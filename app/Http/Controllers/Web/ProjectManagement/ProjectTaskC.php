<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\ProjectManagement\ProjectM;
use App\Models\HRMS\ProjectManagement\ProjectTaskM;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use App\Services\HRMS\Team\TeamManagementScopeS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectTaskC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private ProjectAccessScopeS $accessScope)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $accessibleProjectIds = $this->accessScope->getAccessibleProjectIds();

        $query = ProjectTaskM::with(['project', 'team', 'assignedEmployee.user']);

        if (!$this->accessScope->isSuperAdminOrGlobal()) {
            $query->whereIn('project_id', $accessibleProjectIds);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->orderByDesc('id')->paginate(20);
        
        $employees = $this->getScopedEmployeeOptions();

        $projectsQuery = ProjectM::where('status', 'active');
        if (!$this->isGlobalAdmin()) {
            $projectsQuery->whereIn('id', $accessibleProjectIds);
        }
        $projects = $projectsQuery->get();

        return view('hrms.projects.tasks.index', compact('tasks', 'employees', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_team_id' => 'nullable|exists:project_teams,id',
            'assigned_employee_id' => 'nullable|exists:employees_new,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type' => 'nullable|string|max:50',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,blocked,completed,cancelled',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        abort_unless($this->accessScope->canAccessProject((int) $validated['project_id']), 403);

        if (!$this->isGlobalAdmin() && !empty($validated['assigned_employee_id'])) {
            $allowedEmpIds = $this->getAllowedTeamEmployeeIds();
            abort_unless(in_array((int)$validated['assigned_employee_id'], $allowedEmpIds, true), 403);
        }

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
            $validated['progress_percentage'] = 100;
        }

        ProjectTaskM::create(array_merge($validated, [
            'created_by' => Auth::id(),
        ]));

        return back()->with('success', 'Project task created successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $task = ProjectTaskM::findOrFail($id);
        abort_unless($this->accessScope->canAccessProject((int) $task->project_id), 403);

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,blocked,completed,cancelled',
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
            $validated['progress_percentage'] = 100;
        }

        $task->update(array_merge($validated, ['updated_by' => Auth::id()]));

        return back()->with('success', 'Task status updated successfully.');
    }

    private function isGlobalAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return $this->accessScope->isSuperAdminOrGlobal()
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || in_array(($user->system_role_id ?? 0), [1, 2, 3], true);
    }

    private function getAllowedTeamEmployeeIds(): array
    {
        $teamScope = app(TeamManagementScopeS::class);
        $teamEmpIds = $teamScope->getTeamEmployeeIds();
        $ownEmpId = $this->ownEmployeeId();
        if ($ownEmpId) {
            $teamEmpIds[] = (int) $ownEmpId;
        }
        return array_values(array_unique(array_filter($teamEmpIds)));
    }

    private function getScopedEmployeeOptions()
    {
        if ($this->isGlobalAdmin()) {
            return $this->employeeOptions();
        }

        $allowedEmpIds = $this->getAllowedTeamEmployeeIds();

        return DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->whereIn('employees_new.id', $allowedEmpIds)
            ->select(
                'employees_new.id',
                'employees_new.employee_code',
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name")
            )
            ->orderByRaw("COALESCE(users.name, employees_new.employee_code)")
            ->get();
    }
}
