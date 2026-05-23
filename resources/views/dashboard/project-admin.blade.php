@extends('layouts.panel')
@section('page_title', $dashboard['meta']['title'] ?? 'Project Admin Dashboard')
@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }
    .proj-dash { background: var(--orb-bg); padding: 22px; }
    .proj-hero {
        border-radius: 26px;
        padding: 28px;
        color: #fff;
        background: linear-gradient(135deg, #027A48, #12B76A);
        box-shadow: var(--orb-shadow);
        margin-bottom: 22px;
    }
    .proj-hero h3 { font-weight: 800; margin: 0; }
    .proj-hero p { opacity: .9; margin: 6px 0 0; }
    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
    .stat-card { background: #fff; border: 1px solid var(--orb-border); border-radius: 22px; padding: 18px; box-shadow: var(--orb-shadow); }
    .stat-icon { height: 42px; width: 42px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: #ECFDF3; color: #027A48; margin-bottom: 12px; font-size: 20px;}
    .stat-title { color: var(--orb-muted); font-size: 13px; font-weight: 700; }
    .stat-value { font-size: 28px; font-weight: 900; color: var(--orb-text); }
</style>

<div class="proj-dash">
    <div class="proj-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3>Project Operations Dashboard</h3>
                <p>Track tasks, projects and employee workloads</p>
                <small>{{ $dashboard['meta']['current_date'] }}</small>
            </div>
            <div class="col-lg-4 text-right">
                @foreach($dashboard['quick_actions'] ?? [] as $action)
                    <a href="{{ Route::has($action['route']) ? route($action['route']) : '#' }}" class="btn btn-light btn-sm ml-2 mb-2">
                        <i class="{{ $action['icon'] ?? 'fas fa-arrow-right' }}"></i> {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-project-diagram"></i></div>
            <div class="stat-title">Active Projects</div>
            <div class="stat-value">{{ $dashboard['cards']['active_projects'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-warning"><i class="fas fa-tasks"></i></div>
            <div class="stat-title">Open Tasks</div>
            <div class="stat-value">{{ $dashboard['cards']['open_tasks'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-success"><i class="fas fa-check-double"></i></div>
            <div class="stat-title">Completed Tasks</div>
            <div class="stat-value">{{ $dashboard['cards']['completed_tasks'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-title">Overdue Tasks</div>
            <div class="stat-value">{{ $dashboard['cards']['overdue_tasks'] ?? 0 }}</div>
        </div>
    </div>
</div>
@endsection
