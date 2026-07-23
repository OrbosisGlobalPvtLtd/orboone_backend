<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectManagement\TaskmanagementModel;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\Core\RoleM as Role;
use App\Models\Core\UserM as User;
use App\Services\HRMS\Notification\NotificationS;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TaskmanagementController extends Controller
{
    /**
     * Get role context helper
     */
    private function getUserRoleContext($user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return [
                'is_admin' => false,
                'is_manager' => false,
                'is_employee' => true,
                'role_name' => 'Employee',
                'employee' => null,
                'reporting_user_ids' => [],
            ];
        }

        $emp = EmployeeM::where('user_id', $user->id)->first();
        $roleName = $user->role ? ($user->role->name ?? '') : '';
        $lowerRole = strtolower($roleName);

        $isAdmin = false;
        if (str_contains($lowerRole, 'admin') || str_contains($lowerRole, 'super') || $user->system_role_id == 1) {
            $isAdmin = true;
        }

        $reportingUserIds = [$user->id];
        $isManager = false;

        if ($emp) {
            $subordinateUserIds = EmployeeM::where('reporting_manager_employee_id', $emp->id)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();

            if (count($subordinateUserIds) > 0 || str_contains($lowerRole, 'manager')) {
                $isManager = true;
                $reportingUserIds = array_merge([$user->id], $subordinateUserIds);
            }
        }

        return [
            'is_admin' => $isAdmin,
            'is_manager' => $isManager,
            'is_employee' => !$isAdmin && !$isManager,
            'role_name' => $isAdmin ? 'Admin' : ($isManager ? 'Reporting Manager' : 'Employee'),
            'employee' => $emp,
            'reporting_user_ids' => array_values(array_unique($reportingUserIds)),
        ];
    }

    // -------------------- MAIN DASHBOARD & LIST --------------------
    public function task_management(Request $request)
    {
        $roleCtx = $this->getUserRoleContext();

        $query = TaskmanagementModel::with(['user.employee.employeeDetail']);

        // Role-based Task Scoping
        if (!$roleCtx['is_admin']) {
            if ($roleCtx['is_manager']) {
                $query->whereIn('user_id', $roleCtx['reporting_user_ids']);
            } else {
                $query->where('user_id', auth()->id());
            }
        }

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('due_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('due_date', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        $allTasks = $query->orderBy('id', 'DESC')->get();

        $tasks = $allTasks;
        if ($request->filled('status') && $request->status !== 'all') {
            $desiredStatus = $request->status;
            if ($desiredStatus === 'overdue') {
                $tasks = $tasks->filter(fn($t) => $t->is_overdue);
            } elseif ($desiredStatus === 'in_progress') {
                $tasks = $tasks->filter(fn($t) => in_array($t->status, ['in_progress', 'progress']));
            } else {
                $tasks = $tasks->filter(fn($t) => $t->status === $desiredStatus);
            }
        }

        // Calculate Metrics for Dashboard KPI Cards
        $stats = [
            'total'       => $allTasks->count(),
            'pending'     => $allTasks->where('status', 'pending')->count(),
            'in_progress' => $allTasks->filter(fn($t) => in_array($t->status, ['in_progress', 'progress']))->count(),
            'on_hold'     => $allTasks->where('status', 'on_hold')->count(),
            'completed'   => $allTasks->where('status', 'completed')->count(),
            'verified'    => $allTasks->where('status', 'verified')->count(),
            'closed'      => $allTasks->where('status', 'closed')->count(),
            'due_today'   => $allTasks->filter(function ($t) {
                return $t->due_date && \Carbon\Carbon::parse($t->due_date)->isToday() && !in_array($t->status, ['completed', 'verified', 'closed']);
            })->count(),
            'overdue'     => $allTasks->filter(function ($t) {
                return $t->is_overdue;
            })->count(),
        ];

        // Fetch assignable employees dropdown based on role
        $empQuery = EmployeeM::query()
            ->join('users', 'users.id', '=', 'employees_new.user_id')
            ->select('employees_new.id', 'employees_new.user_id', 'employees_new.employee_code', 'users.name')
            ->where('employees_new.is_active', 1)
            ->where('employees_new.employment_status', 'active');

        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            $empQuery->whereIn('users.id', $roleCtx['reporting_user_ids']);
        }

        $employees = $empQuery->orderBy('users.name')->get();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->system_role_id)->with('menu')->get();

        return view('project_management.task_management', compact('tasks', 'employees', 'accesses', 'stats', 'roleCtx'));
    }

    // -------------------- EMPLOYEE MY TASKS --------------------
    public function myTasks(Request $request)
    {
        $roleCtx = $this->getUserRoleContext();
        $allTasks = TaskmanagementModel::with(['user.employee.employeeDetail'])
            ->where('user_id', auth()->id())
            ->orderBy('id', 'DESC')
            ->get();

        $tasks = $allTasks;
        if ($request->filled('status') && $request->status !== 'all') {
            $desiredStatus = $request->status;
            if ($desiredStatus === 'overdue') {
                $tasks = $tasks->filter(fn($t) => $t->is_overdue);
            } elseif ($desiredStatus === 'in_progress') {
                $tasks = $tasks->filter(fn($t) => in_array($t->status, ['in_progress', 'progress']));
            } else {
                $tasks = $tasks->filter(fn($t) => $t->status === $desiredStatus);
            }
        }

        $stats = [
            'total'       => $allTasks->count(),
            'pending'     => $allTasks->where('status', 'pending')->count(),
            'in_progress' => $allTasks->filter(fn($t) => in_array($t->status, ['in_progress', 'progress']))->count(),
            'on_hold'     => $allTasks->where('status', 'on_hold')->count(),
            'completed'   => $allTasks->where('status', 'completed')->count(),
            'verified'    => $allTasks->where('status', 'verified')->count(),
            'closed'      => $allTasks->where('status', 'closed')->count(),
            'due_today'   => $allTasks->filter(fn($t) => $t->due_date && \Carbon\Carbon::parse($t->due_date)->isToday() && !in_array($t->status, ['completed', 'verified', 'closed']))->count(),
            'overdue'     => $allTasks->filter(fn($t) => $t->is_overdue)->count(),
        ];

        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->system_role_id)->with('menu')->get();

        return view('project_management.my_tasks', compact('tasks', 'accesses', 'stats', 'roleCtx'));
    }


    // -------------------- SHOW ADD FORM --------------------
    public function store_task()
    {
        $roleCtx = $this->getUserRoleContext();
        if ($roleCtx['is_employee']) {
            return redirect()->route('project_management.tasks.my')->with('error', 'Unauthorized to create tasks.');
        }

        $empQuery = EmployeeM::with('user')->where('is_active', 1);
        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            $empQuery->whereIn('user_id', $roleCtx['reporting_user_ids']);
        }
        $employees = $empQuery->get();
        $roles = Role::all();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->system_role_id)->with('menu')->get();

        return view('project_management.add_task', compact('employees', 'roles', 'accesses', 'roleCtx'));
    }

    // -------------------- SAVE NEW TASK --------------------
    public function add_task(Request $request)
    {
        $roleCtx = $this->getUserRoleContext();
        if ($roleCtx['is_employee']) {
            return redirect()->back()->with('error', 'Unauthorized to create tasks.');
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'due_date'    => 'required|date',
            'user_id'     => 'required|exists:users,id',
            'status'      => 'nullable|string|in:pending,in_progress,progress,on_hold,completed',
        ]);

        // Manager check
        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            if (!in_array($request->user_id, $roleCtx['reporting_user_ids'])) {
                return redirect()->back()->with('error', 'You can only assign tasks to employees reporting to you.');
            }
        }

        $user = User::find($request->user_id);
        $employee = EmployeeM::where('user_id', $request->user_id)->first();
        $assigneeName = $user ? $user->name : ($employee->display_name ?? 'User #' . $request->user_id);
        $status = $request->status ?: 'pending';

        $task = TaskmanagementModel::create([
            'title'         => $request->title,
            'description'   => strip_tags($request->description),
            'due_date'      => $request->due_date,
            'status'        => $status,
            'user_id'       => $request->user_id,
            'employee_name' => $assigneeName,
        ]);

        // Log timeline event
        $task->logTimeline(
            'Task Created',
            auth()->id(),
            auth()->user()->name,
            'Task created and assigned to ' . $assigneeName
        );
        $task->save();


        // Send notification
        try {
            app(NotificationS::class)->notifyUser(
                $request->user_id,
                'Task Assigned: ' . $task->title,
                'You have been assigned a new task due on ' . \Carbon\Carbon::parse($task->due_date)->format('M d, Y') . '.',
                ['task_id' => $task->id, 'type' => 'task_assigned']
            );
        } catch (\Throwable $e) {
            // Ignore notification errors if FCM/Mail skipped
        }

        session()->flash('status', 'Task created successfully.');

        return redirect()->route('project_management.tasks.index');
    }

    // -------------------- EDIT FORM --------------------
    public function edit_task($id)
    {
        $roleCtx = $this->getUserRoleContext();
        if ($roleCtx['is_employee']) {
            return redirect()->route('project_management.tasks.my')->with('error', 'Unauthorized to edit tasks.');
        }

        $task = TaskmanagementModel::findOrFail($id);
        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            if (!in_array($task->user_id, $roleCtx['reporting_user_ids'])) {
                return redirect()->route('project_management.tasks.index')->with('error', 'Unauthorized to edit this task.');
            }
        }

        $empQuery = EmployeeM::with('user')->where('is_active', 1);
        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            $empQuery->whereIn('user_id', $roleCtx['reporting_user_ids']);
        }
        $employees = $empQuery->get();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->system_role_id)->with('menu')->get();

        return view('project_management.add_task', compact('task', 'employees', 'accesses', 'roleCtx'));
    }

    // -------------------- UPDATE TASK --------------------
    public function update(Request $request, $id)
    {
        $roleCtx = $this->getUserRoleContext();
        $task = TaskmanagementModel::findOrFail($id);

        if ($roleCtx['is_employee']) {
            return redirect()->back()->with('error', 'Unauthorized to edit task details.');
        }

        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            if (!in_array($task->user_id, $roleCtx['reporting_user_ids'])) {
                return redirect()->back()->with('error', 'Unauthorized to modify this task.');
            }
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'due_date'    => 'required|date',
            'user_id'     => 'required|exists:users,id',
            'status'      => 'required|string|in:pending,in_progress,progress,on_hold,completed,verified,closed',
        ]);

        $oldStatus = $task->status;
        $oldUserId = $task->user_id;

        $user = User::find($request->user_id);
        $employee = EmployeeM::where('user_id', $request->user_id)->first();
        $assigneeName = $user ? $user->name : ($employee->display_name ?? 'User #' . $request->user_id);

        $task->title       = $request->title;
        $task->description = strip_tags($request->description);
        $task->due_date    = $request->due_date;
        $task->status      = $request->status;
        $task->user_id     = $request->user_id;
        $task->employee_name = $assigneeName;

        // Log timeline changes
        if ($oldUserId != $request->user_id) {
            $task->logTimeline(
                'Task Reassigned',
                auth()->id(),
                auth()->user()->name,
                'Reassigned to ' . $assigneeName
            );
        }

        if ($oldStatus != $request->status) {
            $task->logTimeline(
                'Status Updated',
                auth()->id(),
                auth()->user()->name,
                "Status changed from {$oldStatus} to {$request->status}",
                $oldStatus,
                $request->status
            );
        }

        $task->save();

        // Send notifications
        try {
            if ($oldUserId != $request->user_id) {
                app(NotificationS::class)->notifyUser(
                    $request->user_id,
                    'Task Reassigned: ' . $task->title,
                    'A task has been reassigned to you.',
                    ['task_id' => $task->id, 'type' => 'task_assigned']
                );
            } elseif ($oldStatus != $request->status) {
                app(NotificationS::class)->notifyUser(
                    $task->user_id,
                    'Task Status Updated: ' . $task->title,
                    'Task status changed to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.',
                    ['task_id' => $task->id, 'type' => 'task_updated']
                );
            }
        } catch (\Throwable $e) {}

        session()->flash('status', 'Task updated successfully.');

        return redirect()->route('project_management.tasks.index');
    }

    // -------------------- AJAX UPDATE STATUS --------------------
    public function updateStatus(Request $request, $id)
    {
        $roleCtx = $this->getUserRoleContext();
        $task = TaskmanagementModel::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:pending,in_progress,progress,on_hold,completed,verified,closed',
        ]);

        $newStatus = $request->status;

        // Employee Restrictions: cannot set to verified or closed, must own task
        if ($roleCtx['is_employee']) {
            if ($task->user_id != auth()->id()) {
                return response()->json(['status' => false, 'message' => 'Unauthorized task access.'], 403);
            }
            if (in_array($newStatus, ['verified', 'closed'])) {
                return response()->json(['status' => false, 'message' => 'Employees cannot verify or close tasks.'], 403);
            }
        }

        // Manager check
        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            if (!in_array($task->user_id, $roleCtx['reporting_user_ids'])) {
                return response()->json(['status' => false, 'message' => 'Unauthorized manager access.'], 403);
            }
        }

        $oldStatus = $task->status;
        $task->status = $newStatus;

        $task->logTimeline(
            'Status Changed',
            auth()->id(),
            auth()->user()->name,
            "Status changed from " . ucfirst(str_replace('_', ' ', $oldStatus)) . " to " . ucfirst(str_replace('_', ' ', $newStatus)),
            $oldStatus,
            $newStatus
        );

        $task->save();

        // Trigger Notification
        try {
            $notificationS = app(NotificationS::class);
            if ($newStatus === 'completed') {
                // Notify Manager / Admin
                $notificationS::notifyAdmins(
                    'Task Completed: ' . $task->title,
                    auth()->user()->name . ' completed task: ' . $task->title,
                    ['task_id' => $task->id, 'type' => 'task_completed']
                );
            } else {
                $notificationS->notifyUser(
                    $task->user_id,
                    'Task Updated: ' . $task->title,
                    'Status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)),
                    ['task_id' => $task->id, 'type' => 'task_updated']
                );
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => true,
            'message' => 'Task status updated successfully.',
            'task' => $task
        ]);
    }

    // -------------------- AJAX ADD COMMENT / ATTACHMENT --------------------
    public function addComment(Request $request, $id)
    {
        $roleCtx = $this->getUserRoleContext();
        $task = TaskmanagementModel::findOrFail($id);

        if ($roleCtx['is_employee'] && $task->user_id != auth()->id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized task access.'], 403);
        }

        $request->validate([
            'comment'    => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt|max:10240',
        ]);

        $attachments = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('public/task_attachments', $filename);
            $url = Storage::url('task_attachments/' . $filename);

            $ext = strtolower($file->getClientOriginalExtension());
            $type = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'document';

            $attachments[] = [
                'name' => $file->getClientOriginalName(),
                'url'  => $url,
                'type' => $type,
            ];
        }

        $commentItem = $task->addComment(
            $request->comment,
            auth()->id(),
            auth()->user()->name,
            $roleCtx['role_name'],
            $attachments
        );

        $task->logTimeline(
            'Comment Added',
            auth()->id(),
            auth()->user()->name,
            'Added a comment: "' . \Illuminate\Support\Str::limit($request->comment, 50) . '"'
        );

        $task->save();

        return response()->json([
            'status' => true,
            'message' => 'Comment posted successfully.',
            'comment' => $commentItem,
            'updates' => $task->updates_data,
        ]);
    }

    // -------------------- AJAX TASK DETAIL --------------------
    public function showDetail($id)
    {
        $roleCtx = $this->getUserRoleContext();
        $task = TaskmanagementModel::with(['user.employee.employeeDetail'])->findOrFail($id);

        if ($roleCtx['is_employee'] && $task->user_id != auth()->id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized task access.'], 403);
        }

        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            if (!in_array($task->user_id, $roleCtx['reporting_user_ids'])) {
                return response()->json(['status' => false, 'message' => 'Unauthorized task access.'], 403);
            }
        }

        return response()->json([
            'status' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->clean_description,

                'due_date' => $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : null,
                'formatted_due_date' => $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '-',
                'status' => $task->status,
                'is_overdue' => $task->is_overdue,
                'user_id' => $task->user_id,
                'assignee_name' => $task->user->name ?? $task->employee_name ?? 'Unassigned',
                'assignee_code' => $task->user->employee->employee_code ?? null,
                'updates' => $task->updates_data,
            ],
            'permissions' => [
                'can_edit' => !$roleCtx['is_employee'],
                'can_delete' => $roleCtx['is_admin'],
                'can_verify' => !$roleCtx['is_employee'],
                'can_close' => !$roleCtx['is_employee'],
            ]
        ]);
    }

    // -------------------- DELETE --------------------
    public function destroy($id)
    {
        $roleCtx = $this->getUserRoleContext();
        if (!$roleCtx['is_admin']) {
            return redirect()->back()->with('error', 'Only HR/Super Admin can delete tasks.');
        }

        $task = TaskmanagementModel::findOrFail($id);
        $task->delete();

        session()->flash('status', 'Task deleted successfully.');

        return redirect()->route('project_management.tasks.index');
    }

    // -------------------- PRINT --------------------
    public function task_print(Request $request)
    {
        $roleCtx = $this->getUserRoleContext();
        $query = TaskmanagementModel::with(['user.employee.employeeDetail']);

        // Role-based Task Scoping
        if (!$roleCtx['is_admin']) {
            if ($roleCtx['is_manager']) {
                $query->whereIn('user_id', $roleCtx['reporting_user_ids']);
            } else {
                $query->where('user_id', auth()->id());
            }
        }

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('due_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('due_date', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        $task = $query->orderBy('id', 'DESC')->get();

        if ($request->filled('status') && $request->status !== 'all') {
            $desiredStatus = $request->status;
            if ($desiredStatus === 'overdue') {
                $task = $task->filter(fn($t) => $t->is_overdue);
            } elseif ($desiredStatus === 'in_progress') {
                $task = $task->filter(fn($t) => in_array($t->status, ['in_progress', 'progress']));
            } else {
                $task = $task->filter(fn($t) => $t->status === $desiredStatus);
            }
        }

        session()->flash('status', 'Task print generated at ' . now()->format('H:i:s'));

        return view('project_management.task_print', compact('task'));
    }
}
