@php
    $user = auth()->user();

    $canSalaryStructure = $user->hasPermission('payroll.structure.manage');
    $canPayrollDashboard = $user->hasPermission('payroll.dashboard.view');
    $canPayslipView = $user->hasPermission('payroll.payslip.view');
    $canFnfManage = $user->hasPermission('payroll.fnf.manage');
    $canBonusManage = $user->hasPermission('payroll.bonus.manage');
    $canAdjustmentManage = $user->hasPermission('payroll.adjustments.manage');

    $showPayrollMenu =
        $canSalaryStructure ||
        $canPayrollDashboard ||
        $canPayslipView ||
        $canFnfManage ||
        $canBonusManage ||
        $canAdjustmentManage;

    $payrollOpen =
        request()->is('payroll*') ||
        request()->routeIs('enterprise-payroll*') ||
        request()->routeIs('enterprise-payroll*');
@endphp

@if($showPayrollMenu)
<div class="sidebar-group {{ $payrollOpen ? 'open' : '' }}">
    <button
        type="button"
        class="sidebar-group-toggle {{ $payrollOpen ? '' : 'collapsed' }}"
        data-toggle="collapse"
        data-target="#payrollSubmenu"
        aria-expanded="{{ $payrollOpen ? 'true' : 'false' }}"
        aria-controls="payrollSubmenu"
    >
        <span class="menu-icon"><i class="fas fa-money-check-alt"></i></span>
        <span class="menu-text flex-grow-1">Payroll Management</span>
        <span class="group-chevron"><i class="fas fa-chevron-down"></i></span>
    </button>

    <div class="sidebar-submenu collapse {{ $payrollOpen ? 'show' : '' }}" id="payrollSubmenu" data-parent="#sidebarMenu">

        @if ($canSalaryStructure)
            <a href="{{ route('enterprise-payroll.salary-structures.index') }}"
               class="sub-link {{ request()->routeIs('enterprise-payroll.salary-structures.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-layer-group"></i></span>
                <span class="sub-link-text">Salary Structure</span>
            </a>
        @endif

        @if ($canPayrollDashboard)
            <a href="{{ route('enterprise-payroll.dashboard') }}"
               class="sub-link {{ request()->routeIs('enterprise-payroll.dashboard') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-chart-pie"></i></span>
                <span class="sub-link-text">Payroll Dashboard</span>
            </a>
        @endif

        @if ($canPayslipView)
            <a href="{{ route('enterprise-payroll.self.payslips') }}"
               class="sub-link {{ request()->routeIs('enterprise-payroll.self.payslips') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <span class="sub-link-text">My Salary Slips</span>
            </a>
        @endif

        @if ($canFnfManage || $canBonusManage)
            <div class="submenu-divider"></div>
        @endif

        @if ($canFnfManage)
            <a href="{{ route('enterprise-payroll.fnf.index') }}"
               class="sub-link {{ request()->routeIs('enterprise-payroll.fnf.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-walking"></i></span>
                <span class="sub-link-text">Settlement (FNF)</span>
            </a>
        @endif

        @if ($canBonusManage || $canAdjustmentManage)
            <a href="{{ route('enterprise-payroll.bonus-incentives.index') }}"
               class="sub-link {{ request()->routeIs('enterprise-payroll.bonus-incentives.*') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-gift"></i></span>
                <span class="sub-link-text">Bonus Management</span>
            </a>
        @endif

    </div>
</div>
@endif
