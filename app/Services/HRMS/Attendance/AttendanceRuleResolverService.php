<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceRuleResolverService
{
    public const TIMEZONE = 'Asia/Kolkata';

    public function getPolicyForEmployee(Employee $employee, Carbon|string|null $date = null): ?object
    {
        return $this->resolveShiftPolicy($employee, $date ?: Carbon::now(self::TIMEZONE)->toDateString());
    }

    public function getResolvedAttendanceContext(Employee $employee, Carbon|string|null $dateTime = null, ?int $attendanceTimeId = null): array
    {
        $now = $this->date($dateTime);
        $dateStr = $now->toDateString();
        $policy = $this->resolveShiftPolicy($employee, $dateStr, $attendanceTimeId);
        $dayContext = $this->getDayContext($employee, $now);
        $window = $this->calculatePunchWindowState($policy, $now);

        $approvedLeave = DB::table('leave_requests')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereRaw('? BETWEEN start_date AND end_date', [$dateStr])
            ->first();

        $isFirstHalfLeave = false;
        $isSecondHalfLeave = false;
        $isFullLeave = false;

        if ($approvedLeave) {
            $isHalfDayLeave = (bool) ($approvedLeave->is_half_day ?? false) || ! empty($approvedLeave->half_day_type);
            if (! $isHalfDayLeave) {
                $isFullLeave = true;
            } elseif (($approvedLeave->half_day_type ?? '') === 'first_half') {
                $isFirstHalfLeave = true;
            } elseif (($approvedLeave->half_day_type ?? '') === 'second_half') {
                $isSecondHalfLeave = true;
            } else {
                $isFirstHalfLeave = true;
            }
        }

        $requiredWorkMinutes = (int) ($policy?->required_work_minutes ?? 480);
        $halfDayMinMinutes = (int) ($policy?->half_day_min_minutes ?? ($requiredWorkMinutes > 0 ? (int) ($requiredWorkMinutes / 2) : 240));
        $breakMinutes = (int) ($policy?->lunch_break_minutes ?? $policy?->break_minutes ?? 0);

        return [
            'employee_id' => $employee->id,
            'date' => $dateStr,
            'now' => $now,
            'policy' => $policy,
            'shift' => [
                'id' => $policy?->attendance_time_id ?? $policy?->id,
                'name' => $policy?->policy_name ?? $policy?->name ?? 'Standard Shift',
                'shift_type' => $policy?->shift_type ?? 'fixed',
                'employee_shift_timing_id' => $policy?->employee_shift_timing_id ?? null,
            ],
            'timing' => [
                'early_login_from' => $policy?->early_login_from ?? $policy?->punch_allowed_from,
                'normal_login_from' => $policy?->normal_login_from ?? $policy?->shift_start_time,
                'late_after_time' => $policy?->late_after_time,
                'warning_after_time' => $policy?->warning_after_time,
                'half_day_after_time' => $policy?->half_day_after_time,
                'block_after_time' => $policy?->block_after_time,
                'shift_end_time' => $policy?->shift_end_time,
            ],
            'work_duration' => [
                'required_work_minutes' => $requiredWorkMinutes,
                'half_day_min_minutes' => $halfDayMinMinutes,
                'break_minutes' => $breakMinutes,
            ],
            'punch_windows' => $window,
            'leave_adjustments' => [
                'is_full_leave' => $isFullLeave,
                'is_first_half_leave' => $isFirstHalfLeave,
                'is_second_half_leave' => $isSecondHalfLeave,
            ],
            'day_context' => $dayContext,
        ];
    }

    public function getPolicyFromAttendanceTimeId(int $attendanceTimeId, ?Employee $employee = null, Carbon|string|null $date = null): ?object
    {
        if (! $employee) {
            $policy = $this->defaultAttendancePolicy() ?: $this->policyFromDefaultShift();
            if ($policy) {
                $policy = $this->normalizePolicy($policy);
                $shift = DB::table('attendance_times')->where('id', $attendanceTimeId)->first();
                if ($shift) {
                    $policy->attendance_time_id = $shift->id;
                    $policy->id = $shift->id;
                    $policy->punch_allowed_from = $shift->punch_allowed_from;
                    $policy->shift_start_time = $shift->shift_start_time;
                    $policy->late_after_time = $shift->late_after_time;
                    $policy->warning_after_time = $shift->warning_after_time ?? null;
                    $policy->block_after_time = $shift->block_after_time ?? $shift->half_day_after_time ?? $shift->shift_end_time;
                    $policy->shift_end_time = $shift->shift_end_time;
                    $policy->required_work_minutes = $shift->required_work_minutes;
                    $policy->lunch_break_minutes = $shift->lunch_break_minutes ?? $shift->break_minutes ?? 0;
                    $policy->half_day_min_minutes = $shift->required_work_minutes ? (int) ($shift->required_work_minutes / 2) : 0;
                    $policy->absent_below_minutes = $shift->required_work_minutes ? (int) ($shift->required_work_minutes / 4) : 0;

                    $isFlexible = $shift->shift_type === 'flexible_part_time';
                    $policy->shift_type = $isFlexible ? 'flexible_part_time' : 'fixed';
                    $policy->policy_name = $shift->name;
                    $policy->name = $shift->name;
                }
            }
            return $policy;
        }

        return $this->resolveShiftPolicy($employee, $date ?: date('Y-m-d'), $attendanceTimeId);
    }

    public function resolveShiftPolicy(Employee $employee, Carbon|string $date, ?int $attendanceTimeId = null): ?object
    {
        $dateStr = $this->date($date)->toDateString();

        $policy = $this->policyFromEmployeeOverride($employee, $dateStr)
            ?: $this->policyFromEmployeeAssignment($employee, $dateStr)
            ?: $this->policyFromEmployeeColumn($employee);

        if (! $policy) {
            $policy = $this->defaultAttendancePolicy()
                ?: $this->policyFromDefaultShift();
        }

        if (! $policy) {
            return null;
        }

        $policy = $this->normalizePolicy($policy);

        $overrideTiming = null;
        if ($attendanceTimeId) {
            $overrideTiming = DB::table('employee_shift_timings')
                ->where('employee_id', $employee->id)
                ->where('attendance_time_id', $attendanceTimeId)
                ->where('is_active', 1)
                ->whereDate('effective_from', '<=', $dateStr)
                ->where(function ($q) use ($dateStr) {
                    $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $dateStr);
                })
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->first();
        }

        if (! $overrideTiming) {
            $overrideTiming = DB::table('employee_shift_timings')
                ->where('employee_id', $employee->id)
                ->where('is_active', 1)
                ->whereDate('effective_from', '<=', $dateStr)
                ->where(function ($q) use ($dateStr) {
                    $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $dateStr);
                })
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->first();
        }

        if (! $overrideTiming) {
            $overrideTiming = DB::table('employee_shift_timings')
                ->where('employee_id', $employee->id)
                ->whereDate('effective_from', '<=', $dateStr)
                ->where(function ($q) use ($dateStr) {
                    $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $dateStr);
                })
                ->orderByDesc('is_active')
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->first();
        }

        if ($overrideTiming) {
            if (! empty($overrideTiming->attendance_policy_rule_id)) {
                $assignedPolicyRule = DB::table('attendance_policy_rules')->where('id', $overrideTiming->attendance_policy_rule_id)->first();
                if ($assignedPolicyRule) {
                    foreach ($assignedPolicyRule as $ruleKey => $ruleVal) {
                        if (! in_array($ruleKey, ['id', 'created_at', 'updated_at'])) {
                            $policy->{$ruleKey} = $ruleVal;
                        }
                    }
                    $policy->attendance_policy_rule_id = $assignedPolicyRule->id;
                }
            }

            $explicitShiftTemplate = $attendanceTimeId ? DB::table('attendance_times')->where('id', $attendanceTimeId)->first() : null;
            $shiftTemplate = $explicitShiftTemplate ?: DB::table('attendance_times')->where('id', $overrideTiming->attendance_time_id)->first();
            $policy->employee_shift_timing_id = $overrideTiming->id;
            $policy->attendance_time_id = $explicitShiftTemplate ? $attendanceTimeId : $overrideTiming->attendance_time_id;
            $policy->id = $policy->attendance_time_id;

            $explicitTemplate = null;
            if ($attendanceTimeId && $overrideTiming && (int) $overrideTiming->attendance_time_id !== (int) $attendanceTimeId) {
                $explicitTemplate = DB::table('attendance_times')->where('id', $attendanceTimeId)->first();
            }

            $policy->early_login_from = $overrideTiming->punch_allowed_from ?? $explicitTemplate?->punch_allowed_from ?? $shiftTemplate?->punch_allowed_from ?? $shiftTemplate?->early_login_from ?? '08:00:00';
            $policy->normal_login_from = $overrideTiming->shift_start_time ?? $explicitTemplate?->shift_start_time ?? $shiftTemplate?->shift_start_time ?? $shiftTemplate?->normal_login_from ?? '09:30:00';
            $policy->punch_allowed_from = $policy->early_login_from;
            $policy->shift_start_time = $policy->normal_login_from;
            $policy->late_after_time = $overrideTiming->late_after_time ?? $explicitTemplate?->late_after_time ?? $shiftTemplate?->late_after_time ?? '09:45:00';
            $policy->warning_after_time = $shiftTemplate?->warning_after_time ?? $policy->late_after_time;
            $policy->half_day_after_time = $overrideTiming->half_day_after_time ?? $explicitTemplate?->half_day_after_time ?? $shiftTemplate?->half_day_after_time ?? $shiftTemplate?->shift_end_time;
            $policy->block_after_time = $shiftTemplate?->block_after_time ?? $overrideTiming->block_after_time ?? $policy->half_day_after_time ?? $shiftTemplate?->shift_end_time;
            $policy->shift_end_time = $explicitTemplate?->shift_end_time ?? $overrideTiming->shift_end_time ?? $shiftTemplate?->shift_end_time ?? '18:30:00';
            $policy->required_work_minutes = (int) ($explicitTemplate?->required_work_minutes ?? $overrideTiming->required_work_minutes ?? $shiftTemplate?->required_work_minutes ?? 480);
            $policy->half_day_min_minutes = (int) ($shiftTemplate?->half_day_min_minutes ?? ($policy->required_work_minutes ? (int) ($policy->required_work_minutes / 2) : 240));
            $policy->required_office_minutes = (int) ($shiftTemplate?->required_office_minutes ?? $policy->required_work_minutes);
            $policy->lunch_break_minutes = (int) ($overrideTiming->lunch_minutes ?? $shiftTemplate?->lunch_break_minutes ?? $shiftTemplate?->break_minutes ?? 0);
            $policy->break_minutes = $policy->lunch_break_minutes;

            $isFlexible = $shiftTemplate && (in_array(strtolower($shiftTemplate->shift_type ?? ''), ['flexible', 'flexible_part_time']) || stripos($shiftTemplate->name, 'flexible') !== false);
            $policy->shift_type = $isFlexible ? 'flexible_part_time' : 'fixed';
            $policy->policy_name = $shiftTemplate?->name ?? 'Custom Shift';
            $policy->name = $shiftTemplate?->name ?? 'Custom Shift';
        } else {
            if ($attendanceTimeId) {
                $shiftTemplate = DB::table('attendance_times')->where('id', $attendanceTimeId)->first();
            } else if (!empty($policy->attendance_time_id)) {
                $shiftTemplate = DB::table('attendance_times')->where('id', $policy->attendance_time_id)->first();
            } else if (($policy->shift_type ?? null) === 'flexible_part_time' || (isset($policy->policy_name) && str_contains(strtolower($policy->policy_name), 'flexible'))) {
                $shiftTemplate = DB::table('attendance_times')->where('shift_type', 'flexible_part_time')->first();
            } else {
                $defaultPolicy = $this->policyFromDefaultShift();
                $shiftTemplate = $defaultPolicy ? DB::table('attendance_times')->where('id', $defaultPolicy->id)->first() : null;
            }

            if ($shiftTemplate) {
                $policy->attendance_time_id = $shiftTemplate->id;
                $policy->id = $shiftTemplate->id;
                $policy->early_login_from = $shiftTemplate->punch_allowed_from ?? $shiftTemplate->early_login_from ?? '08:00:00';
                $policy->normal_login_from = $shiftTemplate->shift_start_time ?? $shiftTemplate->normal_login_from ?? '09:30:00';
                $policy->punch_allowed_from = $policy->early_login_from;
                $policy->shift_start_time = $policy->normal_login_from;
                $policy->late_after_time = $shiftTemplate->late_after_time;
                $policy->warning_after_time = $shiftTemplate->warning_after_time ?? $shiftTemplate->late_after_time;
                $policy->half_day_after_time = $shiftTemplate->half_day_after_time ?? $shiftTemplate->shift_end_time;
                $policy->block_after_time = $shiftTemplate->block_after_time ?? $shiftTemplate->half_day_after_time ?? $shiftTemplate->shift_end_time;
                $policy->shift_end_time = $shiftTemplate->shift_end_time;
                $policy->required_work_minutes = (int) ($shiftTemplate->required_work_minutes ?: ($policy->required_work_minutes ?? 480));
                $policy->lunch_break_minutes = (int) ($shiftTemplate->lunch_break_minutes ?? $shiftTemplate->break_minutes ?? 0);
                $policy->break_minutes = $policy->lunch_break_minutes;
                $policy->half_day_min_minutes = (int) ($shiftTemplate->half_day_min_minutes ?? ($policy->required_work_minutes ? (int) ($policy->required_work_minutes / 2) : 240));
                $policy->required_office_minutes = (int) ($shiftTemplate->required_office_minutes ?? $policy->required_work_minutes);
                $policy->absent_below_minutes = (int) ($shiftTemplate->absent_below_minutes ?? ($policy->half_day_min_minutes ? (int) ($policy->half_day_min_minutes / 2) : 120));

                $isFlexible = $shiftTemplate && (in_array(strtolower($shiftTemplate->shift_type ?? ''), ['flexible', 'flexible_part_time']) || stripos($shiftTemplate->name, 'flexible') !== false);
                $policy->shift_type = $isFlexible ? 'flexible_part_time' : 'fixed';
                $policy->policy_name = $shiftTemplate->name ?? ($policy->policy_name ?? 'Custom Shift');
                $policy->name = $shiftTemplate->name ?? ($policy->name ?? 'Custom Shift');
            }
        }

        return $policy;
    }

    public function getShiftForEmployee(Employee $employee, Carbon|string|null $date = null): ?object
    {
        return $this->getPolicyForEmployee($employee, $date);
    }

    public function getAttendanceTypeId(string $code): ?int
    {
        if (! Schema::hasTable('attendance_types')) {
            return null;
        }

        $query = AttendanceTypeM::where('code', $code);
        if (Schema::hasColumn('attendance_types', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->value('id');
    }

    public function getDayContext(Employee $employee, Carbon|string|null $date = null): array
    {
        $date = $this->date($date);
        $dateStr = $date->toDateString();
        $holiday = $this->holidayForDate($date);
        $isHoliday = (bool) $holiday;
        $isWeekoff = $this->isWeekoff($date);
        $approvedLeave = $this->getApprovedLeaveOnDate($employee, $date);

        $hasApprovedWorkRequest = DB::table('holiday_work_requests')
            ->where('employee_id', $employee->id)
            ->whereDate('worked_date', $dateStr)
            ->whereIn('status', ['approved', 'completed'])
            ->exists();

        $isHalfDayLeave = $approvedLeave && (bool) ($approvedLeave['is_half_day'] ?? false);
        $isFullLeave = $approvedLeave && ! $isHalfDayLeave;

        return [
            'is_working_day' => (! $isHoliday && ! $isWeekoff && ! $isFullLeave) || $hasApprovedWorkRequest,
            'is_holiday' => $isHoliday && ! $hasApprovedWorkRequest,
            'is_weekoff' => $isWeekoff && ! $hasApprovedWorkRequest,
            'has_approved_work_request' => $hasApprovedWorkRequest,
            'is_on_leave' => $isFullLeave,
            'is_leave' => $isFullLeave,
            'is_half_day_leave' => $isHalfDayLeave,
            'leave_slot' => $isHalfDayLeave ? ($approvedLeave['leave_slot'] ?? 'first_half') : null,
            'holiday_name' => $holiday?->name ?? $holiday?->holiday_name ?? $holiday?->title ?? null,
        ];
    }

    public function resolveMobileState(Employee $employee, Carbon|string|null $dateTime = null, ?Attendance $attendance = null): array
    {
        $now = $this->date($dateTime);
        $today = $now->toDateString();
        $isFuture = $now->copy()->startOfDay()->gt(Carbon::now(self::TIMEZONE)->startOfDay());

        $policy = $this->getPolicyForEmployee($employee, $now);
        $dayContext = $this->getDayContext($employee, $now);
        $window = $this->calculatePunchWindowState($policy, $now);

        if (! $attendance) {
            $attendance = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->latest('id')
                ->first();
        }

        $typeCode = strtolower((string) optional($attendance?->attendanceType)->code);
        $statusCode = strtolower((string) ($attendance?->attendance_status ?? ''));
        $rawStatus = $statusCode ?: ($typeCode ?: '');

        $hasPunchIn = (bool) $attendance?->punch_in_time;
        $hasPunchOut = (bool) $attendance?->punch_out_time;

        $blockedViolation = DB::table('attendance_violations')
            ->where('employee_id', $employee->id)
            ->where('type', 'blocked_punch')
            ->whereDate('violation_date', $today)
            ->first();

        $isUnlocked = (bool) ($attendance?->is_admin_unlocked ?? false)
            || ($blockedViolation && $blockedViolation->policy_action === 'resolved')
            || $statusCode === 'unlocked';

        $isBlockedViolation = $blockedViolation && $blockedViolation->policy_action !== 'resolved';

        $leaveData = $this->getApprovedLeaveOnDate($employee, $now);
        $isFullLeave = $leaveData && ! $leaveData['is_half_day'];
        $isHalfDayLeave = $leaveData && $leaveData['is_half_day'];

        $isAttendanceHalfDay = (bool) ($attendance?->is_half_day ?? false) || $isHalfDayLeave;
        $hasApprovedWorkRequest = (bool) ($dayContext['has_approved_work_request'] ?? false);

        // Check for Final Attendance States
        $isLwp = (bool) ($attendance?->is_lwp ?? false) || $rawStatus === 'lwp' || in_array($typeCode, ['lwp'], true);
        $isLeave = ! $isAttendanceHalfDay && ! $isLwp && ($isFullLeave || $rawStatus === 'leave' || $typeCode === 'leave' || (bool) ($attendance?->is_leave ?? false));
        $isHoliday = ! $hasApprovedWorkRequest && ! $hasPunchIn && ((bool) ($dayContext['is_holiday'] ?? false) || $rawStatus === 'holiday' || in_array($typeCode, ['holiday'], true));
        $isWeekoff = ! $hasApprovedWorkRequest && ! $hasPunchIn && ((bool) ($dayContext['is_weekoff'] ?? false) || $rawStatus === 'week_off' || in_array($typeCode, ['week_off'], true));
        $isHalfDay = $isAttendanceHalfDay || $rawStatus === 'half_day' || in_array($typeCode, ['half_day'], true);
        $isMissedPunch = (bool) ($attendance?->missed_punch ?? $attendance?->is_missed_punch ?? false) || $rawStatus === 'missed_punch' || in_array($typeCode, ['missed_punch'], true);
        $isAbsent = ($rawStatus === 'absent' || in_array($typeCode, ['absent'], true) || $isLwp) && (! $isUnlocked || $hasPunchOut || ! $hasPunchIn);
        $isPresent = $hasPunchIn && ! $isHalfDay && ! $isMissedPunch && ! $isLwp && ! $isAbsent;

        $isBlockedDb = (bool) (
            $attendance?->is_blocked
            || $attendance?->is_punch_blocked
            || $typeCode === 'punch_blocked'
            || $statusCode === 'punch_blocked'
        );

        $evalNow = $attendance ? Carbon::now(self::TIMEZONE) : ($dateTime ? $now : Carbon::now(self::TIMEZONE));
        $attDateStr = $attendance ? Carbon::parse($attendance->attendance_date, self::TIMEZONE)->toDateString() : $today;
        $isAttDateToday = $attDateStr === $evalNow->toDateString();

        // Priority Order: 1 Holiday, 2 Week Off, 3 Approved Leave, 4 Present, 5 Half Day, 6 Missed Punch, 7 Punch Blocked, 8 Absent
        if ($isUnlocked && ! $hasPunchIn && $isAttDateToday) {
            return [
                'status_code' => 'awaiting_punch_in',
                'status_name' => 'Awaiting Punch In',
                'attendance_state' => 'unlocked_waiting_punch_in',
                'is_blocked' => false,
                'is_punch_blocked' => false,
                'show_blocked_card' => false,
                'blocked_message' => null,
                'can_punch_in' => true,
                'can_punch_out' => false,
                'next_action' => 'punch_in',
                'primary_message' => 'Punch-in is available.',
            ];
        }

        $finalCode = null;
        if ($isHoliday) {
            $finalCode = 'holiday';
        } elseif ($isWeekoff) {
            $finalCode = 'week_off';
        } elseif ($isLeave) {
            $finalCode = 'leave';
        } elseif ($isPresent) {
            $finalCode = 'present';
        } elseif ($isHalfDay) {
            $finalCode = 'half_day';
        } elseif ($isMissedPunch) {
            $finalCode = 'missed_punch';
        } elseif ($isBlockedDb) {
            $finalCode = 'punch_blocked';
        } elseif ($isAbsent) {
            $finalCode = 'absent';
        }

        if ($finalCode !== null) {
            // STATE: FINAL ATTENDANCE STATE REACHED
            // Blocked state can NEVER override a final state.
            $statusCodeVal = $finalCode;
            $statusName = match ($finalCode) {
                'leave' => 'Leave',
                'holiday' => 'Holiday',
                'week_off' => 'Week Off',
                'present' => 'Present',
                'half_day' => 'Half Day',
                'missed_punch' => 'Missed Punch',
                'punch_blocked' => 'Punch Blocked',
                'absent' => 'Absent',
                default => ucwords(str_replace('_', ' ', $finalCode)),
            };

            $canPunchIn = false;
            $canPunchOut = $hasPunchIn && ! $hasPunchOut;
            $nextAction = $hasPunchIn ? (! $hasPunchOut ? 'punch_out' : 'completed') : 'none';
            $attendanceState = $hasPunchIn ? (! $hasPunchOut ? 'punched_in' : 'punched_out') : $finalCode;

            if ($finalCode === 'leave') {
                $canPunchOut = false;
                $nextAction = 'none';
                $attendanceState = 'leave';
            }

            if ($finalCode === 'half_day' && ! $hasPunchIn) {
                $canPunchIn = true;
                $nextAction = 'punch_in';
            }

            if (in_array($finalCode, ['absent', 'missed_punch', 'punch_blocked', 'leave', 'holiday', 'week_off'], true)) {
                $attendanceState = $finalCode;
            }

            return [
                'status_code' => $statusCodeVal,
                'status_name' => $statusName,
                'attendance_state' => $attendanceState,
                'is_blocked' => false,
                'is_punch_blocked' => false,
                'show_blocked_card' => false,
                'blocked_message' => null,
                'can_punch_in' => $canPunchIn,
                'can_punch_out' => $canPunchOut,
                'next_action' => $nextAction,
                'primary_message' => $this->primaryMessage($policy, $dayContext, $attendance, $window),
            ];
        }

        if ($isFuture) {
            return [
                'status_code' => 'future',
                'status_name' => 'Future',
                'attendance_state' => 'future',
                'is_blocked' => false,
                'is_punch_blocked' => false,
                'show_blocked_card' => false,
                'blocked_message' => null,
                'can_punch_in' => false,
                'can_punch_out' => false,
                'next_action' => 'none',
                'primary_message' => 'Upcoming date.',
            ];
        }

        $isBlockedDb = (bool) (
            $attendance?->is_blocked
            || $attendance?->is_punch_blocked
            || $typeCode === 'punch_blocked'
            || $statusCode === 'punch_blocked'
        );

        if ($isBlockedDb) {
            return [
                'status_code' => 'punch_blocked',
                'status_name' => 'Punch Blocked',
                'attendance_state' => 'punch_blocked',
                'is_blocked' => true,
                'is_punch_blocked' => true,
                'show_blocked_card' => true,
                'blocked_message' => 'Your punch-in is blocked. Please contact HR/Admin.',
                'can_punch_in' => false,
                'can_punch_out' => false,
                'next_action' => 'blocked',
                'primary_message' => 'Punch-in is blocked.',
            ];
        }

        $isPastDate = Carbon::parse($today, self::TIMEZONE)->startOfDay()->lt($evalNow->copy()->startOfDay());
        $isPastShiftEnd = $window['is_after_shift_end'] ?? false;

        if (! $hasPunchIn && ($isPastDate || $isPastShiftEnd)) {
            return [
                'status_code' => 'absent',
                'status_name' => 'Absent',
                'attendance_state' => 'absent',
                'is_blocked' => false,
                'is_punch_blocked' => false,
                'show_blocked_card' => false,
                'blocked_message' => null,
                'can_punch_in' => false,
                'can_punch_out' => false,
                'next_action' => 'none',
                'primary_message' => null,
            ];
        }

        $canPunchIn = $dayContext['is_working_day'] && $window['is_allowed'];
        $nextAction = $canPunchIn ? 'punch_in' : 'none';

        return [
            'status_code' => 'not_punched',
            'status_name' => 'Not Punched',
            'attendance_state' => 'not_punched',
            'is_blocked' => false,
            'is_punch_blocked' => false,
            'show_blocked_card' => false,
            'blocked_message' => null,
            'can_punch_in' => $canPunchIn,
            'can_punch_out' => false,
            'next_action' => $nextAction,
            'primary_message' => $this->primaryMessage($policy, $dayContext, $attendance, $window),
        ];
    }

    public function buildMobileRulePayload(Employee $employee, Carbon|string|null $dateTime = null): array
    {
        $now = $this->date($dateTime);
        $policy = $this->getPolicyForEmployee($employee, $now);
        $dayContext = $this->getDayContext($employee, $now);

        $today = $now->toDateString();
        $attendance = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->latest('id')
            ->first();

        $window = $this->calculatePunchWindowState($policy, $now);
        $state = $this->resolveMobileState($employee, $now, $attendance);

        $isBlocked = $state['is_blocked'];
        $isPunchBlocked = $state['is_punch_blocked'];
        $showBlockedCard = $state['show_blocked_card'];
        $canPunchIn = $state['can_punch_in'];
        $canPunchOut = $state['can_punch_out'];
        $attendanceState = $state['attendance_state'];
        $statusCodeVal = $state['status_code'];
        $nextAction = $state['next_action'];
        $primaryMessage = $state['primary_message'];
        $blockedMessage = $state['blocked_message'];

        $requiredWorkMinutes = 0;
        if ($policy) {
            $requiredWorkMinutes = (int) ($policy->required_work_minutes ?? 0);
        }
        if ($requiredWorkMinutes <= 0) {
            $shift = $this->policyFromDefaultShift();
            if ($shift) {
                $requiredWorkMinutes = (int) ($shift->required_work_minutes ?? 0);
            }
        }
        if ($requiredWorkMinutes <= 0) {
            if (Schema::hasTable('attendance_policy_rules')) {
                $requiredWorkMinutes = (int) (DB::table('attendance_policy_rules')->where('required_work_minutes', '>', 0)->value('required_work_minutes') ?? 0);
            }
        }
        if ($requiredWorkMinutes <= 0) {
            if (Schema::hasTable('attendance_times')) {
                $requiredWorkMinutes = (int) (DB::table('attendance_times')->where('required_work_minutes', '>', 0)->value('required_work_minutes') ?? 0);
            }
        }

        if ($policy) {
            $policy->required_work_minutes = $requiredWorkMinutes;
        }

        $breakMinutes = 0;
        if ($policy) {
            $breakMinutes = (int) ($policy->lunch_break_minutes ?? $policy->break_minutes ?? 0);
        }
        if ($breakMinutes <= 0) {
            $shift = $this->policyFromDefaultShift();
            if ($shift) {
                $breakMinutes = (int) ($shift->lunch_break_minutes ?? $shift->break_minutes ?? 0);
            }
        }
        if ($breakMinutes <= 0) {
            if (Schema::hasTable('attendance_times')) {
                $breakMinutes = (int) (DB::table('attendance_times')->where('lunch_break_minutes', '>', 0)->value('lunch_break_minutes') ?? 0);
            }
        }

        $remainingSeconds = 0;
        if ($attendance && $attendance->punch_in_time && ! $attendance->punch_out_time) {
            $targetVal = $attendance->target_punch_out_time;
            if ($targetVal) {
                $target = Carbon::parse($today . ' ' . $this->timeString($targetVal), self::TIMEZONE);
            } else {
                $in = Carbon::parse($today . ' ' . $this->timeString($attendance->punch_in_time), self::TIMEZONE);
                $target = $this->targetPunchOut($in, $policy);
            }
            $remainingSeconds = max(0, $now->diffInSeconds($target, false));
        }

        if ($attendance && $state['status_code'] === 'awaiting_punch_in' && ! $attendance->punch_in_time) {
            $attendance->status_code = 'awaiting_punch_in';
            $attendance->status_name = 'Awaiting Punch In';
            $attendance->attendance_status = 'unlocked';
            $attendance->display_status = 'Awaiting Punch In';

            $mockType = new \App\Models\HRMS\Attendance\AttendanceTypeM([
                'code' => 'awaiting_punch_in',
                'name' => 'Awaiting Punch In',
            ]);
            $attendance->setRelation('attendanceType', $mockType);
        }

        return [
            'server_time' => $now->format('Y-m-d H:i:s'),
            'timezone' => self::TIMEZONE,
            'policy' => $this->policyPayload($policy),
            'required_work_minutes' => $requiredWorkMinutes,
            'remaining_work_seconds' => $remainingSeconds,
            'break_minutes' => $breakMinutes,
            'day_context' => $dayContext,
            'attendance' => $attendance,
            'ui' => [
                'attendance_state' => $attendanceState,
                'can_punch_in' => $canPunchIn,
                'can_punch_out' => $canPunchOut,
                'is_blocked' => $isBlocked,
                'is_punch_blocked' => $isPunchBlocked,
                'status_code' => $statusCodeVal,
                'status_name' => $state['status_name'],
                'next_action' => $nextAction,
                'show_early_login_tag' => $window['is_before_shift_start'] && $canPunchIn,
                'show_late_mark' => $attendance ? (bool) ($attendance->is_late ?? false) : $window['is_late'],
                'show_late_warning' => $window['is_warning'],
                'show_blocked_card' => $showBlockedCard,
                'primary_message' => $primaryMessage,
                'warning_message' => $window['is_warning'] ? 'Late punch-in. Warning: punch will be blocked after ' . $this->displayTime($policy?->block_after_time) . '.' : null,
                'blocked_message' => $blockedMessage,
            ],
        ];
    }

    public function calculatePunchWindowState(?object $policy, Carbon|string|null $dateTime = null): array
    {
        $now = $this->date($dateTime);
        $earlyLoginFrom = $this->timeOnDate($policy?->early_login_from ?? $policy?->punch_allowed_from, $now);
        $shiftStart = $this->timeOnDate($policy?->shift_start_time ?? $policy?->normal_login_from, $now);
        $lateAfter = $this->timeOnDate($policy?->late_after_time, $now);
        $warningAfter = $this->timeOnDate($policy?->warning_after_time, $now);
        $halfDayAfter = $this->timeOnDate($policy?->half_day_after_time, $now);
        $blockAfter = $this->timeOnDate($policy?->block_after_time ?? $policy?->half_day_after_time, $now);
        $shiftEnd = $this->timeOnDate($policy?->shift_end_time, $now);

        $isFlexible = ($policy?->shift_type ?? 'fixed') === 'flexible_part_time';
        $isBeforeEarlyLogin = $earlyLoginFrom ? $now->lt($earlyLoginFrom) : false;
        $isAfterShiftEnd = $shiftEnd ? $now->gt($shiftEnd) : false;
        $isBlocked = $blockAfter ? $now->gt($blockAfter) : false;

        $isHalfDayPunch = (! $isFlexible && $halfDayAfter)
            ? $now->gte($halfDayAfter)
            : false;

        return [
            'is_before_early_login' => $isBeforeEarlyLogin,
            'is_before_allowed_from' => $isBeforeEarlyLogin,
            'is_before_shift_start' => $shiftStart ? $now->lt($shiftStart) : false,
            'is_late' => ! $isFlexible && $lateAfter ? $now->gt($lateAfter) : false,
            'is_warning' => ! $isFlexible && $warningAfter && $blockAfter ? $now->betweenIncluded($warningAfter, $blockAfter) : false,
            'is_half_day_punch' => $isHalfDayPunch,
            'is_blocked' => $isBlocked,
            'is_after_shift_end' => $isAfterShiftEnd,
            'is_allowed' => ! $isBeforeEarlyLogin,
            'allowed_from' => $earlyLoginFrom,
            'half_day_after' => $halfDayAfter,
            'block_after' => $blockAfter,
            'shift_end' => $shiftEnd,
        ];
    }

    public function calculateWorkMinutes(Attendance $attendance, ?object $policy = null): array
    {
        if (! $attendance->punch_in_time || ! $attendance->punch_out_time) {
            return ['gross_minutes' => 0, 'break_minutes' => 0, 'net_minutes' => 0, 'target_punch_out_time' => null, 'is_early_out' => false, 'early_out_minutes' => 0];
        }

        $policy = $policy ?: $this->policyForAttendance($attendance);
        $date = Carbon::parse($attendance->attendance_date, self::TIMEZONE)->toDateString();
        $in = Carbon::parse($date . ' ' . $this->timeString($attendance->punch_in_time), self::TIMEZONE);
        $out = Carbon::parse($date . ' ' . $this->timeString($attendance->punch_out_time), self::TIMEZONE);
        if ($out->lt($in)) {
            $out->addDay();
        }

        $isFlexible = ($policy?->shift_type ?? 'fixed') === 'flexible_part_time';
        $breakMinutes = (int) ($policy?->lunch_break_minutes ?? $policy?->break_minutes ?? 0);
        $grossMinutes = $in->diffInMinutes($out);

        $status = $attendance->attendance_status ?? ($attendance->is_half_day ? 'half_day' : 'present');
        $target = $attendance->target_punch_out_time
            ? Carbon::parse($date . ' ' . $this->timeString($attendance->target_punch_out_time), self::TIMEZONE)
            : $this->targetPunchOut($in, $policy, $status);
        if ($target->lt($in)) {
            $target->addDay();
        }

        $isEarlyOut = $out->lt($target);
        $earlyOutMinutes = $isEarlyOut ? $out->diffInMinutes($target) : 0;

        return [
            'gross_minutes' => $grossMinutes,
            'break_minutes' => $breakMinutes,
            'net_minutes' => max(0, $grossMinutes - $breakMinutes),
            'target_punch_out_time' => $target->format('H:i:s'),
            'is_early_out' => $isEarlyOut,
            'early_out_minutes' => $earlyOutMinutes,
        ];
    }

    public function calculateFinalStatus(Attendance $attendance, ?object $policy = null): array
    {
        $policy = $policy ?: $this->policyForAttendance($attendance);
        $isFlexible = ($policy?->shift_type ?? 'fixed') === 'flexible_part_time';
        $work = $this->calculateWorkMinutes($attendance, $policy);
        $required = (int) ($policy?->required_work_minutes ?? 0);
        $halfDay = (int) ($policy?->half_day_min_minutes ?? 0);
        $absentBelow = (int) ($policy?->absent_below_minutes ?? $halfDay);
        $violationLimit = $isFlexible ? 0 : (int) ($policy?->combined_violation_limit ?? 0);
        $violationCount = $isFlexible ? 0 : ((int) $attendance->is_late + (int) $work['is_early_out']);

        $effectiveHalfDayMin = $halfDay > 0 ? $halfDay : ($required > 0 ? (int)($required / 2) : 240);
        $effectiveAbsentBelow = ($absentBelow > 0 && $absentBelow < $effectiveHalfDayMin) ? $absentBelow : (int)($effectiveHalfDayMin / 2);

        $isPreExistingLwp = (bool) $attendance->is_lwp
            || strtolower((string) $attendance->attendance_status) === 'lwp'
            || ! empty($attendance->lwp_reason);

        $isPreExistingHalfDay = (bool) $attendance->is_half_day
            || in_array(strtolower((string) $attendance->attendance_status), ['half_day', 'half_leave', 'first_half_leave', 'second_half_leave'], true)
            || ! empty($attendance->half_day_reason);

        if ($isPreExistingLwp) {
            $code = 'lwp';
        } elseif ($isPreExistingHalfDay) {
            // Already marked Half Day via Step 1 Leave or Step 2 Half Day Punch Window
            if ($effectiveAbsentBelow > 0 && $work['net_minutes'] < $effectiveAbsentBelow) {
                $code = 'lwp';
            } elseif ($effectiveHalfDayMin > 0 && $work['net_minutes'] < $effectiveHalfDayMin) {
                $code = 'lwp';
            } else {
                $code = 'half_day';
            }
        } else {
            // Standard Attendance Punch
            if ($effectiveAbsentBelow > 0 && $work['net_minutes'] < $effectiveAbsentBelow) {
                $code = 'lwp';
            } elseif ($effectiveHalfDayMin > 0 && $work['net_minutes'] < $effectiveHalfDayMin) {
                $code = 'lwp';
            } elseif ($isFlexible && $required > 0 && $work['net_minutes'] < $required) {
                $code = 'half_day';
            } elseif (! $isFlexible && $violationLimit > 0 && $violationCount >= $violationLimit) {
                $code = 'half_day';
            } else {
                // Completed required work duration or at least half_day_min_minutes on fixed shift
                $code = 'present';
            }
        }

        return $work + [
            'attendance_type_code' => $code,
            'attendance_type_id' => $this->getAttendanceTypeId($code),
            'violation_count' => $violationCount,
            'is_half_day' => $code === 'half_day',
            'is_lwp' => $code === 'lwp',
        ];
    }

    public function targetPunchOut(Carbon $punchIn, ?object $policy, string $status = 'present'): Carbon
    {
        $required = (int) ($policy?->required_work_minutes ?? 0);
        $halfDay = (int) ($policy?->half_day_min_minutes ?? 0);
        if ($halfDay <= 0 && $required > 0) {
            $halfDay = (int) ($required / 2);
        }

        $isHalfDayStatus = in_array(strtolower($status), ['half_day', 'half_day_lwp', 'half_leave', 'first_half_leave', 'second_half_leave'], true);
        $applicableWorkMinutes = ($isHalfDayStatus && $halfDay > 0) ? $halfDay : $required;
        $breakMinutes = $isHalfDayStatus ? 0 : (int) ($policy?->lunch_break_minutes ?? $policy?->lunch_minutes ?? $policy?->break_minutes ?? 0);

        return $punchIn->copy()->addMinutes($applicableWorkMinutes + $breakMinutes);
    }

    public function policyPayload(?object $policy): ?array
    {
        if (! $policy) {
            return null;
        }

        $reqMins = (int) ($policy->required_work_minutes ?? 0);
        $halfDayMinMins = (int) ($policy->half_day_min_minutes ?? ($reqMins > 0 ? (int) ($reqMins / 2) : 0));
        $breakMins = (int) ($policy->lunch_break_minutes ?? $policy->break_minutes ?? 0);

        $shiftStart = $this->timeString($policy->shift_start_time ?? $policy->normal_login_from ?? null);
        $shiftEnd = $this->timeString($policy->shift_end_time ?? null);
        $punchAllowedFrom = $this->timeString($policy->punch_allowed_from ?? $policy->early_login_from ?? null);
        $lateAfter = $this->timeString($policy->late_after_time ?? null);
        $warningAfter = $this->timeString($policy->warning_after_time ?? null);
        $halfDayAfter = $this->timeString($policy->half_day_after_time ?? null);
        $blockAfter = $this->timeString($policy->block_after_time ?? null);

        $warningWindowMins = null;
        if ($warningAfter && $shiftStart) {
            try {
                $st = Carbon::parse('2026-01-01 ' . $shiftStart);
                $wa = Carbon::parse('2026-01-01 ' . $warningAfter);
                $warningWindowMins = max(0, $wa->diffInMinutes($st));
            } catch (\Exception $e) {
                $warningWindowMins = null;
            }
        }

        $earlyOutHalfDayMins = (int) ($policy->early_out_half_day_minutes ?? 60);
        $missedPunchAfterMins = (int) ($policy->missed_punch_after_minutes ?? 60);

        $shiftEndCarbon = $shiftEnd ? Carbon::parse('2026-01-01 ' . $shiftEnd, self::TIMEZONE) : null;
        $earlyOutHalfDayCutoff = $shiftEndCarbon ? $shiftEndCarbon->copy()->subMinutes($earlyOutHalfDayMins)->format('H:i:s') : null;
        $missedPunchCutoff = $shiftEndCarbon ? $shiftEndCarbon->copy()->addMinutes($missedPunchAfterMins)->format('H:i:s') : null;

        return [
            'id' => $policy->id ?? null,
            'policy_name' => $policy->policy_name ?? $policy->name ?? 'Default Policy',
            'name' => $policy->policy_name ?? $policy->name ?? 'Default Policy',
            'shift_name' => $policy->policy_name ?? $policy->name ?? 'Default Policy',
            'shift_type' => (string) ($policy->shift_type ?? 'fixed'),
            'is_flexible' => ($policy->shift_type ?? 'fixed') === 'flexible_part_time',

            'punch_allowed_from' => $punchAllowedFrom,
            'punchAllowedFrom' => $punchAllowedFrom,
            'shift_start_time' => $shiftStart,
            'shift_start' => $shiftStart,
            'shiftStart' => $shiftStart,
            'late_after_time' => $lateAfter,
            'late_after' => $lateAfter,
            'lateAfter' => $lateAfter,
            'warning_after_time' => $warningAfter,
            'warning_window' => $warningWindowMins,
            'warningWindow' => $warningWindowMins,
            'half_day_after_time' => $halfDayAfter,
            'half_day_after' => $halfDayAfter,
            'halfDayAfter' => $halfDayAfter,
            'halfDayAfterTime' => $halfDayAfter,
            'block_after_time' => $blockAfter,
            'block_after' => $blockAfter,
            'blockAfter' => $blockAfter,
            'shift_end_time' => $shiftEnd,
            'shift_end' => $shiftEnd,
            'shiftEnd' => $shiftEnd,

            'early_out_half_day_minutes' => $earlyOutHalfDayMins,
            'earlyOutHalfDayMinutes' => $earlyOutHalfDayMins,
            'missed_punch_after_minutes' => $missedPunchAfterMins,
            'missedPunchAfterMinutes' => $missedPunchAfterMins,
            'early_out_half_day_cutoff' => $earlyOutHalfDayCutoff,
            'earlyOutHalfDayCutoff' => $earlyOutHalfDayCutoff,
            'missed_punch_cutoff' => $missedPunchCutoff,
            'missedPunchCutoff' => $missedPunchCutoff,

            'required_work_minutes' => $reqMins,
            'requiredWorkMinutes' => $reqMins,
            'required_work_hours' => $reqMins > 0 ? (round($reqMins / 60, 1) . 'h') : null,
            'requiredWorkHours' => $reqMins > 0 ? (round($reqMins / 60, 1) . 'h') : null,

            'half_day_min_minutes' => $halfDayMinMins,
            'half_day_minimum' => $halfDayMinMins,
            'halfDayMinimum' => $halfDayMinMins,
            'half_day_minutes' => $halfDayMinMins,
            'halfDayMinutes' => $halfDayMinMins,

            'absent_below_minutes' => (int) ($policy->absent_below_minutes ?? 0),

            'lunch_minutes' => $breakMins,
            'lunchMinutes' => $breakMins,
            'lunch_break_minutes' => $breakMins,
            'break_minutes' => $breakMins,
            'breakMinutes' => $breakMins,

            'allowed_missed_punches' => (int) ($policy->allowed_missed_punches ?? 0),
            'combined_violation_limit' => (int) ($policy->combined_violation_limit ?? 0),
            'punch_block_enabled' => (bool) ($policy->punch_block_enabled ?? $policy->auto_block_enabled ?? true),
            'punchBlockEnabled' => (bool) ($policy->punch_block_enabled ?? $policy->auto_block_enabled ?? true),
            'auto_block_enabled' => (bool) ($policy->punch_block_enabled ?? $policy->auto_block_enabled ?? true),
            'auto_absent_enabled' => (bool) ($policy->auto_absent_enabled ?? false),
            'allow_web_punch' => (bool) ($policy->allow_web_punch ?? true),
            'allow_mobile_punch' => (bool) ($policy->allow_mobile_punch ?? true),
            'mobile_only' => ! (bool) ($policy->allow_web_punch ?? true) && (bool) ($policy->allow_mobile_punch ?? true),
            'mobile_punch' => (bool) ($policy->allow_mobile_punch ?? true),
            'is_mobile_only' => ! (bool) ($policy->allow_web_punch ?? true) && (bool) ($policy->allow_mobile_punch ?? true),
        ];
    }

    public function timeString($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value, self::TIMEZONE)->format('H:i:s');
    }

    public function date(Carbon|string|null $dateTime = null): Carbon
    {
        if ($dateTime instanceof Carbon) {
            return $dateTime->copy()->setTimezone(self::TIMEZONE);
        }
        if (empty($dateTime)) {
            return Carbon::now(self::TIMEZONE);
        }
        return Carbon::parse($dateTime, self::TIMEZONE);
    }

    private function policyFromEmployeeOverride(Employee $employee, string $date): ?object
    {
        if (! Schema::hasTable('attendance_policy_employee_overrides') || ! Schema::hasTable('attendance_policy_rules')) {
            return null;
        }

        $override = DB::table('attendance_policy_employee_overrides')
            ->where('employee_id', $employee->id)
            ->where('is_active', 1)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        return $this->markSource($override ? DB::table('attendance_policy_rules')->where('id', $override->attendance_policy_rule_id)->first() : null, 'attendance_policy_rules');
    }

    private function policyFromEmployeeAssignment(Employee $employee, string $date): ?object
    {
        if (! Schema::hasTable('employee_policy_assignments') || ! Schema::hasTable('attendance_policy_rules')) {
            return null;
        }

        $assignment = DB::table('employee_policy_assignments')
            ->where('employee_id', $employee->id)
            ->where('policy_type', 'attendance')
            ->where('is_active', 1)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        return $this->markSource($assignment ? DB::table('attendance_policy_rules')->where('id', $assignment->policy_id)->first() : null, 'attendance_policy_rules');
    }

    private function policyFromEmployeeColumn(Employee $employee): ?object
    {
        if (! Schema::hasTable('attendance_policy_rules')) {
            return null;
        }

        $policyId = $employee->attendance_policy_id ?? $employee->attendance_policy_rule_id ?? null;
        if (! $policyId) {
            return null;
        }

        return $this->markSource(DB::table('attendance_policy_rules')->where('id', $policyId)->first(), 'attendance_policy_rules');
    }

    private function defaultAttendancePolicy(): ?object
    {
        if (! Schema::hasTable('attendance_policy_rules')) {
            return null;
        }

        $query = DB::table('attendance_policy_rules');
        if (Schema::hasColumn('attendance_policy_rules', 'is_active')) {
            $query->orderByDesc('is_active');
        }

        return $this->markSource($query->orderBy('id')->first(), 'attendance_policy_rules');
    }

    private function policyFromDefaultShift(): ?object
    {
        if (! Schema::hasTable('attendance_times')) {
            return null;
        }

        $query = AttendanceTimeM::query();
        if (Schema::hasColumn('attendance_times', 'is_active')) {
            $query->where('is_active', true);
        }

        return $this->markSource($query->orderByDesc('is_default')->orderBy('id')->first(), 'attendance_times');
    }

    private function normalizePolicy(object $policy): object
    {
        $shift = $this->policyFromDefaultShift();
        $fields = ['punch_allowed_from', 'shift_start_time', 'late_after_time', 'warning_after_time', 'block_after_time', 'shift_end_time', 'required_work_minutes', 'half_day_min_minutes', 'absent_below_minutes', 'lunch_break_minutes', 'break_minutes'];

        foreach ($fields as $field) {
            if (! isset($policy->{$field}) && $shift && isset($shift->{$field})) {
                $policy->{$field} = $shift->{$field};
            }
        }

        $policy->policy_name = $policy->policy_name ?? $policy->name ?? null;
        $policy->auto_block_enabled = (bool) ($policy->auto_block_enabled ?? true);
        $policy->auto_absent_enabled = (bool) ($policy->auto_absent_enabled ?? true);

        return $policy;
    }

    private function markSource(?object $policy, string $source): ?object
    {
        if ($policy) {
            $policy->source_table = $source;
        }

        return $policy;
    }

    private function timeOnDate($time, Carbon $date): ?Carbon
    {
        return $time ? Carbon::parse($date->toDateString() . ' ' . $this->timeString($time), self::TIMEZONE) : null;
    }

    private function policyForAttendance(Attendance $attendance): ?object
    {
        $employee = $attendance->employee ?: Employee::find($attendance->employee_id);
        if (! $employee) {
            return $this->defaultAttendancePolicy();
        }

        return $this->resolveShiftPolicy($employee, $attendance->attendance_date, $attendance->attendance_time_id);
    }

    private function primaryMessage(?object $policy, array $dayContext, ?Attendance $attendance, array $window): ?string
    {
        if ($dayContext['has_approved_work_request'] ?? false) {
            if ($attendance?->punch_out_time) {
                return 'Attendance completed for today.';
            }
            if ($attendance?->punch_in_time) {
                return 'Punch-out is available.';
            }
            return 'Approved Work Request for today. Punch-in is available.';
        }
        if ($dayContext['is_holiday']) {
            return 'Today is a holiday.';
        }
        if ($dayContext['is_weekoff']) {
            return 'Today is a week off.';
        }
        if ($dayContext['is_on_leave']) {
            return 'You are on approved leave today.';
        }
        if ($attendance?->punch_out_time) {
            return 'Attendance completed for today.';
        }
        if ($attendance?->punch_in_time) {
            return 'Punch-out is available.';
        }
        if ($window['is_before_allowed_from']) {
            return 'Punch-in is allowed from ' . $this->displayTime($policy?->punch_allowed_from) . '.';
        }

        return null;
    }

    private function displayTime($time): string
    {
        return $time ? Carbon::parse($time, self::TIMEZONE)->format('h:i A') : 'configured time';
    }

    private function holidayForDate(Carbon $date): ?object
    {
        if (! Schema::hasTable('holidays')) {
            return null;
        }

        $dateColumn = Schema::hasColumn('holidays', 'holiday_date') ? 'holiday_date' : (Schema::hasColumn('holidays', 'date') ? 'date' : null);
        if (! $dateColumn) {
            return null;
        }

        $query = DB::table('holidays')->whereDate($dateColumn, $date->toDateString());
        if (Schema::hasColumn('holidays', 'is_active')) {
            $query->where('is_active', 1);
        }

        $holiday = $query->first();
        if ($holiday && isset($holiday->is_working_day_override) && (int) $holiday->is_working_day_override === 1) {
            return null;
        }

        return $holiday;
    }

    private function isWeekoff(Carbon $date): bool
    {
        if (! Schema::hasTable('weekoff_rules')) {
            return false;
        }

        $dayName = strtolower($date->format('l'));
        $isoWeekday = (int) $date->isoWeekday();
        $weekNumber = (int) ceil($date->day / 7);
        $query = DB::table('weekoff_rules');

        if (Schema::hasColumn('weekoff_rules', 'is_active')) {
            $query->where('is_active', 1);
        }
        if (Schema::hasColumn('weekoff_rules', 'effective_from')) {
            $query->where(function ($q) use ($date) {
                $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date->toDateString());
            });
        }
        if (Schema::hasColumn('weekoff_rules', 'effective_to')) {
            $query->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date->toDateString());
            });
        }

        $query->where(function ($q) use ($dayName, $isoWeekday) {
            if (Schema::hasColumn('weekoff_rules', 'weekday')) {
                $q->orWhere('weekday', $isoWeekday);
            }
            if (Schema::hasColumn('weekoff_rules', 'day_name')) {
                $q->orWhereRaw('LOWER(day_name) = ?', [$dayName]);
            }
            if (Schema::hasColumn('weekoff_rules', 'day_of_week')) {
                $q->orWhere('day_of_week', $isoWeekday)->orWhereRaw('LOWER(day_of_week) = ?', [$dayName]);
            }
        });

        if (Schema::hasColumn('weekoff_rules', 'week_number')) {
            $query->where(function ($q) use ($weekNumber) {
                $q->whereNull('week_number')->orWhere('week_number', 0)->orWhere('week_number', $weekNumber);
            });
        }

        if (Schema::hasColumn('weekoff_rules', 'week_number')) {
            $query->orderByRaw('CASE WHEN week_number IS NULL THEN 1 ELSE 0 END');
        }

        $rule = $query->first();
        if (! $rule) {
            return false;
        }

        if (isset($rule->is_working) && (int) $rule->is_working === 1) {
            return false;
        }

        return isset($rule->is_off) ? (int) $rule->is_off === 1 : true;
    }

    public function getApprovedLeaveOnDate(Employee $employee, Carbon|string $date): ?array
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : (string) $date;

        if (Schema::hasTable('leave_requests')) {
            $query = DB::table('leave_requests')
                ->where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereRaw('? BETWEEN start_date AND end_date', [$dateStr]);

            if (Schema::hasTable('leave_request_dates')) {
                $queryOrDates = DB::table('leave_requests')
                    ->join('leave_request_dates', 'leave_request_dates.leave_request_id', '=', 'leave_requests.id')
                    ->where('leave_requests.employee_id', $employee->id)
                    ->where('leave_requests.status', 'approved')
                    ->whereDate('leave_request_dates.leave_date', $dateStr)
                    ->select('leave_requests.*');
                
                $leaveReq = $queryOrDates->first() ?: $query->first();
            } else {
                $leaveReq = $query->first();
            }

            if ($leaveReq) {
                $isHalfDay = (bool) ($leaveReq->is_half_day ?? false)
                    || ! empty($leaveReq->half_day_type)
                    || ! empty($leaveReq->leave_slot)
                    || ($leaveReq->leave_type ?? '') === 'half_day';

                $slot = $leaveReq->half_day_type
                    ?? $leaveReq->leave_slot
                    ?? $leaveReq->slot
                    ?? ($isHalfDay ? 'first_half' : null);

                return [
                    'id' => $leaveReq->id,
                    'leave_request_id' => $leaveReq->id,
                    'leave_type' => $leaveReq->leave_type ?? 'leave',
                    'is_half_day' => $isHalfDay,
                    'leave_slot' => $slot,
                    'half_day_type' => $slot,
                    'status' => 'approved',
                ];
            }
        }

        if (Schema::hasTable('leave_applications')) {
            $app = DB::table('leave_applications')
                ->where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->first();

            if ($app) {
                $isHalfDay = (bool) ($app->is_half_day ?? false)
                    || ! empty($app->half_day_type)
                    || ! empty($app->leave_slot)
                    || ($app->type ?? '') === 'half_day';

                $slot = $app->half_day_type
                    ?? $app->leave_slot
                    ?? $app->slot
                    ?? ($isHalfDay ? 'first_half' : null);

                return [
                    'id' => $app->id,
                    'leave_application_id' => $app->id,
                    'leave_type' => $app->type ?? $app->leave_type ?? 'leave',
                    'is_half_day' => $isHalfDay,
                    'leave_slot' => $slot,
                    'half_day_type' => $slot,
                    'status' => 'approved',
                ];
            }
        }

        return null;
    }

    private function isOnLeave(Employee $employee, Carbon $date): bool
    {
        return $this->getApprovedLeaveOnDate($employee, $date) !== null;
    }
}
