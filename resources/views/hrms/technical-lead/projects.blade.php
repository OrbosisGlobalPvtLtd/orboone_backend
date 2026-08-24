@extends('layouts.panel', ['active' => 'technical_lead_projects'])

@section('page_title', 'Technical Lead Projects')

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

.tl-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.tl-container {
    max-width: 1550px;
    margin: 0 auto;
}

.tl-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.tl-project-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tl-project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.12);
}

.tl-project-header {
    padding: 20px 24px;
    background: #FFFFFF;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
@endsection

@section('_content')
<div class="tl-page">
    <div class="tl-container">
        <!-- Hero Header -->
        <div class="tl-hero">
            <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-project-diagram mr-2"></i>Supervised Projects Overview</h1>
            <p class="mb-0 opacity-90">Overview of active projects where supervised developers are currently assigned.</p>
        </div>

        <div class="row">
            @forelse($projects as $prj)
            @php
                $totalTasks = $prj->tasks->count();
                $completedTasks = $prj->tasks->where('status', 'completed')->count();
                $progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            @endphp
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="tl-project-card">
                    <div class="tl-project-header">
                        <div>
                            <span class="badge badge-light border text-primary font-weight-bold px-2.5 py-1 mb-1" style="border-radius: 6px;">{{ $prj->project_code }}</span>
                            <h5 class="font-weight-bold text-dark mb-0">{{ $prj->name }}</h5>
                        </div>
                        <span class="badge badge-success text-uppercase px-3 py-1 font-weight-bold" style="border-radius: 8px;">{{ $prj->status }}</span>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 10px;">Delivery Head</small>
                            <span class="text-dark font-weight-bold"><i class="fas fa-user-tie text-primary mr-1"></i> {{ $prj->delivery_head_display_name }}</span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 10px;">Assigned Developers</small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($prj->activeAssignments as $assignment)
                                    <span class="badge badge-light border text-dark font-weight-medium px-2.5 py-1" style="border-radius: 6px;">
                                        {{ optional($assignment->employee)->display_name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted font-weight-bold">TASK PROGRESS</small>
                                <small class="font-weight-bold text-primary">{{ $progressPercent }}% ({{ $completedTasks }}/{{ $totalTasks }} Tasks)</small>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 6px; background: #F1F5F9;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $progressPercent }}%; background: var(--orb-primary); border-radius: 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                <h5 class="font-weight-bold text-dark">No Active Supervised Projects</h5>
                <p class="small mb-0">Projects will appear here when supervised developers are assigned to active projects.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
