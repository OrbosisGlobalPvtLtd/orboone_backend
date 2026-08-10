@php
    $attendanceOpen = request()->routeIs('attendances.*') || request()->routeIs('attendance.*') || request()->routeIs('hrms.attendance.*') || request()->routeIs('project_management.tasks.*');
    $allMenus = isset($menus) ? $menus : collect();
    $attendanceSubmenus = collect();

    if ($allMenus instanceof \Illuminate\Support\Collection) {
        if (isset($allMenus[20])) {
            $attendanceSubmenus = $allMenus[20];
        } else {
            $attendanceSubmenus = $allMenus->filter(fn($item) => (isset($item->parent_id) && $item->parent_id == 20) || (isset($item->module_key) && $item->module_key === 'attendance'));
        }
    }
@endphp

{{-- ========== SECTION: ATTENDANCE & TRACKING ========== --}}
<a href="#attendanceSubmenu" data-toggle="collapse" aria-expanded="{{ $attendanceOpen ? 'true' : 'false' }}" 
   class="nav-link sidebar-collapse-btn {{ $attendanceOpen ? '' : 'collapsed' }}">
    <i class="fas fa-calendar-check mr-2"></i>
    <span class="flex-grow-1">Attendance & Tracking</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<ul class="collapse list-unstyled {{ $attendanceOpen ? 'show' : '' }}" id="attendanceSubmenu" data-parent="#sidebarMenu">
    @forelse($attendanceSubmenus as $item)
        @if(!empty($item->route) && \Illuminate\Support\Facades\Route::has($item->route))
            <li>
                <a href="{{ route($item->route) }}" class="nav-link sub-nav-link {{ request()->routeIs($item->route) || request()->routeIs($item->route . '.*') ? 'active' : '' }}">
                    <i class="{{ $item->icon ?? 'fas fa-circle' }} small mr-2"></i> {{ $item->name }}
                </a>
            </li>
        @endif
    @empty
        <li>
            <a href="{{ route('attendances.daily') }}" class="nav-link sub-nav-link {{ request()->routeIs('attendances.daily') ? 'active' : '' }}">
                <i class="fas fa-history small mr-2"></i> Attendance History
            </a>
        </li>
    @endforelse
</ul>
