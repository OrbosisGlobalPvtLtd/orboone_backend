@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container-fluid py-4">
    {{-- Back Button --}}
    <div class="mb-3">
        <a href="{{ route('pages.payroll.monthlylist') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Monthly List
        </a>
    </div>

    @if(!$payroll)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> 
            No payroll record found for {{ date('F Y', strtotime($monthInput . '-01')) }}.
        </div>
    @else
        {{-- Header Card --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-briefcase"></i> Payroll Details - {{ date('F Y', strtotime($monthInput . '-01')) }}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Employee Name</h6>
                        <h5 class="mb-3">{{ $payroll->employee->name }}</h5>

                        <h6 class="text-muted">Employee ID</h6>
                        <p class="mb-3">{{ $payroll->employee->id }}</p>

                        <h6 class="text-muted">Department</h6>
                        <p class="mb-3">{{ $payroll->employee->department->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Position</h6>
                        <h5 class="mb-3">{{ $payroll->employee->position->name ?? 'N/A' }}</h5>

                        <h6 class="text-muted">Payroll Status</h6>
                        <p class="mb-3">
                            @if($payroll->status === 'locked')
                                <span class="badge bg-success"><i class="fas fa-lock"></i> Locked</span>
                            @elseif($payroll->status === 'approved')
                                <span class="badge bg-info"><i class="fas fa-check"></i> Approved</span>
                            @else
                                <span class="badge bg-warning"><i class="fas fa-hourglass-half"></i> Pending</span>
                            @endif
                        </p>

                        <h6 class="text-muted">Net Salary</h6>
                        <h4 class="text-success">₹ {{ number_format($payroll->net_salary, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Salary Breakdown --}}
        <div class="row">
            {{-- Earnings Side --}}
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="fas fa-plus-circle"></i> Earnings
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr>
                                    <td><strong>Basic Salary</strong></td>
                                    <td class="text-end">
                                        <strong>₹ {{ number_format($payroll->basic, 2) }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>HRA</td>
                                    <td class="text-end">₹ {{ number_format($payroll->hra, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Allowance</td>
                                    <td class="text-end">₹ {{ number_format($payroll->allowance, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Gross Salary</strong></td>
                                    <td class="text-end">
                                        <strong class="text-success">₹ {{ number_format($payroll->gross_salary, 2) }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- Attendance Info --}}
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-2">Attendance</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">Working Days</small>
                                    <p class="mb-0"><strong>{{ $payroll->working_days }}</strong></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Paid Days</small>
                                    <p class="mb-0"><strong>{{ $payroll->paid_days }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deductions Side --}}
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white fw-bold">
                        <i class="fas fa-minus-circle"></i> Deductions
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr>
                                    <td>Professional Tax (PT)</td>
                                    <td class="text-end">₹ {{ number_format($payroll->pt, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Provident Fund (PF)</td>
                                    <td class="text-end">₹ 0.00</td>
                                </tr>
                                <tr>
                                    <td>ESI</td>
                                    <td class="text-end">₹ 0.00</td>
                                </tr>
                                <tr>
                                    <td>TDS</td>
                                    <td class="text-end">₹ 0.00</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Total Deductions</strong></td>
                                    <td class="text-end">
                                        <strong class="text-danger">₹ {{ number_format($payroll->total_deductions, 2) }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- Net Salary Calculation --}}
                        <div class="mt-3 p-3 bg-success bg-opacity-10 rounded border border-success">
                            <h6 class="fw-bold mb-2 text-success">Net Salary Calculation</h6>
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <td>Gross Salary</td>
                                        <td class="text-end">₹ {{ number_format($payroll->gross_salary, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Minus:</strong> Total Deductions</td>
                                        <td class="text-end">₹ {{ number_format($payroll->total_deductions, 2) }}</td>
                                    </tr>
                                    <tr class="border-top border-success">
                                        <td><strong>Net Salary</strong></td>
                                        <td class="text-end">
                                            <strong class="text-success fs-5">₹ {{ number_format($payroll->net_salary, 2) }}</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Download Section --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">
                        <i class="fas fa-download"></i> Download Payslip
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Download your salary slip as PDF for {{ date('F Y', strtotime($monthInput . '-01')) }}</p>
                        <a href="{{ route('pages.payroll.payslip.download.employee', [auth()->user()->employee->id, $monthInput]) }}" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="row mt-4">
            <div class="col-12">
                <p class="text-muted small">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Last Updated:</strong> {{ $payroll->updated_at->format('d M Y H:i:s') }}
                </p>
            </div>
        </div>

    @endif

</div>
@endsection
