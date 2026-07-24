@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Employment Verification Letter')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>

    <div style="clear: both; margin-top: 25px;">
        <strong>To,</strong><br>
        <span style="font-size: 13px; font-weight: bold;">{{ $verification_purpose ?? 'To Whomsoever It May Concern' }}</span>
    </div>

    <div class="text-center mt-4 mb-4">
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a; font-weight: bold;">EMPLOYMENT VERIFICATION LETTER</h3>
    </div>

    <p class="text-justify" style="line-height: 1.8;">
        This letter is to formally confirm and verify the employment details of the following individual with <strong>{{ $company_name ?? branding_name() }}</strong>:
    </p>

    <table class="table" style="margin-top: 20px; margin-bottom: 20px;">
        <tbody>
            <tr>
                <td style="width: 40%; font-weight: bold;">Employee Name</td>
                <td style="width: 60%;">{{ $employee_name ?? 'Employee Name' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Employee Code</td>
                <td>{{ $employee_code ?? 'EMP' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Designation</td>
                <td>{{ $designation ?? 'Designation' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Department</td>
                <td>{{ $department ?? 'Department' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Date of Joining</td>
                <td>{{ $joining_date ?? 'Joining Date' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Employment Status</td>
                <td>Active / Permanent Employee</td>
            </tr>
        </tbody>
    </table>

    <p class="text-justify" style="line-height: 1.8;">
        This certificate is issued upon the request of the employee for verification purposes only (such as loan applications, rental agreements, or visa processing) and does not bind the Company to any financial liability, obligation, or guarantee.
    </p>

    <p class="text-justify" style="line-height: 1.8;">
        Should you require any further information or verification, please feel free to contact the HR department.
    </p>

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
                            <img src="{{ $seal_image }}" style="height: 65px; width: auto; max-width: 120px; position: absolute; top: -5px; left: 50%; margin-left: -60px; vertical-align: middle;" alt="Seal">
                        @endif
                    </div>
                    <strong>{{ $signatory_name ?? $authorized_signatory ?? 'Authorized Signatory' }}</strong><br>
                    {{ $signatory_designation ?? 'Head of Human Resources' }}
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
@endsection
