<!DOCTYPE html>
<html>
<head>
    <title>Daily Attendance Report - {{ $date }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4b00e8; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #4b00e8; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        th { background-color: #f8f9fc; color: #4b00e8; font-weight: bold; text-align: left; padding: 10px; border: 1px solid #e3e6f0; text-transform: uppercase; }
        td { padding: 10px; border: 1px solid #e3e6f0; }
        tr:nth-child(even) { background-color: #fdfdff; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .bg-success { background-color: #e6f7e6; color: #28a745; }
        .bg-danger { background-color: #fceaea; color: #dc3545; }
        .bg-warning { background-color: #fff9db; color: #f08c00; }
        .bg-info { background-color: #e0f2fe; color: #0369a1; }
        .bg-dark { background-color: #f1f3f5; color: #495057; }
        .footer { text-align: right; font-size: 10px; color: #999; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
        .summary-box { margin-bottom: 20px; padding: 15px; background: #f8f9fc; border-radius: 8px; }
        .summary-item { display: inline-block; width: 23%; text-align: center; }
        .summary-item strong { display: block; font-size: 14px; color: #4b00e8; }
        .summary-item span { font-size: 10px; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ORBOSIS Globle Pvt Ltd</h1>
        <p>Daily Attendance Report: {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <strong>{{ $attendances->count() }}</strong>
            <span>Total Punches</span>
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
            <strong>{{ $attendances->where('work_type', 'WFH')->count() }}</strong>
            <span>Work From Home</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Punch In</th>
                <th>Punch Out</th>
                <th>Hours</th>
                <th>Status</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $att)
                <tr>
                    <td>{{ $att->user->employee->employee_id ?? 'N/A' }}</td>
                    <td><strong>{{ $att->user->name }}</strong></td>
                    <td>{{ $att->user->employee->department->name ?? '---' }}</td>
                    <td>{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '--:--' }}</td>
                    <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '--:--' }}</td>
                    <td>{{ $att->working_hours ?? '---' }}</td>
                    <td>
                        @php
                            $status = strtolower($att->status ?? 'present');
                            $class = 'bg-dark';
                            if(str_contains($status, 'present')) $class = 'bg-success';
                            if(str_contains($status, 'absent') || str_contains($status, 'leave')) $class = 'bg-danger';
                            if(str_contains($status, 'half')) $class = 'bg-warning';
                            if(str_contains($status, 'wfh')) $class = 'bg-info';
                        @endphp
                        <span class="badge {{ $class }}">{{ $att->status ?? 'Present' }}</span>
                    </td>
                    <td>{{ $att->work_type }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y, h:i A') }} | HRMS Attendance Report
    </div>
</body>
</html>
