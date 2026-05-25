<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Models\Core\AccessM;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamLeaveCalendarC extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        abort_unless(
            Auth::user()->hasPermission('leave.team_calendar.view')
            || Auth::user()->hasPermission('leave.approvals.view')
            || Auth::user()->hasPermission('leave.approvals.view_team')
            || Auth::user()->hasPermission('leave.approvals.view_all'),
            403
        );

        $selectedMonth = (int) $request->input('month', today()->month);
        $selectedYear = (int) $request->input('year', today()->year);

        $firstDayOfMonth = Carbon::create($selectedYear, $selectedMonth, 1);
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
            ->whereMonth('start_date', today()->month)
            ->whereYear('start_date', today()->year)
            ->count();

        $stats['lwp_this_month'] = LeaveRequestM::where('status', 'approved')
            ->whereMonth('start_date', today()->month)
            ->whereYear('start_date', today()->year)
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
        $leavesQuery = LeaveRequestM::with(['employee.user', 'employee.department', 'leaveType', 'dates', 'approver'])
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
            $leavesQuery->where('status', $request->status);
        } else {
            // Default: hide rejected
            $leavesQuery->where('status', '!=', 'rejected');
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
                'date' => Carbon::create($prevMonthObj->year, $prevMonthObj->month, $dayNum),
                'is_current_month' => false,
            ];
        }

        // Fill current month days
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $cells[] = [
                'date' => Carbon::create($selectedYear, $selectedMonth, $day),
                'is_current_month' => true,
            ];
        }

        // Fill next month days to complete full grid row multiples of 7
        $totalCells = count($cells);
        $nextMonthObj = (clone $firstDayOfMonth)->addMonth();
        $nextMonthDaysToFill = (7 - ($totalCells % 7)) % 7;
        for ($day = 1; $day <= $nextMonthDaysToFill; $day++) {
            $cells[] = [
                'date' => Carbon::create($nextMonthObj->year, $nextMonthObj->month, $day),
                'is_current_month' => false,
            ];
        }

        // 5. Map Leaves to Date Cells for quick access in UI
        $calendarData = [];
        foreach ($cells as $cell) {
            $dateStr = $cell['date']->format('Y-m-d');
            $dateLeaves = [];
            foreach ($leaves as $leave) {
                if ($cell['date']->between($leave->start_date, $leave->end_date)) {
                    $dateLeaves[] = $leave;
                }
            }
            $calendarData[$dateStr] = [
                'date' => $cell['date'],
                'is_current_month' => $cell['is_current_month'],
                'leaves' => $dateLeaves
            ];
        }

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
            'accesses'
        ))->with('active', 'leave_management');
    }

    private function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
