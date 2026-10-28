<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeaveBalanceTransactionM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Enums\LeaveBucket;
use App\Services\HRMS\Employee\EmployeeEligibilityS;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        $isLifecycleTransition = $effectiveDate !== null && $forceStage !== null;
        $stage = $forceStage !== null
            ? $this->normalizeStage($forceStage)
            : $this->resolveEmployeeLeaveContext($employee)['stage'];
        $stageRules = $stage !== self::STAGE_PERMANENT;
        if ($stage === self::STAGE_PERMANENT) {
            $effectiveDate = $this->effectiveDateForAllocationYear($employee, $year, $effectiveDate);
            if ($effectiveDate === false) {
                return LeaveAllocationM::firstOrNew([
                    'employee_id' => $employee->id,
                    'year' => $year,
                ]);
            }
        }

        if (! $this->isActiveForLeaveEnrollment($employee)) {
            return LeaveAllocationM::firstOrNew([
                'employee_id' => $employee->id,
                'year' => $year,
            ]);
        }

        try {
            return DB::transaction(function () use (
            $employee,
            $year,
            $userId,
            $stage,
            $stageRules,
            $effectiveDate,
            $isLifecycleTransition
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

            $priorYearAllocation = LeaveAllocationM::query()
                ->where('employee_id', $employee->id)
                ->where('year', $year - 1)
                ->first();
            if ($allocation?->isLedgerBacked()) {
                return $allocation;
            }
            if (! $allocation && $priorYearAllocation?->isLedgerBacked() && $stage === self::STAGE_PERMANENT) {
                $allocation = new LeaveAllocationM([
                    'employee_id' => $employee->id,
                    'year' => $year,
                    'employment_stage' => self::STAGE_PERMANENT,
                    'policy_id' => $priorYearAllocation->policy_id,
                    'confirmation_date' => $employee->confirmation_date,
                    'allocation_from_date' => $this->yearStart($year)->toDateString(),
                    'allocation_to_date' => $this->yearEnd($year)->toDateString(),
                    'uses_ledger' => true,
                    'is_locked' => false,
                ]);
                $allocation->save();
                return $allocation;
            }
            if ($stage === self::STAGE_PERMANENT && $isLifecycleTransition) {
                $policy = $this->policyForEmployee($employee, $effectiveDate);
                if (! $policy) {
                    throw new DomainException("No active leave policy found for employee #{$employee->id} on permanent confirmation.");
                }

                $allocation ??= new LeaveAllocationM(['employee_id' => $employee->id, 'year' => $year]);
                $allocation->fill([
                    'employment_stage' => self::STAGE_PERMANENT,
                    'policy_id' => $policy->id,
                    'confirmation_date' => $effectiveDate->toDateString(),
                    'allocation_from_date' => $effectiveDate->toDateString(),
                    'allocation_to_date' => $this->yearEnd($year)->toDateString(),
                    'total_allocated' => '0.00',
                    'paid_allocated' => '0.00',
                    'sick_allocated' => '0.00',
                    'paid_used' => '0.00',
                    'sick_used' => '0.00',
                    'lwp_used' => '0.00',
                    'total_used' => (string) ($allocation->comp_off_used ?? '0.00'),
                    'paid_remaining' => '0.00',
                    'sick_remaining' => '0.00',
                    'comp_off_remaining' => (string) ($allocation->comp_off_remaining ?? '0.00'),
                    'total_remaining' => (string) ($allocation->comp_off_remaining ?? '0.00'),
                    'uses_ledger' => true,
                    'ledger_effective_from' => null,
                    'is_locked' => false,
                ]);
                $allocation->save();

                return $allocation;
            }
            if ($stage === self::STAGE_PERMANENT && ! $isLifecycleTransition) {
                // Do not recalculate or create a legacy annual snapshot. Permanent cutover is explicit.
                return $allocation ?? new LeaveAllocationM([
                    'employee_id' => $employee->id,
                    'year' => $year,
                    'employment_stage' => self::STAGE_PERMANENT,
                    'uses_ledger' => false,
                ]);
            }
            $policyDate = $this->yearStart($year);
            $policy = $this->policyForEmployee($employee, $policyDate);

            if (! $policy) {
                throw new DomainException("No active leave policy found for employee #{$employee->id} in year {$year}.");
            }

            $this->validatePolicy($policy);

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
            $existingStage = $allocation && $allocation->employment_stage
                ? $this->normalizeStage((string) $allocation->employment_stage)
                : null;
            $stageChanged = $allocation && $existingStage !== $stage;
            $paidUsed = $stageChanged ? 0.0 : (float) ($allocation->paid_used ?? 0.0);
            $sickUsed = $stageChanged ? 0.0 : (float) ($allocation->sick_used ?? 0.0);
            $compOffUsed = (float) ($allocation->comp_off_used ?? 0.0);
            $lwpUsed = $stageChanged ? 0.0 : (float) ($allocation->lwp_used ?? 0.0);
            $compOffAllocated = (float) ($allocation->comp_off_allocated ?? 0.0);
            $allocationReason = $allocation->allocation_reason ?: "Annual Allocation for {$year}";
            $createdByUserId = $allocation->created_by_user_id ?? $userId;
            $isLocked = $isExisting ? (bool) $allocation->is_locked : $isPastYear;

            if ($stageRules) {
                $stageLimit = $stage === self::STAGE_PROBATION
                    ? (string) $policy->probation_leave_limit
                    : ($this->isUnpaidInternEmployee($employee)
                        ? (string) $policy->unpaid_intern_leave_limit
                        : (string) $policy->internship_leave_limit);
                $stageLimit = bcadd($stageLimit, '0', 2);
                $entitlement = new LeaveEntitlementResult(
                    stage: $stage,
                    totalAllocated: (float) $stageLimit,
                    paidAllocated: $stage === self::STAGE_INTERNSHIP && $this->isUnpaidInternEmployee($employee) ? 0.0 : (float) $stageLimit,
                    sickAllocated: 0.0,
                    monthlyQuota: 0.0,
                    allocationFromDate: $fromDate?->toDateString(),
                    allocationToDate: $toDate->toDateString(),
                    policyId: $policy->id,
                    isPaidIntern: $stage !== self::STAGE_INTERNSHIP || ! $this->isUnpaidInternEmployee($employee)
                );
            }

            $allocation->fill([
                'employment_stage' => $stage,
                'uses_ledger' => (bool) ($allocation->uses_ledger ?? false),
                'policy_id' => $policy->id,
                'confirmation_date' => $employee->confirmation_date,
                'allocation_from_date' => $fromDate?->toDateString(),
                'allocation_to_date' => $toDate->toDateString(),
                'total_allocated' => $entitlement->totalAllocated,
                'paid_allocated' => $entitlement->paidAllocated,
                'sick_allocated' => $entitlement->sickAllocated,
                'paid_used' => $paidUsed,
                'sick_used' => $sickUsed,
                'comp_off_used' => $compOffUsed,
                'lwp_used' => $lwpUsed,
                'total_used' => round($paidUsed + $sickUsed + $compOffUsed + $lwpUsed, 2),
                'comp_off_allocated' => $compOffAllocated,
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
        } catch (QueryException $e) {
            // Concurrent first-time generators can both observe no row. The unique
            // employee/year index chooses a winner; return that committed row safely.
            $message = strtolower($e->getMessage());
            $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
            $driverCode = (int) ($e->errorInfo[1] ?? 0);
            $isExpectedUniqueCollision = (
                $sqlState === '23505'
                && (str_contains($message, 'leave_allocations_emp_year_unique')
                    || str_contains($message, 'leave_allocations_employee_id_year_unique'))
            ) || (
                $sqlState === '23000'
                && $driverCode === 1062
                && (str_contains($message, 'leave_allocations_emp_year_unique')
                    || str_contains($message, 'leave_allocations_employee_id_year_unique'))
            ) || (
                $sqlState === '23000'
                && $driverCode === 19
                && str_contains($message, 'unique constraint failed: leave_allocations.employee_id, leave_allocations.year')
            );
            if (! $isExpectedUniqueCollision) {
                throw $e;
            }
            $existing = LeaveAllocationM::query()
                ->where('employee_id', $employee->id)
                ->where('year', $year)
                ->first();
            if ($existing) {
                return $existing;
            }
            throw $e;
        }
    }

    /**
     * Single employee allocation generation with eligibility enforcement.
     */
    public function generateSingle(int $employeeId, int $year, ?int $userId = null): LeaveAllocationM
    {
        $employee = EmployeeM::findOrFail($employeeId);

        if (! $this->isActiveForLeaveEnrollment($employee)) {
            throw new DomainException(
                'Cannot generate leave allocation: Employee is not active.'
            );
        }

        $now = Carbon::now(self::TIMEZONE);
        $through = $year < (int) $now->year ? $this->yearEnd($year) : $now;
        $allocation = $this->enrollEmployee($employee, $through, $userId, $year);

        if (! $allocation->exists) {
            throw new DomainException("Leave allocation could not be generated for year {$year}.");
        }

        if (! $allocation->isLedgerBacked()) {
            app(LeaveMonthlyBalanceService::class)->rebuildEmployeeThrough(
                $employee,
                $year,
                (int) $through->month
            );
        }

        return $allocation;
    }

    /** One idempotent enrollment path shared by Generate Single and Generate Year. */
    public function enrollEmployee(EmployeeM $employee, Carbon $asOf, ?int $userId = null, ?int $allocationYear = null): LeaveAllocationM
    {
        return DB::transaction(fn (): LeaveAllocationM => $this->enrollEmployeeInTransaction($employee, $asOf, $userId, $allocationYear));
    }

    private function enrollEmployeeInTransaction(EmployeeM $employee, Carbon $asOf, ?int $userId, ?int $allocationYear): LeaveAllocationM
    {
        $context = $this->resolveEmployeeLeaveContext($employee);
        if ($context['stage'] !== self::STAGE_PERMANENT) {
            return $this->getOrGenerate($employee, $allocationYear ?? (int) $asOf->year, $userId);
        }
        if (! $context['permanent_date']) {
            throw new DomainException('Employee master confirmation_date is null; permanent leave eligibility cannot be resolved.');
        }
        $asOf = $asOf->copy()->setTimezone(self::TIMEZONE)->startOfDay();
        $year = $allocationYear ?? (int) $asOf->year;
        $futureYear = $year > (int) $asOf->year;
        if (Carbon::parse($context['permanent_date'], self::TIMEZONE)->year > $year) {
            throw new DomainException("Employee #{$employee->id} was not permanent during leave year {$year}.");
        }
        $policyDate = $futureYear ? $this->yearStart($year) : $asOf;
        $policy = $this->policyForEmployee($employee, $policyDate);
        if (! $policy) {
            throw new DomainException("No active leave policy found for employee #{$employee->id} on {$asOf->toDateString()}.");
        }

        $allocation = DB::transaction(function () use ($employee, $year, $policy, $context): LeaveAllocationM {
            $row = LeaveAllocationM::query()->where('employee_id', $employee->id)->where('year', $year)->lockForUpdate()->first();
            if (! $row) {
                $row = new LeaveAllocationM([
                    'employee_id' => $employee->id,
                    'year' => $year,
                    'employment_stage' => self::STAGE_PERMANENT,
                    'policy_id' => $policy->id,
                    'confirmation_date' => $context['permanent_date'],
                    'allocation_from_date' => Carbon::parse($context['permanent_date'], self::TIMEZONE)->year === $year
                        ? $context['permanent_date'] : $this->yearStart($year)->toDateString(),
                    'allocation_to_date' => $this->yearEnd($year)->toDateString(),
                    'uses_ledger' => true,
                    'ledger_effective_from' => null,
                    'is_locked' => false,
                    'total_allocated' => '0.00', 'paid_allocated' => '0.00', 'sick_allocated' => '0.00',
                    'paid_used' => '0.00', 'sick_used' => '0.00', 'lwp_used' => '0.00',
                    'paid_remaining' => '0.00', 'sick_remaining' => '0.00', 'total_remaining' => '0.00',
                ]);
            } elseif ($row->is_locked) {
                throw new DomainException('This leave allocation is locked.');
            }
            if (! $row->isLedgerBacked()) {
                $row->forceFill([
                    'employment_stage' => self::STAGE_PERMANENT,
                    'policy_id' => $policy->id,
                    'confirmation_date' => $context['permanent_date'],
                    'allocation_from_date' => Carbon::parse($context['permanent_date'], self::TIMEZONE)->year === $year
                        ? $context['permanent_date'] : $this->yearStart($year)->toDateString(),
                    'allocation_to_date' => $this->yearEnd($year)->toDateString(),
                    'uses_ledger' => true,
                    'ledger_effective_from' => null,
                ]);
            }
            $row->save();
            return $row;
        });

        if (! $futureYear) {
            app(MonthlyLeaveAccrualService::class)->catchUpEmployee($employee, $asOf, $userId);
            $this->backfillApprovedUsage($employee, $allocation, $asOf, $userId);
            app(LeaveMonthlyBalanceService::class)->rebuildEmployeeThrough($employee, $year, (int) $asOf->month);
        }

        return app(LeaveBalanceMovementService::class)->refreshSnapshot($allocation->fresh());
    }

    private function backfillApprovedUsage(EmployeeM $employee, LeaveAllocationM $allocation, Carbon $asOf, ?int $userId): void
    {
        $futureYear = DB::table('leave_request_dates as d')->join('leave_requests as r', 'r.id', '=', 'd.leave_request_id')
            ->where('d.employee_id', $employee->id)->where('r.status', 'approved')
            ->whereDate('d.leave_date', '>', $asOf->toDateString())
            ->whereYear('d.leave_date', '>', (int) $allocation->year)
            ->orderBy('d.leave_date')->value(DB::raw('YEAR(d.leave_date)'));
        if ($futureYear) {
            $futureAllocation = LeaveAllocationM::query()->where('employee_id', $employee->id)->where('year', (int) $futureYear)->first();
            if (! $futureAllocation?->isLedgerBacked()) {
                throw new DomainException("Approved leave for {$futureYear} has no ledger-backed allocation to debit.");
            }
        }

        $movement = app(LeaveBalanceMovementService::class);
        $splitter = app(PaidLeaveSplitter::class);
        $credits = LeaveBalanceTransactionM::query()
            ->where('leave_allocation_id', $allocation->id)
            ->whereIn('transaction_type', ['monthly_accrual', 'opening_balance'])
            ->orderBy('effective_date')->orderBy('id')
            ->get(['bucket', 'amount', 'effective_date']);
        $requestMovements = LeaveBalanceTransactionM::query()
            ->where('leave_allocation_id', $allocation->id)
            ->whereNotNull('leave_request_id')
            ->whereIn('transaction_type', ['leave_debit', 'reversal', 'adjustment'])
            ->get(['leave_request_id', 'bucket', 'transaction_type', 'amount', 'idempotency_key'])
            ->groupBy('leave_request_id')
            ->map(static fn ($rows) => $rows->groupBy('bucket'));

        $requests = LeaveRequestM::query()
            ->with(['dates' => fn ($query) => $query->orderBy('leave_date'), 'leaveType'])
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($allocation): void {
                $query->whereYear('start_date', (int) $allocation->year)
                    ->orWhereYear('end_date', (int) $allocation->year);
            })
            ->orderBy('start_date')->orderBy('id')->get();

        $cutoverDate = $allocation->ledger_effective_from
            ? Carbon::parse((string) $allocation->ledger_effective_from, self::TIMEZONE)->toDateString()
            : null;
        $creditIndex = 0;
        $creditBalance = ['PL' => '0.000000', 'SL' => '0.000000'];
        $runningUsed = ['PL' => '0.000000', 'SL' => '0.000000'];
        $policyByMonth = [];

        foreach ($requests as $request) {
            if ($request->leaveType?->is_comp_off || $request->leaveType?->is_lwp) {
                continue;
            }
            try {
                $selected = LeaveBucket::fromLeaveType($request->leaveType);
            } catch (\Throwable) {
                continue;
            }

            $dates = $request->dates->filter(function ($date) use ($allocation, $cutoverDate): bool {
                $leaveDate = Carbon::parse((string) $date->leave_date, self::TIMEZONE)->toDateString();
                if ((int) Carbon::parse($leaveDate, self::TIMEZONE)->year !== (int) $allocation->year
                    || ($cutoverDate !== null && $leaveDate <= $cutoverDate)) {
                    return false;
                }

                return (bool) ($date->deduct_as_leave ?? true)
                    || bccomp((string) ($date->paid_day ?? '0'), '0', 6) > 0
                    || bccomp((string) ($date->sick_day ?? '0'), '0', 6) > 0
                    || bccomp((string) ($date->lwp_day ?? '0'), '0', 6) > 0;
            })->values();
            if ($dates->isEmpty()) {
                continue;
            }

            $oldTotals = ['PL' => '0.000000', 'SL' => '0.000000', 'LOP' => '0.000000'];
            $newTotals = ['PL' => '0.000000', 'SL' => '0.000000', 'LOP' => '0.000000'];
            $firstMovementDate = null;

            foreach ($dates as $dateRow) {
                $leaveDate = Carbon::parse((string) $dateRow->leave_date, self::TIMEZONE);
                $oldPaid = bcadd((string) ($dateRow->paid_day ?? '0'), '0', 6);
                $oldSick = bcadd((string) ($dateRow->sick_day ?? '0'), '0', 6);
                $oldLop = bcadd((string) ($dateRow->lwp_day ?? '0'), '0', 6);
                $requestedForDate = bcadd(bcadd($oldPaid, $oldSick, 6), $oldLop, 6);
                if (bccomp($requestedForDate, '0', 6) <= 0) {
                    continue;
                }
                $firstMovementDate ??= $leaveDate->toDateString();

                while ($creditIndex < $credits->count()
                    && Carbon::parse((string) $credits[$creditIndex]->effective_date, self::TIMEZONE)->toDateString() <= $leaveDate->toDateString()) {
                    $credit = $credits[$creditIndex];
                    if (array_key_exists((string) $credit->bucket, $creditBalance)) {
                        $creditBalance[(string) $credit->bucket] = bcadd(
                            $creditBalance[(string) $credit->bucket],
                            (string) $credit->amount,
                            6
                        );
                    }
                    $creditIndex++;
                }

                $monthKey = $leaveDate->format('Y-m');
                $policyByMonth[$monthKey] ??= $this->policyService->requireForEmployee(
                    $employee,
                    $leaveDate->copy()->startOfMonth()
                );
                $available = [];
                foreach (LeaveBucket::cases() as $bucket) {
                    $balance = bcsub($creditBalance[$bucket->value], $runningUsed[$bucket->value], 6);
                    $available[$bucket->value] = bccomp($balance, '0', 6) < 0 ? '0.000000' : $balance;
                }

                $split = $splitter->split(
                    $selected,
                    $requestedForDate,
                    $available['PL'],
                    $available['SL'],
                    $policyByMonth[$monthKey],
                    false,
                    true
                );
                $newPaid = $split['pl'];
                $newSick = $split['sl'];
                $newLop = $split['lop'];
                $remark = $dateRow->remarks;
                $fallback = $selected->otherPaidBucket()->value === 'SL' ? $split['sl'] : $split['pl'];
                if (bccomp($fallback, '0', 6) > 0) {
                    $remark = sprintf('Adjusted to %s due to insufficient %s balance', $selected->otherPaidBucket()->value, $selected->value);
                }
                if (bccomp($split['lop'], '0', 6) > 0) {
                    $remark = ($remark ? $remark . '; ' : '') . 'Converted to LWP due to insufficient PL and SL balance';
                }

                if (bccomp($oldPaid, $newPaid, 6) !== 0
                    || bccomp($oldSick, $newSick, 6) !== 0
                    || bccomp($oldLop, $newLop, 6) !== 0
                    || $dateRow->remarks !== $remark) {
                    $dateRow->paid_day = $newPaid;
                    $dateRow->sick_day = $newSick;
                    $dateRow->lwp_day = $newLop;
                    $dateRow->remarks = $remark;
                    $dateRow->save();
                }

                $oldTotals['PL'] = bcadd($oldTotals['PL'], $oldPaid, 6);
                $oldTotals['SL'] = bcadd($oldTotals['SL'], $oldSick, 6);
                $oldTotals['LOP'] = bcadd($oldTotals['LOP'], $oldLop, 6);
                $newTotals['PL'] = bcadd($newTotals['PL'], $newPaid, 6);
                $newTotals['SL'] = bcadd($newTotals['SL'], $newSick, 6);
                $newTotals['LOP'] = bcadd($newTotals['LOP'], $newLop, 6);
                $runningUsed['PL'] = bcadd($runningUsed['PL'], $newPaid, 6);
                $runningUsed['SL'] = bcadd($runningUsed['SL'], $newSick, 6);
            }

            if ($firstMovementDate !== null) {
                $newRequestPaid = bcadd(bcsub((string) ($request->paid_days ?? '0'), $oldTotals['PL'], 6), $newTotals['PL'], 6);
                $newRequestSick = bcadd(bcsub((string) ($request->sick_days ?? '0'), $oldTotals['SL'], 6), $newTotals['SL'], 6);
                $newRequestLop = bcadd(bcsub((string) ($request->lwp_days ?? '0'), $oldTotals['LOP'], 6), $newTotals['LOP'], 6);
                if (bccomp((string) ($request->paid_days ?? '0'), $newRequestPaid, 6) !== 0
                    || bccomp((string) ($request->sick_days ?? '0'), $newRequestSick, 6) !== 0
                    || bccomp((string) ($request->lwp_days ?? '0'), $newRequestLop, 6) !== 0) {
                    $request->forceFill([
                        'paid_days' => $newRequestPaid,
                        'sick_days' => $newRequestSick,
                        'lwp_days' => $newRequestLop,
                    ])->save();
                }

                foreach (LeaveBucket::cases() as $bucket) {
                    $desiredMovement = bcsub('0', $newTotals[$bucket->value], 6);
                    $existingRows = $requestMovements->get($request->id)?->get($bucket->value, collect()) ?? collect();
                    $actualMovement = '0.000000';
                    foreach ($existingRows as $existingRow) {
                        $actualMovement = bcadd($actualMovement, (string) $existingRow->amount, 6);
                    }
                    $correction = bcsub($desiredMovement, $actualMovement, 6);
                    if (bccomp($correction, '0', 6) === 0) {
                        continue;
                    }

                    $month = (int) Carbon::parse($firstMovementDate, self::TIMEZONE)->month;
                    if ($existingRows->isEmpty() && bccomp($newTotals[$bucket->value], '0', 6) > 0) {
                        $movement->debit(
                            $allocation,
                            $bucket,
                            (int) $request->id,
                            (int) $allocation->year,
                            $month,
                            $newTotals[$bucket->value],
                            $firstMovementDate,
                            $userId,
                            'import',
                            'Approved leave debit imported for request #' . $request->id
                        );
                    } else {
                        // A successful correction makes the next enrollment pass a no-op.
                        // Use a fresh event key so a later policy/request reconciliation can
                        // legitimately move back to a previously seen target without colliding
                        // with an older append-only correction.
                        $uuid = (string) Str::uuid();
                        $movement->adjustment(
                            $allocation,
                            $bucket,
                            (int) $allocation->year,
                            $correction,
                            $firstMovementDate,
                            $uuid,
                            'Append-only backfill correction for approved leave request #' . $request->id,
                            $userId,
                            'import',
                            (int) $request->id
                        );
                    }
                }
            }
        }
    }

    /**
     * Bulk yearly allocation generation with bounded memory and batched profile loading.
     */
    public function generateYearly(int $year, ?int $userId = null): array
    {
        $startedAt = microtime(true);
        $summary = [
            'total_processed' => 0,
            'successful_allocations' => 0,
            'accrual_entries_posted' => 0,
            'legacy_permanent_not_enrolled' => 0,
            'skipped' => 0,
            'skipped_employees' => [],
            'failed' => 0,
            'failed_employee_ids' => [],
        ];
        $query = EmployeeM::query()
            ->without(['user', 'department', 'designation', 'position', 'systemRole'])
            ->with('profile')
            ->where(function ($query) {
                $query->where('is_active', 1)->orWhereNull('is_active');
            });
        if (Schema::hasTable('employee_exit_processes')) {
            $query->addSelect([
                'has_completed_exit' => DB::table('employee_exit_processes')
                    ->selectRaw('1')
                    ->whereColumn('employee_exit_processes.employee_id', 'employees_new.id')
                    ->where('employee_exit_processes.status', 'exit_completed')
                    ->limit(1),
            ]);
        }
        $query->chunkById(250, function ($employees) use ($year, $userId, &$summary) {
                $employeeIds = $employees->pluck('id')->all();
                $existingAllocations = LeaveAllocationM::query()
                    ->where('year', $year)
                    ->whereIn('employee_id', $employeeIds)
                    ->get(['employee_id', 'is_locked', 'uses_ledger', 'employment_stage'])
                    ->keyBy('employee_id');

                foreach ($employees as $employee) {
                    $summary['total_processed']++;
                    try {
                        if (! $this->isActiveForLeaveEnrollment($employee)) {
                            $summary['skipped']++;
                            continue;
                        }

                        $stage = $this->resolveEmployeeLeaveContext($employee)['stage'];
                        $ledgerMode = $stage === self::STAGE_PERMANENT;
                        if ($ledgerMode) {
                            $permanentDate = $this->resolveEmployeeLeaveContext($employee)['permanent_date'];
                            if (! $permanentDate || Carbon::parse($permanentDate, self::TIMEZONE)->year > $year) {
                                $summary['skipped']++;
                                continue;
                            }
                        }

                        if (! $ledgerMode && (bool) ($existingAllocations->get($employee->id)?->is_locked ?? false)) {
                            $summary['skipped']++;
                            continue;
                        }

                        $allocation = $stage === self::STAGE_PERMANENT
                            ? $this->enrollEmployee(
                                $employee,
                                $year > $this->now()->year ? $this->now() : ($this->now()->year === $year ? $this->now() : $this->yearEnd($year)),
                                $userId,
                                $year
                            )
                            : $this->generateForEmployee($employee, $year, $userId);
                        if ($allocation->exists) {
                            if (! $allocation->isLedgerBacked() && $year <= $this->now()->year) {
                                $throughMonth = $year === $this->now()->year ? (int) $this->now()->month : 12;
                                app(LeaveMonthlyBalanceService::class)->rebuildEmployeeThrough($employee, $year, $throughMonth);
                            }
                            $summary['successful_allocations']++;
                            if ($ledgerMode && $allocation->isLedgerBacked() && $year <= $this->now()->year) {
                                $through = $this->now()->year === $year
                                    ? $this->now()
                                    : $this->yearEnd($year);
                                $catchUp = app(MonthlyLeaveAccrualService::class)
                                    ->catchUpEmployee($employee, $through, $userId);
                                $summary['accrual_entries_posted'] += (int) ($catchUp['posted'] ?? 0);
                            }
                        } else {
                            $summary['skipped']++;
                        }
                    } catch (\DomainException $e) {
                        $summary['skipped']++;
                        $summary['skipped_employees'][] = [
                            'employee_id' => (int) $employee->id,
                            'reason' => $e->getMessage(),
                        ];
                    } catch (\Throwable $e) {
                        $summary['failed']++;
                        $summary['failed_employee_ids'][] = (int) $employee->id;
                        Log::error('Annual leave allocation failed for employee', [
                            'employee_id' => (int) $employee->id,
                            'year' => $year,
                            'stage' => $employee->employee_stage ?? $employee->employment_type ?? null,
                            'exception' => get_class($e),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $summary['duration_seconds'] = round(microtime(true) - $startedAt, 3);
        return $summary;
    }

    /** Apply administrator balance changes as auditable ledger adjustments. */
    public function updateAllocation(int $id, array $data, ?int $userId = null): LeaveAllocationM
    {
        $allocation = LeaveAllocationM::query()->findOrFail($id);
        $reason = trim((string) ($data['adjustment_reason'] ?? $data['allocation_reason'] ?? ''));
        if ($reason === '') {
            if (empty($data)) {
                throw new DomainException('Enter a reason for the manual allocation change.');
            }
            $reason = 'Manual allocation update by HR admin';
        }

        if (! $allocation->isLedgerBacked()) {
            return DB::transaction(function () use ($id, $data, $userId, $reason, $allocation): LeaveAllocationM {
                EmployeeM::query()->whereKey($allocation->employee_id)->lockForUpdate()->firstOrFail();
                $row = LeaveAllocationM::query()->lockForUpdate()->findOrFail($id);
                if ($row->is_locked && ! array_key_exists('is_locked', $data)) throw new DomainException('This leave allocation is locked.');
                if (array_key_exists('comp_off_used', $data)
                    && bccomp((string) $data['comp_off_used'], (string) ($row->comp_off_used ?? '0'), 6) !== 0) {
                    throw new DomainException('Comp-Off usage is maintained by approved leave requests and cannot be edited directly.');
                }
                $before = (string) ($row->total_remaining ?? '0');
                foreach ([
                    'year' => 'year', 'policy_id' => 'policy_id', 'employment_stage' => 'employment_stage',
                    'paid_allocated' => 'paid_allocated', 'sick_allocated' => 'sick_allocated',
                    'paid_used' => 'paid_used',
                    'sick_used' => 'sick_used', 'lwp_used' => 'lwp_used',
                    'allocation_from_date' => 'allocation_from_date', 'allocation_to_date' => 'allocation_to_date',
                    'allocation_reason' => 'allocation_reason',
                ] as $input => $column) {
                    if (array_key_exists($input, $data)) $row->{$column} = $data[$input];
                }
                if (array_key_exists('is_locked', $data)) {
                    $row->is_locked = !empty($data['is_locked']);
                }
                $this->recalculateAllocationFields($row);
                $row->save();

                $row = $this->applyCompOffAllocationTarget($row, $data, $userId);

                $after = (string) ($row->total_remaining ?? '0');
                LeaveBalanceLogM::create([
                    'employee_id' => $row->employee_id, 'leave_allocation_id' => $row->id,
                    'action' => 'allocation_manual_edit', 'credit' => max(0, (float) $after - (float) $before),
                    'debit' => max(0, (float) $before - (float) $after), 'balance_before' => $before,
                    'balance_after' => $after, 'remarks' => $reason, 'created_by_user_id' => $userId,
                ]);
                return $row;
            });
        }

        return DB::transaction(function () use ($id, $data, $reason, $userId, $allocation): LeaveAllocationM {
            EmployeeM::query()->whereKey($allocation->employee_id)->lockForUpdate()->firstOrFail();
            $locked = LeaveAllocationM::query()->lockForUpdate()->findOrFail($id);
            if ($locked->is_locked && ! array_key_exists('is_locked', $data)) {
                throw new DomainException('This leave allocation is locked.');
            }

            $before = (string) ($locked->total_remaining ?? '0');
            $balanceService = app(LeaveBalanceService::class);
            $snapshot = $balanceService->getLedgerSnapshot($locked);

            if (array_key_exists('comp_off_used', $data)
                && bccomp((string) $data['comp_off_used'], (string) ($locked->comp_off_used ?? '0'), 6) !== 0) {
                throw new DomainException('Comp-Off usage is maintained by approved leave requests and cannot be edited directly.');
            }

            $locked = $this->applyCompOffAllocationTarget($locked, $data, $userId);
            $snapshot = $balanceService->getLedgerSnapshot($locked);

            // 1. Update editable metadata fields if provided
            foreach ([
                'year', 'policy_id', 'employment_stage', 'allocation_from_date', 'allocation_to_date',
                'allocation_reason',
            ] as $column) {
                if (array_key_exists($column, $data) && $data[$column] !== null) {
                    $locked->{$column} = $data[$column];
                }
            }
            if (array_key_exists('is_locked', $data)) {
                $locked->is_locked = !empty($data['is_locked']);
            }

            $baselineLwpUsed = (string) ($locked->lwp_used ?? '0');
            $submittedLwpUsed = array_key_exists('lwp_used', $data) && $data['lwp_used'] !== null && $data['lwp_used'] !== ''
                ? (string) $data['lwp_used']
                : $baselineLwpUsed;
            $accumulatedLwpShortfall = '0';

            $locked->save();

            $movement = app(LeaveBalanceMovementService::class);
            $asOf = Carbon::now(self::TIMEZONE)->toDateString();
            $formUuid = (string) ($data['adjustment_uuid'] ?? (string) Str::uuid());

            $baselinePl = (string) ($snapshot[LeaveBucket::PL->value]['available'] ?? '0');
            $currentPl = $baselinePl;
            $currentPlUsed = (string) ($snapshot[LeaveBucket::PL->value]['used'] ?? $locked->paid_used ?? '0');
            $baselineSl = (string) ($snapshot[LeaveBucket::SL->value]['available'] ?? '0');
            $currentSl = $baselineSl;
            $currentSlUsed = (string) ($snapshot[LeaveBucket::SL->value]['used'] ?? $locked->sick_used ?? '0');

            // Determine if user entered a custom reason or if it's default/auto-generated
            $rawReason = trim((string) ($data['adjustment_reason'] ?? $data['allocation_reason'] ?? ''));
            $isAutoGenerated = str_starts_with($rawReason, 'Manual adjustment:') || str_starts_with($rawReason, 'Manual balance adjustment:');
            $isAnnualDefault = str_starts_with(strtolower($rawReason), 'annual allocation');
            $userCustomReason = ($rawReason !== '' && ! $isAutoGenerated && ! $isAnnualDefault) ? $rawReason : null;

            $unitPolicy = app(LeaveUnitPolicy::class);
            $effectivePolicy = $locked->policy ?? (
                $locked->employee ? $this->policyService->requireForEmployee($locked->employee, Carbon::parse($asOf, self::TIMEZONE)) : null
            );

            $deltaPlUsed = '0';
            // 2. Process manual used leaves adjustment with Smart Auto-Spillover
            if (array_key_exists('paid_used', $data) && $data['paid_used'] !== null && $data['paid_used'] !== '') {
                $submittedPlUsed = (string) $data['paid_used'];
                $rawDeltaPlUsed = bcsub(bcadd($submittedPlUsed, '0', 6), bcadd($currentPlUsed, '0', 6), 6);
                $deltaPlUsed = $rawDeltaPlUsed;
                if (bccomp($rawDeltaPlUsed, '0', 6) !== 0) {
                    if (bccomp($rawDeltaPlUsed, '0', 6) > 0) {
                        // Deduction: check if PL has enough usable balance
                        $usablePl = $effectivePolicy ? $unitPolicy->usable($currentPl, $effectivePolicy) : $currentPl;
                        if (bccomp($usablePl, '0', 6) < 0) $usablePl = '0.000000';

                        if (bccomp($rawDeltaPlUsed, $usablePl, 6) > 0) {
                            // Smart Spillover: PL absorbs what it can, remainder spills to SL then LWP
                            $takeFromPl = $usablePl;
                            $shortfall = bcsub($rawDeltaPlUsed, $takeFromPl, 6);
                            $usableSl = $effectivePolicy ? $unitPolicy->usable($currentSl, $effectivePolicy) : $currentSl;
                            if (bccomp($usableSl, '0', 6) < 0) $usableSl = '0.000000';
                            $takeFromSl = bccomp($shortfall, $usableSl, 6) <= 0 ? $shortfall : $usableSl;
                            $remainingShortfall = bcsub($shortfall, $takeFromSl, 6);

                            if (bccomp($takeFromPl, '0', 6) > 0) {
                                $plUsedReason = $userCustomReason ?? sprintf(
                                    'Manual adjustment: deducted %s day(s) PL (%s shortfall spilled to SL/LWP)',
                                    (string) (float) $takeFromPl,
                                    (string) (float) $shortfall
                                );
                                $movement->manualUsedAdjustment(
                                    $locked, LeaveBucket::PL, (int) $locked->year, $takeFromPl, $asOf,
                                    $this->bucketAdjustmentUuid($formUuid, 'PL_USED'), $plUsedReason, $userId
                                );
                            }

                            if (bccomp($takeFromSl, '0', 6) > 0) {
                                $slSpillReason = $userCustomReason ?? sprintf(
                                    'Manual adjustment: deducted %s day(s) from SL (Paid leave shortfall spillover)',
                                    (string) (float) $takeFromSl
                                );
                                $movement->manualUsedAdjustment(
                                    $locked, LeaveBucket::SL, (int) $locked->year, $takeFromSl, $asOf,
                                    $this->bucketAdjustmentUuid($formUuid, 'SL_SPILLOVER'), $slSpillReason, $userId
                                );
                            }

                            if (bccomp($remainingShortfall, '0', 6) > 0) {
                                $accumulatedLwpShortfall = bcadd($accumulatedLwpShortfall, $remainingShortfall, 6);
                            }
                        } else {
                            // Fits in PL directly
                            $plUsedReason = $userCustomReason ?? sprintf(
                                'Manual adjustment: deducted %s day(s) (Paid used %s -> %s)',
                                (string) (float) $rawDeltaPlUsed, (string) (float) $currentPlUsed, (string) (float) $submittedPlUsed
                            );
                            $movement->manualUsedAdjustment(
                                $locked, LeaveBucket::PL, (int) $locked->year, $rawDeltaPlUsed, $asOf,
                                $this->bucketAdjustmentUuid($formUuid, 'PL_USED'), $plUsedReason, $userId
                            );
                        }
                    } else {
                        // Reversal
                        $amountAbs = bcsub('0', $rawDeltaPlUsed, 6);
                        $plUsedReason = $userCustomReason ?? sprintf(
                            'Manual adjustment: reversed %s day(s) (Paid used %s -> %s)',
                            (string) (float) $amountAbs, (string) (float) $currentPlUsed, (string) (float) $submittedPlUsed
                        );
                        $movement->manualUsedAdjustment(
                            $locked, LeaveBucket::PL, (int) $locked->year, $rawDeltaPlUsed, $asOf,
                            $this->bucketAdjustmentUuid($formUuid, 'PL_USED'), $plUsedReason, $userId
                        );
                    }
                    $snapshot = $balanceService->getLedgerSnapshot($locked);
                    $currentPl = (string) ($snapshot[LeaveBucket::PL->value]['available'] ?? '0');
                    $currentSl = (string) ($snapshot[LeaveBucket::SL->value]['available'] ?? '0');
                }
            }

            $deltaSlUsed = '0';
            if (array_key_exists('sick_used', $data) && $data['sick_used'] !== null && $data['sick_used'] !== '') {
                $submittedSlUsed = (string) $data['sick_used'];
                $rawDeltaSlUsed = bcsub(bcadd($submittedSlUsed, '0', 6), bcadd($currentSlUsed, '0', 6), 6);
                $deltaSlUsed = $rawDeltaSlUsed;
                if (bccomp($rawDeltaSlUsed, '0', 6) !== 0) {
                    if (bccomp($rawDeltaSlUsed, '0', 6) > 0) {
                        // Deduction: check if SL has enough usable balance
                        $usableSl = $effectivePolicy ? $unitPolicy->usable($currentSl, $effectivePolicy) : $currentSl;
                        if (bccomp($usableSl, '0', 6) < 0) $usableSl = '0.000000';

                        if (bccomp($rawDeltaSlUsed, $usableSl, 6) > 0) {
                            
                            $takeFromSl = $usableSl;
                            $shortfall = bcsub($rawDeltaSlUsed, $takeFromSl, 6);
                            $usablePl = $effectivePolicy ? $unitPolicy->usable($currentPl, $effectivePolicy) : $currentPl;
                            if (bccomp($usablePl, '0', 6) < 0) $usablePl = '0.000000';
                            $takeFromPl = bccomp($shortfall, $usablePl, 6) <= 0 ? $shortfall : $usablePl;
                            $remainingShortfall = bcsub($shortfall, $takeFromPl, 6);

                            if (bccomp($takeFromSl, '0', 6) > 0) {
                                $slUsedReason = $userCustomReason ?? sprintf(
                                    'Manual adjustment: deducted %s day(s) SL (%s shortfall spilled to PL/LWP)',
                                    (string) (float) $takeFromSl,
                                    (string) (float) $shortfall
                                );
                                $movement->manualUsedAdjustment(
                                    $locked, LeaveBucket::SL, (int) $locked->year, $takeFromSl, $asOf,
                                    $this->bucketAdjustmentUuid($formUuid, 'SL_USED'), $slUsedReason, $userId
                                );
                            }

                            if (bccomp($takeFromPl, '0', 6) > 0) {
                                $plSpillReason = $userCustomReason ?? sprintf(
                                    'Manual adjustment: deducted %s day(s) from PL (Sick leave shortfall spillover)',
                                    (string) (float) $takeFromPl
                                );
                                $movement->manualUsedAdjustment(
                                    $locked, LeaveBucket::PL, (int) $locked->year, $takeFromPl, $asOf,
                                    $this->bucketAdjustmentUuid($formUuid, 'PL_SPILLOVER'), $plSpillReason, $userId
                                );
                            }

                            if (bccomp($remainingShortfall, '0', 6) > 0) {
                                $accumulatedLwpShortfall = bcadd($accumulatedLwpShortfall, $remainingShortfall, 6);
                            }
                        } else {
                            // Fits in SL directly
                            $slUsedReason = $userCustomReason ?? sprintf(
                                'Manual adjustment: deducted %s day(s) (Sick used %s -> %s)',
                                (string) (float) $rawDeltaSlUsed, (string) (float) $currentSlUsed, (string) (float) $submittedSlUsed
                            );
                            $movement->manualUsedAdjustment(
                                $locked, LeaveBucket::SL, (int) $locked->year, $rawDeltaSlUsed, $asOf,
                                $this->bucketAdjustmentUuid($formUuid, 'SL_USED'), $slUsedReason, $userId
                            );
                        }
                    } else {
                        // Reversal
                        $amountAbs = bcsub('0', $rawDeltaSlUsed, 6);
                        $slUsedReason = $userCustomReason ?? sprintf(
                            'Manual adjustment: reversed %s day(s) (Sick used %s -> %s)',
                            (string) (float) $amountAbs, (string) (float) $currentSlUsed, (string) (float) $submittedSlUsed
                        );
                        $movement->manualUsedAdjustment(
                            $locked, LeaveBucket::SL, (int) $locked->year, $rawDeltaSlUsed, $asOf,
                            $this->bucketAdjustmentUuid($formUuid, 'SL_USED'), $slUsedReason, $userId
                        );
                    }
                    $snapshot = $balanceService->getLedgerSnapshot($locked);
                    $currentPl = (string) ($snapshot[LeaveBucket::PL->value]['available'] ?? '0');
                    $currentSl = (string) ($snapshot[LeaveBucket::SL->value]['available'] ?? '0');
                }
            }

            if (bccomp($accumulatedLwpShortfall, '0', 6) > 0) {
                $finalLwpVal = max((float) $submittedLwpUsed, (float) $baselineLwpUsed + (float) $accumulatedLwpShortfall);
                $locked->lwp_used = number_format($finalLwpVal, 2, '.', '');
            } else {
                $finalLwpVal = (float) $submittedLwpUsed;
                $locked->lwp_used = number_format($finalLwpVal, 2, '.', '');
            }
            $locked->save();

            // 3. Process Available PL/SL Target adjustments ONLY if user explicitly edited targets
            $plTargetExplicit = isset($data['pl_target_edited']) ? !empty($data['pl_target_edited']) : true;
            $slTargetExplicit = isset($data['sl_target_edited']) ? !empty($data['sl_target_edited']) : true;

            $expectedPlAfterUsed = bcsub($baselinePl, $deltaPlUsed, 6);
            if ($plTargetExplicit && array_key_exists('paid_remaining', $data) && $data['paid_remaining'] !== null && $data['paid_remaining'] !== '') {
                $submittedPlRem = (string) $data['paid_remaining'];
                if (abs((float) $submittedPlRem - (float) $baselinePl) < 0.01
                    || abs((float) $submittedPlRem - (float) $expectedPlAfterUsed) < 0.01) {
                    $targetPl = $currentPl;
                } else {
                    $targetPl = $submittedPlRem;
                }
            } else {
                $targetPl = $currentPl;
            }

            $expectedSlAfterUsed = bcsub($baselineSl, $deltaSlUsed, 6);
            if ($slTargetExplicit && array_key_exists('sick_remaining', $data) && $data['sick_remaining'] !== null && $data['sick_remaining'] !== '') {
                $submittedSlRem = (string) $data['sick_remaining'];
                if (abs((float) $submittedSlRem - (float) $baselineSl) < 0.01
                    || abs((float) $submittedSlRem - (float) $expectedSlAfterUsed) < 0.01) {
                    $targetSl = $currentSl;
                } else {
                    $targetSl = $submittedSlRem;
                }
            } else {
                $targetSl = $currentSl;
            }

            $deltaPl = bcsub(bcadd($targetPl, '0', 6), bcadd($currentPl, '0', 6), 6);
            if ($plTargetExplicit && bccomp($deltaPl, '0', 6) !== 0) {
                $plTargetReason = $userCustomReason ?? sprintf('Manual balance adjustment: Available PL set to %s', (string) (float) $targetPl);
                $movement->adjustment(
                    $locked,
                    LeaveBucket::PL,
                    (int) $locked->year,
                    $deltaPl,
                    $asOf,
                    $this->bucketAdjustmentUuid($formUuid, LeaveBucket::PL->value),
                    $plTargetReason,
                    $userId
                );
            }

            $deltaSl = bcsub(bcadd($targetSl, '0', 6), bcadd($currentSl, '0', 6), 6);
            if ($slTargetExplicit && bccomp($deltaSl, '0', 6) !== 0) {
                $slTargetReason = $userCustomReason ?? sprintf('Manual balance adjustment: Available SL set to %s', (string) (float) $targetSl);
                $movement->adjustment(
                    $locked,
                    LeaveBucket::SL,
                    (int) $locked->year,
                    $deltaSl,
                    $asOf,
                    $this->bucketAdjustmentUuid($formUuid, LeaveBucket::SL->value),
                    $slTargetReason,
                    $userId
                );
            }

            $locked = $movement->refreshSnapshot($locked);
            if (isset($finalLwpVal) && bccomp((string) ($locked->lwp_used ?? '0'), (string) $finalLwpVal, 2) !== 0) {
                $locked->lwp_used = number_format($finalLwpVal, 2, '.', '');
                $locked->save();
            }
            app(LeaveMonthlyBalanceService::class)->rebuildEmployeeThrough(
                $locked->employee,
                (int) $locked->year,
                (int) Carbon::now(self::TIMEZONE)->month
            );

            // 5. Write to balance audit log
            $after = (string) $locked->total_remaining;
            LeaveBalanceLogM::create([
                'employee_id' => $locked->employee_id,
                'leave_allocation_id' => $locked->id,
                'action' => 'allocation_manual_edit',
                'credit' => max(0, (float) $after - (float) $before),
                'debit' => max(0, (float) $before - (float) $after),
                'balance_before' => $before,
                'balance_after' => $after,
                'remarks' => $reason,
                'created_by_user_id' => $userId,
            ]);

            return $locked->fresh();
        });
    }

    private function applyCompOffAllocationTarget(LeaveAllocationM $allocation, array $data, ?int $userId): LeaveAllocationM
    {
        if (! array_key_exists('comp_off_allocated', $data)
            || $data['comp_off_allocated'] === null
            || $data['comp_off_allocated'] === '') {
            return $allocation;
        }

        $target = bcadd((string) $data['comp_off_allocated'], '0', 6);
        $delta = bcsub($target, (string) ($allocation->comp_off_allocated ?? '0'), 6);
        if (bccomp($delta, '0.000000', 6) === 0) {
            return $allocation;
        }

        $reason = trim((string) ($data['comp_off_reason'] ?? ''));
        $idempotencyKey = (string) ($data['comp_off_grant_uuid'] ?? $data['adjustment_uuid'] ?? '');
        if ($reason === '' || ! $userId) {
            throw new DomainException('Enter a Comp-Off reason before changing the allocation.');
        }

        $absoluteDelta = bccomp($delta, '0.000000', 6) > 0 ? $delta : bcsub('0.000000', $delta, 6);
        $amount = bcadd($absoluteDelta, '0', 2);
        if (bccomp($amount, $absoluteDelta, 6) !== 0) {
            throw new DomainException('Comp-Off changes support at most two decimal places.');
        }

        $employee = $allocation->employee ?: EmployeeM::query()->findOrFail($allocation->employee_id);
        $compOffService = app(CompOffService::class);
        if (bccomp($delta, '0.000000', 6) > 0) {
            $compOffService->grantManualCompOff(
                $employee,
                $amount,
                Carbon::today(self::TIMEZONE),
                $reason,
                $userId,
                $idempotencyKey
            );
        } else {
            $compOffService->reduceManualCompOff(
                $employee,
                $amount,
                Carbon::today(self::TIMEZONE),
                $reason,
                $userId,
                $idempotencyKey
            );
        }

        return $allocation->fresh();
    }

    private function bucketAdjustmentUuid(string $formUuid, string $bucket): string
    {
        $hex = substr(hash('sha256', strtolower($formUuid) . ':' . $bucket), 0, 32);
        $hex[12] = '5';
        $hex[16] = dechex((hexdec($hex[16]) & 0x3) | 0x8);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
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
        $allocation = LeaveAllocationM::query()->where('employee_id', $employee->id)
            ->where('year', $year)->first();
        if (! $allocation?->isLedgerBacked()) {
            return [
                'total_allocated' => 0.0, 'total_remaining' => 0.0,
                'paid_allocated' => 0.0, 'sick_allocated' => 0.0,
                'comp_off_remaining' => 0.0, 'lwp_used' => 0.0,
            ];
        }
        $balance = app(\App\Services\HRMS\Leave\LeaveBalanceService::class)->getCentralBalance($employee, Carbon::create($year, 1, 1, 0, 0, 0, self::TIMEZONE));

        return [
            'total_allocated' => (float) $balance['total_allocated'],
            'total_remaining' => (float) ($balance['paid_remaining'] + $balance['sick_remaining']),
            'paid_allocated' => (float) $balance['paid_allocated'],
            'sick_allocated' => (float) $balance['sick_allocated'],
            'comp_off_remaining' => (float) $balance['comp_off_remaining'],
            'lwp_used' => (float) $balance['leave_balance']['total_lwp'],
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
        $stage = $this->resolveEmployeeLeaveContext($employee)['stage'];
        if ($stage !== self::STAGE_PERMANENT) {
            $stageAllocation = LeaveAllocationM::query()
                ->where('employee_id', $employee->id)
                ->where('employment_stage', $stage)
                ->orderByDesc('year')
                ->first();
            if ($stageAllocation) {
                return $stageAllocation;
            }
            return $this->generateForEmployee($employee, $year, $userId, $stage);
        }

        $allocation = LeaveAllocationM::query()
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->first();

        if ($allocation) {
            return $allocation;
        }

        $previousAllocation = LeaveAllocationM::query()
            ->where('employee_id', $employee->id)
            ->where('year', $year - 1)
            ->first();
        if ($previousAllocation?->isLedgerBacked()) {
            return new LeaveAllocationM([
                'employee_id' => $employee->id,
                'year' => $year,
                'employment_stage' => self::STAGE_PERMANENT,
                'uses_ledger' => true,
                'paid_allocated' => '0.00', 'sick_allocated' => '0.00',
                'total_allocated' => '0.00', 'paid_used' => '0.00', 'sick_used' => '0.00',
                'paid_remaining' => '0.00', 'sick_remaining' => '0.00', 'total_remaining' => '0.00',
            ]);
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
            if ($allocation->isLedgerBacked()) {
                return $allocation;
            }

            $policy = $this->policyForEmployee(
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
     * Recalculate derived fields (remaining balances and totals)
     * on an existing allocation without modifying base allocated quotas or usage.
     */
    public function recalculateAllocationFields(
        LeaveAllocationM $allocation,
        ?LeavePolicyM $policy = null,
        ?EmployeeM $employee = null
    ): LeaveAllocationM {
        if ($allocation->isLedgerBacked()) {
            return $allocation;
        }

        $policy ??= $this->resolvePolicyForAllocation($allocation);
        $this->validatePolicy($policy);

        $employee ??= $this->resolveEmployeeForAllocation($allocation);
        $rawStage = trim((string) ($allocation->employment_stage ?? $employee?->employee_stage ?? $employee?->employment_type ?? self::STAGE_PERMANENT));
        if ($rawStage === '') {
            $rawStage = self::STAGE_PERMANENT;
        }
        $stage = $this->normalizeStage($rawStage);

        if ($stage === self::STAGE_PERMANENT) {
            return $allocation;
        }

        if ($stage !== self::STAGE_PERMANENT) {
            $unpaidIntern = $stage === self::STAGE_INTERNSHIP && $this->isUnpaidIntern($allocation, $employee);
            $stageLimit = $stage === self::STAGE_PROBATION
                ? (string) $policy->probation_leave_limit
                : ($unpaidIntern ? (string) $policy->unpaid_intern_leave_limit : (string) $policy->internship_leave_limit);
            $stageLimit = bcadd($stageLimit, '0', 6);
            $allocation->paid_allocated = $unpaidIntern ? '0.000000' : $stageLimit;
            $allocation->sick_allocated = '0.000000';
            $allocation->total_allocated = $stageLimit;
            $paidRemaining = $unpaidIntern ? '0.000000' : bcsub($stageLimit, (string) $allocation->paid_used, 6);
            $allocation->paid_remaining = bccomp($paidRemaining, '0.000000', 6) < 0 ? '0.000000' : $paidRemaining;
            $allocation->sick_remaining = '0.000000';
            $compOffRemaining = bcsub((string) ($allocation->comp_off_allocated ?? '0'), (string) ($allocation->comp_off_used ?? '0'), 6);
            $allocation->comp_off_remaining = bccomp($compOffRemaining, '0.000000', 6) < 0 ? '0.000000' : $compOffRemaining;
            $totalUsed = bcadd((string) $allocation->paid_used, (string) $allocation->sick_used, 6);
            $totalUsed = bcadd($totalUsed, (string) $allocation->comp_off_used, 6);
            $allocation->total_used = bcadd($totalUsed, (string) $allocation->lwp_used, 6);
            $totalRemaining = bcadd((string) $allocation->paid_remaining, (string) $allocation->comp_off_remaining, 6);
            if ($unpaidIntern) {
                $unpaidRemaining = bcsub($stageLimit, (string) $allocation->lwp_used, 6);
                $totalRemaining = bcadd($totalRemaining, bccomp($unpaidRemaining, '0.000000', 6) < 0 ? '0.000000' : $unpaidRemaining, 6);
            }
            $allocation->total_remaining = $totalRemaining;
            return $allocation;
        }

        if ($stage === self::STAGE_INTERNSHIP) {
            $this->recalculateInternshipAllocation(
                $allocation,
                $policy,
                $employee
            );
        } elseif ($stage === self::STAGE_PROBATION) {
            $this->recalculateStandardAllocation($allocation);
        } else {
            $this->recalculatePermanentAllocation($allocation);
        }

        return $allocation;
    }

    /** Apply audited counter changes to a non-ledger allocation under its row lock. */
    public function adjustLegacyAllocation(
        LeaveAllocationM $allocation,
        array $deltas,
        ?int $actorId,
        string $action,
        string $reason,
        ?int $requestId = null,
        ?int $leaveTypeId = null
    ): LeaveAllocationM {
        $allowed = ['paid_used', 'sick_used', 'comp_off_used', 'comp_off_allocated', 'lwp_used'];
        foreach ($deltas as $column => $delta) {
            if (! in_array($column, $allowed, true) || ! is_string($delta)
                || ! preg_match('/^-?\d+(?:\.\d{1,6})?$/', $delta)) {
                throw new DomainException("Invalid legacy leave balance delta for {$column}.");
            }
        }

        return DB::transaction(function () use ($allocation, $deltas, $actorId, $action, $reason, $requestId, $leaveTypeId): LeaveAllocationM {
            $locked = LeaveAllocationM::query()->lockForUpdate()->findOrFail($allocation->id);
            if ($locked->isLedgerBacked()) {
                throw new DomainException('Ledger-backed allocation snapshots must be changed through LeaveBalanceMovementService.');
            }
            if ($locked->is_locked) {
                throw new DomainException('This leave allocation is locked.');
            }

            $before = bcadd((string) ($locked->total_remaining ?? '0'), '0', 6);
            foreach ($deltas as $column => $delta) {
                $next = bcadd((string) ($locked->{$column} ?? '0'), $delta, 6);
                if (bccomp($next, '0', 6) < 0) {
                    throw new DomainException("{$column} cannot be reduced below zero.");
                }
                $locked->{$column} = $next;
            }

            $this->recalculateAllocationFields($locked);
            $locked->save();
            $after = bcadd((string) ($locked->total_remaining ?? '0'), '0', 6);
            $change = bcsub($after, $before, 6);

            LeaveBalanceLogM::query()->create([
                'employee_id' => $locked->employee_id,
                'leave_allocation_id' => $locked->id,
                'leave_request_id' => $requestId,
                'leave_type_id' => $leaveTypeId,
                'action' => $action,
                'credit' => bccomp($change, '0', 6) > 0 ? $change : '0.000000',
                'debit' => bccomp($change, '0', 6) < 0 ? bcsub('0', $change, 6) : '0.000000',
                'balance_before' => $before,
                'balance_after' => $after,
                'remarks' => $reason,
                'created_by_user_id' => $actorId,
            ]);

            return $locked->fresh();
        });
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
        $limit = max(0.0, (float) $policy->probation_leave_limit);

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
        $limit = max(0.0, (float) ($isPaidIntern ? $policy->internship_leave_limit : $policy->unpaid_intern_leave_limit));

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

        $paid = round(((float) $policy->annual_paid_leaves / 12.0) * $remainingMonths, 2);
        $sick = round(((float) $policy->annual_sick_leaves / 12.0) * $remainingMonths, 2);
        $total = round($paid + $sick, 2);

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
            max(0.0, (float) ($isUnpaid ? $policy->unpaid_intern_leave_limit : $policy->internship_leave_limit)),
            2
        );

        if ($isUnpaid) {
            $this->recalculateUnpaidInternAllocation(
                $allocation,
                $internLimit
            );

            return;
        }

        $this->recalculateStandardAllocation($allocation);
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

    }

    private function recalculateStandardAllocation(LeaveAllocationM $allocation): void
    {
        $this->recalculateBaseBalances($allocation);
    }

    private function recalculatePermanentAllocation(LeaveAllocationM $allocation): void
    {
        $this->recalculateBaseBalances($allocation);
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

    /** Resolve leave eligibility from the employee master record, never from an allocation snapshot. */
    public function resolveEmployeeLeaveContext(EmployeeM $employee): array
    {
        $stage = $this->stageFor($employee);
        $rawPermanentDate = $stage === self::STAGE_PERMANENT
            ? ($employee->confirmation_date
                ?: $employee->confirmation_effective_date
                ?: $employee->permanent_at
                ?: $employee->joining_date)
            : null;
        $permanentDate = $rawPermanentDate
            ? Carbon::parse((string) $rawPermanentDate, self::TIMEZONE)->toDateString()
            : null;

        if ($stage === self::STAGE_PERMANENT && ! $employee->confirmation_date && $permanentDate && $employee->exists) {
            $employee->confirmation_date = $permanentDate;
            $employee->saveQuietly();
        }
        $startDate = match ($stage) {
            self::STAGE_PERMANENT => $permanentDate,
            self::STAGE_INTERNSHIP => $employee->internship_start_date ?: $employee->joining_date,
            self::STAGE_PROBATION => $employee->probation_start_date ?: $employee->joining_date,
            default => null,
        };

        return [
            'stage' => $stage,
            'is_paid_intern' => $stage === self::STAGE_INTERNSHIP ? ! $this->isUnpaidInternEmployee($employee) : null,
            'permanent_date' => $permanentDate,
            'joining_date' => $employee->joining_date ? Carbon::parse((string) $employee->joining_date, self::TIMEZONE)->toDateString() : null,
            'stage_start_date' => $startDate ? Carbon::parse((string) $startDate, self::TIMEZONE)->toDateString() : null,
        ];
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

    private function isActiveForLeaveEnrollment(EmployeeM $employee): bool
    {
        return strtolower(trim((string) $employee->employment_status)) === 'active'
            && ($employee->is_active === null || (bool) $employee->is_active);
    }

    public function assertStageRequestAllowed(EmployeeM $employee, LeaveAllocationM $allocation, string $requestedDays): void
    {
        // Unpaid interns are allowed to apply leaves beyond the initial quota, which route into LWP (Leave Without Pay)
        return;
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
            $policy = $this->policyForEmployee($employee, $this->yearStart($year));

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
            self::STAGE_PERMANENT => $effectiveDate?->toDateString() ?: $this->yearStart($year)->toDateString(),

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

    /**
     * Permanent effective dates affect only the allocation in that same year.
     * A later allocation year starts on January 1 under that year's full annual policy.
     * false means the requested allocation predates the employee's permanent effective year.
     */
    private function effectiveDateForAllocationYear(
        EmployeeM $employee,
        int $year,
        ?Carbon $requestedEffectiveDate
    ): Carbon|false|null {
        $lifecycleDate = $this->resolveEmployeeLeaveContext($employee)['permanent_date'];
        $effectiveDate = $lifecycleDate
            ? Carbon::parse($lifecycleDate, self::TIMEZONE)->startOfDay()
            : $requestedEffectiveDate?->copy()->startOfDay();

        if (! $effectiveDate) {
            // Legacy permanent records without a confirmation date receive the
            // normal full annual allocation; joining_date is not a proxy here.
            return null;
        }

        if ($year < $effectiveDate->year) {
            return false;
        }

        return $year === $effectiveDate->year
            ? $effectiveDate
            : null;
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

    private function policyForEmployee(EmployeeM $employee, Carbon $date): LeavePolicyM
    {
        $policy = $this->policyService->forEmployee($employee, $date);
        if (! $policy) {
            throw new DomainException($this->policyService->lastResolutionReason() ?? 'Employee has no active leave policy.');
        }

        return $policy;
    }
}
