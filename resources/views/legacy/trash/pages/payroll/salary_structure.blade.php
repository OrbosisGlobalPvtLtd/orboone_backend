@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'my_salary_structure'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">My Salary Structure</h2>
        </div>
    </div>

    @if(!$structure)
        <div class="alert alert-warning shadow-sm border-0">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Salary structure not assigned yet. Please contact the HR department for more information.
        </div>
    @else
        <div class="row">
            <!-- Summary Card -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-file-invoice-dollar fa-2x text-primary"></i>
                            </div>
                            <h4>{{ $structure->name }}</h4>
                            <p class="text-muted">Effective From: {{ date('d M, Y', strtotime($structure->effective_date)) }}</p>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Basic Salary</span>
                                <span class="font-weight-bold text-primary">₹{{ number_format($structure->basic_salary, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>HRA</span>
                                <span class="font-weight-bold text-success">{{ $structure->hra_percent }}% of Basic</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Allowance</span>
                                <span class="font-weight-bold text-info">₹{{ number_format($structure->allowance, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Professional Tax (PT)</span>
                                <span class="font-weight-bold text-danger">₹{{ number_format($structure->pt_amount, 2) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Components Table -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detailed Components</h5>
                        <span class="badge badge-pill badge-primary px-3">Active</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4">Component</th>
                                        <th>Type</th>
                                        <th>Calculation Mode</th>
                                        <th class="text-right px-4">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="px-4 font-weight-bold">Basic Salary</td>
                                        <td>Earning</td>
                                        <td>Fixed Amount</td>
                                        <td class="text-right px-4">₹{{ number_format($structure->basic_salary, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 font-weight-bold">HRA (House Rent Allowance)</td>
                                        <td>Earning</td>
                                        <td>Percentage (%)</td>
                                        <td class="text-right px-4">{{ $structure->hra_percent }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 font-weight-bold">Other Allowance</td>
                                        <td>Earning</td>
                                        <td>Fixed Amount</td>
                                        <td class="text-right px-4">₹{{ number_format($structure->allowance, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 font-weight-bold">Professional Tax (PT)</td>
                                        <td>Deduction</td>
                                        <td>Fixed Amount</td>
                                        <td class="text-right px-4">₹{{ number_format($structure->pt_amount, 2) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="3" class="px-4 text-right">Estimated Gross Monthly Salary</td>
                                        <td class="text-right px-4 text-success">
                                            ₹{{ number_format($structure->basic_salary + ($structure->basic_salary * $structure->hra_percent / 100) + $structure->allowance, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Download Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-1">Quick Payslip Download</h5>
                                <p class="text-muted mb-0">Need your salary slip for a specific month? Select below and download instantly.</p>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <form id="downloadForm" class="form-row align-items-end justify-content-md-end">
                                    <div class="col-auto">
                                        <label for="month" class="sr-only">Select Month</label>
                                        <select id="month" name="month" class="form-control custom-select" required style="min-width: 200px;">
                                            @for($i = 0; $i < 12; $i++)
                                                <option value="{{ date('Y-m', strtotime("-{$i} months")) }}">
                                                    {{ date('F Y', strtotime("-{$i} months")) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" onclick="prepareDownload()" class="btn btn-primary px-4">
                                            <i class="fas fa-download mr-2"></i> Download PDF
                                        </button>
                                        <a id="downloadLink" href="#" style="display: none;"></a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .card { border-radius: 12px; }
    .card-header { border-radius: 12px 12px 0 0 !important; }
    .table thead th { border-top: 0; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
    .list-group-item { border-color: rgba(0,0,0,0.05); }
    .custom-select { height: calc(1.5em + .75rem + 2px); border-radius: 8px; }
    .btn-primary { border-radius: 8px; font-weight: 600; }
</style>
@endpush

@push('scripts')
<script>
    function prepareDownload() {
        const month = document.getElementById('month').value;
        const employeeId = {{ auth()->user()->employee->id ?? 'null' }};

        if (!employeeId || employeeId === 'null') {
            alert('Employee ID not found. Please contact administrator.');
            return;
        }

        if (!month) {
            alert('Please select a month.');
            return;
        }

        const url = `/payroll/payslip/${employeeId}/${month}/download`;
        const link = document.getElementById('downloadLink');
        link.href = url;
        link.click();
    }
</script>
@endpush
@endsection
