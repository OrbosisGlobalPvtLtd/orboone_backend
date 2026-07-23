<?php

namespace App\Http\Controllers\Api\V1\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\ProjectManagement\TaskmanagementModel;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\Core\UserM as User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class TaskController extends Controller
{
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

    private function formatTaskItem($task)
    {
        $timeline = $task->updates_data['timeline'] ?? [];
        $creatorName = 'Super Admin';
        if (count($timeline) > 0) {
            foreach ($timeline as $item) {
                if (($item['event'] ?? '') === 'Task Created') {
                    $creatorName = $item['user_name'] ?? $creatorName;
                    break;
                }
            }
        }

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->clean_description,

            'due_date' => $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : null,
            'formatted_due_date' => $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '-',
            'status' => $task->status,
            'is_overdue' => $task->is_overdue,
            'user_id' => $task->user_id,
            'assignee_name' => $task->user->name ?? $task->employee_name ?? 'Unassigned',
            'assigned_by' => $creatorName,
            'updates' => $task->updates_data,
            'created_at' => $task->created_at ? $task->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $task->updated_at ? $task->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    // -------------------- TASK LIST (MY TASKS / SCOPED TASKS) --------------------
    public function myTasks(Request $request)
    {
        return $this->getMyTasks($request);
    }

    public function getMyTasks(Request $request)
    {
        $roleCtx = $this->getUserRoleContext();
        $query = TaskmanagementModel::with(['user.employee']);

        // Scope
        if (!$roleCtx['is_admin']) {
            if ($roleCtx['is_manager'] && $request->get('scope') === 'team') {
                $query->whereIn('user_id', $roleCtx['reporting_user_ids']);
            } else {
                $query->where('user_id', auth()->id());
            }
        } elseif ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
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

        return response()->json([
            'status' => true,
            'message' => 'Tasks retrieved successfully.',
            'data' => $tasks->values()->map(fn($t) => $this->formatTaskItem($t)),
        ]);
    }


    // -------------------- TASK DETAIL --------------------
    public function taskDetail($id)
    {
        return $this->getTaskDetails($id);
    }

    public function getTaskDetails($id)
    {
        $roleCtx = $this->getUserRoleContext();
        $task = TaskmanagementModel::with(['user.employee'])->find($id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        if ($roleCtx['is_employee'] && $task->user_id != auth()->id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized task access.'], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Task detail fetched.',
            'data' => $this->formatTaskItem($task),
            'permissions' => [
                'can_edit' => !$roleCtx['is_employee'],
                'can_delete' => $roleCtx['is_admin'],
                'can_verify' => !$roleCtx['is_employee'],
                'can_close' => !$roleCtx['is_employee'],
            ]
        ]);
    }

    // -------------------- CREATE TASK --------------------
    public function createTask(Request $request)
    {
        $roleCtx = $this->getUserRoleContext();
        if ($roleCtx['is_employee']) {
            return response()->json(['status' => false, 'message' => 'Employees cannot create tasks.'], 403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'due_date'    => 'required|date',
            'user_id'     => 'required|exists:users,id',
            'status'      => 'nullable|string|in:pending,in_progress,progress,on_hold,completed',
        ]);

        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            if (!in_array($request->user_id, $roleCtx['reporting_user_ids'])) {
                return response()->json(['status' => false, 'message' => 'Can only assign tasks to reporting team members.'], 403);
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

        $task->logTimeline(
            'Task Created',
            auth()->id(),
            auth()->user()->name,
            'Task created via mobile app and assigned to ' . $assigneeName
        );
        $task->save();


        try {
            app(NotificationS::class)->notifyUser(
                $request->user_id,
                'Task Assigned: ' . $task->title,
                'You have been assigned a new task due on ' . \Carbon\Carbon::parse($task->due_date)->format('M d, Y') . '.',
                ['task_id' => $task->id, 'type' => 'task_assigned']
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => true,
            'message' => 'Task created successfully.',
            'data' => $this->formatTaskItem($task),
        ]);
    }

    // -------------------- UPDATE MY TASK / STATUS --------------------
    public function updateMyTask(Request $request, $id)
    {
        return $this->updateTaskStatus($request, $id);
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $roleCtx = $this->getUserRoleContext();
        $task = TaskmanagementModel::find($id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        $request->validate([
            'status'  => 'nullable|string|in:pending,in_progress,progress,on_hold,completed,verified,closed',
            'comment' => 'nullable|string',
        ]);

        if ($roleCtx['is_employee']) {
            if ($task->user_id != auth()->id()) {
                return response()->json(['status' => false, 'message' => 'Unauthorized task access.'], 403);
            }
            if ($request->filled('status') && in_array($request->status, ['verified', 'closed'])) {
                return response()->json(['status' => false, 'message' => 'Employees cannot verify or close tasks.'], 403);
            }
        }

        $oldStatus = $task->status;
        if ($request->filled('status') && $request->status !== $oldStatus) {
            $task->status = $request->status;
            $task->logTimeline(
                'Status Changed',
                auth()->id(),
                auth()->user()->name,
                "Status updated from {$oldStatus} to {$request->status}",
                $oldStatus,
                $request->status
            );
        }

        // Process file attachment if present
        $attachments = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $file->storeAs('public/task_attachments', $filename);
            $url = Storage::url('task_attachments/' . $filename);

            $ext = strtolower($file->getClientOriginalExtension());
            $type = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'document';

            $attachments[] = [
                'name' => $file->getClientOriginalName(),
                'url'  => $url,
                'type' => $type,
            ];
        }

        if ($request->filled('comment') || count($attachments) > 0) {
            $task->addComment(
                $request->comment ?: 'Uploaded attachment',
                auth()->id(),
                auth()->user()->name,
                $roleCtx['role_name'],
                $attachments
            );
        }

        $task->save();

        // Trigger Notification
        try {
            if ($request->filled('status') && $request->status !== $oldStatus) {
                if ($request->status === 'completed') {
                    app(NotificationS::class)::notifyAdmins(
                        'Task Completed: ' . $task->title,
                        auth()->user()->name . ' marked task completed: ' . $task->title,
                        ['task_id' => $task->id, 'type' => 'task_completed']
                    );
                } else {
                    app(NotificationS::class)->notifyUser(
                        $task->user_id,
                        'Task Status Updated: ' . $task->title,
                        'Task status set to ' . ucfirst(str_replace('_', ' ', $request->status)),
                        ['task_id' => $task->id, 'type' => 'task_updated']
                    );
                }
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => true,
            'message' => 'Task updated successfully.',
            'data' => $this->formatTaskItem($task),
        ]);
    }

    // -------------------- ADMIN UPDATE TASK --------------------
    public function adminUpdateTask(Request $request, $id)
    {
        $roleCtx = $this->getUserRoleContext();
        if ($roleCtx['is_employee']) {
            return response()->json(['status' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $task = TaskmanagementModel::find($id);
        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'due_date'    => 'sometimes|required|date',
            'user_id'     => 'sometimes|required|exists:users,id',
            'status'      => 'sometimes|required|string|in:pending,in_progress,progress,on_hold,completed,verified,closed',
        ]);

        if ($request->has('title')) $task->title = $request->title;
        if ($request->has('description')) $task->description = strip_tags($request->description);
        if ($request->has('due_date')) $task->due_date = $request->due_date;
        if ($request->has('status')) $task->status = $request->status;
        if ($request->has('user_id')) {
            $task->user_id = $request->user_id;
            $user = User::find($request->user_id);
            $emp = EmployeeM::where('user_id', $request->user_id)->first();
            $task->employee_name = $user ? $user->name : ($emp->display_name ?? 'User #' . $request->user_id);
        }


        $task->save();

        return response()->json([
            'status' => true,
            'message' => 'Task updated successfully by admin.',
            'data' => $this->formatTaskItem($task),
        ]);
    }

    // -------------------- ADD COMMENT --------------------
    public function addComment(Request $request, $id)
    {
        $roleCtx = $this->getUserRoleContext();
        $task = TaskmanagementModel::find($id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        $request->validate([
            'comment'    => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt|max:10240',
        ]);

        $attachments = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $file->storeAs('public/task_attachments', $filename);
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
            'Added comment via mobile app: "' . \Illuminate\Support\Str::limit($request->comment, 40) . '"'
        );

        $task->save();

        return response()->json([
            'status' => true,
            'message' => 'Comment added.',
            'comment' => $commentItem,
            'data' => $this->formatTaskItem($task),
        ]);
    }

    // -------------------- DASHBOARD STATS --------------------
    public function getDashboardStats(Request $request)
    {
        $roleCtx = $this->getUserRoleContext();
        $query = TaskmanagementModel::query();

        if (!$roleCtx['is_admin']) {
            if ($roleCtx['is_manager']) {
                $query->whereIn('user_id', $roleCtx['reporting_user_ids']);
            } else {
                $query->where('user_id', auth()->id());
            }
        }

        $allTasks = $query->get();

        return response()->json([
            'status' => true,
            'data' => [
                'total'       => $allTasks->count(),
                'pending'     => $allTasks->where('status', 'pending')->count(),
                'in_progress' => $allTasks->whereIn('status', ['in_progress', 'progress'])->count(),
                'on_hold'     => $allTasks->where('status', 'on_hold')->count(),
                'completed'   => $allTasks->where('status', 'completed')->count(),
                'verified'    => $allTasks->where('status', 'verified')->count(),
                'closed'      => $allTasks->where('status', 'closed')->count(),
                'due_today'   => $allTasks->filter(fn($t) => $t->due_date && \Carbon\Carbon::parse($t->due_date)->isToday() && !in_array($t->status, ['completed', 'verified', 'closed']))->count(),
                'overdue'     => $allTasks->filter(fn($t) => $t->is_overdue)->count(),
            ]
        ]);
    }

    // -------------------- ASSIGNABLE EMPLOYEES --------------------
    public function getAssignableEmployees(Request $request)
    {
        $roleCtx = $this->getUserRoleContext();
        $empQuery = EmployeeM::query()
            ->join('users', 'users.id', '=', 'employees_new.user_id')
            ->select('users.id', 'users.name', 'employees_new.employee_code')
            ->where('employees_new.is_active', 1)
            ->where('employees_new.employment_status', 'active');

        if (!$roleCtx['is_admin'] && $roleCtx['is_manager']) {
            $empQuery->whereIn('users.id', $roleCtx['reporting_user_ids']);
        }

        $employees = $empQuery->orderBy('users.name')->get();

        return response()->json([
            'status' => true,
            'data' => $employees
        ]);
    }
}