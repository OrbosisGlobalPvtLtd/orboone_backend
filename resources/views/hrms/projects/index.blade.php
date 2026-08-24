@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Projects Directory')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
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

.prj-hero h1 {
    font-size: 26px;
    font-weight: 800;
    margin: 0;
    color: #ffffff;
    letter-spacing: -0.02em;
}

.prj-hero p {
    margin: 6px 0 0;
    font-size: 14px;
    opacity: 0.92;
}

.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.prj-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
}

.prj-card-header {
    padding: 20px 20px 14px;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.prj-code-badge {
    background: var(--orb-soft);
    color: var(--orb-primary);
    font-weight: 700;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 8px;
    letter-spacing: 0.03em;
}

.prj-status-badge {
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.05em;
}

.status-active { background: #DCFCE7; color: #15803D; }
.status-planning { background: #FEF3C7; color: #B45309; }
.status-on_hold { background: #FFEDD5; color: #C2410C; }
.status-completed { background: #DBEAFE; color: #1D4ED8; }
.status-archived { background: #F1F5F9; color: #475569; }

.prj-card-body {
    padding: 16px 20px;
    flex: 1;
}

.prj-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--orb-text);
    margin-bottom: 8px;
    line-height: 1.3;
}

.prj-desc {
    font-size: 13px;
    color: var(--orb-muted);
    line-height: 1.5;
    margin-bottom: 16px;
}

.prj-meta-item {
    font-size: 13px;
    color: var(--orb-text);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.prj-meta-item i {
    color: var(--orb-muted);
    width: 16px;
}

.prj-progress-wrapper {
    margin-top: 14px;
}

.prj-progress-bar {
    height: 8px;
    border-radius: 4px;
    background: #E2E8F0;
    overflow: hidden;
}

.prj-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--orb-primary), var(--orb-secondary));
    border-radius: 4px;
    transition: width 0.4s ease;
}

.prj-card-footer {
    padding: 14px 20px;
    background: #FAFAFC;
    border-top: 1px solid var(--orb-border);
    border-radius: 0 0 16px 16px;
    display: flex;
    gap: 8px;
    justify-content: space-between;
}

.filter-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: var(--orb-shadow);
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <div>
                <h1><i class="fas fa-project-diagram mr-2"></i>Project Directory</h1>
                <p>Manage enterprise projects, delivery hierarchy, team assignments, and tracking.</p>
            </div>
            <div>
                @if(auth()->user()->hasPermission('projects.create') || auth()->user()->isSuperAdmin())
                <button type="button" class="btn btn-light font-weight-bold px-4 py-2" style="border-radius: 12px; color: var(--orb-primary);" data-toggle="modal" data-target="#createProjectModal">
                    <i class="fas fa-plus mr-2"></i>Create Project
                </button>
                @endif
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px;" role="alert">
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
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px;" role="alert">
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
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0" placeholder="Search by Project Name, Code, or Client..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 my-1">
                    <select name="status" class="form-control select2">
                        <option value="">-- All Statuses --</option>
                        <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-4 my-1 text-right">
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('projects.index') }}" class="btn btn-light border px-3 ml-1" style="border-radius: 10px;">
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
                        <span class="prj-code-badge">{{ $project->project_code }}</span>
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
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Progress</span>
                                <span class="font-weight-bold" style="color: var(--orb-primary);">{{ $project->progress_percentage }}%</span>
                            </div>
                            <div class="prj-progress-bar">
                                <div class="prj-progress-fill" style="width: {{ $project->progress_percentage }}%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="prj-card-footer">
                        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold flex-fill mr-2" style="border-radius: 8px;">
                            <i class="fas fa-eye mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('projects.hierarchy', $project->id) }}" class="btn btn-sm btn-outline-info font-weight-bold flex-fill" style="border-radius: 8px;">
                            <i class="fas fa-sitemap mr-1"></i> Hierarchy Tree
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 16px;">
                    <div class="card-body">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h4 class="font-weight-bold text-dark mb-2">No Projects Found</h4>
                        <p class="text-muted">No projects match the selected criteria or no projects have been created yet.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $projects->links() }}
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
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
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
<script>
$(document).ready(function() {
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
