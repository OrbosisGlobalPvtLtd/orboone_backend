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

    public function buildDates(EmployeeM $employee, Carbon|string $startDate, Carbon|string $endDate, ?LeaveTypeM $leaveType = null, ?int $excludeRequestId = null): array
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

        // Fetch all approved leave dates of this employee to check adjacent boundary conditions
        $existingLeaveDates = \Illuminate\Support\Facades\DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->where('leave_requests.status', 'approved')
            ->where('leave_request_dates.deduct_as_leave', 1)
            ->when($excludeRequestId, fn($q) => $q->where('leave_requests.id', '<>', $excludeRequestId))
            ->pluck('leave_request_dates.leave_date')
            ->map(fn($d) => Carbon::parse($d, 'Asia/Kolkata')->toDateString())
            ->toArray();

        foreach ($rows as $key => &$row) {
            if ($row['is_working_day']) {
                continue;
            }

            $currentDate = Carbon::parse($key, 'Asia/Kolkata');

            $leftHasLeave = $this->hasLeaveOnLeft($employee, $currentDate, $start, $end, $existingLeaveDates);
            $rightHasLeave = $this->hasLeaveOnRight($employee, $currentDate, $start, $end, $existingLeaveDates);

            if ($leftHasLeave && $rightHasLeave) {
                $includeWeekoff = $row['is_weekoff'] && $policy->weekoff_included_in_sandwich;
                $includeHoliday = $row['is_holiday'] && $policy->holiday_included_in_sandwich;
                if ($includeWeekoff || $includeHoliday) {
                    $row['is_sandwich_day'] = true;
                    $row['deduct_as_leave'] = true;
                }
            }
        }

        return array_values($rows);
    }

    private function hasLeaveOnLeft(EmployeeM $employee, Carbon $date, Carbon $start, Carbon $end, array $existingLeaveDates): bool
    {
        $cursor = $date->copy()->subDay();
        $iterations = 0;
        while ($iterations++ < 30) {
            $info = $this->weekoffHolidayService->dayInfo($cursor);
            if ($info['is_working_day']) {
                if ($cursor->gte($start) && $cursor->lte($end)) {
                    return true;
                }
                if (in_array($cursor->toDateString(), $existingLeaveDates, true)) {
                    return true;
                }
                return false;
            }
            $cursor->subDay();
        }
        return false;
    }

    private function hasLeaveOnRight(EmployeeM $employee, Carbon $date, Carbon $start, Carbon $end, array $existingLeaveDates): bool
    {
        $cursor = $date->copy()->addDay();
        $iterations = 0;
        while ($iterations++ < 30) {
            $info = $this->weekoffHolidayService->dayInfo($cursor);
            if ($info['is_working_day']) {
                if ($cursor->gte($start) && $cursor->lte($end)) {
                    return true;
                }
                if (in_array($cursor->toDateString(), $existingLeaveDates, true)) {
                    return true;
                }
                return false;
            }
            $cursor->addDay();
        }
        return false;
    }
}
