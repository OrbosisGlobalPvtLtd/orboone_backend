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
                <select name="employment_type" id="employment_type" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="full_time" {{ $empType === 'full_time' ? 'selected' : '' }}>Full Time</option>
                    <option value="part_time" {{ $empType === 'part_time' ? 'selected' : '' }}>Part Time</option>
                    <option value="intern" {{ $empType === 'intern' ? 'selected' : '' }}>Intern</option>
                    <option value="freelancer" {{ $empType === 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                    <option value="contract" {{ $empType === 'contract' ? 'selected' : '' }}>Contract</option>
                </select>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Employee Stage</label>
                <input type="text" id="employee_stage_display" class="form-control readonly-field" value="{{ ucfirst(old('derived_employee_stage', $employeeData->employee_stage ?? 'Auto')) }}" readonly>
                <input type="hidden" id="employee_stage" name="derived_employee_stage" value="{{ old('derived_employee_stage', $employeeData->employee_stage ?? '') }}">
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field joining-box">
                <label>Joining Date <span class="required">*</span></label>
                <input type="date" name="joining_date" id="joining_date" class="form-control" value="{{ old('joining_date', $employeeData->joining_date ?? '') }}">
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
                <select name="probation_duration_option" id="probation_duration_option" class="form-select">
                    <option value="3_months" {{ $probOpt === '3_months' ? 'selected' : '' }}>3 Months</option>
                    <option value="6_months" {{ $probOpt === '6_months' ? 'selected' : '' }}>6 Months</option>
                    <option value="custom" {{ $probOpt === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
                <input type="hidden" name="probation_months" id="probation_months" value="{{ old('probation_months', $employeeData->probation_months ?? 3) }}">
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box custom-probation-box {{ $probOpt === 'custom' ? '' : 'eo-hidden' }}">
                <label>Custom Duration <span class="required">*</span></label>
                <div class="input-group">
                    <input type="number" name="probation_duration_value" id="custom_duration_value" class="form-control" min="1" max="365" value="{{ $probVal }}" placeholder="e.g. 10">
                    <select name="probation_duration_type" id="custom_duration_unit" class="form-select" style="max-width: 110px;">
                        <option value="days" {{ $probType === 'days' ? 'selected' : '' }}>Days</option>
                        <option value="months" {{ $probType === 'months' ? 'selected' : '' }}>Months</option>
                    </select>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                <label>Probation Start Date</label>
                <input type="text" id="probation_start_date_display" class="form-control readonly-field" readonly>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                <label>Probation End Date</label>
                <input type="text" id="probation_end_date_display" class="form-control readonly-field" readonly>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field probation-box">
                <label>Expected Permanent Date</label>
                <input type="text" id="permanent_effective_date_display" class="form-control readonly-field" placeholder="Calculated after probation" readonly>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Work Mode <span class="required">*</span></label>
                @php $wMode = old('work_mode', $employeeData->work_mode ?? ''); @endphp
                <select name="work_mode" class="form-select" required>
                    <option value="">Select Work Mode</option>
                    <option value="wfo" {{ $wMode === 'wfo' ? 'selected' : '' }}>WFO</option>
                    <option value="wfh" {{ $wMode === 'wfh' ? 'selected' : '' }}>WFH</option>
                    <option value="hybrid" {{ $wMode === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                </select>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Work Schedule</label>
                <select name="work_schedule_type" id="work_schedule_type" class="form-select @error('work_schedule_type') is-invalid @enderror">
                    <option value="" {{ empty(old('work_schedule_type', $employeeData->work_schedule_type ?? '')) ? 'selected' : '' }}>Select Schedule</option>
                    @foreach($attendanceTimes as $shiftItem)
                    @php
                        $currentVal = old('work_schedule_type', $employeeData->work_schedule_type ?? '');
                        $isMatch = $currentVal && (
                            $currentVal == $shiftItem->code
                            || $currentVal == str_replace('_shift', '', $shiftItem->code)
                            || (in_array($currentVal, ['general', 'full_day']) && str_contains($shiftItem->code, 'general'))
                            || (in_array($currentVal, ['part_time', 'part_day']) && $shiftItem->code === 'part_time_shift')
                            || (in_array($currentVal, ['half_day', 'hourly']) && $shiftItem->code === 'half_day_shift')
                            || (in_array($currentVal, ['wfh']) && $shiftItem->code === 'wfh_shift')
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
                </select>
                @error('work_schedule_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Department <span class="required">*</span></label>
                @php $deptId = old('department_id', $employeeData->department_id ?? ''); @endphp
                <select name="department_id" id="department_id" class="form-select" required>
                    <option value="">Select Department</option>
                    @foreach ($departments as $department)
                    <option value="{{ $department->id }}" {{ (string)$deptId === (string)$department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Designation <span class="required">*</span></label>
                @php $desigId = old('designation_id', $employeeData->designation_id ?? ''); @endphp
                <select name="designation_id" id="designation_id" class="form-select" required>
                    <option value="">Select Designation</option>
                    @foreach ($designations as $designation)
                    <option value="{{ $designation->id }}"
                        data-department-id="{{ $designation->department_id }}"
                        {{ (string)$desigId === (string)$designation->id ? 'selected' : '' }}>
                        {{ $designation->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Reporting Manager</label>
                @php $managerId = old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id ?? ''); @endphp
                <select name="reporting_manager_employee_id" class="form-select">
                    <option value="">Select Manager</option>
                    @foreach ($reportingManagers as $manager)
                    <option value="{{ $manager->id }}" {{ (string)$managerId === (string)$manager->id ? 'selected' : '' }}>
                        {{ $manager->name }} - {{ $manager->employee_code }}
                    </option>
                    @endforeach
                </select>
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
