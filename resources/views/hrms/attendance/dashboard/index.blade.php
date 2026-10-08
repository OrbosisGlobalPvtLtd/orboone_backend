@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Attendance Dashboard')

@section('_head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
@php
$attendanceRows = ($attendances instanceof \Illuminate\Pagination\AbstractPaginator
    ? collect($attendances->items())
    : collect($attendances ?? []))->filter(function ($item) {
        return is_object($item) && !($item->is_punch_blocked || $item->is_blocked || $item->attendance_status === 'punch_blocked' || (optional($item->attendanceType)->code === 'punch_blocked'));
    });

$blockedRows = ($blockedAttendances instanceof \Illuminate\Pagination\AbstractPaginator
    ? collect($blockedAttendances->items())
    : collect($blockedAttendances ?? []))->filter(function ($item) {
        return is_object($item);
    });

$unmarkedRows = ($unmarkedEmployees instanceof \Illuminate\Pagination\AbstractPaginator
    ? collect($unmarkedEmployees->items())
    : collect($unmarkedEmployees ?? []))->filter(function ($item) {
        return is_object($item);
    });

$currentUnmarkedPerPage = (int) request('unmarked_per_page', 10);
$currentBlockedPerPage = (int) request('blocked_per_page', 10);
$currentAttendancePerPage = (int) request('attendance_per_page', request('per_page', 25));

$kpis = [
['label' => 'Present Today', 'value' => $stats['present_today'] ?? 0, 'icon' => 'fa-check-circle', 'tone' => 'success'],
['label' => 'Not Marked Today', 'value' => $stats['unmarked_today'] ?? (method_exists($unmarkedEmployees, 'total') ? $unmarkedEmployees->total() : $unmarkedRows->count()), 'icon' => 'fa-user-slash', 'tone' => 'danger'],
['label' => 'Absent Today', 'value' => $stats['absent_today'] ?? 0, 'icon' => 'fa-user-times', 'tone' => 'danger'],
['label' => 'Late Employees', 'value' => $stats['late_employees'] ?? $stats['late_today'] ?? 0, 'icon' => 'fa-user-clock', 'tone' => 'warning'],
['label' => 'Early Logout', 'value' => $stats['early_logout'] ?? $stats['early_out_today'] ?? 0, 'icon' => 'fa-running', 'tone' => 'orange'],
['label' => 'Pending Unlock', 'value' => $stats['total_blocked'] ?? $stats['punch_blocked'] ?? 0, 'icon' => 'fa-user-lock', 'tone' => 'purple'],
['label' => 'Pending Punch Out', 'value' => $stats['pending_punch_out'] ?? 0, 'icon' => 'fa-clock', 'tone' => 'info'],
['label' => 'Half Day', 'value' => $stats['half_day'] ?? $stats['half_day_today'] ?? 0, 'icon' => 'fa-adjust', 'tone' => 'amber'],
['label' => 'LWP', 'value' => $stats['lwp'] ?? $stats['lwp_today'] ?? 0, 'icon' => 'fa-calendar-minus', 'tone' => 'danger'],
['label' => 'Punch Blocked', 'value' => $stats['punch_blocked'] ?? $stats['punch_blocked_today'] ?? 0, 'icon' => 'fa-user-lock', 'tone' => 'blocked'],
['label' => 'Missed Punches', 'value' => $stats['missed_punches'] ?? $stats['missed_punch_today'] ?? 0, 'icon' => 'fa-exclamation-circle', 'tone' => 'warning'],
['label' => 'Currently Working', 'value' => $stats['currently_working'] ?? 0, 'icon' => 'fa-laptop-house', 'tone' => 'blue'],
//['label' => 'Completed Shift', 'value' => $stats['completed_shift'] ?? 0, 'icon' => 'fa-clipboard-check', 'tone' => 'success'],
['label' => 'WFO Today', 'value' => $stats['wfo_today'] ?? 0, 'icon' => 'fa-building', 'tone' => 'indigo'],
['label' => 'WFH Today', 'value' => $stats['wfh_today'] ?? 0, 'icon' => 'fa-home', 'tone' => 'teal'],
];
@endphp

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

    .att-page {
        min-height: calc(100vh - 90px);
        padding: 18px 16px 36px;
        background: var(--orb-bg);
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Modern OrboOne Hero Banner */
    .att-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 24px;
        padding: 24px 28px;
        margin-bottom: 20px;
        box-shadow: 0 16px 38px rgba(75, 0, 232, .18);
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
        right: -70px;
        top: -100px;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .12);
        pointer-events: none;
    }

    .att-hero:after {
        content: "";
        position: absolute;
        right: 180px;
        bottom: -90px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .06);
        pointer-events: none;
    }

    .att-hero-content {
        position: relative;
        z-index: 2;
        min-width: 0;
    }

    .att-hero-kicker {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .18);
        color: #fff;
        margin-bottom: 8px;
        backdrop-filter: blur(4px);
    }

    .att-hero-title {
        font-size: 28px;
        font-weight: 950;
        margin: 0;
        line-height: 1.2;
        color: #fff;
        letter-spacing: -.02em;
    }

    .att-hero-subtitle {
        font-size: 13.5px;
        font-weight: 550;
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .92);
        max-width: 820px;
        line-height: 1.5;
    }

    .att-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
    }

    .att-btn {
        border: 0;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
        white-space: nowrap;
        transition: all .2s ease;
        cursor: pointer;
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


    /* KPI Summary Cards Grid */
    .att-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(195px, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    @media (min-width: 1650px) {
        .att-kpi-grid {
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }
    }

    @media (min-width: 1200px) and (max-width: 1649px) {
        .att-kpi-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }

    @media (min-width: 860px) and (max-width: 1199px) {
        .att-kpi-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (min-width: 576px) and (max-width: 859px) {
        .att-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .att-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
    }

    .att-kpi {
        min-height: 94px;
        padding: 13px 15px 12px;
        border-radius: 18px;
        border: 1px solid #EAEFF5;
        background: #fff;
        box-shadow: 0 6px 18px rgba(16, 24, 40, .035);
        position: relative;
        overflow: hidden;
        transition: all .2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .att-kpi:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(16, 24, 40, .08);
        border-color: rgba(var(--tone-rgb, 75, 0, 232), 0.4);
    }

    .att-kpi:after {
        content: "";
        position: absolute;
        right: -20px;
        top: -24px;
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: var(--tone-soft);
        pointer-events: none;
        opacity: 0.75;
        transition: transform .3s ease;
    }

    .att-kpi:hover:after {
        transform: scale(1.18);
    }

    .att-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        position: relative;
        z-index: 1;
    }

    .att-kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tone-soft);
        color: var(--tone);
        font-size: 14px;
        flex-shrink: 0;
    }

    .att-kpi-value {
        font-size: 26px;
        line-height: 1;
        font-weight: 950;
        color: var(--orb-text);
        letter-spacing: -.02em;
    }

    .att-kpi-label {
        margin-top: 10px;
        font-size: 11px;
        color: #475467;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        position: relative;
        z-index: 1;
    }

    .att-kpi-line {
        position: absolute;
        left: 14px;
        right: 14px;
        bottom: 0px;
        height: 3px;
        border-radius: 6px 6px 0 0;
        background: linear-gradient(90deg, var(--tone), transparent);
        opacity: 0.9;
    }

    .tone-success {
        --tone: #10B981;
        --tone-soft: rgba(16, 185, 129, .13);
        --tone-rgb: 16, 185, 129;
    }

    .tone-danger {
        --tone: #EF4444;
        --tone-soft: rgba(239, 68, 68, .13);
        --tone-rgb: 239, 68, 68;
    }

    .tone-warning {
        --tone: #F59E0B;
        --tone-soft: rgba(245, 158, 11, .14);
        --tone-rgb: 245, 158, 11;
    }

    .tone-orange {
        --tone: #EA580C;
        --tone-soft: rgba(234, 88, 12, .13);
        --tone-rgb: 234, 88, 12;
    }

    .tone-amber {
        --tone: #D97706;
        --tone-soft: rgba(217, 119, 6, .13);
        --tone-rgb: 217, 119, 6;
    }

    .tone-blocked {
        --tone: #E11D48;
        --tone-soft: rgba(225, 29, 72, .13);
        --tone-rgb: 225, 29, 72;
    }

    .tone-purple {
        --tone: #8B5CF6;
        --tone-soft: rgba(139, 92, 246, .13);
        --tone-rgb: 139, 92, 246;
    }

    .tone-blue {
        --tone: #2563EB;
        --tone-soft: rgba(37, 99, 235, .13);
        --tone-rgb: 37, 99, 235;
    }

    .tone-info {
        --tone: #0284C7;
        --tone-soft: rgba(2, 132, 199, .13);
        --tone-rgb: 2, 132, 199;
    }

    .tone-indigo {
        --tone: #6366F1;
        --tone-soft: rgba(99, 102, 241, .13);
        --tone-rgb: 99, 102, 241;
    }

    .tone-teal {
        --tone: #0D9488;
        --tone-soft: rgba(13, 148, 136, .13);
        --tone-rgb: 13, 148, 136;
    }

    /* Table Cards & Containers */
    .orb-table-card,
    .att-card {
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 24px;
        background: #fff;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
    }

    .orb-table-toolbar,
    .att-section-head {
        padding: 16px 20px;
        background: linear-gradient(180deg, #fff, #FAFBFF);
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }

    .att-section-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 16px;
        font-weight: 950;
        display: flex;
        gap: 9px;
        align-items: center;
    }

    .att-section-title i {
        color: var(--orb-primary);
    }

    .att-section-subtitle {
        font-size: 12.5px;
        color: var(--orb-muted);
        font-weight: 550;
        margin-top: 3px;
    }

    .att-search {
        width: 250px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        padding: 7px 13px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        transition: all .2s ease;
        background: #fff;
    }

    .att-search:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .12);
    }

    .orb-filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .orb-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none !important;
        transition: all .2s ease;
    }

    .orb-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .2);
    }

    /* General Table Typography & Borders */
    .att-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0;
        margin-bottom: 0 !important;
    }

    .att-table thead th {
        background: #F8FAFC;
        color: #475467;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 13px 18px !important;
        border-top: none !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .att-table td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 13px 18px !important;
        vertical-align: middle !important;
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF;
    }

    /* Specific Table Widths */
    #attendanceDataTable {
        min-width: 1120px;
    }

    #unmarkedAttendanceTable,
    #blockedAttendanceTable {
        min-width: 100% !important;
        width: 100% !important;
        table-layout: auto !important;
    }

    /* Employee Cell */
    .att-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .att-emp-name {
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .att-emp-code,
    .att-small {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 750;
    }

    .att-time {
        font-size: 12px;
        font-weight: 850;
        color: #344054;
        white-space: nowrap;
    }

    .att-strong {
        font-weight: 900;
        color: var(--orb-text);
    }

    .att-badge,
    .mode-badge,
    .flag-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        white-space: nowrap;
        font-weight: 950;
        text-transform: uppercase;
    }

    .att-badge {
        padding: 6px 10px;
        font-size: 10.5px;
    }

    .mode-badge {
        padding: 6px 10px;
        font-size: 10px;
    }

    .flag-badge {
        padding: 4px 8px;
        font-size: 9px;
        margin: 2px 3px 2px 0;
    }

    .badge-present {
        background: #DCFCE7;
        color: #166534;
    }

    .badge-absent {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-half_day {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-lwp {
        background: #FEE2E2;
        color: #B42318;
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

    .flag-early {
        background: #FEF2F2;
        color: #B42318;
    }

    .flag-block {
        background: #FFE4E6;
        color: #BE123C;
    }

    .flag-clear {
        background: #F1F5F9;
        color: #475569;
    }

    .flag-missed {
        background: #FEF3C7;
        color: #92400E;
    }

    .flag-unlock {
        background: #EDE9FE;
        color: #5B21B6;
    }

    .action-dot {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #475467;
        display: inline-flex;
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
        min-width: 178px;
    }

    .att-action-menu .dropdown-item {
        border-radius: 11px;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 800;
        color: #344054;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-action-menu .dropdown-item:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .att-block-card {
        border-color: #FED7AA;
        background: radial-gradient(circle at top right, rgba(249, 115, 22, .08), transparent 24%), #fff;
    }

    .att-block-card .att-section-head {
        background: linear-gradient(135deg, #FFF7ED, #fff);
        border-bottom: 1px solid #FFEDD5;
    }

    .att-block-summary {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .att-mini-stat {
        padding: 6px 12px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #FED7AA;
        font-size: 12px;
        font-weight: 900;
        color: #9A3412;
        display: inline-flex;
        align-items: center;
    }

    /* Exact vendor.pagination.orbo Pagination Styles */
    .orb-pagination-wrapper {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
        width: 100% !important;
        padding: 12px 18px 14px !important;
        background: #FFFFFF !important;
        border-top: 1px solid #EEF2F6 !important;
        box-sizing: border-box !important;
    }

    .orb-pagination-info,
    .dataTables_info {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-muted, #6B7280) !important;
        white-space: nowrap !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .orb-pagination-nav,
    .dataTables_paginate {
        display: inline-flex !important;
        align-items: center !important;
    }

    .dataTables_paginate ul.pagination {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        flex-wrap: wrap !important;
    }

    .dataTables_paginate ul.pagination .page-item {
        margin: 0 !important;
        border: none !important;
        background: transparent !important;
    }

    .dataTables_paginate ul.pagination .page-item .page-link,
    .dataTables_paginate .paginate_button {
        height: 34px !important;
        min-width: 34px !important;
        padding: 0 12px !important;
        border-radius: 9px !important;
        border: 1px solid transparent !important;
        background: transparent !important;
        color: var(--orb-primary, #4B00E8) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.18s ease !important;
        box-shadow: none !important;
        text-decoration: none !important;
        outline: none !important;
    }

    .dataTables_paginate ul.pagination .page-item:not(.active):not(.disabled) .page-link:hover,
    .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: var(--orb-soft, #F3EDFF) !important;
        color: var(--orb-primary, #4B00E8) !important;
        border-color: transparent !important;
    }

    .dataTables_paginate ul.pagination .page-item.active .page-link,
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)) !important;
        color: #ffffff !important;
        border: none !important;
        cursor: default !important;
    }

    .dataTables_paginate ul.pagination .page-item.disabled .page-link,
    .dataTables_paginate .paginate_button.disabled,
    .dataTables_paginate .paginate_button.disabled:hover {
        background: transparent !important;
        color: #94A3B8 !important;
        border-color: transparent !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        opacity: 0.6 !important;
    }

    @media (max-width: 991px) {
        .att-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px 20px;
            gap: 16px;
        }

        .att-hero-subtitle {
            max-width: 100%;
        }

        .orb-table-toolbar {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 14px;
        }

        .orb-filter-group {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }

    @media (max-width: 768px) {
        .att-page {
            padding: 12px 10px 28px;
        }

        .att-hero {
            padding: 18px 16px;
            border-radius: 18px;
            gap: 14px;
        }

        .att-hero-title {
            font-size: 22px;
        }

        .att-hero-subtitle {
            font-size: 12.5px;
            line-height: 1.45;
        }

        .att-hero-actions {
            width: 100%;
        }

        .att-hero-actions .att-btn {
            width: 100%;
            justify-content: center;
        }

        .orb-table-toolbar {
            padding: 14px 14px !important;
        }

        .att-search {
            width: 100% !important;
        }

        .orb-filter-group {
            width: 100%;
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px;
        }

        .orb-filter-group a.orb-btn-light {
            width: 100%;
            justify-content: center;
        }

        .att-block-summary {
            width: 100%;
            flex-direction: column;
            align-items: stretch !important;
        }

        .att-mini-stat {
            justify-content: center;
        }

        .orb-pagination-wrapper {
            flex-direction: column;
            align-items: center !important;
            text-align: center;
            gap: 10px !important;
            padding: 10px 12px !important;
        }

        .orb-pagination-nav {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .att-kpi {
            min-height: 86px;
            padding: 10px 12px;
            border-radius: 14px;
        }

        .att-kpi-icon {
            width: 30px;
            height: 30px;
            font-size: 12px;
            border-radius: 9px;
        }

        .att-kpi-value {
            font-size: 22px;
        }

        .att-kpi-label {
            font-size: 10px;
            margin-top: 6px;
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

</style>

<div class="att-page">
    <div class="att-container">
        <!-- Hero Header -->
        <div class="att-hero">
            <div class="att-hero-content">
                <div class="att-hero-kicker">
                    <i class="fas fa-calendar-check"></i> HRMS &bull; ATTENDANCE
                </div>
                <h1 class="att-hero-title">Attendance Dashboard</h1>
                <p class="att-hero-subtitle">Live daily overview with punch status, blocked employees, WFO/WFH, late marks and shift completion.</p>
            </div>
            <div class="att-hero-actions">
                <a href="{{ route('attendances.daily') }}" class="att-btn att-btn-glass">
                    <i class="fas fa-list"></i> Attendance Records
                </a>
            </div>
        </div>

        @if(session('status'))
        <div class="alert alert-success" style="border-radius:16px;font-weight:800;">{{ session('status') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger" style="border-radius:16px;font-weight:800;">{{ session('error') }}</div>
        @endif

        <div class="att-kpi-grid">
            @foreach($kpis as $kpi)
            <div class="att-kpi tone-{{ $kpi['tone'] }}">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
                    <div class="att-kpi-value">{{ $kpi['value'] }}</div>
                </div>
                <div class="att-kpi-label" title="{{ $kpi['label'] }}">{{ $kpi['label'] }}</div>
                <div class="att-kpi-line"></div>
            </div>
            @endforeach
        </div>

        @if($blockedRows->count() > 0)
        <div class="orb-table-card att-block-card" style="border-color: #FED7AA;">
            <div class="orb-table-toolbar justify-content-between align-items-center">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-user-lock"></i> Punch-In Blocked Employees</h5>
                    <div class="att-section-subtitle">Employees auto-blocked after 11:15 AM because they did not punch in.</div>
                </div>
                <div class="att-block-summary">
                    <span class="att-mini-stat">Total: {{ $blockedRows->count() }}</span>
                    <span class="att-mini-stat">Pending Unlock: {{ $blockedRows->where('is_admin_unlocked', false)->count() }}</span>
                </div>
            </div>
            <div class="orb-table-tools-bar eo-toolbar">
                <div id="blockedLengthBox" class="orb-table-length-box eo-toolbar-left"></div>
                <div id="blockedExportButtons" class="orb-table-export-buttons eo-toolbar-right"></div>
            </div>
            <div class="orb-table-wrap table-responsive">
                <table class="att-table att-block-table table mb-0" id="blockedAttendanceTable" style="width:100% !important;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">S.No</th>
                                    <th>Employee</th>
                                    <th>Department / Designation</th>
                                    <th>Date</th>
                                    {{-- <th>Auto Blocked At</th> --}}
                                    <th>Block Reason</th>
                                    <th class="text-center">Regularization Request</th>
                                    <th class="text-center">Status</th>
                                    <th class="no-export text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($blockedRows as $blocked)
                                @php
                                $blockedDate = $blocked->attendance_date ? \Carbon\Carbon::parse($blocked->attendance_date)->format('d M Y') : '-';
                                $rawDate = $blocked->attendance_date ? \Carbon\Carbon::parse($blocked->attendance_date)->toDateString() : null;
                                $autoBlockedAt = $blocked->auto_blocked_at ? \Carbon\Carbon::parse($blocked->auto_blocked_at)->format('d M Y h:i A') : '-';
                                $isUnlocked = (bool) ($blocked->is_admin_unlocked ?? false);
                                $blockedTypeCode = optional($blocked->attendanceType)->code ?: ($blocked->attendance_status ?: 'default');
                                $blockedStatusLabel = $blockedTypeCode === 'punch_blocked' ? 'Punch Blocked' : (optional($blocked->attendanceType)->name ?? ucwords(str_replace('_', ' ', $blockedTypeCode)));

                                $regReq = $blocked->regularization_request ?? null;
                                if (!$regReq && $blocked->employee_id && $rawDate) {
                                    $regReq = \Illuminate\Support\Facades\DB::table('attendance_regularizations')
                                        ->where('employee_id', $blocked->employee_id)
                                        ->whereNull('deleted_at')
                                        ->where(function ($q) use ($rawDate, $blocked) {
                                            if (is_numeric($blocked->id)) {
                                                $q->where('attendance_id', $blocked->id);
                                            }
                                            $q->orWhereDate('requested_punch_in', $rawDate)
                                              ->orWhereDate('requested_punch_out', $rawDate)
                                              ->orWhereDate('created_at', $rawDate);
                                        })
                                        ->latest('id')
                                        ->first();
                                }
                                $hasReg = !empty($regReq);
                                $regStatus = $hasReg ? ($regReq->status ?? 'pending') : null;
                                @endphp
                                <tr class="att-row" data-emp-code="{{ optional($blocked->employee)->employee_code ?? 'N/A' }}" data-emp-name="{{ optional($blocked->user)->name ?? 'Employee' }}" data-department="{{ optional(optional($blocked->employee)->department)->name ?? 'N/A' }}" data-designation="{{ optional(optional($blocked->employee)->designation)->name ?? 'N/A' }}" data-date="{{ $blockedDate }}" data-reason="{{ $blocked->block_reason ?? $blocked->auto_block_reason ?? $blocked->blocked_reason ?? 'Auto blocked after 11:15 AM because employee did not punch in.' }}" data-reg="{{ $hasReg ? ('YES (' . ucwords(str_replace('_', ' ', $regStatus)) . ')') : 'NO' }}" data-status="{{ $blockedStatusLabel }} ({{ $isUnlocked ? 'Unlocked' : 'Pending Unlock' }})">
                                    <td class="text-center"><span class="font-weight-bold text-muted" style="font-size:12px;">{{ ($blockedAttendances instanceof \Illuminate\Pagination\AbstractPaginator ? ($blockedAttendances->currentPage() - 1) * $blockedAttendances->perPage() : 0) + $loop->iteration }}</span></td>
                                    <td>
                                        <div class="att-emp">
                                            @php
                                            $passportPhotoUrl = resolveEmployeePassportPhoto($blocked);
                                            $blockedName = optional($blocked->user)->name ?? 'Employee';
                                            $employeeInitial = resolveEmployeeInitials($blocked);
                                            @endphp
                                            <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                                @if($passportPhotoUrl)
                                                <img
                                                    src="{{ $passportPhotoUrl }}"
                                                    alt="{{ $blockedName }}"
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
                                            <div>
                                                <div class="att-emp-name" title="{{ optional($blocked->user)->name ?? 'N/A' }}">{{ optional($blocked->user)->name ?? 'N/A' }}</div>
                                                <div class="att-emp-code">{{ optional($blocked->employee)->employee_code ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ optional(optional($blocked->employee)->department)->name ?? 'N/A' }}</div>
                                        <div class="text-muted" style="font-size: 11px; margin-top: 2px;">{{ optional(optional($blocked->employee)->designation)->name ?? 'N/A' }}</div>
                                    </td>
                                    <td><span class="att-time">{{ $blockedDate }}</span></td>
                                    {{-- <td><span class="att-time">{{ $autoBlockedAt }}</span></td> --}}
                                    <td>
                                        <div class="att-small" style="max-width:220px;">{{ $blocked->block_reason ?? $blocked->auto_block_reason ?? $blocked->blocked_reason ?? 'Auto blocked after 11:15 AM because employee did not punch in.' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($hasReg)
                                            @php
                                                $badgeStyle = match($regStatus) {
                                                    'approved' => 'background: #DEF7EC; color: #03543F; border: 1px solid #BCF0DA;',
                                                    'rejected' => 'background: #FDE8E8; color: #9B1C1C; border: 1px solid #FBD5D5;',
                                                    'cancelled' => 'background: #F3F4F6; color: #374151; border: 1px solid #E5E7EB;',
                                                    default => 'background: #FEF08A; color: #713F12; border: 1px solid #FDE047;'
                                                };
                                            @endphp
                                            <span class="badge px-2.5 py-1" style="{{ $badgeStyle }} font-size: 11px; font-weight: 800; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-check-circle"></i> YES
                                            </span>
                                            <div class="text-capitalize text-muted font-weight-bold mt-1" style="font-size: 10px;">
                                                {{ ucwords(str_replace('_', ' ', $regStatus)) }}
                                            </div>
                                        @else
                                            <span class="badge px-2.5 py-1" style="background: #F3F4F6; color: #6B7280; border: 1px solid #E5E7EB; font-size: 11px; font-weight: 800; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-times-circle"></i> NO
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="att-badge badge-{{ $blockedTypeCode }}">{{ $blockedStatusLabel }}</span>
                                        @if($isUnlocked)
                                        <span class="flag-badge flag-unlock mt-1">Unlocked</span>
                                        @else
                                        <span class="flag-badge flag-block mt-1">Pending Unlock</span>
                                        @endif
                                        @if($blocked->unlock_type)
                                        <div class="att-small mt-1">{{ ucwords(str_replace('_', ' ', $blocked->unlock_type)) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($hasReg)
                                            <a href="{{ route('hrms.attendance.regularizations.index', ['employee_id' => $blocked->employee_id, 'from' => $rawDate, 'to' => $rawDate]) }}" 
                                               class="btn btn-sm btn-primary font-weight-bold shadow-sm" 
                                               style="border-radius: 10px; font-size: 11px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 5px;"
                                               title="View Regularization Request">
                                                <i class="fas fa-external-link-alt"></i> View Request
                                            </a>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-light text-muted font-weight-bold border" 
                                                    style="border-radius: 10px; font-size: 11px; padding: 6px 12px; cursor: not-allowed; opacity: 0.7;" 
                                                    disabled 
                                                    title="No regularization request submitted by employee yet">
                                                <i class="fas fa-lock"></i> No Request
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
            </div>
            @if($blockedAttendances instanceof \Illuminate\Pagination\AbstractPaginator)
                {{ $blockedAttendances->links('vendor.pagination.orbo') }}
            @endif
        </div>
        @endif

        <!-- Employees Not Marked Attendance Today Table -->
        <div class="orb-table-card att-unmarked-card mb-4" style="border-color: #FECACA; background: radial-gradient(circle at top right, rgba(239, 68, 68, .08), transparent 28%), #fff;">
            <div class="orb-table-toolbar justify-content-between align-items-center flex-wrap gap-3" style="background: linear-gradient(135deg, #FEF2F2, #fff); padding: 16px 18px; border-bottom: 1px solid #FEE2E2;">
                <div>
                    <h5 class="att-section-title text-danger mb-1">
                        <i class="fas fa-user-slash text-danger mr-2"></i> Employees Not Marked Attendance Today
                    </h5>
                    <div class="att-section-subtitle text-muted">
                        List of active employees who have not punched in / marked attendance today ({{ \Carbon\Carbon::parse($today ?? now())->format('d M Y') }}).
                    </div>
                </div>
                <div class="att-block-summary d-flex align-items-center gap-2">
                    <span class="att-mini-stat" style="border-color: #FCA5A5; color: #991B1B; background: #FEF2F2;">
                        <i class="fas fa-exclamation-circle mr-1"></i> Total Not Marked: {{ method_exists($unmarkedEmployees, 'total') ? $unmarkedEmployees->total() : count($unmarkedEmployees ?? []) }}
                    </span>
                </div>
            </div>
            <div class="orb-table-tools-bar eo-toolbar">
                <div id="unmarkedLengthBox" class="orb-table-length-box eo-toolbar-left"></div>
                <div id="unmarkedExportButtons" class="orb-table-export-buttons eo-toolbar-right"></div>
            </div>
            <div class="orb-table-wrap table-responsive">
                <table class="att-table table mb-0" id="unmarkedAttendanceTable" style="width:100% !important;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">S.No</th>
                                    <th>Employee</th>
                                    <th>Department / Designation</th>
                                    <th>Email / Contact</th>
                                    <th class="text-center">Today's Status</th>
                                    <th class="no-export text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unmarkedRows as $emp)
                                @php
                                    $todayAtt = $emp->today_attendance ?? null;
                                    $empUser = $emp->user;
                                    $empName = $empUser->name ?? $emp->display_name ?? 'Employee';
                                    $empCode = $emp->employee_code ?? 'N/A';
                                    $deptName = optional($emp->department)->name ?? 'N/A';
                                    $desigName = optional($emp->designation)->name ?? 'N/A';
                                    $email = $empUser->email ?? '-';
                                    $phone = optional($emp->user)->phone ?? $emp->phone ?? $emp->mobile_number ?? optional($emp->profile)->emergency_contact_number ?? '-';
                                    
                                    $statusBadgeClass = 'badge-danger';
                                    $statusText = 'Not Marked';
                                    
                                    if ($todayAtt) {
                                        if ($todayAtt->is_blocked || $todayAtt->is_punch_blocked || $todayAtt->attendance_status === 'punch_blocked') {
                                            $statusBadgeClass = 'badge-blocked';
                                            $statusText = 'Punch Blocked';
                                        } elseif (in_array($todayAtt->attendance_status, ['absent', 'lwp'], true)) {
                                            $statusBadgeClass = 'badge-danger';
                                            $statusText = 'Marked Absent';
                                        } elseif ($todayAtt->attendance_status === 'leave') {
                                            $statusBadgeClass = 'badge-info';
                                            $statusText = 'On Leave';
                                        } elseif ($todayAtt->attendance_status === 'holiday') {
                                            $statusBadgeClass = 'badge-secondary';
                                            $statusText = 'Holiday';
                                        } elseif ($todayAtt->attendance_status === 'week_off') {
                                            $statusBadgeClass = 'badge-secondary';
                                            $statusText = 'Week Off';
                                        }
                                    }
                                @endphp
                                <tr class="att-row" data-emp-code="{{ $empCode }}" data-emp-name="{{ $empName }}" data-department="{{ $deptName }}" data-designation="{{ $desigName }}" data-email="{{ $email }}" data-phone="{{ $phone !== '-' ? $phone : '-' }}" data-status="{{ $statusText }}">
                                    <td class="text-center"><span class="font-weight-bold text-muted" style="font-size:12px;">{{ ($unmarkedEmployees instanceof \Illuminate\Pagination\AbstractPaginator ? ($unmarkedEmployees->currentPage() - 1) * $unmarkedEmployees->perPage() : 0) + $loop->iteration }}</span></td>
                                    <td>
                                        <div class="att-emp">
                                            @php
                                            $passportPhotoUrl = resolveEmployeePassportPhoto($emp);
                                            $employeeInitial = resolveEmployeeInitials($emp);
                                            @endphp
                                            <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                                @if($passportPhotoUrl)
                                                 <img
                                                    src="{{ $passportPhotoUrl }}"
                                                    alt="{{ $empName }}"
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
                                            <div>
                                                <div class="att-emp-name" title="{{ $empName }}">{{ $empName }}</div>
                                                <div class="att-emp-code">{{ $empCode }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $deptName }}</div>
                                        <div class="text-muted" style="font-size: 11px; margin-top: 2px;">{{ $desigName }}</div>
                                    </td>
                                    <td>
                                        <div class="att-small font-weight-bold text-dark">{{ $email }}</div>
                                        @if($phone && $phone !== '-')
                                        <div class="text-muted att-phone" style="font-size: 11px; margin-top: 2px;"><i class="fas fa-phone-alt mr-1"></i> {{ $phone }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="att-badge {{ $statusBadgeClass }} px-2.5 py-1" style="border-radius: 8px; font-size: 11px; font-weight: 800;">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('attendances.daily', ['employee_id' => $emp->id]) }}" 
                                           class="btn btn-sm btn-light border text-primary font-weight-bold shadow-sm" 
                                           style="border-radius: 10px; font-size: 11px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 5px;"
                                           title="View Attendance Records">
                                            <i class="fas fa-history"></i> Records
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-success py-4 font-weight-bold">
                                        <i class="fas fa-check-circle mr-1"></i> Great news! All active employees have marked attendance for today.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
            </div>
            @if($unmarkedEmployees instanceof \Illuminate\Pagination\AbstractPaginator)
                {{ $unmarkedEmployees->links('vendor.pagination.orbo') }}
            @endif
        </div>

        <div class="orb-table-card">
            <div class="orb-table-toolbar justify-content-between align-items-end flex-wrap gap-3">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-calendar-day"></i> Today's Attendance</h5>
                    <div class="att-section-subtitle">Daily attendance list for today only. Full history is available in Attendance Records.</div>
                </div>
                <div class="orb-filter-group align-items-center">
                    <input type="text" id="todayAttendanceSearch" class="att-search" placeholder="Search today attendance..." value="{{ request('search') }}">
                    <a href="{{ route('attendances.index') }}" class="orb-btn-light py-2 px-3 h-auto" style="min-height: 38px !important; border-radius: 12px !important;"><i class="fas fa-sync-alt"></i> Refresh</a>
                </div>
            </div>
            <div class="orb-table-tools-bar eo-toolbar">
                <div id="attendanceLengthBox" class="orb-table-length-box eo-toolbar-left"></div>
                <div id="attendanceExportButtons" class="orb-table-export-buttons eo-toolbar-right"></div>
            </div>
            <div class="orb-table-wrap table-responsive">
                <table class="att-table table mb-0" id="attendanceDataTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">S.No</th>
                            <th>Employee</th>
                            <!-- <th>Employee Code</th> -->
                            <th>Department</th>
                            <th>Mode</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Target Out</th>
                            <th>Gross</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Flags</th>
                            <th class="no-export text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceRows as $attendance)
                        @php
                         $rawStatus = strtolower($attendance->attendance_status ?? '');
                         if (empty($rawStatus)) {
                             $rawStatus = optional($attendance->attendanceType)->code ?? 'default';
                         }
                         if ($rawStatus === 'absent') {
                             $typeCode = 'absent';
                             $statusName = '🔴 ABSENT';
                         } elseif ($rawStatus === 'lwp') {
                             $typeCode = 'lwp';
                             $statusName = '🔴 LWP';
                         } else {
                             $statusMap = [
                                 'present' => ['present', 'Present'],
                                 'half_day' => ['half_day', 'Half Day'],
                                 'absent' => ['absent', '🔴 ABSENT'],
                                 'lwp' => ['lwp', '🔴 LWP'],
                                 'missed_punch' => ['missed_punch', 'Missed Punch'],
                                 'leave' => ['leave', 'Leave'],
                                 'holiday' => ['holiday', 'Holiday'],
                                 'week_off' => ['week_off', 'Week Off'],
                                 'punch_blocked' => ['punch_blocked', 'Punch Blocked'],
                             ];
                             $mapped = $statusMap[$rawStatus] ?? null;
                             if ($mapped) {
                                 $typeCode = $mapped[0];
                                 $statusName = $mapped[1];
                             } else {
                                 $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                                 $statusName = optional($attendance->attendanceType)->name ?? 'N/A';
                                 if ($typeCode === 'lwp') {
                                     $typeCode = 'lwp';
                                     $statusName = '🔴 LWP';
                                 }
                             }
                         }
                        $modeCode = strtolower($attendance->work_mode ?? '');
                        $modeLabel = $modeCode === 'wfh' ? 'WFH' : ($modeCode === 'wfo' ? 'WFO' : '-');
                        $modeClass = in_array($modeCode, ['wfo', 'wfh']) ? $modeCode : 'default';
                        $grossMinutes = (int) ($attendance->gross_work_minutes ?? 0);
                        $netMinutes = (int) ($attendance->total_work_minutes ?? 0);
                        $grossText = $grossMinutes > 0 ? floor($grossMinutes / 60).'h '.($grossMinutes % 60).'m' : ($attendance->gross_duration ?? '-');
                        $netText = $netMinutes > 0 ? floor($netMinutes / 60).'h '.($netMinutes % 60).'m' : ($attendance->net_duration ?? '-');
                        @endphp
                        <tr class="att-row" data-emp-code="{{ optional($attendance->employee)->employee_code ?? 'N/A' }}" data-emp-name="{{ optional($attendance->user)->name ?? 'Employee' }}" data-department="{{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}" data-mode="{{ $modeLabel }}" data-shift="{{ optional($attendance->attendanceTime)->name ?? '-' }}" data-punch-in="{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}" data-punch-out="{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}" data-target-out="{{ $attendance->target_punch_out_time ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A') : '-' }}" data-gross="{{ $grossText }}" data-net="{{ $netText }}" data-status="{{ $statusName }}" data-flags="{{ $attendance->is_late ? 'Late ' . ($attendance->late_minutes ?? 0) . 'm ' : '' }}{{ $attendance->is_early_out ? 'Early ' . ($attendance->early_out_minutes ?? 0) . 'm ' : '' }}{{ $attendance->missed_punch ? 'Missed ' : '' }}{{ ($attendance->is_blocked || $attendance->is_punch_blocked) ? 'Blocked ' : '' }}{{ $attendance->is_admin_unlocked ? 'Unlocked ' : '' }}{{ (!$attendance->is_late && !$attendance->is_early_out && !$attendance->missed_punch && !$attendance->is_blocked && !$attendance->is_punch_blocked) ? 'Clear' : '' }}">
                            <td class="text-center"><span class="font-weight-bold text-muted" style="font-size:12px;">{{ ($attendances instanceof \Illuminate\Pagination\AbstractPaginator ? ($attendances->currentPage() - 1) * $attendances->perPage() : 0) + $loop->iteration }}</span></td>
                            <td>
                                <div class="att-emp">
                                    @php
                                    $passportPhotoUrl = resolveEmployeePassportPhoto($attendance);
                                    $employeeName = optional($attendance->user)->name ?? 'Employee';
                                    $employeeInitial = resolveEmployeeInitials($attendance);
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
                                    <div>
                                        <div class="att-emp-name" title="{{ optional($attendance->user)->name ?? 'N/A' }}">{{ optional($attendance->user)->name ?? 'N/A' }}</div>
                                        <div class="att-emp-code">{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- <td>{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</td> -->
                            <td>
                                <div class="att-small">{{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}</div>
                            </td>
                            <td><span class="mode-badge mode-{{ $modeClass }}">{{ $modeLabel }}</span></td>
                            <td>
                                <div class="att-small">{{ optional($attendance->attendanceTime)->name ?? '-' }}</div>
                            </td>
                            <td><span class="att-time">{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}</span></td>
                            <td><span class="att-time">{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}</span></td>
                            <td><span class="att-time">{{ $attendance->target_punch_out_time ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A') : '-' }}</span></td>
                            <td><span class="att-small att-strong">{{ $grossText }}</span></td>
                            <td><span class="att-small att-strong">{{ $netText }}</span></td>
                            <td><span class="att-badge badge-{{ $typeCode }}">{{ $statusName }}</span></td>
                            <td>
                                @if($attendance->is_late)
                                <span class="flag-badge flag-late">Late {{ $attendance->late_minutes ?? 0 }}m</span>
                                @endif
                                @if($attendance->is_early_out)
                                <span class="flag-badge flag-early">Early {{ $attendance->early_out_minutes ?? 0 }}m</span>
                                @endif
                                @if($attendance->missed_punch ?? false)
                                <span class="flag-badge flag-missed">Missed</span>
                                @endif
                                @if(($attendance->is_blocked ?? false) || ($attendance->is_punch_blocked ?? false))
                                <span class="flag-badge flag-block">Blocked</span>
                                @endif
                                @if(($attendance->is_admin_unlocked ?? false))
                                <span class="flag-badge flag-unlock">Unlocked</span>
                                @endif
                                @if(!$attendance->is_late && !$attendance->is_early_out && !($attendance->missed_punch ?? false) && !($attendance->is_blocked ?? false) && !($attendance->is_punch_blocked ?? false))
                                <span class="flag-badge flag-clear">Clear</span>
                                @endif
                            </td>
                            <td>
                                <div class="att-action-wrap dropdown">
                                    <button class="action-dot" type="button" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                        @if($canManageAttendance ?? false)
                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $attendance->id }}"><i class="fas fa-edit text-primary"></i> Edit</button>
                                        @endif
                                        <a href="{{ route('attendances.daily', ['employee_id' => optional($attendance->employee)->id]) }}" class="dropdown-item"><i class="fas fa-eye text-info"></i> View Records</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($attendances instanceof \Illuminate\Pagination\AbstractPaginator)
                {{ $attendances->links('vendor.pagination.orbo') }}
            @endif
        </div>      </div>

        @php $renderedModalIds = []; @endphp
        @foreach($attendanceRows as $attendance)
        @php $renderedModalIds[] = $attendance->id; @endphp
        @if($canManageAttendance ?? false)
        @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
        @endif
        @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
        @endforeach

        @foreach($blockedRows as $attendance)
        @if(!in_array($attendance->id, $renderedModalIds))
        @if($canManageAttendance ?? false)
        @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
        @endif
        @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
        @endif
        @endforeach
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
    document.addEventListener('DOMContentLoaded', function() {
        // Clean formatter for DataTable exports
        const cleanExportData = function(data, row, column, node) {
            if (!node) return (data || '').trim();
            const $cell = $(node);

            // 1. Employee cell: Name (Code)
            const $emp = $cell.find('.att-emp');
            if ($emp.length) {
                const name = $cell.find('.att-emp-name').text().trim();
                const code = $cell.find('.att-emp-code').text().trim();
                return code && code !== 'N/A' ? name + ' (' + code + ')' : name;
            }

            // 2. Department & Designation or Multi-line cells
            const $bold = $cell.find('.font-weight-bold, .att-strong');
            const $muted = $cell.find('.text-muted, .att-small:not(.att-strong)');
            if ($bold.length && $muted.length) {
                const bText = $bold.first().text().trim();
                const mText = $muted.first().text().trim();
                if (bText && mText && bText !== mText) {
                    return bText + ' - ' + mText;
                }
            }

            // 3. Status Badges / Flags
            const $badges = $cell.find('.att-badge, .badge, .mode-badge, .flag-badge');
            if ($badges.length) {
                const bList = [];
                $badges.each(function() {
                    const txt = $(this).text().trim();
                    if (txt && !bList.includes(txt)) bList.push(txt);
                });
                if (bList.length) return bList.join(', ');
            }

            // 4. Default: remove icons, avatars, buttons, line breaks
            const $clone = $cell.clone();
            $clone.find('.hrms-emp-avatar, i, svg, button, .dropdown, script').remove();
            return $clone.text().replace(/\s+/g, ' ').trim();
        };

        // Custom Tabular Exporters for CSV, Excel, PDF & Print with separated columns
        function buildAttendanceExportData(data) {
            data.header = ['S.No', 'Employee Code', 'Employee Name', 'Department', 'Work Mode', 'Shift', 'Punch In', 'Punch Out', 'Target Out', 'Gross Hours', 'Net Hours', 'Status', 'Flags'];
            const rows = [];
            $('#attendanceDataTable tbody tr').each(function(i) {
                const $tr = $(this);
                if ($tr.find('td').length < 12 || $tr.find('.dataTables_empty').length) return;
                const sNo = $tr.find('td:eq(0)').text().trim() || (i + 1).toString();
                const empCode = $tr.attr('data-emp-code') || $tr.find('.att-emp-code').text().trim() || '-';
                const empName = $tr.attr('data-emp-name') || $tr.find('.att-emp-name').text().trim() || '-';
                const dept = $tr.attr('data-department') || $tr.find('td:eq(2)').text().trim() || '-';
                const mode = $tr.attr('data-mode') || $tr.find('td:eq(3)').text().trim() || '-';
                const shift = $tr.attr('data-shift') || $tr.find('td:eq(4)').text().trim() || '-';
                const punchIn = $tr.attr('data-punch-in') || $tr.find('td:eq(5)').text().trim() || '-';
                const punchOut = $tr.attr('data-punch-out') || $tr.find('td:eq(6)').text().trim() || '-';
                const targetOut = $tr.attr('data-target-out') || $tr.find('td:eq(7)').text().trim() || '-';
                const gross = $tr.attr('data-gross') || $tr.find('td:eq(8)').text().trim() || '-';
                const net = $tr.attr('data-net') || $tr.find('td:eq(9)').text().trim() || '-';
                const status = $tr.attr('data-status') || $tr.find('td:eq(10) .att-badge, td:eq(10) .badge').text().trim() || '-';
                const flags = $tr.attr('data-flags') || $tr.find('td:eq(11)').text().replace(/\s+/g, ' ').trim() || 'Clear';

                rows.push([sNo, empCode, empName, dept, mode, shift, punchIn, punchOut, targetOut, gross, net, status, flags]);
            });
            data.body = rows;
        }

        function buildBlockedExportData(data) {
            data.header = ['S.No', 'Employee Code', 'Employee Name', 'Department', 'Designation', 'Date', 'Block Reason', 'Regularization Request', 'Status'];
            const rows = [];
            $('#blockedAttendanceTable tbody tr').each(function(i) {
                const $tr = $(this);
                if ($tr.find('td').length < 7 || $tr.find('.dataTables_empty').length) return;
                const sNo = $tr.find('td:eq(0)').text().trim() || (i + 1).toString();
                const empCode = $tr.attr('data-emp-code') || $tr.find('.att-emp-code').text().trim() || '-';
                const empName = $tr.attr('data-emp-name') || $tr.find('.att-emp-name').text().trim() || '-';
                const dept = $tr.attr('data-department') || $tr.find('td:eq(2) .font-weight-bold').text().trim() || '-';
                const desig = $tr.attr('data-designation') || $tr.find('td:eq(2) .text-muted').text().trim() || '-';
                const date = $tr.attr('data-date') || $tr.find('td:eq(3)').text().trim() || '-';
                const reason = $tr.attr('data-reason') || $tr.find('td:eq(4)').text().replace(/\s+/g, ' ').trim() || '-';
                const reg = $tr.attr('data-reg') || $tr.find('td:eq(5)').text().replace(/\s+/g, ' ').trim() || '-';
                const status = $tr.attr('data-status') || $tr.find('td:eq(6)').text().replace(/\s+/g, ' ').trim() || '-';

                rows.push([sNo, empCode, empName, dept, desig, date, reason, reg, status]);
            });
            data.body = rows;
        }

        function buildUnmarkedExportData(data) {
            data.header = ['S.No', 'Employee Code', 'Employee Name', 'Department', 'Designation', 'Email', 'Phone', "Today's Status"];
            const rows = [];
            $('#unmarkedAttendanceTable tbody tr').each(function(i) {
                const $tr = $(this);
                if ($tr.find('td').length < 5 || $tr.find('.dataTables_empty').length) return;
                const sNo = $tr.find('td:eq(0)').text().trim() || (i + 1).toString();
                const empCode = $tr.attr('data-emp-code') || $tr.find('.att-emp-code').text().trim() || '-';
                const empName = $tr.attr('data-emp-name') || $tr.find('.att-emp-name').text().trim() || '-';
                const dept = $tr.attr('data-department') || $tr.find('td:eq(2) .font-weight-bold').text().trim() || '-';
                const desig = $tr.attr('data-designation') || $tr.find('td:eq(2) .text-muted').text().trim() || '-';
                const email = $tr.attr('data-email') || $tr.find('td:eq(3) .att-small').text().trim() || '-';
                const phone = $tr.attr('data-phone') || $tr.find('.att-phone').text().replace(/[^0-9+\s-]/g, '').trim() || '-';
                const status = $tr.attr('data-status') || $tr.find('td:eq(4) .att-badge, td:eq(4) .badge').text().trim() || 'Not Marked';

                rows.push([sNo, empCode, empName, dept, desig, email, phone, status]);
            });
            data.body = rows;
        }

        // Dedicated UTF-8 CSV Generator (Supports separated customized columns & Excel compatibility)
        function triggerCsvExport(buildDataFn, defaultFilename) {
            const data = {};
            buildDataFn(data);
            const filename = defaultFilename.endsWith('.csv') ? defaultFilename : (defaultFilename + '.csv');
            const csvRows = [];
            if (data.header && data.header.length) {
                csvRows.push(data.header.map(function(h) {
                    return '"' + (h || '').toString().replace(/"/g, '""') + '"';
                }).join(','));
            }
            if (data.body && data.body.length) {
                data.body.forEach(function(row) {
                    csvRows.push(row.map(function(cell) {
                        return '"' + (cell || '').toString().replace(/"/g, '""') + '"';
                    }).join(','));
                });
            }
            const csvContent = '\uFEFF' + csvRows.join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        // Render custom length dropdown (10, 25, 50, 100 entries) connected to server pagination
        function renderLengthSelector(containerSelector, paramName, pageParamName, currentValue) {
            const options = [10, 25, 50, 100];
            const currentNum = parseInt(currentValue, 10) || 10;
            if (!options.includes(currentNum)) {
                options.push(currentNum);
                options.sort(function(a, b) { return a - b; });
            }
            let optsHtml = '';
            options.forEach(function(opt) {
                optsHtml += '<option value="' + opt + '"' + (opt === currentNum ? ' selected' : '') + '>' + opt + '</option>';
            });

            const $lengthSelect = $(
                '<div class="dataTables_length d-flex align-items-center">' +
                    '<label class="eo-entries-label mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size:13px; gap:8px;">' +
                        '<span>Show</span>' +
                        '<select class="table-per-page-select orb-per-page-select" ' +
                                'data-param="' + paramName + '" ' +
                                'data-page-param="' + pageParamName + '" style="width: 75px;">' +
                            optsHtml +
                        '</select>' +
                        '<span>entries</span>' +
                    '</label>' +
                '</div>'
            );
            $(containerSelector).empty().append($lengthSelect);

            if ($.fn.select2) {
                const $select = $lengthSelect.find('select');
                $select.select2({
                    minimumResultsForSearch: Infinity,
                    width: '75px',
                    dropdownCssClass: 'select2-dropdown-per-page',
                    containerCssClass: 'select2-container--per-page'
                });
            }
        }

        // Global change event handler for pagination per-page selection
        $(document).on('change', '.orb-per-page-select', function() {
            const param = $(this).data('param') || 'per_page';
            const pageParam = $(this).data('page-param') || 'page';
            const val = $(this).val();
            const url = new URL(window.location.href);
            url.searchParams.set(param, val);
            if (param === 'attendance_per_page') {
                url.searchParams.set('per_page', val);
            }
            url.searchParams.delete(pageParam);
            if (pageParam === 'attendance_page') {
                url.searchParams.delete('page');
            }
            window.location.href = url.toString();
        });

        const todayTable = $('#attendanceDataTable').DataTable({
            paging: false,
            info: false,
            ordering: true,
            responsive: false,
            autoWidth: false,
            scrollX: false,
            searching: true,
            columnDefs: [
                { targets: 0, width: "50px", className: "text-center" },
                { targets: 12, orderable: false, className: "text-right" }
            ],
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    className: 'btn btn-sm btn-export-csv',
                    title: 'Today Attendance Report',
                    action: function(e, dt, button, config) {
                        triggerCsvExport(buildAttendanceExportData, 'Today Attendance Report.csv');
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-sm btn-export-excel',
                    title: 'Today Attendance Report',
                    customizeData: function(data) {
                        buildAttendanceExportData(data);
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    className: 'btn btn-sm btn-export-pdf',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Today Attendance Report',
                    customize: function(doc) {
                        const data = {};
                        buildAttendanceExportData(data);
                        doc.content[1].table.headerRows = 1;
                        const body = [];
                        body.push(data.header.map(function(h) { return { text: h, bold: true, fillColor: '#F1F5F9', fontSize: 7 }; }));
                        data.body.forEach(function(row, i) {
                            body.push(row.map(function(cell) { return { text: cell, fontSize: 6.5, fillColor: i % 2 === 0 ? '#FFFFFF' : '#F8FAFC' }; }));
                        });
                        doc.content[1].table.body = body;
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    className: 'btn btn-sm btn-export-print',
                    title: 'Today Attendance Report',
                    customize: function(win) {
                        const data = {};
                        buildAttendanceExportData(data);
                        let tableHtml = '<table class="table table-bordered" style="width:100%; border-collapse:collapse; font-size:11px;"><thead><tr style="background:#F8FAFC;">';
                        data.header.forEach(function(h) { tableHtml += '<th style="padding:6px 8px; border:1px solid #CBD5E1;">' + h + '</th>'; });
                        tableHtml += '</tr></thead><tbody>';
                        data.body.forEach(function(row) {
                            tableHtml += '<tr>';
                            row.forEach(function(cell) { tableHtml += '<td style="padding:6px 8px; border:1px solid #E2E8F0;">' + cell + '</td>'; });
                            tableHtml += '</tr>';
                        });
                        tableHtml += '</tbody></table>';
                        $(win.document.body).find('table').replaceWith(tableHtml);
                    }
                }
            ],
            language: {
                emptyTable: 'No attendance records found for today.'
            },
            initComplete: function() {
                $('#attendanceDataTable_wrapper .dt-buttons').appendTo('#attendanceExportButtons');
            }
        });

        renderLengthSelector('#attendanceLengthBox', 'attendance_per_page', 'attendance_page', {{ $currentAttendancePerPage }});

        $('#todayAttendanceSearch').on('keyup', function(e) {
            if (e.key === 'Enter') {
                const url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('search', this.value);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('attendance_page');
                window.location.href = url.toString();
            } else {
                todayTable.search(this.value).draw();
            }
        });

        if ($('#blockedAttendanceTable').length) {
            $('#blockedAttendanceTable').DataTable({
                paging: false,
                info: false,
                ordering: true,
                autoWidth: false,
                scrollX: false,
                searching: false,
                columnDefs: [
                    { targets: 0, width: "50px", className: "text-center" },
                    { targets: 1, width: "22%" },
                    { targets: 2, width: "16%" },
                    { targets: 3, width: "12%" },
                    { targets: 4, width: "20%" },
                    { targets: 5, width: "10%", className: "text-center" },
                    { targets: 6, width: "10%", className: "text-center" },
                    { targets: 7, width: "10", orderable: false, className: "text-right" }
                ],
                dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                        className: 'btn btn-sm btn-export-csv',
                        title: 'Punch-In Blocked Employees',
                        action: function(e, dt, button, config) {
                            triggerCsvExport(buildBlockedExportData, 'Punch-In Blocked Employees.csv');
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                        className: 'btn btn-sm btn-export-excel',
                        title: 'Punch-In Blocked Employees',
                        customizeData: function(data) {
                            buildBlockedExportData(data);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                        className: 'btn btn-sm btn-export-pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Punch-In Blocked Employees',
                        customize: function(doc) {
                            const data = {};
                            buildBlockedExportData(data);
                            doc.content[1].table.headerRows = 1;
                            const body = [];
                            body.push(data.header.map(function(h) { return { text: h, bold: true, fillColor: '#F1F5F9', fontSize: 7.5 }; }));
                            data.body.forEach(function(row, i) {
                                body.push(row.map(function(cell) { return { text: cell, fontSize: 7, fillColor: i % 2 === 0 ? '#FFFFFF' : '#F8FAFC' }; }));
                            });
                            doc.content[1].table.body = body;
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Print',
                        className: 'btn btn-sm btn-export-print',
                        title: 'Punch-In Blocked Employees',
                        customize: function(win) {
                            const data = {};
                            buildBlockedExportData(data);
                            let tableHtml = '<table class="table table-bordered" style="width:100%; border-collapse:collapse; font-size:11px;"><thead><tr style="background:#F8FAFC;">';
                            data.header.forEach(function(h) { tableHtml += '<th style="padding:6px 8px; border:1px solid #CBD5E1;">' + h + '</th>'; });
                            tableHtml += '</tr></thead><tbody>';
                            data.body.forEach(function(row) {
                                tableHtml += '<tr>';
                                row.forEach(function(cell) { tableHtml += '<td style="padding:6px 8px; border:1px solid #E2E8F0;">' + cell + '</td>'; });
                                tableHtml += '</tr>';
                            });
                            tableHtml += '</tbody></table>';
                            $(win.document.body).find('table').replaceWith(tableHtml);
                        }
                    }
                ],
                language: {
                    emptyTable: 'No punch-in blocked employees today.'
                },
                initComplete: function() {
                    $('#blockedAttendanceTable_wrapper .dt-buttons').appendTo('#blockedExportButtons');
                }
            });

            renderLengthSelector('#blockedLengthBox', 'blocked_per_page', 'blocked_page', {{ $currentBlockedPerPage }});
        }

        if ($('#unmarkedAttendanceTable').length) {
            $('#unmarkedAttendanceTable').DataTable({
                paging: false,
                info: false,
                ordering: true,
                autoWidth: false,
                scrollX: false,
                searching: false,
                columnDefs: [
                    { targets: 0, width: "50px", className: "text-center" },
                    { targets: 1, width: "26%" },
                    { targets: 2, width: "22%" },
                    { targets: 3, width: "24%" },
                    { targets: 4, width: "16%", className: "text-center" },
                    { targets: 5, width: "12%", orderable: false, className: "text-right" }
                ],
                dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                        className: 'btn btn-sm btn-export-csv',
                        title: 'Employees Not Marked Attendance Today',
                        action: function(e, dt, button, config) {
                            triggerCsvExport(buildUnmarkedExportData, 'Employees Not Marked Attendance Today.csv');
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                        className: 'btn btn-sm btn-export-excel',
                        title: 'Employees Not Marked Attendance Today',
                        customizeData: function(data) {
                            buildUnmarkedExportData(data);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                        className: 'btn btn-sm btn-export-pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Employees Not Marked Attendance Today',
                        customize: function(doc) {
                            const data = {};
                            buildUnmarkedExportData(data);
                            doc.content[1].table.headerRows = 1;
                            const body = [];
                            body.push(data.header.map(function(h) { return { text: h, bold: true, fillColor: '#F1F5F9', fontSize: 7.5 }; }));
                            data.body.forEach(function(row, i) {
                                body.push(row.map(function(cell) { return { text: cell, fontSize: 7, fillColor: i % 2 === 0 ? '#FFFFFF' : '#F8FAFC' }; }));
                            });
                            doc.content[1].table.body = body;
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Print',
                        className: 'btn btn-sm btn-export-print',
                        title: 'Employees Not Marked Attendance Today',
                        customize: function(win) {
                            const data = {};
                            buildUnmarkedExportData(data);
                            let tableHtml = '<table class="table table-bordered" style="width:100%; border-collapse:collapse; font-size:11px;"><thead><tr style="background:#F8FAFC;">';
                            data.header.forEach(function(h) { tableHtml += '<th style="padding:6px 8px; border:1px solid #CBD5E1;">' + h + '</th>'; });
                            tableHtml += '</tr></thead><tbody>';
                            data.body.forEach(function(row) {
                                tableHtml += '<tr>';
                                row.forEach(function(cell) { tableHtml += '<td style="padding:6px 8px; border:1px solid #E2E8F0;">' + cell + '</td>'; });
                                tableHtml += '</tr>';
                            });
                            tableHtml += '</tbody></table>';
                            $(win.document.body).find('table').replaceWith(tableHtml);
                        }
                    }
                ],
                language: {
                    emptyTable: 'All active employees have marked attendance today.'
                },
                initComplete: function() {
                    $('#unmarkedAttendanceTable_wrapper .dt-buttons').appendTo('#unmarkedExportButtons');
                }
            });

            renderLengthSelector('#unmarkedLengthBox', 'unmarked_per_page', 'unmarked_page', {{ $currentUnmarkedPerPage }});
        }
    });
</script>
@endsection
