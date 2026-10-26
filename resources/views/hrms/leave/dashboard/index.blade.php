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
    background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 35%),
                linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#8600EE' }} 100%);
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #fff;
    box-shadow: 0 14px 35px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.ld-hero::after {
    content: '';
    position: absolute;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    right: -70px;
    bottom: -110px;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
    z-index: 1;
}

.ld-hero-content {
    position: relative;
    z-index: 2;
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
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

/* 5-Column Theme Metric Grid & Cards (Matching OrboOne Design System) */
.leave-metric-grid,
.ld-stat-grid {
    display: grid !important;
    grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
    gap: 16px !important;
    margin-bottom: 24px !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.leave-metric-card {
    background: #FFFFFF !important;
    border: 1px solid #E7EAF3 !important;
    border-radius: 16px !important;
    padding: 14px 16px 12px !important;
    box-shadow: 0 10px 24px rgba(16, 24, 40, .055) !important;
    position: relative !important;
    overflow: hidden !important;
    min-height: 92px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.leave-metric-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 14px 30px rgba(16, 24, 40, .09) !important;
}

.leave-metric-card::after {
    content: "" !important;
    position: absolute !important;
    right: -22px !important;
    top: -30px !important;
    width: 82px !important;
    height: 82px !important;
    border-radius: 50% !important;
    background: var(--metric-soft, #F4F2FF) !important;
    pointer-events: none !important;
    z-index: 0 !important;
}

.leave-metric-card .leave-metric-top {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    position: relative !important;
    z-index: 1 !important;
    width: 100% !important;
}

.leave-metric-card .leave-metric-icon {
    width: 38px !important;
    height: 38px !important;
    border-radius: 12px !important;
    background: var(--metric-soft, #F4F2FF) !important;
    color: var(--metric-color, #7C3AED) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 16px !important;
    flex-shrink: 0 !important;
}

.leave-metric-card .leave-metric-value {
    font-size: 26px !important;
    font-weight: 950 !important;
    color: #101828 !important;
    line-height: 1 !important;
    letter-spacing: -0.02em !important;
    text-align: right !important;
}

.leave-metric-card .leave-metric-label {
    font-size: 11px !important;
    font-weight: 900 !important;
    color: #475467 !important;
    text-transform: uppercase !important;
    margin-top: 12px !important;
    position: relative !important;
    z-index: 1 !important;
    letter-spacing: 0.03em !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.leave-metric-card .leave-metric-line {
    position: relative !important;
    height: 3px !important;
    border-radius: 999px !important;
    background: linear-gradient(90deg, var(--metric-color, #7C3AED), transparent) !important;
    margin-top: 8px !important;
    width: 100% !important;
    z-index: 1 !important;
}

/* Dashboard Cards */
.ld-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid var(--orb-border);
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
    margin-bottom: 24px;
    overflow: hidden;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.ld-card-header {
    padding: 16px 22px;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #FAF9FF;
}

.ld-card-title {
    font-size: 15px;
    font-weight: 850;
    color: #101828;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: -0.01em;
}

.ld-card-body {
    padding: 20px 22px;
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
    padding: 14px 16px !important;
    border-bottom: 1px solid var(--orb-border) !important;
}

.ld-table td {
    padding: 13px 16px !important;
    vertical-align: middle !important;
    font-size: 13px;
    border-bottom: 1px solid #F1F5F9;
}

.ld-table tbody tr {
    transition: background 0.15s ease;
}

.ld-table tbody tr:hover {
    background: #FAF8FF !important;
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

.orb-badge-success { background: #ECFDF5; color: #047857; border: 1px solid rgba(16, 185, 129, 0.2); }
.orb-badge-warning { background: #FFFBEB; color: #B45309; border: 1px solid rgba(245, 158, 11, 0.2); }
.orb-badge-danger { background: #FEF2F2; color: #B91C1C; border: 1px solid rgba(239, 68, 68, 0.2); }
.orb-badge-secondary { background: #F1F5F9; color: #475569; border: 1px solid rgba(100, 116, 139, 0.15); }

.holiday-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    background: #FFFFFF;
    border: 1px solid var(--orb-border);
    margin-bottom: 10px;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.holiday-item:last-child {
    margin-bottom: 0;
}

.holiday-item:hover {
    transform: translateY(-1px);
    border-color: #EF4444;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.08);
}

.holiday-date-badge {
    width: 44px;
    height: 44px;
    background: #FEF2F2;
    color: #EF4444;
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
    padding: 11px 14px;
    border-radius: 12px;
    background: #FFFFFF;
    border: 1px solid var(--orb-border);
    margin-bottom: 9px;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.leave-type-pill:last-child {
    margin-bottom: 0;
}

.leave-type-pill:hover {
    transform: translateY(-1px);
    border-color: var(--orb-primary);
    box-shadow: 0 4px 12px rgba(75, 0, 232, 0.06);
}

.quick-nav-btn {
    background: #FFFFFF;
    border: 1px solid var(--orb-border);
    border-radius: 12px;
    padding: 12px 14px;
    color: var(--orb-text);
    font-weight: 750;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.quick-nav-btn:hover {
    background: #FAF8FF;
    border-color: var(--orb-primary);
    color: var(--orb-primary);
    transform: translateX(3px);
    box-shadow: 0 4px 14px rgba(75, 0, 232, 0.07);
}

/* RESPONSIVE BREAKPOINTS */
@media (max-width: 1280px) {
    .leave-metric-grid,
    .ld-stat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
}
@media (max-width: 992px) {
    .ld-hero { flex-direction: column; align-items: flex-start; }
    .ld-hero-actions { width: 100%; justify-content: flex-start; }
}
@media (max-width: 768px) {
    .leave-metric-grid,
    .ld-stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
    .ld-page { padding: 16px 12px 32px; }
    .ld-hero { padding: 20px; border-radius: 16px; }
    .ld-hero h1 { font-size: 22px; }
}
@media (max-width: 480px) {
    .leave-metric-grid,
    .ld-stat-grid {
        grid-template-columns: 1fr !important;
    }
    .ld-hero-actions .btn { width: 100%; text-align: center; justify-content: center; }
}
</style>
@endsection

@section('_content')
<div class="ld-page">
    <div class="ld-container">

        <!-- Hero Banner with Dynamic Branding -->
        <div class="ld-hero">
            <div class="ld-hero-content">
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

        <!-- Stat Cards Grid (Brand Theme Metric Cards) -->
        <div class="leave-metric-grid ld-stat-grid">
            <!-- 1. Pending Approvals -->
            <div class="leave-metric-card" style="--metric-color:#F59E0B;--metric-soft:#FFFBEB;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Pending Approvals</div>
                <div class="leave-metric-line"></div>
            </div>

            <!-- 2. On Leave Today -->
            <div class="leave-metric-card" style="--metric-color:#7C3AED;--metric-soft:#F4F2FF;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['on_leave_today'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">On Leave Today</div>
                <div class="leave-metric-line"></div>
            </div>

            <!-- 3. Approved (This Month) -->
            <div class="leave-metric-card" style="--metric-color:#10B981;--metric-soft:#ECFDF5;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['approved_this_month'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Approved (This Month)</div>
                <div class="leave-metric-line"></div>
            </div>

            <!-- 4. LWP Days -->
            <div class="leave-metric-card" style="--metric-color:#EF4444;--metric-soft:#FEF2F2;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="leave-metric-value">{{ is_numeric($stats['lwp_this_month'] ?? 0) ? number_format((float) ($stats['lwp_this_month'] ?? 0), 2) : ($stats['lwp_this_month'] ?? 0) }}</div>
                </div>
                <div class="leave-metric-label">LWP Days</div>
                <div class="leave-metric-line"></div>
            </div>

            <!-- 5. Allocated Staff -->
            <div class="leave-metric-card" style="--metric-color:#2563EB;--metric-soft:#EFF6FF;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="leave-metric-value">{{ $stats['allocated_employees'] ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Allocated Staff</div>
                <div class="leave-metric-line"></div>
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
                                            <a href="{{ route('leave-approvals.index') }}" class="btn btn-sm font-weight-bold" style="border-radius: 8px; font-size: 11.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; padding: 4px 11px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-check-circle" style="font-size: 11px;"></i> Review
                                            </a>
                                            @else
                                            <a href="{{ route('hrms.leave.history') }}" class="btn btn-sm" style="border-radius: 8px; font-size: 11px; background: #F8FAFC; border: 1px solid var(--orb-border); color: var(--orb-primary); padding: 4px 9px;">
                                                <i class="fas fa-eye"></i>
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
                            <i class="fas fa-layer-group text-primary"></i> Leave Categories
                        </h5>
                        @if(!$isEmployeeRole)
                        <a href="{{ route('hrms.leave.types.index') }}" class="small font-weight-bold" style="color: var(--orb-primary);">Manage</a>
                        @endif
                    </div>
                    <div class="ld-card-body">
                        @forelse($leaveTypes as $lt)
                        <div class="leave-type-pill">
                            <div class="d-flex align-items-center">
                                <span class="mr-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 8px; background: var(--orb-soft); color: var(--orb-primary); font-size: 11px;">
                                    <i class="fas fa-tag"></i>
                                </span>
                                <span class="font-weight-bold text-dark">{{ $lt->name }}</span>
                            </div>
                            <span class="badge badge-light border font-weight-bold" style="font-size: 11px; color: var(--orb-muted); padding: 5px 9px; border-radius: 6px;">
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
                            <i class="fas fa-umbrella-beach" style="color: #EF4444;"></i> Upcoming Holidays
                        </h5>
                        <a href="{{ route('hrms.holidays.index') }}" class="small font-weight-bold" style="color: var(--orb-primary);">View All</a>
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
                                <div class="text-muted small" style="font-size: 11.5px; margin-top: 2px;">{{ $holDate->format('l, Y') }}</div>
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
                            <i class="fas fa-compass" style="color: var(--orb-primary);"></i> Quick Navigation
                        </h5>
                    </div>
                    <div class="ld-card-body">
                        <div class="d-grid gap-2" style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="{{ route('employees-leave-request.summary') }}" class="quick-nav-btn">
                                <span><i class="fas fa-wallet mr-2" style="color: var(--orb-secondary);"></i> Leave Balance Tracker</span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </a>
                            <a href="{{ route('leave-allocations.index') }}" class="quick-nav-btn">
                                <span><i class="fas fa-coins mr-2" style="color: #F59E0B;"></i> Leave Allocations</span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </a>
                            <a href="{{ route('hrms.leave.team_calendar.index') }}" class="quick-nav-btn">
                                <span><i class="fas fa-calendar-alt mr-2" style="color: var(--orb-primary);"></i> Team Leave Calendar</span>
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