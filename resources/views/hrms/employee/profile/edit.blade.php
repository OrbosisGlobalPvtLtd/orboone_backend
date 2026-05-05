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
            --orb-soft: #F4F2FF;
            --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
        }

        .profile-page {
            min-height: calc(100vh - 90px);
            padding: 16px 10px 30px;
            background: var(--orb-bg);
        }

        .profile-container {
            max-width: 1180px;
            margin: 0 auto;
        }

        .profile-header,
        .profile-card,
        .profile-actions-bar {
            background: #fff;
            border: 1px solid var(--orb-border);
            box-shadow: var(--orb-shadow);
        }

        .profile-header {
            border-radius: 18px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .profile-title {
            margin: 0;
            color: var(--orb-text);
            font-size: 24px;
            font-weight: 900;
        }

        .profile-subtitle {
            margin: 4px 0 0;
            color: var(--orb-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .profile-code {
            border-radius: 14px;
            padding: 10px 14px;
            background: var(--orb-soft);
            color: var(--orb-primary);
            border: 1px solid rgba(75, 0, 232, .12);
            font-size: 13px;
            font-weight: 900;
            white-space: nowrap;
        }

        .profile-card {
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .profile-card-head {
            padding: 14px 16px;
            border-bottom: 1px solid #EEF1F6;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--orb-primary);
            background: var(--orb-soft);
        }

        .profile-card-head h5 {
            margin: 0;
            color: var(--orb-text);
            font-size: 15px;
            font-weight: 900;
        }

        .profile-card-head p {
            margin: 2px 0 0;
            color: var(--orb-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .profile-card-body {
            padding: 16px;
        }

        .profile-field {
            margin-bottom: 14px;
        }

        .profile-field label {
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

        textarea.form-control {
            min-height: 88px;
            resize: vertical;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--orb-secondary);
            box-shadow: 0 0 0 .16rem rgba(134, 0, 238, .10);
        }

        .current-file {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
        }

        .current-file a {
            color: var(--orb-primary);
        }

        .profile-actions-bar {
            position: sticky;
            bottom: 0;
            z-index: 30;
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, .96);
            border-radius: 18px;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .profile-actions-note {
            color: var(--orb-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .profile-actions {
            display: flex;
            gap: 9px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn-soft,
        .btn-orb {
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

        .alert {
            border: 0;
            border-radius: 16px;
            box-shadow: var(--orb-shadow);
            font-weight: 650;
        }

        @media(max-width:767px) {
            .profile-page {
                padding: 10px 8px 24px;
            }

            .profile-header {
                flex-direction: column;
                align-items: flex-start;
                border-radius: 16px;
                padding: 14px;
            }

            .profile-title {
                font-size: 21px;
            }

            .profile-code {
                width: 100%;
                text-align: center;
            }

            .profile-card,
            .profile-actions-bar {
                border-radius: 16px;
            }

            .profile-card-head,
            .profile-card-body {
                padding: 14px;
            }

            .profile-actions-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .profile-actions {
                width: 100%;
            }

            .profile-actions .btn,
            .profile-actions a {
                flex: 1 1 100%;
                text-align: center;
            }
        }
    </style>

    <div class="profile-page">
        <div class="profile-container">

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

            <form action="{{ route('hrms.employees.profile.update', $profile->employee_id) }}" method="POST"
                enctype="multipart/form-data">
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
                                        <a href="{{ asset('storage/' . $profile->profile_image) }}" target="_blank">
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
                                        <a href="{{ asset('storage/' . $profile->resume_file) }}" target="_blank">
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
                                    <option value="male"
                                        {{ old('gender', $profile->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female"
                                        {{ old('gender', $profile->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other"
                                        {{ old('gender', $profile->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="col-md-12 profile-field">
                                <label>Address <span class="required">*</span></label>
                                <textarea name="address" class="form-control" placeholder="Enter full address" required>{{ old('address', $profile->address) }}</textarea>
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
                            <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                                <label>Highest Qualification <span class="required">*</span></label>
                                <input type="text" name="highest_qualification"
                                    value="{{ old('highest_qualification', $profile->highest_qualification) }}"
                                    class="form-control" placeholder="e.g. B.Tech, MBA" required>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                                <label>CGPA / Percentage <span class="required">*</span></label>
                                <input type="text" name="cgpa_percentage"
                                    value="{{ old('cgpa_percentage', $profile->cgpa_percentage) }}" class="form-control"
                                    placeholder="e.g. 8.5 or 75%" required>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                                <label>Total Experience <span class="required">*</span></label>
                                <input type="text" name="total_experience"
                                    value="{{ old('total_experience', $profile->total_experience) }}" class="form-control"
                                    placeholder="e.g. 2 years" required>
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
                                    value="{{ old('bank_holder_name', $profile->bank_holder_name) }}" class="form-control"
                                    placeholder="Account holder name" required>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                                <label>Bank Account No <span class="required">*</span></label>
                                <input name="bank_account_no"
                                    value="{{ old('bank_account_no', $profile->bank_account_no) }}" class="form-control"
                                    placeholder="Account number" required>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                                <label>Account Type <span class="required">*</span></label>
                                <select name="bank_account_type" class="form-select" required>
                                    <option value="">Select Account Type</option>
                                    <option value="saving"
                                        {{ old('bank_account_type', $profile->bank_account_type) == 'saving' ? 'selected' : '' }}>
                                        Saving</option>
                                    <option value="current"
                                        {{ old('bank_account_type', $profile->bank_account_type) == 'current' ? 'selected' : '' }}>
                                        Current</option>
                                    <option value="salary"
                                        {{ old('bank_account_type', $profile->bank_account_type) == 'salary' ? 'selected' : '' }}>
                                        Salary</option>
                                </select>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                                <label>IFSC Code <span class="required">*</span></label>
                                <input name="ifsc_code" value="{{ old('ifsc_code', $profile->ifsc_code) }}"
                                    class="form-control" placeholder="IFSC code" required>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-6 profile-field">
                                <label>Bank Branch <span class="required">*</span></label>
                                <input name="bank_branch" value="{{ old('bank_branch', $profile->bank_branch) }}"
                                    class="form-control" placeholder="Bank branch" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-actions-bar">
                    <div class="profile-actions-note">
                        Save changes to update employee profile.
                    </div>

                    <div class="profile-actions">
                        <a href="{{ route('hrms.employees.index') }}" class="btn btn-soft">Cancel</a>
                        <button type="submit" class="btn btn-orb">
                            <i class="fas fa-save mr-1"></i> Update Profile
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection
