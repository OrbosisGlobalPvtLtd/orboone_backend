@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Daily Work Reports')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('_content')

@include('hrms.employee.partials.styles')

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

    .report-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .report-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Premium Purple Gradient Hero Header */
    .report-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 26px !important;
        padding: 30px 36px !important;
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

    .report-header-premium .title-area h3 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .report-header-premium .title-area p {
        font-size: 13.5px !important;
        color: rgba(255, 255, 255, 0.88) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .report-header-premium .header-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.8) !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* View Switcher Toggle Pill */
    .view-switcher-pill {
        display: inline-flex;
        background: rgba(255, 255, 255, 0.18);
        padding: 4px;
        border-radius: 50px;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .view-switcher-btn {
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.9);
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 12.5px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .view-switcher-btn:hover {
        color: #FFFFFF;
        background: rgba(255, 255, 255, 0.12);
    }

    .view-switcher-btn.active {
        background: #FFFFFF !important;
        color: var(--orb-primary) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
    }

    .report-btn-pill {
        height: 40px;
        padding: 0 18px;
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
        color: var(--orb-primary) !important;
        transform: translateY(-2px);
    }

    /* KPI Metrics Summary Grid */
    .report-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .report-kpi-card {
        background: #FFFFFF;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        padding: 18px 20px;
        box-shadow: 0 8px 22px rgba(16, 24, 40, .04);
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .report-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 28px rgba(75, 0, 232, 0.08);
        border-color: rgba(75, 0, 232, 0.25);
    }

    .report-kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }

    .kpi-purple { background: #F4F2FF; color: var(--orb-primary); }
    .kpi-blue { background: #EFF8FF; color: #175CD3; }
    .kpi-amber { background: #FEF7C3; color: #B54708; }
    .kpi-emerald { background: #ECFDF3; color: #027A48; }
    .kpi-indigo { background: #EEF4FF; color: #3538CD; }

    .report-kpi-val {
        font-size: 20px;
        font-weight: 900;
        color: var(--orb-text);
        line-height: 1.2;
        font-feature-settings: "tnum";
    }

    .report-kpi-lbl {
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 2px;
    }

    /* Filter Card */
    .orb-filter-card {
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid var(--orb-border);
        box-shadow: 0 8px 24px rgba(16, 24, 40, .03);
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .report-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1.2fr 1.2fr 2fr auto;
        gap: 14px;
        align-items: flex-end;
    }

    .report-filter-grid label {
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 6px;
        display: block;
    }

    .report-filter-grid .form-control {
        height: 44px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #FFFFFF;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
        width: 100%;
        outline: none;
        transition: all 0.2s ease;
    }

    .report-filter-grid .form-control:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    }

    .select2-container .select2-selection--single {
        height: 44px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        padding: 6px 12px !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 9px !important;
    }

    /* Main Table Card */
    .orb-table-card {
        background: #FFFFFF !important;
        border-radius: 24px !important;
        border: 1px solid var(--orb-border) !important;
        box-shadow: var(--orb-shadow) !important;
        overflow: hidden !important;
        margin-bottom: 30px !important;
    }

    .orb-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding: 18px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #FFFFFF 0%, #FAFBFC 100%);
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toolbar-title {
        font-size: 17px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .toolbar-badge {
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 50px;
        border: 1px solid rgba(75, 0, 232, 0.15);
    }

    .toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .leave-export-btn {
        height: 38px !important;
        border-radius: 12px !important;
        padding: 8px 16px !important;
        font-size: 12.5px !important;
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
        cursor: pointer;
    }

    .leave-export-btn:hover {
        background: #F9F5FF !important;
        color: var(--orb-primary) !important;
        border-color: #D9CCFF !important;
    }

    /* Table Scroll & Styles */
    .orb-table-scroll {
        width: 100% !important;
        overflow-x: auto !important;
    }

    .report-table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .report-table thead th {
        background: #F8FAFC !important;
        color: #475467 !important;
        font-size: 11.5px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        padding: 14px 18px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--orb-border) !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .report-table tbody td {
        padding: 16px 18px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid #F1F5F9 !important;
        background: #FFFFFF !important;
        vertical-align: middle !important;
        transition: background 0.15s ease;
    }

    .report-table tbody tr:hover td {
        background: #FBFBFE !important;
    }

    /* Employee Pill in Table */
    .table-emp-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .table-emp-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-weight: 900;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, 0.12);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
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
        line-height: 1.3;
    }

    .table-emp-meta {
        font-size: 11.5px;
        color: #667085;
        font-weight: 600;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .badge-dept-tag {
        background: #F1F5F9;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 6px;
    }

    /* Badge Pills */
    .badge-premium-pill {
        padding: 5px 12px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        letter-spacing: 0.02em;
    }

    .badge-wfo {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #D1FADF;
    }

    .badge-wfh {
        background: #EFF8FF;
        color: #175CD3;
        border: 1px solid #D1E9FF;
    }

    .badge-gross-pill {
        background: #FEF7C3;
        color: #B54708;
        border: 1px solid #FEF08A;
        font-weight: 800;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .project-tag-pill {
        background: #F4F2FF;
        color: var(--orb-primary);
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
        font-size: 13px;
        color: #1E293B;
        line-height: 1.5;
        max-width: 100%;
        word-break: break-word;
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
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, 0.15);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none !important;
        cursor: pointer;
    }

    .btn-action-primary:hover {
        background: var(--orb-primary);
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
        color: var(--orb-primary);
        border-color: #D9CCFF;
    }

    /* Cards Grid View */
    .emp-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 22px;
        margin-bottom: 30px;
    }

    .emp-summary-card {
        background: #FFFFFF;
        border: 1px solid #E7EAF3;
        border-radius: 22px;
        padding: 22px;
        box-shadow: 0 10px 25px rgba(16, 24, 40, .03);
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    .emp-summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 35px rgba(75, 0, 232, 0.09);
        border-color: rgba(75, 0, 232, 0.22);
    }

    .emp-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .emp-card-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-weight: 800;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, 0.1);
    }

    .emp-card-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .emp-card-name {
        font-weight: 800;
        font-size: 16px;
        color: #101828;
        margin: 0 0 2px 0;
    }

    .emp-card-meta {
        font-size: 12px;
        color: #667085;
        font-weight: 600;
    }

    .emp-stats-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        background: #F8FAFC;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid #F1F5F9;
        margin-bottom: 16px;
        text-align: center;
    }

    .emp-stat-item .stat-val {
        font-size: 15px;
        font-weight: 900;
        color: var(--orb-primary);
        line-height: 1.2;
    }

    .emp-stat-item .stat-lbl {
        font-size: 10px;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 2px;
    }

    .emp-latest-snippet {
        background: #FAFAFA;
        border: 1px solid #F1F5F9;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 18px;
        font-size: 12.5px;
        color: #334155;
    }

    .emp-latest-snippet .snippet-title {
        font-weight: 800;
        color: #0F172A;
        font-size: 11.5px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Slide-Out Timeline Drawer */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .drawer-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .timeline-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: 600px;
        max-width: 90vw;
        height: 100vh;
        background: #FFFFFF;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        z-index: 1060;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }

    .timeline-drawer.active {
        transform: translateX(0);
    }

    .drawer-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        color: #FFFFFF;
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .drawer-header .close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: #FFFFFF;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        transition: background 0.2s ease;
    }

    .drawer-header .close-btn:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    .drawer-body {
        padding: 28px;
        overflow-y: auto;
        flex: 1;
        background: #F8FAFC;
    }

    .timeline-list {
        position: relative;
        padding-left: 24px;
    }

    .timeline-list::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 7px;
        bottom: 10px;
        width: 2px;
        background: #E2E8F0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        top: 14px;
        left: -21px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--orb-primary);
        border: 3px solid #FFFFFF;
        box-shadow: 0 0 0 2px var(--orb-primary);
    }

    .timeline-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }

    @media (max-width: 1200px) {
        .report-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .report-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .report-header-premium {
            flex-direction: column;
            align-items: flex-start;
            padding: 24px 20px !important;
        }
        .report-kpi-grid {
            grid-template-columns: 1fr;
        }
        .report-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="report-page">
    <div class="report-container">

        <!-- Premium Header Area -->
        <div class="report-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-clipboard-list"></i> Daily Work Logging
                </div>
                <h3>Daily Work Reports</h3>
                <p>Track, manage, and review employee tasks, daily progress summaries, and work details.</p>
            </div>

            <div class="d-flex align-items-center flex-wrap" style="gap:12px;">
                <!-- View Mode Switcher Toggle Pill (Listing default active) -->
                <div class="view-switcher-pill">
                    <button type="button" class="view-switcher-btn active" id="btnTableView" onclick="switchWorkReportView('table')">
                        <i class="fas fa-list-ul"></i> Listing View
                    </button>
                    <button type="button" class="view-switcher-btn" id="btnCardsView" onclick="switchWorkReportView('cards')">
                        <i class="fas fa-th-large"></i> Employee Summaries
                    </button>
                </div>

                @if($isAdminOrManager)
                <a href="{{ route('attendances.daily') }}" class="report-btn-pill">
                    <i class="fas fa-calendar-check"></i>
                    Daily Attendance
                </a>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 14px; background: #F0FDF4; border-left: 5px solid #22C55E !important;">
            <i class="fas fa-check-circle text-success mr-2"></i> {{ session('success') }}
        </div>
        @endif

        <!-- KPI Metric Summary Grid -->
        <div class="report-kpi-grid">
            <div class="report-kpi-card">
                <div class="report-kpi-icon kpi-purple">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <div class="report-kpi-val">{{ $statsSummary['total_reports'] ?? count($workLogs) }}</div>
                    <div class="report-kpi-lbl">Work Reports</div>
                </div>
            </div>

            <div class="report-kpi-card">
                <div class="report-kpi-icon kpi-blue">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="report-kpi-val">{{ $statsSummary['unique_employees'] ?? count($employeeSummaries) }}</div>
                    <div class="report-kpi-lbl">Active Staff</div>
                </div>
            </div>

            <div class="report-kpi-card">
                <div class="report-kpi-icon kpi-amber">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="report-kpi-val" style="font-size: 17px;">{{ $statsSummary['total_gross_formatted'] ?? '0 mins' }}</div>
                    <div class="report-kpi-lbl">Gross Work Duration</div>
                </div>
            </div>

            <div class="report-kpi-card">
                <div class="report-kpi-icon kpi-emerald">
                    <i class="fas fa-tasks"></i>
                </div>
                <div>
                    <div class="report-kpi-val">{{ $statsSummary['total_tasks'] ?? 0 }}</div>
                    <div class="report-kpi-lbl">Structured Tasks</div>
                </div>
            </div>

            <div class="report-kpi-card">
                <div class="report-kpi-icon kpi-indigo">
                    <i class="fas fa-laptop-house"></i>
                </div>
                <div>
                    <div class="report-kpi-val" style="font-size: 16px;">
                        <span class="text-success">{{ $statsSummary['wfo_count'] ?? 0 }}</span> WFO / 
                        <span class="text-primary">{{ $statsSummary['wfh_count'] ?? 0 }}</span> WFH
                    </div>
                    <div class="report-kpi-lbl">Work Modes</div>
                </div>
            </div>
        </div>

        <!-- Filter Card Bar -->
        <div class="orb-filter-card">
            <form method="GET" action="{{ route('hrms.attendance.work-reports') }}" id="reportFilterForm">
                <div class="report-filter-grid">
                    @if($isAdminOrManager)
                    <div>
                        <label><i class="fas fa-user text-muted mr-1"></i> Employee</label>
                        <select name="employee_id" id="filterEmployee" class="form-control select2-searchable">
                            <option value="">All Staff Members</option>
                            @foreach($employees as $emp)
                            <option value="{{ optional($emp->employee)->id }}" {{ (string) request('employee_id') === (string) optional($emp->employee)->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ optional($emp->employee)->employee_code ?? 'EMP' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div>
                        <label><i class="fas fa-user text-muted mr-1"></i> Employee</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly disabled>
                    </div>
                    @endif

                    <div>
                        <label><i class="fas fa-building mr-1 text-muted"></i> Work Mode</label>
                        <select name="work_mode" id="filterWorkMode" class="form-control">
                            <option value="">All Modes</option>
                            <option value="wfo" {{ strtolower(request('work_mode')) === 'wfo' ? 'selected' : '' }}>Office (WFO)</option>
                            <option value="wfh" {{ strtolower(request('work_mode')) === 'wfh' ? 'selected' : '' }}>Remote (WFH)</option>
                        </select>
                    </div>

                    <div>
                        <label><i class="far fa-calendar-alt text-muted mr-1"></i> From Date</label>
                        <input type="date" name="from_date" id="filterFromDate" value="{{ request('from_date') }}" class="form-control">
                    </div>

                    <div>
                        <label><i class="far fa-calendar-alt text-muted mr-1"></i> To Date</label>
                        <input type="date" name="to_date" id="filterToDate" value="{{ request('to_date') }}" class="form-control">
                    </div>

                    <div>
                        <label><i class="fas fa-search text-muted mr-1"></i> Search Keyword</label>
                        <input type="text" name="search" id="filterSearch" value="{{ request('search') }}" class="form-control" placeholder="Search tasks, project or summary...">
                    </div>

                    <div>
                        <label>&nbsp;</label>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <button type="submit" id="btnFilterSubmit" class="btn text-white font-weight-bold shadow-sm" style="height: 44px; border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; padding: 0 20px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer;">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('hrms.attendance.work-reports') }}" class="btn btn-light border text-secondary font-weight-bold" style="height: 44px; width: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- VIEW MODE 1: ALL DAILY LOGS LISTING TABLE VIEW (DEFAULT ACTIVE) -->
        <div id="tableViewArea">
            <div class="card orb-table-card">
                <div class="orb-table-toolbar">
                    <div class="toolbar-left">
                        <h4 class="toolbar-title">
                            <i class="fas fa-list-check text-primary"></i> Daily Work Report Listing
                        </h4>
                        <span class="toolbar-badge">
                            {{ count($workLogs) }} Logged Reports
                        </span>
                    </div>
                    <div class="toolbar-right">
                        <div id="dtButtonsContainer" class="d-flex align-items-center" style="gap: 8px;"></div>
                    </div>
                </div>

                <div class="orb-table-scroll">
                    <table class="report-table table mb-0" id="workReportsTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 45px;">#</th>
                                <th style="min-width: 170px;">Employee</th>
                                <th style="min-width: 130px;">Date & Time</th>
                                <th style="min-width: 110px;">Mode & Shift</th>
                                <th style="min-width: 110px;">Gross Work</th>
                                <th style="min-width: 380px; width: 42%;">Project & Work Summary</th>
                                <th class="text-center" style="min-width: 90px;">Tasks</th>
                                <th class="text-right pr-4 no-export" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
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
                                $employeeName = optional($log->user)->name ?? 'Employee';
                                $employeeCode = optional($log->employee)->employee_code ?? 'N/A';
                                $deptName = optional(optional($log->employee)->department)->name ?? 'Staff';
                                $desigName = optional(optional($log->employee)->designation)->name ?? 'Member';

                                $logPayload = [
                                    'id' => $log->id,
                                    'work_log_id' => $log->id,
                                    'employee_name' => $employeeName,
                                    'employee_code' => $employeeCode,
                                    'passport_photo_url' => resolveEmployeePassportPhoto($log->employee ?? $log),
                                    'employee_initial' => resolveEmployeeInitials($log->employee ?? $log),
                                    'department' => $deptName,
                                    'designation' => $desigName,
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

                                $empId = $log->employee_id ?: ($log->user_id ?: 0);
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold text-muted" style="font-size: 12.5px;">
                                    {{ $loop->iteration }}
                                </td>
                                
                                <td>
                                    <div class="table-emp-cell">
                                        <div class="table-emp-avatar">
                                            @if(resolveEmployeePassportPhoto($log->employee ?? $log))
                                                <img src="{{ resolveEmployeePassportPhoto($log->employee ?? $log) }}" alt="{{ $employeeName }}">
                                            @else
                                                <span>{{ resolveEmployeeInitials($log->employee ?? $log) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="table-emp-name">{{ $employeeName }}</div>
                                            <div class="table-emp-meta">
                                                <span>{{ $employeeCode }}</span>
                                                <span>&bull;</span>
                                                <span class="badge-dept-tag">{{ $deptName }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td data-order="{{ $log->work_date ? $log->work_date->format('Y-m-d') : '' }}">
                                    <div class="font-weight-bold text-dark" style="font-size: 13px;">
                                        {{ $log->work_date ? $log->work_date->format('d M Y') : '-' }}
                                    </div>
                                    <div class="small text-muted font-weight-semibold">
                                        {{ $log->work_date ? $log->work_date->format('l') : '' }}
                                        @if($log->created_at)
                                            &bull; <i class="far fa-clock text-muted"></i> {{ $log->created_at->format('h:i A') }}
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div>
                                        <span class="badge-premium-pill {{ $modeBadgeClass }} mb-1">
                                            @if($mode === 'wfh')
                                                <i class="fas fa-laptop-house mr-1"></i> WFH
                                            @else
                                                <i class="fas fa-building mr-1"></i> WFO
                                            @endif
                                        </span>
                                    </div>
                                    <div class="small text-muted font-weight-bold" style="font-size: 11px;">
                                        {{ optional($attendance)->attendanceTime->name ?? 'Default Shift' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="badge-gross-pill">
                                        <i class="fas fa-stopwatch"></i> {{ $grossWork }}
                                    </div>
                                    @if($attendance && $attendance->punch_in_time)
                                    <div class="small text-muted font-weight-bold mt-1" style="font-size: 10.5px;">
                                        {{ \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') }} - {{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : 'Active' }}
                                    </div>
                                    @endif
                                </td>

                                <td style="min-width: 380px;">
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

                                <td class="text-center">
                                    @if($tasksCount > 0)
                                    <span class="badge badge-light border font-weight-bold px-2 py-1" style="border-radius: 8px; font-size: 12px;">
                                        <i class="fas fa-tasks text-primary mr-1"></i> {{ $tasksCount }} Tasks
                                    </span>
                                    @else
                                    <span class="text-muted font-italic" style="font-size:12px;">None</span>
                                    @endif
                                </td>

                                <td class="text-right pr-4">
                                    <div class="action-btn-group">
                                        <button type="button" class="btn-action-primary" 
                                                data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" 
                                                onclick="parseAndOpenWorkReport(this)"
                                                title="View Full Report Details">
                                            <i class="fas fa-eye"></i> Details
                                        </button>
                                        @if($empId)
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
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-clipboard-list text-muted fa-3x mb-3"></i>
                                    <h5 class="font-weight-bold text-dark mb-1">No Daily Work Reports Found</h5>
                                    <p class="text-muted font-weight-semibold mb-0">Try adjusting your filters or date range to see results.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VIEW MODE 2: EMPLOYEE CARDS GRID VIEW (TOGGLED ON DEMAND) -->
        <div id="cardsViewArea" style="display: none;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="font-weight-bold text-dark mb-0" style="font-size:18px;">
                    <i class="fas fa-users text-primary mr-2"></i>Employee Work Summaries
                </h4>
                <span class="text-muted font-weight-bold" style="font-size:13px;" id="cardsCountLabel">
                    Showing {{ count($employeeSummaries) }} Staff Members
                </span>
            </div>

            <div class="emp-cards-grid" id="employeeCardsGrid">
                @forelse($employeeSummaries as $sum)
                <div class="emp-summary-card card-item" 
                     data-employee-id="{{ $sum['employee_id'] }}"
                     data-employee-name="{{ strtolower($sum['user_name']) }}">
                    <div>
                        <div class="emp-card-header">
                            <div class="emp-card-avatar">
                                @if($sum['passport_photo_url'])
                                    <img src="{{ $sum['passport_photo_url'] }}" alt="{{ $sum['user_name'] }}">
                                @else
                                    <span>{{ $sum['employee_initial'] }}</span>
                                @endif
                            </div>
                            <div>
                                <h5 class="emp-card-name">{{ $sum['user_name'] }}</h5>
                                <div class="emp-card-meta">{{ $sum['employee_code'] }} &bull; {{ $sum['department'] }}</div>
                            </div>
                        </div>

                        <div class="emp-stats-bar">
                            <div class="emp-stat-item">
                                <div class="stat-val">{{ $sum['total_reports'] }}</div>
                                <div class="stat-lbl">Reports</div>
                            </div>
                            <div class="emp-stat-item">
                                <div class="stat-val">{{ $sum['total_gross_formatted'] }}</div>
                                <div class="stat-lbl">Gross Work</div>
                            </div>
                            <div class="emp-stat-item">
                                <div class="stat-val">{{ $sum['total_tasks'] }}</div>
                                <div class="stat-lbl">Tasks</div>
                            </div>
                        </div>

                        <div class="emp-latest-snippet">
                            <div class="snippet-title">
                                <span><i class="fas fa-clock text-primary mr-1"></i> Latest Log</span>
                                <span class="badge badge-light border">{{ $sum['latest_date'] }}</span>
                            </div>
                            <div class="text-truncate">{{ $sum['latest_summary'] }}</div>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('hrms.attendance.work-reports.employee-history', $sum['employee_id']) }}" target="_blank" class="btn btn-primary btn-block rounded-12 font-weight-bold py-2 shadow-sm" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none;">
                            <i class="fas fa-history mr-2"></i> View Daily History ({{ $sum['total_reports'] }})
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5 bg-white rounded-20 border">
                    <i class="fas fa-folder-open text-muted fa-3x mb-3"></i>
                    <h5 class="font-weight-bold text-dark">No Employee Work Reports Found</h5>
                    <p class="text-muted">Adjust search filters or select a different date range.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<!-- Slide-Out Employee Daily History Drawer -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeEmployeeTimelineDrawer()"></div>

<div class="timeline-drawer" id="employeeTimelineDrawer">
    <div class="drawer-header">
        <div class="d-flex align-items-center gap-3">
            <div class="emp-card-avatar border-white bg-white text-primary" id="drawerAvatar" style="width:44px; height:44px;">
                <span>E</span>
            </div>
            <div>
                <h5 class="mb-0 text-white font-weight-bold" id="drawerEmpName">Employee Name</h5>
                <div class="text-white-50 font-weight-bold" style="font-size:12px;" id="drawerEmpMeta">Code &bull; Department</div>
            </div>
        </div>
        <button type="button" class="close-btn" onclick="closeEmployeeTimelineDrawer()">&times;</button>
    </div>

    <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between text-muted font-weight-bold" style="font-size:13px;">
        <span><i class="fas fa-calendar-alt text-primary mr-1"></i> Daily Work Log Timeline</span>
        <span class="badge badge-primary px-3 py-1 rounded-50" id="drawerTotalCount">0 Logs</span>
    </div>

    <div class="drawer-body">
        <div class="timeline-list" id="drawerTimelineList">
            <!-- Rendered dynamically -->
        </div>
    </div>
</div>

<!-- Shared Premium Modal -->
@include('hrms.attendance.partials.work-report-modal')

@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
    // View Switcher Function
    function switchWorkReportView(mode) {
        if (mode === 'table') {
            $('#tableViewArea').show();
            $('#cardsViewArea').hide();
            $('#btnTableView').addClass('active');
            $('#btnCardsView').removeClass('active');
            if ($.fn.DataTable.isDataTable('#workReportsTable')) {
                $('#workReportsTable').DataTable().columns.adjust().draw();
            }
        } else {
            $('#tableViewArea').hide();
            $('#cardsViewArea').show();
            $('#btnCardsView').addClass('active');
            $('#btnTableView').removeClass('active');
        }
    }

    // Open Slide-Out Timeline Drawer
    function openEmployeeTimelineDrawer(empSummary) {
        $('#drawerEmpName').text(empSummary.user_name);
        $('#drawerEmpMeta').text(`${empSummary.employee_code} • ${empSummary.department}`);
        $('#drawerTotalCount').text(`${empSummary.total_reports} Logs`);

        if (empSummary.passport_photo_url) {
            $('#drawerAvatar').html(`<img src="${empSummary.passport_photo_url}" style="width:100%;height:100%;object-fit:cover;">`);
        } else {
            $('#drawerAvatar').html(`<span>${empSummary.employee_initial}</span>`);
        }

        let html = '';
        if (empSummary.logs && empSummary.logs.length > 0) {
            empSummary.logs.forEach(function(log) {
                const workDate = log.work_date ? new Date(log.work_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                const gross = (log.attendance && log.attendance.gross_duration) ? log.attendance.gross_duration : '-';
                const mode = (log.attendance && log.attendance.work_mode) ? log.attendance.work_mode.toUpperCase() : 'WFO';
                const modeBadge = mode === 'WFH' ? 'badge-wfh' : 'badge-wfo';
                const summaryText = log.work_summary || 'Work report submitted.';

                html += `
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="font-weight-bold text-dark" style="font-size:14px;"><i class="fas fa-calendar-day text-primary mr-1"></i> ${workDate}</span>
                                <span class="badge-premium-pill ${modeBadge}">${mode}</span>
                            </div>
                            <div class="text-muted font-weight-bold mb-2" style="font-size:12px;">
                                <i class="fas fa-clock mr-1"></i> Gross Work: <span class="text-dark">${gross}</span>
                            </div>
                            <div class="p-2 bg-light rounded-10 text-dark font-weight-bold mb-3" style="font-size:12.5px;">
                                ${summaryText}
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html = `<div class="text-center py-4 text-muted font-weight-bold">No daily work logs found for this employee.</div>`;
        }

        $('#drawerTimelineList').html(html);
        $('#drawerOverlay').addClass('active');
        $('#employeeTimelineDrawer').addClass('active');
    }

    function closeEmployeeTimelineDrawer() {
        $('#drawerOverlay').removeClass('active');
        $('#employeeTimelineDrawer').removeClass('active');
    }

    $(function() {
        // Initialize Select2 if available
        if ($.fn.select2) {
            $('.select2-searchable').select2({
                placeholder: "Select employee...",
                allowClear: true,
                width: '100%'
            });
        }

        // Initialize DataTables for Table View
        if ($('#workReportsTable tbody tr td').length > 1) {
            var table = $('#workReportsTable').DataTable({
                pageLength: 25,
                order: [[2, 'desc']], // Sort by Date desc
                ordering: true,
                searching: true, 
                paging: true,
                info: true,
                dom: "t<'d-flex align-items-center justify-content-between p-3 border-top bg-white'ip>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="far fa-file-excel text-success mr-1"></i> Excel',
                        className: 'leave-export-btn',
                        exportOptions: { columns: ':not(.no-export)' }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv text-primary mr-1"></i> CSV',
                        className: 'leave-export-btn',
                        exportOptions: { columns: ':not(.no-export)' }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="far fa-file-pdf text-danger mr-1"></i> PDF',
                        className: 'leave-export-btn',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: { columns: ':not(.no-export)' },
                        customize: function(doc) {
                            doc.pageMargins = [20, 20, 20, 20];
                            doc.defaultStyle.fontSize = 8.5;
                            if (doc.styles.tableHeader) {
                                doc.styles.tableHeader.fontSize = 9.5;
                                doc.styles.tableHeader.fillColor = '#1E293B';
                                doc.styles.tableHeader.color = '#FFFFFF';
                                doc.styles.tableHeader.alignment = 'left';
                            }
                            if (doc.content && doc.content[1] && doc.content[1].table) {
                                doc.content[1].table.widths = ['4%', '16%', '11%', '9%', '9%', '43%', '8%'];
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Print',
                        className: 'leave-export-btn',
                        exportOptions: { columns: ':not(.no-export)' },
                        customize: function(win) {
                            $(win.document.body).css({
                                'font-family': "'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
                                'color': '#0F172A',
                                'background': '#ffffff',
                                'padding': '16px 20px',
                                'font-size': '11px',
                                '-webkit-print-color-adjust': 'exact',
                                'print-color-adjust': 'exact'
                            });

                            var customStyles = `
                                @page {
                                    size: landscape A4;
                                    margin: 10mm 12mm 12mm 12mm;
                                }
                                * {
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                    box-sizing: border-box;
                                }
                                body {
                                    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
                                    color: #0F172A !important;
                                    background: #ffffff !important;
                                    padding: 0 !important;
                                }
                                .dt-print-header {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    border-bottom: 2px solid #0F172A;
                                    padding-bottom: 12px;
                                    margin-bottom: 16px;
                                }
                                .dt-print-brand {
                                    display: flex;
                                    align-items: center;
                                    gap: 12px;
                                }
                                .dt-print-logo {
                                    max-height: 42px;
                                    max-width: 150px;
                                    object-fit: contain;
                                }
                                .dt-print-title {
                                    font-size: 19px;
                                    font-weight: 900;
                                    color: #0F172A;
                                    margin: 0;
                                    line-height: 1.1;
                                }
                                .dt-print-subtitle {
                                    font-size: 10.5px;
                                    font-weight: 700;
                                    color: #64748B;
                                    text-transform: uppercase;
                                    letter-spacing: 0.08em;
                                    margin-top: 3px;
                                }
                                .dt-print-meta {
                                    text-align: right;
                                    font-size: 10px;
                                    color: #64748B;
                                    font-weight: 600;
                                }
                                .dt-print-badge {
                                    display: inline-block;
                                    background: #F1F5F9;
                                    border: 1px solid #CBD5E1;
                                    color: #334155;
                                    font-size: 9px;
                                    font-weight: 800;
                                    text-transform: uppercase;
                                    letter-spacing: 0.06em;
                                    padding: 3px 8px;
                                    border-radius: 4px;
                                    margin-bottom: 4px;
                                }
                                table.dataTable {
                                    width: 100% !important;
                                    border-collapse: collapse !important;
                                    margin: 0 !important;
                                    font-size: 11px !important;
                                }
                                table.dataTable thead th {
                                    background: #0F172A !important;
                                    color: #ffffff !important;
                                    font-size: 10.5px !important;
                                    font-weight: 800 !important;
                                    text-transform: uppercase !important;
                                    letter-spacing: 0.05em !important;
                                    padding: 10px 10px !important;
                                    border: 1px solid #0F172A !important;
                                    vertical-align: middle !important;
                                }
                                table.dataTable thead th:nth-child(1) { width: 4% !important; text-align: center; }
                                table.dataTable thead th:nth-child(2) { width: 16% !important; }
                                table.dataTable thead th:nth-child(3) { width: 11% !important; }
                                table.dataTable thead th:nth-child(4) { width: 9% !important; }
                                table.dataTable thead th:nth-child(5) { width: 9% !important; }
                                table.dataTable thead th:nth-child(6) { width: 43% !important; }
                                table.dataTable thead th:nth-child(7) { width: 8% !important; text-align: center; }

                                table.dataTable tbody td {
                                    padding: 9px 10px !important;
                                    border: 1px solid #E2E8F0 !important;
                                    font-size: 11px !important;
                                    color: #1E293B !important;
                                    vertical-align: top !important;
                                    background: #ffffff !important;
                                    line-height: 1.45 !important;
                                }
                                table.dataTable tbody tr:nth-child(even) td {
                                    background: #F8FAFC !important;
                                }
                                table.dataTable tbody tr {
                                    page-break-inside: avoid !important;
                                    break-inside: avoid !important;
                                }
                                .project-tag-pill {
                                    background: #EEF2FF !important;
                                    color: #4338CA !important;
                                    border: 1px solid #C7D2FE !important;
                                    font-weight: 800 !important;
                                    font-size: 10px !important;
                                    padding: 2px 6px !important;
                                    border-radius: 4px !important;
                                    display: inline-block !important;
                                    margin-bottom: 4px !important;
                                }
                                .work-summary-snippet {
                                    font-size: 11.5px !important;
                                    color: #1E293B !important;
                                    line-height: 1.45 !important;
                                    max-width: 100% !important;
                                    display: block !important;
                                    word-wrap: break-word !important;
                                    white-space: normal !important;
                                }
                                .work-tasks-mini-list {
                                    margin-top: 5px !important;
                                    display: flex !important;
                                    flex-wrap: wrap !important;
                                    gap: 4px !important;
                                }
                                .mini-task-pill {
                                    font-size: 9.5px !important;
                                    padding: 2px 6px !important;
                                    border-radius: 4px !important;
                                    background: #F1F5F9 !important;
                                    border: 1px solid #CBD5E1 !important;
                                    color: #334155 !important;
                                    display: inline-flex !important;
                                    align-items: center !important;
                                    gap: 3px !important;
                                }
                                .mini-task-pill.done {
                                    background: #ECFDF5 !important;
                                    border-color: #A7F3D0 !important;
                                    color: #065F46 !important;
                                }
                                .badge-premium-pill {
                                    padding: 3px 7px !important;
                                    border-radius: 4px !important;
                                    font-size: 9.5px !important;
                                    font-weight: 800 !important;
                                    display: inline-block !important;
                                }
                                .badge-wfo { background: #ECFDF5 !important; color: #065F46 !important; border: 1px solid #A7F3D0 !important; }
                                .badge-wfh { background: #EFF6FF !important; color: #1E40AF !important; border: 1px solid #BFDBFE !important; }
                                .badge-gross-pill {
                                    background: #FFFBEB !important;
                                    color: #92400E !important;
                                    border: 1px solid #FDE68A !important;
                                    font-weight: 800 !important;
                                    font-size: 10.5px !important;
                                    padding: 2px 6px !important;
                                    border-radius: 4px !important;
                                    display: inline-block !important;
                                }
                                .dt-print-footer {
                                    margin-top: 16px;
                                    padding-top: 10px;
                                    border-top: 1px solid #E2E8F0;
                                    display: flex;
                                    justify-content: space-between;
                                    font-size: 9.5px;
                                    color: #64748B;
                                    font-weight: 600;
                                }
                            `;

                            var styleElem = win.document.createElement('style');
                            styleElem.type = 'text/css';
                            styleElem.innerHTML = customStyles;
                            win.document.head.appendChild(styleElem);

                            $(win.document.body).find('h1').remove();

                            var headerHtml = `
                                <div class="dt-print-header">
                                    <div class="dt-print-brand">
                                        @if(branding_logo())
                                            <img src="{{ branding_logo() }}" alt="{{ company_name() }}" class="dt-print-logo">
                                        @endif
                                        <div>
                                            <div class="dt-print-title">{{ company_name() }}</div>
                                            <div class="dt-print-subtitle">Daily Work Reports Register</div>
                                        </div>
                                    </div>
                                    <div class="dt-print-meta">
                                        <span class="dt-print-badge">Official Attendance & Work Record</span>
                                        <div>Printed: ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</div>
                                        <div style="margin-top: 2px;">Records: ${table.rows({ filter: 'applied' }).count()} Logged Entries</div>
                                    </div>
                                </div>
                            `;
                            $(win.document.body).prepend(headerHtml);

                            var footerHtml = `
                                <div class="dt-print-footer">
                                    <div><strong>{{ company_name() }}</strong> &bull; OrboOne HRMS Work Reports Module</div>
                                    <div>Confidential & Official Document &bull; Valid Without Physical Stamp</div>
                                </div>
                            `;
                            $(win.document.body).append(footerHtml);
                        }
                    }
                ],
                language: {
                    emptyTable: 'No work reports found matching criteria.',
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    }
                }
            });

            // Append buttons into the custom toolbar container
            table.buttons().container().appendTo('#dtButtonsContainer');
        }

        // Instant Cards Search & Filter
        function filterEmployeeCards() {
            const empId = $('#filterEmployee').val();
            const searchVal = $('#filterSearch').val().toLowerCase();
            let visibleCount = 0;

            $('#employeeCardsGrid .card-item').each(function() {
                const cardEmpId = $(this).data('employee-id');
                const cardText = $(this).text().toLowerCase();

                let matchEmp = !empId || cardEmpId == empId;
                let matchSearch = !searchVal || cardText.includes(searchVal);

                if (matchEmp && matchSearch) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            $('#cardsCountLabel').text(`Showing ${visibleCount} Staff Members`);
        }

        $('#filterSearch').on('keyup', function() {
            if ($('#cardsViewArea').is(':visible')) {
                filterEmployeeCards();
            }
        });
    });
</script>
@endsection
