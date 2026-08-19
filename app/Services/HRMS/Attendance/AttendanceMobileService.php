<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Carbon\Carbon;

class AttendanceMobileService
{
    public function __construct(
        private AttendanceS $attendanceService,
        private AttendanceRuleResolverService $resolver,
        private ?WfhRequestService $wfhRequestService = null
    ) {}

    public function profileStatus(int $userId): array
    {
        $employee = Employee::with(['profile', 'documents'])->where('user_id', $userId)->first();
        if (! $employee) {
            return ['status' => false, 'message' => 'Employee profile not found.', 'data' => null];
        }

        return [
            'status' => true,
            'message' => 'Attendance profile status fetched successfully.',
            'data' => [
                'employee_id' => $employee->id,
                'profile_status' => $employee->profile?->profile_status ?? $employee->profile?->approval_status,
                'policy' => $this->resolver->policyPayload($this->resolver->getPolicyForEmployee($employee)),
                'day_context' => $this->resolver->getDayContext($employee),
            ],
        ];
    }

    public function todayStatus(int $userId): array
    {
        $employee = Employee::with(['profile', 'documents'])->where('user_id', $userId)->first();
        if (! $employee) {
            return ['status' => false, 'message' => 'Employee profile not found.', 'data' => null];
        }

        $payload = $this->resolver->buildMobileRulePayload($employee, Carbon::now(AttendanceRuleResolverService::TIMEZONE));

        if (empty((array) $payload['attendance']) && $payload['ui']['show_blocked_card'] === true) {
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', Carbon::now(AttendanceRuleResolverService::TIMEZONE)->toDateString())
                ->first();

            if ($attendance) {
                $payload['attendance'] = $this->formatAttendanceForApi($attendance, $payload['policy'] ?? null);
            }
        } else {
            $payload['attendance'] = $payload['attendance'] ? $this->formatAttendanceForApi($payload['attendance'], $payload['policy'] ?? null) : (object)[];
        }

        $attendanceData = is_array($payload['attendance']) ? $payload['attendance'] : [];
        $payload['status_code'] = $attendanceData['status_code'] ?? ($payload['ui']['status_code'] ?? 'not_punched');
        $payload['status_name'] = $attendanceData['status_name'] ?? ucwords(str_replace('_', ' ', $payload['status_code']));

        $hasPunchIn = ! empty($attendanceData['punch_in_time']);
        $hasPunchOut = ! empty($attendanceData['punch_out_time']);

        $hasWorkReq = ! empty($payload['day_context']['has_approved_work_request']);
        $isClosedState = in_array($payload['status_code'], ['absent', 'present', 'lwp', 'leave', 'holiday', 'week_off', 'missed_punch'], true)
            || ($payload['status_code'] === 'half_day' && $hasPunchOut);

        if ($hasWorkReq && in_array($payload['status_code'], ['holiday', 'week_off'], true)) {
            $isClosedState = false;
        }

        if ($isClosedState) {
            $payload['is_blocked'] = false;
            $payload['is_punch_blocked'] = false;
            $payload['can_punch_in'] = false;
            if (isset($payload['ui']) && is_array($payload['ui'])) {
                $payload['ui']['is_blocked'] = false;
                $payload['ui']['is_punch_blocked'] = false;
                $payload['ui']['show_blocked_card'] = false;
                $payload['ui']['blocked_message'] = null;
                $payload['ui']['status_code'] = $payload['status_code'];
                $payload['ui']['status_name'] = $payload['status_name'];
            }
        } else {
            $payload['is_blocked'] = (bool) ($attendanceData['is_blocked'] ?? $payload['ui']['is_blocked'] ?? false);
            $payload['is_punch_blocked'] = (bool) ($attendanceData['is_punch_blocked'] ?? $payload['ui']['is_punch_blocked'] ?? false);
            $payload['can_punch_in'] = (bool) ($attendanceData['can_punch_in'] ?? $payload['ui']['can_punch_in'] ?? false);
            if (! $hasPunchIn && ($hasWorkReq || ($payload['day_context']['is_half_day_leave'] ?? false) || ($payload['ui']['status_code'] ?? '') === 'half_day' || $payload['status_code'] === 'half_day')) {
                $payload['can_punch_in'] = true;
                $payload['next_action'] = 'punch_in';
                if (isset($payload['ui']) && is_array($payload['ui'])) {
                    $payload['ui']['can_punch_in'] = true;
                    $payload['ui']['next_action'] = 'punch_in';
                }
            }
        }

        if ($payload['status_code'] === 'leave') {
            $payload['can_punch_in'] = false;
            $payload['can_punch_out'] = false;
            $payload['next_action'] = 'none';
            if (isset($payload['ui']) && is_array($payload['ui'])) {
                $payload['ui']['can_punch_in'] = false;
                $payload['ui']['can_punch_out'] = false;
                $payload['ui']['next_action'] = 'none';
            }
        }

        $payload['can_punch_out'] = (bool) ($attendanceData['can_punch_out'] ?? $payload['ui']['can_punch_out'] ?? false);
        if ($payload['status_code'] === 'leave') {
            $payload['can_punch_out'] = false;
        }
        $payload['next_action'] = $attendanceData['next_action'] ?? ($payload['ui']['next_action'] ?? 'none');
        $payload['office_location'] = $this->attendanceService->officeLocationPayload();

        $wfhApproved = false;
        if ($employee && $this->wfhRequestService) {
            $wfhApproved = (bool) $this->wfhRequestService->approvedForDate((int) $employee->id, Carbon::now(AttendanceRuleResolverService::TIMEZONE)->toDateString());
        }

        $isPermanentWfh = $employee ? $employee->isPermanentWfh() : false;
        $dayCtx = $payload['day_context'] ?? [];
        $todayWorkMode = 'wfo';
        $workModeLabel = 'Working From Office';

        if (! empty($dayCtx['is_holiday']) && ! $hasWorkReq) {
            $todayWorkMode = 'holiday';
            $workModeLabel = 'Holiday';
        } elseif (! empty($dayCtx['is_weekoff']) && ! $hasWorkReq) {
            $todayWorkMode = 'week_off';
            $workModeLabel = 'Weekly Off';
        } elseif (! empty($dayCtx['is_on_leave'])) {
            $todayWorkMode = 'leave';
            $workModeLabel = 'Leave';
        } elseif ($hasWorkReq) {
            $todayWorkMode = $wfhApproved ? 'wfh' : 'wfo';
            $workModeLabel = $wfhApproved ? 'Working From Home (Approved Work Request)' : 'Working From Office (Approved Work Request)';
        } elseif ($isPermanentWfh) {
            $todayWorkMode = 'wfh';
            $workModeLabel = 'Working From Home (Permanent)';
        } elseif ($wfhApproved) {
            $todayWorkMode = 'wfh';
            $workModeLabel = 'Working From Home';
        }

        $payload['today_work_mode'] = $todayWorkMode;
        $payload['work_mode_label'] = $workModeLabel;
        $payload['is_permanent_wfh'] = $isPermanentWfh;
        $payload['show_wfh_module'] = ! $isPermanentWfh;
        $payload['can_use_web_attendance'] = $employee->canUseWebAttendance();
        $payload['can_use_mobile_attendance'] = $employee->canUseMobileAttendance();

        $violationCycle = '0 / 3';
        $missedPunchCycle = '0 / 2';
        if ($employee && \Illuminate\Support\Facades\Schema::hasTable('attendance_violations')) {
            $year = Carbon::now('Asia/Kolkata')->year;
            $month = Carbon::now('Asia/Kolkata')->month;

            $qDisc = \Illuminate\Support\Facades\DB::table('attendance_violations')
                ->where('employee_id', $employee->id)
                ->whereIn('type', ['late_login', 'late_mark', 'early_logout', 'early_out'])
                ->whereYear('violation_date', $year)
                ->whereMonth('violation_date', $month)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'pending');
                });
            if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_violations', 'is_consumed')) {
                $qDisc->where(function ($query) {
                    $query->whereNull('is_consumed')->orWhere('is_consumed', false);
                });
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_violations', 'deleted_at')) {
                $qDisc->whereNull('deleted_at');
            }
            $countDisc = $qDisc->count();
            if ($countDisc > 0) {
                $posDisc = (($countDisc - 1) % 3) + 1;
                $violationCycle = "{$posDisc} / 3";
            }

            $qMissed = \Illuminate\Support\Facades\DB::table('attendance_violations')
                ->where('employee_id', $employee->id)
                ->whereIn('type', ['missed_punch'])
                ->whereYear('violation_date', $year)
                ->whereMonth('violation_date', $month)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'pending');
                });
            if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_violations', 'is_consumed')) {
                $qMissed->where(function ($query) {
                    $query->whereNull('is_consumed')->orWhere('is_consumed', false);
                });
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('attendance_violations', 'deleted_at')) {
                $qMissed->whereNull('deleted_at');
            }
            $countMissed = $qMissed->count();
            if ($countMissed > 0) {
                $posMissed = (($countMissed - 1) % 2) + 1;
                $missedPunchCycle = "{$posMissed} / 2";
            }
        }

        $payload['violation_cycle'] = $violationCycle;
        $payload['violations'] = $violationCycle;
        $payload['discipline_cycle'] = $violationCycle;
        $payload['missed_punch_cycle'] = $missedPunchCycle;
        $payload['hours_this_month'] = $violationCycle;
        $payload['total_work_hours'] = $violationCycle;

        return [
            'success' => true,
            'status' => true,
            'message' => 'Today attendance status fetched successfully.',
            'data' => $payload + ['wfh_approved_today' => $wfhApproved || $isPermanentWfh],
            'errors' => null
        ];
    }

    private function localDate($value): ?string
    {
        if (!$value) return null;
        try {
            if ($value instanceof Carbon) {
                return $value->copy()->timezone(AttendanceRuleResolverService::TIMEZONE)->format('Y-m-d');
            }
            return Carbon::parse($value)->timezone(AttendanceRuleResolverService::TIMEZONE)->format('Y-m-d');
        } catch (\Exception $e) {
            return is_string($value) ? explode(' ', $value)[0] : null;
        }
    }

    private function localDateTime($value): ?string
    {
        if (!$value) return null;
        try {
            if ($value instanceof Carbon) {
                return $value->copy()->timezone(AttendanceRuleResolverService::TIMEZONE)->format('Y-m-d H:i:s');
            }
            if (is_string($value) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', trim($value))) {
                return null;
            }
            return Carbon::parse($value)->timezone(AttendanceRuleResolverService::TIMEZONE)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function localTime($value): ?string
    {
        if (!$value) return null;
        try {
            if ($value instanceof Carbon) {
                return $value->copy()->timezone(AttendanceRuleResolverService::TIMEZONE)->format('H:i:s');
            }
            if (is_string($value) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', trim($value))) {
                return Carbon::parse($value, AttendanceRuleResolverService::TIMEZONE)->format('H:i:s');
            }
            return Carbon::parse($value)->timezone(AttendanceRuleResolverService::TIMEZONE)->format('H:i:s');
        } catch (\Exception $e) {
            return is_string($value) && strlen($value) <= 8 ? $value : null;
        }
    }

    private function combineLocalDateAndTime($dateValue, $timeValue): ?Carbon
    {
        if (!$timeValue) return null;
        try {
            if ($timeValue instanceof Carbon) {
                return $timeValue->copy()->timezone(AttendanceRuleResolverService::TIMEZONE);
            }
            if (is_string($timeValue) && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', trim($timeValue))) {
                return Carbon::parse($timeValue)->timezone(AttendanceRuleResolverService::TIMEZONE);
            }
            $dateStr = $this->localDate($dateValue) ?: date('Y-m-d');
            return Carbon::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . trim($timeValue), AttendanceRuleResolverService::TIMEZONE);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function formatAttendanceForApi($attendance, $policy = null): array
    {
        if (! $attendance) {
            return [];
        }

        $data = is_array($attendance) ? $attendance : $attendance->toArray();
        $rawDate = $data['attendance_date'] ?? null;
        $typeCode = $data['attendance_type']['code'] ?? null;
        $hasPunchIn = ! empty($data['punch_in_time']);
        $hasPunchOut = ! empty($data['punch_out_time']);

        $empForPolicy = null;
        if (! empty($data['user_id'])) {
            $empForPolicy = Employee::where('user_id', $data['user_id'])->first();
        } elseif (! empty($data['employee_id'])) {
            $empForPolicy = Employee::find($data['employee_id']);
        }
        $dateForPolicy = Carbon::parse($rawDate ?: date('Y-m-d'), AttendanceRuleResolverService::TIMEZONE);
        $attObj = is_array($attendance) ? (new Attendance($data)) : $attendance;

        if ($empForPolicy) {
            $state = $this->resolver->resolveMobileState($empForPolicy, $dateForPolicy, $attObj);
            $data['status_code'] = $state['status_code'];
            $data['status_name'] = $state['status_name'];
            $data['attendance_status'] = $state['status_code'];
            $data['attendance_state'] = $state['attendance_state'];
            $data['is_blocked'] = $state['is_blocked'];
            $data['is_punch_blocked'] = $state['is_punch_blocked'];
            $data['can_punch_in'] = $state['can_punch_in'];
            $data['can_punch_out'] = $state['can_punch_out'];
            $data['next_action'] = $state['next_action'];
        } else {
            $resolved = $this->attendanceService->resolveFinalStatus($attObj);
            $statusCode = $resolved['status_code'] ?? ($typeCode ?: ($data['attendance_status'] ?? 'not_punched'));
            if ($statusCode === 'lwp') {
                $statusCode = 'absent';
            }
            $statusName = $resolved['status_name'] ?? ($statusCode === 'absent' ? 'Absent' : ucwords(str_replace('_', ' ', $statusCode)));
            $data['status_code'] = $statusCode;
            $data['status_name'] = $statusName;
            $data['attendance_status'] = $statusCode;
            $data['is_blocked'] = false;
            $data['is_punch_blocked'] = false;
            $data['can_punch_in'] = false;
            $data['can_punch_out'] = $hasPunchIn && ! $hasPunchOut;
            $data['next_action'] = $hasPunchIn ? (! $hasPunchOut ? 'punch_out' : 'completed') : 'none';
        }

        $attendanceDate = $this->localDate($rawDate);
        $data['attendance_date'] = $attendanceDate;

        $punchIn = $this->combineLocalDateAndTime($rawDate, $data['punch_in_time'] ?? null);
        $data['punch_in_time'] = $punchIn ? $punchIn->format('Y-m-d H:i:s') : null;
        $data['punch_in_time_formatted'] = $punchIn ? $punchIn->format('h:i A') : null;

        $punchOut = $this->combineLocalDateAndTime($rawDate, $data['punch_out_time'] ?? null);
        $data['punch_out_time'] = $punchOut ? $punchOut->format('Y-m-d H:i:s') : null;
        $data['punch_out_time_formatted'] = $punchOut ? $punchOut->format('h:i A') : null;

        $targetOutTime = $this->localTime($data['target_punch_out_time'] ?? null);
        if ($targetOutTime) {
            $targetOutStr = $attendanceDate . ' ' . $targetOutTime;
            $data['target_punch_out_time'] = $this->localDateTime($targetOutStr) ?: $targetOutStr;
            $data['target_punch_out_time_formatted'] = Carbon::parse($targetOutStr, AttendanceRuleResolverService::TIMEZONE)->format('h:i A');
        } else {
            $data['target_punch_out_time'] = null;
            $data['target_punch_out_time_formatted'] = null;
        }

        $reqMins = (int) ($policy->required_work_minutes ?? $data['required_work_minutes'] ?? 0);
        if ($reqMins <= 0 && $empForPolicy) {
            $policyObj = $this->resolver->getPolicyForEmployee($empForPolicy, $dateForPolicy);
            $reqMins = (int) ($policyObj->required_work_minutes ?? 0);
        }

        $workedMins = 0;
        if ($punchIn) {
            if ($punchOut) {
                $workedMins = max(0, $punchIn->diffInMinutes($punchOut));
            } else {
                $workedMins = max(0, $punchIn->diffInMinutes(Carbon::now(AttendanceRuleResolverService::TIMEZONE)));
            }
        }

        $remainingMins = max(0, $reqMins - $workedMins);
        $workProgressPercent = ($reqMins > 0) ? min(100, (int) round(($workedMins / $reqMins) * 100)) : 0;
        $workCompleted = ($workedMins >= $reqMins && $reqMins > 0);

        $hours = (int) ($reqMins / 60);
        $mins = $reqMins % 60;
        $reqDuration = "{$hours}h {$mins}m";

        $data['required_work_minutes'] = $reqMins;
        $data['requiredWorkMinutes'] = $reqMins;
        $data['required_work_duration'] = $reqDuration;
        $data['requiredWorkDuration'] = $reqDuration;
        $data['worked_minutes'] = $workedMins;
        $data['workedMinutes'] = $workedMins;
        $data['remaining_work_minutes'] = $remainingMins;
        $data['remainingWorkMinutes'] = $remainingMins;
        $data['remaining_work_seconds'] = $remainingMins * 60;
        $data['remainingWorkSeconds'] = $remainingMins * 60;
        $data['work_progress_percent'] = $workProgressPercent;
        $data['workProgressPercent'] = $workProgressPercent;
        $data['work_completed'] = $workCompleted;
        $data['workCompleted'] = $workCompleted;

        $policyObj = $policy ?: ($empForPolicy ? $this->resolver->getPolicyForEmployee($empForPolicy, $dateForPolicy) : null);
        $earlyOutHalfDayMins = (int) ($policyObj->early_out_half_day_minutes ?? 60);
        $missedPunchAfterMins = (int) ($policyObj->missed_punch_after_minutes ?? 60);
        $targetOutVal = $targetOutTime ?: $this->resolver->timeString($policyObj->shift_end_time ?? '18:00:00');
        $shiftEndCarbon = $targetOutVal ? Carbon::parse($attendanceDate . ' ' . $targetOutVal, AttendanceRuleResolverService::TIMEZONE) : null;
        $earlyOutCutoff = $shiftEndCarbon ? $shiftEndCarbon->copy()->subMinutes($earlyOutHalfDayMins)->format('H:i:s') : null;
        $missedPunchCutoff = $shiftEndCarbon ? $shiftEndCarbon->copy()->addMinutes($missedPunchAfterMins)->format('H:i:s') : null;

        $data['early_out_half_day_minutes'] = $earlyOutHalfDayMins;
        $data['earlyOutHalfDayMinutes'] = $earlyOutHalfDayMins;
        $data['missed_punch_after_minutes'] = $missedPunchAfterMins;
        $data['missedPunchAfterMinutes'] = $missedPunchAfterMins;
        $data['early_out_half_day_cutoff'] = $earlyOutCutoff;
        $data['earlyOutHalfDayCutoff'] = $earlyOutCutoff;
        $data['missed_punch_cutoff'] = $missedPunchCutoff;
        $data['missedPunchCutoff'] = $missedPunchCutoff;

        $isHalfDay = (bool) ($data['is_half_day'] ?? false) || strtolower((string) ($data['attendance_status'] ?? '')) === 'half_day';
        if ($isHalfDay) {
            $data['is_early_out'] = false;
            $data['isEarlyOut'] = false;
            $data['early_out_minutes'] = 0;
            $data['earlyOutMinutes'] = 0;
        }

        if ($punchOut && $earlyOutCutoff) {
            $pOutTimeStr = $punchOut->format('H:i:s');
            if ($pOutTimeStr < $earlyOutCutoff) {
                $pOutFmt = $punchOut->format('g:i A');
                $cutoffCarbon = Carbon::parse($attendanceDate . ' ' . $earlyOutCutoff, AttendanceRuleResolverService::TIMEZONE);
                $cutoffFmt = $cutoffCarbon->format('g:i A');
                $targetCarbon = $shiftEndCarbon;
                $targetFmt = $targetCarbon ? $targetCarbon->format('g:i A') : null;
                $earlyByMins = $targetCarbon ? max(0, $punchOut->diffInMinutes($targetCarbon)) : 0;

                $reasonText = "Half Day — Punch Out at {$pOutFmt}, which is before the half-day cutoff of {$cutoffFmt}.";
                $reasonPayload = [
                    'reason_code' => 'EARLY_PUNCH_OUT_HALF_DAY',
                    'reason' => $reasonText,
                    'punch_out_time' => $pOutTimeStr,
                    'punch_out_time_formatted' => $pOutFmt,
                    'target_punch_out_time' => $targetOutVal,
                    'target_punch_out_time_formatted' => $targetFmt,
                    'early_out_half_day_cutoff' => $earlyOutCutoff,
                    'early_out_half_day_cutoff_formatted' => $cutoffFmt,
                    'early_by_minutes' => $earlyByMins,
                ];

                $data['reason_code'] = 'EARLY_PUNCH_OUT_HALF_DAY';
                $data['reasonCode'] = 'EARLY_PUNCH_OUT_HALF_DAY';
                $data['reason'] = $reasonText;
                $data['half_day_reason'] = $reasonText;
                $data['halfDayReason'] = $reasonText;
                $data['early_by_minutes'] = $earlyByMins;
                $data['earlyByMinutes'] = $earlyByMins;
                $data['reason_payload'] = $reasonPayload;
                $data['reasonPayload'] = $reasonPayload;
            }
        }

        $fields = ['auto_blocked_at', 'unlocked_at', 'hr_approved_at', 'created_at', 'updated_at'];
        foreach ($fields as $field) {
            $dt = $this->localDateTime($data[$field] ?? null);
            $data[$field] = $dt;
            $data[$field . '_formatted'] = $dt ? Carbon::parse($dt)->format('h:i A') : null;
        }

        if (isset($data['work_logs']) && is_array($data['work_logs'])) {
            $data['work_logs'] = collect($data['work_logs'])
                ->map(function ($log) {
                    $logData = is_array($log) ? $log : $log->toArray();
                    $logData['work_date'] = $this->localDate($logData['work_date'] ?? null);
                    return $logData;
                })
                ->values()
                ->toArray();
        }

        return $data;
    }

    public function punchIn(int $userId, string $workMode, ?string $note, array $meta = []): array
    {
        return $this->attendanceService->processPunchIn($userId, $workMode, $note, $meta);
    }

    public function punchOut(int $userId, string $taskSummary, ?string $note, array $meta = [], $taskSummaryJson = null): array
    {
        return $this->attendanceService->processPunchOut($userId, $taskSummary, $note, $meta, null, true, $taskSummaryJson);
    }

    public function history(int $userId, array $filters = []): array
    {
        $query = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
            ->where('user_id', $userId)
            ->when($filters['date'] ?? null, fn($q, $date) => $q->whereDate('attendance_date', $date))
            ->when($filters['month'] ?? null, fn($q, $month) => $q->whereMonth('attendance_date', (int) $month))
            ->when($filters['year'] ?? null, fn($q, $year) => $q->whereYear('attendance_date', (int) $year));

        $paginator = $query->orderByDesc('attendance_date')->orderByDesc('id')->paginate((int) ($filters['per_page'] ?? 100));

        $records = collect($paginator->items())
            ->map(fn($item) => $this->formatAttendanceForApi($item))
            ->values();

        return [
            'records' => $records,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ];
    }
}
