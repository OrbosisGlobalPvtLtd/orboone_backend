@php
use Illuminate\Support\Facades\Route;

$menus = isset($menus) ? $menus : collect();
$active = isset($active) ? $active : '';

$isPermanentWfhUser = false;
if (auth()->check()) {
    $empWfhCheck = \Illuminate\Support\Facades\DB::table('employees_new')->where('user_id', auth()->id())->first(['work_mode']);
    if ($empWfhCheck && in_array(strtolower((string)($empWfhCheck->work_mode ?? 'wfo')), ['wfh', 'permanent_wfh', 'permanent wfh'], true)) {
        $isPermanentWfhUser = true;
    }
}

$parentMenus = $menus->get('') ?? $menus->get(null) ?? $menus->get(0) ?? $menus[null] ?? collect();

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
        if (str_starts_with($activeViewVar, 'reporting_') || str_starts_with($activeViewVar, 'team_')) {
            $cleanActive = str_replace(['reporting_', 'team_'], 'reporting.', $activeViewVar);
            if (($item->route ?? '') === $cleanActive) {
                return true;
            }
        }
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

$resolveRouteName = function (?string $routeName): ?string {
    if (empty($routeName)) {
        return null;
    }
    if (Route::has($routeName)) {
        return $routeName;
    }

    $variants = [
        str_replace('-', '_', $routeName),
        str_replace('_', '-', $routeName),
    ];

    foreach ($variants as $variant) {
        if ($variant && Route::has($variant)) {
            return $variant;
        }
    }

    return null;
};
@endphp

<style>
    .favicon-logo {
        display: none !important;
    }
    body.desktop-collapsed .full-logo {
        display: none !important;
    }
    body.desktop-collapsed .favicon-logo {
        display: block !important;
        max-height: 40px !important;
        max-width: 40px !important;
        margin: 0 auto;
        object-fit: contain;
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-logo-box">
                <!-- Full Brand Logo -->
                <img src="{{ $branding['logo_url'] ?? asset('images/Picsart_26-04-02_12-19-10-396.png') }}"
                    alt="{{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }}"
                    class="brand-logo full-logo">
                <!-- Favicon Logo (Shown on sidebar collapse) -->
                <img src="{{ $branding['favicon_url'] ?? asset('favicon.ico') }}"
                    alt="{{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }}"
                    class="brand-logo favicon-logo">
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
            $rMenu = strtolower((string)($menu->route ?? ''));
            $nMenu = strtolower((string)($menu->name ?? ''));
            if ($isPermanentWfhUser && (in_array($rMenu, ['hrms.attendance.my-wfh.index', 'attendances.my-wfh', 'attendance.my-wfh'], true) || str_contains($nMenu, 'my wfh'))) {
                continue;
            }

            $children = $menus->get($menu->id) ?? $menus->get((string)$menu->id) ?? $menus[$menu->id] ?? collect();
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
                    $rChild = strtolower((string)($child->route ?? ''));
                    $nChild = strtolower((string)($child->name ?? ''));
                    if ($isPermanentWfhUser && (in_array($rChild, ['hrms.attendance.my-wfh.index', 'attendances.my-wfh', 'attendance.my-wfh'], true) || str_contains($nChild, 'my wfh'))) {
                        continue;
                    }

                    $resolvedChildRoute = $resolveRouteName($child->route);
                    $childHasRoute = ! empty($resolvedChildRoute);
                    $childActive = $isMenuActive($child);
                    @endphp

                    <a href="{{ $childHasRoute ? route($resolvedChildRoute) : 'javascript:void(0)' }}"
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
            $resolvedMenuRoute = $resolveRouteName($menu->route);
            $hasRoute = ! empty($resolvedMenuRoute);
            $activeState = $isMenuActive($menu);
            @endphp

            <a href="{{ $hasRoute ? route($resolvedMenuRoute) : 'javascript:void(0)' }}"
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
        <div class="sidebar-footer-title">{{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }}</div>
        <div class="sidebar-footer-sub">v1.0 · {{ date('Y') }}</div>
    </div>
</aside>

<script>
(function() {
    function initSidebarAccordion() {
        if (window.__sidebarAccordionBound) return;
        window.__sidebarAccordionBound = true;

        document.addEventListener('click', function(event) {
            var toggle = event.target.closest('[data-sidebar-parent]');
            if (!toggle) return;

            event.preventDefault();

            var group = toggle.closest('.sidebar-group');
            if (!group) return;

            var targetSelector = toggle.getAttribute('data-target');
            var target = targetSelector ? document.querySelector(targetSelector) : group.querySelector('.sidebar-submenu');

            var isOpen = group.classList.contains('open');

            document.querySelectorAll('.sidebar-group.open').forEach(function(otherGroup) {
                if (otherGroup !== group) {
                    otherGroup.classList.remove('open');
                    var otherToggle = otherGroup.querySelector('[data-sidebar-parent]');
                    if (otherToggle) {
                        otherToggle.classList.add('collapsed');
                        otherToggle.setAttribute('aria-expanded', 'false');
                    }
                    var otherSubmenu = otherGroup.querySelector('.sidebar-submenu');
                    if (otherSubmenu) {
                        otherSubmenu.classList.remove('show');
                    }
                }
            });

            if (isOpen) {
                group.classList.remove('open');
                toggle.classList.add('collapsed');
                toggle.setAttribute('aria-expanded', 'false');
                if (target) target.classList.remove('show');
            } else {
                group.classList.add('open');
                toggle.classList.remove('collapsed');
                toggle.setAttribute('aria-expanded', 'true');
                if (target) target.classList.add('show');
            }
        });

        document.querySelectorAll('[data-sidebar-empty-link]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarAccordion);
    } else {
        initSidebarAccordion();
    }
})();
</script>
