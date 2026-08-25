<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceContextResolverService
{
    public const TIMEZONE = 'Asia/Kolkata';

    protected AttendanceRuleResolverService $ruleResolver;

    public function __construct(?AttendanceRuleResolverService $ruleResolver = null)
    {
        $this->ruleResolver = $ruleResolver ?: new AttendanceRuleResolverService();
    }

    /**
     * Resolves the single, unified attendance context for an employee on a given date/time.
     * This is consumed identically by Backend Engine, API, Web Dashboard, and Mobile App.
     */
    public function resolveContext(Employee $employee, Carbon|string|null $dateTime = null, ?Attendance $attendance = null): array
    {
        $now = $this->ruleResolver->date($dateTime);
        $today = $now->toDateString();

        $policyObj = $this->ruleResolver->resolveShiftPolicy($employee, $today);
        $dayContext = $this->ruleResolver->getDayContext($employee, $now);
        $windowState = $this->ruleResolver->calculatePunchWindowState($policyObj, $now);

        if (! $attendance) {
            $attendance = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->latest('id')
                ->first();
        }

        $mobileState = $this->ruleResolver->resolveMobileState($employee, $now, $attendance);

        // Compute Target Out
        $targetOutFormatted = null;
        if ($attendance && $attendance->punch_in_time) {
            if ($attendance->target_punch_out_time) {
                $targetOutFormatted = Carbon::parse($today . ' ' . $this->ruleResolver->timeString($attendance->target_punch_out_time), self::TIMEZONE)->format('h:i A');
            } else {
                $in = Carbon::parse($today . ' ' . $this->ruleResolver->timeString($attendance->punch_in_time), self::TIMEZONE);
                $target = $this->ruleResolver->targetPunchOut($in, $policyObj, $attendance->attendance_status ?? 'present');
                $targetOutFormatted = $target->format('h:i A');
            }
        }

        // UI Window State (BEFORE_WINDOW, OPEN, PUNCHED_IN, PUNCHED_OUT)
        $windowStateCode = 'OPEN';
        if ($attendance && $attendance->punch_out_time) {
            $windowStateCode = 'PUNCHED_OUT';
        } elseif ($attendance && $attendance->punch_in_time) {
            $windowStateCode = 'PUNCHED_IN';
        } elseif ($windowState['is_before_early_login'] ?? false) {
            $windowStateCode = 'BEFORE_WINDOW';
        }

        $isHalfDayContext = (bool) ($attendance?->is_half_day ?? false)
            || ($attendance?->attendance_status === 'half_day')
            || ($dayContext['is_half_day_leave'] ?? false);

        $requiredWorkMinutes = (int) ($policyObj?->required_work_minutes ?? 480);
        $halfDayMinMinutes = (int) ($policyObj?->half_day_min_minutes ?? ($requiredWorkMinutes > 0 ? (int) ($requiredWorkMinutes / 2) : 240));
        if ($halfDayMinMinutes <= 0 && $requiredWorkMinutes > 0) {
            $halfDayMinMinutes = (int) ($requiredWorkMinutes / 2);
        }

        $effectiveRequiredWorkMinutes = $isHalfDayContext ? $halfDayMinMinutes : $requiredWorkMinutes;
        $breakMinutes = $isHalfDayContext ? 0 : (int) ($policyObj?->lunch_break_minutes ?? $policyObj?->break_minutes ?? 0);

        // Calculate Work Durations if Punched In
        $completedWorkMinutes = 0;
        $remainingWorkSeconds = 0;
        if ($attendance && $attendance->punch_in_time) {
            $inTime = Carbon::parse($today . ' ' . $this->ruleResolver->timeString($attendance->punch_in_time), self::TIMEZONE);
            $outTime = $attendance->punch_out_time
                ? Carbon::parse($today . ' ' . $this->ruleResolver->timeString($attendance->punch_out_time), self::TIMEZONE)
                : $now;

            if ($outTime->lt($inTime)) {
                $outTime->addDay();
            }

            $grossMins = $inTime->diffInMinutes($outTime);
            $completedWorkMinutes = max(0, $grossMins - $breakMinutes);

            if (! $attendance->punch_out_time && $targetOutFormatted) {
                $targetCarbon = Carbon::parse($today . ' ' . $targetOutFormatted, self::TIMEZONE);
                if ($targetCarbon->lt($inTime)) {
                    $targetCarbon->addDay();
                }
                $remainingWorkSeconds = max(0, $now->diffInSeconds($targetCarbon, false));
            }
        }

        $shiftPayload = [
            'id' => $policyObj?->attendance_time_id ?? $policyObj?->id,
            'name' => $policyObj?->policy_name ?? $policyObj?->name ?? 'Standard Shift',
            'shift_type' => $policyObj?->shift_type ?? 'fixed',
            'punch_allowed_from' => $this->ruleResolver->timeString($policyObj?->punch_allowed_from ?? $policyObj?->early_login_from),
            'shift_start_time' => $this->ruleResolver->timeString($policyObj?->shift_start_time ?? $policyObj?->normal_login_from),
            'late_after_time' => $this->ruleResolver->timeString($policyObj?->late_after_time),
            'warning_after_time' => $this->ruleResolver->timeString($policyObj?->warning_after_time),
            'half_day_after_time' => $this->ruleResolver->timeString($policyObj?->half_day_after_time),
            'block_after_time' => $this->ruleResolver->timeString($policyObj?->block_after_time),
            'shift_end_time' => $this->ruleResolver->timeString($policyObj?->shift_end_time),
            'required_work_minutes' => $requiredWorkMinutes,
            'half_day_min_minutes' => $halfDayMinMinutes,
            'break_minutes' => $breakMinutes,
        ];

        $policyPayload = [
            'id' => $policyObj?->attendance_policy_rule_id ?? $policyObj?->id,
            'policy_name' => $policyObj?->policy_name ?? 'Default Policy',
            'combined_violation_limit' => (int) ($policyObj?->combined_violation_limit ?? 0),
            'allowed_missed_punches' => (int) ($policyObj?->allowed_missed_punches ?? 0),
            'allow_web_punch' => (bool) ($policyObj?->allow_web_punch ?? true),
            'allow_mobile_punch' => (bool) ($policyObj?->allow_mobile_punch ?? true),
            'wfh_enabled' => (bool) ($policyObj?->wfh_enabled ?? false),
            'regularization_enabled' => (bool) ($policyObj?->regularization_enabled ?? true),
        ];

        $isWorkingDay = (bool) ($dayContext['is_working_day'] ?? true);
        $canPunchIn = $isWorkingDay && (! $attendance || ! $attendance->punch_in_time) && $windowStateCode === 'OPEN';
        $canPunchOut = (bool) ($attendance && $attendance->punch_in_time && ! $attendance->punch_out_time);

        $permissionsPayload = [
            'can_punch_in' => $canPunchIn,
            'can_punch_out' => $canPunchOut,
            'allow_web_punch' => (bool) ($policyObj?->allow_web_punch ?? true),
            'allow_mobile_punch' => (bool) ($policyObj?->allow_mobile_punch ?? true),
            'can_use_web_attendance' => $employee->canUseWebAttendance(),
            'can_use_mobile_attendance' => $employee->canUseMobileAttendance(),
        ];

        $hasPendingReg = $this->ruleResolver->hasPendingRegularizationForDate($employee, $now, $attendance);
        $hasApprovedReg = $this->ruleResolver->hasApprovedRegularizationForDate($employee, $now, $attendance);

        $flagsPayload = [
            'is_blocked' => (bool) $mobileState['is_blocked'],
            'is_punch_blocked' => (bool) $mobileState['is_punch_blocked'],
            'show_blocked_card' => (bool) $mobileState['show_blocked_card'],
            'show_early_login_tag' => ($windowState['is_before_shift_start'] ?? false) && $mobileState['can_punch_in'],
            'show_late_mark' => $attendance ? (bool) ($attendance->is_late ?? false) : ($windowState['is_late'] ?? false),
            'show_late_warning' => (bool) ($windowState['is_warning'] ?? false),
            'is_holiday' => (bool) $dayContext['is_holiday'],
            'is_weekoff' => (bool) $dayContext['is_weekoff'],
            'is_on_leave' => (bool) ($dayContext['is_on_leave'] ?? false),
            'is_leave' => (bool) ($dayContext['is_on_leave'] ?? false),
            'is_half_day_leave' => (bool) ($dayContext['is_half_day_leave'] ?? false),
            'has_pending_regularization' => $hasPendingReg,
            'has_approved_regularization' => $hasApprovedReg,
            'regularization_status' => $hasPendingReg ? 'pending' : ($hasApprovedReg ? 'approved' : null),
        ];

        $messages = [];
        if ($mobileState['primary_message']) {
            $messages[] = $mobileState['primary_message'];
        }
        if ($mobileState['blocked_message']) {
            $messages[] = $mobileState['blocked_message'];
        }

        return [
            'shift' => $shiftPayload,
            'policy' => $policyPayload,
            'attendance' => $attendance ? [
                'id' => $attendance->id,
                'attendance_date' => is_string($attendance->attendance_date) ? $attendance->attendance_date : $attendance->attendance_date->format('Y-m-d'),
                'punch_in_time' => $attendance->punch_in_time ? $this->ruleResolver->timeString($attendance->punch_in_time) : null,
                'punch_out_time' => $attendance->punch_out_time ? $this->ruleResolver->timeString($attendance->punch_out_time) : null,
                'attendance_status' => $attendance->attendance_status,
                'is_late' => (bool) ($attendance->is_late ?? false),
                'is_half_day' => (bool) ($attendance->is_half_day ?? false),
                'is_lwp' => (bool) ($attendance->is_lwp ?? false),
                'is_admin_unlocked' => (bool) ($attendance->is_admin_unlocked ?? false) || $hasApprovedReg,
                'is_blocked' => $hasApprovedReg ? false : (bool) ($attendance->is_blocked ?? false),
                'is_punch_blocked' => $hasApprovedReg ? false : (bool) ($attendance->is_punch_blocked ?? false),
            ] : null,
            'window' => [
                'state' => $windowStateCode,
                'is_allowed' => (bool) ($windowState['is_allowed'] ?? false),
                'allowed_from' => $this->ruleResolver->timeString($policyObj?->punch_allowed_from ?? $policyObj?->early_login_from),
                'block_after' => $this->ruleResolver->timeString($policyObj?->block_after_time),
                'shift_end' => $this->ruleResolver->timeString($policyObj?->shift_end_time),
            ],
            'permissions' => $permissionsPayload,
            'flags' => $flagsPayload,
            'work_duration' => [
                'required_work_minutes' => $effectiveRequiredWorkMinutes,
                'completed_work_minutes' => $completedWorkMinutes,
                'remaining_work_seconds' => $remainingWorkSeconds,
                'break_minutes' => $breakMinutes,
            ],
            'punch_in_time' => $attendance?->punch_in_time ? $this->ruleResolver->timeString($attendance->punch_in_time) : null,
            'punch_out_time' => $attendance?->punch_out_time ? $this->ruleResolver->timeString($attendance->punch_out_time) : null,
            'target_out' => $targetOutFormatted,
            'status_code' => $mobileState['status_code'],
            'status_name' => $mobileState['status_name'],
            'attendance_state' => $mobileState['attendance_state'],
            'next_action' => $mobileState['next_action'],
            'primary_message' => $mobileState['primary_message'],
            'blocked_message' => $mobileState['blocked_message'],
            'has_pending_regularization' => $hasPendingReg,
            'has_approved_regularization' => $hasApprovedReg,
            'regularization_status' => $hasPendingReg ? 'pending' : ($hasApprovedReg ? 'approved' : null),
            'messages' => $messages,
            'server_time' => $now->format('Y-m-d H:i:s'),
            'timezone' => self::TIMEZONE,
            'day_context' => $dayContext,
        ];
    }
}
