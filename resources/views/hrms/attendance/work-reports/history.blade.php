@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Employee Work Report History - ' . ($summary['employee_name'] ?? 'Employee'))

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg) !important;
        font-family: 'Outfit', sans-serif !important;
    }

    .report-page, .att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .report-container, .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Hero Header */
    .report-header-premium {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #6B11F4) 100%) !important;
        border-radius: 26px !important;
        padding: 28px 34px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 14px 35px rgba(75, 0, 232, 0.18) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
        border: none !important;
    }

    .report-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 320px !important;
        height: 320px !important;
        background: rgba(255, 255, 255, 0.09) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .emp-profile-pill {
        display: flex;
        align-items: center;
        gap: 18px;
        position: relative;
        z-index: 1;
    }

    .emp-profile-avatar {
        width: 62px;
        height: 62px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 900;
        color: #fff;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    }

    .emp-profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .emp-profile-title {
        font-size: 24px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
        line-height: 1.2;
    }

    .emp-profile-meta {
        font-size: 13.5px !important;
        color: rgba(255, 255, 255, 0.9) !important;
        margin-top: 5px !important;
        font-weight: 600 !important;
    }

    .action-toolbar-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        z-index: 1;
        flex-wrap: wrap;
    }

    .report-btn-pill {
        height: 40px;
        padding: 0 20px;
        border-radius: 50px;
        font-size: 12.5px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.15);
        color: #FFFFFF !important;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .report-btn-pill:hover {
        background: #FFFFFF;
        color: var(--orb-primary, #4B00E8) !important;
        transform: translateY(-2px);
    }

    .report-btn-pill.btn-white {
        background: #FFFFFF !important;
        color: var(--orb-primary, #4B00E8) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
    }

    .report-btn-pill.btn-white:hover {
        background: #F8FAFC !important;
        transform: translateY(-2px);
    }

    /* Summary KPI Cards matching Violations design */
    .report-kpi-grid, .audit-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .att-kpi {
        min-height: 86px;
        padding: 12px 16px;
        border-radius: 18px;
        border: 1px solid var(--orb-border);
        background: #fff;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045);
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
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
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tone-soft);
        color: var(--tone);
        font-size: 15px;
    }

    .att-kpi-value {
        font-size: 24px;
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
        left: 14px;
        right: 14px;
        bottom: 6px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--tone), transparent);
    }

    .tone-success { --tone: #12B76A; --tone-soft: rgba(18, 183, 106, .12); }
    .tone-danger  { --tone: #F04438; --tone-soft: rgba(240, 68, 56, .12); }
    .tone-warning { --tone: #F79009; --tone-soft: rgba(247, 144, 9, .14); }
    .tone-orange  { --tone: #EA580C; --tone-soft: rgba(234, 88, 12, .13); }
    .tone-amber   { --tone: #D97706; --tone-soft: rgba(217, 119, 6, .13); }
    .tone-blocked { --tone: #B42318; --tone-soft: rgba(180, 35, 24, .13); }
    .tone-purple  { --tone: #7000FF; --tone-soft: rgba(112, 0, 255, .12); }
    .tone-blue    { --tone: #175CD3; --tone-soft: rgba(23, 92, 211, .12); }
    .tone-emerald { --tone: #027A48; --tone-soft: rgba(2, 122, 72, .12); }
    .tone-indigo  { --tone: #4B00E8; --tone-soft: rgba(75, 0, 232, .12); }

    /* Main Container Card */
    .att-card, .orb-table-card {
        background: #FFFFFF !important;
        border-radius: 24px !important;
        border: 1px solid var(--orb-border) !important;
        box-shadow: var(--orb-shadow) !important;
        overflow: hidden !important;
        margin-bottom: 30px !important;
    }

    /* Section Head inside Card */
    .att-section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px 16px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: #FFFFFF;
    }

    .att-section-title {
        font-size: 17px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-section-title i {
        color: var(--orb-primary, #4B00E8);
    }

    /* Filter Panel below Section Head */
    .att-filter-panel {
        padding: 18px 24px;
        background: #FAFBFC;
        border-bottom: 1px solid var(--orb-border);
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 14px;
        align-items: flex-end;
    }

    .att-filter-grid label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--orb-muted);
        margin-bottom: 6px;
        display: block;
    }

    .att-filter-grid .form-control {
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #FFFFFF;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
        width: 100%;
        transition: all 0.2s ease;
    }

    .att-filter-grid .form-control:focus {
        border-color: var(--orb-primary, #4B00E8);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    }

    .att-filter-panel .select2-container .select2-selection--single {
        height: 42px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        padding: 6px 12px !important;
        display: flex !important;
        align-items: center !important;
        background: #FFFFFF !important;
    }

    .att-filter-panel .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 8px !important;
    }

    .att-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-search-btn {
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #6B11F4) 100%);
        color: #FFFFFF;
        border: none;
        padding: 0 20px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .att-search-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3);
        color: #FFFFFF;
    }

    .att-reset-btn {
        height: 42px;
        width: 42px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid var(--orb-border);
        color: var(--orb-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
        text-decoration: none !important;
    }

    .att-reset-btn:hover {
        background: #F8FAFC;
        color: var(--orb-primary, #4B00E8);
        border-color: #D9CCFF;
    }

    /* Table Component */
    .att-table-wrap, .orb-table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .att-table, .report-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .att-table thead th, .report-table thead th {
        background: #FAFBFC;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #475467;
        padding: 14px 18px;
        border-bottom: 1px solid var(--orb-border);
        border-top: none;
        white-space: nowrap;
        vertical-align: middle;
    }

    .att-table tbody td, .report-table tbody td {
        padding: 14px 18px;
        font-size: 13px;
        vertical-align: middle;
        border-bottom: 1px solid var(--orb-border);
        color: var(--orb-text);
        background: #FFFFFF;
        transition: background 0.15s ease;
    }

    .att-table tbody tr:hover td, .report-table tbody tr:hover td {
        background: #F9FAFB !important;
    }

    /* Badges */
    .badge-premium-pill {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        letter-spacing: 0.03em;
    }

    .badge-wfo { background: #ECFDF3; color: #027A48; border: 1px solid #A6F4C5; }
    .badge-wfh { background: #EFF8FF; color: #175CD3; border: 1px solid #B2DDFF; }

    .badge-gross-pill {
        background: #FEF7C3;
        color: #B54708;
        border: 1px solid #FEE4E2;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .project-tag-pill {
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        border: 1px solid rgba(75, 0, 232, 0.15);
        font-weight: 800;
        font-size: 11.5px;
        padding: 3px 10px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        max-width: 100%;
        margin-bottom: 6px;
    }

    .work-summary-snippet {
        font-size: 12.5px;
        color: #1E293B;
        line-height: 1.5;
        font-weight: 500;
    }

    .work-tasks-mini-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 6px;
    }

    .mini-task-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .mini-task-pill.done {
        background: #ECFDF5;
        border-color: #A7F3D0;
        color: #065F46;
    }

    .mini-task-pill.pending {
        background: #FFFBEB;
        border-color: #FDE68A;
        color: #92400E;
    }

    .mini-task-more {
        font-size: 10.5px;
        font-weight: 800;
        color: #64748B;
        padding: 2px 6px;
        background: #F1F5F9;
        border-radius: 6px;
    }

    /* Action Buttons */
    .btn-action-primary {
        height: 34px;
        padding: 0 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        border: 1px solid rgba(75, 0, 232, 0.15);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none !important;
        cursor: pointer;
    }

    .btn-action-primary:hover {
        background: var(--orb-primary, #4B00E8);
        color: #FFFFFF !important;
        box-shadow: 0 4px 10px rgba(75, 0, 232, 0.25);
    }

    /* DataTables wrapper adjustments */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        display: none !important;
    }

    @media (max-width: 1200px) {
        .report-kpi-grid, .audit-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .report-header-premium {
            flex-direction: column;
            align-items: flex-start;
            padding: 24px 20px !important;
        }
        .report-kpi-grid, .audit-kpi-grid {
            grid-template-columns: 1fr;
        }
        .att-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('_content')

<div class="report-page att-page">
    <div class="report-container att-container">

        <!-- Hero Header -->
        <div class="report-header-premium">
            <div class="emp-profile-pill">
                <div class="emp-profile-avatar">
                    @if($summary['passport_photo_url'])
                        <img src="{{ $summary['passport_photo_url'] }}" alt="{{ $summary['employee_name'] }}">
                    @else
                        <span>{{ $summary['employee_initial'] }}</span>
                    @endif
                </div>
                <div>
                    <h3 class="emp-profile-title">{{ $summary['employee_name'] }}</h3>
                    <div class="emp-profile-meta">
                        Code: {{ $summary['employee_code'] }} &bull; {{ $summary['department'] }} &bull; {{ $summary['designation'] }}
                    </div>
                </div>
            </div>

            <div class="action-toolbar-pill">
                <a href="{{ route('hrms.attendance.work-reports') }}" class="report-btn-pill">
                    <i class="fas fa-arrow-left"></i> All Work Reports
                </a>
            </div>
        </div>

        <!-- Summary KPI Cards matching Violations design -->
        <div class="report-kpi-grid audit-kpi-grid">
            <div class="att-kpi tone-purple">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="att-kpi-value">{{ number_format($summary['total_reports']) }}</div>
                </div>
                <div class="att-kpi-label">Daily Reports Logged</div>
                <div class="att-kpi-line"></div>
            </div>

            <div class="att-kpi tone-amber">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-clock"></i></div>
                    <div class="att-kpi-value" style="font-size: 19px; line-height: 1.2;">{{ $summary['total_gross_formatted'] }}</div>
                </div>
                <div class="att-kpi-label">Avg {{ $summary['avg_daily_formatted'] }}</div>
                <div class="att-kpi-line"></div>
            </div>

            <div class="att-kpi tone-success">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-tasks"></i></div>
                    <div class="att-kpi-value" style="font-size: 19px; line-height: 1.2;">
                        <span>{{ $summary['completed_tasks'] }}</span>
                        <span style="font-size: 14px; color: #64748B; font-weight: 700;">/ {{ $summary['total_tasks'] }}</span>
                    </div>
                </div>
                <div class="att-kpi-label">{{ $summary['completion_rate'] }}% Tasks Completed</div>
                <div class="att-kpi-line"></div>
            </div>

            <div class="att-kpi tone-orange">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas fa-laptop-house"></i></div>
                    <div class="att-kpi-value" style="font-size: 19px; line-height: 1.2;">
                        <span>{{ $summary['wfo_count'] }} <small style="font-size: 11px; font-weight: 800; color: #027A48;">WFO</small></span>
                        <span style="font-size: 14px; opacity: 0.4;">/</span>
                        <span>{{ $summary['wfh_count'] }} <small style="font-size: 11px; font-weight: 800; color: #175CD3;">WFH</small></span>
                    </div>
                </div>
                <div class="att-kpi-label">Work Mode Distribution</div>
                <div class="att-kpi-line"></div>
            </div>
        </div>

        <!-- Main Card with Filter, Toolbar, and Table -->
        <div class="card att-card orb-table-card">
            <!-- Card Section Head -->
            <div class="att-section-head">
                <div class="att-section-title-wrap">
                    <h4 class="att-section-title">
                        <i class="fas fa-history"></i> Employee Work Report History
                    </h4>
                    <p class="text-muted small mb-0 mt-1 font-weight-semibold">
                        Review daily work deliverables, time duration, and structured achievements for {{ $summary['employee_name'] }}.
                    </p>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="att-filter-panel">
                <form method="GET" action="{{ route('hrms.attendance.work-reports.employee-history', $employee->id) }}" id="historyFilterForm">
                    <input type="hidden" name="per_page" id="hiddenPerPageInput" value="{{ request('per_page', 25) }}">
                    <div class="att-filter-grid">
                        <div>
                            <label>Work Mode</label>
                            <select name="work_mode" id="hist_work_mode" class="form-control select2-searchable">
                                <option value="">All Modes</option>
                                <option value="WFO" {{ request('work_mode') === 'WFO' ? 'selected' : '' }}>WFO (Office)</option>
                                <option value="WFH" {{ request('work_mode') === 'WFH' ? 'selected' : '' }}>WFH (Remote)</option>
                            </select>
                        </div>

                        <div>
                            <label>Month</label>
                            <select name="month" id="hist_month_select" class="form-control select2-searchable">
                                @php
                                    $activeMonth = request('month', $selectedMonth ?? now()->format('Y-m'));
                                    $isCustom = request()->filled('from_date') || request()->filled('to_date') || $activeMonth === 'custom';
                                @endphp
                                <option value="custom" {{ $isCustom ? 'selected' : '' }}>Custom Date Range</option>
                                @foreach($availableMonths as $val => $lbl)
                                    <option value="{{ $val }}" {{ (!$isCustom && $activeMonth === $val) ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="hist_custom_dates_wrap" style="{{ $isCustom ? '' : 'display: none;' }}">
                            <div class="d-flex gap-2" style="gap: 10px;">
                                <div style="flex: 1;">
                                    <label>From Date</label>
                                    <x-form.date-picker name="from_date" id="wr_hist_from" :value="request('from_date')" placeholder="dd-mm-yyyy" class="form-control" />
                                </div>
                                <div style="flex: 1;">
                                    <label>To Date</label>
                                    <x-form.date-picker name="to_date" id="wr_hist_to" :value="request('to_date')" placeholder="dd-mm-yyyy" class="form-control" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label>&nbsp;</label>
                            <div class="att-filter-actions">
                                <button type="submit" class="att-search-btn">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('hrms.attendance.work-reports.employee-history', $employee->id) }}" class="att-reset-btn" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Entries & Export Toolbar -->
            <div class="orb-table-tools-bar eo-toolbar">
                <div class="orb-table-length-box eo-toolbar-left">
                    <div class="eo-entries-wrapper">
                        <label class="eo-entries-label">
                            Show
                            <select id="recordsPerPageSelect" class="eo-entries-select">
                                @foreach([10, 25, 50, 100, 250] as $size)
                                    <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                                <option value="all" {{ (request('per_page') === 'all' || request('per_page') == -1) ? 'selected' : '' }}>All</option>
                            </select>
                            entries
                        </label>
                    </div>
                </div>

                <div id="historyExportButtons" class="orb-table-export-buttons eo-toolbar-right">
                    <x-ui.export-buttons table="employeeHistoryTable" />
                </div>
            </div>

            <!-- Table Section -->
            <div class="att-table-wrap">
                <table class="att-table table table-hover" id="employeeHistoryTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px; min-width: 50px;">#</th>
                            <th style="min-width: 120px;">Date</th>
                            <th style="min-width: 90px;">Mode</th>
                            <th style="min-width: 130px;">Shift Context</th>
                            <th style="min-width: 120px;">Gross Work</th>
                            <th style="min-width: 380px; width: 44%;">Work Summary Description</th>
                            <th style="min-width: 200px; width: 22%;">Structured Tasks</th>
                            <th class="text-right pr-4 no-export" style="width: 110px; min-width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $startIdx = method_exists($workLogs, 'currentPage') ? (($workLogs->currentPage() - 1) * $workLogs->perPage()) : 0;
                        @endphp
                        @forelse($workLogs as $log)
                        @php
                            $attendance = $log->attendance;
                            $mode = strtolower($attendance->work_mode ?? 'wfo');
                            $modeText = strtoupper($mode);
                            $modeBadgeClass = $mode === 'wfh' ? 'badge-wfh' : 'badge-wfo';
                            
                            $grossWork = $attendance && $attendance->gross_duration ? $attendance->gross_duration : '-';
                            $tasks = $log->work_summary_json;
                            if (is_string($tasks)) {
                                $tasks = json_decode($tasks, true);
                            }
                            
                            $title = 'Work Report Submitted';
                            $description = null;
                            $status = 'Completed';
                            $projectsList = [];
                            $requirementsList = [];
                            $testStatus = ['tested' => false, 'completed' => false];
                            $issues = [];
                            $notes = null;

                            if (is_array($tasks)) {
                                if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                                    $projectsList = $tasks['projects'];
                                    foreach ($projectsList as $p) {
                                        $pName = $p['project_name'] ?? $p['name'] ?? 'Project';
                                        if (isset($p['tasks']) && is_array($p['tasks'])) {
                                            foreach ($p['tasks'] as $t) {
                                                $tName = $t['task_name'] ?? $t['description'] ?? $t['task'] ?? $t['title'] ?? 'Task';
                                                $tDone = (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true) : true);
                                                $requirementsList[] = [
                                                    'text' => $tName,
                                                    'done' => $tDone,
                                                    'project' => $pName
                                                ];
                                            }
                                        }
                                    }
                                }

                                if (empty($requirementsList)) {
                                    $reqItems = $tasks['requirements'] ?? ($tasks['tasks'] ?? []);
                                    if (is_array($reqItems)) {
                                        $requirementsList = $reqItems;
                                    }
                                }

                                $status = $tasks['today_work_status'] ?? ($tasks['status'] ?? 'Completed');

                                if (!empty($projectsList) && !empty($projectsList[0]['project_name'])) {
                                    $title = $projectsList[0]['project_name'];
                                } elseif (!empty($tasks['task_name'])) {
                                    $title = $tasks['task_name'];
                                } elseif (!empty($tasks['title'])) {
                                    $title = $tasks['title'];
                                }

                                $description = $tasks['description'] ?? ($tasks['today_work_description'] ?? 'Work report submitted.');
                            } else {
                                $description = $log->work_summary ?? 'No summary provided.';
                            }
                            
                            // Clean and normalize summary description
                            $cleanSummary = $description;
                            if ($cleanSummary) {
                                if (str_contains($cleanSummary, '☑') || str_contains($cleanSummary, '🗹') || str_contains($cleanSummary, '☐') || str_contains($cleanSummary, 'Project:')) {
                                    $rawLines = explode("\n", str_replace(["\r\n", "\r"], "\n", $cleanSummary));
                                    $filteredLines = [];
                                    foreach ($rawLines as $rLine) {
                                        $trimmed = trim($rLine);
                                        if (empty($trimmed)) continue;
                                        if (stripos($trimmed, 'Project:') === 0) continue;
                                        if (stripos($trimmed, "Today's Work Status:") === 0 || stripos($trimmed, "Today Work Status:") === 0) continue;
                                        if (preg_match('/^[☑🗹☐✓✔•\-*]\s*/u', $trimmed)) {
                                            $trimmed = preg_replace('/^[☑🗹☐✓✔•\-*]\s*/u', '', $trimmed);
                                        }
                                        if (!empty($trimmed)) {
                                            $filteredLines[] = $trimmed;
                                        }
                                    }
                                    $filteredLines = array_unique($filteredLines);
                                    if (count($filteredLines) > 1 && !empty($title) && strtolower(trim($filteredLines[0])) === strtolower(trim($title))) {
                                        array_shift($filteredLines);
                                    }
                                    $cleanSummary = implode(' ', $filteredLines);
                                }
                                $cleanSummary = preg_replace('/[☑🗹☐✓✔]/u', '', $cleanSummary);
                                $cleanSummary = preg_replace('/Today\'?s\s+Work\s+Status:\s*[^\n\r]+/i', '', $cleanSummary);
                                $cleanSummary = preg_replace('/\s+/', ' ', trim($cleanSummary));
                            }

                            if (empty($cleanSummary) || $cleanSummary === 'Work report submitted.') {
                                if (!empty($requirementsList)) {
                                    $taskTexts = array_map(function($r) {
                                        return is_array($r) ? ($r['text'] ?? ($r['task_name'] ?? ($r['task'] ?? ''))) : (string)$r;
                                    }, array_slice($requirementsList, 0, 3));
                                    $taskTexts = array_filter($taskTexts);
                                    if (!empty($taskTexts)) {
                                        $cleanSummary = implode('; ', $taskTexts);
                                    }
                                }
                                if (empty($cleanSummary)) {
                                    $cleanSummary = 'Daily work deliverables logged.';
                                }
                            }

                            $tasksCount = is_array($requirementsList) ? count($requirementsList) : 0;

                            $logPayload = [
                                'id' => $log->id,
                                'work_log_id' => $log->id,
                                'employee_name' => $summary['employee_name'],
                                'employee_code' => $summary['employee_code'],
                                'passport_photo_url' => $summary['passport_photo_url'],
                                'employee_initial' => $summary['employee_initial'],
                                'department' => $summary['department'],
                                'designation' => $summary['designation'],
                                'work_date' => $log->work_date ? $log->work_date->format('d M Y') : '-',
                                'shift_name' => optional(optional($log->attendance)->attendanceTime)->name ?? 'Default Shift',
                                'attendance_status' => (optional($log->attendance)->attendance_status ?? 'present'),
                                'title' => $title,
                                'description' => $cleanSummary,
                                'status' => $status,
                                'work_mode' => strtoupper(optional($log->attendance)->work_mode ?? 'WFO'),
                                'submitted_time' => $log->created_at ? $log->created_at->format('h:i A') : '-',
                                'projects' => $projectsList,
                                'requirements' => $requirementsList,
                                'test_status' => $testStatus,
                                'issues' => $issues,
                                'notes' => $notes,
                            ];
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold text-muted table-sr-no" style="font-size: 12px;" data-export="{{ $startIdx + $loop->iteration }}">
                                {{ $startIdx + $loop->iteration }}
                            </td>
                            <td data-export="{{ $log->work_date ? $log->work_date->format('d M Y') : '-' }}" data-order="{{ $log->work_date ? $log->work_date->format('Y-m-d') : '' }}">
                                <div class="font-weight-bold text-dark" style="font-size: 13px; white-space: nowrap;">
                                    {{ $log->work_date ? $log->work_date->format('d M Y') : '-' }}
                                </div>
                                @if($log->work_date)
                                <div class="small text-muted font-weight-semibold">
                                    {{ $log->work_date->format('l') }}
                                </div>
                                @endif
                            </td>
                            <td data-export="{{ $modeText }}">
                                <span class="badge-premium-pill {{ $modeBadgeClass }}">
                                    @if($mode === 'wfh')
                                        <i class="fas fa-laptop-house mr-1"></i> WFH
                                    @else
                                        <i class="fas fa-building mr-1"></i> WFO
                                    @endif
                                </span>
                            </td>
                            <td data-export="{{ optional($attendance)->attendanceTime->name ?? 'Default Shift' }}">
                                <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                    {{ optional($attendance)->attendanceTime->name ?? 'Default Shift' }}
                                </div>
                            </td>
                            <td data-export="{{ $grossWork }}">
                                <div class="badge-gross-pill" style="white-space: nowrap;">
                                    <i class="fas fa-stopwatch mr-1"></i> {{ $grossWork }}
                                </div>
                            </td>
                            <td data-export="{{ $cleanSummary }}" style="min-width: 380px;">
                                @if(!empty($title) && $title !== 'Work Report Submitted' && strtolower(trim($title)) !== strtolower(trim($cleanSummary)))
                                <div class="project-tag-pill" title="{{ $title }}">
                                    <i class="fas fa-folder-open"></i> {{ $title }}
                                </div>
                                @endif
                                <div class="work-summary-snippet" title="{{ $cleanSummary }}">
                                    {{ $cleanSummary }}
                                </div>
                                @if(!empty($requirementsList) && count($requirementsList) > 0)
                                <div class="work-tasks-mini-list">
                                    @foreach(array_slice($requirementsList, 0, 2) as $reqItem)
                                        @php
                                            $rText = is_array($reqItem) ? ($reqItem['text'] ?? ($reqItem['task_name'] ?? 'Task')) : (string)$reqItem;
                                            $rDone = is_array($reqItem) ? ($reqItem['done'] ?? true) : true;
                                        @endphp
                                        <span class="mini-task-pill {{ $rDone ? 'done' : 'pending' }}">
                                            <i class="fas {{ $rDone ? 'fa-check' : 'fa-circle' }}"></i> {{ Str::limit($rText, 45) }}
                                        </span>
                                    @endforeach
                                    @if(count($requirementsList) > 2)
                                        <span class="mini-task-more">+{{ count($requirementsList) - 2 }} more</span>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td data-export="{{ $tasksCount }} Tasks" class="text-center">
                                @if($tasksCount > 0)
                                    <span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                        <i class="fas fa-list-check text-primary"></i> {{ $tasksCount }} Tasks
                                    </span>
                                @else
                                    <span class="text-muted font-italic" style="font-size:12px;">None</span>
                                @endif
                            </td>
                            <td class="text-right pr-4 no-export" style="white-space: nowrap;">
                                <button type="button" class="btn-action-primary"
                                        data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
                                        onclick="parseAndOpenWorkReport(this)"
                                        title="View Full Report Details">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-3x d-block mb-3 opacity-50"></i>
                                <h5 class="font-weight-bold text-dark mb-1">No Work Report History Found</h5>
                                <p class="text-muted font-weight-semibold mb-0">Try adjusting your filters or date range.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination using Orbo Theme Template -->
            @if(method_exists($workLogs, 'links'))
                {{ $workLogs->appends(request()->query())->links('vendor.pagination.orbo') }}
            @endif
        </div>

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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function() {
    // Initialize Select2 on filter panel
    if ($.fn.select2) {
        $('.select2-searchable').select2({
            width: '100%',
            minimumResultsForSearch: 8
        });
    }

    // Month Selector Change Toggle
    $('#hist_month_select').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#hist_custom_dates_wrap').slideDown(180);
        } else {
            $('#hist_custom_dates_wrap').slideUp(180);
            $('#wr_hist_from').val('');
            $('#wr_hist_to').val('');
        }
    });

    // Handle Server-Side Page Length Change
    $('#recordsPerPageSelect').on('change', function() {
        var len = $(this).val();
        $('#hiddenPerPageInput').val(len);
        $('#historyFilterForm').submit();
    });

    // Initialize DataTable for Exports and Sorting only (server handles pagination)
    var table = $('#employeeHistoryTable').DataTable({
        paging: false,
        ordering: true,
        searching: false,
        info: false,
        buttons: [
            { 
                extend: 'csvHtml5', 
                text: 'CSV', 
                className: 'd-none', 
                exportOptions: { columns: ':not(.no-export)' } 
            },
            { 
                extend: 'excelHtml5', 
                text: 'Excel', 
                className: 'd-none', 
                exportOptions: { columns: ':not(.no-export)' } 
            },
            { 
                extend: 'pdfHtml5', 
                text: 'PDF', 
                className: 'd-none', 
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: ':not(.no-export)' }
            },
            { 
                extend: 'print', 
                text: 'Print', 
                className: 'd-none',
                exportOptions: { columns: ':not(.no-export)' }
            }
        ]
    });

    // Custom Export buttons bridge for Orbo theme export buttons
    $(document).on('click', '.eo-toolbar [data-export-action]', function(e) {
        var action = $(this).data('export-action');
        if (action === 'csv') table.button('.buttons-csv').trigger();
        else if (action === 'excel') table.button('.buttons-excel').trigger();
        else if (action === 'pdf') table.button('.buttons-pdf').trigger();
        else if (action === 'print') table.button('.buttons-print').trigger();
    });
});
</script>
@endsection
