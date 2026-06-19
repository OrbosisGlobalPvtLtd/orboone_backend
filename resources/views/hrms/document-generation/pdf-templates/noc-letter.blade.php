@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'No Objection Certificate')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>

    <div style="clear: both; margin-top: 25px;">
        <strong>To Whomsoever It May Concern</strong>
    </div>

    <div class="text-center mt-4 mb-4">
        <h3 style="text-decoration: underline; letter-spacing: 2px; color: #1e3a8a; font-weight: bold;">NO OBJECTION CERTIFICATE</h3>
    </div>

    <p class="text-justify" style="line-height: 1.8;">
        This is to certify that <strong>Mr./Ms. {{ $employee_name ?? 'Employee Name' }}</strong> (Employee Code: <strong>{{ $employee_code ?? 'EMP' }}</strong>) is employed with <strong>{{ $company_name ?? branding_name() }}</strong> as a <strong>{{ $designation ?? 'Designation' }}</strong> in the <strong>{{ $department ?? 'Department' }}</strong> department.
    </p>

    <p class="text-justify" style="line-height: 1.8;">
        The Employee has expressed interest in applying for <strong>{{ $noc_purpose ?? 'higher studies / visa processing / external project collaboration' }}</strong>.
    </p>

    <p class="text-justify" style="line-height: 1.8;">
        We wish to confirm that <strong>{{ $company_name ?? branding_name() }}</strong> has no objection to the Employee pursuing the aforementioned activity. We confirm that their work schedule and responsibilities will not conflict with this request, or have been mutually agreed upon with the management.
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #1e3a8a; padding: 12px; margin-top: 15px; margin-bottom: 15px;">
        <strong style="color: #1e3a8a;">Additional Remarks:</strong>
        <p style="margin-top: 8px; margin-bottom: 0; font-size: 12.5px; line-height: 1.6;" class="text-justify">
            {!! nl2br(e($noc_remarks ?? 'This is to confirm that the Company has no objection to the employee pursuing their requested application. We wish them all the best in their future endeavors.')) !!}
        </p>
    </div>

    <p class="text-justify" style="line-height: 1.8; margin-top: 20px;">
        This certificate is issued upon the request of the employee and does not impose any financial or legal liability on the Company.
    </p>

    <div class="signature-section signature-block" style="margin-top: 35px;">
        <table class="signature-table">
            <tr>
                <td>
                    Sincerely,<br>
                    <strong>For {{ $company_name ?? branding_name() }}</strong>
                    <div style="height: 60px; margin-top: 5px; margin-bottom: 5px; position: relative;">
                        @if(!empty($signature_image))
                            <img src="{{ $signature_image }}" style="height: 55px; width: auto; max-width: 180px; display: inline-block; vertical-align: middle;" alt="Signature">
                        @else
                            <div style="height: 40px;"></div>
                        @endif
                        @if(!empty($seal_image))
                            <img src="{{ $seal_image }}" style="height: 65px; width: auto; max-width: 120px; position: absolute; top: 5px; left: 140px; vertical-align: middle;" alt="Seal">
                        @endif
                    </div>
                    <strong>{{ $signatory_name ?? $authorized_signatory ?? 'Authorized Signatory' }}</strong><br>
                    {{ $signatory_designation ?? 'Head of Human Resources' }}
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
@endsection
