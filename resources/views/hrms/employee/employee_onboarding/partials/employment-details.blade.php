<div class="eo-card">
    <div class="eo-card-head">
        <div class="eo-section-title">
            <div class="eo-section-icon"><i class="fas fa-briefcase"></i></div>
            <div>
                <h5>Employment Details</h5>
                <p>Lifecycle stage, department, designation, manager and work setup.</p>
            </div>
        </div>
    </div>

    <div class="eo-card-body">
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Employment Type <span class="required">*</span></label>
                @php
                    $empType = old('employment_type', $employeeData->employment_type ?? '');
                @endphp
                <x-form.select 
                    name="employment_type" 
                    id="employment_type" 
                    placeholder="Select Type"
                    :options="[
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'intern' => 'Intern',
                        'freelancer' => 'Freelancer',
                        'contract' => 'Contract'
                    ]"
                    :selected="$empType" 
                    :searchable="true" 
                    :required="true"
                    wrapper-class="m-0"
                />
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Employee Stage</label>
                <input type="text" id="employee_stage_display" class="form-control readonly-field" value="{{ ucfirst(old('derived_employee_stage', $employeeData->employee_stage ?? 'Auto')) }}" readonly>
                <input type="hidden" id="employee_stage" name="derived_employee_stage" value="{{ old('derived_employee_stage', $employeeData->employee_stage ?? '') }}">
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field joining-box">
                <label>Joining Date <span class="required">*</span></label>
                <x-form.date-picker 
                    name="joining_date" 
                    id="joining_date" 
                    :value="old('joining_date', $employeeData->joining_date ?? '')" 
                />
            </div>

            @php
                $empProbType = $employeeData->probation_duration_type ?? 'months';
                $empProbVal = $employeeData->probation_duration_value ?? $employeeData->probation_months ?? 3;

                if (old('probation_duration_option') !== null) {
                    $probOpt = old('probation_duration_option');
                } elseif (isset($employeeData->probation_duration_option) && !empty($employeeData->probation_duration_option)) {
                    $probOpt = $employeeData->probation_duration_option;
                } elseif (isset($employeeData)) {
                    if ($empProbType === 'days') {
                        $probOpt = 'custom';
                    } elseif ((int)$empProbVal === 3) {
                        $probOpt = '3_months';
                    } elseif ((int)$empProbVal === 6) {
                        $probOpt = '6_months';
                    } else {
                        $probOpt = 'custom';
                    }
                } else {
                    $probOpt = '3_months';
                }

                $probVal = old('probation_duration_value', $employeeData->probation_duration_value ?? ($empProbType === 'months' ? ($employeeData->probation_months ?? 3) : 10));
                $probType = old('probation_duration_type', $employeeData->probation_duration_type ?? 'months');
            @endphp

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                <label>Probation Duration</label>
                <x-form.select 
                    name="probation_duration_option" 
                    id="probation_duration_option" 
                    placeholder="Select Duration"
                    :options="[
                        '3_months' => '3 Months',
                        '6_months' => '6 Months',
                        'custom' => 'Custom'
                    ]"
                    :selected="$probOpt" 
                    :searchable="true" 
                    wrapper-class="m-0"
                />
                <input type="hidden" name="probation_months" id="probation_months" value="{{ old('probation_months', $employeeData->probation_months ?? 3) }}">
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box custom-probation-box {{ $probOpt === 'custom' ? '' : 'eo-hidden' }}">
                <label>Custom Duration <span class="required">*</span></label>
                <div class="input-group" style="display: flex; gap: 8px;">
                    <input type="number" name="probation_duration_value" id="custom_duration_value" class="form-control" min="1" max="365" value="{{ $probVal }}" placeholder="e.g. 10" style="flex: 1;">
                    <x-form.select 
                        name="probation_duration_type" 
                        id="custom_duration_unit" 
                        :options="[
                            'days' => 'Days',
                            'months' => 'Months'
                        ]"
                        :selected="$probType" 
                        :searchable="true" 
                        wrapper-class="m-0 flex-shrink-0"
                        style="width: 130px;"
                    />
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                <label>Probation Start Date</label>
                <input type="text" id="probation_start_date_display" class="form-control readonly-field" placeholder="Auto calculated from joining date" readonly>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                <label>Probation End Date</label>
                <input type="text" id="probation_end_date_display" class="form-control readonly-field" placeholder="Auto calculated from duration" readonly>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                <label>Expected Permanent Date</label>
                <input type="text" id="permanent_effective_date_display" class="form-control readonly-field" placeholder="Auto calculated after probation" readonly>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Work Mode <span class="required">*</span></label>
                @php $wMode = old('work_mode', $employeeData->work_mode ?? ''); @endphp
                <x-form.select 
                    name="work_mode" 
                    id="work_mode" 
                    placeholder="Select Work Mode"
                    :options="[
                        'wfo' => 'WFO',
                        'wfh' => 'WFH',
                        'hybrid' => 'Hybrid'
                    ]"
                    :selected="$wMode" 
                    :searchable="true" 
                    :required="true"
                    wrapper-class="m-0"
                />
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Work Schedule</label>
                <x-form.select 
                    name="work_schedule_type" 
                    id="work_schedule_type" 
                    placeholder="Select Schedule"
                    :searchable="true" 
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
                @error('work_schedule_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Department <span class="required">*</span></label>
                @php
                    $deptId = old('department_id', $employeeData->department_id ?? '');
                    $deptOptions = [];
                    foreach ($departments as $department) {
                        $deptOptions[$department->id] = $department->name;
                    }
                @endphp
                <x-form.select 
                    name="department_id" 
                    id="department_id" 
                    placeholder="Select Department"
                    :options="$deptOptions"
                    :selected="$deptId" 
                    :searchable="true" 
                    :required="true"
                    wrapper-class="m-0"
                />
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Designation <span class="required">*</span></label>
                @php 
                    $currDeptId = old('department_id', $employeeData->department_id ?? '');
                    $desigId = old('designation_id', $employeeData->designation_id ?? ''); 
                @endphp
                <x-form.select 
                    name="designation_id" 
                    id="designation_id" 
                    placeholder="Select Designation"
                    :searchable="true" 
                    :required="true"
                    data-initial-val="{{ $desigId }}"
                    wrapper-class="m-0"
                >
                    @foreach ($designations as $designation)
                        @if(empty($currDeptId) || (string)$currDeptId === (string)$designation->department_id)
                        <option value="{{ $designation->id }}"
                            data-department-id="{{ $designation->department_id }}"
                            {{ (string)$desigId === (string)$designation->id ? 'selected' : '' }}>
                            {{ $designation->name }}
                        </option>
                        @endif
                    @endforeach
                </x-form.select>
                @error('designation_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Reporting Manager</label>
                @php
                    $managerId = old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id ?? '');
                    $managerOptions = [];
                    foreach ($reportingManagers as $manager) {
                        $managerOptions[$manager->id] = $manager->name . ' - ' . $manager->employee_code;
                    }
                @endphp
                <x-form.select 
                    name="reporting_manager_employee_id" 
                    id="reporting_manager_employee_id" 
                    placeholder="Select Manager"
                    :options="$managerOptions"
                    :selected="$managerId" 
                    :searchable="true" 
                    wrapper-class="m-0"
                />
            </div>
        </div>

        <div id="flexible_shift_timings_box" class="eo-smart-panel" style="display: none; margin-bottom: 14px;">
            <div class="eo-panel-title">
                <i class="fas fa-clock"></i> Flexible Shift Timing Customisation
            </div>
            <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-6 eo-field mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: var(--orb-text); margin-bottom: 6px; display: block;">Punch Allowed From <span class="required">*</span></label>
                    <div class="time-picker-container">
                        <input type="time" name="punch_allowed_from" id="punch_allowed_from" class="form-control native-time-input" value="{{ old('punch_allowed_from', isset($activeShiftTiming->punch_allowed_from) ? \Carbon\Carbon::parse($activeShiftTiming->punch_allowed_from)->format('H:i') : '') }}">
                        <span class="time-display-val">--:--</span>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 eo-field mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: var(--orb-text); margin-bottom: 6px; display: block;">Shift Start <span class="required">*</span></label>
                    <div class="time-picker-container">
                        <input type="time" name="shift_start_time" id="shift_start_time" class="form-control native-time-input" value="{{ old('shift_start_time', isset($activeShiftTiming->shift_start_time) ? \Carbon\Carbon::parse($activeShiftTiming->shift_start_time)->format('H:i') : '') }}">
                        <span class="time-display-val">--:--</span>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 eo-field mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: var(--orb-text); margin-bottom: 6px; display: block;">Late After <span class="required">*</span></label>
                    <div class="time-picker-container">
                        <input type="time" name="late_after_time" id="late_after_time" class="form-control native-time-input" value="{{ old('late_after_time', isset($activeShiftTiming->late_after_time) ? \Carbon\Carbon::parse($activeShiftTiming->late_after_time)->format('H:i') : '') }}">
                        <span class="time-display-val">--:--</span>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 eo-field mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: var(--orb-text); margin-bottom: 6px; display: block;">Blocked Punch <span class="required">*</span></label>
                    <div class="time-picker-container">
                        <input type="time" name="block_after_time" id="block_after_time" class="form-control native-time-input" value="{{ old('block_after_time', isset($activeShiftTiming->block_after_time) ? \Carbon\Carbon::parse($activeShiftTiming->block_after_time)->format('H:i') : '') }}">
                        <span class="time-display-val">--:--</span>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 eo-field mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: var(--orb-text); margin-bottom: 6px; display: block;">Half Day After <span class="required">*</span></label>
                    <div class="time-picker-container">
                        <input type="time" name="half_day_after_time" id="half_day_after_time" class="form-control native-time-input" value="{{ old('half_day_after_time', isset($activeShiftTiming->half_day_after_time) ? \Carbon\Carbon::parse($activeShiftTiming->half_day_after_time)->format('H:i') : '') }}">
                        <span class="time-display-val">--:--</span>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 eo-field mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: var(--orb-text); margin-bottom: 6px; display: block;">Shift End <span class="required">*</span></label>
                    <div class="time-picker-container">
                        <input type="time" name="shift_end_time" id="shift_end_time" class="form-control native-time-input" value="{{ old('shift_end_time', isset($activeShiftTiming->shift_end_time) ? \Carbon\Carbon::parse($activeShiftTiming->shift_end_time)->format('H:i') : '') }}">
                        <span class="time-display-val">--:--</span>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 eo-field mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: var(--orb-text); margin-bottom: 6px; display: block;">Required Minutes <span class="required">*</span></label>
                    <input type="number" name="required_work_minutes" id="required_work_minutes" class="form-control" placeholder="e.g. 480" value="{{ old('required_work_minutes', $activeShiftTiming->required_work_minutes ?? '') }}">
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 eo-field mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: var(--orb-text); margin-bottom: 6px; display: block;">Lunch Minutes <span class="required">*</span></label>
                    <input type="number" name="lunch_minutes" id="lunch_minutes" class="form-control" placeholder="e.g. 60" value="{{ old('lunch_minutes', $activeShiftTiming->lunch_minutes ?? '') }}">
                </div>
            </div>
        </div>
    </div>
</div>
