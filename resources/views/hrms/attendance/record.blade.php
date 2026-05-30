@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', request()->routeIs('hrms.attendance.my') ? 'My Attendance' : 'Attendance Records')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
@php
    $isMyAttendance = request()->routeIs('hrms.attendance.my');
@endphp

<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 16px 12px 36px;
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    .att-hero {
        background: linear-gradient(135deg, #4B00E8 0%, #7600EC 55%, #9A00F5 100%);
        border-radius: 30px;
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
        background: rgba(255, 255, 255, .12)
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
        border: 0;
        border-radius: 14px;
        padding: 13px 18px;
        font-weight: 950;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .att-btn-light {
        background: #fff;
        color: #101828 !important;
        box-shadow: 0 10px 22px rgba(16, 24, 40, .08);
    }

    .att-btn-light:hover {
        background: #F9F5FF;
        color: var(--orb-primary) !important;
    }

    .att-metric-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
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
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--orb-shadow);
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
        color: var(--orb-primary)
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
        border: 1px solid #FAD7AA;
        background: #FFF7ED;
        color: #C2410C;
        border-radius: 12px;
        padding: 9px 12px;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    .att-filter-panel {
        padding: 16px 22px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
    }

    .att-filter-group label {
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        color: #667085;
        margin-bottom: 6px;
        display: block;
        letter-spacing: .04em;
    }

    .att-filter-group .form-control {
        height: 43px;
        border-radius: 14px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        font-weight: 750;
        padding: 0 14px;
        box-shadow: none !important;
        background: #fff;
    }

    .att-filter-group .form-control:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .10) !important;
    }

    .att-table-wrap {
        padding: 0 16px 16px;
    }

    .att-table {
        width: 100% !important;
        min-width: 1560px;
        border-collapse: separate !important;
        border-spacing: 0;
        margin: 0 !important;
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
        vertical-align: middle !important;
    }

    .att-table tbody td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 13px 12px !important;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF !important;
    }

    .att-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0
    }

    .att-avatar {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--orb-soft), #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 950;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .08);
        flex-shrink: 0;
        position: relative !important;
        overflow: hidden !important;
    }

    .att-avatar-img {
        width: 40px !important;
        height: 40px !important;
        border-radius: 14px !important;
        object-fit: cover !important;
        display: block !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
        flex-shrink: 0 !important;
    }

    .att-emp-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--orb-text);
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .att-emp-code {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .att-badge,
    .mode-badge,
    .flag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-weight: 950;
        text-transform: uppercase
    }

    .att-badge,
    .mode-badge {
        padding: 6px 10px;
        font-size: 10px
    }

    .flag {
        padding: 4px 8px;
        font-size: 9px;
        margin: 2px 3px 2px 0
    }

    .badge-present {
        background: #DCFCE7;
        color: #166534
    }

    .badge-absent,
    .badge-lwp {
        background: #FEE2E2;
        color: #991B1B
    }

    .badge-half_day {
        background: #FEF3C7;
        color: #92400E
    }

    .badge-leave {
        background: #DBEAFE;
        color: #1E40AF
    }

    .badge-week_off {
        background: #F1F5F9;
        color: #475569
    }

    .badge-holiday {
        background: #EDE9FE;
        color: #5B21B6
    }

    .badge-punch_blocked {
        background: #FFE4E6;
        color: #BE123C
    }

    .badge-default {
        background: #F1F5F9;
        color: #475569
    }

    .mode-wfo {
        background: #EEF2FF;
        color: #3730A3
    }

    .mode-wfh {
        background: #ECFEFF;
        color: #155E75
    }

    .mode-default {
        background: #F1F5F9;
        color: #475569
    }

    .flag-late {
        background: #FFF7ED;
        color: #C2410C
    }

    .flag-early,
    .flag-blocked {
        background: #FEF2F2;
        color: #B42318
    }

    .flag-missed {
        background: #FEF3C7;
        color: #92400E
    }

    .flag-clear {
        background: #F1F5F9;
        color: #475569
    }

    .att-task {
        max-width: 205px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 650
    }

    .att-action-wrap {
        display: flex;
        justify-content: flex-end
    }

    .action-dot {
        width: 35px;
        height: 35px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #475467;
        display: flex;
        align-items: center;
        justify-content: center
    }

    .action-dot:hover {
        background: var(--orb-soft);
        color: var(--orb-primary)
    }

    .dropdown-menu.att-action-menu {
        border: 1px solid var(--orb-border);
        border-radius: 15px;
        box-shadow: 0 18px 45px rgba(16, 24, 40, .14);
        padding: 7px;
        min-width: 185px
    }

    .att-action-menu .dropdown-item {
        border-radius: 11px;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px
    }

    .att-action-menu .dropdown-item:hover {
        background: var(--orb-soft);
        color: var(--orb-primary)
    }

    /* Premium unified Datatables styles */
    .leave-dt-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 24px;
        border-top: 1px solid #E7EAF3;
        border-bottom: 1px solid #E7EAF3;
        background: #fff;
    }

    .leave-dt-left {
        display: flex;
        align-items: center;
    }

    .leave-dt-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dt-buttons {
        display: flex !important;
        gap: 8px;
    }

    .leave-export-btn {
        height: 38px !important;
        border-radius: 12px !important;
        padding: 8px 16px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        color: #344054 !important;
        background: #fff !important;
        border: 1px solid #E7EAF3 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 1px 2px rgba(16,24,40,0.05) !important;
        transition: all 0.2s ease !important;
        margin-bottom: 0 !important;
    }

    .leave-export-btn:hover {
        background: #F9F5FF !important;
        color: #4B00E8 !important;
        border-color: #D9CCFF !important;
    }

    .dataTables_length select {
        border-radius: 10px !important;
        padding: 4px 22px 4px 8px !important;
        height: 38px !important;
        border: 1px solid #E7EAF3 !important;
    }

    .leave-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        background: #fff;
        border-top: 1px solid #E7EAF3;
    }

    .dataTables_scroll {
        border: 1px solid #EEF2F6;
        border-radius: 18px;
        overflow: hidden;
        margin: 16px 24px;
    }

    .dataTables_scrollHead {
        background: #F8FAFC
    }

    .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        border-bottom: 0 !important
    }

    .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #D0D5DD;
        border-radius: 20px
    }

    .dataTables_info {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 700
    }

    .page-link {
        border-radius: 10px !important;
        margin: 0 2px;
        border-color: var(--orb-border);
        color: var(--orb-primary);
        font-weight: 800
    }

    .page-item.active .page-link {
        background: var(--orb-primary) !important;
        border-color: var(--orb-primary) !important;
        color: #fff !important
    }

    @media(max-width:1300px) {
        .att-metric-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .att-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width:992px) {
        .leave-dt-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .leave-dt-right {
            justify-content: flex-end;
        }
    }

    @media(max-width:768px) {
        .att-page {
            padding: 12px 8px 25px
        }

        .att-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px;
            border-radius: 24px
        }

        .att-title {
            font-size: 25px
        }

        .att-hero-actions {
            width: 100%
        }

        .att-btn {
            width: 100%
        }

        .att-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .att-section-head {
            flex-direction: column;
            gap: 12px;
        }

        .att-head-badges {
            justify-content: flex-start
        }

        .att-filter-grid {
            grid-template-columns: 1fr
        }
    }
</style>

<div class="att-page">
    <div class="att-container">

        @php
        $recordItems = $attendances instanceof \Illuminate\Pagination\AbstractPaginator ? collect($attendances->items()) : collect($attendances);
        $totalRecords = $recordItems->count();
        $presentRecords = $recordItems->filter(fn($a) => optional($a->attendanceType)->code === 'present')->count();
        $lateRecords = $recordItems->filter(fn($a) => ($a->is_late ?? $a->late_mark ?? false))->count();
        $blockedRecords = $recordItems->filter(fn($a) => ($a->is_blocked ?? $a->is_punch_blocked ?? false))->count();
        $missedRecords = $recordItems->filter(fn($a) => ($a->missed_punch ?? false))->count();
        $halfDayRecords = $recordItems->filter(fn($a) => ($a->is_half_day ?? false))->count();
        $wfoRecords = $recordItems->filter(fn($a) => strtolower($a->work_mode ?? '') === 'wfo')->count();
        $wfhRecords = $recordItems->filter(fn($a) => strtolower($a->work_mode ?? '') === 'wfh')->count();
        @endphp

        <div class="att-hero">
            <div>
                <div class="att-kicker">
                    <i class="fas fa-calendar-check"></i>
                    {{ $isMyAttendance ? 'EMPLOYEE • ATTENDANCE' : 'HRMS • ATTENDANCE' }}
                </div>
                <h3 class="att-title">{{ $isMyAttendance ? 'My Attendance' : 'Attendance Records' }}</h3>
                <div class="att-subtitle">
                    {{ $isMyAttendance 
                        ? 'Track your daily punches, working hours, late marks, missed punches, and monthly attendance summary.'
                        : 'Overall employee attendance records with filters, shift timing, work duration, flags and export options.' }}
                </div>
            </div>
            @if(!$isMyAttendance)
            <div class="att-hero-actions">
                <a href="{{ route('attendances.index') }}" class="att-btn att-btn-light">
                    <i class="fas fa-chart-line"></i> Attendance Dashboard
                </a>
                <a href="{{ route('attendances.record') }}" class="att-btn att-btn-light">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
            @endif
        </div>

        <div class="att-metric-grid">
            <div class="att-metric" style="--metric-color:#12B76A;--metric-soft:#E8F8EF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-list"></i></div>
                    <div class="att-metric-value">{{ $totalRecords }}</div>
                </div>
                <div class="att-metric-label">Total Records</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#16A34A;--metric-soft:#DCFCE7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="att-metric-value">{{ $presentRecords }}</div>
                </div>
                <div class="att-metric-label">Present</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#F97316;--metric-soft:#FFF7ED;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="att-metric-value">{{ $lateRecords }}</div>
                </div>
                <div class="att-metric-label">Late</div>
                <div class="att-metric-line"></div>
            </div>
            @if(!$isMyAttendance)
            <div class="att-metric" style="--metric-color:#E11D48;--metric-soft:#FFE4E6;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-user-lock"></i></div>
                    <div class="att-metric-value">{{ $blockedRecords }}</div>
                </div>
                <div class="att-metric-label">Blocked</div>
                <div class="att-metric-line"></div>
            </div>
            @endif
            <div class="att-metric" style="--metric-color:#D97706;--metric-soft:#FEF3C7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="att-metric-value">{{ $missedRecords }}</div>
                </div>
                <div class="att-metric-label">Missed Punch</div>
                <div class="att-metric-line"></div>
            </div>
            @if($isMyAttendance)
            <div class="att-metric" style="--metric-color:#F59E0B;--metric-soft:#FEF3C7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-business-time"></i></div>
                    <div class="att-metric-value">{{ $halfDayRecords }}</div>
                </div>
                <div class="att-metric-label">Half Day</div>
                <div class="att-metric-line"></div>
            </div>
            @endif
            <div class="att-metric" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-building"></i></div>
                    <div class="att-metric-value">{{ $wfoRecords }}</div>
                </div>
                <div class="att-metric-label">WFO</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#0E7490;--metric-soft:#ECFEFF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-home"></i></div>
                    <div class="att-metric-value">{{ $wfhRecords }}</div>
                </div>
                <div class="att-metric-label">WFH</div>
                <div class="att-metric-line"></div>
            </div>
        </div>

        @if(session('status'))
        <div class="alert alert-success" style="border-radius:16px;font-weight:800;">{{ session('status') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger" style="border-radius:16px;font-weight:800;">{{ session('error') }}</div>
        @endif

        <div class="att-card">
            <div class="att-section-head">
                @if($isMyAttendance)
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:50%; background:#F4F2FF; color:#4B00E8; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                        <i class="fas fa-table"></i>
                    </div>
                    <div>
                        <h5 class="att-section-title" style="margin:0; font-size:18px;">My Attendance History</h5>
                        <div class="att-section-sub" style="margin-top:4px;">Review your attendance logs, punch timings, working hours, and status.</div>
                    </div>
                </div>
                @else
                <div>
                    <h5 class="att-section-title"><i class="fas fa-table"></i> Attendance Records List</h5>
                    <div class="att-section-sub">Filters are attached with this table and auto-apply on change/search.</div>
                </div>
                <div class="att-head-badges">
                    <span class="att-total-pill">Total: {{ $totalRecords }}</span>
                    <span class="att-total-pill">Blocked: {{ $blockedRecords }}</span>
                </div>
                @endif
            </div>

            <div class="att-filter-panel">
                <form method="GET" action="{{ $isMyAttendance ? route('hrms.attendance.my') : route('attendances.record') }}" id="dailyAttendanceFilterForm">
                    <div class="att-filter-grid">

                        @if(!$isMyAttendance)
                        <div class="att-filter-group">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control auto-filter-input"
                                value="{{ request('search') }}"
                                placeholder="Name, email, employee code">
                        </div>
                        @endif

                        <div class="att-filter-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control auto-filter" value="{{ request('date') }}">
                        </div>

                        <div class="att-filter-group">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control auto-filter" value="{{ request('from_date') }}">
                        </div>

                        <div class="att-filter-group">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control auto-filter" value="{{ request('to_date') }}">
                        </div>

                        @if(!$isMyAttendance)
                        <div class="att-filter-group">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control auto-filter">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                @php $employeeId = optional($emp->employee)->id ?? $emp->id; @endphp
                                <option value="{{ $employeeId }}" {{ request('employee_id') == $employeeId ? 'selected' : '' }}>
                                    {{ $emp->name ?? optional($emp->user)->name ?? 'Employee' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="att-filter-group">
                            <label>Status</label>
                            <select name="attendance_type_id" class="form-control auto-filter">
                                <option value="">All Status</option>
                                @foreach($attendanceTypes as $type)
                                <option value="{{ $type->id }}" {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Shift</label>
                            <select name="attendance_time_id" class="form-control auto-filter">
                                <option value="">All Shifts</option>
                                @foreach($attendanceTimes ?? [] as $shift)
                                <option value="{{ $shift->id }}" {{ request('attendance_time_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Work Mode</label>
                            <select name="work_mode" class="form-control auto-filter">
                                <option value="">All</option>
                                <option value="wfo" {{ request('work_mode') == 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ request('work_mode') == 'wfh' ? 'selected' : '' }}>WFH</option>
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Flags</label>
                            <select name="flag" class="form-control auto-filter">
                                <option value="">All Records</option>
                                <option value="late" {{ request('flag') == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="early_out" {{ request('flag') == 'early_out' ? 'selected' : '' }}>Early Logout</option>
                                <option value="blocked" {{ request('flag') == 'blocked' ? 'selected' : '' }}>Punch Blocked</option>
                                <option value="missed_punch" {{ request('flag') == 'missed_punch' ? 'selected' : '' }}>Missed Punch</option>
                                <option value="clear" {{ request('flag') == 'clear' ? 'selected' : '' }}>Clear</option>
                            </select>
                        </div>

                        @if($isMyAttendance)
                        <div class="att-filter-group d-flex align-items-end">
                            <a href="{{ route('hrms.attendance.my') }}" class="btn btn-light w-100" style="height:43px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; gap:8px; font-weight:750; border:1px solid #E4E7EC;">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                        @endif

                    </div>
                </form>
            </div>

            <div class="att-table-wrap">
                <table class="table att-table" id="dailyAttendanceDataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            @if(!$isMyAttendance)
                            <th>Employee</th>
                            @endif
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Target Out</th>
                            <th>Gross</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Flags</th>
                            <th>Task Summary</th>
                            <th class="no-export text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($attendances as $attendance)
                        @php
                        $typeCode = optional($attendance->attendanceType)->code ?? 'default';

                        $modeCode = strtolower($attendance->work_mode ?? '');
                        $modeLabel = $modeCode === 'wfh' ? 'WFH' : ($modeCode === 'wfo' ? 'WFO' : '-');
                        $modeClass = in_array($modeCode, ['wfo','wfh']) ? 'mode-'.$modeCode : 'mode-default';

                        $employeeName = optional($attendance->user)->name
                        ?? optional(optional($attendance->employee)->user)->name
                        ?? 'Employee';

                        $employeeCode = optional($attendance->employee)->employee_code ?? 'N/A';

                        $attDate = $attendance->attendance_date
                        ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y')
                        : '-';

                        $punchIn = $attendance->punch_in_time ?? $attendance->punch_in ?? null;
                        $punchOut = $attendance->punch_out_time ?? $attendance->punch_out ?? null;

                        $gross = $attendance->gross_duration
                        ?? (isset($attendance->gross_work_minutes) ? floor($attendance->gross_work_minutes / 60).'h '.($attendance->gross_work_minutes % 60).'m' : '-');

                        $net = $attendance->net_duration
                        ?? (isset($attendance->working_minutes) ? floor($attendance->working_minutes / 60).'h '.($attendance->working_minutes % 60).'m' : '-');

                        $workSummary = optional($attendance->workLogs->first())->work_summary
                        ?? $attendance->punch_out_note
                        ?? '-';

                        $isBlocked = $attendance->is_blocked ?? $attendance->is_punch_blocked ?? false;
                        $isLate = $attendance->is_late ?? $attendance->late_mark ?? false;
                        $isEarly = $attendance->is_early_out ?? $attendance->early_leave_mark ?? false;
                        $isMissed = $attendance->missed_punch ?? false;
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            @if(!$isMyAttendance)
                            <td>
                                <div class="att-emp">
                                    @php
                                        $passportPhotoUrl = resolveEmployeePassportPhoto($attendance->employee ?? $attendance);
                                        $employeeInitial = resolveEmployeeInitials($attendance->employee ?? $attendance);
                                    @endphp
                                    <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                        @if($passportPhotoUrl)
                                            <img
                                                src="{{ $passportPhotoUrl }}"
                                                alt="{{ $employeeName }}"
                                                class="hrms-emp-avatar-img"
                                                onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');"
                                            >
                                            <span class="hrms-emp-avatar-fallback is-hidden">
                                                {{ $employeeInitial }}
                                            </span>
                                        @else
                                            <span class="hrms-emp-avatar-fallback is-visible">
                                                {{ $employeeInitial }}
                                            </span>
                                        @endif
                                    </span>
                                    <div>
                                        <div class="att-emp-name" title="{{ $employeeName }}">
                                            {{ $employeeName }}
                                        </div>
                                        <div class="att-emp-code" title="{{ $employeeCode }}">
                                            {{ $employeeCode }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif

                            <td><strong>{{ $attDate }}</strong></td>

                            <td>
                                <span class="mode-badge {{ $modeClass }}">
                                    {{ $modeLabel }}
                                </span>
                            </td>

                            <td>{{ optional($attendance->attendanceTime)->name ?? '-' }}</td>

                            <td>{{ $punchIn ? \Carbon\Carbon::parse($punchIn)->format('h:i A') : '-' }}</td>

                            <td>{{ $punchOut ? \Carbon\Carbon::parse($punchOut)->format('h:i A') : '-' }}</td>

                            <td>
                                {{ $attendance->target_punch_out_time
                                        ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A')
                                        : '-' }}
                            </td>

                            <td>{{ $gross }}</td>

                            <td><strong>{{ $net }}</strong></td>

                            <td>
                                <span class="att-badge badge-{{ $typeCode }}">
                                    {{ optional($attendance->attendanceType)->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                @if($isLate)
                                <span class="flag flag-late">Late</span>
                                @endif

                                @if($isEarly)
                                <span class="flag flag-early">Early</span>
                                @endif

                                @if($isBlocked)
                                <span class="flag flag-blocked">Blocked</span>
                                @endif

                                @if($isMissed)
                                <span class="flag flag-missed">Missed</span>
                                @endif

                                @if(!$isLate && !$isEarly && !$isBlocked && !$isMissed)
                                <span class="flag flag-clear">Clear</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $firstLog = $attendance->workLogs->first();
                                @endphp
                                @if($firstLog)
                                    @php
                                        $tasks = $firstLog->work_summary_json;
                                        if (is_string($tasks)) {
                                            $tasks = json_decode($tasks, true);
                                        }
                                        $title = 'Work Report Submitted';
                                        $status = 'Completed';
                                        $requirementsList = [];
                                        
                                        if (is_array($tasks)) {
                                            if (array_keys($tasks) !== range(0, count($tasks) - 1)) {
                                                $title = $tasks['title'] ?? ($tasks['task_title'] ?? 'Work Report Submitted');
                                                $status = $tasks['status'] ?? 'Completed';
                                                $requirementsList = $tasks['requirements'] ?? ($tasks['tasks'] ?? []);
                                            } else {
                                                $requirementsList = $tasks;
                                            }
                                        }
                                        $taskCount = is_array($requirementsList) ? count($requirementsList) : 0;
                                        $tasksLabel = $taskCount . ' ' . \Illuminate\Support\Str::plural('Task', $taskCount);
                                        $statusClass = strtolower($status) === 'completed' ? 'badge-present' : 'badge-half_day';
                                    @endphp
                                    <div class="d-flex flex-column gap-1" style="max-width: 200px;">
                                        <div style="font-size: 12px; font-weight: 700; color: #1D2939; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $title }}">
                                            {{ $title }}
                                        </div>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="badge-premium-pill badge-wfo" style="font-size: 9px; padding: 3px 8px; font-weight: 800; border-radius: 6px;">
                                                <i class="fas fa-list-check" style="font-size: 8px;"></i> {{ $tasksLabel }}
                                            </span>
                                            <span class="badge-premium-pill {{ $statusClass }}" style="font-size: 9px; padding: 3px 8px; font-weight: 800; border-radius: 6px; text-transform: uppercase;">
                                                {{ $status }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 12px; font-style: italic;">No Report</span>
                                @endif
                            </td>

                            <td>
                                <div class="att-action-wrap dropdown">
                                    <button type="button" class="action-dot" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                        @if($firstLog)
                                            @php
                                                $tasks = $firstLog->work_summary_json;
                                                if (is_string($tasks)) {
                                                    $tasks = json_decode($tasks, true);
                                                }
                                                
                                                $repTitle = 'Work Report Submitted';
                                                $repDesc = $firstLog->work_summary;
                                                $repStatus = 'Completed';
                                                $requirementsList = [];
                                                $testStatus = ['tested' => false, 'completed' => false];
                                                $issues = [];
                                                $notes = null;

                                                if (is_array($tasks)) {
                                                    if (array_keys($tasks) !== range(0, count($tasks) - 1)) {
                                                        $repTitle = $tasks['title'] ?? ($tasks['task_title'] ?? 'Work Report Submitted');
                                                        $repDesc = $tasks['description'] ?? $firstLog->work_summary;
                                                        $repStatus = $tasks['status'] ?? 'Completed';
                                                        $requirementsList = $tasks['requirements'] ?? ($tasks['tasks'] ?? []);
                                                        
                                                        // Extract test status
                                                        if (isset($tasks['test_status']) && is_array($tasks['test_status'])) {
                                                            $testStatus = [
                                                                'tested' => $tasks['test_status']['tested'] ?? false,
                                                                'completed' => $tasks['test_status']['completed'] ?? false
                                                            ];
                                                        } else {
                                                            $testedVal = $tasks['tested'] ?? false;
                                                            $testStatus = [
                                                                'tested' => ($testedVal === true || $testedVal === 'yes' || $testedVal === 'tested' || $testedVal === 'Completed'),
                                                                'completed' => ($testedVal === true || $testedVal === 'yes' || $testedVal === 'tested' || $testedVal === 'Completed')
                                                            ];
                                                        }
                                                        
                                                        $issues = $tasks['issues'] ?? [];
                                                        $notes = $tasks['notes'] ?? null;
                                                    } else {
                                                        $requirementsList = $tasks;
                                                    }
                                                }

                                                if (!is_array($issues)) {
                                                    $issues = $issues ? [$issues] : [];
                                                }

                                                $logPayload = [
                                                    'employee_name' => $employeeName,
                                                    'employee_code' => $employeeCode,
                                                    'passport_photo_url' => resolveEmployeePassportPhoto($attendance->employee ?? $attendance),
                                                    'employee_initial' => resolveEmployeeInitials($attendance->employee ?? $attendance),
                                                    'department' => optional(optional($attendance->employee)->department)->name ?? 'Staff',
                                                    'designation' => optional(optional($attendance->employee)->designation)->name ?? 'Member',
                                                    'work_date' => $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-',
                                                    'shift_name' => optional($attendance->attendanceTime)->name ?? 'Default Shift',
                                                    'attendance_status' => $attendance->attendance_status ?? 'present',
                                                    'title' => $repTitle,
                                                    'description' => $repDesc,
                                                    'status' => $repStatus,
                                                    'work_mode' => strtoupper($attendance->work_mode ?? 'WFO'),
                                                    'submitted_time' => $firstLog->created_at ? $firstLog->created_at->format('h:i A') : '-',
                                                    'requirements' => $requirementsList,
                                                    'test_status' => $testStatus,
                                                    'issues' => $issues,
                                                    'notes' => $notes,
                                                ];
                                            @endphp
                                            <button type="button"
                                                class="dropdown-item"
                                                data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
                                                onclick="parseAndOpenWorkReport(this)">
                                                <i class="fas fa-clipboard-list text-primary"></i>
                                                View Work Report
                                            </button>
                                        @else
                                            <button type="button" class="dropdown-item disabled text-muted" disabled style="cursor: not-allowed; opacity: 0.6;">
                                                <i class="fas fa-clipboard-list text-muted"></i>
                                                No Work Report
                                            </button>
                                        @endif

                                        @if(!$isMyAttendance)
                                            @if($isBlocked)
                                            <button type="button"
                                                class="dropdown-item"
                                                data-toggle="modal"
                                                data-target="#unlockModal{{ $attendance->id }}">
                                                <i class="fas fa-unlock text-success"></i>
                                                Unlock
                                            </button>
                                            @endif

                                            @if(($canManageAttendance ?? false) || (auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin')))
                                            <button type="button"
                                                class="dropdown-item"
                                                data-toggle="modal"
                                                data-target="#editModal{{ $attendance->id }}">
                                                <i class="fas fa-edit text-primary"></i>
                                                Edit
                                            </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($attendances as $attendance)
            @if(!$isMyAttendance)
                @if(($canManageAttendance ?? false) || (auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin')))
                    @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
                @endif

                @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
            @endif
        @endforeach

    </div>
</div>

<!-- Shared Premium Modal -->
@include('hrms.attendance.partials.work-report-modal')
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
        const form = document.getElementById('dailyAttendanceFilterForm');
        const filters = document.querySelectorAll('.auto-filter');
        const searchInput = document.querySelector('.auto-filter-input');
        let typingTimer = null;

        function submitFilterForm() {
            if (form) {
                form.submit();
            }
        }

        filters.forEach(function(filter) {
            filter.addEventListener('change', submitFilterForm);
        });

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(submitFilterForm, 500);
            });

            searchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    submitFilterForm();
                }
            });
        }

        if ($.fn.DataTable.isDataTable('#dailyAttendanceDataTable')) {
            $('#dailyAttendanceDataTable').DataTable().destroy();
        }

        $('#dailyAttendanceDataTable').DataTable({
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
            dom: '<"leave-dt-toolbar"<"leave-dt-left"l><"leave-dt-right"B>>rt<"leave-table-footer"ip>',
            buttons: [{
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'leave-export-btn',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'leave-export-btn',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'leave-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A3',
                    title: 'Orbosis HRMS Attendance Records',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'leave-export-btn',
                    title: 'Orbosis HRMS Attendance Records',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ],
            language: {
                lengthMenu: 'Show _MENU_ entries',
                emptyTable: 'No attendance records found.',
                info: 'Showing _START_ to _END_ of _TOTAL_ attendance records',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        setTimeout(function() {
            $('#dailyAttendanceDataTable').DataTable().columns.adjust();
        }, 250);
    });
</script>
@endsection