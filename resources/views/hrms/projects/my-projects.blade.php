@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'My Projects')

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
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.prj-page {
    padding: 28px 20px 48px;
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
    padding: 30px 34px;
    margin-bottom: 28px;
    color: #ffffff;
    box-shadow: 0 12px 32px rgba(75, 0, 232, 0.18);
}

.prj-hero h1 {
    font-size: 26px;
    font-weight: 800;
    margin: 0;
    color: #ffffff;
    letter-spacing: -0.02em;
}

.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
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
    font-size: 13px;
    margin-bottom: 10px;
}

.badge-lead {
    background: #FEF3C7;
    color: #92400E;
    border: 1px solid #FCD34D;
    font-weight: 700;
    border-radius: 8px;
}

.badge-code {
    background: #F1F5F9;
    color: #334155;
    border: 1px solid #E2E8F0;
    font-weight: 700;
    border-radius: 8px;
    padding: 6px 12px;
}

.badge-active-status {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-weight: 800;
    border-radius: 8px;
    padding: 6px 12px;
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
                <div>
                    <h1><i class="fas fa-project-diagram mr-2"></i>My Assigned Projects</h1>
                    <p class="mb-0 opacity-90 mt-1" style="font-size: 14.5px;">Projects where you are actively assigned. Explore project overview, team details & organizational hierarchy tree.</p>
                </div>
                <div>
                    <span class="badge badge-light px-3 py-2 text-dark font-weight-bold shadow-sm" style="border-radius: 12px; font-size: 14px;">
                        <i class="fas fa-layer-group text-primary mr-1"></i> {{ $projects->count() }} Assigned Project(s)
                    </span>
                </div>
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

                            <h4 class="font-weight-bold text-dark mb-2" style="font-size: 19px; letter-spacing: -0.01em;">{{ $project->name }}</h4>
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
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </span>
                            </div>

                            <div class="prj-meta-item d-flex align-items-center justify-content-between mb-0">
                                <span class="text-muted"><i class="fas fa-users text-info mr-2"></i> Team Members:</span>
                                <span class="font-weight-bold text-dark">{{ $totalMembersCount }} Member(s)</span>
                            </div>
                        </div>

                        <!-- Card Action Footer with Explicit Margin Spacing -->
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
                    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 18px;">
                        <div class="card-body">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
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
