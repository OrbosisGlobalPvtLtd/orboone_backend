<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyAllowanceService
{
    public function __construct(
        private LeavePolicyService $policyService,
        private LeaveAllocationService $allocationService
    ) {
    }

    /**
     * Credit monthly leave consumption allowance for an employee for a target month and year.
     * Keeps annual leave balance (paid_allocated / paid_remaining) UNCHANGED.
     * Accumulates monthly allowance directly in monthly_paid_available month-to-month within the calendar year.
     * Idempotent per employee, year, and month using last_monthly_credit_month (YYYY-MM).
     */
    public function creditMonthlyAllowance(EmployeeM $employee, int $year, int $month, ?Carbon $date = null, ?int $userId = null): ?array
    {
        if (! app(\App\Services\HRMS\Employee\EmployeeEligibilityS::class)->canUseLeave($employee)) {
            return null;
        }

        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata');
        $creditMonthKey = sprintf('%04d-%02d', $year, $month);

        $allocation = $this->allocationService->getOrGenerate($employee, $year, $userId);

        if ($allocation->last_monthly_credit_month === $creditMonthKey) {
            return null;
        }

        $alreadyLogged = LeaveBalanceLogM::where('employee_id', $employee->id)
            ->where('action', 'monthly_allowance_credit')
            ->where('remarks', 'like', "%Month {$month}, {$year}%")
            ->exists();

        if ($alreadyLogged) {
            $allocation->last_monthly_credit_month = $creditMonthKey;
            $allocation->save();
            return null;
        }

        return DB::transaction(function () use ($employee, $allocation, $year, $month, $creditMonthKey, $date, $userId) {
            $policy = $this->policyService->forEmployee($employee, $date);
            $credit = (float) ($policy->monthly_allowance_credit ?? $policy->monthly_leave_limit ?? 2.0);
            if ($credit <= 0.0) {
                $credit = 2.0;
            }

            $beforeAllowance = (float) ($allocation->monthly_paid_available ?? 0.0);

            $mode = strtolower((string) ($policy->monthly_allowance_mode ?? 'accumulate'));
            $accumulate = $mode === 'accumulate' || (bool) ($policy->allow_monthly_balance_accumulation ?? true);

            if ($accumulate) {
                $afterAllowance = round($beforeAllowance + $credit, 2);
            } else {
                $afterAllowance = $credit;
            }

            $allocation->monthly_paid_available = $afterAllowance;
            $allocation->last_monthly_credit_month = $creditMonthKey;
            $allocation->save();

            LeaveBalanceLogM::create([
                'employee_id' => $employee->id,
                'leave_allocation_id' => $allocation->id,
                'action' => 'monthly_allowance_credit',
                'credit' => $credit,
                'debit' => 0.0,
                'balance_before' => $beforeAllowance,
                'balance_after' => $afterAllowance,
                'remarks' => "Monthly Allowance Credit for Month {$month}, {$year}",
                'created_by_user_id' => $userId,
            ]);

            return [
                'employee_id' => $employee->id,
                'credit' => $credit,
                'before_allowance' => $beforeAllowance,
                'after_allowance' => $afterAllowance,
                'annual_paid_remaining' => (float) $allocation->paid_remaining,
            ];
        });
    }

    /**
     * Process monthly allowance credits for all active confirmed employees.
     */
    public function creditAllEligible(int $year, int $month, ?int $userId = null): array
    {
        $employees = EmployeeM::where('employment_status', 'active')
            ->where(function ($query) {
                $query->where('is_permanent', true)
                    ->orWhere('employee_stage', 'permanent')
                    ->orWhere('employment_type', 'permanent');
            })
            ->get();

        $processed = 0;
        $skipped = 0;
        $targetDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata');

        foreach ($employees as $employee) {
            $res = $this->creditMonthlyAllowance($employee, $year, $month, $targetDate, $userId);
            if ($res) {
                $processed++;
            } else {
                $skipped++;
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'processed_count' => $processed,
            'skipped_count' => $skipped,
        ];
    }

    /**
     * Deduct consumed paid leave from monthly_paid_available upon leave request approval.
     */
    public function deductMonthlyAllowance(LeaveAllocationM $allocation, float $days, ?int $userId = null): float
    {
        if ($days <= 0) {
            return 0.0;
        }

        $before = (float) ($allocation->monthly_paid_available ?? 0.0);
        $deductedFromMonthly = min($days, $before);
        $after = max(0.0, round($before - $deductedFromMonthly, 2));

        $allocation->monthly_paid_available = $after;
        $allocation->save();

        if ($deductedFromMonthly > 0) {
            LeaveBalanceLogM::create([
                'employee_id' => $allocation->employee_id,
                'leave_allocation_id' => $allocation->id,
                'action' => 'monthly_allowance_deduction',
                'credit' => 0.0,
                'debit' => $deductedFromMonthly,
                'balance_before' => $before,
                'balance_after' => $after,
                'remarks' => "Monthly Allowance Deduction of {$deductedFromMonthly} day(s)",
                'created_by_user_id' => $userId,
            ]);
        }

        return $deductedFromMonthly;
    }

    /**
     * Reset year-end monthly allowance on December 31st per DB policy.
     */
    public function resetYearEndAllowance(int $year, ?int $userId = null): array
    {
        $allocations = LeaveAllocationM::where('year', $year)->get();
        $processed = 0;

        foreach ($allocations as $allocation) {
            $before = (float) ($allocation->monthly_paid_available ?? 0.0);
            if ($before <= 0.0) {
                continue;
            }

            DB::transaction(function () use ($allocation, $before, $year, $userId) {
                $allocation->monthly_paid_available = 0.0;
                $allocation->save();

                LeaveBalanceLogM::create([
                    'employee_id' => $allocation->employee_id,
                    'leave_allocation_id' => $allocation->id,
                    'action' => 'allowance_reset',
                    'credit' => 0.0,
                    'debit' => $before,
                    'balance_before' => $before,
                    'balance_after' => 0.0,
                    'remarks' => "Year-end monthly allowance reset for year {$year}",
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
