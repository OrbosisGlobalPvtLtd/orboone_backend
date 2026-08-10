<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeShiftTimingM;
use Illuminate\Http\Request;

class EmployeeShiftAssignmentC extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $departmentId = $request->input('department_id');
        $employeeId = $request->input('employee_id');

        $query = EmployeeM::with(['user', 'department', 'designation', 'currentShiftTiming.attendanceTime']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        if (!empty($employeeId)) {
            $query->where('id', $employeeId);
        }

        $employees = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $allEmployeesList = EmployeeM::with('user')->orderBy('id', 'desc')->get();
        $attendanceTimes = AttendanceTimeM::where('is_active', 1)->orderBy('name')->get();
        $departments = DepartmentM::orderBy('name')->get();

        $defaultShift = AttendanceTimeM::where('is_default', 1)->first() ?? AttendanceTimeM::first();

        $allShiftAssignments = EmployeeShiftTimingM::with(['employee.user', 'attendanceTime'])
            ->orderByDesc('id')
            ->get();

        return view('hrms.employee.shift_assignment.index', compact(
            'employees',
            'allEmployeesList',
            'attendanceTimes',
            'departments',
            'defaultShift',
            'allShiftAssignments'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'attendance_time_id' => 'required|exists:attendance_times,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'nullable|boolean',
            'punch_allowed_from' => 'nullable',
            'shift_start_time' => 'nullable',
            'late_after_time' => 'nullable',
            'block_after_time' => 'nullable',
            'half_day_after_time' => 'nullable',
            'shift_end_time' => 'nullable',
            'required_work_minutes' => 'nullable|integer',
            'lunch_minutes' => 'nullable|integer',
        ]);

        $isActive = $request->boolean('is_active', true);

        if ($isActive) {
            EmployeeShiftTimingM::where('employee_id', $data['employee_id'])
                ->where('is_active', 1)
                ->update(['is_active' => 0]);
        }

        $shiftTime = AttendanceTimeM::find($data['attendance_time_id']);

        $punchAllowedFrom = $request->filled('punch_allowed_from') ? $request->input('punch_allowed_from') : $shiftTime->punch_allowed_from;
        $shiftStartTime   = $request->filled('shift_start_time') ? $request->input('shift_start_time') : $shiftTime->shift_start_time;
        $lateAfterTime    = $request->filled('late_after_time') ? $request->input('late_after_time') : $shiftTime->late_after_time;
        $blockAfterTime   = $request->filled('block_after_time') ? $request->input('block_after_time') : ($shiftTime->block_after_time ?? $shiftTime->half_day_after_time ?? $shiftTime->shift_end_time);
        $halfDayAfterTime = $request->filled('half_day_after_time') ? $request->input('half_day_after_time') : $shiftTime->half_day_after_time;
        $shiftEndTime     = $request->filled('shift_end_time') ? $request->input('shift_end_time') : $shiftTime->shift_end_time;
        $requiredMinutes  = $request->filled('required_work_minutes') ? (int) $request->input('required_work_minutes') : $shiftTime->required_work_minutes;
        $lunchMinutes     = $request->filled('lunch_minutes') ? (int) $request->input('lunch_minutes') : ($shiftTime->lunch_break_minutes ?? 0);

        EmployeeShiftTimingM::create([
            'employee_id' => $data['employee_id'],
            'attendance_time_id' => $data['attendance_time_id'],
            'punch_allowed_from' => $punchAllowedFrom,
            'shift_start_time' => $shiftStartTime,
            'late_after_time' => $lateAfterTime,
            'block_after_time' => $blockAfterTime,
            'half_day_after_time' => $halfDayAfterTime,
            'shift_end_time' => $shiftEndTime,
            'required_work_minutes' => $requiredMinutes,
            'lunch_minutes' => $lunchMinutes,
            'effective_from' => $data['effective_from'],
            'effective_to' => $data['effective_to'] ?? null,
            'is_active' => $isActive,
        ]);

        return back()->with('status', 'Employee shift assignment created successfully.');
    }

    public function update(Request $request, $id)
    {
        $assignment = EmployeeShiftTimingM::findOrFail($id);

        $data = $request->validate([
            'attendance_time_id' => 'required|exists:attendance_times,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'nullable|boolean',
            'punch_allowed_from' => 'nullable',
            'shift_start_time' => 'nullable',
            'late_after_time' => 'nullable',
            'block_after_time' => 'nullable',
            'half_day_after_time' => 'nullable',
            'shift_end_time' => 'nullable',
            'required_work_minutes' => 'nullable|integer',
            'lunch_minutes' => 'nullable|integer',
        ]);

        $isActive = $request->boolean('is_active', true);

        if ($isActive && !$assignment->is_active) {
            EmployeeShiftTimingM::where('employee_id', $assignment->employee_id)
                ->where('id', '!=', $assignment->id)
                ->where('is_active', 1)
                ->update(['is_active' => 0]);
        }

        $shiftTime = AttendanceTimeM::find($data['attendance_time_id']);

        $punchAllowedFrom = $request->filled('punch_allowed_from') ? $request->input('punch_allowed_from') : $shiftTime->punch_allowed_from;
        $shiftStartTime   = $request->filled('shift_start_time') ? $request->input('shift_start_time') : $shiftTime->shift_start_time;
        $lateAfterTime    = $request->filled('late_after_time') ? $request->input('late_after_time') : $shiftTime->late_after_time;
        $blockAfterTime   = $request->filled('block_after_time') ? $request->input('block_after_time') : ($shiftTime->block_after_time ?? $shiftTime->half_day_after_time ?? $shiftTime->shift_end_time);
        $halfDayAfterTime = $request->filled('half_day_after_time') ? $request->input('half_day_after_time') : $shiftTime->half_day_after_time;
        $shiftEndTime     = $request->filled('shift_end_time') ? $request->input('shift_end_time') : $shiftTime->shift_end_time;
        $requiredMinutes  = $request->filled('required_work_minutes') ? (int) $request->input('required_work_minutes') : $shiftTime->required_work_minutes;
        $lunchMinutes     = $request->filled('lunch_minutes') ? (int) $request->input('lunch_minutes') : ($shiftTime->lunch_break_minutes ?? 0);

        $assignment->update([
            'attendance_time_id' => $data['attendance_time_id'],
            'punch_allowed_from' => $punchAllowedFrom,
            'shift_start_time' => $shiftStartTime,
            'late_after_time' => $lateAfterTime,
            'block_after_time' => $blockAfterTime,
            'half_day_after_time' => $halfDayAfterTime,
            'shift_end_time' => $shiftEndTime,
            'required_work_minutes' => $requiredMinutes,
            'lunch_minutes' => $lunchMinutes,
            'effective_from' => $data['effective_from'],
            'effective_to' => $data['effective_to'] ?? null,
            'is_active' => $isActive,
        ]);

        return back()->with('status', 'Employee shift assignment updated successfully.');
    }
}
