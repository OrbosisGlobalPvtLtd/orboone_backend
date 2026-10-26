<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Models\Core\AccessM;
use App\Models\Core\UserM;
use App\Models\HRMS\Leave\HolidayM;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamLeaveCalendarC extends Controller
{
    public function index(Request $request)
    {
        /** @var UserM|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole('super_admin'));

        $isHrOrAdmin = $isSuperAdmin
            || ($user->roles()->whereIn('slug', ['super_admin', 'admin', 'hr_admin'])->exists());

        $isTeamManager = false;
        $userEmp = \Illuminate\Support\Facades\DB::table('employees_new')->where('user_id', $user->id)->first(['id']);
        if ($userEmp) {
            $teamScope = app(\App\Services\HRMS\Team\TeamManagementScopeS::class);
            $isTeamManager = ! empty($teamScope->getTeamEmployeeIds($userEmp->id));
        }

        $userRoles = $user->roles ?? collect();
        $isOnlyEmployee = $userRoles->isNotEmpty() && $userRoles->every(fn($r) => $r->slug === 'employee');

        // Pure employees without managerial reportees are strictly forbidden
        if ($isOnlyEmployee && ! $isTeamManager && ! $isHrOrAdmin) {
            abort(403, 'Employees are not authorized to view the Team Leave Calendar.');
        }

        abort_unless(
            $isHrOrAdmin
            || $isTeamManager
            || $user->hasPermission('leave.team_calendar.view')
            || $user->hasPermission('leave.approvals.view')
            || $user->hasPermission('leave.approvals.view_team')
            || $user->hasPermission('leave.approvals.view_all'),
            403,
            'You do not have permission to view the Team Leave Calendar.'
        );

        $selectedMonth = $request->filled('month') ? (int) $request->input('month') : today()->month;
        $selectedYear = $request->filled('year') ? (int) $request->input('year') : today()->year;

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = today()->month;
        }
        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = today()->year;
        }

        $firstDayOfMonth = Carbon::create($selectedYear, $selectedMonth, 1)->startOfDay();
        $startOfMonthDate = (clone $firstDayOfMonth)->startOfMonth();
        $endOfMonthDate = (clone $firstDayOfMonth)->endOfMonth();

        // 1. Fetch Real Database Stats
        $stats = [];
        $stats['on_leave_today'] = LeaveRequestM::where('status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();

        $stats['upcoming_leaves'] = LeaveRequestM::where('status', 'approved')
            ->whereDate('start_date', '>', today())
            ->count();

        $stats['pending_requests'] = LeaveRequestM::where('status', 'pending')
            ->count();

        $stats['approved_this_month'] = LeaveRequestM::where('status', 'approved')
            ->whereMonth('start_date', $selectedMonth)
            ->whereYear('start_date', $selectedYear)
            ->count();

        $stats['lwp_this_month'] = LeaveRequestM::where('status', 'approved')
            ->whereMonth('start_date', $selectedMonth)
            ->whereYear('start_date', $selectedYear)
            ->where(function($q) {
                $q->where('lwp_days', '>', 0)
                  ->orWhereHas('leaveType', function($lt) {
                      $lt->where('name', 'like', '%LWP%')
                        ->orWhere('code', 'like', '%LWP%');
                  });
            })
            ->count();

        // 2. Fetch Filter Options
        $departments = DepartmentM::orderBy('name')->get();
        $employees = EmployeeM::active()->get();
        $leaveTypes = LeaveTypeM::orderBy('name')->get();

        // 3. Fetch Leaves data with filters
        $leavesQuery = LeaveRequestM::with(['employee.user', 'employee.department', 'employee.designation', 'leaveType', 'dates', 'approver'])
            ->where(function($q) use ($startOfMonthDate, $endOfMonthDate) {
                $q->whereDate('start_date', '<=', $endOfMonthDate)
                  ->whereDate('end_date', '>=', $startOfMonthDate);
            });

        if ($request->filled('department_id')) {
            $leavesQuery->whereHas('employee', function($emp) use ($request) {
                $emp->where('department_id', $request->department_id);
            });
        }
        if ($request->filled('employee_id')) {
            $leavesQuery->where('employee_id', $request->employee_id);
        }
        if ($request->filled('leave_type_id')) {
            $leavesQuery->where('leave_type_id', $request->leave_type_id);
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'all') {
                // Include all statuses
            } else {
                $leavesQuery->where('status', $request->status);
            }
        } else {
            // Default: Active leaves only (approved & pending), exclude cancelled/void/rejected
            $leavesQuery->whereIn('status', ['approved', 'pending']);
        }

        $leaves = $leavesQuery->latest()->get();

        // 4. Generate Calendar ISO Grid Cells
        $cells = [];
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $startOfWeekDay = $firstDayOfMonth->dayOfWeekIso; // 1 for Mon, 7 for Sun

        // Fill previous month days
        $prevMonthObj = (clone $firstDayOfMonth)->subMonth();
        $daysInPrevMonth = $prevMonthObj->daysInMonth;
        for ($i = $startOfWeekDay - 1; $i > 0; $i--) {
            $dayNum = $daysInPrevMonth - $i + 1;
            $cells[] = [
                'date' => Carbon::create($prevMonthObj->year, $prevMonthObj->month, $dayNum)->startOfDay(),
                'is_current_month' => false,
            ];
        }

        // Fill current month days
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $cells[] = [
                'date' => Carbon::create($selectedYear, $selectedMonth, $day)->startOfDay(),
                'is_current_month' => true,
            ];
        }

        // Fill next month days to complete full grid row multiples of 7
        $totalCells = count($cells);
        $nextMonthObj = (clone $firstDayOfMonth)->addMonth();
        $nextMonthDaysToFill = (7 - ($totalCells % 7)) % 7;
        for ($day = 1; $day <= $nextMonthDaysToFill; $day++) {
            $cells[] = [
                'date' => Carbon::create($nextMonthObj->year, $nextMonthObj->month, $day)->startOfDay(),
                'is_current_month' => false,
            ];
        }

        // 5. Fetch Holidays for displayed window
        $firstGridDate = $cells[0]['date'];
        $lastGridDate = $cells[count($cells) - 1]['date'];
        $holidays = HolidayM::where('is_active', true)
            ->whereDate('holiday_date', '>=', $firstGridDate)
            ->whereDate('holiday_date', '<=', $lastGridDate)
            ->get()
            ->keyBy(function($h) {
                return $h->holiday_date->format('Y-m-d');
            });

        // 6. Map Leaves & Holidays to Date Cells
        $calendarData = [];
        foreach ($cells as $cell) {
            $dateStr = $cell['date']->format('Y-m-d');
            $dateLeaves = [];
            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date)->startOfDay();
                $end = Carbon::parse($leave->end_date)->startOfDay();
                if ($cell['date']->gte($start) && $cell['date']->lte($end)) {
                    $dateLeaves[] = $leave;
                }
            }
            $calendarData[$dateStr] = [
                'date' => $cell['date'],
                'is_current_month' => $cell['is_current_month'],
                'leaves' => $dateLeaves,
                'holiday' => $holidays->get($dateStr)
            ];
        }

        $prevMonthDate = (clone $firstDayOfMonth)->subMonth();
        $nextMonthDate = (clone $firstDayOfMonth)->addMonth();
        $accesses = $this->accesses();

        return view('hrms.leave.calendar.index', compact(
            'stats',
            'departments',
            'employees',
            'leaveTypes',
            'leaves',
            'calendarData',
            'cells',
            'selectedMonth',
            'selectedYear',
            'prevMonthDate',
            'nextMonthDate',
            'accesses'
        ))->with('active', 'leave_management');
    }

    private function accesses()
    {
        /** @var UserM|null $user */
        $user = Auth::user();
        $roleId = $user?->role_id ?? $user?->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
