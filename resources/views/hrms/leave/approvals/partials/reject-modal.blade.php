<!-- REJECT MODAL -->
<div class="modal fade" id="rejectModal{{ $lr->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered leave-modal-dialog" role="document" style="max-width: 500px;">
        <div class="modal-content border-0 shadow-lg leave-modal-content">
            <div class="modal-header text-white px-3 px-sm-4 py-3 align-items-center justify-content-between leave-modal-header" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);">
                <div class="d-flex align-items-center" style="gap: 10px; min-width: 0;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); border: 1px solid rgba(255, 255, 255, 0.3); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.12); flex-shrink: 0;">
                        <i class="fas fa-times-circle text-white"></i>
                    </div>
                    <div style="min-width: 0;">
                        <h5 class="modal-title font-weight-bold text-white mb-0 text-truncate" style="font-size: 15px; letter-spacing: 0.2px;">
                            Reject Leave Request
                        </h5>
                        <div class="text-white-50 text-truncate" style="font-size: 10.5px; font-weight: 500; opacity: 0.92;">
                            Request ID: #LR-{{ str_pad($lr->id, 4, '0', STR_PAD_LEFT) }}
                        </div>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-10 border-0 ml-2" style="width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 18px; outline: none; line-height: 1; flex-shrink: 0;" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('leave-approvals.reject', $lr->id) }}" onsubmit="var btn = this.querySelector('button[type=submit]'); if(btn) { btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin mr-1\'></i> Rejecting...'; }">
                @csrf
                <div class="modal-body px-3 px-sm-4 py-3 bg-white" style="overflow-y: auto;">
                    <p class="text-dark font-weight-bold mb-2" style="font-size: 13px; line-height: 1.4;">Are you sure you want to reject the leave request for <strong>{{ $lr->display_name }}</strong>?</p>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-muted uppercase mb-1" style="font-size: 10px; letter-spacing: 0.4px;">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required minlength="3" maxlength="1000" style="border-radius: 10px; font-size: 12.5px;" placeholder="Enter rejection reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-3 px-sm-4 py-2.5 align-items-center justify-content-between leave-modal-footer">
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; color: #475569; font-size: 12.5px;" data-dismiss="modal">Cancel</button>
                    <div class="leave-modal-footer-actions">
                        <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #DC2626; font-size: 12.5px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);">
                            <i class="fas fa-times mr-1"></i> Reject Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

