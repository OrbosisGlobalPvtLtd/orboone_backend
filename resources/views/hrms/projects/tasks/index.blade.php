@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Project Tasks')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: rgba(75, 0, 232, 0.06);
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.prj-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.prj-container {
    max-width: 1550px;
    margin: 0 auto;
}

.prj-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    overflow: hidden;
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <div>
                <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-list-check mr-2"></i>Project Tasks</h1>
                <p class="mb-0 opacity-90">Track task assignments, progress percentage, priorities, and deadlines across projects.</p>
            </div>
            <div>
                <button type="button" class="btn btn-light font-weight-bold px-4 py-2" style="border-radius: 12px; color: var(--orb-primary);" data-toggle="modal" data-target="#createTaskModal">
                    <i class="fas fa-plus mr-2"></i>Create Task
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px;" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="prj-card p-4 mb-4">
            <form method="GET" action="{{ route('projects.tasks.index') }}" class="form-row align-items-center">
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">Project</label>
                    <select name="project_id" class="form-control select2">
                        <option value="">-- All Projects --</option>
                        @foreach(\App\Models\HRMS\ProjectManagement\ProjectM::all() as $prj)
                            <option value="{{ $prj->id }}" {{ request('project_id') == $prj->id ? 'selected' : '' }}>{{ $prj->name }} ({{ $prj->project_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">Priority</label>
                    <select name="priority" class="form-control">
                        <option value="">-- All Priorities --</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All Statuses --</option>
                        <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>Todo</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 my-1 text-right align-self-end">
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('projects.tasks.index') }}" class="btn btn-light border px-3 ml-1" style="border-radius: 10px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tasks Table -->
        <div class="prj-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Task Title</th>
                            <th>Project</th>
                            <th>Assigned Employee</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress %</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td>
                                <strong class="text-dark">{{ $task->title }}</strong>
                                <div class="small text-muted">{{ Str::limit($task->description, 50) }}</div>
                            </td>
                            <td><span class="badge badge-light border font-weight-bold">{{ optional($task->project)->name ?? 'N/A' }}</span></td>
                            <td><strong class="text-dark">{{ optional(optional($task->assignedEmployee)->user)->name ?? 'Unassigned' }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $task->priority == 'urgent' ? 'danger' : ($task->priority == 'high' ? 'warning' : 'secondary') }} text-uppercase px-2 py-1">
                                    {{ strtoupper($task->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $task->status == 'completed' ? 'success' : ($task->status == 'blocked' ? 'danger' : 'info') }} text-uppercase px-2 py-1">
                                    {{ str_replace('_', ' ', strtoupper($task->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-fill" style="height: 8px; border-radius: 4px; min-width: 70px;">
                                        <div class="progress-bar bg-success" style="width: {{ $task->progress_percentage }}%;"></div>
                                    </div>
                                    <small class="font-weight-bold text-dark">{{ $task->progress_percentage }}%</small>
                                </div>
                            </td>
                            <td><span class="small text-muted">{{ $task->due_date ? $task->due_date->format('d M Y') : 'N/A' }}</span></td>
                            <td>
                                <button class="btn btn-xs btn-outline-primary px-2 py-1" style="border-radius: 6px;" data-toggle="modal" data-target="#updateStatusModal{{ $task->id }}">
                                    <i class="fas fa-edit mr-1"></i> Update Status
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="updateStatusModal{{ $task->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                                            <form action="{{ route('projects.tasks.update_status', $task->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                                                    <h5 class="modal-title font-weight-bold">Update Task Status</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body p-4 text-left">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="todo" {{ $task->status == 'todo' ? 'selected' : '' }}>Todo</option>
                                                            <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                            <option value="blocked" {{ $task->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                                            <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                            <option value="cancelled" {{ $task->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold">Progress Percentage (0 - 100%)</label>
                                                        <input type="number" name="progress_percentage" class="form-control" min="0" max="100" value="{{ $task->progress_percentage }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light px-4 py-3">
                                                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                                                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No project tasks found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $tasks->links() }}
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form action="{{ route('projects.tasks.store') }}" method="POST">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Create Project Task</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Task title..." required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-control select2" required>
                                <option value="">-- Select Project --</option>
                                @foreach(\App\Models\HRMS\ProjectManagement\ProjectM::where('status', 'active')->get() as $prj)
                                    <option value="{{ $prj->id }}">{{ $prj->name }} ({{ $prj->project_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Assigned Employee</label>
                            <select name="assigned_employee_id" class="form-control select2">
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->display_name }} ({{ $emp->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Priority</label>
                            <select name="priority" class="form-control">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Status</label>
                            <select name="status" class="form-control">
                                <option value="todo" selected>Todo</option>
                                <option value="in_progress">In Progress</option>
                                <option value="blocked">Blocked</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Task description..."></textarea>
                    </div>
                    <input type="hidden" name="progress_percentage" value="0">
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;"><i class="fas fa-save mr-1"></i> Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
