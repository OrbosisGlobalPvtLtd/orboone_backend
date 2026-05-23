@extends('layouts.panel')
@section('page_title', $dashboard['meta']['title'] ?? 'Finance Admin Dashboard')
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
    .fin-dash { background: var(--orb-bg); padding: 22px; }
    .fin-hero {
        border-radius: 26px;
        padding: 28px;
        color: #fff;
        background: linear-gradient(135deg, #101828, #344054);
        box-shadow: var(--orb-shadow);
        margin-bottom: 22px;
    }
    .fin-hero h3 { font-weight: 800; margin: 0; }
    .fin-hero p { opacity: .9; margin: 6px 0 0; }
    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
    .stat-card { background: #fff; border: 1px solid var(--orb-border); border-radius: 22px; padding: 18px; box-shadow: var(--orb-shadow); }
    .stat-icon { height: 42px; width: 42px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: #F2F4F7; color: #101828; margin-bottom: 12px; font-size: 20px;}
    .stat-title { color: var(--orb-muted); font-size: 13px; font-weight: 700; }
    .stat-value { font-size: 28px; font-weight: 900; color: var(--orb-text); }
    .dash-grid { display: grid; grid-template-columns: 1fr; gap: 18px; margin-bottom: 22px; }
    .orb-card { background: #fff; border: 1px solid var(--orb-border); border-radius: 24px; box-shadow: var(--orb-shadow); }
    .orb-card-head { padding: 18px 20px; border-bottom: 1px solid var(--orb-border); display: flex; justify-content: space-between; align-items: center; }
    .orb-card-head h5 { margin: 0; font-weight: 800; color: var(--orb-text); }
    .orb-card-body { padding: 18px 20px; }
</style>

<div class="fin-dash">
    <div class="fin-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3>Finance & Payroll Operations</h3>
                <p>{{ \Carbon\Carbon::now()->format('F Y') }} Payroll Cycle</p>
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
            <div class="stat-title">Payroll Employees</div>
            <div class="stat-value">{{ $dashboard['cards']['total_payroll_employees'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-warning"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-title">Payroll Pending</div>
            <div class="stat-value">{{ $dashboard['cards']['payroll_pending'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-danger"><i class="fas fa-minus-circle"></i></div>
            <div class="stat-title">LWP Deductions</div>
            <div class="stat-value">{{ $dashboard['cards']['lwp_deductions'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-primary"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="stat-title">FNF Pending</div>
            <div class="stat-value">{{ $dashboard['cards']['fnf_pending'] ?? 0 }}</div>
        </div>
    </div>

    <div class="dash-grid">
        <div class="orb-card">
            <div class="orb-card-head">
                <h5><i class="fas fa-chart-bar"></i> Payroll Attendance Impact</h5>
            </div>
            <div class="orb-card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h3 class="text-success">{{ $dashboard['payroll']['payable_days'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Payable Days</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-danger">{{ $dashboard['payroll']['total_lwp'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total LWP Days</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-warning">{{ $dashboard['payroll']['total_absent'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Absent Days</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-info">{{ $dashboard['payroll']['total_half_days'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Half Days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
