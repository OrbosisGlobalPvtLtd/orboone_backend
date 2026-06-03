@extends('layouts.panel', ['active' => 'document_generation'])

@section('page_title', 'Generate HR Document')

@section('_head')
<style>
    .document-page {
        background: #F8FAFC;
        padding: 24px;
        min-height: calc(100vh - 80px);
    }

    .premium-hero {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%);
        border-radius: 20px;
        padding: 30px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 8px 30px rgba(75, 0, 232, 0.12);
    }

    .premium-hero h2 {
        font-weight: 800;
        margin-bottom: 8px;
    }

    .orb-card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .orb-card-header {
        background: #F8FAFC;
        padding: 20px 24px;
        border-bottom: 1px solid #E2E8F0;
    }

    .orb-card-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }

    .doc-type-card {
        border: 2px solid #E2E8F0;
        border-radius: 16px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.25s ease;
        background: white;
        height: 100%;
    }

    .doc-type-card:hover {
        border-color: var(--orb-primary, #4B00E8);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(75, 0, 232, 0.08);
    }

    .doc-type-card.active {
        border-color: var(--orb-primary, #4B00E8);
        background-color: var(--primary-light, #F3EDFF);
    }

    .doc-type-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #F1F5F9;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
        transition: all 0.2s;
    }

    .doc-type-card.active .doc-type-icon {
        background: var(--orb-primary, #4B00E8);
        color: white;
    }

    .doc-type-card h5 {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #1e293b;
    }

    .doc-type-card p {
        font-size: 11px;
        color: #64748b;
        margin: 0;
        line-height: 1.4;
    }

    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #E2E8F0;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        margin-right: 8px;
    }

    .step-active .step-number {
        background: var(--orb-primary, #4B00E8);
        color: white;
    }

    .form-label {
        font-size: 11px;
        text-transform: uppercase;
        color: #475569;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .form-control,
    .form-select {
        height: 42px;
        border-radius: 10px;
        border: 1px solid #CBD5E1;
        font-size: 13px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--orb-primary, #4B00E8);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.15);
    }

    textarea.form-control {
        height: auto;
    }

    .btn-orb-primary {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 28px;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(75, 0, 232, 0.2);
    }

    .btn-orb-primary:hover {
        opacity: 0.95;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(75, 0, 232, 0.3);
        color: white;
    }

    .btn-orb-soft {
        background: var(--primary-light, #F3EDFF);
        color: var(--orb-primary, #4B00E8);
        border: 1px solid var(--border, #E7EAF3);
        border-radius: 50px;
        padding: 12px 28px;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-orb-soft:hover {
        background: var(--border, #E7EAF3);
    }

    .preview-pane {
        background: #f1f5f9;
        border-radius: 20px;
        overflow: hidden;
        height: 100%;
        min-height: 650px;
        box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    #previewIframe {
        background: transparent;
        border: none;
        width: 100% !important;
        height: 100% !important;
        top: 0;
        left: 0;
        position: absolute;
    }

    #previewLoader {
        background: rgba(255, 255, 255, 0.85) !important;
        color: #1e293b !important;
        backdrop-filter: blur(4px);
    }

    #previewLoader .spinner-border {
        color: var(--orb-primary, #4B00E8) !important;
    }

    #noPreviewSplash {
        color: #475569 !important;
    }
</style>
@endsection

@section('_content')
<div class="document-page">

    <!-- Hero Header -->
    <div class="premium-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2><i class="fas fa-file-pdf me-2"></i> Document Generation</h2>
                <p class="mb-0 opacity-80">Generate professional HR documents using HTML PDF templates.</p>
            </div>
            <a href="{{ route('hrms.document-generation.generated.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">
                <i class="fas fa-list me-2"></i> View History
            </a>
        </div>
    </div>

    <div class="row align-items-start">
        <!-- Input & Form Config Side -->
        <div class="col-lg-6 mb-4">
            <div class="orb-card">

                <form id="generationForm" method="POST" action="{{ route('hrms.document-generation.generated.store') }}">
                    @csrf

                    <!-- Step 1: Employee/Candidate Select -->
                    <div class="orb-card-header step-active">
                        <h4><span class="step-number">1</span> Select Recipient</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employee</label>
                                <select name="employee_id" id="employee_select" class="form-select">
                                    <option value="">-- Candidate / Manual Entry --</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="candidate_name_field">
                                <label class="form-label">Candidate / Full Name</label>
                                <input type="text" name="manual_fields[candidate_name]" id="candidate_name" class="form-control" placeholder="Enter Full Name" value="John Doe">
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Select Document Type Dropdown -->
                    <div class="orb-card-header step-active border-top">
                        <h4><span class="step-number">2</span> Select Document Type</h4>
                    </div>
                    <div class="card-body p-4">
                        <input type="hidden" name="document_type" id="document_type_input" value="offer_letter">
                        <div class="mb-0">
                            <label class="form-label d-flex align-items-center gap-2">
                                <i class="fas fa-file-signature text-primary"></i> Select Document Type
                            </label>
                            <div class="position-relative">
                                <select id="document_type_select" class="form-select w-100 fw-bold text-dark shadow-sm" style="border: 2px solid #CBD5E1; border-radius: 12px; height: 50px; padding-left: 45px; font-size: 14px; transition: all 0.25s ease;">
                                    <option value="offer_letter" selected>Offer Letter</option>
                                    <option value="appointment_letter">Appointment Letter</option>
                                    <option value="experience_letter">Experience Letter</option>
                                    <option value="relieving_letter">Relieving Letter</option>
                                    <option value="internship_certificate">Internship Certificate</option>
                                    <option value="salary_certificate">Salary Certificate</option>
                                    <option value="warning_letter">Warning Letter</option>
                                    <option value="appreciation_letter">Appreciation Letter</option>
                                    <option value="nda_agreement">NDA Agreement</option>
                                </select>
                                <div class="position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary" style="pointer-events: none; z-index: 5;">
                                    <i class="fas fa-envelope-open-text text-primary fs-5" id="document_type_icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Document Specific Form Fields -->
                    <div class="orb-card-header step-active border-top">
                        <h4><span class="step-number">3</span> Fill Document Details</h4>
                    </div>
                    <div class="card-body p-4">

                        <!-- Common Basic overrides (Always Rendered) -->
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Issue Date</label>
                                <input type="date" name="manual_fields[issue_date]" id="common_issue_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="manual_fields[company_name]" id="common_company_name" class="form-control" value="Orbosis Global Pvt Ltd">
                            </div>
                        </div>

                        <!-- DYNAMIC FORM SECTIONS -->

                        <!-- Form: Offer Letter -->
                        <div class="dynamic-form-section" id="form_offer_letter">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Joining Date</label>
                                    <input type="date" name="manual_fields[joining_date]" id="offer_joining_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Offer Valid Till</label>
                                    <input type="date" name="manual_fields[offer_valid_till]" id="offer_valid_till" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="manual_fields[designation]" id="offer_designation" class="form-control" placeholder="Software Engineer" value="Software Engineer">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="manual_fields[department]" id="offer_department" class="form-control" placeholder="Engineering" value="Engineering">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Office Location</label>
                                    <input type="text" name="manual_fields[office_location]" id="offer_location" class="form-control" value="Corporate Office">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Probation Period</label>
                                    <input type="text" name="manual_fields[probation_period]" class="form-control" value="3 Months">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Monthly Gross Salary (INR)</label>
                                    <input type="number" name="manual_fields[monthly_gross_salary]" id="offer_salary" class="form-control" placeholder="50000" value="50000" oninput="calculateOfferSalary()">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Annual CTC (INR)</label>
                                    <input type="number" name="manual_fields[annual_ctc]" id="offer_ctc" class="form-control" placeholder="600000" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Basic Monthly</label>
                                    <input type="number" name="manual_fields[basic_monthly]" id="offer_basic" class="form-control" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">HRA Monthly</label>
                                    <input type="number" name="manual_fields[hra_monthly]" id="offer_hra" class="form-control" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Special Allowance</label>
                                    <input type="number" name="manual_fields[special_allowance_monthly]" id="offer_special" class="form-control" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Professional Tax</label>
                                    <input type="number" name="manual_fields[professional_tax_monthly]" id="offer_pt" class="form-control" value="200" oninput="calculateOfferSalary()">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Net Pay Monthly (Approx)</label>
                                    <input type="number" name="manual_fields[net_pay_monthly]" id="offer_net_pay" class="form-control" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">HR Manager Name</label>
                                    <input type="text" name="manual_fields[hr_manager_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Job Responsibilities</label>
                                    <textarea name="manual_fields[job_responsibilities]" class="form-control" rows="3" placeholder="Enter key roles..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form: Appointment Letter -->
                        <div class="dynamic-form-section d-none" id="form_appointment_letter">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Joining Date</label>
                                    <input type="date" name="manual_fields[joining_date]" id="app_joining_date" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="manual_fields[designation]" id="app_designation" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="manual_fields[department]" id="app_department" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Work Location</label>
                                    <input type="text" name="manual_fields[work_location]" id="app_location" class="form-control" value="Corporate Office">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Monthly Gross Salary (INR)</label>
                                    <input type="number" name="manual_fields[monthly_salary]" id="app_salary" class="form-control" placeholder="50000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Salary In Words</label>
                                    <input type="text" name="manual_fields[salary_in_words]" class="form-control" placeholder="Fifty Thousand Rupees Only">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Probation Period</label>
                                    <input type="text" name="manual_fields[probation_period]" class="form-control" value="6 Months">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Notice Period (Probation)</label>
                                    <input type="text" name="manual_fields[notice_period_probation]" class="form-control" value="15 Days">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Notice Period (Confirmed)</label>
                                    <input type="text" name="manual_fields[notice_period_confirmed]" class="form-control" value="30 Days">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Reporting Manager Name</label>
                                    <input type="text" name="manual_fields[reporting_manager_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Project Manager Name</label>
                                    <input type="text" name="manual_fields[project_manager_name]" class="form-control" value="Harshit Singh">
                                </div>
                            </div>
                        </div>

                        <!-- Form: Experience Certificate -->
                        <div class="dynamic-form-section d-none" id="form_experience_letter">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Joining Date</label>
                                    <input type="date" name="manual_fields[joining_date]" id="exp_joining_date" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Relieving Date</label>
                                    <input type="date" name="manual_fields[relieving_date]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="manual_fields[designation]" id="exp_designation" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Name</label>
                                    <input type="text" name="manual_fields[signatory_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Signatory Designation</label>
                                    <input type="text" name="manual_fields[signatory_designation]" class="form-control" value="Head of Human Resources">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Core Roles & Responsibilities</label>
                                    <textarea name="manual_fields[experience_responsibilities]" class="form-control" rows="3" placeholder="Assigned deliverables..."></textarea>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Performance Summary</label>
                                    <textarea name="manual_fields[performance_summary]" class="form-control" rows="3">Their performance has been highly satisfactory and exemplary. They exhibited excellent problem-solving skills and collaborated well within their team.</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form: Relieving Letter -->
                        <div class="dynamic-form-section d-none" id="form_relieving_letter">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Joining Date</label>
                                    <input type="date" name="manual_fields[joining_date]" id="rel_joining_date" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Relieving Date</label>
                                    <input type="date" name="manual_fields[relieving_date]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Resignation Date</label>
                                    <input type="date" name="manual_fields[resignation_date]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="manual_fields[designation]" id="rel_designation" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Name</label>
                                    <input type="text" name="manual_fields[signatory_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Designation</label>
                                    <input type="text" name="manual_fields[signatory_designation]" class="form-control" value="Head of Human Resources">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Handover Details</label>
                                    <textarea name="manual_fields[handover_status]" class="form-control" rows="3">All company assets, including laptop, security access badges, source code repositories, and work documents, have been successfully handed over to the designated team leader. Full and final settlement of accounts has been fully completed and paid.</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form: Internship Certificate -->
                        <div class="dynamic-form-section d-none" id="form_internship_certificate">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Internship Start Date</label>
                                    <input type="date" name="manual_fields[internship_start_date]" id="int_joining_date" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Internship End Date</label>
                                    <input type="date" name="manual_fields[internship_end_date]" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="manual_fields[designation]" id="int_designation" class="form-control" value="Software Engineering Intern">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Name</label>
                                    <input type="text" name="manual_fields[signatory_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Signatory Designation</label>
                                    <input type="text" name="manual_fields[signatory_designation]" class="form-control" value="Head of Human Resources & Training">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Internship Work Summary</label>
                                    <textarea name="manual_fields[internship_work_summary]" class="form-control" rows="3">During the internship, the candidate was trained on core technologies, developed modular product components, participated in product deployment cycles, and collaborated with senior engineering teams.</textarea>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Performance Appraisal</label>
                                    <textarea name="manual_fields[performance_summary]" class="form-control" rows="3">The candidate demonstrated strong learning abilities, analytical problem-solving skills, and deep dedication to all assigned tasks.</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form: Salary Certificate -->
                        <div class="dynamic-form-section d-none" id="form_salary_certificate">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Monthly Gross Salary (INR)</label>
                                    <input type="number" name="manual_fields[monthly_salary]" id="sal_salary" class="form-control" oninput="calculateSalSalary()">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Annual Salary (INR)</label>
                                    <input type="number" name="manual_fields[annual_salary]" id="sal_annual" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="manual_fields[designation]" id="sal_designation" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="manual_fields[department]" id="sal_department" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Name</label>
                                    <input type="text" name="manual_fields[signatory_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Designation</label>
                                    <input type="text" name="manual_fields[signatory_designation]" class="form-control" value="Head of Human Resources">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Purpose</label>
                                    <input type="text" name="manual_fields[purpose]" class="form-control" value="Address Verification / Loan Application">
                                </div>
                            </div>
                        </div>

                        <!-- Form: Warning Letter -->
                        <div class="dynamic-form-section d-none" id="form_warning_letter">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Warning Subject</label>
                                    <input type="text" name="manual_fields[warning_subject]" class="form-control" value="First Written Warning for Performance/Conduct">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Incident Date</label>
                                    <input type="date" name="manual_fields[incident_date]" class="form-control" value="{{ date('Y-m-d', strtotime('-1 days')) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Name</label>
                                    <input type="text" name="manual_fields[signatory_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Designation</label>
                                    <input type="text" name="manual_fields[signatory_designation]" class="form-control" value="Head of Human Resources">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Warning Reason (Infraction Details)</label>
                                    <textarea name="manual_fields[warning_reason]" class="form-control" rows="3" placeholder="Provide description of policy infraction, performance gaps, or attendance issues...">This warning is issued due to repeated unexcused absences and failure to deliver key project components on scheduled deadlines.</textarea>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Corrective Action Required</label>
                                    <textarea name="manual_fields[corrective_action]" class="form-control" rows="3">You are instructed to immediately rectify these performance gaps and display a high standard of professional conduct and discipline. Your reporting manager will review your progress weekly.</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form: Appreciation Letter -->
                        <div class="dynamic-form-section d-none" id="form_appreciation_letter">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Achievement Title</label>
                                    <input type="text" name="manual_fields[achievement_title]" class="form-control" value="Exceptional Performance & Project Delivery">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Performance Period</label>
                                    <input type="text" name="manual_fields[performance_period]" class="form-control" value="Q2 2026">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Name</label>
                                    <input type="text" name="manual_fields[signatory_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Designation</label>
                                    <input type="text" name="manual_fields[signatory_designation]" class="form-control" value="Director of Engineering">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Appreciation Reason (Achievement Details)</label>
                                    <textarea name="manual_fields[appreciation_reason]" class="form-control" rows="4">Your hard work and dedication during our recent product release helped us deliver outstanding results under tight timelines. Your problem-solving abilities and positive mindset have been highly inspiring to your entire team.</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form: NDA Agreement -->
                        <div class="dynamic-form-section d-none" id="form_nda_agreement">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Effective Date</label>
                                    <input type="date" name="manual_fields[effective_date]" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Party Name (Candidate/Employee)</label>
                                    <input type="text" name="manual_fields[party_name]" id="nda_party_name" class="form-control" placeholder="Second Party Name">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Party Address</label>
                                    <input type="text" name="manual_fields[party_address]" id="nda_address" class="form-control" placeholder="Employee's local address">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confidentiality Period</label>
                                    <input type="text" name="manual_fields[confidentiality_period]" class="form-control" value="5 Years post employment termination">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Signatory Name</label>
                                    <input type="text" name="manual_fields[signatory_name]" class="form-control" value="Harshit Singh">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Signatory Designation</label>
                                    <input type="text" name="manual_fields[signatory_designation]" class="form-control" value="Director of Operations">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Actions Toolbar -->
                    <div class="card-footer bg-light p-4 d-flex justify-content-end gap-3 border-top">
                        <button type="button" class="btn-orb-soft" onclick="triggerLivePreview()">
                            <i class="fas fa-eye me-2"></i> Preview Document
                        </button>
                        <button type="submit" class="btn-orb-primary">
                            <i class="fas fa-check-circle me-2"></i> Generate & Save PDF
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Live Preview IFrame Side -->
        <div class="col-lg-6 mb-4 sticky-lg-top" style=" z-index: 10;">
            <div class="orb-card d-flex flex-column">
                <div class="orb-card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-file-pdf text-danger me-2"></i> Live Document Preview</h4>
                    <span class="badge bg-primary rounded-pill" id="preview_doc_type_badge">Offer Letter</span>
                </div>
                <div class="card-body p-0 position-relative" style="background: #f1f5f9; height: calc(100vh - 180px); min-height: 550px; overflow: hidden;">

                    <div id="previewLoader" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255, 255, 255, 0.85); z-index: 10; backdrop-filter: blur(4px);">
                        <div class="text-center text-dark">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <div>Generating Realtime Preview...</div>
                        </div>
                    </div>

                    <div id="noPreviewSplash" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted p-5 text-center" style="position: absolute; top:0; left:0;">
                        <i class="fas fa-file-invoice fs-1 mb-3 text-secondary"></i>
                        <h5>No Active Preview</h5>
                        <p class="small mb-0">Fill in recipient information and click the "Preview Document" button to render an exact A4 PDF blueprint preview.</p>
                    </div>

                    <iframe id="previewIframe" src="" class="w-100 h-100 border-0 d-none" style="position: absolute; top:0; left:0;"></iframe>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Employee structural data lookup map for automatic field pre-filling
    const employeesMap = {
        @foreach($employees as $emp)
        "{{ $emp->id }}": {
            "name": "{{ addslashes($emp->display_name) }}",
            "designation": "{{ addslashes($emp->designation?->name ?? '') }}",
            "department": "{{ addslashes($emp->department?->name ?? '') }}",
            "joining_date": "{{ $emp->joining_date ? date('Y-m-d', strtotime($emp->joining_date)) : '' }}",
            "salary": "{{ $emp->salaryStructure?->gross_salary ?? $emp->gross_salary ?? '' }}",
            "location": "{{ addslashes($emp->profile?->work_location ?? 'Remote') }}",
            "address": "{{ addslashes($emp->profile?->present_address ?: $emp->profile?->address ?: '') }}",
            "city": "{{ addslashes($emp->profile?->city ?: $emp->profile?->present_city ?: '') }}"
        },
        @endforeach
    };

    // Automatic Form Selector based on Dropdown
    document.getElementById('document_type_select').addEventListener('change', function() {
        const selectedType = this.value;
        const selectedText = this.options[this.selectedIndex].text;

        document.getElementById('document_type_input').value = selectedType;
        document.getElementById('preview_doc_type_badge').innerText = selectedText;

        // Update premium icon dynamically
        const iconMap = {
            'offer_letter': 'fas fa-envelope-open-text text-primary',
            'appointment_letter': 'fas fa-file-signature text-primary',
            'experience_letter': 'fas fa-award text-primary',
            'relieving_letter': 'fas fa-door-open text-primary',
            'internship_certificate': 'fas fa-graduation-cap text-primary',
            'salary_certificate': 'fas fa-wallet text-primary',
            'warning_letter': 'fas fa-exclamation-triangle text-danger',
            'appreciation_letter': 'fas fa-thumbs-up text-success',
            'nda_agreement': 'fas fa-balance-scale text-primary'
        };
        const iconEl = document.getElementById('document_type_icon');
        if (iconEl) {
            iconEl.className = iconMap[selectedType] || 'fas fa-file-alt text-primary';
        }

        // Hide all specific forms and show the chosen one
        document.querySelectorAll('.dynamic-form-section').forEach(form => form.classList.add('d-none'));
        const activeForm = document.getElementById('form_' + selectedType);
        if (activeForm) {
            activeForm.classList.remove('d-none');
        }

        // Trigger updates if salary forms are selected
        if (selectedType === 'offer_letter') {
            calculateOfferSalary();
        } else if (selectedType === 'salary_certificate') {
            calculateSalSalary();
        }

        // Live preview immediately changes on template selection
        triggerLivePreview();
    });

    // Employee dropdown change listener for smart pre-fills
    document.getElementById('employee_select').addEventListener('change', function() {
        const empId = this.value;
        const candField = document.getElementById('candidate_name_field');

        if (!empId) {
            candField.classList.remove('d-none');
            document.getElementById('candidate_name').required = true;
            return;
        }

        candField.classList.add('d-none');
        document.getElementById('candidate_name').required = false;

        // Load pre-fill details from dataset
        const emp = employeesMap[empId];
        if (emp) {
            // Offer Letter
            document.getElementById('offer_designation').value = emp.designation;
            document.getElementById('offer_department').value = emp.department;
            document.getElementById('offer_joining_date').value = emp.joining_date;
            document.getElementById('offer_location').value = emp.location;
            document.getElementById('offer_salary').value = emp.salary;
            calculateOfferSalary();

            // Appointment
            document.getElementById('app_designation').value = emp.designation;
            document.getElementById('app_department').value = emp.department;
            document.getElementById('app_joining_date').value = emp.joining_date;
            document.getElementById('app_location').value = emp.location;
            document.getElementById('app_salary').value = emp.salary;

            // Experience
            document.getElementById('exp_designation').value = emp.designation;
            document.getElementById('exp_joining_date').value = emp.joining_date;

            // Relieving
            document.getElementById('rel_designation').value = emp.designation;
            document.getElementById('rel_joining_date').value = emp.joining_date;

            // Internship
            document.getElementById('int_joining_date').value = emp.joining_date;
            document.getElementById('int_designation').value = emp.designation;

            // Salary Certificate
            document.getElementById('sal_salary').value = emp.salary;
            document.getElementById('sal_designation').value = emp.designation;
            document.getElementById('sal_department').value = emp.department;
            calculateSalSalary();

            // NDA
            document.getElementById('nda_address').value = emp.address + (emp.city ? ', ' + emp.city : '');
            if (document.getElementById('nda_party_name')) {
                document.getElementById('nda_party_name').value = emp.name;
            }
        }
    });

    // Salary breakdown calculator helper for Offer Letter
    function calculateOfferSalary() {
        const gross = parseFloat(document.getElementById('offer_salary').value) || 0;
        const pt = parseFloat(document.getElementById('offer_pt').value) || 0;
        const ctc = gross * 12;
        document.getElementById('offer_ctc').value = ctc;

        const basic = gross * 0.50;
        const hra = gross * 0.20;
        const special = gross - basic - hra;
        const netPay = gross - pt;

        document.getElementById('offer_basic').value = basic.toFixed(2);
        document.getElementById('offer_hra').value = hra.toFixed(2);
        document.getElementById('offer_special').value = Math.max(0, special).toFixed(2);
        document.getElementById('offer_net_pay').value = Math.max(0, netPay).toFixed(2);
    }

    // Salary certificate calculator helper
    function calculateSalSalary() {
        const gross = parseFloat(document.getElementById('sal_salary').value) || 0;
        document.getElementById('sal_annual').value = (gross * 12).toFixed(2);
    }

    // Live preview renderer using iframe
    function triggerLivePreview() {
        const form = document.getElementById('generationForm');
        const formData = new FormData(form);

        const loader = document.getElementById('previewLoader');
        const splash = document.getElementById('noPreviewSplash');
        const iframe = document.getElementById('previewIframe');

        loader.classList.remove('d-none');
        loader.classList.add('d-flex');

        fetch("{{ route('hrms.document-generation.generated.preview') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.html || 'Required fields missing or dynamic preview failed.');
                    }).catch(() => {
                        throw new Error('Required fields missing or preview rendering failed.');
                    });
                }
                return response.json();
            })
            .then(data => {
                splash.classList.add('d-none');
                iframe.classList.remove('d-none');

                // Write rendered Blade HTML directly into iframe
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                doc.write(data.html);
                doc.close();
            })
            .catch(error => {
                splash.classList.add('d-none');
                iframe.classList.remove('d-none');

                const doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body {
                        font-family: 'DejaVu Sans', sans-serif;
                        padding: 30px;
                        background: #fff5f5;
                        color: #c53030;
                        margin: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                        box-sizing: border-box;
                    }
                    .error-card {
                        background: white;
                        border: 1px solid #feb2b2;
                        border-radius: 12px;
                        padding: 24px;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                        max-width: 480px;
                        width: 100%;
                    }
                    h4 { margin: 0 0 10px 0; font-size: 16px; display: flex; align-items: center; gap: 8px; }
                    p { margin: 0 0 15px 0; font-size: 13px; line-height: 1.5; color: #4a5568; }
                    small { font-size: 11px; color: #718096; display: block; border-top: 1px solid #edf2f7; padding-top: 10px; }
                </style>
            </head>
            <body>
                <div class="error-card">
                    <h4>⚠️ Live Preview Dynamic Loading</h4>
                    <p>Provide a valid recipient name or select an active employee in Step 1 to populate the blueprint view.</p>
                    <small>Real-time synchronizer is active. All keystrokes and template selections will update this pane automatically.</small>
                </div>
            </body>
            </html>
        `);
                doc.close();
                console.warn("Live Preview Render Notice: ", error.message);
            })
            .finally(() => {
                loader.classList.add('d-none');
                loader.classList.remove('d-flex');
            });
    }

    // Debounce helper to avoid heavy server requests on each keystroke
    let previewTimeout = null;

    function debouncedPreview() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(() => {
            triggerLivePreview();
        }, 600); // 600ms debounce
    }

    // Register dynamic realtime listener on all inputs, select lists, and textareas
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Initial calculation & immediate preview load
        calculateOfferSalary();
        triggerLivePreview();

        // 2. Attach input/change event listeners to all form controls inside generationForm
        const form = document.getElementById('generationForm');
        if (form) {
            // Use event delegation on input & change for robust capturing of all current and dynamic fields
            form.addEventListener('input', function(e) {
                if (e.target && e.target.matches('input, textarea')) {
                    debouncedPreview();
                }
            });
            form.addEventListener('change', function(e) {
                if (e.target && e.target.matches('select, input, textarea')) {
                    debouncedPreview();
                }
            });
        }
    });
</script>
@endpush