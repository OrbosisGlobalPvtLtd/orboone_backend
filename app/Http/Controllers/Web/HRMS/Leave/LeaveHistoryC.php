<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveHistoryC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $user = auth()->user();
        $isEmployeeRole = ($user->role_id ?? null) == 7 || ($user->system_role_id ?? null) == 7;
        $ownEmp = $user->employee ?? $this->currentEmployee();

        // Auto-expire past pending leaves
        app(\App\Services\HRMS\Leave\AutoExpireLeaveService::class)->expirePastPendingRequests();

        $query = LeaveRequestM::with(['employee.user', 'employee.department', 'employee.designation', 'leaveType', 'dates', 'approver']);

        // Employee scoping
        if ($isEmployeeRole) {
            abort_if(! $ownEmp, 403, 'No employee record linked to your account.');
            $query->where('employee_id', $ownEmp->id);
        } else {
            $query = $this->scopeEmployeeVisibility($query, 'leave.approvals.view_all', 'leave.approvals.view_team', 'employee_id');
        }

        // Apply Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('employee_id') && ! $isEmployeeRole) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');

        if ($fromDate) {
            $query->whereDate('start_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('end_date', '<=', $toDate);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee.user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('employee', function ($eq) use ($search) {
                    $eq->where('employee_code', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($request->filled('month')) {
            $query->whereMonth('start_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        $requests = $query->latest('id')->paginate(25)->appends($request->query());

        // Stats summary
        $baseStatsQuery = LeaveRequestM::query();
        if ($isEmployeeRole) {
            $baseStatsQuery->where('employee_id', $ownEmp?->id);
        } else {
            $this->scopeEmployeeVisibility($baseStatsQuery, 'leave.approvals.view_all', 'leave.approvals.view_team', 'employee_id');
        }

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'approved' => (clone $baseStatsQuery)->where('status', 'approved')->count(),
            'pending' => (clone $baseStatsQuery)->where('status', 'pending')->count(),
            'rejected' => (clone $baseStatsQuery)->where('status', 'rejected')->count(),
            'cancelled' => (clone $baseStatsQuery)->where('status', 'cancelled')->count(),
        ];

        $employees = $isEmployeeRole
            ? collect([$ownEmp])
            : $this->scopedEmployeeOptions('leave.approvals.view_all', 'leave.approvals.view_team');

        $leaveTypes = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $departments = DepartmentM::orderBy('name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.history.index', compact(
            'requests',
            'stats',
            'employees',
            'leaveTypes',
            'departments',
            'isEmployeeRole',
            'accesses'
        ))->with('active', 'leave_management');
    }
}
