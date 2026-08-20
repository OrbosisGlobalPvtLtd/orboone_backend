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
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.prj-hero h1 {
    font-size: 26px;
    font-weight: 800;
    margin: 0;
    color: #ffffff;
}

.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    display: flex;
    flex-direction: column;
    height: 100%;
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <h1><i class="fas fa-tasks mr-2"></i>My Assigned Projects</h1>
            <p class="mb-0 opacity-90">Projects where you are actively assigned as Delivery Head, Team Lead, or Team Member.</p>
        </div>

        <div class="row">
            @forelse($projects as $project)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="prj-card">
                    <div class="p-4 flex-fill">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-light border font-weight-bold">{{ $project->project_code }}</span>
                            <span class="badge badge-info text-uppercase px-2 py-1">{{ $project->status }}</span>
                        </div>
                        <h4 class="font-weight-bold text-dark mb-2">{{ $project->name }}</h4>
                        <p class="text-secondary small mb-3">{{ Str::limit($project->description ?? 'No project overview provided.', 90) }}</p>
                        
                        <div class="small mb-2"><strong>Client:</strong> {{ $project->client_name ?? 'N/A' }}</div>
                        <div class="small mb-3"><strong>Delivery Head:</strong> {{ optional(optional($project->deliveryHead)->user)->name ?? 'Unassigned' }}</div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Progress</span>
                                <span class="font-weight-bold text-primary">{{ $project->progress_percentage }}%</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar" style="width: {{ $project->progress_percentage }}%; background: linear-gradient(90deg, var(--orb-primary), var(--orb-secondary));"></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-light border-top rounded-bottom">
                        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-block text-white font-weight-bold py-2" style="background: var(--orb-primary); border-radius: 8px;">
                            <i class="fas fa-eye mr-1"></i> View Project Dashboard
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 16px;">
                    <div class="card-body">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h4 class="font-weight-bold text-dark mb-2">No Projects Assigned</h4>
                        <p class="text-muted">You are not currently assigned to any active project team.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
