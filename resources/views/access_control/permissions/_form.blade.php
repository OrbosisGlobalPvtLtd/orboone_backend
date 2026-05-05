@csrf

<div class="ac-grid">
    <div>
        <label class="ac-label">Name</label>
        <input type="text" name="name" class="ac-control" value="{{ old('name', $permission->action ?? '') }}" required>
    </div>

    <div>
        <label class="ac-label">Permission Key</label>
        <input type="text" name="permission_key" class="ac-control" value="{{ old('permission_key', $permission->key ?? '') }}" placeholder="employees.view" required>
    </div>

    <div>
        <label class="ac-label">Module Key</label>
        <input type="text" name="module_key" class="ac-control" value="{{ old('module_key', $permission->module ?? '') }}" placeholder="hrms" required>
    </div>

    <div>
        <label class="ac-label">Submodule</label>
        <input type="text" name="submodule" class="ac-control" value="{{ old('submodule', $permission->submodule ?? '') }}" placeholder="employees">
    </div>

    <div style="grid-column:1 / -1;">
        <label class="ac-label">Description</label>
        <textarea name="description" class="ac-control">{{ old('description', $permission->description ?? '') }}</textarea>
    </div>

    @if($hasIsActive ?? false)
        <div>
            <input type="hidden" name="is_active" value="0">
            <label class="ac-check">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $permission->is_active ?? true) ? 'checked' : '' }}>
                <span>
                    <strong>Active</strong>
                    <span>Inactive permissions stay saved but are not used for access.</span>
                </span>
            </label>
        </div>
    @endif
</div>

<div class="mt-4 d-flex align-items-center flex-wrap" style="gap:8px;">
    <button type="submit" class="ac-btn ac-btn-primary">
        <i class="fas fa-save"></i>
        Save
    </button>
    <a href="{{ route('permissions.index') }}" class="ac-btn ac-btn-soft">Cancel</a>
</div>
