@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'payroll_dashboard'])

@section('_content')
    <div class="orb-page-header">
        <div class="orb-page-header-content">
            <div class="orb-page-kicker">
                <i class="fas fa-wallet"></i> HRMS &bull; Payroll
            </div>

            <h1 class="orb-page-title">
                Payroll Dashboard
            </h1>

            <p class="orb-page-subtitle">
                Comprehensive overview of salary aggregation, active employee structures, and monthly statutory computations.
            </p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="orb-table-card mb-0" style="border-radius: 20px; background: radial-gradient(circle at top right, rgba(75, 0, 232, .12), transparent 28%), #fff;">
                <div class="orb-card-body p-4 d-flex align-items-center gap-3">
                    <div class="orb-kpi-icon" style="width: 48px; height: 48px; border-radius: 14px; background: rgba(75, 0, 232, .10); color: var(--orb-primary); display: flex; align-items: center; justify-content: center; font-size: 18px;"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="orb-muted small font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">Total Active Employees</div>
                        <div class="h2 mb-0 font-weight-black mt-1" style="color: #101828;">{{ $employeesCount }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="orb-table-card mb-0" style="border-radius: 20px; background: radial-gradient(circle at top right, rgba(18, 183, 106, .12), transparent 28%), #fff;">
                <div class="orb-card-body p-4 d-flex align-items-center gap-3">
                    <div class="orb-kpi-icon" style="width: 48px; height: 48px; border-radius: 14px; background: rgba(18, 183, 106, .10); color: #12B76A; display: flex; align-items: center; justify-content: center; font-size: 18px;"><i class="fas fa-wallet"></i></div>
                    <div>
                        <div class="orb-muted small font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">Total Salaries (This Month)</div>
                        <div class="h2 mb-0 font-weight-black mt-1" style="color: #12B76A;">₹{{ number_format($totalSalaries, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="orb-table-card mb-0" style="border-radius: 20px; background: radial-gradient(circle at top right, rgba(14, 165, 233, .12), transparent 28%), #fff;">
                <div class="orb-card-body p-4 d-flex align-items-center gap-3">
                    <div class="orb-kpi-icon" style="width: 48px; height: 48px; border-radius: 14px; background: rgba(14, 165, 233, .10); color: #0EA5E9; display: flex; align-items: center; justify-content: center; font-size: 18px;"><i class="fas fa-handshake"></i></div>
                    <div>
                        <div class="orb-muted small font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">Pending F&F</div>
                        <div class="h2 mb-0 font-weight-black mt-1" style="color: #0EA5E9;">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions & Employee Selection -->
        <div class="col-md-6">
            <div class="orb-table-card mb-4">
                <div class="orb-table-toolbar justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-black"><i class="fas fa-bolt text-primary mr-1"></i> Quick Actions</h5>
                </div>
                <div class="orb-card-body p-4">
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
                        <button type="submit" class="orb-btn-primary btn-block justify-content-center">Generate & Download PDF</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Payrolls -->
        <div class="col-md-6">
            <div class="orb-table-card mb-4">
                <div class="orb-table-toolbar justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-black"><i class="fas fa-history text-primary mr-1"></i> Recent Payrolls</h5>
                    <a href="{{ route('pages.payroll.payslips') }}" class="orb-btn-light py-1 px-3" style="min-height: 32px !important; font-size: 12px;"><i class="fas fa-arrow-right"></i> View All</a>
                </div>
                <div class="orb-table-wrapper p-0">
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
