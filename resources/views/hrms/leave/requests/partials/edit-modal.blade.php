<!-- ==================================================
     EDIT LEAVE REQUEST MODAL (ENHANCED PREMIUM UI)
     ================================================== -->
<div class="modal fade" id="editLeaveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <form id="editLeaveForm" method="POST" action="" enctype="multipart/form-data" class="modal-content border-0">
            @csrf
            @method('PUT')
            
            <!-- Modal Header (Slim Orbo Theme) -->
            <div class="modal-header border-0 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important; color: #fff; padding: 11px 22px; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <div style="width: 32px; height: 32px; border-radius: 9px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.32); display: flex; align-items: center; justify-content: center; font-size: 13.5px; color: #fff; margin-right: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.12);">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 15.5px; letter-spacing: -0.2px;">Edit Leave Request</h5>
                        <span class="badge font-weight-bold px-2 py-0.5 text-white" style="border-radius: 6px; font-size: 10px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.35);">Policy Engine</span>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close" style="background: rgba(255, 255, 255, 0.2); width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.3); font-size: 13px; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body (2-Column Grid) -->
            <div class="modal-body">
                <div class="row">
                    <!-- LEFT COLUMN: Form Fields -->
                    <div class="col-lg-7 col-12 mb-3 mb-lg-0 pr-lg-3">
                        <div class="bg-white p-3.5 p-md-4 rounded-xl border" style="border-color: #E2E8F0 !important; border-radius: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <span class="font-weight-bold text-dark d-flex align-items-center" style="font-size: 13.5px;">
                                    <i class="fas fa-file-signature text-primary mr-2" style="font-size: 14px;"></i> Application Details
                                </span>
                                <span class="text-muted" style="font-size: 11px;">Mandatory fields marked <span class="text-danger">*</span></span>
                            </div>

                            <!-- Leave Type -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1 d-flex align-items-center justify-content-between" style="font-size: 12.5px;">
                                    <span><i class="fas fa-layer-group text-primary mr-1.5"></i> Leave Type <span class="text-danger">*</span></span>
                                </label>
                                <select name="leave_type_id" id="edit_leave_type_id" class="form-control select2-searchable shadow-none" style="border-radius: 12px; height: 44px; font-weight: 600;" required>
                                    @php
                                        $typesList = $leaveTypes ?? \App\Models\HRMS\Leave\LeaveTypeM::where('is_active', true)->get();
                                    @endphp
                                    @foreach($typesList as $lt)
                                        <option value="{{ $lt->id }}" 
                                            data-code="{{ $lt->code }}"
                                            data-is-comp-off="{{ $lt->is_comp_off ? 1 : 0 }}"
                                            data-is-sick="{{ $lt->is_sick ? 1 : 0 }}"
                                            data-is-paid="{{ ($lt->is_paid && !$lt->is_sick && !$lt->is_comp_off) ? 1 : 0 }}"
                                            data-is-lwp="{{ $lt->is_lwp ? 1 : 0 }}"
                                            data-halfday="{{ $lt->allow_half_day ? 1 : 0 }}">
                                            {{ $lt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date Range Row -->
                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark mb-1" style="font-size: 12.5px;">
                                            <i class="fas fa-calendar-alt text-primary mr-1.5"></i> Start Date <span class="text-danger">*</span>
                                        </label>
                                        <x-form.date-picker name="start_date" id="edit_start_date" placeholder="dd-mm-yyyy" class="shadow-none" style="border-radius: 12px; height: 44px;" required />
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark mb-1" style="font-size: 12.5px;">
                                            <i class="fas fa-calendar-check text-primary mr-1.5"></i> End Date <span class="text-danger">*</span>
                                        </label>
                                        <x-form.date-picker name="end_date" id="edit_end_date" placeholder="dd-mm-yyyy" class="shadow-none" style="border-radius: 12px; height: 44px;" required />
                                        <small id="edit_end_date_hint" class="text-muted mt-1" style="font-size: 11px; display: none;">
                                            <i class="fas fa-lock mr-1 text-warning"></i> Auto-locked to Start Date for half-day
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Half Day & Emergency Leave Options -->
                            <div class="row mb-3">
                                <!-- Half Day Card -->
                                <div class="col-md-6 col-12 mb-2 mb-md-0">
                                    <div class="edit-option-box d-flex flex-column justify-content-center" id="edit_half_day_card">
                                        <div class="custom-control custom-checkbox d-flex align-items-center">
                                            <input type="checkbox" class="custom-control-input" id="edit_is_half_day" name="is_half_day" value="1">
                                            <label class="custom-control-label font-weight-bold text-dark mb-0" for="edit_is_half_day" style="font-size: 12.5px; cursor: pointer; user-select: none;">
                                                Half Day Leave
                                            </label>
                                        </div>

                                        <!-- Session Selector -->
                                        <div id="edit_half_day_type_container" class="mt-2 pt-2 border-top" style="display: none;">
                                            <div class="d-flex align-items-center" style="gap: 12px;">
                                                <div class="custom-control custom-radio custom-control-inline mr-0">
                                                    <input type="radio" id="edit_session_first" name="half_day_type" value="first_half" class="custom-control-input" checked>
                                                    <label class="custom-control-label font-weight-bold text-dark small" for="edit_session_first" style="cursor: pointer; font-size: 11.5px;">☀️ 1st Half</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline mr-0">
                                                    <input type="radio" id="edit_session_second" name="half_day_type" value="second_half" class="custom-control-input">
                                                    <label class="custom-control-label font-weight-bold text-dark small" for="edit_session_second" style="cursor: pointer; font-size: 11.5px;">🌙 2nd Half</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emergency Leave Card -->
                                <div class="col-md-6 col-12">
                                    <div class="edit-option-box d-flex flex-column justify-content-center">
                                        <div class="custom-control custom-checkbox d-flex align-items-center">
                                            <input type="checkbox" class="custom-control-input" id="edit_emergency_leave" name="emergency_leave" value="1">
                                            <label class="custom-control-label font-weight-bold text-dark mb-0" for="edit_emergency_leave" style="font-size: 12.5px; cursor: pointer; user-select: none;">
                                                Emergency Leave
                                            </label>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>

                            <!-- Reason / Notes -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1" style="font-size: 12.5px;">
                                    <i class="fas fa-comment-alt text-primary mr-1.5"></i> Reason / Explanation <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control shadow-none" name="reason" id="edit_leave_reason" rows="2" style="border-radius: 12px; font-size: 13px; line-height: 1.5;" required placeholder="Describe the reason for your leave request..."></textarea>
                            </div>

                            <!-- Supporting Document / Attachment -->
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark mb-1 d-flex align-items-center justify-content-between" style="font-size: 12.5px;">
                                    <span><i class="fas fa-paperclip text-primary mr-1.5"></i> Supporting Document / Medical Certificate <small class="text-muted font-weight-normal">(Optional)</small></span>
                                </label>

                                <div id="edit_existing_attachment_container" class="mb-2 p-2.5 px-3 bg-light border d-flex align-items-center justify-content-between" style="border-radius: 12px; border-color: #E2E8F0 !important; display: none;">
                                    <div class="d-flex align-items-center text-truncate mr-2">
                                        <i class="fas fa-file-pdf text-danger mr-2" style="font-size: 15px;"></i>
                                        <div>
                                            <div class="small font-weight-bold text-dark text-truncate" id="edit_existing_attachment_name" style="font-size: 12px;">Attached Document</div>
                                            <small class="text-muted" style="font-size: 10px;">Currently attached to this request</small>
                                        </div>
                                    </div>
                                    <a href="#" id="edit_current_attachment_link" target="_blank" class="btn btn-xs btn-outline-primary font-weight-bold px-2.5 py-1" style="border-radius: 8px; font-size: 11.5px; white-space: nowrap;">
                                        <i class="fas fa-external-link-alt mr-1"></i> View / Download
                                    </a>
                                </div>

                                <input type="file" name="attachment" id="edit_attachment" class="form-control" style="border-radius: 10px; height: 40px; padding: 7px 12px; font-size: 12.5px;" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                    Allowed formats: PDF, JPG, PNG, DOC (Max: 5MB). Leave empty to keep existing file.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Live Calculation Dashboard Card -->
                    <div class="col-lg-5 col-12 pl-lg-2">
                        <div class="p-3.5 p-md-4 rounded-xl border h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(180deg, #FFFFFF 0%, #FAF8FF 100%); border: 1.5px solid #E0DBF5 !important; border-radius: 18px; box-shadow: 0 4px 20px rgba(75, 0, 232, 0.04);">
                            <div>
                                <!-- Live Calculation Header -->
                                <div class="d-flex align-items-center justify-content-between mb-2.5 border-bottom pb-2">
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <div style="width: 30px; height: 30px; border-radius: 9px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13.5px; box-shadow: 0 2px 8px rgba(75,0,232,0.2);">
                                            <i class="fas fa-calculator"></i>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark" style="font-size: 13.5px; line-height: 1.2;">Live Calculation</div>
                                            <div class="text-muted small" style="font-size: 10.5px;">Real-time policy & quota engine</div>
                                        </div>
                                    </div>
                                    <span id="editCalcSpinner" class="badge badge-pill badge-primary font-weight-bold px-2 py-0.5" style="display: none; font-size: 10.5px; background: #4B00E8;">
                                        <i class="fas fa-circle-notch fa-spin mr-1"></i> Calculating...
                                    </span>
                                </div>

                                <!-- Key Metrics 4-Grid Cards -->
                                <div class="row mb-2.5" style="margin-left: -4px; margin-right: -4px;">
                                    <!-- Calendar Days -->
                                    <div class="col-6 mb-2 px-1">
                                        <div class="edit-kpi-card border text-center" style="background: #F8FAFC; border-color: #E2E8F0 !important;">
                                            <div class="text-muted font-weight-bold" style="font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.03em;">
                                                <i class="far fa-calendar-alt mr-1"></i> Calendar
                                            </div>
                                            <div class="font-weight-bold text-dark mt-0.5" style="font-size: 17px; line-height: 1.15;" id="edit_prev_cal_days">0</div>
                                            <small class="text-muted d-block" style="font-size: 9.5px; line-height: 1;">Total Span</small>
                                        </div>
                                    </div>

                                    <!-- Working Days -->
                                    <div class="col-6 mb-2 px-1">
                                        <div class="edit-kpi-card border text-center" style="background: #F0FDF4; border-color: #BBF7D0 !important;">
                                            <div class="font-weight-bold" style="color: #166534; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.03em;">
                                                <i class="fas fa-briefcase mr-1"></i> Working
                                            </div>
                                            <div class="font-weight-bold mt-0.5" style="color: #15803D; font-size: 17px; line-height: 1.15;" id="edit_prev_work_days">0</div>
                                            <small class="d-block" style="color: #166534; opacity: 0.8; font-size: 9.5px; line-height: 1;">Office Days</small>
                                        </div>
                                    </div>

                                    <!-- Sandwich Days -->
                                    <div class="col-6 mb-2 px-1">
                                        <div class="edit-kpi-card border text-center" id="edit_sand_card" style="background: #FFFBEB; border-color: #FDE68A !important;">
                                            <div class="font-weight-bold" style="color: #92400E; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.03em;">
                                                <i class="fas fa-layer-group mr-1"></i> Sandwich
                                            </div>
                                            <div class="font-weight-bold mt-0.5" style="color: #B45309; font-size: 17px; line-height: 1.15;" id="edit_prev_sand_days">0</div>
                                            <small class="d-block" style="color: #92400E; opacity: 0.8; font-size: 9.5px; line-height: 1;">Offs Counted</small>
                                        </div>
                                    </div>

                                    <!-- Hero Metric: Total Deducted -->
                                    <div class="col-6 mb-2 px-1">
                                        <div class="edit-kpi-card border-0 text-center shadow-sm" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;">
                                            <div class="font-weight-bold text-white-50" style="font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.03em;">
                                                <i class="fas fa-check-circle mr-1 text-white"></i> Deducted
                                            </div>
                                            <div class="font-weight-bold text-white mt-0.5" style="font-size: 16px; line-height: 1.15;" id="edit_prev_deducted_val">0.0 Days</div>
                                            <small class="text-white-50 d-block" style="font-size: 9.5px; line-height: 1;">Total Balance</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sandwich Rule Alert Banner -->
                                <div id="edit_sandwich_alert" class="alert border-0 p-2.5 mb-2.5 shadow-none" style="display: none; border-radius: 12px; background: #FEF3C7; border: 1.5px solid #FCD34D !important;">
                                    <div class="d-flex align-items-center font-weight-bold mb-0.5" style="font-size: 12px; color: #92400E;">
                                        <i class="fas fa-layer-group mr-1.5 text-warning"></i> Sandwich Rule Applied!
                                    </div>
                                    <div class="small" style="font-size: 11.5px; color: #78350F; line-height: 1.4;">
                                        <strong id="edit_sandwich_count_text">0</strong> intervening off/holiday day(s) counted as leave:
                                    </div>
                                    <div id="edit_sandwich_dates_list" class="mt-1.5 d-flex flex-wrap" style="gap: 5px;"></div>
                                </div>

                                <!-- Notice Warning Banner -->
                                <div id="edit_notice_alert" class="alert border-0 p-2.5 mb-2.5 shadow-none" style="display: none; border-radius: 12px; background: #FFFBEB; border-left: 4px solid #F59E0B !important;">
                                    <div class="d-flex align-items-center font-weight-bold mb-0.5" style="font-size: 12px; color: #92400E;">
                                        <i class="fas fa-exclamation-triangle mr-1.5 text-warning"></i> Policy Notice
                                    </div>
                                    <div id="edit_notice_text" class="small text-muted" style="font-size: 11.5px; line-height: 1.4;"></div>
                                </div>

                                <!-- Calculation Error Banner -->
                                <div id="edit_calc_error" class="alert border-0 p-2.5 mb-2.5 shadow-none" style="display: none; border-radius: 12px; background: #FEF2F2; border-left: 4px solid #EF4444 !important;">
                                    <div class="d-flex align-items-center font-weight-bold text-danger mb-0.5" style="font-size: 12px;">
                                        <i class="fas fa-times-circle mr-1.5"></i> Calculation Alert
                                    </div>
                                    <div id="edit_calc_error_text" class="small text-danger" style="font-size: 11.5px; line-height: 1.4;"></div>
                                </div>

                                <!-- Quota Split Breakdown Card -->
                                <div class="p-3 rounded-xl border mb-2.5" style="background: #FFFFFF; border-color: #E2E8F0 !important; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                    <div class="font-weight-bold text-dark mb-2 d-flex align-items-center justify-content-between" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">
                                        <span><i class="fas fa-chart-pie text-primary mr-1"></i> Leave Quota Split</span>
                                        <span id="edit_ledger_split_formula" class="font-weight-bold px-2 py-0.5 rounded" style="font-size: 10.5px; font-family: monospace; background: #EDE9FE; color: #5B21B6; display: none;"></span>
                                    </div>

                                    <div class="d-flex flex-column" style="gap: 6px;">
                                        <div class="d-flex align-items-center justify-content-between p-1.5 px-2.5 rounded" style="background: #F0FDF4; border: 1px solid #BBF7D0; font-size: 11.5px; color: #166534;">
                                            <span class="font-weight-bold"><i class="fas fa-check-circle mr-1 text-success"></i> Paid (PL):</span>
                                            <strong id="edit_split_paid_val">0.0 Days</strong>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between p-1.5 px-2.5 rounded" style="background: #FEF2F2; border: 1px solid #FECACA; font-size: 11.5px; color: #991B1B;">
                                            <span class="font-weight-bold"><i class="fas fa-heartbeat mr-1 text-danger"></i> Sick (SL):</span>
                                            <strong id="edit_split_sick_val">0.0 Days</strong>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between p-1.5 px-2.5 rounded" style="background: #F5F3FF; border: 1px solid #DDD6FE; font-size: 11.5px; color: #6D28D9;">
                                            <span class="font-weight-bold"><i class="fas fa-clock mr-1" style="color: #7C3AED;"></i> Comp-Off:</span>
                                            <strong id="edit_split_comp_val">0.0 Days</strong>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between p-1.5 px-2.5 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; font-size: 11.5px; color: #475569;">
                                            <span class="font-weight-bold"><i class="fas fa-user-clock mr-1 text-muted"></i> LWP (Unpaid):</span>
                                            <strong id="edit_split_lwp_val">0.0 Days</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Remaining Balance Impact Box -->
                                <div id="edit_balance_impact_box" class="p-2.5 px-3 rounded-xl border" style="background: #F0F9FF; border-color: #BAE6FD !important; border-radius: 12px; display: none;">
                                    <div class="d-flex align-items-center justify-content-between text-muted font-weight-bold mb-1" style="font-size: 10.5px; text-transform: uppercase;">
                                        <span style="color: #0369A1;"><i class="fas fa-wallet mr-1"></i> Balance Impact</span>
                                        <span class="badge badge-light text-info font-weight-bold" style="font-size: 9.5px; background: #E0F2FE;">Post-Deduction</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between" style="font-size: 12.5px;">
                                        <div>
                                            <span class="text-muted" style="font-size: 11px;">Current:</span>
                                            <strong class="text-dark ml-1" id="edit_balance_impact_curr">0.0 Days</strong>
                                        </div>
                                        <div style="color: #0284C7; font-weight bold;">
                                            <i class="fas fa-arrow-right mx-1.5" style="font-size: 11px;"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted" style="font-size: 11px;">After:</span>
                                            <strong style="color: #0369A1;" class="ml-1" id="edit_balance_impact_after">0.0 Days</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 d-flex align-items-center justify-content-between" style="background: #FAF9FE; padding: 12px 22px; border-top: 1px solid #E2E8F0 !important;">
                <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal" style="border-radius: 12px; padding: 10px 24px; border: 1.5px solid #E2E8F0; color: #475569; font-size: 13px;">
                    <i class="fas fa-times mr-1.5"></i> Cancel
                </button>
                <button type="submit" id="btnEditSaveLeave" class="btn text-white font-weight-bold shadow" style="border-radius: 12px; padding: 10px 32px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important; border: none; font-size: 13px; box-shadow: 0 4px 14px rgba(75,0,232,0.25) !important;">
                    <i class="fas fa-check-circle mr-1.5"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
