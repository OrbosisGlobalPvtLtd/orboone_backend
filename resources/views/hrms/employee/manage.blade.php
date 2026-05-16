@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Manage Employee')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .em-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .em-container {
        max-width: 1320px;
        margin: 0 auto;
    }

    .em-hero,
    .em-card {
        background: var(--orb-card);
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
    }

    .em-hero {
        padding: 18px;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
    }

    .em-user {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .em-avatar {
        width: 68px;
        height: 68px;
        border-radius: 24px;
        background: linear-gradient(135deg, #F4F2FF, #EEF2FF);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 900;
        overflow: hidden;
        flex: 0 0 auto;
        border: 1px solid #EEE7FF;
    }

    .em-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .em-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 25px;
        font-weight: 900;
        letter-spacing: -.4px;
    }

    .em-sub {
        margin: 5px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 750;
    }

    .em-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 9px;
    }

    .em-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .em-badge-success {
        background: #DCFCE7;
        color: #166534;
    }

    .em-badge-warning {
        background: #FFF4D6;
        color: #B54708;
    }

    .em-badge-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .em-badge-info {
        background: #E0F2FE;
        color: #0369A1;
    }

    .em-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .em-btn {
        min-height: 40px;
        border-radius: 13px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 900;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .em-btn-light {
        background: #fff;
        color: var(--orb-text);
        border-color: var(--orb-border);
    }

    .em-btn-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff !important;
    }

    .em-btn-success {
        background: #16A34A;
        color: #fff !important;
        display: none;
    }

    .em-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        align-items: start;
    }

    .em-card {
        overflow: hidden;
    }

    .em-card-full {
        grid-column: 1/-1;
    }

    .em-card-head {
        padding: 15px 16px;
        border-bottom: 1px solid #EEF1F6;
        background: linear-gradient(135deg, #FCFCFD, #F7F4FF);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }

    .em-card-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 16px;
        font-weight: 900;
    }

    .em-card-title i {
        color: var(--orb-primary);
    }

    .em-card-sub {
        margin-top: 3px;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 750;
    }

    .em-card-body {
        padding: 16px;
    }

    .em-section {
        padding: 14px;
        border: 1px solid #EEF1F6;
        border-radius: 18px;
        background: #fff;
        margin-bottom: 12px;
    }

    .em-section:last-child {
        margin-bottom: 0;
    }

    .em-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 12px;
        color: var(--orb-primary);
        font-size: 13px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .em-section-title i {
        width: 30px;
        height: 30px;
        border-radius: 11px;
        background: var(--orb-soft);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .em-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .em-form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .em-field label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 10.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .em-control {
        width: 100%;
        min-height: 42px;
        border-radius: 13px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 800;
        padding: 8px 12px;
    }

    textarea.em-control {
        height: 92px;
        resize: vertical;
    }

    .em-control[readonly],
    .em-control:disabled {
        background: #fff;
        color: #344054;
        opacity: 1;
        pointer-events: none;
    }

    body.edit-mode .em-control {
        background: #F9FAFB;
    }

    body.edit-mode .em-control:not([readonly]):not(:disabled):focus {
        outline: none;
        border-color: rgba(75, 0, 232, .45);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
        background: #fff;
    }

    .em-error {
        color: #DC2626;
        font-size: 11px;
        font-weight: 800;
        margin-top: 5px;
    }

    .em-hidden {
        display: none !important;
    }

    .em-file-view-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px;
        border: 1px solid #EEF1F6;
        border-radius: 15px;
        background: #F8FAFC;
        margin-top: 8px;
    }

    .em-file-view-box span {
        font-size: 12px;
        font-weight: 900;
        color: var(--orb-muted);
    }

    .em-file-view-box a {
        font-size: 12px;
        font-weight: 900;
        color: var(--orb-primary);
        text-decoration: none;
    }

    .em-upload-control {
        display: none;
        margin-top: 8px;
    }

    body.edit-mode .em-upload-control {
        display: block;
    }

    .em-upload-label {
        width: 100%;
        min-height: 76px;
        border-radius: 16px;
        border: 1px dashed rgba(75, 0, 232, .35);
        background: linear-gradient(180deg, #fff, #F8F5FF);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        cursor: pointer;
        margin: 0;
    }

    .em-upload-label input {
        display: none;
    }

    .em-upload-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: #F4F2FF;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .em-upload-text strong {
        display: block;
        font-size: 13px;
        font-weight: 950;
        color: var(--orb-primary);
    }

    .em-upload-text small {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-muted);
    }

    .em-doc-table-wrap {
        overflow-x: auto;
        margin-top: 10px;
    }

    .em-doc-table {
        width: 100%;
        min-width: 760px;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .em-doc-table th {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 0 10px 4px;
        border: 0;
    }

    .em-doc-table td {
        background: #FCFCFD;
        border-top: 1px solid #EEF1F6;
        border-bottom: 1px solid #EEF1F6;
        padding: 12px 10px;
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-text);
        vertical-align: middle;
    }

    .em-doc-table td:first-child {
        border-left: 1px solid #EEF1F6;
        border-radius: 14px 0 0 14px;
    }

    .em-doc-table td:last-child {
        border-right: 1px solid #EEF1F6;
        border-radius: 0 14px 14px 0;
        text-align: right;
    }

    .em-doc-name {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .em-doc-icon {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .em-doc-title {
        font-weight: 950;
        color: var(--orb-text);
    }

    .em-doc-sub {
        font-size: 11px;
        font-weight: 750;
        color: var(--orb-muted);
        margin-top: 2px;
    }

    .em-doc-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .em-doc-required {
        background: #FFF7ED;
        color: #C2410C;
    }

    .em-doc-optional {
        background: #F1F5F9;
        color: #475569;
    }

    .em-doc-verified {
        background: #DCFCE7;
        color: #166534;
    }

    .em-doc-pending {
        background: #E0F2FE;
        color: #0369A1;
    }

    .em-doc-rejected {
        background: #FEE2E2;
        color: #991B1B;
    }

    .em-doc-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .em-doc-view {
        min-height: 34px;
        border-radius: 11px;
        padding: 7px 10px;
        background: #F4F2FF;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .14);
        font-size: 12px;
        font-weight: 950;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .em-reupload-label {
        min-height: 34px;
        border-radius: 11px;
        padding: 7px 10px;
        background: #E0F2FE;
        color: #0369A1;
        border: 0;
        font-size: 12px;
        font-weight: 950;
        display: none;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        margin: 0;
    }

    body.edit-mode .em-reupload-label {
        display: inline-flex;
    }

    .em-reupload-label input {
        display: none;
    }

    .em-reupload-label.is-uploading {
        opacity: .75;
        pointer-events: none;
    }

    .em-reupload-label.is-uploading i {
        animation: docSpin .8s linear infinite;
    }

    .em-reupload-label.is-uploading i:before {
        content: "\f110";
    }

    @keyframes docSpin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .salary-table-wrap {
        overflow-x: auto;
    }

    .salary-table {
        width: 100%;
        min-width: 860px;
        margin: 0;
    }

    .salary-table th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .4px;
        border-bottom: 1px solid var(--orb-border);
        padding: 11px 12px;
        white-space: nowrap;
    }

    .salary-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #F1F3F8;
        font-size: 13px;
        font-weight: 700;
        color: #344054;
        vertical-align: middle;
    }

    .salary-pill {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .salary-active {
        background: #DCFCE7;
        color: #166534;
    }

    .salary-closed {
        background: #F2F4F7;
        color: #667085;
    }

    .salary-type {
        background: #F4F2FF;
        color: var(--orb-primary);
    }

    .empty-history {
        padding: 22px;
        text-align: center;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 800;
    }

    @media(max-width:1100px) {
        .em-layout {
            grid-template-columns: 1fr;
        }

        .em-form-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:768px) {
        .em-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .em-form-grid,
        .em-form-grid-3 {
            grid-template-columns: 1fr;
        }

        .em-actions,
        .em-btn {
            width: 100%;
        }
    }

    @media(max-width:575px) {
        .em-page {
            padding: 10px 8px 24px;
        }

        .em-user {
            align-items: flex-start;
        }

        .em-title {
            font-size: 21px;
        }
    }
</style>

@php
$name = $employeeData->name ?? 'Employee';
$initial = strtoupper(substr($name, 0, 1));
$isCompleted = (int) ($employeeData->is_profile_completed ?? 0) === 1;

$employmentStatus = strtolower($employeeData->employment_status ?? 'active');
$profileStatus = strtolower($employeeData->profile_status ?? 'pending');
$stage = strtolower($employeeData->employee_stage ?? 'probation');
$isPermanent = (int) ($employeeData->is_permanent ?? 0) === 1 || $stage === 'permanent';

$employmentBadgeClass = match ($employmentStatus) {
'active' => 'em-badge-success',
'resigned' => 'em-badge-warning',
'terminated', 'inactive' => 'em-badge-danger',
default => '',
};

$profileBadgeClass = match ($profileStatus) {
'approved' => 'em-badge-success',
'submitted' => 'em-badge-info',
'rejected' => 'em-badge-danger',
default => 'em-badge-warning',
};

$stageBadgeClass = match ($stage) {
'internship' => 'em-badge-info',
'probation' => 'em-badge-warning',
'permanent' => 'em-badge-success',
'contract', 'freelance' => 'em-badge-info',
default => '',
};

$internshipStatus = strtolower($employeeData->internship_status ?? '');
$approvedAt = !empty($employeeData->approved_at) ? \Carbon\Carbon::parse($employeeData->approved_at)->format('d M Y') : null;

$fileUrl = function ($path) {
return !empty($path) && Route::has('hrms.documents.file')
? route('hrms.documents.file', $path)
: '#';
};

$employeeDocuments = $employeeDocuments ?? collect();
@endphp

<div class="em-page">
    <div class="em-container">

        @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('hrms.employees.manage.update', $employeeData->id) }}"
            enctype="multipart/form-data" id="employeeManageForm">
            @csrf
            @method('PUT')

            <div class="em-hero">
                <div class="em-user">
                    <div class="em-avatar">
                        @if (!empty($employeeData->profile_image))
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
                            <span class="em-badge {{ $stageBadgeClass }}"><i class="fas fa-layer-group"></i>{{ ucfirst(str_replace('_', ' ', $stage)) }}</span>
                            <span class="em-badge {{ $profileBadgeClass }}"><i class="fas fa-id-card"></i>{{ $isCompleted ? 'Profile Approved' : ucfirst($profileStatus) }}</span>
                            @if ($isPermanent)
                            <span class="em-badge em-badge-success"><i class="fas fa-user-check"></i>Permanent</span>
                            @endif
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
                            <h5 class="em-card-title"><i class="fas fa-user-tie mr-2"></i>Employee Details</h5>
                            <div class="em-card-sub">Basic, job, lifecycle and salary setup</div>
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
                                        @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $employeeData->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Designation</label>
                                    <select name="designation_id" id="designation_id" class="em-control editable-select" disabled>
                                        <option value="">Select Designation</option>
                                        @foreach ($designations as $des)
                                        <option value="{{ $des->id }}" data-department-id="{{ $des->department_id ?? '' }}" {{ old('designation_id', $employeeData->designation_id) == $des->id ? 'selected' : '' }}>
                                            {{ $des->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('designation_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Reporting Manager</label>
                                    <select name="reporting_manager_employee_id" class="em-control editable-select" disabled>
                                        <option value="">Select Manager</option>
                                        @foreach(($reportingManagers ?? collect()) as $manager)
                                        <option value="{{ $manager->id }}" {{ old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id ?? '') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }} - {{ $manager->employee_code }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('reporting_manager_employee_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>System Role</label>
                                    <select name="system_role_id" class="em-control editable-select" disabled>
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('system_role_id', $employeeData->system_role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name ?? ($role->name ?? ($role->title ?? 'Role '.$role->id)) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('system_role_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-briefcase"></i>Employment & Lifecycle</h6>
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
                                    <input type="text" id="employee_stage_display" class="em-control" value="{{ ucfirst(str_replace('_', ' ', $stage ?: 'Auto')) }}" readonly>
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
                                        <option value="inactive" {{ old('employment_status', $employeeData->employment_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                            </div>
                        </div>

                        <div class="em-section probation-section">
                            <h6 class="em-section-title"><i class="fas fa-hourglass-half"></i>Probation / Permanent Details</h6>
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

                                <div class="em-field">
                                    <label>Is Permanent</label>
                                    <input type="text" class="em-control" value="{{ $isPermanent ? 'Yes' : 'No' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Permanent Date</label>
                                    <input type="date" class="em-control" value="{{ $employeeData->permanent_at ?? '' }}" readonly>
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
                                    <label>Extended To</label>
                                    <input type="date" class="em-control" value="{{ $employeeData->internship_extended_to ?? '' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Internship Status</label>
                                    <input type="text" class="em-control" value="{{ $internshipStatus ? ucfirst(str_replace('_', ' ', $internshipStatus)) : '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Completed At</label>
                                    <input type="text" class="em-control" value="{{ !empty($employeeData->internship_completed_at) ? \Carbon\Carbon::parse($employeeData->internship_completed_at)->format('d M Y h:i A') : '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Paid Intern</label>
                                    <select name="is_paid_intern" class="em-control editable-select" disabled>
                                        <option value="">Select</option>
                                        <option value="1" {{ (string) old('is_paid_intern', $employeeData->is_paid_intern) === '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ (string) old('is_paid_intern', $employeeData->is_paid_intern) === '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="em-section contract-section">
                            <h6 class="em-section-title"><i class="fas fa-file-contract"></i>Contract / Freelance Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Contract End / Review Date</label>
                                    <input type="date" name="contract_end_date" class="em-control editable" value="{{ old('contract_end_date', $employeeData->contract_end_date ?? '') }}" readonly>
                                    @error('contract_end_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-money-bill-wave"></i>Current Salary Update</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Actual Salary</label>
                                    <input type="number" step="0.01" name="actual_salary" class="em-control editable" value="{{ old('actual_salary', $employeeData->actual_salary) }}" readonly>
                                    @error('actual_salary') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Salary Effective From</label>
                                    <input type="date" name="salary_effective_from" class="em-control editable" value="{{ old('salary_effective_from', now()->toDateString()) }}" readonly>
                                    @error('salary_effective_from') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" style="grid-column:1/-1;">
                                    <label>Salary Reason</label>
                                    <input type="text" name="salary_change_reason" class="em-control editable" value="{{ old('salary_change_reason') }}" placeholder="Increment / Stage change / Correction" readonly>
                                    @error('salary_change_reason') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="em-card">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-id-card mr-2"></i>Profile Details</h5>
                            <div class="em-card-sub">Personal, education, experience and bank details</div>
                        </div>
                    </div>

                    <div class="em-card-body">
                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-user"></i>Personal Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Profile Image</label>

                                    <div class="em-file-view-box">
                                        <span>{{ !empty($employeeData->profile_image) ? 'Image uploaded' : 'No image uploaded' }}</span>
                                        @if (!empty($employeeData->profile_image))
                                        <a href="{{ $fileUrl($employeeData->profile_image) }}" target="_blank"><i class="fas fa-eye"></i> View</a>
                                        @endif
                                    </div>

                                    <div class="em-upload-control">
                                        <label class="em-upload-label">
                                            <input type="file" name="profile_image" class="editable-file" disabled accept=".jpg,.jpeg,.png,.webp">
                                            <span class="em-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="em-upload-text">
                                                <strong>{{ !empty($employeeData->profile_image) ? 'Replace Profile Image' : 'Upload Profile Image' }}</strong>
                                                <small>JPG, PNG, WEBP supported</small>
                                            </span>
                                        </label>
                                    </div>

                                    @error('profile_image') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

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

                                <div class="em-field">
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
                                    <label>Experience Type</label>
                                    <select name="experience_type" class="em-control editable-select" disabled>
                                        <option value="">Select Experience Type</option>
                                        <option value="fresher" {{ old('experience_type', $employeeData->experience_type ?? '') == 'fresher' ? 'selected' : '' }}>Fresher</option>
                                        <option value="experienced" {{ old('experience_type', $employeeData->experience_type ?? '') == 'experienced' ? 'selected' : '' }}>Experienced</option>
                                    </select>
                                    @error('experience_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Total Experience</label>
                                    <input type="text" name="total_experience" class="em-control editable" value="{{ old('total_experience', $employeeData->total_experience) }}" readonly>
                                    @error('total_experience') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" style="grid-column:1/-1;">
                                    <label>Resume File</label>

                                    <div class="em-file-view-box">
                                        <span>{{ !empty($employeeData->resume_file) ? 'Resume uploaded' : 'No resume uploaded' }}</span>
                                        @if (!empty($employeeData->resume_file))
                                        <a href="{{ $fileUrl($employeeData->resume_file) }}" target="_blank"><i class="fas fa-eye"></i> View</a>
                                        @endif
                                    </div>

                                    <div class="em-upload-control">
                                        <label class="em-upload-label">
                                            <input type="file" name="resume_file" class="editable-file" disabled accept=".pdf,.jpg,.jpeg,.png,.webp">
                                            <span class="em-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="em-upload-text">
                                                <strong>{{ !empty($employeeData->resume_file) ? 'Replace Resume' : 'Upload Resume' }}</strong>
                                                <small>PDF, JPG, PNG, WEBP supported</small>
                                            </span>
                                        </label>
                                    </div>

                                    @error('resume_file') <div class="em-error">{{ $message }}</div> @enderror
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
                            <h6 class="em-section-title"><i class="fas fa-id-card"></i>Profile Approval Status</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Profile Status</label>
                                    <input type="text" class="em-control" value="{{ ucfirst($employeeData->profile_status ?? 'pending') }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Profile Completed</label>
                                    <input type="text" class="em-control" value="{{ $isCompleted ? 'Yes' : 'No' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Approved At</label>
                                    <input type="text" class="em-control" value="{{ $approvedAt ?? '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Rejection Reason</label>
                                    <input type="text" class="em-control" value="{{ $employeeData->rejection_reason ?? '-' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="em-card em-card-full">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-history mr-2"></i>Salary History</h5>
                            <div class="em-card-sub">Date-wise salary/stipend records. Old records are preserved.</div>
                        </div>
                    </div>

                    <div class="em-card-body">
                        <div class="salary-table-wrap">
                            @if (isset($salaryHistories) && $salaryHistories->count())
                            <table class="salary-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Stage</th>
                                        <th>Salary Type</th>
                                        <th>Amount</th>
                                        <th>Effective From</th>
                                        <th>Effective To</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salaryHistories as $history)
                                    @php
                                    $historyStage = $history->employment_stage ?? ($history->stage ?? '-');
                                    $historyType = $history->salary_type ?? ((float) ($history->salary_amount ?? 0) <= 0 ? 'unpaid' : ($historyStage==='internship' ? 'stipend' : 'salary' ));
                                        $active=isset($history->is_active) ? (int) $history->is_active === 1 : empty($history->effective_to);
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $historyStage)) }}</td>
                                            <td><span class="salary-pill salary-type">{{ ucfirst($historyType) }}</span></td>
                                            <td>₹{{ number_format((float) ($history->salary_amount ?? 0), 2) }}</td>
                                            <td>{{ !empty($history->effective_from) ? \Carbon\Carbon::parse($history->effective_from)->format('d M Y') : '-' }}</td>
                                            <td>{{ !empty($history->effective_to) ? \Carbon\Carbon::parse($history->effective_to)->format('d M Y') : '-' }}</td>
                                            <td>
                                                <span class="salary-pill {{ $active ? 'salary-active' : 'salary-closed' }}">
                                                    {{ $active ? 'Active' : 'Closed' }}
                                                </span>
                                            </td>
                                            <td>{{ $history->reason ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="empty-history">
                                <i class="fas fa-info-circle mr-1"></i> No salary history found.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="em-card em-card-full">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-folder-open mr-2"></i>Employee Documents</h5>
                            <div class="em-card-sub">Uploaded employee documents. Edit mode me upload/re-upload option show hoga.</div>
                        </div>
                    </div>

                    <div class="em-card-body">
                        <div class="em-doc-table-wrap">
                            <table class="em-doc-table">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Required</th>
                                        <th>Status</th>
                                        <th>Uploaded At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($employeeDocuments as $doc)
                                    @php
                                    $docTitle = $doc->document_type_name ?? $doc->title ?? 'Document';
                                    $docStatus = strtolower($doc->verification_status ?? 'pending');

                                    $docStatusClass = match($docStatus) {
                                    'verified' => 'em-doc-verified',
                                    'rejected' => 'em-doc-rejected',
                                    default => 'em-doc-pending',
                                    };

                                    $docPath = $doc->file_path ?? null;

                                    $docUrl = !empty($docPath) && Route::has('hrms.documents.file')
                                    ? route('hrms.documents.file', $docPath)
                                    : (!empty($docPath) ? asset('storage/'.$docPath) : null);

                                    $documentTypeId = $doc->document_type_id ?? $doc->category_id ?? null;
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="em-doc-name">
                                                <div class="em-doc-icon"><i class="fas fa-file-alt"></i></div>
                                                <div>
                                                    <div class="em-doc-title">{{ $docTitle }}</div>
                                                    <div class="em-doc-sub">{{ $doc->file_original_name ?? 'No file uploaded' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="em-doc-pill {{ !empty($doc->is_required) ? 'em-doc-required' : 'em-doc-optional' }}">
                                                {{ !empty($doc->is_required) ? 'Required' : 'Optional' }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="em-doc-pill {{ $docStatusClass }}">
                                                {{ ucfirst($docStatus) }}
                                            </span>
                                        </td>

                                        <td>
                                            @if(!empty($doc->uploaded_at))
                                            {{ \Carbon\Carbon::parse($doc->uploaded_at)->format('d M Y, h:i A') }}
                                            @elseif(!empty($doc->created_at))
                                            {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y, h:i A') }}
                                            @else
                                            -
                                            @endif
                                        </td>

                                        <td>
                                            <div class="em-doc-actions">
                                                @if($docUrl)
                                                <a href="{{ $docUrl }}" target="_blank" class="em-doc-view">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @endif

                                                @if($documentTypeId && Route::has('hrms.documents.employee.upload_from_profile'))
                                                <label class="em-reupload-label">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                    {{ !empty($doc->file_path) ? 'Re-upload' : 'Upload' }}

                                                    <input type="file"
                                                        name="file"
                                                        form="docUploadForm{{ $loop->iteration }}"
                                                        class="js-manage-doc-upload"
                                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                        required>
                                                </label>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center;border-radius:14px;border:1px solid #EEF1F6;">
                                            No employee documents found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        @foreach($employeeDocuments as $doc)
        @php
        $documentTypeId = $doc->document_type_id ?? $doc->category_id ?? null;
        @endphp

        @if($documentTypeId && Route::has('hrms.documents.employee.upload_from_profile'))
        <form id="docUploadForm{{ $loop->iteration }}"
            action="{{ route('hrms.documents.employee.upload_from_profile', [$employeeData->id, $documentTypeId]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="d-none">
            @csrf
            <input type="hidden" name="keep_verified" value="1">
        </form>
        @endif
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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

            if (editBtn) editBtn.style.display = 'none';
            if (saveBtn) saveBtn.style.display = 'inline-flex';
        }

        function toggleEmploymentSections() {
            const type = employmentTypeSelect ? employmentTypeSelect.value : '';
            const currentStage = employeeStageSelect ? employeeStageSelect.value : '';

            let stage = currentStage || (
                type === 'intern' ? 'internship' :
                (type === 'freelancer' ? 'freelance' :
                    (type === 'contract' ? 'contract' : 'probation'))
            );

            if (employmentTypeSelect && document.body.classList.contains('edit-mode')) {
                stage = type === 'intern' ? 'internship' :
                    (type === 'freelancer' ? 'freelance' :
                        (type === 'contract' ? 'contract' : 'probation'));

                if (employeeStageSelect) {
                    employeeStageSelect.value = stage;
                }
            }

            if (employeeStageDisplay) {
                employeeStageDisplay.value = stage ?
                    stage.replace(/_/g, ' ').replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    }) :
                    'Auto';
            }

            document.querySelectorAll('.internship-section').forEach(function(el) {
                el.style.display = stage === 'internship' ? 'block' : 'none';
            });

            document.querySelectorAll('.probation-section').forEach(function(el) {
                el.style.display = stage === 'internship' || stage === 'contract' || stage === 'freelance' ?
                    'none' :
                    'block';
            });

            document.querySelectorAll('.contract-section').forEach(function(el) {
                el.style.display = stage === 'contract' || stage === 'freelance' ? 'block' : 'none';
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

        document.querySelectorAll('.js-manage-doc-upload').forEach(function(input) {
            input.addEventListener('change', function() {
                if (!this.files || !this.files.length) return;

                const label = this.closest('.em-reupload-label');
                const formId = this.getAttribute('form');
                const form = document.getElementById(formId);

                if (label) {
                    label.classList.add('is-uploading');
                    label.innerHTML = '<i class="fas fa-spinner"></i> Uploading...';
                }

                if (form) {
                    form.appendChild(this);
                    form.submit();
                }
            });
        });

        if (editBtn) {
            editBtn.addEventListener('click', function() {
                enableEditMode();
                toggleEmploymentSections();
            });
        }

        if (departmentSelect) {
            departmentSelect.addEventListener('change', function() {
                loadDesignations(this.value);
            });
        }

        if (employmentTypeSelect) {
            employmentTypeSelect.addEventListener('change', toggleEmploymentSections);
            toggleEmploymentSections();
        }

        @if($errors -> any())
        enableEditMode();
        @endif
    });
</script>
@endsection