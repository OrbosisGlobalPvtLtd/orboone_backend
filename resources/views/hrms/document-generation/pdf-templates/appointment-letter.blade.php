@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Appointment Letter')

@section('styles')
<style>
    .letter-body {
        font-size: 11px;
        line-height: 1.36;
        color: #111827;
    }
    .letter-body p {
        margin: 0 0 5px 0;
    }
    .letter-body h2 {
        margin: 0 0 8px 0;
        font-size: 16px;
    }
    .letter-body h4 {
        margin: 0 0 4px 0;
        font-size: 12px;
    }
    .salary-table {
        width: 70%;
        border-collapse: collapse;
        margin: 4px 0 6px 0;
    }
    .salary-table th,
    .salary-table td {
        border: 1px solid #222;
        padding: 3px 6px;
    }
    .closing-sign-section {
        margin-top: 14px;
        page-break-inside: avoid;
    }
    .ack-section {
        margin-top: 14px;
        page-break-inside: avoid;
    }
</style>
@endsection

@section('content')
<div class="letter-body">

    <div class="text-center" style="margin-bottom: 8px;">
        <h2 style="letter-spacing:1px; color:#1e3a8a; margin-bottom:8px;">
            APPOINTMENT LETTER
        </h2>
    </div>

    <p><strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d/m/Y') }}</p>

    <p>
        <strong>Name:</strong> {{ $employee_name ?? 'Employee Name' }}<br>
        <strong>Address:</strong> {{ $employee_address ?? 'Indore' }}
    </p>

    <p>
        <strong>Subject:</strong> Appointment for the position of
        <strong>{{ $designation ?? 'Full Stack Developer' }}</strong>.
    </p>

    <p>Dear <strong>{{ $employee_first_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        We are pleased to inform you that, based on your performance in the selection process
        and mutual discussions, you are hereby appointed as
        <strong>{{ $designation ?? 'Full Stack Developer' }}</strong> with
        <strong>{{ $company_name ?? branding_name() }}</strong>, effective from
        <strong>{{ $joining_date ?? 'To Be Confirmed' }}</strong>.
    </p>

    <p>This letter outlines the terms and conditions of your employment with us.</p>

    <p class="text-justify">
        <strong>1. Position & Reporting</strong><br>
        You will be appointed to the position of
        <strong>{{ $designation ?? 'Full Stack Developer' }}</strong> and will report directly to
        <strong>{{ !empty($reporting_manager_name) ? $reporting_manager_name : 'Prabhat Agarwal (CEO)' }}</strong> &
        <strong>{{ !empty($project_manager_name) ? $project_manager_name : 'Sourabh Parihar (Project Manager)' }}</strong>
        or any other person designated by the Company from time to time.
    </p>

    <p class="text-justify">
        You may be assigned duties, projects, or responsibilities consistent with your position,
        and may be transferred or deputed to any department, client site, or branch location
        within India as per business requirements.
    </p>

    @php
        $monthlyGross = (float)($monthly_salary ?? $monthly_gross_salary ?? $salary ?? $salary_monthly ?? 0);
        $basicAmount = isset($basic_salary) && is_numeric($basic_salary) ? (float)$basic_salary : ($monthlyGross * 0.50);
        $hraAmount = isset($hra) && is_numeric($hra) ? (float)$hra : ($monthlyGross * 0.20);
        $allowanceAmount = isset($allowances) && is_numeric($allowances) 
            ? (float)$allowances 
            : max(0, $monthlyGross - $basicAmount - $hraAmount);
    @endphp

    <p class="text-justify">
        <strong>2. Compensation & Benefits</strong><br>
        Your total monthly remuneration will be
        <strong>₹{{ number_format($monthlyGross, 0) }}</strong>
        (Rupees {{ $salary_in_words ?? 'As Agreed' }}).
    </p>

    <table class="salary-table">
        <tr>
            <th>Component</th>
            <th>Amount (₹)</th>
        </tr>
        <tr>
            <td>Basic Salary</td>
            <td>{{ number_format($basicAmount, 0) }}</td>
        </tr>
        <tr>
            <td>HRA</td>
            <td>{{ number_format($hraAmount, 0) }}</td>
        </tr>
        <tr>
            <td>Allowances</td>
            <td>{{ number_format($allowanceAmount, 0) }}</td>
        </tr>
        <tr>
            <td><strong>Total Gross Salary</strong></td>
            <td><strong>{{ number_format($monthlyGross, 0) }}</strong></td>
        </tr>
    </table>

    <p class="text-justify">
        <strong>Deductions:</strong><br>
        Only Professional Tax will be deducted at present. As we are a startup, no other
        statutory deductions such as PF, ESIC, or Gratuity are applicable currently.
        These may be introduced later as per government regulations.
    </p>

    <p>
        Salary will be paid on <strong>7th – 10th</strong> of every month, directly to your registered bank.
    </p>

    <p class="text-justify">
        <strong>3. Work Location & Hours</strong><br>
        <strong>Primary Location:</strong>
        {{ $work_location ?? $office_location ?? 'Agrawal Plaza, B6, MIG Colony, LIG Square, Indore' }}<br>
        <strong>Standard Working Days:</strong> Monday to Friday<br>
        <strong>Saturday Structure:</strong> 2nd & 4th Saturday Off, 1st & 3rd Saturday Working<br>
        <strong>Sunday:</strong> Weekly Off<br>
        <strong>Working Hours:</strong> {{ $working_hours ?? '10:00 AM – 7:00 PM IST' }}
    </p>

    <p class="text-justify">
        Employees are expected to maintain punctuality and professionalism. The Company supports
        flexible work arrangements when approved by your reporting manager.
    </p>

    <p class="text-justify">
        <strong>4. Sandwich Leave Rule (Weekend Inclusion)</strong><br>
        If an employee applies for leave on Friday and Saturday, or Friday and Monday,
        then Saturday and Sunday weekly offs will also be counted as leave.
        Example: Leave on Friday and Monday means Saturday and Sunday will be included,
        and total leave deduction will be 4 days.
    </p>

    <p class="text-justify">
        <strong>5. Communication & Work Expectations</strong><br>
        Applicable to all employees, including WFO and WFH. Employees must stay active on
        official platforms such as Slack, Teams, WhatsApp, attend meetings, attend daily
        stand-ups, share regular updates, and report blockers proactively.
    </p>

    <p class="text-justify">
        <strong>6. Probation Period</strong><br>
        You will be on a probation period of <strong>{{ $probation_period ?? '3 Months' }}</strong>
        from your date of joining. Upon successful completion and review, your employment
        will be confirmed in writing. During probation, performance will be evaluated,
        leaves will be limited as per policy, and extra leaves will be treated as LWP.
        Your notice period during probation will be
        <strong>{{ $notice_period_probation ?? '15 Days' }}</strong>. After confirmation,
        your notice period will be <strong>{{ $notice_period_confirmed ?? '2 Months' }}</strong>.
    </p>

    <p class="text-justify">
        <strong>7. Leave Policy</strong><br>
        After confirmation, you will be entitled to the annual leave structure:
        <strong>25 Leaves</strong> — 18 Paid Leaves and 7 Sick Leaves from January to December.
        Leaves are earned after confirmation, not from joining date. Maximum 2 leaves per month
        are allowed, and extra leaves will be treated as Leave Without Pay. Leave intimation
        should be given 2–5 days in advance.
    </p>

    <p class="text-justify">
        Complete leave policy and monthly allocation details will be shared in the Employee
        Orientation and Company Policy document.
    </p>

    <p class="text-justify">
        <strong>8. Confidentiality & Non-Disclosure</strong><br>
        During your employment and after its termination, you must not disclose any confidential
        or proprietary information related to the Company, its clients, vendors, or partners.
        You must not use company data, code, strategies, or documents for personal or external
        purposes, and must not share credentials, source code, or project details without prior
        written approval. You are required to sign a Non-Disclosure Agreement separately as part
        of the joining formalities.
    </p>

    <p class="text-justify">
        <strong>9. Intellectual Property (IP) Rights</strong><br>
        All intellectual property, source code, designs, documentation, and inventions created
        during your employment shall be the exclusive property of {{ $company_name ?? branding_name() }}.
        You agree not to claim ownership or reuse these materials outside the organization.
    </p>

    <p class="text-justify">
        <strong>10. Client & Project Cost Responsibility</strong><br>
        For all client-facing projects, any App Store or Play Store registration/publication fees
        will be borne by the client. The company will not be liable for costs related to domain
        renewals, server licenses, or third-party subscriptions unless explicitly agreed in writing.
        You are expected to maintain project hygiene, including documentation, version control,
        and time tracking as per company processes.
    </p>

    <p class="text-justify">
        <strong>11. Conduct & Professional Ethics</strong><br>
        Employees are expected to maintain professional decorum, follow data security, IT usage,
        and email communication guidelines, and avoid any action that may harm the reputation
        of the company. Any misconduct, violation, or breach of trust may result in disciplinary
        action, including termination.
    </p>

    <p class="text-justify">
        <strong>12. Termination of Employment</strong><br>
        Either party may terminate this employment by giving {{ $notice_period_confirmed ?? '2 Months' }} written notice or salary
        in lieu of notice after confirmation. During probation, {{ $notice_period_probation ?? '15 Days' }} notice or salary in lieu
        applies. The company reserves the right to terminate employment without notice for
        misconduct, violation of company policy, breach of confidentiality, or underperformance
        after due warning.
    </p>

    <p class="text-justify">
        Upon separation, you must return all company property including laptop, documents,
        ID cards, files, and devices, and complete clearance formalities before release of
        your final settlement.
    </p>

    <p class="text-justify">
        <strong>13. Performance & Evaluation</strong><br>
        Your performance will be reviewed periodically based on assigned goals, punctuality,
        teamwork, and adherence to company standards. The management reserves the right to
        modify your compensation or role as per performance reviews and business needs.
    </p>

    <p class="text-justify">
        <strong>14. Amendments</strong><br>
        This appointment letter constitutes the entire agreement between you and the Company.
        Any amendments must be made in writing and signed by both parties.
    </p>

    <p class="text-justify">
        <strong>15. Acceptance</strong><br>
        Kindly sign and return a duplicate copy of this letter to confirm your acceptance of the
        above terms and conditions.
    </p>

    <p>
        We are delighted to welcome you to the {{ $company_name ?? branding_name() }} family
        and look forward to a long and mutually rewarding association.
    </p>

    <div class="closing-sign-section">
        Warm Regards,<br>
        <strong>For {{ $company_name ?? branding_name() }}</strong>
        <div style="min-height: 50px; margin-top: 4px; margin-bottom: 4px;">
            @if(!empty($signature_image) && !empty($seal_image))
                <div style="display: inline-block; vertical-align: middle;">
                    <img src="{{ $signature_image }}" style="height: 48px; width: auto; max-width: 140px; vertical-align: middle;" alt="Signature">
                    <img src="{{ $seal_image }}" style="height: 52px; width: auto; max-width: 100px; vertical-align: middle; margin-left: 15px;" alt="Seal">
                </div>
            @elseif(!empty($signature_image))
                <img src="{{ $signature_image }}" style="height: 48px; width: auto; max-width: 150px; vertical-align: middle;" alt="Signature">
            @elseif(!empty($seal_image))
                <img src="{{ $seal_image }}" style="height: 52px; width: auto; max-width: 100px; vertical-align: middle; display: block;" alt="Seal">
            @else
                <div style="height: 35px;"></div>
            @endif
        </div>
        <strong>{{ $hr_manager_name ?? $authorized_signatory ?? 'HR Admin' }}</strong><br>
        {{ !empty($signatory_designation) ? $signatory_designation : 'Human Resource Manager' }}

    </div>

    <div class="ack-section">
        <h4 style="color:#1e3a8a; margin-bottom:6px;">Employee Acknowledgment</h4>

        <p class="text-justify">
            I, <strong>{{ $employee_name ?? 'Employee Name' }}</strong>, accept the terms and
            conditions stated above and agree to abide by them during my employment with
            <strong>{{ $company_name ?? branding_name() }}</strong>.
        </p>

        <p style="margin-top:25px;">
            Signature: ___________________________<br><br>
            Date: ________________________________<br><br>
            Place: _______________________________
        </p>
    </div>

</div>
@endsection
