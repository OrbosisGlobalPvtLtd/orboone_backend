@extends('layouts.panel')

@section('page_title', 'Leave Approvals')

@section('_head')
@include('hrms.leave.shared.style')

<style>
    :root {
        --leave-primary: var(--orb-primary, #4B00E8);
        --leave-secondary: var(--orb-secondary, #8600EE);
        --leave-border: var(--orb-border, #E7EAF3);
        --leave-text: var(--orb-text, #101828);
        --leave-muted: var(--orb-muted, #667085);
        --leave-soft: var(--orb-soft, #F4F2FF);
        --leave-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .leave-page-wrap {
        padding-bottom: 24px;
    }

    .leave-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 22px 24px;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, .26), transparent 35%),
            linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .22);
        margin-bottom: 18px;
    }

    .leave-hero::after {
        content: '';
        position: absolute;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        right: -90px;
        bottom: -120px;
        background: rgba(255, 255, 255, .14);
    }

    .leave-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .leave-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        color: rgba(255, 255, 255, .92);
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .leave-hero-title {
        font-size: 26px;
        font-weight: 900;
        margin: 0;
        letter-spacing: -.03em;
        color: #fff;
    }

    .leave-hero-subtitle {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
        max-width: 780px;
        line-height: 1.6;
    }

    .leave-card {
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 24px;
        box-shadow: var(--leave-shadow);
        overflow: hidden;
        margin-bottom: 18px;
    }

    .leave-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--leave-border);
        background: linear-gradient(180deg, #fff, #FCFCFD);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .leave-card-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .leave-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--leave-soft);
        color: var(--leave-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .leave-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        color: var(--leave-text);
    }

    .leave-card-subtitle {
        margin: 2px 0 0;
        font-size: 12px;
        color: var(--leave-muted);
        font-weight: 600;
    }

    .leave-card-body {
        padding: 18px;
    }

    .leave-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        align-items: end;
    }

    .leave-label {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #667085;
        margin-bottom: 7px;
    }

    .leave-control {
        width: 100%;
        height: 44px;
        border-radius: 14px;
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        padding: 0 14px;
        font-size: 13px;
        font-weight: 700;
        outline: none;
        transition: all .2s ease;
    }

    .leave-control:focus {
        border-color: rgba(75, 0, 232, .25);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
    }

    .leave-reset-btn {
        height: 44px;
        border-radius: 14px;
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        transition: all .2s ease;
    }

    .leave-reset-btn:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border-color: rgba(75, 0, 232, .18);
        text-decoration: none;
    }

    .leave-action-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .leave-light-btn {
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all .2s ease;
    }

    .leave-light-btn:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .leave-table-wrap {
        padding: 14px;
    }

    .leave-table-responsive {
        overflow-x: auto;
        border-radius: 18px;
        border: 1px solid var(--leave-border);
    }

    .leave-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--leave-text);
    }

    .leave-table thead th {
        background: #F9FAFB;
        color: #475467;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 950;
        padding: 14px;
        border-bottom: 1px solid var(--leave-border);
        white-space: nowrap;
    }

    .leave-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #F2F4F7;
        vertical-align: middle;
        font-size: 13px;
        white-space: nowrap;
    }

    .leave-table tbody tr {
        transition: all .15s ease;
    }

    .leave-table tbody tr:hover {
        background: #FAFAFF;
    }

    .leave-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .leave-employee {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 200px;
    }

    .leave-avatar {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(75, 0, 232, .12), rgba(134, 0, 238, .16));
        color: var(--leave-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .leave-employee-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .leave-employee-meta {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 2px;
        font-weight: 700;
    }

    .leave-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .pill-type {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .pill-approved {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .pill-pending {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .pill-rejected {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .pill-cancelled {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .leave-split {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 130px;
    }

    .leave-split-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        font-size: 11px;
        font-weight: 800;
        color: var(--leave-muted);
    }

    .leave-split-value {
        color: var(--leave-text);
        font-weight: 900;
    }

    .leave-status {
        min-width: 110px;
    }

    .leave-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .leave-action-btn {
        border: 0;
        border-radius: 12px;
        height: 34px;
        padding: 0 14px;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .2s ease;
    }

    .approve-btn {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .reject-btn {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .processed-text {
        color: var(--leave-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .empty-state {
        padding: 42px 18px;
        text-align: center;
    }

    .empty-state i {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--leave-soft);
        color: var(--leave-primary);
        font-size: 20px;
        margin-bottom: 12px;
    }

    .dataTables_wrapper .dt-buttons {
        display: none !important;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--leave-border);
        border-radius: 12px;
        padding: 7px 10px;
        outline: none;
        font-size: 12px;
        color: var(--leave-text);
        background: #fff;
    }

    @media(max-width:991px) {
        .leave-filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:767px) {
        .leave-filter-grid {
            grid-template-columns: 1fr;
        }

        .leave-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .leave-hero-title {
            font-size: 22px;
        }

        .leave-card-head {
            padding: 16px;
        }

        .leave-card-body {
            padding: 14px;
        }
    }
</style>
@endsection

@section('_content')
<div class="leave-page-wrap">

    <div class="leave-hero">
        <div class="leave-hero-content">
            <div>
                <div class="leave-hero-kicker">
                    <i class="fas fa-user-check"></i>
                    HRMS Leave Approval Panel
                </div>
                <h1 class="leave-hero-title">Leave Approvals</h1>
                <div class="leave-hero-subtitle">
                    Approve or reject employee leave requests with payroll impact, leave balance deduction, attendance sync and complete approval workflow tracking.
                </div>
            </div>
        </div>
    </div>

    @include('hrms.leave.shared.flash')

    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-filter"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Filter Leave Requests</h5>
                    <div class="leave-card-subtitle">
                        Filters auto-apply when changed. Use reset to clear all filters.
                    </div>
                </div>
            </div>
        </div>

        <div class="leave-card-body">
            <form id="leaveApprovalFilterForm" method="GET">
                <div class="leave-filter-grid">

                    <div>
                        <div class="leave-label">Approval Status</div>
                        <select name="status" class="leave-control">
                            <option value="">All Status</option>
                            @foreach(['pending','approved','rejected','cancelled'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="leave-label">Employee</div>
                        <select name="employee_id" class="leave-control">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->user_name ?? $employee->display_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="leave-label">Leave Type</div>
                        <select name="leave_type_id" class="leave-control">
                            <option value="">All Types</option>
                            @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="leave-label">Reset</div>
                        <a href="{{ url()->current() }}" class="leave-reset-btn w-100">
                            <i class="fas fa-undo"></i>
                            Reset Filter
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Leave Approval Requests</h5>
                    <div class="leave-card-subtitle">
                        Manage employee leave requests with payroll and attendance impact.
                    </div>
                </div>
            </div>

            <div class="leave-action-wrap">
                <button type="button" class="leave-light-btn" onclick="triggerLeaveExport('csv');">
                    <i class="fas fa-file-csv"></i> CSV
                </button>
                <button type="button" class="leave-light-btn" onclick="triggerLeaveExport('excel');">
                    <i class="fas fa-file-excel text-success"></i> Excel
                </button>
                <button type="button" class="leave-light-btn" onclick="triggerLeaveExport('pdf');">
                    <i class="fas fa-file-pdf text-danger"></i> PDF
                </button>
                <button type="button" class="leave-light-btn" onclick="triggerLeaveExport('print');">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <div class="leave-table-wrap">
            <div class="leave-table-responsive">
                <table class="leave-table js-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Period</th>
                            <th>Days</th>
                            <th>Balance Split</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($requests as $request)
                        @php
                        $employeeName = optional($request->employee)->display_name ?? 'Unknown Employee';
                        $employeeCode = optional($request->employee)->employee_code ?? 'EMP';
                        $leaveType = optional($request->leaveType)->name ?? 'Leave';
                        $initial = strtoupper(substr(trim($employeeName),0,1));

                        $statusClass = 'pill-pending';
                        if($request->status === 'approved'){
                        $statusClass = 'pill-approved';
                        }elseif($request->status === 'rejected'){
                        $statusClass = 'pill-rejected';
                        }elseif($request->status === 'cancelled'){
                        $statusClass = 'pill-cancelled';
                        }
                        @endphp

                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>

                            <td>
                                <div class="leave-employee">
                                    <div class="leave-avatar">{{ $initial }}</div>
                                    <div>
                                        <div class="leave-employee-name">{{ $employeeName }}</div>
                                        <div class="leave-employee-meta">{{ $employeeCode }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="leave-pill pill-type">
                                    <i class="fas fa-tag"></i>
                                    {{ $leaveType }}
                                </span>
                            </td>

                            <td>
                                <div style="font-weight:800;">
                                    {{ optional($request->start_date)->format('d M') }}
                                    -
                                    {{ optional($request->end_date)->format('d M Y') }}
                                </div>
                            </td>

                            <td><strong>{{ $request->deducted_days }}</strong></td>

                            <td>
                                <div class="leave-split">
                                    <div class="leave-split-item"><span>Paid</span><span class="leave-split-value">{{ $request->paid_days }}</span></div>
                                    <div class="leave-split-item"><span>Sick</span><span class="leave-split-value">{{ $request->sick_days }}</span></div>
                                    <div class="leave-split-item"><span>Comp Off</span><span class="leave-split-value">{{ $request->comp_off_days }}</span></div>
                                    <div class="leave-split-item"><span>LWP</span><span class="leave-split-value">{{ $request->lwp_days }}</span></div>
                                </div>
                            </td>

                            <td class="leave-status">
                                <span class="leave-pill {{ $statusClass }}">
                                    <i class="fas fa-circle" style="font-size:6px;"></i>
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>

                            <td>
                                @if($request->status === 'pending')
                                <div class="leave-actions">
                                    <form method="POST" action="{{ route('leave-approvals.approve', $request->id) }}" class="d-inline">
                                        @csrf
                                        <button class="leave-action-btn approve-btn">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('leave-approvals.reject', $request->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="reason" value="Rejected from approval list">
                                        <button class="leave-action-btn reject-btn">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                                @else
                                <span class="processed-text">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Already Processed
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <div style="font-weight:900;color:var(--leave-text);">
                                        No Leave Requests Found
                                    </div>
                                    <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                        New leave approval requests will appear here.
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($requests, 'links'))
            <div class="mt-3">
                {{ $requests->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

<script>
    $(document).on('change', '#leaveApprovalFilterForm select', function() {
        $('#leaveApprovalFilterForm').submit();
    });

    function triggerLeaveExport(type) {
        let table = $('.js-datatable').DataTable();

        let buttons = {
            csv: '.buttons-csv',
            excel: '.buttons-excel',
            pdf: '.buttons-pdf',
            print: '.buttons-print'
        };

        if (buttons[type]) {
            table.button(buttons[type]).trigger();
        }
    }
</script>
@endsection