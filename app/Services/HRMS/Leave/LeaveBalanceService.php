<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeaveTypeM;
use Carbon\Carbon;

class LeaveBalanceService
{
    public function __construct(
        private MonthlyLeaveQuotaService $monthlyQuotaService,
        private LeaveAllocationService $allocationService
    ) {
    }

    /**
     * Get central leave balance structure for employee (Single Source of Truth).
     */
    public function isInternOrProbation(EmployeeM $employee): bool
    {
        $stage = strtolower((string) ($employee->employee_stage ?: $employee->employment_type ?: ''));
        return str_contains($stage, 'intern') || str_contains($stage, 'probation');
    }

    /**
     * Get central leave balance structure for employee (Single Source of Truth).
     */
    public function getCentralBalance(EmployeeM $employee, ?Carbon $date = null): array
    {
        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $year = (int) $date->year;

        $quota = $this->monthlyQuotaService->getMonthlyQuota($employee, $date);
        $allocation = $this->allocationService->getOrGenerate($employee, $year);

        $isInternOrProbation = $this->isInternOrProbation($employee);

        $monthlyQuota = (float) ($allocation->monthly_quota ?? $quota['monthly_limit'] ?? 2.0);
        if ($isInternOrProbation || (float) $allocation->paid_allocated < $monthlyQuota) {
            $monthlyQuota = min($monthlyQuota, (float) $allocation->paid_allocated);
        }

        $monthlyCarry = (float) ($allocation->monthly_carry_forward ?? $quota['carry_forward_available'] ?? 0.0);
        $monthlyCarry = min($monthlyCarry, (float) $allocation->paid_remaining);

        $monthlyUsed = (float) ($allocation->monthly_used_this_month ?? $quota['current_month_used'] ?? 0.0);
        $totalMonthlyRemaining = min(max(0.0, $monthlyQuota + $monthlyCarry - $monthlyUsed), (float) $allocation->paid_remaining);

        $totalPaid = (float) ($allocation->paid_remaining ?? $totalMonthlyRemaining);
        $totalSick = (float) ($allocation->sick_remaining ?? 0.0);
        $totalCompOff = (float) ($allocation->comp_off_remaining ?? 0.0);
        $totalLwp = (float) ($allocation->lwp_used ?? 0.0);

        $paidAlloc = (float) ($allocation->paid_allocated ?? 0.0);
        $sickAlloc = (float) ($allocation->sick_allocated ?? 0.0);
        $compAlloc = (float) ($allocation->comp_off_allocated ?? 0.0);
        $totalAlloc = (float) ($allocation->total_allocated ?? 0.0);

        return [
            'employee_id' => (int) $employee->id,
            'paid_allocated' => round($paidAlloc, 2),
            'paid_remaining' => round($totalPaid, 2),
            'sick_allocated' => round($sickAlloc, 2),
            'sick_remaining' => round($totalSick, 2),
            'comp_off_allocated' => round($compAlloc, 2),
            'comp_off_remaining' => round($totalCompOff, 2),
            'total_allocated' => round($totalAlloc, 2),
            'monthly_limit' => round($monthlyQuota, 2),
            'current_month_used' => round($monthlyUsed, 2),
            'carry_forward_available' => round($monthlyCarry, 2),
            'available_this_month' => round($totalMonthlyRemaining, 2),
            'leave_balance' => [
                'monthly_quota' => round($monthlyQuota, 2),
                'monthly_carry_forward' => round($monthlyCarry, 2),
                'monthly_used_this_month' => round($monthlyUsed, 2),
                'total_monthly_remaining_paid' => round($totalMonthlyRemaining, 2),
                'total_remaining_paid' => round($totalPaid, 2),
                'total_remaining_sick' => round($totalSick, 2),
                'total_remaining_comp_off' => round($totalCompOff, 2),
                'total_lwp' => round($totalLwp, 2),
            ],
        ];
    }

    /**
     * Get type-wise balance breakdown dynamically from leave_types table and allocation.
     */
    public function getTypeWiseBalances(EmployeeM $employee, ?Carbon $date = null): array
    {
        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $central = $this->getCentralBalance($employee, $date);
        $lb = $central['leave_balance'];

        $types = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $list = [];

        foreach ($types as $type) {
            $code = strtoupper($type->code ?? '');
            $name = strtolower($type->name ?? '');

            $available = 0.0;
            $isPaid = (bool) $type->is_paid;

            if ($code === 'PL' || $name === 'paid leave' || $isPaid) {
                $available = (float) $lb['total_monthly_remaining_paid'];
            } elseif ($code === 'SL' || $name === 'sick leave' || (bool) $type->is_sick) {
                $available = (float) $lb['total_remaining_sick'];
            } elseif ($code === 'COMP_OFF' || (bool) $type->is_comp_off) {
                $available = (float) $lb['total_remaining_comp_off'];
            } else {
                $available = 0.0;
            }

            $item = [
                'leave_type_id' => (int) $type->id,
                'leave_type' => $type->name,
                'code' => $type->code,
                'is_paid' => $isPaid,
                'available' => round($available, 2),
            ];

            if ($code === 'PL' || $name === 'paid leave' || $isPaid) {
                $item['monthly_quota'] = (float) $lb['monthly_quota'];
                $item['monthly_carry_forward'] = (float) $lb['monthly_carry_forward'];
                $item['monthly_used_this_month'] = (float) $lb['monthly_used_this_month'];
                $item['total_monthly_remaining_paid'] = (float) $lb['total_monthly_remaining_paid'];
            }

            $list[] = $item;
        }

        return $list;
    }

    /**
     * Get paginated balance history from leave_balance_logs.
     */
    public function getBalanceHistory(EmployeeM $employee, int $perPage = 20): array
    {
        $logs = LeaveBalanceLogM::where('employee_id', $employee->id)
            ->latest('id')
            ->paginate($perPage);

        $items = collect($logs->items())->map(function (LeaveBalanceLogM $log) {
            $createdAt = Carbon::parse($log->created_at)->setTimezone('Asia/Kolkata');
            return [
                'id' => (int) $log->id,
                'action' => $log->action,
                'month' => $createdAt->format('Y-m'),
                'credit' => (float) ($log->credit ?? 0.0),
                'debit' => (float) ($log->debit ?? 0.0),
                'balance_before' => (float) ($log->balance_before ?? 0.0),
                'balance_after' => (float) ($log->balance_after ?? 0.0),
                'remarks' => $log->remarks ?? '',
                'created_at' => $createdAt->toDateTimeString(),
            ];
        })->values()->toArray();

        return [
            'history' => $items,
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ];
    }
}
