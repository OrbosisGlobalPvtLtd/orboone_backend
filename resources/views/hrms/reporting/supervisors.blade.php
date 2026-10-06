@extends('layouts.panel', ['active' => 'reporting_managers'])

@section('page_title', 'Reporting Managers')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F6F7FB;
    --orb-card: #FFFFFF;
    --orb-border: #E7EAF3;
    --orb-text: #101828;
    --orb-muted: #667085;
    --orb-soft: #F4F2FF;
    --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
}

.rep-page {
    padding: 14px 16px 36px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
    font-family: 'Outfit', sans-serif;
}

.rep-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

/* Signature Hero Header Banner */
.rep-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px;
    padding: 22px 28px;
    margin-bottom: 20px;
    box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    flex-wrap: wrap;
}

.rep-hero:before {
    content: "";
    position: absolute;
    right: -60px;
    top: -80px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.rep-hero-kicker {
    font-size: 11.5px;
    font-weight: 850;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .92;
    margin-bottom: 6px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.rep-hero-title {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    line-height: 1.15;
    color: #ffffff;
}

.rep-hero-subtitle {
    font-size: 13px;
    font-weight: 500;
    margin-top: 5px;
    opacity: .92;
    max-width: 800px;
}

.rep-btn-glass {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.38) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border-radius: 999px !important;
    padding: 8px 18px !important;
    font-size: 12.5px !important;
    font-weight: 750 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    transition: all 0.2s ease !important;
}

.rep-btn-glass:hover {
    background: rgba(255, 255, 255, 0.32) !important;
    border-color: rgba(255, 255, 255, 0.65) !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

/* Metric KPI Cards Grid */
.team-metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.team-metric-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    padding: 14px 14px 10px;
    box-shadow: 0 10px 24px rgba(16, 24, 40, .055);
    position: relative;
    overflow: hidden;
    min-height: 92px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.team-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(16, 24, 40, .09);
}

.team-metric-card:after {
    content: "";
    position: absolute;
    right: -22px;
    top: -30px;
    width: 86px;
    height: 86px;
    border-radius: 50%;
    background: var(--metric-soft, #F4F2FF);
    pointer-events: none;
}

.team-metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    position: relative;
    z-index: 1;
}

.team-metric-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: var(--metric-soft, #F4F2FF);
    color: var(--metric-color, var(--orb-primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.team-metric-value {
    font-size: 24px;
    font-weight: 900;
    color: #101828;
    line-height: 1;
}

.team-metric-label {
    font-size: 11px;
    font-weight: 850;
    color: #475467;
    text-transform: uppercase;
    margin-top: 12px;
    position: relative;
    z-index: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: 0.03em;
}

.team-metric-line {
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--metric-color, var(--orb-primary)), transparent);
    margin-top: 8px;
}

/* Card & Table */
.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.rep-card-head {
    padding: 16px 22px;
    background: linear-gradient(180deg, #FFFFFF, #FAFBFF);
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.rep-card-title {
    font-size: 16.5px;
    font-weight: 850;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rep-card-sub {
    font-size: 12px;
    color: var(--orb-muted);
    font-weight: 550;
    margin-top: 2px;
}

.rep-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.rep-table thead th {
    background: #F8FAFC !important;
    color: #475467 !important;
    font-size: 11px !important;
    font-weight: 850 !important;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 12px 16px !important;
    border-bottom: 1px solid #E2E8F0 !important;
    border-top: none !important;
    white-space: nowrap;
}

.rep-table tbody td {
    padding: 13px 16px !important;
    vertical-align: middle !important;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    border-bottom: 1px solid #F1F5F9;
    background: #FFFFFF;
}

.rep-table tbody tr:hover td {
    background: #F8FAFC !important;
}

@media (max-width: 768px) {
    .rep-page {
        padding: 10px 10px 30px;
    }
    .rep-hero {
        padding: 18px 16px;
        border-radius: 18px;
    }
    .rep-hero-title {
        font-size: 20px;
    }
    .team-metric-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">

        <!-- Hero Header -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-user-shield"></i> Team Management &bull; Supervisors Roster
                </div>
                <h1 class="rep-hero-title">Reporting Managers Roster</h1>
                <div class="rep-hero-subtitle">Comprehensive directory of designated Reporting Managers and active team supervisory lines.</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('reporting.dashboard') }}" class="rep-btn-glass">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <a href="{{ route('reporting.assignments') }}" class="rep-btn-glass">
                    <i class="fas fa-user-plus"></i> Assign Team Members
                </a>
                <a href="{{ route('reporting.structure') }}" class="rep-btn-glass">
                    <i class="fas fa-sitemap"></i> Organization Tree
                </a>
            </div>
        </div>

        @php
            $totalSupervisors = count($supervisorsData);
            $totalAssignedEmployees = collect($supervisorsData)->sum('employees_count');
        @endphp

        <!-- Metric KPI Cards -->
        <div class="team-metric-grid">
            <div class="team-metric-card" style="--metric-color:#4B00E8;--metric-soft:#F4F2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="team-metric-value">{{ $totalSupervisors }}</div>
                </div>
                <div class="team-metric-label">Reporting Managers</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric-card" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-users"></i></div>
                    <div class="team-metric-value text-success">{{ $totalAssignedEmployees }}</div>
                </div>
                <div class="team-metric-label">Supervised Employees</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric-card" style="--metric-color:#D97706;--metric-soft:#FFFBEB;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-building"></i></div>
                    <div class="team-metric-value text-warning">{{ collect($supervisorsData)->pluck('department_name')->filter()->unique()->count() }}</div>
                </div>
                <div class="team-metric-label">Departments Covered</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        <!-- Supervisors Table Card -->
        <div class="rep-card">
            <div class="rep-card-head">
                <div>
                    <h5 class="rep-card-title"><i class="fas fa-users-cog text-primary"></i> Active Reporting Managers</h5>
                    <div class="rep-card-sub">Overview of managers, their department, and assigned team headcount.</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table rep-table mb-0" id="supervisorsTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>Reporting Manager</th>
                            <th>Department</th>
                            <th>HR Designation</th>
                            <th class="text-center">Reporting Employees</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supervisorsData as $sup)
                        @php
                            $initials = strtoupper(substr($sup->supervisor_name ?? 'RM', 0, 2));
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold" style="font-size: 11.5px;">
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 850; font-size: 12px; flex-shrink: 0;">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <strong class="text-dark font-weight-bold d-block" style="font-size: 13.5px;">{{ $sup->supervisor_name }}</strong>
                                        <small class="text-muted"><span class="badge badge-light border font-weight-bold" style="font-size: 10.5px;">{{ $sup->supervisor_code }}</span></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11.5px; border-radius: 6px;">
                                    <i class="fas fa-building text-muted mr-1"></i> {{ $sup->department_name ?? 'General Staff' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted font-weight-bold" style="font-size: 12.5px;">
                                    {{ $sup->designation_name ?? 'Reporting Manager' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary font-weight-bold px-3 py-1.5" style="background: var(--orb-primary); border-radius: 20px; font-size: 11px;">
                                    <i class="fas fa-users mr-1"></i> {{ $sup->employees_count }} Employees
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success px-3 py-1 font-weight-bold text-uppercase" style="border-radius: 20px; font-size: 10.5px; letter-spacing: 0.03em;">
                                    Active
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('reporting.assignments', ['supervisor_id' => $sup->supervisor_id]) }}" class="btn btn-sm btn-outline-primary px-3 font-weight-bold" style="border-radius: 10px;">
                                    <i class="fas fa-users mr-1"></i> View Team ({{ $sup->employees_count }})
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div style="width: 70px; height: 70px; border-radius: 20px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px;">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark mb-1">No Active Reporting Managers Found</h5>
                                <p class="small text-muted mb-0">Assign employees to a Reporting Manager from Employee Assignments to populate this roster.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
