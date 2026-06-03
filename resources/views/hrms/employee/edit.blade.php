@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Edit Employee')

@section('_content')
<style>
    :root {

        --orb-rose: #EC4E74;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .eo-page {
        min-height: calc(100vh - 90px) !important;
        padding: 24px 24px 24px !important;
        background: var(--orb-bg) !important;
    }

    @media (max-width: 991px) {
        .eo-page {
            padding: 18px 18px 110px !important;
        }
    }

    @media (max-width: 575px) {
        .eo-page {
            padding: 12px 12px 110px !important;
        }
    }

    .eo-container {
        max-width: 1280px !important;
        margin: 0 auto !important;
    }

    .eo-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        border: 0 !important;
        border-radius: 22px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, .16) !important;
        padding: 24px 28px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 20px !important;
        margin-bottom: 24px !important;
    }

    .eo-title {
        margin: 0 !important;
        color: #fff !important;
        font-size: 26px !important;
        font-weight: 900 !important;
        letter-spacing: -.5px !important;
    }

    .eo-subtitle {
        margin: 6px 0 0 !important;
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    .eo-code-badge {
        border-radius: 50px !important;
        padding: 8px 16px !important;
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        white-space: nowrap !important;
    }

    .eo-card {
        background: #fff !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        border-radius: 22px !important;
        box-shadow: var(--orb-shadow) !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }

    .eo-card-head {
        padding: 18px 24px !important;
        border-bottom: 1px solid var(--orb-border, #E7EAF3) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        background: #fff !important;
    }

    .eo-section-title {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
    }

    .eo-section-icon {
        width: 40px !important;
        height: 40px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: var(--orb-primary) !important;
        background: #F4F2FF !important;
        font-size: 16px !important;
        flex: 0 0 auto !important;
    }

    .eo-section-title h5 {
        margin: 0 !important;
        color: var(--orb-text, #101828) !important;
        font-size: 16px !important;
        font-weight: 800 !important;
    }

    .eo-section-title p {
        margin: 4px 0 0 0 !important;
        color: var(--orb-muted, #667085) !important;
        font-size: 12px !important;
        font-weight: 500 !important;
    }

    .eo-card-body {
        padding: 24px !important;
    }

    .eo-field {
        margin-bottom: 20px !important;
    }

    .eo-field label {
        display: block !important;
        margin-bottom: 6px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--orb-muted, #667085) !important;
        letter-spacing: .4px !important;
    }

    .required {
        color: var(--orb-rose) !important;
    }

    .form-control,
    .form-select {
        height: 42px !important;
        min-height: 42px !important;
        border-radius: 12px !important;
        border: 1px solid #DDE3EE !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: #111827 !important;
        background: #fff !important;
        box-shadow: none !important;
        outline: none !important;
        transition: all .2s !important;
    }

    .form-control::placeholder {
        color: #98A2B3 !important;
        font-weight: 500 !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--orb-secondary, #8600EE) !important;
        box-shadow: 0 0 0 4px rgba(134, 0, 238, .08) !important;
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: rgba(236, 78, 116, .75) !important;
        box-shadow: 0 0 0 4px rgba(236, 78, 116, .08) !important;
    }

    .invalid-feedback {
        display: block !important;
        margin-top: 5px !important;
        font-size: 11px !important;
        font-weight: 750 !important;
    }

    .form-select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        width: 100% !important;
        cursor: pointer !important;
        padding: 8px 36px 8px 14px !important;
        line-height: 1.4 !important;
        color: #101828 !important;
        background: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%23667085' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat right 14px center #fff !important;
    }

    .form-select:hover {
        border-color: rgba(75, 0, 232, .34) !important;
    }

    .form-select:focus {
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%238600EE' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
    }

    .readonly-field {
        background: #F8F5FF !important;
        border-color: rgba(75, 0, 232, .14) !important;
        color: var(--orb-primary) !important;
        font-weight: 900 !important;
    }

    .disabled-soft {
        background: #F8FAFC !important;
        color: #98A2B3 !important;
        border-color: #EAECF0 !important;
    }

    input[type="date"].form-control {
        color-scheme: light !important;
    }

    input[type="date"].form-control::-webkit-calendar-picker-indicator {
        cursor: pointer !important;
        opacity: .75 !important;
        filter: hue-rotate(245deg) saturate(1.4) !important;
    }

    .small-note {
        margin-top: 6px !important;
        color: #7A8291 !important;
        font-size: 11px !important;
        font-weight: 600 !important;
    }

    .eo-smart-panel {
        display: none;
        margin-top: 10px !important;
        border-radius: 16px !important;
        padding: 18px !important;
        background: #FBFAFF !important;
        border: 1px solid rgba(75, 0, 232, .12) !important;
    }

    .eo-panel-title {
        font-size: 13px !important;
        font-weight: 900 !important;
        color: var(--orb-primary) !important;
        margin-bottom: 14px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .eo-actions-bar {
        position: sticky !important;
        bottom: 10px !important;
        z-index: 100 !important;
        background: rgba(255, 255, 255, .96) !important;
        backdrop-filter: blur(12px) !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        border-radius: 22px !important;
        padding: 14px 20px !important;
        box-shadow: 0 -10px 30px rgba(16, 24, 40, .08) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        margin-top: 30px !important;
        width: 100% !important;
    }

    .eo-actions-note {
        color: var(--orb-muted) !important;
        font-size: 12px !important;
        font-weight: 700 !important;
    }

    .eo-actions {
        display: flex !important;
        gap: 9px !important;
        flex-wrap: wrap !important;
        justify-content: flex-end !important;
    }

    .btn-soft,
    .btn-orb,
    .btn-profile {
        border-radius: 12px !important;
        padding: 9px 16px !important;
        font-size: 13px !important;
        font-weight: 900 !important;
        min-height: 40px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        transition: all .2s !important;
    }

    .btn-soft {
        background: #F4F6FB !important;
        border: 1px solid #E5E7EB !important;
        color: #111827 !important;
    }

    .btn-soft:hover {
        background: #EAEFF7 !important;
        color: #111827 !important;
        border-color: #D1D5DB !important;
    }

    .btn-orb {
        border: 0 !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(75, 0, 232, .16) !important;
    }

    .btn-orb:hover {
        background: linear-gradient(135deg, #3A00B8, #7300D3) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
    }

    .btn-profile {
        border: 0 !important;
        background: linear-gradient(135deg, #EC4E74, #D400D5) !important;
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(212, 0, 213, .14) !important;
    }

    .btn-profile:hover {
        background: linear-gradient(135deg, #D6395F, #BF00C2) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
    }

    .alert {
        border: 0 !important;
        border-radius: 16px !important;
        box-shadow: var(--orb-shadow) !important;
        font-weight: 650 !important;
    }

    .eo-hidden {
        display: none !important;
    }

    @media(max-width:767px) {
        .eo-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            border-radius: 22px !important;
            padding: 20px !important;
        }

        .eo-title {
            font-size: 22px !important;
        }

        .eo-subtitle {
            font-size: 12px !important;
        }

        .eo-code-badge {
            width: 100% !important;
            text-align: center !important;
        }

        .eo-card-head {
            padding: 16px !important;
        }

        .eo-card-body {
            padding: 16px !important;
        }

        .eo-actions-bar {
            position: static !important;
            flex-direction: column !important;
            align-items: stretch !important;
            padding: 16px !important;
        }

        .eo-actions {
            width: 100% !important;
        }

        .eo-actions .btn,
        .eo-actions a,
        .eo-actions button {
            flex: 1 1 100% !important;
            text-align: center !important;
            width: 100% !important;
        }
    }
</style>

@php
$employeeStage = old('derived_employee_stage', $employeeData->employee_stage ?? '');
$employmentType = old('employment_type', $employeeData->employment_type ?? '');
$joiningDate = old('joining_date', $employeeData->joining_date ?? '');
$internshipStart = old('internship_start_date', $employeeData->internship_start_date ?? '');
$internshipEnd = old('internship_end_date', $employeeData->internship_end_date ?? '');
$contractEnd = old('contract_end_date', $employeeData->contract_end_date ?? '');
$probationMonths = old('probation_months', $employeeData->probation_months ?? 3);
@endphp

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header">
            <div>
                <h1 class="eo-title">Edit Employee</h1>
                <p class="eo-subtitle">Update employee account, lifecycle, department, role and salary setup.</p>
            </div>
            <div class="eo-code-badge">Code: {{ $employeeData->employee_code }}</div>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix these errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('hrms.employees.update', $employeeData->id) }}" method="POST" id="employeeEditForm">
            @csrf
            @method('PUT')

            <div class="eo-card">
                <div class="eo-card-head">
                    <div class="eo-section-title">
                        <div class="eo-section-icon"><i class="fas fa-user-edit"></i></div>
                        <div>
                            <h5>Account Details</h5>
                            <p>Login identity and basic contact information.</p>
                        </div>
                    </div>
                </div>

                <div class="eo-card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employee Code</label>
                            <input type="text" class="form-control readonly-field" value="{{ $employeeData->employee_code }}" readonly>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $employeeData->name) }}" placeholder="Enter full name" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $employeeData->email) }}" placeholder="employee@company.com" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Phone <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $employeeData->phone) }}" placeholder="Phone number" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="eo-card">
                <div class="eo-card-head">
                    <div class="eo-section-title">
                        <div class="eo-section-icon"><i class="fas fa-briefcase"></i></div>
                        <div>
                            <h5>Employment Details</h5>
                            <p>Lifecycle stage, department, designation, manager and work setup.</p>
                        </div>
                    </div>
                </div>

                <div class="eo-card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employment Type <span class="required">*</span></label>
                            <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="full_time" {{ $employmentType == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part_time" {{ $employmentType == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="intern" {{ $employmentType == 'intern' ? 'selected' : '' }}>Intern</option>
                                <option value="freelancer" {{ $employmentType == 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                                <option value="contract" {{ $employmentType == 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                            @error('employment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employee Stage</label>
                            <input type="text" id="employee_stage_display" class="form-control readonly-field" value="Auto" readonly>
                            <input type="hidden" id="employee_stage" name="derived_employee_stage" value="{{ $employeeStage }}">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field joining-box">
                            <label>Joining Date <span class="required">*</span></label>
                            <input type="date" name="joining_date" id="joining_date" class="form-control @error('joining_date') is-invalid @enderror"
                                value="{{ $joiningDate }}">
                            @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                            <label>Probation Duration</label>
                            <select name="probation_months" id="probation_months" class="form-select">
                                <option value="3" {{ (string)$probationMonths === '3' ? 'selected' : '' }}>3 Months</option>
                                <option value="6" {{ (string)$probationMonths === '6' ? 'selected' : '' }}>6 Months</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                            <label>Probation End Date</label>
                            <input type="text" id="probation_end_date_display" class="form-control readonly-field" readonly>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Work Mode <span class="required">*</span></label>
                            <select name="work_mode" class="form-select @error('work_mode') is-invalid @enderror" required>
                                <option value="">Select Work Mode</option>
                                <option value="wfo" {{ old('work_mode', $employeeData->work_mode) == 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ old('work_mode', $employeeData->work_mode) == 'wfh' ? 'selected' : '' }}>WFH</option>
                                <option value="hybrid" {{ old('work_mode', $employeeData->work_mode) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                            </select>
                            @error('work_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Work Schedule</label>
                            <select name="work_schedule_type" class="form-select @error('work_schedule_type') is-invalid @enderror">
                                <option value="">Select Schedule</option>
                                <option value="full_day" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'full_day' ? 'selected' : '' }}>Full Day</option>
                                <option value="part_day" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'part_day' ? 'selected' : '' }}>Part Day</option>
                                <option value="hourly" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                <option value="shift_based" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'shift_based' ? 'selected' : '' }}>Shift Based</option>
                            </select>
                            @error('work_schedule_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Department <span class="required">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $employeeData->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Designation <span class="required">*</span></label>
                            <select name="designation_id" id="designation_id" class="form-select @error('designation_id') is-invalid @enderror" required>
                                <option value="">Select Designation</option>
                                @foreach($designations as $designation)
                                <option value="{{ $designation->id }}"
                                    data-department-id="{{ $designation->department_id }}"
                                    {{ old('designation_id', $employeeData->designation_id) == $designation->id ? 'selected' : '' }}>
                                    {{ $designation->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('designation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Reporting Manager</label>
                            <select name="reporting_manager_employee_id" class="form-select @error('reporting_manager_employee_id') is-invalid @enderror">
                                <option value="">Select Manager</option>
                                @foreach($reportingManagers as $manager)
                                <option value="{{ $manager->id }}" {{ old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id) == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} - {{ $manager->employee_code }}
                                </option>
                                @endforeach
                            </select>
                            @error('reporting_manager_employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="intern_box" class="eo-smart-panel">
                        <div class="eo-panel-title">
                            <i class="fas fa-user-graduate"></i> Internship Setup
                        </div>

                        <div class="row">
                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Internship Start Date <span class="required">*</span></label>
                                <input type="date" name="internship_start_date" id="internship_start_date"
                                    class="form-control @error('internship_start_date') is-invalid @enderror"
                                    value="{{ $internshipStart }}">
                                @error('internship_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Internship Duration <span class="required">*</span></label>
                                <select name="internship_duration_months" id="internship_duration_months" class="form-select">
                                    <option value="">Select Duration</option>
                                    <option value="3">3 Months</option>
                                    <option value="6">6 Months</option>
                                    <option value="custom" selected>Custom End Date</option>
                                </select>
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Internship End Date <span class="required">*</span></label>
                                <input type="date" name="internship_end_date" id="internship_end_date"
                                    class="form-control @error('internship_end_date') is-invalid @enderror"
                                    value="{{ $internshipEnd }}">
                                <div class="small-note">Auto calculated for 3/6 months. Select manually if Custom is chosen.</div>
                                @error('internship_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Paid / Unpaid <span class="required">*</span></label>
                                <select name="is_paid_intern" id="is_paid_intern" class="form-select @error('is_paid_intern') is-invalid @enderror">
                                    <option value="">Select</option>
                                    <option value="1" {{ old('is_paid_intern', (string)($employeeData->is_paid_intern ?? '')) === '1' ? 'selected' : '' }}>Paid / Stipend</option>
                                    <option value="0" {{ old('is_paid_intern', (string)($employeeData->is_paid_intern ?? '')) === '0' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                                @error('is_paid_intern') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Duration Summary</label>
                                <input type="text" id="internship_duration_display" class="form-control readonly-field" readonly>
                            </div>
                        </div>
                    </div>

                    <div id="contract_box" class="eo-smart-panel">
                        <div class="eo-panel-title">
                            <i class="fas fa-file-contract"></i> Contract / Freelance Setup
                        </div>

                        <div class="row">
                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Contract End / Review Date</label>
                                <input type="date" name="contract_end_date" id="contract_end_date"
                                    class="form-control @error('contract_end_date') is-invalid @enderror"
                                    value="{{ $contractEnd }}">
                                <div class="small-note">This will be ignored by the controller if the database column does not exist.</div>
                                @error('contract_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="eo-card">
                <div class="eo-card-head">
                    <div class="eo-section-title">
                        <div class="eo-section-icon"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <h5>Access & Salary</h5>
                            <p>Role, active status and salary/stipend history update.</p>
                        </div>
                    </div>
                </div>

                <div class="eo-card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>System Role <span class="required">*</span></label>
                            <select name="system_role_id" class="form-select @error('system_role_id') is-invalid @enderror" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('system_role_id', $employeeData->system_role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name ?? ($role->name ?? ($role->title ?? 'Role '.$role->id)) }}
                                </option>
                                @endforeach
                            </select>
                            @error('system_role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employment Status</label>
                            <select name="employment_status" id="employment_status" class="form-select @error('employment_status') is-invalid @enderror">
                                <option value="active" {{ old('employment_status', $employeeData->employment_status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="resigned" {{ old('employment_status', $employeeData->employment_status ?? '') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                <option value="terminated" {{ old('employment_status', $employeeData->employment_status ?? '') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                <option value="inactive" {{ old('employment_status', $employeeData->employment_status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field" id="relieving_box">
                            <label>Relieving Date</label>
                            <input type="date" name="relieving_date" id="relieving_date"
                                class="form-control @error('relieving_date') is-invalid @enderror"
                                value="{{ old('relieving_date', $employeeData->relieving_date ?? '') }}">
                            <div class="small-note" id="relieving_note">Required when employee is resigned or terminated.</div>
                            @error('relieving_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label id="salary_label">Actual Salary <span class="required">*</span></label>
                            <input type="number" name="actual_salary" id="actual_salary"
                                class="form-control @error('actual_salary') is-invalid @enderror"
                                value="{{ old('actual_salary', $employeeData->actual_salary) }}"
                                min="0" step="1" placeholder="Enter salary">
                            <div class="small-note" id="salary_note">Salary update employee_salary_histories me sync hogi.</div>
                            @error('actual_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Salary Effective From</label>
                            <input type="date" name="salary_effective_from" id="salary_effective_from"
                                class="form-control @error('salary_effective_from') is-invalid @enderror"
                                value="{{ old('salary_effective_from') }}">
                            <div class="small-note" id="salary_effective_note">Salary history effective date.</div>
                            @error('salary_effective_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Salary Reason</label>
                            <input type="text" name="salary_change_reason" id="salary_change_reason"
                                class="form-control @error('salary_change_reason') is-invalid @enderror"
                                value="{{ old('salary_change_reason') }}"
                                placeholder="Salary update / correction">
                            @error('salary_change_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="eo-actions-bar">
                <div class="eo-actions-note">
                    Employee account and HRMS access will be updated.
                </div>

                <div class="eo-actions">
                    <a href="{{ route('hrms.employees.index') }}" class="btn btn-soft">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>

                    <button type="submit" class="btn btn-orb">
                        <i class="fas fa-save"></i> Update Employee
                    </button>

                    @if(Route::has('hrms.employees.profile.complete'))
                    <a href="{{ route('hrms.employees.profile.complete', $employeeData->id) }}" class="btn btn-profile">
                        <i class="fas fa-user-check"></i> Complete Profile
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const department = document.getElementById('department_id');
        const designation = document.getElementById('designation_id');
        const employmentType = document.getElementById('employment_type');
        const employeeStage = document.getElementById('employee_stage');
        const employeeStageDisplay = document.getElementById('employee_stage_display');
        const joiningDate = document.getElementById('joining_date');
        const probationMonths = document.getElementById('probation_months');
        const probationDisplay = document.getElementById('probation_end_date_display');
        const internBox = document.getElementById('intern_box');
        const contractBox = document.getElementById('contract_box');
        const internshipStart = document.getElementById('internship_start_date');
        const internshipEnd = document.getElementById('internship_end_date');
        const internshipDurationMonths = document.getElementById('internship_duration_months');
        const durationDisplay = document.getElementById('internship_duration_display');
        const paidIntern = document.getElementById('is_paid_intern');
        const salary = document.getElementById('actual_salary');
        const salaryLabel = document.getElementById('salary_label');
        const salaryNote = document.getElementById('salary_note');
        const salaryEffectiveFrom = document.getElementById('salary_effective_from');
        const salaryEffectiveNote = document.getElementById('salary_effective_note');
        const salaryReason = document.getElementById('salary_change_reason');
        const employmentStatus = document.getElementById('employment_status');
        const relievingBox = document.getElementById('relieving_box');
        const relievingDate = document.getElementById('relieving_date');
        const relievingNote = document.getElementById('relieving_note');

        let salaryEffectiveTouched = false;
        let employmentTypeChanged = false;

        const stageLabels = {
            internship: 'Internship',
            probation: 'Probation',
            permanent: 'Permanent',
            freelance: 'Freelance',
            contract: 'Contract'
        };

        function formatDateDDMMYYYY(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }

        function formatInputDate(date) {
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${date.getFullYear()}-${month}-${day}`;
        }

        function addMonths(dateString, months) {
            const date = new Date(dateString + 'T00:00:00');
            date.setMonth(date.getMonth() + Number(months) - 1);
            date.setMonth(date.getMonth() + 1);
            date.setDate(0);
            return date;
        }

        function diffDaysInclusive(startValue, endValue) {
            if (!startValue || !endValue) return '';

            const start = new Date(startValue + 'T00:00:00');
            const end = new Date(endValue + 'T00:00:00');
            const diff = end - start;

            if (diff < 0) return 'Invalid date range';

            return Math.floor(diff / (1000 * 60 * 60 * 24)) + 1;
        }

        function filterDesignations() {
            if (!department || !designation) return;

            const deptId = department.value;

            Array.from(designation.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const belongsToDepartment = option.getAttribute('data-department-id') === deptId;
                option.hidden = !belongsToDepartment;
                option.disabled = !belongsToDepartment;
            });

            const selected = designation.options[designation.selectedIndex];

            if (selected && selected.value && selected.disabled) {
                designation.value = '';
            }
        }

        function defaultStageForType() {
            if (!employmentType || !employmentType.value) return '';
            if (employmentType.value === 'intern') return 'internship';
            if (employmentType.value === 'freelancer') return 'freelance';
            if (employmentType.value === 'contract') return 'contract';
            return 'probation';
        }

        function currentStage() {
            let stage = employeeStage.value || defaultStageForType();

            if (employmentTypeChanged) {
                stage = defaultStageForType();
            }

            employeeStage.value = stage;
            employeeStageDisplay.value = stageLabels[stage] || 'Auto';

            return stage;
        }

        function updateProbation() {
            const stage = currentStage();

            if (!joiningDate.value || stage !== 'probation') {
                probationDisplay.value = '';
                return;
            }

            const date = addMonths(joiningDate.value, probationMonths.value || 3);
            probationDisplay.value = formatDateDDMMYYYY(date);
        }

        function updateInternshipEndDate() {
            const duration = internshipDurationMonths.value;

            if (!internshipStart.value) {
                durationDisplay.value = '';
                return;
            }

            if (duration && duration !== 'custom') {
                const endDate = addMonths(internshipStart.value, duration);
                internshipEnd.value = formatInputDate(endDate);
                internshipEnd.setAttribute('readonly', 'readonly');
            } else {
                internshipEnd.removeAttribute('readonly');
            }

            updateInternshipDuration();
        }

        function updateInternshipDuration() {
            const days = diffDaysInclusive(internshipStart.value, internshipEnd.value);

            if (!days) {
                durationDisplay.value = '';
                return;
            }

            durationDisplay.value = days === 'Invalid date range' ? days : days + ' days';
        }

        function disableSalaryEffectiveForUnpaidIntern() {
            if (!salaryEffectiveFrom) return;

            salaryEffectiveFrom.value = '';
            salaryEffectiveFrom.setAttribute('readonly', 'readonly');
            salaryEffectiveFrom.classList.add('disabled-soft');

            if (salaryEffectiveNote) {
                salaryEffectiveNote.innerText = 'Salary effective date is not required for an unpaid internship.';
            }
        }

        function enableSalaryEffective() {
            if (!salaryEffectiveFrom) return;

            salaryEffectiveFrom.removeAttribute('readonly');
            salaryEffectiveFrom.classList.remove('disabled-soft');

            if (salaryEffectiveNote) {
                salaryEffectiveNote.innerText = 'Salary history effective date.';
            }
        }

        function updateSalary() {
            const stage = currentStage();

            if (stage === 'internship') {
                salaryLabel.innerHTML = 'Stipend / Salary <span class="required">*</span>';
                salaryReason.placeholder = 'Initial internship stipend';

                if (paidIntern.value === '0') {
                    salary.value = 0;
                    salary.setAttribute('readonly', 'readonly');
                    salary.classList.add('disabled-soft');
                    salaryNote.innerText = 'Unpaid internship selected, salary locked at 0.';
                    disableSalaryEffectiveForUnpaidIntern();
                    return;
                }

                salary.removeAttribute('readonly');
                salary.classList.remove('disabled-soft');
                enableSalaryEffective();

                if (!salaryEffectiveTouched && internshipStart.value) {
                    salaryEffectiveFrom.value = internshipStart.value;
                }

                salaryNote.innerText = 'Please enter the stipend amount for a paid intern.';
                return;
            }

            salaryLabel.innerHTML = 'Actual Salary <span class="required">*</span>';
            salary.removeAttribute('readonly');
            salary.classList.remove('disabled-soft');
            enableSalaryEffective();
            salaryReason.placeholder = 'Salary update / correction';

            if (!salaryEffectiveTouched && joiningDate.value) {
                salaryEffectiveFrom.value = joiningDate.value;
            }

            salaryNote.innerText = 'Salary update employee_salary_histories me sync hogi.';
        }

        function updateEmploymentFields() {
            const stage = currentStage();

            if (stage === 'internship') {
                internBox.style.display = 'block';
                contractBox.style.display = 'none';
                document.querySelectorAll('.joining-box,.probation-box').forEach(el => el.classList.add('eo-hidden'));
                joiningDate.value = '';
                probationDisplay.value = '';
            } else if (stage === 'contract' || stage === 'freelance') {
                internBox.style.display = 'none';
                contractBox.style.display = 'block';
                document.querySelectorAll('.joining-box').forEach(el => el.classList.remove('eo-hidden'));
                document.querySelectorAll('.probation-box').forEach(el => el.classList.add('eo-hidden'));
                probationDisplay.value = '';
            } else {
                internBox.style.display = 'none';
                contractBox.style.display = 'none';
                document.querySelectorAll('.joining-box,.probation-box').forEach(el => el.classList.remove('eo-hidden'));
                updateProbation();
            }

            updateInternshipEndDate();
            updateSalary();
        }

        function updateRelievingVisibility() {
            if (!employmentStatus || !relievingBox || !relievingDate) return;

            const needsRelieving = employmentStatus.value === 'resigned' || employmentStatus.value === 'terminated';

            relievingBox.style.display = needsRelieving ? 'block' : 'none';

            if (relievingNote) {
                relievingNote.style.display = needsRelieving ? 'block' : 'none';
            }

            if (needsRelieving) {
                relievingDate.setAttribute('required', 'required');
            } else {
                relievingDate.removeAttribute('required');
            }
        }

        salaryEffectiveFrom?.addEventListener('change', function() {
            salaryEffectiveTouched = true;
        });

        department?.addEventListener('change', filterDesignations);

        employmentType?.addEventListener('change', function() {
            employmentTypeChanged = true;
            salaryEffectiveTouched = false;
            salaryEffectiveFrom.value = '';
            updateEmploymentFields();
        });

        joiningDate?.addEventListener('change', function() {
            if (currentStage() !== 'internship') {
                salaryEffectiveTouched = false;
            }

            updateProbation();
            updateSalary();
        });

        probationMonths?.addEventListener('change', updateProbation);

        internshipStart?.addEventListener('change', function() {
            if (currentStage() === 'internship' && paidIntern.value !== '0') {
                salaryEffectiveTouched = false;
            }

            updateInternshipEndDate();
            updateSalary();
        });

        internshipDurationMonths?.addEventListener('change', updateInternshipEndDate);
        internshipEnd?.addEventListener('change', updateInternshipDuration);

        paidIntern?.addEventListener('change', function() {
            salaryEffectiveTouched = false;
            updateSalary();
        });

        employmentStatus?.addEventListener('change', updateRelievingVisibility);

        filterDesignations();
        updateEmploymentFields();
        updateProbation();
        updateInternshipEndDate();
        updateSalary();
        updateRelievingVisibility();
    });
</script>
@endsection