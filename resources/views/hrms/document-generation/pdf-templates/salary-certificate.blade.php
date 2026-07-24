@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Salary Certificate')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>
    
    <div style="clear: both; margin-top: 30px;">
        <h2 class="text-center" style="letter-spacing: 2px; color: #1e3a8a; font-weight: bold; margin-bottom: 30px;">SALARY CERTIFICATE</h2>
    </div>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8;">
        This is to certify that <strong>{{ $employee_prefix ?? 'Mr./Ms.' }} {{ $employee_name ?? 'Employee Name' }}</strong> (Employee Code: <strong>{{ $employee_code ?? 'EMP' }}</strong>) is a permanent employee of 
        <strong>{{ $company_name ?? branding_name() }}</strong>. 
    </p>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8;">
        They are currently employed as a <strong>{{ $designation ?? 'Software Engineer' }}</strong> in the <strong>{{ $department ?? 'Engineering' }}</strong> department.
    </p>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8;">
        Their current compensation details are as follows:
    </p>

    <table class="table" style="margin-top: 20px; margin-bottom: 25px;">
        <thead>
            <tr>
                <th style="width: 60%;">Description</th>
                <th style="width: 40%; text-align: right;">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Monthly Gross Salary</td>
                <td class="text-right" style="font-weight: bold;">INR {{ number_format((float)($monthly_salary ?? $salary ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Annual CTC / Compensation</td>
                <td class="text-right" style="font-weight: bold;">INR {{ number_format((float)($annual_salary ?? ($monthly_salary ?? 0) * 12), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8;">
        <strong>Salary in Words:</strong> Rupees {{ $salary_in_words ?? 'As Declared' }} Only
    </p>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8; margin-top: 20px;">
        <strong>Purpose:</strong> This certificate is issued upon the request of the employee for the purpose of 
        <strong>{{ $purpose ?? 'Address Verification / Loan Application' }}</strong> and carries no financial liability or obligation on the part of the Company.
    </p>

    <div class="signature-section signature-block" style="margin-top: 50px;">
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
                    {{ $signatory_designation ?? 'Human Resource Manager' }}
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
@endsection
