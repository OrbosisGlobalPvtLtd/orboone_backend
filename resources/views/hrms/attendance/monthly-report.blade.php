@extends('layouts.panel', ['active' => 'attendances'])
@section('page_title', 'Attendances')
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
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 14px 35px rgba(16,24,40,.07);
}

.att-page{min-height:calc(100vh - 90px);padding:18px 12px 35px;background:var(--orb-bg);}
.att-container{max-width:1480px;margin:0 auto;}
.att-card{background:#fff;border:1px solid var(--orb-border);border-radius:24px;box-shadow:var(--orb-shadow);overflow:hidden;}

.att-header{
    padding:22px;
    margin-bottom:18px;
    background:linear-gradient(135deg,#fff,#f8f5ff);
    border:1px solid var(--orb-border);
    border-radius:26px;
    box-shadow:var(--orb-shadow);
    display:flex;
    justify-content:space-between;
    gap:16px;
    align-items:center;
}

.att-title{font-size:26px;font-weight:950;color:var(--orb-text);margin:0;}
.att-subtitle{font-size:13px;color:var(--orb-muted);margin:5px 0 0;}

.att-btn{
    border:0;
    border-radius:14px;
    padding:10px 16px;
    font-weight:900;
    display:inline-flex;
    gap:8px;
    align-items:center;
    justify-content:center;
    text-decoration:none!important;
}

.att-btn-light{background:#fff;color:var(--orb-text);border:1px solid var(--orb-border);}

.att-kpi{
    padding:18px;
    border-radius:22px;
    background:#fff;
    border:1px solid var(--orb-border);
    box-shadow:var(--orb-shadow);
    position:relative;
    overflow:hidden;
}

.att-kpi:after{
    content:"";
    position:absolute;
    right:-30px;
    top:-30px;
    width:95px;
    height:95px;
    border-radius:50%;
    background:linear-gradient(135deg,rgba(75,0,232,.12),rgba(134,0,238,.05));
}

.att-kpi span{
    font-size:11px;
    color:var(--orb-muted);
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.att-kpi h3{
    font-size:25px;
    font-weight:950;
    color:var(--orb-text);
    margin:5px 0 0;
}

.att-filter-wrap{
    padding:16px 18px;
    background:linear-gradient(180deg,#fff,#fafbff);
    border-bottom:1px solid var(--orb-border);
}

.att-filter-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}

.att-filter-title{
    font-size:15px;
    font-weight:950;
    color:var(--orb-text);
    margin:0;
    display:flex;
    align-items:center;
    gap:9px;
}

.att-filter-title i{color:var(--orb-primary);}

.att-filter-grid{
    display:grid;
    grid-template-columns:1fr 1fr 1.5fr 1.5fr;
    gap:10px;
    align-items:end;
}

.att-filter-wrap label{
    font-size:10px;
    font-weight:950;
    color:#667085;
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:5px;
}

.att-filter-wrap .form-control{
    border-radius:13px;
    border:1px solid #E4E7EC;
    height:42px;
    font-size:13px;
}

.att-filter-wrap .form-control:focus{
    border-color:var(--orb-primary);
    box-shadow:0 0 0 .15rem rgba(75,0,232,.12);
}

.att-table-wrap{padding:0 16px 16px;}
.att-table-responsive{width:100%;}

.att-table{
    width:100%!important;
    min-width:1050px;
    table-layout:fixed;
    border-collapse:collapse!important;
}

.att-table thead th{
    background:#F8FAFC;
    color:#475467;
    font-size:11px;
    font-weight:950;
    text-transform:uppercase;
    padding:13px 14px!important;
    border-top:1px solid #EAECF0!important;
    border-bottom:1px solid #EAECF0!important;
    white-space:nowrap;
}

.att-table td{
    background:#fff;
    border-bottom:1px solid #EEF2F6!important;
    padding:14px!important;
    vertical-align:middle;
}

.att-table tbody tr{transition:.2s ease;}
.att-table tbody tr:hover td{background:#FAF8FF;}

.att-table th:nth-child(1), .att-table td:nth-child(1){width:220px;}
.att-table th:nth-child(2), .att-table td:nth-child(2){width:130px;}
.att-table th:nth-child(3), .att-table td:nth-child(3){width:90px;}
.att-table th:nth-child(4), .att-table td:nth-child(4){width:90px;}
.att-table th:nth-child(5), .att-table td:nth-child(5){width:100px;}
.att-table th:nth-child(6), .att-table td:nth-child(6){width:85px;}
.att-table th:nth-child(7), .att-table td:nth-child(7){width:95px;}
.att-table th:nth-child(8), .att-table td:nth-child(8){width:85px;}
.att-table th:nth-child(9), .att-table td:nth-child(9){width:105px;}
.att-table th:nth-child(10), .att-table td:nth-child(10){width:135px;}

.att-emp-name{
    font-weight:900;
    color:var(--orb-text);
    font-size:14px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.att-emp-code{
    font-size:12px;
    color:var(--orb-muted);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.dataTables_wrapper > .row:first-child{
    background:#fff;
    border-bottom:1px solid var(--orb-border);
    padding:14px 16px;
    margin:0 -16px 12px!important;
}

.dataTables_wrapper .dt-buttons .btn{
    border-radius:11px!important;
    font-size:12px!important;
    font-weight:800!important;
    margin-right:6px!important;
    margin-bottom:6px!important;
}

.dataTables_length select{
    border-radius:10px!important;
    padding:4px 22px 4px 8px!important;
}

.dataTables_scrollBody{
    overflow-x:auto!important;
    overflow-y:hidden!important;
    border-bottom:0!important;
}

.dataTables_scrollHead{overflow:hidden!important;}

@media(max-width:1100px){
    .att-filter-grid{grid-template-columns:repeat(2,1fr);}
    .att-header{flex-direction:column;align-items:flex-start;}
}

@media(max-width:768px){
    .att-page{padding:12px 8px 25px;}
    .att-header{padding:18px;}
    .att-title{font-size:22px;}
    .att-filter-head{align-items:flex-start;flex-direction:column;}
    .att-filter-grid{grid-template-columns:1fr;}
}
</style>

<div class="att-page">
    <div class="att-container">

        <div class="att-header">
            <div>
                <h3 class="att-title">Monthly Attendance Report</h3>
                <p class="att-subtitle">
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }} employee-wise attendance summary.
                </p>
            </div>

            <a href="{{ route('attendances.export-pdf', request()->query() + ['month' => $month, 'year' => $year]) }}" class="att-btn att-btn-light">
                <i class="fas fa-file-pdf text-danger"></i> Export Report
            </a>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row mb-3">
            @foreach([
                'Present' => $summary['present'],
                'Absent' => $summary['absent'],
                'Half Day' => $summary['half_day'],
                'Leave' => $summary['leave'],
                'Week Off' => $summary['week_off'],
                'Pending HR' => $summary['pending_hr'],
                'Late' => $summary['late'],
                'Early Out' => $summary['early_out'],
                'Total Hours' => number_format($summary['total_hours'], 1).'h',
            ] as $label => $value)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="att-kpi">
                        <span>{{ $label }}</span>
                        <h3>{{ $value }}</h3>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="att-card">
            <div class="att-filter-wrap">
                <div class="att-filter-head">
                    <h5 class="att-filter-title">
                        <i class="fas fa-calendar-alt"></i> Attendance Report
                    </h5>

                    <a href="{{ route('attendances.monthly-report') }}" class="att-btn att-btn-light">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>

                <form method="GET" action="{{ route('attendances.monthly-report') }}" id="monthlyAttendanceFilterForm">
                    <div class="att-filter-grid">
                        <div>
                            <label>Month</label>
                            <select name="month" class="form-control auto-filter">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label>Year</label>
                            <input type="number" name="year" class="form-control auto-filter" value="{{ $year }}" min="2000" max="2100">
                        </div>

                        <div>
                            <label>Employee</label>
                            <select name="employee_id" class="form-control auto-filter">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    @php $employeeId = optional($emp->employee)->id; @endphp
                                    @if($employeeId)
                                        <option value="{{ $employeeId }}" {{ request('employee_id') == $employeeId ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Department</label>
                            <select name="department_id" class="form-control auto-filter">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="att-table-wrap">
                <div class="att-table-responsive">
                    <table class="att-table table" id="monthlyAttendanceDataTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Emp Code</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Half Day</th>
                                <th>Leave</th>
                                <th>Week Off</th>
                                <th>Late</th>
                                <th>Early Out</th>
                                <th>Total Hours</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($employeeRows as $row)
                                <tr>
                                    <td>
                                        <div class="att-emp-name" title="{{ $row['employee_name'] }}">
                                            {{ $row['employee_name'] }}
                                        </div>
                                        <div class="att-emp-code" title="{{ $row['department_name'] }}">
                                            {{ $row['department_name'] }}
                                        </div>
                                    </td>
                                    <td>{{ $row['employee_code'] }}</td>
                                    <td>{{ $row['present'] }}</td>
                                    <td>{{ $row['absent'] }}</td>
                                    <td>{{ $row['half_day'] }}</td>
                                    <td>{{ $row['leave'] }}</td>
                                    <td>{{ $row['week_off'] }}</td>
                                    <td>{{ $row['late'] }}</td>
                                    <td>{{ $row['early_out'] }}</td>
                                    <td><strong>{{ number_format($row['total_hours'], 1) }}h</strong></td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('monthlyAttendanceFilterForm');
    const filters = document.querySelectorAll('.auto-filter');

    function submitFilterForm() {
        if (form) {
            form.submit();
        }
    }

    filters.forEach(function (filter) {
        filter.addEventListener('change', submitFilterForm);
    });

    $('#monthlyAttendanceDataTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        ordering: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        paging: true,
        info: true,
        searching: false,
        dom:
            "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
            "<'row'<'col-md-12'tr>>" +
            "<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>",
        buttons: [
            {
                extend: 'csvHtml5',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-light border',
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-light border',
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-light border',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Orbosis HRMS Monthly Attendance Report',
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-light border',
                title: 'Orbosis HRMS Monthly Attendance Report',
            }
        ],
        language: {
            lengthMenu: 'Show _MENU_ entries',
            emptyTable: 'No monthly attendance records found.',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            paginate: {
                previous: 'Prev',
                next: 'Next'
            }
        }
    });
});
</script>
@endsection