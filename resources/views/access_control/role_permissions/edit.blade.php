@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Role Permissions')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Role Permissions</h1>
                <p class="ac-subtitle">{{ $role->name }}</p>
            </div>
            <a href="{{ route('role_permissions.index') }}" class="ac-btn ac-btn-soft">Back</a>
        </div>

        @include('access_control.partials.flash')

        <form action="{{ route('role_permissions.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            @forelse($permissions as $module => $items)
                <div class="ac-card mb-3">
                    <div class="ac-card-body">
                        <h2 class="ac-section-title">{{ ucfirst(str_replace('_', ' ', $module)) }}</h2>

                        <div class="ac-check-list">
                            @foreach($items as $permission)
                                <label class="ac-check">
                                    <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"
                                        {{ in_array((int) $permission->id, $selectedPermissionIds, true) ? 'checked' : '' }}
                                        {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                    <span>
                                        <strong>{{ $permission->action ?? $permission->key }}</strong>
                                        <span>{{ $permission->key }}{{ $permission->submodule ? ' / '.$permission->submodule : '' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="ac-card">
                    <div class="ac-card-body text-center text-muted">No permissions found.</div>
                </div>
            @endforelse

            <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                <button type="submit" class="ac-btn ac-btn-primary">
                    <i class="fas fa-save"></i>
                    Save Mapping
                </button>
                <a href="{{ route('role_permissions.index') }}" class="ac-btn ac-btn-soft">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
