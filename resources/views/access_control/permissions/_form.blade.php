@csrf

<div class="ac-grid mb-4">
    <div>
        <label class="ac-label">Action / Permission Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="ac-control" value="{{ old('name', $permission->action ?? '') }}" placeholder="e.g. View Employee" required>
    </div>

    <div>
        <label class="ac-label">Permission Key <span class="text-danger">*</span></label>
        <input type="text" name="permission_key" class="ac-control" value="{{ old('permission_key', $permission->key ?? '') }}" placeholder="e.g. employees.view" required>
    </div>

    <div>
        <label class="ac-label">Module Key <span class="text-danger">*</span></label>
        <input type="text" name="module_key" class="ac-control" value="{{ old('module_key', $permission->module ?? '') }}" placeholder="e.g. hrms" required>
    </div>

    <div>
        <label class="ac-label">Submodule Key</label>
        <input type="text" name="submodule" class="ac-control" value="{{ old('submodule', $permission->submodule ?? '') }}" placeholder="e.g. employees">
    </div>

    <div style="grid-column: 1 / -1;">
        <label class="ac-label">Description</label>
        <textarea name="description" class="ac-control" placeholder="Describe what actions or resources this permission key safeguards...">{{ old('description', $permission->description ?? '') }}</textarea>
    </div>

    @if($hasIsActive ?? false)
        <div>
            <input type="hidden" name="is_active" value="0">
            <label class="ac-check d-flex align-items-start">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $permission->is_active ?? true) ? 'checked' : '' }} class="mr-2">
                <span>
                    <strong>Active state</strong>
                    <span>Inactive permissions stay stored but are temporarily ignored.</span>
                </span>
            </label>
        </div>
    @endif
</div>

<div class="d-flex align-items-center flex-wrap pt-3 border-top" style="gap:8px;">
    <button type="submit" class="ac-btn ac-btn-primary">
        <i class="fas fa-save mr-1"></i> Save Permission Settings
    </button>
    <a href="{{ route('permissions.index') }}" class="ac-btn ac-btn-soft">Cancel</a>
</div>
