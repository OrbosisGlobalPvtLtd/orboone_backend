<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceTime;
use App\Models\AttendanceType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendancesController extends Controller
{
    private $attendanceService;

    public function __construct(\App\Services\AttendanceService $attendanceService)
    {
        $this->middleware('auth');
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $query = Attendance::with(['user.employee', 'user.employee.employeeDetail']);

        // 1. Employee Filter / Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        // 2. Date Filters
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filter == 'weekly') {
            $query->whereBetween('date', [
                Carbon::now()->startOfWeek()->format('Y-m-d'),
                Carbon::now()->endOfWeek()->format('Y-m-d')
            ]);
        } elseif ($request->filter == 'monthly') {
            $query->whereMonth('date', Carbon::now()->month)
                  ->whereYear('date', Carbon::now()->year);
        }

        // Get Summary Stats before pagination
        $statsData = $query->get();
        $stats = [
            'total_late' => $statsData->where('is_late', true)->count(),
            'total_early_out' => $statsData->where('is_early_out', true)->count(),
            'total_hours' => $statsData->sum(function($item) {
                return is_numeric($item->working_hours) ? $item->working_hours : 0;
            }),
            'total_blocked' => $statsData->where('is_blocked', true)->count(),
        ];

        $attendances = $query->orderBy('date', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate(15);

        $employees = User::whereHas('employee')->get();

        return view('pages.attendances', compact('attendances', 'employees', 'stats'));
    }

    /**
     * Store Clock IN or Clock OUT
     */
    public function store(Request $request)
    {
        $userId = auth()->id();
        
        // Check if punching in or out
        $today = Carbon::today()->format('Y-m-d');
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            // Punch In
            $result = $this->attendanceService->processPunchIn($userId, $request->work_type ?? 'WFO', $request->note);
            if ($result['status'] == 'blocked') {
                return back()->with('error', $result['message']);
            }
            return back()->with('status', $result['message']);
        } else {
            // Punch Out
            $result = $this->attendanceService->processPunchOut($userId, $request->note);
            if ($result['status'] == 'error') {
                return back()->with('error', $result['message']);
            }
            return back()->with('status', $result['message']);
        }
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:attendances,id'
        ]);

        $attendance = Attendance::findOrFail($request->id);
        $attendance->update([
            'is_blocked' => false,
            'manual_unlock_by' => auth()->id()
        ]);

        return back()->with('status', 'Attendance punch unlocked successfully.');
    }

    public function adminPunchIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'time' => 'required',
            'work_type' => 'required',
            'status' => 'required'
        ]);

        // We assume time is today's date + the submitted time string
        $customTime = Carbon::parse($request->time)->format('Y-m-d H:i:s');

        $result = $this->attendanceService->processAdminPunchIn(
            $request->user_id, 
            $request->work_type, 
            'Admin Override', 
            $customTime, 
            $request->status
        );

        if ($result['status'] == 'error') {
            return back()->with('error', $result['message']);
        }

        return back()->with('status', $result['message']);
    }

    public function adminPunchOut(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'time' => 'required'
        ]);

        $customTime = Carbon::parse($request->time)->format('Y-m-d H:i:s');

        $result = $this->attendanceService->processAdminPunchOut(
            $request->user_id, 
            'Admin Override', 
            $customTime
        );

        if ($result['status'] == 'error') {
            return back()->with('error', $result['message']);
        }

        return back()->with('status', $result['message']);
    }

    // --- API METHODS ---

    public function apiPunchIn(Request $request)
    {
        $userId = auth()->id();
        $result = $this->attendanceService->processPunchIn($userId, $request->work_type ?? 'WFO', $request->note);
        
        return response()->json($result, $result['status'] == 'blocked' ? 403 : 200);
    }

    public function apiPunchOut(Request $request)
    {
        $userId = auth()->id();
        $result = $this->attendanceService->processPunchOut($userId, $request->note);
        
        return response()->json($result, $result['status'] == 'error' ? 400 : 200);
    }

    public function apiLogs(Request $request)
    {
        $userId = auth()->id();
        $logs = Attendance::where('user_id', $userId)
            ->orderBy('date', 'DESC')
            ->paginate(20);

        return response()->json($logs);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:attendances,id',
            'status' => 'required',
        ]);

        $attendance = Attendance::findOrFail($request->id);

        if ($request->filled('clock_in')) {
            $attendance->clock_in = Carbon::parse($request->clock_in)->format('h:i A');
        } else {
            $attendance->clock_in = null;
        }

        if ($request->filled('clock_out')) {
            $attendance->clock_out = Carbon::parse($request->clock_out)->format('h:i A');
        } else {
            $attendance->clock_out = null;
        }

        $attendance->save();

        // Calculate hours based on new times (this will temporarily overwrite status)
        if ($attendance->clock_in && $attendance->clock_out) {
            $this->attendanceService->calculateWorkingHours($attendance);
        }

        // Forcefully re-apply the admin's requested fields
        $attendance->status = $request->status;
        $attendance->note = $request->note;
        if (strtolower($request->status) == 'blocked') {
            $attendance->is_blocked = true;
        }
        $attendance->save();

        return back()->with('status', 'Attendance record updated successfully.');
    }

    public function print(Request $request)
    {
        // Reuse logic from index if needed, for simplicity fetching all
        $attendances = Attendance::with(['user.employee'])->orderBy('date', 'DESC')->get();
        return view('pages.attendances_print', compact('attendances'));
    }

    /**
     * Delete an attendance record
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return back()->with('status', 'Attendance record deleted successfully.');
    }

    /**
     * Export daily attendance to PDF
     */
    public function exportPdf(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        
        $attendances = Attendance::with(['user.employee', 'user.employee.department'])
            ->whereDate('date', $date)
            ->orderBy('clock_in', 'ASC')
            ->get();

        if ($attendances->isEmpty()) {
            return back()->with('error', 'No attendance records found for ' . Carbon::parse($date)->format('d M, Y'));
        }

        $pdf = Pdf::loadView('pages.attendance_pdf', [
            'attendances' => $attendances,
            'date' => $date
        ]);

        return $pdf->download('attendance_report_' . $date . '.pdf');
    }
}
