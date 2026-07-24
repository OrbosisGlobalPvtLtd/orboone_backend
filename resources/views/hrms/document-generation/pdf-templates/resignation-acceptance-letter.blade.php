@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Resignation Acceptance Letter')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>

    <div style="clear: both; margin-top: 15px;">
        <strong>To,</strong><br>
        <span style="font-size: 13px; font-weight: bold;">{{ $employee_name ?? 'Employee Name' }}</span><br>
        Employee Code: {{ $employee_code ?? 'EMP' }}
    </div>

    <div class="text-center mt-4 mb-4">
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a; font-weight: bold;">RESIGNATION ACCEPTANCE LETTER</h3>
    </div>

    <p>Dear <strong>{{ $employee_first_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        We are writing to formally acknowledge and accept your resignation from the position of <strong>{{ $designation ?? 'Designation' }}</strong>, which you submitted on <strong>{{ $resignation_date ?? date('d M, Y') }}</strong>.
    </p>

    <p class="text-justify">
        As mutually agreed, your resignation has been accepted by the management, and your last working day with the organization will be <strong>{{ $relieving_date ?? date('d M, Y') }}</strong>.
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #1e3a8a; padding: 12px; margin-top: 15px; margin-bottom: 15px;">
        <strong style="color: #1e3a8a;">Exit Clearance & Settlement Instructions:</strong>
        <p style="margin-top: 8px; margin-bottom: 0; font-size: 12.5px; line-height: 1.6;" class="text-justify">
            {!! nl2br(e($resignation_remarks ?? 'We hereby accept your formal resignation. We appreciate your contributions during your service. Please coordinate with the IT and Admin team for asset return and clearance procedures.')) !!}
        </p>
    </div>

    <p class="text-justify">
        We appreciate the service you have rendered to <strong>{{ $company_name ?? branding_name() }}</strong> during your tenure. We request you to complete all necessary task handover formalities and return all company assets, laptops, security badges, and documents in your possession prior to your final exit.
    </p>

    <p class="mt-4">We wish you the very best of success in all your future professional endeavors and personal pursuits.</p>

    <div class="signature-section signature-block" style="margin-top: 35px;">
        <table class="signature-table">
            <tr>
                <td>
                    Warm regards,<br>
                    <strong>For {{ $company_name ?? branding_name() }}</strong>
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
                    <strong>{{ $signatory_name ?? $authorized_signatory ?? 'Authorized Signatory' }}</strong><br>
                    {{ $signatory_designation ?? 'Head of Human Resources' }}
                </td>
                <td class="text-right">
                    Accepted & Acknowledged,<br>
                    <br><br><br><br>
                    ___________________________<br>
                    <strong>{{ $employee_name ?? 'Employee Name' }}</strong><br>
                    Date:
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
