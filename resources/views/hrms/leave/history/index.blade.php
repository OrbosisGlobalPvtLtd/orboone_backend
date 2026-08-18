@extends('layouts.panel', ['active' => 'leave_management'])

@section('page_title', 'Leave History & Master Audit Log')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#8600EE' }};
    --orb-primary-hover: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-bg: #F8FAFC;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: rgba(75, 0, 232, 0.08);
}

body {
    background: var(--orb-bg) !important;
    overflow-x: hidden !important;
}

.lh-page {
    padding: 24px 20px 48px;
}

.lh-container {
    max-width: 1550px;
    margin: 0 auto;
}

.lh-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#8600EE' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #fff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.lh-hero h1 {
    font-size: 26px;
    font-weight: 900;
    margin: 0;
    color: #fff;
    letter-spacing: -0.02em;
}

.lh-hero p {
    margin: 6px 0 0;
    font-size: 13.5px;
    opacity: 0.92;
}

.lh-stat-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.lh-stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 20px;
    border: 1px solid var(--orb-border);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.lh-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}

.lh-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.lh-stat-card.total .lh-stat-icon { background: rgba(75, 0, 232, 0.08); color: var(--orb-primary); }
.lh-stat-card.approved .lh-stat-icon { background: #ECFDF5; color: #10B981; }
.lh-stat-card.pending .lh-stat-icon { background: #FFFBEB; color: #F59E0B; }
.lh-stat-card.rejected .lh-stat-icon { background: #FEF2F2; color: #EF4444; }
.lh-stat-card.cancelled .lh-stat-icon { background: #F1F5F9; color: #64748B; }

.lh-stat-val {
    font-size: 22px;
    font-weight: 900;
    color: var(--orb-text);
    line-height: 1.1;
}

.lh-stat-lbl {
    font-size: 11px;
    font-weight: 800;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 3px;
}

.lh-filter-panel {
    background: #fff;
    border-radius: 18px 18px 0 0;
    padding: 16px 24px;
    border: 1px solid var(--orb-border);
    border-bottom: 0;
}

.lh-filter-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 12px;
    align-items: end;
}

.lh-filter-grid label {
    font-size: 11px;
    font-weight: 800;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 4px;
    display: block;
}

.lh-filter-grid .form-control {
    height: 38px !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 600;
    padding: 0 12px !important;
    border: 1px solid var(--orb-border);
    background-color: #FAFAFA;
}

.lh-filter-grid .form-control:focus {
    background-color: #fff;
    border-color: var(--orb-primary);
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
}

.lh-card {
    background: #fff;
    border-radius: 0 0 18px 18px;
    border: 1px solid var(--orb-border);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.lh-table {
    width: 100% !important;
    margin: 0 !important;
}

.lh-table thead th {
    background: #F8FAFC !important;
    color: var(--orb-muted) !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 14px 16px !important;
    border-bottom: 1px solid var(--orb-border) !important;
}

.lh-table td {
    padding: 14px 16px !important;
    vertical-align: middle !important;
    font-size: 13.5px;
    border-bottom: 1px solid #F1F5F9;
}

.lh-table tbody tr:hover {
    background-color: #F8FAFC !important;
}

.orb-badge {
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 11.5px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.orb-badge-success { background: #ECFDF5; color: #047857; }
.orb-badge-warning { background: #FFFBEB; color: #B45309; }
.orb-badge-danger { background: #FEF2F2; color: #B91C1C; }
.orb-badge-secondary { background: #F1F5F9; color: #475569; }

.dataTables_wrapper .dataTables_length {
    margin-bottom: 0 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
}

.dataTables_wrapper .dataTables_length select {
    height: 36px !important;
    min-width: 72px !important;
    padding: 2px 26px 2px 12px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    border-radius: 10px !important;
    border: 1px solid var(--orb-border) !important;
}

.dt-buttons .btn {
    border-radius: 10px !important;
    padding: 6px 14px !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    box-shadow: none !important;
}

@media (max-width: 1200px) {
    .lh-stat-grid { grid-template-columns: repeat(3, 1fr); }
    .lh-filter-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .lh-stat-grid { grid-template-columns: repeat(2, 1fr); }
    .lh-filter-grid { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('_content')
<div class="lh-page">
    <div class="lh-container">

        <!-- Hero Header with Dynamic DB Branding Colors -->
        <div class="lh-hero">
            <div>
                <div style="font-size: 11px; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase; opacity: 0.9; margin-bottom: 6px;">
                    <i class="fas fa-history mr-1"></i> HRMS &bull; AUDIT LOG
                </div>
                <h1>Leave History</h1>
                <p>Master record of all approved, pending, rejected, and cancelled leave applications.</p>
            </div>
            <div>
                <a href="{{ route('leave-requests.create') }}" class="btn btn-light font-weight-bold shadow-sm" style="border-radius: 12px; padding: 11px 22px; font-weight: 800; color: var(--orb-primary); background: #ffffff;">
                    <i class="fas fa-plus-circle mr-1"></i> Apply New Leave
                </a>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Stats Grid -->
        <div class="lh-stat-grid">
            <div class="lh-stat-card total">
                <div class="lh-stat-icon"><i class="fas fa-folder-open"></i></div>
                <div>
                    <div class="lh-stat-val">{{ $stats['total'] ?? 0 }}</div>
                    <div class="lh-stat-lbl">Total Records</div>
                </div>
            </div>
            <div class="lh-stat-card approved">
                <div class="lh-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="lh-stat-val">{{ $stats['approved'] ?? 0 }}</div>
                    <div class="lh-stat-lbl">Approved</div>
                </div>
            </div>
            <div class="lh-stat-card pending">
                <div class="lh-stat-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="lh-stat-val">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="lh-stat-lbl">Pending</div>
                </div>
            </div>
            <div class="lh-stat-card rejected">
                <div class="lh-stat-icon"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="lh-stat-val">{{ $stats['rejected'] ?? 0 }}</div>
                    <div class="lh-stat-lbl">Rejected</div>
                </div>
            </div>
            <div class="lh-stat-card cancelled">
                <div class="lh-stat-icon"><i class="fas fa-ban"></i></div>
                <div>
                    <div class="lh-stat-val">{{ $stats['cancelled'] ?? 0 }}</div>
                    <div class="lh-stat-lbl">Cancelled</div>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="lh-filter-panel">
            <form method="GET" action="{{ route('hrms.leave.history') }}" id="filterForm">
                <div class="lh-filter-grid">
                    <div>
                        <label>Status</label>
                        <select name="status" class="form-control js-auto-filter">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div>
                        <label>Leave Type</label>
                        <select name="leave_type_id" class="form-control js-auto-filter">
                            <option value="">All Leave Types</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}" {{ (string)request('leave_type_id') === (string)$lt->id ? 'selected' : '' }}>
                                    {{ $lt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(!$isEmployeeRole)
                    <div>
                        <label>Employee</label>
                        <select name="employee_id" class="form-control js-auto-filter">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ (string)request('employee_id') === (string)$emp->id ? 'selected' : '' }}>
                                    {{ $emp->display_name ?? $emp->user_name ?? $emp->employee_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Department</label>
                        <select name="department_id" class="form-control js-auto-filter">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ (string)request('department_id') === (string)$dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div></div>
                    <div></div>
                    @endif

                    <div>
                        <label>From Date</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control js-auto-filter">
                    </div>

                    <div>
                        <label>To Date</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control js-auto-filter">
                    </div>

                    <div>
                        <a href="{{ route('hrms.leave.history') }}" class="btn btn-light border font-weight-bold btn-block d-flex align-items-center justify-content-center" style="height: 38px; border-radius: 10px;">
                            <i class="fas fa-undo-alt mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- History Table Card -->
        <div class="lh-card">
            <div class="p-3">
                <div class="table-responsive">
                    <table class="lh-table table table-hover js-orb-datatable" id="leaveHistoryTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Total Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Submitted At</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                            @php
                                $empName = optional(optional($req->employee)->user)->name ?? optional($req->employee)->display_name ?? optional($req->employee)->employee_code ?? 'N/A';
                                $deptName = optional(optional($req->employee)->department)->name ?? 'N/A';
                                $photoUrl = resolveEmployeePassportPhoto($req->employee_id);
                                $initials = resolveEmployeeInitials($req->employee_id);
                                
                                $daysCount = $req->total_days ?? $req->days ?? ($req->start_date && $req->end_date ? \Carbon\Carbon::parse($req->start_date)->diffInDays(\Carbon\Carbon::parse($req->end_date)) + 1 : 1);

                                $statusBadge = $req->status === 'approved'
                                    ? 'orb-badge-success'
                                    : ($req->status === 'pending'
                                        ? 'orb-badge-warning'
                                        : ($req->status === 'rejected'
                                            ? 'orb-badge-danger'
                                            : 'orb-badge-secondary'));
                            @endphp
                            <tr>
                                <td><strong>{{ (($requests->currentPage() - 1) * $requests->perPage()) + $loop->iteration }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($photoUrl)
                                            <img src="{{ $photoUrl }}" class="avatar-table rounded-circle mr-2" style="width:36px; height:36px; object-fit:cover;" alt="">
                                        @else
                                            <div class="avatar-table rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px; background: var(--orb-soft); color: var(--orb-primary); font-weight: 900; font-size: 13px;">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-weight-bold text-dark" style="line-height: 1.2;">{{ $empName }}</div>
                                            <div class="text-muted small" style="font-size: 11px;">{{ $deptName }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-primary">{{ optional($req->leaveType)->name ?? 'Paid Leave' }}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }}</span>
                                    <span class="text-muted small mx-1">to</span>
                                    <span class="font-weight-bold">{{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light border font-weight-bold px-2 py-1" style="font-size: 12px; border-radius: 8px;">
                                        {{ $daysCount }} {{ Str::plural('Day', $daysCount) }}
                                    </span>
                                </td>
                                <td>
                                    <span title="{{ $req->reason }}" data-toggle="tooltip" style="cursor: help;">
                                        {{ Str::limit($req->reason, 35) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="orb-badge {{ $statusBadge }}">
                                        @if($req->status === 'approved') <i class="fas fa-check-circle"></i>
                                        @elseif($req->status === 'pending') <i class="fas fa-clock"></i>
                                        @elseif($req->status === 'rejected') <i class="fas fa-times-circle"></i>
                                        @else <i class="fas fa-minus-circle"></i>
                                        @endif
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                                <td class="small text-muted font-weight-600">
                                    {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y h:i A') }}
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-sm btn-light border font-weight-bold" data-toggle="modal" data-target="#viewModal{{ $req->id }}" style="border-radius: 8px; padding: 5px 12px;">
                                        <i class="fas fa-eye text-primary mr-1"></i> View
                                    </button>
                                </td>
                            </tr>

                            <!-- View Details Modal -->
                            <div class="modal fade" id="viewModal{{ $req->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content" style="border-radius: 20px; border: 0; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.2);">
                                        <div class="modal-header text-white" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }}, {{ $branding['secondary_color'] ?? '#8600EE' }}); padding: 18px 24px;">
                                            <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-info-circle mr-2"></i> Leave Application Audit</h5>
                                            <button type="button" class="close text-white opacity-80" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body p-4" style="background: #FAF9FF;">
                                            <div class="mb-3 p-3 bg-white rounded-lg border">
                                                <div class="text-muted small uppercase font-weight-bold mb-1">Applicant Employee</div>
                                                <div class="font-weight-bold text-dark h6 mb-0">{{ $empName }} ({{ optional($req->employee)->employee_code }})</div>
                                                <div class="text-muted small">{{ $deptName }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <div class="p-3 bg-white rounded-lg border">
                                                        <div class="text-muted small uppercase font-weight-bold mb-1">Leave Type</div>
                                                        <div class="font-weight-bold text-primary">{{ optional($req->leaveType)->name ?? 'Paid Leave' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-3 bg-white rounded-lg border">
                                                        <div class="text-muted small uppercase font-weight-bold mb-1">Current Status</div>
                                                        <div><span class="orb-badge {{ $statusBadge }}">{{ ucfirst($req->status) }}</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3 bg-white rounded-lg border mb-3">
                                                <div class="text-muted small uppercase font-weight-bold mb-1">Duration & Days Count</div>
                                                <div class="font-weight-bold text-dark">
                                                    {{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }} ({{ $daysCount }} {{ Str::plural('Day', $daysCount) }})
                                                </div>
                                            </div>
                                            <div class="p-3 bg-white rounded-lg border mb-3">
                                                <div class="text-muted small uppercase font-weight-bold mb-1">Application Reason</div>
                                                <div class="text-dark">{{ $req->reason ?: 'N/A' }}</div>
                                            </div>
                                            @if($req->rejection_reason)
                                            <div class="p-3 bg-white rounded-lg border border-danger">
                                                <div class="text-danger small uppercase font-weight-bold mb-1">Manager Note / Rejection Reason</div>
                                                <div class="text-dark">{{ $req->rejection_reason }}</div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer bg-white">
                                            <button type="button" class="btn btn-secondary font-weight-bold px-4" style="border-radius: 10px;" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($requests->hasPages())
            <div class="p-3 border-top bg-light d-flex justify-content-end">
                {{ $requests->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    $('.js-auto-filter').on('change', function() {
        $('#filterForm').submit();
    });

    if ($('#leaveHistoryTable').length) {
        $('#leaveHistoryTable').DataTable({
            dom: '<"d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"lip>',
            buttons: [
                { extend: 'csv', className: 'btn btn-sm btn-light border font-weight-bold mr-1', text: '<i class="fas fa-file-csv text-secondary mr-1"></i> CSV' },
                { extend: 'excel', className: 'btn btn-sm btn-light border font-weight-bold mr-1', text: '<i class="fas fa-file-excel text-success mr-1"></i> Excel' },
                { extend: 'pdf', className: 'btn btn-sm btn-light border font-weight-bold mr-1', text: '<i class="fas fa-file-pdf text-danger mr-1"></i> PDF' },
                { extend: 'print', className: 'btn btn-sm btn-light border font-weight-bold', text: '<i class="fas fa-print text-primary mr-1"></i> Print' }
            ],
            paging: false,
            info: false,
            searching: true,
            ordering: true
        });
    }
});
</script>
@endsection
