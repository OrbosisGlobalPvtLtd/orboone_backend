@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Module CRUD Permissions Management')

@section('_head')
@include('access_control.partials.styles')
<style>
    .perm-nav-tabs {
        display: flex;
        gap: 12px;
        border-bottom: 2px solid #E2E8F0;
        margin-bottom: 24px;
        padding-bottom: 2px;
    }
    .perm-tab-item {
        padding: 12px 24px;
        font-weight: 700;
        font-size: 14px;
        border-radius: 12px 12px 0 0;
        color: #64748B;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-bottom: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .perm-tab-item:hover {
        color: #0F172A;
        background: #F1F5F9;
    }
    .perm-tab-item.active {
        color: #4F46E5;
        background: #FFFFFF;
        border-color: #CBD5E1;
        border-bottom: 3px solid #4F46E5;
        margin-bottom: -3px;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.03);
    }
    .matrix-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .matrix-card-header {
        background: #F8FAFC;
        padding: 16px 24px;
        border-bottom: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .matrix-card-title {
        font-size: 16px;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .crud-grid-container {
        padding: 16px 24px;
    }
    .crud-row {
        padding: 14px 16px;
        border: 1px solid #F1F5F9;
        border-radius: 12px;
        margin-bottom: 12px;
        background: #FAFAFA;
        transition: background 0.15s ease;
    }
    .crud-row:hover {
        background: #FFFFFF;
        border-color: #E2E8F0;
    }
    .crud-row-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .crud-badges-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .crud-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
        border: 1px solid #E2E8F0;
    }
    .crud-badge-pill.type-view { background: #EFF6FF; color: #1D4ED8; border-color: #BFDBFE; }
    .crud-badge-pill.type-create { background: #ECFDF5; color: #047857; border-color: #A7F3D0; }
    .crud-badge-pill.type-edit { background: #FFFBEB; color: #B45309; border-color: #FDE68A; }
    .crud-badge-pill.type-delete { background: #FEF2F2; color: #B91C1C; border-color: #FECACA; }
    .crud-badge-pill input[type="checkbox"] {
        cursor: pointer;
        width: 15px;
        height: 15px;
    }
    .badge-status {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-status-inherited { background: #E2E8F0; color: #475569; }
    .badge-status-granted { background: #10B981; color: #FFFFFF; }
    .badge-status-revoked { background: #EF4444; color: #FFFFFF; }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-gray-800 mb-1">
                <i class="fas fa-layer-group text-primary mr-2"></i>Module CRUD Permissions
            </h1>
            <p class="text-muted mb-0">Manage granular CRUD permissions and menu visibility by Role, User, and Profile.</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-12 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-12 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Navigation Scope Tabs -->
    <div class="perm-nav-tabs">
        <a href="{{ route('access_control.module_permissions.index', ['tab' => 'role', 'role_id' => $selectedRoleId]) }}" 
           class="perm-tab-item {{ $tab === 'role' ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i> Permissions By Role
        </a>
        <a href="{{ route('access_control.module_permissions.index', ['tab' => 'user', 'user_id' => $selectedUserId]) }}" 
           class="perm-tab-item {{ $tab === 'user' ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i> Permissions By User (Overrides)
        </a>
        <a href="{{ route('access_control.module_permissions.index', ['tab' => 'profile', 'department_id' => $selectedDepartmentId]) }}" 
           class="perm-tab-item {{ $tab === 'profile' ? 'active' : '' }}">
            <i class="fas fa-id-card"></i> Permissions By Profile / Department
        </a>
    </div>

    <!-- Filter Bar & Target Selector -->
    <div class="card border-0 shadow-sm rounded-16 mb-4">
        <div class="card-body p-4">
            @if($tab === 'role')
                <form method="GET" action="{{ route('access_control.module_permissions.index') }}" class="row align-items-end">
                    <input type="hidden" name="tab" value="role">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="font-weight-bold text-gray-700 mb-1"><i class="fas fa-user-tag text-primary mr-2"></i>Select System Role:</label>
                        <select name="role_id" class="form-control form-control-lg rounded-12 select2-searchable">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ (int)$selectedRoleId === (int)$r->id ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->slug }}) {{ $r->is_system ? '- System Default' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <button type="submit" class="btn btn-primary font-weight-bold px-4 rounded-12 shadow-sm" style="height: 48px; background: var(--orb-primary); border: none;">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <input type="text" id="moduleSearch" class="form-control rounded-12" placeholder="Search modules & permissions...">
                    </div>
                </form>
            @elseif($tab === 'user')
                <form method="GET" action="{{ route('access_control.module_permissions.index') }}" class="row align-items-end">
                    <input type="hidden" name="tab" value="user">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="font-weight-bold text-gray-700 mb-1"><i class="fas fa-user text-primary mr-2"></i>Select Target User:</label>
                        <select name="user_id" class="form-control form-control-lg rounded-12 select2-searchable">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (int)$selectedUserId === (int)$u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <button type="submit" class="btn btn-primary font-weight-bold px-4 rounded-12 shadow-sm" style="height: 48px; background: var(--orb-primary); border: none;">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <input type="text" id="moduleSearch" class="form-control rounded-12" placeholder="Search modules & permissions...">
                    </div>
                </form>
            @elseif($tab === 'profile')
                <form method="GET" action="{{ route('access_control.module_permissions.index') }}" class="row align-items-end">
                    <input type="hidden" name="tab" value="profile">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="font-weight-bold text-gray-700 mb-1"><i class="fas fa-building text-primary mr-2"></i>Select Profile / Department:</label>
                        <select name="department_id" class="form-control form-control-lg rounded-12 select2-searchable">
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ (int)$selectedDepartmentId === (int)$d->id ? 'selected' : '' }}>
                                    {{ $d->name }} Department / Profile
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <button type="submit" class="btn btn-primary font-weight-bold px-4 rounded-12 shadow-sm" style="height: 48px; background: var(--orb-primary); border: none;">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <input type="text" id="moduleSearch" class="form-control rounded-12" placeholder="Search modules & permissions...">
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- TAB 1: BY ROLE -->
    @if($tab === 'role')
        <form method="POST" action="{{ route('access_control.module_permissions.update_role', $selectedRoleId) }}">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted font-weight-bold">Configuring permissions for role: <span class="text-primary">{{ $role->name ?? 'Role' }}</span></span>
                <button type="submit" class="btn btn-primary btn-lg rounded-12 px-4 shadow-sm">
                    <i class="fas fa-save mr-2"></i>Save Role Permissions
                </button>
            </div>

            @foreach($modulesTree as $module)
                <div class="matrix-card module-block">
                    <div class="matrix-card-header">
                        <h3 class="matrix-card-title">
                            <input type="checkbox" name="menu_ids[]" value="{{ $module['id'] }}" 
                                   class="parent-menu-checkbox" {{ $module['is_assigned'] ? 'checked' : '' }}>
                            <i class="fas fa-folder text-warning"></i> {{ $module['name'] }}
                        </h3>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-8 toggle-all-btn">
                            Toggle All
                        </button>
                    </div>

                    <div class="crud-grid-container">
                        @if(!empty($module['crud']))
                            <div class="crud-row">
                                <div class="crud-row-header">
                                    <span class="font-weight-bold text-dark"><i class="fas fa-cog text-muted mr-1"></i> Module Level Controls</span>
                                </div>
                                <div class="crud-badges-group">
                                    @foreach(['view', 'create', 'edit', 'delete'] as $type)
                                        @foreach($module['crud'][$type] ?? [] as $perm)
                                            <label class="crud-badge-pill type-{{ $type }}">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $perm['id'] }}" {{ $perm['is_assigned'] ? 'checked' : '' }}>
                                                <span>{{ ucfirst($type) }}: {{ $perm['action'] }}</span>
                                            </label>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @foreach($module['submenus'] as $sub)
                            <div class="crud-row submenu-block">
                                <div class="crud-row-header">
                                    <span class="font-weight-bold text-dark">
                                        <input type="checkbox" name="menu_ids[]" value="{{ $sub['id'] }}" 
                                               class="child-menu-checkbox" {{ $sub['is_assigned'] ? 'checked' : '' }}>
                                        <i class="fas fa-file-alt text-primary mr-1"></i> {{ $sub['name'] }}
                                    </span>
                                </div>
                                <div class="crud-badges-group">
                                    @foreach(['view', 'create', 'edit', 'delete'] as $type)
                                        @foreach($sub['crud'][$type] ?? [] as $perm)
                                            <label class="crud-badge-pill type-{{ $type }}">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $perm['id'] }}" {{ $perm['is_assigned'] ? 'checked' : '' }}>
                                                <span>{{ ucfirst($type) }}: {{ $perm['action'] }}</span>
                                            </label>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="text-right mt-4 mb-5">
                <button type="submit" class="btn btn-primary btn-lg rounded-12 px-5 shadow-sm">
                    <i class="fas fa-save mr-2"></i>Save Role Permissions
                </button>
            </div>
        </form>

    <!-- TAB 2: BY USER (OVERRIDES) -->
    @elseif($tab === 'user')
        <form method="POST" action="{{ route('access_control.module_permissions.update_user', $selectedUserId) }}">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-muted font-weight-bold">Custom permission overrides for user: <span class="text-primary">{{ $user->name ?? 'User' }}</span></span>
                    <span class="badge badge-info ml-2">Primary Role: {{ $user->primary_role_name ?? 'Default' }}</span>
                </div>
                <button type="submit" class="btn btn-primary btn-lg rounded-12 px-4 shadow-sm">
                    <i class="fas fa-save mr-2"></i>Save User Overrides
                </button>
            </div>

            @foreach($modulesTree as $module)
                <div class="matrix-card module-block">
                    <div class="matrix-card-header">
                        <h3 class="matrix-card-title">
                            <i class="fas fa-folder text-warning"></i> {{ $module['name'] }}
                        </h3>
                    </div>

                    <div class="crud-grid-container">
                        @foreach($module['submenus'] as $sub)
                            <div class="crud-row submenu-block">
                                <div class="crud-row-header">
                                    <span class="font-weight-bold text-dark">
                                        <i class="fas fa-file-alt text-primary mr-1"></i> {{ $sub['name'] }}
                                    </span>
                                </div>
                                <div class="crud-badges-group">
                                    @foreach(['view', 'create', 'edit', 'delete'] as $type)
                                        @foreach($sub['crud'][$type] ?? [] as $perm)
                                            <div class="p-2 border rounded-8 bg-white d-inline-flex align-items-center gap-2 mr-2 mb-2">
                                                <span class="font-weight-bold text-capitalize text-dark" style="font-size:12px;">{{ $type }}: {{ $perm['action'] }}</span>
                                                
                                                @if($perm['is_inherited'])
                                                    <span class="badge-status badge-status-inherited">Role Inherited</span>
                                                @endif

                                                <select name="user_override[{{ $perm['key'] }}]" class="form-control form-control-sm rounded-6" style="width:130px; font-size:11px;">
                                                    <option value="inherit" {{ $perm['override_status'] === null ? 'selected' : '' }}>Inherit Default</option>
                                                    <option value="grant" {{ $perm['override_status'] === true ? 'selected' : '' }}>Explicit Grant</option>
                                                    <option value="revoke" {{ $perm['override_status'] === false ? 'selected' : '' }}>Explicit Revoke</option>
                                                </select>
                                                
                                                <!-- Processed in controller -->
                                                <input type="hidden" name="grants[]" value="{{ $perm['key'] }}" class="grant-input" {{ $perm['override_status'] === true ? '' : 'disabled' }}>
                                                <input type="hidden" name="revokes[]" value="{{ $perm['key'] }}" class="revoke-input" {{ $perm['override_status'] === false ? '' : 'disabled' }}>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="text-right mt-4 mb-5">
                <button type="submit" class="btn btn-primary btn-lg rounded-12 px-5 shadow-sm">
                    <i class="fas fa-save mr-2"></i>Save User Overrides
                </button>
            </div>
        </form>

    <!-- TAB 3: BY PROFILE / DEPARTMENT -->
    @elseif($tab === 'profile')
        <form method="POST" action="{{ route('access_control.module_permissions.update_profile', $selectedDepartmentId) }}">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted font-weight-bold">Profile Baseline permissions for: <span class="text-primary">{{ $department->name ?? 'Department' }} Profile</span></span>
                <button type="submit" class="btn btn-primary btn-lg rounded-12 px-4 shadow-sm">
                    <i class="fas fa-save mr-2"></i>Save Profile Permissions
                </button>
            </div>

            @foreach($modulesTree as $module)
                <div class="matrix-card module-block">
                    <div class="matrix-card-header">
                        <h3 class="matrix-card-title">
                            <i class="fas fa-building text-info"></i> {{ $module['name'] }}
                        </h3>
                    </div>

                    <div class="crud-grid-container">
                        @foreach($module['submenus'] as $sub)
                            <div class="crud-row submenu-block">
                                <div class="crud-row-header">
                                    <span class="font-weight-bold text-dark">
                                        <i class="fas fa-file-alt text-primary mr-1"></i> {{ $sub['name'] }}
                                    </span>
                                </div>
                                <div class="crud-badges-group">
                                    @foreach(['view', 'create', 'edit', 'delete'] as $type)
                                        @foreach($sub['crud'][$type] ?? [] as $perm)
                                            <label class="crud-badge-pill type-{{ $type }}">
                                                <input type="checkbox" name="permission_keys[]" value="{{ $perm['key'] }}" {{ $perm['is_assigned'] ? 'checked' : '' }}>
                                                <span>{{ ucfirst($type) }}: {{ $perm['action'] }}</span>
                                            </label>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="text-right mt-4 mb-5">
                <button type="submit" class="btn btn-primary btn-lg rounded-12 px-5 shadow-sm">
                    <i class="fas fa-save mr-2"></i>Save Profile Permissions
                </button>
            </div>
        </form>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Instant module search
    const searchInput = document.getElementById('moduleSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.module-block').forEach(function (card) {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // Dynamic select change handler for User Overrides tab
    document.querySelectorAll('select[name^="user_override"]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            const container = this.closest('div');
            const grantInput = container.querySelector('.grant-input');
            const revokeInput = container.querySelector('.revoke-input');

            if (this.value === 'grant') {
                grantInput.removeAttribute('disabled');
                revokeInput.setAttribute('disabled', 'disabled');
            } else if (this.value === 'revoke') {
                revokeInput.removeAttribute('disabled');
                grantInput.setAttribute('disabled', 'disabled');
            } else {
                grantInput.setAttribute('disabled', 'disabled');
                revokeInput.setAttribute('disabled', 'disabled');
            }
        });
    });

    // Parent to child menu checkbox sync
    document.querySelectorAll('.parent-menu-checkbox').forEach(function (parentCb) {
        parentCb.addEventListener('change', function () {
            const block = this.closest('.module-block');
            block.querySelectorAll('.child-menu-checkbox').forEach(function (childCb) {
                childCb.checked = parentCb.checked;
            });
        });
    });
});
</script>
@endsection
