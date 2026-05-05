<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Leave\LeaveAllocationM as LeaveAllocation;
use Carbon\Carbon;

class LeaveS
{
    public function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $holidays = \App\Models\HRMS\Leave\NationalHolidayM::whereBetween('holiday_date', [
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
        ])->pluck('holiday_date')->toArray();

        $count = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (
                $current->dayOfWeek !== Carbon::SUNDAY
                && ! in_array($current->format('Y-m-d'), $holidays, true)
            ) {
                $count++;
            }

            $current->addDay();
        }

        return $count;
    }

    public function calculateAllocationForEmployee(Employee $employee, int $year): ?LeaveAllocation
    {
        $joiningDateValue = $employee->joining_date ?? $employee->start_of_contract ?? null;

        if (! $joiningDateValue) {
            return null;
        }

        $joiningDate = Carbon::parse($joiningDateValue);

        if ($joiningDate->year > $year) {
            return $this->saveAllocation((int) $employee->id, $year, 0, 0);
        }

        $isPermanent = $this->isEmployeePermanent($employee, $year);

        if (($employee->employee_stage ?? null) === 'internship' || ! $isPermanent) {
            return $this->saveAllocation((int) $employee->id, $year, 1, 0);
        }

        $accrualMonthCount = 12;

        if ($joiningDate->year === $year) {
            $month = (int) $joiningDate->month;
            $day = (int) $joiningDate->day;
            $accrualMonthCount = (12 - $month) + ($day === 1 ? 1 : 0);
        }

        $totalPl = $accrualMonthCount * 1.5;
        $totalSl = round($accrualMonthCount * (7 / 12), 2);

        return $this->saveAllocation((int) $employee->id, $year, $totalPl, $totalSl);
    }

    private function isEmployeePermanent(Employee $employee, int $year): bool
    {
        $stage = strtolower((string) ($employee->employee_stage ?? ''));
        $probationStatus = strtolower((string) ($employee->probation_status ?? ''));
        $employmentType = strtolower((string) ($employee->employment_type ?? ''));

        if ($stage === 'permanent' || in_array($probationStatus, ['permanent', 'completed', 'confirmed'], true)) {
            return true;
        }

        if ($stage === 'probation' || in_array($probationStatus, ['probation', 'ongoing', 'pending'], true)) {
            if ($employee->probation_end_date) {
                $probationEnd = Carbon::parse($employee->probation_end_date);
                if ($probationEnd->year < $year) {
                    return true;
                }
            }

            return false;
        }

        if ($stage === 'internship') {
            return false;
        }

        return in_array($employmentType, ['full_time', 'part_time'], true);
    }

    private function saveAllocation(int $employeeId, int $year, float $pl, float $sl): LeaveAllocation
    {
        $allocation = LeaveAllocation::firstOrNew([
            'employee_id' => $employeeId,
            'year' => $year,
        ]);

        $allocation->total_pl = $pl;
        $allocation->total_sl = $sl;

        if (! $allocation->exists) {
            $allocation->used_pl = 0;
            $allocation->used_sl = 0;
            $allocation->lwp_days = 0;
        }

        $allocation->save();

        return $allocation;
    }
}
