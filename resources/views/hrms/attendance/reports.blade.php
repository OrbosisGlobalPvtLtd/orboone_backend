@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Attendance Records')

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
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 18px 12px 35px;
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    .att-header {
        background:
            radial-gradient(circle at top right, rgba(75, 0, 232, .12), transparent 26%),
            linear-gradient(135deg, #fff, #F8F5FF);
        border: 1px solid var(--orb-border);
        border-radius: 26px;
        padding: 20px 22px;
        margin-bottom: 16px;
        box-shadow: var(--orb-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .att-title {
        font-size: 28px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
    }

    .att-subtitle {
        font-size: 13px;
        color: var(--orb-muted);
        margin-top: 5px;
        font-weight: 650;
    }

    .att-btn {
        border: 0;
        border-radius: 13px;
        padding: 10px 16px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
        transition: .2s ease;
    }

    .att-btn-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff !important;
    }

    .att-btn-light {
        background: #fff;
        border: 1px solid var(--orb-border);
        color: var(--orb-text) !important;
    }

    .att-btn:hover {
        transform: translateY(-1px);
    }

    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--orb-shadow);
    }

    .att-section-head {
        padding: 20px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #fff, #FAFBFF);
    }

    .att-section-title {
        font-size: 16px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .att-section-title i {
        color: var(--orb-primary);
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
        margin-top: 18px;
    }

    .att-filter-group label {
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        color: #667085;
        margin-bottom: 6px;
        display: block;
        letter-spacing: .04em;
    }

    .att-filter-group .form-control {
        height: 44px;
        border-radius: 14px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        font-weight: 700;
        padding: 0 14px;
        box-shadow: none !important;
    }

    .att-filter-group .form-control:focus {
        border-color: var(--orb-primary);
    }

    .att-table-wrap {
        padding: 18px;
    }

    .att-table-scroll {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        border-radius: 18px;
        border: 1px solid #EEF2F6;
    }

    /* ONLY ONE HORIZONTAL SCROLL */
    .att-table-scroll::-webkit-scrollbar {
        height: 10px;
    }

    .att-table-scroll::-webkit-scrollbar-thumb {
        background: #D0D5DD;
        border-radius: 20px;
    }

    .att-table {
        width: 100%;
        min-width: 1700px;
        border-collapse: separate !important;
        border-spacing: 0;
        margin: 0 !important;
    }

    .att-table thead th {
        background: #F8FAFC;
        color: #475467;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 13px !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .att-table td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 13px !important;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF;
    }

    .att-emp {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .att-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--orb-soft), #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 950;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .08);
        flex-shrink: 0;
    }

    .att-emp-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--orb-text);
    }

    .att-emp-code {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
    }

    .att-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
    }

    .badge-present {
        background: #DCFCE7;
        color: #166534;
    }

    .badge-absent {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-half_day {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-leave {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .badge-week_off {
        background: #F1F5F9;
        color: #475569;
    }

    .badge-punch_blocked {
        background: #FFE4E6;
        color: #BE123C;
    }

    .badge-lwp {
        background: #FEE2E2;
        color: #B42318;
    }

    .badge-default {
        background: #F1F5F9;
        color: #475569;
    }

    .mode-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
    }

    .mode-wfo {
        background: #EEF2FF;
        color: #3730A3;
    }

    .mode-wfh {
        background: #ECFEFF;
        color: #155E75;
    }

    .flag {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 9px;
        font-weight: 950;
        margin: 2px 3px 2px 0;
    }

    .flag-late {
        background: #FFF7ED;
        color: #C2410C;
    }

    .flag-early {
        background: #FEF2F2;
        color: #B42318;
    }

    .flag-blocked {
        background: #FFE4E6;
        color: #BE123C;
    }

    .flag-missed {
        background: #FEF3C7;
        color: #92400E;
    }

    .flag-clear {
        background: #F1F5F9;
        color: #475569;
    }

    .att-action-wrap {
        display: flex;
        justify-content: flex-end;
    }

    .action-dot {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #475467;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: .2s ease;
    }

    .action-dot:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .dropdown-menu.att-action-menu {
        border: 1px solid var(--orb-border);
        border-radius: 15px;
        box-shadow: 0 18px 45px rgba(16, 24, 40, .14);
        padding: 7px;
        min-width: 190px;
    }

    .att-action-menu .dropdown-item {
        border-radius: 11px;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-action-menu .dropdown-item:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .dataTables_wrapper .dataTables_scroll {
        overflow: unset !important;
    }

    .dataTables_wrapper .dataTables_scrollBody {
        overflow: unset !important;
    }

    .dataTables_wrapper .row:first-child {
        margin-bottom: 15px;
    }

    .dataTables_filter {
        display: none !important;
    }

    .dataTables_length select {
        border-radius: 10px !important;
    }

    .dt-buttons .btn {
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        margin-left: 5px !important;
    }

    .page-link {
        border-radius: 10px !important;
    }

    @media(max-width:1200px) {
        .att-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width:768px) {

        .att-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .att-filter-grid {
            grid-template-columns: 1fr;
        }

    }
</style>

<div class="att-page">
    <div class="att-container">

        <div class="att-header">

            <div>
                <h3 class="att-title">Attendance Records</h3>

                <div class="att-subtitle">
                    Complete employee attendance records with filters, shifts, work duration, blocked punch-ins and violation tracking.
                </div>
            </div>

            <div class="d-flex align-items-center" style="gap:10px;">

                <a href="{{ route('attendances.index') }}"
                    class="att-btn att-btn-light">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>

                <a href="{{ route('attendances.export-pdf', request()->query()) }}"
                    class="att-btn att-btn-primary">
                    <i class="fas fa-file-export"></i>
                    Export
                </a>

            </div>

        </div>

        <div class="att-card">

            <div class="att-section-head">

                <h5 class="att-section-title">
                    <i class="fas fa-filter"></i>
                    Attendance Filters
                </h5>

                <form method="GET" action="{{ route('attendances.daily') }}">

                    <div class="att-filter-grid">

                        <div class="att-filter-group">
                            <label>Search</label>
                            <input type="text"
                                name="search"
                                class="form-control filter-auto-submit"
                                placeholder="Employee Name / Code"
                                value="{{ request('search') }}">
                        </div>

                        <div class="att-filter-group">
                            <label>Employee</label>

                            <select name="employee_id"
                                class="form-control filter-auto-submit">

                                <option value="">All Employees</option>

                                @foreach($employees as $emp)

                                <option value="{{ optional($emp->employee)->id }}"
                                    {{ request('employee_id') == optional($emp->employee)->id ? 'selected' : '' }}>

                                    {{ $emp->name }}

                                </option>

                                @endforeach

                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Status</label>

                            <select name="attendance_type_id"
                                class="form-control filter-auto-submit">

                                <option value="">All Status</option>

                                @foreach($attendanceTypes as $type)

                                <option value="{{ $type->id }}"
                                    {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>

                                    {{ $type->name }}

                                </option>

                                @endforeach

                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Shift</label>

                            <select name="attendance_time_id"
                                class="form-control filter-auto-submit">

                                <option value="">All Shifts</option>

                                @foreach($attendanceTimes as $shift)

                                <option value="{{ $shift->id }}"
                                    {{ request('attendance_time_id') == $shift->id ? 'selected' : '' }}>

                                    {{ $shift->name }}

                                </option>

                                @endforeach

                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Work Mode</label>

                            <select name="work_mode"
                                class="form-control filter-auto-submit">

                                <option value="">All</option>

                                <option value="wfo"
                                    {{ request('work_mode') == 'wfo' ? 'selected' : '' }}>
                                    WFO
                                </option>

                                <option value="wfh"
                                    {{ request('work_mode') == 'wfh' ? 'selected' : '' }}>
                                    WFH
                                </option>

                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Flags</label>

                            <select name="flag"
                                class="form-control filter-auto-submit">

                                <option value="">All Records</option>

                                <option value="late"
                                    {{ request('flag') == 'late' ? 'selected' : '' }}>
                                    Late
                                </option>

                                <option value="early_out"
                                    {{ request('flag') == 'early_out' ? 'selected' : '' }}>
                                    Early Logout
                                </option>

                                <option value="blocked"
                                    {{ request('flag') == 'blocked' ? 'selected' : '' }}>
                                    Punch Blocked
                                </option>

                                <option value="missed_punch"
                                    {{ request('flag') == 'missed_punch' ? 'selected' : '' }}>
                                    Missed Punch
                                </option>

                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>From Date</label>

                            <input type="date"
                                name="from_date"
                                class="form-control filter-auto-submit"
                                value="{{ request('from_date') }}">
                        </div>

                        <div class="att-filter-group">
                            <label>To Date</label>

                            <input type="date"
                                name="to_date"
                                class="form-control filter-auto-submit"
                                value="{{ request('to_date') }}">
                        </div>

                        <div class="att-filter-group">
                            <label>Single Date</label>

                            <input type="date"
                                name="date"
                                class="form-control filter-auto-submit"
                                value="{{ request('date') }}">
                        </div>

                        <div class="att-filter-group d-flex align-items-end">

                            <a href="{{ route('attendances.daily') }}"
                                class="att-btn att-btn-light w-100">
                                <i class="fas fa-undo"></i>
                                Reset
                            </a>

                        </div>

                    </div>

                </form>

            </div>

            <div class="att-table-wrap">

                <div class="att-table-scroll">

                    <table class="table att-table" id="attendanceTable">

                        <thead>
                            <tr>

                                <th>Employee</th>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Shift</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                                <th>Target Out</th>
                                <th>Gross Work</th>
                                <th>Net Work</th>
                                <th>Break</th>
                                <th>Status</th>
                                <th>Flags</th>
                                <th class="text-right">Action</th>

                            </tr>
                        </thead>

                        <tbody>

                            @forelse($attendances as $attendance)

                            @php
                            $typeCode = optional($attendance->attendanceType)->code ?? 'default';

                            $modeClass = strtolower($attendance->work_mode ?? '') == 'wfh'
                            ? 'mode-wfh'
                            : 'mode-wfo';
                            @endphp

                            <tr>

                                <td>

                                    <div class="att-emp">

                                        <div class="att-avatar">
                                            {{ strtoupper(substr(optional($attendance->user)->name ?? 'U',0,1)) }}
                                        </div>

                                        <div>

                                            <div class="att-emp-name">
                                                {{ optional($attendance->user)->name ?? '-' }}
                                            </div>

                                            <div class="att-emp-code">
                                                {{ optional($attendance->employee)->employee_code ?? '-' }}
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    {{ optional($attendance->attendance_date)
                                        ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y')
                                        : '-' }}
                                </td>

                                <td>
                                    <span class="mode-badge {{ $modeClass }}">
                                        {{ strtoupper($attendance->work_mode ?? '-') }}
                                    </span>
                                </td>

                                <td>
                                    {{ optional($attendance->attendanceTime)->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $attendance->punch_in_time
                                        ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A')
                                        : '-' }}
                                </td>

                                <td>
                                    {{ $attendance->punch_out_time
                                        ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A')
                                        : '-' }}
                                </td>

                                <td>
                                    {{ $attendance->target_punch_out_time
                                        ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A')
                                        : '-' }}
                                </td>

                                <td>{{ $attendance->gross_duration ?? '-' }}</td>

                                <td>
                                    <strong>{{ $attendance->net_duration ?? '-' }}</strong>
                                </td>

                                <td>
                                    {{ $attendance->break_minutes ?? 0 }}m
                                </td>

                                <td>
                                    <span class="att-badge badge-{{ $typeCode }}">
                                        {{ optional($attendance->attendanceType)->name ?? '-' }}
                                    </span>
                                </td>

                                <td>

                                    @if($attendance->is_late)
                                    <span class="flag flag-late">Late</span>
                                    @endif

                                    @if($attendance->is_early_out)
                                    <span class="flag flag-early">Early</span>
                                    @endif

                                    @if($attendance->is_blocked)
                                    <span class="flag flag-blocked">Blocked</span>
                                    @endif

                                    @if($attendance->missed_punch)
                                    <span class="flag flag-missed">Missed</span>
                                    @endif

                                    @if(
                                    !$attendance->is_late &&
                                    !$attendance->is_early_out &&
                                    !$attendance->is_blocked &&
                                    !$attendance->missed_punch
                                    )
                                    <span class="flag flag-clear">Clear</span>
                                    @endif

                                </td>

                                <td>

                                    <div class="att-action-wrap dropdown">

                                        <button type="button"
                                            class="action-dot"
                                            data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right att-action-menu">

                                            @if($attendance->is_blocked)

                                            <button type="button"
                                                class="dropdown-item"
                                                data-toggle="modal"
                                                data-target="#unlockModal{{ $attendance->id }}">

                                                <i class="fas fa-unlock text-success"></i>
                                                Unlock

                                            </button>

                                            @endif

                                            @if(auth()->user()?->hasRole('super_admin'))

                                            <button type="button"
                                                class="dropdown-item"
                                                data-toggle="modal"
                                                data-target="#editModal{{ $attendance->id }}">

                                                <i class="fas fa-edit text-primary"></i>
                                                Edit Attendance

                                            </button>

                                            @endif

                                        </div>

                                    </div>

                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="13" class="text-center py-5">
                                    No attendance records found.
                                </td>
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
    $(function() {

        $('#attendanceTable').DataTable({

            pageLength: 25,
            ordering: true,
            searching: false,
            responsive: false,
            autoWidth: false,
            scrollX: false,
            paging: true,

            dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>",

            buttons: [

                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-light border'
                },

                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-light border'
                },

                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-light border',
                    orientation: 'landscape',
                    pageSize: 'A4'
                },

                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-light border'
                }

            ]

        });

        // AUTO FILTER SUBMIT

        let filterTimer;

        $('.filter-auto-submit').on('change keyup', function() {

            clearTimeout(filterTimer);

            filterTimer = setTimeout(function() {
                $('form').submit();
            }, 500);

        });

    });
</script>

@endsection
