@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Monthly Attendance Report')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg) !important;
        overflow-x: hidden !important;
    }

    .att-page {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 80px);
        padding: 24px;
        background: var(--orb-bg);
        overflow-x: hidden;
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* HERO */

    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 26px 28px;
        color: #fff;
        box-shadow: 0 20px 45px rgba(75, 0, 232, .22);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin: 0 0 18px;
    }

    .orb-hero::after {
        content: '';
        position: absolute;
        width: 230px;
        height: 230px;
        border-radius: 50%;
        right: -95px;
        bottom: -115px;
        background: rgba(255, 255, 255, .10);
    }

    .orb-hero-content,
    .orb-hero-actions {
        position: relative;
        z-index: 2;
    }

    .orb-hero-content {
        min-width: 0;
    }

    .orb-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .15);
        color: rgba(255, 255, 255, .94);
        font-size: 11px;
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .orb-hero h1 {
        font-size: 28px;
        font-weight: 950;
        margin: 0;
        letter-spacing: -.03em;
        color: #fff;
    }

    .orb-hero p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .84);
        font-size: 13px;
        line-height: 1.6;
        max-width: 780px;
    }

    /* BUTTONS */

    .orb-btn {
        border-radius: 14px;
        min-height: 40px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none !important;
        border: 1px solid transparent;
        line-height: 1;
        white-space: nowrap;
    }

    .orb-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .orb-btn-primary {
        background: #fff;
        color: var(--orb-primary);
        border-color: rgba(255, 255, 255, .65);
        box-shadow: 0 12px 24px rgba(16, 24, 40, .12);
    }

    .orb-btn-primary:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .orb-btn-light {
        background: #fff;
        color: var(--orb-text);
        border-color: var(--orb-border);
    }

    .orb-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .orb-btn-reset {
        min-height: 34px;
        height: 34px;
        padding: 0 12px;
        border-radius: 11px;
        font-size: 12px;
        box-shadow: none;
    }

    /* SUMMARY GRID */

    .orb-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .orb-summary-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        padding: 16px 18px;
        box-shadow: var(--orb-shadow);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.2s ease;
    }

    .orb-summary-card:hover {
        transform: translateY(-2px);
    }

    .orb-summary-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .card-purple .orb-summary-icon {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }
    .card-success .orb-summary-icon {
        background: #ECFDF3;
        color: #027A48;
    }
    .card-danger .orb-summary-icon {
        background: #FEF3F2;
        color: #B42318;
    }
    .card-warning .orb-summary-icon {
        background: #FFFAEB;
        color: #B54708;
    }
    .card-info .orb-summary-icon {
        background: #F0F9FF;
        color: #026AA2;
    }

    .orb-summary-label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--orb-muted);
        font-weight: 900;
        margin-bottom: 4px;
        letter-spacing: .04em;
    }

    .orb-summary-value {
        font-size: 26px;
        font-weight: 950;
        color: var(--orb-text);
        line-height: 1.1;
    }

    /* TABLE CARD */

    .orb-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        margin-bottom: 18px;
        overflow: hidden;
    }

    .orb-table-card .orb-card-body {
        padding: 0;
        overflow: hidden;
    }

    .orb-table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #EEF2F6;
        background: #fff;
    }

    .orb-table-head-left {
        min-width: 0;
    }

    .orb-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, .10);
    }

    .orb-table-head-right {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 0 0 auto;
    }

    .orb-table-title {
        margin: 0;
        font-size: 16px;
        font-weight: 950;
        color: var(--orb-text);
        letter-spacing: -.02em;
    }

    .orb-table-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 600;
    }

    .orb-table-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .10);
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    /* ATTACHED FILTER */

    .orb-filter {
        margin: 0;
        border-bottom: 1px solid #EEF2F6;
        background: #FCFCFD;
        padding: 14px 20px;
    }

    .orb-filter-form {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        align-items: end;
        margin: 0;
        width: 100%;
    }

    .orb-filter-item {
        min-width: 0;
        margin: 0 !important;
    }

    .orb-filter label {
        font-size: 10px;
        font-weight: 950;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 6px;
        display: block;
    }

    .orb-filter .form-control,
    .orb-filter .custom-select {
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        height: 38px;
        min-height: 38px;
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-text);
        box-shadow: none !important;
        padding: 0 11px;
        background-color: #fff;
    }

    .orb-filter .form-control:focus {
        border-color: rgba(75, 0, 232, .30);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    /* DATATABLE TOOLBAR */

    .orb-table-tools {
        padding: 10px 20px;
        border-bottom: 1px solid #EEF2F6;
        background: #fff;
        min-height: 54px;
        display: flex;
        align-items: center;
    }

    .orb-table-tools:empty {
        display: none;
    }

    .dataTables_wrapper {
        width: 100%;
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }

    .orb-dt-toolbar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        flex-wrap: nowrap !important;
        margin: 0 !important;
        width: 100%;
    }

    .orb-dt-toolbar .dt-left,
    .orb-dt-toolbar .dt-right {
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        max-width: none !important;
        flex: 0 0 auto !important;
        padding: 0 !important;
    }

    .orb-dt-toolbar .dt-right {
        margin-left: auto !important;
        justify-content: flex-end !important;
    }

    .dataTables_wrapper .dataTables_length {
        display: flex;
        align-items: center;
        margin: 0 !important;
        font-size: 12px;
        font-weight: 800;
        color: var(--orb-muted);
        white-space: nowrap;
    }

    .dataTables_wrapper .dataTables_length label {
        margin: 0 !important;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .dataTables_wrapper .dataTables_length select {
        width: auto !important;
        min-width: 68px;
        height: 32px;
        border: 1px solid var(--orb-border);
        border-radius: 9px;
        padding: 0 8px;
        font-size: 12px;
        font-weight: 800;
        background: #fff;
        color: var(--orb-text);
        margin: 0 2px !important;
    }

    .dataTables_wrapper .dt-buttons {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
        margin: 0 !important;
        width: auto !important;
        flex: 0 0 auto !important;
    }

    .dataTables_wrapper .dt-buttons .btn,
    .dataTables_wrapper .dt-buttons .dt-button {
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
        height: 32px !important;
        min-height: 32px !important;
        padding: 0 10px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        line-height: 30px !important;
        box-shadow: none !important;
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        white-space: nowrap !important;
    }

    .dataTables_wrapper .dt-buttons .btn:hover,
    .dataTables_wrapper .dt-buttons .dt-button:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, .22) !important;
    }

    /* SCROLLABLE TABLE */

    .orb-table-wrap {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        background: #fff;
    }

    .orb-table {
        min-width: 1200px;
        margin: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .orb-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #F8FAFC;
        border-top: 0 !important;
        border-bottom: 1px solid var(--orb-border) !important;
        color: #475467;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        padding: 13px 12px;
    }

    .orb-table tbody td {
        vertical-align: middle !important;
        white-space: nowrap;
        padding: 13px 12px !important;
        border-color: #F2F4F7 !important;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
        border-bottom: 1px solid #F2F4F7 !important;
    }

    .orb-table tbody tr {
        transition: all .15s ease;
    }

    .orb-table tbody tr:hover td {
        background: #FAF8FF !important;
    }

    /* EMPLOYEE DETAILS */

    .orb-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-weight: 850;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, 0.08);
        position: relative !important;
        overflow: hidden !important;
    }

    .att-avatar-img {
        width: 38px !important;
        height: 38px !important;
        border-radius: 12px !important;
        object-fit: cover !important;
        display: block !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
        flex-shrink: 0 !important;
    }

    .att-emp-name {
        font-size: 13.5px;
        font-weight: 900;
        color: var(--orb-text);
    }

    .att-emp-sub {
        font-size: 11.5px;
        color: var(--orb-muted);
        margin-top: 2px;
        font-weight: 600;
    }

    .att-hours {
        font-weight: 900;
        color: var(--orb-primary);
    }

    /* BADGES */

    .orb-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 900;
        min-width: 34px;
        height: 24px;
    }

    .orb-badge-success {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .orb-badge-warning {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .orb-badge-danger {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .orb-badge-muted {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .orb-badge-primary {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    /* DATATABLE SCROLL SCROLLBAR */

    .dataTables_scrollBody::-webkit-scrollbar {
        height: 8px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 20px;
    }

    /* FOOTER & PAGINATION */

    .orb-table-footer,
    .dataTables_wrapper .row:last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        margin: 0 !important;
        padding: 12px 18px 14px !important;
        border-top: 1px solid #EEF2F6;
        background: #fff;
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 0 !important;
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-muted);
        white-space: nowrap;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding: 0 !important;
        margin: 0 !important;
        white-space: nowrap;
        overflow-x: visible !important;
    }

    .dataTables_wrapper .paginate_button {
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        margin: 0 2px !important;
        padding: 5px 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
    }

    /* RESPONSIVE LAYOUTS */

    @media(max-width: 1199px) {
        .att-page {
            padding: 18px;
        }

        .orb-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .orb-filter-form {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width: 991px) {
        .orb-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .orb-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .orb-hero-actions,
        .orb-hero-actions .orb-btn {
            width: 100%;
        }
    }

    @media(max-width: 768px) {
        .att-page {
            padding: 12px;
        }

        .orb-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .orb-hero h1 {
            font-size: 22px;
        }

        .orb-summary-grid {
            grid-template-columns: 1fr;
        }

        .orb-table-header {
            flex-direction: column;
            align-items: stretch;
            padding: 14px;
        }

        .orb-table-head-right {
            justify-content: space-between;
            width: 100%;
            flex-wrap: wrap;
            gap: 8px;
        }

        .orb-filter {
            padding: 12px 14px;
        }

        .orb-filter-form {
            grid-template-columns: 1fr;
        }

        .orb-table-tools {
            padding: 10px 14px;
        }

        .orb-dt-toolbar {
            flex-wrap: wrap !important;
            align-items: flex-start !important;
        }

        .orb-dt-toolbar .dt-left,
        .orb-dt-toolbar .dt-right {
            width: 100% !important;
        }

        .dataTables_wrapper .dt-buttons {
            justify-content: flex-start !important;
            flex-wrap: wrap !important;
            margin-top: 6px !important;
        }

        .orb-table-footer,
        .dataTables_wrapper .row:last-child {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 12px 14px 14px !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            width: 100%;
            overflow-x: auto !important;
            padding-bottom: 3px !important;
        }
    }
</style>
@endsection

@section('_content')
<div class="att-page">
    <div class="att-container">

        <!-- Hero Header -->
        <div class="orb-hero">
            <div class="orb-hero-content">
                <div class="orb-hero-kicker">
                    <i class="fas fa-calendar-check"></i>
                    HRMS &bull; ATTENDANCE REPORT
                </div>
                <h1>Monthly Attendance Report</h1>
                <p>
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }} employee-wise attendance summary
                </p>
            </div>

            <div class="orb-hero-actions">
                <a href="{{ route('attendances.export-pdf', request()->query() + ['month' => $month, 'year' => $year]) }}"
                    class="orb-btn orb-btn-primary">
                    <i class="fas fa-file-pdf text-danger"></i>
                    Export Monthly Report
                </a>
            </div>
        </div>

        <!-- Dynamic Summary KPI Cards -->
        <div class="orb-summary-grid">
            @php
            $cards = [
                ['label' => 'Total Employees', 'value' => count($employeeRows), 'icon' => 'fa-users', 'color' => 'purple'],
                ['label' => 'Present Days', 'value' => $summary['present'] ?? 0, 'icon' => 'fa-calendar-check', 'color' => 'success'],
                ['label' => 'Absent Days', 'value' => $summary['absent'] ?? 0, 'icon' => 'fa-calendar-times', 'color' => 'danger'],
                ['label' => 'Half Days', 'value' => $summary['half_day'] ?? 0, 'icon' => 'fa-business-time', 'color' => 'warning'],
                ['label' => 'Leaves / LWP', 'value' => ($summary['leave'] ?? 0) + ($summary['punch_blocked'] ?? 0), 'icon' => 'fa-plane-departure', 'color' => 'info'],
            ];
            @endphp

            @foreach($cards as $card)
            <div class="orb-summary-card card-{{ $card['color'] }}">
                <div class="orb-summary-icon">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div>
                    <div class="orb-summary-label">
                        {{ $card['label'] }}
                    </div>
                    <div class="orb-summary-value">
                        {{ $card['value'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Table Parent Card -->
        <div class="orb-card orb-table-card">
            <div class="orb-card-body">
                
                <!-- Table Header Attached -->
                <div class="orb-table-header">
                    <div class="orb-table-head-left d-flex align-items-center" style="gap: 14px;">
                        <div class="orb-icon-box">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="orb-table-title">Employee-wise Attendance Summary</h3>
                            <p class="orb-table-subtitle">Overview of monthly dynamic presence metrics, late count, and total hours.</p>
                        </div>
                    </div>

                    <div class="orb-table-head-right">
                        <span class="orb-table-count">
                            <i class="fas fa-calendar-alt"></i>
                            Month: {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                        </span>
                        <span class="orb-table-count">
                            <i class="fas fa-database"></i>
                            Total: {{ count($employeeRows) }}
                        </span>

                        @if(request('month') || request('year') || request('employee_id') || request('department_id'))
                        <a href="{{ route('attendances.monthly-report') }}"
                            class="orb-btn orb-btn-light orb-btn-reset">
                            <i class="fas fa-undo"></i>
                            Reset
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Attached Filters -->
                <div class="orb-filter">
                    <form method="GET" action="{{ route('attendances.monthly-report') }}" id="monthlyAttendanceFilterForm" class="orb-filter-form">
                        
                        <div class="orb-filter-item">
                            <label>Month</label>
                            <select name="month" class="form-control auto-filter">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null,$m,1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="orb-filter-item">
                            <label>Year</label>
                            <input type="number" name="year" class="form-control auto-filter" value="{{ $year }}">
                        </div>

                        <div class="orb-filter-item">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control auto-filter">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    @php
                                    $employeeId = optional($emp->employee)->id;
                                    @endphp
                                    @if($employeeId)
                                        <option value="{{ $employeeId }}" {{ request('employee_id') == $employeeId ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="orb-filter-item">
                            <label>Department</label>
                            <select name="department_id" class="form-control auto-filter">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </form>
                </div>

                <!-- DataTable Dynamic Toolbar container -->
                <div class="orb-table-tools"></div>

                <!-- Scrollable Table Body -->
                <div class="orb-table-wrap">
                    <table class="orb-table table table-hover" id="monthlyAttendanceDataTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Emp Code</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Half Day</th>
                                <th class="text-center">Leave</th>
                                <th class="text-center">Week Off</th>
                                <th class="text-center">Late</th>
                                <th class="text-center">Early Out</th>
                                <th class="text-center">Total Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeRows as $row)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center" style="gap: 10px;">
                                        @php
                                            $empModel = \App\Models\HRMS\Employee\EmployeeM::find($row['employee_id'] ?? null);
                                            $passportPhotoUrl = resolveEmployeePassportPhoto($empModel);
                                            $employeeName = $row['employee_name'] ?? 'Employee';
                                            $employeeInitial = resolveEmployeeInitials($empModel);
                                        @endphp
                                        <span class="hrms-emp-avatar hrms-emp-avatar-sm">
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
                                            <div class="att-emp-name">{{ $row['employee_name'] }}</div>
                                            <div class="att-emp-sub">{{ $row['department_name'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $row['employee_code'] }}
                                </td>
                                <td class="text-center">
                                    <span class="orb-badge orb-badge-success">{{ $row['present'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="orb-badge orb-badge-danger">{{ $row['absent'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="orb-badge orb-badge-warning">{{ $row['half_day'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="orb-badge orb-badge-primary">{{ $row['leave'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="orb-badge orb-badge-muted">{{ $row['week_off'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="orb-badge orb-badge-warning">{{ $row['late'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="orb-badge orb-badge-danger">{{ $row['early_out'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="att-hours">
                                        {{ number_format($row['total_hours'], 1) }}h
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td>—</td>
                                <td>—</td>
                                <td class="text-center"><span class="orb-badge orb-badge-muted">0</span></td>
                                <td class="text-center"><span class="orb-badge orb-badge-muted">0</span></td>
                                <td class="text-center"><span class="orb-badge orb-badge-muted">0</span></td>
                                <td class="text-center"><span class="orb-badge orb-badge-muted">0</span></td>
                                <td class="text-center"><span class="orb-badge orb-badge-muted">0</span></td>
                                <td class="text-center"><span class="orb-badge orb-badge-muted">0</span></td>
                                <td class="text-center"><span class="orb-badge orb-badge-muted">0</span></td>
                                <td class="text-center"><span class="att-hours">0h</span></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('monthlyAttendanceFilterForm');

        document.querySelectorAll('.auto-filter').forEach(function(item) {
            item.addEventListener('change', function() {
                if (form) {
                    form.submit();
                }
            });
        });

        if (window.jQuery && $.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#monthlyAttendanceDataTable')) {
                $('#monthlyAttendanceDataTable').DataTable().destroy();
            }

            var $table = $('#monthlyAttendanceDataTable');
            var dataTable = $table.DataTable({
                destroy: true,
                paging: true,
                searching: false,
                info: true,
                lengthChange: true,
                responsive: false,
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                ordering: true,
                order: [],
                scrollX: true,
                scrollCollapse: true,

                dom: "<'orb-dt-toolbar'<'dt-left'l><'dt-right'B>>" +
                    "rt" +
                    "<'orb-table-footer'<'dt-info'i><'dt-pagination'p>>",

                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                        className: 'btn btn-sm'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                        className: 'btn btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                        className: 'btn btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: '{{ branding_name() }} Monthly Attendance Report'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Print',
                        className: 'btn btn-sm',
                        title: '{{ branding_name() }} Monthly Attendance Report'
                    }
                ],

                language: {
                    emptyTable: 'No monthly attendance records found.',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    }
                },

                initComplete: function() {
                    var $wrapper = $table.closest('.dataTables_wrapper');
                    var $toolbar = $wrapper.find('.orb-dt-toolbar').first();
                    var $toolsTarget = $table.closest('.orb-card').find('.orb-table-tools').first();

                    if ($toolsTarget.length && $toolbar.length) {
                        $toolsTarget.empty().append($toolbar);
                    }
                }
            });

            setTimeout(function() {
                dataTable.columns.adjust();
            }, 250);
        }
    });
</script>
@endsection
