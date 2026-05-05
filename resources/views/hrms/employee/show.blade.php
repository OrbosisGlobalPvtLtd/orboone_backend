@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Employee Details')

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

.ev-page{min-height:calc(100vh - 90px);padding:16px 10px 30px;background:var(--orb-bg);}
.ev-container{max-width:1280px;margin:0 auto;}

.ev-header{
    background:#fff;border:1px solid var(--orb-border);border-radius:20px;box-shadow:var(--orb-shadow);
    padding:18px;display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:14px;
}
.ev-user{display:flex;align-items:center;gap:14px;}
.ev-avatar{
    width:64px;height:64px;border-radius:22px;background:#F4F2FF;color:var(--orb-primary);
    display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;overflow:hidden;
}
.ev-avatar img{width:100%;height:100%;object-fit:cover;display:block;}
.ev-title{margin:0;color:var(--orb-text);font-size:24px;font-weight:900;}
.ev-sub{margin:4px 0 0;color:var(--orb-muted);font-size:13px;font-weight:700;}
.ev-actions{display:flex;gap:8px;flex-wrap:wrap;}

.ev-btn{
    min-height:40px;border-radius:12px;padding:9px 14px;font-size:13px;font-weight:900;
    display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none!important;border:1px solid var(--orb-border);
    background:#fff;color:var(--orb-text);
}
.ev-btn-primary{border:0;color:#fff!important;background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary));}
.ev-btn-soft{background:var(--orb-soft);color:var(--orb-primary);border-color:#EEE7FF;}

.ev-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.ev-card{
    background:#fff;border:1px solid var(--orb-border);border-radius:20px;box-shadow:var(--orb-shadow);overflow:hidden;
}
.ev-card-head{padding:14px 16px;border-bottom:1px solid #EEF1F6;background:#FCFCFD;}
.ev-card-title{margin:0;color:var(--orb-text);font-size:15px;font-weight:900;}
.ev-card-body{padding:16px;}

.ev-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.ev-item{padding:11px;border:1px solid #EEF1F6;border-radius:14px;background:#fff;}
.ev-label{display:block;color:var(--orb-muted);font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;}
.ev-value{color:var(--orb-text);font-size:13px;font-weight:800;word-break:break-word;}

.ev-pill{
    display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 10px;font-size:11px;font-weight:900;text-transform:uppercase;
}
.ev-pill-active{background:#DCFCE7;color:#166534;}
.ev-pill-resigned{background:#FFF4D6;color:#B54708;}
.ev-pill-terminated{background:#FEE2E2;color:#991B1B;}
.ev-pill-default{background:#F2F4F7;color:#475467;}
.ev-pill-completed{background:#DCFCE7;color:#166534;}
.ev-pill-pending{background:#FFF4D6;color:#B54708;}
.ev-pill-submitted{background:#E0F2FE;color:#0369A1;}
.ev-pill-rejected{background:#FEE2E2;color:#991B1B;}

.ev-full{grid-column:1/-1;}
.ev-docs{display:flex;gap:12px;flex-wrap:wrap;align-items:center;}
.ev-doc{
    min-height:42px;border-radius:13px;padding:10px 13px;background:#F8FAFC;border:1px solid var(--orb-border);
    color:var(--orb-text);font-size:13px;font-weight:900;text-decoration:none!important;display:inline-flex;align-items:center;gap:8px;
}
.ev-doc:hover{background:var(--orb-soft);color:var(--orb-primary);}
.ev-doc-preview{
    width:90px;height:90px;border-radius:16px;overflow:hidden;border:1px solid var(--orb-border);
    background:#F8FAFC;display:inline-flex;align-items:center;justify-content:center;
}
.ev-doc-preview img{width:100%;height:100%;object-fit:cover;display:block;}
.ev-empty{color:var(--orb-muted);font-size:13px;font-weight:700;}

@media(max-width:991px){
    .ev-header{flex-direction:column;align-items:flex-start;}
    .ev-grid{grid-template-columns:1fr;}
}
@media(max-width:575px){
    .ev-page{padding:10px 8px 24px;}
    .ev-user{align-items:flex-start;}
    .ev-title{font-size:20px;}
    .ev-info-grid{grid-template-columns:1fr;}
    .ev-actions,.ev-btn{width:100%;}
}
</style>

@php
    $name = $employeeData->name ?? 'Employee';
    $initial = strtoupper(substr($name, 0, 1));

    $fileUrl = function ($path) {
        if (empty($path)) {
            return '#';
        }

        if (Route::has('hrms.documents.file')) {
            return route('hrms.documents.file', $path);
        }

        if (Route::has('hrms.employee.file')) {
            return route('hrms.employee.file', $path);
        }

        if (Route::has('employee.file')) {
            return route('employee.file', $path);
        }

        return asset('storage/'.$path);
    };

    $employmentStatus = strtolower($employeeData->employment_status ?? 'active');
    $employmentStatusClass = match($employmentStatus) {
        'active' => 'ev-pill-active',
        'resigned' => 'ev-pill-resigned',
        'terminated' => 'ev-pill-terminated',
        default => 'ev-pill-default',
    };

    $profileStatus = strtolower($employeeData->profile_status ?? 'pending');
    $profileStatusClass = match($profileStatus) {
        'approved' => 'ev-pill-completed',
        'submitted' => 'ev-pill-submitted',
        'rejected' => 'ev-pill-rejected',
        default => 'ev-pill-pending',
    };

    $isCompleted = (int)($employeeData->is_profile_completed ?? 0) === 1;

    function evDate($date) {
        return !empty($date) ? \Carbon\Carbon::parse($date)->format('d M Y') : '-';
    }
@endphp

<div class="ev-page">
    <div class="ev-container">

        <div class="ev-header">
            <div class="ev-user">
                <div class="ev-avatar">
                    @if(!empty($employeeData->profile_image))
                        <img src="{{ $fileUrl($employeeData->profile_image) }}" alt="Profile">
                    @else
                        {{ $initial }}
                    @endif
                </div>

                <div>
                    <h1 class="ev-title">{{ $employeeData->name ?? '-' }}</h1>
                    <p class="ev-sub">
                        {{ $employeeData->employee_code ?? '-' }}
                        · {{ $employeeData->designation_name ?? 'No Designation' }}
                        · {{ $employeeData->department_name ?? 'No Department' }}
                    </p>
                    <div class="mt-2">
                        <span class="ev-pill {{ $employmentStatusClass }}">{{ ucfirst($employmentStatus) }}</span>
                        <span class="ev-pill {{ $profileStatusClass }}">{{ $isCompleted ? 'Profile Completed' : ucfirst($profileStatus) }}</span>
                    </div>
                </div>
            </div>

            <div class="ev-actions">
                <a href="{{ route('hrms.employees.index') }}" class="ev-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </a>

                @if(Route::has('hrms.employees.edit'))
                    <a href="{{ route('hrms.employees.edit', $employeeData->id) }}" class="ev-btn ev-btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @endif

                @if(Route::has('hrms.employees.profile.view'))
                    <a href="{{ route('hrms.employees.profile.view', $employeeData->id) }}" class="ev-btn ev-btn-soft">
                        <i class="fas fa-id-card"></i> HRMS Profile
                    </a>
                @endif
            </div>
        </div>

        <div class="ev-grid">

            <div class="ev-card">
                <div class="ev-card-head">
                    <h5 class="ev-card-title">Basic Details</h5>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Employee Code</span><span class="ev-value">{{ $employeeData->employee_code ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Name</span><span class="ev-value">{{ $employeeData->name ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Email</span><span class="ev-value">{{ $employeeData->email ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Phone</span><span class="ev-value">{{ $employeeData->phone ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Date of Birth</span><span class="ev-value">{{ evDate($employeeData->date_of_birth ?? null) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Gender</span><span class="ev-value">{{ !empty($employeeData->gender) ? ucfirst($employeeData->gender) : '-' }}</span></div>
                        <div class="ev-item" style="grid-column:1/-1;"><span class="ev-label">Address</span><span class="ev-value">{{ $employeeData->address ?? '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="ev-card">
                <div class="ev-card-head">
                    <h5 class="ev-card-title">Job Details</h5>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Department</span><span class="ev-value">{{ $employeeData->department_name ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Designation</span><span class="ev-value">{{ $employeeData->designation_name ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">System Role</span><span class="ev-value">{{ $employeeData->role_name ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Reporting Manager</span><span class="ev-value">{{ $employeeData->manager_name ?? '-' }} {{ !empty($employeeData->manager_code) ? '('.$employeeData->manager_code.')' : '' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Employment Type</span><span class="ev-value">{{ ucfirst(str_replace('_', ' ', $employeeData->employment_type ?? '-')) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Employee Stage</span><span class="ev-value">{{ ucfirst(str_replace('_', ' ', $employeeData->employee_stage ?? '-')) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Work Mode</span><span class="ev-value">{{ strtoupper($employeeData->work_mode ?? '-') }}</span></div>
                        <div class="ev-item"><span class="ev-label">Work Schedule</span><span class="ev-value">{{ ucfirst(str_replace('_', ' ', $employeeData->work_schedule_type ?? '-')) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Joining Date</span><span class="ev-value">{{ evDate($employeeData->joining_date ?? null) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Relieving Date</span><span class="ev-value">{{ evDate($employeeData->relieving_date ?? null) }}</span></div>
                    </div>
                </div>
            </div>

            <div class="ev-card">
                <div class="ev-card-head">
                    <h5 class="ev-card-title">Probation / Internship</h5>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Probation Months</span><span class="ev-value">{{ $employeeData->probation_months ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Probation Status</span><span class="ev-value">{{ !empty($employeeData->probation_status) ? ucfirst($employeeData->probation_status) : '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Probation Start</span><span class="ev-value">{{ evDate($employeeData->probation_start_date ?? null) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Probation End</span><span class="ev-value">{{ evDate($employeeData->probation_end_date ?? null) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Internship Start</span><span class="ev-value">{{ evDate($employeeData->internship_start_date ?? null) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Internship End</span><span class="ev-value">{{ evDate($employeeData->internship_end_date ?? null) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Internship Extended To</span><span class="ev-value">{{ evDate($employeeData->internship_extended_to ?? null) }}</span></div>
                        <div class="ev-item"><span class="ev-label">Paid Intern</span><span class="ev-value">{{ isset($employeeData->is_paid_intern) ? ((int)$employeeData->is_paid_intern === 1 ? 'Yes' : 'No') : '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Actual Salary</span><span class="ev-value">{{ !empty($employeeData->actual_salary) ? number_format($employeeData->actual_salary, 2) : '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="ev-card">
                <div class="ev-card-head">
                    <h5 class="ev-card-title">Education & Experience</h5>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Highest Qualification</span><span class="ev-value">{{ $employeeData->highest_qualification ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">CGPA / Percentage</span><span class="ev-value">{{ $employeeData->cgpa_percentage ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Total Experience</span><span class="ev-value">{{ $employeeData->total_experience ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Profile Status</span><span class="ev-value">{{ ucfirst($employeeData->profile_status ?? 'pending') }}</span></div>
                    </div>
                </div>
            </div>

            <div class="ev-card">
                <div class="ev-card-head">
                    <h5 class="ev-card-title">Bank Details</h5>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Account Holder</span><span class="ev-value">{{ $employeeData->bank_holder_name ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Account Number</span><span class="ev-value">{{ $employeeData->bank_account_no ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Account Type</span><span class="ev-value">{{ $employeeData->bank_account_type ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">IFSC Code</span><span class="ev-value">{{ $employeeData->ifsc_code ?? '-' }}</span></div>
                        <div class="ev-item" style="grid-column:1/-1;"><span class="ev-label">Bank Branch</span><span class="ev-value">{{ $employeeData->bank_branch ?? '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="ev-card">
                <div class="ev-card-head">
                    <h5 class="ev-card-title">Profile Completion</h5>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Completed</span><span class="ev-value">{{ $isCompleted ? 'Yes' : 'No' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Completed At</span><span class="ev-value">{{ evDate($employeeData->profile_completed_at ?? null) }}</span></div>
                        <div class="ev-item" style="grid-column:1/-1;"><span class="ev-label">Rejection Reason</span><span class="ev-value">{{ $employeeData->rejection_reason ?? '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="ev-card ev-full">
                <div class="ev-card-head">
                    <h5 class="ev-card-title">Documents</h5>
                </div>
                <div class="ev-card-body">
                    <div class="ev-docs">
                        @if(!empty($employeeData->profile_image))
                            <a href="{{ $fileUrl($employeeData->profile_image) }}" target="_blank" class="ev-doc-preview">
                                <img src="{{ $fileUrl($employeeData->profile_image) }}" alt="Profile Image">
                            </a>

                            <a href="{{ $fileUrl($employeeData->profile_image) }}" target="_blank" class="ev-doc">
                                <i class="fas fa-image"></i> View Profile Image
                            </a>
                        @endif

                        @if(!empty($employeeData->resume_file))
                            <a href="{{ $fileUrl($employeeData->resume_file) }}" target="_blank" class="ev-doc">
                                <i class="fas fa-file-alt"></i> View Resume
                            </a>
                        @endif

                        @if(empty($employeeData->profile_image) && empty($employeeData->resume_file))
                            <div class="ev-empty">No documents uploaded.</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
