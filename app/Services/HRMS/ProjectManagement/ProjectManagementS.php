<?php

namespace App\Services\HRMS\ProjectManagement;

use App\Models\HRMS\ProjectManagement\ProjectM;
use App\Models\HRMS\ProjectManagement\ProjectTeamM;
use App\Models\HRMS\ProjectManagement\ProjectAssignmentM;
use App\Models\HRMS\ProjectManagement\ProjectTaskM;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectManagementS
{
    /**
     * Generate unique automatic project code.
     */
    public function generateProjectCode(): string
    {
        $year = date('Y');
        $lastProject = ProjectM::latest('id')->first();
        $nextId = $lastProject ? ($lastProject->id + 1) : 1;

        $code = 'PRJ-' . $year . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        while (ProjectM::where('project_code', $code)->exists()) {
            $nextId++;
            $code = 'PRJ-' . $year . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * Create a new project.
     */
    public function createProject(array $data): ProjectM
    {
        return DB::transaction(function () use ($data) {
            $code = !empty($data['project_code']) ? strtoupper(trim($data['project_code'])) : $this->generateProjectCode();

            $dhEmpId = !empty($data['delivery_head_employee_id']) ? (int) $data['delivery_head_employee_id'] : null;
            $dhCustomName = !empty($data['delivery_head_name']) ? trim($data['delivery_head_name']) : null;

            $project = ProjectM::create([
                'project_code' => $code,
                'name' => trim($data['name']),
                'client_name' => isset($data['client_name']) ? trim($data['client_name']) : null,
                'delivery_head_employee_id' => $dhEmpId,
                'delivery_head_name' => $dhCustomName,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'status' => $data['status'] ?? 'active',
                'description' => $data['description'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Assign Delivery Head if selected
            if (!empty($data['delivery_head_employee_id'])) {
                $this->assignEmployee([
                    'project_id' => $project->id,
                    'project_team_id' => null,
                    'employee_id' => $data['delivery_head_employee_id'],
                    'project_role' => 'delivery_head',
                    'assigned_at' => now(),
                ]);
            }

            return $project;
        });
    }

    /**
     * Create a project team.
     */
    public function createTeam(array $data): ProjectTeamM
    {
        return DB::transaction(function () use ($data) {
            $team = ProjectTeamM::create([
                'project_id' => $data['project_id'],
                'team_name' => trim($data['team_name']),
                'team_lead_employee_id' => $data['team_lead_employee_id'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? 1,
                'created_by' => Auth::id(),
            ]);

            // Assign Team Lead if selected
            if (!empty($data['team_lead_employee_id'])) {
                $this->assignEmployee([
                    'project_id' => $data['project_id'],
                    'project_team_id' => $team->id,
                    'employee_id' => $data['team_lead_employee_id'],
                    'project_role' => 'team_lead',
                    'assigned_at' => now(),
                ]);
            }

            return $team;
        });
    }

    /**
     * Assign employee to project / team.
     */
    public function assignEmployee(array $data): ProjectAssignmentM
    {
        return DB::transaction(function () use ($data) {
            // Check if active assignment exists for same project/team/role
            $existing = ProjectAssignmentM::where('project_id', $data['project_id'])
                ->where('employee_id', $data['employee_id'])
                ->where('is_active', 1)
                ->first();

            if ($existing) {
                // If updating team or role, update existing assignment
                $existing->update([
                    'project_team_id' => $data['project_team_id'] ?? $existing->project_team_id,
                    'project_role' => $data['project_role'] ?? $existing->project_role,
                ]);
                return $existing;
            }

            return ProjectAssignmentM::create([
                'project_id' => $data['project_id'],
                'project_team_id' => $data['project_team_id'] ?? null,
                'employee_id' => $data['employee_id'],
                'project_role' => $data['project_role'] ?? 'team_member',
                'assigned_at' => $data['assigned_at'] ?? now(),
                'is_active' => 1,
                'created_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Relieve employee from project.
     */
    public function relieveEmployee(int $assignmentId): bool
    {
        $assignment = ProjectAssignmentM::findOrFail($assignmentId);
        return $assignment->update([
            'is_active' => 0,
            'relieved_at' => now(),
        ]);
    }

    /**
     * Reassign Team Lead for a team safely preserving audit logs.
     */
    public function reassignTeamLead(int $teamId, int $newLeadEmployeeId): ProjectTeamM
    {
        return DB::transaction(function () use ($teamId, $newLeadEmployeeId) {
            $team = ProjectTeamM::findOrFail($teamId);
            $oldLeadId = $team->team_lead_employee_id;

            if ($oldLeadId && $oldLeadId != $newLeadEmployeeId) {
                // Deactivate old team lead assignment
                ProjectAssignmentM::where('project_team_id', $teamId)
                    ->where('employee_id', $oldLeadId)
                    ->where('project_role', 'team_lead')
                    ->where('is_active', 1)
                    ->update([
                        'is_active' => 0,
                        'relieved_at' => now(),
                    ]);
            }

            $team->update(['team_lead_employee_id' => $newLeadEmployeeId]);

            $this->assignEmployee([
                'project_id' => $team->project_id,
                'project_team_id' => $team->id,
                'employee_id' => $newLeadEmployeeId,
                'project_role' => 'team_lead',
                'assigned_at' => now(),
            ]);

            return $team;
        });
    }

    /**
     * Get hierarchy tree array for a project.
     */
    public function getProjectHierarchy(int $projectId): array
    {
        $project = ProjectM::with([
            'deliveryHead.user',
            'teams' => function($q) {
                $q->where('is_active', 1)->with([
                    'teamLead.user',
                    'activeAssignments.employee.user',
                    'activeAssignments.employee.designation'
                ]);
            },
            'activeAssignments' => function($q) {
                $q->whereNull('project_team_id')->with('employee.user', 'employee.designation');
            }
        ])->findOrFail($projectId);

        return [
            'project_id' => $project->id,
            'project_code' => $project->project_code,
            'project_name' => $project->name,
            'status' => $project->status,
            'delivery_head' => $project->deliveryHead ? [
                'id' => $project->deliveryHead->id,
                'name' => $project->deliveryHead->display_name,
                'code' => $project->deliveryHead->employee_code,
            ] : null,
            'teams' => $project->teams->map(function($team) {
                return [
                    'team_id' => $team->id,
                    'team_name' => $team->team_name,
                    'team_lead' => $team->teamLead ? [
                        'id' => $team->teamLead->id,
                        'name' => $team->teamLead->display_name,
                        'code' => $team->teamLead->employee_code,
                    ] : null,
                    'members' => $team->activeAssignments->map(function($assign) {
                        return [
                            'assignment_id' => $assign->id,
                            'employee_id' => $assign->employee_id,
                            'name' => optional($assign->employee)->display_name,
                            'code' => optional($assign->employee)->employee_code,
                            'role' => $assign->project_role,
                            'designation' => optional(optional($assign->employee)->designation)->name ?? 'N/A',
                        ];
                    }),
                ];
            }),
            'direct_members' => $project->activeAssignments->map(function($assign) {
                return [
                    'assignment_id' => $assign->id,
                    'employee_id' => $assign->employee_id,
                    'name' => optional($assign->employee)->display_name,
                    'code' => optional($assign->employee)->employee_code,
                    'role' => $assign->project_role,
                    'designation' => optional(optional($assign->employee)->designation)->name ?? 'N/A',
                ];
            }),
        ];
    }
}
