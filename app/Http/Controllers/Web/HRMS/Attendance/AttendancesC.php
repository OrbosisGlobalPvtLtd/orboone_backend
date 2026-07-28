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
use App\Services\HRMS\Attendance\AttendanceS;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    ->orWhere('missed_punch', true);
            })
            ->where(function ($q) use ($request) {
                if ($request->flag === 'unlocked') {
                    $q->where('is_admin_unlocked', true);
                } else {
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
                } else {
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

        // Merge, sort, and paginate manually
        $merged = $realAttendances->concat($virtualAttendances)
            ->sortByDesc('attendance_date');

        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
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
            ->where(function($q) {
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

    public function rules()
    {
        $attendanceTimes = AttendanceTime::orderByDesc('is_default')->orderBy('name')->get();
        $attendancePolicies = AttendancePolicyRule::orderByDesc('is_active')->orderBy('policy_name')->get();
        return view('hrms.attendance.rules', compact('attendanceTimes', 'attendancePolicies'));
    }

    public function updateRule(Request $request, AttendanceTime $attendanceTime)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance rules.');

        $data = $request->validate([
            'name' => 'required|string',
            'punch_allowed_from' => 'required',
            'shift_start_time' => 'required',
            'shift_end_time' => 'required',
            'late_after_time' => 'required',
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

        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');
        if ($request->filled('lunch_break_minutes')) {
            $data['break_minutes'] = $request->input('lunch_break_minutes');
        }

        $attendanceTime->update($data);
        return back()->with('status', 'Shift rule updated successfully.');
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
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

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
        $query = User::whereHas('employee')->with('employee')->orderBy('name');
        if (! $this->canViewAll('attendance.records.view_all') && ! $this->canViewAll('attendance.monthly_report.view_all')) {
            $ids = $this->userHasPermission('attendance.monthly_report.view_team') || $this->userHasPermission('attendance.regularization.view_team')
                ? $this->teamEmployeeIds(true)
                : array_filter([$this->ownEmployeeId()]);
            $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery->whereIn('id', $ids));
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
            'punch_allowed_from' => 'required',
            'shift_start_time' => 'required',
            'late_after_time' => 'required',
            'warning_after_time' => 'required',
            'block_after_time' => 'required',
            'shift_end_time' => 'required',
            'required_work_minutes' => 'required|integer|min:0',
            'half_day_min_minutes' => 'required|integer|min:0',
            'absent_below_minutes' => 'required|integer|min:0',
            'lunch_break_minutes' => 'required|integer|min:0',
            'allowed_missed_punches' => 'required|integer|min:0',
            'combined_violation_limit' => 'required|integer|min:0',
            'late_violation_limit' => 'required|integer|min:0',
            'early_violation_limit' => 'required|integer|min:0',
            'auto_block_enabled' => 'boolean',
            'auto_absent_enabled' => 'boolean',
            'is_active' => 'boolean',
        ]);

        foreach (['auto_block_enabled', 'auto_absent_enabled', 'is_active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        return $data;
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
}
