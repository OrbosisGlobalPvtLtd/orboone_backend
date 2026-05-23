@php
    $payableDays = $run->payrolls->sum('payable_days');
    $lwpDays = $run->payrolls->sum('lwp_days');
    $attendanceDed = $run->payrolls->sum('attendance_deduction');
    $payslipsGen = \App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM::whereIn('payroll_id', $run->payrolls->pluck('id'))->count();
@endphp
@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
<style>
    .ep-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .ep-summary-card { background: #fff; padding: 16px; border-radius: 12px; border: 1px solid var(--ep-border); box-shadow: 0 2px 8px rgba(16, 24, 40, .02); }
    .ep-summary-val { font-size: 20px; font-weight: 800; color: var(--ep-text); margin-bottom: 2px; }
    .ep-summary-lbl { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--ep-muted); }
    .ep-emp-card { background: #fff; border-radius: 16px; border: 1px solid var(--ep-border); padding: 24px; box-shadow: 0 4px 12px rgba(16, 24, 40, .03); margin-bottom: 24px; }
    .ep-emp-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--ep-border); padding-bottom: 16px; margin-bottom: 16px; }
    .ep-emp-name { font-size: 20px; font-weight: 900; color: var(--ep-primary); }
    .ep-emp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; }
    .ep-emp-stat { background: var(--ep-bg); padding: 12px; border-radius: 8px; text-align: center; }
</style>
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero align-items-center">
        <div>
            <div class="ep-kicker"><i class="fas fa-file-invoice-dollar"></i> Payroll Run Details</div>
            <h1>Payroll Run - {{ \Carbon\Carbon::createFromDate($run->year, $run->month, 1)->format('F Y') }}</h1>
            <p>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $run->status]) | Employees: {{ $run->total_employees }} | Net: ₹{{ number_format((float) $run->total_net, 2) }}</p>
        </div>
        <div class="ep-hero-actions">
            @if($run->status !== 'locked' && auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.approve'))
                <form method="POST" action="{{ route('enterprise-payroll.runs.approve', $run) }}">@csrf<button class="ep-btn ep-btn-success"><i class="fas fa-check"></i> Approve</button></form>
            @endif
            @if($run->status !== 'locked' && auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.lock'))
                <form method="POST" action="{{ route('enterprise-payroll.runs.lock', $run) }}">@csrf<button class="ep-btn ep-btn-danger"><i class="fas fa-lock"></i> Lock</button></form>
            @endif
            @if($run->status === 'locked' && auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.reopen'))
                <form method="POST" action="{{ route('enterprise-payroll.runs.reopen', $run) }}">@csrf<button class="ep-btn ep-btn-warning"><i class="fas fa-unlock"></i> Reopen</button></form>
            @endif
            @if(auth()->user() && auth()->user()->hasPermission('enterprise_payslip.generate'))
                <form method="POST" action="{{ route('enterprise-payroll.runs.payslips.generate', $run) }}">@csrf<button class="ep-btn ep-btn-primary"><i class="fas fa-file-pdf"></i> Generate Payslips</button></form>
            @endif
            <a href="{{ route('enterprise-payroll.runs.index') }}" class="ep-btn ep-btn-light"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-summary-grid">
        <div class="ep-summary-card"><div class="ep-summary-val">{{ $run->total_employees }}</div><div class="ep-summary-lbl">Employees Processed</div></div>
        <div class="ep-summary-card"><div class="ep-summary-val">₹{{ number_format((float) $run->total_gross, 2) }}</div><div class="ep-summary-lbl">Gross Payroll</div></div>
        <div class="ep-summary-card"><div class="ep-summary-val">₹{{ number_format((float) $run->total_deductions, 2) }}</div><div class="ep-summary-lbl">Total Deductions</div></div>
        <div class="ep-summary-card"><div class="ep-summary-val text-success">₹{{ number_format((float) $run->total_net, 2) }}</div><div class="ep-summary-lbl">Net Payroll</div></div>
        <div class="ep-summary-card"><div class="ep-summary-val">{{ $payableDays }}</div><div class="ep-summary-lbl">Payable Days</div></div>
        <div class="ep-summary-card"><div class="ep-summary-val">{{ $lwpDays }}</div><div class="ep-summary-lbl">LWP Days</div></div>
        <div class="ep-summary-card"><div class="ep-summary-val">₹{{ number_format((float) $attendanceDed, 2) }}</div><div class="ep-summary-lbl">Attendance Deduction</div></div>
        <div class="ep-summary-card"><div class="ep-summary-val">{{ $payslipsGen }}</div><div class="ep-summary-lbl">Payslips Generated</div></div>
    </div>

    @if($run->payrolls->count() == 1)
        @php $p = $run->payrolls->first(); @endphp
        <div class="ep-emp-card">
            <div class="ep-emp-header">
                <div class="ep-emp-name"><i class="fas fa-user-circle mr-2"></i> {{ optional($p->employee)->display_name }} ({{ optional($p->employee)->employee_code }})</div>
                <div>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $p->status])</div>
            </div>
            <div class="ep-emp-grid">
                <div class="ep-emp-stat"><div class="ep-summary-val text-success">₹{{ number_format((float) $p->net_salary, 2) }}</div><div class="ep-summary-lbl">Net Salary</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">₹{{ number_format((float) $p->gross_salary, 2) }}</div><div class="ep-summary-lbl">Gross Salary</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val text-danger">₹{{ number_format((float) $p->total_deductions, 2) }}</div><div class="ep-summary-lbl">Deductions</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->payable_days }}</div><div class="ep-summary-lbl">Payable Days</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->present_days }}</div><div class="ep-summary-lbl">Present</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->paid_leave_days }}</div><div class="ep-summary-lbl">Paid Leave</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->sick_leave_days }}</div><div class="ep-summary-lbl">Sick Leave</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->comp_off_days }}</div><div class="ep-summary-lbl">Comp Off</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val text-warning">{{ $p->half_days }}</div><div class="ep-summary-lbl">Half Day</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val text-danger">{{ $p->lwp_days }}</div><div class="ep-summary-lbl">LWP</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val text-danger">{{ $p->absent_days }}</div><div class="ep-summary-lbl">Absent</div></div>
            </div>
        </div>
    @endif

    <div class="ep-card">
        <!-- Table Card Header -->
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h5 class="ep-table-title">Employee Payroll Details</h5>
                    <p class="ep-table-subtitle">Comprehensive breakdown of monthly attendance and salary payout for all employees.</p>
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
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th>Payable</th>
                            <th>Present</th>
                            <th>Paid Leave</th>
                            <th>Sick</th>
                            <th>Comp Off</th>
                            <th>Half Day</th>
                            <th>LWP</th>
                            <th>Absent</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right">Deductions</th>
                            <th class="text-right">Net</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($run->payrolls as $payroll)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ optional($payroll->employee)->display_name }}</td>
                            <td>{{ $payroll->payable_days }}</td>
                            <td>{{ $payroll->present_days }}</td>
                            <td>{{ $payroll->paid_leave_days }}</td>
                            <td>{{ $payroll->sick_leave_days }}</td>
                            <td>{{ $payroll->comp_off_days }}</td>
                            <td>{{ $payroll->half_days }}</td>
                            <td>{{ $payroll->lwp_days }}</td>
                            <td>{{ $payroll->absent_days }}</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->gross_salary, 2) }}</td>
                            <td class="text-right text-danger">₹{{ number_format((float) $payroll->total_deductions, 2) }}</td>
                            <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) $payroll->net_salary, 2) }}</td>
                            <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $payroll->status])</td>
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
                    emptyTable: 'No payroll records found.',
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
