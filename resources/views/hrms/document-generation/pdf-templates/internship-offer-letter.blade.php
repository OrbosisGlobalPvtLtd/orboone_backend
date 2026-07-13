@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Internship Offer Letter')

@section('content')
@php
$candidateName = $employee_name ?? $candidate_name ?? 'Candidate Name';
$candidateFirstName = (!empty($employee_first_name) ? $employee_first_name : null) ?? explode(' ', trim($candidateName))[0] ?? 'Candidate';
$companyName = $company_name ?? branding_name();
if (empty($companyName) || $companyName === 'HRMS' || $companyName === 'Default') {
    $companyName = 'Orbosis Global Pvt. Ltd.';
}
$issueDate = $issue_date ?? $current_date ?? date('d M, Y');
$joiningDate = $joining_date ?? 'To Be Confirmed';
$designationText = $designation ?? 'Software Engineer Intern';
$internshipDuration = $internship_duration ?? '3-month';
$internshipMode = $internship_mode ?? 'Hybrid';
$officeLocation = $office_location ?? 'Indore Office';
$stipendAmount = $stipend_amount ?? 18000;
$compensationType = $compensation_type ?? 'Unpaid';
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
                <strong>To,</strong><br>
                <strong>{{ $candidateName }}</strong><br>
                {{ $employee_city ?? $candidate_city ?? 'Indore' }},
            </td>
            <td style="border:none; padding:0; width:45%; text-align:right; vertical-align:top;">
                <strong>Date:</strong> {{ $issueDate }}
            </td>
        </tr>
    </table>

    <div style="text-align: center; margin-top: 15px; margin-bottom: 20px; border-top: 1px solid #1c72b8; border-bottom: 1px solid #1c72b8; padding: 6px 0; background-color: #f8fafc;">
        <span style="color: #1c72b8; font-weight: bold; font-size: 13.5px;">
            Subject: Offer of Internship for {{ $designationText }} Position
        </span>
    </div>

    <p>Dear {{ $candidateFirstName }},</p>

    <p class="text-justify">
        We are pleased to offer you the position of <strong>{{ $designationText }}</strong> with <strong>{{ $companyName }}</strong>, commencing on <strong>{{ $joiningDate }}</strong>. This is a <strong>{{ $internshipDuration }} full-time @if($compensationType === 'Unpaid')unpaid @endif internship</strong> conducted in <strong>{{ $internshipMode }}</strong> mode at our <strong>{{ $officeLocation }}</strong>. The internship is designed to provide you with practical exposure to software development practices, industry methodologies, and corporate work processes.
    </p>

    <p class="text-justify">
        Your working hours will be <strong>{{ $working_hours ?? '10:00 AM to 7:00 PM' }}</strong>, 
        <strong>{{ $working_days ?? 'Monday to Saturday' }}</strong>, 
        {{ $saturday_off_clause ?? 'with second and fourth Saturdays observed as off (alternate Saturdays off)' }}, as per company policy.
        @if($compensationType === 'Paid')
            You will receive a monthly stipend of <strong>₹{{ is_numeric($stipendAmount) ? number_format((float)$stipendAmount) : $stipendAmount }}</strong> during the internship period.
        @endif
    </p>

    <p class="text-justify">
        {!! nl2br(e($job_responsibilities ?? 'During the internship, you will assist in software development, testing, debugging, and application maintenance under the guidance of senior team members. You will contribute to real-world projects, participate in code reviews, and support documentation while collaborating with the team. This internship is designed to help you build practical technical skills and gain exposure to industry-standard software development practices.')) !!}
    </p>

    <p class="text-justify">
        Upon successful completion of the internship, you will receive an <strong>Internship Completion Certificate</strong>. Based on your performance and organizational requirements, you may be offered a full-time employment opportunity. If performance expectations are not met, the internship may be extended for further evaluation.
    </p>

    <p class="text-justify">
        Kindly confirm your acceptance of this offer by replying to this email.
    </p>

    <div class="signature-section signature-block" style="margin-top:45px;">
        <table class="signature-table">
            <tr>
                <td style="border:none; padding:0; width:100%; vertical-align:bottom;">
                    <strong>For {{ $companyName }}</strong><br>
                    <div style="height: 50px; margin-top: 5px; margin-bottom: 5px; position: relative;">
                        @if(!empty($signature_image))
                            <img src="{{ $signature_image }}" style="height: 45px; width: auto; max-width: 150px; display: inline-block; vertical-align: middle;" alt="Signature">
                        @else
                            <div style="height: 35px;"></div>
                        @endif
                    </div>
                    <strong>{{ $hr_manager_name ?? 'Vanshika Dhunna' }}</strong><br>
                    {{ $signatory_designation ?? 'HR Manager' }}<br>
                    {{ $companyName }}
                </td>
            </tr>
        </table>
    </div>

</div>
@endsection
