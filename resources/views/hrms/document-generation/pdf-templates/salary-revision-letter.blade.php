@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Salary Revision Letter')

@section('content')
<div class="letter-body" style="font-family: 'Times New Roman', Times, serif; color: #0f172a; line-height: 1.35; font-size: 11pt;">
    
    <!-- Top Date Right Aligned -->
    <div style="text-align: right; font-size: 11pt; font-weight: bold; margin-bottom: 6px;">
        Date: {{ !empty($issue_date) ? date('d/m/Y', strtotime($issue_date)) : date('d/m/Y') }}
    </div>
    
    <!-- Main Title -->
    <div style="text-align: center; margin-bottom: 10px;">
        <h2 style="font-size: 16pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; color: #1e3a8a; margin: 0; letter-spacing: 0.5px; font-family: 'Times New Roman', Times, serif;">
            SALARY REVISION LETTER
        </h2>
    </div>

    <!-- Employee Information Header Block -->
    <div style="margin-bottom: 8px; font-size: 11pt; line-height: 1.35;">
        <table style="width: 100%; border-collapse: collapse; border: none; font-family: 'Times New Roman', Times, serif;">
            <tr>
                <td style="width: 160px; font-weight: bold; padding: 1px 0;">Employee Name:</td>
                <td style="padding: 1px 0;">{{ $employee_name ?? 'Employee Name' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Employee ID:</td>
                <td style="padding: 1px 0;">{{ $employee_code ?? 'EMP001' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Designation:</td>
                <td style="padding: 1px 0;">{{ $designation ?? 'Software Engineer' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Department:</td>
                <td style="padding: 1px 0;">{{ $department ?? 'Engineering' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 1px 0;">Salary Revision Date:</td>
                <td style="padding: 1px 0;">{{ !empty($salary_revision_date) ? date('d/m/Y', strtotime($salary_revision_date)) : date('01/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Subject -->
    <div style="text-align: center; margin-top: 4px; margin-bottom: 6px;">
        <h4 style="font-size: 13pt; font-weight: bold; text-decoration: underline; margin: 0; font-family: 'Times New Roman', Times, serif;">
            Subject: Salary Revision
        </h4>
    </div>

    <!-- Salutation -->
    <div style="margin-bottom: 4px; font-size: 11pt;">
        Dear {{ $employee_name ?? 'Employee' }},
    </div>

    <!-- Intro Paragraph -->
    <p style="text-align: justify; margin-bottom: 5px; font-size: 11pt; line-height: 1.35;">
        {!! !empty($intro_clause) ? str_replace('{salary_revision_date}', '<strong>' . (!empty($salary_revision_date) ? date('jS M Y', strtotime($salary_revision_date)) : date('jS M Y')) . '</strong>', $intro_clause) : 'This is to certify that your salary has been revised with effect from <strong>' . (!empty($salary_revision_date) ? date('jS M Y', strtotime($salary_revision_date)) : date('jS M Y')) . '</strong>, in recognition of your performance, contribution, and responsibilities within the organization.' !!}
    </p>

    @php
        $introText = !empty($intro_clause) ? strtolower($intro_clause) : '';
        $hasStructureHeading = str_contains($introText, 'compensation structure');
    @endphp
    @if(!$hasStructureHeading)
    <div style="margin-bottom: 5px; font-weight: 600; font-size: 11pt;">
        Your revised compensation structure is as follows:
    </div>
    @endif

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

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px; border: 1px solid #000; font-size: 10pt; font-family: 'Times New Roman', Times, serif;">
        <thead>
            <tr style="background: #f8fafc;">
                <th style="border: 1px solid #000; padding: 2.5px 6px; text-align: left; font-weight: bold; width: 46%;">Salary Component</th>
                <th style="border: 1px solid #000; padding: 2.5px 6px; text-align: right; font-weight: bold; width: 27%;">Existing Salary (Rs.)</th>
                <th style="border: 1px solid #000; padding: 2.5px 6px; text-align: right; font-weight: bold; width: 27%;">Revised Salary (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="font-weight: bold; background: #fefefe;">
                <td style="border: 1px solid #000; padding: 2px 6px; text-decoration: underline;">Gross Salary</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingGross }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedGross }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; padding-left: 12px;">Basic</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingBasic }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedBasic }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; padding-left: 12px;">HRA</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingHra }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedHra }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; padding-left: 12px;">Special Allowance</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingSpl }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedSpl }}</td>
            </tr>
            <tr style="font-weight: bold; background: #fefefe;">
                <td style="border: 1px solid #000; padding: 2px 6px;">Subtotal (A)</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingSubA }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedSubA }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 6px; padding-left: 12px;">Professional Tax</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingPt }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedPt }}</td>
            </tr>
            <tr style="font-weight: bold; background: #fefefe;">
                <td style="border: 1px solid #000; padding: 2px 6px;">Subtotal (B)</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingSubB }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedSubB }}</td>
            </tr>
            <tr style="font-weight: bold; background: #fefefe;">
                <td style="border: 1px solid #000; padding: 2px 6px;">CTC (A-B)</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingCtc }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedCtc }}</td>
            </tr>
            <tr style="font-weight: bold; background: #fefefe;">
                <td style="border: 1px solid #000; padding: 2px 6px;">Net Pay/ Take Home salary</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtExistingNet }}</td>
                <td style="border: 1px solid #000; padding: 2px 6px; text-align: right;">{{ $fmtRevisedNet }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Highlighted Revised Summary Lines -->
    <div style="margin-bottom: 6px; font-size: 11pt; line-height: 1.35;">
        <div><strong>Revised Monthly Gross Salary:</strong> Rs. {{ $fmtRevisedGross }}</div>
        <div><strong>Revised Annual CTC:</strong> {{ (str_contains(strtolower($revised_annual_ctc_lpa ?? ''), 'rs') || str_contains(strtolower($revised_annual_ctc_lpa ?? ''), '₹')) ? ($revised_annual_ctc_lpa) : ('Rs. ' . ($revised_annual_ctc_lpa ?? '1.86 LPA')) }}</div>
    </div>

    <!-- Applicability Clause -->
    <p style="text-align: justify; margin-bottom: 4px; font-size: 10.5pt; line-height: 1.3;">
        {!! !empty($applicability_clause) ? str_replace('{salary_revision_date}', '<strong>' . (!empty($salary_revision_date) ? date('jS M Y', strtotime($salary_revision_date)) : date('jS M Y')) . '</strong>', $applicability_clause) : 'The revised salary will be applicable from <strong>' . (!empty($salary_revision_date) ? date('jS M Y', strtotime($salary_revision_date)) : date('jS M Y')) . '</strong> and will be subject to applicable company policies, statutory deductions, and the terms of your employment.' !!}
    </p>

    <!-- Unchanged Terms Clause -->
    <p style="text-align: justify; margin-bottom: 4px; font-size: 10.5pt; line-height: 1.3;">
        {{ $unchanged_terms_clause ?? 'All other terms and conditions of your employment will remain unchanged.' }}
    </p>

    <!-- Closing Clause -->
    <p style="text-align: justify; margin-bottom: 10px; font-size: 10.5pt; line-height: 1.3;">
        {{ $closing_clause ?? 'We appreciate your continued efforts and contribution to the organization and look forward to your continued association with us.' }}
    </p>

    <!-- Dual Signatures Section: Company HR Signature (Left) & Candidate Signature (Right) -->
    <div class="signature-section signature-block" style="margin-top: 10px;">
        <table class="signature-table" style="width: 100%; border-collapse: collapse; border: none; font-family: 'Times New Roman', Times, serif;">
            <tr>
                <td style="width: 55%; vertical-align: top; text-align: left; padding: 0;">
                    <strong>For {{ $company_name ?? branding_name() }}</strong>
                    <div style="min-height: 30px; margin-top: 2px; margin-bottom: 2px;">
                        @if(!empty($signature_image))
                            <img src="{{ $signature_image }}" style="height: 35px; width: auto; max-width: 140px; vertical-align: middle;" alt="Signature">
                        @else
                            <div style="height: 25px;"></div>
                        @endif
                    </div>
                    <strong>{{ $hr_manager_name ?? $signatory_name ?? 'Vanshika Dhunna' }}</strong><br>
                    <span style="font-size: 10.5pt; color: #475569;">{{ $signatory_designation ?? 'Human Resource Manager' }}</span><br>
                    <span style="font-size: 10.5pt; color: #475569;">{{ $company_name ?? branding_name() }}</span>
                </td>
                <td style="width: 45%; vertical-align: top; text-align: right; padding: 0;">
                    <strong>Candidate's Signature</strong>
                    <div style="height: 40px;"></div>
                    <strong>{{ $employee_name ?? 'Employee Name' }}</strong>
                </td>
            </tr>
        </table>
    </div>

</div>
@endsection
