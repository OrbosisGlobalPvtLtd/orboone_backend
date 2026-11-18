<?php

namespace App\Services\HRMS\EnterprisePayroll;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\MonthlyAttendanceSummaryM;
use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use App\Services\HRMS\Attendance\AttendancePayableDayResolver;

class EnterpriseAttendanceLeaveResolverS
{
    public function resolve(EmployeeM $employee, int $month, int $year): array
    {
        $summary = MonthlyAttendanceSummaryM::query()
            ->where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($summary && $summary->is_locked) {
            return $this->fromMonthlySummary($summary, $employee);
        }

        return $this->fromAttendancesAndApprovedLeaves($employee, $month, $year);
    }

    private function fromMonthlySummary(MonthlyAttendanceSummaryM $summary, ?EmployeeM $employee = null): array
    {
        $employee = $employee ?: EmployeeM::find($summary->employee_id);
        $tableHolidays = $employee ? $this->activeHolidayDays($employee, (int) $summary->month, (int) $summary->year) : 0.0;
        $holidayDays = $summary->is_locked
            ? (float) $summary->holiday_days
            : $tableHolidays;

        $saturdayOffDays = 0.0;
        $sundayOffDays = 0.0;
        if ($employee && Schema::hasTable('attendances')) {
            $start = Carbon::create($summary->year, $summary->month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $weekOffDates = AttendanceM::query()
                ->where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
                ->where(function ($q) {
                    $q->whereHas('attendanceType', fn ($sub) => $sub->where('code', 'like', '%week%'))
                        ->orWhere('attendance_status', 'like', '%week%');
                })
                ->pluck('attendance_date');

            foreach ($weekOffDates as $d) {
                $c = Carbon::parse($d);
                if ($c->isSaturday()) $saturdayOffDays += 1.0;
                elseif ($c->isSunday()) $sundayOffDays += 1.0;
            }
        }

        $days = [
            'present_days' => (float) $summary->present_days,
            'paid_leave_days' => (float) $summary->paid_leave_days,
            'sick_leave_days' => (float) $summary->sick_leave_days,
            'comp_off_days' => (float) $summary->comp_off_days,
            'holiday_days' => $holidayDays,
            'week_off_days' => (float) $summary->week_off_days,
            'half_days' => (float) $summary->half_days,
            'lwp_days' => (float) $summary->lwp_days,
            'absent_days' => (float) $summary->absent_days,
            'late_count' => (int) $summary->late_count,
            'early_out_count' => (int) $summary->early_out_count,
            'missed_punch_count' => (int) $summary->missed_punch_count,
        ];

        $actualPresentDays = (float) $days['present_days'];
        $rawDisplayedPresentDays = round($actualPresentDays + $days['holiday_days'] + $saturdayOffDays, 2);
        $displayedPresentDays = min(26.0, $rawDisplayedPresentDays);
        $days['actual_present_days'] = $actualPresentDays;
        $days['displayed_present_days'] = $displayedPresentDays;
        $days['saturday_off_days'] = $saturdayOffDays;
        $days['sunday_off_days'] = $sundayOffDays;

        $totalWorkingDays = $this->totalWorkingDays($days);
        $this->guardWorkingDays($totalWorkingDays);
        $calendarDays = Carbon::create($summary->year, $summary->month, 1)->daysInMonth;

        $payableDays = $summary->is_locked && (float) $summary->payable_days > 0 ? (float) $summary->payable_days : $this->payableDays($days);
        $days['present_days'] = $displayedPresentDays;

        return $days + [
            'total_working_days' => $totalWorkingDays,
            'payable_days' => $payableDays,
            'unpaid_days' => $this->unpaidDays($days),
            'calendar_days' => $calendarDays,
            'source' => 'monthly_attendance_summaries',
        ];
    }

    private function fromAttendancesAndApprovedLeaves(EmployeeM $employee, int $month, int $year): array
    {
        if (! Schema::hasTable('attendances')) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance summary is unavailable and attendance records cannot be calculated.',
            ]);
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $attendances = AttendanceM::query()
            ->with('attendanceType')
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        if ($attendances->isEmpty()) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance summary is unavailable and attendances cannot be calculated.',
            ]);
        }

        $days = [
            'present_days' => 0.0,
            'paid_leave_days' => 0.0,
            'sick_leave_days' => 0.0,
            'comp_off_days' => 0.0,
            'holiday_days' => 0.0,
            'week_off_days' => 0.0,
            'half_days' => 0.0,
            'lwp_days' => 0.0,
            'absent_days' => 0.0,
            'late_count' => 0,
            'early_out_count' => 0,
            'missed_punch_count' => 0,
        ];

        $saturdayOffDays = 0.0;
        $sundayOffDays = 0.0;
        $missedPunchWarningDays = 0.0;

        $payableResolver = app(AttendancePayableDayResolver::class);
        foreach ($attendances as $attendance) {
            $code = strtolower((string) optional($attendance->attendanceType)->code);
            $status = strtolower((string) $attendance->attendance_status);
            $name = strtolower((string) optional($attendance->attendanceType)->name);
            $label = trim($code . ' ' . $status . ' ' . $name);

            $attDate = Carbon::parse($attendance->attendance_date);
            $isSat = $attDate->isSaturday();
            $isSun = $attDate->isSunday();

            $days['late_count'] += $attendance->is_late ? 1 : 0;
            $days['early_out_count'] += $attendance->is_early_out ? 1 : 0;
            $days['missed_punch_count'] += ($attendance->missed_punch || $attendance->is_missed_punch) ? 1 : 0;

            $compOffType = $payableResolver->compOffWorkedDayType($attendance);
            if ($compOffType !== null) {
                if ($compOffType === 'holiday') {
                    $days['holiday_days'] += 1;
                } else {
                    $days['week_off_days'] += 1;
                    if ($isSat) $saturdayOffDays += 1.0;
                    elseif ($isSun) $sundayOffDays += 1.0;
                }
            } elseif ($attendance->is_half_day || str_contains($label, 'half')) {
                if ($attendance->leave_request_id) {
                    // Employee worked half day AND took approved half-day leave (PL/SL/LOP).
                    // The leave portion is accounted for via approvedLeaveDays() below.
                    // The worked half MUST be credited as 0.5 present day so the day is fully covered!
                    $days['present_days'] += 0.5;
                } else {
                    $days['half_days'] += 1;
                }
            } elseif ($attendance->leave_request_id) {
                // Approved full-day leave records are accounted for accurately via approvedLeaveDays() below
            } elseif (str_contains($label, 'absent')) {
                $days['absent_days'] += 1;
            } elseif ($attendance->is_lwp || str_contains($label, 'lwp')) {
                $days['lwp_days'] += 1;
            } elseif (str_contains($label, 'missed_punch') || str_contains($label, 'missed')) {
                if ($attendance->is_lwp) {
                    $days['lwp_days'] += 1;
                } else {
                    $missedPunchWarningDays += 1.0;
                }
            } elseif (str_contains($label, 'holiday')) {
                $days['holiday_days'] += 1;
            } elseif (str_contains($label, 'week')) {
                $days['week_off_days'] += 1;
                if ($isSat) $saturdayOffDays += 1.0;
                elseif ($isSun) $sundayOffDays += 1.0;
            } elseif (str_contains($label, 'leave') || $attendance->leave_request_id) {
                // Synced leave records are accounted for accurately via approvedLeaveDays() below
            } else {
                $days['present_days'] += 1;
            }
        }

        $leaveDays = $this->approvedLeaveDays($employee, $month, $year);
        foreach (['paid_leave_days', 'sick_leave_days', 'comp_off_days', 'lwp_days'] as $key) {
            $days[$key] += (float) $leaveDays[$key];
        }

        $tableHolidays = $this->activeHolidayDays($employee, $month, $year);
        $days['holiday_days'] = max((float) $days['holiday_days'], $tableHolidays);

        // Missed Punch: up to 2 warning days per policy cycle are credited to Actual Present
        $creditedMp = min(2.0, (float) $missedPunchWarningDays);
        $remainingMp = max(0.0, (float) $missedPunchWarningDays - $creditedMp);

        // Actual Present Days (working present + credited warning days)
        $actualPresentDays = round((float) $days['present_days'] + $creditedMp, 2);

        // Required Formula: Present Days (Credited) = Actual Present Days + Paid Holidays + Eligible Saturday Weekly Offs
        $rawDisplayedPresentDays = round($actualPresentDays + (float) $days['holiday_days'] + $saturdayOffDays, 2);
        // Under fixed salary base of 26 days, Present days shown on payslip cannot exceed the 26-day base
        $displayedPresentDays = min(26.0, $rawDisplayedPresentDays);

        $days['actual_present_days'] = $actualPresentDays;
        $days['displayed_present_days'] = $displayedPresentDays;
        $days['remaining_mp_days'] = $remainingMp;
        $days['saturday_off_days'] = $saturdayOffDays;
        $days['sunday_off_days'] = $sundayOffDays;

        $totalWorkingDays = $this->totalWorkingDays($days);
        $this->guardWorkingDays($totalWorkingDays);
        $calendarDays = $start->daysInMonth;

        $payableDays = $this->payableDays($days);
        $days['present_days'] = $displayedPresentDays;

        return $days + [
            'total_working_days' => $totalWorkingDays,
            'payable_days' => $payableDays,
            'unpaid_days' => $this->unpaidDays($days),
            'calendar_days' => $calendarDays,
            'source' => 'attendances_and_approved_leaves',
        ];
    }

    private function activeHolidayDays(EmployeeM $employee, int $month, int $year): float
    {
        if (! Schema::hasTable('holidays')) {
            return 0.0;
        }

        $query = DB::table('holidays')
            ->whereMonth('holiday_date', $month)
            ->whereYear('holiday_date', $year);

        if (Schema::hasColumn('holidays', 'is_active')) {
            $query->where('is_active', 1);
        }

        return (float) $query->count();
    }

    private function approvedLeaveDays(EmployeeM $employee, int $month, int $year): array
    {
        $blank = [
            'paid_leave_days' => 0.0,
            'sick_leave_days' => 0.0,
            'comp_off_days' => 0.0,
            'lwp_days' => 0.0,
        ];

        if (! Schema::hasTable('leave_requests') || ! Schema::hasTable('leave_request_dates')) {
            return $blank;
        }

        $totals = app(\App\Services\HRMS\Leave\LeaveMonthlyBalanceService::class)
            ->leaveTotalsForEmployeeMonth($employee, $year, $month);

        return [
            'paid_leave_days' => (float) ($totals['paid_leave_days'] ?? 0),
            'sick_leave_days' => (float) ($totals['sick_leave_days'] ?? 0),
            'comp_off_days' => (float) ($totals['comp_off_days'] ?? 0),
            'lwp_days' => (float) ($totals['lwp_days'] ?? 0),
        ];
    }

    private function payableDays(array $days): float
    {
        $basePresent = $days['actual_present_days'] ?? $days['present_days'];
        $remainingMp = $days['remaining_mp_days'] ?? 0.0;
        return round(
            $basePresent
            + $remainingMp
            + $days['paid_leave_days']
            + $days['sick_leave_days']
            + $days['comp_off_days']
            + $days['holiday_days']
            + $days['week_off_days']
            + ($days['half_days'] * 0.5),
            2
        );
    }

    private function unpaidDays(array $days): float
    {
        return round($days['lwp_days'] + $days['absent_days'] + ($days['half_days'] * 0.5), 2);
    }

    private function totalWorkingDays(array $days): float
    {
        $basePresent = $days['actual_present_days'] ?? $days['present_days'];
        $remainingMp = $days['remaining_mp_days'] ?? 0.0;
        return round(
            $basePresent
            + $remainingMp
            + $days['paid_leave_days']
            + $days['sick_leave_days']
            + $days['comp_off_days']
            + $days['holiday_days']
            + $days['week_off_days']
            + $days['half_days']
            + $days['lwp_days']
            + $days['absent_days'],
            2
        );
    }

    private function guardWorkingDays(float $totalWorkingDays): void
    {
        if ($totalWorkingDays <= 0) {
            throw ValidationException::withMessages([
                'working_days' => 'Working days are missing for this payroll month.',
            ]);
        }
    }
}
