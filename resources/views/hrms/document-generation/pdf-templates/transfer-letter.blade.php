@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Transfer Letter')

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
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a; font-weight: bold;">LETTER OF TRANSFER</h3>
    </div>

    <p>Dear <strong>{{ $employee_first_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        This is to inform you that the management of <strong>{{ $company_name ?? branding_name() }}</strong> has decided to transfer you from your current work location to our new operations branch, due to strategic business requirements and operational expansions.
    </p>

    <p class="text-justify">
        Effective from <strong>{{ $effective_date ?? date('d M, Y') }}</strong>, you will be transferred from <strong>{{ $current_branch ?? 'Current Location' }}</strong> to <strong>{{ $new_branch ?? 'New Location' }}</strong>. You will continue to hold your current designation as <strong>{{ $designation ?? 'Designation' }}</strong>.
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #1e3a8a; padding: 12px; margin-top: 15px; margin-bottom: 15px;">
        <strong style="color: #1e3a8a;">Transfer, Relocation & Handover Notes:</strong>
        <p style="margin-top: 8px; margin-bottom: 0; font-size: 12.5px; line-height: 1.6;" class="text-justify">
            {!! nl2br(e($transfer_remarks ?? 'This is to inform you that you are being transferred to our new branch. Please complete your ongoing project handover formalities and report to the branch head at the new location on the effective date.')) !!}
        </p>
    </div>

    <p class="text-justify">
        Your current salary, benefits, and overall terms and conditions of employment will remain unaffected by this transfer, unless explicitly stated in writing. We expect you to complete all ongoing project handovers to your team lead prior to your departure.
    </p>

    <p class="mt-4">We appreciate your support and cooperation in this transition. We wish you the very best in your new office environment.</p>

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
