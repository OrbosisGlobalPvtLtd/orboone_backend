<div class="modal fade orb-type-modal" id="deleteAllocationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="" id="deleteAllocationForm" class="modal-content leave-modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header leave-modal-header bg-danger text-white">
                <div>
                    <h5 class="leave-modal-title text-white"><i class="fas fa-shield-alt mr-2"></i>Allocation Record</h5>
                    <div class="leave-modal-subtitle text-white-50">Records are retained for balance and audit history.</div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body leave-modal-body">
                <p class="font-weight-bold mb-1" id="delete_employee_name">Allocation cannot be deleted</p>
                <p class="text-muted">Allocation: <strong id="delete_employee_name"></strong></p>
                <div class="alert alert-warning">Deletion is blocked when ledger transactions reference this row.</div>
                <label for="delete_reason">Deletion reason</label>
                <input class="form-control" id="delete_reason" name="reason" maxlength="255" required>
            </div>
            <div class="modal-footer leave-modal-footer">
                <button type="button" class="leave-btn-light" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Delete allocation</button>
            </div>
        </form>
    </div>
</div>
