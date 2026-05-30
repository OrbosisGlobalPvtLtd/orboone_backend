@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Attendance Dashboard')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
@php
$attendanceRows = $attendances instanceof \Illuminate\Pagination\AbstractPaginator
? collect($attendances->items())
: collect($attendances ?? []);

$blockedRows = collect($blockedAttendances ?? []);

$renderedModalIds = [];

$kpis = [
['label' => 'Present Today', 'value' => $stats['present_today'] ?? 0, 'icon' => 'fa-check-circle', 'tone' => 'success'],
['label' => 'Absent Today', 'value' => $stats['absent_today'] ?? 0, 'icon' => 'fa-user-times', 'tone' => 'danger'],
['label' => 'Late Employees', 'value' => $stats['late_employees'] ?? $stats['late_today'] ?? 0, 'icon' => 'fa-user-clock', 'tone' => 'warning'],
['label' => 'Early Logout', 'value' => $stats['early_logout'] ?? $stats['early_out_today'] ?? 0, 'icon' => 'fa-running', 'tone' => 'orange'],
['label' => 'Pending Unlock', 'value' => $stats['total_blocked'] ?? $stats['punch_blocked'] ?? 0, 'icon' => 'fa-user-lock', 'tone' => 'purple'],
['label' => 'Pending Punch Out', 'value' => $stats['pending_punch_out'] ?? 0, 'icon' => 'fa-clock', 'tone' => 'info'],
['label' => 'Half Day', 'value' => $stats['half_day'] ?? $stats['half_day_today'] ?? 0, 'icon' => 'fa-adjust', 'tone' => 'amber'],
['label' => 'LWP', 'value' => $stats['lwp'] ?? $stats['lwp_today'] ?? 0, 'icon' => 'fa-calendar-minus', 'tone' => 'danger'],
['label' => 'Punch Blocked', 'value' => $stats['punch_blocked'] ?? $stats['punch_blocked_today'] ?? 0, 'icon' => 'fa-user-lock', 'tone' => 'blocked'],
['label' => 'Missed Punches', 'value' => $stats['missed_punches'] ?? $stats['missed_punch_today'] ?? 0, 'icon' => 'fa-exclamation-circle', 'tone' => 'warning'],
['label' => 'Currently Working', 'value' => $stats['currently_working'] ?? 0, 'icon' => 'fa-laptop-house', 'tone' => 'blue'],
['label' => 'Completed Shift', 'value' => $stats['completed_shift'] ?? 0, 'icon' => 'fa-clipboard-check', 'tone' => 'success'],
['label' => 'WFO Today', 'value' => $stats['wfo_today'] ?? 0, 'icon' => 'fa-building', 'tone' => 'indigo'],
['label' => 'WFH Today', 'value' => $stats['wfh_today'] ?? 0, 'icon' => 'fa-home', 'tone' => 'teal'],
];
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
        padding: 18px 12px 35px;
        background: var(--orb-bg);
    }

    .att-container {
        max-width: 1500px;
        margin: 0 auto;
    }

    .att-header,
    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
    }

    .att-header {
        border-radius: 26px;
        padding: 20px 22px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        background: radial-gradient(circle at top right, rgba(75, 0, 232, .12), transparent 28%), linear-gradient(135deg, #fff, #F8F5FF);
    }

    .att-title {
        font-size: 25px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
    }

    .att-subtitle {
        font-size: 13px;
        color: var(--orb-muted);
        font-weight: 650;
        margin: 5px 0 0;
    }

    .att-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .att-btn {
        border: 0;
        border-radius: 13px;
        padding: 9px 14px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .att-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    .att-btn-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff !important;
    }

    .att-kpi-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .att-kpi {
        min-height: 88px;
        padding: 12px;
        border-radius: 18px;
        border: 1px solid var(--orb-border);
        background: #fff;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045);
        position: relative;
        overflow: hidden;
        transition: .18s ease;
    }

    .att-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(16, 24, 40, .08);
    }

    .att-kpi:after {
        content: "";
        position: absolute;
        right: -32px;
        top: -34px;
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: var(--tone-soft);
    }

    .att-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .att-kpi-icon {
        width: 34px;
        height: 34px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tone-soft);
        color: var(--tone);
        font-size: 14px;
    }

    .att-kpi-value {
        font-size: 25px;
        line-height: 1;
        font-weight: 950;
        color: var(--orb-text);
    }

    .att-kpi-label {
        margin-top: 10px;
        font-size: 10px;
        color: var(--orb-muted);
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        position: relative;
        z-index: 1;
    }

    .att-kpi-line {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 9px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--tone), transparent);
    }

    .tone-success {
        --tone: #12B76A;
        --tone-soft: rgba(18, 183, 106, .12);
    }

    .tone-danger {
        --tone: #F04438;
        --tone-soft: rgba(240, 68, 56, .12);
    }

    .tone-warning {
        --tone: #F79009;
        --tone-soft: rgba(247, 144, 9, .14);
    }

    .tone-orange {
        --tone: #EA580C;
        --tone-soft: rgba(234, 88, 12, .13);
    }

    .tone-amber {
        --tone: #D97706;
        --tone-soft: rgba(217, 119, 6, .13);
    }

    .tone-blocked {
        --tone: #B42318;
        --tone-soft: rgba(180, 35, 24, .13);
    }

    .tone-purple {
        --tone: #7A5AF8;
        --tone-soft: rgba(122, 90, 248, .13);
    }

    .tone-blue {
        --tone: #2563EB;
        --tone-soft: rgba(37, 99, 235, .12);
    }

    .tone-info {
        --tone: #0EA5E9;
        --tone-soft: rgba(14, 165, 233, .13);
    }

    .tone-indigo {
        --tone: #4F46E5;
        --tone-soft: rgba(79, 70, 229, .13);
    }

    .tone-teal {
        --tone: #0F766E;
        --tone-soft: rgba(15, 118, 110, .13);
    }

    .att-card {
        border-radius: 24px;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .att-section-head {
        padding: 16px 18px;
        background: linear-gradient(180deg, #fff, #FAFBFF);
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .att-section-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 16px;
        font-weight: 950;
        display: flex;
        gap: 9px;
        align-items: center;
    }

    .att-section-title i {
        color: var(--orb-primary);
    }

    .att-section-subtitle {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 650;
        margin-top: 3px;
    }

    .att-search {
        width: 230px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        padding: 7px 11px;
        font-size: 13px;
        outline: none;
    }

    .att-search:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .10);
    }

    .att-table-wrap {
        padding: 14px 16px 16px;
    }

    .att-table-responsive {
        width: 100%;
        overflow-x: visible !important;
        -webkit-overflow-scrolling: auto;
    }

    /* Single horizontal scrollbar: DataTables scroll body only */
    .dataTables_wrapper .dataTables_scroll {
        width: 100%;
    }

    .dataTables_wrapper .dataTables_scrollHead {
        overflow: hidden !important;
    }

    .dataTables_wrapper .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        border-bottom: 0 !important;
    }

    .dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px;
    }

    .dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #D0D5DD;
        border-radius: 20px;
    }

    .dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #F2F4F7;
        border-radius: 20px;
    }

    .att-table {
        width: 100% !important;
        min-width: 1320px;
        border-collapse: separate !important;
        border-spacing: 0;
        table-layout: fixed;
        margin-bottom: 0 !important;
    }

    .att-table thead th {
        background: #F8FAFC;
        color: #475467;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 12px !important;
        border-top: 1px solid #EAECF0 !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .att-table td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 12px !important;
        vertical-align: middle !important;
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF;
    }

    .att-table th:nth-child(1),
    .att-table td:nth-child(1) {
        width: 225px;
    }

    .att-table th:nth-child(2),
    .att-table td:nth-child(2) {
        width: 120px;
    }

    .att-table th:nth-child(3),
    .att-table td:nth-child(3) {
        width: 140px;
    }

    .att-table th:nth-child(4),
    .att-table td:nth-child(4) {
        width: 90px;
    }

    .att-table th:nth-child(5),
    .att-table td:nth-child(5) {
        width: 120px;
    }

    .att-table th:nth-child(6),
    .att-table td:nth-child(6) {
        width: 95px;
    }

    .att-table th:nth-child(7),
    .att-table td:nth-child(7) {
        width: 95px;
    }

    .att-table th:nth-child(8),
    .att-table td:nth-child(8) {
        width: 112px;
    }

    .att-table th:nth-child(9),
    .att-table td:nth-child(9) {
        width: 100px;
    }

    .att-table th:nth-child(10),
    .att-table td:nth-child(10) {
        width: 100px;
    }

    .att-table th:nth-child(11),
    .att-table td:nth-child(11) {
        width: 125px;
    }

    .att-table th:nth-child(12),
    .att-table td:nth-child(12) {
        width: 145px;
    }

    .att-table th:nth-child(13),
    .att-table td:nth-child(13) {
        width: 82px;
    }

    .att-block-table {
        min-width: 1120px;
    }

    .att-block-table th:nth-child(1),
    .att-block-table td:nth-child(1) {
        width: 230px;
    }

    .att-block-table th:nth-child(2),
    .att-block-table td:nth-child(2) {
        width: 150px;
    }

    .att-block-table th:nth-child(3),
    .att-block-table td:nth-child(3) {
        width: 120px;
    }

    .att-block-table th:nth-child(4),
    .att-block-table td:nth-child(4) {
        width: 135px;
    }

    .att-block-table th:nth-child(5),
    .att-block-table td:nth-child(5) {
        width: 260px;
    }

    .att-block-table th:nth-child(6),
    .att-block-table td:nth-child(6) {
        width: 135px;
    }

    .att-block-table th:nth-child(7),
    .att-block-table td:nth-child(7) {
        width: 135px;
    }

    .att-block-table th:nth-child(8),
    .att-block-table td:nth-child(8) {
        width: 95px;
    }

    .att-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .att-avatar {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        background: linear-gradient(135deg, var(--orb-soft), #fff);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .10);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 950;
        flex: 0 0 auto;
    }

    .att-emp-name {
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 900;
        max-width: 145px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .att-emp-code,
    .att-small {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 750;
    }

    .att-time {
        font-size: 12px;
        font-weight: 850;
        color: #344054;
        white-space: nowrap;
    }

    .att-strong {
        font-weight: 900;
        color: var(--orb-text);
    }

    .att-badge,
    .mode-badge,
    .flag-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        white-space: nowrap;
        font-weight: 950;
        text-transform: uppercase;
    }

    .att-badge {
        padding: 6px 9px;
        font-size: 10px;
    }

    .mode-badge {
        padding: 6px 10px;
        font-size: 10px;
    }

    .flag-badge {
        padding: 4px 8px;
        font-size: 9px;
        margin: 2px 3px 2px 0;
    }

    .badge-present {
        background: #DCFCE7;
        color: #166534;
    }

    .badge-absent {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-half_day {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-lwp {
        background: #FEE2E2;
        color: #B42318;
    }

    .badge-leave {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .badge-week_off {
        background: #F1F5F9;
        color: #475569;
    }

    .badge-holiday {
        background: #EDE9FE;
        color: #5B21B6;
    }

    .badge-punch_blocked {
        background: #FFE4E6;
        color: #BE123C;
    }

    .badge-default {
        background: #F1F5F9;
        color: #475569;
    }

    .mode-wfo {
        background: #EEF2FF;
        color: #3730A3;
    }

    .mode-wfh {
        background: #ECFEFF;
        color: #155E75;
    }

    .mode-default {
        background: #F1F5F9;
        color: #475569;
    }

    .flag-late {
        background: #FFF7ED;
        color: #C2410C;
    }

    .flag-early {
        background: #FEF2F2;
        color: #B42318;
    }

    .flag-block {
        background: #FFE4E6;
        color: #BE123C;
    }

    .flag-clear {
        background: #F1F5F9;
        color: #475569;
    }

    .flag-missed {
        background: #FEF3C7;
        color: #92400E;
    }

    .flag-unlock {
        background: #EDE9FE;
        color: #5B21B6;
    }

    .att-empty {
        padding: 36px 16px !important;
        text-align: center;
        color: var(--orb-muted);
        font-weight: 800;
    }

    .att-action-wrap {
        display: flex;
        justify-content: flex-end;
    }

    .action-dot {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #475467;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .action-dot:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .dropdown-menu.att-action-menu {
        border: 1px solid var(--orb-border);
        border-radius: 15px;
        box-shadow: 0 18px 45px rgba(16, 24, 40, .14);
        padding: 7px;
        min-width: 178px;
    }

    .att-action-menu .dropdown-item {
        border-radius: 11px;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 800;
        color: #344054;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-action-menu .dropdown-item:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .att-block-card {
        border-color: #FED7AA;
        background: radial-gradient(circle at top right, rgba(249, 115, 22, .12), transparent 24%), #fff;
    }

    .att-block-card .att-section-head {
        background: linear-gradient(135deg, #FFF7ED, #fff);
    }

    .att-block-summary {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .att-mini-stat {
        padding: 7px 10px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #FED7AA;
        font-size: 11px;
        font-weight: 900;
        color: #9A3412;
    }

    .dataTables_wrapper>.row:first-child {
        background: #fff;
        border-bottom: 1px solid var(--orb-border);
        padding: 12px 16px;
        margin: 0 -16px 12px !important;
    }

    .dataTables_wrapper .dt-buttons .btn {
        border-radius: 11px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        margin-right: 6px !important;
        margin-bottom: 6px !important;
    }

    .dataTables_length select {
        border-radius: 10px !important;
        padding: 4px 22px 4px 8px !important;
    }

    .dataTables_info {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 750;
    }

    .page-link {
        border-radius: 10px !important;
        margin: 0 2px;
        border-color: var(--orb-border);
        color: var(--orb-primary);
        font-weight: 800;
    }

    @media(max-width:1280px) {
        .att-kpi-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media(max-width:992px) {

        .att-header,
        .att-section-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .att-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .att-toolbar,
        .att-search {
            width: 100%;
        }
    }

    @media(max-width:576px) {
        .att-page {
            padding: 12px 8px 25px;
        }

        .att-title {
            font-size: 22px;
        }

        .att-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .att-kpi {
            min-height: 86px;
            padding: 10px;
            border-radius: 16px;
        }

        .att-kpi-value {
            font-size: 22px;
        }

        .att-kpi-label {
            font-size: 9px;
        }
    }
</style>

<div class="att-page">
    <div class="att-container">
        <div class="orb-page-header">
            <div class="orb-page-header-content">
                <div class="orb-page-kicker">
                    <i class="fas fa-calendar-alt"></i> HRMS &bull; Attendance
                </div>
                <h1 class="orb-page-title">Attendance Dashboard</h1>
                <p class="orb-page-subtitle">Live daily overview with punch status, blocked employees, WFO/WFH, late marks and shift completion.</p>
            </div>
            <div class="orb-page-actions">
                <a href="{{ route('attendances.daily') }}" class="orb-btn-light"><i class="fas fa-list"></i> Attendance Records</a>
                <a href="{{ route('attendances.export-pdf', request()->query()) }}" class="orb-btn-light"><i class="fas fa-file-pdf text-danger"></i> Export</a>
            </div>
        </div>

        @if(session('status'))
        <div class="alert alert-success" style="border-radius:16px;font-weight:800;">{{ session('status') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger" style="border-radius:16px;font-weight:800;">{{ session('error') }}</div>
        @endif

        <div class="att-kpi-grid">
            @foreach($kpis as $kpi)
            <div class="att-kpi tone-{{ $kpi['tone'] }}">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
                    <div class="att-kpi-value">{{ $kpi['value'] }}</div>
                </div>
                <div class="att-kpi-label" title="{{ $kpi['label'] }}">{{ $kpi['label'] }}</div>
                <div class="att-kpi-line"></div>
            </div>
            @endforeach
        </div>

        <div class="orb-table-card att-block-card" style="border-color: #FED7AA;">
            <div class="orb-table-toolbar justify-content-between align-items-center">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-user-lock"></i> Punch-In Blocked Employees</h5>
                    <div class="att-section-subtitle">Employees auto-blocked after 11:15 AM because they did not punch in.</div>
                </div>
                <div class="att-block-summary">
                    <span class="att-mini-stat">Total: {{ $blockedRows->count() }}</span>
                    <span class="att-mini-stat">Pending Unlock: {{ $blockedRows->where('is_admin_unlocked', false)->count() }}</span>
                </div>
            </div>
            <div class="orb-table-wrapper att-table-wrap">
                <div class="att-table-responsive">
                    <table class="att-table att-block-table table mb-0" id="blockedAttendanceTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Date</th>
                                <th>Auto Blocked At</th>
                                <th>Block Reason</th>
                                <th>Status</th>
                                <th>Approved By</th>
                                <th class="no-export text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($blockedRows as $blocked)
                            @php
                            $blockedDate = $blocked->attendance_date ? \Carbon\Carbon::parse($blocked->attendance_date)->format('d M Y') : '-';
                            $autoBlockedAt = $blocked->auto_blocked_at ? \Carbon\Carbon::parse($blocked->auto_blocked_at)->format('d M Y h:i A') : '-';
                            $isUnlocked = (bool) ($blocked->is_admin_unlocked ?? false);
                            $blockedTypeCode = optional($blocked->attendanceType)->code ?: ($blocked->attendance_status ?: 'default');
                            $blockedStatusLabel = $blockedTypeCode === 'punch_blocked' ? 'Punch Blocked' : (optional($blocked->attendanceType)->name ?? ucwords(str_replace('_', ' ', $blockedTypeCode)));
                            @endphp
                            <tr>
                                <td>
                                    <div class="att-emp">
                                        @php
                                        $passportPhotoUrl = resolveEmployeePassportPhoto($blocked);
                                        $blockedName = optional($blocked->user)->name ?? 'Employee';
                                        $employeeInitial = resolveEmployeeInitials($blocked);
                                        @endphp
                                        <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                            @if($passportPhotoUrl)
                                            <img
                                                src="{{ $passportPhotoUrl }}"
                                                alt="{{ $blockedName }}"
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
                                        <div>
                                            <div class="att-emp-name" title="{{ optional($blocked->user)->name ?? 'N/A' }}">{{ optional($blocked->user)->name ?? 'N/A' }}</div>
                                            <div class="att-emp-code">{{ optional($blocked->employee)->employee_code ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="att-small">{{ optional(optional($blocked->employee)->department)->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="att-small">{{ optional(optional($blocked->employee)->designation)->name ?? 'N/A' }}</div>
                                </td>
                                <td><span class="att-time">{{ $blockedDate }}</span></td>
                                <td><span class="att-time">{{ $autoBlockedAt }}</span></td>
                                <td>
                                    <div class="att-small" style="max-width:240px;">{{ $blocked->block_reason ?? $blocked->auto_block_reason ?? $blocked->blocked_reason ?? 'Auto blocked after 11:15 AM because employee did not punch in.' }}</div>
                                </td>
                                <td>
                                    <span class="att-badge badge-{{ $blockedTypeCode }}">{{ $blockedStatusLabel }}</span>
                                    @if($isUnlocked)
                                    <span class="flag-badge flag-unlock mt-1">Unlocked</span>
                                    @else
                                    <span class="flag-badge flag-block mt-1">Pending Unlock</span>
                                    @endif
                                    @if($blocked->unlock_type)
                                    <div class="att-small mt-1">{{ ucwords(str_replace('_', ' ', $blocked->unlock_type)) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="att-small">{{ optional($blocked->unlockedBy)->name ?? optional($blocked->hrApprovedBy)->name ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="att-action-wrap dropdown">
                                        <button class="action-dot" type="button" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                            @if($canUnlockAttendance ?? false)
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#unlockModal{{ $blocked->id }}"><i class="fas fa-unlock text-success"></i> Unlock</button>
                                            @endif
                                            @if($canManageAttendance ?? false)
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $blocked->id }}"><i class="fas fa-edit text-primary"></i> Edit</button>
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
        </div>

        <div class="orb-table-card">
            <div class="orb-table-toolbar justify-content-between align-items-end flex-wrap gap-3">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-calendar-day"></i> Today's Attendance</h5>
                    <div class="att-section-subtitle">Daily attendance list for today only. Full history is available in Attendance Records.</div>
                </div>
                <div class="orb-filter-group align-items-center">
                    <input type="text" id="todayAttendanceSearch" class="att-search" placeholder="Search today attendance...">
                    <a href="{{ route('attendances.index') }}" class="orb-btn-light py-2 px-3 h-auto" style="min-height: 38px !important; border-radius: 12px !important;"><i class="fas fa-sync-alt"></i> Refresh</a>
                </div>
            </div>
            <div class="orb-table-wrapper att-table-wrap">
                <div class="att-table-responsive">
                    <table class="att-table table mb-0" id="attendanceDataTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <!-- <th>Employee Code</th> -->
                                <th>Department</th>
                                <th>Mode</th>
                                <th>Shift</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                                <th>Target Out</th>
                                <th>Gross</th>
                                <th>Net</th>
                                <th>Status</th>
                                <th>Flags</th>
                                <th class="no-export text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendanceRows as $attendance)
                            @php
                            $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                            $statusName = optional($attendance->attendanceType)->name ?? 'N/A';
                            $modeCode = strtolower($attendance->work_mode ?? '');
                            $modeLabel = $modeCode === 'wfh' ? 'WFH' : ($modeCode === 'wfo' ? 'WFO' : '-');
                            $modeClass = in_array($modeCode, ['wfo', 'wfh']) ? $modeCode : 'default';
                            $grossMinutes = (int) ($attendance->gross_work_minutes ?? 0);
                            $netMinutes = (int) ($attendance->total_work_minutes ?? 0);
                            $grossText = $grossMinutes > 0 ? floor($grossMinutes / 60).'h '.($grossMinutes % 60).'m' : ($attendance->gross_duration ?? '-');
                            $netText = $netMinutes > 0 ? floor($netMinutes / 60).'h '.($netMinutes % 60).'m' : ($attendance->net_duration ?? '-');
                            @endphp
                            <tr>
                                <td>
                                    <div class="att-emp">
                                        @php
                                        $passportPhotoUrl = resolveEmployeePassportPhoto($attendance);
                                        $employeeName = optional($attendance->user)->name ?? 'Employee';
                                        $employeeInitial = resolveEmployeeInitials($attendance);
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
                                        <div>
                                            <div class="att-emp-name" title="{{ optional($attendance->user)->name ?? 'N/A' }}">{{ optional($attendance->user)->name ?? 'N/A' }}</div>
                                            <div class="att-emp-code">{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <!-- <td>{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</td> -->
                                <td>
                                    <div class="att-small">{{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}</div>
                                </td>
                                <td><span class="mode-badge mode-{{ $modeClass }}">{{ $modeLabel }}</span></td>
                                <td>
                                    <div class="att-small">{{ optional($attendance->attendanceTime)->name ?? '-' }}</div>
                                </td>
                                <td><span class="att-time">{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}</span></td>
                                <td><span class="att-time">{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}</span></td>
                                <td><span class="att-time">{{ $attendance->target_punch_out_time ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A') : '-' }}</span></td>
                                <td><span class="att-small att-strong">{{ $grossText }}</span></td>
                                <td><span class="att-small att-strong">{{ $netText }}</span></td>
                                <td><span class="att-badge badge-{{ $typeCode }}">{{ $statusName }}</span></td>
                                <td>
                                    @if($attendance->is_late)
                                    <span class="flag-badge flag-late">Late {{ $attendance->late_minutes ?? 0 }}m</span>
                                    @endif
                                    @if($attendance->is_early_out)
                                    <span class="flag-badge flag-early">Early {{ $attendance->early_out_minutes ?? 0 }}m</span>
                                    @endif
                                    @if($attendance->missed_punch ?? false)
                                    <span class="flag-badge flag-missed">Missed</span>
                                    @endif
                                    @if(($attendance->is_blocked ?? false) || ($attendance->is_punch_blocked ?? false))
                                    <span class="flag-badge flag-block">Blocked</span>
                                    @endif
                                    @if(($attendance->is_admin_unlocked ?? false))
                                    <span class="flag-badge flag-unlock">Unlocked</span>
                                    @endif
                                    @if(!$attendance->is_late && !$attendance->is_early_out && !($attendance->missed_punch ?? false) && !($attendance->is_blocked ?? false) && !($attendance->is_punch_blocked ?? false))
                                    <span class="flag-badge flag-clear">Clear</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="att-action-wrap dropdown">
                                        <button class="action-dot" type="button" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                            @if(($canUnlockAttendance ?? false) && (($attendance->is_blocked ?? false) || ($attendance->is_punch_blocked ?? false) || $typeCode === 'punch_blocked'))
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#unlockModal{{ $attendance->id }}"><i class="fas fa-unlock text-success"></i> Unlock</button>
                                            @endif
                                            @if($canManageAttendance ?? false)
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $attendance->id }}"><i class="fas fa-edit text-primary"></i> Edit</button>
                                            @endif
                                            <a href="{{ route('attendances.daily', ['employee_id' => optional($attendance->employee)->id]) }}" class="dropdown-item"><i class="fas fa-eye text-info"></i> View Records</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @foreach($attendanceRows as $attendance)
        @php $renderedModalIds[] = $attendance->id; @endphp
        @if($canManageAttendance ?? false)
        @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
        @endif
        @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
        @endforeach

        @foreach($blockedRows as $attendance)
        @if(!in_array($attendance->id, $renderedModalIds))
        @if($canManageAttendance ?? false)
        @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
        @endif
        @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
        @endif
        @endforeach
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
        const todayTable = $('#attendanceDataTable').DataTable({
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
            searching: true,
            dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
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
                    pageSize: 'A4',
                    title: 'Today Attendance Report',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-light border',
                    title: 'Today Attendance Report',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ],
            language: {
                lengthMenu: 'Show _MENU_ entries',
                emptyTable: 'No attendance records found for today.',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        $('#todayAttendanceSearch').on('keyup', function() {
            todayTable.search(this.value).draw();
        });

        $('#blockedAttendanceTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All']
            ],
            ordering: true,
            responsive: false,
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,
            searching: false,
            paging: true,
            info: true,
            dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
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
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-light border',
                    title: 'Punch-In Blocked Employees',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ],
            language: {
                emptyTable: 'No punch-in blocked employees today.',
                info: 'Showing _START_ to _END_ of _TOTAL_ blocked records',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });
    });
</script>
@endsection