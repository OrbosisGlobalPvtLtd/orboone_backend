@extends('layouts.panel', ['active' => 'leave_management'])

@section('page_title', 'Leave History & Master Audit Log')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
@include('hrms.leave.shared.style')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#8600EE' }};
    --orb-primary-hover: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-bg: #F8FAFC;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: rgba(75, 0, 232, 0.08);
}

body {
    background: var(--orb-bg) !important;
    overflow-x: hidden !important;
}

.lh-page {
    padding: 24px 20px 48px;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.lh-container {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 auto;
    box-sizing: border-box !important;
}

.lh-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#8600EE' }} 100%);
    border-radius: 20px;
    padding: 24px 30px;
    margin-bottom: 24px;
    color: #fff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.lh-hero h1 {
    font-size: 26px;
    font-weight: 900;
    margin: 0;
    color: #fff;
    letter-spacing: -0.02em;
}

.lh-hero p {
    margin: 6px 0 0;
    font-size: 13.5px;
    opacity: 0.92;
}

/* Table Card Standard (Orbo HRMS Theme) */
.orb-table-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 24px;
    box-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    overflow: hidden;
    margin-bottom: 24px;
}

.orb-table-head {
    padding: 18px 24px;
    border-bottom: 1px solid var(--orb-border);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.orb-table-title-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
}

.orb-table-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: var(--orb-soft);
    color: var(--orb-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.orb-table-title-wrap h3 {
    font-size: 17px;
    font-weight: 800;
    color: var(--orb-text);
    margin: 0;
    letter-spacing: -0.02em;
}

.orb-table-title-wrap p {
    font-size: 12px;
    color: var(--orb-muted);
    margin: 2px 0 0 0;
}

/* Attached Filters Grid (Leave Allocation / Comp-Off Pattern) */
.eo-filter-grid {
    display: grid;
    gap: 12px;
    align-items: flex-end;
    padding: 16px 20px;
    background: #FAFAFB;
    border-bottom: 1px solid var(--orb-border);
}

.eo-filter-grid.cols-admin {
    grid-template-columns: 1fr 1fr 1.2fr 1.1fr 1fr 0.9fr auto;
}

.eo-filter-grid.cols-emp {
    grid-template-columns: 1.2fr 1.2fr 1fr 0.9fr auto;
}

@media (max-width: 1200px) {
    .eo-filter-grid.cols-admin,
    .eo-filter-grid.cols-emp {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .eo-filter-grid.cols-admin,
    .eo-filter-grid.cols-emp {
        grid-template-columns: 1fr;
    }
}

.eo-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.eo-field label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--orb-muted);
    margin: 0;
    white-space: nowrap;
}

.eo-control {
    width: 100%;
    height: 38px !important;
    padding: 0 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--orb-text);
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 10px;
    outline: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.eo-control:focus {
    border-color: var(--orb-primary);
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12);
}

.eo-filter-actions-col {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.eo-filter-actions-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

#btnFilterSubmit {
    height: 38px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 800;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%);
    color: #ffffff;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25);
    transition: all 0.2s ease;
    white-space: nowrap;
}

#btnFilterSubmit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(75, 0, 232, 0.35);
}

#resetFilter {
    height: 38px;
    padding: 0 14px;
    font-size: 12.5px;
    font-weight: 800;
    border-radius: 10px;
    background: #ffffff;
    color: var(--orb-muted);
    border: 1px solid var(--orb-border);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    text-decoration: none;
}

#resetFilter:hover {
    background: #F4F5F7;
    color: var(--orb-text);
    border-color: #D0D5DD;
}

/* Toolbar: Show Entries on Left, Tools on Right */
.orb-table-tools-bar.eo-toolbar {
    padding: 12px 20px !important;
    background: #ffffff !important;
    border-bottom: 1px solid var(--orb-border) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
    box-sizing: border-box !important;
}

.eo-toolbar-left,
.orb-table-length-box {
    display: flex !important;
    align-items: center !important;
}

.eo-entries-label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin: 0 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    color: var(--orb-muted) !important;
    white-space: nowrap !important;
}

/* Select2 Per-Page Dropdown */
.select2-container--per-page.select2-container {
    width: 75px !important;
    min-width: 75px !important;
    margin: 0 4px !important;
    display: inline-block !important;
    vertical-align: middle !important;
}

.select2-container--per-page .select2-selection--single {
    height: 38px !important;
    border-radius: 10px !important;
    border: 1px solid #D0D5DD !important;
    background: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
    padding: 0 20px 0 8px !important;
    transition: all 0.2s ease !important;
}

.select2-container--per-page.select2-container--open .select2-selection--single,
.select2-container--per-page.select2-container--focus .select2-selection--single {
    border-color: var(--orb-primary) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
}

.select2-container--per-page .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    color: var(--orb-text) !important;
    padding: 0 !important;
    text-align: center !important;
    width: 100% !important;
}

.select2-container--per-page .select2-selection__arrow {
    height: 36px !important;
    right: 6px !important;
    width: 18px !important;
}

.select2-dropdown-per-page.select2-dropdown {
    border-radius: 12px !important;
    border: 1px solid #E7EAF3 !important;
    box-shadow: 0 10px 30px rgba(16, 24, 40, 0.15) !important;
    padding: 6px !important;
    min-width: 75px !important;
    z-index: 99999 !important;
    background: #ffffff !important;
}

.select2-dropdown-per-page .select2-results__option {
    padding: 6px 10px !important;
    border-radius: 8px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-align: center !important;
    margin-bottom: 2px !important;
    color: #101828 !important;
    transition: all 0.15s ease !important;
}

.select2-dropdown-per-page .select2-results__option--selected,
.select2-dropdown-per-page .select2-results__option--highlighted {
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
    color: #ffffff !important;
}

/* Tool Buttons / Export Group on Right Side */
.orb-table-export-buttons.eo-toolbar-right {
    display: inline-flex !important;
    align-items: center !important;
}

.orbo-export-group {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
}

.orbo-export-btn {
    height: 36px !important;
    padding: 0 16px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    letter-spacing: -0.2px !important;
    border: 1px solid #E2E8F0 !important;
    background: #FFFFFF !important;
    color: #0F172A !important;
    border-radius: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02) !important;
    transition: all 0.18s ease-in-out !important;
    white-space: nowrap !important;
    text-decoration: none !important;
    cursor: pointer !important;
    user-select: none !important;
}

.orbo-export-btn:hover {
    background: #F8FAFC !important;
    color: #0F172A !important;
    border-color: #CBD5E1 !important;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.07) !important;
    transform: translateY(-1px) !important;
}

.orbo-export-btn:active {
    transform: translateY(0) !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
}

.orbo-export-btn i {
    font-size: 14px !important;
    line-height: 1 !important;
}

.icon-csv { color: #0F172A !important; }
.icon-excel { color: #16A34A !important; }
.icon-pdf { color: #DC2626 !important; }
.icon-print { color: #0F172A !important; }

/* Table Styles */
.lh-table {
    width: 100% !important;
    margin: 0 !important;
}

.lh-table thead th {
    background: #F8FAFC !important;
    color: var(--orb-muted) !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 14px 16px !important;
    border-bottom: 1px solid var(--orb-border) !important;
    white-space: nowrap;
}

.lh-table td {
    padding: 14px 16px !important;
    vertical-align: middle !important;
    font-size: 13.5px;
    border-bottom: 1px solid #F1F5F9;
}

.lh-table tbody tr:hover {
    background-color: #F8FAFC !important;
}

.orb-badge {
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 11.5px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}

.orb-badge-success { background: #ECFDF5; color: #047857; }
.orb-badge-warning { background: #FFFBEB; color: #B45309; }
.orb-badge-danger { background: #FEF2F2; color: #B91C1C; }
.orb-badge-secondary { background: #F1F5F9; color: #475569; }

/* Select2 overrides inside filter bar */
.select2-container .select2-selection--single {
    height: 38px !important;
    border-radius: 10px !important;
    border: 1px solid var(--orb-border) !important;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    color: var(--orb-text) !important;
}
.select2-container .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}

/* Hide default DataTables controls if any are left */
.dataTables_wrapper .dataTables_filter {
    display: none !important;
}

/* 3-Dot Action Button & Dropdown Menu (Matched with Leave Approvals) */
.btn-action-dots {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #F1F5F9;
    color: #475569;
    border: 1px solid #CBD5E1;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
}

.btn-action-dots:hover,
.btn-action-dots:focus {
    background: #EEF2FF;
    color: var(--orb-primary, #4B00E8);
    border-color: #C7D2FE;
    outline: none;
    box-shadow: 0 2px 6px rgba(75, 0, 232, 0.15);
}

.dropdown-menu-action {
    min-width: 190px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
    border: 1px solid #E2E8F0;
    padding: 6px;
    background: #FFFFFF;
    z-index: 1050;
}

.dropdown-menu-action .dropdown-item {
    font-size: 12px;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 8px;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.15s ease;
}

.dropdown-menu-action .dropdown-item:hover {
    background: #EEF2FF;
    color: var(--orb-primary, #4B00E8);
}

.dropdown-menu-action .dropdown-item.text-danger:hover {
    background: #FFF1F2;
    color: #E11D48 !important;
}

.leave-table-responsive {
    overflow-x: auto;
    overflow-y: visible !important;
}

.leave-table-wrap {
    overflow: visible !important;
}

/* Responsive Leave Details Modal Styles (Matched with Leave Approvals) */
.leave-modal-dialog {
    max-width: 800px;
    width: 95%;
    margin: 1.75rem auto;
}

.leave-modal-content {
    border-radius: 16px;
    overflow: hidden;
    max-height: 86vh;
    display: flex;
    flex-direction: column;
    background: #FFFFFF;
}

.leave-modal-header {
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #7C3AED) 100%);
    min-height: 56px;
    height: auto;
    flex-shrink: 0;
    border-radius: 16px 16px 0 0;
}

.leave-modal-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.leave-info-tile {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 58px;
    box-shadow: 0 1px 3px rgba(15,23,42,0.03);
    min-width: 0;
}

.leave-stage-pipeline-box {
    border-radius: 10px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(15,23,42,0.03);
    background: #FFFFFF;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.leave-balance-breakdown-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.leave-balance-tile {
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.leave-stage-arrow {
    font-size: 10px;
    transition: transform 0.2s ease;
}

@media (max-width: 992px) {
    .leave-modal-info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .leave-balance-breakdown-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .leave-modal-dialog {
        width: calc(100% - 16px) !important;
        max-width: 100% !important;
        margin: 8px auto !important;
    }
    .leave-modal-content {
        max-height: 92vh !important;
        border-radius: 14px !important;
    }
    .leave-modal-info-grid {
        grid-template-columns: 1fr !important;
        gap: 8px !important;
    }
    .leave-balance-breakdown-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 6px !important;
    }
    .leave-modal-header, .leave-modal-footer {
        padding: 10px 14px !important;
        min-height: 52px !important;
        height: auto !important;
    }
    .leave-stage-pipeline-box {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
    }
    .leave-stage-items-wrap {
        flex-direction: column !important;
        align-items: stretch !important;
        width: 100% !important;
        gap: 6px !important;
    }
    .leave-stage-arrow {
        transform: rotate(90deg);
        align-self: center;
        margin: 2px 0;
    }
    .leave-modal-footer {
        flex-direction: column !important;
        gap: 8px !important;
    }
}
</style>
@endsection

@section('_content')
<div class="lh-page">
    <div class="lh-container">

        <!-- Hero Header -->
        <div class="lh-hero">
            <div>
                <div style="font-size: 11px; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase; opacity: 0.9; margin-bottom: 6px;">
                    <i class="fas fa-history mr-1"></i> HRMS &bull; AUDIT LOG
                </div>
                <h1>Leave History</h1>
                <p>Master record of all approved, pending, rejected, and cancelled leave applications.</p>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Stats Grid (Image 2 Design) -->
        <div class="leave-metric-grid">
            <div class="leave-metric" style="--metric-color:#4B00E8;--metric-soft:rgba(75, 0, 232, 0.08);">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['total'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Total Records</div>
                <div class="leave-metric-line"></div>
            </div>

            <div class="leave-metric" style="--metric-color:#10B981;--metric-soft:#ECFDF5;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['approved'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Approved</div>
                <div class="leave-metric-line"></div>
            </div>

            <div class="leave-metric" style="--metric-color:#F59E0B;--metric-soft:#FFFBEB;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Pending</div>
                <div class="leave-metric-line"></div>
            </div>

            <div class="leave-metric" style="--metric-color:#EF4444;--metric-soft:#FEF2F2;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['rejected'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Rejected</div>
                <div class="leave-metric-line"></div>
            </div>

            <div class="leave-metric" style="--metric-color:#64748B;--metric-soft:#F1F5F9;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['cancelled'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Cancelled</div>
                <div class="leave-metric-line"></div>
            </div>
        </div>

        <!-- Master Leave History Card (Orbo Standard Layout) -->
        <div class="orb-table-card">

            <!-- 1. Table Card Header with Apply Leave Button -->
            <div class="orb-table-head">
                <div class="orb-table-title-wrap">
                    <div class="orb-table-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h3 class="m-0">Leave Applications History</h3>
                        <p class="m-0 text-muted" style="font-size: 12.5px;">Master record of all approved, pending, rejected, and cancelled leave applications.</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('leave-requests.create') }}" class="leave-btn leave-btn-primary" style="height: 38px; border-radius: 10px; padding: 0 16px; font-weight: 800; font-size: 13px; text-decoration: none; color: #fff;">
                        <i class="fas fa-plus-circle mr-1"></i> Apply Leave
                    </a>
                </div>
            </div>

            <!-- 2. Filter Form operated via Search Button -->
            <form method="GET" action="{{ route('hrms.leave.history') }}" id="filterForm" class="eo-filter-grid {{ !$isEmployeeRole ? 'cols-admin' : 'cols-emp' }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 25) }}">
                @if(!$isEmployeeRole)
                    <div class="eo-field">
                        <label for="filter_employee"><i class="fas fa-user mr-1 text-primary"></i> Employee</label>
                        <select id="filter_employee" name="employee_id" class="eo-control select2-searchable">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ (string)request('employee_id') === (string)$emp->id ? 'selected' : '' }}>
                                    {{ $emp->display_name ?? $emp->user_name ?? $emp->employee_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="eo-field">
                        <label for="filter_department"><i class="fas fa-building mr-1 text-primary"></i> Department</label>
                        <select id="filter_department" name="department_id" class="eo-control select2-searchable">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ (string)request('department_id') === (string)$dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="eo-field">
                    <label for="filter_status"><i class="fas fa-tasks mr-1 text-primary"></i> Status</label>
                    <select id="filter_status" name="status" class="eo-control select2-searchable">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <div class="eo-field">
                    <label for="filter_leave_type"><i class="fas fa-umbrella-beach mr-1 text-primary"></i> Leave Type</label>
                    <select id="filter_leave_type" name="leave_type_id" class="eo-control select2-searchable">
                        <option value="">All Leave Types</option>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}" {{ (string)request('leave_type_id') === (string)$lt->id ? 'selected' : '' }}>
                                {{ $lt->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                

                <div class="eo-field">
                    <label for="filter_month"><i class="far fa-calendar-check mr-1 text-primary"></i> Month</label>
                    <select id="filter_month" name="month" class="eo-control select2-searchable">
                        <option value="all" {{ ($selectedMonth ?? '') === 'all' ? 'selected' : '' }}>All Months</option>
                        @foreach([
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ] as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ (string)($selectedMonth ?? '') === (string)$mNum ? 'selected' : '' }}>
                                {{ $mName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="eo-field">
                    <label for="filter_year"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Year</label>
                    <select id="filter_year" name="year" class="eo-control select2-searchable">
                        @php $selYear = (int) ($year ?? request('year', date('Y'))); @endphp
                        @for($y = 2024; $y <= 2030; $y++)
                            <option value="{{ $y }}" {{ $selYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="eo-filter-actions-col">
                    <div class="eo-filter-actions-wrap">
                        <button type="submit" id="btnFilterSubmit">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                        @if(request()->filled('status') || request()->filled('leave_type_id') || request()->filled('employee_id') || request()->filled('department_id') || (request()->filled('month') && request('month') !== (string)date('n')) || (request()->filled('year') && (int)request('year') !== (int)date('Y')) || request()->filled('search'))
                            <a href="{{ route('hrms.leave.history') }}" id="resetFilter" title="Reset Filters">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- 3. Table Action Toolbar: Show Entries on Left, Export Tools on Right (Search Field Removed) -->
            <div class="orb-table-tools-bar eo-toolbar">
                <div class="orb-table-length-box eo-toolbar-left">
                    <div class="dataTables_length d-flex align-items-center">
                        <label class="eo-entries-label mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size: 13px; gap: 8px;">
                            <span>Show</span>
                            <select id="leaveHistoryPerPageSelect" name="per_page" class="table-per-page-select orb-per-page-select" style="width: 75px;">
                                @foreach([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                            <span>entries</span>
                        </label>
                    </div>
                </div>
                <div class="orb-table-export-buttons eo-toolbar-right">
                    <div class="orbo-export-group">
                        <button type="button" class="orbo-export-btn btn-export-csv" onclick="exportLeaveHistoryTable('csv')" title="Export CSV">
                            <i class="fas fa-file-csv icon-csv"></i>
                            <span>CSV</span>
                        </button>
                        <button type="button" class="orbo-export-btn btn-export-excel" onclick="exportLeaveHistoryTable('excel')" title="Export Excel">
                            <i class="fas fa-file-excel icon-excel"></i>
                            <span>Excel</span>
                        </button>
                        <button type="button" class="orbo-export-btn btn-export-pdf" onclick="exportLeaveHistoryTable('pdf', event)" title="Export PDF">
                            <i class="fas fa-file-pdf icon-pdf"></i>
                            <span>PDF</span>
                        </button>
                        <button type="button" class="orbo-export-btn btn-export-print" onclick="window.print()" title="Print">
                            <i class="fas fa-print icon-print"></i>
                            <span>Print</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. Table Section -->
            <div class="leave-table-wrap p-0">
                <div class="leave-table-responsive" style="border: 0; border-radius: 0;">
                    <table class="lh-table table table-hover m-0" id="leaveHistoryTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="text-center">#</th>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Total Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Submitted At</th>
                                <th class="text-center no-export" style="width: 70px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            @php
                                $empName = optional(optional($req->employee)->user)->name ?? optional($req->employee)->display_name ?? optional($req->employee)->employee_code ?? 'N/A';
                                $deptName = optional(optional($req->employee)->department)->name ?? 'N/A';
                                $photoUrl = resolveEmployeePassportPhoto($req->employee_id);
                                $initials = resolveEmployeeInitials($req->employee_id);
                                
                                $daysVal = (float)($req->requested_days ?? $req->deducted_days ?? 0);
                                if ($daysVal <= 0) {
                                    $daysVal = $req->is_half_day ? 0.5 : ($req->start_date && $req->end_date ? \Carbon\Carbon::parse($req->start_date)->diffInDays(\Carbon\Carbon::parse($req->end_date)) + 1 : 1);
                                }
                                $daysDisplay = ($daysVal == (int)$daysVal) ? (int)$daysVal : number_format($daysVal, 1);

                                $startFmt = $req->start_date ? \Carbon\Carbon::parse($req->start_date)->format('d M Y') : '-';
                                $endFmt = $req->end_date ? \Carbon\Carbon::parse($req->end_date)->format('d M Y') : $startFmt;

                                $statusBadge = $req->status === 'approved'
                                    ? 'orb-badge-success'
                                    : ($req->status === 'pending'
                                        ? 'orb-badge-warning'
                                        : ($req->status === 'rejected'
                                            ? 'orb-badge-danger'
                                            : 'orb-badge-secondary'));
                            @endphp
                            <tr>
                                <td class="text-center"><strong>{{ (($requests->currentPage() - 1) * $requests->perPage()) + $loop->iteration }}</strong></td>
                                <td>
                                    <div>
                                        <div class="font-weight-bold text-dark" style="line-height: 1.25; font-size: 13.5px;">{{ $empName }}</div>
                                        <div class="text-muted small" style="font-size: 11px;">{{ $deptName }}</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-primary">{{ optional($req->leaveType)->name ?? 'Paid Leave' }}</span>
                                </td>
                                <td>
                                    @if($startFmt === $endFmt)
                                        <span class="font-weight-bold">{{ $startFmt }}</span>
                                    @else
                                        <span class="font-weight-bold">{{ $startFmt }}</span>
                                        <span class="text-muted small mx-1">to</span>
                                        <span class="font-weight-bold">{{ $endFmt }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($req->is_half_day || $daysVal == 0.5)
                                        <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11.5px; border-radius: 8px;" title="{{ $req->half_day_type === 'second_half' ? '2nd Half' : ($req->half_day_type === 'first_half' ? '1st Half' : 'Half Day') }}">
                                            {{ $daysDisplay }} Day ({{ $req->half_day_type === 'second_half' ? '2nd Half' : ($req->half_day_type === 'first_half' ? '1st Half' : 'Half Day') }})
                                        </span>
                                    @else
                                        <span class="badge badge-light border font-weight-bold px-2 py-1" style="font-size: 12px; border-radius: 8px;">
                                            {{ $daysDisplay }} {{ $daysVal == 1 ? 'Day' : 'Days' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span title="{{ $req->reason }}" data-toggle="tooltip" style="cursor: help;">
                                        {{ Str::limit($req->reason, 35) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="orb-badge {{ $statusBadge }}">
                                        @if($req->status === 'approved') <i class="fas fa-check-circle"></i>
                                        @elseif($req->status === 'pending') <i class="fas fa-clock"></i>
                                        @elseif($req->status === 'rejected') <i class="fas fa-times-circle"></i>
                                        @else <i class="fas fa-minus-circle"></i>
                                        @endif
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                                <td class="small text-muted font-weight-600">
                                    {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y h:i A') }}
                                </td>
                                <td class="text-center align-middle">
                                    <div class="dropdown">
                                        <button class="btn-action-dots" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-action">
                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#viewModal{{ $req->id }}">
                                                <i class="fas fa-eye text-primary"></i> View Details & Timeline
                                            </a>

                                            @if(strtolower(trim($req->status)) === 'approved' && ($isSuperAdminUser || $isHrAdminUser) && Route::has('leave-approvals.void'))
                                                <a class="dropdown-item text-danger font-weight-bold" href="javascript:void(0)" data-toggle="modal" data-target="#voidModal{{ $req->id }}">
                                                    <i class="fas fa-ban text-danger"></i> Make Null & Void
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open text-muted mb-2" style="font-size: 38px; opacity: 0.5;"></i>
                                        <div class="font-weight-bold text-dark">No leave records found</div>
                                        <div class="small">Try adjusting your filters or date range.</div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 5. Pagination Footer -->
            @if($requests->hasPages())
            <div class="p-3 border-top bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small font-weight-bold">
                    Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} entries
                </div>
                <div>
                    {{ $requests->links() }}
                </div>
            </div>
            @endif

        </div>

        <!-- ==================================================
             VIEW DETAILS MODALS (Same Rich UI as Leave Approvals & Requests)
             ================================================== -->
        @foreach($requests as $lr)
            @php
                $ltName = $lr->leave_type_name ?? ($lr->leaveType?->name ?? 'Leave');
                $ltLower = strtolower($ltName);
                $ltStyle = match(true) {
                    str_contains($ltLower, 'sick') => 'background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5;',
                    str_contains($ltLower, 'casual') => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;',
                    str_contains($ltLower, 'comp') => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
                    str_contains($ltLower, 'earned') || str_contains($ltLower, 'privilege') => 'background: #EFF6FF; color: #1E40AF; border: 1px solid #93C5FD;',
                    default => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;'
                };

                $stLower = strtolower(trim($lr->status ?? 'pending'));
                $startDateFormatted = !empty($lr->start_date) ? \Carbon\Carbon::parse($lr->start_date)->format('d M Y') : '—';
                $endDateFormatted = !empty($lr->end_date) ? \Carbon\Carbon::parse($lr->end_date)->format('d M Y') : '—';
                $isSingleDay = (!empty($lr->start_date) && !empty($lr->end_date) && $lr->start_date === $lr->end_date);

                $daysVal = (float)($lr->requested_days ?? $lr->deducted_days ?? 1);
                $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);

                $managerEmpId = $lr->current_reporting_manager_id ?? ($lr->employee?->reporting_manager_employee_id ?? null);
                $hasManager = !empty($managerEmpId);

                $mgrApproved = !empty($lr->manager_approved_by) || !empty($lr->manager_approved_at) || ($lr->approval_level === 'manager_approved');
                $mgrRejected = ($stLower === 'rejected' && empty($lr->manager_approved_by));
                $hrApproved = ($stLower === 'approved' && (!empty($lr->hr_approved_by) || !empty($lr->hr_approved_at)));
                $hrRejected = ($stLower === 'rejected' && !empty($lr->manager_approved_by));

                $canApprove = false;
                $canReject = false;
                $isAssignedManager = false;
            @endphp

            @include('hrms.leave.approvals.partials.view-modal', compact(
                'lr',
                'ltName',
                'ltStyle',
                'stLower',
                'startDateFormatted',
                'endDateFormatted',
                'isSingleDay',
                'daysText',
                'hasManager',
                'isAssignedManager',
                'isSuperAdminUser',
                'isHrAdminUser',
                'mgrApproved',
                'mgrRejected',
                'hrApproved',
                'hrRejected',
                'canApprove',
                'canReject'
            ))

            @include('hrms.leave.approvals.partials.void-modal', compact(
                'lr',
                'stLower',
                'isSuperAdminUser',
                'isHrAdminUser',
                'isSingleDay',
                'startDateFormatted',
                'endDateFormatted'
            ))
        @endforeach

    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
function updateQueryStringParameter(uri, key, value) {
    const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    const separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        return uri + separator + key + "=" + value;
    }
}

function exportLeaveHistoryTable(format, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
        if (e.stopImmediatePropagation) e.stopImmediatePropagation();
    }

    const table = document.getElementById('leaveHistoryTable');
    if (!table) return;

    const filePrefix = 'leave_history_records_';

    if (format === 'csv' || format === 'excel') {
        let csv = [];
        const rows = table.querySelectorAll('tr');
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('th, td');
            // exclude the last column (Action)
            const colLen = cols.length > 1 ? cols.length - 1 : cols.length;
            for (let j = 0; j < colLen; j++) {
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                text = text.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            if (row.length > 0) {
                csv.push(row.join(','));
            }
        }
        const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
        const link = document.createElement('a');
        link.setAttribute('href', csvContent);
        link.setAttribute('download', filePrefix + new Date().toISOString().slice(0, 10) + '.' + (format === 'excel' ? 'xls' : 'csv'));
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else if (format === 'pdf') {
        const pMake = window.pdfMake || (typeof pdfMake !== 'undefined' ? pdfMake : null);
        if (!pMake) {
            alert('PDF library is loading, please try again in a moment.');
            return;
        }

        if (typeof window.vfsFonts !== 'undefined' && window.vfsFonts.pdfMake) {
            pMake.vfs = window.vfsFonts.pdfMake.vfs;
        } else if (typeof window.pdfMake_vfs !== 'undefined') {
            pMake.vfs = window.pdfMake_vfs;
        }

        const headers = [];
        const thCols = table.querySelectorAll('thead tr th');
        const thLen = thCols.length > 1 ? thCols.length - 1 : thCols.length;
        for (let j = 0; j < thLen; j++) {
            headers.push({
                text: thCols[j].innerText.replace(/\s+/g, ' ').trim(),
                bold: true,
                fillColor: '#4B00E8',
                color: '#FFFFFF',
                fontSize: 8.5
            });
        }

        const bodyRows = [headers];
        const trRows = table.querySelectorAll('tbody tr');

        trRows.forEach(function(tr, rowIndex) {
            const tdCols = tr.querySelectorAll('td');
            if (tdCols.length === 1 && tr.querySelector('.empty-state')) {
                return;
            }
            if (tdCols.length > 0) {
                const rowData = [];
                const tdLen = tdCols.length > 1 ? tdCols.length - 1 : tdCols.length;
                for (let j = 0; j < tdLen; j++) {
                    let cellText = tdCols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                    rowData.push({
                        text: cellText || '-',
                        fontSize: 8,
                        fillColor: rowIndex % 2 === 0 ? '#FFFFFF' : '#F8FAFC',
                        color: '#1E293B'
                    });
                }
                bodyRows.push(rowData);
            }
        });

        const docDefinition = {
            pageOrientation: 'landscape',
            pageSize: 'A4',
            pageMargins: [20, 24, 20, 24],
            content: [
                {
                    text: 'LEAVE APPLICATIONS HISTORY',
                    fontSize: 14,
                    bold: true,
                    alignment: 'center',
                    color: '#4B00E8',
                    margin: [0, 0, 0, 4]
                },
                {
                    text: 'Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString(),
                    fontSize: 8.5,
                    alignment: 'center',
                    color: '#64748B',
                    margin: [0, 0, 0, 14]
                },
                {
                    table: {
                        headerRows: 1,
                        widths: ['5%', '22%', '12%', '18%', '10%', '15%', '10%', '8%'],
                        body: bodyRows
                    },
                    layout: {
                        hLineWidth: function () { return 0.5; },
                        vLineWidth: function () { return 0.5; },
                        hLineColor: function () { return '#E2E8F0'; },
                        vLineColor: function () { return '#E2E8F0'; },
                        paddingLeft: function () { return 6; },
                        paddingRight: function () { return 6; },
                        paddingTop: function () { return 6; },
                        paddingBottom: function () { return 6; }
                    }
                }
            ]
        };

        pMake.createPdf(docDefinition).download(filePrefix + new Date().toISOString().slice(0, 10) + '.pdf');
    }
}

$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    // Select2 on filter dropdowns
    if (window.jQuery && $.fn.select2) {
        $('.select2-searchable').select2({
            width: '100%'
        });

        // Show Entries Per-Page Select2
        $('#leaveHistoryPerPageSelect').select2({
            minimumResultsForSearch: Infinity,
            width: '75px',
            dropdownCssClass: 'select2-dropdown-per-page',
            containerCssClass: 'select2-container--per-page'
        }).on('change', function() {
            const val = $(this).val();
            let url = updateQueryStringParameter(window.location.href, 'per_page', val);
            url = url.replace(/([?&])page=\d+/gi, '').replace(/\?&/, '?').replace(/&&/, '&');
            if (url.endsWith('?') || url.endsWith('&')) {
                url = url.slice(0, -1);
            }
            window.location.href = url;
        });
    }

    // Initialize DataTables without search bar and without duplicate pagination/length
    if (window.jQuery && $.fn.DataTable && $('#leaveHistoryTable').length) {
        $.fn.dataTable.ext.errMode = 'none';
        if ($.fn.DataTable.isDataTable('#leaveHistoryTable')) {
            $('#leaveHistoryTable').DataTable().destroy();
        }
        $('#leaveHistoryTable').DataTable({
            dom: 't',
            paging: false,
            info: false,
            searching: false,
            ordering: true,
            columnDefs: [
                { orderable: false, targets: [8] }
            ]
        });
    }
});
</script>
@endsection
