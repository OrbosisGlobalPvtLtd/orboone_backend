                        @php
                            $manageStage = strtolower($employeeData->employee_stage ?? 'probation');
                            $isManageInternship = $manageStage === 'internship' || ($employeeData->employment_type ?? '') === 'intern';
                            $isManageContractOrFreelance = in_array($manageStage, ['contract', 'freelance']);
                            $isManagePermanent = $manageStage === 'permanent' || ($employeeData->probation_status ?? '') === 'completed';

                            $empProbType = $employeeData->probation_duration_type ?? 'months';
                            $empProbVal = $employeeData->probation_duration_value ?? $employeeData->probation_months ?? 3;

                            if (old('probation_duration_option') !== null) {
                                $manageProbOpt = old('probation_duration_option');
                            } elseif (isset($employeeData->probation_duration_option) && !empty($employeeData->probation_duration_option)) {
                                $manageProbOpt = $employeeData->probation_duration_option;
                            } elseif ($empProbType === 'days') {
                                $manageProbOpt = 'custom';
                            } elseif ((int)$empProbVal === 3) {
                                $manageProbOpt = '3_months';
                            } elseif ((int)$empProbVal === 6) {
                                $manageProbOpt = '6_months';
                            } else {
                                $manageProbOpt = 'custom';
                            }

                            $manageCustomVal = old('custom_duration_value', old('probation_duration_value', $employeeData->probation_duration_value ?? ($empProbType === 'months' ? ($employeeData->probation_months ?? 3) : 10)));
                            $manageCustomUnit = old('custom_duration_unit', old('probation_duration_type', $employeeData->probation_duration_type ?? 'months'));
                        @endphp
                        <div class="em-section probation-section" style="{{ ($isManageInternship || $isManageContractOrFreelance) ? 'display: none;' : '' }}">
                            <h6 class="em-section-title"><i class="fas fa-hourglass-half"></i>Probation / Permanent Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Probation Duration @if(formatProbationDuration($employeeData))<span class="em-prob-formatted-text text-muted font-weight-normal ml-1">({{ formatProbationDuration($employeeData) }})</span>@endif</label>
                                    <select name="probation_duration_option" id="manage_probation_duration_option" class="em-control editable" disabled>
                                        <option value="3_months" {{ $manageProbOpt === '3_months' ? 'selected' : '' }}>3 Months</option>
                                        <option value="6_months" {{ $manageProbOpt === '6_months' ? 'selected' : '' }}>6 Months</option>
                                        <option value="custom" {{ $manageProbOpt === 'custom' ? 'selected' : '' }}>Custom</option>
                                    </select>
                                    <input type="hidden" name="probation_months" id="manage_probation_months" value="{{ old('probation_months', $employeeData->probation_months ?? 3) }}">
                                </div>

                                <div class="em-field manage-custom-probation-box {{ $manageProbOpt === 'custom' ? '' : 'em-hidden' }}" id="manage_custom_probation_box" style="{{ $manageProbOpt === 'custom' ? '' : 'display: none;' }}">
                                    <label>Custom Duration <span class="required">*</span></label>
                                    <div class="input-group" style="display: flex; gap: 8px;">
                                        <input type="number" name="custom_duration_value" id="manage_custom_duration_value" class="em-control editable" min="1" max="365" value="{{ $manageCustomVal }}" placeholder="e.g. 10" disabled style="flex: 1;">
                                        <select name="custom_duration_unit" id="manage_custom_duration_unit" class="em-control editable" disabled style="max-width: 110px;">
                                            <option value="days" {{ $manageCustomUnit === 'days' ? 'selected' : '' }}>Days</option>
                                            <option value="months" {{ $manageCustomUnit === 'months' ? 'selected' : '' }}>Months</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="em-field">
                                    <label>Probation Status</label>
                                    <input type="text" name="probation_status" class="em-control em-control-readonly" value="{{ old('probation_status', $employeeData->probation_status) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Probation Start Date</label>
                                    <input type="date" name="probation_start_date" id="manage_probation_start_date" class="em-control editable" value="{{ old('probation_start_date', $employeeData->probation_start_date) }}" disabled>
                                </div>

                                <div class="em-field">
                                    <label>Probation End Date</label>
                                    <input type="date" name="probation_end_date" id="manage_probation_end_date" class="em-control em-control-readonly" value="{{ old('probation_end_date', $employeeData->probation_end_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Is Permanent</label>
                                    <input type="text" class="em-control em-control-readonly" value="{{ $isPermanent ? 'Yes' : 'No' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Permanent Date</label>
                                    <input type="date" name="confirmation_date" id="manage_confirmation_date" class="em-control editable" value="{{ old('confirmation_date', $employeeData->confirmation_date ?? $employeeData->permanent_at ?? '') }}" disabled>
                                </div>
                            </div>
                        </div>
