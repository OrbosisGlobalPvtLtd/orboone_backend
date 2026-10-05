                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-building"></i>Job Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Department</label>
                                    <x-form.select 
                                        name="department_id" 
                                        id="department_id" 
                                        class="em-control editable-select" 
                                        placeholder="Select Department"
                                        :options="$departments" 
                                        :selected="old('department_id', $employeeData->department_id)" 
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    />
                                    @error('department_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Designation</label>
                                    @php
                                        $currDeptId = old('department_id', $employeeData->department_id ?? '');
                                        $desigId = old('designation_id', $employeeData->designation_id ?? '');
                                    @endphp
                                    <x-form.select 
                                        name="designation_id" 
                                        id="designation_id" 
                                        class="em-control editable-select" 
                                        placeholder="Select Designation"
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    >
                                        @foreach ($designations as $des)
                                            @if(empty($currDeptId) || (string)$currDeptId === (string)($des->department_id ?? ''))
                                            <option value="{{ $des->id }}" data-department-id="{{ $des->department_id ?? '' }}" {{ (string)$desigId === (string)$des->id ? 'selected' : '' }}>
                                                {{ $des->name }}
                                            </option>
                                            @endif
                                        @endforeach
                                    </x-form.select>
                                    @error('designation_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Reporting Manager</label>
                                    <x-form.select 
                                        name="reporting_manager_employee_id" 
                                        id="reporting_manager_employee_id" 
                                        class="em-control editable-select" 
                                        placeholder="Select Manager"
                                        :selected="old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id ?? '')" 
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    >
                                        @foreach(($reportingManagers ?? collect()) as $manager)
                                        <option value="{{ $manager->id }}" {{ old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id ?? '') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }} - {{ $manager->employee_code }}
                                        </option>
                                        @endforeach
                                    </x-form.select>
                                    @error('reporting_manager_employee_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>System Role</label>
                                    <x-form.select 
                                        name="system_role_id" 
                                        id="system_role_id" 
                                        class="em-control editable-select" 
                                        placeholder="Select Role"
                                        :selected="old('system_role_id', $employeeData->system_role_id)" 
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    >
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('system_role_id', $employeeData->system_role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name ?? ($role->name ?? ($role->title ?? 'Role '.$role->id)) }}
                                        </option>
                                        @endforeach
                                    </x-form.select>
                                    @error('system_role_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
