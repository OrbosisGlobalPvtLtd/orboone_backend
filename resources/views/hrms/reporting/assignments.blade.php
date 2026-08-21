@extends('layouts.panel', ['active' => 'reporting_assignments'])

@section('page_title', isset($selectedSupervisor) && $selectedSupervisor ? 'Reporting Assignments - ' . $selectedSupervisor->display_name : 'Employee Assignments')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.rep-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.rep-container {
    max-width: 1550px;
    margin: 0 auto;
}

.rep-hero {
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

.rep-card {
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
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header -->
        <div class="rep-hero" style="padding: 20px 24px;">
            <div>
                @if(isset($selectedSupervisor) && $selectedSupervisor)
                    <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-users-cog mr-2"></i>Reporting Assignments: {{ $selectedSupervisor->display_name }}</h3>
                    <p class="mb-0 opacity-90 small">Managing active reporting employees assigned to <strong>{{ $selectedSupervisor->display_name }}</strong> ({{ $selectedSupervisor->employee_code }} &bull; {{ optional($selectedSupervisor->department)->name ?? 'General' }}).</p>
                @else
                    <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-users-cog mr-2"></i>Employee Reporting Assignments</h3>
                    <p class="mb-0 opacity-90 small">Manage reporting relationships between Reporting Managers and employees across all enterprise departments.</p>
                @endif
            </div>
            <div>
                <button type="button" class="btn btn-light font-weight-bold px-4 py-2" data-toggle="modal" data-target="#assignModal" style="border-radius: 12px; color: var(--orb-primary);">
                    <i class="fas fa-user-plus mr-2"></i>
                    @if(isset($selectedSupervisor) && $selectedSupervisor)
                        Assign Employees to {{ $selectedSupervisor->display_name }}
                    @else
                        Assign Reporting Manager
                    @endif
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="rep-card p-3 mb-4">
            <form method="GET" action="{{ route('reporting.assignments') }}" class="form-inline">
                @if(request('supervisor_id'))
                    <input type="hidden" name="supervisor_id" value="{{ request('supervisor_id') }}">
                @endif
                <div class="input-group mr-2 flex-grow-1" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control" placeholder="Search employee or manager..." value="{{ request('search') }}" style="border-radius: 10px 0 0 10px;">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit" style="border-radius: 0 10px 10px 0; background: var(--orb-primary); border-color: var(--orb-primary);"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                @if(request('search') || request('supervisor_id') || (isset($selectedSupervisor) && $selectedSupervisor))
                    <a href="{{ route('reporting.assignments') }}" class="btn btn-light border text-muted font-weight-bold" style="border-radius: 10px;"><i class="fas fa-list mr-1"></i> View All Assignments</a>
                @endif
            </form>
        </div>

        <!-- Assignments Table -->
        <div class="rep-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">Department & Designation</th>
                            <th class="py-3">Reporting Manager</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center">Assigned Since</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assign)
                        <tr>
                            <td class="py-3 px-4 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ $assign->employee_name }}</strong>
                                <small class="text-muted">{{ $assign->employee_code }}</small>
                            </td>
                            <td class="py-3 align-middle">
                                <span class="badge badge-light border text-primary font-weight-bold px-2 py-0.5" style="border-radius: 6px;">{{ $assign->department_name ?? 'General' }}</span>
                                <small class="text-muted d-block mt-0.5">{{ $assign->designation_name ?? 'Employee' }}</small>
                            </td>
                            <td class="py-3 align-middle">
                                <strong class="text-dark"><i class="fas fa-user-shield text-warning mr-1"></i>{{ $assign->supervisor_name }}</strong>
                            </td>
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 8px;">Active</span>
                            </td>
                            <td class="py-3 align-middle text-center">
                                <small class="text-muted font-weight-bold">{{ \Carbon\Carbon::parse($assign->start_date ?? $assign->created_at)->format('d M Y') }}</small>
                            </td>
                            <td class="py-3 px-4 align-middle text-right">
                                <form method="POST" action="{{ route('reporting.assignments.relieve', $assign->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to relieve or transfer this reporting assignment?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold px-3" style="border-radius: 8px;">
                                        <i class="fas fa-user-minus mr-1"></i> Relieve
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-users-cog fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Active Reporting Assignments Found</h5>
                                <p class="small mb-0">Click button above or use Employee Onboarding to assign Reporting Managers.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assignments->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Assign Employees Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form method="POST" action="{{ route('reporting.assignments.assign') }}">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-plus mr-2"></i>
                        @if(isset($selectedSupervisor) && $selectedSupervisor)
                            Assign Employees to {{ $selectedSupervisor->display_name }}
                        @else
                            Assign Employees to Reporting Manager
                        @endif
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Select Reporting Manager <span class="text-danger">*</span></label>
                        <select name="supervisor_employee_id" class="form-control" required style="border-radius: 10px;">
                            <option value="">-- Select Reporting Manager --</option>
                            @foreach($supervisors as $sup)
                                <option value="{{ $sup->id }}" {{ (old('supervisor_employee_id', optional($selectedSupervisor)->id) == $sup->id) ? 'selected' : '' }}>
                                    {{ $sup->display_name }} ({{ $sup->employee_code }} &bull; {{ optional($sup->department)->name ?? 'General' }} &bull; {{ optional($sup->designation)->name ?? 'Manager' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Assigned From Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 10px;">
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Select Reporting Employees <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <input type="text" id="empSearchInput" class="form-control" placeholder="Search by name, department or code..." style="border-radius: 10px 0 0 10px;">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold px-3" id="selectAllBtn">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3" id="clearAllBtn">Clear</button>
                            </div>
                        </div>

                        <div class="border rounded p-3 bg-white" id="empCheckboxContainer" style="max-height: 260px; overflow-y: auto; border-radius: 12px !important;">
                            @foreach($employees as $emp)
                            <div class="custom-control custom-checkbox py-2 border-bottom border-light emp-item" data-emp-id="{{ $emp->id }}">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="custom-control-input emp-checkbox" id="emp_cb_{{ $emp->id }}">
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer pl-1" for="emp_cb_{{ $emp->id }}" style="user-select: none;">
                                    <span class="text-dark">{{ $emp->display_name }}</span>
                                    <small class="text-muted ml-1">({{ $emp->employee_code }} &bull; {{ optional($emp->department)->name ?? 'Dept' }} &bull; {{ optional($emp->designation)->name ?? 'Employee' }})</small>

                                    @if($emp->current_supervisor_name)
                                        <div class="mt-1">
                                            <span class="badge badge-warning text-dark px-2 py-0.5" style="font-size: 10px; border-radius: 4px;">
                                                <i class="fas fa-exchange-alt mr-1"></i>Reporting Manager: {{ $emp->current_supervisor_name }} (Will Transfer)
                                            </span>
                                        </div>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4" style="border-radius: 10px; background: var(--orb-primary); border-color: var(--orb-primary);"><i class="fas fa-check-circle mr-2"></i>Assign Reporting Employees</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('empSearchInput');
    var items = document.querySelectorAll('.emp-item');
    var supervisorSelect = document.querySelector('select[name="supervisor_employee_id"]');

    function filterEmployees() {
        var searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
        var selectedSupId = supervisorSelect ? supervisorSelect.value : '';

        items.forEach(function(item) {
            var empId = item.getAttribute('data-emp-id');
            var cb = item.querySelector('.emp-checkbox');
            var text = item.textContent.toLowerCase();

            // Exclude the selected supervisor from their own reporting employee list
            if (selectedSupId && empId === selectedSupId) {
                item.style.display = 'none';
                if (cb) cb.checked = false;
            } else {
                if (searchVal === '' || text.indexOf(searchVal) !== -1) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterEmployees);
    }

    if (supervisorSelect) {
        supervisorSelect.addEventListener('change', filterEmployees);
    }

    // Run initial filter on page load / modal render
    filterEmployees();

    var selectAll = document.getElementById('selectAllBtn');
    var clearAll = document.getElementById('clearAllBtn');

    if (selectAll) {
        selectAll.addEventListener('click', function() {
            document.querySelectorAll('.emp-checkbox').forEach(function(cb) {
                if (cb.closest('.emp-item').style.display !== 'none') {
                    cb.checked = true;
                }
            });
        });
    }

    if (clearAll) {
        clearAll.addEventListener('click', function() {
            document.querySelectorAll('.emp-checkbox').forEach(function(cb) {
                cb.checked = false;
            });
        });
    }
});
</script>
@endsection
