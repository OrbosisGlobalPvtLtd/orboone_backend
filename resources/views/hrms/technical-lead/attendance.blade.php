@extends('layouts.panel', ['active' => 'technical_lead_attendance'])

@section('page_title', 'Technical Lead Attendance')

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
    --orb-soft: rgba(75, 0, 232, 0.08);
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.tl-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.tl-container {
    max-width: 1550px;
    margin: 0 auto;
}

.tl-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.tl-card {
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
<div class="tl-page">
    <div class="tl-container">
        <!-- Hero Header -->
        <div class="tl-hero">
            <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-calendar-check mr-2"></i>Technical Lead Attendance</h1>
            <p class="mb-0 opacity-90">View attendance, punch times, and work locations for supervised developers.</p>
        </div>

        <!-- Filter Card -->
        <div class="tl-card p-4">
            <form method="GET" action="{{ route('technical_lead.attendance') }}" class="form-row align-items-center">
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">From Date</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">To Date</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">Attendance Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All Statuses --</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    </select>
                </div>
                <div class="col-md-3 my-1 text-right align-self-end">
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('technical_lead.attendance') }}" class="btn btn-light border px-3 ml-1" style="border-radius: 10px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Attendance Table -->
        <div class="tl-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3">Employee</th>
                            <th class="py-3">HR Designation</th>
                            <th class="py-3">Punch In</th>
                            <th class="py-3">Punch Out</th>
                            <th class="py-3">Work Mode</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                        <tr>
                            <td class="py-3 px-4">
                                <span class="badge badge-light border font-weight-bold px-3 py-1 text-dark" style="border-radius: 8px;">
                                    {{ $att->attendance_date ? $att->attendance_date->format('d M Y') : 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <strong class="text-dark font-weight-bold">{{ optional($att->employee)->display_name }}</strong>
                                <div class="small text-muted">{{ optional($att->employee)->employee_code }}</div>
                            </td>
                            <td class="py-3"><span class="small text-muted">{{ optional(optional($att->employee)->designation)->name ?? 'N/A' }}</span></td>
                            <td class="py-3"><span class="small font-weight-bold text-success">{{ $att->punch_in ? \Carbon\Carbon::parse($att->punch_in)->format('h:i A') : 'N/A' }}</span></td>
                            <td class="py-3"><span class="small font-weight-bold text-danger">{{ $att->punch_out ? \Carbon\Carbon::parse($att->punch_out)->format('h:i A') : 'N/A' }}</span></td>
                            <td class="py-3">
                                <span class="badge badge-light border font-weight-bold text-primary px-3 py-1" style="border-radius: 8px;">
                                    {{ $att->work_type ?? 'WFO' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($att->status == 'present')
                                    <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 8px;">Present</span>
                                @elseif($att->status == 'absent')
                                    <span class="badge badge-danger px-3 py-1 font-weight-bold" style="border-radius: 8px;">Absent</span>
                                @else
                                    <span class="badge badge-warning px-3 py-1 font-weight-bold" style="border-radius: 8px;">{{ ucfirst($att->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No attendance records found for supervised developers.</td>
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
