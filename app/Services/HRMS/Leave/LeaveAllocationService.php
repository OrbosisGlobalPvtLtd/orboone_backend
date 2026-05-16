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
    public function __construct(private LeavePolicyService $policyService)
    {
    }

    public function generateForEmployee(EmployeeM $employee, int $year, ?int $userId = null): LeaveAllocationM
    {
        return DB::transaction(function () use ($employee, $year, $userId) {
            $policy = $this->policyService->forEmployee($employee, Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata'));
            $stage = $this->stageFor($employee);
            $fromDate = $this->allocationStartDate($employee, $stage, $year);
            $toDate = Carbon::create($year, 12, 31, 0, 0, 0, 'Asia/Kolkata');

            [$total, $paid, $sick] = $this->allocationAmounts($policy, $stage, $fromDate, $toDate);

            $allocation = LeaveAllocationM::firstOrNew([
                'employee_id' => $employee->id,
                'year' => $year,
                'employment_stage' => $stage,
            ]);

            if ($allocation->exists && $allocation->is_locked) {
                return $allocation;
            }

            $before = (float) ($allocation->total_remaining ?? 0);

            $allocation->fill([
                'policy_id' => $policy->id,
                'confirmation_date' => $employee->confirmation_date,
                'allocation_from_date' => $fromDate?->toDateString(),
                'allocation_to_date' => $toDate->toDateString(),
                'total_allocated' => $total,
                'paid_allocated' => $paid,
                'sick_allocated' => $sick,
                'comp_off_allocated' => (float) ($allocation->comp_off_allocated ?? 0),
                'allocation_reason' => 'Auto allocation for ' . $year,
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

        $approvedRequests = $employee->leaveRequests()
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->get();

        $allocation->paid_used = $approvedRequests->sum('paid_days');
        $allocation->sick_used = $approvedRequests->sum('sick_days');
        $allocation->comp_off_used = $approvedRequests->sum('comp_off_days');
        $allocation->lwp_used = $approvedRequests->sum('lwp_days');

        $this->recalculateAllocationFields($allocation);
        $allocation->save();

        return $allocation;
    }

    public function recalculateAllocationFields(LeaveAllocationM $allocation): LeaveAllocationM
    {
        $allocation->total_used = round((float) $allocation->paid_used + (float) $allocation->sick_used + (float) $allocation->comp_off_used, 2);
        $allocation->paid_remaining = round(max(0, (float) $allocation->paid_allocated - (float) $allocation->paid_used), 2);
        $allocation->sick_remaining = round(max(0, (float) $allocation->sick_allocated - (float) $allocation->sick_used), 2);
        $allocation->comp_off_remaining = round(max(0, (float) $allocation->comp_off_allocated - (float) $allocation->comp_off_used), 2);
        $allocation->total_remaining = round((float) $allocation->paid_remaining + (float) $allocation->sick_remaining + (float) $allocation->comp_off_remaining, 2);

        return $allocation;
    }

    private function allocationAmounts(LeavePolicyM $policy, string $stage, ?Carbon $fromDate, Carbon $toDate): array
    {
        if ($stage === 'probation') {
            $limit = (float) $policy->probation_leave_limit;
            return [$limit, $limit, 0.0];
        }

        if ($stage === 'internship') {
            $limit = (float) $policy->internship_leave_limit;
            return [$limit, $limit, 0.0];
        }

        if (! $fromDate || $fromDate->gt($toDate)) {
            return [0.0, 0.0, 0.0];
        }

        $months = 12 - ((int) $fromDate->month) + 1;
        $total = $this->roundByPolicy(((float) $policy->annual_total_leaves / 12) * $months, $policy->rounding_method);
        $paid = $this->roundByPolicy(((float) $policy->annual_paid_leaves / 12) * $months, $policy->rounding_method);
        $sick = $this->roundByPolicy(((float) $policy->annual_sick_leaves / 12) * $months, $policy->rounding_method);

        return [$total, min($paid, $total), min($sick, max(0, $total - min($paid, $total)))];
    }

    private function allocationStartDate(EmployeeM $employee, string $stage, int $year): ?Carbon
    {
        if ($stage === 'permanent') {
            $date = $employee->confirmation_date ?: $employee->joining_date;
        } elseif ($stage === 'internship') {
            $date = $employee->internship_start_date ?: $employee->joining_date;
        } else {
            $date = $employee->probation_start_date ?: $employee->joining_date;
        }

        if (! $date) {
            return null;
        }

        $start = Carbon::parse($date, 'Asia/Kolkata')->startOfMonth();
        $yearStart = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata');

        return $start->lt($yearStart) ? $yearStart : $start;
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

    private function roundByPolicy(float $value, ?string $method): float
    {
        return match ($method) {
            'floor' => floor($value * 2) / 2,
            'ceil' => ceil($value * 2) / 2,
            default => round($value, 2),
        };
    }
}
