@extends('layouts.panel', ['active' => 'leave_management'])

@section('page_title', 'Leave Management Dashboard')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#8600EE' }};
    --orb-primary-hover: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-bg: #F8FAFC;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: rgba(75, 0, 232, 0.08);
}

body {
    background: var(--orb-bg) !important;
    overflow-x: hidden !important;
}

.ld-page {
    padding: 24px 20px 48px;
}

.ld-container {
    max-width: 1550px;
    margin: 0 auto;
}

.ld-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#8600EE' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #fff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.ld-hero h1 {
    font-size: 26px;
    font-weight: 900;
    margin: 0;
    color: #fff;
    letter-spacing: -0.02em;
}

.ld-hero p {
    margin: 6px 0 0;
    font-size: 13.5px;
    opacity: 0.92;
}

.ld-hero-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.ld-stat-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.ld-stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 20px;
    border: 1px solid var(--orb-border);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.ld-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}

.ld-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.ld-stat-card.pending .ld-stat-icon { background: #FFFBEB; color: #F59E0B; }
.ld-stat-card.today .ld-stat-icon { background: #EEF2FF; color: var(--orb-primary); }
.ld-stat-card.approved .ld-stat-icon { background: #ECFDF5; color: #10B981; }
.ld-stat-card.lwp .ld-stat-icon { background: #FEF2F2; color: #EF4444; }
.ld-stat-card.allocated .ld-stat-icon { background: #F1F5F9; color: #64748B; }

.ld-stat-val {
    font-size: 22px;
    font-weight: 900;
    color: var(--orb-text);
    line-height: 1.1;
}

.ld-stat-lbl {
    font-size: 11px;
    font-weight: 800;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 3px;
}

.ld-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid var(--orb-border);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    margin-bottom: 24px;
    overflow: hidden;
}

.ld-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #FAF9FF;
}

.ld-card-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ld-card-body {
    padding: 20px 24px;
}

.ld-table {
    width: 100% !important;
    margin: 0 !important;
}

.ld-table thead th {
    background: #F8FAFC !important;
    color: var(--orb-muted) !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 12px 14px !important;
    border-bottom: 1px solid var(--orb-border) !important;
}

.ld-table td {
    padding: 12px 14px !important;
    vertical-align: middle !important;
    font-size: 13px;
    border-bottom: 1px solid #F1F5F9;
}

.orb-badge {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.orb-badge-success { background: #ECFDF5; color: #047857; }
.orb-badge-warning { background: #FFFBEB; color: #B45309; }
.orb-badge-danger { background: #FEF2F2; color: #B91C1C; }
.orb-badge-secondary { background: #F1F5F9; color: #475569; }

.holiday-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px;
    border-radius: 12px;
    background: #FAFAFA;
    border: 1px solid var(--orb-border);
    margin-bottom: 10px;
}

.holiday-item:last-child {
    margin-bottom: 0;
}

.holiday-date-badge {
    width: 44px;
    height: 44px;
    background: var(--orb-soft);
    color: var(--orb-primary);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    flex-shrink: 0;
}

.holiday-date-badge .day { font-size: 14px; line-height: 1; }
.holiday-date-badge .month { font-size: 9px; text-transform: uppercase; line-height: 1; margin-top: 2px; }

.leave-type-pill {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-radius: 10px;
    background: #F8FAFC;
    border: 1px solid var(--orb-border);
    margin-bottom: 8px;
}

/* RESPONSIVE BREAKPOINTS */
@media (max-width: 1200px) {
    .ld-stat-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 992px) {
    .ld-hero { flex-direction: column; align-items: flex-start; }
    .ld-hero-actions { width: 100%; justify-content: flex-start; }
}
@media (max-width: 768px) {
    .ld-stat-grid { grid-template-columns: repeat(2, 1fr); }
    .ld-page { padding: 16px 12px 32px; }
    .ld-hero { padding: 20px; border-radius: 16px; }
    .ld-hero h1 { font-size: 22px; }
}
@media (max-width: 576px) {
    .ld-stat-grid { grid-template-columns: 1fr; }
    .ld-hero-actions .btn { width: 100%; text-align: center; justify-content: center; }
}
</style>
@endsection

@section('_content')
<div class="ld-page">
    <div class="ld-container">

        <!-- Hero Banner with Dynamic Branding -->
        <div class="ld-hero">
            <div>
                <div style="font-size: 11px; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase; opacity: 0.9; margin-bottom: 6px;">
                    <i class="fas fa-chart-pie mr-1"></i> HRMS &bull; LEAVE MANAGEMENT WORKBENCH
                </div>
                <h1>Leave Management Dashboard</h1>
                <p>Real-time overview of organization leave volume, pending approvals, employees on leave, and holidays.</p>
            </div>
            <div class="ld-hero-actions">
                @if(!$isEmployeeRole)
                <a href="{{ route('leave-approvals.index') }}" class="btn font-weight-bold shadow-sm" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.35); border-radius: 12px; padding: 10px 22px; font-size: 13.5px; font-weight: 800; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
                    <i class="fas fa-check-circle mr-1"></i> Approvals Workbench
                </a>
                @endif
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Stat Cards Grid -->
        <div class="ld-stat-grid">
            <div class="ld-stat-card pending">
                <div class="ld-stat-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="ld-stat-val">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="ld-stat-lbl">Pending Approvals</div>
                </div>
            </div>
            <div class="ld-stat-card today">
                <div class="ld-stat-icon"><i class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="ld-stat-val">{{ $stats['on_leave_today'] ?? 0 }}</div>
                    <div class="ld-stat-lbl">On Leave Today</div>
                </div>
            </div>
            <div class="ld-stat-card approved">
                <div class="ld-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="ld-stat-val">{{ $stats['approved_this_month'] ?? 0 }}</div>
                    <div class="ld-stat-lbl">Approved (This Month)</div>
                </div>
            </div>
            <div class="ld-stat-card lwp">
                <div class="ld-stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="ld-stat-val">{{ $stats['lwp_this_month'] ?? 0 }}</div>
                    <div class="ld-stat-lbl">LWP Days</div>
                </div>
            </div>
            <div class="ld-stat-card allocated">
                <div class="ld-stat-icon"><i class="fas fa-users"></i></div>
                <div>
                    <div class="ld-stat-val">{{ $stats['allocated_employees'] ?? 0 }}</div>
                    <div class="ld-stat-lbl">Allocated Staff</div>
                </div>
            </div>
        </div>

        <!-- Main Multi-Column Grid -->
        <div class="row">
            <!-- Left 8 Columns: Tables & Activity -->
            <div class="col-xl-8 col-lg-8 col-12">

                <!-- On Leave Today Panel (Only rendered if data exists) -->
                @if(!empty($onLeaveTodayList) && count($onLeaveTodayList) > 0)
                <div class="ld-card">
                    <div class="ld-card-header">
                        <h5 class="ld-card-title">
                            <i class="fas fa-user-clock text-warning"></i> Employees On Leave Today
                        </h5>
                        <span class="badge badge-warning p-2 font-weight-bold" style="border-radius: 8px;">
                            {{ count($onLeaveTodayList) }} On Leave
                        </span>
                    </div>
                    <div class="ld-card-body">
                        <div class="row">
                            @foreach($onLeaveTodayList as $onLeave)
                            @php
                                $empName = optional(optional($onLeave->employee)->user)->name ?? optional($onLeave->employee)->display_name ?? 'Employee';
                                $deptName = optional(optional($onLeave->employee)->department)->name ?? 'N/A';
                                $photoUrl = resolveEmployeePassportPhoto($onLeave->employee_id);
                                $initials = resolveEmployeeInitials($onLeave->employee_id);
                            @endphp
                            <div class="col-md-6 col-12 mb-3">
                                <div class="p-3 rounded-lg border bg-white d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        @if($photoUrl)
                                            <img src="{{ $photoUrl }}" class="rounded-circle mr-3" style="width:40px; height:40px; object-fit:cover;" alt="">
                                        @else
                                            <div class="rounded-circle mr-3 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; background: var(--orb-soft); color: var(--orb-primary); font-weight: 900; font-size: 13px;">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-weight-bold text-dark">{{ $empName }}</div>
                                            <div class="text-muted small">{{ $deptName }} &bull; <span class="text-primary font-weight-bold">{{ optional($onLeave->leaveType)->name }}</span></div>
                                        </div>
                                    </div>
                                    <span class="badge badge-light border text-muted small font-weight-bold">
                                        Until {{ \Carbon\Carbon::parse($onLeave->end_date)->format('d M') }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Recent Leave Applications Table -->
                <div class="ld-card">
                    <div class="ld-card-header">
                        <h5 class="ld-card-title">
                            <i class="fas fa-history text-primary"></i> Active Leave Applications
                        </h5>
                        <a href="{{ route('hrms.leave.history') }}" class="btn btn-sm btn-light border font-weight-bold" style="border-radius: 8px;">
                            View Audit Log <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="ld-card-body p-0">
                        <div class="table-responsive">
                            <table class="ld-table table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Period</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentRequests as $req)
                                    @php
                                        $empName = optional(optional($req->employee)->user)->name ?? optional($req->employee)->display_name ?? 'Employee';
                                        $deptName = optional(optional($req->employee)->department)->name ?? 'N/A';
                                        $photoUrl = resolveEmployeePassportPhoto($req->employee_id);
                                        $initials = resolveEmployeeInitials($req->employee_id);
                                        $days = $req->total_days ?? $req->days ?? 1;

                                        $statusBadge = $req->status === 'approved'
                                            ? 'orb-badge-success'
                                            : ($req->status === 'pending'
                                                ? 'orb-badge-warning'
                                                : ($req->status === 'rejected'
                                                    ? 'orb-badge-danger'
                                                    : 'orb-badge-secondary'));
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($photoUrl)
                                                    <img src="{{ $photoUrl }}" class="rounded-circle mr-2" style="width:32px; height:32px; object-fit:cover;" alt="">
                                                @else
                                                    <div class="rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width:32px; height:32px; background: var(--orb-soft); color: var(--orb-primary); font-weight: 900; font-size: 11px;">
                                                        {{ $initials }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-weight-bold text-dark" style="line-height: 1.2;">{{ $empName }}</div>
                                                    <div class="text-muted small" style="font-size: 11px;">{{ $deptName }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-primary">{{ optional($req->leaveType)->name ?? 'Paid Leave' }}</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }}</span>
                                            <span class="text-muted small">&rarr;</span>
                                            <span class="font-weight-bold">{{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border font-weight-bold px-2 py-1">{{ $days }} D</span>
                                        </td>
                                        <td>
                                            <span class="orb-badge {{ $statusBadge }}">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            @if($req->status === 'pending' && !$isEmployeeRole)
                                            <a href="{{ route('leave-approvals.index') }}" class="btn btn-sm btn-warning font-weight-bold" style="border-radius: 8px; font-size: 11px;">
                                                <i class="fas fa-check mr-1"></i> Review
                                            </a>
                                            @else
                                            <a href="{{ route('hrms.leave.history') }}" class="btn btn-sm btn-light border" style="border-radius: 8px; font-size: 11px;">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                            No recent leave applications found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 4 Columns: Widgets & Quick Actions -->
            <div class="col-xl-4 col-lg-4 col-12">
                <!-- Leave Categories Widget -->
                <div class="ld-card">
                    <div class="ld-card-header">
                        <h5 class="ld-card-title">
                            <i class="fas fa-layer-group text-info"></i> Leave Categories
                        </h5>
                        @if(!$isEmployeeRole)
                        <a href="{{ route('hrms.leave.types.index') }}" class="small font-weight-bold">Manage</a>
                        @endif
                    </div>
                    <div class="ld-card-body">
                        @forelse($leaveTypes as $lt)
                        <div class="leave-type-pill">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tag text-primary mr-2"></i>
                                <span class="font-weight-bold text-dark">{{ $lt->name }}</span>
                            </div>
                            <span class="badge badge-light border font-weight-bold">
                                {{ $lt->max_days_per_year ?? $lt->max_days ?? 'Flexible' }} Days/Yr
                            </span>
                        </div>
                        @empty
                        <div class="text-muted text-center py-2">No leave types configured.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Upcoming Holidays Widget -->
                <div class="ld-card">
                    <div class="ld-card-header">
                        <h5 class="ld-card-title">
                            <i class="fas fa-umbrella-beach text-danger"></i> Upcoming Holidays
                        </h5>
                        <a href="{{ route('hrms.holidays.index') }}" class="small font-weight-bold">View All</a>
                    </div>
                    <div class="ld-card-body">
                        @forelse($upcomingHolidays as $hol)
                        @php
                            $holDate = \Carbon\Carbon::parse($hol->holiday_date);
                        @endphp
                        <div class="holiday-item">
                            <div class="holiday-date-badge">
                                <span class="day">{{ $holDate->format('d') }}</span>
                                <span class="month">{{ $holDate->format('M') }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold text-dark" style="line-height: 1.2;">{{ $hol->name }}</div>
                                <div class="text-muted small">{{ $holDate->format('l, Y') }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-muted text-center py-3">No upcoming holidays scheduled.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Navigation Shortcuts -->
                <div class="ld-card">
                    <div class="ld-card-header">
                        <h5 class="ld-card-title">
                            <i class="fas fa-compass text-secondary"></i> Quick Navigation
                        </h5>
                    </div>
                    <div class="ld-card-body">
                        <div class="d-grid gap-2" style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="{{ route('employees-leave-request.summary') }}" class="btn btn-light border text-left font-weight-bold d-flex align-items-center justify-content-between" style="border-radius: 10px; padding: 10px 14px;">
                                <span><i class="fas fa-wallet text-secondary mr-2"></i> Leave Balance Tracker</span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </a>
                            <a href="{{ route('leave-allocations.index') }}" class="btn btn-light border text-left font-weight-bold d-flex align-items-center justify-content-between" style="border-radius: 10px; padding: 10px 14px;">
                                <span><i class="fas fa-coins text-warning mr-2"></i> Leave Allocations</span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </a>
                            <a href="{{ route('hrms.leave.team_calendar.index') }}" class="btn btn-light border text-left font-weight-bold d-flex align-items-center justify-content-between" style="border-radius: 10px; padding: 10px 14px;">
                                <span><i class="fas fa-calendar-alt text-primary mr-2"></i> Team Leave Calendar</span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('_script')
<script>
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection