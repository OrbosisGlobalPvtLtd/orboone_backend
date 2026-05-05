@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
<style>
    :root {
        --primary-orb: #1560ab;
        --secondary-orb: #0099cc;
        --success-orb: #1cc88a;
        --warning-orb: #f6c23e;
        --danger-orb: #e74a3b;
        --bg-light-orb: #f8f9fc;
    }

    .custom-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        background: white;
        margin-bottom: 2rem;
    }

    .filter-bar {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 25px;
    }

    .status-tab {
        border-radius: 30px;
        padding: 10px 25px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s;
        border: 2px solid transparent;
        color: #858796;
    }

    .status-tab.active {
        background: var(--primary-orb);
        color: white;
        box-shadow: 0 4px 10px rgba(21, 96, 171, 0.2);
    }

    .status-tab:hover:not(.active) {
        background: #f1f3f9;
        color: var(--primary-orb);
    }

    .task-badge {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 5px 12px;
        border-radius: 50px;
    }

    .badge-pending { background: #eef2ff; color: #4e73df; }
    .badge-in_progress { background: #fff9db; color: #f08c00; }
    .badge-completed { background: #e6f7e6; color: #1cc88a; }

    .avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-orb {
        background: var(--primary-orb);
        color: white;
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 700;
        transition: all 0.3s;
    }

    .btn-orb:hover {
        background: var(--secondary-orb);
        color: white;
        transform: translateY(-2px);
    }

    .table thead th {
        background: #f8f9fc;
        color: var(--primary-orb);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 15px;
    }
</style>

<div class="container-fluid py-4 px-4">
    <!-- Header Area -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-tasks text-primary mr-2"></i> Task Management Hub
            </h4>
            <p class="text-muted small mb-0">Track, manage and report on team productivity</p>
        </div>
        <div class="d-flex">
            <a href="{{ route('project_management.tasks.create') }}" class="btn btn-orb mr-2">
                <i class="fas fa-plus mr-2"></i> Create Task
            </a>
            <a href="{{ route('project_management.tasks.export') }}" class="btn btn-light shadow-sm" style="border-radius: 50px;" target="_blank">
                <i class="fas fa-print mr-2"></i> Generate Report
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form action="{{ route('project_management.tasks.index') }}" method="GET" class="row align-items-end">
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="small font-weight-bold text-muted text-uppercase">Search Task</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px;"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="search" class="form-control border-left-0" style="border-radius: 0 10px 10px 0;" placeholder="Search title or details..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <label class="small font-weight-bold text-muted text-uppercase">Employee</label>
                <select name="user_id" class="form-control custom-select" style="border-radius: 10px;">
                    <option value="all">All Staff</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->user->id }}" {{ request('user_id') == $emp->user->id ? 'selected' : '' }}>
                            {{ $emp->employeeDetail->name ?? $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <label class="small font-weight-bold text-muted text-uppercase">Start Date</label>
                <input type="date" name="start_date" class="form-control" style="border-radius: 10px;" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <label class="small font-weight-bold text-muted text-uppercase">End Date</label>
                <input type="date" name="end_date" class="form-control" style="border-radius: 10px;" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm" style="border-radius: 10px;">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
                @if(request()->hasAny(['search', 'user_id', 'status', 'start_date', 'end_date']))
                    <a href="{{ route('project_management.tasks.index') }}" class="btn btn-link btn-sm text-danger mt-1">Clear Filters</a>
                @endif
            </div>
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
        </form>
    </div>

    <!-- Status Tabs -->
    <div class="d-flex mb-4 overflow-auto pb-2">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="status-tab mr-3 {{ !request('status') || request('status') == 'all' ? 'active' : '' }}">
            <i class="fas fa-list-ul mr-2"></i> ALL TASKS
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="status-tab mr-3 {{ request('status') == 'pending' ? 'active' : '' }}">
            <i class="fas fa-clock mr-2"></i> PENDING
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'in_progress']) }}" class="status-tab mr-3 {{ request('status') == 'in_progress' ? 'active' : '' }}">
            <i class="fas fa-spinner fa-spin mr-2"></i> IN PROGRESS
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}" class="status-tab {{ request('status') == 'completed' ? 'active' : '' }}">
            <i class="fas fa-check-double mr-2"></i> COMPLETED
        </a>
    </div>

    <!-- Task List -->
    <div class="card custom-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Task Information</th>
                        <th>Assigned To</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr id="row-{{ $task->id }}">
                            <td class="font-weight-bold text-muted">#{{ $task->id }}</td>
                            <td>
                                <div class="font-weight-bold text-dark mb-1">{{ $task->title }}</div>
                                <div class="text-muted small" style="white-space:pre-line; max-height: 4.5em; overflow: hidden; overflow-wrap: break-word; word-wrap: break-word;">
                                    {{ strip_tags($task->description) }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $empPhoto = $task->user->employee->employeeDetail->photo ?? 'profile.png';
                                        $empFinalUrl = (strpos($empPhoto, 'http') === 0) ? $empPhoto : asset('storage/' . $empPhoto);
                                    @endphp
                                    <img src="{{ $empFinalUrl }}" 
                                         onerror="this.src='{{ asset('images/profile.png') }}'; this.onerror=null;"
                                         class="avatar-sm mr-2">
                                    <div>
                                        <div class="font-weight-bold small text-dark">{{ $task->user->employee->employeeDetail->name ?? ($task->user->name ?? 'N/A') }}</div>
                                        <div class="extra-small text-muted">{{ $task->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small font-weight-bold">
                                    <i class="far fa-calendar-alt text-muted mr-1"></i>
                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d M, Y') }}
                                </div>
                                @php
                                    $isOverdue = \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status != 'completed';
                                @endphp
                                @if($isOverdue)
                                    <span class="badge badge-danger text-uppercase px-1" style="font-size: 10px;">Overdue</span>
                                @endif
                            </td>
                            <td>
                                <span class="task-badge badge-{{ $task->status }}">
                                    <i class="fas fa-{{ $task->status == 'completed' ? 'check-circle' : ($task->status == 'in_progress' ? 'spinner fa-spin' : 'hourglass-half') }} mr-1"></i>
                                    {{ str_replace('_', ' ', $task->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <a href="{{ route('project_management.tasks.edit', $task->id) }}" class="btn btn-sm btn-light text-primary hover-shadow" title="Edit Task">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('project_management.tasks.destroy', $task->id) }}" method="POST" id="deleteForm-{{ $task->id }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-light text-danger hover-shadow ml-1" onclick="confirmDelete({{ $task->id }})" title="Delete Task">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted p-5">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-2"></i>
                                    <h5>No tasks found matching your criteria</h5>
                                    <p class="small">Try adjusting your filters or search terms</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmDelete(taskId) {
        if (confirm("Are you sure you want to delete this task? This action cannot be undone.")) {
            document.getElementById('deleteForm-' + taskId).submit();
        }
    }
</script>
@endsection
