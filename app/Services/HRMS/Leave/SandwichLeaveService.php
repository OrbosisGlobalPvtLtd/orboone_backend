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
        ?int $excludeRequestId = null
    ): array {
        $start = $startDate instanceof Carbon 
            ? $startDate->copy()->timezone('Asia/Kolkata')->startOfDay() 
            : Carbon::parse($startDate, 'Asia/Kolkata')->startOfDay();
        $end = $endDate instanceof Carbon 
            ? $endDate->copy()->timezone('Asia/Kolkata')->startOfDay() 
            : Carbon::parse($endDate, 'Asia/Kolkata')->startOfDay();

        $policy = $this->policyService->forEmployee($employee, $start);

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

        if (!$sandwichEnabled) {
            return array_values($requestedRows);
        }

        // Fetch existing qualifying leave dates (approved or pending)
        $qualifyingLeaveDates = $this->getQualifyingLeaveDates($employee, $excludeRequestId);

        // Active leave set = requested dates + existing qualifying leave dates
        $activeLeaveSet = array_flip(array_unique(array_merge($requestedDateStrs, $qualifyingLeaveDates)));

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

                $leftHasLeave = isset($activeLeaveSet[$leftWorkingDateStr]);
                $rightHasLeave = isset($activeLeaveSet[$rightWorkingDateStr]);

                if ($leftHasLeave && $rightHasLeave) {
                    foreach ($block['dates'] as $nDateStr) {
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

    private function getQualifyingLeaveDates(EmployeeM $employee, ?int $excludeRequestId): array
    {
        $datesFromDatesTable = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->whereIn('leave_requests.status', ['approved', 'pending'])
            ->where('leave_request_dates.deduct_as_leave', 1)
            ->when($excludeRequestId, fn($q) => $q->where('leave_requests.id', '<>', $excludeRequestId))
            ->pluck('leave_request_dates.leave_date')
            ->map(fn($d) => Carbon::parse($d, 'Asia/Kolkata')->toDateString())
            ->toArray();

        $datesFromRequestsTable = [];
        $existingRequests = DB::table('leave_requests')
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'pending'])
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

