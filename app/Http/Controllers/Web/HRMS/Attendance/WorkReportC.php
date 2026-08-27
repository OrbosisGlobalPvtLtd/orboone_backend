<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\AttendanceWorkLogM as WorkLog;
use Illuminate\Http\Request;

class WorkReportC extends Controller
{
    use HrmsCrudPage;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.work_reports.view_all')
            || $this->userHasPermission('attendance.work_reports.view_team')
            || $this->userHasPermission('attendance.work_reports.view_own'),
            403
        );

        $query = WorkLog::with([
            'user',
            'employee.department',
            'employee.designation',
            'attendance.attendanceTime'
        ]);

        // Role-based scoping of employee visibility
        $allPermission = 'attendance.work_reports.view_all';
        $teamPermission = 'attendance.work_reports.view_team';
        
        $query = $this->scopeEmployeeVisibility($query, $allPermission, $teamPermission, 'employee_id');

        // Retrieve all scoping-restricted work logs for full client-side search/filter
        $workLogs = $query->orderByDesc('work_date')
            ->orderByDesc('id')
            ->get();

        // Get employees dropdown depending on role visibility
        $employees = $this->attendanceEmployees();

        // Check if admin / manager
        $isAdminOrManager = $this->userHasPermission('attendance.work_reports.view_all') 
            || $this->userHasPermission('attendance.work_reports.view_team');

        return view('hrms.attendance.work-reports', compact('workLogs', 'employees', 'isAdminOrManager'));
    }

    private function attendanceEmployees()
    {
        $query = User::whereHas('employee')->with('employee')->orderBy('name');
        if (! $this->canViewAll('attendance.work_reports.view_all')) {
            $ids = $this->userHasPermission('attendance.work_reports.view_team')
                ? $this->teamEmployeeIds(true)
                : array_filter([$this->ownEmployeeId()]);
            $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery->whereIn('id', $ids));
        }

        return $query->get();
    }
}
