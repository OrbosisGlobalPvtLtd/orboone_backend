<div class="modal fade" id="editWfhModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form id="editWfhForm" method="POST" action="" class="modal-content orb-modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Edit WFH Request</h5>
                    <small class="text-white-50 d-block">Modify details and dates for this Work From Home request.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Employee Info Preview -->
                <div class="orb-detail-emp-banner mb-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <div class="orb-emp-avatar-box">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 14px;" id="edit_employee_name">-</div>
                            <div class="text-muted small" id="edit_employee_code">-</div>
                        </div>
                    </div>
                    <div id="edit_current_status_badge"></div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="orb-form-label">From Date <span class="text-danger">*</span></label>
                        <x-form.date-picker name="from_date" id="edit_wfh_from_date" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="orb-form-label">To Date <span class="text-danger">*</span></label>
                        <x-form.date-picker name="to_date" id="edit_wfh_to_date" required />
                    </div>
                </div>

                <div id="edit_wfh_calc_box" class="p-3 mb-3 d-none" style="background:#F8F9FA; border-radius:12px; border:1px solid #E9ECEF;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-primary font-weight-bold" id="edit_wfh_calc_period" style="font-size:13px;">Requested Period</strong>
                        <span class="badge badge-primary" id="edit_wfh_calc_total">0 Days</span>
                    </div>
                    <div class="row text-center small">
                        <div class="col-4">
                            <div class="text-muted">Working Days</div>
                            <strong class="text-success" style="font-size:16px;" id="edit_wfh_calc_working">0</strong>
                        </div>
                        <div class="col-4">
                            <div class="text-muted">Weekly Off</div>
                            <strong class="text-warning" style="font-size:16px;" id="edit_wfh_calc_weekoff">0</strong>
                        </div>
                        <div class="col-4">
                            <div class="text-muted">Holidays</div>
                            <strong class="text-info" style="font-size:16px;" id="edit_wfh_calc_holiday">0</strong>
                        </div>
                    </div>
                    <div class="mt-2 text-center text-dark font-weight-bold pt-2 border-top" style="font-size:12px;">
                        Actual WFH Days: <span class="text-success font-weight-bold" id="edit_wfh_calc_actual">0</span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="orb-form-label">Reason Category <span class="text-danger">*</span></label>
                    <select name="reason_category" id="edit_wfh_reason_category" class="form-control select2-modal-searchable select2-searchable" required style="border-radius: 10px;">
                        <option value="" disabled>-- Select WFH Reason Category --</option>
                        <option value="personal_reason">Personal / Family Work</option>
                        <option value="health_medical">Health & Medical Care</option>
                        <option value="commute_disruption">Commute & Transport Disruption</option>
                        <option value="home_maintenance">Home Maintenance & Delivery</option>
                        <option value="severe_weather">Severe Weather / Local Disruption</option>
                        <option value="focused_work">Deep Focused Project Work</option>
                        <option value="normal">Normal</option>
                        <option value="company_assigned">Company Assigned</option>
                        <option value="other">Other Valid Reason</option>
                    </select>
                </div>

                @if($isHrOrAdmin ?? false)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="orb-form-label">Status</label>
                        <select name="status" id="edit_wfh_status" class="form-control select2-modal-searchable select2-searchable" style="border-radius: 10px;">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="orb-form-label">Payroll Impact</label>
                        <select name="payroll_impact" id="edit_wfh_payroll_impact" class="form-control select2-modal-searchable select2-searchable" style="border-radius: 10px;">
                            <option value="none">None (Paid)</option>
                            <option value="lwp">LWP (Loss of Pay)</option>
                        </select>
                    </div>
                </div>
                @endif

                <div class="form-group mb-0">
                    <label class="orb-form-label">Reason Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="reason" id="edit_wfh_reason" rows="3" required placeholder="Describe your reason for requesting WFH..." style="border-radius: 10px;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="orb-btn orb-btn-gradient"><i class="fas fa-save mr-1"></i> Update Request</button>
            </div>
        </form>
    </div>
</div>
