@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-calculator"></i> Preview</div>
            <h1>Payroll Preview {{ $month }}/{{ $year }}</h1>
            <p>Calculation preview uses active salary structure, payroll-ready attendance summaries and approved leave/claim data.</p>
        </div>
        @if(empty($errors) && auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.generate'))
            <form method="POST" action="{{ route('enterprise-payroll.runs.generate') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button class="ep-btn ep-btn-primary"><i class="fas fa-play"></i> Generate Payroll</button>
            </form>
        @endif
    </div>

    @if($errors)
        <div class="alert alert-danger border-0 shadow-sm">
            <strong>Payroll cannot be generated until these issues are fixed.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors as $error)
                    <li>{{ $error['employee'] }}: {{ $error['error'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ep-card">
        <!-- Table Card Header -->
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-calculator"></i></div>
                <div>
                    <h5 class="ep-table-title">Calculation Details</h5>
                    <p class="ep-table-subtitle">Summary of gross earnings, deductions, bonuses, and net pay before final generation.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                <!-- No additional actions needed -->
            </div>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table js-orb-datatable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Payable</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right">Deductions</th>
                            <th class="text-right">Net</th>
                            <th class="text-right">Bonus</th>
                            <th class="text-right">Incentive</th>
                            <th class="text-right">Reimbursement</th>
                            <th class="text-right">Attendance Deduction</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row['employee_name'] }}</td>
                            <td>{{ $row['attendance']['payable_days'] }}</td>
                            <td class="text-right">₹{{ number_format($row['gross_salary'], 2) }}</td>
                            <td class="text-right text-danger">₹{{ number_format($row['total_deductions'], 2) }}</td>
                            <td class="text-right font-weight-bold text-primary">₹{{ number_format($row['net_salary'], 2) }}</td>
                            <td class="text-right text-success">₹{{ number_format($row['bonus_amount'], 2) }}</td>
                            <td class="text-right text-success">₹{{ number_format($row['incentive_amount'], 2) }}</td>
                            <td class="text-right">₹{{ number_format($row['reimbursement_amount'], 2) }}</td>
                            <td class="text-right text-danger">₹{{ number_format($row['attendance_deduction'], 2) }}</td>
                        </tr>
                    @endforeach
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
                    emptyTable: 'No preview records found.',
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
