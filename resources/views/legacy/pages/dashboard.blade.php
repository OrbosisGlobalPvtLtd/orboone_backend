@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'dashboard'])

@section('_content')
<style>
:root {
    --orb-primary: #4B00E8;
    --orb-secondary: #8600EE;
    --orb-pink: #D400D5;
    --orb-rose: #EC4E74;
    --orb-yellow: #FFB101;

    --orb-bg: #f6f7fb;
    --orb-surface: #ffffff;
    --orb-border: #e9ecf4;
    --orb-text: #18212f;
    --orb-text-soft: #6b7280;
    --orb-radius: 20px;
    --orb-shadow: 0 12px 30px rgba(17, 24, 39, 0.06);
    --orb-shadow-soft: 0 6px 18px rgba(17, 24, 39, 0.04);
}

.dashboard-shell {
    padding: 10px 6px 20px; 
}

.orb-card {
    background: var(--orb-surface);
    border: 1px solid var(--orb-border);
    border-radius: var(--orb-radius);
    box-shadow: var(--orb-shadow-soft);
}

.dashboard-hero {
    position: relative;
    overflow: hidden;
    border-radius: 26px;
    padding: 28px;
    background: linear-gradient(135deg, #4B00E8 0%, #8600EE 45%, #D400D5 75%, #EC4E74 100%);
    color: #fff;
    box-shadow: 0 18px 45px rgba(75, 0, 232, 0.18);
    margin-bottom: 20px;
}

.dashboard-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -30px;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.09);
}

.dashboard-hero::after {
    content: '';
    position: absolute;
    bottom: -80px;
    right: 80px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,0.07);
}

.hero-content,
.hero-side {
    position: relative;
    z-index: 1;
}

.hero-kpis {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.hero-mini-card {
    min-width: 140px;
    padding: 12px 14px;
    border-radius: 16px;
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.16);
    backdrop-filter: blur(8px);
}

.hero-mini-card .label {
    font-size: 12px;
    color: rgba(255,255,255,0.78);
    margin-bottom: 3px;
    font-weight: 600;
}

.hero-mini-card .value {
    font-size: 1.35rem;
    font-weight: 800;
    color: #fff;
}

.hero-side-card {
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.16);
    border-radius: 18px;
    padding: 18px;
    backdrop-filter: blur(8px);
}

.hero-side-card h6 {
    font-size: 0.8rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.72);
    margin-bottom: 10px;
    font-weight: 700;
}

.hero-side-card .big {
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
}

.hero-side-card p {
    margin: 8px 0 0;
    color: rgba(255,255,255,0.76);
    font-size: 0.88rem;
}

.section-block {
    margin-bottom: 22px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--orb-text);
    margin-bottom: 14px;
}

.section-title i {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: rgba(75, 0, 232, 0.08);
    color: var(--orb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.kpi-card {
    height: 100%;
    padding: 18px;
    border-radius: 20px;
    background: #fff;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow-soft);
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
}

.kpi-card:hover,
.quick-action-card:hover,
.info-widget:hover,
.module-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--orb-shadow);
}

.kpi-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--orb-primary), var(--orb-pink));
}

.kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.kpi-text small {
    display: block;
    color: var(--orb-text-soft);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.72rem;
    margin-bottom: 6px;
}

.kpi-text h3 {
    margin: 0;
    font-size: 1.9rem;
    font-weight: 800;
    color: var(--orb-text);
}

.kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: var(--orb-primary);
    background: rgba(75, 0, 232, 0.08);
}

.kpi-sub {
    margin-top: 12px;
    font-size: 0.83rem;
    color: var(--orb-text-soft);
    font-weight: 600;
}

.quick-action-card {
    height: 100%;
    padding: 18px;
    border-radius: 18px;
    background: #fff;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow-soft);
    transition: all 0.25s ease;
    text-decoration: none !important;
    display: block;
}

.quick-action-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    font-size: 1.2rem;
    color: #fff;
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
}

.quick-action-card h6 {
    font-size: 1rem;
    font-weight: 800;
    color: var(--orb-text);
    margin-bottom: 6px;
}

.quick-action-card p {
    margin: 0;
    color: var(--orb-text-soft);
    font-size: 0.85rem;
    line-height: 1.55;
}

.info-widget {
    height: 100%;
    padding: 20px;
    border-radius: 20px;
    background: #fff;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow-soft);
    transition: all 0.25s ease;
}

.info-widget-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.info-widget-head h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    color: var(--orb-text);
}

.info-widget-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(75, 0, 232, 0.08);
    color: var(--orb-primary);
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 0.75rem;
    font-weight: 700;
}

.status-success {
    background: rgba(16, 185, 129, 0.12);
    color: #047857;
}

.status-warning {
    background: rgba(255, 177, 1, 0.16);
    color: #9a6700;
}

.status-danger {
    background: rgba(236, 78, 116, 0.14);
    color: #b42318;
}

.birthday-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.birthday-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 16px;
    background: #fafbff;
    border: 1px solid #eef1f7;
}

.birthday-item img {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    object-fit: cover;
}

.birthday-item h6 {
    margin: 0 0 3px;
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--orb-text);
}

.birthday-item p {
    margin: 0;
    font-size: 0.78rem;
    color: var(--orb-text-soft);
    font-weight: 600;
}

.module-card {
    height: 100%;
    background: #fff;
    border-radius: 18px;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow-soft);
    padding: 20px 16px;
    text-align: center;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
}

.module-card::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 4px;
    background: linear-gradient(90deg, var(--orb-primary), var(--orb-secondary));
    opacity: 0;
    transition: opacity 0.25s ease;
}

.module-card:hover::before {
    opacity: 1;
}

.module-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    border-radius: 999px;
    padding: 6px 10px;
    color: #fff;
    background: linear-gradient(135deg, var(--orb-yellow), #e69b00);
}

.module-badge.optional {
    background: linear-gradient(135deg, #7b8090, #5d6270);
}

.module-icon-wrap {
    width: 72px;
    height: 72px;
    margin: 12px auto 16px;
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.9rem;
}

.icon-crm { background: rgba(78,115,223,0.12); color: #4e73df; }
.icon-pm { background: rgba(28,200,138,0.12); color: #1cc88a; }
.icon-fin { background: rgba(246,194,62,0.14); color: #d89c05; }
.icon-sup { background: rgba(54,185,204,0.14); color: #36b9cc; }

.module-card h5 {
    font-size: 1rem;
    font-weight: 800;
    color: var(--orb-text);
    margin-bottom: 8px;
}

.module-card p {
    margin: 0;
    color: var(--orb-text-soft);
    font-size: 0.84rem;
    line-height: 1.55;
}

.custom-table-container {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--orb-shadow-soft);
    border: 1px solid var(--orb-border);
    padding: 16px;
}

.custom-table-container .table {
    margin: 0;
}

.custom-table-container thead th {
    background: #f8f9fc;
    border: none;
    color: var(--orb-text);
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: 1px;
    padding: 14px 12px;
}

.custom-table-container tbody td {
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f8;
    padding: 14px 12px;
    color: #4b5563;
    font-weight: 600;
}

.custom-table-container tbody tr:last-child td {
    border-bottom: none;
}

.custom-table-container tbody tr:hover td {
    background: #fcfcff;
}

.empty-state {
    text-align: center;
    padding: 38px 20px;
    color: var(--orb-text-soft);
}

.empty-state i {
    font-size: 2.4rem;
    color: #c2c7d3;
    margin-bottom: 10px;
}

.notice-ticker {
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 24px rgba(75, 0, 232, 0.12);
    display: flex;
    align-items: center;
    background: linear-gradient(90deg, #EC4E74, #D400D5, #8600EE, #4B00E8);
    color: #fff;
    margin-bottom: 20px;
}

.notice-label {
    padding: 14px 18px;
    background: #FFB101;
    color: #4B00E8;
    font-weight: 900;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-size: 0.78rem;
    white-space: nowrap;
}

.notice-content {
    padding: 0 18px;
    overflow: hidden;
    white-space: nowrap;
    width: 100%;
}

.notice-content marquee {
    font-weight: 700;
    font-size: 0.92rem;
}

.btn-orb {
    border: none;
    border-radius: 12px;
    padding: 8px 14px;
    font-size: 0.82rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    color: #fff !important;
    box-shadow: 0 8px 18px rgba(75, 0, 232, 0.16);
}

.btn-orb:hover {
    color: #fff !important;
    text-decoration: none;
    transform: translateY(-1px);
}

@media (max-width: 991px) {
    .dashboard-hero {
        padding: 22px 18px;
    }

    .hero-kpis {
        margin-top: 16px;
    }

    .dashboard-hero h2 {
        font-size: 1.5rem !important;
    }

    .hero-side {
        margin-top: 16px;
    }
}

@media (max-width: 767px) {
    .dashboard-shell {
        padding: 4px 0 14px;
    }

    .dashboard-hero {
        border-radius: 22px;
        padding: 18px 16px;
    }

    .hero-kpis {
        gap: 10px;
    }

    .hero-mini-card {
        min-width: calc(50% - 5px);
        flex: 1;
    }

    .section-title {
        font-size: 1rem;
    }

    .section-title i {
        width: 34px;
        height: 34px;
        font-size: 0.85rem;
    }

    .kpi-card,
    .info-widget,
    .quick-action-card,
    .module-card {
        border-radius: 16px;
    }

    .notice-ticker {
        flex-direction: column;
        align-items: stretch;
    }

    .notice-label {
        text-align: center;
    }

    .notice-content {
        padding: 10px 14px;
    }
}
</style>

<div class="container-fluid dashboard-shell">

    <div class="dashboard-hero">
        <div class="row align-items-center">
            <div class="col-lg-8 hero-content">
                <h2 class="font-weight-bold mb-2 text-white" style="letter-spacing: .3px;">
                    Welcome back, {{ Auth::user()->name }} 👋
                </h2>
                <p class="mb-0 text-white-50" style="font-size: 1rem;">
                    Here is your premium Orbosis HRMS dashboard summary for today.
                </p>

                <div class="hero-kpis">
                    <div class="hero-mini-card">
                        <div class="label">Today</div>
                        <div class="value">{{ now()->format('d M') }}</div>
                    </div>
                    <div class="hero-mini-card">
                        <div class="label">User Role</div>
                        <div class="value" style="font-size:1rem;">{{ auth()->user()->role->name ?? 'User' }}</div>
                    </div>
                    <div class="hero-mini-card">
                        <div class="label">Employees</div>
                        <div class="value">{{ $employeesCount ?? 0 }}</div>
                    </div>
                    <div class="hero-mini-card">
                        <div class="label">Applicants</div>
                        <div class="value">{{ $recruitmentCandidatesCount ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 hero-side">
                <div class="hero-side-card">
                    <h6>Workspace Status</h6>
                    <div class="big">HRMS Active</div>
                    <p>Attendance, employees, documents, payroll and internal operations in one place.</p>
                </div>
            </div>
        </div>
    </div>

    @if(isset($todayStatus) && (count($todayStatus->birthdays) > 0 || $todayStatus->is_holiday))
        <div class="notice-ticker">
            <div class="notice-label">
                <i class="fas fa-broadcast-tower mr-2"></i> Live
            </div>
            <div class="notice-content">
                <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();">
                    @if(count($todayStatus->birthdays) > 0)
                        🎉 Happy Birthday to
                        @foreach($todayStatus->birthdays as $bday)
                            <span style="color:#FFB101; text-transform:uppercase;">{{ $bday->name }}</span>@if(!$loop->last) & @endif
                        @endforeach
                        &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                    @endif

                    @if($todayStatus->is_holiday)
                        ✨ Today we are celebrating
                        @foreach($todayStatus->holidays as $holiday)
                            <span style="color:#FFB101; text-transform:uppercase;">{{ $holiday }}</span>@if(!$loop->last) & @endif
                        @endforeach
                    @endif
                </marquee>
            </div>
        </div>
    @endif

    <div class="section-block">
        <div class="section-title">
            <i class="fas fa-chart-pie"></i>
            Dashboard Overview
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-text">
                            <small>Total Employees</small>
                            <h3>{{ $employeesCount ?? 0 }}</h3>
                        </div>
                        <div class="kpi-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="kpi-sub">Active workforce overview</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-text">
                            <small>Total Applicants</small>
                            <h3>{{ $recruitmentCandidatesCount ?? 0 }}</h3>
                        </div>
                        <div class="kpi-icon" style="background:rgba(28,200,138,.10); color:#1cc88a;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <div class="kpi-sub">Recruitment pipeline summary</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-text">
                            <small>Birthdays Today</small>
                            <h3>{{ isset($todayStatus) ? count($todayStatus->birthdays) : 0 }}</h3>
                        </div>
                        <div class="kpi-icon" style="background:rgba(236,78,116,.10); color:#EC4E74;">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                    </div>
                    <div class="kpi-sub">Celebrate your team moments</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-text">
                            <small>Holiday Status</small>
                            <h3>{{ isset($todayStatus) && $todayStatus->is_holiday ? 'Yes' : 'No' }}</h3>
                        </div>
                        <div class="kpi-icon" style="background:rgba(255,177,1,.14); color:#d89900;">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                    </div>
                    <div class="kpi-sub">Today’s holiday information</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-block">
        <div class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="{{ route('employees-data') }}" class="quick-action-card">
                    <div class="quick-action-icon"><i class="fas fa-id-card"></i></div>
                    <h6>Employees</h6>
                    <p>View employee records, profile data and organization structure.</p>
                </a>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <a href="{{ route('attendances') }}" class="quick-action-card">
                    <div class="quick-action-icon" style="background:linear-gradient(135deg,#1cc88a,#16a36d);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h6>Attendance</h6>
                    <p>Track punches, work mode, office presence and attendance history.</p>
                </a>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <a href="{{ auth()->user()->isAdmin() ? route('leave-approvals.index') : route('leave-requests.index') }}" class="quick-action-card">
                    <div class="quick-action-icon" style="background:linear-gradient(135deg,#EC4E74,#D400D5);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h6>Leave Requests</h6>
                    <p>Manage leave applications, balances and approvals from one place.</p>
                </a>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <a href="{{ route('announcements') }}" class="quick-action-card">
                    <div class="quick-action-icon" style="background:linear-gradient(135deg,#FFB101,#EC4E74);">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h6>Announcements</h6>
                    <p>Publish internal updates, circulars and important organization notices.</p>
                </a>
            </div>
        </div>
    </div>

    <div class="section-block">
        <div class="section-title">
            <i class="fas fa-sun"></i>
            Today’s Highlights
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="info-widget h-100">
                    <div class="info-widget-head">
                        <h5>Holiday Status</h5>
                        <div class="info-widget-icon"><i class="fas fa-umbrella-beach"></i></div>
                    </div>

                    @if(isset($todayStatus) && $todayStatus->is_holiday)
                        @foreach($todayStatus->holidays as $holiday)
                            <div class="status-pill status-warning mb-2">
                                <i class="fas fa-star"></i> {{ $holiday }}
                            </div>
                        @endforeach
                        <p class="mb-0 mt-2 text-muted">Enjoy your day. Today is marked as a holiday in the system.</p>
                    @else
                        <div class="status-pill status-success">
                            <i class="fas fa-check-circle"></i> Working Day
                        </div>
                        <p class="mb-0 mt-3 text-muted">No holiday configured for today.</p>
                    @endif
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="info-widget h-100">
                    <div class="info-widget-head">
                        <h5>Birthdays Today</h5>
                        <div class="info-widget-icon"><i class="fas fa-birthday-cake"></i></div>
                    </div>

                    @if(isset($todayStatus) && count($todayStatus->birthdays) > 0)
                        <div class="birthday-list">
                            @foreach($todayStatus->birthdays as $bday)
                                <div class="birthday-item">
                                    <img src="{{ $bday->image_url ? $bday->image_url : asset('images/profile.png') }}" alt="">
                                    <div>
                                        <h6>{{ $bday->name }}</h6>
                                        <p>{{ $bday->employee_id }} • {{ $bday->department }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-birthday-cake"></i>
                            <h6 class="mb-1">No birthdays today</h6>
                            <p class="mb-0">No employee birthdays are scheduled for today.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="info-widget h-100">
                    <div class="info-widget-head">
                        <h5>Workspace Focus</h5>
                        <div class="info-widget-icon"><i class="fas fa-briefcase"></i></div>
                    </div>

                    <div class="status-pill status-success mb-3">
                        <i class="fas fa-shield-alt"></i> HRMS Running
                    </div>

                    <p class="mb-2 text-muted">Use this dashboard to monitor employee activity, internal operations and HR workflows.</p>
                    <p class="mb-0 text-muted">Keep attendance, leaves, documents and payroll organized from one premium admin panel.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="section-block">
        <div class="section-title">
            <i class="fas fa-rocket"></i>
            Upcoming Modules
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="module-card">
                    <span class="module-badge">Coming Soon</span>
                    <div class="module-icon-wrap icon-crm">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h5>CRM System</h5>
                    <p>Customer relationship management, leads and client interaction workflows.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="module-card">
                    <span class="module-badge">Coming Soon</span>
                    <div class="module-icon-wrap icon-pm">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h5>Project Management</h5>
                    <p>Manage internal tasks, timelines, work allocation and project visibility.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="module-card">
                    <span class="module-badge optional">Optional</span>
                    <div class="module-icon-wrap icon-fin">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h5>Finance</h5>
                    <p>Invoice tracking, expense workflows and organization finance operations.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="module-card">
                    <span class="module-badge optional">Optional</span>
                    <div class="module-icon-wrap icon-sup">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>Support Desk</h5>
                    <p>Internal helpdesk, tickets, issue escalation and service communication.</p>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->isAdmin())
        <div class="section-block">
            <div class="section-title">
                <i class="fas fa-file-signature"></i>
                Contract Renewals
            </div>

            <div class="custom-table-container">
                <div class="table-responsive">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th class="text-left" style="width: 35%;">Employee Name</th>
                                <th style="width: 20%;">Contract Ends On</th>
                                <th style="width: 20%;">Status</th>
                                <th style="width: 20%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($endingEmployees) && count($endingEmployees) > 0)
                                @foreach ($endingEmployees as $employee)
                                    @php
                                        $endDate = \Carbon\Carbon::parse($employee->end_of_contract);
                                        $daysRemaining = round(now()->diffInDays($endDate, false));

                                        if ($daysRemaining < 0) {
                                            $bgClass = 'status-danger';
                                            $text = 'Expired';
                                        } elseif ($daysRemaining < 30) {
                                            $bgClass = 'status-warning';
                                            $text = $daysRemaining . ' Days Left';
                                        } else {
                                            $bgClass = 'status-success';
                                            $text = $daysRemaining . ' Days Left';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">
                                            {{ $loop->iteration + $endingEmployees->firstItem() - 1 }}
                                        </td>
                                        <td class="text-left">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold"
                                                     style="width: 38px; height: 38px; background: linear-gradient(135deg,#4B00E8,#8600EE); margin-right: 12px;">
                                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                </div>
                                                <span class="font-weight-bold" style="color:#111827;">{{ $employee->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $endDate->format('d M, Y') }}</td>
                                        <td>
                                            <span class="status-pill {{ $bgClass }}">{{ $text }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('employees-data.edit', ['employee' => $employee->id]) }}" class="btn btn-orb">
                                                <i class="fas fa-sync-alt mr-1"></i> Renew
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-check-circle"></i>
                                            <h6 class="mb-1">All clear</h6>
                                            <p class="mb-0">No employee contracts are ending soon.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    @if(isset($endingEmployees) && count($endingEmployees) > 0)
                        <div class="mt-4 d-flex justify-content-end">
                            {{ $endingEmployees->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="section-block">
            <div class="section-title">
                <i class="fas fa-clock"></i>
                My Attendance Log
            </div>

            <div class="custom-table-container">
                <div class="table-responsive">
                    <table class="table text-center align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Clock-In</th>
                                <th>Clock-Out</th>
                                <th>Work Type</th>
                                <th>Location</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($attendanceRecords) && count($attendanceRecords) > 0)
                                @forelse ($attendanceRecords as $attendance)
                                    <tr>
                                        <td class="font-weight-bold text-muted">
                                            {{ $loop->iteration + $attendanceRecords->firstItem() - 1 }}
                                        </td>
                                        <td class="font-weight-bold" style="color:#111827;">
                                            {{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}
                                        </td>
                                        <td>
                                            <span class="status-pill status-success">
                                                <i class="fas fa-sign-in-alt"></i> {{ $attendance->clock_in ?? '--:--' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-pill status-danger">
                                                <i class="fas fa-sign-out-alt"></i> {{ $attendance->clock_out ?? '--:--' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->work_type == 'WFH')
                                                <span class="status-pill" style="background:rgba(59,130,246,.12); color:#1d4ed8;">
                                                    <i class="fas fa-home"></i> WFH
                                                </span>
                                            @else
                                                <span class="status-pill" style="background:rgba(107,114,128,.12); color:#374151;">
                                                    <i class="fas fa-building"></i> WFO
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance->latitude || $attendance->clock_out_latitude)
                                                <span class="font-weight-bold" style="color:#4B00E8;">
                                                    <i class="fas fa-map-marker-alt"></i> Logged
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-truncate" style="max-width: 200px;" title="{{ $attendance->note ?? $attendance->clock_out_note }}">
                                            {{ $attendance->note ?? $attendance->clock_out_note ?? 'No notes' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-clipboard-list"></i>
                                                <h6 class="mb-1">No attendance records</h6>
                                                <p class="mb-0">Your punches will appear here.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            @else
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fas fa-clipboard-list"></i>
                                            <h6 class="mb-1">No attendance records</h6>
                                            <p class="mb-0">Your punches will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    @if(isset($attendanceRecords) && count($attendanceRecords) > 0)
                        <div class="mt-4 d-flex justify-content-end">
                            {{ $attendanceRecords->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
@if(isset($todayStatus) && count($todayStatus->birthdays) > 0)
document.addEventListener('DOMContentLoaded', function() {
    var duration = 6 * 1000;
    var animationEnd = Date.now() + duration;

    var defaults = {
        startVelocity: 30,
        spread: 360,
        ticks: 60,
        zIndex: 9999,
        colors: ['#ffb101', '#ec4e74', '#d400d5', '#8600ee', '#4b00e8']
    };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    var interval = setInterval(function() {
        var timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            clearInterval(interval);
            return;
        }

        var particleCount = 40 * (timeLeft / duration);

        confetti(Object.assign({}, defaults, {
            particleCount: particleCount,
            origin: {
                x: randomInRange(0.1, 0.3),
                y: Math.random() - 0.2
            }
        }));

        confetti(Object.assign({}, defaults, {
            particleCount: particleCount,
            origin: {
                x: randomInRange(0.7, 0.9),
                y: Math.random() - 0.2
            }
        }));
    }, 250);
});
@endif
</script>
@endsection
