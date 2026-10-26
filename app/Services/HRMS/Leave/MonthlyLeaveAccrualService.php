<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use App\Enums\LeaveBucket;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceTransactionM;
use App\Services\HRMS\Leave\LeavePolicyService;

class MonthlyLeaveAccrualService
{
    public function __construct(
        private LeaveBalanceMovementService $movementService,
        private MonthlyLeaveAccrualCalculator $calculator,
        private LeaveAllocationService $allocationService,
        private LeavePolicyService $policyService,
        private LeaveMonthlyBalanceService $monthlyBalanceService
    ) {
    }

    /**
     * Post monthly ledger accruals for opted-in permanent allocations only.
     */
    public function accrueMonthlyLeaves(?Carbon $date = null, ?int $userId = null, bool $dryRun = false): array
    {
        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');

        return $this->accrueLedgerEmployees($date, $userId, $dryRun);
    }

    /** Catch up every eligible month through the target month for an opted-in permanent allocation. */
    public function catchUpEmployee(EmployeeM $employee, Carbon $through, ?int $userId = null, bool $dryRun = false): array
    {
        $through = $through->copy()->setTimezone('Asia/Kolkata');
        $currentYear = (int) $through->year;
        $current = LeaveAllocationM::query()->where('employee_id', $employee->id)->where('year', $currentYear)->first();
        if (! $current) {
            $previous = LeaveAllocationM::query()->where('employee_id', $employee->id)->where('year', $currentYear - 1)->first();
            if ($previous?->isLedgerBacked()) {
                $current = $dryRun
                    ? new LeaveAllocationM([
                        'employee_id' => $employee->id, 'year' => $currentYear,
                        'employment_stage' => 'permanent', 'uses_ledger' => true,
                        'allocation_from_date' => $this->allocationService->resolveEmployeeLeaveContext($employee)['permanent_date'],
                    ])
                    : $this->allocationService->generateForEmployee($employee, $currentYear, $userId, 'permanent');
            }
        }

        $allocations = LeaveAllocationM::query()->where('employee_id', $employee->id)
            ->where('year', '<=', $currentYear)->orderBy('year')->get()->filter(
                static fn (LeaveAllocationM $allocation): bool => $allocation->isLedgerBacked()
            );
        if ($current?->isLedgerBacked() && ! $current->exists) {
            $allocations->push($current);
        }
        if ($allocations->isEmpty()) {
            return ['posted' => 0, 'skipped' => 1, 'dry_run' => $dryRun];
        }

        $eligibilityDate = $this->allocationService->resolveEmployeeLeaveContext($employee)['permanent_date'];
        if (! $eligibilityDate) {
            return ['posted' => 0, 'skipped' => 1, 'dry_run' => $dryRun];
        }

        $eligible = Carbon::parse($eligibilityDate, 'Asia/Kolkata');
        $posted = 0;
        foreach ($allocations as $allocation) {
            $year = (int) $allocation->year;
            if ($year < (int) $eligible->year) {
                continue;
            }
            $closedBuckets = $year < $currentYear
                ? LeaveBalanceTransactionM::query()->where('leave_allocation_id', $allocation->id)
                    ->where('transaction_type', 'lapse')->pluck('bucket')->all()
                : [];

            $firstEligibleMonth = $year === (int) $eligible->year ? (int) $eligible->month : 1;
            $lastMonth = $year === $currentYear ? (int) $through->month : 12;
            $startMonth = $firstEligibleMonth;
            $cutover = $allocation->ledger_effective_from;
            if ($cutover && (int) $cutover->year === $year) {
                $startMonth = max($startMonth, (int) $cutover->month + 1);
            } elseif ($cutover && (int) $cutover->year > $year) {
                continue;
            }

            for ($month = $startMonth; $month <= $lastMonth; $month++) {
                $eligibleMonth = $month - $firstEligibleMonth + 1;
                $posted += $this->postMonth($employee, $allocation, $year, $month, $eligibleMonth, $userId, $dryRun, $closedBuckets);
            }

            if (! $dryRun) {
                $this->monthlyBalanceService->rebuildEmployeeThrough($employee, $year, $lastMonth);
            }
        }

        return ['posted' => $posted, 'skipped' => 0, 'dry_run' => $dryRun];
    }

    private function accrueLedgerEmployees(Carbon $date, ?int $userId, bool $dryRun = false): array
    {
        $summary = ['year' => (int) $date->year, 'month' => (int) $date->month,
            'processed_count' => 0, 'skipped_count' => 0, 'failed_count' => 0];
        EmployeeM::query()->where('employment_status', 'active')
            ->where(function ($query): void {
                $query->where('is_permanent', true)->orWhere('employee_stage', 'permanent')->orWhere('employment_type', 'permanent');
            })->chunkById(250, function ($employees) use ($date, $userId, $dryRun, &$summary): void {
                foreach ($employees as $employee) {
                    try {
                        $result = $this->catchUpEmployee($employee, $date, $userId, $dryRun);
                        if ($result['skipped']) {
                            $summary['skipped_count']++;
                        } else {
                            $summary['processed_count']++;
                        }
                    } catch (\Throwable $exception) {
                        $summary['failed_count']++;
                        report($exception);
                    }
                }
            });
        return $summary;
    }

    private function postMonth(EmployeeM $employee, LeaveAllocationM $allocation, int $year, int $month, int $k, ?int $userId, bool $dryRun, array $closedBuckets = []): int
    {
        $count = 0;
        $effectiveDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata');
        $policy = $this->policyService->requireForEmployee($employee, $effectiveDate);
        foreach ([[LeaveBucket::PL, (string) $policy->annual_paid_leaves], [LeaveBucket::SL, (string) $policy->annual_sick_leaves]] as [$bucket, $annual]) {
            if (in_array($bucket->value, $closedBuckets, true)) {
                continue;
            }
            $amount = $this->calculator->creditForMonth($annual, $k);
            if ($dryRun) {
                $count++;
                continue;
            }
            $this->movementService->accrue($allocation, $bucket, $year, $month, $amount, $userId, (int) $policy->id);
            $count++;
        }
        return $count;
    }

    /**
     * Legacy monthly carry-forward lapse is retired; ledger-backed lapse uses the dedicated command.
     */
    public function processYearEndLapse(int $year, ?int $userId = null): array
    {
        return ['year' => $year, 'processed_count' => 0, 'skipped_count' => 0, 'retired' => true];
    }
}
