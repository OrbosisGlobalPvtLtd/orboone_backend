<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyLeaveQuotaService
{
    public function __construct(
        private LeavePolicyService $policyService,
        private LeaveAllocationService $allocationService
    ) {
    }

    /**
     * Calculate and manage employee's monthly leave quota, unused quota carry-forward,
     * and usage within the calendar year.
     */
    public function getMonthlyQuota(EmployeeM $employee, ?Carbon $date = null, ?int $userId = null): array
    {
        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $year = (int) $date->year;
        $month = (int) $date->month;
        $targetMonthStr = sprintf('%04d-%02d', $year, $month);

        $policy = $this->policyService->forEmployee($employee, $date);
        $allocation = $this->allocationService->getOrGenerate($employee, $year, $userId);

        $monthlyLimit = (float) ($policy->monthly_leave_limit ?? 2.0);
        $stageStr = strtolower((string) ($employee->employee_stage ?: $employee->employment_type ?: $allocation->employment_stage ?: ''));
        $isInternOrProbation = str_contains($stageStr, 'intern') || str_contains($stageStr, 'probation');
        if ($isInternOrProbation || (float) $allocation->paid_allocated < $monthlyLimit) {
            $monthlyLimit = min($monthlyLimit, (float) $allocation->paid_allocated);
        }
        $allocation->monthly_quota = $monthlyLimit;

        if ($allocation->last_month_processed === null) {
            $allocation->last_month_processed = $targetMonthStr;
            $allocation->monthly_used_this_month = (float) ($allocation->monthly_used_this_month ?? 0.0);
            $allocation->monthly_carry_forward = (float) ($allocation->monthly_carry_forward ?? 0.0);
            $this->allocationService->recalculateAllocationFields($allocation);
            $allocation->save();
        } elseif ($allocation->last_month_processed !== $targetMonthStr) {
            $lastProcessedDate = Carbon::createFromFormat('Y-m', $allocation->last_month_processed, 'Asia/Kolkata')->startOfMonth();

            if ($lastProcessedDate->year === $year && $lastProcessedDate->lt($date->copy()->startOfMonth())) {
                DB::transaction(function () use ($employee, $allocation, $policy, $monthlyLimit, $lastProcessedDate, $date, $targetMonthStr, $userId) {
                    $oldCarry = (float) ($allocation->monthly_carry_forward ?? 0.0);
                    $oldUsed = (float) ($allocation->monthly_used_this_month ?? 0.0);

                    $unusedQuota = max(0.0, round(($monthlyLimit + $oldCarry) - $oldUsed, 2));
                    $allowCarry = (bool) ($policy->allow_monthly_carry_forward ?? $policy->carry_forward_enabled ?? true);
                    $newCarryForward = $allowCarry ? min($unusedQuota, (float) $allocation->paid_remaining) : 0.0;

                    $allocation->monthly_carry_forward = $newCarryForward;
                    $allocation->monthly_used_this_month = 0.0;
                    $allocation->monthly_quota = $monthlyLimit;
                    $allocation->last_month_processed = $targetMonthStr;
                    $this->allocationService->recalculateAllocationFields($allocation);
                    $allocation->save();

                    // Log monthly earned accrual
                    LeaveBalanceLogM::create([
                        'employee_id' => $employee->id,
                        'leave_allocation_id' => $allocation->id,
                        'action' => 'monthly_earned_leave_accrual',
                        'credit' => $monthlyLimit,
                        'debit' => 0.0,
                        'balance_before' => $oldCarry,
                        'balance_after' => (float) $allocation->total_monthly_remaining_paid,
                        'remarks' => "Monthly leave accrual credited for {$date->format('F Y')}",
                        'created_by_user_id' => $userId,
                    ]);

                    if ($newCarryForward > 0) {
                        LeaveBalanceLogM::create([
                            'employee_id' => $employee->id,
                            'leave_allocation_id' => $allocation->id,
                            'action' => 'monthly_quota_carry_forward',
                            'credit' => $newCarryForward,
                            'debit' => 0.0,
                            'balance_before' => $oldCarry,
                            'balance_after' => (float) $allocation->total_monthly_remaining_paid,
                            'remarks' => "Unused {$lastProcessedDate->format('F')} monthly quota carried forward to {$date->format('F')}",
                            'created_by_user_id' => $userId,
                        ]);
                    }
                });
            } elseif ($lastProcessedDate->year !== $year) {
                DB::transaction(function () use ($employee, $allocation, $targetMonthStr, $year, $userId) {
                    $oldCarry = (float) ($allocation->monthly_carry_forward ?? 0.0);
                    $allocation->monthly_carry_forward = 0.0;
                    $allocation->monthly_used_this_month = 0.0;
                    $allocation->last_month_processed = $targetMonthStr;
                    $this->allocationService->recalculateAllocationFields($allocation);
                    $allocation->save();

                    LeaveBalanceLogM::create([
                        'employee_id' => $employee->id,
                        'leave_allocation_id' => $allocation->id,
                        'action' => 'monthly_quota_reset',
                        'credit' => 0.0,
                        'debit' => $oldCarry,
                        'balance_before' => $oldCarry,
                        'balance_after' => (float) $allocation->total_monthly_remaining_paid,
                        'remarks' => "Year-end monthly quota reset for year {$year}",
                        'created_by_user_id' => $userId,
                    ]);
                });
            }
        }

        $this->allocationService->recalculateAllocationFields($allocation);
        $allocation->save();

        $currentMonthUsed = (float) ($allocation->monthly_used_this_month ?? 0.0);
        $carryForwardAvailable = (float) ($allocation->monthly_carry_forward ?? 0.0);
        $totalMonthlyRemainingPaid = (float) ($allocation->total_monthly_remaining_paid ?? 0.0);

        return [
            'monthly_limit' => $monthlyLimit,
            'monthly_quota' => $monthlyLimit,
            'current_month_used' => $currentMonthUsed,
            'carry_forward_available' => $carryForwardAvailable,
            'available_this_month' => $totalMonthlyRemainingPaid,
            'total_monthly_remaining_paid' => $totalMonthlyRemainingPaid,
        ];
    }

    /**
     * Record monthly leave usage upon leave request approval.
     */
    public function recordMonthlyLeaveUsage(LeaveAllocationM $allocation, float $days): void
    {
        if ($days <= 0) {
            return;
        }

        $allocation->monthly_used_this_month = round((float) ($allocation->monthly_used_this_month ?? 0.0) + $days, 2);
        $this->allocationService->recalculateAllocationFields($allocation);
        $allocation->save();
    }

    /**
     * Refund monthly leave usage upon leave request cancellation/rejection.
     */
    public function refundMonthlyLeaveUsage(LeaveAllocationM $allocation, float $days): void
    {
        if ($days <= 0) {
            return;
        }

        $allocation->monthly_used_this_month = round(max(0.0, (float) ($allocation->monthly_used_this_month ?? 0.0) - $days), 2);
        $this->allocationService->recalculateAllocationFields($allocation);
        $allocation->save();
    }

    /**
     * Reset year-end monthly quota on December 31st according to DB leave policy.
     */
    public function resetYearEndQuota(int $year, ?int $userId = null): array
    {
        $allocations = LeaveAllocationM::where('year', $year)->get();
        $processed = 0;

        foreach ($allocations as $allocation) {
            $oldCarry = (float) ($allocation->monthly_carry_forward ?? 0.0);

            DB::transaction(function () use ($allocation, $oldCarry, $year, $userId) {
                $allocation->monthly_carry_forward = 0.0;
                $allocation->monthly_used_this_month = 0.0;
                $allocation->save();

                LeaveBalanceLogM::create([
                    'employee_id' => $allocation->employee_id,
                    'leave_allocation_id' => $allocation->id,
                    'action' => 'monthly_quota_reset',
                    'credit' => 0.0,
                    'debit' => $oldCarry,
                    'balance_before' => $oldCarry,
                    'balance_after' => 0.0,
                    'remarks' => "Year-end monthly quota reset for year {$year}",
                    'created_by_user_id' => $userId,
                ]);
            });

            $processed++;
        }

        return [
            'year' => $year,
            'reset_count' => $processed,
        ];
    }
}
