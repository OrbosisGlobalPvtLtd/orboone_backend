<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceStateResolverService
{
    public const TIMEZONE = 'Asia/Kolkata';

    protected AttendanceRuleResolverService $ruleResolver;

    public function __construct(?AttendanceRuleResolverService $ruleResolver = null)
    {
        $this->ruleResolver = $ruleResolver ?: new AttendanceRuleResolverService();
    }

    /**
     * Resolves complete backend-driven attendance state for an employee on a given date/time.
     */
    public function resolveState(Employee $employee, Carbon|string|null $dateTime = null, ?Attendance $attendance = null): array
    {
        $now = $this->ruleResolver->date($dateTime);
        $today = $now->toDateString();

        $resolvedContext = $this->ruleResolver->getResolvedAttendanceContext($employee, $now);
        $policy = $resolvedContext['policy'];

        if (! $attendance) {
            $attendance = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->latest('id')
                ->first();
        }

        $mobileState = $this->ruleResolver->resolveMobileState($employee, $now, $attendance);
        $windowState = $this->ruleResolver->calculatePunchWindowState($policy, $now);

        // Compute formatted Target Out Time if punched in
        $targetOutFormatted = null;
        if ($attendance && $attendance->punch_in_time) {
            if ($attendance->target_punch_out_time) {
                $targetOutFormatted = Carbon::parse($today . ' ' . $this->ruleResolver->timeString($attendance->target_punch_out_time), self::TIMEZONE)->format('h:i A');
            } else {
                $in = Carbon::parse($today . ' ' . $this->ruleResolver->timeString($attendance->punch_in_time), self::TIMEZONE);
                $target = $this->ruleResolver->targetPunchOut($in, $policy, $attendance->attendance_status ?? 'present');
                $targetOutFormatted = $target->format('h:i A');
            }
        }

        $currentShiftName = $resolvedContext['shift']['name'] ?? $policy?->policy_name ?? $policy?->name ?? 'Standard Shift';
        $attendanceStatusName = $mobileState['status_name'] ?? 'Not Punched';
        $primaryMessage = $mobileState['primary_message'] ?? 'Punch In Available';

        // Window state string representation
        $windowStateStr = 'normal';
        if ($windowState['is_blocked']) {
            $windowStateStr = 'blocked';
        } elseif ($windowState['is_late']) {
            $windowStateStr = 'late';
        } elseif ($windowState['is_warning']) {
            $windowStateStr = 'warning';
        } elseif ($windowState['is_before_early_login']) {
            $windowStateStr = 'early';
        }

        return [
            'current_shift' => $currentShiftName,
            'attendance_status' => $attendanceStatusName,
            'window_state' => $windowStateStr,
            'can_punch_in' => (bool) $mobileState['can_punch_in'],
            'can_punch_out' => (bool) $mobileState['can_punch_out'],
            'target_out' => $targetOutFormatted,
            'message' => $primaryMessage,
            'ui' => [
                'status_code' => $mobileState['status_code'],
                'status_name' => $mobileState['status_name'],
                'attendance_state' => $mobileState['attendance_state'],
                'is_blocked' => $mobileState['is_blocked'],
                'is_punch_blocked' => $mobileState['is_punch_blocked'],
                'show_blocked_card' => $mobileState['show_blocked_card'],
                'blocked_message' => $mobileState['blocked_message'],
                'next_action' => $mobileState['next_action'],
                'primary_message' => $primaryMessage,
                'warning_message' => $windowState['is_warning'] ? 'Late punch-in. Warning: punch will be blocked after ' . $this->displayTime($policy?->block_after_time) . '.' : null,
            ],
            'shift' => $resolvedContext['shift'],
            'timing' => $resolvedContext['timing'],
            'work_duration' => $resolvedContext['work_duration'],
            'punch_windows' => $windowState,
            'leave_adjustments' => $resolvedContext['leave_adjustments'],
            'day_context' => $resolvedContext['day_context'],
            'attendance' => $attendance,
        ];
    }

    private function displayTime($time): string
    {
        return $time ? Carbon::parse($time, self::TIMEZONE)->format('h:i A') : 'configured time';
    }
}
