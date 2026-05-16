@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Employee Onboarding')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
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
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .eo-container {
        max-width: 1280px;
        margin: 0 auto;
    }

    .eo-header {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        box-shadow: var(--orb-shadow);
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .eo-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 24px;
        font-weight: 900;
        letter-spacing: -.4px;
    }

    .eo-subtitle {
        margin: 4px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .eo-code-badge {
        border-radius: 14px;
        padding: 10px 14px;
        background: #F4F2FF;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .12);
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .eo-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 14px;
    }

    .eo-card-head {
        padding: 14px 16px;
        border-bottom: 1px solid #EEF1F6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fff;
    }

    .eo-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .eo-section-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--orb-primary);
        background: var(--orb-soft);
        flex: 0 0 auto;
    }

    .eo-section-title h5 {
        margin: 0;
        color: var(--orb-text);
        font-size: 15px;
        font-weight: 900;
    }

    .eo-section-title p {
        margin: 2px 0 0;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .eo-card-body {
        padding: 16px;
    }

    .eo-field {
        margin-bottom: 14px;
    }

    .eo-field label {
        display: block;
        margin-bottom: 6px;
        color: #344054;
        font-size: 12px;
        font-weight: 850;
    }

    .required {
        color: var(--orb-rose);
    }

    .form-control,
    .form-select {
        min-height: 42px;
        border-radius: 12px;
        border: 1px solid #DDE3EE;
        font-size: 13px;
        font-weight: 650;
        color: #111827;
        background: #fff;
        box-shadow: none;
    }

    .form-control::placeholder {
        color: #98A2B3;
        font-weight: 500;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--orb-secondary);
        box-shadow: 0 0 0 .16rem rgba(134, 0, 238, .10);
    }

    .form-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 100%;
        cursor: pointer;
        padding: 9px 48px 9px 13px;
        line-height: 1.35;
        color: #101828;
        background-color: #fff;
        background-image:
            url("data:image/svg+xml,%3Csvg width='18' height='18' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%234B00E8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"),
            linear-gradient(135deg, #F6F2FF 0%, #EEF1FF 100%);
        background-repeat: no-repeat, no-repeat;
        background-position: right 13px center, right 8px center;
        background-size: 18px 18px, 34px 30px;
        transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease, transform .18s ease;
    }

    .form-select:hover {
        border-color: rgba(75, 0, 232, .34);
        background-color: #FCFAFF;
        box-shadow: 0 8px 18px rgba(75, 0, 232, .05);
    }

    .form-select:focus {
        background-image:
            url("data:image/svg+xml,%3Csvg width='18' height='18' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%238600EE' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"),
            linear-gradient(135deg, #F4F2FF 0%, #EEF1FF 100%);
    }

    .form-select:disabled {
        cursor: not-allowed;
        background-color: #F8FAFC;
        color: #98A2B3;
        opacity: 1;
    }

    .form-select option {
        color: #101828;
        background: #fff;
        font-size: 13px;
        font-weight: 700;
        padding: 10px 12px;
    }

    .form-select option:first-child {
        color: #98A2B3;
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


    input[type="date"].form-control,
    input[type="date"].eo-date {
        color-scheme: light;
    }

    input[type="date"].form-control::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: .75;
        filter: hue-rotate(245deg) saturate(1.4);
    }

    input[type="date"].form-control:hover::-webkit-calendar-picker-indicator {
        opacity: 1;
    }

    .small-note {
        margin-top: 5px;
        color: #7A8291;
        font-size: 11px;
        font-weight: 600;
    }

    .eo-smart-panel {
        display: none;
        margin-top: 4px;
        border-radius: 16px;
        padding: 14px;
        background: #FBFAFF;
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .eo-panel-title {
        font-size: 13px;
        font-weight: 900;
        color: var(--orb-primary);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .eo-actions-bar {
        position: sticky;
        bottom: 0;
        z-index: 30;
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(12px);
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 12px;
        box-shadow: 0 -8px 26px rgba(16, 24, 40, .08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .eo-actions-note {
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .eo-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-soft,
    .btn-orb,
    .btn-profile {
        border-radius: 12px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 900;
        min-height: 40px;
    }

    .btn-soft {
        background: #F4F6FB;
        border: 1px solid #E5E7EB;
        color: #111827 !important;
    }

    .btn-orb {
        border: 0;
        background: linear-gradient(135deg, #4B00E8, #8600EE);
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(75, 0, 232, .16);
    }

    .btn-profile {
        border: 0;
        background: linear-gradient(135deg, #EC4E74, #D400D5);
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(212, 0, 213, .14);
    }

    .alert {
        border: 0;
        border-radius: 16px;
        box-shadow: var(--orb-shadow);
        font-weight: 650;
    }

    .eo-hidden {
        display: none !important;
    }

    @media(max-width:767px) {
        .eo-page {
            padding: 10px 8px 24px;
        }

        .eo-header {
            flex-direction: column;
            align-items: flex-start;
            border-radius: 16px;
            padding: 14px;
        }

        .eo-title {
            font-size: 21px;
        }

        .eo-subtitle {
            font-size: 12px;
        }

        .eo-code-badge {
            width: 100%;
            text-align: center;
        }

        .eo-card,
        .eo-actions-bar {
            border-radius: 16px;
        }

        .eo-card-head {
            padding: 14px;
        }

        .eo-card-body {
            padding: 14px;
        }

        .eo-actions-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .eo-actions {
            width: 100%;
        }

        .eo-actions .btn,
        .eo-actions a {
            flex: 1 1 100%;
            text-align: center;
        }
    }
</style>

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header">
            <div>
                <h1 class="eo-title">Employee Onboarding</h1>
                <p class="eo-subtitle">Create employee account, lifecycle, department, role and salary setup.</p>
            </div>
            <div class="eo-code-badge">Code: {{ $nextEmployeeCode }}</div>
        </div>

        @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix these errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('hrms.employees.store') }}" method="POST" id="employeeOnboardingForm">
            @csrf

            <div class="eo-card">
                <div class="eo-card-head">
                    <div class="eo-section-title">
                        <div class="eo-section-icon"><i class="fas fa-user-plus"></i></div>
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
                            <input type="text" class="form-control readonly-field" value="{{ $nextEmployeeCode }}"
                                readonly>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                placeholder="Enter full name" required>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                placeholder="employee@company.com" required>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Phone <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                                placeholder="Phone number" required>
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
                            <select name="employment_type" id="employment_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="full_time" {{ old('employment_type') == 'full_time' ? 'selected' : '' }}>
                                    Full Time</option>
                                <option value="part_time" {{ old('employment_type') == 'part_time' ? 'selected' : '' }}>
                                    Part Time</option>
                                <option value="intern" {{ old('employment_type') == 'intern' ? 'selected' : '' }}>
                                    Intern</option>
                                <option value="freelancer"
                                    {{ old('employment_type') == 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                                <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>
                                    Contract</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employee Stage</label>
                            <input type="text" id="employee_stage_display" class="form-control readonly-field"
                                value="Auto" readonly>
                            <input type="hidden" id="employee_stage" name="derived_employee_stage" value="">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field joining-box">
                            <label>Joining Date <span class="required">*</span></label>
                            <input type="date" name="joining_date" id="joining_date" class="form-control"
                                value="{{ old('joining_date') }}">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                            <label>Probation Duration</label>
                            <select name="probation_months" id="probation_months" class="form-select">
                                <option value="3" {{ old('probation_months', 3) == 3 ? 'selected' : '' }}>3 Months
                                </option>
                                <option value="6" {{ old('probation_months') == 6 ? 'selected' : '' }}>6 Months
                                </option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                            <label>Probation End Date</label>
                            <input type="text" id="probation_end_date_display" class="form-control readonly-field"
                                readonly>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Work Mode <span class="required">*</span></label>
                            <select name="work_mode" class="form-select" required>
                                <option value="">Select Work Mode</option>
                                <option value="wfo" {{ old('work_mode') == 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ old('work_mode') == 'wfh' ? 'selected' : '' }}>WFH</option>
                                <option value="hybrid" {{ old('work_mode') == 'hybrid' ? 'selected' : '' }}>Hybrid
                                </option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Work Schedule</label>
                            <select name="work_schedule_type" class="form-select">
                                <option value="">Select Schedule</option>
                                <option value="full_day"
                                    {{ old('work_schedule_type') == 'full_day' ? 'selected' : '' }}>Full Day</option>
                                <option value="part_day"
                                    {{ old('work_schedule_type') == 'part_day' ? 'selected' : '' }}>Part Day</option>
                                <option value="hourly" {{ old('work_schedule_type') == 'hourly' ? 'selected' : '' }}>
                                    Hourly</option>
                                <option value="shift_based"
                                    {{ old('work_schedule_type') == 'shift_based' ? 'selected' : '' }}>Shift Based
                                </option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Department <span class="required">*</span></label>
                            <select name="department_id" id="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Designation <span class="required">*</span></label>
                            <select name="designation_id" id="designation_id" class="form-select" required>
                                <option value="">Select Designation</option>
                                @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}"
                                    data-department-id="{{ $designation->department_id }}"
                                    {{ old('designation_id') == $designation->id ? 'selected' : '' }}>
                                    {{ $designation->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Reporting Manager</label>
                            <select name="reporting_manager_employee_id" class="form-select">
                                <option value="">Select Manager</option>
                                @foreach ($reportingManagers as $manager)
                                <option value="{{ $manager->id }}"
                                    {{ old('reporting_manager_employee_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} - {{ $manager->employee_code }}
                                </option>
                                @endforeach
                            </select>
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
                                    class="form-control" value="{{ old('internship_start_date') }}">
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Internship Duration <span class="required">*</span></label>
                                <select name="internship_duration_months" id="internship_duration_months"
                                    class="form-select">
                                    <option value="">Select Duration</option>
                                    <option value="3"
                                        {{ old('internship_duration_months', 3) == 3 ? 'selected' : '' }}>3 Months
                                    </option>
                                    <option value="6"
                                        {{ old('internship_duration_months') == 6 ? 'selected' : '' }}>6 Months
                                    </option>
                                    <option value="custom"
                                        {{ old('internship_duration_months') == 'custom' ? 'selected' : '' }}>Custom
                                        End Date</option>
                                </select>
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Internship End Date <span class="required">*</span></label>
                                <input type="date" name="internship_end_date" id="internship_end_date"
                                    class="form-control" value="{{ old('internship_end_date') }}">
                                <div class="small-note">Auto calculated for 3/6 months. Custom me manually select karo.
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Paid / Unpaid <span class="required">*</span></label>
                                <select name="is_paid_intern" id="is_paid_intern" class="form-select">
                                    <option value="">Select</option>
                                    <option value="1" {{ old('is_paid_intern') === '1' ? 'selected' : '' }}>Paid
                                        / Stipend</option>
                                    <option value="0" {{ old('is_paid_intern') === '0' ? 'selected' : '' }}>
                                        Unpaid</option>
                                </select>
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Duration Summary</label>
                                <input type="text" id="internship_duration_display"
                                    class="form-control readonly-field" readonly>
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
                                    class="form-control" value="{{ old('contract_end_date') }}">
                                <div class="small-note">Agar DB column nahi hai to controller me ignore rahega.</div>
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
                            <p>Role, active status and initial salary/stipend history.</p>
                        </div>
                    </div>
                </div>

                <div class="eo-card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>System Role <span class="required">*</span></label>
                            <select name="system_role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ old('system_role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name ?? ($role->name ?? ($role->title ?? 'Role ' . $role->id)) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employment Status</label>
                            <select name="employment_status" class="form-select">
                                <option value="active"
                                    {{ old('employment_status', 'active') == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="resigned"
                                    {{ old('employment_status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                <option value="terminated"
                                    {{ old('employment_status') == 'terminated' ? 'selected' : '' }}>Terminated
                                </option>
                                <option value="inactive"
                                    {{ old('employment_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Relieving Date</label>
                            <input type="date" name="relieving_date" class="form-control"
                                value="{{ old('relieving_date') }}">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label id="salary_label">Actual Salary <span class="required">*</span></label>
                            <input type="number" name="actual_salary" id="actual_salary" class="form-control"
                                value="{{ old('actual_salary') }}" min="0" step="1"
                                placeholder="Enter salary">
                            <div class="small-note" id="salary_note">Initial salary employee_salary_histories me save
                                hogi.</div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Salary Effective From</label>
                            <input type="date" name="salary_effective_from" id="salary_effective_from"
                                class="form-control" value="{{ old('salary_effective_from') }}">
                            <div class="small-note" id="salary_effective_note">Salary history effective date.</div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Salary Reason</label>
                            <input type="text" name="salary_change_reason" id="salary_change_reason"
                                class="form-control" value="{{ old('salary_change_reason') }}"
                                placeholder="Initial salary">
                        </div>
                    </div>
                </div>
            </div>

            <div class="eo-actions-bar">
                <div class="eo-actions-note">
                    Employee profile will start as pending. HR can approve it later.
                </div>

                <div class="eo-actions">
                    <a href="{{ route('hrms.employees.index') }}" class="btn btn-soft">Cancel</a>

                    <button type="submit" name="action" value="save" class="btn btn-orb">
                        <i class="fas fa-save mr-1"></i> Create Employee
                    </button>

                    <button type="submit" name="action" value="save_profile" class="btn btn-profile">
                        <i class="fas fa-user-check mr-1"></i> Create & Complete Profile
                    </button>
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

        let salaryEffectiveTouched = false;

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
            const day = date.getDate();
            date.setMonth(date.getMonth() + Number(months));

            if (date.getDate() !== day) {
                date.setDate(0);
            }
            date.setDate(date.getDate() - 1);
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
            const deptId = department.value;

            Array.from(designation.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = option.getAttribute('data-department-id') !== deptId;
            });

            const selected = designation.options[designation.selectedIndex];
            if (selected && selected.hidden) {
                designation.value = '';
            }
        }

        const stageLabels = {
            internship: 'Internship',
            probation: 'Probation',
            freelance: 'Freelance',
            contract: 'Contract'
        };

        function defaultStageForType() {
            if (!employmentType.value) return '';
            if (employmentType.value === 'intern') return 'internship';
            if (employmentType.value === 'freelancer') return 'freelance';
            if (employmentType.value === 'contract') return 'contract';
            return 'probation';
        }

        function currentStage() {
            const stage = defaultStageForType();
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

            if (days === 'Invalid date range') {
                durationDisplay.value = days;
                return;
            }

            durationDisplay.value = days + ' days';
        }

        function disableSalaryEffectiveForUnpaidIntern() {
            salaryEffectiveFrom.value = '';
            salaryEffectiveFrom.setAttribute('readonly', 'readonly');
            salaryEffectiveFrom.classList.add('disabled-soft');
            salaryEffectiveNote.innerText = 'Unpaid internship me salary effective date required nahi hai.';
        }

        function enableSalaryEffective() {
            salaryEffectiveFrom.removeAttribute('readonly');
            salaryEffectiveFrom.classList.remove('disabled-soft');
            salaryEffectiveNote.innerText = 'Salary history effective date.';
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

                salaryNote.innerText = 'Paid intern ke liye stipend amount enter karo.';
                return;
            }

            salaryLabel.innerHTML = 'Actual Salary <span class="required">*</span>';
            salary.removeAttribute('readonly');
            salary.classList.remove('disabled-soft');
            enableSalaryEffective();
            salaryReason.placeholder = 'Initial salary';

            if (!salaryEffectiveTouched && joiningDate.value) {
                salaryEffectiveFrom.value = joiningDate.value;
            }

            salaryNote.innerText = 'Initial salary employee_salary_histories me save hogi.';
        }

        function updateEmploymentFields() {
            const stage = currentStage();

            if (stage === 'internship') {
                internBox.style.display = 'block';
                contractBox.style.display = 'none';
                document.querySelectorAll('.joining-box,.probation-box').forEach(el => el.classList.add(
                    'eo-hidden'));
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
                document.querySelectorAll('.joining-box,.probation-box').forEach(el => el.classList.remove(
                    'eo-hidden'));
                updateProbation();
            }

            updateInternshipEndDate();
            updateSalary();
        }

        salaryEffectiveFrom.addEventListener('change', function() {
            salaryEffectiveTouched = true;
        });

        department.addEventListener('change', filterDesignations);
        employmentType.addEventListener('change', function() {
            salaryEffectiveTouched = false;
            salaryEffectiveFrom.value = '';
            updateEmploymentFields();
        });

        joiningDate.addEventListener('change', function() {
            if (currentStage() !== 'internship') {
                salaryEffectiveTouched = false;
            }

            updateProbation();
            updateSalary();
        });

        probationMonths.addEventListener('change', updateProbation);

        internshipStart.addEventListener('change', function() {
            if (currentStage() === 'internship' && paidIntern.value !== '0') {
                salaryEffectiveTouched = false;
            }

            updateInternshipEndDate();
            updateSalary();
        });

        internshipDurationMonths.addEventListener('change', updateInternshipEndDate);
        internshipEnd.addEventListener('change', updateInternshipDuration);

        paidIntern.addEventListener('change', function() {
            salaryEffectiveTouched = false;
            updateSalary();
        });

        filterDesignations();
        updateEmploymentFields();
        updateProbation();
        updateInternshipEndDate();
        updateSalary();
    });
</script>
@endsection