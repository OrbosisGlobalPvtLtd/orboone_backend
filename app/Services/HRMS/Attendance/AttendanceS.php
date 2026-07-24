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
    public function __construct(
        private AttendanceRuleResolverService $ruleResolver,
        private ?WfhRequestService $wfhRequestService = null
    ) {}

    public function processPunchIn(
        int $userId,
        string $workMode = 'wfo',
        ?string $note = null,
        array|string|float|null $meta = [],
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

        $hasApprovedLeave = DB::table('leave_requests')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereRaw('? BETWEEN start_date AND end_date', [$today])
            ->exists();

        if ($hasApprovedLeave) {
            return ['status' => 'error', 'message' => 'Attendance punch is disabled during approved leave.'];
        }

        if (! is_array($meta)) {
            $lat = $meta !== null && $meta !== '' ? (float) $meta : null;
            $lng = is_numeric($customTime) ? (float) $customTime : null;
            $customTimeStr = is_string($attendanceTypeId) ? $attendanceTypeId : (is_string($customTime) && ! is_numeric($customTime) ? $customTime : null);
            $meta = [
                'latitude' => $lat,
                'longitude' => $lng,
            ];
            $customTime = $customTimeStr;
            $attendanceTypeId = null;
        }

        if ($enforceEmployeeRules && ! $this->employeeEligibleForAttendance($employee)) {
            return ['status' => 'error', 'message' => 'You are not eligible to mark attendance yet.'];
        }

        $employeeWorkMode = strtolower((string) ($employee->work_mode ?? 'wfo'));
        $requestedWorkMode = strtolower($workMode);

        if ($employeeWorkMode === 'wfh') {
            // Permanent WFH employee - direct WFH attendance, no approval/quota check required
            $workMode = 'wfh';
        } else {
            // WFO employee (or non-permanent WFH)
            if ($requestedWorkMode === 'wfh') {
                $approvedWfh = $this->wfhRequestService?->approvedForDate((int) $employee->id, $today);
                if (! $approvedWfh || $approvedWfh->status !== 'approved') {
                    if ($enforceEmployeeRules) {
                        return [
                            'status' => 'error',
                            'message' => 'You cannot mark WFH attendance because no approved WFH request exists for today.',
                        ];
                    }
                }
                $workMode = 'wfh';
            } else {
                $workMode = 'wfo';
                if ($enforceEmployeeRules) {
                    $locationValidation = $this->validateWfoOfficeLocation($meta);
                    if (($locationValidation['status'] ?? null) === 'error') {
                        return $locationValidation;
                    }
                }
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

        $blockedViolation = DB::table('attendance_violations')
            ->where('employee_id', $employee->id)
            ->where('type', 'blocked_punch')
            ->whereDate('violation_date', $today)
            ->first();

        $isUnlocked = $blockedViolation && $blockedViolation->policy_action === 'resolved';

        if ($existing && ($existing->is_blocked || $existing->is_punch_blocked) && ! $existing->is_admin_unlocked && ! $isUnlocked) {
            if (! $blockedViolation) {
                $this->recordAttendanceViolation($existing, 'blocked_punch', $today, [
                    'source' => 'mobile',
                    'policy_action' => 'blocked',
                    'remarks' => 'Punch-in blocked after allowed time.',
                ]);
            }
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
            && ! $isUnlocked
            && ! optional($existing)->is_late_exempted
            && ! optional($existing)->is_admin_unlocked;

        if ($isBlocked) {
            $blockedType = $this->attendanceType('punch_blocked');
            $blockedAttendance = Attendance::updateOrCreate(
                ['employee_id' => $employee->id, 'attendance_date' => $today],
                [
                    'user_id' => $userId,
                    'attendance_time_id' => $this->resolveShiftIdForPolicy($shift, $existing, $this->defaultShift()?->id),
                    'attendance_type_id' => $blockedType?->id ?: ($existing->attendance_type_id ?? null),
                    'work_mode' => $workMode,
                    'punch_in_latitude' => $meta['latitude'] ?? null,
                    'punch_in_longitude' => $meta['longitude'] ?? null,
                    'punch_in_address' => $meta['address'] ?? null,
                    'punch_in_ip' => $meta['ip'] ?? null,
                    'punch_in_device' => $meta['device'] ?? null,
                    'is_blocked' => true,
                    'is_punch_blocked' => true,
                    'blocked_reason' => 'Punch-in blocked after allowed time.',
                    'block_reason' => 'Punch-in blocked after allowed time.',
                    'attendance_source' => 'mobile',
                    'attendance_status' => 'punch_blocked',
                ]
            );

            if (! $blockedViolation) {
                AttendanceViolationM::create([
                    'employee_id' => $employee->id,
                    'attendance_id' => $blockedAttendance->id,
                    'violation_date' => $today,
                    'type' => 'blocked_punch',
                    'minutes' => 0,
                    'source' => 'mobile',
                    'policy_action' => 'blocked',
                    'remarks' => 'Punch-in blocked after allowed time.',
                ]);
            }
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
        if (($isUnlocked || ($existing && $existing->is_admin_unlocked)) && ! ($existing && $existing->punch_in_time)) {
            $attendanceTypeForPunchIn = $presentType?->id ?: $attendanceTypeForPunchIn;
            $attendanceStatusForPunchIn = 'present';
        }

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $today],
            [
                'user_id' => $userId,
                'attendance_time_id' => $this->resolveShiftIdForPolicy($shift, $existing, $this->defaultShift()?->id),
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
                'is_admin_unlocked' => $isUnlocked || (bool) optional($existing)->is_admin_unlocked,
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

        $this->syncAttendanceViolations($attendance);

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
        array|string|float|null $meta = [],
        ?string $customTime = null,
        bool $enforceEmployeeRules = true,
        $taskSummaryJson = null
    ): array {
        $timezone = $this->attendanceTimezone();

        if (! is_array($meta)) {
            $lat = $meta !== null && $meta !== '' ? (float) $meta : null;
            $lng = is_numeric($customTime) ? (float) $customTime : null;
            $customTimeStr = is_string($customTime) && ! is_numeric($customTime) ? $customTime : null;
            $meta = [
                'latitude' => $lat,
                'longitude' => $lng,
            ];
            $customTime = $customTimeStr;
        }

        $now = $customTime ? Carbon::parse($customTime, $timezone) : Carbon::now($timezone);
        $today = $now->toDateString();
        $employee = Employee::where('user_id', $userId)->first();

        if (! $employee) {
            return ['status' => 'error', 'message' => 'Employee profile not found.'];
        }

        $hasApprovedLeave = DB::table('leave_requests')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereRaw('? BETWEEN start_date AND end_date', [$today])
            ->exists();

        if ($hasApprovedLeave) {
            return ['status' => 'error', 'message' => 'Attendance punch is disabled during approved leave.'];
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

        $this->wfhRequestService?->applyLwpConversionIfRequired($attendance->fresh());

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

        Log::info('HRMS auto-block missed punch-ins bypassed per refactored workflow.', $counts + ['date' => $date]);

        return $counts;
    }

    public function unlockAttendance(int|string $attendanceId, int $adminUserId, array|string|null $payload = null): array
    {
        $data = is_array($payload) ? $payload : ['unlock_remarks' => $payload];
        $unlockType = $data['unlock_type'] ?? 'unlock_only';
        $approvedPunchIn = $data['approved_punch_in_time'] ?? null;
        $presentType = $this->attendanceType('present');
        $now = Carbon::now($this->attendanceTimezone());
        $approvalNote = trim((string) ($data['hr_approval_note'] ?? ''))
            ?: (trim((string) ($data['unlock_remarks'] ?? '')) ?: 'Unlocked by HR/Admin.');

        if (is_string($attendanceId) && str_starts_with($attendanceId, 'violation_')) {
            $violationId = (int) substr($attendanceId, 10);
            $violation = AttendanceViolationM::with('employee')->find($violationId);
            if (! $violation) {
                return ['success' => false, 'status' => 'error', 'message' => 'Blocked punch violation not found.'];
            }

            // Mark violation resolved
            $violation->update([
                'policy_action' => 'resolved',
                'remarks' => ($violation->remarks ?: '') . ' [Resolved: Unlocked/approved by HR/Admin.]',
            ]);

            if ($unlockType === 'manual_punch_in') {
                if (! $approvedPunchIn) {
                    return ['success' => false, 'status' => 'error', 'message' => 'Approved punch-in time is required for manual punch-in.'];
                }

                $date = $violation->violation_date->toDateString();
                $employee = $violation->employee;
                $punchIn = Carbon::parse($date . ' ' . $approvedPunchIn, $this->attendanceTimezone());
                $shift = $this->ruleResolver->getPolicyForEmployee($employee, $date);

                $targetPunchOut = $this->targetPunchOutTime($punchIn, $shift);
                $isLate = $this->isLatePunch($punchIn, $shift);
                $lateMinutes = $isLate ? $this->lateMinutes($punchIn, $shift) : 0;

                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'attendance_date' => $date,
                    'attendance_time_id' => $this->resolveShiftIdForPolicy($shift, null, $this->defaultShift()?->id),
                    'attendance_type_id' => $presentType?->id,
                    'punch_in_time' => $punchIn->format('H:i:s'),
                    'target_punch_out_time' => $targetPunchOut,
                    'attendance_status' => 'present',
                    'attendance_source' => 'admin_unlock',
                    'is_late' => $isLate,
                    'late_minutes' => $lateMinutes,
                    'is_blocked' => false,
                    'is_punch_blocked' => false,
                    'is_admin_unlocked' => true,
                    'unlock_type' => 'manual_punch_in',
                    'unlock_remarks' => $data['unlock_remarks'] ?? null,
                    'hr_approval_note' => $approvalNote,
                    'unlocked_by' => $adminUserId,
                    'unlocked_at' => $now,
                    'hr_approved_by' => $adminUserId,
                    'hr_approved_at' => $now,
                ]);

                // Link the violation to the attendance ID
                $violation->update(['attendance_id' => $attendance->id]);

                $this->syncAttendanceViolations($attendance);

                $this->notifyAttendanceUnlocked($employee, $attendance->id);

                return ['success' => true, 'status' => 'success', 'message' => 'Attendance unlocked successfully.', 'data' => $attendance->fresh()];
            }

            $this->notifyAttendanceUnlocked($violation->employee, null);

            return ['success' => true, 'status' => 'success', 'message' => 'Attendance unlocked successfully.', 'data' => null];
        }

        $attendance = Attendance::with(['employee', 'attendanceTime'])->find($attendanceId);
        if (! $attendance) {
            return ['success' => false, 'status' => 'error', 'message' => 'Attendance record not found.'];
        }

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
                return ['success' => false, 'status' => 'error', 'message' => 'Approved punch-in time is required for manual punch-in.'];
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

        $this->syncAttendanceViolations($attendance);

        if ($attendance->punch_in_time && $attendance->punch_out_time) {
            $this->calculateAttendanceStats($attendance);
        }

        $this->notifyAttendanceUnlocked($attendance->employee, $attendance->id);

        return ['success' => true, 'status' => 'success', 'message' => 'Attendance unlocked successfully.', 'data' => $attendance->fresh()];
    }

    private function notifyAttendanceUnlocked($employee, ?int $attendanceId): void
    {
        try {
            $notificationService = app(\App\Services\HRMS\Notification\NotificationS::class);
            $user = $employee?->user ?: ($employee ? \App\Models\Core\UserM::find($employee->user_id) : null);
            if ($user) {
                $notificationService->notifyEmployee(
                    'Attendance Unlocked',
                    "Your attendance has been unlocked.\nYou can now Punch In.",
                    'attendance_unlocked',
                    'hrms.attendance.my',
                    [],
                    ['employee_id' => $employee->id, 'attendance_id' => $attendanceId],
                    $user->id
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Attendance unlock notification failed: ' . $e->getMessage());
        }
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
            'skipped_pending_regularization' => 0,
            'skipped_policy_disabled' => 0,
            'skipped_not_due' => 0,
            'skipped_has_punch_in' => 0,
            'skipped_has_attendance' => 0,
            'skipped_no_user' => 0,
            'skipped_inactive' => 0,
            'skipped_profile' => 0,
        ];

        if (! $absentType) {
            $counts['missing_attendance_type'] = 1;
            return $counts;
        }

        // 1. Process legacy blocked records (for regression parity)
        $legacyRecords = Attendance::with('attendanceType')
            ->whereDate('attendance_date', $date)
            ->where(function ($query) {
                $query->where('is_blocked', true)
                    ->orWhere('is_punch_blocked', true)
                    ->orWhere('attendance_status', 'punch_blocked');
            })
            ->get();

        foreach ($legacyRecords as $attendance) {
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

            if ($attendance->punch_in_time || $attendance->punch_out_time) {
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

            if ($employee && $this->employeeOnLeave($employee, $date)) {
                $counts['skipped_protected_status']++;
                continue;
            }

            if ($this->hasPendingRegularization($attendance, $date)) {
                $counts['skipped_pending_regularization']++;
                continue;
            }

            $typeCode = optional($attendance->attendanceType)->code;
            if (in_array($attendance->attendance_type_id, $skipTypeIds, true) || in_array($typeCode, ['leave', 'holiday', 'week_off'], true) || in_array($attendance->attendance_status, ['leave', 'holiday', 'week_off'], true)) {
                $counts['skipped_protected_status']++;
                continue;
            }

            $reason = 'Auto marked absent at day-end due to unresolved punch block.';
            $updates = $this->attendancePayload([
                'attendance_type_id' => $absentType->id,
                'attendance_status' => 'absent',
                'attendance_source' => 'system_auto',
                'is_blocked' => false,
                'is_punch_blocked' => false,
                'is_locked' => false,
                'is_lwp' => false,
                'lwp_reason' => null,
                'is_half_day' => false,
                'half_day_reason' => null,
                'missed_punch' => false,
                'is_missed_punch' => false,
                'missed_punch_reason' => null,
                'remarks' => $reason,
                'pending_hr_reason' => null,
            ]);

            if ($dryRun) {
                $counts['would_mark_absent']++;
                continue;
            }

            $attendance->fill($updates)->save();
            $counts['marked_absent']++;
        }

        // 2. Process active employees who NEVER punched in (Rule 4)
        $employees = Employee::with(['profile', 'documents'])->active()->get();

        foreach ($employees as $employee) {
            $counts['total_checked']++;

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

            $policy = $this->ruleResolver->getPolicyForEmployee($employee, $date);
            if (! $policy || ! (bool) ($policy->auto_absent_enabled ?? false)) {
                $counts['skipped_policy_disabled']++;
                continue;
            }

            if (! $this->dayEndProcessingDue($date, $policy, $now)) {
                $counts['skipped_not_due']++;
                continue;
            }

            // Check if there is already an attendance record for this day
            $existing = Attendance::where('employee_id', $employee->id)->whereDate('attendance_date', $date)->first();
            if ($existing) {
                $counts['skipped_has_attendance']++;
                continue;
            }

            $dayContext = $this->ruleResolver->getDayContext($employee, $date);
            if ($dayContext['is_on_leave'] || $dayContext['is_holiday'] || $dayContext['is_weekoff']) {
                $counts['skipped_protected_status']++;
                continue;
            }

            // Employee never punched in. Create absent record automatically at day end.
            $reason = 'Auto marked absent at day-end because employee never punched in.';
            $updates = $this->attendancePayload([
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
                'attendance_time_id' => $this->resolveShiftIdForPolicy($policy, null, null),
                'attendance_type_id' => $absentType->id,
                'attendance_date' => $date,
                'attendance_status' => 'absent',
                'attendance_source' => 'system_auto',
                'is_lwp' => true,
                'lwp_reason' => 'Absent (Never punched in)',
                'is_blocked' => false,
                'is_punch_blocked' => false,
                'is_locked' => false,
                'is_half_day' => false,
                'missed_punch' => false,
                'is_missed_punch' => false,
                'remarks' => $reason,
            ]);

            if ($dryRun) {
                $counts['would_mark_absent']++;
                continue;
            }

            Attendance::create($updates);
            $counts['marked_absent_never_punched'] = ($counts['marked_absent_never_punched'] ?? 0) + 1;
        }

        Log::info('HRMS auto-close blocked/absent attendance completed.', $counts + ['date' => $date]);

        return $counts;
    }

    private function hasPendingRegularization(Attendance $attendance, string $date): bool
    {
        if (! Schema::hasTable('attendance_regularizations')) {
            return false;
        }

        $query = DB::table('attendance_regularizations')
            ->where('employee_id', $attendance->employee_id)
            ->where('status', 'pending');

        if (Schema::hasColumn('attendance_regularizations', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->where(function ($q) use ($attendance, $date) {
            $q->where('attendance_id', $attendance->id);

            if (Schema::hasColumn('attendance_regularizations', 'requested_punch_in')) {
                $q->orWhereDate('requested_punch_in', $date);
            }
            if (Schema::hasColumn('attendance_regularizations', 'requested_punch_out')) {
                $q->orWhereDate('requested_punch_out', $date);
            }
            if (Schema::hasColumn('attendance_regularizations', 'created_at')) {
                $q->orWhereDate('created_at', $date);
            }
        });

        return $query->exists();
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
        } elseif (($halfDayMinutes > 0 && $netMinutes < $halfDayMinutes) || ($combinedViolationLimit > 0 && $violationCount >= $combinedViolationLimit)) {
            $typeCode = 'half_day';
            $isHalfDay = true;
        }

        $threshold = $this->policyDayEndTime($shift) ?: '23:59:00';
        $dayCloseTime = Carbon::parse($date . ' ' . $threshold, $timezone);
        $isCheckedOutBeforeClose = $out->lte($dayCloseTime);

        $updateData = [
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
        ];

        if ($isCheckedOutBeforeClose) {
            $updateData['missed_punch'] = false;
            $updateData['is_missed_punch'] = false;
            $updateData['missed_punch_reason'] = null;
        }

        $attendance->fill($updateData);

        if (! $attendance->is_blocked && ! $attendance->is_punch_blocked) {
            $attendance->attendance_type_id = $this->attendanceType($typeCode)?->id ?: $attendance->attendance_type_id;
            $attendance->attendance_status = $typeCode;
        }

        $attendance->save();

        if ($isCheckedOutBeforeClose && Schema::hasTable('attendance_violations')) {
            AttendanceViolationM::where('attendance_id', $attendance->id)
                ->where('type', 'missed_punch')
                ->delete();
        }

        if ($isHalfDay) {
            $this->notifyAttendance($employee, 'half_day', 'Half Day Marked', 'Your attendance has been marked as half day.', $date, [
                'attendance_id' => $attendance->id,
                'total_work_minutes' => $netMinutes,
            ]);
        }

        if ($isEarly) {
            $this->recordAttendanceViolation($attendance, 'early_logout', $date, [
                'minutes' => $attendance->early_out_minutes ?? 0,
                'source' => 'system',
                'policy_action' => 'early_logout',
                'remarks' => 'Early logout detected.',
            ]);
            $this->notifyAttendance($employee, 'early_logout', 'Early Logout Recorded', 'Your punch-out was earlier than the target time.', $date, [
                'attendance_id' => $attendance->id,
                'early_out_minutes' => $attendance->early_out_minutes,
            ]);
        }

        if ($attendance->is_late) {
            $this->recordAttendanceViolation($attendance, 'late_login', $date, [
                'minutes' => $attendance->late_minutes ?? 0,
                'source' => 'system',
                'policy_action' => 'late_mark',
                'remarks' => 'Late login detected.',
            ]);
        }

        $this->applyCombinedViolationHalfDay($attendance, $date);
        $this->syncAttendanceViolations($attendance);

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
        $pendingHrType = $this->attendanceType('pending_hr');
        $skipTypeIds = AttendanceType::whereIn('code', ['leave', 'holiday', 'week_off', 'punch_blocked'])->pluck('id')->all();
        $counts = [
            'dry_run' => $dryRun ? 1 : 0,
            'total_checked' => 0,
            'marked_missed_punch' => 0,
            'would_mark_missed_punch' => 0,
            'marked_absent' => 0,
            'marked_lwp' => 0,
            'marked_warning' => 0,
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
            $warningIndex = min($missedCount, max(1, $allowedMissedPunches));
            $missedPunchType = $this->attendanceType('missed_punch');
            $type = $limitExceeded && $lwpType ? $lwpType : ($missedPunchType ?: ($pendingHrType ?: $attendance->attendanceType ?: $absentType));
            $nth = $this->ordinal($missedCount);
            $reason = $limitExceeded
                ? "{$nth} missed punch converted to LWP (Allowed: {$allowedMissedPunches})"
                : "Missed punch warning {$warningIndex} of {$allowedMissedPunches}. No deduction applied.";

            $updates = $this->attendancePayload([
                'missed_punch' => true,
                'is_missed_punch' => true,
                'missed_punch_reason' => $reason,
                'attendance_type_id' => $type->id,
                'attendance_status' => $limitExceeded && $lwpType ? 'lwp' : 'missed_punch',
                'attendance_source' => 'system_auto',
                'is_lwp' => $limitExceeded,
                'lwp_reason' => $limitExceeded ? $reason : null,
                'punch_out_note' => $reason,
                'pending_hr_reason' => $limitExceeded ? null : $reason,
                'is_locked' => $limitExceeded,
                'is_half_day' => false,
                'total_work_minutes' => 0,
                'gross_work_minutes' => 0,
                'remarks' => $limitExceeded ? $reason : 'Missed punch-out. Regularization required.',
            ]);

            if ($dryRun) {
                $counts['would_mark_missed_punch']++;
                continue;
            }

            $attendance->fill($updates)->save();
            if ($this->recordAttendanceViolation($attendance, 'missed_punch', $date, [
                'source' => 'system_auto',
                'policy_action' => $limitExceeded ? 'lwp' : 'warning',
                'converted_to_lwp' => $limitExceeded,
                'remarks' => $reason,
            ])) {
                $counts['violations_created']++;
            }

            $counts['marked_missed_punch']++;
            if ($limitExceeded && $lwpType) {
                $counts['marked_lwp']++;
            } else {
                $counts['marked_warning']++;
            }
        }

        if (!$dryRun) {
            $this->finalizeUnresolvedMissedPunches();
        }

        Log::info('HRMS process missed punches completed.', $counts + ['date' => $date]);

        return $counts;
    }

    public function finalizeUnresolvedMissedPunches(): int
    {
        $timezone = $this->attendanceTimezone();
        $today = Carbon::today($timezone);

        $graceDays = 3;
        if (Schema::hasTable('settings')) {
            $setting = DB::table('settings')->where('key', 'missed_punch_grace_days')->first();
            if ($setting) {
                $graceDays = (int) $setting->value;
            }
        }

        $cutoffDate = $today->copy()->subDays($graceDays)->toDateString();

        $lwpType = $this->attendanceType('lwp');
        if (!$lwpType) {
            Log::error('LWP attendance type not found. Cannot finalize unresolved missed punches.');
            return 0;
        }

        $records = Attendance::query()
            ->whereDate('attendance_date', '<=', $cutoffDate)
            ->where(function ($query) {
                $query->where('attendance_status', 'missed_punch')
                    ->orWhere(function ($q) {
                        $q->where('attendance_status', 'pending_hr')
                            ->where(function ($q2) {
                                $q2->where('missed_punch', true)
                                    ->orWhere('is_missed_punch', true);
                            });
                    });
            })
            ->get();

        $updatedCount = 0;
        foreach ($records as $attendance) {
            $hasRegularization = DB::table('attendance_regularizations')
                ->where('employee_id', $attendance->employee_id)
                ->where('attendance_id', $attendance->id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereNull('deleted_at')
                ->exists();

            if ($hasRegularization) {
                continue;
            }

            $reason = 'Missed punch regularization not submitted within grace period';
            $attendance->fill([
                'attendance_status' => 'lwp',
                'attendance_type_id' => $lwpType->id,
                'is_lwp' => true,
                'lwp_reason' => $reason,
                'remarks' => $reason,
            ]);
            $attendance->save();

            $this->syncAttendanceViolations($attendance);

            $updatedCount++;
        }

        return $updatedCount;
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
        $target = null;
        if ($attendance?->target_punch_out_time) {
            $target = Carbon::parse($now->toDateString() . ' ' . $this->ruleResolver->timeString($attendance->target_punch_out_time), $timezone);
        } elseif ($attendance?->punch_in_time) {
            $in = Carbon::parse($now->toDateString() . ' ' . $this->ruleResolver->timeString($attendance->punch_in_time), $timezone);
            $target = $this->ruleResolver->targetPunchOut($in, $shift);
        }
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

        $state = $this->ruleResolver->resolveMobileState($employee, $now, $attendance);
        $statusCodeVal = $state['status_code'];
        $statusName = $state['status_name'];
        $isBlocked = $state['is_blocked'];
        $canPunchIn = $state['can_punch_in'];
        $canPunchOut = $state['can_punch_out'];

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
            'next_action' => $state['next_action'],
            'dynamic_timer_enabled' => (bool) ($attendance?->punch_in_time && ! $attendance?->punch_out_time),
            'office_location' => $this->officeLocationPayload(),
        ];
    }

    public function resolveFinalStatus(?Attendance $attendance): array
    {
        if (! $attendance) {
            return ['status_code' => 'not_punched', 'status_name' => 'Not Punched'];
        }

        $employee = $attendance->employee ?: Employee::find($attendance->employee_id);
        $date = Carbon::parse($attendance->attendance_date, $this->attendanceTimezone());
        if ($employee) {
            $state = $this->ruleResolver->resolveMobileState($employee, $date, $attendance);
            return [
                'status_code' => $state['status_code'],
                'status_name' => $state['status_name'],
            ];
        }

        $statusCode = $attendance->attendance_status ?: optional($attendance->attendanceType)->code ?: 'absent';
        if ($statusCode === 'lwp') {
            $statusCode = 'absent';
        }
        return [
            'status_code' => $statusCode,
            'status_name' => $statusCode === 'absent' ? 'Absent' : ucwords(str_replace('_', ' ', $statusCode)),
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

        $policyDayEnd = $this->policyDayEndTime($policy);
        if ($policyDayEnd) {
            $dueAt = Carbon::parse($date . ' ' . $policyDayEnd, $this->attendanceTimezone());
        } elseif ($policy?->shift_end_time) {
            $dueAt = Carbon::parse($date . ' ' . $this->ruleResolver->timeString($policy->shift_end_time), $this->attendanceTimezone());
        } else {
            $dueAt = Carbon::parse($date . ' 23:59:00', $this->attendanceTimezone());
        }

        return $now->gte($dueAt);
    }

    private function missedPunchProcessingDue(string $date, ?object $policy, Carbon $now): bool
    {
        $attendanceDate = Carbon::parse($date, $this->attendanceTimezone())->startOfDay();
        if ($attendanceDate->lt($now->copy()->startOfDay())) {
            return true;
        }

        $threshold = $this->policyDayEndTime($policy) ?: '23:59:00';

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

    private function applyCombinedViolationHalfDay(Attendance $attendance, string $date): void
    {
        if ($attendance->is_lwp || $attendance->is_blocked || $attendance->is_punch_blocked) {
            return;
        }

        $asOfDate = Carbon::parse($date, $this->attendanceTimezone());
        $count = AttendanceViolationM::where('employee_id', $attendance->employee_id)
            ->whereYear('violation_date', $asOfDate->year)
            ->whereMonth('violation_date', $asOfDate->month)
            ->whereIn('type', ['late_login', 'early_logout'])
            ->count();

        $employee = $attendance->employee ?: Employee::find($attendance->employee_id);
        $policy = $employee ? $this->ruleResolver->getPolicyForEmployee($employee, $date) : null;
        $combinedViolationLimit = $policy ? (int) ($policy->combined_violation_limit ?? 3) : 3;

        if ($combinedViolationLimit <= 0) {
            return;
        }

        if ($count < $combinedViolationLimit) {
            return;
        }

        $effectiveCode = strtolower((string) (optional($attendance->attendanceType)->code ?: $attendance->attendance_status));
        if (
            (bool) $attendance->is_lwp
            || in_array($effectiveCode, ['lwp', 'absent', 'half_day'], true)
            || (bool) $attendance->is_half_day
        ) {
            return;
        }

        $halfDayType = $this->attendanceType('half_day');
        $updates = [
            'is_half_day' => true,
            'half_day_reason' => "Auto half-day due to {$combinedViolationLimit} monthly combined violations (late + early logout).",
            'violation_count' => max((int) $attendance->violation_count, $count),
        ];
        if ($halfDayType) {
            $updates['attendance_type_id'] = $halfDayType->id;
            $updates['attendance_status'] = 'half_day';
        }
        $attendance->fill($this->attendancePayload($updates))->save();
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
            'message' => $allowed ? 'Success' : 'You are outside the allowed office location.',
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
            if ($requiredDocs->isNotEmpty() && $requiredDocs->contains(fn($doc) => $doc->verification_status !== 'verified')) {
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
            ->filter(fn($value, $column) => Schema::hasColumn('attendances', $column))
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

    public function syncAttendanceViolations(Attendance $attendance): void
    {
        if (! Schema::hasTable('attendance_violations')) {
            return;
        }

        $date = Carbon::parse($attendance->attendance_date)->toDateString();

        // 1. Late Login
        if ($attendance->is_late || (int) $attendance->late_minutes > 0) {
            $this->recordAttendanceViolation($attendance, 'late_login', $date, [
                'minutes' => (int) $attendance->late_minutes,
                'source' => $attendance->attendance_source ?: 'system',
                'policy_action' => 'late_mark',
                'remarks' => $attendance->punch_in_note ?: 'Late login detected.',
            ]);
        } else {
            // Check if there is an active late login violation to resolve
            $violation = AttendanceViolationM::where('attendance_id', $attendance->id)
                ->where('type', 'late_login')
                ->whereDate('violation_date', $date)
                ->first();
            if ($violation && $violation->policy_action !== 'resolved') {
                $reason = $attendance->is_late_exempted ? 'Exempted by HR/Admin' : 'Regularized/recalculated';
                $violation->update([
                    'policy_action' => 'resolved',
                    'remarks' => $violation->remarks . " [Resolved: {$reason}.]",
                ]);
            }
        }

        // 2. Early Logout
        if ($attendance->is_early_out || (int) $attendance->early_out_minutes > 0) {
            $this->recordAttendanceViolation($attendance, 'early_logout', $date, [
                'minutes' => (int) $attendance->early_out_minutes,
                'source' => $attendance->attendance_source ?: 'system',
                'policy_action' => 'early_logout',
                'remarks' => $attendance->punch_out_note ?: 'Early logout detected.',
            ]);
        } else {
            // Check if there is an active early logout violation to resolve
            $violation = AttendanceViolationM::where('attendance_id', $attendance->id)
                ->where('type', 'early_logout')
                ->whereDate('violation_date', $date)
                ->first();
            if ($violation && $violation->policy_action !== 'resolved') {
                $violation->update([
                    'policy_action' => 'resolved',
                    'remarks' => $violation->remarks . ' [Resolved: Adjusted by HR/Admin / recalculated.]',
                ]);
            }
        }

        // 3. Blocked Punch
        $isBlocked = $attendance->is_blocked || $attendance->is_punch_blocked || optional($attendance->attendanceType)->code === 'punch_blocked' || $attendance->attendance_status === 'punch_blocked';
        if ($isBlocked) {
            // Only log blocked punch violation if it was an actual user punch attempt (source is NOT system_auto)
            if ($attendance->attendance_source !== 'system_auto') {
                // Check by employee and date
                $exists = AttendanceViolationM::where('employee_id', $attendance->employee_id)
                    ->where('type', 'blocked_punch')
                    ->whereDate('violation_date', $date)
                    ->first();
                if (! $exists) {
                    AttendanceViolationM::create([
                        'employee_id' => $attendance->employee_id,
                        'attendance_id' => $attendance->id,
                        'violation_date' => $date,
                        'type' => 'blocked_punch',
                        'minutes' => 0,
                        'source' => $attendance->attendance_source ?: 'system_auto',
                        'policy_action' => 'blocked',
                        'remarks' => $attendance->blocked_reason ?: ($attendance->block_reason ?: 'Punch blocked.'),
                    ]);
                } else {
                    if (! $exists->attendance_id) {
                        $exists->update(['attendance_id' => $attendance->id]);
                    }
                }
            }
        } else {
            // Check if there is an active blocked punch violation to resolve (by employee and date)
            $violation = AttendanceViolationM::where('employee_id', $attendance->employee_id)
                ->where('type', 'blocked_punch')
                ->whereDate('violation_date', $date)
                ->first();
            if ($violation) {
                $violationUpdates = [];
                if (! $violation->attendance_id) {
                    $violationUpdates['attendance_id'] = $attendance->id;
                }
                if ($violation->policy_action !== 'resolved') {
                    $violationUpdates['policy_action'] = 'resolved';
                    $violationUpdates['remarks'] = ($violation->remarks ?: '') . ' [Resolved: Unlocked/approved by HR/Admin.]';
                }
                if (! empty($violationUpdates)) {
                    $violation->update($violationUpdates);
                }
            }
        }

        // 4. Missed Punch
        if ($attendance->missed_punch || $attendance->is_missed_punch || $attendance->attendance_status === 'missed_punch') {
            $this->recordAttendanceViolation($attendance, 'missed_punch', $date, [
                'minutes' => 0,
                'source' => $attendance->attendance_source ?: 'system_auto',
                'policy_action' => $attendance->is_lwp ? 'lwp' : 'warning',
                'converted_to_lwp' => (bool) $attendance->is_lwp,
                'remarks' => $attendance->missed_punch_reason ?: 'Missed punch detected.',
            ]);
        }
    }

    private function resolveShiftIdForPolicy(?object $policy, ?object $existing = null, ?int $fallback = null): ?int
    {
        $resolvedShiftId = null;
        if ($policy) {
            if (($policy->source_table ?? null) === 'attendance_times') {
                $resolvedShiftId = $policy->id;
            } else {
                $policyName = $policy->policy_name ?? $policy->name ?? '';
                $mappedShiftCode = null;
                if (str_contains($policyName, 'Default') || str_contains($policyName, 'General')) {
                    $mappedShiftCode = 'general_shift';
                } elseif (str_contains($policyName, 'Part Time')) {
                    $mappedShiftCode = 'part_time_shift';
                } elseif (str_contains($policyName, 'Half Day Morning')) {
                    $mappedShiftCode = 'half_day_morning';
                } elseif (str_contains($policyName, 'Half Day Evening')) {
                    $mappedShiftCode = 'half_day_evening';
                } elseif (str_contains($policyName, 'Half Day')) {
                    $mappedShiftCode = 'half_day_shift';
                } elseif (str_contains($policyName, 'WFH')) {
                    $mappedShiftCode = 'wfh_shift';
                }

                if ($mappedShiftCode) {
                    $resolvedShiftId = DB::table('attendance_times')->where('code', $mappedShiftCode)->value('id');
                }
            }
        }
        return $resolvedShiftId ?: ($existing->attendance_time_id ?? $fallback);
    }

    private function ordinal(int $number): string
    {
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number . 'th';
        } else {
            return $number . $ends[$number % 10];
        }
    }
}
