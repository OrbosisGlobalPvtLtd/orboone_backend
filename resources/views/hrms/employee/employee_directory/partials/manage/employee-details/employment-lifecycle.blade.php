                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-briefcase"></i>Employment & Lifecycle</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Employment Type</label>
                                    <x-form.select 
                                        name="employment_type" 
                                        id="employment_type" 
                                        class="em-control editable-select" 
                                        placeholder="Select Employment Type"
                                        :options="[
                                            'full_time' => 'Full Time',
                                            'part_time' => 'Part Time',
                                            'intern' => 'Intern',
                                            'freelancer' => 'Freelancer',
                                            'contract' => 'Contract'
                                        ]"
                                        :selected="old('employment_type', $employeeData->employment_type)" 
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    />
                                    @error('employment_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Employee Stage</label>
                                    <input type="text" id="employee_stage_display" class="em-control" value="{{ ucfirst(str_replace('_', ' ', $stage ?: 'Auto')) }}" readonly>
                                    <input type="hidden" id="employee_stage" name="derived_employee_stage" value="{{ old('derived_employee_stage', $employeeData->employee_stage ?? '') }}">
                                </div>

                                <div class="em-field">
                                    <label>Work Mode</label>
                                    <x-form.select 
                                        name="work_mode" 
                                        id="work_mode" 
                                        class="em-control editable-select" 
                                        placeholder="Select Work Mode"
                                        :options="[
                                            'wfo' => 'WFO',
                                            'wfh' => 'WFH',
                                            'hybrid' => 'Hybrid'
                                        ]"
                                        :selected="old('work_mode', $employeeData->work_mode)" 
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    />
                                    @error('work_mode') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Work Schedule</label>
                                    <x-form.select 
                                        name="work_schedule_type" 
                                        id="work_schedule_type" 
                                        class="em-control editable-select" 
                                        placeholder="Select Schedule"
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    >
                                        @php
                                        $empType = old('employment_type', $employeeData->employment_type ?? 'full_time');
                                        $currentVal = old('work_schedule_type', $employeeData->work_schedule_type ?? '');
                                        if (empty($currentVal)) {
                                            if (in_array($empType, ['full_time', 'intern', 'contract', 'consultant', 'trainee'])) {
                                                $currentVal = 'general_shift';
                                            } elseif ($empType === 'part_time') {
                                                $currentVal = 'part_time_shift';
                                            }
                                        }
                                        @endphp
                                        @foreach($attendanceTimes as $shiftItem)
                                        @php
                                        $isMatch = !empty($currentVal) && (
                                            $currentVal === $shiftItem->code
                                            || $currentVal === str_replace('_shift', '', $shiftItem->code)
                                            || (in_array($currentVal, ['general', 'full_day', 'general_shift']) && str_contains($shiftItem->code, 'general'))
                                            || (in_array($currentVal, ['part_time', 'part_day', 'part_time_shift']) && $shiftItem->code === 'part_time_shift')
                                            || (in_array($currentVal, ['half_day', 'hourly', 'half_day_shift']) && $shiftItem->code === 'half_day_shift')
                                            || (in_array($currentVal, ['wfh', 'wfh_shift']) && $shiftItem->code === 'wfh_shift')
                                            || (in_array($currentVal, ['flexible_part_time', 'flexible']) && str_contains($shiftItem->code, 'flexible'))
                                            || (in_array($currentVal, ['dynamic_hours']) && $shiftItem->code === 'dynamic_hours')
                                        );

                                        $displayName = $shiftItem->name;
                                        if (str_contains($shiftItem->code, 'general')) {
                                            $displayName = 'General Shift (Full Day)';
                                        }
                                        @endphp
                                        <option value="{{ $shiftItem->code }}" {{ $isMatch ? 'selected' : '' }}>
                                            {{ $displayName }}
                                        </option>
                                        @endforeach
                                    </x-form.select>
                                    @error('work_schedule_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Employment Status</label>
                                    <x-form.select 
                                        name="employment_status" 
                                        id="employment_status" 
                                        class="em-control editable-select" 
                                        placeholder="Select Status"
                                        :options="[
                                            'active' => 'Active',
                                            'resigned' => 'Resigned',
                                            'terminated' => 'Terminated',
                                            'inactive' => 'Inactive'
                                        ]"
                                        :selected="old('employment_status', $employeeData->employment_status)" 
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    />
                                    @error('employment_status') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field non-intern-field">
                                    <label>Joining Date</label>
                                    <x-form.date-picker 
                                        name="joining_date" 
                                        id="joining_date" 
                                        class="em-control editable" 
                                        :value="old('joining_date', $employeeData->joining_date)" 
                                        disabled 
                                    />
                                    @error('joining_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Relieving Date</label>
                                    <x-form.date-picker 
                                        name="relieving_date" 
                                        id="relieving_date" 
                                        class="em-control editable" 
                                        :value="old('relieving_date', $employeeData->relieving_date)" 
                                        disabled 
                                    />
                                    @error('relieving_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
