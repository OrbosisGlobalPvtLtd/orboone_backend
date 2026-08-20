<?php

namespace App\Http\Controllers\Api\V1\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Services\HRMS\ProjectManagement\TechnicalLeadScopeS;
use App\Models\HRMS\ProjectManagement\TechnicalLeadAssignmentM;
use App\Models\HRMS\ProjectManagement\ProjectM;
use App\Models\HRMS\ProjectManagement\ProjectTaskM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceWorkLogM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TechnicalLeadApiC extends Controller
{
    public function __construct(private TechnicalLeadScopeS $scopeS)
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * GET /api/v1/technical-lead/dashboard
     */
    public function dashboard(Request $request)
    {
        $supervisedIds = $this->scopeS->getActiveSupervisedEmployeeIds();
        $today = Carbon::today()->format('Y-m-d');

        $attendanceToday = AttendanceM::whereIn('employee_id', $supervisedIds)
            ->whereDate('attendance_date', $today)
            ->get();

        $supervisedProjectIds = $this->scopeS->getSupervisedProjectIds();

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'supervised_developers_count' => count($supervisedIds),
                    'supervised_projects_count' => count($supervisedProjectIds),
                    'present_today' => $attendanceToday->where('status', 'present')->count(),
                    'wfh_today' => $attendanceToday->where('work_type', 'WFH')->count(),
                    'wfo_today' => $attendanceToday->where('work_type', 'WFO')->count(),
                    'late_today' => $attendanceToday->where('is_late', 1)->count(),
                    'absent_today' => $attendanceToday->where('status', 'absent')->count(),
                    'on_leave_today' => LeaveRequestM::whereIn('employee_id', $supervisedIds)
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->count(),
                    'work_reports_submitted_today' => AttendanceWorkLogM::whereIn('employee_id', $supervisedIds)
                        ->whereDate('work_date', $today)
                        ->count(),
                ],
            ]
        ]);
    }

    /**
     * GET /api/v1/technical-lead/developers
     */
    public function developers(Request $request)
    {
        $tlEmpId = $this->scopeS->getOwnEmployeeId();
        $assignments = TechnicalLeadAssignmentM::with([
            'employee.user',
            'employee.designation'
        ])->where('is_active', 1);

        if (!$this->scopeS->isSuperAdminOrGlobal() && $tlEmpId) {
            $assignments->where('technical_lead_employee_id', $tlEmpId);
        }

        return response()->json([
            'status' => 'success',
            'data' => $assignments->get()
        ]);
    }

    /**
     * GET /api/v1/technical-lead/attendance
     */
    public function attendance(Request $request)
    {
        $query = AttendanceM::with(['employee.user', 'employee.designation']);
        $this->scopeS->scopeAttendanceQuery($query);

        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate(20)
        ]);
    }

    /**
     * GET /api/v1/technical-lead/leave
     */
    public function leave(Request $request)
    {
        $query = LeaveRequestM::with(['employee.user', 'employee.designation', 'leaveType']);
        $this->scopeS->scopeLeaveQuery($query);

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate(20)
        ]);
    }

    /**
     * GET /api/v1/technical-lead/work-reports
     */
    public function workReports(Request $request)
    {
        $query = AttendanceWorkLogM::with(['employee.user', 'employee.designation']);
        $this->scopeS->scopeWorkReports($query);

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate(20)
        ]);
    }

    /**
     * GET /api/v1/technical-lead/projects
     */
    public function projects(Request $request)
    {
        $projectIds = $this->scopeS->getSupervisedProjectIds();
        $projects = ProjectM::with(['deliveryHead.user', 'tasks'])->whereIn('id', $projectIds)->get();

        return response()->json([
            'status' => 'success',
            'data' => $projects
        ]);
    }
}
