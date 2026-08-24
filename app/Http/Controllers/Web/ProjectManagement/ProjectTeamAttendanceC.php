<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProjectTeamAttendanceC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private ProjectAccessScopeS $accessScope)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('projects.team_attendance.view')
            || $this->userHasPermission('attendance.records.view_all')
            || $this->userHasPermission('projects.team_lead.view')
            || $this->userHasPermission('projects.delivery_head.view'),
            403
        );

        $now = Carbon::now('Asia/Kolkata');
        $date = $request->input('date', $now->toDateString());
        $projectId = $request->filled('project_id') ? (int) $request->project_id : null;

        $query = Attendance::with(['employee.user', 'employee.designation', 'attendanceType'])
            ->whereDate('attendance_date', $date);

        $query = $this->accessScope->scopeAttendanceQuery($query, $projectId, $date, $date);

        if ($request->filled('work_mode')) {
            $query->where('work_mode', $request->work_mode);
        }

        $attendances = $query->orderBy('id', 'desc')->paginate(25)->appends($request->query());
        $accessibleProjects = $this->accessScope->getAccessibleProjectIds();

        return view('hrms.projects.team.attendance', compact('attendances', 'date', 'projectId'));
    }
}
