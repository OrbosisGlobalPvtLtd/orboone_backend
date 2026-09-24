<!-- REJECT MODAL -->
<div class="modal fade glass-modal" id="rejectModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #EF4444, #DC2626) !important;">
                <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i> Reject Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('hrms.attendance.regularizations.reject', $row->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-times-circle text-danger mb-3" style="font-size: 56px;"></i>
                        <h4 class="font-weight-bold mb-2">Reject Request?</h4>
                        <p class="text-muted">Explain the reason for rejecting <strong>{{ $row->employee_display_name ?? $row->name }}</strong>'s regularization request.</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Type the reason for rejection here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: rgba(255,255,255,0.5);">
                    <button type="button" class="btn btn-light" style="border-radius: 10px;" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold px-4" style="border-radius: 10px; background: #EF4444; border: 0;">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
