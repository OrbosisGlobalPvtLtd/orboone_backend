@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Employee Details')

@section('_content')
<style>
:root{
    --orb-bg:#F6F7FB;
    --orb-card:#FFFFFF;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 14px 35px rgba(16,24,40,.07);
}

.ev-page {
    min-height: calc(100vh - 90px) !important;
    padding: 24px 24px 30px !important;
    background: var(--orb-bg) !important;
}

.ev-container {
    max-width: 1280px !important;
    margin: 0 auto !important;
}

.ev-header {
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
    color: #ffffff !important;
    border: 0 !important;
    border-radius: 26px !important;
    box-shadow: var(--orb-shadow) !important;
    padding: 24px 28px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 20px !important;
    margin-bottom: 20px !important;
}

.ev-user {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
}

.ev-avatar {
    width: 74px !important;
    height: 74px !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 28px !important;
    font-weight: 900 !important;
    overflow: hidden !important;
    border: 3px solid rgba(255, 255, 255, 0.25) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
}

.ev-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
}

.ev-title {
    margin: 0 !important;
    color: #ffffff !important;
    font-size: 24px !important;
    font-weight: 900 !important;
}

.ev-sub {
    margin: 6px 0 0 !important;
    color: rgba(255, 255, 255, 0.8) !important;
    font-size: 13px !important;
    font-weight: 500 !important;
}

.ev-actions {
    display: flex !important;
    gap: 10px !important;
    flex-wrap: wrap !important;
    align-items: center !important;
}

/* Header action buttons */
.ev-btn-back {
    height: 40px !important;
    min-height: 40px !important;
    border-radius: 50px !important;
    padding: 0 20px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    text-decoration: none !important;
    background: rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    transition: all 0.2s ease-in-out !important;
}

.ev-btn-back:hover {
    background: rgba(255, 255, 255, 0.25) !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    text-decoration: none !important;
}

.ev-btn-edit {
    height: 40px !important;
    min-height: 40px !important;
    border-radius: 50px !important;
    padding: 0 20px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    text-decoration: none !important;
    background: #ffffff !important;
    color: var(--orb-primary) !important;
    border: 0 !important;
    box-shadow: 0 4px 14px rgba(75, 0, 232, 0.2) !important;
    transition: all 0.2s ease-in-out !important;
}

.ev-btn-edit:hover {
    background: #F4F2FF !important;
    color: var(--orb-primary) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 18px rgba(75, 0, 232, 0.3) !important;
    text-decoration: none !important;
}

.ev-btn-profile {
    height: 40px !important;
    min-height: 40px !important;
    border-radius: 50px !important;
    padding: 0 20px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    text-decoration: none !important;
    background: rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    transition: all 0.2s ease-in-out !important;
}

.ev-btn-profile:hover {
    background: rgba(255, 255, 255, 0.25) !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    text-decoration: none !important;
}

/* Glass Status Pills inside header */
.ev-header .ev-pill {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
}

.ev-header .ev-pill-active { background: rgba(22, 163, 74, 0.3) !important; border-color: rgba(22, 163, 74, 0.4) !important; }
.ev-header .ev-pill-inactive { background: rgba(220, 38, 38, 0.3) !important; border-color: rgba(220, 38, 38, 0.4) !important; }
.ev-header .ev-pill-completed { background: rgba(22, 163, 74, 0.3) !important; border-color: rgba(22, 163, 74, 0.4) !important; }
.ev-header .ev-pill-pending { background: rgba(217, 119, 6, 0.3) !important; border-color: rgba(217, 119, 6, 0.4) !important; }
.ev-header .ev-pill-submitted { background: rgba(2, 132, 199, 0.3) !important; border-color: rgba(2, 132, 199, 0.4) !important; }
.ev-header .ev-pill-rejected { background: rgba(220, 38, 38, 0.3) !important; border-color: rgba(220, 38, 38, 0.4) !important; }

.ev-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 20px !important;
}

.ev-card {
    background: #fff !important;
    border: 1px solid var(--orb-border) !important;
    border-radius: 22px !important;
    box-shadow: var(--orb-shadow) !important;
    overflow: hidden !important;
}

.ev-card-head {
    padding: 16px 20px !important;
    border-bottom: 1px solid var(--orb-border) !important;
    background: #fff !important;
}

.ev-card-title-wrap {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
}

.ev-card-icon {
    width: 36px !important;
    height: 36px !important;
    border-radius: 10px !important;
    background: var(--orb-soft) !important;
    color: var(--orb-primary) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 14px !important;
    flex-shrink: 0 !important;
}

.ev-card-title {
    margin: 0 !important;
    color: var(--orb-text) !important;
    font-size: 15px !important;
    font-weight: 800 !important;
}

.ev-card-subtitle {
    display: block !important;
    color: var(--orb-muted) !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    margin-top: 2px !important;
}

.ev-card-body {
    padding: 20px !important;
}

.ev-info-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 12px !important;
}

.ev-item {
    padding: 12px 14px !important;
    border: 1px solid #EEF1F6 !important;
    border-radius: 12px !important;
    background: #FDFDFD !important;
    transition: all .2s !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    min-height: 60px !important;
}

.ev-item:hover {
    background: #fff !important;
    border-color: rgba(75, 0, 232, 0.12) !important;
    box-shadow: 0 4px 12px rgba(16, 24, 40, .02) !important;
}

.ev-label {
    display: block !important;
    color: var(--orb-muted) !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: .5px !important;
    margin-bottom: 4px !important;
}

.ev-value {
    color: var(--orb-text) !important;
    font-size: 13px !important;
    font-weight: 750 !important;
    word-break: break-word !important;
}

.ev-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    border-radius: 50px !important;
    padding: 5px 12px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: .5px !important;
}

.ev-pill-active { background: #DCFCE7 !important; color: #166534 !important; }
.ev-pill-inactive { background: #FDE8E8 !important; color: #9B1C1C !important; }
.ev-pill-resigned { background: #FEE4E2 !important; color: #B42318 !important; }
.ev-pill-terminated { background: #FEE2E2 !important; color: #991B1B !important; }
.ev-pill-default { background: #F2F4F7 !important; color: #475467 !important; }
.ev-pill-completed { background: #DCFCE7 !important; color: #166534 !important; }
.ev-pill-pending { background: #FFF4D6 !important; color: #B54708 !important; }
.ev-pill-submitted { background: #E0F2FE !important; color: #0369A1 !important; }
.ev-pill-rejected { background: #FEE2E2 !important; color: #991B1B !important; }

.ev-full { grid-column: 1/-1 !important; }

/* Table layout for documents */
.ev-table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
}

.ev-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 0 !important;
    font-size: 13px !important;
    min-width: 800px !important;
}

.ev-table th {
    background: #FAFBFC !important;
    color: var(--orb-muted) !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: .5px !important;
    padding: 14px 20px !important;
    border-bottom: 1px solid var(--orb-border) !important;
    text-align: left !important;
}

.ev-table td {
    padding: 14px 20px !important;
    border-bottom: 1px solid var(--orb-border) !important;
    color: var(--orb-text) !important;
    vertical-align: middle !important;
}

.ev-table tr:last-child td {
    border-bottom: 0 !important;
}

.ev-table tr:hover td {
    background: #FAF8FF !important;
}

.ev-badge-req {
    background: #FFF0F0 !important;
    color: #E02424 !important;
    border: 1px solid #FBD5D5 !important;
    border-radius: 6px !important;
    padding: 3px 8px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
}

.ev-badge-opt {
    background: #F0F5FF !important;
    color: #1A56DB !important;
    border: 1px solid #C3DDFD !important;
    border-radius: 6px !important;
    padding: 3px 8px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
}

.ev-doc-type-cell {
    display: flex !important;
    flex-direction: column !important;
}

.ev-doc-type-name {
    font-weight: 800 !important;
    color: var(--orb-text) !important;
}

.ev-doc-type-code {
    font-size: 11px !important;
    color: var(--orb-muted) !important;
    margin-top: 2px !important;
}

.ev-file-info {
    display: flex !important;
    flex-direction: column !important;
}

.ev-file-name {
    font-weight: 750 !important;
    color: var(--orb-text) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.ev-file-name i {
    color: var(--orb-primary) !important;
}

.ev-file-date {
    font-size: 11px !important;
    color: var(--orb-muted) !important;
    margin-top: 2px !important;
}

.ev-verifier-info, .ev-rejection-info {
    display: flex !important;
    flex-direction: column !important;
}

.ev-verifier-name {
    font-weight: 750 !important;
    color: #166534 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
}

.ev-verifier-date {
    font-size: 11px !important;
    color: var(--orb-muted) !important;
    margin-top: 2px !important;
}

.ev-rejection-label {
    font-weight: 750 !important;
    color: #991B1B !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
}

.ev-rejection-reason {
    font-size: 11px !important;
    color: var(--orb-muted) !important;
    margin-top: 2px !important;
}

.ev-doc-actions {
    display: inline-flex !important;
    gap: 6px !important;
}

.ev-btn-icon {
    min-height: 30px !important;
    border-radius: 8px !important;
    padding: 5px 12px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    text-decoration: none !important;
    border: 1px solid var(--orb-primary) !important;
    background: var(--orb-primary) !important;
    color: #fff !important;
    transition: all .2s !important;
}

.ev-btn-icon:hover {
    background: #3A00B8 !important;
    border-color: #3A00B8 !important;
    color: #fff !important;
}

.ev-btn-icon-soft {
    background: var(--orb-soft) !important;
    border-color: rgba(75, 0, 232, 0.12) !important;
    color: var(--orb-primary) !important;
}

.ev-btn-icon-soft:hover {
    background: rgba(75, 0, 232, 0.1) !important;
    color: var(--orb-primary) !important;
    border-color: rgba(75, 0, 232, 0.2) !important;
}

.ev-empty {
    color: var(--orb-muted) !important;
    font-size: 13px !important;
    font-weight: 700 !important;
}

@media (max-width: 991px) {
    .ev-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        padding: 20px !important;
    }
    .ev-grid {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
    }
    .ev-actions {
        width: 100% !important;
        margin-top: 12px !important;
    }
    .ev-actions .ev-btn {
        flex: 1 1 auto !important;
    }
}

@media (max-width: 575px) {
    .ev-page {
        padding: 12px 12px 24px !important;
    }
    .ev-user {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 12px !important;
    }
    .ev-avatar {
        width: 70px !important;
        height: 70px !important;
    }
    .ev-info-grid {
        grid-template-columns: 1fr !important;
    }
    .ev-actions {
        flex-direction: column !important;
    }
    .ev-actions .ev-btn {
        width: 100% !important;
    }
}
</style>

@php
    $salaryHistories = $salaryHistories ?? collect();

    $user = auth()->user();
    $canSeeSalary = false;
    if ($user) {
        $canSeeSalary = $user->hasRole('super_admin') 
            || $user->hasRole('Super Admin') 
            || $user->hasRole('hr_admin') 
            || $user->hasRole('hr') 
            || $user->hasRole('admin') 
            || $user->hasRole('finance_admin')
            || $user->can('hrms.employees.salary')
            || $user->can('employees.salary')
            || $user->can('salary.view')
            || $user->can('payroll.view')
            || $user->can('employees.edit')
            || $user->can('hrms.employees.edit');
    }

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
            return route('hrms.documents.file', ['path' => $path]);
        }

        if (Route::has('employee.file')) {
            return route('hrms.documents.file', ['path' => $path]);
        }

        return route('hrms.documents.file', ['path' => $path]);
    };

    $employmentStatus = strtolower($employeeData->employment_status ?? 'active');
    $isActive = (int)($employeeData->is_active ?? 1) === 1;

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
                @php
                    $passportPhotoUrl = resolveEmployeePassportPhoto($employeeData);
                    $employeeInitial = $initial;
                    $employeeName = $employeeData->name ?? 'Employee';
                @endphp
                <span class="hrms-emp-avatar mr-3">
                    @if($passportPhotoUrl)
                        <img
                            src="{{ $passportPhotoUrl }}"
                            alt="{{ $employeeName }}"
                            class="hrms-emp-avatar-img"
                            onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');"
                        >
                        <span class="hrms-emp-avatar-fallback is-hidden">
                            {{ $employeeInitial }}
                        </span>
                    @else
                        <span class="hrms-emp-avatar-fallback is-visible">
                            {{ $employeeInitial }}
                        </span>
                    @endif
                </span>

                <div>
                    <h1 class="ev-title">{{ $employeeData->name ?? '-' }}</h1>
                    <p class="ev-sub">
                        {{ $employeeData->employee_code ?? '-' }}
                        · {{ $employeeData->designation_name ?? 'No Designation' }}
                        · {{ $employeeData->department_name ?? 'No Department' }}
                    </p>
                    <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                        <span class="ev-pill {{ $isActive ? 'ev-pill-active' : 'ev-pill-inactive' }}">
                            {{ $isActive ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="ev-pill {{ $profileStatusClass }}">
                            {{ $isCompleted ? 'Profile Completed' : ucfirst($profileStatus) }}
                        </span>
                        @if(!empty($employeeData->employee_stage))
                            <span class="ev-pill ev-pill-default">
                                {{ ucfirst(str_replace('_', ' ', $employeeData->employee_stage)) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ev-actions">
                <a href="{{ route('hrms.employees.index') }}" class="ev-btn-back">
                    <i class="fas fa-arrow-left"></i> Back
                </a>

                @if(Route::has('hrms.employees.edit'))
                    <a href="{{ route('hrms.employees.edit', $employeeData->employee_id ?? $employeeData->id) }}" class="ev-btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @endif

                @if(Route::has('hrms.employees.profile.view'))
                    <a href="{{ route('hrms.employees.profile.view', $employeeData->employee_id ?? $employeeData->id) }}" class="ev-btn-profile">
                        <i class="fas fa-id-card"></i> HRMS Profile
                    </a>
                @endif
            </div>
        </div>

        <div class="ev-grid">

            <!-- Section A: Basic Details -->
            <div class="ev-card">
                <div class="ev-card-head">
                    <div class="ev-card-title-wrap">
                        <span class="ev-card-icon"><i class="fas fa-user"></i></span>
                        <div>
                            <h5 class="ev-card-title">Basic Details</h5>
                            <span class="ev-card-subtitle">Personal identification & contact details</span>
                        </div>
                    </div>
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

            <!-- Section B: Job Assignment -->
            <div class="ev-card">
                <div class="ev-card-head">
                    <div class="ev-card-title-wrap">
                        <span class="ev-card-icon"><i class="fas fa-briefcase"></i></span>
                        <div>
                            <h5 class="ev-card-title">Job Assignment</h5>
                            <span class="ev-card-subtitle">Employment mapping & structural hierarchy</span>
                        </div>
                    </div>
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

            <!-- Section C: Lifecycle Details (Conditional) -->
            @php
                $hasInternship = !empty($employeeData->internship_start_date) || !empty($employeeData->internship_end_date) || ($employeeData->employee_stage ?? '') === 'internship';
                $hasProbation = !empty($employeeData->probation_start_date) || !empty($employeeData->probation_end_date) || ($employeeData->employee_stage ?? '') === 'probation';
                $hasConfirmation = ($employeeData->employee_stage ?? '') === 'permanent' || !empty($employeeData->permanent_at) || in_array($employeeData->probation_status ?? '', ['completed', 'confirmed']);
                $hasExit = $employmentStatus !== 'active' || !empty($employeeData->relieving_date) || !$isActive;
            @endphp

            @if($hasInternship || $hasProbation || $hasConfirmation || $hasExit)
                <div class="ev-card ev-full">
                    <div class="ev-card-head">
                        <div class="ev-card-title-wrap">
                            <span class="ev-card-icon"><i class="fas fa-history"></i></span>
                            <div>
                                <h5 class="ev-card-title">Lifecycle Details</h5>
                                <span class="ev-card-subtitle">Lifecycle stages, progress tracking & timelines</span>
                            </div>
                        </div>
                    </div>
                    <div class="ev-card-body">
                        <div class="ev-info-grid">
                            @if($hasInternship)
                                <div class="ev-item" style="border-left: 4px solid #3B82F6;"><span class="ev-label">Internship Start</span><span class="ev-value">{{ evDate($employeeData->internship_start_date ?? null) }}</span></div>
                                <div class="ev-item" style="border-left: 4px solid #3B82F6;"><span class="ev-label">Internship End</span><span class="ev-value">{{ evDate($employeeData->internship_end_date ?? null) }}</span></div>
                                <div class="ev-item" style="border-left: 4px solid #3B82F6;"><span class="ev-label">Internship Extended To</span><span class="ev-value">{{ evDate($employeeData->internship_extended_to ?? null) }}</span></div>
                                <div class="ev-item" style="border-left: 4px solid #3B82F6;"><span class="ev-label">Paid Intern</span><span class="ev-value">{{ isset($employeeData->is_paid_intern) ? ((int)$employeeData->is_paid_intern === 1 ? 'Yes' : 'No') : '-' }}</span></div>
                            @endif

                            @if($hasProbation)
                                <div class="ev-item" style="border-left: 4px solid #F59E0B;"><span class="ev-label">Probation Months</span><span class="ev-value">{{ $employeeData->probation_months ?? '-' }}</span></div>
                                <div class="ev-item" style="border-left: 4px solid #F59E0B;"><span class="ev-label">Probation Status</span><span class="ev-value">{{ !empty($employeeData->probation_status) ? ucfirst($employeeData->probation_status) : '-' }}</span></div>
                                <div class="ev-item" style="border-left: 4px solid #F59E0B;"><span class="ev-label">Probation Start</span><span class="ev-value">{{ evDate($employeeData->probation_start_date ?? null) }}</span></div>
                                <div class="ev-item" style="border-left: 4px solid #F59E0B;"><span class="ev-label">Probation End</span><span class="ev-value">{{ evDate($employeeData->probation_end_date ?? null) }}</span></div>
                            @endif

                            @if($hasConfirmation)
                                <div class="ev-item" style="border-left: 4px solid #10B981; grid-column: span 2;"><span class="ev-label">Confirmation / Permanent Date</span><span class="ev-value">{{ evDate($employeeData->permanent_at ?? $employeeData->joining_date ?? null) }} (Confirmed)</span></div>
                            @endif

                            @if($hasExit)
                                <div class="ev-item" style="border-left: 4px solid #EF4444;"><span class="ev-label">Relieving Date</span><span class="ev-value">{{ evDate($employeeData->relieving_date ?? null) }}</span></div>
                                <div class="ev-item" style="border-left: 4px solid #EF4444;"><span class="ev-label">Exit Status</span><span class="ev-value" style="color: #EF4444;">{{ ucfirst($employmentStatus) }}</span></div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Section D: Education & Experience -->
            <div class="ev-card">
                <div class="ev-card-head">
                    <div class="ev-card-title-wrap">
                        <span class="ev-card-icon"><i class="fas fa-graduation-cap"></i></span>
                        <div>
                            <h5 class="ev-card-title">Education & Experience</h5>
                            <span class="ev-card-subtitle">Academic qualifications & professional background</span>
                        </div>
                    </div>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Highest Qualification</span><span class="ev-value">{{ $employeeData->highest_qualification ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">CGPA / Percentage</span><span class="ev-value">{{ $employeeData->cgpa_percentage ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Experience Type</span><span class="ev-value">{{ !empty($employeeData->experience_type) ? ucfirst($employeeData->experience_type) : '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Total Experience</span><span class="ev-value">{{ $employeeData->total_experience ?? '-' }}</span></div>
                        <div class="ev-item" style="grid-column: 1 / -1;">
                            <span class="ev-label">Resume Attachment</span>
                            @if(!empty($employeeData->resume_file))
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <a href="{{ $fileUrl($employeeData->resume_file) }}" target="_blank" class="ev-btn ev-btn-soft" style="min-height: 32px !important; padding: 6px 12px !important; font-size: 12px !important;">
                                        <i class="fas fa-file-pdf"></i> View Resume
                                    </a>
                                </div>
                            @else
                                <span class="ev-value text-muted" style="font-size: 12px; font-weight: 500;">No resume uploaded.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section E: Bank Details -->
            @php
                $maskedAccount = '-';
                if (!empty($employeeData->bank_account_no)) {
                    $acc = (string) $employeeData->bank_account_no;
                    if (strlen($acc) > 4) {
                        $maskedAccount = str_repeat('•', strlen($acc) - 4) . ' ' . substr($acc, -4);
                    } else {
                        $maskedAccount = str_repeat('•', strlen($acc));
                    }
                }
            @endphp
            <div class="ev-card">
                <div class="ev-card-head">
                    <div class="ev-card-title-wrap">
                        <span class="ev-card-icon"><i class="fas fa-university"></i></span>
                        <div>
                            <h5 class="ev-card-title">Bank Details</h5>
                            <span class="ev-card-subtitle">Salary disbursement bank details</span>
                        </div>
                    </div>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Account Holder</span><span class="ev-value">{{ $employeeData->bank_holder_name ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Account Number</span><span class="ev-value font-weight-bold" style="letter-spacing: 1px;">{{ $maskedAccount }}</span></div>
                        <div class="ev-item"><span class="ev-label">Account Type</span><span class="ev-value">{{ $employeeData->bank_account_type ?? '-' }}</span></div>
                        <div class="ev-item"><span class="ev-label">IFSC Code</span><span class="ev-value">{{ $employeeData->ifsc_code ?? '-' }}</span></div>
                        <div class="ev-item" style="grid-column:1/-1;"><span class="ev-label">Bank Branch</span><span class="ev-value">{{ $employeeData->bank_branch ?? '-' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Section F: Emergency Contact -->
            <div class="ev-card">
                <div class="ev-card-head">
                    <div class="ev-card-title-wrap">
                        <span class="ev-card-icon"><i class="fas fa-phone-alt"></i></span>
                        <div>
                            <h5 class="ev-card-title">Emergency Contact</h5>
                            <span class="ev-card-subtitle">Primary contact in case of emergencies</span>
                        </div>
                    </div>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item" style="grid-column: span 2;">
                            <span class="ev-label">Emergency Contact Number</span>
                            <span class="ev-value" style="font-size: 15px; font-weight: 800; color: #DC2626;">
                                @if(!empty($employeeData->emergency_contact_number))
                                    <i class="fas fa-phone-alt mr-1"></i> {{ $employeeData->emergency_contact_number }}
                                @else
                                    <span class="text-muted" style="font-weight: 500; font-size: 13px;">Not provided</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Profile Approval & Meta Info -->
            <div class="ev-card">
                <div class="ev-card-head">
                    <div class="ev-card-title-wrap">
                        <span class="ev-card-icon"><i class="fas fa-user-check"></i></span>
                        <div>
                            <h5 class="ev-card-title">Profile Verification Status</h5>
                            <span class="ev-card-subtitle">HR profile verification & onboarding completion logs</span>
                        </div>
                    </div>
                </div>
                <div class="ev-card-body">
                    <div class="ev-info-grid">
                        <div class="ev-item"><span class="ev-label">Completed</span><span class="ev-value">{{ $isCompleted ? 'Yes' : 'No' }}</span></div>
                        <div class="ev-item"><span class="ev-label">Completed At</span><span class="ev-value">{{ evDate($employeeData->profile_completed_at ?? null) }}</span></div>
                        <div class="ev-item" style="grid-column:1/-1;"><span class="ev-label">Rejection Reason</span><span class="ev-value" style="color: #991B1B;">{{ $employeeData->rejection_reason ?? '-' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Section: Salary History -->
            @if($canSeeSalary)
                <div class="ev-card ev-full">
                    <div class="ev-card-head">
                        <div class="ev-card-title-wrap">
                            <span class="ev-card-icon"><i class="fas fa-money-check-alt"></i></span>
                            <div>
                                <h5 class="ev-card-title">Salary History</h5>
                                <span class="ev-card-subtitle">Review salary revisions, effective dates, CTC, and approval status.</span>
                            </div>
                        </div>
                    </div>
                    <div class="ev-card-body" style="padding: 0;">
                        <div class="ev-table-responsive">
                            <table class="ev-table">
                                <thead>
                                    <tr>
                                        <th>Effective Date</th>
                                        <th>Lifecycle Stage</th>
                                        <th>Reason / Revision Type</th>
                                        <th>Authorized By</th>
                                        <th style="text-align: right;">Gross Salary / CTC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($salaryHistories as $history)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="font-weight-bold" style="color: var(--orb-text);">{{ evDate($history->effective_from) }}</span>
                                                    @if(!empty($history->effective_to))
                                                        <span class="text-muted" style="font-size: 11px;">to {{ evDate($history->effective_to) }}</span>
                                                    @else
                                                        <span class="text-success" style="font-size: 11px; font-weight: 750;"><i class="fas fa-dot-circle" style="font-size: 8px;"></i> Active / Present</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ev-pill ev-pill-default">
                                                    {{ ucfirst(str_replace('_', ' ', $history->stage)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-weight-medium" style="color: var(--orb-text); font-size: 13px;">
                                                    {{ $history->reason ?: 'Regular revision' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted" style="font-size: 12px; font-weight: 700;">
                                                    <i class="far fa-user-circle"></i> {{ $history->creator_name ?? 'System' }}
                                                </span>
                                            </td>
                                            <td style="text-align: right; font-size: 14px; font-weight: 900; color: var(--orb-primary);">
                                                ₹ {{ number_format($history->salary_amount, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center" style="padding: 30px;">
                                                <div class="ev-empty">No salary history available.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Section G: Employee Documents (Bottom Full Width) -->
            <div class="ev-card ev-full">
                <div class="ev-card-head">
                    <div class="ev-card-title-wrap">
                        <span class="ev-card-icon"><i class="fas fa-file-alt"></i></span>
                        <div>
                            <h5 class="ev-card-title">Employee Documents</h5>
                            <span class="ev-card-subtitle">Mandatory and optional compliance documents & verification logs</span>
                        </div>
                    </div>
                </div>
                <div class="ev-card-body" style="padding: 0;">
                    <div class="ev-table-responsive">
                        <table class="ev-table">
                            <thead>
                                <tr>
                                    <th>Document Type</th>
                                    <th>Requirement</th>
                                    <th>Status</th>
                                    <th>Uploaded File</th>
                                    <th>Verification Details</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                    @php
                                        $status = $doc['verification_status'];
                                        $statusClass = match($status) {
                                            'verified' => 'ev-pill-completed',
                                            'pending' => 'ev-pill-submitted',
                                            'rejected' => 'ev-pill-rejected',
                                            'missing' => 'ev-pill-pending',
                                            default => 'ev-pill-default',
                                        };
                                        $statusLabel = match($status) {
                                            'verified' => 'Verified & Locked',
                                            'pending' => 'Pending Verification',
                                            'rejected' => 'Rejected',
                                            'missing' => 'Not uploaded',
                                            default => 'Unknown',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="ev-doc-type-cell">
                                                <span class="ev-doc-type-name">{{ $doc['name'] }}</span>
                                                @if(!empty($doc['code']))
                                                    <span class="ev-doc-type-code">{{ $doc['code'] }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($doc['is_mandatory'])
                                                <span class="ev-badge-req">Mandatory</span>
                                            @else
                                                <span class="ev-badge-opt">Optional</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="ev-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td>
                                            @if($doc['is_uploaded'])
                                                <div class="ev-file-info">
                                                    <span class="ev-file-name" title="{{ $doc['file_original_name'] }}">
                                                        <i class="far fa-file-alt"></i> {{ Str::limit($doc['file_original_name'], 30) }}
                                                    </span>
                                                    <span class="ev-file-date">Uploaded: {{ !empty($doc['uploaded_at']) ? \Carbon\Carbon::parse($doc['uploaded_at'])->format('d M Y, h:i A') : '-' }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted font-weight-bold" style="font-size: 12px;">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($status === 'verified')
                                                <div class="ev-verifier-info">
                                                    <span class="ev-verifier-name"><i class="fas fa-check-circle text-success"></i> Verified by: {{ $doc['verifier_name'] ?? 'HR Admin' }}</span>
                                                    <span class="ev-verifier-date">{{ !empty($doc['verified_at']) ? \Carbon\Carbon::parse($doc['verified_at'])->format('d M Y') : '-' }}</span>
                                                </div>
                                            @elseif($status === 'rejected')
                                                <div class="ev-rejection-info">
                                                    <span class="ev-rejection-label"><i class="fas fa-exclamation-circle text-danger"></i> Reason:</span>
                                                    <span class="ev-rejection-reason" title="{{ $doc['rejection_reason'] }}">{{ Str::limit($doc['rejection_reason'], 40) }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted font-weight-bold" style="font-size: 12px;">-</span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            @if($doc['is_uploaded'])
                                                <div class="ev-doc-actions">
                                                    <a href="{{ $fileUrl($doc['file_path']) }}" target="_blank" class="ev-btn-icon" title="View / Preview">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="{{ $fileUrl($doc['file_path']) }}?download=1" class="ev-btn-icon ev-btn-icon-soft" title="Download">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted font-weight-bold" style="font-size: 12px;">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center" style="padding: 30px;">
                                            <div class="ev-empty">No documents submitted by this employee yet.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
