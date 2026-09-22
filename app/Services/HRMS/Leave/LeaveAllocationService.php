<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Services\HRMS\Employee\EmployeeEligibilityS;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class LeaveAllocationService
{
    public const TIMEZONE = 'Asia/Kolkata';

    public const STAGE_PERMANENT = 'permanent';
    public const STAGE_PROBATION = 'probation';
    public const STAGE_INTERNSHIP = 'internship';

    public function __construct(
        private LeavePolicyService $policyService,
        private EmployeeEligibilityS $eligibilityService
    ) {
    }

    /**
     * Central Authoritative Entitlement Calculation Engine.
     * Single Source of Truth for all leave quota calculations across the HRMS.
     */
    public function calculateEntitlement(
        LeavePolicyM $policy,
        string $stage,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null,
        bool $isPaidIntern = true
    ): LeaveEntitlementResult {
        $this->validatePolicy($policy);

        $normalizedStage = $this->normalizeStage($stage);

        return match ($normalizedStage) {
            self::STAGE_PROBATION => $this->calculateProbationEntitlement($policy, $fromDate, $toDate),
            self::STAGE_INTERNSHIP => $this->calculateInternshipEntitlement($policy, $isPaidIntern, $fromDate, $toDate),
            self::STAGE_PERMANENT => $this->calculatePermanentEntitlement($policy, $fromDate, $toDate),
            default => throw new DomainException("Unsupported leave allocation stage: '{$normalizedStage}'."),
        };
    }

    /**
     * Backward-compatible helper returning [$total, $paid, $sick] array.
     */
    public function calculateAllocationAmounts(
        ?LeavePolicyM $policy,
        string $stage,
        ?Carbon $fromDate,
        ?Carbon $toDate = null,
        ?bool $isPaidIntern = true
    ): array {
        if (! $policy) {
            return [0.0, 0.0, 0.0];
        }

        $result = $this->calculateEntitlement(
            $policy,
            $stage,
            $fromDate,
            $toDate,
            (bool) $isPaidIntern
        );

        return [
            $result->totalAllocated,
            $result->paidAllocated,
            $result->sickAllocated,
        ];
    }

    /**
     * Generate or regenerate leave allocation for an individual employee.
     */
    public function generateForEmployee(
        EmployeeM $employee,
        int $year,
        ?int $userId = null,
        ?string $forceStage = null,
        ?Carbon $effectiveDate = null
    ): LeaveAllocationM {
        if (! $this->eligibilityService->canUseLeave($employee)) {
            return LeaveAllocationM::firstOrNew([
                'employee_id' => $employee->id,
                'year' => $year,
            ]);
        }

        return DB::transaction(function () use (
            $employee,
            $year,
            $userId,
            $forceStage,
            $effectiveDate
        ) {
            $allocation = LeaveAllocationM::query()
                ->where('employee_id', $employee->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            // Lock protection: if allocation is locked, preserve it untouched
            if ($allocation?->is_locked) {
                return $allocation;
            }

            $policyDate = $this->yearStart($year);
            $policy = $this->policyService->forEmployee($employee, $policyDate);

            if (! $policy) {
                throw new DomainException("No active leave policy found for employee #{$employee->id} in year {$year}.");
            }

            $this->validatePolicy($policy);

            $stage = $forceStage !== null
                ? $this->normalizeStage($forceStage)
                : $this->stageFor($employee);

            $fromDate = $this->allocationStartDate(
                $employee,
                $stage,
                $year,
                $effectiveDate
            );

            $toDate = $this->yearEnd($year);

            $isPaidIntern = $stage !== self::STAGE_INTERNSHIP
                || ! $this->isUnpaidInternEmployee($employee);

            $entitlement = $this->calculateEntitlement(
                $policy,
                $stage,
                $fromDate,
                $toDate,
                $isPaidIntern
            );

            $isExisting = $allocation?->exists ?? false;

            if (! $allocation) {
                $allocation = new LeaveAllocationM([
                    'employee_id' => $employee->id,
                    'year' => $year,
                ]);
            }

            $before = (float) ($allocation->total_remaining ?? 0.0);

            $currentYear = $this->now()->year;
            $isPastYear = $year < $currentYear;

            // Preserve historical usage and balances strictly
            $paidUsed = (float) ($allocation->paid_used ?? 0.0);
            $sickUsed = (float) ($allocation->sick_used ?? 0.0);
            $compOffUsed = (float) ($allocation->comp_off_used ?? 0.0);
            $lwpUsed = (float) ($allocation->lwp_used ?? 0.0);
            $compOffAllocated = (float) ($allocation->comp_off_allocated ?? 0.0);
            $monthlyUsed = (float) ($allocation->monthly_used_this_month ?? 0.0);
            $monthlyCarry = (float) ($allocation->monthly_carry_forward ?? 0.0);

            $allocationReason = $allocation->allocation_reason ?: "Annual Allocation for {$year}";
            $createdByUserId = $allocation->created_by_user_id ?? $userId;
            $isLocked = $isExisting ? (bool) $allocation->is_locked : $isPastYear;

            $allocation->fill([
                'employment_stage' => $stage,
                'policy_id' => $policy->id,
                'confirmation_date' => $employee->confirmation_date,
                'allocation_from_date' => $fromDate?->toDateString(),
                'allocation_to_date' => $toDate->toDateString(),
                'total_allocated' => $entitlement->totalAllocated,
                'paid_allocated' => $entitlement->paidAllocated,
                'sick_allocated' => $entitlement->sickAllocated,
                'monthly_quota' => $entitlement->monthlyQuota,
                'paid_used' => $paidUsed,
                'sick_used' => $sickUsed,
                'comp_off_used' => $compOffUsed,
                'lwp_used' => $lwpUsed,
                'comp_off_allocated' => $compOffAllocated,
                'monthly_used_this_month' => $monthlyUsed,
                'monthly_carry_forward' => $monthlyCarry,
                'last_month_processed' => $allocation->last_month_processed,
                'allocation_reason' => $allocationReason,
                'is_locked' => $isLocked,
                'created_by_user_id' => $createdByUserId,
            ]);

            $this->recalculateAllocationFields(
                $allocation,
                $policy,
                $employee
            );

            if ($allocation->isDirty()) {
                $allocation->save();
            }

            $after = (float) ($allocation->total_remaining ?? 0.0);
            $balanceDifference = round($after - $before, 2);

            if ($allocation->wasRecentlyCreated || abs($balanceDifference) >= 0.01) {
                LeaveBalanceLogM::create([
                    'employee_id' => $employee->id,
                    'leave_allocation_id' => $allocation->id,
                    'action' => 'allocation_generated',
                    'credit' => max(0.0, $balanceDifference),
                    'debit' => max(0.0, -$balanceDifference),
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'remarks' => 'Leave allocation generated from DB policy.',
                    'created_by_user_id' => $userId,
                ]);
            }

            return $allocation;
        });
    }

    /**
     * Single employee allocation generation with eligibility enforcement.
     */
    public function generateSingle(int $employeeId, int $year, ?int $userId = null): LeaveAllocationM
    {
        $employee = EmployeeM::findOrFail($employeeId);

        if (! $this->eligibilityService->canUseLeave($employee)) {
            throw new DomainException(
                'Cannot generate leave allocation: Employee profile is pending verification or employee is not active/eligible for leave management.'
            );
        }

        $allocation = $this->generateForEmployee($employee, $year, $userId);

        if (! $allocation->exists) {
            throw new DomainException("Leave allocation could not be generated for year {$year}.");
        }

        return $allocation;
    }

    /**
     * Bulk yearly allocation generation for all eligible employees using cursor streaming.
     */
    public function generateYearly(int $year, ?int $userId = null): int
    {
        $count = 0;
        foreach (EmployeeM::cursor() as $employee) {
            if ($this->eligibilityService->canUseLeave($employee)) {
                $this->generateForEmployee($employee, $year, $userId);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Manual allocation update by admin with server-side validation and balance derivation.
     */
    public function updateAllocation(int $id, array $data, ?int $userId = null): LeaveAllocationM
    {
        return DB::transaction(function () use ($id, $data, $userId) {
            $allocation = LeaveAllocationM::lockForUpdate()->findOrFail($id);

            $before = (float) ($allocation->total_remaining ?? 0.0);

            $allocation->year = (int) $data['year'];
            $allocation->policy_id = ! empty($data['policy_id']) ? (int) $data['policy_id'] : null;
            $allocation->employment_stage = strtolower(trim($data['employment_stage']));
            
            // Explicitly set quotas
            $allocation->paid_allocated = round(max(0.0, (float) $data['paid_allocated']), 2);
            $allocation->sick_allocated = round(max(0.0, (float) $data['sick_allocated']), 2);
            $allocation->comp_off_allocated = round(max(0.0, (float) ($data['comp_off_allocated'] ?? $allocation->comp_off_allocated ?? 0.0)), 2);
            $allocation->total_allocated = round($allocation->paid_allocated + $allocation->sick_allocated, 2);

            // Preserve historical usage or apply sanitized updates
            $allocation->paid_used = round(max(0.0, (float) ($data['paid_used'] ?? $allocation->paid_used ?? 0.0)), 2);
            $allocation->sick_used = round(max(0.0, (float) ($data['sick_used'] ?? $allocation->sick_used ?? 0.0)), 2);
            $allocation->comp_off_used = round(max(0.0, (float) ($data['comp_off_used'] ?? $allocation->comp_off_used ?? 0.0)), 2);
            $allocation->lwp_used = round(max(0.0, (float) ($data['lwp_used'] ?? $allocation->lwp_used ?? 0.0)), 2);
            
            if (array_key_exists('monthly_quota', $data)) {
                $allocation->monthly_quota = round(max(0.0, (float) $data['monthly_quota']), 2);
            }
            if (array_key_exists('monthly_carry_forward', $data)) {
                $allocation->monthly_carry_forward = round(max(0.0, (float) $data['monthly_carry_forward']), 2);
            }
            if (array_key_exists('monthly_used_this_month', $data)) {
                $allocation->monthly_used_this_month = round(max(0.0, (float) $data['monthly_used_this_month']), 2);
            }
            if (array_key_exists('allocation_from_date', $data)) {
                $allocation->allocation_from_date = $data['allocation_from_date'];
            }
            if (array_key_exists('allocation_to_date', $data)) {
                $allocation->allocation_to_date = $data['allocation_to_date'];
            }
            if (array_key_exists('allocation_reason', $data)) {
                $allocation->allocation_reason = $data['allocation_reason'];
            }

            if (array_key_exists('is_locked', $data)) {
                $allocation->is_locked = (bool) $data['is_locked'];
            }

            // Recalculate remaining balances entirely server-side (ignoring any client-forged remaining values)
            $this->recalculateAllocationFields($allocation);

            if ($allocation->isDirty()) {
                $allocation->save();
            }

            $after = (float) ($allocation->total_remaining ?? 0.0);
            $diff = round($after - $before, 2);

            if (abs($diff) >= 0.01) {
                LeaveBalanceLogM::create([
                    'employee_id' => $allocation->employee_id,
                    'leave_allocation_id' => $allocation->id,
                    'action' => 'allocation_manual_edit',
                    'credit' => max(0.0, $diff),
                    'debit' => max(0.0, -$diff),
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'remarks' => 'Leave allocation updated manually by admin.',
                    'created_by_user_id' => $userId,
                ]);
            }

            return $allocation;
        });
    }

    /**
     * Preview quota calculation for UI/AJAX.
     * Uses the EXACT same entitlement calculation pipeline as actual generation.
     */
    public function previewQuota(
        ?int $policyId,
        string $stage,
        ?string $fromDateStr = null,
        ?string $toDateStr = null,
        ?int $employeeId = null
    ): array {
        $policy = $policyId ? LeavePolicyM::find($policyId) : $this->policyService->activeDefault();
        
        if (! $policy) {
            return [
                'total_allocated' => 0.0,
                'paid_allocated' => 0.0,
                'sick_allocated' => 0.0,
                'monthly_quota' => 0.0,
            ];
        }

        $fromDate = $fromDateStr ? Carbon::parse($fromDateStr, self::TIMEZONE) : null;
        $toDate = $toDateStr ? Carbon::parse($toDateStr, self::TIMEZONE) : null;

        $isPaidIntern = true;
        $normalizedStage = $this->normalizeStage($stage);

        if ($normalizedStage === self::STAGE_INTERNSHIP && $employeeId) {
            $employee = EmployeeM::find($employeeId);
            if ($employee) {
                $isPaidIntern = ! $this->isUnpaidInternEmployee($employee);
            }
        }

        $entitlement = $this->calculateEntitlement(
            $policy,
            $normalizedStage,
            $fromDate,
            $toDate,
            $isPaidIntern
        );

        return [
            'total_allocated' => $entitlement->totalAllocated,
            'paid_allocated' => $entitlement->paidAllocated,
            'sick_allocated' => $entitlement->sickAllocated,
            'monthly_quota' => $entitlement->monthlyQuota,
        ];
    }

    /**
     * Retrieve employee leave balance summary.
     */
    public function getEmployeeBalance(int $userId, int $year): ?array
    {
        $employee = EmployeeM::where('user_id', $userId)->first();
        if (! $employee) {
            return null;
        }

        $allocation = $this->getOrGenerate($employee, $year, $userId);

        return [
            'total_allocated' => (float) $allocation->total_allocated,
            'total_remaining' => (float) $allocation->total_remaining,
            'paid_allocated' => (float) $allocation->paid_allocated,
            'sick_allocated' => (float) $allocation->sick_allocated,
            'comp_off_remaining' => (float) $allocation->comp_off_remaining,
            'lwp_used' => (float) $allocation->lwp_used,
        ];
    }

    /**
     * Get existing allocation or generate fresh allocation if missing.
     */
    public function getOrGenerate(
        EmployeeM $employee,
        int $year,
        ?int $userId = null
    ): LeaveAllocationM {
        $allocation = LeaveAllocationM::query()
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->first();

        if ($allocation) {
            return $allocation;
        }

        return $this->generateForEmployee(
            $employee,
            $year,
            $userId
        );
    }

    /**
     * Recalculate allocation fields for a specific employee and year.
     */
    public function recalculateForEmployee(
        EmployeeM $employee,
        int $year
    ): ?LeaveAllocationM {
        return DB::transaction(function () use ($employee, $year) {
            $allocation = LeaveAllocationM::query()
                ->where('employee_id', $employee->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $allocation || $allocation->is_locked) {
                return $allocation;
            }

            $policy = $this->policyService->forEmployee(
                $employee,
                $this->yearStart($year)
            );

            if (! $policy) {
                return $allocation;
            }

            $this->validatePolicy($policy);

            $this->recalculateAllocationFields(
                $allocation,
                $policy,
                $employee
            );

            if ($allocation->isDirty()) {
                $allocation->save();
            }

            return $allocation;
        });
    }

    /**
     * Recalculate derived fields (remaining balances, totals, monthly quota)
     * on an existing allocation without modifying base allocated quotas or usage.
     */
    public function recalculateAllocationFields(
        LeaveAllocationM $allocation,
        ?LeavePolicyM $policy = null,
        ?EmployeeM $employee = null
    ): LeaveAllocationM {
        $policy ??= $this->resolvePolicyForAllocation($allocation);
        $this->validatePolicy($policy);

        $employee ??= $this->resolveEmployeeForAllocation($allocation);
        $rawStage = trim((string) ($allocation->employment_stage ?? $employee?->employee_stage ?? $employee?->employment_type ?? self::STAGE_PERMANENT));
        if ($rawStage === '') {
            $rawStage = self::STAGE_PERMANENT;
        }
        $stage = $this->normalizeStage($rawStage);

        if ($stage === self::STAGE_INTERNSHIP) {
            $this->recalculateInternshipAllocation(
                $allocation,
                $policy,
                $employee
            );
        } elseif ($stage === self::STAGE_PROBATION) {
            $this->recalculateStandardAllocation(
                $allocation,
                true
            );
        } else {
            $this->recalculatePermanentAllocation(
                $allocation,
                $policy
            );
        }

        return $allocation;
    }

    /*
    |--------------------------------------------------------------------------
    | Stage-Specific Entitlement Calculations
    |--------------------------------------------------------------------------
    */

    private function calculateProbationEntitlement(
        LeavePolicyM $policy,
        ?Carbon $fromDate,
        ?Carbon $toDate
    ): LeaveEntitlementResult {
        $limit = round(max(0.0, (float) $policy->probation_leave_limit), 2);

        return new LeaveEntitlementResult(
            stage: self::STAGE_PROBATION,
            totalAllocated: $limit,
            paidAllocated: $limit,
            sickAllocated: 0.0,
            monthlyQuota: 0.0,
            allocationFromDate: $fromDate?->toDateString(),
            allocationToDate: $toDate?->toDateString(),
            policyId: $policy->id,
            isPaidIntern: true
        );
    }

    private function calculateInternshipEntitlement(
        LeavePolicyM $policy,
        bool $isPaidIntern,
        ?Carbon $fromDate,
        ?Carbon $toDate
    ): LeaveEntitlementResult {
        $limit = round(max(0.0, (float) $policy->internship_leave_limit), 2);

        if (! $isPaidIntern) {
            return new LeaveEntitlementResult(
                stage: self::STAGE_INTERNSHIP,
                totalAllocated: $limit,
                paidAllocated: 0.0,
                sickAllocated: 0.0,
                monthlyQuota: 0.0,
                allocationFromDate: $fromDate?->toDateString(),
                allocationToDate: $toDate?->toDateString(),
                policyId: $policy->id,
                isPaidIntern: false
            );
        }

        return new LeaveEntitlementResult(
            stage: self::STAGE_INTERNSHIP,
            totalAllocated: $limit,
            paidAllocated: $limit,
            sickAllocated: 0.0,
            monthlyQuota: 0.0,
            allocationFromDate: $fromDate?->toDateString(),
            allocationToDate: $toDate?->toDateString(),
            policyId: $policy->id,
            isPaidIntern: true
        );
    }

    private function calculatePermanentEntitlement(
        LeavePolicyM $policy,
        ?Carbon $fromDate,
        ?Carbon $toDate
    ): LeaveEntitlementResult {
        if (! $fromDate || ($toDate && $fromDate->gt($toDate))) {
            return new LeaveEntitlementResult(
                stage: self::STAGE_PERMANENT,
                totalAllocated: 0.0,
                paidAllocated: 0.0,
                sickAllocated: 0.0,
                monthlyQuota: 0.0,
                allocationFromDate: $fromDate?->toDateString(),
                allocationToDate: $toDate?->toDateString(),
                policyId: $policy->id,
                isPaidIntern: true
            );
        }

        [$total, $paid, $sick] = $this->calculatePermanentProration($policy, $fromDate, $toDate);
        $monthlyLimit = round(max(0.0, (float) $policy->monthly_leave_limit), 2);
        $monthlyQuota = round(min($monthlyLimit, $paid), 2);

        return new LeaveEntitlementResult(
            stage: self::STAGE_PERMANENT,
            totalAllocated: $total,
            paidAllocated: $paid,
            sickAllocated: $sick,
            monthlyQuota: $monthlyQuota,
            allocationFromDate: $fromDate?->toDateString(),
            allocationToDate: $toDate?->toDateString(),
            policyId: $policy->id,
            isPaidIntern: true
        );
    }

    /**
     * Authoritative Proration Calculation for Permanent Employees.
     */
    public function calculatePermanentProration(
        LeavePolicyM $policy,
        Carbon $fromDate,
        ?Carbon $toDate = null
    ): array {
        $startYear = (int) $fromDate->year;
        $startMonth = (int) $fromDate->month;

        if ($toDate) {
            $endYear = (int) $toDate->year;
            $endMonth = (int) $toDate->month;
        } else {
            $endYear = $startYear;
            $endMonth = 12;
        }

        if ($endYear < $startYear || ($endYear === $startYear && $startMonth > $endMonth)) {
            return [0.0, 0.0, 0.0];
        }

        if ($endYear === $startYear) {
            $remainingMonths = max(0, min(12, $endMonth - $startMonth + 1));
        } else {
            $remainingMonths = max(0, min(12, 13 - $startMonth));
        }

        $total = (float) round(
            ((float) $policy->annual_total_leaves / 12.0) * $remainingMonths
        );

        $annualTotal = (float) $policy->annual_total_leaves;

        $paidRatio = $annualTotal > 0
            ? ((float) $policy->annual_paid_leaves / $annualTotal)
            : 0.0;

        $paid = (float) round($total * $paidRatio);

        $sick = (float) round(
            max(0.0, $total - $paid),
            2
        );

        return [
            $total,
            $paid,
            $sick,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Recalculation Sub-Methods
    |--------------------------------------------------------------------------
    */

    private function recalculateBaseBalances(LeaveAllocationM $allocation): void
    {
        $allocation->paid_remaining = round(
            max(
                0.0,
                (float) $allocation->paid_allocated
                - (float) $allocation->paid_used
            ),
            2
        );

        $allocation->sick_remaining = round(
            max(
                0.0,
                (float) $allocation->sick_allocated
                - (float) $allocation->sick_used
            ),
            2
        );

        $allocation->comp_off_remaining = $this->calculateCompOffRemaining($allocation);

        $allocation->total_allocated = round(
            (float) $allocation->paid_allocated
            + (float) $allocation->sick_allocated,
            2
        );

        $allocation->total_used = round(
    (float) $allocation->paid_used
    + (float) $allocation->sick_used
    + (float) $allocation->comp_off_used
    + (float) $allocation->lwp_used,
    2
);

        $allocation->total_remaining = round(
            (float) $allocation->paid_remaining
            + (float) $allocation->sick_remaining
            + (float) $allocation->comp_off_remaining,
            2
        );
    }

    private function recalculateInternshipAllocation(
        LeaveAllocationM $allocation,
        LeavePolicyM $policy,
        ?EmployeeM $employee
    ): void {
        $isUnpaid = $this->isUnpaidIntern(
            $allocation,
            $employee
        );

        $internLimit = round(
            max(0.0, (float) $policy->internship_leave_limit),
            2
        );

        if ($isUnpaid) {
            $this->recalculateUnpaidInternAllocation(
                $allocation,
                $internLimit
            );

            return;
        }

        $this->recalculateStandardAllocation(
            $allocation,
            true
        );
    }

    private function recalculateUnpaidInternAllocation(
        LeaveAllocationM $allocation,
        float $internLimit
    ): void {
        $allocation->paid_allocated = 0.0;
        $allocation->sick_allocated = 0.0;
        $allocation->total_allocated = $internLimit;

        $allocation->paid_remaining = 0.0;
        $allocation->sick_remaining = 0.0;

        $allocation->comp_off_remaining = $this->calculateCompOffRemaining($allocation);

        $allocation->total_used = round(
            (float) $allocation->paid_used
            + (float) $allocation->sick_used
            + (float) $allocation->comp_off_used
            + (float) $allocation->lwp_used,
            2
        );

        $allocation->total_remaining = round(
            max(
                0.0,
                $internLimit - (float) $allocation->lwp_used
            ),
            2
        );

        $this->resetMonthlyFields($allocation);
    }

    private function recalculateStandardAllocation(
        LeaveAllocationM $allocation,
        bool $includeMonthlyReset = true
    ): void {
        $this->recalculateBaseBalances($allocation);

        if ($includeMonthlyReset) {
            $this->resetMonthlyFields($allocation);
        }
    }

    private function recalculatePermanentAllocation(
        LeaveAllocationM $allocation,
        LeavePolicyM $policy
    ): void {
        $this->recalculateBaseBalances($allocation);

        $monthlyLimit = round(
            max(0.0, (float) $policy->monthly_leave_limit),
            2
        );

        $allocation->monthly_quota = round(
            min(
                $monthlyLimit,
                (float) $allocation->paid_allocated
            ),
            2
        );

        $allocation->monthly_carry_forward = round(
            max(
                0.0,
                min(
                    (float) ($allocation->monthly_carry_forward ?? 0.0),
                    (float) $allocation->paid_remaining
                )
            ),
            2
        );

        $carry = (float) $allocation->monthly_carry_forward;
        $quota = (float) $allocation->monthly_quota;
        $usedThisMonth = max(
            0.0,
            (float) ($allocation->monthly_used_this_month ?? 0.0)
        );

        $monthlyRemainingRaw = max(
            0.0,
            ($quota + $carry) - $usedThisMonth
        );

        $allocation->total_monthly_remaining_paid = round(
            min(
                $monthlyRemainingRaw,
                (float) $allocation->paid_remaining
            ),
            2
        );
    }

    private function resetMonthlyFields(
        LeaveAllocationM $allocation
    ): void {
        $allocation->monthly_quota = 0.0;
        $allocation->monthly_carry_forward = 0.0;
        $allocation->total_monthly_remaining_paid = 0.0;
    }

    private function calculateCompOffRemaining(
        LeaveAllocationM $allocation
    ): float {
        return round(
            max(
                0.0,
                (float) ($allocation->comp_off_allocated ?? 0.0)
                - (float) ($allocation->comp_off_used ?? 0.0)
            ),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Policy & Stage Helpers
    |--------------------------------------------------------------------------
    */

    private function validatePolicy(
        LeavePolicyM $policy
    ): void {
        $total = (float) $policy->annual_total_leaves;
        $paid = (float) $policy->annual_paid_leaves;
        $sick = (float) $policy->annual_sick_leaves;

        $monthly = $policy->monthly_leave_limit;
        $probation = $policy->probation_leave_limit;
        $internship = $policy->internship_leave_limit;

        if ($total <= 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: annual_total_leaves must be greater than 0."
            );
        }

        if ($paid < 0 || $sick < 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: annual_paid_leaves and annual_sick_leaves must not be negative."
            );
        }

        if (round($paid + $sick, 2) !== round($total, 2)) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: annual_paid_leaves ({$paid}) + annual_sick_leaves ({$sick}) must equal annual_total_leaves ({$total})."
            );
        }

        if ($monthly === null || (float) $monthly < 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: monthly_leave_limit must be present and non-negative."
            );
        }

        if ($probation === null || (float) $probation < 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: probation_leave_limit must be present and non-negative."
            );
        }

        if ($internship === null || (float) $internship < 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: internship_leave_limit must be present and non-negative."
            );
        }
    }

    public function normalizeStage(string $rawStage): string
    {
        $stage = strtolower(trim($rawStage));

        if ($stage === '') {
            throw new DomainException('Employee stage cannot be empty.');
        }

        if (in_array($stage, ['permanent', 'confirmed', 'full_time', 'full-time'], true)) {
            return self::STAGE_PERMANENT;
        }

        if (in_array($stage, ['probation', 'on_probation'], true) || str_contains($stage, 'probation')) {
            return self::STAGE_PROBATION;
        }

        if (in_array($stage, ['internship', 'intern', 'paid_intern', 'unpaid_intern', 'intern_paid', 'intern_unpaid'], true) || str_contains($stage, 'intern')) {
            return self::STAGE_INTERNSHIP;
        }

        throw new DomainException(
            "Unsupported or unknown employee stage: '{$rawStage}'. Valid stages are: permanent, probation, internship."
        );
    }

    public function stageFor(EmployeeM $employee): string
    {
        $rawStage = trim(
            (string) (
                $employee->employee_stage
                ?: $employee->employment_type
                ?: ''
            )
        );

        if ($rawStage === '') {
            throw new DomainException(
                "Employee #{$employee->id} has no valid employee_stage or employment_type assigned."
            );
        }

        return $this->normalizeStage($rawStage);
    }

    public function isUnpaidInternEmployee(
        EmployeeM $employee
    ): bool {
        return (int) ($employee->is_paid_intern ?? 1) === 0
            || (
                (float) ($employee->actual_salary ?? 0.0) <= 0
                && (int) ($employee->is_paid_intern ?? 0) === 0
            );
    }

    public function isUnpaidIntern(
        LeaveAllocationM $allocation,
        ?EmployeeM $employee = null
    ): bool {
        $stage = strtolower((string) ($allocation->employment_stage ?: ''));
        if ($stage !== '' && $stage !== self::STAGE_INTERNSHIP && $stage !== 'intern') {
            return false;
        }

        $employee ??= $this->resolveEmployeeForAllocation($allocation);
        if (! $employee) {
            return false;
        }

        $empStage = strtolower((string) ($employee->employee_stage ?: $employee->employment_type ?: ''));
        if ($stage === '' && $empStage !== self::STAGE_INTERNSHIP && $empStage !== 'intern') {
            return false;
        }

        return $this->isUnpaidInternEmployee($employee);
    }

    private function resolveEmployeeForAllocation(
        LeaveAllocationM $allocation
    ): ?EmployeeM {
        if ($allocation->relationLoaded('employee') && $allocation->employee) {
            return $allocation->employee;
        }

        return EmployeeM::find($allocation->employee_id);
    }

    private function resolvePolicyForAllocation(
        LeaveAllocationM $allocation
    ): LeavePolicyM {
        if ($allocation->policy_id) {
            $policy = LeavePolicyM::find($allocation->policy_id);
            if ($policy) {
                return $policy;
            }
        }

        $employee = $this->resolveEmployeeForAllocation($allocation);

        if ($employee) {
            $year = (int) ($allocation->year ?: $this->now()->year);
            $policy = $this->policyService->forEmployee($employee, $this->yearStart($year));

            if (! $policy) {
                throw new DomainException("No active leave policy found for employee #{$employee->id} for year {$year}.");
            }

            return $policy;
        }

        $policy = $this->policyService->activeDefault();

        if (! $policy) {
            throw new DomainException('No active default leave policy found.');
        }

        return $policy;
    }

    private function allocationStartDate(
        EmployeeM $employee,
        string $stage,
        int $year,
        ?Carbon $effectiveDate = null
    ): ?Carbon {
        $date = match ($stage) {
            self::STAGE_PERMANENT =>
                $effectiveDate?->toDateString()
                ?: $employee->permanent_at
                ?: $employee->confirmation_effective_date
                ?: $employee->confirmation_date
                ?: $employee->joining_date,

            self::STAGE_INTERNSHIP =>
                $effectiveDate?->toDateString()
                ?: $employee->internship_start_date
                ?: $employee->joining_date,

            self::STAGE_PROBATION =>
                $effectiveDate?->toDateString()
                ?: $employee->probation_start_date
                ?: $employee->joining_date,

            default => null,
        };

        if (! $date) {
            return null;
        }

        $parsedDate = Carbon::parse($date, self::TIMEZONE)->copy();
        $yearStart = $this->yearStart($year);

        return $parsedDate->lt($yearStart) ? $yearStart : $parsedDate;
    }

    private function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    private function yearStart(int $year): Carbon
    {
        return Carbon::create($year, 1, 1, 0, 0, 0, self::TIMEZONE);
    }

    private function yearEnd(int $year): Carbon
    {
        return Carbon::create($year, 12, 31, 0, 0, 0, self::TIMEZONE);
    }
}
