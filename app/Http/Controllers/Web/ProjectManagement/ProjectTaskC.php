<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\ProjectManagement\ProjectTaskM;
use App\Services\HRMS\ProjectManagement\ProjectAccessScopeS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $employees = $this->employeeOptions();

        return view('hrms.projects.tasks.index', compact('tasks', 'employees'));
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
}
