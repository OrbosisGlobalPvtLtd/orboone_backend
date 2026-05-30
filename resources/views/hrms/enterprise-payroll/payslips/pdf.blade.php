<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $payslipNo }}</title>
    <style>
        @page { margin: 28px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .header { border-bottom: 3px solid #4B00E8; padding-bottom: 12px; margin-bottom: 18px; }
        .logo { height: 52px; }
        .company { float: right; text-align: right; line-height: 1.45; color: #374151; }
        .company h1 { margin: 0; font-size: 20px; color: #4B00E8; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin: 16px 0; color: #111827; }
        .section-title { background: #F4F2FF; color: #4B00E8; font-weight: bold; padding: 8px 10px; border: 1px solid #E7EAF3; margin-top: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #E5E7EB; padding: 8px; vertical-align: top; }
        th { background: #F9FAFB; text-align: left; color: #374151; }
        .right { text-align: right; }
        .summary td { font-weight: bold; }
        .footer { margin-top: 22px; line-height: 1.55; }
        .signature { margin-top: 48px; text-align: right; font-weight: bold; }
        .page-break { page-break-before: always; }
        .disclaimer { border: 1px solid #E5E7EB; padding: 18px; line-height: 1.8; font-size: 13px; }
    </style>
</head>
<body>
    @php
        $snapshot = (array) ($payroll->calculation_snapshot['policy_snapshot'] ?? []);
        $ratios = (array) ($snapshot['payable_ratios'] ?? []);
        $creditWindow = (array) ($snapshot['salary_credit_window']['current'] ?? []);
        $futureCreditWindow = (array) ($snapshot['salary_credit_window']['future_target'] ?? []);
    @endphp
    <div class="header">
        <img class="logo" src="{{ public_path('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="Orbosis">
        <div class="company">
            <h1>Orbosis Global Pvt. Ltd.</h1>
            <div>Contact: +91 99999 99999</div>
            <div>Email: {{ config('hrms.emails.hr') ?: config('mail.from.address') }}</div>
            <div>GST: As per company records</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="title">Salary Slip - {{ $monthName }} {{ $payroll->year }}</div>

    <div class="section-title">Employee Details</div>
    <table>
        <tr>
            <th>Employee Name</th><td>{{ optional($employee)->display_name }}</td>
            <th>Employee ID</th><td>{{ $employee->employee_code ?? $employee->id }}</td>
        </tr>
        <tr>
            <th>Designation</th><td>{{ optional($employee->designation)->name ?? '-' }}</td>
            <th>Department</th><td>{{ optional($employee->department)->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Month & Year</th><td>{{ $monthName }} {{ $payroll->year }}</td>
            <th>Payslip No</th><td>{{ $payslipNo }}</td>
        </tr>
    </table>

    <div class="section-title">Attendance Summary</div>
    <table>
        <tr>
            <th>Payroll Basis</th><td>{{ $snapshot['salary_day_basis'] ?? '-' }}</td>
            <th>Working Day Mode</th><td>{{ $snapshot['working_day_mode'] ?? '-' }}</td>
            <th>Calendar Days</th><td>{{ $payroll->calculation_snapshot['calendar_days'] ?? $snapshot['calendar_days'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Policy Working Days</th><td>{{ $payroll->calculation_snapshot['policy_working_days'] ?? $snapshot['policy_working_days'] ?? $payroll->total_working_days }}</td>
            <th>Payable Days</th><td>{{ $payroll->payable_days }}</td>
            <th>Deduction Days</th><td>{{ number_format((float) $payroll->lwp_days + (float) $payroll->absent_days + ((float) $payroll->half_days * 0.5), 2) }}</td>
        </tr>
        <tr>
            <th>Total Working Days</th><td>{{ $payroll->total_working_days }}</td>
            <th>Present Days</th><td>{{ $payroll->present_days }}</td>
            <th>Per Day Salary</th><td>{{ number_format((float) $payroll->per_day_salary, 2) }}</td>
        </tr>
        <tr>
            <th>Half-Day</th><td>{{ $payroll->half_days }}</td>
            <th>Leave Without Pay</th><td>{{ $payroll->lwp_days }}</td>
            <th>Absent Days</th><td>{{ $payroll->absent_days }}</td>
        </tr>
        <tr>
            <th>Paid Leave</th><td>{{ $payroll->paid_leave_days }}</td>
            <th>Sick Leave</th><td>{{ $payroll->sick_leave_days }}</td>
            <th>Comp Off</th><td>{{ $payroll->comp_off_days }}</td>
        </tr>
    </table>

    <div class="section-title">Earnings & Deductions</div>
    <table>
        <tr><th>Earnings</th><th class="right">Amount</th><th>Deductions</th><th class="right">Amount</th></tr>
        <tr><td>Basic Salary</td><td class="right">{{ number_format((float) $payroll->basic_salary, 2) }}</td><td>Professional Tax</td><td class="right">{{ number_format((float) $payroll->professional_tax, 2) }}</td></tr>
        <tr><td>HRA</td><td class="right">{{ number_format((float) $payroll->hra, 2) }}</td><td>PF</td><td class="right">{{ number_format((float) ($payroll->calculation_snapshot['pf'] ?? 0), 2) }}</td></tr>
        <tr><td>Special Allowance</td><td class="right">{{ number_format((float) $payroll->special_allowance, 2) }}</td><td>ESI</td><td class="right">{{ number_format((float) ($payroll->calculation_snapshot['esi'] ?? 0), 2) }}</td></tr>
        <tr><td>Bonus</td><td class="right">{{ number_format((float) $payroll->bonus_amount, 2) }}</td><td>TDS</td><td class="right">{{ number_format((float) $payroll->tds, 2) }}</td></tr>
        <tr><td>Incentive</td><td class="right">{{ number_format((float) $payroll->incentive_amount, 2) }}</td><td>Attendance Deduction</td><td class="right">{{ number_format((float) $payroll->attendance_deduction, 2) }}</td></tr>
        <tr><td>Reimbursement</td><td class="right">{{ number_format((float) $payroll->reimbursement_amount, 2) }}</td><td>LWP Deduction</td><td class="right">{{ number_format((float) $payroll->lwp_deduction, 2) }}</td></tr>
        <tr><td></td><td></td><td>Half-Day Deduction</td><td class="right">{{ number_format((float) $payroll->half_day_deduction, 2) }}</td></tr>
        <tr><td></td><td></td><td>Absent Deduction</td><td class="right">{{ number_format((float) $payroll->absent_deduction, 2) }}</td></tr>
        <tr><td></td><td></td><td>Other Deduction</td><td class="right">{{ number_format((float) $payroll->other_deduction, 2) }}</td></tr>
        <tr class="summary"><td>Gross Salary</td><td class="right">{{ number_format((float) $payroll->gross_salary, 2) }}</td><td>Total Deductions</td><td class="right">{{ number_format((float) $payroll->total_deductions, 2) }}</td></tr>
    </table>

    <div class="footer">
        <table>
            <tr><th>Net Salary</th><td class="right">{{ number_format((float) $payroll->net_salary, 2) }}</td></tr>
            <tr><th>Net Salary in Words</th><td>{{ $payroll->net_salary_words }}</td></tr>
            <tr>
                <th>Salary Credit Window</th>
                <td>
                    {{ ($creditWindow['start_day'] ?? 7) }}th - {{ ($creditWindow['end_day'] ?? 10) }}th
                    (Future Target: {{ ($futureCreditWindow['start_day'] ?? 5) }}th - {{ ($futureCreditWindow['end_day'] ?? 7) }}th)
                </td>
            </tr>
        </table>
    </div>

    <div class="signature">
        For Orbosis Global Pvt. Ltd.<br><br>
        HR Manager
    </div>

    <div class="page-break"></div>
    <div class="header">
        <img class="logo" src="{{ public_path('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="Orbosis">
        <div class="company"><h1>Orbosis Global Pvt. Ltd.</h1><div>Salary Slip Disclaimer</div></div>
        <div style="clear: both;"></div>
    </div>
    <div class="section-title">Disclaimer</div>
    <div class="disclaimer">
        Salary calculation is strictly based on daily Punch-In and Punch-Out records.
        Missing, incomplete, or incorrect punch records will cause deductions as per company policy.
        Attendance, leave, LWP, missed punch, late/early violations, approved reimbursements, bonus and incentives are consumed from approved HRMS records for the selected payroll month.
        This is a system generated salary slip and is valid without a physical signature.
    </div>
</body>
</html>
