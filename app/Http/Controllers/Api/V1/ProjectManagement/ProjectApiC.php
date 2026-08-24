<?php

namespace App\Http\Controllers\Api\V1\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\HRMS\ProjectManagement\ProjectM;
use App\Models\HRMS\ProjectManagement\ProjectTaskM;
use App\Models\HRMS\ProjectManagement\WorkReportTemplateM;
use App\Models\HRMS\Attendance\AttendanceWorkLogM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use App\Services\HRMS\ProjectManagement\ProjectManagementS;
use App\Services\HRMS\ProjectManagement\WorkReportTemplateS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectApiC extends Controller
{
    public function __construct(
        private ProjectAccessScopeS $accessScope,
        private ProjectManagementS $projectService,
        private WorkReportTemplateS $templateService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function myProjects()
    {
        $employeeId = $this->accessScope->getOwnEmployeeId();
        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
        }

        $projectIds = $this->accessScope->getEmployeeProjectIds($employeeId);
        $projects = ProjectM::with(['deliveryHead.user', 'teams.teamLead.user'])
            ->whereIn('id', $projectIds)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $projects->map(function ($p) {
                return [
                    'id' => $p->id,
                    'project_code' => $p->project_code,
                    'name' => $p->name,
                    'client_name' => $p->client_name,
                    'status' => $p->status,
                    'progress_percentage' => $p->progress_percentage,
                    'delivery_head_name' => optional(optional($p->deliveryHead)->user)->name ?? 'N/A',
                ];
            }),
        ]);
    }

    public function show($id)
    {
        abort_unless($this->accessScope->canAccessProject((int) $id), 403, 'Unauthorized access to project.');

        $project = ProjectM::with(['deliveryHead.user', 'teams.teamLead.user'])->findOrFail($id);
        $hierarchy = $this->projectService->getProjectHierarchy((int) $id);

        return response()->json([
            'success' => true,
            'data' => [
                'project' => $project,
                'hierarchy' => $hierarchy,
            ],
        ]);
    }

    public function tasks($id)
    {
        abort_unless($this->accessScope->canAccessProject((int) $id), 403, 'Unauthorized access to project tasks.');

        $tasks = ProjectTaskM::with(['assignedEmployee.user'])
            ->where('project_id', $id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    public function getWorkReportTemplate(Request $request, $id)
    {
        abort_unless($this->accessScope->canAccessProject((int) $id), 403, 'Unauthorized access to project.');

        $roleType = $request->input('role_type', 'developer');
        $template = $this->templateService->getTemplateForRole($roleType, (int) $id);

        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    public function submitWorkReport(Request $request)
    {
        $validated = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'project_id' => 'required|exists:projects,id',
            'project_task_id' => 'nullable|exists:project_tasks,id',
            'work_summary' => 'required|string',
            'work_summary_json' => 'nullable|array',
            'duration_minutes' => 'nullable|integer',
            'task_type' => 'nullable|string',
        ]);

        abort_unless($this->accessScope->canAccessProject((int) $validated['project_id']), 403, 'Unauthorized project.');

        $attendance = AttendanceM::findOrFail($validated['attendance_id']);
        $employeeId = $this->accessScope->getOwnEmployeeId();

        $workLog = AttendanceWorkLogM::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employeeId,
            'user_id' => Auth::id(),
            'work_date' => $attendance->attendance_date,
            'work_summary' => $validated['work_summary'],
            'work_summary_json' => $validated['work_summary_json'] ?? null,
            'project_id' => $validated['project_id'],
            'project_task_id' => $validated['project_task_id'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? 0,
            'task_type' => $validated['task_type'] ?? 'Feature',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily work report submitted successfully.',
            'data' => $workLog,
        ]);
    }

    public function teamAttendance(Request $request, $id)
    {
        abort_unless($this->accessScope->canAccessProject((int) $id), 403, 'Unauthorized project.');

        $date = $request->input('date', now()->toDateString());
        $query = AttendanceM::with(['employee.user', 'employee.designation'])
            ->whereDate('attendance_date', $date);

        $query = $this->accessScope->scopeAttendanceQuery($query, (int) $id, $date, $date);
        $attendances = $query->get();

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }
}
