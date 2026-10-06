@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', request()->routeIs('hrms.attendance.my') ? 'My Attendance' : 'Attendance Records')

@section('_head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
    .select2-container--default .select2-selection--single {
        height: 43px !important;
        border: 1px solid #E4E7EC !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        background-color: #fff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 41px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #101828 !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 41px !important;
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
@endsection

@section('_content')
@php
    $isMyAttendance = request()->routeIs('hrms.attendance.my');
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
        border-radius: 30px;
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
        background: rgba(255, 255, 255, .12)
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


    .att-metric-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
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
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--orb-shadow);
        width: 100%;
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
        color: var(--orb-primary)
    }

    .att-section-sub {
        font-size: 13px;
        color: var(--orb-muted);
        font-weight: 600;
        margin-top: 4px;
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

    .att-total-pill {
        border: 1px solid #FAD7AA;
        background: #FFF7ED;
        color: #C2410C;
        border-radius: 10px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
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

    .att-filter-group label {
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        color: #667085;
        margin-bottom: 6px;
        display: block;
        letter-spacing: .04em;
    }

    .att-filter-group .form-control {
        height: 42px;
        border-radius: 12px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        font-weight: 700;
        padding: 0 12px;
        box-shadow: none !important;
        background: #fff;
    }

    .att-filter-group .form-control:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .10) !important;
    }

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

    .att-table {
        width: 100% !important;
        min-width: 1040px;
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
        padding: 11px 8px !important;
        border-top: none !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .att-table tbody td {
        background: #fff;
        border-bottom: 1px solid #F2F4F7 !important;
        padding: 9px 8px !important;
        vertical-align: middle !important;
        white-space: nowrap;
        font-size: 12.5px;
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF !important;
    }

    .att-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0
    }

    .att-avatar {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--orb-soft), #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 950;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .08);
        flex-shrink: 0;
        position: relative !important;
        overflow: hidden !important;
    }

    .att-avatar-img {
        width: 40px !important;
        height: 40px !important;
        border-radius: 14px !important;
        object-fit: cover !important;
        display: block !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
        flex-shrink: 0 !important;
    }

    .att-emp-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--orb-text);
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .att-emp-code {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .att-badge,
    .mode-badge,
    .flag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-weight: 950;
        text-transform: uppercase
    }

    .att-badge,
    .mode-badge {
        padding: 6px 10px;
        font-size: 10px
    }

    .flag {
        padding: 4px 8px;
        font-size: 9px;
        margin: 2px 3px 2px 0
    }

    .badge-present {
        background: #DCFCE7;
        color: #166534
    }

    .badge-absent,
    .badge-lwp {
        background: #FEE2E2;
        color: #991B1B
    }

    .badge-half_day {
        background: #FEF3C7;
        color: #92400E
    }

    .badge-leave {
        background: #DBEAFE;
        color: #1E40AF
    }

    .badge-week_off {
        background: #F1F5F9;
        color: #475569
    }

    .badge-holiday {
        background: #EDE9FE;
        color: #5B21B6
    }

    .badge-punch_blocked {
        background: #FFE4E6;
        color: #BE123C
    }

    .badge-missed_punch {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .badge-default {
        background: #F1F5F9;
        color: #475569
    }

    .mode-wfo {
        background: #EEF2FF;
        color: #3730A3
    }

    .mode-wfh {
        background: #ECFEFF;
        color: #155E75
    }

    .mode-default {
        background: #F1F5F9;
        color: #475569
    }

    .flag-late {
        background: #FFF7ED;
        color: #C2410C
    }

    .flag-early,
    .flag-blocked {
        background: #FEF2F2;
        color: #B42318
    }

    .flag-missed {
        background: #FEF3C7;
        color: #92400E
    }

    .flag-clear {
        background: #F1F5F9;
        color: #475569
    }

    .att-task {
        max-width: 205px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 650
    }

    .att-action-wrap {
        display: flex;
        justify-content: flex-end
    }

    .action-dot {
        width: 35px;
        height: 35px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #475467;
        display: flex;
        align-items: center;
        justify-content: center
    }

    .action-dot:hover {
        background: var(--orb-soft);
        color: var(--orb-primary)
    }

    .dropdown-menu.att-action-menu {
        border: 1px solid #EAECF0;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(16, 24, 40, 0.1), 0 8px 10px -6px rgba(16, 24, 40, 0.06);
        padding: 6px;
        min-width: 175px;
        background: #ffffff;
    }

    .att-action-menu .dropdown-item {
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 600;
        color: #344054;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.15s ease;
    }

    .att-action-menu .dropdown-item i {
        font-size: 13px;
        width: 16px;
        text-align: center;
        flex-shrink: 0;
    }

    .att-action-menu .dropdown-item:hover {
        background: #F4F3FF;
        color: var(--orb-primary, #4B00E8);
    }

    .att-action-menu .dropdown-item.disabled,
    .att-action-menu .dropdown-item:disabled {
        color: #98A2B3 !important;
        background: transparent !important;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Premium unified Datatables styles */
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

    .dataTables_length select {
        border-radius: 10px !important;
        padding: 4px 22px 4px 8px !important;
        height: 38px !important;
        border: 1px solid #E7EAF3 !important;
    }

    .leave-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        background: #fff;
        border-top: 1px solid #E7EAF3;
    }

    .dataTables_scroll {
        border: 1px solid #EEF2F6;
        border-radius: 18px;
        overflow: hidden;
        margin: 16px 24px;
    }

    .dataTables_scrollHead {
        background: #F8FAFC
    }

    .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        border-bottom: 0 !important
    }

    .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #D0D5DD;
        border-radius: 20px
    }

    .dataTables_info {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 700
    }

    .page-link {
        border-radius: 10px !important;
        margin: 0 2px;
        border-color: var(--orb-border);
        color: var(--orb-primary);
        font-weight: 800
    }

    .page-item.active .page-link {
        background: var(--orb-primary) !important;
        border-color: var(--orb-primary) !important;
        color: #fff !important
    }

    @media(max-width:1300px) {
        .att-metric-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .att-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width:992px) {
        .leave-dt-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .leave-dt-right {
            justify-content: flex-end;
        }
    }

    @media(max-width:768px) {
        .att-page {
            padding: 12px 8px 25px
        }

        .att-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px;
            border-radius: 24px
        }

        .att-title {
            font-size: 25px
        }

        .att-hero-actions {
            width: 100%
        }

        .att-btn {
            width: 100%
        }

        .att-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .att-section-head {
            flex-direction: column;
            gap: 12px;
        }

        .att-head-badges {
            justify-content: flex-start
        }

        .att-filter-grid {
            grid-template-columns: 1fr
        }
    }
</style>

<div class="att-page">
    <div class="att-container">

        @php
        $isPaginator = $attendances instanceof \Illuminate\Pagination\AbstractPaginator;
        $recordItems = $isPaginator ? collect($attendances->items()) : collect($attendances);
        $totalRecords = isset($stats['total']) ? (int) $stats['total'] : ($isPaginator ? $attendances->total() : $recordItems->count());
        $presentRecords = isset($stats['present']) ? (int) $stats['present'] : $recordItems->filter(fn($a) => optional($a->attendanceType)->code === 'present' || ($a->attendance_status ?? '') === 'present')->count();
        $lateRecords = isset($stats['late']) ? (int) $stats['late'] : $recordItems->filter(fn($a) => ($a->is_late ?? $a->late_mark ?? false))->count();
        $blockedRecords = isset($stats['blocked']) ? (int) $stats['blocked'] : $recordItems->filter(fn($a) => ($a->is_blocked ?? $a->is_punch_blocked ?? false))->count();
        $missedRecords = isset($stats['missed_punch']) ? (int) $stats['missed_punch'] : $recordItems->filter(fn($a) => ($a->missed_punch ?? $a->is_missed_punch ?? false) || ($a->attendance_status ?? '') === 'missed_punch')->count();
        $halfDayRecords = isset($stats['half_day']) ? (int) $stats['half_day'] : $recordItems->filter(fn($a) => ($a->is_half_day ?? false) || ($a->attendance_status ?? '') === 'half_day')->count();
        $wfoRecords = isset($stats['wfo']) ? (int) $stats['wfo'] : $recordItems->filter(fn($a) => strtolower($a->work_mode ?? '') === 'wfo')->count();
        $wfhRecords = isset($stats['wfh']) ? (int) $stats['wfh'] : $recordItems->filter(fn($a) => strtolower($a->work_mode ?? '') === 'wfh')->count();
        @endphp

        <div class="att-hero">
            <div>
                <div class="att-kicker">
                    <i class="fas fa-calendar-check"></i>
                    {{ $isMyAttendance ? 'EMPLOYEE • ATTENDANCE' : 'HRMS • ATTENDANCE' }}
                </div>
                <h3 class="att-title">{{ $isMyAttendance ? 'My Attendance' : 'Attendance Records' }}</h3>
                <div class="att-subtitle">
                    {{ $isMyAttendance 
                        ? 'Track your daily punches, working hours, late marks, missed punches, and monthly attendance summary.'
                        : 'Overall employee attendance records with filters, shift timing, work duration, flags and export options.' }}
                </div>
            </div>
            @if(!$isMyAttendance)
            <div class="att-hero-actions">
                {{-- <a href="{{ route('attendances.index') }}" class="att-btn att-btn-glass">
                    <i class="fas fa-chart-line"></i> Attendance Dashboard
                </a> --}}
            </div>
            @endif
        </div>

        <div class="att-metric-grid">
            <div class="att-metric" style="--metric-color:#12B76A;--metric-soft:#E8F8EF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-list"></i></div>
                    <div class="att-metric-value">{{ $totalRecords }}</div>
                </div>
                <div class="att-metric-label">Total Records</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#16A34A;--metric-soft:#DCFCE7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="att-metric-value">{{ $presentRecords }}</div>
                </div>
                <div class="att-metric-label">Present</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#F97316;--metric-soft:#FFF7ED;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="att-metric-value">{{ $lateRecords }}</div>
                </div>
                <div class="att-metric-label">Late</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#D97706;--metric-soft:#FEF3C7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="att-metric-value">{{ $missedRecords }}</div>
                </div>
                <div class="att-metric-label">Missed Punch</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#F59E0B;--metric-soft:#FEF3C7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-business-time"></i></div>
                    <div class="att-metric-value">{{ $halfDayRecords }}</div>
                </div>
                <div class="att-metric-label">Half Day</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-building"></i></div>
                    <div class="att-metric-value">{{ $wfoRecords }}</div>
                </div>
                <div class="att-metric-label">WFO</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#0E7490;--metric-soft:#ECFEFF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-home"></i></div>
                    <div class="att-metric-value">{{ $wfhRecords }}</div>
                </div>
                <div class="att-metric-label">WFH</div>
                <div class="att-metric-line"></div>
            </div>
        </div>

        @if(session('status'))
        <div class="alert alert-success" style="border-radius:16px;font-weight:800;">{{ session('status') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger" style="border-radius:16px;font-weight:800;">{{ session('error') }}</div>
        @endif

        <div class="att-card">
            <div class="att-section-head">
                @if($isMyAttendance)
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:50%; background:#F4F2FF; color:var(--orb-primary); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                        <i class="fas fa-table"></i>
                    </div>
                    <div>
                        <h5 class="att-section-title" style="margin:0; font-size:18px;">My Attendance History</h5>
                        <div class="att-section-sub" style="margin-top:4px;">Review your attendance logs, punch timings, working hours, and status.</div>
                    </div>
                </div>
                @else
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:50%; background:#F4F2FF; color:var(--orb-primary); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div>
                        <h5 class="att-section-title" style="margin:0; font-size:18px;">Employee Attendance Records</h5>
                        <div class="att-section-sub" style="margin-top:4px;">Filter by employee to inspect their attendance history, timings, and status.</div>
                    </div>
                </div>
                @endif
            </div>

            <div class="att-filter-panel">
                <form method="GET" action="{{ $isMyAttendance ? route('hrms.attendance.my') : route('attendances.record') }}" id="dailyAttendanceFilterForm">
                    <div class="att-filter-grid">

                        @if(!$isMyAttendance)
                        <div class="att-filter-group">
                            <label><i class="fas fa-user text-primary mr-1"></i> Employee</label>
                            <select name="employee_id" class="form-control select2-searchable" id="employeeSelect">
                                <option value="all" {{ ((string)($selectedEmployeeId ?? request('employee_id')) === 'all') ? 'selected' : '' }}> All Employees</option>
                                @foreach($employees as $emp)
                                @php 
                                    $empId = optional($emp->employee)->id ?? $emp->id; 
                                    $empName = $emp->name ?? optional($emp->user)->name ?? 'Employee';
                                    $empCode = optional($emp->employee)->employee_code ?? $emp->employee_code ?? '';
                                    $deptName = optional(optional($emp->employee)->department)->name ?? optional($emp->department)->name ?? '';
                                    $isAuth = ($empId == ($currentEmployeeId ?? null));
                                @endphp
                                <option value="{{ $empId }}" {{ ((string)($selectedEmployeeId ?? request('employee_id')) === (string)$empId) ? 'selected' : '' }}>
                                    {{ $empName }}{{ $empCode ? " ({$empCode})" : '' }}{{ $isAuth ? ' • [You]' : '' }}{{ $deptName ? " • {$deptName}" : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

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
                            <x-form.date-picker name="date" id="record_date" :value="request('date')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-group custom-date-filter {{ $isCustomRange ? '' : 'd-none' }}">
                            <label>From Date</label>
                            <x-form.date-picker name="from_date" id="record_from_date" :value="request('from_date')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-group custom-date-filter {{ $isCustomRange ? '' : 'd-none' }}">
                            <label>To Date</label>
                            <x-form.date-picker name="to_date" id="record_to_date" :value="request('to_date')" placeholder="dd-mm-yyyy" class="form-control" />
                        </div>

                        <div class="att-filter-group">
                            <label>Status</label>
                            <select name="attendance_type_id" class="form-control select2-searchable">
                                <option value="">All Status</option>
                                @foreach($attendanceTypes as $type)
                                <option value="{{ $type->id }}" {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Shift</label>
                            <select name="attendance_time_id" class="form-control select2-searchable">
                                <option value="">All Shifts</option>
                                @foreach($attendanceTimes ?? [] as $shift)
                                <option value="{{ $shift->id }}" {{ request('attendance_time_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Work Mode</label>
                            <select name="work_mode" class="form-control select2-searchable">
                                <option value="">All</option>
                                <option value="wfo" {{ request('work_mode') == 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ request('work_mode') == 'wfh' ? 'selected' : '' }}>WFH</option>
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Flags</label>
                            <select name="flag" class="form-control select2-searchable">
                                <option value="">All Records</option>
                                <option value="late" {{ request('flag') == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="early_out" {{ request('flag') == 'early_out' ? 'selected' : '' }}>Early Logout</option>
                                <option value="blocked" {{ request('flag') == 'blocked' ? 'selected' : '' }}>Punch Blocked</option>
                                <option value="missed_punch" {{ request('flag') == 'missed_punch' ? 'selected' : '' }}>Missed Punch</option>
                                <option value="clear" {{ request('flag') == 'clear' ? 'selected' : '' }}>Clear</option>
                            </select>
                        </div>

                        <div class="att-filter-group d-flex align-items-end" style="gap: 8px;">
                            <button type="submit" class="btn text-white font-weight-bold shadow-sm" style="height:43px; border-radius:14px; background:linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border:none; flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px;">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ $isMyAttendance ? route('hrms.attendance.my') : route('attendances.record') }}" class="btn btn-light" style="height:43px; width:43px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; font-weight:750; border:1px solid #E4E7EC; background:#fff; color:#344054; flex-shrink:0;" title="Reset Filters">
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
                                    <option value="{{ $size }}" {{ (int) request('per_page', 50) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                            <span>entries</span>
                        </label>
                    </div>
                </div>
                <div class="orb-table-export-buttons eo-toolbar-right">
                    @if(!$isMyAttendance)
                    @php
                        $exportParams = request()->query();
                        if (!isset($exportParams['month_year']) && !isset($exportParams['date']) && !isset($exportParams['from_date']) && !isset($exportParams['month'])) {
                            if (!empty($selectedMonthYear)) {
                                $exportParams['month_year'] = $selectedMonthYear;
                            }
                        }
                        if (!isset($exportParams['employee_id']) && !empty($selectedEmployeeId)) {
                            $exportParams['employee_id'] = $selectedEmployeeId;
                        }
                    @endphp
                    <x-ui.export-buttons 
                        :csvUrl="route('attendances.export-excel', $exportParams)" 
                        :excelUrl="route('attendances.export-excel', $exportParams)" 
                        :pdfUrl="route('attendances.export-pdf', $exportParams)" 
                        :printUrl="route('attendances.print', $exportParams)" 
                    />
                    @endif
                </div>
            </div>

            <div class="att-table-wrap">
                <table class="table att-table" id="dailyAttendanceDataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            @if(!$isMyAttendance)
                            <th>Employee</th>
                            @endif
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Target Out</th>
                            <th>Gross</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Flags</th>
                            <th>Work Report</th>
                            <th class="no-export text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($attendances as $attendance)
                        @php
                        $rawStatus = strtolower($attendance->attendance_status ?? '');
                        if (empty($rawStatus)) {
                            $rawStatus = optional($attendance->attendanceType)->code ?? 'default';
                        }
                        if (($attendance->is_admin_unlocked || $attendance->unlocked_at || $attendance->unlock_type) && ($rawStatus === 'punch_blocked' || empty($rawStatus))) {
                            $rawStatus = $attendance->punch_in_time ? 'present' : 'unlocked';
                        }
                        if ($rawStatus === 'absent') {
                            $typeCode = 'absent';
                            $statusName = 'ABSENT';
                        } elseif ($rawStatus === 'lwp') {
                            $typeCode = 'lwp';
                            $statusName = 'LWP';
                        } else {
                            $statusMap = [
                                'present'           => ['present', 'PRESENT'],
                                'half_day'          => ['half_day', 'HALF DAY'],
                                'absent'            => ['absent', 'ABSENT'],
                                'lwp'               => ['lwp', 'LWP'],
                                'missed_punch'      => ['missed_punch', 'MISSED PUNCH'],
                                'leave'             => ['leave', 'LEAVE'],
                                'holiday'           => ['holiday', 'HOLIDAY'],
                                'week_off'          => ['week_off', 'WEEK OFF'],
                                'punch_blocked'     => ['punch_blocked', 'PUNCH BLOCKED'],
                                'unlocked'          => ['unlocked', 'UNLOCKED'],
                                'awaiting_punch_in' => ['unlocked', 'UNLOCKED'],
                                
                            ];
                            $mapped = $statusMap[$rawStatus] ?? null;
                            if ($mapped) {
                                $typeCode = $mapped[0];
                                $statusName = $mapped[1];
                            } else {
                                $typeCode = optional($attendance->attendanceType)->code ?? ($rawStatus ?: 'default');
                                $statusName = strtoupper(optional($attendance->attendanceType)->name ?? ($attendance->attendance_status ?? 'N/A'));
                            }
                        }

                        $modeCode = strtolower($attendance->work_mode ?? '');
                        $modeLabel = $modeCode === 'wfh' ? 'WFH' : ($modeCode === 'wfo' ? 'WFO' : '-');
                        $modeClass = in_array($modeCode, ['wfo','wfh']) ? 'mode-'.$modeCode : 'mode-default';

                        $employeeName = optional($attendance->user)->name
                        ?? optional(optional($attendance->employee)->user)->name
                        ?? 'Employee';

                        $employeeCode = optional($attendance->employee)->employee_code ?? 'N/A';

                        $attDate = $attendance->attendance_date
                        ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y')
                        : '-';

                        $punchIn = $attendance->punch_in_time ?? $attendance->punch_in ?? null;
                        $punchOut = $attendance->punch_out_time ?? $attendance->punch_out ?? null;

                        $gross = $attendance->gross_duration
                        ?? (isset($attendance->gross_work_minutes) ? floor($attendance->gross_work_minutes / 60).'h '.($attendance->gross_work_minutes % 60).'m' : '-');

                        $net = $attendance->net_duration
                        ?? (isset($attendance->working_minutes) ? floor($attendance->working_minutes / 60).'h '.($attendance->working_minutes % 60).'m' : '-');

                        $workSummary = optional($attendance->workLogs->first())->work_summary
                        ?? $attendance->punch_out_note
                        ?? '-';

                        $isBlocked = $attendance->is_blocked ?? $attendance->is_punch_blocked ?? false;
                        $isLate = $attendance->is_late ?? $attendance->late_mark ?? false;
                        $isEarly = $attendance->is_early_out ?? $attendance->early_leave_mark ?? false;
                        $isMissed = (bool) ($attendance->missed_punch ?? $attendance->is_missed_punch ?? false);
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            @if(!$isMyAttendance)
                            <td>
                                <div class="att-emp">
                                    @php
                                        $passportPhotoUrl = resolveEmployeePassportPhoto($attendance->employee ?? $attendance);
                                        $employeeInitial = resolveEmployeeInitials($attendance->employee ?? $attendance);
                                    @endphp
                                    <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                        @if($passportPhotoUrl)
                                            <img
                                                src="{{ $passportPhotoUrl }}"
                                                alt="{{ $employeeName }}"
                                                class="hrms-emp-avatar-img"
                                                onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');"
                                            >
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
                                        <div class="att-emp-name" title="{{ $employeeName }}">
                                            {{ $employeeName }}
                                        </div>
                                        <div class="att-emp-code" title="{{ $employeeCode }}">
                                            {{ $employeeCode }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif

                            <td><strong>{{ $attDate }}</strong></td>

                            <td>
                                <span class="mode-badge {{ $modeClass }}">
                                    {{ $modeLabel }}
                                </span>
                            </td>

                            <td>{{ optional($attendance->attendanceTime)->name ?? '-' }}</td>

                            <td>{{ $punchIn ? \Carbon\Carbon::parse($punchIn)->format('h:i A') : '-' }}</td>

                            <td>{{ $punchOut ? \Carbon\Carbon::parse($punchOut)->format('h:i A') : '-' }}</td>

                            <td>
                                {{ $attendance->target_punch_out_time
                                        ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A')
                                        : '-' }}
                            </td>

                            <td>{{ $gross }}</td>

                            <td><strong>{{ $net }}</strong></td>

                             <td>
                                <span class="att-badge badge-{{ $typeCode }}">
                                    {{ $statusName }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $reasonText = match ($typeCode) {
                                        'lwp' => $attendance->lwp_reason ?: ($attendance->status_reason ?: ($attendance->remarks ?: ($attendance->half_day_reason ?: null))),
                                        'half_day' => $attendance->half_day_reason ?: ($attendance->status_reason ?: ($attendance->remarks ?: ($attendance->lwp_reason ?: null))),
                                        'absent' => $attendance->status_reason ?: ($attendance->remarks ?: ($attendance->lwp_reason ?: null)),
                                        'punch_blocked' => $attendance->blocked_reason ?: ($attendance->block_reason ?: ($attendance->status_reason ?: ($attendance->remarks ?: null))),
                                        'missed_punch' => $attendance->missed_punch_reason ?: ($attendance->lwp_reason ?: ($attendance->status_reason ?: ($attendance->remarks ?: null))),
                                        'unlocked', 'awaiting_punch_in' => $attendance->unlock_remarks ?: ($attendance->approval_remarks ?: ($attendance->status_reason ?: null)),
                                        default => $attendance->half_day_reason 
                                            ?: ($attendance->lwp_reason 
                                            ?: ($attendance->missed_punch_reason
                                            ?: ($attendance->status_reason 
                                            ?: ($attendance->remarks 
                                            ?: ($attendance->blocked_reason
                                            ?: ($attendance->block_reason
                                            ?: ($attendance->unlock_remarks 
                                            ?: ($attendance->approval_remarks ?: null)))))))),
                                    };
                                @endphp
                                @if(!empty($reasonText))
                                    <div style="min-width: 180px; max-width: 340px; font-size: 12px; color: #344054; font-weight: 500; line-height: 1.45; white-space: normal; word-break: break-word;" title="{{ $reasonText }}">
                                        {{ $reasonText }}
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">-</span>
                                @endif
                            </td>

                            <td>
                                @if($isLate)
                                <span class="flag flag-late">Late</span>
                                @endif

                                @if($isEarly)
                                <span class="flag flag-early">Early</span>
                                @endif

                                @if($isBlocked)
                                <span class="flag flag-blocked">Blocked</span>
                                @endif

                                @if($isMissed)
                                <span class="flag flag-missed">Missed</span>
                                @endif

                                @if(!$isLate && !$isEarly && !$isBlocked && !$isMissed)
                                <span class="flag flag-clear">Clear</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $firstLog = $attendance->workLogs->first();
                                    $logPayload = null;
                                    $repTitle = 'Work Report Submitted';
                                    $repDesc = null;
                                    $repStatus = 'Completed';
                                    $projectsList = [];
                                    $requirementsList = [];
                                    $testStatus = ['tested' => false, 'completed' => false];
                                    $issues = [];
                                    $notes = null;

                                    if ($firstLog) {
                                        $tasks = $firstLog->work_summary_json;
                                        if (is_string($tasks)) {
                                            $tasks = json_decode($tasks, true);
                                        }

                                        if (is_array($tasks)) {
                                            if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                                                $projectsList = $tasks['projects'];
                                                foreach ($projectsList as $p) {
                                                    $pName = $p['project_name'] ?? $p['name'] ?? 'Project';
                                                    if (!empty($pName) && $repTitle === 'Work Report Submitted') {
                                                        $repTitle = $pName;
                                                    }
                                                    if (isset($p['tasks']) && is_array($p['tasks'])) {
                                                        foreach ($p['tasks'] as $t) {
                                                            $tName = $t['task_name'] ?? $t['description'] ?? $t['task'] ?? $t['title'] ?? 'Task';
                                                            $tDone = (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true || $t['is_completed'] === 'true') : (isset($t['completed']) ? ($t['completed'] == 1 || $t['completed'] === true || $t['completed'] === 'true') : true));
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

                                            if (isset($tasks['today_work_status']) && !empty($tasks['today_work_status'])) {
                                                $repStatus = ucfirst($tasks['today_work_status']);
                                            } elseif (isset($tasks['current_status']) && !empty($tasks['current_status'])) {
                                                $repStatus = ucfirst($tasks['current_status']);
                                            } elseif (isset($tasks['status']) && !empty($tasks['status'])) {
                                                $repStatus = ucfirst($tasks['status']);
                                            }

                                            if ($repTitle === 'Work Report Submitted' && !empty($tasks['task_name'])) {
                                                $repTitle = $tasks['task_name'];
                                            } elseif ($repTitle === 'Work Report Submitted' && !empty($tasks['title'])) {
                                                $repTitle = $tasks['title'];
                                            }

                                            $rawDesc = $tasks['description'] ?? ($tasks['today_work_description'] ?? null);
                                            if ($rawDesc && !str_contains($rawDesc, '☑') && !str_contains($rawDesc, '☐')) {
                                                $repDesc = $rawDesc;
                                            } else {
                                                if ($repStatus) {
                                                    $repDesc = "Today's Work Status: " . ucfirst($repStatus);
                                                } else {
                                                    $repDesc = "Work report submitted with project tasks.";
                                                }
                                            }

                                            if (isset($tasks['test_status']) && is_array($tasks['test_status'])) {
                                                $testStatus = [
                                                    'tested' => $tasks['test_status']['tested'] ?? false,
                                                    'completed' => $tasks['test_status']['completed'] ?? false,
                                                ];
                                            } else {
                                                $stLower = strtolower($repStatus);
                                                $isTested = in_array($stLower, ['testing', 'done', 'completed', 'tested', 'yes'], true);
                                                $isCompleted = in_array($stLower, ['done', 'completed', 'yes'], true);
                                                $testStatus = [
                                                    'tested' => $isTested,
                                                    'completed' => $isCompleted,
                                                ];
                                            }

                                            $rawIssues = $tasks['issues_blockers'] ?? ($tasks['issues'] ?? []);
                                            if (is_array($rawIssues)) {
                                                $issues = $rawIssues;
                                            } elseif (is_string($rawIssues) && trim($rawIssues) !== '' && strtolower(trim($rawIssues)) !== 'no issues' && strtolower(trim($rawIssues)) !== 'none') {
                                                $issues = [$rawIssues];
                                            }

                                            $notes = $tasks['additional_notes'] ?? ($tasks['remarks'] ?? ($tasks['notes'] ?? null));
                                        } else {
                                            $repDesc = $firstLog->work_summary ?? 'No summary provided.';
                                        }

                                        $logPayload = [
                                            'id' => $firstLog->id,
                                            'work_log_id' => $firstLog->id,
                                            'employee_name' => $employeeName,
                                            'employee_code' => $employeeCode,
                                            'passport_photo_url' => resolveEmployeePassportPhoto($attendance->employee ?? $attendance),
                                            'employee_initial' => resolveEmployeeInitials($attendance->employee ?? $attendance),
                                            'department' => optional(optional($attendance->employee)->department)->name ?? 'Staff',
                                            'designation' => optional(optional($attendance->employee)->designation)->name ?? 'Member',
                                            'work_date' => $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-',
                                            'shift_name' => optional($attendance->attendanceTime)->name ?? 'Default Shift',
                                            'attendance_status' => ($attendance->attendance_status ?? 'present') === 'absent' && ($attendance->is_lwp ?? false) ? '🔴 ABSENT' : ($attendance->attendance_status ?? 'present'),
                                            'is_lwp' => (bool) ($attendance->is_lwp ?? false),
                                            'title' => $repTitle,
                                            'description' => $repDesc,
                                            'status' => $repStatus,
                                            'work_mode' => strtoupper($attendance->work_mode ?? 'WFO'),
                                            'submitted_time' => $firstLog->created_at ? $firstLog->created_at->format('h:i A') : '-',
                                            'projects' => $projectsList,
                                            'requirements' => $requirementsList,
                                            'test_status' => $testStatus,
                                            'issues' => $issues,
                                            'notes' => $notes,
                                        ];
                                    }
                                @endphp
                                @if($firstLog && $logPayload)
                                    @php
                                        $taskCount = is_array($requirementsList) ? count($requirementsList) : 0;
                                        $tasksLabel = $taskCount . ' ' . \Illuminate\Support\Str::plural('Task', $taskCount);
                                        $stLower = strtolower($repStatus);
                                        $statusClass = ($stLower === 'completed' || $stLower === 'done') ? 'badge-present' : ($stLower === 'testing' ? 'badge-info' : 'badge-half_day');
                                    @endphp
                                    <div role="button" class="d-flex flex-column gap-1" style="max-width: 200px; cursor: pointer;" 
                                         data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
                                         onclick="parseAndOpenWorkReport(this)"
                                         title="Click to view work report">
                                        <div style="font-size: 12px; font-weight: 700; color: #1D2939; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $repTitle }}">
                                            {{ $repTitle }}
                                        </div>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="badge-premium-pill badge-wfo" style="font-size: 9px; padding: 3px 8px; font-weight: 800; border-radius: 6px;">
                                                <i class="fas fa-list-check" style="font-size: 8px;"></i> {{ $tasksLabel }}
                                            </span>
                                            <span class="badge-premium-pill {{ $statusClass }}" style="font-size: 9px; padding: 3px 8px; font-weight: 800; border-radius: 6px; text-transform: uppercase;">
                                                {{ $repStatus }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 12px; font-style: italic;">No Report</span>
                                @endif
                            </td>

                            <td>
                                <div class="att-action-wrap dropdown">
                                    <button type="button" class="action-dot" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                        <button type="button"
                                            class="dropdown-item"
                                            data-toggle="modal"
                                            data-target="#viewModal{{ $attendance->id }}">
                                            <i class="fas fa-eye text-info"></i>
                                            <span>View Details</span>
                                        </button>

                                        @if($firstLog && $logPayload)
                                            <button type="button"
                                                class="dropdown-item"
                                                data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
                                                onclick="parseAndOpenWorkReport(this)">
                                                <i class="fas fa-clipboard-list text-primary"></i>
                                                <span>View Work Report</span>
                                            </button>
                                        @else
                                            <button type="button" class="dropdown-item disabled" disabled>
                                                <i class="fas fa-clipboard-list text-muted"></i>
                                                <span>No Work Report</span>
                                            </button>
                                        @endif

                                        @if(!$isMyAttendance)
                                            {{-- Direct unlock disabled: Unlock must be done via Regularization Request Approval --}}

                                            @if(($canManageAttendance ?? false) || (auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin')))
                                            <button type="button"
                                                class="dropdown-item"
                                                data-toggle="modal"
                                                data-target="#editModal{{ $attendance->id }}">
                                                <i class="fas fa-edit text-warning"></i>
                                                <span>Edit Attendance</span>
                                            </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($attendances instanceof \Illuminate\Pagination\AbstractPaginator && $attendances->hasPages())
            <div class="border-top bg-white" style="border-bottom-left-radius:18px; border-bottom-right-radius:18px;">
                {{ $attendances->appends(request()->query())->links('vendor.pagination.orbo') }}
            </div>
            @endif
        </div>

        @foreach($attendances as $attendance)
            @include('hrms.attendance.partials.view-modal', ['attendance' => $attendance])

            @if(!$isMyAttendance)
                @if(($canManageAttendance ?? false) || (auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin')))
                    @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
                @endif

                @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
            @endif
        @endforeach

    </div>
</div>

<!-- Shared Premium Modal -->
@include('hrms.attendance.partials.work-report-modal')
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
        const form = document.getElementById('dailyAttendanceFilterForm');
        const filters = document.querySelectorAll('.auto-filter');
        const searchInput = document.querySelector('.auto-filter-input');
        let typingTimer = null;

        const dateInput = document.querySelector('input[name="date"]');
        const fromDateInput = document.querySelector('input[name="from_date"]');
        const toDateInput = document.querySelector('input[name="to_date"]');
        const monthYearSelect = document.getElementById('monthYearSelect');
        const customDateFilters = document.querySelectorAll('.custom-date-filter');

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
                // Month filter does not auto-submit; user submits via Search button
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

        if ($.fn.DataTable.isDataTable('#dailyAttendanceDataTable')) {
            $('#dailyAttendanceDataTable').DataTable().destroy();
        }

        const currentPerPage = "{{ request('per_page', '50') }}";

        const dtTable = $('#dailyAttendanceDataTable').DataTable({
            destroy: true,
            pageLength: currentPerPage === 'all' || currentPerPage === '-1' ? -1 : parseInt(currentPerPage) || 50,
            lengthMenu: [
                [10, 25, 50, 100, 250, 500, -1],
                [10, 25, 50, 100, 250, 500, 'All']
            ],
            ordering: true,
            order: [],
            responsive: false,
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            info: false,
            searching: false,
            dom: 'rt',
            language: {
                emptyTable: 'No attendance records found.'
            }
        });

        $('#recordsPerPageSelect').on('change', function() {
            let val = $(this).val();
            let url = new URL(window.location.href);
            if (val === '-1') val = 'all';
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page'); 
            window.location.href = url.toString();
        });

        $('.dataTables_length select').off('change').on('change', function() {
            let val = $(this).val();
            let url = new URL(window.location.href);
            if (val === '-1') val = 'all';
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page'); 
            window.location.href = url.toString();
        });

        // Dynamically sync form filters with export buttons on click
        $('.orb-table-export-buttons a').on('click', function(e) {
            var href = $(this).attr('href');
            if (!href || href === '#' || href.startsWith('javascript:')) return;
            
            var url = new URL(href, window.location.origin);
            var form = $('#dailyAttendanceFilterForm');
            if (form.length) {
                var formData = form.serializeArray();
                formData.forEach(function(item) {
                    if (item.value && item.value !== 'all' && item.value !== '') {
                        url.searchParams.set(item.name, item.value);
                    }
                });
                $(this).attr('href', url.toString());
            }
        });

        setTimeout(function() {
            $('#dailyAttendanceDataTable').DataTable().columns.adjust();
        }, 250);
    });
</script>
@endsection