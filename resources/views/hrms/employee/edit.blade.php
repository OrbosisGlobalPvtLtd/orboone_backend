@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Edit Employee')

@section('_content')
<style>
:root{
    --orb-primary:#4B00E8;
    --orb-secondary:#8600EE;
    --orb-rose:#EC4E74;
    --orb-bg:#F6F7FB;
    --orb-card:#FFFFFF;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 10px 28px rgba(16,24,40,.06);
}

.eo-page{
    min-height:calc(100vh - 90px);
    padding:16px 10px 30px;
    background:var(--orb-bg);
}

.eo-container{
    max-width:1280px;
    margin:0 auto;
}

.eo-header{
    background:#fff;
    border:1px solid var(--orb-border);
    border-radius:18px;
    box-shadow:var(--orb-shadow);
    padding:16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    margin-bottom:14px;
}

.eo-title{
    margin:0;
    color:var(--orb-text);
    font-size:24px;
    font-weight:900;
    letter-spacing:-.4px;
}

.eo-subtitle{
    margin:4px 0 0;
    color:var(--orb-muted);
    font-size:13px;
    font-weight:600;
}

.eo-code-badge{
    border-radius:14px;
    padding:10px 14px;
    background:#F4F2FF;
    color:var(--orb-primary);
    border:1px solid rgba(75,0,232,.12);
    font-size:13px;
    font-weight:900;
    white-space:nowrap;
}

.eo-card{
    background:#fff;
    border:1px solid var(--orb-border);
    border-radius:18px;
    box-shadow:var(--orb-shadow);
    overflow:hidden;
    margin-bottom:14px;
}

.eo-card-head{
    padding:14px 16px;
    border-bottom:1px solid #EEF1F6;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    background:#fff;
}

.eo-section-title{
    display:flex;
    align-items:center;
    gap:10px;
}

.eo-section-icon{
    width:36px;
    height:36px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--orb-primary);
    background:var(--orb-soft);
    flex:0 0 auto;
}

.eo-section-title h5{
    margin:0;
    color:var(--orb-text);
    font-size:15px;
    font-weight:900;
}

.eo-section-title p{
    margin:2px 0 0;
    color:var(--orb-muted);
    font-size:12px;
    font-weight:600;
}

.eo-card-body{
    padding:16px;
}

.eo-field{
    margin-bottom:14px;
}

.eo-field label{
    display:block;
    margin-bottom:6px;
    color:#344054;
    font-size:12px;
    font-weight:850;
}

.required{
    color:var(--orb-rose);
}

.form-control,
.form-select{
    min-height:42px;
    border-radius:12px;
    border:1px solid #DDE3EE;
    font-size:13px;
    font-weight:650;
    color:#111827;
    background:#fff;
    box-shadow:none;
}

.form-control::placeholder{
    color:#98A2B3;
    font-weight:500;
}

.form-control:focus,
.form-select:focus{
    border-color:var(--orb-secondary);
    box-shadow:0 0 0 .16rem rgba(134,0,238,.10);
}

.readonly-field{
    background:#F8F5FF!important;
    border-color:rgba(75,0,232,.14)!important;
    color:var(--orb-primary)!important;
    font-weight:900!important;
}

.small-note{
    margin-top:5px;
    color:#7A8291;
    font-size:11px;
    font-weight:600;
}

.eo-intern-panel{
    display:none;
    margin-top:4px;
    border-radius:16px;
    padding:14px;
    background:#FBFAFF;
    border:1px solid rgba(75,0,232,.12);
}

.eo-actions-bar{
    position:sticky;
    bottom:0;
    z-index:30;
    background:rgba(255,255,255,.96);
    backdrop-filter:blur(12px);
    border:1px solid var(--orb-border);
    border-radius:18px;
    padding:12px;
    box-shadow:0 -8px 26px rgba(16,24,40,.08);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.eo-actions-note{
    color:var(--orb-muted);
    font-size:12px;
    font-weight:700;
}

.eo-actions{
    display:flex;
    gap:9px;
    flex-wrap:wrap;
    justify-content:flex-end;
}

.btn-soft,
.btn-orb,
.btn-profile{
    border-radius:12px;
    padding:9px 14px;
    font-size:13px;
    font-weight:900;
    min-height:40px;
}

.btn-soft{
    background:#F4F6FB;
    border:1px solid #E5E7EB;
    color:#111827!important;
}

.btn-orb{
    border:0;
    background:linear-gradient(135deg,#4B00E8,#8600EE);
    color:#fff!important;
    box-shadow:0 8px 18px rgba(75,0,232,.16);
}

.btn-profile{
    border:0;
    background:linear-gradient(135deg,#EC4E74,#D400D5);
    color:#fff!important;
    box-shadow:0 8px 18px rgba(212,0,213,.14);
}

.alert{
    border:0;
    border-radius:16px;
    box-shadow:var(--orb-shadow);
    font-weight:650;
}

@media(max-width:767px){
    .eo-page{
        padding:10px 8px 24px;
    }

    .eo-header{
        flex-direction:column;
        align-items:flex-start;
        border-radius:16px;
        padding:14px;
    }

    .eo-title{
        font-size:21px;
    }

    .eo-subtitle{
        font-size:12px;
    }

    .eo-code-badge{
        width:100%;
        text-align:center;
    }

    .eo-card,
    .eo-actions-bar{
        border-radius:16px;
    }

    .eo-card-head{
        padding:14px;
    }

    .eo-card-body{
        padding:14px;
    }

    .eo-actions-bar{
        flex-direction:column;
        align-items:stretch;
    }

    .eo-actions{
        width:100%;
    }

    .eo-actions .btn,
    .eo-actions a{
        flex:1 1 100%;
        text-align:center;
    }
}
</style>

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header">
            <div>
                <h1 class="eo-title">Edit Employee</h1>
                <p class="eo-subtitle">Update employee account, role, department and salary details.</p>
            </div>

            <div class="eo-code-badge">
                Code: {{ $employeeData->employee_code }}
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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

        <form action="{{ route('hrms.employees.update', $employeeData->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="eo-card">
                <div class="eo-card-head">
                    <div class="eo-section-title">
                        <div class="eo-section-icon"><i class="fas fa-user"></i></div>
                        <div>
                            <h5>Account Details</h5>
                            <p>Login identity and contact information.</p>
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
                            <input type="text" name="name" class="form-control" value="{{ old('name', $employeeData->name) }}" placeholder="Enter full name" required>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $employeeData->email) }}" placeholder="employee@company.com" required>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Phone <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $employeeData->phone) }}" placeholder="Phone number" required>
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
                            <p>Department, designation, manager and work setup.</p>
                        </div>
                    </div>
                </div>

                <div class="eo-card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employment Type <span class="required">*</span></label>
                            <select name="employment_type" id="employment_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="full_time" {{ old('employment_type', $employeeData->employment_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part_time" {{ old('employment_type', $employeeData->employment_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="intern" {{ old('employment_type', $employeeData->employment_type) == 'intern' ? 'selected' : '' }}>Intern</option>
                                <option value="freelancer" {{ old('employment_type', $employeeData->employment_type) == 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                                <option value="contract" {{ old('employment_type', $employeeData->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employee Stage</label>
                            <input type="text" id="employee_stage_display" class="form-control readonly-field" value="{{ ucfirst(str_replace('_', ' ', old('derived_employee_stage', $employeeData->employee_stage ?? 'Auto'))) }}" readonly>
                            <input type="hidden" id="employee_stage" name="derived_employee_stage" value="{{ old('derived_employee_stage', $employeeData->employee_stage ?? '') }}">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field joining-box">
                            <label>Joining Date <span class="required joining-required">*</span></label>
                            <input type="date" name="joining_date" id="joining_date" class="form-control" value="{{ old('joining_date', $employeeData->joining_date) }}">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Probation End Date</label>
                            <input type="text" id="probation_end_date_display" class="form-control readonly-field" value="{{ old('probation_end_date', $employeeData->probation_end_date) }}" readonly>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Work Mode <span class="required">*</span></label>
                            <select name="work_mode" class="form-select" required>
                                <option value="">Select Work Mode</option>
                                <option value="wfo" {{ old('work_mode', $employeeData->work_mode) == 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ old('work_mode', $employeeData->work_mode) == 'wfh' ? 'selected' : '' }}>WFH</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Work Schedule</label>
                            <select name="work_schedule_type" class="form-select">
                                <option value="">Select Schedule</option>
                                <option value="full_day" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'full_day' ? 'selected' : '' }}>Full Day</option>
                                <option value="part_day" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'part_day' ? 'selected' : '' }}>Part Day</option>
                                <option value="hourly" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                <option value="shift_based" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'shift_based' ? 'selected' : '' }}>Shift Based</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Department <span class="required">*</span></label>
                            <select name="department_id" id="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $employeeData->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Designation <span class="required">*</span></label>
                            <select name="designation_id" id="designation_id" class="form-select" required>
                                <option value="">Select Designation</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}"
                                            data-department-id="{{ $designation->department_id }}"
                                            {{ old('designation_id', $employeeData->designation_id) == $designation->id ? 'selected' : '' }}>
                                        {{ $designation->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Reporting Manager</label>
                            <select name="reporting_manager_employee_id" class="form-select">
                                <option value="">Select Manager</option>
                                @foreach($reportingManagers as $manager)
                                    <option value="{{ $manager->id }}" {{ old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id) == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }} - {{ $manager->employee_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="intern_box" class="eo-intern-panel">
                        <div class="row">
                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Internship Start Date <span class="required">*</span></label>
                                <input type="date" name="internship_start_date" id="internship_start_date" class="form-control" value="{{ old('internship_start_date', $employeeData->internship_start_date) }}">
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Internship End Date <span class="required">*</span></label>
                                <input type="date" name="internship_end_date" id="internship_end_date" class="form-control" value="{{ old('internship_end_date', $employeeData->internship_end_date) }}">
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Paid / Unpaid <span class="required">*</span></label>
                                <select name="is_paid_intern" id="is_paid_intern" class="form-select">
                                    <option value="">Select</option>
                                    <option value="1" {{ old('is_paid_intern', (string) $employeeData->is_paid_intern) === '1' ? 'selected' : '' }}>Paid</option>
                                    <option value="0" {{ old('is_paid_intern', (string) $employeeData->is_paid_intern) === '0' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                                <label>Duration</label>
                                <input type="text" id="internship_duration_display" class="form-control readonly-field" readonly>
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
                            <p>Role, status and payroll base salary.</p>
                        </div>
                    </div>
                </div>

                <div class="eo-card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>System Role <span class="required">*</span></label>
                            <select name="system_role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('system_role_id', $employeeData->system_role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Employment Status <span class="required">*</span></label>
                            <select name="employment_status" class="form-select" required>
                                <option value="active" {{ old('employment_status', $employeeData->employment_status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="resigned" {{ old('employment_status', $employeeData->employment_status) == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                <option value="terminated" {{ old('employment_status', $employeeData->employment_status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Relieving Date</label>
                            <input type="date" name="relieving_date" class="form-control" value="{{ old('relieving_date', $employeeData->relieving_date) }}">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Actual Salary <span class="required">*</span></label>
                            <input type="number" name="actual_salary" id="actual_salary" class="form-control" value="{{ old('actual_salary', $employeeData->actual_salary) }}" min="0" step="1" placeholder="Enter salary">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Salary Effective From</label>
                            <input type="date" name="salary_effective_from" class="form-control" value="{{ old('salary_effective_from') }}">
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                            <label>Salary Reason</label>
                            <input type="text" name="salary_change_reason" class="form-control" value="{{ old('salary_change_reason') }}" placeholder="Salary update">
                        </div>
                    </div>
                </div>
            </div>

            <div class="eo-actions-bar">
                <div class="eo-actions-note">
                    Changes will update employee account and HRMS access.
                </div>

                <div class="eo-actions">
                    <a href="{{ route('hrms.employees.index') }}" class="btn btn-soft">Cancel</a>

                    <button type="submit" class="btn btn-orb">
                        <i class="fas fa-save mr-1"></i> Update Employee
                    </button>

                    @if(Route::has('hrms.employees.profile.complete'))
                        <a href="{{ route('hrms.employees.profile.complete', $employeeData->id) }}" class="btn btn-profile">
                            <i class="fas fa-user-check mr-1"></i> Complete Profile
                        </a>
                    @endif
                </div>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const department = document.getElementById('department_id');
    const designation = document.getElementById('designation_id');
    const employmentType = document.getElementById('employment_type');
    const employeeStage = document.getElementById('employee_stage');
    const employeeStageDisplay = document.getElementById('employee_stage_display');
    const joiningDate = document.getElementById('joining_date');
    const probationDisplay = document.getElementById('probation_end_date_display');
    const internBox = document.getElementById('intern_box');
    const internshipStart = document.getElementById('internship_start_date');
    const internshipEnd = document.getElementById('internship_end_date');
    const durationDisplay = document.getElementById('internship_duration_display');
    const paidIntern = document.getElementById('is_paid_intern');
    const salary = document.getElementById('actual_salary');

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
        permanent: 'Permanent',
        freelance: 'Freelance',
        contract: 'Contract'
    };
    let employmentTypeChanged = false;

    function defaultStageForType() {
        if (!employmentType.value) return '';
        if (employmentType.value === 'intern') return 'internship';
        if (employmentType.value === 'freelancer') return 'freelance';
        if (employmentType.value === 'contract') return 'contract';
        return 'probation';
    }

    function currentStage() {
        const stage = employmentTypeChanged
            ? defaultStageForType()
            : (employeeStage.value || defaultStageForType());

        employeeStage.value = stage;
        employeeStageDisplay.value = stageLabels[stage] || 'Auto';

        return stage;
    }

    function updateProbation() {
        if (!joiningDate.value || currentStage() !== 'probation') {
            probationDisplay.value = '';
            return;
        }

        const date = new Date(joiningDate.value);
        date.setMonth(date.getMonth() + 3);
        probationDisplay.value = date.toISOString().slice(0, 10);
    }

    function updateInternshipDuration() {
        if (!internshipStart.value || !internshipEnd.value) {
            durationDisplay.value = '';
            return;
        }

        const start = new Date(internshipStart.value);
        const end = new Date(internshipEnd.value);

        if (end < start) {
            durationDisplay.value = 'Invalid date range';
            return;
        }

        const diffDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
        durationDisplay.value = diffDays + ' days';
    }

    function updateSalary() {
        if (currentStage() === 'internship' && paidIntern.value === '0') {
            salary.value = 0;
            salary.setAttribute('readonly', 'readonly');
        } else {
            salary.removeAttribute('readonly');
        }
    }

    function updateEmploymentFields() {
        if (currentStage() === 'internship') {
            internBox.style.display = 'block';
            probationDisplay.value = '';
        } else {
            internBox.style.display = 'none';
            updateProbation();
        }

        updateSalary();
        updateInternshipDuration();
    }

    department.addEventListener('change', filterDesignations);
    employmentType.addEventListener('change', function () {
        employmentTypeChanged = true;
        updateEmploymentFields();
    });
    employeeStage.addEventListener('change', updateEmploymentFields);
    joiningDate.addEventListener('change', updateProbation);
    internshipStart.addEventListener('change', updateInternshipDuration);
    internshipEnd.addEventListener('change', updateInternshipDuration);
    paidIntern.addEventListener('change', updateSalary);

    filterDesignations();
    updateEmploymentFields();
    updateProbation();
    updateInternshipDuration();
    updateSalary();
});
</script>
@endsection
