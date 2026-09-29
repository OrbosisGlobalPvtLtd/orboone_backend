@php
    $isAdmin = auth()->user()->isAdmin();
    $payrollOpen = request()->is('payroll*') || request()->routeIs('enterprise-payroll*') || request()->routeIs('enterprise-payroll*');
@endphp

{{-- ========== SECTION: 4. PAYROLL MANAGEMENT ========== --}}
<a href="#payrollSubmenu" data-toggle="collapse" aria-expanded="{{ $payrollOpen ? 'true' : 'false' }}" 
   class="nav-link sidebar-collapse-btn {{ $payrollOpen ? '' : 'collapsed' }}">
    <i class="fas fa-money-check-alt mr-2"></i>
    <span class="flex-grow-1">4. Payroll Management</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<ul class="collapse list-unstyled {{ $payrollOpen ? 'show' : '' }}" id="payrollSubmenu" data-parent="#sidebarMenu">
    
    @if ($isAdmin)
    {{-- Sub-module: Salary Structure (Admin) --}}
    <li>
        <a href="{{ route('enterprise-payroll.salary-structures.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('enterprise-payroll.salary-structures.index') ? 'active' : '' }}">
            <i class="fas fa-layer-group small mr-2"></i> Salary Structure
        </a>
    </li>

    {{-- Sub-module: Payroll Dashboard (Admin) --}}
    <li>
        <a href="{{ route('enterprise-payroll.dashboard') }}" class="nav-link sub-nav-link {{ request()->routeIs('enterprise-payroll.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie small mr-2"></i> Payroll Dashboard
        </a>
    </li>
    @endif

    {{-- Sub-module: Salary Slip / Payslips (Both) --}}
    <li>
        <a href="{{ route('enterprise-payroll.self.payslips') }}" class="nav-link sub-nav-link {{ request()->routeIs('enterprise-payroll.self.payslips') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar small mr-2 text-success"></i> My Salary Slips
        </a>
    </li>

    @if ($isAdmin)
    {{-- Sub-module: FNF (Admin) --}}
    <li>
        <a href="{{ route('enterprise-payroll.fnf.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('enterprise-payroll.fnf.index') ? 'active' : '' }}">
            <i class="fas fa-walking small mr-2 text-danger"></i> Settlement (FNF)
        </a>
    </li>

    {{-- Sub-module: Bonus Management (Admin Placeholder) --}}
    <li>
        <a href="{{ route('enterprise-payroll.bonus-incentives.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('enterprise-payroll.bonus-incentives.*') ? 'active' : '' }}">
            <i class="fas fa-gift small mr-2 text-warning"></i> Bonus Management
        </a>
    </li>

    <li>
        <a href="{{ route('enterprise-payroll.policies.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('enterprise-payroll.policies.*') ? 'active' : '' }}">
            <i class="fas fa-cogs small mr-2 text-primary"></i> Payroll Policy Settings
        </a>
    </li>
    @endif
</ul>
