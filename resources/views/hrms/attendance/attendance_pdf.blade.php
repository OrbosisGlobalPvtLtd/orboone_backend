<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ branding_name() }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 14px 16px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 8.5px;
            line-height: 1.3;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* HEADER SECTION */
        .brand-header {
            width: 100%;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .brand-table {
            width: 100%;
            border-collapse: collapse;
        }

        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #4f46e5;
            letter-spacing: -0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            margin-top: 2px;
        }

        .meta-box {
            text-align: right;
            font-size: 8.5px;
            color: #64748b;
        }

        .meta-badge {
            display: inline-block;
            background: #eef2ff;
            color: #4338ca;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 5px;
            border: 1px solid #c7d2fe;
            margin-bottom: 3px;
            font-size: 9px;
        }

        /* KPI SUMMARY SECTION */
        .summary-container {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 8px;
            text-align: center;
            width: 16%;
        }

        .kpi-value {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .kpi-label {
            font-size: 7.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }

        /* TABLE SECTION */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: fixed;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
            text-align: left;
            overflow: hidden;
            word-wrap: break-word;
        }

        .data-table td {
            padding: 5px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            overflow: hidden;
            word-wrap: break-word;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
        }

        .badge-present { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-absent { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-half_day { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-leave { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-punch_blocked { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-default { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        .flag-pill {
            display: inline-block;
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #ffedd5;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            margin-right: 1px;
            margin-bottom: 1px;
        }

        .flag-clear {
            color: #16a34a;
            font-weight: 700;
        }

        .emp-name {
            font-size: 8.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.15;
        }

        .emp-code {
            font-size: 7px;
            color: #64748b;
        }

        /* FOOTER */
        .report-footer {
            width: 100%;
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 7.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- BRAND HEADER -->
    <div class="brand-header">
        <table class="brand-table">
            <tr>
                <td style="border:0; padding:0;">
                    <div class="company-name">{{ branding_name() }}</div>
                    <div class="report-title">Employee Attendance & Work Summary Report</div>
                </td>
                <td style="border:0; padding:0;" class="meta-box">
                    <div class="meta-badge">Period: {{ $periodLabel ?? 'All Records' }}</div>
                    <div>Generated: {{ now()->format('d M Y, h:i A') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- SUMMARY KPI CARDS -->
    @php
        $totalCount = $attendances->count();
        $presentCount = $attendances->filter(fn($a) => optional($a->attendanceType)->code === 'present')->count();
        $lateCount = $attendances->where('is_late', true)->count();
        $earlyOutCount = $attendances->where('is_early_out', true)->count();
        $blockedCount = $attendances->filter(fn($a) => ($a->is_blocked || $a->is_punch_blocked))->count();
        $totalHours = round($attendances->sum('total_work_minutes') / 60, 1);
    @endphp

    <table class="summary-container">
        <tr>
            <td class="kpi-card" style="border-left: 3px solid #4f46e5;">
                <div class="kpi-value">{{ $totalCount }}</div>
                <div class="kpi-label">Total Records</div>
            </td>
            <td style="width: 1%;"></td>
            <td class="kpi-card" style="border-left: 3px solid #16a34a;">
                <div class="kpi-value" style="color: #16a34a;">{{ $presentCount }}</div>
                <div class="kpi-label">Present</div>
            </td>
            <td style="width: 1%;"></td>
            <td class="kpi-card" style="border-left: 3px solid #f97316;">
                <div class="kpi-value" style="color: #ea580c;">{{ $lateCount }}</div>
                <div class="kpi-label">Late Marks</div>
            </td>
            <td style="width: 1%;"></td>
            <td class="kpi-card" style="border-left: 3px solid #0284c7;">
                <div class="kpi-value" style="color: #0284c7;">{{ $earlyOutCount }}</div>
                <div class="kpi-label">Early Logout</div>
            </td>
            <td style="width: 1%;"></td>
            <td class="kpi-card" style="border-left: 3px solid #dc2626;">
                <div class="kpi-value" style="color: #dc2626;">{{ $blockedCount }}</div>
                <div class="kpi-label">Punch Blocked</div>
            </td>
            <td style="width: 1%;"></td>
            <td class="kpi-card" style="border-left: 3px solid #8b5cf6;">
                <div class="kpi-value" style="color: #7c3aed;">{{ $totalHours }}h</div>
                <div class="kpi-label">Total Work Hours</div>
            </td>
        </tr>
    </table>

    <!-- DETAILED ATTENDANCE TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22px; text-align: center;">#</th>
                <th style="width: 125px;">Employee</th>
                <th style="width: 85px;">Dept / Shift</th>
                <th style="width: 60px;">Date</th>
                <th style="width: 35px; text-align: center;">Mode</th>
                <th style="width: 50px; text-align: center;">Punch In</th>
                <th style="width: 50px; text-align: center;">Punch Out</th>
                <th style="width: 50px; text-align: center;">Target Out</th>
                <th style="width: 40px; text-align: center;">Gross</th>
                <th style="width: 40px; text-align: center;">Net</th>
                <th style="width: 65px; text-align: center;">Status</th>
                <th style="width: 75px;">Flags</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $index => $attendance)
                @php
                    $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                    $typeName = optional($attendance->attendanceType)->name ?? ucwords(str_replace('_', ' ', $attendance->attendance_status ?? 'N/A'));
                    
                    $flags = [];
                    if ($attendance->is_late) {
                        $flags[] = 'Late ' . ($attendance->late_minutes ?? 0) . 'm';
                    }
                    if ($attendance->is_early_out) {
                        $flags[] = 'Early ' . ($attendance->early_out_minutes ?? 0) . 'm';
                    }
                    if ($attendance->is_blocked || $attendance->is_punch_blocked) {
                        $flags[] = 'Blocked';
                    }
                    if ($attendance->missed_punch || $attendance->is_missed_punch) {
                        $flags[] = 'Missed';
                    }
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b; font-weight: 700;">{{ $index + 1 }}</td>
                    <td>
                        <div class="emp-name">{{ optional($attendance->user)->name ?? optional($attendance->employee)->display_name ?? 'N/A' }}</div>
                        <div class="emp-code">{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #334155;">{{ optional(optional($attendance->employee)->department)->name ?? 'Staff' }}</div>
                        <div style="font-size: 7px; color: #64748b;">{{ optional($attendance->attendanceTime)->name ?? 'Default Shift' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 700;">{{ $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-' }}</div>
                    </td>
                    <td style="text-align: center; font-weight: 800; color: #475569;">
                        {{ ($attendance->punch_in_time && !in_array($typeCode, ['week_off', 'absent', 'leave'], true)) ? strtoupper($attendance->work_mode ?? 'WFO') : '-' }}
                    </td>
                    <td style="text-align: center;">
                        {{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}
                    </td>
                    <td style="text-align: center;">
                        {{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}
                    </td>
                    <td style="text-align: center; color: #64748b;">
                        {{ $attendance->target_punch_out_time ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A') : '-' }}
                    </td>
                    <td style="text-align: center; font-weight: 600;">
                        {{ $attendance->gross_duration ?? 'N/A' }}
                    </td>
                    <td style="text-align: center; font-weight: 800; color: #4f46e5;">
                        {{ $attendance->net_duration ?? 'N/A' }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-{{ $typeCode }}">{{ $typeName }}</span>
                    </td>
                    <td>
                        @if(!empty($flags))
                            @foreach($flags as $flag)
                                <span class="flag-pill">{{ $flag }}</span>
                            @endforeach
                        @else
                            <span class="flag-clear">Clear</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 14px; color: #94a3b8; font-weight: 700;">
                        No attendance records found for the selected period/filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- REPORT FOOTER -->
    <table class="report-footer">
        <tr>
            <td style="border:0; padding:0;">
                Confidential &bull; HRMS System Generated Attendance Report
            </td>
            <td style="border:0; padding:0; text-align: right;">
                {{ branding_name() }} &bull; Total {{ count($attendances) }} Records
            </td>
        </tr>
    </table>

</body>
</html>
