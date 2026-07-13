@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Promotion Letter')

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
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a; font-weight: bold;">LETTER OF PROMOTION</h3>
    </div>

    <p>Dear <strong>{{ $employee_first_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        On behalf of the management at <strong>{{ $company_name ?? branding_name() }}</strong>, we are absolutely delighted to inform you that you have been promoted. This promotion is a reflection of your outstanding performance, continuous dedication, and valuable contributions to the organization.
    </p>

    <p class="text-justify">
        Effective from <strong>{{ $effective_date ?? date('d M, Y') }}</strong>, you are promoted from the position of <strong>{{ $current_designation ?? 'Current Designation' }}</strong> to <strong>{{ $new_designation ?? 'New Designation' }}</strong> within the <strong>{{ $department ?? 'Department' }}</strong> department.
    </p>

    <p class="text-justify">
        In connection with your promotion, your monthly gross salary has been revised to <strong>₹ {{ number_format((float)($monthly_salary ?? 0), 2) }}</strong> (Rupees <strong>{{ $salary_in_words ?? 'As Agreed' }}</strong> Only).
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #1e3a8a; padding: 12px; margin-top: 15px; margin-bottom: 15px;">
        <strong style="color: #1e3a8a;">Roles, Responsibilities & Remarks:</strong>
        <p style="margin-top: 8px; margin-bottom: 0; font-size: 12.5px; line-height: 1.6;" class="text-justify">
            {!! nl2br(e($promotion_remarks ?? 'Due to your exemplary performance, dedication, and positive contribution to the team, the management is pleased to promote you. In your new role, you will be responsible for leading key deliverables and guiding junior team members.')) !!}
        </p>
    </div>

    <p class="text-justify">
        All other terms and conditions of your employment remain unchanged. We appreciate your efforts and commitment towards the company and are confident that you will continue to achieve success and inspire your peers in this new capacity.
    </p>

    <p class="mt-4">Congratulations once again on this well-deserved promotion. We wish you continued growth and success with us.</p>

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
