@extends('layouts.panel', ['active' => 'reporting_structure'])

@section('page_title', 'Reporting Structure')

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
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.tree-card {
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--orb-shadow);
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <div class="rep-hero" style="padding: 20px 24px;">
            <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-sitemap mr-2"></i>Organization Reporting Structure</h3>
            <p class="mb-0 opacity-90 small">Hierarchical breakdown of Reporting Managers and their assigned reporting employees.</p>
        </div>

        <div class="row">
            @forelse($supervisors as $sup)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="tree-card">
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="avatar-circle mr-3 text-white font-weight-bold d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }}, {{ $branding['secondary_color'] ?? '#FF5252' }});">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <strong class="text-dark font-weight-bold d-block">{{ $sup->supervisor_name }}</strong>
                            <small class="text-muted">{{ $sup->supervisor_code }} &bull; {{ $sup->designation_name ?? 'Reporting Manager' }} ({{ $sup->department_name ?? 'General' }})</small>
                        </div>
                    </div>

                    <h6 class="text-uppercase text-muted font-weight-bold mb-2" style="font-size: 11px;">Reporting Employees ({{ count($sup->employees) }})</h6>
                    <ul class="list-group list-group-flush">
                        @forelse($sup->employees as $emp)
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                            <span><i class="fas fa-user-circle text-primary mr-2"></i>{{ $emp->display_name }}</span>
                            <small class="text-muted">{{ $emp->designation_name ?? 'Employee' }}</small>
                        </li>
                        @empty
                        <li class="list-group-item px-0 py-2 text-muted small border-0">No active reporting employees assigned.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-sitemap fa-3x mb-3 text-muted"></i>
                <h5>No Reporting Structures Defined Yet</h5>
                <p class="small">Use "Employee Assignments" or Employee Onboarding to assign Reporting Managers to employees.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
