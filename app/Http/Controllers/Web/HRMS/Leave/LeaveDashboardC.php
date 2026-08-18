<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Leave\HolidayM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use Carbon\Carbon;

class LeaveDashboardC extends Controller
{
    use HrmsCrudPage;

    public function index()
    {
        $now = Carbon::now('Asia/Kolkata');
        $today = $now->toDateString();
        $user = auth()->user();
        $isEmployeeRole = ($user->role_id ?? null) == 7 || ($user->system_role_id ?? null) == 7;
        $ownEmp = $user->employee ?? $this->currentEmployee();

        $baseQuery = LeaveRequestM::query();
        if ($isEmployeeRole && $ownEmp) {
            $baseQuery->where('employee_id', $ownEmp->id);
        } elseif (! $isEmployeeRole) {
            $this->scopeEmployeeVisibility($baseQuery, 'leave.approvals.view_all', 'leave.approvals.view_team', 'employee_id');
        }

        $stats = [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved_this_month' => (clone $baseQuery)->where('status', 'approved')->whereMonth('start_date', $now->month)->whereYear('start_date', $now->year)->count(),
            'on_leave_today' => (clone $baseQuery)->where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'lwp_this_month' => (clone $baseQuery)->where('status', 'approved')->whereMonth('start_date', $now->month)->whereYear('start_date', $now->year)->sum('lwp_days'),
            'allocated_employees' => LeaveAllocationM::where('year', $now->year)->distinct('employee_id')->count('employee_id'),
        ];

        $recentRequestsQuery = LeaveRequestM::with(['employee.user', 'employee.department', 'leaveType']);
        if ($isEmployeeRole && $ownEmp) {
            $recentRequestsQuery->where('employee_id', $ownEmp->id);
        } elseif (! $isEmployeeRole) {
            $this->scopeEmployeeVisibility($recentRequestsQuery, 'leave.approvals.view_all', 'leave.approvals.view_team', 'employee_id');
        }

        $recentRequests = $recentRequestsQuery
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->latest('id')
            ->limit(10)
            ->get();

        $onLeaveTodayQuery = LeaveRequestM::with(['employee.user', 'employee.department', 'leaveType'])
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);

        if ($isEmployeeRole && $ownEmp) {
            $onLeaveTodayQuery->where('employee_id', $ownEmp->id);
        } elseif (! $isEmployeeRole) {
            $this->scopeEmployeeVisibility($onLeaveTodayQuery, 'leave.approvals.view_all', 'leave.approvals.view_team', 'employee_id');
        }

        $onLeaveTodayList = $onLeaveTodayQuery->limit(6)->get();

        $upcomingHolidays = HolidayM::whereDate('holiday_date', '>=', $today)
            ->orderBy('holiday_date')
            ->limit(5)
            ->get();

        $leaveTypes = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.dashboard.index', compact('stats', 'recentRequests', 'onLeaveTodayList', 'upcomingHolidays', 'leaveTypes', 'isEmployeeRole', 'accesses'))
            ->with('active', 'leave_management');
    }
}
