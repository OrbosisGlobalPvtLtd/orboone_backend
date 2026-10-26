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
use Illuminate\Support\Facades\Auth;
use App\Services\HRMS\Leave\AutoExpireLeaveService;

class LeaveHistoryC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $user = Auth::user();
        $isEmployeeRole = ($user->role_id ?? null) == 7 || ($user->system_role_id ?? null) == 7;
        $ownEmp = $user->employee ?? $this->currentEmployee();

        // Auto-expire past pending leaves
        app(AutoExpireLeaveService::class)->expirePastPendingRequests();

        $query = LeaveRequestM::with([
            'employee.user',
            'employee.department',
            'employee.designation',
            'employee.reportingManager.user',
            'leaveType',
            'dates',
            'approver',
            'managerApprover',
            'hrApprover',
            'canceller',
            'reportingManager.user',
        ]);

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

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $currentMonth = (int) Carbon::now('Asia/Kolkata')->month;

        if ($request->has('month')) {
            $selectedMonth = (string) $request->month;
        } else {
            $selectedMonth = (string) $currentMonth;
        }

        
        if ($selectedMonth !== 'all' && $selectedMonth !== '') {
            $m = (int) $selectedMonth;
            $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth()->toDateString();
            $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth()->toDateString();

            $query->where(function ($q) use ($monthStart, $monthEnd) {
                $q->where(function ($sub) use ($monthStart, $monthEnd) {
                    $sub->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $monthEnd)
                        ->whereDate('end_date', '>=', $monthStart);
                })->orWhere(function ($sub) use ($monthStart, $monthEnd) {
                    $sub->whereNull('end_date')
                        ->whereDate('start_date', '>=', $monthStart)
                        ->whereDate('start_date', '<=', $monthEnd);
                })->orWhere(function ($sub) use ($monthStart, $monthEnd) {
                    $sub->whereDate('created_at', '>=', $monthStart)
                        ->whereDate('created_at', '<=', $monthEnd);
                });
            });
        } elseif ($request->filled('year') && $request->year !== 'all') {
            $yearStart = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
            $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();

            $query->where(function ($q) use ($yearStart, $yearEnd) {
                $q->where(function ($sub) use ($yearStart, $yearEnd) {
                    $sub->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $yearEnd)
                        ->whereDate('end_date', '>=', $yearStart);
                })->orWhere(function ($sub) use ($yearStart, $yearEnd) {
                    $sub->whereNull('end_date')
                        ->whereDate('start_date', '>=', $yearStart)
                        ->whereDate('start_date', '<=', $yearEnd);
                })->orWhere(function ($sub) use ($yearStart, $yearEnd) {
                    $sub->whereDate('created_at', '>=', $yearStart)
                        ->whereDate('created_at', '<=', $yearEnd);
                });
            });
        }

        $perPage = (int) $request->get('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $requests = $query->latest('id')->paginate($perPage)->appends($request->query());

        // Enrich requests with allocation metrics and names for Leave Request Details view modal
        $empIds = $requests->pluck('employee_id')->filter()->unique()->all();
        $allocations = !empty($empIds)
            ? \Illuminate\Support\Facades\DB::table('leave_allocations')
                ->whereIn('employee_id', $empIds)
                ->where('year', $year ?: Carbon::now('Asia/Kolkata')->year)
                ->get()
                ->keyBy('employee_id')
            : collect();

        foreach ($requests as $item) {
            $emp = $item->employee;
            $item->display_name = $emp?->user?->name ?? $emp?->employee_code ?? 'Employee';
            $item->employee_code = $emp?->employee_code ?? '—';
            $item->department_name = $emp?->department?->name ?? 'General';
            $item->designation_name = $emp?->designation?->name ?? 'Employee';
            $item->leave_type_name = $item->leaveType?->name ?? 'Leave';
            $item->current_reporting_manager_id = $emp?->reporting_manager_employee_id;
            $item->reporting_manager_name = $emp?->reportingManager?->user?->name ?? null;
            $item->manager_approver_name = $item->managerApprover?->name ?? null;
            $item->hr_approver_name = $item->hrApprover?->name ?? ($item->approver?->name ?? null);
            $item->rejected_by_name = $item->approver?->name ?? null;

            $la = $allocations->get($item->employee_id);
            $item->paid_allocated = $la?->paid_allocated ?? 0;
            $item->paid_remaining = $la?->paid_remaining ?? 0;
            $item->paid_used = $la?->paid_used ?? 0;
            $item->sick_allocated = $la?->sick_allocated ?? 0;
            $item->sick_remaining = $la?->sick_remaining ?? 0;
            $item->sick_used = $la?->sick_used ?? 0;
            $item->comp_off_allocated = $la?->comp_off_allocated ?? 0;
            $item->comp_off_remaining = $la?->comp_off_remaining ?? 0;
            $item->comp_off_used = $la?->comp_off_used ?? 0;
        }

        // Stats summary
        $baseStatsQuery = LeaveRequestM::query();
        if ($isEmployeeRole) {
            $baseStatsQuery->where('employee_id', $ownEmp?->id);
        } else {
            $this->scopeEmployeeVisibility($baseStatsQuery, 'leave.approvals.view_all', 'leave.approvals.view_team', 'employee_id');
        }

        if ($selectedMonth !== 'all' && $selectedMonth !== '') {
            $m = (int) $selectedMonth;
            $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth()->toDateString();
            $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth()->toDateString();

            $baseStatsQuery->where(function ($q) use ($monthStart, $monthEnd) {
                $q->where(function ($sub) use ($monthStart, $monthEnd) {
                    $sub->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $monthEnd)
                        ->whereDate('end_date', '>=', $monthStart);
                })->orWhere(function ($sub) use ($monthStart, $monthEnd) {
                    $sub->whereNull('end_date')
                        ->whereDate('start_date', '>=', $monthStart)
                        ->whereDate('start_date', '<=', $monthEnd);
                })->orWhere(function ($sub) use ($monthStart, $monthEnd) {
                    $sub->whereDate('created_at', '>=', $monthStart)
                        ->whereDate('created_at', '<=', $monthEnd);
                });
            });
        } elseif ($request->filled('year') && $request->year !== 'all') {
            $yearStart = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
            $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();

            $baseStatsQuery->where(function ($q) use ($yearStart, $yearEnd) {
                $q->where(function ($sub) use ($yearStart, $yearEnd) {
                    $sub->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $yearEnd)
                        ->whereDate('end_date', '>=', $yearStart);
                })->orWhere(function ($sub) use ($yearStart, $yearEnd) {
                    $sub->whereNull('end_date')
                        ->whereDate('start_date', '>=', $yearStart)
                        ->whereDate('start_date', '<=', $yearEnd);
                })->orWhere(function ($sub) use ($yearStart, $yearEnd) {
                    $sub->whereDate('created_at', '>=', $yearStart)
                        ->whereDate('created_at', '<=', $yearEnd);
                });
            });
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

        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $userRoleName = strtolower($user->role->name ?? '');
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || in_array((int) ($user->system_role_id ?? $user->role_id ?? 0), [1, 2, 3], true)
            || in_array($userRoleName, ['admin', 'super_admin', 'hr_admin', 'hr admin', 'hr'], true)
            || $this->userHasPermission('leave.approvals.view_all')
            || $this->userHasPermission('leave.approvals.approve');

        $isSuperAdminUser = $isSuperAdmin;
        $isHrAdminUser = $isHrOrAdmin;
        $authEmpId = $ownEmp?->id;

        return view('hrms.leave.history.index', compact(
            'requests',
            'stats',
            'employees',
            'leaveTypes',
            'departments',
            'isEmployeeRole',
            'accesses',
            'selectedMonth',
            'year',
            'isSuperAdminUser',
            'isHrAdminUser',
            'authEmpId'
        ))->with('active', 'leave_management');
    }
}
