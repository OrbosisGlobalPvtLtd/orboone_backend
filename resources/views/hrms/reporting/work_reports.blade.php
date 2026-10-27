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
    padding: 16px 18px 40px;
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
    border-radius: 22px;
    padding: 22px 28px;
    margin-bottom: 20px;
    box-shadow: 0 16px 36px rgba(75, 0, 232, 0.18);
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
    width: 280px;
    height: 280px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.rep-hero-kicker {
    font-size: 11px;
    font-weight: 850;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .92;
    margin-bottom: 5px;
    display: flex;
    gap: 7px;
    align-items: center;
}

.rep-hero-title {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    line-height: 1.2;
    color: #ffffff;
}

.rep-hero-subtitle {
    font-size: 13px;
    font-weight: 500;
    margin-top: 5px;
    opacity: .92;
    max-width: 750px;
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
    font-size: 12px !important;
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
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.team-metric-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 14px 16px 12px;
    box-shadow: 0 8px 22px rgba(16, 24, 40, .045);
    position: relative;
    overflow: hidden;
    min-height: 90px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.team-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(16, 24, 40, .09);
}

.team-metric-card:after {
    content: "";
    position: absolute;
    right: -24px;
    top: -28px;
    width: 82px;
    height: 82px;
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
    width: 36px;
    height: 36px;
    border-radius: 11px;
    background: var(--metric-soft, #F4F2FF);
    color: var(--metric-color, var(--orb-primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.team-metric-value {
    font-size: 23px;
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
    margin-top: 10px;
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
    gap: 14px;
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

/* Multi-Filter Bar with Separate Year and Month */
.rep-filter-bar {
    padding: 16px 18px 12px;
    background: #FFFFFF;
    border-bottom: 1px solid #F1F5F9;
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

.rep-filter-col-year {
    flex: 0 1 105px;
    min-width: 95px;
}

.rep-filter-col-month {
    flex: 1 1 145px;
    min-width: 135px;
}

.rep-filter-col-date {
    flex: 1 1 140px;
    min-width: 130px;
}

.rep-filter-col-mode {
    flex: 1 1 125px;
    min-width: 115px;
}

.rep-filter-col-emp {
    flex: 1.5 1 180px;
    min-width: 165px;
}

.rep-filter-col-proj {
    flex: 1.2 1 155px;
    min-width: 140px;
}

.rep-filter-col-search {
    flex: 1.5 1 200px;
    min-width: 175px;
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

/* Select2 Customization */
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

/* Table Toolbar: Show Entries + Export Group */
.orb-table-tools-bar {
    padding: 10px 18px;
    background: #FAFBFC;
    border-bottom: 1px solid var(--orb-border);
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

/* Table Section Matching Orbo Attendance Work Reports Standard */
.rep-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.rep-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    margin-bottom: 0;
    min-width: 1100px;
}

.rep-table thead th {
    background: #FAFBFC;
    color: #475467;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 13px 16px;
    border-top: none;
    border-bottom: 1px solid var(--orb-border);
    white-space: nowrap;
    vertical-align: middle;
}

.rep-table tbody td {
    padding: 13px 16px;
    border-bottom: 1px solid var(--orb-border);
    color: var(--orb-text);
    vertical-align: middle;
    font-size: 13px;
    background: #FFFFFF;
    transition: background 0.15s ease;
}

.rep-table tbody tr:hover td {
    background: #F9FAFB !important;
}

/* Employee Column with Passport Avatar */
.table-emp-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.table-emp-avatar {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: #F4F2FF;
    color: var(--orb-primary, #4B00E8);
    font-weight: 800;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(75, 0, 232, 0.15);
}

.table-emp-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.table-emp-name {
    font-weight: 800;
    font-size: 13.5px;
    color: #101828;
    line-height: 1.25;
}

.table-emp-meta {
    font-size: 11.5px;
    color: var(--orb-muted);
    font-weight: 600;
    margin-top: 2px;
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
    white-space: nowrap;
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
    white-space: nowrap;
}

/* Structured Tasks & Project Styling */
.structured-project-group {
    margin-bottom: 4px;
}

.structured-task-item {
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 2px;
}

.structured-task-item .task-tag {
    font-size: 11px;
    margin-right: 5px;
}

/* Action Button Group */
.action-btn-group {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.btn-action-primary {
    height: 34px;
    padding: 0 12px;
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

.btn-action-secondary {
    height: 34px;
    width: 34px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    background: #FFFFFF;
    color: #475467;
    border: 1px solid #E7EAF3;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    text-decoration: none !important;
}

.btn-action-secondary:hover {
    background: #F8FAFC;
    color: var(--orb-primary, #4B00E8);
    border-color: #D9CCFF;
}

/* Responsive Media Queries Across All Screen Sizes */
@media (max-width: 1280px) {
    .team-metric-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 991px) {
    .rep-hero {
        padding: 18px 20px;
    }
    .rep-hero-title {
        font-size: 21px;
    }
    .team-metric-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .rep-filter-row {
        gap: 10px;
    }
    .rep-filter-col-year,
    .rep-filter-col-month,
    .rep-filter-col-date,
    .rep-filter-col-mode {
        flex: 1 1 calc(50% - 10px);
    }
    .rep-filter-col-emp,
    .rep-filter-col-proj,
    .rep-filter-col-search {
        flex: 1 1 100%;
    }
    .rep-filter-actions-right {
        width: 100%;
        margin-top: 6px;
    }
    .rep-search-btn, .rep-reset-btn {
        flex: 1;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .rep-page {
        padding: 12px 12px 30px;
    }
    .rep-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 18px;
        border-radius: 18px;
    }
    .rep-hero-right {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    .rep-btn-glass {
        padding: 6px 14px !important;
        font-size: 11.5px !important;
    }
    .team-metric-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .team-metric-card {
        padding: 12px 14px 10px;
    }
    .team-metric-value {
        font-size: 20px;
    }
    .rep-section-head {
        padding: 14px 16px;
    }
    .rep-filter-bar {
        padding: 14px 16px;
    }
    .rep-filter-row > div:not(.rep-filter-actions-right) {
        width: 100% !important;
        flex: 1 1 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }
    .rep-filter-actions-right {
        width: 100%;
        justify-content: stretch;
    }
    .orb-table-tools-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    .orb-table-length-box {
        justify-content: space-between;
    }
    .orbo-export-group {
        width: 100%;
        display: flex;
        justify-content: space-around;
    }
    .orbo-export-btn {
        flex: 1;
        justify-content: center;
        padding: 6px 4px;
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .team-metric-grid {
        grid-template-columns: 1fr;
    }
    .rep-hero-title {
        font-size: 19px;
    }
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">

        <!-- 1. Signature Hero Header Banner -->
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
            $currDate = request('date');
            $currFromDate = request('from_date');
            $currToDate = request('to_date');
            $currMode = request('work_mode');
            $currEmpId = request('employee_id');
            $currProjId = request('project_id');
            $currSearch = request('search');

            $activeYear = (string) ($selectedYear ?? request('year', date('Y')));
            $activeMonth = (string) ($selectedMonth ?? request('month', date('m')));
            $isCustomMode = ($isCustomDate ?? ($activeMonth === 'custom' || (!empty($currFromDate) || !empty($currToDate))));
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

            <!-- Attached Multi-Filter Bar with Separate Year and Month -->
            <form method="GET" action="{{ route('reporting.work_reports') }}" id="workReportFilterForm">
                <input type="hidden" name="per_page" id="filterPerPageHidden" value="{{ request('per_page', 25) }}">

                <div class="rep-filter-bar">
                    <div class="rep-filter-row">
                        <!-- 1. Year Filter (Separate) -->
                        <div class="rep-filter-col-year" id="yearFilterWrap">
                            <label class="rep-filter-label" for="filterYearSelect">
                                <i class="fas fa-calendar text-muted mr-1"></i> Year
                            </label>
                            <select name="year" id="filterYearSelect" class="select2-filter select2-searchable">
                                @foreach($filterYears as $yVal => $yLbl)
                                    <option value="{{ $yVal }}" {{ (string) $activeYear === (string) $yVal ? 'selected' : '' }}>{{ $yLbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Month Filter (Separate) -->
                        <div class="rep-filter-col-month" id="monthFilterWrap">
                            <label class="rep-filter-label" for="filterMonthSelect">
                                <i class="fas fa-calendar-alt text-muted mr-1"></i> Month
                            </label>
                            <select name="month" id="filterMonthSelect" class="select2-filter select2-searchable">
                                
                                <option value="custom" {{ $isCustomMode ? 'selected' : '' }}>Custom Date Range</option>
                                @foreach($filterMonths as $mVal => $mLbl)
                                    @if($mVal !== 'custom')
                                        <option value="{{ $mVal }}" {{ (!$isCustomMode && (string)$activeMonth === (string)$mVal) ? 'selected' : '' }}>{{ $mLbl }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- 3. Single Work Date (Visible when regular month is selected) -->
                        <div id="singleDateWrap" class="rep-filter-col-date" style="display: {{ $isCustomMode ? 'none' : 'block' }};">
                            <label class="rep-filter-label" for="filterDateInput">
                                <i class="fas fa-calendar-day text-muted mr-1"></i> Work Date
                            </label>
                            <x-form.date-picker name="date" id="filterDateInput" :value="$currDate" placeholder="dd-mm-yyyy" class="rep-date-control" />
                        </div>

                        <!-- 4. Custom From Date (Visible when Custom Range chosen) -->
                        <div id="customFromWrap" class="rep-filter-col-date" style="display: {{ $isCustomMode ? 'block' : 'none' }};">
                            <label class="rep-filter-label" for="filterFromDate">
                                <i class="fas fa-calendar-alt text-muted mr-1"></i> From Date
                            </label>
                            <x-form.date-picker name="from_date" id="filterFromDate" :value="$currFromDate" placeholder="dd-mm-yyyy" class="rep-date-control" />
                        </div>

                        <!-- 5. Custom To Date (Visible when Custom Range chosen) -->
                        <div id="customToWrap" class="rep-filter-col-date" style="display: {{ $isCustomMode ? 'block' : 'none' }};">
                            <label class="rep-filter-label" for="filterToDate">
                                <i class="fas fa-calendar-alt text-muted mr-1"></i> To Date
                            </label>
                            <x-form.date-picker name="to_date" id="filterToDate" :value="$currToDate" placeholder="dd-mm-yyyy" class="rep-date-control" />
                        </div>

                        <!-- 6. Work Mode -->
                        <div class="rep-filter-col-mode">
                            <label class="rep-filter-label" for="filterWorkMode">
                                <i class="fas fa-building text-muted mr-1"></i> Work Mode
                            </label>
                            <select name="work_mode" id="filterWorkMode" class="select2-filter">
                                <option value="">All Modes</option>
                                <option value="wfo" {{ strtolower($currMode) === 'wfo' ? 'selected' : '' }}>Office (WFO)</option>
                                <option value="wfh" {{ strtolower($currMode) === 'wfh' ? 'selected' : '' }}>Remote (WFH)</option>
                            </select>
                        </div>

                        <!-- 7. Employee (Select2) -->
                        <div class="rep-filter-col-emp">
                            <label class="rep-filter-label" for="filterEmployeeSelect">
                                <i class="far fa-user text-muted mr-1"></i> Employee
                            </label>
                            <select name="employee_id" id="filterEmployeeSelect" class="select2-filter select2-searchable">
                                <option value="">All Employees ({{ $teamEmployees->count() }})</option>
                                @foreach($teamEmployees as $emp)
                                    <option value="{{ $emp->id }}" {{ (string)$currEmpId === (string)$emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }} ({{ $emp->employee_code }}){{ $emp->designation ? ' • ' . $emp->designation->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 8. Project (Select2) -->
                        <div class="rep-filter-col-proj">
                            <label class="rep-filter-label" for="filterProjectSelect">
                                <i class="far fa-folder text-muted mr-1"></i> Project
                            </label>
                            <select name="project_id" id="filterProjectSelect" class="select2-filter select2-searchable">
                                <option value="">All Projects ({{ $teamProjects->count() }})</option>
                                @foreach($teamProjects as $prj)
                                    <option value="{{ $prj->id }}" {{ (string)$currProjId === (string)$prj->id ? 'selected' : '' }}>
                                        {{ $prj->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 9. Search Keyword -->
                        <div class="rep-filter-col-search">
                            <label class="rep-filter-label">
                                <i class="fas fa-search text-muted mr-1"></i> Search
                            </label>
                            <div class="rep-search-input-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" id="filter-search" class="rep-search-input" value="{{ $currSearch }}" placeholder="Search name, task, keyword...">
                            </div>
                        </div>

                        <!-- 10. Action Buttons -->
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
                        <option value="10" {{ (int) request('per_page', 25) === 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ (int) request('per_page', 25) === 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ (int) request('per_page', 25) === 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ (int) request('per_page', 25) === 100 ? 'selected' : '' }}>100</option>
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

            <!-- Attached Table Section Matching Attendance Work Report Design -->
            <div class="rep-table-wrap">
                <table class="rep-table" id="reportingWorkReportsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px; min-width: 50px;">#</th>
                            <th style="min-width: 220px;">Employee</th>
                            <th style="min-width: 110px;">Date</th>
                            <th class="text-center" style="min-width: 90px;">Mode</th>
                            <th style="min-width: 125px;">Shift Context</th>
                            <th class="text-center" style="min-width: 115px;">Gross Work</th>
                            <th style="min-width: 320px; width: 34%;">Work Summary Description</th>
                            <th style="min-width: 250px; width: 22%;">Structured Tasks</th>
                            <th class="text-right pr-4" style="width: 110px; min-width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workReports as $report)
                            @php
                                $row = formatWorkReportRow($report);
                                $empId = $report->employee_id ?: ($report->user_id ?: 0);
                                $modeBadgeClass = $row['mode'] === 'WFH' ? 'badge-wfh' : 'badge-wfo';

                                $logPayload = [
                                    'id' => $report->id,
                                    'work_log_id' => $report->id,
                                    'employee_name' => $row['employee_name'],
                                    'employee_code' => $row['employee_code'],
                                    'passport_photo_url' => resolveEmployeePassportPhoto($report->employee ?? $report),
                                    'employee_initial' => resolveEmployeeInitials($report->employee ?? $report),
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
                                $startIdx = ($workReports->currentPage() - 1) * $workReports->perPage();
                            @endphp
                        <tr>
                            <td class="text-center font-weight-bold text-muted" style="font-size: 12px;">
                                {{ $startIdx + $loop->iteration }}
                            </td>

                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span class="table-emp-name">{{ $row['employee_name'] }}</span>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 5px;">
                                        <span class="emp-code-badge"><i class="fas fa-id-badge"></i> {{ $row['employee_code'] }}</span>
                                        @if(!empty($row['designation']))
                                            <span class="text-muted font-weight-semibold" style="font-size: 11.5px;">&bull; {{ $row['designation'] }}</span>
                                        @elseif(!empty($row['department']))
                                            <span class="text-muted font-weight-semibold" style="font-size: 11.5px;">&bull; {{ $row['department'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 13px; white-space: nowrap;">
                                    {{ $row['date'] }}
                                </div>
                                @if($row['day_name'])
                                    <div class="small text-muted font-weight-semibold">
                                        {{ $row['day_name'] }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge-premium-pill {{ $modeBadgeClass }}">
                                    @if($row['mode'] === 'WFH')
                                        <i class="fas fa-laptop-house mr-1"></i> WFH
                                    @else
                                        <i class="fas fa-building mr-1"></i> WFO
                                    @endif
                                </span>
                            </td>

                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                    {{ $row['shift_context'] ?: 'General Shift' }}
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="badge-gross-pill" style="white-space: nowrap;">
                                    <i class="fas fa-stopwatch mr-1"></i> {{ $row['gross_work'] ?: '0h 0m' }}
                                </div>
                            </td>

                            <td style="min-width: 300px;">
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
                                            @forelse($row['summary_paragraphs'] as $para)
                                                @php
                                                    $paraDisplay = $para;
                                                    if (!empty($row['title'])) {
                                                        $paraDisplay = preg_replace('/^' . preg_quote($row['title'], '/') . '\s*[-–:]\s*/i', '', $paraDisplay);
                                                    }
                                                @endphp
                                                @if(trim($paraDisplay) !== '')
                                                    <p class="mb-1" style="line-height: 1.45; color: #1E293B; font-size: 12.5px; font-weight: 500;">
                                                        {{ $paraDisplay }}
                                                    </p>
                                                @endif
                                            @empty
                                                <span class="text-muted font-weight-normal" style="font-size: 11.5px;">—</span>
                                            @endforelse
                                        </div>
                                        @if(!empty($row['notes']))
                                            <div class="mt-1 p-1.5 px-2 rounded" style="background: #FFFBEB; border: 1px solid #FEF3C7; color: #92400E; font-size: 11.5px; border-radius: 6px;">
                                                <strong>Note:</strong> {{ $row['notes'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td style="min-width: 240px;">
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
                                                    <div class="structured-task-item d-flex align-items-center pl-2 mb-1">
                                                        <span class="task-tag font-weight-bold {{ $ptDone ? 'text-success' : 'text-warning' }}" style="font-size: 11px; white-space: nowrap;">
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
                                        @forelse($row['structured_tasks'] as $tItem)
                                            <div class="structured-task-item d-flex align-items-center pl-2 mb-1">
                                                <span class="task-tag font-weight-bold {{ $tItem['done'] ? 'text-success' : 'text-warning' }}" style="font-size: 11px; white-space: nowrap;">
                                                    {{ $tItem['done'] ? '[Done]' : '[Pending]' }}
                                                </span>
                                                <span class="text-dark">{{ $tItem['text'] }}</span>
                                            </div>
                                        @empty
                                            <span class="text-muted font-weight-normal" style="font-size: 11.5px;">—</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>

                            <td class="text-right pr-4" style="white-space: nowrap;">
                                <div class="action-btn-group">
                                    <button type="button" class="btn-action-primary" 
                                            data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" 
                                            onclick="parseAndOpenWorkReport(this)"
                                            title="View Full Report Details">
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                    @if($empId && Route::has('hrms.attendance.work-reports.employee-history'))
                                    <a href="{{ route('hrms.attendance.work-reports.employee-history', $empId) }}" 
                                       target="_blank" 
                                       class="btn-action-secondary" 
                                       title="View Employee Work History">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-clipboard-list fa-3x mb-3 text-muted" style="opacity: 0.35;"></i>
                                <h5 class="font-weight-bold text-dark mb-1">No Daily Work Reports Found</h5>
                                <p class="small mb-0 text-muted">Try adjusting your year, month, or search filters to see results.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination using Orbo Theme Template -->
            @if(method_exists($workReports, 'links'))
                <div class="orb-table-footer p-0 border-0">
                    {{ $workReports->appends(request()->query())->links('vendor.pagination.orbo') }}
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

        // Handle Year Dropdown Change (auto submit form)
        $('#filterYearSelect').on('change', function() {
            var mVal = $('#filterMonthSelect').val();
            if (mVal !== 'custom') {
                document.getElementById('workReportFilterForm').submit();
            }
        });

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
            document.getElementById('workReportFilterForm').submit();
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
