<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\ProjectManagement\ProjectTeamM;
use App\Services\HRMS\ProjectManagement\ProjectManagementS;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use Illuminate\Http\Request;

class ProjectTeamC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private ProjectManagementS $projectService,
        private ProjectAccessScopeS $accessScope
    ) {
        $this->middleware('auth');
    }

    public function store(Request $request, $projectId)
    {
        abort_unless($this->accessScope->canAccessProject((int) $projectId), 403);

        $validated = $request->validate([
            'team_name' => 'required|string|max:100',
            'team_lead_employee_id' => 'nullable|exists:employees_new,id',
            'description' => 'nullable|string',
        ]);

        $this->projectService->createTeam([
            'project_id' => $projectId,
            'team_name' => $validated['team_name'],
            'team_lead_employee_id' => $validated['team_lead_employee_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Project team created successfully.');
    }

    public function update(Request $request, $id)
    {
        $team = ProjectTeamM::findOrFail($id);
        abort_unless($this->accessScope->canAccessProject((int) $team->project_id), 403);

        $validated = $request->validate([
            'team_name' => 'required|string|max:100',
            'team_lead_employee_id' => 'nullable|exists:employees_new,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['team_lead_employee_id']) && $validated['team_lead_employee_id'] != $team->team_lead_employee_id) {
            $this->projectService->reassignTeamLead($team->id, (int) $validated['team_lead_employee_id']);
        }

        $team->update($validated);

        return back()->with('success', 'Project team updated successfully.');
    }
}
