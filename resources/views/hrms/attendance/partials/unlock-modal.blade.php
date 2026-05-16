<div class="modal fade" id="unlockModal{{ $attendance->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('attendances.unlock') }}" class="modal-content">
            @csrf

            <input type="hidden" name="id" value="{{ $attendance->id }}">

            <div class="modal-header">
                <h5 class="modal-title">Unlock Attendance</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <p class="text-muted">Choose how this blocked or pending attendance should be unlocked.</p>

                <label>Unlock Type</label>
                <select name="unlock_type" class="form-control mb-3 unlock-type-select" data-target="#approvedPunchIn{{ $attendance->id }}" required>
                    <option value="unlock_only">Unlock Only</option>
                    <option value="late_exemption">Late Exemption</option>
                    <option value="manual_punch_in">Manual Punch-In</option>
                </select>

                <label>Reason Category</label>
                <input type="text" name="unlock_reason_category" class="form-control mb-3" placeholder="Traffic, client visit, HR approval, etc.">

                <div id="approvedPunchIn{{ $attendance->id }}" class="manual-punch-field mb-3" style="display:none;">
                    <label>Approved Punch-In Time</label>
                    <input type="time" name="approved_punch_in_time" class="form-control">
                </div>

                <label>Unlock Remarks</label>
                <textarea name="unlock_remarks" class="form-control mb-3" rows="3" placeholder="Enter unlock remarks..."></textarea>

                <label>HR Approval Note</label>
                <textarea name="hr_approval_note" class="form-control" rows="3" placeholder="Enter approval note...">Approved by HR/Admin.</textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Unlock Attendance</button>
            </div>
        </form>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('change', function (event) {
                if (!event.target.classList.contains('unlock-type-select')) return;
                const target = document.querySelector(event.target.dataset.target);
                if (target) target.style.display = event.target.value === 'manual_punch_in' ? 'block' : 'none';
            });
        </script>
    @endpush
@endonce
