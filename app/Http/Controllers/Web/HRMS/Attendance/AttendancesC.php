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
use App\Services\HRMS\Attendance\AttendanceMobileService;
use App\Services\HRMS\Attendance\AttendanceRuleResolverService;
use App\Services\HRMS\Attendance\AttendanceService;
use App\Services\HRMS\Employee\EmployeeShiftAssignmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\HRMS\Team\TeamManagementScopeS;
use App\Models\HRMS\Attendance\AttendanceDailyStatusLogM;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Services\Core\Menu\SidebarMenuResolverS;
use Illuminate\Validation\ValidationException;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;

class AttendancesC extends Controller
{
    use HrmsCrudPage;

    private AttendanceService $attendanceService;
    private AttendanceMobileService $mobileService;

    public function __construct(
        AttendanceService $attendanceService,
        AttendanceMobileService $mobileService
    ) {
        $this->middleware('auth');
        $this->attendanceService = $attendanceService;
        $this->mobileService = $mobileService;
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
            'violations',
            'regularizations.approvedBy',
            'statusLogs.createdBy',
            'payrollImpacts',
            'leaveRequest.leaveType',
            'compOff',
        ]);
    }

    private function recordsQuery()
    {
        return Attendance::with([
            'user',
            'employee.department',
            'employee.designation',
            'attendanceType',
            'attendanceTime',
            'workLogs',
            'unlockedBy',
            'hrApprovedBy',
        ]);
    }

    private function applyFilters(mixed $query, Request $request)
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

        if ($request->filled('employee_id') && $request->employee_id !== 'all') {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn($employeeQuery) => $employeeQuery->where('department_id', $request->department_id));
        }

        if ($request->filled('attendance_time_id')) {
            $query->where('attendance_time_id', $request->attendance_time_id);
        }

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');

        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->date);
        } elseif ($fromDate || $toDate) {
            if ($fromDate) {
                $query->whereDate('attendance_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('attendance_date', '<=', $toDate);
            }
        } elseif ($request->filled('month_year') && $request->month_year !== 'all') {
            $parts = explode('-', $request->month_year);
            if (count($parts) === 2) {
                $year = (int) $parts[0];
                $month = (int) $parts[1];
                $query->whereYear('attendance_date', $year)->whereMonth('attendance_date', $month);
            }
        } elseif ($request->filled('month') && $request->month !== 'all' && $request->month !== 'custom') {
            if (str_contains($request->month, '-')) {
                $parts = explode('-', $request->month);
                if (count($parts) === 2) {
                    $query->whereYear('attendance_date', (int) $parts[0])->whereMonth('attendance_date', (int) $parts[1]);
                }
            } else {
                $query->whereMonth('attendance_date', (int) $request->month);
                if ($request->filled('year')) {
                    $query->whereYear('attendance_date', (int) $request->year);
                }
            }
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

        // Only active, non-exited employees with completed approved profiles
        $query = $this->scopeAttendanceQuery($this->applyFilters($this->baseQuery(), $request), 'attendance.records.view_all', 'attendance.regularization.view_team')
            ->whereHas('employee', function ($eq) use ($today) {
                $eq->activeEligible($today);
            });

        $todayRecordsQuery = Attendance::with('attendanceType')
            ->whereDate('attendance_date', $today)
            ->whereHas('employee', function ($eq) use ($today) {
                $eq->activeEligible($today);
            });

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

        // Unmarked attendance today (Active employees with completed profiles who haven't punched in today)
        /** @var User|null $user */
        $user = Auth::user();
        $isEmployeeRole = ($user->role_id ?? null) == 7 || ($user->system_role_id ?? null) == 7;
        $activeEmployeesQuery = EmployeeM::with(['user', 'department', 'designation', 'profile'])
            ->activeEligible($today);

        if ($isEmployeeRole || (! $this->canViewAll('attendance.records.view_all') && ! $this->canViewAll('attendance.monthly_report.view_all'))) {
            $ids = ($this->userHasPermission('attendance.monthly_report.view_team') || $this->userHasPermission('attendance.regularization.view_team')) && ! $isEmployeeRole
                ? $this->teamEmployeeIds(true)
                : array_filter([$this->ownEmployeeId()]);
            $activeEmployeesQuery->whereIn('id', $ids);
        }
        $activeEmployeesList = $activeEmployeesQuery->orderBy('id')->get();

        $punchedInEmployeeIds = $todayRecords->whereNotNull('punch_in_time')->pluck('employee_id')->filter()->unique()->toArray();
        $todayRecordsByEmp = $todayRecords->keyBy('employee_id');

        $allUnmarkedEmployees = $activeEmployeesList->reject(function ($emp) use ($punchedInEmployeeIds) {
            return in_array($emp->id, $punchedInEmployeeIds, true);
        })->map(function ($emp) use ($todayRecordsByEmp) {
            $emp->today_attendance = $todayRecordsByEmp->get($emp->id);
            return $emp;
        })->values();

        $stats['unmarked_today'] = $allUnmarkedEmployees->count();

        // Server-side pagination for Unmarked Attendance Table
        $unmarkedPage = (int) $request->input('unmarked_page', 1);
        $unmarkedPerPage = (int) $request->input('unmarked_per_page', 10);
        if ($unmarkedPerPage <= 0) {
            $unmarkedPerPage = 10;
        }
        $unmarkedEmployees = new LengthAwarePaginator(
            $allUnmarkedEmployees->forPage($unmarkedPage, $unmarkedPerPage)->values(),
            $allUnmarkedEmployees->count(),
            $unmarkedPerPage,
            $unmarkedPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'unmarked_page',
            ]
        );

        // Server-side pagination for Today's Attendance Table
        $attendancePerPage = (int) $request->input('attendance_per_page', $request->input('per_page', 25));
        if ($attendancePerPage <= 0) {
            $attendancePerPage = 25;
        }
        $attendances = $query->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate($attendancePerPage, ['*'], 'attendance_page')
            ->appends($request->query());
        $this->normalizeAttendanceCollection($attendances->getCollection());

        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $attendanceTimes = AttendanceTime::where('is_active', true)->orderByDesc('is_default')->get();
        $canManageAttendance = $this->canManageAttendance();
        $canUnlockAttendance = $this->canUnlockAttendance();

        // Server-side pagination for Blocked Attendance Table
        $blockedPerPage = (int) $request->input('blocked_per_page', 10);
        if ($blockedPerPage <= 0) {
            $blockedPerPage = 10;
        }

        $blockedAttendancesQuery = $this->scopeAttendanceQuery($this->baseQuery(), 'attendance.records.view_all', 'attendance.regularization.view_team')
            ->whereDate('attendance_date', $today)
            ->where(function ($q) {
                $q->where('is_punch_blocked', true)
                    ->orWhere('is_blocked', true)
                    ->orWhere('attendance_status', 'punch_blocked');
            })
            ->whereHas('employee', function ($eq) use ($today) {
                $eq->activeEligible($today);
            });

        $blockedAttendances = $blockedAttendancesQuery
            ->orderBy('id')
            ->paginate($blockedPerPage, ['*'], 'blocked_page')
            ->appends($request->query());
        $this->normalizeAttendanceCollection($blockedAttendances->getCollection());

        $blockedEmpIds = $blockedAttendances->pluck('employee_id')->filter()->unique()->toArray();
        $regRequestsByEmp = collect();
        if (!empty($blockedEmpIds)) {
            $regRequestsByEmp = DB::table('attendance_regularizations')
                ->whereIn('employee_id', $blockedEmpIds)
                ->whereNull('deleted_at')
                ->latest('id')
                ->get()
                ->groupBy('employee_id');
        }

        foreach ($blockedAttendances as $blocked) {
            $empId = $blocked->employee_id;
            $attDate = $blocked->attendance_date ? Carbon::parse($blocked->attendance_date)->toDateString() : null;
            $attId = is_numeric($blocked->id) ? $blocked->id : null;

            $empRegs = $regRequestsByEmp->get($empId, collect());
            $regRequest = $empRegs->first(function ($r) use ($attDate, $attId) {
                if ($attId && $r->attendance_id == $attId) {
                    return true;
                }
                if ($attDate) {
                    $inDate = $r->requested_punch_in ? Carbon::parse($r->requested_punch_in)->toDateString() : null;
                    $outDate = $r->requested_punch_out ? Carbon::parse($r->requested_punch_out)->toDateString() : null;
                    $cDate = $r->created_at ? Carbon::parse($r->created_at)->toDateString() : null;
                    if ($inDate === $attDate || $outDate === $attDate || $cDate === $attDate) {
                        return true;
                    }
                }
                return false;
            });
            $blocked->regularization_request = $regRequest;
        }

        return view('hrms.attendance.dashboard.index', compact(
            'attendances',
            'employees',
            'attendanceTypes',
            'attendanceTimes',
            'stats',
            'blockedAttendances',
            'unmarkedEmployees',
            'today',
            'canManageAttendance',
            'canUnlockAttendance'
        ));
    }

    public function daily(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.records.view_all') || $this->userHasPermission('attendance.my.view'), 403);

        $selectedMonth = $request->input('month');
        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');
        if (!$request->has('month') && !$fromDate && !$toDate && !$request->has('date')) {
            $selectedMonth = now()->format('Y-m');
            $request->merge(['month' => $selectedMonth]);
        }

        $query = $this->scopeAttendanceQuery($this->applyFilters($this->baseQuery(), $request), 'attendance.records.view_all');

        // Calculate accurate metric counts for the filtered dataset without loading entire table into memory
        $statsQuery = clone $query;
        $totalRecords = (clone $statsQuery)->count();
        $presentRecords = (clone $statsQuery)->where(function ($q) {
            $q->whereIn('attendance_status', ['present', 'verified', 'approved', 'early'])
                ->orWhere(function ($sq) {
                    $sq->whereNotNull('punch_in_time')
                        ->whereNotIn('attendance_status', ['absent', 'punch_blocked']);
                });
        })->count();
        $lateRecords = (clone $statsQuery)->where('is_late', 1)->count();
        $blockedRecords = (clone $statsQuery)->where(function ($q) {
            $q->where('attendance_status', 'punch_blocked')
                ->orWhere('is_blocked', 1)
                ->orWhere('is_punch_blocked', 1);
        })->count();
        $missedRecords = (clone $statsQuery)->where(function ($q) {
            $q->where('missed_punch', 1)
                ->orWhere('is_missed_punch', 1)
                ->orWhere(function ($sq) {
                    $sq->whereNotNull('punch_in_time')
                        ->whereNull('punch_out_time')
                        ->where('is_half_day', 0)
                        ->whereDate('attendance_date', '<', now()->toDateString());
                });
        })->count();
        $halfDayRecords = (clone $statsQuery)->where(function ($q) {
            $q->where('attendance_status', 'half_day')->orWhere('is_half_day', 1);
        })->count();
        $wfoRecords = (clone $statsQuery)->where('work_mode', 'wfo')->count();
        $wfhRecords = (clone $statsQuery)->where('work_mode', 'wfh')->count();

        $statsSummary = [
            'total' => $totalRecords,
            'present' => $presentRecords,
            'late' => $lateRecords,
            'blocked' => $blockedRecords,
            'missed' => $missedRecords,
            'half_day' => $halfDayRecords,
            'wfo' => $wfoRecords,
            'wfh' => $wfhRecords,
        ];

        // Available Months for dropdown
        $availableMonths = [];
        $currentMonthObj = now()->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $m = (clone $currentMonthObj)->subMonths($i);
            $availableMonths[$m->format('Y-m')] = $m->format('F Y');
        }

        $perPage = (int) $request->input('attendance_per_page', $request->input('per_page', 25));
        if ($perPage <= 0) {
            $perPage = 25;
        }

        $attendances = $this->orderAttendanceQuery($query, $request)->paginate($perPage)->appends($request->query());
        $this->normalizeAttendanceCollection($attendances->getCollection());
        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $attendanceTimes = AttendanceTime::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $departments = DepartmentM::orderBy('name')->get();
        $canManageAttendance = $this->canManageAttendance();

        return view('hrms.attendance.records.daily', compact(
            'attendances',
            'employees',
            'attendanceTypes',
            'attendanceTimes',
            'departments',
            'canManageAttendance',
            'statsSummary',
            'availableMonths',
            'selectedMonth'
        ));
    }

    public function teamAttendance(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isGlobal = $this->canViewAll('attendance.records.view_all');
        $canTeam = $this->canViewTeam('attendance.regularization.view_team') || $this->canViewTeam('attendance.monthly_report.view_team');

        $supervisorEmpId = $this->ownEmployeeId();
        $teamScope = app(TeamManagementScopeS::class);
        $teamEmpIds = $supervisorEmpId ? $teamScope->getTeamEmployeeIds($supervisorEmpId) : [];

        // If global/admin with no specific team, default to all active employees
        if (($isGlobal || $teamScope->isSuperAdminOrGlobal()) && empty($teamEmpIds)) {
            $teamEmpIds = EmployeeM::active()->pluck('id')->toArray();
        }

        $hasAccess = $isGlobal
            || $canTeam
            || !empty($teamEmpIds)
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin', 'manager', 'lead', 'team_lead']))
            || (method_exists($user, 'can') && ($user->can('attendance.records.view_team') || $user->can('attendance.records.view_all') || $user->can('reporting.attendance')));

        abort_unless($hasAccess, 403);

        $today = Carbon::now($this->attendanceService->attendanceTimezone())->toDateString();
        $currentMonthYear = Carbon::now($this->attendanceService->attendanceTimezone())->format('Y-m');
        $viewMode = $request->input('view_mode', 'today'); // 'today' or 'history'

        // Date & Month filters
        $date = $request->input('date');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $monthYear = $request->input('month_year');

        // Base Query
        $query = Attendance::with([
            'user',
            'employee.department',
            'employee.designation',
            'employee.reportingManager.user',
            'attendanceType',
            'attendanceTime',
            'workLogs',
        ]);

        if (!empty($teamEmpIds)) {
            $query->whereIn('employee_id', $teamEmpIds);
        } else {
            $query->whereRaw('1 = 0');
        }

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })->orWhereHas('employee', function ($eq) use ($search) {
                    $eq->where('employee_code', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('work_mode')) {
            $query->where('work_mode', strtolower($request->work_mode));
        }

        if ($request->filled('status_filter')) {
            $sf = $request->status_filter;
            if ($sf === 'working') {
                $query->whereNotNull('punch_in_time')->whereNull('punch_out_time')->where('is_blocked', 0);
            } elseif ($sf === 'completed') {
                $query->whereNotNull('punch_in_time')->whereNotNull('punch_out_time');
            } elseif ($sf === 'late') {
                $query->where('is_late', 1);
            } elseif ($sf === 'half_day') {
                $query->where('is_half_day', 1);
            } elseif ($sf === 'wfh') {
                $query->where('work_mode', 'wfh');
            } elseif ($sf === 'blocked') {
                $query->where(function ($q) {
                    $q->where('is_blocked', 1)->orWhere('is_punch_blocked', 1);
                });
            }
        }

        if ($viewMode === 'today') {
            $date = $date ?: $today;
            $query->whereDate('attendance_date', $date);
            $selectedMonthYear = $currentMonthYear;
        } else {
            $hasRange = !empty($fromDate) || !empty($toDate);
            $hasSingleDate = !empty($date);

            if ($fromDate && $toDate) {
                $query->whereBetween('attendance_date', [$fromDate, $toDate]);
                $selectedMonthYear = 'all';
            } elseif ($fromDate) {
                $query->whereDate('attendance_date', '>=', $fromDate);
                $selectedMonthYear = 'all';
            } elseif ($toDate) {
                $query->whereDate('attendance_date', '<=', $toDate);
                $selectedMonthYear = 'all';
            } elseif ($hasSingleDate) {
                $query->whereDate('attendance_date', $date);
                try {
                    $selectedMonthYear = Carbon::parse($date)->format('Y-m');
                } catch (\Throwable $e) {
                    $selectedMonthYear = $currentMonthYear;
                }
            } elseif (!empty($monthYear) && $monthYear !== 'all' && $monthYear !== 'custom') {
                $query->where('attendance_date', 'LIKE', "{$monthYear}%");
                $selectedMonthYear = $monthYear;
            } else {
                // Default to current month in History mode
                $query->where('attendance_date', 'LIKE', "{$currentMonthYear}%");
                $selectedMonthYear = $currentMonthYear;
            }
        }

        // Team stats today calculation
        $todayStatsQuery = Attendance::whereDate('attendance_date', $today);
        if (!empty($teamEmpIds)) {
            $todayStatsQuery->whereIn('employee_id', $teamEmpIds);
        } else {
            $todayStatsQuery->whereRaw('1 = 0');
        }
        $todayAttendanceRows = $todayStatsQuery->get();

        $teamEmployees = !empty($teamEmpIds)
            ? EmployeeM::with(['user', 'department', 'designation'])->active()->whereIn('id', $teamEmpIds)->orderBy('id')->get()
            : collect();
        $totalTeamCount = $teamEmployees->count();

        $punchedInEmpIds = $todayAttendanceRows->whereNotNull('punch_in_time')->pluck('employee_id')->filter()->unique()->toArray();
        $currentlyWorkingCount = $todayAttendanceRows->whereNotNull('punch_in_time')->whereNull('punch_out_time')->where('is_blocked', 0)->count();
        $completedShiftCount = $todayAttendanceRows->whereNotNull('punch_in_time')->whereNotNull('punch_out_time')->count();
        $lateTodayCount = $todayAttendanceRows->where('is_late', 1)->count();
        $wfhTodayCount = $todayAttendanceRows->where('work_mode', 'wfh')->count();
        $blockedCount = $todayAttendanceRows->filter(fn($r) => $r->is_blocked || $r->is_punch_blocked)->count();

        // Approved Leaves today
        $leaveEmpQuery = DB::table('leave_requests')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 'approved');
        if (!empty($teamEmpIds)) {
            $leaveEmpQuery->whereIn('employee_id', $teamEmpIds);
        } else {
            $leaveEmpQuery->whereRaw('1 = 0');
        }
        $onLeaveTodayCount = $leaveEmpQuery->distinct('employee_id')->count('employee_id');

        $notPunchedCount = max(0, $totalTeamCount - count($punchedInEmpIds) - $onLeaveTodayCount);

        $stats = [
            'total_team' => $totalTeamCount,
            'currently_working' => $currentlyWorkingCount,
            'completed_shift' => $completedShiftCount,
            'late_today' => $lateTodayCount,
            'wfh_today' => $wfhTodayCount,
            'on_leave_today' => $onLeaveTodayCount,
            'not_punched_today' => $notPunchedCount,
            'blocked_today' => $blockedCount,
        ];

        // Handle Team Attendance Export (Full filtered dataset)
        if ($request->filled('export')) {
            $exportType = strtolower($request->input('export'));
            $allRows = (clone $query)->orderByDesc('attendance_date')
                ->orderByDesc('punch_in_time')
                ->orderByDesc('id')
                ->get();
            $this->normalizeAttendanceCollection($allRows);

            if ($exportType === 'json') {
                $formatted = [];
                foreach ($allRows as $idx => $row) {
                    $punchIn = $row->punch_in_time ? Carbon::parse($row->punch_in_time) : null;
                    $punchOut = $row->punch_out_time ? Carbon::parse($row->punch_out_time) : null;

                    $punchInFormatted = $punchIn ? $punchIn->format('h:i A') : '--:--';
                    $punchOutFormatted = $punchOut ? $punchOut->format('h:i A') : ($punchIn ? 'Currently Active' : '--:--');
                    $dateFormatted = $row->attendance_date ? Carbon::parse($row->attendance_date)->format('d M Y') : '-';

                    if ($punchIn && $punchOut) {
                        $mins = $punchIn->diffInMinutes($punchOut);
                        $h = floor($mins / 60);
                        $m = $mins % 60;
                        $workingHours = sprintf('%02dh %02dm', $h, $m);
                    } elseif ($punchIn) {
                        $mins = $punchIn->diffInMinutes(Carbon::now());
                        $h = floor($mins / 60);
                        $m = $mins % 60;
                        $workingHours = sprintf('%02dh %02dm (Live)', $h, $m);
                    } else {
                        $workingHours = '--';
                    }

                    $statusLabel = optional($row->attendanceType)->name ?? ucwords(str_replace('_', ' ', $row->attendance_status ?? 'N/A'));
                    if ($punchIn && !$punchOut && !$row->is_blocked) {
                        $statusLabel = 'Currently Working';
                    } elseif ($punchIn && $punchOut) {
                        $statusLabel = 'Completed Shift';
                    }

                    $workSummary = '-';
                    if ($row->workLogs && $row->workLogs->count() > 0) {
                        $summaryParts = [];
                        foreach ($row->workLogs as $log) {
                            if (!empty($log->task_title)) {
                                $summaryParts[] = $log->task_title;
                            } elseif (!empty($log->work_summary)) {
                                $summaryParts[] = $log->work_summary;
                            }
                        }
                        if (!empty($summaryParts)) {
                            $workSummary = implode('; ', $summaryParts);
                        }
                    }

                    $flags = [];
                    if ($row->is_late) $flags[] = 'Late (' . ($row->late_minutes ?? 0) . 'm)';
                    if ($row->is_early_out) $flags[] = 'Early Out (' . ($row->early_out_minutes ?? 0) . 'm)';
                    if ($row->is_blocked || $row->is_punch_blocked) $flags[] = 'Blocked';
                    if ($row->missed_punch || $row->is_missed_punch) $flags[] = 'Missed Punch';
                    $flagStr = !empty($flags) ? implode(', ', $flags) : 'Clear';

                    $formatted[] = [
                        'sr_no' => $idx + 1,
                        'employee_name' => optional($row->employee)->display_name ?? optional($row->user)->name ?? 'Team Member',
                        'employee_code' => optional($row->employee)->employee_code ?? '-',
                        'department' => optional(optional($row->employee)->department)->name ?? '-',
                        'designation' => optional(optional($row->employee)->designation)->name ?? '-',
                        'shift' => optional($row->attendanceTime)->name ?? 'General Shift',
                        'date' => $dateFormatted,
                        'punch_in' => $punchInFormatted,
                        'punch_out' => $punchOutFormatted,
                        'working_hours' => $workingHours,
                        'work_mode' => strtoupper($row->work_mode ?? 'WFO'),
                        'status' => $statusLabel,
                        'work_summary' => $workSummary,
                        'flags' => $flagStr,
                    ];
                }
                return response()->json([
                    'success' => true,
                    'total' => count($formatted),
                    'data' => $formatted,
                ]);
            }

            if ($exportType === 'csv' || $exportType === 'excel') {
                $filename = 'team_attendance_' . date('Y_m_d_His') . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Pragma' => 'no-cache',
                    'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                    'Expires' => '0',
                ];

                $callback = function () use ($allRows) {
                    $handle = fopen('php://output', 'w');
                    // UTF-8 BOM
                    fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                    fputcsv($handle, [
                        '#',
                        'Employee Name',
                        'Employee Code',
                        'Department',
                        'Designation',
                        'Date',
                        'Shift',
                        'Login (Punch In)',
                        'Logout (Punch Out)',
                        'Working Hours',
                        'Work Mode',
                        'Status',
                        'Work Summary',
                        'Flags / Remarks'
                    ]);

                    foreach ($allRows as $index => $row) {
                        $punchIn = $row->punch_in_time ? Carbon::parse($row->punch_in_time) : null;
                        $punchOut = $row->punch_out_time ? Carbon::parse($row->punch_out_time) : null;

                        $punchInFormatted = $punchIn ? $punchIn->format('h:i A') : '--:--';
                        $punchOutFormatted = $punchOut ? $punchOut->format('h:i A') : ($punchIn ? 'Currently Active' : '--:--');
                        $dateFormatted = $row->attendance_date ? Carbon::parse($row->attendance_date)->format('d M Y') : '-';

                        if ($punchIn && $punchOut) {
                            $mins = $punchIn->diffInMinutes($punchOut);
                            $h = floor($mins / 60);
                            $m = $mins % 60;
                            $workingHours = sprintf('%02dh %02dm', $h, $m);
                        } elseif ($punchIn) {
                            $mins = $punchIn->diffInMinutes(Carbon::now());
                            $h = floor($mins / 60);
                            $m = $mins % 60;
                            $workingHours = sprintf('%02dh %02dm (Live)', $h, $m);
                        } else {
                            $workingHours = '--';
                        }

                        $statusLabel = optional($row->attendanceType)->name ?? ucwords(str_replace('_', ' ', $row->attendance_status ?? 'N/A'));
                        if ($punchIn && !$punchOut && !$row->is_blocked) {
                            $statusLabel = 'Currently Working';
                        } elseif ($punchIn && $punchOut) {
                            $statusLabel = 'Completed Shift';
                        }

                        $workSummary = '-';
                        if ($row->workLogs && $row->workLogs->count() > 0) {
                            $summaryParts = [];
                            foreach ($row->workLogs as $log) {
                                if (!empty($log->task_title)) {
                                    $summaryParts[] = $log->task_title;
                                } elseif (!empty($log->work_summary)) {
                                    $summaryParts[] = $log->work_summary;
                                }
                            }
                            if (!empty($summaryParts)) {
                                $workSummary = implode('; ', $summaryParts);
                            }
                        }

                        $flags = [];
                        if ($row->is_late) $flags[] = 'Late (' . ($row->late_minutes ?? 0) . 'm)';
                        if ($row->is_early_out) $flags[] = 'Early Out (' . ($row->early_out_minutes ?? 0) . 'm)';
                        if ($row->is_blocked || $row->is_punch_blocked) $flags[] = 'Blocked';
                        if ($row->missed_punch || $row->is_missed_punch) $flags[] = 'Missed Punch';
                        $flagStr = !empty($flags) ? implode(', ', $flags) : 'Clear';

                        fputcsv($handle, [
                            $index + 1,
                            optional($row->employee)->display_name ?? optional($row->user)->name ?? 'Team Member',
                            optional($row->employee)->employee_code ?? '-',
                            optional(optional($row->employee)->department)->name ?? '-',
                            optional(optional($row->employee)->designation)->name ?? '-',
                            $dateFormatted,
                            optional($row->attendanceTime)->name ?? 'General Shift',
                            $punchInFormatted,
                            $punchOutFormatted,
                            $workingHours,
                            strtoupper($row->work_mode ?? 'WFO'),
                            $statusLabel,
                            $workSummary,
                            $flagStr
                        ]);
                    }
                    fclose($handle);
                };

                return response()->stream($callback, 200, $headers);
            }
        }

        $perPage = 50;
        if ($request->filled('per_page')) {
            $perPage = ($request->per_page === 'all' || $request->per_page == '-1') ? 5000 : (int) $request->per_page;
        } elseif ($request->filled('attendance_per_page')) {
            $perPage = ($request->attendance_per_page === 'all' || $request->attendance_per_page == '-1') ? 5000 : (int) $request->attendance_per_page;
        }

        $attendances = $query->orderByDesc('attendance_date')
            ->orderByDesc('punch_in_time')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->query());

        $this->normalizeAttendanceCollection($attendances->getCollection());

        // Transform collection for easy view rendering
        $attendances->getCollection()->transform(function ($row) {
            $punchIn = $row->punch_in_time ? Carbon::parse($row->punch_in_time) : null;
            $punchOut = $row->punch_out_time ? Carbon::parse($row->punch_out_time) : null;

            $row->punch_in_formatted = $punchIn ? $punchIn->format('h:i A') : '--:--';
            $row->punch_out_formatted = $punchOut ? $punchOut->format('h:i A') : ($punchIn ? 'Currently Active' : '--:--');
            $row->date_formatted = $row->attendance_date ? Carbon::parse($row->attendance_date)->format('d M Y') : '-';

            // Working hours calculation
            if ($punchIn && $punchOut) {
                $mins = $punchIn->diffInMinutes($punchOut);
                $h = floor($mins / 60);
                $m = $mins % 60;
                $row->working_hours_label = sprintf('%02dh %02dm', $h, $m);
            } elseif ($punchIn) {
                $mins = $punchIn->diffInMinutes(Carbon::now());
                $h = floor($mins / 60);
                $m = $mins % 60;
                $row->working_hours_label = sprintf('%02dh %02dm (Live)', $h, $m);
            } else {
                $row->working_hours_label = '--';
            }

            return $row;
        });

        return view('hrms.attendance.records.team', [
            'attendances' => $attendances,
            'teamEmployees' => $teamEmployees,
            'stats' => $stats,
            'today' => $today,
            'date' => $date,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'selectedMonthYear' => $selectedMonthYear,
            'viewMode' => $viewMode,
            'accesses' => $this->accesses(),
            'active' => 'attendances',
            'isGlobal' => $isGlobal,
        ]);
    }

    private function resolveAttendanceRecordsQuery(Request $request): array
    {
        $currentEmployee = EmployeeM::where('user_id', Auth::id())->first();
        $currentEmployeeId = $currentEmployee ? $currentEmployee->id : ($this->ownEmployeeId() ?: null);
        $userId = Auth::id();

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');
        $singleDate = $request->input('date');
        $monthYear = $request->input('month_year');
        $month = $request->input('month');

        $hasDateFilter = !empty($singleDate)
            || !empty($fromDate)
            || !empty($toDate)
            || (!empty($monthYear) && $monthYear !== 'all' && $monthYear !== 'custom')
            || (!empty($month) && $month !== 'all' && $month !== 'custom');

        $currentMonthYear = Carbon::now($this->attendanceService->attendanceTimezone())->format('Y-m');
        $selectedMonthYear = $hasDateFilter
            ? ($monthYear ?: '')
            : $currentMonthYear;

        $isMyAttendance = request()->routeIs('hrms.attendance.my') || ($request->input('view_scope') === 'my');

        if ($isMyAttendance) {
            $selectedEmployeeId = $currentEmployeeId;
            $filterRequest = clone $request;
            $filterRequest->query->remove('employee_id');
            $filterRequest->query->remove('search');
            if (! $hasDateFilter && $currentMonthYear) {
                $filterRequest->merge(['month_year' => $currentMonthYear]);
            }

            $query = $this->applyFilters($this->recordsQuery(), $filterRequest);
            $query->where(function ($q) use ($currentEmployeeId, $userId) {
                if ($currentEmployeeId && $userId) {
                    $q->where('employee_id', $currentEmployeeId)->orWhere('user_id', $userId);
                } elseif ($currentEmployeeId) {
                    $q->where('employee_id', $currentEmployeeId);
                } elseif ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            });
        } else {
            // Admin / HR Admin page (/attendances/record)
            $hasEmployeeFilter = $request->filled('employee_id');
            if ($hasEmployeeFilter) {
                $selectedEmployeeId = $request->input('employee_id');
            } else {
                $selectedEmployeeId = 'all';
            }

            $filterRequest = clone $request;
            if (! $hasEmployeeFilter && $selectedEmployeeId && $selectedEmployeeId !== 'all') {
                $filterRequest->merge(['employee_id' => $selectedEmployeeId]);
            } elseif ($selectedEmployeeId === 'all') {
                $filterRequest->query->remove('employee_id');
            }

            if (! $hasDateFilter && $currentMonthYear) {
                $filterRequest->merge(['month_year' => $currentMonthYear]);
            }

            $query = $this->scopeAttendanceQuery($this->applyFilters($this->recordsQuery(), $filterRequest), 'attendance.records.view_all', 'attendance.regularization.view_team');
        }


        $periodLabel = 'All Records';
        if ($filterRequest->filled('from_date') && $filterRequest->filled('to_date')) {
            $periodLabel = Carbon::parse($filterRequest->from_date)->format('d M Y') . ' - ' . Carbon::parse($filterRequest->to_date)->format('d M Y');
        } elseif ($filterRequest->filled('from_date')) {
            $periodLabel = 'From ' . Carbon::parse($filterRequest->from_date)->format('d M Y');
        } elseif ($filterRequest->filled('to_date')) {
            $periodLabel = 'Up to ' . Carbon::parse($filterRequest->to_date)->format('d M Y');
        } elseif ($filterRequest->filled('date')) {
            $periodLabel = Carbon::parse($filterRequest->date)->format('d M Y');
        } elseif ($filterRequest->filled('month_year') && $filterRequest->month_year !== 'all') {
            try {
                $periodLabel = Carbon::parse($filterRequest->month_year . '-01')->format('F Y');
            } catch (\Throwable $e) {
                $periodLabel = $filterRequest->month_year;
            }
        } elseif ($filterRequest->filled('month') && $filterRequest->filled('year')) {
            $periodLabel = Carbon::create((int) $filterRequest->year, (int) $filterRequest->month, 1)->format('F Y');
        }

        return [$query, $filterRequest, $selectedEmployeeId, $selectedMonthYear, $periodLabel];
    }

    public function attendanceRecord(Request $request)
    {
        $isMyAttendance = request()->routeIs('hrms.attendance.my') || ($request->input('view_scope') === 'my');

        if ($isMyAttendance) {
            abort_unless(
                $this->userHasPermission('attendance.my.view')
                    || $this->canViewAll('attendance.records.view_all'),
                403
            );
        } else {
            abort_unless(
                $this->canViewAll('attendance.records.view_all')
                    || $this->userHasPermission('attendance.records.view_all'),
                403,
                'Access denied. Only authorized HR/Admin can view all employee attendance records.'
            );
        }

        $currentEmployee = EmployeeM::where('user_id', Auth::id())->first();
        $currentEmployeeId = $currentEmployee ? $currentEmployee->id : ($this->ownEmployeeId() ?: null);

        [$query, $filterRequest, $selectedEmployeeId, $selectedMonthYear, $periodLabel] = $this->resolveAttendanceRecordsQuery($request);

        $statsRaw = (clone $query)->leftJoin('attendance_types', 'attendance_types.id', '=', 'attendances.attendance_type_id')
            ->selectRaw("
                COUNT(attendances.id) as total_records,
                COUNT(CASE WHEN (attendances.attendance_status = 'present' OR attendance_types.code = 'present') THEN 1 END) as present_records,
                COUNT(CASE WHEN (attendances.is_late = 1 OR attendances.late_minutes > 0) THEN 1 END) as late_records,
                COUNT(CASE WHEN (attendances.missed_punch = 1 OR attendances.is_missed_punch = 1 OR attendances.attendance_status = 'missed_punch' OR attendance_types.code = 'missed_punch') THEN 1 END) as missed_records,
                COUNT(CASE WHEN (attendances.is_half_day = 1 OR attendances.attendance_status = 'half_day' OR attendance_types.code = 'half_day') THEN 1 END) as half_day_records,
                COUNT(CASE WHEN LOWER(COALESCE(attendances.work_mode, '')) = 'wfo' THEN 1 END) as wfo_records,
                COUNT(CASE WHEN LOWER(COALESCE(attendances.work_mode, '')) = 'wfh' THEN 1 END) as wfh_records
            ")->first();

        $stats = [
            'total' => (int) ($statsRaw->total_records ?? 0),
            'present' => (int) ($statsRaw->present_records ?? 0),
            'late' => (int) ($statsRaw->late_records ?? 0),
            'missed_punch' => (int) ($statsRaw->missed_records ?? 0),
            'half_day' => (int) ($statsRaw->half_day_records ?? 0),
            'wfo' => (int) ($statsRaw->wfo_records ?? 0),
            'wfh' => (int) ($statsRaw->wfh_records ?? 0),
        ];

        $perPage = 50;
        if ($request->filled('per_page')) {
            $perPage = ($request->per_page === 'all' || $request->per_page == '-1') ? 5000 : (int) $request->per_page;
        }

        $attendances = $this->orderAttendanceQuery($query, $filterRequest)->paginate($perPage)->appends($request->query());
        $this->normalizeAttendanceCollection($attendances->getCollection());
        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $attendanceTimes = AttendanceTime::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $departments = DepartmentM::orderBy('name')->get();
        $canManageAttendance = $this->canManageAttendance();

        return view('hrms.attendance.records.index', compact(
            'attendances',
            'employees',
            'attendanceTypes',
            'attendanceTimes',
            'departments',
            'canManageAttendance',
            'selectedEmployeeId',
            'currentEmployeeId',
            'selectedMonthYear',
            'stats'
        ));
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

        $result = $this->attendanceService->unlockAttendance($request->id, Auth::id(), $request->only([
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

        $employee = EmployeeM::find($request->employee_id);
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
        abort_unless($this->canManageAttendance(), 403, 'Only HR/Admin can modify attendance history.');

        $request->validate([
            'id' => 'required|exists:attendances,id',
            'attendance_type_id' => 'required|exists:attendance_types,id',
            'attendance_date' => 'nullable',
            'work_mode' => 'nullable|in:wfo,wfh',
            'punch_in_time' => 'nullable',
            'punch_out_time' => 'nullable',
            'note' => 'nullable|string|max:2000',
            'hr_approval_note' => 'nullable|string|max:2000',
        ]);

        $attendance = Attendance::findOrFail($request->id);
        $type = AttendanceType::findOrFail($request->attendance_type_id);
        $typeCode = strtolower($type->code);

        $punchInTime = $request->filled('punch_in_time') ? Carbon::parse($request->punch_in_time)->format('H:i:s') : null;
        $punchOutTime = $request->filled('punch_out_time') ? Carbon::parse($request->punch_out_time)->format('H:i:s') : null;
        $adminReason = $request->hr_approval_note ?: ($request->note ?: 'Manually updated by HR/Admin');

        $attDateStr = $request->filled('attendance_date') ? Carbon::parse($request->attendance_date)->toDateString() : Carbon::parse($attendance->attendance_date)->toDateString();

        $employee = $attendance->employee ?: EmployeeM::find($attendance->employee_id);
        $ruleResolver = app(AttendanceRuleResolverService::class);
        $shiftId = $attendance->attendance_time_id ?: null;
        $shift = $shiftId
            ? $ruleResolver->getPolicyFromAttendanceTimeId((int) $shiftId, $employee, $attDateStr)
            : ($employee ? $ruleResolver->getPolicyForEmployee($employee, $attDateStr) : null);

        $updateData = [
            'attendance_type_id' => $type->id,
            'attendance_status' => $typeCode,
            'attendance_source' => 'admin_override',
            'punch_in_time' => $punchInTime,
            'punch_out_time' => $punchOutTime,
            'hr_approval_note' => $adminReason,
            'hr_approved_by' => Auth::id(),
            'hr_approved_at' => now(),
            'remarks' => $adminReason,
            'status_reason' => $adminReason,
            'is_admin_unlocked' => true,
            'unlocked_by' => Auth::id(),
            'unlocked_at' => now(),
            'unlock_type' => 'hr_manual_override',
        ];

        if ($request->filled('work_mode')) {
            $updateData['work_mode'] = $request->work_mode;
        }

        if ($request->filled('attendance_date')) {
            $updateData['attendance_date'] = $attDateStr;
        }

        if ($request->filled('note')) {
            $updateData['punch_out_note'] = $request->note;
        }

        // Adjust flags based on selected status
        if ($typeCode === 'present') {
            $updateData['is_lwp'] = false;
            $updateData['is_half_day'] = false;
            $updateData['is_blocked'] = false;
            $updateData['is_punch_blocked'] = false;
            $updateData['missed_punch'] = false;
            $updateData['is_missed_punch'] = false;
            $updateData['lwp_reason'] = null;
            $updateData['half_day_reason'] = null;
        } elseif ($typeCode === 'half_day') {
            $updateData['is_half_day'] = true;
            $updateData['is_lwp'] = false;
            $updateData['is_blocked'] = false;
            $updateData['is_punch_blocked'] = false;
            $updateData['half_day_reason'] = $adminReason;
            $updateData['lwp_reason'] = null;
        } elseif ($typeCode === 'lwp') {
            $updateData['is_lwp'] = true;
            $updateData['is_half_day'] = false;
            $updateData['lwp_reason'] = $adminReason;
            $updateData['half_day_reason'] = null;
        } elseif ($typeCode === 'absent') {
            $updateData['is_lwp'] = true;
            $updateData['is_half_day'] = false;
            $updateData['status_reason'] = $adminReason;
            $updateData['lwp_reason'] = $adminReason;
        } elseif (in_array($typeCode, ['leave', 'holiday', 'week_off'], true)) {
            $updateData['is_lwp'] = false;
            $updateData['is_half_day'] = false;
            $updateData['is_blocked'] = false;
            $updateData['is_punch_blocked'] = false;
            $updateData['missed_punch'] = false;
            $updateData['is_missed_punch'] = false;
        }

        $isDynamicShift = in_array(strtolower($shift?->shift_type ?? ''), ['dynamic_hours', 'flexible_part_time'], true)
            || (is_object($shift) && method_exists($shift, 'isDynamicShift') && $shift->isDynamicShift());

        $requiredMinutes = (int) ($shift?->required_work_minutes ?? 0);
        if ($requiredMinutes <= 0) {
            $requiredMinutes = (int) ($shift?->required_office_minutes ?? 0);
        }
        if ($requiredMinutes <= 0 && ! empty($shift?->shift_start_time) && ! empty($shift?->shift_end_time)) {
            try {
                $st = Carbon::parse('2026-01-01 ' . $ruleResolver->timeString($shift->shift_start_time));
                $et = Carbon::parse('2026-01-01 ' . $ruleResolver->timeString($shift->shift_end_time));
                if ($et->lt($st)) {
                    $et->addDay();
                }
                $requiredMinutes = $st->diffInMinutes($et);
            } catch (\Throwable $e) {
                $requiredMinutes = 480;
            }
        }
        if ($requiredMinutes <= 0) {
            $requiredMinutes = 480;
        }

        $halfDayMinMinutes = (int) ($shift?->half_day_min_minutes ?? (int)($requiredMinutes / 2));

        // Recalculate target punch out & late / early stats
        if ($punchInTime) {
            $punchInCarbon = Carbon::parse($attDateStr . ' ' . $punchInTime, AttendanceRuleResolverService::TIMEZONE);
            $targetOutCarbon = $ruleResolver->targetPunchOut($punchInCarbon, $shift, $typeCode);
            $updateData['target_punch_out_time'] = $targetOutCarbon ? $targetOutCarbon->format('H:i:s') : null;

            if (in_array($typeCode, ['leave', 'holiday', 'week_off'], true) || (bool) $attendance->is_late_exempted || $isDynamicShift) {
                $updateData['is_late'] = false;
                $updateData['late_minutes'] = 0;
            } elseif ($shift && $shift->late_after_time) {
                $lateAfterCarbon = Carbon::parse($attDateStr . ' ' . $shift->late_after_time, AttendanceRuleResolverService::TIMEZONE);
                $isLate = $punchInCarbon->gt($lateAfterCarbon);
                $updateData['is_late'] = $isLate;
                $updateData['late_minutes'] = $isLate ? $lateAfterCarbon->diffInMinutes($punchInCarbon) : 0;
            } else {
                $updateData['is_late'] = false;
                $updateData['late_minutes'] = 0;
            }
        } else {
            $updateData['target_punch_out_time'] = null;
            $updateData['is_late'] = false;
            $updateData['late_minutes'] = 0;
        }

        if ($punchInTime && $punchOutTime) {
            $punchInCarbon = Carbon::parse($attDateStr . ' ' . $punchInTime, AttendanceRuleResolverService::TIMEZONE);
            $punchOutCarbon = Carbon::parse($attDateStr . ' ' . $punchOutTime, AttendanceRuleResolverService::TIMEZONE);

            $isEarly = false;
            $earlyMinutes = 0;
            if (! empty($updateData['target_punch_out_time'])) {
                $targetCarbon = Carbon::parse($attDateStr . ' ' . $updateData['target_punch_out_time'], AttendanceRuleResolverService::TIMEZONE);
                if ($targetCarbon->lt($punchInCarbon)) {
                    $targetCarbon->addDay();
                }
                if ($punchOutCarbon->lt($punchInCarbon)) {
                    $punchOutCarbon->addDay();
                }
                $isEarly = $punchOutCarbon->lt($targetCarbon);
                $earlyMinutes = $isEarly ? (int) abs($punchOutCarbon->diffInMinutes($targetCarbon)) : 0;
            }

            $updateData['is_early_out'] = $isEarly;
            $updateData['early_out_minutes'] = $earlyMinutes;
            $grossMinutes = (int) abs($punchInCarbon->diffInMinutes($punchOutCarbon));
            $breakMinutes = (int) ($shift?->lunch_break_minutes ?? $shift?->break_minutes ?? 0);
            $netMinutes = max(0, $grossMinutes - $breakMinutes);

            $earlyOutHalfDayMins = (int) ($shift?->early_out_half_day_minutes ?? 60);
            $halfDayCutoffCarbon = ! empty($updateData['target_punch_out_time'])
                ? Carbon::parse($attDateStr . ' ' . $updateData['target_punch_out_time'], AttendanceRuleResolverService::TIMEZONE)->subMinutes($earlyOutHalfDayMins)
                : null;
            $isHalfDayByPunchOut = $halfDayCutoffCarbon ? $punchOutCarbon->lt($halfDayCutoffCarbon) : false;

            // If user left status as half_day or absent from old state, but new timings satisfy full day or within early out grace window
            if (in_array($typeCode, ['half_day', 'absent', 'lwp', 'missed_punch'], true) && ($netMinutes >= $requiredMinutes || (! $isHalfDayByPunchOut && ! $isDynamicShift && $netMinutes >= $halfDayMinMinutes))) {
                $presentType = AttendanceType::where('code', 'present')->first();
                if ($presentType) {
                    $typeCode = 'present';
                    $updateData['attendance_type_id'] = $presentType->id;
                    $updateData['attendance_status'] = 'present';
                    $updateData['is_half_day'] = false;
                    $updateData['is_lwp'] = false;
                    $updateData['half_day_reason'] = null;
                }
            } elseif (in_array($typeCode, ['absent', 'lwp'], true) && $netMinutes >= $halfDayMinMinutes) {
                $halfDayType = AttendanceType::where('code', 'half_day')->first();
                if ($halfDayType) {
                    $typeCode = 'half_day';
                    $updateData['attendance_type_id'] = $halfDayType->id;
                    $updateData['attendance_status'] = 'half_day';
                    $updateData['is_half_day'] = true;
                    $updateData['is_lwp'] = false;
                }
            }

            $updateData['gross_work_minutes'] = $grossMinutes;
            $updateData['break_minutes'] = $breakMinutes;
            $updateData['lunch_break_minutes'] = $breakMinutes;
            $updateData['total_work_minutes'] = $netMinutes;
            $updateData['gross_duration'] = sprintf('%d hours %d mins', intdiv($grossMinutes, 60), $grossMinutes % 60);
            $updateData['total_duration'] = sprintf('%d hours %d mins', intdiv($netMinutes, 60), $netMinutes % 60);
        } elseif ($punchInTime && ! $punchOutTime) {
            $updateData['punch_out_time'] = null;
            $updateData['is_early_out'] = false;
            $updateData['early_out_minutes'] = 0;
            $updateData['gross_work_minutes'] = 0;
            $updateData['total_work_minutes'] = 0;
            $updateData['gross_duration'] = 'N/A';
            $updateData['total_duration'] = 'N/A';

            // When only punch in is recorded, ensure active attendance status is present (unless explicit leave/holiday/week_off)
            if (in_array($typeCode, ['absent', 'lwp', 'half_day', 'missed_punch'], true)) {
                $presentType = AttendanceType::where('code', 'present')->first();
                if ($presentType) {
                    $typeCode = 'present';
                    $updateData['attendance_type_id'] = $presentType->id;
                    $updateData['attendance_status'] = 'present';
                    $updateData['is_half_day'] = false;
                    $updateData['is_lwp'] = false;
                    $updateData['missed_punch'] = false;
                    $updateData['is_missed_punch'] = false;
                }
            }
        }

        $oldStatus = $attendance->attendance_status ?? optional($attendance->attendanceType)->code ?? 'unknown';
        $attendance->update($updateData);

        try {
            AttendanceDailyStatusLogM::create([
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance->id,
                'status_date' => $attendance->attendance_date ?? now()->toDateString(),
                'old_status' => $oldStatus,
                'new_status' => $typeCode,
                'source' => 'admin_override',
                'remarks' => $adminReason,
                'created_by_user_id' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not log attendance status change: ' . $e->getMessage());
        }

        if ($attendance->punch_in_time && $attendance->punch_out_time) {
            $this->attendanceService->calculateAttendanceStats($attendance);
        }

        $this->attendanceService->syncAttendanceViolations($attendance);
        $this->attendanceService->rebuildEmployeeViolationCycles($attendance->employee_id, $attDateStr);

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

        $today = Carbon::now($this->attendanceService->attendanceTimezone())->toDateString();

        // Base query for attendance records that are PUNCH BLOCKED or UNLOCKED by HR
        $query = $this->scopeAttendanceQuery($this->baseQuery(), 'attendance.records.view_all', 'attendance.regularization.view_team')
            ->where(function ($q) {
                // Blocked or Unlocked records (including past blocked punches auto-marked absent)
                $q->where('is_blocked', true)
                    ->orWhere('is_punch_blocked', true)
                    ->orWhere('attendance_status', 'punch_blocked')
                    ->orWhere('attendance_status', 'unlocked')
                    ->orWhere('is_admin_unlocked', true)
                    ->orWhereNotNull('unlocked_at')
                    ->orWhereNotNull('unlock_type')
                    ->orWhereNotNull('auto_block_reason')
                    ->orWhereNotNull('block_reason')
                    ->orWhereNotNull('blocked_reason');
            })
            ->whereNotIn('attendance_status', ['leave', 'holiday', 'week_off']);

        // Filter by request flag if specified
        if ($request->flag === 'unlocked') {
            $query->where(function ($sq) {
                $sq->where('is_admin_unlocked', true)
                    ->orWhereNotNull('unlocked_at')
                    ->orWhere('attendance_status', 'unlocked');
            });
        } elseif ($request->flag === 'blocked') {
            $query->where(function ($sq) {
                $sq->whereNull('is_admin_unlocked')
                    ->orWhere('is_admin_unlocked', false)
                    ->orWhere('is_admin_unlocked', 0);
            })->whereNull('unlocked_at')->where('attendance_status', '<>', 'unlocked');
        } elseif ($request->flag === 'missed') {
            $query->where(function ($sq) {
                $sq->where('missed_punch', 1)->orWhere('is_missed_punch', 1)->orWhere('attendance_status', 'missed_punch');
            });
        } elseif ($request->flag === 'manual_punch_in') {
            $query->where('unlock_type', 'manual_punch_in');
        } elseif ($request->flag === 'today') {
            $query->whereDate('attendance_date', $today);
        }

        // Query blocked_punch violations from attendance_violations table
        $violationQuery = AttendanceViolationM::with(['employee.user', 'employee.department'])
            ->where('type', 'blocked_punch')
            ->where(function ($sq) use ($request) {
                if ($request->flag === 'unlocked') {
                    $sq->where('policy_action', 'resolved');
                } elseif ($request->flag === 'blocked') {
                    $sq->where(function ($bq) {
                        $bq->whereNull('policy_action')
                            ->orWhere('policy_action', '<>', 'resolved');
                    });
                }
            });

        // Apply employee/department/date filters to violation query
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

        $hasDateFilter = $request->filled('date')
            || $request->filled('from_date')
            || $request->filled('to_date')
            || $request->filled('month_year')
            || $request->filled('month')
            || $request->filled('from')
            || $request->filled('to');

        $currentMonthYear = Carbon::now($this->attendanceService->attendanceTimezone())->format('Y-m');
        $selectedMonthYear = $request->input('month_year', $hasDateFilter ? ($request->input('month_year') ?: '') : $currentMonthYear);

        $filterRequest = clone $request;
        if (! $hasDateFilter && $currentMonthYear) {
            $filterRequest->merge(['month_year' => $currentMonthYear]);
        }

        $vFrom = $filterRequest->input('from_date') ?: $filterRequest->input('from');
        $vTo = $filterRequest->input('to_date') ?: $filterRequest->input('to');

        if ($filterRequest->filled('date')) {
            $violationQuery->whereDate('violation_date', $filterRequest->date);
        } elseif ($vFrom || $vTo) {
            if ($vFrom) {
                $violationQuery->whereDate('violation_date', '>=', $vFrom);
            }
            if ($vTo) {
                $violationQuery->whereDate('violation_date', '<=', $vTo);
            }
        } elseif ($filterRequest->filled('month_year') && $filterRequest->month_year !== 'all') {
            $parts = explode('-', $filterRequest->month_year);
            if (count($parts) === 2) {
                $violationQuery->whereYear('violation_date', (int) $parts[0])->whereMonth('violation_date', (int) $parts[1]);
            }
        } elseif ($filterRequest->filled('month') && $filterRequest->month !== 'all' && $filterRequest->month !== 'custom') {
            if (str_contains($filterRequest->month, '-')) {
                $parts = explode('-', $filterRequest->month);
                if (count($parts) === 2) {
                    $violationQuery->whereYear('violation_date', (int) $parts[0])->whereMonth('violation_date', (int) $parts[1]);
                }
            } else {
                $violationQuery->whereMonth('violation_date', (int) $filterRequest->month);
                if ($filterRequest->filled('year')) {
                    $violationQuery->whereYear('violation_date', (int) $filterRequest->year);
                }
            }
        } else {
            if ($filterRequest->filter === 'today' || $filterRequest->flag === 'today') {
                $violationQuery->whereDate('violation_date', $today);
            } elseif ($filterRequest->filter === 'yesterday') {
                $violationQuery->whereDate('violation_date', Carbon::yesterday()->toDateString());
            }
        }

        $blockedViolations = $violationQuery->get();
        $presentType = AttendanceType::where('code', 'present')->first();
        $blockedType = AttendanceType::where('code', 'punch_blocked')->first();

        $virtualAttendances = $blockedViolations->map(function ($violation) use ($presentType, $blockedType) {
            $isResolved = $violation->policy_action === 'resolved';
            $att = new Attendance();
            $att->id = 'violation_' . $violation->id;
            $att->employee_id = $violation->employee_id;
            $att->attendance_date = $violation->violation_date->toDateString();
            $att->attendance_status = $isResolved ? 'unlocked' : 'punch_blocked';
            $att->is_blocked = ! $isResolved;
            $att->is_punch_blocked = ! $isResolved;
            $att->is_admin_unlocked = $isResolved;
            $att->unlocked_at = $isResolved ? $violation->updated_at : null;
            $att->block_reason = $violation->remarks ?: 'Punch-in blocked after allowed time.';
            $att->auto_block_reason = $violation->remarks ?: 'Punch-in blocked after allowed time.';
            $att->blocked_reason = $violation->remarks ?: 'Punch-in blocked after allowed time.';

            $att->setRelation('employee', $violation->employee);
            if ($violation->employee) {
                $att->setRelation('user', $violation->employee->user);
            }

            $att->setRelation('attendanceType', $isResolved ? $presentType : $blockedType);

            return $att;
        });

        $realAttendances = $this->applyFilters($query, $filterRequest)->get();

        // Build set of existing real attendance IDs and (employee_id + date) keys for strict deduplication
        $realAttendanceIds = $realAttendances->pluck('id')->filter()->toArray();
        $realEmpDateKeys = $realAttendances->map(function ($a) {
            $empId = $a->employee_id;
            $date = $a->attendance_date ? Carbon::parse($a->attendance_date)->format('Y-m-d') : '';
            return ($empId && $date) ? $empId . '_' . $date : null;
        })->filter()->unique()->toArray();

        // Reject virtual violation entries if the attendance record is already present in realAttendances or linked
        $virtualAttendances = $virtualAttendances->reject(function ($vAtt) use ($realAttendanceIds, $realEmpDateKeys) {
            $vDate = $vAtt->attendance_date ? Carbon::parse($vAtt->attendance_date)->format('Y-m-d') : '';
            $key = $vAtt->employee_id . '_' . $vDate;
            if (in_array($key, $realEmpDateKeys, true)) {
                return true;
            }
            $violationId = (int) str_replace('violation_', '', (string) $vAtt->id);
            $violation = AttendanceViolationM::find($violationId);
            if ($violation && $violation->attendance_id && in_array((int) $violation->attendance_id, $realAttendanceIds, true)) {
                return true;
            }
            return false;
        });

        // Merge, sort latest on top (date desc, timestamp desc, id desc), and paginate manually
        $merged = $realAttendances->concat($virtualAttendances)
            ->sortByDesc(function ($item) {
                $dateTs = $item->attendance_date ? Carbon::parse($item->attendance_date)->timestamp : 0;
                $timeTs = ($item->unlocked_at ?? $item->updated_at ?? $item->created_at) ? Carbon::parse($item->unlocked_at ?? $item->updated_at ?? $item->created_at)->timestamp : 0;
                $idNum = (int) preg_replace('/[^0-9]/', '', (string) $item->id);
                return [$dateTs, $timeTs, $idNum];
            })->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPageReq = $request->input('per_page', '25');
        if ($perPageReq === 'all' || $perPageReq === '-1') {
            $perPage = max(1, $merged->count());
        } else {
            $perPage = (int) $perPageReq > 0 ? (int) $perPageReq : 25;
        }
        $currentPageItems = $merged->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $attendances = new LengthAwarePaginator(
            $currentPageItems,
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $attendances->appends($request->all());

        $this->normalizeAttendanceCollection($attendances->getCollection());

        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();
        $canManageAttendance = $this->canManageAttendance();
        $canUnlockAttendance = $this->canUnlockAttendance();

        // Count stats for Blocked & Unlocked records
        $totalBlocked = $merged->count();
        $pendingUnlock = $merged->filter(fn($item) => ! ($item->is_admin_unlocked || $item->unlocked_at || ($item->attendance_status ?? '') === 'unlocked'))->count();
        $unlockedTotal = $merged->filter(fn($item) => (bool) ($item->is_admin_unlocked || $item->unlocked_at || ($item->attendance_status ?? '') === 'unlocked'))->count();
        $unlockedToday = $merged->filter(function ($item) use ($today) {
            $isUnl = (bool) ($item->is_admin_unlocked || $item->unlocked_at || ($item->attendance_status ?? '') === 'unlocked');
            if (! $isUnl) return false;
            $unlDate = $item->unlocked_at ? Carbon::parse($item->unlocked_at)->toDateString() : ($item->updated_at ? Carbon::parse($item->updated_at)->toDateString() : null);
            return $unlDate === $today;
        })->count();
        $missedPunchTotal = $merged->filter(fn($item) => (bool) ($item->missed_punch || $item->is_missed_punch || ($item->attendance_status ?? '') === 'missed_punch'))->count();
        $manualPunchTotal = $merged->filter(fn($item) => ($item->unlock_type ?? '') === 'manual_punch_in')->count();

        $stats = [
            'total_blocked' => $totalBlocked,
            'pending_unlock' => $pendingUnlock,
            'unlocked_total' => $unlockedTotal,
            'unlocked_today' => $unlockedToday,
            'missed_punch' => $missedPunchTotal,
            'manual_punch' => $manualPunchTotal,
        ];

        return view('hrms.attendance.pending-approvals.index', compact(
            'attendances',
            'employees',
            'attendanceTypes',
            'stats',
            'canManageAttendance',
            'canUnlockAttendance',
            'today',
            'selectedMonthYear'
        ));
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

        return view('hrms.attendance.reports.monthly', compact(
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
        return view('hrms.attendance.policies.index', compact('attendancePolicies'));
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

        return view('hrms.attendance.policies.rules', compact('attendanceTimes', 'employeeShiftTimings', 'employees', 'attendancePolicies'));
    }

    public function updateRule(Request $request, AttendanceTime $attendanceTime)
    {
        abort_unless($this->canManageAttendance(), 403, 'Only Super Admin can modify attendance rules.');

        $isDynamicDuration = in_array($request->input('shift_type'), ['flexible_part_time', 'dynamic_hours'], true);

        $data = $request->validate([
            'name' => 'required|string',
            'shift_type' => 'nullable|string|in:fixed,flexible_part_time,dynamic_hours',
            'punch_allowed_from' => $isDynamicDuration ? 'nullable' : 'required',
            'shift_start_time' => $isDynamicDuration ? 'nullable' : 'required',
            'shift_end_time' => $isDynamicDuration ? 'nullable' : 'required',
            'late_after_time' => $isDynamicDuration ? 'nullable' : 'required',
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

        $data['shift_type'] = $request->input('shift_type', $attendanceTime->shift_type ?? 'fixed');
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

        $shiftService = app(EmployeeShiftAssignmentService::class);
        $result = $shiftService->assignShift((int) $data['employee_id'], $data, Auth::id());

        if (!empty($result['warning'])) {
            session()->flash('warning', $result['warning']);
        }

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
        $data['is_active'] = $request->boolean('is_active', true);

        $shiftService = app(EmployeeShiftAssignmentService::class);
        $shiftService->updateShiftAssignment((int) $employeeShiftTiming->id, $data, Auth::id());

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
        return view('hrms.attendance.types.index', compact('attendanceTypes'));
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
        abort_unless(
            $this->userHasPermission('attendance.export')
                || $this->userHasPermission('attendance.records.view_all')
                || $this->userHasPermission('attendance.monthly_report.view_all')
                || $this->canViewAll('attendance.records.view_all'),
            403
        );

        [$query, $filterRequest, $selectedEmployeeId, $selectedMonthYear, $periodLabel] = $this->resolveAttendanceRecordsQuery($request);

        $attendances = $this->orderAttendanceQuery($query, $filterRequest)->get();
        $this->normalizeAttendanceCollection($attendances);

        return view('hrms.attendance.reports.print', compact('attendances', 'periodLabel'));
    }

    public function exportPdf(Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.export')
                || $this->userHasPermission('attendance.records.view_all')
                || $this->userHasPermission('attendance.monthly_report.view_all')
                || $this->canViewAll('attendance.records.view_all'),
            403
        );

        [$query, $filterRequest, $selectedEmployeeId, $selectedMonthYear, $periodLabel] = $this->resolveAttendanceRecordsQuery($request);

        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', '300');

        $rawRows = $this->orderAttendanceQuery($query, $filterRequest)->get();

        $totalCount = $rawRows->count();
        $presentCount = 0;
        $lateCount = 0;
        $earlyOutCount = 0;
        $blockedCount = 0;
        $totalMinutes = 0;

        $rows = [];
        foreach ($rawRows as $index => $a) {
            $typeCode = optional($a->attendanceType)->code ?? 'default';
            $typeName = optional($a->attendanceType)->name ?? ucwords(str_replace('_', ' ', $a->attendance_status ?? 'N/A'));

            if ($typeCode === 'present' || $a->attendance_status === 'present') {
                $presentCount++;
            }
            if ($a->is_late) {
                $lateCount++;
            }
            if ($a->is_early_out) {
                $earlyOutCount++;
            }
            if ($a->is_blocked || $a->is_punch_blocked) {
                $blockedCount++;
            }
            $totalMinutes += (int) ($a->total_work_minutes ?? 0);

            $reasonText = $a->half_day_reason
                ?: ($a->lwp_reason
                    ?: ($a->status_reason
                        ?: ($a->remarks
                            ?: ($a->blocked_reason
                                ?: ($a->block_reason
                                    ?: ($a->auto_block_reason
                                        ?: ($a->unlock_remarks
                                            ?: ($a->approval_remarks ?: '-'))))))));

            $flags = [];
            if ($a->is_late) {
                $flags[] = 'Late ' . ($a->late_minutes ?? 0) . 'm';
            }
            if ($a->is_early_out) {
                $flags[] = 'Early ' . ($a->early_out_minutes ?? 0) . 'm';
            }
            if ($a->is_blocked || $a->is_punch_blocked) {
                $flags[] = 'Blocked';
            }
            if ($a->missed_punch || $a->is_missed_punch) {
                $flags[] = 'Missed';
            }

            $rows[] = [
                'sno' => $index + 1,
                'emp_name' => optional($a->user)->name ?? optional($a->employee)->display_name ?? 'N/A',
                'emp_code' => optional($a->employee)->employee_code ?? 'N/A',
                'dept' => optional(optional($a->employee)->department)->name ?? 'Staff',
                'shift' => optional($a->attendanceTime)->name ?? 'Default Shift',
                'date' => $a->attendance_date ? Carbon::parse($a->attendance_date)->format('d M Y') : '-',
                'mode' => ($a->punch_in_time && !in_array($typeCode, ['week_off', 'absent', 'leave'], true)) ? strtoupper($a->work_mode ?? 'WFO') : '-',
                'punch_in' => $a->punch_in_time ? Carbon::parse($a->punch_in_time)->format('h:i A') : '-',
                'punch_out' => $a->punch_out_time ? Carbon::parse($a->punch_out_time)->format('h:i A') : '-',
                'target_out' => $a->target_punch_out_time ? Carbon::parse($a->target_punch_out_time)->format('h:i A') : '-',
                'gross' => $a->gross_duration ?? 'N/A',
                'net' => $a->net_duration ?? 'N/A',
                'status_code' => $typeCode,
                'status_name' => $typeName,
                'reason' => $reasonText,
                'flags' => !empty($flags) ? implode(', ', $flags) : 'Clear',
            ];
        }

        unset($rawRows);

        $stats = [
            'total' => $totalCount,
            'present' => $presentCount,
            'late' => $lateCount,
            'early_out' => $earlyOutCount,
            'blocked' => $blockedCount,
            'total_hours' => round($totalMinutes / 60, 1),
        ];

        // 30 rows per page chunk
        $chunkedRows = array_chunk($rows, 30);
        if (empty($chunkedRows)) {
            $chunkedRows = [[]];
        }

        $pdf = Pdf::loadView('hrms.attendance.reports.pdf', compact('chunkedRows', 'periodLabel', 'stats'))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', false)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download('attendance_report_' . date('Y_m_d_His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.export')
                || $this->userHasPermission('attendance.records.view_all')
                || $this->userHasPermission('attendance.monthly_report.view_all')
                || $this->canViewAll('attendance.records.view_all'),
            403
        );

        [$query, $filterRequest, $selectedEmployeeId, $selectedMonthYear, $periodLabel] = $this->resolveAttendanceRecordsQuery($request);

        $rows = $this->orderAttendanceQuery($query, $filterRequest)->get();
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
                'Reason',
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

                $reasonText = $row->half_day_reason
                    ?: ($row->lwp_reason
                        ?: ($row->status_reason
                            ?: ($row->remarks
                                ?: ($row->blocked_reason
                                    ?: ($row->block_reason
                                        ?: ($row->auto_block_reason
                                            ?: ($row->unlock_remarks
                                                ?: ($row->approval_remarks ?: '-'))))))));

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
                    $reasonText,
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
        /** @var User|null $user */
        $user = Auth::user();
        $isEmployeeRole = ($user->role_id ?? null) == 7
            || ($user->system_role_id ?? null) == 7;

        $query = User::whereHas('employee', function ($eq) {
            $eq->activeEligible();
        })->with(['employee.department', 'employee.designation'])->orderBy('name');
        if ($isEmployeeRole || (! $this->canViewAll('attendance.records.view_all') && ! $this->canViewAll('attendance.monthly_report.view_all'))) {
            $ids = ($this->userHasPermission('attendance.monthly_report.view_team') || $this->userHasPermission('attendance.regularization.view_team')) && ! $isEmployeeRole
                ? $this->teamEmployeeIds(true)
                : array_filter([$this->ownEmployeeId()]);
            $query->whereHas('employee', fn($employeeQuery) => $employeeQuery->whereIn('id', $ids));
        }

        return $query->get();
    }

    private function scopeAttendanceQuery(mixed $query, string $allPermission, ?string $teamPermission = null)
    {
        return $this->scopeEmployeeVisibility($query, $allPermission, $teamPermission, 'employee_id');
    }

    private function activeAttendanceTypes()
    {
        return AttendanceType::where('is_active', true)
            ->whereNotIn(DB::raw('LOWER(name)'), [
                'late',
                'early leave',
                'early out',
                'missed punch',
                'punch blocked',
                'blocked'
            ])
            ->orderBy('name')
            ->get();
    }

    private function reportPeriodLabel(int|string $month, int|string $year)
    {
        return Carbon::create((int) $year, (int) $month, 1)->format('F Y');
    }

    private function canManageAttendance(): bool
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();
        return $this->userHasPermission('attendance.rules.manage')
            || $this->userHasPermission('attendance.records.manage')
            || (bool) ($authUser && method_exists($authUser, 'isSuperAdmin') && $authUser->isSuperAdmin())
            || (bool) ($authUser && method_exists($authUser, 'isAdmin') && $authUser->isAdmin());
    }

    private function canUnlockAttendance(): bool
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();
        return $this->userHasPermission('attendance.blocked.unlock')
            || (bool) ($authUser && method_exists($authUser, 'isAdmin') && $authUser->isAdmin());
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

    private function orderAttendanceQuery(mixed $query, Request $request)
    {
        if ($request->filled('from_date') || $request->filled('date') || $request->filled('to_date') || $request->filled('month')) {
            return $query->orderBy('attendance_date', 'asc')->orderBy('id', 'asc');
        }
        return $query->orderByDesc('attendance_date')->orderByDesc('id');
    }

    private function normalizeAttendanceCollection(Collection $items): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $typeCache = AttendanceType::all()->keyBy('code');

        foreach ($items as $attendance) {
            $resolved = $this->attendanceService->resolveFinalStatus($attendance);
            $resolvedCode = (string) ($resolved['status_code'] ?? '');
            if ($resolvedCode === '') {
                continue;
            }

            $attendance->attendance_status = $resolvedCode;
            $attendance->status_code = $resolvedCode;
            $attendance->status_name = $resolved['status_name'] ?? ucwords(str_replace('_', ' ', $resolvedCode));

            if (isset($typeCache[$resolvedCode])) {
                $attendance->setRelation('attendanceType', $typeCache[$resolvedCode]);
            }
        }
    }

    public function accessControl(Request $request)
    {
        $exitedEmployeeIds = DB::table('employee_exit_processes')
            ->whereNotIn('status', ['cancelled', 'rejected', 'rolled_back'])
            ->pluck('employee_id')
            ->filter()
            ->toArray();

        $query = DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
            ->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id')
            ->where(function ($q) {
                $q->where('employees_new.is_active', 1)
                    ->orWhereNull('employees_new.is_active');
            })
            ->where(function ($q) {
                $q->whereNull('employees_new.employment_status')
                    ->orWhere('employees_new.employment_status', '')
                    ->orWhereNotIn(DB::raw('LOWER(TRIM(employees_new.employment_status))'), [
                        'exited',
                        'exit',
                        'resigned',
                        'resigned_and_exited',
                        'terminated',
                        'inactive',
                        'relieved',
                        'absconded',
                        'suspended'
                    ]);
            })
            ->where(function ($q) {
                $q->whereNull('employees_new.employee_stage')
                    ->orWhereNotIn(DB::raw('LOWER(TRIM(employees_new.employee_stage))'), [
                        'exited',
                        'exit',
                        'resigned',
                        'terminated',
                        'relieved'
                    ]);
            })
            ->where(function ($q) {
                $q->whereNull('employees_new.relieving_date')
                    ->orWhere('employees_new.relieving_date', '>', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('users.is_active')
                    ->orWhere('users.is_active', 1);
            })
            ->when(!empty($exitedEmployeeIds), function ($q) use ($exitedEmployeeIds) {
                $q->whereNotIn('employees_new.id', $exitedEmployeeIds);
            })
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

        return view('hrms.attendance.access-control.index', compact('employees', 'departments', 'designations'));
    }

    public function updateAccessControl(Request $request, int|string $id)
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
            app(SidebarMenuResolverS::class)->clearCache((int) $employee->user_id);
        }

        return back()->with('success', 'Access updated for ' . ($employee->employee_code ?? 'Employee'));
    }

    public function bulkUpdateAccessControl(Request $request)
    {
        // Support selected_ids_json or employee_ids
        $employeeIds = $request->input('employee_ids', []);
        if (empty($employeeIds) && $request->filled('selected_ids_json')) {
            $decoded = json_decode($request->input('selected_ids_json'), true);
            if (is_array($decoded)) {
                $employeeIds = array_map('intval', $decoded);
            }
        }

        $target = $request->input('update_target') ?? $request->input('target_scope', 'selected');

        $exitedEmployeeIds = DB::table('employee_exit_processes')
            ->whereNotIn('status', ['cancelled', 'rejected', 'rolled_back'])
            ->pluck('employee_id')
            ->filter()
            ->toArray();

        $query = DB::table('employees_new')
            ->where(function ($q) {
                $q->where('employees_new.is_active', 1)
                    ->orWhereNull('employees_new.is_active');
            })
            ->where(function ($q) {
                $q->whereNull('employees_new.employment_status')
                    ->orWhere('employees_new.employment_status', '')
                    ->orWhereNotIn(DB::raw('LOWER(TRIM(employees_new.employment_status))'), [
                        'exited',
                        'exit',
                        'resigned',
                        'resigned_and_exited',
                        'terminated',
                        'inactive',
                        'relieved',
                        'absconded',
                        'suspended'
                    ]);
            })
            ->where(function ($q) {
                $q->whereNull('employees_new.employee_stage')
                    ->orWhereNotIn(DB::raw('LOWER(TRIM(employees_new.employee_stage))'), [
                        'exited',
                        'exit',
                        'resigned',
                        'terminated',
                        'relieved'
                    ]);
            })
            ->where(function ($q) {
                $q->whereNull('employees_new.relieving_date')
                    ->orWhere('employees_new.relieving_date', '>', now()->toDateString());
            })
            ->when(!empty($exitedEmployeeIds), function ($q) use ($exitedEmployeeIds) {
                $q->whereNotIn('employees_new.id', $exitedEmployeeIds);
            });

        if ($target === 'selected') {
            if (empty($employeeIds)) {
                return back()->with('error', 'Please select at least one active employee.');
            }
            $query->whereIn('employees_new.id', $employeeIds);
        } elseif ($target === 'department' && $request->filled('department_id')) {
            $query->where('employees_new.department_id', $request->department_id);
        } elseif ($target === 'designation' && $request->filled('designation_id')) {
            $query->where('employees_new.designation_id', $request->designation_id);
        }

        $updateData = ['updated_at' => now()];

        $mobileVal = $request->input('allow_mobile_attendance');
        if ($mobileVal === '1' || $mobileVal === 1 || $mobileVal === 'enable') {
            $updateData['allow_mobile_attendance'] = 1;
        } elseif ($mobileVal === '0' || $mobileVal === 0 || $mobileVal === 'disable') {
            $updateData['allow_mobile_attendance'] = 0;
        }

        $webVal = $request->input('allow_web_attendance');
        if ($webVal === '1' || $webVal === 1 || $webVal === 'enable') {
            $updateData['allow_web_attendance'] = 1;
        } elseif ($webVal === '0' || $webVal === 0 || $webVal === 'disable') {
            $updateData['allow_web_attendance'] = 0;
        }

        if (count($updateData) > 1) {
            $userIds = (clone $query)->whereNotNull('user_id')->pluck('user_id');
            $count = $query->update($updateData);
            $resolver = app(SidebarMenuResolverS::class);
            foreach ($userIds as $uId) {
                $resolver->clearCache((int) $uId);
            }
            return back()->with('success', "Attendance access updated for {$count} active employees.");
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
                Auth::id(),
                $workMode,
                $request->note,
                $meta
            );

            if (($result['status'] ?? null) === 'error') {
                return back()->with('error', $result['message'] ?? 'Punch in failed.');
            }

            return back()->with('success', $result['message'] ?? 'Punch in recorded successfully.');
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            Log::error('Web Punch In Exception: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
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
            'issues_blockers' => 'nullable|string|max:5000',
            'remarks' => 'nullable|string|max:1000',
            'projects' => 'nullable|array',
            'projects.*.project_id' => 'nullable',
            'projects.*.custom_project_name' => 'required_if:projects.*.project_id,custom',
            'projects.*.project_status' => 'nullable|string',
            'projects.*.tasks' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string|max:2000',
            'browser' => 'nullable|string|max:255',
            'os' => 'nullable|string|max:255',
            'gps_status' => 'nullable|string|max:255',
        ]);

        $scopeS = app(ProjectAccessScopeS::class);
        $accessibleProjectIds = $scopeS->getAccessibleProjectIds();

        $rawProjects = $request->projects ?? [];
        $todayWorkStatus = strtolower($request->today_work_status ?? $request->current_status ?? 'in_progress');
        $projectsPayload = [];
        $summaryTextLines = [];

        if (is_array($rawProjects) && count($rawProjects) > 0) {
            foreach ($rawProjects as $pIdx => $pBlock) {
                $projIdInput = $pBlock['project_id'] ?? null;
                $projId = is_numeric($projIdInput) ? (int)$projIdInput : null;
                $isCustom = $projIdInput === 'custom' || !empty($pBlock['is_custom']);
                $customName = $isCustom ? trim($pBlock['custom_project_name'] ?? '') : null;

                if ($projId && !in_array($projId, $accessibleProjectIds, true) && !$scopeS->isSuperAdminOrGlobal()) {
                    return back()->with('error', "You are not authorized to submit daily work reports for selected project ID: {$projId}.");
                }

                $projObj = $projId ? DB::table('projects')->where('id', $projId)->first() : null;
                $projectName = $projObj ? $projObj->name : ($customName ?: 'Custom / Other Work');

                $tasksPayload = [];
                $taskLines = [];

                if (isset($pBlock['tasks']) && is_array($pBlock['tasks'])) {
                    foreach ($pBlock['tasks'] as $tItem) {
                        $tName = trim($tItem['task_name'] ?? $tItem['description'] ?? '');
                        if (empty($tName)) continue;

                        $isCompleted = !empty($tItem['is_completed']) || !empty($tItem['completed']);
                        $isCompleted = ($isCompleted == 1 || $isCompleted === '1' || $isCompleted === true || $isCompleted === 'true');

                        $tasksPayload[] = [
                            'description' => $tName,
                            'task_name' => $tName,
                            'completed' => $isCompleted,
                            'is_completed' => $isCompleted,
                        ];

                        $icon = $isCompleted ? '☑' : '☐';
                        $taskLines[] = "  {$icon} {$tName}";
                    }
                }

                if (!empty($tasksPayload)) {
                    $projectsPayload[] = [
                        'project_id' => $projId,
                        'project_name' => $projectName,
                        'is_custom' => $isCustom,
                        'custom_project_name' => $customName,
                        'tasks' => $tasksPayload,
                    ];

                    $summaryTextLines[] = "Project: {$projectName}\n" . implode("\n", $taskLines);
                }
            }
        }

        // Fallback for legacy single task_name / today_work_description form
        if (empty($projectsPayload)) {
            $taskName = trim($request->task_name ?? '');
            $workDesc = trim($request->today_work_description ?? 'Daily Work Update Completed');

            $summaryTextLines[] = ($taskName ? "[{$taskName}] " : '') . $workDesc;

            $projectsPayload[] = [
                'project_id' => null,
                'project_name' => $taskName ?: 'General Work',
                'is_custom' => true,
                'custom_project_name' => $taskName,
                'tasks' => [
                    [
                        'description' => $workDesc,
                        'task_name' => $workDesc,
                        'completed' => strtolower($todayWorkStatus) === 'done',
                        'is_completed' => strtolower($todayWorkStatus) === 'done',
                    ],
                ],
            ];
        }

        $taskSummaryText = implode("\n\n", $summaryTextLines);
        $taskSummaryText .= "\n\nToday's Work Status: " . ucfirst(str_replace('_', ' ', $todayWorkStatus));

        if (!empty($request->issues_blockers)) {
            $taskSummaryText .= "\n\nIssues / Blockers:\n" . trim($request->issues_blockers);
        }
        if (!empty($request->remarks)) {
            $taskSummaryText .= "\n\nAdditional Notes:\n" . trim($request->remarks);
        }

        $taskSummaryJson = [
            'projects' => $projectsPayload,
            'today_work_status' => $todayWorkStatus,
            'current_status' => $todayWorkStatus,
            'issues_blockers' => $request->issues_blockers,
            'remarks' => $request->remarks,
            'additional_notes' => $request->remarks,
            'task_name' => $projectsPayload[0]['project_name'] ?? 'Daily Work',
            'today_work_description' => $taskSummaryText,
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
            Auth::id(),
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
        $employee = DB::table('employees_new')->where('user_id', Auth::id())->first();
        if (! $employee) {
            return back()->with('error', 'Employee profile not found.');
        }

        $empObj = EmployeeM::find($employee->id);
        $todayStatusResult = $this->mobileService->todayStatus(Auth::id());
        $attendancePayload = $todayStatusResult['data'] ?? [];

        $todayDate = Carbon::now($this->attendanceService->attendanceTimezone())->toDateString();
        $attendanceRecord = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $todayDate)
            ->first();

        $workLogs = $attendanceRecord?->workLogs;
        $workSummaryLog = $workLogs?->first();

        return view('hrms.attendance.records.today', [
            'employee' => $empObj,
            'attendancePayload' => $attendancePayload,
            'attendanceRecord' => $attendanceRecord,
            'workSummaryLog' => $workSummaryLog,
            'canWebPunch' => $empObj ? $empObj->canUseWebAttendance() : false,
            'canMobilePunch' => $empObj ? $empObj->canUseMobileAttendance() : true,
        ]);
    }
}
