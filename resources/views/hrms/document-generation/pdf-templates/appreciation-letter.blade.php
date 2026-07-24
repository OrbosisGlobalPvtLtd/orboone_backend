@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Letter of Appreciation')

@section('styles')
<style>
    .appreciate-box {
        border-left: 4px solid #16a34a;
        padding: 15px;
        background-color: #f0fdf4;
        margin-top: 15px;
        margin-bottom: 15px;
    }
</style>
@endsection

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>
    
    <div style="clear: both; margin-top: 15px;">
        <strong>To,</strong><br>
        <span style="font-size: 13px; font-weight: bold;">{{ $employee_name ?? 'Employee Name' }}</span><br>
        Employee Code: {{ $employee_code ?? 'EMP' }}<br>
        {{ $designation ?? 'Software Engineer' }} - {{ $department ?? 'Engineering' }}
    </div>

    <div class="text-center mt-4 mb-4">
        <h3 style="text-decoration: underline; letter-spacing: 2px; color: #16a34a; font-weight: bold;">LETTER OF APPRECIATION</h3>
    </div>

    <p>Dear <strong>{{ $employee_first_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        On behalf of the management at <strong>{{ $company_name ?? branding_name() }}</strong>, we are writing to express our sincere appreciation for your outstanding dedication and exceptional performance.
    </p>

    <div class="appreciate-box text-justify">
        <strong style="color: #15803d; font-size: 12px;">{{ $achievement_title ?? 'Exceptional Performance & Project Delivery' }}</strong>
        @if(!empty($performance_period))
        <br><small style="color: #166534;">Performance Period: <strong>{{ $performance_period }}</strong></small>
        @endif
        <p style="margin-top: 8px; margin-bottom: 0;">
            {!! nl2br(e($appreciation_reason ?? $achievement_details ?? 'Your hard work and dedication during our recent product release helped us deliver outstanding results under tight timelines. Your problem-solving abilities and positive mindset have been highly inspiring to your entire team.')) !!}
        </p>
    </div>

    <p class="text-justify">
        It is employees like you who make a real difference and drive our organization toward success. Your continuous efforts, collaborative spirit, and commitment to excellence do not go unnoticed, and we value your contribution greatly.
    </p>

    <p class="mt-4">Thank you once again for your remarkable commitment and excellent work. We look forward to your continued success and growth with us.</p>

    <div class="signature-section signature-block" style="margin-top: 60px;">
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
                    {{ $signatory_designation ?? 'Director / Manager' }}
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
@endsection
