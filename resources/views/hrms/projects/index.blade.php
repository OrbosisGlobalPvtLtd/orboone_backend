@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Projects Directory')

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

/* Filter Card */
.filter-card {
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    padding: 16px 20px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
}

/* Project Cards */
.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

.prj-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.09);
    border-color: #CBD5E1;
}

.prj-card-header {
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.prj-code-badge {
    background: var(--orb-soft);
    color: var(--orb-primary);
    font-weight: 850;
    font-size: 11.5px;
    padding: 4px 10px;
    border-radius: 8px;
    border: 1px solid #DDD6FE;
    letter-spacing: 0.03em;
}

.prj-status-badge {
    font-weight: 850;
    font-size: 10.5px;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.04em;
}

.status-active { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
.status-completed { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
.status-on_hold { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
.status-planning { background: #F5F3FF; color: #6D28D9; border: 1px solid #DDD6FE; }
.status-archived { background: #F1F5F9; color: #475467; border: 1px solid #E2E8F0; }

.prj-card-body {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.prj-title {
    font-size: 17px;
    font-weight: 850;
    color: var(--orb-text);
    margin-bottom: 6px;
    line-height: 1.3;
}

.prj-desc {
    color: var(--orb-muted);
    font-size: 12.5px;
    font-weight: 500;
    margin-bottom: 14px;
    line-height: 1.45;
}

.prj-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #475569;
    font-weight: 600;
    margin-bottom: 6px;
}

.prj-meta-item i {
    color: var(--orb-primary);
    width: 16px;
}

.prj-progress-wrapper {
    margin-top: auto;
    padding-top: 14px;
}

.prj-progress-bar {
    height: 6px;
    border-radius: 999px;
    background: #E2E8F0;
    overflow: hidden;
}

.prj-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--orb-primary), var(--orb-secondary));
    border-radius: 999px;
    transition: width 0.4s ease;
}

.prj-card-footer {
    padding: 14px 20px;
    background: #FAFBFF;
    border-top: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    gap: 8px;
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

        <!-- Signature Hero Banner -->
        <div class="prj-hero">
            <div>
                <div class="prj-hero-kicker">
                    <i class="fas fa-project-diagram"></i> Project Management &bull; Projects Directory
                </div>
                <h1>Projects Directory</h1>
                <p>Enterprise project management, team assignments, delivery tracking, and milestones.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(Auth::user()->hasRole(['super_admin', 'admin', 'hr_admin', 'project_manager', 'delivery_head']))
                <button type="button" class="btn text-dark font-weight-bold px-4 py-2 bg-white shadow-sm" style="border-radius: 999px;" data-toggle="modal" data-target="#createProjectModal">
                    <i class="fas fa-plus-circle text-primary mr-1"></i> New Project
                </button>
                @endif
                <a href="{{ route('projects.tasks.index') }}" class="prj-btn-glass">
                    <i class="fas fa-tasks"></i> Project Tasks
                </a>
                <a href="{{ route('reporting.projects') }}" class="prj-btn-glass">
                    <i class="fas fa-chart-line"></i> Reporting View
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards Grid -->
        <div class="team-metric-grid">
            <div class="team-metric-card" style="--metric-color:#4B00E8;--metric-soft:#F4F2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-cubes"></i></div>
                    <div class="team-metric-value">{{ $projects->total() }}</div>
                </div>
                <div class="team-metric-label">Total Projects</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric-card" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-play-circle"></i></div>
                    <div class="team-metric-value text-success">{{ $projects->where('status', 'active')->count() }}</div>
                </div>
                <div class="team-metric-label">Active on Page</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric-card" style="--metric-color:#6366F1;--metric-soft:#EEF2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-check-double"></i></div>
                    <div class="team-metric-value text-primary">{{ $projects->where('status', 'completed')->count() }}</div>
                </div>
                <div class="team-metric-label">Completed on Page</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 16px;" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i><strong>Unable to save project:</strong>
                <ul class="mb-0 mt-1 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 16px;" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('projects.index') }}" class="form-row align-items-center">
                <div class="col-md-5 my-1">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 12px 0 0 12px; height: 42px;"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0" style="border-radius: 0 12px 12px 0; height: 42px;" placeholder="Search by Project Name, Code, or Client..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 my-1">
                    <select name="status" class="form-control select2-searchable">
                        <option value="">-- All Statuses --</option>
                        <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-4 my-1 text-right">
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="height: 42px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; border-radius: 12px;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('projects.index') }}" class="btn btn-light border px-3 ml-1 font-weight-bold" style="height: 42px; border-radius: 12px; display: inline-flex; align-items: center;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Projects Grid -->
        <div class="row">
            @forelse($projects as $project)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="prj-card">
                    <div class="prj-card-header">
                        <span class="prj-code-badge"><i class="fas fa-tag"></i> {{ $project->project_code }}</span>
                        <span class="prj-status-badge status-{{ $project->status }}">{{ str_replace('_', ' ', $project->status) }}</span>
                    </div>
                    <div class="prj-card-body">
                        <div class="prj-title">{{ $project->name }}</div>
                        <div class="prj-desc">{{ Str::limit($project->description ?? 'No project description provided.', 85) }}</div>
                        
                        <div class="prj-meta-item">
                            <i class="fas fa-building"></i>
                            <span>Client: <strong>{{ $project->client_name ?? 'N/A' }}</strong></span>
                        </div>
                        <div class="prj-meta-item">
                            <i class="fas fa-user-tie"></i>
                            <span>Delivery Head: <strong>{{ $project->delivery_head_display_name }}</strong></span>
                        </div>
                        <div class="prj-meta-item">
                            <i class="fas fa-users-cog"></i>
                            <span>Teams: <strong>{{ $project->activeTeams->count() }}</strong> | Members: <strong>{{ $project->activeAssignments->count() }}</strong></span>
                        </div>

                        <div class="prj-progress-wrapper">
                            <div class="d-flex justify-content-between small text-muted mb-1 font-weight-bold">
                                <span>Progress</span>
                                <span style="color: var(--orb-primary);">{{ $project->progress_percentage }}%</span>
                            </div>
                            <div class="prj-progress-bar">
                                <div class="prj-progress-fill" style="width: {{ $project->progress_percentage }}%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="prj-card-footer">
                        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold flex-fill mr-2" style="border-radius: 10px;">
                            <i class="fas fa-eye mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('projects.hierarchy', $project->id) }}" class="btn btn-sm btn-outline-secondary font-weight-bold flex-fill" style="border-radius: 10px;">
                            <i class="fas fa-sitemap mr-1"></i> Hierarchy
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 20px;">
                    <div class="card-body">
                        <div style="width: 70px; height: 70px; border-radius: 20px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px;">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h4 class="font-weight-bold text-dark mb-2">No Projects Found</h4>
                        <p class="text-muted">No projects match the selected criteria or no projects have been created yet.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $projects->links('vendor.pagination.orbo') }}
        </div>
    </div>
</div>

<!-- Create Project Modal -->
<div class="modal fade" id="createProjectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Create New Project</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Project Code <small class="text-muted font-weight-normal">(Auto-generated)</small></label>
                            <input type="text" name="project_code" class="form-control bg-light" value="{{ $nextProjectCode ?? '' }}" placeholder="Auto-generated e.g. {{ $nextProjectCode ?? 'PRJ-2026-001' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. OrboOne HRMS" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Client Name</label>
                            <input type="text" name="client_name" class="form-control" placeholder="e.g. Internal / Enterprise Client">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Delivery Head</label>
                            <select name="delivery_head_employee_id" id="deliveryHeadSelect" class="form-control select2-dh" style="width: 100%;">
                                <option value="">-- Select Delivery Head --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->display_name }} ({{ $emp->employee_code }})</option>
                                @endforeach
                                <option value="custom" class="font-weight-bold text-primary">+ Other / Enter Custom Name...</option>
                            </select>
                            <div class="mt-2 d-none" id="customDeliveryHeadWrapper">
                                <label class="font-weight-bold text-primary small mb-1"><i class="fas fa-edit mr-1"></i>Enter Custom Delivery Head Name <span class="text-danger">*</span></label>
                                <input type="text" name="delivery_head_name" id="customDeliveryHeadInput" class="form-control" placeholder="e.g. Rahul Sharma (External)">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Start Date</label>
                            <x-form.date-picker name="start_date" placeholder="Start Date" />
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">End Date</label>
                            <x-form.date-picker name="end_date" placeholder="End Date" />
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control select2-searchable" required>
                                <option value="active" selected>Active</option>
                                <option value="planning">Planning</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief project description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;"><i class="fas fa-save mr-1"></i> Save Project</button>
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

    $('#createProjectModal').on('shown.bs.modal', function () {
        $('.select2-dh').select2({
            dropdownParent: $('#createProjectModal'),
            placeholder: '-- Select Delivery Head --',
            allowClear: true
        });
    });

    $(document).on('change', '#deliveryHeadSelect', function() {
        var val = $(this).val();
        if (val === 'custom') {
            $('#customDeliveryHeadWrapper').removeClass('d-none').hide().slideDown(200);
            $('#customDeliveryHeadInput').focus().prop('required', true);
        } else {
            $('#customDeliveryHeadWrapper').slideUp(150, function() {
                $(this).addClass('d-none');
            });
            $('#customDeliveryHeadInput').val('').prop('required', false);
        }
    });
});
</script>
@endsection
