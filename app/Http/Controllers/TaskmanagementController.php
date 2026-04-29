<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskmanagementModel;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;

class TaskmanagementController extends Controller
{
    // -------------------- LIST (ADMIN) --------------------
    public function task_management(Request $request)
    {
        $query = TaskmanagementModel::with(['user.employee.employeeDetail']);

        // 1. Search by title or description
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // 2. Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 3. Filter by User/Employee
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        // 4. Filter by Date Range (Due Date)
        if ($request->filled('start_date')) {
            $query->whereDate('due_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('due_date', '<=', $request->end_date);
        }

        $tasks = $query->orderBy('id', 'DESC')->get();
        
        // For employee dropdown
        $employees = Employee::with('user')->get();
        $accesses  = \App\Models\Access::where('role_id', auth()->user()->role_id)->with('menu')->get();

        return view('pages.task_management', compact('tasks', 'employees', 'accesses'));
    }

    // -------------------- PRINT --------------------
    public function task_print()
    {
        $task = TaskmanagementModel::with('user')->get();

        session()->flash('status', 'Task print generated at ' . now()->format('H:i:s'));

        return view('pages.task_print', compact('task'));
    }

    // -------------------- SHOW ADD FORM --------------------
    public function store_task()
    {
        // employees already linked to users
        $employees = Employee::with('user')->get();
        $roles     = Role::all();
        $accesses  = \App\Models\Access::where('role_id', auth()->user()->role_id)->with('menu')->get();

        return view('pages.add_task', compact('employees', 'roles', 'accesses'));
    }

    // -------------------- SAVE NEW TASK --------------------
    public function add_task(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'due_date'      => 'required|date',
            'user_id'       => 'required|exists:users,id',  // assigned user
            'status'        => 'required|string|in:pending,in_progress,completed',
        ]);

        // Get employee name for that user (optional)
        $employee = Employee::where('user_id', $request->user_id)->first();

        TaskmanagementModel::create([
            'title'         => $request->title,
            'description'   => strip_tags($request->description),
            'due_date'      => $request->due_date,
            'status'        => $request->status,
            'user_id'       => $request->user_id,
            'employee_name' => $employee->name ?? null,
        ]);

        session()->flash('status', 'Task added successfully at ' . now()->format('H:i:s'));

        return redirect()->route('task_management');
    }

    // -------------------- EDIT FORM --------------------
    public function edit_task($id)
    {
        $task      = TaskmanagementModel::findOrFail($id);
        $employees = Employee::with('user')->get();
        $accesses  = \App\Models\Access::where('role_id', auth()->user()->role_id)->with('menu')->get();

        return view('pages.add_task', compact('task', 'employees', 'accesses'));
    }

    // -------------------- UPDATE TASK --------------------
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'due_date'      => 'required|date',
            'user_id'       => 'required|exists:users,id',
            'status'        => 'required|string|in:pending,in_progress,completed',
        ]);

        $task = TaskmanagementModel::findOrFail($id);
        $employee = Employee::where('user_id', $request->user_id)->first();

        $task->update([
            'title'         => $request->title,
            'description'   => strip_tags($request->description),
            'due_date'      => $request->due_date,
            'status'        => $request->status,
            'user_id'       => $request->user_id,
            'employee_name' => $employee->name ?? null,
        ]);

        session()->flash('status', 'Task updated successfully at ' . now()->format('H:i:s'));

        return redirect()->route('task_management');
    }

    // -------------------- DELETE --------------------
    public function destroy($id)
    {
        $task = TaskmanagementModel::findOrFail($id);
        $task->delete();

        session()->flash('status', 'Task deleted successfully at ' . now()->format('H:i:s'));

        return redirect()->route('task_management');
    }

    // -------------------- EMPLOYEE WEB VIEW: MY TASKS --------------------
    public function myTasks()
    {
        $tasks = TaskmanagementModel::with('user')
            ->where('user_id', auth()->id())
            ->orderBy('id', 'DESC')
            ->get();
        $accesses = \App\Models\Access::where('role_id', auth()->user()->role_id)->with('menu')->get();

        return view('pages.my_tasks', compact('tasks', 'accesses'));
    }
}
