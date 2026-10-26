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
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }

    .att-filter-grid .att-filter-col {
        flex: 1 1 140px;
        min-width: 125px;
    }

    .att-filter-grid .att-filter-workmode {
        flex: 1 1 130px;
        min-width: 120px;
    }

    .att-filter-grid .att-filter-year {
        flex: 0.8 1 100px;
        min-width: 90px;
        max-width: 120px;
    }

    .att-filter-grid .att-filter-month {
        flex: 1.2 1 150px;
        min-width: 135px;
    }

    .att-filter-grid .att-filter-date {
        flex: 1 1 135px;
        min-width: 120px;
    }

    .att-filter-grid .att-filter-from,
    .att-filter-grid .att-filter-to {
        flex: 1 1 135px;
        min-width: 120px;
    }

    .att-filter-grid .att-filter-actions-col {
        flex: 0 0 auto;
        min-width: auto;
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
        height: 38px;
        border-radius: 10px;
        border: 1px solid var(--orb-border);
        background: #FFFFFF;
        padding: 6px 12px;
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

    .att-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-search-btn {
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #6B11F4) 100%);
        color: #FFFFFF;
        border: none;
        padding: 0 18px;
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
        height: 38px;
        width: 38px;
        border-radius: 10px;
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

    .work-summary-full-text p {
        margin-bottom: 4px;
    }
    .work-summary-full-text p:last-child {
        margin-bottom: 0;
    }

    .structured-task-item {
        margin-bottom: 3px;
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
                    <input type="hidden" name="per_page" id="hiddenPerPageInput" value="{{ request('per_page', $filters['per_page'] ?? 25) }}">
                    <div class="att-filter-grid">
                        @php
                            $currY = $filters['current_year'] ?? date('Y');
                            $selY = $filters['selected_year'] ?? request('year', $currY);
                            $currM = $filters['current_month'] ?? date('m');
                            $selM = $filters['selected_month'] ?? request('month', $currM);
                            if (request()->filled('date')) {
                                try {
                                    $pDate = \Carbon\Carbon::parse(request('date'));
                                    $selY = $pDate->format('Y');
                                    $selM = $pDate->format('m');
                                } catch (\Throwable $e) {}
                            }
                            $isCustom = ($selM === 'custom' || (!request()->has('month') && !request()->filled('date') && (request()->filled('from_date') || request()->filled('to_date'))));
                        @endphp

                        <div class="att-filter-col att-filter-workmode">
                            <label>Work Mode</label>
                            <select name="work_mode" id="hist_work_mode" class="form-control select2-searchable" data-placeholder="All Modes">
                                <option value="">All Modes</option>
                                <option value="wfo" {{ strtolower(request('work_mode', $filters['work_mode'] ?? '')) === 'wfo' ? 'selected' : '' }}>Office (WFO)</option>
                                <option value="wfh" {{ strtolower(request('work_mode', $filters['work_mode'] ?? '')) === 'wfh' ? 'selected' : '' }}>Remote (WFH)</option>
                            </select>
                        </div>

                        <div class="att-filter-col att-filter-year" id="yearFilterWrap">
                            <label><i class="fas fa-calendar text-primary mr-1"></i> Year</label>
                            <select name="year" id="yearFilterSelect" class="form-control select2-searchable" data-allow-clear="false">
                                @foreach($filters['years'] ?? get_filter_years(1, 4) as $yVal => $yLbl)
                                    <option value="{{ $yVal }}" {{ (string) $selY === (string) $yVal ? 'selected' : '' }}>{{ $yLbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-col att-filter-month" id="monthFilterWrap">
                            <label><i class="fas fa-calendar-alt text-primary mr-1"></i> Month</label>
                            <select name="month" id="monthFilterSelect" class="form-control select2-searchable" data-allow-clear="false">
                                <option value="custom" {{ $isCustom ? 'selected' : '' }}>Custom Date Range</option>
                                @foreach($filters['months'] ?? get_filter_months(true) as $val => $lbl)
                                    @if($val !== 'custom')
                                        @php
                                            $isSel = !$isCustom && ((string) $selM === (string) $val || (is_numeric($selM) && is_numeric($val) && (int) $selM === (int) $val));
                                        @endphp
                                        <option value="{{ $val }}" {{ $isSel ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-col att-filter-date" id="singleDateWrap" style="display: {{ $isCustom ? 'none' : 'block' }};">
                            <label><i class="fas fa-calendar-day text-primary mr-1"></i> Date</label>
                            <x-form.date-picker name="date" id="filterDate" :value="request('date', $filters['date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-col att-filter-from" id="customFromWrap" style="display: {{ $isCustom ? 'block' : 'none' }};">
                            <label>Date From</label>
                            <x-form.date-picker name="from_date" id="filterFromDate" :value="request('from_date', $filters['from_date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-col att-filter-to" id="customToWrap" style="display: {{ $isCustom ? 'block' : 'none' }};">
                            <label>Date To</label>
                            <x-form.date-picker name="to_date" id="filterToDate" :value="request('to_date', $filters['to_date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-col att-filter-actions-col">
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
                                    <option value="{{ $size }}" {{ (int) request('per_page', $filters['per_page'] ?? 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
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
                            <th style="min-width: 380px; width: 42%;">Work Summary Description</th>
                            <th style="min-width: 240px; width: 28%;">Structured Tasks</th>
                            <th class="text-right pr-4 no-export" style="width: 110px; min-width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $startIdx = method_exists($workLogs, 'currentPage') ? (($workLogs->currentPage() - 1) * $workLogs->perPage()) : 0;
                        @endphp
                        @forelse($workLogs as $log)
                        @php
                            $row = formatWorkReportRow($log);
                            $modeBadgeClass = $row['mode'] === 'WFH' ? 'badge-wfh' : 'badge-wfo';

                            $logPayload = [
                                'id' => $log->id,
                                'work_log_id' => $log->id,
                                'employee_name' => $summary['employee_name'],
                                'employee_code' => $summary['employee_code'],
                                'passport_photo_url' => $summary['passport_photo_url'],
                                'employee_initial' => $summary['employee_initial'],
                                'department' => $summary['department'],
                                'designation' => $summary['designation'],
                                'work_date' => $row['date'],
                                'shift_name' => $row['shift_context'],
                                'attendance_status' => (optional($log->attendance)->attendance_status ?? 'present'),
                                'title' => $row['title'] ?? 'Work Report',
                                'description' => $row['summary_desc'],
                                'status' => $row['status'],
                                'work_mode' => $row['mode'],
                                'submitted_time' => $row['submitted_time'],
                                'projects' => $row['projects'] ?? [],
                                'requirements' => array_map(fn($t) => ['text' => $t['text'], 'done' => $t['done']], $row['structured_tasks']),
                                'test_status' => $row['test_status'] ?? ['tested' => false, 'completed' => true],
                                'issues' => $row['issues'] ?? [],
                                'notes' => $row['notes'] ?? null,
                            ];
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold text-muted table-sr-no" style="font-size: 12px;" data-export="{{ $startIdx + $loop->iteration }}">
                                {{ $startIdx + $loop->iteration }}
                            </td>

                            <td data-export="{{ $row['date'] }}" data-order="{{ $row['date_raw'] }}">
                                <div class="font-weight-bold text-dark" style="font-size: 13px; white-space: nowrap;">
                                    {{ $row['date'] }}
                                </div>
                                @if($row['day_name'])
                                <div class="small text-muted font-weight-semibold">
                                    {{ $row['day_name'] }}
                                </div>
                                @endif
                            </td>

                            <td data-export="{{ $row['mode'] }}">
                                <span class="badge-premium-pill {{ $modeBadgeClass }}">
                                    @if($row['mode'] === 'WFH')
                                        <i class="fas fa-laptop-house mr-1"></i> WFH
                                    @else
                                        <i class="fas fa-building mr-1"></i> WFO
                                    @endif
                                </span>
                            </td>

                            <td data-export="{{ $row['shift_context'] }}">
                                <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                    {{ $row['shift_context'] }}
                                </div>
                            </td>

                            <td data-export="{{ $row['gross_work'] }}">
                                <div class="badge-gross-pill" style="white-space: nowrap;">
                                    <i class="fas fa-stopwatch mr-1"></i> {{ $row['gross_work'] }}
                                </div>
                            </td>

                            <td data-export="{{ $row['summary_desc'] }}" style="min-width: 320px;">
                                @if(!empty($row['projects']) && count($row['projects']) > 0)
                                    <div class="d-flex flex-column" style="gap: 6px;">
                                        <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                            @foreach($row['projects'] as $pItem)
                                                @php
                                                    $pName = $pItem['project_name'] ?? ($pItem['name'] ?? 'Project');
                                                    $pTasks = $pItem['tasks'] ?? [];
                                                    $pDoneCount = 0;
                                                    foreach($pTasks as $pt) {
                                                        if (is_array($pt) && (!empty($pt['is_completed']) || !empty($pt['completed']))) $pDoneCount++;
                                                        elseif (!is_array($pt)) $pDoneCount++;
                                                    }
                                                @endphp
                                                <div class="p-1.5 px-2.5 rounded border" style="background: #F8FAFC; border: 1px solid #E2E8F0 !important; border-radius: 8px;">
                                                    <div class="d-flex align-items-center justify-content-between" style="gap: 10px;">
                                                        <span class="font-weight-bold text-dark" style="font-size: 12px;">
                                                            <i class="fas fa-folder text-primary mr-1" style="font-size: 11px;"></i> {{ $pName }}
                                                        </span>
                                                        <span class="badge border" style="font-size: 10px; font-weight: 700; background: #fff; color: #475467; padding: 2px 6px; border-radius: 6px;">
                                                            {{ count($pTasks) }} {{ count($pTasks) === 1 ? 'task' : 'tasks' }} &bull; <span class="{{ $pDoneCount === count($pTasks) ? 'text-success' : 'text-primary' }}">{{ $pDoneCount }}/{{ count($pTasks) }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if(!empty($row['notes']))
                                            <div class="mt-1 p-1.5 px-2 rounded" style="background: #FFFBEB; border: 1px solid #FEF3C7; color: #92400E; font-size: 11.5px; border-radius: 6px;">
                                                <strong>Note:</strong> {{ $row['notes'] }}
                                            </div>
                                        @endif
                                        @if(!empty($row['issues']) && count($row['issues']) > 0)
                                            <div class="mt-1 p-1.5 px-2 rounded" style="background: #FEF2F2; border: 1px solid #FEE2E2; color: #991B1B; font-size: 11.5px; border-radius: 6px;">
                                                <strong>Issue:</strong> {{ implode(', ', $row['issues']) }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div>
                                        @if(!empty($row['title']))
                                            <div class="mb-1.5">
                                                <div class="p-1.5 px-2.5 rounded border d-inline-flex align-items-center" style="background: #F8FAFC; border: 1px solid #E2E8F0 !important; border-radius: 8px; gap: 8px;">
                                                    <span class="font-weight-bold text-dark" style="font-size: 12px;">
                                                        <i class="fas fa-folder text-primary mr-1" style="font-size: 11px;"></i> {{ $row['title'] }}
                                                    </span>
                                                    @if(!empty($row['structured_tasks']))
                                                        <span class="badge border" style="font-size: 10px; font-weight: 700; background: #fff; color: #475467; padding: 2px 6px; border-radius: 6px;">
                                                            {{ count($row['structured_tasks']) }} {{ count($row['structured_tasks']) === 1 ? 'task' : 'tasks' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        <div class="work-summary-full-text">
                                            @foreach($row['summary_paragraphs'] as $para)
                                                @php
                                                    $paraDisplay = $para;
                                                    if (!empty($row['title'])) {
                                                        $paraDisplay = preg_replace('/^' . preg_quote($row['title'], '/') . '\s*[-–:]\s*/i', '', $paraDisplay);
                                                    }
                                                @endphp
                                                @if(trim($paraDisplay) !== '')
                                                    <p class="mb-1" style="line-height: 1.5; color: #1E293B; font-size: 12.5px; font-weight: 500;">
                                                        {{ $paraDisplay }}
                                                    </p>
                                                @endif
                                            @endforeach
                                        </div>
                                        @if(!empty($row['notes']))
                                            <div class="mt-1 p-1.5 px-2 rounded" style="background: #FFFBEB; border: 1px solid #FEF3C7; color: #92400E; font-size: 11.5px; border-radius: 6px;">
                                                <strong>Note:</strong> {{ $row['notes'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td data-export="{{ $row['structured_tasks_text'] }}" style="min-width: 240px;">
                                @if(!empty($row['projects']) && count($row['projects']) > 0)
                                    <div class="structured-tasks-grouped d-flex flex-column" style="gap: 6px;">
                                        @foreach($row['projects'] as $pItem)
                                            @php
                                                $pName = $pItem['project_name'] ?? ($pItem['name'] ?? 'Project');
                                                $pTasks = $pItem['tasks'] ?? [];
                                            @endphp
                                            <div class="structured-project-group">
                                                <div class="font-weight-bold text-dark mb-1" style="font-size: 11.5px; opacity: 0.9;">
                                                    <i class="fas fa-folder text-primary mr-1" style="font-size: 10.5px;"></i> {{ $pName }}:
                                                </div>
                                                @foreach($pTasks as $pt)
                                                    @php
                                                        $ptName = is_array($pt) ? ($pt['task_name'] ?? ($pt['description'] ?? 'Task')) : $pt;
                                                        $ptDone = is_array($pt) ? (!empty($pt['is_completed']) || !empty($pt['completed'])) : true;
                                                    @endphp
                                                    <div class="structured-task-item {{ $ptDone ? 'done' : 'pending' }} d-flex align-items-center pl-2 mb-1" style="line-height: 1.4; font-size: 12px; font-weight: 600;">
                                                        <span class="task-tag font-weight-bold {{ $ptDone ? 'text-success' : 'text-warning' }}" style="font-size: 11px; margin-right: 6px; white-space: nowrap;">
                                                            {{ $ptDone ? '[Done]' : '[Pending]' }}
                                                        </span>
                                                        <span class="text-dark">{{ $ptName }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="structured-tasks-list">
                                        @if(!empty($row['title']))
                                            <div class="font-weight-bold text-dark mb-1" style="font-size: 11.5px; opacity: 0.9;">
                                                <i class="fas fa-folder text-primary mr-1" style="font-size: 10.5px;"></i> {{ $row['title'] }}:
                                            </div>
                                        @endif
                                        @foreach($row['structured_tasks'] as $tItem)
                                            <div class="structured-task-item {{ $tItem['done'] ? 'done' : 'pending' }} d-flex align-items-center pl-2 mb-1" style="line-height: 1.4; font-size: 12px; font-weight: 600;">
                                                <span class="task-tag font-weight-bold {{ $tItem['done'] ? 'text-success' : 'text-warning' }}" style="font-size: 11px; margin-right: 6px; white-space: nowrap;">
                                                    {{ $tItem['done'] ? '[Done]' : '[Pending]' }}
                                                </span>
                                                <span class="text-dark">{{ $tItem['text'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
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

<script>
$(function() {
    // Initialize Select2 via global panel helper to preserve allowClear and standard styles
    if (typeof window.initSearchableSelects === 'function') {
        window.initSearchableSelects();
    }

    // Toggle Custom Date Fields based on Month Filter
    $('#monthFilterSelect').on('change select2:select', function() {
        var val = $(this).val();
        if (val === 'custom') {
            $('#singleDateWrap').slideUp(150);
            $('#customFromWrap').slideDown(150);
            $('#customToWrap').slideDown(150);
            $('#filterDate').val('');
        } else {
            $('#customFromWrap').slideUp(150);
            $('#customToWrap').slideUp(150);
            $('#singleDateWrap').slideDown(150);
            $('#filterFromDate').val('');
            $('#filterToDate').val('');
        }
    });

    function extractYearMonth(str) {
        if (!str) return null;
        var parts = str.split(/[-/]/);
        if (parts.length === 3) {
            if (parts[0].length === 4) {
                return { year: parts[0], month: parts[1].padStart(2, '0') };
            } else if (parts[2].length === 4) {
                return { year: parts[2], month: parts[1].padStart(2, '0') };
            }
        }
        return null;
    }

    // Auto-sync Year & Month when single Date is chosen
    $('#filterDate').on('change input', function() {
        var dateVal = $(this).val();
        if (dateVal) {
            $('#filterFromDate').val('');
            $('#filterToDate').val('');
            var ym = extractYearMonth(dateVal);
            if (ym) {
                if ($('#yearFilterSelect option[value="' + ym.year + '"]').length > 0) {
                    $('#yearFilterSelect').val(ym.year).trigger('change.select2');
                }
                if ($('#monthFilterSelect option[value="' + ym.month + '"]').length > 0) {
                    $('#monthFilterSelect').val(ym.month).trigger('change.select2');
                }
            }
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
                exportOptions: { 
                    columns: ':not(.no-export)',
                    format: {
                        body: function(data, row, column, node) {
                            if (node && node.hasAttribute('data-export')) {
                                return node.getAttribute('data-export');
                            }
                            return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                        }
                    }
                } 
            },
            { 
                extend: 'excelHtml5', 
                text: 'Excel', 
                className: 'd-none', 
                exportOptions: { 
                    columns: ':not(.no-export)',
                    format: {
                        body: function(data, row, column, node) {
                            if (node && node.hasAttribute('data-export')) {
                                return node.getAttribute('data-export');
                            }
                            return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                        }
                    }
                } 
            },
            { 
                extend: 'pdfHtml5', 
                text: 'PDF', 
                className: 'd-none', 
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { 
                    columns: ':not(.no-export)',
                    format: {
                        body: function(data, row, column, node) {
                            if (node && node.hasAttribute('data-export')) {
                                return node.getAttribute('data-export');
                            }
                            return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                        }
                    }
                },
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 8;
                    doc.styles.tableHeader.fontSize = 9;
                    doc.styles.tableHeader.fillColor = '#4B00E8';
                    doc.styles.tableHeader.color = '#FFFFFF';
                    doc.styles.tableHeader.alignment = 'left';
                    doc.pageMargins = [15, 20, 15, 20];
                    if (doc.content && doc.content[1] && doc.content[1].table) {
                        doc.content[1].table.widths = ['5%', '13%', '9%', '12%', '11%', '26%', '24%'];
                    }
                }
            },
            { 
                extend: 'print', 
                text: 'Print', 
                className: 'd-none',
                exportOptions: { 
                    columns: ':not(.no-export)',
                    format: {
                        body: function(data, row, column, node) {
                            if (node && node.hasAttribute('data-export')) {
                                return node.getAttribute('data-export').replace(/\n/g, '<br>');
                            }
                            return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                        }
                    }
                },
                customize: function(win) {
                    $(win.document.body).css('font-size', '11px');
                    $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                }
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
