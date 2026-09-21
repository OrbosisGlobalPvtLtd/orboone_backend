@if($canApprove)
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form id="approveForm" method="POST" class="modal-content orb-modal-content border-0">
            @csrf
            <input type="hidden" name="override_quota" id="approve_override_quota" value="0">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-check-circle mr-2"></i> Approve WFH Request</h5>
                    <small class="text-white-50 d-block">Confirm full or partial WFH approval for this request.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="approveQuotaWarning" class="alert alert-warning border-0 shadow-sm mb-3 d-none" style="border-radius: 12px; background: #FFFAEB; color: #B54708;">
                    <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Monthly WFH Limit Exceeded</div>
                    <div class="small mb-1">
                        <strong>Limit:</strong> <span id="approve_q_limit">0</span> Days &nbsp;|&nbsp;
                        <strong>Approved:</strong> <span id="approve_q_used">0</span> Days &nbsp;|&nbsp;
                        <strong>Requested:</strong> <span id="approve_q_req">0</span> Working Days
                    </div>
                    <div class="small text-dark font-weight-semibold">This request exceeds the employee's monthly WFH quota. Do you still want to approve it?</div>
                </div>

                <div class="orb-form-group mb-3">
                    <label class="orb-form-label"><i class="fas fa-calendar-check text-primary mr-1"></i> Partial Approval (Optional Date Range)</label>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted font-weight-bold d-block mb-1">Approved From</small>
                            <input type="date" class="form-control" name="approved_from_date">
                        </div>
                        <div class="col-6">
                            <small class="text-muted font-weight-bold d-block mb-1">Approved To</small>
                            <input type="date" class="form-control" name="approved_to_date">
                        </div>
                    </div>
                    <small class="form-text text-muted mt-1">Leave empty to approve full requested period.</small>
                </div>
                <div class="orb-form-group mb-0">
                    <label class="orb-form-label">Remarks / Audit Note (Optional)</label>
                    <input type="text" class="form-control" name="remarks" placeholder="Optional remarks for audit logs">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" id="approveSubmitBtn" class="orb-btn orb-btn-gradient"><i class="fas fa-check"></i> Confirm Approve</button>
            </div>
        </form>
    </div>
</div>
@endif
