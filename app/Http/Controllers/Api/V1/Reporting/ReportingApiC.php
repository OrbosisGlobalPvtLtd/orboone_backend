<?php

namespace App\Http\Controllers\Api\V1\Reporting;

use App\Http\Controllers\Controller;
use App\Models\EmployeeM;
use App\Services\HRMS\Reporting\ReportingScopeS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingApiC extends Controller
{
    protected ReportingScopeS $scopeS;

    public function __construct(ReportingScopeS $scopeS)
    {
        $this->scopeS = $scopeS;
    }

    /**
     * Mobile API Dashboard
     */
    public function dashboard(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        if (!$supervisorEmpId) {
            return response()->json(['status' => 'error', 'message' => 'Authenticated user is not linked to an active employee record.'], 403);
        }

        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);
        $today = date('Y-m-d');

        $attendances = DB::table('attendances')
            ->whereIn('employee_id', $supervisedEmpIds)
            ->whereDate('attendance_date', $today)
            ->get();

        $leaves = DB::table('leave_requests')
            ->whereIn('employee_id', $supervisedEmpIds)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 'approved')
            ->get();

        $workReports = DB::table('attendance_work_logs')
            ->whereIn('employee_id', $supervisedEmpIds)
            ->whereDate('work_date', $today)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'supervised_employees_count' => count($supervisedEmpIds),
                'present_today_count' => $attendances->whereIn('status', ['present', 'late', 'half_day'])->count(),
                'wfh_today_count' => $attendances->where('work_type', 'wfh')->count(),
                'on_leave_today_count' => $leaves->count(),
                'work_reports_submitted_today' => $workReports->count(),
            ]
        ]);
    }

    /**
     * Mobile API My Reporting Employees
     */
    public function myEmployees(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        if (!$supervisorEmpId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized employee access.'], 403);
        }

        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);
        $employees = EmployeeM::with(['designation', 'department'])
            ->whereIn('id', $supervisedEmpIds)
            ->get();

        return response()->json(['status' => 'success', 'data' => $employees]);
    }

    /**
     * Mobile API Attendance
     */
    public function attendance(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        if (!$supervisorEmpId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        $date = $request->date ?? date('Y-m-d');
        $query = DB::table('attendances')->whereDate('attendance_date', $date);
        $query = $this->scopeS->scopeAttendanceQuery($query, $supervisorEmpId);

        return response()->json(['status' => 'success', 'data' => $query->get()]);
    }

    /**
     * Mobile API Leave
     */
    public function leave(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        if (!$supervisorEmpId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        $query = DB::table('leave_requests');
        $query = $this->scopeS->scopeLeaveQuery($query, $supervisorEmpId);

        return response()->json(['status' => 'success', 'data' => $query->orderByDesc('id')->get()]);
    }

    /**
     * Mobile API Daily Work Reports
     */
    public function workReports(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        if (!$supervisorEmpId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        $query = DB::table('attendance_work_logs');
        $query = $this->scopeS->scopeWorkReports($query, $supervisorEmpId);

        return response()->json(['status' => 'success', 'data' => $query->orderByDesc('work_date')->get()]);
    }

    /**
     * Mobile API Projects
     */
    public function projects(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        if (!$supervisorEmpId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);
        $projects = DB::table('project_assignments')
            ->join('projects', 'projects.id', '=', 'project_assignments.project_id')
            ->whereIn('project_assignments.employee_id', $supervisedEmpIds)
            ->where('project_assignments.is_active', 1)
            ->select('projects.*')
            ->distinct()
            ->get();

        return response()->json(['status' => 'success', 'data' => $projects]);
    }
}
