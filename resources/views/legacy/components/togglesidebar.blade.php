<header class="topbar">
    <div class="topbar-left">
        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>

        <div class="page-title">@yield('page_title', 'Dashboard')</div>
    </div>

    <div class="topbar-right">
        <div class="profile-chip">
            <span class="profile-dot"></span>
            <span>{{ auth()->user()->name ?? 'Super Admin' }}</span>
        </div>
    </div>
</header>