@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'attendances'])

@section('page_title', 'Team Attendance (Login & Logout)')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
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

    .team-att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 14px 16px 36px;
        font-family: 'Outfit', sans-serif;
    }

    .team-att-container {
        max-width: 100% !important;
        width: 100%;
        margin: 0 auto;
    }

    /* Hero Banner */
    .team-hero {
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

    .team-hero:before {
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

    .team-hero-kicker {
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

    .team-hero-title {
        font-size: 26px;
        font-weight: 900;
        margin: 0;
        line-height: 1.15;
        color: #ffffff;
    }

    .team-hero-subtitle {
        font-size: 13.5px;
        font-weight: 500;
        margin-top: 6px;
        opacity: .92;
        max-width: 800px;
    }

    .team-hero-badge {
        background: rgba(255, 255, 255, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        color: #ffffff !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        border-radius: 999px !important;
        padding: 8px 18px !important;
        font-size: 13px !important;
        font-weight: 750 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        white-space: nowrap;
    }

    /* View Switcher Bar */
    .view-switcher-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        background: #FFFFFF;
        padding: 8px 14px;
        border-radius: 16px;
        border: 1px solid var(--orb-border);
        box-shadow: 0 4px 14px rgba(16, 24, 40, 0.04);
        flex-wrap: wrap;
    }

    .view-tab-btn {
        padding: 8px 18px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 750;
        color: var(--orb-muted);
        border: none;
        background: transparent;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none !important;
    }

    .view-tab-btn:hover {
        color: var(--orb-primary);
        background: #F1F5F9;
    }

    .view-tab-btn.active {
        background: var(--orb-primary);
        color: #FFFFFF;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25);
    }

    /* Metric Cards Grid */
    .team-metric-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .team-metric {
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

    .team-metric:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 24, 40, .09);
    }

    .team-metric:after {
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
    .team-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--orb-shadow);
        width: 100%;
        margin-bottom: 24px;
    }

    .team-section-head {
        padding: 16px 20px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #fff, #FAFBFF);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .team-section-title {
        font-size: 17px;
        font-weight: 850;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .team-section-title i {
        color: var(--orb-primary);
    }

    .team-section-sub {
        font-size: 12.5px;
        color: var(--orb-muted);
        font-weight: 550;
        margin-top: 3px;
    }

    /* Filter Grid */
    .team-filter-panel {
        padding: 16px 20px;
        border-bottom: 1px solid var(--orb-border);
        background: #FFFFFF;
    }

    .team-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .team-filter-group label {
        font-size: 10.5px;
        font-weight: 850;
        text-transform: uppercase;
        color: #667085;
        margin-bottom: 6px;
        display: block;
        letter-spacing: .04em;
    }

    .team-filter-group .form-control,
    .team-filter-group .filter-input {
        height: 42px;
        border-radius: 12px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        font-weight: 650;
        padding: 0 12px;
        box-shadow: none !important;
        background: #fff;
        width: 100%;
    }

    .team-filter-group .form-control:focus,
    .team-filter-group .filter-input:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .10) !important;
    }

    /* Select2 Skin */
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #E4E7EC !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        background-color: #fff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: #101828 !important;
        padding-left: 0 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }

    .select2-dropdown {
        border: 1px solid #E4E7EC !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        font-size: 13px !important;
        font-weight: 550 !important;
        z-index: 9999 !important;
    }

    .select2-search--dropdown .select2-search__field {
        border-radius: 8px !important;
        border: 1px solid #E4E7EC !important;
        padding: 6px 10px !important;
    }

    /* Compact Select2 for Table Length Entries */
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

    /* Table Toolbar */
    .orb-table-tools-bar {
        padding: 10px 20px;
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
        gap: 10px;
    }

    .orb-table-export-buttons {
        display: flex;
        align-items: center;
        gap: 8px;
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

    /* Table & Scroll */
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

    .team-table {
        width: 100% !important;
        min-width: 1080px;
        border-collapse: separate !important;
        border-spacing: 0;
        margin: 0 !important;
    }

    .team-table thead th {
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

    .team-table tbody td {
        background: #fff;
        border-bottom: 1px solid #F2F4F7 !important;
        padding: 11px 14px !important;
        vertical-align: middle !important;
        white-space: nowrap;
        font-size: 13px;
        color: #1E293B;
    }

    .team-table tbody tr:hover td {
        background: #FCFAFF !important;
    }

    /* Employee Cell */
    .team-emp-box {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .avatar-box {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: linear-gradient(135deg, #EEF2FF, #E0E7FF);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 850;
        font-size: 13.5px;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, 0.1);
    }

    /* Badges */
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

    .badge-unlocked {
        background: #D1FAE5;
        color: #065F46;
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

    .status-badge {
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

    .status-working {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }

    .status-completed {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }

    .status-late {
        background: #FFFBEB;
        color: #B45309;
        border: 1px solid #FDE68A;
    }

    .status-wfh {
        background: #F5F3FF;
        color: #6D28D9;
        border: 1px solid #DDD6FE;
    }

    .status-leave {
        background: #FAF5FF;
        color: #7E22CE;
        border: 1px solid #E9D5FF;
    }

    .status-absent {
        background: #FEF2F2;
        color: #B91C1C;
        border: 1px solid #FECACA;
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

    @media (max-width: 1400px) {
        .team-metric-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .team-hero {
            padding: 20px;
        }
        .team-hero-title {
            font-size: 22px;
        }
    }

    @media (max-width: 768px) {
        .team-att-page {
            padding: 10px 10px 30px;
        }
        .team-hero {
            padding: 18px 16px;
            border-radius: 18px;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        .team-hero-title {
            font-size: 20px;
        }
        .team-hero-subtitle {
            font-size: 12px;
        }
        .team-hero-badge {
            font-size: 12px !important;
            padding: 6px 14px !important;
        }
        .view-switcher-bar {
            padding: 6px 8px;
            gap: 6px;
        }
        .view-tab-btn {
            padding: 7px 12px;
            font-size: 12px;
            flex: 1;
            justify-content: center;
        }
        .team-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .team-metric {
            padding: 10px 12px;
            min-height: 80px;
            border-radius: 14px;
        }
        .team-metric-value {
            font-size: 20px;
        }
        .team-metric-label {
            font-size: 10px;
            margin-top: 6px;
        }
        .team-filter-panel {
            padding: 12px 14px;
        }
        .team-filter-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .orb-table-tools-bar {
            padding: 10px 14px;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .orb-table-export-buttons {
            width: 100%;
            justify-content: flex-start;
            overflow-x: auto;
        }
    }

    @media (max-width: 480px) {
        .view-switcher-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .view-tab-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>
@endsection

@section('_content')
<div class="team-att-page">
    <div class="team-att-container">
        
        <!-- Hero Header -->
        <div class="team-hero">
            <div>
                <div class="team-hero-kicker">
                    <i class="fas fa-users-cog"></i> TEAM ATTENDANCE & LOGS
                </div>
                <h3 class="team-hero-title">Team Attendance & Login Tracking</h3>
                <div class="team-hero-subtitle">Real-time team login, logout, working hours, work mode, and attendance logs.</div>
            </div>
            <div>
                <span class="team-hero-badge">
                    <i class="fas fa-calendar-day mr-1"></i> Today: {{ \Carbon\Carbon::parse($today)->format('d M Y') }}
                </span>
            </div>
        </div>

        <!-- View Mode Switcher -->
        <div class="view-switcher-bar">
            <a href="{{ url()->current() }}?view_mode=today" class="view-tab-btn {{ $viewMode === 'today' ? 'active' : '' }}">
                <i class="fas fa-clock"></i> Today's Live Attendance
            </a>
            <a href="{{ url()->current() }}?view_mode=history" class="view-tab-btn {{ $viewMode === 'history' ? 'active' : '' }}">
                <i class="fas fa-history"></i> Attendance History & Range
            </a>
            <div class="ml-auto small text-muted font-weight-bold d-none d-md-flex align-items-center gap-1">
                <i class="fas fa-shield-alt text-success mr-1"></i> Team Management Scope
            </div>
        </div>

        <!-- Metrics Overview Grid -->
        <div class="team-metric-grid">
            <div class="team-metric" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-users"></i></div>
                    <div class="team-metric-value">{{ $stats['total_team'] }}</div>
                </div>
                <div class="team-metric-label">Total Team</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon">
                        <span class="pulse-dot mr-1"></span>
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="team-metric-value text-success">{{ $stats['currently_working'] }}</div>
                </div>
                <div class="team-metric-label">Currently Working</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric" style="--metric-color:#2563EB;--metric-soft:#EFF6FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="team-metric-value text-primary">{{ $stats['completed_shift'] }}</div>
                </div>
                <div class="team-metric-label">Completed Shift</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric" style="--metric-color:#D97706;--metric-soft:#FFFBEB;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="team-metric-value text-warning">{{ $stats['late_today'] }}</div>
                </div>
                <div class="team-metric-label">Late Today</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric" style="--metric-color:#7C3AED;--metric-soft:#F5F3FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-home"></i></div>
                    <div class="team-metric-value" style="color: #7C3AED;">{{ $stats['wfh_today'] }}</div>
                </div>
                <div class="team-metric-label">WFH Today</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric" style="--metric-color:#DC2626;--metric-soft:#FEF2F2;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-user-times"></i></div>
                    <div class="team-metric-value text-danger">{{ $stats['not_punched_today'] }}</div>
                </div>
                <div class="team-metric-label">Not Punched / Absent</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        <!-- Filter & Attendance Table Card -->
        <div class="team-card">
            <div class="team-section-head">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:12px; background:#F4F2FF; color:var(--orb-primary); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h5 class="team-section-title">Filter Team Attendance</h5>
                        <div class="team-section-sub">Filter by team member, shift, date or mode to inspect detailed attendance logs.</div>
                    </div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="team-filter-panel">
                <form method="GET" action="{{ url()->current() }}" id="teamAttendanceFilterForm">
                    <input type="hidden" name="view_mode" value="{{ $viewMode }}">

                    <div class="team-filter-grid">
                        <!-- Team Member -->
                        <div class="team-filter-group">
                            <label><i class="fas fa-user text-primary mr-1"></i> Team Member</label>
                            <select name="employee_id" class="form-control select2-searchable">
                                <option value="">All Team Members</option>
                                @foreach($teamEmployees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Work Mode -->
                        <div class="team-filter-group">
                            <label><i class="fas fa-laptop-house text-primary mr-1"></i> Work Mode</label>
                            <select name="work_mode" class="form-control select2-searchable">
                                <option value="">All Work Modes</option>
                                <option value="wfo" {{ request('work_mode') === 'wfo' ? 'selected' : '' }}>WFO (Office)</option>
                                <option value="wfh" {{ request('work_mode') === 'wfh' ? 'selected' : '' }}>WFH (Home)</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="team-filter-group">
                            <label><i class="fas fa-info-circle text-primary mr-1"></i> Status</label>
                            <select name="status_filter" class="form-control select2-searchable">
                                <option value="">All Statuses</option>
                                <option value="working" {{ request('status_filter') === 'working' ? 'selected' : '' }}>Currently Working</option>
                                <option value="completed" {{ request('status_filter') === 'completed' ? 'selected' : '' }}>Completed Shift</option>
                                <option value="late" {{ request('status_filter') === 'late' ? 'selected' : '' }}>Late In</option>
                                <option value="half_day" {{ request('status_filter') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                                <option value="wfh" {{ request('status_filter') === 'wfh' ? 'selected' : '' }}>WFH</option>
                                <option value="blocked" {{ request('status_filter') === 'blocked' ? 'selected' : '' }}>Blocked / Lock</option>
                            </select>
                        </div>

                        @if($viewMode === 'history')
                            @php
                                $hasRangeParam = request()->filled('from_date') || request()->filled('to_date');
                                $monthParam = request('month_year');
                                $hasSingleDate = request()->filled('date');
                                $isCustomRange = ($monthParam === 'all' || $monthParam === 'custom' || ($hasRangeParam && !$hasSingleDate));
                                $effectiveMonthYear = ($selectedMonthYear && $selectedMonthYear !== 'all') ? $selectedMonthYear : \Carbon\Carbon::now()->format('Y-m');
                            @endphp

                            <!-- Month Select -->
                            <div class="team-filter-group">
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

                            <!-- Custom Date Range (From / To Date) -->
                            <div class="team-filter-group custom-date-filter {{ $isCustomRange ? '' : 'd-none' }}" style="{{ $isCustomRange ? '' : 'display: none;' }}">
                                <label><i class="fas fa-calendar-day text-primary mr-1"></i> From Date</label>
                                <x-form.date-picker name="from_date" id="team_from_date" :value="$fromDate" placeholder="From Date" class="filter-input" />
                            </div>
                            <div class="team-filter-group custom-date-filter {{ $isCustomRange ? '' : 'd-none' }}" style="{{ $isCustomRange ? '' : 'display: none;' }}">
                                <label><i class="fas fa-calendar-day text-primary mr-1"></i> To Date</label>
                                <x-form.date-picker name="to_date" id="team_to_date" :value="$toDate" placeholder="To Date" class="filter-input" />
                            </div>
                        @else
                            <!-- Single Date -->
                            <div class="team-filter-group">
                                <label><i class="fas fa-calendar-day text-primary mr-1"></i> Date</label>
                                <x-form.date-picker name="date" id="team_date" :value="$date" placeholder="Date" class="filter-input" />
                            </div>
                        @endif

                        <!-- Search Input -->
                        <div class="team-filter-group">
                            <label><i class="fas fa-search text-primary mr-1"></i> Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search name or code..." value="{{ request('search') }}">
                        </div>

                        <!-- Action Buttons -->
                        <div class="team-filter-group d-flex align-items-end" style="gap: 8px;">
                            <button type="submit" class="btn font-weight-bold text-white shadow-sm" style="height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ url()->current() }}?view_mode={{ $viewMode }}" class="btn btn-light" style="height: 42px; width: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 750; border: 1px solid #E4E7EC; background: #fff; color: #344054; flex-shrink: 0;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Action Toolbar with Show Entries & Export Tools -->
            <div class="orb-table-tools-bar">
                <div class="orb-table-length-box">
                    <div class="d-flex align-items-center">
                        <label class="mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size: 13px; gap: 8px;">
                            <span>Show</span>
                            <select id="recordsPerPageSelect" name="per_page" class="table-per-page-select orb-per-page-select" style="width: 75px;">
                                @foreach([10, 25, 50, 100, 250] as $size)
                                    <option value="{{ $size }}" {{ (int) request('per_page', 50) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                            <span>entries</span>
                        </label>
                    </div>
                </div>

                <div class="orb-table-export-buttons">
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
            </div>

            <!-- Attendance Data Table -->
            <div class="att-table-wrap">
                <table class="team-table" id="teamAttendanceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Login (Punch In)</th>
                            <th>Logout (Punch Out)</th>
                            <th>Working Hours</th>
                            <th>Work Mode</th>
                            <th>Status</th>
                            <th>Work Summary</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $row)
                        @php
                            $isWorking = !empty($row->punch_in_time) && empty($row->punch_out_time) && !$row->is_blocked;
                            $isCompleted = !empty($row->punch_in_time) && !empty($row->punch_out_time);
                            $empName = $row->employee->display_name ?? optional($row->user)->name ?? 'Team Member';
                            $empCode = $row->employee->employee_code ?? '-';
                            $dept = $row->employee->department->name ?? '-';
                            $desig = $row->employee->designation->name ?? '-';
                            $initials = strtoupper(substr($empName, 0, 2));
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}</td>
                            <td>
                                <div>
                                    <strong class="d-block text-dark font-weight-bold" style="font-size: 13.5px;">{{ $empName }}</strong>
                                    <small class="text-muted"><span class="badge badge-light border font-weight-bold" style="font-size: 10.5px;">{{ $empCode }}</span> {{ $desig }}</small>
                                </div>
                            </td>
                            <td>
                                <strong class="text-dark font-weight-bold">{{ $row->date_formatted }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-light border font-weight-bold" style="border-radius: 8px; font-size: 11.5px; padding: 5px 9px;">
                                    <i class="far fa-clock text-primary mr-1"></i> {{ $row->attendanceTime->name ?? 'General Shift' }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($row->punch_in_time))
                                    <div>
                                        <strong class="text-success font-weight-bold" style="font-size: 13.5px;">
                                            <i class="fas fa-sign-in-alt mr-1"></i> {{ $row->punch_in_formatted }}
                                        </strong>
                                        @if($row->is_late)
                                            <span class="badge badge-warning text-dark ml-1 font-weight-bold" style="font-size: 10px; border-radius: 6px;">LATE</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted font-weight-bold">--:--</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($row->punch_out_time))
                                    <div>
                                        <strong class="text-primary font-weight-bold" style="font-size: 13.5px;">
                                            <i class="fas fa-sign-out-alt mr-1"></i> {{ $row->punch_out_formatted }}
                                        </strong>
                                    </div>
                                @elseif($isWorking)
                                    <span class="status-badge status-working">
                                        <span class="pulse-dot"></span> Active Now
                                    </span>
                                @else
                                    <span class="text-muted font-weight-bold">--:--</span>
                                @endif
                            </td>
                            <td>
                                <strong class="font-weight-bold {{ $isWorking ? 'text-success' : 'text-dark' }}" style="font-size: 13px;">
                                    {{ $row->working_hours_label }}
                                </strong>
                            </td>
                            <td>
                                @if(strtolower($row->work_mode ?? '') === 'wfh')
                                    <span class="status-badge status-wfh"><i class="fas fa-home mr-1"></i> WFH</span>
                                @else
                                    <span class="status-badge status-completed"><i class="fas fa-building mr-1"></i> WFO</span>
                                @endif
                            </td>
                            <td>
                                @if($isWorking)
                                    <span class="status-badge status-working">🟢 Working</span>
                                @elseif($isCompleted)
                                    <span class="status-badge status-completed">🔵 Shift Done</span>
                                @elseif($row->is_blocked || $row->is_punch_blocked)
                                    <span class="status-badge status-absent">🔴 Blocked</span>
                                @elseif($row->attendance_status === 'absent')
                                    <span class="status-badge status-absent">🔴 Absent</span>
                                @else
                                    <span class="status-badge status-completed">{{ ucwords(str_replace('_', ' ', $row->attendance_status ?? 'Recorded')) }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $workSummary = optional($row->workLogs->first())->work_description ?? $row->punch_in_note ?? '-';
                                @endphp
                                <span class="d-inline-block text-truncate" style="max-width: 170px;" title="{{ $workSummary }}">
                                    {{ $workSummary }}
                                </span>
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-light border px-2.5 py-1 shadow-sm"
                                    data-toggle="modal"
                                    data-target="#viewModal{{ $row->id }}"
                                    style="border-radius: 10px; font-weight: 750; font-size: 12px; border-color: #E2E8F0;">
                                    <i class="fas fa-eye text-primary mr-1"></i> Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <div class="mb-2" style="font-size: 36px; opacity: 0.35;"><i class="fas fa-user-clock"></i></div>
                                <strong style="font-size: 14px;">No team attendance records found for the selected period.</strong>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div>
                {{ $attendances->links('vendor.pagination.orbo') }}
            </div>
        </div>

        @foreach($attendances as $row)
            @include('hrms.attendance.partials.view-modal', ['attendance' => $row])
        @endforeach

    </div>
</div>

<!-- Shared Premium Modal -->
@include('hrms.attendance.partials.work-report-modal')
@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize standard searchable dropdowns
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-searchable').select2({
            width: '100%',
            dropdownAutoWidth: true
        });

        // Initialize per-page select
        $('.table-per-page-select').select2({
            minimumResultsForSearch: -1,
            containerCssClass: 'select2-container--per-page',
            dropdownCssClass: 'select2-dropdown-per-page',
            width: '75px'
        });
    }

    // Month select change handler for instant custom date range toggle
    function syncCustomDateFilter(val) {
        var isCustom = (val === 'all' || val === 'custom');
        var filters = document.querySelectorAll('.custom-date-filter');
        filters.forEach(function(el) {
            if (isCustom) {
                el.classList.remove('d-none');
                el.style.display = '';
            } else {
                el.classList.add('d-none');
                el.style.display = 'none';
            }
        });
        if (!isCustom) {
            var fromInput = document.getElementById('team_from_date');
            var toInput = document.getElementById('team_to_date');
            if (fromInput) fromInput.value = '';
            if (toInput) toInput.value = '';
        }
    }

    if (typeof $ !== 'undefined') {
        $('#monthYearSelect').on('change select2:select select2:unselect select2:close', function() {
            syncCustomDateFilter($(this).val());
        });

        // Run on initial load
        if ($('#monthYearSelect').length) {
            syncCustomDateFilter($('#monthYearSelect').val());
        }
    }

    var monthSelectEl = document.getElementById('monthYearSelect');
    if (monthSelectEl) {
        monthSelectEl.addEventListener('change', function() {
            syncCustomDateFilter(this.value);
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

    // Construct export URL with active form filters
    function getExportUrl(format) {
        var formEl = document.getElementById('teamAttendanceFilterForm');
        var params = new URLSearchParams();
        if (formEl) {
            var formData = new FormData(formEl);
            for (var pair of formData.entries()) {
                if (pair[1] !== null && pair[1] !== '') {
                    params.append(pair[0], pair[1]);
                }
            }
        }
        params.set('export', format);
        var currentUrl = window.location.pathname;
        return currentUrl + '?' + params.toString();
    }

    // CSV Export (Full Dataset)
    $('#btnExportCSV').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        window.location.href = getExportUrl('csv');
    });

    // Excel Export (Full Dataset)
    $('#btnExportExcel').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        window.location.href = getExportUrl('excel');
    });

    // PDF Export (Full Dataset via pdfMake)
    $('#btnExportPDF').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> PDF...').prop('disabled', true);

        $.getJSON(getExportUrl('json'), function(res) {
            $btn.html(origHtml).prop('disabled', false);
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No attendance records found to export.');
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
                    { text: 'DATE', style: 'tableHeader', alignment: 'center' },
                    { text: 'SHIFT', style: 'tableHeader', alignment: 'center' },
                    { text: 'LOGIN (IN)', style: 'tableHeader', alignment: 'center' },
                    { text: 'LOGOUT (OUT)', style: 'tableHeader', alignment: 'center' },
                    { text: 'WORKING HOURS', style: 'tableHeader', alignment: 'center' },
                    { text: 'MODE', style: 'tableHeader', alignment: 'center' },
                    { text: 'STATUS', style: 'tableHeader', alignment: 'center' }
                ]
            ];

            res.data.forEach(function(item) {
                var empText = item.employee_name + (item.employee_code && item.employee_code !== '-' ? '\n' + item.employee_code : '') + (item.designation && item.designation !== '-' ? '\n' + item.designation : '');

                body.push([
                    { text: String(item.sr_no), alignment: 'center', style: 'tableCell' },
                    { text: empText, style: 'tableCell' },
                    { text: item.date || '—', alignment: 'center', style: 'tableCell' },
                    { text: item.shift || 'General Shift', alignment: 'center', style: 'tableCell' },
                    { text: item.punch_in || '--:--', alignment: 'center', style: 'tableCell' },
                    { text: item.punch_out || '--:--', alignment: 'center', style: 'tableCell' },
                    { text: item.working_hours || '--', alignment: 'center', style: 'tableCell' },
                    { text: item.work_mode || 'WFO', alignment: 'center', style: 'tableCell' },
                    { text: item.status || '—', alignment: 'center', style: 'tableCell', bold: true }
                ]);
            });

            var docDefinition = {
                pageOrientation: 'landscape',
                pageSize: 'A4',
                pageMargins: [18, 18, 18, 18],
                content: [
                    { text: 'Team Attendance - Full Report', style: 'header' },
                    { text: 'Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString() + ' | Total Records: ' + res.total, style: 'subHeader' },
                    {
                        table: {
                            headerRows: 1,
                            widths: ['4%', '20%', '11%', '12%', '11%', '11%', '11%', '7%', '13%'],
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

            pdfMake.createPdf(docDefinition).download("Team_Attendance_" + new Date().toISOString().slice(0,10) + ".pdf");
        }).fail(function() {
            $btn.html(origHtml).prop('disabled', false);
            alert('Failed to fetch full attendance data for PDF export.');
        });
    });

    // Print (Full Dataset via isolated iframe)
    $('#btnPrint').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Print...').prop('disabled', true);

        $.getJSON(getExportUrl('json'), function(res) {
            $btn.html(origHtml).prop('disabled', false);
            if (!res.success || !res.data || res.data.length === 0) {
                alert('No attendance records found to print.');
                return;
            }

            var rowsHtml = '';
            res.data.forEach(function(item) {
                rowsHtml += '<tr>' +
                    '<td style="text-align:center;">' + item.sr_no + '</td>' +
                    '<td><strong>' + item.employee_name + '</strong><br><small style="color:#64748B;">' + item.employee_code + (item.designation && item.designation !== '-' ? ' • ' + item.designation : '') + '</small></td>' +
                    '<td style="text-align:center;">' + item.date + '</td>' +
                    '<td style="text-align:center;">' + item.shift + '</td>' +
                    '<td style="text-align:center;color:#047857;font-weight:bold;">' + item.punch_in + '</td>' +
                    '<td style="text-align:center;color:#1D4ED8;font-weight:bold;">' + item.punch_out + '</td>' +
                    '<td style="text-align:center;font-weight:bold;">' + item.working_hours + '</td>' +
                    '<td style="text-align:center;">' + item.work_mode + '</td>' +
                    '<td style="text-align:center;font-weight:bold;">' + item.status + '</td>' +
                    '</tr>';
            });

            var iframe = document.getElementById('teamAttendancePrintFrame');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'teamAttendancePrintFrame';
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
            doc.write('<!DOCTYPE html><html><head><title>Team Attendance</title>');
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
            doc.write('<h2>Team Attendance - Full Dataset Report</h2>');
            doc.write('<p>Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString() + ' | Total Records: ' + res.total + '</p>');
            doc.write('<table><thead><tr><th style="width:28px;text-align:center;">#</th><th>Employee</th><th style="text-align:center;">Date</th><th style="text-align:center;">Shift</th><th style="text-align:center;">Login (In)</th><th style="text-align:center;">Logout (Out)</th><th style="text-align:center;">Working Hours</th><th style="text-align:center;">Mode</th><th style="text-align:center;">Status</th></tr></thead><tbody>' + rowsHtml + '</tbody></table>');
            doc.write('</body></html>');
            doc.close();

            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 250);
        }).fail(function() {
            $btn.html(origHtml).prop('disabled', false);
            alert('Failed to fetch full attendance data for Print.');
        });
    });
});
</script>
@endsection
