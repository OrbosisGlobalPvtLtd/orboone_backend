@extends('layouts.panel')

@section('page_title', 'Apply Leave')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@include('hrms.enterprise-payroll.partials.styles')

<style>
    body, .ep-page, .set-page, button, input, select, textarea {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }

    .leave-page-container {
        width: 100%;
        margin: 0 auto;
    }

    /* Mode Segmented Toggle */
    .mode-toggle-group {
        display: flex;
        background: #F1F5F9;
        padding: 4px;
        border-radius: 14px;
        gap: 6px;
        margin-bottom: 20px;
    }

    .mode-toggle-btn {
        flex: 1;
        text-align: center;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        color: #64748B;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border: none;
        background: transparent;
    }

    .mode-toggle-btn.active {
        background: #FFFFFF;
        color: var(--orb-primary, #4B00E8);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* Theme Primary Submit Button */
    .leave-btn-theme-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        color: #FFFFFF !important;
        border: none !important;
        border-radius: 12px !important;
        height: 46px;
        padding: 0 28px;
        font-size: 13.5px;
        font-weight: 800;
        letter-spacing: 0.02em;
        cursor: pointer;
        box-shadow: 0 8px 22px rgba(75, 0, 232, 0.28);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none !important;
    }

    .leave-btn-theme-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(75, 0, 232, 0.38);
        filter: brightness(1.05);
        color: #FFFFFF !important;
    }

    .leave-btn-theme-submit:active:not(:disabled) {
        transform: translateY(0);
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
    }

    .leave-btn-theme-submit:disabled {
        opacity: 0.72;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* Theme Cancel Button */
    .leave-btn-theme-cancel {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        background: #F8FAFC !important;
        color: #475569 !important;
        border: 1.5px solid #CBD5E1 !important;
        border-radius: 12px !important;
        height: 48px !important;
        padding: 0 24px !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
    }

    .leave-btn-theme-cancel:hover {
        background: #E2E8F0 !important;
        border-color: #94A3B8 !important;
        color: #0F172A !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        text-decoration: none !important;
    }

    /* Live Preview Side Card */
    .summary-card-sticky {
        position: sticky;
        top: 90px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-top: 4px solid var(--orb-primary, #4B00E8);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.25s ease;
    }

    .summary-card-sticky:hover {
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
    }

    .ep-calc-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: rgba(75, 0, 232, 0.08);
        color: var(--orb-primary, #4B00E8);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .calc-loading-pill {
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-primary, #4B00E8);
        background: rgba(75, 0, 232, 0.08);
        border: 1px solid rgba(75, 0, 232, 0.18);
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        animation: pulseSoft 1.5s infinite;
    }

    @keyframes pulseSoft {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.55; }
    }

    .badge-theme-policy {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.04em;
        color: #475569;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 5px 10px;
    }

    #sandwichPreviewCard {
        transition: opacity 0.22s ease, filter 0.22s ease;
    }

    #sandwichPreviewCard.calculating {
        opacity: 0.55;
        pointer-events: none;
        filter: blur(0.3px);
    }

    .preview-pill {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 12px 14px;
        text-align: center;
        transition: all 0.25s ease;
    }

    .preview-pill:hover {
        border-color: #CBD5E1;
        background: #F1F5F9;
    }

    .preview-pill.pill-payable {
        border-color: #10B981 !important;
        background: #ECFDF5 !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.12);
    }

    .preview-pill.pill-payable:hover {
        background: #D1FAE5 !important;
    }

    .preview-pill.pill-sandwich-active {
        border-color: #F59E0B !important;
        background: #FFFBEB !important;
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.12);
    }

    .preview-num {
        font-size: 22px;
        font-weight: 900;
        color: #0F172A;
        line-height: 1.1;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .preview-lbl {
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748B;
        margin-bottom: 5px;
    }

    .formula-split-badge {
        font-size: 11.5px;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 8px;
        background: #F4F2FF;
        border: 1px solid rgba(75, 0, 232, 0.14);
        color: var(--orb-primary, #4B00E8);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        letter-spacing: 0.02em;
    }

    .split-badge {
        font-size: 12px;
        font-weight: 800;
        padding: 8px 14px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.18s ease;
    }

    .split-badge:hover {
        transform: translateX(2px);
    }

    /* Orbo Date Picker Theme Styling */
    .leave-page-container .orbo-date-picker,
    .leave-page-container .orbo-date-picker-display,
    .leave-page-container input[data-date-picker] {
        height: 44px !important;
        border-radius: 12px !important;
        border: 1px solid #E2E8F0 !important;
        background-color: #FFFFFF !important;
        font-size: 13.5px !important;
        font-weight: 600 !important;
        color: #0F172A !important;
        box-shadow: none !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .leave-page-container .orbo-date-picker:focus,
    .leave-page-container .orbo-date-picker-display:focus,
    .leave-page-container input[data-date-picker]:focus {
        border-color: var(--orb-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
        outline: none !important;
    }
</style>
@endsection

@section('_content')
<div class="ep-page">
    <div class="leave-page-container">
        
        <!-- Premium Hero Header -->
        <div class="ep-hero" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;">
            <div>
                <div class="ep-kicker"><i class="fas fa-plane-departure"></i> LEAVE MANAGEMENT</div>
                <h1 style="font-size: 26px; font-weight: 900; color: #fff;">Apply Leave Request</h1>
                <p style="font-size: 13px; color: rgba(255,255,255,0.85); margin-bottom: 0;">Submit your leave application. Monthly paid leave quotas, sandwich rules & LWP apply automatically.</p>
            </div>
            
            <div>
                <a href="{{ route('leave-requests.index') }}" class="btn font-weight-bold" style="background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.35); border-radius: 12px; padding: 10px 20px; font-size: 13px;">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Requests
                </a>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <div class="row">
            <!-- LEFT COLUMN: APPLICATION FORM -->
            <div class="col-12 col-lg-7 mb-4">
                <div class="ep-card">
                    <div class="ep-table-header">
                        <div class="ep-table-head-left">
                            <div class="ep-icon-box"><i class="fas fa-edit"></i></div>
                            <div>
                                <h5 class="ep-table-title">Leave Details </h5>
                                <p class="ep-table-subtitle">Fill in your request details carefully.</p>
                            </div>
                        </div>
                    </div>

                    <div class="ep-card-body" style="padding: 24px;">
                        <form id="applyLeaveForm" method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
                            @csrf

                            @if($isAdminOrHr && $employees->isNotEmpty())
                            <!-- Employee Selector for Admin/HR -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Select Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" id="apply_employee_id" class="form-control select2-searchable" style="border-radius: 12px; height: 44px;" onchange="triggerLivePreview()">
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ ($employee && $employee->id == $emp->id) ? 'selected' : '' }}>
                                            {{ $emp->display_name }} ({{ $emp->employee_code ?? 'EMP' }}) &bull; {{ optional($emp->department)->name ?? 'General' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- 1. Duration Mode Selector -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted mb-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Leave Duration Mode</label>
                                <div class="mode-toggle-group">
                                    <button type="button" id="btnModeSingle" class="mode-toggle-btn active" onclick="setLeaveMode('single')">
                                        <i class="fas fa-calendar-day mr-1"></i> Single Day Leave
                                    </button>
                                    <button type="button" id="btnModeMultiple" class="mode-toggle-btn" onclick="setLeaveMode('multiple')">
                                        <i class="fas fa-calendar-week mr-1"></i> Multiple Days Leave
                                    </button>
                                </div>
                                <input type="hidden" name="leave_mode" id="leave_mode_input" value="single">
                            </div>

                            <!-- 2. Leave Type -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Leave Type <span class="text-danger">*</span></label>
                                <select name="leave_type_id" id="leave_type_id" class="form-control shadow-none select2-searchable" style="border-radius: 12px; height: 44px; font-weight: 600;" required onchange="triggerLivePreview()">
                                    <option value="" disabled {{ old('leave_type_id') !== null ? '' : 'selected' }}>Select leave type...</option>
                                    @foreach($leaveTypes as $type)
                                        @php
                                            $isDisabled = false;
                                            $suffix = '';
                                            
                                            if ($type->is_comp_off && ($allocation->comp_off_remaining ?? 0) <= 0 && !$isAdminOrHr) {
                                                $isDisabled = true;
                                                $suffix = ' (No balance available)';
                                            }
                                            
                                            $isSelected = old('leave_type_id') !== null 
                                                ? old('leave_type_id') == $type->id 
                                                : ($type->code === 'paid_leave');
                                        @endphp
                                        <option value="{{ $type->id }}" 
                                            {{ $isDisabled ? 'disabled' : '' }} 
                                            {{ $isSelected ? 'selected' : '' }}>
                                            {{ $type->name }}{{ $suffix }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Leave is applied in 0.5-day units. Select the PL or SL leave type; the selected balance is used first.</small>
                            </div>

                            <!-- 3. Dates Selection (Orbo Date Picker) -->
                            <div class="row">
                                <!-- Single Date Input -->
                                <div class="col-12 mb-3" id="single_date_container">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Leave Date <span class="text-danger">*</span></label>
                                    <x-form.date-picker 
                                        name="single_date_input" 
                                        id="single_date_input" 
                                        :value="old('start_date', date('Y-m-d'))" 
                                        placeholder="dd-mm-yyyy" 
                                        class="shadow-none" 
                                    />
                                </div>

                                <!-- Multiple Dates Inputs -->
                                <div class="col-md-6 col-12 mb-3" id="start_date_container" style="display: none;">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Start Date <span class="text-danger">*</span></label>
                                    <x-form.date-picker 
                                        name="start_date" 
                                        id="start_date" 
                                        :value="old('start_date', date('Y-m-d'))" 
                                        placeholder="dd-mm-yyyy" 
                                        class="shadow-none" 
                                    />
                                </div>

                                <div class="col-md-6 col-12 mb-3" id="end_date_container" style="display: none;">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">End Date <span class="text-danger">*</span></label>
                                    <x-form.date-picker 
                                        name="end_date" 
                                        id="end_date" 
                                        :value="old('end_date', date('Y-m-d'))" 
                                        placeholder="dd-mm-yyyy" 
                                        class="shadow-none" 
                                    />
                                </div>
                            </div>

                            <!-- 4 & 5. Half Day and Emergency Leave (2-Grid Layout) -->
                            <div class="row">
                                <!-- Half Day Option (Only for Single Date) -->
                                <div class="col-md-6 col-12 mb-3" id="half_day_section">
                                    <div class="p-3 h-100 d-flex flex-column justify-content-center" style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; min-height: 56px;">
                                        <div class="d-flex align-items-center flex-wrap" style="gap: 14px;">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="is_half_day" name="is_half_day" value="1" {{ old('is_half_day') ? 'checked' : '' }} onchange="toggleHalfDaySession(); triggerLivePreview();">
                                                <label class="custom-control-label font-weight-bold text-dark" for="is_half_day" style="font-size: 13px; cursor: pointer;">
                                                    Half Day Leave
                                                </label>
                                            </div>

                                            <div id="half_day_type_container" style="{{ old('is_half_day') ? 'display: block;' : 'display: none;' }}">
                                                <div class="d-flex align-items-center" style="gap: 8px;">
                                                    <span class="font-weight-bold text-muted small mr-1">Session:</span>
                                                    <div class="custom-control custom-radio custom-control-inline mr-2">
                                                        <input type="radio" id="session_first" name="half_day_type" value="first_half" class="custom-control-input" {{ old('half_day_type', 'first_half') === 'first_half' ? 'checked' : '' }} onchange="triggerLivePreview()">
                                                        <label class="custom-control-label font-weight-bold text-dark small" for="session_first" style="cursor: pointer;">1st Half</label>
                                                    </div>
                                                    <div class="custom-control custom-radio custom-control-inline mr-0">
                                                        <input type="radio" id="session_second" name="half_day_type" value="second_half" class="custom-control-input" {{ old('half_day_type') === 'second_half' ? 'checked' : '' }} onchange="triggerLivePreview()">
                                                        <label class="custom-control-label font-weight-bold text-dark small" for="session_second" style="cursor: pointer;">2nd Half</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emergency Leave Option -->
                                <div class="col-md-6 col-12 mb-3" id="emergency_leave_section">
                                    <div class="p-3 h-100 d-flex align-items-center" style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; min-height: 56px;">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="emergency_leave" name="emergency_leave" value="1" {{ old('emergency_leave') ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold text-dark mb-0" for="emergency_leave" style="font-size: 13px; cursor: pointer;">
                                                Emergency Leave <small class="text-muted font-weight-normal">(Urgent)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Attachment / Medical Certificate -->
                            <div class="form-group mb-3" id="attachmentGroup">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="font-weight-bold text-dark mb-0" id="attachmentLabel" style="font-size: 13px;">
                                        Supporting Document / Attachment <small class="text-muted font-weight-normal" id="attachmentOptionalText">(Optional)</small>
                                    </label>
                                    <span id="medicalCertBadge" class="badge badge-danger font-weight-bold px-2 py-1" style="font-size: 11px; display: none; border-radius: 6px;">
                                        <i class="fas fa-file-medical mr-1"></i> Medical Certificate Required
                                    </span>
                                </div>
                                <input type="file" name="attachment" id="attachmentInput" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="form-control shadow-none @error('attachment') is-invalid @enderror" style="border-radius: 12px; height: 44px; padding: 8px 14px;">
                                @error('attachment')
                                    <div class="invalid-feedback d-block font-weight-bold">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1" style="font-size: 11px;" id="attachmentHelpText">
                                    Allowed file formats: PDF, JPG, PNG, DOC (Max: 5MB).
                                </small>

                                <!-- Medical Certificate Alert Notice -->
                                <div id="medicalNoticeAlert" class="alert alert-danger border-0 shadow-sm p-3 mt-2 mb-0" style="border-radius: 12px; display: none; background: #FEF2F2; border-left: 4px solid #EF4444 !important;">
                                    <div class="d-flex align-items-center text-danger font-weight-bold mb-1" style="font-size: 13px;">
                                        <i class="fas fa-notes-medical mr-2" style="font-size: 15px;"></i> Medical Certificate Mandatory
                                    </div>
                                    <div class="text-danger small" style="font-size: 12px; line-height: 1.5;">
                                        Policy requires a verified <strong>Medical Certificate / Doctor Prescription</strong> to be attached when taking <strong>more than 2 consecutive days of Sick Leave</strong>.
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Reason -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Reason / Explanation <span class="text-danger">*</span></label>
                                <textarea name="reason" rows="3" class="form-control shadow-none" style="border-radius: 12px;" required placeholder="Describe the reason for your leave request...">{{ old('reason') }}</textarea>
                            </div>

                            <!-- SUBMIT BUTTONS -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap pt-4 border-top" style="gap: 14px;">
                                <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                                    <button type="submit" id="btnSubmitLeave" class="leave-btn-theme-submit">
                                        <span class="spinner-border spinner-border-sm mr-2" id="btnSubmitSpinner" role="status" aria-hidden="true" style="display: none;"></span>
                                        <i class="fas fa-paper-plane mr-2" id="btnSubmitIcon"></i>
                                        <span id="btnSubmitText">Submit Leave Request</span>
                                    </button>
                                    <a href="{{ route('leave-requests.index') }}" class="leave-btn-theme-cancel">
                                        <i class="fas fa-times text-muted mr-1"></i>
                                        <span>Cancel</span>
                                    </a>
                                </div>
                                <div class="d-none d-md-flex align-items-center text-muted small font-weight-bold" style="font-size: 11.5px;">
                                    <i class="fas fa-shield-alt mr-1.5" style="color: var(--orb-primary, #4B00E8);"></i>
                                    <span>Policy auto-verified</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: LIVE CALCULATION & QUOTA SUMMARY CARD -->
            <div class="col-12 col-lg-5">
                <div class="summary-card-sticky">
                    <!-- Available Balances Strip -->
                    <div class="p-3 mb-3" style="background: linear-gradient(135deg, rgba(75, 0, 232, 0.04) 0%, rgba(255, 82, 82, 0.04) 100%); border: 1px solid rgba(75, 0, 232, 0.12); border-radius: 14px;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="font-weight-bold text-dark" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.04em;">
                                <i class="fas fa-wallet mr-1" style="color: var(--orb-primary, #4B00E8);"></i> Available Balance
                            </span>
                            <span class="badge badge-light border text-muted small font-weight-bold" style="font-size: 10px;">{{ date('Y') }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-center" style="gap: 8px;">
                            <div class="flex-fill p-2 rounded" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
                                <div class="small text-muted font-weight-bold" style="font-size: 10px;">Paid (PL)</div>
                                <div class="font-weight-bold text-dark" style="font-size: 14px;" id="disp_pl_balance">{{ number_format($allocation->paid_remaining ?? 0, 1) }}</div>
                            </div>
                            <div class="flex-fill p-2 rounded" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
                                <div class="small text-muted font-weight-bold" style="font-size: 10px;">Sick (SL)</div>
                                <div class="font-weight-bold text-dark" style="font-size: 14px;" id="disp_sl_balance">{{ number_format($allocation->sick_remaining ?? 0, 1) }}</div>
                            </div>
                            <div class="flex-fill p-2 rounded" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
                                <div class="small text-muted font-weight-bold" style="font-size: 10px;">Comp-Off</div>
                                <div class="font-weight-bold text-dark" style="font-size: 14px;" id="disp_comp_balance">{{ number_format($allocation->comp_off_remaining ?? 0, 1) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Calculation Header -->
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <div class="ep-calc-icon-box">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark" style="font-size: 15px; line-height: 1.2;">Live Leave Calculation</div>
                                <div class="text-muted small" style="font-size: 11px;">Real-time quota & sandwich impact</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 6px;">
                            <span id="calcLoadingBadge" class="calc-loading-pill" style="display: none;">
                                <i class="fas fa-circle-notch fa-spin mr-1"></i> Calculating...
                            </span>
                            <span class="badge-theme-policy">POLICY</span>
                        </div>
                    </div>

                    <div id="sandwichPreviewCard">
                        <!-- 1. Day Counts Grid -->
                        <div class="row mb-3">
                            <div class="col-6 mb-2">
                                <div class="preview-pill">
                                    <div class="preview-lbl"><i class="fas fa-calendar-alt mr-1"></i> Calendar Days</div>
                                    <div class="preview-num" id="prev_cal_days">0</div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="preview-pill">
                                    <div class="preview-lbl"><i class="fas fa-briefcase mr-1 text-success"></i> Working Days</div>
                                    <div class="preview-num text-success" id="prev_work_days">0</div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="preview-pill" id="prev_sand_pill">
                                    <div class="preview-lbl"><i class="fas fa-layer-group mr-1 text-warning"></i> Sandwich Days</div>
                                    <div class="preview-num text-warning" id="prev_sand_days">0</div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="preview-pill pill-payable">
                                    <div class="preview-lbl" style="color: #047857;"><i class="fas fa-check-double mr-1"></i> Payable (Paid) Days</div>
                                    <div class="preview-num" style="color: #047857;" id="prev_payable_paid_days">0.0</div>
                                </div>
                            </div>
                        </div>

                        <!-- 1b. Advance Notice Warning Badge -->
                        <div id="noticeWarningBanner" class="alert alert-warning border-0 shadow-sm p-3 mb-3" style="border-radius: 12px; display: none; background: #FFFBEB; border-left: 4px solid #F59E0B !important;">
                            <div class="d-flex align-items-center gap-2 font-weight-bold text-dark mb-1" style="font-size: 13px; color: #92400E;">
                                <i class="fas fa-exclamation-triangle text-warning mr-1"></i> Notice Requirement Warning
                            </div>
                            <div class="small text-muted mb-0" id="noticeWarningText"></div>
                        </div>

                        <!-- 1c. Duplicate Date Warning Badge -->
                        <div id="duplicateWarningBanner" class="alert alert-warning border-0 shadow-sm p-3 mb-3" style="border-radius: 12px; display: none; background: #FFF7ED; border-left: 4px solid #EA580C !important;">
                            <div class="d-flex align-items-center gap-2 font-weight-bold text-dark mb-1" style="font-size: 13px; color: #9A3412;">
                                <i class="fas fa-exclamation-circle text-warning mr-1"></i> Existing Request Notice
                            </div>
                            <div class="small text-muted mb-0" id="duplicateWarningText"></div>
                        </div>

                        <!-- 1d. Calculation Error Alert Badge -->
                        <div id="calcErrorBanner" class="alert alert-danger border-0 shadow-sm p-3 mb-3" style="border-radius: 12px; display: none; background: #FEF2F2; border-left: 4px solid #EF4444 !important;">
                            <div class="d-flex align-items-center gap-2 font-weight-bold text-danger mb-1" style="font-size: 13px;">
                                <i class="fas fa-times-circle mr-1"></i> Calculation Alert
                            </div>
                            <div class="small text-danger mb-0" id="calcErrorText"></div>
                        </div>

                        <!-- 2. Sandwich Rule Alert Badge -->
                        <div id="sandwichBadge" class="alert alert-warning border-0 shadow-sm p-3 mb-3" style="border-radius: 12px; display: none; background: #FEF3C7; border: 1px solid #FDE68A;">
                            <div class="d-flex align-items-center gap-2 font-weight-bold text-dark mb-1" style="font-size: 13px; color: #92400E;">
                                <i class="fas fa-bread-slice text-warning"></i> Sandwich Rule Applied!
                            </div>
                            <div id="sandwichBadgeMsg" class="small text-muted mb-1">Intervening holidays/week-offs are included in total leave span.</div>
                            <div class="small font-weight-bold text-dark">Total Span Deducted: <span id="prev_total_ded_text">0.0</span> Days</div>
                        </div>

                        <!-- 3. Quota Split Breakdown -->
                        <div class="p-3 mb-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="font-weight-bold text-dark" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.04em;">Leave Quota Deduction Split</div>
                            </div>
                            <div id="ledgerSplitSummary" class="formula-split-badge mb-2.5" style="display:none;"></div>
                            
                            <div class="split-badge mb-2" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #047857;">
                                <span><i class="fas fa-calendar-check mr-1.5"></i> Paid Leave Deducted</span>
                                <strong id="prev_paid_days">0.0 Days</strong>
                            </div>

                            <div class="split-badge mb-2" style="background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C;">
                                <span><i class="fas fa-heartbeat mr-1.5"></i> Sick Leave Deducted</span>
                                <strong id="prev_sick_days">0.0 Days</strong>
                            </div>

                            <div class="split-badge mb-2" style="background: #F5F3FF; border: 1px solid #DDD6FE; color: #6D28D9;">
                                <span><i class="fas fa-clock mr-1.5"></i> Comp-Off Deducted</span>
                                <strong id="prev_comp_days">0.0 Days</strong>
                            </div>

                            <div class="split-badge" style="background: #F1F5F9; border: 1px solid #E2E8F0; color: #334155;">
                                <span><i class="fas fa-user-clock mr-1.5"></i> LWP (Leave Without Pay)</span>
                                <strong id="prev_lwp_days">0.0 Days</strong>
                            </div>
                        </div>

                        <!-- 4. LWP Spillover Notice Banner -->
                        <div id="lwpNoticeBanner" class="alert alert-danger border-0 shadow-sm p-3 mb-3" style="border-radius: 12px; display: none; background: #FEF2F2; border-left: 4px solid #EF4444 !important;">
                            <div class="d-flex align-items-center gap-2 font-weight-bold text-danger mb-1" style="font-size: 13px;" id="lwpNoticeTitle">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Insufficient Leave Balance (LWP)
                            </div>
                            <div class="small text-danger" id="lwpNoticeText">
                                You do not have sufficient balance for the selected leave type. The requested days will be applied as Leave Without Pay (LWP).
                            </div>
                        </div>

                        <!-- 5. Verification Note inside summary card -->
                        <div class="pt-3 border-top d-flex align-items-center justify-content-center text-muted small font-weight-bold" style="font-size: 11.5px;">
                            <i class="fas fa-check-circle text-success mr-1.5"></i> Live Quota &amp; Policy Auto-Verified
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('_script')
<script>
    function setDatePickerValue(inputId, value) {
        var el = document.getElementById(inputId);
        if (!el) return;
        el.value = value || '';
        if (el._flatpickr) {
            el._flatpickr.setDate(value || '', false);
        }
    }

    function syncSingleDate() {
        var singleEl = document.getElementById('single_date_input');
        if (!singleEl) return;
        var singleVal = singleEl.value;
        if (!singleVal) return;

        setDatePickerValue('start_date', singleVal);
        setDatePickerValue('end_date', singleVal);
    }

    function setLeaveMode(mode) {
        document.getElementById('leave_mode_input').value = mode;
        var btnSingle = document.getElementById('btnModeSingle');
        var btnMultiple = document.getElementById('btnModeMultiple');
        var singleContainer = document.getElementById('single_date_container');
        var startContainer = document.getElementById('start_date_container');
        var endContainer = document.getElementById('end_date_container');
        var halfDaySection = document.getElementById('half_day_section');
        var emergencySection = document.getElementById('emergency_leave_section');
        var halfDayCheck = document.getElementById('is_half_day');

        if (mode === 'single') {
            btnSingle.classList.add('active');
            btnMultiple.classList.remove('active');
            singleContainer.style.display = 'block';
            startContainer.style.display = 'none';
            endContainer.style.display = 'none';
            if (halfDaySection) halfDaySection.style.display = 'block';
            if (emergencySection) emergencySection.className = 'col-md-6 col-12 mb-3';

            var startEl = document.getElementById('start_date');
            if (startEl && startEl.value) {
                setDatePickerValue('single_date_input', startEl.value);
            }
            syncSingleDate();
        } else {
            btnMultiple.classList.add('active');
            btnSingle.classList.remove('active');
            singleContainer.style.display = 'none';
            startContainer.style.display = 'block';
            endContainer.style.display = 'block';
            
            // Multiple Days -> DISABLE & HIDE Half Day Option
            if (halfDaySection) halfDaySection.style.display = 'none';
            if (emergencySection) emergencySection.className = 'col-12 mb-3';
            if (halfDayCheck) halfDayCheck.checked = false;
            toggleHalfDaySession();

            var singleVal = document.getElementById('single_date_input').value;
            var startEl = document.getElementById('start_date');
            var endEl = document.getElementById('end_date');

            if (singleVal) {
                if (startEl && !startEl.value) {
                    setDatePickerValue('start_date', singleVal);
                }
                if (endEl && !endEl.value) {
                    setDatePickerValue('end_date', singleVal);
                }
            }

            setTimeout(function() {
                [startEl, endEl].forEach(function(el) {
                    if (el && el._flatpickr) {
                        el._flatpickr.redraw();
                    }
                });
            }, 60);
        }

        triggerLivePreview();
    }

    function toggleHalfDaySession() {
        var isHalfDay = document.getElementById('is_half_day').checked;
        var sessionContainer = document.getElementById('half_day_type_container');
        if (!sessionContainer) return;
        if (isHalfDay) {
            sessionContainer.classList.remove('d-none');
            sessionContainer.style.display = 'block';
        } else {
            sessionContainer.classList.add('d-none');
            sessionContainer.style.display = 'none';
        }
    }

    function setupFlatpickrHooks() {
        var singleEl = document.getElementById('single_date_input');
        if (singleEl && singleEl._flatpickr) {
            singleEl._flatpickr.config.onChange.push(function(selectedDates, dateStr) {
                syncSingleDate();
                triggerLivePreview();
            });
        }

        var startEl = document.getElementById('start_date');
        var endEl = document.getElementById('end_date');

        if (startEl && startEl._flatpickr) {
            startEl._flatpickr.config.onChange.push(function(selectedDates, dateStr) {
                if (selectedDates[0] && endEl && endEl._flatpickr) {
                    endEl._flatpickr.set('minDate', selectedDates[0]);
                    var curEnd = endEl._flatpickr.selectedDates[0];
                    if (curEnd && curEnd < selectedDates[0]) {
                        endEl._flatpickr.setDate(selectedDates[0], true);
                        return;
                    }
                }
                triggerLivePreview();
            });
        }

        if (endEl && endEl._flatpickr) {
            endEl._flatpickr.config.onChange.push(function(selectedDates, dateStr) {
                triggerLivePreview();
            });
        }
    }

    var previewAbortController = null;
    var previewDebounceTimer = null;

    function triggerLivePreview() {
        clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(function() {
            performLivePreview();
        }, 160);
    }

    function performLivePreview() {
        var leaveTypeId = document.getElementById('leave_type_id').value;
        var startDate = document.getElementById('start_date').value;
        var endDate = document.getElementById('end_date').value;
        var isHalfDay = document.getElementById('is_half_day').checked;
        var halfDayType = document.querySelector('input[name="half_day_type"]:checked') ? document.querySelector('input[name="half_day_type"]:checked').value : 'first_half';
        var emergencyLeave = document.getElementById('emergency_leave').checked;
        var employeeIdEl = document.getElementById('apply_employee_id');
        var employeeId = employeeIdEl ? employeeIdEl.value : null;

        if (!leaveTypeId || !startDate || !endDate) {
            return;
        }

        if (previewAbortController) {
            previewAbortController.abort();
        }
        previewAbortController = new AbortController();

        var cardEl = document.getElementById('sandwichPreviewCard');
        var loadingBadge = document.getElementById('calcLoadingBadge');
        if (cardEl) cardEl.classList.add('calculating');
        if (loadingBadge) loadingBadge.style.display = 'inline-flex';

        fetch("{{ route('leave-requests.preview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            signal: previewAbortController.signal,
            body: JSON.stringify({
                employee_id: employeeId,
                leave_type_id: leaveTypeId,
                start_date: startDate,
                end_date: endDate,
                is_half_day: isHalfDay,
                half_day_type: halfDayType,
                emergency_leave: emergencyLeave
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.success && res.data) {
                var d = res.data;

                // Update available balance strip if returned
                if (d.current_balance) {
                    var plBal = document.getElementById('disp_pl_balance');
                    var slBal = document.getElementById('disp_sl_balance');
                    var compBal = document.getElementById('disp_comp_balance');
                    if (plBal && d.current_balance.paid_remaining !== undefined) {
                        plBal.textContent = Number(d.current_balance.paid_remaining).toFixed(1);
                    }
                    if (slBal && d.current_balance.sick_remaining !== undefined) {
                        slBal.textContent = Number(d.current_balance.sick_remaining).toFixed(1);
                    }
                    if (compBal && d.current_balance.comp_off_remaining !== undefined) {
                        compBal.textContent = Number(d.current_balance.comp_off_remaining).toFixed(1);
                    }
                }

                // Update ledger split formula
                var ledgerSplit = document.getElementById('ledgerSplitSummary');
                if (ledgerSplit) {
                    ledgerSplit.style.display = d.ledger_backed ? 'block' : 'none';
                    if (d.ledger_backed) {
                        var plStr = 'PL ' + Number(d.pl_days || 0).toFixed(2);
                        var slStr = 'SL ' + Number(d.sl_days || 0).toFixed(2);
                        var compStr = 'Comp-Off ' + Number(d.comp_off_days || 0).toFixed(2);
                        var lwpStr = 'LWP ' + Number(d.lop_days || 0).toFixed(2);

                        if (d.is_lwp) {
                            ledgerSplit.textContent = lwpStr;
                        } else if (d.is_sick) {
                            ledgerSplit.textContent = slStr + ' + ' + plStr + ' + ' + compStr + ' + ' + lwpStr;
                        } else if (d.is_comp_off) {
                            ledgerSplit.textContent = compStr + ' + ' + plStr + ' + ' + slStr + ' + ' + lwpStr;
                        } else {
                            // Paid Leave (PL) default:
                            ledgerSplit.textContent = plStr + ' + ' + slStr + ' + ' + compStr + ' + ' + lwpStr;
                        }
                    } else {
                        ledgerSplit.textContent = '';
                    }
                }

                // Day counts grid
                document.getElementById('prev_cal_days').textContent = d.requested_calendar_days;
                document.getElementById('prev_work_days').textContent = Number(d.working_days || 0).toString();
                document.getElementById('prev_sand_days').textContent = Number(d.sandwich_days || 0).toString();

                var sandPill = document.getElementById('prev_sand_pill');
                if (sandPill) {
                    if (Number(d.sandwich_days || 0) > 0) {
                        sandPill.classList.add('pill-sandwich-active');
                    } else {
                        sandPill.classList.remove('pill-sandwich-active');
                    }
                }
                
                var payablePaidDays = Math.max(0, Number(d.deducted_days || 0) - Number(d.lwp_days || 0));
                document.getElementById('prev_payable_paid_days').textContent = Number(payablePaidDays).toFixed(1);

                if (document.getElementById('prev_total_ded_text')) {
                    document.getElementById('prev_total_ded_text').textContent = Number(d.deducted_days).toFixed(1);
                }

                document.getElementById('prev_paid_days').textContent = Number(d.paid_days).toFixed(1) + ' Days';
                document.getElementById('prev_sick_days').textContent = Number(d.sick_days).toFixed(1) + ' Days';
                document.getElementById('prev_comp_days').textContent = Number(d.comp_off_days).toFixed(1) + ' Days';
                document.getElementById('prev_lwp_days').textContent = Number(d.lwp_days).toFixed(1) + ' Days';

                // Notice requirement warning badge
                var noticeBanner = document.getElementById('noticeWarningBanner');
                var noticeMsgEl = document.getElementById('noticeWarningText');
                if (d.notice_warning) {
                    if (noticeBanner) noticeBanner.style.display = 'block';
                    if (noticeMsgEl) noticeMsgEl.textContent = d.notice_warning;
                } else {
                    if (noticeBanner) noticeBanner.style.display = 'none';
                }

                // Duplicate Request warning badge
                var dupBanner = document.getElementById('duplicateWarningBanner');
                var dupMsgEl = document.getElementById('duplicateWarningText');
                if (d.duplicate_warning) {
                    if (dupBanner) dupBanner.style.display = 'block';
                    if (dupMsgEl) dupMsgEl.textContent = d.duplicate_warning;
                } else {
                    if (dupBanner) dupBanner.style.display = 'none';
                }

                // Hide any calculation error
                var calcErrBanner = document.getElementById('calcErrorBanner');
                if (calcErrBanner) calcErrBanner.style.display = 'none';

                // Sandwich rule badge
                var sandwichBadge = document.getElementById('sandwichBadge');
                var sandwichMsgEl = document.getElementById('sandwichBadgeMsg');
                if (d.sandwich_applied || d.sandwich_days > 0) {
                    sandwichBadge.style.display = 'block';
                    if (sandwichMsgEl && d.sandwich_message) {
                        sandwichMsgEl.textContent = d.sandwich_message;
                    }
                } else {
                    sandwichBadge.style.display = 'none';
                }

                // Medical certificate requirement for sick leave > 2 days
                var isMedReq = !!d.requires_medical_certificate;
                var medBadge = document.getElementById('medicalCertBadge');
                var medAlert = document.getElementById('medicalNoticeAlert');
                var attLabel = document.getElementById('attachmentLabel');
                var attOptText = document.getElementById('attachmentOptionalText');
                var attInput = document.getElementById('attachmentInput');

                if (isMedReq) {
                    if (medBadge) medBadge.style.display = 'inline-block';
                    if (medAlert) medAlert.style.display = 'block';
                    if (attOptText) attOptText.style.display = 'none';
                    if (attLabel) attLabel.innerHTML = 'Medical Certificate <span class="text-danger">* (Required)</span>';
                    if (attInput) {
                        attInput.required = true;
                        attInput.classList.add('border-danger');
                    }
                } else {
                    if (medBadge) medBadge.style.display = 'none';
                    if (medAlert) medAlert.style.display = 'none';
                    if (attOptText) attOptText.style.display = 'inline';
                    if (attLabel) attLabel.innerHTML = 'Supporting Document / Attachment <small class="text-muted font-weight-normal" id="attachmentOptionalText">(Optional)</small>';
                    if (attInput) {
                        attInput.required = false;
                        attInput.classList.remove('border-danger', 'is-invalid');
                    }
                }

                // LWP Notice
                var lwpNoticeBanner = document.getElementById('lwpNoticeBanner');
                var lwpNoticeTitle = document.getElementById('lwpNoticeTitle');
                var lwpNoticeText = document.getElementById('lwpNoticeText');
                if (d.lwp_days > 0) {
                    lwpNoticeBanner.style.display = 'block';
                    if (lwpNoticeTitle) {
                        lwpNoticeTitle.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> Insufficient Leave Balance (LWP)';
                    }
                    var lwpCount = Number(d.lwp_days).toFixed(1);
                    lwpNoticeText.innerHTML = 'You do not have sufficient balance for the selected leave type. <strong>' + lwpCount + ' day(s)</strong> will be applied as <strong>Leave Without Pay (LWP)</strong>.';
                } else {
                    lwpNoticeBanner.style.display = 'none';
                }
            } else if (res && !res.success) {
                var calcErrBanner = document.getElementById('calcErrorBanner');
                var calcErrText = document.getElementById('calcErrorText');
                if (calcErrBanner && res.message) {
                    calcErrBanner.style.display = 'block';
                    if (calcErrText) calcErrText.textContent = res.message;
                }
                var dupBanner = document.getElementById('duplicateWarningBanner');
                if (dupBanner) dupBanner.style.display = 'none';
            }
        })
        .catch(function(err) {
            if (err.name !== 'AbortError') {
                console.warn('Preview calculation error:', err);
                var calcErrBanner = document.getElementById('calcErrorBanner');
                var calcErrText = document.getElementById('calcErrorText');
                if (calcErrBanner) {
                    calcErrBanner.style.display = 'block';
                    if (calcErrText) calcErrText.textContent = 'Unable to calculate leave preview. Please verify selected dates.';
                }
            }
        })
        .finally(function() {
            if (cardEl) cardEl.classList.remove('calculating');
            if (loadingBadge) loadingBadge.style.display = 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.initOrboDatePickers === 'function') {
            window.initOrboDatePickers();
        }
        setupFlatpickrHooks();
        setLeaveMode('single');

        var attInput = document.getElementById('attachmentInput');
        if (attInput) {
            attInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    this.classList.remove('is-invalid', 'border-danger');
                }
            });
        }

        var form = document.getElementById('applyLeaveForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                var mode = document.getElementById('leave_mode_input').value;
                if (mode === 'single') {
                    syncSingleDate();
                }

                // Validate medical certificate if required
                var attField = document.getElementById('attachmentInput');
                if (attField && attField.required && (!attField.files || attField.files.length === 0)) {
                    e.preventDefault();
                    attField.classList.add('is-invalid', 'border-danger');
                    attField.focus();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Medical Certificate Required',
                            text: 'Policy requires a verified Medical Certificate when taking more than 2 consecutive days of Sick Leave.',
                            confirmButtonColor: '#4B00E8'
                        });
                    } else {
                        alert('Medical Certificate is required for more than 2 consecutive days of Sick Leave. Please upload your document.');
                    }
                    return false;
                }

                var submitBtn = document.getElementById('btnSubmitLeave');
                var spinner = document.getElementById('btnSubmitSpinner');
                var text = document.getElementById('btnSubmitText');
                var icon = document.getElementById('btnSubmitIcon');

                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    if (spinner) spinner.style.display = 'inline-block';
                    if (icon) icon.style.display = 'none';
                    if (text) text.textContent = 'Submitting Request...';
                }
            });
        }

        if (window.jQuery) {
            $(document).on('change change.select2 select2:select input', '#apply_employee_id, #leave_type_id, #single_date_input, #start_date, #end_date, #is_half_day, input[name="half_day_type"], #emergency_leave', function() {
                if (this.id === 'single_date_input') {
                    syncSingleDate();
                }
                triggerLivePreview();
            });
        }

        setTimeout(function() {
            syncSingleDate();
            triggerLivePreview();
        }, 150);
    });
</script>
@endsection
