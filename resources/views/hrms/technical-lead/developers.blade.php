@extends('layouts.panel', ['active' => 'technical_lead_developers'])

@section('page_title', 'Supervised Developers Roster')

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

.tl-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.tl-container {
    max-width: 1550px;
    margin: 0 auto;
}

.tl-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.tl-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}
</style>
@endsection

@section('_content')
<div class="tl-page">
    <div class="tl-container">
        <!-- Hero Header -->
        <div class="tl-hero">
            <div>
                <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-users-cog mr-2"></i>Supervised Developers Roster</h1>
                <p class="mb-0 opacity-90">Manage developers under Technical Lead supervision across enterprise projects.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light font-weight-bold px-4 py-2 mr-2" style="border-radius: 12px; color: var(--orb-primary);" data-toggle="modal" data-target="#assignDeveloperModal">
                    <i class="fas fa-user-plus mr-2"></i>Assign Developer
                </button>
                <button type="button" class="btn btn-outline-light font-weight-bold px-3 py-2" style="border-radius: 12px;" data-toggle="modal" data-target="#assignmentHistoryModal">
                    <i class="fas fa-history mr-1"></i> Assignment History
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
        <div class="tl-card p-4">
            <form method="GET" action="{{ route('technical_lead.developers') }}" class="form-row align-items-center">
                <div class="col-md-8 my-1">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0" placeholder="Search developer by name or employee code..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 my-1 text-right">
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">
                        <i class="fas fa-search mr-1"></i> Search
                    </button>
                    <a href="{{ route('technical_lead.developers') }}" class="btn btn-light border px-3 ml-1" style="border-radius: 10px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Assignments Table -->
        <div class="tl-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Developer</th>
                            <th class="py-3">HR Designation</th>
                            <th class="py-3">Current Project(s)</th>
                            <th class="py-3">Project Role & Team</th>
                            <th class="py-3">Technical Lead</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3">Assigned Since</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assign)
                        <tr>
                            <td class="py-3 px-4 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ optional($assign->employee)->display_name }}</strong>
                                <small class="text-muted">{{ optional($assign->employee)->employee_code }}</small>
                            </td>
                            <td class="py-3 align-middle">
                                <span class="small font-weight-bold text-muted">{{ optional(optional($assign->employee)->designation)->name ?? 'Developer' }}</span>
                            </td>
                            <td class="py-3 align-middle">
                                @if(!empty($assign->active_projects) && count($assign->active_projects) > 0)
                                    @foreach($assign->active_projects as $prj)
                                        <span class="badge badge-light border text-primary font-weight-bold px-2.5 py-1 mb-1 d-inline-block" style="border-radius: 6px;">
                                            <i class="fas fa-folder mr-1"></i>{{ $prj->project_name }} ({{ $prj->project_code }})
                                        </span>
                                    @endforeach
                                @else
                                    <span class="badge badge-light text-muted font-weight-normal px-2.5 py-1" style="border-radius: 6px;">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-3 align-middle">
                                @if(!empty($assign->active_projects) && count($assign->active_projects) > 0)
                                    @foreach($assign->active_projects as $prj)
                                        <div class="small mb-1">
                                            <span class="badge badge-secondary text-uppercase px-2 py-0.5" style="font-size: 10px;">{{ str_replace('_', ' ', $prj->project_role) }}</span>
                                            @if($prj->team_name)
                                                <span class="text-muted">&bull; {{ $prj->team_name }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="small text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-3 align-middle">
                                <span class="badge badge-info px-3 py-1 font-weight-bold" style="border-radius: 8px;">
                                    <i class="fas fa-user-shield mr-1"></i> {{ optional($assign->technicalLead)->display_name }}
                                </span>
                            </td>
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 8px;">Active</span>
                            </td>
                            <td class="py-3 align-middle">
                                <small class="text-muted font-weight-medium">{{ $assign->assigned_at ? $assign->assigned_at->format('d M Y') : 'N/A' }}</small>
                            </td>
                            <td class="py-3 px-4 align-middle text-right">
                                <form action="{{ route('technical_lead.developers.relieve', $assign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Relieve developer from Technical Lead supervision?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 font-weight-bold" style="border-radius: 8px;">
                                        <i class="fas fa-user-minus mr-1"></i> Relieve
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-users-slash fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Supervised Developers Assigned</h5>
                                <p class="small mb-0">Click "Assign Developer" above to assign developers under Technical Lead supervision.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $assignments->links() }}
        </div>
    </div>
</div>

<!-- Assign Developer Modal -->
<div class="modal fade" id="assignDeveloperModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form action="{{ route('technical_lead.developers.assign') }}" method="POST">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Assign Developer to Technical Lead</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Technical Lead Selection -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Select Technical Lead <span class="text-danger">*</span></label>
                        <select name="technical_lead_employee_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Select Technical Lead --</option>
                            @foreach($technicalLeads as $tl)
                                <option value="{{ $tl->id }}" {{ (isset($tlEmpId) && $tlEmpId == $tl->id) ? 'selected' : '' }}>
                                    {{ $tl->display_name }} ({{ $tl->employee_code }} &bull; {{ optional($tl->designation)->name ?? 'Senior Developer' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned From Date -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Assigned From Date <span class="text-danger">*</span></label>
                        <input type="date" name="assigned_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                        <small class="form-text text-muted">The developer's current project is automatically derived from existing project assignments.</small>
                    </div>

                    <!-- Developer Selection -->
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Select Developers to Supervise (Multiple) <span class="text-danger">*</span></label>

                        <!-- Search Bar -->
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="devSearchInput" class="form-control border-left-0" placeholder="Type to search developer by name or code...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold px-3" id="selectAllDevsBtn">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3" id="deselectAllDevsBtn">Clear</button>
                            </div>
                        </div>

                        <!-- Checkbox Container -->
                        <div class="border rounded p-3 bg-white shadow-inner" id="devCheckboxContainer" style="max-height: 260px; overflow-y: auto; border-radius: 12px !important;">
                            @foreach($employees as $emp)
                            <div class="custom-control custom-checkbox py-2 border-bottom border-light dev-item">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="custom-control-input dev-checkbox" id="dev_cb_{{ $emp->id }}">
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer pl-1" for="dev_cb_{{ $emp->id }}" style="user-select: none;">
                                    <span class="text-dark">{{ $emp->display_name }}</span>
                                    <small class="text-muted ml-1">({{ $emp->employee_code }} &bull; {{ optional($emp->designation)->name ?? 'Employee' }})</small>
                                    
                                    <div class="mt-1">
                                        @if($emp->current_project_name)
                                            <span class="badge badge-light border text-primary px-2 py-0.5" style="font-size: 10px; border-radius: 4px;">
                                                <i class="fas fa-folder mr-1"></i>{{ $emp->current_project_name }}
                                                @if($emp->current_team_name) ({{ $emp->current_team_name }}) @endif
                                                &bull; {{ str_replace('_', ' ', $emp->current_role) }}
                                            </span>
                                        @else
                                            <span class="badge badge-light text-muted px-2 py-0.5" style="font-size: 10px; border-radius: 4px;">No Active Project</span>
                                        @endif

                                        @if($emp->current_tl_name)
                                            <span class="badge badge-warning text-dark px-2 py-0.5 ml-1" style="font-size: 10px; border-radius: 4px;">
                                                <i class="fas fa-exchange-alt mr-1"></i>Currently Supervised by: {{ $emp->current_tl_name }} (Will Transfer)
                                            </span>
                                        @endif
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                            <small class="text-muted"><i class="fas fa-user-check text-success mr-1"></i><strong id="selectedDevCount">0</strong> developer(s) selected</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;"><i class="fas fa-check mr-1"></i> Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assignment History Modal -->
<div class="modal fade" id="assignmentHistoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-history mr-2"></i>Supervision Assignment History</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 px-4">Developer</th>
                                <th class="py-3">Technical Lead</th>
                                <th class="py-3">Assigned Date</th>
                                <th class="py-3">Relieved Date</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historyAssignments as $hist)
                            <tr>
                                <td class="py-3 px-4">
                                    <strong class="text-dark font-weight-bold">{{ optional($hist->employee)->display_name }}</strong>
                                    <div class="small text-muted">{{ optional($hist->employee)->employee_code }}</div>
                                </td>
                                <td class="py-3"><span class="small font-weight-bold text-dark">{{ optional($hist->technicalLead)->display_name }}</span></td>
                                <td class="py-3"><span class="small text-muted">{{ $hist->assigned_at ? $hist->assigned_at->format('d M Y') : 'N/A' }}</span></td>
                                <td class="py-3"><span class="small text-muted">{{ $hist->relieved_at ? $hist->relieved_at->format('d M Y, h:i A') : 'N/A' }}</span></td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge badge-secondary px-3 py-1 font-weight-bold" style="border-radius: 8px;">Relieved</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No past assignment history records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-secondary px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 10px;">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
$(document).ready(function() {
    $('#devSearchInput').on('keyup', function() {
        var val = $(this).val().toLowerCase();
        $('#devCheckboxContainer .dev-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    function updateCount() {
        var count = $('.dev-checkbox:checked').length;
        $('#selectedDevCount').text(count);
    }

    $(document).on('change', '.dev-checkbox', function() {
        updateCount();
    });

    $('#selectAllDevsBtn').on('click', function() {
        $('#devCheckboxContainer .dev-item:visible .dev-checkbox').prop('checked', true);
        updateCount();
    });

    $('#deselectAllDevsBtn').on('click', function() {
        $('.dev-checkbox').prop('checked', false);
        updateCount();
    });
});
</script>
@endsection
