@extends('layouts.print')

@section('_content')
<style>
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            background: #fff !important;
            color: #0f172a !important;
            font-size: 11px !important;
        }
        .no-print {
            display: none !important;
        }
    }
    
    .print-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 10px;
        font-family: system-ui, -apple-system, sans-serif;
    }

    .brand-title {
        font-size: 24px;
        font-weight: 900;
        color: #4f46e5;
        letter-spacing: -0.5px;
        margin: 0;
        text-transform: uppercase;
    }

    .sub-title {
        font-size: 14px;
        font-weight: 700;
        color: #475569;
        margin-top: 2px;
    }

    .kpi-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        text-align: center;
    }

    .kpi-val {
        font-size: 18px;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.1;
    }

    .kpi-lbl {
        font-size: 10px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        margin-top: 2px;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 11px;
    }

    .print-table th {
        background: #f1f5f9 !important;
        color: #1e293b !important;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.3px;
        padding: 9px 8px;
        border: 1px solid #cbd5e1 !important;
    }

    .print-table td {
        padding: 8px 8px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .badge-pill {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge-present { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-absent { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-half_day { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-leave { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .badge-punch_blocked { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .badge-default { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    .flag-tag {
        display: inline-block;
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #ffedd5;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        margin-right: 2px;
    }
</style>

<div class="print-container">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom style-border">
        <div>
            <div class="brand-title">{{ branding_name() }}</div>
            <div class="sub-title">Attendance & Work Performance Log Report</div>
        </div>
        <div class="text-right">
            <span class="badge badge-primary px-3 py-2" style="font-size: 12px; font-weight: 800; background: #4f46e5;">
                Period: {{ $periodLabel ?? 'All Records' }}
            </span>
            <div class="text-muted small mt-1 font-weight-bold">Generated: {{ now()->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    <!-- SUMMARY KPI ROW -->
    @php
        $totalCount = $attendances->count();
        $presentCount = $attendances->filter(fn($a) => optional($a->attendanceType)->code === 'present')->count();
        $lateCount = $attendances->where('is_late', true)->count();
        $earlyOutCount = $attendances->where('is_early_out', true)->count();
        $blockedCount = $attendances->filter(fn($a) => ($a->is_blocked || $a->is_punch_blocked))->count();
        $totalHours = round($attendances->sum('total_work_minutes') / 60, 1);
    @endphp

    <div class="row no-gutters mb-3" style="gap: 8px;">
        <div class="col kpi-box" style="border-left: 4px solid #4f46e5;">
            <div class="kpi-val">{{ $totalCount }}</div>
            <div class="kpi-lbl">Total Records</div>
        </div>
        <div class="col kpi-box" style="border-left: 4px solid #16a34a;">
            <div class="kpi-val text-success">{{ $presentCount }}</div>
            <div class="kpi-lbl">Present Days</div>
        </div>
        <div class="col kpi-box" style="border-left: 4px solid #f97316;">
            <div class="kpi-val" style="color: #ea580c;">{{ $lateCount }}</div>
            <div class="kpi-lbl">Late Marks</div>
        </div>
        <div class="col kpi-box" style="border-left: 4px solid #0284c7;">
            <div class="kpi-val text-info">{{ $earlyOutCount }}</div>
            <div class="kpi-lbl">Early Logout</div>
        </div>
        <div class="col kpi-box" style="border-left: 4px solid #dc2626;">
            <div class="kpi-val text-danger">{{ $blockedCount }}</div>
            <div class="kpi-lbl">Punch Blocked</div>
        </div>
        <div class="col kpi-box" style="border-left: 4px solid #8b5cf6;">
            <div class="kpi-val" style="color: #7c3aed;">{{ $totalHours }}h</div>
            <div class="kpi-lbl">Work Hours</div>
        </div>
    </div>

    <!-- MAIN TABLE -->
    <table class="table print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">#</th>
                <th>Employee Details</th>
                <th>Dept / Shift</th>
                <th>Date</th>
                <th class="text-center">Mode</th>
                <th class="text-center">Punch In</th>
                <th class="text-center">Punch Out</th>
                <th class="text-center">Target Out</th>
                <th class="text-center">Gross</th>
                <th class="text-center">Net</th>
                <th class="text-center">Status</th>
                <th>Flags / Remarks</th>
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
                    <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-weight-bold text-dark" style="font-size: 12px;">
                            {{ optional($attendance->user)->name ?? optional($attendance->employee)->display_name ?? 'N/A' }}
                        </div>
                        <div class="small text-muted font-weight-bold">
                            {{ optional($attendance->employee)->employee_code ?? 'N/A' }}
                        </div>
                    </td>
                    <td>
                        <div class="font-weight-bold text-dark">
                            {{ optional(optional($attendance->employee)->department)->name ?? 'Staff' }}
                        </div>
                        <div class="small text-muted">
                            {{ optional($attendance->attendanceTime)->name ?? 'Default Shift' }}
                        </div>
                    </td>
                    <td>
                        <div class="font-weight-bold">
                            {{ $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-' }}
                        </div>
                    </td>
                    <td class="text-center font-weight-bold text-secondary">
                        {{ strtoupper($attendance->work_mode ?? 'WFO') }}
                    </td>
                    <td class="text-center font-weight-bold">
                        {{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}
                    </td>
                    <td class="text-center font-weight-bold">
                        {{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}
                    </td>
                    <td class="text-center text-muted">
                        {{ $attendance->target_punch_out_time ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A') : '-' }}
                    </td>
                    <td class="text-center font-weight-bold">
                        {{ $attendance->gross_duration ?? 'N/A' }}
                    </td>
                    <td class="text-center font-weight-bold text-primary">
                        {{ $attendance->net_duration ?? 'N/A' }}
                    </td>
                    <td class="text-center">
                        <span class="badge-pill badge-{{ $typeCode }}">{{ $typeName }}</span>
                    </td>
                    <td>
                        @if(!empty($flags))
                            @foreach($flags as $flag)
                                <span class="flag-tag">{{ $flag }}</span>
                            @endforeach
                        @else
                            <span class="text-success font-weight-bold">&check; Clear</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center py-4 text-muted font-weight-bold">
                        No attendance records found for the selected period/filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top text-muted small font-weight-bold">
        <div>Confidential &bull; Generated by {{ branding_name() }} HRMS System</div>
        <div>Total {{ count($attendances) }} Records Printed</div>
    </div>
</div>
@endsection

@section('_script')
<script>
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 300);
    }
</script>
@endsection
