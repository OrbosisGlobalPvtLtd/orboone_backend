<div class="eo-card">
    <div class="eo-card-head">
        <div class="eo-section-title">
            <div class="eo-section-icon"><i class="fas fa-shield-alt"></i></div>
            <div>
                <h5>Access & Salary</h5>
                <p>Role, active status and initial salary/stipend history.</p>
            </div>
        </div>
    </div>

    <div class="eo-card-body">
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>System Role <span class="required">*</span></label>
                @php $roleId = old('system_role_id', $employeeData->system_role_id ?? ''); @endphp
                <select name="system_role_id" class="form-select" required>
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                    <option value="{{ $role->id }}"
                        {{ (string)$roleId === (string)$role->id || (empty($roleId) && ($role->slug ?? '') === 'employee') ? 'selected' : '' }}>
                        {{ $role->display_name ?? ($role->name ?? ($role->title ?? 'Role ' . $role->id)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Employment Status</label>
                @php $statusVal = old('employment_status', $employeeData->employment_status ?? 'active'); @endphp
                <select name="employment_status" class="form-select">
                    <option value="active" {{ $statusVal === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="resigned" {{ $statusVal === 'resigned' ? 'selected' : '' }}>Resigned</option>
                    <option value="terminated" {{ $statusVal === 'terminated' ? 'selected' : '' }}>Terminated</option>
                    <option value="inactive" {{ $statusVal === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Relieving Date</label>
                <input type="date" name="relieving_date" class="form-control"
                    value="{{ old('relieving_date', $employeeData->relieving_date ?? '') }}">
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label id="salary_label">Actual Salary (Monthly CTC) <span class="required">*</span></label>
                <input type="number" name="actual_salary" id="actual_salary" class="form-control"
                    value="{{ old('actual_salary', $employeeData->actual_salary ?? '') }}" min="0" step="1"
                    placeholder="Enter Monthly CTC (Example: 25000)">
                <div class="small-note" id="salary_note">Annual CTC is calculated automatically by the system.</div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Salary Effective From</label>
                <input type="date" name="salary_effective_from" id="salary_effective_from"
                    class="form-control" value="{{ old('salary_effective_from', $employeeData->salary_effective_from ?? '') }}">
                <div class="small-note" id="salary_effective_note">Salary history effective date.</div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Salary Reason</label>
                <input type="text" name="salary_change_reason" id="salary_change_reason"
                    class="form-control" value="{{ old('salary_change_reason', $employeeData->salary_change_reason ?? '') }}"
                    placeholder="Initial salary">
            </div>
        </div>
    </div>
</div>
