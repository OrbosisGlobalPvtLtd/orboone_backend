<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\Core\AccessM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveRequestM;
use Carbon\Carbon;

class LeaveDashboardC extends Controller
{
    public function index()
    {
        $now = Carbon::now('Asia/Kolkata');
        $stats = [
            'pending' => LeaveRequestM::where('status', 'pending')->count(),
            'approved_this_month' => LeaveRequestM::where('status', 'approved')->whereMonth('approved_at', $now->month)->whereYear('approved_at', $now->year)->count(),
            'lwp_this_month' => LeaveRequestM::where('status', 'approved')->whereMonth('start_date', $now->month)->whereYear('start_date', $now->year)->sum('lwp_days'),
            'allocated_employees' => LeaveAllocationM::where('year', $now->year)->distinct('employee_id')->count('employee_id'),
        ];

        $recentRequests = LeaveRequestM::with(['employee.user', 'leaveType'])->latest()->limit(12)->get();
        $accesses = $this->accesses();

        return view('hrms.leave.dashboard.index', compact('stats', 'recentRequests', 'accesses'))->with('active', 'leave_management');
    }

    private function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
