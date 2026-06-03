@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Certificate of Internship')

@section('styles')
<style>
    .cert-border {
        border: 5px double #1e3a8a;
        padding: 30px;
        background-color: #fcfdfd;
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<div class="cert-border">
    <div class="text-center" style="margin-top: 15px;">
        <h2 style="color: #1e3a8a; font-family: Georgia, serif; font-size: 26px; font-weight: bold; letter-spacing: 2px; margin-bottom: 5px;">CERTIFICATE OF INTERNSHIP</h2>
        <p style="color: #6b7280; font-style: italic; font-size: 13px;">This Certificate is proudly presented to</p>
        
        <h1 style="color: #111827; font-family: Georgia, serif; font-size: 28px; font-weight: bold; margin-top: 20px; margin-bottom: 20px; text-decoration: underline;">
            {{ $employee_name ?? $candidate_name ?? 'Intern Name' }}
        </h1>
        
        <p class="text-justify" style="font-size: 13px; line-height: 1.8; margin-top: 25px; text-align: center;">
            for successfully completing a professional training internship as a <strong>{{ $designation ?? 'Software Development Intern' }}</strong> at <br>
            <strong style="color: #1e3a8a; font-size: 15px;">{{ $company_name ?? branding_name() }}</strong>
        </p>

        <p style="font-size: 13px; font-style: italic; margin-top: 10px; margin-bottom: 30px;">
            The internship was conducted from <strong>{{ $internship_start_date ?? 'Start Date' }}</strong> to <strong>{{ $internship_end_date ?? 'End Date' }}</strong>.
        </p>
    </div>

    <div class="text-justify" style="font-size: 12px; line-height: 1.7; padding: 0 15px;">
        <strong>Work Summary:</strong><br>
        {!! nl2br(e($internship_work_summary ?? 'During the internship, the candidate was trained on core technologies, developed modular product components, participated in product deployment cycles, and collaborated with senior engineering teams.')) !!}
    </div>

    <div class="text-justify" style="font-size: 12px; line-height: 1.7; padding: 0 15px; margin-top: 15px;">
        <strong>Performance Appraisal:</strong><br>
        {!! nl2br(e($performance_summary ?? 'The candidate demonstrated strong learning abilities, analytical problem-solving skills, and deep dedication to all assigned tasks.')) !!}
    </div>

    <div style="margin-top: 50px; width: 100%;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: none;">
                    <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
                </td>
                <td style="width: 50%; border: none; text-align: right;">
                    <strong>For {{ $company_name ?? branding_name() }}</strong>
                    <br><br><br><br>
                    <strong>{{ $signatory_name ?? $authorized_signatory ?? 'Authorized Signatory' }}</strong><br>
                    <span style="font-size: 10px; color: #4b5563;">{{ $signatory_designation ?? 'Head of Human Resources & Training' }}</span>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
