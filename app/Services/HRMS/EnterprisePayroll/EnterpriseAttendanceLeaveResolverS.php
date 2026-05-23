<?php

namespace App\Services\HRMS\EnterprisePayroll;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\MonthlyAttendanceSummaryM;
use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class EnterpriseAttendanceLeaveResolverS
{
    public function resolve(EmployeeM $employee, int $month, int $year): array
    {
        $summary = MonthlyAttendanceSummaryM::query()
            ->where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($summary) {
            return $this->fromMonthlySummary($summary);
        }

        return $this->fromAttendancesAndApprovedLeaves($employee, $month, $year);
    }

    private function fromMonthlySummary(MonthlyAttendanceSummaryM $summary): array
    {
        $days = [
            'present_days' => (float) $summary->present_days,
            'paid_leave_days' => (float) $summary->paid_leave_days,
            'sick_leave_days' => (float) $summary->sick_leave_days,
            'comp_off_days' => (float) $summary->comp_off_days,
            'holiday_days' => (float) $summary->holiday_days,
            'week_off_days' => (float) $summary->week_off_days,
            'half_days' => (float) $summary->half_days,
            'lwp_days' => (float) $summary->lwp_days,
            'absent_days' => (float) $summary->absent_days,
            'late_count' => (int) $summary->late_count,
            'early_out_count' => (int) $summary->early_out_count,
            'missed_punch_count' => (int) $summary->missed_punch_count,
        ];

        $totalWorkingDays = $this->totalWorkingDays($days);
        $this->guardWorkingDays($totalWorkingDays);

        return $days + [
            'total_working_days' => $totalWorkingDays,
            'payable_days' => $this->payableDays($days),
            'unpaid_days' => $this->unpaidDays($days),
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

        foreach ($attendances as $attendance) {
            $code = strtolower((string) optional($attendance->attendanceType)->code);
            $status = strtolower((string) $attendance->attendance_status);
            $name = strtolower((string) optional($attendance->attendanceType)->name);
            $label = trim($code . ' ' . $status . ' ' . $name);

            $days['late_count'] += $attendance->is_late ? 1 : 0;
            $days['early_out_count'] += $attendance->is_early_out ? 1 : 0;
            $days['missed_punch_count'] += ($attendance->missed_punch || $attendance->is_missed_punch) ? 1 : 0;

            if ($attendance->is_half_day || str_contains($label, 'half')) {
                $days['half_days'] += 1;
            } elseif ($attendance->is_lwp || str_contains($label, 'lwp')) {
                $days['lwp_days'] += 1;
            } elseif (str_contains($label, 'absent')) {
                $days['absent_days'] += 1;
            } elseif (str_contains($label, 'holiday')) {
                $days['holiday_days'] += 1;
            } elseif (str_contains($label, 'week')) {
                $days['week_off_days'] += 1;
            } else {
                $days['present_days'] += 1;
            }
        }

        $leaveDays = $this->approvedLeaveDays($employee, $month, $year);
        foreach (['paid_leave_days', 'sick_leave_days', 'comp_off_days', 'lwp_days'] as $key) {
            $days[$key] += (float) $leaveDays[$key];
        }

        $totalWorkingDays = $this->totalWorkingDays($days);
        $this->guardWorkingDays($totalWorkingDays);

        return $days + [
            'total_working_days' => $totalWorkingDays,
            'payable_days' => $this->payableDays($days),
            'unpaid_days' => $this->unpaidDays($days),
            'source' => 'attendances_and_approved_leaves',
        ];
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

        $row = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->where('leave_requests.status', 'approved')
            ->whereMonth('leave_request_dates.leave_date', $month)
            ->whereYear('leave_request_dates.leave_date', $year)
            ->selectRaw('SUM(leave_request_dates.paid_day) as paid, SUM(leave_request_dates.sick_day) as sick, SUM(leave_request_dates.comp_off_day) as comp, SUM(leave_request_dates.lwp_day) as lwp')
            ->first();

        return [
            'paid_leave_days' => (float) ($row->paid ?? 0),
            'sick_leave_days' => (float) ($row->sick ?? 0),
            'comp_off_days' => (float) ($row->comp ?? 0),
            'lwp_days' => (float) ($row->lwp ?? 0),
        ];
    }

    private function payableDays(array $days): float
    {
        return round(
            $days['present_days']
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
        return round(
            $days['present_days']
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
