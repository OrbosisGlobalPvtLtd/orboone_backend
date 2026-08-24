@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Team Attendance Matrix')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: rgba(75, 0, 232, 0.06);
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.prj-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.prj-container {
    max-width: 1550px;
    margin: 0 auto;
}

.prj-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    overflow: hidden;
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-user-check mr-2"></i>Team Attendance Matrix</h1>
            <p class="mb-0 opacity-90">Project-scoped attendance records for Team Leads and Delivery Heads.</p>
        </div>

        <!-- Filter Card -->
        <div class="prj-card p-4 mb-4">
            <form method="GET" action="{{ route('projects.team.attendance') }}" class="form-row align-items-center">
                <div class="col-md-4 my-1">
                    <label class="small font-weight-bold text-muted">Attendance Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-4 my-1">
                    <label class="small font-weight-bold text-muted">Work Mode</label>
                    <select name="work_mode" class="form-control">
                        <option value="">-- All Work Modes --</option>
                        <option value="wfo" {{ request('work_mode') == 'wfo' ? 'selected' : '' }}>Work From Office (WFO)</option>
                        <option value="wfh" {{ request('work_mode') == 'wfh' ? 'selected' : '' }}>Work From Home (WFH)</option>
                    </select>
                </div>
                <div class="col-md-4 my-1 text-right align-self-end">
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">
                        <i class="fas fa-search mr-1"></i> View Attendance
                    </button>
                    <a href="{{ route('projects.team.attendance') }}" class="btn btn-light border px-3 ml-1" style="border-radius: 10px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Attendance Table -->
        <div class="prj-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>HR Designation</th>
                            <th>Status</th>
                            <th>Work Mode</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                        <tr>
                            <td><span class="small font-weight-bold text-dark">{{ $att->attendance_date->format('d M Y') }}</span></td>
                            <td>
                                <strong class="text-dark">{{ optional($att->employee)->display_name }}</strong>
                                <div class="small text-muted">{{ optional($att->employee)->employee_code }}</div>
                            </td>
                            <td><span class="small text-muted">{{ optional(optional($att->employee)->designation)->name ?? 'N/A' }}</span></td>
                            <td>
                                <span class="badge badge-{{ $att->punch_in_time ? 'success' : 'danger' }} text-uppercase px-2 py-1">
                                    {{ $att->status_name }}
                                </span>
                                @if($att->is_late)<span class="badge badge-warning text-uppercase ml-1">LATE</span>@endif
                                @if($att->is_half_day)<span class="badge badge-secondary text-uppercase ml-1">HALF DAY</span>@endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $att->work_mode == 'wfh' ? 'info' : 'primary' }} text-uppercase px-2 py-1">
                                    {{ strtoupper($att->work_mode ?? 'wfo') }}
                                </span>
                            </td>
                            <td><span class="small font-weight-bold">{{ $att->punch_in_time ? $att->punch_in_time->format('h:i A') : 'N/A' }}</span></td>
                            <td><span class="small font-weight-bold">{{ $att->punch_out_time ? $att->punch_out_time->format('h:i A') : 'N/A' }}</span></td>
                            <td><span class="small font-weight-bold text-primary">{{ $att->duration }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No attendance records found for the selected team scope/date.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection
