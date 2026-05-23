@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'payroll_run'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-1">Payroll Preview</h2>
            <p class="text-muted"><i class="fas fa-calendar-check mr-1"></i> Review calculations for {{ date('F Y', strtotime($monthInput . '-01')) }}</p>
        </div>
        <div class="col-md-6 text-md-right">
            @if($payrolls->count())
                <form method="POST" action="{{ route('pages.payroll.lock', $monthInput) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg shadow-sm px-5" onclick="return confirm('Once locked, you can generate payslips. Continue?')">
                        <i class="fas fa-lock mr-2"></i> Lock & Finalize Payroll
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">Payroll Breakdown</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light text-uppercase small font-weight-bold text-muted">
                        <tr>
                            <th class="px-4">Employee</th>
                            <th class="text-center">Paid / Working</th>
                            <th class="text-right">Basic</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right">Deductions</th>
                            <th class="text-right">Net Payable</th>
                            <th class="text-center px-4">Status</th>
                            <th class="text-center px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $p)
                            <tr>
                                <td class="px-4">
                                    <span class="font-weight-bold d-block text-dark">{{ $p->employee->display_name ?? 'N/A' }}</span>
                                    <small class="text-muted">ID: {{ $p->employee->employee_code ?? $p->employee->id }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border">{{ $p->payable_days ?? $p->paid_days ?? '-' }} / {{ $p->working_days ?? '-' }}</span>
                                </td>
                                <td class="text-right">Rs {{ number_format($p->basic, 2) }}</td>
                                <td class="text-right">Rs {{ number_format($p->gross_salary, 2) }}</td>
                                <td class="text-right text-danger">-Rs {{ number_format($p->total_deductions, 2) }}</td>
                                <td class="text-right">
                                    <span class="font-weight-bold text-success">Rs {{ number_format($p->net_salary, 2) }}</span>
                                </td>
                                <td class="text-center px-4">
                                    <span class="badge badge-{{ $p->status == 'locked' ? 'success' : 'warning' }}-soft text-{{ $p->status == 'locked' ? 'success' : 'warning' }} px-3 py-2">
                                        {{ ucfirst($p->status ?? 'Draft') }}
                                    </span>
                                </td>
                                <td class="text-center px-4">
                                    @if(!in_array($p->status, ['approved', 'locked'], true))
                                        <form method="POST" action="{{ route('pages.payroll.approve', $p->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Approve this payroll row?')">
                                                <i class="fas fa-check mr-1"></i> Approve
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Ready for payslip</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-exclamation-circle fa-3x text-light mb-3 d-block"></i>
                                    <p class="text-muted">No payroll data generated for this month.</p>
                                    <a href="{{ route('pages.payroll.payrollrun') }}" class="btn btn-primary mt-2">Run Payroll Now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 12px; }
    .table thead th { border-top: 0; letter-spacing: 0.5px; }
    .badge-success-soft { background-color: rgba(40, 167, 69, 0.1); }
    .badge-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .btn-success { border-radius: 10px; font-weight: 700; }
</style>
@endpush
@endsection
