@php
use Illuminate\Support\Facades\Route;

$menus = isset($menus) ? (is_array($menus) ? collect($menus) : $menus) : collect();
$active = isset($active) ? $active : '';

$parentMenus = $menus->get('') ?? $menus->get(null) ?? $menus->get(0) ?? $menus[null] ?? collect();

$isMenuActive = function($item) use ($active) {
    $activeViewVar = $active ?? '';

    if (!empty($item->route) && $item->route !== '#') {
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
@endphp

<style>
    .brand-logo.full-logo {
        max-height: 38px !important;
        max-width: 140px !important;
        object-fit: contain;
        display: block;
        margin: 0 auto;
    }
    .favicon-logo {
        display: none !important;
    }
    body.desktop-collapsed .full-logo {
        display: none !important;
    }
    body.desktop-collapsed .favicon-logo {
        display: block !important;
        max-height: 32px !important;
        max-width: 32px !important;
        margin: 0 auto;
        object-fit: contain;
    }
    .sidebar {
        width: var(--sidebar-width);
        max-width: 100vw;
    }
    .sidebar-header {
        height: 60px !important;
        padding: 0 16px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #ffffff !important;
        border-bottom: 1px solid #eef1f7 !important;
        position: relative !important;
        box-sizing: border-box !important;
        flex-shrink: 0 !important;
    }
    .brand {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        min-width: 0 !important;
    }
    .brand-logo-box {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 0 !important;
    }
    .sidebar-close {
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        border-radius: 8px !important;
        border: none !important;
        background: transparent !important;
        color: #64748b !important;
        display: none !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        font-size: 16px !important;
        padding: 0 !important;
        flex-shrink: 0 !important;
        transition: all .2s ease !important;
        z-index: 10 !important;
    }
    .sidebar-close:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }
    .sidebar-body {
        flex: 1 1 auto !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding: 8px 8px 12px !important;
        -webkit-overflow-scrolling: touch;
    }
    .sidebar-body::-webkit-scrollbar {
        width: 5px;
    }
    .sidebar-body::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
        border-radius: 999px;
    }
    .menu-label {
        padding: 6px 8px 6px !important;
        margin: 0 0 4px 0 !important;
        font-size: 10px !important;
        text-transform: uppercase !important;
        letter-spacing: .12em !important;
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 800 !important;
    }
    .sidebar a,
    .sidebar a:hover,
    .sidebar a:focus,
    .sidebar a:active,
    .menu > a,
    .menu > a:hover,
    .menu > a:focus,
    .menu > a:active,
    .sidebar-group-toggle,
    .sidebar-group-toggle:hover,
    .sidebar-group-toggle:focus,
    .sidebar-group-toggle:active,
    .sub-link,
    .sub-link:hover,
    .sub-link:focus,
    .sub-link:active {
        text-decoration: none !important;
    }
    .menu {
        display: flex !important;
        flex-direction: column !important;
        gap: 3px !important;
    }
    .menu > a,
    .sidebar-group-toggle {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        min-width: 0 !important;
        min-height: 38px !important;
        box-sizing: border-box !important;
        padding: 0 8px !important;
        gap: 7px !important;
        text-decoration: none !important;
        border-radius: 8px !important;
        color: rgba(255, 255, 255, 0.95) !important;
        font-weight: 650 !important;
        transition: background .18s ease, color .18s ease !important;
    }
    .menu > a:hover,
    .sidebar-group-toggle:hover {
        background: rgba(255, 255, 255, 0.14) !important;
        color: #ffffff !important;
    }
    .menu > a.active {
        background: #ffffff !important;
        color: var(--orb-primary) !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
    }
    .menu > a.active .menu-icon {
        color: var(--orb-primary) !important;
    }
    .menu-icon {
        flex: 0 0 16px !important;
        width: 16px !important;
        min-width: 16px !important;
        max-width: 16px !important;
        height: 16px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        flex-shrink: 0 !important;
        font-size: 13.5px !important;
    }
    .menu-icon i,
    .menu-icon svg {
        font-size: 13.5px !important;
        width: 15px !important;
        max-width: 15px !important;
        max-height: 15px !important;
        text-align: center !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        margin: 0 auto !important;
    }
    .menu-text {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        font-size: 12.8px !important;
        font-weight: 650 !important;
        line-height: 1.25 !important;
        text-decoration: none !important;
    }
    .group-chevron {
        flex: 0 0 12px !important;
        width: 12px !important;
        min-width: 12px !important;
        margin-left: auto !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        font-size: 10px !important;
        color: rgba(255, 255, 255, 0.75) !important;
        text-decoration: none !important;
        transition: transform .2s ease !important;
    }
    .sidebar-group.open .group-chevron {
        transform: rotate(180deg) !important;
    }
    .sidebar-submenu {
        display: none;
        padding: 2px 0 2px 14px !important;
    }
    .sidebar-group.open .sidebar-submenu,
    .sidebar-submenu.show {
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
    }
    .sub-link {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        min-width: 0 !important;
        min-height: 34px !important;
        box-sizing: border-box !important;
        padding: 0 8px 0 10px !important;
        gap: 8px !important;
        text-decoration: none !important;
        border-radius: 7px !important;
        color: rgba(255, 255, 255, 0.92) !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        transition: background .18s ease, color .18s ease !important;
    }
    .sub-link:hover {
        background: rgba(255, 255, 255, 0.14) !important;
        color: #ffffff !important;
    }
    .sub-link.active {
        background: #ffffff !important;
        color: var(--orb-primary) !important;
        font-weight: 800 !important;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1) !important;
    }
    .sub-link.active .sub-link-icon {
        color: var(--orb-primary) !important;
    }
    .sub-link-icon {
        flex: 0 0 14px !important;
        width: 14px !important;
        min-width: 14px !important;
        max-width: 14px !important;
        height: 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        flex-shrink: 0 !important;
        font-size: 12px !important;
    }
    .sub-link-icon i,
    .sub-link-icon svg {
        font-size: 12px !important;
        width: 13px !important;
        max-width: 13px !important;
        max-height: 13px !important;
        text-align: center !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        margin: 0 auto !important;
    }
    .sub-link-text {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        line-height: 1.25 !important;
        text-decoration: none !important;
    }
    .sidebar-footer {
        flex-shrink: 0 !important;
        padding: 10px 12px !important;
        text-align: center !important;
        border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
        background: rgba(0, 0, 0, 0.05) !important;
    }
    .sidebar-footer-sub {
        font-size: 11px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        font-weight: 600 !important;
        letter-spacing: 0.02em !important;
    }

    @media (max-width: 991.98px) {
        .sidebar {
            width: min(300px, 88vw) !important;
            min-width: min(280px, 94vw) !important;
            max-width: 100vw !important;
        }
        .sidebar-close {
            display: inline-flex !important;
        }
    }

    @media (max-width: 480px) {
        .sidebar {
            width: min(300px, 90vw) !important;
            min-width: min(275px, 95vw) !important;
        }
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

        <button type="button" class="sidebar-close" onclick="closeSidebar()" aria-label="Close Sidebar">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="sidebar-body">
        <div class="menu-label">Main Menu</div>

        <nav class="menu" id="sidebarMenu">
            @forelse($parentMenus as $menu)
            @php
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
                    <span class="menu-text">{{ $menu->name }}</span>
                    <span class="group-chevron"><i class="fas fa-chevron-down"></i></span>
                </a>

                <div class="sidebar-submenu collapse {{ $isOpen ? 'show' : '' }}"
                    id="menu{{ $menu->id }}"
                    data-parent="#sidebarMenu">
                    @forelse($children as $child)
                    @php
                    $childHasRoute = ! empty($child->route) && $child->route !== '#' && Route::has($child->route);
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
            $hasRoute = ! empty($menu->route) && $menu->route !== '#' && Route::has($menu->route);
            $activeState = $isMenuActive($menu);
            @endphp

            <a href="{{ $hasRoute ? route($menu->route) : 'javascript:void(0)' }}"
                class="{{ $activeState ? 'active' : '' }}"
                @if(! $hasRoute) data-sidebar-empty-link @endif>
                <span class="menu-icon"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></span>
                <span class="menu-text">{{ $menu->name }}</span>
            </a>
            @endif
            @empty
            <div class="empty-sidebar-state text-center py-3 text-muted">
                No menu available
            </div>
            @endforelse
        </nav>
    </div>

    {{-- <div class="sidebar-footer">
        <div class="sidebar-footer-sub">{{ $branding['company_name'] ?? 'OrboOne' }} v1.0 • {{ date('Y') }}</div>
    </div> --}}
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
