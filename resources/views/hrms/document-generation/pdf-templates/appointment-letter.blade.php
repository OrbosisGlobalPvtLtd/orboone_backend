@extends('hrms.document-generation.pdf-templates.layouts.document-layout')

@section('title', 'Appointment Letter')

@section('content')
<div class="letter-body">
    <div style="float: right; text-align: right;">
        <strong>Date:</strong> {{ $issue_date ?? $current_date ?? date('d M, Y') }}
    </div>
    
    <div style="clear: both; margin-top: 15px;">
        <strong>To,</strong><br>
        <span style="font-size: 13px; font-weight: bold;">{{ $employee_name ?? 'Employee Name' }}</span><br>
        {{ $employee_address ?? 'Employee Address' }}<br>
        {{ $employee_city ?? '' }}
    </div>

    <div class="text-center mt-4 mb-4">
        <h3 style="text-decoration: underline; letter-spacing: 1px; color: #1e3a8a;">LETTER OF APPOINTMENT</h3>
    </div>

    <p>Dear <strong>{{ $employee_name ?? 'Employee' }}</strong>,</p>

    <p class="text-justify">
        We are pleased to appoint you as <strong>{{ $designation ?? 'Software Engineer' }}</strong> in the <strong>{{ $department ?? 'Engineering' }}</strong> department at 
        <strong>{{ $company_name ?? 'Orbosis Global Pvt Ltd' }}</strong> on the following terms and conditions:
    </p>

    <p class="text-justify">
        <strong>1. Date of Joining:</strong> Your appointment is effective from your date of joining, which is <strong>{{ $joining_date ?? 'To Be Confirmed' }}</strong>.
    </p>

    <p class="text-justify">
        <strong>2. Work Location:</strong> Your current place of work will be <strong>{{ $work_location ?? $office_location ?? 'Corporate Office' }}</strong>. However, the Company reserves the right to transfer you to any other location, office, branch, or subsidiary of the Company as per business requirements.
    </p>

    <p class="text-justify">
        <strong>3. Compensation:</strong> Your monthly gross salary will be <strong>INR {{ number_format((float)($monthly_salary ?? $salary ?? 0), 2) }}</strong> (Rupees {{ $salary_in_words ?? 'As Agreed' }}). All payments will be subject to applicable taxes and standard statutory deductions.
    </p>

    <p class="text-justify">
        <strong>4. Probation and Confirmation:</strong> You will be on probation for a period of <strong>{{ $probation_period ?? '6 Months' }}</strong> from your date of joining. The company may, at its sole discretion, extend or cut short the probation period. Upon satisfactory performance, your employment will be confirmed in writing.
    </p>

    <p class="text-justify">
        <strong>5. Termination & Notice Period:</strong> 
        During the probation period, either party may terminate this agreement by giving <strong>{{ $notice_period_probation ?? '15 Days' }}</strong> notice in writing. 
        Upon confirmation, either party may terminate this agreement by giving <strong>{{ $notice_period_confirmed ?? '30 Days' }}</strong> notice in writing or salary in lieu thereof.
    </p>

    <p class="text-justify">
        <strong>6. Reporting Structure:</strong> You will report to <strong>{{ $reporting_manager_name ?? 'Department Head' }}</strong>, or any other supervisor assigned by the Company management. Your project assignments will be coordinated by <strong>{{ $project_manager_name ?? 'Project Lead' }}</strong>.
    </p>

    <p class="text-justify">
        <strong>7. Code of Conduct:</strong> You are expected to dedicate your whole time and energy to the business affairs of the Company and maintain strict confidentiality regarding all company trade secrets, technical parameters, client information, and proprietary data.
    </p>

    <p class="mt-4">Please sign the duplicate copy of this letter to confirm your acceptance of the terms and conditions outlined above.</p>

    <div class="signature-section" style="margin-top: 50px;">
        <table class="signature-table">
            <tr>
                <td>
                    Sincerely,<br>
                    <strong>For {{ $company_name ?? 'Orbosis Global Pvt Ltd' }}</strong>
                    <br><br><br><br>
                    <strong>{{ $hr_manager_name ?? $authorized_signatory ?? 'Authorized Signatory' }}</strong><br>
                    Human Resources Manager
                </td>
                <td class="text-right">
                    Accepted and Agreed,<br>
                    <br><br><br><br>
                    ___________________________<br>
                    <strong>{{ $employee_name ?? 'Employee Signature' }}</strong><br>
                    Date:
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
