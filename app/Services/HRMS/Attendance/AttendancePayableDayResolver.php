<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM;
use Illuminate\Support\Collection;

class AttendancePayableDayResolver
{
    public function resolve(AttendanceM $attendance): array
    {
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

