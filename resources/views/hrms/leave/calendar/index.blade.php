@extends('layouts.panel', ['active' => 'leave_management'])

@section('page_title', 'Team Leave Calendar')

@section('_head')
<style>
    :root {
        --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
        --orb-secondary: {{ $branding['secondary_color'] ?? '#8600EE' }};
        --orb-gradient: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#8600EE' }} 100%);
        --orb-bg: #F8FAFC;
        --orb-card: #FFFFFF;
        --orb-border: #E2E8F0;
        --orb-text: #0F172A;
        --orb-muted: #64748B;
        --orb-soft: rgba(75, 0, 232, 0.08);
        --orb-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    .calendar-page {
        background: var(--orb-bg);
        padding: 18px 16px 48px;
        min-height: calc(100vh - 90px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* 1. Hero Header */
    .cal-hero {
        background: var(--orb-gradient);
        border-radius: 16px;
        padding: 22px 24px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.15);
        margin-bottom: 18px;
    }

    .cal-kicker {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        opacity: 0.9;
        margin-bottom: 5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .cal-title {
        font-size: 22px;
        font-weight: 900;
        margin: 0;
        line-height: 1.2;
    }

    .cal-subtitle {
        font-size: 13px;
        font-weight: 500;
        margin: 4px 0 0;
        opacity: 0.9;
    }

    .cal-hero-btn {
        height: 38px;
        border-radius: 9px;
        padding: 0 18px;
        font-size: 12.5px;
        font-weight: 750;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: #fff !important;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .cal-hero-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .cal-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 16px 18px;
        }
        .cal-hero-btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* 2. Responsive Metric Cards */
    .cal-stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 13px;
        padding: 12px 14px;
        box-shadow: var(--orb-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .stat-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .stat-lbl {
        text-transform: uppercase;
        font-size: 9.5px;
        font-weight: 800;
        color: var(--orb-muted);
        letter-spacing: 0.4px;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat-val {
        margin: 2px 0 0;
        font-size: 20px;
        font-weight: 900;
        color: var(--orb-text);
        line-height: 1.1;
    }

    @media (max-width: 1200px) {
        .cal-stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .cal-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    }

    @media (max-width: 480px) {
        .cal-stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .stat-card {
            padding: 10px;
            gap: 8px;
        }
        .stat-icon-wrap {
            width: 32px;
            height: 32px;
            font-size: 13px;
            border-radius: 8px;
        }
        .stat-val {
            font-size: 17px;
        }
    }

    /* 3. Filter Panel Responsive System */
    .cal-filter-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        box-shadow: var(--orb-shadow);
        padding: 16px 18px;
        margin-bottom: 18px;
    }

    .cal-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }

    .cal-filter-col {
        flex: 1 1 160px;
        min-width: 140px;
    }

    .cal-filter-col-sm {
        flex: 0 1 130px;
        min-width: 110px;
    }

    .cal-filter-col-xs {
        flex: 0 1 105px;
        min-width: 90px;
    }

    .cal-filter-col-actions {
        flex: 0 0 auto;
        display: flex;
        gap: 8px;
    }

    @media (max-width: 991px) {
        .cal-filter-col {
            flex: 1 1 calc(50% - 12px);
        }
        .cal-filter-col-sm, .cal-filter-col-xs {
            flex: 1 1 calc(50% - 12px);
        }
        .cal-filter-col-actions {
            flex: 1 1 100%;
        }
    }

    @media (max-width: 575px) {
        .cal-filter-col, .cal-filter-col-sm, .cal-filter-col-xs {
            flex: 1 1 100%;
        }
        .cal-filter-col-actions {
            flex: 1 1 100%;
        }
    }

    .cal-filter-label {
        font-size: 10.5px;
        font-weight: 750;
        color: #334155;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .cal-filter-card .select2-container {
        width: 100% !important;
    }

    .cal-filter-card .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 9px !important;
        background-color: #FAFAFA !important;
    }

    .cal-filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        padding-left: 10px !important;
        padding-right: 28px !important;
    }

    .cal-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 6px !important;
    }

    /* Primary Gradient Search Button */
    .btn-search-theme {
        height: 38px;
        border-radius: 9px;
        font-weight: 800;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 20px;
        background: var(--orb-gradient) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-search-theme:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.35);
        color: #fff !important;
    }

    .btn-reset-theme {
        height: 38px;
        width: 38px;
        border-radius: 9px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #64748B;
        border: 1px solid var(--orb-border);
        transition: all 0.15s ease;
        text-decoration: none !important;
        flex-shrink: 0;
    }

    .btn-reset-theme:hover {
        background: #F1F5F9;
        color: var(--orb-primary);
        border-color: #CBD5E1;
    }

    /* 4. Calendar & Sidebar Grid */
    .cal-content-row {
        display: flex;
        gap: 18px;
        align-items: flex-start;
    }

    .cal-main-col {
        flex: 1;
        min-width: 0;
    }

    .cal-sidebar-col {
        width: 380px;
        flex-shrink: 0;
        position: sticky;
        top: 85px;
    }

    @media (max-width: 1200px) {
        .cal-content-row {
            flex-direction: column;
        }
        .cal-sidebar-col {
            width: 100%;
            position: static;
        }
    }

    /* 5. Calendar Card & Header */
    .calendar-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .calendar-header {
        padding: 14px 18px;
        border-bottom: 1px solid var(--orb-border);
        background: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .cal-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .cal-month-title {
        font-size: 16px;
        font-weight: 900;
        color: var(--orb-text);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .cal-nav-btn {
        height: 30px;
        padding: 0 10px;
        border-radius: 7px;
        border: 1px solid var(--orb-border);
        background: #FFFFFF;
        color: var(--orb-text);
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        text-decoration: none !important;
        transition: all 0.15s ease;
    }

    .cal-nav-btn:hover {
        background: #F1F5F9;
        color: var(--orb-primary);
        border-color: #CBD5E1;
    }

    .cal-nav-btn.today-btn {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, 0.2);
    }

    .cal-nav-btn.today-btn:hover {
        background: var(--orb-primary);
        color: #fff;
    }

    .cal-view-tabs {
        display: inline-flex;
        background: #F1F5F9;
        border-radius: 8px;
        padding: 2px;
        gap: 2px;
    }

    .cal-view-tab {
        padding: 4px 10px;
        font-size: 11.5px;
        font-weight: 700;
        color: #64748B;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
        border: none;
        background: transparent;
    }

    .cal-view-tab.active {
        background: #FFFFFF;
        color: var(--orb-primary);
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .cal-legend {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 11px;
        font-weight: 700;
        color: var(--orb-muted);
        flex-wrap: wrap;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .legend-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }

    @media (max-width: 768px) {
        .calendar-header {
            padding: 12px 14px;
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .cal-header-left {
            justify-content: space-between;
            width: 100%;
        }
        .cal-header-right {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .cal-legend {
            font-size: 10px;
            gap: 8px;
        }
    }

    /* 6. Week Days Header */
    .calendar-week-headers {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #F8FAFC;
        border-bottom: 1px solid var(--orb-border);
        text-align: center;
    }

    .week-day-header {
        padding: 8px 2px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748B;
        letter-spacing: 0.5px;
    }

    .week-day-header.weekend {
        color: #94A3B8;
        background: #F1F5F9;
    }

    /* 7. Calendar Grid & Cells */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: var(--orb-border);
        gap: 1px;
    }

    .calendar-cell {
        background: #fff;
        min-height: 98px;
        padding: 6px 7px;
        position: relative;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        flex-direction: column;
        user-select: none;
    }

    .calendar-cell:hover {
        background: #F8FAFC;
        z-index: 2;
        box-shadow: 0 0 0 1px var(--orb-primary);
    }

    .calendar-cell.other-month {
        background: #FAFBFD;
        opacity: 0.55;
    }

    .calendar-cell.cell-weekend {
        background: #FAFBFD;
    }

    .calendar-cell.today {
        background: #F8FAFF;
    }

    .calendar-cell.active-cell {
        box-shadow: inset 0 0 0 2px var(--orb-primary);
        background: #F5F3FF !important;
        z-index: 3;
    }

    .cell-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
    }

    .cell-date-num {
        font-size: 12.5px;
        font-weight: 800;
        color: #1E293B;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .calendar-cell.today .cell-date-num {
        background: var(--orb-primary);
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(75, 0, 232, 0.35);
    }

    .cell-today-pill {
        font-size: 8.5px;
        font-weight: 900;
        text-transform: uppercase;
        background: rgba(75, 0, 232, 0.1);
        color: var(--orb-primary);
        padding: 1px 4px;
        border-radius: 4px;
        letter-spacing: 0.3px;
    }

    .cell-holiday-chip {
        background: #F3E8FF;
        color: #7E22CE;
        border: 1px solid #E9D5FF;
        border-radius: 5px;
        padding: 2px 5px;
        font-size: 9.5px;
        font-weight: 800;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }

    .cell-holiday-chip i {
        font-size: 8.5px;
        flex-shrink: 0;
    }

    .leave-chips-list {
        display: flex;
        flex-direction: column;
        gap: 3px;
        flex: 1;
    }

    .mini-leave-chip {
        font-size: 10.5px;
        font-weight: 750;
        padding: 2px 5px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
        border: 1px solid transparent;
        transition: transform 0.1s ease;
    }

    .mini-leave-chip:hover {
        transform: translateX(1px);
    }

    .mini-leave-chip.approved {
        background: #ECFDF5;
        color: #065F46;
        border-color: #A7F3D0;
    }

    .mini-leave-chip.pending {
        background: #FFFBEB;
        color: #92400E;
        border-color: #FDE68A;
    }

    .mini-leave-chip.lwp {
        background: #FFF1F2;
        color: #9F1239;
        border-color: #FECDD3;
    }

    .mini-leave-chip.rejected {
        background: #FEF2F2;
        color: #991B1B;
        border-color: #FECACA;
    }

    .mini-leave-chip.cancelled {
        background: #F1F5F9;
        color: #64748B;
        border-color: #E2E8F0;
    }

    .mini-leave-chip .dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }

    .mini-leave-chip .emp-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
    }

    .mini-leave-chip .half-tag {
        font-size: 8px;
        font-weight: 850;
        text-transform: uppercase;
        background: rgba(0, 0, 0, 0.08);
        padding: 1px 3px;
        border-radius: 3px;
        flex-shrink: 0;
    }

    .more-leaves-chip {
        font-size: 9.5px;
        font-weight: 800;
        color: var(--orb-primary);
        background: var(--orb-soft);
        padding: 1px 4px;
        border-radius: 5px;
        text-align: center;
        border: 1px dashed rgba(75, 0, 232, 0.25);
    }

    /* Mobile Indicator Dots inside Calendar Cell */
    .mobile-cell-indicators {
        display: none;
    }

    /* Responsive Switch on Mobile Screens (<= 768px) */
    @media (max-width: 768px) {
        .calendar-cell {
            min-height: 52px !important;
            padding: 3px 1px !important;
            text-align: center;
            align-items: center;
        }

        .cell-top-bar {
            justify-content: center;
            margin-bottom: 2px;
            width: 100%;
        }

        .cell-date-num {
            width: 20px;
            height: 20px;
            font-size: 11px;
            border-radius: 5px;
        }

        .cell-today-pill {
            display: none !important;
        }

        .cell-holiday-chip {
            display: none !important;
        }

        .leave-chips-list {
            display: none !important;
        }

        .mobile-cell-indicators {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.5px;
            flex-wrap: wrap;
            margin-top: auto;
            padding-bottom: 2px;
            width: 100%;
        }

        .mobile-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            display: inline-block;
        }

        .mobile-dot.approved { background: #10B981; }
        .mobile-dot.pending { background: #F59E0B; }
        .mobile-dot.lwp { background: #EF4444; }

        .mobile-holiday-dot {
            color: #7E22CE;
            font-size: 8px;
            line-height: 1;
        }

        .mobile-count-pill {
            font-size: 8px;
            font-weight: 900;
            background: rgba(75, 0, 232, 0.12);
            color: var(--orb-primary);
            padding: 0 3px;
            border-radius: 4px;
            line-height: 1.2;
        }
    }

    /* 8. Mobile Agenda / List View Tab */
    #calendarListView {
        display: none;
        padding: 16px;
    }

    .agenda-day-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(15,23,42,0.03);
    }

    .agenda-day-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 8px;
        margin-bottom: 8px;
        border-bottom: 1px dashed var(--orb-border);
    }

    .agenda-date-title {
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-text);
        margin: 0;
    }

    /* 9. Detail Side Panel */
    .detail-panel {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--orb-shadow);
    }

    .detail-header {
        background: var(--orb-gradient);
        padding: 16px 20px;
        color: #fff;
    }

    .detail-date-title {
        font-size: 15px;
        font-weight: 900;
        margin: 0 0 4px;
        color: #fff;
        line-height: 1.3;
        word-break: break-word;
    }

    .detail-subtitle-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 4px;
    }

    .detail-badge-pill {
        font-size: 11px;
        font-weight: 800;
        background: rgba(255, 255, 255, 0.22);
        color: #fff;
        padding: 2.5px 9px;
        border-radius: 20px;
        backdrop-filter: blur(4px);
    }

    .detail-body {
        padding: 16px;
        max-height: 560px;
        overflow-y: auto;
    }

    @media (max-width: 1200px) {
        .detail-body {
            max-height: none;
        }
    }

    .detail-holiday-banner {
        background: linear-gradient(135deg, #FAF5FF 0%, #F3E8FF 100%);
        border: 1px solid #E9D5FF;
        border-radius: 11px;
        padding: 11px 13px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-holiday-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #7E22CE;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .detail-holiday-title {
        font-size: 13px;
        font-weight: 850;
        color: #581C87;
        margin: 0;
    }

    .detail-holiday-desc {
        font-size: 11px;
        font-weight: 600;
        color: #7E22CE;
        margin: 2px 0 0;
    }

    .panel-leave-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 10px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
        transition: transform 0.15s ease, border-color 0.15s ease;
    }

    .panel-leave-card:hover {
        border-color: #CBD5E1;
        transform: translateY(-1px);
    }

    .panel-leave-card:last-child {
        margin-bottom: 0;
    }

    .panel-card-emp-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .panel-emp-avatar {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        background: var(--orb-gradient);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12.5px;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(75, 0, 232, 0.2);
    }

    .panel-emp-name {
        font-size: 14px;
        font-weight: 850;
        color: #0F172A;
        margin: 0;
        line-height: 1.2;
    }

    .panel-emp-dept {
        font-size: 11.5px;
        font-weight: 600;
        color: #64748B;
        margin: 2px 0 0;
    }

    .panel-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 10px;
    }

    .badge-leave-type {
        font-size: 10.5px;
        font-weight: 750;
        padding: 2.5px 7px;
        border-radius: 5px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, 0.18);
    }

    .badge-leave-status {
        font-size: 10.5px;
        font-weight: 800;
        padding: 2.5px 7px;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-leave-status.approved {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
    }

    .badge-leave-status.pending {
        background: #FFFBEB;
        color: #92400E;
        border: 1px solid #FDE68A;
    }

    .badge-leave-status.rejected {
        background: #FEF2F2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }

    .badge-leave-status.cancelled {
        background: #F1F5F9;
        color: #64748B;
        border: 1px solid #E2E8F0;
    }

    .badge-lwp {
        background: #FFF1F2;
        color: #9F1239;
        border: 1px solid #FECDD3;
        font-size: 9.5px;
        font-weight: 850;
        padding: 2px 5px;
        border-radius: 4px;
    }

    .panel-meta-table {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 9px;
        padding: 7px 10px;
        font-size: 11.5px;
    }

    .panel-meta-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        border-bottom: 1px dashed #E2E8F0;
    }

    .panel-meta-row:last-child {
        border-bottom: none;
    }

    .panel-meta-lbl {
        color: #64748B;
        font-weight: 600;
    }

    .panel-meta-val {
        color: #0F172A;
        font-weight: 750;
    }

    .panel-reason-box {
        margin-top: 8px;
        background: #F8FAFC;
        border-left: 3px solid var(--orb-primary);
        border-radius: 5px;
        padding: 7px 9px;
        font-size: 11.5px;
        color: #334155;
    }

    .panel-actions {
        display: flex;
        gap: 6px;
        margin-top: 10px;
    }

    .panel-btn {
        border-radius: 7px;
        padding: 6px 10px;
        font-size: 11.5px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .panel-btn-approve {
        background: #10B981;
        color: #fff;
    }

    .panel-btn-approve:hover {
        background: #059669;
    }

    .panel-btn-reject {
        background: #EF4444;
        color: #fff;
    }

    .panel-btn-reject:hover {
        background: #DC2626;
    }
</style>
@endsection

@section('_content')
<div class="calendar-page">

    <!-- Top Hero Header -->
    <div class="cal-hero">
        <div>
            <div class="cal-kicker">
                <i class="fas fa-calendar-week"></i> HRMS &bull; TEAM AVAILABILITY
            </div>
            <h1 class="cal-title">Team Leave Calendar</h1>
            <p class="cal-subtitle">View team availability, approved leaves, pending applications, and company holidays.</p>
        </div>
        {{-- <div>
            <a href="{{ route('leave-approvals.index') }}" class="cal-hero-btn">
                <i class="fas fa-check-circle"></i> Leave Approvals
            </a>
        </div> --}}
    </div>

    @include('hrms.leave.shared.flash')

    <!-- Responsive Stat Cards Grid -->
    <div class="cal-stats-grid">
        <div class="stat-card" style="border-bottom: 3px solid var(--orb-primary);">
            <div class="stat-icon-wrap" style="background: rgba(75,0,232,0.08); color: var(--orb-primary);">
                <i class="fas fa-plane-departure"></i>
            </div>
            <div style="min-width: 0;">
                <p class="stat-lbl">On Leave Today</p>
                <h4 class="stat-val">{{ $stats['on_leave_today'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="stat-card" style="border-bottom: 3px solid #3b82f6;">
            <div class="stat-icon-wrap" style="background: rgba(59, 130, 246, 0.08); color: #3b82f6;">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div style="min-width: 0;">
                <p class="stat-lbl">Upcoming Leaves</p>
                <h4 class="stat-val">{{ $stats['upcoming_leaves'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="stat-card" style="border-bottom: 3px solid #f59e0b;">
            <div class="stat-icon-wrap" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;">
                <i class="fas fa-clock"></i>
            </div>
            <div style="min-width: 0;">
                <p class="stat-lbl">Pending Requests</p>
                <h4 class="stat-val">{{ $stats['pending_requests'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="stat-card" style="border-bottom: 3px solid #10b981;">
            <div class="stat-icon-wrap" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div style="min-width: 0;">
                <p class="stat-lbl">{{ date('M', mktime(0, 0, 0, $selectedMonth, 1)) }} Approved</p>
                <h4 class="stat-val">{{ $stats['approved_this_month'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="stat-card" style="border-bottom: 3px solid #ef4444;">
            <div class="stat-icon-wrap" style="background: rgba(239, 68, 68, 0.08); color: #ef4444;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div style="min-width: 0;">
                <p class="stat-lbl">{{ date('M', mktime(0, 0, 0, $selectedMonth, 1)) }} LWP</p>
                <h4 class="stat-val">{{ $stats['lwp_this_month'] ?? 0 }}</h4>
            </div>
        </div>
    </div>

    <!-- Enhanced Responsive Filter Panel -->
    <div class="cal-filter-card">
        <form method="GET" action="{{ route('hrms.leave.team_calendar.index') }}" id="filterForm">
            <div class="cal-filter-row">
                <div class="cal-filter-col">
                    <label class="cal-filter-label"><i class="fas fa-building text-primary"></i> Department</label>
                    <select name="department_id" id="filterDeptSelect" class="form-control select2-searchable" data-placeholder="All Departments">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cal-filter-col">
                    <label class="cal-filter-label"><i class="fas fa-user text-primary"></i> Employee</label>
                    <select name="employee_id" id="filterEmpSelect" class="form-control select2-searchable" data-placeholder="All Employees">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (string) request('employee_id') === (string) $emp->id ? 'selected' : '' }}>
                                {{ $emp->display_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="cal-filter-col">
                    <label class="cal-filter-label"><i class="fas fa-tag text-primary"></i> Leave Type</label>
                    <select name="leave_type_id" id="filterLeaveTypeSelect" class="form-control select2-searchable" data-placeholder="All Types">
                        <option value="">All Types</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ (string) request('leave_type_id') === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cal-filter-col">
                    <label class="cal-filter-label"><i class="fas fa-info-circle text-primary"></i> Status</label>
                    <select name="status" id="filterStatusSelect" class="form-control select2-searchable">
                        <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>Active (Approved &amp; Pending)</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved Only</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Only</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    </select>
                </div>

                <div class="cal-filter-col-sm">
                    <label class="cal-filter-label"><i class="fas fa-calendar-alt text-primary"></i> Month</label>
                    <select name="month" id="filterMonthSelect" class="form-control select2-searchable" data-allow-clear="false">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) $selectedMonth === (int) $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="cal-filter-col-xs">
                    <label class="cal-filter-label"><i class="fas fa-calendar text-primary"></i> Year</label>
                    <select name="year" id="filterYearSelect" class="form-control select2-searchable" data-allow-clear="false">
                        @for($y = today()->year - 2; $y <= today()->year + 2; $y++)
                            <option value="{{ $y }}" {{ (int) $selectedYear === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="cal-filter-col-actions">
                    <button type="submit" class="btn-search-theme" title="Search Leaves">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('hrms.leave.team_calendar.index') }}" class="btn-reset-theme" title="Reset All Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Calendar & Side Detail View -->
    <div class="cal-content-row">
        <!-- Calendar Grid Column -->
        <div class="cal-main-col">
            <div class="calendar-card">
                <!-- Calendar Toolbar -->
                <div class="calendar-header">
                    <div class="cal-header-left">
                        <h4 class="cal-month-title">
                            <i class="far fa-calendar-alt text-primary"></i>
                            <span>{{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}</span>
                        </h4>
                        <div class="d-flex align-items-center gap-1">
                            @php
                                $cleanParams = array_filter(request()->except(['month', 'year']), fn($v) => $v !== null && $v !== '');
                                $prevQuery = array_merge($cleanParams, ['month' => $prevMonthDate->month, 'year' => $prevMonthDate->year]);
                                $nextQuery = array_merge($cleanParams, ['month' => $nextMonthDate->month, 'year' => $nextMonthDate->year]);
                                $todayQuery = array_merge($cleanParams, ['month' => today()->month, 'year' => today()->year]);
                            @endphp
                            <a href="{{ route('hrms.leave.team_calendar.index', $prevQuery) }}" class="cal-nav-btn" title="Previous Month">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <a href="{{ route('hrms.leave.team_calendar.index', $todayQuery) }}" class="cal-nav-btn today-btn">
                                Today
                            </a>
                            <a href="{{ route('hrms.leave.team_calendar.index', $nextQuery) }}" class="cal-nav-btn" title="Next Month">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="cal-header-right">
                        <!-- View Switcher -->
                        <div class="cal-view-tabs mr-2">
                            <button type="button" class="cal-view-tab active" id="tabGridView" onclick="switchCalendarView('grid')">
                                <i class="fas fa-th-large mr-1"></i> Grid
                            </button>
                            <button type="button" class="cal-view-tab" id="tabListView" onclick="switchCalendarView('list')">
                                <i class="fas fa-list mr-1"></i> List
                            </button>
                        </div>

                        <!-- Visual Legend -->
                        <div class="cal-legend">
                            <span class="legend-item"><span class="legend-dot" style="background: #10B981;"></span> Approved</span>
                            <span class="legend-item"><span class="legend-dot" style="background: #F59E0B;"></span> Pending</span>
                            <span class="legend-item"><span class="legend-dot" style="background: #EF4444;"></span> LWP</span>
                            <span class="legend-item"><span class="legend-dot" style="background: #7E22CE;"></span> Holiday</span>
                        </div>
                    </div>
                </div>

                <!-- 1. MONTH GRID VIEW -->
                <div id="calendarGridView">
                    <!-- Week Days Header -->
                    <div class="calendar-week-headers">
                        <div class="week-day-header">Mon</div>
                        <div class="week-day-header">Tue</div>
                        <div class="week-day-header">Wed</div>
                        <div class="week-day-header">Thu</div>
                        <div class="week-day-header">Fri</div>
                        <div class="week-day-header weekend">Sat</div>
                        <div class="week-day-header weekend">Sun</div>
                    </div>

                    <!-- Calendar 7xN Grid -->
                    <div class="calendar-grid">
                        @foreach($calendarData as $dateStr => $data)
                            @php
                                $cellDate = $data['date'];
                                $isToday = $cellDate->isToday();
                                $isWeekend = $cellDate->isWeekend();
                                $leavesList = $data['leaves'];
                                $holiday = $data['holiday'] ?? null;
                                $formattedDate = $cellDate->format('l, d F Y');
                            @endphp
                            <div class="calendar-cell {{ !$data['is_current_month'] ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'cell-weekend' : '' }}" 
                                 data-date="{{ $dateStr }}" 
                                 data-formatted="{{ $formattedDate }}"
                                 onclick="selectDate('{{ $dateStr }}', '{{ $formattedDate }}')">
                                
                                <div class="cell-top-bar">
                                    <span class="cell-date-num">{{ $cellDate->day }}</span>
                                    @if($isToday)
                                        <span class="cell-today-pill">Today</span>
                                    @endif
                                </div>

                                @if($holiday)
                                    <div class="cell-holiday-chip" title="{{ $holiday->title }} (Holiday)">
                                        <i class="fas fa-gift"></i>
                                        <span>{{ $holiday->title }}</span>
                                    </div>
                                @endif

                                <!-- Desktop Leave Chips List -->
                                <div class="leave-chips-list">
                                    @foreach(collect($leavesList)->take(3) as $leave)
                                        @php
                                            $empName = optional($leave->employee)->display_name 
                                                ?? optional(optional($leave->employee)->user)->name 
                                                ?? optional($leave->employee)->employee_code 
                                                ?? 'Employee';
                                            $isLwp = ($leave->lwp_days > 0) || str_contains(strtolower($leave->leaveType->name ?? ''), 'lwp');
                                            $isHalf = (bool) $leave->is_half_day;
                                            $statusClass = $isLwp ? 'lwp' : ($leave->status ?? 'approved');
                                        @endphp
                                        <div class="mini-leave-chip {{ $statusClass }}" title="{{ $empName }} - {{ $leave->leaveType->name ?? 'Leave' }} ({{ ucfirst($leave->status) }})">
                                            <span class="dot"></span>
                                            <span class="emp-name">{{ $empName }}</span>
                                            @if($isHalf)
                                                <span class="half-tag">0.5d</span>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if(count($leavesList) > 3)
                                        <div class="more-leaves-chip">
                                            +{{ count($leavesList) - 3 }} More
                                        </div>
                                    @endif
                                </div>

                                <!-- Mobile Indicator Dots & Count -->
                                <div class="mobile-cell-indicators">
                                    @if($holiday)
                                        <span class="mobile-holiday-dot" title="{{ $holiday->title }}"><i class="fas fa-gift"></i></span>
                                    @endif
                                    @if(count($leavesList) > 0)
                                        @php
                                            $hasPending = collect($leavesList)->where('status', 'pending')->count() > 0;
                                            $hasApproved = collect($leavesList)->where('status', 'approved')->count() > 0;
                                            $hasLwp = collect($leavesList)->filter(fn($l) => ($l->lwp_days > 0) || str_contains(strtolower($l->leaveType->name ?? ''), 'lwp'))->count() > 0;
                                        @endphp
                                        @if($hasLwp)
                                            <span class="mobile-dot lwp"></span>
                                        @elseif($hasPending)
                                            <span class="mobile-dot pending"></span>
                                        @else
                                            <span class="mobile-dot approved"></span>
                                        @endif
                                        <span class="mobile-count-pill">{{ count($leavesList) }}</span>
                                    @endif
                                </div>

                                <!-- Hidden Payload For Instant Side-Panel Hydration -->
                                <input type="hidden" id="leaves-count-{{ $dateStr }}" value="{{ count($leavesList) }}">
                                <input type="hidden" id="holiday-name-{{ $dateStr }}" value="{{ $holiday ? $holiday->title : '' }}">
                                <input type="hidden" id="holiday-type-{{ $dateStr }}" value="{{ $holiday ? ucfirst($holiday->holiday_type ?? 'Company Holiday') : '' }}">

                                <div id="leaves-data-{{ $dateStr }}" style="display: none;">
                                    @foreach($leavesList as $leave)
                                        @php
                                            $empName = optional($leave->employee)->display_name 
                                                ?? optional(optional($leave->employee)->user)->name 
                                                ?? optional($leave->employee)->employee_code 
                                                ?? 'Employee';
                                            $empCode = optional($leave->employee)->employee_code ?? 'EMP';
                                            $deptName = optional(optional($leave->employee)->department)->name ?? 'General';
                                            $desigName = optional(optional($leave->employee)->designation)->name ?? '';
                                            $isLwp = ($leave->lwp_days > 0) || str_contains(strtolower($leave->leaveType->name ?? ''), 'lwp');
                                            
                                            $daysCount = $leave->total_days ?? $leave->days ?? $leave->requested_days;
                                            if (!$daysCount || $daysCount <= 0) {
                                                $daysCount = ($leave->start_date && $leave->end_date ? \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 : 1);
                                            }
                                            if ($leave->is_half_day) {
                                                $daysCount = 0.5;
                                            }

                                            // Initials Avatar
                                            $words = explode(' ', trim($empName));
                                            $initials = '';
                                            if (count($words) >= 2) {
                                                $initials = mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
                                            } else {
                                                $initials = mb_substr($empName, 0, 2);
                                            }
                                            $initials = strtoupper($initials);
                                        @endphp
                                        <div class="panel-leave-card">
                                            <div class="panel-card-emp-header">
                                                <div class="panel-emp-avatar">{{ $initials }}</div>
                                                <div style="flex: 1; min-width: 0;">
                                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                                        <h6 class="panel-emp-name text-truncate">{{ $empName }}</h6>
                                                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 10px;">{{ $empCode }}</span>
                                                    </div>
                                                    <p class="panel-emp-dept text-truncate">{{ $deptName }} {{ $desigName ? '• ' . $desigName : '' }}</p>
                                                </div>
                                            </div>

                                            <div class="panel-badge-row">
                                                <span class="badge-leave-type">
                                                    <i class="fas fa-tag mr-1" style="font-size: 9px;"></i>
                                                    {{ optional($leave->leaveType)->name ?? 'Leave' }}
                                                </span>
                                                <span class="badge-leave-status {{ $leave->status }}">
                                                    @if($leave->status === 'approved')
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                    @elseif($leave->status === 'pending')
                                                        <i class="fas fa-clock mr-1"></i>
                                                    @endif
                                                    {{ ucfirst($leave->status) }}
                                                </span>
                                                @if($isLwp)
                                                    <span class="badge-lwp">LWP</span>
                                                @endif
                                                @if($leave->is_half_day)
                                                    <span class="badge bg-warning-subtle text-warning border border-warning" style="font-size: 10px; font-weight: 800; border-radius: 5px; padding: 2px 6px;">
                                                        Half Day
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="panel-meta-table">
                                                <div class="panel-meta-row">
                                                    <span class="panel-meta-lbl">Date Span</span>
                                                    <span class="panel-meta-val">
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} &rarr; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                                    </span>
                                                </div>
                                                <div class="panel-meta-row">
                                                    <span class="panel-meta-lbl">Duration</span>
                                                    <span class="panel-meta-val">
                                                        {{ $daysCount }} {{ Str::plural('Day', $daysCount) }} {{ $leave->is_half_day ? '(Half Day)' : '(Full Day)' }}
                                                    </span>
                                                </div>
                                                @if($leave->status === 'approved' && $leave->approver)
                                                <div class="panel-meta-row">
                                                    <span class="panel-meta-lbl">Approved By</span>
                                                    <span class="panel-meta-val">{{ $leave->approver->name }}</span>
                                                </div>
                                                @endif
                                            </div>

                                            @if($leave->reason)
                                            <div class="panel-reason-box">
                                                <div class="text-muted font-weight-bold" style="font-size: 10px; text-transform: uppercase; margin-bottom: 2px;">
                                                    <i class="fas fa-quote-left mr-1"></i> Reason
                                                </div>
                                                {{ $leave->reason }}
                                            </div>
                                            @endif

                                            @if($leave->status === 'pending')
                                                @if(auth()->user()->hasPermission('leave.approvals.approve') || auth()->user()->hasPermission('leave.approvals.reject'))
                                                    <div class="panel-actions">
                                                        <form method="POST" action="{{ route('leave-approvals.approve', $leave->id) }}" style="flex: 1; margin: 0;">
                                                            @csrf
                                                            <input type="hidden" name="remark" value="Approved via Team Calendar">
                                                            <button type="submit" class="panel-btn panel-btn-approve w-100" onclick="return confirm('Approve this request for {{ $empName }}?')">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('leave-approvals.reject', $leave->id) }}" style="flex: 1; margin: 0;">
                                                            @csrf
                                                            <input type="hidden" name="reason" value="Rejected via Team Calendar">
                                                            <button type="submit" class="panel-btn panel-btn-reject w-100" onclick="return confirm('Reject this request for {{ $empName }}?')">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. MONTH LIST / AGENDA VIEW -->
                <div id="calendarListView">
                    @php
                        $agendaDays = collect($calendarData)->filter(function($d) {
                            return $d['is_current_month'] && (count($d['leaves']) > 0 || !empty($d['holiday']));
                        });
                    @endphp

                    @if($agendaDays->count() > 0)
                        @foreach($agendaDays as $dateStr => $data)
                            @php
                                $cellDate = $data['date'];
                                $holiday = $data['holiday'] ?? null;
                                $leavesList = $data['leaves'];
                            @endphp
                            <div class="agenda-day-card" onclick="selectDate('{{ $dateStr }}', '{{ $cellDate->format('l, d F Y') }}'); switchCalendarView('grid');">
                                <div class="agenda-day-header">
                                    <h6 class="agenda-date-title">
                                        <i class="far fa-calendar-alt text-primary mr-1"></i>
                                        {{ $cellDate->format('l, d F Y') }}
                                        @if($cellDate->isToday())
                                            <span class="cell-today-pill ml-1">Today</span>
                                        @endif
                                    </h6>
                                    <span class="badge bg-light text-primary border" style="font-size: 11px;">
                                        {{ count($leavesList) }} on leave
                                    </span>
                                </div>

                                @if($holiday)
                                    <div class="cell-holiday-chip mb-2" style="font-size: 11px; padding: 4px 8px;">
                                        <i class="fas fa-gift mr-1"></i>
                                        <strong>{{ $holiday->title }}</strong> ({{ ucfirst($holiday->holiday_type ?? 'Company Holiday') }})
                                    </div>
                                @endif

                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($leavesList as $leave)
                                        @php
                                            $empName = optional($leave->employee)->display_name ?? 'Employee';
                                            $isLwp = ($leave->lwp_days > 0) || str_contains(strtolower($leave->leaveType->name ?? ''), 'lwp');
                                            $statusClass = $isLwp ? 'lwp' : ($leave->status ?? 'approved');
                                        @endphp
                                        <span class="mini-leave-chip {{ $statusClass }}" style="font-size: 11px; padding: 3px 8px;">
                                            <span class="dot"></span>
                                            {{ $empName }} ({{ optional($leave->leaveType)->name ?? 'Leave' }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3 opacity-50"></i>
                            <h6 class="font-weight-bold">No Scheduled Leaves or Holidays</h6>
                            <p class="text-muted small mb-0">No leave applications found for {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- Right Side Selected Date Detail Panel -->
        <div class="cal-sidebar-col" id="calendarSidebarCol">
            <div class="detail-panel">
                <div class="detail-header">
                    <h5 class="detail-date-title" id="detail-panel-date">
                        {{ today()->format('l, d F Y') }}
                    </h5>
                    <div class="detail-subtitle-wrap">
                        <span class="text-white-50" style="font-size: 11.5px; font-weight: 600;">Team Availability</span>
                        <span class="detail-badge-pill" id="detail-count-badge">
                            <span id="total-on-leave-count">0</span> On Leave
                        </span>
                    </div>
                </div>

                <div class="detail-body" id="detail-leaves-list">
                    <!-- Populated dynamically via selectDate() -->
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('_script')
<script>
    $(function() {
        // Initialize standard searchable Select2 dropdowns
        if (typeof window.initSearchableSelects === 'function') {
            window.initSearchableSelects();
        } else if ($.fn.select2) {
            $('.select2-searchable').select2({
                width: '100%',
                theme: 'bootstrap4'
            });
        }

        // Auto submit form when Month or Year changes from dropdown
        $('#filterMonthSelect, #filterYearSelect').on('change select2:select', function() {
            $('#filterForm').submit();
        });

        // On initial page load, auto-select Today if in viewed month, otherwise select 1st day of month
        const todayStr = '{{ today()->format('Y-m-d') }}';
        let initialCell = $(`.calendar-cell[data-date="${todayStr}"]`);

        if (initialCell.length === 0 || initialCell.hasClass('other-month')) {
            initialCell = $('.calendar-cell').not('.other-month').first();
        }

        if (initialCell.length > 0) {
            initialCell.trigger('click');
        }
    });

    let currentSelectedDate = '';

    function switchCalendarView(viewMode) {
        if (viewMode === 'list') {
            $('#calendarGridView').hide();
            $('#calendarListView').show();
            $('#tabListView').addClass('active');
            $('#tabGridView').removeClass('active');
        } else {
            $('#calendarListView').hide();
            $('#calendarGridView').show();
            $('#tabGridView').addClass('active');
            $('#tabListView').removeClass('active');
        }
    }

    function selectDate(dateStr, formattedDate) {
        $('.calendar-cell').removeClass('active-cell');
        const activeCell = $(`.calendar-cell[data-date="${dateStr}"]`);
        activeCell.addClass('active-cell');

        currentSelectedDate = dateStr;

        $('#detail-panel-date').text(formattedDate);

        const leavesHtml = $(`#leaves-data-${dateStr}`).html();
        const totalLeaves = parseInt($(`#leaves-count-${dateStr}`).val() || 0);
        const holidayName = $(`#holiday-name-${dateStr}`).val();
        const holidayType = $(`#holiday-type-${dateStr}`).val();

        $('#total-on-leave-count').text(totalLeaves);

        let finalHtml = '';

        if (holidayName) {
            finalHtml += `
                <div class="detail-holiday-banner">
                    <div class="detail-holiday-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div>
                        <h6 class="detail-holiday-title">${holidayName}</h6>
                        <p class="detail-holiday-desc">${holidayType || 'Company Holiday'}</p>
                    </div>
                </div>
            `;
        }

        if (totalLeaves > 0) {
            finalHtml += leavesHtml;
        } else {
            finalHtml += `
                <div class="text-center py-4 px-2">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: #F1F5F9; color: #10B981; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 10px;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h6 style="font-size: 13.5px; font-weight: 800; color: #1E293B; margin-bottom: 3px;">All Team Members Available</h6>
                    <p class="text-muted mb-0" style="font-size: 11.5px; font-weight: 500;">No employees are on leave on this date.</p>
                </div>
            `;
        }

        $('#detail-leaves-list').html(finalHtml);

        // On small mobile screens, smoothly scroll to detail panel so user sees clicked details
        if ($(window).width() <= 768 && activeCell.length > 0) {
            const sidebarTop = $('#calendarSidebarCol').offset().top - 70;
            $('html, body').animate({ scrollTop: sidebarTop }, 250);
        }
    }
</script>
@endsection
