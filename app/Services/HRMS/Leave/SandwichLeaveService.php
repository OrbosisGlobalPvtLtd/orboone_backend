<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveTypeM;
use Carbon\Carbon;

class SandwichLeaveService
{
    public function __construct(
        private LeavePolicyService $policyService,
        private WeekoffHolidayService $weekoffHolidayService
    ) {
    }

    public function buildDates(EmployeeM $employee, Carbon|string $startDate, Carbon|string $endDate, ?LeaveTypeM $leaveType = null): array
    {
        $start = $startDate instanceof Carbon ? $startDate->copy() : Carbon::parse($startDate, 'Asia/Kolkata');
        $end = $endDate instanceof Carbon ? $endDate->copy() : Carbon::parse($endDate, 'Asia/Kolkata');
        $policy = $this->policyService->forEmployee($employee, $start);

        $rows = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $info = $this->weekoffHolidayService->dayInfo($cursor);
            $rows[$cursor->toDateString()] = [
                'leave_date' => $cursor->toDateString(),
                'day_name' => $cursor->format('l'),
                'is_working_day' => $info['is_working_day'],
                'is_weekoff' => $info['is_weekoff'],
                'is_holiday' => $info['is_holiday'],
                'is_sandwich_day' => false,
                'deduct_as_leave' => (bool) $info['is_working_day'],
                'leave_type_code' => $leaveType?->code,
            ];
            $cursor->addDay();
        }

        if (! $policy->sandwich_enabled) {
            return array_values($rows);
        }

        $workingLeaveDates = collect($rows)
            ->filter(fn ($row) => $row['is_working_day'])
            ->keys()
            ->values();

        if ($workingLeaveDates->count() < 2) {
            return array_values($rows);
        }

        $first = Carbon::parse($workingLeaveDates->first(), 'Asia/Kolkata');
        $last = Carbon::parse($workingLeaveDates->last(), 'Asia/Kolkata');

        // A non-working day becomes sandwich leave only when real leave exists on both sides.
        $cursor = $first->copy();
        while ($cursor->lte($last)) {
            $key = $cursor->toDateString();
            if (isset($rows[$key]) && ! $rows[$key]['is_working_day']) {
                $includeWeekoff = $rows[$key]['is_weekoff'] && $policy->weekoff_included_in_sandwich;
                $includeHoliday = $rows[$key]['is_holiday'] && $policy->holiday_included_in_sandwich;
                if ($includeWeekoff || $includeHoliday) {
                    $rows[$key]['is_sandwich_day'] = true;
                    $rows[$key]['deduct_as_leave'] = true;
                }
            }
            $cursor->addDay();
        }

        return array_values($rows);
    }
}
