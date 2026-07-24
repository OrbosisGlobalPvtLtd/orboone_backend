@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Warning Letter')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>
    
    <div style="clear: both; margin-top: 15px;">
        <strong>To,</strong><br>
        <span style="font-size: 13px; font-weight: bold;">{{ $employee_name ?? 'Employee Name' }}</span><br>
        Employee Code: {{ $employee_code ?? 'EMP' }}<br>
        {{ $designation ?? 'Software Engineer' }} - {{ $department ?? 'Engineering' }}
    </div>

    <div class="text-center mt-4 mb-4">
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #b91c1c; font-weight: bold;">LETTER OF WARNING / REMAND</h3>
    </div>

    <p>Dear <strong>{{ $employee_first_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        This letter serves as a formal written warning concerning your performance / conduct issues at <strong>{{ $company_name ?? branding_name() }}</strong>. 
        It has been brought to the attention of the management that you have committed a breach of company policies and guidelines.
    </p>

    <p class="text-justify" style="background-color: #fef2f2; border-left: 4px solid #b91c1c; padding: 12px; color: #7f1d1d;">
        <strong>Subject:</strong> {{ $warning_subject ?? 'Letter of Warning' }}<br>
        @if(!empty($incident_date))
        <strong>Incident Date:</strong> {{ date('d M, Y', strtotime($incident_date)) }}<br>
        @endif
        <strong style="display: block; margin-top: 6px;">Reason for Warning:</strong>
        {!! nl2br(e($warning_reason ?? $warning_details ?? $description ?? 'Unexcused absences, persistent delays in deliverables, or unprofessional behavior during work hours.')) !!}
    </p>

    <p class="text-justify">
        <strong>Required Corrective Action:</strong><br>
        {!! nl2br(e($corrective_action ?? $action_required ?? 'You are instructed to immediately rectify these performance gaps and display a high standard of professional conduct and discipline. Your reporting manager will review your progress weekly.')) !!}
    </p>

    <p class="text-justify">
        Please note that this is a serious matter and the Company expects immediate improvement. Failure to correct your performance or any further occurrence of such behavior will result in severe disciplinary actions, up to and including immediate termination of your employment services.
    </p>

    <p class="mt-4">Please sign and return a duplicate copy of this warning letter as an acknowledgment of receipt.</p>

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
                            <img src="{{ $seal_image }}" style="height: 65px; width: auto; max-width: 120px; position: absolute; top: -5px; left: 50%; margin-left: -60px; vertical-align: middle;" alt="Seal">
                        @endif
                    </div>
                    <strong>{{ $signatory_name ?? $authorized_signatory ?? 'Authorized Signatory' }}</strong><br>
                    {{ $signatory_designation ?? 'Head of Human Resources' }}
                </td>
                <td class="text-right">
                    Acknowledged & Received,<br>
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
