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
        max-width: 1280px;
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
        transition: all 0.2s ease;
        border: none;
        background: transparent;
    }

    .mode-toggle-btn.active {
        background: #FFFFFF;
        color: #4F46E5;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }

    /* Live Preview Side Card */
    .summary-card-sticky {
        position: sticky;
        top: 90px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    }

    .preview-pill {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 12px 14px;
        text-align: center;
    }

    .preview-num {
        font-size: 20px;
        font-weight: 900;
        color: #0F172A;
        line-height: 1.1;
    }

    .preview-lbl {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748B;
        margin-bottom: 4px;
    }

    .split-badge {
        font-size: 12px;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
</style>
@endsection

@section('_content')
<div class="ep-page">
    <div class="leave-page-container">
        
        <!-- Premium Hero Header -->
        <div class="ep-hero" style="background: linear-gradient(135deg, #4B00E8 0%, #7C3AED 100%);">
            <div>
                <div class="ep-kicker"><i class="fas fa-plane-departure"></i> LEAVE MANAGEMENT</div>
                <h1 style="font-size: 26px; font-weight: 900; color: #fff;">Apply Leave Request</h1>
                <p style="font-size: 13px; color: rgba(255,255,255,0.85); margin-bottom: 0;">Submit your leave application. Monthly paid leave quotas, sandwich rules & LWP spillover apply automatically.</p>
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
                                <h5 class="ep-table-title">Leave Details</h5>
                                <p class="ep-table-subtitle">Fill in your request details carefully.</p>
                            </div>
                        </div>
                    </div>

                    <div class="ep-card-body" style="padding: 24px;">
                        <form id="applyLeaveForm" method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
                            @csrf

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
                                <select name="leave_type_id" id="leave_type_id" class="form-control shadow-none" style="border-radius: 12px; height: 44px; font-weight: 600;" required onchange="triggerLivePreview()">
                                    <option value="" disabled {{ old('leave_type_id') === null ? '' : 'selected' }}>Select leave type...</option>
                                    @foreach($leaveTypes as $type)
                                        @php
                                            $isConfirmed = $employee->is_permanent;
                                            $isDisabled = false;
                                            $suffix = '';
                                            
                                            if (!$isConfirmed && !$type->is_lwp) {
                                                $isDisabled = true;
                                                $suffix = ' (Confirmed employees only)';
                                            } elseif ($type->is_comp_off && ($allocation->comp_off_remaining ?? 0) <= 0) {
                                                $isDisabled = true;
                                                $suffix = ' (No balance available)';
                                            }
                                            
                                            $isSelected = old('leave_type_id') !== null 
                                                ? old('leave_type_id') == $type->id 
                                                : ($type->is_lwp);
                                        @endphp
                                        <option value="{{ $type->id }}" 
                                            {{ $isDisabled ? 'disabled' : '' }} 
                                            {{ $isSelected ? 'selected' : '' }}>
                                            {{ $type->name }}{{ $suffix }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 3. Dates Selection -->
                            <div class="row">
                                <!-- Single Date Input -->
                                <div class="col-12 mb-3" id="single_date_container">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Leave Date <span class="text-danger">*</span></label>
                                    <input type="date" id="single_date_input" value="{{ old('start_date', date('Y-m-d')) }}" class="form-control shadow-none" style="border-radius: 12px; height: 44px; font-weight: 600;" onchange="syncSingleDate(); triggerLivePreview();">
                                </div>

                                <!-- Multiple Dates Inputs -->
                                <div class="col-md-6 col-12 mb-3" id="start_date_container" style="display: none;">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" class="form-control shadow-none" style="border-radius: 12px; height: 44px; font-weight: 600;" onchange="triggerLivePreview()">
                                </div>

                                <div class="col-md-6 col-12 mb-3" id="end_date_container" style="display: none;">
                                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', date('Y-m-d')) }}" class="form-control shadow-none" style="border-radius: 12px; height: 44px; font-weight: 600;" onchange="triggerLivePreview()">
                                </div>
                            </div>

                            <!-- 4. Half Day Options (Only for Single Date) -->
                            <div class="form-group mb-3" id="half_day_section">
                                <div class="p-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px;">
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 20px;">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_half_day" name="is_half_day" value="1" {{ old('is_half_day') ? 'checked' : '' }} onchange="toggleHalfDaySession(); triggerLivePreview();">
                                            <label class="custom-control-label font-weight-bold text-dark" for="is_half_day" style="font-size: 13px; cursor: pointer;">
                                                Half Day Leave
                                            </label>
                                        </div>

                                        <div id="half_day_type_container" style="{{ old('is_half_day') ? '' : 'display: none;' }}">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="font-weight-bold text-muted small mr-2">Session:</span>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="session_first" name="half_day_type" value="first_half" class="custom-control-input" {{ old('half_day_type', 'first_half') === 'first_half' ? 'checked' : '' }} onchange="triggerLivePreview()">
                                                    <label class="custom-control-label font-weight-bold text-dark small" for="session_first" style="cursor: pointer;">First Half</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="session_second" name="half_day_type" value="second_half" class="custom-control-input" {{ old('half_day_type') === 'second_half' ? 'checked' : '' }} onchange="triggerLivePreview()">
                                                    <label class="custom-control-label font-weight-bold text-dark small" for="session_second" style="cursor: pointer;">Second Half</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Emergency Leave -->
                            <div class="form-group mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="emergency_leave" name="emergency_leave" value="1" {{ old('emergency_leave') ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-dark" for="emergency_leave" style="font-size: 13px; cursor: pointer;">
                                        Emergency Leave <small class="text-muted">(Check if applying due to urgent emergency)</small>
                                    </label>
                                </div>
                            </div>

                            <!-- 6. Attachment -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Attachment <small class="text-muted">(Optional)</small></label>
                                <input type="file" name="attachment" class="form-control shadow-none" style="border-radius: 12px; height: 44px; padding: 8px 14px;">
                            </div>

                            <!-- 7. Reason -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Reason / Explanation <span class="text-danger">*</span></label>
                                <textarea name="reason" rows="3" class="form-control shadow-none" style="border-radius: 12px;" required placeholder="Describe the reason for your leave request...">{{ old('reason') }}</textarea>
                            </div>

                            <!-- SUBMIT BUTTONS -->
                            <div class="d-flex align-items-center flex-wrap pt-3 border-top" style="gap: 12px;">
                                <button type="submit" class="btn btn-primary font-weight-bold shadow-sm" style="border-radius: 12px; padding: 10px 24px; font-size: 13px; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); border: none;">
                                    <i class="fas fa-check-circle mr-1"></i> Submit Request
                                </button>
                                <a href="{{ route('leave-requests.index') }}" class="btn btn-light border font-weight-bold" style="border-radius: 12px; padding: 10px 20px; font-size: 13px;">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: LIVE CALCULATION & QUOTA SUMMARY CARD -->
            <div class="col-12 col-lg-5">
                <div class="summary-card-sticky">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                        <div class="font-weight-bold text-dark" style="font-size: 15px;">
                            <i class="fas fa-calculator text-primary mr-1"></i> Live Leave Calculation
                        </div>
                        <span class="badge badge-primary px-2 py-1 font-weight-bold" style="border-radius: 6px;">HR POLICY</span>
                    </div>

                    <div id="sandwichPreviewCard">
                        <!-- 1. Day Counts Grid -->
                        <div class="row mb-3">
                            <div class="col-6 mb-2">
                                <div class="preview-pill">
                                    <div class="preview-lbl">Calendar Days</div>
                                    <div class="preview-num" id="prev_cal_days">0</div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="preview-pill">
                                    <div class="preview-lbl">Working Days</div>
                                    <div class="preview-num text-success" id="prev_work_days">0</div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="preview-pill">
                                    <div class="preview-lbl">Sandwich Days</div>
                                    <div class="preview-num text-warning" id="prev_sand_days">0</div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="preview-pill" style="border-color: #10B981; background: #ECFDF5;">
                                    <div class="preview-lbl" style="color: #047857;">Payable (Paid) Days</div>
                                    <div class="preview-num" style="color: #047857;" id="prev_payable_paid_days">0.0</div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Sandwich Rule Alert Badge -->
                        <div id="sandwichBadge" class="alert alert-warning border-0 shadow-sm p-3 mb-3" style="border-radius: 12px; display: none;">
                            <div class="d-flex align-items-center gap-2 font-weight-bold text-dark mb-1" style="font-size: 13px;">
                                <i class="fas fa-bread-slice text-warning"></i> Sandwich Rule Applied!
                            </div>
                            <div class="small text-muted">Intervening holidays/week-offs are included in total leave span (Total Deducted: <strong id="prev_total_ded_text">0.0</strong> Days).</div>
                        </div>

                        <!-- 3. Quota Split Breakdown -->
                        <div class="p-3 mb-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px;">
                            <div class="font-weight-bold text-dark mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em;">Leave Quota Deduction Split</div>
                            
                            <div class="split-badge mb-2" style="background: #ECFDF5; color: #047857;">
                                <span><i class="fas fa-star mr-1"></i> Paid Leave Deducted</span>
                                <strong id="prev_paid_days">0.0 Days</strong>
                            </div>

                            <div class="split-badge mb-2" style="background: #FEF2F2; color: #B91C1C;">
                                <span><i class="fas fa-heartbeat mr-1"></i> Sick Leave Deducted</span>
                                <strong id="prev_sick_days">0.0 Days</strong>
                            </div>

                            <div class="split-badge mb-2" style="background: #F5F3FF; color: #6D28D9;">
                                <span><i class="fas fa-clock mr-1"></i> Comp-Off Deducted</span>
                                <strong id="prev_comp_days">0.0 Days</strong>
                            </div>

                            <div class="split-badge" style="background: #F1F5F9; color: #334155;">
                                <span><i class="fas fa-user-clock mr-1"></i> LWP (Leave Without Pay)</span>
                                <strong id="prev_lwp_days">0.0 Days</strong>
                            </div>
                        </div>

                        <!-- 4. LWP Spillover Notice Banner -->
                        <div id="lwpNoticeBanner" class="alert alert-danger border-0 shadow-sm p-3 mb-0" style="border-radius: 12px; display: none;">
                            <div class="d-flex align-items-center gap-2 font-weight-bold text-danger mb-1" style="font-size: 13px;">
                                <i class="fas fa-exclamation-circle"></i> Monthly Quota Exceeded (LWP Spillover)
                            </div>
                            <div class="small text-danger" id="lwpNoticeText">
                                Paid leave quota for this month is exhausted. Excess days will automatically be deducted as Leave Without Pay (LWP).
                            </div>
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
    function setLeaveMode(mode) {
        document.getElementById('leave_mode_input').value = mode;
        var btnSingle = document.getElementById('btnModeSingle');
        var btnMultiple = document.getElementById('btnModeMultiple');
        var singleContainer = document.getElementById('single_date_container');
        var startContainer = document.getElementById('start_date_container');
        var endContainer = document.getElementById('end_date_container');
        var halfDaySection = document.getElementById('half_day_section');
        var halfDayCheck = document.getElementById('is_half_day');

        if (mode === 'single') {
            btnSingle.classList.add('active');
            btnMultiple.classList.remove('active');
            singleContainer.style.display = 'block';
            startContainer.style.display = 'none';
            endContainer.style.display = 'none';
            halfDaySection.style.display = 'block';
            syncSingleDate();
        } else {
            btnMultiple.classList.add('active');
            btnSingle.classList.remove('active');
            singleContainer.style.display = 'none';
            startContainer.style.display = 'block';
            endContainer.style.display = 'block';
            
            // Multiple Days -> DISABLE & HIDE Half Day Option
            halfDaySection.style.display = 'none';
            halfDayCheck.checked = false;
            toggleHalfDaySession();
        }

        triggerLivePreview();
    }

    function syncSingleDate() {
        var singleVal = document.getElementById('single_date_input').value;
        document.getElementById('start_date').value = singleVal;
        document.getElementById('end_date').value = singleVal;
    }

    function toggleHalfDaySession() {
        var isHalfDay = document.getElementById('is_half_day').checked;
        var sessionContainer = document.getElementById('half_day_type_container');
        if (isHalfDay) {
            sessionContainer.style.display = 'block';
        } else {
            sessionContainer.style.display = 'none';
        }
    }

    function triggerLivePreview() {
        var leaveTypeId = document.getElementById('leave_type_id').value;
        var startDate = document.getElementById('start_date').value;
        var endDate = document.getElementById('end_date').value;
        var isHalfDay = document.getElementById('is_half_day').checked;
        var halfDayType = document.querySelector('input[name="half_day_type"]:checked') ? document.querySelector('input[name="half_day_type"]:checked').value : 'first_half';
        var emergencyLeave = document.getElementById('emergency_leave').checked;

        if (!leaveTypeId || !startDate || !endDate) {
            return;
        }

        fetch("{{ route('leave-requests.preview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
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
                document.getElementById('prev_cal_days').textContent = d.requested_calendar_days;
                document.getElementById('prev_work_days').textContent = d.working_days;
                document.getElementById('prev_sand_days').textContent = d.sandwich_days;
                document.getElementById('prev_payable_paid_days').textContent = Number(d.paid_days).toFixed(1);
                if (document.getElementById('prev_total_ded_text')) {
                    document.getElementById('prev_total_ded_text').textContent = Number(d.deducted_days).toFixed(1);
                }

                document.getElementById('prev_paid_days').textContent = Number(d.paid_days).toFixed(1) + ' Days';
                document.getElementById('prev_sick_days').textContent = Number(d.sick_days).toFixed(1) + ' Days';
                document.getElementById('prev_comp_days').textContent = Number(d.comp_off_days).toFixed(1) + ' Days';
                document.getElementById('prev_lwp_days').textContent = Number(d.lwp_days).toFixed(1) + ' Days';

                // Sandwich rule badge
                var sandwichBadge = document.getElementById('sandwichBadge');
                if (d.sandwich_applied || d.sandwich_days > 0) {
                    sandwichBadge.style.display = 'block';
                } else {
                    sandwichBadge.style.display = 'none';
                }

                // LWP Spillover Notice
                var lwpNoticeBanner = document.getElementById('lwpNoticeBanner');
                var lwpNoticeText = document.getElementById('lwpNoticeText');
                if (d.lwp_days > 0) {
                    lwpNoticeBanner.style.display = 'block';
                    lwpNoticeText.textContent = 'Monthly paid leave quota is exceeded/exhausted. ' + Number(d.lwp_days).toFixed(1) + ' day(s) will automatically spill over and be deducted as LWP (Leave Without Pay).';
                } else {
                    lwpNoticeBanner.style.display = 'none';
                }
            }
        })
        .catch(function(err) {
            console.warn('Preview error:', err);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setLeaveMode('single');
    });
</script>
@endsection
