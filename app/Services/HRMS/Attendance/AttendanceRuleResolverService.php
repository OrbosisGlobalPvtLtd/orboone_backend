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
        $typeCode = optional($attendance?->attendanceType)->code;
        $statusCode = $attendance?->attendance_status;
        $isUnlocked = (bool) ($attendance?->is_admin_unlocked ?? false);
        
        $hasPunchIn = (bool) $attendance?->punch_in_time;
        $hasPunchOut = (bool) $attendance?->punch_out_time;

        // Define default states based on user requirements
        $isBlocked = false;
        $isPunchBlocked = false;
        $showBlockedCard = false;
        $canPunchIn = false;
        $canPunchOut = false;
        $attendanceState = 'absent';
        $statusCodeVal = 'not_punched';
        $nextAction = 'none';

        if ($attendance) {
            $isBlockedDb = ! $isUnlocked && (bool) (
                $attendance->is_blocked
                || $attendance->is_punch_blocked
                || $typeCode === 'punch_blocked'
                || $statusCode === 'punch_blocked'
            );
            
            if ($isUnlocked) {
                $isBlocked = false;
                $isPunchBlocked = false;
                $showBlockedCard = false;
                
                if (! $hasPunchIn) {
                    $statusCodeVal = 'unlocked';
                    $canPunchIn = true;
                    $nextAction = 'punch_in';
                } else {
                    $statusCodeVal = 'present';
                    $canPunchIn = false;
                    $canPunchOut = ! $hasPunchOut;
                    $nextAction = ! $hasPunchOut ? 'punch_out' : 'completed';
                    $attendanceState = ! $hasPunchOut ? 'punched_in' : 'punched_out';
                }
            } elseif ($isBlockedDb) {
                $isBlocked = true;
                $isPunchBlocked = true;
                $showBlockedCard = true;
                $statusCodeVal = 'punch_blocked';
                $canPunchIn = false;
                $canPunchOut = false;
                $nextAction = 'blocked';
            } else {
                // Not unlocked, not blocked in DB
                // Normal processing
                $statusCodeVal = $typeCode ?: ($statusCode ?: 'not_punched');
                if ($statusCodeVal === 'pending_hr') {
                    $statusCodeVal = 'not_punched';
                }
                
                if (! $hasPunchIn) {
                    // Check if block time is crossed
                    if ($window['is_blocked']) {
                        $isBlocked = true;
                        $isPunchBlocked = true;
                        $showBlockedCard = true;
                        $statusCodeVal = 'punch_blocked';
                        $canPunchIn = false;
                        $nextAction = 'blocked';
                    } else {
                        $canPunchIn = $dayContext['is_working_day'] && $window['is_allowed'];
                        $nextAction = $canPunchIn ? 'punch_in' : 'none';
                    }
                } else {
                    $statusCodeVal = 'present';
                    $canPunchIn = false;
                    $canPunchOut = ! $hasPunchOut;
                    $nextAction = ! $hasPunchOut ? 'punch_out' : 'completed';
                    $attendanceState = ! $hasPunchOut ? 'punched_in' : 'punched_out';
                }
            }
        } else {
            // Attendance is null (no attendance row exists)
            $isBlocked = false;
            $isPunchBlocked = false;
            $showBlockedCard = false;
            $canPunchOut = false;
            $attendanceState = 'absent';
            
            if ($window['is_blocked']) {
                $statusCodeVal = 'absent';
                $canPunchIn = false;
                $nextAction = 'blocked_by_policy';
            } else {
                $canPunchIn = $dayContext['is_working_day'] && $window['is_allowed'];
                $statusCodeVal = 'not_punched';
                $nextAction = $canPunchIn ? 'punch_in' : 'none';
            }
        }

        $primaryMessage = $this->primaryMessage($policy, $dayContext, $attendance, $window);
        $blockedMessage = $showBlockedCard ? 'Your punch-in is blocked. Please contact HR/Admin.' : null;
        
        if ($isUnlocked && ! $hasPunchIn) {
            $primaryMessage = 'Punch-in is available.';
            $blockedMessage = null;
        } elseif (! $attendance && $window['is_blocked']) {
            $primaryMessage = 'Punch-in is blocked by policy.';
            $blockedMessage = null;
        }

        return [
            'server_time' => $now->format('Y-m-d H:i:s'),
            'timezone' => self::TIMEZONE,
            'policy' => $this->policyPayload($policy),
            'day_context' => $dayContext,
            'attendance' => $attendance,
            'ui' => [
                'attendance_state' => $attendanceState,
                'can_punch_in' => $canPunchIn,
                'can_punch_out' => $canPunchOut,
                'is_blocked' => $isBlocked,
                'is_punch_blocked' => $isPunchBlocked,
                'status_code' => $statusCodeVal,
                'next_action' => $nextAction,
                'show_early_login_tag' => $window['is_before_shift_start'] && $canPunchIn,
                'show_late_mark' => $window['is_late'],
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

        return [
            'is_before_allowed_from' => $allowedFrom ? $now->lt($allowedFrom) : false,
            'is_before_shift_start' => $shiftStart ? $now->lt($shiftStart) : false,
            'is_late' => $lateAfter ? $now->gt($lateAfter) : false,
            'is_warning' => $warningAfter && $blockAfter ? $now->betweenIncluded($warningAfter, $blockAfter) : false,
            'is_blocked' => $blockAfter ? $now->gt($blockAfter) : false,
            'is_allowed' => (! $allowedFrom || $now->gte($allowedFrom)) && (! $blockAfter || $now->lte($blockAfter)),
            'allowed_from' => $allowedFrom,
            'block_after' => $blockAfter,
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
        } elseif ($required > 0 && $work['net_minutes'] < $required) {
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

    private function primaryMessage(?object $policy, array $dayContext, ?Attendance $attendance, array $window): string
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
        if ($window['is_blocked']) {
            return 'Your punch-in is blocked. Please contact HR/Admin.';
        }
        if ($window['is_warning']) {
            return 'Late punch-in. Warning: punch will be blocked after ' . $this->displayTime($policy?->block_after_time) . '.';
        }

        return 'Punch-in is available.';
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
