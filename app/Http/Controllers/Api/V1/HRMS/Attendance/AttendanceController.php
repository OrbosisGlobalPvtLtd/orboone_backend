<?php

namespace App\Http\Controllers\Api\V1\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceS $attendanceService)
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

        $result = $this->attendanceService->processPunchIn(
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

        $result = $this->attendanceService->processPunchOut(
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
        $today = Carbon::now(config('app.timezone', 'Asia/Kolkata'))->toDateString();

        $attendance = Attendance::with(['attendanceType', 'attendanceTime', 'workLogs'])
            ->where('user_id', auth()->id())
            ->whereDate('attendance_date', $today)
            ->first();

        return $this->apiResponse(
            true,
            $attendance ? 'Today attendance fetched successfully.' : 'No attendance recorded for today.',
            $attendance ? $this->formatAttendanceRecord($attendance) : null
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

        $data = $attendance->toArray();

        $data['attendance_date'] = $attendance->attendance_date
            ? Carbon::parse($attendance->attendance_date)->format('Y-m-d')
            : null;

        if (isset($data['work_logs']) && is_array($data['work_logs'])) {
            $data['work_logs'] = collect($attendance->workLogs ?? [])
                ->map(function ($log) {
                    $logData = $log->toArray();

                    $logData['work_date'] = $log->work_date
                        ? Carbon::parse($log->work_date)->format('Y-m-d')
                        : null;

                    return $logData;
                })
                ->values()
                ->toArray();
        }

        return $data;
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
            'pending_hr' => $records->filter(fn ($item) => $code($item) === 'pending_hr' || $item->is_blocked)->count(),
            'late' => $records->where('is_late', true)->count(),
            'early_out' => $records->where('is_early_out', true)->count(),
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
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }
}