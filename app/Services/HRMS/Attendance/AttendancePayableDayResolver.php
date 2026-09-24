<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendancePayableDayResolver
{
    public function isCompOffWorkedDay(AttendanceM $attendance): bool
    {
        if (strtolower((string) $attendance->attendance_source) === 'comp_off_work') {
            return true;
        }

        if (!$attendance->employee_id || !$attendance->attendance_date) {
            return false;
        }

        $dateStr = \Carbon\Carbon::parse($attendance->attendance_date)->toDateString();

        $resolver = app(AttendanceRuleResolverService::class);
        $employee = $attendance->employee ?: \App\Models\HRMS\Employee\EmployeeM::find($attendance->employee_id);
        if (!$employee) {
            return false;
        }

        $dayContext = $resolver->getDayContext($employee, $dateStr);
        if (!($dayContext['is_holiday'] || $dayContext['is_weekoff'])) {
            return false;
        }

        return DB::table('holiday_work_requests')
            ->where('employee_id', $attendance->employee_id)
            ->whereDate('worked_date', $dateStr)
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            ->exists();
    }

    public function resolve(AttendanceM $attendance): array
    {
        // Holiday/Weekoff worked specifically for Comp-Off must NOT create payroll deduction
        if ($this->isCompOffWorkedDay($attendance)) {
            return $this->row(1.0, 'paid', false, 'Holiday/Weekoff Comp-Off worked day (no payroll deduction).');
        }

        $code = strtolower((string) optional($attendance->attendanceType)->code);
        $status = strtolower((string) ($attendance->attendance_status ?? ''));
        $effective = $code !== '' ? $code : $status;

        $isBlocked = (bool) ($attendance->is_punch_blocked || $attendance->is_blocked || $effective === 'punch_blocked');
        $isPendingHr = $effective === 'pending_hr';
        $isMissedPunch = (bool) ($attendance->missed_punch || $attendance->is_missed_punch || $effective === 'missed_punch');

        if ($isBlocked) {
            return $this->row(0.0, 'unpaid', true, 'Punch blocked attendance is unresolved.');
        }

        if ($isPendingHr || $isMissedPunch) {
            // Check if an approved regularization exists for this attendance
            $hasApprovedReg = DB::table('attendance_regularizations')
                ->where('attendance_id', $attendance->id)
                ->where('status', 'approved')
                ->exists();

            if ($hasApprovedReg) {
                return $this->row(1.0, 'paid', false, 'Missed punch regularized and approved.');
            }

            // Unapproved, pending, or rejected missed punch is automatically treated as LWP for payroll calculation (NO payroll block)
            return $this->row(0.0, 'unpaid', false, 'Missed punch treated as LWP for payroll calculation.');
        }

        if ((bool) $attendance->is_lwp || in_array($effective, ['lwp', 'absent'], true)) {
            return $this->row(0.0, 'unpaid', false, 'Unpaid attendance.');
        }

        if ((bool) $attendance->is_half_day || $effective === 'half_day') {
            return $this->row(0.5, 'partial_paid', false, 'Half day payable as 0.5.');
        }

        if (in_array($effective, ['present', 'holiday', 'week_off', 'leave'], true)) {
            return $this->row(1.0, 'paid', false, 'Payable full day.');
        }

        return $this->row(0.0, 'unpaid', false, 'Default unpaid for unknown status.');
    }

    public function unresolvedCount(Collection $attendances): int
    {
        return $attendances->filter(fn (AttendanceM $attendance) => (bool) $this->resolve($attendance)['is_unresolved'])->count();
    }

    private function row(float $payableDay, string $impact, bool $unresolved, string $reason): array
    {
        return [
            'payable_day' => $payableDay,
            'payroll_impact' => $impact,
            'is_unresolved' => $unresolved,
            'reason' => $reason,
        ];
    }
}

