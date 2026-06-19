@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Increment Letter')

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
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a; font-weight: bold;">SALARY INCREMENT & REVISION LETTER</h3>
    </div>

    <p>Dear <strong>{{ $employee_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        We would like to express our appreciation for your valuable contributions, hard work, and commitment to <strong>{{ $company_name ?? branding_name() }}</strong> during the past review cycle. Your efforts have significantly contributed to the achievements of your department and the organization.
    </p>

    <p class="text-justify">
        In recognition of your performance and dedication, the management is pleased to revise your compensation structure. Effective from <strong>{{ $effective_date ?? date('d M, Y') }}</strong>, your monthly gross salary is increased to <strong>₹ {{ number_format((float)($monthly_salary ?? 0), 2) }}</strong> (Rupees <strong>{{ $salary_in_words ?? 'As Agreed' }}</strong> Only).
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #1e3a8a; padding: 12px; margin-top: 15px; margin-bottom: 15px;">
        <strong style="color: #1e3a8a;">Review Performance Summary & Remarks:</strong>
        <p style="margin-top: 8px; margin-bottom: 0; font-size: 12.5px; line-height: 1.6;" class="text-justify">
            {!! nl2br(e($increment_remarks ?? 'Based on your performance review and contribution to the organization, the management is pleased to revise your annual compensation structure. We appreciate your efforts and commitment towards the company.')) !!}
        </p>
    </div>

    <p class="text-justify">
        All other terms and conditions of your employment contract remain unchanged. We hope that you will continue to display the same level of enthusiasm, professionalism, and dedication in your future tasks.
    </p>

    <p class="mt-4">Congratulations on this compensation revision. We wish you a successful and rewarding year ahead.</p>

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
