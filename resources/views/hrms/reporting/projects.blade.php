@extends('layouts.panel', ['active' => 'reporting_projects'])

@section('page_title', 'Projects & Tasks')

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

/* Metric KPI Cards Grid (6 Cards) */
.team-metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

@media (min-width: 1200px) {
    .team-metric-grid {
        grid-template-columns: repeat(6, 1fr);
    }
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

/* Filter Card Panel */
.rep-card {
    background: #fff;
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

.rep-filter-body {
    padding: 18px 22px;
    background: #FFFFFF;
}

.rep-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    align-items: end;
}

.rep-filter-group label {
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    color: #667085;
    margin-bottom: 6px;
    display: block;
    letter-spacing: .04em;
}

.rep-filter-group .form-control {
    height: 42px;
    border-radius: 12px;
    border: 1px solid #E4E7EC;
    font-size: 13px;
    font-weight: 650;
    padding: 0 12px;
    box-shadow: none !important;
}

.rep-filter-group .form-control:focus {
    border-color: var(--orb-primary);
    box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .10) !important;
}

/* Select2 Skin */
.select2-container--default .select2-selection--single {
    height: 42px !important;
    border: 1px solid #E4E7EC !important;
    border-radius: 12px !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 10px !important;
    background-color: #fff !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 40px !important;
    font-size: 13px !important;
    font-weight: 650 !important;
    color: #101828 !important;
    padding-left: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 8px !important;
}

.select2-dropdown {
    border: 1px solid #E4E7EC !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    font-size: 13px !important;
    font-weight: 550 !important;
    z-index: 9999 !important;
}

/* Project Cards Grid */
.prj-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.prj-card {
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    padding: 20px;
    transition: all 0.25s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.prj-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.09);
    border-color: #CBD5E1;
}

.prj-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
}

.prj-code-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 850;
    background: #F4F2FF;
    color: var(--orb-primary);
    border: 1px solid #DDD6FE;
    letter-spacing: .03em;
}

.prj-status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.prj-status-active {
    background: #ECFDF5;
    color: #047857;
    border: 1px solid #A7F3D0;
}

.prj-status-completed {
    background: #EFF6FF;
    color: #1D4ED8;
    border: 1px solid #BFDBFE;
}

.prj-status-hold {
    background: #FFFBEB;
    color: #B45309;
    border: 1px solid #FDE68A;
}

.prj-name {
    font-size: 17px;
    font-weight: 850;
    color: #101828;
    margin: 0 0 6px 0;
    line-height: 1.3;
}

.prj-desc {
    font-size: 12.5px;
    color: #64748B;
    font-weight: 500;
    line-height: 1.45;
    margin-bottom: 14px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.prj-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 12px;
    background: #F8FAFC;
    border: 1px solid #EDF2F7;
    border-radius: 12px;
    margin-bottom: 14px;
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
}

.prj-members-section {
    border-top: 1px dashed #E2E8F0;
    padding-top: 14px;
    margin-top: 10px;
}

.prj-members-title {
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748B;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.prj-member-item {
    background: #FAFBFF;
    border: 1px solid #EEF2F6;
    border-radius: 12px;
    padding: 10px 12px;
    margin-bottom: 8px;
    transition: background 0.15s ease;
}

.prj-member-item:hover {
    background: #F4F2FF;
}

.prj-member-avatar {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 850;
    font-size: 11.5px;
    flex-shrink: 0;
}

.prj-member-name {
    font-size: 12.5px;
    font-weight: 800;
    color: #101828;
}

.prj-member-meta {
    font-size: 11px;
    color: #64748B;
    font-weight: 600;
}

.prj-task-chip {
    font-size: 10.5px;
    font-weight: 750;
    padding: 2px 7px;
    border-radius: 6px;
    background: #EDE9FE;
    color: #5B21B6;
}

.prj-actions {
    border-top: 1px solid #F1F5F9;
    padding-top: 14px;
    margin-top: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}

.prj-btn-action {
    height: 34px;
    padding: 0 12px;
    border-radius: 10px;
    font-size: 11.5px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none !important;
    transition: all 0.15s ease;
}

/* Premium Empty State */
.rep-empty-state {
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 24px;
    padding: 60px 24px;
    text-align: center;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
}

.rep-empty-icon-wrap {
    width: 90px;
    height: 90px;
    border-radius: 28px;
    background: linear-gradient(135deg, #F4F2FF 0%, #EDE9FE 100%);
    color: var(--orb-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    margin-bottom: 20px;
    box-shadow: 0 12px 30px rgba(75, 0, 232, 0.12);
    position: relative;
}

.rep-empty-icon-wrap:after {
    content: "";
    position: absolute;
    inset: -6px;
    border-radius: 32px;
    border: 2px dashed #DDD6FE;
}

.rep-empty-title {
    font-size: 20px;
    font-weight: 900;
    color: #101828;
    margin: 0 0 8px 0;
}

.rep-empty-desc {
    font-size: 13.5px;
    color: #64748B;
    max-width: 500px;
    margin: 0 auto 24px;
    line-height: 1.55;
    font-weight: 500;
}

.rep-empty-actions {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
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
    .prj-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">

        <!-- Signature Hero Banner -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-project-diagram"></i> Team Management &bull; Projects & Tasks
                </div>
                <h1 class="rep-hero-title">Reporting Employees – Projects & Tasks</h1>
                <div class="rep-hero-subtitle">Inspect active projects, team assignments, roles, and task progress of your reporting workforce.</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('reporting.dashboard') }}" class="rep-btn-glass">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <a href="{{ route('reporting.my_employees') }}" class="rep-btn-glass">
                    <i class="fas fa-users"></i> My Team
                </a>
                <a href="{{ route('reporting.work_reports') }}" class="rep-btn-glass">
                    <i class="fas fa-file-alt"></i> Daily Work Reports
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards Grid -->
        <div class="team-metric-grid">
            <!-- 1. Total Projects -->
            <div class="team-metric-card" style="--metric-color:#4B00E8;--metric-soft:#F4F2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-cubes"></i></div>
                    <div class="team-metric-value">{{ $stats['total_projects'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Total Projects</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 2. Active Projects -->
            <div class="team-metric-card" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-play-circle"></i></div>
                    <div class="team-metric-value text-success">{{ $stats['active_projects'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Active Projects</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 3. Completed Projects -->
            <div class="team-metric-card" style="--metric-color:#6366F1;--metric-soft:#EEF2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-check-double"></i></div>
                    <div class="team-metric-value text-primary">{{ $stats['completed_projects'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Completed Projects</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 4. Team Members Assigned -->
            <div class="team-metric-card" style="--metric-color:#D97706;--metric-soft:#FFFBEB;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-users"></i></div>
                    <div class="team-metric-value text-warning">{{ $stats['total_members'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Assigned Members</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 5. Total Tasks -->
            <div class="team-metric-card" style="--metric-color:#7C3AED;--metric-soft:#F5F3FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-tasks"></i></div>
                    <div class="team-metric-value">{{ $stats['total_tasks'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Assigned Tasks</div>
                <div class="team-metric-line"></div>
            </div>

            <!-- 6. Completed Tasks -->
            <div class="team-metric-card" style="--metric-color:#0D9488;--metric-soft:#F0FDFA;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="team-metric-value text-info">{{ $stats['completed_tasks'] ?? 0 }}</div>
                </div>
                <div class="team-metric-label">Completed Tasks</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="rep-card">
            <div class="rep-card-head">
                <div>
                    <h5 class="rep-card-title"><i class="fas fa-filter text-primary"></i> Filter Projects & Tasks</h5>
                    <div class="rep-card-sub">Filter by project name, assigned employee, status or keyword.</div>
                </div>
            </div>
            <div class="rep-filter-body">
                <form method="GET" action="{{ route('reporting.projects') }}" id="projectsFilterForm">
                    <div class="rep-filter-grid">
                        <!-- Project Filter -->
                        <div class="rep-filter-group">
                            <label><i class="fas fa-project-diagram text-primary mr-1"></i> Project</label>
                            <select name="project_id" class="form-control select2-searchable">
                                <option value="">All Projects</option>
                                @foreach($allProjectsList as $prjItem)
                                    <option value="{{ $prjItem->id }}" {{ request('project_id') == $prjItem->id ? 'selected' : '' }}>
                                        {{ $prjItem->name }} ({{ $prjItem->project_code ?? 'PRJ' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Team Member Filter -->
                        <div class="rep-filter-group">
                            <label><i class="fas fa-user text-primary mr-1"></i> Team Member</label>
                            <select name="employee_id" class="form-control select2-searchable">
                                <option value="">All Reporting Members</option>
                                @foreach($teamEmployees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="rep-filter-group">
                            <label><i class="fas fa-info-circle text-primary mr-1"></i> Status</label>
                            <select name="status" class="form-control select2-searchable">
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="active" {{ request('status') === 'active' || !request()->has('status') ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <!-- Search Input -->
                        <div class="rep-filter-group">
                            <label><i class="fas fa-search text-primary mr-1"></i> Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search project, code, client..." value="{{ request('search') }}">
                        </div>

                        <!-- Action Buttons -->
                        <div class="rep-filter-group d-flex align-items-end" style="gap: 8px;">
                            <button type="submit" class="btn font-weight-bold text-white shadow-sm" style="height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('reporting.projects') }}" class="btn btn-light" style="height: 42px; width: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 750; border: 1px solid #E4E7EC; background: #fff; color: #344054; flex-shrink: 0;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Projects Cards Grid -->
        @if($projects->count() > 0)
            <div class="prj-grid">
                @foreach($projects as $prj)
                @php
                    $st = strtolower($prj->status ?? 'active');
                    $stClass = 'prj-status-active';
                    if (in_array($st, ['completed', 'done', 'closed'])) $stClass = 'prj-status-completed';
                    elseif (in_array($st, ['on_hold', 'hold', 'paused'])) $stClass = 'prj-status-hold';
                @endphp
                <div class="prj-card">
                    <div>
                        <div class="prj-header">
                            <span class="prj-code-badge"><i class="fas fa-tag"></i> {{ $prj->project_code ?? 'PRJ-' . $prj->id }}</span>
                            <span class="prj-status-badge {{ $stClass }}">{{ strtoupper($prj->status ?? 'ACTIVE') }}</span>
                        </div>

                        <h4 class="prj-name">{{ $prj->name }}</h4>
                        <div class="prj-desc">{{ $prj->description ?: 'No project summary description provided.' }}</div>

                        <div class="prj-meta-row">
                            <div>
                                <i class="fas fa-user-tie text-primary mr-1"></i> Delivery Head: <strong>{{ $prj->delivery_head_name ?? 'Not Assigned' }}</strong>
                            </div>
                            @if(!empty($prj->client_name))
                            <div>
                                <i class="fas fa-building text-muted mr-1"></i> Client: <strong>{{ $prj->client_name }}</strong>
                            </div>
                            @endif
                        </div>

                        <!-- Project Progress -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 font-weight-bold" style="font-size: 11px;">
                                <span class="text-muted text-uppercase">Task Progress ({{ $prj->completed_tasks_count }}/{{ $prj->total_tasks_count }})</span>
                                <span class="text-primary">{{ $prj->progress_percentage }}%</span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 999px; background: #EEF2F6;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $prj->progress_percentage }}%; border-radius: 999px;"></div>
                            </div>
                        </div>

                        <!-- Assigned Reporting Members -->
                        <div class="prj-members-section">
                            <div class="prj-members-title">
                                <span><i class="fas fa-users text-primary mr-1"></i> Reporting Employees</span>
                                <span class="badge badge-light border">{{ count($prj->reporting_members) }} Assigned</span>
                            </div>

                            @forelse($prj->reporting_members as $mem)
                            @php
                                $initials = strtoupper(substr($mem->display_name ?? 'EM', 0, 2));
                            @endphp
                            <div class="prj-member-item">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="prj-member-avatar">{{ $initials }}</div>
                                        <div>
                                            <div class="prj-member-name">{{ $mem->display_name }}</div>
                                            <div class="prj-member-meta">
                                                <span class="badge badge-light border" style="font-size: 10px;">{{ $mem->employee_code }}</span>
                                                @if($mem->designation_name) &bull; {{ $mem->designation_name }} @endif
                                                @if($mem->role_name) &bull; <span class="text-primary font-weight-bold">{{ $mem->role_name }}</span> @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="prj-task-chip">{{ count($mem->tasks) }} Tasks</span>
                                    </div>
                                </div>

                                @if(count($mem->tasks) > 0)
                                <div class="mt-2 pt-2 border-top border-light">
                                    @foreach($mem->tasks as $tsk)
                                    @php
                                        $tskSt = strtolower($tsk->status ?? 'todo');
                                        $badgeSt = 'badge-secondary';
                                        if (in_array($tskSt, ['completed', 'done'])) $badgeSt = 'badge-success';
                                        elseif (in_array($tskSt, ['in_progress', 'doing'])) $badgeSt = 'badge-info';
                                        elseif (in_array($tskSt, ['blocked', 'hold'])) $badgeSt = 'badge-danger';
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <small class="text-dark font-weight-bold text-truncate" style="max-width: 70%;">
                                            <i class="fas fa-check-circle text-muted mr-1" style="font-size: 10px;"></i> {{ $tsk->title }}
                                        </small>
                                        <span class="badge {{ $badgeSt }}" style="font-size: 9.5px; border-radius: 4px;">{{ strtoupper($tsk->status ?? 'TODO') }} ({{ $tsk->progress_percentage ?? 0 }}%)</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @empty
                            <div class="text-center py-3 text-muted" style="font-size: 12px; background: #F8FAFC; border-radius: 10px;">
                                <i class="fas fa-user-slash mr-1 text-muted"></i> No reporting employees assigned to this project.
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="prj-actions">
                        @if(Route::has('projects.show'))
                            <a href="{{ route('projects.show', $prj->id) }}" class="btn btn-outline-primary prj-btn-action">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        @endif
                        @if(Route::has('projects.hierarchy'))
                            <a href="{{ route('projects.hierarchy', $prj->id) }}" class="btn btn-outline-secondary prj-btn-action">
                                <i class="fas fa-sitemap"></i> Hierarchy
                            </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <!-- Premium Empty State -->
            <div class="rep-empty-state">
                <div class="rep-empty-icon-wrap">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3 class="rep-empty-title">No Active Projects Found</h3>
                <p class="rep-empty-desc">
                    Projects and assigned tasks will appear here as soon as your reporting team members are assigned to active projects in the Project Management module.
                </p>
                <div class="rep-empty-actions">
                    <a href="{{ route('reporting.projects') }}" class="btn btn-light border font-weight-bold px-4 py-2" style="border-radius: 12px;">
                        <i class="fas fa-undo mr-1"></i> Reset Filters
                    </a>
                    <a href="{{ route('reporting.my_employees') }}" class="btn text-white font-weight-bold px-4 py-2 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none;">
                        <i class="fas fa-users mr-1"></i> View Reporting Team
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-searchable').select2({
            width: '100%',
            dropdownAutoWidth: true
        });
    }
});
</script>
@endsection
