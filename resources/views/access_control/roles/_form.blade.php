@csrf

<div class="ac-grid mb-4">
    <div>
        <label class="ac-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="ac-control" value="{{ old('name', $role->name ?? '') }}" placeholder="e.g. Finance Manager" required>
    </div>

    <div>
        <label class="ac-label">Role Code / Slug <span class="text-danger">*</span></label>
        <input type="text" name="slug" class="ac-control" value="{{ old('slug', $role->slug ?? '') }}" placeholder="e.g. finance_manager" required>
    </div>

    <div style="grid-column: 1 / -1;">
        <label class="ac-label">Description</label>
        <textarea name="description" class="ac-control" placeholder="Describe the permissions and access levels associated with this role...">{{ old('description', $role->description ?? '') }}</textarea>
    </div>

    <div>
        <input type="hidden" name="is_system" value="0">
        <label class="ac-check d-flex align-items-start">
            <input type="checkbox" name="is_system" value="1" {{ old('is_system', $role->is_system ?? false) ? 'checked' : '' }} class="mr-2">
            <span>
                <strong>System Protected Role</strong>
                <span>Protected roles cannot be deleted from the database.</span>
            </span>
        </label>
    </div>

    <div>
        <input type="hidden" name="status" value="0">
        <label class="ac-check d-flex align-items-start">
            <input type="checkbox" name="status" value="1" {{ old('status', $role->status ?? true) ? 'checked' : '' }} class="mr-2">
            <span>
                <strong>Active & Selectable</strong>
                <span>Inactive roles remain saved but cannot be linked to active administrators.</span>
            </span>
        </label>
    </div>
</div>

<div class="d-flex align-items-center flex-wrap pt-3 border-top" style="gap:8px;">
    <button type="submit" class="ac-btn ac-btn-primary">
        <i class="fas fa-save mr-1"></i> Save Role Settings
    </button>
    <a href="{{ route('roles.index') }}" class="ac-btn ac-btn-soft">Cancel</a>
</div>
