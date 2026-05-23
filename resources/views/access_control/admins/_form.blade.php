@csrf
@php
    $selectedRoleIds = old('role_ids', $admin->assigned_role_ids ?? []);
    $selectedRoleIds = is_array($selectedRoleIds) ? $selectedRoleIds : [$selectedRoleIds];
    $selectedRoleIds = array_map('intval', $selectedRoleIds);
@endphp

<div class="ac-grid mb-4">
    <div>
        <label class="ac-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="ac-control" value="{{ old('name', $admin->name ?? '') }}" placeholder="e.g. Rahul Sharma" required>
    </div>

    <div>
        <label class="ac-label">Email Address <span class="text-danger">*</span></label>
        <input type="email" name="email" class="ac-control" value="{{ old('email', $admin->email ?? '') }}" placeholder="e.g. rahul@company.com" required>
    </div>

    <div>
        <label class="ac-label">Password {{ isset($admin) ? '(Leave blank to keep unchanged)' : '' }} <span class="text-danger">*</span></label>
        <input type="password" name="password" class="ac-control" {{ isset($admin) ? '' : 'required' }} placeholder="Enter account password">
    </div>

    <div style="grid-column: 1 / -1;">
        <label class="ac-label mb-2">Assign Admin Roles <span class="text-danger">*</span></label>
        <div class="ac-check-list">
            @foreach($roles as $role)
                <label class="ac-check d-flex align-items-start">
                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" {{ in_array((int) $role->id, $selectedRoleIds, true) ? 'checked' : '' }}>
                    <span>
                        <strong>{{ $role->name }}</strong>
                        <span>{{ $role->slug }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('role_ids')
            <div class="text-danger small mt-2 font-weight-bold">{{ $message }}</div>
        @enderror
        @error('role_ids.*')
            <div class="text-danger small mt-2 font-weight-bold">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <input type="hidden" name="is_active" value="0">
        <label class="ac-check d-flex align-items-start">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $admin->is_active ?? true) ? 'checked' : '' }} class="mr-2">
            <span>
                <strong>Active Login state</strong>
                <span>Inactive administrators are immediately locked out of both interfaces.</span>
            </span>
        </label>
    </div>

    <div>
        <input type="hidden" name="is_app_access" value="0">
        <label class="ac-check d-flex align-items-start">
            <input type="checkbox" name="is_app_access" value="1" {{ old('is_app_access', $admin->is_app_access ?? false) ? 'checked' : '' }} class="mr-2">
            <span>
                <strong>Mobile App Access</strong>
                <span>Enable mobile device credential logins for administrative settings.</span>
            </span>
        </label>
    </div>
</div>

<div class="d-flex align-items-center flex-wrap pt-3 border-top" style="gap:8px;">
    <button type="submit" class="ac-btn ac-btn-primary">
        <i class="fas fa-save mr-1"></i> Save Admin Credentials
    </button>
    <a href="{{ route('admins.index') }}" class="ac-btn ac-btn-soft">Cancel</a>
</div>
