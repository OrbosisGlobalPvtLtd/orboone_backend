@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Attendance Records')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@endsection

@section('_content')

@include('hrms.employee.partials.styles')

<style>
    :root {

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
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .att-container {
        max-width: 1500px;
        margin: 0 auto;
    }

    /* Premium Purple Gradient Hero Header */
    .att-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 26px !important;
        padding: 32px 36px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 28px !important;
        border: none !important;
    }

    .att-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 300px !important;
        height: 300px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .att-header-premium .title-area h3 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .att-header-premium .title-area p {
        font-size: 14px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .att-header-premium .header-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.75) !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* Premium Pill Buttons */
    .att-btn-pill {
        height: 42px !important;
        padding: 0 20px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        cursor: pointer !important;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
    }

    .att-btn-pill:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    /* Table card styling */
    .orb-table-card {
        background: #fff !important;
        border-radius: 24px !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: 0 14px 35px rgba(16,24,40,.07) !important;
        overflow: hidden !important;
        margin-bottom: 30px !important;
    }

    /* Table Toolbar */
    .orb-table-toolbar {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
        padding: 16px 26px !important;
        border-top: 1px solid #F1F5F9 !important;
        border-bottom: 1px solid #F1F5F9 !important;
        background: #fff !important;
    }

    .orb-table-toolbar .toolbar-left {
        display: flex !important;
        align-items: center !important;
    }

    .orb-table-toolbar .toolbar-right {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    /* Attached Filters Area inside Table Card */
    .att-filters-attached {
        background: #F8FAFC !important;
        border-bottom: 1px solid var(--orb-border) !important;
        padding: 20px 26px 12px !important;
    }

    .att-filter-grid {
        display: grid !important;
        grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        gap: 12px !important;
        align-items: flex-end !important;
    }

    .att-filter-grid label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .att-filter-grid .form-control {
        height: 44px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        width: 100% !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .att-filter-grid .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    /* Entries Dropdown CSS */
    .dataTables_length,
    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
        margin: 0 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: var(--orb-muted) !important;
    }

    .dataTables_length select {
        width: 72px !important;
        height: 34px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
        border: 1px solid var(--orb-border) !important;
        outline: none !important;
    }

    /* Export button CSS */
    .orb-export-btn {
        height: 34px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        background: #fff !important;
        border: 1px solid #E7EAF3 !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        margin-left: 6px !important;
        transition: all 0.2s ease !important;
        color: #475467 !important;
    }

    .orb-export-btn:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
        transform: translateY(-1px) !important;
    }

    /* Table Scroll area */
    .orb-table-scroll {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        border: none !important;
    }

    .orb-table-scroll table {
        min-width: 1400px !important;
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    /* Table Header CSS */
    .orb-table-scroll table thead th {
        background: #F8FAFC !important;
        color: #101828 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 16px 18px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .orb-table-scroll table tbody td {
        padding: 16px 18px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        background: #fff !important;
    }

    .orb-table-scroll table tbody tr:hover td {
        background: #FAFBFF !important;
    }

    /* Table Footer styling */
    .orb-table-footer {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
        padding: 18px 26px 24px !important;
        background: #fff !important;
        border-top: 1px solid var(--orb-border) !important;
    }

    .orb-table-footer .footer-left {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-muted) !important;
    }

    .orb-table-footer .footer-right {
        display: flex !important;
        align-items: center !important;
    }

    select,
    select option,
    .form-select,
    .form-select option {
        color: #101828 !important;
        background: #fff !important;
    }

    .badge-awaiting_punch_in {
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
    }

    .btn-undo:hover {
        background: #F8FAFC !important;
        border-color: #cbd5e1 !important;
        color: var(--orb-primary) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }

    @media (max-width: 1300px) {
        .att-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 991px) {
        .att-header-premium {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 24px !important;
        }
        .att-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 575px) {
        .att-filter-grid {
            grid-template-columns: 1fr !important;
        }
        .att-btn-pill {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>

<div class="att-page">
    <div class="att-container">

        <!-- Premium Header Area -->
        <div class="att-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-calendar-check"></i> Attendance Administration
                </div>
                <h3>Daily Attendance Sheet</h3>
                <p>Monitor daily attendance records, working hours and status flags.</p>
            </div>

            <div class="d-flex align-items-center" style="gap:12px;">
                <a href="{{ route('attendances.index') }}" class="att-btn-pill text-white">
                    <i class="fas fa-chart-pie"></i>
                    Attendance Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 12px;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4 py-3" style="border-radius: 12px;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
        @endif

        <!-- Main Card -->
        <div class="card orb-table-card">

            <div class="orb-table-card-header d-flex align-items-center justify-content-between" style="padding: 24px 26px 18px; border-bottom: 1px solid #EEF2F7; background: #fff; flex-wrap: wrap; gap: 16px;">
                <div class="orb-title-wrap d-flex align-items-center" style="gap: 16px;">
                    <span class="orb-card-icon" style="width: 46px; height: 46px; border-radius: 12px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #101828;">Attendance Records List</h3>
                        <p style="margin: 4px 0 0 0; font-size: 13px; color: #667085;">Review employee attendance, punch timings, working hours and status.</p>
                    </div>
                </div>

                <!-- Reset Filters Button in Card Header -->
                <button type="button" class="btn btn-undo btn-outline-secondary btn-sm d-flex align-items-center" style="height: 40px !important; border-radius: 10px !important; padding: 0 16px !important; font-size: 13px !important; font-weight: 700 !important; border: 1px solid #e2e8f0 !important; color: #475467 !important; background: #fff !important; transition: all 0.2s ease !important; cursor: pointer;">
                    <i class="fas fa-undo mr-2" style="font-size: 11px;"></i> Reset Filters
                </button>
            </div>

            <!-- Attached Filters inside the Card -->
            <div class="att-filters-attached">
                <form id="attendanceFilterForm" onsubmit="return false;">
                    <div class="att-filter-grid">

                        <div>
                            <label>Employee</label>
                            <select name="employee_id" class="form-control">
                                <option value="">All Staff</option>
                                @foreach($employees as $emp)
                                <option value="{{ optional($emp->employee)->id }}"
                                    {{ request('employee_id') == optional($emp->employee)->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <div>
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>

                        <div>
                            <label>Status</label>
                            <select name="attendance_type_id" class="form-control">
                                <option value="">All Status</option>
                                @foreach($attendanceTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Work Mode</label>
                            <select name="work_mode" class="form-control">
                                <option value="">All</option>
                                <option value="WFO">WFO</option>
                                <option value="WFH">WFH</option>
                            </select>
                        </div>

                        <div>
                            <label>Flags</label>
                            <select name="flag" class="form-control">
                                <option value="">All Records</option>
                                <option value="Late">Late</option>
                                <option value="Early Out">Early Out</option>
                                <option value="Blocked">Blocked</option>
                                <option value="Missed">Missed</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Custom DataTables Toolbar Row -->
            <div class="orb-table-toolbar">
                <div class="toolbar-left"></div>
                <div class="toolbar-right"></div>
            </div>

            <!-- Scrollable Table Body Only -->
            <div class="orb-table-scroll">
                    <table class="att-table table mb-0" id="attendanceRecordsTable">
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
                                <th>Status</th>
                                <th>Flags</th>
                                <th class="text-right pr-4 no-export">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($attendances as $attendance)
                            @php
                            $attStatus = strtolower((string) ($attendance->attendance_status ?? ''));
                            $punchInTime = $attendance->punch_in_time;
                            $isAdminUnlocked = (bool) ($attendance->is_admin_unlocked ?? false);
                            
                            if (in_array($attStatus, ['unlocked', 'awaiting_punch_in'], true) || ($isAdminUnlocked && is_null($punchInTime))) {
                                $typeCode = 'awaiting_punch_in';
                                $statusName = 'Awaiting Punch In';
                            } elseif ($attStatus === 'punch_blocked' || ($attendance->is_punch_blocked ?? false) || ($attendance->is_blocked ?? false)) {
                                $typeCode = 'punch_blocked';
                                $statusName = 'Punch Blocked';
                            } elseif ($attStatus === 'half_day' || ($attendance->is_half_day ?? false)) {
                                $typeCode = 'half_day';
                                $statusName = 'Half Day';
                            } elseif ($attStatus === 'absent' || $attStatus === 'lwp' || ($attendance->is_lwp ?? false)) {
                                $typeCode = 'absent';
                                $statusName = '🔴 ABSENT';
                            } elseif ($attStatus === 'present' || ! is_null($punchInTime)) {
                                $typeCode = 'present';
                                $statusName = 'Present';
                            } else {
                                $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                                $statusName = optional($attendance->attendanceType)->name ?? 'Pending';
                                if ($typeCode === 'lwp' || $typeCode === 'absent') {
                                    $typeCode = 'absent';
                                    $statusName = '🔴 ABSENT';
                                }
                            }
                            $modeCode = strtolower($attendance->work_mode ?? '');
                            $modeClass = $modeCode === 'wfh' ? 'mode-wfh' : 'mode-wfo';
                            @endphp

                            <tr>
                                <td>
                                    <div class="att-emp">
                                        @php
                                            $passportPhotoUrl = resolveEmployeePassportPhoto($attendance->employee ?? $attendance);
                                            $employeeName = optional($attendance->user)->name ?? 'Employee';
                                            $employeeInitial = resolveEmployeeInitials($attendance->employee ?? $attendance);
                                        @endphp
                                        <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                            @if($passportPhotoUrl)
                                                <img
                                                    src="{{ $passportPhotoUrl }}"
                                                    alt="{{ $employeeName }}"
                                                    class="hrms-emp-avatar-img"
                                                    onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');"
                                                >
                                                <span class="hrms-emp-avatar-fallback is-hidden">
                                                    {{ $employeeInitial }}
                                                </span>
                                            @else
                                                <span class="hrms-emp-avatar-fallback is-visible">
                                                    {{ $employeeInitial }}
                                                </span>
                                            @endif
                                        </span>
                                        <div>
                                            <div class="att-emp-name">
                                                {{ optional($attendance->user)->name ?? 'N/A' }}
                                            </div>
                                            <div class="att-emp-code">
                                                {{ optional($attendance->employee)->employee_code ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $attendance->attendance_date
                                        ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y')
                                        : '-' }}
                                </td>

                                <td>
                                    <span class="eo-pill {{ $modeClass }}">
                                        <span class="eo-dot"></span>
                                        {{ strtoupper($modeCode ?: '-') }}
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

                                <td>
                                    {{ $attendance->gross_duration ?? '-' }}
                                </td>

                                <td>
                                    <strong>{{ $attendance->net_duration ?? '-' }}</strong>
                                </td>

                                <td>
                                    <span class="eo-pill badge-{{ $typeCode }}">
                                        {{ $statusName }}
                                    </span>
                                </td>

                                <td>
                                    @if($attendance->is_late)
                                    <span class="flag flag-late">Late</span>
                                    @endif

                                    @if($attendance->is_early_out)
                                    <span class="flag flag-early">Early Out</span>
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

                                <td class="text-right pr-4">
                                    <div class="dropdown text-right">
                                        <button class="btn btn-sm btn-light border-0 p-2" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 50% !important; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" style="border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid var(--orb-border); padding: 6px;">
                                            @if($attendance->is_blocked)
                                            <button type="button" class="dropdown-item d-flex align-items-center" data-toggle="modal" data-target="#unlockModal{{ $attendance->id }}" style="border-radius: 8px; font-weight: 600; padding: 8px 12px; font-size: 13px; gap: 8px;">
                                                <i class="fas fa-unlock text-success"></i> Unlock
                                            </button>
                                            @endif

                                            @if($canManageAttendance ?? false)
                                            <button type="button" class="dropdown-item d-flex align-items-center" data-toggle="modal" data-target="#editModal{{ $attendance->id }}" style="border-radius: 8px; font-weight: 600; padding: 8px 12px; font-size: 13px; gap: 8px;">
                                                <i class="fas fa-edit text-primary"></i> Edit
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <!-- Empty block handled eloquently by DataTables -->
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer for Pagination & Info -->
            <div class="orb-table-footer">
                <div class="footer-left"></div>
                <div class="footer-right"></div>
            </div>

        </div>

        @foreach($attendances as $attendance)
        @if($canManageAttendance ?? false)
        @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
        @endif
        @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
        @endforeach

    </div>
</div>

@endsection

@section('_script')

<script src="https://cdn.datatables.net/1.13.8/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    // Vanilla JS Date Parser
    function parseDate(dateStr) {
        if (!dateStr) return null;
        var parts = dateStr.replace(/,/g, '').split(' ');
        if (parts.length === 3) {
            var day = parseInt(parts[0]);
            var months = {jan:0,feb:1,mar:2,apr:3,may:4,jun:5,jul:6,aug:7,sep:8,oct:9,nov:10,dec:11};
            var month = months[parts[1].toLowerCase().substring(0,3)];
            var year = parseInt(parts[2]);
            return new Date(year, month, day);
        }
        return new Date(dateStr);
    }

    $(function() {
        if ($.fn.DataTable.isDataTable('#attendanceRecordsTable')) {
            $('#attendanceRecordsTable').DataTable().destroy();
        }

        var table = $('#attendanceRecordsTable').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: false,
            autoWidth: false,
            scrollX: false,
            dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
                "<'row'<'col-md-12 orb-table-scroll't>>" +
                "<'row align-items-center mt-3 px-4 pb-4'<'col-md-5'i><'col-md-7'p>>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-info"></i> CSV',
                    className: 'orb-export-btn',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'orb-export-btn',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'orb-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Attendance Records List',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'orb-export-btn',
                    title: 'Attendance Records List',
                    exportOptions: { columns: ':not(.no-export)' }
                }
            ],
            language: {
                emptyTable: 'No records found.',
                zeroRecords: 'No matching records found.',
                paginate: {
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>'
                }
            }
        });

        // Relocate the generated controls to the custom orb-table-toolbar and orb-table-footer containers!
        $('.orb-table-toolbar .toolbar-left').append($('.dataTables_length'));
        $('.orb-table-toolbar .toolbar-right').append($('.dt-buttons'));
        $('.orb-table-footer .footer-left').append($('.dataTables_info'));
        $('.orb-table-footer .footer-right').append($('.dataTables_paginate'));

        // Auto-apply filters
        $('select[name="employee_id"]').on('change', function() {
            var val = $(this).val();
            if (!val) {
                table.column(0).search('').draw();
            } else {
                var text = $(this).find('option:selected').text().trim();
                table.column(0).search(text).draw();
            }
        });

        // From Date / To Date search logic
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'attendanceRecordsTable') return true;
                
                var min = $('input[name="from_date"]').val();
                var max = $('input[name="to_date"]').val();
                var dateStr = data[1];
                
                if (!dateStr || dateStr === '-') return true;
                
                var date = parseDate(dateStr);
                if (!date || isNaN(date.getTime())) return true;
                
                if (min) {
                    var minDate = new Date(min);
                    minDate.setHours(0,0,0,0);
                    if (date < minDate) return false;
                }
                if (max) {
                    var maxDate = new Date(max);
                    maxDate.setHours(23,59,59,999);
                    if (date > maxDate) return false;
                }
                return true;
            }
        );

        $('input[name="from_date"], input[name="to_date"]').on('change', function() {
            table.draw();
        });

        // Status filter
        $('select[name="attendance_type_id"]').on('change', function() {
            var val = $(this).val();
            if (!val) {
                table.column(9).search('').draw();
            } else {
                var text = $(this).find('option:selected').text().trim();
                table.column(9).search(text).draw();
            }
        });

        // Work Mode filter
        $('select[name="work_mode"]').on('change', function() {
            var val = $(this).val();
            table.column(2).search(val ? val : '').draw();
        });

        // Flags filter
        $('select[name="flag"]').on('change', function() {
            var val = $(this).val();
            if (!val) {
                table.column(10).search('').draw();
            } else {
                table.column(10).search(val).draw();
            }
        });

        // Reset Button
        $('.btn-undo').on('click', function(e) {
            e.preventDefault();
            $('select[name="employee_id"]').val('');
            $('input[name="from_date"]').val('');
            $('input[name="to_date"]').val('');
            $('select[name="attendance_type_id"]').val('');
            $('select[name="work_mode"]').val('');
            $('select[name="flag"]').val('');
            table.search('').columns().search('').draw();
        });
    });
</script>

@endsection
