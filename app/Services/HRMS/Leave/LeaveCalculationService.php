<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveCalculationService
{
    public function __construct(
        private LeavePolicyService $policyService,
        private LeaveAllocationService $allocationService,
        private SandwichLeaveService $sandwichLeaveService
    ) {
    }

    public function calculate(EmployeeM $employee, LeaveTypeM $leaveType, array $payload, ?LeaveRequestM $existingRequest = null): array
    {
        $start = Carbon::parse($payload['start_date'], 'Asia/Kolkata')->startOfDay();
        $end = Carbon::parse($payload['end_date'], 'Asia/Kolkata')->startOfDay();

        if ($end->lt($start)) {
            throw ValidationException::withMessages(['end_date' => 'End date must be after or equal to start date.']);
        }

        $policy = $this->policyService->forEmployee($employee, $start);
        $allocation = $this->allocationService->getOrGenerate($employee, $start->year, auth()->id());
        $isHalfDay = (bool) ($payload['is_half_day'] ?? false);

        if ($isHalfDay && ! $leaveType->allow_half_day) {
            throw ValidationException::withMessages(['is_half_day' => 'Half-day leave is not allowed for this leave type.']);
        }

        if ($isHalfDay && ! $start->isSameDay($end)) {
            throw ValidationException::withMessages(['is_half_day' => 'Half-day leave can be applied for one date only.']);
        }

        $dateRows = $this->sandwichLeaveService->buildDates($employee, $start, $end, $leaveType);
        $deductedDays = 0.0;
        foreach ($dateRows as $row) {
            if ($row['deduct_as_leave']) {
                $deductedDays += $isHalfDay ? 0.5 : 1.0;
            }
        }

        if ($deductedDays <= 0) {
            throw ValidationException::withMessages(['start_date' => 'Selected range has no payable leave days.']);
        }

        $maxAtOnce = $leaveType->max_days_per_request ?: $policy->max_leave_at_once;
        if ($maxAtOnce && $deductedDays > (float) $maxAtOnce) {
            throw ValidationException::withMessages(['end_date' => "Maximum leave at once is {$maxAtOnce} day(s)."]);
        }

        $this->ensureNoDuplicateDates($employee, $dateRows, $existingRequest);
        $this->ensureSickAttachment($employee, $leaveType, $policy, $deductedDays, $payload, $dateRows);

        [$paid, $sick, $compOff, $lwp] = $this->splitDays(
            $employee,
            $leaveType,
            $policy,
            $allocation,
            $start,
            $dateRows,
            $deductedDays
        );

        $dateRows = $this->applySplitToDates($dateRows, $isHalfDay ? 0.5 : 1.0, $paid, $sick, $compOff, $lwp);

        return [
            'policy' => $policy,
            'allocation' => $allocation,
            'dates' => $dateRows,
            'requested_days' => $deductedDays,
            'deducted_days' => $deductedDays,
            'sandwich_applied' => collect($dateRows)->contains(fn ($row) => (bool) $row['is_sandwich_day']),
            'paid_days' => $paid,
            'sick_days' => $sick,
            'comp_off_days' => $compOff,
            'lwp_days' => $lwp,
        ];
    }

    private function splitDays(EmployeeM $employee, LeaveTypeM $leaveType, $policy, $allocation, Carbon $start, array $dateRows, float $deductedDays): array
    {
        if ($leaveType->is_lwp) {
            return [0.0, 0.0, 0.0, $deductedDays];
        }

        $stage = strtolower((string) ($allocation->employment_stage ?: $employee->employee_stage));
        $stageLimit = null;
        if ($stage === 'probation') {
            $stageLimit = (float) $policy->probation_leave_limit;
        } elseif ($stage === 'internship') {
            $stageLimit = (float) $policy->internship_leave_limit;
        }

        $paidCapacity = (float) $allocation->paid_remaining;
        $sickCapacity = (float) $allocation->sick_remaining;
        $compCapacity = (float) $allocation->comp_off_remaining;

        if ($stageLimit !== null) {
            $alreadyUsed = (float) $allocation->paid_used + (float) $allocation->sick_used + (float) $allocation->comp_off_used;
            $stageAvailable = max(0, $stageLimit - $alreadyUsed);
            $paidCapacity = min($paidCapacity + $sickCapacity + $compCapacity, $stageAvailable);
            $sickCapacity = $leaveType->is_sick ? $paidCapacity : 0.0;
            $compCapacity = $leaveType->is_comp_off ? $paidCapacity : 0.0;
        }

        if ($policy->nov_dec_half_usage_enabled && in_array((int) $start->month, [11, 12], true)) {
            $remaining = (float) $allocation->total_remaining;
            if ($remaining > (float) $policy->nov_dec_threshold_balance) {
                // In Nov/Dec, cap usable balance to the policy percentage when balance exceeds threshold.
                $allowed = round($remaining * ((float) $policy->nov_dec_usage_percentage / 100), 2);
                $paidCapacity = min($paidCapacity, $allowed);
                $sickCapacity = min($sickCapacity, $allowed);
                $compCapacity = min($compCapacity, $allowed);
            }
        }

        // Monthly leave limit must be enforced independent of accumulation behavior.
        $monthlyAvailable = $this->remainingMonthlyLimit($employee, $policy, $dateRows);
        $paidCapacity = min($paidCapacity, $monthlyAvailable);
        $sickCapacity = min($sickCapacity, $monthlyAvailable);
        $compCapacity = min($compCapacity, $monthlyAvailable);

        $paid = 0.0;
        $sick = 0.0;
        $compOff = 0.0;

        if ($leaveType->is_comp_off) {
            if ($compCapacity <= 0) {
                $hasApprovedPendingWorkRequest = DB::table('holiday_work_requests')
                    ->where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('comp_off_generated', 0)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($hasApprovedPendingWorkRequest) {
                    throw ValidationException::withMessages([
                        'leave_type_id' => 'You cannot use comp-off leave before completing approved holiday/weekoff work.',
                    ]);
                }
            }
            $compOff = min($deductedDays, $compCapacity);
        } elseif ($leaveType->is_sick) {
            $sick = min($deductedDays, $sickCapacity);
            $paid = min($deductedDays - $sick, $paidCapacity);
        } else {
            $paid = min($deductedDays, $paidCapacity);
            $sick = min($deductedDays - $paid, $sickCapacity);
        }

        $lwp = round(max(0, $deductedDays - $paid - $sick - $compOff), 2);

        return [round($paid, 2), round($sick, 2), round($compOff, 2), $lwp];
    }

    private function remainingMonthlyLimit(EmployeeM $employee, $policy, array $dateRows): float
    {
        $limit = (float) ($policy->monthly_leave_limit ?? 0);
        if ($limit <= 0) {
            return INF;
        }

        $firstDeductedDate = collect($dateRows)->firstWhere('deduct_as_leave', true)['leave_date'] ?? null;
        if (! $firstDeductedDate) {
            return 0.0;
        }

        $date = Carbon::parse($firstDeductedDate, 'Asia/Kolkata');
        $used = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->where('leave_requests.status', 'approved')
            ->whereMonth('leave_request_dates.leave_date', $date->month)
            ->whereYear('leave_request_dates.leave_date', $date->year)
            ->where('leave_request_dates.deduct_as_leave', 1)
            ->sum(DB::raw('paid_day + sick_day + comp_off_day'));

        return max(0, $limit - (float) $used);
    }

    private function ensureNoDuplicateDates(EmployeeM $employee, array $dateRows, ?LeaveRequestM $existingRequest): void
    {
        $dates = collect($dateRows)
            ->where('deduct_as_leave', true)
            ->pluck('leave_date')
            ->values()
            ->all();

        $duplicate = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->whereIn('leave_request_dates.leave_date', $dates)
            ->whereNotIn('leave_requests.status', ['rejected', 'cancelled']);

        if ($existingRequest) {
            $duplicate->where('leave_requests.id', '<>', $existingRequest->id);
        }

        if ($duplicate->exists()) {
            throw ValidationException::withMessages(['start_date' => 'A non-cancelled leave request already exists for one or more selected dates.']);
        }
    }

    private function ensureSickAttachment(EmployeeM $employee, LeaveTypeM $leaveType, $policy, float $deductedDays, array $payload, array $dateRows): void
    {
        if (! $leaveType->is_sick) {
            return;
        }

        $certificateAfter = (int) ($leaveType->medical_certificate_after_days ?: $policy->medical_certificate_after_days ?: 0);
        if ($certificateAfter <= 0) {
            return;
        }

        $requiresCertificate = $deductedDays > (float) $certificateAfter
            || $this->maxConsecutiveSickDaysIncludingRequest($employee, $dateRows) > $certificateAfter;

        if ($requiresCertificate && empty($payload['attachment_path']) && empty($payload['attachment'])) {
            throw ValidationException::withMessages(['attachment' => 'Medical certificate is required for this sick leave duration.']);
        }
    }

    private function maxConsecutiveSickDaysIncludingRequest(EmployeeM $employee, array $dateRows): int
    {
        $requestedDates = collect($dateRows)
            ->filter(fn (array $row) => (bool) ($row['deduct_as_leave'] ?? false))
            ->pluck('leave_date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date, 'Asia/Kolkata')->toDateString())
            ->values()
            ->all();

        if (empty($requestedDates)) {
            return 0;
        }

        $approvedSickDates = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->where('leave_requests.status', 'approved')
            ->where(function ($query) {
                $query->where('leave_types.is_sick', 1)
                    ->orWhere('leave_types.code', 'sick_leave');
            })
            ->where('leave_request_dates.sick_day', '>', 0)
            ->pluck('leave_request_dates.leave_date')
            ->map(fn ($date) => Carbon::parse($date, 'Asia/Kolkata')->toDateString())
            ->all();

        $allDates = collect(array_merge($approvedSickDates, $requestedDates))
            ->unique()
            ->sort()
            ->values();

        $maxRun = 0;
        $currentRun = 0;
        $prev = null;
        foreach ($allDates as $date) {
            $current = Carbon::parse($date, 'Asia/Kolkata');
            if ($prev && $prev->copy()->addDay()->isSameDay($current)) {
                $currentRun++;
            } else {
                $currentRun = 1;
            }
            $maxRun = max($maxRun, $currentRun);
            $prev = $current;
        }

        return $maxRun;
    }

    private function applySplitToDates(array $dateRows, float $unit, float $paid, float $sick, float $compOff, float $lwp): array
    {
        foreach ($dateRows as &$row) {
            $row['paid_day'] = 0;
            $row['sick_day'] = 0;
            $row['comp_off_day'] = 0;
            $row['lwp_day'] = 0;

            if (! $row['deduct_as_leave']) {
                continue;
            }

            $remaining = $unit;
            $take = min($remaining, $paid);
            $row['paid_day'] = $take;
            $paid = round($paid - $take, 2);
            $remaining = round($remaining - $take, 2);

            $take = min($remaining, $sick);
            $row['sick_day'] = $take;
            $sick = round($sick - $take, 2);
            $remaining = round($remaining - $take, 2);

            $take = min($remaining, $compOff);
            $row['comp_off_day'] = $take;
            $compOff = round($compOff - $take, 2);
            $remaining = round($remaining - $take, 2);

            $take = min($remaining, $lwp);
            $row['lwp_day'] = $take;
            $lwp = round($lwp - $take, 2);
        }

        return $dateRows;
    }
}
