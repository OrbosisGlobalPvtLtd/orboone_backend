<div class="modal fade" id="unlockModal{{ $attendance->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('attendances.unlock') }}" class="modal-content">
            @csrf

            <input type="hidden" name="id" value="{{ $attendance->id }}">

            <div class="modal-header">
                <h5 class="modal-title">Approve Pending Attendance</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <p class="text-muted">
                    This attendance is currently pending HR approval.
                </p>

                <label>Approval Note</label>
                <textarea name="hr_approval_note" class="form-control" rows="4" placeholder="Enter approval reason...">Approved by HR/Admin.</textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Approve Attendance</button>
            </div>
        </form>
    </div>
</div>