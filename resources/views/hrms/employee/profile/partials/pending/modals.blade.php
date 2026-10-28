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

{{-- Permanent Delete Modal --}}
<div class="confirm-modal" id="permanentDeleteModal">
    <div class="confirm-box confirm-box-sm">
        <div class="confirm-header delete-header">
            <div>
                <h5 class="confirm-title">
                    <i class="fas fa-trash-alt mr-2"></i> Permanent Delete
                </h5>
                <p class="confirm-subtitle">Permanently delete employee & all records.</p>
            </div>
            <button type="button" class="confirm-close-btn" id="closeDeleteModalBtn">&times;</button>
        </div>
        <div class="confirm-body" style="padding: 18px 24px;">
            <div class="confirm-alert reject-alert" style="margin-bottom: 16px; font-size: 13px; line-height: 1.5;">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Warning:</strong> This action is permanent and irreversible. The employee record, onboarding profile, uploaded documents, attendance history, and system user account will be completely deleted.
            </div>

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">Employee</div>
                    <div id="deleteEmpName" style="font-size: 14.5px; font-weight: 800; color: #0F172A; margin-top: 1px;"></div>
                </div>
                <div>
                    <span id="deleteEmpCode" class="code-badge" style="font-size: 12px; padding: 4px 10px;"></span>
                </div>
            </div>

            <label class="font-weight-bold" style="font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                Type <strong style="color: #D92D20; user-select: all;">DELETE EMPLOYEE</strong> to confirm: <span class="text-danger">*</span>
            </label>
            <input type="text" id="deleteConfirmInput" class="form-control" placeholder="Type DELETE EMPLOYEE" autocomplete="off" style="border: 1px solid #CBD5E1; border-radius: 8px; padding: 9px 14px; font-size: 13.5px; font-weight: 700; letter-spacing: 0.5px;">
            <div id="deleteConfirmError" class="text-danger mt-1 font-weight-bold" style="font-size: 12px; display: none;">
                Please type exact "DELETE EMPLOYEE" to confirm.
            </div>

            <form id="permanentDeleteForm" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
                <input type="hidden" name="delete_mode" value="permanent">
                <input type="hidden" name="confirm_text" value="DELETE EMPLOYEE">
            </form>
        </div>
        <div class="confirm-footer" style="padding: 14px 24px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn btn-secondary btn-cancel" id="cancelDeleteModal" style="padding: 7px 16px; font-size: 13px;">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmPermanentDeleteBtn" style="background: #D92D20; border-color: #D92D20; font-weight: 700; border-radius: 8px; padding: 7px 18px; font-size: 13px;">
                <i class="fas fa-trash-alt mr-1"></i> Delete Permanently
            </button>
        </div>
    </div>
</div>
