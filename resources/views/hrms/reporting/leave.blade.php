@extends('layouts.panel', ['active' => 'team_leave'])

@section('page_title', 'Team Leave')

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
                <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-plane-departure mr-2"></i>Team Leave</h3>
                <p class="mb-0 opacity-90 small">Visibility for leave applications, status, and history of your team members.</p>
            </div>
            <form method="GET" action="{{ route('reporting.leave') }}" class="form-inline flex-wrap gap-2">
                <select name="employee_id" class="form-control mr-2 mb-2" style="border-radius: 10px;">
                    <option value="">-- All Team Members --</option>
                    @foreach($teamEmployees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->display_name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-control mr-2 mb-2" style="border-radius: 10px;">
                    <option value="">-- All Statuses --</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <button type="submit" class="btn btn-light font-weight-bold mb-2" style="border-radius: 10px; color: var(--orb-primary);"><i class="fas fa-filter mr-1"></i> Filter</button>
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
                    <div class="rep-metric-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">On Leave Today</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $onLeaveTodayCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Approved Requests</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $approvedLeaveCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Pending Approvals</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $pendingLeaveCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Team Leave Status -->
        <div class="rep-card p-4 mb-4">
            <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-calendar-day text-primary mr-2"></i>Today's Team Leave Status ({{ \Carbon\Carbon::parse(date('Y-m-d'))->format('d M Y') }})</h6>
            <div class="d-flex flex-wrap gap-2">
                @forelse($todayLeaves as $tl)
                    <span class="badge badge-warning px-3 py-2 font-weight-bold" style="border-radius: 20px; background-color: #FEF3C7; color: #D97706; border: 1px solid #FCD34D; font-size: 13px;">
                        <i class="fas fa-user-clock mr-1.5"></i> {{ $tl->display_name }} &bull; {{ $tl->leave_type_name ?? 'Leave' }}
                    </span>
                @empty
                    <span class="text-muted small"><i class="fas fa-check-circle text-success mr-1"></i>No team members are on leave today. All active members are available.</span>
                @endforelse
            </div>
        </div>

        <!-- Leave Requests Table -->
        <div class="rep-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">Department & Designation</th>
                            <th class="py-3">Leave Type</th>
                            <th class="py-3 text-center">Dates (From - To)</th>
                            <th class="py-3 text-center">Duration</th>
                            <th class="py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $lr)
                        <tr>
                            <!-- Employee -->
                            <td class="py-3 px-4 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ $lr->display_name }}</strong>
                                <small class="text-muted">{{ $lr->employee_code }}</small>
                            </td>

                            <!-- Department & Designation -->
                            <td class="py-3 align-middle">
                                <span class="badge badge-light border text-primary font-weight-bold px-2 py-0.5" style="border-radius: 6px;">{{ $lr->department_name ?? 'General' }}</span>
                                <small class="text-muted d-block mt-0.5">{{ $lr->designation_name ?? 'Employee' }}</small>
                            </td>

                            <!-- Leave Type -->
                            <td class="py-3 align-middle font-weight-bold text-dark">
                                <span class="badge badge-light border px-2.5 py-1" style="border-radius: 8px; color: #4338CA; background: #EEF2FF;">
                                    {{ $lr->leave_type_name ?? 'Leave' }}
                                </span>
                            </td>

                            <!-- Dates -->
                            <td class="py-3 align-middle text-center font-weight-bold text-dark">
                                {{ \Carbon\Carbon::parse($lr->start_date)->format('d M Y') }}
                                <span class="text-muted mx-1">&rarr;</span>
                                {{ \Carbon\Carbon::parse($lr->end_date)->format('d M Y') }}
                            </td>

                            <!-- Duration -->
                            <td class="py-3 align-middle text-center font-weight-bold text-primary">
                                {{ number_format($lr->days ?? 1, 2) }} Day(s)
                            </td>

                            <!-- Status -->
                            <td class="py-3 align-middle text-center">
                                @php
                                    $st = strtolower($lr->status ?? 'approved');
                                @endphp
                                @if($st === 'approved')
                                    <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 8px;">Approved</span>
                                @elseif($st === 'pending')
                                    <span class="badge badge-warning px-3 py-1 font-weight-bold" style="border-radius: 8px; background-color: #FEF3C7; color: #D97706; border: 1px solid #FCD34D;">Pending</span>
                                @else
                                    <span class="badge badge-danger px-3 py-1 font-weight-bold" style="border-radius: 8px;">{{ ucfirst($st) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-minus fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Leave Requests Found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($leaveRequests->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $leaveRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
