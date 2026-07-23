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
        $date = $this->date($date)->toDateString();

        $policy = $this->policyFromEmployeeOverride($employee, $date)
            ?: $this->policyFromEmployeeAssignment($employee, $date)
            ?: $this->policyFromEmployeeColumn($employee)
            ?: $this->defaultAttendancePolicy()
            ?: $this->policyFromDefaultShift();

        return $policy ? $this->normalizePolicy($policy) : null;
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
        $holiday = $this->holidayForDate($date);
        $isHoliday = (bool) $holiday;
        $isWeekoff = $this->isWeekoff($date);
        $isOnLeave = $this->isOnLeave($employee, $date);

        return [
            'is_working_day' => ! $isHoliday && ! $isWeekoff && ! $isOnLeave,
            'is_holiday' => $isHoliday,
            'is_weekoff' => $isWeekoff,
            'is_on_leave' => $isOnLeave,
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

        // Check for Final Attendance States
        $isLeave = $dayContext['is_on_leave'] || $rawStatus === 'leave' || in_array($typeCode, ['leave'], true) || (bool) ($attendance?->is_leave ?? false);
        $isHoliday = $dayContext['is_holiday'] || $rawStatus === 'holiday' || in_array($typeCode, ['holiday'], true);
        $isWeekoff = $dayContext['is_weekoff'] || $rawStatus === 'week_off' || in_array($typeCode, ['week_off'], true);
        $isHalfDay = (bool) ($attendance?->is_half_day ?? false) || $rawStatus === 'half_day' || in_array($typeCode, ['half_day'], true);
        $isMissedPunch = (bool) ($attendance?->missed_punch ?? $attendance?->is_missed_punch ?? false) || $rawStatus === 'missed_punch' || in_array($typeCode, ['missed_punch'], true);
        $isLwp = (bool) ($attendance?->is_lwp ?? false) || $rawStatus === 'lwp' || in_array($typeCode, ['lwp'], true);
        $isAbsent = ($rawStatus === 'absent' || in_array($typeCode, ['absent'], true)) && ! $isUnlocked;
        $isPresent = $hasPunchIn && ! $isHalfDay && ! $isMissedPunch && ! $isLwp && ! $isAbsent;

        $isBlockedDb = (bool) (
            $attendance?->is_blocked
            || $attendance?->is_punch_blocked
            || $typeCode === 'punch_blocked'
            || $statusCode === 'punch_blocked'
        );

        $evalNow = Carbon::now(self::TIMEZONE);
        $attDateStr = $attendance ? Carbon::parse($attendance->attendance_date, self::TIMEZONE)->toDateString() : $evalNow->toDateString();
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
        $allowedFrom = $this->timeOnDate($policy?->punch_allowed_from, $now);
        $shiftStart = $this->timeOnDate($policy?->shift_start_time, $now);
        $lateAfter = $this->timeOnDate($policy?->late_after_time, $now);
        $warningAfter = $this->timeOnDate($policy?->warning_after_time, $now);
        $blockAfter = $this->timeOnDate($policy?->block_after_time, $now);
        $shiftEnd = $this->timeOnDate($policy?->shift_end_time, $now);

        $isAfterShiftEnd = $shiftEnd ? $now->gt($shiftEnd) : false;

        return [
            'is_before_allowed_from' => $allowedFrom ? $now->lt($allowedFrom) : false,
            'is_before_shift_start' => $shiftStart ? $now->lt($shiftStart) : false,
            'is_late' => $lateAfter ? $now->gt($lateAfter) : false,
            'is_warning' => $warningAfter && $blockAfter ? $now->betweenIncluded($warningAfter, $blockAfter) : false,
            'is_blocked' => $blockAfter ? $now->gt($blockAfter) : false,
            'is_after_shift_end' => $isAfterShiftEnd,
            'is_allowed' => (! $allowedFrom || $now->gte($allowedFrom)) && (! $shiftEnd || $now->lte($shiftEnd)),
            'allowed_from' => $allowedFrom,
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

        $breakMinutes = (int) ($policy?->lunch_break_minutes ?? $policy?->break_minutes ?? 0);
        $grossMinutes = $in->diffInMinutes($out);
        $target = $attendance->target_punch_out_time
            ? Carbon::parse($date . ' ' . $this->timeString($attendance->target_punch_out_time), self::TIMEZONE)
            : $this->targetPunchOut($in, $policy);
        if ($target->lt($in)) {
            $target->addDay();
        }

        return [
            'gross_minutes' => $grossMinutes,
            'break_minutes' => $breakMinutes,
            'net_minutes' => max(0, $grossMinutes - $breakMinutes),
            'target_punch_out_time' => $target->format('H:i:s'),
            'is_early_out' => $out->lt($target),
            'early_out_minutes' => $out->lt($target) ? $out->diffInMinutes($target) : 0,
        ];
    }

    public function calculateFinalStatus(Attendance $attendance, ?object $policy = null): array
    {
        $policy = $policy ?: $this->policyForAttendance($attendance);
        $work = $this->calculateWorkMinutes($attendance, $policy);
        $required = (int) ($policy?->required_work_minutes ?? 0);
        $halfDay = (int) ($policy?->half_day_min_minutes ?? 0);
        $absentBelow = (int) ($policy?->absent_below_minutes ?? $halfDay);
        $violationLimit = (int) ($policy?->combined_violation_limit ?? 0);
        $violationCount = (int) $attendance->is_late + (int) $work['is_early_out'];
        $code = 'present';

        if ($absentBelow > 0 && $work['net_minutes'] < $absentBelow) {
            $code = 'lwp';
        } elseif (($halfDay > 0 && $work['net_minutes'] < $halfDay) || ($violationLimit > 0 && $violationCount >= $violationLimit)) {
            $code = 'half_day';
        }

        return $work + [
            'attendance_type_code' => $code,
            'attendance_type_id' => $this->getAttendanceTypeId($code),
            'violation_count' => $violationCount,
            'is_half_day' => $code === 'half_day',
            'is_lwp' => $code === 'lwp',
        ];
    }

    public function targetPunchOut(Carbon $punchIn, ?object $policy): Carbon
    {
        $minutes = (int) ($policy?->required_work_minutes ?? 0) + (int) ($policy?->lunch_break_minutes ?? $policy?->break_minutes ?? 0);

        return $punchIn->copy()->addMinutes($minutes);
    }

    public function policyPayload(?object $policy): ?array
    {
        if (! $policy) {
            return null;
        }

        return [
            'id' => $policy->id ?? null,
            'policy_name' => $policy->policy_name ?? $policy->name ?? null,
            'punch_allowed_from' => $this->timeString($policy->punch_allowed_from ?? null),
            'shift_start_time' => $this->timeString($policy->shift_start_time ?? null),
            'late_after_time' => $this->timeString($policy->late_after_time ?? null),
            'warning_after_time' => $this->timeString($policy->warning_after_time ?? null),
            'block_after_time' => $this->timeString($policy->block_after_time ?? null),
            'shift_end_time' => $this->timeString($policy->shift_end_time ?? null),
            'required_work_minutes' => (int) ($policy->required_work_minutes ?? 0),
            'half_day_min_minutes' => (int) ($policy->half_day_min_minutes ?? 0),
            'absent_below_minutes' => (int) ($policy->absent_below_minutes ?? 0),
            'lunch_break_minutes' => (int) ($policy->lunch_break_minutes ?? $policy->break_minutes ?? 0),
            'allowed_missed_punches' => (int) ($policy->allowed_missed_punches ?? 0),
            'combined_violation_limit' => (int) ($policy->combined_violation_limit ?? 0),
            'auto_block_enabled' => (bool) ($policy->auto_block_enabled ?? false),
            'auto_absent_enabled' => (bool) ($policy->auto_absent_enabled ?? false),
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
        return $dateTime instanceof Carbon
            ? $dateTime->copy()->setTimezone(self::TIMEZONE)
            : Carbon::parse($dateTime ?: 'now', self::TIMEZONE);
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
        if (! Schema::hasTable('attendance_policy_rules') || ! Schema::hasColumn('employees_new', 'attendance_policy_rule_id') || ! $employee->attendance_policy_rule_id) {
            return null;
        }

        return $this->markSource(DB::table('attendance_policy_rules')->where('id', $employee->attendance_policy_rule_id)->first(), 'attendance_policy_rules');
    }

    private function defaultAttendancePolicy(): ?object
    {
        if (! Schema::hasTable('attendance_policy_rules')) {
            return null;
        }

        $query = DB::table('attendance_policy_rules');
        if (Schema::hasColumn('attendance_policy_rules', 'is_active')) {
            $query->where('is_active', 1);
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

        return $employee ? $this->getPolicyForEmployee($employee, $attendance->attendance_date) : $this->defaultAttendancePolicy();
    }

    private function primaryMessage(?object $policy, array $dayContext, ?Attendance $attendance, array $window): ?string
    {
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

    private function isOnLeave(Employee $employee, Carbon $date): bool
    {
        if (Schema::hasTable('leave_requests') && Schema::hasTable('leave_request_dates')) {
            $query = DB::table('leave_requests')
                ->join('leave_request_dates', 'leave_request_dates.leave_request_id', '=', 'leave_requests.id')
                ->where('leave_requests.employee_id', $employee->id)
                ->where('leave_requests.status', 'approved')
                ->whereDate('leave_request_dates.leave_date', $date->toDateString());

            if (Schema::hasColumn('leave_request_dates', 'deduct_as_leave')) {
                $query->where('leave_request_dates.deduct_as_leave', 1);
            }

            if ($query->exists()) {
                return true;
            }
        }

        if (! Schema::hasTable('leave_applications')) {
            return false;
        }

        return DB::table('leave_applications')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->exists();
    }
}
