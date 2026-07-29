@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Relieving Letter')

@section('content')
<div class="letter-body">

    <div style="text-align:center; margin-bottom:22px;">
        <h2 style="margin:0; color:#1e3a8a; font-size:18px; letter-spacing:1px; font-weight:bold;">
            ORBOSIS GLOBAL PRIVATE LIMITED
        </h2>

        <h3 style="margin:18px 0 0 0; color:#1e3a8a; font-size:17px; letter-spacing:1px; font-weight:bold;">
            RELIEVING LETTER
        </h3>
    </div>

    <p style="font-size:13px; margin-bottom:24px;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d/m/Y') }}
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        This letter confirms that
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
        {{ ucfirst($gender_pronoun_subject ?? 'they') }} has been relieved from
        {{ $gender_pronoun_possessive ?? 'their' }} duties effective from the close of
        working hours on <strong>{{ $relieving_date ?? 'Relieving Date' }}</strong>,
        following the completion of {{ $gender_pronoun_possessive ?? 'their' }}
        tenure with the organization.
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        We would like to confirm that
        {{ $gender_pronoun_subject ?? 'they' }} has duly completed all required handover
        formalities and there are no outstanding obligations from
        {{ $gender_pronoun_possessive ?? 'their' }} end as per the company’s policies.
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        Throughout {{ $gender_pronoun_possessive ?? 'their' }} tenure,
        {{ $gender_pronoun_subject ?? 'they' }} maintained a professional approach towards
        {{ $gender_pronoun_possessive ?? 'their' }} responsibilities and upheld the standards
        expected by the organization.
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8;">
        We take this opportunity to acknowledge
        {{ $gender_pronoun_possessive ?? 'their' }} association with the company and wish
        {{ $gender_pronoun_object ?? 'them' }} continued success in
        {{ $gender_pronoun_possessive ?? 'their' }} future professional endeavors.
    </p>

    <div style="page-break-inside:avoid; margin-top:65px;">
        <p style="font-size:13px; line-height:1.8; margin:0;">
            <strong>For {{ $company_name ?? branding_name() }}</strong>
        </p>

        <div style="min-height: 65px; margin-top: 5px; margin-bottom: 5px;">
            @if(!empty($signature_image) && !empty($seal_image))
                <div style="display: inline-block; vertical-align: middle;">
                    <img src="{{ $signature_image }}" style="height: 55px; width: auto; max-width: 160px; vertical-align: middle;" alt="Signature">
                    <img src="{{ $seal_image }}" style="height: 65px; width: auto; max-width: 120px; vertical-align: middle; margin-left: 15px;" alt="Seal">
                </div>
            @elseif(!empty($signature_image))
                <img src="{{ $signature_image }}" style="height: 55px; width: auto; max-width: 180px; vertical-align: middle;" alt="Signature">
            @elseif(!empty($seal_image))
                <img src="{{ $seal_image }}" style="height: 65px; width: auto; max-width: 120px; vertical-align: middle; display: block;" alt="Seal">
            @else
                <div style="height: 45px;"></div>
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