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
        margin-bottom: 24px;
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
        background-color: #fff;
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

    /* Group styling */
    .form-group-section {
        background: #FCFDFE;
        border: 1px solid #F1F5F9;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.01);
    }
    
    .form-group-section-title {
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-primary, #4B00E8);
        margin-bottom: 15px;
        border-bottom: 1px solid #F1F5F9;
        padding-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Premium Smart Form styles */
    .smart-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .smart-form-grid.two-col {
        grid-template-columns: repeat(2, 1fr);
    }

    .smart-form-grid-stacked {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .smart-form-field {
        display: flex;
        flex-direction: column;
    }

    .smart-form-label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: inline-flex;
        align-items: center;
        user-select: none; /* Fix text selection/highlight styling issue on label */
    }

    .smart-form-control,
    .smart-select {
        height: 48px;
        border-radius: 12px;
        border: 1.5px solid #E2E8F0;
        font-size: 14px;
        padding: 0 16px;
        background-color: #FFFFFF;
        color: #1E293B;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
        width: 100%;
        box-sizing: border-box;
    }

    /* select wrapper for custom premium icons and arrows */
    .smart-select-wrapper {
        position: relative;
        width: 100%;
        display: block;
    }

    .smart-select-wrapper .smart-select {
        padding-left: 52px !important;
        padding-right: 48px !important;
        height: 52px !important;
        line-height: 52px !important;
        appearance: none !important;
        background-image: none !important;
    }

    .smart-select-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--orb-primary, #4B00E8);
        font-size: 16px;
        pointer-events: none;
        z-index: 10;
    }

    .smart-select-arrow {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #475569;
        font-size: 14px;
        pointer-events: none;
        z-index: 10;
    }

    select.smart-select:not(.smart-select-wrapper select) {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 12px 12px;
        padding-right: 40px;
    }

    .smart-form-control:focus,
    .smart-select:focus {
        border-color: var(--orb-primary, #4B00E8);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1);
        outline: none;
    }

    textarea.smart-form-control {
        height: auto;
        padding: 12px 16px;
        line-height: 1.5;
    }

    .grid-col-span-2 {
        grid-column: span 2;
    }

    @media (max-width: 768px) {
        .smart-form-grid,
        .smart-form-grid.two-col {
            grid-template-columns: 1fr !important;
            gap: 16px;
        }
        .grid-col-span-2 {
            grid-column: span 1;
        }
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

    <!-- Error Alerts -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <h5 class="alert-heading fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Required Fields Missing</h5>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row align-items-start">
        <!-- Input & Form Config Side -->
        <div class="col-lg-6 mb-4">
            <div class="orb-card">

                <form id="generationForm" method="POST" action="{{ route('hrms.document-generation.generated.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Step 1: Employee/Candidate Select -->
                    <div class="orb-card-header step-active">
                        <h4><span class="step-number">1</span> Select Recipient</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="smart-form-grid two-col">
                            <div class="smart-form-field">
                                <label class="smart-form-label">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" id="employee_select" class="smart-select">
                                    <option value="">-- Candidate / Manual Entry --</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="smart-form-field" id="candidate_name_field">
                                <label class="smart-form-label">Candidate / Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="manual_fields[candidate_name]" id="candidate_name" class="smart-form-control" placeholder="Enter Full Name" value="">
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Select Document Type Dropdown -->
                    <div class="orb-card-header step-active border-top">
                        <h4><span class="step-number">2</span> Select Document Type</h4>
                    </div>
                    <div class="card-body p-4">
                        <input type="hidden" name="document_type" id="document_type_input" value="">
                        <div class="smart-form-field mb-0">
                            <label class="smart-form-label">Choose Document Template <span class="text-danger">*</span></label>
                            <div class="smart-select-wrapper">
                                <i class="fas fa-file-signature smart-select-icon" id="document_type_icon"></i>
                                <select id="document_type_select" class="smart-select text-dark shadow-sm">
                                    @foreach($documentTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down smart-select-arrow"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Document Specific Form Fields -->
                    <div class="orb-card-header step-active border-top">
                        <h4><span class="step-number">3</span> Fill Document Details</h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Dynamic Smart Form Container -->
                        <div id="dynamic_form_container"></div>
                    </div>

                    <!-- Step 4: Email Delivery -->
                    <div class="orb-card-header step-active border-top">
                        <h4><span class="step-number">4</span> Email Delivery</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="send_email" id="send_email_checkbox" value="1">
                            <label class="form-check-label fw-bold text-dark" for="send_email_checkbox">Send generated document to employee email</label>
                        </div>
                        <div id="email_delivery_fields" class="d-none">
                            <div class="smart-form-grid two-col">
                                <div class="smart-form-field">
                                    <label class="smart-form-label">Employee Email <span class="text-danger">*</span></label>
                                    <input type="email" name="employee_email" id="employee_email" class="smart-form-control" placeholder="employee@example.com">
                                    <span id="no_email_warning" class="text-danger d-none small mt-1"><i class="fas fa-exclamation-circle"></i> Employee has no email address. Checkbox is disabled.</span>
                                </div>
                                <div class="smart-form-field">
                                    <label class="smart-form-label">CC Email (Optional)</label>
                                    <input type="email" name="cc_email" id="cc_email" class="smart-form-control" placeholder="cc@example.com">
                                </div>
                            </div>
                            <div class="smart-form-field mt-3">
                                <label class="smart-form-label">Email Subject Override (Optional)</label>
                                <input type="text" name="email_subject" id="email_subject" class="smart-form-control" placeholder="Enter custom subject to override default">
                            </div>
                            <div class="smart-form-field mt-3">
                                <label class="smart-form-label">Email Message Override (Optional)</label>
                                <textarea name="email_message" id="email_message" class="smart-form-control" rows="3" placeholder="Enter custom email message to override default"></textarea>
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
        <div class="col-lg-6 mb-4 sticky-lg-top" style="z-index: 10;">
            <div class="orb-card d-flex flex-column">
                <div class="orb-card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-file-pdf text-danger me-2"></i> Live Document Preview</h4>
                    <span class="badge bg-primary rounded-pill" id="preview_doc_type_badge">Offer Letter</span>
                </div>
                <div class="card-body p-0 position-relative" style="background: #f1f5f9; width: 100%; aspect-ratio: 1 / 1.414; overflow: hidden; box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05);">

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
    // Templates configuration map
    const TEMPLATE_CONFIGS = @json(\App\Services\HRMS\DocumentGeneration\DocumentFieldConfigS::getTemplates());
    @php
        $dbCompany = \Illuminate\Support\Facades\DB::table('company_settings')->first();
        $companyNameDefaultVal = $dbCompany?->company_name ?: (branding_name() === 'HRMS' || branding_name() === 'Default' ? 'Orbosis Global Pvt. Ltd.' : branding_name());
    @endphp
    const COMPANY_NAME_DEFAULT = "{!! addslashes($companyNameDefaultVal) !!}";

    // Employee structural data lookup map for automatic field pre-filling
    const employeesMap = {
        @foreach($employees as $emp)
        "{{ $emp->id }}": {
            "name": "{{ addslashes($emp->display_name) }}",
            "employee_code": "{{ addslashes($emp->employee_code) }}",
            "email": "{{ addslashes($emp->user?->email ?: $emp->email ?: '') }}",
            "phone": "{{ addslashes($emp->mobile ?: $emp->profile?->mobile ?: '') }}",
            "designation": "{{ addslashes($emp->designation?->name ?? '') }}",
            "department": "{{ addslashes($emp->department?->name ?? '') }}",
            "employment_type": "{{ addslashes($emp->employment_type ?? '') }}",
            "joining_date": "{{ $emp->joining_date ? date('Y-m-d', strtotime($emp->joining_date)) : '' }}",
            "salary": "{{ $emp->salaryStructure?->gross_salary ?? $emp->gross_salary ?? '' }}",
            "location": "{{ addslashes($emp->profile?->work_location ?? 'Remote') }}",
            "address": "{{ addslashes($emp->profile?->present_address ?: $emp->profile?->address ?: '') }}",
            "city": "{{ addslashes($emp->profile?->city ?: $emp->profile?->present_city ?: '') }}",
            "state": "{{ addslashes($emp->profile?->state ?? '') }}",
            "country": "{{ addslashes($emp->profile?->country ?? '') }}",
            "gender": "{{ addslashes($emp->gender ?: $emp->profile?->gender ?: '') }}",
            "reporting_manager_name": "{{ addslashes($emp->reportingManager?->employee_name ?: $emp->reportingManager?->full_name ?: $emp->reportingManager?->display_name ?: '') }}"
        },
        @endforeach
    };

    // Evaluates conditional visibility fields dynamically
    function evaluateConditionalVisibility() {
        const form = document.getElementById('generationForm');
        const conditionalElements = document.querySelectorAll('[data-show-if]');
        
        conditionalElements.forEach(wrapper => {
            const rule = JSON.parse(wrapper.getAttribute('data-show-if'));
            let isVisible = true;

            Object.keys(rule).forEach(key => {
                const targetInput = form.querySelector(`[name="manual_fields[${key}]"]`);
                if (targetInput) {
                    if (targetInput.value !== rule[key]) {
                        isVisible = false;
                    }
                }
            });

            const colWrapper = wrapper.closest('.smart-form-field, .col-md-6, .col-md-12');
            if (colWrapper) {
                if (isVisible) {
                    colWrapper.classList.remove('d-none');
                    colWrapper.querySelectorAll('input, select, textarea').forEach(el => {
                        el.disabled = false;
                        if (wrapper.getAttribute('data-required') === 'true') {
                            el.required = true;
                        }
                    });
                } else {
                    colWrapper.classList.add('d-none');
                    colWrapper.querySelectorAll('input, select, textarea').forEach(el => {
                        el.disabled = true;
                        el.required = false;
                    });
                }
            }
        });
    }

    // Build the dynamic smart form elements based on chosen template config
    function buildDynamicForm(selectedType) {
        const container = document.getElementById('dynamic_form_container');
        container.innerHTML = ''; // Clear container

        const config = TEMPLATE_CONFIGS[selectedType];
        if (!config || !config.fields) {
            container.innerHTML = '<div class="alert alert-warning">No configuration found for this document type.</div>';
            return;
        }

        // Section mappings and ordering
        const sections = {
            'recipient': { title: 'Recipient Information', fields: [], icon: 'fa-user-tie text-primary' },
            'details': { title: 'Document Specific Details', fields: [], icon: 'fa-info-circle text-primary' },
            'salary': { title: 'Compensation Structure', fields: [], icon: 'fa-wallet text-success' },
            'paragraphs': { title: 'Rich Text / Paragraph Clauses', fields: [], icon: 'fa-paragraph text-info' },
            'signatory': { title: 'Authorized Signatories', fields: [], icon: 'fa-signature text-secondary' }
        };

        // Distribute fields to sections
        config.fields.forEach(field => {
            const sec = field.section || 'details';
            if (sections[sec]) {
                sections[sec].fields.push(field);
            } else {
                sections['details'].fields.push(field);
            }
        });

        const todayDate = getFormattedDate(new Date());
        const yesterdayDate = getFormattedDate(new Date(Date.now() - 86400000));
        const validTillDate = getFormattedDate(new Date(Date.now() + (7 * 86400000)));

        // Render sections that have fields
        Object.keys(sections).forEach(key => {
            const sec = sections[key];
            if (sec.fields.length === 0 && key !== 'signatory') return;

            const sectionEl = document.createElement('div');
            sectionEl.className = 'form-group-section';
            
            sectionEl.innerHTML = `
                <div class="form-group-section-title">
                    <i class="fas ${sec.icon} me-2"></i> ${sec.title}
                </div>
                <div class="smart-form-grid" id="section_row_${key}"></div>
            `;
            container.appendChild(sectionEl);

            const rowEl = sectionEl.querySelector(`#section_row_${key}`);

            sec.fields.forEach(field => {
                const isFullWidth = (field.type === 'textarea');
                const fieldContainer = document.createElement('div');
                fieldContainer.className = `smart-form-field ${isFullWidth ? 'grid-col-span-2' : ''}`;

                // Calculate default value
                let val = '';
                if (field.default === 'today') val = todayDate;
                else if (field.default === 'yesterday') val = yesterdayDate;
                else if (field.default === 'today+7') val = validTillDate;
                else if (field.default === 'company_name') val = COMPANY_NAME_DEFAULT;
                else if (field.default !== undefined) val = field.default;

                const showIfAttr = field.show_if ? `data-show-if='${JSON.stringify(field.show_if)}'` : '';
                const reqStar = field.required ? ' <span class="text-danger">*</span>' : '';
                const reqAttr = field.required ? 'required' : '';
                let readonlyAttr = field.readonly ? 'readonly' : '';
                if (field.autofill === 'employee_code' && document.getElementById('employee_select').value !== '') {
                    readonlyAttr = 'readonly style="background-color: #e9ecef; cursor: not-allowed; border: 1px solid #ced4da;"';
                }
                const autofillData = field.autofill ? `data-autofill="${field.autofill}"` : '';

                let inputHtml = '';
                if (field.type === 'textarea') {
                    inputHtml = `<textarea name="manual_fields[${field.name}]" id="field_${field.name}" class="smart-form-control" rows="4" ${autofillData} ${reqAttr} ${readonlyAttr} placeholder="${field.placeholder || ''}">${val}</textarea>`;
                } else if (field.type === 'select') {
                    const opts = field.options || [];
                    inputHtml = `<select name="manual_fields[${field.name}]" id="field_${field.name}" class="smart-select" ${autofillData} ${reqAttr}>`;
                    opts.forEach(o => {
                        inputHtml += `<option value="${o}" ${o === val ? 'selected' : ''}>${o}</option>`;
                    });
                    inputHtml += `</select>`;
                } else {
                    inputHtml = `<input type="${field.type}" name="manual_fields[${field.name}]" id="field_${field.name}" class="smart-form-control" value="${val}" ${autofillData} ${reqAttr} ${readonlyAttr}>`;
                }

                fieldContainer.innerHTML = `
                    <div ${showIfAttr} data-required="${field.required ? 'true' : 'false'}" class="smart-form-field">
                        <label class="smart-form-label">${field.label}${reqStar}</label>
                        ${inputHtml}
                    </div>
                `;
                rowEl.appendChild(fieldContainer);
            });

            // If signatory section, append custom upload buttons
            if (key === 'signatory') {
                const sigContainer = document.createElement('div');
                sigContainer.className = `smart-form-field`;
                sigContainer.innerHTML = `
                    <label class="smart-form-label">Custom Signature Image (Optional)</label>
                    <input type="file" id="signature_image_file" name="signature_image_file" class="smart-form-control" accept="image/png, image/jpeg, image/jpg, image/webp">
                    <input type="hidden" name="manual_fields[signature_image_base64]" id="signature_image_base64">
                    <small class="text-muted">Overwrites default signature. Max 2MB.</small>
                `;
                rowEl.appendChild(sigContainer);

                const sealContainer = document.createElement('div');
                sealContainer.className = `smart-form-field`;
                sealContainer.innerHTML = `
                    <label class="smart-form-label">Custom Seal Image (Optional)</label>
                    <input type="file" id="seal_image_file" name="seal_image_file" class="smart-form-control" accept="image/png, image/jpeg, image/jpg, image/webp">
                    <input type="hidden" name="manual_fields[seal_image_base64]" id="seal_image_base64">
                    <small class="text-muted">Overwrites default seal. Max 2MB.</small>
                `;
                rowEl.appendChild(sealContainer);
            }
        });

        // Attach change listeners to custom signature & seal uploads
        const sigFile = document.getElementById('signature_image_file');
        const sealFile = document.getElementById('seal_image_file');

        if (sigFile) {
            sigFile.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Signature file exceeds 2MB limit.');
                        e.target.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        document.getElementById('signature_image_base64').value = evt.target.result;
                        debouncedPreview();
                    };
                    reader.readAsDataURL(file);
                } else {
                    document.getElementById('signature_image_base64').value = '';
                    debouncedPreview();
                }
            });
        }

        if (sealFile) {
            sealFile.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Seal file exceeds 2MB limit.');
                        e.target.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        document.getElementById('seal_image_base64').value = evt.target.result;
                        debouncedPreview();
                    };
                    reader.readAsDataURL(file);
                } else {
                    document.getElementById('seal_image_base64').value = '';
                    debouncedPreview();
                }
            });
        }

        // Trigger dynamic auto-fill if employee is already selected
        handleEmployeeAutofill();

        // Evaluate conditional field rules
        evaluateConditionalVisibility();

        // Attach field listeners for calculations
        attachCalculationListeners(selectedType);

        // Immediate preview reload
        triggerLivePreview();
    }

    // Date helper
    function getFormattedDate(d) {
        let month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    // Number to words converter (Indian Rupees Style)
    function numberToWords(num) {
        const ones = {
            0: "Zero", 1: "One", 2: "Two", 3: "Three", 4: "Four", 5: "Five", 6: "Six", 7: "Seven", 8: "Eight", 9: "Nine",
            10: "Ten", 11: "Eleven", 12: "Twelve", 13: "Thirteen", 14: "Fourteen", 15: "Fifteen", 16: "Sixteen", 17: "Seventeen", 18: "Eighteen", 19: "Nineteen"
        };
        const tens = {
            0: "Zero", 1: "Ten", 2: "Twenty", 3: "Thirty", 4: "Forty", 5: "Fifty", 6: "Sixty", 7: "Seventy", 8: "Eighty", 9: "Ninety"
        };

        if (num < 20) return ones[num];
        let res = "";
        if (num >= 10000000) {
            res += numberToWords(Math.floor(num / 10000000)) + " Crore ";
            num %= 10000000;
        }
        if (num >= 100000) {
            res += numberToWords(Math.floor(num / 100000)) + " Lakh ";
            num %= 100000;
        }
        if (num >= 1000) {
            res += numberToWords(Math.floor(num / 1000)) + " Thousand ";
            num %= 1000;
        }
        if (num >= 100) {
            res += numberToWords(Math.floor(num / 100)) + " Hundred ";
            num %= 100;
        }
        if (num > 0) {
            if (res !== "") res += "and ";
            if (num < 20) {
                res += ones[num];
            } else {
                res += tens[Math.floor(num / 10)];
                if (num % 10 > 0) {
                    res += " " + ones[num % 10];
                }
            }
        }
        return res.trim();
    }

    // Attach calculation calculation handlers based on template keys
    function attachCalculationListeners(docType) {
        if (docType === 'offer_letter') {
            const grossInput = document.getElementById('field_monthly_gross_salary');
            const ptInput = document.getElementById('field_professional_tax_monthly');

            const calculate = () => {
                if (!grossInput) return;
                const gross = parseFloat(grossInput.value) || 0;
                const pt = parseFloat(ptInput.value) || 0;
                
                if (document.getElementById('field_annual_ctc')) document.getElementById('field_annual_ctc').value = gross * 12;
                if (document.getElementById('field_basic_monthly')) document.getElementById('field_basic_monthly').value = (gross * 0.50).toFixed(2);
                if (document.getElementById('field_hra_monthly')) document.getElementById('field_hra_monthly').value = (gross * 0.20).toFixed(2);
                
                const special = gross - (gross * 0.50) - (gross * 0.20) - (gross > 0 ? 1600.0 : 0.0);
                if (document.getElementById('field_special_allowance_monthly')) document.getElementById('field_special_allowance_monthly').value = Math.max(0, special).toFixed(2);
                
                if (document.getElementById('field_net_pay_monthly')) document.getElementById('field_net_pay_monthly').value = Math.max(0, gross - pt).toFixed(2);
            };

            if (grossInput) grossInput.addEventListener('input', calculate);
            if (ptInput) ptInput.addEventListener('input', calculate);
            if (grossInput) calculate();
        } else if (docType === 'salary_certificate') {
            const salaryInput = document.getElementById('field_monthly_salary');
            const calculate = () => {
                if (!salaryInput) return;
                const gross = parseFloat(salaryInput.value) || 0;
                document.getElementById('field_annual_salary').value = (gross * 12).toFixed(2);
                
                const inWords = numberToWords(Math.floor(gross));
                document.getElementById('field_salary_in_words').value = inWords ? inWords + ' Rupees Only' : 'Zero Rupees Only';
            };
            if (salaryInput) {
                salaryInput.addEventListener('input', calculate);
                calculate();
            }
        } else if (docType === 'appointment_letter') {
            const salaryInput = document.getElementById('field_monthly_salary');
            const calculate = () => {
                if (!salaryInput) return;
                const gross = parseFloat(salaryInput.value) || 0;
                const inWords = numberToWords(Math.floor(gross));
                document.getElementById('field_salary_in_words').value = inWords ? inWords + ' Rupees Only' : 'Zero Rupees Only';
                
                // Set default components
                const basic = gross * 0.50;
                const hra = gross * 0.20;
                const conveyance = gross > 0 ? 1600.0 : 0.0;
                const allowances = gross - basic - hra - conveyance;

                if (document.getElementById('field_basic_salary')) document.getElementById('field_basic_salary').value = basic.toFixed(2);
                if (document.getElementById('field_hra')) document.getElementById('field_hra').value = hra.toFixed(2);
                if (document.getElementById('field_conveyance')) document.getElementById('field_conveyance').value = conveyance.toFixed(2);
                if (document.getElementById('field_allowances')) document.getElementById('field_allowances').value = Math.max(0, allowances).toFixed(2);
            };
            if (salaryInput) {
                salaryInput.addEventListener('input', calculate);
                calculate();
            }
        } else if (['promotion_letter', 'increment_letter'].includes(docType)) {
            const salaryInput = document.getElementById('field_monthly_salary');
            const calculate = () => {
                if (!salaryInput) return;
                const gross = parseFloat(salaryInput.value) || 0;
                const inWords = numberToWords(Math.floor(gross));
                document.getElementById('field_salary_in_words').value = inWords ? inWords + ' Rupees Only' : 'Zero Rupees Only';
            };
            if (salaryInput) {
                salaryInput.addEventListener('input', calculate);
                calculate();
            }
        }
    }

    // Handles the employee selection event & auto-fills mapped values
    function handleEmployeeAutofill() {
        const empId = document.getElementById('employee_select').value;
        const candField = document.getElementById('candidate_name_field');
        const emailCheckbox = document.getElementById('send_email_checkbox');
        const emailWarning = document.getElementById('no_email_warning');
        const empEmailInput = document.getElementById('employee_email');

        if (!empId) {
            candField.classList.remove('d-none');
            document.getElementById('candidate_name').required = true;
            if (emailCheckbox) {
                emailCheckbox.disabled = false;
            }
            if (emailWarning) emailWarning.classList.add('d-none');
            return;
        }

        candField.classList.add('d-none');
        document.getElementById('candidate_name').required = false;

        // Fetch data from API
        const url = `/hrms/document-generation/employees/${empId}/document-data`;
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error("Failed to fetch employee details.");
                return res.json();
            })
            .then(data => {
                // Populate Candidate/Full Name field
                const candNameInput = document.getElementById('candidate_name');
                if (candNameInput) {
                    candNameInput.value = data.employee_name;
                }

                // Autofill email
                if (empEmailInput) empEmailInput.value = data.employee_email;
                if (emailCheckbox) {
                    if (!data.employee_email) {
                        emailCheckbox.checked = false;
                        emailCheckbox.disabled = true;
                        if (emailWarning) emailWarning.classList.remove('d-none');
                        const emailFieldsDiv = document.getElementById('email_delivery_fields');
                        if (emailFieldsDiv) emailFieldsDiv.classList.add('d-none');
                    } else {
                        emailCheckbox.disabled = false;
                        if (emailWarning) emailWarning.classList.add('d-none');
                    }
                }

                // Autofill dynamic fields
                const autofillInputs = document.querySelectorAll('[data-autofill]');
                autofillInputs.forEach(input => {
                    const key = input.getAttribute('data-autofill');
                    
                    // We map the keys to data keys
                    let val = data[key];
                    if (val === undefined || val === null) {
                        // fallback checks
                        if (key === 'name') val = data.employee_name;
                        else if (key === 'phone') val = data.employee_mobile;
                        else if (key === 'location') val = data.work_location;
                        else if (key === 'address') val = data.employee_address;
                        else if (key === 'city') val = data.employee_city;
                        else if (key === 'state') val = data.employee_state;
                        else if (key === 'country') val = data.employee_country;
                        else if (key === 'salary') val = data.monthly_salary;
                    }

                    // Specific pronoun derivations
                    if (key === 'gender' && ['Mr.', 'Ms.', 'Mrs.'].includes(input.value)) {
                        const g = (data.gender || '').toLowerCase();
                        val = (g === 'female' || g === 'f') ? 'Ms.' : 'Mr.';
                    }

                    // Set value
                    if (val !== undefined && val !== null && val !== '') {
                        input.value = val;
                        // Trigger input event to run calculation listeners automatically
                        input.dispatchEvent(new Event('input'));
                    }

                    // Check if Employee Code
                    if (key === 'employee_code') {
                        input.setAttribute('readonly', 'readonly');
                        input.style.backgroundColor = '#e9ecef';
                        input.style.cursor = 'not-allowed';
                        input.style.border = '1px solid #ced4da';
                    }
                });

                // Trigger pronoun logic if Mr/Ms selector exists
                const prefixInput = document.getElementById('field_employee_prefix');
                if (prefixInput) {
                    const g = (data.gender || '').toLowerCase();
                    prefixInput.value = (g === 'female' || g === 'f') ? 'Ms.' : 'Mr.';
                    
                    const subject = document.getElementById('field_gender_pronoun_subject');
                    if (subject) subject.value = (g === 'female' || g === 'f') ? 'she' : 'he';

                    const subjectCap = document.getElementById('field_gender_pronoun_subject_capitalized');
                    if (subjectCap) subjectCap.value = (g === 'female' || g === 'f') ? 'She' : 'He';

                    const possessive = document.getElementById('field_gender_pronoun_possessive');
                    if (possessive) possessive.value = (g === 'female' || g === 'f') ? 'her' : 'his';

                    const obj = document.getElementById('field_gender_pronoun_object');
                    if (obj) obj.value = (g === 'female' || g === 'f') ? 'her' : 'him';
                }

                // Trigger salary calculations
                const selectedType = document.getElementById('document_type_select').value;
                attachCalculationListeners(selectedType);

                // Update live preview
                triggerLivePreview();
            })
            .catch(err => {
                console.error(err);
            });
    }

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
            'nda_agreement': 'fas fa-balance-scale text-primary',
            'joining_letter': 'fas fa-sign-in-alt text-primary',
            'promotion_letter': 'fas fa-level-up-alt text-success',
            'confirmation_letter': 'fas fa-user-check text-primary',
            'employment_verification_letter': 'fas fa-user-shield text-info',
            'noc_letter': 'fas fa-handshake text-primary',
            'increment_letter': 'fas fa-chart-line text-success',
            'transfer_letter': 'fas fa-exchange-alt text-warning',
            'resignation_acceptance_letter': 'fas fa-sign-out-alt text-danger'
        };
        
        const iconEl = document.getElementById('document_type_icon');
        if (iconEl) {
            iconEl.className = 'smart-select-icon ' + (iconMap[selectedType] || 'fas fa-file-alt text-primary');
        }

        // Rebuild form fields based on config
        buildDynamicForm(selectedType);
    });

    // Employee dropdown change listener for smart pre-fills
    document.getElementById('employee_select').addEventListener('change', handleEmployeeAutofill);

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
        // Trigger initial select logic
        const docTypeSelect = document.getElementById('document_type_select');
        document.getElementById('document_type_input').value = docTypeSelect.value;
        buildDynamicForm(docTypeSelect.value);

        // Attach input/change event listeners to all form controls inside generationForm
        const form = document.getElementById('generationForm');
        if (form) {
            form.addEventListener('input', function(e) {
                if (e.target && e.target.matches('input, textarea')) {
                    debouncedPreview();
                }
            });
            form.addEventListener('change', function(e) {
                if (e.target && e.target.matches('select, input, textarea')) {
                    evaluateConditionalVisibility();
                    debouncedPreview();
                }
            });
        }
        // Attach send_email checkbox logic
        const sendEmailCheckbox = document.getElementById('send_email_checkbox');
        if (sendEmailCheckbox) {
            sendEmailCheckbox.addEventListener('change', function() {
                const fields = document.getElementById('email_delivery_fields');
                const empEmailInput = document.getElementById('employee_email');
                if (this.checked) {
                    fields.classList.remove('d-none');
                    if (empEmailInput) empEmailInput.required = true;
                } else {
                    fields.classList.add('d-none');
                    if (empEmailInput) empEmailInput.required = false;
                }
            });
        }
    });
</script>
@endpush