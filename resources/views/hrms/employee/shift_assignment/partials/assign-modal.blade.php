<!-- Assign Shift Modal -->
<div class="modal fade" id="assignShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('employee.shift-assignment.store') }}" class="modal-content border-0 rounded-24 shadow-lg overflow-hidden">
            @csrf
            <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #4F46E5));">
                <div>
                    <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-user-plus mr-2"></i> Assign Employee Shift</h5>
                    <div class="text-white-50 small">Map a shift template and active date boundaries for an employee</div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background: #F9FAFB;">
                <div class="card border-0 rounded-16 shadow-2xs p-4 mb-3 bg-white">
                    <h6 class="shift-modal-card-title"><i class="fas fa-user mr-1 text-primary"></i> Employee & Shift Selection</h6>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" id="assignEmployeeSelect" class="form-control rounded-12 border-light bg-light" required style="height: 42px; font-size: 13px;">
                                <option value="">-- Select Employee --</option>
                                @foreach($allEmployeesList as $empOption)
                                    <option value="{{ $empOption->id }}">{{ $empOption->employee_code }} - {{ optional($empOption->user)->name ?? 'Employee #' . $empOption->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Select Shift Template <span class="text-danger">*</span></label>
                            <select name="attendance_time_id" id="assignShiftSelect" class="form-control rounded-12 border-light bg-light" required style="height: 42px; font-size: 13px;" onchange="handleShiftTemplateSelect('assign', this)">
                                <option value="">-- Select Shift --</option>
                                @foreach($attendanceTimes as $timeOption)
                                    @php
                                        $isFlex = in_array(strtolower($timeOption->shift_type ?? ''), ['flexible_part_time', 'dynamic_hours']) || stripos($timeOption->name, 'flexible') !== false;
                                        $isDynamicOpt = strtolower($timeOption->shift_type ?? '') === 'dynamic_hours';
                                    @endphp
                                    <option value="{{ $timeOption->id }}"
                                        data-flexible="{{ $isFlex ? '1' : '0' }}"
                                        data-shift-type="{{ strtolower($timeOption->shift_type ?? 'fixed') }}"
                                        data-punch-allowed="{{ $timeOption->punch_allowed_from ? \Carbon\Carbon::parse($timeOption->punch_allowed_from)->format('H:i') : '' }}"
                                        data-shift-start="{{ $timeOption->shift_start_time ? \Carbon\Carbon::parse($timeOption->shift_start_time)->format('H:i') : '' }}"
                                        data-late-after="{{ $timeOption->late_after_time ? \Carbon\Carbon::parse($timeOption->late_after_time)->format('H:i') : '' }}"
                                        data-block-after="{{ $timeOption->block_after_time ? \Carbon\Carbon::parse($timeOption->block_after_time)->format('H:i') : '' }}"
                                        data-half-day-after="{{ $timeOption->half_day_after_time ? \Carbon\Carbon::parse($timeOption->half_day_after_time)->format('H:i') : '' }}"
                                        data-shift-end="{{ $timeOption->shift_end_time ? \Carbon\Carbon::parse($timeOption->shift_end_time)->format('H:i') : '' }}"
                                        data-req-mins="{{ $timeOption->required_work_minutes ?? 480 }}"
                                        data-lunch-mins="{{ $timeOption->lunch_break_minutes ?? 60 }}">
                                        {{ $timeOption->name }} ({{ $isDynamicOpt ? 'Dynamic Hours' : ($timeOption->shift_start_time ? \Carbon\Carbon::parse($timeOption->shift_start_time)->format('h:i A') . ' - ' . ($timeOption->shift_end_time ? \Carbon\Carbon::parse($timeOption->shift_end_time)->format('h:i A') : 'Flexible') : 'Flexible Duration') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Shift Timing Customisation Section -->
                <div class="card border-0 rounded-16 shadow-2xs p-4 mb-3 bg-white" id="assignFlexibleSection">
                    <h6 class="shift-modal-card-title"><i class="fas fa-clock mr-1 text-primary"></i> Shift Timing Customisation</h6>
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Punch Allowed <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="punch_allowed_from" id="assign_punch_allowed_from" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Shift Start <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="shift_start_time" id="assign_shift_start_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Late After <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="late_after_time" id="assign_late_after_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Blocked Punch <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="block_after_time" id="assign_block_after_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Half Day After <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="half_day_after_time" id="assign_half_day_after_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Shift End <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="shift_end_time" id="assign_shift_end_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Required Minutes <span class="text-danger">*</span></label>
                            <input type="number" name="required_work_minutes" id="assign_required_work_minutes" class="form-control rounded-12 border-light bg-light" value="480" min="0" placeholder="e.g. 480" style="height: 42px; font-size: 13px;">
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Lunch Minutes <span class="text-danger">*</span></label>
                            <input type="number" name="lunch_minutes" id="assign_lunch_minutes" class="form-control rounded-12 border-light bg-light" value="60" min="0" placeholder="e.g. 60" style="height: 42px; font-size: 13px;">
                        </div>
                    </div>
                </div>

                <div class="card border-0 rounded-16 shadow-2xs p-4 bg-white">
                    <h6 class="shift-modal-card-title"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Date Boundaries & Status</h6>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Effective From Date <span class="text-danger">*</span></label>
                            <input type="date" name="effective_from" class="form-control rounded-12 border-light bg-light" value="{{ date('Y-m-d') }}" required style="height: 42px; font-size: 13px;">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Effective Till Date (Optional)</label>
                            <input type="date" name="effective_to" class="form-control rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                        </div>
                    </div>
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" id="assignShiftActive" name="is_active" value="1" checked>
                        <label class="custom-control-label font-weight-bold text-dark" for="assignShiftActive" style="font-size: 13px;">Set as Active Shift Assignment</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-0 px-4 py-3">
                <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold" style="background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #4F46E5)); font-size: 13px;">
                    <i class="fas fa-check-circle mr-1"></i> Save Shift Assignment
                </button>
            </div>
        </form>
    </div>
</div>
