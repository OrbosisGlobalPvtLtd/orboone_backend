@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Role Permissions Mapping')

@section('_head')
@include('access_control.partials.styles')
@endsection

@section('_content')
<div class="ac-page">
    <div class="ac-container">
        <!-- Premium Purple Gradient Hero -->
        <div class="ac-header">
            <div>
                <div class="ac-kicker">
                    <i class="fas fa-key"></i> HRMS &bull; ACCESS CONTROL
                </div>
                <h1 class="ac-title">Role Permissions Mapping</h1>
                <p class="ac-subtitle">Customize security privileges and authorization gates for role "{{ $role->name }}"</p>
            </div>
            <a href="{{ route('role_permissions.index') }}" class="ac-btn ac-btn-soft">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @include('access_control.partials.flash')

        @if($role->slug === 'super_admin')
            <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 18px; font-weight: 700; font-size: 13px; background: #FFF9EB; color: #B25E00; border: 1px solid rgba(247, 144, 9, 0.15);">
                <i class="fas fa-shield-alt mr-2" style="font-size: 16px;"></i> Note: As the ultimate Super Administrator role, all permissions are granted by default and cannot be revoked.
            </div>
        @endif

        <form action="{{ route('role_permissions.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            @forelse($permissions as $module => $items)
                <!-- Group Card for Module -->
                <div class="ac-card mb-4">
                    <div class="ac-table-header" style="background: #F8FAFC;">
                        <div class="ac-table-head-left">
                            <div class="ac-icon-box" style="background: #fff; border: 1px solid var(--ac-border);"><i class="fas fa-layer-group text-primary"></i></div>
                            <div>
                                <h5 class="ac-table-title" style="text-transform: capitalize;">{{ str_replace('_', ' ', $module) }} Module</h5>
                                <p class="ac-table-subtitle">Configure operational actions, validation scopes, and data policies for this block.</p>
                            </div>
                        </div>
                    </div>

                    <div class="ac-card-body" style="padding: 24px;">
                        <div class="ac-check-list">
                            @foreach($items as $permission)
                                <label class="ac-check d-flex align-items-start {{ $role->slug === 'super_admin' ? 'disabled' : '' }}" style="{{ $role->slug === 'super_admin' ? 'opacity: 0.8; cursor: not-allowed;' : '' }}">
                                    <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"
                                        {{ in_array((int) $permission->id, $selectedPermissionIds, true) ? 'checked' : '' }}
                                        {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                    <span>
                                        <strong>{{ $permission->action ?? $permission->key }}</strong>
                                        <span class="d-inline-flex align-items-center mt-1" style="font-family: monospace; font-size: 10px; background: #F1F5F9; border: 1px solid var(--ac-border); border-radius: 4px; padding: 1px 4px;">
                                            {{ $permission->key }}{{ $permission->submodule ? ' / '.$permission->submodule : '' }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="ac-card py-5 text-center text-muted mb-4">
                    <div style="font-size: 32px; color: var(--ac-muted);"><i class="fas fa-folder-open"></i></div>
                    <h6 class="mt-3 font-weight-bold">No Permissions Configured</h6>
                    <p class="small mb-0">Please create permission keys before editing mappings.</p>
                </div>
            @endforelse

            <div class="d-flex align-items-center flex-wrap pt-3" style="gap:8px;">
                @if($role->slug !== 'super_admin')
                    <button type="submit" class="ac-btn ac-btn-primary" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; min-height: 42px; border-radius: 12px; font-weight: 800; padding: 0 24px;">
                        <i class="fas fa-save mr-1"></i> Save Mapping Configuration
                    </button>
                @endif
                <a href="{{ route('role_permissions.index') }}" class="ac-btn ac-btn-soft" style="background: #F1F5F9 !important; color: #475569 !important; border-color: #E2E8F0 !important; min-height: 42px; border-radius: 12px; font-weight: 800;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
