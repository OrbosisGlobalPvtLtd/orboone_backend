@php
use Illuminate\Support\Facades\Route;

$menus = isset($menus) ? $menus : collect();
$active = isset($active) ? $active : '';

$isEmployeeRole = auth()->check() && optional(auth()->user()->role)->slug === 'employee';

$employeeOnlyMenus = [
    'my documents',
    'my generated documents',
    'my attendance',
    'my leave requests',
    'my payslips',
    'my salary slips',
];

$isEmployeeOnly = function($menu) use ($employeeOnlyMenus) {
    $name = strtolower(trim($menu->name ?? ''));
    $slug = strtolower(trim($menu->slug ?? ''));

    if (in_array($name, $employeeOnlyMenus, true) || in_array($slug, $employeeOnlyMenus, true)) {
        return true;
    }

    $prefixes = [
        'employee.self', 'employee.profile', 'employee.documents', 
        'employee.attendance', 'employee.leave', 'employee.salary', 
        'employee.announcements', 'my.profile', 'my.documents', 
        'my.attendance', 'my.leave'
    ];

    if (!empty($menu->module_key)) {
        foreach ($prefixes as $prefix) {
            if (str_starts_with(strtolower($menu->module_key), $prefix)) return true;
        }
    }

    if (!empty($menu->permission_key)) {
        foreach ($prefixes as $prefix) {
            if (str_starts_with(strtolower($menu->permission_key), $prefix)) return true;
        }
    }

    if (!empty($menu->route)) {
        foreach ($prefixes as $prefix) {
            if (str_starts_with(strtolower($menu->route), $prefix)) return true;
        }
    }

    if (!empty($menu->url)) {
        foreach ($prefixes as $prefix) {
            if (str_starts_with(strtolower($menu->url), $prefix)) return true;
        }
    }

    return false;
};

if (empty($isEmployeeUser) || !$isEmployeeRole) {
    $menus = $menus->map(function($children) use ($isEmployeeOnly) {
        return collect($children)->reject(function($menu) use ($isEmployeeOnly) {
            return $isEmployeeOnly($menu);
        });
    });
}

$parentMenus = $menus[null] ?? collect();

if (empty($isEmployeeUser) || !$isEmployeeRole) {
    $parentMenus = $parentMenus->reject(function($menu) use ($isEmployeeOnly, $menus) {
        if ($isEmployeeOnly($menu)) return true;
        // If parent has no route, and all children were filtered out, hide parent
        if (empty($menu->route)) {
            $children = $menus[$menu->id] ?? collect();
            if ($children->isEmpty()) return true;
        }
        return false;
    });
}

$isMenuActive = function($item) use ($active) {
    $activeViewVar = $active ?? '';
    
    // Check if route matches directly or via wildcard
    if (!empty($item->route) && Route::has($item->route)) {
        if (request()->routeIs($item->route)) {
            return true;
        }
        
        // Handle document generation routes wildcards
        if ($item->route === 'hrms.document-generation.dashboard') {
            if (request()->routeIs('hrms.document-generation.*') && !request()->routeIs('hrms.document-generation.self.*')) {
                return true;
            }
        }
        if ($item->route === 'hrms.document-generation.self.index') {
            if (request()->routeIs('hrms.document-generation.self.*')) {
                return true;
            }
        }
    }
    
    // Check via active layout variable
    if (!empty($activeViewVar)) {
        if ($activeViewVar === 'document_generation' && ($item->module_key === 'document_generation' || $item->route === 'hrms.document-generation.dashboard')) {
            return true;
        }
        if ($activeViewVar === 'my_documents' && ($item->route === 'hrms.document-generation.self.index' || (strtolower($item->name ?? '') === 'my documents' && $item->module_key === 'employee.documents'))) {
            return true;
        }
    }
    
    return false;
};

$currentRoute = optional(request()->route())->getName() ?? '';
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
            $name = strtolower(trim($menu->name ?? ''));
            $slug = strtolower(trim($menu->slug ?? ''));

            $isEmployeeOnlyMenu = in_array($name, $employeeOnlyMenus, true)
                || in_array($slug, $employeeOnlyMenus, true);

            if ($isEmployeeOnlyMenu && !$isEmployeeRole) {
                continue;
            }

            $children = $menus[$menu->id] ?? collect();
            $hasChildren = $children->count() > 0;
            $isParentMenu = $hasChildren || empty($menu->route);

            $isOpen = false;

            if ($hasChildren) {
            foreach ($children as $child) {
            if ($isMenuActive($child)) {
            $isOpen = true;
            break;
            }
            }
            } else {
            $isOpen = $isMenuActive($menu);
            }
            @endphp

            @if($isParentMenu)
            <div class="sidebar-group {{ $isOpen ? 'open' : '' }}">
                <a
                    href="javascript:void(0)"
                    role="button"
                    class="sidebar-group-toggle {{ $isOpen ? '' : 'collapsed' }}"
                    data-sidebar-parent
                    data-target="#menu{{ $menu->id }}"
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    aria-controls="menu{{ $menu->id }}">
                    <span class="menu-icon"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></span>
                    <span class="menu-text flex-grow-1">{{ $menu->name }}</span>
                    <span class="group-chevron"><i class="fas fa-chevron-down"></i></span>
                </a>

                <div class="sidebar-submenu collapse {{ $isOpen ? 'show' : '' }}"
                    id="menu{{ $menu->id }}"
                    data-parent="#sidebarMenu">
                    @forelse($children as $child)
                    @php
                    $childName = strtolower(trim($child->name ?? ''));
                    $childSlug = strtolower(trim($child->slug ?? ''));

                    $childIsEmployeeOnly = in_array($childName, $employeeOnlyMenus, true)
                        || in_array($childSlug, $employeeOnlyMenus, true);

                    if ($childIsEmployeeOnly && !$isEmployeeRole) {
                        continue;
                    }

                    $childHasRoute = $child->route && Route::has($child->route);
                    $childActive = $isMenuActive($child);
                    @endphp

                    <a href="{{ $childHasRoute ? route($child->route) : 'javascript:void(0)' }}"
                        class="sub-link {{ $childActive ? 'active' : '' }}"
                        @if(! $childHasRoute) data-sidebar-empty-link @endif>
                        <span class="sub-link-icon"><i class="{{ $child->icon ?? 'fas fa-circle' }}"></i></span>
                        <span class="sub-link-text">{{ $child->name }}</span>
                    </a>
                    @empty
                    <div class="sub-link text-muted" data-sidebar-empty-link>
                        <span class="sub-link-icon"><i class="fas fa-circle-info"></i></span>
                        <span class="sub-link-text">No submenu available</span>
                    </div>
                    @endforelse
                </div>
            </div>
            @else
            @php
            $hasRoute = $menu->route && Route::has($menu->route);
            $activeState = $isMenuActive($menu);
            @endphp

            <a href="{{ $hasRoute ? route($menu->route) : 'javascript:void(0)' }}"
                class="{{ $activeState ? 'active' : '' }}"
                @if(! $hasRoute) data-sidebar-empty-link @endif>
                <span class="menu-icon"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></span>
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
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-sidebar-empty-link]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
            });
        });

        document.querySelectorAll('[data-sidebar-parent]').forEach(function(toggle) {
            toggle.addEventListener('click', function(event) {
                event.preventDefault();

                var targetSelector = toggle.getAttribute('data-target');
                var target = targetSelector ? document.querySelector(targetSelector) : null;
                var group = toggle.closest('.sidebar-group');

                if (!target || !group) {
                    return;
                }

                var isOpen = target.classList.contains('show');

                document.querySelectorAll('#sidebarMenu .sidebar-submenu.show').forEach(function(submenu) {
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
    });
</script>