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
                color: #111;
                font-size: 10.5px;
                line-height: 1.28;
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
                font-size: 10px;
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
                font-size: 11px;
                padding-top: 8px !important;
                letter-spacing: .5px;
            }

            .footer-right {
                width: 34%;
                text-align: left;
            }

            .footer-icon {
                display: inline-block;
                width: 16px;
                color: #1B84A6;
                font-weight: bold;
            }

            .page {
                padding: 115px 50px 95px 50px;
            }

            .company-title {
                text-align: center;
                color: #3f6591;
                font-family: DejaVu Serif, serif;
                margin-bottom: 4px;
                line-height: 1.25;
            }

            .company-title .name {
                font-size: 15px;
                letter-spacing: 0.5px;
            }

            .company-title .slip {
                font-size: 13px;
                font-weight: bold;
                letter-spacing: 0.5px;
            }

            .section-title {
                color: #365b84;
                font-family: DejaVu Serif, serif;
                font-size: 13px;
                font-weight: bold;
                margin: 6px 0 2px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 6px;
            }

            th,
            td {
                border: 1px solid #222;
                padding: 3.2px 6px;
                vertical-align: middle;
                font-size: 10.5px;
            }

            th {
                text-align: left;
                font-weight: normal;
            }

            .amount {
                white-space: nowrap;
                text-align: right;
            }

            /* Table Column Widths & Alignments */
            .details-table td:first-child {
                width: 30%;
            }

            .details-table td:last-child {
                width: 70%;
            }

            .attendance-table td:first-child {
                width: 75%;
            }

            .attendance-table td:last-child {
                width: 25%;
                text-align: right;
            }

            .earnings-table th:nth-child(1),
            .earnings-table td:nth-child(1) {
                width: 35%;
            }

            .earnings-table th:nth-child(2),
            .earnings-table td:nth-child(2) {
                width: 15%;
                text-align: right;
            }

            .earnings-table th:nth-child(3),
            .earnings-table td:nth-child(3) {
                width: 35%;
            }

            .earnings-table th:nth-child(4),
            .earnings-table td:nth-child(4) {
                width: 15%;
                text-align: right;
            }

            .summary-signature-wrapper {
                page-break-inside: avoid;
            }

            .salary-summary {
                margin-top: 5px;
                line-height: 1.45;
                font-size: 10.5px;
            }

            .sign-block {
                margin-top: 10px;
                line-height: 1.35;
                font-size: 10.5px;
            }

            .signature-img {
                height: 28px;
                margin: 3px 0 0 6px;
            }

            .page-break {
                page-break-before: always;
            }

            .disclaimer {
                border: 1px solid #222;
                padding: 10px;
                line-height: 1.5;
                font-size: 11px;
                margin-top: 8px;
            }
        </style>

        @if(isset($isPreview) && $isPreview)
        <style>
            body {
                background: #f0f2f5;
                padding: 40px 15px;
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
                border-radius: 8px;
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
                bottom: 6px !important;
            }
        </style>
        @endif
    </head>

    <body>
        @php
        $snapshot = (array) ($payroll->calculation_snapshot['policy_snapshot'] ?? []);
        $monthYear = $monthName . ' -' . $payroll->year;

        $deductionDays = (float) $payroll->lwp_days
        + (float) $payroll->absent_days
        + ((float) $payroll->half_days * 0.5);

        $leaveText = trim(
        (float) $payroll->paid_leave_days > 0
        ? $payroll->paid_leave_days . ' (PL)'
        : '0'
        );

        $companySettings = \Illuminate\Support\Facades\DB::table('company_settings')->first();
        $companyName = $companySettings->company_name ?? branding_name();
        $companyEmail = $companySettings->email ?? 'info@orbosis.com';
        $companyPhone = $companySettings->phone ?? '+91-8770702092';
        $companyAddress = $companySettings->address ?? 'Indore, Madhya Pradesh, India';
        $companyWebsite = preg_replace('#^https?://#i', '', $companySettings->website ?? 'www.orbosis.com');

        $headerSrc = (isset($isPreview) && $isPreview)
        ? asset('assets/hrms/document-letterhead/header.png')
        : public_path('assets/hrms/document-letterhead/header.png');

        $footerSrc = (isset($isPreview) && $isPreview)
        ? asset('assets/hrms/document-letterhead/footer.png')
        : public_path('assets/hrms/document-letterhead/footer.png');

        $signatureSrc = null;
        if (!empty($companySettings->seal)) {
        $sealPath = storage_path('app/public/' . $companySettings->seal);
        if (is_file($sealPath)) {
        $signatureSrc = (isset($isPreview) && $isPreview)
        ? asset('storage/' . $companySettings->seal)
        : $sealPath;
        }
        }

        if (!$signatureSrc) {
        $staticSig = public_path('assets/hrms/document-letterhead/signature.png');
        if (is_file($staticSig)) {
        $signatureSrc = (isset($isPreview) && $isPreview)
        ? asset('assets/hrms/document-letterhead/signature.png')
        : $staticSig;
        }
        }
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
                    <div class="name">{{ $companyName }}</div>
                    <div class="slip">Salary Slip - {{ strtoupper($monthName) }} - {{ $payroll->year }}</div>
                </div>

                <div class="section-title">Employee Detail</div>
                <table class="details-table">
                    <tr>
                        <td style="width: 30%;">Employee Name</td>
                        <td style="width: 70%;">{{ optional($employee)->display_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Employee ID</td>
                        <td>{{ $employee->employee_code ?? $employee->id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Designation</td>
                        <td>{{ optional($employee->designation)->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td>{{ optional($employee->department)->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Month & Year</td>
                        <td>{{ $monthName }} - {{ $payroll->year }}</td>
                    </tr>
                </table>

                <div class="section-title">Attendance Summary</div>
                <table class="attendance-table">
                    <tr>
                        <td style="width: 75%;">Total Working Days</td>
                        <td style="text-align: right; width: 25%;">{{ $payroll->total_working_days }}</td>
                    </tr>
                    <tr>
                        <td>Present Days</td>
                        <td style="text-align: right;">{{ $payroll->present_days }}</td>
                    </tr>
                    <tr>
                        <td>Half Days</td>
                        <td style="text-align: right;">{{ $payroll->half_days }}</td>
                    </tr>
                    <tr>
                        <td>Paid Leaves</td>
                        <td style="text-align: right;">{{ $leaveText }}</td>
                    </tr>
                    <tr>
                        <td>Absent Days</td>
                        <td style="text-align: right;">{{ $payroll->absent_days }}</td>
                    </tr>
                    <tr>
                        <td>Leave Without Pay (LWP)</td>
                        <td style="text-align: right;">{{ $payroll->lwp_days }}</td>
                    </tr>
                    <tr>
                        <td>Per Day Salary</td>
                        <td style="text-align: right;">₹{{ number_format((float) $payroll->per_day_salary, 2) }}</td>
                    </tr>
                </table>

                <div class="section-title">Earnings & Deductions</div>
                <table class="earnings-table">
                    <tr>
                        <th style="width: 35%;">Earnings</th>
                        <th style="width: 15%; text-align: right;">Amount (INR)</th>
                        <th style="width: 35%;">Deductions</th>
                        <th style="width: 15%; text-align: right;">Amount (INR)</th>
                    </tr>
                    <tr>
                        <td>Basic Salary</td>
                        <td class="amount" style="text-align: right;">₹{{ number_format((float) $payroll->basic_salary, 0) }}</td>
                        <td>Professional Tax</td>
                        <td class="amount" style="text-align: right;">₹{{ number_format((float) $payroll->professional_tax, 0) }}</td>
                    </tr>
                    <tr>
                        <td>HRA</td>
                        <td class="amount" style="text-align: right;">₹{{ number_format((float) $payroll->hra, 0) }}</td>
                        <td>Half-Day</td>
                        <td class="amount" style="text-align: right;">₹{{ number_format((float) $payroll->half_day_deduction, 0) }}</td>
                    </tr>
                    <tr>
                        <td>Special Allowance</td>
                        <td class="amount" style="text-align: right;">₹{{ number_format((float) $payroll->special_allowance, 0) }}</td>
                        <td>Total Deductions</td>
                        <td class="amount" style="text-align: right;">₹{{ number_format((float) $payroll->total_deductions, 0) }}</td>
                    </tr>
                </table>

                <div class="summary-signature-wrapper">
                    <div class="salary-summary">
                        Gross Salary - ₹{{ number_format((float) $payroll->gross_salary, 0) }}<br>
                        Net Salary - ₹{{ number_format((float) $payroll->net_salary, 2) }}<br>
                        Net Salary (in words) - {{ $payroll->net_salary_words }}
                    </div>

                    <div class="sign-block">
                        For {{ $companyName }}<br>
                        Warm Regards,<br>

                        @if($signatureSrc)
                        <img class="signature-img" src="{{ $signatureSrc }}" alt="Signature"><br>
                        @endif

                        Orbosis HR<br>
                        Human Resource Executive
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>