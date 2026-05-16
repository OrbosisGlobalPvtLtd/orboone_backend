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
        background: radial-gradient(circle at top right, rgba(75, 0, 232, .12), transparent 26%), linear-gradient(135deg, #fff, #F8F5FF);
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
    }

    .att-btn-light {
        background: #fff;
        border: 1px solid var(--orb-border);
        color: var(--orb-text) !important;
    }

    .att-btn-light:hover {
        background: #F9F5FF;
        color: var(--orb-primary) !important;
    }

    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--orb-shadow);
    }

    .att-section-head {
        padding: 18px;
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
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .10) !important;
    }

    .att-table-wrap {
        padding: 16px;
    }

    .att-table {
        width: 100% !important;
        min-width: 1560px;
        border-collapse: separate !important;
        border-spacing: 0;
        margin: 0 !important;
    }

    .att-table thead th {
        background: #F8FAFC !important;
        color: #475467 !important;
        font-size: 10px !important;
        font-weight: 950 !important;
        text-transform: uppercase;
        padding: 13px 12px !important;
        border-top: 1px solid #EAECF0 !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .att-table tbody td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 13px 12px !important;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF !important;
    }

    .att-table th:nth-child(1),
    .att-table td:nth-child(1) {
        width: 65px;
        text-align: center;
    }

    .att-table th:nth-child(2),
    .att-table td:nth-child(2) {
        width: 230px;
    }

    .att-table th:nth-child(3),
    .att-table td:nth-child(3) {
        width: 125px;
    }

    .att-table th:nth-child(4),
    .att-table td:nth-child(4) {
        width: 80px;
    }

    .att-table th:nth-child(5),
    .att-table td:nth-child(5) {
        width: 145px;
    }

    .att-table th:nth-child(6),
    .att-table td:nth-child(6) {
        width: 100px;
    }

    .att-table th:nth-child(7),
    .att-table td:nth-child(7) {
        width: 100px;
    }

    .att-table th:nth-child(8),
    .att-table td:nth-child(8) {
        width: 110px;
    }

    .att-table th:nth-child(9),
    .att-table td:nth-child(9) {
        width: 105px;
    }

    .att-table th:nth-child(10),
    .att-table td:nth-child(10) {
        width: 105px;
    }

    .att-table th:nth-child(11),
    .att-table td:nth-child(11) {
        width: 125px;
    }

    .att-table th:nth-child(12),
    .att-table td:nth-child(12) {
        width: 145px;
    }

    .att-table th:nth-child(13),
    .att-table td:nth-child(13) {
        width: 220px;
    }

    .att-table th:nth-child(14),
    .att-table td:nth-child(14) {
        width: 85px;
        text-align: right;
    }

    .att-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .att-avatar {
        width: 40px;
        height: 40px;
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
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .att-emp-code {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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

    .badge-holiday {
        background: #EDE9FE;
        color: #5B21B6;
    }

    .badge-pending_hr {
        background: #FFEDD5;
        color: #9A3412;
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

    .mode-default {
        background: #F1F5F9;
        color: #475569;
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

    .att-task {
        max-width: 205px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 650;
    }

    .att-action-wrap {
        display: flex;
        justify-content: flex-end;
    }

    .action-dot {
        width: 35px;
        height: 35px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #475467;
        display: flex;
        align-items: center;
        justify-content: center;
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
        min-width: 185px;
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

    /* DataTables controls bahar rahenge, scroll sirf table head+body me hoga */
    .dataTables_wrapper>.row:first-child {
        background: #fff;
        border-bottom: 1px solid var(--orb-border);
        padding: 12px 16px;
        margin: 0 -16px 12px !important;
    }

    .dataTables_wrapper>.row:last-child {
        background: #fff;
        border-top: 1px solid var(--orb-border);
        padding: 12px 16px 0;
        margin: 12px -16px 0 !important;
    }

    .dataTables_scroll {
        border: 1px solid #EEF2F6;
        border-radius: 18px;
        overflow: hidden;
    }

    .dataTables_scrollHead {
        background: #F8FAFC;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        border-bottom: 0 !important;
    }

    .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #D0D5DD;
        border-radius: 20px;
    }

    .dataTables_wrapper .dt-buttons {
        display: flex !important;
        justify-content: flex-end !important;
        gap: 7px;
        flex-wrap: wrap;
    }

    .dataTables_wrapper .dt-buttons .btn {
        border-radius: 11px !important;
        font-size: 12px !important;
        font-weight: 850 !important;
        background: #fff !important;
        color: #344054 !important;
        border: 1px solid #E4E7EC !important;
        padding: 8px 13px !important;
        margin-bottom: 6px !important;
    }

    .dataTables_wrapper .dt-buttons .btn:hover {
        background: #F9F5FF !important;
        color: #4B00E8 !important;
        border-color: #D9CCFF !important;
    }

    .dataTables_length select {
        border-radius: 10px !important;
        padding: 4px 22px 4px 8px !important;
    }

    .dataTables_info {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 700;
    }

    .page-link {
        border-radius: 10px !important;
        margin: 0 2px;
        border-color: var(--orb-border);
        color: var(--orb-primary);
        font-weight: 800;
    }

    @media(max-width:1200px) {
        .att-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width:768px) {
        .att-page {
            padding: 12px 8px 25px;
        }

        .att-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .att-title {
            font-size: 22px;
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
                    Overall employee attendance records with filters, shift timing, work duration and export options.
                </div>
            </div>

            <a href="{{ route('attendances.record') }}" class="att-btn att-btn-light">
                <i class="fas fa-undo"></i> Reset
            </a>
        </div>

        @if(session('status'))
        <div class="alert alert-success" style="border-radius:16px;font-weight:800;">{{ session('status') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger" style="border-radius:16px;font-weight:800;">{{ session('error') }}</div>
        @endif

        <div class="att-card">
            <div class="att-section-head">
                <h5 class="att-section-title">
                    <i class="fas fa-filter"></i> Attendance Filters
                </h5>

                <form method="GET" action="{{ route('attendances.record') }}" id="dailyAttendanceFilterForm">
                    <div class="att-filter-grid">

                        <div class="att-filter-group">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control auto-filter-input"
                                value="{{ request('search') }}"
                                placeholder="Name, email, employee code">
                        </div>

                        <div class="att-filter-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control auto-filter" value="{{ request('date') }}">
                        </div>

                        <div class="att-filter-group">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control auto-filter" value="{{ request('from_date') }}">
                        </div>

                        <div class="att-filter-group">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control auto-filter" value="{{ request('to_date') }}">
                        </div>

                        <div class="att-filter-group">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control auto-filter">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                @php $employeeId = optional($emp->employee)->id ?? $emp->id; @endphp
                                <option value="{{ $employeeId }}" {{ request('employee_id') == $employeeId ? 'selected' : '' }}>
                                    {{ $emp->name ?? optional($emp->user)->name ?? 'Employee' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group">
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

                        <div class="att-filter-group">
                            <label>Shift</label>
                            <select name="attendance_time_id" class="form-control auto-filter">
                                <option value="">All Shifts</option>
                                @foreach($attendanceTimes ?? [] as $shift)
                                <option value="{{ $shift->id }}" {{ request('attendance_time_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Work Mode</label>
                            <select name="work_mode" class="form-control auto-filter">
                                <option value="">All</option>
                                <option value="wfo" {{ request('work_mode') == 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ request('work_mode') == 'wfh' ? 'selected' : '' }}>WFH</option>
                            </select>
                        </div>

                        <div class="att-filter-group">
                            <label>Flags</label>
                            <select name="flag" class="form-control auto-filter">
                                <option value="">All Records</option>
                                <option value="late" {{ request('flag') == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="early_out" {{ request('flag') == 'early_out' ? 'selected' : '' }}>Early Logout</option>
                                <option value="blocked" {{ request('flag') == 'blocked' ? 'selected' : '' }}>Punch Blocked</option>
                                <option value="pending_hr" {{ request('flag') == 'pending_hr' ? 'selected' : '' }}>Pending HR</option>
                                <option value="missed_punch" {{ request('flag') == 'missed_punch' ? 'selected' : '' }}>Missed Punch</option>
                                <option value="clear" {{ request('flag') == 'clear' ? 'selected' : '' }}>Clear</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <div class="att-table-wrap">
                <table class="table att-table" id="dailyAttendanceDataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Target Out</th>
                            <th>Gross</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Flags</th>
                            <th>Task Summary</th>
                            <th class="no-export text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($attendances as $attendance)
                        @php
                        $typeCode = optional($attendance->attendanceType)->code ?? 'default';

                        $modeCode = strtolower($attendance->work_mode ?? '');
                        $modeLabel = $modeCode === 'wfh' ? 'WFH' : ($modeCode === 'wfo' ? 'WFO' : '-');
                        $modeClass = in_array($modeCode, ['wfo','wfh']) ? 'mode-'.$modeCode : 'mode-default';

                        $employeeName = optional($attendance->user)->name
                        ?? optional(optional($attendance->employee)->user)->name
                        ?? 'Employee';

                        $employeeCode = optional($attendance->employee)->employee_code ?? 'N/A';

                        $attDate = $attendance->attendance_date
                        ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y')
                        : '-';

                        $punchIn = $attendance->punch_in_time ?? $attendance->punch_in ?? null;
                        $punchOut = $attendance->punch_out_time ?? $attendance->punch_out ?? null;

                        $gross = $attendance->gross_duration
                        ?? (isset($attendance->gross_work_minutes) ? floor($attendance->gross_work_minutes / 60).'h '.($attendance->gross_work_minutes % 60).'m' : '-');

                        $net = $attendance->net_duration
                        ?? (isset($attendance->working_minutes) ? floor($attendance->working_minutes / 60).'h '.($attendance->working_minutes % 60).'m' : '-');

                        $workSummary = optional($attendance->workLogs->first())->work_summary
                        ?? $attendance->punch_out_note
                        ?? '-';

                        $isBlocked = $attendance->is_blocked ?? $attendance->is_punch_blocked ?? false;
                        $isLate = $attendance->is_late ?? $attendance->late_mark ?? false;
                        $isEarly = $attendance->is_early_out ?? $attendance->early_leave_mark ?? false;
                        $isMissed = $attendance->missed_punch ?? false;
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <div class="att-emp">
                                    <div class="att-avatar">
                                        {{ strtoupper(substr($employeeName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="att-emp-name" title="{{ $employeeName }}">
                                            {{ $employeeName }}
                                        </div>
                                        <div class="att-emp-code" title="{{ $employeeCode }}">
                                            {{ $employeeCode }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td><strong>{{ $attDate }}</strong></td>

                            <td>
                                <span class="mode-badge {{ $modeClass }}">
                                    {{ $modeLabel }}
                                </span>
                            </td>

                            <td>{{ optional($attendance->attendanceTime)->name ?? '-' }}</td>

                            <td>{{ $punchIn ? \Carbon\Carbon::parse($punchIn)->format('h:i A') : '-' }}</td>

                            <td>{{ $punchOut ? \Carbon\Carbon::parse($punchOut)->format('h:i A') : '-' }}</td>

                            <td>
                                {{ $attendance->target_punch_out_time
                                        ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A')
                                        : '-' }}
                            </td>

                            <td>{{ $gross }}</td>

                            <td><strong>{{ $net }}</strong></td>

                            <td>
                                <span class="att-badge badge-{{ $typeCode }}">
                                    {{ optional($attendance->attendanceType)->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                @if($isLate)
                                <span class="flag flag-late">Late</span>
                                @endif

                                @if($isEarly)
                                <span class="flag flag-early">Early</span>
                                @endif

                                @if($isBlocked)
                                <span class="flag flag-blocked">Blocked</span>
                                @endif

                                @if($isMissed)
                                <span class="flag flag-missed">Missed</span>
                                @endif

                                @if(!$isLate && !$isEarly && !$isBlocked && !$isMissed)
                                <span class="flag flag-clear">Clear</span>
                                @endif
                            </td>

                            <td>
                                <div class="att-task" title="{{ $workSummary }}">
                                    {{ $workSummary }}
                                </div>
                            </td>

                            <td>
                                <div class="att-action-wrap dropdown">
                                    <button type="button" class="action-dot" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                        @if($isBlocked)
                                        <button type="button"
                                            class="dropdown-item"
                                            data-toggle="modal"
                                            data-target="#unlockModal{{ $attendance->id }}">
                                            <i class="fas fa-unlock text-success"></i>
                                            Unlock
                                        </button>
                                        @endif

                                        @if(($canManageAttendance ?? false) || (auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin')))
                                        <button type="button"
                                            class="dropdown-item"
                                            data-toggle="modal"
                                            data-target="#editModal{{ $attendance->id }}">
                                            <i class="fas fa-edit text-primary"></i>
                                            Edit
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($attendances as $attendance)
        @if(($canManageAttendance ?? false) || (auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin')))
        @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
        @endif

        @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
        @endforeach

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
        const form = document.getElementById('dailyAttendanceFilterForm');
        const filters = document.querySelectorAll('.auto-filter');
        const searchInput = document.querySelector('.auto-filter-input');
        let typingTimer = null;

        function submitFilterForm() {
            if (form) {
                form.submit();
            }
        }

        filters.forEach(function(filter) {
            filter.addEventListener('change', submitFilterForm);
        });

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(submitFilterForm, 500);
            });

            searchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    submitFilterForm();
                }
            });
        }

        if ($.fn.DataTable.isDataTable('#dailyAttendanceDataTable')) {
            $('#dailyAttendanceDataTable').DataTable().destroy();
        }

        $('#dailyAttendanceDataTable').DataTable({
            destroy: true,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            ordering: true,
            responsive: false,
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,
            paging: true,
            info: true,
            searching: false,
            dom: "<'row align-items-center mb-3'<'col-md-4'l><'col-md-8 text-md-right'B>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>",
            buttons: [{
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-light border',
                    orientation: 'landscape',
                    pageSize: 'A3',
                    title: 'Orbosis HRMS Attendance Records',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-light border',
                    title: 'Orbosis HRMS Attendance Records',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ],
            language: {
                lengthMenu: 'Show _MENU_ entries',
                emptyTable: 'No attendance records found.',
                info: 'Showing _START_ to _END_ of _TOTAL_ attendance records',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        setTimeout(function() {
            $('#dailyAttendanceDataTable').DataTable().columns.adjust();
        }, 250);
    });
</script>
@endsection