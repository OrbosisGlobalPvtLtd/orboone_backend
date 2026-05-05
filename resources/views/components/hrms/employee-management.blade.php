@php
    $user = auth()->user();

    $canEmployeeCreate = $user->hasPermission('employees.create');
    $canEmployeeView = $user->hasPermission('employees.view');
    $canPerformanceView = $user->hasPermission('employees.performance.view');
    $canDepartmentManage = $user->hasPermission('departments.manage');
    $canOrgHierarchyManage = $user->hasPermission('organization_hierarchy.manage');
    $canProbationManage = $user->hasPermission('probation.manage');
    $canAssetManage = $user->hasPermission('asset_allocations.manage');
    $canDirectoryView = $user->hasPermission('employees.directory.view');

    $showEmployeeMenu =
        $canEmployeeCreate ||
        $canEmployeeView ||
        $canPerformanceView ||
        $canDepartmentManage ||
        $canOrgHierarchyManage ||
        $canProbationManage ||
        $canAssetManage ||
        $canDirectoryView;

    $empOpen =
        request()->is('employees*') ||
        request()->routeIs('hrms.employees.*') ||
        request()->routeIs('hrms.employees.performance_scores.*') ||
        request()->routeIs('hrms.departments.*') ||
        request()->routeIs('hrms.designations.*') ||
        request()->routeIs('hrms.assets.*');
@endphp

@if($showEmployeeMenu)
<div class="sidebar-group {{ $empOpen ? 'open' : '' }}">
    <button
        type="button"
        class="sidebar-group-toggle {{ $empOpen ? '' : 'collapsed' }}"
        data-toggle="collapse"
        data-target="#employeeMenu"
        aria-expanded="{{ $empOpen ? 'true' : 'false' }}"
        aria-controls="employeeMenu"
    >
        <span class="menu-icon"><i class="fas fa-users"></i></span>
        <span class="menu-text flex-grow-1">Employee Management</span>
        <span class="group-chevron"><i class="fas fa-chevron-down"></i></span>
    </button>

    <div class="sidebar-submenu collapse {{ $empOpen ? 'show' : '' }}" id="employeeMenu" data-parent="#sidebarMenu">

        @if ($canEmployeeCreate)
            <a href="{{ route('hrms.employees.create') }}"
               class="sub-link {{ request()->routeIs('hrms.employees.create') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-user-plus"></i></span>
                <span class="sub-link-text">Employee Onboarding</span>
            </a>
        @endif

        @if ($canEmployeeView)
            <a href="{{ route('hrms.employees.index') }}"
               class="sub-link {{ request()->routeIs('hrms.employees.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-id-card"></i></span>
                <span class="sub-link-text">Profile Management</span>
            </a>
        @endif

        @if ($canPerformanceView)
            <a href="{{ route('hrms.employees.performance_scores.index') }}"
               class="sub-link {{ request()->routeIs('hrms.employees.performance_scores.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-chart-line"></i></span>
                <span class="sub-link-text">Performance Score</span>
            </a>
        @endif

        @if ($canDepartmentManage || $canOrgHierarchyManage || $canProbationManage || $canAssetManage)
            <div class="submenu-divider"></div>
        @endif

        @if ($canDepartmentManage)
            <a href="{{ route('hrms.departments.index') }}"
               class="sub-link {{ request()->routeIs('hrms.departments.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-building"></i></span>
                <span class="sub-link-text">Dept & Designation</span>
            </a>
        @endif

        @if ($canOrgHierarchyManage)
            <a href="{{ route('hrms.employees.index') }}"
               class="sub-link">
                <span class="sub-link-icon"><i class="fas fa-sitemap"></i></span>
                <span class="sub-link-text">Org Hierarchy</span>
            </a>
        @endif

        @if ($canProbationManage)
            <a href="{{ route('hrms.employees.index') }}"
               class="sub-link">
                <span class="sub-link-icon"><i class="fas fa-user-check"></i></span>
                <span class="sub-link-text">Probation & Confirm</span>
            </a>
        @endif

        @if ($canAssetManage)
            <a href="{{ route('hrms.assets.index') }}"
               class="sub-link {{ request()->routeIs('hrms.assets.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-laptop-code"></i></span>
                <span class="sub-link-text">Asset Allocation</span>
            </a>
        @endif

        @if ($canDirectoryView)
            <a href="{{ route('hrms.employees.index') }}"
               class="sub-link">
                <span class="sub-link-icon"><i class="fas fa-address-book"></i></span>
                <span class="sub-link-text">Employee Directory</span>
            </a>
        @endif

    </div>
</div>
@endif