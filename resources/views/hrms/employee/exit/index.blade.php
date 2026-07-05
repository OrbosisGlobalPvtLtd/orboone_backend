@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Exit Employees')

@section('_content')
@include('hrms.employee.partials.styles')

<style>
    /* Custom exit-table-card specific styles */
    .exit-table-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .eo-card-header-premium {
        padding: 24px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .eo-card-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .eo-header-icon-circle {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .eo-card-title-premium {
        font-size: 18px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
    }

    .eo-card-subtitle-premium {
        font-size: 12px;
        font-weight: 600;
        color: var(--orb-muted);
        margin: 4px 0 0 0;
    }

    /* Filters Layout (Fit all filters in one row on desktop) */
    .eo-filter-inside {
        padding: 20px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #FCFCFD;
    }

    .exit-filter-grid {
        display: grid;
        grid-template-columns: 1.35fr 1fr 1fr 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
    }

    .exit-filter-grid .eo-field {
        margin-bottom: 0 !important;
    }

    .exit-filter-grid .eo-control {
        height: 38px !important;
        border-radius: 12px !important;
        font-size: 12px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        font-weight: 700;
        padding: 8px 12px;
        outline: none;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    }

    .exit-filter-grid .eo-control:focus {
        border-color: rgba(75, 0, 232, .45) !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    .exit-filter-grid label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    @media(max-width:1200px) {
        .exit-filter-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media(max-width:768px) {
        .exit-filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media(max-width:576px) {
        .exit-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Custom Toolbar for Length */
    .eo-toolbar {
        padding: 14px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .eo-toolbar-left {
        display: flex;
        align-items: center;
    }

    .eo-entries-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .eo-entries-label {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 600;
    }

    .eo-entries-select {
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--orb-border);
        padding: 0 8px;
        font-size: 13px;
        font-weight: 700;
        background: #fff;
        color: var(--orb-text);
        outline: none;
    }

    .eo-export-btn {
        height: 36px;
        padding: 0 14px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 800;
        background: #fff;
        color: #344054;
        border: 1px solid var(--orb-border);
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: 0.15s ease;
        cursor: pointer;
    }

    .eo-export-btn:hover {
        background: #F8FAFC;
        color: var(--orb-text);
        border-color: #D0D5DD;
    }

    /* Scroll Behavior */
    .exit-table-card { overflow:hidden; }
    .exit-table-scroll {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }
    .exit-table-scroll table {
        min-width: 1300px;
        margin-bottom: 0;
    }
    .exit-dt-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 22px;
        border-top: 1px solid #E7EAF3;
        flex-wrap: wrap;
        gap: 12px;
        background: #fff;
    }

    /* DataTable Overrides & Premium Styling */
    .dataTables_wrapper {
        position: relative;
        width: 100%;
    }

    .dataTables_paginate {
        display: flex;
        align-items: center;
    }

    .dataTables_info {
        font-size: 13px;
        color: var(--orb-muted);
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 6px 12px !important;
        margin-left: 4px !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 8px !important;
        background: #fff !important;
        color: #344054 !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        cursor: pointer !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
        display: inline-block !important;
        transition: 0.15s ease;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #F8FAFC !important;
        color: var(--orb-text) !important;
        border-color: #D0D5DD !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--orb-primary) !important;
        color: #fff !important;
        border-color: var(--orb-primary) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        background: #F9FAFB !important;
        color: #D0D5DD !important;
        border-color: #EAECF0 !important;
        cursor: not-allowed !important;
    }

    /* Table Column Sizing and Formatting */
    .eo-table th {
        background: #F8FAFC !important;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .45px;
        border-bottom: 1px solid var(--orb-border);
        white-space: nowrap;
        padding: 16px 20px;
    }

    .eo-table td {
        vertical-align: middle;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 650;
        border-bottom: 1px solid #F1F3F8;
        padding: 16px 20px;
    }

    .eo-table tbody tr:hover {
        background: #FCFAFF;
    }

    .eo-emp-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .eo-emp-cell .eo-name {
        font-weight: 950;
        color: var(--orb-text);
        font-size: 14px;
    }

    .eo-emp-cell .eo-code-under {
        display: inline-flex;
        width: max-content;
        padding: 4px 8px;
        border-radius: 8px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .eo-emp-cell .eo-muted-text {
        font-size: 12px;
        color: var(--orb-muted);
    }

    .eo-pill {
        display: inline-flex !important;
        align-items: center !important;
        border-radius: 999px !important;
        white-space: nowrap !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        font-size: 10px !important;
        padding: 5px 10px !important;
        gap: 6px !important;
    }

    .eo-pill-success {
        background: #DCFCE7 !important;
        color: #15803D !important;
    }

    .eo-pill-info {
        background: #E0F2FE !important;
        color: #0369A1 !important;
    }

    .eo-pill-danger {
        background: #FEE2E2 !important;
        color: #B91C1C !important;
    }

    .eo-pill-warning {
        background: #FEF3C7 !important;
        color: #B45309 !important;
    }

    .eo-pill-resigned {
        background: rgba(247, 144, 9, .12) !important;
        color: #D97706 !important;
    }

    .eo-pill-terminated {
        background: rgba(240, 68, 56, .10) !important;
        color: #DC2626 !important;
    }

    .eo-pill-inactive {
        background: #F1F5F9 !important;
        color: #475569 !important;
    }

    .eo-select,
    .eo-mini-form .eo-input,
    .eo-mini-form .eo-date {
        height: 36px !important;
        border-radius: 8px !important;
        border: 1px solid var(--orb-border) !important;
        padding: 6px 10px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--orb-text) !important;
        background: #fff !important;
        outline: none !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
    }

    .eo-mini-form .eo-input {
        width: 110px !important;
    }

    .eo-mini-form .eo-date {
        width: 130px !important;
    }

    .eo-action-btn {
        height: 36px !important;
        border-radius: 8px !important;
        padding: 0 14px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: 0.15s ease !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
    }

    .eo-action-btn:hover {
        background: var(--orb-primary) !important;
        color: #fff !important;
        border-color: var(--orb-primary) !important;
    }

    /* Fixed Actions Layout (No wrapping, completely inline on a single row) */
    .eo-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 8px !important;
        flex-wrap: nowrap !important;
        white-space: nowrap !important;
    }

    .eo-actions a,
    .eo-actions button {
        flex-shrink: 0 !important;
    }

    .eo-icon-btn {
        width: 36px !important;
        height: 36px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #F8FAFC !important;
        border: 1px solid var(--orb-border) !important;
        color: var(--orb-muted) !important;
        transition: 0.15s ease !important;
    }

    .eo-icon-btn:hover {
        background: var(--orb-primary) !important;
        color: #fff !important;
        border-color: var(--orb-primary) !important;
    }

    /* Premium Modal Styling & Animation */
    .modal-content {
        border: none !important;
        border-radius: 24px !important;
        overflow: hidden !important;
        box-shadow: 0 20px 50px rgba(16, 24, 40, 0.15) !important;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        padding: 20px 24px !important;
        border: none !important;
        color: #fff !important;
    }

    .modal-header .modal-title {
        font-size: 18px !important;
        font-weight: 900 !important;
        color: #fff !important;
        margin: 0 !important;
    }

    .modal-header .modal-subtitle {
        font-size: 12px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 4px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .modal-header .close {
        color: #fff !important;
        opacity: 0.8 !important;
        text-shadow: none !important;
        background: rgba(255, 255, 255, 0.15) !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: none !important;
        outline: none !important;
    }

    .modal-header .close:hover {
        opacity: 1 !important;
        background: rgba(255, 255, 255, 0.25) !important;
    }

    .modal-body {
        padding: 24px !important;
        background: #fff !important;
    }

    .modal-footer {
        padding: 16px 24px !important;
        border-top: 1px solid var(--orb-border) !important;
        background: #F8FAFC !important;
        display: flex !important;
        justify-content: flex-end !important;
        gap: 12px !important;
    }

    /* Modal Form / Button Pill Actions */
    .btn-orb,
    .btn-primary {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border-radius: 50px !important;
        font-weight: 800 !important;
        padding: 10px 24px !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15) !important;
    }

    .btn-orb:hover,
    .btn-primary:hover {
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.25) !important;
        transform: translateY(-1px) !important;
        color: #fff !important;
    }

    .btn-soft,
    .btn-secondary {
        background: #EAECEF !important;
        color: #4A5568 !important;
        border-radius: 50px !important;
        font-weight: 800 !important;
        padding: 10px 24px !important;
        border: none !important;
    }

    .btn-soft:hover,
    .btn-secondary:hover {
        background: #DFE2E6 !important;
        color: #2D3748 !important;
        transform: translateY(-1px) !important;
    }

    .eo-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .required {
        color: #D92D20 !important;
        font-weight: 950 !important;
    }

    /* Action Modal Cards */
    .eo-action-card {
        border: 1px solid #EEF1F6;
        border-radius: 18px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .eo-action-card:last-child {
        margin-bottom: 0;
    }

    .eo-action-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: #F8FAFC;
        border-bottom: 1px solid #EEF1F6;
    }

    .eo-action-icon {
        width: 36px;
        height: 36px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F4F2FF;
        color: var(--orb-primary);
        flex: 0 0 auto;
        font-size: 14px;
    }

    .eo-action-title {
        font-size: 13px;
        font-weight: 950;
        color: var(--orb-text);
        line-height: 1.2;
    }

    .eo-action-sub {
        font-size: 11px;
        font-weight: 750;
        color: var(--orb-muted);
        margin-top: 3px;
        line-height: 1.35;
    }

    .eo-action-body {
        padding: 16px;
        background: #fff;
    }

    .eo-action-btn-premium {
        height: 36px;
        border-radius: 8px;
        padding: 0 14px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid transparent;
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        transition: 0.15s ease;
    }

    .eo-action-btn-premium:hover {
        background: var(--orb-primary) !important;
        color: #fff !important;
    }
</style>

@php
    // Safe extraction of unique values from existing employee dataset for the filtering system
    $departments = $employees->pluck('department_name')->filter()->unique()->sort();
    $statuses = $employees->pluck('employment_status')->filter()->unique()->sort();
    $exitTypes = $employees->pluck('exit_type')->filter()->unique()->sort();
    $assetStatuses = $employees->pluck('asset_handover_status')->filter()->unique()->sort();
    $fnfStatuses = $employees->pluck('fnf_status')->filter()->unique()->sort();
@endphp

<div class="eo-page">
    <div class="eo-container">
        <!-- Hero Header -->
        <div class="eo-header">
            <div>
                <div class="orb-page-kicker">
                    <i class="fas fa-sign-out-alt"></i> HRMS • EMPLOYEE EXIT
                </div>
                <h1 class="eo-title">Exit Employees</h1>
                <p class="eo-subtitle">Track resigned, terminated, inactive employees, clearance, assets, FNF and documents.</p>
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

        @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
        </div>
        @endif

        <div class="eo-card exit-table-card">
            <!-- Premium Card Header -->
            <div class="eo-card-header-premium">
                <div class="eo-card-header-left">
                    <div class="eo-header-icon-circle">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div>
                        <h5 class="eo-card-title-premium">Exit Employee Records</h5>
                        <p class="eo-card-subtitle-premium">Manage exit lifecycle, clearance status, FNF, assets and documents.</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="eo-export-btn js-export-csv">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                    <button type="button" class="eo-export-btn js-export-excel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button type="button" class="eo-export-btn js-export-pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button type="button" class="eo-export-btn js-export-print">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>

            <!-- Filters Section (One Single Compact Row on Desktop) -->
            <div class="eo-filter-inside">
                <div class="exit-filter-grid">
                    <div class="eo-field">
                        <label>Search Employee</label>
                        <input type="text" id="filterSearch" class="eo-control" placeholder="Name, code or email...">
                    </div>
                    <div class="eo-field">
                        <label>Department</label>
                        <select id="filterDepartment" class="eo-control">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ strtolower($dept) }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="eo-field">
                        <label>Status</label>
                        <select id="filterStatus" class="eo-control">
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $stat)
                                <option value="{{ strtolower($stat) }}">{{ ucfirst($stat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="eo-field">
                        <label>Exit Type</label>
                        <select id="filterExitType" class="eo-control">
                            <option value="">All Exit Types</option>
                            @foreach ($exitTypes as $type)
                                <option value="{{ strtolower($type) }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="eo-field">
                        <label>Asset Status</label>
                        <select id="filterAssetStatus" class="eo-control">
                            <option value="">All Asset Statuses</option>
                            @foreach ($assetStatuses as $ast)
                                <option value="{{ strtolower($ast) }}">{{ ucfirst(str_replace('_', ' ', $ast)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="eo-field">
                        <label>FNF Status</label>
                        <select id="filterFnfStatus" class="eo-control">
                            <option value="">All FNF Statuses</option>
                            @foreach ($fnfStatuses as $fnf)
                                <option value="{{ strtolower($fnf) }}">{{ ucfirst(str_replace('_', ' ', $fnf)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="eo-field">
                        <button type="button" id="resetFilter" class="eo-btn eo-btn-light" style="height:38px !important; min-height:38px !important; border-radius:12px !important; font-size:12px !important; font-weight:800; padding:0 16px; box-shadow:none;">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Entries Toolbar -->
            <div class="eo-toolbar">
                <div class="eo-toolbar-left">
                    <div class="eo-entries-wrapper">
                        <span class="eo-entries-label">Show</span>
                        <select id="customLengthMenu" class="eo-entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="eo-entries-label">entries</span>
                    </div>
                </div>
                <div class="eo-toolbar-right text-muted" style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    Exit Clearance Flow Table
                </div>
            </div>

            <!-- Table Container (Horizontal Scroll Only) -->
            <div class="exit-table-scroll">
                <table id="exitEmployeesTable" class="table table-hover eo-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Exit Type</th>
                            <th>Joining</th>
                            <th>Last Working</th>
                            <th>Asset</th>
                            <th>FNF</th>
                            <th>Docs</th>
                            <th>Handover</th>
                            <th>Experience Letter</th>
                            <th>Relieving Letter</th>
                            <th>Exit Flow</th>
                            <th>Final Status</th>
                            <th style="min-width: 250px; width: 250px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        @php
                        $status = strtolower($employee->employment_status ?? 'inactive');

                        $statusClass = match ($status) {
                            'terminated' => 'eo-pill-terminated',
                            'inactive' => 'eo-pill-inactive',
                            default => 'eo-pill-resigned',
                        };

                        $exitType = $employee->exit_type ?? '-';
                        $exitStatus = $employee->exit_status ?? 'exit_initiated';
                        $assetStatus = $employee->asset_handover_status ?? 'pending';
                        $fnfStatus = $employee->fnf_status ?? 'pending';
                        $documentStatus = $employee->document_status ?? 'pending';
                        $handoverStatus = $employee->handover_status ?? 'pending';
                        $experienceStatus = $employee->experience_letter_status ?? 'pending';
                        $relievingStatus = $employee->relieving_letter_status ?? 'pending';
                        $finalStatus = $employee->final_status ?? 'pending';

                        $statusPill = function ($value) {
                            return match (strtolower($value ?? 'pending')) {
                                'completed', 'issued', 'not_required' => 'eo-pill-success',
                                'processing', 'clearance_pending' => 'eo-pill-info',
                                'lost', 'damaged' => 'eo-pill-danger',
                                default => 'eo-pill-warning',
                            };
                        };
                        @endphp

                        <tr id="employee-row-{{ $employee->id }}"
                            data-search="{{ strtolower(($employee->employee_code ?? '') . ' ' . ($employee->name ?? '') . ' ' . ($employee->email ?? '')) }}"
                            data-department="{{ strtolower($employee->department_name ?? '') }}"
                            data-status="{{ strtolower($employee->employment_status ?? '') }}"
                            data-exit-type="{{ strtolower($employee->exit_type ?? '') }}"
                            data-asset="{{ strtolower($employee->asset_handover_status ?? 'pending') }}"
                            data-fnf="{{ strtolower($employee->fnf_status ?? 'pending') }}">
                            <td>
                                <div class="eo-emp-cell">
                                    <div class="eo-name">{{ $employee->name ?? '-' }}</div>
                                    <div class="eo-code-under">{{ $employee->employee_code ?? 'EMP-' . $employee->id }}</div>
                                    <div class="eo-muted-text">{{ $employee->email ?? '-' }}</div>
                                </div>
                            </td>

                            <td>{{ $employee->department_name ?? '-' }}</td>
                            <td>{{ $employee->designation_name ?? '-' }}</td>

                            <td>
                                <span class="eo-pill {{ $statusClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill eo-pill-info">
                                    {{ ucfirst(str_replace('_', ' ', $exitType)) }}
                                </span>
                            </td>

                            <td>
                                {{ !empty($employee->joining_date) ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '-' }}
                            </td>

                            <td>
                                {{ !empty($employee->relieving_date) ? \Carbon\Carbon::parse($employee->relieving_date)->format('d M Y') : '-' }}
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($assetStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $assetStatus)) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($fnfStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $fnfStatus)) }}
                                </span>
                            </td>
                            <td>
                                <span class="eo-pill {{ $statusPill($documentStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $documentStatus)) }}
                                </span>
                            </td>
                            <td>
                                <span class="eo-pill {{ $statusPill($handoverStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $handoverStatus)) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($experienceStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $experienceStatus)) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($relievingStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $relievingStatus)) }}
                                </span>
                            </td>
                            <td>
                                <span class="eo-pill {{ $statusPill($exitStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $exitStatus)) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($finalStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $finalStatus)) }}
                                </span>
                            </td>

                            <td>
                                <div class="eo-actions">
                                    @if (Route::has('hrms.employees.show'))
                                    <a href="{{ route('hrms.employees.show', $employee->id) }}"
                                        class="eo-icon-btn" title="View Employee">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif

                                    @if (Route::has('hrms.employees.manage'))
                                    <a href="{{ route('hrms.employees.manage', $employee->id) }}"
                                        class="eo-icon-btn" title="Manage Employee">
                                        <i class="fas fa-user-cog"></i>
                                    </a>
                                    @elseif(Route::has('hrms.employees.edit'))
                                    <a href="{{ route('hrms.employees.edit', $employee->id) }}"
                                        class="eo-icon-btn" title="Edit Employee">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif

                                    <!-- Compact Premium Trigger Button to launch Employee-specific Exit Modal -->
                                    <button type="button" class="eo-action-btn-premium" data-toggle="modal" data-target="#exitModal-{{ $employee->id }}" title="Process / Update Exit">
                                        <i class="fas fa-clipboard-check"></i> Process / Update Exit
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="16" class="eo-empty text-center py-4">No exit employees found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer / Pagination (will be dynamically appended by DataTable drawCallback) -->
            <div class="exit-dt-footer"></div>
        </div>
    </div>
</div>

<!-- Modal declarations (Rendered outside the scrollable table area for safety and flawless z-indexing) -->
@foreach($employees as $employee)
@php
    $defaultNoticeDays = app(\App\Services\HRMS\Employee\EmployeeExitPolicyS::class)
        ->getNoticePeriodDays(null, 'resignation');
    $status = strtolower($employee->employment_status ?? 'inactive');
    $exitType = $employee->exit_type ?? '-';
    $exitStatus = $employee->exit_status ?? 'exit_initiated';
    $assetStatus = $employee->asset_handover_status ?? 'pending';
    $fnfStatus = $employee->fnf_status ?? 'pending';
    $documentStatus = $employee->document_status ?? 'pending';
    $handoverStatus = $employee->handover_status ?? 'pending';
    $experienceStatus = $employee->experience_letter_status ?? 'pending';
    $relievingStatus = $employee->relieving_letter_status ?? 'pending';
    $finalStatus = $employee->final_status ?? 'pending';

    $statusPill = function ($value) {
        return match (strtolower($value ?? 'pending')) {
            'completed', 'issued', 'not_required' => 'eo-pill-success',
            'processing', 'clearance_pending' => 'eo-pill-info',
            'lost', 'damaged' => 'eo-pill-danger',
            default => 'eo-pill-warning',
        };
    };
@endphp

<div class="modal fade" id="exitModal-{{ $employee->id }}" tabindex="-1" role="dialog" aria-labelledby="exitModalLabel-{{ $employee->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="exitModalLabel-{{ $employee->id }}">
                        <i class="fas fa-user-check mr-2"></i> Process Employee Exit
                    </h5>
                    <p class="modal-subtitle">
                        Clearance flow management for <strong>{{ $employee->name ?? 'Employee' }}</strong> ({{ $employee->employee_code ?? 'N/A' }})
                    </p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(empty($employee->exit_process_id))
                    <!-- If exit is NOT yet initiated, show the initiation form -->
                    @if (Route::has('hrms.employees.exit.mark'))
                    <form action="{{ route('hrms.employees.exit.mark', $employee->id) }}" method="POST" class="mb-0 eo-exit-init-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="eo-label">Exit Type <span class="required">*</span></label>
                                <select name="exit_type" class="eo-control eo-exit-type" required>
                                    <option value="" disabled selected>Select Exit Type</option>
                                    <option value="resignation">Resignation</option>
                                    <option value="termination">Termination</option>
                                    <option value="retirement">Retirement</option>
                                    <option value="contract_end">End of Contract</option>
                                    <option value="mutual_separation">Mutual Separation</option>
                                    <option value="layoff_redundancy">Layoff / Redundancy</option>
                                    <option value="absconding">Absconded</option>
                                    <option value="deceased">Deceased</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="eo-label">Resignation Date</label>
                                <input type="date" name="resignation_date" class="eo-control eo-resignation-date" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="eo-label">Termination/Absconding Date</label>
                                <input type="date" name="termination_date" class="eo-control eo-termination-date" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="eo-label">Last Working Day (Optional)</label>
                                <input type="date" name="last_working_day" class="eo-control eo-last-working-day" value="{{ $employee->relieving_date ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="eo-label">Notice Period (Days)</label>
                                <input type="number" name="notice_period_days" class="eo-control eo-notice-days" min="0" value="{{ $defaultNoticeDays }}" placeholder="Auto from policy">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="eo-label">Reason</label>
                                <input type="text" name="reason" class="eo-control" placeholder="Reason for exit...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="eo-label">Remarks</label>
                                <input type="text" name="remarks" class="eo-control" placeholder="Additional remarks...">
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="mr-3"><input type="checkbox" name="notice_waived" value="1" class="eo-notice-waived"> Notice Waived</label>
                                <label class="mr-3"><input type="checkbox" name="immediate_exit" value="1" class="eo-immediate-exit"> Immediate Exit</label>
                                <label class="mr-3"><input type="checkbox" name="buyout_recovery" value="1"> Buyout/Recovery Applicable</label>
                                <label><input type="checkbox" name="immediate_disable_login" value="1"> Disable Login Immediately</label>
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0" style="background: transparent; border-top: none; margin-top: 15px;">
                            <button type="button" class="btn btn-secondary btn-soft" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-orb" onclick="return confirm('Initiate employee exit process?')">
                                <i class="fas fa-play-circle mr-1"></i> Initiate Exit
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i>
                        <p class="mb-0">Initiation route is not accessible at this moment.</p>
                    </div>
                    @endif
                @else
                    <!-- If exit IS initiated, show live clearance card along with sub-forms inside modal -->
                    <div class="eo-action-card mb-4">
                        <div class="eo-action-card-head">
                            <div class="eo-action-icon"><i class="fas fa-info-circle"></i></div>
                            <div>
                                <div class="eo-action-title">Current Clearance Status</div>
                                <div class="eo-action-sub">Live tracking of clearance checklist and status values.</div>
                            </div>
                        </div>
                        <div class="eo-action-body">
                            <div class="row">
                                <div class="col-6 col-md-4 mb-3">
                                    <span class="eo-label mb-1">Asset Status</span>
                                    <div><span class="eo-pill {{ $statusPill($assetStatus) }}">{{ ucfirst(str_replace('_', ' ', $assetStatus)) }}</span></div>
                                </div>
                                <div class="col-6 col-md-4 mb-3">
                                    <span class="eo-label mb-1">FNF Status</span>
                                    <div><span class="eo-pill {{ $statusPill($fnfStatus) }}">{{ ucfirst(str_replace('_', ' ', $fnfStatus)) }}</span></div>
                                </div>
                                <div class="col-6 col-md-4 mb-3">
                                    <span class="eo-label mb-1">Documents</span>
                                    <div><span class="eo-pill {{ $statusPill($documentStatus) }}">{{ ucfirst(str_replace('_', ' ', $documentStatus)) }}</span></div>
                                </div>
                                <div class="col-6 col-md-4 mb-3">
                                    <span class="eo-label mb-1">Handover</span>
                                    <div><span class="eo-pill {{ $statusPill($handoverStatus) }}">{{ ucfirst(str_replace('_', ' ', $handoverStatus)) }}</span></div>
                                </div>
                                <div class="col-6 col-md-4 mb-3">
                                    <span class="eo-label mb-1">Experience Letter</span>
                                    <div><span class="eo-pill {{ $statusPill($experienceStatus) }}">{{ ucfirst(str_replace('_', ' ', $experienceStatus)) }}</span></div>
                                </div>
                                <div class="col-6 col-md-4 mb-3">
                                    <span class="eo-label mb-1">Relieving Letter</span>
                                    <div><span class="eo-pill {{ $statusPill($relievingStatus) }}">{{ ucfirst(str_replace('_', ' ', $relievingStatus)) }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Auto Module Verification Summary Cards -->
                    @if(!empty($employee->module_summary))
                    <div class="card border-0 shadow-sm mb-4" style="border-radius:20px; background: #fff; border: 1px solid #E7EAF3; box-shadow: 0 10px 28px rgba(16, 24, 40, .06);">
                        <div class="card-header bg-white border-0 pt-3 pb-0">
                            <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-search-dollar text-primary mr-2"></i> Auto Module Verification Summary</h6>
                            <p class="text-muted small mb-0">Informational overview synced from other HRMS modules before exit finalisation.</p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- 1. Attendance -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">Attendance Issues</div>
                                        <div class="h5 font-weight-bold {{ $employee->module_summary['attendance_pending'] > 0 ? 'text-danger' : 'text-success' }} mb-0">
                                            {{ $employee->module_summary['attendance_pending'] }} Pending
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 2. Leave -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">Leave Balance</div>
                                        <div class="h5 font-weight-bold text-primary mb-0">
                                            {{ $employee->module_summary['leave_remaining'] }} Days Left
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Assets -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">Assigned Assets</div>
                                        <div class="h5 font-weight-bold {{ $employee->module_summary['assets_assigned'] > 0 ? 'text-warning' : 'text-success' }} mb-0">
                                            {{ $employee->module_summary['assets_assigned'] }} Assigned
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Payroll -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">Pending Payroll</div>
                                        <div class="h5 font-weight-bold {{ $employee->module_summary['payroll_pending'] > 0 ? 'text-danger' : 'text-success' }} mb-0">
                                            {{ $employee->module_summary['payroll_pending'] }} Pending
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Documents -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">Exit Documents</div>
                                        <div class="h5 font-weight-bold text-success mb-0">
                                            {{ $employee->module_summary['documents_count'] }} Generated
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. Loans/Recoveries -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">Loans/Adjustments</div>
                                        <div class="h5 font-weight-bold {{ $employee->module_summary['loans_pending'] > 0 ? 'text-danger' : 'text-success' }} mb-0">
                                            {{ $employee->module_summary['loans_pending'] }} Pending
                                        </div>
                                    </div>
                                </div>

                                <!-- 7. WFH -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">WFH Requests</div>
                                        <div class="h5 font-weight-bold text-dark mb-0">
                                            {{ $employee->module_summary['wfh_pending'] }} Pending
                                        </div>
                                    </div>
                                </div>

                                <!-- 8. Holiday Work -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">Pending Comp-Off</div>
                                        <div class="h5 font-weight-bold text-dark mb-0">
                                            {{ $employee->module_summary['holiday_work_pending'] }} Pending
                                        </div>
                                    </div>
                                </div>

                                <!-- 9. Notice Period -->
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="p-3 border rounded shadow-xs" style="background:#F9FAFB; border-radius:15px;">
                                        <div class="text-muted small font-weight-bold mb-1">Notice Remaining</div>
                                        <div class="h5 font-weight-bold text-dark mb-0">
                                            {{ $employee->module_summary['notice_days_remaining'] }} Days
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Department Exit Clearances Section -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius:20px; background: #fff; border: 1px solid #E7EAF3; box-shadow: 0 10px 28px rgba(16, 24, 40, .06);">
                        <div class="card-header bg-white border-0 pt-3 pb-0">
                            <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-tasks text-primary mr-2"></i> Department Exit Clearances</h6>
                            <p class="text-muted small mb-0">Approval statuses and checklists across mandatory business departments.</p>
                        </div>
                        <div class="card-body">
                            @php
                                $actor = auth()->user();
                                $isSuperAdmin = $actor && method_exists($actor, 'isSuperAdmin') && $actor->isSuperAdmin();
                                $isHrAdmin = $actor && method_exists($actor, 'hasRole') && $actor->hasRole('hr_admin');
                                $isHRorAdmin = $isSuperAdmin || $isHrAdmin;
                                
                                $deptKeys = ['hr', 'manager', 'it', 'admin', 'finance', 'asset', 'security', 'accounts'];
                                $deptLabels = [
                                    'hr' => 'Human Resources (HR)',
                                    'manager' => 'Reporting Manager',
                                    'it' => 'IT Department',
                                    'admin' => 'Admin Department',
                                    'finance' => 'Finance Department',
                                    'asset' => 'Asset Management Team',
                                    'security' => 'Security Office',
                                    'accounts' => 'Accounts Department',
                                ];
                            @endphp

                            @foreach($deptKeys as $dKey)
                                @php
                                    $clearance = isset($employee->clearances[$dKey]) ? $employee->clearances[$dKey] : null;
                                    $clrStatus = $clearance ? $clearance->status : 'pending';
                                    $clrRemarks = $clearance ? $clearance->remarks : '';
                                    $checklist = $clearance ? json_decode($clearance->checklist, true) : [];
                                    $approvedBy = '';
                                    if ($clearance && $clearance->approved_by_user_id) {
                                        $approvedBy = \DB::table('users')->where('id', $clearance->approved_by_user_id)->value('name');
                                    }
                                    $approvedAt = $clearance && $clearance->approved_at ? \Carbon\Carbon::parse($clearance->approved_at)->format('d M Y, h:i A') : '';
                                    
                                    // Evaluate security/approve permission
                                    $canApproveDept = false;
                                    if ($isHRorAdmin) {
                                        $canApproveDept = true;
                                    } else {
                                        if ($dKey === 'manager' && $actor->employee && $employee->reporting_manager_employee_id == $actor->employee->id) {
                                            $canApproveDept = true;
                                        }
                                        
                                        if (!$canApproveDept) {
                                            $pMap = [
                                                'hr' => 'employee_exit.clearance.hr',
                                                'manager' => 'employee_exit.clearance.manager',
                                                'it' => 'employee_exit.clearance.it',
                                                'admin' => 'employee_exit.clearance.admin',
                                                'finance' => 'employee_exit.clearance.finance',
                                                'asset' => 'employee_exit.clearance.asset',
                                                'security' => 'employee_exit.clearance.security',
                                                'accounts' => 'employee_exit.clearance.accounts',
                                            ];
                                            if (isset($pMap[$dKey]) && $actor->hasPermission($pMap[$dKey])) {
                                                $canApproveDept = true;
                                            }
                                        }
                                        
                                        // Fallback department name check
                                        if (!$canApproveDept && $actor->employee && !empty($actor->employee->department_id)) {
                                            $uDeptName = \DB::table('departments')->where('id', $actor->employee->department_id)->value('name');
                                            if ($uDeptName) {
                                                $uDeptNameLower = strtolower($uDeptName);
                                                if ($dKey === 'it' && (str_contains($uDeptNameLower, 'it') || str_contains($uDeptNameLower, 'infrastructure') || str_contains($uDeptNameLower, 'devops'))) {
                                                    $canApproveDept = true;
                                                } elseif ($dKey === 'finance' && (str_contains($uDeptNameLower, 'finance') || str_contains($uDeptNameLower, 'account'))) {
                                                    $canApproveDept = true;
                                                } elseif ($dKey === 'accounts' && (str_contains($uDeptNameLower, 'finance') || str_contains($uDeptNameLower, 'account'))) {
                                                    $canApproveDept = true;
                                                }
                                            }
                                        }
                                    }
                                @endphp

                                <div class="eo-action-card mb-3" style="border-left: 4px solid {{ $clrStatus === 'approved' ? '#10B981' : ($clrStatus === 'rejected' ? '#EF4444' : '#F59E0B') }};">
                                    <div class="eo-action-card-head d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="eo-action-title">{{ $deptLabels[$dKey] }} Clearance</div>
                                            <div class="eo-action-sub mb-0">Status: 
                                                <span class="badge badge-{{ $clrStatus === 'approved' ? 'success' : ($clrStatus === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($clrStatus) }}
                                                </span>
                                                @if($approvedBy)
                                                    <span class="text-muted small ml-1">by <strong>{{ $approvedBy }}</strong> on {{ $approvedAt }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <i class="fas {{ $clrStatus === 'approved' ? 'fa-check-circle text-success' : ($clrStatus === 'rejected' ? 'fa-times-circle text-danger' : 'fa-clock text-warning') }} fa-lg"></i>
                                        </div>
                                    </div>
                                    
                                    @if($canApproveDept || $isHRorAdmin)
                                    <form action="{{ route('hrms.employees.exit.clearance.dept.update', $employee->id) }}" method="POST" class="mb-0">
                                        @csrf
                                        <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
                                        <input type="hidden" name="department_key" value="{{ $dKey }}">
                                        
                                        <div class="eo-action-body">
                                            @if(!empty($checklist))
                                            <label class="eo-label">Clearance Checklist Items:</label>
                                            <div class="mb-3 pl-2">
                                                @foreach($checklist as $index => $item)
                                                    <div class="custom-control custom-checkbox mb-1">
                                                        <input type="hidden" name="checklist[{{ $item['item'] }}]" value="0">
                                                        <input type="checkbox" name="checklist[{{ $item['item'] }}]" value="1" class="custom-control-input" id="chk-{{ $dKey }}-{{ $index }}-{{ $employee->id }}" {{ $item['completed'] ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-weight-normal text-dark" for="chk-{{ $dKey }}-{{ $index }}-{{ $employee->id }}">{{ $item['item'] }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @endif
                                            
                                            <div class="row">
                                                <div class="col-md-8 mb-2">
                                                    <label class="eo-label">Remarks</label>
                                                    <input type="text" name="remarks" class="eo-control" value="{{ $clrRemarks }}" placeholder="Enter department clearance remarks...">
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="eo-label">Action</label>
                                                    <select name="status" class="eo-control" required>
                                                        <option value="pending" {{ $clrStatus === 'pending' ? 'selected' : '' }}>Set Pending</option>
                                                        <option value="approved" {{ $clrStatus === 'approved' ? 'selected' : '' }}>Approve Clearance</option>
                                                        <option value="rejected" {{ $clrStatus === 'rejected' ? 'selected' : '' }}>Reject Clearance</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end mt-2">
                                                <button type="submit" class="btn btn-primary btn-orb px-3 py-1" style="height:32px; min-height:32px; font-size:11px;">
                                                    <i class="fas fa-save mr-1"></i> Save {{ strtoupper($dKey) }} Status
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @else
                                        <!-- Read only view for employees or other departments -->
                                        <div class="eo-action-body">
                                            @if(!empty($checklist))
                                            <label class="eo-label">Clearance Checklist Items:</label>
                                            <div class="mb-2 pl-2">
                                                @foreach($checklist as $item)
                                                    <div class="mb-1 text-dark">
                                                        <i class="fas {{ $item['completed'] ? 'fa-check-square text-success' : 'fa-square text-muted' }} mr-2"></i>
                                                        <span class="{{ $item['completed'] ? 'text-success font-weight-bold' : '' }}">{{ $item['item'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @endif
                                            @if($clrRemarks)
                                                <div class="mt-2 text-muted small"><strong>Remarks:</strong> {{ $clrRemarks }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Flow Management Section Cards -->
                    <div class="eo-action-card mb-3">
                        <div class="eo-action-card-head">
                            <div class="eo-action-icon" style="background: var(--orb-soft); color: var(--orb-primary);"><i class="fas fa-sync-alt"></i></div>
                            <div>
                                <div class="eo-action-title">Refresh Exit Status</div>
                                <div class="eo-action-sub">Sync clearance levels, assets, FNF and documents from modules.</div>
                            </div>
                        </div>
                        <form action="{{ route('hrms.employees.exit.refresh', $employee->id) }}" method="POST" class="mb-0">
                            @csrf
                            <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
                            <div class="eo-action-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="text-muted small">Update status values dynamically based on live asset/clearance records.</span>
                                <button type="submit" class="btn btn-primary btn-orb px-3 py-2" style="height:36px; min-height:36px; font-size:12px;">
                                    <i class="fas fa-sync-alt mr-1"></i> Refresh Status
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="eo-action-card mb-3">
                        <div class="eo-action-card-head">
                            <div class="eo-action-icon" style="background: #EEF2FF; color: #3730A3;"><i class="fas fa-sliders-h"></i></div>
                            <div>
                                <div class="eo-action-title">Update Clearance Status</div>
                                <div class="eo-action-sub">Set cleared/waived statuses before final exit approval.</div>
                            </div>
                        </div>
                        <form action="{{ route('hrms.employees.exit.clearance.update', $employee->id) }}" method="POST" class="mb-0">
                            @csrf
                            <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
                            <div class="eo-action-body">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="eo-label">Asset Status</label>
                                        <select name="asset_status" class="eo-control">
                                            <option value="">No Change</option>
                                            <option value="pending">Pending</option>
                                            <option value="cleared">Cleared</option>
                                            <option value="waived">Waived</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="eo-label">FnF Status</label>
                                        <select name="fnf_status" class="eo-control">
                                            <option value="">No Change</option>
                                            <option value="pending">Pending</option>
                                            <option value="processing">Processing</option>
                                            <option value="approved">Approved</option>
                                            <option value="paid">Paid</option>
                                            <option value="completed">Completed</option>
                                            <option value="waived">Waived</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="eo-label">Document Status</label>
                                        <select name="document_status" class="eo-control">
                                            <option value="">No Change</option>
                                            <option value="pending">Pending</option>
                                            <option value="generated">Generated</option>
                                            <option value="sent">Sent</option>
                                            <option value="completed">Completed</option>
                                            <option value="waived">Waived</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="eo-label">Handover Status</label>
                                        <select name="handover_status" class="eo-control">
                                            <option value="">No Change</option>
                                            <option value="pending">Pending</option>
                                            <option value="cleared">Cleared</option>
                                            <option value="completed">Completed</option>
                                            <option value="waived">Waived</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="eo-label">Remarks</label>
                                        <input type="text" name="remarks" class="eo-control" placeholder="Optional clearance remarks">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-orb px-3 py-2" style="height:36px; min-height:36px; font-size:12px;">
                                        <i class="fas fa-save mr-1"></i> Save Clearance
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="eo-action-card mb-3">
                        <div class="eo-action-card-head">
                            <div class="eo-action-icon" style="background: #DCFCE7; color: #15803D;"><i class="fas fa-check-double"></i></div>
                            <div>
                                <div class="eo-action-title">Complete Exit Process (Final Settlement)</div>
                                <div class="eo-action-sub">Finalize full-and-final, lock user account, and mark inactive.</div>
                            </div>
                        </div>
                        <form action="{{ route('hrms.employees.exit.complete', $employee->id) }}" method="POST" class="mb-0">
                            @csrf
                            <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
                            <div class="eo-action-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="text-muted small">This action is permanent and will disable login credentials.</span>
                                @php
                                    $clearanceApproved = true;
                                    $mandatoryDepts = ['hr', 'manager', 'it', 'admin', 'finance', 'asset'];
                                    foreach ($mandatoryDepts as $mDept) {
                                        $status = isset($employee->clearances[$mDept]) ? $employee->clearances[$mDept]->status : 'pending';
                                        if ($status !== 'approved') {
                                            $clearanceApproved = false;
                                            break;
                                        }
                                    }
                                @endphp
                                @if($clearanceApproved)
                                    <button type="submit" class="btn btn-success em-btn-success px-3 py-2" style="height:36px; min-height:36px; font-size:12px; border-radius:50px; font-weight:800; border:none;" onclick="return confirm('Complete exit and disable login?')">
                                        <i class="fas fa-user-check mr-1"></i> Complete Exit
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success em-btn-success px-3 py-2" style="height:36px; min-height:36px; font-size:12px; border-radius:50px; font-weight:800; border:none; opacity: 0.5; cursor: not-allowed;" disabled title="Clearances are pending approval">
                                        <i class="fas fa-ban mr-1"></i> Complete Exit (Blocked)
                                    </button>
                                    <span class="text-danger small mt-1 d-block w-100"><i class="fas fa-exclamation-triangle mr-1"></i> All mandatory clearances (HR, Manager, IT, Admin, Finance, Assets) must be approved.</span>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="eo-action-card">
                        <div class="eo-action-card-head">
                            <div class="eo-action-icon" style="background: #FEE2E2; color: #B91C1C;"><i class="fas fa-times-circle"></i></div>
                            <div>
                                <div class="eo-action-title">Cancel Exit Process</div>
                                <div class="eo-action-sub">Abort the exit sequence and restore active employment status.</div>
                            </div>
                        </div>
                        <form action="{{ route('hrms.employees.exit.cancel', $employee->id) }}" method="POST" class="mb-0">
                            @csrf
                            <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
                            <div class="eo-action-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="text-muted small">Restores the employee's active status and deletes exit record.</span>
                                <button type="submit" class="btn btn-danger px-3 py-2" style="height:36px; min-height:36px; font-size:12px; border-radius:50px; font-weight:800; border:none; background:#DC2626;" onclick="return confirm('Cancel this exit process?')">
                                    <i class="fas fa-ban mr-1"></i> Cancel Exit
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer px-0 pb-0" style="background: transparent; border-top: none; margin-top: 15px;">
                        <button type="button" class="btn btn-secondary btn-soft" data-dismiss="modal">Close</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

<!-- DataTables JS & Buttons Extensions -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('filterSearch');
        const departmentFilter = document.getElementById('filterDepartment');
        const statusFilter = document.getElementById('filterStatus');
        const exitTypeFilter = document.getElementById('filterExitType');
        const assetStatusFilter = document.getElementById('filterAssetStatus');
        const fnfStatusFilter = document.getElementById('filterFnfStatus');
        const resetBtn = document.getElementById('resetFilter');

        // Initialize DataTable with custom styling and export features
        const table = $('#exitEmployeesTable').DataTable({
            dom: 't<"d-none"ip>', // Generate native info and pagination hidden, we move them to custom footer
            pageLength: 10,
            ordering: true,
            order: [], // Server-side default order preserved
            columnDefs: [
                { orderable: false, targets: [15] } // Actions column not sortable
            ],
            language: {
                emptyTable: "No exit employees found."
            },
            buttons: [
                {
                    extend: 'csv',
                    className: 'd-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    }
                },
                {
                    extend: 'excel',
                    className: 'd-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    }
                },
                {
                    extend: 'pdf',
                    className: 'd-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    }
                },
                {
                    extend: 'print',
                    className: 'd-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    }
                }
            ],
            drawCallback: function() {
                const api = this.api();
                const $wrapper = $(api.table().container());
                const $card = $('#exitEmployeesTable').closest('.eo-card');

                let $footer = $card.find('.exit-dt-footer');
                if (!$footer.length) {
                    $footer = $('<div class="exit-dt-footer"></div>');
                    $card.append($footer);
                }

                // Retrieve native info and pagination elements
                const $info = $wrapper.find('.dataTables_info');
                const $paginate = $wrapper.find('.dataTables_paginate');

                // Remove hidden class if present
                $info.removeClass('d-none');
                $paginate.removeClass('d-none');

                // Populate custom footer outside horizontal scroll
                $footer.empty().append($info).append($paginate);
            }
        });

        // DataTable filtering logic
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                if (!row) return true;

                const search = (searchInput.value || '').toLowerCase().trim();
                const department = (departmentFilter.value || '').toLowerCase().trim();
                const status = (statusFilter.value || '').toLowerCase().trim();
                const exitType = (exitTypeFilter.value || '').toLowerCase().trim();
                const assetStatus = (assetStatusFilter.value || '').toLowerCase().trim();
                const fnfStatus = (fnfStatusFilter.value || '').toLowerCase().trim();

                const matchSearch = !search || (row.getAttribute('data-search') || '').includes(search);
                const matchDepartment = !department || (row.getAttribute('data-department') || '') === department;
                const matchStatus = !status || (row.getAttribute('data-status') || '') === status;
                const matchExitType = !exitType || (row.getAttribute('data-exit-type') || '') === exitType;
                const matchAsset = !assetStatus || (row.getAttribute('data-asset') || '') === assetStatus;
                const matchFnf = !fnfStatus || (row.getAttribute('data-fnf') || '') === fnfStatus;

                return matchSearch && matchDepartment && matchStatus && matchExitType && matchAsset && matchFnf;
            }
        );

        function applyFilters() {
            table.draw();
        }

        // Custom entries dropdown
        const customLength = document.getElementById('customLengthMenu');
        if (customLength) {
            customLength.addEventListener('change', function() {
                table.page.len(parseInt(this.value)).draw();
            });
        }

        document.querySelectorAll('.eo-exit-init-form').forEach(function(form) {
            const exitType = form.querySelector('.eo-exit-type');
            const resignationDate = form.querySelector('.eo-resignation-date');
            const terminationDate = form.querySelector('.eo-termination-date');
            const lastWorkingDay = form.querySelector('.eo-last-working-day');
            const noticeDays = form.querySelector('.eo-notice-days');
            const noticeWaived = form.querySelector('.eo-notice-waived');
            const immediateExit = form.querySelector('.eo-immediate-exit');

            const toYmd = function(dateObj) {
                const y = dateObj.getFullYear();
                const m = String(dateObj.getMonth() + 1).padStart(2, '0');
                const d = String(dateObj.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + d;
            };

            const recalc = function() {
                if (!exitType || !lastWorkingDay) return;

                const type = String(exitType.value || '').toLowerCase();
                const waived = !!(noticeWaived && noticeWaived.checked);
                const immediate = !!(immediateExit && immediateExit.checked);
                const notice = Math.max(0, parseInt((noticeDays && noticeDays.value) ? noticeDays.value : '15', 10) || 0);

                if (type === 'termination' || type === 'absconding' || immediate) {
                    if (terminationDate && terminationDate.value) {
                        lastWorkingDay.value = terminationDate.value;
                    }
                    return;
                }

                if (waived) {
                    if (resignationDate && resignationDate.value) {
                        lastWorkingDay.value = resignationDate.value;
                    }
                    return;
                }

                if (resignationDate && resignationDate.value) {
                    const base = new Date(resignationDate.value + 'T00:00:00');
                    if (!isNaN(base.getTime())) {
                        base.setDate(base.getDate() + (Math.max(1, notice) - 1));
                        lastWorkingDay.value = toYmd(base);
                    }
                }
            };

            [exitType, resignationDate, terminationDate, noticeDays, noticeWaived, immediateExit].forEach(function(el) {
                if (!el) return;
                el.addEventListener('change', recalc);
                el.addEventListener('input', recalc);
            });

            recalc();
        });

        // Bind custom premium export buttons to DataTable triggers
        $('.js-export-csv').on('click', function() {
            table.button('.buttons-csv').trigger();
        });
        $('.js-export-excel').on('click', function() {
            table.button('.buttons-excel').trigger();
        });
        $('.js-export-pdf').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });
        $('.js-export-print').on('click', function() {
            table.button('.buttons-print').trigger();
        });

        // Event listeners for responsive filtering
        searchInput.addEventListener('keyup', applyFilters);
        departmentFilter.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        exitTypeFilter.addEventListener('change', applyFilters);
        assetStatusFilter.addEventListener('change', applyFilters);
        fnfStatusFilter.addEventListener('change', applyFilters);

        // Reset Filters action
        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            departmentFilter.value = '';
            statusFilter.value = '';
            exitTypeFilter.value = '';
            assetStatusFilter.value = '';
            fnfStatusFilter.value = '';
            applyFilters();
        });
    });
</script>
@endsection
