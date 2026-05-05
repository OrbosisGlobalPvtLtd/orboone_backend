<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Attendance\AttendanceWorkLogM;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Carbon\Carbon;

class AttendanceS
{
    public function processPunchIn(
        int $userId,
        string $workMode = 'wfo',
        ?string $note = null,
        array $meta = [],
        ?string $customTime = null,
        ?int $attendanceTypeId = null,
        bool $enforceEmployeeRules = true
    ): array {
        $timezone = $this->attendanceTimezone();
        $now = $customTime ? Carbon::parse($customTime, $timezone) : Carbon::now($timezone);
        $today = $now->toDateString();
        $employee = Employee::with('profile')->where('user_id', $userId)->first();

        if (! $employee) {
            return ['status' => 'error', 'message' => 'Employee profile not found.'];
        }

        if ($enforceEmployeeRules && ! $this->employeeCanPunchAttendance($employee)) {
            return ['status' => 'error', 'message' => 'Complete your profile to mark attendance.'];
        }

        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing && $existing->punch_in_time) {
            return ['status' => 'error', 'message' => 'Punch in already recorded for today.'];
        }

        if ($existing) {
            $existing->loadMissing('attendanceType');
            $existingType = optional($existing->attendanceType)->code;

            if (in_array($existingType, ['absent', 'leave', 'week_off', 'holiday'], true)) {
                return ['status' => 'error', 'message' => 'Attendance is already marked for today.'];
            }
        }

        $shift = $this->defaultShift();

        if ($enforceEmployeeRules && $shift && $shift->punch_allowed_from) {
            $allowedFrom = Carbon::parse($today.' '.$shift->punch_allowed_from, $timezone);

            if ($now->lt($allowedFrom)) {
                return [
                    'status' => 'error',
                    'message' => 'Punch in is allowed from '.$allowedFrom->format('h:i A').'.',
                ];
            }
        }

        $presentType = $this->attendanceType('present');
        $pendingHrType = $this->attendanceType('pending_hr');
        $time = $now->format('H:i:s');
        $lateAfterTime = $shift?->late_after_time
            ? Carbon::parse($shift->late_after_time, $timezone)->format('H:i:s')
            : null;
        $isLate = $lateAfterTime
            ? $time > $lateAfterTime
            : false;
        $isBlocked = $lateAfterTime
            ? $time > $lateAfterTime && ! $attendanceTypeId
            : false;

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $today],
            [
                'user_id' => $userId,
                'attendance_time_id' => $shift?->id,
                'attendance_type_id' => $attendanceTypeId ?: ($isBlocked ? $pendingHrType?->id : $presentType?->id),
                'punch_in_time' => $time,
                'work_mode' => strtolower($workMode),
                'punch_in_latitude' => $meta['latitude'] ?? null,
                'punch_in_longitude' => $meta['longitude'] ?? null,
                'punch_in_address' => $meta['address'] ?? null,
                'punch_in_ip' => $meta['ip'] ?? null,
                'punch_in_device' => $meta['device'] ?? null,
                'is_late' => $isLate,
                'late_minutes' => $this->lateMinutes($now, $shift),
                'is_blocked' => $isBlocked,
                'block_reason' => $isBlocked ? 'Punch in is pending HR approval because you are late.' : null,
                'hr_approved_by' => null,
                'hr_approved_at' => null,
                'hr_approval_note' => null,
                'is_profile_completed_at_punch' => $this->employeeCanPunchAttendance($employee),
                'is_locked' => true,
                'punch_in_note' => $note,
            ]
        );

        return [
            'status' => $isBlocked ? 'blocked' : true,
            'message' => $isBlocked
                ? 'Punch in is pending HR approval because you are late.'
                : ($isLate ? 'Punch in recorded with late mark.' : 'Punch in recorded successfully.'),
            'data' => $attendance->fresh(['attendanceType', 'attendanceTime', 'workLogs']),
        ];
    }

    public function processPunchOut(
        int $userId,
        string $taskSummary,
        ?string $note = null,
        array $meta = [],
        ?string $customTime = null
    ): array {
        $timezone = $this->attendanceTimezone();
        $now = $customTime ? Carbon::parse($customTime, $timezone) : Carbon::now($timezone);
        $today = $now->toDateString();
        $employee = Employee::where('user_id', $userId)->first();

        if (! $employee) {
            return ['status' => 'error', 'message' => 'Employee profile not found.'];
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (! $attendance || ! $attendance->punch_in_time) {
            return ['status' => 'error', 'message' => 'No active punch in found.'];
        }

        if ($attendance->punch_out_time) {
            return ['status' => 'error', 'message' => 'Punch out already recorded.'];
        }

        $attendance->loadMissing('attendanceType');
        $isPendingHr = optional($attendance->attendanceType)->code === 'pending_hr';

        if ($attendance->is_blocked || $isPendingHr) {
            return [
                'status' => 'error',
                'message' => 'Waiting for HR approval. Punch out is disabled.',
            ];
        }

        $attendance->fill([
            'punch_out_time' => $now->format('H:i:s'),
            'punch_out_latitude' => $meta['latitude'] ?? null,
            'punch_out_longitude' => $meta['longitude'] ?? null,
            'punch_out_address' => $meta['address'] ?? null,
            'punch_out_ip' => $meta['ip'] ?? null,
            'punch_out_device' => $meta['device'] ?? null,
            'punch_out_note' => $note,
        ])->save();

        AttendanceWorkLogM::updateOrCreate(
            ['attendance_id' => $attendance->id],
            [
                'employee_id' => $employee->id,
                'user_id' => $userId,
                'work_date' => $today,
                'work_summary' => $taskSummary,
            ]
        );

        $this->calculateWorkingHours($attendance);

        return [
            'status' => true,
            'message' => 'Punch out recorded successfully.',
            'data' => $attendance->fresh(['attendanceType', 'workLogs']),
        ];
    }

    public function autoCloseMissedPunchouts(?Carbon $beforeDate = null): int
    {
        $beforeDate = $beforeDate ?: Carbon::today($this->attendanceTimezone());
        $absentType = $this->attendanceType('absent');

        $records = Attendance::with('attendanceType')
            ->whereDate('attendance_date', '<', $beforeDate->toDateString())
            ->whereNotNull('punch_in_time')
            ->whereNull('punch_out_time')
            ->get();

        $closed = 0;

        foreach ($records as $attendance) {
            $isPendingHr = optional($attendance->attendanceType)->code === 'pending_hr';
            $isUnapprovedLate = ($attendance->is_blocked || $isPendingHr) && ! $attendance->hr_approved_at;
            $reason = $isUnapprovedLate
                ? 'Auto marked absent because late punch was not approved by HR before day end.'
                : 'Auto marked absent because punch out was not completed before day end.';

            $attendance->fill([
                'attendance_type_id' => $absentType?->id ?: $attendance->attendance_type_id,
                'is_blocked' => false,
                'block_reason' => $reason,
                'hr_approval_note' => $reason,
                'punch_out_note' => $reason,
                'is_locked' => true,
            ])->save();

            $closed++;
        }

        return $closed;
    }

    public function processAdminPunchIn(
        int $userId,
        string $workMode = 'wfo',
        ?string $note = 'Admin Override',
        ?string $customTime = null,
        ?int $attendanceTypeId = null
    ): array {
        return $this->processPunchIn(
            $userId,
            $workMode,
            $note,
            ['ip' => request()?->ip(), 'device' => request()?->userAgent()],
            $customTime,
            $attendanceTypeId,
            false
        );
    }

    public function processAdminPunchOut(
        int $userId,
        string $taskSummary,
        ?string $note = 'Admin Override',
        ?string $customTime = null
    ): array {
        return $this->processPunchOut(
            $userId,
            $taskSummary,
            $note,
            ['ip' => request()?->ip(), 'device' => request()?->userAgent()],
            $customTime
        );
    }

    public function calculateWorkingHours(Attendance $attendance): Attendance
    {
        if (! $attendance->punch_in_time || ! $attendance->punch_out_time) {
            return $attendance;
        }

        $shift = $attendance->attendanceTime ?: $this->defaultShift();
        $timezone = $this->attendanceTimezone();
        $date = Carbon::parse($attendance->attendance_date, $timezone)->toDateString();
        $in = Carbon::parse($date.' '.$attendance->punch_in_time, $timezone);
        $out = Carbon::parse($date.' '.$attendance->punch_out_time, $timezone);

        if ($out->lt($in)) {
            $out->addDay();
        }

        $grossMinutes = $in->diffInMinutes($out);
        $lunchMinutes = (int) ($shift?->lunch_break_minutes ?? 60);
        $netMinutes = max(0, $grossMinutes - $lunchMinutes);
        $requiredMinutes = (int) ($shift?->required_work_minutes ?? 480);
        $halfDayMinutes = (int) ($shift?->half_day_min_minutes ?? 240);
        $shiftEnd = $shift?->shift_end_time ? Carbon::parse($date.' '.$shift->shift_end_time, $timezone) : null;

        $attendance->gross_work_minutes = $grossMinutes;
        $attendance->lunch_break_minutes = $lunchMinutes;
        $attendance->total_work_minutes = $netMinutes;
        $attendance->is_early_out = $shiftEnd ? $out->lt($shiftEnd) : $netMinutes < $requiredMinutes;
        $attendance->early_out_minutes = $shiftEnd && $out->lt($shiftEnd) ? $out->diffInMinutes($shiftEnd) : 0;

        $typeCode = 'present';
        if ($netMinutes < $halfDayMinutes) {
            $typeCode = 'absent';
        } elseif ($netMinutes < $requiredMinutes) {
            $typeCode = 'half_day';
        }

        if (! $attendance->is_blocked) {
            $type = $this->attendanceType($typeCode);
            $attendance->attendance_type_id = $type?->id ?: $attendance->attendance_type_id;
        }

        $attendance->save();

        return $attendance;
    }

    private function defaultShift(): ?AttendanceTime
    {
        return AttendanceTime::where('is_default', true)->first()
            ?: AttendanceTime::where('is_active', true)->orderBy('id')->first();
    }

    private function attendanceType(string $code): ?AttendanceType
    {
        return AttendanceType::where('code', $code)->first();
    }

    private function attendanceTimezone(): string
    {
        return config('app.timezone', 'Asia/Kolkata') ?: 'Asia/Kolkata';
    }

    private function employeeCanPunchAttendance(Employee $employee): bool
    {
        $profile = $employee->profile;

        if (! $profile) {
            return false;
        }

        return (bool) $profile->is_profile_completed;
    }

    private function lateMinutes(Carbon $now, ?AttendanceTime $shift): int
    {
        if (! $shift || ! $shift->shift_start_time || ! $shift->late_after_time) {
            return 0;
        }

        $lateAfter = Carbon::parse($now->toDateString().' '.$shift->late_after_time, $now->getTimezone());

        return $now->gt($lateAfter) ? $lateAfter->diffInMinutes($now) : 0;
    }
}
