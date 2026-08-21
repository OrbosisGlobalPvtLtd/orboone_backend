@extends('layouts.panel', ['active' => 'team_attendance'])

@section('page_title', 'Team Attendance')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.rep-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.rep-container {
    max-width: 1550px;
    margin: 0 auto;
}

.rep-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.rep-metric-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: var(--orb-shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    height: 100%;
}

.rep-metric-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header -->
        <div class="rep-hero">
            <div>
                <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-calendar-check mr-2"></i>Team Attendance</h3>
                <p class="mb-0 opacity-90 small">Daily attendance punch records, work modes, and working hours for your team members.</p>
            </div>
            <form method="GET" action="{{ route('reporting.attendance') }}" class="form-inline flex-wrap gap-2">
                <select name="employee_id" class="form-control mr-2 mb-2" style="border-radius: 10px;">
                    <option value="">-- All Team Members --</option>
                    @foreach($teamEmployees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->display_name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date" class="form-control mr-2 mb-2" value="{{ $date }}" style="border-radius: 10px;">
                <button type="submit" class="btn btn-light font-weight-bold mb-2" style="border-radius: 10px; color: var(--orb-primary);"><i class="fas fa-filter mr-1"></i> Filter Date</button>
            </form>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row mb-4">
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(75, 0, 232, 0.08); color: #4B00E8;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Total Team</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $totalTeamCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Present Today</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $presentCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">On Leave</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $onLeaveCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Not Punched In</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $notPunchedCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rep-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">Department & Designation</th>
                            <th class="py-3 text-center">Punch In</th>
                            <th class="py-3 text-center">Punch Out</th>
                            <th class="py-3 text-center">Working Hours</th>
                            <th class="py-3 text-center">Work Mode</th>
                            <th class="py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeesPaginator as $emp)
                        @php
                            $att = $emp->attendance_record;
                            $leave = $emp->leave_record;
                        @endphp
                        <tr>
                            <!-- Employee -->
                            <td class="py-3 px-4 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ $emp->display_name }}</strong>
                                <small class="text-muted">{{ $emp->employee_code }}</small>
                            </td>

                            <!-- Department & Designation -->
                            <td class="py-3 align-middle">
                                <span class="badge badge-light border text-primary font-weight-bold px-2 py-0.5" style="border-radius: 6px;">{{ optional($emp->department)->name ?? 'General' }}</span>
                                <small class="text-muted d-block mt-0.5">{{ optional($emp->designation)->name ?? 'Employee' }}</small>
                            </td>

                            <!-- Punch In -->
                            <td class="py-3 align-middle text-center font-weight-bold text-dark">
                                @if($att && $att->punch_in_time)
                                    {{ \Carbon\Carbon::parse($att->punch_in_time)->format('h:i A') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- Punch Out -->
                            <td class="py-3 align-middle text-center font-weight-bold text-dark">
                                @if($att && $att->punch_out_time)
                                    {{ \Carbon\Carbon::parse($att->punch_out_time)->format('h:i A') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- Working Hours -->
                            <td class="py-3 align-middle text-center font-weight-bold text-primary">
                                @if($att && $att->punch_in_time && $att->punch_out_time)
                                    @php
                                        $in = \Carbon\Carbon::parse($att->punch_in_time);
                                        $out = \Carbon\Carbon::parse($att->punch_out_time);
                                        $diffMinutes = $in->diffInMinutes($out);
                                        $hours = floor($diffMinutes / 60);
                                        $mins = $diffMinutes % 60;
                                    @endphp
                                    {{ $hours }}h {{ $mins }}m
                                @elseif($att && $att->punch_in_time)
                                    <span class="text-warning small font-weight-bold">Active Session</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- Work Mode -->
                            <td class="py-3 align-middle text-center">
                                @if($att)
                                    <span class="badge badge-light border text-primary px-3 py-1 font-weight-bold" style="border-radius: 8px;">{{ strtoupper($att->work_type ?? 'WFO') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 align-middle text-center">
                                @if($leave)
                                    <span class="badge badge-warning px-3 py-1 font-weight-bold" style="border-radius: 8px; background-color: #FEF3C7; color: #D97706; border: 1px solid #FCD34D;">
                                        ON LEAVE
                                    </span>
                                @elseif($att)
                                    <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 8px;">
                                        {{ strtoupper($att->status ?? 'PRESENT') }}
                                    </span>
                                @else
                                    <span class="badge badge-light border text-muted px-2.5 py-1" style="border-radius: 8px;">
                                        Not Punched In
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Team Attendance Records Found for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employeesPaginator->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $employeesPaginator->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
