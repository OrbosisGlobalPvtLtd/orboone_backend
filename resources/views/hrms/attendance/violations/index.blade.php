@extends('layouts.panel', [
'accesses' => $accesses ?? [],
'active' => $active ?? 'hrms'
])

@section('page_title', $pageTitle ?? 'Attendance Violations Audit Dashboard')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
    :root {
        --orb-bg: #F8FAFC;
        --orb-border: #E2E8F0;
        --orb-text: #0F172A;
        --orb-muted: #64748B;
        --orb-soft: #F1EDFF;
        --orb-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
    }

    body {
        overflow-x: hidden !important;
    }

    .att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px 20px 48px;
    }

    .att-container {
        max-width: 1480px;
        margin: 0 auto;
    }

    .att-hero {
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #6366F1) 100%);
        border-radius: 24px !important;
        padding: 32px 36px;
        margin-bottom: 24px;
        box-shadow: 0 20px 50px rgba(75, 0, 232, 0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        color: #fff !important;
        position: relative;
        overflow: hidden;
    }

    .att-hero * {
        color: #fff !important;
    }

    .att-hero:before {
        content: "";
        position: absolute;
        right: -80px;
        top: -110px;
        width: 360px;
        height: 360px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        pointer-events: none;
    }

    .att-kicker {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        opacity: 0.95;
        margin-bottom: 8px;
        display: flex;
        gap: 8px;
        align-items: center;
        color: #fff !important;
    }

    .att-title {
        font-size: 30px;
        font-weight: 900;
        margin: 0;
        line-height: 1.2;
        color: #fff !important;
    }

    .att-subtitle {
        font-size: 14px;
        opacity: 0.95;
        margin-top: 6px;
        max-width: 800px;
        color: #fff !important;
    }

    /* Attendance Dashboard Style Summary KPI Cards */
    .audit-kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .att-kpi {
        min-height: 88px;
        padding: 12px 14px;
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
        font-size: 15px;
    }

    .att-kpi-value {
        font-size: 25px;
        line-height: 1;
        font-weight: 950;
        color: var(--orb-text);
    }

    .att-kpi-label {
        margin-top: 10px;
        font-size: 10.5px;
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
        bottom: 7px;
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

    /* Filter Panel & Card */
    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px !important;
        box-shadow: var(--orb-shadow);
        overflow: hidden !important;
    }

    .att-section-head {
        padding: 22px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #fff, #FCFDFF);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .att-section-title {
        font-size: 19px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .att-section-title i {
        color: var(--orb-primary, #4B00E8);
    }

    .att-filter-panel {
        padding: 20px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        align-items: end;
    }

    .att-filter-grid label {
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
        display: block;
    }

    .att-filter-grid .form-control,
    .att-filter-grid .custom-select {
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        font-size: 13px;
        font-weight: 600;
        padding: 0 14px;
        box-shadow: none !important;
        background: #fff;
        width: 100%;
    }

    .att-filter-grid .form-control:focus,
    .att-filter-grid .custom-select:focus {
        border-color: var(--orb-primary, #4B00E8);
    }

    /* Badges & Color Scheme */
    .orb-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .orb-badge-orange {
        background: #FFEDD5;
        color: #C2410C;
    }

    .orb-badge-blue {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .orb-badge-red {
        background: #FEE2E2;
        color: #B91C1C;
    }

    .orb-badge-yellow {
        background: #FEF3C7;
        color: #B45309;
    }

    .orb-badge-darkred {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FCA5A5;
    }

    .orb-badge-green {
        background: #D1FAE5;
        color: #047857;
    }

    .orb-badge-purple {
        background: #F3E8FF;
        color: #6D28D9;
    }

    .orb-badge-secondary {
        background: #F1F5F9;
        color: #475569;
    }

    /* Counter pill */
    .counter-pill {
        background: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #0F172A;
        font-weight: 900;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* DATATABLE TOOLBAR & EXPORT BUTTONS (Standard HRMS UI) */
    .crud-dt-toolbar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        flex-wrap: nowrap !important;
        margin: 0 !important;
        width: 100%;
        padding: 12px 24px !important;
        background: #fff !important;
        border-bottom: 1px solid var(--orb-border) !important;
        box-sizing: border-box !important;
    }

    .crud-dt-left,
    .dataTables_length {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        white-space: nowrap !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--orb-muted) !important;
    }

    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin: 0 !important;
        white-space: nowrap !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--orb-muted) !important;
    }

    .dataTables_length select {
        width: auto !important;
        min-width: 70px !important;
        height: 34px !important;
        padding: 2px 24px 2px 10px !important;
        display: inline-block !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 10px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .crud-dt-right,
    .dt-buttons {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        margin: 0 !important;
        width: auto !important;
        flex: 0 0 auto !important;
    }

    .crud-export-btn,
    .dt-button.crud-export-btn {
        height: 34px !important;
        padding: 0 14px !important;
        border-radius: 10px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: none !important;
        transition: all 0.2s !important;
    }

    .crud-export-btn:hover,
    .dt-button.crud-export-btn:hover {
        background: var(--orb-soft) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
        color: var(--orb-primary, #4B00E8) !important;
    }

    /* Footer Pagination & Info */
    .orb-table-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 14px 24px !important;
        background: #fff !important;
        border-top: 1px solid var(--orb-border) !important;
    }

    .dataTables_info {
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--orb-muted) !important;
        padding-top: 0 !important;
    }

    .dataTables_paginate {
        padding-top: 0 !important;
    }

    .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        padding: 5px 11px !important;
        margin-left: 3px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
    }

    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background: var(--orb-primary, #4B00E8) !important;
        color: #fff !important;
        border-color: var(--orb-primary, #4B00E8) !important;
    }

    /* Table layout */
    .att-table-wrap {
        padding: 0;
    }

    .att-table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 0 !important;
    }

    .att-table thead th {
        background: #F8FAFC !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        padding: 14px 18px !important;
        border-top: 1px solid var(--orb-border) !important;
        border-bottom: 2px solid var(--orb-border) !important;
    }

    .att-table tbody td {
        padding: 14px 18px !important;
        vertical-align: middle !important;
        font-size: 13px !important;
        border-bottom: 1px solid #F1F5F9 !important;
    }

    .emp-name-btn {
        background: none;
        border: none;
        padding: 0;
        color: var(--orb-text);
        font-weight: 800;
        cursor: pointer;
        text-align: left;
        text-decoration: none !important;
    }

    .emp-name-btn:hover {
        color: var(--orb-primary, #4B00E8);
    }

    /* Drawer / Offcanvas Styles */
    .audit-drawer-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1050;
        display: none;
    }

    .audit-drawer {
        position: fixed;
        top: 0;
        right: -550px;
        width: 520px;
        max-width: 90vw;
        height: 100vh;
        background: #fff;
        box-shadow: -10px 0 40px rgba(0, 0, 0, 0.15);
        z-index: 1051;
        transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }

    .audit-drawer.open {
        right: 0;
    }

    .audit-drawer-head {
        padding: 20px 24px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #6366F1));
        color: #fff !important;
    }

    .audit-drawer-head * {
        color: #fff !important;
    }

    .audit-drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    /* Timeline Components */
    .timeline-list {
        position: relative;
        padding-left: 24px;
        margin-top: 16px;
    }

    .timeline-list:before {
        content: "";
        position: absolute;
        left: 8px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: #E2E8F0;
    }

    .timeline-card {
        position: relative;
        margin-bottom: 20px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 14px;
    }

    .timeline-card:before {
        content: "";
        position: absolute;
        left: -21px;
        top: 16px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--orb-primary, #4B00E8);
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px var(--orb-primary, #4B00E8);
    }

    @media(max-width: 1200px) {
        .audit-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .att-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 640px) {
        .audit-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .att-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('_content')
<div class="att-page">
    <div class="att-container">

        <!-- Hero Header -->
        <div class="att-hero">
            <div>
                <div class="att-kicker"><i class="fas fa-shield-alt"></i> HRMS &bull; AUDIT DASHBOARD</div>
                <h3 class="att-title">{{ $pageTitle ?? 'Attendance Violations Audit Dashboard' }}</h3>
                <div class="att-subtitle">{{ $pageSubtitle ?? 'Enterprise audit overview of attendance discipline, missed punch cycles, and penalty conversions.' }}</div>
            </div>
            <!-- <div>
                <a href="{{ route('hrms.attendance.violations.export-excel', request()->query()) }}" class="btn btn-light font-weight-bold px-4 py-2" style="border-radius: 12px; color: var(--orb-primary, #4B00E8) !important; background: #fff !important;">
                    <i class="fas fa-file-export mr-2"></i> Export Audit Log (CSV)
                </a>
            </div> -->
        </div>

        <!-- Phase 1: Summary Cards (Exact Attendance Dashboard UI Style) -->
        <div class="audit-kpi-grid">
            <div class="att-kpi tone-purple">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="att-kpi-value">{{ number_format($summaryMetrics['total_today'] ?? 0) }}</div>
                </div>
                <div class="att-kpi-label">Today's Violations</div>
                <div class="att-kpi-line"></div>
            </div>

            <div class="att-kpi tone-warning">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="att-kpi-value">{{ number_format($summaryMetrics['late_today'] ?? 0) }}</div>
                </div>
                <div class="att-kpi-label">Late Login Today</div>
                <div class="att-kpi-line"></div>
            </div>

            <div class="att-kpi tone-orange">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-running"></i></div>
                    <div class="att-kpi-value">{{ number_format($summaryMetrics['early_today'] ?? 0) }}</div>
                </div>
                <div class="att-kpi-label">Early Logout Today</div>
                <div class="att-kpi-line"></div>
            </div>

            <div class="att-kpi tone-danger">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-user-times"></i></div>
                    <div class="att-kpi-value">{{ number_format($summaryMetrics['missed_today'] ?? 0) }}</div>
                </div>
                <div class="att-kpi-label">Missed Punch Today</div>
                <div class="att-kpi-line"></div>
            </div>

            <div class="att-kpi tone-amber">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-adjust"></i></div>
                    <div class="att-kpi-value">{{ number_format($summaryMetrics['half_day_applied'] ?? 0) }}</div>
                </div>
                <div class="att-kpi-label">Half Day Applied</div>
                <div class="att-kpi-line"></div>
            </div>

            <div class="att-kpi tone-blocked">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-calendar-minus"></i></div>
                    <div class="att-kpi-value">{{ number_format($summaryMetrics['lwp_applied'] ?? 0) }}</div>
                </div>
                <div class="att-kpi-label">LWP Applied</div>
                <div class="att-kpi-line"></div>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="att-card">
            <div class="att-section-head">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-list-alt"></i> Violation & Penalty Audit Logs</h5>
                    <div class="text-muted small mt-1">Server side audited violation records with active cycle counts and penalty statuses.</div>
                </div>
            </div>

            <!-- Phase 2: Expanded Server-side Filters -->
            <div class="att-filter-panel">
                <form method="GET" id="filterForm">
                    <div class="att-filter-grid">
                        <div>
                            <label>Employee</label>
                            <select name="employee_id" class="form-control js-auto-filter">
                                <option value="">All Employees</option>
                                @foreach($filters['employee_options'] ?? [] as $id => $name)
                                <option value="{{ $id }}" {{ (string) request('employee_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Department</label>
                            <select name="department_id" class="form-control js-auto-filter">
                                <option value="">All Departments</option>
                                @foreach($filters['departments'] ?? [] as $id => $name)
                                <option value="{{ $id }}" {{ (string) request('department_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Designation</label>
                            <select name="designation_id" class="form-control js-auto-filter">
                                <option value="">All Designations</option>
                                @foreach($filters['designations'] ?? [] as $id => $name)
                                <option value="{{ $id }}" {{ (string) request('designation_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Violation Type</label>
                            <select name="type" class="form-control js-auto-filter">
                                <option value="">All Types</option>
                                @foreach($filters['types'] ?? [] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Penalty Status</label>
                            <select name="penalty_status" class="form-control js-auto-filter">
                                <option value="">All Statuses</option>
                                @foreach($filters['penalty_statuses'] ?? [] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('penalty_status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Date From</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control js-auto-filter">
                        </div>

                        <div>
                            <label>Date To</label>
                            <div class="d-flex gap-2">
                                <input type="date" name="to" value="{{ request('to') }}" class="form-control js-auto-filter">
                                <a href="{{ url()->current() }}" class="btn btn-light border font-weight-bold ml-2" style="border-radius: 12px;" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="att-table-wrap">
                <div class="table-responsive">
                    <table class="att-table table table-hover" id="violationsDataTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">S.No.</th>
                                <th>Employee</th>
                                <!-- <th>Department</th> -->
                                <th>Date</th>
                                <th>Violation Type</th>
                                <th>Minutes</th>
                                <th>Active Counter</th>
                                <th>Penalty Status</th>
                                <th>Attendance Status</th>
                                <th>Created At</th>
                                <th class="text-right no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            <tr>
                                <td><strong>{{ (($rows->currentPage() - 1) * $rows->perPage()) + $loop->iteration }}</strong></td>
                                <td>
                                    <div>
                                        <button type="button" class="emp-name-btn js-open-emp-drawer" data-emp-id="{{ $row->employee_id }}">
                                            {{ $row->employee_display_name }}
                                        </button>
                                        <div class="text-muted small"><code>{{ $row->employee_code }}</code> &bull; {{ $row->designation_name ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <!-- <td>
                                    <div class="font-weight-bold text-dark">{{ $row->department_name ?? 'N/A' }}</div>
                                </td> -->
                                <td><span class="font-weight-bold">{{ $row->formatted_date }}</span></td>
                                <td>
                                    @php
                                    $typeBadgeClass = match($row->type) {
                                    'late_login', 'late_mark' => 'orb-badge-orange',
                                    'early_logout' => 'orb-badge-blue',
                                    'missed_punch' => 'orb-badge-red',
                                    default => 'orb-badge-secondary'
                                    };
                                    @endphp
                                    <span class="orb-badge {{ $typeBadgeClass }}">
                                        {{ $row->human_type }}
                                    </span>
                                </td>
                                <td><span class="font-weight-bold">{{ $row->minutes ? $row->minutes . ' mins' : '-' }}</span></td>
                                <td>
                                    <span class="counter-pill">
                                        <i class="fas fa-sync-alt text-primary mr-1" style="font-size: 10px;"></i> {{ $row->active_counter }}
                                    </span>
                                </td>
                                <td>
                                    <span class="orb-badge {{ $row->penalty_badge_class }}">
                                        {{ $row->penalty_status_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-dark">{{ $row->attendance_status_label }}</span>
                                </td>
                                <td class="small text-muted">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y h:i A') }}</td>
                                <td class="text-right no-export">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm border" type="button" data-toggle="dropdown" style="border-radius: 8px;">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow-sm" style="border-radius: 12px;">
                                            <button type="button" class="dropdown-item js-open-att-modal" data-att-id="{{ $row->attendance_id }}">
                                                <i class="fas fa-calendar-check mr-2 text-info"></i> View Attendance Audit
                                            </button>
                                            <button type="button" class="dropdown-item js-open-emp-drawer" data-emp-id="{{ $row->employee_id }}">
                                                <i class="fas fa-user-shield mr-2 text-primary"></i> View Employee Audit Drawer
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($rows, 'links'))
            <div class="mt-3 px-3 pb-3">
                {{ $rows->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Phase 6 & 8: Employee Audit Side Drawer -->
<div class="audit-drawer-backdrop" id="auditDrawerBackdrop"></div>
<div class="audit-drawer" id="auditDrawer">
    <div class="audit-drawer-head">
        <h5 class="mb-0 font-weight-bold text-white"><i class="fas fa-user-shield mr-2"></i> Employee Violation Audit</h5>
        <button type="button" class="close text-white" id="closeDrawerBtn">&times;</button>
    </div>
    <div class="audit-drawer-body" id="auditDrawerBody">
        <div class="text-center py-5 text-muted">
            <i class="fas fa-circle-notch fa-spin fa-2x mb-3 text-primary"></i>
            <div>Loading employee audit payload...</div>
        </div>
    </div>
</div>

<!-- Phase 7: Attendance Audit Modal -->
<div class="modal fade glass-modal" id="attendanceAuditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 650px;">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #6366F1));">
                <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-calendar-check mr-2"></i> Attendance Audit Detail</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4" id="attendanceAuditModalBody">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-circle-notch fa-spin fa-2x mb-2 text-primary"></i>
                    <div>Loading attendance details...</div>
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
        // Auto submit filter on select change
        document.querySelectorAll('.js-auto-filter').forEach(function(input) {
            input.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });

        // DataTable initialization
        if (window.jQuery && $.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#violationsDataTable')) {
                $('#violationsDataTable').DataTable().destroy();
            }

            $('#violationsDataTable').DataTable({
                destroy: true,
                paging: true,
                searching: false,
                info: true,
                lengthChange: true,
                responsive: false,
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                order: [],

                language: {
                    emptyTable: 'No records found.',
                    zeroRecords: 'No matching records found.',
                    lengthMenu: 'Show _MENU_ entries'
                },

                dom: '<"crud-dt-toolbar"<"crud-dt-left"l><"crud-dt-right"B>>rt<"orb-table-footer"ip>',

                buttons: [{
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv text-muted mr-1"></i> CSV',
                        className: 'crud-export-btn',
                        title: 'Attendance Violations Audit Log',
                        exportOptions: {
                            columns: ':not(.no-export)',
                            format: {
                                body: function(data, row, column, node) {
                                    return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                }
                            }
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel text-success mr-1"></i> Excel',
                        className: 'crud-export-btn',
                        title: 'Attendance Violations Audit Log',
                        sheetName: 'Violations Audit',
                        exportOptions: {
                            columns: ':not(.no-export)',
                            format: {
                                body: function(data, row, column, node) {
                                    return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                }
                            }
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf text-danger mr-1"></i> PDF',
                        className: 'crud-export-btn',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Attendance Violations Audit Log',
                        exportOptions: {
                            columns: ':not(.no-export)',
                            format: {
                                body: function(data, row, column, node) {
                                    return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                }
                            }
                        },
                        customize: function(doc) {
                            doc.defaultStyle.fontSize = 8;
                            doc.styles.tableHeader.fontSize = 9;
                            doc.styles.tableHeader.bold = true;
                            doc.styles.tableHeader.fillColor = '#4B00E8';
                            doc.styles.tableHeader.color = '#ffffff';
                            doc.styles.title.fontSize = 14;
                            doc.styles.title.bold = true;
                            doc.styles.title.alignment = 'left';
                            doc.pageMargins = [15, 15, 15, 15];
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print text-primary mr-1"></i> Print',
                        className: 'crud-export-btn',
                        title: 'Attendance Violations Audit Log',
                        exportOptions: {
                            columns: ':not(.no-export)',
                            format: {
                                body: function(data, row, column, node) {
                                    return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                }
                            }
                        },
                        customize: function(win) {
                            $(win.document.body).css('font-size', '10pt').css('padding', '20px');
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit')
                                .css('border-collapse', 'collapse');
                            $(win.document.body).find('h1').css('font-size', '16pt').css('margin-bottom', '15px');
                        }
                    }
                ]
            });
        }

        // Side Drawer Logic
        const drawer = document.getElementById('auditDrawer');
        const backdrop = document.getElementById('auditDrawerBackdrop');
        const closeBtn = document.getElementById('closeDrawerBtn');
        const drawerBody = document.getElementById('auditDrawerBody');

        function openDrawer(employeeId) {
            backdrop.style.display = 'block';
            drawer.classList.add('open');
            drawerBody.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="fas fa-circle-notch fa-spin fa-2x mb-3 text-primary"></i>
                <div>Loading employee audit payload...</div>
            </div>
        `;

            fetch(`{{ url('hrms/attendance/violations/employee-audit') }}/${employeeId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        drawerBody.innerHTML = `<div class="alert alert-danger">${data.error || 'Failed to load employee audit.'}</div>`;
                        return;
                    }
                    renderDrawerContent(data);
                })
                .catch(err => {
                    console.error(err);
                    drawerBody.innerHTML = `<div class="alert alert-danger">Unable to load employee audit profile.</div>`;
                });
        }

        function closeDrawer() {
            drawer.classList.remove('open');
            backdrop.style.display = 'none';
        }

        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (backdrop) backdrop.addEventListener('click', closeDrawer);

        document.querySelectorAll('.js-open-emp-drawer').forEach(btn => {
            btn.addEventListener('click', function() {
                const empId = this.getAttribute('data-emp-id');
                if (empId) openDrawer(empId);
            });
        });

        function renderDrawerContent(data) {
            const emp = data.employee;
            const pol = data.policy;
            const ctr = data.counters;

            let timelineHtml = '';
            if (data.timeline && data.timeline.length > 0) {
                data.timeline.forEach(item => {
                    timelineHtml += `
                    <div class="timeline-card">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-bold text-dark" style="font-size: 13px;">${item.type}</span>
                            <span class="orb-badge ${item.badge_class}">${item.penalty_status}</span>
                        </div>
                        <div class="text-muted small mb-1"><i class="fas fa-calendar-day mr-1"></i> ${item.date} ${item.minutes > 0 ? '&bull; ' + item.minutes + ' mins' : ''}</div>
                        <div class="small text-dark font-weight-bold">${item.remarks}</div>
                    </div>
                `;
                });
            } else {
                timelineHtml = '<div class="text-muted small py-2">No violation history records found.</div>';
            }

            let html = `
            <!-- Employee Profile Card -->
            <div class="d-flex align-items-center p-3 mb-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px;">
                ${emp.photo_url ? `<img src="${emp.photo_url}" style="width:48px; height:48px; border-radius:50%; object-fit:cover;">` : `<div class="d-inline-flex align-items-center justify-content-center font-weight-bold text-primary" style="width:48px; height:48px; border-radius:50%; background:var(--orb-soft); font-size:16px;">${emp.initials}</div>`}
                <div class="ml-3">
                    <h6 class="font-weight-bold text-dark mb-0">${emp.name}</h6>
                    <div class="text-muted small"><code>${emp.code}</code> &bull; ${emp.designation}</div>
                    <div class="badge badge-light border mt-1">${emp.department}</div>
                </div>
            </div>

            <!-- Active Cycle Counters -->
            <div class="row mx-0 mb-3">
                <div class="col-6 pl-0 pr-1">
                    <div class="p-3 text-center" style="background: #FFF7ED; border: 1px solid #FFEDD5; border-radius: 14px;">
                        <div class="small font-weight-bold text-muted text-uppercase">Discipline Cycle</div>
                        <div class="h4 font-weight-900 text-warning mb-0 mt-1">${ctr.discipline}</div>
                    </div>
                </div>
                <div class="col-6 pr-0 pl-1">
                    <div class="p-3 text-center" style="background: #FEF2F2; border: 1px solid #FEE2E2; border-radius: 14px;">
                        <div class="small font-weight-bold text-muted text-uppercase">Missed Punch Cycle</div>
                        <div class="h4 font-weight-900 text-danger mb-0 mt-1">${ctr.missed_punch}</div>
                    </div>
                </div>
            </div>

            <!-- Policy Rules -->
            <div class="p-3 mb-4" style="background: #F1F5F9; border-radius: 14px; font-size: 12px;">
                <div class="font-weight-bold text-dark mb-1"><i class="fas fa-gavel mr-1 text-primary"></i> ${pol.name} (${pol.shift_type})</div>
                <div class="text-muted">Shift Timing: <strong>${pol.shift_start} - ${pol.shift_end}</strong></div>
                <div class="text-muted">Discipline Limit: <strong>${pol.discipline_limit}</strong> &bull; Missed Limit: <strong>${pol.missed_limit}</strong></div>
            </div>

            <!-- Timeline -->
            <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-history mr-1 text-primary"></i> Violation Audit Timeline</h6>
            <div class="timeline-list">${timelineHtml}</div>
        `;

            drawerBody.innerHTML = html;
        }

        // Attendance Audit Modal Logic
        document.querySelectorAll('.js-open-att-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                const attId = this.getAttribute('data-att-id');
                if (!attId) return;

                $('#attendanceAuditModal').modal('show');
                const modalBody = document.getElementById('attendanceAuditModalBody');
                modalBody.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-circle-notch fa-spin fa-2x mb-2 text-primary"></i>
                    <div>Loading attendance details...</div>
                </div>
            `;

                fetch(`{{ url('hrms/attendance/violations/attendance-audit') }}/${attId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            modalBody.innerHTML = `<div class="alert alert-danger">${data.message || 'Unable to load attendance details.'}</div>`;
                            return;
                        }

                        const att = data.attendance;
                        modalBody.innerHTML = `
                        <div class="p-3 mb-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px;">
                            <div class="font-weight-bold text-dark h6 mb-1">${att.employee_name} (<code>${att.employee_code}</code>)</div>
                            <div class="text-muted small">${att.department} &bull; Date: <strong>${att.date}</strong></div>
                        </div>

                        <div class="row mx-0 mb-3 text-center">
                            <div class="col-4 p-2 border-right">
                                <div class="small text-muted font-weight-bold">PUNCH IN</div>
                                <div class="font-weight-bold text-dark mt-1">${att.punch_in}</div>
                            </div>
                            <div class="col-4 p-2 border-right">
                                <div class="small text-muted font-weight-bold">PUNCH OUT</div>
                                <div class="font-weight-bold text-dark mt-1">${att.punch_out}</div>
                            </div>
                            <div class="col-4 p-2">
                                <div class="small text-muted font-weight-bold">TARGET OUT</div>
                                <div class="font-weight-bold text-dark mt-1">${att.target_punch_out}</div>
                            </div>
                        </div>

                        <div class="row mx-0 mb-3 text-center">
                            <div class="col-4 p-2">
                                <div class="small text-muted font-weight-bold">WORK MINS</div>
                                <div class="font-weight-bold text-primary mt-1">${att.total_work_minutes} mins</div>
                            </div>
                            <div class="col-4 p-2">
                                <div class="small text-muted font-weight-bold">LATE MINS</div>
                                <div class="font-weight-bold text-warning mt-1">${att.late_minutes} mins</div>
                            </div>
                            <div class="col-4 p-2">
                                <div class="small text-muted font-weight-bold">EARLY OUT</div>
                                <div class="font-weight-bold text-info mt-1">${att.early_out_minutes} mins</div>
                            </div>
                        </div>

                        <div class="p-3" style="background: #F1F5F9; border-radius: 12px; font-size: 13px;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted font-weight-bold">ATTENDANCE STATUS</span>
                                <span class="font-weight-bold text-primary">${att.status}</span>
                            </div>
                            ${att.is_half_day ? `<div class="text-warning font-weight-bold mb-1"><i class="fas fa-adjust mr-1"></i> Half Day: ${att.half_day_reason || 'Policy Threshold'}</div>` : ''}
                            ${att.is_lwp ? `<div class="text-danger font-weight-bold mb-1"><i class="fas fa-ban mr-1"></i> LWP: ${att.lwp_reason || 'Policy Threshold'}</div>` : ''}
                            <div class="text-muted small mt-2"><i class="fas fa-gavel mr-1"></i> Policy Applied: <strong>${att.policy_name}</strong></div>
                        </div>
                    `;
                    })
                    .catch(err => {
                        console.error(err);
                        modalBody.innerHTML = `<div class="alert alert-danger">Error fetching attendance audit details.</div>`;
                    });
            });
        });
    });
</script>
@endsection