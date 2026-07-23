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
        
        // Leave Advance Notice Validation
        $isSick = (bool) $leaveType->is_sick;
        $isLwp = (bool) $leaveType->is_lwp;
        $isEmergency = (bool) ($payload['emergency_leave'] ?? false);

        if (! $isSick && ! $isLwp && ! $isEmergency) {
            $today = Carbon::now('Asia/Kolkata')->startOfDay();
            $startDate = Carbon::parse($payload['start_date'], 'Asia/Kolkata')->startOfDay();
            if ($today->diffInDays($startDate, false) < 2) {
                throw ValidationException::withMessages([
                    'start_date' => 'Normal leaves must be applied at least 2 days in advance.'
                ]);
            }
        }

        // Sick leave certificate validation
        $requiresMedicalCertificate = false;
        if ($leaveType->is_sick) {
            $consecutiveCount = $this->maxConsecutiveSickDaysIncludingRequest($employee, $dateRows, $existingRequest?->id);
            if ($consecutiveCount > 2) {
                $requiresMedicalCertificate = true;
                if (empty($payload['attachment_path']) && empty($payload['attachment'])) {
                    throw ValidationException::withMessages([
                        'attachment' => 'Medical certificate is required for more than 2 consecutive sick leave days.'
                    ]);
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
        $paid = 0.0;
        $sick = 0.0;
        $compOff = 0.0;
        $lwp = 0.0;

        $paidCapacity = (float) $allocation->paid_remaining;
        $sickCapacity = (float) $allocation->sick_remaining;
        $compCapacity = (float) $allocation->comp_off_remaining;

        // confirmation check
        if (! $employee->is_permanent) {
            $paidCapacity = 0.0;
            $sickCapacity = 0.0;
            $compCapacity = 0.0;
        }

        // November & December rule
        if (in_array((int) $start->month, [11, 12], true)) {
            $remaining = (float) $allocation->total_remaining;
            if ($remaining > 10.0) {
                $allowed = round($remaining * 0.5, 2);
                $paidCapacity = min($paidCapacity, $allowed);
                $sickCapacity = min($sickCapacity, $allowed);
                $compCapacity = min($compCapacity, $allowed);
            }
        }

        // For Comp-Off, validate balance at the beginning
        if ($leaveType->is_comp_off) {
            if ($compCapacity <= 0) {
                throw ValidationException::withMessages([
                    'leave_type_id' => 'Comp-Off balance is not available.'
                ]);
            }
            // Check pending holiday work requests as in original code
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

        $monthlyPlUsed = [];

        foreach ($dateRows as $row) {
            if (! $row['deduct_as_leave']) {
                continue;
            }

            $deductCount = count(array_filter($dateRows, fn($r) => $r['deduct_as_leave']));
            $dayUnit = $deductCount > 0 ? ($deductedDays / $deductCount) : 1.0;

            $d = Carbon::parse($row['leave_date'], 'Asia/Kolkata');
            $monthKey = $d->format('Y-m');

            if ($leaveType->is_lwp) {
                $lwp += $dayUnit;
            } elseif ($leaveType->is_comp_off) {
                $allocatedComp = min($dayUnit, $compCapacity);
                $compOff += $allocatedComp;
                $compCapacity = round($compCapacity - $allocatedComp, 2);
                $lwp += round($dayUnit - $allocatedComp, 2);
            } elseif ($leaveType->is_sick) {
                $allocatedSick = min($dayUnit, $sickCapacity);
                $sick += $allocatedSick;
                $sickCapacity = round($sickCapacity - $allocatedSick, 2);
                $lwp += round($dayUnit - $allocatedSick, 2);
            } else {
                // Paid Leave
                if (! isset($monthlyPlUsed[$monthKey])) {
                    $used = DB::table('leave_request_dates')
                        ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
                        ->where('leave_request_dates.employee_id', $employee->id)
                        ->where('leave_requests.status', 'approved')
                        ->whereMonth('leave_request_dates.leave_date', $d->month)
                        ->whereYear('leave_request_dates.leave_date', $d->year)
                        ->where('leave_request_dates.deduct_as_leave', 1)
                        ->when($excludeRequestId, fn($q) => $q->where('leave_requests.id', '<>', $excludeRequestId))
                        ->sum('leave_request_dates.paid_day');
                    $monthlyPlUsed[$monthKey] = (float) $used;
                }

                $monthlyAvailable = max(0.0, 2.0 - $monthlyPlUsed[$monthKey]);
                $allocatedPaid = min($dayUnit, $paidCapacity, $monthlyAvailable);

                $paid += $allocatedPaid;
                $paidCapacity = round($paidCapacity - $allocatedPaid, 2);
                $monthlyPlUsed[$monthKey] = round($monthlyPlUsed[$monthKey] + $allocatedPaid, 2);
                $lwp += round($dayUnit - $allocatedPaid, 2);
            }
        }

        return [round($paid, 2), round($sick, 2), round($compOff, 2), round($lwp, 2)];
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
