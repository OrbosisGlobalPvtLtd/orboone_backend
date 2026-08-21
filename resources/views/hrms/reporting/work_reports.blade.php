@extends('layouts.panel', ['active' => 'reporting_work_reports'])

@section('page_title', 'Reporting Employee Work Reports')

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
        <div class="rep-hero" style="padding: 20px 24px;">
            <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-file-signature mr-2"></i>Reporting Employee Work Reports</h3>
            <p class="mb-0 opacity-90 small">Daily work summaries submitted by your reporting employees upon punch-out.</p>
        </div>

        <!-- Filter Card -->
        <div class="rep-card p-3 mb-4">
            <form method="GET" action="{{ route('reporting.work_reports') }}" class="form-inline flex-wrap gap-2">
                <input type="date" name="date" class="form-control mr-2 mb-2" value="{{ request('date') }}" style="border-radius: 10px;" placeholder="Select Date">

                <select name="employee_id" class="form-control mr-2 mb-2" style="border-radius: 10px;">
                    <option value="">-- All Reporting Employees --</option>
                    @foreach($teamEmployees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->display_name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>

                <select name="project_id" class="form-control mr-2 mb-2" style="border-radius: 10px;">
                    <option value="">-- All Projects --</option>
                    @foreach($teamProjects as $prj)
                        <option value="{{ $prj->id }}" {{ request('project_id') == $prj->id ? 'selected' : '' }}>
                            {{ $prj->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary font-weight-bold px-4 mb-2" style="border-radius: 10px; background: var(--orb-primary); border-color: var(--orb-primary);"><i class="fas fa-filter mr-1"></i> Filter</button>
                @if(request('date') || request('employee_id') || request('project_id'))
                    <a href="{{ route('reporting.work_reports') }}" class="btn btn-light border text-muted font-weight-bold ml-2 mb-2" style="border-radius: 10px;">Clear</a>
                @endif
            </form>
        </div>

        <div class="rep-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3">Employee</th>
                            <th class="py-3">Project / Context</th>
                            <th class="py-3">Daily Work Summary</th>
                            <th class="py-3 text-center">Submission Time</th>
                            <th class="py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workReports as $report)
                        <tr>
                            <td class="py-3 px-4 align-middle font-weight-bold text-dark" style="white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($report->work_date ?? $report->created_at)->format('d M Y') }}
                            </td>
                            <td class="py-3 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ $report->display_name }}</strong>
                                <small class="text-muted">{{ $report->employee_code }}</small>
                            </td>
                            <td class="py-3 align-middle font-weight-bold text-primary">
                                {{ $report->project_name ?? 'General Work' }}
                            </td>
                            <td class="py-3 align-middle">
                                <div class="text-dark small" style="max-width: 500px; white-space: pre-wrap;">{{ $report->work_description ?? $report->work_summary ?? 'No summary provided.' }}</div>
                            </td>
                            <td class="py-3 align-middle text-center font-weight-bold text-muted small">
                                {{ $report->created_at ? \Carbon\Carbon::parse($report->created_at)->format('h:i A') : '—' }}
                            </td>
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-info px-3 py-1 font-weight-bold" style="border-radius: 8px;"><i class="fas fa-check-circle mr-1"></i> Submitted</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-file-signature fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Daily Work Reports Found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($workReports->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $workReports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
