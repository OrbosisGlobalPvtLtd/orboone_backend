@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Blocked / Unlock Requests')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
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
        overflow-x: hidden !important;
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
        border-radius: 30px !important;
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
        cursor: pointer;
        transition: all 0.2s ease;
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
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    }

    .att-btn-glass:hover,
    .att-hero-actions .att-btn:hover {
        background: rgba(255, 255, 255, 0.32) !important;
        border-color: rgba(255, 255, 255, 0.65) !important;
        color: #ffffff !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
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
        border-radius: 18px !important;
        box-shadow: var(--orb-shadow);
        overflow: hidden !important;
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

    .att-head-badges {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        align-items: center;
    }

    .att-total-pill {
        border: 1px solid var(--orb-border);
        background: #F8FAFC;
        color: var(--orb-text);
        border-radius: 10px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 850;
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
        color: var(--orb-primary);
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
        height: 42px;
        border-radius: 12px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        font-weight: 700;
        padding: 0 12px;
        box-shadow: none !important;
        background: #fff;
    }

    .att-filter-grid .form-control:focus {
        border-color: var(--orb-primary);
    }

    .att-table-toolbar {
        padding: 10px 18px;
        background: #F8FAFC;
        border-bottom: 1px solid #EAECF0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .att-table-wrap {
        padding: 0 !important;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .att-table {
        width: 100% !important;
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
        padding: 11px 12px !important;
        border-top: none !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .att-table tbody td {
        background: #fff;
        border-bottom: 1px solid #F2F4F7 !important;
        padding: 10px 12px !important;
        vertical-align: middle !important;
        font-size: 12.5px;
        color: var(--orb-text);
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF !important;
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
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px;
    }

    .att-emp-code {
        font-size: 11px;
        color: var(--orb-muted);
        font-weight: 700;
        margin-top: 2px;
    }

    .att-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge-present { background: #dcfce7; color: #166534; }
    .badge-absent { background: #fee2e2; color: #991b1b; }
    .badge-half_day { background: #fef3c7; color: #92400e; }
    .badge-leave { background: #dbeafe; color: #1e40af; }
    .badge-week_off { background: #f1f5f9; color: #475569; }
    .badge-holiday { background: #ede9fe; color: #5b21b6; }
    .badge-punch_blocked { background: #ffe4e6; color: #be123c; }
    .badge-missed_punch { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .badge-default { background: #f1f5f9; color: #475569; }
    .badge-unlocked { background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; }

    .att-action-btn {
        border-radius: 10px;
        padding: 6px 12px;
        font-size: 11.5px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 0;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    /* UNIFIED DATATABLES TOOLBAR STYLES */
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

    .select2-dropdown-per-page {
        border-radius: 12px !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: 0 10px 30px rgba(16, 24, 40, 0.1) !important;
        padding: 6px !important;
        min-width: 75px !important;
        z-index: 1060 !important;
    }

    .select2-dropdown-per-page .select2-results__option {
        padding: 6px 12px !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-align: center !important;
        margin-bottom: 2px !important;
    }

    .select2-dropdown-per-page .select2-results__option--selected,
    .select2-dropdown-per-page .select2-results__option--highlighted {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        color: #fff !important;
    }

    .select2-container--per-page .select2-selection--single {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid #D0D5DD !important;
        background: #fff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
    }

    .select2-container--per-page .select2-selection__rendered {
        font-weight: 800 !important;
        color: #1D2939 !important;
        font-size: 13px !important;
        padding-left: 10px !important;
        padding-right: 22px !important;
        line-height: 36px !important;
    }

    .select2-container--per-page .select2-selection__arrow {
        height: 36px !important;
        right: 6px !important;
    }

    .dataTables_length select {
        border-radius: 10px !important;
        padding: 4px 22px 4px 8px !important;
        height: 38px !important;
        border: 1px solid #E7EAF3 !important;
    }

    @media(max-width:1300px) {
        .att-metric-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .att-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width:992px) {
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
            align-items: flex-start;
        }
        .att-head-badges {
            justify-content: flex-start;
        }
        .att-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .att-table td.dataTables_empty {
        text-align: center !important;
        padding: 40px 20px !important;
        color: var(--orb-muted, #64748B) !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        background: transparent !important;
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
        </div>

        @if(session('status'))
        <div class="alert alert-success border-0 shadow-sm" style="border-radius:14px; font-weight:750;">{{ session('status') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm" style="border-radius:14px; font-weight:750;">{{ session('error') }}</div>
        @endif

        <div class="att-metric-grid">
            <div class="att-metric" style="--metric-color:var(--orb-primary);--metric-soft:#F4F2FF;">
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
            </div>

            <div class="att-filter-panel">
                @php
                    $hasRangeParam = request()->filled('from_date') || request()->filled('to_date');
                    $monthParam = request('month_year');
                    $hasSingleDate = request()->filled('date');

                    $isCustomRange = ($monthParam === 'all' || $monthParam === 'custom' || ($hasRangeParam && !$hasSingleDate));

                    if ($isCustomRange) {
                        $effectiveMonthYear = 'all';
                    } elseif ($hasSingleDate) {
                        try {
                            $effectiveMonthYear = \Carbon\Carbon::parse(request('date'))->format('Y-m');
                        } catch (\Throwable $e) {
                            $effectiveMonthYear = ($selectedMonthYear && $selectedMonthYear !== 'all') ? $selectedMonthYear : \Carbon\Carbon::now()->format('Y-m');
                        }
                    } else {
                        $effectiveMonthYear = ($selectedMonthYear && $selectedMonthYear !== 'all') ? $selectedMonthYear : \Carbon\Carbon::now()->format('Y-m');
                    }
                @endphp

                <form method="GET" action="{{ route('attendances.pending-approval') }}" id="pendingFilterForm">
                    <div class="att-filter-grid">
                        <div class="att-filter-group">
                            <label><i class="fas fa-user text-primary mr-1"></i> Employee</label>
                            <select name="employee_id" class="form-control select2-searchable">
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

                        <div class="att-filter-group">
                            <label><i class="fas fa-calendar-alt text-primary mr-1"></i> Month</label>
                            <select name="month_year" class="form-control select2-searchable" id="monthYearSelect">
                                <option value="all" {{ ($isCustomRange || $effectiveMonthYear === 'all') ? 'selected' : '' }}>Custom Date Range</option>
                                @php
                                    $cursorDate = \Carbon\Carbon::now();
                                    for ($m = 0; $m < 12; $m++) {
                                        $val = $cursorDate->format('Y-m');
                                        $label = $cursorDate->format('F Y');
                                        if ($m === 0) {
                                            $label .= ' (Current)';
                                        }
                                        $isSelected = (!$isCustomRange && $effectiveMonthYear === $val);
                                        echo "<option value=\"{$val}\" " . ($isSelected ? 'selected' : '') . ">{$label}</option>";
                                        $cursorDate->subMonth();
                                    }
                                @endphp
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label><i class="fas fa-calendar-day text-primary mr-1"></i> Date</label>
                            <x-form.date-picker name="date" id="pending_date" :value="request('date')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-group custom-date-filter {{ $isCustomRange ? '' : 'd-none' }}">
                            <label>From Date</label>
                            <x-form.date-picker name="from_date" id="pending_from_date" :value="request('from_date')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-group custom-date-filter {{ $isCustomRange ? '' : 'd-none' }}">
                            <label>To Date</label>
                            <x-form.date-picker name="to_date" id="pending_to_date" :value="request('to_date')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-group">
                            <label>Status Type</label>
                            <select name="flag" class="form-control select2-searchable">
                                <option value="">All Status</option>
                                <option value="blocked" {{ request('flag') == 'blocked' ? 'selected' : '' }}>Punch Blocked</option>
                                <option value="missed" {{ request('flag') == 'missed' ? 'selected' : '' }}>Missed Punch</option>
                                <option value="unlocked" {{ request('flag') == 'unlocked' ? 'selected' : '' }}>Unlocked</option>
                                <option value="manual_punch_in" {{ request('flag') == 'manual_punch_in' ? 'selected' : '' }}>Manual Punch-In Approved</option>
                            </select>
                        </div>

                        <div class="att-filter-group d-flex align-items-end" style="gap: 8px;">
                            <button type="submit" class="btn text-white font-weight-bold shadow-sm" style="height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px;">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('attendances.pending-approval') }}" class="btn btn-light border font-weight-bold" style="height: 42px; width: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Action Toolbar with Show Entries & Export Tools -->
            <div class="orb-table-tools-bar eo-toolbar">
                <div class="orb-table-length-box eo-toolbar-left">
                    <div class="dataTables_length d-flex align-items-center">
                        <label class="eo-entries-label mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size: 13px; gap: 8px;">
                            <span>Show</span>
                            <select id="recordsPerPageSelect" name="per_page" class="table-per-page-select orb-per-page-select" style="width: 75px;">
                                @foreach([10, 25, 50, 100, 250] as $size)
                                    <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                                <option value="all" {{ (request('per_page') === 'all' || request('per_page') == -1) ? 'selected' : '' }}>All</option>
                            </select>
                            <span>entries</span>
                        </label>
                    </div>
                </div>
                <div class="orb-table-export-buttons eo-toolbar-right">
                    <x-ui.export-buttons table="pendingDataTable" />
                </div>
            </div>

            <div class="att-table-wrap">
                <table class="att-table table" id="pendingDataTable">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">S.No.</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th class="text-center">Attendance Status</th>
                            <th class="text-center">Blocked Status</th>
                            <th>Blocked Reason</th>
                            {{-- <th class="text-right no-export" style="width: 140px;">Action</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        @php
                        $isUnlocked = (bool) ($attendance->is_admin_unlocked || $attendance->unlocked_at || ($attendance->attendance_status ?? '') === 'unlocked');
                        $attDateStr = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->toDateString() : null;
                        $todayStr = $today ?? \Carbon\Carbon::now('Asia/Kolkata')->toDateString();
                        $isPastDate = $attDateStr && $attDateStr < $todayStr;

                        $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                        $rawStatus = strtolower($attendance->attendance_status ?? '');
                        if (empty($rawStatus) || $rawStatus === 'default') {
                            $rawStatus = $typeCode;
                        }

                        // Determine actual daily Attendance Status (Present, Absent, Half Day, etc.)
                        if ($isUnlocked && ($rawStatus === 'punch_blocked' || $rawStatus === 'unlocked' || $rawStatus === 'awaiting_punch_in' || empty($rawStatus))) {
                            $statusCode = $attendance->punch_in_time ? 'present' : 'unlocked';
                            $statusLabel = $attendance->punch_in_time ? 'Present' : 'Unlocked';
                        } elseif (!$isUnlocked && $isPastDate) {
                            $statusCode = 'absent';
                            $statusLabel = '🔴 ABSENT';
                        } elseif (empty($rawStatus) || $rawStatus === 'unlocked' || $rawStatus === 'present' || ($isUnlocked && empty($attendance->attendance_status))) {
                            $statusCode = 'present';
                            $statusLabel = 'Present';
                        } elseif ($rawStatus === 'absent') {
                            $statusCode = 'absent';
                            $statusLabel = '🔴 ABSENT';
                        } elseif ($rawStatus === 'lwp') {
                            $statusCode = 'lwp';
                            $statusLabel = '🔴 LWP';
                        } elseif ($rawStatus === 'half_day') {
                            $statusCode = 'half_day';
                            $statusLabel = 'Half Day';
                        } elseif ($rawStatus === 'missed_punch') {
                            $statusCode = 'missed_punch';
                            $statusLabel = 'Missed Punch';
                        } elseif ($rawStatus === 'leave') {
                            $statusCode = 'leave';
                            $statusLabel = 'Leave';
                        } elseif ($rawStatus === 'holiday') {
                            $statusCode = 'holiday';
                            $statusLabel = 'Holiday';
                        } elseif ($rawStatus === 'week_off') {
                            $statusCode = 'week_off';
                            $statusLabel = 'Week Off';
                        } elseif ($rawStatus === 'punch_blocked') {
                            $statusCode = 'punch_blocked';
                            $statusLabel = 'Punch Blocked';
                        } else {
                            $statusCode = $rawStatus;
                            $statusLabel = optional($attendance->attendanceType)->name ?? ucwords(str_replace('_', ' ', $rawStatus));
                        }
                        $attDate = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-';

                        $displayReason = $attendance->block_reason ?? $attendance->auto_block_reason ?? $attendance->blocked_reason;
                        if (empty($displayReason)) {
                            if ($isUnlocked) {
                                $displayReason = 'Unlocked by HR';
                            } elseif ($statusCode === 'missed_punch' || $attendance->missed_punch) {
                                $displayReason = 'Missed Punch (Out Time Pending)';
                            } elseif ($statusCode === 'punch_blocked') {
                                $displayReason = 'Punch-in window has closed for today\'s shift.';
                            } else {
                                $displayReason = 'Punch-in window has closed for today\'s shift.';
                            }
                        }

                        $sNo = $attendances instanceof \Illuminate\Pagination\LengthAwarePaginator
                            ? ($attendances->firstItem() ? $attendances->firstItem() + $loop->index : $loop->iteration)
                            : $loop->iteration;
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $sNo }}</td>
                            <td>
                                <div class="att-emp">
                                    @php
                                    $passportPhotoUrl = resolveEmployeePassportPhoto($attendance->employee ?? $attendance);
                                    $employeeName = optional($attendance->user)->name ?? 'Employee';
                                    $employeeCode = optional($attendance->employee)->employee_code ?? '';
                                    $deptName = optional(optional($attendance->employee)->department)->name ?? '';
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
                                        <div class="att-emp-name" title="{{ $employeeName }}">
                                            {{ $employeeName }}
                                        </div>
                                        <div class="att-emp-code" title="{{ $employeeCode }}">
                                            {{ $employeeCode }}{{ $deptName ? " • {$deptName}" : '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><strong>{{ $attDate }}</strong></td>
                            <td class="text-center">
                                <span class="att-badge badge-{{ $statusCode }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="att-badge badge-{{ $isUnlocked ? 'unlocked' : 'punch_blocked' }}">
                                    {{ $isUnlocked ? '🔓 UNLOCKED' : 'PUNCH BLOCKED' }}
                                </span>
                                @if($isUnlocked && $attendance->unlocked_at)
                                <div class="small text-muted mt-1" style="font-size: 10px;">
                                    <i class="fas fa-check-circle text-success"></i> Unlocked {{ \Carbon\Carbon::parse($attendance->unlocked_at)->format('d M h:i A') }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 260px; font-size: 12px; font-weight: 600;" title="{{ $displayReason }}">
                                    <span class="{{ $isUnlocked ? 'text-success' : ($statusCode === 'missed_punch' ? 'text-warning' : 'text-danger') }}">
                                        {{ $displayReason }}
                                    </span>
                                </div>
                            </td>
                            {{-- <td class="text-right no-export">
                                <button type="button" class="att-action-btn att-action-view" data-toggle="modal" data-target="#viewModal{{ $attendance->id }}" title="View Record Details">
                                    <i class="fas fa-eye"></i> View Record
                                </button>
                            </td> --}}
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendances instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="border-top bg-white" style="border-bottom-left-radius:18px; border-bottom-right-radius:18px;">
                {{ $attendances->appends(request()->query())->links('vendor.pagination.orbo') }}
            </div>
            @endif

            @foreach($attendances as $attendance)
            @include('hrms.attendance.partials.view-modal', ['attendance' => $attendance])
            @endforeach
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
    $(function() {
        if (typeof initSearchableSelects === 'function') {
            initSearchableSelects();
        }

        if (typeof $.fn.select2 !== 'undefined') {
            $('#recordsPerPageSelect').select2({
                minimumResultsForSearch: Infinity,
                width: '75px',
                dropdownCssClass: 'select2-dropdown-per-page',
                containerCssClass: 'select2-container--per-page'
            });
        }

        $('#recordsPerPageSelect').on('change', function() {
            let val = $(this).val();
            let url = new URL(window.location.href);
            if (val === '-1') val = 'all';
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });

        const monthYearSelect = document.getElementById('monthYearSelect');
        const customDateFilters = document.querySelectorAll('.custom-date-filter');
        const dateInput = document.getElementById('pending_date');
        const fromDateInput = document.getElementById('pending_from_date');
        const toDateInput = document.getElementById('pending_to_date');

        function toggleCustomDateFilters(show) {
            customDateFilters.forEach(function(el) {
                if (show) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });
        }

        if (monthYearSelect) {
            $(monthYearSelect).on('change', function() {
                const val = $(this).val();
                if (val === 'all' || val === 'custom') {
                    toggleCustomDateFilters(true);
                } else {
                    toggleCustomDateFilters(false);
                    if (fromDateInput) fromDateInput.value = '';
                    if (toDateInput) toDateInput.value = '';
                }
            });
        }

        if (dateInput) {
            dateInput.addEventListener('change', function() {
                if (this.value) {
                    if (fromDateInput) fromDateInput.value = '';
                    if (toDateInput) toDateInput.value = '';
                }
            });
        }

        if (fromDateInput) {
            fromDateInput.addEventListener('change', function() {
                if (this.value && dateInput) {
                    dateInput.value = '';
                }
            });
        }

        if (toDateInput) {
            toDateInput.addEventListener('change', function() {
                if (this.value && dateInput) {
                    dateInput.value = '';
                }
            });
        }

        const exportFormatBody = function(data, row, column, node) {
            let rawText = $(node).text().replace(/\s+/g, ' ').trim();
            rawText = rawText.replace(/[\u{1F300}-\u{1F9FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]|🔴|🔓/gu, '').trim();

            if (column === 1) { // Employee column
                let name = $(node).find('.att-emp-name').text().trim();
                let dept = $(node).find('.att-emp-code').text().trim();
                return dept ? name + ' (' + dept + ')' : name;
            } else if (column === 4) { // Blocked Status column
                if (rawText.includes('UNLOCKED')) {
                    return 'UNLOCKED';
                }
                return rawText;
            }
            return rawText;
        };

        $.fn.dataTable.ext.errMode = 'none';

        if ($.fn.DataTable.isDataTable('#pendingDataTable')) {
            $('#pendingDataTable').DataTable().destroy();
        }

        const table = $('#pendingDataTable').DataTable({
            destroy: true,
            ordering: false,
            responsive: false,
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            info: false,
            searching: false,
            dom: 'rt',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-success"></i> CSV',
                    className: 'leave-export-btn',
                    title: '{{ branding_name() }} Pending Unlock & HR Approvals',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: { body: exportFormatBody }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'leave-export-btn',
                    title: '{{ branding_name() }} Pending Unlock & HR Approvals',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: { body: exportFormatBody }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'leave-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: '',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: { body: exportFormatBody }
                    },
                    customize: function(doc) {
                        doc.pageMargins = [25, 35, 25, 35];
                        doc.defaultStyle.fontSize = 8.5;
                        doc.styles.tableHeader.fontSize = 9.5;
                        doc.styles.tableHeader.bold = true;
                        doc.styles.tableHeader.fillColor = '#1E293B';
                        doc.styles.tableHeader.color = '#FFFFFF';
                        doc.styles.tableHeader.alignment = 'left';

                        doc.content.unshift({
                            text: '{{ branding_name() }} - Pending Unlock & HR Approvals',
                            fontSize: 15,
                            bold: true,
                            color: '#1E293B',
                            alignment: 'center',
                            margin: [0, 0, 0, 15]
                        });

                        let tableNode = doc.content.find(c => c.table);
                        if (tableNode) {
                            tableNode.table.widths = ['5%', '28%', '12%', '14%', '15%', '26%'];
                            tableNode.layout = {
                                hLineWidth: function(i, node) { return i === 0 || i === node.table.body.length ? 1.5 : 0.5; },
                                vLineWidth: function() { return 0; },
                                hLineColor: function() { return '#CBD5E1'; },
                                paddingLeft: function() { return 6; },
                                paddingRight: function() { return 6; },
                                paddingTop: function() { return 6; },
                                paddingBottom: function() { return 6; },
                                fillColor: function(rowIndex) {
                                    return (rowIndex === 0) ? '#1E293B' : (rowIndex % 2 === 0 ? '#F8FAFC' : null);
                                }
                            };
                        }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'leave-export-btn',
                    title: '',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: { body: exportFormatBody }
                    },
                    customize: function(win) {
                        $(win.document.body).css('font-family', 'Inter, system-ui, -apple-system, sans-serif').css('padding', '20px');
                        $(win.document.body).prepend(
                            '<div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1E293B; padding-bottom: 12px;">' +
                                '<h2 style="margin: 0; color: #1E293B; font-weight: 800; font-size: 20px;">{{ branding_name() }}</h2>' +
                                '<h4 style="margin: 4px 0 0; color: #475569; font-weight: 600; font-size: 14px;">Pending Unlock & HR Approvals Report</h4>' +
                                '<p style="margin: 4px 0 0; color: #94A3B8; font-size: 11px;">Report Generated: ' + new Date().toLocaleString() + '</p>' +
                            '</div>'
                        );
                    }
                }
            ],
            language: {
                emptyTable: 'No pending approvals found.',
                zeroRecords: 'No pending approvals found.'
            }
        });

        setTimeout(function() {
            $('#pendingDataTable').DataTable().columns.adjust();
        }, 250);
    });
</script>
@endsection