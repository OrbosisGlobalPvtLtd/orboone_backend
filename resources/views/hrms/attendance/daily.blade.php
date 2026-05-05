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

.att-filter-actions{display:flex;gap:8px;flex-wrap:wrap;}

.att-filter-grid{
    display:grid;
    grid-template-columns:1.5fr 1fr 1.2fr 1fr 1.4fr;
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

.dataTables_scroll{width:100%;}
.dataTables_scrollBody{overflow-x:auto!important;overflow-y:hidden!important;border-bottom:0!important;}
.dataTables_scrollHead{overflow:hidden!important;}

.att-table{
    width:100%!important;
    min-width:1240px;
    table-layout:fixed;
    border-collapse:collapse!important;
}

.att-table th{
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
.att-table th:nth-child(2), .att-table td:nth-child(2){width:120px;}
.att-table th:nth-child(3), .att-table td:nth-child(3){width:125px;}
.att-table th:nth-child(4), .att-table td:nth-child(4){width:90px;}
.att-table th:nth-child(5), .att-table td:nth-child(5){width:95px;}
.att-table th:nth-child(6), .att-table td:nth-child(6){width:95px;}
.att-table th:nth-child(7), .att-table td:nth-child(7){width:135px;}
.att-table th:nth-child(8), .att-table td:nth-child(8){width:155px;}
.att-table th:nth-child(9), .att-table td:nth-child(9){width:135px;}
.att-table th:nth-child(10), .att-table td:nth-child(10){width:250px;}

.att-avatar{
    width:42px;
    height:42px;
    border-radius:14px;
    background:var(--orb-soft);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:950;
    color:var(--orb-primary);
    flex:0 0 auto;
    box-shadow:inset 0 0 0 1px rgba(75,0,232,.08);
}

.att-emp{display:flex;gap:11px;align-items:center;min-width:0;}
.att-emp-name{font-weight:900;color:var(--orb-text);font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;}
.att-emp-code,.att-small{font-size:12px;color:var(--orb-muted);}
.att-dept{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;}

.att-badge{
    display:inline-flex;
    align-items:center;
    border-radius:999px;
    padding:6px 10px;
    font-size:10px;
    font-weight:950;
    text-transform:uppercase;
    white-space:nowrap;
}

.badge-present{background:#dcfce7;color:#166534}
.badge-absent{background:#fee2e2;color:#991b1b}
.badge-half_day{background:#fef3c7;color:#92400e}
.badge-leave{background:#dbeafe;color:#1e40af}
.badge-week_off{background:#f1f5f9;color:#475569}
.badge-holiday{background:#ede9fe;color:#5b21b6}
.badge-pending_hr{background:#ffedd5;color:#9a3412}
.badge-default{background:#f1f5f9;color:#475569}

.mode-badge{
    padding:6px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:950;
    white-space:nowrap;
}

.mode-wfo{background:#eef2ff;color:#3730a3}
.mode-wfh{background:#ecfeff;color:#155e75}
.mode-default{background:#f1f5f9;color:#475569}

.att-task{
    max-width:230px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.att-empty{
    padding:45px 15px!important;
    text-align:center;
    color:var(--orb-muted);
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

.dataTables_length select{border-radius:10px!important;padding:4px 22px 4px 8px!important;}

@media(max-width:1100px){
    .att-filter-grid{grid-template-columns:repeat(2,1fr);}
    .att-header{flex-direction:column;align-items:flex-start;}
}

@media(max-width:768px){
    .att-page{padding:12px 8px 25px;}
    .att-header{padding:18px;}
    .att-title{font-size:22px;}
    .att-filter-head{align-items:flex-start;flex-direction:column;}
    .att-filter-actions{width:100%;}
    .att-filter-actions .att-btn{flex:1;}
    .att-filter-grid{grid-template-columns:1fr;}
    .dataTables_wrapper > .row:first-child{gap:10px;}
}
</style>

<div class="att-page">
    <div class="att-container">

        <div class="att-header">
            <div>
                <h3 class="att-title">Daily Attendance</h3>
                <p class="att-subtitle">Today by default, with employee, status and work mode filters.</p>
            </div>

            <a href="{{ route('attendances.export-pdf', request()->query() + ['date' => $date]) }}" class="att-btn att-btn-light">
                <i class="fas fa-file-pdf text-danger"></i> Export Report
            </a>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="att-card">
            <div class="att-filter-wrap">
                <div class="att-filter-head">
                    <h5 class="att-filter-title">
                        <i class="fas fa-calendar-day"></i> Today Attendance
                    </h5>

                    <div class="att-filter-actions">
                        <a href="{{ route('attendances.daily') }}" class="att-btn att-btn-light">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ route('attendances.daily') }}" id="dailyAttendanceFilterForm">
                    <div class="att-filter-grid">
                        <div>
                            <label>Search</label>
                            <input type="text" name="search" class="form-control auto-filter-input" value="{{ request('search') }}" placeholder="Name, email, employee code">
                        </div>

                        <div>
                            <label>Date</label>
                            <input type="date" name="date" class="form-control auto-filter" value="{{ $date }}">
                        </div>

                        <div>
                            <label>Status</label>
                            <select name="attendance_type_id" class="form-control auto-filter">
                                <option value="">All Status</option>
                                @foreach($attendanceTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Work Mode</label>
                            <select name="work_mode" class="form-control auto-filter">
                                <option value="">All</option>
                                <option value="wfo" {{ request('work_mode') == 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ request('work_mode') == 'wfh' ? 'selected' : '' }}>WFH</option>
                            </select>
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
                    </div>
                </form>
            </div>

            <div class="att-table-wrap">
                <div class="att-table-responsive">
                    <table class="att-table table" id="dailyAttendanceDataTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Emp Code</th>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                                <th>Total Work</th>
                                <th>Status</th>
                                <th>Flags</th>
                                <th>Task Summary</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($attendances as $attendance)
                                @php
                                    $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                                    $modeCode = strtolower($attendance->work_mode ?? '');
                                    $modeLabel = $modeCode === 'wfh' ? 'WFH' : ($modeCode === 'wfo' ? 'WFO' : '-');
                                    $modeClass = in_array($modeCode, ['wfo', 'wfh']) ? $modeCode : 'default';
                                    $summary = $attendance->workLogs->pluck('work_summary')->filter()->implode(' | ') ?: $attendance->punch_out_note ?: '-';
                                @endphp

                                <tr>
                                    <td>
                                        <div class="att-emp">
                                            <div class="att-avatar">
                                                {{ strtoupper(substr(optional($attendance->user)->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="att-emp-name" title="{{ optional($attendance->user)->name ?? 'N/A' }}">
                                                    {{ optional($attendance->user)->name ?? 'N/A' }}
                                                </div>
                                                <div class="att-emp-code att-dept" title="{{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}">
                                                    {{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</td>

                                    <td>
                                        <strong>
                                            {{ $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-' }}
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="mode-badge mode-{{ $modeClass }}">
                                            {{ $modeLabel }}
                                        </span>
                                    </td>

                                    <td>{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}</td>

                                    <td>{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}</td>

                                    <td><strong>{{ $attendance->net_duration ?? '-' }}</strong></td>

                                    <td>
                                        <span class="att-badge badge-{{ $typeCode }}">
                                            {{ optional($attendance->attendanceType)->name ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($attendance->is_late)
                                            <div class="att-small text-warning font-weight-bold">Late: {{ $attendance->late_minutes ?? 0 }} min</div>
                                        @endif

                                        @if($attendance->is_early_out)
                                            <div class="att-small text-danger font-weight-bold">Early: {{ $attendance->early_out_minutes ?? 0 }} min</div>
                                        @endif

                                        @if($attendance->is_blocked)
                                            <div class="att-small text-danger font-weight-bold">Pending HR</div>
                                        @endif

                                        @if(!$attendance->is_late && !$attendance->is_early_out && !$attendance->is_blocked)
                                            <span class="att-small">Clear</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="att-small att-task" title="{{ $summary }}">
                                            {{ $summary }}
                                        </div>
                                    </td>
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
    const form = document.getElementById('dailyAttendanceFilterForm');
    const filters = document.querySelectorAll('.auto-filter');
    const searchInput = document.querySelector('.auto-filter-input');
    let typingTimer = null;

    function submitFilterForm() {
        if (form) form.submit();
    }

    filters.forEach(function (filter) {
        filter.addEventListener('change', submitFilterForm);
    });

    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(submitFilterForm, 500);
        });

        searchInput.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                submitFilterForm();
            }
        });
    }

    $('#dailyAttendanceDataTable').DataTable({
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
                title: 'Orbosis HRMS Daily Attendance Report',
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-light border',
                title: 'Orbosis HRMS Daily Attendance Report',
            }
        ],
        language: {
            lengthMenu: 'Show _MENU_ entries',
            emptyTable: 'No daily attendance records found.',
            info: 'Showing _START_ to _END_ of _TOTAL_ attendance records',
            paginate: {
                previous: 'Prev',
                next: 'Next'
            }
        }
    });
});
</script>
@endsection