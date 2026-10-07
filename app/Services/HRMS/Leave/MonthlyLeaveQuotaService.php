<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeavePolicyM;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class MonthlyLeaveQuotaService
{
    private const TIMEZONE = 'Asia/Kolkata';

    private const CHUNK_SIZE = 500;

    public function __construct(
        private LeavePolicyService $policyService,
        private LeaveAllocationService $allocationService
    ) {
    }

    public function getMonthlyQuota(
        EmployeeM $employee,
        ?Carbon $date = null,
        ?int $userId = null
    ): array {
        $context = $this->resolveMonthlyContext(
            $employee,
            $date,
            $userId
        );

        $allocation = $context['allocation'];

     
        if ($context['is_future']) {
            return $this->buildFutureQuotaResponse($context);
        }

       
        if ((bool) $allocation->is_locked) {
            return $this->buildQuotaResponse($allocation);
        }

        /*
         * Monthly quota applies only to permanent employees.
         */
        if ($context['stage'] !== 'permanent') {
            return $this->handleNonPermanentQuota(
                $allocation,
                $context['policy']
            );
        }

        
        $this->processMonthlyTransition($context);

        
        $this->recalculateMonthlyBalance(
            $allocation,
            $context['policy']
        );

        return $this->buildQuotaResponse($allocation);
    }

    
    public function recordMonthlyLeaveUsage(
        LeaveAllocationM $allocation,
        float $days
    ): void {
        $days = $this->normalizeDays($days);

        if ($days <= 0) {
            return;
        }

        DB::transaction(function () use ($allocation, $days): void {
            $lockedAllocation = $this->lockAllocation($allocation);

            $currentUsage = $this->decimal(
                $lockedAllocation->monthly_used_this_month
            );

            $lockedAllocation->monthly_used_this_month = $this->decimal(
                $currentUsage + $days
            );

            $this->allocationService->recalculateAllocationFields(
                $lockedAllocation
            );

            if ($lockedAllocation->isDirty()) {
                $lockedAllocation->save();
            }

            $this->syncAllocationModel(
                $allocation,
                $lockedAllocation
            );
        });
    }

    
    public function refundMonthlyLeaveUsage(
        LeaveAllocationM $allocation,
        float $days
    ): void {
        $days = $this->normalizeDays($days);

        if ($days <= 0) {
            return;
        }

        DB::transaction(function () use ($allocation, $days): void {
            $lockedAllocation = $this->lockAllocation($allocation);

            $currentUsage = $this->decimal(
                $lockedAllocation->monthly_used_this_month
            );

            $lockedAllocation->monthly_used_this_month = $this->decimal(
                max(0.0, $currentUsage - $days)
            );

            $this->allocationService->recalculateAllocationFields(
                $lockedAllocation
            );

            if ($lockedAllocation->isDirty()) {
                $lockedAllocation->save();
            }

            $this->syncAllocationModel(
                $allocation,
                $lockedAllocation
            );
        });
    }

    
    
    public function resetYearEndQuota(
        int $year,
        ?int $userId = null
    ): array {
        $processed = 0;

        LeaveAllocationM::query()
            ->where('year', $year)
            ->chunkById(
                self::CHUNK_SIZE,
                function ($allocations) use (
                    &$processed,
                    $year,
                    $userId
                ): void {
                    foreach ($allocations as $allocation) {
                        $wasReset = $this->resetSingleYearEndAllocation(
                            $allocation->id,
                            $year,
                            $userId
                        );

                        if ($wasReset) {
                            $processed++;
                        }
                    }
                }
            );

        return [
            'year' => $year,
            'reset_count' => $processed,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Context Resolution
    |--------------------------------------------------------------------------
    */

    
    private function resolveMonthlyContext(
        EmployeeM $employee,
        ?Carbon $date,
        ?int $userId
    ): array {
        $now = Carbon::now(self::TIMEZONE);

        $targetDate = $date
            ? $date->copy()->setTimezone(self::TIMEZONE)
            : $now->copy();

        $targetMonth = $targetDate
            ->copy()
            ->startOfMonth();

        $currentMonth = $now
            ->copy()
            ->startOfMonth();

        $isFuture = $date !== null && $targetMonth->gt($currentMonth);

        $year = (int) $targetDate->year;
        $month = (int) $targetDate->month;

        $targetMonthStr = $targetDate->format('Y-m');

        $policy = $this->policyService->forEmployee(
            $employee,
            $targetDate
        );

        if (! $policy) {
            throw new DomainException(
                "No active leave policy found for employee #{$employee->id} "
                . "on {$targetDate->toDateString()}."
            );
        }

        $stage = $this->allocationService->stageFor($employee);

        $allocation = $this->allocationService->getOrGenerate(
            $employee,
            $year,
            $userId
        );

        $monthlyLimit = $this->resolveMonthlyLimit(
            $policy,
            $allocation
        );

        return [
            'employee' => $employee,
            'policy' => $policy,
            'allocation' => $allocation,
            'stage' => $stage,
            'monthly_limit' => $monthlyLimit,
            'year' => $year,
            'month' => $month,
            'target_month_str' => $targetMonthStr,
            'target_date' => $targetDate,
            'target_month' => $targetMonth,
            'is_future' => $isFuture,
            'user_id' => $userId,
        ];
    }

   
    private function resolveMonthlyLimit(
        LeavePolicyM $policy,
        LeaveAllocationM $allocation
    ): float {
        if (
            ! isset($policy->monthly_leave_limit)
            || ! is_numeric($policy->monthly_leave_limit)
        ) {
            throw new DomainException(
                "Leave policy #{$policy->id} has an invalid or missing "
                . "monthly_leave_limit."
            );
        }

        $limit = (float) $policy->monthly_leave_limit;

        if ($limit < 0) {
            throw new DomainException(
                "Leave policy #{$policy->id} has a negative "
                . "monthly_leave_limit."
            );
        }

        $paidAllocated = max(
            0.0,
            $this->decimal($allocation->paid_allocated)
        );

        return $this->decimal(
            min($limit, $paidAllocated)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Stage Handling
    |--------------------------------------------------------------------------
    */

    
    private function handleNonPermanentQuota(
        LeaveAllocationM $allocation,
        ?LeavePolicyM $policy = null
    ): array {
        $allocation->monthly_quota = 0.0;
        $allocation->monthly_carry_forward = 0.0;
        $allocation->total_monthly_remaining_paid = 0.0;

        $this->recalculateMonthlyBalance(
            $allocation,
            $policy
        );

        return [
            'monthly_limit' => 0.0,
            'monthly_quota' => 0.0,
            'current_month_used' => 0.0,
            'carry_forward_available' => 0.0,
            'available_this_month' => $this->decimal(
                $allocation->paid_remaining
            ),

            'total_monthly_remaining_paid' => 0.0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Monthly Transition
    |--------------------------------------------------------------------------
    */

    
    private function processMonthlyTransition(array $context): void
    {
        $allocation = $context['allocation'];

        $targetMonthStr = $context['target_month_str'];
        $targetMonth = $context['target_month'];
        $targetYear = $context['year'];

        $lastProcessed = $allocation->last_month_processed;

    
        if ($lastProcessed === null || trim((string) $lastProcessed) === '') {
            $this->processInitialMonth($context);
            return;
        }

        
        if ($lastProcessed === $targetMonthStr) {
            return;
        }

        $lastProcessedDate = $this->parseProcessedMonth(
            (string) $lastProcessed
        );

       
        if ($targetMonth->lt($lastProcessedDate)) {
            return;
        }

       
        if ($lastProcessedDate->year === $targetYear) {
            $this->processSameYearRollover(
                $context,
                $lastProcessedDate
            );

            return;
        }

        
        $this->processYearBoundary($context);
    }

    
    private function processInitialMonth(array $context): void
    {
        $allocation = $context['allocation'];

        $allocation->last_month_processed =
            $context['target_month_str'];

        $allocation->monthly_used_this_month =
            $this->decimal(
                $allocation->monthly_used_this_month
            );

        $allocation->monthly_carry_forward =
            $this->decimal(
                $allocation->monthly_carry_forward
            );

        $allocation->monthly_quota =
            $context['monthly_limit'];
    }

    
    private function processSameYearRollover(
        array $context,
        Carbon $lastProcessedDate
    ): void {
        DB::transaction(function () use (
            $context,
            $lastProcessedDate
        ): void {
            $allocation = $this->lockAllocation(
                $context['allocation']
            );

           
            if (
                $allocation->last_month_processed
                === $context['target_month_str']
            ) {
                $context['allocation']->fill(
                    $allocation->getAttributes()
                );

                $context['allocation']->syncOriginal();

                return;
            }

            $policy = $context['policy'];
            $monthlyLimit = $context['monthly_limit'];

            $oldCarry = $this->decimal(
                $allocation->monthly_carry_forward
            );

            $oldUsed = $this->decimal(
                $allocation->monthly_used_this_month
            );

            $oldQuota = $this->decimal(
                $allocation->monthly_quota
            );

            
            $unusedQuota = max(
                0.0,
                $this->decimal(
                    ($oldQuota + $oldCarry) - $oldUsed
                )
            );

            $allowCarry = $this->monthlyCarryForwardEnabled(
                $policy
            );

            
            $targetMonth = $context['target_month'];

            $monthsElapsed = $lastProcessedDate->diffInMonths(
                $targetMonth
            );

            $intermediateMonths = max(
                0,
                $monthsElapsed - 1
            );

            $intermediateEntitlement =
                $intermediateMonths * $monthlyLimit;

            $accumulatedUnused = $this->decimal(
                $unusedQuota + $intermediateEntitlement
            );

           
            $paidRemaining = max(
                0.0,
                $this->decimal($allocation->paid_remaining)
            );

            $newCarryForward = $allowCarry
                ? $this->decimal(
                    min(
                        $accumulatedUnused,
                        $paidRemaining
                    )
                )
                : 0.0;

            $allocation->monthly_carry_forward =
                $newCarryForward;

            $allocation->monthly_used_this_month = 0.0;

            $allocation->monthly_quota =
                $monthlyLimit;

            $allocation->last_month_processed =
                $context['target_month_str'];

            $this->allocationService->recalculateAllocationFields(
                $allocation,
                $policy
            );

            if ($allocation->isDirty()) {
                $allocation->save();
            }

           
            LeaveBalanceLogM::create([
                'employee_id' => $allocation->employee_id,
                'leave_allocation_id' => $allocation->id,
                'action' => 'monthly_earned_leave_accrual',
                'credit' => $monthlyLimit,
                'debit' => 0.0,
                'balance_before' => $oldCarry,
                'balance_after' =>
                    $this->decimal(
                        $allocation->total_monthly_remaining_paid
                    ),
                'remarks' =>
                    "Monthly leave accrual credited for "
                    . $context['target_date']->format('F Y'),
                'created_by_user_id' => $context['user_id'],
            ]);

            if ($newCarryForward > 0) {
                LeaveBalanceLogM::create([
                    'employee_id' => $allocation->employee_id,
                    'leave_allocation_id' => $allocation->id,
                    'action' => 'monthly_quota_carry_forward',
                    'credit' => $newCarryForward,
                    'debit' => 0.0,
                    'balance_before' => $oldCarry,
                    'balance_after' =>
                        $this->decimal(
                            $allocation->total_monthly_remaining_paid
                        ),
                    'remarks' =>
                        "Unused {$lastProcessedDate->format('F')} "
                        . "monthly quota carried forward to "
                        . $context['target_date']->format('F'),
                    'created_by_user_id' => $context['user_id'],
                ]);
            }

            
            $this->syncAllocationModel(
                $context['allocation'],
                $allocation
            );
        });
    }

    
    private function processYearBoundary(array $context): void
    {
        DB::transaction(function () use ($context): void {
            $allocation = $this->lockAllocation(
                $context['allocation']
            );

            
            if (
                $allocation->last_month_processed
                === $context['target_month_str']
            ) {
                $context['allocation']->fill(
                    $allocation->getAttributes()
                );

                $context['allocation']->syncOriginal();

                return;
            }

            $oldCarry = $this->decimal(
                $allocation->monthly_carry_forward
            );

            $allocation->monthly_carry_forward = 0.0;
            $allocation->monthly_used_this_month = 0.0;

            $allocation->monthly_quota =
                $context['monthly_limit'];

            $allocation->last_month_processed =
                $context['target_month_str'];

            $this->allocationService->recalculateAllocationFields(
                $allocation,
                $context['policy']
            );

            if ($allocation->isDirty()) {
                $allocation->save();
            }

           
            if ($oldCarry > 0) {
                LeaveBalanceLogM::create([
                    'employee_id' => $allocation->employee_id,
                    'leave_allocation_id' => $allocation->id,
                    'action' => 'monthly_quota_reset',
                    'credit' => 0.0,
                    'debit' => $oldCarry,
                    'balance_before' => $oldCarry,
                    'balance_after' =>
                        $this->decimal(
                            $allocation->total_monthly_remaining_paid
                        ),
                    'remarks' =>
                        "Year-end monthly quota reset for year "
                        . $context['year'],
                    'created_by_user_id' => $context['user_id'],
                ]);
            }

            $this->syncAllocationModel(
                $context['allocation'],
                $allocation
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Year-End Reset
    |--------------------------------------------------------------------------
    */

    
    private function resetSingleYearEndAllocation(
        int $allocationId,
        int $year,
        ?int $userId
    ): bool {
        return DB::transaction(function () use (
            $allocationId,
            $year,
            $userId
        ): bool {
            $allocation = LeaveAllocationM::query()
                ->whereKey($allocationId)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $allocation) {
                return false;
            }

            $oldCarry = $this->decimal(
                $allocation->monthly_carry_forward
            );

            $oldUsed = $this->decimal(
                $allocation->monthly_used_this_month
            );

            if ($oldCarry <= 0 && $oldUsed <= 0) {
                return false;
            }

            $allocation->monthly_carry_forward = 0.0;
            $allocation->monthly_used_this_month = 0.0;

            $this->allocationService->recalculateAllocationFields(
                $allocation
            );

            if ($allocation->isDirty()) {
                $allocation->save();
            }

            if ($oldCarry > 0) {
                LeaveBalanceLogM::create([
                    'employee_id' => $allocation->employee_id,
                    'leave_allocation_id' => $allocation->id,
                    'action' => 'monthly_quota_reset',
                    'credit' => 0.0,
                    'debit' => $oldCarry,
                    'balance_before' => $oldCarry,
                    'balance_after' => 0.0,
                    'remarks' =>
                        "Year-end monthly quota reset for year {$year}",
                    'created_by_user_id' => $userId,
                ]);
            }

            return true;
        });
    }

   

    
    private function recalculateMonthlyBalance(
        LeaveAllocationM $allocation,
        ?LeavePolicyM $policy = null
    ): void {
        $this->allocationService->recalculateAllocationFields(
            $allocation,
            $policy
        );

        if ($allocation->isDirty()) {
            $allocation->save();
        }
    }

  

   
    private function buildQuotaResponse(
        LeaveAllocationM $allocation
    ): array {
        $monthlyQuota = $this->decimal(
            $allocation->monthly_quota
        );

        $currentMonthUsed = $this->decimal(
            $allocation->monthly_used_this_month
        );

        $carryForward = $this->decimal(
            $allocation->monthly_carry_forward
        );

        $remaining = $this->decimal(
            $allocation->total_monthly_remaining_paid
        );

        return [
            'monthly_limit' => $monthlyQuota,
            'monthly_quota' => $monthlyQuota,
            'current_month_used' => $currentMonthUsed,
            'carry_forward_available' => $carryForward,
            'available_this_month' => $remaining,
            'total_monthly_remaining_paid' => $remaining,
        ];
    }

   
    private function buildFutureQuotaResponse(
        array $context
    ): array {
        if ($context['stage'] !== 'permanent') {
            return [
                'monthly_limit' => 0.0,
                'monthly_quota' => 0.0,
                'current_month_used' => 0.0,
                'carry_forward_available' => 0.0,
                'available_this_month' => 0.0,
                'total_monthly_remaining_paid' => 0.0,
            ];
        }

        $monthlyLimit = $this->decimal(
            $context['monthly_limit']
        );

        $paidRemaining = max(
            0.0,
            $this->decimal(
                $context['allocation']->paid_remaining
            )
        );

        $futureAvailable = $this->decimal(
            min($monthlyLimit, $paidRemaining)
        );

        return [
            'monthly_limit' => $monthlyLimit,
            'monthly_quota' => $monthlyLimit,
            'current_month_used' => 0.0,
            'carry_forward_available' => 0.0,
            'available_this_month' => $futureAvailable,
            'total_monthly_remaining_paid' => $futureAvailable,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Locking / Concurrency Helpers
    |--------------------------------------------------------------------------
    */

    
    private function lockAllocation(
        LeaveAllocationM $allocation
    ): LeaveAllocationM {
        if (! $allocation->exists || ! $allocation->getKey()) {
            throw new DomainException(
                'Cannot lock an unsaved leave allocation.'
            );
        }

        return LeaveAllocationM::query()
            ->whereKey($allocation->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    
    private function syncAllocationModel(
        LeaveAllocationM $target,
        LeaveAllocationM $source
    ): void {
        $target->setRawAttributes(
            $source->getAttributes(),
            true
        );

        $target->syncOriginal();
    }

    /*
    |--------------------------------------------------------------------------
    | Validation / Utility
    |--------------------------------------------------------------------------
    */

    
    private function parseProcessedMonth(
        string $value
    ): Carbon {
        try {
            $date = Carbon::createFromFormat(
                '!Y-m',
                $value,
                self::TIMEZONE
            );

            if (! $date || $date->format('Y-m') !== $value) {
                throw new DomainException();
            }

            return $date->startOfMonth();
        } catch (\Throwable) {
            throw new DomainException(
                "Invalid last_month_processed value: '{$value}'. "
                . "Expected format YYYY-MM."
            );
        }
    }

   
    private function monthlyCarryForwardEnabled(
        LeavePolicyM $policy
    ): bool {
        if ($policy->allow_monthly_carry_forward !== null) {
            return (bool) $policy->allow_monthly_carry_forward;
        }

        if ($policy->carry_forward_enabled !== null) {
            return (bool) $policy->carry_forward_enabled;
        }

        
        return true;
    }

    
    private function normalizeDays(float $days): float
    {
        if (! is_finite($days)) {
            throw new DomainException(
                'Leave days must be a finite numeric value.'
            );
        }

        return $this->decimal($days);
    }

    
    private function decimal(mixed $value): float
    {
        return round((float) ($value ?? 0.0), 2);
    }
}
