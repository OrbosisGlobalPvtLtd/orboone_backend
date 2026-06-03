@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Relieving Letter')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>
    
    <div style="clear: both; margin-top: 15px;">
        <strong>To,</strong><br>
        <span style="font-size: 13px; font-weight: bold;">{{ $employee_name ?? 'Employee Name' }}</span><br>
        {{ $employee_address ?? 'Employee Address' }}<br>
        {{ $employee_city ?? '' }}
    </div>

    <div class="text-center mt-4 mb-4">
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a;">RELIEVING LETTER & FULL-SETTLEMENT RECEIPT</h3>
    </div>

    <p>Dear <strong>{{ $employee_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        We refer to your resignation letter dated <strong>{{ $resignation_date ?? 'N/A' }}</strong> from the post of 
        <strong>{{ $designation ?? 'Software Engineer' }}</strong> at <strong>{{ $company_name ?? branding_name() }}</strong>.
    </p>

    <p class="text-justify">
        We would like to inform you that your resignation has been accepted by the management and you are hereby officially relieved from the services of the Company at the close of working hours on <strong>{{ $relieving_date ?? 'Relieving Date' }}</strong>. Your employment tenure with us was from <strong>{{ $joining_date ?? 'Joining Date' }}</strong> to <strong>{{ $relieving_date ?? 'Relieving Date' }}</strong>.
    </p>

    <p class="text-justify">
        <strong>Handover & Clearance Status:</strong><br>
        {!! nl2br(e($handover_status ?? 'All company assets, including laptop, security access badges, source code repositories, and work documents, have been successfully handed over to the designated team leader. Full and final settlement of accounts has been fully completed and paid.')) !!}
    </p>

    <p class="text-justify">
        We certify that you have complied with the company exit protocol and have settled all outstanding dues. The company records reflect that during your employment, your conduct, integrity, and performance were satisfactory.
    </p>

    <p class="text-justify">
        We thank you for the services rendered to <strong>{{ $company_name ?? branding_name() }}</strong> during your tenure and wish you the very best in all your future professional and personal endeavors.
    </p>

    <div class="signature-section" style="margin-top: 60px;">
        <table class="signature-table">
            <tr>
                <td>
                    Sincerely,<br>
                    <strong>For {{ $company_name ?? branding_name() }}</strong>
                    <br><br><br><br>
                    <strong>{{ $signatory_name ?? $authorized_signatory ?? 'Authorized Signatory' }}</strong><br>
                    {{ $signatory_designation ?? 'Manager - Human Resources' }}
                </td>
                <td class="text-right">
                    Received & Acknowledged,<br>
                    <br><br><br><br>
                    ___________________________<br>
                    <strong>{{ $employee_name ?? 'Employee Signature' }}</strong><br>
                    Date:
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
