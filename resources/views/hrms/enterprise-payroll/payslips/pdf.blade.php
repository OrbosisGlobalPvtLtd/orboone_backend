<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $payslipNo }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 10px;
            line-height: 1.3;
            background: #fff;
        }

        .letterhead-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 92px;
            width: 100%;
            z-index: 10;
        }

        .letterhead-header img {
            width: 100%;
            display: block;
        }

        .letterhead-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            height: 74px;
            width: 100%;
            z-index: 10;
        }

        .letterhead-footer img.footer-bg {
            width: 100%;
            display: block;
        }

        .footer-icon-img {
            width: 11px !important;
            height: 11px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            margin-right: 4px !important;
            margin-top: -2px !important;
        }

        .footer-contact {
            color: #1B84A6;
            font-size: 9.5px;
            line-height: 1.35;
            padding: 4px 42px 0 42px;
        }

        .footer-contact table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .footer-contact td {
            border: none;
            padding: 0;
            vertical-align: top;
            color: #1B84A6;
        }

        .footer-left {
            width: 34%;
            text-align: left;
        }

        .footer-center {
            width: 32%;
            text-align: center;
            font-size: 10.5px;
            padding-top: 8px !important;
            letter-spacing: .5px;
        }

        .footer-right {
            width: 34%;
            text-align: left;
        }

        .page {
            padding: 96px 40px 70px 40px;
        }

        .company-title {
            text-align: center;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .company-title .name {
            font-size: 14.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #104F7A;
            text-transform: uppercase;
        }

        .company-title .slip {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #104F7A;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .section-heading {
            color: #104F7A;
            font-size: 10px;
            font-weight: bold;
            margin: 7px 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        table.grid-table th,
        table.grid-table td {
            border: 1px solid #b8cde3;
            padding: 2.8px 5px;
            font-size: 9px;
            vertical-align: middle;
            text-align: left;
        }

        table.grid-table th {
            background-color: #eef3f8;
            font-weight: bold;
            color: #111827;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }

        .details-table td {
            border: 1px solid #b8cde3;
            padding: 2.8px 5px;
            font-size: 9px;
            vertical-align: middle;
        }

        .details-table td.lbl {
            background-color: #eef3f8;
            font-weight: bold;
            color: #111827;
            width: 22%;
        }

        .details-table td.val {
            background-color: #ffffff;
            color: #1f2937;
            width: 28%;
        }

        .amount-in-words {
            font-size: 9px;
            color: #334155;
            margin-top: 3px;
            font-style: italic;
        }

        .sign-block {
            margin-top: 8px;
            line-height: 1.3;
            font-size: 9.5px;
        }

        .signature-img {
            height: 36px;
            max-width: 130px;
            margin: 2px 0;
            display: block;
        }

        .page-break {
            page-break-before: always;
        }
    </style>

    @if(isset($isPreview) && $isPreview)
    <style>
        body {
            background: #f0f2f5;
            padding: 30px 15px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .page-container {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12), 0 8px 10px rgba(0, 0, 0, 0.08);
            margin: 0 auto;
            position: relative;
            border-radius: 6px;
            overflow: hidden;
        }

        .page {
            min-height: 297mm;
            background: transparent;
        }

        .letterhead-header {
            position: absolute !important;
        }

        .letterhead-footer {
            position: absolute !important;
            bottom: 4px !important;
        }
    </style>
    @endif
</head>

<body>
    @php
    $snapshot = (array) ($payroll->calculation_snapshot['policy_snapshot'] ?? []);
    $attendanceSnapshot = (array) ($payroll->calculation_snapshot['attendance'] ?? []);
    $calendarDays = (int) ($attendanceSnapshot['calendar_days'] ?? \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->daysInMonth);
    $monthYear = $monthName . ' ' . $payroll->year;

    $companySettings = \Illuminate\Support\Facades\DB::table('company_settings')->first();
    $companyName = $companySettings->company_name ?? branding_name();
    $companyEmail = $companySettings->email ?? 'info@orbosis.com';
    $companyPhone = $companySettings->phone ?? '+91-8770702092';
    $companyAddress = $companySettings->address ?? 'Indore, Madhya Pradesh, India';
    $companyWebsite = preg_replace('#^https?://#i', '', $companySettings->website ?? 'www.orbosis.com');

    // Header & Footer
    $headerPath = public_path('assets/hrms/document-letterhead/header.png');
    $headerSrc = is_file($headerPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath))
        : (isset($isPreview) && $isPreview ? asset('assets/hrms/document-letterhead/header.png') : $headerPath);

    $footerPath = public_path('assets/hrms/document-letterhead/footer.png');
    $footerSrc = is_file($footerPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath))
        : (isset($isPreview) && $isPreview ? asset('assets/hrms/document-letterhead/footer.png') : $footerPath);

    // Resolve Active HR Signatory via optimized Model helper
    $activeHr = $activeHr ?? \App\Models\HRMS\Employee\EmployeeM::currentActiveHrManager();
    $hrName = $activeHr?->display_name ?? $activeHr?->user?->name ?? 'Authorized Signatory';
    $hrDesignation = $activeHr?->designation?->name ?? ($activeHr ? 'Human Resource Manager' : 'Authorized Signatory');

    // Signature (Dynamic signature with fallback)
    $signatureSrc = null;
    $hrSlug = \Illuminate\Support\Str::slug($hrName);
    $specificSig = public_path("assets/hrms/document-letterhead/{$hrSlug}_signature.png");
    $vanshikaSig = public_path('assets/hrms/document-letterhead/vanshika_signature.png');

    if (is_file($specificSig)) {
        $signatureSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($specificSig));
    } elseif ($activeHr && is_file($vanshikaSig) && str_contains(strtolower($hrName), 'vanshika')) {
        $signatureSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($vanshikaSig));
    } elseif (!empty($companySettings->seal)) {
        $sealPath = storage_path('app/public/' . $companySettings->seal);
        if (is_file($sealPath)) {
            $mime = mime_content_type($sealPath) ?: 'image/jpeg';
            $signatureSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($sealPath));
        }
    }

    // Masked bank account
    $bankAcc = optional($employee->profile)->bank_account_no ?? ($payroll->calculation_snapshot['bank_account_no'] ?? null);
    $maskedBankAccount = !empty($bankAcc)
        ? (strlen($bankAcc) > 4 ? str_repeat('X', max(0, strlen($bankAcc) - 4)) . substr($bankAcc, -4) : $bankAcc)
        : '-';

    // Amount Formatter
    $fmt = function($amt) {
        if ((float) $amt <= 0) return '-';
        return ((float) $amt == round((float) $amt))
            ? '₹' . number_format((float) $amt, 0)
            : '₹' . number_format((float) $amt, 2);
    };

    // Days Formatter
    $fmtDays = function($val) {
        if ($val === null || $val === '' || (float) $val <= 0) return '-';
        $floatVal = (float) $val;
        if ($floatVal == round($floatVal)) {
            return number_format($floatVal, 0);
        }
        $formatted = number_format($floatVal, 2);
        if (str_ends_with($formatted, '0')) {
            return rtrim($formatted, '0');
        }
        return $formatted;
    };

    // Component rules from snapshot, policy, or global rules
    $policyId = $payroll->policy_id ?? ($snapshot['policy_id'] ?? null);
    $rulesService = app(\App\Services\HRMS\EnterprisePayroll\EnterpriseSalaryComponentRulesS::class);
    $rulesBreakdown = $rulesService->breakdown((float) $payroll->gross_salary, $policyId);
    $pcts = $payroll->calculation_snapshot['salary_component_rules'] 
        ?? ($rulesBreakdown['percentages'] ?? ['basic' => 50, 'hra' => 20, 'special_allowance' => 30]);

    $basicPct = (float) ($pcts['basic'] ?? 50);
    $hraPct = (float) ($pcts['hra'] ?? 20);
    $specialPct = (float) ($pcts['special_allowance'] ?? 30);

    $fmtPct = function($p) {
        return ((float)$p == round((float)$p)) ? number_format((float)$p, 0) . '%' : number_format((float)$p, 1) . '%';
    };

    // Amounts calculated according to policy percentage
    $basicAmount = (float) $payroll->basic_salary > 0 ? (float) $payroll->basic_salary : $rulesBreakdown['basic'];
    $hraAmount = (float) $payroll->hra > 0 ? (float) $payroll->hra : $rulesBreakdown['hra'];
    $specialAmount = (float) $payroll->special_allowance > 0 ? (float) $payroll->special_allowance : $rulesBreakdown['special_allowance'];

    // Salary Breakdown lists
    $breakdownEarnings = [];
    $breakdownEarnings[] = ['name' => 'Basic Salary', 'amount' => $basicAmount];
    $breakdownEarnings[] = ['name' => 'HRA', 'amount' => $hraAmount];
    $breakdownEarnings[] = ['name' => 'Special Allowance', 'amount' => $specialAmount];

    if ((float) $payroll->bonus_amount > 0) {
        $breakdownEarnings[] = ['name' => 'Bonus', 'amount' => (float) $payroll->bonus_amount];
    }
    if ((float) $payroll->incentive_amount > 0) {
        $breakdownEarnings[] = ['name' => 'Incentive', 'amount' => (float) $payroll->incentive_amount];
    }
    if ((float) $payroll->reimbursement_amount > 0) {
        $breakdownEarnings[] = ['name' => 'Reimbursement', 'amount' => (float) $payroll->reimbursement_amount];
    }

    $breakdownDeductions = [];
    $breakdownDeductions[] = ['name' => 'Professional Tax', 'amount' => (float) $payroll->professional_tax];

    $attDedAmount = (float) $payroll->attendance_deduction;
    $snap = $payroll->calculation_snapshot ?? [];
    $missedPunchDedAmount = (float) ($payroll->missed_punch_deduction ?? $snap['missed_punch_deduction'] ?? 0);
    $earlyOutDedAmount = (float) ($payroll->early_out_deduction ?? $snap['early_out_deduction'] ?? 0);
    $lwpDedAmount = (float) ($payroll->lwp_deduction ?? $snap['lwp_deduction'] ?? 0);
    $halfDayDedAmount = (float) ($payroll->half_day_deduction ?? $snap['half_day_deduction'] ?? 0);
    $absentDedAmount = (float) ($payroll->absent_deduction ?? $snap['absent_deduction'] ?? 0);

    // Standard attendance deductions in Salary Breakdown table (all unpaid days grouped under LWP)
    $totalLwpDed = round($lwpDedAmount + $absentDedAmount + $halfDayDedAmount, 2);
    $totalLwpDays = (float)($payroll->lwp_days ?? 0) + ((float)($payroll->half_days ?? 0) * 0.5);
    if ($totalLwpDed > 0) {
        $breakdownDeductions[] = ['name' => 'LWP', 'amount' => $totalLwpDed];
    } elseif ($totalLwpDays > 0 || (float)($payroll->absent_days ?? 0) > 0) {
        $breakdownDeductions[] = ['name' => 'LWP', 'amount' => $totalLwpDed];
    } else {
        $breakdownDeductions[] = ['name' => 'LWP', 'amount' => 0.0];
    }

    $breakdownDeductions[] = ['name' => 'Other Deductions', 'amount' => (float) $payroll->other_deduction];

    $pfAmount = (float) ($payroll->pf ?? ($payroll->items->where('item_code', 'pf')->first()?->amount ?? 0));
    if ($pfAmount > 0) {
        $breakdownDeductions[] = ['name' => 'PF', 'amount' => $pfAmount];
    }
    $esiAmount = (float) ($payroll->esi ?? ($payroll->items->where('item_code', 'esi')->first()?->amount ?? 0));
    if ($esiAmount > 0) {
        $breakdownDeductions[] = ['name' => 'ESI', 'amount' => $esiAmount];
    }
    if ((float) $payroll->tds > 0) {
        $breakdownDeductions[] = ['name' => 'TDS', 'amount' => (float) $payroll->tds];
    }

    $breakdownDeductionsTotal = array_sum(array_column($breakdownDeductions, 'amount'));
    $adjustmentDeductionsTotal = $missedPunchDedAmount + $earlyOutDedAmount;
    $grandTotalDeductions = round($breakdownDeductionsTotal + $adjustmentDeductionsTotal, 2);
    $netSalaryPayable = round($payroll->gross_salary - $grandTotalDeductions, 2);

    $payrollDays = (int) ($payroll->salary_day_base 
        ?? $payroll->calculation_snapshot['salary_day_base'] 
        ?? ($policySnapshot['salary_day_base'] ?? 26));

    // Present days in attendance summary follows the exact 26-day calculation formula:
    // Salary Days = (Payable Days * 26) / Calendar Days
    $salaryDays = (float) ($payroll->salary_days ?? $payroll->calculation_snapshot['salary_days'] ?? 0);
    if ($salaryDays <= 0 && (float) $payroll->payable_days > 0 && $calendarDays > 0) {
        $salaryDays = round(((float) $payroll->payable_days * $payrollDays) / $calendarDays, 5);
    }
    $displayedPresentDays = round($salaryDays, 2);

    $plDays = (float) ($payroll->paid_leave_days ?? $attendanceSnapshot['paid_leave_days'] ?? 0);
    $slDays = (float) ($payroll->sick_leave_days ?? $attendanceSnapshot['sick_leave_days'] ?? 0);
    $coDays = (float) ($payroll->comp_off_days ?? $attendanceSnapshot['comp_off_days'] ?? 0);

    $leaveParts = [];
    if ($plDays > 0) {
        $leaveParts[] = $fmtDays($plDays) . ' PL';
    }
    if ($slDays > 0) {
        $leaveParts[] = $fmtDays($slDays) . ' SL';
    }
    if ($coDays > 0) {
        $leaveParts[] = $fmtDays($coDays) . ' CO';
    }
    $displayedPaidLeave = !empty($leaveParts) ? implode(' | ', $leaveParts) : '-';

    $breakdownRowCount = max(count($breakdownEarnings), count($breakdownDeductions));
    @endphp

    <div class="page-container">
        <div class="letterhead-header">
            <img src="{{ $headerSrc }}" alt="Header">
        </div>

        <div class="letterhead-footer">
            <img class="footer-bg" src="{{ $footerSrc }}" alt="Footer">

            <div class="footer-contact">
                <table>
                    <tr>
                        <td class="footer-left">
                            <div>
                                <img class="footer-icon-img" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzFCODRBNiI+PHBhdGggZD0iTTYuNjIgMTAuNzljMS40NCAyLjgzIDMuNzYgNS4xNCA2LjU5IDYuNTlsMi4yLTIuMmMuMjctLjI3LjY3LS4zNiAxLjAyLS4yNCAxLjEyLjM3IDIuMzMuNTcgMy41Ny41Ny41NSAwIDEgLjQ1IDEgMVYyMGMwIC41NS0uNDUgMS0xIDEtOS4zOSAwLTE3LTcuNjEtMTctMTcgMC0uNTUuNDUtMSAxLTFoMy41Yy41NSAwIDEgLjQ1IDEgMSAwIDEuMjUuMiAyLjQ1LjU3IDMuNTcuMTEuMzUuMDMuNzQtLjI1IDEuMDJsLTIuMiAyLjJ6Ii8+PC9zdmc+" width="11" height="11">
                                {{ $companyPhone }}
                            </div>
                            <div>
                                <img class="footer-icon-img" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzFCODRBNiI+PHBhdGggZD0iTTIwIDRINGMtMS4xIDAtMS45OS45LTEuOTkgMkwyIDE4YzAgMS4xLjkgMiAyIDJ2MTZjMS4xIDAgMi0uOSAyLTJWNmMwLTEuMS0uOS0yLTItMnptMCA0bC04IDUtOC01VjZsOCA1IDgtNXYyeiIvPjwvc3ZnPg==" width="11" height="11">
                                {{ $companyEmail }}
                            </div>
                        </td>

                        <td class="footer-center">
                            23 AAECO8032D1ZU
                        </td>

                        <td class="footer-right">
                            <div>
                                <img class="footer-icon-img" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2VhNDMzNSI+PHBhdGggZD0iTTEyIDJDOC4xMyAyIDUgNS4xMyA1IDljMCA1LjI1IDcgMTMgNyAxM3M3LTcuNzUgNy0xM2MwLTMuODctMy4xMy03LTctN3ptMCA5LjVjLTEuMzggMC0yLjUtMS4xMi0yLjUtMi41czEuMTItMi41IDIuNS0yLjUgMi41IDEuMTIgMi41IDIuNS0xLjEyIDIuNS0yLjUgMi41eiIvPjwvc3ZnPg==" width="11" height="11">
                                {{ $companyAddress }}
                            </div>
                            <div>
                                <img class="footer-icon-img" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzQyODVmNCI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTBjNS41MiAwIDEwLTQuNDggMTAtMTBTMTcuNTIgMiAxMiAyem0tMSAxNy45M2MtMy45NS0uNDktNy0zLjg1LTctNy45MyAwLS42Mi4wOC0xLjIxLjIxLTEuNzlMOSAxNXYxYzAgMS4xLjkgMiAyIDJ2MS45M3ptNi45LTIuNTNjLS4yNi0uODEtMS0xLjQtMS45LTEuNGgtMXYtM2MwLS41NS0uNDUtMS0xLTFoLTZ2LTJoMmMuNTUgMCAxLS40NSAxLTFWN2gyYzEuMSAwIDItLjkgMi0ydi0uNDFjMi45MyAxLjE5IDUgNC4wNiA1IDcuNDEgMCAyLjA4LS44IDMuOTctMi4xIDUuMzl6Ii8+PC9zdmc+" width="11" height="11">
                                {{ $companyWebsite }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="page">
            <div class="company-title">
                <div class="name">{{ strtoupper($companyName) }}</div>
                <div class="slip">SALARY SLIP – {{ strtoupper($monthName) }} {{ $payroll->year }}</div>
            </div>

            <table class="details-table">
                <tr>
                    <td class="lbl">Employee Name</td>
                    <td class="val">{{ optional($employee)->display_name ?? '-' }}</td>
                    <td class="lbl">Employee ID</td>
                    <td class="val">{{ $employee->employee_code ?? $employee->id ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Designation</td>
                    <td class="val">{{ optional($employee->designation)->name ?? '-' }}</td>
                    <td class="lbl">Department</td>
                    <td class="val">{{ optional($employee->department)->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Date of Joining</td>
                    <td class="val">{{ !empty($employee->joining_date) ? date('d F Y', strtotime($employee->joining_date)) : '-' }}</td>
                    <td class="lbl">Location</td>
                    <td class="val">{{ optional($employee->profile)->work_location ?? $employee->location ?? 'Indore' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Pay Period</td>
                    <td class="val">{{ $monthName }} {{ $payroll->year }}</td>
                    <td class="lbl">Bank A/C</td>
                    <td class="val">{{ $maskedBankAccount }}</td>
                </tr>
            </table>

            <div class="section-heading">ATTENDANCE SUMMARY:</div>
            <table class="grid-table">
                <thead>
                    <tr>
                        <th style="width: 17%;">Calendar Days</th>
                        <th style="width: 17%;">Payroll Days</th>
                        <th style="width: 16%;">Present</th>
                        <th style="width: 17%;">Paid Leave</th>
                        <th style="width: 16%;">LWP</th>
                        <th style="width: 17%;">Absent</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $calendarDays ?: '-' }}</td>
                        <td>{{ $payrollDays }}</td>
                        <td>{{ $fmtDays($displayedPresentDays) }}</td>
                        <td>{{ $displayedPaidLeave }}</td>
                        <td>{{ $fmtDays($totalLwpDays) }}</td>
                        <td>{{ $fmtDays($payroll->absent_days) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-heading">SALARY BREAKDOWN:</div>
            <table class="grid-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Earnings</th>
                        <th style="width: 15%;">Amount (INR)</th>
                        <th style="width: 35%;">Deductions</th>
                        <th style="width: 15%;">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < $breakdownRowCount; $i++)
                    <tr>
                        <td>{{ $breakdownEarnings[$i]['name'] ?? '' }}</td>
                        <td>
                            @if(isset($breakdownEarnings[$i]))
                                {{ $fmt($breakdownEarnings[$i]['amount']) }}
                            @endif
                        </td>
                        <td>{{ $breakdownDeductions[$i]['name'] ?? '' }}</td>
                        <td>
                            @if(isset($breakdownDeductions[$i]))
                                {{ $fmt($breakdownDeductions[$i]['amount']) }}
                            @endif
                        </td>
                    </tr>
                    @endfor
                    <tr style="font-weight: bold;">
                        <td>Gross Earnings</td>
                        <td>{{ $fmt($payroll->gross_salary) }}</td>
                        <td>Total Deductions</td>
                        <td>{{ $breakdownDeductionsTotal > 0 ? $fmt($breakdownDeductionsTotal) : '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-heading">ATTENDANCE ADJUSTMENTS:</div>
            <table class="grid-table">
                <thead>
                    <tr>
                        <th style="width: 38%;">Violation</th>
                        <th style="width: 24%;">Days</th>
                        <th style="width: 38%;">Deduction (Included in LWP)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $mpCount = (int) ($payroll->missed_punch_count ?? $attendanceSnapshot['missed_punch_count'] ?? 0);
                        $eoCount = (int) ($payroll->early_out_count ?? $attendanceSnapshot['early_out_count'] ?? 0);
                        $halfDaysCount = (float) ($payroll->half_days ?? $attendanceSnapshot['half_days'] ?? 0);
                        $mpIncludedInLwp = (bool) ($snap['missed_punch_included_in_lwp'] ?? ($mpCount >= 3 && ((float)($payroll->lwp_days ?? 0) + (float)($payroll->absent_days ?? 0)) >= intdiv($mpCount, 3)));
                        
                        $mpPenaltyDays = intdiv($mpCount, 3);
                        $totUnpaidDays = (float)($payroll->lwp_days ?? 0) + (float)($payroll->absent_days ?? 0) + ($halfDaysCount * 0.5);
                        $mpDeductionVal = ($mpPenaltyDays > 0 && $totUnpaidDays > 0)
                            ? round((float)$payroll->attendance_deduction * ($mpPenaltyDays / $totUnpaidDays), 2)
                            : (float)$missedPunchDedAmount;

                        $monthStart = sprintf('%04d-%02d-01', $payroll->year, $payroll->month);
                        $monthEnd = \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->endOfMonth()->toDateString();
                        $dbEoViolations = \Illuminate\Support\Facades\DB::table('attendance_violations')
                            ->where('employee_id', $payroll->employee_id)
                            ->whereBetween('violation_date', [$monthStart, $monthEnd])
                            ->whereIn('type', ['early_logout', 'early_out'])
                            ->get();

                        $displayEoCount = max($eoCount, $dbEoViolations->count());
                        $convertedHalfDay = $dbEoViolations->where('converted_to_half_day', 1)->isNotEmpty();

                        $eoDeductionVal = (float)$earlyOutDedAmount;
                        if ($eoDeductionVal <= 0 && $halfDayDedAmount > 0 && ($convertedHalfDay || $halfDaysCount > 0)) {
                            $eoDeductionVal = $halfDayDedAmount;
                        }
                    @endphp
                    <tr>
                        <td>Missed Punch</td>
                        <td>{{ $mpCount > 0 ? $mpCount : '-' }}</td>
                        <td>
                            @if($mpDeductionVal > 0)
                                {{ $fmt($mpDeductionVal) }}
                            @elseif($missedPunchDedAmount > 0)
                                {{ $fmt($missedPunchDedAmount) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Early Log-out</td>
                        <td>{{ $displayEoCount > 0 ? $displayEoCount : '-' }}</td>
                        <td>
                            @if($eoDeductionVal > 0)
                                {{ $fmt($eoDeductionVal) }}
                            @elseif($earlyOutDedAmount > 0)
                                {{ $fmt($earlyOutDedAmount) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="grid-table" style="margin-top: 8px;">
                <tr>
                    <td style="width: 50%; font-weight: bold;">TOTAL SALARY DEDUCTIONS</td>
                    <td style="width: 50%; font-weight: bold;">
                        {{ $grandTotalDeductions > 0 ? $fmt($grandTotalDeductions) : '-' }}
                    </td>
                </tr>
                <tr style="background-color: #eef3f8;">
                    <td style="font-weight: bold;">NET SALARY PAYABLE</td>
                    <td style="font-weight: bold;">{{ $fmt($netSalaryPayable) }}</td>
                </tr>
            </table>

            <div class="amount-in-words">
                Amount in Words: <em>{{ $payroll->net_salary_words }}</em>
            </div>

            <div class="sign-block">
                <div style="font-weight: bold; margin-bottom: 2px;">For {{ $companyName }}</div>
                <div style="min-height: 38px; margin: 3px 0;">
                    @if($signatureSrc)
                        <img class="signature-img" src="{{ $signatureSrc }}" alt="Signature">
                    @else
                        <div style="height: 35px;"></div>
                    @endif
                </div>
                <div style="font-weight: bold; color: #111827;">{{ $hrName }}</div>
                <div style="color: #475569; font-size: 9.5px;">Human Resource Manager</div>
            </div>
        </div>
    </div>
</body>

</html>
