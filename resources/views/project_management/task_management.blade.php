@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@endsection

@section('_content')
@php
    if (!isset($employees)) {
        $employees = \App\Models\HRMS\Employee\EmployeeM::with(['user', 'employeeDetail'])->get();
    }
@endphp
@include('hrms.employee.partials.styles')

<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
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

    /* Premium Purple Gradient Hero Header */
    .task-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 26px !important;
        padding: 32px 36px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 28px !important;
        border: none !important;
    }

    .task-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 300px !important;
        height: 300px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .task-header-premium .title-area h3 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .task-header-premium .title-area p {
        font-size: 14px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .task-header-premium .header-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.75) !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* Premium Pill Buttons */
    .task-btn-pill {
        height: 42px !important;
        padding: 0 20px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        cursor: pointer !important;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
    }

    .task-btn-pill:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    /* Premium Status Tabs */
    .status-tab {
        border-radius: 50px !important;
        padding: 8px 20px !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        transition: all 0.2s ease !important;
        border: 1px solid var(--orb-border) !important;
        color: var(--orb-muted) !important;
        background: #fff !important;
        display: inline-flex !important;
        align-items: center !important;
        text-decoration: none !important;
        margin-right: 10px;
    }

    .status-tab.active {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15) !important;
    }

    .status-tab:hover:not(.active) {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, 0.15) !important;
    }

    /* Attached Filters */
    .task-filters-attached {
        background: #F8FAFC !important;
        border-bottom: 1px solid var(--orb-border) !important;
        padding: 20px 24px !important;
    }

    .task-filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        align-items: flex-end;
    }

    .task-filter-grid label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .task-filter-grid .form-control {
        height: 40px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        width: 100% !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .task-filter-grid .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    /* Table custom styles */
    .task-badge-premium {
        font-size: 10px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        padding: 5px 12px !important;
        border-radius: 50px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
    }

    .badge-pending-premium { background: #FFF7ED !important; color: #C2410C !important; }
    .badge-in_progress-premium { background: #EFF6FF !important; color: #1D4ED8 !important; }
    .badge-completed-premium { background: #ECFDF5 !important; color: #047857 !important; }

    .avatar-sm-premium {
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        border: 2px solid #fff !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }

    /* Modal premium theme overrides */
    .modal-content {
        border-radius: 24px !important;
        border: none !important;
        box-shadow: 0 20px 50px rgba(16, 24, 40, 0.15) !important;
        overflow: hidden !important;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border-bottom: none !important;
        padding: 24px !important;
    }

    .modal-header .modal-title {
        font-weight: 900 !important;
        font-size: 20px !important;
    }

    .modal-body {
        padding: 28px !important;
        background: #fff !important;
    }

    .modal-footer {
        padding: 16px 28px !important;
        background: #F8FAFC !important;
        border-top: 1px solid var(--orb-border) !important;
    }

    /* Compact Fields in modal */
    .modal-body label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .modal-body .form-control,
    .modal-body .custom-select {
        height: 40px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
    }

    .modal-body textarea.form-control {
        height: auto !important;
    }

    .modal-body .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    /* select color fixes */
    select,
    select option,
    .form-select,
    .form-select option {
        color: #101828 !important;
        background: #fff !important;
    }

    .select2-container .select2-selection__rendered {
        color: #101828 !important;
    }

    /* Entries Dropdown CSS */
    .dataTables_length,
    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
        margin: 0 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: var(--orb-muted) !important;
    }

    .dataTables_length select {
        width: auto !important;
        min-width: 64px !important;
        max-width: 80px !important;
        height: 34px !important;
        padding: 4px 24px 4px 10px !important;
        border-radius: 8px !important;
        border: 1px solid var(--orb-border) !important;
        outline: none !important;
    }

    /* Export button CSS */
    .orb-export-btn {
        height: 34px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        background: #fff !important;
        border: 1px solid #E7EAF3 !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        margin-left: 6px !important;
        transition: all 0.2s ease !important;
        color: #475467 !important;
    }

    .orb-export-btn:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
        transform: translateY(-1px) !important;
    }

    /* Scroll structure */
    .orb-table-scroll {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .orb-table-scroll table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .orb-table-scroll table thead th {
        background: #F8FAFC !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 14px 20px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .orb-table-scroll table tbody td {
        padding: 16px 20px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        background: #fff !important;
    }

    .orb-table-scroll table tbody tr:hover td {
        background: #FDFDFF !important;
    }

    @media (max-width: 1200px) {
        .task-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 991px) {
        .task-header-premium {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 24px !important;
        }
        .task-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .task-filter-grid {
            grid-template-columns: 1fr;
        }
        .task-btn-pill {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>

<div class="task-page">
    <div class="task-container">

        <!-- Premium Header Area -->
        <div class="task-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-tasks"></i> Productivity Center
                </div>
                <h3>Task Management Hub</h3>
                <p>Track, manage and report on team productivity.</p>
            </div>

            <div class="d-flex align-items-center" style="gap:12px;">
                <button type="button" class="task-btn-pill bg-white text-primary border-0" data-toggle="modal" data-target="#createTaskModal" style="background: #fff !important; color: var(--orb-primary) !important;">
                    <i class="fas fa-plus"></i>
                    Create Task
                </button>

                <a href="{{ route('project_management.tasks.export', request()->query()) }}" class="task-btn-pill text-white" target="_blank">
                    <i class="fas fa-print"></i>
                    Generate Report
                </a>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="d-flex mb-4 overflow-auto pb-2" style="white-space: nowrap;">
            <a href="#" class="status-tab active" data-status="all">
                <i class="fas fa-list-ul mr-2"></i> ALL TASKS
            </a>
            <a href="#" class="status-tab" data-status="pending">
                <i class="fas fa-clock mr-2"></i> PENDING
            </a>
            <a href="#" class="status-tab" data-status="in_progress">
                <i class="fas fa-spinner fa-spin mr-2"></i> IN PROGRESS
            </a>
            <a href="#" class="status-tab" data-status="completed">
                <i class="fas fa-check-double mr-2"></i> COMPLETED
            </a>
        </div>

        <!-- Main Card -->
        <div class="card orb-table-card">

            <div class="att-card-header">
                <h5 class="att-card-title">Task List Records</h5>
                <p class="att-card-subtitle">Manage project deadlines, assignments and progress status.</p>
            </div>

            <!-- Attached Filters inside the Card -->
            <div class="task-filters-attached">
                <form id="taskFilterForm" onsubmit="return false;">
                    <div class="task-filter-grid">

                        <div>
                            <label>Search Task</label>
                            <input type="text" name="search" class="form-control" placeholder="Search title or details..." value="{{ request('search') }}">
                        </div>

                        <div>
                            <label>Employee</label>
                            <select name="user_id" class="form-control">
                                <option value="all">All Staff</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->user->id }}" {{ request('user_id') == $emp->user->id ? 'selected' : '' }}>
                                        {{ $emp->employeeDetail->name ?? $emp->name }}
                                    </option>
                                @endforeach
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

                        <div class="d-flex align-items-center" style="gap:8px;">
                            <button type="button" class="btn btn-undo btn-secondary d-flex align-items-center justify-content-center w-100" style="height: 40px !important; border-radius: 9px !important;">
                                <i class="fas fa-undo mr-2"></i> Reset Filters
                            </button>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="att-table-wrap">
                <div class="orb-table-scroll">
                    <table class="table mb-0" id="taskTable">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="pl-4">ID</th>
                                <th>Task Information</th>
                                <th>Assigned To</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th class="text-right pr-4 no-export">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tasks as $task)
                                <tr id="row-{{ $task->id }}">
                                    <td class="font-weight-bold text-muted pl-4">#{{ $task->id }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark mb-1" style="font-size: 14px;">{{ $task->title }}</div>
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
                                                 class="avatar-sm-premium mr-2">
                                            <div>
                                                <div class="font-weight-bold small text-dark">{{ $task->user->employee->employeeDetail->name ?? ($task->user->name ?? 'N/A') }}</div>
                                                <div class="extra-small text-muted">{{ $task->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small font-weight-bold" style="font-size: 13px;">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ \Carbon\Carbon::parse($task->due_date)->format('d M, Y') }}
                                        </div>
                                        @php
                                            $isOverdue = \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status != 'completed';
                                        @endphp
                                        @if($isOverdue)
                                            <span class="badge badge-danger text-uppercase px-2 py-1 mt-1" style="font-size: 9px; border-radius: 30px;">Overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="task-badge-premium badge-{{ $task->status }}-premium">
                                            <i class="fas fa-{{ $task->status == 'completed' ? 'check-circle' : ($task->status == 'in_progress' ? 'spinner fa-spin' : 'hourglass-half') }}"></i>
                                            {{ str_replace('_', ' ', $task->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right pr-4">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light text-primary hover-shadow" data-toggle="modal" data-target="#editTaskModal{{ $task->id }}" title="Edit Task" style="border-radius: 50% !important; width: 32px; height: 32px; padding: 0 !important; display: inline-flex; align-items: center; justify-content: center; background: #f8f9fc;">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('project_management.tasks.destroy', $task->id) }}" method="POST" id="deleteForm-{{ $task->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-light text-danger hover-shadow ml-1" onclick="confirmDelete({{ $task->id }})" title="Delete Task" style="border-radius: 50% !important; width: 32px; height: 32px; padding: 0 !important; display: inline-flex; align-items: center; justify-content: center; background: #f8f9fc;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <!-- Empty state handled by DataTables -->
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ==================================================
     CREATE TASK MODAL
     ================================================== -->
<div class="modal fade" id="createTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Create New Task</h5>
                    <p class="mb-0 text-white-50 small">Assign project work and track progress</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('project_management.tasks.create') }}" method="POST" id="createTaskForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label>Task Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Design UI/UX for HRMS" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label>Assign To <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control custom-select" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->user->id }}">
                                            {{ $employee->employeeDetail->name ?? $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label>Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label>Priority/Status</label>
                                <select name="status" class="form-control custom-select">
                                    <option value="pending" selected>Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Detailed Instructions</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Describe the task details here..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish Task Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================================================
     EDIT TASK MODALS (One per task for safety)
     ================================================== -->
@foreach ($tasks as $task)
<div class="modal fade" id="editTaskModal{{ $task->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Edit Task #{{ $task->id }}</h5>
                    <p class="mb-0 text-white-50 small">Modify task details and tracking status</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('project_management.tasks.update', $task->id) }}" method="POST">
                @csrf
                @method('POST')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label>Task Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Design UI/UX for HRMS" value="{{ old('title', $task->title) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label>Assign To <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control custom-select" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->user->id }}" {{ $task->user_id == $employee->user->id ? 'selected' : '' }}>
                                            {{ $employee->employeeDetail->name ?? $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label>Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $task->due_date) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label>Priority/Status</label>
                                <select name="status" class="form-control custom-select">
                                    <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Detailed Instructions</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Describe the task details here...">{{ old('description', $task->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Task Info</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    function confirmDelete(taskId) {
        if (confirm("Are you sure you want to delete this task? This action cannot be undone.")) {
            document.getElementById('deleteForm-' + taskId).submit();
        }
    }

    // Vanilla JS Date Parser
    function parseDate(dateStr) {
        if (!dateStr) return null;
        var parts = dateStr.replace(/,/g, '').split(' ');
        if (parts.length === 3) {
            var day = parseInt(parts[0]);
            var months = {jan:0,feb:1,mar:2,apr:3,may:4,jun:5,jul:6,aug:7,sep:8,oct:9,nov:10,dec:11};
            var month = months[parts[1].toLowerCase().substring(0,3)];
            var year = parseInt(parts[2]);
            return new Date(year, month, day);
        }
        return new Date(dateStr);
    }

    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#taskTable')) {
            $('#taskTable').DataTable().destroy();
        }

        var table = $('#taskTable').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: false,
            autoWidth: false,
            scrollX: false,
            dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
                "<'row'<'col-md-12't>>" +
                "<'row align-items-center mt-3 px-4 pb-4'<'col-md-5'i><'col-md-7'p>>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-info"></i> CSV',
                    className: 'orb-export-btn',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'orb-export-btn',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'orb-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Task List Records',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'orb-export-btn',
                    title: 'Task List Records',
                    exportOptions: { columns: ':not(.no-export)' }
                }
            ],
            language: {
                emptyTable: 'No records found.',
                zeroRecords: 'No matching records found.',
                paginate: {
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>'
                }
            }
        });

        // Search Task keyup
        $('input[name="search"]').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Employee change
        $('select[name="user_id"]').on('change', function() {
            var val = $(this).val();
            if (val === 'all') {
                table.column(2).search('').draw();
            } else {
                var text = $(this).find('option:selected').text().trim();
                table.column(2).search(text).draw();
            }
        });

        // Custom Date range filter
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'taskTable') return true;
                
                var min = $('input[name="start_date"]').val();
                var max = $('input[name="end_date"]').val();
                var dateStr = data[3];
                
                if (!dateStr) return true;
                
                var date = parseDate(dateStr);
                if (!date || isNaN(date.getTime())) return true;
                
                if (min) {
                    var minDate = new Date(min);
                    minDate.setHours(0,0,0,0);
                    if (date < minDate) return false;
                }
                if (max) {
                    var maxDate = new Date(max);
                    maxDate.setHours(23,59,59,999);
                    if (date > maxDate) return false;
                }
                return true;
            }
        );

        $('input[name="start_date"], input[name="end_date"]').on('change', function() {
            table.draw();
        });

        // Status Tabs click
        $('.status-tab').on('click', function(e) {
            e.preventDefault();
            $('.status-tab').removeClass('active');
            $(this).addClass('active');
            
            var status = $(this).data('status');
            if (status === 'all') {
                table.column(4).search('').draw();
            } else {
                table.column(4).search(status).draw();
            }
        });

        // Reset button visible
        $('.btn-undo').on('click', function(e) {
            e.preventDefault();
            $('input[name="search"]').val('');
            $('select[name="user_id"]').val('all');
            $('input[name="start_date"]').val('');
            $('input[name="end_date"]').val('');
            $('.status-tab[data-status="all"]').trigger('click');
            table.search('').columns().search('').draw();
        });
    });
</script>
@endsection
