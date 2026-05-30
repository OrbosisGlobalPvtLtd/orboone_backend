@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'payroll_run'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-1">Monthly Payslips</h2>
            <p class="text-muted"><i class="fas fa-calendar-alt mr-1"></i> For {{ date('F Y', strtotime($monthInput . '-01')) }}</p>
        </div>
        <div class="col-md-6 text-md-right">
            <form method="POST" action="{{ route('pages.payroll.payslipgenerate', $monthInput) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary shadow-sm px-4">
                    <i class="fas fa-sync-alt mr-1"></i> Generate / Re-generate All
                </button>
            </form>

            <a href="{{ route('pages.payroll.payslip.downloadall', $monthInput) }}" 
               class="btn btn-success shadow-sm px-4 ml-2" 
               onclick="return confirm('This will generate and download a ZIP of all payslips for the month. Continue?');">
                <i class="fas fa-file-archive mr-1"></i> Download All (ZIP)
            </a>
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
            <h5 class="mb-0">Employee Payroll Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light text-uppercase small font-weight-bold text-muted">
                        <tr>
                            <th class="px-4">Employee Details</th>
                            <th>Working / Paid Days</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th class="text-right px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $p)
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        @php
                                            $passportPhotoUrl = resolveEmployeeAdminAvatar($p->employee);
                                            $employeeName = $p->employee->name ?? 'Employee';
                                            $initial = strtoupper(substr($employeeName, 0, 1));
                                        @endphp
                                        @if($passportPhotoUrl)
                                            <div class="avatar-sm mr-3" style="width: 40px; height: 40px; border-radius: 50%;">
                                                <img src="{{ $passportPhotoUrl }}"
                                                     alt="{{ $employeeName }}"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                                <span style="display: none;">{{ $initial }}</span>
                                            </div>
                                        @else
                                            <div class="avatar-sm mr-3">
                                                <div class="rounded-circle bg-soft-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(29, 44, 162, 0.1); color: #1d2ca2; font-weight: bold;">
                                                    {{ $initial }}
                                                </div>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-weight-bold d-block text-dark">{{ $p->employee->name }}</span>
                                            <small class="text-muted">ID: {{ $p->employee->id }} | {{ $p->employee->position->name ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light border">{{ $p->paid_days }} / {{ $p->working_days }} Days</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-success">₹{{ number_format($p->net_salary, 2) }}</span>
                                </td>
                                <td>
                                    @if($p->payslip)
                                        <span class="badge badge-success-soft text-success px-2 py-1">
                                            <i class="fas fa-check-circle mr-1"></i> Generated
                                        </span>
                                    @else
                                        <span class="badge badge-warning-soft text-warning px-2 py-1">
                                            <i class="fas fa-hourglass-half mr-1"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right px-4">
                                    <a class="btn btn-sm btn-outline-success px-3"
                                       href="{{ route('pages.payroll.payslip.download.employee', [$p->employee->id, $monthInput]) }}">
                                        <i class="fas fa-download mr-1"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-file-invoice fa-3x text-light mb-3 d-block"></i>
                                    <p class="text-muted">No payroll records found for this month.</p>
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
    .avatar-sm { overflow: hidden !important; border-radius: 50% !important; }
    .avatar-sm img {
        width: 100% !important;
        height: 100% !important;
        border-radius: inherit !important;
        object-fit: cover !important;
        display: block !important;
    }
    .table thead th { border-top: 0; letter-spacing: 0.5px; }
    .badge-success-soft { background-color: rgba(40, 167, 69, 0.1); }
    .badge-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .btn-primary, .btn-success { border-radius: 8px; font-weight: 600; }
    .btn-outline-success { border-radius: 6px; }
</style>
@endpush
@endsection
