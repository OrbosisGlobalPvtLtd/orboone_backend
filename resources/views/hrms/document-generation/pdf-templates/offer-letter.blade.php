@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Offer Letter')

@section('content')
@php
$candidateName = $employee_name ?? $candidate_name ?? 'Candidate Name';
$candidateFirstName = (!empty($employee_first_name) ? $employee_first_name : null) ?? explode(' ', trim($candidateName))[0] ?? 'Candidate';
$companyName = $company_name ?? branding_name();
$issueDate = $issue_date ?? $current_date ?? date('d M, Y');
$joiningDate = $joining_date ?? 'To Be Confirmed';
$designationText = $designation ?? 'Software Developer';
$departmentText = $department ?? 'Engineering';
$officeLocation = $office_location ?? $work_location ?? 'Indore';
$annualCtc = $annual_ctc ?? (($monthly_gross_salary ?? 0) * 12);
$monthlyGross = $monthly_gross_salary ?? 0;
$basicMonthly = $basic_monthly ?? ($monthlyGross * 0.50);
$hraMonthly = $hra_monthly ?? ($monthlyGross * 0.20);
$specialMonthly = $special_allowance_monthly ?? ($monthlyGross - $basicMonthly - $hraMonthly);
$ptMonthly = $professional_tax_monthly ?? 200;
$netMonthly = $net_pay_monthly ?? ($monthlyGross - $ptMonthly);
@endphp

<div class="letter-body">

    <div style="text-align:center; margin-bottom:18px;">
        <h3 style="text-decoration: underline; font-size:16px; font-weight:700; color:#111827; margin:0;">
            OFFER LETTER
        </h3>
    </div>

    <table style="width:100%; border-collapse:collapse; border:none; margin-bottom:18px;">
        <tr>
            <td style="border:none; padding:0; width:55%; vertical-align:top;">
                <strong>{{ $candidateName }}</strong><br>
                {{ $employee_city ?? $candidate_city ?? 'Indore' }},
            </td>
            <td style="border:none; padding:0; width:45%; text-align:right; vertical-align:top;">
                <strong>Date:</strong> {{ $issueDate }}
            </td>
        </tr>
    </table>

    <p>Dear {{ $candidateFirstName }},</p>

    <p class="text-justify">
        {{ $companyName }} is pleased to offer you the position of
        <strong>{{ $designationText }}</strong> with our organization. We are excited about the skills,
        energy, and perspective you bring to our team, and we look forward to your contribution to our
        ongoing growth and success.
    </p>

    <p class="text-justify">
        After your internship and reviewing your performance and skills, we believe you will be a good fit
        for our organization. We are pleased to confirm your joining with {{ $companyName }}, effective
        <strong>{{ $joiningDate }}</strong>, with working hours from
        <strong>{{ $working_hours ?? '10:00 AM to 7:00 PM' }}</strong>,
        {{ $working_days ?? 'Monday to Saturday' }}, your primary place of work will be at
        <strong>{{ $officeLocation }}</strong> office.
    </p>

    @if(isset($compensation_type) && $compensation_type === 'Unpaid')
        <p class="text-justify">
            {!! nl2br(e($unpaid_clause ?? 'This offer is for an unpaid engagement. No salary, stipend, or monetary compensation shall be payable during this period unless separately approved in writing by the Company. The engagement is intended to provide professional exposure, learning, project experience, and practical workplace training.')) !!}
        </p>
    @else
        <p class="text-justify">
            Your annual compensation package will be
            <strong>₹ {{ is_numeric($annualCtc) ? number_format((float)$annualCtc, 2) : $annualCtc }}</strong>.
            A detailed breakup of your salary structure is provided below as per company policy.
        </p>
    @endif

    <p class="text-justify">
        In this role, you will work on
        {!! nl2br(e($job_responsibilities ?? 'developing, maintaining, optimizing, testing, and delivering assigned software/project tasks as per company requirements. You will also complete tasks assigned by your reporting manager and are expected to perform your duties sincerely, follow company guidelines, and work cooperatively with team members while maintaining professional conduct at all times.')) !!}
    </p>

    <p class="text-justify">
        You will be on probation for a period of
        <strong>{{ $probation_period ?? 'Three months' }}</strong> from your date of joining.
        During this period, your performance and conduct will be reviewed. Upon satisfactory performance,
        your employment may be confirmed. The probation period may be extended if required.
    </p>

    <p class="text-justify">
        Your working hours, weekly offs, leave entitlements, holidays, and other benefits will be governed
        by company policy and shared with you after joining. Due to work requirements, you may occasionally
        need to work additional hours to meet project deadlines, without additional compensation unless
        specified by policy.
    </p>

    <p class="text-justify">
        During your employment, you may have access to confidential company information. You are required
        to maintain strict confidentiality of all such information during and after your employment. Any
        misuse or unauthorized sharing of company information may result in disciplinary action.
    </p>

    <p class="text-justify">
        Any work, code, designs, developments, or improvements created by you during the course of your
        employment will be the property of {{ $companyName }}, as per applicable laws and company policy.
    </p>

    <p class="text-justify">
        This offer is subject to verification of your educational qualifications, previous employment details,
        and other required documents. If any information provided is found to be incorrect, the company
        reserves the right to withdraw this offer or terminate employment.
    </p>

    <div class="page-break"></div>

    <p class="text-justify">
        This offer letter provides an overview of your employment. The complete terms and conditions,
        including company policies, service rules, probation details, and code of conduct, will be communicated
        to you through a formal Appointment Letter, which will be issued after your joining and completion of
        joining formalities.
    </p>

    <p><strong>Submission of Documents:</strong></p>

    <p class="text-justify">
        At the time of your joining, a photocopy of the following documents should be submitted.
        Please carry the original copies for verification.
    </p>

    <ul style="margin-top:8px; padding-left:22px;">
        <li>A signed copy of the Offer Letter.</li>
        <li>Aadhar Card.</li>
        <li>Permanent Account Number (PAN) Card.</li>
        <li>Marksheet X.</li>
        <li>Marksheet XII.</li>
        <li>Experience Letter.</li>
        <li>Relieving Letter.</li>
        <li>Degree Certificate.</li>
        <li>Postgraduate Degree Certificate, if applicable.</li>
        <li>Proof of Age.</li>
        <li>2 Photographs.</li>
        <li>Bank Details.</li>
    </ul>

    <p class="text-justify">
        Please note that this offer letter is valid until
        <strong>{{ $offer_valid_till ?? date('d M, Y', strtotime('+7 days')) }}</strong>.
        Kindly confirm your acceptance by signing and returning a copy of this letter on or before the mentioned date.
        If we do not receive your response within the stipulated time, this offer may be withdrawn.
    </p>

    <p class="text-justify">
        We are excited about the opportunity to work with you and welcome you to {{ $companyName }}.
        We hope this will be a positive and successful association for both you and the organization.
    </p>

    <p class="text-justify">
        If you have any questions or need clarification, please feel free to contact the Human Resources team.
    </p>

    <div class="signature-section signature-block" style="margin-top:35px;">
        <table class="signature-table">
            <tr>
                <td>
                    <strong>{{ $signatory_designation ?? 'Human Resource Manager' }}</strong><br>
                    <div style="height: 50px; margin-top: 5px; margin-bottom: 5px; position: relative;">
                        @if(!empty($signature_image))
                            <img src="{{ $signature_image }}" style="height: 45px; width: auto; max-width: 150px; display: inline-block; vertical-align: middle;" alt="Signature">
                        @else
                            <div style="height: 35px;"></div>
                        @endif
                        <!-- @if(!empty($seal_image))
                            <img src="{{ $seal_image }}" style="height: 55px; width: auto; max-width: 100px; position: absolute; top: 5px; left: 120px; vertical-align: middle;" alt="Seal">
                        @endif -->
                    </div>
                    <strong>{{ $hr_manager_name ?? $authorized_signatory ?? 'HR' }}</strong><br>
                    {{ $companyName }}
                </td>
                <td class="text-right">
                    <strong>Candidate’s Signature</strong><br><br><br>
                    <strong>{{ $candidateName }}</strong>
                </td>
            </tr>
        </table>
    </div>

    @if(!isset($compensation_type) || $compensation_type !== 'Unpaid')
        <div class="page-break"></div>

        <div class="text-center mb-4">
            <h3 style="text-decoration: underline; font-size:15px; color:#111827;">Annexure</h3>
        </div>

        <table class="table" style="font-size:11px;">
            <thead>
                <tr>
                    <th style="width:50%;"></th>
                    <th style="width:25%; text-align:center;">Monthly (₹)</th>
                    <th style="width:25%; text-align:center;">Annual (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Gross Salary</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$monthlyGross, 2) }}</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$monthlyGross * 12, 2) }}</strong></td>
                </tr>

                <tr>
                    <td colspan="3" style="height:12px;"></td>
                </tr>

                <tr>
                    <td colspan="3"><strong>Salary Structure (A)</strong></td>
                </tr>
                <tr>
                    <td>Basic</td>
                    <td class="text-center">{{ number_format((float)$basicMonthly, 2) }}</td>
                    <td class="text-center">{{ number_format((float)$basicMonthly * 12, 2) }}</td>
                </tr>
                <tr>
                    <td>HRA</td>
                    <td class="text-center">{{ number_format((float)$hraMonthly, 2) }}</td>
                    <td class="text-center">{{ number_format((float)$hraMonthly * 12, 2) }}</td>
                </tr>
                <tr>
                    <td>Special Allowance</td>
                    <td class="text-center">{{ number_format((float)$specialMonthly, 2) }}</td>
                    <td class="text-center">{{ number_format((float)$specialMonthly * 12, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Subtotal (A)</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$monthlyGross, 2) }}</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$monthlyGross * 12, 2) }}</strong></td>
                </tr>

                <tr>
                    <td colspan="3" style="height:12px;"></td>
                </tr>

                <tr>
                    <td colspan="3"><strong>Deductions (B)</strong></td>
                </tr>
                <tr>
                    <td>Professional Tax</td>
                    <td class="text-center">{{ number_format((float)$ptMonthly, 2) }}</td>
                    <td class="text-center">{{ number_format((float)$ptMonthly * 12, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Subtotal (B)</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$ptMonthly, 2) }}</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$ptMonthly * 12, 2) }}</strong></td>
                </tr>

                <tr>
                    <td colspan="3" style="height:12px;"></td>
                </tr>

                <tr>
                    <td><strong>CTC (A-B)</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$monthlyGross, 2) }}</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$annualCtc, 2) }}</strong></td>
                </tr>

                <tr>
                    <td style="height:45px;"><strong>Net Pay (A-B)<br>(Take Home Salary)</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$netMonthly, 2) }}</strong></td>
                    <td class="text-center"><strong>{{ number_format((float)$netMonthly * 12, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section signature-block" style="margin-top:45px;">
            <table class="signature-table">
                <tr>
                    <td>
                        <strong>{{ $signatory_designation ?? 'Human Resource Manager' }}</strong><br>
                        <div style="height: 50px; margin-top: 5px; margin-bottom: 5px; position: relative;">
                            @if(!empty($signature_image))
                                <img src="{{ $signature_image }}" style="height: 45px; width: auto; max-width: 150px; display: inline-block; vertical-align: middle;" alt="Signature">
                            @else
                                <div style="height: 35px;"></div>
                            @endif
                            <!-- @if(!empty($seal_image))
                                <img src="{{ $seal_image }}" style="height: 55px; width: auto; max-width: 100px; position: absolute; top: 5px; left: 120px; vertical-align: middle;" alt="Seal">
                            @endif -->
                        </div>
                        <strong>{{ $hr_manager_name ?? $authorized_signatory ?? 'HR' }}</strong><br>
                        {{ $companyName }}
                    </td>
                    <td class="text-right">
                        <strong>Candidate’s Signature</strong><br><br><br>
                        <strong>{{ $candidateName }}</strong>
                    </td>
                </tr>
            </table>
        </div>
    @endif

</div>
@endsection