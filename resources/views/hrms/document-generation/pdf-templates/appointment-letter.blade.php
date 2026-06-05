@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Appointment Letter')

@section('content')
<div class="letter-body">

    <div class="text-center mb-4">
        <h2 style="letter-spacing:1px; color:#1e3a8a; margin-bottom:18px;">
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

    <p>Dear <strong>{{ $employee_name ?? 'Employee' }}</strong>,</p>

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
        <strong>{{ $reporting_manager_name ?? 'Prabhat Agarwal (CEO)' }}</strong> &
        <strong>{{ $project_manager_name ?? 'Sourabh Parihar (Project Manager)' }}</strong>
        or any other person designated by the Company from time to time.
    </p>

    <p class="text-justify">
        You may be assigned duties, projects, or responsibilities consistent with your position,
        and may be transferred or deputed to any department, client site, or branch location
        within India as per business requirements.
    </p>

    <p class="text-justify">
        <strong>2. Compensation & Benefits</strong><br>
        Your total monthly remuneration will be
        <strong>₹{{ number_format((float)($monthly_salary ?? $salary ?? 0), 0) }}</strong>
        (Rupees {{ $salary_in_words ?? 'As Agreed' }}).
    </p>

    <table style="width:70%; border-collapse:collapse; margin:8px 0 12px 0;">
        <tr>
            <th style="border:1px solid #222; padding:6px;">Component</th>
            <th style="border:1px solid #222; padding:6px;">Amount (₹)</th>
        </tr>
        <tr>
            <td style="border:1px solid #222; padding:6px;">Basic Salary</td>
            <td style="border:1px solid #222; padding:6px;">{{ number_format((float)($basic_salary ?? 0), 0) }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #222; padding:6px;">HRA</td>
            <td style="border:1px solid #222; padding:6px;">{{ number_format((float)($hra ?? 0), 0) }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #222; padding:6px;">Conveyance</td>
            <td style="border:1px solid #222; padding:6px;">{{ number_format((float)($conveyance ?? 0), 0) }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #222; padding:6px;">Allowances</td>
            <td style="border:1px solid #222; padding:6px;">{{ number_format((float)($allowances ?? 0), 0) }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #222; padding:6px;"><strong>Total Gross Salary</strong></td>
            <td style="border:1px solid #222; padding:6px;"><strong>{{ number_format((float)($monthly_salary ?? $salary ?? 0), 0) }}</strong></td>
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
        your notice period will be <strong>{{ $notice_period_confirmed ?? '30 Days' }}</strong>.
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
        Either party may terminate this employment by giving 30 days’ written notice or salary
        in lieu of notice after confirmation. During probation, 15 days’ notice or salary in lieu
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

    <div style="page-break-inside: avoid; margin-top:35px;">
        Warm Regards,<br>
        <strong>For {{ $company_name ?? branding_name() }}</strong>
        <div style="height: 60px; margin-top: 5px; margin-bottom: 5px; position: relative;">
            @if(!empty($signature_image))
                <img src="{{ $signature_image }}" style="max-height: 55px; max-width: 180px; display: inline-block;" alt="Signature">
            @else
                <div style="height: 40px;"></div>
            @endif
            @if(!empty($seal_image))
                <img src="{{ $seal_image }}" style="max-height: 65px; max-width: 65px; position: absolute; top: 5px; left: 140px;" alt="Seal">
            @endif
        </div>
        <strong>{{ $hr_manager_name ?? $authorized_signatory ?? 'HR Manager' }}</strong><br>
        {{ $signatory_designation ?? 'Human Resource Manager' }}
    </div>

    <div style="page-break-inside: avoid; margin-top:45px;">
        <h4 style="color:#1e3a8a; margin-bottom:10px;">Employee Acknowledgment</h4>

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