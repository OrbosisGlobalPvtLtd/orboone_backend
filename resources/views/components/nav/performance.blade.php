@php
    $isAdmin = auth()->user()->isAdmin();
    $empOpen = request()->is('employees*') || request()->routeIs('hrms.employees.*') || request()->routeIs('hrms.employees.performance_scores.*')
               || request()->routeIs('hrms.departments.*') || request()->routeIs('hrms.designations.*') || request()->routeIs('hrms.assets.*');
@endphp

{{-- ========== SECTION: 1. EMPLOYEE MANAGEMENT MODULE ========== --}}
<a class="nav-link sidebar-collapse-btn {{ $empOpen ? '' : 'collapsed' }}"
   data-toggle="collapse" href="#employeeMenu" role="button"
   aria-expanded="{{ $empOpen ? 'true' : 'false' }}" aria-controls="employeeMenu">
    <i class="fas fa-users-viewfinder mr-2"></i>
    <span class="flex-grow-1">1. Employee Management</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<div class="collapse {{ $empOpen ? 'show' : '' }}" id="employeeMenu" data-parent="#sidebarMenu">

    {{-- Sub-module: Employee Onboarding (Admin Only) --}}
    @if ($isAdmin)
    <a href="{{ route('hrms.employees.create') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('hrms.employees.create') ? 'active' : '' }}">
        <i class="fas fa-user-plus small mr-2"></i>
        Employee Onboarding
    </a>
    @endif

    {{-- Sub-module: Employee Profile Management (Both) --}}
    <a href="{{ route('hrms.employees.index') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('hrms.employees.index') ? 'active' : '' }}">
        <i class="fas fa-id-card small mr-2"></i>
        Profile Management
    </a>

    {{-- Sub-module: Employee Performance --}}
    <a href="{{ route('hrms.employees.performance_scores.index') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('hrms.employees.performance_scores.index') ? 'active' : '' }}">
        <i class="fas fa-chart-line small mr-2"></i>
        Performance Score
    </a>

    @if ($isAdmin)
    <div class="border-top mx-3 my-2" style="border-color:rgba(255,255,255,0.1) !important;"></div>

    {{-- Sub-module: Department & Designation (Admin Only) --}}
    <a href="{{ route('hrms.departments.index') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('hrms.departments.index') ? 'active' : '' }}">
        <i class="fa-solid fa-building small mr-2"></i>
        Dept & Designation
    </a>

    {{-- Sub-module: Organizational Hierarchy (Placeholder/Dir) --}}
    <a href="{{ route('hrms.employees.index') }}"
       class="nav-link sub-nav-link">
        <i class="fas fa-sitemap small mr-2"></i>
        Org Hierarchy
    </a>

    {{-- Sub-module: Probation & Confirmation (Placeholder) --}}
    <a href="{{ route('hrms.employees.index') }}"
       class="nav-link sub-nav-link">
        <i class="fas fa-user-check small mr-2"></i>
        Probation & Confirm
    </a>

    {{-- Sub-module: Asset Allocation (Admin Only) --}}
    <a href="{{ route('hrms.assets.index') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('hrms.assets.index') ? 'active' : '' }}">
        <i class="fa-solid fa-laptop-code small mr-2"></i>
        Asset Allocation
    </a>
    @endif

    {{-- Sub-module: Employee Directory (Both) --}}
    <a href="{{ route('hrms.employees.index') }}"
       class="nav-link sub-nav-link">
        <i class="fas fa-address-book small mr-2"></i>
        Employee Directory
    </a>

</div>