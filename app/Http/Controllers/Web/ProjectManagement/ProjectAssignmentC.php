<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\ProjectManagement\ProjectAssignmentM;
use App\Services\HRMS\ProjectManagement\ProjectManagementS;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use Illuminate\Http\Request;

class ProjectAssignmentC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private ProjectManagementS $projectService,
        private ProjectAccessScopeS $accessScope
    ) {
        $this->middleware('auth');
    }

    public function assign(Request $request, $projectId)
    {
        abort_unless($this->accessScope->canAccessProject((int) $projectId), 403);

        $validated = $request->validate([
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees_new,id',
            'employee_id' => 'nullable|exists:employees_new,id',
            'project_team_id' => 'nullable|exists:project_teams,id',
            'project_role' => 'required|in:delivery_head,team_lead,team_member',
        ]);

        $rawIds = $request->input('employee_ids', $request->input('employee_id'));
        $employeeIds = is_array($rawIds) ? $rawIds : array_filter([$rawIds]);

        if (empty($employeeIds)) {
            return back()->withErrors(['employee_ids' => 'Please select at least one employee.']);
        }

        $assignedCount = 0;
        foreach ($employeeIds as $empId) {
            if (!$empId) continue;
            $this->projectService->assignEmployee([
                'project_id' => $projectId,
                'project_team_id' => $validated['project_team_id'] ?? null,
                'employee_id' => $empId,
                'project_role' => $validated['project_role'],
                'assigned_at' => now(),
            ]);
            $assignedCount++;
        }

        return back()->with('success', "{$assignedCount} employee(s) assigned to project successfully.");
    }

    public function relieve($id)
    {
        $assignment = ProjectAssignmentM::findOrFail($id);
        abort_unless($this->accessScope->canAccessProject((int) $assignment->project_id), 403);

        $this->projectService->relieveEmployee((int) $id);

        return back()->with('success', 'Employee relieved from project assignment.');
    }
}
