<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ branding_name() }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 7.5px;
            line-height: 1.25;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* HEADER SECTION */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: -0.3px;
        }

        .report-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #475569;
            margin-top: 2px;
        }

        /* KPI SUMMARY SECTION */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .kpi-cell {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            text-align: center;
            width: 16.6%;
        }

        .kpi-num {
            font-size: 11.5px;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.1;
        }

        .kpi-lbl {
            font-size: 6.5px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-top: 1px;
        }

        /* DATA TABLE SECTION */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            table-layout: fixed;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            padding: 4px 3px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }

        .data-table td {
            padding: 3px 3px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* STATUSES */
        .st-present { color: #15803d; font-weight: bold; }
        .st-absent { color: #b91c1c; font-weight: bold; }
        .st-half_day { color: #b45309; font-weight: bold; }
        .st-leave { color: #1d4ed8; font-weight: bold; }
        .st-blocked { color: #991b1b; font-weight: bold; }
        .st-default { color: #475569; font-weight: bold; }

        .flag-txt { color: #c2410c; font-weight: bold; font-size: 6.5px; }
        .flag-ok { color: #16a34a; font-weight: bold; }

        .page-break {
            page-break-after: always;
        }

        /* FOOTER */
        .footer-table {
            width: 100%;
            margin-top: 6px;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
            font-size: 6.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    @php
        $totalChunks = count($chunkedRows ?? [[]]);
        $brandName = branding_name();
        $genDate = now()->format('d M Y, h:i A');
    @endphp

    @forelse($chunkedRows as $chunkIndex => $chunk)
        @if($chunkIndex > 0)
            <div class="page-break"></div>
        @endif

        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td style="border:0; padding:0;">
                    <div class="company-name">{{ $brandName }}</div>
                    <div class="report-title">
                        Employee Attendance &amp; Work Summary Report
                        @if($totalChunks > 1)
                            <span style="font-size: 7.5px; color: #64748b; font-weight: normal;">(Page {{ $chunkIndex + 1 }} of {{ $totalChunks }})</span>
                        @endif
                    </div>
                </td>
                <td style="border:0; padding:0; text-align: right; color: #64748b; font-size: 7.5px;">
                    <div><strong>Period:</strong> {{ $periodLabel ?? 'All Records' }}</div>
                    <div>Generated: {{ $genDate }}</div>
                </td>
            </tr>
        </table>

        <!-- KPI SUMMARY ON FIRST PAGE -->
        @if($chunkIndex === 0 && isset($stats))
        <table class="kpi-table">
            <tr>
                <td class="kpi-cell" style="border-left: 3px solid #4f46e5;">
                    <div class="kpi-num">{{ $stats['total'] ?? 0 }}</div>
                    <div class="kpi-lbl">Total Records</div>
                </td>
                <td class="kpi-cell" style="border-left: 3px solid #16a34a;">
                    <div class="kpi-num" style="color: #16a34a;">{{ $stats['present'] ?? 0 }}</div>
                    <div class="kpi-lbl">Present</div>
                </td>
                <td class="kpi-cell" style="border-left: 3px solid #f97316;">
                    <div class="kpi-num" style="color: #ea580c;">{{ $stats['late'] ?? 0 }}</div>
                    <div class="kpi-lbl">Late Marks</div>
                </td>
                <td class="kpi-cell" style="border-left: 3px solid #0284c7;">
                    <div class="kpi-num" style="color: #0284c7;">{{ $stats['early_out'] ?? 0 }}</div>
                    <div class="kpi-lbl">Early Logout</div>
                </td>
                <td class="kpi-cell" style="border-left: 3px solid #dc2626;">
                    <div class="kpi-num" style="color: #dc2626;">{{ $stats['blocked'] ?? 0 }}</div>
                    <div class="kpi-lbl">Punch Blocked</div>
                </td>
                <td class="kpi-cell" style="border-left: 3px solid #8b5cf6;">
                    <div class="kpi-num" style="color: #7c3aed;">{{ $stats['total_hours'] ?? 0 }}h</div>
                    <div class="kpi-lbl">Total Work Hours</div>
                </td>
            </tr>
        </table>
        @endif

        <!-- DATA TABLE -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18px; text-align: center;">#</th>
                    <th style="width: 105px;">Employee</th>
                    <th style="width: 75px;">Dept / Shift</th>
                    <th style="width: 50px;">Date</th>
                    <th style="width: 28px; text-align: center;">Mode</th>
                    <th style="width: 40px; text-align: center;">Punch In</th>
                    <th style="width: 40px; text-align: center;">Punch Out</th>
                    <th style="width: 40px; text-align: center;">Target Out</th>
                    <th style="width: 35px; text-align: center;">Gross</th>
                    <th style="width: 35px; text-align: center;">Net</th>
                    <th style="width: 50px; text-align: center;">Status</th>
                    <th style="width: 80px;">Reason</th>
                    <th style="width: 60px;">Flags</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chunk as $r)
                    @php
                        $stClass = 'st-default';
                        if ($r['status_code'] === 'present') $stClass = 'st-present';
                        elseif ($r['status_code'] === 'absent') $stClass = 'st-absent';
                        elseif ($r['status_code'] === 'half_day') $stClass = 'st-half_day';
                        elseif ($r['status_code'] === 'leave') $stClass = 'st-leave';
                        elseif (in_array($r['status_code'], ['punch_blocked', 'blocked'], true)) $stClass = 'st-blocked';
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $r['sno'] }}</td>
                        <td>
                            <strong>{{ $r['emp_name'] }}</strong><br>
                            <span style="color: #64748b; font-size: 6.5px;">{{ $r['emp_code'] }}</span>
                        </td>
                        <td>
                            <strong>{{ $r['dept'] }}</strong><br>
                            <span style="color: #64748b; font-size: 6.5px;">{{ $r['shift'] }}</span>
                        </td>
                        <td>{{ $r['date'] }}</td>
                        <td style="text-align: center;">{{ $r['mode'] }}</td>
                        <td style="text-align: center;">{{ $r['punch_in'] }}</td>
                        <td style="text-align: center;">{{ $r['punch_out'] }}</td>
                        <td style="text-align: center; color: #64748b;">{{ $r['target_out'] }}</td>
                        <td style="text-align: center;">{{ $r['gross'] }}</td>
                        <td style="text-align: center; font-weight: bold; color: #4f46e5;">{{ $r['net'] }}</td>
                        <td style="text-align: center;"><span class="{{ $stClass }}">{{ $r['status_name'] }}</span></td>
                        <td style="font-size: 6.5px; color: #475569;">{{ $r['reason'] }}</td>
                        <td>
                            @if($r['flags'] === 'Clear')
                                <span class="flag-ok">Clear</span>
                            @else
                                <span class="flag-txt">{{ $r['flags'] }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" style="text-align: center; padding: 12px; color: #94a3b8;">
                            No attendance records found for the selected period/filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- FOOTER -->
        <table class="footer-table">
            <tr>
                <td style="border:0; padding:0;">
                    Confidential &bull; HRMS System Generated Attendance Report
                </td>
                <td style="border:0; padding:0; text-align: right;">
                    {{ $brandName }} &bull; Page {{ $chunkIndex + 1 }} of {{ $totalChunks }}
                </td>
            </tr>
        </table>
    @empty
        <div style="text-align: center; padding: 20px; font-size: 10px; color: #64748b;">
            No attendance records found for the selected period.
        </div>
    @endforelse

</body>
</html>
