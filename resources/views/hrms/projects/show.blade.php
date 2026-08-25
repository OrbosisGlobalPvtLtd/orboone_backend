@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Project Dashboard - ' . $project->name)

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
    --orb-soft: rgba(75, 0, 232, 0.08);
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

/* Hero Header Banner */
.prj-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 32px 36px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.prj-hero-info {
    max-width: 700px;
}

.prj-hero-badges {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.prj-hero-status {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 20px;
    letter-spacing: 0.06em;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.prj-hero-code {
    background: rgba(0, 0, 0, 0.2);
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 10px;
    letter-spacing: 0.04em;
}

.prj-hero h1 {
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 10px 0;
    color: #ffffff;
    letter-spacing: -0.02em;
    line-height: 1.2;
}

.prj-hero-meta {
    font-size: 14px;
    opacity: 0.95;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.prj-hero-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.prj-hero-btn {
    background: #ffffff;
    color: var(--orb-primary);
    font-weight: 700;
    font-size: 13.5px;
    padding: 10px 18px;
    border-radius: 12px;
    border: none;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    text-decoration: none !important;
}

.prj-hero-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
    color: var(--orb-primary);
    background: #ffffff;
}

/* Metric KPI Cards */
.prj-metric-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 22px;
    box-shadow: var(--orb-shadow);
    display: flex;
    align-items: center;
    gap: 18px;
    transition: transform 0.2s ease;
}

.prj-metric-card:hover {
    transform: translateY(-2px);
}

.prj-metric-icon {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    background: var(--orb-soft);
    color: var(--orb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}

/* Section Cards & Tables */
.prj-section-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.prj-section-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--orb-border);
    background: #FAFAFC;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.prj-section-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
}

.prj-section-title i {
    margin-right: 10px;
    color: var(--orb-primary);
}

.role-badge-delivery_head { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
.role-badge-team_lead { background: #E0E7FF; color: #3730A3; border: 1px solid #A5B4FC; }
.role-badge-team_member { background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; }
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <div class="prj-hero-info">
                <div class="prj-hero-badges">
                    <span class="prj-hero-status">{{ str_replace('_', ' ', $project->status) }}</span>
                    <span class="prj-hero-code"><i class="fas fa-barcode mr-1"></i> {{ $project->project_code }}</span>
                </div>
                <h1><i class="fas fa-project-diagram mr-2"></i>{{ $project->name }}</h1>
                <div class="prj-hero-meta">
                    <span><i class="fas fa-building mr-1 opacity-80"></i> Client: <strong>{{ $project->client_name ?? 'N/A' }}</strong></span>
                    <span><i class="fas fa-user-tie mr-1 opacity-80"></i> Delivery Head: <strong>{{ $project->delivery_head_display_name }}</strong></span>
                </div>
            </div>
            <div class="prj-hero-actions">
                <a href="{{ route('projects.hierarchy', $project->id) }}" class="prj-hero-btn">
                    <i class="fas fa-sitemap mr-2"></i> Hierarchy Tree
                </a>
                @if($canManageProject ?? false)
                <button type="button" class="prj-hero-btn" data-toggle="modal" data-target="#assignMemberModal">
                    <i class="fas fa-user-plus mr-2"></i> Assign Member
                </button>
                <button type="button" class="prj-hero-btn" data-toggle="modal" data-target="#createTeamModal">
                    <i class="fas fa-users-cog mr-2"></i> Create Team
                </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 14px;" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <!-- Metric KPI Cards -->
        <div class="row mb-4">
            <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
                <div class="prj-metric-card">
                    <div class="prj-metric-icon"><i class="fas fa-users-cog"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.04em;">Sub-Teams</div>
                        <div class="h3 font-weight-extrabold mb-0 text-dark">{{ $project->teams->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
                <div class="prj-metric-card">
                    <div class="prj-metric-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;"><i class="fas fa-user-friends"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.04em;">Active Members</div>
                        <div class="h3 font-weight-extrabold mb-0 text-dark">{{ $project->activeAssignments->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
                <div class="prj-metric-card">
                    <div class="prj-metric-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;"><i class="fas fa-tasks"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.04em;">Total Tasks</div>
                        <div class="h3 font-weight-extrabold mb-0 text-dark">{{ $taskStats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="prj-metric-card">
                    <div class="prj-metric-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.04em;">Overall Progress</div>
                        <div class="h3 font-weight-extrabold mb-0 text-dark">{{ $taskStats['progress_percentage'] }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Teams Section -->
                <div class="prj-section-card">
                    <div class="prj-section-header">
                        <h5 class="prj-section-title"><i class="fas fa-users-cog"></i>Project Sub-Teams</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-4">Team Name</th>
                                    <th class="py-3">Team Lead</th>
                                    <th class="py-3">Active Members</th>
                                    <th class="py-3 px-4">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->teams as $team)
                                <tr>
                                    <td class="py-3 px-4"><strong class="text-dark font-weight-bold">{{ $team->team_name }}</strong></td>
                                    <td class="py-3">
                                        @if($team->teamLead)
                                            <span class="badge badge-info px-3 py-1 font-weight-bold" style="border-radius: 8px;"><i class="fas fa-user-shield mr-1"></i> {{ $team->teamLead->display_name }}</span>
                                        @else
                                            <span class="text-muted small">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="py-3"><span class="badge badge-light border font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ $team->activeAssignments->count() }} members</span></td>
                                    <td class="py-3 px-4"><span class="text-muted small">{{ $team->description ?? 'N/A' }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No sub-teams created yet. Member assignments belong directly to the project.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Active Members Section -->
                <div class="prj-section-card">
                    <div class="prj-section-header">
                        <h5 class="prj-section-title"><i class="fas fa-users"></i>Assigned Project Members</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-4">Employee</th>
                                    <th class="py-3">Project Role</th>
                                    <th class="py-3">Sub-Team</th>
                                    <th class="py-3">HR Designation</th>
                                    <th class="py-3">Assigned Date</th>
                                    @if($canManageProject ?? false)
                                    <th class="py-3 px-4 text-right">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->activeAssignments as $assign)
                                <tr>
                                    <td class="py-3 px-4">
                                        <strong class="text-dark font-weight-bold">{{ optional($assign->employee)->display_name }}</strong>
                                        <div class="small text-muted">{{ optional($assign->employee)->employee_code }}</div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge role-badge-{{ $assign->project_role }} font-weight-bold text-uppercase px-3 py-1" style="border-radius: 8px;">
                                            {{ str_replace('_', ' ', $assign->project_role) }}
                                        </span>
                                    </td>
                                    <td class="py-3"><span class="font-weight-bold text-secondary small">{{ optional($assign->team)->team_name ?? 'Direct Member' }}</span></td>
                                    <td class="py-3"><span class="small text-muted">{{ optional(optional($assign->employee)->designation)->name ?? 'N/A' }}</span></td>
                                    <td class="py-3"><span class="small text-muted">{{ $assign->assigned_at ? $assign->assigned_at->format('d M Y') : 'N/A' }}</span></td>
                                    @if($canManageProject ?? false)
                                    <td class="py-3 px-4 text-right">
                                        <form action="{{ route('projects.relieve', $assign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Relieve employee from this project?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger px-3 font-weight-bold" style="border-radius: 8px;"><i class="fas fa-user-minus mr-1"></i> Relieve</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No active members assigned to this project yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Side Card: Project Details -->
            <div class="col-lg-4">
                <div class="prj-section-card">
                    <div class="prj-section-header">
                        <h5 class="prj-section-title"><i class="fas fa-info-circle"></i>Project Metadata</h5>
                    </div>
                    <div class="p-4">
                        <p class="text-secondary small mb-4 line-height-16">{{ $project->description ?? 'No project overview entered.' }}</p>
                        <hr class="my-3">
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted small">Client Name:</span> <strong class="text-dark small">{{ $project->client_name ?? 'N/A' }}</strong></div>
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted small">Delivery Head:</span> <strong class="text-dark small">{{ $project->delivery_head_display_name }}</strong></div>
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted small">Start Date:</span> <strong class="text-dark small">{{ $project->start_date ? $project->start_date->format('d M Y') : 'N/A' }}</strong></div>
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted small">End Date:</span> <strong class="text-dark small">{{ $project->end_date ? $project->end_date->format('d M Y') : 'N/A' }}</strong></div>
                        <div class="mb-0 d-flex justify-content-between"><span class="text-muted small">Created Date:</span> <strong class="text-dark small">{{ $project->created_at ? $project->created_at->format('d M Y') : 'N/A' }}</strong></div>
                    </div>
                </div>

                <!-- Side Card: Technical Supervisors (Informational) -->
                <div class="prj-section-card">
                    <div class="prj-section-header">
                        <h5 class="prj-section-title"><i class="fas fa-user-shield text-info mr-2"></i>Technical Supervisors</h5>
                    </div>
                    <div class="p-4">
                        <p class="text-muted small mb-3">Technical Leads supervising developers assigned to this project (Informational).</p>
                        @if(isset($technicalSupervisors) && count($technicalSupervisors) > 0)
                            @foreach($technicalSupervisors as $tlId => $tlDevs)
                                @php $first = $tlDevs->first(); @endphp
                                <div class="mb-3 p-3 bg-light rounded" style="border-radius: 12px; border: 1px solid #E2E8F0;">
                                    <strong class="text-dark d-block font-weight-bold"><i class="fas fa-laptop-code text-primary mr-1"></i>{{ $first->tl_name }}</strong>
                                    <small class="text-muted d-block mb-2">{{ $first->tl_code }} &bull; {{ $first->designation_name ?? 'Technical Lead' }}</small>
                                    <div class="small text-muted font-weight-bold uppercase" style="font-size: 10px; letter-spacing: 0.04em;">SUPERVISED DEVELOPERS IN PROJECT:</div>
                                    <div class="mt-1">
                                        @foreach($tlDevs as $item)
                                            <span class="badge badge-white border text-dark font-weight-medium px-2 py-1 mb-1" style="border-radius: 6px;">{{ $item->dev_name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted small">No external Technical Lead supervision assigned for developers in this project.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form action="{{ route('projects.teams.store', $project->id) }}" method="POST">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-users-cog mr-2"></i>Create Project Team</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Team Name <span class="text-danger">*</span></label>
                        <input type="text" name="team_name" class="form-control" placeholder="e.g. API Backend Team" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Team Lead</label>
                        <select name="team_lead_employee_id" class="form-control select2">
                            <option value="">-- Select Team Lead --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->display_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief team description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;"><i class="fas fa-save mr-1"></i> Save Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Member Modal -->
<div class="modal fade" id="assignMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form action="{{ route('projects.assign', $project->id) }}" method="POST">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Assign Member to Project</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Select Employees <span class="text-danger">*</span></label>

                        <!-- Search Bar & Controls -->
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="employeeSearchInput" class="form-control border-left-0" placeholder="Type to search employee by name or code...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold px-3" id="selectAllEmployeesBtn">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3" id="deselectAllEmployeesBtn">Clear</button>
                            </div>
                        </div>

                        <!-- Checkbox Scroll Container -->
                        <div class="border rounded p-3 bg-white shadow-inner" id="employeeCheckboxContainer" style="max-height: 220px; overflow-y: auto; border-radius: 12px !important;">
                            @foreach($employees as $emp)
                            <div class="custom-control custom-checkbox py-1.5 border-bottom border-light employee-item">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="custom-control-input emp-checkbox" id="emp_cb_{{ $emp->id }}">
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer pl-1" for="emp_cb_{{ $emp->id }}" style="user-select: none;">
                                    {{ $emp->display_name }} <span class="text-muted font-weight-normal ml-1">({{ $emp->employee_code }})</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                            <small class="text-muted"><i class="fas fa-user-check text-success mr-1"></i><strong id="selectedEmpCount">0</strong> employee(s) selected</small>
                            <small class="text-danger font-weight-bold d-none" id="noEmpErrorMsg">Please select at least one employee.</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Project Team (Optional)</label>
                        <select name="project_team_id" class="form-control">
                            <option value="">-- Direct Member (No Sub-Team) --</option>
                            @foreach($project->teams as $team)
                                <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Project Role <span class="text-danger">*</span></label>
                        <select name="project_role" class="form-control" required>
                            <option value="team_member" selected>Team Member</option>
                            <option value="team_lead">Team Lead</option>
                            <option value="delivery_head">Delivery Head</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;"><i class="fas fa-check mr-1"></i> Assign Employees</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
$(document).ready(function() {
    // Live Search Filter
    $('#employeeSearchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#employeeCheckboxContainer .employee-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Count Selected Employees
    function updateSelectedCount() {
        var count = $('.emp-checkbox:checked').length;
        $('#selectedEmpCount').text(count);
        if (count > 0) {
            $('#noEmpErrorMsg').addClass('d-none');
        }
    }

    $(document).on('change', '.emp-checkbox', function() {
        updateSelectedCount();
    });

    // Select All Visible
    $('#selectAllEmployeesBtn').on('click', function() {
        $('#employeeCheckboxContainer .employee-item:visible .emp-checkbox').prop('checked', true);
        updateSelectedCount();
    });

    // Clear All
    $('#deselectAllEmployeesBtn').on('click', function() {
        $('.emp-checkbox').prop('checked', false);
        updateSelectedCount();
    });

    // Form Submission Check
    $('#assignMemberModal form').on('submit', function(e) {
        if ($('.emp-checkbox:checked').length === 0) {
            e.preventDefault();
            $('#noEmpErrorMsg').removeClass('d-none');
        } else {
            $('#noEmpErrorMsg').addClass('d-none');
        }
    });
});
</script>
@endsection
