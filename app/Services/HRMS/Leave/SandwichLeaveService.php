<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveTypeM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SandwichLeaveService
{
    public function __construct(
        private LeavePolicyService $policyService,
        private WeekoffHolidayService $weekoffHolidayService
    ) {
    }

    public function buildDates(
        EmployeeM $employee, 
        Carbon|string $startDate, 
        Carbon|string $endDate, 
        ?LeaveTypeM $leaveType = null, 
        ?int $excludeRequestId = null,
        bool $isHalfDay = false
    ): array {
        $start = $startDate instanceof Carbon 
            ? $startDate->copy()->timezone('Asia/Kolkata')->startOfDay() 
            : Carbon::parse($startDate, 'Asia/Kolkata')->startOfDay();
        $end = $endDate instanceof Carbon 
            ? $endDate->copy()->timezone('Asia/Kolkata')->startOfDay() 
            : Carbon::parse($endDate, 'Asia/Kolkata')->startOfDay();

        $policy = $this->policyService->requireForEmployee($employee, $start);

        $sandwichEnabled = (bool) ($policy?->sandwich_enabled ?? true);
        $weekoffIncludedInSandwich = (bool) ($policy?->weekoff_included_in_sandwich ?? true);
        $holidayIncludedInSandwich = (bool) ($policy?->holiday_included_in_sandwich ?? true);

        $dateInfoMap = [];

        // Build requested date rows
        $requestedRows = [];
        $requestedDateStrs = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateStr = $cursor->toDateString();
            $info = $this->getDayInfo($cursor, $dateInfoMap);
            $requestedDateStrs[] = $dateStr;
            $requestedRows[$dateStr] = [
                'leave_date' => $dateStr,
                'day_name' => $cursor->format('l'),
                'is_working_day' => (bool) $info['is_working_day'],
                'is_weekoff' => (bool) $info['is_weekoff'],
                'is_holiday' => (bool) $info['is_holiday'],
                'is_sandwich_day' => false,
                'deduct_as_leave' => (bool) $info['is_working_day'],
                'leave_type_code' => $leaveType?->code,
            ];
            $cursor->addDay();
        }

        // Rule: Sandwich does not apply if disabled, or if neither weekoff nor holiday is included.
        // Rule: "A half day leave day is treated as PRESENT (the employee worked half the day).
        //        A half day on either side breaks the sandwich, so no sandwich is applied.
        //        A half day working day (even if the other half is leave) also does not trigger a sandwich."
        // Hence, if the current application is a half-day leave, it CANNOT trigger or be part of any sandwich!
        if (!$sandwichEnabled || $isHalfDay || (!$weekoffIncludedInSandwich && !$holidayIncludedInSandwich)) {
            return array_values($requestedRows);
        }

        // Fetch existing qualifying FULL DAY leave dates (approved or pending)
        $qualifyingFullDayDates = $this->getQualifyingFullDayLeaveDates($employee, $excludeRequestId);

        // Requested full day working dates
        $requestedFullDayDates = [];
        foreach ($requestedRows as $dStr => $row) {
            if ($row['is_working_day']) {
                $requestedFullDayDates[] = $dStr;
            }
        }

        // Full-day leave set = requested full day dates + existing qualifying full-day leave dates
        $fullDayLeaveSet = array_flip(array_unique(array_merge($requestedFullDayDates, $qualifyingFullDayDates)));

        // Dates already deducted in existing approved/pending requests (so we don't re-deduct them as sandwich)
        $alreadyDeductedDates = $this->getAlreadyDeductedDates($employee, $excludeRequestId);

        // Identify non-working blocks touching or enclosed by active leave set
        $sandwichRows = [];

        // Search evaluation range starts before $start and goes beyond $end
        $evalStart = $start->copy()->subDays(1);
        $evalEnd = $end->copy()->addDays(1);

        $evalCursor = $evalStart->copy();
        $visitedNonWorkingDates = [];

        while ($evalCursor->lte($evalEnd)) {
            $info = $this->getDayInfo($evalCursor, $dateInfoMap);
            $dateStr = $evalCursor->toDateString();

            if (!$info['is_working_day'] && !isset($visitedNonWorkingDates[$dateStr])) {
                $block = $this->findContiguousNonWorkingBlock($evalCursor, $dateInfoMap);
                foreach ($block['dates'] as $dStr) {
                    $visitedNonWorkingDates[$dStr] = true;
                }

                $leftWorkingDateStr = $block['left_working_date'];
                $rightWorkingDateStr = $block['right_working_date'];

                // Sandwich rule: FULL DAY leave must be present on the working day immediately before AND immediately after
                $leftHasFullDayLeave = isset($fullDayLeaveSet[$leftWorkingDateStr]);
                $rightHasFullDayLeave = isset($fullDayLeaveSet[$rightWorkingDateStr]);

                if ($leftHasFullDayLeave && $rightHasFullDayLeave) {
                    // Rule: "Consecutive off/holiday days (e.g., Sunday + Monday holiday) are treated as one block.
                    // The sandwich applies to the entire block or not at all."
                    foreach ($block['dates'] as $nDateStr) {
                        // Skip if date was already deducted in an existing approved request
                        if (isset($alreadyDeductedDates[$nDateStr]) && !isset($requestedRows[$nDateStr])) {
                            continue;
                        }

                        $nDate = Carbon::parse($nDateStr, 'Asia/Kolkata');
                        $nInfo = $this->getDayInfo($nDate, $dateInfoMap);

                        $includeWeekoff = $nInfo['is_weekoff'] && $weekoffIncludedInSandwich;
                        $includeHoliday = $nInfo['is_holiday'] && $holidayIncludedInSandwich;

                        if ($includeWeekoff || $includeHoliday) {
                            $sandwichRows[$nDateStr] = [
                                'leave_date' => $nDateStr,
                                'day_name' => $nDate->format('l'),
                                'is_working_day' => false,
                                'is_weekoff' => (bool) $nInfo['is_weekoff'],
                                'is_holiday' => (bool) $nInfo['is_holiday'],
                                'is_sandwich_day' => true,
                                'deduct_as_leave' => true,
                                'leave_type_code' => $leaveType?->code,
                            ];
                        }
                    }
                }
            }

            $evalCursor->addDay();
        }

        // Merge requested rows and derived sandwich rows
        $allRows = array_merge($requestedRows, $sandwichRows);

        // Update any requested dates if they were determined to be sandwich days
        foreach ($allRows as $dStr => &$row) {
            if (isset($sandwichRows[$dStr])) {
                $row['is_sandwich_day'] = true;
                $row['deduct_as_leave'] = true;
            }
        }
        unset($row);

        ksort($allRows);

        return array_values($allRows);
    }

    private function getDayInfo(Carbon $date, array &$dateInfoMap): array
    {
        $dateStr = $date->toDateString();
        if (!isset($dateInfoMap[$dateStr])) {
            $dateInfoMap[$dateStr] = $this->weekoffHolidayService->dayInfo($date);
        }
        return $dateInfoMap[$dateStr];
    }

    private function getQualifyingFullDayLeaveDates(EmployeeM $employee, ?int $excludeRequestId): array
    {
        // Only FULL-DAY approved or pending leave requests qualify for sandwich triggers
        $datesFromDatesTable = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->whereIn('leave_requests.status', ['approved', 'pending'])
            ->where('leave_requests.is_half_day', 0)
            ->where('leave_request_dates.deduct_as_leave', 1)
            ->when($excludeRequestId, fn($q) => $q->where('leave_requests.id', '<>', $excludeRequestId))
            ->pluck('leave_request_dates.leave_date')
            ->map(fn($d) => Carbon::parse($d, 'Asia/Kolkata')->toDateString())
            ->toArray();

        $datesFromRequestsTable = [];
        $existingRequests = DB::table('leave_requests')
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'pending'])
            ->where('is_half_day', 0)
            ->when($excludeRequestId, fn($q) => $q->where('id', '<>', $excludeRequestId))
            ->get(['start_date', 'end_date']);

        foreach ($existingRequests as $req) {
            if (!$req->start_date || !$req->end_date) {
                continue;
            }
            $c = Carbon::parse($req->start_date, 'Asia/Kolkata');
            $e = Carbon::parse($req->end_date, 'Asia/Kolkata');
            while ($c->lte($e)) {
                $datesFromRequestsTable[] = $c->toDateString();
                $c->addDay();
            }
        }

        return array_values(array_unique(array_merge($datesFromDatesTable, $datesFromRequestsTable)));
    }

    private function getAlreadyDeductedDates(EmployeeM $employee, ?int $excludeRequestId): array
    {
        $dates = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->whereIn('leave_requests.status', ['approved', 'pending'])
            ->where('leave_request_dates.deduct_as_leave', 1)
            ->when($excludeRequestId, fn($q) => $q->where('leave_requests.id', '<>', $excludeRequestId))
            ->pluck('leave_request_dates.leave_date')
            ->map(fn($d) => Carbon::parse($d, 'Asia/Kolkata')->toDateString())
            ->toArray();

        return array_flip($dates);
    }

    private function findContiguousNonWorkingBlock(Carbon $startDate, array &$dateInfoMap): array
    {
        $blockDates = [];

        // Expand backward to find left working boundary
        $leftCursor = $startDate->copy();
        while (true) {
            $info = $this->getDayInfo($leftCursor, $dateInfoMap);
            if ($info['is_working_day']) {
                $leftWorkingDate = $leftCursor->toDateString();
                break;
            }
            $leftCursor->subDay();
        }

        // Expand forward to find right working boundary
        $rightCursor = $startDate->copy();
        while (true) {
            $info = $this->getDayInfo($rightCursor, $dateInfoMap);
            if ($info['is_working_day']) {
                $rightWorkingDate = $rightCursor->toDateString();
                break;
            }
            $rightCursor->addDay();
        }

        // Collect all non-working dates between left working date and right working date
        $collector = Carbon::parse($leftWorkingDate, 'Asia/Kolkata')->addDay();
        $rightBound = Carbon::parse($rightWorkingDate, 'Asia/Kolkata');

        while ($collector->lt($rightBound)) {
            $blockDates[] = $collector->toDateString();
            $collector->addDay();
        }

        return [
            'dates' => $blockDates,
            'left_working_date' => $leftWorkingDate,
            'right_working_date' => $rightWorkingDate,
        ];
    }
}

