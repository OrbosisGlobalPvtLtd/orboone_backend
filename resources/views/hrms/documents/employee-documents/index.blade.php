@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'All Employee Documents')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@include('hrms.documents.partials.styles')
<style>
    /* DataTable Toolbar layout inside the table card */
    .dm-table-toolbar-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 16px 24px !important;
        border-bottom: 1px solid var(--dm-border) !important;
        background: #fff !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    #employeeLengthBox .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--dm-muted) !important;
        white-space: nowrap !important;
    }

    #employeeLengthBox .dataTables_length select {
        width: 70px !important;
        height: 34px !important;
        border-radius: 9px !important;
        border: 1px solid var(--dm-border) !important;
        padding: 4px 8px !important;
        outline: none !important;
        font-weight: 700 !important;
    }

    /* DataTable buttons styling */
    .dt-buttons {
        display: flex !important;
        gap: 6px !important;
    }

    .dt-buttons .btn {
        height: 32px !important;
        padding: 0 12px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border-radius: 9px !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        color: var(--dm-muted) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: none !important;
        transition: all 0.2s ease !important;
    }

    .dt-buttons .btn:hover {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        border-color: var(--dm-primary) !important;
    }

    /* Pagination design styling */
    .dataTables_paginate {
        margin: 0 !important;
    }

    .pagination {
        display: flex !important;
        gap: 4px !important;
        margin: 0 !important;
        list-style: none !important;
    }

    .page-item .page-link {
        height: 32px !important;
        padding: 0 12px !important;
        border-radius: 9px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        color: var(--dm-muted) !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        transition: all 0.2s ease !important;
    }

    .page-item:hover .page-link {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        text-decoration: none !important;
    }

    .page-item.active .page-link {
        background: var(--dm-primary) !important;
        color: #fff !important;
        border-color: var(--dm-primary) !important;
    }

    .page-item.disabled .page-link {
        opacity: 0.5 !important;
        pointer-events: none !important;
    }

    /* Card avatar alignment */
    .dm-avatar-wrapper {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 900 !important;
        font-size: 13px !important;
        border: 1px solid rgba(75, 0, 232, 0.15) !important;
    }

    .dm-table-footer-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 16px 24px !important;
        border-top: 1px solid var(--dm-border) !important;
        background: #fff !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    #employeeInfoBox {
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--dm-muted) !important;
    }
</style>
@endsection

@section('_content')
<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; DOCUMENT MANAGEMENT
            </div>
            <h1>Employee Documents Directory</h1>
            <p>Complete record directory. The "Verified" compliance status will lock once all uploaded documents are approved.</p>
        </div>
    </div>

    <!-- Main Card -->
    <div class="dm-card">
        <!-- Card Header with circular icon -->
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-folder-open"></i></div>
                <div>
                    <h5 class="dm-table-title">Employee Document Listing</h5>
                    <p class="dm-table-subtitle">Filter by search tags or compliance states to verify outstanding employee documents.</p>
                </div>
            </div>
        </div>

        <!-- Filter Row Attached inside card -->
        <form method="GET" action="{{ route('documents.employee.index') }}" id="docDirectoryFilterForm">
            <div class="dm-filter-wrapper">
                <div class="dm-filter-row">
                    <div class="dm-filter-col" style="flex: 2 1 300px;">
                        <label class="dm-filter-label">Search Directory</label>
                        <input type="text" name="search" id="filterSearch" value="{{ request('search') }}" class="dm-filter-control" placeholder="Search by name, email, employee code...">
                    </div>

                    <div class="dm-filter-col">
                        <label class="dm-filter-label">Compliance Status</label>
                        <select name="status" id="filterStatus" class="dm-filter-control">
                            <option value="">All Compliance States</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                        </select>
                    </div>

                    <div class="dm-filter-col" style="flex: 0 0 auto;">
                        <a href="{{ route('documents.employee.index') }}" class="dm-btn dm-btn-dark-light" style="height: 40px; border-radius: 9px;">
                            <i class="fas fa-undo"></i> Reset Filters
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- DataTable Toolbar (Entries and buttons appended here) -->
        <div class="dm-table-toolbar-row">
            <div id="employeeLengthBox"></div>
            <div id="employeeExportButtons"></div>
        </div>

        <!-- Table Listing -->
        <div class="dm-table-wrap">
            <table id="employeeDocDirectoryTable" class="table dm-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Email</th>
                        <th>Documents Status Overview</th>
                        <th>Compliance State</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($employees as $emp)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="dm-avatar-wrapper">
                                    {{ strtoupper(substr($emp->user->name ?? 'E', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--dm-text); font-size: 14px;">{{ $emp->user->name ?? '-' }}</div>
                                    <div style="font-size: 11px; color: var(--dm-muted); font-weight: 700;">{{ $emp->employee_code ?? '-' }}</div>
                                </div>
                            </div>
                        </td>

                        <td><span style="font-weight: 600;">{{ $emp->user->email ?? '-' }}</span></td>

                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="dm-badge dm-badge-secondary" style="font-size: 9px; padding: 2px 8px;">Total: {{ $emp->doc_total }}</span>
                                <span class="dm-badge dm-badge-success" style="font-size: 9px; padding: 2px 8px;">Verify: {{ $emp->doc_verified }}</span>
                                <span class="dm-badge dm-badge-warning" style="font-size: 9px; padding: 2px 8px;">Pen: {{ $emp->doc_pending }}</span>
                                <span class="dm-badge dm-badge-danger" style="font-size: 9px; padding: 2px 8px;">Rej: {{ $emp->doc_rejected }}</span>
                            </div>
                        </td>

                        <td>
                            @if($emp->verification_status === 'verified')
                            <span class="dm-badge dm-badge-success"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                            @else
                            <span class="dm-badge dm-badge-warning"><i class="fas fa-hourglass-half mr-1"></i> Pending</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <a href="{{ route('documents.employee.show', $emp->id) }}" class="dm-action-btn-pill dm-action-btn-primary">
                                <i class="fas fa-eye mr-1"></i> View Docs
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No employee documents found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- DataTable Footer (Pagination & pagination info appended here) -->
        <div class="dm-table-footer-row">
            <div id="employeeInfoBox"></div>
            <div id="employeePaginationBox"></div>
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
                        columns: [0, 1, 2, 3],
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
                        columns: [0, 1, 2, 3],
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
                        columns: [0, 1, 2, 3],
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
                        columns: [0, 1, 2, 3],
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
