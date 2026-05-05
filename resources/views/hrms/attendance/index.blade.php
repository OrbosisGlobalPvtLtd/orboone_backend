@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Attendances')

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
            max-width: 1480px;
            margin: 0 auto;
        }

        .att-card {
            background: #fff;
            border: 1px solid var(--orb-border);
            border-radius: 24px;
            box-shadow: var(--orb-shadow);
            overflow: hidden;
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
            margin: 5px 0 0;
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

        .att-btn-primary {
            background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
            color: #fff !important;
        }

        .att-kpi {
            padding: 18px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid var(--orb-border);
            box-shadow: var(--orb-shadow);
            position: relative;
            overflow: hidden;
        }

        .att-kpi:after {
            content: "";
            position: absolute;
            right: -34px;
            top: -34px;
            width: 105px;
            height: 105px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(75, 0, 232, .13), rgba(134, 0, 238, .05));
        }

        .att-kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            background: var(--orb-soft);
            color: var(--orb-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            margin-bottom: 12px;
        }

        .att-kpi span {
            font-size: 11px;
            color: var(--orb-muted);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .att-kpi h3 {
            font-size: 28px;
            font-weight: 950;
            color: var(--orb-text);
            margin: 5px 0 0;
        }

        .att-filter-wrap {
            padding: 16px 18px;
            background: linear-gradient(180deg, #fff, #fafbff);
            border-bottom: 1px solid var(--orb-border);
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
            gap: 9px;
        }

        .att-filter-title i {
            color: var(--orb-primary);
        }

        .att-filter-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr 1fr 1fr 1fr 1fr;
            gap: 10px;
            align-items: end;
        }

        .att-filter-wrap label {
            font-size: 10px;
            font-weight: 950;
            color: #667085;
            text-transform: uppercase;
            letter-spacing: .04em;
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
            padding: 0 16px 16px;
        }

        .att-table-responsive {
            width: 100%;
        }

        .dataTables_scroll {
            width: 100%;
        }

        .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            border-bottom: 0 !important;
        }

        .dataTables_scrollHead {
            overflow: hidden !important;
        }

        .att-table {
            width: 100% !important;
            min-width: 1320px;
            table-layout: fixed;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
        }

        .att-table thead th {
            background: #F8FAFC;
            color: #475467;
            font-size: 11px;
            font-weight: 950;
            text-transform: uppercase;
            padding: 13px 14px !important;
            border-top: 1px solid #EAECF0 !important;
            border-bottom: 1px solid #EAECF0 !important;
            white-space: nowrap;
        }

        .att-table tbody tr {
            transition: .2s ease;
        }

        .att-table tbody tr:hover td {
            background: #FAF8FF;
        }

        .att-table td {
            background: #fff;
            border-top: 0 !important;
            border-bottom: 1px solid #EEF2F6 !important;
            padding: 14px !important;
            vertical-align: middle;
        }

        .att-table th:nth-child(1),
        .att-table td:nth-child(1) {
            width: 220px;
        }

        .att-table th:nth-child(2),
        .att-table td:nth-child(2) {
            width: 135px;
        }

        .att-table th:nth-child(3),
        .att-table td:nth-child(3) {
            width: 75px;
        }

        .att-table th:nth-child(4),
        .att-table td:nth-child(4) {
            width: 95px;
        }

        .att-table th:nth-child(5),
        .att-table td:nth-child(5) {
            width: 95px;
        }

        .att-table th:nth-child(6),
        .att-table td:nth-child(6) {
            width: 115px;
        }

        .att-table th:nth-child(7),
        .att-table td:nth-child(7) {
            width: 115px;
        }

        .att-table th:nth-child(8),
        .att-table td:nth-child(8) {
            width: 135px;
        }

        .att-table th:nth-child(9),
        .att-table td:nth-child(9) {
            width: 250px;
        }

        .att-table th:nth-child(10),
        .att-table td:nth-child(10) {
            width: 130px;
        }

        .att-table th:nth-child(11),
        .att-table td:nth-child(11) {
            width: 75px;
        }

        .att-table td:nth-child(2),
        .att-table td:nth-child(4),
        .att-table td:nth-child(5),
        .att-table td:nth-child(6),
        .att-table td:nth-child(7),
        .att-table td:nth-child(8),
        .att-table td:nth-child(11) {
            white-space: nowrap;
        }

        .att-table td:nth-child(11) {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .att-avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: var(--orb-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 950;
            color: var(--orb-primary);
            flex: 0 0 auto;
            box-shadow: inset 0 0 0 1px rgba(75, 0, 232, .08);
        }

        .att-emp {
            display: flex;
            gap: 11px;
            align-items: center;
            min-width: 0;
        }

        .att-emp-name {
            font-weight: 900;
            color: var(--orb-text);
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 145px;
        }

        .att-emp-code {
            font-size: 12px;
            color: var(--orb-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 145px;
        }

        .att-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 9px;
            font-size: 10px;
            font-weight: 950;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .badge-present {
            background: #dcfce7;
            color: #166534;
        }

        .badge-absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-half_day {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-leave {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-week_off {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-holiday {
            background: #ede9fe;
            color: #5b21b6;
        }

        .badge-pending_hr {
            background: #ffedd5;
            color: #9a3412;
        }

        .badge-default {
            background: #f1f5f9;
            color: #475569;
        }

        .mode-badge {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 950;
        }

        .mode-wfo {
            background: #eef2ff;
            color: #3730a3;
        }

        .mode-wfh {
            background: #ecfeff;
            color: #155e75;
        }

        .mode-default {
            background: #f1f5f9;
            color: #475569;
        }

        .att-small {
            font-size: 12px;
            color: var(--orb-muted);
        }

        .att-task {
            max-width: 230px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.4;
        }

        .att-actions {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid var(--orb-border);
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .dataTables_wrapper>.row:first-child {
            background: #fff;
            border-bottom: 1px solid var(--orb-border);
            padding: 14px 16px;
            margin: 0 -16px 12px !important;
        }

        .dataTables_wrapper .dt-buttons .btn {
            border-radius: 11px !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            margin-right: 6px !important;
            margin-bottom: 6px !important;
        }

        .dataTables_length select {
            border-radius: 10px !important;
            padding: 4px 22px 4px 8px !important;
        }

        .dataTables_filter input {
            border-radius: 12px !important;
            border: 1px solid var(--orb-border) !important;
            padding: 7px 10px !important;
        }

        /* Modal Fix */
        .modal-backdrop {
            z-index: 1240 !important;
            background: #0F172A !important;
        }

        .modal-backdrop.show {
            opacity: .58 !important;
        }

        .modal {
            z-index: 1250 !important;
        }

        .modal-content {
            background: #fff !important;
            border: 0 !important;
            border-radius: 22px !important;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .28) !important;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
            color: #fff;
            border-bottom: 0 !important;
            padding: 18px 22px;
        }

        .modal-title {
            color: #fff;
            font-weight: 900;
        }

        .modal-header .close {
            color: #fff;
            opacity: 1;
            text-shadow: none;
        }

        .modal-body {
            background: #fff !important;
            padding: 22px;
        }

        .modal-footer {
            background: #F8FAFC;
            border-top: 1px solid #EEF2F6 !important;
            padding: 16px 22px;
        }

        .modal label {
            font-size: 11px;
            font-weight: 900;
            color: #667085;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .modal .form-control {
            border-radius: 13px !important;
            border: 1px solid #E4E7EC !important;
            min-height: 42px;
            font-size: 13px;
            background: #fff !important;
        }

        @media(max-width:1200px) {
            .att-filter-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .att-container {
                max-width: 100%;
            }
        }

        @media(max-width:768px) {
            .att-page {
                padding: 12px 8px 25px;
            }

            .att-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 18px;
            }

            .att-title {
                font-size: 22px;
            }

            .att-filter-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .att-filter-grid {
                grid-template-columns: 1fr;
            }

            .dataTables_wrapper>.row:first-child {
                gap: 10px;
            }
        }
    </style>

    <div class="att-page">
        <div class="att-container">

            <div class="att-header">
                <div>
                    <h3 class="att-title">Attendance Dashboard</h3>
                    <p class="att-subtitle">
                        Admin view for daily attendance, WFO/WFH, late marks, early outs and HR pending approvals.
                    </p>
                </div>

                <a href="{{ route('attendances.export-pdf', request()->query()) }}" class="att-btn att-btn-light">
                    <i class="fas fa-file-pdf text-danger"></i> Export Report
                </a>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row mb-3">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="att-kpi">
                        <div class="att-kpi-icon"><i class="fas fa-clock"></i></div>
                        <span>Total Hours</span>
                        <h3>{{ number_format($stats['total_hours'] ?? 0, 1) }}h</h3>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="att-kpi">
                        <div class="att-kpi-icon"><i class="fas fa-user-clock"></i></div>
                        <span>Late Marks</span>
                        <h3>{{ $stats['total_late'] ?? 0 }}</h3>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="att-kpi">
                        <div class="att-kpi-icon"><i class="fas fa-running"></i></div>
                        <span>Early Outs</span>
                        <h3>{{ $stats['total_early_out'] ?? 0 }}</h3>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="att-kpi">
                        <div class="att-kpi-icon"><i class="fas fa-user-shield"></i></div>
                        <span>Pending HR</span>
                        <h3>{{ $stats['total_pending_hr'] ?? ($stats['total_blocked'] ?? 0) }}</h3>
                    </div>
                </div>
            </div>

            <div class="att-card">
                <div class="att-filter-wrap">
                    <div class="att-filter-head">
                        <h5 class="att-filter-title">
                            <i class="fas fa-sliders-h"></i> Attendance
                        </h5>

                        <a href="{{ route('attendances.index') }}" class="att-btn att-btn-light">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>

                    <form method="GET" action="{{ route('attendances.index') }}" id="attendanceFilterForm">
                        <div class="att-filter-grid">
                            <div>
                                <label>Employee</label>
                                <select name="employee_id" class="form-control auto-filter">
                                    <option value="">All Employees</option>
                                    @foreach ($employees as $emp)
                                        @php $empRecordId = optional($emp->employee)->id; @endphp
                                        @if ($empRecordId)
                                            <option value="{{ $empRecordId }}"
                                                {{ request('employee_id') == $empRecordId ? 'selected' : '' }}>
                                                {{ $emp->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control auto-filter"
                                    value="{{ request('from_date') }}">
                            </div>

                            <div>
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control auto-filter"
                                    value="{{ request('to_date') }}">
                            </div>

                            <div>
                                <label>Period</label>
                                <select name="filter" class="form-control auto-filter">
                                    <option value="">Custom / All</option>
                                    <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today
                                    </option>
                                    <option value="yesterday" {{ request('filter') == 'yesterday' ? 'selected' : '' }}>
                                        Yesterday</option>
                                    <option value="weekly" {{ request('filter') == 'weekly' ? 'selected' : '' }}>This Week
                                    </option>
                                    <option value="monthly" {{ request('filter') == 'monthly' ? 'selected' : '' }}>This
                                        Month</option>
                                </select>
                            </div>

                            <div>
                                <label>Status</label>
                                <select name="attendance_type_id" class="form-control auto-filter">
                                    <option value="">All Status</option>
                                    @foreach ($attendanceTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label>Mode</label>
                                <select name="work_mode" class="form-control auto-filter">
                                    <option value="">All</option>
                                    <option value="wfo" {{ request('work_mode') == 'wfo' ? 'selected' : '' }}>WFO
                                    </option>
                                    <option value="wfh" {{ request('work_mode') == 'wfh' ? 'selected' : '' }}>WFH
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label>Flags</label>
                                <select name="flag" class="form-control auto-filter">
                                    <option value="">All Records</option>
                                    <option value="late" {{ request('flag') == 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="early_out" {{ request('flag') == 'early_out' ? 'selected' : '' }}>Early
                                        Out</option>
                                    <option value="pending_hr" {{ request('flag') == 'pending_hr' ? 'selected' : '' }}>
                                        Pending HR</option>
                                    <option value="clear" {{ request('flag') == 'clear' ? 'selected' : '' }}>Clear
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="att-table-wrap">
                    <div class="att-table-responsive">
                        <table class="att-table table" id="attendanceDataTable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Punch In</th>
                                    <th>Punch Out</th>
                                    <th>Net</th>
                                    <th>Gross</th>
                                    <th>Status</th>
                                    <th>Task Summary</th>
                                    <th>Flags</th>
                                    <th class="no-export text-right">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($attendances as $attendance)
                                    @php
                                        $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                                        $workSummary = optional($attendance->workLogs->first())->work_summary;
                                        $modeCode = strtolower($attendance->work_mode ?? '');
                                        $modeLabel = $modeCode === 'wfh' ? 'WFH' : ($modeCode === 'wfo' ? 'WFO' : '-');
                                        $modeClass = in_array($modeCode, ['wfo', 'wfh']) ? $modeCode : 'default';
                                        $attDate = $attendance->attendance_date
                                            ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y')
                                            : '-';
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="att-emp">
                                                <div class="att-avatar">
                                                    {{ strtoupper(substr(optional($attendance->user)->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="att-emp-name"
                                                        title="{{ optional($attendance->user)->name ?? 'N/A' }}">
                                                        {{ optional($attendance->user)->name ?? 'N/A' }}
                                                    </div>
                                                    <div class="att-emp-code"
                                                        title="{{ optional($attendance->employee)->employee_code ?? 'N/A' }}">
                                                        {{ optional($attendance->employee)->employee_code ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td><strong>{{ $attDate }}</strong></td>

                                        <td><span class="mode-badge mode-{{ $modeClass }}">{{ $modeLabel }}</span>
                                        </td>

                                        <td>{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}
                                        </td>
                                        <td>{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}
                                        </td>

                                        <td><strong>{{ $attendance->net_duration ?? '-' }}</strong></td>
                                        <td>{{ $attendance->gross_duration ?? '-' }}</td>

                                        <td>
                                            <span class="att-badge badge-{{ $typeCode }}">
                                                {{ optional($attendance->attendanceType)->name ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="att-small att-task"
                                                title="{{ $workSummary ?: $attendance->punch_out_note ?: '-' }}">
                                                {{ $workSummary ?: $attendance->punch_out_note ?: '-' }}
                                            </div>
                                        </td>

                                        <td>
                                            @if ($attendance->is_late)
                                                <div class="att-small text-warning font-weight-bold">Late:
                                                    {{ $attendance->late_minutes ?? 0 }} min</div>
                                            @endif

                                            @if ($attendance->is_early_out)
                                                <div class="att-small text-danger font-weight-bold">Early:
                                                    {{ $attendance->early_out_minutes ?? 0 }} min</div>
                                            @endif

                                            @if ($attendance->is_blocked)
                                                <div class="att-small text-danger font-weight-bold">Pending HR</div>
                                            @endif

                                            @if (!$attendance->is_late && !$attendance->is_early_out && !$attendance->is_blocked)
                                                <span class="att-small">Clear</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="att-actions">
                                                @if ($attendance->is_blocked)
                                                    <button type="button" class="icon-btn text-success"
                                                        data-toggle="modal"
                                                        data-target="#unlockModal{{ $attendance->id }}"
                                                        title="Approve / Unlock">
                                                        <i class="fas fa-unlock"></i>
                                                    </button>
                                                @endif

                                                <button type="button" class="icon-btn text-primary" data-toggle="modal"
                                                    data-target="#editModal{{ $attendance->id }}"
                                                    title="Edit Attendance">
                                                    <i class="fas fa-edit"></i>
                                                </button>
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

            {{-- Modals outside table --}}
            @foreach ($attendances as $attendance)
                @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
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
            const form = document.getElementById('attendanceFilterForm');
            const filters = document.querySelectorAll('.auto-filter');

            function submitFilterForm() {
                if (form) form.submit();
            }

            filters.forEach(function(filter) {
                filter.addEventListener('change', submitFilterForm);
            });

            $('#attendanceDataTable').DataTable({
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                ordering: true,
                responsive: false,
                autoWidth: false,
                scrollX: true,
                paging: true,
                info: true,
                searching: false,
                dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
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
                        pageSize: 'A4',
                        title: 'Orbosis HRMS Attendance Report',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-light border',
                        title: 'Orbosis HRMS Attendance Report',
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
        });
    </script>
@endsection
