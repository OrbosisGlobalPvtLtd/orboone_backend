@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div><div class="ep-kicker"><i class="fas fa-chart-bar"></i> Enterprise Payroll</div><h1>Reports</h1><p>Payroll, deductions, reimbursements, bonus, salary, LWP and attendance impact reports.</p></div>
    </div>
    <div class="row">
        @php
            $icons = [
                'employee-salary' => 'fas fa-money-check-alt text-primary',
                'reimbursement' => 'fas fa-receipt text-success',
                'bonus-incentive' => 'fas fa-gift text-warning',
                'monthly-payroll' => 'fas fa-file-invoice-dollar text-info'
            ];
            $descriptions = [
                'employee-salary' => 'View active and inactive salary structures assigned to employees.',
                'reimbursement' => 'Analyze approved and paid reimbursements across departments.',
                'bonus-incentive' => 'Review performance bonuses and incentives given to employees.',
                'monthly-payroll' => 'Comprehensive monthly payroll generation and payout summaries.'
            ];
        @endphp
        @foreach($reports as $key => $label)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="ep-card h-100 mb-0">
                    <div class="ep-card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="ep-badge ep-badge-primary mr-3" style="font-size:18px; padding:12px 14px;"><i class="{{ $icons[$key] ?? 'fas fa-chart-bar text-primary' }}"></i></div>
                            <h5 class="font-weight-bold text-dark mb-0">{{ $label }}</h5>
                        </div>
                        <p class="text-muted small flex-grow-1">{{ $descriptions[$key] ?? 'Detailed analysis and breakdown of enterprise payroll data.' }}</p>
                        <a href="{{ route('enterprise-payroll.reports.show', $key) }}" class="ep-btn ep-btn-light w-100 mt-2"><i class="fas fa-eye"></i> View Report</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
