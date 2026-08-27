@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-file-invoice-dollar"></i> Enterprise Payroll</div>
            <h1>{{ $self ? 'My Payslips' : 'Payslips' }}</h1>
            <p>Download generated enterprise payslips for approved or generated payroll.</p>
        </div>
    </div>

    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-card">
        <!-- Table Card Header -->
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h5 class="ep-table-title">{{ $self ? 'My Payslips' : 'Payslips' }}</h5>
                    <p class="ep-table-subtitle">Download generated enterprise payslips for approved or generated payroll.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                <!-- No additional actions needed -->
            </div>
        </div>

        <!-- Attached Filters -->
        <div class="ep-card-filters">
            <form method="GET" action="{{ $self ? route('enterprise-payroll.self.payslips') : route('enterprise-payroll.payslips.index') }}" class="row align-items-end ep-form" id="filterForm">
                @if(!$self)
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Employee</label>
                    <select name="employee_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        @foreach($employees ?? [] as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Month</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        <option value="">All Months</option>
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="{{ request('year') }}" onkeyup="if(event.keyCode === 13) this.form.submit()" placeholder="Year">
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ $self ? route('enterprise-payroll.self.payslips') : route('enterprise-payroll.payslips.index') }}" class="ep-btn ep-btn-light w-100"><i class="fas fa-sync-alt"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table js-orb-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Payslip No</th>
                            <th>Employee</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th class="text-right">Net Salary</th>
                            <th>Generated At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($payslips) && count($payslips) > 0)
                            @foreach($payslips as $payslip)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $payslip->payslip_no }}</td>
                                    <td>{{ optional($payslip->employee)->display_name }}</td>
                                    <td>{{ \Carbon\Carbon::create()->month($payslip->month)->format('F') }}</td>
                                    <td>{{ $payslip->year }}</td>
                                    <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) optional($payslip->payroll)->net_salary, 2) }}</td>
                                    <td>{{ $payslip->generated_at ? $payslip->generated_at->format('d M Y h:i A') : '-' }}</td>
                                    <td>
                                        <a class="ep-btn ep-btn-light" style="height: 30px; padding: 0 10px;" href="{{ route('enterprise-payroll.payslips.download', $payslip) }}">
                                            <i class="fas fa-download text-primary"></i> Download
                                        </a>
                                        @if(!$self && ($accesses['enterprise_payslip.generate'] ?? false))
                                            <form action="{{ route('enterprise-payroll.payslips.regenerate', $payslip) }}" method="POST" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to regenerate this payslip PDF without recalculating the salary?')">
                                                @csrf
                                                <button type="submit" class="ep-btn ep-btn-light text-warning" style="height: 30px; padding: 0 10px;">
                                                    <i class="fas fa-sync-alt"></i> Regenerate
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
    if (window.jQuery && $.fn.DataTable) {
        $('.js-orb-datatable').each(function() {
            var $table = $(this);
            $table.DataTable({
                pageLength: 25,
                order: [],
                searching: false,
                lengthChange: true,
                autoWidth: false,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    emptyTable: 'No payslips found.',
                    zeroRecords: 'No matching records found.'
                },
                dom: '<"crud-dt-toolbar"<"crud-dt-left"l><"crud-dt-right"B>>rt<"orb-table-footer"ip>',
                buttons: [
                    { extend: 'csvHtml5', text: '<i class="fas fa-file-csv text-muted"></i> CSV', className: 'crud-export-btn' },
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel text-success"></i> Excel', className: 'crud-export-btn' },
                    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf text-danger"></i> PDF', className: 'crud-export-btn' },
                    { extend: 'print', text: '<i class="fas fa-print text-primary"></i> Print', className: 'crud-export-btn' }
                ]
            });
        });
    }
</script>
@endsection
