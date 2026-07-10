@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Employee Directory')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@include('hrms.employee.partials.styles')
@endsection

@section('_content')
<style>
    /* 1. Metric card grid & card overrides */
    .eo-stat-grid {
        display: grid !important;
        grid-template-columns: repeat(5, minmax(170px, 1fr)) !important;
        gap: 12px !important;
        margin-bottom: 24px !important;
    }

    .eo-stat {
        background: #fff !important;
        border-radius: 18px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        padding: 12px 16px !important; /* Reduced padding slightly */
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045) !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important; /* Reduced gap slightly */
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        min-height: 72px !important; /* Reduced height slightly */
    }

    .eo-stat-icon {
        width: 38px !important; /* Reduced icon size slightly */
        height: 38px !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        flex-shrink: 0 !important;
    }

    .eo-stat-value {
        font-size: 18px !important; /* Reduced slightly */
        font-weight: 900 !important;
        color: var(--orb-text, #101828) !important;
        margin: 0 !important;
        letter-spacing: -.5px !important;
    }

    .eo-stat-label {
        font-size: 10px !important; /* Reduced slightly */
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--orb-muted, #667085) !important;
        margin: 0 0 2px 0 !important;
        letter-spacing: .5px !important;
    }

    /* Media query responsiveness for Metric Card Grid */
    @media (max-width: 1400px) {
        .eo-stat-grid {
            grid-template-columns: repeat(4, minmax(180px, 1fr)) !important;
        }
    }

    @media (max-width: 991px) {
        .eo-stat-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 575px) {
        .eo-stat-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* 2. Reset Button Custom Styling */
    #resetFilter {
        background: #fff !important;
        border: 1px solid #E7EAF3 !important;
        color: var(--orb-primary) !important;
        box-shadow: 0 8px 18px rgba(16, 24, 40, .06) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        height: 38px !important;
        padding: 0 16px !important;
        border-radius: 12px !important;
        transition: all .2s ease !important;
        cursor: pointer !important;
    }

    #resetFilter:hover {
        background: #F4F2FF !important; /* Hover stays soft purple/gray */
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
        transform: translateY(-1px) !important;
    }

    .eo-filter-grid {
        display: grid !important;
        grid-template-columns: repeat(5, 1fr) !important;
        gap: 12px !important;
    }

    @media (max-width: 1200px) {
        .eo-filter-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    @media (max-width: 768px) {
        .eo-filter-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 575px) {
        .eo-filter-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .eo-field {
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
    }

    .eo-field label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--orb-muted, #667085) !important;
        margin: 0 !important;
        letter-spacing: .4px !important;
    }

    .eo-control {
        height: 38px !important;
        border: 1px solid #DDE3EE !important;
        border-radius: 12px !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: var(--orb-text, #101828) !important;
        background: #fff !important;
        outline: none !important;
        transition: all .2s !important;
        width: 100% !important;
    }

    .eo-control:focus {
        border-color: var(--orb-secondary, #8600EE) !important;
        box-shadow: 0 0 0 4px rgba(134, 0, 238, .08) !important;
    }

    select.eo-control {
        cursor: pointer !important;
        padding-right: 28px !important;
        appearance: none !important;
        background: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%23667085' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat right 12px center #fff !important;
    }

    .orb-table-card {
        background: #fff !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        border-radius: 22px !important;
        box-shadow: var(--orb-shadow, 0 10px 28px rgba(16, 24, 40, .06)) !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }

    .orb-table-head {
        padding: 20px 24px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-bottom: 1px solid var(--orb-border, #E7EAF3) !important;
        background: #fff !important;
    }

    .orb-table-title-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 15px !important;
    }

    .orb-table-icon {
        width: 42px !important;
        height: 42px !important;
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        flex-shrink: 0 !important;
    }

    .orb-table-title-wrap h3 {
        margin: 0 !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        color: var(--orb-text, #101828) !important;
    }

    .orb-table-title-wrap p {
        margin: 4px 0 0 0 !important;
        font-size: 13px !important;
        color: var(--orb-muted, #667085) !important;
        font-weight: 500 !important;
    }

    .orb-table-tools {
        padding: 16px 24px !important;
        background: #fff !important;
        border-bottom: 1px solid var(--orb-border, #E7EAF3) !important;
    }

    .orb-table-wrap {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        overflow-x: auto !important;
    }

    #employeesTable {
        width: 100% !important;
        margin: 0 !important;
    }

    #employeesTable thead th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .45px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--orb-border);
        white-space: nowrap;
    }

    #employeesTable tbody td {
        padding: 13px 14px;
        border-bottom: 1px solid #F1F3F8;
        vertical-align: middle;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 650;
    }

    #employeesTable tbody tr:hover {
        background: #FCFAFF;
    }

    .eo-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 240px;
    }

    .eo-avatar {
        width: 42px;
        height: 42px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--orb-primary);
        font-size: 14px;
        font-weight: 950;
        background: #F4F2FF;
        border: 1px solid #EEE7FF;
        overflow: hidden;
        flex: 0 0 auto;
    }

    .eo-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .eo-name {
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 950;
        line-height: 1.2;
    }

    .eo-meta {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 750;
        margin-top: 3px;
    }

    .eo-mini {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 700;
        margin-top: 2px;
    }

    .eo-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 950;
        white-space: nowrap;
        text-transform: uppercase;
    }

    .eo-dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
    }

    .eo-pill-active {
        color: #12B76A;
        background: rgba(18, 183, 106, .10);
    }

    .eo-pill-pending {
        color: #F79009;
        background: rgba(247, 144, 9, .12);
    }

    .eo-pill-danger {
        color: #EC4E74;
        background: rgba(236, 78, 116, .10);
    }

    .eo-pill-default {
        color: #667085;
        background: #F2F4F7;
    }

    .eo-pill-wfh {
        color: #06AED4;
        background: rgba(6, 174, 212, .10);
    }

    .eo-pill-wfo {
        color: var(--orb-primary);
        background: rgba(75, 0, 232, .08);
    }

    .eo-pill-hybrid {
        color: #D400D5;
        background: rgba(212, 0, 213, .08);
    }

    .eo-pill-blue {
        color: #2563EB;
        background: rgba(37, 99, 235, .10);
    }

    .eo-actions-cell {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .eo-action-menu .dropdown-toggle {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 12px;
        background: #F4F2FF;
        color: var(--orb-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .eo-action-menu .dropdown-toggle::after {
        display: none !important;
    }

    .eo-action-menu .dropdown-menu {
        border: 1px solid var(--orb-border);
        box-shadow: 0 18px 40px rgba(16, 24, 40, .12);
        border-radius: 14px;
        padding: 8px;
    }

    .eo-action-menu .dropdown-item {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        padding: 8px 10px;
    }

    .eo-action-menu .dropdown-item i {
        width: 18px;
        color: var(--orb-primary);
    }

    .dataTables_filter {
        display: none;
    }

    .dataTables_length label,
    .dataTables_info {
        margin: 0 !important;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap !important;
    }

    #employeeLengthBox .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px;
    }

    #employeeLengthBox .dataTables_length select {
        width: auto !important;
        min-width: 68px;
        height: 34px;
        margin: 0 4px !important;
        border-radius: 10px;
        border: 1px solid var(--orb-border);
        padding: 4px 8px;
    }

    .dt-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .dt-buttons .btn {
        border-radius: 11px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        font-size: 12px !important;
        font-weight: 950 !important;
        padding: 8px 12px !important;
        box-shadow: 0 6px 16px rgba(16, 24, 40, .045) !important;
    .dt-buttons .btn:hover {
        color: #fff !important;
        border-color: var(--orb-primary) !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important    }

    #employeesTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    #employeesTable thead th {
        background: #F8FAFC !important;
        color: #475467 !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: .5px !important;
        border-bottom: 1px solid var(--orb-border, #E7EAF3) !important;
        padding: 14px 18px !important;
        border-top: 0 !important;
        white-space: nowrap !important;
    }

    #employeesTable tbody td {
        padding: 14px 18px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: var(--orb-text, #101828) !important;
        border-bottom: 1px solid #F2F4F7 !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    #employeesTable tbody tr:hover td {
        background: #FDFDFF !important;
    }

    .eo-table-footer {
        padding: 16px 24px !important;
        border-top: 1px solid var(--orb-border, #E7EAF3) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        flex-wrap: wrap !important;
        background: #fff !important;
    }

    #employeeLengthBox select {
        height: 34px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        border-radius: 8px !important;
        padding: 2px 8px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        outline: none !important;
    }

    #employeeLengthBox {
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--orb-muted, #667085) !important;
    }

    #employeeExportButtons {
        display: flex !important;
        gap: 6px !important;
    }

    #employeeExportButtons .btn {
        height: 34px !important;
        padding: 0 12px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        background: #fff !important;
        color: var(--orb-text, #101828) !important;
        border-radius: 8px !important;
        transition: all .2s !important;
    }

    #employeeExportButtons .btn:hover {
        background: var(--orb-soft, #F4F2FF) !important;
        color: var(--orb-primary, #4B00E8) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
    }

    #employeeInfoBox {
        font-size: 13px !important;
        font-weight: 750 !important;
        color: var(--orb-muted, #667085) !important;
    }

    .pagination {
        margin: 0 !important;
        gap: 4px !important;
    }

    .page-item .page-link {
        border-radius: 8px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        color: var(--orb-text, #101828) !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        padding: 6px 12px !important;
        min-width: 32px !important;
        text-align: center !important;
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #8600EE)) !important;
        border-color: transparent !important;
        color: #fff !important;
    }

    .page-item:not(.active) .page-link:hover {
        background: var(--orb-soft, #F4F2FF) !important;
        color: var(--orb-primary, #4B00E8) !important;
        border-color: rgba(75, 0, 232, .2) !important;
    }

    .emp-profile-cell {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        min-width: 240px !important;
    }

    .emp-avatar {
        width: 40px !important;
        height: 40px !important;
        border-radius: 12px !important;
        background: var(--orb-soft, #F4F2FF) !important;
        color: var(--orb-primary, #4B00E8) !important;
        font-size: 15px !important;
        font-weight: 900 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        overflow: hidden !important;
    }

    .emp-avatar img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    .emp-info {
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
    }

    .emp-name-link {
        font-size: 14px !important;
        font-weight: 900 !important;
        color: var(--orb-text, #101828) !important;
        text-decoration: none !important;
        transition: color .2s !important;
    }

    .emp-name-link:hover {
        color: var(--orb-primary, #4B00E8) !important;
    }

    .emp-code {
        font-size: 11px !important;
        font-weight: 750 !important;
        color: var(--orb-muted, #667085) !important;
    }

    .emp-sub-info {
        font-size: 11px !important;
        font-weight: 600 !important;
        color: var(--orb-muted, #667085) !important;
        margin-top: 1px !important;
    }

    .eo-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        border-radius: 999px !important;
        padding: 4px 10px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        white-space: nowrap !important;
    }

    .eo-dot {
        width: 6px !important;
        height: 6px !important;
        border-radius: 50% !important;
        display: inline-block !important;
    }

    .eo-pill-default { background: #F2F4F7 !important; color: #344054 !important; }
    .eo-pill-default .eo-dot { background: #475467 !important; }

    .eo-pill-active { background: #ECFDF5 !important; color: #027A48 !important; }
    .eo-pill-active .eo-dot { background: #12B76A !important; }

    .eo-pill-pending { background: #FFFAEB !important; color: #B54708 !important; }
    .eo-pill-pending .eo-dot { background: #F79009 !important; }

    .eo-pill-danger { background: #FEF2F2 !important; color: #B42318 !important; }
    .eo-pill-danger .eo-dot { background: #F04438 !important; }

    .eo-pill-wfh { background: #EFF8FF !important; color: #175CD3 !important; }
    .eo-pill-wfh .eo-dot { background: #2E90FA !important; }

    .eo-pill-wfo { background: #FDF2FA !important; color: #C11574 !important; }
    .eo-pill-wfo .eo-dot { background: #EE46BC !important; }

    .eo-pill-hybrid { background: #F4F3FF !important; color: #5925DC !important; }
    .eo-pill-hybrid .eo-dot { background: #84ADFF !important; }

    .eo-pill-blue { background: #F0F9FF !important; color: #026AA2 !important; }
    .eo-pill-blue .eo-dot { background: #06AED4 !important; }

    .action-btn-group {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .btn-act {
        width: 32px !important;
        height: 32px !important;
        border-radius: 9px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #475467 !important;
        background: #F8FAFC !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        font-size: 13px !important;
        transition: all .2s !important;
        text-decoration: none !important;
    }

    .btn-act:hover {
        background: var(--orb-soft, #F4F2FF) !important;
        color: var(--orb-primary, #4B00E8) !important;
        border-color: rgba(75, 0, 232, .2) !important;
        transform: translateY(-1px) !important;
    }

    .btn-act.danger:hover {
        background: #FEF2F2 !important;
        color: #DC2626 !important;
        border-color: rgba(220, 38, 38, .2) !important;
    }
</style>

<div class="eo-page">
    <div class="eo-container">

        <div class="orb-page-header">
            <div class="orb-page-header-content">
                <div class="orb-page-kicker">
                    <i class="fas fa-users"></i> HRMS &bull; Employee
                </div>

                <h1 class="orb-page-title">
                    Employee Directory
                </h1>

                <p class="orb-page-subtitle">
                    Active approved employees, verification status, work mode and HR lifecycle in one premium view.
                </p>
            </div>

            <div class="orb-page-actions">
                @if (Route::has('hrms.employees.pending_profiles'))
                <a href="{{ route('hrms.employees.pending_profiles') }}" class="orb-btn-light">
                    <i class="fas fa-user-clock"></i>
                    Pending Profiles
                </a>
                @endif

                @if (Route::has('hrms.employees.create'))
                <a href="{{ route('hrms.employees.create') }}" class="orb-btn-light">
                    <i class="fas fa-plus-circle"></i>
                    Add Employee
                </a>
                @endif
            </div>
        </div>

        @php
        $stats = $stats ?? [];
        @endphp

        <div class="eo-stat-grid">
            <div class="eo-stat border-bottom-primary">
                <div class="eo-stat-icon primary"><i class="fas fa-users"></i></div>
                <div>
                    <p class="eo-stat-label">Total Employees</p>
                    <h3 class="eo-stat-value">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="eo-stat border-bottom-success">
                <div class="eo-stat-icon success"><i class="fas fa-user-check"></i></div>
                <div>
                    <p class="eo-stat-label">Active</p>
                    <h3 class="eo-stat-value">{{ $stats['active'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="eo-stat border-bottom-warning">
                <div class="eo-stat-icon warning"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <p class="eo-stat-label">Probation</p>
                    <h3 class="eo-stat-value">{{ $stats['probation'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="eo-stat border-bottom-info">
                <div class="eo-stat-icon info"><i class="fas fa-laptop-house"></i></div>
                <div>
                    <p class="eo-stat-label">WFH / Hybrid</p>
                    <h3 class="eo-stat-value">{{ $stats['remote'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="eo-stat border-bottom-danger">
                <div class="eo-stat-icon danger"><i class="fas fa-file-signature"></i></div>
                <div>
                    <p class="eo-stat-label">Docs Pending</p>
                    <h3 class="eo-stat-value">{{ $stats['docs_pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        <div class="orb-table-card">
            <!-- 2. Fix table card structure: Table Card Header -->
            <div class="orb-table-head d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- LEFT: circular icon, title, subtitle -->
                <div class="orb-table-title-wrap">
                    <span class="orb-table-icon"><i class="fas fa-users-cog"></i></span>
                    <div>
                        <h3>Employee Directory List</h3>
                        <p>Manage active employees, verification status, work mode, and HR lifecycle.</p>
                    </div>
                </div>
                <!-- RIGHT: Reset button -->
                <div class="d-flex align-items-center">
                    <button type="button" id="resetFilter" class="orb-btn-light py-2 px-3 h-auto">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </div>

            <!-- 3. Filters: attached under table header -->
            <div class="orb-table-tools border-bottom">
                <div class="eo-filter-grid">
                    <div class="eo-field">
                        <label>Search</label>
                        <input type="text" id="filterSearch" class="eo-control" style="height: 38px !important;" placeholder="Search name, code, email, phone...">
                    </div>

                    <div class="eo-field">
                        <label>Department</label>
                        <select id="filterDepartment" class="eo-control" style="height: 38px !important;">
                            <option value="">All Departments</option>
                            @foreach ($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Work Mode</label>
                        <select id="filterWorkMode" class="eo-control" style="height: 38px !important;">
                            <option value="">All Mode</option>
                            <option value="wfo">WFO</option>
                            <option value="wfh">WFH</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Employment Type</label>
                        <select id="filterEmploymentType" class="eo-control" style="height: 38px !important;">
                            <option value="">All Type</option>
                            <option value="full_time">Full Time</option>
                            <option value="intern">Intern</option>
                            <option value="contract">Contract</option>
                            <option value="part_time">Part Time</option>
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Status</label>
                        <select id="filterStatus" class="eo-control" style="height: 38px !important;">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="probation">Probation</option>
                            <option value="internship">Internship</option>
                            <option value="notice">Notice</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 4. DataTable toolbar: single clean row, length LEFT, export RIGHT -->
            <div class="orb-table-tools py-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div id="employeeLengthBox" class="d-flex align-items-center"></div>
                <div id="employeeExportButtons" class="d-flex align-items-center gap-1"></div>
            </div>

            <!-- 6. Table wrapper -->
            <div class="orb-table-wrap table-responsive">
                <table id="employeesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Type & Mode</th>
                            <th>Manager</th>
                            <th>Shift</th>
                            <th>Verification</th>
                            <th>Stage</th>
                            <th>Joining</th>
                            <th>Status</th>
                            <th width="90" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Pagination below table -->
            <div class="eo-table-footer">
                <div id="employeeInfoBox"></div>
                <div id="employeePaginationBox"></div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {

        function cleanExportText(data) {
            return $('<div>').html(data || '').text().replace(/\s+/g, ' ').trim();
        }

        function pill(text, type) {
            let label = text || '-';
            let cls = 'eo-pill-default';

            type = (type || label || '').toString().toLowerCase();

            if (type.includes('active') || type.includes('approved') || type.includes('verified') || type.includes('present')) {
                cls = 'eo-pill-active';
            } else if (type.includes('pending') || type.includes('probation') || type.includes('submitted')) {
                cls = 'eo-pill-pending';
            } else if (type.includes('reject') || type.includes('inactive') || type.includes('exit') || type.includes('missing')) {
                cls = 'eo-pill-danger';
            } else if (type.includes('wfh')) {
                cls = 'eo-pill-wfh';
            } else if (type.includes('wfo')) {
                cls = 'eo-pill-wfo';
            } else if (type.includes('hybrid')) {
                cls = 'eo-pill-hybrid';
            } else if (type.includes('intern')) {
                cls = 'eo-pill-blue';
            }

            return '<span class="eo-pill ' + cls + '"><span class="eo-dot"></span>' + label + '</span>';
        }

        let table = $('#employeesTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            ajax: {
                url: "{{ route('hrms.employees.index') }}",
                type: "GET",
                data: function(d) {
                    d.ajax_table = 1;
                    d.department = $('#filterDepartment').val();
                    d.work_mode = $('#filterWorkMode').val();
                    d.employment_type = $('#filterEmploymentType').val();
                    d.status = $('#filterStatus').val();
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert('Unable to load employee data. Please check the console for details.');
                }
            },
            columns: [{
                    data: 'employee',
                    name: 'employee',
                    defaultContent: '-'
                },
                {
                    data: 'department',
                    name: 'department',
                    defaultContent: '-'
                },
                {
                    data: 'designation',
                    name: 'designation',
                    defaultContent: '-'
                },
                {
                    data: 'employment_type',
                    name: 'employment_type',
                    defaultContent: '-',
                    render: function(data, type, row) {
                        let typePill = pill(row.employment_type || '-', row.employment_type || '');
                        let modePill = pill(row.work_mode || '-', row.work_mode || '');
                        return '<div class="d-flex flex-column align-items-start gap-1">' + typePill + '<div style="margin-top: 4px;">' + modePill + '</div></div>';
                    }
                },
                {
                    data: 'reporting_manager',
                    name: 'reporting_manager',
                    defaultContent: '-'
                },
                {
                    data: 'shift',
                    name: 'shift',
                    defaultContent: '-'
                },
                {
                    data: 'verification_status',
                    name: 'verification_status',
                    defaultContent: '-',
                    orderable: false,
                    render: function(data) {
                        return pill(data || '-', data || '');
                    }
                },
                {
                    data: 'employee_stage',
                    name: 'employee_stage',
                    defaultContent: '-',
                    render: function(data) {
                        return pill(data || '-', data || '');
                    }
                },
                {
                    data: 'joining_date',
                    name: 'joining_date',
                    defaultContent: '-'
                },
                {
                    data: 'status',
                    name: 'status',
                    defaultContent: '-',
                    render: function(data) {
                        return pill(data || '-', data || '');
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    defaultContent: '-',
                    className: 'text-center'
                }
            ],
            order: [
                [0, 'asc']
            ],
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    title: 'Employee Directory',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    title: 'Employee Directory',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    title: 'Employee Directory',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                }
            ],
            language: {
                processing: '<strong>Loading employees...</strong>',
                emptyTable: 'No approved active employees found',
                zeroRecords: 'No matching employee found'
            },
            initComplete: function() {
                $('.dataTables_length').appendTo('#employeeLengthBox');
                $('.dt-buttons').appendTo('#employeeExportButtons');
                $('.dataTables_info').appendTo('#employeeInfoBox');
                $('.dataTables_paginate').appendTo('#employeePaginationBox');
            }
        });

        let searchTimer = null;

        $('#filterSearch').on('keyup', function() {
            clearTimeout(searchTimer);
            let value = this.value;

            searchTimer = setTimeout(function() {
                table.search(value).draw();
            }, 300);
        });

        $('#filterDepartment, #filterWorkMode, #filterEmploymentType, #filterStatus').on('change', function() {
            table.ajax.reload();
        });

        $('#resetFilter').on('click', function() {
            $('#filterSearch').val('');
            $('#filterDepartment').val('');
            $('#filterWorkMode').val('');
            $('#filterEmploymentType').val('');
            $('#filterStatus').val('');

            table.search('');
            table.ajax.reload();
        });
    });
</script>
@endsection