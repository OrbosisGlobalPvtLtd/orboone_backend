<div class="modal fade" id="wfhDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content orb-modal-content border-0">
            <div class="modal-header">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="orb-icon-box" style="background: rgba(255,255,255,0.2); color: #fff; border: 0; width: 40px; height: 40px; font-size: 16px;">
                        <i class="fas fa-home"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">WFH Request Details</h5>
                        <small class="text-white-50 d-block" style="font-size: 11.5px;">Complete breakdown of dates, quota impact, and approval history</small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <!-- Employee & Status Hero Pill Card -->
                <div class="orb-detail-emp-banner mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                        <div class="d-flex align-items-center" style="gap: 12px;">
                            <div class="orb-emp-avatar-box">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold text-dark" id="d_employee" style="font-size: 15px;">-</h6>
                                <span class="badge badge-light font-weight-bold text-muted" id="d_employee_code" style="font-size: 11px;">-</span>
                            </div>
                        </div>
                        <div id="d_status_badge">
                            <!-- Populated with badge -->
                        </div>
                    </div>
                </div>

                <!-- Date & Days Metric Cards (Grid of 4) -->
                <div class="orb-detail-section mb-3">
                    <div class="orb-detail-title"><i class="fas fa-calendar-alt"></i> Schedule & Days Breakdown</div>
                    
                    <!-- Date Range Box -->
                    <div class="orb-date-range-card mb-3">
                        <div class="row no-gutters text-center">
                            <div class="col-6 border-right pr-2">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 10px;">From Date</span>
                                <strong class="text-dark" id="d_from_date" style="font-size: 13px;">-</strong>
                            </div>
                            <div class="col-6 pl-2">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 10px;">To Date</span>
                                <strong class="text-dark" id="d_to_date" style="font-size: 13px;">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="gap: 8px 0;">
                        <div class="col-6 col-md-3">
                            <div class="orb-stat-pill">
                                <span class="orb-stat-pill-label">Working Days</span>
                                <strong class="orb-stat-pill-val text-primary" id="d_working_days">-</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="orb-stat-pill">
                                <span class="orb-stat-pill-label">Total Days</span>
                                <strong class="orb-stat-pill-val text-dark" id="d_total_days">-</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="orb-stat-pill">
                                <span class="orb-stat-pill-label">Weekends</span>
                                <strong class="orb-stat-pill-val text-muted" id="d_weekoff_days">-</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="orb-stat-pill">
                                <span class="orb-stat-pill-label">Holidays</span>
                                <strong class="orb-stat-pill-val text-muted" id="d_holiday_days">-</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Request Info & Policy Grid -->
                <div class="orb-detail-section mb-3">
                    <div class="orb-detail-title"><i class="fas fa-sliders-h"></i> Request Information & Policy</div>
                    <div class="row" style="gap: 10px 0;">
                        <div class="col-6 col-md-6">
                            <div class="orb-info-tile">
                                <span class="orb-info-tile-label">Request Type</span>
                                <strong class="orb-info-tile-val" id="d_type">-</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="orb-info-tile">
                                <span class="orb-info-tile-label">Reason Category</span>
                                <strong class="orb-info-tile-val" id="d_reason_cat">-</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="orb-info-tile">
                                <span class="orb-info-tile-label">Quota Impact</span>
                                <div id="d_quota">-</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="orb-info-tile">
                                <span class="orb-info-tile-label">Payroll Impact</span>
                                <div id="d_payroll">-</div>
                            </div>
                        </div>
                        <div class="col-12" id="d_lwp_box" style="display: none;">
                            <div class="orb-info-tile border-warning" style="background: #FFFBEB;">
                                <span class="orb-info-tile-label text-warning font-weight-bold">LWP Reason</span>
                                <div class="orb-info-tile-val text-dark" id="d_lwp_reason">-</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="orb-info-tile">
                                <span class="orb-info-tile-label">Reason / Purpose</span>
                                <div class="orb-info-tile-val text-dark" id="d_reason" style="white-space: pre-wrap; font-size: 12.5px;">-</div>
                            </div>
                        </div>
                        <div class="col-12" id="d_assigned_by_box" style="display: none;">
                            <div class="orb-info-tile">
                                <span class="orb-info-tile-label">Assigned By</span>
                                <div class="orb-info-tile-val" id="d_assigned_by">-</div>
                            </div>
                        </div>
                        <div class="col-12" id="d_remarks_box" style="display: none;">
                            <div class="orb-info-tile">
                                <span class="orb-info-tile-label">Remarks</span>
                                <div class="orb-info-tile-val" id="d_remarks">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval & Audit History -->
                <div class="orb-detail-section mb-0">
                    <div class="orb-detail-title"><i class="fas fa-history"></i> Approval & Audit History</div>
                    <div class="orb-timeline-grid">
                        <div class="orb-timeline-card">
                            <div class="orb-tl-icon bg-light text-primary"><i class="fas fa-paper-plane"></i></div>
                            <div class="orb-tl-content">
                                <span class="orb-tl-label">Applied Date</span>
                                <strong class="orb-tl-val" id="d_applied_at">-</strong>
                            </div>
                        </div>
                        <div class="orb-timeline-card">
                            <div class="orb-tl-icon bg-light text-success"><i class="fas fa-user-check"></i></div>
                            <div class="orb-tl-content">
                                <span class="orb-tl-label">Manager Approved At</span>
                                <strong class="orb-tl-val" id="d_mgr_at">-</strong>
                            </div>
                        </div>
                        <div class="orb-timeline-card">
                            <div class="orb-tl-icon bg-light text-info"><i class="fas fa-shield-alt"></i></div>
                            <div class="orb-tl-content">
                                <span class="orb-tl-label">HR Approved At</span>
                                <strong class="orb-tl-val" id="d_hr_at">-</strong>
                            </div>
                        </div>
                        <div class="orb-timeline-card" id="d_rej_card" style="display: none;">
                            <div class="orb-tl-icon bg-danger-light text-danger"><i class="fas fa-times-circle"></i></div>
                            <div class="orb-tl-content">
                                <span class="orb-tl-label text-danger font-weight-bold">Rejected At & Reason</span>
                                <strong class="orb-tl-val text-danger" id="d_rej_at">-</strong>
                                <small class="d-block text-muted mt-1" id="d_rej_reason">-</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Close</button>
            </div>
        </div>
    </div>
</div>
