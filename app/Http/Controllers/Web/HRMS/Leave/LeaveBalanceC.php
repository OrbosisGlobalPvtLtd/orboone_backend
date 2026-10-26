<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveBalanceC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private LeaveBalanceService $balanceService
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');
        $isExplicitAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array($roleId, [1, 2, 3], true)
            || in_array($roleName, ['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true);

        $isEmployee = ($roleId === 7 || $roleName === 'employee' || (method_exists($user, 'isEmployee') && $user->isEmployee())) && ! $isExplicitAdmin;

        $isAdmin = ! $isEmployee && (
            $isExplicitAdmin
            || $this->userHasPermission('leave.balance.view_all')
        );

        $isManager = ! $isEmployee && ! $isAdmin && (
            $roleId === 9
            || $roleName === 'manager'
            || $this->userHasPermission('leave.balance.view_team')
        );

        $canViewOthers = ! $isEmployee && ($isAdmin || $isManager);
        $isEmployeeRole = $isEmployee || ! $canViewOthers;

        abort_unless(
            $isEmployeeRole
            || $isAdmin
            || $isManager
            || $this->userHasPermission('leave.balance.view_all')
            || $this->userHasPermission('leave.balance.view_team')
            || $this->userHasPermission('leave.balance.view_own')
            || $this->userHasPermission('leave.balance.view'),
            403
        );

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $date = Carbon::now('Asia/Kolkata');

        $currentEmp = $this->currentEmployee();
        $targetEmployeeId = null;

        if ($isEmployeeRole) {
            abort_if(! $currentEmp, 403, 'No employee record linked to your account.');
            $targetEmployeeId = (int) $currentEmp->id;
            $canViewOthers = false;

            // Restrict dropdown list for employees to strictly contain only their own profile
            $currentEmpOption = (object) [
                'id' => $currentEmp->id,
                'display_name' => $user->name ?? $currentEmp->display_name ?? 'My Profile',
                'user_name' => $user->name ?? $currentEmp->display_name ?? 'My Profile',
                'employee_code' => $currentEmp->employee_code ?? '',
            ];
            $employees = collect([$currentEmpOption]);
        } else {
            $employees = $canViewOthers
                ? $this->scopedEmployeeOptions('leave.balance.view_all', 'leave.balance.view_team')
                : collect();

            if ($canViewOthers && $request->filled('employee_id')) {
                $targetEmployeeId = (int) $request->employee_id;
            } elseif ($currentEmp) {
                $targetEmployeeId = (int) $currentEmp->id;
            } elseif ($canViewOthers && $employees->isNotEmpty()) {
                $targetEmployeeId = (int) $employees->first()->id;
            }
        }

        $targetEmployee = $targetEmployeeId ? EmployeeM::with(['user', 'profile'])->find($targetEmployeeId) : null;

        $allocation = null;
        $monthlyQuotaInfo = null;
        $centralBalance = null;
        $ledgerBalances = null;

        if ($targetEmployee) {
            $policyDate = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata');
            $ledgerShape = $this->balanceService->getApiBalanceShape($targetEmployee, $policyDate);
            $allocation = $ledgerShape['allocation'] ?? $this->balanceService->allocationForRead($targetEmployee, $policyDate);
            $centralBalance = $ledgerShape['central'] ?? $this->balanceService->getCentralBalance($targetEmployee, $policyDate);
            $ledgerBalances = $ledgerShape['fields'] ?? null;
            $monthlyQuotaInfo = [
                'monthly_limit' => (float) ($centralBalance['monthly_limit'] ?? 0),
                'current_month_used' => (float) ($centralBalance['current_month_used'] ?? 0),
                'carry_forward_available' => (float) ($centralBalance['carry_forward_available'] ?? 0),
                'available_this_month' => (float) ($centralBalance['available_this_month'] ?? 0),
                'total_monthly_remaining_paid' => (float) ($centralBalance['paid_remaining']
                    ?? $centralBalance['leave_balance']['total_remaining_paid']
                    ?? 0),
            ];
        }

        $balances = $allocation ? collect([$allocation]) : collect();
        $accesses = $this->accesses();

        return view('hrms.leave.balances.index', compact(
            'balances',
            'targetEmployee',
            'allocation',
            'monthlyQuotaInfo',
            'centralBalance',
            'ledgerBalances',
            'employees',
            'year',
            'accesses',
            'canViewOthers',
            'isEmployeeRole'
        ))->with('active', 'leave_management');
    }
}
