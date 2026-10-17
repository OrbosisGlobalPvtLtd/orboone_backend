<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeaveBalanceTransactionM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Models\HRMS\Leave\LeaveTypeM;
use Carbon\Carbon;
use App\Enums\LeaveBucket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    /** @var array<string, array<int, array{PL: string, SL: string}>> */
    private array $permanentEntitlementSchedules = [];

    public function __construct(
        private LeaveAllocationService $allocationService,
        private LeavePolicyService $policyService,
        private MonthlyLeaveAccrualCalculator $accrualCalculator
    ) {
    }

    /**
     * Return permanent entitlement through a calendar month for a ledger-backed allocation.
     * This is separate from the ledger balance, which contains only posted transactions.
     *
     * @return array{PL: string, SL: string}
     */
    public function getPermanentEntitlement(LeaveAllocationM $allocation, int $throughMonth): array
    {
        if ($throughMonth < 0 || $throughMonth > 12) {
            throw new \InvalidArgumentException('Entitlement month must be between 0 and 12.');
        }

        if ($throughMonth === 0) {
            return ['PL' => '0.000000', 'SL' => '0.000000'];
        }

        $employee = $allocation->employee;
        if (! $employee) {
            return [
                'PL' => (string) ($allocation->paid_allocated ?? '0.000000'),
                'SL' => (string) ($allocation->sick_allocated ?? '0.000000'),
            ];
        }

        $permanentDate = $this->allocationService->resolveEmployeeLeaveContext($employee)['permanent_date'];

        if (! $permanentDate) {
            return [
                'PL' => (string) ($allocation->paid_allocated ?? '0.000000'),
                'SL' => (string) ($allocation->sick_allocated ?? '0.000000'),
            ];
        }

        $year = (int) $allocation->year;
        $start = Carbon::parse($permanentDate, 'Asia/Kolkata');
        if ((int) $start->year > $year) {
            return ['PL' => '0.000000', 'SL' => '0.000000'];
        }

        $cacheKey = $allocation->employee_id . ':' . $year . ':' . $start->toDateString();
        $firstMonth = (int) $start->year === $year ? (int) $start->month : 1;
        if (! isset($this->permanentEntitlementSchedules[$cacheKey])) {
            $entitlement = ['PL' => '0.000000', 'SL' => '0.000000'];
            $schedule = [];

            for ($month = $firstMonth; $month <= 12; $month++) {
                $effectiveDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata');
                $policy = $this->policyService->forEmployee($employee, $effectiveDate);
                if (! $policy) {
                    return [
                        'PL' => (string) ($allocation->paid_allocated ?? '0.000000'),
                        'SL' => (string) ($allocation->sick_allocated ?? '0.000000'),
                    ];
                }

                $eligibleMonth = $month - $firstMonth + 1;
                $entitlement['PL'] = bcadd(
                    $entitlement['PL'],
                    $this->accrualCalculator->creditForMonth((string) $policy->annual_paid_leaves, $eligibleMonth),
                    6
                );
                $entitlement['SL'] = bcadd(
                    $entitlement['SL'],
                    $this->accrualCalculator->creditForMonth((string) $policy->annual_sick_leaves, $eligibleMonth),
                    6
                );
                $schedule[$month] = $entitlement;
            }

            $this->permanentEntitlementSchedules[$cacheKey] = $schedule;
        }

        return $this->permanentEntitlementSchedules[$cacheKey][$throughMonth]
            ?? ['PL' => '0.000000', 'SL' => '0.000000'];
    }

    /** @return array{PL: array{available: string, used: string}, SL: array{available: string, used: string}} */
    public function getLedgerSnapshot(LeaveAllocationM $allocation): array
    {
        $empty = [
            'PL' => ['available' => '0.000000', 'used' => '0.000000'],
            'SL' => ['available' => '0.000000', 'used' => '0.000000'],
        ];
        if (! $allocation->exists) {
            return $empty;
        }

        $rows = LeaveBalanceTransactionM::query()
            ->where('leave_allocation_id', $allocation->id)
            ->where('employee_id', $allocation->employee_id)
            ->where('period_year', $allocation->year)
            ->selectRaw('bucket, COALESCE(SUM(amount), 0) AS available')
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type IN ('leave_debit', 'reversal') THEN amount ELSE 0 END), 0) AS net_debits")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        foreach (LeaveBucket::cases() as $bucket) {
            $row = $rows->get($bucket->value);
            if (! $row) {
                continue;
            }
            $netDebits = (string) $row->net_debits;
            $empty[$bucket->value] = [
                'available' => (string) $row->available,
                'used' => bccomp($netDebits, '0', 6) < 0 ? bcsub('0', $netDebits, 6) : '0.000000',
            ];
        }

        return $empty;
    }

    /**
     * Build display totals for opted-in allocations without changing legacy rows.
     * Historical approved usage before cutover is read from leave_request_dates;
     * usage after cutover comes from the ledger to avoid counting it twice.
     *
     * @return array<int, array<string, string>> keyed by allocation id
     */
    public function getAllocationDisplaySummaries(Collection $allocations, ?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? Carbon::now('Asia/Kolkata'))->copy()->setTimezone('Asia/Kolkata');
        $ledgerAllocations = $allocations->filter(
            static fn (LeaveAllocationM $allocation): bool => $allocation->isLedgerBacked()
        )->values();
        if ($ledgerAllocations->isEmpty()) {
            return [];
        }

        $allocationIds = $ledgerAllocations->pluck('id')->all();
        $movements = LeaveBalanceTransactionM::query()
            ->whereIn('leave_allocation_id', $allocationIds)
            ->selectRaw('leave_allocation_id, bucket, COALESCE(SUM(amount), 0) AS available')
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type IN ('leave_debit', 'reversal') THEN amount ELSE 0 END), 0) AS net_used")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'monthly_accrual' THEN amount ELSE 0 END), 0) AS accrued")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'opening_balance' THEN amount ELSE 0 END), 0) AS opening")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'monthly_accrual' AND period_month = ? THEN amount ELSE 0 END), 0) AS current_month_accrued", [$asOf->month])
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type IN ('leave_debit', 'reversal') AND period_month = ? THEN amount ELSE 0 END), 0) AS current_month_net_used", [$asOf->month])
            ->groupBy('leave_allocation_id', 'bucket')
            ->get()
            ->groupBy('leave_allocation_id');

        $preCutoverUsage = DB::table('leave_allocations as a')
            ->join('leave_request_dates as d', function ($join): void {
                $join->on('d.employee_id', '=', 'a.employee_id')
                    ->whereRaw('YEAR(d.leave_date) = a.year');
            })
            ->join('leave_requests as r', 'r.id', '=', 'd.leave_request_id')
            ->whereIn('a.id', $allocationIds)
            ->whereNotNull('a.ledger_effective_from')
            ->where('r.status', 'approved')
            ->whereRaw('DATE(d.leave_date) <= a.ledger_effective_from')
            ->whereRaw("DATE(d.leave_date) >= STR_TO_DATE(CONCAT(a.year, '-01-01'), '%Y-%m-%d')")
            ->selectRaw('a.id AS allocation_id')
            ->selectRaw("SUM(CASE WHEN d.paid_day > 0 AND NOT EXISTS (SELECT 1 FROM leave_balance_transactions t WHERE t.leave_allocation_id = a.id AND t.leave_request_id = r.id AND t.bucket = 'PL' AND t.transaction_type = 'leave_debit') THEN d.paid_day ELSE 0 END) AS pl_used")
            ->selectRaw("SUM(CASE WHEN d.sick_day > 0 AND NOT EXISTS (SELECT 1 FROM leave_balance_transactions t WHERE t.leave_allocation_id = a.id AND t.leave_request_id = r.id AND t.bucket = 'SL' AND t.transaction_type = 'leave_debit') THEN d.sick_day ELSE 0 END) AS sl_used")
            ->groupBy('a.id')
            ->get()
            ->keyBy('allocation_id');

        $carryMovements = collect();
        $priorMonthUsage = collect();
        foreach ($ledgerAllocations->groupBy('year') as $allocationYear => $yearAllocations) {
            $allocationYear = (int) $allocationYear;
            $periodMonth = $allocationYear < (int) $asOf->year
                ? 12
                : ($allocationYear === (int) $asOf->year ? (int) $asOf->month : 1);
            if ($periodMonth <= 1) {
                continue;
            }

            $cutoff = Carbon::create($allocationYear, $periodMonth, 1, 0, 0, 0, 'Asia/Kolkata')->toDateString();
            $yearAllocationIds = $yearAllocations->pluck('id')->all();
            $carryMovements = $carryMovements->merge(
                LeaveBalanceTransactionM::query()
                    ->whereIn('leave_allocation_id', $yearAllocationIds)
                    ->whereDate('effective_date', '<', $cutoff)
                    ->selectRaw('leave_allocation_id, bucket, SUM(amount) AS balance')
                    ->groupBy('leave_allocation_id', 'bucket')
                    ->get()
                    ->mapWithKeys(static fn ($row) => [
                        $row->leave_allocation_id . ':' . $row->bucket => (string) $row->balance,
                    ])
            );

            $priorMonthUsage = $priorMonthUsage->merge(
                DB::table('leave_allocations as a')
                    ->join('leave_request_dates as d', function ($join): void {
                        $join->on('d.employee_id', '=', 'a.employee_id')
                            ->whereRaw('YEAR(d.leave_date) = a.year');
                    })
                    ->join('leave_requests as r', 'r.id', '=', 'd.leave_request_id')
                    ->whereIn('a.id', $yearAllocationIds)
                    ->where('r.status', 'approved')
                    ->whereRaw("DATE(d.leave_date) >= STR_TO_DATE(CONCAT(a.year, '-01-01'), '%Y-%m-%d')")
                    ->whereDate('d.leave_date', '<', $cutoff)
                    ->selectRaw('a.id AS allocation_id')
                    ->selectRaw('COALESCE(SUM(d.paid_day), 0) AS pl_used')
                    ->selectRaw('COALESCE(SUM(d.sick_day), 0) AS sl_used')
                    ->groupBy('a.id')
                    ->get()
                    ->mapWithKeys(static fn ($row) => ['allocation:' . $row->allocation_id => $row])
            );
        }

        $effectivePoliciesByYear = [];
        foreach ($ledgerAllocations->groupBy('year') as $allocationYear => $yearAllocations) {
            $policyDate = (int) $allocationYear < (int) $asOf->year
                ? Carbon::create((int) $allocationYear, 12, 31, 23, 59, 59, 'Asia/Kolkata')
                : ((int) $allocationYear > (int) $asOf->year
                    ? Carbon::create((int) $allocationYear, 1, 1, 0, 0, 0, 'Asia/Kolkata')
                    : Carbon::create($asOf->year, $asOf->month, 1, 0, 0, 0, 'Asia/Kolkata'));
            $effectivePoliciesByYear[(int) $allocationYear] = $this->effectivePoliciesForAllocations($yearAllocations, $policyDate);
        }
        $summaries = [];
        foreach ($ledgerAllocations as $allocation) {
            $year = (int) $allocation->year;
            $asOfForYear = $year < (int) $asOf->year
                ? Carbon::create($year, 12, 31, 23, 59, 59, 'Asia/Kolkata')
                : ($year > (int) $asOf->year
                    ? Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata')->subDay()
                    : $asOf);
            $lastMonth = $asOfForYear->year === $year ? (int) $asOfForYear->month : 0;
            $earnedToDate = $this->getPermanentEntitlement($allocation, $lastMonth);
            $yearEndEntitlement = $this->getPermanentEntitlement($allocation, 12);
            $plMovement = $movements->get($allocation->id, collect())->firstWhere('bucket', LeaveBucket::PL->value);
            $slMovement = $movements->get($allocation->id, collect())->firstWhere('bucket', LeaveBucket::SL->value);
            $plAccrued = (string) ($plMovement->accrued ?? '0.000000');
            $slAccrued = (string) ($slMovement->accrued ?? '0.000000');
            $legacyUsage = $preCutoverUsage->get($allocation->id);
            $plUsed = bcadd($this->netUsed($plMovement), (string) ($legacyUsage->pl_used ?? '0'), 6);
            $slUsed = bcadd($this->netUsed($slMovement), (string) ($legacyUsage->sl_used ?? '0'), 6);
            $plAvailable = (string) ($plMovement->available ?? '0.000000');
            $slAvailable = (string) ($slMovement->available ?? '0.000000');
            $unitPolicy = app(LeaveUnitPolicy::class);
            $currentPolicy = $effectivePoliciesByYear[$year][$allocation->employee_id] ?? null;
            $plUsable = $currentPolicy ? $unitPolicy->usable($plAvailable, $currentPolicy) : '0.000000';
            $slUsable = $currentPolicy ? $unitPolicy->usable($slAvailable, $currentPolicy) : '0.000000';
            $compOffAvailable = (string) ($allocation->comp_off_remaining ?? '0');
            $plMonthly = (string) ($plMovement->current_month_accrued ?? '0.000000');
            $slMonthly = (string) ($slMovement->current_month_accrued ?? '0.000000');
            $plMonthDebit = (string) ($plMovement->current_month_net_used ?? '0.000000');
            $slMonthDebit = (string) ($slMovement->current_month_net_used ?? '0.000000');
            $plMonthUsed = bccomp($plMonthDebit, '0', 6) < 0 ? bcsub('0', $plMonthDebit, 6) : '0.000000';
            $slMonthUsed = bccomp($slMonthDebit, '0', 6) < 0 ? bcsub('0', $slMonthDebit, 6) : '0.000000';

            $priorUsage = $priorMonthUsage->get('allocation:' . $allocation->id);
            $plCarryForward = $carryMovements->get($allocation->id . ':' . LeaveBucket::PL->value)
                ?? $this->nonNegative(bcsub('0.000000', (string) ($priorUsage->pl_used ?? '0'), 6));
            $slCarryForward = $carryMovements->get($allocation->id . ':' . LeaveBucket::SL->value)
                ?? $this->nonNegative(bcsub('0.000000', (string) ($priorUsage->sl_used ?? '0'), 6));

            $yearEndPlRem = $this->nonNegative(bcsub($yearEndEntitlement['PL'], $plUsed, 6));
            $yearEndSlRem = $this->nonNegative(bcsub($yearEndEntitlement['SL'], $slUsed, 6));
            $yearEndTotalRem = bcadd(bcadd($yearEndPlRem, $yearEndSlRem, 6), $compOffAvailable, 6);

            $summaries[$allocation->id] = [
                'pl_accrued' => $plAccrued,
                'sl_accrued' => $slAccrued,
                'pl_earned_to_date' => $earnedToDate['PL'],
                'sl_earned_to_date' => $earnedToDate['SL'],
                'total_earned_to_date' => bcadd($earnedToDate['PL'], $earnedToDate['SL'], 6),
                'year_end_pl' => $yearEndEntitlement['PL'],
                'year_end_sl' => $yearEndEntitlement['SL'],
                'year_end_total' => bcadd($yearEndEntitlement['PL'], $yearEndEntitlement['SL'], 6),
                'year_end_pl_remaining' => $yearEndPlRem,
                'year_end_sl_remaining' => $yearEndSlRem,
                'year_end_total_remaining' => $yearEndTotalRem,
                'pl_opening' => (string) ($plMovement->opening ?? '0.000000'),
                'sl_opening' => (string) ($slMovement->opening ?? '0.000000'),
                'total_accrued' => bcadd($plAccrued, $slAccrued, 6),
                'pl_used' => $plUsed,
                'sl_used' => $slUsed,
                'total_paid_used' => bcadd($plUsed, $slUsed, 6),
                'total_used' => bcadd(
                    bcadd($plUsed, $slUsed, 6),
                    bcadd((string) ($allocation->comp_off_used ?? '0'), (string) ($allocation->lwp_used ?? '0'), 6),
                    6
                ),
                'pl_available' => $plAvailable,
                'sl_available' => $slAvailable,
                'total_paid_available' => bcadd($plAvailable, $slAvailable, 6),
                'total_available' => bcadd(bcadd($plAvailable, $slAvailable, 6), $compOffAvailable, 6),
                'pl_usable' => $plUsable,
                'sl_usable' => $slUsable,
                'total_usable' => bcadd($plUsable, $slUsable, 6),
                'pl_monthly' => $plMonthly,
                'sl_monthly' => $slMonthly,
                'total_monthly' => bcadd($plMonthly, $slMonthly, 6),
                'pl_current_month_used' => $plMonthUsed,
                'sl_current_month_used' => $slMonthUsed,
                'total_current_month_used' => bcadd($plMonthUsed, $slMonthUsed, 6),
                'total_current_month_credit' => bcadd($plMonthly, $slMonthly, 6),
                'pl_carry_forward' => $plCarryForward,
                'sl_carry_forward' => $slCarryForward,
                'total_carry_forward' => bcadd($plCarryForward, $slCarryForward, 6),
                'period_label' => $asOfForYear->format('M Y'),
                'as_of' => $asOfForYear->toDateString(),
                'annual_pl' => (string) ($currentPolicy?->annual_paid_leaves ?? ''),
                'annual_sl' => (string) ($currentPolicy?->annual_sick_leaves ?? ''),
                'annual_entitlement_pl' => (string) ($currentPolicy?->annual_paid_leaves ?? ''),
                'annual_entitlement_sl' => (string) ($currentPolicy?->annual_sick_leaves ?? ''),
                'accrued_to_date_pl' => $plAccrued,
                'accrued_to_date_sl' => $slAccrued,
                'opening_balance_pl' => (string) ($plMovement->opening ?? '0.000000'),
                'opening_balance_sl' => (string) ($slMovement->opening ?? '0.000000'),
                'year_end_carry_forward' => '0.00',
                'lapse_date_label' => $currentPolicy
                    && (int) $currentPolicy->leave_lapse_month >= 1 && (int) $currentPolicy->leave_lapse_month <= 12
                    && (int) $currentPolicy->leave_lapse_day >= 1 && (int) $currentPolicy->leave_lapse_day <= 31
                    ? Carbon::create($year, (int) $currentPolicy->leave_lapse_month, (int) $currentPolicy->leave_lapse_day, 0, 0, 0, 'Asia/Kolkata')->format('d M')
                    : 'not configured',
            ];
        }

        return $summaries;
    }

    /** Resolve the policy effective for all listed employees in bounded queries. */
    private function effectivePoliciesForAllocations(Collection $allocations, Carbon $date): array
    {
        $employees = $allocations->mapWithKeys(static fn (LeaveAllocationM $allocation): array => [
            (int) $allocation->employee_id => $allocation->employee,
        ])->filter();
        if ($employees->isEmpty()) return [];
        return $this->policyService->forEmployees($employees, $date)->all();
    }

    private function netUsed(?object $movement): string
    {
        $netUsed = (string) ($movement->net_used ?? '0');
        return bccomp($netUsed, '0', 6) < 0 ? bcsub('0', $netUsed, 6) : '0.000000';
    }

    private function nonNegative(string $amount): string
    {
        return bccomp($amount, '0', 6) < 0 ? '0.000000' : $amount;
    }

    public function getLedgerBucketBalance(LeaveAllocationM $allocation, LeaveBucket $bucket): string
    {
        return $this->getLedgerSnapshot($allocation)[$bucket->value]['available'];
    }

    public function ledgerBackedAllocation(EmployeeM $employee, ?Carbon $date = null): ?LeaveAllocationM
    {
        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $allocation = LeaveAllocationM::query()
            ->where('employee_id', $employee->id)
            ->where('year', $date->year)
            ->first();
        return $allocation?->isLedgerBacked() ? $allocation : null;
    }

    /** Additional ledger contract fields; legacy fields remain shaped by each endpoint. */
    public function getLedgerApiBalances(EmployeeM $employee, ?Carbon $date = null): ?array
    {
        return $this->getLedgerApiBalancesForAllocation($employee, $this->ledgerBackedAllocation($employee, $date), $date);
    }

    private function getLedgerApiBalancesForAllocation(
        EmployeeM $employee,
        ?LeaveAllocationM $allocation,
        ?Carbon $date = null,
        ?array $snapshot = null
    ): ?array {
        if (! $allocation) {
            return null;
        }
        $snapshot ??= $this->getLedgerSnapshot($allocation);
        $unitPolicy = app(LeaveUnitPolicy::class);
        $policy = $this->policyService->requireForEmployee($employee, $date ?? Carbon::now('Asia/Kolkata'));
        $pl = (float) $snapshot['PL']['available'];
        $sl = (float) $snapshot['SL']['available'];
        $accruedPl = (float) $this->ledgerTransactionTotal($allocation, LeaveBucket::PL, ['monthly_accrual']);
        $accruedSl = (float) $this->ledgerTransactionTotal($allocation, LeaveBucket::SL, ['monthly_accrual']);
        $calc = app(MonthlyLeaveAccrualCalculator::class);
        $month = (int) ($date ?? Carbon::now('Asia/Kolkata'))->month;
        $plMonthlyAccrual = (float) $calc->creditForMonth((string) $policy->annual_paid_leaves, $month);
        $slMonthlyAccrual = (float) $calc->creditForMonth((string) $policy->annual_sick_leaves, $month);

        $yearEnd = $this->getPermanentEntitlement($allocation, 12);
        $yearEndPl = (float) $yearEnd['PL'];
        $yearEndSl = (float) $yearEnd['SL'];
        $yearEndTotal = (float) bcadd($yearEnd['PL'], $yearEnd['SL'], 6);

        $compOffAlloc = (float) ($allocation->comp_off_allocated ?? 0);
        $compOffUsed = (float) ($allocation->comp_off_used ?? 0);
        $compOffRem = (float) ($allocation->comp_off_remaining ?? 0);

        $totalEarnedToDate = (float) bcadd(bcadd((string) $accruedPl, (string) $accruedSl, 6), (string) $compOffAlloc, 6);

        $totalAvailable = (float) bcadd(bcadd((string) $pl, (string) $sl, 6), (string) $compOffRem, 6);
        $totalUsed = (float) bcadd(bcadd($snapshot['PL']['used'], $snapshot['SL']['used'], 6), (string) $compOffUsed, 6);

        $yearEndPlRem = (float) $this->nonNegative(bcsub($yearEnd['PL'], $snapshot['PL']['used'], 6));
        $yearEndSlRem = (float) $this->nonNegative(bcsub($yearEnd['SL'], $snapshot['SL']['used'], 6));
        $yearEndTotalRem = (float) bcadd(bcadd((string) $yearEndPlRem, (string) $yearEndSlRem, 6), (string) $compOffRem, 6);

        $slDisplayStr = rtrim(rtrim(number_format($slMonthlyAccrual, 4), '0'), '.');
        $plDisplayStr = rtrim(rtrim(number_format($plMonthlyAccrual, 2), '0'), '.');

        return [
            'available_pl' => $pl,
            'available_sl' => $sl,
            'available_comp_off' => $compOffRem,
            'usable_pl' => (float) $unitPolicy->usable((string) $pl, $policy),
            'usable_sl' => (float) $unitPolicy->usable((string) $sl, $policy),
            'accrued_pl' => $accruedPl,
            'accrued_sl' => $accruedSl,
            'accrued_comp_off' => $compOffAlloc,
            'annual_entitlement_pl' => $yearEndPl,
            'annual_entitlement_sl' => $yearEndSl,
            'accrued_to_date_pl' => $accruedPl,
            'accrued_to_date_sl' => $accruedSl,
            'accrued_to_date_comp_off' => $compOffAlloc,
            'earned_pl' => $accruedPl,
            'earned_sl' => $accruedSl,
            'earned_comp_off' => $compOffAlloc,
            'total_earned_to_date' => $totalEarnedToDate,
            'year_end_pl' => $yearEndPl,
            'year_end_sl' => $yearEndSl,
            'year_end_total' => $yearEndTotal,
            'year_end_pl_remaining' => $yearEndPlRem,
            'year_end_sl_remaining' => $yearEndSlRem,
            'year_end_total_remaining' => $yearEndTotalRem,
            'annual_quota' => $yearEndTotal,
            'annual_quota_pl' => $yearEndPl,
            'annual_quota_sl' => $yearEndSl,
            'annual_remaining' => $yearEndTotalRem,
            'annual_remaining_pl' => $yearEndPlRem,
            'annual_remaining_sl' => $yearEndSlRem,
            'annual_remaining_comp_off' => $compOffRem,
            'used_pl' => (float) $snapshot['PL']['used'],
            'used_sl' => (float) $snapshot['SL']['used'],
            'used_comp_off' => $compOffUsed,
            'total_available' => $totalAvailable,
            'total_remaining' => $yearEndTotalRem,
            'available_this_month' => $totalAvailable,
            'total_used' => $totalUsed,
            'monthly_quota_pl' => $plMonthlyAccrual,
            'monthly_quota_sl' => $slMonthlyAccrual,
            'monthly_quota_total' => (float) bcadd((string) $plMonthlyAccrual, (string) $slMonthlyAccrual, 6),
            'monthly_quota_display' => "PL {$plDisplayStr} | SL {$slDisplayStr}",
        ];
    }

    /** Complete controller-facing balance shape for ledger-backed reads. */
    public function getApiBalanceShape(EmployeeM $employee, ?Carbon $date = null): ?array
    {
        $allocation = $this->ledgerBackedAllocation($employee, $date);
        if (! $allocation) {
            return null;
        }
        $snapshot = $this->getLedgerSnapshot($allocation);
        $fields = $this->getLedgerApiBalancesForAllocation($employee, $allocation, $date, $snapshot);
        $central = $this->ledgerCentralBalance($employee, $allocation, $snapshot, $fields);

        $compOffAlloc = (float) ($allocation->comp_off_allocated ?? 0);
        $compOffUsed = (float) ($allocation->comp_off_used ?? 0);
        $compOffRem = (float) ($allocation->comp_off_remaining ?? 0);

        $balances = [
            [
                'type' => 'total',
                'label' => 'Total Leave',
                'allocated' => $fields['year_end_total'],
                'year_entitlement' => $fields['year_end_total'],
                'year_remaining' => $fields['year_end_total_remaining'],
                'year_end_total_remaining' => $fields['year_end_total_remaining'],
                'annual_quota' => $fields['year_end_total'],
                'annual_remaining' => $fields['year_end_total_remaining'],
                'earned_to_date' => $fields['total_earned_to_date'],
                'total_earned_to_date' => $fields['total_earned_to_date'],
                'used' => $fields['total_used'],
                'remaining' => $fields['year_end_total_remaining'],
                'available_this_month' => $fields['total_available'],
                'total_available' => $fields['total_available'],
            ],
            [
                'type' => 'paid',
                'label' => 'Paid Leave',
                'allocated' => $fields['accrued_pl'],
                'annual_entitlement' => $fields['year_end_pl'],
                'year_remaining' => $fields['year_end_pl_remaining'],
                'earned_to_date' => $fields['accrued_pl'],
                'used' => $fields['used_pl'],
                'remaining' => $fields['available_pl'],
                'available_this_month' => $fields['available_pl'],
            ],
            [
                'type' => 'sick',
                'label' => 'Sick Leave',
                'allocated' => $fields['accrued_sl'],
                'annual_entitlement' => $fields['year_end_sl'],
                'year_remaining' => $fields['year_end_sl_remaining'],
                'earned_to_date' => $fields['accrued_sl'],
                'used' => $fields['used_sl'],
                'remaining' => $fields['available_sl'],
                'available_this_month' => $fields['available_sl'],
            ],
            [
                'type' => 'comp_off',
                'label' => 'Comp Off',
                'allocated' => $compOffAlloc,
                'annual_entitlement' => $compOffAlloc,
                'year_remaining' => $compOffRem,
                'earned_to_date' => $compOffAlloc,
                'used' => $compOffUsed,
                'remaining' => $compOffRem,
                'available_this_month' => $compOffRem,
            ],
            [
                'type' => 'lwp',
                'label' => 'Leave Without Pay',
                'allocated' => 0.0,
                'annual_entitlement' => 0.0,
                'year_remaining' => 0.0,
                'earned_to_date' => 0.0,
                'used' => (float) ($allocation->lwp_used ?? 0),
                'remaining' => 0.0,
                'available_this_month' => 0.0,
            ],
        ];
        return [
            'allocation' => $allocation,
            'central' => $central,
            'fields' => $fields,
            'balances' => $balances,
            'monthly_quota' => [
                'limit' => 0.0,
                'used' => $central['current_month_used'],
                'carry_forward' => $central['carry_forward_available'],
                'available' => $fields['total_available'],
                'pl_quota' => $fields['monthly_quota_pl'],
                'sl_quota' => $fields['monthly_quota_sl'],
                'quota_display' => $fields['monthly_quota_display'],
                'available_pl' => $fields['available_pl'],
                'available_sl' => $fields['available_sl'],
                'available_comp_off' => $compOffRem,
            ],
        ];
    }

    private function ledgerTransactionTotal(LeaveAllocationM $allocation, LeaveBucket $bucket, array $types): string
    {
        return (string) LeaveBalanceTransactionM::query()
            ->where('leave_allocation_id', $allocation->id)
            ->where('employee_id', $allocation->employee_id)
            ->where('period_year', $allocation->year)
            ->where('bucket', $bucket->value)
            ->whereIn('transaction_type', $types)
            ->sum('amount');
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
     * Read an employee's current-year allocation without generating or saving one.
     * Non-permanent employees use the most recent row for their current stage.
     */
    public function allocationForRead(EmployeeM $employee, ?Carbon $date = null): LeaveAllocationM
    {
        $date = ($date ?? Carbon::now('Asia/Kolkata'))->copy()->setTimezone('Asia/Kolkata');
        $context = $this->allocationService->resolveEmployeeLeaveContext($employee);
        $stage = $context['stage'];
        $query = LeaveAllocationM::query()->where('employee_id', $employee->id);

        if ($stage === LeaveAllocationService::STAGE_PERMANENT) {
            $allocation = $query->where('year', (int) $date->year)->first();
        } else {
            $allocation = $query->where('employment_stage', $stage)->orderByDesc('year')->first();
        }

        return $allocation ?? new LeaveAllocationM([
            'employee_id' => $employee->id,
            'year' => (int) $date->year,
            'employment_stage' => $stage,
            'paid_allocated' => '0.00',
            'paid_used' => '0.00',
            'paid_remaining' => '0.00',
            'sick_allocated' => '0.00',
            'sick_used' => '0.00',
            'sick_remaining' => '0.00',
            'comp_off_allocated' => '0.00',
            'comp_off_used' => '0.00',
            'comp_off_remaining' => '0.00',
            'lwp_used' => '0.00',
            'total_allocated' => '0.00',
            'total_used' => '0.00',
            'total_remaining' => '0.00',
        ]);
    }

    /**
     * Get central leave balance structure for employee (Single Source of Truth).
     */
    public function getCentralBalance(EmployeeM $employee, ?Carbon $date = null): array
    {
        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $year = (int) $date->year;

        {
            $existingAllocation = LeaveAllocationM::query()
                ->where('employee_id', $employee->id)
                ->where('year', $year)
                ->first();
            $previousAllocation = $existingAllocation ? null : LeaveAllocationM::query()
                ->where('employee_id', $employee->id)->where('year', $year - 1)->first();
            if ($existingAllocation?->isLedgerBacked()) {
                return $this->ledgerCentralBalance($employee, $existingAllocation);
            }
            if (! $existingAllocation && $previousAllocation?->isLedgerBacked()) {
                return $this->emptyCentralBalance((int) $employee->id);
            }
        }

        if ($this->isInternOrProbation($employee)) {
            $allocation = $this->allocationForRead($employee, $date);
            $paid = (float) ($allocation->paid_remaining ?? 0);
            $sick = (float) ($allocation->sick_remaining ?? 0);
            $compOff = (float) ($allocation->comp_off_remaining ?? 0);
            $totalAllocated = (float) ($allocation->total_allocated ?? 0);
            $totalRemaining = $paid + $sick + $compOff;

            return [
                'employee_id' => (int) $employee->id,
                'paid_allocated' => (float) ($allocation->paid_allocated ?? 0),
                'paid_remaining' => $paid,
                'sick_allocated' => (float) ($allocation->sick_allocated ?? 0),
                'sick_remaining' => $sick,
                'comp_off_allocated' => (float) ($allocation->comp_off_allocated ?? 0),
                'comp_off_remaining' => $compOff,
                'total_allocated' => $totalAllocated,
                'monthly_limit' => 0.0,
                'current_month_used' => 0.0,
                'carry_forward_available' => 0.0,
                'available_this_month' => $totalRemaining,
                'leave_balance' => [
                    'monthly_quota' => 0.0,
                    'monthly_carry_forward' => 0.0,
                    'monthly_used_this_month' => 0.0,
                    'total_monthly_remaining_paid' => $paid,
                    'total_remaining_paid' => $paid,
                    'total_remaining_sick' => $sick,
                    'total_remaining_comp_off' => $compOff,
                    'total_lwp' => (float) ($allocation->lwp_used ?? 0),
                ],
            ];
        }

        // A legacy snapshot is intentionally not treated as an authoritative balance.
        return $this->emptyCentralBalance((int) $employee->id);
    }

    private function emptyCentralBalance(int $employeeId): array
    {
        return [
            'employee_id' => $employeeId,
            'paid_allocated' => 0.0, 'paid_remaining' => 0.0,
            'sick_allocated' => 0.0, 'sick_remaining' => 0.0,
            'comp_off_allocated' => 0.0, 'comp_off_remaining' => 0.0,
            'total_allocated' => 0.0, 'monthly_limit' => 0.0,
            'current_month_used' => 0.0, 'carry_forward_available' => 0.0,
            'available_this_month' => 0.0,
            'leave_balance' => [
                'monthly_quota' => 0.0, 'monthly_carry_forward' => 0.0,
                'monthly_used_this_month' => 0.0, 'total_monthly_remaining_paid' => 0.0,
                'total_remaining_paid' => 0.0, 'total_remaining_sick' => 0.0,
                'total_remaining_comp_off' => 0.0, 'total_lwp' => 0.0,
            ],
        ];
    }

    private function ledgerCentralBalance(
        EmployeeM $employee,
        LeaveAllocationM $allocation,
        ?array $snapshot = null,
        ?array $fields = null
    ): array {
        $snapshot ??= $this->getLedgerSnapshot($allocation);
        $pl = (float) $snapshot['PL']['available'];
        $sl = (float) $snapshot['SL']['available'];
        $compOff = (float) ($allocation->comp_off_remaining ?? 0);
        $monthly = app(LeaveMonthlyBalanceService::class)->compatibilityValuesFor(
            $employee,
            (int) $allocation->year,
            (int) Carbon::now('Asia/Kolkata')->month
        );
        $yearEndTotal = $fields['year_end_total'] ?? (float) ($allocation->total_allocated ?? 0);
        $yearEndPl = $fields['year_end_pl'] ?? (float) ($allocation->paid_allocated ?? 0);
        $yearEndSl = $fields['year_end_sl'] ?? (float) ($allocation->sick_allocated ?? 0);

        return [
            'employee_id' => (int) $employee->id,
            'paid_allocated' => $yearEndPl,
            'paid_remaining' => $pl,
            'sick_allocated' => $yearEndSl,
            'sick_remaining' => $sl,
            'comp_off_allocated' => (float) ($allocation->comp_off_allocated ?? 0),
            'comp_off_remaining' => $compOff,
            'total_allocated' => $yearEndTotal,
            'monthly_limit' => (float) $monthly['monthly_quota'],
            'current_month_used' => (float) $monthly['monthly_used_this_month'],
            'carry_forward_available' => (float) $monthly['monthly_carry_forward'],
            'available_this_month' => $pl,
            'leave_balance' => [
                'monthly_quota' => (float) $monthly['monthly_quota'],
                'monthly_carry_forward' => (float) $monthly['monthly_carry_forward'],
                'monthly_used_this_month' => (float) $monthly['monthly_used_this_month'],
                'total_monthly_remaining_paid' => $pl,
                'total_remaining_paid' => $pl,
                'total_remaining_sick' => $sl,
                'total_remaining_comp_off' => $compOff,
                'total_lwp' => (float) ($allocation->lwp_used ?? 0),
            ],
        ];
    }

    private function displayAmount(string $amount): float
    {
        return (float) $amount;
    }

    /**
     * Get type-wise balance breakdown dynamically from leave_types table and allocation.
     */
    public function getTypeWiseBalances(EmployeeM $employee, ?Carbon $date = null, ?array $central = null): array
    {
        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $central ??= $this->getCentralBalance($employee, $date);
        $lb = $central['leave_balance'];

        $types = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $list = [];

        foreach ($types as $type) {
            $code = strtoupper($type->code ?? '');
            $name = strtolower($type->name ?? '');

            $available = 0.0;
            $isPaid = (bool) $type->is_paid;

            if ($code === 'COMP_OFF' || (bool) $type->is_comp_off) {
                $available = (float) $lb['total_remaining_comp_off'];
            } else {
                try {
                    $bucket = LeaveBucket::fromLeaveType($type);
                    $available = (float) ($bucket === LeaveBucket::PL
                        ? $lb['total_remaining_paid']
                        : $lb['total_remaining_sick']);
                } catch (\InvalidArgumentException) {
                    $available = 0.0;
                }
            }

            $item = [
                'leave_type_id' => (int) $type->id,
                'leave_type' => $type->name,
                'code' => $type->code,
                'is_paid' => $isPaid,
                'available' => (float) $available,
            ];

            if (strtoupper((string) $type->code) === LeaveBucket::PL->value) {
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
        $allocation = $this->ledgerBackedAllocation($employee);
        if ($allocation) {
            $transactions = LeaveBalanceTransactionM::query()->where('employee_id', $employee->id)
                ->whereIn('leave_allocation_id', LeaveAllocationM::query()->where('employee_id', $employee->id)
                    ->where('uses_ledger', true)->select('id'))->latest('id')->paginate($perPage);
            return [
                'history' => collect($transactions->items())->map(fn (LeaveBalanceTransactionM $transaction) => [
                    'id' => (int) $transaction->id,
                    'action' => $transaction->transaction_type,
                    'month' => Carbon::parse($transaction->effective_date)->format('Y-m'),
                    'credit' => bccomp((string) $transaction->amount, '0', 6) > 0 ? (float) $transaction->amount : 0.0,
                    'debit' => bccomp((string) $transaction->amount, '0', 6) < 0 ? (float) bcsub('0', (string) $transaction->amount, 6) : 0.0,
                    'balance_before' => (float) $transaction->balance_before,
                    'balance_after' => (float) $transaction->balance_after,
                    'remarks' => $transaction->reason ?? '',
                    'created_at' => Carbon::parse($transaction->created_at)->setTimezone('Asia/Kolkata')->toDateTimeString(),
                    'date' => Carbon::parse($transaction->effective_date)->toDateString(),
                    'bucket' => $transaction->bucket,
                    'type' => $transaction->transaction_type,
                    'amount' => (string) $transaction->amount,
                    'leave_request_id' => $transaction->leave_request_id,
                    'reason' => $transaction->reason,
                ])->values()->toArray(),
                'pagination' => ['total' => $transactions->total(), 'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(), 'last_page' => $transactions->lastPage()],
            ];
        }
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
