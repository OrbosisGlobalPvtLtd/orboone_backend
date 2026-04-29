@php
    $user = auth()->user();

    $canLeaveAllocation = $user->hasPermission('leave.allocation.manage');
    $canLeaveApply = $user->hasPermission('leave.apply');
    $canLeaveApproval = $user->hasPermission('leave.approve');
    $canLeaveBalance = $user->hasPermission('leave.balance.view');
    $canHolidayManage = $user->hasPermission('holiday.manage');

    $showLeaveMenu =
        $canLeaveAllocation ||
        $canLeaveApply ||
        $canLeaveApproval ||
        $canLeaveBalance ||
        $canHolidayManage;

    $leaveOpen =
        request()->routeIs('leave-*') ||
        request()->routeIs('employees-leave-request*');
@endphp

@if($showLeaveMenu)
<div class="sidebar-group {{ $leaveOpen ? 'open' : '' }}">
    <button
        type="button"
        class="sidebar-group-toggle {{ $leaveOpen ? '' : 'collapsed' }}"
        data-toggle="collapse"
        data-target="#leaveSubmenu"
        aria-expanded="{{ $leaveOpen ? 'true' : 'false' }}"
        aria-controls="leaveSubmenu"
    >
        <span class="menu-icon"><i class="fas fa-calendar-alt"></i></span>
        <span class="menu-text flex-grow-1">Leave Management</span>
        <span class="group-chevron"><i class="fas fa-chevron-down"></i></span>
    </button>

    <div class="sidebar-submenu collapse {{ $leaveOpen ? 'show' : '' }}" id="leaveSubmenu" data-parent="#sidebarMenu">

        @if ($canLeaveAllocation)
            <a href="{{ route('leave-allocations.index') }}"
               class="sub-link {{ request()->routeIs('leave-allocations.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-coins"></i></span>
                <span class="sub-link-text">Leave Allocation</span>
            </a>
        @endif

        @if ($canLeaveApply)
            <a href="{{ route('leave-requests.index') }}"
               class="sub-link {{ request()->routeIs('leave-requests.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-paper-plane"></i></span>
                <span class="sub-link-text">Apply for Leave</span>
            </a>
        @endif

        @if ($canLeaveApproval)
            <a href="{{ route('leave-approvals.index') }}"
               class="sub-link {{ request()->routeIs('leave-approvals.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-check-double"></i></span>
                <span class="sub-link-text">Leave Approvals</span>
            </a>
        @endif

        @if ($canLeaveBalance)
            <a href="{{ route('employees-leave-request.summary') }}"
               class="sub-link {{ request()->routeIs('employees-leave-request.summary') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-history"></i></span>
                <span class="sub-link-text">Balance Tracker</span>
            </a>
        @endif

        @if ($canHolidayManage)
            <a href="{{ route('dashboard') }}"
               class="sub-link">
                <span class="sub-link-icon"><i class="fas fa-glass-cheers"></i></span>
                <span class="sub-link-text">Holiday List</span>
            </a>
        @endif

    </div>
</div>
@endif