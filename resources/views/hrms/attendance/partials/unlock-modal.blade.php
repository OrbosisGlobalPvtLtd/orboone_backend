<div class="modal fade" id="unlockModal{{ $attendance->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title">Unlock Attendance</h5>
                    <p class="orb-modal-subtitle">Choose how this blocked attendance should be unlocked.</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('attendances.unlock') }}">
                @csrf

                <input type="hidden" name="id" value="{{ $attendance->id }}">

                <div class="modal-body orb-modal-body">
                    <!-- Section 1: Unlock Type & Reason -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-key"></i> Unlock Settings
                        </div>
                        <div class="orb-form-grid">
                            <div>
                                <label class="orb-form-label">Unlock Type <span class="text-danger">*</span></label>
                                <select name="unlock_type" class="form-control unlock-type-select" data-target="#approvedPunchIn{{ $attendance->id }}" required>
                                    <option value="unlock_only">Unlock Only</option>
                                    <option value="late_exemption">Late Exemption</option>
                                    <option value="manual_punch_in">Manual Punch-In</option>
                                </select>
                            </div>
                            <div>
                                <label class="orb-form-label">Reason Category</label>
                                <input type="text" name="unlock_reason_category" class="form-control" placeholder="Traffic, client visit, manager approval, etc.">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Manual Punch Time (Conditional) -->
                    <div id="approvedPunchIn{{ $attendance->id }}" class="orb-form-section manual-punch-field" style="display:none;">
                        <div class="orb-form-section-title">
                            <i class="fas fa-clock"></i> Approved Punch-In Time
                        </div>
                        <div>
                            <label class="orb-form-label">Punch-In Time <span class="text-danger">*</span></label>
                            <input type="time" name="approved_punch_in_time" class="form-control">
                        </div>
                    </div>

                    <!-- Section 3: Remarks and Notes -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-comment-alt"></i> Remarks & Notes
                        </div>
                        <div class="orb-form-grid" style="grid-template-columns: 1fr;">
                            <div>
                                <label class="orb-form-label">Unlock Remarks</label>
                                <textarea name="unlock_remarks" class="form-control" rows="3" placeholder="Enter unlock remarks..."></textarea>
                            </div>
                            <div>
                                <label class="orb-form-label">Unlock Note</label>
                                <textarea name="hr_approval_note" class="form-control" rows="3" placeholder="Enter unlock note...">Unlocked by HR/Admin.</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-primary"><i class="fas fa-unlock"></i> Unlock Attendance</button>
                </div>
            </form>
        </div>
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
