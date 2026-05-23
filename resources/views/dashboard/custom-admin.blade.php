@extends('layouts.panel')
@section('page_title', $dashboard['meta']['title'] ?? 'Custom Admin Dashboard')
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
    .custom-dash { background: var(--orb-bg); padding: 22px; }
    .custom-hero {
        border-radius: 26px;
        padding: 28px;
        color: #fff;
        background: linear-gradient(135deg, #0BA5EC, #0284C7);
        box-shadow: var(--orb-shadow);
        margin-bottom: 22px;
    }
    .custom-hero h3 { font-weight: 800; margin: 0; }
    .custom-hero p { opacity: .9; margin: 6px 0 0; }
    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
    .stat-card { background: #fff; border: 1px solid var(--orb-border); border-radius: 22px; padding: 18px; box-shadow: var(--orb-shadow); }
    .stat-icon { height: 42px; width: 42px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: #E0F2FE; color: #0284C7; margin-bottom: 12px; font-size: 20px;}
    .stat-title { color: var(--orb-muted); font-size: 13px; font-weight: 700; }
    .stat-value { font-size: 28px; font-weight: 900; color: var(--orb-text); }
</style>

<div class="custom-dash">
    <div class="custom-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3>Admin Dashboard</h3>
                <p>Welcome to your customized administration panel</p>
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
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-title">Employees</div>
            <div class="stat-value">{{ $dashboard['cards']['employees'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-success"><i class="fas fa-clock"></i></div>
            <div class="stat-title">Attendance</div>
            <div class="stat-value">{{ $dashboard['cards']['attendance'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-warning"><i class="fas fa-plane"></i></div>
            <div class="stat-title">Leave</div>
            <div class="stat-value">{{ $dashboard['cards']['leave'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-info"><i class="fas fa-folder"></i></div>
            <div class="stat-title">Documents</div>
            <div class="stat-value">{{ $dashboard['cards']['documents'] ?? 0 }}</div>
        </div>
    </div>
</div>
@endsection
