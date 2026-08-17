@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Salary Revision Letter')

@section('content')
<div class="letter-body" style="font-family: Arial, Helvetica, sans-serif; color: #0f172a; line-height: 1.6; font-size: 13px;">
    
    <!-- Top Date Right Aligned -->
    <div style="text-align: right; font-size: 13px; font-weight: bold; margin-bottom: 20px;">
        Date: {{ !empty($issue_date) ? date('d/m/Y', strtotime($issue_date)) : date('d/m/Y') }}
    </div>
    
    <!-- Main Title -->
    <div style="text-align: center; margin-bottom: 25px;">
        <h2 style="font-size: 18px; font-weight: bold; text-decoration: underline; text-transform: uppercase; color: #1e3a8a; margin: 0; letter-spacing: 0.5px;">
            SALARY REVISION LETTER
        </h2>
    </div>

    <!-- Employee Information Header Block -->
    <div style="margin-bottom: 20px; font-size: 13px; line-height: 1.7;">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 170px; font-weight: bold; padding: 2px 0;">Employee Name:</td>
                <td style="padding: 2px 0;">{{ $employee_name ?? 'Employee Name' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 2px 0;">Employee ID:</td>
                <td style="padding: 2px 0;">{{ $employee_code ?? 'EMP001' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 2px 0;">Designation:</td>
                <td style="padding: 2px 0;">{{ $designation ?? 'Software Engineer' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 2px 0;">Department:</td>
                <td style="padding: 2px 0;">{{ $department ?? 'Engineering' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 2px 0;">Salary Revision Date:</td>
                <td style="padding: 2px 0;">{{ !empty($salary_revision_date) ? date('d/m/Y', strtotime($salary_revision_date)) : date('01/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Subject -->
    <div style="text-align: center; margin-top: 15px; margin-bottom: 20px;">
        <h4 style="font-size: 14px; font-weight: bold; text-decoration: underline; margin: 0;">
            Subject: Salary Revision
        </h4>
    </div>

    <!-- Salutation -->
    <div style="margin-bottom: 15px; font-size: 13px;">
        Dear {{ $employee_name ?? 'Employee' }},
    </div>

    <!-- Intro Paragraph -->
    <p style="text-align: justify; margin-bottom: 15px; font-size: 13px; line-height: 1.65;">
        {!! !empty($intro_clause) ? str_replace('{salary_revision_date}', '<strong>' . (!empty($salary_revision_date) ? date('jS M Y', strtotime($salary_revision_date)) : date('jS M Y')) . '</strong>', $intro_clause) : 'This is to certify that your salary has been revised with effect from <strong>' . (!empty($salary_revision_date) ? date('jS M Y', strtotime($salary_revision_date)) : date('jS M Y')) . '</strong>, in recognition of your performance, contribution, and responsibilities within the organization.' !!}
    </p>

    <div style="margin-bottom: 12px; font-weight: 500;">
        Your revised compensation structure is as follows:
    </div>

    <!-- Salary Comparison Table -->
    @php
        $fmtExistingGross = is_numeric($existing_gross_salary ?? null) ? number_format((float)$existing_gross_salary) : '10,000';
        $fmtRevisedGross  = is_numeric($revised_gross_salary ?? null) ? number_format((float)$revised_gross_salary) : '12,500';

        $fmtExistingBasic = is_numeric($existing_basic ?? null) ? number_format((float)$existing_basic) : '5,000';
        $fmtRevisedBasic  = is_numeric($revised_basic ?? null) ? number_format((float)$revised_basic) : '6,250';

        $fmtExistingHra   = is_numeric($existing_hra ?? null) ? number_format((float)$existing_hra) : '2,000';
        $fmtRevisedHra    = is_numeric($revised_hra ?? null) ? number_format((float)$revised_hra) : '2,500';

        $fmtExistingSpl   = is_numeric($existing_special_allowance ?? null) ? number_format((float)$existing_special_allowance) : '3,000';
        $fmtRevisedSpl    = is_numeric($revised_special_allowance ?? null) ? number_format((float)$revised_special_allowance) : '3,750';

        $fmtExistingSubA  = is_numeric($existing_subtotal_a ?? null) ? number_format((float)$existing_subtotal_a) : '10,000';
        $fmtRevisedSubA   = is_numeric($revised_subtotal_a ?? null) ? number_format((float)$revised_subtotal_a) : '12,500';

        $fmtExistingPt    = is_numeric($existing_pt ?? null) ? number_format((float)$existing_pt) : '200';
        $fmtRevisedPt     = is_numeric($revised_pt ?? null) ? number_format((float)$revised_pt) : '200';

        $fmtExistingSubB  = is_numeric($existing_subtotal_b ?? null) ? number_format((float)$existing_subtotal_b) : '200';
        $fmtRevisedSubB   = is_numeric($revised_subtotal_b ?? null) ? number_format((float)$revised_subtotal_b) : '200';

        $fmtExistingCtc   = is_numeric($existing_ctc ?? null) ? number_format((float)$existing_ctc) : '10,000';
        $fmtRevisedCtc    = is_numeric($revised_ctc ?? null) ? number_format((float)$revised_ctc) : '12,500';

        $fmtExistingNet   = is_numeric($existing_net_pay ?? null) ? number_format((float)$existing_net_pay) : '9,800';
        $fmtRevisedNet    = is_numeric($revised_net_pay ?? null) ? number_format((float)$revised_net_pay) : '12,300';
    @endphp

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 18px; border: 1px solid #000; font-size: 12.5px;">
        <thead>
            <tr style="background: #f8fafc;">
                <th style="border: 1px solid #000; padding: 6px 10px; text-align: left; font-weight: bold; width: 45%;">Salary Component</th>
                <th style="border: 1px solid #000; padding: 6px 10px; text-align: right; font-weight: bold; width: 27.5%;">Existing Salary (₹)</th>
                <th style="border: 1px solid #000; padding: 6px 10px; text-align: right; font-weight: bold; width: 27.5%;">Revised Salary (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="font-weight: bold;">
                <td style="border: 1px solid #000; padding: 5px 10px; text-decoration: underline;">Gross Salary</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingGross }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedGross }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 10px; padding-left: 15px;">Basic</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingBasic }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedBasic }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 10px; padding-left: 15px;">HRA</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingHra }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedHra }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 10px; padding-left: 15px;">Special Allowance</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingSpl }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedSpl }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="border: 1px solid #000; padding: 5px 10px;">Subtotal (A)</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingSubA }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedSubA }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px 10px; padding-left: 15px;">Professional Tax</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingPt }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedPt }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="border: 1px solid #000; padding: 5px 10px;">Subtotal (B)</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingSubB }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedSubB }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="border: 1px solid #000; padding: 5px 10px;">CTC (A-B)</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingCtc }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedCtc }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="border: 1px solid #000; padding: 5px 10px;">Net Pay/ Take Home salary</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtExistingNet }}</td>
                <td style="border: 1px solid #000; padding: 5px 10px; text-align: right;">{{ $fmtRevisedNet }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Highlighted Revised Summary Lines -->
    <div style="margin-bottom: 18px; font-size: 13px; line-height: 1.7;">
        <div><strong>Revised Monthly Gross Salary:</strong> ₹{{ $fmtRevisedGross }}</div>
        <div><strong>Revised Annual CTC:</strong> ₹{{ $revised_annual_ctc_lpa ?? '1.5 LPA' }}</div>
    </div>

    <!-- Applicability Clause -->
    <p style="text-align: justify; margin-bottom: 12px; font-size: 12.5px; line-height: 1.6;">
        {!! !empty($applicability_clause) ? str_replace('{salary_revision_date}', '<strong>' . (!empty($salary_revision_date) ? date('jS M Y', strtotime($salary_revision_date)) : date('jS M Y')) . '</strong>', $applicability_clause) : 'The revised salary will be applicable from <strong>' . (!empty($salary_revision_date) ? date('jS M Y', strtotime($salary_revision_date)) : date('jS M Y')) . '</strong> and will be subject to applicable company policies, statutory deductions, and the terms of your employment.' !!}
    </p>

    <!-- Unchanged Terms Clause -->
    <p style="text-align: justify; margin-bottom: 12px; font-size: 12.5px; line-height: 1.6;">
        {{ $unchanged_terms_clause ?? 'All other terms and conditions of your employment will remain unchanged.' }}
    </p>

    <!-- Closing Clause -->
    <p style="text-align: justify; margin-bottom: 30px; font-size: 12.5px; line-height: 1.6;">
        {{ $closing_clause ?? 'We appreciate your continued efforts and contribution to the organization and look forward to your continued association with us.' }}
    </p>

    <!-- Dual Signatures Section: Company HR Signature (Left) & Candidate Signature (Right) -->
    <div class="signature-section signature-block" style="margin-top: 35px;">
        <table class="signature-table" style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 55%; vertical-align: top; text-align: left; padding: 0;">
                    <strong>For {{ $company_name ?? branding_name() }}</strong>
                    <div style="min-height: 55px; margin-top: 6px; margin-bottom: 6px;">
                        @if(!empty($signature_image))
                            <img src="{{ $signature_image }}" style="height: 55px; width: auto; max-width: 180px; vertical-align: middle;" alt="Signature">
                        @else
                            <div style="height: 45px;"></div>
                        @endif
                    </div>
                    <strong>{{ $hr_manager_name ?? $signatory_name ?? 'Vanshika Dhunna' }}</strong><br>
                    <span style="font-size: 12px; color: #475569;">{{ $signatory_designation ?? 'Human Resource Manager' }}</span><br>
                    <span style="font-size: 12px; color: #475569;">{{ $company_name ?? branding_name() }}</span>
                </td>
                <td style="width: 45%; vertical-align: top; text-align: right; padding: 0;">
                    <strong>Candidate's Signature</strong>
                    <div style="height: 75px;"></div>
                    <strong>{{ $employee_name ?? 'Employee Name' }}</strong>
                </td>
            </tr>
        </table>
    </div>

</div>
@endsection
