@php
    $isAdmin = auth()->user()->isAdmin();
    $payrollOpen = request()->is('payroll*') || request()->routeIs('pages.payroll*') || request()->routeIs('hrms.payroll*');
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
        <a href="{{ route('pages.payroll.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('pages.payroll.index') ? 'active' : '' }}">
            <i class="fas fa-layer-group small mr-2"></i> Salary Structure
        </a>
    </li>

    {{-- Sub-module: Payroll Dashboard (Admin) --}}
    <li>
        <a href="{{ route('pages.payroll.dashboard') }}" class="nav-link sub-nav-link {{ request()->routeIs('pages.payroll.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie small mr-2"></i> Payroll Dashboard
        </a>
    </li>
    @endif

    {{-- Sub-module: Salary Slip / Payslips (Both) --}}
    <li>
        <a href="{{ route('pages.payroll.payslips') }}" class="nav-link sub-nav-link {{ request()->routeIs('pages.payroll.payslips') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar small mr-2 text-success"></i> My Salary Slips
        </a>
    </li>

    @if ($isAdmin)
    {{-- Sub-module: FNF (Admin) --}}
    <li>
        <a href="{{ route('pages.payroll.fnf') }}" class="nav-link sub-nav-link {{ request()->routeIs('pages.payroll.fnf') ? 'active' : '' }}">
            <i class="fas fa-walking small mr-2 text-danger"></i> Settlement (FNF)
        </a>
    </li>

    {{-- Sub-module: Bonus Management (Admin Placeholder) --}}
    <li>
        <a href="{{ route('hrms.payroll.adjustments.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('hrms.payroll.adjustments.*') ? 'active' : '' }}">
            <i class="fas fa-gift small mr-2 text-warning"></i> Bonus Management
        </a>
    </li>
    @endif
</ul>
