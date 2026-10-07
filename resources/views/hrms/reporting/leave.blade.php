@extends('layouts.panel', ['active' => 'team_leave'])

@section('page_title', 'Team Leave Management')

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

/* Today's Team Leave Status Alert Card */
.today-status-card {
    background: #FFFFFF;
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    padding: 14px 18px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
}

.today-status-title {
    font-size: 12px;
    font-weight: 850;
    color: #1E293B;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.today-leave-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 12px;
    border-radius: 50px;
    background: #FEF3C7;
    color: #92400E;
    border: 1px solid #FCD34D;
    font-size: 11.5px;
    font-weight: 700;
    transition: transform 0.15s ease;
}

.today-leave-pill:hover {
    transform: translateY(-1px);
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
    flex: 1 1 230px;
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

.rep-filter-col {
    flex: 1 1 170px;
    min-width: 150px;
}

.rep-filter-col-sm {
    flex: 1 1 150px;
    min-width: 130px;
}

.rep-filter-col-lg {
    flex: 1 1 200px;
    min-width: 170px;
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
    flex-wrap: wrap;
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
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(75, 0, 232, 0.22);
    transition: all 0.2s ease;
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
    font-weight: 750;
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

.select2-container--per-page .select2-selection--single {
    height: 34px !important;
    border-radius: 8px !important;
    padding: 0 6px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    min-width: 65px !important;
}

.select2-container--per-page .select2-selection__arrow {
    height: 32px !important;
    right: 4px !important;
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
}

.rep-table thead th {
    background: #F8FAFC;
    color: #475569;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 12px 14px;
    border-top: 1px solid var(--orb-border);
    border-bottom: 1px solid var(--orb-border);
    white-space: nowrap;
    vertical-align: middle;
}

.rep-table tbody td {
    padding: 13px 14px;
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

/* Employee Name Link & Code Badge */
.emp-name-link {
    font-weight: 750;
    color: #0F172A;
    font-size: 13px;
    line-height: 1.25;
    display: block;
    text-decoration: none !important;
}

.emp-name-link:hover {
    color: var(--orb-primary);
}

.emp-code-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 700;
    color: #64748B;
    background: #F1F5F9;
    padding: 1px 6px;
    border-radius: 6px;
    margin-top: 2px;
}

/* Action Dots */
.btn-action-dots {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    color: #64748B;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-action-dots:hover {
    background: #F1F5F9;
    color: #0F172A;
    border-color: #CBD5E1;
}

.dropdown-menu-action {
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.1);
    padding: 6px;
    min-width: 190px;
}

.dropdown-menu-action .dropdown-item {
    font-size: 12.5px;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #334155;
}

.dropdown-menu-action .dropdown-item:hover {
    background: #F8FAFC;
    color: var(--orb-primary);
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">

        <!-- 1. Header Banner -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-plane-departure"></i> Team Leave Workbench
                </div>
                <h3 class="rep-hero-title">Team Leave Management</h3>
                <div class="rep-hero-subtitle">Monitor leave requests, manage multi-stage approvals (Manager &rarr; HR), and track team availability.</div>
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

        <!-- 2. 5 KPI Summary Cards Grid -->
        <div class="team-metric-grid">
            <!-- Total Pending -->
            <div class="team-metric-card" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-clock"></i></div>
                    <div class="team-metric-value text-primary">{{ $totalPendingCount ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Total Pending</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- Pending Manager -->
            <div class="team-metric-card" style="--metric-color:#D97706;--metric-soft:#FFFBEB;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="team-metric-value text-warning">{{ $managerPendingCount ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Pending Manager</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- Pending HR -->
            <div class="team-metric-card" style="--metric-color:#2563EB;--metric-soft:#EFF6FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="team-metric-value text-info">{{ $hrPendingCount ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Pending HR</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- Approved -->
            <div class="team-metric-card" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="team-metric-value text-success">{{ $approvedLeaveCount ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Approved</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- Rejected -->
            <div class="team-metric-card" style="--metric-color:#DC2626;--metric-soft:#FEF2F2;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="team-metric-value text-danger">{{ $rejectedLeaveCount ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Rejected</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        <!-- 3. Today's Team Leave Status Alert Card -->
        <div class="today-status-card">
            <div class="today-status-title">
                <i class="fas fa-calendar-day text-primary" style="font-size: 13px;"></i>
                <span>Today's Team Leave Status ({{ \Carbon\Carbon::now()->format('d M Y') }})</span>
            </div>
            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                @if(!empty($todayLeaves) && count($todayLeaves) > 0)
                    @foreach($todayLeaves as $tl)
                        <div class="today-leave-pill">
                            <i class="fas fa-user-clock text-warning"></i>
                            <span>{{ $tl->display_name }}</span>
                            <span class="text-muted font-weight-normal">&bull;</span>
                            <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 10px; border-radius: 6px;">{{ $tl->leave_type_name ?? 'Leave' }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="p-2 px-3 bg-light border text-muted font-weight-bold d-inline-flex align-items-center" style="border-radius: 10px; font-size: 12px; gap: 6px;">
                        <i class="fas fa-check-circle text-success" style="font-size: 13px;"></i>
                        <span>No team members are on leave today. All active members are available.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 4. Main Attached Table Card (Header + Filter Bar + Table Tools + Table + Pagination) -->
        <div class="rep-card">
            <!-- Attached Section Header -->
            <div class="rep-section-head">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="rep-section-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h4 class="rep-section-title">Leave Approval Workbench</h4>
                        <small class="text-muted font-weight-bold" style="font-size: 11.5px;">All employee leave applications & approval timelines</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background: var(--orb-soft); color: var(--orb-primary); font-size: 12px; font-weight: 800; padding: 6px 12px; border-radius: 10px; border: 1px solid rgba(75, 0, 232, 0.15);">
                        <i class="fas fa-file-alt mr-1"></i> {{ $leaveRequests->total() }} Total Applications
                    </span>
                </div>
            </div>

            <!-- Attached Filter Bar Form -->
            <form method="GET" action="{{ route('reporting.leave') }}" id="leaveFilterForm">
                <input type="hidden" name="per_page" id="filterPerPageHidden" value="{{ request('per_page', 25) }}">

                <div class="rep-filter-bar">
                    <div class="rep-filter-row">
                        <!-- 1. Search Keyword -->
                        <div class="rep-filter-col-search">
                            <label class="rep-filter-label"><i class="fas fa-search text-muted mr-1"></i> Search</label>
                            <div class="rep-search-input-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" id="filter-search" class="rep-search-input" value="{{ request('search') }}" placeholder="Search name, code, reason...">
                            </div>
                        </div>

                        <!-- 2. Status / Stage -->
                        <div class="rep-filter-col">
                            <label class="rep-filter-label"><i class="fas fa-filter text-muted mr-1"></i> Status / Stage</label>
                            <select name="status" id="filter-status" class="select2-filter">
                                <option value="" {{ request('status') == '' ? 'selected' : '' }}>Pending Requests</option>
                                <option value="pending_manager" {{ request('status') == 'pending_manager' ? 'selected' : '' }}>Pending Manager</option>
                                <option value="pending_hr" {{ request('status') == 'pending_hr' ? 'selected' : '' }}>Pending HR</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Null & Void</option>
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Requests</option>
                            </select>
                        </div>

                        <!-- 3. Employee -->
                        <div class="rep-filter-col-lg">
                            <label class="rep-filter-label"><i class="fas fa-user text-muted mr-1"></i> Employee</label>
                            <select name="employee_id" id="filter-employee" class="select2-filter">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 4. Leave Type -->
                        <div class="rep-filter-col-sm">
                            <label class="rep-filter-label"><i class="fas fa-tag text-muted mr-1"></i> Leave Type</label>
                            <select name="leave_type_id" id="filter-leave-type" class="select2-filter">
                                <option value="">All Leave Types</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                        {{ $lt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 5. Right Action Buttons -->
                        <div class="rep-filter-actions-right">
                            <button type="submit" class="rep-search-btn">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('reporting.leave') }}" class="rep-reset-btn" title="Reset Filters">
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
                <table class="rep-table" id="teamLeaveTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Employee</th>
                            <th>Reporting Manager</th>
                            <th>Leave Type</th>
                            <th class="text-center">Leave Period</th>
                            <th class="text-center">Days</th>
                            <th class="text-center">Manager Approval</th>
                            <th class="text-center">HR Approval</th>
                            <th class="text-center">Overall Status</th>
                            <th class="text-center" style="width: 60px;">⋮</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $lr)
                            @php
                                $ltName = $lr->leave_type_name ?? 'Leave';
                                $ltLower = strtolower($ltName);
                                $ltStyle = match(true) {
                                    str_contains($ltLower, 'sick') => 'background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5;',
                                    str_contains($ltLower, 'casual') => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;',
                                    str_contains($ltLower, 'comp') => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
                                    str_contains($ltLower, 'earned') || str_contains($ltLower, 'privilege') => 'background: #EFF6FF; color: #1E40AF; border: 1px solid #93C5FD;',
                                    default => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;'
                                };

                                $stLower = strtolower(trim($lr->status ?? 'pending'));
                                $startDateFormatted = \Carbon\Carbon::parse($lr->start_date)->format('d M Y');
                                $endDateFormatted = \Carbon\Carbon::parse($lr->end_date)->format('d M Y');
                                $isSingleDay = ($lr->start_date === $lr->end_date);

                                $daysVal = (float)($lr->requested_days ?? $lr->deducted_days ?? 1);
                                $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);

                                $managerEmpId = $lr->current_reporting_manager_id ?? $lr->reporting_manager_employee_id;
                                $hasManager = !empty($managerEmpId);

                                $user = auth()->user();
                                $userEmpId = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->value('id');
                                $isAssignedManager = (!empty($managerEmpId) && (int)$managerEmpId === (int)$userEmpId);
                                $isSuperAdminUser = method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : (in_array((int)($user->system_role_id ?? $user->role_id ?? 0), [1, 2], true));

                                $mgrApproved = !empty($lr->manager_approved_by) || !empty($lr->manager_approved_at) || ($lr->approval_level === 'manager_approved');
                                $mgrRejected = ($stLower === 'rejected' && empty($lr->manager_approved_by));
                                $hrApproved = ($stLower === 'approved');
                                $hrRejected = ($stLower === 'rejected' && !empty($lr->manager_approved_by));
                            @endphp
                        <tr>
                            <!-- 1. S.No -->
                            <td class="text-center font-weight-bold text-muted" style="font-size: 12px;">
                                {{ ($leaveRequests->currentPage() - 1) * $leaveRequests->perPage() + $loop->iteration }}
                            </td>

                            <!-- 2. Employee -->
                            <td>
                                <div>
                                    @if(Route::has('employees.show'))
                                        <a href="{{ route('employees.show', $lr->employee_id) }}" class="emp-name-link">
                                            {{ $lr->display_name }}
                                        </a>
                                    @else
                                        <span class="emp-name-link">{{ $lr->display_name }}</span>
                                    @endif
                                    <div class="emp-code-badge">
                                        <i class="fas fa-id-badge text-muted" style="font-size: 10px;"></i> {{ $lr->employee_code }}
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Reporting Manager -->
                            <td>
                                @if(!empty($lr->reporting_manager_name))
                                    <div>
                                        <strong class="text-dark font-weight-bold d-block" style="font-size: 12.5px; line-height: 1.2;">
                                            {{ $lr->reporting_manager_name }}
                                        </strong>
                                    </div>
                                @else
                                    <span class="small text-muted font-weight-bold">Not Assigned</span>
                                @endif
                            </td>

                            <!-- 4. Leave Type -->
                            <td>
                                <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 6px; font-size: 11px; {{ $ltStyle }}">
                                    {{ $ltName }}
                                </span>
                            </td>

                            <!-- 5. Leave Period -->
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center bg-light px-2.5 py-1" style="border-radius: 8px; border: 1px solid #E2E8F0; font-size: 11.5px; font-weight: 600; color: #1E293B;">
                                    @if($isSingleDay)
                                        <span>{{ $startDateFormatted }}</span>
                                    @else
                                        <span>{{ $startDateFormatted }}</span>
                                        <i class="fas fa-arrow-right text-muted" style="font-size: 9px; margin: 0 6px;"></i>
                                        <span>{{ $endDateFormatted }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 6. Days -->
                            <td class="text-center">
                                <span class="badge badge-light border font-weight-bold px-2.5 py-1 text-dark" style="border-radius: 6px; font-size: 11px;">
                                    {{ $daysText }}
                                </span>
                            </td>

                            <!-- 7. Manager Approval -->
                            <td class="text-center">
                                @if($hrApproved)
                                    @if($hasManager && $mgrApproved)
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                            ✓ Approved
                                        </span>
                                        @if(!empty($lr->manager_approver_name))
                                            <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->manager_approver_name }}</small>
                                        @endif
                                    @else
                                        <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #F8FAFC; color: #64748B;">⚪ NOT REQUIRED</span>
                                    @endif
                                @elseif($mgrApproved)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                        ✓ Approved
                                    </span>
                                    @if(!empty($lr->manager_approver_name))
                                        <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->manager_approver_name }}</small>
                                    @endif
                                @elseif($mgrRejected)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                        ✕ Rejected
                                    </span>
                                    @if(!empty($lr->rejected_by_name))
                                        <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->rejected_by_name }}</small>
                                    @endif
                                @else
                                    @if($hasManager)
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                            🟠 PENDING
                                        </span>
                                    @else
                                        <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #F8FAFC; color: #64748B;">⚪ NOT REQUIRED</span>
                                    @endif
                                @endif
                            </td>

                            <!-- 8. HR Approval -->
                            <td class="text-center">
                                @if($hrApproved)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                        ✓ Approved
                                    </span>
                                    @if(!empty($lr->hr_approver_name))
                                        <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->hr_approver_name }}</small>
                                    @endif
                                @elseif($hrRejected)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                        ✕ Rejected
                                    </span>
                                    @if(!empty($lr->rejected_by_name))
                                        <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->rejected_by_name }}</small>
                                    @endif
                                @elseif($stLower === 'pending')
                                    @if($hasManager && !$mgrApproved)
                                        <span class="text-muted small" style="font-size: 11px;">— Waiting Manager</span>
                                    @else
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;">
                                            🔵 PENDING HR
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted" style="font-size: 11px;">—</span>
                                @endif
                            </td>

                            <!-- 9. Overall Status -->
                            <td class="text-center">
                                @if($stLower === 'approved')
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                        🟢 APPROVED
                                    </span>
                                @elseif($stLower === 'void')
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">
                                        ⚪ NULL & VOID
                                    </span>
                                @elseif($stLower === 'rejected' || $stLower === 'cancelled')
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                        🔴 REJECTED
                                    </span>
                                @elseif($stLower === 'pending')
                                    @if($hasManager && !$mgrApproved)
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                            🟠 PENDING MANAGER
                                        </span>
                                    @else
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;">
                                            🔵 PENDING HR
                                        </span>
                                    @endif
                                @else
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                        🟠 PENDING
                                    </span>
                                @endif
                            </td>

                            <!-- 10. Actions Three-Dot Column (⋮) -->
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn-action-dots" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-action">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#viewModal{{ $lr->id }}">
                                            <i class="fas fa-eye text-primary"></i> View Details
                                        </a>

                                        @if($stLower === 'pending' && Route::has('leave-approvals.approve'))
                                            @if($isAssignedManager)
                                                @if(!$mgrApproved)
                                                    <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success border-0 bg-transparent font-weight-bold" onclick="return confirm('Approve leave request at Manager stage?')">
                                                            <i class="fas fa-check-circle text-success"></i> Approve Request
                                                        </button>
                                                    </form>
                                                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}">
                                                        <i class="fas fa-times-circle text-danger"></i> Reject Request
                                                    </a>
                                                @else
                                                    <span class="dropdown-item text-muted disabled" style="cursor: not-allowed; opacity: 0.8;">
                                                        <i class="fas fa-check text-success"></i> Approved by You (Sent to HR)
                                                    </span>
                                                @endif
                                            @elseif($isSuperAdminUser)
                                                <!-- Super Admin Override Actions -->
                                                <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success border-0 bg-transparent font-weight-bold" onclick="return confirm('Super Admin Override: Approve leave request?')">
                                                        <i class="fas fa-crown text-warning"></i> Super Admin Approve
                                                    </button>
                                                </form>
                                                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}">
                                                    <i class="fas fa-times-circle text-danger"></i> Reject Request
                                                </a>
                                            @else
                                                @if($hasManager && !$mgrApproved)
                                                    <span class="dropdown-item text-muted disabled" style="cursor: not-allowed; opacity: 0.8;">
                                                        <i class="fas fa-clock text-warning"></i> Waiting for Manager
                                                    </span>
                                                @else
                                                    <span class="dropdown-item text-muted disabled" style="cursor: not-allowed; opacity: 0.8;">
                                                        <i class="fas fa-hourglass-half text-primary"></i> Pending HR Final Approval
                                                    </span>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-minus fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                <h5 class="font-weight-bold text-dark">No Team Leave Applications Found</h5>
                                <p class="small mb-0">No leave requests match the selected filters.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination in Card Footer -->
            @if(method_exists($leaveRequests, 'links'))
                <div class="px-3 py-2 border-top">
                    {{ $leaveRequests->appends(request()->query())->links('vendor.pagination.orbo') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS PLACED OUTSIDE THE TABLE FOR CLEAN DOM HIERARCHY -->
<!-- ========================================================================= -->
@foreach($leaveRequests as $lr)
    @php
        $ltName = $lr->leave_type_name ?? 'Leave';
        $ltLower = strtolower($ltName);
        $ltStyle = match(true) {
            str_contains($ltLower, 'sick') => 'background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5;',
            str_contains($ltLower, 'casual') => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;',
            str_contains($ltLower, 'comp') => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
            str_contains($ltLower, 'earned') || str_contains($ltLower, 'privilege') => 'background: #EFF6FF; color: #1E40AF; border: 1px solid #93C5FD;',
            default => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;'
        };

        $stLower = strtolower(trim($lr->status ?? 'pending'));
        $startDateFormatted = \Carbon\Carbon::parse($lr->start_date)->format('d M Y');
        $endDateFormatted = \Carbon\Carbon::parse($lr->end_date)->format('d M Y');
        $isSingleDay = ($lr->start_date === $lr->end_date);

        $daysVal = (float)($lr->requested_days ?? $lr->deducted_days ?? 1);
        $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);

        $managerEmpId = $lr->current_reporting_manager_id ?? $lr->reporting_manager_employee_id;
        $hasManager = !empty($managerEmpId);

        $user = auth()->user();
        $userEmpId = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->value('id');
        $isAssignedManager = (!empty($managerEmpId) && (int)$managerEmpId === (int)$userEmpId);
        $isSuperAdminUser = method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : (in_array((int)($user->system_role_id ?? $user->role_id ?? 0), [1, 2], true));

        $mgrApproved = !empty($lr->manager_approved_by) || !empty($lr->manager_approved_at) || ($lr->approval_level === 'manager_approved');
        $mgrRejected = ($stLower === 'rejected' && empty($lr->manager_approved_by));
        $hrApproved = ($stLower === 'approved');
        $hrRejected = ($stLower === 'rejected' && !empty($lr->manager_approved_by));
    @endphp

    <!-- VIEW TIMELINE & DETAILS MODAL -->
    <div class="modal fade" id="viewModal{{ $lr->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 800px; width: 94%;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden; max-height: 85vh; display: flex; flex-direction: column; background: #FFFFFF;">
                
                <!-- Dynamic Header -->
                <div class="modal-header text-white px-4 py-3 align-items-center justify-content-between" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); height: 58px; flex-shrink: 0; border-radius: 18px 18px 0 0;">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); border: 1px solid rgba(255, 255, 255, 0.3); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                            <i class="fas fa-calendar-check text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 15px; letter-spacing: 0.2px;">
                                Leave Request Details
                            </h5>
                            <div class="text-white-50" style="font-size: 10.5px; font-weight: 500; opacity: 0.92;">
                                Request ID: #LR-{{ str_pad($lr->id, 4, '0', STR_PAD_LEFT) }} &bull; Submitted {{ \Carbon\Carbon::parse($lr->created_at)->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    <button type="button" class="close text-white opacity-10 border-0" style="width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 18px; outline: none; line-height: 1;" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Scrollable Modal Body -->
                <div class="modal-body px-4 py-3" style="overflow-y: auto; flex: 1; background: #F8FAFC;">
                    
                    <!-- 3-Column Information Grid -->
                    <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        
                        <!-- Employee Card -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">EMPLOYEE</div>
                                <div class="font-weight-bold text-dark text-truncate" style="font-size: 13px; line-height: 1.2;">{{ $lr->display_name }}</div>
                                <div class="text-muted font-weight-bold" style="font-size: 10px;">{{ $lr->employee_code }}</div>
                            </div>
                        </div>

                        <!-- Department & Designation -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(75, 0, 232, 0.08); color: var(--orb-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">ORGANIZATION</div>
                                <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px; line-height: 1.2;">{{ $lr->department_name ?? 'General' }}</div>
                                <div class="text-muted font-weight-bold text-truncate" style="font-size: 10.5px;">{{ $lr->designation_name ?? 'Employee' }}</div>
                            </div>
                        </div>

                        <!-- Leave Type -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #F3E8FF; color: #9333EA; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">LEAVE TYPE</div>
                                <div class="mt-0.5">
                                    <span class="badge font-weight-bold px-2.5 py-0.5" style="border-radius: 6px; font-size: 10.5px; {{ $ltStyle }}">
                                        {{ $ltName }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Period -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEF2F2; color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">LEAVE PERIOD</div>
                                <div class="font-weight-bold text-dark mt-0.5" style="font-size: 12px; line-height: 1.2;">
                                    @if($isSingleDay)
                                        {{ $startDateFormatted }}
                                    @else
                                        {{ $startDateFormatted }} — {{ $endDateFormatted }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="far fa-clock"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">DURATION</div>
                                <div class="font-weight-bold text-success mt-0.5" style="font-size: 12.5px; line-height: 1.2;">
                                    {{ $daysText }}
                                </div>
                            </div>
                        </div>

                        <!-- Reporting Manager -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">REPORTING MANAGER</div>
                                <div class="font-weight-bold text-dark mt-0.5 text-truncate" style="font-size: 12px; line-height: 1.2;">
                                    {{ $lr->reporting_manager_name ?? '— Not Assigned' }}
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Reason -->
                    @if(!empty($lr->reason))
                        <div class="p-3 bg-white border rounded-lg mb-3" style="border-radius: 10px;">
                            <strong class="text-muted font-weight-bold d-block uppercase" style="font-size: 10px;">LEAVE REASON</strong>
                            <p class="mb-0 text-dark font-weight-500 mt-1" style="font-size: 12.5px;">{{ $lr->reason }}</p>
                        </div>
                    @endif

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-top bg-white px-4 py-2.5 align-items-center justify-content-between" style="height: 58px; flex-shrink: 0; border-radius: 0 0 18px 18px;">
                    <button type="button" class="btn btn-sm btn-light font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; border: 1px solid #CBD5E1; color: #475569; font-size: 12.5px;" data-dismiss="modal">
                        Close
                    </button>

                    <div class="d-flex align-items-center" style="gap: 8px;">
                        @if($stLower === 'pending' && Route::has('leave-approvals.approve'))
                            @if($isAssignedManager)
                                @if(!$mgrApproved)
                                    <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; font-size: 12.5px;" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}" data-dismiss="modal">
                                        <i class="fas fa-times mr-1"></i> Reject Request
                                    </button>
                                    <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm text-white font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.3); font-size: 12.5px;" onclick="return confirm('Approve leave request at Manager stage for {{ addslashes($lr->display_name) }}?')">
                                            <i class="fas fa-check mr-1"></i> Approve Request
                                        </button>
                                    </form>
                                @endif
                            @elseif($isSuperAdminUser)
                                <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; font-size: 12.5px;" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i> Reject Request
                                </button>
                                <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm text-white font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.3); font-size: 12.5px;" onclick="return confirm('Super Admin Override: Approve leave request for {{ addslashes($lr->display_name) }}?')">
                                        <i class="fas fa-crown mr-1 text-warning"></i> Super Admin Approve
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- REJECT MODAL -->
    @if(Route::has('leave-approvals.reject'))
    <div class="modal fade" id="rejectModal{{ $lr->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 460px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
                <form method="POST" action="{{ route('leave-approvals.reject', $lr->id) }}">
                    @csrf
                    <div class="modal-header bg-danger text-white p-3">
                        <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 15px;">
                            <i class="fas fa-times-circle mr-2"></i> Reject Leave Request
                        </h5>
                        <button type="button" class="close text-white opacity-10" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-muted font-weight-bold mb-2">Rejecting request for <strong>{{ $lr->display_name }}</strong> ({{ $startDateFormatted }}@if(!$isSingleDay) to {{ $endDateFormatted }}@endif):</p>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide reason for rejection..." style="border-radius: 10px; font-size: 13px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3 border-top">
                        <button type="button" class="btn btn-light border font-weight-bold" style="border-radius: 9px;" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger font-weight-bold px-4" style="border-radius: 9px;">Reject Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
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

    // 2. Per-page change handler
    $('#recordsPerPageSelect').on('change', function() {
        $('#filterPerPageHidden').val(this.value);
        $('#leaveFilterForm').submit();
    });

    // Helper to construct full export URL with all current filters
    function getExportUrl(format) {
        var formEl = document.getElementById('leaveFilterForm');
        var formData = new FormData(formEl);
        var params = new URLSearchParams();
        
        for (var pair of formData.entries()) {
            if (pair[1] !== null && pair[1] !== '') {
                params.append(pair[0], pair[1]);
            }
        }
        params.set('export', format);
        return "{{ route('reporting.leave') }}?" + params.toString();
    }

    // 3. CSV Export (Full Dataset)
    $('#btnExportCSV').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        window.location.href = getExportUrl('csv');
    });

    // 4. Excel Export (Full Dataset)
    $('#btnExportExcel').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        window.location.href = getExportUrl('excel');
    });

    // 5. PDF Export (Full Dataset)
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
                    { text: 'REPORTING MANAGER', style: 'tableHeader' },
                    { text: 'LEAVE TYPE', style: 'tableHeader', alignment: 'center' },
                    { text: 'LEAVE PERIOD', style: 'tableHeader', alignment: 'center' },
                    { text: 'DAYS', style: 'tableHeader', alignment: 'center' },
                    { text: 'MANAGER APPROVAL', style: 'tableHeader', alignment: 'center' },
                    { text: 'HR APPROVAL', style: 'tableHeader', alignment: 'center' },
                    { text: 'OVERALL STATUS', style: 'tableHeader', alignment: 'center' }
                ]
            ];

            res.data.forEach(function(item) {
                var empText = item.employee_name + (item.employee_code ? '\n' + item.employee_code : '') + (item.designation ? '\n' + item.designation : '');

                body.push([
                    { text: String(item.sr_no), alignment: 'center', style: 'tableCell' },
                    { text: empText, style: 'tableCell' },
                    { text: item.reporting_manager || '—', style: 'tableCell' },
                    { text: item.leave_type || '—', alignment: 'center', style: 'tableCell' },
                    { text: item.leave_period || '—', alignment: 'center', style: 'tableCell' },
                    { text: item.days || '1 Day', alignment: 'center', style: 'tableCell' },
                    { text: item.manager_approval || 'Pending', alignment: 'center', style: 'tableCell' },
                    { text: item.hr_approval || 'Pending', alignment: 'center', style: 'tableCell' },
                    { text: item.overall_status || 'PENDING', alignment: 'center', style: 'tableCell', bold: true }
                ]);
            });

            var docDefinition = {
                pageOrientation: 'landscape',
                pageSize: 'A4',
                pageMargins: [18, 18, 18, 18],
                content: [
                    { text: 'Team Leave Applications - Full Report', style: 'header' },
                    { text: 'Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString() + ' | Total Records: ' + res.total, style: 'subHeader' },
                    {
                        table: {
                            headerRows: 1,
                            widths: ['4%', '18%', '14%', '10%', '15%', '7%', '11%', '11%', '10%'],
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

            pdfMake.createPdf(docDefinition).download("Team_Leave_Applications_" + new Date().toISOString().slice(0,10) + ".pdf");
        }).fail(function() {
            $btn.html(origHtml).prop('disabled', false);
            alert('Failed to fetch full leave data for PDF export.');
        });
    });

    // 6. Print (Full Dataset via isolated iframe to prevent duplicate dialogs)
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
                rowsHtml += '<tr>' +
                    '<td style="text-align:center;">' + item.sr_no + '</td>' +
                    '<td><strong>' + item.employee_name + '</strong><br><small style="color:#64748B;">' + item.employee_code + (item.designation ? ' • ' + item.designation : '') + '</small></td>' +
                    '<td>' + (item.reporting_manager || '—') + '</td>' +
                    '<td style="text-align:center;">' + (item.leave_type || '—') + '</td>' +
                    '<td style="text-align:center;">' + (item.leave_period || '—') + '</td>' +
                    '<td style="text-align:center;">' + (item.days || '—') + '</td>' +
                    '<td style="text-align:center;">' + (item.manager_approval || 'Pending') + '</td>' +
                    '<td style="text-align:center;">' + (item.hr_approval || 'Pending') + '</td>' +
                    '<td style="text-align:center;font-weight:bold;">' + (item.overall_status || 'PENDING') + '</td>' +
                    '</tr>';
            });

            var iframe = document.getElementById('leavePrintFrame');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'leavePrintFrame';
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
            doc.write('<!DOCTYPE html><html><head><title>Team Leave Applications</title>');
            doc.write('<style>');
            doc.write('@page { size: landscape A4; margin: 8mm; }');
            doc.write('body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; padding: 12px; color: #101828; margin: 0; }');
            doc.write('h2 { font-size: 16px; margin: 0 0 3px 0; color: #101828; }');
            doc.write('p { font-size: 10px; color: #64748B; margin: 0 0 10px 0; }');
            doc.write('table { width: 100%; border-collapse: collapse; font-size: 10px; }');
            doc.write('th, td { border: 1px solid #CBD5E1; padding: 6px 7px; text-align: left; vertical-align: middle; }');
            doc.write('th { background-color: #4B00E8; color: #ffffff; font-weight: 700; text-transform: uppercase; font-size: 9px; }');
            doc.write('tr:nth-child(even) { background-color: #FAFBFF; }');
            doc.write('</style></head><body>');
            doc.write('<h2>Team Leave Applications - Full Dataset</h2>');
            doc.write('<p>Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString() + ' | Total Records: ' + res.total + '</p>');
            doc.write('<table><thead><tr><th style="width:28px;text-align:center;">#</th><th>Employee</th><th>Reporting Manager</th><th style="text-align:center;">Leave Type</th><th style="text-align:center;">Leave Period</th><th style="text-align:center;">Days</th><th style="text-align:center;">Manager Approval</th><th style="text-align:center;">HR Approval</th><th style="text-align:center;">Overall Status</th></tr></thead><tbody>' + rowsHtml + '</tbody></table>');
            doc.write('</body></html>');
            doc.close();

            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 250);
        }).fail(function() {
            $btn.html(origHtml).prop('disabled', false);
            alert('Failed to fetch full leave data for Print.');
        });
    });
});
</script>
@endsection
