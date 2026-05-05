@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'employees'])

@section('_content')
<style>
    :root {
        --orb-1: #4b00e8;
        --orb-2: #8600ee;
        --orb-3: #d400d5;
        --orb-4: #ec4e74;
        --orb-5: #ffb101;
        --glass-bg: rgba(255, 255, 255, 0.96);
        --card-shadow: 0 10px 40px rgba(75, 0, 232, 0.08);
        --card-radius: 24px;
    }

    body { background-color: #f8faff; }

    .form-card { 
        border-radius: var(--card-radius); 
        border: 1px solid rgba(255, 255, 255, 0.4); 
        box-shadow: var(--card-shadow); 
        overflow: hidden; 
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        margin-bottom: 2.5rem;
        transition: all 0.4s ease;
    }

    .section-title {
        color: var(--orb-1);
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 2px;
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
        position: relative;
    }
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 80px;
        height: 2px;
        background: linear-gradient(90deg, var(--orb-1), var(--orb-2));
    }
    .section-title i { margin-right: 12px; font-size: 1.1rem; color: var(--orb-2); }

    .form-label { 
        font-weight: 700; 
        color: #64748b; 
        font-size: 0.72rem; 
        text-transform: uppercase; 
        margin-bottom: 10px; 
        display: block; 
        letter-spacing: 0.8px;
    }

    .form-control, .form-select {
        border-radius: 14px;
        padding: 12px 18px;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.92rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: #fff;
        color: #1e293b;
    }
    .form-control:focus, .form-select:focus { 
        border-color: var(--orb-1); 
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1);
        transform: translateY(-2px);
    }

    .form-group { margin-bottom: 2rem; }
    
    .file-drop {
        border: 2px dashed #e2e8f0;
        border-radius: 20px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        transition: all 0.4s;
        cursor: pointer;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 140px;
    }
    .file-drop:hover {
        border-color: var(--orb-1);
        background: #fdfdff;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.05);
    }
    
    .btn-brand {
        background: linear-gradient(135deg, var(--orb-1) 0%, var(--orb-2) 100%);
        color: white !important;
        border: none;
        border-radius: 15px;
        padding: 16px 35px;
        font-weight: 700;
        letter-spacing: 0.5px;
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.25);
        transition: all 0.3s;
    }
    .btn-brand:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(75, 0, 232, 0.35); }
    
    .dynamic-section { 
        background: #fcfaff; 
        border-radius: 18px; 
        padding: 25px; 
        border: 1px dashed var(--secondary-brand); 
        margin-bottom: 2rem;
        animation: fadeIn 0.5s ease-out;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .current-avatar-container {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto 1.5rem;
        border-radius: 25px;
        padding: 5px;
        background: linear-gradient(135deg, var(--primary-brand), var(--accent-brand));
        box-shadow: 0 10px 20px rgba(75, 0, 232, 0.15);
    }
    .current-avatar {
        width: 100%;
        height: 100%;
        border-radius: 20px;
        object-fit: cover;
        border: 4px solid #fff;
    }

    .input-group-text {
        border-radius: 12px 0 0 12px;
        background-color: #f7fafc;
        border-color: #e1e5eb;
        color: var(--primary-brand);
        font-weight: 700;
    }
    .input-group .form-control { border-radius: 0 12px 12px 0; }

    @media (max-width: 768px) {
        .container-fluid { padding: 1.5rem !important; }
        .btn-brand { width: 100%; margin-bottom: 10px; }
    }
</style>

<div class="container-fluid py-4 px-md-4">
    <div class="mb-5 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="font-weight-bold text-dark mb-1"><i class="fas fa-user-edit mr-2" style="color: var(--primary-brand);"></i> Modify Employee Profile</h4>
            <p class="text-muted small mb-0">Record: <span class="font-weight-bold text-dark">{{ $employee->name }}</span> | <span class="badge badge-light border">{{ $employee->employee_id }}</span></p>
        </div>
        <a href="{{ route('employees-data') }}" class="btn btn-white shadow-sm border px-4 py-2" style="border-radius: 12px; font-weight: 600;">
            <i class="fas fa-arrow-left mr-2"></i> Employee Directory
        </a>
    </div>

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-lg mb-4" style="border-radius: 15px; background: #fff5f5; color: #c53030;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('employees-data.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Left Column: Primary & Employment -->
            <div class="col-lg-8">
                <div class="card form-card">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- SECTION 1: GENERAL PROFILE -->
                        <div class="section-title"><i class="fas fa-id-card mr-3"></i> General Profile</div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->user->email) }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->employeeDetail->phone ?? '') }}" placeholder="e.g. +91 9876543210" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Emergency Contact Number</label>
                                    <input type="text" name="emergency_contact_number" class="form-control" value="{{ old('emergency_contact_number', $employee->employeeDetail->emergency_contact_number ?? '') }}" placeholder="Contact for emergencies">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">System Role <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id', $employee->user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- SECTION 2: EMPLOYMENT DETAILS -->
                        <div class="section-title mt-4"><i class="fas fa-briefcase mr-3"></i> Employment Assignment</div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                                    <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required onchange="toggleEmploymentFields()">
                                        <option value="Full-Time" {{ old('employment_type', $employee->employment_type) == 'Full-Time' ? 'selected' : '' }}>Full-Time</option>
                                        <option value="Intern" {{ old('employment_type', $employee->employment_type) == 'Intern' ? 'selected' : '' }}>Intern</option>
                                        <option value="Contract" {{ old('employment_type', $employee->employment_type) == 'Contract' ? 'selected' : '' }}>Contract</option>
                                        <option value="Freelancer" {{ old('employment_type', $employee->employment_type) == 'Freelancer' ? 'selected' : '' }}>Freelancer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Work Mode <span class="text-danger">*</span></label>
                                    <select name="employee_status" class="form-select @error('employee_status') is-invalid @enderror" required>
                                        <option value="WFO" {{ old('employee_status', $employee->employee_status) == 'WFO' ? 'selected' : '' }}>WFO (Work From Office)</option>
                                        <option value="WFH" {{ old('employee_status', $employee->employee_status) == 'WFH' ? 'selected' : '' }}>WFH (Work From Home)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select" required>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Designation <span class="text-danger">*</span></label>
                                    <select name="position_id" class="form-select" required>
                                        @foreach($positions as $pos)
                                            <option value="{{ $pos->id }}" {{ old('position_id', $employee->position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Reporting Manager</label>
                                    <select name="manager_id" class="form-select">
                                        <option value="">-- No Direct Head --</option>
                                        @foreach($managers as $m)
                                            <option value="{{ $m->id }}" {{ old('manager_id', $employee->manager_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">System Stage <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="Active" {{ old('status', $employee->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Probation" {{ old('status', $employee->status) == 'Probation' ? 'selected' : '' }}>Probation</option>
                                        <option value="Inactive" {{ old('status', $employee->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="Completed" {{ old('status', $employee->status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- DYNAMIC SECTIONS -->
                        <div id="intern_fields" class="dynamic-section" style="display:none;">
                            <h6 class="font-weight-bold mb-3" style="color: var(--primary-brand);"><i class="fas fa-graduation-cap mr-2"></i> Intern Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Internship Type</label>
                                    <select name="internship_type" id="internship_type" class="form-select">
                                        <option value="Paid Intern" {{ $employee->internship_type == 'Paid Intern' ? 'selected' : '' }}>Paid Intern</option>
                                        <option value="Unpaid Intern" {{ $employee->internship_type == 'Unpaid Intern' ? 'selected' : '' }}>Unpaid Intern</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Duration (Months)</label>
                                    <input type="number" name="internship_duration" id="internship_duration" class="form-control" value="{{ $employee->internship_duration }}" min="1" max="12">
                                </div>
                            </div>
                        </div>
                        <div id="fulltime_fields" class="dynamic-section" style="display:none;">
                            <h6 class="font-weight-bold mb-3" style="color: var(--secondary-brand);"><i class="fas fa-check-double mr-2"></i> Probation & Confirmation</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Probation Status</label>
                                    <select name="probation_status" id="probation_status" class="form-select" onchange="toggleProbationFields()">
                                        <option value="Probation" {{ $employee->probation_status == 'Probation' ? 'selected' : '' }}>In Probation</option>
                                        <option value="Permanent" {{ $employee->probation_status == 'Permanent' ? 'selected' : '' }}>Confirmed / Permanent</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="probation_date_field">
                                    <label class="form-label">Adjust Probation End Date</label>
                                    <input type="date" name="probation_end_date" class="form-control" value="{{ $employee->probation_end_date }}">
                                </div>
                            </div>
                        </div>
                        <!-- SECTION 3: TIMELINE & STATUS -->
                        <div class="section-title mt-4"><i class="fas fa-clock mr-3"></i> Timeline & Lifecycle</div>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_of_contract" class="form-control" value="{{ old('start_of_contract', $employee->start_of_contract) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Relieving Date (Optional)</label>
                                    <input type="date" name="end_of_contract" class="form-control" value="{{ old('end_of_contract', $employee->end_of_contract) }}">
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Employment Status <span class="text-danger">*</span></label>
                                    <select name="employment_status" class="form-select" required>
                                        <option value="Active" {{ old('employment_status', $employee->employment_status) == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Resigned" {{ old('employment_status', $employee->employment_status) == 'Resigned' ? 'selected' : '' }}>Resigned</option>
                                        <option value="Terminated" {{ old('employment_status', $employee->employment_status) == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card form-card">
                    <div class="card-body p-4 p-md-5">
                        <!-- SECTION 4: SALARY & BANKING -->
                        <div class="section-title"><i class="fas fa-money-check-alt mr-3"></i> Compensation & Banking</div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $employee->bank_name) }}" >
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Monthly Gross Salary</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text px-3">₹</span></div>
                                        <input type="number" name="actual_salary" class="form-control" value="{{ old('actual_salary', $employee->actual_salary) }}" step="0.01" >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $employee->account_number) }}" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                    <select name="account_type" class="form-select">
                                        <option value="Savings" {{ old('account_type', $employee->account_type) == 'Savings' ? 'selected' : '' }}>Savings Account</option>
                                        <option value="Current" {{ old('account_type', $employee->account_type) == 'Current' ? 'selected' : '' }}>Current Account</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="holder_name" class="form-control" value="{{ old('holder_name', $employee->holder_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc" class="form-control" value="{{ old('ifsc', $employee->ifsc_code) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                                    <input type="text" name="branch" class="form-control" value="{{ old('branch', $employee->branch_name) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Personal & Files -->
            <div class="col-lg-4">
                <div class="card form-card text-center">
                    <div class="card-body p-4">
                        <div class="section-title"><i class="fas fa-camera mr-2"></i> Profile Photo</div>
                        <div class="current-avatar-container">
                            @php
                                $photo = $employee->employeeDetail->photo ?? 'images/profile.png';
                                $photo = str_replace('public/', '', $photo);
                                if (Str::startsWith($photo, 'http')) {
                                    $finalUrl = $photo;
                                } elseif (Str::startsWith($photo, 'uploads/')) {
                                    $finalUrl = asset($photo);
                                } elseif ($photo && !in_array($photo, ['default_avatar.png', 'images/profile.png', 'profile.png'])) {
                                    $finalUrl = asset('storage/' . $photo);
                                } else {
                                    $finalUrl = asset('images/profile.png');
                                }
                            @endphp
                            <img src="{{ $finalUrl }}" 
                                 onerror="this.src='{{ asset('images/profile.png') }}'; this.onerror=null;" 
                                 class="current-avatar" 
                                 id="avatar_preview"
                                 alt="Profile Photo">
                        </div>
                        <div class="file-drop mb-3" onclick="document.getElementById('photo_input').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: var(--primary-brand); opacity: 0.5;"></i>
                            <span id="photo_name" class="font-weight-bold text-muted small">Replace PNG/JPG portrait</span>
                            <input type="file" id="photo_input" name="photo" class="d-none" accept="image/*"
                                   onchange="updateFileName(this, 'photo_name'); previewImage(this, 'avatar_preview');">
                        </div>
                        <div class="badge badge-primary py-2 px-3 shadow-sm" style="border-radius: 8px; background: var(--primary-brand); font-weight: 700;">{{ $employee->employee_id }}</div>
                    </div>
                </div>

                <div class="card form-card">
                    <div class="card-body p-4">
                        <div class="section-title"><i class="fas fa-user-tag mr-2"></i> Personal Bio</div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $employee->employeeDetail->date_of_birth ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="M" {{ old('gender', $employee->employeeDetail->gender ?? '') == 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ old('gender', $employee->employeeDetail->gender ?? '') == 'F' ? 'selected' : '' }}>Female</option>
                                <option value="O" {{ old('gender', $employee->employeeDetail->gender ?? '') == 'O' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Residential Address</label>
                            <textarea name="address" class="form-control" rows="3">{{ old('address', $employee->employeeDetail->address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card form-card">
                    <div class="card-body p-4">
                        <div class="section-title"><i class="fas fa-user-graduate mr-2"></i> Academic Records</div>
                        <div class="form-group">
                            <label class="form-label">Highest Qualification</label>
                            <input type="text" name="last_education" class="form-control" value="{{ old('last_education', $employee->employeeDetail->last_education ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CGPA / Percentage</label>
                            <input type="text" name="gpa" class="form-control" value="{{ old('gpa', $employee->employeeDetail->gpa ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Total Experience (Years)</label>
                            <input type="number" name="work_experience_in_years" class="form-control" value="{{ old('work_experience_in_years', $employee->employeeDetail->work_experience_in_years ?? 0) }}" min="0">
                        </div>
                        
                        <label class="form-label">Resume / CV (PDF)</label>
                        <div class="file-drop" onclick="document.getElementById('cv_input').click()">
                            <i class="fas fa-file-pdf fa-2x text-danger mb-2 opacity-75"></i>
                            <span id="cv_name" class="font-weight-bold text-muted small">{{ $employee->employeeDetail->cv ? 'Replace Professional CV' : 'Upload Professional CV' }}</span>
                            <input type="file" id="cv_input" name="cv" class="d-none" accept="application/pdf" onchange="updateFileName(this, 'cv_name')">
                        </div>
                        @if($employee->employeeDetail->cv)
                            @php
                                $cvPath = $employee->employeeDetail->cv;
                                $cvPath = str_replace('public/', '', $cvPath);
                                $cvUrl = Str::startsWith($cvPath, 'http') ? $cvPath : (Str::startsWith($cvPath, 'uploads/') ? asset($cvPath) : asset('storage/' . $cvPath));
                            @endphp
                            <div class="mt-3 text-center">
                                <a href="{{ $cvUrl }}" target="_blank" class="font-weight-bold" style="color: var(--primary-brand); font-size: 0.75rem;">
                                    <i class="fas fa-external-link-alt mr-1"></i> View Current Resume
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card form-card" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
                    <div class="card-body p-4">
                        <div class="section-title"><i class="fas fa-key mr-2"></i> Update Credentials</div>
                        <div class="form-group mb-0">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                        <p class="extra-small text-muted mt-2 italic mb-0">Min. 8 characters if changing.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-brand btn-block py-3 font-weight-bold shadow-lg">
                        <i class="fas fa-save mr-2"></i> SYNC CHANGES
                    </button>
                    <a href="{{ route('employees-data') }}" class="btn btn-link btn-block text-muted font-weight-bold mt-2">
                        <i class="fas fa-times mr-1"></i> Discard and Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function updateFileName(input, targetId) {
        if (input.files[0]) {
            document.getElementById(targetId).textContent = input.files[0].name;
            const dropZone = document.getElementById(targetId).closest('.file-drop');
            if (dropZone) {
                dropZone.style.borderColor = 'var(--orb-1)';
                dropZone.style.background = '#f0f4ff';
                dropZone.style.boxShadow = '0 10px 20px rgba(75, 0, 232, 0.05)';
            }
        }
    }

    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(previewId);
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleEmploymentFields() {
        const type = document.getElementById('employment_type').value;
        const intern = document.getElementById('intern_fields');
        const fulltime = document.getElementById('fulltime_fields');
        
        if(intern) intern.style.display = (type === 'Intern') ? 'block' : 'none';
        if(fulltime) fulltime.style.display = (type === 'Full-Time') ? 'block' : 'none';
    }

    function toggleProbationFields() {
        const status = document.getElementById('probation_status').value;
        const dateField = document.getElementById('probation_date_field');
        if(dateField) dateField.style.display = (status === 'Probation') ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleEmploymentFields();
        toggleProbationFields();

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> SYNCING...';

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show border-0 shadow-lg fixed-top m-4';
                        alert.style.zIndex = '9999';
                        alert.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="mr-3 bg-success text-white p-2 rounded-circle">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div><strong>Updated!</strong> ${data.message}</div>
                            </div>
                        `;
                        document.body.appendChild(alert);
                        
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Update failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                    
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show border-0 shadow-lg fixed-top m-4';
                    alert.style.zIndex = '9999';
                    alert.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="mr-3 bg-danger text-white p-2 rounded-circle">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div><strong>Error!</strong> ${error.message}</div>
                        </div>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    `;
                    document.body.appendChild(alert);
                });
            });
        }
    });
</script>
@endsection