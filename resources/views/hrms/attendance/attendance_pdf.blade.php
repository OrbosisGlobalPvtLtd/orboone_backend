<!DOCTYPE html>
<html>

<head>
    <title>Attendance Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.45;
        }

        .header {
            text-align: center;
            margin-bottom: 22px;
            border-bottom: 2px solid #4b00e8;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #4b00e8;
            font-size: 22px;
        }

        .header p {
            margin: 4px 0;
            color: #64748b;
        }

        .summary-box {
            margin-bottom: 18px;
            padding: 12px;
            background: #f8f9fc;
            border-radius: 8px;
        }

        .summary-item {
            display: inline-block;
            width: 19%;
            text-align: center;
        }

        .summary-item strong {
            display: block;
            font-size: 13px;
            color: #4b00e8;
        }

        .summary-item span {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }

        th {
            background: #f8f9fc;
            color: #4b00e8;
            font-weight: bold;
            text-align: left;
            padding: 7px;
            border: 1px solid #e3e6f0;
            text-transform: uppercase;
        }

        td {
            padding: 7px;
            border: 1px solid #e3e6f0;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #fdfdff;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
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

        .footer {
            text-align: right;
            font-size: 9px;
            color: #94a3b8;
            margin-top: 22px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>ORBOSIS Global Pvt Ltd</h1>
        <p>Attendance Report: {{ $periodLabel ?? 'All Records' }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <strong>{{ $attendances->count() }}</strong>
            <span>Total Records</span>
        </div>
        <div class="summary-item">
            <strong>{{ $attendances->where('is_late', true)->count() }}</strong>
            <span>Late Marks</span>
        </div>
        <div class="summary-item">
            <strong>{{ $attendances->where('is_early_out', true)->count() }}</strong>
            <span>Early Outs</span>
        </div>
        <div class="summary-item">
            <strong>{{ $attendances->where('is_blocked', true)->count() }}</strong>
            <span>Pending HR</span>
        </div>
        <div class="summary-item">
            <strong>{{ number_format($attendances->sum('total_work_minutes') / 60, 1) }}h</strong>
            <span>Work Hours</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Code</th>
                <th>Department</th>
                <th>Date</th>
                <th>Mode</th>
                <th>Punch In</th>
                <th>Punch Out</th>
                <th>Hours</th>
                <th>Status</th>
                <th>Flags</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                @php
                    $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                    $flags = [];
                    if ($attendance->is_late) {
                        $flags[] = 'Late ' . $attendance->late_minutes . 'm';
                    }
                    if ($attendance->is_early_out) {
                        $flags[] = 'Early ' . $attendance->early_out_minutes . 'm';
                    }
                    if ($attendance->is_blocked) {
                        $flags[] = 'Pending HR';
                    }
                @endphp
                <tr>
                    <td><strong>{{ optional($attendance->user)->name ?? 'N/A' }}</strong></td>
                    <td>{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</td>
                    <td>{{ optional(optional($attendance->employee)->department)->name ?? '-' }}</td>
                    <td>{{ optional($attendance->attendance_date)->format('d M Y') ?? '-' }}</td>
                    <td>{{ strtoupper($attendance->work_mode ?? '-') }}</td>
                    <td>{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}
                    </td>
                    <td>{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}
                    </td>
                    <td>{{ $attendance->net_duration }}</td>
                    <td><span
                            class="badge badge-{{ $typeCode }}">{{ optional($attendance->attendanceType)->name ?? 'N/A' }}</span>
                    </td>
                    <td>{{ $flags ? implode(', ', $flags) : 'Clear' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} | HRMS Attendance Report
    </div>
</body>

</html>
