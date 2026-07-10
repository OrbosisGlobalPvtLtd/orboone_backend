@extends('hrms.document-generation.pdf-templates.layouts.document-layout', ['hide_footer' => true])

@section('title', 'Discontinuing Letter')

@section('content')
@php
$employeeName = $employee_name ?? 'Ritik';
$companyName = $company_name ?? branding_name();
if (empty($companyName) || $companyName === 'HRMS' || $companyName === 'Default') {
    $companyName = 'Orbosis Global Pvt. Ltd.';
}
$issueDate = $issue_date ?? $current_date ?? date('d M, Y');
$discontinueDate = $discontinue_date ?? date('d M, Y');
$hrManagerName = $hr_manager_name ?? 'Vanshika Dhunna';
$signatoryDesignation = $signatory_designation ?? 'HR Manager';
@endphp

<div class="letter-body">

    <div style="text-align:center; margin-bottom:18px;">
        <h3 style="color:#1c72b8; font-size:15px; font-weight:700; margin:0; letter-spacing: 0.5px;">
            {{ $companyName }}
        </h3>
        <h3 style="color:#1c72b8; font-size:15px; font-weight:700; margin:4px 0 0 0; letter-spacing: 0.5px;">
            Discontinuing Letter
        </h3>
    </div>

    <p style="font-size:13px; margin-bottom:24px; margin-top:20px;">
        <strong>Dear {{ $employeeName }},</strong>
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8; margin-bottom:18px;">
        {!! nl2br(e($discontinue_reason ?? "After careful review of the Company's current business requirements and financial position, we regret to inform you that the Company has decided to discontinue your employment with the Company effective " . $discontinueDate . ".")) !!}
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8; margin-bottom:18px;">
        This decision has been made solely due to organizational and financial considerations and does not reflect your performance, commitment, or contributions during your tenure with the Company. We sincerely appreciate the efforts and professionalism you have demonstrated while working with us.
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8; margin-bottom:18px;">
        {!! nl2br(e($handover_clause ?? "You are requested to complete all handover formalities and return any Company assets, documents, or access credentials in your possession on or before your last working day. The Company will process your final settlement and any applicable dues in accordance with Company policy and applicable laws.")) !!}
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8; margin-bottom:18px;">
        We would like to thank you for your valuable contributions and wish you every success in your future professional endeavors.
    </p>

    <p class="text-justify" style="font-size:13px; line-height:1.8; margin-bottom:18px;">
        Should you require any clarification regarding the separation process, please feel free to contact the HR Department.
    </p>

    <div style="page-break-inside:avoid; margin-top:40px;">
        <p style="font-size:13px; line-height:1.8; margin:0;">
            <strong>For {{ $companyName }}</strong>
        </p>

        <div style="height: 55px; margin-top: 5px; margin-bottom: 5px; position: relative;">
            @if(!empty($signature_image))
                <img src="{{ $signature_image }}" style="height: 48px; width: auto; max-width: 180px; display: inline-block; vertical-align: middle;" alt="Signature">
            @else
                <div style="height: 35px;"></div>
            @endif
        </div>

        <p style="font-size:13px; line-height:1.6; margin:0;">
            <strong>{{ $hrManagerName }}</strong><br>
            {{ $signatoryDesignation }}
        </p>
    </div>

</div>
@endsection
