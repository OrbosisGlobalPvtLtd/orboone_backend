@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Daily Attendance Records')

@section('_head')
@endsection

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #FF5252;
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
        padding: 12px 14px 28px;
    }

    .att-container {
        max-width: 100% !important;
        width: 100%;
        margin: 0 auto;
    }

    .att-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
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
        background: rgba(255, 255, 255, .12);
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
        transition: all .2s ease;
    }

    .att-btn-glass,
    .att-hero-actions .att-btn {
        background: rgba(255, 255, 255, 0.18) !important;
        border: 1px solid rgba(255, 255, 255, 0.38) !important;
        color: #ffffff !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border-radius: 999px !important;
        padding: 9px 20px !important;
        font-size: 13.5px !important;
        font-weight: 750 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        transition: all 0.2s ease !important;
    }

    .att-btn-glass:hover,
    .att-hero-actions .att-btn:hover {
        background: rgba(255, 255, 255, 0.32) !important;
        border-color: rgba(255, 255, 255, 0.65) !important;
        color: #ffffff !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    }

    .att-btn-glass i,
    .att-hero-actions .att-btn i {
        color: #ffffff !important;
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
        color: var(--metric-color, var(--orb-primary));
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
        background: linear-gradient(90deg, var(--metric-color, var(--orb-primary)), transparent);
        margin-top: 8px;
    }

    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--orb-shadow);
        width: 100%;
    }

    .att-section-head {
        padding: 16px 20px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #fff, #FAFBFF);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .att-section-title {
        font-size: 18px;
        font-weight: 900;
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
        font-weight: 600;
        margin-top: 4px;
    }

    .att-filter-panel {
        padding: 14px 18px;
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
        height: 42px;
        border-radius: 12px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        font-weight: 700;
        padding: 0 12px;
        box-shadow: none !important;
        background: #fff;
    }

    .att-filter-group .form-control:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .10) !important;
    }

    .att-table-wrap {
        padding: 0 !important;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #CBD5E1 #F1F5F9;
    }

    .att-table-wrap::-webkit-scrollbar {
        height: 6px;
    }

    .att-table-wrap::-webkit-scrollbar-track {
        background: #F1F5F9;
        border-radius: 999px;
    }

    .att-table-wrap::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 999px;
    }

    .att-table-wrap::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }

    .att-table {
        width: 100% !important;
        min-width: 1040px;
        border-collapse: separate !important;
        border-spacing: 0;
        margin: 0 !important;
    }

    .att-table thead th {
        background: #F8FAFC !important;
        color: #475467 !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 11px 8px !important;
        border-top: none !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .att-table tbody td {
        background: #fff;
        border-bottom: 1px solid #F2F4F7 !important;
        padding: 9px 8px !important;
        vertical-align: middle !important;
        white-space: nowrap;
        font-size: 12.5px;
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF !important;
    }

    .att-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
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
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .att-emp-code {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .att-badge,
    .mode-badge,
    .flag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-weight: 950;
        text-transform: uppercase;
    }

    .att-badge,
    .mode-badge {
        padding: 6px 10px;
        font-size: 10px;
    }

    .flag {
        padding: 4px 8px;
        font-size: 9px;
        margin: 2px 3px 2px 0;
    }

    .badge-present {
        background: #DCFCE7;
        color: #166534;
    }

    .badge-absent,
    .badge-lwp {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-half_day {
        background: #FEF3C7;
        color: #92400E;
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

    .badge-missed_punch {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
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

    .flag-early,
    .flag-blocked {
        background: #FEF2F2;
        color: #B42318;
    }

    .flag-missed {
        background: #FEF3C7;
        color: #92400E;
    }

    .flag-clear {
        background: #F1F5F9;
        color: #475569;
    }

    .att-action-wrap {
        display: flex;
        justify-content: flex-end;
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
        justify-content: center;
        transition: all .2s ease;
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
        min-width: 185px;
    }

    .att-action-menu .dropdown-item {
        border-radius: 11px;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-action-menu .dropdown-item:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
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
        color: var(--orb-primary) !important;
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
            gap: 12px;
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

        @php
        $totalRecords = $statsSummary['total'] ?? (method_exists($attendances, 'total') ? $attendances->total() : count($attendances));
        $presentRecords = $statsSummary['present'] ?? 0;
        $lateRecords = $statsSummary['late'] ?? 0;
        $blockedRecords = $statsSummary['blocked'] ?? 0;
        $missedRecords = $statsSummary['missed'] ?? 0;
        $halfDayRecords = $statsSummary['half_day'] ?? 0;
        $wfoRecords = $statsSummary['wfo'] ?? 0;
        $wfhRecords = $statsSummary['wfh'] ?? 0;

        $activeMonth = request('month', $selectedMonth ?? now()->format('Y-m'));
        $isCustom = request()->filled('from_date') || request()->filled('to_date') || $activeMonth === 'custom';
        $isPaginator = method_exists($attendances, 'firstItem');
        @endphp

        <!-- HERO HEADER -->
        <div class="att-hero">
            <div>
                <div class="att-kicker">
                    <i class="fas fa-calendar-check"></i>
                    HRMS • ATTENDANCE
                </div>
                <h3 class="att-title">Daily Attendance Records</h3>
                <div class="att-subtitle">
                    Overall employee attendance records with filters, shift timing, work duration, flags and export options.
                </div>
            </div>
            <div class="att-hero-actions">
                <a href="{{ route('attendances.index') }}" class="att-btn att-btn-glass">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- STAT METRIC CARDS -->
        <div class="att-metric-grid">
            <div class="att-metric" style="--metric-color:#12B76A;--metric-soft:#E8F8EF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-list"></i></div>
                    <div class="att-metric-value">{{ number_format($totalRecords) }}</div>
                </div>
                <div class="att-metric-label">Total Records</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#16A34A;--metric-soft:#DCFCE7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="att-metric-value">{{ number_format($presentRecords) }}</div>
                </div>
                <div class="att-metric-label">Present</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#F97316;--metric-soft:#FFF7ED;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="att-metric-value">{{ number_format($lateRecords) }}</div>
                </div>
                <div class="att-metric-label">Late</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#D97706;--metric-soft:#FEF3C7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="att-metric-value">{{ number_format($missedRecords) }}</div>
                </div>
                <div class="att-metric-label">Missed Punch</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#F59E0B;--metric-soft:#FEF3C7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-business-time"></i></div>
                    <div class="att-metric-value">{{ number_format($halfDayRecords) }}</div>
                </div>
                <div class="att-metric-label">Half Day</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-building"></i></div>
                    <div class="att-metric-value">{{ number_format($wfoRecords) }}</div>
                </div>
                <div class="att-metric-label">WFO</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#0E7490;--metric-soft:#ECFEFF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-home"></i></div>
                    <div class="att-metric-value">{{ number_format($wfhRecords) }}</div>
                </div>
                <div class="att-metric-label">WFH</div>
                <div class="att-metric-line"></div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="border-radius:16px;font-weight:800;">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger" style="border-radius:16px;font-weight:800;">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
        </div>
        @endif

        <!-- MAIN TABLE CARD -->
        <div class="att-card">
            <div class="att-section-head">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:50%; background:#F4F2FF; color:var(--orb-primary); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div>
                        <h5 class="att-section-title" style="margin:0; font-size:18px;">Attendance Logs</h5>
                        <div class="att-section-sub" style="margin-top:4px;">Filter employee records, timings, gross/net work hours, and punch status.</div>
                    </div>
                </div>
                
            </div>

            <!-- FILTER PANEL -->
            <div class="att-filter-panel">
                <form method="GET" action="{{ route('attendances.daily') }}" id="attendanceFilterForm">
                    <div class="att-filter-grid">
                        <div class="att-filter-group">
                            <label><i class="fas fa-user text-primary mr-1"></i> Employee</label>
                            <select name="employee_id" class="form-control select2-searchable">
                                <option value="">All Staff</option>
                                @foreach($employees as $emp)
                                <option value="{{ optional($emp->employee)->id }}"
                                    {{ request('employee_id') == optional($emp->employee)->id ? 'selected' : '' }}>
                                    {{ $emp->name }}{{ optional($emp->employee)->employee_code ? ' (' . optional($emp->employee)->employee_code . ')' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label><i class="fas fa-calendar-alt text-primary mr-1"></i> Month</label>
                            <select name="month" id="daily_month_select" class="form-control select2-searchable">
                                <option value="custom" {{ $isCustom ? 'selected' : '' }}>Custom Date Range</option>
                                @foreach($availableMonths ?? [] as $val => $lbl)
                                <option value="{{ $val }}" {{ (!$isCustom && $activeMonth === $val) ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group" id="daily_custom_dates_wrap" style="{{ $isCustom ? '' : 'display: none;' }}">
                            <div class="d-flex" style="gap: 8px;">
                                <div style="flex: 1;">
                                    <label>From Date</label>
                                    <x-form.date-picker name="from_date" id="daily_from_date" :value="request('from_date')" placeholder="dd-mm-yyyy" class="form-control" />
                                </div>
                                <div style="flex: 1;">
                                    <label>To Date</label>
                                    <x-form.date-picker name="to_date" id="daily_to_date" :value="request('to_date')" placeholder="dd-mm-yyyy" class="form-control" />
                                </div>
                            </div>
                        </div>

                        <div class="att-filter-group">
                            <label>Status</label>
                            <select name="attendance_type_id" class="form-control select2-searchable">
                                <option value="">All Status</option>
                                @foreach($attendanceTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Work Mode</label>
                            <select name="work_mode" class="form-control select2-searchable">
                                <option value="">All</option>
                                <option value="wfo" {{ strtolower(request('work_mode')) === 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ strtolower(request('work_mode')) === 'wfh' ? 'selected' : '' }}>WFH</option>
                            </select>
                        </div>

                        <div class="att-filter-group d-flex align-items-end" style="gap: 8px;">
                            <button type="submit" class="btn text-white font-weight-bold shadow-sm" style="height:43px; border-radius:14px; background:linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border:none; flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px;">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('attendances.daily') }}" class="btn btn-light" style="height:43px; width:43px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; font-weight:750; border:1px solid #E4E7EC; background:#fff; color:#344054; flex-shrink:0;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- UNIFIED DATATABLES TOOLBAR -->
            <div class="leave-dt-toolbar">
                <div class="leave-dt-left">
                    <div id="recordsLengthBox"></div>
                </div>
                <div class="leave-dt-right">
                    <div id="recordsExportButtons"></div>
                </div>
            </div>

            <!-- TABLE WRAPPER -->
            <div class="att-table-wrap">
                <table class="table att-table" id="attendanceRecordsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">S.No</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Gross Work</th>
                            <th>Net Work</th>
                            <th>Status</th>
                            <th>Flags</th>
                            <th class="text-right pr-4 no-export">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        @php
                        $sNo = $isPaginator ? (($attendances->firstItem() ?? 1) + $loop->index) : $loop->iteration;
                        $attStatus = strtolower((string) ($attendance->attendance_status ?? ''));
                        $punchInTime = $attendance->punch_in_time;
                        $isAdminUnlocked = (bool) ($attendance->is_admin_unlocked ?? false);
                        
                        if (in_array($attStatus, ['unlocked', 'awaiting_punch_in'], true) || ($isAdminUnlocked && is_null($punchInTime))) {
                            $typeCode = 'warning';
                            $statusBadgeClass = 'badge-leave';
                            $statusName = 'Awaiting Punch In';
                        } elseif ($attStatus === 'punch_blocked' || ($attendance->is_punch_blocked ?? false) || ($attendance->is_blocked ?? false)) {
                            $typeCode = 'danger';
                            $statusBadgeClass = 'badge-punch_blocked';
                            $statusName = 'Punch Blocked';
                        } elseif ($attStatus === 'half_day' || ($attendance->is_half_day ?? false)) {
                            $typeCode = 'info';
                            $statusBadgeClass = 'badge-half_day';
                            $statusName = 'Half Day';
                        } elseif ($attStatus === 'lwp' || ($attendance->is_lwp ?? false)) {
                            $typeCode = 'danger';
                            $statusBadgeClass = 'badge-lwp';
                            $statusName = 'LWP';
                        } elseif ($attStatus === 'absent') {
                            $typeCode = 'danger';
                            $statusBadgeClass = 'badge-absent';
                            $statusName = 'ABSENT';
                        } elseif ($attStatus === 'present' || ! is_null($punchInTime)) {
                            $typeCode = 'success';
                            $statusBadgeClass = 'badge-present';
                            $statusName = 'Present';
                        } else {
                            $typeCode = 'secondary';
                            $statusBadgeClass = 'badge-default';
                            $statusName = optional($attendance->attendanceType)->name ?? 'Pending';
                        }
                        $modeCode = strtoupper($attendance->work_mode ?? '-');
                        $modeClass = strtolower($modeCode) === 'wfo' ? 'mode-wfo' : (strtolower($modeCode) === 'wfh' ? 'mode-wfh' : 'mode-default');

                        $empUser = optional($attendance->user);
                        $empObj = optional($attendance->employee);
                        $avatarUrl = $empUser->profile_picture ?? $empObj->profile_picture ?? $empObj->avatar ?? null;
                        $nameInitials = strtoupper(substr($empUser->name ?? $empObj->name ?? 'EM', 0, 2));
                        $formattedDate = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-';
                        $shiftName = optional($attendance->attendanceTime)->name ?? 'General Shift';
                        $punchInStr = $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-';
                        $punchOutStr = $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-';
                        $grossStr = $attendance->gross_duration ?? 'N/A';
                        $netStr = $attendance->net_duration ?? 'N/A';
                        @endphp

                        @php
                            $flagList = [];
                            if ($attendance->is_late) $flagList[] = 'Late';
                            if ($attendance->is_early_out) $flagList[] = 'Early Out';
                            if ($attendance->missed_punch) $flagList[] = 'Missed';
                            if (($attendance->is_blocked ?? false) || ($attendance->is_punch_blocked ?? false)) $flagList[] = 'Blocked';
                            if ($attendance->is_admin_unlocked ?? false) $flagList[] = 'Unlocked';
                            $flagsExportStr = count($flagList) > 0 ? implode(', ', $flagList) : 'Clear';
                        @endphp

                        <tr data-sno="{{ $sNo }}"
                            data-emp-name="{{ $empUser->name ?? 'N/A' }}"
                            data-emp-code="{{ $empObj->employee_code ?? 'EMP' }}"
                            data-date="{{ $formattedDate }}"
                            data-mode="{{ $modeCode }}"
                            data-shift="{{ $shiftName }}"
                            data-punch-in="{{ $punchInStr }}"
                            data-punch-out="{{ $punchOutStr }}"
                            data-gross="{{ $grossStr }}"
                            data-net="{{ $netStr }}"
                            data-status="{{ $statusName }}"
                            data-flags="{{ $flagsExportStr }}">
                            <td class="text-center font-weight-bold text-muted" style="width: 50px;">
                                {{ $sNo }}
                            </td>
                            <td>
                                <div class="att-emp">
                                    {{-- <div class="att-avatar">
                                        @if(!empty($avatarUrl))
                                            <img src="{{ asset($avatarUrl) }}" class="att-avatar-img" alt="{{ $empUser->name ?? 'User' }}" onerror="this.onerror=null;this.parentElement.innerHTML='{{ $nameInitials }}';">
                                        @else
                                            {{ $nameInitials }}
                                        @endif
                                    </div> --}}
                                    <div style="min-width: 0;">
                                        <div class="att-emp-name" title="{{ $empUser->name ?? 'N/A' }}">{{ $empUser->name ?? 'N/A' }}</div>
                                        <div class="att-emp-code">{{ $empObj->employee_code ?? 'EMP' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="font-weight-bold text-dark">{{ $formattedDate }}</span>
                            </td>

                            <td>
                                <span class="mode-badge {{ $modeClass }}">{{ $modeCode }}</span>
                            </td>

                            <td>
                                <span class="text-secondary font-weight-bold">{{ $shiftName }}</span>
                            </td>

                            <td>
                                <span class="font-weight-bold text-dark">{{ $punchInStr }}</span>
                            </td>

                            <td>
                                <span class="font-weight-bold text-dark">{{ $punchOutStr }}</span>
                            </td>

                            <td>
                                <span class="text-muted font-weight-bold">{{ $grossStr }}</span>
                            </td>

                            <td>
                                <span class="text-dark font-weight-bolder">{{ $netStr }}</span>
                            </td>

                            <td>
                                <span class="att-badge {{ $statusBadgeClass }}">
                                    {{ $statusName }}
                                </span>
                            </td>

                            <td>
                                @if($attendance->is_late)
                                    <span class="flag flag-late">Late</span>
                                @endif

                                @if($attendance->is_early_out)
                                    <span class="flag flag-early">Early Out</span>
                                @endif

                                @if($attendance->missed_punch)
                                    <span class="flag flag-missed">Missed</span>
                                @endif

                                @if(($attendance->is_blocked ?? false) || ($attendance->is_punch_blocked ?? false))
                                    <span class="flag flag-blocked">Blocked</span>
                                @endif

                                @if(($attendance->is_admin_unlocked ?? false))
                                    <span class="flag flag-clear border">Unlocked</span>
                                @endif

                                @if(empty($flagList))
                                    <span class="flag flag-clear">Clear</span>
                                @endif
                            </td>

                            <td class="text-right pr-3">
                                <div class="att-action-wrap dropdown">
                                    <button type="button" class="action-dot" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                        <button type="button" class="dropdown-item font-weight-bold" data-toggle="modal" data-target="#viewModal{{ $attendance->id }}">
                                            <i class="fas fa-eye text-info"></i> View Details
                                        </button>
                                        @if($canManageAttendance ?? false)
                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $attendance->id }}">
                                            <i class="fas fa-edit text-primary"></i> Edit Attendance
                                        </button>
                                        @endif
                                        {{-- 
                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#unlockModal{{ $attendance->id }}">
                                            <i class="fas fa-unlock-alt text-warning"></i> Unlock / Clear
                                        </button>
                                        --}}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- VENDOR ORBO PAGINATION -->
            @if($attendances instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="px-3 pb-3">
                    {{ $attendances->links('vendor.pagination.orbo') }}
                </div>
            @endif
        </div>

        @foreach($attendances as $attendance)
        @include('hrms.attendance.partials.view-modal', ['attendance' => $attendance])
        @if($canManageAttendance ?? false)
        @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
        @endif
        {{-- @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance]) --}}
        @endforeach

    </div>
</div>

@endsection

@section('_script')
<script>
    $(document).ready(function() {
        // Month filter custom range toggle
        $('#daily_month_select').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#daily_custom_dates_wrap').slideDown(180);
            } else {
                $('#daily_custom_dates_wrap').slideUp(180);
                $('#daily_from_date').val('');
                $('#daily_to_date').val('');
            }
        });

        // Custom Export Builder with clean separated columns
        function buildDailyExportData(data) {
            data.header = ['S.No', 'Employee Code', 'Employee Name', 'Date', 'Mode', 'Shift', 'Punch In', 'Punch Out', 'Gross Work', 'Net Work', 'Status', 'Flags'];
            const rows = [];
            $('#attendanceRecordsTable tbody tr').each(function() {
                const $tr = $(this);
                if ($tr.find('td').length < 11 || $tr.find('.dataTables_empty').length) return;
                const sNo = $tr.attr('data-sno') || $tr.find('td:eq(0)').text().trim() || '-';
                const empCode = $tr.attr('data-emp-code') || $tr.find('.att-emp-code').text().trim() || '-';
                const empName = $tr.attr('data-emp-name') || $tr.find('.att-emp-name').text().trim() || '-';
                const date = $tr.attr('data-date') || $tr.find('td:eq(2)').text().trim() || '-';
                const mode = $tr.attr('data-mode') || $tr.find('td:eq(3)').text().trim() || '-';
                const shift = $tr.attr('data-shift') || $tr.find('td:eq(4)').text().trim() || '-';
                const punchIn = $tr.attr('data-punch-in') || $tr.find('td:eq(5)').text().trim() || '-';
                const punchOut = $tr.attr('data-punch-out') || $tr.find('td:eq(6)').text().trim() || '-';
                const gross = $tr.attr('data-gross') || $tr.find('td:eq(7)').text().trim() || '-';
                const net = $tr.attr('data-net') || $tr.find('td:eq(8)').text().trim() || '-';
                const status = $tr.attr('data-status') || $tr.find('td:eq(9) .att-badge').text().trim() || '-';
                const flags = $tr.attr('data-flags') || $tr.find('td:eq(10)').text().replace(/\s+/g, ' ').trim() || 'Clear';

                rows.push([sNo, empCode, empName, date, mode, shift, punchIn, punchOut, gross, net, status, flags]);
            });
            data.body = rows;
        }

        // Dedicated UTF-8 CSV Generator
        function triggerCsvExport(buildDataFn, defaultFilename) {
            const data = {};
            buildDataFn(data);
            const filename = defaultFilename.endsWith('.csv') ? defaultFilename : (defaultFilename + '.csv');
            const csvRows = [];
            if (data.header && data.header.length) {
                csvRows.push(data.header.map(h => '"' + String(h).replace(/"/g, '""') + '"').join(','));
            }
            if (data.body && data.body.length) {
                data.body.forEach(row => {
                    csvRows.push(row.map(cell => '"' + String(cell || '').replace(/"/g, '""') + '"').join(','));
                });
            }
            const csvContent = "\uFEFF" + csvRows.join("\r\n");
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        var table = $('#attendanceRecordsTable').DataTable({
            paging: false,
            info: false,
            ordering: true,
            scrollX: true,
            responsive: false,
            autoWidth: false,
            columnDefs: [
                { targets: 0, width: "50px", className: "text-center" },
                { targets: 11, orderable: false, className: "text-right" }
            ],
            dom: "<'row'<'col-12'tr>>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-success"></i> CSV',
                    className: 'leave-export-btn',
                    action: function(e, dt, button, config) {
                        triggerCsvExport(buildDailyExportData, 'Daily Attendance Records.csv');
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'leave-export-btn',
                    title: 'Daily Attendance Records',
                    customizeData: function(data) {
                        buildDailyExportData(data);
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'leave-export-btn',
                    title: 'Daily Attendance Records',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    customize: function (doc) {
                        const data = {};
                        buildDailyExportData(data);
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 9;
                        doc.styles.tableHeader.fillColor = '#4B00E8';
                        if (doc.content && doc.content[1] && doc.content[1].table) {
                            doc.content[1].table.body = [
                                data.header.map(h => ({ text: h, style: 'tableHeader' })),
                                ...data.body.map(r => r.map(c => ({ text: c })))
                            ];
                            doc.content[1].table.widths = Array(data.header.length + 1).join('*').split('');
                        }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'leave-export-btn',
                    exportOptions: { columns: ':not(.no-export)' }
                }
            ],
            language: {
                emptyTable: 'No records found.',
                zeroRecords: 'No matching records found.'
            }
        });

        // Attach buttons to custom toolbar
        table.buttons().container().appendTo('#recordsExportButtons');

        // Custom entries per page length select linking to backend pagination
        var currentPerPage = "{{ request('attendance_per_page', request('per_page', 25)) }}";
        var lengthSelect = $(
            '<div class="dataTables_length d-flex align-items-center gap-2">' +
                '<label class="mb-0 font-weight-bold text-muted mr-2" style="font-size:13px;">Show</label>' +
                '<select class="table-per-page-select" style="width:75px;">' +
                    '<option value="10"' + (currentPerPage == 10 ? ' selected' : '') + '>10</option>' +
                    '<option value="25"' + (currentPerPage == 25 ? ' selected' : '') + '>25</option>' +
                    '<option value="50"' + (currentPerPage == 50 ? ' selected' : '') + '>50</option>' +
                    '<option value="100"' + (currentPerPage == 100 ? ' selected' : '') + '>100</option>' +
                '</select>' +
                '<label class="mb-0 font-weight-bold text-muted ml-2" style="font-size:13px;">entries</label>' +
            '</div>'
        );

        $('#recordsLengthBox').append(lengthSelect);

        if ($.fn.select2) {
            var $perPageSelect = lengthSelect.find('select');
            $perPageSelect.select2({
                minimumResultsForSearch: Infinity,
                width: '75px',
                dropdownCssClass: 'select2-dropdown-per-page',
                containerCssClass: 'select2-container--per-page'
            });

            $perPageSelect.on('change', function() {
                var val = $(this).val();
                var url = new URL(window.location.href);
                url.searchParams.set('attendance_per_page', val);
                url.searchParams.set('per_page', val);
                url.searchParams.delete('attendance_page');
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        } else {
            lengthSelect.find('select').on('change', function() {
                var val = $(this).val();
                var url = new URL(window.location.href);
                url.searchParams.set('attendance_per_page', val);
                url.searchParams.set('per_page', val);
                url.searchParams.delete('attendance_page');
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        }
    });
</script>
@endsection
