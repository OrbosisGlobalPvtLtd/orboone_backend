@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'payroll_run'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">Generate Employee Salary Slip</h2>
            <p class="text-muted">Select an employee and period to generate and download their professional payslip.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-file-pdf mr-2"></i>Payslip Generation Tool</h5>
                </div>
                <div class="card-body py-4">
                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('pages.payroll.salaryslip.download') }}">
                        @csrf
                        <div class="form-group mb-4">
                            <label for="employee_id" class="font-weight-bold text-dark">Select Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control form-control-lg custom-select" required>
                                <option value="">-- Search and Select Employee --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }} (ID: {{ $employee->id }}) - {{ $employee->position->name ?? 'No Position' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-1 d-block">All active employees are listed above.</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="month" class="font-weight-bold text-dark">Select Month</label>
                            <input type="month" name="month" id="month" class="form-control form-control-lg" value="{{ date('Y-m') }}" required>
                            <small class="text-muted mt-1 d-block">Choose the month for which you want to generate the slip.</small>
                        </div>

                        <div class="mt-5">
                            <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm py-3">
                                <i class="fas fa-download mr-2"></i> Generate & Download PDF
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light border-0 py-3">
                    <div class="d-flex align-items-center text-muted small">
                        <i class="fas fa-info-circle mr-2 fa-lg text-primary"></i>
                        <span>If payroll is not generated for the selected month, the system will calculate it on-the-fly based on attendance records.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-6 d-none d-md-block">
            <div class="h-100 d-flex flex-column justify-content-center px-lg-5">
                <div class="text-center mb-4">
                    <div class="bg-soft-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; background-color: rgba(40, 167, 69, 0.1);">
                        <i class="fas fa-check-double fa-3x text-success"></i>
                    </div>
                    <h3>Fully Automated</h3>
                    <p class="text-muted px-lg-5">Our system automatically fetches attendance data and applies assigned salary structures to ensure 100% accuracy in every payslip.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 15px; overflow: hidden; }
    .btn-success { border-radius: 12px; font-weight: 700; letter-spacing: 0.5px; }
    .form-control-lg { border-radius: 10px; height: calc(1.5em + 1.25rem + 2px); }
    .custom-select { border-radius: 10px; }
</style>
@endpush
@endsection
