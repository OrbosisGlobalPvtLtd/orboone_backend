@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@endsection

@section('_content')
@include('hrms.employee.partials.styles')

<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .task-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .task-container {
        max-width: 1500px;
        margin: 0 auto;
    }

    .task-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 24px !important;
        padding: 28px 32px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15) !important;
        margin-bottom: 24px !important;
    }

    .task-header-premium .title-area h3 {
        font-size: 24px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
    }

    .task-header-premium .title-area p {
        font-size: 13px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 4px 0 0 0 !important;
    }

    .task-btn-pill {
        height: 40px !important;
        padding: 0 18px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: none !important;
        cursor: pointer !important;
        text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }

    .task-btn-pill:hover {
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    /* KPI Grid Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }
    @media (max-width: 1200px) {
        .kpi-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .kpi-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        text-decoration: none !important;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .kpi-val {
        font-size: 20px;
        font-weight: 900;
        color: var(--orb-text);
        line-height: 1.1;
    }

    .kpi-lbl {
        font-size: 11px;
        font-weight: 700;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 3px;
    }

    /* Status Badges */
    .task-badge {
        font-size: 10px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        padding: 5px 12px !important;
        border-radius: 50px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
    }
    .badge-pending { background: #FFF7ED !important; color: #C2410C !important; }
    .badge-in_progress, .badge-progress { background: #EFF6FF !important; color: #1D4ED8 !important; }
    .badge-on_hold { background: #FEF3C7 !important; color: #D97706 !important; }
    .badge-completed { background: #ECFDF5 !important; color: #047857 !important; }
    .badge-verified { background: #F0FDF4 !important; color: #15803D !important; border: 1px solid #BBF7D0; }
    .badge-closed { background: #F1F5F9 !important; color: #475467 !important; }
    .badge-overdue { background: #FEF2F2 !important; color: #DC2626 !important; }

    .avatar-sm {
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        border: 2px solid #fff !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08) !important;
    }

    /* Filters Bar */
    .filter-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        align-items: flex-end;
    }

    .filter-grid label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        margin-bottom: 5px !important;
        display: block;
    }

    .filter-grid .form-control, .filter-grid .form-select {
        height: 38px !important;
        border-radius: 8px !important;
        border: 1px solid var(--orb-border) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    /* Table styling */
    .task-table-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .task-table th {
        background: #F8FAFC !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        padding: 14px 18px !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .task-table td {
        padding: 14px 18px !important;
        vertical-align: middle !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px !important;
        border: none !important;
        box-shadow: 0 20px 50px rgba(16, 24, 40, 0.15) !important;
        overflow: hidden !important;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border-bottom: none !important;
        padding: 20px 24px !important;
    }

    .timeline-item {
        position: relative;
        padding-left: 24px;
        padding-bottom: 16px;
        border-left: 2px solid #E2E8F0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--orb-primary);
    }
</style>

<div class="task-page">
    <div class="task-container">

        <!-- Top Header -->
        <div class="task-header-premium">
            <div class="title-area">
                <h3>Task Management Dashboard</h3>
                <p>Track, manage, and verify team tasks in real-time.</p>
            </div>

            <div class="d-flex align-items-center" style="gap:10px;">
                @if(!$roleCtx['is_employee'])
                    <button type="button" class="task-btn-pill text-primary bg-white" data-toggle="modal" data-target="#createTaskModal">
                        <i class="fas fa-plus-circle"></i> Create Task
                    </button>
                @endif

                <a href="{{ route('project_management.tasks.export', request()->query()) }}" class="task-btn-pill text-white" style="background: rgba(255,255,255,0.2);" target="_blank">
                    <i class="fas fa-print"></i> Export Report
                </a>
            </div>
        </div>

        <!-- KPI Metrics Grid -->
        <div class="kpi-grid">
            <a href="?status=all" class="kpi-card">
                <div class="kpi-icon" style="background: #EEF2FF; color: #4F46E5;"><i class="fas fa-tasks"></i></div>
                <div>
                    <div class="kpi-val">{{ $stats['total'] }}</div>
                    <div class="kpi-lbl">Total Tasks</div>
                </div>
            </a>
            <a href="?status=pending" class="kpi-card">
                <div class="kpi-icon" style="background: #FFF7ED; color: #C2410C;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="kpi-val">{{ $stats['pending'] }}</div>
                    <div class="kpi-lbl">Pending</div>
                </div>
            </a>
            <a href="?status=in_progress" class="kpi-card">
                <div class="kpi-icon" style="background: #EFF6FF; color: #1D4ED8;"><i class="fas fa-spinner"></i></div>
                <div>
                    <div class="kpi-val">{{ $stats['in_progress'] }}</div>
                    <div class="kpi-lbl">In Progress</div>
                </div>
            </a>
            <a href="?status=on_hold" class="kpi-card">
                <div class="kpi-icon" style="background: #FEF3C7; color: #D97706;"><i class="fas fa-pause-circle"></i></div>
                <div>
                    <div class="kpi-val">{{ $stats['on_hold'] }}</div>
                    <div class="kpi-lbl">On Hold</div>
                </div>
            </a>
            <a href="?status=completed" class="kpi-card">
                <div class="kpi-icon" style="background: #ECFDF5; color: #047857;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="kpi-val">{{ $stats['completed'] }}</div>
                    <div class="kpi-lbl">Completed</div>
                </div>
            </a>
            <a href="?status=verified" class="kpi-card">
                <div class="kpi-icon" style="background: #F0FDF4; color: #15803D;"><i class="fas fa-badge-check"></i></div>
                <div>
                    <div class="kpi-val">{{ $stats['verified'] }}</div>
                    <div class="kpi-lbl">Verified</div>
                </div>
            </a>
            <a href="?status=closed" class="kpi-card">
                <div class="kpi-icon" style="background: #F1F5F9; color: #475467;"><i class="fas fa-folder-minus"></i></div>
                <div>
                    <div class="kpi-val">{{ $stats['closed'] }}</div>
                    <div class="kpi-lbl">Closed</div>
                </div>
            </a>
            <a href="?status=overdue" class="kpi-card">
                <div class="kpi-icon" style="background: #FEF2F2; color: #DC2626;"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="kpi-val">{{ $stats['overdue'] }}</div>
                    <div class="kpi-lbl">Overdue</div>
                </div>
            </a>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('project_management.tasks.index') }}" id="taskSearchForm">
                <div class="filter-grid">
                    <div>
                        <label>Search Title/Description</label>
                        <input type="text" name="search" class="form-control" placeholder="Search task..." value="{{ request('search') }}">
                    </div>

                    @if(!$roleCtx['is_employee'])
                    <div>
                        <label>Assignee Employee</label>
                        <select name="user_id" class="form-select">
                            <option value="all">All Assignees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->user_id }}" {{ request('user_id') == $emp->user_id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div>
                        <label>Status Filter</label>
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    <div>
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    <div>
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>

                    <div>
                        <a href="{{ route('project_management.tasks.index') }}" class="btn btn-light btn-block d-flex align-items-center justify-content-center" style="height: 38px; border-radius: 8px; border: 1px solid var(--orb-border); font-weight: 600; font-size: 13px;">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Task Table Card -->
        <div class="task-table-card">
            <div class="table-responsive">
                <table class="table task-table mb-0" id="taskMainTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Task Details</th>
                            <th>Assignee</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td class="font-weight-bold text-muted">#{{ $task->id }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $task->title }}</div>
                                    <div class="text-muted small mt-1" style="max-height: 2.4em; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strip_tags($task->clean_description) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $photo = $task->user->employee->employeeDetail->photo ?? 'profile.png';
                                            $imgUrl = (strpos($photo, 'http') === 0) ? $photo : asset('storage/' . $photo);
                                        @endphp
                                        <img src="{{ $imgUrl }}" onerror="this.src='{{ asset('images/profile.png') }}';" class="avatar-sm mr-2">
                                        <div>
                                            <div class="font-weight-bold text-dark small">{{ $task->user->name ?? $task->employee_name ?? 'N/A' }}</div>
                                            <div class="extra-small text-muted">{{ $task->user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small font-weight-bold">
                                        <i class="far fa-calendar-alt text-muted mr-1"></i>
                                        {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '-' }}
                                    </div>
                                    @if($task->is_overdue)
                                        <span class="badge badge-overdue mt-1">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="task-badge badge-{{ $task->status }}">
                                        {{ str_replace('_', ' ', $task->status) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-light text-primary" onclick="openTaskDetailModal({{ $task->id }})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$roleCtx['is_employee'])
                                            <button type="button" class="btn btn-sm btn-light text-dark" data-toggle="modal" data-target="#editTaskModal{{ $task->id }}" title="Edit Task">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        @if($roleCtx['is_admin'])
                                            <form action="{{ route('project_management.tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete Task">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-tasks fa-2x mb-2 text-muted"></i>
                                    <div>No tasks found matching current filters.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- CREATE TASK MODAL -->
@if(!$roleCtx['is_employee'])
<div class="modal fade" id="createTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Create New Task</h5>
                    <p class="mb-0 text-white-50 small">Assign task to employee</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('project_management.tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Complete HRMS Audit" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Assign To Employee <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->user_id }}">{{ $emp->name }} ({{ $emp->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="on_hold">On Hold</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Task Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Detailed description..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- EDIT TASK MODALS -->
@foreach($tasks as $task)
@if(!$roleCtx['is_employee'])
<div class="modal fade" id="editTaskModal{{ $task->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Edit Task #{{ $task->id }}</h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('project_management.tasks.update', $task->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ $task->title }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Assign To Employee <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->user_id }}" {{ $task->user_id == $emp->user_id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ in_array($task->status, ['in_progress', 'progress']) ? 'selected' : '' }}>In Progress</option>
                                <option value="on_hold" {{ $task->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="verified" {{ $task->status == 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="closed" {{ $task->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Task Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="4" required>{{ $task->clean_description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<!-- DYNAMIC TASK DETAIL & COMMENTS MODAL -->
<div class="modal fade" id="taskDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="detailModalTitle">Task Detail</h5>
                    <p class="mb-0 text-white-50 small" id="detailModalSubtitle">Assignee & Status</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-7">
                        <div class="card p-3 mb-3 border">
                            <h6 class="font-weight-bold text-muted text-uppercase mb-2" style="font-size: 11px;">Description</h6>
                            <div id="detailDescription" style="white-space: pre-line; font-size: 14px; color: #334155;"></div>
                        </div>

                        <!-- Status Quick Action -->
                        <div class="card p-3 mb-3 border bg-light">
                            <h6 class="font-weight-bold text-muted text-uppercase mb-2" style="font-size: 11px;">Update Task Status</h6>
                            <div class="d-flex align-items-center gap-2">
                                <select id="quickStatusSelect" class="form-select" style="max-width: 220px;" onchange="submitQuickStatusUpdate()">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="completed">Completed</option>
                                    <option value="verified" id="optVerified">Verified</option>
                                    <option value="closed" id="optClosed">Closed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Comments Feed -->
                        <div class="card p-3 border">
                            <h6 class="font-weight-bold text-muted text-uppercase mb-3" style="font-size: 11px;">Discussion & Comments</h6>
                            <div id="commentsFeed" style="max-height: 250px; overflow-y: auto;" class="mb-3">
                                <!-- Dynamic comments -->
                            </div>

                            <form id="commentForm" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-2">
                                    <textarea name="comment" id="commentInput" class="form-control" rows="2" placeholder="Write a comment..." required></textarea>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <input type="file" name="attachment" id="attachmentInput" class="form-control-file" style="max-width: 250px; font-size: 12px;">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="submitComment()">Post Comment</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <!-- Task Timeline Activity -->
                        <div class="card p-3 border mb-3">
                            <h6 class="font-weight-bold text-muted text-uppercase mb-3" style="font-size: 11px;">Activity Timeline</h6>
                            <div id="timelineFeed" style="max-height: 300px; overflow-y: auto;">
                                <!-- Dynamic timeline -->
                            </div>
                        </div>

                        <!-- Attachments list -->
                        <div class="card p-3 border">
                            <h6 class="font-weight-bold text-muted text-uppercase mb-2" style="font-size: 11px;">Attachments</h6>
                            <div id="attachmentsList">
                                <!-- Dynamic attachments -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
let currentDetailTaskId = null;

function openTaskDetailModal(taskId) {
    currentDetailTaskId = taskId;
    $('#taskDetailModal').modal('show');
    $('#commentsFeed').html('<div class="text-center p-3 text-muted">Loading...</div>');
    $('#timelineFeed').html('<div class="text-center p-3 text-muted">Loading...</div>');

    $.get('/hrms/task_detail/' + taskId, function(res) {
        if(res.status) {
            let t = res.task;
            $('#detailModalTitle').text('#' + t.id + ' - ' + t.title);
            $('#detailModalSubtitle').text('Assignee: ' + t.assignee_name + ' | Due: ' + t.formatted_due_date);
            $('#detailDescription').text(t.description || 'No description.');
            $('#quickStatusSelect').val(t.status);

            // Handle Employee verify/close restriction
            if(!res.permissions.can_verify) {
                $('#optVerified').hide();
                $('#optClosed').hide();
            } else {
                $('#optVerified').show();
                $('#optClosed').show();
            }

            // Render comments
            let comments = t.updates.comments || [];
            if(comments.length === 0) {
                $('#commentsFeed').html('<div class="text-muted small">No comments yet.</div>');
            } else {
                let cHtml = '';
                comments.forEach(c => {
                    let attHtml = '';
                    if(c.attachments && c.attachments.length > 0) {
                        c.attachments.forEach(att => {
                            attHtml += `<div class="mt-1"><a href="${att.url}" target="_blank" class="small"><i class="fas fa-paperclip"></i> ${att.name}</a></div>`;
                        });
                    }
                    cHtml += `
                        <div class="p-2 mb-2 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="small text-dark">${c.user_name} (${c.role})</strong>
                                <span class="text-muted extra-small" style="font-size: 10px;">${c.created_at}</span>
                            </div>
                            <div class="small text-secondary">${c.comment}</div>
                            ${attHtml}
                        </div>
                    `;
                });
                $('#commentsFeed').html(cHtml);
            }

            // Render timeline
            let timeline = t.updates.timeline || [];
            if(timeline.length === 0) {
                $('#timelineFeed').html('<div class="text-muted small">No activity recorded.</div>');
            } else {
                let tHtml = '';
                timeline.slice().reverse().forEach(tl => {
                    tHtml += `
                        <div class="timeline-item mb-2">
                            <div class="font-weight-bold small text-dark">${tl.event}</div>
                            <div class="extra-small text-muted">${tl.user_name} • ${tl.timestamp}</div>
                            <div class="small text-secondary mt-1">${tl.details}</div>
                        </div>
                    `;
                });
                $('#timelineFeed').html(tHtml);
            }

            // Render attachments
            let attachments = t.updates.attachments || [];
            if(attachments.length === 0) {
                $('#attachmentsList').html('<div class="text-muted small">No files attached.</div>');
            } else {
                let aHtml = '';
                attachments.forEach(a => {
                    aHtml += `
                        <div class="d-flex align-items-center justify-content-between p-2 mb-1 bg-light rounded border">
                            <div class="small text-truncate" style="max-width: 200px;">
                                <i class="fas fa-file-alt text-primary mr-1"></i> ${a.name}
                            </div>
                            <a href="${a.url}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-download"></i></a>
                        </div>
                    `;
                });
                $('#attachmentsList').html(aHtml);
            }
        }
    });
}

function submitQuickStatusUpdate() {
    if(!currentDetailTaskId) return;
    let newStatus = $('#quickStatusSelect').val();

    $.post('/hrms/task/' + currentDetailTaskId + '/update_status', {
        _token: '{{ csrf_token() }}',
        status: newStatus
    }, function(res) {
        if(res.status) {
            alert('Status updated!');
            openTaskDetailModal(currentDetailTaskId);
            location.reload();
        } else {
            alert(res.message || 'Error updating status');
        }
    }).fail(function(err) {
        alert(err.responseJSON ? err.responseJSON.message : 'Permission denied or update error.');
    });
}

function submitComment() {
    if(!currentDetailTaskId) return;
    let text = $('#commentInput').val().trim();
    if(!text) {
        alert('Please enter a comment.');
        return;
    }

    let formData = new FormData($('#commentForm')[0]);

    $.ajax({
        url: '/hrms/task/' + currentDetailTaskId + '/comment',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
            if(res.status) {
                $('#commentInput').val('');
                $('#attachmentInput').val('');
                openTaskDetailModal(currentDetailTaskId);
            }
        },
        error: function(err) {
            alert('Failed to post comment.');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('taskSearchForm');
    if (!form) return;

    const inputs = form.querySelectorAll('select, input[type="date"]');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            form.submit();
        });
    });

    const searchInput = form.querySelector('input[name="search"]');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                form.submit();
            }, 600);
        });

        if (searchInput.value) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }
    }
});
</script>
@endsection
