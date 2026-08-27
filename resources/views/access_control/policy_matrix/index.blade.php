@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'RBAC Policy Matrix')

@section('_head')
@include('access_control.partials.styles')
<style>
    .matrix-module-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .matrix-module-header {
        background: #F8FAFC;
        padding: 16px 24px;
        border-bottom: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .matrix-module-title {
        font-size: 16px;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .matrix-submenu-row {
        padding: 16px 24px;
        border-bottom: 1px solid #F1F5F9;
        background: #ffffff;
    }
    .matrix-submenu-row:last-child {
        border-bottom: none;
    }
    .matrix-submenu-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .crud-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
        padding-left: 28px;
    }
    .crud-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #E2E8F0;
        background: #F8FAFC;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .crud-pill input[type="checkbox"] {
        accent-color: #4B00E8;
        width: 15px;
        height: 15px;
        cursor: pointer;
    }
    .crud-pill.badge-view { border-color: rgba(59, 130, 246, 0.3); background: #EFF6FF; color: #1E40AF; }
    .crud-pill.badge-create { border-color: rgba(34, 197, 94, 0.3); background: #F0FDF4; color: #166534; }
    .crud-pill.badge-edit { border-color: rgba(245, 158, 11, 0.3); background: #FFFBEB; color: #92400E; }
    .crud-pill.badge-delete { border-color: rgba(239, 68, 68, 0.3); background: #FEF2F2; color: #991B1B; }
    .crud-pill.badge-manage { border-color: rgba(168, 85, 247, 0.3); background: #FAF5FF; color: #6B21A8; }
    
    .role-switcher-card {
        background: linear-gradient(135deg, #4B00E8 0%, #7622FF 100%);
        border-radius: 20px;
        padding: 24px 30px;
        color: #ffffff;
        margin-bottom: 28px;
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.2);
    }
    .role-select-box {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #ffffff;
        border-radius: 12px;
        padding: 10px 16px;
        font-weight: 700;
        font-size: 14px;
        width: 100%;
        max-width: 320px;
        backdrop-filter: blur(8px);
    }
    .role-select-box option {
        color: #0F172A;
        background: #ffffff;
    }
</style>
@endsection

@section('_content')
<div class="ac-page">
    <div class="ac-container">
        
        <!-- Header -->
        <div class="ac-header mb-4">
            <div>
                <div class="ac-kicker">
                    <i class="fas fa-shield-halved"></i> HRMS &bull; ACCESS CONTROL
                </div>
                <h1 class="ac-title">Dynamic RBAC Policy Matrix</h1>
                <p class="ac-subtitle">Manage Page Visibility (Sidebar) and Granular CRUD Permissions in one place</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <a href="{{ route('roles.index') }}" class="ac-btn ac-btn-soft">
                    <i class="fas fa-user-shield mr-1"></i> Roles List
                </a>
            </div>
        </div>

        @include('access_control.partials.flash')

        <!-- Role Selector Header Bar -->
        <div class="role-switcher-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
                <div>
                    <h4 class="m-0 font-weight-bold" style="color: #ffffff;"><i class="fas fa-user-tag mr-2"></i> Selected Target Role: {{ $role->name }}</h4>
                    <p class="mb-0 mt-1 small" style="opacity: 0.85;">Switching role will update the policy matrix below.</p>
                </div>
                <div>
                    <form method="GET" action="{{ route('access_control.policy_matrix.index') }}" id="roleSelectForm">
                        <select name="role_id" class="role-select-box" onchange="document.getElementById('roleSelectForm').submit();">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ (int)$selectedRoleId === (int)$r->id ? 'selected' : '' }}>
                                    {{ $r->name }} {{ $r->slug === 'super_admin' ? '(Super Admin)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

        @if($role->slug === 'super_admin')
            <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 18px; font-weight: 700; font-size: 13px; background: #FFF9EB; color: #B25E00; border: 1px solid rgba(247, 144, 9, 0.15);">
                <i class="fas fa-shield-alt mr-2" style="font-size: 16px;"></i> Super Administrator has 100% full system access to all navigation pages and CRUD permissions automatically.
            </div>
        @endif

        <form action="{{ route('access_control.policy_matrix.update', $selectedRoleId) }}" method="POST" id="matrixForm">
            @csrf

            <!-- Quick Action Toolbar -->
            <div class="ac-card p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px; background: #F8FAFC;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAllCheckboxes(true)" style="border-radius: 8px; font-weight: 700;">
                        <i class="fas fa-check-double mr-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllCheckboxes(false)" style="border-radius: 8px; font-weight: 700;">
                        <i class="fas fa-times mr-1"></i> Deselect All
                    </button>
                </div>
                <div>
                    @if($role->slug !== 'super_admin')
                        <button type="submit" class="ac-btn ac-btn-primary" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; font-size: 14px; padding: 8px 24px; border-radius: 10px;">
                            <i class="fas fa-save mr-1"></i> Save RBAC Policy Matrix
                        </button>
                    @endif
                </div>
            </div>

            <!-- Modules Tree -->
            @foreach($modules as $module)
                <div class="matrix-module-card">
                    <!-- Module Level Header -->
                    <div class="matrix-module-header">
                        <div class="matrix-module-title">
                            <i class="fas fa-folder-open text-primary"></i>
                            <span>{{ $module['name'] }}</span>
                        </div>
                        <div>
                            <label class="ac-check mb-0">
                                <input type="checkbox" name="menu_ids[]" value="{{ $module['id'] }}"
                                    data-module-id="{{ $module['id'] }}"
                                    class="parent-menu-checkbox"
                                    {{ $module['is_assigned'] ? 'checked' : '' }}
                                    {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                <span class="font-weight-bold small">Enable Module in Sidebar</span>
                            </label>
                        </div>
                    </div>

                    <!-- Module Root CRUD Permissions (if any) -->
                    @if(!empty($module['crud_permissions']))
                        <div class="px-4 pt-3 pb-2 bg-light border-bottom">
                            <div class="small font-weight-bold text-muted mb-1"><i class="fas fa-key mr-1"></i> Module Level Action Permissions:</div>
                            <div class="crud-grid pl-0">
                                @foreach($module['crud_permissions'] as $perm)
                                    @php
                                        $badgeClass = 'badge-view';
                                        if (str_contains($perm['action'], 'create') || str_contains($perm['action'], 'add')) $badgeClass = 'badge-create';
                                        elseif (str_contains($perm['action'], 'edit') || str_contains($perm['action'], 'update')) $badgeClass = 'badge-edit';
                                        elseif (str_contains($perm['action'], 'delete') || str_contains($perm['action'], 'destroy')) $badgeClass = 'badge-delete';
                                        elseif (str_contains($perm['action'], 'manage') || str_contains($perm['action'], 'approve')) $badgeClass = 'badge-manage';
                                    @endphp
                                    <label class="crud-pill {{ $badgeClass }}">
                                        <input type="checkbox" name="permission_ids[]" value="{{ $perm['id'] }}"
                                            class="perm-checkbox"
                                            {{ $perm['is_assigned'] ? 'checked' : '' }}
                                            {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                        <span>{{ $perm['key'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Submenus / Pages -->
                    @if(!empty($module['submenus']))
                        @foreach($module['submenus'] as $child)
                            <div class="matrix-submenu-row">
                                <div class="matrix-submenu-header">
                                    <div class="d-flex align-items-center" style="gap: 12px;">
                                        <label class="ac-check mb-0">
                                            <input type="checkbox" name="menu_ids[]" value="{{ $child['id'] }}"
                                                data-parent-module="{{ $module['id'] }}"
                                                class="child-menu-checkbox"
                                                {{ $child['is_assigned'] ? 'checked' : '' }}
                                                {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                            <span class="font-weight-bold text-dark" style="font-size: 14px;">
                                                <i class="fas fa-link mr-1 text-muted"></i> {{ $child['name'] }}
                                            </span>
                                        </label>
                                        @if($child['route'])
                                            <span class="badge badge-light border text-monospace small">{{ $child['route'] }}</span>
                                        @endif
                                        @if($child['permission_key'])
                                            <span class="badge badge-info text-monospace small" title="Primary Permission Key"><i class="fas fa-lock mr-1"></i> {{ $child['permission_key'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Granular CRUD Action Checkboxes for Page -->
                                @if(!empty($child['crud_permissions']))
                                    <div class="crud-grid">
                                        @foreach($child['crud_permissions'] as $perm)
                                            @php
                                                $badgeClass = 'badge-view';
                                                if (str_contains($perm['action'], 'create') || str_contains($perm['action'], 'add')) $badgeClass = 'badge-create';
                                                elseif (str_contains($perm['action'], 'edit') || str_contains($perm['action'], 'update')) $badgeClass = 'badge-edit';
                                                elseif (str_contains($perm['action'], 'delete') || str_contains($perm['action'], 'destroy')) $badgeClass = 'badge-delete';
                                                elseif (str_contains($perm['action'], 'manage') || str_contains($perm['action'], 'approve')) $badgeClass = 'badge-manage';
                                            @endphp
                                            <label class="crud-pill {{ $badgeClass }}" title="{{ $perm['description'] }}">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $perm['id'] }}"
                                                    class="perm-checkbox"
                                                    {{ $perm['is_assigned'] ? 'checked' : '' }}
                                                    {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                                <span>{{ $perm['key'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach

            <!-- Unmapped Permissions Accordion -->
            @if(!$unmappedPermissions->isEmpty())
                <div class="matrix-module-card mt-4">
                    <div class="matrix-module-header bg-light">
                        <div class="matrix-module-title text-secondary">
                            <i class="fas fa-sliders-h"></i>
                            <span>Additional System / Action Permissions</span>
                        </div>
                    </div>
                    <div class="p-4">
                        @foreach($unmappedPermissions as $modName => $perms)
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-dark text-capitalize"><i class="fas fa-layer-group mr-1 text-primary"></i> Module: {{ $modName }}</h6>
                                <div class="crud-grid pl-0">
                                    @foreach($perms as $perm)
                                        @php
                                            $badgeClass = 'badge-view';
                                            if (str_contains($perm['action'], 'create')) $badgeClass = 'badge-create';
                                            elseif (str_contains($perm['action'], 'edit') || str_contains($perm['action'], 'update')) $badgeClass = 'badge-edit';
                                            elseif (str_contains($perm['action'], 'delete')) $badgeClass = 'badge-delete';
                                            elseif (str_contains($perm['action'], 'manage')) $badgeClass = 'badge-manage';
                                        @endphp
                                        <label class="crud-pill {{ $badgeClass }}">
                                            <input type="checkbox" name="permission_ids[]" value="{{ $perm['id'] }}"
                                                class="perm-checkbox"
                                                {{ $perm['is_assigned'] ? 'checked' : '' }}
                                                {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                            <span>{{ $perm['key'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Submit Buttons -->
            @if($role->slug !== 'super_admin')
                <div class="d-flex align-items-center flex-wrap pt-3 mb-5" style="gap: 12px;">
                    <button type="submit" class="ac-btn ac-btn-primary" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; min-height: 44px; border-radius: 12px; font-weight: 800; padding: 0 28px;">
                        <i class="fas fa-save mr-2"></i> Save Policy Matrix & Sync Sidebar
                    </button>
                    <a href="{{ route('roles.index') }}" class="ac-btn ac-btn-soft" style="background: #F1F5F9 !important; color: #475569 !important; border-color: #E2E8F0 !important; min-height: 44px; border-radius: 12px; font-weight: 800;">Cancel</a>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
function toggleAllCheckboxes(state) {
    document.querySelectorAll('#matrixForm input[type="checkbox"]:not([disabled])').forEach(function(cb) {
        cb.checked = state;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.child-menu-checkbox').forEach(function(childCb) {
        childCb.addEventListener('change', function() {
            if (this.checked) {
                var parentId = this.getAttribute('data-parent-module');
                var parentCb = document.querySelector('.parent-menu-checkbox[data-module-id="' + parentId + '"]');
                if (parentCb) {
                    parentCb.checked = true;
                }
            }
        });
    });
});
</script>
@endsection
