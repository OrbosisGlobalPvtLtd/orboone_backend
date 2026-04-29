@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Employee Onboarding')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')

<style>
:root{
    --orb-primary:#4B00E8;
    --orb-secondary:#8600EE;
    --orb-bg:#F6F7FB;
    --orb-card:#FFFFFF;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 10px 28px rgba(16,24,40,.06);
}

.eo-page{
    min-height:calc(100vh - 90px);
    padding:16px 10px 30px;
    background:var(--orb-bg);
}

.eo-container{
    max-width:1320px;
    margin:0 auto;
}

.eo-header{
    background:#fff;
    border:1px solid var(--orb-border);
    border-radius:20px;
    box-shadow:var(--orb-shadow);
    padding:16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    margin-bottom:14px;
}

.eo-title{
    margin:0;
    color:var(--orb-text);
    font-size:24px;
    font-weight:900;
    letter-spacing:-.4px;
}

.eo-subtitle{
    margin:4px 0 0;
    color:var(--orb-muted);
    font-size:13px;
    font-weight:600;
}

.eo-btn{
    min-height:40px;
    border-radius:12px;
    padding:9px 14px;
    font-size:13px;
    font-weight:800;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    border:1px solid transparent;
    text-decoration:none !important;
    cursor:pointer;
    white-space:nowrap;
}

.eo-btn-primary{
    color:#fff !important;
    background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary));
    box-shadow:0 10px 22px rgba(75,0,232,.16);
}

.eo-btn-light{
    background:#fff;
    color:var(--orb-text);
    border-color:var(--orb-border);
}

.eo-card{
    background:#fff;
    border:1px solid var(--orb-border);
    border-radius:20px;
    box-shadow:var(--orb-shadow);
    overflow:hidden;
}

.eo-filter-inside{
    padding:14px 16px;
    border-bottom:1px solid var(--orb-border);
    background:#FCFCFD;
}

.eo-filter-grid{
    display:grid;
    grid-template-columns:1.6fr 1fr 1fr 1fr auto;
    gap:10px;
    align-items:end;
}

.eo-field label{
    display:block;
    margin:0 0 6px;
    color:var(--orb-muted);
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.4px;
}

.eo-control{
    width:100%;
    height:42px;
    border-radius:12px !important;
    border:1px solid var(--orb-border) !important;
    background:#F9FAFB !important;
    color:var(--orb-text) !important;
    font-size:13px;
    font-weight:700;
    padding:8px 12px;
    outline:none;
}

.eo-control:focus{
    border-color:rgba(75,0,232,.45) !important;
    background:#fff !important;
    box-shadow:0 0 0 4px rgba(75,0,232,.08) !important;
}

.eo-table-toolbar{
    padding:14px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    border-bottom:1px solid var(--orb-border);
    background:#fff;
}

.eo-toolbar-left{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:nowrap;
    min-width:0;
}

#employeeLengthBox{
    display:flex;
    align-items:center;
    min-width:max-content;
}

#employeeExportButtons{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:8px;
    flex-wrap:wrap;
}

#employeesTable{
    width:100% !important;
    margin:0 !important;
}

#employeesTable thead th{
    background:#F8FAFC;
    color:#667085;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.45px;
    padding:12px 14px;
    border-bottom:1px solid var(--orb-border);
    white-space:nowrap;
}

#employeesTable tbody td{
    padding:12px 14px;
    border-bottom:1px solid #F1F3F8;
    vertical-align:middle;
    color:var(--orb-text);
    font-size:13px;
    font-weight:650;
}

#employeesTable tbody tr:hover{
    background:#FCFAFF;
}

.eo-emp{
    display:flex;
    align-items:center;
    gap:10px;
    min-width:220px;
}

.eo-avatar{
    width:38px;
    height:38px;
    border-radius:13px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--orb-primary);
    font-size:14px;
    font-weight:900;
    background:#F4F2FF;
    border:1px solid #EEE7FF;
    flex:0 0 auto;
}

.eo-name{
    color:var(--orb-text);
    font-size:13px;
    font-weight:900;
}

.eo-meta{
    color:var(--orb-muted);
    font-size:11px;
    font-weight:700;
    margin-top:2px;
}

.eo-code{
    display:inline-flex;
    padding:6px 9px;
    border-radius:10px;
    background:#F4F2FF;
    color:var(--orb-primary);
    font-size:12px;
    font-weight:900;
    white-space:nowrap;
}

.eo-pill{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 9px;
    border-radius:999px;
    font-size:11px;
    font-weight:900;
    white-space:nowrap;
    text-transform:uppercase;
}

.eo-pill-active{color:#12B76A;background:rgba(18,183,106,.10);}
.eo-pill-resigned{color:#F79009;background:rgba(247,144,9,.12);}
.eo-pill-terminated{color:#F04438;background:rgba(240,68,56,.10);}
.eo-pill-default{color:#667085;background:#F2F4F7;}
.eo-pill-wfh{color:#06AED4;background:rgba(6,174,212,.10);}
.eo-pill-wfo{color:var(--orb-primary);background:rgba(75,0,232,.08);}
.eo-pill-hybrid{color:#D400D5;background:rgba(212,0,213,.08);}

.eo-dot{
    width:6px;
    height:6px;
    border-radius:999px;
    background:currentColor;
}

.eo-actions-cell{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:6px;
    white-space:nowrap;
}

.eo-icon-btn{
    width:34px;
    height:34px;
    border:0;
    border-radius:11px;
    background:#F8FAFC;
    color:#667085;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    transition:.18s ease;
}

.eo-icon-btn:hover{
    color:#fff;
    text-decoration:none;
    transform:translateY(-1px);
}

.eo-icon-profile:hover{background:#12B76A;}
.eo-icon-view:hover{background:var(--orb-primary);}
.eo-icon-edit:hover{background:var(--orb-secondary);}
.eo-icon-delete:hover{background:#EC4E74;}

.dataTables_wrapper{
    padding:0;
}

.dataTables_filter{
    display:none;
}

.dataTables_length{
    padding:0 !important;
    margin:0 !important;
}

.dataTables_length label,
.dataTables_info{
    margin:0 !important;
    color:var(--orb-muted);
    font-size:12px;
    font-weight:700;
    white-space:nowrap !important;
}

#employeeLengthBox .dataTables_length label{
    display:flex !important;
    align-items:center !important;
    gap:6px;
    white-space:nowrap !important;
}

#employeeLengthBox .dataTables_length select{
    width:auto !important;
    min-width:68px;
    height:34px;
    margin:0 4px !important;
    border-radius:10px;
    border:1px solid var(--orb-border);
    padding:4px 8px;
}

.dt-buttons{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.dt-buttons .btn{
    border-radius:11px !important;
    border:1px solid var(--orb-border) !important;
    background:#fff !important;
    color:var(--orb-text) !important;
    font-size:12px !important;
    font-weight:900 !important;
    padding:8px 12px !important;
    box-shadow:0 6px 16px rgba(16,24,40,.045) !important;
}

.dt-buttons .btn:hover{
    color:#fff !important;
    border-color:var(--orb-primary) !important;
    background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary)) !important;
}

.eo-table-footer{
    padding:14px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    background:#fff;
}

.dataTables_paginate{
    display:flex;
    justify-content:flex-end;
}

.page-link{
    border-radius:10px !important;
    margin:0 3px;
    border:1px solid var(--orb-border);
    color:var(--orb-primary);
    font-weight:800;
}

.page-item.active .page-link{
    background:var(--orb-primary);
    border-color:var(--orb-primary);
}

@media(max-width:1100px){
    .eo-filter-grid{
        grid-template-columns:1fr 1fr 1fr;
    }
}

@media(max-width:991px){
    .eo-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .eo-header .eo-btn{
        align-self:flex-start;
    }

    .eo-filter-grid{
        grid-template-columns:1fr 1fr;
    }

    .eo-table-toolbar{
        flex-direction:column;
        align-items:flex-start;
    }

    #employeeExportButtons{
        justify-content:flex-start;
    }

    .eo-table-footer{
        flex-direction:column;
        align-items:flex-start;
    }

    #employeePaginationBox{
        width:100%;
        overflow-x:auto;
    }
}

@media(max-width:576px){
    .eo-page{
        padding:12px 8px 22px;
    }

    .eo-header{
        border-radius:16px;
        padding:14px;
    }

    .eo-title{
        font-size:21px;
    }

    .eo-subtitle{
        font-size:12px;
    }

    .eo-filter-grid{
        grid-template-columns:1fr;
    }

    .eo-btn{
        width:100%;
    }

    .eo-filter-inside,
    .eo-table-toolbar,
    .eo-table-footer{
        padding:12px;
    }

    .eo-toolbar-left{
        width:100%;
        flex-wrap:wrap;
    }

    #employeeLengthBox{
        width:100%;
    }

    #employeeExportButtons{
        width:100%;
        display:grid;
        grid-template-columns:1fr 1fr 1fr;
        gap:8px;
    }

    .dt-buttons,
    .dt-buttons .btn{
        width:100%;
    }

    .dt-buttons{
        display:contents;
    }

    .dt-buttons .btn{
        justify-content:center;
        padding:8px 6px !important;
        font-size:11px !important;
    }

    #employeeInfoBox{
        width:100%;
    }

    #employeePaginationBox{
        width:100%;
    }

    .dataTables_paginate{
        justify-content:flex-start;
        overflow-x:auto;
        max-width:100%;
        padding-bottom:4px;
    }
}
</style>

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header">
            <div>
                <h1 class="eo-title">Employee Onboarding</h1>
                <p class="eo-subtitle">Manage employee records, onboarding status and work mode.</p>
            </div>

            @if(Route::has('employees-data.create'))
                <a href="{{ route('employees-data.create') }}" class="eo-btn eo-btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Add Employee
                </a>
            @endif
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
                        <input type="text" id="filterSearch" class="eo-control" placeholder="Search employee...">
                    </div>

                    <div class="eo-field">
                        <label>Department</label>
                        <select id="filterDepartment" class="eo-control">
                            <option value="">All Departments</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Status</label>
                        <select id="filterStatus" class="eo-control">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="resigned">Resigned</option>
                            <option value="terminated">Terminated</option>
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
                        <label>&nbsp;</label>
                        <button type="button" id="resetFilter" class="eo-btn eo-btn-light">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="eo-table-toolbar">
                <div class="eo-toolbar-left">
                    <div id="employeeLengthBox"></div>
                </div>

                <div id="employeeExportButtons"></div>
            </div>

            <div class="table-responsive">
                <table id="employeesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Contact</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Type</th>
                            <th>Mode</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
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
$(document).ready(function () {
    function cleanExportText(data) {
        return $('<div>').html(data).text().replace(/\s+/g, ' ').trim();
    }

    let table = $('#employeesTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: "{{ route('employees-data') }}",
            type: "GET",
            data: function (d) {
                d.ajax_table = 1;
                d.department = $('#filterDepartment').val();
                d.status = $('#filterStatus').val();
                d.work_mode = $('#filterWorkMode').val();
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Employee data load nahi ho raha. Console check karo.');
            }
        },
        columns: [
            { data: 'employee', name: 'employee' },
            { data: 'employee_code', name: 'employee_code' },
            { data: 'contact', name: 'contact' },
            { data: 'department', name: 'department' },
            { data: 'designation', name: 'designation' },
            { data: 'employment_type', name: 'employment_type' },
            { data: 'work_mode', name: 'work_mode' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable:false, searchable:false }
        ],
        order: [[0, 'asc']],
        dom:
            "<'d-none'lB>" +
            "<'row'<'col-12'tr>>" +
            "<'d-none'i p>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                title: 'Employee Onboarding Records',
                className: 'btn btn-sm',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7],
                    format: { body: function (data) { return cleanExportText(data); } }
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                title: 'Employee Onboarding Records',
                className: 'btn btn-sm',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7],
                    format: { body: function (data) { return cleanExportText(data); } }
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print',
                title: 'Employee Onboarding Records',
                className: 'btn btn-sm',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7],
                    format: { body: function (data) { return cleanExportText(data); } }
                }
            }
        ],
        language: {
            processing: '<strong>Loading...</strong>',
            emptyTable: 'No employee records found',
            zeroRecords: 'No matching employee found'
        },
        initComplete: function () {
            $('.dataTables_length').appendTo('#employeeLengthBox');
            $('.dt-buttons').appendTo('#employeeExportButtons');
            $('.dataTables_info').appendTo('#employeeInfoBox');
            $('.dataTables_paginate').appendTo('#employeePaginationBox');
        }
    });

    let searchTimer = null;

    $('#filterSearch').on('keyup', function () {
        clearTimeout(searchTimer);
        let value = this.value;

        searchTimer = setTimeout(function () {
            table.search(value).draw();
        }, 300);
    });

    $('#filterDepartment, #filterStatus, #filterWorkMode').on('change', function () {
        table.ajax.reload();
    });

    $('#resetFilter').on('click', function () {
        $('#filterSearch').val('');
        $('#filterDepartment').val('');
        $('#filterStatus').val('');
        $('#filterWorkMode').val('');

        table.search('');
        table.ajax.reload();
    });
});
</script>
@endsection