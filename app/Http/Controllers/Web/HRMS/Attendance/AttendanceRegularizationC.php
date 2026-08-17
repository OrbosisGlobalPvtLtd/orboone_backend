<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Mail\HrWorkflowAlertMail;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceS;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
            ->addSelect([
                DB::raw('COALESCE(attendances.attendance_date, DATE(attendance_regularizations.requested_punch_in), DATE(attendance_regularizations.requested_punch_out), DATE(attendance_regularizations.created_at)) as mapped_attendance_date'),
                DB::raw('attendances.punch_in_time as mapped_current_punch_in'),
                DB::raw('attendances.punch_out_time as mapped_current_punch_out'),
            ])
            ->whereNull('attendance_regularizations.deleted_at');

        $user = auth()->user();
        $isEmployeeRole = ($user->role_id ?? null) == 7 
            || ($user->system_role_id ?? null) == 7 
            || ! $this->userHasPermission('attendance.regularization.approve');

        if ($isEmployeeRole) {
            $ownEmpId = $this->ownEmployeeId();
            if ($ownEmpId) {
                $query->where('attendance_regularizations.employee_id', $ownEmpId);
            }
        } else {
            $this->scopeEmployeeVisibility($query, 'attendance.regularization.view_all', 'attendance.regularization.view_team', 'attendance_regularizations.employee_id');
        }

        $this->applyCommonFilters($query, $request, [
            'dateColumn' => 'attendance_regularizations.created_at',
            'filterMap' => [
                'employee_id' => 'attendance_regularizations.employee_id',
                'status' => 'attendance_regularizations.status',
                'request_type' => 'attendance_regularizations.request_type',
            ],
        ]);

        return view('hrms.attendance.regularizations.index', $this->pageData($query->latest('attendance_regularizations.id')->paginate(50), $request));
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

            $employeeId = $request->input('employee_id');

            if (! $this->canViewAll('attendance.regularization.view_all') && ! $this->canViewTeam('attendance.regularization.view_team')) {
                $employeeId = $this->ownEmployeeId();
            } elseif (empty($employeeId)) {
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

            $employee = EmployeeM::find($employeeId);
            if (! $employee) {
                return response()->json([
                    'success' => false,
                    'can_regularize' => false,
                    'attendance_status' => null,
                    'message' => 'Employee profile not found.',
                    'available_options' => [],
                ]);
            }

            $service = app(\App\Services\HRMS\Attendance\AttendanceRegularizationService::class);
            $result = $service->getAvailableRegularizationTypes($employee, $rawDate);

            return response()->json($result);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Web Attendance Regularization getOptions error: ' . $e->getMessage(), [
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

        $data = $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'attendance_date' => 'required|date|before_or_equal:today',
            'request_type' => 'required|string|in:' . implode(',', self::REQUEST_TYPES),
            'requested_punch_in' => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|min:5',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);

        if (! $this->canViewAll('attendance.regularization.view_all')) {
            $employeeId = $this->ownEmployeeId();
            abort_if(! $employeeId, 403);
            $data['employee_id'] = $employeeId;
        }

        $employee = EmployeeM::find($data['employee_id']);
        if (! $employee) {
            return back()->with('error', 'Employee not found.')->withInput();
        }

        $attendanceDate = $data['attendance_date'];

        $service = app(\App\Services\HRMS\Attendance\AttendanceRegularizationService::class);
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
        } catch (\Illuminate\Validation\ValidationException $e) {
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

    public function update(Request $request, $id)
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
                $service = app(\App\Services\HRMS\Attendance\AttendanceRegularizationService::class);
                $service->validateRegularizationTimes(
                    employee: $employee,
                    attendanceDate: $baseDate,
                    requestType: $data['request_type'],
                    requestedPunchIn: $data['requested_punch_in'] ?? null,
                    requestedPunchOut: $data['requested_punch_out'] ?? null,
                    existingPunchIn: $row->existing_punch_in,
                    existingPunchOut: $row->existing_punch_out
                );
            } catch (\Illuminate\Validation\ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            }
        }

        $data['requested_punch_in'] = ! empty($data['requested_punch_in']) ? Carbon::parse($baseDate . ' ' . $data['requested_punch_in'])->toDateTimeString() : null;
        $data['requested_punch_out'] = ! empty($data['requested_punch_out']) ? Carbon::parse($baseDate . ' ' . $data['requested_punch_out'])->toDateTimeString() : null;
        DB::table('attendance_regularizations')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));

        return back()->with('success', 'Regularization request updated.');
    }

    public function approve($id)
    {
        abort_unless($this->userHasPermission('attendance.regularization.approve'), 403);
        $this->authorizeRegularizationRow($id, false);

        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        if (! $row || $row->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        try {
            $service = app(\App\Services\HRMS\Attendance\AttendanceRegularizationService::class);
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

    public function reject($id)
    {
        abort_unless($this->userHasPermission('attendance.regularization.reject'), 403);
        $this->authorizeRegularizationRow($id, false);

        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        $note = request('rejection_note') ?: request('rejection_reason') ?: 'Rejected by Admin';

        if ($row) {
            $attendance = $row->attendance_id ? AttendanceM::find($row->attendance_id) : null;
            if (!$attendance) {
                $attendance = AttendanceM::firstOrCreate(
                    ['employee_id' => $row->employee_id, 'attendance_date' => Carbon::parse($row->created_at)->toDateString()]
                );
            }
            if ($attendance && !$attendance->payroll_processed && !$attendance->is_locked) {
                $lwpType = app(AttendanceS::class)->attendanceType('lwp');
                $attendance->attendance_status = 'lwp';
                if ($lwpType) {
                    $attendance->attendance_type_id = $lwpType->id;
                }
                $attendance->is_lwp = true;
                $attendance->lwp_reason = 'Missed punch regularization rejected';
                $attendance->remarks = 'Missed punch regularization rejected';
                $attendance->save();

                app(AttendanceS::class)->syncAttendanceViolations($attendance);
            }
        }

        DB::table('attendance_regularizations')->where('id', $id)->update([
            'status' => 'rejected',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => $this->nowKolkata(),
            'rejection_reason' => $note,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Regularization rejected.');
    }

    public function destroy($id)
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
            'dateColumn' => 'attendance_regularizations.created_at',
            'filterMap' => [
                'employee_id' => 'attendance_regularizations.employee_id',
                'status' => 'attendance_regularizations.status',
                'request_type' => 'attendance_regularizations.request_type',
            ],
        ]);
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

    private function pageData($rows, Request $request): array
    {
        $user = auth()->user();
        $isEmployeeRole = ($user->role_id ?? null) == 7 
            || ($user->system_role_id ?? null) == 7 
            || ! $this->userHasPermission('attendance.regularization.approve');

        if ($isEmployeeRole) {
            $ownEmp = $user->employee ?? $this->currentEmployee();
            $employees = $ownEmp ? [$ownEmp->id => ($user->name ?? $ownEmp->employee_code)] : [];
        } else {
            $employees = $this->scopedEmployeeOptions('attendance.regularization.view_all', 'attendance.regularization.view_team')->pluck('display_name', 'id')->toArray();
        }
        $requestTypes = DB::table('attendance_regularizations')->whereNull('deleted_at')->whereNotNull('request_type')->distinct()->pluck('request_type', 'request_type')->toArray();

        $filters = [
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']],
            ['name' => 'request_type', 'label' => 'Request Type', 'type' => 'select', 'options' => $requestTypes],
            ['name' => 'from', 'label' => 'From', 'type' => 'date'],
            ['name' => 'to', 'label' => 'To', 'type' => 'date'],
        ];

        if (! $isEmployeeRole && ($this->canViewAll('attendance.regularization.view_all') || $this->canViewTeam('attendance.regularization.view_team'))) {
            array_unshift($filters, ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees]);
        }

        return [
            'accesses' => $this->accesses(),
            'active' => 'attendance',
            'pageTitle' => 'Attendance Regularizations',
            'pageSubtitle' => 'Review, create, approve, and reject attendance correction requests.',
            'rows' => $rows,
            'canViewAll' => $isEmployeeRole ? false : $this->canViewAll('attendance.regularization.view_all'),
            'canViewTeam' => $isEmployeeRole ? false : $this->canViewTeam('attendance.regularization.view_team'),
            'isEmployeeRole' => $isEmployeeRole,
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
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees],
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
            'canApprove' => ! $isEmployeeRole && ($this->userHasPermission('attendance.regularization.approve') || $this->canViewAll('attendance.regularization.view_all') || $this->canViewTeam('attendance.regularization.view_team')),
            'canReject' => ! $isEmployeeRole && ($this->userHasPermission('attendance.regularization.approve') || $this->canViewAll('attendance.regularization.view_all') || $this->canViewTeam('attendance.regularization.view_team')),
            'storeRoute' => 'hrms.attendance.regularizations.store',
            'updateRoute' => 'hrms.attendance.regularizations.update',
            'deleteRoute' => 'hrms.attendance.regularizations.destroy',
            'rowActions' => [
                ['label' => 'Approve', 'route' => 'hrms.attendance.regularizations.approve', 'icon' => 'fas fa-check', 'confirm' => 'Approve this request?'],
                ['label' => 'Reject', 'route' => 'hrms.attendance.regularizations.reject', 'icon' => 'fas fa-times', 'confirm' => 'Reject this request?'],
            ],
        ];
    }

    private function authorizeRegularizationRow($id, bool $allowOwn): void
    {
        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        abort_if(! $row, 404);

        if ($this->canViewAll('attendance.regularization.view_all')) {
            return;
        }

        if ($this->canViewTeam('attendance.regularization.view_team') && in_array((int) $row->employee_id, $this->teamEmployeeIds(false), true)) {
            return;
        }

        abort_unless($allowOwn && (int) $row->employee_id === (int) $this->ownEmployeeId(), 403);
    }
}
