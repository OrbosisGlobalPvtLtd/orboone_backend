@extends('layouts.panel', ['active' => 'reporting_projects'])

@section('page_title', 'Projects & Tasks')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.rep-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.rep-container {
    max-width: 1550px;
    margin: 0 auto;
}

.rep-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.prj-card {
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <div class="rep-hero" style="padding: 20px 24px;">
            <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-project-diagram mr-2"></i>Reporting Employees – Projects & Tasks</h3>
            <p class="mb-0 opacity-90 small">Projects, team assignments, roles, and task progress of your reporting employees.</p>
        </div>

        <div class="row">
            @forelse($projects as $prj)
            <div class="col-md-6 col-lg-6 mb-4">
                <div class="prj-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge badge-light border text-primary font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ $prj->project_code ?? 'PRJ' }}</span>
                        <span class="badge badge-success px-2.5 py-1 font-weight-bold" style="border-radius: 6px;">{{ strtoupper($prj->status ?? 'ACTIVE') }}</span>
                    </div>

                    <h4 class="font-weight-bold text-dark mb-1">{{ $prj->name }}</h4>
                    <p class="text-muted small mb-3">{{ $prj->description ?? 'No project description provided.' }}</p>

                    <div class="border-top pt-3 mt-3">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size: 11px;">Assigned Reporting Employees ({{ count($prj->reporting_members) }})</h6>

                        <div class="list-group list-group-flush">
                            @forelse($prj->reporting_members as $mem)
                            <div class="list-group-item px-0 py-2.5 border-bottom border-light">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <strong class="text-dark d-block"><i class="fas fa-user-circle text-primary mr-1"></i>{{ $mem->display_name }}</strong>
                                        <small class="text-muted">
                                            {{ $mem->employee_code }}
                                            @if($mem->team_name)&bull; Team: <span class="text-dark font-weight-bold">{{ $mem->team_name }}</span>@endif
                                            @if($mem->role_name)&bull; Role: <span class="text-info font-weight-bold">{{ $mem->role_name }}</span>@endif
                                        </small>
                                    </div>
                                </div>

                                @if(count($mem->tasks) > 0)
                                    <div class="bg-light rounded p-2 mt-1">
                                        @foreach($mem->tasks as $tsk)
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-dark font-weight-bold text-truncate" style="max-width: 70%;"><i class="fas fa-tasks text-muted mr-1"></i>{{ $tsk->title }}</small>
                                                <small class="badge badge-secondary" style="font-size: 10px;">{{ strtoupper($tsk->status) }} ({{ $tsk->progress_percentage }}%)</small>
                                            </div>
                                            <div class="progress" style="height: 4px; border-radius: 2px;">
                                                <div class="progress-bar bg-success" style="width: {{ $tsk->progress_percentage }}%;"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <small class="text-muted font-italic">No active tasks assigned on this project.</small>
                                @endif
                            </div>
                            @empty
                            <div class="text-muted small">No reporting employees assigned to this project.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                        <span class="small font-weight-bold text-muted">Delivery Head: {{ $prj->delivery_head_name ?? 'N/A' }}</span>
                        <a href="{{ route('projects.show', $prj->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold px-3" style="border-radius: 8px;">View Project Dashboard</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                <h5 class="font-weight-bold text-dark">No Active Projects Found</h5>
                <p class="small">Projects will appear here when your reporting employees are assigned to projects.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
