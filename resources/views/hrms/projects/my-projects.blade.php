@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'My Projects')

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

.prj-page {
    padding: 14px 16px 36px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
    font-family: 'Outfit', sans-serif;
}

.prj-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

/* Signature Hero Header Banner */
.prj-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px;
    padding: 22px 28px;
    margin-bottom: 20px;
    color: #ffffff;
    box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    overflow: hidden;
}

.prj-hero:before {
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

.prj-hero-kicker {
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

.prj-hero h1 {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    color: #ffffff;
    line-height: 1.15;
}

.prj-hero p {
    margin: 4px 0 0;
    font-size: 13px;
    opacity: 0.92;
}

.prj-btn-glass {
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

.prj-btn-glass:hover {
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

/* Project Cards */
.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}

.prj-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    border-color: rgba(75, 0, 232, 0.2);
}

.prj-meta-item {
    background: #F8FAFC;
    border: 1px solid #F1F5F9;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 12.5px;
    margin-bottom: 8px;
}

.badge-lead {
    background: #FEF3C7;
    color: #92400E;
    border: 1px solid #FCD34D;
    font-weight: 750;
    border-radius: 8px;
}

.badge-code {
    background: #F4F2FF;
    color: var(--orb-primary);
    border: 1px solid #DDD6FE;
    font-weight: 850;
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 11.5px;
}

.badge-active-status {
    background: #ECFDF5;
    color: #047857;
    border: 1px solid #A7F3D0;
    font-weight: 850;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 10.5px;
}

@media (max-width: 768px) {
    .prj-page { padding: 10px 10px 30px; }
    .prj-hero { padding: 18px 16px; border-radius: 18px; }
    .prj-hero h1 { font-size: 20px; }
    .team-metric-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">

        <!-- Hero Header -->
        <div class="prj-hero">
            <div>
                <div class="prj-hero-kicker">
                    <i class="fas fa-project-diagram"></i> Project Management &bull; Personal Workspace
                </div>
                <h1>My Assigned Projects</h1>
                <p>Projects where you are actively assigned. Explore project dashboard, team members, and hierarchy tree.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('projects.index') }}" class="prj-btn-glass">
                    <i class="fas fa-layer-group"></i> All Projects Directory
                </a>
                <a href="{{ route('projects.tasks.index') }}" class="prj-btn-glass">
                    <i class="fas fa-tasks"></i> My Tasks
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards Grid -->
        <div class="team-metric-grid">
            <div class="team-metric-card" style="--metric-color:#4B00E8;--metric-soft:#F4F2FF;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="team-metric-value">{{ $projects->count() }}</div>
                </div>
                <div class="team-metric-label">Assigned Projects</div>
                <div class="team-metric-line"></div>
            </div>

            <div class="team-metric-card" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
                <div class="team-metric-top">
                    <div class="team-metric-icon"><i class="fas fa-play-circle"></i></div>
                    <div class="team-metric-value text-success">{{ $projects->where('status', 'active')->count() }}</div>
                </div>
                <div class="team-metric-label">Active Projects</div>
                <div class="team-metric-line"></div>
            </div>
        </div>

        <div class="row">
            @forelse($projects as $project)
                @php
                    $deliveryHeadName = optional(optional($project->deliveryHead)->user)->name ?? 'Unassigned';
                    
                    // Resolve Team Lead names
                    $teamLeadNames = [];
                    foreach ($project->teams as $tm) {
                        if ($tm->teamLead && $tm->teamLead->user) {
                            $teamLeadNames[] = $tm->teamLead->user->name;
                        }
                    }
                    foreach ($project->activeAssignments as $asgn) {
                        $role = strtolower((string)($asgn->project_role ?? ''));
                        if (in_array($role, ['team_lead', 'team lead', 'project_lead', 'lead'], true) && $asgn->employee && $asgn->employee->user) {
                            $teamLeadNames[] = $asgn->employee->user->name;
                        }
                    }
                    $teamLeadNames = array_values(array_unique($teamLeadNames));
                    $totalMembersCount = $project->activeAssignments->count();
                @endphp
                
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="prj-card">
                        <div class="p-4 flex-fill">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge badge-code">
                                    <i class="fas fa-hashtag text-muted mr-1"></i>{{ $project->project_code }}
                                </span>
                                <span class="badge badge-active-status text-uppercase">
                                    <i class="fas fa-circle mr-1" style="font-size: 7px; vertical-align: middle;"></i>{{ $project->status }}
                                </span>
                            </div>

                            <h4 class="font-weight-bold text-dark mb-2" style="font-size: 18px; letter-spacing: -0.01em;">{{ $project->name }}</h4>
                            <p class="text-secondary small mb-3" style="line-height: 1.55; min-height: 40px;">
                                {{ Str::limit($project->description ?? 'No project overview provided.', 85) }}
                            </p>
                            
                            <div class="prj-meta-item d-flex align-items-center justify-content-between">
                                <span class="text-muted"><i class="fas fa-user-shield text-primary mr-2"></i> Delivery Head:</span>
                                <span class="font-weight-bold text-dark">{{ $deliveryHeadName }}</span>
                            </div>

                            <div class="prj-meta-item d-flex align-items-center justify-content-between">
                                <span class="text-muted"><i class="fas fa-user-tie text-warning mr-2"></i> Team Lead:</span>
                                <span class="font-weight-bold text-dark">
                                    @if(!empty($teamLeadNames))
                                        <span class="badge badge-lead px-2.5 py-1"><i class="fas fa-crown mr-1"></i>{{ implode(', ', $teamLeadNames) }}</span>
                                    @else
                                        <span class="text-muted font-weight-normal">Unassigned</span>
                                    @endif
                                </span>
                            </div>

                            <div class="prj-meta-item d-flex align-items-center justify-content-between mb-0">
                                <span class="text-muted"><i class="fas fa-users text-info mr-2"></i> Team Members:</span>
                                <span class="font-weight-bold text-dark">{{ $totalMembersCount }} Member(s)</span>
                            </div>
                        </div>

                        <!-- Card Action Footer -->
                        <div class="p-3 bg-light border-top">
                            <div class="d-flex align-items-center">
                                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold flex-fill py-2" style="border-radius: 12px; font-size: 13px; margin-right: 6px;">
                                    <i class="fas fa-chart-line mr-1"></i> Dashboard
                                </a>
                                <a href="{{ route('projects.hierarchy', $project->id) }}" class="btn btn-sm text-white font-weight-bold flex-fill py-2" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border-radius: 12px; font-size: 13px; margin-left: 6px;">
                                    <i class="fas fa-sitemap mr-1"></i> Hierarchy Tree
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 20px;">
                        <div class="card-body">
                            <div style="width: 70px; height: 70px; border-radius: 20px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px;">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-2">No Projects Assigned</h4>
                            <p class="text-muted mb-0">You are not currently assigned to any active project team.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
