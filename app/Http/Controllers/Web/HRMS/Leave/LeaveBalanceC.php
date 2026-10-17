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
        abort_unless(
            $this->userHasPermission('leave.balance.view_all')
            || $this->userHasPermission('leave.balance.view_team')
            || $this->userHasPermission('leave.balance.view_own')
            || $this->userHasPermission('leave.balance.view'),
            403
        );

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $date = Carbon::now('Asia/Kolkata');

        $canViewAll = $this->canViewAll('leave.balance.view_all');
        $canViewTeam = $this->canViewTeam('leave.balance.view_team');
        $canViewOthers = $canViewAll || $canViewTeam;

        $employees = $canViewOthers
            ? $this->scopedEmployeeOptions('leave.balance.view_all', 'leave.balance.view_team')
            : collect();

        $currentEmp = $this->currentEmployee();
        $targetEmployeeId = null;

        if ($canViewOthers && $request->filled('employee_id')) {
            $targetEmployeeId = (int) $request->employee_id;
        } elseif ($currentEmp) {
            $targetEmployeeId = (int) $currentEmp->id;
        } elseif ($canViewOthers && $employees->isNotEmpty()) {
            $targetEmployeeId = (int) $employees->first()->id;
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
            'canViewOthers'
        ))->with('active', 'leave_management');
    }
}
