<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendancePolicyRuleM as AttendancePolicyRule;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeShiftTimingM;
use App\Services\HRMS\Attendance\AttendanceS;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AttendancesC extends Controller
{
    use HrmsCrudPage;

    private AttendanceS $attendanceService;

    public function __construct(AttendanceS $attendanceService)
    {
        $this->middleware('auth');
        $this->attendanceService = $attendanceService;
    }

    private function baseQuery()
    {
        return Attendance::with([
            'user',
            'employee.department',
            'employee.designation',
            'attendanceType',
            'attendanceTime',
            'workLogs',
            'hrApprovedBy',
            'unlockedBy',
        ]);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })->orWhereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery->where('employee_code', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn($employeeQuery) => $employeeQuery->where('department_id', $request->department_id));
        }

        if ($request->filled('attendance_time_id')) {
            $query->where('attendance_time_id', $request->attendance_time_id);
        }

        if ($request->filled('from_date') || $request->filled('to_date')) {
            if ($request->filled('from_date')) {
                $query->whereDate('attendance_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('attendance_date', '<=', $request->to_date);
            }
        } elseif ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->date);
        } elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('attendance_date', (int) $request->month)
                ->whereYear('attendance_date', (int) $request->year);
        } else {
            $today = Carbon::now($this->attendanceService->attendanceTimezone())->toDateString();
            if ($request->filter === 'today') {
                $query->whereDate('attendance_date', $today);
            } elseif ($request->filter === 'yesterday') {
                $query->whereDate('attendance_date', Carbon::yesterday()->toDateString());
            }
        }

        if ($request->filled('attendance_type_id')) {
            $query->where('attendance_type_id', $request->attendance_type_id);
        }

        if ($request->filled('work_mode')) {
            $query->where('work_mode', strtolower($request->work_mode));
        }

        if ($request->filled('flag')) {
            switch ($request->flag) {
                case 'late':
                    $query->where('is_late', 1);
                    break;
                case 'early_out':
                    $query->where('is_early_out', 1);
                    break;
                case 'blocked':
                    $query->where(function ($blockedQuery) {
                        $blockedQuery->where('is_punch_blocked', 1)
                            ->orWhere('is_blocked', 1)
                            ->orWhere('attendance_status', 'punch_blocked');
                    });
                    break;
                case 'half_day':
                    $query->where('is_half_day', 1);
                    break;
                case 'lwp':
                    $query->where('is_lwp', 1);
                    break;
                case 'missed':
                case 'missed_punch':
                    $query->where(function ($q) {
                        $q->where('missed_punch', 1)
                            ->orWhere('is_missed_punch', 1)
                            ->orWhere('attendance_status', 'missed_punch');
                    });
                    break;
                case 'unlocked':
                    $query->where('is_admin_unlocked', 1);
                    break;
                case 'manual_punch_in':
                    $query->where('unlock_type', 'manual_punch_in');
                    break;
                case 'clear':
                    $query->where('is_late', 0)
                        ->where('is_early_out', 0)
                        ->where('is_blocked', 0)
                        ->where('is_punch_blocked', 0)
                        ->where('missed_punch', 0)
                        ->where('is_missed_punch', 0);
                    break;
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.dashboard.view'), 403);

        $today = Carbon::now($this->attendanceService->attendanceTimezone())->toDateString();
        if (! $request->filled('date') && ! $request->filled('from_date')) {
            $request->merge(['date' => $today]);
        }

        $query = $this->scopeAttendanceQuery($this->applyFilters($this->baseQuery(), $request), 'attendance.records.view_all', 'attendance.regularization.view_team');
        $todayRecordsQuery = Attendance::with('attendanceType')->whereDate('attendance_date', $today);
        $todayRecords = $this->scopeAttendanceQuery($todayRecordsQuery, 'attendance.records.view_all', 'attendance.regularization.view_team')->get();
        $this->normalizeAttendanceCollection($todayRecords);

        $stats = [
            'present_today' => $todayRecords->filter(fn($item) => optional($item->attendanceType)->code === 'present')->count(),
            'absent_today' => $todayRecords->filter(fn($item) => optional($item->attendanceType)->code === 'absent')->count(),
            'late_employees' => $todayRecords->where('is_late', true)->count(),
            'early_logout' => $todayRecords->where('is_early_out', true)->count(),
            'half_day' => $todayRecords->where('is_half_day', true)->count(),
            'lwp' => $todayRecords->where('is_lwp', true)->count(),
            'punch_blocked' => $todayRecords->filter(fn($item) => $item->is_punch_blocked || $item->is_blocked || $item->attendance_status === 'punch_blocked')->count(),
            'pending_hr' => $todayRecords->filter(fn($item) => in_array($item->attendance_status, ['pending_hr', 'missed_punch'], true))->count(),
            'missed_punches' => $todayRecords->where('missed_punch', true)->count(),
            'currently_working' => $todayRecords->whereNotNull('punch_in_time')->whereNull('punch_out_time')->where('is_blocked', false)->count(),
            'pending_punch_out' => $todayRecords->whereNotNull('punch_in_time')->whereNull('punch_out_time')->count(),
            'completed_shift' => $todayRecords->whereNotNull('punch_in_time')->whereNotNull('punch_out_time')->count(),
            'wfo_today' => $todayRecords->where('work_mode', 'wfo')->count(),
            'wfh_today' => $todayRecords->where('work_mode', 'wfh')->count(),
            'total_hours' => round($todayRecords->sum('total_work_minutes') / 60, 1),
            'total_late' => $todayRecords->where('is_late', true)->count(),
            'total_early_out' => $todayRecords->where('is_early_out', true)->count(),
            'total_pending_hr' => $todayRecords->filter(fn($item) => in_array($item->attendance_status, ['pending_hr', 'missed_punch'], true))->count(),
            'total_blocked' => $todayRecords->filter(fn($item) => $item->is_punch_blocked || $item->is_blocked || $item->attendance_status === 'punch_blocked')->count(),
        ];

        $attendances = $query->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());
        $this->normalizeAttendanceCollection($attendances->getCollection());

        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $attendanceTimes = AttendanceTime::where('is_active', true)->orderByDesc('is_default')->get();
        $canManageAttendance = $this->canManageAttendance();
        $canUnlockAttendance = $this->canUnlockAttendance();
        $blockedAttendances = $this->scopeAttendanceQuery($this->baseQuery(), 'attendance.records.view_all', 'attendance.regularization.view_team')
            ->whereDate('attendance_date', $today)
            ->where(function ($q) {
                $q->where('is_punch_blocked', true)
                    ->orWhere('is_blocked', true)
                    ->orWhere('attendance_status', 'punch_blocked');
            })
            ->orderBy('id')
            ->get();
        $this->normalizeAttendanceCollection($blockedAttendances);

        foreach ($blockedAttendances as $blocked) {
            $empId = $blocked->employee_id;
            $attDate = $blocked->attendance_date ? Carbon::parse($blocked->attendance_date)->toDateString() : null;
            $attId = is_numeric($blocked->id) ? $blocked->id : null;

            $regRequest = null;
            if ($empId && $attDate) {
                $regRequest = DB::table('attendance_regularizations')
                    ->where('employee_id', $empId)
                    ->whereNull('deleted_at')
                    ->where(function ($q) use ($attDate, $attId) {
                        if ($attId) {
                            $q->where('attendance_id', $attId);
                        }
                        $q->orWhereDate('requested_punch_in', $attDate)
                          ->orWhereDate('requested_punch_out', $attDate)
                          ->orWhereDate('created_at', $attDate);
                    })
                    ->latest('id')
                    ->first();
            }
            $blocked->regularization_request = $regRequest;
        }

        return view('hrms.attendance.index', compact(
            'attendances',
            'employees',
            'attendanceTypes',
            'attendanceTimes',
            'stats',
            'blockedAttendances',
            'canManageAttendance',
            'canUnlockAttendance'
        ));
    }

    public function daily(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.records.view_all') || $this->userHasPermission('attendance.my.view'), 403);
        $query = $this->scopeAttendanceQuery($this->applyFilters($this->baseQuery(), $request), 'attendance.records.view_all');

        $attendances = $this->orderAttendanceQuery($query, $request)->paginate(50)->appends($request->query());
        $this->normalizeAttendanceCollection($attendances->getCollection());
        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $attendanceTimes = AttendanceTime::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $departments = DepartmentM::orderBy('name')->get();
        $canManageAttendance = $this->canManageAttendance();

        return view('hrms.attendance.daily', compact('attendances', 'employees', 'attendanceTypes', 'attendanceTimes', 'departments', 'canManageAttendance'));
    }

    public function attendanceRecord(Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.records.view_all')
                || $this->userHasPermission('attendance.my.view')
                || $this->userHasPermission('attendance.regularization.view_team'),
            403
        );

        $allPermission = request()->routeIs('hrms.attendance.my') ? 'attendance.__never_all' : 'attendance.records.view_all';
        $teamPermission = request()->routeIs('hrms.attendance.my') ? null : 'attendance.regularization.view_team';
        $query = $this->scopeAttendanceQuery($this->applyFilters($this->baseQuery(), $request), $allPermission, $teamPermission);

        $perPage = 50;
        if ($request->filled('per_page')) {
            $perPage = ($request->per_page === 'all' || $request->per_page == '-1') ? 5000 : (int) $request->per_page;
        }

        $attendances = $this->orderAttendanceQuery($query, $request)->paginate($perPage)->appends($request->query());
        $this->normalizeAttendanceCollection($attendances->getCollection());
        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $attendanceTimes = AttendanceTime::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $departments = DepartmentM::orderBy('name')->get();
        $canManageAttendance = $this->canManageAttendance();

        return view('hrms.attendance.record', compact('attendances', 'employees', 'attendanceTypes', 'attendanceTimes', 'departments', 'canManageAttendance'));
    }

    public function unlock(Request $request)
    {
        abort_unless($this->canUnlockAttendance(), 403, 'Only HR/Admin can unlock attendance.');

        $request->validate([
            'id' => 'required|string',
            'unlock_type' => 'required|in:unlock_only,late_exemption,manual_punch_in',
            'unlock_reason_category' => 'nullable|string|max:255',
            'unlock_remarks' => 'nullable|string|max:2000',
            'hr_approval_note' => 'nullable|string|max:2000',
            'approved_punch_in_time' => 'required_if:unlock_type,manual_punch_in|nullable',
        ]);

        $result = $this->attendanceService->unlockAttendance($request->id, auth()->id(), $request->only([
            'unlock_type',
            'unlock_reason_category',
            'unlock_remarks',
            'hr_approval_note',
            'approved_punch_in_time',
        ]));

        if (($result['status'] ?? null) !== 'error') {
            return back()->with('status', $result['message']);
        }
        return back()->with('error', $result['message']);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can override attendance.');

        $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'type' => 'required|in:in,out',
            'time' => 'required',
            'task_summary' => 'required_if:type,out',
        ]);

        $employee = \App\Models\HRMS\Employee\EmployeeM::find($request->employee_id);
        $customTime = Carbon::parse($request->time)->format('Y-m-d H:i:s');

        if ($request->type === 'in') {
            $result = $this->attendanceService->processPunchIn(
                $employee->user_id,
                $request->work_mode ?? 'wfo',
                $request->note ?? 'Admin Punch In',
                ['ip' => $request->ip(), 'device' => 'Admin Panel'],
                $customTime,
                null,
                false
            );
        } else {
            $result = $this->attendanceService->processPunchOut(
                $employee->user_id,
                $request->task_summary,
                $request->note ?? 'Admin Punch Out',
                ['ip' => $request->ip(), 'device' => 'Admin Panel'],
                $customTime
            );
        }

        if (($result['success'] ?? $result['status'] ?? false) !== 'error' && (bool) ($result['success'] ?? $result['status'] ?? false)) {
            return back()->with('status', $result['message']);
        }
        return back()->with('error', $result['message']);
    }

    public function update(Request $request)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance history.');

        $request->validate([
            'id' => 'required|exists:attendances,id',
            'attendance_type_id' => 'required|exists:attendance_types,id',
            'punch_in_time' => 'nullable',
            'punch_out_time' => 'nullable',
            'hr_approval_note' => 'nullable|string|max:2000',
        ]);

        $attendance = Attendance::findOrFail($request->id);
        $attendance->update([
            'attendance_type_id' => $request->attendance_type_id,
            'punch_in_time' => $request->filled('punch_in_time') ? Carbon::parse($request->punch_in_time)->format('H:i:s') : $attendance->punch_in_time,
            'punch_out_time' => $request->filled('punch_out_time') ? Carbon::parse($request->punch_out_time)->format('H:i:s') : $attendance->punch_out_time,
            'hr_approval_note' => $request->hr_approval_note,
            'hr_approved_by' => auth()->id(),
            'hr_approved_at' => now(),
        ]);

        if ($attendance->punch_in_time && $attendance->punch_out_time) {
            $this->attendanceService->calculateAttendanceStats($attendance);
        }

        return back()->with('status', 'Attendance updated successfully.');
    }

    public function adminPunchIn(Request $request)
    {
        // Wrapper for store method with type=in
        $request->merge(['type' => 'in']);
        return $this->store($request);
    }

    public function adminPunchOut(Request $request)
    {
        // Wrapper for store method with type=out
        $request->merge(['type' => 'out']);
        return $this->store($request);
    }

    public function pendingApproval(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.blocked.view'), 403);

        $query = $this->scopeAttendanceQuery($this->baseQuery(), 'attendance.records.view_all', 'attendance.regularization.view_team')
            ->where(function ($query) {
                $query->where('is_blocked', true)
                    ->orWhere('is_punch_blocked', true)
                    ->orWhere('attendance_status', 'punch_blocked')
                    ->orWhere('attendance_status', 'unlocked')
                    ->orWhere('is_admin_unlocked', true)
                    ->orWhereNotNull('unlocked_at')
                    ->orWhere('missed_punch', true);
            })
            ->where(function ($q) use ($request) {
                if ($request->flag === 'unlocked') {
                    $q->where(function ($sq) {
                        $sq->where('is_admin_unlocked', true)
                            ->orWhereNotNull('unlocked_at')
                            ->orWhere('attendance_status', 'unlocked');
                    });
                } elseif ($request->flag === 'blocked') {
                    $q->where(function ($sq) {
                        $sq->whereNull('is_admin_unlocked')
                            ->orWhere('is_admin_unlocked', false)
                            ->orWhere('is_admin_unlocked', 0);
                    })->whereNull('unlocked_at');
                }
            })
            ->when($request->flag === 'manual_punch_in', fn($q) => $q->where('unlock_type', 'manual_punch_in'));

        // Query blocked_punch violations from attendance_violations table
        $violationQuery = \App\Models\HRMS\Attendance\AttendanceViolationM::with(['employee.user', 'employee.department'])
            ->where('type', 'blocked_punch')
            ->where(function ($q) use ($request) {
                if ($request->flag === 'unlocked') {
                    $q->where('policy_action', 'resolved');
                } elseif ($request->flag === 'blocked') {
                    $q->where(function ($sq) {
                        $sq->whereNull('policy_action')
                            ->orWhere('policy_action', '<>', 'resolved');
                    });
                }
            });

        // Apply same filters to violation query
        if ($request->filled('search')) {
            $search = $request->search;
            $violationQuery->whereHas('employee', function ($eq) use ($search) {
                $eq->where('employee_code', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('employee_id')) {
            $violationQuery->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $violationQuery->whereHas('employee', fn($eq) => $eq->where('department_id', $request->department_id));
        }

        if ($request->filled('date')) {
            $violationQuery->whereDate('violation_date', $request->date);
        } elseif ($request->filled('from_date')) {
            $violationQuery->whereDate('violation_date', '>=', $request->from_date);
            if ($request->filled('to_date')) {
                $violationQuery->whereDate('violation_date', '<=', $request->to_date);
            }
        } else {
            $today = Carbon::now($this->attendanceService->attendanceTimezone())->toDateString();
            if ($request->filter === 'today') {
                $violationQuery->whereDate('violation_date', $today);
            } elseif ($request->filter === 'yesterday') {
                $violationQuery->whereDate('violation_date', Carbon::yesterday()->toDateString());
            }
        }

        $blockedViolations = $violationQuery->get();

        $presentType = AttendanceType::where('code', 'present')->first();
        $blockedType = AttendanceType::where('code', 'punch_blocked')->first();

        $virtualAttendances = $blockedViolations->map(function ($violation) use ($presentType, $blockedType) {
            $att = new Attendance();
            $att->id = 'violation_' . $violation->id;
            $att->employee_id = $violation->employee_id;
            $att->attendance_date = $violation->violation_date->toDateString();
            $att->attendance_status = $violation->policy_action === 'resolved' ? 'unlocked' : 'punch_blocked';
            $att->is_blocked = $violation->policy_action !== 'resolved';
            $att->is_punch_blocked = $violation->policy_action !== 'resolved';
            $att->is_admin_unlocked = $violation->policy_action === 'resolved';
            $att->unlocked_at = $violation->policy_action === 'resolved' ? $violation->updated_at : null;
            $att->block_reason = $violation->remarks ?: 'Punch-in blocked after allowed time.';
            $att->auto_block_reason = $violation->remarks ?: 'Punch-in blocked after allowed time.';
            $att->blocked_reason = $violation->remarks ?: 'Punch-in blocked after allowed time.';

            $att->setRelation('employee', $violation->employee);
            if ($violation->employee) {
                $att->setRelation('user', $violation->employee->user);
            }

            $att->setRelation('attendanceType', $violation->policy_action === 'resolved' ? $presentType : $blockedType);

            return $att;
        });

        // Now get the real ones
        $realAttendances = $this->applyFilters($query, $request)->get();

        // Merge, sort latest on top (date desc, timestamp desc, id desc), and paginate manually
        $merged = $realAttendances->concat($virtualAttendances)
            ->sortByDesc(function ($item) {
                $dateTs = $item->attendance_date ? Carbon::parse($item->attendance_date)->timestamp : 0;
                $timeTs = ($item->unlocked_at ?? $item->updated_at ?? $item->created_at) ? Carbon::parse($item->unlocked_at ?? $item->updated_at ?? $item->created_at)->timestamp : 0;
                $idNum = (int) preg_replace('/[^0-9]/', '', (string) $item->id);
                return [$dateTs, $timeTs, $idNum];
            })->values();

        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 25;
        $currentPageItems = $merged->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        $attendances->appends($request->all());

        $this->normalizeAttendanceCollection($attendances->getCollection());

        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $canManageAttendance = $this->canManageAttendance();
        $canUnlockAttendance = $this->canUnlockAttendance();

        $approvalRecords = Attendance::with('attendanceType')->get();
        $today = Carbon::now($this->attendanceService->attendanceTimezone())->toDateString();

        // Count stats including violations
        $totalBlockedViolations = \App\Models\HRMS\Attendance\AttendanceViolationM::where('type', 'blocked_punch')->count();
        $pendingUnlockViolations = \App\Models\HRMS\Attendance\AttendanceViolationM::where('type', 'blocked_punch')
            ->where(function ($q) {
                $q->whereNull('policy_action')->orWhere('policy_action', '<>', 'resolved');
            })
            ->count();
        $unlockedTodayViolations = \App\Models\HRMS\Attendance\AttendanceViolationM::where('type', 'blocked_punch')
            ->where('policy_action', 'resolved')
            ->whereDate('updated_at', $today)
            ->count();

        $stats = [
            'total_blocked' => $approvalRecords->filter(fn($item) => $item->is_punch_blocked || $item->is_blocked || $item->attendance_status === 'punch_blocked')->count() + $totalBlockedViolations,
            'pending_unlock' => $approvalRecords->filter(fn($item) => ($item->is_blocked || $item->is_punch_blocked || $item->attendance_status === 'punch_blocked') && ! $item->is_admin_unlocked)->count() + $pendingUnlockViolations,
            'pending_hr' => $approvalRecords->where('attendance_status', 'pending_hr')->count(),
            'missed_punch' => $approvalRecords->where('missed_punch', true)->count(),
            'manual_punch' => $approvalRecords->where('unlock_type', 'manual_punch_in')->count(),
            'unlocked_today' => $approvalRecords->filter(fn($item) => $item->unlocked_at && Carbon::parse($item->unlocked_at)->toDateString() === $today)->count() + $unlockedTodayViolations,
        ];

        return view('hrms.attendance.pending-approval', compact('attendances', 'employees', 'attendanceTypes', 'stats', 'canManageAttendance', 'canUnlockAttendance'));
    }

    public function monthlyReport(Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.monthly_report.view_all')
                || $this->userHasPermission('attendance.monthly_report.view_team')
                || $this->userHasPermission('attendance.monthly_report.view_own')
                || $this->userHasPermission('attendance.monthly_report.view'),
            403
        );

        $month = (int) ($request->month ?: now()->month);
        $year = (int) ($request->year ?: now()->year);

        $query = $this->scopeAttendanceQuery($this->baseQuery(), 'attendance.monthly_report.view_all', 'attendance.monthly_report.view_team')
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year);

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $attendances = $this->applyFilters($query, $request)
            ->orderBy('attendance_date')
            ->get();
        $this->normalizeAttendanceCollection($attendances);

        $summary = [
            'present' => 0,
            'absent' => 0,
            'half_day' => 0,
            'leave' => 0,
            'week_off' => 0,
            'punch_blocked' => 0,
            'late' => 0,
            'early_out' => 0,
            'total_hours' => 0,
        ];

        $employeeData = [];

        foreach ($attendances as $att) {
            $typeCode = optional($att->attendanceType)->code;

            // Global summary
            if ($typeCode === 'present') $summary['present']++;
            if ($typeCode === 'absent') $summary['absent']++;
            if ($typeCode === 'half_day') $summary['half_day']++;
            if ($typeCode === 'leave') $summary['leave']++;
            if ($typeCode === 'week_off') $summary['week_off']++;
            if ($typeCode === 'punch_blocked') $summary['punch_blocked']++;

            if ($att->is_late) $summary['late']++;
            if ($att->is_early_out) $summary['early_out']++;
            $summary['total_hours'] += ($att->total_work_minutes / 60);

            // Per employee row
            $empId = $att->employee_id;
            if (!isset($employeeData[$empId])) {
                $employeeData[$empId] = [
                    'employee_id' => $empId,
                    'employee_name' => optional($att->user)->name ?? 'N/A',
                    'employee_code' => optional($att->employee)->employee_code ?? 'N/A',
                    'department_name' => optional(optional($att->employee)->department)->name ?? 'N/A',
                    'present' => 0,
                    'absent' => 0,
                    'half_day' => 0,
                    'leave' => 0,
                    'week_off' => 0,
                    'late' => 0,
                    'early_out' => 0,
                    'total_hours' => 0,
                ];
            }

            if ($typeCode === 'present') $employeeData[$empId]['present']++;
            if ($typeCode === 'absent') $employeeData[$empId]['absent']++;
            if ($typeCode === 'half_day') $employeeData[$empId]['half_day']++;
            if ($typeCode === 'leave') $employeeData[$empId]['leave']++;
            if ($typeCode === 'week_off') $employeeData[$empId]['week_off']++;
            if ($att->is_late) $employeeData[$empId]['late']++;
            if ($att->is_early_out) $employeeData[$empId]['early_out']++;
            $employeeData[$empId]['total_hours'] += ($att->total_work_minutes / 60);
        }

        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $departments = DepartmentM::orderBy('name')->get();
        $employeeRows = array_values($employeeData);

        return view('hrms.attendance.monthly-report', compact(
            'attendances',
            'employees',
            'attendanceTypes',
            'departments',
            'month',
            'year',
            'summary',
            'employeeRows'
        ));
    }

    public function policies()
    {
        $attendancePolicies = AttendancePolicyRule::orderByDesc('is_active')->orderBy('policy_name')->get();
        return view('hrms.attendance.policies', compact('attendancePolicies'));
    }

    public function rules()
    {
        $attendanceTimes = AttendanceTime::withCount(['employeeShiftTimings as active_assigned_count' => function ($q) {
            $q->where('is_active', true);
        }])->orderByDesc('is_default')->orderBy('name')->get();

        $employeeShiftTimings = EmployeeShiftTimingM::with(['employee.user', 'attendanceTime'])
            ->orderByDesc('is_active')
            ->orderByDesc('effective_from')
            ->get();

        $employees = EmployeeM::with('user')->where('employment_status', 'active')->orderBy('id')->get();

        $attendancePolicies = AttendancePolicyRule::orderByDesc('is_active')->orderBy('policy_name')->get();

        return view('hrms.attendance.rules', compact('attendanceTimes', 'employeeShiftTimings', 'employees', 'attendancePolicies'));
    }

    public function updateRule(Request $request, AttendanceTime $attendanceTime)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance rules.');

        $isFlexible = $request->input('shift_type') === 'flexible_part_time';

        $data = $request->validate([
            'name' => 'required|string',
            'shift_type' => 'nullable|string|in:fixed,flexible_part_time',
            'punch_allowed_from' => $isFlexible ? 'nullable' : 'required',
            'shift_start_time' => $isFlexible ? 'nullable' : 'required',
            'shift_end_time' => $isFlexible ? 'nullable' : 'required',
            'late_after_time' => $isFlexible ? 'nullable' : 'required',
            'warning_after_time' => 'nullable',
            'block_after_time' => 'nullable',
            'half_day_after_time' => 'nullable',
            'required_work_minutes' => 'required|integer',
            'half_day_min_minutes' => 'required|integer',
            'absent_below_minutes' => 'nullable|integer',
            'lunch_break_minutes' => 'required|integer',
            'break_minutes' => 'nullable|integer',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data['shift_type'] = $request->input('shift_type', 'fixed');
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');
        if ($request->filled('lunch_break_minutes')) {
            $data['break_minutes'] = $request->input('lunch_break_minutes');
        }

        $attendanceTime->update($data);
        return back()->with('status', 'Shift rule updated successfully.');
    }

    public function storeEmployeeShift(Request $request)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin / HR can assign employee shifts.');

        $data = $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'attendance_time_id' => 'required|exists:attendance_times,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'punch_allowed_from' => 'nullable',
            'shift_start_time' => 'nullable',
            'late_after_time' => 'nullable',
            'half_day_after_time' => 'nullable',
            'shift_end_time' => 'nullable',
            'required_work_minutes' => 'nullable|integer',
            'lunch_minutes' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = auth()->id();

        if ($data['is_active']) {
            EmployeeShiftTimingM::where('employee_id', $data['employee_id'])
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        EmployeeShiftTimingM::create($data);

        return back()->with('status', 'Employee shift timing assigned successfully.');
    }

    public function updateEmployeeShift(Request $request, EmployeeShiftTimingM $employeeShiftTiming)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin / HR can modify employee shifts.');

        $data = $request->validate([
            'attendance_time_id' => 'required|exists:attendance_times,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'punch_allowed_from' => 'nullable',
            'shift_start_time' => 'nullable',
            'late_after_time' => 'nullable',
            'half_day_after_time' => 'nullable',
            'shift_end_time' => 'nullable',
            'required_work_minutes' => 'nullable|integer',
            'lunch_minutes' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = auth()->id();

        $employeeShiftTiming->update($data);

        return back()->with('status', 'Employee shift timing updated successfully.');
    }

    public function storePolicyRule(Request $request)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance policy rules.');

        AttendancePolicyRule::create($this->validatedPolicyRule($request));

        return back()->with('status', 'Attendance policy rule created successfully.');
    }

    public function updatePolicyRule(Request $request, AttendancePolicyRule $attendancePolicyRule)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance policy rules.');

        $attendancePolicyRule->update($this->validatedPolicyRule($request));

        return back()->with('status', 'Attendance policy rule updated successfully.');
    }

    public function types()
    {
        $attendanceTypes = AttendanceType::withCount('attendances')->orderBy('name')->get();
        return view('hrms.attendance.types', compact('attendanceTypes'));
    }

    public function storeType(Request $request)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance status types.');

        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:attendance_types,code',
            'is_paid' => 'nullable|boolean',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        $data['is_paid'] = $request->boolean('is_paid');
        $data['is_active'] = $request->boolean('is_active');

        AttendanceType::create($data);
        return back()->with('status', 'Attendance type created successfully.');
    }

    public function updateType(Request $request, AttendanceType $attendanceType)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance status types.');

        $data = $request->validate([
            'name' => 'required|string',
            'code' => ['required', 'string', Rule::unique('attendance_types')->ignore($attendanceType->id)],
            'is_paid' => 'nullable|boolean',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        $data['is_paid'] = $request->boolean('is_paid');
        $data['is_active'] = $request->boolean('is_active');

        $attendanceType->update($data);
        return back()->with('status', 'Attendance type updated successfully.');
    }

    public function destroyType(AttendanceType $attendanceType)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance status types.');

        if ($attendanceType->attendances()->exists()) {
            return back()->with('error', 'Cannot delete type that has attendance records.');
        }
        $attendanceType->delete();
        return back()->with('status', 'Attendance type deleted successfully.');
    }

    public function print(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.export'), 403);
        $attendances = $this->orderAttendanceQuery($this->scopeAttendanceQuery($this->applyFilters($this->baseQuery(), $request), 'attendance.records.view_all', 'attendance.monthly_report.view_team'), $request)->get();
        $this->normalizeAttendanceCollection($attendances);

        $periodLabel = 'All Records';
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $periodLabel = Carbon::parse($request->from_date)->format('d M Y') . ' - ' . Carbon::parse($request->to_date)->format('d M Y');
        } elseif ($request->filled('from_date')) {
            $periodLabel = 'From ' . Carbon::parse($request->from_date)->format('d M Y');
        } elseif ($request->filled('to_date')) {
            $periodLabel = 'Up to ' . Carbon::parse($request->to_date)->format('d M Y');
        } elseif ($request->filled('date')) {
            $periodLabel = Carbon::parse($request->date)->format('d M Y');
        } elseif ($request->filled('month') && $request->filled('year')) {
            $periodLabel = Carbon::create((int) $request->year, (int) $request->month, 1)->format('F Y');
        }

        return view('hrms.attendance.attendances_print', compact('attendances', 'periodLabel'));
    }

    public function exportPdf(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.export'), 403);
        $attendances = $this->orderAttendanceQuery($this->scopeAttendanceQuery($this->applyFilters($this->baseQuery(), $request), 'attendance.records.view_all', 'attendance.monthly_report.view_team'), $request)->get();
        $this->normalizeAttendanceCollection($attendances);

        $periodLabel = 'All Records';
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $periodLabel = Carbon::parse($request->from_date)->format('d M Y') . ' - ' . Carbon::parse($request->to_date)->format('d M Y');
        } elseif ($request->filled('from_date')) {
            $periodLabel = 'From ' . Carbon::parse($request->from_date)->format('d M Y');
        } elseif ($request->filled('to_date')) {
            $periodLabel = 'Up to ' . Carbon::parse($request->to_date)->format('d M Y');
        } elseif ($request->filled('date')) {
            $periodLabel = Carbon::parse($request->date)->format('d M Y');
        } elseif ($request->filled('month') && $request->filled('year')) {
            $periodLabel = Carbon::create((int) $request->year, (int) $request->month, 1)->format('F Y');
        }

        $pdf = Pdf::loadView('hrms.attendance.attendance_pdf', compact('attendances', 'periodLabel'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('attendance_report_' . date('Y_m_d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.export'), 403);
        $rows = $this->orderAttendanceQuery($this->scopeAttendanceQuery(
            $this->applyFilters($this->baseQuery(), $request),
            'attendance.records.view_all',
            'attendance.monthly_report.view_team'
        ), $request)->get();

        $this->normalizeAttendanceCollection($rows);

        $filename = 'attendance_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'S.No',
                'Employee Name',
                'Employee Code',
                'Department',
                'Shift',
                'Date',
                'Work Mode',
                'Punch In',
                'Punch Out',
                'Target Out',
                'Gross Duration',
                'Net Duration',
                'Status',
                'Late Minutes',
                'Early Out Minutes',
                'Flags / Remarks'
            ]);

            foreach ($rows as $index => $row) {
                $flags = [];
                if ($row->is_late) {
                    $flags[] = 'Late ' . ($row->late_minutes ?? 0) . 'm';
                }
                if ($row->is_early_out) {
                    $flags[] = 'Early ' . ($row->early_out_minutes ?? 0) . 'm';
                }
                if ($row->is_blocked || $row->is_punch_blocked) {
                    $flags[] = 'Punch Blocked';
                }
                if ($row->missed_punch || $row->is_missed_punch) {
                    $flags[] = 'Missed Punch';
                }
                $flagStr = !empty($flags) ? implode(', ', $flags) : 'Clear';

                fputcsv($handle, [
                    $index + 1,
                    optional($row->user)->name ?? optional($row->employee)->display_name ?? 'N/A',
                    optional($row->employee)->employee_code ?? 'N/A',
                    optional(optional($row->employee)->department)->name ?? 'Staff',
                    optional($row->attendanceTime)->name ?? 'Default Shift',
                    optional($row->attendance_date)->format('d M Y') ?? '-',
                    strtoupper($row->work_mode ?? 'WFO'),
                    $row->punch_in_time ? Carbon::parse($row->punch_in_time)->format('h:i A') : '-',
                    $row->punch_out_time ? Carbon::parse($row->punch_out_time)->format('h:i A') : '-',
                    $row->target_punch_out_time ? Carbon::parse($row->target_punch_out_time)->format('h:i A') : '-',
                    $row->gross_duration ?? '-',
                    $row->net_duration ?? '-',
                    optional($row->attendanceType)->name ?? ucwords(str_replace('_', ' ', $row->attendance_status ?? 'N/A')),
                    (int) ($row->late_minutes ?? 0),
                    (int) ($row->early_out_minutes ?? 0),
                    $flagStr
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Request $request)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can delete attendance history.');

        $request->validate(['id' => 'required|exists:attendances,id']);
        Attendance::findOrFail($request->id)->delete();

        return back()->with('status', 'Attendance record deleted successfully.');
    }

    // Helper Methods
    private function attendanceEmployees()
    {
        $user = auth()->user();
        $isEmployeeRole = ($user->role_id ?? null) == 7 
            || ($user->system_role_id ?? null) == 7;

        $query = User::whereHas('employee')->with('employee')->orderBy('name');
        if ($isEmployeeRole || (! $this->canViewAll('attendance.records.view_all') && ! $this->canViewAll('attendance.monthly_report.view_all'))) {
            $ids = ($this->userHasPermission('attendance.monthly_report.view_team') || $this->userHasPermission('attendance.regularization.view_team')) && ! $isEmployeeRole
                ? $this->teamEmployeeIds(true)
                : array_filter([$this->ownEmployeeId()]);
            $query->whereHas('employee', fn($employeeQuery) => $employeeQuery->whereIn('id', $ids));
        }

        return $query->get();
    }

    private function scopeAttendanceQuery($query, string $allPermission, ?string $teamPermission = null)
    {
        return $this->scopeEmployeeVisibility($query, $allPermission, $teamPermission, 'employee_id');
    }

    private function activeAttendanceTypes()
    {
        return AttendanceType::where('is_active', true)->orderBy('name')->get();
    }

    private function reportPeriodLabel($month, $year)
    {
        return Carbon::create($year, $month, 1)->format('F Y');
    }

    private function canManageAttendance(): bool
    {
        return $this->userHasPermission('attendance.rules.manage')
            || $this->userHasPermission('attendance.records.manage')
            || (bool) (auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
            || (bool) (auth()->user() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin());
    }

    private function canUnlockAttendance(): bool
    {
        return $this->userHasPermission('attendance.blocked.unlock')
            || (bool) (auth()->user() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin());
    }

    private function validatedPolicyRule(Request $request): array
    {
        $data = $request->validate([
            'policy_name' => 'required|string|max:255',
            'punch_allowed_from' => 'nullable',
            'shift_start_time' => 'nullable',
            'late_after_time' => 'nullable',
            'warning_after_time' => 'nullable',
            'block_after_time' => 'nullable',
            'shift_end_time' => 'nullable',
            'required_work_minutes' => 'nullable|integer|min:0',
            'half_day_min_minutes' => 'nullable|integer|min:0',
            'absent_below_minutes' => 'nullable|integer|min:0',
            'early_out_half_day_minutes' => 'nullable|integer|min:0',
            'missed_punch_after_minutes' => 'nullable|integer|min:0',
            'allowed_missed_punches' => 'nullable|integer|min:0',
            'combined_violation_limit' => 'nullable|integer|min:0',
            'late_violation_limit' => 'nullable|integer|min:0',
            'early_violation_limit' => 'nullable|integer|min:0',
            'missed_punch_lwp_after' => 'nullable|integer|min:0',
            'monthly_wfh_limit' => 'nullable|integer|min:0',
            'punch_block_enabled' => 'nullable|boolean',
            'auto_block_enabled' => 'nullable|boolean',
            'auto_absent_enabled' => 'nullable|boolean',
            'wfh_enabled' => 'nullable|boolean',
            'regularization_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        foreach (['punch_block_enabled', 'auto_block_enabled', 'auto_absent_enabled', 'wfh_enabled', 'regularization_enabled', 'is_active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        if (isset($data['auto_block_enabled']) && ! isset($data['punch_block_enabled'])) {
            $data['punch_block_enabled'] = $data['auto_block_enabled'];
        }

        return collect($data)
            ->filter(fn($val, $col) => Schema::hasColumn('attendance_policy_rules', $col))
            ->all();
    }

    private function orderAttendanceQuery($query, Request $request)
    {
        if ($request->filled('from_date') || $request->filled('date') || $request->filled('to_date') || $request->filled('month')) {
            return $query->orderBy('attendance_date', 'asc')->orderBy('id', 'asc');
        }
        return $query->orderByDesc('attendance_date')->orderByDesc('id');
    }

    private function normalizeAttendanceCollection(Collection $items): void
    {
        $typeCache = [];

        foreach ($items as $attendance) {
            $resolved = $this->attendanceService->resolveFinalStatus($attendance);
            $resolvedCode = (string) ($resolved['status_code'] ?? '');
            if ($resolvedCode === '') {
                continue;
            }

            $attendance->attendance_status = $resolvedCode;
            $attendance->status_code = $resolvedCode;
            $attendance->status_name = $resolved['status_name'] ?? ucwords(str_replace('_', ' ', $resolvedCode));

            if (! isset($typeCache[$resolvedCode])) {
                $typeCache[$resolvedCode] = AttendanceType::where('code', $resolvedCode)->first();
            }

            if ($typeCache[$resolvedCode]) {
                $attendance->setRelation('attendanceType', $typeCache[$resolvedCode]);
            }
        }
    }

    public function accessControl(Request $request)
    {
        $query = DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
            ->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id')
            ->select([
                'employees_new.id',
                'employees_new.employee_code',
                'employees_new.user_id',
                'employees_new.work_mode',
                'employees_new.allow_mobile_attendance',
                'employees_new.allow_web_attendance',
                'employees_new.department_id',
                'employees_new.designation_id',
                'users.name as user_name',
                'users.email as user_email',
                'users.is_app_access',
                'users.is_web_access',
                'departments.name as department_name',
                'designations.name as designation_name',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('employees_new.employee_code', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('employees_new.department_id', $request->department_id);
        }

        if ($request->filled('designation_id')) {
            $query->where('employees_new.designation_id', $request->designation_id);
        }

        if ($request->filled('web_attendance')) {
            $val = $request->web_attendance === '1' || $request->web_attendance === 'yes';
            $query->where('employees_new.allow_web_attendance', $val ? 1 : 0);
        }

        if ($request->filled('mobile_attendance')) {
            $val = $request->mobile_attendance === '1' || $request->mobile_attendance === 'yes';
            $query->where('employees_new.allow_mobile_attendance', $val ? 1 : 0);
        }

        $employees = $query->orderBy('users.name')->paginate(50)->appends($request->all());
        $departments = DB::table('departments')->orderBy('name')->pluck('name', 'id')->toArray();
        $designations = DB::table('designations')->orderBy('name')->pluck('name', 'id')->toArray();

        return view('hrms.attendance.access-control', compact('employees', 'departments', 'designations'));
    }

    public function updateAccessControl(Request $request, $id)
    {
        $request->validate([
            'allow_mobile_attendance' => 'nullable|boolean',
            'allow_web_attendance' => 'nullable|boolean',
            'is_app_access' => 'nullable|boolean',
            'is_web_access' => 'nullable|boolean',
        ]);

        $employee = DB::table('employees_new')->where('id', $id)->first();
        if (! $employee) {
            return back()->with('error', 'Employee not found.');
        }

        $updateData = [];
        if ($request->has('allow_mobile_attendance')) {
            $updateData['allow_mobile_attendance'] = $request->boolean('allow_mobile_attendance');
        }
        if ($request->has('allow_web_attendance')) {
            $updateData['allow_web_attendance'] = $request->boolean('allow_web_attendance');
        }

        if (! empty($updateData)) {
            $updateData['updated_at'] = now();
            DB::table('employees_new')->where('id', $id)->update($updateData);
        }

        if ($employee->user_id && ($request->has('is_app_access') || $request->has('is_web_access'))) {
            $userUpdate = [];
            if ($request->has('is_app_access')) {
                $userUpdate['is_app_access'] = $request->boolean('is_app_access') ? 1 : 0;
            }
            if ($request->has('is_web_access')) {
                $userUpdate['is_web_access'] = $request->boolean('is_web_access') ? 1 : 0;
            }
            if (! empty($userUpdate)) {
                $userUpdate['updated_at'] = now();
                DB::table('users')->where('id', $employee->user_id)->update($userUpdate);
            }
        }

        if ($employee->user_id) {
            app(\App\Services\Core\Menu\SidebarMenuResolverS::class)->clearCache((int) $employee->user_id);
        }

        return back()->with('success', 'Access updated for ' . ($employee->employee_code ?? 'Employee'));
    }

    public function bulkUpdateAccessControl(Request $request)
    {
        $request->validate([
            'update_target' => 'required|in:selected,department,designation,all',
            'employee_ids' => 'required_if:update_target,selected|array',
            'employee_ids.*' => 'integer|exists:employees_new,id',
            'department_id' => 'required_if:update_target,department|nullable|integer',
            'designation_id' => 'required_if:update_target,designation|nullable|integer',
            'allow_mobile_attendance' => 'required|in:keep,enable,disable',
            'allow_web_attendance' => 'required|in:keep,enable,disable',
        ]);

        $query = DB::table('employees_new');

        if ($request->update_target === 'selected') {
            $query->whereIn('id', $request->input('employee_ids', []));
        } elseif ($request->update_target === 'department' && $request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        } elseif ($request->update_target === 'designation' && $request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        $updateData = ['updated_at' => now()];

        if ($request->allow_mobile_attendance === 'enable') {
            $updateData['allow_mobile_attendance'] = 1;
        } elseif ($request->allow_mobile_attendance === 'disable') {
            $updateData['allow_mobile_attendance'] = 0;
        }

        if ($request->allow_web_attendance === 'enable') {
            $updateData['allow_web_attendance'] = 1;
        } elseif ($request->allow_web_attendance === 'disable') {
            $updateData['allow_web_attendance'] = 0;
        }

        if (count($updateData) > 1) {
            $userIds = (clone $query)->whereNotNull('user_id')->pluck('user_id');
            $count = $query->update($updateData);
            $resolver = app(\App\Services\Core\Menu\SidebarMenuResolverS::class);
            foreach ($userIds as $uId) {
                $resolver->clearCache((int) $uId);
            }
            return back()->with('success', "Attendance access updated for {$count} employees.");
        }

        return back()->with('info', 'No changes made.');
    }

    public function webClockIn(Request $request)
    {
        try {
            $request->validate([
                'work_mode' => 'nullable|string|in:wfo,wfh,WFO,WFH',
                'note' => 'nullable|string|max:1000',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'address' => 'nullable|string|max:2000',
                'browser' => 'nullable|string|max:255',
                'os' => 'nullable|string|max:255',
                'gps_status' => 'nullable|string|max:255',
            ]);

            $workMode = strtolower((string) $request->input('work_mode', 'wfo'));
            $lat = ($request->filled('latitude') && (float) $request->latitude !== 0.0) ? (float) $request->latitude : null;
            $lng = ($request->filled('longitude') && (float) $request->longitude !== 0.0) ? (float) $request->longitude : null;

            $meta = [
                'latitude' => $lat,
                'longitude' => $lng,
                'address' => $request->address,
                'ip' => $request->ip(),
                'device' => trim($request->userAgent() . ' | OS: ' . ($request->os ?? 'Unknown') . ' | Browser: ' . ($request->browser ?? 'Unknown') . ' | GPS: ' . ($request->gps_status ?? 'Unknown')),
                'attendance_source' => 'web',
                'source' => 'web',
            ];

            $result = $this->attendanceService->processPunchIn(
                auth()->id(),
                $workMode,
                $request->note,
                $meta
            );

            if (($result['status'] ?? null) === 'error') {
                return back()->with('error', $result['message'] ?? 'Punch in failed.');
            }

            return back()->with('success', $result['message'] ?? 'Punch in recorded successfully.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            Log::error('Web Punch In Exception: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Punch in error: ' . $e->getMessage());
        }
    }

    public function webClockOut(Request $request)
    {
        $request->validate([
            'task_summary' => 'nullable|string|max:10000',
            'task_name' => 'nullable|string|max:255',
            'today_work_description' => 'nullable|string|max:5000',
            'current_status' => 'nullable|string|max:50',
            'test_status' => 'nullable',
            'completed_tasks' => 'nullable|string|max:5000',
            'pending_tasks' => 'nullable|string|max:5000',
            'tomorrow_plan' => 'nullable|string|max:5000',
            'issues_blockers' => 'nullable|string|max:5000',
            'requirements' => 'nullable|array',
            'remarks' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string|max:2000',
            'browser' => 'nullable|string|max:255',
            'os' => 'nullable|string|max:255',
            'gps_status' => 'nullable|string|max:255',
        ]);

        $taskSummaryText = $request->task_summary;
        if (empty($taskSummaryText)) {
            $taskSummaryText = trim(($request->task_name ? '[' . $request->task_name . '] ' : '') . ($request->today_work_description ?? 'Daily Work Update Completed'));
        }

        // Filter out empty requirements
        $reqs = array_values(array_filter((array) ($request->requirements ?? []), function ($item) {
            return !empty(trim((string) $item));
        }));

        $testStatusVal = $request->test_status;
        if (is_array($testStatusVal)) {
            $testStatusVal = implode(', ', array_filter($testStatusVal));
        }

        $taskSummaryJson = [
            'task_name' => $request->task_name,
            'today_work_description' => $request->today_work_description,
            'current_status' => $request->current_status ?? 'Progress',
            'test_status' => $testStatusVal,
            'requirements' => $reqs,
            'completed_tasks' => $request->completed_tasks,
            'pending_tasks' => $request->pending_tasks,
            'tomorrow_plan' => $request->tomorrow_plan,
            'issues_blockers' => $request->issues_blockers,
            'remarks' => $request->remarks,
        ];

        $meta = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'ip' => $request->ip(),
            'device' => trim($request->userAgent() . ' | OS: ' . ($request->os ?? 'Unknown') . ' | Browser: ' . ($request->browser ?? 'Unknown') . ' | GPS: ' . ($request->gps_status ?? 'Unknown')),
            'attendance_source' => 'web',
            'source' => 'web',
        ];

        $result = $this->attendanceService->processPunchOut(
            auth()->id(),
            $taskSummaryText,
            $request->remarks,
            $meta,
            null,
            true,
            $taskSummaryJson
        );

        if (($result['status'] ?? null) === 'error') {
            return back()->with('error', $result['message'] ?? 'Punch out failed.');
        }

        return back()->with('success', $result['message'] ?? 'Punch out recorded successfully.');
    }

    public function today(Request $request)
    {
        $employee = DB::table('employees_new')->where('user_id', auth()->id())->first();
        if (! $employee) {
            return back()->with('error', 'Employee profile not found.');
        }

        $empObj = \App\Models\HRMS\Employee\EmployeeM::find($employee->id);
        $todayStatusResult = $this->attendanceService ? (new \App\Services\HRMS\Attendance\AttendanceMobileService($this->attendanceService, app(\App\Services\HRMS\Attendance\AttendanceRuleResolverService::class), app(\App\Services\HRMS\Attendance\WfhRequestService::class)))->todayStatus(auth()->id()) : [];
        $attendancePayload = $todayStatusResult['data'] ?? [];

        $todayDate = Carbon::now($this->attendanceService->attendanceTimezone())->toDateString();
        $attendanceRecord = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $todayDate)
            ->first();

        $workLogs = $attendanceRecord?->workLogs;
        $workSummaryLog = $workLogs?->first();

        return view('hrms.attendance.today', [
            'employee' => $empObj,
            'attendancePayload' => $attendancePayload,
            'attendanceRecord' => $attendanceRecord,
            'workSummaryLog' => $workSummaryLog,
            'canWebPunch' => $empObj ? $empObj->canUseWebAttendance() : false,
            'canMobilePunch' => $empObj ? $empObj->canUseMobileAttendance() : true,
        ]);
    }
}
