@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Project Hierarchy - ' . $project->name)

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
    margin-bottom: 28px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.prj-hero h1 {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 4px 0;
    color: #ffffff;
}

/* =========================================================
   TRUE ORGANIZATIONAL TREE DIAGRAM CSS
   ========================================================= */
.org-tree-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    padding: 10px 0;
}

/* Level 1: Root Node (Delivery Head) */
.org-root-node {
    background: #ffffff;
    border: 2px solid var(--orb-primary);
    border-radius: 20px;
    padding: 24px 40px;
    box-shadow: 0 14px 36px rgba(75, 0, 232, 0.15);
    text-align: center;
    position: relative;
    z-index: 5;
    min-width: 340px;
}

.org-root-badge {
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FCA5A5;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    padding: 5px 16px;
    border-radius: 20px;
    letter-spacing: 0.06em;
    display: inline-block;
    margin-bottom: 10px;
}

.org-root-name {
    font-size: 22px;
    font-weight: 800;
    color: var(--orb-text);
    margin-bottom: 4px;
}

.org-root-code {
    font-size: 13px;
    color: var(--orb-muted);
}

/* Stem Line down from Root */
.org-stem-line {
    width: 3px;
    height: 40px;
    background: var(--orb-primary);
    margin: 0 auto;
}

/* Horizontal Branching Bar */
.org-branch-container {
    display: flex;
    justify-content: center;
    width: 100%;
    position: relative;
    flex-wrap: wrap;
    gap: 32px;
}

.org-branch-bar {
    position: absolute;
    top: 0;
    height: 3px;
    background: var(--orb-primary);
    left: 20%;
    right: 20%;
}

/* Level 2: Team Columns */
.org-team-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    min-width: 320px;
    max-width: 440px;
    position: relative;
}

.org-team-stem {
    width: 3px;
    height: 30px;
    background: var(--orb-primary);
}

.org-team-card {
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
    width: 100%;
    overflow: hidden;
}

.org-team-header {
    padding: 18px 24px;
    background: #FAFAFC;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.org-team-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--orb-text);
    margin: 0;
}

/* Team Lead Node Inside Card */
.org-lead-box {
    background: linear-gradient(135deg, rgba(75, 0, 232, 0.07) 0%, rgba(255, 82, 82, 0.07) 100%);
    border: 1px solid rgba(75, 0, 232, 0.18);
    border-radius: 14px;
    padding: 16px 20px;
    margin: 20px;
}

.org-lead-badge {
    background: #E0E7FF;
    color: #3730A3;
    border: 1px solid #A5B4FC;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 12px;
    letter-spacing: 0.05em;
}

/* Tree Branch Connector for Team Members */
.org-members-tree {
    position: relative;
    padding-left: 28px;
    margin: 0 20px 20px 20px;
}

.org-members-tree::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 24px;
    left: 10px;
    width: 3px;
    background: var(--orb-primary);
    border-radius: 2px;
    opacity: 0.3;
}

.org-member-row {
    position: relative;
    margin-bottom: 12px;
}

.org-member-row::before {
    content: '';
    position: absolute;
    top: 20px;
    left: -18px;
    width: 18px;
    height: 3px;
    background: var(--orb-primary);
    opacity: 0.3;
}

.org-member-card {
    background: #F8FAFC;
    border: 1px solid var(--orb-border);
    border-radius: 12px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <div>
                <h1><i class="fas fa-sitemap mr-2"></i>Project Org Hierarchy: {{ $project->name }}</h1>
                <p class="mb-0 opacity-90">Visual Organizational Chart: Delivery Head &rarr; Team Leads &rarr; Team Members</p>
            </div>
            <div>
                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-light font-weight-bold px-4 py-2" style="border-radius: 12px; color: var(--orb-primary);">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Visual Org Tree Wrapper -->
        <div class="org-tree-wrapper">
            <!-- Level 1: Delivery Head Root Node -->
            <div class="org-root-node">
                <span class="org-root-badge">Delivery Head</span>
                <div class="org-root-name"><i class="fas fa-user-tie mr-2 text-danger"></i>{{ $project->delivery_head_display_name }}</div>
                <div class="org-root-code">Employee Code: <strong>{{ $hierarchy['delivery_head']['code'] ?? 'N/A' }}</strong></div>
            </div>

            <!-- Vertical Stem Line -->
            <div class="org-stem-line"></div>

            <!-- Level 2 & 3: Teams & Members (Sub-Teams + Direct Project Members) -->
            @php
                $totalCols = count($hierarchy['teams']) + (count($hierarchy['direct_members']) > 0 ? 1 : 0);
            @endphp
            <div class="org-branch-container">
                @if($totalCols > 1)
                <div class="org-branch-bar"></div>
                @endif

                @foreach($hierarchy['teams'] as $team)
                <div class="org-team-col">
                    <div class="org-team-stem"></div>
                    <div class="org-team-card">
                        <div class="org-team-header">
                            <h5 class="org-team-title"><i class="fas fa-users-cog mr-2 text-primary"></i>{{ $team['team_name'] }}</h5>
                            <span class="badge badge-light border font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ count($team['members']) }} members</span>
                        </div>

                        <!-- Team Lead Box -->
                        <div class="org-lead-box">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="org-lead-badge">Team Lead</span>
                                <span class="small font-weight-bold text-muted">{{ $team['team_lead']['code'] ?? 'N/A' }}</span>
                            </div>
                            <div class="font-weight-bold text-dark h6 mb-0">
                                <i class="fas fa-user-shield text-indigo mr-1"></i> {{ $team['team_lead']['name'] ?? 'Unassigned' }}
                            </div>
                        </div>

                        <!-- Sub-Tree Branch for Members -->
                        <div class="org-members-tree">
                            <div class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.04em;"><i class="fas fa-user-friends mr-1"></i> Reporting Members</div>
                            @forelse($team['members'] as $member)
                            <div class="org-member-row">
                                <div class="org-member-card">
                                    <div>
                                        <div class="font-weight-bold text-dark small"><i class="fas fa-user text-muted mr-1.5"></i>{{ $member['name'] }}</div>
                                        <div class="small text-muted" style="font-size: 11px;">{{ $member['code'] }}</div>
                                    </div>
                                    <span class="badge badge-white border text-secondary small font-weight-normal px-2 py-1">{{ $member['designation'] }}</span>
                                </div>
                            </div>
                            @empty
                            <div class="text-muted small py-2">No members assigned to this sub-team.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Direct Project Governance & Assigned Members Card -->
                @if(!empty($hierarchy['direct_members']) && count($hierarchy['direct_members']) > 0)
                @php
                    $directLeads = collect($hierarchy['direct_members'])->filter(function($m) {
                        $role = strtolower(trim((string)($m['role'] ?? '')));
                        return in_array($role, ['team_lead', 'team lead', 'project_lead', 'project lead', 'lead', 'manager'], true);
                    });
                    $directNonLeads = collect($hierarchy['direct_members'])->reject(function($m) {
                        $role = strtolower(trim((string)($m['role'] ?? '')));
                        return in_array($role, ['team_lead', 'team lead', 'project_lead', 'project lead', 'lead', 'manager'], true);
                    });
                @endphp
                <div class="org-team-col">
                    <div class="org-team-stem"></div>
                    <div class="org-team-card">
                        <div class="org-team-header">
                            <h5 class="org-team-title"><i class="fas fa-project-diagram mr-2 text-primary"></i> Direct Project Team</h5>
                            <span class="badge badge-light border font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ count($hierarchy['direct_members']) }} Members</span>
                        </div>

                        <!-- Team Lead Box for Direct Project Team -->
                        @if($directLeads->count() > 0)
                            @foreach($directLeads as $lead)
                            <div class="org-lead-box">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="org-lead-badge"><i class="fas fa-user-shield mr-1"></i> Team Lead</span>
                                    <span class="small font-weight-bold text-muted">{{ $lead['code'] ?? 'N/A' }}</span>
                                </div>
                                <div class="font-weight-bold text-dark h6 mb-1">
                                    <i class="fas fa-user-tie text-primary mr-1.5"></i> {{ $lead['name'] }}
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light">
                                    <small class="text-muted"><i class="fas fa-briefcase mr-1"></i> {{ $lead['designation'] ?? 'N/A' }}</small>
                                    <span class="badge badge-indigo text-white px-2 py-0.5" style="font-size: 10px; border-radius: 6px;">{{ str_replace('_', ' ', strtoupper($lead['role'] ?? 'TEAM LEAD')) }}</span>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="org-lead-box">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="org-lead-badge">Team Lead</span>
                                    <span class="small font-weight-bold text-muted">N/A</span>
                                </div>
                                <div class="font-weight-bold text-muted h6 mb-0">
                                    <i class="fas fa-user-slash text-muted mr-1"></i> Direct Project Members
                                </div>
                            </div>
                        @endif

                        <!-- Reporting Members Tree -->
                        <div class="org-members-tree">
                            <div class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.04em;"><i class="fas fa-user-friends mr-1"></i> Reporting Team Members</div>
                            @forelse($directNonLeads as $member)
                            <div class="org-member-row">
                                <div class="org-member-card">
                                    <div>
                                        <div class="font-weight-bold text-dark small">
                                            <i class="fas fa-user text-muted mr-1.5"></i>{{ $member['name'] }}
                                            @if(!empty($member['role']) && strtolower($member['role']) !== 'team_member')
                                                <span class="badge badge-light border text-secondary px-2 py-0.5 ml-1" style="font-size: 10px; border-radius: 6px;">{{ str_replace('_', ' ', strtoupper($member['role'])) }}</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted" style="font-size: 11px;">{{ $member['code'] }}</div>
                                    </div>
                                    <span class="badge badge-white border text-secondary small font-weight-normal px-2 py-1">{{ $member['designation'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                            @empty
                            <div class="text-muted small py-2 font-italic">No additional team members assigned under this lead.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif

                @if($totalCols === 0)
                <div class="text-center py-4 w-100">
                    <div class="alert alert-light border text-muted px-4 py-3" style="border-radius: 16px;">
                        <i class="fas fa-info-circle mr-2 text-info"></i> No team members or sub-teams assigned to this project yet.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
