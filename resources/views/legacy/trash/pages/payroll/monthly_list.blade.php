@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'my_monthly_salary'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">My Monthly Salary History</h2>
            <p class="text-muted">View and download your monthly payroll records and payslips.</p>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 text-primary"><i class="fas fa-history mr-2"></i>Payroll Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Month & Year</th>
                            <th>Basic</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th class="text-center px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $p)
                            @php
                                $monthYear = date('F Y', mktime(0, 0, 0, $p->month, 1, $p->year));
                                $monthInput = sprintf('%04d-%02d', $p->year, $p->month);
                            @endphp
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-2 mr-3 text-center" style="min-width: 50px;">
                                            <span class="d-block font-weight-bold text-primary">{{ date('M', mktime(0, 0, 0, $p->month, 1, $p->year)) }}</span>
                                            <span class="small text-muted">{{ $p->year }}</span>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold d-block text-dark">{{ $monthYear }}</span>
                                            <small class="text-muted">Working Days: {{ $p->working_days }} | Paid: {{ $p->paid_days }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>₹{{ number_format($p->basic, 2) }}</td>
                                <td>
                                    <span class="font-weight-bold text-success">₹{{ number_format($p->net_salary, 2) }}</span>
                                </td>
                                <td>
                                    @if($p->status === 'locked' || $p->status === 'approved')
                                        <span class="badge badge-success px-3 py-2">
                                            <i class="fas fa-check-circle mr-1"></i> Finalized
                                        </span>
                                    @else
                                        <span class="badge badge-warning px-3 py-2 text-white">
                                            <i class="fas fa-clock mr-1"></i> Processing
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center px-4">
                                    <div class="btn-group">
                                        <a href="{{ route('pages.payroll.monthlydetail', $monthInput) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($p->status === 'locked' || $p->status === 'approved')
                                            <a href="{{ route('pages.payroll.payslip.download.employee', [auth()->user()->employee->id, $monthInput]) }}" 
                                               class="btn btn-sm btn-success ml-1" 
                                               title="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-receipt fa-3x text-light"></i>
                                    </div>
                                    <h5 class="text-muted">No payroll records found</h5>
                                    <p class="text-muted small">Your monthly salary history will appear here once generated.</p>
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
    .card { border-radius: 12px; overflow: hidden; }
    .table thead th { border-top: 0; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #6c757d; }
    .badge { border-radius: 6px; font-weight: 600; }
    .btn-group .btn { border-radius: 6px !important; margin: 0 2px; }
    .bg-light { background-color: #f8f9fa !important; }
</style>
@endpush
@endsection
