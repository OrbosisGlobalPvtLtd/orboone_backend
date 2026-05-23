<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceLocationM as AttendanceLocation;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Models\HRMS\Attendance\AttendanceWorkLogM;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Leave\HolidayM;
use App\Models\HRMS\Leave\LeaveApplicationM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AttendanceS
{
    public function __construct(private AttendanceRuleResolverService $ruleResolver)
    {
    }

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
        $employee = Employee::with(['profile', 'documents'])->where('user_id', $userId)->first();

        if (! $employee) {
            return ['status' => 'error', 'message' => 'Employee profile not found.'];
        }

        if ($enforceEmployeeRules && ! $this->employeeEligibleForAttendance($employee)) {
            return ['status' => 'error', 'message' => 'You are not eligible to mark attendance yet.'];
        }

        $workMode = strtolower($workMode);
        if ($enforceEmployeeRules && $workMode === 'wfo') {
            $locationValidation = $this->validateWfoOfficeLocation($meta);
            if (($locationValidation['status'] ?? null) === 'error') {
                return $locationValidation;
            }
        }

        $policy = $this->ruleResolver->getPolicyForEmployee($employee, $now);
        $dayContext = $this->ruleResolver->getDayContext($employee, $now);

        if (! $dayContext['is_working_day']) {
            return ['status' => 'error', 'message' => 'Attendance punch is not allowed for leave, holiday, or week off.'];
        }

        $existing = Attendance::with('attendanceType')
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing && $existing->punch_in_time) {
            return ['status' => 'error', 'message' => 'Punch in already recorded for today.'];
        }

        if ($existing && in_array(optional($existing->attendanceType)->code, ['absent', 'leave', 'week_off', 'holiday'], true)) {
            return ['status' => 'error', 'message' => 'Attendance is already marked for today.'];
        }

        if ($existing && ($existing->is_blocked || $existing->is_punch_blocked) && ! $existing->is_admin_unlocked) {
            return ['status' => 'error', 'message' => 'Your punch-in is blocked. Please contact HR/Admin.'];
        }

        $shift = $policy ?: $this->shiftFor($workMode);
        $window = $this->ruleResolver->calculatePunchWindowState($shift, $now);
        if ($enforceEmployeeRules && $window['is_before_allowed_from']) {
            return ['status' => 'error', 'message' => 'Punch-in is allowed from ' . $window['allowed_from']->format('h:i A') . '.'];
        }

        $time = $now->format('H:i:s');
        $blockAfter = $window['block_after'];
        $isBlocked = $window['is_blocked']
            && ! $attendanceTypeId
            && ! optional($existing)->is_late_exempted
            && ! optional($existing)->is_admin_unlocked;

        if ($isBlocked) {
            $this->notifyAttendance($employee, 'punch_blocked', 'Punch In Blocked', 'Your punch-in is blocked after ' . $blockAfter->format('h:i A') . '. Please contact HR/Admin.', $today);
            return ['status' => 'error', 'message' => 'Punch in is blocked after ' . $blockAfter->format('h:i A') . '. Please contact HR/Admin.'];
        }

        $targetPunchOut = $this->targetPunchOutTime($now, $shift);
        $isLate = ! optional($existing)->is_late_exempted && $this->isLatePunch($now, $shift);
        $lateMinutes = $isLate ? $this->lateMinutes($now, $shift) : 0;
        $presentType = $this->attendanceType('present');
        $existingTypeCode = optional($existing?->attendanceType)->code;
        $attendanceTypeForPunchIn = $attendanceTypeId
            ?: (in_array($existingTypeCode, ['punch_blocked', 'pending_hr'], true) ? $presentType?->id : ($existing->attendance_type_id ?? $presentType?->id));

        $attendanceStatusForPunchIn = ($existing && ! in_array($existing->attendance_status, ['pending_hr', 'punch_blocked', 'unlocked'], true)) ? $existing->attendance_status : 'present';
        if ($existing && $existing->is_admin_unlocked && ! $existing->punch_in_time) {
            $attendanceTypeForPunchIn = $presentType?->id ?: $attendanceTypeForPunchIn;
            $attendanceStatusForPunchIn = 'present';
        }

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $today],
            [
                'user_id' => $userId,
                'attendance_time_id' => ($shift?->source_table ?? null) === 'attendance_times' ? $shift?->id : ($existing->attendance_time_id ?? $this->defaultShift()?->id),
                'attendance_type_id' => $attendanceTypeForPunchIn,
                'punch_in_time' => $time,
                'target_punch_out_time' => $targetPunchOut,
                'work_mode' => $workMode,
                'punch_in_latitude' => $meta['latitude'] ?? null,
                'punch_in_longitude' => $meta['longitude'] ?? null,
                'punch_in_address' => $meta['address'] ?? null,
                'punch_in_ip' => $meta['ip'] ?? null,
                'punch_in_device' => $meta['device'] ?? null,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'is_blocked' => false,
                'is_punch_blocked' => false,
                'blocked_reason' => null,
                'block_reason' => null,
                'auto_block_reason' => null,
                'auto_blocked_at' => null,
                'is_profile_completed_at_punch' => $this->employeeEligibleForAttendance($employee),
                'is_locked' => false,
                'punch_in_note' => $note,
                'attendance_source' => 'mobile',
                'attendance_status' => $attendanceStatusForPunchIn,
            ]
        );

        if ($isLate) {
            $this->notifyAttendance($employee, 'late_mark', 'Late Mark Applied', 'Your punch-in was recorded with a late mark.', $today, [
                'attendance_id' => $attendance->id,
                'late_minutes' => $lateMinutes,
            ]);
        }

        return [
            'status' => true,
            'message' => $isLate ? 'Punch in recorded with late mark.' : 'Punch in recorded successfully.',
            'data' => $attendance->fresh(['attendanceType', 'attendanceTime', 'workLogs']),
        ];
    }

    public function processPunchOut(
        int $userId,
        string $taskSummary,
        ?string $note = null,
        array $meta = [],
        ?string $customTime = null,
        bool $enforceEmployeeRules = true,
        $taskSummaryJson = null
    ): array {
        $timezone = $this->attendanceTimezone();
        $now = $customTime ? Carbon::parse($customTime, $timezone) : Carbon::now($timezone);
        $today = $now->toDateString();
        $employee = Employee::where('user_id', $userId)->first();

        if (! $employee) {
            return ['status' => 'error', 'message' => 'Employee profile not found.'];
        }

        $attendance = Attendance::with('attendanceType')
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (! $attendance || ! $attendance->punch_in_time) {
            return ['status' => 'error', 'message' => 'No active punch in found.'];
        }

        if ($attendance->punch_out_time) {
            return ['status' => 'error', 'message' => 'Punch out already recorded.'];
        }

        if ($attendance->is_blocked || $attendance->is_punch_blocked || optional($attendance->attendanceType)->code === 'punch_blocked') {
            return ['status' => 'error', 'message' => 'Attendance is blocked. Please contact HR/Admin.'];
        }

        $existingWorkMode = strtolower($attendance->work_mode ?? 'wfo');
        if ($enforceEmployeeRules && $existingWorkMode === 'wfo') {
            $locationValidation = $this->validateOfficeRadiusForWfo('punch_out', isset($meta['latitude']) && $meta['latitude'] !== '' ? (float) $meta['latitude'] : null, isset($meta['longitude']) && $meta['longitude'] !== '' ? (float) $meta['longitude'] : null);
            if (! $locationValidation['allowed']) {
                return [
                    'status' => 'error',
                    'message' => $locationValidation['message'],
                    'data' => [
                        'distance_meters' => $locationValidation['distance_meters'],
                        'allowed_radius_meters' => $locationValidation['allowed_radius_meters'],
                        'office_location_name' => $locationValidation['office_location_name'],
                    ],
                ];
            }
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
                'work_summary_json' => $taskSummaryJson,
                'latitude' => $meta['latitude'] ?? null,
                'longitude' => $meta['longitude'] ?? null,
                'device_info' => $meta['device'] ?? null,
                'ip_address' => $meta['ip'] ?? null,
                'remarks' => $note,
            ]
        );

        $this->calculateAttendanceStats($attendance);

        return [
            'status' => true,
            'message' => 'Punch out recorded successfully.',
            'data' => $attendance->fresh(['attendanceType', 'attendanceTime', 'workLogs']),
        ];
    }

    public function blockMissedPunchIns(?string $date = null): int
    {
        return (int) ($this->autoBlockMissedPunchIns($date)['created'] ?? 0);
    }

    public function autoBlockMissedPunchIns(?string $date = null, bool $dryRun = false): array
    {
        $timezone = $this->attendanceTimezone();
        $now = Carbon::now($timezone);
        $date = $date ?: $now->toDateString();
        $counts = [
            'dry_run' => $dryRun ? 1 : 0,
            'total_checked' => 0,
            'created' => 0,
            'would_create' => 0,
            'skipped_existing' => 0,
            'skipped_inactive' => 0,
            'skipped_profile' => 0,
            'skipped_policy_disabled' => 0,
            'skipped_not_due' => 0,
            'skipped_leave' => 0,
            'skipped_holiday' => 0,
            'skipped_weekoff' => 0,
            'skipped_no_user' => 0,
        ];

        $blockedType = $this->attendanceType('punch_blocked');
        if (! $blockedType) {
            $counts['missing_attendance_type'] = 1;
            return $counts;
        }

        $employees = Employee::with(['profile', 'documents'])->active()->get();
        $status = 'punch_blocked';

        foreach ($employees as $employee) {
            $counts['total_checked']++;
            $policy = $this->ruleResolver->getPolicyForEmployee($employee, $date);
            if (! $policy || ! (bool) ($policy->auto_block_enabled ?? false)) {
                $counts['skipped_policy_disabled']++;
                continue;
            }

            if (! $policy->block_after_time) {
                $counts['skipped_not_due']++;
                continue;
            }

            $blockAt = Carbon::parse($date . ' ' . $this->ruleResolver->timeString($policy->block_after_time), $timezone)->addSecond();
            if ($now->lt($blockAt)) {
                $counts['skipped_not_due']++;
                continue;
            }

            if (! $employee->user_id) {
                $counts['skipped_no_user']++;
                continue;
            }

            if (! $this->employeeIsActive($employee)) {
                $counts['skipped_inactive']++;
                continue;
            }

            if (! $this->employeeProfileApproved($employee)) {
                $counts['skipped_profile']++;
                continue;
            }

            $dayContext = $this->ruleResolver->getDayContext($employee, $date);
            if ($dayContext['is_on_leave']) {
                $counts['skipped_leave']++;
                continue;
            }

            if ($dayContext['is_holiday']) {
                $counts['skipped_holiday']++;
                continue;
            }

            if ($dayContext['is_weekoff']) {
                $counts['skipped_weekoff']++;
                continue;
            }

            $existing = Attendance::where('employee_id', $employee->id)->whereDate('attendance_date', $date)->first();
            if ($existing) {
                $counts['skipped_existing']++;
                continue;
            }

            $reason = 'Auto blocked after ' . Carbon::parse($policy->block_after_time, $timezone)->format('h:i A') . ' because employee did not punch in.';
            $payload = $this->attendancePayload([
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
                'attendance_time_id' => ($policy?->source_table ?? null) === 'attendance_times' ? $policy?->id : null,
                'attendance_type_id' => $blockedType?->id,
                'attendance_date' => $date,
                'punch_in_time' => null,
                'punch_out_time' => null,
                'is_punch_blocked' => true,
                'is_blocked' => true,
                'blocked_reason' => $reason,
                'block_reason' => $reason,
                'auto_blocked_at' => $now,
                'auto_block_reason' => $reason,
                'hr_approval_note' => null,
                'is_locked' => true,
                'punch_in_note' => $reason,
                'attendance_source' => 'system_auto',
                'attendance_status' => $status,
                'pending_hr_reason' => null,
            ]);

            if ($dryRun) {
                $counts['would_create']++;
                continue;
            }

            $attendance = Attendance::firstOrCreate(
                ['employee_id' => $employee->id, 'attendance_date' => $date],
                $payload
            );

            $this->notifyAttendance($employee, 'punch_blocked', 'Punch In Blocked', 'Your punch-in was auto-blocked because you did not punch in before the allowed time.', $date, [
                'attendance_id' => $attendance->id,
                'reason' => $reason,
            ]);

            $counts['created']++;
        }

        Log::info('HRMS auto-block missed punch-ins completed.', $counts + ['date' => $date]);

        return $counts;
    }

    public function unlockAttendance(int $attendanceId, int $adminUserId, array|string|null $payload = null): array
    {
        $attendance = Attendance::with(['employee', 'attendanceTime'])->find($attendanceId);
        if (! $attendance) {
            return ['success' => false, 'message' => 'Attendance record not found.'];
        }

        $data = is_array($payload) ? $payload : ['unlock_remarks' => $payload];
        $unlockType = $data['unlock_type'] ?? 'unlock_only';
        $approvedPunchIn = $data['approved_punch_in_time'] ?? null;
        $presentType = $this->attendanceType('present');
        $now = Carbon::now($this->attendanceTimezone());
        $approvalNote = trim((string) ($data['hr_approval_note'] ?? ''))
            ?: (trim((string) ($data['unlock_remarks'] ?? '')) ?: 'Unlocked by HR/Admin.');

        $updates = [
            'attendance_type_id' => $presentType?->id ?: $attendance->attendance_type_id,
            'attendance_status' => 'unlocked',
            'is_admin_unlocked' => true,
            'unlock_type' => $unlockType,
            'unlock_reason_category' => $data['unlock_reason_category'] ?? null,
            'unlock_remarks' => $data['unlock_remarks'] ?? null,
            'hr_approval_note' => $approvalNote,
            'unlocked_by' => $adminUserId,
            'unlocked_at' => $now,
            'hr_approved_by' => $adminUserId,
            'hr_approved_at' => $now,
            'is_locked' => false,
            'is_blocked' => false,
            'is_punch_blocked' => false,
            'block_reason' => null,
            'blocked_reason' => null,
            'auto_block_reason' => null,
            'auto_blocked_at' => null,
            'punch_in_note' => null,
            'pending_hr_reason' => null,
        ];

        if ($unlockType === 'late_exemption') {
            $updates['is_late_exempted'] = true;
            $updates['is_late'] = false;
            $updates['late_minutes'] = 0;
        }

        if ($unlockType === 'manual_punch_in') {
            if (! $approvedPunchIn) {
                return ['success' => false, 'message' => 'Approved punch-in time is required for manual punch-in.'];
            }

            $date = Carbon::parse($attendance->attendance_date, $this->attendanceTimezone())->toDateString();
            $punchIn = Carbon::parse($date . ' ' . $approvedPunchIn, $this->attendanceTimezone());
            $shift = $attendance->employee ? $this->ruleResolver->getPolicyForEmployee($attendance->employee, $date) : ($attendance->attendanceTime ?: $this->defaultShift());

            $updates['approved_punch_in_time'] = $punchIn->format('H:i:s');
            $updates['punch_in_time'] = $punchIn->format('H:i:s');
            $updates['target_punch_out_time'] = $this->targetPunchOutTime($punchIn, $shift);
            $updates['attendance_type_id'] = $presentType?->id ?: $attendance->attendance_type_id;
            $updates['attendance_status'] = 'present';
            $updates['attendance_source'] = 'admin_unlock';
            $updates['is_late'] = $updates['is_late_exempted'] ?? false ? false : $this->isLatePunch($punchIn, $shift);
            $updates['late_minutes'] = $updates['is_late'] ? $this->lateMinutes($punchIn, $shift) : 0;
        }

        $attendance->fill($this->attendancePayload($updates))->save();

        if ($attendance->punch_in_time && $attendance->punch_out_time) {
            $this->calculateAttendanceStats($attendance);
        }

        return ['success' => true, 'message' => 'Attendance unlocked successfully.', 'data' => $attendance->fresh()];
    }

    public function processBlockedAbsent(?string $date = null): int
    {
        return (int) ($this->autoCloseBlockedAttendance($date)['marked_absent'] ?? 0);
    }

    public function autoCloseBlockedAttendance(?string $date = null, bool $dryRun = false): array
    {
        $timezone = $this->attendanceTimezone();
        $now = Carbon::now($timezone);
        $date = $date ?: Carbon::now($timezone)->toDateString();
        $absentType = $this->attendanceType('absent');
        $skipTypeIds = AttendanceType::whereIn('code', ['leave', 'holiday', 'week_off'])->pluck('id')->all();
        $counts = [
            'dry_run' => $dryRun ? 1 : 0,
            'total_checked' => 0,
            'marked_absent' => 0,
            'would_mark_absent' => 0,
            'skipped_payroll_processed' => 0,
            'skipped_approved_or_unlocked' => 0,
            'skipped_protected_status' => 0,
            'skipped_policy_disabled' => 0,
            'skipped_not_due' => 0,
            'skipped_has_punch_in' => 0,
        ];

        if (! $absentType) {
            $counts['missing_attendance_type'] = 1;
            return $counts;
        }

        $records = Attendance::with('attendanceType')
            ->whereDate('attendance_date', $date)
            ->where(function ($query) {
                $query->where('is_blocked', true)
                    ->orWhere('is_punch_blocked', true)
                    ->orWhere('attendance_status', 'punch_blocked');
            })
            ->get();

        foreach ($records as $attendance) {
            $counts['total_checked']++;
            $employee = $attendance->employee ?: Employee::find($attendance->employee_id);
            $policy = $employee ? $this->ruleResolver->getPolicyForEmployee($employee, $date) : null;
            if (! $policy || ! (bool) ($policy->auto_absent_enabled ?? false)) {
                $counts['skipped_policy_disabled']++;
                continue;
            }

            if (! $this->dayEndProcessingDue($date, $policy, $now)) {
                $counts['skipped_not_due']++;
                continue;
            }

            if ($attendance->punch_in_time) {
                $counts['skipped_has_punch_in']++;
                continue;
            }

            if ($attendance->payroll_processed) {
                $counts['skipped_payroll_processed']++;
                continue;
            }

            if ($attendance->is_admin_unlocked || $attendance->hr_approved_by || $attendance->hr_approved_at) {
                $counts['skipped_approved_or_unlocked']++;
                continue;
            }

            $typeCode = optional($attendance->attendanceType)->code;
            if (in_array($attendance->attendance_type_id, $skipTypeIds, true) || in_array($typeCode, ['leave', 'holiday', 'week_off'], true) || in_array($attendance->attendance_status, ['leave', 'holiday', 'week_off'], true)) {
                $counts['skipped_protected_status']++;
                continue;
            }

            $reason = 'Marked absent because employee did not punch in today before allowed time.';

            $updates = $this->attendancePayload([
                'attendance_type_id' => $absentType->id,
                'attendance_status' => 'absent',
                'attendance_source' => 'system_auto',
                'is_blocked' => false,
                'is_punch_blocked' => false,
                'is_locked' => true,
                'is_lwp' => true,
                'lwp_reason' => $reason,
                'block_reason' => $reason,
                'auto_block_reason' => $attendance->auto_block_reason ?: $reason,
                'blocked_reason' => $reason,
                'punch_in_note' => $reason,
                'punch_out_note' => $reason,
                'pending_hr_reason' => null,
            ]);

            if ($dryRun) {
                $counts['would_mark_absent']++;
                continue;
            }

            $attendance->fill($updates)->save();

            $counts['marked_absent']++;
        }

        Log::info('HRMS auto-close blocked attendance completed.', $counts + ['date' => $date]);

        return $counts;
    }

    public function calculateAttendanceStats(Attendance $attendance): Attendance
    {
        return $this->calculateWorkingHours($attendance);
    }

    public function calculateWorkingHours(Attendance $attendance): Attendance
    {
        if (! $attendance->punch_in_time || ! $attendance->punch_out_time) {
            return $attendance;
        }

        $employee = $attendance->employee ?: Employee::find($attendance->employee_id);
        $shift = $employee ? $this->ruleResolver->getPolicyForEmployee($employee, $attendance->attendance_date) : ($attendance->attendanceTime ?: $this->defaultShift());
        $timezone = $this->attendanceTimezone();
        $date = Carbon::parse($attendance->attendance_date, $timezone)->toDateString();
        $in = Carbon::parse($date . ' ' . $this->ruleResolver->timeString($attendance->punch_in_time), $timezone);
        $out = Carbon::parse($date . ' ' . $this->ruleResolver->timeString($attendance->punch_out_time), $timezone);

        if ($out->lt($in)) {
            $out->addDay();
        }

        $grossMinutes = $in->diffInMinutes($out);
        $breakMinutes = (int) ($shift?->lunch_break_minutes ?? $shift?->break_minutes ?? 0);
        $netMinutes = max(0, $grossMinutes - $breakMinutes);
        $requiredMinutes = (int) ($shift?->required_work_minutes ?? 0);
        $halfDayMinutes = (int) ($shift?->half_day_min_minutes ?? 0);
        $absentBelowMinutes = (int) ($shift?->absent_below_minutes ?? $halfDayMinutes);
        $combinedViolationLimit = (int) ($shift?->combined_violation_limit ?? 0);

        $target = $attendance->target_punch_out_time
            ? Carbon::parse($date . ' ' . $this->ruleResolver->timeString($attendance->target_punch_out_time), $timezone)
            : Carbon::parse($date . ' ' . $this->targetPunchOutTime($in, $shift), $timezone);

        if ($target->lt($in)) {
            $target->addDay();
        }

        $isEarly = $out->lt($target);
        $violationCount = (int) $attendance->is_late + (int) $isEarly;
        $typeCode = 'present';
        $isHalfDay = false;
        $isLwp = false;

        if ($absentBelowMinutes > 0 && $netMinutes < $absentBelowMinutes) {
            $typeCode = 'lwp';
            $isLwp = true;
        } elseif (($requiredMinutes > 0 && $netMinutes < $requiredMinutes) || ($combinedViolationLimit > 0 && $violationCount >= $combinedViolationLimit)) {
            $typeCode = 'half_day';
            $isHalfDay = true;
        }

        $attendance->fill([
            'target_punch_out_time' => $attendance->target_punch_out_time ?: $target->format('H:i:s'),
            'gross_work_minutes' => $grossMinutes,
            'break_minutes' => $breakMinutes,
            'lunch_break_minutes' => $breakMinutes,
            'total_work_minutes' => $netMinutes,
            'is_early_out' => $isEarly,
            'early_out_minutes' => $isEarly ? $out->diffInMinutes($target) : 0,
            'violation_count' => $violationCount,
            'is_half_day' => $isHalfDay,
            'is_lwp' => $isLwp,
        ]);

        if (! $attendance->is_blocked && ! $attendance->is_punch_blocked) {
            $attendance->attendance_type_id = $this->attendanceType($typeCode)?->id ?: $attendance->attendance_type_id;
        }

        $attendance->save();

        if ($isHalfDay) {
            $this->notifyAttendance($employee, 'half_day', 'Half Day Marked', 'Your attendance has been marked as half day.', $date, [
                'attendance_id' => $attendance->id,
                'total_work_minutes' => $netMinutes,
            ]);
        }

        if ($isEarly) {
            $this->notifyAttendance($employee, 'early_logout', 'Early Logout Recorded', 'Your punch-out was earlier than the target time.', $date, [
                'attendance_id' => $attendance->id,
                'early_out_minutes' => $attendance->early_out_minutes,
            ]);
        }

        return $attendance;
    }

    private function notifyAttendance(Employee $employee, string $type, string $title, string $message, string $date, array $extra = []): void
    {
        if (! $employee->user_id) {
            return;
        }

        $notificationS = app(\App\Services\HRMS\Notification\NotificationS::class);
        if ($notificationS->alreadySent($type, (int) $employee->id, $date, (int) $employee->user_id)) {
            return;
        }

        $notificationS->notifyEmployee(
            $title,
            $message,
            $type,
            'attendance',
            ['attendance_date' => $date],
            array_merge([
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
                'employee_name' => $employee->display_name,
                'target_date' => $date,
            ], $extra),
            $employee->user_id
        );
    }

    public function processMissedPunches(?string $date = null, bool $dryRun = false): array
    {
        $timezone = $this->attendanceTimezone();
        $now = Carbon::now($timezone);
        $date = $date ?: $now->toDateString();
        $absentType = $this->attendanceType('absent');
        $lwpType = $this->attendanceType('lwp');
        $skipTypeIds = AttendanceType::whereIn('code', ['leave', 'holiday', 'week_off', 'punch_blocked'])->pluck('id')->all();
        $counts = [
            'dry_run' => $dryRun ? 1 : 0,
            'total_checked' => 0,
            'marked_missed_punch' => 0,
            'would_mark_missed_punch' => 0,
            'marked_absent' => 0,
            'marked_lwp' => 0,
            'violations_created' => 0,
            'skipped_not_due' => 0,
            'skipped_payroll_processed' => 0,
            'skipped_protected_status' => 0,
            'skipped_blocked' => 0,
            'skipped_missing_policy' => 0,
        ];

        if (! $absentType) {
            $counts['missing_attendance_type'] = 1;
            return $counts;
        }

        $records = Attendance::with(['attendanceType', 'employee'])
            ->whereDate('attendance_date', $date)
            ->whereNotNull('punch_in_time')
            ->whereNull('punch_out_time')
            ->where(function ($query) {
                $query->where('is_blocked', false)->orWhereNull('is_blocked');
            })
            ->where(function ($query) {
                $query->where('is_punch_blocked', false)->orWhereNull('is_punch_blocked');
            })
            ->whereNotIn('attendance_type_id', $skipTypeIds)
            ->get();

        foreach ($records as $attendance) {
            $counts['total_checked']++;
            $employee = $attendance->employee ?: Employee::find($attendance->employee_id);
            $policy = $employee ? $this->ruleResolver->getPolicyForEmployee($employee, $date) : null;

            if (! $policy) {
                $counts['skipped_missing_policy']++;
                continue;
            }

            if (! $this->missedPunchProcessingDue($date, $policy, $now)) {
                $counts['skipped_not_due']++;
                continue;
            }

            if ($attendance->payroll_processed) {
                $counts['skipped_payroll_processed']++;
                continue;
            }

            $typeCode = optional($attendance->attendanceType)->code;
            if (in_array($typeCode, ['leave', 'holiday', 'week_off'], true) || in_array($attendance->attendance_status, ['leave', 'holiday', 'week_off'], true)) {
                $counts['skipped_protected_status']++;
                continue;
            }

            if ($attendance->is_blocked || $attendance->is_punch_blocked || in_array($typeCode, ['punch_blocked'], true)) {
                $counts['skipped_blocked']++;
                continue;
            }

            $missedCount = Attendance::where('employee_id', $attendance->employee_id)
                ->where(function ($query) {
                    $query->where('missed_punch', true)->orWhere('is_missed_punch', true);
                })
                ->whereYear('attendance_date', Carbon::parse($date, $timezone)->year)
                ->whereMonth('attendance_date', Carbon::parse($date, $timezone)->month)
                ->whereDate('attendance_date', '<>', $date)
                ->count() + 1;

            $allowedMissedPunches = (int) ($policy->allowed_missed_punches ?? 0);
            $limitExceeded = $allowedMissedPunches >= 0 && $missedCount > $allowedMissedPunches;
            $type = $limitExceeded && $lwpType ? $lwpType : $absentType;
            $reason = $limitExceeded
                ? 'Marked LWP because missed punch limit exceeded.'
                : 'Marked absent because employee did not punch out.';

            $updates = $this->attendancePayload([
                'missed_punch' => true,
                'is_missed_punch' => true,
                'missed_punch_reason' => 'Marked missed punch because employee did not punch out.',
                'attendance_type_id' => $type->id,
                'attendance_status' => $limitExceeded && $lwpType ? 'lwp' : 'absent',
                'attendance_source' => 'system_auto',
                'is_lwp' => $limitExceeded,
                'lwp_reason' => $reason,
                'punch_out_note' => $reason,
                'pending_hr_reason' => null,
                'is_locked' => true,
            ]);

            if ($dryRun) {
                $counts['would_mark_missed_punch']++;
                continue;
            }

            $attendance->fill($updates)->save();
            if ($this->recordAttendanceViolation($attendance, 'missed_punch', $date, [
                'source' => 'system_auto',
                'policy_action' => $limitExceeded ? 'lwp' : 'absent',
                'converted_to_lwp' => $limitExceeded,
                'remarks' => $reason,
            ])) {
                $counts['violations_created']++;
            }

            $counts['marked_missed_punch']++;
            if ($limitExceeded && $lwpType) {
                $counts['marked_lwp']++;
            } else {
                $counts['marked_absent']++;
            }
        }

        Log::info('HRMS process missed punches completed.', $counts + ['date' => $date]);

        return $counts;
    }

    public function autoCloseMissedPunchouts(?Carbon $beforeDate = null): int
    {
        $beforeDate = $beforeDate ?: Carbon::today($this->attendanceTimezone());
        return (int) ($this->processMissedPunches($beforeDate->copy()->subDay()->toDateString())['marked_missed_punch'] ?? 0);
    }

    public function processAdminPunchIn(int $userId, string $workMode = 'wfo', ?string $note = 'Admin Override', ?string $customTime = null, ?int $attendanceTypeId = null): array
    {
        return $this->processPunchIn($userId, $workMode, $note, ['ip' => request()?->ip(), 'device' => request()?->userAgent()], $customTime, $attendanceTypeId, false);
    }

    public function processAdminPunchOut(int $userId, string $taskSummary, ?string $note = 'Admin Override', ?string $customTime = null): array
    {
        return $this->processPunchOut($userId, $taskSummary, $note, ['ip' => request()?->ip(), 'device' => request()?->userAgent()], $customTime, false);
    }

    public function todayStatus(int $userId): array
    {
        $timezone = $this->attendanceTimezone();
        $now = Carbon::now($timezone);
        $employee = Employee::where('user_id', $userId)->first();
        
        $attendance = null;
        if ($employee) {
            $attendance = Attendance::with(['attendanceType', 'attendanceTime'])
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $now->toDateString())
                ->latest('id')
                ->first();
        } else {
            $attendance = Attendance::with(['attendanceType', 'attendanceTime'])
                ->where('user_id', $userId)
                ->whereDate('attendance_date', $now->toDateString())
                ->latest('id')
                ->first();
        }

        $shift = $employee ? $this->ruleResolver->getPolicyForEmployee($employee, $now) : ($attendance?->attendanceTime ?: $this->defaultShift());
        $completed = (int) ($attendance?->total_work_minutes ?? 0);
        if ($attendance?->punch_in_time && ! $attendance->punch_out_time) {
            $in = Carbon::parse($now->toDateString() . ' ' . $this->ruleResolver->timeString($attendance->punch_in_time), $timezone);
            $completed = max(0, $in->diffInMinutes($now) - (int) ($shift?->lunch_break_minutes ?? $shift?->break_minutes ?? 0));
        }
        $required = (int) ($shift?->required_work_minutes ?? 0);
        $target = $attendance?->target_punch_out_time ? Carbon::parse($now->toDateString() . ' ' . $this->ruleResolver->timeString($attendance->target_punch_out_time), $timezone) : null;
        $remainingSeconds = $target && ! $attendance?->punch_out_time ? max(0, $now->diffInSeconds($target, false)) : 0;

        $typeCode = optional($attendance?->attendanceType)->code;
        $statusCode = $attendance?->attendance_status;
        $isUnlocked = (bool) ($attendance?->is_admin_unlocked ?? false);
        $isBlocked = (bool) (
            $attendance?->is_blocked
            || $attendance?->is_punch_blocked
            || $typeCode === 'punch_blocked'
            || $statusCode === 'punch_blocked'
        );
        if ($isUnlocked) {
            $isBlocked = false;
        }

        $statusCodeVal = $typeCode ?? 'not_punched';
        $statusName = optional($attendance?->attendanceType)->name ?? 'Not Punched';
        if ($isBlocked) {
            $statusCodeVal = 'punch_blocked';
            $statusName = 'Punch Blocked';
        } elseif ($isUnlocked && ! $attendance?->punch_in_time) {
            $statusCodeVal = 'unlocked';
            $statusName = 'Unlocked';
        } elseif ($attendance?->punch_in_time) {
            $statusCodeVal = 'present';
            $statusName = 'Present';
        } elseif ($statusCodeVal === 'pending_hr') {
            $statusCodeVal = 'not_punched';
            $statusName = 'Not Punched';
        }

        $canPunchIn = ! $isBlocked && ! $attendance?->punch_in_time;
        $canPunchOut = ! $isBlocked && (bool) ($attendance?->punch_in_time && ! $attendance?->punch_out_time);

        return [
            'attendance_date' => $now->toDateString(),
            'server_time' => $now->format('Y-m-d H:i:s'),
            'status_code' => $statusCodeVal,
            'status_name' => $statusName,
            'punch_in_time' => $attendance?->punch_in_time,
            'punch_out_time' => $attendance?->punch_out_time,
            'target_punch_out_time' => $attendance?->target_punch_out_time,
            'completed_work_minutes' => $completed,
            'remaining_work_minutes' => max(0, $required - $completed),
            'remaining_work_seconds' => $remainingSeconds,
            'required_work_minutes' => $required,
            'required_office_minutes' => (int) (($shift?->required_work_minutes ?? 0) + ($shift?->lunch_break_minutes ?? $shift?->break_minutes ?? 0)),
            'break_minutes' => (int) ($shift?->lunch_break_minutes ?? $shift?->break_minutes ?? 0),
            'is_late' => (bool) ($attendance?->is_late ?? false),
            'is_early_out' => (bool) ($attendance?->is_early_out ?? false),
            'is_punch_blocked' => $isBlocked,
            'is_blocked' => $isBlocked,
            'can_punch_in' => $canPunchIn,
            'can_punch_out' => $canPunchOut,
            'late_warning' => $this->lateWarning($now, $shift),
            'next_action' => $isBlocked ? 'blocked' : (! $attendance?->punch_in_time ? 'punch_in' : (! $attendance?->punch_out_time ? 'punch_out' : 'completed')),
            'dynamic_timer_enabled' => (bool) ($attendance?->punch_in_time && ! $attendance?->punch_out_time),
            'office_location' => $this->officeLocationPayload(),
        ];
    }

    public function defaultOfficeLocation(): ?AttendanceLocation
    {
        if (! Schema::hasTable('attendance_locations')) {
            return null;
        }

        return AttendanceLocation::where('is_active', 1)
            ->where('is_default', 1)
            ->first();
    }

    public function officeLocationPayload(): array
    {
        $location = $this->defaultOfficeLocation();

        if (! $location) {
            return ['enabled' => false];
        }

        return [
            'enabled' => true,
            'name' => $location->name,
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'radius_meters' => (int) $location->radius_meters,
        ];
    }

    public function calculateDistanceMeters($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadiusMeters = 6371000;
        $latFrom = deg2rad((float) $lat1);
        $lngFrom = deg2rad((float) $lng1);
        $latTo = deg2rad((float) $lat2);
        $lngTo = deg2rad((float) $lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2)
            + cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return $angle * $earthRadiusMeters;
    }

    public function defaultShift(): ?AttendanceTime
    {
        return AttendanceTime::where('is_default', true)->where('is_active', true)->first()
            ?: AttendanceTime::where('is_active', true)->orderBy('id')->first();
    }

    public function shiftFor(string $workMode): ?AttendanceTime
    {
        if (strtolower($workMode) === 'wfh') {
            return AttendanceTime::where('code', 'wfh_shift')->where('is_active', true)->first() ?: $this->defaultShift();
        }

        return $this->defaultShift();
    }

    public function attendanceType(string $code): ?AttendanceType
    {
        return AttendanceType::where('code', $code)->first();
    }

    public function attendanceTimezone(): string
    {
        return 'Asia/Kolkata';
    }

    public function isOffDay(Carbon $date): bool
    {
        if (Schema::hasTable('weekoff_rules')) {
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
                if (Schema::hasColumn('weekoff_rules', 'day_name')) {
                    $q->orWhereRaw('LOWER(day_name) = ?', [$dayName]);
                }
                if (Schema::hasColumn('weekoff_rules', 'weekday')) {
                    $q->orWhere('weekday', $isoWeekday);
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

            $rule = $query->first();
            if ($rule) {
                if (property_exists($rule, 'is_working') && (int) $rule->is_working === 1) {
                    return false;
                }

                if (property_exists($rule, 'is_off')) {
                    return (int) $rule->is_off === 1;
                }

                return true;
            }
        }

        if ($date->isSunday()) {
            return true;
        }
        if (! $date->isSaturday()) {
            return false;
        }

        $saturdayNumber = (int) ceil($date->day / 7);
        if (in_array($saturdayNumber, [2, 4], true)) {
            return true;
        }

        return $saturdayNumber === 5 && (bool) config('attendance.fifth_saturday_off', false);
    }

    private function dayEndProcessingDue(string $date, ?object $policy, Carbon $now): bool
    {
        $attendanceDate = Carbon::parse($date, $this->attendanceTimezone())->startOfDay();
        if ($attendanceDate->lt($now->copy()->startOfDay())) {
            return true;
        }

        $threshold = $this->policyDayEndTime($policy) ?: '23:59:00';
        $dueAt = Carbon::parse($date . ' ' . $threshold, $this->attendanceTimezone());

        if ($policy?->shift_end_time) {
            $shiftEnd = Carbon::parse($date . ' ' . $this->ruleResolver->timeString($policy->shift_end_time), $this->attendanceTimezone());
            if ($shiftEnd->gt($dueAt)) {
                $dueAt = $shiftEnd;
            }
        }

        return $now->gte($dueAt);
    }

    private function missedPunchProcessingDue(string $date, ?object $policy, Carbon $now): bool
    {
        $attendanceDate = Carbon::parse($date, $this->attendanceTimezone())->startOfDay();
        if ($attendanceDate->lt($now->copy()->startOfDay())) {
            return true;
        }

        $threshold = $policy?->shift_end_time
            ? $this->ruleResolver->timeString($policy->shift_end_time)
            : ($this->policyDayEndTime($policy) ?: '23:59:00');

        return $now->gte(Carbon::parse($date . ' ' . $threshold, $this->attendanceTimezone()));
    }

    private function policyDayEndTime(?object $policy): ?string
    {
        foreach (['auto_absent_after_time', 'day_end_time', 'end_of_day_time', 'attendance_day_end_time'] as $field) {
            if (! empty($policy->{$field})) {
                return $this->ruleResolver->timeString($policy->{$field});
            }
        }

        return null;
    }

    private function recordAttendanceViolation(Attendance $attendance, string $type, string $date, array $payload): bool
    {
        if (! Schema::hasTable('attendance_violations')) {
            return false;
        }

        $exists = AttendanceViolationM::where('attendance_id', $attendance->id)
            ->where('type', $type)
            ->whereDate('violation_date', $date)
            ->exists();

        if ($exists) {
            return false;
        }

        AttendanceViolationM::create([
            'employee_id' => $attendance->employee_id,
            'attendance_id' => $attendance->id,
            'violation_date' => $date,
            'type' => $type,
            'minutes' => $payload['minutes'] ?? 0,
            'source' => $payload['source'] ?? 'system_auto',
            'policy_action' => $payload['policy_action'] ?? null,
            'converted_to_half_day' => (bool) ($payload['converted_to_half_day'] ?? false),
            'converted_to_lwp' => (bool) ($payload['converted_to_lwp'] ?? false),
            'remarks' => $payload['remarks'] ?? null,
        ]);

        return true;
    }

    private function validateWfoOfficeLocation(array $meta): array
    {
        $locationValidation = $this->validateOfficeRadiusForWfo('punch_in', isset($meta['latitude']) && $meta['latitude'] !== '' ? (float) $meta['latitude'] : null, isset($meta['longitude']) && $meta['longitude'] !== '' ? (float) $meta['longitude'] : null);
        if (! $locationValidation['allowed']) {
            return [
                'status' => 'error',
                'message' => $locationValidation['message'],
                'data' => [
                    'distance_meters' => $locationValidation['distance_meters'],
                    'allowed_radius_meters' => $locationValidation['allowed_radius_meters'],
                    'office_location_name' => $locationValidation['office_location_name'],
                ],
            ];
        }

        return [
            'status' => true,
            'data' => [
                'distance_meters' => $locationValidation['distance_meters'],
                'allowed_radius_meters' => $locationValidation['allowed_radius_meters'],
                'office_location_name' => $locationValidation['office_location_name'],
            ],
        ];
    }

    public function validateOfficeRadiusForWfo(string $action, ?float $lat, ?float $lng): array
    {
        $officeLocation = $this->defaultOfficeLocation();
        if (! $officeLocation) {
            return [
                'allowed' => false,
                'message' => 'Office location is not configured. Please contact HR/Admin.',
                'distance_meters' => 0.0,
                'allowed_radius_meters' => 0,
                'office_location_name' => '',
            ];
        }

        if ($lat === null || $lng === null) {
            $actionWord = ($action === 'punch_out') ? 'punch-out' : 'punch-in';
            return [
                'allowed' => false,
                'message' => "Location is required for WFO {$actionWord}.",
                'distance_meters' => 0.0,
                'allowed_radius_meters' => (int) $officeLocation->radius_meters,
                'office_location_name' => $officeLocation->name,
            ];
        }

        $distanceMeters = $this->calculateDistanceMeters(
            $lat,
            $lng,
            $officeLocation->latitude,
            $officeLocation->longitude
        );
        $allowedRadiusMeters = (int) $officeLocation->radius_meters;

        $allowed = $distanceMeters <= $allowedRadiusMeters;
        $actionMsg = ($action === 'punch_out') ? 'punch-out' : 'punch-in';

        return [
            'allowed' => $allowed,
            'message' => $allowed ? 'Success' : "You are outside the allowed office {$actionMsg} radius.",
            'distance_meters' => round($distanceMeters, 2),
            'allowed_radius_meters' => $allowedRadiusMeters,
            'office_location_name' => $officeLocation->name,
        ];
    }

    private function targetPunchOutTime(Carbon $punchIn, ?object $shift): string
    {
        return $this->ruleResolver->targetPunchOut($punchIn, $shift)->format('H:i:s');
    }

    private function employeeEligibleForAttendance(Employee $employee): bool
    {
        if (! $this->employeeIsActive($employee)) {
            return false;
        }

        if (! $this->employeeProfileApproved($employee)) {
            return false;
        }

        if ($this->documentGatingEnabled()) {
            $requiredDocs = $employee->documents->where('is_required', true);
            if ($requiredDocs->isNotEmpty() && $requiredDocs->contains(fn ($doc) => $doc->verification_status !== 'verified')) {
                return false;
            }
        }

        return true;
    }

    private function documentGatingEnabled(): bool
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'key') || ! Schema::hasColumn('settings', 'value')) {
            return false;
        }

        $row = DB::table('settings')->whereIn('key', ['document_gating_enabled', 'attendance_document_gating_enabled'])->first();
        return $row ? filter_var($row->value, FILTER_VALIDATE_BOOLEAN) : false;
    }

    private function employeeOnLeave(Employee $employee, string $date): bool
    {
        if (Schema::hasTable('leave_requests') && Schema::hasTable('leave_request_dates')) {
            $query = DB::table('leave_requests')
                ->join('leave_request_dates', 'leave_request_dates.leave_request_id', '=', 'leave_requests.id')
                ->where('leave_requests.employee_id', $employee->id)
                ->where('leave_requests.status', 'approved')
                ->whereDate('leave_request_dates.leave_date', $date);

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

        return LeaveApplicationM::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    private function isHolidayDate(string $date): bool
    {
        if (! Schema::hasTable('holidays')) {
            return false;
        }

        $dateColumn = Schema::hasColumn('holidays', 'holiday_date') ? 'holiday_date' : (Schema::hasColumn('holidays', 'date') ? 'date' : null);
        if (! $dateColumn) {
            return false;
        }

        $query = DB::table('holidays')->whereDate($dateColumn, $date);

        if (Schema::hasColumn('holidays', 'is_active')) {
            $query->where('is_active', 1);
        }

        $holiday = $query->first();
        if (! $holiday) {
            return false;
        }

        if (property_exists($holiday, 'is_working_day_override') && (int) $holiday->is_working_day_override === 1) {
            return false;
        }

        return true;
    }

    private function employeeIsActive(Employee $employee): bool
    {
        return (bool) $employee->is_active && (($employee->employment_status ?? 'active') === 'active');
    }

    private function employeeProfileApproved(Employee $employee): bool
    {
        if (! Schema::hasTable('employee_profiles')) {
            Log::warning('Attendance auto processing skipped employee because employee_profiles table is missing.', [
                'employee_id' => $employee->id,
            ]);
            return false;
        }

        $profile = $employee->profile;

        if (! $profile) {
            return false;
        }

        return ($profile->profile_status ?? null) === 'approved'
            || ($profile->approval_status ?? null) === 'approved'
            || (bool) ($profile->is_profile_completed ?? false);
    }

    private function attendancePayload(array $payload): array
    {
        if (! Schema::hasTable('attendances')) {
            return $payload;
        }

        return collect($payload)
            ->filter(fn ($value, $column) => Schema::hasColumn('attendances', $column))
            ->all();
    }

    private function isLatePunch(Carbon $now, ?object $shift): bool
    {
        if (! $shift?->late_after_time) {
            return false;
        }

        return $now->gt(Carbon::parse($now->toDateString() . ' ' . $shift->late_after_time, $now->getTimezone()));
    }

    private function lateMinutes(Carbon $now, ?object $shift): int
    {
        if (! $shift?->late_after_time) {
            return 0;
        }

        $lateAfter = Carbon::parse($now->toDateString() . ' ' . $shift->late_after_time, $now->getTimezone());
        return $now->gt($lateAfter) ? $lateAfter->diffInMinutes($now) : 0;
    }

    private function lateWarning(Carbon $now, ?object $shift): ?string
    {
        if (! $shift?->warning_after_time || ! $shift?->block_after_time) {
            return null;
        }

        $warning = Carbon::parse($now->toDateString() . ' ' . $shift->warning_after_time, $now->getTimezone());
        $block = Carbon::parse($now->toDateString() . ' ' . $shift->block_after_time, $now->getTimezone());

        if ($now->betweenIncluded($warning, $block)) {
            return 'Late warning active. Punch in will be blocked after ' . $block->format('h:i A') . '.';
        }
        return null;
    }
}
