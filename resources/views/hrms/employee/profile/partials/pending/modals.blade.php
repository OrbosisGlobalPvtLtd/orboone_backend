{{-- Approve Profile Modal --}}
<div class="confirm-modal" id="approveModal">
    <div class="confirm-box">
        <div class="confirm-header approve-header">
            <div>
                <h5 class="confirm-title">
                    <i class="fas fa-check-circle mr-2"></i> Approve Employee Profile
                </h5>
                <p class="confirm-subtitle">Verify and activate this profile in the Employee Directory.</p>
            </div>
            <button type="button" class="confirm-close-btn" onclick="document.getElementById('cancelApprove').click();">&times;</button>
        </div>
        <div class="confirm-body">
            <div class="confirm-alert approve-alert">
                <i class="fas fa-info-circle mr-1"></i>
                After approval, this employee will appear in Employee Directory and will be removed from Pending Profiles.
            </div>
        </div>
        <div class="confirm-footer">
            <button type="button" class="btn btn-secondary btn-cancel" id="cancelApprove">Cancel</button>
            <button type="button" class="btn btn-success btn-confirm" id="confirmApprove">
                <i class="fas fa-check-circle mr-1"></i> Yes, Approve
            </button>
        </div>
    </div>
</div>

{{-- Reject Profile Modal --}}
<div class="confirm-modal" id="rejectModal">
    <div class="confirm-box">
        <div class="confirm-header reject-header">
            <div>
                <h5 class="confirm-title">
                    <i class="fas fa-times-circle mr-2"></i> Reject Employee Profile
                </h5>
                <p class="confirm-subtitle">Add a reason so the employee/HR can correct the profile details.</p>
            </div>
            <button type="button" class="confirm-close-btn" onclick="document.getElementById('cancelReject').click();">&times;</button>
        </div>
        <div class="confirm-body">
            <div class="confirm-alert reject-alert">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                This profile will be marked as rejected and sent back to the employee for necessary corrections.
            </div>
            <label class="font-weight-bold" style="font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                Rejection Reason <span class="text-danger">*</span>
            </label>
            <textarea id="rejectReasonBox" class="reject-textarea form-control" rows="3" placeholder="Enter clear rejection reason or missing details..."></textarea>
        </div>
        <div class="confirm-footer">
            <button type="button" class="btn btn-secondary btn-cancel" id="cancelReject">Cancel</button>
            <button type="button" class="btn btn-danger btn-reject" id="confirmReject">
                <i class="fas fa-times-circle mr-1"></i> Reject Profile
            </button>
        </div>
    </div>
</div>
