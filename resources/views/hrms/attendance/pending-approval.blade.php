@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Blocked / HR Approval')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
<style>
:root{
    --orb-primary:#4B00E8;
    --orb-secondary:#8600EE;
    --orb-bg:#F6F7FB;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 14px 35px rgba(16,24,40,.07);
}

.att-page{min-height:calc(100vh - 90px);padding:18px 12px 35px;background:var(--orb-bg);}
.att-container{max-width:1480px;margin:0 auto;}
.att-card{background:#fff;border:1px solid var(--orb-border);border-radius:24px;box-shadow:var(--orb-shadow);overflow:hidden;}

.att-header{
    padding:22px;
    margin-bottom:18px;
    background:linear-gradient(135deg,#fff,#f8f5ff);
    border:1px solid var(--orb-border);
    border-radius:26px;
    box-shadow:var(--orb-shadow);
    display:flex;
    justify-content:space-between;
    gap:16px;
    align-items:center;
}

.att-title{font-size:26px;font-weight:950;color:var(--orb-text);margin:0;}
.att-subtitle{font-size:13px;color:var(--orb-muted);margin:5px 0 0;}

.att-btn{
    border:0;
    border-radius:14px;
    padding:10px 16px;
    font-weight:900;
    display:inline-flex;
    gap:8px;
    align-items:center;
    justify-content:center;
    text-decoration:none!important;
}

.att-btn-light{background:#fff;color:var(--orb-text);border:1px solid var(--orb-border);}

.att-kpi {
    padding: 18px;
    border-radius: 22px;
    background: #fff;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow);
    position: relative;
    overflow: hidden;
}

.att-kpi:after {
    content: "";
    position: absolute;
    right: -34px;
    top: -34px;
    width: 105px;
    height: 105px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(75, 0, 232, .13), rgba(134, 0, 238, .05));
}

.att-kpi-icon {
    width: 42px;
    height: 42px;
    border-radius: 15px;
    background: var(--orb-soft);
    color: var(--orb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    margin-bottom: 12px;
}

.att-kpi span {
    font-size: 11px;
    color: var(--orb-muted);
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.att-kpi h3 {
    font-size: 28px;
    font-weight: 950;
    color: var(--orb-text);
    margin: 5px 0 0;
}

.att-filter-wrap{
    padding:16px 18px;
    background:linear-gradient(180deg,#fff,#fafbff);
    border-bottom:1px solid var(--orb-border);
}

.att-filter-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}

.att-filter-title{
    font-size:15px;
    font-weight:950;
    color:var(--orb-text);
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
}

.att-filter-title i{color:var(--orb-primary);}

.att-filter-grid{
    display:grid;
    grid-template-columns:1.5fr 1fr 1.2fr 1fr 1.4fr;
    gap:10px;
    align-items:end;
}

.att-filter-wrap label{
    font-size:10px;
    font-weight:950;
    color:#667085;
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:5px;
}

.att-filter-wrap .form-control{
    border-radius:13px;
    border:1px solid #E4E7EC;
    height:42px;
    font-size:13px;
}

.att-table-wrap{padding:0 16px 16px;}
.att-table-responsive{width:100%;}

.att-table{
    width:100%!important;
    min-width:1400px;
    table-layout:fixed;
    border-collapse:collapse!important;
}

.att-table th{
    background:#F8FAFC;
    color:#475467;
    font-size:11px;
    font-weight:950;
    text-transform:uppercase;
    padding:13px 14px!important;
    border-top:1px solid #EAECF0!important;
    border-bottom:1px solid #EAECF0!important;
    white-space:nowrap;
}

.att-table td{
    background:#fff;
    border-bottom:1px solid #EEF2F6!important;
    padding:14px!important;
    vertical-align:middle;
}

.att-table tbody tr{transition:.2s ease;}
.att-table tbody tr:hover td{background:#FAF8FF;}

.att-avatar{
    width:42px;
    height:42px;
    border-radius:14px;
    background:var(--orb-soft);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:950;
    color:var(--orb-primary);
    flex:0 0 auto;
}

.att-emp{display:flex;gap:11px;align-items:center;}
.att-emp-name{font-weight:900;color:var(--orb-text);font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.att-emp-code{font-size:12px;color:var(--orb-muted);}
.att-dept{font-size:11px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

.att-badge{
    display:inline-flex;
    align-items:center;
    border-radius:999px;
    padding:6px 10px;
    font-size:10px;
    font-weight:950;
    text-transform:uppercase;
}

.badge-present{background:#dcfce7;color:#166534}
.badge-absent{background:#fee2e2;color:#991b1b}
.badge-half_day{background:#fef3c7;color:#92400e}
.badge-leave{background:#dbeafe;color:#1e40af}
.badge-week_off{background:#f1f5f9;color:#475569}
.badge-holiday{background:#ede9fe;color:#5b21b6}
.badge-pending_hr{background:#ffedd5;color:#9a3412}
.badge-punch_blocked{background:#ffe4e6;color:#be123c}
.badge-default{background:#f1f5f9;color:#475569}

.dataTables_wrapper > .row:first-child{
    background:#fff;
    border-bottom:1px solid var(--orb-border);
    padding:14px 16px;
    margin:0 -16px 12px!important;
}

.dataTables_wrapper .dt-buttons .btn{
    border-radius:11px!important;
    font-size:12px!important;
    font-weight:800!important;
    margin-right:6px!important;
    margin-bottom:6px!important;
}

.dataTables_length select{border-radius:10px!important;padding:4px 22px 4px 8px!important;}

@media(max-width:768px){
    .att-page{padding:12px 8px 25px;}
    .att-header{flex-direction:column;align-items:flex-start;}
    .att-filter-grid{grid-template-columns:1fr;}
}
</style>

<div class="att-page">
    <div class="att-container">

        <div class="att-header">
            <div>
                <h3 class="att-title">Blocked / HR Approval</h3>
                <p class="att-subtitle">Review and unlock attendance records requiring HR/Admin action.</p>
            </div>
            <a href="{{ route('attendances.export-pdf', request()->query()) }}" class="att-btn att-btn-light">
                <i class="fas fa-file-pdf text-danger"></i> Export Report
            </a>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row mb-3">
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="att-kpi" style="border-left: 5px solid #dc2626;">
                    <div class="att-kpi-icon" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-lock"></i></div>
                    <span>Total Blocked</span>
                    <h3>{{ $stats['total_blocked'] ?? 0 }}</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="att-kpi" style="border-left: 5px solid #f59e0b;">
                    <div class="att-kpi-icon" style="background: #fef3c7; color: #f59e0b;"><i class="fas fa-unlock-alt"></i></div>
                    <span>Pending Unlock</span>
                    <h3>{{ $stats['pending_unlock'] ?? 0 }}</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="att-kpi" style="border-left: 5px solid #ea580c;">
                    <div class="att-kpi-icon" style="background: #ffedd5; color: #ea580c;"><i class="fas fa-user-shield"></i></div>
                    <span>Pending HR</span>
                    <h3>{{ $stats['pending_hr'] ?? 0 }}</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="att-kpi" style="border-left: 5px solid #6366f1;">
                    <div class="att-kpi-icon" style="background: #e0e7ff; color: #6366f1;"><i class="fas fa-user-slash"></i></div>
                    <span>Missed Punch</span>
                    <h3>{{ $stats['missed_punch'] ?? 0 }}</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="att-kpi" style="border-left: 5px solid #16a34a;">
                    <div class="att-kpi-icon" style="background: #dcfce7; color: #16a34a;"><i class="fas fa-check-circle"></i></div>
                    <span>Unlock Today</span>
                    <h3>{{ $stats['unlocked_today'] ?? 0 }}</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="att-kpi" style="border-left: 5px solid #4f46e5;">
                    <div class="att-kpi-icon" style="background: #e0e7ff; color: #4f46e5;"><i class="fas fa-user-edit"></i></div>
                    <span>Manual Punch Approved</span>
                    <h3>{{ $stats['manual_punch'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="att-card">
            <div class="att-filter-wrap">
                <div class="att-filter-head">
                    <h5 class="att-filter-title">
                        <i class="fas fa-user-lock"></i> Pending Approvals
                    </h5>
                    <a href="{{ route('attendances.pending-approval') }}" class="att-btn att-btn-light">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>

                <form method="GET" action="{{ route('attendances.pending-approval') }}" id="pendingFilterForm">
                    <div class="att-filter-grid">
                        <div>
                            <label>Employee</label>
                            <select name="employee_id" class="form-control auto-filter">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    @php $employeeId = optional($emp->employee)->id; @endphp
                                    @if($employeeId)
                                        <option value="{{ $employeeId }}" {{ request('employee_id') == $employeeId ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control auto-filter" value="{{ request('from_date') }}">
                        </div>

                        <div>
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control auto-filter" value="{{ request('to_date') }}">
                        </div>

                        <div>
                            <label>Status Type</label>
                            <select name="flag" class="form-control auto-filter">
                                <option value="">All Status</option>
                                <option value="blocked" {{ request('flag') == 'blocked' ? 'selected' : '' }}>Punch Blocked</option>
                                <option value="pending_hr" {{ request('flag') == 'pending_hr' ? 'selected' : '' }}>Pending HR</option>
                                <option value="missed" {{ request('flag') == 'missed' ? 'selected' : '' }}>Missed Punch</option>
                                <option value="unlocked" {{ request('flag') == 'unlocked' ? 'selected' : '' }}>Unlocked</option>
                                <option value="manual_punch_in" {{ request('flag') == 'manual_punch_in' ? 'selected' : '' }}>Manual Punch-In Approved</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="att-table-wrap">
                <div class="att-table-responsive" style="overflow-x: auto;">
                    <table class="att-table table" id="pendingDataTable">
                        <thead>
                            <tr>
                                <th style="width: 220px;">Employee</th>
                                <th style="width: 120px;">Emp Code</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 200px;">Blocked Reason</th>
                                <th style="width: 100px;">Punch In</th>
                                <th style="width: 100px;">Punch Out</th>
                                <th style="width: 150px;">Unlock Type</th>
                                <th style="width: 150px;">Unlock Reason</th>
                                <th style="width: 100px;" class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                @php
                                    $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                                    $statusCode = $typeCode !== 'default' ? $typeCode : ($attendance->attendance_status ?: 'default');
                                    $statusLabel = $statusCode === 'punch_blocked'
                                        ? 'Punch Blocked'
                                        : ($statusCode === 'pending_hr' ? 'Pending HR' : (optional($attendance->attendanceType)->name ?? ucwords(str_replace('_', ' ', $statusCode))));
                                    $attDate = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="att-emp">
                                            <div class="att-avatar">
                                                {{ strtoupper(substr(optional($attendance->user)->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div style="min-width: 0;">
                                                <div class="att-emp-name" title="{{ optional($attendance->user)->name ?? 'N/A' }}">
                                                    {{ optional($attendance->user)->name ?? 'N/A' }}
                                                </div>
                                                <div class="att-dept" title="{{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}">
                                                    {{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</td>
                                    <td><strong>{{ $attDate }}</strong></td>
                                    <td>
                                        <span class="att-badge badge-{{ $statusCode }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-danger font-weight-bold" style="font-size: 11px;">
                                            {{ $attendance->block_reason ?? $attendance->auto_block_reason ?? $attendance->blocked_reason ?? 'Pending HR / Blocked' }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}</td>
                                    <td>{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}</td>
                                    <td>
                                        @if($attendance->is_admin_unlocked)
                                            <span class="badge badge-success px-2 py-1">{{ str_replace('_', ' ', strtoupper($attendance->unlock_type)) }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-muted">{{ $attendance->unlock_reason_category ?? '-' }}</div>
                                    </td>
                                    <td class="text-right">
                                        <div class="d-flex justify-content-end align-items-center" style="gap: 5px;">
                                            @if(($canUnlockAttendance ?? false) && ($attendance->is_blocked || $attendance->is_punch_blocked || in_array($statusCode, ['pending_hr', 'punch_blocked'], true)))
                                                <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#unlockModal{{ $attendance->id }}" title="Unlock/Approve">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            @endif
                                            @if($canManageAttendance ?? false)
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editModal{{ $attendance->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @foreach($attendances as $attendance)
                @if($canManageAttendance ?? false)
                    @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
                @endif
                @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
            @endforeach
        </div>

        @if($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('pendingFilterForm');
    const filters = document.querySelectorAll('.auto-filter');

    filters.forEach(function (filter) {
        filter.addEventListener('change', () => { if (form) form.submit(); });
    });

    $('#pendingDataTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        ordering: true,
        responsive: false,
        autoWidth: false,
        scrollX: false,
        paging: false,
        info: false,
        searching: false,
        dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
            "<'row'<'col-md-12'tr>>" +
            "<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>",
    });
});
</script>
@endsection
