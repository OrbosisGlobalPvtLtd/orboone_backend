<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\Attendance\AttendanceWorkLogM as WorkLog;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use Illuminate\Http\Request;

class ProjectTeamWorkReportC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private ProjectAccessScopeS $accessScope)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('projects.team_work_reports.view')
            || $this->userHasPermission('attendance.work_reports.view_all')
            || $this->userHasPermission('projects.team_lead.view')
            || $this->userHasPermission('projects.delivery_head.view'),
            403
        );

        $projectId = $request->filled('project_id') ? (int) $request->project_id : null;

        $query = WorkLog::with([
            'user',
            'employee.department',
            'employee.designation',
            'attendance'
        ]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if (!$this->accessScope->isSuperAdminOrGlobal()) {
            $memberIds = $this->accessScope->getTeamMemberEmployeeIdsForLead(null, $projectId);
            $query->whereIn('employee_id', $memberIds);
        }

        if ($request->filled('from')) {
            $query->whereDate('work_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('work_date', '<=', $request->to);
        }

        $workLogs = $query->orderByDesc('work_date')->orderByDesc('id')->paginate(25)->appends($request->query());
        $projects = \App\Models\HRMS\ProjectManagement\ProjectM::where('status', 'active')->orderBy('name')->get();

        return view('hrms.projects.team.work-reports', compact('workLogs', 'projectId', 'projects'));
    }
}
