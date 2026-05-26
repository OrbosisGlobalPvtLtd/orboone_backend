@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Probation / Internship')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .eo-page {
        min-height: calc(100vh - 90px);
        padding: 24px 20px 30px;
        background: var(--orb-bg);
    }

    .eo-container {
        max-width: 1320px;
        margin: 0 auto;
    }

    /* Premium Hero Header */
    .eo-header-premium {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 32px 36px;
        color: #fff;
        margin-bottom: 24px;
        box-shadow: 0 14px 35px rgba(75, 0, 232, 0.15);
        position: relative;
        overflow: hidden;
    }

    .eo-header-kicker {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 8px;
    }

    .eo-header-title {
        font-size: 28px;
        font-weight: 950;
        margin: 0 0 8px 0;
        color: #fff;
    }

    .eo-header-subtitle {
        font-size: 14px;
        font-weight: 650;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
        max-width: 700px;
    }

    .eo-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    /* Card Header Premium */
    .eo-card-header-premium {
        padding: 24px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
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

    /* Filters Layout */
    .eo-filter-inside {
        padding: 20px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #FCFCFD;
    }

    .eo-filter-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr 1fr 1fr auto;
        gap: 14px;
        align-items: end;
    }

    .eo-field label,
    .eo-action-body label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .eo-control,
    .eo-date {
        width: 100%;
        height: 40px;
        border-radius: 10px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 12px;
        outline: none;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    }

    .eo-control:focus,
    .eo-date:focus {
        border-color: rgba(75, 0, 232, .45) !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    .eo-readonly-date {
        background: #F8FAFC !important;
        color: #344054 !important;
    }

    .eo-btn {
        min-height: 40px;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid transparent;
        text-decoration: none !important;
        cursor: pointer;
        white-space: nowrap;
    }

    .eo-btn-light {
        background: #fff;
        color: #344054;
        border-color: var(--orb-border);
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    }

    .eo-btn-light:hover {
        background: #F8FAFC;
        color: var(--orb-text);
        border-color: #D0D5DD;
    }

    /* Custom Toolbar for Exports/Length */
    .eo-toolbar {
        padding: 14px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
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

    .eo-toolbar-right {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .eo-export-btn {
        height: 32px;
        padding: 0 12px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        background: #fff;
        color: #344054;
        border: 1px solid var(--orb-border);
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: 0.15s ease;
    }

    .eo-export-btn:hover {
        background: #F8FAFC;
        color: var(--orb-text);
        border-color: #D0D5DD;
    }

    /* Horizontal Table Scroll ONLY for the table */
    .lifecycle-table-wrap {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .eo-table {
        min-width: 1160px;
        margin-bottom: 0 !important;
    }

    .eo-table th {
        background: #F8FAFC;
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

    .eo-code {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 10px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .eo-emp-cell {
        min-width: 150px;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .eo-name {
        font-weight: 900;
        color: var(--orb-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }

    .eo-code-under {
        display: inline-flex;
        width: max-content;
        padding: 5px 8px;
        border-radius: 9px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .eo-table th:nth-child(5),
    .eo-table td:nth-child(5) {
        min-width: 118px;
    }

    .eo-table th:nth-child(6),
    .eo-table td:nth-child(6) {
        min-width: 155px;
        width: 155px;
    }

    .eo-muted-text {
        font-size: 12px;
        color: var(--orb-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 145px;
    }

    .eo-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
        background: #F2F4F7;
        color: #667085;
    }

    .eo-pill-active {
        background: rgba(18, 183, 106, .10);
        color: #12B76A;
    }

    .eo-pill-warning {
        background: rgba(247, 144, 9, .12);
        color: #F79009;
    }

    .eo-pill-purple {
        background: rgba(75, 0, 232, .08);
        color: #4B00E8;
    }

    .eo-pill-danger {
        background: rgba(240, 68, 56, .10);
        color: #F04438;
    }

    .eo-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        white-space: nowrap;
    }

    .eo-icon-btn,
    .eo-more-btn {
        width: 34px;
        height: 34px;
        border: 1px solid var(--orb-border);
        border-radius: 11px;
        background: #fff;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: .18s ease;
        text-decoration: none !important;
        cursor: pointer;
    }

    .eo-icon-btn:hover,
    .eo-more-btn:hover {
        color: #fff;
        background: var(--orb-primary);
        border-color: var(--orb-primary);
    }

    .eo-empty {
        text-align: center;
        color: #667085;
        font-weight: 800;
        padding: 24px;
    }

    .eo-highlight-row {
        background: #FFF7ED !important;
        box-shadow: inset 4px 0 0 #F79009;
    }

    .modal-backdrop {
        z-index: 1240 !important;
        background: #0F172A !important;
    }

    .modal-backdrop.show {
        opacity: .58 !important;
    }

    .modal {
        z-index: 1250 !important;
    }

    .eo-life-modal .modal-dialog {
        max-width: 780px;
    }

    .eo-modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    .eo-modal-header {
        padding: 18px 22px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        border-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .eo-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        color: #fff;
    }

    .eo-modal-subtitle {
        margin-top: 4px;
        font-size: 12px;
        color: rgba(255, 255, 255, .78);
        font-weight: 700;
    }

    .eo-modal-close {
        color: #fff;
        opacity: 1;
        text-shadow: none;
        outline: none;
    }

    .eo-modal-body {
        padding: 18px;
        background: #fff;
        max-height: 78vh;
        overflow-y: auto;
    }

    .eo-action-card {
        border: 1px solid #EEF1F6;
        border-radius: 18px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 14px;
    }

    .eo-action-card:last-child {
        margin-bottom: 0;
    }

    .eo-action-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 14px;
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

    .eo-info-note {
        padding: 10px 12px;
        border-radius: 13px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.45;
    }

    .eo-menu-submit {
        width: 100%;
        min-height: 42px;
        border: 0;
        border-radius: 13px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    }

    .eo-menu-submit-success {
        background: #16A34A;
    }

    .eo-menu-submit-warning {
        background: #F79009;
    }

    .eo-menu-submit-danger {
        background: #DC2626;
    }

    /* Custom DataTables Premium Styling */
    .dataTables_wrapper {
        position: relative;
        width: 100%;
    }

    .orb-dt-toolbar,
    .orb-dt-footer,
    .dataTables_info,
    .dataTables_paginate {
        overflow: visible !important;
    }

    .dataTables_wrapper .row,
    .dataTables_wrapper .dataTables_paginate {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .orb-dt-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 28px;
        border-top: 1px solid var(--orb-border);
        background: #fff;
        flex-wrap: wrap;
    }

    .dataTables_paginate {
        padding: 0 !important;
        display: flex;
        align-items: center;
    }

    .dataTables_info {
        padding: 0 !important;
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

    @media(max-width:1100px) {
        .eo-filter-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media(max-width:991px) {
        .eo-filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .eo-toolbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .eo-toolbar-right {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media(max-width:576px) {
        .eo-page {
            padding: 12px 8px 22px;
        }

        .eo-header-premium {
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .eo-header-title {
            font-size: 22px;
        }

        .eo-header-subtitle {
            font-size: 12px;
        }

        .eo-card-header-premium {
            padding: 18px;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr;
        }

        .eo-btn {
            width: 100%;
        }

        .eo-filter-inside {
            padding: 16px;
        }

        .eo-life-modal .modal-dialog {
            margin: 12px;
        }

        .eo-modal-body {
            padding: 14px;
        }
    }
</style>

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header-premium">
            <div class="eo-header-kicker">HRMS • EMPLOYEE LIFECYCLE</div>
            <h1 class="eo-header-title">Probation / Internship</h1>
            <p class="eo-header-subtitle">Track active probation, internships, extensions, conversion and permanent status.</p>
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

        <div class="eo-card">
            <!-- Card Header Premium -->
            <div class="eo-card-header-premium">
                <div class="eo-card-header-left">
                    <div class="eo-header-icon-circle">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h4 class="eo-card-title-premium">Probation & Internship List</h4>
                        <p class="eo-card-subtitle-premium">Manage employee lifecycle stages, probation timelines, internships and conversion status.</p>
                    </div>
                </div>
            </div>

            <div class="eo-filter-inside">
                <div class="eo-filter-grid">
                    <div class="eo-field">
                        <label>Search</label>
                        <input type="text" id="filterSearch" class="eo-control" placeholder="Search employee...">
                    </div>

                    <div class="eo-field">
                        <label>Department</label>
                        <select id="filterDepartment" class="eo-control">
                            <option value="">All Departments</option>
                            @foreach ($departments ?? [] as $dept)
                            <option value="{{ strtolower($dept->name ?? '') }}">{{ $dept->name ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Status</label>
                        <select id="filterStatus" class="eo-control">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="extended">Extended</option>
                            <option value="completed">Completed</option>
                            <option value="converted_to_probation">Converted To Probation</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Stage</label>
                        <select id="filterEmploymentType" class="eo-control">
                            <option value="">All Stage</option>
                            <option value="probation">Probation</option>
                            <option value="intern">Internship</option>
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>&nbsp;</label>
                        <button type="button" id="resetFilter" class="eo-btn eo-btn-light">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Custom Premium Toolbar for exports and length menu -->
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

                <div class="eo-toolbar-right">
                    <button type="button" class="btn eo-export-btn js-export-csv" title="Export CSV"><i class="fas fa-file-csv mr-1"></i>CSV</button>
                    <button type="button" class="btn eo-export-btn js-export-excel" title="Export Excel"><i class="fas fa-file-excel mr-1"></i>Excel</button>
                    <button type="button" class="btn eo-export-btn js-export-pdf" title="Export PDF"><i class="fas fa-file-pdf mr-1"></i>PDF</button>
                    <button type="button" class="btn eo-export-btn js-export-print" title="Print"><i class="fas fa-print mr-1"></i>Print</button>
                </div>
            </div>

            <div class="lifecycle-table-wrap">
                <table class="table table-hover eo-table" id="probationInternshipTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Stage</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Salary Type</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($employees as $employee)
                        @php
                        $stage = strtolower($employee->employee_stage ?? '');
                        $type = strtolower($employee->employment_type ?? '');
                        $isIntern = $stage === 'internship' || ($stage === '' && $type === 'intern');
                        $displayType = $isIntern ? 'Internship' : 'Probation';

                        $startDate = $isIntern
                        ? $employee->internship_start_date
                        : ($employee->probation_start_date ?:
                        $employee->joining_date);

                        $endDate = $isIntern
                        ? ($employee->internship_extended_to ?:
                        $employee->internship_end_date)
                        : $employee->probation_end_date;

                        $effectiveDate = $endDate
                        ? \Carbon\Carbon::parse($endDate)->copy()->addDay()
                        : \Carbon\Carbon::today();

                        $status = $isIntern
                        ? ($employee->internship_status ?:
                        ($employee->internship_extended_to
                        ? 'extended'
                        : 'active'))
                        : ($employee->probation_status ?:
                        'pending');

                        $statusClass = match ($status) {
                        'active', 'completed', 'converted_to_probation' => 'eo-pill-active',
                        'extended' => 'eo-pill-warning',
                        'exited' => 'eo-pill-danger',
                        default => 'eo-pill-purple',
                        };

                        $salaryType = $isIntern
                        ? ((int) ($employee->is_paid_intern ?? 0) === 1
                        ? 'Paid / Stipend'
                        : 'Unpaid')
                        : 'Salary';
                        @endphp

                        <tr id="employee-row-{{ $employee->id }}" data-employee-id="{{ $employee->id }}"
                            data-search="{{ strtolower(($employee->name ?? '') . ' ' . ($employee->employee_code ?? '') . ' ' . ($employee->department_name ?? '') . ' ' . ($employee->designation_name ?? '') . ' ' . $displayType . ' ' . $status) }}"
                            data-department="{{ strtolower($employee->department_name ?? '') }}"
                            data-status="{{ strtolower($status) }}"
                            data-employment-type="{{ $isIntern ? 'intern' : 'probation' }}">

                            <td>
                                <div class="eo-emp-cell">
                                    <div class="eo-name" title="{{ $employee->name ?? '-' }}">
                                        {{ $employee->name ?? '-' }}
                                    </div>
                                    <span class="eo-code-under">
                                        {{ $employee->employee_code ?? 'EMP-' . $employee->id }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="eo-muted-text" title="{{ $employee->department_name ?? '-' }}">
                                    {{ $employee->department_name ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="eo-muted-text" title="{{ $employee->designation_name ?? '-' }}">
                                    {{ $employee->designation_name ?? '-' }}
                                </div>
                            </td>
                            <td><span class="eo-pill eo-pill-purple">{{ $displayType }}</span></td>
                            <td>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : '-' }}</td>
                            <td>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}</td>
                            <td><span
                                    class="eo-pill {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </td>
                            <td>{{ $salaryType }}</td>

                            <td>
                                <div class="eo-actions">
                                    @if (Route::has('hrms.employees.show'))
                                    <a href="{{ route('hrms.employees.show', $employee->id) }}"
                                        class="eo-icon-btn" title="View Employee">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif

                                    @if (Route::has('hrms.employees.edit'))
                                    <a href="{{ route('hrms.employees.edit', $employee->id) }}"
                                        class="eo-icon-btn" title="Edit Employee">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif

                                    <button type="button" class="eo-more-btn" title="Lifecycle Actions"
                                        data-toggle="modal"
                                        data-target="#employeeLifecycleModal{{ $employee->id }}">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @foreach ($employees as $employee)
        @php
        $stage = strtolower($employee->employee_stage ?? '');
        $type = strtolower($employee->employment_type ?? '');
        $isIntern = $stage === 'internship' || ($stage === '' && $type === 'intern');
        $displayType = $isIntern ? 'Internship' : 'Probation';

        $endDate = $isIntern
        ? ($employee->internship_extended_to ?:
        $employee->internship_end_date)
        : $employee->probation_end_date;

        $effectiveDate = $endDate
        ? \Carbon\Carbon::parse($endDate)->copy()->addDay()
        : \Carbon\Carbon::today();

        $status = $isIntern
        ? ($employee->internship_status ?:
        ($employee->internship_extended_to
        ? 'extended'
        : 'active'))
        : ($employee->probation_status ?:
        'pending');
        @endphp

        <div class="modal fade eo-life-modal" id="employeeLifecycleModal{{ $employee->id }}" tabindex="-1"
            role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content eo-modal-content">
                    <div class="eo-modal-header">
                        <div>
                            <h5 class="eo-modal-title">{{ $employee->name ?? 'Employee' }}</h5>
                            <div class="eo-modal-subtitle">
                                {{ $employee->employee_code ?? 'EMP-' . $employee->id }} · {{ $displayType }} ·
                                Effective actions from {{ $effectiveDate->format('d M Y') }}
                            </div>
                        </div>

                        <button type="button" class="close eo-modal-close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="eo-modal-body">
                        @if (
                        !$isIntern &&
                        !in_array($status, ['completed', 'confirmed'], true) &&
                        Route::has('hrms.employees.probation.mark_permanent'))
                        <div class="eo-action-card">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon"><i class="fas fa-user-check"></i></div>
                                <div>
                                    <div class="eo-action-title">Mark Permanent</div>
                                    <div class="eo-action-sub">Permanent date and salary will apply after
                                        probation end date.</div>
                                </div>
                            </div>

                            <form
                                action="{{ route('hrms.employees.probation.mark_permanent', $employee->id) }}"
                                method="POST">
                                @csrf
                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Permanent Effective Date</label>
                                            <input type="text" class="eo-date eo-readonly-date"
                                                value="{{ $effectiveDate->format('d M Y') }}" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Permanent Salary</label>
                                            <input type="number" name="actual_salary" class="eo-date"
                                                min="0" step="1"
                                                placeholder="Optional salary update">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label>Salary Reason</label>
                                            <input type="text" name="salary_change_reason" class="eo-date"
                                                placeholder="Permanent salary update">
                                        </div>
                                    </div>

                                    <div class="eo-info-note mb-3">
                                        If admin marks early, permanent status and salary will still start from
                                        {{ $effectiveDate->format('d M Y') }}.
                                    </div>

                                    <button type="submit" class="eo-menu-submit"
                                        onclick="return confirm('Mark this employee as permanent? Effective date will be after probation end date.')">
                                        <i class="fas fa-user-check"></i> Mark Permanent
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @if ($isIntern && Route::has('hrms.employees.internship.extend'))
                        <div class="eo-action-card">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon"><i class="fas fa-calendar-plus"></i></div>
                                <div>
                                    <div class="eo-action-title">Extend Internship</div>
                                    <div class="eo-action-sub">Extend internship and optionally update stipend.
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.internship.extend', $employee->id) }}"
                                method="POST">
                                @csrf
                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Current End Date</label>
                                            <input type="text" class="eo-date eo-readonly-date"
                                                value="{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}"
                                                readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Extend Internship To</label>
                                            <input type="date" name="internship_extended_to"
                                                class="eo-date"
                                                min="{{ $endDate ? \Carbon\Carbon::parse($endDate)->copy()->addDay()->toDateString() : now()->toDateString() }}"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>New Stipend / Salary</label>
                                            <input type="number" name="actual_salary" class="eo-date"
                                                min="0" step="1"
                                                placeholder="Leave blank if no change">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Extension Reason</label>
                                            <input type="text" name="reason" class="eo-date"
                                                placeholder="Reason for extension">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label>Salary Reason</label>
                                            <input type="text" name="salary_change_reason" class="eo-date"
                                                placeholder="Stipend update reason">
                                        </div>
                                    </div>

                                    <button type="submit" class="eo-menu-submit eo-menu-submit-warning"
                                        onclick="return confirm('Extend this internship?')">
                                        <i class="fas fa-calendar-plus"></i> Extend Internship
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @if ($isIntern && Route::has('hrms.employees.internship.complete'))
                        <div class="eo-action-card">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon"><i class="fas fa-check-circle"></i></div>
                                <div>
                                    <div class="eo-action-title">Complete / Convert Internship</div>
                                    <div class="eo-action-sub">Mark completed, move to probation, or move
                                        permanent.</div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.internship.complete', $employee->id) }}"
                                method="POST">
                                @csrf
                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Effective Date</label>
                                            <input type="text" class="eo-date eo-readonly-date"
                                                value="{{ $effectiveDate->format('d M Y') }}" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Action</label>
                                            <select name="next_stage" class="eo-date" required>
                                                <option value="completed">Only Mark Internship Completed
                                                </option>
                                                <option value="probation">Complete & Move to Probation</option>
                                                <!-- <option value="permanent">Complete & Move Permanent</option> -->
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Salary / Stipend</label>
                                            <input type="number" name="actual_salary" class="eo-date"
                                                min="0" step="1"
                                                placeholder="Required only if moving stage">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Salary Reason</label>
                                            <input type="text" name="salary_change_reason" class="eo-date"
                                                placeholder="Internship completion salary">
                                        </div>
                                    </div>

                                    <div class="eo-info-note mb-3">
                                        If admin applies early, lifecycle and salary will still be effective
                                        from {{ $effectiveDate->format('d M Y') }}.
                                    </div>

                                    <button type="submit" class="eo-menu-submit eo-menu-submit-success"
                                        onclick="return confirm('Apply internship completion action? Effective date will be after internship end date.')">
                                        <i class="fas fa-check-circle"></i> Apply Action
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @if ($isIntern && Route::has('hrms.employees.exit.mark'))
                        <div class="eo-action-card mb-0">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon" style="background:#FEE2E2;color:#DC2626;">
                                    <i class="fas fa-user-times"></i>
                                </div>
                                <div>
                                    <div class="eo-action-title">Internship Exit</div>
                                    <div class="eo-action-sub">Use only if intern will not continue after
                                        internship.</div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.exit.mark', $employee->id) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="employment_status" value="inactive">

                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Internship Exit Date</label>
                                            <input type="date" name="relieving_date" class="eo-date"
                                                value="{{ $effectiveDate->toDateString() }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Exit Reason</label>
                                            <input type="text" name="reason" class="eo-date"
                                                placeholder="Internship completed exit">
                                        </div>
                                    </div>

                                    <button type="submit" class="eo-menu-submit eo-menu-submit-danger"
                                        onclick="return confirm('Exit this intern?')">
                                        <i class="fas fa-user-times"></i> Internship Exit
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>

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
        const employmentTypeFilter = document.getElementById('filterEmploymentType');
        const resetBtn = document.getElementById('resetFilter');

        const urlParams = new URLSearchParams(window.location.search);
        const highlightEmployeeId = urlParams.get('highlight_employee') || urlParams.get('highlight');
        const stageFromNotification = (urlParams.get('stage') || '').toLowerCase();

        // Debug check
        const $table = $('#probationInternshipTable');
        console.log('TH count:', $table.find('thead tr:first th').length);
        $table.find('tbody tr').each(function(index) {
            console.log('Row', index, 'TD count:', $(this).children('td').length);
        });

        // Initialize DataTable
        const table = $('#probationInternshipTable').DataTable({
            dom: 't<"d-none"ip>', // Generate pagination & info in a hidden wrapper, then move them manually
            pageLength: 10,
            ordering: true,
            order: [], // Disable initial sort to keep server order
            columnDefs: [{
                    orderable: false,
                    targets: [8]
                } // Actions column not sortable
            ],
            language: {
                emptyTable: "No probation or internship records found."
            },
            buttons: [{
                    extend: 'csv',
                    className: 'd-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                },
                {
                    extend: 'excel',
                    className: 'd-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                },
                {
                    extend: 'pdf',
                    className: 'd-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                },
                {
                    extend: 'print',
                    className: 'd-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                }
            ],
            initComplete: function() {
                const api = this.api();
                const $wrapper = $(api.table().container());
                const $card = $('#probationInternshipTable').closest('.eo-card');

                let $footer = $card.find('.orb-dt-footer');
                if (!$footer.length) {
                    $footer = $('<div class="orb-dt-footer"></div>');
                    $card.append($footer);
                }

                $footer.empty();

                // Pluck the pagination and info and append to footer
                const $info = $wrapper.find('.dataTables_info');
                const $paginate = $wrapper.find('.dataTables_paginate');

                $footer.append($info);
                $footer.append($paginate);
            }
        });

        // Custom search filtering pipeline
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                if (!row) return true;

                const search = (searchInput.value || '').toLowerCase().trim();
                const department = (departmentFilter.value || '').toLowerCase().trim();
                const status = (statusFilter.value || '').toLowerCase().trim();
                const employmentType = (employmentTypeFilter.value || '').toLowerCase().trim();

                const matchSearch = !search || (row.getAttribute('data-search') || '').includes(search);
                const matchDepartment = !department || (row.getAttribute('data-department') || '') === department;
                const matchStatus = !status || (row.getAttribute('data-status') || '') === status;
                const matchEmploymentType = !employmentType || (row.getAttribute('data-employment-type') || '') === employmentType;

                return matchSearch && matchDepartment && matchStatus && matchEmploymentType;
            }
        );

        function applyFilters() {
            table.draw();
        }

        // Custom length menu binding
        const customLength = document.getElementById('customLengthMenu');
        if (customLength) {
            customLength.addEventListener('change', function() {
                table.page.len(parseInt(this.value)).draw();
            });
        }

        // Bind export actions
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

        function highlightEmployeeFromNotification() {
            if (!highlightEmployeeId) return;

            searchInput.value = '';
            departmentFilter.value = '';
            statusFilter.value = '';
            if (stageFromNotification === 'probation') {
                employmentTypeFilter.value = 'probation';
            } else if (stageFromNotification === 'internship') {
                employmentTypeFilter.value = 'intern';
            } else {
                employmentTypeFilter.value = '';
            }

            applyFilters();

            const row = document.getElementById('employee-row-' + highlightEmployeeId);

            if (row) {
                row.classList.add('eo-highlight-row');

                setTimeout(function() {
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 250);

                setTimeout(function() {
                    row.classList.remove('eo-highlight-row');
                }, 8000);
            }
        }

        searchInput.addEventListener('keyup', applyFilters);
        departmentFilter.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        employmentTypeFilter.addEventListener('change', applyFilters);

        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            departmentFilter.value = '';
            statusFilter.value = '';
            employmentTypeFilter.value = '';
            applyFilters();
        });

        highlightEmployeeFromNotification();
    });
</script>
@endsection