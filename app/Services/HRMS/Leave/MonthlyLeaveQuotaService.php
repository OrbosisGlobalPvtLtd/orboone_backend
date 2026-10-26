<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Enums\LeaveBucket;
use Carbon\Carbon;

/**
 * @deprecated Monthly quota/carry-forward leave was replaced by monthly PL/SL ledger accrual.
 * This compatibility adapter is read-only and never mutates allocation snapshots.
 */
class MonthlyLeaveQuotaService
{
    public function __construct(private LeaveAllocationService $allocationService)
    {
    }

    public function getMonthlyQuota(EmployeeM $employee, ?Carbon $date = null, ?int $userId = null): array
    {
        $date = ($date ?? Carbon::now('Asia/Kolkata'))->copy()->setTimezone('Asia/Kolkata');
        $allocation = LeaveAllocationM::query()->where('employee_id', $employee->id)
            ->where('year', $date->year)->first();

        if ($allocation?->isLedgerBacked()) {
            $snapshot = app(LeaveBalanceService::class)->getLedgerSnapshot($allocation);
            $available = bcadd($snapshot[LeaveBucket::PL->value]['available'], $snapshot[LeaveBucket::SL->value]['available'], 6);
            return [
                'monthly_limit' => 0.0,
                'current_month_used' => 0.0,
                'carry_forward_available' => 0.0,
                'available_this_month' => (float) $available,
                'allocation' => $allocation,
            ];
        }

        if ($this->allocationService->stageFor($employee) !== LeaveAllocationService::STAGE_PERMANENT) {
            $allocation ??= LeaveAllocationM::query()->where('employee_id', $employee->id)
                ->where('employment_stage', $this->allocationService->stageFor($employee))
                ->orderByDesc('year')->first();
            return [
                'monthly_limit' => 0.0,
                'current_month_used' => 0.0,
                'carry_forward_available' => 0.0,
                'available_this_month' => (float) ($allocation?->paid_remaining ?? 0),
                'allocation' => $allocation,
            ];
        }

        return [
            'monthly_limit' => 0.0,
            'current_month_used' => 0.0,
            'carry_forward_available' => 0.0,
            'available_this_month' => 0.0,
            'allocation' => $allocation,
        ];
    }

    /** @deprecated Leave usage is written by LeaveBalanceMovementService or the stage owner. */
    public function recordMonthlyLeaveUsage(LeaveAllocationM $allocation, float $days): void
    {
    }

    /** @deprecated Reversals are written by LeaveBalanceMovementService or the stage owner. */
    public function refundMonthlyLeaveUsage(LeaveAllocationM $allocation, float $days): void
    {
    }

    /** @deprecated Ledger lapse is handled by hrms:leave-lapse-ledger. */
    public function resetYearEndQuota(int $year, ?int $userId = null): array
    {
        return ['year' => $year, 'reset_count' => 0, 'retired' => true];
    }
}
