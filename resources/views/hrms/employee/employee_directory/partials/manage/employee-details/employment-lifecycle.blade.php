                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-briefcase"></i>Employment & Lifecycle</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Employment Type</label>
                                    <select name="employment_type" id="employment_type" class="em-control editable-select" disabled>
                                        <option value="">Select Employment Type</option>
                                        <option value="full_time" {{ old('employment_type', $employeeData->employment_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('employment_type', $employeeData->employment_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="intern" {{ old('employment_type', $employeeData->employment_type) == 'intern' ? 'selected' : '' }}>Intern</option>
                                        <option value="freelancer" {{ old('employment_type', $employeeData->employment_type) == 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                                        <option value="contract" {{ old('employment_type', $employeeData->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                    </select>
                                    @error('employment_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Employee Stage</label>
                                    <input type="text" id="employee_stage_display" class="em-control" value="{{ ucfirst(str_replace('_', ' ', $stage ?: 'Auto')) }}" readonly>
                                    <input type="hidden" id="employee_stage" name="derived_employee_stage" value="{{ old('derived_employee_stage', $employeeData->employee_stage ?? '') }}">
                                </div>

                                <div class="em-field">
                                    <label>Work Mode</label>
                                    <select name="work_mode" class="em-control editable-select" disabled>
                                        <option value="">Select Work Mode</option>
                                        <option value="wfo" {{ old('work_mode', $employeeData->work_mode) == 'wfo' ? 'selected' : '' }}>WFO</option>
                                        <option value="wfh" {{ old('work_mode', $employeeData->work_mode) == 'wfh' ? 'selected' : '' }}>WFH</option>
                                        <option value="hybrid" {{ old('work_mode', $employeeData->work_mode) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    </select>
                                    @error('work_mode') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Work Schedule</label>
                                    <select name="work_schedule_type" id="work_schedule_type" class="em-control editable-select" disabled>
                                        <option value="">Select Schedule</option>
                                        @foreach($attendanceTimes as $shiftItem)
                                        @php
                                        $currentVal = old('work_schedule_type', $employeeData->work_schedule_type ?? '');
                                        $isMatch = $currentVal == $shiftItem->code
                                        || $currentVal == str_replace('_shift', '', $shiftItem->code)
                                        || (in_array($currentVal, ['general', 'full_day']) && str_contains($shiftItem->code, 'general'))
                                        || (in_array($currentVal, ['part_time', 'part_day']) && $shiftItem->code === 'part_time_shift')
                                        || (in_array($currentVal, ['half_day', 'hourly']) && $shiftItem->code === 'half_day_shift')
                                        || (in_array($currentVal, ['wfh']) && $shiftItem->code === 'wfh_shift');

                                        $displayName = $shiftItem->name;
                                        if (str_contains($shiftItem->code, 'general')) {
                                        $displayName = 'General Shift (Full Day)';
                                        }
                                        @endphp
                                        <option value="{{ $shiftItem->code }}" {{ $isMatch ? 'selected' : '' }}>
                                            {{ $displayName }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('work_schedule_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Employment Status</label>
                                    <select name="employment_status" class="em-control editable-select" disabled>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('employment_status', $employeeData->employment_status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="resigned" {{ old('employment_status', $employeeData->employment_status) == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                        <option value="terminated" {{ old('employment_status', $employeeData->employment_status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                        <option value="inactive" {{ old('employment_status', $employeeData->employment_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('employment_status') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field non-intern-field">
                                    <label>Joining Date</label>
                                    <input type="date" name="joining_date" class="em-control editable" value="{{ old('joining_date', $employeeData->joining_date) }}" readonly>
                                    @error('joining_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Relieving Date</label>
                                    <input type="date" name="relieving_date" class="em-control editable" value="{{ old('relieving_date', $employeeData->relieving_date) }}" readonly>
                                    @error('relieving_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
