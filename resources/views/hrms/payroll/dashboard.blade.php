@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'payroll_dashboard'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Payroll Dashboard</h2>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Active Employees</h5>
                    <p class="display-4 font-weight-bold">{{ $employeesCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Salaries (This Month)</h5>
                    <p class="display-4 font-weight-bold">₹{{ number_format($totalSalaries, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending F&F</h5>
                    <p class="display-4 font-weight-bold">0</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions & Employee Selection -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('pages.payroll.payrollrun') }}" class="btn btn-outline-primary btn-block py-3">
                                <i class="fas fa-play-circle mb-2"></i><br>Run Payroll
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('pages.payroll.index') }}" class="btn btn-outline-secondary btn-block py-3">
                                <i class="fas fa-list mb-2"></i><br>Salary Structures
                            </a>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mt-4 mb-3">Generate Employee Salary Slip</h5>
                    <form action="{{ route('pages.payroll.salaryslip.download') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="employee_id">Select Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control select2" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} (ID: {{ $employee->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="month">Select Month</label>
                            <input type="month" name="month" id="month" class="form-control" value="{{ date('Y-m') }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Generate & Download PDF</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Payrolls -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Payrolls</h5>
                    <a href="{{ route('pages.payroll.payslips') }}" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Month/Year</th>
                                    <th>Net Salary</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayrolls as $payroll)
                                    <tr>
                                        <td>{{ $payroll->employee->name }}</td>
                                        <td>{{ date('M Y', strtotime($payroll->year . '-' . $payroll->month . '-01')) }}</td>
                                        <td>₹{{ number_format($payroll->net_salary, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $payroll->status == 'locked' ? 'success' : 'warning' }}">
                                                {{ ucfirst($payroll->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No recent payrolls found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 10px; }
    .card-header { border-bottom: 1px solid #f8f9fa; border-radius: 10px 10px 0 0 !important; }
    .display-4 { font-size: 2.5rem; }
    .btn-outline-primary, .btn-outline-secondary { border-radius: 8px; border-width: 2px; }
    .table thead th { border-top: 0; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #6c757d; }
</style>
@endpush
@endsection
