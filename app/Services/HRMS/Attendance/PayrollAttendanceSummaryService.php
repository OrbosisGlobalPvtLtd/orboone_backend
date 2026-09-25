<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\MonthlyAttendanceSummaryM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Employee\EmployeeEligibilityS;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PayrollAttendanceSummaryService
{
    public function __construct(
        private AttendancePayableDayResolver $payableDayResolver,
        private EmployeeEligibilityS $eligibilityService
    ) {
    }

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
            if (!$this->eligibilityService->canUsePayroll($employee, $month, $year)) {
                continue;
            }
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

        $present = 0;
        $holidayFromAtt = 0.0;
        $weekOff = 0;
        $absent = 0;
        $halfDays = 0;
        $lwp = (float) ($leaveRows->lwp ?? 0);

        foreach ($attendances as $attendance) {
            $isCompOffWorked = $this->payableDayResolver->isCompOffWorkedDay($attendance);
            $code = strtolower((string) optional($attendance->attendanceType)->code);

            if ($isCompOffWorked) {
                if ($code === 'holiday') {
                    $holidayFromAtt += 1.0;
                } else {
                    $weekOff += 1;
                }
            } else {
                if ($code === 'present') {
                    $present++;
                } elseif ($code === 'holiday') {
                    $holidayFromAtt += 1.0;
                } elseif ($code === 'week_off') {
                    $weekOff++;
                } elseif ($code === 'absent') {
                    $absent++;
                }

                if ($attendance->is_half_day) {
                    $halfDays++;
                }
                if ($attendance->is_lwp) {
                    $lwp += 1.0;
                }
            }
        }

        $tableHolidays = $this->activeHolidayDays($employee, $month, $year);
        $finalHolidays = max($holidayFromAtt, $tableHolidays);
        $missingHolidayDays = max(0.0, $finalHolidays - $holidayFromAtt);

        $attendancePayableDays = $attendances->filter(function (AttendanceM $attendance) {
            $code = strtolower((string) optional($attendance->attendanceType)->code);
            $status = strtolower((string) ($attendance->attendance_status ?? ''));
            return !in_array($code, ['leave'], true) && !in_array($status, ['leave'], true) && empty($attendance->leave_request_id);
        })->sum(function (AttendanceM $attendance) {
            return (float) $this->payableDayResolver->resolve($attendance)['payable_day'];
        });
        $summary->fill([
            'present_days' => $present,
            'paid_leave_days' => (float) ($leaveRows->paid ?? 0),
            'sick_leave_days' => (float) ($leaveRows->sick ?? 0),
            'comp_off_days' => (float) ($leaveRows->comp ?? 0),
            'holiday_days' => $finalHolidays,
            'week_off_days' => $weekOff,
            'half_days' => $halfDays,
            'lwp_days' => $lwp,
            'absent_days' => $absent,
            'late_count' => $attendances->where('is_late', true)->count(),
            'early_out_count' => $attendances->where('is_early_out', true)->count(),
            'missed_punch_count' => $attendances->where('is_missed_punch', true)->count() + $attendances->where('missed_punch', true)->count(),
            'total_work_minutes' => $attendances->sum('total_work_minutes'),
            'payable_days' => max(0, round(
                $attendancePayableDays
                + $missingHolidayDays
                + (float) ($leaveRows->paid ?? 0)
                + (float) ($leaveRows->sick ?? 0)
                + (float) ($leaveRows->comp ?? 0),
                2
            )),
        ]);
        $summary->save();

        return $summary;
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
