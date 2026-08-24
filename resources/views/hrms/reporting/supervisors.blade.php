@extends('layouts.panel', ['active' => 'reporting_managers'])

@section('page_title', 'Reporting Managers')

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
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header -->
        <div class="rep-hero" style="padding: 20px 24px;">
            <div>
                <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-user-shield mr-2"></i>Reporting Managers Roster</h3>
                <p class="mb-0 opacity-90 small">Overview of assigned Reporting Managers across enterprise departments.</p>
            </div>
            <div>
                <a href="{{ route('reporting.assignments') }}" class="btn btn-light font-weight-bold px-4 py-2" style="border-radius: 12px; color: var(--orb-primary);">
                    <i class="fas fa-user-plus mr-2"></i>Assign Reporting Employees
                </a>
            </div>
        </div>

        <div class="rep-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Reporting Manager</th>
                            <th class="py-3">Department</th>
                            <th class="py-3">HR Designation</th>
                            <th class="py-3 text-center">Reporting Employees</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supervisorsData as $sup)
                        <tr>
                            <td class="py-3 px-4 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ $sup->supervisor_name }}</strong>
                                <small class="text-muted">{{ $sup->supervisor_code }}</small>
                            </td>
                            <td class="py-3 align-middle font-weight-bold text-dark">{{ $sup->department_name ?? 'General' }}</td>
                            <td class="py-3 align-middle text-muted">{{ $sup->designation_name ?? 'Reporting Manager' }}</td>
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-primary font-weight-bold px-3 py-1.5" style="background: var(--orb-primary); border-radius: 8px;">
                                    <i class="fas fa-users mr-1"></i> {{ $sup->employees_count }} Employees
                                </span>
                            </td>
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 8px;">Active</span>
                            </td>
                            <td class="py-3 px-4 align-middle text-right">
                                <a href="{{ route('reporting.assignments', ['supervisor_id' => $sup->supervisor_id]) }}" class="btn btn-sm btn-outline-primary px-3 font-weight-bold" style="border-radius: 8px;">
                                    <i class="fas fa-list mr-1"></i> View Reporting Employees
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-user-shield fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Active Reporting Managers Found</h5>
                                <p class="small mb-0">Assign employees to a Reporting Manager from Employee Onboarding or Employee Assignments.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
