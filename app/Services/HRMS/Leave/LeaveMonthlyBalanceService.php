<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\CompOffM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceTransactionM;
use App\Models\HRMS\Leave\LeaveMonthlyBalanceM;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class LeaveMonthlyBalanceService
{
    public function __construct(
        private LeaveAllocationService $allocationService,
        private LeavePolicyService $policyService,
        private MonthlyLeaveAccrualCalculator $accrualCalculator
    ) {
    }

    /** Rebuild this employee's calendar-year passbook through the requested month. */
    public function rebuildEmployeeThrough(EmployeeM $employee, int $year, int $throughMonth): void
    {
        if (! Schema::hasTable('leave_monthly_balances')) {
            return;
        }

        if ($throughMonth < 1 || $throughMonth > 12) {
            throw new \InvalidArgumentException('Month must be between 1 and 12.');
        }

        DB::transaction(function () use ($employee, $year, $throughMonth): void {
            $allocation = LeaveAllocationM::query()
                ->where('employee_id', $employee->id)
                ->where('year', $year)
                ->first();
            $context = $this->allocationService->resolveEmployeeLeaveContext($employee);
            $permanentDate = $context['permanent_date'] ?? null;
            $stage = strtolower((string) ($allocation?->employment_stage ?? $context['stage'] ?? ''));
            $ledgerBacked = $allocation?->isLedgerBacked() ?? false;
            $cutover = $allocation?->ledger_effective_from;

            $startMonth = 1;
            if ($stage === LeaveAllocationService::STAGE_PERMANENT) {
                $permDate = $permanentDate
                    ?: ($allocation?->confirmation_date?->toDateString()
                        ?: ($allocation?->allocation_from_date?->toDateString()
                            ?: ($employee->confirmation_date
                                ?: ($employee->confirmation_effective_date
                                    ?: $employee->permanent_at))));
                if ($permDate) {
                    $permCarbon = Carbon::parse($permDate, 'Asia/Kolkata');
                    if ((int) $permCarbon->year === $year) {
                        $startMonth = (int) $permCarbon->month;
                    } elseif ((int) $permCarbon->year > $year) {
                        if ($allocation?->id) {
                            LeaveMonthlyBalanceM::query()
                                ->where('employee_id', $employee->id)
                                ->where('year', $year)
                                ->where('leave_allocation_id', $allocation->id)
                                ->where('is_locked', false)
                                ->delete();
                        }
                        return;
                    } else {
                        // Became permanent in a previous year (e.g. 2026, and year is 2027)
                        // In 2027, the record starts from January (month 1)
                        $startMonth = 1;
                    }
                }
            } else {
                $stageDate = $allocation?->allocation_from_date?->toDateString()
                    ?: ($employee->internship_start_date
                        ?: ($employee->probation_start_date ?: $employee->joining_date));
                if ($stageDate) {
                    $stageCarbon = Carbon::parse($stageDate, 'Asia/Kolkata');
                    if ((int) $stageCarbon->year === $year) {
                        $startMonth = (int) $stageCarbon->month;
                    } elseif ((int) $stageCarbon->year > $year) {
                        if ($allocation?->id) {
                            LeaveMonthlyBalanceM::query()
                                ->where('employee_id', $employee->id)
                                ->where('year', $year)
                                ->where('leave_allocation_id', $allocation->id)
                                ->where('is_locked', false)
                                ->delete();
                        }
                        return;
                    } else {
                        $startMonth = 1;
                    }
                }
            }

            if ($startMonth > $throughMonth) {
                if ($allocation?->id) {
                    LeaveMonthlyBalanceM::query()
                        ->where('employee_id', $employee->id)
                        ->where('year', $year)
                        ->where('leave_allocation_id', $allocation->id)
                        ->where('is_locked', false)
                        ->delete();
                }
                return;
            }

            if ($startMonth > 1 && $allocation?->id) {
                LeaveMonthlyBalanceM::query()
                    ->where('employee_id', $employee->id)
                    ->where('year', $year)
                    ->where('month', '<', $startMonth)
                    ->where('leave_allocation_id', $allocation->id)
                    ->where('is_locked', false)
                    ->delete();
            }

            $existingRows = LeaveMonthlyBalanceM::query()
                ->where('employee_id', $employee->id)
                ->where('year', $year)
                ->whereBetween('month', [$startMonth, $throughMonth])
                ->get()
                ->keyBy('month');

            $start = Carbon::create($year, $startMonth, 1, 0, 0, 0, 'Asia/Kolkata')->toDateString();
            $end = Carbon::create($year, $throughMonth, 1, 0, 0, 0, 'Asia/Kolkata')->endOfMonth()->toDateString();

            $leaveTotals = DB::table('leave_request_dates as d')
                ->join('leave_requests as r', 'r.id', '=', 'd.leave_request_id')
                ->where('d.employee_id', $employee->id)
                ->where('r.status', 'approved')
                ->whereBetween('d.leave_date', [$start, $end])
                ->selectRaw('MONTH(d.leave_date) as month')
                ->selectRaw('COALESCE(SUM(d.paid_day), 0) as pl_used')
                ->selectRaw('COALESCE(SUM(d.sick_day), 0) as sl_used')
                ->selectRaw('COALESCE(SUM(d.comp_off_day), 0) as comp_off_used')
                ->selectRaw('COALESCE(SUM(d.lwp_day), 0) as lwp_used')
                ->groupByRaw('MONTH(d.leave_date)')
                ->get()
                ->keyBy('month');

            $trackedCompOffMovements = collect();
            if (Schema::hasTable('leave_comp_off_transactions')) {
                $trackedCompOffMovements = DB::table('leave_comp_off_transactions as t')
                    ->join('leave_requests as r', 'r.id', '=', 't.leave_request_id')
                    ->where('t.employee_id', $employee->id)
                    ->where('r.status', 'approved')
                    ->whereBetween('t.effective_date', [$start, $end])
                    ->selectRaw('MONTH(t.effective_date) as month')
                    ->selectRaw('COALESCE(SUM(t.amount), 0) as net_amount')
                    ->groupByRaw('MONTH(t.effective_date)')
                    ->get()
                    ->keyBy('month');
            }

            $movementRows = collect();
            $runningLedger = ['PL' => '0.000000', 'SL' => '0.000000'];
            if ($allocation?->exists) {
                $movementRows = LeaveBalanceTransactionM::query()
                    ->where('leave_allocation_id', $allocation->id)
                    ->where('employee_id', $employee->id)
                    ->whereBetween('effective_date', [$start, $end])
                    ->selectRaw('MONTH(effective_date) as month, bucket, transaction_type')
                    ->selectRaw('COALESCE(SUM(amount), 0) as amount')
                    ->groupByRaw('MONTH(effective_date), bucket, transaction_type')
                    ->get()
                    ->groupBy('month');

                $priorAmounts = LeaveBalanceTransactionM::query()
                    ->where('leave_allocation_id', $allocation->id)
                    ->where('employee_id', $employee->id)
                    ->whereDate('effective_date', '<', $start)
                    ->selectRaw('bucket, COALESCE(SUM(amount), 0) as amount')
                    ->groupBy('bucket')->get()->keyBy('bucket');
                foreach (['PL', 'SL'] as $bucket) {
                    $runningLedger[$bucket] = (string) ($priorAmounts->get($bucket)->amount ?? '0.000000');
                }
            }

            $compOffRows = CompOffM::query()
                ->where('employee_id', $employee->id)
                ->whereBetween('worked_date', [$start, $end])
                ->where('status', '!=', 'cancelled')
                ->selectRaw('MONTH(worked_date) as month')
                ->selectRaw('COALESCE(SUM(earned_days), 0) as remaining_earned')
                ->selectRaw("COALESCE(SUM(CASE WHEN status = 'expired' THEN earned_days ELSE 0 END), 0) as expired_remaining")
                ->groupByRaw('MONTH(worked_date)')
                ->get()->keyBy('month');

            $lockedMonths = Schema::hasTable('monthly_attendance_summaries')
                ? DB::table('monthly_attendance_summaries')
                    ->where('employee_id', $employee->id)
                    ->where('year', $year)
                    ->whereBetween('month', [1, $throughMonth])
                    ->where('is_locked', true)
                    ->get(['month', 'locked_at', 'locked_by_user_id'])
                    ->keyBy('month')
                : collect();

            $rows = [];
            $rowsToPersist = [];
            $previous = ['PL' => '0.000000', 'SL' => '0.000000'];
            $today = Carbon::today('Asia/Kolkata');

            for ($month = $startMonth; $month <= $throughMonth; $month++) {
                $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata');
                $periodEnd = $periodStart->copy()->endOfMonth();
                $policy = $this->policyService->forEmployee($employee, $periodStart);
                $totals = $leaveTotals->get($month);
                $monthMovements = $movementRows->get($month, collect());
                $existing = $existingRows->get($month);

                // A payroll-locked month is an immutable close. Carry its closing
                // balance forward, but do not rewrite its stored passbook row.
                if ($existing?->is_locked) {
                    foreach ($monthMovements as $movement) {
                        if (isset($runningLedger[$movement->bucket])) {
                            $runningLedger[$movement->bucket] = bcadd(
                                $runningLedger[$movement->bucket],
                                (string) $movement->amount,
                                6
                            );
                        }
                    }
                    $previous['PL'] = (string) $existing->pl_closing;
                    $previous['SL'] = (string) $existing->sl_closing;
                    $rows[] = $existing->getAttributes();
                    continue;
                }
                $eligibleMonth = 0;
                if ($stage === LeaveAllocationService::STAGE_PERMANENT && $permanentDate) {
                    $permanentStart = Carbon::parse($permanentDate, 'Asia/Kolkata');
                    if ($permanentStart->startOfDay()->lessThanOrEqualTo($periodEnd)) {
                        $firstEligibleMonth = $permanentStart->year === $year ? (int) $permanentStart->month : 1;
                        $eligibleMonth = max(0, $month - $firstEligibleMonth + 1);
                    }
                }

                $plAccrued = '0.000000';
                $slAccrued = '0.000000';
                if ($eligibleMonth > 0 && $policy) {
                    $plAccrued = $this->accrualForMonth($monthMovements, 'PL', $ledgerBacked, $cutover, $year, $month,
                        (string) $policy->annual_paid_leaves, $eligibleMonth);
                    $slAccrued = $this->accrualForMonth($monthMovements, 'SL', $ledgerBacked, $cutover, $year, $month,
                        (string) $policy->annual_sick_leaves, $eligibleMonth);
                }

                $plUsed = $this->decimal((string) ($totals->pl_used ?? '0'));
                $slUsed = $this->decimal((string) ($totals->sl_used ?? '0'));
                $compOffUsed = $this->decimal((string) ($totals->comp_off_used ?? '0'));
                $lwpUsed = $this->decimal((string) ($totals->lwp_used ?? '0'));

                $plLapsed = $this->movementAmount($monthMovements, 'PL', 'lapse');
                $slLapsed = $this->movementAmount($monthMovements, 'SL', 'lapse');
                $plAdjustment = $this->movementAmount($monthMovements, 'PL', 'adjustment');
                $slAdjustment = $this->movementAmount($monthMovements, 'SL', 'adjustment');

                $plClosing = bcsub(bcadd(bcadd($previous['PL'], $plAccrued, 6), $plAdjustment, 6), bcadd($plUsed, $plLapsed, 6), 6);
                $slClosing = bcsub(bcadd(bcadd($previous['SL'], $slAccrued, 6), $slAdjustment, 6), bcadd($slUsed, $slLapsed, 6), 6);

                $shouldReconcileLedger = $ledgerBacked && (! $cutover || $periodEnd->greaterThanOrEqualTo(Carbon::parse($cutover, 'Asia/Kolkata')->startOfDay()));
                if ($shouldReconcileLedger) {
                    foreach (['PL', 'SL'] as $bucket) {
                        $monthRows = $monthMovements->filter(static fn ($row): bool => $row->bucket === $bucket);
                        foreach ($monthRows as $row) {
                            $runningLedger[$bucket] = bcadd($runningLedger[$bucket], (string) $row->amount, 6);
                        }
                        $candidate = $bucket === 'PL' ? $plClosing : $slClosing;
                        $adjustment = bcsub($runningLedger[$bucket], $candidate, 6);
                        if ($bucket === 'PL') {
                            $plAdjustment = bcadd($plAdjustment, $adjustment, 6);
                            $plClosing = $runningLedger[$bucket];
                        } else {
                            $slAdjustment = bcadd($slAdjustment, $adjustment, 6);
                            $slClosing = $runningLedger[$bucket];
                        }
                    }
                } else {
                    if (bccomp($plClosing, '0.000000', 6) < 0) {
                        $plClosing = '0.000000';
                    }
                    if (bccomp($slClosing, '0.000000', 6) < 0) {
                        $slClosing = '0.000000';
                    }
                }

                $previous['PL'] = $plClosing;
                $previous['SL'] = $slClosing;

                $trackedCompOffUsed = bccomp((string) ($trackedCompOffMovements->get($month)->net_amount ?? '0'), '0', 6) < 0
                    ? bcsub('0', (string) $trackedCompOffMovements->get($month)->net_amount, 6)
                    : '0.000000';
                $legacyCompOffUsed = bcsub($compOffUsed, $trackedCompOffUsed, 6);
                if (bccomp($legacyCompOffUsed, '0.000000', 6) < 0) {
                    $legacyCompOffUsed = '0.000000';
                }
                $compOffEarned = $existing
                    ? (string) $existing->comp_off_earned
                    : bcadd((string) ($compOffRows->get($month)->remaining_earned ?? '0'), $legacyCompOffUsed, 6);
                $monthClosed = $periodEnd->lessThan($today);
                $compOffLapsed = $monthClosed ? bcsub($compOffEarned, $compOffUsed, 6) : '0.000000';
                if (bccomp($compOffLapsed, '0.000000', 6) < 0) {
                    $compOffLapsed = '0.000000';
                }
                $compOffClosing = bcsub(bcsub($compOffEarned, $compOffUsed, 6), $compOffLapsed, 6);
                if (bccomp($compOffClosing, '0.000000', 6) < 0) {
                    $compOffClosing = '0.000000';
                }

                $passbookRow = [
                    'employee_id' => $employee->id,
                    'leave_allocation_id' => $allocation?->id,
                    'policy_id' => $policy?->id,
                    'year' => $year,
                    'month' => $month,
                    'employment_stage' => $stage !== '' ? $stage : null,
                    'balance_source' => $ledgerBacked ? 'ledger' : ($stage === LeaveAllocationService::STAGE_PERMANENT ? 'legacy_projection' : 'stage'),
                    'pl_opening' => $month === $startMonth ? ($runningLedger['PL'] !== '0.000000' && $ledgerBacked ? $runningLedger['PL'] : '0.000000') : $this->openingForMonth($month, 'PL', $rows),
                    'pl_accrued' => $plAccrued,
                    'pl_used' => $plUsed,
                    'pl_adjustment' => $plAdjustment,
                    'pl_lapsed' => $plLapsed,
                    'pl_closing' => $plClosing,
                    'sl_opening' => $month === $startMonth ? ($runningLedger['SL'] !== '0.000000' && $ledgerBacked ? $runningLedger['SL'] : '0.000000') : $this->openingForMonth($month, 'SL', $rows),
                    'sl_accrued' => $slAccrued,
                    'sl_used' => $slUsed,
                    'sl_adjustment' => $slAdjustment,
                    'sl_lapsed' => $slLapsed,
                    'sl_closing' => $slClosing,
                    'comp_off_earned' => $compOffEarned,
                    'comp_off_used' => $compOffUsed,
                    'comp_off_lapsed' => $compOffLapsed,
                    'comp_off_closing' => $compOffClosing,
                    'lwp_used' => $lwpUsed,
                    'is_locked' => $lockedMonths->has($month),
                    'locked_at' => $lockedMonths->get($month)?->locked_at,
                    'locked_by_user_id' => $lockedMonths->get($month)?->locked_by_user_id,
                    'calculated_at' => now('Asia/Kolkata'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $rows[] = $passbookRow;
                $rowsToPersist[] = $passbookRow;
            }

            if ($rowsToPersist !== []) {
                LeaveMonthlyBalanceM::query()->upsert(
                    $rowsToPersist,
                    ['employee_id', 'year', 'month'],
                    array_values(array_diff(array_keys($rowsToPersist[0]), ['employee_id', 'year', 'month', 'created_at']))
                );
            }
        });
    }

    /** Refresh one period from its first month so opening balances remain sequential. */
    public function refreshEmployeeMonth(EmployeeM $employee, int $year, int $month): void
    {
        $this->rebuildEmployeeThrough($employee, $year, $month);
    }

    /** Read the current passbook month for legacy API compatibility fields. */
    public function compatibilityValuesFor(EmployeeM $employee, int $year, int $month): array
    {
        $empty = [
            'monthly_quota' => 0.0,
            'monthly_carry_forward' => 0.0,
            'monthly_used_this_month' => 0.0,
        ];
        if (! Schema::hasTable('leave_monthly_balances')) {
            return $empty;
        }

        $row = LeaveMonthlyBalanceM::query()
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first(['pl_accrued', 'pl_opening', 'pl_used']);

        if (! $row) {
            return $empty;
        }

        return [
            'monthly_quota' => (float) $row->pl_accrued,
            'monthly_carry_forward' => (float) $row->pl_opening,
            'monthly_used_this_month' => (float) $row->pl_used,
        ];
    }

    /** Canonical monthly leave totals for payroll, with approved date rows as fallback. */
    public function leaveTotalsForEmployeeMonth(EmployeeM $employee, int $year, int $month): array
    {
        $snapshot = Schema::hasTable('leave_monthly_balances') ? LeaveMonthlyBalanceM::query()
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first() : null;

        if ($snapshot) {
            return [
                'paid_leave_days' => (string) $snapshot->pl_used,
                'sick_leave_days' => (string) $snapshot->sl_used,
                'comp_off_days' => (string) $snapshot->comp_off_used,
                'lwp_days' => (string) $snapshot->lwp_used,
            ];
        }

        $row = DB::table('leave_request_dates as d')
            ->join('leave_requests as r', 'r.id', '=', 'd.leave_request_id')
            ->where('d.employee_id', $employee->id)
            ->where('r.status', 'approved')
            ->whereYear('d.leave_date', $year)
            ->whereMonth('d.leave_date', $month)
            ->selectRaw('COALESCE(SUM(d.paid_day), 0) as paid')
            ->selectRaw('COALESCE(SUM(d.sick_day), 0) as sick')
            ->selectRaw('COALESCE(SUM(d.comp_off_day), 0) as comp')
            ->selectRaw('COALESCE(SUM(d.lwp_day), 0) as lwp')
            ->first();

        return [
            'paid_leave_days' => (string) ($row->paid ?? '0'),
            'sick_leave_days' => (string) ($row->sick ?? '0'),
            'comp_off_days' => (string) ($row->comp ?? '0'),
            'lwp_days' => (string) ($row->lwp ?? '0'),
        ];
    }

    public function queryForYear(int $year): Builder
    {
        return LeaveMonthlyBalanceM::query()
            ->with(['employee.user', 'allocation', 'policy'])
            ->where('year', $year)
            ->orderBy('month')
            ->orderBy('employee_id');
    }

    /** Record a new/revised Comp-Off grant without losing the original grant after use. */
    public function recordCompOffEarnedDelta(EmployeeM $employee, Carbon $earnedDate, string $delta): void
    {
        if (! Schema::hasTable('leave_monthly_balances')) {
            return;
        }

        $year = (int) $earnedDate->year;
        $month = (int) $earnedDate->month;
        $existing = LeaveMonthlyBalanceM::query()
            ->where('employee_id', $employee->id)->where('year', $year)->where('month', $month)->exists();
        if (! $existing) {
            $this->rebuildEmployeeThrough($employee, $year, $month);
            return;
        }

        DB::transaction(function () use ($employee, $year, $month, $delta): void {
            $row = LeaveMonthlyBalanceM::query()->where('employee_id', $employee->id)
                ->where('year', $year)->where('month', $month)->lockForUpdate()->firstOrFail();
            $row->comp_off_earned = bcadd((string) $row->comp_off_earned, $delta, 6);
            if (bccomp((string) $row->comp_off_earned, '0.000000', 6) < 0) {
                $row->comp_off_earned = '0.000000';
            }
            $row->save();
            $this->rebuildEmployeeThrough($employee, $year, $month);
        });
    }

    private function accrualForMonth($monthMovements, string $bucket, bool $ledgerBacked, mixed $cutover,
        int $year, int $month, string $annual, int $eligibleMonth): string
    {
        $posted = $this->movementAmount($monthMovements, $bucket, 'monthly_accrual');
        if (! $ledgerBacked) {
            return $this->accrualCalculator->creditForMonth($annual, $eligibleMonth);
        }
        if ($cutover && $year === (int) $cutover->year && $month <= (int) $cutover->month) {
            return $this->accrualCalculator->creditForMonth($annual, $eligibleMonth);
        }
        return $posted;
    }

    private function movementAmount($monthMovements, string $bucket, string $type): string
    {
        $row = $monthMovements->first(static fn ($movement): bool =>
            $movement->bucket === $bucket && $movement->transaction_type === $type);
        $amount = (string) ($row->amount ?? '0.000000');
        if ($type === 'lapse' && bccomp($amount, '0.000000', 6) < 0) {
            return bcsub('0.000000', $amount, 6);
        }
        return $this->decimal($amount);
    }

    private function openingForMonth(int $month, string $bucket, array $rows): string
    {
        foreach ($rows as $row) {
            if ((int) $row['month'] === $month - 1) {
                return (string) $row[strtolower($bucket) . '_closing'];
            }
        }
        return '0.000000';
    }

    private function decimal(string $amount): string
    {
        return bcadd($amount, '0', 6);
    }
}
