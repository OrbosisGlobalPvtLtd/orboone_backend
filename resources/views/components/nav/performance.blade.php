@php
    $isAdmin = auth()->user()->isAdmin();
    $empOpen = request()->is('employees*') || request()->routeIs('employees-data*') || request()->routeIs('employees-performance-score*')
               || request()->routeIs('departments-data*') || request()->routeIs('positions-data*') || request()->routeIs('asset-allocations*');
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
    <a href="{{ route('employees-data.create') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('employees-data.create') ? 'active' : '' }}">
        <i class="fas fa-user-plus small mr-2"></i>
        Employee Onboarding
    </a>
    @endif

    {{-- Sub-module: Employee Profile Management (Both) --}}
    <a href="{{ route('employees-data') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('employees-data') ? 'active' : '' }}">
        <i class="fas fa-id-card small mr-2"></i>
        Profile Management
    </a>

    {{-- Sub-module: Employee Performance --}}
    <a href="{{ route('employees-performance-score') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('employees-performance-score') ? 'active' : '' }}">
        <i class="fas fa-chart-line small mr-2"></i>
        Performance Score
    </a>

    @if ($isAdmin)
    <div class="border-top mx-3 my-2" style="border-color:rgba(255,255,255,0.1) !important;"></div>

    {{-- Sub-module: Department & Designation (Admin Only) --}}
    <a href="{{ route('departments-data') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('departments-data') ? 'active' : '' }}">
        <i class="fa-solid fa-building small mr-2"></i>
        Dept & Designation
    </a>

    {{-- Sub-module: Organizational Hierarchy (Placeholder/Dir) --}}
    <a href="{{ route('employees-data') }}"
       class="nav-link sub-nav-link">
        <i class="fas fa-sitemap small mr-2"></i>
        Org Hierarchy
    </a>

    {{-- Sub-module: Probation & Confirmation (Placeholder) --}}
    <a href="{{ route('employees-data') }}"
       class="nav-link sub-nav-link">
        <i class="fas fa-user-check small mr-2"></i>
        Probation & Confirm
    </a>

    {{-- Sub-module: Asset Allocation (Admin Only) --}}
    <a href="{{ route('asset-allocations.index') }}"
       class="nav-link sub-nav-link {{ request()->routeIs('asset-allocations.index') ? 'active' : '' }}">
        <i class="fa-solid fa-laptop-code small mr-2"></i>
        Asset Allocation
    </a>
    @endif

    {{-- Sub-module: Employee Directory (Both) --}}
    <a href="{{ route('employees-data') }}"
       class="nav-link sub-nav-link">
        <i class="fas fa-address-book small mr-2"></i>
        Employee Directory
    </a>

</div>