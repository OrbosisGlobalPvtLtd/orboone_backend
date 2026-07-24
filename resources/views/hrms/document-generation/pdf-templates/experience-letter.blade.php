@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Experience Certificate')

@section('content')
<div class="letter-body">

    <div style="text-align:center; margin-bottom:22px;">
        <h2 style="margin:0; color:#1e3a8a; font-size:18px; letter-spacing:1px; font-weight:bold;">
            ORBOSIS GLOBAL PRIVATE LIMITED
        </h2>

        <h3 style="margin:18px 0 0 0; color:#1e3a8a; font-size:17px; letter-spacing:1px; font-weight:bold;">
            EXPERIENCE CERTIFICATE
        </h3>
    </div>

    <p style="font-size:13px; margin-bottom:24px;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d/m/Y') }}
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        This is to certify that
        <strong>{{ $employee_prefix ?? 'Mr./Ms.' }} {{ $employee_name ?? 'Employee Name' }}</strong>
        was employed with
        <strong>{{ $company_name ?? branding_name() }}</strong>
        as a
        <strong>{{ $designation ?? 'Employee' }}</strong>
        from
        <strong>{{ $joining_date ?? 'Joining Date' }}</strong>
        to
        <strong>{{ $relieving_date ?? 'Relieving Date' }}</strong>.
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        During {{ $gender_pronoun_possessive ?? 'their' }} tenure,
        <strong>{{ $employee_prefix ?? 'Mr./Ms.' }} {{ $employee_name ?? 'Employee Name' }}</strong>
        was responsible for
        {!! nl2br(e($experience_responsibilities ?? 'managing assigned responsibilities, coordinating with team members, supporting day-to-day operations, and ensuring timely completion of work as per organizational requirements.')) !!}
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        {{ ucfirst($gender_pronoun_subject ?? 'they') }} demonstrated a responsible and proactive
        approach towards {{ $gender_pronoun_possessive ?? 'their' }} work, along with the ability
        to manage {{ $gender_pronoun_possessive ?? 'their' }} responsibilities effectively.
        {{ ucfirst($gender_pronoun_subject ?? 'they') }} maintained a professional attitude
        throughout {{ $gender_pronoun_possessive ?? 'their' }} tenure and handled tasks with
        clarity and consistency.
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        During {{ $gender_pronoun_possessive ?? 'their' }} time with the organization,
        {{ $gender_pronoun_subject ?? 'they' }} showed sincerity and commitment towards
        {{ $gender_pronoun_possessive ?? 'their' }} role.
        {{ ucfirst($gender_pronoun_subject ?? 'they') }} was dependable in executing assigned
        responsibilities, coordinating with team members, and ensuring smooth day-to-day
        operations. {{ ucfirst($gender_pronoun_possessive ?? 'their') }} overall contribution
        supported the functioning of the department in a positive manner.
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        {{ $performance_summary ?? 'Their conduct and performance throughout their tenure were found to be satisfactory.' }}
    </p>

    <div style="page-break-inside:avoid; margin-top:65px;">
        <p style="font-size:13px; line-height:1.8; margin:0;">
            <strong>For {{ $company_name ?? branding_name() }}</strong>
        </p>

        <div style="height: 60px; margin-top: 5px; margin-bottom: 5px; position: relative;">
            @if(!empty($signature_image))
                <img src="{{ $signature_image }}" style="height: 55px; width: auto; max-width: 180px; display: inline-block; vertical-align: middle;" alt="Signature">
            @else
                <div style="height: 40px;"></div>
            @endif
            @if(!empty($seal_image))
                <img src="{{ $seal_image }}" style="height: 65px; width: auto; max-width: 120px; position: absolute; top: -5px; left: 50%; margin-left: -60px; vertical-align: middle;" alt="Seal">
            @endif
        </div>

        <p style="font-size:13px; line-height:1.6; margin:0;">
            <strong>{{ $signatory_name ?? $authorized_signatory ?? 'Prabhat Agrawal' }}</strong><br>
            {{ $signatory_designation ?? 'Chief Executive Officer' }}<br>
            {{ $company_name ?? branding_name() }}
        </p>
    </div>

</div>
@endsection