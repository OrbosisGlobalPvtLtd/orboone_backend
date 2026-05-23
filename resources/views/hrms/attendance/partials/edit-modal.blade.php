<div class="modal fade" id="editModal{{ $attendance->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title">Update Attendance</h5>
                    <p class="orb-modal-subtitle">Modify status, timings, and approval notes for this record.</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('attendances.update') }}">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" value="{{ $attendance->id }}">

                <div class="modal-body orb-modal-body">
                    <!-- Section 1: Attendance Status -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-info-circle"></i> Attendance Status
                        </div>
                        <div class="orb-form-grid">
                            <div>
                                <label class="orb-form-label">Status <span class="text-danger">*</span></label>
                                <select name="attendance_type_id" class="form-control" required>
                                    @foreach($attendanceTypes as $type)
                                        <option value="{{ $type->id }}" {{ $attendance->attendance_type_id == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="orb-form-label">Date</label>
                                <input type="date" name="attendance_date" class="form-control" value="{{ optional($attendance->attendance_date)->format('Y-m-d') }}">
                            </div>
                            <div style="grid-column: span 2;">
                                <label class="orb-form-label">Work Mode</label>
                                <select name="work_mode" class="form-control">
                                    <option value="">None</option>
                                    <option value="wfo" {{ $attendance->work_mode == 'wfo' ? 'selected' : '' }}>Work From Office</option>
                                    <option value="wfh" {{ $attendance->work_mode == 'wfh' ? 'selected' : '' }}>Work From Home</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Punch Timing -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-clock"></i> Punch Timing
                        </div>
                        <div class="orb-form-grid">
                            <div>
                                <label class="orb-form-label">Punch In</label>
                                <input type="time" name="punch_in_time" class="form-control" value="{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('H:i') : '' }}">
                            </div>
                            <div>
                                <label class="orb-form-label">Punch Out</label>
                                <input type="time" name="punch_out_time" class="form-control" value="{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('H:i') : '' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Notes -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-sticky-note"></i> Notes & Remarks
                        </div>
                        <div class="orb-form-grid" style="grid-template-columns: 1fr;">
                            <div>
                                <label class="orb-form-label">Note</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Enter punch out note...">{{ $attendance->punch_out_note }}</textarea>
                            </div>
                            <div>
                                <label class="orb-form-label">Admin Note</label>
                                <textarea name="hr_approval_note" class="form-control" rows="3" placeholder="Enter HR approval note...">{{ $attendance->hr_approval_note }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-primary"><i class="fas fa-save"></i> Update Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>
