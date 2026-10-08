<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Mail\HrWorkflowAlertMail;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceRegularizationService;
use App\Services\HRMS\Attendance\AttendanceRuleResolverService;
use App\Services\HRMS\Attendance\AttendanceService;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Core\UserM;
use Illuminate\Validation\ValidationException;

class AttendanceRegularizationC extends Controller
{
    use HrmsCrudPage;
    private const REQUEST_TYPES = [
        'regular_attendance', 'missed_punch_in', 'missed_punch_out', 'attendance_correction',
        'wrong_punch_time', 'late_mark_exemption', 'early_logout_correction', 'geofence_issue',
        'system_error', 'unlock_attendance', 'other',
    ];

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.regularization.view_all')
            || $this->userHasPermission('attendance.regularization.view_team')
            || $this->userHasPermission('attendance.regularization.view_own')
            || $this->userHasPermission('attendance.regularization.view'),
            403
        );

        $query = $this->employeeJoinedQuery('attendance_regularizations')
            ->leftJoin('attendances', 'attendances.id', '=', 'attendance_regularizations.attendance_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
            ->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id')
            ->leftJoin('attendance_types', 'attendance_types.id', '=', 'attendances.attendance_type_id')
            ->addSelect([
                DB::raw('COALESCE(attendances.attendance_date, DATE(attendance_regularizations.requested_punch_in), DATE(attendance_regularizations.requested_punch_out), DATE(attendance_regularizations.created_at)) as mapped_attendance_date'),
                DB::raw('attendances.punch_in_time as mapped_current_punch_in'),
                DB::raw('attendances.punch_out_time as mapped_current_punch_out'),
                DB::raw('attendances.attendance_status as current_attendance_status'),
                'departments.name as department_name',
                'designations.name as designation_name',
                'attendance_types.name as current_attendance_type_name',
            ])
            ->whereNull('attendance_regularizations.deleted_at');

        /** @var UserM|null $user */
        $user = Auth::user();
        $isHrOrAdmin = $this->isHrOrAdminUser();

        if ($isHrOrAdmin) {
            // Global visibility for HR Admin & Super Admin
        } elseif ($this->canViewTeam('attendance.regularization.view_team') || (method_exists($user, 'hasRole') && $user->hasRole(['manager', 'lead', 'team_lead']))) {
            $this->scopeEmployeeVisibility($query, 'attendance.regularization.view_all', 'attendance.regularization.view_team', 'attendance_regularizations.employee_id');
        } else {
            $ownEmpId = $this->ownEmployeeId();
            if ($ownEmpId) {
                $query->where('attendance_regularizations.employee_id', $ownEmpId);
            }
        }

        $ownEmp = $user->employee ?? $this->currentEmployee();
        $ownEmpId = $ownEmp?->id ?: $this->ownEmployeeId();

        $hasEmpFilter = $request->has('employee_id');
        $defaultEmpId = (! $hasEmpFilter && ! $request->has('reset') && $ownEmpId) ? (int) $ownEmpId : null;

        if ($hasEmpFilter) {
            if ($request->filled('employee_id') && $request->input('employee_id') !== 'all') {
                $query->where('attendance_regularizations.employee_id', $request->input('employee_id'));
            }
        } elseif ($defaultEmpId) {
            $query->where('attendance_regularizations.employee_id', $defaultEmpId);
        }

        $this->applyCommonFilters($query, $request, [
            'filterMap' => [
                'status' => 'attendance_regularizations.status',
                'request_type' => 'attendance_regularizations.request_type',
            ],
        ]);

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');
        $month = $request->input('month');

        // Default to current month if no filter params specified
        if ($month === null && !$fromDate && !$toDate && !$request->has('reset')) {
            $month = now()->format('Y-m');
        }

        if ($fromDate || $toDate) {
            if ($fromDate) {
                $query->where(function ($q) use ($fromDate) {
                    $q->whereDate('attendances.attendance_date', '>=', $fromDate)
                        ->orWhere(function ($sub) use ($fromDate) {
                            $sub->whereNull('attendances.attendance_date')
                                ->whereDate('attendance_regularizations.created_at', '>=', $fromDate);
                        });
                });
            }
            if ($toDate) {
                $query->where(function ($q) use ($toDate) {
                    $q->whereDate('attendances.attendance_date', '<=', $toDate)
                        ->orWhere(function ($sub) use ($toDate) {
                            $sub->whereNull('attendances.attendance_date')
                                ->whereDate('attendance_regularizations.created_at', '<=', $toDate);
                        });
                });
            }
        } elseif ($month && $month !== 'all' && $month !== 'custom') {
            try {
                $monthDate = Carbon::parse($month . '-01');
                $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
                $endOfMonth = $monthDate->copy()->endOfMonth()->toDateString();

                $query->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('attendances.attendance_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                            $sub->whereNull('attendances.attendance_date')
                                ->whereBetween(DB::raw('DATE(attendance_regularizations.created_at)'), [$startOfMonth, $endOfMonth]);
                        });
                });
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }

        $perPageInput = (string) $request->input('per_page', '25');
        if ($perPageInput === 'all' || $perPageInput === '-1') {
            $rows = $query->latest('attendance_regularizations.id')->paginate(5000)->appends($request->all());
        } else {
            $perPage = min(max((int) $perPageInput, 5), 500);
            $rows = $query->latest('attendance_regularizations.id')->paginate($perPage)->appends($request->all());
        }

        return view('hrms.attendance.regularizations.index', $this->pageData($rows, $request));
    }

    public function getOptions(Request $request)
    {
        try {
            $rawDate = $request->input('date') ?? $request->input('attendance_date');
            if (empty($rawDate)) {
                return response()->json([
                    'success' => false,
                    'can_regularize' => false,
                    'attendance_status' => null,
                    'message' => 'Please select an attendance date.',
                    'available_options' => [],
                ]);
            }

            $isHrOrAdmin = $this->isHrOrAdminUser();
            if ($isHrOrAdmin) {
                $employeeId = $request->input('employee_id') ?: $this->ownEmployeeId();
            } else {
                // Reporting Managers and Employees can only regularize for themselves
                $employeeId = $this->ownEmployeeId();
            }

            if (empty($employeeId)) {
                return response()->json([
                    'success' => false,
                    'can_regularize' => false,
                    'attendance_status' => null,
                    'message' => 'Please select an employee first.',
                    'available_options' => [],
                ]);
            }

            $employee = EmployeeM::activeEligible()->find($employeeId);
            if (! $employee) {
                return response()->json([
                    'success' => false,
                    'can_regularize' => false,
                    'attendance_status' => null,
                    'message' => 'Selected employee is not active or profile is incomplete/not approved.',
                    'available_options' => [],
                ]);
            }

            $service = app(AttendanceRegularizationService::class);
            $result = $service->getAvailableRegularizationTypes($employee, $rawDate);

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('Web Attendance Regularization getOptions error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'can_regularize' => false,
                'attendance_status' => null,
                'message' => config('app.debug') ? $e->getMessage() : 'Unable to load regularization options.',
                'available_options' => [],
            ]);
        }
    }

    public function store(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.regularization.create'), 403);

        $isHrOrAdmin = $this->isHrOrAdminUser();

        $data = $request->validate([
            'employee_id' => $isHrOrAdmin ? 'required|exists:employees_new,id' : 'nullable',
            'attendance_date' => 'required|date|before_or_equal:today',
            'request_type' => 'required|string|in:' . implode(',', self::REQUEST_TYPES),
            'requested_punch_in' => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|min:5',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);

        if (! $isHrOrAdmin) {
            $employeeId = $this->ownEmployeeId();
            if (! $employeeId) {
                return back()->with('error', 'Your employee profile was not found.')->withInput();
            }
            $data['employee_id'] = $employeeId;
        }

        $employee = EmployeeM::activeEligible()->find($data['employee_id']);
        if (! $employee) {
            return back()->with('error', 'Selected employee is not active or profile is incomplete/not approved.')->withInput();
        }

        $attendanceDate = $data['attendance_date'];

        $service = app(AttendanceRegularizationService::class);
        $optionsResult = $service->getAvailableRegularizationTypes($employee, $attendanceDate);

        if (! $optionsResult['can_regularize']) {
            return back()->with('error', $optionsResult['message'] ?? 'Regularization is not allowed for this date.')->withInput();
        }

        $allowedOptionIds = array_column($optionsResult['available_options'], 'id');
        if (! in_array($data['request_type'], $allowedOptionIds, true)) {
            return back()->with('error', 'Selected regularization type is not valid for this date.')->withInput();
        }

        $attendance = AttendanceM::where('employee_id', $data['employee_id'])
            ->whereDate('attendance_date', $attendanceDate)
            ->first();

        unset($data['attendance_date']);

        if ($data['request_type'] === 'regular_attendance' && (empty($data['requested_punch_in']) || empty($data['requested_punch_out']))) {
            return back()->withErrors(['requested_punch_in' => 'Both Punch In and Punch Out times are required for Regular Attendance.'])->withInput();
        }
        if ($data['request_type'] === 'missed_punch_in' && empty($data['requested_punch_in'])) {
            return back()->withErrors(['requested_punch_in' => 'Requested punch in time is required.'])->withInput();
        }
        if ($data['request_type'] === 'missed_punch_out' && empty($data['requested_punch_out'])) {
            return back()->withErrors(['requested_punch_out' => 'Requested punch out time is required.'])->withInput();
        }

        try {
            $service->validateRegularizationTimes(
                employee: $employee,
                attendanceDate: $attendanceDate,
                requestType: $data['request_type'],
                requestedPunchIn: $data['requested_punch_in'] ?? null,
                requestedPunchOut: $data['requested_punch_out'] ?? null,
                existingPunchIn: $attendance?->punch_in_time,
                existingPunchOut: $attendance?->punch_out_time
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $requestedIn = ! empty($data['requested_punch_in']) ? Carbon::parse($attendanceDate . ' ' . $data['requested_punch_in'])->toDateTimeString() : null;
        $requestedOut = ! empty($data['requested_punch_out']) ? Carbon::parse($attendanceDate . ' ' . $data['requested_punch_out'])->toDateTimeString() : null;
        DB::table('attendance_regularizations')->insert(array_merge($data, [
            'attendance_id' => $attendance?->id,
            'existing_punch_in' => $attendance?->punch_in_time,
            'existing_punch_out' => $attendance?->punch_out_time,
            'requested_punch_in' => $requestedIn,
            'requested_punch_out' => $requestedOut,
            'status' => $data['status'] ?? 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        app(NotificationS::class)->notifyHrAndSuperAdmin(
            'Attendance Regularization Request',
            'Regularization request submitted by employee.',
            'attendance_regularization_submitted',
            'hrms.attendance.regularizations.index'
        );

        $hrEmail = config('hrms.emails.hr');
        if ($hrEmail) {
            $details = [
                'Employee Name' => $employee?->display_name ?: 'Employee',
                'Employee Code' => $employee?->employee_code ?: 'N/A',
                'Attendance Date' => $attendanceDate,
                'Request Type' => (string) $data['request_type'],
                'Current Punch In' => (string) ($attendance?->punch_in_time ?: '-'),
                'Current Punch Out' => (string) ($attendance?->punch_out_time ?: '-'),
                'Requested Punch In' => (string) ($data['requested_punch_in'] ?: '-'),
                'Requested Punch Out' => (string) ($data['requested_punch_out'] ?: '-'),
                'Reason' => (string) $data['reason'],
            ];

            Mail::to($hrEmail)->queue(new HrWorkflowAlertMail(
                subjectText: 'Attendance Regularization Request - ' . ($employee?->display_name ?: 'Employee'),
                workflowTitle: 'Attendance Regularization Request',
                details: $details,
                actionUrl: route('hrms.attendance.regularizations.index'),
                replyToEmail: $employee?->user?->email
            ));
        }

        return back()->with('success', 'Regularization request saved.');
    }

    public function update(Request $request, int|string $id)
    {
        $this->authorizeRegularizationRow($id, true);
        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        abort_if(! $row, 404);

        $data = $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'request_type' => 'required|string|in:' . implode(',', self::REQUEST_TYPES),
            'requested_punch_in' => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|min:5',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);

        $baseDate = Carbon::parse($row->created_at)->toDateString();

        $employee = EmployeeM::find($data['employee_id']);
        if ($employee) {
            try {
                $service = app(AttendanceRegularizationService::class);
                $service->validateRegularizationTimes(
                    employee: $employee,
                    attendanceDate: $baseDate,
                    requestType: $data['request_type'],
                    requestedPunchIn: $data['requested_punch_in'] ?? null,
                    requestedPunchOut: $data['requested_punch_out'] ?? null,
                    existingPunchIn: $row->existing_punch_in,
                    existingPunchOut: $row->existing_punch_out
                );
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            }
        }

        $data['requested_punch_in'] = ! empty($data['requested_punch_in']) ? Carbon::parse($baseDate . ' ' . $data['requested_punch_in'])->toDateTimeString() : null;
        $data['requested_punch_out'] = ! empty($data['requested_punch_out']) ? Carbon::parse($baseDate . ' ' . $data['requested_punch_out'])->toDateTimeString() : null;
        DB::table('attendance_regularizations')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));

        return back()->with('success', 'Regularization request updated.');
    }

    public function approve(int|string $id)
    {
        abort_unless($this->userHasPermission('attendance.regularization.approve'), 403);
        $this->authorizeRegularizationRow($id, false);

        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        if (! $row || $row->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        try {
            $service = app(AttendanceRegularizationService::class);
            $result = $service->applyApprovedRegularization((int) $id, $this->actorId());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $employee = EmployeeM::find($row->employee_id);
        if ($employee?->user_id) {
            app(NotificationS::class)->notifyEmployee(
                'Attendance Regularization Update',
                'Your regularization request has been approved.',
                'attendance_regularization_approved',
                'hrms.attendance.regularizations.index',
                [],
                ['regularization_id' => $id],
                (int) $employee->user_id
            );
        }

        $warning = $result['warning'] ?? null;
        $msg = $result['message'] ?? 'Regularization approved and attendance recalculated.';

        if ($warning) {
            return back()->with('warning', $warning)->with('success', $msg);
        }

        return back()->with('success', $msg);
    }

    public function reject(int|string $id)
    {
        abort_unless($this->userHasPermission('attendance.regularization.reject'), 403);
        $this->authorizeRegularizationRow($id, false);

        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        $note = request('rejection_note') ?: request('rejection_reason') ?: 'Rejected by Admin';

        if ($row) {
            $attendanceService = app(AttendanceService::class);
            $attendance = $row->attendance_id ? AttendanceM::find($row->attendance_id) : null;
            if (!$attendance) {
                $attendance = AttendanceM::firstOrCreate(
                    ['employee_id' => $row->employee_id, 'attendance_date' => Carbon::parse($row->created_at)->toDateString()]
                );
            }
            if ($attendance && !$attendance->payroll_processed && !$attendance->is_locked) {
                $employee = EmployeeM::find($row->employee_id);
                $dateStr = Carbon::parse($attendance->attendance_date)->toDateString();
                $policy = $employee ? app(AttendanceRuleResolverService::class)->resolveShiftPolicy($employee, $dateStr, $attendance->attendance_time_id) : null;
                $allowedMissedPunches = (int) ($policy->allowed_missed_punches ?? 2);
                if ($allowedMissedPunches <= 0 && isset($policy->missed_punch_lwp_after) && (int)$policy->missed_punch_lwp_after > 1) {
                    $allowedMissedPunches = (int)$policy->missed_punch_lwp_after - 1;
                }

                $attDate = Carbon::parse($attendance->attendance_date, AttendanceRegularizationService::TIMEZONE);
                $missedCount = AttendanceViolationM::where('employee_id', $attendance->employee_id)
                    ->where('type', 'missed_punch')
                    ->whereYear('violation_date', $attDate->year)
                    ->whereMonth('violation_date', $attDate->month)
                    ->count();

                $limitExceeded = $allowedMissedPunches >= 0 && $missedCount > $allowedMissedPunches;

                if ($limitExceeded) {
                    $lwpType = $attendanceService->attendanceType('lwp');
                    $attendance->attendance_status = 'lwp';
                    if ($lwpType) {
                        $attendance->attendance_type_id = $lwpType->id;
                    }
                    $attendance->is_lwp = true;
                    $attendance->lwp_reason = 'Monthly missed punch grace limit exceeded. Regularization rejected.';
                    $attendance->remarks = 'Monthly missed punch grace limit exceeded. Regularization rejected.';
                } else {
                    $attendance->remarks = 'Regularization rejected: ' . $note;
                }
                $attendance->save();

                $attendanceService->calculateAttendanceStats($attendance);
                $attendanceService->syncAttendanceViolations($attendance);
            }
        }

        DB::table('attendance_regularizations')->where('id', $id)->update([
            'status' => 'rejected',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => $this->nowKolkata(),
            'rejection_reason' => $note,
            'updated_at' => now(),
        ]);

        $employee = $row ? EmployeeM::find($row->employee_id) : null;
        if ($employee?->user_id) {
            app(NotificationS::class)->notifyEmployee(
                'Attendance Regularization Update',
                'Your regularization request has been rejected.',
                'attendance_regularization_rejected',
                'hrms.attendance.regularizations.index',
                [],
                ['regularization_id' => $id],
                (int) $employee->user_id
            );
        }

        return back()->with('success', 'Regularization rejected.');
    }

    public function destroy(int|string $id)
    {
        $this->authorizeRegularizationRow($id, true);

        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        if (! $row) {
            return back()->with('error', 'Regularization request not found.');
        }

        if ($row->status !== 'pending') {
            return back()->with('error', 'Approved or processed regularization requests cannot be deleted.');
        }

        DB::table('attendance_regularizations')->where('id', $id)->update(['deleted_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Regularization deleted.');
    }

    public function exportExcel(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.export'), 403);

        $query = $this->employeeJoinedQuery('attendance_regularizations')
            ->whereNull('attendance_regularizations.deleted_at');
        $this->scopeEmployeeVisibility($query, 'attendance.regularization.view_all', 'attendance.regularization.view_team', 'attendance_regularizations.employee_id');
        $this->applyCommonFilters($query, $request, [
            'filterMap' => [
                'employee_id' => 'attendance_regularizations.employee_id',
                'status' => 'attendance_regularizations.status',
                'request_type' => 'attendance_regularizations.request_type',
            ],
        ]);

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');
        $month = $request->input('month');

        if ($fromDate) {
            $query->where(function ($q) use ($fromDate) {
                $q->whereDate('attendance_regularizations.created_at', '>=', $fromDate);
            });
        }
        if ($toDate) {
            $query->where(function ($q) use ($toDate) {
                $q->whereDate('attendance_regularizations.created_at', '<=', $toDate);
            });
        }
        if (!$fromDate && !$toDate && $month) {
            try {
                $monthDate = Carbon::parse($month . '-01');
                $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
                $endOfMonth = $monthDate->copy()->endOfMonth()->toDateString();
                $query->whereBetween(DB::raw('DATE(attendance_regularizations.created_at)'), [$startOfMonth, $endOfMonth]);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        $rows = $query->latest('attendance_regularizations.id')->get();

        return response()->stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Code', 'Type', 'Requested In', 'Requested Out', 'Status', 'Created At']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->employee_display_name,
                    $row->employee_code,
                    $row->request_type,
                    $row->requested_punch_in,
                    $row->requested_punch_out,
                    $row->status,
                    $row->created_at,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_regularizations.csv"',
        ]);
    }

    private function isHrOrAdminUser(): bool
    {
        /** @var UserM|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');

        return (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array($roleId, [1, 2, 3], true)
            || in_array($roleName, ['admin', 'super_admin', 'super admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true)
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin', 'hr']))
            || $this->userHasPermission('attendance.regularization.view_all');
    }

    private function pageData(mixed $rows, Request $request): array
    {
        /** @var UserM|null $user */
        $user = Auth::user();
        $isHrOrAdmin = $this->isHrOrAdminUser();
        $canViewTeam = ! $isHrOrAdmin && ($this->canViewTeam('attendance.regularization.view_team') || (method_exists($user, 'hasRole') && $user->hasRole(['manager', 'lead', 'team_lead'])));
        $isEmployeeRole = ! $isHrOrAdmin && ! $canViewTeam && ! $this->userHasPermission('attendance.regularization.approve');

        $ownEmp = $user->employee ?? $this->currentEmployee();
        $ownEmpId = $ownEmp?->id;

        // Filter dropdown on index page (Admin: all, Manager: team, Employee: self)
        if ($isEmployeeRole) {
            $filterEmployees = $ownEmp ? [$ownEmp->id => ($user->name ?? $ownEmp->employee_code)] : [];
        } else {
            $filterEmployees = $this->scopedEmployeeOptions('attendance.regularization.view_all', 'attendance.regularization.view_team')->pluck('display_name', 'id')->toArray();
        }

        // Create modal employee options (Admin: all, Manager/Employee: self only)
        if ($isHrOrAdmin) {
            $createEmployees = $filterEmployees;
        } else {
            $createEmployees = $ownEmp ? [$ownEmp->id => ($user->name ?? $ownEmp->employee_code)] : [];
        }

        $requestTypes = DB::table('attendance_regularizations')->whereNull('deleted_at')->whereNotNull('request_type')->distinct()->pluck('request_type', 'request_type')->toArray();

        $months = [
            'custom' => 'Custom Date Range',
            'all' => 'All Months',
        ];
        for ($i = 0; $i < 24; $i++) {
            $m = Carbon::now()->subMonths($i);
            $key = $m->format('Y-m');
            $months[$key] = $m->format('F Y') . ($i === 0 ? ' (Current)' : '');
        }

        $filters = [
            ['name' => 'month', 'label' => 'Month', 'type' => 'select', 'options' => $months],
            ['name' => 'from', 'label' => 'From Date', 'type' => 'date'],
            ['name' => 'to', 'label' => 'To Date', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']],
            ['name' => 'request_type', 'label' => 'Request Type', 'type' => 'select', 'options' => $requestTypes],
        ];

        if (! $isEmployeeRole && ($isHrOrAdmin || $canViewTeam)) {
            array_unshift($filters, ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $filterEmployees]);
        }

        $baseStatsQuery = DB::table('attendance_regularizations')
            ->leftJoin('attendances', 'attendances.id', '=', 'attendance_regularizations.attendance_id')
            ->whereNull('attendance_regularizations.deleted_at');

        if (! $isHrOrAdmin) {
            if ($canViewTeam) {
                $teamEmpIds = $this->teamEmployeeIds(true);
                $baseStatsQuery->whereIn('attendance_regularizations.employee_id', $teamEmpIds);
            } else {
                if ($ownEmpId) {
                    $baseStatsQuery->where('attendance_regularizations.employee_id', $ownEmpId);
                }
            }
        }

        $hasEmpFilter = $request->has('employee_id');
        $defaultEmpId = (! $hasEmpFilter && ! $request->has('reset') && $ownEmpId) ? (int) $ownEmpId : null;

        if ($hasEmpFilter) {
            if ($request->filled('employee_id') && $request->input('employee_id') !== 'all') {
                $baseStatsQuery->where('attendance_regularizations.employee_id', $request->input('employee_id'));
            }
        } elseif ($defaultEmpId) {
            $baseStatsQuery->where('attendance_regularizations.employee_id', $defaultEmpId);
        }

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');
        $month = $request->input('month');

        if ($month === null && !$fromDate && !$toDate && !$request->has('reset')) {
            $month = now()->format('Y-m');
        }

        if ($fromDate || $toDate) {
            if ($fromDate) {
                $baseStatsQuery->where(function ($q) use ($fromDate) {
                    $q->whereDate('attendances.attendance_date', '>=', $fromDate)
                        ->orWhere(function ($sub) use ($fromDate) {
                            $sub->whereNull('attendances.attendance_date')
                                ->whereDate('attendance_regularizations.created_at', '>=', $fromDate);
                        });
                });
            }
            if ($toDate) {
                $baseStatsQuery->where(function ($q) use ($toDate) {
                    $q->whereDate('attendances.attendance_date', '<=', $toDate)
                        ->orWhere(function ($sub) use ($toDate) {
                            $sub->whereNull('attendances.attendance_date')
                                ->whereDate('attendance_regularizations.created_at', '<=', $toDate);
                        });
                });
            }
        } elseif ($month && $month !== 'all' && $month !== 'custom') {
            try {
                $monthDate = Carbon::parse($month . '-01');
                $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
                $endOfMonth = $monthDate->copy()->endOfMonth()->toDateString();

                $baseStatsQuery->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('attendances.attendance_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                            $sub->whereNull('attendances.attendance_date')
                                ->whereBetween(DB::raw('DATE(attendance_regularizations.created_at)'), [$startOfMonth, $endOfMonth]);
                        });
                });
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'pending' => (clone $baseStatsQuery)->where('attendance_regularizations.status', 'pending')->count(),
            'approved' => (clone $baseStatsQuery)->where('attendance_regularizations.status', 'approved')->count(),
            'rejected' => (clone $baseStatsQuery)->where('attendance_regularizations.status', 'rejected')->count(),
            'unlock_requests' => (clone $baseStatsQuery)->where('attendance_regularizations.request_type', 'unlock_attendance')->count(),
            'punch_corrections' => (clone $baseStatsQuery)->whereIn('attendance_regularizations.request_type', ['regular_attendance', 'missed_punch_in', 'missed_punch_out', 'wrong_punch_time'])->count(),
        ];

        return [
            'accesses' => $this->accesses(),
            'active' => 'attendance',
            'pageTitle' => 'Attendance Regularizations',
            'pageSubtitle' => 'Review, create, approve, and reject attendance correction requests.',
            'rows' => $rows,
            'stats' => $stats,
            'canViewAll' => $isHrOrAdmin,
            'canViewTeam' => $canViewTeam,
            'isEmployeeRole' => $isEmployeeRole,
            'canApplyForOthers' => $isHrOrAdmin,
            'isSelfOnly' => ! $isHrOrAdmin,
            'defaultEmployeeId' => $defaultEmpId ?? $ownEmpId,
            'modalOwnEmpId' => $ownEmpId,
            'columns' => [
                ['key' => 'employee_display_name', 'label' => 'Employee'],
                ['key' => 'employee_code', 'label' => 'Code'],
                ['key' => 'mapped_attendance_date', 'label' => 'Date', 'type' => 'date'],
                ['key' => 'request_type', 'label' => 'Request Type'],
                ['key' => 'mapped_current_punch_in', 'label' => 'Current In', 'type' => 'datetime'],
                ['key' => 'mapped_current_punch_out', 'label' => 'Current Out', 'type' => 'datetime'],
                ['key' => 'requested_punch_in', 'label' => 'Requested In', 'type' => 'datetime'],
                ['key' => 'requested_punch_out', 'label' => 'Requested Out', 'type' => 'datetime'],
                ['key' => 'reason', 'label' => 'Reason'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ['key' => 'created_at', 'label' => 'Submitted At', 'type' => 'datetime'],
            ],
            'filters' => $filters,
            'formFields' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $createEmployees],
                ['name' => 'attendance_date', 'label' => 'Attendance Date', 'type' => 'date'],
                ['name' => 'request_type', 'label' => 'Request Type', 'type' => 'select', 'options' => [
                    'missed_punch_in' => 'Missed Punch In',
                    'missed_punch_out' => 'Missed Punch Out',
                    'wrong_punch_time' => 'Wrong Punch Timing',
                    'late_mark_exemption' => 'Late Mark Exemption',
                    'early_logout_correction' => 'Early Logout Correction',
                    'geofence_issue' => 'Geofence Issue',
                    'system_error' => 'System/App Error',
                    'other' => 'Other',
                ]],
                ['name' => 'requested_punch_in', 'label' => 'Requested Punch In', 'type' => 'time'],
                ['name' => 'requested_punch_out', 'label' => 'Requested Punch Out', 'type' => 'time'],
                ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 12],
            ],
            'canCreate' => true,
            'canEdit' => true,
            'canDelete' => true,
            'canApprove' => ! $isEmployeeRole && ($this->userHasPermission('attendance.regularization.approve') || $isHrOrAdmin || $canViewTeam),
            'canReject' => ! $isEmployeeRole && ($this->userHasPermission('attendance.regularization.approve') || $isHrOrAdmin || $canViewTeam),
            'storeRoute' => 'hrms.attendance.regularizations.store',
            'updateRoute' => 'hrms.attendance.regularizations.update',
            'deleteRoute' => 'hrms.attendance.regularizations.destroy',
            'rowActions' => [
                ['label' => 'Approve', 'route' => 'hrms.attendance.regularizations.approve', 'icon' => 'fas fa-check', 'confirm' => 'Approve this request?'],
                ['label' => 'Reject', 'route' => 'hrms.attendance.regularizations.reject', 'icon' => 'fas fa-times', 'confirm' => 'Reject this request?'],
            ],
        ];
    }

    private function authorizeRegularizationRow(int|string $id, bool $allowOwn): void
    {
        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        abort_if(! $row, 404);

        if ($this->isHrOrAdminUser()) {
            return;
        }

        if ($this->canViewTeam('attendance.regularization.view_team') && in_array((int) $row->employee_id, $this->teamEmployeeIds(false), true)) {
            return;
        }

        abort_unless($allowOwn && (int) $row->employee_id === (int) $this->ownEmployeeId(), 403);
    }
}
