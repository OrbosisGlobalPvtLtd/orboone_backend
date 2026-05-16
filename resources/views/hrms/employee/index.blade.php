@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Employee Directory')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-success: #12B76A;
        --orb-warning: #F79009;
        --orb-danger: #EC4E74;
        --orb-info: #06AED4;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .eo-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background:
            radial-gradient(circle at top left, rgba(75, 0, 232, .08), transparent 30%),
            var(--orb-bg);
    }

    .eo-container {
        max-width: 1420px;
        margin: 0 auto;
    }

    .eo-hero {
        background: linear-gradient(135deg, #4B00E8, #8600EE);
        border-radius: 24px;
        padding: 20px;
        color: #fff;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .22);
        margin-bottom: 14px;
        position: relative;
        overflow: hidden;
    }

    .eo-hero:before {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
        right: -90px;
        top: -120px;
    }

    .eo-hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .eo-title {
        margin: 0;
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -.5px;
    }

    .eo-subtitle {
        margin: 5px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
        font-weight: 650;
    }

    .eo-hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .eo-btn {
        min-height: 40px;
        border-radius: 13px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid transparent;
        text-decoration: none !important;
        cursor: pointer;
        white-space: nowrap;
    }

    .eo-btn-primary {
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        box-shadow: 0 10px 22px rgba(75, 0, 232, .16);
    }

    .eo-btn-white {
        color: var(--orb-primary) !important;
        background: #fff;
        border-color: rgba(255, 255, 255, .45);
    }

    .eo-btn-light {
        background: #fff;
        color: var(--orb-text);
        border-color: var(--orb-border);
    }

    .eo-btn-warning {
        background: #FFF7E8;
        color: #B54708 !important;
        border-color: #FEDF89;
    }

    .eo-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        margin-bottom: 14px;
    }

    .eo-stat {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .eo-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex: 0 0 auto;
    }

    .eo-stat-icon.primary {
        background: #F4F2FF;
        color: var(--orb-primary);
    }

    .eo-stat-icon.success {
        background: rgba(18, 183, 106, .10);
        color: var(--orb-success);
    }

    .eo-stat-icon.warning {
        background: rgba(247, 144, 9, .12);
        color: var(--orb-warning);
    }

    .eo-stat-icon.info {
        background: rgba(6, 174, 212, .10);
        color: var(--orb-info);
    }

    .eo-stat-icon.danger {
        background: rgba(236, 78, 116, .10);
        color: var(--orb-danger);
    }

    .eo-stat-label {
        margin: 0;
        font-size: 11px;
        color: var(--orb-muted);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .eo-stat-value {
        margin: 2px 0 0;
        font-size: 20px;
        color: var(--orb-text);
        font-weight: 950;
    }

    .eo-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .eo-filter-inside {
        padding: 15px 16px;
        border-bottom: 1px solid var(--orb-border);
        background: #FCFCFD;
    }

    .eo-filter-grid {
        display: grid;
        grid-template-columns: 1.8fr 1fr 1fr 1fr 1fr auto;
        gap: 10px;
        align-items: end;
    }

    .eo-field label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .eo-control {
        width: 100%;
        height: 42px;
        border-radius: 13px !important;
        border: 1px solid var(--orb-border) !important;
        background: #F9FAFB !important;
        color: var(--orb-text) !important;
        font-size: 13px;
        font-weight: 750;
        padding: 8px 12px;
        outline: none;
    }

    .eo-control:focus {
        border-color: rgba(75, 0, 232, .45) !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    .eo-table-toolbar {
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
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
    }

    .dt-buttons .btn:hover {
        color: #fff !important;
        border-color: var(--orb-primary) !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
    }

    .eo-table-footer {
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fff;
    }

    .page-link {
        border-radius: 10px !important;
        margin: 0 3px;
        border: 1px solid var(--orb-border);
        color: var(--orb-primary);
        font-weight: 850;
    }

    .page-item.active .page-link {
        background: var(--orb-primary);
        border-color: var(--orb-primary);
    }

    @media(max-width:1200px) {
        .eo-stat-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .eo-filter-grid {
            grid-template-columns: 1fr 1fr 1fr;
        }
    }

    @media(max-width:991px) {
        .eo-hero-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .eo-table-toolbar,
        .eo-table-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        #employeeExportButtons,
        #employeeLengthBox,
        #employeeInfoBox,
        #employeePaginationBox {
            width: 100%;
        }
    }

    @media(max-width:576px) {
        .eo-page {
            padding: 12px 8px 22px;
        }

        .eo-hero {
            border-radius: 18px;
            padding: 16px;
        }

        .eo-title {
            font-size: 22px;
        }

        .eo-stat-grid {
            grid-template-columns: 1fr;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr;
        }

        .eo-btn {
            width: 100%;
        }

        .eo-hero-actions {
            width: 100%;
        }

        #employeeExportButtons {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }

        .dt-buttons {
            display: contents;
        }

        .dt-buttons .btn {
            width: 100%;
            font-size: 11px !important;
            padding: 8px 6px !important;
        }
    }
</style>

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-hero">
            <div class="eo-hero-content">
                <div>
                    <h1 class="eo-title">Employee Directory</h1>
                    <p class="eo-subtitle">
                        Active approved employees, verification status, work mode and HR lifecycle in one premium view.
                    </p>
                </div>

                <div class="eo-hero-actions">
                    @if (Route::has('hrms.employees.pending_profiles'))
                    <a href="{{ route('hrms.employees.pending_profiles') }}" class="eo-btn eo-btn-white">
                        <i class="fas fa-user-clock"></i>
                        Pending Profiles
                    </a>
                    @endif

                    @if (Route::has('hrms.employees.create'))
                    <a href="{{ route('hrms.employees.create') }}" class="eo-btn eo-btn-white">
                        <i class="fas fa-plus-circle"></i>
                        Add Employee
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @php
        $stats = $stats ?? [];
        @endphp

        <div class="eo-stat-grid">
            <div class="eo-stat">
                <div class="eo-stat-icon primary"><i class="fas fa-users"></i></div>
                <div>
                    <p class="eo-stat-label">Total Employees</p>
                    <h3 class="eo-stat-value">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="eo-stat">
                <div class="eo-stat-icon success"><i class="fas fa-user-check"></i></div>
                <div>
                    <p class="eo-stat-label">Active</p>
                    <h3 class="eo-stat-value">{{ $stats['active'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="eo-stat">
                <div class="eo-stat-icon warning"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <p class="eo-stat-label">Probation</p>
                    <h3 class="eo-stat-value">{{ $stats['probation'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="eo-stat">
                <div class="eo-stat-icon info"><i class="fas fa-laptop-house"></i></div>
                <div>
                    <p class="eo-stat-label">WFH / Hybrid</p>
                    <h3 class="eo-stat-value">{{ $stats['remote'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="eo-stat">
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

        <div class="eo-card">
            <div class="eo-filter-inside">
                <div class="eo-filter-grid">
                    <div class="eo-field">
                        <label>Search</label>
                        <input type="text" id="filterSearch" class="eo-control"
                            placeholder="Search name, employee code, email, phone...">
                    </div>

                    <div class="eo-field">
                        <label>Department</label>
                        <select id="filterDepartment" class="eo-control">
                            <option value="">All Departments</option>
                            @foreach ($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Work Mode</label>
                        <select id="filterWorkMode" class="eo-control">
                            <option value="">All Mode</option>
                            <option value="wfo">WFO</option>
                            <option value="wfh">WFH</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Employment Type</label>
                        <select id="filterEmploymentType" class="eo-control">
                            <option value="">All Type</option>
                            <option value="full_time">Full Time</option>
                            <option value="intern">Intern</option>
                            <option value="contract">Contract</option>
                            <option value="part_time">Part Time</option>
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Status</label>
                        <select id="filterStatus" class="eo-control">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="probation">Probation</option>
                            <option value="internship">Internship</option>
                            <option value="notice">Notice</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>&nbsp;</label>
                        <button type="button" id="resetFilter" class="eo-btn eo-btn-light">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="eo-table-toolbar">
                <div id="employeeLengthBox"></div>
                <div id="employeeExportButtons"></div>
            </div>

            <div class="table-responsive">
                <table id="employeesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Type</th>
                            <th>Mode</th>
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
                    alert('Employee data load nahi ho raha. Console check karo.');
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
                    render: function(data) {
                        return pill(data || '-', data || '');
                    }
                },
                {
                    data: 'work_mode',
                    name: 'work_mode',
                    defaultContent: '-',
                    render: function(data) {
                        return pill(data || '-', data || '');
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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