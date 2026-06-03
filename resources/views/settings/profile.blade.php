@extends('layouts.panel', ['active' => 'profile'])

@section('page_title', 'My Profile')

@section('_content')
@php
    $employee = $profile->employee;
    $employeeProfile = $employee?->profile;
    $role = $profile->primaryRole ?: $profile->role;
    $manager = $employee?->reportingManager?->user;
    
    // Database fallback reads for safety
    $emergencyContact = $employeeProfile?->emergency_contact_number;
    $phone = $profile->phone;
    
    $imagePath = $employeeProfile?->profile_image;
    $avatarUrl = ($employee && $imagePath) ? route('employee.profile-image', ['employee' => $employee->id]) : null;
    $initials = collect(explode(' ', trim($profile->name ?? 'U')))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
    
    $status = $employeeProfile?->profile_status ?? 'incomplete';
    $isLocked = $status === 'submitted';
    
    // Whitelist check helper
    $isFieldEditable = function($fieldName) use ($editableFields, $isLocked) {
        if (!in_array($fieldName, $editableFields)) {
            return false;
        }
        if ($isLocked) {
            return false;
        }
        return true;
    };

    $fieldStatusAttr = function($fieldName) use ($isFieldEditable) {
        return 'readonly';
    };

    $fieldSelectStatusAttr = function($fieldName) use ($isFieldEditable) {
        return 'disabled';
    };

    $fieldStatusClass = function($fieldName) use ($isFieldEditable) {
        return $isFieldEditable($fieldName) ? '' : 'field-locked';
    };
@endphp

<style>
    :root {
        --set-primary: var(--orb-primary, #4B00E8);
        --set-secondary: var(--orb-secondary, #8600EE);
        --set-soft: #F4F2FF;
    }

    .field-locked {
        background-color: #F8F9FC !important;
        color: #667085 !important;
        border-color: #E7EAF3 !important;
        cursor: not-allowed;
    }
    .lock-icon {
        color: #98A2B3;
        font-size: 11px;
        margin-left: 6px;
    }

    .profile-page {
        min-height: calc(100vh - 90px);
        padding: 24px;
        background: #F6F7FB;
    }
    .profile-container {
        max-width: 1280px;
        margin: 0 auto;
    }
    .profile-hero {
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary));
        border-radius: 26px;
        padding: 32px;
        color: white;
        margin-bottom: 28px;
        box-shadow: 0 12px 35px rgba(75, 0, 232, 0.18);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }
    .profile-hero-left {
        display: flex;
        align-items: center;
        gap: 24px;
    }
    
    /* Interactive Avatar styles */
    .avatar-container {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 24px;
        overflow: hidden;
        border: 4px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 900;
        color: white;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .avatar-container:hover {
        border-color: white;
        transform: scale(1.03);
    }
    .profile-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.65);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .avatar-container:hover .avatar-overlay {
        opacity: 1;
    }

    .profile-hero-title {
        font-size: 28px;
        font-weight: 900;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .profile-hero-subtitle {
        margin: 6px 0 0;
        opacity: 0.95;
        font-size: 13px;
        font-weight: 700;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .status-pill-incomplete { background: #FFF0F0; color: #E53E3E; border: 1px solid #FED7D7; }
    .status-pill-submitted { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }
    .status-pill-approved { background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; }
    .status-pill-rejected { background: #FFF7ED; color: #EA580C; border: 1px solid #FFEDD5; }

    .stage-pill {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-grid-cols {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 28px;
    }
    .profile-card {
        background: white;
        border: 1px solid #E7EAF3;
        border-radius: 22px;
        padding: 28px;
        box-shadow: 0 10px 30px rgba(16, 24, 40, 0.03);
        margin-bottom: 28px;
    }
    .profile-card h3 {
        font-size: 16px;
        font-weight: 900;
        color: #101828;
        margin: 0 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 14px;
        border-bottom: 1px solid #F3F4F6;
    }
    .profile-card h3 i {
        color: var(--set-primary);
    }
    .profile-label {
        display: block;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
    }
    .profile-control {
        width: 100%;
        height: 42px;
        border-radius: 12px !important;
        border: 1px solid #E7EAF3 !important;
        background: #F9FAFB !important;
        color: #101828 !important;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 14px;
        transition: all 0.25s ease;
    }
    .profile-control:focus {
        border-color: var(--set-primary) !important;
        background: white !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.08) !important;
    }
    textarea.profile-control {
        height: auto;
        min-height: 96px;
    }
    .profile-btn {
        min-height: 40px;
        border-radius: 12px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid #E7EAF3;
        text-decoration: none !important;
        background: white;
        color: #101828;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .profile-btn:hover {
        background: #F9FAFB;
        border-color: #D0D5DD;
    }
    .profile-btn-primary {
        color: white !important;
        border-color: transparent;
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary));
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.2);
    }
    .profile-btn-primary:hover {
        opacity: 0.95;
        box-shadow: 0 6px 20px rgba(75, 0, 232, 0.25);
    }

    .profile-sidebar-item {
        padding: 14px 16px;
        border: 1px solid #E7EAF3;
        border-radius: 14px;
        background: #FCFCFD;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .profile-sidebar-value {
        color: #101828;
        font-size: 13px;
        font-weight: 800;
        text-align: right;
    }

    .stage-section-card {
        background: #F8F9FC;
        border: 1px dashed #D0D5DD;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
    }
    .stage-section-card h4 {
        font-size: 13px;
        font-weight: 900;
        color: var(--set-primary);
        margin: 0 0 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .document-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 20px;
    }

    @media(max-width: 991px) {
        .profile-grid-cols {
            grid-template-columns: 1fr;
        }
        .document-grid {
            grid-template-columns: 1fr;
        }
        .profile-hero {
            padding: 24px;
        }
    }
</style>

<!-- Hidden Profile Image Upload Form -->
@if(!$isLocked)
<form id="avatar_upload_form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="display: none;">
    @csrf
    @method('PUT')
    <input type="file" id="avatar_file_input" name="profile_image" accept="image/*" onchange="document.getElementById('avatar_upload_form').submit();">
</form>
@endif

<div class="profile-page">
    <div class="profile-container">
        
        <!-- Premium Hero -->
        <div class="profile-hero">
            <div class="profile-hero-left">
                <!-- Interactive Avatar Container -->
                <div class="avatar-container" onclick="@if(!$isLocked) document.getElementById('avatar_file_input').click() @endif">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $profile->name }}" class="profile-avatar-img">
                    @else
                        {{ $initials ?: 'U' }}
                    @endif
                    @if(!$isLocked)
                        <div class="avatar-overlay">
                            <i class="fas fa-camera text-white" style="font-size: 20px;"></i>
                            <span style="font-size: 9px; font-weight: 900; color: white; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Update</span>
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="profile-hero-title">{{ $profile->name }}</h1>
                    <p class="profile-hero-subtitle">
                        <i class="fas fa-id-badge mr-1"></i> {{ $employee?->employee_code ?? '-' }} • 
                        <i class="fas fa-envelope mr-1"></i> {{ $profile->email }} • 
                        <span class="stage-pill ml-2">{{ $employee?->employee_stage ?? 'Employee' }}</span>
                    </p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                @if($status === 'incomplete')
                    <span class="status-pill status-pill-incomplete"><i class="fas fa-exclamation-triangle mr-1"></i> Awaiting Details</span>
                @elseif($status === 'submitted')
                    <span class="status-pill status-pill-submitted"><i class="fas fa-clock mr-1"></i> Under Review</span>
                @elseif($status === 'approved')
                    <span class="status-pill status-pill-approved"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                @elseif($status === 'rejected')
                    <span class="status-pill status-pill-rejected"><i class="fas fa-times-circle mr-1"></i> Rejected</span>
                @endif

                @if(in_array($status, ['incomplete', 'rejected']))
                    <form action="{{ route('profile.submit') }}" method="POST" style="display: inline-block;">
                        @csrf
                        <button type="submit" class="profile-btn profile-btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit for Verification
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Success/Error Alerts -->
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 14px; font-weight: 800; font-size: 13px;">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 14px; font-weight: 800; font-size: 13px;">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if($status === 'rejected' && $employeeProfile?->rejection_reason)
            <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 16px; font-weight: 700; background: #FFF9E6; border-left: 5px solid #EF6C00;">
                <h5 class="text-warning mb-1" style="font-weight: 800; font-size: 14px;"><i class="fas fa-comment-dots mr-2"></i>HR Rejection Feedback:</h5>
                <p class="mb-0 text-dark" style="font-size: 13px;">{{ $employeeProfile->rejection_reason }}</p>
            </div>
        @endif

        <!-- Restructured Grid -->
        <div class="profile-grid-cols">
            
            <!-- Left Column: Forms -->
            <div>
                
                <!-- B. Personal Information -->
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="profile-card">
                        <h3>
                            <span><i class="fas fa-user-circle mr-2"></i>Personal Information</span>
                            @if(!$isLocked)
                                <div>
                                    <button type="button" class="btn-edit-section profile-btn" style="min-height: 32px; font-size: 11px; padding: 4px 10px;" onclick="enableSectionEdit(this)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <div class="btn-save-group" style="display: none; gap: 6px;">
                                        <button type="button" class="profile-btn" style="min-height: 32px; font-size: 11px; padding: 4px 10px;" onclick="cancelSectionEdit(this)">Cancel</button>
                                        <button type="submit" class="profile-btn profile-btn-primary" style="min-height: 32px; font-size: 11px; padding: 4px 10px;">Save</button>
                                    </div>
                                </div>
                            @endif
                        </h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="profile-control section-editable {{ $fieldStatusClass('date_of_birth') }}" value="{{ old('date_of_birth', $employeeProfile?->date_of_birth ? \Carbon\Carbon::parse($employeeProfile->date_of_birth)->format('Y-m-d') : '') }}" {{ $fieldStatusAttr('date_of_birth') }}>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Gender</label>
                                <select name="gender" class="profile-control section-editable {{ $fieldStatusClass('gender') }}" {{ $fieldSelectStatusAttr('gender') }}>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $employeeProfile?->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $employeeProfile?->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $employeeProfile?->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="profile-label">Permanent Address</label>
                                <textarea name="address" class="profile-control section-editable {{ $fieldStatusClass('address') }}" {{ $fieldStatusAttr('address') }} placeholder="Enter permanent address">{{ old('address', $employeeProfile?->address) }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Highest Qualification</label>
                                <input type="text" name="highest_qualification" class="profile-control section-editable {{ $fieldStatusClass('highest_qualification') }}" value="{{ old('highest_qualification', $employeeProfile?->highest_qualification) }}" {{ $fieldStatusAttr('highest_qualification') }} placeholder="e.g. Master of Science">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">CGPA / Percentage</label>
                                <input type="text" name="cgpa_percentage" class="profile-control section-editable {{ $fieldStatusClass('cgpa_percentage') }}" value="{{ old('cgpa_percentage', $employeeProfile?->cgpa_percentage) }}" {{ $fieldStatusAttr('cgpa_percentage') }} placeholder="e.g. 9.2 or 92%">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Experience Type</label>
                                <select name="experience_type" id="experience_type" class="profile-control section-editable {{ $fieldStatusClass('experience_type') }}" {{ $fieldSelectStatusAttr('experience_type') }}>
                                    <option value="fresher" {{ old('experience_type', $employeeProfile?->experience_type ?? 'fresher') === 'fresher' ? 'selected' : '' }}>Fresher</option>
                                    <option value="experienced" {{ old('experience_type', $employeeProfile?->experience_type) === 'experienced' ? 'selected' : '' }}>Experienced</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="experience_years_wrapper">
                                <label class="profile-label">Total Experience (Years)</label>
                                <input type="text" name="total_experience" class="profile-control section-editable {{ $fieldStatusClass('total_experience') }}" value="{{ old('total_experience', $employeeProfile?->total_experience) }}" {{ $fieldStatusAttr('total_experience') }} placeholder="e.g. 3.5 Years">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="profile-label">Resume / CV File</label>
                                @if($employeeProfile?->resume_file)
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2 bg-light" style="border-radius: 12px; border-color: #E7EAF3 !important;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-file-pdf text-danger" style="font-size: 20px;"></i>
                                            <span style="font-weight: 700; font-size: 13px;">Resume/CV Uploaded</span>
                                        </div>
                                        <a href="{{ route('hrms.documents.file', $employeeProfile->resume_file) }}" target="_blank" class="profile-btn" style="min-height: 28px; font-size: 11px; padding: 4px 8px;">
                                            <i class="fas fa-eye text-primary"></i> View
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="resume_file" class="profile-control section-editable {{ $fieldStatusClass('resume_file') }}" accept=".pdf,.doc,.docx" {{ $fieldStatusAttr('resume_file') }}>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- C. Contact Information -->
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="profile-card">
                        <h3>
                            <span><i class="fas fa-phone mr-2"></i>Contact Information</span>
                            @if(!$isLocked)
                                <div>
                                    <button type="button" class="btn-edit-section profile-btn" style="min-height: 32px; font-size: 11px; padding: 4px 10px;" onclick="enableSectionEdit(this)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <div class="btn-save-group" style="display: none; gap: 6px;">
                                        <button type="button" class="profile-btn" style="min-height: 32px; font-size: 11px; padding: 4px 10px;" onclick="cancelSectionEdit(this)">Cancel</button>
                                        <button type="submit" class="profile-btn profile-btn-primary" style="min-height: 32px; font-size: 11px; padding: 4px 10px;">Save</button>
                                    </div>
                                </div>
                            @endif
                        </h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Email Address <i class="fas fa-lock lock-icon"></i></label>
                                <input type="email" class="profile-control field-locked" value="{{ $profile->email }}" readonly disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Phone Number <i class="fas fa-lock lock-icon"></i></label>
                                <input type="text" class="profile-control field-locked" value="{{ $phone }}" readonly disabled>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="profile-label">Current Address</label>
                                <textarea name="address" class="profile-control section-editable {{ $fieldStatusClass('address') }}" {{ $fieldStatusAttr('address') }} placeholder="Enter current address">{{ old('address', $employeeProfile?->address) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="profile-label">Emergency Contact Number</label>
                                <input type="text" name="emergency_contact_number" class="profile-control section-editable {{ $fieldStatusClass('emergency_contact_number') }}" value="{{ old('emergency_contact_number', $emergencyContact) }}" {{ $fieldStatusAttr('emergency_contact_number') }} placeholder="Enter emergency contact number">
                            </div>
                        </div>
                    </div>
                </form>

                <!-- F. Bank Details -->
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="profile-card">
                        <h3>
                            <span><i class="fas fa-credit-card mr-2"></i>Bank Details</span>
                            @if(!$isLocked)
                                <div>
                                    <button type="button" class="btn-edit-section profile-btn" style="min-height: 32px; font-size: 11px; padding: 4px 10px;" onclick="enableSectionEdit(this)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <div class="btn-save-group" style="display: none; gap: 6px;">
                                        <button type="button" class="profile-btn" style="min-height: 32px; font-size: 11px; padding: 4px 10px;" onclick="cancelSectionEdit(this)">Cancel</button>
                                        <button type="submit" class="profile-btn profile-btn-primary" style="min-height: 32px; font-size: 11px; padding: 4px 10px;">Save</button>
                                    </div>
                                </div>
                            @endif
                        </h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Account Holder Name</label>
                                <input type="text" name="bank_holder_name" class="profile-control section-editable {{ $fieldStatusClass('bank_holder_name') }}" value="{{ old('bank_holder_name', $employeeProfile?->bank_holder_name) }}" {{ $fieldStatusAttr('bank_holder_name') }} placeholder="Enter bank holder name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Bank Account Number</label>
                                <input type="text" name="bank_account_no" class="profile-control section-editable {{ $fieldStatusClass('bank_account_no') }}" value="{{ old('bank_account_no', $employeeProfile?->bank_account_no) }}" {{ $fieldStatusAttr('bank_account_no') }} placeholder="Enter bank account number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">Account Type</label>
                                <select name="bank_account_type" class="profile-control section-editable {{ $fieldStatusClass('bank_account_type') }}" {{ $fieldSelectStatusAttr('bank_account_type') }}>
                                    <option value="">Select Account Type</option>
                                    <option value="Savings" {{ old('bank_account_type', $employeeProfile?->bank_account_type) === 'Savings' ? 'selected' : '' }}>Savings</option>
                                    <option value="Current" {{ old('bank_account_type', $employeeProfile?->bank_account_type) === 'Current' ? 'selected' : '' }}>Current</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="profile-label">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="profile-control section-editable {{ $fieldStatusClass('ifsc_code') }}" value="{{ old('ifsc_code', $employeeProfile?->ifsc_code) }}" {{ $fieldStatusAttr('ifsc_code') }} placeholder="e.g. SBIN0001234">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="profile-label">Bank Name & Branch</label>
                                <input type="text" name="bank_branch" class="profile-control section-editable {{ $fieldStatusClass('bank_branch') }}" value="{{ old('bank_branch', $employeeProfile?->bank_branch) }}" {{ $fieldStatusAttr('bank_branch') }} placeholder="Enter bank and branch details">
                            </div>
                        </div>
                    </div>
                </form>

            </div>

            <!-- Right Column: Official Details -->
            <div>
                
                <!-- D. Job Assignment -->
                <div class="profile-card">
                    <h3><span><i class="fas fa-briefcase mr-2"></i>Job Assignment</span></h3>
                    
                    <div class="profile-sidebar-item">
                        <span class="profile-label">Employee ID <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value">{{ $employee?->employee_code ?? '-' }}</div>
                    </div>
                    
                    <div class="profile-sidebar-item">
                        <span class="profile-label">Department <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value">{{ $employee?->department?->name ?? '-' }}</div>
                    </div>

                    <div class="profile-sidebar-item">
                        <span class="profile-label">Designation <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value">{{ $employee?->designation?->name ?? '-' }}</div>
                    </div>

                    <div class="profile-sidebar-item">
                        <span class="profile-label">Reporting Manager <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value">{{ $manager?->name ?? '-' }}</div>
                    </div>

                    <div class="profile-sidebar-item">
                        <span class="profile-label">Work Location <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value">{{ $employee?->work_location ?? 'Main Headquarters' }}</div>
                    </div>

                    <div class="profile-sidebar-item">
                        <span class="profile-label">Work Mode <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value" style="text-transform: capitalize;">{{ $employee?->work_mode ?? '-' }}</div>
                    </div>

                    <div class="profile-sidebar-item">
                        <span class="profile-label">Joining Date <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value">{{ $employee?->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-M-Y') : '-' }}</div>
                    </div>

                    <div class="profile-sidebar-item">
                        <span class="profile-label">Employment Status <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value" style="text-transform: capitalize;">{{ $employee?->employment_status ?? '-' }}</div>
                    </div>

                    <div class="profile-sidebar-item">
                        <span class="profile-label">Employment Type <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value" style="text-transform: capitalize;">{{ str_replace('_', ' ', $employee?->employment_type ?? '-') }}</div>
                    </div>

                    <div class="profile-sidebar-item">
                        <span class="profile-label">Role / Grade <i class="fas fa-lock lock-icon"></i></span>
                        <div class="profile-sidebar-value">{{ $role?->name ?? '-' }}</div>
                    </div>
                </div>

                <!-- E. Lifecycle / Stage Details -->
                <div class="profile-card">
                    <h3><span><i class="fas fa-history mr-2"></i>Lifecycle / Stage Details</span></h3>

                    @php
                        $stage = strtolower($employee?->employee_stage ?? '');
                        $hasInternship = !empty($employee->internship_start_date);
                        $hasProbation = !empty($employee->probation_start_date);
                        $isConfirmed = $employee?->is_permanent || !empty($employee->confirmation_date);
                    @endphp

                    @if($hasInternship)
                        <!-- Internship Section -->
                        <div class="stage-section-card">
                            <h4><i class="fas fa-graduation-cap"></i> Internship Details</h4>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Start Date:</span>
                                    <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($employee->internship_start_date)->format('d-M-Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">End Date:</span>
                                    <span class="font-weight-bold text-dark">{{ $employee->internship_end_date ? \Carbon\Carbon::parse($employee->internship_end_date)->format('d-M-Y') : '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Stipend / Paid:</span>
                                    <span class="font-weight-bold text-dark">{{ $employee->is_paid_intern ? 'Paid Internship' : 'Unpaid Internship' }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge badge-primary px-2 py-1" style="font-size: 10px; border-radius: 6px; text-transform: uppercase;">{{ $employee->internship_status ?? 'Active' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($hasProbation)
                        <!-- Probation Section -->
                        <div class="stage-section-card">
                            <h4><i class="fas fa-user-shield"></i> Probation Details</h4>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Start Date:</span>
                                    <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($employee->probation_start_date)->format('d-M-Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">End Date:</span>
                                    <span class="font-weight-bold text-dark">{{ $employee->probation_end_date ? \Carbon\Carbon::parse($employee->probation_end_date)->format('d-M-Y') : '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Probation Duration:</span>
                                    <span class="font-weight-bold text-dark">{{ $employee->probation_months ?? '6' }} Months</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge badge-warning px-2 py-1 text-dark" style="font-size: 10px; border-radius: 6px; text-transform: uppercase;">{{ $employee->probation_status ?? 'Under Probation' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($isConfirmed)
                        <!-- Confirmation Details -->
                        <div class="stage-section-card" style="background: #F0FDF4; border-color: #BBF7D0;">
                            <h4 style="color: #15803D;"><i class="fas fa-check-circle"></i> Permanent Confirmation</h4>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Confirmation Date:</span>
                                    <span class="font-weight-bold text-dark">{{ $employee->confirmation_date ? \Carbon\Carbon::parse($employee->confirmation_date)->format('d-M-Y') : '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Permanent Status:</span>
                                    <span class="badge badge-success px-2 py-1" style="font-size: 10px; border-radius: 6px; text-transform: uppercase;">Confirmed</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!empty($employee->relieving_date))
                        <!-- Notice / Relieving -->
                        <div class="stage-section-card" style="background: #FEF2F2; border-color: #FCA5A5;">
                            <h4 style="color: #991B1B;"><i class="fas fa-door-open"></i> Resignation / Notice Period</h4>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Relieving Date:</span>
                                    <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($employee->relieving_date)->format('d-M-Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 12px;">
                                    <span class="text-muted">Exit Status:</span>
                                    <span class="badge badge-danger px-2 py-1" style="font-size: 10px; border-radius: 6px; text-transform: uppercase;">Relieved</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!$hasInternship && !$hasProbation && !$isConfirmed && empty($employee->relieving_date))
                        <p class="text-muted text-center py-3" style="font-size: 12px; font-weight: 600;">No lifecycle stages registered.</p>
                    @endif
                </div>

            </div>
        </div>

        <!-- G. Compliance Documents (Full Width at Bottom) -->
        <div class="profile-card">
            <h3><span><i class="fas fa-file-signature mr-2"></i>Mandatory Compliance Documents</span></h3>
            <p class="text-muted mb-4" style="font-size: 12px; font-weight: 600;">Upload mandatory documents for profile verification. Verified documents will be locked securely.</p>

            <div class="document-grid">
                @php
                    $uploadedCount = 0;
                @endphp
                @foreach($documentTypes as $type)
                    @php
                        $doc = $employeeDocuments->get($type->id);
                    @endphp
                    @if($doc)
                        @php
                            $uploadedCount++;
                            $docStatus = $doc->verification_status;
                        @endphp
                        <div class="p-3 border rounded-lg" style="border-radius: 16px; border: 1px solid #E7EAF3; background: #FCFCFD; transition: all 0.2s ease;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-file-alt text-primary" style="font-size: 18px;"></i>
                                    <div>
                                        <span style="font-weight: 800; font-size: 13px; color: #101828;">{{ $type->name }}</span>
                                        @if($type->is_mandatory)
                                            <span class="badge badge-danger ml-1" style="font-size: 9px; font-weight: 800; background: #FEF3F2; color: #B42318; border: 1px solid #FECDCA; border-radius: 6px;">Required</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Status Badges -->
                                <div>
                                    @if($docStatus === 'missing')
                                        <span class="badge" style="font-size: 10px; font-weight: 800; background: #FFF0F0; color: #E53E3E; padding: 4px 10px; border-radius: 12px; border: 1px solid #FED7D7;"><i class="fas fa-times-circle mr-1"></i> Missing</span>
                                    @elseif($docStatus === 'pending')
                                        <span class="badge" style="font-size: 10px; font-weight: 800; background: #FFFBEB; color: #D97706; padding: 4px 10px; border-radius: 12px; border: 1px solid #FDE68A;"><i class="fas fa-hourglass-half mr-1"></i> Pending</span>
                                    @elseif($docStatus === 'verified')
                                        <span class="badge" style="font-size: 10px; font-weight: 800; background: #F0FDF4; color: #16A34A; padding: 4px 10px; border-radius: 12px; border: 1px solid #BBF7D0;"><i class="fas fa-lock mr-1"></i> Locked</span>
                                    @elseif($docStatus === 'rejected')
                                        <span class="badge" style="font-size: 10px; font-weight: 800; background: #FFF7ED; color: #EA580C; padding: 4px 10px; border-radius: 12px; border: 1px solid #FFEDD5;"><i class="fas fa-exclamation-triangle mr-1"></i> Rejected</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-2 p-2 rounded" style="background: #F1F5F9; border: 1px solid #E2E8F0;">
                                <span class="text-truncate text-muted" style="font-size: 11px; font-weight: 700; max-width: 220px;"><i class="fas fa-paperclip mr-1"></i> {{ $doc->file_original_name }}</span>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('hrms.documents.file', $doc->file_path) }}" target="_blank" class="profile-btn" style="min-height: 28px; font-size: 11px; padding: 4px 10px; background: white;">
                                        <i class="fas fa-eye text-primary"></i> View
                                    </a>
                                </div>
                            </div>

                            @if(($docStatus === 'missing' || $docStatus === 'rejected') && !$isLocked)
                                <!-- File Upload Actions -->
                                <form action="{{ route('hrms.documents.self.upload') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="document_type_id" value="{{ $type->id }}">
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.webp" style="font-size: 11px; width: 100%;">
                                        <button type="submit" class="profile-btn profile-btn-primary" style="min-height: 30px; font-size: 11px; padding: 4px 14px; white-space: nowrap;">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </div>
                                </form>
                            @elseif($docStatus === 'verified')
                                <div class="mt-3 text-success d-flex align-items-center gap-1" style="font-size: 11px; font-weight: 800;">
                                    <i class="fas fa-lock"></i> Verified & Locked
                                </div>
                            @elseif($isLocked)
                                <div class="mt-3 text-muted d-flex align-items-center gap-1" style="font-size: 11px; font-weight: 700;">
                                    <i class="fas fa-hourglass-half"></i> Under HR Verification Review
                                </div>
                            @endif
                            
                            @if($doc->rejection_reason)
                                <div class="mt-2 text-danger p-2 rounded" style="font-size: 11px; font-weight: 700; background: #FFF5F5; border-left: 3px solid #E53E3E;">
                                    <strong>Reason:</strong> {{ $doc->rejection_reason }}
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
                @if($uploadedCount === 0)
                    <p class="text-muted text-center py-4 w-100" style="font-size: 12px; font-weight: 600;">No compliance documents uploaded yet.</p>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
    function enableSectionEdit(button) {
        var card = button.closest('.profile-card');
        var editables = card.querySelectorAll('.section-editable');
        editables.forEach(function(el) {
            el.removeAttribute('readonly');
            el.removeAttribute('disabled');
            el.classList.remove('field-locked');
        });
        button.style.display = 'none';
        card.querySelector('.btn-save-group').style.display = 'inline-flex';
    }

    function cancelSectionEdit(button) {
        var card = button.closest('.profile-card');
        var editables = card.querySelectorAll('.section-editable');
        editables.forEach(function(el) {
            el.setAttribute('readonly', 'readonly');
            el.setAttribute('disabled', 'disabled');
            el.classList.add('field-locked');
        });
        card.querySelector('.btn-save-group').style.display = 'none';
        if (card.querySelector('.btn-edit-section')) {
            card.querySelector('.btn-edit-section').style.display = 'inline-flex';
        }
        
        // Reset the parent form to original values
        button.closest('form').reset();
        
        // Trigger experience display refresh in case toggled
        var expSelect = card.querySelector('#experience_type');
        if (expSelect) {
            var expWrapper = card.querySelector('#experience_years_wrapper');
            if (expSelect.value === 'fresher') {
                expWrapper.style.display = 'none';
            } else {
                expWrapper.style.display = 'block';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Experience type toggle logic
        var expSelect = document.getElementById('experience_type');
        var expWrapper = document.getElementById('experience_years_wrapper');

        function toggleExperienceYears() {
            if (expSelect && expWrapper) {
                if (expSelect.value === 'fresher') {
                    expWrapper.style.display = 'none';
                } else {
                    expWrapper.style.display = 'block';
                }
            }
        }

        if (expSelect && expWrapper) {
            expSelect.addEventListener('change', toggleExperienceYears);
            toggleExperienceYears();
        }
    });
</script>
@endsection
