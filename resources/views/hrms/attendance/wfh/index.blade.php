@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'attendance'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-home"></i> Attendance & Time Tracking</div>
            <h1>WFH Requests</h1>
            <p>Track, approve and manage employee Work From Home requests with quota and payroll impact.</p>
        </div>
    </div>

    <div class="row ep-metrics-grid">
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="ep-metric-card border-bottom-primary">
                <div class="ep-metric-icon" style="background:#F4F2FF;color:var(--orb-primary);"><i class="fas fa-list"></i></div>
                <div class="ep-metric-content"><div class="ep-metric-label">Total Requests</div><div class="ep-metric-value">{{ $stats['total'] ?? 0 }}</div></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="ep-metric-card border-bottom-warning">
                <div class="ep-metric-icon" style="background:#FFFAEB;color:#B54708;"><i class="fas fa-hourglass-half"></i></div>
                <div class="ep-metric-content"><div class="ep-metric-label">Pending</div><div class="ep-metric-value">{{ $stats['pending'] ?? 0 }}</div></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="ep-metric-card border-bottom-success">
                <div class="ep-metric-icon" style="background:#ECFDF3;color:#027A48;"><i class="fas fa-check-circle"></i></div>
                <div class="ep-metric-content"><div class="ep-metric-label">Approved</div><div class="ep-metric-value">{{ $stats['approved'] ?? 0 }}</div></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="ep-metric-card border-bottom-danger">
                <div class="ep-metric-icon" style="background:#FEF3F2;color:#B42318;"><i class="fas fa-times-circle"></i></div>
                <div class="ep-metric-content"><div class="ep-metric-label">Rejected</div><div class="ep-metric-value">{{ $stats['rejected'] ?? 0 }}</div></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="ep-metric-card border-bottom-primary">
                <div class="ep-metric-icon" style="background:#EEF4FF;color:#175CD3;"><i class="fas fa-building"></i></div>
                <div class="ep-metric-content"><div class="ep-metric-label">Company Assigned</div><div class="ep-metric-value">{{ $stats['company_assigned'] ?? 0 }}</div></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="ep-metric-card border-bottom-danger">
                <div class="ep-metric-icon" style="background:#FEF3F2;color:#B42318;"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="ep-metric-content"><div class="ep-metric-label">Converted To LWP</div><div class="ep-metric-value">{{ $stats['lwp'] ?? 0 }}</div></div>
            </div>
        </div>
    </div>

    <div class="ep-card">
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-home"></i></div>
                <div>
                    <h5 class="ep-table-title">WFH Requests</h5>
                    <p class="ep-table-subtitle">Review request details, quota impact and payroll impact with clear approvals.</p>
                </div>
            </div>
            @if($canAssign ?? false)
            <div>
                <button type="button" class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#assignWfhModal">
                    <i class="fas fa-plus-circle"></i> Assign Company WFH
                </button>
            </div>
            @endif
        </div>

        <div class="ep-card-filters">
            <form method="GET" class="row align-items-end ep-form">
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Employee</label>
                    <select name="employee_id" class="form-control js-auto-filter">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Status</label>
                    <select name="status" class="form-control js-auto-filter">
                        <option value="">All Status</option>
                        @foreach(['pending','manager_approved','hr_approved','approved','rejected','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Request Type</label>
                    <select name="request_type" class="form-control js-auto-filter">
                        <option value="">All Type</option>
                        @foreach(['working_day_wfh','holiday_wfh','weekoff_wfh','company_assigned_wfh'] as $t)
                        <option value="{{ $t }}" @selected(request('request_type') === $t)>{{ ucwords(str_replace('_', ' ', $t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Reason Category</label>
                    <select name="reason_category" class="form-control js-auto-filter">
                        <option value="">All Reason</option>
                        @foreach(['normal','personal_reason','manager_assigned','company_assigned','internet_issue','electricity_issue','other'] as $r)
                        <option value="{{ $r }}" @selected(request('reason_category') === $r)>{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Date From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control js-auto-filter">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Date To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control js-auto-filter">
                </div>
                <div class="col-md-12 mt-2 d-flex justify-content-end gap-2">
                    <a href="{{ route('hrms.attendance.wfh.index') }}" class="ep-btn ep-btn-light"><i class="fas fa-sync-alt"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table js-orb-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Source</th>
                            <th>Request Type</th>
                            <th>Reason Category</th>
                            <th>Quota Impact</th>
                            <th>Payroll Impact</th>
                            <th>Approval Stage</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->employee_display_name ?? '-' }}</td>
                            <td>{{ $row->employee_code ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->request_date)->format('d M Y') }}</td>
                            <td>{{ $row->source_label ?? 'Employee Requested' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $row->request_type)) }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $row->reason_category)) }}</td>
                            <td>
                                <span class="ep-badge {{ $row->counts_in_monthly_quota ? 'ep-badge-warning' : 'ep-badge-primary' }}">
                                    {{ $row->counts_in_monthly_quota ? 'Counts in Quota' : 'Non-Quota' }}
                                </span>
                            </td>
                            <td>
                                <span class="ep-badge {{ $row->payroll_impact === 'lwp' ? 'ep-badge-danger' : 'ep-badge-success' }}">
                                    {{ strtoupper($row->payroll_impact === 'lwp' ? 'LWP' : 'None') }}
                                </span>
                            </td>
                            <td>
                                <span class="ep-badge {{ in_array($row->status, ['pending','manager_approved','hr_approved']) ? 'ep-badge-warning' : (in_array($row->status, ['approved']) ? 'ep-badge-success' : 'ep-badge-danger') }}">
                                    {{ $row->approval_stage ?? ucwords(str_replace('_', ' ', $row->status)) }}
                                </span>
                            </td>
                            <td><span class="ep-badge {{ in_array($row->status, ['pending','manager_approved','hr_approved']) ? 'ep-badge-warning' : (in_array($row->status, ['approved']) ? 'ep-badge-success' : 'ep-badge-danger') }}">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span></td>
                            <td>{{ $row->approved_by_label ?? '-' }}</td>
                            <td>{{ optional($row->created_at)->format('d M Y h:i A') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="ep-btn ep-btn-light dropdown-toggle" data-toggle="dropdown" style="height:32px;padding:0 10px;">Action</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <button type="button" class="dropdown-item js-view-details"
                                            data-row='@json($row)'>View Details</button>
                                        @if($canApprove && in_array($row->status, ['pending','manager_approved','hr_approved']))
                                        <button type="button" class="dropdown-item text-success js-approve" data-id="{{ $row->id }}">Approve</button>
                                        @endif
                                        @if($canReject && in_array($row->status, ['pending','manager_approved','hr_approved']))
                                        <button type="button" class="dropdown-item text-danger js-reject" data-id="{{ $row->id }}">Reject</button>
                                        @endif
                                        @if(($canMarkLwp ?? false) && $row->status === 'approved' && $row->payroll_impact !== 'lwp')
                                        <button type="button" class="dropdown-item text-warning js-mark-lwp" data-id="{{ $row->id }}">Mark as LWP</button>
                                        @endif
                                        @if($canAssign ?? false)
                                        <button type="button" class="dropdown-item js-open-assign"><i class="fas fa-plus-circle mr-1"></i>Assign WFH</button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="14" class="text-center py-4">No WFH requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $rows->withQueryString()->links() }}</div>
</div>

@if($canAssign ?? false)
<div class="modal fade" id="assignWfhModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('hrms.attendance.wfh.assign') }}" class="modal-content ep-form border-0 shadow-lg">
            @csrf
            <div class="ep-modal-header">
                <h5 class="modal-title">Assign Company WFH</h5>
                <p>Assign approved WFH to employee, department, or all active employees.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label>Assignment Scope</label>
                        <select class="form-control js-assign-scope" name="assignment_scope" required>
                            <option value="single">Single Employee</option>
                            <option value="multiple">Multiple Employees</option>
                            <option value="department">Department</option>
                            <option value="designation">Designation</option>
                            <option value="all">All Employees</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 js-scope-single">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 d-none js-scope-multiple">
                        <label>Employees</label>
                        <select name="employee_ids[]" class="form-control" multiple size="5">
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 d-none js-scope-department">
                        <label>Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">Select Department</option>
                            @foreach(($departments ?? []) as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 d-none js-scope-designation">
                        <label>Designation</label>
                        <select name="designation_id" class="form-control">
                            <option value="">Select Designation</option>
                            @foreach(($designations ?? []) as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Reason Category</label>
                        <select name="reason_category" class="form-control">
                            <option value="company_assigned">Company Assigned</option>
                            <option value="manager_assigned">Manager Assigned</option>
                            <option value="normal">Normal</option>
                            <option value="personal_reason">Personal Reason</option>
                            <option value="internet_issue">Internet Issue</option>
                            <option value="electricity_issue">Electricity Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Payroll Impact</label>
                        <select name="payroll_impact" class="form-control">
                            <option value="none">None</option>
                            <option value="lwp">LWP</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label>Reason</label>
                        <textarea class="form-control" name="reason" rows="2" required placeholder="Why is company assigning WFH?"></textarea>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label>Counts In Monthly Quota</label>
                        <select name="counts_in_monthly_quota" class="form-control">
                            <option value="0" selected>No (Default)</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-check"></i> Assign WFH</button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="modal fade" id="wfhDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ep-form border-0 shadow-lg">
            <div class="ep-modal-header">
                <h5 class="modal-title">WFH Request Details</h5>
                <p>View request reason, approval timeline and payroll impact.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-user"></i> Request Information</div>
                    <div class="row">
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Employee</small><strong id="d_employee">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Date</small><strong id="d_date">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Request Type</small><strong id="d_type">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Reason Category</small><strong id="d_reason_cat">-</strong></div>
                        <div class="col-md-12 mb-2"><small class="text-muted d-block">Reason</small><strong id="d_reason">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Quota Impact</small><strong id="d_quota">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Payroll Impact</small><strong id="d_payroll">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">LWP Reason</small><strong id="d_lwp_reason">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Assigned By</small><strong id="d_assigned_by">-</strong></div>
                        <div class="col-md-12 mb-2"><small class="text-muted d-block">Remarks</small><strong id="d_remarks">-</strong></div>
                    </div>
                </div>
                <div class="ep-section-card mb-0">
                    <div class="ep-section-title"><i class="fas fa-history"></i> Approval Timeline</div>
                    <div class="row">
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Status</small><strong id="d_status">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Manager Approved At</small><strong id="d_mgr_at">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">HR Approved At</small><strong id="d_hr_at">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Rejected At</small><strong id="d_rej_at">-</strong></div>
                        <div class="col-md-12"><small class="text-muted d-block">Rejection Reason</small><strong id="d_rej_reason">-</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canMarkLwp ?? false)
<div class="modal fade" id="markLwpModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="markLwpForm" method="POST" class="modal-content ep-form border-0 shadow-lg">
            @csrf
            <div class="ep-modal-header">
                <h5 class="modal-title">Mark WFH as LWP</h5>
                <p>Set attendance status to LWP with reason and optional remarks.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="ep-form-group">
                    <label>LWP Reason</label>
                    <textarea class="form-control" name="lwp_reason" required></textarea>
                </div>
                <div class="ep-form-group">
                    <label>Remarks (Optional)</label>
                    <textarea class="form-control" name="remarks"></textarea>
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button class="ep-modal-btn ep-modal-btn-danger"><i class="fas fa-exclamation-triangle"></i> Confirm LWP</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($canApprove)
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="approveForm" method="POST" class="modal-content ep-form border-0 shadow-lg">
            @csrf
            <div class="ep-modal-header">
                <h5 class="modal-title">Approve WFH Request</h5>
                <p>Confirm WFH approval for this request.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="ep-form-group">
                    <label>Remarks (Optional)</label>
                    <input type="text" class="form-control" name="remarks" placeholder="Optional remarks for audit logs">
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-check"></i> Confirm Approve</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($canReject)
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="rejectForm" method="POST" class="modal-content ep-form border-0 shadow-lg">
            @csrf
            <div class="ep-modal-header">
                <h5 class="modal-title">Reject WFH Request</h5>
                <p>Rejection reason is mandatory.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="ep-form-group">
                    <label>Rejection Reason</label>
                    <textarea class="form-control" name="rejection_reason" required></textarea>
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button class="ep-modal-btn ep-modal-btn-danger"><i class="fas fa-times"></i> Confirm Reject</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('_script')
<script>
    (function() {
        document.querySelectorAll('.js-view-details').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = JSON.parse(this.getAttribute('data-row') || '{}');
                var fmt = function(v) { return v ? String(v) : '-'; };
                document.getElementById('d_employee').textContent = fmt(row.employee_display_name);
                document.getElementById('d_date').textContent = fmt(row.request_date);
                document.getElementById('d_type').textContent = fmt((row.request_type || '').replaceAll('_', ' '));
                document.getElementById('d_reason_cat').textContent = fmt((row.reason_category || '').replaceAll('_', ' '));
                document.getElementById('d_reason').textContent = fmt(row.reason);
                document.getElementById('d_quota').textContent = row.counts_in_monthly_quota ? 'Counts in Quota' : 'Non-Quota';
                document.getElementById('d_payroll').textContent = fmt((row.payroll_impact || 'none').toUpperCase());
                document.getElementById('d_lwp_reason').textContent = fmt(row.lwp_reason);
                document.getElementById('d_assigned_by').textContent = fmt(row.assigned_by_label);
                document.getElementById('d_remarks').textContent = fmt(row.remarks);
                document.getElementById('d_status').textContent = fmt((row.status || '').replaceAll('_', ' '));
                document.getElementById('d_mgr_at').textContent = fmt(row.manager_approved_at);
                document.getElementById('d_hr_at').textContent = fmt(row.hr_approved_at);
                document.getElementById('d_rej_at').textContent = fmt(row.rejected_at);
                document.getElementById('d_rej_reason').textContent = fmt(row.rejection_reason);
                $('#wfhDetailsModal').modal('show');
            });
        });

        var scopeSelect = document.querySelector('.js-assign-scope');
        var singleBox = document.querySelector('.js-scope-single');
        var multipleBox = document.querySelector('.js-scope-multiple');
        var departmentBox = document.querySelector('.js-scope-department');
        var designationBox = document.querySelector('.js-scope-designation');
        var toggleAssignScope = function() {
            if (!scopeSelect) return;
            var scope = scopeSelect.value;
            if (singleBox) singleBox.classList.toggle('d-none', scope !== 'single');
            if (multipleBox) multipleBox.classList.toggle('d-none', scope !== 'multiple');
            if (departmentBox) departmentBox.classList.toggle('d-none', scope !== 'department');
            if (designationBox) designationBox.classList.toggle('d-none', scope !== 'designation');
        };
        if (scopeSelect) {
            scopeSelect.addEventListener('change', toggleAssignScope);
            toggleAssignScope();
        }

        var approveForm = document.getElementById('approveForm');
        document.querySelectorAll('.js-approve').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!approveForm) return;
                approveForm.action = "{{ route('hrms.attendance.wfh.approve', ['id' => '__ID__']) }}".replace('__ID__', this.dataset.id);
                $('#approveModal').modal('show');
            });
        });

        var rejectForm = document.getElementById('rejectForm');
        document.querySelectorAll('.js-reject').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!rejectForm) return;
                rejectForm.action = "{{ route('hrms.attendance.wfh.reject', ['id' => '__ID__']) }}".replace('__ID__', this.dataset.id);
                $('#rejectModal').modal('show');
            });
        });

        var markLwpForm = document.getElementById('markLwpForm');
        document.querySelectorAll('.js-mark-lwp').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!markLwpForm) return;
                markLwpForm.action = "{{ route('hrms.attendance.wfh.mark-lwp', ['id' => '__ID__']) }}".replace('__ID__', this.dataset.id);
                $('#markLwpModal').modal('show');
            });
        });

        document.querySelectorAll('.js-open-assign').forEach(function(btn) {
            btn.addEventListener('click', function() {
                $('#assignWfhModal').modal('show');
            });
        });

        document.querySelectorAll('.js-auto-filter').forEach(function(el) {
            el.addEventListener('change', function() {
                var form = this.closest('form');
                if (form) form.submit();
            });
        });

        if (window.jQuery && $.fn.DataTable) {
            $('.js-orb-datatable').DataTable({
                pageLength: 25,
                order: [],
                searching: false,
                lengthChange: true,
                autoWidth: false,
                dom: '<"crud-dt-toolbar"<"crud-dt-left"l><"crud-dt-right"B>>rt<"orb-table-footer"ip>',
                buttons: [
                    {extend:'csvHtml5', text:'<i class="fas fa-file-csv text-muted"></i> CSV', className:'crud-export-btn'},
                    {extend:'excelHtml5', text:'<i class="fas fa-file-excel text-success"></i> Excel', className:'crud-export-btn'},
                    {extend:'pdfHtml5', text:'<i class="fas fa-file-pdf text-danger"></i> PDF', className:'crud-export-btn'},
                    {extend:'print', text:'<i class="fas fa-print text-primary"></i> Print', className:'crud-export-btn'}
                ]
            });
        }
    })();
</script>
@endsection
