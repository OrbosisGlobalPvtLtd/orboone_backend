                            <!-- Flexible Shift Timings Box -->
                            <div id="flexible_shift_timings_box" class="em-section" style="display: none; border-top: 1px dashed var(--orb-border); margin-top: 20px; padding-top: 15px;">
                                <h6 class="em-section-title" style="color: var(--orb-primary); font-weight: 750; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-clock"></i> Flexible Shift Timing Customisation
                                </h6>
                                <div class="em-form-grid">
                                    <div class="em-field">
                                        <label>Punch Allowed From <span class="required">*</span></label>
                                        <div class="time-picker-container">
                                            <input type="time" name="punch_allowed_from" id="punch_allowed_from" class="em-control editable native-time-input" value="{{ old('punch_allowed_from', isset($activeShiftTiming) && $activeShiftTiming->punch_allowed_from ? \Carbon\Carbon::parse($activeShiftTiming->punch_allowed_from)->format('H:i') : '') }}" readonly>
                                            <span class="time-display-val">--:--</span>
                                        </div>
                                        @error('punch_allowed_from') <div class="em-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="em-field">
                                        <label>Shift Start <span class="required">*</span></label>
                                        <div class="time-picker-container">
                                            <input type="time" name="shift_start_time" id="shift_start_time" class="em-control editable native-time-input" value="{{ old('shift_start_time', isset($activeShiftTiming) && $activeShiftTiming->shift_start_time ? \Carbon\Carbon::parse($activeShiftTiming->shift_start_time)->format('H:i') : '') }}" readonly>
                                            <span class="time-display-val">--:--</span>
                                        </div>
                                        @error('shift_start_time') <div class="em-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="em-field">
                                        <label>Late After <span class="required">*</span></label>
                                        <div class="time-picker-container">
                                            <input type="time" name="late_after_time" id="late_after_time" class="em-control editable native-time-input" value="{{ old('late_after_time', isset($activeShiftTiming) && $activeShiftTiming->late_after_time ? \Carbon\Carbon::parse($activeShiftTiming->late_after_time)->format('H:i') : '') }}" readonly>
                                            <span class="time-display-val">--:--</span>
                                        </div>
                                        @error('late_after_time') <div class="em-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="em-field">
                                        <label>Half Day After <span class="required">*</span></label>
                                        <div class="time-picker-container">
                                            <input type="time" name="half_day_after_time" id="half_day_after_time" class="em-control editable native-time-input" value="{{ old('half_day_after_time', isset($activeShiftTiming) && $activeShiftTiming->half_day_after_time ? \Carbon\Carbon::parse($activeShiftTiming->half_day_after_time)->format('H:i') : '') }}" readonly>
                                            <span class="time-display-val">--:--</span>
                                        </div>
                                        @error('half_day_after_time') <div class="em-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="em-field">
                                        <label>Blocked Punch <span class="required">*</span></label>
                                        <div class="time-picker-container">
                                            <input type="time" name="block_after_time" id="block_after_time" class="em-control editable native-time-input" value="{{ old('block_after_time', isset($activeShiftTiming) && $activeShiftTiming->block_after_time ? \Carbon\Carbon::parse($activeShiftTiming->block_after_time)->format('H:i') : '') }}" readonly>
                                            <span class="time-display-val">--:--</span>
                                        </div>
                                        @error('block_after_time') <div class="em-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="em-field">
                                        <label>Shift End <span class="required">*</span></label>
                                        <div class="time-picker-container">
                                            <input type="time" name="shift_end_time" id="shift_end_time" class="em-control editable native-time-input" value="{{ old('shift_end_time', isset($activeShiftTiming) && $activeShiftTiming->shift_end_time ? \Carbon\Carbon::parse($activeShiftTiming->shift_end_time)->format('H:i') : '') }}" readonly>
                                            <span class="time-display-val">--:--</span>
                                        </div>
                                        @error('shift_end_time') <div class="em-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="em-field">
                                        <label>Required Minutes <span class="required">*</span></label>
                                        <input type="number" name="required_work_minutes" id="required_work_minutes" class="em-control editable" placeholder="e.g. 480" value="{{ old('required_work_minutes', isset($activeShiftTiming) ? $activeShiftTiming->required_work_minutes : '') }}" readonly>
                                        @error('required_work_minutes') <div class="em-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="em-field">
                                        <label>Lunch Minutes <span class="required">*</span></label>
                                        <input type="number" name="lunch_minutes" id="lunch_minutes" class="em-control editable" placeholder="e.g. 60" value="{{ old('lunch_minutes', isset($activeShiftTiming) ? $activeShiftTiming->lunch_minutes : '') }}" readonly>
                                        @error('lunch_minutes') <div class="em-error">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
