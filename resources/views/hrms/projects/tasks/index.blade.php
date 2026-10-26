@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Project Tasks')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F6F7FB;
    --orb-card: #FFFFFF;
    --orb-border: #E7EAF3;
    --orb-text: #101828;
    --orb-muted: #667085;
    --orb-soft: #F4F2FF;
    --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
}

.prj-page {
    padding: 14px 16px 36px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
    font-family: 'Outfit', sans-serif;
}

.prj-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

/* Signature Hero Banner */
.prj-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px;
    padding: 22px 28px;
    margin-bottom: 20px;
    color: #ffffff;
    box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    overflow: hidden;
}

.prj-hero:before {
    content: "";
    position: absolute;
    right: -60px;
    top: -80px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.prj-hero-kicker {
    font-size: 11.5px;
    font-weight: 850;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .92;
    margin-bottom: 6px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.prj-hero h1 {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    color: #ffffff;
    line-height: 1.15;
}

.prj-hero p {
    margin: 4px 0 0;
    font-size: 13px;
    opacity: 0.92;
}

.prj-btn-glass {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.38) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border-radius: 999px !important;
    padding: 8px 18px !important;
    font-size: 12.5px !important;
    font-weight: 750 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    transition: all 0.2s ease !important;
}

.prj-btn-glass:hover {
    background: rgba(255, 255, 255, 0.32) !important;
    border-color: rgba(255, 255, 255, 0.65) !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

/* Metric KPI Cards Grid */
.team-metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.team-metric-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    padding: 14px 14px 10px;
    box-shadow: 0 10px 24px rgba(16, 24, 40, .055);
    position: relative;
    overflow: hidden;
    min-height: 92px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.team-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(16, 24, 40, .09);
}

.team-metric-card:after {
    content: "";
    position: absolute;
    right: -22px;
    top: -30px;
    width: 86px;
    height: 86px;
    border-radius: 50%;
    background: var(--metric-soft, #F4F2FF);
    pointer-events: none;
}

.team-metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    position: relative;
    z-index: 1;
}

.team-metric-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: var(--metric-soft, #F4F2FF);
    color: var(--metric-color, var(--orb-primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.team-metric-value {
    font-size: 24px;
    font-weight: 900;
    color: #101828;
    line-height: 1;
}

.team-metric-label {
    font-size: 11px;
    font-weight: 850;
    color: #475467;
    text-transform: uppercase;
    margin-top: 12px;
    position: relative;
    z-index: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: 0.03em;
}

.team-metric-line {
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--metric-color, var(--orb-primary)), transparent);
    margin-top: 8px;
}

/* Card & Table */
.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.prj-card-head {
    padding: 16px 22px;
    background: linear-gradient(180deg, #FFFFFF, #FAFBFF);
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.prj-card-title {
    font-size: 16.5px;
    font-weight: 850;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.prj-card-sub {
    font-size: 12px;
    color: var(--orb-muted);
    font-weight: 550;
    margin-top: 2px;
}

.prj-filter-body {
    padding: 18px 22px;
    background: #FFFFFF;
}

.prj-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    align-items: end;
}

.prj-filter-group label {
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    color: #667085;
    margin-bottom: 6px;
    display: block;
    letter-spacing: .04em;
}

.prj-filter-group .form-control {
    height: 42px;
    border-radius: 12px;
    border: 1px solid #E4E7EC;
    font-size: 13px;
    font-weight: 650;
    padding: 0 12px;
    box-shadow: none !important;
}

.prj-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.prj-table thead th {
    background: #F8FAFC !important;
    color: #475467 !important;
    font-size: 11px !important;
    font-weight: 850 !important;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 12px 16px !important;
    border-bottom: 1px solid #E2E8F0 !important;
    border-top: none !important;
    white-space: nowrap;
}

.prj-table tbody td {
    padding: 13px 16px !important;
    vertical-align: middle !important;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    border-bottom: 1px solid #F1F5F9;
    background: #FFFFFF;
}

.prj-table tbody tr:hover td {
    background: #F8FAFC !important;
}

/* Select2 Skin */
.select2-container--default .select2-selection--single {
    height: 42px !important;
    border: 1px solid #E4E7EC !important;
    border-radius: 12px !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 10px !important;
    background-color: #fff !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 40px !important;
    font-size: 13px !important;
    font-weight: 650 !important;
    color: #101828 !important;
    padding-left: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 8px !important;
}

.select2-dropdown {
    border: 1px solid #E4E7EC !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    font-size: 13px !important;
    font-weight: 550 !important;
    z-index: 9999 !important;
}

@media (max-width: 768px) {
    .prj-page { padding: 10px 10px 30px; }
    .prj-hero { padding: 18px 16px; border-radius: 18px; }
    .prj-hero h1 { font-size: 20px; }
    .team-metric-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">

        <!-- Hero Header -->
        <div class="prj-hero">
            <div>
                <div class="prj-hero-kicker">
                    <i class="fas fa-tasks"></i> Project Management &bull; Tasks Board
                </div>
                <h1>Project Tasks</h1>
                <p>Track task assignments, progress percentage, priorities, and deadlines across enterprise projects.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn text-dark font-weight-bold px-4 py-2 bg-white shadow-sm" style="border-radius: 999px;" data-toggle="modal" data-target="#createTaskModal">
                    <i class="fas fa-plus-circle text-primary mr-1"></i> Create Task
                </button>
                <a href="{{ route('projects.index') }}" class="prj-btn-glass">
                    <i class="fas fa-layer-group"></i> Projects Directory
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards Grid -->
        <div class="team-metric-grid">
            <div class="team-metric-card" style="--metric-color:#4B00E8;--metric-soft:#F4F2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-tasks"></i></div>
                    <div class="team-metric-value">{{ $tasks->total() }}</div>
                </div>
                <div class="team-metric-label">Total Tasks</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric-card" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="team-metric-value text-success">{{ $tasks->where('status', 'completed')->count() }}</div>
                </div>
                <div class="team-metric-label">Completed on Page</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric-card" style="--metric-color:#D97706;--metric-soft:#FFFBEB;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-spinner"></i></div>
                    <div class="team-metric-value text-warning">{{ $tasks->where('status', 'in_progress')->count() }}</div>
                </div>
                <div class="team-metric-label">In Progress on Page</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 16px;" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="prj-card">
            <div class="prj-card-head">
                <div>
                    <h5 class="prj-card-title"><i class="fas fa-filter text-primary"></i> Filter Project Tasks</h5>
                    <div class="prj-card-sub">Filter by project, priority level or current execution status.</div>
                </div>
            </div>
            <div class="prj-filter-body">
                <form method="GET" action="{{ route('projects.tasks.index') }}">
                    <div class="prj-filter-grid">
                        <div class="prj-filter-group">
                            <label><i class="fas fa-project-diagram text-primary mr-1"></i> Project</label>
                            <select name="project_id" class="form-control select2-searchable">
                                <option value="">-- All Projects --</option>
                                @foreach(($projects ?? \App\Models\HRMS\ProjectManagement\ProjectM::all()) as $prj)
                                    <option value="{{ $prj->id }}" {{ request('project_id') == $prj->id ? 'selected' : '' }}>{{ $prj->name }} ({{ $prj->project_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="prj-filter-group">
                            <label><i class="fas fa-exclamation-triangle text-primary mr-1"></i> Priority</label>
                            <select name="priority" class="form-control select2-searchable">
                                <option value="">-- All Priorities --</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="prj-filter-group">
                            <label><i class="fas fa-info-circle text-primary mr-1"></i> Status</label>
                            <select name="status" class="form-control select2-searchable">
                                <option value="">-- All Statuses --</option>
                                <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>Todo</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="prj-filter-group d-flex align-items-end" style="gap: 8px;">
                            <button type="submit" class="btn font-weight-bold text-white shadow-sm" style="height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('projects.tasks.index') }}" class="btn btn-light" style="height: 42px; width: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 750; border: 1px solid #E4E7EC; background: #fff; color: #344054; flex-shrink: 0;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tasks Table -->
        <div class="prj-card">
            <div class="table-responsive">
                <table class="table prj-table mb-0">
                    <thead>
                        <tr>
                            <th>Task Title</th>
                            <th>Project</th>
                            <th>Assigned Employee</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress %</th>
                            <th>Due Date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td>
                                <strong class="text-dark d-block" style="font-size: 13.5px;">{{ $task->title }}</strong>
                                <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                            </td>
                            <td><span class="badge badge-light border font-weight-bold" style="font-size: 11.5px; border-radius: 6px;">{{ optional($task->project)->name ?? 'N/A' }}</span></td>
                            <td><strong class="text-dark" style="font-size: 13px;">{{ optional(optional($task->assignedEmployee)->user)->name ?? 'Unassigned' }}</strong></td>
                            <td>
                                @php
                                    $prioBg = '#EDE9FE'; $prioColor = '#5B21B6';
                                    if ($task->priority == 'urgent') { $prioBg = '#FEF2F2'; $prioColor = '#DC2626'; }
                                    elseif ($task->priority == 'high') { $prioBg = '#FFFBEB'; $prioColor = '#B45309'; }
                                @endphp
                                <span class="badge font-weight-bold text-uppercase px-2.5 py-1" style="background: {{ $prioBg }}; color: {{ $prioColor }}; border-radius: 6px; font-size: 10.5px;">
                                    {{ strtoupper($task->priority) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $stBg = '#EFF6FF'; $stColor = '#1D4ED8';
                                    if ($task->status == 'completed') { $stBg = '#ECFDF5'; $stColor = '#047857'; }
                                    elseif ($task->status == 'blocked') { $stBg = '#FEF2F2'; $stColor = '#DC2626'; }
                                @endphp
                                <span class="badge font-weight-bold text-uppercase px-2.5 py-1" style="background: {{ $stBg }}; color: {{ $stColor }}; border-radius: 20px; font-size: 10.5px;">
                                    {{ str_replace('_', ' ', strtoupper($task->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-fill" style="height: 6px; border-radius: 999px; min-width: 70px; background: #EEF2F6;">
                                        <div class="progress-bar bg-success" style="width: {{ $task->progress_percentage }}%; border-radius: 999px;"></div>
                                    </div>
                                    <small class="font-weight-bold text-dark">{{ $task->progress_percentage }}%</small>
                                </div>
                            </td>
                            <td>
                                <span class="small font-weight-bold text-muted">
                                    <i class="far fa-calendar-alt text-muted mr-1"></i> {{ $task->due_date ? $task->due_date->format('d M Y') : 'N/A' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <button class="btn btn-sm btn-outline-primary px-3 font-weight-bold" style="border-radius: 8px;" data-toggle="modal" data-target="#updateStatusModal{{ $task->id }}">
                                    <i class="fas fa-edit mr-1"></i> Status
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
                                                        <select name="status" class="form-control select2-searchable">
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
                                                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div style="width: 70px; height: 70px; border-radius: 20px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px;">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark mb-1">No Project Tasks Found</h5>
                                <p class="small text-muted mb-0">Create new tasks to assign work packages across project teams.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 bg-white border-top">
                {{ $tasks->links('vendor.pagination.orbo') }}
            </div>
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
                            <select name="project_id" class="form-control select2-modal" required style="width: 100%;">
                                <option value="">-- Select Project --</option>
                                @foreach(($projects ?? \App\Models\HRMS\ProjectManagement\ProjectM::where('status', 'active')->get()) as $prj)
                                    <option value="{{ $prj->id }}">{{ $prj->name }} ({{ $prj->project_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Assigned Employee</label>
                            <select name="assigned_employee_id" class="form-control select2-modal" style="width: 100%;">
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
                            <select name="priority" class="form-control select2-searchable">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Status</label>
                            <select name="status" class="form-control select2-searchable">
                                <option value="todo" selected>Todo</option>
                                <option value="in_progress">In Progress</option>
                                <option value="blocked">Blocked</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Due Date</label>
                        <x-form.date-picker name="due_date" placeholder="Due Date" />
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

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-searchable').select2({
            width: '100%',
            dropdownAutoWidth: true
        });
    }

    $('#createTaskModal').on('shown.bs.modal', function () {
        $('.select2-modal').select2({
            dropdownParent: $('#createTaskModal'),
            width: '100%'
        });
    });
});
</script>
@endsection
