@if($canReject)
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form id="rejectForm" method="POST" class="modal-content orb-modal-content border-0">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i> Reject WFH Request</h5>
                    <small class="text-white-50 d-block">Rejection reason is mandatory.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="orb-form-group mb-0">
                    <label class="orb-form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="rejection_reason" rows="3" required placeholder="State reason for rejecting request..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">Cancel</button>
                <button class="orb-btn btn-danger font-weight-bold"><i class="fas fa-times"></i> Confirm Reject</button>
            </div>
        </form>
    </div>
</div>
@endif
