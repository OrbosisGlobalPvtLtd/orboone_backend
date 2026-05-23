<!DOCTYPE html>
{{-- LEGACY PAYROLL MODULE - DO NOT USE for new enterprise payroll payslips. --}}
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ date('F Y', strtotime($month . '-01')) }} - {{ $p->employee->display_name ?? 'Employee' }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; }
        .container { padding: 34px; }
        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 16px; margin-bottom: 18px; }
        .company-name { font-size: 24px; font-weight: bold; color: #007bff; text-transform: uppercase; }
        .company-address { font-size: 12px; color: #777; margin-top: 5px; }
        .payslip-title { font-size: 18px; font-weight: bold; text-align: center; margin: 18px 0; background-color: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; }
        .info-section { width: 100%; margin-bottom: 24px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; color: #555; width: 42%; }
        .value { color: #333; width: 58%; }
        .salary-section { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .salary-section th { background-color: #007bff; color: white; padding: 10px; text-align: left; }
        .salary-section td { padding: 9px; border: 1px solid #dee2e6; }
        .salary-section .amount { text-align: right; font-weight: bold; }
        .total-row { background-color: #f8f9fa; font-weight: bold; }
        .net-salary { font-size: 18px; color: #28a745; background-color: #e9f7ef; padding: 14px; border: 1px solid #c3e6cb; margin-top: 26px; text-align: center; }
        .note { margin-top: 34px; font-size: 12px; color: #555; }
        .footer { margin-top: 38px; font-size: 11px; color: #777; border-top: 1px solid #dee2e6; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">Orbosis HRM</div>
            <div class="company-address">LIG Square, Indore, MP - 452001 | Phone: +91 62650715881</div>
        </div>

        <div class="payslip-title">Payslip for the month of {{ date('F Y', strtotime($month . '-01')) }}</div>

        <table class="info-section">
            <tr>
                <td style="width: 50%;">
                    <table class="info-table">
                        <tr><td class="label">Employee ID:</td><td class="value">{{ $p->employee->employee_code ?? $p->employee->id }}</td></tr>
                        <tr><td class="label">Name:</td><td class="value">{{ $p->employee->display_name ?? 'N/A' }}</td></tr>
                        <tr><td class="label">Department:</td><td class="value">{{ $p->employee->department->name ?? 'N/A' }}</td></tr>
                        <tr><td class="label">Position:</td><td class="value">{{ $p->employee->position->name ?? 'N/A' }}</td></tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table class="info-table">
                        <tr><td class="label">Working Days:</td><td class="value">{{ $p->working_days }}</td></tr>
                        <tr><td class="label">Payable Days:</td><td class="value">{{ $p->payable_days ?? $p->paid_days }}</td></tr>
                        <tr><td class="label">LWP / Absent / Half:</td><td class="value">{{ $p->lwp_days ?? 0 }} / {{ $p->absent_days ?? 0 }} / {{ $p->half_days ?? 0 }}</td></tr>
                        <tr><td class="label">Payroll Status:</td><td class="value">{{ ucfirst($p->status) }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="salary-section">
            <thead>
                <tr>
                    <th colspan="2">Earnings</th>
                    <th colspan="2">Deductions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td class="amount">Rs {{ number_format($p->basic, 2) }}</td>
                    <td>Professional Tax (PT)</td>
                    <td class="amount">Rs {{ number_format($p->pt, 2) }}</td>
                </tr>
                <tr>
                    <td>House Rent Allowance (HRA)</td>
                    <td class="amount">Rs {{ number_format($p->hra, 2) }}</td>
                    <td>TDS/Income Tax</td>
                    <td class="amount">Rs {{ number_format($p->tds ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Other Allowances</td>
                    <td class="amount">Rs {{ number_format($p->allowance, 2) }}</td>
                    <td>Other Deductions</td>
                    <td class="amount">Rs {{ number_format($p->other_deductions ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Bonus / Incentive</td>
                    <td class="amount">Rs {{ number_format(($p->bonus ?? 0) + ($p->incentive ?? 0), 2) }}</td>
                    <td>Attendance Loss (Audit)</td>
                    <td class="amount">Rs {{ number_format(($p->lwp_deduction ?? 0) + ($p->absent_deduction ?? 0) + ($p->half_day_deduction ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td>Reimbursements</td>
                    <td class="amount">Rs {{ number_format($p->reimbursements ?? 0, 2) }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="total-row">
                    <td>Gross Earnings</td>
                    <td class="amount">Rs {{ number_format($p->gross_salary, 2) }}</td>
                    <td>Total Deductions</td>
                    <td class="amount">Rs {{ number_format($p->total_deductions, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="net-salary">
            NET PAYABLE SALARY: Rs {{ number_format($p->net_salary, 2) }}
            <br>
            <span style="font-size: 12px; font-weight: normal; color: #555;">(Amount in words: {{ convertNumberToWords($p->net_salary) }} Rupees Only)</span>
        </div>

        <p class="note">Note: This payslip is generated only from approved payroll data for the selected period.</p>

        <div class="footer">
            &copy; {{ date('Y') }} Orbosis HRM. All Rights Reserved.
        </div>
    </div>
</body>
</html>
