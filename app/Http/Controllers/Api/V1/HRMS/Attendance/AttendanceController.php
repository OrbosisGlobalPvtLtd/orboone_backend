<?php

namespace App\Http\Controllers\Api\V1\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Services\HRMS\Attendance\AttendanceMobileService;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceS $attendanceService,
        private AttendanceMobileService $mobileService
    )
    {
    }

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
            isset($result['data']) ? $this->formatAttendanceRecord($result['data']) : null,
            ($result['status'] ?? null) === 'error' ? 422 : 200
        );
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'task_summary' => ['required_without:task_details', 'string', 'min:5', 'max:5000'],
            'task_details' => ['nullable', 'string', 'min:5', 'max:5000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $result = $this->mobileService->punchOut(
            auth()->id(),
            $request->task_summary ?: $request->task_details,
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
            $result['message'] ?? 'Punch out processed.',
            isset($result['data']) ? $this->formatAttendanceRecord($result['data']) : null,
            ($result['status'] ?? null) === 'error' ? 422 : 200
        );
    }

    public function getAttendance(Request $request)
    {
        $query = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
            ->where('user_id', auth()->id())
            ->when($request->filled('date'), fn ($query) => $query->whereDate('attendance_date', $request->date))
            ->when($request->filled('month'), fn ($query) => $query->whereMonth('attendance_date', (int) $request->month))
            ->when($request->filled('year'), fn ($query) => $query->whereYear('attendance_date', (int) $request->year));

        $summaryRecords = (clone $query)->get();

        $attendance = $query
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 100));

        $records = collect($attendance->items())
            ->map(fn ($item) => $this->formatAttendanceRecord($item))
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
        ]);
    }

    public function unlock(Request $request)
    {
        if (! $this->canManageAttendance()) {
            return $this->apiResponse(false, 'Only Admin/HR can unlock attendance.', null, 403);
        }

        $request->validate([
            'attendance_id' => ['required', 'exists:attendances,id'],
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
            isset($result['data']) ? $this->formatAttendanceRecord($result['data']) : null,
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
            ->map(fn ($item) => $this->formatAttendanceRecord($item))
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
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('attendance_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('attendance_date', '<=', $request->to_date))
            ->orderByDesc('attendance_date')
            ->paginate((int) $request->input('per_page', 20));

        $records->setCollection(
            $records->getCollection()
                ->map(fn ($item) => $this->formatAttendanceRecord($item))
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
        $code = fn ($item) => strtolower((string) optional($item->attendanceType)->code);

        return [
            'present' => $records->filter(fn ($item) => $code($item) === 'present')->count(),
            'absent' => $records->filter(fn ($item) => $code($item) === 'absent')->count(),
            'half_day' => $records->filter(fn ($item) => $code($item) === 'half_day')->count(),
            'leave' => $records->filter(fn ($item) => $code($item) === 'leave')->count(),
            'week_off' => $records->filter(fn ($item) => $code($item) === 'week_off')->count(),
            'holiday' => $records->filter(fn ($item) => $code($item) === 'holiday')->count(),
            'pending_hr' => $records->filter(fn ($item) => $code($item) === 'pending_hr')->count(),
            'punch_blocked' => $records->filter(fn ($item) => $code($item) === 'punch_blocked' || $item->is_blocked || $item->is_punch_blocked)->count(),
            'late' => $records->where('is_late', true)->count(),
            'early_out' => $records->where('is_early_out', true)->count(),
            'lwp' => $records->filter(fn ($item) => $code($item) === 'lwp' || $item->is_lwp)->count(),
            'missed_punch' => $records->where('missed_punch', true)->count(),
            'total_work_minutes' => (int) $records->sum('total_work_minutes'),
            'total_work_hours' => round(((int) $records->sum('total_work_minutes')) / 60, 2),
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
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }
}
