@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Confirmation Letter')

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
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a; font-weight: bold;">LETTER OF EMPLOYMENT CONFIRMATION</h3>
    </div>

    <p>Dear <strong>{{ $employee_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        We are pleased to inform you that your performance and conduct during your probation period have been evaluated and found to be highly satisfactory. Consequently, the management of <strong>{{ $company_name ?? branding_name() }}</strong> is pleased to confirm your employment with the organization.
    </p>

    <p class="text-justify">
        Your confirmation is effective from <strong>{{ $confirmation_date ?? date('d M, Y') }}</strong>. You will continue to serve in the position of <strong>{{ $designation ?? 'Designation' }}</strong> in the <strong>{{ $department ?? 'Department' }}</strong> department.
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #1e3a8a; padding: 12px; margin-top: 15px; margin-bottom: 15px;">
        <strong style="color: #1e3a8a;">Employment Details & Remarks:</strong>
        <p style="margin-top: 8px; margin-bottom: 0; font-size: 12.5px; line-height: 1.6;" class="text-justify">
            {!! nl2br(e($confirmation_remarks ?? 'We are pleased to confirm your employment with our organization. Your probation period has been successfully completed, and you are now a permanent employee. We look forward to your continued contribution and growth with us.')) !!}
        </p>
    </div>

    <p class="text-justify">
        All other terms and conditions of your employment as detailed in your Appointment Letter will remain in full force. We appreciate your dedication, hard work, and values that you bring to the team, and we look forward to a mutually beneficial association with you.
    </p>

    <p class="mt-4">We congratulate you on your confirmation and wish you a rewarding career with us.</p>

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
                            <img src="{{ $seal_image }}" style="height: 65px; width: auto; max-width: 120px; position: absolute; top: 5px; left: 140px; vertical-align: middle;" alt="Seal">
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
