<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Services\HRMS\Attendance\AttendanceS;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AttendancesC extends Controller
{
    private AttendanceS $attendanceService;

    public function __construct(AttendanceS $attendanceService)
    {
        $this->middleware('auth');
        $this->attendanceService = $attendanceService;
    }

    private function baseQuery()
    {
        return Attendance::with([
            'user',
            'employee.department',
            'employee.designation',
            'attendanceType',
            'attendanceTime',
            'workLogs',
            'hrApprovedBy',
        ]);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })->orWhereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery->where('employee_code', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }

        if (!$request->filled('date') && !$request->filled('from_date') && !$request->filled('to_date')) {
            if ($request->filter === 'today') {
                $query->whereDate('attendance_date', Carbon::today()->toDateString());
            }

            if ($request->filter === 'yesterday') {
                $query->whereDate('attendance_date', Carbon::yesterday()->toDateString());
            }

            if ($request->filter === 'weekly') {
                $query->whereBetween('attendance_date', [
                    Carbon::now()->startOfWeek()->toDateString(),
                    Carbon::now()->endOfWeek()->toDateString(),
                ]);
            }

            if ($request->filter === 'monthly') {
                $query->whereBetween('attendance_date', [
                    Carbon::now()->startOfMonth()->toDateString(),
                    Carbon::now()->endOfMonth()->toDateString(),
                ]);
            }
        }

        if ($request->filled('attendance_type_id')) {
            $query->where('attendance_type_id', $request->attendance_type_id);
        }

        if ($request->filled('attendance_type_code')) {
            $query->whereHas('attendanceType', function ($typeQuery) use ($request) {
                $typeQuery->where('code', $request->attendance_type_code);
            });
        }

        if ($request->filled('work_mode')) {
            $query->where('work_mode', $request->work_mode);
        }

        if ($request->filled('flag')) {
            if ($request->flag === 'late') {
                $query->where('is_late', 1);
            }

            if ($request->flag === 'early_out') {
                $query->where('is_early_out', 1);
            }

            if ($request->flag === 'pending_hr') {
                $query->where('is_blocked', 1);
            }

            if ($request->flag === 'clear') {
                $query->where('is_late', 0)
                    ->where('is_early_out', 0)
                    ->where('is_blocked', 0);
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->ensureDefaultAttendanceSetup();

        $query = $this->applyFilters($this->baseQuery(), $request);

        $statsData = (clone $query)->get();

        $stats = [
            'total_late' => $statsData->where('is_late', true)->count(),
            'total_early_out' => $statsData->where('is_early_out', true)->count(),
            'total_minutes' => $statsData->sum('total_work_minutes'),
            'total_hours' => round($statsData->sum('total_work_minutes') / 60, 2),
            'total_blocked' => $statsData->where('is_blocked', true)->count(),
            'total_present' => $statsData->filter(fn ($item) => optional($item->attendanceType)->code === 'present')->count(),
            'total_absent' => $statsData->filter(fn ($item) => optional($item->attendanceType)->code === 'absent')->count(),
            'total_half_day' => $statsData->filter(fn ($item) => optional($item->attendanceType)->code === 'half_day')->count(),
            'total_pending_hr' => $statsData->filter(fn ($item) => optional($item->attendanceType)->code === 'pending_hr' || $item->is_blocked)->count(),
        ];

        $attendances = $query->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $employees = User::whereHas('employee')
            ->with('employee')
            ->orderBy('name')
            ->get();

        $attendanceTypes = AttendanceType::where('is_active', true)
            ->orderBy('name')
            ->get();

        $attendanceTimes = AttendanceTime::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('hrms.attendance.index', compact(
            'attendances',
            'employees',
            'attendanceTypes',
            'attendanceTimes',
            'stats'
        ));
    }

    public function print(Request $request)
    {
        $attendances = $this->applyFilters($this->baseQuery(), $request)
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();

        return view('hrms.attendance.attendances_print', compact('attendances'));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->applyFilters($this->baseQuery(), $request);

        if ($request->filled('month')) {
            $query->whereMonth('attendance_date', (int) $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('attendance_date', (int) $request->year);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($employeeQuery) use ($request) {
                $employeeQuery->where('department_id', $request->department_id);
            });
        }

        $attendances = $query
            ->orderByDesc('attendance_date')
            ->orderBy('punch_in_time', 'ASC')
            ->get();

        if ($attendances->isEmpty()) {
            return back()->with('error', 'No attendance records found for selected filters.');
        }

        $periodLabel = $this->reportPeriodLabel($request);

        $pdf = Pdf::loadView('hrms.attendance.attendance_pdf', [
            'attendances' => $attendances,
            'filters' => $request->query(),
            'periodLabel' => $periodLabel,
        ]);

        return $pdf->download('attendance_report_'.now()->format('Y_m_d_His').'.pdf');
    }

    public function store(Request $request)
    {
        $userId = auth()->id();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance || !$attendance->punch_in_time) {
            $request->validate([
                'work_mode' => 'required|in:wfo,wfh',
                'note' => 'nullable|string|max:1000',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'address' => 'nullable|string|max:2000',
            ]);

            $result = $this->attendanceService->processPunchIn(
                $userId,
                $request->work_mode,
                $request->note,
                [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'address' => $request->address,
                    'ip' => $request->ip(),
                    'device' => $request->userAgent(),
                ]
            );

            if (($result['status'] ?? null) === 'blocked') {
                return back()->with('error', $result['message'] ?? 'Punch in blocked.');
            }

            if (($result['status'] ?? null) === 'error') {
                return back()->with('error', $result['message'] ?? 'Unable to punch in.');
            }

            return back()->with('status', $result['message'] ?? 'Punch in successful.');
        }

        $request->validate([
            'task_summary' => 'required|string|min:5|max:5000',
            'note' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string|max:2000',
        ]);

        $result = $this->attendanceService->processPunchOut(
            $userId,
            $request->task_summary,
            $request->note,
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'ip' => $request->ip(),
                'device' => $request->userAgent(),
            ]
        );

        if (($result['status'] ?? null) === 'error') {
            return back()->with('error', $result['message'] ?? 'Unable to punch out.');
        }

        return back()->with('status', $result['message'] ?? 'Punch out successful.');
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:attendances,id',
            'hr_approval_note' => 'nullable|string|max:2000',
        ]);

        $presentType = AttendanceType::where('code', 'present')->first();
        $attendance = Attendance::findOrFail($request->id);

        $attendance->update([
            'attendance_type_id' => $presentType?->id ?? $attendance->attendance_type_id,
            'is_blocked' => false,
            'block_reason' => null,
            'hr_approved_by' => auth()->id(),
            'hr_approved_at' => now(),
            'hr_approval_note' => $request->hr_approval_note ?? 'Approved by HR/Admin.',
        ]);

        return back()->with('status', 'Attendance approved/unlocked successfully.');
    }

    public function adminPunchIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'time' => 'required',
            'work_mode' => 'required|in:wfo,wfh',
            'attendance_type_id' => 'nullable|exists:attendance_types,id',
            'note' => 'nullable|string|max:1000',
        ]);

        $customTime = Carbon::parse($request->time)->format('Y-m-d H:i:s');

        $result = $this->attendanceService->processAdminPunchIn(
            $request->user_id,
            $request->work_mode,
            $request->note ?? 'Admin Override',
            $customTime,
            $request->attendance_type_id
        );

        if (($result['status'] ?? null) === 'error') {
            return back()->with('error', $result['message'] ?? 'Admin punch in failed.');
        }

        return back()->with('status', $result['message'] ?? 'Admin punch in successful.');
    }

    public function adminPunchOut(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'time' => 'required',
            'task_summary' => 'required|string|min:5|max:5000',
            'note' => 'nullable|string|max:1000',
        ]);

        $customTime = Carbon::parse($request->time)->format('Y-m-d H:i:s');

        $result = $this->attendanceService->processAdminPunchOut(
            $request->user_id,
            $request->task_summary,
            $request->note ?? 'Admin Override',
            $customTime
        );

        if (($result['status'] ?? null) === 'error') {
            return back()->with('error', $result['message'] ?? 'Admin punch out failed.');
        }

        return back()->with('status', $result['message'] ?? 'Admin punch out successful.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:attendances,id',
            'attendance_type_id' => 'required|exists:attendance_types,id',
            'attendance_date' => 'nullable|date',
            'punch_in_time' => 'nullable',
            'punch_out_time' => 'nullable',
            'work_mode' => 'nullable|in:wfo,wfh',
            'note' => 'nullable|string|max:2000',
            'hr_approval_note' => 'nullable|string|max:2000',
        ]);

        $attendance = Attendance::findOrFail($request->id);

        $attendance->attendance_type_id = $request->attendance_type_id;

        if ($request->filled('attendance_date')) {
            $attendance->attendance_date = $request->attendance_date;
        }

        $attendance->punch_in_time = $request->filled('punch_in_time')
            ? Carbon::parse($request->punch_in_time)->format('H:i:s')
            : null;

        $attendance->punch_out_time = $request->filled('punch_out_time')
            ? Carbon::parse($request->punch_out_time)->format('H:i:s')
            : null;

        if ($request->filled('work_mode')) {
            $attendance->work_mode = $request->work_mode;
        }

        $attendance->punch_out_note = $request->note;
        $attendance->hr_approval_note = $request->hr_approval_note;
        $attendance->save();

        if ($attendance->punch_in_time && $attendance->punch_out_time) {
            $this->attendanceService->calculateWorkingHours($attendance);
        }

        $attendance->refresh();

        $type = AttendanceType::find($request->attendance_type_id);

        if ($type && $type->code === 'pending_hr') {
            $attendance->is_blocked = true;
            $attendance->block_reason = $attendance->block_reason ?: 'Marked pending HR by admin.';
        }

        if ($type && $type->code !== 'pending_hr' && $request->filled('hr_approval_note')) {
            $attendance->is_blocked = false;
            $attendance->hr_approved_by = auth()->id();
            $attendance->hr_approved_at = now();
        }

        $attendance->save();

        return back()->with('status', 'Attendance record updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        return back()->with('error', 'Attendance records are read-only and cannot be deleted.');
    }

    public function pendingApproval(Request $request)
    {
        $request->merge(['attendance_type_code' => 'pending_hr']);
        return $this->index($request);
    }

    public function daily(Request $request)
    {
        $this->ensureDefaultAttendanceSetup();

        $request->merge([
            'date' => $request->input('date', Carbon::today()->toDateString()),
        ]);

        $query = $this->applyFilters($this->baseQuery(), $request);

        $attendances = $query->orderBy('punch_in_time')
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        $employees = $this->attendanceEmployees();
        $attendanceTypes = $this->activeAttendanceTypes();

        return view('hrms.attendance.daily', [
            'attendances' => $attendances,
            'employees' => $employees,
            'attendanceTypes' => $attendanceTypes,
            'date' => $request->date,
        ]);
    }

    public function monthlyReport(Request $request)
    {
        $this->ensureDefaultAttendanceSetup();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $query = $this->baseQuery()
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($employeeQuery) use ($request) {
                $employeeQuery->where('department_id', $request->department_id);
            });
        }

        $records = $query->orderBy('attendance_date')->get();

        $summary = [
            'present' => $records->filter(fn ($item) => optional($item->attendanceType)->code === 'present')->count(),
            'absent' => $records->filter(fn ($item) => optional($item->attendanceType)->code === 'absent')->count(),
            'half_day' => $records->filter(fn ($item) => optional($item->attendanceType)->code === 'half_day')->count(),
            'leave' => $records->filter(fn ($item) => optional($item->attendanceType)->code === 'leave')->count(),
            'week_off' => $records->filter(fn ($item) => optional($item->attendanceType)->code === 'week_off')->count(),
            'pending_hr' => $records->filter(fn ($item) => optional($item->attendanceType)->code === 'pending_hr' || $item->is_blocked)->count(),
            'late' => $records->where('is_late', true)->count(),
            'early_out' => $records->where('is_early_out', true)->count(),
            'total_hours' => round($records->sum('total_work_minutes') / 60, 2),
        ];

        $employeeRows = $records->groupBy('employee_id')->map(function ($items) {
            $first = $items->first();

            return [
                'employee_name' => optional($first->user)->name ?? 'N/A',
                'employee_code' => optional($first->employee)->employee_code ?? 'N/A',
                'department_name' => optional(optional($first->employee)->department)->name ?? 'N/A',
                'present' => $items->filter(fn ($item) => optional($item->attendanceType)->code === 'present')->count(),
                'absent' => $items->filter(fn ($item) => optional($item->attendanceType)->code === 'absent')->count(),
                'half_day' => $items->filter(fn ($item) => optional($item->attendanceType)->code === 'half_day')->count(),
                'leave' => $items->filter(fn ($item) => optional($item->attendanceType)->code === 'leave')->count(),
                'week_off' => $items->filter(fn ($item) => optional($item->attendanceType)->code === 'week_off')->count(),
                'late' => $items->where('is_late', true)->count(),
                'early_out' => $items->where('is_early_out', true)->count(),
                'total_hours' => round($items->sum('total_work_minutes') / 60, 2),
            ];
        })->values();

        return view('hrms.attendance.monthly-report', [
            'month' => $month,
            'year' => $year,
            'summary' => $summary,
            'employeeRows' => $employeeRows,
            'employees' => $this->attendanceEmployees(),
            'departments' => $this->departmentsForFilter(),
        ]);
    }

    public function rules()
    {
        $this->ensureDefaultAttendanceSetup();

        $attendanceTimes = AttendanceTime::orderByDesc('is_default')->get();
        return view('hrms.attendance.rules', compact('attendanceTimes'));
    }

    public function updateRule(Request $request, AttendanceTime $attendanceTime)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'punch_allowed_from' => ['required', 'date_format:H:i'],
            'shift_start_time' => ['required', 'date_format:H:i'],
            'late_after_time' => ['required', 'date_format:H:i'],
            'half_day_after_time' => ['nullable', 'date_format:H:i'],
            'shift_end_time' => ['required', 'date_format:H:i'],
            'required_work_minutes' => ['required', 'integer', 'min:1', 'gte:half_day_min_minutes'],
            'half_day_min_minutes' => ['required', 'integer', 'min:1'],
            'lunch_break_minutes' => ['required', 'integer', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            AttendanceTime::where('id', '!=', $attendanceTime->id)->update(['is_default' => false]);
        }

        foreach (['punch_allowed_from', 'shift_start_time', 'late_after_time', 'half_day_after_time', 'shift_end_time'] as $field) {
            if (! empty($data[$field])) {
                $data[$field] = Carbon::parse($data[$field])->format('H:i:s');
            }
        }

        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');

        $attendanceTime->update($data);

        return back()->with('status', 'Attendance rule updated successfully.');
    }

    public function types()
    {
        $this->ensureDefaultAttendanceSetup();

        $attendanceTypes = AttendanceType::withCount('attendances')->orderBy('name')->get();
        return view('hrms.attendance.types', compact('attendanceTypes'));
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+$/', 'unique:attendance_types,code'],
            'is_paid' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        AttendanceType::create([
            'name' => $data['name'],
            'code' => strtolower($data['code']),
            'is_paid' => $request->boolean('is_paid'),
            'color' => $data['color'] ?? '#64748b',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Attendance type created successfully.');
    }

    public function updateType(Request $request, AttendanceType $attendanceType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('attendance_types', 'code')->ignore($attendanceType->id),
            ],
            'is_paid' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $attendanceType->update([
            'name' => $data['name'],
            'code' => strtolower($data['code']),
            'is_paid' => $request->boolean('is_paid'),
            'color' => $data['color'] ?? '#64748b',
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Attendance type updated successfully.');
    }

    public function destroyType(AttendanceType $attendanceType)
    {
        $systemCodes = ['present', 'absent', 'half_day', 'leave', 'holiday', 'week_off', 'pending_hr'];
        $linkedAttendances = $attendanceType->attendances()->count();

        if (in_array($attendanceType->code, $systemCodes, true) || $linkedAttendances > 0) {
            $attendanceType->update(['is_active' => false]);

            return back()->with('status', 'Attendance type is in use, so it was deactivated instead of deleted.');
        }

        $attendanceType->delete();

        return back()->with('status', 'Attendance type deleted successfully.');
    }

    private function attendanceEmployees()
    {
        return User::whereHas('employee')
            ->with('employee.department')
            ->orderBy('name')
            ->get();
    }

    private function activeAttendanceTypes()
    {
        return AttendanceType::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function departmentsForFilter()
    {
        if (! Schema::hasTable('departments')) {
            return collect();
        }

        return DB::table('departments')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    private function ensureDefaultAttendanceSetup(): void
    {
        $types = [
            ['name' => 'Present', 'code' => 'present', 'is_paid' => true, 'color' => '#16a34a'],
            ['name' => 'Absent', 'code' => 'absent', 'is_paid' => false, 'color' => '#dc2626'],
            ['name' => 'Half Day', 'code' => 'half_day', 'is_paid' => true, 'color' => '#f59e0b'],
            ['name' => 'Leave', 'code' => 'leave', 'is_paid' => true, 'color' => '#2563eb'],
            ['name' => 'Holiday', 'code' => 'holiday', 'is_paid' => true, 'color' => '#7c3aed'],
            ['name' => 'Week Off', 'code' => 'week_off', 'is_paid' => true, 'color' => '#64748b'],
            ['name' => 'Pending HR Approval', 'code' => 'pending_hr', 'is_paid' => false, 'color' => '#ea580c'],
        ];

        foreach ($types as $type) {
            AttendanceType::firstOrCreate(
                ['code' => $type['code']],
                $type + ['is_active' => true]
            );
        }

        AttendanceTime::firstOrCreate(
            ['code' => 'general_shift'],
            [
                'name' => 'General Shift',
                'punch_allowed_from' => '09:00:00',
                'shift_start_time' => '10:00:00',
                'late_after_time' => '11:15:00',
                'half_day_after_time' => '14:00:00',
                'shift_end_time' => '19:00:00',
                'required_work_minutes' => 480,
                'half_day_min_minutes' => 240,
                'lunch_break_minutes' => 60,
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }

    private function reportPeriodLabel(Request $request): string
    {
        if ($request->filled('date')) {
            return Carbon::parse($request->date)->format('d M Y');
        }

        if ($request->filled('month') || $request->filled('year')) {
            $month = (int) $request->input('month', now()->month);
            $year = (int) $request->input('year', now()->year);

            return Carbon::create($year, $month, 1)->format('F Y');
        }

        if ($request->filled('from_date') || $request->filled('to_date')) {
            return trim(($request->from_date ?: 'Start').' to '.($request->to_date ?: 'Today'));
        }

        return 'All Records';
    }
}
