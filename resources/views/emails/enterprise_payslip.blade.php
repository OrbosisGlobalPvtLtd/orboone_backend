@extends('emails.layouts.enterprise')

@section('content')
<span class="badge">Payslip Released</span>
<h2 style="margin:0 0 8px;">Hello, {{ $employee->display_name }}</h2>
<p style="margin:0 0 12px;color:#475467;">Your payslip for the month of <strong>{{ \Carbon\Carbon::create(null, $payroll->month)->format('F') }} {{ $payroll->year }}</strong> has been generated and is now available.</p>

<table class="meta">
    <tr><td class="label">Employee Name</td><td class="value">{{ $employee->display_name }}</td></tr>
    <tr><td class="label">Employee ID</td><td class="value">{{ $employee->employee_code ?? $employee->id }}</td></tr>
    <tr><td class="label">Payslip No</td><td class="value">{{ $payslip->payslip_no }}</td></tr>
    <tr><td class="label">Net Salary</td><td class="value">₹{{ number_format((float) $payroll->net_salary, 2) }}</td></tr>
</table>

<p style="margin:16px 0 0;color:#475467;font-size:13px;">
    Please find the attached PDF copy of your payslip for your records. You can also view it directly in your OrboOne HRMS portal.
</p>
@endsection
