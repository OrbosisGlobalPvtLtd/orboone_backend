@extends('layouts.panel', ['active' => 'my-profile'])

@section('page_title', 'Complete Profile')

@section('_content')
<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-success: #027A48;
        --orb-warning: #B54708;
        --orb-danger: #B42318;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .profile-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        overflow-x: hidden;
    }

    .profile-container {
        width: 100%;
        max-width: 1220px;
        margin: 0 auto;
    }

    .profile-hero {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 28px;
        padding: 26px;
        color: #fff;
        box-shadow: var(--orb-shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        position: relative;
        overflow: hidden;
    }

    .profile-hero::after {
        content: '';
        position: absolute;
        right: -60px;
        top: -60px;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
    }

    .profile-hero-left {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
        position: relative;
        z-index: 2;
    }

    .avatar-container {
        position: relative;
        width: 92px;
        height: 92px;
        border-radius: 24px;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, .45);
        background: rgba(255, 255, 255, .14);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 900;
        color: #fff;
        box-shadow: 0 8px 22px rgba(0, 0, 0, .12);
        flex: 0 0 auto;
        cursor: pointer;
        transition: .2s ease;
    }

    .avatar-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(0, 0, 0, .18);
    }

    .profile-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hero-kicker {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .82;
        margin-bottom: 6px;
    }

    .profile-hero-title {
        font-size: 28px;
        font-weight: 900;
        margin: 0;
        letter-spacing: -.5px;
    }

    .profile-hero-subtitle {
        margin: 5px 0 0;
        opacity: .92;
        font-size: 13px;
        font-weight: 700;
    }

    .hero-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
        position: relative;
        z-index: 2;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 14px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .25);
        color: #fff;
    }

    .status-pill-incomplete {
        background: #FEF3F2;
        color: #B42318;
        border-color: #FECDCA;
    }

    .status-pill-submitted {
        background: #FFFAEB;
        color: #B54708;
        border-color: #FEDF89;
    }

    .status-pill-approved {
        background: #ECFDF3;
        color: #027A48;
        border-color: #ABEFC6;
    }

    .status-pill-rejected {
        background: #FEF3F2;
        color: #B42318;
        border-color: #FECDCA;
    }

    .profile-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        box-shadow: var(--orb-shadow);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .card-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: #fff;
    }

    .card-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .card-icon {
        width: 42px;
        height: 42px;
        border-radius: 15px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 16px;
    }

    .card-title {
        font-size: 17px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0;
    }

    .card-subtitle {
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-muted);
        margin: 3px 0 0;
    }

    .card-body {
        padding: 20px;
    }

    .profile-section {
        border: 1px solid #EEF0F6;
        background: #FCFCFD;
        border-radius: 20px;
        padding: 18px;
        margin-bottom: 16px;
    }

    .profile-section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 14px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0 0 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #EEF0F6;
    }

    .section-title i {
        color: var(--orb-primary);
    }

    .profile-label {
        display: block;
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .045em;
        margin-bottom: 7px;
    }

    .profile-control {
        width: 100%;
        min-height: 42px;
        border-radius: 13px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        font-size: 13px;
        font-weight: 700;
        padding: 9px 13px;
        transition: .2s ease;
    }

    .profile-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    textarea.profile-control {
        min-height: 90px;
    }

    .field-locked,
    .profile-control:disabled,
    .profile-control[readonly] {
        background: #F8FAFC !important;
        color: #667085 !important;
        cursor: not-allowed;
    }

    .lock-icon {
        color: #98A2B3;
        font-size: 11px;
        margin-left: 5px;
    }

    .profile-btn {
        min-height: 40px;
        border-radius: 14px;
        padding: 10px 16px;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid var(--orb-border);
        text-decoration: none !important;
        background: #fff;
        color: var(--orb-text);
        cursor: pointer;
        transition: .2s ease;
    }

    .profile-btn:hover:not(:disabled) {
        background: #F9FAFB;
        color: var(--orb-primary);
        transform: translateY(-1px);
    }

    .profile-btn-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #ffffff !important;
        border: 0 !important;
        box-shadow: 0 10px 24px rgba(75, 0, 232, .24) !important;
    }

    .profile-btn-primary:hover,
    .profile-btn-primary:focus,
    .profile-btn-primary:active {
        background: linear-gradient(135deg, #3F00C8, #7600D6) !important;
        color: #ffffff !important;
        border: 0 !important;
        box-shadow: 0 14px 30px rgba(75, 0, 232, .32) !important;
        transform: translateY(-1px);
    }

    .profile-btn-primary i,
    .profile-btn-primary:hover i,
    .profile-btn-primary:focus i,
    .profile-btn-primary:active i {
        color: #ffffff !important;
    }

    .profile-btn-soft {
        background: var(--orb-soft);
        border-color: #DED6FF;
        color: var(--orb-primary) !important;
    }

    .profile-btn-soft:hover {
        background: #ECE8FF;
        color: var(--orb-primary) !important;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .info-tile {
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        background: #fff;
        padding: 13px;
    }

    .info-value {
        font-size: 13px;
        font-weight: 900;
        color: var(--orb-text);
        word-break: break-word;
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: 5px 9px;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .badge-required {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .badge-optional {
        background: #F2F4F7;
        color: #344054;
        border: 1px solid #EAECF0;
    }

    .badge-missing {
        background: #F2F4F7;
        color: #344054;
        border: 1px solid #EAECF0;
    }

    .badge-pending {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .badge-verified {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .badge-rejected {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .doc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .doc-card {
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        background: #fff;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        transition: .2s ease;
    }

    .doc-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(16, 24, 40, .06);
    }

    .doc-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .doc-name {
        font-size: 14px;
        font-weight: 900;
        color: var(--orb-text);
        line-height: 1.35;
    }

    .doc-file {
        background: #F8FAFC;
        border: 1px solid #EAECF0;
        border-radius: 14px;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .doc-file-name {
        font-size: 12px;
        font-weight: 800;
        color: #475467;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .doc-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .profile-submit-bar {
        position: sticky;
        bottom: 0;
        z-index: 50;
        width: 100%;
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(12px);
        border-top: 1px solid #E7EAF3;
        padding: 14px 24px;
        box-shadow: 0 -10px 30px rgba(16, 24, 40, .08);
        border-radius: 18px 18px 0 0;
        margin-top: 16px;
    }

    .profile-submit-inner {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .fixed-submit-title {
        font-size: 13px;
        font-weight: 900;
        color: var(--orb-text);
    }

    .fixed-submit-subtitle {
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-muted);
    }

    .alert {
        border-radius: 16px !important;
        font-size: 13px;
        font-weight: 800;
    }

    @media(max-width:991px) {
        .profile-page {
            padding: 18px 18px 24px;
        }

        .info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .doc-grid {
            grid-template-columns: 1fr;
        }

        .profile-submit-bar {
            padding: 12px 16px;
        }
    }

    @media(max-width:575px) {
        .profile-page {
            padding: 12px 12px 24px;
        }

        .profile-hero {
            padding: 20px;
            border-radius: 22px;
        }

        .profile-hero-left {
            align-items: flex-start;
        }

        .avatar-container {
            width: 76px;
            height: 76px;
            border-radius: 20px;
        }

        .profile-hero-title {
            font-size: 22px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .card-body {
            padding: 14px;
        }

        .profile-section {
            padding: 14px;
        }

        .profile-submit-inner {
            flex-direction: column;
            align-items: stretch;
        }

        .profile-submit-inner .profile-btn {
            width: 100%;
        }
    }
</style>

@php
$isReadOnly = in_array($status['profile_verification_status'] ?? '', ['submitted', 'approved']);
$disabled = $isReadOnly ? 'disabled' : '';
@endphp

<div class="profile-page">
    <div class="profile-container">
        <div class="profile-hero">
            <div class="profile-hero-left">
                <div class="avatar-container">
                    @if($profile->profile_image)
                    <img src="{{ route('hrms.documents.file', ['path' => $profile->profile_image]) }}" alt="{{ $employee->user->name ?? '' }}" class="profile-avatar-img">
                    @else
                    {{ substr($employee->user->name ?? 'U', 0, 1) }}
                    @endif
                </div>
                <div>
                    <div class="hero-kicker">Employee • Profile Completion</div>
                    <h1 class="profile-hero-title">{{ $employee->user->name ?? 'Employee' }}</h1>
                    <p class="profile-hero-subtitle">
                        {{ $employee?->employee_code ?? 'Employee' }}
                        @if($employee?->department?->name) • {{ $employee->department->name }} @endif
                        @if($employee?->designation?->name) • {{ $employee->designation->name }} @endif
                    </p>
                </div>
            </div>
            <div class="hero-meta">
                @if($status['profile_verification_status'] === 'incomplete')
                <span class="status-pill status-pill-incomplete"><i class="fas fa-exclamation-triangle"></i> Profile Incomplete</span>
                @elseif($status['profile_verification_status'] === 'submitted')
                <span class="status-pill status-pill-submitted"><i class="fas fa-hourglass-half"></i> Pending Verification</span>
                @elseif($status['profile_verification_status'] === 'approved')
                <span class="status-pill status-pill-approved"><i class="fas fa-check-circle"></i> Verified Profile</span>
                @elseif($status['profile_verification_status'] === 'rejected')
                <span class="status-pill status-pill-rejected"><i class="fas fa-times-circle"></i> Correction Required</span>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <strong>Please fix these errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($status['profile_verification_status'] === 'rejected' && $status['rejection_reason'])
        <div class="alert alert-warning border-0 shadow-sm mb-4" style="background:#FFF9E6;border-left:5px solid #EF6C00;">
            <strong><i class="fas fa-comment-dots mr-2"></i>HR Rejection Feedback:</strong>
            <div class="mt-1">{{ $status['rejection_reason'] }}</div>
        </div>
        @endif

        <form action="{{ route('hrms.employee.submit_verification') }}" method="POST" enctype="multipart/form-data" id="profileDetailsForm">
            @csrf

            <div class="profile-card">
                <div class="card-head">
                    <div class="card-title-wrap">
                        <div class="card-icon"><i class="fas fa-id-card"></i></div>
                        <div>
                            <h2 class="card-title">Employee Profile Details</h2>
                            <p class="card-subtitle">Official assignment, personal, education, contact, and bank information in one structured card.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="profile-section">
                        <h3 class="section-title"><i class="fas fa-briefcase"></i> Job Information <span class="ml-auto" style="font-size:11px;color:#98A2B3;font-weight:800;">Read-only official assignment</span></h3>
                        <div class="info-grid">
                            <div class="info-tile"><span class="profile-label">Employee Code <i class="fas fa-lock lock-icon"></i></span>
                                <div class="info-value">{{ $employee?->employee_code ?? '-' }}</div>
                            </div>
                            <div class="info-tile"><span class="profile-label">Department <i class="fas fa-lock lock-icon"></i></span>
                                <div class="info-value">{{ $employee?->department?->name ?? '-' }}</div>
                            </div>
                            <div class="info-tile"><span class="profile-label">Designation <i class="fas fa-lock lock-icon"></i></span>
                                <div class="info-value">{{ $employee?->designation?->name ?? '-' }}</div>
                            </div>
                            <div class="info-tile"><span class="profile-label">Role <i class="fas fa-lock lock-icon"></i></span>
                                <div class="info-value">{{ $employee->user->role->name ?? 'Employee' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-section">
                        <h3 class="section-title"><i class="fas fa-user"></i> Personal & Contact Details</h3>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">Profile Image</label>
                                <input type="file" name="profile_image" class="profile-control" accept="image/*" {{ $disabled }} style="padding-top:6px;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">DOB *</label>
                                <input type="date" name="date_of_birth" class="profile-control" value="{{ old('date_of_birth', $profile?->date_of_birth) }}" required {{ $disabled }}>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">Gender *</label>
                                <select name="gender" class="profile-control" required {{ $disabled }}>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $profile?->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $profile?->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $profile?->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="profile-label">Address *</label>
                                <textarea name="address" class="profile-control" required {{ $disabled }}>{{ old('address', $profile?->address) }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label"><i class="fas fa-phone-alt mr-1"></i> Emergency Contact Number *</label>
                                <input type="text" name="emergency_contact_number" class="profile-control" value="{{ old('emergency_contact_number', $profile?->emergency_contact_number) }}" placeholder="Enter emergency contact number" required {{ $disabled }}>
                                <small class="text-muted d-block mt-1" style="font-size:11px;font-weight:700;">Family member or emergency contact phone number</small>
                            </div>
                        </div>
                    </div>

                    <div class="profile-section">
                        <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Education & Experience</h3>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">Experience Type *</label>
                                <select name="experience_type" id="experience_type" class="profile-control" required onchange="toggleExperience(this.value)" {{ $disabled }}>
                                    <option value="fresher" {{ old('experience_type', $profile?->experience_type ?? 'fresher') === 'fresher' ? 'selected' : '' }}>Fresher</option>
                                    <option value="experienced" {{ old('experience_type', $profile?->experience_type) === 'experienced' ? 'selected' : '' }}>Experienced</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">Highest Qualification *</label>
                                <input type="text" name="highest_qualification" class="profile-control" value="{{ old('highest_qualification', $profile?->highest_qualification) }}" required {{ $disabled }}>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">CGPA / Percentage</label>
                                <input type="text" name="cgpa_percentage" class="profile-control" value="{{ old('cgpa_percentage', $profile?->cgpa_percentage) }}" {{ $disabled }}>
                            </div>
                            <div class="col-md-4 mb-3" id="experience_years_wrapper">
                                <label class="profile-label">Total Experience *</label>
                                <input type="text" name="total_experience" id="total_experience" class="profile-control" value="{{ old('total_experience', $profile?->total_experience) }}" {{ $disabled }}>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="profile-label">Resume File</label>
                                @if($profile?->resume_file)
                                <div class="doc-file mb-2">
                                    <span class="doc-file-name"><i class="fas fa-file-pdf text-danger mr-1"></i> Uploaded</span>
                                    <a href="{{ route('hrms.documents.file', ['path' => $profile->resume_file]) }}" target="_blank" class="profile-btn" style="min-height:30px;padding:4px 10px;font-size:11px;"><i class="fas fa-eye"></i> View</a>
                                </div>
                                @endif
                                <input type="file" name="resume_file" class="profile-control" accept=".pdf,.doc,.docx" {{ $disabled }} style="padding-top:6px;">
                            </div>
                        </div>
                    </div>

                    <div class="profile-section">
                        <h3 class="section-title"><i class="fas fa-university"></i> Bank Details</h3>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">Account Holder Name *</label>
                                <input type="text" name="bank_holder_name" class="profile-control" value="{{ old('bank_holder_name', $profile?->bank_holder_name) }}" required {{ $disabled }}>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">Bank Account No *</label>
                                <input type="text" name="bank_account_no" class="profile-control" value="{{ old('bank_account_no', $profile?->bank_account_no) }}" required {{ $disabled }}>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">Account Type *</label>
                                <select name="bank_account_type" class="profile-control" required {{ $disabled }}>
                                    <option value="">Select Account Type</option>
                                    <option value="saving" {{ old('bank_account_type', $profile?->bank_account_type) === 'saving' ? 'selected' : '' }}>Savings</option>
                                    <option value="current" {{ old('bank_account_type', $profile?->bank_account_type) === 'current' ? 'selected' : '' }}>Current</option>
                                    <option value="salary" {{ old('bank_account_type', $profile?->bank_account_type) === 'salary' ? 'selected' : '' }}>Salary</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="profile-label">IFSC Code *</label>
                                <input type="text" name="ifsc_code" class="profile-control" value="{{ old('ifsc_code', $profile?->ifsc_code) }}" required {{ $disabled }}>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="profile-label">Bank Branch *</label>
                                <input type="text" name="bank_branch" class="profile-control" value="{{ old('bank_branch', $profile?->bank_branch) }}" required {{ $disabled }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="profile-card">
            <div class="card-head">
                <div class="card-title-wrap">
                    <div class="card-icon"><i class="fas fa-folder-open"></i></div>
                    <div>
                        <h2 class="card-title">Compliance Documents</h2>
                        <p class="card-subtitle">View uploaded documents and upload missing or rejected documents for verification.</p>
                    </div>
                </div>
                <span id="doc_items_count" class="badge-soft badge-pending">{{ $documentTypes->count() ?? 0 }} Items</span>
            </div>
            <div class="card-body">
                <div class="doc-grid">
                    @forelse($documentTypes as $type)
                    @php
                    $doc = $uploadedDocuments->get($type->id);
                    $docStatus = $doc ? ($doc->verification_status ?? 'pending') : 'missing';
                    $statusClass = [
                    'missing' => 'badge-missing',
                    'pending' => 'badge-pending',
                    'verified' => 'badge-verified',
                    'rejected' => 'badge-rejected',
                    ][$docStatus] ?? 'badge-pending';
                    @endphp
                    <div class="doc-card" data-applies-to="{{ strtolower(trim($type->applies_to ?? '')) }}">
                        <div class="doc-top">
                            <div>
                                <div class="doc-name"><i class="fas fa-file-alt text-primary mr-1"></i>{{ $type->name }}</div>
                                <div class="mt-2">
                                    @if($type->is_mandatory)
                                    <span class="badge-soft badge-required">Required</span>
                                    @else
                                    <span class="badge-soft badge-optional">Optional</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge-soft {{ $statusClass }}" id="doc_badge_{{ $type->id }}">
                                @if($docStatus === 'verified') <i class="fas fa-lock"></i> Verified
                                @elseif($docStatus === 'pending') <i class="fas fa-hourglass-half"></i> Pending
                                @elseif($docStatus === 'rejected') <i class="fas fa-exclamation-triangle"></i> Rejected
                                @else <i class="fas fa-times-circle"></i> Missing
                                @endif
                            </span>
                        </div>

                        <div id="doc_file_container_{{ $type->id }}">
                            @if($doc)
                            <div class="doc-file">
                                <span class="doc-file-name"><i class="fas fa-paperclip mr-1"></i>{{ $doc->file_original_name ?? basename($doc->file_path) }}</span>
                                <a href="{{ route('hrms.documents.file', ['path' => $doc->file_path]) }}" target="_blank" class="profile-btn" style="min-height:30px;padding:4px 10px;font-size:11px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                            @else
                            <div class="doc-file">
                                <span class="doc-file-name text-muted"><i class="fas fa-info-circle mr-1"></i>No file uploaded yet</span>
                            </div>
                            @endif
                        </div>

                        @if($doc && $doc->rejection_reason)
                        <div class="alert alert-danger mb-0 py-2 px-3" style="font-size:12px;">
                            <strong>Reason:</strong> {{ $doc->rejection_reason }}
                        </div>
                        @endif

                        @if(!$isReadOnly)
                        <div class="doc-actions mt-1">
                            <input type="file" id="file_{{ $type->id }}" class="d-none" onchange="uploadDoc({{ $type->id }})">
                            <button type="button" class="profile-btn profile-btn-soft w-100" onclick="document.getElementById('file_{{ $type->id }}').click()" id="upload_btn_{{ $type->id }}">
                                <i class="fas fa-upload"></i> {{ $doc ? 'Re-upload Document' : 'Upload Document' }}
                            </button>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="doc-card" style="grid-column: 1 / -1;">
                        <div class="text-center text-muted py-3" style="font-weight:800;">No compliance documents required.</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="profile-submit-bar mt-4">
            <div class="profile-submit-inner">
                <div>
                    <div class="fixed-submit-title">Ready to submit your profile?</div>
                    <div class="fixed-submit-subtitle text-danger">* Save profile fields and upload mandatory documents before submitting.</div>
                </div>
                <button type="button" onclick="document.getElementById('profileDetailsForm').submit()" class="profile-btn profile-btn-primary" {{ $isReadOnly ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane"></i> Submit for Verification
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleExperience(val) {
        var expWrapper = document.getElementById('experience_years_wrapper');
        var expInput = document.getElementById('total_experience');
        if (!expWrapper || !expInput) return;

        if (val === 'fresher') {
            expWrapper.style.display = 'none';
            expInput.value = '0';
        } else {
            expWrapper.style.display = 'block';
            if (expInput.value === '0') {
                expInput.value = '';
            }
        }

        // Show/hide document cards based on experience type
        var docCards = document.querySelectorAll('.doc-card');
        var visibleCount = 0;
        docCards.forEach(function(card) {
            var appliesTo = card.getAttribute('data-applies-to') || '';
            if (val === 'fresher') {
                if (appliesTo === 'experienced' || appliesTo === 'experience' || appliesTo === 'exp') {
                    card.style.display = 'none';
                } else {
                    card.style.display = 'block';
                    visibleCount++;
                }
            } else {
                card.style.display = 'block';
                visibleCount++;
            }
        });

        var countBadge = document.getElementById('doc_items_count');
        if (countBadge) {
            countBadge.textContent = visibleCount + ' Items';
        }
    }
    toggleExperience('{{ old("experience_type", $profile?->experience_type ?? "fresher") }}');

    function uploadDoc(typeId) {
        let fileInput = document.getElementById('file_' + typeId);
        if (!fileInput.files.length) return;

        let formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('document_type_id', typeId);

        let url = `{{ route('hrms.employee.documents.upload') }}`;

        let btn = document.getElementById('upload_btn_' + typeId);
        let originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        btn.disabled = true;

        fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let badge = document.getElementById('doc_badge_' + typeId);
                    badge.className = 'badge-soft badge-pending';
                    badge.innerHTML = '<i class="fas fa-hourglass-half"></i> Pending';

                    let fileContainer = document.getElementById('doc_file_container_' + typeId);
                    fileContainer.innerHTML = `
                    <div class="doc-file">
                        <span class="doc-file-name"><i class="fas fa-paperclip mr-1"></i>${fileInput.files[0].name.substring(0, 30)}...</span>
                        <span class="badge badge-success px-2 py-1" style="font-size:10px;">New</span>
                    </div>
                `;

                    btn.innerHTML = '<i class="fas fa-upload"></i> Re-upload Document';
                    btn.disabled = false;
                } else {
                    alert(data.message || 'Error uploading document');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during upload.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
    }
</script>
@endsection
