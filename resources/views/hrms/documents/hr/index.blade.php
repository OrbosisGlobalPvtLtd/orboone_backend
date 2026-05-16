@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Pending Verifications')

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
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .eo-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .eo-container {
        max-width: 1320px;
        margin: 0 auto;
    }

    .eo-header {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .eo-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 24px;
        font-weight: 900;
        letter-spacing: -.4px;
    }

    .eo-subtitle {
        margin: 4px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .eo-btn {
        min-height: 40px;
        border-radius: 12px;
        padding: 9px 14px;
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

    .eo-btn-primary {
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        box-shadow: 0 10px 22px rgba(75, 0, 232, .16);
    }

    .eo-btn-light {
        background: #fff;
        color: var(--orb-text);
        border-color: var(--orb-border);
    }

    .eo-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .eo-filter-inside {
        padding: 14px 16px;
        border-bottom: 1px solid var(--orb-border);
        background: #FCFCFD;
    }

    .eo-filter-grid {
        display: grid;
        grid-template-columns: 1.8fr 1fr 1fr auto;
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
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        background: #F9FAFB !important;
        color: var(--orb-text) !important;
        font-size: 13px;
        font-weight: 700;
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

    .eo-toolbar-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: nowrap;
        min-width: 0;
    }

    #employeeLengthBox {
        display: flex;
        align-items: center;
        min-width: max-content;
    }

    #employeeExportButtons {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    #employeeDocTable {
        width: 100% !important;
        margin: 0 !important;
    }

    #employeeDocTable thead th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .45px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--orb-border);
        white-space: nowrap;
    }

    #employeeDocTable tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #F1F3F8;
        vertical-align: middle;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 650;
    }

    #employeeDocTable tbody tr:hover {
        background: #FCFAFF;
    }

    .eo-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 220px;
    }

    .eo-avatar {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--orb-primary);
        font-size: 14px;
        font-weight: 900;
        background: #F4F2FF;
        border: 1px solid #EEE7FF;
        flex: 0 0 auto;
        overflow: hidden;
    }

    .eo-name {
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 900;
    }

    .eo-meta {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 700;
        margin-top: 2px;
    }

    .eo-docs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .eo-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
        text-transform: uppercase;
    }

    .eo-pill-default {
        color: #667085;
        background: #F2F4F7;
    }

    .eo-pill-active {
        color: #12B76A;
        background: rgba(18, 183, 106, .10);
    }

    .eo-pill-warning {
        color: #B54708;
        background: #FFF7E8;
    }

    .eo-pill-danger {
        color: #EC4E74;
        background: rgba(236, 78, 116, .10);
    }

    .eo-dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
    }

    .eo-actions-cell {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .eo-icon-btn {
        min-width: 34px;
        height: 34px;
        border: 0;
        border-radius: 11px;
        background: #F8FAFC;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: .18s ease;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none !important;
    }

    .eo-icon-btn:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .eo-icon-view:hover {
        background: var(--orb-primary);
    }

    .verify-switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
        margin: 0;
    }

    .verify-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .verify-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #E4E7EC;
        border-radius: 999px;
        transition: .2s;
    }

    .verify-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .2s;
        box-shadow: 0 3px 8px rgba(16, 24, 40, .18);
    }

    .verify-switch input:checked+.verify-slider {
        background: #12B76A;
    }

    .verify-switch input:checked+.verify-slider:before {
        transform: translateX(24px);
    }

    .dataTables_filter {
        display: none;
    }

    .dataTables_length {
        padding: 0 !important;
        margin: 0 !important;
    }

    .dataTables_length label,
    .dataTables_info {
        margin: 0 !important;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap !important;
    }

    #employeeLengthBox .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px;
        white-space: nowrap !important;
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
        font-weight: 900 !important;
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

    .dataTables_paginate {
        display: flex;
        justify-content: flex-end;
    }

    .page-link {
        border-radius: 10px !important;
        margin: 0 3px;
        border: 1px solid var(--orb-border);
        color: var(--orb-primary);
        font-weight: 800;
    }

    .page-item.active .page-link {
        background: var(--orb-primary);
        border-color: var(--orb-primary);
    }

    @media(max-width:1100px) {
        .eo-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media(max-width:991px) {
        .eo-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .eo-table-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }

        #employeeExportButtons {
            justify-content: flex-start;
        }

        .eo-table-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        #employeePaginationBox {
            width: 100%;
            overflow-x: auto;
        }
    }

    @media(max-width:576px) {
        .eo-page {
            padding: 12px 8px 22px;
        }

        .eo-header {
            border-radius: 16px;
            padding: 14px;
        }

        .eo-title {
            font-size: 21px;
        }

        .eo-subtitle {
            font-size: 12px;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr;
        }

        .eo-btn {
            width: 100%;
        }

        .eo-filter-inside,
        .eo-table-toolbar,
        .eo-table-footer {
            padding: 12px;
        }

        #employeeLengthBox,
        #employeeExportButtons,
        #employeeInfoBox,
        #employeePaginationBox {
            width: 100%;
        }

        #employeeExportButtons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .dt-buttons,
        .dt-buttons .btn {
            width: 100%;
        }

        .dt-buttons {
            display: contents;
        }

        .dt-buttons .btn {
            justify-content: center;
            padding: 8px 6px !important;
            font-size: 11px !important;
        }

        .dataTables_paginate {
            justify-content: flex-start;
            overflow-x: auto;
            max-width: 100%;
            padding-bottom: 4px;
        }
    }
</style>

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header">
            <div>
                <h1 class="eo-title">Employee Document Verifications</h1>
                <!-- <p class="eo-subtitle">Employee ek baar show hoga, details ke andar sabhi documents dikhenge.</p> -->
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        <div class="eo-card">
            <form method="GET" action="{{ route('hrms.documents.hr.index') }}" id="docFilterForm">
                <div class="eo-filter-inside">
                    <div class="eo-filter-grid">
                        <div class="eo-field">
                            <label>Search</label>
                            <input type="text"
                                name="employee"
                                id="filterSearch"
                                value="{{ request('employee') }}"
                                class="eo-control"
                                placeholder="Search employee, code, email...">
                        </div>

                        <!-- <div class="eo-field">
                            <label>Document Type</label>
                            <select name="document_type_id" id="filterDocumentType" class="eo-control">
                                <option value="">All Document Types</option>
                                @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}" {{ request('document_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div> -->

                        <div class="eo-field">
                            <label>Status</label>
                            <select name="status" id="filterStatus" class="eo-control">
                                <option value="">Pending Employees</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            </select>
                        </div>

                        <div class="eo-field">
                            <label>&nbsp;</label>
                            <a href="{{ route('hrms.documents.hr.index') }}" class="eo-btn eo-btn-light">
                                <i class="fas fa-undo"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="eo-table-toolbar">
                <div class="eo-toolbar-left">
                    <div id="employeeLengthBox"></div>
                </div>
                <div id="employeeExportButtons"></div>
            </div>

            <div class="table-responsive">
                <table id="employeeDocTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Documents</th>
                            <th>Status</th>
                            <th class="text-center">Verify All</th>
                            <th width="150" class="text-center">Details</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td>
                                <div class="eo-emp">
                                    <div class="eo-avatar">
                                        {{ strtoupper(substr($employee->user->name ?? 'E', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="eo-name">{{ $employee->user->name ?? '-' }}</div>
                                        <div class="eo-meta">
                                            {{ $employee->employee_code ?? '-' }}
                                            @if($employee->user?->email)
                                            • {{ $employee->user->email }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="eo-docs">
                                    <span class="eo-pill eo-pill-default">Total: {{ $employee->doc_total }}</span>
                                    <span class="eo-pill eo-pill-active">Verified: {{ $employee->doc_verified }}</span>
                                    <span class="eo-pill eo-pill-warning">Pending: {{ $employee->doc_pending }}</span>
                                    <span class="eo-pill eo-pill-danger">Rejected: {{ $employee->doc_rejected }}</span>
                                </div>
                            </td>

                            <td>
                                @if($employee->doc_status === 'verified')
                                <span class="eo-pill eo-pill-active">
                                    <span class="eo-dot"></span> Verified
                                </span>
                                @else
                                <span class="eo-pill eo-pill-warning">
                                    <span class="eo-dot"></span> Pending
                                </span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($employee->doc_status !== 'verified')
                                <form action="{{ route('hrms.documents.hr.verify_employee', $employee->id) }}"
                                    method="POST"
                                    class="verify-all-form">
                                    @csrf
                                    <label class="verify-switch" title="Verify all documents">
                                        <input type="checkbox" class="verify-all-toggle">
                                        <span class="verify-slider"></span>
                                    </label>
                                </form>
                                @else
                                <span class="eo-pill eo-pill-active">
                                    <span class="eo-dot"></span> Done
                                </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('hrms.documents.hr.show', $employee->user_id) }}"
                                    class="eo-icon-btn eo-icon-view">
                                    <i class="fas fa-eye mr-1"></i> Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        function cleanExportText(data) {
            return $('<div>').html(data).text().replace(/\s+/g, ' ').trim();
        }

        let table = $('#employeeDocTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            order: [
                [0, 'asc']
            ],
            dom: "<'d-none'lB>" +
                "<'row'<'col-12'tr>>" +
                "<'d-none'i p>",
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    title: 'Employee Document Verifications',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2],
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
                    title: 'Employee Document Verifications',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2],
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
                    title: 'Employee Document Verifications',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    title: 'Employee Document Verifications',
                    className: 'btn btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                }
            ],
            language: {
                emptyTable: 'No pending employee documents found',
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

            searchTimer = setTimeout(function() {
                $('#docFilterForm').submit();
            }, 500);
        });

        $('#filterDocumentType, #filterStatus').on('change', function() {
            $('#docFilterForm').submit();
        });

        $('.verify-all-toggle').on('change', function() {
            const checkbox = this;
            const form = $(checkbox).closest('form');

            if (checkbox.checked) {
                if (confirm('Is employee ke all documents verify ho jayenge aur pending list se remove ho jayega. Continue?')) {
                    form.submit();
                } else {
                    checkbox.checked = false;
                }
            }
        });
    });
</script>
@endsection