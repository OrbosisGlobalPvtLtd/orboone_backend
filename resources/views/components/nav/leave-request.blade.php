@php
    $isAdmin = auth()->user()->isAdmin();
    $leaveOpen = request()->routeIs('leave-*') || request()->routeIs('employees-leave-request*');
@endphp

{{-- ========== SECTION: 3. LEAVE MANAGEMENT ========== --}}
<a href="#leaveSubmenu" data-toggle="collapse" aria-expanded="{{ $leaveOpen ? 'true' : 'false' }}" 
   class="nav-link sidebar-collapse-btn {{ $leaveOpen ? '' : 'collapsed' }}">
    <i class="fas fa-calendar-alt mr-2"></i>
    <span class="flex-grow-1">3. Leave Management</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<ul class="collapse list-unstyled {{ $leaveOpen ? 'show' : '' }}" id="leaveSubmenu" data-parent="#sidebarMenu">
    
    {{-- Sub-module: Leave Allocation (Admin Only) --}}
    @if ($isAdmin)
    <li>
        <a href="{{ route('leave-allocations.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('leave-allocations.index') ? 'active' : '' }}">
            <i class="fas fa-coins small mr-2 text-warning"></i> Leave Allocation (HR)
        </a>
    </li>
    @endif

    {{-- Sub-module: Leave Application (Employee/Both) --}}
    <li>
        <a href="{{ route('leave-requests.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('leave-requests.index') ? 'active' : '' }}">
            <i class="fas fa-paper-plane small mr-2"></i> Apply for Leave
        </a>
    </li>

    {{-- Sub-module: Leave Approval (Admin Only) --}}
    @if ($isAdmin)
    <li>
        <a href="{{ route('leave-approvals.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('leave-approvals.index') ? 'active' : '' }}">
            <i class="fas fa-check-double small mr-2 text-success"></i> Leave Approvals
        </a>
    </li>
    @endif

    {{-- Sub-module: Leave Balance Tracker (Both) --}}
    <li>
        <a href="{{ route('employees-leave-request.summary') }}" class="nav-link sub-nav-link {{ request()->routeIs('employees-leave-request.summary') ? 'active' : '' }}">
            <i class="fas fa-history small mr-2 text-info"></i> Balance Tracker
        </a>
    </li>

    {{-- Sub-module: National Holiday List (Both) --}}
    <li>
        <a href="{{ route('dashboard') }}" class="nav-link sub-nav-link">
            <i class="fas fa-glass-cheers small mr-2 text-danger"></i> Holiday List
        </a>
    </li>
</ul>