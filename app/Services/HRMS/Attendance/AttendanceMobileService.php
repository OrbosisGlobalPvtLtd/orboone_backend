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
    ) {
    }

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
        $payload['is_blocked'] = (bool) ($attendanceData['is_blocked'] ?? $payload['ui']['is_blocked'] ?? false);
        $payload['is_punch_blocked'] = (bool) ($attendanceData['is_punch_blocked'] ?? $payload['ui']['is_punch_blocked'] ?? false);
        $payload['can_punch_in'] = (bool) ($attendanceData['can_punch_in'] ?? $payload['ui']['can_punch_in'] ?? false);
        $payload['can_punch_out'] = (bool) ($attendanceData['can_punch_out'] ?? $payload['ui']['can_punch_out'] ?? false);
        $payload['next_action'] = $attendanceData['next_action'] ?? ($payload['ui']['next_action'] ?? 'none');
        $payload['office_location'] = $this->attendanceService->officeLocationPayload();

        $wfhApproved = false;
        if ($employee && $this->wfhRequestService) {
            $wfhApproved = (bool) $this->wfhRequestService->approvedForDate((int) $employee->id, Carbon::now(AttendanceRuleResolverService::TIMEZONE)->toDateString());
        }

        return [
            'success' => true,
            'status' => true,
            'message' => 'Today attendance status fetched successfully.',
            'data' => $payload + ['wfh_approved_today' => $wfhApproved],
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
        $isUnlocked = (bool) ($data['is_admin_unlocked'] ?? false) || ($data['attendance_status'] ?? null) === 'unlocked';
        $isBlocked = (bool) (
            ($data['is_blocked'] ?? false)
            || ($data['is_punch_blocked'] ?? false)
            || $typeCode === 'punch_blocked'
        );
        if ($isUnlocked) {
            $isBlocked = false;
        }
        $hasPunchIn = ! empty($data['punch_in_time']);
        $hasPunchOut = ! empty($data['punch_out_time']);

        $resolved = $this->attendanceService->resolveFinalStatus(is_array($attendance) ? (new Attendance($data)) : $attendance);
        $statusCode = $resolved['status_code'] ?? ($typeCode ?: ($data['attendance_status'] ?? 'not_punched'));
        $statusName = $resolved['status_name'] ?? ($data['attendance_type']['name'] ?? ucwords(str_replace('_', ' ', $statusCode)));
        if ($isBlocked) {
            $statusCode = 'punch_blocked';
            $statusName = 'Punch Blocked';
        } elseif ($isUnlocked && ! $hasPunchIn) {
            $statusCode = 'awaiting_punch_in';
            $statusName = 'Awaiting Punch In';
            if (isset($data['attendance_type'])) {
                $data['attendance_type']['code'] = 'awaiting_punch_in';
                $data['attendance_type']['name'] = 'Awaiting Punch In';
            }
        }

        $data['status_code'] = $statusCode;
        $data['status_name'] = $statusName;
        $data['is_blocked'] = $isBlocked;
        $data['is_punch_blocked'] = $isBlocked;
        $data['can_punch_in'] = ! $isBlocked && ! $hasPunchIn;
        $data['can_punch_out'] = ! $isBlocked && $hasPunchIn && ! $hasPunchOut;
        $data['next_action'] = $isBlocked ? 'blocked' : (! $hasPunchIn ? 'punch_in' : (! $hasPunchOut ? 'punch_out' : 'completed'));

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
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', $date))
            ->when($filters['month'] ?? null, fn ($q, $month) => $q->whereMonth('attendance_date', (int) $month))
            ->when($filters['year'] ?? null, fn ($q, $year) => $q->whereYear('attendance_date', (int) $year));

        $paginator = $query->orderByDesc('attendance_date')->orderByDesc('id')->paginate((int) ($filters['per_page'] ?? 100));
        
        $records = collect($paginator->items())
            ->map(fn ($item) => $this->formatAttendanceForApi($item))
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
