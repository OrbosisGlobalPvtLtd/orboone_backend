@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Blocked / Unlock Requests')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        overflow-x: hidden !important;
    }

    .att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 16px 12px 36px;
    }

    .att-container {
        max-width: 1480px;
        margin: 0 auto;
    }

    .att-hero {
        background: linear-gradient(135deg, #4B00E8 0%, #7600EC 55%, #9A00F5 100%);
        border-radius: 26px !important;
        padding: 30px;
        margin-bottom: 18px;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .20);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .att-hero:before {
        content: "";
        position: absolute;
        right: -80px;
        top: -110px;
        width: 360px;
        height: 360px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .12);
        pointer-events: none;
    }

    .att-kicker {
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        opacity: .9;
        margin-bottom: 10px;
        display: flex;
        gap: 9px;
        align-items: center;
    }

    .att-title {
        font-size: 34px;
        font-weight: 950;
        margin: 0;
        line-height: 1.1;
        color: #fff;
    }

    .att-subtitle {
        font-size: 15px;
        font-weight: 650;
        margin-top: 10px;
        opacity: .92;
        max-width: 850px;
    }

    .att-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .att-btn {
        border-radius: 14px;
        padding: 13px 18px;
        font-weight: 950;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        text-decoration: none !important;
        white-space: nowrap;
        border: 0;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .att-btn-light {
        background: #fff;
        color: #101828 !important;
        box-shadow: 0 10px 22px rgba(16, 24, 40, .08);
    }

    .att-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary) !important;
    }

    .att-metric-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .att-metric {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 14px 14px 10px;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .055);
        position: relative;
        overflow: hidden;
        min-height: 92px;
    }

    .att-metric:after {
        content: "";
        position: absolute;
        right: -22px;
        top: -30px;
        width: 86px;
        height: 86px;
        border-radius: 50%;
        background: var(--metric-soft, #F4F2FF);
        pointer-events: none;
    }

    .att-metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        position: relative;
        z-index: 1;
    }

    .att-metric-icon {
        width: 36px;
        height: 36px;
        border-radius: 13px;
        background: var(--metric-soft, #F4F2FF);
        color: var(--metric-color, #4B00E8);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .att-metric-value {
        font-size: 25px;
        font-weight: 950;
        color: #101828;
        line-height: 1;
    }

    .att-metric-label {
        font-size: 11px;
        font-weight: 950;
        color: #475467;
        text-transform: uppercase;
        margin-top: 14px;
        position: relative;
        z-index: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .att-metric-line {
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--metric-color, #4B00E8), transparent);
        margin-top: 8px;
    }

    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px !important;
        box-shadow: var(--orb-shadow);
        overflow: hidden !important;
    }

    .att-section-head {
        padding: 18px 22px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #fff, #FAFBFF);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .att-section-title {
        font-size: 19px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .att-section-title i {
        color: var(--orb-primary);
    }

    .att-section-sub {
        font-size: 13px;
        color: var(--orb-muted);
        font-weight: 650;
        margin-top: 4px;
    }

    .att-head-badges {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .att-total-pill {
        border: 1px solid var(--orb-border);
        background: #F8FAFC;
        color: var(--orb-text);
        border-radius: 12px;
        padding: 9px 12px;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .att-total-pill.orange {
        border-color: #FED7AA;
        background: #FFF7ED;
        color: #C2410C;
    }

    .att-total-pill.purple {
        border-color: #E0D7FF;
        background: #F5F2FF;
        color: #4B00E8;
    }

    .att-filter-panel {
        padding: 16px 22px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .att-filter-grid label {
        font-size: 10px;
        font-weight: 950;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
        display: block;
    }

    .att-filter-grid .form-control {
        height: 43px;
        border-radius: 14px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        font-weight: 750;
        padding: 0 14px;
        box-shadow: none !important;
        background: #fff;
    }

    .att-filter-grid .form-control:focus {
        border-color: var(--orb-primary);
    }

    .att-table-wrap {
        padding: 0 16px 16px;
    }

    .att-table-responsive {
        width: 100% !important;
        overflow: hidden !important;
    }

    .att-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .att-table thead th {
        background: #F8FAFC !important;
        color: #344054 !important;
        font-size: 10px !important;
        font-weight: 950 !important;
        text-transform: uppercase;
        padding: 14px 12px !important;
        border-top: 1px solid #EAECF0 !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
    }

    .att-table td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 14px 12px !important;
        vertical-align: middle;
        font-size: 13px;
        color: var(--orb-text);
    }

    .att-table tbody tr {
        transition: .2s ease;
    }

    .att-table tbody tr:hover td {
        background: #FAF8FF;
    }

    .att-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--orb-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 950;
        color: var(--orb-primary);
        flex-shrink: 0;
        position: relative !important;
        overflow: hidden !important;
    }

    .att-avatar-img {
        width: 42px !important;
        height: 42px !important;
        border-radius: 14px !important;
        object-fit: cover !important;
        display: block !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
        flex-shrink: 0 !important;
    }

    .att-emp {
        display: flex;
        gap: 10px;
        align-items: center;
        min-width: 0;
    }

    .att-emp-name {
        font-weight: 900;
        color: var(--orb-text);
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .att-dept {
        font-size: 11px;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 2px;
    }

    .att-emp-code {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 700;
    }

    .att-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge-present {
        background: #dcfce7;
        color: #166534;
    }

    .badge-absent {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-half_day {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-leave {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-week_off {
        background: #f1f5f9;
        color: #475569;
    }

    .badge-holiday {
        background: #ede9fe;
        color: #5b21b6;
    }

    .badge-punch_blocked {
        background: #ffe4e6;
        color: #be123c;
    }

    .badge-default {
        background: #f1f5f9;
        color: #475569;
    }

    .att-action-btn {
        border-radius: 999px;
        padding: 7px 14px;
        font-size: 11px;
        font-weight: 950;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 0;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .att-action-approve {
        background: #DCFCE7;
        color: #15803D;
    }

    .att-action-approve:hover {
        background: #15803D;
        color: #fff;
    }

    .att-action-edit {
        background: #F4F2FF;
        color: #4B00E8;
    }

    .att-action-edit:hover {
        background: #4B00E8;
        color: #fff;
    }

    .dataTables_wrapper {
        width: 100% !important;
        overflow: hidden !important;
    }

    .dataTables_wrapper>.row:first-child {
        background: #fff;
        border-bottom: 1px solid var(--orb-border);
        padding: 13px 16px;
        margin: 0 -16px 0 !important;
        align-items: center !important;
    }

    .dataTables_wrapper>.row:last-child {
        border-top: 1px solid var(--orb-border);
        padding: 12px 16px 0;
        margin: 12px -16px 0 !important;
    }

    .dataTables_scroll {
        width: 100% !important;
        border-radius: 18px;
        overflow: hidden !important;
        margin-top: 16px;
    }

    .dataTables_scrollHead {
        width: 100% !important;
        background: #F8FAFC;
        overflow: hidden !important;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollBody {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        border-bottom: 0 !important;
    }

    .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 20px;
    }

    .dataTables_wrapper .dt-buttons {
        display: flex !important;
        justify-content: flex-end !important;
        gap: 8px;
        flex-wrap: wrap;
    }

    .dataTables_wrapper .dt-buttons .btn {
        height: 34px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        background: #fff !important;
        color: #101828 !important;
        border: 1px solid #E7EAF3 !important;
        padding: 0 14px !important;
        margin-bottom: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: all 0.2s ease !important;
        box-shadow: none !important;
    }

    .dataTables_wrapper .dt-buttons .btn:hover {
        background: #F4F2FF !important;
        color: #4B00E8 !important;
        border-color: #D9CCFF !important;
    }

    .dataTables_length select {
        padding: 4px 22px 4px 8px !important;
        border-radius: 10px !important;
    }

    .dataTables_info {
        color: var(--orb-muted);
        font-weight: 700;
    }

    .page-link {
        border-radius: 10px;
        margin: 0 2px;
        border-color: var(--orb-border);
        color: var(--orb-primary);
        font-weight: 800;
    }

    .page-item.active .page-link {
        background: var(--orb-primary) !important;
        border-color: var(--orb-primary) !important;
        color: #fff !important;
    }

    @media(max-width:1300px) {
        .att-metric-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .att-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width:768px) {
        .att-page {
            padding: 12px 8px 25px;
        }

        .att-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px;
            border-radius: 24px;
        }

        .att-title {
            font-size: 25px;
        }

        .att-hero-actions {
            width: 100%;
        }

        .att-btn {
            width: 100%;
        }

        .att-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .att-section-head {
            flex-direction: column;
        }

        .att-head-badges {
            justify-content: flex-start;
        }

        .att-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="att-page">
    <div class="att-container">

        <div class="att-hero">
            <div>
                <div class="att-kicker"><i class="fas fa-calendar-check"></i> HRMS &bull; ATTENDANCE</div>
                <h3 class="att-title">Pending Unlock / HR Approval</h3>
                <div class="att-subtitle">Manage employees blocked after attendance cutoff and approve admin unlock requests.</div>
            </div>
            <div class="att-hero-actions">
                <a href="{{ route('attendances.index') }}" class="att-btn att-btn-light">
                    <i class="fas fa-chart-line"></i> Attendance Dashboard
                </a>
                <a href="{{ route('attendances.record') }}" class="att-btn att-btn-light">
                    <i class="fas fa-list"></i> Attendance Records
                </a>
            </div>
        </div>

        @if(session('status'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('status') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        <div class="att-metric-grid">
            <div class="att-metric" style="--metric-color:#4B00E8;--metric-soft:#F4F2FF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-lock"></i></div>
                    <div class="att-metric-value">{{ $stats['total_blocked'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Total Pending</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#F59E0B;--metric-soft:#FEF3C7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-unlock-alt"></i></div>
                    <div class="att-metric-value">{{ $stats['pending_unlock'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Pending Unlock</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#EA580C;--metric-soft:#FFEDD5;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-user-lock"></i></div>
                    <div class="att-metric-value">{{ $stats['total_blocked'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Auto Blocked</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#16A34A;--metric-soft:#DCFCE7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="att-metric-value">{{ $stats['unlocked_today'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Approved Today</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#6366F1;--metric-soft:#E0E7FF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-user-slash"></i></div>
                    <div class="att-metric-value">{{ $stats['missed_punch'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Missed Punch</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-user-edit"></i></div>
                    <div class="att-metric-value">{{ $stats['manual_punch'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Manual Punch</div>
                <div class="att-metric-line"></div>
            </div>
        </div>

        <div class="att-card">
            <div class="att-section-head">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-user-lock"></i> Pending Unlock & HR Approvals</h5>
                    <div class="att-section-sub">Manage blocked attendance status and unlock requests under HR approval workflow.</div>
                </div>
                <div class="att-head-badges align-items-center">
                    <span class="att-total-pill orange"><i class="fas fa-lock"></i> Total Blocked: {{ $stats['total_blocked'] ?? 0 }}</span>
                    <span class="att-total-pill purple"><i class="fas fa-unlock-alt"></i> Pending Unlock: {{ $stats['pending_unlock'] ?? 0 }}</span>
                    <a href="{{ route('attendances.pending-approval') }}" class="att-btn att-btn-light" style="padding: 9px 14px; font-size: 12px; height: 36px; border-radius: 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-undo"></i> Reset Filters
                    </a>
                </div>
            </div>

            <div class="att-filter-panel">
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
                                <!-- <th style="width: 100px;">Punch In</th>
                                <th style="width: 100px;">Punch Out</th> -->
                                <!-- <th style="width: 150px;">Unlock Type</th>
                                <th style="width: 150px;">Unlock Reason</th> -->
                                <th style="width: 150px;" class="text-right no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                            @php
                            // Rule 1: Jo employee unlock/approved ho chuka hai, wo Pending Approval table me bilkul show nahi hona chahiye.
                            if (request('flag') !== 'unlocked' && ($attendance->is_admin_unlocked || $attendance->unlocked_at)) {
                            continue;
                            }

                            $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                            $statusCode = $typeCode !== 'default' ? $typeCode : ($attendance->attendance_status ?: 'default');
                            $statusLabel = $statusCode === 'punch_blocked'
                            ? 'Punch Blocked'
                            : (optional($attendance->attendanceType)->name ?? ucwords(str_replace('_', ' ', $statusCode)));
                            $attDate = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-';
                            @endphp
                            <tr>
                                <td>
                                    <div class="att-emp">
                                        @php
                                        $passportPhotoUrl = resolveEmployeePassportPhoto($attendance->employee ?? $attendance);
                                        $employeeName = optional($attendance->user)->name ?? 'Employee';
                                        $employeeInitial = resolveEmployeeInitials($attendance->employee ?? $attendance);
                                        @endphp
                                        <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                            @if($passportPhotoUrl)
                                            <img
                                                src="{{ $passportPhotoUrl }}"
                                                alt="{{ $employeeName }}"
                                                class="hrms-emp-avatar-img"
                                                onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');">
                                            <span class="hrms-emp-avatar-fallback is-hidden">
                                                {{ $employeeInitial }}
                                            </span>
                                            @else
                                            <span class="hrms-emp-avatar-fallback is-visible">
                                                {{ $employeeInitial }}
                                            </span>
                                            @endif
                                        </span>
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
                                <td><span class="att-emp-code">{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</span></td>
                                <td><strong>{{ $attDate }}</strong></td>
                                <td>
                                    <span class="att-badge badge-{{ $statusCode }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-danger font-weight-bold" style="font-size: 11px;">
                                        {{ $attendance->block_reason ?? $attendance->auto_block_reason ?? $attendance->blocked_reason ?? 'Punch blocked' }}
                                    </span>
                                </td>
                                <!-- <td>{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}</td>
                                    <td>{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}</td> -->
                                <!-- <td>
                                        @if($attendance->is_admin_unlocked)
                                            <span class="badge badge-success px-2 py-1">{{ str_replace('_', ' ', strtoupper($attendance->unlock_type)) }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-muted">{{ $attendance->unlock_reason_category ?? '-' }}</div>
                                    </td> -->
                                <td class="text-right no-export">
                                    <div class="d-flex justify-content-end align-items-center" style="gap: 6px;">
                                        @if(($canUnlockAttendance ?? false) && ($attendance->is_blocked || $attendance->is_punch_blocked || $statusCode === 'punch_blocked'))
                                        <button type="button" class="att-action-btn att-action-approve" data-toggle="modal" data-target="#unlockModal{{ $attendance->id }}" title="Unlock/Approve">
                                            <i class="fas fa-unlock"></i> Unlock
                                        </button>
                                        @endif
                                        @if($canManageAttendance ?? false)
                                        <button type="button" class="att-action-btn att-action-edit" data-toggle="modal" data-target="#editModal{{ $attendance->id }}" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">No unlock requests pending approval.</td>
                            </tr>
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
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('pendingFilterForm');
        const filters = document.querySelectorAll('.auto-filter');

        filters.forEach(function(filter) {
            filter.addEventListener('change', () => {
                if (form) form.submit();
            });
        });

        if ($.fn.DataTable.isDataTable('#pendingDataTable')) {
            $('#pendingDataTable').DataTable().destroy();
        }

        $('#pendingDataTable').DataTable({
            destroy: true,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            ordering: true,
            responsive: false,
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,
            paging: true,
            info: true,
            searching: false,
            dom: "<'row align-items-center mb-3'<'col-md-4'l><'col-md-8 text-md-right'B>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>",
            buttons: [{
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-light border',
                    orientation: 'landscape',
                    pageSize: 'A3',
                    title: 'Orbosis HRMS Pending Unlock & HR Approvals',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-light border',
                    title: 'Orbosis HRMS Pending Unlock & HR Approvals',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ],
            language: {
                lengthMenu: 'Show _MENU_ entries',
                emptyTable: 'No pending approvals found.',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        setTimeout(function() {
            $('#pendingDataTable').DataTable().columns.adjust();
        }, 250);
    });
</script>
@endsection