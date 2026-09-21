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
    private const TIMEZONE = 'Asia/Kolkata';

    private const STAGE_PERMANENT = 'permanent';
    private const STAGE_PROBATION = 'probation';
    private const STAGE_INTERNSHIP = 'internship';

    public function __construct(
        private LeavePolicyService $policyService,
        private EmployeeEligibilityS $eligibilityService
    ) {
    }

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

            if ($allocation?->is_locked) {
                return $allocation;
            }

            $policyDate = $this->yearStart($year);

            $policy = $this->policyService->forEmployee($employee, $policyDate);

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

            [$total, $paid, $sick] = $this->calculateAllocationAmounts(
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

            $paidUsed = (float) ($allocation->paid_used ?? 0.0);
            $sickUsed = (float) ($allocation->sick_used ?? 0.0);
            $compOffUsed = (float) ($allocation->comp_off_used ?? 0.0);
            $lwpUsed = (float) ($allocation->lwp_used ?? 0.0);

            $monthlyUsed = (float) (
                $allocation->monthly_used_this_month ?? 0.0
            );

            $monthlyCarry = (float) (
                $allocation->monthly_carry_forward ?? 0.0
            );

            $paidAllocated = $this->resolveAnnualAllocatedValue(
                $allocation,
                'paid_allocated',
                $paid,
                $forceStage
            );

            $sickAllocated = $this->resolveAnnualAllocatedValue(
                $allocation,
                'sick_allocated',
                $sick,
                $forceStage
            );

            $totalAllocated = $this->resolveAnnualAllocatedValue(
                $allocation,
                'total_allocated',
                $total,
                $forceStage
            );

            $allocationReason = $allocation->allocation_reason
                ?: "Annual Allocation for {$year}";

            $createdByUserId = $allocation->created_by_user_id ?? $userId;

            $isLocked = $isExisting
                ? (bool) $allocation->is_locked
                : $isPastYear;

            $allocation->fill([
                'employment_stage' => $stage,
                'policy_id' => $policy->id,

                'confirmation_date' => $employee->confirmation_date,

                'allocation_from_date' => $fromDate?->toDateString(),
                'allocation_to_date' => $toDate->toDateString(),

                'total_allocated' => $totalAllocated,
                'paid_allocated' => $paidAllocated,
                'sick_allocated' => $sickAllocated,

                'paid_used' => $paidUsed,
                'sick_used' => $sickUsed,
                'comp_off_used' => $compOffUsed,
                'lwp_used' => $lwpUsed,

                'comp_off_allocated' => (float) (
                    $allocation->comp_off_allocated ?? 0.0
                ),

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

            if ($allocation->wasRecentlyCreated || $balanceDifference !== 0.0) {
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

    public function recalculateAllocationFields(
        LeaveAllocationM $allocation,
        ?LeavePolicyM $policy = null,
        ?EmployeeM $employee = null
    ): LeaveAllocationM {
        $policy ??= $this->resolvePolicyForAllocation($allocation);

        $this->validatePolicy($policy);

        $stage = $this->normalizeStage(
            (string) ($allocation->employment_stage ?? '')
        );

        $employee ??= $this->resolveEmployeeForAllocation($allocation);

        if ($stage === self::STAGE_INTERNSHIP) {
            $this->recalculateInternshipAllocation(
                $allocation,
                $policy,
                $employee
            );
        } elseif ($stage === self::STAGE_PROBATION) {
            $this->recalculateStandardAllocation(
                $allocation,
                false
            );
        } else {
            $this->recalculatePermanentAllocation(
                $allocation,
                $policy
            );
        }

        return $allocation;
    }

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

        $this->validatePolicy($policy);

        $normalizedStage = $this->normalizeStage($stage);

        return match ($normalizedStage) {
            self::STAGE_PROBATION => $this->calculateProbationAllocation($policy),

            self::STAGE_INTERNSHIP => $this->calculateInternshipAllocation(
                $policy,
                (bool) $isPaidIntern
            ),

            self::STAGE_PERMANENT => $this->calculatePermanentAllocation(
                $policy,
                $fromDate,
                $toDate
            ),

            default => throw new DomainException(
                "Unsupported leave allocation stage: {$normalizedStage}."
            ),
        };
    }

    private function calculateProbationAllocation(
        LeavePolicyM $policy
    ): array {
        $limit = round(
            max(0.0, (float) $policy->probation_leave_limit),
            2
        );

        return [
            $limit,
            $limit,
            0.0,
        ];
    }

    private function calculateInternshipAllocation(
        LeavePolicyM $policy,
        bool $isPaidIntern
    ): array {
        $limit = round(
            max(0.0, (float) $policy->internship_leave_limit),
            2
        );

        if (! $isPaidIntern) {
            return [
                $limit,
                0.0,
                0.0,
            ];
        }

        return [
            $limit,
            $limit,
            0.0,
        ];
    }

    private function calculatePermanentAllocation(
        LeavePolicyM $policy,
        ?Carbon $fromDate,
        ?Carbon $toDate
    ): array {
        if (! $fromDate) {
            return [0.0, 0.0, 0.0];
        }

        if ($toDate && $fromDate->gt($toDate)) {
            return [0.0, 0.0, 0.0];
        }

        return $this->calculatePermanentProration(
            $policy,
            $fromDate,
            $toDate
        );
    }

    private function calculatePermanentProratedTotal(
        LeavePolicyM $policy,
        Carbon $fromDate
    ): float {
        $remainingMonths = 13 - $fromDate->month;

        return (float) round(
            ((float) $policy->annual_total_leaves / 12) * $remainingMonths
        );
    }

    private function calculatePermanentProration(
        LeavePolicyM $policy,
        Carbon $fromDate,
        ?Carbon $toDate = null
    ): array {
        $total = $this->calculatePermanentProratedTotal(
            $policy,
            $fromDate
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

        $allocation->comp_off_remaining = $this->calculateCompOffRemaining(
            $allocation
        );

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

        $allocation->comp_off_remaining =
            $this->calculateCompOffRemaining($allocation);

        $allocation->total_allocated = round(
            (float) $allocation->paid_allocated
            + (float) $allocation->sick_allocated,
            2
        );

        $allocation->total_used = round(
            (float) $allocation->paid_used
            + (float) $allocation->sick_used
            + (float) $allocation->comp_off_used,
            2
        );

        $allocation->total_remaining = round(
            (float) $allocation->paid_remaining
            + (float) $allocation->sick_remaining
            + (float) $allocation->comp_off_remaining,
            2
        );

        if ($includeMonthlyReset) {
            $this->resetMonthlyFields($allocation);
        }
    }

    private function recalculatePermanentAllocation(
        LeaveAllocationM $allocation,
        LeavePolicyM $policy
    ): void {
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

        $allocation->comp_off_remaining =
            $this->calculateCompOffRemaining($allocation);

        $allocation->total_allocated = round(
            (float) $allocation->paid_allocated
            + (float) $allocation->sick_allocated,
            2
        );

        $allocation->total_used = round(
            (float) $allocation->paid_used
            + (float) $allocation->sick_used
            + (float) $allocation->comp_off_used,
            2
        );

        $allocation->total_remaining = round(
            (float) $allocation->paid_remaining
            + (float) $allocation->sick_remaining
            + (float) $allocation->comp_off_remaining,
            2
        );

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

    private function resolveAnnualAllocatedValue(
        LeaveAllocationM $allocation,
        string $field,
        float $calculatedValue,
        ?string $forceStage
    ): float {
        if (
            $allocation->exists
            && $allocation->{$field} !== null
            && $forceStage === null
        ) {
            return (float) $allocation->{$field};
        }

        return round($calculatedValue, 2);
    }

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
                "Invalid leave policy #{$policy->id}: " .
                "annual_total_leaves must be greater than 0."
            );
        }

        if ($paid < 0 || $sick < 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: " .
                "annual_paid_leaves and annual_sick_leaves " .
                "must not be negative."
            );
        }

        if (
            round($paid + $sick, 2)
            !== round($total, 2)
        ) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: " .
                "annual_paid_leaves ({$paid}) + " .
                "annual_sick_leaves ({$sick}) must equal " .
                "annual_total_leaves ({$total})."
            );
        }

        if ($monthly === null || (float) $monthly < 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: " .
                "monthly_leave_limit must be present and non-negative."
            );
        }

        if ($probation === null || (float) $probation < 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: " .
                "probation_leave_limit must be present and non-negative."
            );
        }

        if ($internship === null || (float) $internship < 0) {
            throw new DomainException(
                "Invalid leave policy #{$policy->id}: " .
                "internship_leave_limit must be present and non-negative."
            );
        }
    }

    public function normalizeStage(string $rawStage): string
    {
        $stage = strtolower(trim($rawStage));

        if ($stage === '') {
            throw new DomainException(
                'Employee stage cannot be empty.'
            );
        }

        if (in_array(
            $stage,
            [
                'permanent',
                'confirmed',
                'full_time',
                'full-time',
            ],
            true
        )) {
            return self::STAGE_PERMANENT;
        }

        if (
            in_array(
                $stage,
                [
                    'probation',
                    'on_probation',
                ],
                true
            )
            || str_contains($stage, 'probation')
        ) {
            return self::STAGE_PROBATION;
        }

        if (
            in_array(
                $stage,
                [
                    'internship',
                    'intern',
                ],
                true
            )
            || str_contains($stage, 'intern')
        ) {
            return self::STAGE_INTERNSHIP;
        }

        throw new DomainException(
            "Unsupported or unknown employee stage: '{$rawStage}'. " .
            'Valid stages are: permanent, probation, internship.'
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
                "Employee #{$employee->id} has no valid " .
                'employee_stage or employment_type assigned.'
            );
        }

        return $this->normalizeStage($rawStage);
    }

    private function isUnpaidInternEmployee(
        EmployeeM $employee
    ): bool {
        return
            (int) ($employee->is_paid_intern ?? 1) === 0
            ||
            (
                (float) ($employee->actual_salary ?? 0.0) <= 0
                && (int) ($employee->is_paid_intern ?? 0) === 0
            );
    }

    private function isUnpaidIntern(
        LeaveAllocationM $allocation,
        ?EmployeeM $employee = null
    ): bool {
        $employee ??= $this->resolveEmployeeForAllocation(
            $allocation
        );

        return $employee
            ? $this->isUnpaidInternEmployee($employee)
            : false;
    }

    private function resolveEmployeeForAllocation(
        LeaveAllocationM $allocation
    ): ?EmployeeM {
        if (
            $allocation->relationLoaded('employee')
            && $allocation->employee
        ) {
            return $allocation->employee;
        }

        return EmployeeM::find(
            $allocation->employee_id
        );
    }

    private function resolvePolicyForAllocation(
        LeaveAllocationM $allocation
    ): LeavePolicyM {
        $employee = $this->resolveEmployeeForAllocation(
            $allocation
        );

        if ($employee) {
            $year = (int) (
                $allocation->year
                ?: $this->now()->year
            );

            $policy = $this->policyService->forEmployee(
                $employee,
                $this->yearStart($year)
            );

            if (! $policy) {
                throw new DomainException(
                    "No active leave policy found for employee " .
                    "#{$employee->id} for year {$year}."
                );
            }

            return $policy;
        }

        $policy = $this->policyService->activeDefault();

        if (! $policy) {
            throw new DomainException(
                'No active default leave policy found.'
            );
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
                $employee->permanent_at
                ?: $effectiveDate?->toDateString()
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

        $parsedDate = Carbon::parse(
            $date,
            self::TIMEZONE
        );

        $yearStart = $this->yearStart($year);

        return $parsedDate->lt($yearStart)
            ? $yearStart
            : $parsedDate;
    }

    private function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    private function yearStart(int $year): Carbon
    {
        return Carbon::create(
            $year,
            1,
            1,
            0,
            0,
            0,
            self::TIMEZONE
        );
    }

    private function yearEnd(int $year): Carbon
    {
        return Carbon::create(
            $year,
            12,
            31,
            0,
            0,
            0,
            self::TIMEZONE
        );
    }
}
