                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-building"></i>Job Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Department</label>
                                    <select name="department_id" id="department_id" class="em-control editable-select" disabled>
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $employeeData->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Designation</label>
                                    <select name="designation_id" id="designation_id" class="em-control editable-select" disabled>
                                        <option value="">Select Designation</option>
                                        @foreach ($designations as $des)
                                        <option value="{{ $des->id }}" data-department-id="{{ $des->department_id ?? '' }}" {{ old('designation_id', $employeeData->designation_id) == $des->id ? 'selected' : '' }}>
                                            {{ $des->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('designation_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Reporting Manager</label>
                                    <select name="reporting_manager_employee_id" class="em-control editable-select" disabled>
                                        <option value="">Select Manager</option>
                                        @foreach(($reportingManagers ?? collect()) as $manager)
                                        <option value="{{ $manager->id }}" {{ old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id ?? '') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }} - {{ $manager->employee_code }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('reporting_manager_employee_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>System Role</label>
                                    <select name="system_role_id" class="em-control editable-select" disabled>
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('system_role_id', $employeeData->system_role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name ?? ($role->name ?? ($role->title ?? 'Role '.$role->id)) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('system_role_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
