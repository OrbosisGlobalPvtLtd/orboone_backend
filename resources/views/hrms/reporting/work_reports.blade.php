@extends('layouts.panel', ['active' => 'reporting_work_reports'])

@section('page_title', 'Reporting Employee Work Reports')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F6F7FB;
    --orb-card: #FFFFFF;
    --orb-border: #E7EAF3;
    --orb-text: #101828;
    --orb-muted: #667085;
    --orb-soft: #F4F2FF;
    --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
}

.rep-page {
    min-height: calc(100vh - 90px);
    background: var(--orb-bg);
    padding: 14px 16px 36px;
    font-family: 'Outfit', sans-serif;
    color: var(--orb-text);
}

.rep-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

/* Signature Hero Header Banner */
.rep-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px;
    padding: 22px 28px;
    margin-bottom: 20px;
    box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    flex-wrap: wrap;
}

.rep-hero::before {
    content: "";
    position: absolute;
    right: -60px;
    top: -80px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.rep-hero-kicker {
    font-size: 11.5px;
    font-weight: 850;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .92;
    margin-bottom: 6px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.rep-hero-title {
    font-size: 26px;
    font-weight: 900;
    margin: 0;
    line-height: 1.15;
    color: #ffffff;
}

.rep-hero-subtitle {
    font-size: 13.5px;
    font-weight: 500;
    margin-top: 6px;
    opacity: .92;
    max-width: 800px;
}

.rep-hero-right {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    z-index: 1;
}

.rep-btn-glass {
    background: rgba(255, 255, 255, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.45) !important;
    color: #ffffff !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    border-radius: 999px !important;
    padding: 8px 18px !important;
    font-size: 12.5px !important;
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

.rep-btn-glass:hover {
    background: rgba(255, 255, 255, 0.32) !important;
    border-color: rgba(255, 255, 255, 0.65) !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

/* 5 KPI Stat Summary Cards Grid */
.team-metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

@media (min-width: 1400px) {
    .team-metric-grid {
        grid-template-columns: repeat(5, 1fr);
    }
}

.team-metric-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    padding: 14px 14px 10px;
    box-shadow: 0 10px 24px rgba(16, 24, 40, .055);
    position: relative;
    overflow: hidden;
    min-height: 92px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.team-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(16, 24, 40, .09);
}

.team-metric-card:after {
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

.team-metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    position: relative;
    z-index: 1;
}

.team-metric-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: var(--metric-soft, #F4F2FF);
    color: var(--metric-color, var(--orb-primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.team-metric-value {
    font-size: 24px;
    font-weight: 900;
    color: #101828;
    line-height: 1;
    font-feature-settings: "tnum";
}

.team-metric-label {
    font-size: 11px;
    font-weight: 850;
    color: #475467;
    text-transform: uppercase;
    margin-top: 12px;
    position: relative;
    z-index: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: 0.03em;
}

.team-metric-line {
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--metric-color, var(--orb-primary)), transparent);
    margin-top: 8px;
}

/* Main Unified Table Card */
.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.rep-section-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--orb-border);
    background: linear-gradient(180deg, #fff, #FAFBFF);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.rep-section-title {
    font-size: 16px;
    font-weight: 850;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rep-section-icon {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    background: var(--orb-soft);
    color: var(--orb-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

/* Horizontal Theme Filter Bar */
.rep-filter-bar {
    padding: 16px 18px 8px;
    background: #FFFFFF;
    border-bottom: none;
}

.rep-filter-row {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 12px;
    width: 100%;
}

.rep-filter-label {
    display: block;
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    color: #475467;
    margin-bottom: 6px;
    letter-spacing: 0.04em;
    line-height: 1;
}

.rep-filter-col-search {
    flex: 1 1 240px;
    min-width: 200px;
}

.rep-search-input-wrap {
    position: relative;
    width: 100%;
}

.rep-search-input-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
    font-size: 13px;
    pointer-events: none;
    z-index: 2;
}

.rep-search-input {
    height: 38px;
    padding-left: 34px !important;
    padding-right: 12px;
    border-radius: 10px;
    border: 1px solid #CBD5E1;
    font-size: 12.5px;
    font-weight: 600;
    background: #fff;
    width: 100%;
    transition: all 0.2s ease;
}

.rep-search-input:focus {
    border-color: var(--orb-primary);
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    outline: none;
}

.rep-filter-col-month {
    flex: 1 1 170px;
    min-width: 150px;
}

.rep-filter-col-mode {
    flex: 1 1 135px;
    min-width: 120px;
}

.rep-filter-col-date {
    flex: 1 1 175px;
    min-width: 155px;
}

.rep-date-control,
.rep-date-control.orbo-date-picker-display,
.orbo-date-picker,
.orbo-date-picker-display,
.flatpickr-input.orbo-date-picker-display,
input[data-date-picker] {
    height: 38px !important;
    border-radius: 10px !important;
    border: 1px solid #CBD5E1 !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    background-color: #ffffff !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234B00E8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px 16px !important;
    width: 100% !important;
    padding: 0 36px 0 12px !important;
    transition: all 0.2s ease !important;
    color: #101828 !important;
    box-sizing: border-box !important;
    cursor: pointer !important;
}

.rep-date-control:focus,
.rep-date-control.orbo-date-picker-display:focus,
.orbo-date-picker:focus,
.orbo-date-picker-display:focus,
.flatpickr-input.orbo-date-picker-display:focus,
input[data-date-picker]:focus {
    border-color: var(--orb-primary) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
    outline: none !important;
}

.rep-filter-col-emp {
    flex: 1 1 210px;
    min-width: 180px;
}

.rep-filter-col-proj {
    flex: 1 1 180px;
    min-width: 150px;
}

@media (max-width: 768px) {
    .rep-filter-col-month,
    .rep-filter-col-mode,
    .rep-filter-col-date,
    .rep-filter-col-emp,
    .rep-filter-col-proj,
    .rep-filter-col-search {
        width: 100% !important;
        flex: 1 1 100% !important;
        max-width: 100% !important;
    }
}

/* Select2 Standard Skin */
.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid #CBD5E1 !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 10px !important;
    background-color: #fff !important;
    transition: all 0.2s ease;
}

.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--orb-primary) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 12.5px !important;
    font-weight: 650 !important;
    color: #101828 !important;
    padding-left: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
}

.select2-dropdown {
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 12px !important;
    box-shadow: 0 12px 30px rgba(16, 24, 40, 0.12) !important;
    font-size: 12.5px !important;
    font-weight: 550 !important;
    overflow: hidden !important;
    z-index: 99999 !important;
}

.select2-search--dropdown .select2-search__field {
    border-radius: 8px !important;
    border: 1px solid #E2E8F0 !important;
    padding: 6px 10px !important;
    font-size: 12px !important;
}

.select2-results__option--highlighted[aria-selected] {
    background-color: var(--orb-primary, #4B00E8) !important;
    color: #ffffff !important;
}

.rep-filter-actions-right {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    flex-wrap: nowrap;
    align-self: flex-end;
}

.rep-search-btn {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border: none;
    color: #ffffff;
    height: 38px;
    padding: 0 18px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 750;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(75, 0, 232, 0.22);
    transition: all 0.2s ease;
    cursor: pointer;
    white-space: nowrap;
}

.rep-search-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(75, 0, 232, 0.32);
    color: #ffffff;
}

.rep-reset-btn {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    color: #475467;
    height: 38px;
    padding: 0 14px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none !important;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.rep-reset-btn:hover {
    background: #F8FAFC;
    color: #0F172A;
    border-color: #94A3B8;
}

/* Table Action Tools Bar (Show Entries + Export) */
.orb-table-tools-bar {
    padding: 6px 18px 14px;
    background: #FFFFFF;
    border-bottom: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.orb-table-length-box {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 700;
    color: #64748B;
}

.table-per-page-select {
    height: 34px !important;
    border-radius: 8px !important;
    padding: 0 8px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    border: 1px solid #CBD5E1 !important;
    color: #0F172A !important;
    background: #fff !important;
    outline: none !important;
    cursor: pointer;
}

.table-per-page-select:focus {
    border-color: var(--orb-primary) !important;
}

.orbo-export-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 10px;
    padding: 3px;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
}

.orbo-export-btn {
    border: none;
    background: transparent;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 750;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.orbo-export-btn:hover {
    background: #F1F5F9;
    color: #0F172A;
}

.orbo-export-btn .icon-csv { color: #0284C7; }
.orbo-export-btn .icon-excel { color: #16A34A; }
.orbo-export-btn .icon-pdf { color: #DC2626; }
.orbo-export-btn .icon-print { color: #475569; }

/* Table Section */
.rep-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.rep-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 12.5px;
    margin-bottom: 0;
    min-width: 1100px;
}

.rep-table thead th {
    background: #F8FAFC;
    color: #475569;
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 12px 14px;
    border-top: 1px solid var(--orb-border);
    border-bottom: 1px solid var(--orb-border);
    white-space: nowrap;
    vertical-align: middle;
}

.rep-table tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid #F1F5F9;
    color: #0F172A;
    vertical-align: middle;
    font-size: 12.5px;
}

.rep-table tbody tr:last-child td {
    border-bottom: none;
}

.rep-table tbody tr:hover {
    background: #FAFBFF;
}

/* Employee Details */
.emp-name-text {
    font-weight: 800;
    color: #0F172A;
    font-size: 13.5px;
    line-height: 1.25;
    display: inline-block;
    white-space: nowrap;
}

.emp-code-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 750;
    color: #64748B;
    background: #F1F5F9;
    padding: 2px 7px;
    border-radius: 6px;
    white-space: nowrap;
}

/* Scrollable Container Styling */
.rep-summary-box {
    max-height: 90px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #CBD5E1 transparent;
    padding-right: 4px;
}
.rep-summary-box::-webkit-scrollbar {
    width: 4px;
}
.rep-summary-box::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 4px;
}

/* Badge Pills */
.badge-premium-pill {
    padding: 3px 10px;
    border-radius: 50px;
    font-weight: 800;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    letter-spacing: 0.02em;
    white-space: nowrap;
}
.badge-wfo { background: #ECFDF3; color: #027A48; border: 1px solid #D1FADF; }
.badge-wfh { background: #EFF8FF; color: #175CD3; border: 1px solid #D1E9FF; }

.badge-gross-pill {
    background: #FEF7C3;
    color: #B54708;
    border: 1px solid #FEF08A;
    font-weight: 800;
    font-size: 11.5px;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}

.structured-task-item {
    font-size: 11.5px;
    line-height: 1.45;
    margin-bottom: 3px;
}

.btn-details-view {
    height: 32px;
    padding: 0 12px;
    border-radius: 8px;
    background: #F8FAFC;
    border: 1px solid #CBD5E1;
    color: #334155;
    font-size: 12px;
    font-weight: 750;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
    cursor: pointer;
    white-space: nowrap;
}

.btn-details-view:hover {
    background: var(--orb-soft);
    color: var(--orb-primary);
    border-color: rgba(75, 0, 232, 0.3);
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">

        <!-- 1. Header Hero Banner -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-file-signature"></i> Team Management &bull; Work Logs
                </div>
                <h3 class="rep-hero-title">Daily Work Reports</h3>
                <div class="rep-hero-subtitle">Daily work summaries submitted by your reporting employees upon punch-out.</div>
            </div>
            <div class="rep-hero-right">
                @if(Route::has('reporting.dashboard'))
                    <a href="{{ route('reporting.dashboard') }}" class="rep-btn-glass">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                @endif
                @if(Route::has('reporting.my_employees'))
                    <a href="{{ route('reporting.my_employees') }}" class="rep-btn-glass">
                        <i class="fas fa-users"></i> My Team
                    </a>
                @endif
            </div>
        </div>

        <!-- 2. 5 KPI Summary Stat Cards Grid -->
        <div class="team-metric-grid">
            <!-- Total Reports -->
            <div class="team-metric-card" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="team-metric-value text-primary">{{ $stats['total'] ?? $workReports->total() }}</div>
                </div>
                <div class="team-metric-label">Total Submissions</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- Today's Reports -->
            <div class="team-metric-card" style="--metric-color:#2563EB;--metric-soft:#EFF6FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="team-metric-value text-info">{{ $stats['today'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Today's Reports</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- WFO Submissions -->
            <div class="team-metric-card" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-building"></i></div>
                    <div class="team-metric-value text-success">{{ $stats['wfo'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">WFO Reports</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- WFH Submissions -->
            <div class="team-metric-card" style="--metric-color:#7E22CE;--metric-soft:#F3E8FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-laptop-house"></i></div>
                    <div class="team-metric-value" style="color: #7E22CE;">{{ $stats['wfh'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">WFH Reports</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- Total Gross Work -->
            <div class="team-metric-card" style="--metric-color:#D97706;--metric-soft:#FFFBEB;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-stopwatch"></i></div>
                    <div class="team-metric-value text-warning">{{ $stats['gross_hours'] ?? 0 }}h</div>
                </div>
                <div class="team-metric-label">Gross Work Hours</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        <!-- 3. Main Attached Table Container Card -->
        @php
            $todayDate = \Carbon\Carbon::today()->toDateString();
            $yesterdayDate = \Carbon\Carbon::yesterday()->toDateString();
            $currentDate = request('date');
            $currentFromDate = request('from_date');
            $currentToDate = request('to_date');
            $currentMode = request('work_mode');
            $currentEmpId = request('employee_id');
            $currentProjId = request('project_id');
            $currentSearch = request('search');
            
            $isCustomRange = (request('month') === 'custom' || (!empty($currentFromDate) || !empty($currentToDate)));
            $currentMonth = $isCustomRange ? 'custom' : request('month', $stats['selected_month'] ?? date('Y-m'));
            
            $hasActiveFilters = !empty($currentDate) || !empty($currentFromDate) || !empty($currentToDate) || !empty($currentMode) || (!empty($currentMonth) && $currentMonth !== date('Y-m')) || !empty($currentEmpId) || !empty($currentProjId) || !empty($currentSearch);
        @endphp

        <div class="rep-card">
            <!-- Attached Section Header -->
            <div class="rep-section-head">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="rep-section-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h4 class="rep-section-title">Team Work Reports Workbench</h4>
                        <small class="text-muted font-weight-bold" style="font-size: 11.5px;">Employee daily submissions, punch context & project tasks</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background: var(--orb-soft); color: var(--orb-primary); font-size: 12px; font-weight: 800; padding: 6px 12px; border-radius: 10px; border: 1px solid rgba(75, 0, 232, 0.15);">
                        <i class="fas fa-file-signature mr-1"></i> {{ $workReports->total() }} Total Submissions
                    </span>
                </div>
            </div>

            <!-- Attached Filter Bar Form -->
            <form method="GET" action="{{ route('reporting.work_reports') }}" id="workReportFilterForm">
                <input type="hidden" name="per_page" id="filterPerPageHidden" value="{{ request('per_page', 25) }}">

                <div class="rep-filter-bar">
                    <div class="rep-filter-row">
                        <!-- 1. Month -->
                        <div class="rep-filter-col-month">
                            <label class="rep-filter-label" for="filterMonthSelect">
                                <i class="far fa-calendar-alt text-muted mr-1"></i> Month
                            </label>
                            <select name="month" id="filterMonthSelect" class="select2-filter select2-searchable">
                                <option value="custom" {{ $currentMonth === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                                @foreach($months as $mKey => $mLabel)
                                    <option value="{{ $mKey }}" {{ ($currentMonth === $mKey) ? 'selected' : '' }}>
                                        {{ $mLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Custom From Date (visible when Custom is chosen) -->
                        <div id="customFromWrap" class="rep-filter-col-date" style="display: {{ $isCustomRange ? 'block' : 'none' }};">
                            <label class="rep-filter-label" for="filterFromDate">
                                <i class="fas fa-calendar-alt text-muted mr-1"></i> From Date
                            </label>
                            <x-form.date-picker name="from_date" id="filterFromDate" :value="$currentFromDate" placeholder="dd-mm-yyyy" class="rep-date-control" />
                        </div>

                        <!-- 3. Custom To Date (visible when Custom is chosen) -->
                        <div id="customToWrap" class="rep-filter-col-date" style="display: {{ $isCustomRange ? 'block' : 'none' }};">
                            <label class="rep-filter-label" for="filterToDate">
                                <i class="fas fa-calendar-alt text-muted mr-1"></i> To Date
                            </label>
                            <x-form.date-picker name="to_date" id="filterToDate" :value="$currentToDate" placeholder="dd-mm-yyyy" class="rep-date-control" />
                        </div>

                        <!-- 4. Single Work Date (Optional, visible when regular month is selected) -->
                        <div id="singleDateWrap" class="rep-filter-col-date" style="display: {{ $isCustomRange ? 'none' : 'block' }};">
                            <label class="rep-filter-label" for="filterDateInput">
                                <i class="fas fa-calendar-day text-muted mr-1"></i> Work Date
                            </label>
                            <x-form.date-picker name="date" id="filterDateInput" :value="$currentDate" placeholder="dd-mm-yyyy" class="rep-date-control" />
                        </div>

                        <!-- 5. Work Mode -->
                        <div class="rep-filter-col-mode">
                            <label class="rep-filter-label" for="filterWorkMode">
                                <i class="fas fa-building text-muted mr-1"></i> Work Mode
                            </label>
                            <select name="work_mode" id="filterWorkMode" class="select2-filter">
                                <option value="">All Modes</option>
                                <option value="wfo" {{ strtolower($currentMode) === 'wfo' ? 'selected' : '' }}>Office (WFO)</option>
                                <option value="wfh" {{ strtolower($currentMode) === 'wfh' ? 'selected' : '' }}>Remote (WFH)</option>
                            </select>
                        </div>

                        <!-- 6. Employee (Select2) -->
                        <div class="rep-filter-col-emp">
                            <label class="rep-filter-label" for="filterEmployeeSelect">
                                <i class="far fa-user text-muted mr-1"></i> Employee
                            </label>
                            <select name="employee_id" id="filterEmployeeSelect" class="select2-filter select2-searchable">
                                <option value="">All Employees ({{ $teamEmployees->count() }})</option>
                                @foreach($teamEmployees as $emp)
                                    <option value="{{ $emp->id }}" {{ $currentEmpId == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }} ({{ $emp->employee_code }}){{ $emp->designation ? ' • ' . $emp->designation->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 7. Project (Select2) -->
                        <div class="rep-filter-col-proj">
                            <label class="rep-filter-label" for="filterProjectSelect">
                                <i class="far fa-folder text-muted mr-1"></i> Project
                            </label>
                            <select name="project_id" id="filterProjectSelect" class="select2-filter select2-searchable">
                                <option value="">All Projects ({{ $teamProjects->count() }})</option>
                                @foreach($teamProjects as $prj)
                                    <option value="{{ $prj->id }}" {{ $currentProjId == $prj->id ? 'selected' : '' }}>
                                        {{ $prj->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 8. Search Keyword -->
                        <div class="rep-filter-col-search">
                            <label class="rep-filter-label">
                                <i class="fas fa-search text-muted mr-1"></i> Search
                            </label>
                            <div class="rep-search-input-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" id="filter-search" class="rep-search-input" value="{{ $currentSearch }}" placeholder="Search name, task, keyword...">
                            </div>
                        </div>

                        <!-- 9. Action Buttons -->
                        <div class="rep-filter-actions-right">
                            <button type="submit" class="rep-search-btn">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('reporting.work_reports') }}" class="rep-reset-btn" title="Reset Filters">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Attached Table Tools Bar: Show entries + Export Group -->
            <div class="orb-table-tools-bar">
                <div class="orb-table-length-box">
                    <span>Show</span>
                    <select id="recordsPerPageSelect" class="table-per-page-select">
                        <option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="orbo-export-group">
                    <button type="button" class="orbo-export-btn" id="btnExportCSV">
                        <i class="fas fa-file-csv icon-csv"></i> CSV
                    </button>
                    <button type="button" class="orbo-export-btn" id="btnExportExcel">
                        <i class="fas fa-file-excel icon-excel"></i> Excel
                    </button>
                    <button type="button" class="orbo-export-btn" id="btnExportPDF">
                        <i class="fas fa-file-pdf icon-pdf"></i> PDF
                    </button>
                    <button type="button" class="orbo-export-btn" id="btnPrint">
                        <i class="fas fa-print icon-print"></i> Print
                    </button>
                </div>
            </div>

            <!-- Attached Table Section -->
            <div class="rep-table-wrap">
                <table class="rep-table" id="reportingWorkReportsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px; min-width: 45px;">#</th>
                            <th style="min-width: 210px; width: 210px;">Employee</th>
                            <th style="min-width: 120px; width: 120px;">Date</th>
                            <th class="text-center" style="min-width: 85px; width: 85px;">Mode</th>
                            <th style="min-width: 125px; width: 125px;">Shift Context</th>
                            <th class="text-center" style="min-width: 110px; width: 110px;">Gross Work</th>
                            <th style="min-width: 280px;">Work Summary Description</th>
                            <th style="min-width: 220px;">Structured Tasks</th>
                            <th class="text-center" style="width: 100px; min-width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workReports as $report)
                            @php
                                $row = formatWorkReportRow($report);
                                $modeBadgeClass = $row['mode'] === 'WFH' ? 'badge-wfh' : 'badge-wfo';

                                $logPayload = [
                                    'id' => $report->id,
                                    'work_log_id' => $report->id,
                                    'employee_name' => $row['employee_name'],
                                    'employee_code' => $row['employee_code'],
                                    'passport_photo_url' => resolveEmployeePassportPhoto($report),
                                    'employee_initial' => resolveEmployeeInitials($report),
                                    'department' => $row['department'],
                                    'designation' => $row['designation'],
                                    'work_date' => $row['date'],
                                    'shift_name' => $row['shift_context'],
                                    'attendance_status' => ($report->attendance_status ?? 'present') === 'absent' && ($report->is_lwp ?? false) ? '🔴 ABSENT' : ($report->attendance_status ?? 'present'),
                                    'is_lwp' => (bool) ($report->is_lwp ?? false),
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
                            <td class="text-center font-weight-bold text-muted" style="font-size: 11.5px;">
                                {{ $loop->iteration + ($workReports->currentPage() - 1) * $workReports->perPage() }}
                            </td>

                            <td style="min-width: 210px;">
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span class="emp-name-text">{{ $row['employee_name'] }}</span>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 5px;">
                                        <span class="emp-code-badge"><i class="fas fa-id-badge"></i> {{ $row['employee_code'] }}</span>
                                        @if(!empty($row['designation']))
                                            <span class="text-muted font-weight-semibold" style="font-size: 11px;">&bull; {{ $row['designation'] }}</span>
                                        @elseif(!empty($row['department']))
                                            <span class="text-muted font-weight-semibold" style="font-size: 11px;">&bull; {{ $row['department'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td style="white-space: nowrap; min-width: 120px;">
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $row['date'] }}</div>
                                @if($row['day_name'])
                                    <div class="small text-muted font-weight-semibold" style="font-size: 11px;">{{ $row['day_name'] }}</div>
                                @endif
                            </td>

                            <td class="text-center" style="white-space: nowrap; min-width: 85px;">
                                <span class="badge-premium-pill {{ $modeBadgeClass }}">
                                    @if($row['mode'] === 'WFH')
                                        <i class="fas fa-laptop-house"></i> WFH
                                    @else
                                        <i class="fas fa-building"></i> WFO
                                    @endif
                                </span>
                            </td>

                            <td style="white-space: nowrap; min-width: 125px;">
                                <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                    {{ $row['shift_context'] ?: 'General Shift' }}
                                </div>
                            </td>

                            <td class="text-center" style="white-space: nowrap; min-width: 110px;">
                                <div class="badge-gross-pill">
                                    <i class="fas fa-stopwatch mr-1"></i> {{ $row['gross_work'] ?: '0h 0m' }}
                                </div>
                            </td>

                            <td>
                                <div class="rep-summary-box">
                                    @forelse($row['summary_paragraphs'] as $para)
                                        <p class="mb-1" style="line-height: 1.45; color: #1E293B; font-size: 12.5px; font-weight: 500;">
                                            {{ $para }}
                                        </p>
                                    @empty
                                        <span class="text-muted font-weight-normal" style="font-size: 11.5px;">—</span>
                                    @endforelse
                                </div>
                            </td>

                            <td>
                                <div class="rep-summary-box">
                                    @forelse($row['structured_tasks'] as $tItem)
                                        <div class="structured-task-item" style="line-height: 1.4; margin-bottom: 3px; font-size: 11.5px; font-weight: 600;">
                                            <span class="font-weight-bold {{ $tItem['done'] ? 'text-success' : 'text-warning' }}">{{ $tItem['done'] ? '✓' : '•' }}</span>
                                            <span class="text-dark">{{ $tItem['text'] }}</span>
                                        </div>
                                    @empty
                                        <span class="text-muted font-weight-normal" style="font-size: 11.5px;">—</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="text-center" style="white-space: nowrap; width: 100px; min-width: 100px;">
                                <button type="button" class="btn-details-view" data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" onclick="parseAndOpenWorkReport(this)">
                                    <i class="fas fa-eye text-primary"></i> Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-file-signature fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                <h5 class="font-weight-bold text-dark">No Daily Work Reports Found</h5>
                                <p class="small mb-0">No work report submissions match the selected date or filters.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Attached Card Footer for Server Pagination -->
            @if($workReports->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                    <div class="text-muted font-weight-bold small">
                        Showing {{ $workReports->firstItem() ?? 0 }} to {{ $workReports->lastItem() ?? 0 }} of {{ $workReports->total() }} reports
                    </div>
                    <div>
                        {{ $workReports->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('hrms.attendance.partials.work-report-modal')
@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
    window.applyQuickDate = function(dateVal) {
        var dateInput = document.getElementById('filterDateInput');
        if (dateInput) {
            dateInput.value = dateVal;
            document.getElementById('workReportFilterForm').submit();
        }
    };

    $(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-filter').select2({
                minimumResultsForSearch: 6,
                width: '100%'
            });
            $('.select2-searchable').select2({
                width: '100%'
            });
        }

        // Handle Month Dropdown Change (Custom Date Range toggle)
        $('#filterMonthSelect').on('change', function() {
            var val = $(this).val();
            if (val === 'custom') {
                $('#customFromWrap, #customToWrap').slideDown(150, function() {
                    if (typeof window.initOrboDatePickers === 'function') {
                        window.initOrboDatePickers();
                    }
                });
                $('#singleDateWrap').slideUp(150);
                $('#filterDateInput').val('');
            } else {
                $('#customFromWrap, #customToWrap').slideUp(150);
                $('#singleDateWrap').slideDown(150);
                $('#filterFromDate, #filterToDate').val('');
                document.getElementById('workReportFilterForm').submit();
            }
        });

        // Handle Entries per page select
        $('#recordsPerPageSelect').on('change', function() {
            var perPage = $(this).val();
            $('#filterPerPageHidden').val(perPage);
            $('#workReportFilterForm').submit();
        });

        // Helper to construct full export URL with all current filters
        function getExportUrl(format) {
            var formEl = document.getElementById('workReportFilterForm');
            var formData = new FormData(formEl);
            var params = new URLSearchParams();
            
            for (var pair of formData.entries()) {
                if (pair[1] !== null && pair[1] !== '') {
                    params.append(pair[0], pair[1]);
                }
            }
            params.set('export', format);
            return "{{ route('reporting.work_reports') }}?" + params.toString();
        }

        // 1. Export CSV (Full Dataset)
        $('#btnExportCSV').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            window.location.href = getExportUrl('csv');
        });

        // 2. Export Excel (Full Dataset)
        $('#btnExportExcel').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            window.location.href = getExportUrl('excel');
        });

        // 3. Export PDF (Full Dataset)
        $('#btnExportPDF').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var $btn = $(this);
            var origHtml = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin"></i> PDF...').prop('disabled', true);

            $.getJSON(getExportUrl('json'), function(res) {
                $btn.html(origHtml).prop('disabled', false);
                if (!res.success || !res.data || res.data.length === 0) {
                    alert('No records found to export.');
                    return;
                }

                if (typeof pdfMake === 'undefined') {
                    alert('PDF generation library is loading. Please try again.');
                    return;
                }

                var body = [
                    [
                        { text: '#', style: 'tableHeader', alignment: 'center' },
                        { text: 'EMPLOYEE', style: 'tableHeader' },
                        { text: 'DATE', style: 'tableHeader' },
                        { text: 'MODE', style: 'tableHeader', alignment: 'center' },
                        { text: 'SHIFT CONTEXT', style: 'tableHeader' },
                        { text: 'GROSS WORK', style: 'tableHeader', alignment: 'center' },
                        { text: 'WORK SUMMARY DESCRIPTION', style: 'tableHeader' },
                        { text: 'STRUCTURED TASKS', style: 'tableHeader' }
                    ]
                ];

                res.data.forEach(function(item) {
                    var empText = item.employee_name + (item.employee_code ? '\n' + item.employee_code : '') + (item.designation ? '\n' + item.designation : '');
                    var dateText = item.date + (item.day_name ? '\n' + item.day_name : '');
                    var tasksText = (item.structured_tasks && item.structured_tasks.length > 0) ? item.structured_tasks.join('\n') : '—';

                    body.push([
                        { text: String(item.sr_no), alignment: 'center', style: 'tableCell' },
                        { text: empText, style: 'tableCell' },
                        { text: dateText, style: 'tableCell' },
                        { text: item.mode, alignment: 'center', style: 'tableCell' },
                        { text: item.shift_context || 'General Shift', style: 'tableCell' },
                        { text: item.gross_work || '0h 0m', alignment: 'center', style: 'tableCell' },
                        { text: item.work_summary || '—', style: 'tableCell' },
                        { text: tasksText, style: 'tableCell' }
                    ]);
                });

                var docDefinition = {
                    pageOrientation: 'landscape',
                    pageSize: 'A4',
                    pageMargins: [18, 18, 18, 18],
                    content: [
                        { text: 'Daily Work Reports - Full Report', style: 'header' },
                        { text: 'Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString() + ' | Total Records: ' + res.total, style: 'subHeader' },
                        {
                            table: {
                                headerRows: 1,
                                widths: ['4%', '15%', '10%', '8%', '11%', '10%', '24%', '18%'],
                                body: body
                            },
                            layout: {
                                fillColor: function (rowIndex) {
                                    return (rowIndex === 0) ? '#4B00E8' : (rowIndex % 2 === 0 ? '#FAFBFF' : null);
                                },
                                hLineWidth: function () { return 0.5; },
                                vLineWidth: function () { return 0.5; },
                                hLineColor: function () { return '#CBD5E1'; },
                                vLineColor: function () { return '#CBD5E1'; }
                            }
                        }
                    ],
                    styles: {
                        header: {
                            fontSize: 14,
                            bold: true,
                            color: '#101828',
                            margin: [0, 0, 0, 3]
                        },
                        subHeader: {
                            fontSize: 8.5,
                            color: '#64748B',
                            margin: [0, 0, 0, 8]
                        },
                        tableHeader: {
                            bold: true,
                            fontSize: 8,
                            color: '#FFFFFF',
                            fillColor: '#4B00E8'
                        },
                        tableCell: {
                            fontSize: 7.5,
                            color: '#1E293B'
                        }
                    }
                };

                pdfMake.createPdf(docDefinition).download("Daily_Work_Reports_All_" + new Date().toISOString().slice(0,10) + ".pdf");
            }).fail(function() {
                $btn.html(origHtml).prop('disabled', false);
                alert('Failed to fetch full reports data for PDF export.');
            });
        });

        // 4. Print Table (Full Dataset via iframe to prevent duplicate dialogs)
        $('#btnPrint').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var $btn = $(this);
            var origHtml = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Print...').prop('disabled', true);

            $.getJSON(getExportUrl('json'), function(res) {
                $btn.html(origHtml).prop('disabled', false);
                if (!res.success || !res.data || res.data.length === 0) {
                    alert('No records found to print.');
                    return;
                }

                var rowsHtml = '';
                res.data.forEach(function(item) {
                    var tasksHtml = item.structured_tasks && item.structured_tasks.length > 0
                        ? item.structured_tasks.map(function(t) { return '<div style="margin-bottom:2px;">' + t + '</div>'; }).join('')
                        : '—';

                    rowsHtml += '<tr>' +
                        '<td style="text-align:center;">' + item.sr_no + '</td>' +
                        '<td><strong>' + item.employee_name + '</strong><br><small style="color:#64748B;">' + item.employee_code + (item.designation ? ' • ' + item.designation : '') + '</small></td>' +
                        '<td>' + item.date + '<br><small style="color:#64748B;">' + item.day_name + '</small></td>' +
                        '<td style="text-align:center;">' + item.mode + '</td>' +
                        '<td>' + (item.shift_context || 'General Shift') + '</td>' +
                        '<td style="text-align:center;">' + (item.gross_work || '0h 0m') + '</td>' +
                        '<td>' + (item.work_summary ? item.work_summary.replace(/\n/g, '<br>') : '—') + '</td>' +
                        '<td>' + tasksHtml + '</td>' +
                        '</tr>';
                });

                var iframe = document.getElementById('repPrintFrame');
                if (!iframe) {
                    iframe = document.createElement('iframe');
                    iframe.id = 'repPrintFrame';
                    iframe.style.position = 'fixed';
                    iframe.style.right = '0';
                    iframe.style.bottom = '0';
                    iframe.style.width = '0';
                    iframe.style.height = '0';
                    iframe.style.border = '0';
                    document.body.appendChild(iframe);
                }

                var doc = iframe.contentWindow.document;
                doc.open();
                doc.write('<!DOCTYPE html><html><head><title>Daily Work Reports</title>');
                doc.write('<style>');
                doc.write('@page { size: landscape A4; margin: 8mm; }');
                doc.write('body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; padding: 12px; color: #101828; margin: 0; }');
                doc.write('h2 { font-size: 16px; margin: 0 0 3px 0; color: #101828; }');
                doc.write('p { font-size: 10px; color: #64748B; margin: 0 0 10px 0; }');
                doc.write('table { width: 100%; border-collapse: collapse; font-size: 10px; }');
                doc.write('th, td { border: 1px solid #CBD5E1; padding: 6px 7px; text-align: left; vertical-align: top; }');
                doc.write('th { background-color: #4B00E8; color: #ffffff; font-weight: 700; text-transform: uppercase; font-size: 9px; }');
                doc.write('tr:nth-child(even) { background-color: #FAFBFF; }');
                doc.write('</style></head><body>');
                doc.write('<h2>Daily Work Reports - Full Dataset</h2>');
                doc.write('<p>Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString() + ' | Total Records: ' + res.total + '</p>');
                doc.write('<table><thead><tr><th style="width:28px;text-align:center;">#</th><th>Employee</th><th>Date</th><th style="text-align:center;">Mode</th><th>Shift Context</th><th style="text-align:center;">Gross Work</th><th>Work Summary Description</th><th>Structured Tasks</th></tr></thead><tbody>' + rowsHtml + '</tbody></table>');
                doc.write('</body></html>');
                doc.close();

                setTimeout(function() {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                }, 250);
            }).fail(function() {
                $btn.html(origHtml).prop('disabled', false);
                alert('Failed to fetch full reports data for Print.');
            });
        });
    });
</script>
@endsection
