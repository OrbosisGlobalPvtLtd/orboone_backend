<!-- Single Reusable Delete Confirmation Modal -->
<div class="modal fade orb-type-modal" id="deleteAllocationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="" id="deleteAllocationForm" class="modal-content leave-modal-content">
            @csrf
            @method('DELETE')

            <div class="modal-header leave-modal-header bg-danger text-white">
                <div>
                    <h5 class="leave-modal-title text-white">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Delete Leave Allocation
                    </h5>
                    <div class="leave-modal-subtitle text-white-50">Confirm deletion</div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>

            <div class="modal-body leave-modal-body text-center py-4">
                <div class="mb-3 text-danger" style="font-size: 42px;">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <p class="font-weight-bold text-dark mb-1" style="font-size: 15px;">
                    Are you sure you want to delete this allocation?
                </p>
                <p class="text-muted" style="font-size: 13px;">
                    Employee: <strong id="delete_employee_name">Unknown Employee</strong><br>
                    Year: <strong id="delete_year">-</strong> | Stage: <strong id="delete_stage">-</strong>
                </p>
                <div class="alert alert-warning text-left mb-0" style="font-size: 12px;">
                    <i class="fas fa-info-circle mr-1"></i> This action cannot be undone. Any leave balance calculated for this record will be deleted.
                </div>
            </div>

            <div class="modal-footer leave-modal-footer">
                <button type="button" class="leave-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger font-weight-bold" style="border-radius:14px; padding: 0 16px; height: 42px;">
                    <i class="fas fa-trash-alt mr-1"></i> Delete Allocation
                </button>
            </div>
        </form>
    </div>
</div>
