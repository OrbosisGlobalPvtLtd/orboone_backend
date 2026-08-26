<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\ProjectManagement\ProjectM;
use App\Models\HRMS\ProjectManagement\ProjectTaskM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\ProjectManagement\ProjectManagementS;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private ProjectManagementS $projectService,
        private ProjectAccessScopeS $accessScope
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $accessibleProjectIds = $this->accessScope->getAccessibleProjectIds();

        abort_unless(
            $this->userHasPermission('projects.view_all')
            || $this->userHasPermission('projects.my_projects.view')
            || $this->userHasPermission('projects.delivery_head.view')
            || $this->userHasPermission('projects.team_lead.view')
            || $this->accessScope->isProjectManagerOrLead()
            || !empty($accessibleProjectIds),
            403
        );

        $query = ProjectM::with(['deliveryHead.user', 'activeTeams.teamLead.user', 'activeAssignments']);

        if (!$this->accessScope->isSuperAdminOrGlobal()) {
            $query->whereIn('id', $accessibleProjectIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderByDesc('id')->paginate(15);
        $employees = $this->employeeOptions();
        $nextProjectCode = $this->projectService->generateProjectCode();

        return view('hrms.projects.index', compact('projects', 'employees', 'nextProjectCode'));
    }

    public function store(Request $request)
    {
        abort_unless(
            $this->userHasPermission('projects.create') 
            || $this->canViewAll('projects.manage') 
            || $this->accessScope->isProjectManagerOrLead(), 
            403
        );

        $validated = $request->validate([
            'project_code' => 'nullable|string|max:50',
            'name' => 'required|string|max:191',
            'client_name' => 'nullable|string|max:191',
            'delivery_head_employee_id' => 'nullable',
            'delivery_head_name' => 'nullable|string|max:191',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,on_hold,completed,archived',
            'description' => 'nullable|string',
        ]);

        $submittedCode = $request->input('project_code');
        if (empty($submittedCode) || ProjectM::where('project_code', $submittedCode)->exists()) {
            $validated['project_code'] = $this->projectService->generateProjectCode();
        } else {
            $validated['project_code'] = strtoupper(trim($submittedCode));
        }

        $empId = $request->input('delivery_head_employee_id');
        $customName = $request->input('delivery_head_name');

        if ($empId === 'custom' || (!empty(trim($customName ?? '')) && empty($empId))) {
            $validated['delivery_head_name'] = trim($customName);
            $validated['delivery_head_employee_id'] = null;
        } elseif (is_numeric($empId) && \App\Models\HRMS\Employee\EmployeeM::where('id', $empId)->exists()) {
            $validated['delivery_head_employee_id'] = (int) $empId;
            $validated['delivery_head_name'] = null;
        } else {
            $validated['delivery_head_employee_id'] = null;
            $validated['delivery_head_name'] = null;
        }

        $project = $this->projectService->createProject($validated);

        return redirect()->route('projects.show', $project->id)->with('success', 'Project created successfully.');
    }

    public function show($id)
    {
        abort_unless($this->accessScope->canAccessProject((int) $id), 403);

        $project = ProjectM::with([
            'deliveryHead.user',
            'teams.teamLead.user',
            'teams.activeAssignments.employee.user',
            'activeAssignments.employee.user',
            'activeAssignments.employee.designation',
            'tasks.assignedEmployee.user'
        ])->findOrFail($id);

        $hierarchy = $this->projectService->getProjectHierarchy((int) $id);
        $employees = $this->employeeOptions();
        $taskStats = [
            'total' => $project->tasks->where('status', '!=', 'cancelled')->count(),
            'completed' => $project->tasks->where('status', 'completed')->count(),
            'in_progress' => $project->tasks->where('status', 'in_progress')->count(),
            'blocked' => $project->tasks->where('status', 'blocked')->count(),
            'todo' => $project->tasks->where('status', 'todo')->count(),
            'progress_percentage' => $project->progress_percentage,
        ];

        $assignedEmployeeIds = $project->activeAssignments->pluck('employee_id')->all();

        $technicalSupervisors = DB::table('technical_lead_assignments')
            ->join('employees_new as tl', 'tl.id', '=', 'technical_lead_assignments.technical_lead_employee_id')
            ->join('employees_new as dev', 'dev.id', '=', 'technical_lead_assignments.employee_id')
            ->leftJoin('users as tlu', 'tlu.id', '=', 'tl.user_id')
            ->leftJoin('users as devu', 'devu.id', '=', 'dev.user_id')
            ->leftJoin('designations', 'designations.id', '=', 'tl.designation_id')
            ->whereIn('technical_lead_assignments.employee_id', $assignedEmployeeIds)
            ->where('technical_lead_assignments.is_active', 1)
            ->select(
                'tl.id as tl_id',
                DB::raw('COALESCE(tlu.name, tl.employee_code) as tl_name'),
                'tl.employee_code as tl_code',
                'designations.name as designation_name',
                DB::raw('COALESCE(devu.name, dev.employee_code) as dev_name')
            )
            ->get()
            ->groupBy('tl_id');

        $canManageProject = $this->accessScope->canManageProject((int) $id);

        return view('hrms.projects.show', compact('project', 'hierarchy', 'employees', 'taskStats', 'technicalSupervisors', 'canManageProject'));
    }

    public function update(Request $request, $id)
    {
        abort_unless($this->userHasPermission('projects.update') || $this->canViewAll('projects.manage'), 403);

        $project = ProjectM::findOrFail($id);

        $validated = $request->validate([
            'project_code' => "required|string|max:50|unique:projects,project_code,{$id}",
            'name' => 'required|string|max:191',
            'client_name' => 'nullable|string|max:191',
            'delivery_head_employee_id' => 'nullable|exists:employees_new,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,on_hold,completed,archived',
            'description' => 'nullable|string',
        ]);

        $project->update(array_merge($validated, ['updated_by' => Auth::id()]));

        return back()->with('success', 'Project details updated successfully.');
    }

    public function hierarchy($id)
    {
        abort_unless($this->accessScope->canAccessProject((int) $id), 403);

        $hierarchy = $this->projectService->getProjectHierarchy((int) $id);
        $project = ProjectM::findOrFail($id);

        return view('hrms.projects.hierarchy', compact('project', 'hierarchy'));
    }

    public function myProjects()
    {
        $employeeId = $this->ownEmployeeId();
        abort_if(!$employeeId, 403);

        $projectIds = $this->accessScope->getEmployeeProjectIds($employeeId);
        $projects = ProjectM::with([
            'deliveryHead.user',
            'teams.teamLead.user',
            'teams.members.user',
            'activeAssignments.employee.user',
            'activeAssignments.employee.designation'
        ])
        ->whereIn('id', $projectIds)
        ->get();

        return view('hrms.projects.my-projects', compact('projects'));
    }
}
