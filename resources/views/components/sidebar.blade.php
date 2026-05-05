<!-- @php
    $user = auth()->user();

    $isHrms = !request()->is('module/crm*') 
        && !request()->is('module/project-mgmt*') 
        && !request()->is('module/finance*');

    // 🔐 MODULE LEVEL CONTROL
    $canEmployeeModule = $user->hasModuleAccess('employees');
    $canAttendanceModule = $user->hasModuleAccess('attendance');
    $canLeaveModule = $user->hasModuleAccess('leave');
    $canPayrollModule = $user->hasModuleAccess('payroll');
    $canDocumentModule = $user->hasModuleAccess('documents');
    $canAnnouncementModule = $user->hasModuleAccess('announcements');
@endphp

<aside class="sidebar" id="sidebar">

    {{-- ================= HEADER ================= --}}
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-logo-box">
                <img src="{{ asset('images/Picsart_26-04-02_12-19-10-396.png') }}" 
                     alt="Orbosis HRMS" 
                     class="brand-logo">
            </div>
        </div>

        <button type="button" class="sidebar-close" onclick="closeSidebar()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- ================= BODY ================= --}}
    <div class="sidebar-body">

        {{-- MODULE SWITCH --}}
        <div class="menu-label">Modules</div>

        <div class="module-switcher">
            <a href="{{ route('dashboard') }}"
               class="module-switch-item {{ $isHrms ? 'active hrms' : '' }}">
                <span class="module-switch-icon"><i class="fas fa-users-cog"></i></span>
                <span class="module-switch-text">HRMS</span>
            </a>

            <a href="{{ route('module.crm') }}"
               class="module-switch-item {{ request()->is('module/crm*') ? 'active crm' : '' }}">
                <span class="module-switch-icon"><i class="fas fa-handshake"></i></span>
                <span class="module-switch-text">CRM</span>
            </a>

            <a href="{{ route('module.project-mgmt') }}"
               class="module-switch-item {{ request()->is('module/project-mgmt*') ? 'active pm' : '' }}">
                <span class="module-switch-icon"><i class="fas fa-project-diagram"></i></span>
                <span class="module-switch-text">Projects</span>
            </a>

            <a href="{{ route('module.finance') }}"
               class="module-switch-item {{ request()->is('module/finance*') ? 'active fin' : '' }}">
                <span class="module-switch-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <span class="module-switch-text">Finance</span>
            </a>
        </div>

        {{-- ================= NAV ================= --}}
        <div class="menu-label mt-3">
            {{ $isHrms ? 'Main Menu' : 'Module Overview' }}
        </div>

        <nav class="menu" id="sidebarMenu">

            @if ($isHrms)

                {{-- EMPLOYEE --}}
                @if($canEmployeeModule)
                <div class="menu-block">
                    @include('components.hrms.employee-management')
                </div>
                @endif

                {{-- ATTENDANCE --}}
                @if($canAttendanceModule)
                <div class="menu-block">
                    @include('components.hrms.attendances')
                </div>
                @endif

                {{-- LEAVE --}}
                @if($canLeaveModule)
                <div class="menu-block">
                    @include('components.hrms.leave-management')
                </div>
                @endif

                {{-- PAYROLL --}}
                @if($canPayrollModule)
                <div class="menu-block">
                    @include('components.hrms.payroll')
                </div>
                @endif

                {{-- DOCUMENTS --}}
                @if($canDocumentModule)
                <div class="menu-block">
                    @include('components.hrms.documents')
                </div>
                @endif

                {{-- ANNOUNCEMENTS --}}
                @if($canAnnouncementModule)
                <div class="menu-block">
                    @include('components.hrms.announcements')
                </div>
                @endif

            @else
                {{-- FUTURE MODULES (same as before) --}}
                <a href="#">
                    <span class="menu-icon"><i class="fas fa-rocket"></i></span>
                    <span class="menu-text">Coming Soon</span>
                </a>
            @endif

        </nav>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="sidebar-footer">
        <div class="sidebar-footer-title">Orbosis Business Suite</div>
        <div class="sidebar-footer-sub">v1.0 · {{ date('Y') }}</div>
    </div>

</aside> -->


@php
    use Illuminate\Support\Facades\Route;

    $parentMenus = $menus[null] ?? collect();
@endphp

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-logo-box">
                <img src="{{ asset('images/Picsart_26-04-02_12-19-10-396.png') }}"
                     alt="Orbosis HRMS"
                     class="brand-logo">
            </div>
        </div>

        <button type="button" class="sidebar-close" onclick="closeSidebar()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="sidebar-body">
        <div class="menu-label">Main Menu</div>

        <nav class="menu" id="sidebarMenu">
            @forelse($parentMenus as $menu)
                @php
                    $children = $menus[$menu->id] ?? collect();
                    $hasChildren = $children->count() > 0;
                    $isParentMenu = $hasChildren || empty($menu->route);

                    $isOpen = false;

                    if ($hasChildren) {
                        foreach ($children as $child) {
                            if ($child->route && request()->routeIs($child->route)) {
                                $isOpen = true;
                                break;
                            }
                        }
                    } else {
                        $isOpen = $menu->route ? request()->routeIs($menu->route) : false;
                    }
                @endphp

                @if($isParentMenu)
                    <div class="sidebar-group {{ $isOpen ? 'open' : '' }}">
                        <a
                            href="#"
                            role="button"
                            class="sidebar-group-toggle {{ $isOpen ? '' : 'collapsed' }}"
                            data-sidebar-parent
                            data-toggle="collapse"
                            data-target="#menu{{ $menu->id }}"
                            aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                            aria-controls="menu{{ $menu->id }}"
                        >
                            <span class="menu-icon"><i class="{{ $menu->icon }}"></i></span>
                            <span class="menu-text flex-grow-1">{{ $menu->name }}</span>
                            <span class="group-chevron"><i class="fas fa-chevron-down"></i></span>
                        </a>

                        <div class="sidebar-submenu collapse {{ $isOpen ? 'show' : '' }}"
                             id="menu{{ $menu->id }}"
                             data-parent="#sidebarMenu">
                            @foreach($children as $child)
                                @php
                                    $childHasRoute = $child->route && Route::has($child->route);
                                @endphp
                                <a href="{{ $childHasRoute ? route($child->route) : '#' }}"
                                   class="sub-link {{ $child->route && request()->routeIs($child->route) ? 'active' : '' }}"
                                   @if(! $childHasRoute) data-sidebar-empty-link @endif>
                                    <span class="sub-link-icon"><i class="{{ $child->icon }}"></i></span>
                                    <span class="sub-link-text">{{ $child->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $menu->route && Route::has($menu->route) ? route($menu->route) : 'javascript:void(0)' }}"
                       class="{{ $menu->route && request()->routeIs($menu->route) ? 'active' : '' }}">
                        <span class="menu-icon"><i class="{{ $menu->icon }}"></i></span>
                        <span class="menu-text flex-grow-1">{{ $menu->name }}</span>
                    </a>
                @endif
            @empty
                <div class="empty-sidebar-state text-center py-3 text-muted">
                    No menu available
                </div>
            @endforelse
        </nav>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-footer-title">Orbosis Business Suite</div>
        <div class="sidebar-footer-sub">v1.0 · {{ date('Y') }}</div>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-sidebar-parent], [data-sidebar-empty-link]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
            });
        });

        var hasBootstrapCollapse = window.jQuery && window.jQuery.fn && window.jQuery.fn.collapse;

        if (!hasBootstrapCollapse) {
            document.querySelectorAll('[data-sidebar-parent]').forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    var targetSelector = toggle.getAttribute('data-target');
                    var target = targetSelector ? document.querySelector(targetSelector) : null;
                    var group = toggle.closest('.sidebar-group');

                    if (!target || !group) {
                        return;
                    }

                    var isOpen = target.classList.contains('show');

                    document.querySelectorAll('#sidebarMenu .sidebar-submenu.show').forEach(function (submenu) {
                        if (submenu !== target) {
                            submenu.classList.remove('show');
                            var submenuGroup = submenu.closest('.sidebar-group');
                            var submenuToggle = submenuGroup ? submenuGroup.querySelector('[data-sidebar-parent]') : null;

                            if (submenuGroup) {
                                submenuGroup.classList.remove('open');
                            }

                            if (submenuToggle) {
                                submenuToggle.classList.add('collapsed');
                                submenuToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });

                    target.classList.toggle('show', !isOpen);
                    group.classList.toggle('open', !isOpen);
                    toggle.classList.toggle('collapsed', isOpen);
                    toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                });
            });
        }

        document.querySelectorAll('#sidebarMenu .sidebar-submenu').forEach(function (submenu) {
            submenu.addEventListener('shown.bs.collapse', function () {
                var group = submenu.closest('.sidebar-group');
                var toggle = group ? group.querySelector('[data-sidebar-parent]') : null;

                if (group) {
                    group.classList.add('open');
                }

                if (toggle) {
                    toggle.classList.remove('collapsed');
                    toggle.setAttribute('aria-expanded', 'true');
                }
            });

            submenu.addEventListener('hidden.bs.collapse', function () {
                var group = submenu.closest('.sidebar-group');
                var toggle = group ? group.querySelector('[data-sidebar-parent]') : null;

                if (group) {
                    group.classList.remove('open');
                }

                if (toggle) {
                    toggle.classList.add('collapsed');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    });
</script>
