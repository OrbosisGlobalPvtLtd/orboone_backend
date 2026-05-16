@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'All Employee Documents')

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
        --orb-card: #fff;
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
        margin-bottom: 14px;
    }

    .eo-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 24px;
        font-weight: 900;
    }

    .eo-subtitle {
        margin: 4px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 600;
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
        grid-template-columns: 1.7fr 1fr auto;
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
    }

    .eo-btn {
        min-height: 40px;
        border-radius: 12px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none !important;
    }

    .eo-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    .eo-table-toolbar,
    .eo-table-footer {
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fff;
        border-bottom: 1px solid var(--orb-border);
    }

    .eo-table-footer {
        border-bottom: 0;
    }

    #employeeLengthBox,
    #employeeExportButtons {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    #employeeDocDirectoryTable {
        width: 100% !important;
        margin: 0 !important;
    }

    #employeeDocDirectoryTable thead th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        padding: 12px 14px;
        border-bottom: 1px solid var(--orb-border);
    }

    #employeeDocDirectoryTable tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #F1F3F8;
        vertical-align: middle;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 650;
    }

    #employeeDocDirectoryTable tbody tr:hover {
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
        font-weight: 900;
        background: #F4F2FF;
        border: 1px solid #EEE7FF;
    }

    .eo-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--orb-text);
    }

    .eo-meta {
        font-size: 11px;
        font-weight: 700;
        color: var(--orb-muted);
        margin-top: 2px;
    }

    .eo-code {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 999px;
        background: #F2F4F7;
        color: #344054;
        font-size: 12px;
        font-weight: 900;
    }

    .eo-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
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

    .eo-pill-warning {
        color: #B54708;
        background: #FFF7E8;
    }

    .eo-pill-danger {
        color: #EC4E74;
        background: rgba(236, 78, 116, .10);
    }

    .eo-pill-muted {
        color: #667085;
        background: #F2F4F7;
    }

    .eo-docs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .eo-icon-btn {
        min-width: 34px;
        height: 34px;
        border-radius: 11px;
        background: #F8FAFC;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none !important;
    }

    .eo-icon-btn:hover {
        color: #fff;
        background: var(--orb-primary);
        text-decoration: none;
    }

    .dataTables_filter {
        display: none;
    }

    .dataTables_length label,
    .dataTables_info {
        margin: 0 !important;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 700;
    }

    #employeeLengthBox .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px;
    }

    #employeeLengthBox .dataTables_length select {
        min-width: 68px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid var(--orb-border);
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
    }

    .dt-buttons .btn:hover {
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
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

    @media(max-width:991px) {
        .eo-filter-grid {
            grid-template-columns: 1fr;
        }

        .eo-table-toolbar,
        .eo-table-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="eo-page">
    <div class="eo-container">
        <div class="eo-header">
            <h1 class="eo-title">Employee Documents Directory</h1>
            <p class="eo-subtitle">Verified tabhi dikhayega jab employee ke sabhi uploaded documents verified honge.</p>
        </div>

        <div class="eo-card">
            <form method="GET" action="{{ route('hrms.documents.employee.index') }}" id="docDirectoryFilterForm">
                <div class="eo-filter-inside">
                    <div class="eo-filter-grid">
                        <div class="eo-field">
                            <label>Search</label>
                            <input type="text" name="search" id="filterSearch" value="{{ request('search') }}" class="eo-control" placeholder="Search name, email, code...">
                        </div>

                        <div class="eo-field">
                            <label>Document Status</label>
                            <select name="status" id="filterStatus" class="eo-control">
                                <option value="">All Status</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>

                        <div class="eo-field">
                            <label>&nbsp;</label>
                            <a href="{{ route('hrms.documents.employee.index') }}" class="eo-btn eo-btn-light">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="eo-table-toolbar">
                <div id="employeeLengthBox"></div>
                <div id="employeeExportButtons"></div>
            </div>

            <div class="table-responsive">
                <table id="employeeDocDirectoryTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <!-- <th>Employee Code</th> -->
                            <th>Email</th>
                            <th>Documents</th>
                            <th>Document Status</th>
                            <th width="150" class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($employees as $emp)
                        <tr>
                            <td>
                                <div class="eo-emp">
                                    <div class="eo-avatar">{{ strtoupper(substr($emp->user->name ?? 'E', 0, 1)) }}</div>
                                    <div>
                                        <div class="eo-name">{{ $emp->user->name ?? '-' }}</div>
                                        <div class="eo-meta">{{ $emp->employee_code ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- <td><span class="eo-code">{{ $emp->employee_code ?? '-' }}</span></td> -->

                            <td>{{ $emp->user->email ?? '-' }}</td>

                            <td>
                                <div class="eo-docs">
                                    <span class="eo-pill eo-pill-muted">Total: {{ $emp->doc_total }}</span>
                                    <span class="eo-pill eo-pill-active">Verify: {{ $emp->doc_verified }}</span>
                                    <span class="eo-pill eo-pill-warning">Pen: {{ $emp->doc_pending }}</span>
                                    <span class="eo-pill eo-pill-danger">Rej: {{ $emp->doc_rejected }}</span>
                                </div>
                            </td>

                            <td>
                                @if($emp->verification_status === 'verified')
                                <span class="eo-pill eo-pill-active"><span class="eo-dot"></span> Verified</span>
                                @else
                                <span class="eo-pill eo-pill-warning"><span class="eo-dot"></span> Pending</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('hrms.documents.employee.show', $emp->id) }}" class="eo-icon-btn">
                                    <i class="fas fa-eye mr-1"></i> View Docs
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No employee documents found.</td>
                        </tr>
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

        let table = $('#employeeDocDirectoryTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            order: [
                [0, 'asc']
            ],
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    title: 'Employee Documents Directory',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
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
                    title: 'Employee Documents Directory',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
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
                    title: 'Employee Documents Directory',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
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
                    title: 'Employee Documents Directory',
                    className: 'btn btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                }
            ],
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
                $('#docDirectoryFilterForm').submit();
            }, 500);
        });

        $('#filterStatus').on('change', function() {
            $('#docDirectoryFilterForm').submit();
        });
    });
</script>
@endsection