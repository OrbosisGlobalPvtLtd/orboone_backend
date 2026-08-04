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
        @if(auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.generate'))
            <form method="POST" action="{{ route('enterprise-payroll.runs.generate') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                @if(!empty($employee_id))
                    <input type="hidden" name="employee_id" value="{{ $employee_id }}">
                @endif
                <button class="ep-btn ep-btn-primary"><i class="fas fa-play"></i> Generate Payroll</button>
            </form>
        @endif
    </div>

    @if(!empty($hasPendingRegularizations) || !empty($payrollErrors))
        <div class="alert alert-warning border-0 shadow-sm mb-4" style="background: #FFFBEB; border-left: 4px solid #F59E0B !important; border-radius: 12px; padding: 16px 20px;">
            <div class="d-flex align-items-center mb-1">
                <i class="fas fa-exclamation-triangle text-warning mr-2" style="font-size: 18px;"></i>
                <strong class="text-dark" style="font-size: 15px;">Attendance Review</strong>
            </div>
            <p class="mb-0 text-dark small" style="line-height: 1.5;">
                Pending attendance regularization exists. Payroll can still be generated. Any future approvals should be adjusted in the next payroll or arrears process.
            </p>
            @if(!empty($payrollErrors))
                <ul class="mb-0 mt-2 text-danger small pl-3">
                    @foreach($payrollErrors as $error)
                        <li><strong>{{ $error['employee'] }}:</strong> {{ $error['error'] }}</li>
                    @endforeach
                </ul>
            @endif
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
                            <th class="text-right">Base Gross</th>
                            <th class="text-right">Bonus / Incentive</th>
                            <th class="text-right">Reimbursement</th>
                            <th class="text-right">Gross Earnings</th>
                            <th class="text-right">Attendance Ded.</th>
                            <th class="text-right">Statutory & Other Ded.</th>
                            <th class="text-right">Total Deductions</th>
                            <th class="text-right">Net Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $row)
                        @php
                            $payableDays = $row['attendance']['payable_days'] ?? $row['payable_days'] ?? 0;
                            $bonusIncentive = round(($row['bonus_amount'] ?? 0) + ($row['incentive_amount'] ?? 0), 2);
                            $reimbursement = round($row['reimbursement_amount'] ?? 0, 2);
                            $grossEarnings = round($row['gross_salary'] ?? 0, 2);
                            $baseGross = round($grossEarnings - $bonusIncentive - $reimbursement, 2);
                            $attendanceDed = round($row['attendance_deduction'] ?? 0, 2);
                            $statutoryDed = round(
                                ($row['professional_tax'] ?? 0) +
                                ($row['pf'] ?? 0) +
                                ($row['esi'] ?? 0) +
                                ($row['tds'] ?? 0) +
                                ($row['other_deduction'] ?? 0),
                                2
                            );
                            $totalDeductions = round($row['total_deductions'] ?? 0, 2);
                            $netSalary = round($row['net_salary'] ?? 0, 2);
                        @endphp
                        <tr>
                            <td><strong>{{ $row['employee_name'] }}</strong></td>
                            <td><span class="badge badge-light border font-weight-bold px-2 py-1">{{ $payableDays }}</span></td>
                            <td class="text-right">₹{{ number_format($baseGross, 2) }}</td>
                            <td class="text-right text-success">₹{{ number_format($bonusIncentive, 2) }}</td>
                            <td class="text-right text-success">₹{{ number_format($reimbursement, 2) }}</td>
                            <td class="text-right font-weight-bold text-dark">₹{{ number_format($grossEarnings, 2) }}</td>
                            <td class="text-right text-danger">₹{{ number_format($attendanceDed, 2) }}</td>
                            <td class="text-right text-danger">₹{{ number_format($statutoryDed, 2) }}</td>
                            <td class="text-right text-danger font-weight-bold">₹{{ number_format($totalDeductions, 2) }}</td>
                            <td class="text-right font-weight-900 text-primary" style="font-size: 14px; background: #F8FAFC;">₹{{ number_format($netSalary, 2) }}</td>
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
