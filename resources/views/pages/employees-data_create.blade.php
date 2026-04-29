<!-- @extends('layouts.admin', ['accesses' => $accesses, 'active' => 'employees']) -->
@extends('layouts.panel')

@section('page_title', 'Employee Onboarding')
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
    .section-title i { margin-right: 12px; font-size: 1.1rem; color: var(--orb-3); }

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
    }
    .file-drop:hover {
        border-color: var(--orb-1);
        background: #fdfdff;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.05);
    }

    .btn-brand-lg {
        background: linear-gradient(135deg, var(--orb-1), var(--orb-2));
        color: white !important;
        border: none;
        border-radius: 18px;
        padding: 18px 40px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.25);
        transition: all 0.4s;
        width: 100%;
    }
    .btn-brand-lg:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 20px 40px rgba(75, 0, 232, 0.35);
        filter: brightness(1.1);
    }

    .id-badge {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 12px 24px;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        text-align: right;
        min-width: 180px;
    }
    .id-badge h2 {
        background: linear-gradient(135deg, var(--orb-1), var(--orb-3));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: 1px;
    }
</style>

<div class="container-fluid py-5 px-md-5">
    <div class="mb-5 d-flex align-items-center justify-content-between flex-wrap page-header">
        <div>
            <h1 class="font-weight-bold text-dark m-0" style="font-size: 2.2rem; letter-spacing: -1px;">
                <i class="fas fa-user-plus mr-3" style="color: var(--orb-3);"></i>Personnel Onboarding
            </h1>
            <p class="text-muted mt-2 font-weight-500" style="font-size: 1.1rem;">Initialize a new professional profile within the ecosystem.</p>
        </div>
        <div>
            <a href="{{ route('employees-data') }}" class="btn btn-white border shadow-sm px-4 py-2 mb-3 mb-md-0 mr-md-3" style="border-radius: 14px; font-weight: 700; color: #64748b;">
                <i class="fas fa-arrow-left mr-2"></i> Back to Directory
            </a>

            <div class="id-badge mt-4">
                <small class="uppercase font-weight-bold opacity-75 text-muted" style="letter-spacing: 1px; font-size: 0.65rem;">Auto-Generated ID</small>
                <h2 class="m-0 font-weight-bold">{{ $nextId }}</h2>
            </div>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-lg mb-4" style="border-radius: 15px; border-left: 5px solid #ec4e74 !important; background: white;">
            <div class="d-flex align-items-center p-2">
                <i class="fas fa-exclamation-circle text-danger mr-3 fa-lg"></i>
                <div class="font-weight-bold text-dark">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <form action="{{ route('employees-data.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Left Column: Primary & Employment -->
            <div class="col-lg-8">
                <div class="card form-card">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- SECTION 1: GENERAL PROFILE -->
                        <div class="section-title"><i class="fas fa-id-card mr-3"></i> General Profile</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="e.g. john@orbosis.com" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+91 XXXXX XXXXX" required>
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Emergency Contact Number</label>
                                    <input type="text" name="emergency_contact_number" class="form-control @error('emergency_contact_number') is-invalid @enderror" value="{{ old('emergency_contact_number') }}" placeholder="Contact for emergencies">
                                    @error('emergency_contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">System Role <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                        <option value="">-- Choose Access Level --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: EMPLOYMENT DETAILS -->
                        <div class="section-title mt-4"><i class="fas fa-briefcase mr-3"></i> Employment Assignment</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                                    <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required onchange="toggleEmploymentFields()">
                                        <option value="Full-Time" {{ old('employment_type') == 'Full-Time' ? 'selected' : '' }}>Full-Time Employee</option>
                                        <option value="Intern" {{ old('employment_type') == 'Intern' ? 'selected' : '' }}>Internship</option>
                                        <option value="Contract" {{ old('employment_type') == 'Contract' ? 'selected' : '' }}>Contractor</option>
                                        <option value="Freelancer" {{ old('employment_type') == 'Freelancer' ? 'selected' : '' }}>Freelancer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Work Mode <span class="text-danger">*</span></label>
                                    <select name="employee_status" class="form-select @error('employee_status') is-invalid @enderror" required>
                                        <option value="WFO" {{ old('employee_status') == 'WFO' ? 'selected' : '' }}>WFO (Work From Office)</option>
                                        <option value="WFH" {{ old('employee_status') == 'WFH' ? 'selected' : '' }}>WFH (Work From Home)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select" required>
                                        <option value="">-- Assign Department --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Designation <span class="text-danger">*</span></label>
                                    <select name="position_id" class="form-select" required>
                                        <option value="">-- Assign Job Title --</option>
                                        @foreach($positions as $pos)
                                            <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Reporting Manager</label>
                                    <select name="manager_id" class="form-select border-primary-soft">
                                        <option value="">-- No Direct Head/Admin --</option>
                                        @foreach($managers as $m)
                                            <option value="{{ $m->id }}" {{ old('manager_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Initial System State <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Probation" {{ old('status') == 'Probation' ? 'selected' : '' }} selected>In Probation</option>
                                        <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- DYNAMIC SECTIONS -->
                        <div id="intern_fields" class="dynamic-section" style="display:none; background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 2rem; border: 1px dashed #cbd5e1;">
                            <h6 class="font-weight-bold text-primary mb-3" style="letter-spacing: 0.5px;"><i class="fas fa-graduation-cap mr-2"></i> Internship Parameters</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Internship Type</label>
                                        <select name="internship_type" id="internship_type" class="form-select border-primary-soft">
                                            <option value="Paid Intern">Paid Internship</option>
                                            <option value="Unpaid Intern">Unpaid Internship</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Duration <small>(Months)</small></label>
                                        <div class="input-group">
                                            <input type="number" name="internship_duration" id="internship_duration" class="form-control" value="3" min="1" max="24" oninput="calculateDates()" style="font-weight: bold; text-align: center;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="form-label text-primary">Calculated End Date</label>
                                        <input type="date" id="intern_end_date" class="form-control text-primary font-weight-bold" readonly style="background-color: #e2e8f0; border-color: #cbd5e1;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: TIMELINE & STATUS -->
                        <div class="section-title mt-4"><i class="fas fa-clock mr-3"></i> Timeline & Lifecycle</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" id="label_start_date">Joining Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_of_contract" id="start_of_contract" class="form-control" value="{{ old('start_of_contract', date('Y-m-d')) }}" required onchange="calculateDates()">
                                </div>
                            </div>
                            <div class="col-md-4" id="relieving_date_container">
                                <div class="form-group">
                                    <label class="form-label">Relieving Date <small class="text-muted">(Optional)</small></label>
                                    <input type="date" name="end_of_contract" id="end_of_contract" class="form-control" value="{{ old('end_of_contract') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Employment Status <span class="text-danger">*</span></label>
                                    <select name="employment_status" class="form-select">
                                        <option value="Active" selected>Currently Employed</option>
                                        <option value="Resigned">Resigned</option>
                                        <option value="Terminated">Terminated</option>
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
                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" placeholder="e.g. HDFC Bank">
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Monthly Gross Salary</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text px-3">₹</span></div>
                                        <input type="number" name="actual_salary" class="form-control" value="{{ old('actual_salary') }}" step="0.01" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Account Type</label>
                                    <select name="account_type" class="form-select">
                                        <option value="Savings">Savings Account</option>
                                        <option value="Current">Current Account</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Account Holder Name</label>
                                    <input type="text" name="holder_name" class="form-control" value="{{ old('holder_name') }}" placeholder="As per bank records" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">IFSC Code</label>
                                    <input type="text" name="ifsc" class="form-control" value="{{ old('ifsc') }}" placeholder="e.g. HDFC0001234">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Branch Name</label>
                                    <input type="text" name="branch" class="form-control" value="{{ old('branch') }}" placeholder="e.g. Downtown Branch">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Personal & Files -->
            <div class="col-lg-4">
                <div class="card form-card mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="section-title"><i class="fas fa-camera mr-2"></i> Profile Photo</div>
                        <div class="file-drop mb-3" onclick="document.getElementById('photo_input').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: var(--primary-brand); opacity: 0.5;"></i>
                            <span id="photo_name" class="font-weight-bold text-muted small">Upload PNG/JPG portrait</span>
                            <input type="file" id="photo_input" name="photo" class="d-none" accept="image/*" onchange="updateFileName(this, 'photo_name')">
                        </div>
                        <p class="extra-small text-muted mb-0 italic">Recommended: 400x400px Square Image</p>
                    </div>
                </div>

                <div class="card form-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-title"><i class="fas fa-user-tag mr-2"></i> Personal Bio</div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                                <option value="O">Other</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Residential Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Full residential physical address">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card form-card mb-4">
                    <div class="card-body p-4">
                        <div class="section-title"><i class="fas fa-user-graduate mr-2"></i> Academic Records</div>
                        <div class="form-group">
                            <label class="form-label">Highest Qualification</label>
                            <input type="text" name="last_education" class="form-control" value="{{ old('last_education') }}" placeholder="e.g. Master's in CS" >
                        </div>
                        <div class="form-group">
                            <label class="form-label">CGPA / Percentage</label>
                            <input type="text" name="gpa" class="form-control" value="{{ old('gpa') }}" placeholder="e.g. 8.5/10">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Total Experience (Years)</label>
                            <input type="number" name="work_experience_in_years" class="form-control" value="{{ old('work_experience_in_years', 0) }}" min="0">
                        </div>
                        
                        <label class="form-label">Resume / CV (PDF)</label>
                        <div class="file-drop" onclick="document.getElementById('cv_input').click()">
                            <i class="fas fa-file-pdf fa-2x text-danger mb-2 opacity-75"></i>
                            <span id="cv_name" class="font-weight-bold text-muted small">Upload professional CV</span>
                            <input type="file" id="cv_input" name="cv" class="d-none" accept="application/pdf" onchange="updateFileName(this, 'cv_name')">
                        </div>
                    </div>
                </div>

                <div class="card form-card mb-4" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
                    <div class="card-body p-4">
                        <div class="section-title"><i class="fas fa-key mr-2"></i> Initial Access Credentials</div>
                        <div class="form-group mb-0">
                            <label class="form-label">Login Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                        </div>
                        <p class="extra-small text-muted mt-2 italic mb-0">Employee will be logged in with their email and this password.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-brand btn-block py-3 font-weight-bold shadow-lg">
                        <i class="fas fa-save mr-2"></i> COMPLETE ONBOARDING
                    </button>
                    <a href="{{ route('employees-data') }}" class="btn btn-link btn-block text-muted font-weight-bold mt-2">
                        <i class="fas fa-times mr-1"></i> Cancel and Discard
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
            const dropZone = document.getElementById(targetId).parentElement;
            dropZone.style.borderColor = 'var(--primary-brand)';
            dropZone.style.background = '#f0f4ff';
            dropZone.style.boxShadow = '0 10px 20px rgba(75, 0, 232, 0.05)';
        }
    }

    function toggleEmploymentFields() {
        const type = document.getElementById('employment_type').value;
        const internFields = document.getElementById('intern_fields');
        const relievingContainer = document.getElementById('relieving_date_container');
        const labelStart = document.getElementById('label_start_date');
        const relievingInput = document.getElementById('end_of_contract');
        
        if (type === 'Intern') {
            internFields.style.display = 'block';
            relievingContainer.style.display = 'none';
            labelStart.innerHTML = 'Start Date <span class="text-danger">*</span>';
            relievingInput.value = ''; // clear relieving date since it's an intern
        } else {
            // Full-Time or others
            internFields.style.display = 'none';
            relievingContainer.style.display = 'block';
            labelStart.innerHTML = 'Joining Date <span class="text-danger">*</span>';
        }
        
        calculateDates(); // Update in case dates were already set
    }

    function calculateDates() {
        const startInput = document.getElementById('start_of_contract').value;
        if (!startInput) return;
        
        const type = document.getElementById('employment_type').value;
        if (type === 'Intern') {
            const duration = parseInt(document.getElementById('internship_duration').value) || 0;
            
            // Intern logic: Add duration months
            const startDate = new Date(startInput);
            startDate.setMonth(startDate.getMonth() + duration);
            
            const year = startDate.getFullYear();
            const month = String(startDate.getMonth() + 1).padStart(2, '0');
            const day = String(startDate.getDate()).padStart(2, '0');
            
            document.getElementById('intern_end_date').value = `${year}-${month}-${day}`;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleEmploymentFields();

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESSING...';

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
                        // Success notification
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show border-0 shadow-lg fixed-top m-4';
                        alert.style.zIndex = '9999';
                        alert.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="mr-3 bg-success text-white p-2 rounded-circle">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div><strong>Success!</strong> ${data.message}</div>
                            </div>
                        `;
                        document.body.appendChild(alert);
                        
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Something went wrong');
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