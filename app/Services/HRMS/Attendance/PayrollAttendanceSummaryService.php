<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\MonthlyAttendanceSummaryM;
use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollAttendanceSummaryService
{
    public function generate(int $month, int $year, ?int $employeeId = null): int
    {
        $employees = EmployeeM::query()
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->where(function ($query) {
                $query->where('employment_status', 'active')->orWhereNull('employment_status');
            })
            ->get();

        $count = 0;
        foreach ($employees as $employee) {
            $this->generateForEmployee($employee, $month, $year);
            $count++;
        }

        return $count;
    }

    public function generateForEmployee(EmployeeM $employee, int $month, int $year): MonthlyAttendanceSummaryM
    {
        $summary = MonthlyAttendanceSummaryM::firstOrNew([
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
        ]);

        if ($summary->exists && $summary->is_locked) {
            return $summary;
        }

        $attendances = AttendanceM::with('attendanceType')
            ->where('employee_id', $employee->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get();

        $leaveRows = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->where('leave_requests.status', 'approved')
            ->whereMonth('leave_request_dates.leave_date', $month)
            ->whereYear('leave_request_dates.leave_date', $year)
            ->selectRaw('SUM(paid_day) as paid, SUM(sick_day) as sick, SUM(comp_off_day) as comp, SUM(lwp_day) as lwp')
            ->first();

        $present = $attendances->filter(fn ($attendance) => optional($attendance->attendanceType)->code === 'present')->count();
        $holiday = $attendances->filter(fn ($attendance) => optional($attendance->attendanceType)->code === 'holiday')->count();
        $weekOff = $attendances->filter(fn ($attendance) => optional($attendance->attendanceType)->code === 'week_off')->count();
        $absent = $attendances->filter(fn ($attendance) => optional($attendance->attendanceType)->code === 'absent')->count();
        $halfDays = $attendances->where('is_half_day', true)->count();
        $lwp = (float) ($leaveRows->lwp ?? 0) + (float) $attendances->where('is_lwp', true)->count();

        $summary->fill([
            'present_days' => $present,
            'paid_leave_days' => (float) ($leaveRows->paid ?? 0),
            'sick_leave_days' => (float) ($leaveRows->sick ?? 0),
            'comp_off_days' => (float) ($leaveRows->comp ?? 0),
            'holiday_days' => $holiday,
            'week_off_days' => $weekOff,
            'half_days' => $halfDays,
            'lwp_days' => $lwp,
            'absent_days' => $absent,
            'late_count' => $attendances->where('is_late', true)->count(),
            'early_out_count' => $attendances->where('is_early_out', true)->count(),
            'missed_punch_count' => $attendances->where('is_missed_punch', true)->count() + $attendances->where('missed_punch', true)->count(),
            'total_work_minutes' => $attendances->sum('total_work_minutes'),
            'payable_days' => max(0, $present + (float) ($leaveRows->paid ?? 0) + (float) ($leaveRows->sick ?? 0) + (float) ($leaveRows->comp ?? 0) + $holiday + $weekOff - ($halfDays * 0.5)),
        ]);
        $summary->save();

        return $summary;
    }

    public function lock(int $month, int $year, int $userId, ?int $employeeId = null): int
    {
        return MonthlyAttendanceSummaryM::where('month', $month)
            ->where('year', $year)
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->update([
                'is_locked' => true,
                'locked_by_user_id' => $userId,
                'locked_at' => Carbon::now('Asia/Kolkata'),
            ]);
    }

    public function unlock(int $month, int $year, ?int $employeeId = null): int
    {
        $query = MonthlyAttendanceSummaryM::where('month', $month)->where('year', $year);
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($query->where('is_locked', true)->doesntExist()) {
            throw ValidationException::withMessages(['summary' => 'No locked summary found for the selected period.']);
        }

        return $query->update([
            'is_locked' => false,
            'locked_by_user_id' => null,
            'locked_at' => null,
        ]);
    }
}
