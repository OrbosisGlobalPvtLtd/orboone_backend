@extends('layouts.panel', ['active' => 'reporting_dashboard'])

@section('page_title', 'Team Management Dashboard')

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

/* Hero Banner */
.rep-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 26px;
    padding: 24px 28px;
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

.rep-btn-glass {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.38) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border-radius: 999px !important;
    padding: 9px 20px !important;
    font-size: 13px !important;
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

/* Metric KPI Cards Grid */
.rep-metric-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.rep-metric {
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

.rep-metric:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(16, 24, 40, .09);
}

.rep-metric:after {
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

.rep-metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    position: relative;
    z-index: 1;
}

.rep-metric-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: var(--metric-soft, #F4F2FF);
    color: var(--metric-color, var(--orb-primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.rep-metric-value {
    font-size: 24px;
    font-weight: 900;
    color: #101828;
    line-height: 1;
}

.rep-metric-label {
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

.rep-metric-line {
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--metric-color, var(--orb-primary)), transparent);
    margin-top: 8px;
}

/* Card Containers */
.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.rep-section-head {
    padding: 14px 20px;
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
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--orb-soft);
    color: var(--orb-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

/* Module Shortcuts Grid (Full-width 6 modules) */
.shortcut-grid-6 {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}

.shortcut-btn {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1E293B;
    font-weight: 750;
    font-size: 13px;
    transition: all 0.2s ease;
    text-decoration: none !important;
}

.shortcut-btn:hover {
    background: #EEF2FF;
    border-color: #C7D2FE;
    color: var(--orb-primary);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(75, 0, 232, 0.08);
}

.shortcut-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}

/* Filter Panel */
.rep-filter-panel {
    padding: 14px 20px;
    background: #FFFFFF;
    border-bottom: 1px solid var(--orb-border);
}

.rep-search-form {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    width: 100%;
}

.rep-search-input-wrap {
    position: relative;
    flex: 1;
    min-width: 260px;
    max-width: 420px;
}

.rep-search-input-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
    font-size: 13px;
}

.rep-search-input {
    height: 42px;
    padding-left: 38px !important;
    padding-right: 14px;
    border-radius: 12px;
    border: 1px solid #E2E8F0;
    font-size: 13px;
    font-weight: 600;
    background: #fff;
    width: 100%;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.rep-search-input:focus {
    border-color: var(--orb-primary);
    box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .10) !important;
    outline: none;
}

.rep-filter-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Brand Theme Search Button */
.rep-search-btn {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 12px !important;
    font-weight: 800 !important;
    font-size: 13px !important;
    padding: 0 20px !important;
    height: 42px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25) !important;
    transition: all 0.2s ease !important;
    cursor: pointer;
    white-space: nowrap;
}

.rep-search-btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(75, 0, 232, 0.38) !important;
    color: #ffffff !important;
    opacity: 0.95;
}

.rep-reset-btn {
    background: #F8FAFC !important;
    color: #475467 !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 12px !important;
    font-weight: 750 !important;
    font-size: 13px !important;
    padding: 0 16px !important;
    height: 42px !important;
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
    border-color: #CBD5E1 !important;
}

/* Select2 Per-Page Styling */
.select2-container--per-page.select2-container--default .select2-selection--single {
    min-height: 38px !important;
    height: 38px !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 10px !important;
    padding: 0 6px !important;
    width: 75px !important;
    background-color: #fff !important;
    display: flex !important;
    align-items: center !important;
}

.select2-container--per-page.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 13px !important;
    font-weight: 750 !important;
    color: var(--orb-text, #101828) !important;
    padding-left: 4px !important;
}

.select2-container--per-page.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 6px !important;
}

.select2-dropdown-per-page {
    min-width: 75px !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    font-size: 13px !important;
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

/* Table Tools Bar & Export Buttons (Standard Orbo Theme) */
.orb-table-tools-bar {
    padding: 12px 20px;
    background: #F8FAFC;
    border-bottom: 1px solid #EAECF0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.orbo-export-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.orbo-export-btn {
    height: 36px !important;
    border-radius: 10px !important;
    padding: 6px 14px !important;
    font-size: 12.5px !important;
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
    min-width: 820px;
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
    font-size: 13px;
    color: #1E293B;
}

.rep-table tbody tr:hover td {
    background: #FCFAFF !important;
}

/* Status Badges */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.2px;
    text-transform: uppercase;
}

.status-pill.present {
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
}

.status-pill.wfh {
    background: #E0F2FE;
    color: #0369A1;
    border: 1px solid #7DD3FC;
}

.status-pill.half_day {
    background: #FEF3C7;
    color: #B45309;
    border: 1px solid #FCD34D;
}

.status-pill.late {
    background: #FFEDD5;
    color: #C2410C;
    border: 1px solid #FDBA74;
}

.status-pill.leave {
    background: #FAF5FF;
    color: #7E22CE;
    border: 1px solid #E9D5FF;
}

.status-pill.absent {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

.status-pill.pending {
    background: #FEF3C7;
    color: #B45309;
    border: 1px solid #FCD34D;
}

.status-pill.submitted {
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
}

/* Live Pulse Dot */
.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10B981;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-green 1.6s infinite;
}

@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Responsive Grid Rules */
@media (max-width: 1200px) {
    .rep-metric-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .shortcut-grid-6 {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .rep-page {
        padding: 10px 10px 30px;
    }
    .rep-hero {
        padding: 18px 16px;
        border-radius: 18px;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .rep-hero-title {
        font-size: 20px;
    }
    .rep-metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }
    .shortcut-grid-6 {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .rep-search-form {
        flex-direction: column;
        align-items: stretch;
    }
    .rep-search-input-wrap {
        max-width: 100%;
        min-width: 100%;
    }
    .rep-filter-actions {
        width: 100%;
    }
    .rep-search-btn,
    .rep-reset-btn {
        flex: 1;
    }
    .orb-table-tools-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .orbo-export-group {
        width: 100%;
    }
    .orbo-export-btn {
        flex: 1;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .rep-metric {
        padding: 10px 12px;
        min-height: 80px;
        border-radius: 14px;
    }
    .rep-metric-value {
        font-size: 20px;
    }
    .rep-metric-label {
        font-size: 10px;
        margin-top: 6px;
    }
    .shortcut-btn {
        padding: 10px 12px;
        font-size: 12px;
    }
    .shortcut-icon-box {
        width: 30px;
        height: 30px;
        font-size: 13px;
    }
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        
        <!-- Hero Header -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-users-cog"></i> TEAM MANAGEMENT & OPERATIONS
                </div>
                <h3 class="rep-hero-title">Team Management Dashboard</h3>
                <div class="rep-hero-subtitle">Real-time operational monitoring, attendance tracking, daily work logs, and team performance overview.</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap" style="gap: 10px;">
                <a href="{{ route('reporting.my_employees') }}" class="rep-btn-glass">
                    <i class="fas fa-users mr-1"></i> My Team
                </a>
                <a href="{{ route('reporting.work_reports') }}" class="rep-btn-glass">
                    <i class="fas fa-file-alt mr-1"></i> Daily Work Reports
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards Grid (Responsive 6 -> 3 -> 2) -->
        <div class="rep-metric-grid">
            <div class="rep-metric" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
                <div class="rep-metric-top">
                    <div class="rep-metric-icon"><i class="fas fa-users"></i></div>
                    <div class="rep-metric-value">{{ $employeesCount }}</div>
                </div>
                <div class="rep-metric-label">Total Team</div>
                <div class="rep-metric-line"></div>
            </div>

            <div class="rep-metric" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="rep-metric-top">
                    <div class="rep-metric-icon">
                        <span class="pulse-dot mr-1"></span>
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="rep-metric-value text-success">{{ $presentCount }}</div>
                </div>
                <div class="rep-metric-label">Present Today</div>
                <div class="rep-metric-line"></div>
            </div>

            <div class="rep-metric" style="--metric-color:#0284C7;--metric-soft:#E0F2FE;">
                <div class="rep-metric-top">
                    <div class="rep-metric-icon"><i class="fas fa-laptop-house"></i></div>
                    <div class="rep-metric-value" style="color: #0284C7;">{{ $wfhCount }}</div>
                </div>
                <div class="rep-metric-label">WFH Today</div>
                <div class="rep-metric-line"></div>
            </div>

            <div class="rep-metric" style="--metric-color:#D97706;--metric-soft:#FFFBEB;">
                <div class="rep-metric-top">
                    <div class="rep-metric-icon"><i class="fas fa-umbrella-beach"></i></div>
                    <div class="rep-metric-value text-warning">{{ $onLeaveCount }}</div>
                </div>
                <div class="rep-metric-label">On Leave</div>
                <div class="rep-metric-line"></div>
            </div>

            <div class="rep-metric" style="--metric-color:#9333EA;--metric-soft:#FAF5FF;">
                <div class="rep-metric-top">
                    <div class="rep-metric-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="rep-metric-value" style="color: #9333EA;">{{ $workReportsSubmittedToday }}</div>
                </div>
                <div class="rep-metric-label">Work Reports</div>
                <div class="rep-metric-line"></div>
            </div>

            <div class="rep-metric" style="--metric-color:#2563EB;--metric-soft:#EFF6FF;">
                <div class="rep-metric-top">
                    <div class="rep-metric-icon"><i class="fas fa-diagram-project"></i></div>
                    <div class="rep-metric-value text-primary">{{ $projectsCount }}</div>
                </div>
                <div class="rep-metric-label">Active Projects</div>
                <div class="rep-metric-line"></div>
            </div>
        </div>

        <!-- Team Management Quick Access (Responsive 6 -> 3 -> 2) -->
        <div class="rep-card">
            <div class="rep-section-head">
                <div class="rep-section-title">
                    <span class="rep-section-icon"><i class="fas fa-th-large"></i></span>
                    <span>Team Management Quick Access</span>
                </div>
            </div>

            <div class="p-3">
                <div class="shortcut-grid-6">
                    <a href="{{ route('reporting.my_employees') }}" class="shortcut-btn">
                        <div class="shortcut-icon-box" style="background: #EEF2FF; color: #4F46E5;">
                            <i class="fas fa-users"></i>
                        </div>
                        <span>My Team</span>
                    </a>
                    <a href="{{ route('reporting.attendance') }}" class="shortcut-btn">
                        <div class="shortcut-icon-box" style="background: #ECFDF5; color: #059669;">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <span>Attendance</span>
                    </a>
                    <a href="{{ route('reporting.leave') }}" class="shortcut-btn">
                        <div class="shortcut-icon-box" style="background: #FFFBEB; color: #D97706;">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <span>Team Leave</span>
                    </a>
                    <a href="{{ route('reporting.work_reports') }}" class="shortcut-btn">
                        <div class="shortcut-icon-box" style="background: #EFF6FF; color: #2563EB;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <span>Daily Reports</span>
                    </a>
                    <a href="{{ route('reporting.assignments') }}" class="shortcut-btn">
                        <div class="shortcut-icon-box" style="background: #FAF5FF; color: #9333EA;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span>Supervision</span>
                    </a>
                    <a href="{{ route('reporting.projects') }}" class="shortcut-btn">
                        <div class="shortcut-icon-box" style="background: #EEF2FF; color: #4B00E8;">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <span>Projects & Tasks</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Today's Team Operational Live Status Card -->
        <div class="rep-card">
            <!-- Card Header -->
            <div class="rep-section-head">
                <div class="rep-section-title">
                    <span class="rep-section-icon"><i class="fas fa-stream"></i></span>
                    <span>Today's Team Operational Live Status</span>
                </div>
                <div>
                    <span class="badge badge-light border px-2.5 py-1 font-weight-bold text-muted" style="border-radius: 8px; font-size: 11.5px;">
                        <i class="fas fa-calendar-day mr-1 text-primary"></i> {{ \Carbon\Carbon::now()->format('d M Y') }}
                    </span>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="rep-filter-panel">
                <form method="GET" action="{{ route('reporting.dashboard') }}" id="searchDashboardForm" class="rep-search-form">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 25) }}">
                    <div class="rep-search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" id="filter-search-dashboard" class="rep-search-input" placeholder="Search employee, code, designation..." value="{{ request('search') }}">
                    </div>

                    <div class="rep-filter-actions">
                        <button type="submit" class="rep-search-btn">
                            <i class="fas fa-search"></i>
                            <span>Search</span>
                        </button>
                        <a href="{{ route('reporting.dashboard') }}" class="rep-reset-btn">
                            <i class="fas fa-undo text-muted" style="font-size: 11px;"></i>
                            <span>Reset Filter</span>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Tools Bar (Select2 Per Page + Export Buttons) -->
            <div class="orb-table-tools-bar">
                <div class="orb-table-length-box d-flex align-items-center" style="gap: 8px;">
                    <span class="text-muted font-weight-bold" style="font-size: 13px;">Show</span>
                    <select class="table-per-page-select" id="recordsPerPageSelect">
                        @foreach([10, 25, 50, 100, 250] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span class="text-muted font-weight-bold" style="font-size: 13px;">entries</span>
                </div>

                <div class="orb-table-export-buttons">
                    <div class="orbo-export-group">
                        <button type="button" class="orbo-export-btn btn-export-csv" id="btnExportCSV" title="Export CSV">
                            <i class="fas fa-file-csv icon-csv"></i>
                            <span>CSV</span>
                        </button>
                        <button type="button" class="orbo-export-btn btn-export-excel" id="btnExportExcel" title="Export Excel">
                            <i class="fas fa-file-excel icon-excel"></i>
                            <span>Excel</span>
                        </button>
                        <button type="button" class="orbo-export-btn btn-export-print" id="btnPrint" title="Print">
                            <i class="fas fa-print icon-print"></i>
                            <span>Print</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rep-table-wrap">
                <table class="rep-table" id="dashboardTeamTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 55px;">#</th>
                            <th>Employee Name</th>
                            <th>Designation & Department</th>
                            <th class="text-center">Attendance Today</th>
                            <th class="text-center">Leave Status</th>
                            <th class="text-center">Work Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDevelopers as $item)
                            @php
                                $emp = $item['employee'] ?? $item->employee ?? null;
                                $displayName = $emp->display_name ?? (optional($emp->user ?? null)->name ?? 'Employee');
                                $empCode = $emp->employee_code ?? 'N/A';

                                $att = $item['attendance'] ?? $item->attendance ?? null;
                                $lve = $item['leave'] ?? $item->leave ?? null;
                                $wlog = $item['work_log'] ?? $item->work_log ?? null;

                                $attText = 'NOT PUNCHED';
                                $attClass = 'absent';
                                $attIcon = 'far fa-circle';

                                if ($lve) {
                                    $attText = 'ON LEAVE';
                                    $attClass = 'leave';
                                    $attIcon = 'fas fa-umbrella-beach';
                                } elseif ($att) {
                                    $st = strtolower($att->attendance_status ?? 'present');
                                    $wm = strtoupper($att->work_mode ?? '');
                                    if ($wm === 'WFH') {
                                        $attText = $st === 'half_day' ? 'WFH (HALF DAY)' : 'WFH';
                                        $attClass = 'wfh';
                                        $attIcon = 'fas fa-laptop-house';
                                    } elseif ($st === 'half_day') {
                                        $attText = 'HALF DAY';
                                        $attClass = 'half_day';
                                        $attIcon = 'fas fa-adjust';
                                    } elseif ($st === 'late') {
                                        $attText = 'LATE';
                                        $attClass = 'late';
                                        $attIcon = 'fas fa-clock';
                                    } else {
                                        $attText = 'PRESENT';
                                        $attClass = 'present';
                                        $attIcon = 'fas fa-check-circle';
                                    }
                                }

                                $leaveText = $lve ? 'On Leave' : 'No Leave';

                                $reportText = $wlog ? 'Submitted' : 'Pending';
                                $reportClass = $wlog ? 'submitted' : 'pending';
                            @endphp
                        <tr>
                            <!-- # (Serial Number) -->
                            <td class="text-center font-weight-bold text-muted" style="font-size: 12px;">
                                {{ method_exists($recentDevelopers, 'firstItem') ? ($recentDevelopers->firstItem() + $loop->index) : $loop->iteration }}
                            </td>

                            <!-- Employee Name -->
                            <td>
                                <div>
                                    <strong class="text-dark font-weight-bold d-block" style="line-height: 1.3; font-size: 13.5px;">{{ $displayName }}</strong>
                                    <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 10px; padding: 2px 6px; border-radius: 4px;">{{ $empCode }}</span>
                                </div>
                            </td>

                            <!-- Designation & Department -->
                            <td>
                                <div>
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 12.5px; line-height: 1.2;">
                                        {{ optional($emp->designation ?? null)->name ?? 'Employee' }}
                                    </span>
                                    <small class="text-muted font-weight-bold" style="font-size: 11px;">
                                        <i class="fas fa-building text-muted mr-1" style="font-size: 10px; opacity: 0.7;"></i>{{ optional($emp->department ?? null)->name ?? 'General' }}
                                    </small>
                                </div>
                            </td>

                            <!-- Attendance Today -->
                            <td class="text-center">
                                <span class="status-pill {{ $attClass }}">
                                    <i class="{{ $attIcon }} mr-1"></i> {{ $attText }}
                                </span>
                                @if($att && isset($att->punch_in_time))
                                    <small class="d-block text-muted mt-1 font-weight-bold" style="font-size: 10.5px;">
                                        In: {{ \Carbon\Carbon::parse($att->punch_in_time)->format('h:i A') }}
                                    </small>
                                @endif
                            </td>

                            <!-- Leave Status -->
                            <td class="text-center">
                                @if($lve)
                                    <span class="status-pill leave">
                                        <i class="fas fa-umbrella-beach mr-1"></i> On Leave
                                    </span>
                                @else
                                    <span class="text-muted font-weight-bold" style="font-size: 12px;">No Leave</span>
                                @endif
                            </td>

                            <!-- Work Report Status -->
                            <td class="text-center">
                                <span class="status-pill {{ $reportClass }}">
                                    <i class="fas fa-check-circle mr-1"></i> {{ $reportText }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-users-slash fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                <h5 class="font-weight-bold text-dark">No Active Reporting Employees Found</h5>
                                <p class="small mb-0">Employees under your supervision will appear here once assigned.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Server-side Pagination Bar using Orbo Theme -->
            @if(method_exists($recentDevelopers, 'links'))
            <div>
                {{ $recentDevelopers->appends(request()->query())->links('vendor.pagination.orbo') }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Per Page Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.table-per-page-select').select2({
            minimumResultsForSearch: -1,
            containerCssClass: 'select2-container--per-page',
            dropdownCssClass: 'select2-dropdown-per-page',
            width: '75px'
        });
    }

    // Per-page change handler
    const perPageSelect = document.getElementById('recordsPerPageSelect');
    if (perPageSelect) {
        $(perPageSelect).on('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', this.value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // CSV Export
    $('#btnExportCSV').on('click', function() {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "S.No,Employee Name,Designation,Department,Attendance Today,Leave Status,Work Report\n";
        
        $('#dashboardTeamTable tbody tr').each(function() {
            let row = [];
            $(this).find('td').each(function(idx) {
                let text = $(this).text().replace(/\s+/g, ' ').trim();
                row.push('"' + text.replace(/"/g, '""') + '"');
            });
            if (row.length > 1) {
                csvContent += row.join(",") + "\n";
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "Team_Dashboard_Status_" + new Date().toISOString().slice(0,10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Excel Export (HTML Table format)
    $('#btnExportExcel').on('click', function() {
        let tab_text = "<table border='1px'><tr bgcolor='#4B00E8' style='color:#FFFFFF;'>";
        let table = document.getElementById('dashboardTeamTable');
        
        for (let j = 0; j < table.rows.length; j++) {
            tab_text += table.rows[j].innerHTML + "</tr><tr>";
        }
        tab_text += "</tr></table>";
        tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, "");
        tab_text = tab_text.replace(/<img[^>]*>/gi, "");
        tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, "");

        let sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));
        return (sa);
    });

    // Print
    $('#btnPrint').on('click', function() {
        window.print();
    });
});
</script>
@endsection
