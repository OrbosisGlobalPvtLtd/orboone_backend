@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Monthly Attendance Report')

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
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .att-page {
        min-height: calc(100vh - 90px);
        padding: 18px 12px 35px;
        background: var(--orb-bg);
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    .att-header {
        padding: 22px;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #fff, #f8f5ff);
        border: 1px solid var(--orb-border);
        border-radius: 26px;
        box-shadow: var(--orb-shadow);
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
    }

    .att-title {
        font-size: 26px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
    }

    .att-subtitle {
        font-size: 13px;
        color: var(--orb-muted);
        margin-top: 6px;
    }

    .att-btn {
        border: 0;
        border-radius: 14px;
        padding: 10px 16px;
        font-weight: 900;
        display: inline-flex;
        gap: 8px;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
    }

    .att-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    .att-btn-light:hover {
        background: #F9F5FF;
        color: var(--orb-primary);
    }

    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .att-kpi {
        padding: 18px;
        border-radius: 22px;
        background: #fff;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .att-kpi::after {
        content: "";
        position: absolute;
        right: -35px;
        top: -35px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg,
                rgba(75, 0, 232, .10),
                rgba(134, 0, 238, .03));
    }

    .att-kpi-label {
        font-size: 11px;
        color: var(--orb-muted);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 8px;
    }

    .att-kpi-value {
        font-size: 28px;
        font-weight: 950;
        color: var(--orb-text);
        line-height: 1;
    }

    .att-filter-wrap {
        padding: 18px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #fff, #fafbff);
    }

    .att-filter-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .att-filter-title {
        font-size: 15px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-filter-title i {
        color: var(--orb-primary);
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .att-filter-wrap label {
        font-size: 10px;
        font-weight: 950;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 5px;
    }

    .att-filter-wrap .form-control {
        border-radius: 13px;
        border: 1px solid #E4E7EC;
        height: 42px;
        font-size: 13px;
    }

    .att-filter-wrap .form-control:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .12);
    }

    .att-table-wrap {
        padding: 16px;
    }

    .att-table {
        width: 100% !important;
        min-width: 1150px;
        border-collapse: collapse !important;
    }

    .att-table thead th {
        background: #F8FAFC;
        color: #475467;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 14px !important;
        border-top: 1px solid #EAECF0 !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
    }

    .att-table tbody td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 14px !important;
        vertical-align: middle;
    }

    .att-table tbody tr {
        transition: .2s ease;
    }

    .att-table tbody tr:hover td {
        background: #FAF8FF;
    }

    .att-emp-name {
        font-size: 14px;
        font-weight: 900;
        color: var(--orb-text);
    }

    .att-emp-sub {
        font-size: 12px;
        color: var(--orb-muted);
        margin-top: 2px;
    }

    .att-hours {
        font-weight: 900;
        color: var(--orb-primary);
    }

    .dataTables_wrapper .row:first-child {
        margin-bottom: 12px;
    }

    .dt-buttons {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
    }

    .export-btn {
        border-radius: 12px !important;
        padding: 8px 14px !important;
        background: #fff !important;
        color: #344054 !important;
        border: 1px solid #E4E7EC !important;
        font-size: 12px !important;
        font-weight: 850 !important;
    }

    .export-btn:hover {
        background: #F9F5FF !important;
        color: #4B00E8 !important;
        border-color: #D9CCFF !important;
    }

    .export-btn i {
        margin-right: 6px;
    }

    .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: hidden !important;
    }

    .dataTables_scrollHead {
        overflow: hidden !important;
    }

    .dataTables_paginate .paginate_button {
        border-radius: 10px !important;
    }

    @media(max-width:1100px) {
        .att-filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .att-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media(max-width:768px) {

        .att-page {
            padding: 12px 8px 25px;
        }

        .att-header {
            padding: 18px;
        }

        .att-title {
            font-size: 22px;
        }

        .att-filter-grid {
            grid-template-columns: 1fr;
        }

        .att-filter-head {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="att-page">

    <div class="att-container">

        <div class="att-header">

            <div>
                <h2 class="att-title">
                    Monthly Attendance Report
                </h2>

                <div class="att-subtitle">
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                    employee-wise attendance summary
                </div>
            </div>

            <div>
                <a href="{{ route('attendances.export-pdf', request()->query() + ['month' => $month, 'year' => $year]) }}"
                    class="att-btn att-btn-light">

                    <i class="fas fa-file-pdf text-danger"></i>
                    Export Monthly Report
                </a>
            </div>

        </div>

        <div class="row mb-3">

            @foreach([

            ['label' => 'Present', 'value' => $summary['present'] ?? 0],
            ['label' => 'Absent', 'value' => $summary['absent'] ?? 0],
            ['label' => 'Half Day', 'value' => $summary['half_day'] ?? 0],
            ['label' => 'Leave', 'value' => $summary['leave'] ?? 0],
            ['label' => 'Week Off', 'value' => $summary['week_off'] ?? 0],
            ['label' => 'Late', 'value' => $summary['late'] ?? 0],
            ['label' => 'Early Out', 'value' => $summary['early_out'] ?? 0],
            ['label' => 'Pending HR', 'value' => $summary['pending_hr'] ?? 0],
            ['label' => 'Total Hours', 'value' => number_format($summary['total_hours'] ?? 0,1).'h'],

            ] as $card)

            <div class="col-xl-3 col-lg-4 col-md-6 mb-3">

                <div class="att-kpi">

                    <div class="att-kpi-label">
                        {{ $card['label'] }}
                    </div>

                    <div class="att-kpi-value">
                        {{ $card['value'] }}
                    </div>

                </div>

            </div>

            @endforeach

        </div>

        <div class="att-card">

            <div class="att-filter-wrap">

                <div class="att-filter-head">

                    <h5 class="att-filter-title">
                        <i class="fas fa-filter"></i>
                        Attendance Filters
                    </h5>

                    <a href="{{ route('attendances.monthly-report') }}"
                        class="att-btn att-btn-light">

                        <i class="fas fa-undo"></i>
                        Reset
                    </a>

                </div>

                <form method="GET"
                    action="{{ route('attendances.monthly-report') }}"
                    id="monthlyAttendanceFilterForm">

                    <div class="att-filter-grid">

                        <div>
                            <label>Month</label>

                            <select name="month"
                                class="form-control auto-filter">

                                @for($m = 1; $m <= 12; $m++)

                                    <option value="{{ $m }}"
                                    {{ (int)$month === $m ? 'selected' : '' }}>

                                    {{ \Carbon\Carbon::create(null,$m,1)->format('F') }}

                                    </option>

                                    @endfor

                            </select>
                        </div>

                        <div>
                            <label>Year</label>

                            <input type="number"
                                name="year"
                                class="form-control auto-filter"
                                value="{{ $year }}">
                        </div>

                        <div>
                            <label>Employee</label>

                            <select name="employee_id"
                                class="form-control auto-filter">

                                <option value="">
                                    All Employees
                                </option>

                                @foreach($employees as $emp)

                                @php
                                $employeeId = optional($emp->employee)->id;
                                @endphp

                                @if($employeeId)

                                <option value="{{ $employeeId }}"
                                    {{ request('employee_id') == $employeeId ? 'selected' : '' }}>

                                    {{ $emp->name }}

                                </option>

                                @endif

                                @endforeach

                            </select>
                        </div>

                        <div>
                            <label>Department</label>

                            <select name="department_id"
                                class="form-control auto-filter">

                                <option value="">
                                    All Departments
                                </option>

                                @foreach($departments as $department)

                                <option value="{{ $department->id }}"
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>

                                    {{ $department->name }}

                                </option>

                                @endforeach

                            </select>
                        </div>

                    </div>

                </form>

            </div>

            <div class="att-table-wrap">

                <div style="width:100%;overflow-x:auto;">

                    <table class="att-table table table-bordered"
                        id="monthlyAttendanceDataTable">

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

                                    <div class="att-emp-name">
                                        {{ $row['employee_name'] }}
                                    </div>

                                    <div class="att-emp-sub">
                                        {{ $row['department_name'] }}
                                    </div>

                                </td>

                                <td>
                                    {{ $row['employee_code'] }}
                                </td>

                                <td>{{ $row['present'] }}</td>

                                <td>{{ $row['absent'] }}</td>

                                <td>{{ $row['half_day'] }}</td>

                                <td>{{ $row['leave'] }}</td>

                                <td>{{ $row['week_off'] }}</td>

                                <td>{{ $row['late'] }}</td>

                                <td>{{ $row['early_out'] }}</td>

                                <td>
                                    <span class="att-hours">
                                        {{ number_format($row['total_hours'],1) }}h
                                    </span>
                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td>—</td>
                                <td>—</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0h</td>
                            </tr>

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
    document.addEventListener('DOMContentLoaded', function() {

        const form = document.getElementById('monthlyAttendanceFilterForm');

        document.querySelectorAll('.auto-filter').forEach(function(item) {

            item.addEventListener('change', function() {

                if (form) {
                    form.submit();
                }

            });

        });

        if ($.fn.DataTable.isDataTable('#monthlyAttendanceDataTable')) {
            $('#monthlyAttendanceDataTable').DataTable().destroy();
        }

        $('#monthlyAttendanceDataTable').DataTable({

            destroy: true,

            pageLength: 25,

            ordering: true,

            responsive: false,

            autoWidth: false,

            searching: false,

            paging: true,

            info: true,

            scrollX: true,

            dom: "<'row align-items-center mb-3'<'col-md-4'l><'col-md-8 text-right'B>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>",

            buttons: [

                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'export-btn'
                },

                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'export-btn'
                },

                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Orbosis HRMS Monthly Attendance Report'
                },

                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'export-btn',
                    title: 'Orbosis HRMS Monthly Attendance Report'
                }

            ],

            language: {

                emptyTable: 'No monthly attendance records found.',

                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }

            }

        });

    });
</script>

@endsection