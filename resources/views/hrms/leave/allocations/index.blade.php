@extends('layouts.panel')

@section('page_title', 'Leave Allocation')

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

    .leave-action-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 14px;
    }

    .leave-action-box {
        border: 1px solid var(--leave-border);
        border-radius: 18px;
        padding: 16px;
        background: #FCFCFD;
    }

    .leave-action-title {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        margin-bottom: 4px;
    }

    .leave-action-subtitle {
        font-size: 12px;
        font-weight: 600;
        color: var(--leave-muted);
        margin-bottom: 14px;
    }

    .leave-form-row {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .leave-control {
        height: 42px;
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

    .leave-year-input {
        width: 115px;
    }

    .leave-employee-select {
        min-width: 240px;
        flex: 1;
    }

    .leave-btn {
        border: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 12px 24px rgba(75, 0, 232, .18);
        transition: all .2s ease;
        white-space: nowrap;
    }

    .leave-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(75, 0, 232, .24);
        color: #fff;
    }

    .leave-btn-light {
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        border-radius: 14px;
        height: 42px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .leave-btn-light:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border-color: rgba(75, 0, 232, .18);
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
        text-align: center;
        vertical-align: middle;
    }

    .leave-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #F2F4F7;
        vertical-align: middle;
        text-align: center;
        font-size: 13px;
        white-space: nowrap;
    }

    .leave-table thead th.text-left,
    .leave-table tbody td.text-left {
        text-align: left !important;
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
        min-width: 210px;
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
        overflow: hidden !important;
    }

    .leave-avatar img {
        width: 100% !important;
        height: 100% !important;
        border-radius: inherit !important;
        object-fit: cover !important;
        display: block !important;
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
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .pill-stage {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .pill-policy {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .pill-lwp {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .leave-metric {
        font-weight: 950;
        color: var(--leave-text);
        font-size: 14px;
    }

    .metric-muted {
        color: var(--leave-muted);
        font-size: 11px;
        font-weight: 700;
        margin-top: 2px;
    }

    .leave-breakdown-box {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 4px;
        line-height: 1.4;
    }

    .leave-breakdown-item {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 6px;
        background: #F8F9FA;
        border: 1px solid #E9ECEF;
        margin-right: 2px;
        margin-bottom: 2px;
        font-size: 10px;
        font-weight: 700;
    }

    .leave-breakdown-item span {
        color: var(--leave-text);
        font-weight: 900;
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

    @media(max-width:991px) {
        .leave-action-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:767px) {
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

        .leave-form-row {
            align-items: stretch;
        }

        .leave-control,
        .leave-btn,
        .leave-btn-light {
            width: 100%;
        }

        .leave-year-input,
        .leave-employee-select {
            width: 100%;
            min-width: 100%;
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
                    <i class="fas fa-coins"></i>
                    HRMS Leave Allocation
                </div>
                <h1 class="leave-hero-title">Leave Allocation</h1>
                <div class="leave-hero-subtitle">
                    Generate, edit, and review policy-driven yearly leave allocations with used, remaining and LWP tracking.
                </div>
            </div>
        </div>
    </div>

    @include('hrms.leave.shared.flash')

    @if($canManageAllocations ?? false)
    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-magic"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Generate Leave Allocation</h5>
                    <div class="leave-card-subtitle">
                        Generate allocation for full year or for a selected employee only.
                    </div>
                </div>
            </div>
        </div>

        <div class="leave-card-body">
            <div class="leave-action-grid">

                <div class="leave-action-box">
                    <div class="leave-action-title">Generate Yearly Allocation</div>
                    <div class="leave-action-subtitle">Process leave allocation for all eligible employees.</div>

                    <form method="POST" action="{{ route('leave-allocations.process') }}">
                        @csrf
                        <div class="leave-form-row">
                            <input name="year"
                                class="leave-control leave-year-input"
                                value="{{ $year }}"
                                placeholder="Year">

                            <button class="leave-btn" type="submit">
                                <i class="fas fa-play"></i>
                                Generate Year
                            </button>
                        </div>
                    </form>
                </div>

                <div class="leave-action-box">
                    <div class="leave-action-title">Generate Single Employee</div>
                    <div class="leave-action-subtitle">Run allocation for one employee without affecting others.</div>

                    <form method="POST" action="{{ route('leave-allocations.single') }}">
                        @csrf
                        <div class="leave-form-row">
                            <input name="year"
                                class="leave-control leave-year-input"
                                value="{{ $year }}"
                                placeholder="Year">

                            <select name="employee_id" class="leave-control leave-employee-select">
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->user_name ?? $employee->display_name }}
                                </option>
                                @endforeach
                            </select>

                            <button class="leave-btn-light" type="submit">
                                <i class="fas fa-user-check"></i>
                                Generate Single
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @endif

    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Allocation Records</h5>
                    <div class="leave-card-subtitle">
                        View allocated, used, remaining and LWP leave balances by employee.
                    </div>
                </div>
            </div>

            <div class="leave-action-wrap align-items-center">
                <form action="{{ route('leave-allocations.index') }}" method="GET" class="d-inline-flex align-items-center mr-3">
                    <label class="mr-2 mb-0 font-weight-bold" style="font-size:12px; color:#475467;"><i class="fas fa-calendar-alt text-primary mr-1"></i> Filter Year:</label>
                    <select name="year" onchange="this.form.submit()" class="form-control form-control-sm" style="border-radius:10px; font-weight:800; width:95px; height:36px; border:1px solid #D0D5DD; background:#ffffff; color:#101828; box-shadow:0 1px 2px rgba(16,24,40,0.05);">
                        @php
                            $selectedYear = (int) ($year ?? date('Y'));
                        @endphp
                        @for($y = 2024; $y <= 2030; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>

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
                <table class="leave-table js-datatable" style="width:100%;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th class="text-left" style="min-width: 180px;">Employee</th>
                            <th class="text-center" style="min-width: 150px;">Stage & Policy</th>
                            <th class="text-center" style="min-width: 140px;">Total Allocated</th>
                            <th class="text-center" style="min-width: 140px;">Total Used</th>
                            <th class="text-center" style="min-width: 160px;">Remaining Balances</th>
                            <th class="text-center" style="min-width: 180px;">Monthly & Carry Forward</th>
                            <th class="text-center" style="min-width: 110px;">Status</th>
                            <th class="text-center" style="min-width: 90px; width: 90px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($allocations as $allocation)
                        @php
                        $employeeName = optional($allocation->employee)->display_name
                        ?? optional(optional($allocation->employee)->user)->name
                        ?? 'Unknown Employee';

                        $employeeCode = optional($allocation->employee)->employee_code
                        ?? optional($allocation->employee)->code
                        ?? 'EMP';

                        $initial = strtoupper(substr(trim($employeeName), 0, 1));
                        @endphp

                        <tr>
                            <td class="text-center"><strong>{{ $loop->iteration }}</strong></td>

                             <td class="text-left">
                                 <div class="leave-employee">
                                     @php
                                         $passportPhotoUrl = resolveEmployeeAdminAvatar($allocation->employee);
                                     @endphp
                                     @if($passportPhotoUrl)
                                         <div class="leave-avatar">
                                             <img src="{{ $passportPhotoUrl }}"
                                                  alt="{{ $employeeName }}"
                                                  onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                             <span style="display: none;">{{ $initial }}</span>
                                         </div>
                                     @else
                                         <div class="leave-avatar">{{ $initial }}</div>
                                     @endif
                                    <div>
                                        <div class="leave-employee-name">{{ $employeeName }}</div>
                                        <div class="leave-employee-meta">{{ $employeeCode }}</div>
                                        <div class="text-muted mt-1" style="font-size:10px; font-weight: 700;">Year: <strong>{{ $allocation->year }}</strong></div>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="mb-1">
                                    <span class="leave-pill pill-stage">
                                        <i class="fas fa-user-clock"></i>
                                        {{ ucfirst(str_replace('_', ' ', $allocation->employment_stage ?? '-')) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="leave-pill pill-policy">
                                        <i class="fas fa-shield-alt"></i>
                                        {{ optional($allocation->policy)->policy_name ?? 'Default Policy' }}
                                    </span>
                                </div>
                            </td>

                            

                            <td class="text-center">
                                <div class="leave-metric">{{ number_format((float) $allocation->total_allocated, 2) }}</div>
                                <div class="leave-breakdown-box">
                                    <span class="leave-breakdown-item badge-paid">Paid: <span>{{ number_format((float)$allocation->paid_allocated, 2) }}</span></span>
                                    <span class="leave-breakdown-item badge-sick">Sick: <span>{{ number_format((float)$allocation->sick_allocated, 2) }}</span></span>
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="leave-metric">{{ number_format((float) $allocation->total_used, 2) }}</div>
                                <div class="leave-breakdown-box">
                                    <span class="leave-breakdown-item badge-paid">Paid: <span>{{ number_format((float)$allocation->paid_used, 2) }}</span></span>
                                    <span class="leave-breakdown-item badge-sick">Sick: <span>{{ number_format((float)$allocation->sick_used, 2) }}</span></span>
                                </div>
                                @if((float) $allocation->lwp_used > 0)
                                    <div class="mt-1">
                                        <span class="leave-pill pill-lwp">
                                            <i class="fas fa-exclamation-circle"></i> LWP: {{ number_format((float) $allocation->lwp_used, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="leave-metric text-success">{{ number_format((float) $allocation->total_remaining, 2) }}</div>
                                <div class="leave-breakdown-box">
                                    <span class="leave-breakdown-item badge-paid">Paid Rem: <span>{{ number_format((float)$allocation->paid_remaining, 2) }}</span></span>
                                    <span class="leave-breakdown-item badge-sick">Sick Rem: <span>{{ number_format((float)$allocation->sick_remaining, 2) }}</span></span>
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="leave-breakdown-box" style="font-size:11px; line-height: 1.6;">
                                    <div><span class="badge badge-primary px-2 py-1" style="border-radius:6px; font-weight:800; font-size:11px;">Monthly Rem Paid: {{ number_format((float)$allocation->total_monthly_remaining_paid, 2) }}</span></div>
                                    <div class="mt-1"><span class="badge badge-info px-2 py-1" style="border-radius:6px; font-weight:800; font-size:11px;">Carry Forward: {{ number_format((float)$allocation->monthly_carry_forward, 2) }}</span></div>
                                    <!-- <div class="text-muted mt-1" style="font-size:10px; font-weight:700;">Quota: <strong>{{ number_format((float)$allocation->monthly_quota, 2) }}</strong> / mo</div> -->
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $currentSystemYear = (int) date('Y');
                                @endphp
                                @if($allocation->year < $currentSystemYear || $allocation->is_locked)
                                    <span class="leave-pill" style="background:#FEE4E2; color:#D92D20; border:1px solid #FECDCA;">
                                        <i class="fas fa-lock mr-1"></i> Inactive
                                    </span>
                                @elseif($allocation->year > $currentSystemYear)
                                    <span class="leave-pill" style="background:#EFF8FF; color:#175CD3; border:1px solid #B2DDFF;">
                                        <i class="fas fa-calendar-plus mr-1"></i> Upcoming
                                    </span>
                                @else
                                    <span class="leave-pill" style="background:#E8F5E9; color:#1B5E20; border:1px solid #C8E6C9;">
                                        <i class="fas fa-check-circle mr-1"></i> Active
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="leave-action-wrap justify-content-center">
                                    <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center" style="border-radius:10px; width:34px; height:34px; padding:0; box-shadow: 0 4px 10px rgba(75,0,232,0.15);" data-toggle="modal" data-target="#editAllocationModal{{ $allocation->id }}" title="Edit Record">
                                        <i class="fas fa-edit" style="font-size:13px;"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center ml-1" style="border-radius:10px; width:34px; height:34px; padding:0; box-shadow: 0 4px 10px rgba(220,53,69,0.15);" data-toggle="modal" data-target="#deleteAllocationModal{{ $allocation->id }}" title="Delete Record">
                                        <i class="fas fa-trash-alt" style="font-size:13px;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-wallet"></i>
                                    <div style="font-weight:900;color:var(--leave-text);">
                                        No Allocation Records Found
                                    </div>
                                    <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                        Generate yearly allocation to show employee leave balances here.
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($allocations, 'links'))
            <div class="mt-3">
                {{ $allocations->links() }}
            </div>
            @endif
        </div>
    </div>

    @if($canManageAllocations ?? false)
    @foreach($allocations as $allocation)
    @php
        $empName = optional($allocation->employee)->display_name
            ?? optional(optional($allocation->employee)->user)->name
            ?? 'Unknown Employee';
    @endphp
    <!-- Edit Modal -->
    <div class="modal fade orb-type-modal" id="editAllocationModal{{ $allocation->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('leave-allocations.update', $allocation->id) }}" class="modal-content leave-modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">
                            <i class="fas fa-edit text-primary mr-2"></i>Edit Leave Allocation
                        </h5>
                        <div class="leave-modal-subtitle">Employee: <strong>{{ $empName }}</strong></div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="row">
                        <!-- General Settings -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Year</label>
                            <input type="number" name="year" class="leave-control w-100" value="{{ old('year', $allocation->year) }}" required min="2020" max="2099">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Employment Stage</label>
                            <select name="employment_stage" class="leave-control w-100" required>
                                <option value="permanent" {{ strtolower($allocation->employment_stage) === 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="probation" {{ strtolower($allocation->employment_stage) === 'probation' ? 'selected' : '' }}>Probation</option>
                                <option value="internship" {{ strtolower($allocation->employment_stage) === 'internship' ? 'selected' : '' }}>Internship</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-dark">Leave Policy</label>
                            <select name="policy_id" class="leave-control w-100">
                                <option value="">-- Select Leave Policy --</option>
                                @foreach($policies as $p)
                                    <option value="{{ $p->id }}" {{ $allocation->policy_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->policy_name }} (Total: {{ $p->annual_total_leaves }}, Paid: {{ $p->annual_paid_leaves }}, Sick: {{ $p->annual_sick_leaves }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dates -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Allocation From Date</label>
                            <input type="date" name="allocation_from_date" class="leave-control w-100" value="{{ $allocation->allocation_from_date ? \Carbon\Carbon::parse($allocation->allocation_from_date)->format('Y-m-d') : '' }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Allocation To Date</label>
                            <input type="date" name="allocation_to_date" class="leave-control w-100" value="{{ $allocation->allocation_to_date ? \Carbon\Carbon::parse($allocation->allocation_to_date)->format('Y-m-d') : '' }}">
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12"><h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-calculator mr-1"></i> Allocated Leave Quotas</h6></div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-dark">Paid Allocated</label>
                            <input type="number" step="0.5" min="0" name="paid_allocated" class="leave-control w-100" value="{{ old('paid_allocated', $allocation->paid_allocated) }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-dark">Sick Allocated</label>
                            <input type="number" step="0.5" min="0" name="sick_allocated" class="leave-control w-100" value="{{ old('sick_allocated', $allocation->sick_allocated) }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-dark">Comp-Off Allocated</label>
                            <input type="number" step="0.5" min="0" name="comp_off_allocated" class="leave-control w-100" value="{{ old('comp_off_allocated', $allocation->comp_off_allocated) }}">
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12"><h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-history mr-1"></i> Used Leaves (Manual Adjustment)</h6></div>

                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold text-dark">Paid Used</label>
                            <input type="number" step="0.5" min="0" name="paid_used" class="leave-control w-100" value="{{ old('paid_used', $allocation->paid_used) }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold text-dark">Sick Used</label>
                            <input type="number" step="0.5" min="0" name="sick_used" class="leave-control w-100" value="{{ old('sick_used', $allocation->sick_used) }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold text-dark">Comp-Off Used</label>
                            <input type="number" step="0.5" min="0" name="comp_off_used" class="leave-control w-100" value="{{ old('comp_off_used', $allocation->comp_off_used) }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold text-dark">LWP Used</label>
                            <input type="number" step="0.5" min="0" name="lwp_used" class="leave-control w-100" value="{{ old('lwp_used', $allocation->lwp_used) }}">
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12"><h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-cog mr-1"></i> Monthly & Carry Forward Settings</h6></div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-dark">Monthly Quota</label>
                            <input type="number" step="0.5" min="0" name="monthly_quota" class="leave-control w-100" value="{{ old('monthly_quota', $allocation->monthly_quota) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-dark">Monthly Carry Forward</label>
                            <input type="number" step="0.5" min="0" name="monthly_carry_forward" class="leave-control w-100" value="{{ old('monthly_carry_forward', $allocation->monthly_carry_forward) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-dark">Used This Month</label>
                            <input type="number" step="0.5" min="0" name="monthly_used_this_month" class="leave-control w-100" value="{{ old('monthly_used_this_month', $allocation->monthly_used_this_month) }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-dark">Allocation Reason</label>
                            <input type="text" name="allocation_reason" class="leave-control w-100" value="{{ old('allocation_reason', $allocation->allocation_reason) }}" placeholder="Reason for allocation or adjustment">
                        </div>

                        <div class="col-md-12 mb-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_locked_{{ $allocation->id }}" name="is_locked" value="1" {{ $allocation->is_locked ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-dark" for="is_locked_{{ $allocation->id }}">
                                    Lock Allocation (Prevents automatic recalculation on system cron)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="leave-btn"><i class="fas fa-save mr-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade orb-type-modal" id="deleteAllocationModal{{ $allocation->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('leave-allocations.destroy', $allocation->id) }}" class="modal-content leave-modal-content">
                @csrf
                @method('DELETE')

                <div class="modal-header leave-modal-header bg-danger text-white">
                    <div>
                        <h5 class="leave-modal-title text-white">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Delete Leave Allocation
                        </h5>
                        <div class="leave-modal-subtitle text-white-50">Confirm deletion</div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>

                <div class="modal-body leave-modal-body text-center py-4">
                    <div class="mb-3 text-danger" style="font-size: 42px;">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <p class="font-weight-bold text-dark mb-1" style="font-size: 15px;">
                        Are you sure you want to delete this allocation?
                    </p>
                    <p class="text-muted" style="font-size: 13px;">
                        Employee: <strong>{{ $empName }}</strong><br>
                        Year: <strong>{{ $allocation->year }}</strong> | Stage: <strong>{{ ucfirst($allocation->employment_stage) }}</strong>
                    </p>
                    <div class="alert alert-warning text-left mb-0" style="font-size: 12px;">
                        <i class="fas fa-info-circle mr-1"></i> This action cannot be undone. Any leave balance calculated for this record will be deleted.
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold" style="border-radius:14px; padding: 0 16px; height: 42px;">
                        <i class="fas fa-trash-alt mr-1"></i> Delete Allocation
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
    @endif

</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.DataTable) {
            var exportColumns = [0, 1, 2, 3, 4, 5, 6];

            var exportFormat = {
                body: function (data, row, column, node) {
                    if (typeof data === 'string') {
                        var text = data.replace(/<br\s*\/?>/gi, '\n')
                                       .replace(/<\/div>/gi, '\n')
                                       .replace(/<\/p>/gi, '\n')
                                       .replace(/<\/span>/gi, ' ')
                                       .replace(/<[^>]+>/g, '')
                                       .replace(/&nbsp;/g, ' ')
                                       .replace(/&amp;/g, '&')
                                       .replace(/&lt;/g, '<')
                                       .replace(/&gt;/g, '>');

                        var lines = text.split('\n').map(function(l) {
                            return l.trim();
                        }).filter(function(l) {
                            return l.length > 0;
                        });

                        // Clean Employee column (col 1): strip single letter avatar fallback if present
                        if (column === 1 && lines.length > 1) {
                            if (lines[0].length === 1) {
                                lines.shift();
                            }
                        }

                        return lines.join('\n');
                    }
                    return data;
                }
            };

            $('.js-datatable').each(function() {
                var $t = $(this);
                if ($.fn.DataTable.isDataTable($t)) {
                    $t.DataTable().destroy();
                }
                $t.DataTable({
                    pageLength: 25,
                    responsive: false,
                    scrollX: true,
                    autoWidth: false,
                    language: {
                        emptyTable: '<div class="py-4 text-center"><i class="fas fa-folder-open fa-3x mb-3 text-muted opacity-50"></i><br>No records found</div>'
                    },
                    dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [
                        {
                            extend: 'excel',
                            title: 'OrboOne_Leave_Allocation_Report',
                            filename: 'Leave_Allocation_Report_' + new Date().toISOString().slice(0,10),
                            className: 'btn btn-light border shadow-sm',
                            exportOptions: {
                                columns: exportColumns,
                                format: exportFormat
                            }
                        },
                        {
                            extend: 'csv',
                            title: 'OrboOne_Leave_Allocation_Report',
                            filename: 'Leave_Allocation_Report_' + new Date().toISOString().slice(0,10),
                            className: 'btn btn-light border shadow-sm',
                            exportOptions: {
                                columns: exportColumns,
                                format: exportFormat
                            }
                        },
                        {
                            extend: 'pdf',
                            title: 'OrboOne - Leave Allocation Report',
                            filename: 'Leave_Allocation_Report_' + new Date().toISOString().slice(0,10),
                            orientation: 'landscape',
                            pageSize: 'A4',
                            className: 'btn btn-light border shadow-sm',
                            exportOptions: {
                                columns: exportColumns,
                                format: exportFormat
                            },
                            customize: function (doc) {
                                doc.pageMargins = [20, 25, 20, 25];
                                doc.defaultStyle.fontSize = 8;
                                doc.styles.tableHeader.fontSize = 9;
                                doc.styles.tableHeader.fillColor = '#4B00E8';
                                doc.styles.tableHeader.color = '#FFFFFF';
                                doc.styles.tableHeader.alignment = 'center';
                                doc.styles.title = {
                                    color: '#4B00E8',
                                    fontSize: 14,
                                    alignment: 'center',
                                    bold: true,
                                    margin: [0, 0, 0, 10]
                                };

                                var objLayout = {};
                                objLayout['hLineWidth'] = function(i) { return 0.5; };
                                objLayout['vLineWidth'] = function(i) { return 0; };
                                objLayout['hLineColor'] = function(i) { return '#E7EAF3'; };
                                objLayout['paddingLeft'] = function(i) { return 5; };
                                objLayout['paddingRight'] = function(i) { return 5; };
                                objLayout['paddingTop'] = function(i) { return 5; };
                                objLayout['paddingBottom'] = function(i) { return 5; };
                                doc.content[1].layout = objLayout;

                                doc.content[1].table.widths = ['5%', '22%', '18%', '13%', '13%', '14%', '15%'];

                                // Align table cells
                                var tableBody = doc.content[1].table.body;
                                for (var i = 1; i < tableBody.length; i++) {
                                    for (var j = 0; j < tableBody[i].length; j++) {
                                        if (j === 1) {
                                            tableBody[i][j].alignment = 'left';
                                        } else {
                                            tableBody[i][j].alignment = 'center';
                                        }
                                    }
                                }
                            }
                        },
                        {
                            extend: 'print',
                            title: '',
                            className: 'btn btn-light border shadow-sm',
                            exportOptions: {
                                columns: exportColumns,
                                format: exportFormat
                            },
                            customize: function (win) {
                                var doc = win.document;

                                var style = doc.createElement('style');
                                style.type = 'text/css';
                                style.innerHTML = `
                                    @page {
                                        size: A4 landscape;
                                        margin: 10mm 12mm;
                                    }
                                    body {
                                        font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
                                        color: #101828 !important;
                                        background: #ffffff !important;
                                        margin: 0 !important;
                                        padding: 15px !important;
                                        -webkit-print-color-adjust: exact !important;
                                        print-color-adjust: exact !important;
                                    }
                                    .print-header-box {
                                        display: flex;
                                        align-items: center;
                                        justify-content: space-between;
                                        border-bottom: 2px solid #4B00E8;
                                        padding-bottom: 12px;
                                        margin-bottom: 18px;
                                    }
                                    .print-brand {
                                        font-size: 22px;
                                        font-weight: 900;
                                        color: #4B00E8;
                                        letter-spacing: -0.5px;
                                    }
                                    .print-subbrand {
                                        font-size: 12px;
                                        color: #667085;
                                        font-weight: 700;
                                    }
                                    .print-title-right {
                                        text-align: right;
                                    }
                                    .print-title {
                                        font-size: 18px;
                                        font-weight: 900;
                                        color: #101828;
                                    }
                                    .print-meta {
                                        font-size: 11px;
                                        color: #667085;
                                        margin-top: 2px;
                                    }
                                    table.print-table {
                                        width: 100% !important;
                                        border-collapse: collapse !important;
                                        margin-top: 10px !important;
                                        font-size: 11px !important;
                                    }
                                    table.print-table th {
                                        background-color: #4B00E8 !important;
                                        color: #ffffff !important;
                                        font-size: 11px !important;
                                        font-weight: 800 !important;
                                        text-transform: uppercase !important;
                                        letter-spacing: 0.5px !important;
                                        padding: 10px 12px !important;
                                        border: 1px solid #4B00E8 !important;
                                        text-align: center !important;
                                        vertical-align: middle !important;
                                    }
                                    table.print-table th:nth-child(2) {
                                        text-align: left !important;
                                    }
                                    table.print-table td {
                                        padding: 8px 10px !important;
                                        border: 1px solid #E7EAF3 !important;
                                        vertical-align: middle !important;
                                        text-align: center !important;
                                        line-height: 1.5 !important;
                                        white-space: pre-line !important;
                                    }
                                    table.print-table td:nth-child(2) {
                                        text-align: left !important;
                                    }
                                    table.print-table tr:nth-child(even) td {
                                        background-color: #F9FAFB !important;
                                    }
                                    .print-footer-box {
                                        margin-top: 25px;
                                        padding-top: 12px;
                                        border-top: 1px solid #E7EAF3;
                                        font-size: 10px;
                                        color: #667085;
                                        display: flex;
                                        justify-content: space-between;
                                    }
                                `;
                                doc.head.appendChild(style);

                                var today = new Date();
                                var dateStr = today.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + today.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                                var headerHtml = `
                                    <div class="print-header-box">
                                        <div>
                                            <div class="print-brand">OrboOne HRMS</div>
                                            <div class="print-subbrand">Employee Leave Management System</div>
                                        </div>
                                        <div class="print-title-right">
                                            <div class="print-title">Leave Allocation Report</div>
                                            <div class="print-meta">Printed: ${dateStr}</div>
                                        </div>
                                    </div>
                                `;

                                var footerHtml = `
                                    <div class="print-footer-box">
                                        <div>Confidential - OrboOne HRMS</div>
                                        <div>Official Leave Allocation Statement</div>
                                    </div>
                                `;

                                var $body = $(doc.body);
                                $body.find('h1').remove();
                                $body.prepend(headerHtml);
                                $body.append(footerHtml);

                                var $table = $body.find('table');
                                $table.addClass('print-table');
                                $table.css('width', '100%');
                            }
                        }
                    ]
                });
            });
        }
    });

    function triggerLeaveExport(type) {
        if ($.fn.DataTable.isDataTable('.js-datatable')) {
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
        } else {
            alert('No records available to export.');
        }
    }
</script>
@endsection