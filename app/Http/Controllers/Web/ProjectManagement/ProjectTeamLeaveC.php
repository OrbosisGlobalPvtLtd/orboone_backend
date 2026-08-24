<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\Leave\LeaveRequestM as LeaveRequest;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use Illuminate\Http\Request;

class ProjectTeamLeaveC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private ProjectAccessScopeS $accessScope)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('projects.team_leave.view')
            || $this->userHasPermission('leave.approvals.view_all')
            || $this->userHasPermission('projects.team_lead.view')
            || $this->userHasPermission('projects.delivery_head.view'),
            403
        );

        $projectId = $request->filled('project_id') ? (int) $request->project_id : null;

        $query = LeaveRequest::with(['employee.user', 'employee.designation', 'leaveType', 'dates']);

        if (!$this->accessScope->isSuperAdminOrGlobal()) {
            $memberIds = $this->accessScope->getTeamMemberEmployeeIdsForLead(null, $projectId);
            $query->whereIn('employee_id', $memberIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByDesc('start_date')->paginate(25)->appends($request->query());

        return view('hrms.projects.team.leave', compact('requests', 'projectId'));
    }
}
