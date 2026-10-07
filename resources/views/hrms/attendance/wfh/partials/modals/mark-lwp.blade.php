@if($canMarkLwp ?? false)
<div class="modal fade" id="markLwpModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form id="markLwpForm" method="POST" class="modal-content orb-modal-content border-0">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i> Mark WFH as LWP</h5>
                    <small class="text-white-50 d-block">Set attendance status to LWP with reason and optional remarks.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="orb-form-group mb-3">
                    <label class="orb-form-label">LWP Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="lwp_reason" rows="3" required placeholder="State reason for converting to LWP..."></textarea>
                </div>
                <div class="orb-form-group mb-0">
                    <label class="orb-form-label">Remarks (Optional)</label>
                    <textarea class="form-control" name="remarks" rows="2" placeholder="Audit remarks..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">Cancel</button>
                <button class="orb-btn btn-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Confirm LWP</button>
            </div>
        </form>
    </div>
</div>
@endif
