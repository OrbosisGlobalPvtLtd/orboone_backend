@extends('layouts.panel', ['active' => 'reporting_history'])

@section('page_title', 'Reporting History')

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
            <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-history mr-2"></i>Reporting History Audit Log</h3>
            <p class="mb-0 opacity-90 small">Historical log of past employee reporting manager assignments and transfers.</p>
        </div>

        <div class="rep-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">HR Designation</th>
                            <th class="py-3">Previous Reporting Manager</th>
                            <th class="py-3 text-center">Assigned From</th>
                            <th class="py-3 text-center">Relieved On</th>
                            <th class="py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                        <tr>
                            <td class="py-3 px-4 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ $item->employee_name }}</strong>
                                <small class="text-muted">{{ $item->employee_code }}</small>
                            </td>
                            <td class="py-3 align-middle text-muted">{{ $item->designation_name ?? 'Employee' }}</td>
                            <td class="py-3 align-middle font-weight-bold text-dark"><i class="fas fa-user-shield text-warning mr-1"></i>{{ $item->supervisor_name }}</td>
                            <td class="py-3 align-middle text-center font-weight-bold text-dark">{{ \Carbon\Carbon::parse($item->start_date ?? $item->created_at)->format('d M Y') }}</td>
                            <td class="py-3 align-middle text-center font-weight-bold text-danger">{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : '—' }}</td>
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-secondary px-3 py-1 font-weight-bold" style="border-radius: 8px;">Relieved / Transferred</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Historical Reporting Records Found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($history->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
