<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeavePolicyCalculatorS
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

        // Build dates including advanced sandwich checks
        $dateRows = $this->sandwichLeaveService->buildDates($employee, $start, $end, $leaveType, $existingRequest?->id);
        
        $requestedCalendarDays = $start->diffInDays($end) + 1;
        $workingDays = 0;
        $sandwichDays = 0;
        $weekendHolidayCounted = 0;
        $deductedDays = 0.0;

        foreach ($dateRows as $row) {
            if ($row['is_working_day']) {
                $workingDays++;
            }
            if ($row['is_sandwich_day']) {
                $sandwichDays++;
            }
            if (($row['is_weekoff'] || $row['is_holiday']) && $row['deduct_as_leave']) {
                $weekendHolidayCounted++;
            }
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
        
        // Sick leave certificate validation
        $requiresMedicalCertificate = false;
        if ($leaveType->is_sick) {
            $certificateAfter = (int) ($leaveType->medical_certificate_after_days ?: $policy->medical_certificate_after_days ?: 0);
            if ($certificateAfter > 0) {
                $consecutiveCount = $this->maxConsecutiveSickDaysIncludingRequest($employee, $dateRows, $existingRequest?->id);
                if ($consecutiveCount > $certificateAfter) {
                    $requiresMedicalCertificate = true;
                    if (empty($payload['attachment_path']) && empty($payload['attachment'])) {
                        throw ValidationException::withMessages([
                            'attachment' => 'Medical certificate is required for more than 2 consecutive sick leave days.'
                        ]);
                    }
                }
            }
        }

        // Split days into categories
        [$paid, $sick, $compOff, $lwp] = $this->splitDays(
            $employee,
            $leaveType,
            $policy,
            $allocation,
            $start,
            $dateRows,
            $deductedDays,
            $existingRequest?->id
        );

        $dateRows = $this->applySplitToDates($dateRows, $isHalfDay ? 0.5 : 1.0, $paid, $sick, $compOff, $lwp);

        // Calculate balance after split
        $balanceAfterSplit = [
            'paid_remaining' => round(max(0, (float) $allocation->paid_remaining - $paid), 2),
            'sick_remaining' => round(max(0, (float) $allocation->sick_remaining - $sick), 2),
            'comp_off_remaining' => round(max(0, (float) $allocation->comp_off_remaining - $compOff), 2),
            'total_remaining' => round(max(0, (float) $allocation->total_remaining - $paid - $sick - $compOff), 2),
        ];

        return [
            'policy' => $policy,
            'allocation' => $allocation,
            'dates' => $dateRows,
            'requested_calendar_days' => $requestedCalendarDays,
            'working_days' => $workingDays,
            'sandwich_days' => $sandwichDays,
            'weekend_holiday_counted' => $weekendHolidayCounted,
            'requested_days' => $deductedDays,
            'deducted_days' => $deductedDays,
            'sandwich_applied' => $sandwichDays > 0,
            'paid_days' => $paid,
            'sick_days' => $sick,
            'comp_off_days' => $compOff,
            'lwp_days' => $lwp,
            'requires_medical_certificate' => $requiresMedicalCertificate,
            'rejection_reason' => null,
            'balance_after_split' => $balanceAfterSplit,
        ];
    }

    private function splitDays(
        EmployeeM $employee, 
        LeaveTypeM $leaveType, 
        $policy, 
        $allocation, 
        Carbon $start, 
        array $dateRows, 
        float $deductedDays,
        ?int $excludeRequestId = null
    ): array
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

        // Apply Monthly paid leave carry forward rule for Paid Leave type
        if ($leaveType->is_paid || $leaveType->code === 'paid_leave') {
            $allocationStartMonth = $allocation->allocation_from_date ? $allocation->allocation_from_date->month : 1;
            $creditedMonths = max(1, $start->month - $allocationStartMonth + 1);
            $creditedPaid = $creditedMonths * 2;

            $usedPaidBefore = DB::table('leave_request_dates')
                ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
                ->where('leave_request_dates.employee_id', $employee->id)
                ->where('leave_requests.status', 'approved')
                ->where('leave_request_dates.leave_date', '<', $start->toDateString())
                ->when($excludeRequestId, fn($q) => $q->where('leave_requests.id', '<>', $excludeRequestId))
                ->sum('leave_request_dates.paid_day');

            $paidCapacity = max(0.0, min((float) $allocation->paid_allocated, $creditedPaid - $usedPaidBefore));
            $paidCapacity = min($paidCapacity, (float) $allocation->paid_remaining);
        }

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
                $allowed = round($remaining * ((float) $policy->nov_dec_usage_percentage / 100), 2);
                $paidCapacity = min($paidCapacity, $allowed);
                $sickCapacity = min($sickCapacity, $allowed);
                $compCapacity = min($compCapacity, $allowed);
            }
        }

        $monthlyAvailable = $this->remainingMonthlyLimit($employee, $policy, $dateRows);
        if (! ($policy->allow_monthly_balance_accumulation || $policy->carry_forward_enabled)) {
            $paidCapacity = min($paidCapacity, $monthlyAvailable);
        }
        $sickCapacity = min($sickCapacity, $monthlyAvailable);
        $compCapacity = min($compCapacity, $monthlyAvailable);

        $paid = 0.0;
        $sick = 0.0;
        $compOff = 0.0;

        if ($leaveType->is_comp_off) {
            if ($compCapacity < $deductedDays) {
                throw ValidationException::withMessages([
                    'leave_type_id' => 'Comp Off balance is not available.'
                ]);
            }
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

    private function maxConsecutiveSickDaysIncludingRequest(EmployeeM $employee, array $dateRows, ?int $excludeRequestId = null): int
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
            ->when($excludeRequestId, fn($q) => $q->where('leave_requests.id', '<>', $excludeRequestId))
            ->pluck('leave_request_dates.leave_date')
            ->map(fn ($date) => Carbon::parse($date, 'Asia/Kolkata')->toDateString())
            ->all();

        $allSickSet = array_flip(array_merge($approvedSickDates, $requestedDates));

        $first = Carbon::parse(min($requestedDates), 'Asia/Kolkata');
        $last = Carbon::parse(max($requestedDates), 'Asia/Kolkata');

        $leftCount = 0;
        $cursor = $first->copy()->subDay();
        while (isset($allSickSet[$cursor->toDateString()])) {
            $leftCount++;
            $cursor->subDay();
        }

        $rightCount = 0;
        $cursor = $last->copy()->addDay();
        while (isset($allSickSet[$cursor->toDateString()])) {
            $rightCount++;
            $cursor->addDay();
        }

        $requestedSickCount = count($requestedDates);

        return $leftCount + $requestedSickCount + $rightCount;
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
