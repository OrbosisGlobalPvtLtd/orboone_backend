@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'attendance'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-home"></i> Attendance & Time Tracking</div>
            <h1>My WFH Requests</h1>
            <p>Apply and manage your Work From Home requests, check monthly quota, and track approval stages.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #ECFDF3; color: #027A48;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #FEF3F2; color: #B42318;">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if($isPermanentWfh ?? false)
    <div class="alert alert-info border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #EFF8FF; color: #175CD3;">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x mr-3"></i>
            <div>
                <h5 class="mb-1 font-weight-bold">Permanent Work From Home Employee</h5>
                <p class="mb-0">You are assigned as a Permanent Work From Home employee. No WFH approval or request is required to mark WFH attendance.</p>
            </div>
        </div>
    </div>
    @else
    <div class="row ep-metrics-grid">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="ep-metric-card border-bottom-primary" style="height:115px;">
                <div class="ep-metric-icon" style="background:#F4F2FF;color:var(--orb-primary);"><i class="fas fa-calendar-alt"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Monthly WFH Limit</div>
                    <div class="ep-metric-value">{{ $balance['monthly_limit'] ?? 0 }} Days</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="ep-metric-card border-bottom-warning" style="height:115px;">
                <div class="ep-metric-icon" style="background:#FFFAEB;color:#B54708;"><i class="fas fa-hourglass-half"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Used This Month</div>
                    <div class="ep-metric-value">{{ $balance['used'] ?? 0 }} Days</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="ep-metric-card border-bottom-success" style="height:115px;">
                <div class="ep-metric-icon" style="background:#ECFDF3;color:#027A48;"><i class="fas fa-check-circle"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Remaining Quota</div>
                    <div class="ep-metric-value">{{ $balance['remaining'] ?? 0 }} Days</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="ep-metric-card border-bottom-primary" style="height:115px;">
                <div class="ep-metric-icon" style="background:#EEF4FF;color:#175CD3;"><i class="fas fa-building"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Non-Quota Approved</div>
                    <div class="ep-metric-value">{{ $balance['non_quota_approved'] ?? 0 }} Days</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="ep-card">
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-home"></i></div>
                <div>
                    <h5 class="ep-table-title">My WFH Request History</h5>
                    <p class="ep-table-subtitle">Apply for new WFH requests or check status and details of previous requests.</p>
                </div>
            </div>
            @if(! ($isPermanentWfh ?? false))
            <div>
                <button type="button" class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#applyWfhModal">
                    <i class="fas fa-plus-circle"></i> Request WFH
                </button>
            </div>
            @endif
        </div>

        <div class="ep-card-filters">
            <form method="GET" class="row align-items-end ep-form">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Status</label>
                    <select name="status" class="form-control js-auto-filter">
                        <option value="">All Status</option>
                        @foreach(['pending','manager_approved','hr_approved','approved','rejected','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Reason Category</label>
                    <select name="reason_category" class="form-control js-auto-filter">
                        <option value="">All Reason</option>
                        @foreach(['normal','personal_reason','internet_issue','electricity_issue','other'] as $r)
                        <option value="{{ $r }}" @selected(request('reason_category') === $r)>{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Date From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control js-auto-filter">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Date To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control js-auto-filter">
                </div>
            </form>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Date Range</th>
                            <th>Working Days</th>
                            <th>Source</th>
                            <th>Request Type</th>
                            <th>Reason Category</th>
                            <th>Quota Impact</th>
                            <th>Payroll Impact</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration + ($rows->currentPage() - 1) * $rows->perPage() }}</td>
                            <td><strong>{{ $row->date_range_label ?? \Carbon\Carbon::parse($row->request_date)->format('d M Y') }}</strong></td>
                            <td><span class="ep-badge ep-badge-primary">{{ $row->working_days ?? 1 }} Days</span></td>
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
                            <td><span class="ep-badge {{ in_array($row->status, ['pending','manager_approved','hr_approved']) ? 'ep-badge-warning' : (in_array($row->status, ['approved']) ? 'ep-badge-success' : 'ep-badge-danger') }}">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span></td>
                            <td>{{ $row->approved_by_label ?? '-' }}</td>
                            <td>{{ optional($row->created_at)->format('d M Y h:i A') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="ep-btn ep-btn-light dropdown-toggle" data-toggle="dropdown" style="height:32px;padding:0 10px;">Action</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <button type="button" class="dropdown-item js-view-details" data-row='@json($row)'>View Details</button>
                                        @if(in_array($row->status, ['pending','manager_approved']))
                                        <form method="POST" action="{{ route('hrms.attendance.my-wfh.cancel', ['id' => $row->id]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">Cancel Request</button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="12" class="text-center py-4">No WFH requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $rows->withQueryString()->links() }}</div>
</div>

<div class="modal fade" id="applyWfhModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form method="POST" action="{{ route('hrms.attendance.my-wfh.apply') }}" class="modal-content ep-form border-0 shadow-lg">
            @csrf
            <div class="ep-modal-header">
                <h5 class="modal-title">Request Work From Home</h5>
                <p>Submit a WFH request range for manager and HR approval.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label>From Date</label>
                        <input type="date" name="from_date" id="wfh_from_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label>To Date</label>
                        <input type="date" name="to_date" id="wfh_to_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div id="wfh_calc_box" class="p-3 mb-3 d-none" style="background:#F8F9FA; border-radius:10px; border:1px solid #E9ECEF;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-primary" id="wfh_calc_period">Requested Period</strong>
                        <span class="badge badge-primary" id="wfh_calc_total">0 Days</span>
                    </div>
                    <div class="row text-center small">
                        <div class="col-4">
                            <div class="text-muted">Working Days</div>
                            <strong class="text-success" style="font-size:16px;" id="wfh_calc_working">0</strong>
                        </div>
                        <div class="col-4">
                            <div class="text-muted">Weekly Off</div>
                            <strong class="text-warning" style="font-size:16px;" id="wfh_calc_weekoff">0</strong>
                        </div>
                        <div class="col-4">
                            <div class="text-muted">Holidays</div>
                            <strong class="text-info" style="font-size:16px;" id="wfh_calc_holiday">0</strong>
                        </div>
                    </div>
                    <div class="mt-2 text-center text-dark font-weight-bold pt-2 border-top">
                        Actual WFH Days: <span class="text-success" id="wfh_calc_actual">0</span>
                    </div>
                </div>

                <div class="ep-form-group">
                    <label>Reason Category</label>
                    <select name="reason_category" class="form-control" required>
                        <option value="normal">Normal</option>
                        <option value="personal_reason">Personal Reason</option>
                        <option value="internet_issue">Internet Issue</option>
                        <option value="electricity_issue">Electricity Issue</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="ep-form-group">
                    <label>Reason Description</label>
                    <textarea class="form-control" name="reason" rows="3" required placeholder="Describe your reason for requesting WFH..."></textarea>
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-check"></i> Submit Request</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="wfhDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ep-form border-0 shadow-lg">
            <div class="ep-modal-header">
                <h5 class="modal-title">WFH Request Details</h5>
                <p>View complete request information, days breakdown, and approval history.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-user"></i> Application Overview</div>
                    <div class="row">
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">From Date</small><strong id="d_from_date">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">To Date</small><strong id="d_to_date">-</strong></div>
                        <div class="col-md-3 mb-2"><small class="text-muted d-block">Total Calendar Days</small><strong id="d_total_days">-</strong></div>
                        <div class="col-md-3 mb-2"><small class="text-muted d-block">Total Working Days</small><strong id="d_working_days">-</strong></div>
                        <div class="col-md-3 mb-2"><small class="text-muted d-block">Weekend Count</small><strong id="d_weekoff_days">-</strong></div>
                        <div class="col-md-3 mb-2"><small class="text-muted d-block">Holiday Count</small><strong id="d_holiday_days">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Reason Category</small><strong id="d_reason_cat">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Request Type</small><strong id="d_type">-</strong></div>
                        <div class="col-md-12 mb-2"><small class="text-muted d-block">Reason</small><strong id="d_reason">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Quota Impact</small><strong id="d_quota">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Payroll Impact</small><strong id="d_payroll">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">LWP Reason</small><strong id="d_lwp_reason">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Assigned By</small><strong id="d_assigned_by">-</strong></div>
                        <div class="col-md-12 mb-2"><small class="text-muted d-block">Remarks</small><strong id="d_remarks">-</strong></div>
                    </div>
                </div>
                <div class="ep-section-card mb-0">
                    <div class="ep-section-title"><i class="fas fa-history"></i> Status & Approval History</div>
                    <div class="row">
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Applied Date</small><strong id="d_applied_at">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Current Status</small><strong id="d_status">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Manager Approved At</small><strong id="d_mgr_at">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">HR Approved At</small><strong id="d_hr_at">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Rejected At</small><strong id="d_rej_at">-</strong></div>
                        <div class="col-md-6 mb-2"><small class="text-muted d-block">Rejection Reason</small><strong id="d_rej_reason">-</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
    (function() {
        document.querySelectorAll('.js-view-details').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = JSON.parse(this.getAttribute('data-row') || '{}');
                var fmt = function(v) { return (v !== null && v !== undefined && String(v).trim() !== '') ? String(v) : '-'; };
                document.getElementById('d_from_date').textContent = fmt(row.from_date_formatted || row.request_date);
                document.getElementById('d_to_date').textContent = fmt(row.to_date_formatted || row.from_date_formatted || row.request_date);
                document.getElementById('d_total_days').textContent = fmt(row.total_days || 1) + ' Days';
                document.getElementById('d_working_days').textContent = fmt(row.working_days || 1) + ' Days';
                document.getElementById('d_weekoff_days').textContent = fmt(row.weekoff_days || 0) + ' Days';
                document.getElementById('d_holiday_days').textContent = fmt(row.holiday_days || 0) + ' Days';
                document.getElementById('d_type').textContent = fmt((row.request_type || '').replaceAll('_', ' '));
                document.getElementById('d_reason_cat').textContent = fmt((row.reason_category || '').replaceAll('_', ' '));
                document.getElementById('d_reason').textContent = fmt(row.reason);
                document.getElementById('d_quota').textContent = row.counts_in_monthly_quota ? 'Counts in Quota' : 'Non-Quota';
                document.getElementById('d_payroll').textContent = fmt((row.payroll_impact || 'none').toUpperCase());
                document.getElementById('d_lwp_reason').textContent = fmt(row.lwp_reason);
                document.getElementById('d_assigned_by').textContent = fmt(row.assigned_by_label);
                document.getElementById('d_remarks').textContent = fmt(row.remarks);
                document.getElementById('d_applied_at').textContent = fmt(row.created_at);
                document.getElementById('d_status').textContent = fmt((row.status || '').replaceAll('_', ' '));
                document.getElementById('d_mgr_at').textContent = fmt(row.manager_approved_at);
                document.getElementById('d_hr_at').textContent = fmt(row.hr_approved_at);
                document.getElementById('d_rej_at').textContent = fmt(row.rejected_at);
                document.getElementById('d_rej_reason').textContent = fmt(row.rejection_reason);
                $('#wfhDetailsModal').modal('show');
            });
        });

        document.querySelectorAll('.js-auto-filter').forEach(function(el) {
            el.addEventListener('change', function() {
                var form = this.closest('form');
                if (form) form.submit();
            });
        });

        var calcWfhDays = function() {
            var fromEl = document.getElementById('wfh_from_date');
            var toEl = document.getElementById('wfh_to_date');
            if (!fromEl || !toEl) return;
            var from = fromEl.value;
            var to = toEl.value;
            if (!from || !to) return;

            fetch("{{ route('hrms.attendance.my-wfh.calculate-days') }}?from_date=" + from + "&to_date=" + to)
                .then(function(res) { return res.json(); })
                .then(function(res) {
                    if (res.status && res.data) {
                        var d = res.data;
                        document.getElementById('wfh_calc_period').textContent = d.period_label;
                        document.getElementById('wfh_calc_total').textContent = d.total_days + ' Total Days';
                        document.getElementById('wfh_calc_working').textContent = d.working_days;
                        document.getElementById('wfh_calc_weekoff').textContent = d.weekoff_days;
                        document.getElementById('wfh_calc_holiday').textContent = d.holiday_days;
                        document.getElementById('wfh_calc_actual').textContent = d.actual_wfh_days;
                        document.getElementById('wfh_calc_box').classList.remove('d-none');
                    }
                })
                .catch(function(e) { console.error(e); });
        };
        var fromInput = document.getElementById('wfh_from_date');
        var toInput = document.getElementById('wfh_to_date');
        if (fromInput && toInput) {
            fromInput.addEventListener('change', calcWfhDays);
            toInput.addEventListener('change', calcWfhDays);
            calcWfhDays();
        }
    })();
</script>
@endsection
