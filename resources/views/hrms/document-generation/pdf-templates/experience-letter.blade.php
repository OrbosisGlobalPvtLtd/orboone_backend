@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Experience Certificate')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>
    
    <div style="clear: both; margin-top: 30px;">
        <h2 class="text-center" style="letter-spacing: 2px; color: #1e3a8a; font-weight: bold; margin-bottom: 30px;">TO WHOMSOEVER IT MAY CONCERN</h2>
    </div>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8;">
        This is to certify that <strong>Mr./Ms. {{ $employee_name ?? 'Employee Name' }}</strong> was employed with 
        <strong>{{ $company_name ?? 'Orbosis Global Pvt Ltd' }}</strong> from 
        <strong>{{ $joining_date ?? 'Start Date' }}</strong> to <strong>{{ $relieving_date ?? 'End Date' }}</strong>.
    </p>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8;">
        During their tenure with us, they held the designation of <strong>{{ $designation ?? 'Software Engineer' }}</strong> and performed their duties with utmost dedication and professionalism.
    </p>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8;">
        <strong>Core Roles & Responsibilities:</strong><br>
        {!! nl2br(e($experience_responsibilities ?? 'Responsible for system design, code execution, collaboration with team leads, and quality checks.')) !!}
    </p>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8;">
        <strong>Performance Summary:</strong><br>
        {!! nl2br(e($performance_summary ?? 'Their performance has been highly satisfactory and exemplary. They exhibited excellent problem-solving skills and collaborated well within their team.')) !!}
    </p>

    <p class="text-justify" style="font-size: 13px; line-height: 1.8; margin-top: 25px;">
        We found them to be sincere, hardworking, and extremely honest in their conduct. We would like to take this opportunity to thank them for their contributions and wish them all the success in their future professional endeavors.
    </p>

    <div class="signature-section" style="margin-top: 60px;">
        <table class="signature-table">
            <tr>
                <td>
                    Sincerely,<br>
                    <strong>For {{ $company_name ?? 'Orbosis Global Pvt Ltd' }}</strong>
                    <br><br><br><br>
                    <strong>{{ $signatory_name ?? $authorized_signatory ?? 'Authorized Signatory' }}</strong><br>
                    {{ $signatory_designation ?? 'Manager - Human Resources' }}
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
@endsection
