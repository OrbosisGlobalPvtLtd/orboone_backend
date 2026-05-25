@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Edit Profile')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-rose: #EC4E74;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    html,
    body {
        overflow-x: hidden !important;
    }

    .profile-page {
        min-height: calc(100vh - 90px);
        padding: 24px !important;
        background: var(--orb-bg);
        overflow-x: hidden !important;
        width: 100%;
    }

    .profile-container {
        max-width: 1180px;
        width: 100%;
        margin: 0 auto;
        overflow-x: visible !important;
    }

    .profile-header {
        border-radius: 26px !important;
        padding: 28px !important;
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        box-shadow: 0 14px 35px rgba(75, 0, 232, .15) !important;
        margin-bottom: 24px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
    }

    .profile-title {
        margin: 0 !important;
        color: #fff !important;
        font-size: 1.65rem !important;
        font-weight: 950 !important;
    }

    .profile-subtitle {
        margin: 6px 0 0 !important;
        color: rgba(255, 255, 255, .86) !important;
        font-size: .88rem !important;
        font-weight: 700 !important;
    }

    .profile-code {
        background: rgba(255, 255, 255, .15) !important;
        border: 1px solid rgba(255, 255, 255, .25) !important;
        color: #fff !important;
        backdrop-filter: blur(8px) !important;
        border-radius: 999px !important;
        padding: 8px 16px !important;
        font-size: 13px !important;
        font-weight: 900 !important;
        white-space: nowrap;
    }

    .profile-card {
        background: #fff !important;
        border: 1px solid var(--orb-border) !important;
        box-shadow: var(--orb-shadow) !important;
        border-radius: 22px !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }

    .profile-card-head {
        padding: 20px 24px !important;
        border-bottom: 1px solid var(--orb-border) !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        background: #fff !important;
    }

    .profile-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 15px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: var(--orb-primary) !important;
        background: rgba(75, 0, 232, .07) !important;
        font-size: 16px !important;
        flex: 0 0 auto;
    }

    .profile-card-head h5 {
        margin: 0 !important;
        color: var(--orb-text) !important;
        font-size: 1.05rem !important;
        font-weight: 950 !important;
    }

    .profile-card-head p {
        margin: 3px 0 0 !important;
        color: var(--orb-muted) !important;
        font-size: .78rem !important;
        font-weight: 650 !important;
    }

    .profile-card-body {
        padding: 24px !important;
    }

    .profile-field {
        margin-bottom: 18px;
    }

    .profile-field label {
        display: block !important;
        margin-bottom: 6px !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: .5px !important;
    }

    .required {
        color: var(--orb-rose);
        font-weight: 900;
    }

    .form-control,
    .form-select {
        min-height: 42px;
        border-radius: 12px !important;
        border: 1px solid #DDE3EE !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: #111827 !important;
        background: #fff !important;
        box-shadow: none !important;
        padding: 8px 14px !important;
    }

    textarea.form-control {
        min-height: 92px !important;
        resize: vertical;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--orb-secondary) !important;
        box-shadow: 0 0 0 3px rgba(134, 0, 238, .10) !important;
    }

    .current-file {
        margin-top: 6px;
        font-size: 12px;
        font-weight: 800;
    }

    .current-file a {
        color: var(--orb-primary);
        text-decoration: none;
    }

    .profile-actions-bar {
        position: sticky !important;
        bottom: 0 !important;
        z-index: 50 !important;
        width: 100% !important;
        background: rgba(255, 255, 255, .96) !important;
        backdrop-filter: blur(16px) !important;
        border: 1px solid #E7EAF3 !important;
        border-radius: 18px 18px 0 0 !important;
        padding: 14px 24px !important;
        box-shadow: 0 -12px 30px rgba(16, 24, 40, .08) !important;
        margin-top: 24px !important;
        overflow-x: hidden !important;
    }

    .profile-actions-inner {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        width: 100% !important;
    }

    .profile-actions-note {
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .profile-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        align-items: center;
        flex-shrink: 0;
    }

    .btn-soft,
    .btn-orb {
        border-radius: 12px !important;
        padding: 9px 18px !important;
        font-size: 13px !important;
        font-weight: 900 !important;
        min-height: 40px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    .btn-soft {
        background: #fff !important;
        border: 1px solid var(--orb-border) !important;
        color: #344054 !important;
        box-shadow: 0 6px 14px rgba(16, 24, 40, .05) !important;
    }

    .btn-orb {
        border: 0 !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        box-shadow: 0 10px 24px rgba(75, 0, 232, .22) !important;
    }

    .btn-orb:hover {
        background: linear-gradient(135deg, #3F00C8, #7300CC) !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .alert {
        border: 0;
        border-radius: 16px;
        box-shadow: var(--orb-shadow);
        font-weight: 650;
    }

    /*
        Table page ke liye important fix:
        horizontal scroll sirf table ke andar rahega,
        pagination scroll ke andar nahi jayega.
    */
    .table-responsive,
    .table-scroll,
    .data-table-scroll {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .table-responsive .pagination,
    .table-scroll .pagination,
    .data-table-scroll .pagination {
        display: none !important;
    }

    .pagination-wrapper,
    .pagination-area,
    .custom-pagination {
        width: 100%;
        overflow-x: visible !important;
        display: flex;
        justify-content: flex-end;
        margin-top: 16px;
    }

    @media(max-width:767px) {
        .profile-page {
            padding: 12px 12px 150px !important;
        }

        .profile-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            border-radius: 20px !important;
            padding: 20px !important;
        }

        .profile-code {
            width: 100% !important;
            text-align: center !important;
        }

        .profile-card {
            border-radius: 16px !important;
        }

        .profile-card-head {
            padding: 16px 20px !important;
        }

        .profile-card-body {
            padding: 20px !important;
        }

        .profile-actions-bar {
            left: 0 !important;
            width: 100% !important;
            padding: 12px !important;
        }

        .profile-actions-inner {
            flex-direction: column;
            align-items: stretch !important;
        }

        .profile-actions {
            width: 100%;
            flex-direction: column;
        }

        .profile-actions .btn-soft,
        .profile-actions .btn-orb {
            width: 100% !important;
        }
    }
</style>

@php
    $privateFileUrl = function ($path) {
        if (empty($path)) {
            return '#';
        }

        if (Route::has('hrms.documents.file')) {
            return route('hrms.documents.file', $path);
        }

        if (Route::has('hrms.employee.file')) {
            return route('hrms.employee.file', $path);
        }

        return asset('storage/' . $path);
    };
@endphp

<div class="profile-page" id="profilePage">
    <div class="profile-container" id="profileContainer">

        <div class="profile-header">
            <div>
                <h1 class="profile-title">Edit Employee Profile</h1>
                <p class="profile-subtitle">Update personal, education and bank profile details.</p>
            </div>

            <div class="profile-code">
                Employee ID: {{ $profile->employee_id }}
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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

        <form action="{{ route('hrms.employees.profile.update', $profile->employee_id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="profile-card">
                <div class="profile-card-head">
                    <div class="profile-icon"><i class="fas fa-user"></i></div>
                    <div>
                        <h5>Personal Details</h5>
                        <p>Profile image, resume and personal information.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 profile-field">
                            <label>Profile Image</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">

                            @if (!empty($profile->profile_image))
                                <div class="current-file">
                                    <a href="{{ $privateFileUrl($profile->profile_image) }}" target="_blank">
                                        View current image
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 profile-field">
                            <label>Resume File</label>
                            <input type="file" name="resume_file" class="form-control" accept=".pdf,.doc,.docx">

                            @if (!empty($profile->resume_file))
                                <div class="current-file">
                                    <a href="{{ $privateFileUrl($profile->resume_file) }}" target="_blank">
                                        View current resume
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 profile-field">
                            <label>DOB <span class="required">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control"
                                value="{{ old('date_of_birth', $profile->date_of_birth) }}" required>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 profile-field">
                            <label>Gender <span class="required">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $profile->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $profile->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $profile->gender) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-md-12 profile-field">
                            <label>Address <span class="required">*</span></label>
                            <textarea name="address" class="form-control" required>{{ old('address', $profile->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-head">
                    <div class="profile-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div>
                        <h5>Education & Experience</h5>
                        <p>Qualification, score and total work experience.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-6 profile-field">
                            <label>Experience Type <span class="required">*</span></label>
                            <select name="experience_type" id="experienceType" class="form-select" required>
                                <option value="">Select Experience Type</option>
                                <option value="fresher" {{ old('experience_type', $profile->experience_type) == 'fresher' ? 'selected' : '' }}>Fresher</option>
                                <option value="experienced" {{ old('experience_type', $profile->experience_type) == 'experienced' ? 'selected' : '' }}>Experienced</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-3 col-md-6 profile-field">
                            <label>Highest Qualification <span class="required">*</span></label>
                            <input type="text" name="highest_qualification"
                                value="{{ old('highest_qualification', $profile->highest_qualification) }}"
                                class="form-control" required>
                        </div>

                        <div class="col-xl-3 col-lg-3 col-md-6 profile-field">
                            <label>CGPA / Percentage <span class="required">*</span></label>
                            <input type="text" name="cgpa_percentage"
                                value="{{ old('cgpa_percentage', $profile->cgpa_percentage) }}"
                                class="form-control" required>
                        </div>

                        <div class="col-xl-3 col-lg-3 col-md-6 profile-field" id="total_experience_container">
                            <label>Total Experience <span class="required">*</span></label>
                            <input type="text" name="total_experience" id="total_experience"
                                value="{{ old('total_experience', $profile->total_experience) }}"
                                class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-head">
                    <div class="profile-icon"><i class="fas fa-building-columns"></i></div>
                    <div>
                        <h5>Bank Details</h5>
                        <p>Salary account and banking information.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                            <label>Account Holder Name <span class="required">*</span></label>
                            <input name="bank_holder_name"
                                value="{{ old('bank_holder_name', $profile->bank_holder_name) }}"
                                class="form-control" required>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                            <label>Bank Account No <span class="required">*</span></label>
                            <input name="bank_account_no"
                                value="{{ old('bank_account_no', $profile->bank_account_no) }}"
                                class="form-control" required>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                            <label>Account Type <span class="required">*</span></label>
                            <select name="bank_account_type" class="form-select" required>
                                <option value="">Select Account Type</option>
                                <option value="saving" {{ old('bank_account_type', $profile->bank_account_type) == 'saving' ? 'selected' : '' }}>Saving</option>
                                <option value="current" {{ old('bank_account_type', $profile->bank_account_type) == 'current' ? 'selected' : '' }}>Current</option>
                                <option value="salary" {{ old('bank_account_type', $profile->bank_account_type) == 'salary' ? 'selected' : '' }}>Salary</option>
                            </select>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                            <label>IFSC Code <span class="required">*</span></label>
                            <input name="ifsc_code" value="{{ old('ifsc_code', $profile->ifsc_code) }}"
                                class="form-control" required>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                            <label>Bank Branch <span class="required">*</span></label>
                            <input name="bank_branch" value="{{ old('bank_branch', $profile->bank_branch) }}"
                                class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-actions-bar" id="profileActionBar">
                <div class="profile-actions-inner">
                    <div class="profile-actions-note">
                        Save changes to update employee profile.
                    </div>

                    <div class="profile-actions">
                        <a href="{{ route('hrms.employees.index') }}" class="btn btn-soft">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-orb">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function toggleExperienceFields(value) {
            const container = document.getElementById('total_experience_container');
            const input = document.getElementById('total_experience');

            if (!container || !input) return;

            if (value === 'fresher') {
                container.style.display = 'none';
                input.value = '0';
                input.setAttribute('readonly', 'readonly');
                input.removeAttribute('required');
            } else if (value === 'experienced') {
                container.style.display = 'block';
                input.removeAttribute('readonly');
                input.setAttribute('required', 'required');

                if (input.value === '0') {
                    input.value = '';
                }
            } else {
                container.style.display = 'none';
                input.removeAttribute('required');
            }
        }

        const expSelect = document.getElementById('experienceType');

        if (expSelect) {
            toggleExperienceFields(expSelect.value);

            expSelect.addEventListener('change', function () {
                toggleExperienceFields(this.value);
            });
        }
    });
</script>
@endsection