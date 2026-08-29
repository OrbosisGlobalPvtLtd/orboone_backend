@php
    $user = auth()->user();
    if (!$user) {
        return;
    }

    $isSuperAdmin = method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : false;
    $isAdminOrHr = (method_exists($user, 'isAdmin') ? $user->isAdmin() : false) || (method_exists($user, 'isHrAdmin') ? $user->isHrAdmin() : false);

    // Dynamic RBAC Permission Checks for Leave Management Sub-menus
    $canDashboard = $isSuperAdmin || $isAdminOrHr || $user->hasPermission('leave.dashboard.view');
    $canApprovals = $isSuperAdmin || $isAdminOrHr || $user->hasPermission('leave.approvals.view_all') || $user->hasPermission('leave.approvals.view_team') || $user->hasPermission('leave.approvals.view');
    $canLeaveRequests = $isSuperAdmin || $isAdminOrHr || $user->hasPermission('leave.my_requests.view') || $user->hasPermission('leave.approvals.view_all') || $user->hasPermission('leave.history.view');
    $canHistory = $isSuperAdmin || $isAdminOrHr || $user->hasPermission('leave.history.view') || $user->hasPermission('leave.my_requests.view');
    $canApply = $isSuperAdmin || $isAdminOrHr || $user->hasPermission('leave.my_requests.create') || $user->hasPermission('leave.my_requests.view') || $user->hasPermission('leave.apply') || $user->hasPermission('leave_self.apply');
    $canAllocations = $isSuperAdmin || $isAdminOrHr || $user->hasPermission('leave.allocation.manage') || $user->hasPermission('leave.allocation.view_all') || $user->hasPermission('leave.allocation.view');
    $canBalances = $isSuperAdmin || $isAdminOrHr || $user->hasPermission('leave.balance.view_all') || $user->hasPermission('leave.balance.view_team') || $user->hasPermission('leave.balance.view_own') || $user->hasPermission('leave.balance.view') || $user->hasPermission('leave_self.view_balance');
    $canHolidays = $isSuperAdmin || $isAdminOrHr || $user->hasPermission('leave.holidays.manage') || $user->hasPermission('leave.team_calendar.view');

    $hasAnyLeaveAccess = $canDashboard || $canApprovals || $canLeaveRequests || $canHistory || $canApply || $canAllocations || $canBalances || $canHolidays;
    $leaveOpen = request()->routeIs('hrms.leave.*') || request()->routeIs('leave-*') || request()->routeIs('employees-leave-request*');
@endphp

@if($hasAnyLeaveAccess)
{{-- ========== SECTION: 3. LEAVE MANAGEMENT ========== --}}
<a href="#leaveSubmenu" data-toggle="collapse" aria-expanded="{{ $leaveOpen ? 'true' : 'false' }}" 
   class="nav-link sidebar-collapse-btn {{ $leaveOpen ? '' : 'collapsed' }}">
    <i class="fas fa-calendar-alt mr-2"></i>
    <span class="flex-grow-1">3. Leave Management</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<ul class="collapse list-unstyled {{ $leaveOpen ? 'show' : '' }}" id="leaveSubmenu" data-parent="#sidebarMenu">
    {{-- 1. Leave Dashboard --}}
    @if($canDashboard)
    <li>
        <a href="{{ route('hrms.leave.dashboard') }}" class="nav-link sub-nav-link {{ request()->routeIs('hrms.leave.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie small mr-2 text-primary"></i> Leave Dashboard
        </a>
    </li>
    @endif

    {{-- 2. Leave Approvals --}}
    @if($canApprovals)
    <li>
        <a href="{{ route('leave-approvals.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('leave-approvals.index') ? 'active' : '' }}">
            <i class="fas fa-check-circle small mr-2 text-success"></i> Leave Approvals
        </a>
    </li>
    @endif

    {{-- 3. Leave Requests --}}
    @if($canLeaveRequests)
    <li>
        <a href="{{ route('leave-requests.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('leave-requests.index') ? 'active' : '' }}">
            <i class="fas fa-calendar-check small mr-2 text-primary"></i> Leave Requests
        </a>
    </li>
    @endif

    {{-- 4. Leave History --}}
    @if($canHistory)
    <li>
        <a href="{{ route('hrms.leave.history') }}" class="nav-link sub-nav-link {{ request()->routeIs('hrms.leave.history') ? 'active' : '' }}">
            <i class="fas fa-history small mr-2 text-info"></i> Leave History
        </a>
    </li>
    @endif

    {{-- 4. Apply for Leave --}}
    @if($canApply)
    <li>
        <a href="{{ route('leave-requests.create') }}" class="nav-link sub-nav-link {{ request()->routeIs('leave-requests.create') ? 'active' : '' }}">
            <i class="fas fa-paper-plane small mr-2 text-warning"></i> Apply for Leave
        </a>
    </li>
    @endif

    {{-- 5. Leave Allocations --}}
    @if($canAllocations)
    <li>
        <a href="{{ route('leave-allocations.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('leave-allocations.index') ? 'active' : '' }}">
            <i class="fas fa-coins small mr-2 text-warning"></i> Leave Allocation
        </a>
    </li>
    @endif

    {{-- 6. Leave Balance Tracker --}}
    @if($canBalances)
    <li>
        <a href="{{ route('employees-leave-request.summary') }}" class="nav-link sub-nav-link {{ request()->routeIs('employees-leave-request.summary') ? 'active' : '' }}">
            <i class="fas fa-wallet small mr-2 text-secondary"></i> Balance Tracker
        </a>
    </li>
    @endif

    {{-- 7. Holiday List --}}
    @if($canHolidays)
    <li>
        <a href="{{ route('hrms.holidays.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('hrms.holidays.index') ? 'active' : '' }}">
            <i class="fas fa-umbrella-beach small mr-2 text-danger"></i> Holiday List
        </a>
    </li>
    @endif
</ul>
@endif
