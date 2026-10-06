@extends('layouts.panel', ['active' => 'team_my_team'])

@section('page_title', 'My Team Management')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
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
    padding: 14px 16px 36px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
    font-family: 'Outfit', sans-serif;
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

.rep-hero:before {
    content: "";
    position: absolute;
    right: -60px;
    top: -80px;
    width: 320px;
    height: 320px;
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
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    line-height: 1.15;
    color: #ffffff;
}

.rep-hero-subtitle {
    font-size: 13px;
    font-weight: 500;
    margin-top: 5px;
    opacity: .92;
    max-width: 800px;
}

.rep-btn-glass {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.38) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
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

/* Metric KPI Cards Grid (7 Cards) */
.team-metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

@media (min-width: 1400px) {
    .team-metric-grid {
        grid-template-columns: repeat(7, 1fr);
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

/* Main Table Container Card */
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

.rep-section-title i {
    color: var(--orb-primary);
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
    padding: 14px 18px;
    background: #FFFFFF;
    border-bottom: 1px solid var(--orb-border);
}

.rep-filter-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    width: 100%;
}

.rep-filter-col-search {
    position: relative;
    flex: 1 1 220px;
    min-width: 200px;
    max-width: 320px;
}

.rep-filter-col-search i {
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

.rep-filter-col {
    width: 140px;
    flex: 0 0 140px;
}

.rep-filter-col-sm {
    width: 125px;
    flex: 0 0 125px;
}

.rep-filter-col-lg {
    width: 155px;
    flex: 0 0 155px;
}

@media (max-width: 768px) {
    .rep-filter-col,
    .rep-filter-col-sm,
    .rep-filter-col-lg,
    .rep-filter-col-search {
        width: 100% !important;
        flex: 1 1 100% !important;
        max-width: 100% !important;
    }
}

/* Select2 Standard Filter Skin */
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
    z-index: 9999 !important;
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

.rep-more-btn {
    background: #F8FAFC !important;
    color: #475467 !important;
    border: 1px solid #CBD5E1 !important;
    border-radius: 10px !important;
    font-weight: 750 !important;
    font-size: 12px !important;
    padding: 0 12px !important;
    height: 38px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    transition: all 0.2s ease !important;
    cursor: pointer;
    white-space: nowrap;
}

.rep-more-btn:hover,
.rep-more-btn.active {
    background: #EEF2FF !important;
    color: var(--orb-primary) !important;
    border-color: #C7D2FE !important;
}

.rep-filter-actions-right {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .rep-filter-actions-right {
        margin-left: 0;
        width: 100%;
    }
}

/* Brand Theme Search Button */
.rep-search-btn {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 800 !important;
    font-size: 12.5px !important;
    padding: 0 16px !important;
    height: 38px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2) !important;
    transition: all 0.2s ease !important;
    cursor: pointer;
    white-space: nowrap;
}

.rep-search-btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3) !important;
    color: #ffffff !important;
    opacity: 0.95;
}

.rep-reset-btn {
    background: #F8FAFC !important;
    color: #475467 !important;
    border: 1px solid #CBD5E1 !important;
    border-radius: 10px !important;
    font-weight: 750 !important;
    font-size: 12.5px !important;
    padding: 0 14px !important;
    height: 38px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap;
}

.rep-reset-btn:hover {
    background: #F1F5F9 !important;
    color: var(--orb-primary) !important;
    border-color: #94A3B8 !important;
}

/* More Filters Collapsible Box */
.more-filters-collapse {
    background: #F8FAFC;
    padding: 14px 18px;
    border-top: 1px solid #E2E8F0;
    display: none;
}

.more-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.more-filter-item label {
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    color: #64748B;
    margin-bottom: 4px;
    display: block;
    letter-spacing: .03em;
}

/* Compact Select2 for Table Length Entries */
.select2-container--per-page.select2-container--default .select2-selection--single {
    min-height: 36px !important;
    height: 36px !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 10px !important;
    padding: 0 6px !important;
    width: 75px !important;
    background-color: #fff !important;
    display: flex !important;
    align-items: center !important;
}

.select2-container--per-page.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px !important;
    font-size: 12.5px !important;
    font-weight: 750 !important;
    color: var(--orb-text, #101828) !important;
    padding-left: 4px !important;
}

.select2-container--per-page.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px !important;
    right: 6px !important;
}

.select2-dropdown-per-page {
    min-width: 75px !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    z-index: 99999 !important;
}

.select2-dropdown-per-page .select2-results__option {
    padding: 6px 10px !important;
    text-align: center !important;
}

.select2-dropdown-per-page .select2-results__option--highlighted[aria-selected] {
    background-color: var(--orb-primary, #4B00E8) !important;
    color: #ffffff !important;
}

/* Table Tools Bar & Export Buttons */
.orb-table-tools-bar {
    padding: 10px 18px;
    background: #F8FAFC;
    border-bottom: 1px solid #EAECF0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.orb-table-length-box {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 750;
    color: #475467;
}

.orbo-export-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.orbo-export-btn {
    height: 34px !important;
    border-radius: 9px !important;
    padding: 5px 12px !important;
    font-size: 12px !important;
    font-weight: 750 !important;
    color: #344054 !important;
    background: #ffffff !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: 0 1px 2px rgba(16,24,40,0.04) !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    cursor: pointer;
}

.orbo-export-btn:hover {
    background: #F8FAFC !important;
    color: var(--orb-primary) !important;
    border-color: #CBD5E1 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06) !important;
}

.orbo-export-btn .icon-csv { color: #0284C7; }
.orbo-export-btn .icon-excel { color: #16A34A; }
.orbo-export-btn .icon-pdf { color: #DC2626; }
.orbo-export-btn .icon-print { color: var(--orb-primary); }

/* Table Design */
.rep-table-wrap {
    padding: 0 !important;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #CBD5E1 #F1F5F9;
}

.rep-table-wrap::-webkit-scrollbar {
    height: 6px;
}

.rep-table-wrap::-webkit-scrollbar-track {
    background: #F1F5F9;
    border-radius: 999px;
}

.rep-table-wrap::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 999px;
}

.rep-table {
    width: 100% !important;
    min-width: 980px;
    border-collapse: separate !important;
    border-spacing: 0;
    margin: 0 !important;
}

.rep-table thead th {
    background: #F8FAFC !important;
    color: #475467 !important;
    font-size: 11px !important;
    font-weight: 850 !important;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 12px 14px !important;
    border-top: none !important;
    border-bottom: 1px solid #EAECF0 !important;
    white-space: nowrap;
    vertical-align: middle !important;
}

.rep-table tbody td {
    background: #fff;
    border-bottom: 1px solid #F2F4F7 !important;
    padding: 11px 14px !important;
    vertical-align: middle !important;
    white-space: nowrap;
    font-size: 12.5px;
    color: #1E293B;
}

.rep-table tbody tr:hover td {
    background: #FCFAFF !important;
}

/* Employee Column Styling */
.emp-avatar-circle {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(75, 0, 232, 0.12), rgba(255, 82, 82, 0.12));
    color: var(--orb-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 850;
    font-size: 13.5px;
    border: 1px solid rgba(75, 0, 232, 0.15);
    flex-shrink: 0;
}

.emp-info-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}

.emp-name-link {
    font-size: 13px;
    font-weight: 800;
    color: #0F172A;
    text-decoration: none !important;
    display: block;
    line-height: 1.25;
    transition: color 0.15s ease;
}

.emp-name-link:hover {
    color: var(--orb-primary);
}

.emp-code-badge {
    font-size: 11px;
    font-weight: 750;
    color: #64748B;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

/* 3-Dot Action Button */
.btn-action-dots {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #F8FAFC;
    color: #475569;
    border: 1px solid #CBD5E1;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-action-dots:hover,
.btn-action-dots:focus {
    background: #EEF2FF;
    color: var(--orb-primary);
    border-color: #C7D2FE;
}

.dropdown-menu-action {
    min-width: 175px;
    border-radius: 12px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.14);
    border: 1px solid #E2E8F0;
    padding: 6px;
    z-index: 1050;
}

.dropdown-menu-action .dropdown-item {
    font-size: 12px;
    font-weight: 700;
    padding: 7px 12px;
    border-radius: 8px;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.15s ease;
}

.dropdown-menu-action .dropdown-item:hover {
    background: #F4F2FF;
    color: var(--orb-primary);
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">

        <!-- Signature Hero Header Banner -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-users-cog"></i> Team Management &bull; Supervision Scope
                </div>
                <h1 class="rep-hero-title">My Team Management</h1>
                <div class="rep-hero-subtitle">
                    Unified operational workspace for team members under your supervision (Project Team & Reporting Scope).
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(Route::has('reporting.dashboard'))
                    <a href="{{ route('reporting.dashboard') }}" class="rep-btn-glass">
                        <i class="fas fa-th-large"></i> Team Dashboard
                    </a>
                @endif
                @if(Route::has('hrms.attendance.records.team'))
                    <a href="{{ route('hrms.attendance.records.team') }}" class="rep-btn-glass">
                        <i class="fas fa-calendar-check"></i> Team Attendance
                    </a>
                @endif
            </div>
        </div>

        <!-- Rich Summary KPI Cards Grid (7 Cards) -->
        <div class="team-metric-grid">
            <!-- 1. Total Team Members -->
            <div class="team-metric-card" style="--metric-color: #4B00E8; --metric-soft: #EEF2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="team-metric-value">{{ $summaryStats['total'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Total Members</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 2. Reporting Team -->
            <div class="team-metric-card" style="--metric-color: #0284C7; --metric-soft: #E0F2FE;">
                <div class="team-metric-top">
                    <div class="team-metric-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="team-metric-value">{{ $summaryStats['reporting_team'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Reporting Team</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 3. Project Team -->
            <div class="team-metric-card" style="--metric-color: #16A34A; --metric-soft: #DCFCE7;">
                <div class="team-metric-top">
                    <div class="team-metric-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="team-metric-value">{{ $summaryStats['project_team'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Project Team</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 4. Both Scope -->
            <div class="team-metric-card" style="--metric-color: #7E22CE; --metric-soft: #F3E8FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="team-metric-value">{{ $summaryStats['both'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Both Scope</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 5. Paid Employees -->
            <div class="team-metric-card" style="--metric-color: #059669; --metric-soft: #ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="team-metric-value">{{ $summaryStats['paid'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Paid Employees</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 6. Unpaid Employees -->
            <div class="team-metric-card" style="--metric-color: #DC2626; --metric-soft: #FEE2E2;">
                <div class="team-metric-top">
                    <div class="team-metric-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="team-metric-value">{{ $summaryStats['unpaid'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Unpaid Employees</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 7. Interns -->
            <div class="team-metric-card" style="--metric-color: #D97706; --metric-soft: #FEF3C7;">
                <div class="team-metric-top">
                    <div class="team-metric-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="team-metric-value">{{ $summaryStats['interns'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Interns</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="rep-card">
            <!-- Section Header -->
            <div class="rep-section-head">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="rep-section-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h4 class="rep-section-title">My Team Members</h4>
                        <small class="text-muted font-weight-bold" style="font-size: 11.5px;">All team members under your supervision scope</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background: var(--orb-soft); color: var(--orb-primary); font-size: 12px; font-weight: 800; padding: 6px 12px; border-radius: 10px; border: 1px solid rgba(75, 0, 232, 0.15);">
                        <i class="fas fa-user-check mr-1"></i> {{ $employees->total() }} Total Matching
                    </span>
                </div>
            </div>

            <!-- Horizontal Live Filter Form -->
            <form method="GET" action="{{ route('reporting.my_employees') }}" id="myTeamFilterForm">
                <input type="hidden" name="per_page" id="filterPerPageHidden" value="{{ request('per_page', 25) }}">

                <div class="rep-filter-bar">
                    <div class="rep-filter-row">
                        <!-- 1. Search Keyword -->
                        <div class="rep-filter-col-search">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" id="filter-search-keyword" class="rep-search-input" placeholder="Search Employee (Name, ID...)" value="{{ request('search') }}">
                        </div>

                        <!-- 2. Team Source Dropdown -->
                        <div class="rep-filter-col">
                            <select name="team_source" id="filter-team-source" class="select2-filter">
                                <option value="">Team Source</option>
                                <option value="Reporting Team" {{ request('team_source') === 'Reporting Team' ? 'selected' : '' }}>Reporting</option>
                                <option value="Project Team" {{ request('team_source') === 'Project Team' ? 'selected' : '' }}>Project</option>
                                <option value="Both" {{ request('team_source') === 'Both' ? 'selected' : '' }}>Both Scope</option>
                            </select>
                        </div>

                        <!-- 3. Employment Stage Dropdown -->
                        <div class="rep-filter-col">
                            <select name="employment" id="filter-employment" class="select2-filter">
                                <option value="">Employment</option>
                                <option value="permanent" {{ request('employment') === 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="probation" {{ request('employment') === 'probation' ? 'selected' : '' }}>Probation</option>
                                <option value="internship" {{ request('employment') === 'internship' ? 'selected' : '' }}>Internship</option>
                                <option value="contract" {{ request('employment') === 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="freelance" {{ request('employment') === 'freelance' ? 'selected' : '' }}>Freelance</option>
                            </select>
                        </div>

                        <!-- 4. Pay Status Dropdown -->
                        <div class="rep-filter-col-sm">
                            <select name="pay_status" id="filter-pay-status" class="select2-filter">
                                <option value="">Pay Status</option>
                                <option value="paid" {{ request('pay_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="unpaid" {{ request('pay_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>

                        <!-- 5. Department Dropdown -->
                        <div class="rep-filter-col-lg">
                            <select name="department_id" id="filter-department" class="select2-filter">
                                <option value="">Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 6. Project Dropdown -->
                        <div class="rep-filter-col-lg">
                            <select name="project_id" id="filter-project" class="select2-filter">
                                <option value="">Project</option>
                                @foreach($projects as $prj)
                                    <option value="{{ $prj->id }}" {{ request('project_id') == $prj->id ? 'selected' : '' }}>{{ $prj->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 7. More Filters Toggle Button -->
                        <button type="button" class="rep-more-btn" id="btnToggleMoreFilters">
                            <i class="fas fa-sliders-h text-muted"></i> More Filters
                        </button>

                        <!-- 8. Right Action Buttons -->
                        <div class="rep-filter-actions-right">
                            <button type="submit" class="rep-search-btn">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('reporting.my_employees') }}" class="rep-reset-btn" title="Reset Filters">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Collapsible More Filters Section -->
                <div class="more-filters-collapse" id="moreFiltersCollapse" style="{{ (request('designation_id') || request('manager_id')) ? 'display: block;' : '' }}">
                    <div class="more-filter-grid">
                        <div class="more-filter-item">
                            <label><i class="fas fa-id-card-alt mr-1"></i> Designation</label>
                            <select name="designation_id" id="filter-designation" class="select2-filter w-100">
                                <option value="">All Designations</option>
                                @foreach($designations as $desig)
                                    <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="more-filter-item">
                            <label><i class="fas fa-user-shield mr-1"></i> Reporting Manager</label>
                            <select name="manager_id" id="filter-manager" class="select2-filter w-100">
                                <option value="">All Managers</option>
                                @foreach($reportingManagers as $mgr)
                                    <option value="{{ $mgr->id }}" {{ request('manager_id') == $mgr->id ? 'selected' : '' }}>{{ $mgr->display_name ?? optional($mgr->user)->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Table Tools Bar: Show [ 25 ] entries + Export Buttons -->
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

            <!-- Table Section -->
            <div class="rep-table-wrap">
                <table class="rep-table" id="myTeamTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Employee</th>
                            <th>Organization</th>
                            <th class="text-center">Employment</th>
                            <th>Reporting</th>
                            <th class="text-center">Team</th>
                            <th>Projects & Teams</th>
                            <th class="text-center" style="width: 60px;">⋮</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            @php
                                $projectAssignments = $emp->project_assignments_list ?? [];
                                $reportingManager = $emp->reportingManager ?? null;

                                $displayName = $emp->display_name ?? (optional($emp->user ?? null)->name ?? 'Employee');
                                $empCode = $emp->employee_code ?? 'N/A';
                                $initials = strtoupper(substr($displayName, 0, 2));

                                // Employment Stage & Type Determination
                                $stageRaw = strtolower($emp->employee_stage ?? '');
                                $typeRaw = strtolower($emp->employment_type ?? '');
                                $isPaidIntern = $emp->is_paid_intern ?? null;
                                $isIntern = ($stageRaw === 'internship' || $typeRaw === 'intern');

                                $stageLabel = 'Permanent';
                                $stageBadgeCss = 'background: #DEF7EC; color: #03543F; border: 1px solid #84E1BC;';
                                $stageIcon = 'fas fa-user-check';

                                $payLabel = 'Paid';
                                $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                $payIcon = 'fas fa-rupee-sign';

                                if ($isIntern) {
                                    $stageLabel = 'Internship';
                                    $stageBadgeCss = 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;';
                                    $stageIcon = 'fas fa-user-graduate';

                                    if ($isPaidIntern === 1 || $isPaidIntern === true) {
                                        $payLabel = 'Paid';
                                        $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                        $payIcon = 'fas fa-rupee-sign';
                                    } else {
                                        $payLabel = 'Unpaid';
                                        $payBadgeCss = 'background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;';
                                        $payIcon = 'fas fa-hand-holding-usd';
                                    }
                                } elseif ($stageRaw === 'probation' || ($emp->probation_status && in_array($emp->probation_status, ['pending', 'ongoing']))) {
                                    $stageLabel = 'Probation';
                                    $stageBadgeCss = 'background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;';
                                    $stageIcon = 'fas fa-user-clock';
                                    $payLabel = 'Paid';
                                    $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                } elseif ($stageRaw === 'contract' || $typeRaw === 'contract') {
                                    $stageLabel = 'Contract';
                                    $stageBadgeCss = 'background: #E0F2FE; color: #075985; border: 1px solid #7DD3FC;';
                                    $stageIcon = 'fas fa-file-contract';
                                    $payLabel = 'Paid';
                                    $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                } elseif ($stageRaw === 'freelance' || $typeRaw === 'freelancer') {
                                    $stageLabel = 'Freelance';
                                    $stageBadgeCss = 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;';
                                    $stageIcon = 'fas fa-laptop-code';
                                    $payLabel = 'Paid';
                                    $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                } else {
                                    $stageLabel = ucfirst($stageRaw ?: ($typeRaw ?: 'Permanent'));
                                    $stageBadgeCss = 'background: #DEF7EC; color: #03543F; border: 1px solid #84E1BC;';
                                    $stageIcon = 'fas fa-user-check';
                                    $payLabel = 'Paid';
                                    $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                }

                                $teamSourceStr = match($emp->team_source ?? 'Reporting Team') {
                                    'Both' => 'Both Scope',
                                    'Reporting Team' => 'Reporting',
                                    default => 'Project'
                                };
                                $teamSourceBadge = match($teamSourceStr) {
                                    'Both Scope' => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
                                    'Reporting' => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;',
                                    default => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;'
                                };

                                $deptName = optional($emp->department ?? null)->name ?? 'General';
                                $desigName = optional($emp->designation ?? null)->name ?? 'Staff';
                                $mgrName = $reportingManager ? ($reportingManager->display_name ?? 'Manager') : 'Not Assigned';
                            @endphp
                        <tr>
                            <!-- 1. S.No. -->
                            <td class="text-center font-weight-bold text-muted" style="font-size: 12px;">
                                {{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}
                            </td>

                            <!-- 2. Employee Column -->
                            <td>
                                <div>
                                    @if(Route::has('employees.show'))
                                        <a href="{{ route('employees.show', $emp->id) }}" class="emp-name-link">
                                            {{ $displayName }}
                                        </a>
                                    @else
                                        <span class="emp-name-link">{{ $displayName }}</span>
                                    @endif
                                    <div class="emp-code-badge">
                                        <i class="fas fa-id-badge text-muted" style="font-size: 10px;"></i> {{ $empCode }}
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Organization Column -->
                            <td>
                                <div>
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 13px; line-height: 1.25;">
                                        {{ $deptName }}
                                    </span>
                                    <small class="text-muted font-weight-bold" style="font-size: 11.5px;">{{ $desigName }}</small>
                                </div>
                            </td>

                            <!-- 4. Consolidated Employment Column -->
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center justify-content-center" style="gap: 4px;">
                                    <div class="d-flex align-items-center" style="gap: 4px;">
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10px; {{ $stageBadgeCss }}">
                                            <i class="{{ $stageIcon }} mr-1"></i> {{ $stageLabel }}
                                        </span>
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; {{ $payBadgeCss }}">
                                            {{ $payLabel }}
                                        </span>
                                    </div>
                                    @if($isIntern && isset($emp->internship_period) && $emp->internship_period !== '—')
                                        <small class="text-muted font-weight-bold" style="font-size: 10px; white-space: nowrap;">
                                            {{ $emp->internship_period }}
                                        </small>
                                    @endif
                                </div>
                            </td>

                            <!-- 5. Reporting Column -->
                            <td>
                                @if($reportingManager)
                                    <div>
                                        <strong class="text-dark font-weight-bold d-block" style="font-size: 12.5px; line-height: 1.2;">
                                            {{ $reportingManager->display_name ?? 'Manager' }}
                                        </strong>
                                        <small class="text-muted font-weight-bold" style="font-size: 10.5px;">{{ $reportingManager->employee_code ?? '' }}</small>
                                    </div>
                                @else
                                    <span class="small text-muted font-weight-bold">Not Assigned</span>
                                @endif
                            </td>

                            <!-- 6. Team Column -->
                            <td class="text-center">
                                <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 8px; font-size: 10.5px; {{ $teamSourceBadge }}">
                                    {{ $teamSourceStr }}
                                </span>
                            </td>

                            <!-- 7. Projects & Teams Column -->
                            <td>
                                @php
                                    $firstPrj = $projectAssignments->first();
                                    $remainingCount = count($projectAssignments) - 1;
                                @endphp
                                @if($firstPrj)
                                    <div class="mb-0">
                                        <span class="font-weight-bold text-primary d-block" style="font-size: 12.5px; line-height: 1.2;">
                                            <i class="fas fa-folder text-primary mr-1" style="font-size: 10.5px;"></i>{{ $firstPrj->project_name }}
                                        </span>
                                        <small class="text-muted" style="font-size: 11px;">
                                            @if($firstPrj->team_name)<span class="badge badge-light border" style="font-size: 9.5px; font-weight: 700;">{{ $firstPrj->team_name }}</span>@endif
                                            @if($firstPrj->role_name)<span class="text-info font-weight-bold ml-1">({{ $firstPrj->role_name }})</span>@endif
                                        </small>
                                        @if($remainingCount > 0)
                                            <span class="badge badge-primary font-weight-bold ml-1" style="border-radius: 6px; font-size: 9.5px;" title="{{ $projectAssignments->pluck('project_name')->join(', ') }}">
                                                +{{ $remainingCount }} more
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="small text-muted font-weight-bold">No Active Projects</span>
                                @endif
                            </td>

                            <!-- 8. Actions Three-Dot Column (⋮) -->
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn-action-dots" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-action">
                                        @if(Route::has('employees.show'))
                                            <a class="dropdown-item" href="{{ route('employees.show', $emp->id) }}">
                                                <i class="fas fa-user-circle text-primary"></i> View Profile
                                            </a>
                                        @endif
                                        @if(Route::has('projects.my'))
                                            <a class="dropdown-item" href="{{ route('projects.my') }}">
                                                <i class="fas fa-project-diagram text-success"></i> View Projects
                                            </a>
                                        @elseif(Route::has('projects.index'))
                                            <a class="dropdown-item" href="{{ route('projects.index') }}">
                                                <i class="fas fa-project-diagram text-success"></i> View Projects
                                            </a>
                                        @endif
                                        @if(Route::has('project_management.tasks.my'))
                                            <a class="dropdown-item" href="{{ route('project_management.tasks.my') }}">
                                                <i class="fas fa-tasks text-warning"></i> View Tasks
                                            </a>
                                        @elseif(Route::has('projects.tasks.index'))
                                            <a class="dropdown-item" href="{{ route('projects.tasks.index') }}">
                                                <i class="fas fa-tasks text-warning"></i> View Tasks
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-users-slash fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                <h5 class="font-weight-bold text-dark">No Team Members Found</h5>
                                <p class="small mb-0">No employees match the selected filters or supervision scope.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination Bar using Orbo Theme -->
            @if(method_exists($employees, 'links'))
                <div>
                    {{ $employees->appends(request()->query())->links('vendor.pagination.orbo') }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function getPdfMake() {
    if (typeof window.pdfMake === 'undefined') {
        return null;
    }
    if (typeof window.vfsFonts !== 'undefined' && window.vfsFonts.pdfMake) {
        window.pdfMake.vfs = window.vfsFonts.pdfMake.vfs;
    } else if (typeof window.pdfMake.vfs === 'undefined' && typeof window.pdfMake_vfs !== 'undefined') {
        window.pdfMake.vfs = window.pdfMake_vfs;
    }
    return window.pdfMake;
}

document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize Filter Select2 Dropdowns
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-filter').select2({
            width: '100%'
        });

        // Initialize Per-Page Select2
        $('.table-per-page-select').select2({
            minimumResultsForSearch: -1,
            containerCssClass: 'select2-container--per-page',
            dropdownCssClass: 'select2-dropdown-per-page',
            width: '75px'
        });
    }

    // 2. Toggle More Filters
    $('#btnToggleMoreFilters').on('click', function() {
        $(this).toggleClass('active');
        $('#moreFiltersCollapse').slideToggle(200);
    });

    // 3. Per-page change handler -> updates hidden input and submits form
    $('#recordsPerPageSelect').on('change', function() {
        $('#filterPerPageHidden').val(this.value);
        $('#myTeamFilterForm').submit();
    });

    // 4. CSV Export
    $('#btnExportCSV').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let csv = [];
        let headers = [];
        $('#myTeamTable thead th').each(function(i) {
            if (i < 7) {
                headers.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
            }
        });
        csv.push(headers.join(','));

        $('#myTeamTable tbody tr').each(function() {
            let cols = $(this).find('td');
            if (cols.length >= 7) {
                let row = [];
                // 0. S.No
                row.push('"' + $(cols[0]).text().trim().replace(/"/g, '""') + '"');
                // 1. Employee
                let empName = $(cols[1]).find('.emp-name-link').text().trim() || $(cols[1]).text().trim();
                let empCode = $(cols[1]).find('.emp-code-badge').text().trim();
                row.push('"' + (empName + (empCode ? ' (' + empCode + ')' : '')).replace(/"/g, '""') + '"');
                // 2. Organization
                let dept = $(cols[2]).find('span').text().trim() || $(cols[2]).text().trim();
                let desig = $(cols[2]).find('small').text().trim();
                row.push('"' + (dept + (desig ? ' - ' + desig : '')).replace(/"/g, '""') + '"');
                // 3. Employment
                let empType = $(cols[3]).text().replace(/\s+/g, ' ').trim();
                row.push('"' + empType.replace(/"/g, '""') + '"');
                // 4. Reporting
                let mgr = $(cols[4]).text().replace(/\s+/g, ' ').trim();
                row.push('"' + mgr.replace(/"/g, '""') + '"');
                // 5. Team
                let team = $(cols[5]).text().trim();
                row.push('"' + team.replace(/"/g, '""') + '"');
                // 6. Projects & Teams
                let prj = $(cols[6]).text().replace(/\s+/g, ' ').trim();
                row.push('"' + prj.replace(/"/g, '""') + '"');

                csv.push(row.join(','));
            }
        });

        let csvString = '\uFEFF' + csv.join('\r\n');
        let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'My_Team_Directory_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(function() { URL.revokeObjectURL(url); }, 500);
    });

    // 5. Excel Export (HTML Blob format)
    $('#btnExportExcel').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let rowsHtml = '';
        
        // Header
        rowsHtml += '<tr style="background-color: #4B00E8; color: #FFFFFF; font-weight: bold;">';
        $('#myTeamTable thead th').each(function(i) {
            if (i < 7) {
                rowsHtml += '<th style="padding: 10px; border: 1px solid #CBD5E1;">' + $(this).text().trim() + '</th>';
            }
        });
        rowsHtml += '</tr>';

        // Body
        $('#myTeamTable tbody tr').each(function(rowIndex) {
            let cols = $(this).find('td');
            if (cols.length >= 7) {
                let bg = rowIndex % 2 === 0 ? '#FFFFFF' : '#F8FAFC';
                rowsHtml += '<tr style="background-color: ' + bg + ';">';
                
                // S.No
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0; text-align: center;">' + $(cols[0]).text().trim() + '</td>';
                // Employee
                let empName = $(cols[1]).find('.emp-name-link').text().trim() || $(cols[1]).text().trim();
                let empCode = $(cols[1]).find('.emp-code-badge').text().trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0;"><strong>' + empName + '</strong><br><small style="color: #64748B;">' + empCode + '</small></td>';
                // Organization
                let dept = $(cols[2]).find('span').text().trim() || $(cols[2]).text().trim();
                let desig = $(cols[2]).find('small').text().trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0;">' + dept + '<br><small style="color: #64748B;">' + desig + '</small></td>';
                // Employment
                let empType = $(cols[3]).text().replace(/\s+/g, ' ').trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0; text-align: center;">' + empType + '</td>';
                // Reporting
                let mgr = $(cols[4]).text().replace(/\s+/g, ' ').trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0;">' + mgr + '</td>';
                // Team
                let team = $(cols[5]).text().trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0; text-align: center;">' + team + '</td>';
                // Projects
                let prj = $(cols[6]).text().replace(/\s+/g, ' ').trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0;">' + prj + '</td>';

                rowsHtml += '</tr>';
            }
        });

        let template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' +
            '<head><meta charset="utf-8"></head>' +
            '<body><table border="1">' + rowsHtml + '</table></body></html>';

        let blob = new Blob(['\uFEFF' + template], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'My_Team_Directory_' + new Date().toISOString().slice(0,10) + '.xls');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(function() { URL.revokeObjectURL(url); }, 500);
    });

    // 6. PDF Export (Direct PDF Download using pdfMake)
    $('#btnExportPDF').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let tableBody = [];
        
        // Header
        let headerRow = [];
        $('#myTeamTable thead th').each(function(i) {
            if (i < 7) {
                headerRow.push({
                    text: $(this).text().trim(),
                    bold: true,
                    color: '#FFFFFF',
                    fillColor: '#4B00E8',
                    fontSize: 9,
                    margin: [0, 4, 0, 4]
                });
            }
        });
        tableBody.push(headerRow);

        // Body
        $('#myTeamTable tbody tr').each(function() {
            let cols = $(this).find('td');
            if (cols.length >= 7) {
                let row = [];
                // 0. S.No
                row.push({ text: $(cols[0]).text().trim(), alignment: 'center', fontSize: 8.5 });
                // 1. Employee
                let empName = $(cols[1]).find('.emp-name-link').text().trim() || $(cols[1]).text().trim();
                let empCode = $(cols[1]).find('.emp-code-badge').text().trim();
                row.push({ text: empName + '\n' + empCode, bold: true, fontSize: 8.5 });
                // 2. Organization
                let dept = $(cols[2]).find('span').text().trim() || $(cols[2]).text().trim();
                let desig = $(cols[2]).find('small').text().trim();
                row.push({ text: dept + '\n' + desig, fontSize: 8.5 });
                // 3. Employment
                let empType = $(cols[3]).text().replace(/\s+/g, ' ').trim();
                row.push({ text: empType, alignment: 'center', fontSize: 8 });
                // 4. Reporting
                let mgr = $(cols[4]).text().replace(/\s+/g, ' ').trim();
                row.push({ text: mgr, fontSize: 8.5 });
                // 5. Team
                let team = $(cols[5]).text().trim();
                row.push({ text: team, alignment: 'center', fontSize: 8.5 });
                // 6. Projects & Teams
                let prj = $(cols[6]).text().replace(/\s+/g, ' ').trim();
                row.push({ text: prj, fontSize: 8.5 });

                tableBody.push(row);
            }
        });

        let printDate = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

        let docDefinition = {
            pageOrientation: 'landscape',
            pageSize: 'A4',
            pageMargins: [20, 25, 20, 25],
            header: function(currentPage, pageCount) {
                return {
                    margin: [20, 10, 20, 0],
                    columns: [
                        { text: 'OrboOne HRMS — Team Management', fontSize: 8, bold: true, color: '#4B00E8' },
                        { text: 'Page ' + currentPage + ' of ' + pageCount, alignment: 'right', fontSize: 8, color: '#64748B' }
                    ]
                };
            },
            content: [
                {
                    text: 'My Team Directory',
                    fontSize: 16,
                    bold: true,
                    color: '#101828',
                    margin: [0, 0, 0, 3]
                },
                {
                    text: 'Exported on: ' + printDate + ' | Supervision Scope',
                    fontSize: 9,
                    color: '#64748B',
                    margin: [0, 0, 0, 12]
                },
                {
                    table: {
                        headerRows: 1,
                        widths: ['5%', '18%', '16%', '15%', '15%', '12%', '19%'],
                        body: tableBody
                    },
                    layout: {
                        fillColor: function (rowIndex) {
                            if (rowIndex === 0) return '#4B00E8';
                            return (rowIndex % 2 === 0) ? '#F8FAFC' : null;
                        },
                        hLineWidth: function () { return 0.5; },
                        vLineWidth: function () { return 0.5; },
                        hLineColor: function () { return '#E2E8F0'; },
                        vLineColor: function () { return '#E2E8F0'; },
                        paddingLeft: function () { return 6; },
                        paddingRight: function () { return 6; },
                        paddingTop: function () { return 5; },
                        paddingBottom: function () { return 5; }
                    }
                }
            ],
            defaultStyle: {
                font: 'Roboto'
            }
        };

        const pMake = getPdfMake();
        if (pMake && typeof pMake.createPdf === 'function') {
            try {
                pMake.createPdf(docDefinition).download('My_Team_Directory_' + new Date().toISOString().slice(0,10) + '.pdf');
            } catch (err) {
                console.error('PDF Generation Error:', err);
                alert('PDF generation encountered an error. Please try again.');
            }
        } else {
            alert('PDF engine is loading. Please wait a moment and try again.');
        }
    });

    // 7. Print (Dedicated Styled Print Window)
    $('#btnPrint').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let printWin = window.open('', '_blank', 'width=1100,height=750');
        if (!printWin) {
            window.print();
            return;
        }

        let rowsHtml = '';
        
        // Header
        rowsHtml += '<thead><tr>';
        $('#myTeamTable thead th').each(function(i) {
            if (i < 7) {
                rowsHtml += '<th>' + $(this).text().trim() + '</th>';
            }
        });
        rowsHtml += '</tr></thead><tbody>';

        // Body
        $('#myTeamTable tbody tr').each(function() {
            let cols = $(this).find('td');
            if (cols.length >= 7) {
                rowsHtml += '<tr>';
                // S.No
                rowsHtml += '<td style="text-align: center;">' + $(cols[0]).text().trim() + '</td>';
                // Employee
                let empName = $(cols[1]).find('.emp-name-link').text().trim() || $(cols[1]).text().trim();
                let empCode = $(cols[1]).find('.emp-code-badge').text().trim();
                rowsHtml += '<td><strong>' + empName + '</strong><br><small style="color: #64748B;">' + empCode + '</small></td>';
                // Organization
                let dept = $(cols[2]).find('span').text().trim() || $(cols[2]).text().trim();
                let desig = $(cols[2]).find('small').text().trim();
                rowsHtml += '<td>' + dept + '<br><small style="color: #64748B;">' + desig + '</small></td>';
                // Employment
                let empType = $(cols[3]).text().replace(/\s+/g, ' ').trim();
                rowsHtml += '<td style="text-align: center;">' + empType + '</td>';
                // Reporting
                let mgr = $(cols[4]).text().replace(/\s+/g, ' ').trim();
                rowsHtml += '<td>' + mgr + '</td>';
                // Team
                let team = $(cols[5]).text().trim();
                rowsHtml += '<td style="text-align: center;">' + team + '</td>';
                // Projects
                let prj = $(cols[6]).text().replace(/\s+/g, ' ').trim();
                rowsHtml += '<td>' + prj + '</td>';
                rowsHtml += '</tr>';
            }
        });
        rowsHtml += '</tbody>';

        let printDate = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

        let html = `<!DOCTYPE html>
        <html>
        <head>
            <title>My Team Directory (Print)</title>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
            <style>
                @media print {
                    @page { size: A4 landscape; margin: 10mm 12mm; }
                }
                body {
                    font-family: 'Outfit', sans-serif;
                    color: #0F172A;
                    background: #FFFFFF;
                    margin: 0;
                    padding: 16px;
                }
                .print-hero {
                    background: linear-gradient(135deg, #4B00E8 0%, #FF5252 100%) !important;
                    border-radius: 14px;
                    padding: 18px 24px;
                    color: #ffffff;
                    margin-bottom: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                .print-hero h2 { margin: 0; font-size: 20px; font-weight: 800; color: #fff; }
                .print-hero p { margin: 4px 0 0 0; font-size: 12px; opacity: 0.9; color: #fff; }
                .print-meta { background: rgba(255,255,255,0.22); padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; color: #fff; }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px;
                    margin-top: 10px;
                }
                th {
                    background: #1E293B !important;
                    color: #FFFFFF !important;
                    font-size: 11px;
                    font-weight: 800;
                    text-transform: uppercase;
                    padding: 9px 12px;
                    text-align: left;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                    border: 1px solid #1E293B;
                }
                td {
                    padding: 8px 12px;
                    border: 1px solid #E2E8F0;
                    vertical-align: middle;
                }
                tr:nth-child(even) td {
                    background: #F8FAFC !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            </style>
        </head>
        <body>
            <div class="print-hero">
                <div>
                    <h2>OrboOne HRMS</h2>
                    <p>Team Management &bull; My Team Directory</p>
                </div>
                <div class="print-meta">
                    Date: ${printDate}
                </div>
            </div>
            <table>${rowsHtml}</table>
        </body>
        </html>`;

        printWin.document.open();
        printWin.document.write(html);
        printWin.document.close();
        
        printWin.onload = function() {
            printWin.focus();
            setTimeout(function() {
                printWin.print();
            }, 300);
        };
    });
});
</script>
@endsection
