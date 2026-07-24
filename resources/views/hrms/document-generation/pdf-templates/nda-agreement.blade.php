@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Non-Disclosure Agreement')

@section('content')
<div class="letter-body">
    <div class="text-center mt-2 mb-4">
        <h2 style="letter-spacing: 1px; color: #1e3a8a; font-weight: bold;">NON-DISCLOSURE & CONFIDENTIALITY AGREEMENT</h2>
    </div>

    <p class="text-justify">
        This Non-Disclosure Agreement (the "Agreement") is entered into and made effective as of 
        <strong>{{ $effective_date ?? $agreement_date ?? $issue_date ?? date('d M, Y') }}</strong> (the "Effective Date"), by and between:
    </p>

    <p style="margin-left: 20px;">
        <strong>1. {{ $company_name ?? branding_name() }}</strong>, a company incorporated under laws of India, having its principal place of business at {{ $company_address ?? 'Corporate Office' }} (hereinafter referred to as the "Disclosing Party" or the "Company").
    </p>

    <p style="margin-left: 20px; margin-top: 5px;">
        <strong>2. Mr./Ms. {{ $party_name ?? $employee_name ?? 'Employee Name' }}</strong>, residing at {{ $party_address ?? $employee_address ?? 'Employee Address' }} (hereinafter referred to as the "Receiving Party" or the "Employee").
    </p>

    <p class="text-justify">
        WHEREAS, the Employee is employed or seeks to be employed by the Company. In connection with such employment, the Company may disclose to the Employee certain proprietary and confidential information.
    </p>

    <p class="text-justify">
        NOW, THEREFORE, in consideration of the employment and mutual covenants herein contained, the parties agree as follows:
    </p>

    <p class="text-justify">
        <strong>1. Confidential Information:</strong> Confidential Information refers to any proprietary data, client lists, software source code, specifications, marketing strategies, financials, designs, or technical parameters shared during employment.
    </p>

    <p class="text-justify">
        <strong>2. Obligations of Employee:</strong> The Employee agrees to keep all Confidential Information strictly secret and confidential. The Employee shall not disclose, copy, duplicate, or share such information with any third party without explicit prior written authorization from the Company management.
    </p>

    <p class="text-justify">
        <strong>3. Term & Survival:</strong> The obligations under this Agreement shall survive during employment and for a period of {{ $confidentiality_period ?? 'five (5) years after the termination of employment' }}.
    </p>

    <p class="text-justify">
        <strong>4. Remedies & Damages:</strong> The Employee acknowledges that any breach of this Agreement would cause irreparable damage to the Company. In the event of a breach, the Company is entitled to seek injunctive relief, legal costs, and damages as per prevailing civil/criminal laws.
    </p>

    <p class="mt-4">IN WITNESS WHEREOF, the parties hereto have executed this Non-Disclosure Agreement as of the Effective Date.</p>

    <div class="signature-section" style="margin-top: 35px;">
        <table class="signature-table">
            <tr>
                <td>
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
                    {{ $signatory_designation ?? 'Director / Manager' }}
                </td>
                <td class="text-right">
                    <strong>Employee Acceptance</strong>
                    <br><br><br><br>
                    ___________________________<br>
                    <strong>{{ $party_name ?? $employee_name ?? 'Employee Signature' }}</strong><br>
                    Receiving Party Signature
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
