@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Manage Employee')

@section('_content')
<style>
:root{
    --orb-primary:#4B00E8;
    --orb-secondary:#8600EE;
    --orb-bg:#F6F7FB;
    --orb-card:#FFFFFF;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 10px 28px rgba(16,24,40,.06);
}

.em-page{min-height:calc(100vh - 90px);padding:16px 10px 30px;background:var(--orb-bg);}
.em-container{max-width:1320px;margin:0 auto;}

.em-hero,.em-card{
    background:var(--orb-card);
    border:1px solid var(--orb-border);
    border-radius:22px;
    box-shadow:var(--orb-shadow);
}

.em-hero{
    padding:18px;
    margin-bottom:14px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:18px;
}

.em-user{display:flex;align-items:center;gap:14px;min-width:0;}
.em-avatar{
    width:68px;height:68px;border-radius:24px;background:linear-gradient(135deg,#F4F2FF,#EEF2FF);
    color:var(--orb-primary);display:flex;align-items:center;justify-content:center;
    font-size:26px;font-weight:900;overflow:hidden;flex:0 0 auto;border:1px solid #EEE7FF;
}
.em-avatar img{width:100%;height:100%;object-fit:cover;}
.em-title{margin:0;color:var(--orb-text);font-size:25px;font-weight:900;letter-spacing:-.4px;}
.em-sub{margin:5px 0 0;color:var(--orb-muted);font-size:13px;font-weight:750;}

.em-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:9px;}
.em-badge{
    display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 10px;
    background:var(--orb-soft);color:var(--orb-primary);font-size:11px;font-weight:900;text-transform:uppercase;
}
.em-badge-success{background:#DCFCE7;color:#166534;}
.em-badge-warning{background:#FFF4D6;color:#B54708;}
.em-badge-danger{background:#FEE2E2;color:#991B1B;}
.em-badge-info{background:#E0F2FE;color:#0369A1;}

.em-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.em-btn{
    min-height:40px;border-radius:13px;padding:10px 14px;font-size:13px;font-weight:900;
    border:1px solid transparent;display:inline-flex;align-items:center;justify-content:center;gap:8px;
    cursor:pointer;text-decoration:none!important;white-space:nowrap;
}
.em-btn-light{background:#fff;color:var(--orb-text);border-color:var(--orb-border);}
.em-btn-primary{background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary));color:#fff!important;}
.em-btn-success{background:#16A34A;color:#fff!important;display:none;}

.em-layout{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
    align-items:start;
}
.em-card{overflow:hidden;}
.em-card-full{grid-column:1/-1;}

.em-card-head{
    padding:15px 16px;
    border-bottom:1px solid #EEF1F6;
    background:linear-gradient(135deg,#FCFCFD,#F7F4FF);
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
}
.em-card-title{margin:0;color:var(--orb-text);font-size:16px;font-weight:900;}
.em-card-title i{color:var(--orb-primary);}
.em-card-sub{margin-top:3px;color:var(--orb-muted);font-size:12px;font-weight:750;}
.em-card-body{padding:16px;}

.em-section{
    padding:14px;
    border:1px solid #EEF1F6;
    border-radius:18px;
    background:#fff;
    margin-bottom:12px;
}
.em-section:last-child{margin-bottom:0;}

.em-section-title{
    display:flex;align-items:center;gap:8px;
    margin:0 0 12px;
    color:var(--orb-primary);
    font-size:13px;
    font-weight:950;
    text-transform:uppercase;
    letter-spacing:.35px;
}
.em-section-title i{
    width:30px;height:30px;border-radius:11px;background:var(--orb-soft);
    display:inline-flex;align-items:center;justify-content:center;
}

.em-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.em-form-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}

.em-field label{
    display:block;margin:0 0 6px;color:var(--orb-muted);
    font-size:10.5px;font-weight:900;text-transform:uppercase;letter-spacing:.35px;
}
.em-control{
    width:100%;min-height:42px;border-radius:13px;border:1px solid var(--orb-border);
    background:#fff;color:var(--orb-text);font-size:13px;font-weight:800;padding:8px 12px;
}
textarea.em-control{height:92px;resize:vertical;}

.em-control[readonly],
.em-control:disabled{
    background:#fff;color:#344054;opacity:1;pointer-events:none;
}

body.edit-mode .em-control{background:#F9FAFB;}
body.edit-mode .em-control:not([readonly]):not(:disabled):focus{
    outline:none;border-color:rgba(75,0,232,.45);
    box-shadow:0 0 0 4px rgba(75,0,232,.08);background:#fff;
}

.em-file-view{margin-top:8px;font-size:12px;font-weight:900;color:var(--orb-muted);}
.em-file-view a{color:var(--orb-primary);text-decoration:none;}
.em-error{color:#DC2626;font-size:11px;font-weight:800;margin-top:5px;}
.em-hidden{display:none!important;}

.em-doc-box{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:12px;border:1px solid #EEF1F6;border-radius:15px;background:#F8FAFC;
    margin-top:8px;
}
.em-doc-box span{font-size:12px;font-weight:900;color:var(--orb-muted);}
.em-doc-box a{font-size:12px;font-weight:900;color:var(--orb-primary);text-decoration:none;}

@media(max-width:1100px){
    .em-layout{grid-template-columns:1fr;}
    .em-form-grid-3{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:768px){
    .em-hero{flex-direction:column;align-items:flex-start;}
    .em-form-grid,.em-form-grid-3{grid-template-columns:1fr;}
    .em-actions,.em-btn{width:100%;}
}
@media(max-width:575px){
    .em-page{padding:10px 8px 24px;}
    .em-user{align-items:flex-start;}
    .em-title{font-size:21px;}
}
</style>

@php
    $name = $employeeData->name ?? 'Employee';
    $initial = strtoupper(substr($name, 0, 1));
    $isCompleted = (int)($employeeData->is_profile_completed ?? 0) === 1;

    $employmentStatus = strtolower($employeeData->employment_status ?? 'active');
    $profileStatus = strtolower($employeeData->profile_status ?? 'pending');

    $employmentBadgeClass = match($employmentStatus) {
        'active' => 'em-badge-success',
        'resigned' => 'em-badge-warning',
        'terminated' => 'em-badge-danger',
        default => '',
    };

    $profileBadgeClass = match($profileStatus) {
        'approved' => 'em-badge-success',
        'submitted' => 'em-badge-info',
        'rejected' => 'em-badge-danger',
        default => 'em-badge-warning',
    };

    $fileUrl = function($path) {
        return !empty($path) ? route('hrms.documents.file', $path) : '#';
    };
@endphp

<div class="em-page">
    <div class="em-container">

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                <i class="fas fa-exclamation-triangle mr-2"></i>Please fix highlighted errors.
            </div>
        @endif

        <form method="POST" action="{{ route('hrms.employees.manage.update', $employeeData->id) }}" enctype="multipart/form-data" id="employeeManageForm">
            @csrf
            @method('PUT')

            <div class="em-hero">
                <div class="em-user">
                    <div class="em-avatar">
                        @if(!empty($employeeData->profile_image))
                            <img src="{{ $fileUrl($employeeData->profile_image) }}" alt="Profile">
                        @else
                            {{ $initial }}
                        @endif
                    </div>

                    <div>
                        <h1 class="em-title">{{ $employeeData->name ?? 'Employee' }}</h1>
                        <p class="em-sub">
                            {{ $employeeData->employee_code ?? '-' }}
                            · {{ $employeeData->department_name ?? 'No Department' }}
                            · {{ $employeeData->designation_name ?? 'No Designation' }}
                        </p>

                        <div class="em-badges">
                            <span class="em-badge {{ $employmentBadgeClass }}"><i class="fas fa-circle"></i>{{ ucfirst($employmentStatus) }}</span>
                            <span class="em-badge {{ $profileBadgeClass }}"><i class="fas fa-id-card"></i>{{ $isCompleted ? 'Profile Completed' : ucfirst($profileStatus) }}</span>
                            <span class="em-badge"><i class="fas fa-briefcase"></i>{{ strtoupper($employeeData->work_mode ?? '-') }}</span>
                        </div>
                    </div>
                </div>

                <div class="em-actions">
                    <a href="{{ route('hrms.employees.index') }}" class="em-btn em-btn-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="button" class="em-btn em-btn-primary" id="editBtn">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button type="submit" class="em-btn em-btn-success" id="saveBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>

            <div class="em-layout">

                <div class="em-card">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-user-tie mr-2"></i>Onboarding Details</h5>
                            <div class="em-card-sub">Employee basic, job and employment setup</div>
                        </div>
                    </div>

                    <div class="em-card-body">

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-id-badge"></i>Basic Information</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Employee Code</label>
                                    <input type="text" class="em-control" value="{{ $employeeData->employee_code ?? '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Name</label>
                                    <input type="text" name="name" class="em-control editable" value="{{ old('name', $employeeData->name) }}" readonly>
                                    @error('name') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Email</label>
                                    <input type="email" name="email" class="em-control editable" value="{{ old('email', $employeeData->email) }}" readonly>
                                    @error('email') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="em-control editable" value="{{ old('phone', $employeeData->phone) }}" readonly>
                                    @error('phone') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-building"></i>Job Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Department</label>
                                    <select name="department_id" id="department_id" class="em-control editable-select" disabled>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $employeeData->department_id) == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Designation</label>
                                    <select name="designation_id" id="designation_id" class="em-control editable-select" disabled data-selected="{{ old('designation_id', $employeeData->designation_id) }}">
                                        <option value="">Select Designation</option>
                                        @foreach($designations as $des)
                                            <option value="{{ $des->id }}" {{ old('designation_id', $employeeData->designation_id) == $des->id ? 'selected' : '' }}>
                                                {{ $des->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('designation_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>System Role</label>
                                    <select name="system_role_id" class="em-control editable-select" disabled>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('system_role_id', $employeeData->system_role_id) == $role->id ? 'selected' : '' }}>
                                                {{ $role->display_name ?? $role->name ?? $role->title ?? 'Role '.$role->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('system_role_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-briefcase"></i>Employment Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Employment Type</label>
                                    <select name="employment_type" id="employment_type" class="em-control editable-select" disabled>
                                        <option value="">Select Employment Type</option>
                                        <option value="full_time" {{ old('employment_type', $employeeData->employment_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('employment_type', $employeeData->employment_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="intern" {{ old('employment_type', $employeeData->employment_type) == 'intern' ? 'selected' : '' }}>Intern</option>
                                        <option value="freelancer" {{ old('employment_type', $employeeData->employment_type) == 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                                        <option value="contract" {{ old('employment_type', $employeeData->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                    </select>
                                    @error('employment_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Employee Stage</label>
                                    <input type="text" id="employee_stage_display" class="em-control" value="{{ ucfirst(str_replace('_', ' ', old('derived_employee_stage', $employeeData->employee_stage ?? 'Auto'))) }}" readonly>
                                    <input type="hidden" id="employee_stage" name="derived_employee_stage" value="{{ old('derived_employee_stage', $employeeData->employee_stage ?? '') }}">
                                </div>

                                <div class="em-field">
                                    <label>Work Mode</label>
                                    <select name="work_mode" class="em-control editable-select" disabled>
                                        <option value="">Select Work Mode</option>
                                        <option value="wfo" {{ old('work_mode', $employeeData->work_mode) == 'wfo' ? 'selected' : '' }}>WFO</option>
                                        <option value="wfh" {{ old('work_mode', $employeeData->work_mode) == 'wfh' ? 'selected' : '' }}>WFH</option>
                                        <option value="hybrid" {{ old('work_mode', $employeeData->work_mode) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    </select>
                                    @error('work_mode') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Work Schedule</label>
                                    <select name="work_schedule_type" class="em-control editable-select" disabled>
                                        <option value="">Select Schedule</option>
                                        <option value="full_day" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'full_day' ? 'selected' : '' }}>Full Day</option>
                                        <option value="part_day" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'part_day' ? 'selected' : '' }}>Part Day</option>
                                        <option value="hourly" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                        <option value="shift_based" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'shift_based' ? 'selected' : '' }}>Shift Based</option>
                                    </select>
                                    @error('work_schedule_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Employment Status</label>
                                    <select name="employment_status" class="em-control editable-select" disabled>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('employment_status', $employeeData->employment_status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="resigned" {{ old('employment_status', $employeeData->employment_status) == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                        <option value="terminated" {{ old('employment_status', $employeeData->employment_status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                    </select>
                                    @error('employment_status') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field non-intern-field">
                                    <label>Joining Date</label>
                                    <input type="date" name="joining_date" class="em-control editable" value="{{ old('joining_date', $employeeData->joining_date) }}" readonly>
                                    @error('joining_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Relieving Date</label>
                                    <input type="date" name="relieving_date" class="em-control editable" value="{{ old('relieving_date', $employeeData->relieving_date) }}" readonly>
                                    @error('relieving_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Actual Salary</label>
                                    <input type="number" step="0.01" name="actual_salary" class="em-control editable" value="{{ old('actual_salary', $employeeData->actual_salary) }}" readonly>
                                    @error('actual_salary') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Salary Effective From</label>
                                    <input type="date" name="salary_effective_from" class="em-control editable" value="{{ old('salary_effective_from') }}" readonly>
                                    @error('salary_effective_from') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Salary Reason</label>
                                    <input type="text" name="salary_change_reason" class="em-control editable" value="{{ old('salary_change_reason') }}" readonly>
                                    @error('salary_change_reason') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section probation-section">
                            <h6 class="em-section-title"><i class="fas fa-hourglass-half"></i>Probation Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Probation Months</label>
                                    <input type="number" name="probation_months" class="em-control editable" value="{{ old('probation_months', $employeeData->probation_months) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Probation Status</label>
                                    <input type="text" name="probation_status" class="em-control editable" value="{{ old('probation_status', $employeeData->probation_status) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Probation Start</label>
                                    <input type="date" name="probation_start_date" class="em-control editable" value="{{ old('probation_start_date', $employeeData->probation_start_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Probation End</label>
                                    <input type="date" name="probation_end_date" class="em-control editable" value="{{ old('probation_end_date', $employeeData->probation_end_date) }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="em-section internship-section">
                            <h6 class="em-section-title"><i class="fas fa-user-graduate"></i>Internship Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Internship Start</label>
                                    <input type="date" name="internship_start_date" class="em-control editable" value="{{ old('internship_start_date', $employeeData->internship_start_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Internship End</label>
                                    <input type="date" name="internship_end_date" class="em-control editable" value="{{ old('internship_end_date', $employeeData->internship_end_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Paid Intern</label>
                                    <select name="is_paid_intern" class="em-control editable-select" disabled>
                                        <option value="">Select</option>
                                        <option value="1" {{ (string)old('is_paid_intern', $employeeData->is_paid_intern) === '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ (string)old('is_paid_intern', $employeeData->is_paid_intern) === '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="em-card">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-id-card mr-2"></i>Profile Details</h5>
                            <div class="em-card-sub">Personal, education, bank and documents</div>
                        </div>
                    </div>

                    <div class="em-card-body">

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-user"></i>Personal Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="em-control editable" value="{{ old('date_of_birth', $employeeData->date_of_birth) }}" readonly>
                                    @error('date_of_birth') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Gender</label>
                                    <select name="gender" class="em-control editable-select" disabled>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $employeeData->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $employeeData->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $employeeData->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" style="grid-column:1/-1;">
                                    <label>Address</label>
                                    <textarea name="address" class="em-control editable" readonly>{{ old('address', $employeeData->address) }}</textarea>
                                    @error('address') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-graduation-cap"></i>Education & Experience</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Highest Qualification</label>
                                    <input type="text" name="highest_qualification" class="em-control editable" value="{{ old('highest_qualification', $employeeData->highest_qualification) }}" readonly>
                                    @error('highest_qualification') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>CGPA / Percentage</label>
                                    <input type="text" name="cgpa_percentage" class="em-control editable" value="{{ old('cgpa_percentage', $employeeData->cgpa_percentage) }}" readonly>
                                    @error('cgpa_percentage') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Total Experience</label>
                                    <input type="text" name="total_experience" class="em-control editable" value="{{ old('total_experience', $employeeData->total_experience) }}" readonly>
                                    @error('total_experience') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-university"></i>Bank Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Account Holder</label>
                                    <input type="text" name="bank_holder_name" class="em-control editable" value="{{ old('bank_holder_name', $employeeData->bank_holder_name) }}" readonly>
                                    @error('bank_holder_name') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Account Number</label>
                                    <input type="text" name="bank_account_no" class="em-control editable" value="{{ old('bank_account_no', $employeeData->bank_account_no) }}" readonly>
                                    @error('bank_account_no') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Account Type</label>
                                    <select name="bank_account_type" class="em-control editable-select" disabled>
                                        <option value="">Select Account Type</option>
                                        <option value="saving" {{ old('bank_account_type', $employeeData->bank_account_type) == 'saving' ? 'selected' : '' }}>Saving</option>
                                        <option value="savings" {{ old('bank_account_type', $employeeData->bank_account_type) == 'savings' ? 'selected' : '' }}>Savings</option>
                                        <option value="current" {{ old('bank_account_type', $employeeData->bank_account_type) == 'current' ? 'selected' : '' }}>Current</option>
                                        <option value="salary" {{ old('bank_account_type', $employeeData->bank_account_type) == 'salary' ? 'selected' : '' }}>Salary</option>
                                    </select>
                                    @error('bank_account_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>IFSC Code</label>
                                    <input type="text" name="ifsc_code" class="em-control editable" value="{{ old('ifsc_code', $employeeData->ifsc_code) }}" readonly>
                                    @error('ifsc_code') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Bank Branch</label>
                                    <input type="text" name="bank_branch" class="em-control editable" value="{{ old('bank_branch', $employeeData->bank_branch) }}" readonly>
                                    @error('bank_branch') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-file-upload"></i>Documents & Status</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Profile Image</label>
                                    <input type="file" name="profile_image" class="em-control editable-file" disabled>
                                    <div class="em-doc-box">
                                        <span>{{ !empty($employeeData->profile_image) ? 'Image uploaded' : 'No image uploaded' }}</span>
                                        @if(!empty($employeeData->profile_image))
                                            <a href="{{ $fileUrl($employeeData->profile_image) }}" target="_blank">View</a>
                                        @endif
                                    </div>
                                    @error('profile_image') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Resume File</label>
                                    <input type="file" name="resume_file" class="em-control editable-file" disabled>
                                    <div class="em-doc-box">
                                        <span>{{ !empty($employeeData->resume_file) ? 'Resume uploaded' : 'No resume uploaded' }}</span>
                                        @if(!empty($employeeData->resume_file))
                                            <a href="{{ $fileUrl($employeeData->resume_file) }}" target="_blank">View</a>
                                        @endif
                                    </div>
                                    @error('resume_file') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Profile Status</label>
                                    <input type="text" class="em-control" value="{{ ucfirst($employeeData->profile_status ?? 'pending') }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Profile Completed</label>
                                    <input type="text" class="em-control" value="{{ $isCompleted ? 'Yes' : 'No' }}" readonly>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const departmentSelect = document.getElementById('department_id');
    const designationSelect = document.getElementById('designation_id');
    const employmentTypeSelect = document.getElementById('employment_type');
    const employeeStageSelect = document.getElementById('employee_stage');
    const employeeStageDisplay = document.getElementById('employee_stage_display');

    function enableEditMode() {
        document.body.classList.add('edit-mode');

        document.querySelectorAll('.editable').forEach(function(el) {
            el.removeAttribute('readonly');
        });

        document.querySelectorAll('.editable-select, .editable-file').forEach(function(el) {
            el.removeAttribute('disabled');
        });

        editBtn.style.display = 'none';
        saveBtn.style.display = 'inline-flex';
    }

    function toggleEmploymentSections() {
        const type = employmentTypeSelect ? employmentTypeSelect.value : '';
        const stage = employeeStageSelect && employeeStageSelect.value
            ? employeeStageSelect.value
            : (type === 'intern' ? 'internship' : (type === 'freelancer' ? 'freelance' : (type === 'contract' ? 'contract' : 'probation')));

        if (employeeStageDisplay) {
            employeeStageDisplay.value = stage
                ? stage.replace('_', ' ').replace(/\b\w/g, function (char) { return char.toUpperCase(); })
                : 'Auto';
        }

        document.querySelectorAll('.internship-section').forEach(function(el) {
            el.classList.toggle('em-hidden', stage !== 'internship');
        });

        document.querySelectorAll('.probation-section').forEach(function(el) {
            el.classList.toggle('em-hidden', stage === 'internship');
        });

        document.querySelectorAll('.non-intern-field').forEach(function(el) {
            el.classList.toggle('em-hidden', stage === 'internship');
        });
    }

    function loadDesignations(departmentId, selectedId = '') {
        if (!designationSelect) return;

        if (!departmentId) {
            designationSelect.innerHTML = '<option value="">Select Designation</option>';
            return;
        }

        designationSelect.innerHTML = '<option value="">Loading...</option>';

        fetch("{{ url('/hrms/employees/get-designations') }}/" + departmentId)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                designationSelect.innerHTML = '<option value="">Select Designation</option>';

                data.forEach(function(item) {
                    const selected = String(selectedId) === String(item.id) ? 'selected' : '';
                    designationSelect.innerHTML += '<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>';
                });
            })
            .catch(function() {
                designationSelect.innerHTML = '<option value="">Unable to load designations</option>';
            });
    }

    if (editBtn) {
        editBtn.addEventListener('click', enableEditMode);
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', function () {
            loadDesignations(this.value);
        });
    }

    if (employmentTypeSelect) {
        employmentTypeSelect.addEventListener('change', toggleEmploymentSections);
        toggleEmploymentSections();
    }

    if (employeeStageSelect) {
        employeeStageSelect.addEventListener('change', toggleEmploymentSections);
    }

    @if($errors->any())
        enableEditMode();
    @endif
});
</script>
@endsection
