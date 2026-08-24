<?php

namespace App\Http\Controllers\Api\V1\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Services\HRMS\Attendance\AttendanceMobileService;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceS $attendanceService,
        private AttendanceMobileService $mobileService
    ) {}

    public function clockIn(Request $request)
    {
        $request->validate([
            'work_mode' => ['required_without:work_type', 'in:wfo,wfh,WFO,WFH'],
            'work_type' => ['nullable', 'in:wfo,wfh,WFO,WFH'],
            'note' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $workMode = strtolower($request->input('work_mode', $request->input('work_type', 'wfo')));

        if ($workMode === 'wfo' && (! $request->filled('latitude') || ! $request->filled('longitude'))) {
            return $this->apiResponse(false, 'Location is required for WFO punch in.', null, 422, [
                'latitude' => ['Latitude is required for WFO punch in.'],
                'longitude' => ['Longitude is required for WFO punch in.'],
            ]);
        }

        $result = $this->mobileService->punchIn(
            auth()->id(),
            $workMode,
            $request->note,
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'ip' => $request->ip(),
                'device' => $request->userAgent(),
            ]
        );

        return $this->apiResponse(
            ($result['status'] ?? null) !== 'error',
            $result['message'] ?? 'Punch in processed.',
            isset($result['data'])
                ? (($result['status'] ?? null) === 'error' ? $result['data'] : $this->formatAttendanceRecord($result['data']))
                : null,
            ($result['status'] ?? null) === 'error' ? 422 : 200,
            $result['errors'] ?? (($result['status'] ?? null) === 'error' ? ($result['data'] ?? null) : null)
        );
    }

    public function clockOut(Request $request)
    {
        if (! $request->filled('task_summary') && empty($request->projects)) {
            return $this->apiResponse(false, 'The task summary field is required.', null, 422, [
                'task_summary' => ['The task summary field is required.'],
            ]);
        }

        if ($request->filled('task_summary') && strlen((string) $request->task_summary) < 5 && empty($request->projects)) {
            return $this->apiResponse(false, 'The task summary must be at least 5 characters.', null, 422, [
                'task_summary' => ['The task summary must be at least 5 characters.'],
            ]);
        }

        $request->validate([
            'task_summary' => ['nullable', 'string', 'max:10000'],
            'task_summary_json' => ['nullable', 'array'],
            'projects' => ['nullable', 'array'],
            'projects.*.project_id' => ['nullable'],
            'projects.*.custom_project_name' => ['nullable', 'string'],
            'projects.*.tasks' => ['nullable', 'array'],
            'today_work_status' => ['nullable', 'string'],
            'issues_blockers' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $scopeS = app(\App\Services\HRMS\ProjectManagement\ProjectAccessScopeS::class);
        $accessibleProjectIds = $scopeS->getAccessibleProjectIds();

        $rawProjects = $request->projects ?? ($request->task_summary_json['projects'] ?? []);
        $todayWorkStatus = strtolower($request->today_work_status ?? $request->task_summary_json['today_work_status'] ?? $request->current_status ?? 'in_progress');
        $issuesBlockers = $request->issues_blockers ?? ($request->task_summary_json['issues_blockers'] ?? null);
        $remarks = $request->remarks ?? $request->note ?? ($request->task_summary_json['remarks'] ?? $request->task_summary_json['additional_notes'] ?? null);

        $projectsPayload = [];
        $summaryTextLines = [];

        if (is_array($rawProjects) && count($rawProjects) > 0) {
            foreach ($rawProjects as $pIdx => $pBlock) {
                $projIdInput = $pBlock['project_id'] ?? null;
                $projId = is_numeric($projIdInput) ? (int) $projIdInput : null;
                $isCustom = $projIdInput === 'custom' || ! empty($pBlock['is_custom']);
                $customName = $isCustom ? trim($pBlock['custom_project_name'] ?? '') : null;

                if ($projId && ! in_array($projId, $accessibleProjectIds, true) && ! $scopeS->isSuperAdminOrGlobal()) {
                    return $this->apiResponse(false, "You are not authorized to submit daily work reports for selected project ID: {$projId}.", null, 422, [
                        'projects' => ["Unauthorized project selection: {$projId}"],
                    ]);
                }

                $projObj = $projId ? DB::table('projects')->where('id', $projId)->first() : null;
                $projectName = $projObj ? $projObj->name : ($customName ?: 'Custom / Other Work');

                $tasksPayload = [];
                $taskLines = [];

                if (isset($pBlock['tasks']) && is_array($pBlock['tasks'])) {
                    foreach ($pBlock['tasks'] as $tItem) {
                        $tName = trim($tItem['task_name'] ?? $tItem['description'] ?? '');
                        if (empty($tName)) {
                            continue;
                        }

                        $isCompleted = ! empty($tItem['is_completed']) || ! empty($tItem['completed']);
                        $isCompleted = ($isCompleted == 1 || $isCompleted === '1' || $isCompleted === true || $isCompleted === 'true');

                        $tasksPayload[] = [
                            'description' => $tName,
                            'task_name' => $tName,
                            'completed' => $isCompleted,
                            'is_completed' => $isCompleted,
                        ];

                        $icon = $isCompleted ? '☑' : '☐';
                        $taskLines[] = "  {$icon} {$tName}";
                    }
                }

                if (! empty($tasksPayload)) {
                    $projectsPayload[] = [
                        'project_id' => $projId,
                        'project_name' => $projectName,
                        'is_custom' => $isCustom,
                        'custom_project_name' => $customName,
                        'tasks' => $tasksPayload,
                    ];

                    $summaryTextLines[] = "Project: {$projectName}\n" . implode("\n", $taskLines);
                }
            }
        }

        if (! empty($projectsPayload)) {
            $taskSummaryText = implode("\n\n", $summaryTextLines);
            $taskSummaryText .= "\n\nToday's Work Status: " . ucfirst(str_replace('_', ' ', $todayWorkStatus));

            if (! empty($issuesBlockers)) {
                $taskSummaryText .= "\n\nIssues / Blockers:\n" . trim($issuesBlockers);
            }
            if (! empty($remarks)) {
                $taskSummaryText .= "\n\nAdditional Notes:\n" . trim($remarks);
            }

            $taskSummaryJson = [
                'projects' => $projectsPayload,
                'today_work_status' => $todayWorkStatus,
                'current_status' => $todayWorkStatus,
                'issues_blockers' => $issuesBlockers,
                'remarks' => $remarks,
                'additional_notes' => $remarks,
                'task_name' => $projectsPayload[0]['project_name'] ?? 'Daily Work',
                'today_work_description' => $taskSummaryText,
            ];
        } else {
            $taskSummaryText = $request->task_summary ?? 'Daily Work Update Completed';
            $taskSummaryJson = $request->task_summary_json;
        }

        $result = $this->mobileService->punchOut(
            auth()->id(),
            $taskSummaryText,
            $remarks,
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'ip' => $request->ip(),
                'device' => $request->userAgent(),
            ],
            $taskSummaryJson
        );

        return $this->apiResponse(
            ($result['status'] ?? null) !== 'error',
            $result['message'] ?? 'Punch out processed.',
            isset($result['data'])
                ? (($result['status'] ?? null) === 'error' ? $result['data'] : $this->formatAttendanceRecord($result['data']))
                : null,
            ($result['status'] ?? null) === 'error' ? 422 : 200,
            $result['errors'] ?? (($result['status'] ?? null) === 'error' ? $result['data'] : null)
        );
    }

    public function getAttendance(Request $request)
    {
        $query = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
            ->where('user_id', auth()->id())
            ->when($request->filled('date'), fn($query) => $query->whereDate('attendance_date', $request->date))
            ->when($request->filled('month'), fn($query) => $query->whereMonth('attendance_date', (int) $request->month))
            ->when($request->filled('year'), fn($query) => $query->whereYear('attendance_date', (int) $request->year));

        $summaryRecords = (clone $query)->get();

        $attendance = $query
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 100));

        $records = collect($attendance->items())
            ->map(fn($item) => $this->formatAttendanceRecord($item))
            ->values();

        return $this->apiResponse(true, 'Attendance records fetched successfully.', [
            'records' => $records,
            'summary' => $this->summaryFromRecords($summaryRecords),
            'pagination' => [
                'total' => $attendance->total(),
                'per_page' => $attendance->perPage(),
                'current_page' => $attendance->currentPage(),
                'last_page' => $attendance->lastPage(),
                'next_page_url' => $attendance->nextPageUrl(),
                'prev_page_url' => $attendance->previousPageUrl(),
            ],
        ]);
    }

    public function today()
    {
        return $this->todayStatus();
    }

    public function todayStatus()
    {
        $result = $this->mobileService->todayStatus(auth()->id());

        return $this->apiResponse($result['status'], $result['message'], $result['data'], $result['status'] ? 200 : 404);
    }

    public function todayContext()
    {
        $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            return $this->apiResponse(false, 'Employee profile not found.', null, 404);
        }

        $contextResolver = app(\App\Services\HRMS\Attendance\AttendanceContextResolverService::class);
        $data = $contextResolver->resolveContext($employee);

        return $this->apiResponse(true, 'Attendance context fetched successfully.', $data);
    }

    public function profileStatus()
    {
        $result = $this->mobileService->profileStatus(auth()->id());

        return $this->apiResponse($result['status'], $result['message'], $result['data'], $result['status'] ? 200 : 404);
    }

    public function history(Request $request)
    {
        $data = $this->mobileService->history(auth()->id(), $request->only(['date', 'month', 'year', 'per_page']));

        return $this->apiResponse(true, 'Attendance history fetched successfully.', $data);
    }

    public function monthly(Request $request)
    {
        $request->merge([
            'month' => $request->input('month', Carbon::now($this->attendanceService->attendanceTimezone())->month),
            'year' => $request->input('year', Carbon::now($this->attendanceService->attendanceTimezone())->year),
        ]);

        return $this->getAttendance($request);
    }

    public function rules()
    {
        $shift = $this->attendanceService->defaultShift();

        return $this->apiResponse(true, 'Attendance rules fetched successfully.', [
            'timezone' => $this->attendanceService->attendanceTimezone(),
            'shift' => $shift,
            'punch_window' => [
                'allowed_from' => $shift?->punch_allowed_from,
                'early_login_from' => $shift?->early_login_from,
                'normal_login_from' => $shift?->normal_login_from,
                'late_after_time' => $shift?->late_after_time,
                'warning_after_time' => $shift?->warning_after_time,
                'block_after_time' => $shift?->block_after_time,
            ],
            'office_location' => $this->attendanceService->officeLocationPayload(),
        ]);
    }

    public function unlock(Request $request)
    {
        if (! $this->canManageAttendance()) {
            return $this->apiResponse(false, 'Only Admin/HR can unlock attendance.', null, 403);
        }

        $request->validate([
            'attendance_id' => ['required', 'string'],
            'unlock_type' => ['required', 'in:unlock_only,late_exemption,manual_punch_in'],
            'unlock_reason_category' => ['nullable', 'string', 'max:255'],
            'unlock_remarks' => ['nullable', 'string', 'max:2000'],
            'hr_approval_note' => ['nullable', 'string', 'max:2000'],
            'approved_punch_in_time' => ['required_if:unlock_type,manual_punch_in', 'nullable'],
        ]);

        $result = $this->attendanceService->unlockAttendance($request->attendance_id, auth()->id(), $request->only([
            'unlock_type',
            'unlock_reason_category',
            'unlock_remarks',
            'hr_approval_note',
            'approved_punch_in_time',
        ]));

        return $this->apiResponse(
            $result['success'],
            $result['message'],
            (isset($result['data']) && $result['data']) ? $this->formatAttendanceRecord($result['data']) : null,
            $result['success'] ? 200 : 422
        );
    }

    public function getMyAttendanceCalendar(Request $request)
    {
        $now = Carbon::now(config('app.timezone', 'Asia/Kolkata'));
        $month = (int) $request->input('month', $now->month);
        $year = (int) $request->input('year', $now->year);

        $records = Attendance::with('attendanceType')
            ->where('user_id', auth()->id())
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get()
            ->map(fn($item) => $this->formatAttendanceRecord($item))
            ->values();

        return $this->apiResponse(true, 'Attendance calendar fetched successfully.', $records);
    }

    public function manualAttendanceUpdate(Request $request)
    {
        if (! $this->canManageAttendance()) {
            return $this->apiResponse(false, 'Attendance records are read-only for employees.', null, 403);
        }

        $request->validate([
            'attendance_id' => ['required', 'exists:attendances,id'],
            'attendance_type_id' => ['nullable', 'exists:attendance_types,id'],
            'punch_in_time' => ['nullable'],
            'punch_out_time' => ['nullable'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);

        if ($request->filled('attendance_type_id')) {
            $attendance->attendance_type_id = $request->attendance_type_id;
        }

        if ($request->filled('punch_in_time')) {
            $attendance->punch_in_time = Carbon::parse($request->punch_in_time)->format('H:i:s');
        }

        if ($request->filled('punch_out_time')) {
            $attendance->punch_out_time = Carbon::parse($request->punch_out_time)->format('H:i:s');
        }

        $attendance->hr_approval_note = $request->note;
        $attendance->save();

        if ($attendance->punch_in_time && $attendance->punch_out_time) {
            $this->attendanceService->calculateWorkingHours($attendance);
        }

        return $this->apiResponse(
            true,
            'Attendance updated successfully.',
            $this->formatAttendanceRecord($attendance->fresh(['attendanceType', 'workLogs']))
        );
    }

    public function lateEarlyReport(Request $request)
    {
        $records = Attendance::with(['user', 'employee', 'attendanceType'])
            ->where(function ($query) {
                $query->where('is_late', true)
                    ->orWhere('is_early_out', true);
            })
            ->when($request->filled('from_date'), fn($query) => $query->whereDate('attendance_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($query) => $query->whereDate('attendance_date', '<=', $request->to_date))
            ->orderByDesc('attendance_date')
            ->paginate((int) $request->input('per_page', 20));

        $records->setCollection(
            $records->getCollection()
                ->map(fn($item) => $this->formatAttendanceRecord($item))
        );

        return $this->apiResponse(true, 'Late and early out report fetched successfully.', $records);
    }

    private function formatAttendanceRecord($attendance): ?array
    {
        if (! $attendance) {
            return null;
        }

        return $this->mobileService->formatAttendanceForApi($attendance);
    }



    private function summaryFromRecords($records): array
    {
        $code = fn($item) => strtolower((string) optional($item->attendanceType)->code);

        $user = auth()->user();
        $employee = $user ? \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->first() : null;
        $violationCycle = '0 / 3';
        $missedPunchCycle = '0 / 2';
        if ($employee && Schema::hasTable('attendance_violations')) {
            $year = Carbon::now()->year;
            $month = Carbon::now()->month;

            $qDisc = DB::table('attendance_violations')
                ->where('employee_id', $employee->id)
                ->whereIn('type', ['late_login', 'late_mark', 'early_logout', 'early_out'])
                ->whereYear('violation_date', $year)
                ->whereMonth('violation_date', $month)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'pending');
                });
            if (Schema::hasColumn('attendance_violations', 'is_consumed')) {
                $qDisc->where(function ($query) {
                    $query->whereNull('is_consumed')->orWhere('is_consumed', false);
                });
            }
            if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
                $qDisc->whereNull('deleted_at');
            }
            $countDisc = $qDisc->count();
            if ($countDisc > 0) {
                $posDisc = (($countDisc - 1) % 3) + 1;
                $violationCycle = "{$posDisc} / 3";
            }

            $qMissed = DB::table('attendance_violations')
                ->where('employee_id', $employee->id)
                ->whereIn('type', ['missed_punch'])
                ->whereYear('violation_date', $year)
                ->whereMonth('violation_date', $month)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'pending');
                });
            if (Schema::hasColumn('attendance_violations', 'is_consumed')) {
                $qMissed->where(function ($query) {
                    $query->whereNull('is_consumed')->orWhere('is_consumed', false);
                });
            }
            if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
                $qMissed->whereNull('deleted_at');
            }
            $countMissed = $qMissed->count();
            if ($countMissed > 0) {
                $posMissed = (($countMissed - 1) % 2) + 1;
                $missedPunchCycle = "{$posMissed} / 2";
            }
        }

        return [
            'present' => $records->filter(fn($item) => $code($item) === 'present')->count(),
            'absent' => $records->filter(fn($item) => $code($item) === 'absent' || $code($item) === 'lwp' || $item->is_lwp)->count(),
            'half_day' => $records->filter(fn($item) => $code($item) === 'half_day')->count(),
            'leave' => $records->filter(fn($item) => $code($item) === 'leave')->count(),
            'week_off' => $records->filter(fn($item) => $code($item) === 'week_off')->count(),
            'holiday' => $records->filter(fn($item) => $code($item) === 'holiday')->count(),
            'pending_hr' => $records->filter(fn($item) => (string) ($item->attendance_status ?? '') === 'pending_hr')->count(),
            'punch_blocked' => $records->filter(fn($item) => $item->is_blocked || $item->is_punch_blocked || $item->attendance_status === 'punch_blocked')->count(),
            'late' => $records->where('is_late', true)->count(),
            'early_out' => $records->where('is_early_out', true)->count(),
            'lwp' => 0,
            'missed_punch' => $records->where('missed_punch', true)->count(),
            'total_work_minutes' => (int) $records->sum('total_work_minutes'),
            'total_work_hours' => $violationCycle,
            'hours_this_month' => $violationCycle,
            'violation_cycle' => $violationCycle,
            'violations_count' => $violationCycle,
            'discipline_cycle' => $violationCycle,
            'missed_punch_cycle' => $missedPunchCycle,
        ];
    }

    private function canManageAttendance(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    private function apiResponse(bool $success, string $message, $data = null, int $status = 200, $errors = null)
    {
        return response()->json([
            'status' => $success,
            'success' => $success,
            'message' => app(\App\Services\Shared\MobileApiMessageS::class)->cleanMessage($message),
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }
}
