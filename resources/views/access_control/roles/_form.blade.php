@csrf

<div class="ac-grid">
    <div>
        <label class="ac-label">Name</label>
        <input type="text" name="name" class="ac-control" value="{{ old('name', $role->name ?? '') }}" required>
    </div>

    <div>
        <label class="ac-label">Code</label>
        <input type="text" name="slug" class="ac-control" value="{{ old('slug', $role->slug ?? '') }}" placeholder="hr_admin">
    </div>

    <div class="col-span-2" style="grid-column:1 / -1;">
        <label class="ac-label">Description</label>
        <textarea name="description" class="ac-control">{{ old('description', $role->description ?? '') }}</textarea>
    </div>

    <div>
        <input type="hidden" name="is_system" value="0">
        <label class="ac-check">
            <input type="checkbox" name="is_system" value="1" {{ old('is_system', $role->is_system ?? false) ? 'checked' : '' }}>
            <span>
                <strong>System Role</strong>
                <span>System roles are protected from deletion.</span>
            </span>
        </label>
    </div>

    <div>
        <input type="hidden" name="status" value="0">
        <label class="ac-check">
            <input type="checkbox" name="status" value="1" {{ old('status', $role->status ?? true) ? 'checked' : '' }}>
            <span>
                <strong>Active</strong>
                <span>Inactive roles stay saved but cannot be selected in active lists.</span>
            </span>
        </label>
    </div>
</div>

<div class="mt-4 d-flex align-items-center flex-wrap" style="gap:8px;">
    <button type="submit" class="ac-btn ac-btn-primary">
        <i class="fas fa-save"></i>
        Save
    </button>
    <a href="{{ route('roles.index') }}" class="ac-btn ac-btn-soft">Cancel</a>
</div>
