@csrf
@php
    $selectedRoleIds = old('role_ids', $admin->assigned_role_ids ?? []);
    $selectedRoleIds = is_array($selectedRoleIds) ? $selectedRoleIds : [$selectedRoleIds];
    $selectedRoleIds = array_map('intval', $selectedRoleIds);
@endphp

<div class="ac-grid">
    <div>
        <label class="ac-label">Name</label>
        <input type="text" name="name" class="ac-control" value="{{ old('name', $admin->name ?? '') }}" required>
    </div>

    <div>
        <label class="ac-label">Email</label>
        <input type="email" name="email" class="ac-control" value="{{ old('email', $admin->email ?? '') }}" required>
    </div>

    <div>
        <label class="ac-label">Password</label>
        <input type="password" name="password" class="ac-control" {{ isset($admin) ? '' : 'required' }}>
    </div>

    <div style="grid-column:1/-1;">
        <label class="ac-label">Admin Roles</label>
        <div class="ac-check-list">
            @foreach($roles as $role)
                <label class="ac-check">
                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" {{ in_array((int) $role->id, $selectedRoleIds, true) ? 'checked' : '' }}>
                    <span>
                        <strong>{{ $role->name }}</strong>
                        <span>{{ $role->slug }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('role_ids')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
        @error('role_ids.*')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <input type="hidden" name="is_active" value="0">
        <label class="ac-check">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $admin->is_active ?? true) ? 'checked' : '' }}>
            <span>
                <strong>Active</strong>
                <span>Inactive admins cannot access the web panel.</span>
            </span>
        </label>
    </div>

    <div>
        <input type="hidden" name="is_app_access" value="0">
        <label class="ac-check">
            <input type="checkbox" name="is_app_access" value="1" {{ old('is_app_access', $admin->is_app_access ?? false) ? 'checked' : '' }}>
            <span>
                <strong>App Access</strong>
                <span>Enable only when this admin also needs mobile app access.</span>
            </span>
        </label>
    </div>
</div>

<div class="mt-4 d-flex align-items-center flex-wrap" style="gap:8px;">
    <button type="submit" class="ac-btn ac-btn-primary">
        <i class="fas fa-save"></i>
        Save
    </button>
    <a href="{{ route('admins.index') }}" class="ac-btn ac-btn-soft">Cancel</a>
</div>
