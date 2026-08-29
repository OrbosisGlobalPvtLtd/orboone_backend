@php
    $currentRoute = Route::currentRouteName();
@endphp

<div class="ac-nav-tabs-wrapper mb-4">
    <div class="ac-nav-tabs">
        <a href="{{ route('access_control.visualizer.index') }}" 
           class="ac-nav-tab {{ str_starts_with($currentRoute, 'access_control.visualizer') ? 'active' : '' }}">
            <i class="fas fa-chart-network"></i>
            <span>RBAC Visualizer</span>
            <span class="ac-nav-badge">Live</span>
        </a>

        <a href="{{ route('access_control.policy_matrix.index') }}" 
           class="ac-nav-tab {{ str_starts_with($currentRoute, 'access_control.policy_matrix') ? 'active' : '' }}">
            <i class="fas fa-th"></i>
            <span>Policy Matrix</span>
        </a>

        <a href="{{ route('access_control.module_permissions.index') }}" 
           class="ac-nav-tab {{ str_starts_with($currentRoute, 'access_control.module_permissions') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i>
            <span>Module CRUD Access</span>
        </a>

        <a href="{{ route('roles.index') }}" 
           class="ac-nav-tab {{ str_starts_with($currentRoute, 'roles.') ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i>
            <span>Roles</span>
        </a>

        <a href="{{ route('permissions.index') }}" 
           class="ac-nav-tab {{ str_starts_with($currentRoute, 'permissions.') ? 'active' : '' }}">
            <i class="fas fa-key"></i>
            <span>Permissions</span>
        </a>

        <a href="{{ route('admins.index') }}" 
           class="ac-nav-tab {{ str_starts_with($currentRoute, 'admins.') ? 'active' : '' }}">
            <i class="fas fa-users-cog"></i>
            <span>Admin Users</span>
        </a>
    </div>
</div>

<style>
    .ac-nav-tabs-wrapper {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }
    .ac-nav-tabs {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .ac-nav-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 750;
        font-size: 13px;
        color: #64748B;
        text-decoration: none !important;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .ac-nav-tab:hover {
        color: var(--orb-primary, #4B00E8);
        background: #F8FAFC;
    }
    .ac-nav-tab.active {
        color: #ffffff !important;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25);
    }
    .ac-nav-badge {
        font-size: 10px;
        padding: 2px 7px;
        border-radius: 20px;
        font-weight: 850;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .ac-nav-tab:not(.active) .ac-nav-badge {
        background: #EEF2FF;
        color: var(--orb-primary, #4B00E8);
    }
</style>
