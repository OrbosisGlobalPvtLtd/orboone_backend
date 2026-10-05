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
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: #FEF2F2; color: #EF4444; font-size: 32px; border: 2px solid #FECACA;">
                            <i class="fas fa-times"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1">Reject Regularization Request?</h5>
                        <p class="text-muted small">Please provide the rejection rationale for <strong>{{ $row->employee_display_name ?? $row->name }}</strong>.</p>
                    </div>
                    <div class="form-group mb-1">
                        <label class="font-weight-bold text-dark small text-uppercase">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Type the reason for rejection here..." style="border-radius: 10px; resize: vertical;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border font-weight-bold" style="border-radius: 10px; font-size: 13px;" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger font-weight-bold px-4 shadow-sm" style="border-radius: 10px; background: #EF4444; border: 0; font-size: 13px;">
                        <i class="fas fa-ban mr-1"></i> Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
