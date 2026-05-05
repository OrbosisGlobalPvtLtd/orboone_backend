@extends('layouts.admin', ['accesses' => $accesses ?? [], 'active' => 'attendances'])

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
            --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
        }

        .att-page {
            min-height: calc(100vh - 90px);
            padding: 18px 12px 35px;
            background: var(--orb-bg);
        }

        .att-container {
            max-width: 1450px;
            margin: 0 auto;
        }

        .att-card,
        .att-header {
            background: #fff;
            border: 1px solid var(--orb-border);
            border-radius: 20px;
            box-shadow: var(--orb-shadow);
        }

        .att-header {
            padding: 18px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 18px;
        }

        .att-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--orb-text);
            margin: 0;
        }

        .att-subtitle {
            font-size: 13px;
            color: var(--orb-muted);
            margin: 4px 0 0;
        }

        .att-btn {
            border: 0;
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 700;
            display: inline-flex;
            gap: 8px;
            align-items: center;
        }

        .att-btn-primary {
            background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
            color: #fff;
        }

        .att-btn-danger {
            background: linear-gradient(135deg, #ec4e74, #ff7675);
            color: #fff;
        }

        .att-stat {
            border-radius: 18px;
            padding: 18px;
            background: #fff;
            border: 1px solid var(--orb-border);
            box-shadow: var(--orb-shadow);
        }

        .att-stat span {
            font-size: 12px;
            color: var(--orb-muted);
            font-weight: 700;
            text-transform: uppercase;
        }

        .att-stat h3 {
            font-size: 26px;
            margin: 6px 0 0;
            font-weight: 900;
            color: var(--orb-text);
        }

        .att-filter {
            padding: 18px;
            margin-bottom: 18px;
        }

        .att-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .att-table th {
            font-size: 11px;
            text-transform: uppercase;
            color: #667085;
            font-weight: 800;
            border: 0;
            padding: 12px;
        }

        .att-table td {
            background: #fff;
            border-top: 1px solid #edf0f6;
            border-bottom: 1px solid #edf0f6;
            padding: 14px 12px;
            vertical-align: middle;
        }

        .att-table td:first-child {
            border-left: 1px solid #edf0f6;
            border-radius: 14px 0 0 14px;
        }

        .att-table td:last-child {
            border-right: 1px solid #edf0f6;
            border-radius: 0 14px 14px 0;
        }

        .att-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--orb-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: var(--orb-primary);
        }

        .att-emp {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .att-emp-name {
            font-weight: 800;
            color: var(--orb-text);
            font-size: 14px;
        }

        .att-emp-code {
            font-size: 12px;
            color: var(--orb-muted);
        }

        .att-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 11px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
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

        .att-small {
            font-size: 12px;
            color: var(--orb-muted);
        }

        .att-actions {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            border: 1px solid var(--orb-border);
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-att-card {
            display: none;
            background: #fff;
            border: 1px solid var(--orb-border);
            border-radius: 18px;
            box-shadow: var(--orb-shadow);
            padding: 16px;
            margin-bottom: 12px;
        }

        @media(max-width:992px) {
            .desktop-table {
                display: none;
            }

            .mobile-att-card {
                display: block;
            }

            .att-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <div class="att-page">
        <div class="att-container">

            <div class="att-header">
                <div>
                    <h3 class="att-title">Attendance Management</h3>
                    <p class="att-subtitle">Daily punch, work mode, late marks, task summary and monthly history.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="att-btn att-btn-primary" data-toggle="modal" data-target="#adminPunchInModal">
                        <i class="fas fa-sign-in-alt"></i> Admin Punch In
                    </button>

                    <button class="att-btn att-btn-danger" data-toggle="modal" data-target="#adminPunchOutModal">
                        <i class="fas fa-sign-out-alt"></i> Admin Punch Out
                    </button>

                    <a href="{{ route('attendances.export-pdf', ['date' => request('date')]) }}"
                        class="att-btn btn-light border">
                        <i class="fas fa-file-pdf text-danger"></i> Export PDF
                    </a>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row mb-3">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="att-stat">
                        <span>Total Hours</span>
                        <h3>{{ number_format($stats['total_hours'] ?? 0, 1) }}h</h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="att-stat">
                        <span>Late Marks</span>
                        <h3>{{ $stats['total_late'] ?? 0 }}</h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="att-stat">
                        <span>Early Outs</span>
                        <h3>{{ $stats['total_early_out'] ?? 0 }}</h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="att-stat">
                        <span>Pending HR</span>
                        <h3>{{ $stats['total_pending_hr'] ?? ($stats['total_blocked'] ?? 0) }}</h3>
                    </div>
                </div>
            </div>

            <div class="att-card att-filter">
                <form method="GET" action="{{ route('attendances.index') }}">
                    <div class="row align-items-end">
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="font-weight-bold small">Search</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                placeholder="Name, email, employee code">
                        </div>

                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="font-weight-bold small">Employee</label>
                            <select name="employee_id" class="form-control">
                                <option value="">All Employees</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ optional($emp->employee)->id }}"
                                        {{ request('employee_id') == optional($emp->employee)->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="font-weight-bold small">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                        </div>

                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="font-weight-bold small">Period</label>
                            <select name="filter" class="form-control">
                                <option value="">All</option>
                                <option value="weekly" {{ request('filter') == 'weekly' ? 'selected' : '' }}>This Week
                                </option>
                                <option value="monthly" {{ request('filter') == 'monthly' ? 'selected' : '' }}>This Month
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="font-weight-bold small">Status</label>
                            <select name="attendance_type_id" class="form-control">
                                <option value="">All Status</option>
                                @foreach ($attendanceTypes as $type)
                                    <option value="{{ $type->id }}"
                                        {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-1 col-md-6 mb-2">
                            <button type="submit" class="att-btn att-btn-primary w-100 justify-content-center">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="desktop-table att-card p-3">
                <div class="table-responsive">
                    <table class="att-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Work Mode</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                                <th>Net Hours</th>
                                <th>Status</th>
                                <th>Task Summary</th>
                                <th>Flags</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                @php
                                    $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                                    $workSummary = optional($attendance->workLogs->first())->work_summary;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="att-emp">
                                            <div class="att-avatar">
                                                {{ strtoupper(substr(optional($attendance->user)->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="att-emp-name">{{ optional($attendance->user)->name ?? 'N/A' }}
                                                </div>
                                                <div class="att-emp-code">
                                                    {{ optional($attendance->employee)->employee_code ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ optional($attendance->attendance_date)->format('d M Y') }}</strong>
                                    </td>
                                    <td>
                                        {{ $attendance->work_mode_label }}
                                    </td>
                                    <td>{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}
                                    </td>
                                    <td>{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}
                                    </td>
                                    <td>
                                        <strong>{{ $attendance->net_duration }}</strong>
                                        <div class="att-small">Gross: {{ $attendance->gross_duration }}</div>
                                    </td>
                                    <td>
                                        <span class="att-badge badge-{{ $typeCode }}">
                                            {{ optional($attendance->attendanceType)->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td style="max-width:260px;">
                                        <div class="att-small">{{ $workSummary ?: $attendance->punch_out_note ?: '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($attendance->is_late)
                                            <div class="att-small text-warning font-weight-bold">Late:
                                                {{ $attendance->late_minutes }} min</div>
                                        @endif
                                        @if ($attendance->is_early_out)
                                            <div class="att-small text-danger font-weight-bold">Early:
                                                {{ $attendance->early_out_minutes }} min</div>
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
                                                <button class="icon-btn text-success" data-toggle="modal"
                                                    data-target="#unlockModal{{ $attendance->id }}" title="Approve">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            @endif

                                            <button class="icon-btn text-primary" data-toggle="modal"
                                                data-target="#editModal{{ $attendance->id }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                @include('hrms.attendance.partials.edit-modal', [
                                    'attendance' => $attendance,
                                ])
                                @include('hrms.attendance.partials.unlock-modal', [
                                    'attendance' => $attendance,
                                ])
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        No attendance records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $attendances->links() }}
                </div>
            </div>

            @foreach ($attendances as $attendance)
                @php
                    $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                    $workSummary = optional($attendance->workLogs->first())->work_summary;
                @endphp

                <div class="mobile-att-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="att-emp">
                            <div class="att-avatar">
                                {{ strtoupper(substr(optional($attendance->user)->name ?? 'U', 0, 1)) }}</div>
                            <div>
                                <div class="att-emp-name">{{ optional($attendance->user)->name ?? 'N/A' }}</div>
                                <div class="att-emp-code">{{ optional($attendance->employee)->employee_code ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <span
                            class="att-badge badge-{{ $typeCode }}">{{ optional($attendance->attendanceType)->name ?? 'N/A' }}</span>
                    </div>

                    <hr>

                    <div class="row small">
                        <div class="col-6 mb-2">
                            <strong>Date:</strong><br>{{ optional($attendance->attendance_date)->format('d M Y') }}
                        </div>
                        <div class="col-6 mb-2"><strong>Mode:</strong><br>{{ $attendance->work_mode_label }}</div>
                        <div class="col-6 mb-2">
                            <strong>In:</strong><br>{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}
                        </div>
                        <div class="col-6 mb-2">
                            <strong>Out:</strong><br>{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}
                        </div>
                        <div class="col-12 mb-2">
                            <strong>Task:</strong><br>{{ $workSummary ?: $attendance->punch_out_note ?: '-' }}
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    {{-- Admin Punch In Modal --}}
    <div class="modal fade" id="adminPunchInModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('attendances.admin.punch-in') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Admin Punch In</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <label>Employee</label>
                    <select name="user_id" class="form-control mb-3" required>
                        <option value="">Select Employee</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} -
                                {{ optional($emp->employee)->employee_code }}</option>
                        @endforeach
                    </select>

                    <label>Time</label>
                    <input type="datetime-local" name="time" class="form-control mb-3" required>

                    <label>Work Mode</label>
                    <select name="work_mode" class="form-control mb-3" required>
                        <option value="wfo">Work From Office</option>
                        <option value="wfh">Work From Home</option>
                    </select>

                    <label>Status</label>
                    <select name="attendance_type_id" class="form-control mb-3">
                        @foreach ($attendanceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>

                    <label>Note</label>
                    <textarea name="note" class="form-control" rows="3">Admin Override</textarea>
                </div>
                <div class="modal-footer">
                    <button class="att-btn att-btn-primary">Save Punch In</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Admin Punch Out Modal --}}
    <div class="modal fade" id="adminPunchOutModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('attendances.admin.punch-out') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Admin Punch Out</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <label>Employee</label>
                    <select name="user_id" class="form-control mb-3" required>
                        <option value="">Select Employee</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} -
                                {{ optional($emp->employee)->employee_code }}</option>
                        @endforeach
                    </select>

                    <label>Time</label>
                    <input type="datetime-local" name="time" class="form-control mb-3" required>

                    <label>Task Summary</label>
                    <textarea name="task_summary" class="form-control mb-3" rows="4" required
                        placeholder="What did employee work on?"></textarea>

                    <label>Note</label>
                    <textarea name="note" class="form-control" rows="3">Admin Override</textarea>
                </div>
                <div class="modal-footer">
                    <button class="att-btn att-btn-danger">Save Punch Out</button>
                </div>
            </form>
        </div>
    </div>
@endsection
