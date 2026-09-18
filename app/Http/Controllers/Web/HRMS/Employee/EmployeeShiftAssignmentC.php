<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeShiftTimingM;
use App\Services\HRMS\Employee\EmployeeShiftAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeShiftAssignmentC extends Controller
{
    private EmployeeShiftAssignmentService $shiftAssignmentService;

    public function __construct(EmployeeShiftAssignmentService $shiftAssignmentService)
    {
        $this->shiftAssignmentService = $shiftAssignmentService;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $departmentId = $request->input('department_id');
        $employeeId = $request->input('employee_id');

        $query = EmployeeM::query()
            ->select('employees_new.*')
            ->join('users', 'users.id', '=', 'employees_new.user_id')
            ->join('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
            ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
            ->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id')
            ->where('employees_new.employment_status', 'active')
            ->where(function ($q) {
                $q->where('employees_new.is_active', 1)
                  ->orWhereNull('employees_new.is_active');
            })
            ->where(function ($q) {
                $q->whereNull('employees_new.employee_stage')
                  ->orWhereNotIn('employees_new.employee_stage', ['exited', 'resigned']);
            })
            ->where('employee_profiles.is_profile_completed', 1)
            ->where('employee_profiles.profile_status', 'approved')
            ->with(['user', 'department', 'designation', 'currentShiftTiming.attendanceTime', 'profile']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('employees_new.employee_code', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('departments.name', 'like', "%{$search}%")
                    ->orWhere('designations.name', 'like', "%{$search}%");
            });
        }

        if (!empty($departmentId)) {
            $query->where('employees_new.department_id', $departmentId);
        }

        if (!empty($employeeId)) {
            $query->where('employees_new.id', $employeeId);
        }

        $employees = $query->orderBy('employees_new.id', 'desc')->paginate(15)->withQueryString();

        $allEmployeesList = EmployeeM::query()
            ->select('employees_new.*')
            ->join('users', 'users.id', '=', 'employees_new.user_id')
            ->join('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
            ->where('employees_new.employment_status', 'active')
            ->where('employee_profiles.is_profile_completed', 1)
            ->where('employee_profiles.profile_status', 'approved')
            ->with('user')
            ->orderBy('employees_new.id', 'desc')
            ->get();
        $attendanceTimes = AttendanceTimeM::where('is_active', 1)->orderBy('name')->get();
        $departments = DepartmentM::orderBy('name')->get();

        $defaultShift = AttendanceTimeM::where('is_default', 1)->first() ?? AttendanceTimeM::first();

        $allShiftAssignments = EmployeeShiftTimingM::whereHas('employee', function ($q) {
                $q->join('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
                  ->where('employees_new.employment_status', 'active')
                  ->where('employee_profiles.is_profile_completed', 1)
                  ->where('employee_profiles.profile_status', 'approved');
            })
            ->with(['employee.user', 'attendanceTime'])
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

        $data['is_active'] = $request->boolean('is_active', true);

        $result = $this->shiftAssignmentService->assignShift(
            (int) $data['employee_id'],
            $data,
            Auth::id() ?: 1
        );

        if (!empty($result['warning'])) {
            session()->flash('warning', $result['warning']);
        }

        return back()->with('status', 'Employee shift assignment created successfully.');
    }

    public function update(Request $request, $id)
    {
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

        $data['is_active'] = $request->boolean('is_active', true);

        $this->shiftAssignmentService->updateShiftAssignment((int) $id, $data, Auth::id() ?: 1);

        return back()->with('status', 'Employee shift assignment updated successfully.');
    }
}
