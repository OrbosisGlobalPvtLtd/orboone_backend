<!-- EDIT REGULARIZATION REQUEST MODAL -->
@if(!empty($canEdit) && $row->status === 'pending')
<div class="modal fade glass-modal" id="editModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Edit Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route($updateRoute, $row->id) }}" class="js-regularization-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="employee_id" value="{{ $row->employee_id }}">
                    
                    <div class="reg-form-grid">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small text-uppercase">Attendance Date</label>
                            <input type="text" class="form-control" style="background: #F1F5F9; border-radius: 10px; height: 42px;" value="{{ \Carbon\Carbon::parse($row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at)))->format('d M Y') }}" readonly>
                        </div>

                        <div class="form-group mb-0 js-req-type-group">
                            <label class="font-weight-bold text-dark small text-uppercase">Request Type <span class="text-danger">*</span></label>
                            <select name="request_type" class="form-control custom-select select2-modal-searchable" style="border-radius: 10px;" required>
                                <option value="">Select Type</option>
                                <option value="missed_punch_in" {{ $row->request_type === 'missed_punch_in' ? 'selected' : '' }}>Missed Punch In</option>
                                <option value="missed_punch_out" {{ $row->request_type === 'missed_punch_out' ? 'selected' : '' }}>Missed Punch Out</option>
                                <option value="wrong_punch_time" {{ $row->request_type === 'wrong_punch_time' ? 'selected' : '' }}>Punch Time Correction</option>
                                <option value="late_mark_exemption" {{ $row->request_type === 'late_mark_exemption' ? 'selected' : '' }}>Late Mark Exemption</option>
                                <option value="early_logout_correction" {{ $row->request_type === 'early_logout_correction' ? 'selected' : '' }}>Early Logout Exemption</option>
                                <option value="other" {{ $row->request_type === 'other' ? 'selected' : '' }}>Attendance Status Correction</option>
                            </select>
                        </div>

                        <!-- Conditionally displayed time pickers -->
                        <div class="form-group mb-0 js-in-group" style="display: none;">
                            <label class="font-weight-bold text-dark small text-uppercase">Requested Punch In Time <span class="text-danger">*</span></label>
                            <input type="time" name="requested_punch_in" class="form-control" style="border-radius: 10px; height: 42px;" value="{{ $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('H:i') : '' }}">
                        </div>

                        <div class="form-group mb-0 js-out-group" style="display: none;">
                            <label class="font-weight-bold text-dark small text-uppercase">Requested Punch Out Time <span class="text-danger">*</span></label>
                            <input type="time" name="requested_punch_out" class="form-control" style="border-radius: 10px; height: 42px;" value="{{ $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('H:i') : '' }}">
                        </div>

                        <!-- Requested status correction dropdown (maps to other) -->
                        @php
                            $savedStatus = 'Present';
                            if ($row->request_type === 'other' && str_contains($row->reason, '[Attendance Status Correction:')) {
                                preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                                $savedStatus = $matches[1] ?? 'Present';
                            }
                        @endphp
                        <div class="form-group mb-0 js-status-group" style="display: none;">
                            <label class="font-weight-bold text-dark small text-uppercase">Requested Status <span class="text-danger">*</span></label>
                            <select name="requested_status" class="form-control custom-select select2-modal-searchable" style="border-radius: 10px;">
                                <option value="Present" {{ $savedStatus === 'Present' ? 'selected' : '' }}>Present</option>
                                <option value="Half Day" {{ $savedStatus === 'Half Day' ? 'selected' : '' }}>Half Day</option>
                                <option value="Absent" {{ $savedStatus === 'Absent' ? 'selected' : '' }}>Absent</option>
                            </select>
                        </div>

                        <div class="form-group mb-0 grid-col-full">
                            <label class="font-weight-bold text-dark small text-uppercase">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" minlength="5" required placeholder="Describe the reason for correction..." style="border-radius: 10px; resize: vertical;">{{ str_replace(['[Attendance Status Correction: Present]', '[Attendance Status Correction: Half Day]', '[Attendance Status Correction: Absent]', '[Attendance Status Correction: present]', '[Attendance Status Correction: half_day]', '[Attendance Status Correction: absent]'], '', $row->reason) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border font-weight-bold" style="border-radius: 10px; font-size: 13px;" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn text-white font-weight-bold px-4 shadow-sm" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: 0; border-radius: 10px; font-size: 13px;">
                        <i class="fas fa-save mr-1"></i> Update Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
