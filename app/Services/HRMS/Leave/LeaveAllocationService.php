<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeavePolicyM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveAllocationService
{
    private const PERMANENT_PRORATION_BY_MONTH = [
        1 => 25,
        2 => 23,
        3 => 21,
        4 => 19,
        5 => 17,
        6 => 15,
        7 => 13,
        8 => 10,
        9 => 8,
        10 => 6,
        11 => 4,
        12 => 2,
    ];

    public function __construct(private LeavePolicyService $policyService)
    {
    }

    public function generateForEmployee(
        EmployeeM $employee,
        int $year,
        ?int $userId = null,
        ?string $forceStage = null,
        ?Carbon $effectiveDate = null
    ): LeaveAllocationM
    {
        if (!app(\App\Services\HRMS\Employee\EmployeeEligibilityS::class)->canUseLeave($employee)) {
            return LeaveAllocationM::firstOrNew(['employee_id' => $employee->id, 'year' => $year]);
        }

        return DB::transaction(function () use ($employee, $year, $userId, $forceStage, $effectiveDate) {
            $policy = $this->policyService->forEmployee($employee, Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata'));
            $stage = $forceStage ? strtolower($forceStage) : $this->stageFor($employee);
            $fromDate = $this->allocationStartDate($employee, $stage, $year, $effectiveDate);
            $toDate = Carbon::create($year, 12, 31, 0, 0, 0, 'Asia/Kolkata');

            [$total, $paid, $sick] = $this->allocationAmounts($policy, $stage, $fromDate, $toDate);

            $allocation = LeaveAllocationM::firstOrNew([
                'employee_id' => $employee->id,
                'year' => $year,
            ]);

            if ($allocation->exists && $allocation->is_locked) {
                return $allocation;
            }

            $before = (float) ($allocation->total_remaining ?? 0);

            $currentSystemYear = (int) Carbon::now('Asia/Kolkata')->year;
            $isPastYear = $year < $currentSystemYear;

            $monthlyUsed = $allocation->exists ? (float) ($allocation->monthly_used_this_month ?? 0.0) : 0.0;
            $monthlyCarry = $allocation->exists ? (float) ($allocation->monthly_carry_forward ?? 0.0) : 0.0;
            $lastMonthProcessed = $allocation->exists ? ($allocation->last_month_processed ?? sprintf('%04d-%02d', $year, 1)) : sprintf('%04d-%02d', $year, 1);

            $paidUsed = $allocation->exists ? (float) ($allocation->paid_used ?? 0.0) : 0.0;
            $sickUsed = $allocation->exists ? (float) ($allocation->sick_used ?? 0.0) : 0.0;
            $compOffUsed = $allocation->exists ? (float) ($allocation->comp_off_used ?? 0.0) : 0.0;
            $lwpUsed = $allocation->exists ? (float) ($allocation->lwp_used ?? 0.0) : 0.0;

            $allocation->fill([
                'employment_stage' => $stage,
                'policy_id' => $policy->id,
                'confirmation_date' => $employee->confirmation_date,
                'allocation_from_date' => $fromDate?->toDateString(),
                'allocation_to_date' => $toDate->toDateString(),
                'total_allocated' => $total,
                'paid_allocated' => $paid,
                'sick_allocated' => $sick,
                'paid_used' => $paidUsed,
                'sick_used' => $sickUsed,
                'comp_off_used' => $compOffUsed,
                'lwp_used' => $lwpUsed,
                'comp_off_allocated' => (float) ($allocation->comp_off_allocated ?? 0),
                'monthly_used_this_month' => $monthlyUsed,
                'monthly_carry_forward' => $monthlyCarry,
                'last_month_processed' => $lastMonthProcessed,
                'allocation_reason' => "Annual Allocation for {$year}",
                'is_locked' => $isPastYear,
                'created_by_user_id' => $userId,
            ]);

            $this->recalculateAllocationFields($allocation);
            $allocation->save();

            $after = (float) $allocation->total_remaining;
            if (round($after - $before, 2) !== 0.0) {
                LeaveBalanceLogM::create([
                    'employee_id' => $employee->id,
                    'leave_allocation_id' => $allocation->id,
                    'action' => 'allocation_generated',
                    'credit' => max(0, $after - $before),
                    'debit' => max(0, $before - $after),
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'remarks' => 'Leave allocation generated from DB policy.',
                    'created_by_user_id' => $userId,
                ]);
            }

            return $allocation;
        });
    }

    public function getOrGenerate(EmployeeM $employee, int $year, ?int $userId = null): LeaveAllocationM
    {
        $allocation = LeaveAllocationM::where('employee_id', $employee->id)
            ->where('year', $year)
            ->first();

        return $allocation ?: $this->generateForEmployee($employee, $year, $userId);
    }

    public function recalculateForEmployee(EmployeeM $employee, int $year): ?LeaveAllocationM
    {
        $allocation = LeaveAllocationM::where('employee_id', $employee->id)
            ->where('year', $year)
            ->first();

        if (! $allocation || $allocation->is_locked) {
            return $allocation;
        }

        $sums = $employee->leaveRequests()
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->selectRaw('
                COALESCE(SUM(paid_days), 0) as paid_used,
                COALESCE(SUM(sick_days), 0) as sick_used,
                COALESCE(SUM(comp_off_days), 0) as comp_off_used,
                COALESCE(SUM(lwp_days), 0) as lwp_used
            ')
            ->first();

        $allocation->paid_used = (float) ($sums->paid_used ?? 0.0);
        $allocation->sick_used = (float) ($sums->sick_used ?? 0.0);
        $allocation->comp_off_used = (float) ($sums->comp_off_used ?? 0.0);
        $allocation->lwp_used = (float) ($sums->lwp_used ?? 0.0);

        $this->recalculateAllocationFields($allocation);
        $allocation->save();

        return $allocation;
    }

    public function recalculateAllocationFields(LeaveAllocationM $allocation): LeaveAllocationM
    {
        $allocation->paid_remaining = round(max(0.0, (float) $allocation->paid_allocated - (float) $allocation->paid_used), 2);
        $allocation->sick_remaining = round(max(0.0, (float) $allocation->sick_allocated - (float) $allocation->sick_used), 2);
        $allocation->comp_off_remaining = round(max(0.0, (float) $allocation->comp_off_allocated - (float) $allocation->comp_off_used), 2);

        $allocation->total_allocated = round((float) $allocation->paid_allocated + (float) $allocation->sick_allocated, 2);
        $allocation->total_used = round((float) $allocation->paid_used + (float) $allocation->sick_used + (float) $allocation->comp_off_used, 2);
        $allocation->total_remaining = round((float) $allocation->paid_remaining + (float) $allocation->sick_remaining + (float) $allocation->comp_off_remaining, 2);

        $stage = strtolower((string) ($allocation->employment_stage ?? ''));
        $isInternOrProbation = str_contains($stage, 'intern') || str_contains($stage, 'probation');

        $rawQuota = (float) ($allocation->monthly_quota ?? 2.0);
        if ($isInternOrProbation || (float) $allocation->paid_allocated < $rawQuota) {
            $allocation->monthly_quota = round(min($rawQuota, (float) $allocation->paid_allocated), 2);
        } else {
            $allocation->monthly_quota = round($rawQuota, 2);
        }

        if ($isInternOrProbation) {
            $allocation->monthly_carry_forward = 0.0;
        } else {
            $allocation->monthly_carry_forward = round(max(0.0, min((float) ($allocation->monthly_carry_forward ?? 0.0), (float) $allocation->paid_remaining)), 2);
        }

        $carry = (float) $allocation->monthly_carry_forward;
        $quota = (float) $allocation->monthly_quota;
        $usedThisMonth = (float) ($allocation->monthly_used_this_month ?? 0.0);

        $monthlyRemainingRaw = max(0.0, ($quota + $carry) - $usedThisMonth);
        $allocation->total_monthly_remaining_paid = round(min($monthlyRemainingRaw, (float) $allocation->paid_remaining), 2);

        return $allocation;
    }

    public function calculateAllocationAmounts(?LeavePolicyM $policy, string $stage, ?Carbon $fromDate, ?Carbon $toDate = null): array
    {
        if (! $policy) {
            return [0.0, 0.0, 0.0];
        }

        $stage = strtolower($stage);

        if ($stage === 'probation') {
            $limit = (float) $policy->probation_leave_limit;
            return [$limit, $limit, 0.0];
        }

        if ($stage === 'internship') {
            $limit = (float) $policy->internship_leave_limit;
            return [$limit, $limit, 0.0];
        }

        if (! $fromDate || ($toDate && $fromDate->gt($toDate))) {
            return [0.0, 0.0, 0.0];
        }

        $month = (int) $fromDate->month;
        $baseTotal = (float) (self::PERMANENT_PRORATION_BY_MONTH[$month] ?? 25.0);

        $annualTotal = (float) $policy->annual_total_leaves;
        $total = ($annualTotal == 25) ? $baseTotal : round(($baseTotal / 25.0) * $annualTotal, 2);

        $paidRatio = (float) $policy->annual_paid_leaves / max(1.0, $annualTotal);
        $paid = (float) round($total * $paidRatio);
        $sick = (float) max(0.0, $total - $paid);

        return [$total, $paid, $sick];
    }

    private function allocationAmounts(LeavePolicyM $policy, string $stage, ?Carbon $fromDate, Carbon $toDate): array
    {
        return $this->calculateAllocationAmounts($policy, $stage, $fromDate, $toDate);
    }

    private function allocationStartDate(EmployeeM $employee, string $stage, int $year, ?Carbon $effectiveDate = null): ?Carbon
    {
        if ($stage === 'permanent') {
            $date = $employee->permanent_at;
            if (! $date && $effectiveDate) {
                $date = $effectiveDate->toDateString();
            }
            if (! $date) {
                $date = $employee->confirmation_effective_date ?: $employee->confirmation_date;
            }
        } elseif ($stage === 'internship') {
            $date = $effectiveDate?->toDateString()
                ?: $employee->internship_start_date
                ?: $employee->joining_date;
        } else {
            $date = $effectiveDate?->toDateString()
                ?: $employee->probation_start_date
                ?: $employee->joining_date;
        }

        if (! $date) {
            return null;
        }

        $parsedDate = Carbon::parse($date, 'Asia/Kolkata');
        $yearStart = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata');

        return $parsedDate->lt($yearStart) ? $yearStart : $parsedDate;
    }

    private function stageFor(EmployeeM $employee): string
    {
        $stage = strtolower((string) ($employee->employee_stage ?: $employee->employment_type));

        if (str_contains($stage, 'intern')) {
            return 'internship';
        }

        if (str_contains($stage, 'probation')) {
            return 'probation';
        }

        return 'permanent';
    }
}
