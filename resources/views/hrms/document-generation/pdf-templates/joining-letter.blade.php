@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Joining Letter')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>

    <div style="clear: both; margin-top: 15px;">
        <strong>To,</strong><br>
        <span style="font-size: 13px; font-weight: bold;">{{ $employee_name ?? 'Employee Name' }}</span><br>
        {{ $employee_address ?? 'Address Details' }}
    </div>

    <div class="text-center mt-4 mb-4">
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a; font-weight: bold;">JOINING LETTER & ONBOARDING ACKNOWLEDGMENT</h3>
    </div>

    <p>Dear <strong>{{ $employee_first_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        We are pleased to formally welcome you to the team at <strong>{{ $company_name ?? branding_name() }}</strong>. This letter serves as our official acknowledgment of your joining the organization.
    </p>

    <p class="text-justify">
        Your appointment is effective from your joining date, <strong>{{ $joining_date ?? 'Joining Date' }}</strong>, in the position of <strong>{{ $designation ?? 'Designation' }}</strong> within the <strong>{{ $department ?? 'Department' }}</strong> department.
    </p>

    <p class="text-justify">
        <strong>Onboarding Status & Instructions:</strong><br>
        {!! nl2br(e($joining_remarks ?? 'We are pleased to welcome you to our organization. Please submit your relevant documents and onboarding files to the HR department on your date of joining.')) !!}
    </p>

    <p class="text-justify">
        You will undergo the Company's standard employee orientation program, which will introduce you to our operational policies, work culture, and project guidelines. We expect you to maintain a high level of professionalism, diligence, and collaboration in your duties.
    </p>

    <p class="mt-4">We are excited about your journey with us and look forward to a successful partnership. Welcome aboard!</p>

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
