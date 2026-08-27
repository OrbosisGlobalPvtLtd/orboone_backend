<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Report History - {{ $summary['employee_name'] }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #101828;
            background: #ffffff;
            font-size: 12px;
            line-height: 1.4;
        }
        .print-header {
            border-bottom: 2px solid #4B00E8;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .company-title {
            font-size: 20px;
            font-weight: 800;
            color: #4B00E8;
        }
        .emp-info-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
        .stats-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-box {
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            background: #FAFAFA;
        }
        .stat-box .val {
            font-size: 16px;
            font-weight: 800;
            color: #4B00E8;
        }
        .stat-box .lbl {
            font-size: 10px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
        }
        .table-print {
            width: 100%;
            border-collapse: collapse;
        }
        .table-print th {
            background: #F1F5F9 !important;
            color: #0F172A;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 10px;
            border: 1px solid #CBD5E1;
        }
        .table-print td {
            padding: 10px;
            border: 1px solid #E2E8F0;
            vertical-align: top;
            font-size: 11px;
        }
        .no-print {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Action Toolbar for screen viewing -->
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm font-weight-bold">
            <i class="fas fa-print mr-1"></i> Print / Save PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm font-weight-bold">
            <i class="fas fa-times mr-1"></i> Close Window
        </button>
    </div>

    <!-- Printable Document Header -->
    <div class="print-header">
        <div>
            <div class="company-title">OrboOne HRMS</div>
            <div class="text-muted font-weight-bold" style="font-size:12px;">Employee Daily Work Report History</div>
        </div>
        <div class="text-right">
            <div class="font-weight-bold">Printed On: {{ now()->format('d M Y, h:i A') }}</div>
            <div class="text-muted" style="font-size:11px;">Confidential Document</div>
        </div>
    </div>

    <!-- Employee Information Card -->
    <div class="emp-info-box">
        <div class="row">
            <div class="col-6">
                <strong>Employee Name:</strong> {{ $summary['employee_name'] }}<br>
                <strong>Employee Code:</strong> {{ $summary['employee_code'] }}
            </div>
            <div class="col-6 text-right">
                <strong>Department:</strong> {{ $summary['department'] }}<br>
                <strong>Designation:</strong> {{ $summary['designation'] }}
            </div>
        </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="stats-summary-grid">
        <div class="stat-box">
            <div class="val">{{ $summary['total_reports'] }}</div>
            <div class="lbl">Reports Logged</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $summary['total_gross_formatted'] }}</div>
            <div class="lbl">Total Gross Hours</div>
        </div>
        <div class="stat-box">
            <div class="val">{{ $summary['total_tasks'] }}</div>
            <div class="lbl">Tasks Completed</div>
        </div>
    </div>

    <!-- Printable History Table -->
    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">S.No</th>
                <th style="width: 90px;">Date</th>
                <th style="width: 60px;">Mode</th>
                <th style="width: 100px;">Shift Context</th>
                <th style="width: 90px;">Gross Hours</th>
                <th>Work Summary & Tasks Logged</th>
            </tr>
        </thead>
        <tbody>
            @forelse($workLogs as $log)
            @php
                $attendance = $log->attendance;
                $mode = strtoupper(optional($attendance)->work_mode ?? 'WFO');
                $grossWork = optional($attendance)->gross_duration ?? '-';
                $tasks = $log->work_summary_json;
                if (is_string($tasks)) {
                    $tasks = json_decode($tasks, true);
                }
                
                $title = $log->work_summary ?: 'Work report submitted.';
                $taskList = [];
                if (is_array($tasks)) {
                    if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                        foreach ($tasks['projects'] as $p) {
                            if (isset($p['tasks']) && is_array($p['tasks'])) {
                                foreach ($p['tasks'] as $t) {
                                    $taskList[] = $t['task_name'] ?? $t['description'] ?? $t['title'] ?? 'Task';
                                }
                            }
                        }
                    } elseif (isset($tasks['requirements']) && is_array($tasks['requirements'])) {
                        foreach ($tasks['requirements'] as $req) {
                            $taskList[] = is_string($req) ? $req : ($req['text'] ?? $req['task'] ?? 'Task');
                        }
                    }
                }
            @endphp
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td><strong>{{ $log->work_date ? $log->work_date->format('d M Y') : '-' }}</strong></td>
                <td>{{ $mode }}</td>
                <td>{{ optional(optional($log->attendance)->attendanceTime)->name ?? 'Default Shift' }}</td>
                <td><strong>{{ $grossWork }}</strong></td>
                <td>
                    <div style="font-weight: 700; margin-bottom: 4px;">{{ $title }}</div>
                    @if(!empty($taskList))
                        <ul style="margin: 0; padding-left: 18px; font-size: 10.5px;">
                            @foreach($taskList as $tItem)
                                <li>{{ $tItem }}</li>
                            @endforeach
                        </ul>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">No work reports logged.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
