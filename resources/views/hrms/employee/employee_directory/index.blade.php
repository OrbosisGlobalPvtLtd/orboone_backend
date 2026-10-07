@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Employee Directory')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@include('hrms.employee.partials.styles')
@endsection

@section('_content')
<style>
    /* 0. Full Width Fluid Fit for All Zoom & Screen Ratios */
    .eo-page,
    .eo-container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }

    .orb-page-header,
    .eo-stat-grid,
    .eo-card,
    .orb-table-wrap,
    #employeesTable {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 1. Metric card grid & card overrides */
    .eo-stat-grid {
        display: grid !important;
        grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
        gap: 12px !important;
        margin-bottom: 24px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .eo-stat {
        background: #fff !important;
        border-radius: 18px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        padding: 12px 16px !important;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045) !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        min-height: 72px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        min-width: 0 !important;
    }

    .eo-stat-icon {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        flex-shrink: 0 !important;
    }

    .eo-stat-value {
        font-size: 18px !important;
        font-weight: 900 !important;
        color: var(--orb-text, #101828) !important;
        margin: 0 !important;
        letter-spacing: -.5px !important;
    }

    .eo-stat-label {
        font-size: 10px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--orb-muted, #667085) !important;
        margin: 0 0 2px 0 !important;
        letter-spacing: .5px !important;
    }

    /* Media query responsiveness for Metric Card Grid */
    @media (max-width: 1199px) {
        .eo-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 768px) {
        .eo-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 575px) {
        .eo-stat-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* 2. Filter Action Buttons Styling */
    .eo-filter-actions-col {
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-end !important;
        min-width: 0 !important;
    }

    .eo-filter-actions-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        width: 100% !important;
    }

    .eo-filter-grid {
        display: grid !important;
        grid-template-columns: 1.5fr 1.2fr 1fr 1fr 1fr auto !important;
        gap: 12px !important;
        align-items: flex-end !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    @media (max-width: 1199px) {
        .eo-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 991px) {
        .eo-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
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
        min-width: 0 !important;
    }

    .eo-field label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--orb-muted, #667085) !important;
        margin: 0 !important;
        letter-spacing: .4px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
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

    /* Select2 compatibility within filter fields */
    .eo-field .select2-container {
        width: 100% !important;
    }
    .eo-field .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #DDE3EE !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        background: #fff !important;
        padding: 0 8px !important;
        transition: all .2s ease !important;
    }
    .eo-field .select2-container--default.select2-container--focus .select2-selection--single,
    .eo-field .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--orb-secondary, #8600EE) !important;
        box-shadow: 0 0 0 4px rgba(134, 0, 238, .08) !important;
    }
    .eo-field .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: var(--orb-text, #101828) !important;
        padding-left: 4px !important;
    }
    .eo-field .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
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

    .eo-action-menu {
        position: relative !important;
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

    .eo-action-menu .dropdown-menu,
    .eo-action-floating-menu {
        background: #ffffff !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        box-shadow: 0 20px 45px rgba(16, 24, 40, .16), 0 4px 12px rgba(16, 24, 40, .05) !important;
        border-radius: 16px !important;
        padding: 8px !important;
        min-width: 195px !important;
        z-index: 99999 !important;
    }

    .eo-action-menu.dropup .dropdown-menu {
        top: auto !important;
        bottom: 100% !important;
        margin-bottom: 6px !important;
        transform: none !important;
    }

    .eo-action-menu .dropdown-item,
    .eo-action-floating-menu .dropdown-item {
        border-radius: 10px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        padding: 8px 12px !important;
        color: var(--orb-text, #101828) !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        transition: all 0.15s ease !important;
    }

    .eo-action-menu .dropdown-item:hover,
    .eo-action-floating-menu .dropdown-item:hover {
        background: #F4F2FF !important;
        color: var(--orb-primary, #6366F1) !important;
    }

    .eo-action-menu .dropdown-item i,
    .eo-action-floating-menu .dropdown-item i {
        width: 20px !important;
        font-size: 14px !important;
        color: var(--orb-primary, #6366F1) !important;
        text-align: center !important;
        flex-shrink: 0 !important;
        display: inline-block !important;
    }

    .eo-action-menu .dropdown-item.text-warning i,
    .eo-action-floating-menu .dropdown-item.text-warning i {
        color: #F59E0B !important;
    }

    .eo-action-menu .dropdown-item.text-danger i,
    .eo-action-floating-menu .dropdown-item.text-danger i {
        color: #EF4444 !important;
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

            <div class="eo-stat" style="border-bottom: 3px solid #8B5CF6 !important;">
                <div class="eo-stat-icon" style="background: rgba(139, 92, 246, 0.12); color: #8B5CF6;"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <p class="eo-stat-label">Internship</p>
                    <h3 class="eo-stat-value">{{ $stats['internship'] ?? 0 }}</h3>
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
            </div>

            <!-- 3. Filters: attached under table header -->
            <div class="orb-table-tools border-bottom">
                <div class="eo-filter-grid">
                    <div class="eo-field">
                        <label>Search</label>
                        <input type="text" id="filterSearch" class="eo-control" style="height: 38px !important;" placeholder="Search name, code, email, phone...">
                    </div>

                    <x-form.select
                        id="filterDepartment"
                        name="department"
                        label="Department"
                        :options="$departments ?? []"
                        placeholder="All Departments"
                        :searchable="true"
                        wrapper-class="eo-field mb-0"
                        class="eo-control"
                    />

                    <x-form.select
                        id="filterWorkMode"
                        name="work_mode"
                        label="Work Mode"
                        :options="[
                            'wfo' => 'WFO',
                            'wfh' => 'WFH',
                            'hybrid' => 'Hybrid'
                        ]"
                        placeholder="All Mode"
                        :searchable="true"
                        wrapper-class="eo-field mb-0"
                        class="eo-control"
                    />

                    <x-form.select
                        id="filterEmploymentType"
                        name="employment_type"
                        label="Employment Type"
                        :options="[
                            'full_time' => 'Full Time',
                            'intern' => 'Intern',
                            'contract' => 'Contract',
                            'part_time' => 'Part Time'
                        ]"
                        placeholder="All Type"
                        :searchable="true"
                        wrapper-class="eo-field mb-0"
                        class="eo-control"
                    />

                    <x-form.select
                        id="filterStatus"
                        name="status"
                        label="Status"
                        :options="[
                            'active' => 'Active',
                            'probation' => 'Probation',
                            'internship' => 'Internship',
                            'notice' => 'Notice',
                            'inactive' => 'Inactive'
                        ]"
                        placeholder="All Status"
                        :searchable="true"
                        wrapper-class="eo-field mb-0"
                        class="eo-control"
                    />

                    <div class="eo-field eo-filter-actions-col">
                        <label class="d-none d-sm-block">&nbsp;</label>
                        <div class="eo-filter-actions-wrap">
                            <x-ui.button
                                type="button"
                                id="btnEmpFilterSubmit"
                                variant="search"
                                icon="fas fa-search mr-1"
                                title="Search / Apply Filter"
                                class="orbo-button-flex"
                            >
                                Search
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                id="resetFilter"
                                variant="reset"
                                icon="fas fa-undo mr-1"
                                title="Reset Filters"
                                class="orbo-button-flex"
                            >
                                Reset
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. DataTable toolbar: clean responsive row, length LEFT, export RIGHT -->
            <div class="orb-table-tools-bar">
                <div id="employeeLengthBox"></div>
                <div id="employeeExportButtons"></div>
            </div>

            <!-- 6. Table wrapper -->
            <div class="orb-table-wrap table-responsive">
                <table id="employeesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th width="45" class="text-center">S.No</th>
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

@include('hrms.employee.partials.initiate_exit_modal')
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
    $(document).ready(function() {

        function formatExportColumn(data, row, column, node, targetType) {
            if (!data) return '-';
            let $el = $('<div>').html(data);

            // Strip action dropdowns, avatars, dots, scripts, styles
            $el.find('.hrms-emp-avatar, .hrms-emp-avatar-fallback, .eo-action-menu, .eo-dot, script, style').remove();

            // Column 0: S.No
            if (column === 0) {
                return (row + 1).toString();
            }

            // Column 1: Employee Name, Code, Email
            if (column === 1) {
                let name = $el.find('.eo-name').text().trim();
                let code = $el.find('.eo-meta').text().trim();
                let email = $el.find('.eo-mini').text().trim();

                if (name) {
                    if (targetType === 'print') {
                        let html = '<div style="font-weight:700; color:#0F172A; font-size:12px; line-height:1.3;">' + name + '</div>';
                        if (code && code !== '-') {
                            html += '<div style="color:#475569; font-size:11px; font-weight:500; margin-top:2px;">' + code + '</div>';
                        }
                        if (email && email !== '-') {
                            html += '<div style="color:#64748B; font-size:10px; margin-top:1px; word-break:break-all;">' + email + '</div>';
                        }
                        return html;
                    } else if (targetType === 'pdf') {
                        let lines = [name];
                        if (code && code !== '-') lines.push('(' + code + ')');
                        if (email && email !== '-') lines.push(email);
                        return lines.join('\n');
                    } else {
                        let parts = [name];
                        if (code && code !== '-') parts.push('(' + code + ')');
                        if (email && email !== '-') parts.push('- ' + email);
                        return parts.join(' ');
                    }
                }
            }

            // Column 4: Type & Mode (handle multiple pills)
            if (column === 4) {
                let pills = [];
                $el.find('.eo-pill').each(function() {
                    let t = $(this).text().trim();
                    if (t) pills.push(t);
                });
                if (pills.length > 0) {
                    return targetType === 'pdf' ? pills.join('\n') : pills.join(' / ');
                }
            }

            // Column 5: Manager Name & Code
            if (column === 5) {
                let managerCode = $el.find('.eo-mini').text().trim();
                $el.find('.eo-mini').remove();
                let managerName = $el.text().replace(/\s+/g, ' ').trim();
                if (!managerName) managerName = 'Not assigned';

                if (managerName !== 'Not assigned' && managerCode && managerCode !== '-') {
                    return targetType === 'pdf' ? managerName + '\n(' + managerCode + ')' : managerName + ' (' + managerCode + ')';
                }
                return managerName;
            }

            if (targetType === 'print') {
                return $el.html().trim() || $el.text().replace(/\s+/g, ' ').trim() || '-';
            }

            return $el.text().replace(/\s+/g, ' ').trim() || '-';
        }

        function cleanExportText(data) {
            return formatExportColumn(data, null, null, null, 'text');
        }

        function buildTabularExportData(data) {
            data.header = [
                'S.No',
                'Employee Code',
                'Employee Name',
                'Email',
                'Department',
                'Designation',
                'Employment Type',
                'Work Mode',
                'Reporting Manager',
                'Manager Code',
                'Shift',
                'Verification Status',
                'Stage',
                'Joining Date',
                'Status'
            ];

            let api = $('#employeesTable').DataTable();
            let rowsData = api.rows({ search: 'applied' }).data().toArray();
            let startIdx = (api.page.info && api.page.info().start) ? api.page.info().start : 0;

            let newBody = [];
            for (let i = 0; i < rowsData.length; i++) {
                let row = rowsData[i];
                let sNo = (startIdx + i + 1).toString();

                let empCode = row.raw_employee_code || '';
                let empName = row.raw_name || '';
                let empEmail = row.raw_email || '';

                if (!empName) {
                    let $emp = $('<div>').html(row.employee || '');
                    empName = $emp.find('.eo-name').text().trim() || '-';
                    empCode = $emp.find('.eo-meta').text().trim() || '-';
                    empEmail = $emp.find('.eo-mini').text().trim() || '-';
                }

                let dept = row.raw_department || $('<div>').html(row.department || '-').text().trim();
                let desig = row.raw_designation || $('<div>').html(row.designation || '-').text().trim();
                let empType = row.raw_employment_type || $('<div>').html(row.employment_type || '-').text().trim();
                let workMode = row.raw_work_mode || $('<div>').html(row.work_mode || '-').text().trim();

                let mgrName = row.raw_manager_name || '';
                let mgrCode = row.raw_manager_code || '';
                if (!mgrName) {
                    let $mgr = $('<div>').html(row.reporting_manager || '');
                    mgrCode = $mgr.find('.eo-mini').text().trim() || '-';
                    $mgr.find('.eo-mini').remove();
                    mgrName = $mgr.text().replace(/\s+/g, ' ').trim() || 'Not assigned';
                }

                let shift = row.raw_shift || $('<div>').html(row.shift || '-').text().trim();
                let verif = row.raw_verification_status || $('<div>').html(row.verification_status || '-').text().trim();
                let stage = row.raw_stage || $('<div>').html(row.employee_stage || '-').text().trim();
                let joining = row.raw_joining_date || $('<div>').html(row.joining_date || '-').text().trim();
                let status = row.raw_status || $('<div>').html(row.status || '-').text().trim();

                newBody.push([
                    sNo,
                    empCode,
                    empName,
                    empEmail,
                    dept,
                    desig,
                    empType,
                    workMode,
                    mgrName,
                    mgrCode,
                    shift,
                    verif,
                    stage,
                    joining,
                    status
                ]);
            }

            data.body = newBody;
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
            pageLength: 50,
            lengthMenu: [
                [50, 10, 25, 100],
                [50, 10, 25, 100]
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
            columns: [
                {
                    data: null,
                    name: 's_no',
                    orderable: false,
                    searchable: false,
                    className: 'text-center font-weight-bold text-muted',
                    width: '45px',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
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
                [1, 'asc']
            ],
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    title: 'Employee Directory',
                    className: 'btn btn-sm btn-export-csv',
                    customizeData: function(data) {
                        buildTabularExportData(data);
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    title: 'Employee Directory',
                    className: 'btn btn-sm btn-export-excel',
                    customizeData: function(data) {
                        buildTabularExportData(data);
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    title: 'Employee Directory',
                    className: 'btn btn-sm btn-export-pdf',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                            body: function(data, row, column, node) {
                                return formatExportColumn(data, row, column, node, 'pdf');
                            }
                        }
                    },
                    customize: function(doc) {
                        doc.pageOrientation = 'landscape';
                        doc.pageSize = 'A4';
                        doc.pageMargins = [14, 18, 14, 18];

                        // Set clean header title
                        doc.content[0] = {
                            text: 'EMPLOYEE DIRECTORY REPORT',
                            fontSize: 13,
                            bold: true,
                            alignment: 'center',
                            color: '#0F172A',
                            margin: [0, 0, 0, 10]
                        };

                        doc.defaultStyle.fontSize = 7;
                        doc.styles.tableHeader = {
                            fontSize: 7.5,
                            bold: true,
                            color: '#0F172A',
                            fillColor: '#F1F5F9',
                            alignment: 'left'
                        };

                        // All 11 columns proportional widths (Sum = 100%)
                        doc.content[1].table.widths = ['3.5%', '16.5%', '11%', '11%', '8%', '11%', '8%', '8%', '7%', '8%', '8%'];

                        let body = doc.content[1].table.body;
                        for (let i = 0; i < body.length; i++) {
                            let row = body[i];
                            let isHeader = (i === 0);
                            for (let j = 0; j < row.length; j++) {
                                let cell = row[j];
                                if (isHeader) {
                                    cell.fillColor = '#F1F5F9';
                                    cell.color = '#0F172A';
                                    cell.bold = true;
                                    cell.fontSize = 7.5;
                                    if (j === 0 || j === 4 || j === 7 || j === 8 || j === 9 || j === 10) {
                                        cell.alignment = 'center';
                                    }
                                } else {
                                    if (i % 2 === 0) {
                                        cell.fillColor = '#F8FAFC';
                                    }
                                    cell.fontSize = 6.8;
                                    if (j === 0 || j === 4 || j === 7 || j === 8 || j === 9 || j === 10) {
                                        cell.alignment = 'center';
                                    }
                                }
                            }
                        }

                        doc.content[1].layout = {
                            hLineWidth: function() { return 0.5; },
                            vLineWidth: function() { return 0.5; },
                            hLineColor: function() { return '#E2E8F0'; },
                            vLineColor: function() { return '#E2E8F0'; },
                            paddingLeft: function() { return 4; },
                            paddingRight: function() { return 4; },
                            paddingTop: function() { return 3.5; },
                            paddingBottom: function() { return 3.5; }
                        };
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    title: 'Employee Directory Report',
                    className: 'btn btn-sm btn-export-print',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                            body: function(data, row, column, node) {
                                return formatExportColumn(data, row, column, node, 'print');
                            }
                        }
                    },
                    customize: function (win) {
                        $(win.document.body)
                            .css('font-family', "'Inter', system-ui, -apple-system, sans-serif")
                            .css('padding', '24px')
                            .css('background', '#fff')
                            .css('color', '#0F172A');

                        $(win.document.body).find('h1')
                            .css('text-align', 'center')
                            .css('font-size', '20px')
                            .css('font-weight', '800')
                            .css('color', '#0F172A')
                            .css('letter-spacing', '0.5px')
                            .css('text-transform', 'uppercase')
                            .css('margin-bottom', '20px')
                            .css('padding-bottom', '12px')
                            .css('border-bottom', '2px solid #E2E8F0');

                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', '11px')
                            .css('width', '100%')
                            .css('border-collapse', 'collapse')
                            .css('margin-top', '10px');

                        $(win.document.body).find('table th')
                            .css('background-color', '#F8FAFC')
                            .css('color', '#334155')
                            .css('font-weight', '700')
                            .css('padding', '10px 12px')
                            .css('border', '1px solid #CBD5E1')
                            .css('text-transform', 'uppercase')
                            .css('font-size', '10px')
                            .css('letter-spacing', '0.5px');

                        $(win.document.body).find('table td')
                            .css('padding', '8px 12px')
                            .css('border', '1px solid #E2E8F0')
                            .css('color', '#1E293B')
                            .css('vertical-align', 'top');

                        $(win.document.body).find('table tbody tr:nth-child(even)').css('background-color', '#F8FAFC');
                    }
                }
            ],
            language: {
                processing: '<strong>Loading employees...</strong>',
                emptyTable: 'No approved active employees found',
                zeroRecords: 'No matching employee found'
            },
            drawCallback: function(settings) {
                $('.dataTables_info').appendTo('#employeeInfoBox');
                $('.dataTables_paginate').appendTo('#employeePaginationBox');
            },
            initComplete: function() {
                $('.dataTables_length').appendTo('#employeeLengthBox');
                $('.dt-buttons').appendTo('#employeeExportButtons');
                $('.dataTables_info').appendTo('#employeeInfoBox');
                $('.dataTables_paginate').appendTo('#employeePaginationBox');
            }
        });

        $('#btnEmpFilterSubmit').on('click', function(e) {
            e.preventDefault();
            let value = $('#filterSearch').val();
            table.search(value);
            table.page('first').draw('page');
        });

        $('#filterSearch').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#btnEmpFilterSubmit').click();
            }
        });

        $('#resetFilter').on('click', function() {
            $('#filterSearch').val('');
            $('#filterDepartment').val('').trigger('change');
            $('#filterWorkMode').val('').trigger('change');
            $('#filterEmploymentType').val('').trigger('change');
            $('#filterStatus').val('').trigger('change');

            table.search('');
            table.page('first').draw('page');
        });

        // Production-grade floating action dropdown (immune to table-responsive overflow clipping)
        $(document).on('show.bs.dropdown', '.eo-action-menu', function() {
            const $parent = $(this);
            const $toggle = $parent.find('.dropdown-toggle');
            const $menu = $parent.find('.dropdown-menu');
            if ($toggle.length === 0 || $menu.length === 0) return;

            $menu.addClass('eo-action-floating-menu');
            $menu.data('parent-cell', $parent);
            $('body').append($menu.detach());

            function positionDropdown() {
                const rect = $toggle[0].getBoundingClientRect();
                const menuWidth = $menu.outerWidth() || 195;
                const menuHeight = $menu.outerHeight() || 220;
                const windowHeight = $(window).height();
                const windowWidth = $(window).width();

                let top = rect.bottom + 4;
                let left = rect.right - menuWidth;

                if (left < 10) left = 10;
                if (left + menuWidth > windowWidth - 10) left = windowWidth - menuWidth - 10;

                if (top + menuHeight > windowHeight - 10) {
                    top = Math.max(10, rect.top - menuHeight - 4);
                }

                $menu.css({
                    position: 'fixed',
                    top: top + 'px',
                    left: left + 'px',
                    margin: '0',
                    zIndex: 99999,
                    display: 'block'
                });
            }

            positionDropdown();
        });

        $(document).on('hide.bs.dropdown', '.eo-action-menu', function() {
            const $parent = $(this);
            $('body > .dropdown-menu.eo-action-floating-menu').each(function() {
                const $menu = $(this);
                if ($menu.data('parent-cell') && $menu.data('parent-cell')[0] === $parent[0]) {
                    $menu.removeClass('eo-action-floating-menu');
                    $menu.css({ display: '', position: '', top: '', left: '', zIndex: '', margin: '' });
                    $parent.append($menu.detach());
                }
            });
        });

        // Close open floating dropdown on page or table scroll
        $(window).add('.orb-table-wrap').on('scroll', function() {
            $('.eo-action-menu.show .dropdown-toggle').dropdown('hide');
        });

        // Open Initiate Exit Modal dynamically
        $(document).on('click', '.btn-open-initiate-exit-modal', function(e) {
            e.preventDefault();
            const btn = $(this);
            const employeeName = btn.data('employee-name');
            const employeeCode = btn.data('employee-code');
            const employeeStage = String(btn.data('employee-stage') || '').toLowerCase();
            const employmentType = String(btn.data('employment-type') || '').toLowerCase();
            const actionUrl = btn.data('action-url');

            const modal = $('#initiateExitModal');
            const form = $('#initiateExitGlobalForm');

            form.attr('action', actionUrl);
            $('#initiateExitModalLabel').html('<i class="fas fa-sign-out-alt mr-2"></i> Initiate Exit: ' + employeeName + ' (' + employeeCode + ')');
            $('#initiateExitModalSub').text('Select exit type and parameters for ' + employeeName);

            const exitTypeSelect = form.find('.eo-exit-type');
            if (employeeStage === 'internship' || employmentType === 'intern' || employmentType === 'internship') {
                exitTypeSelect.val('internship_completed').trigger('change');
            } else {
                exitTypeSelect.val('resignation').trigger('change');
            }

            modal.modal('show');
        });
    });
</script>
@endsection