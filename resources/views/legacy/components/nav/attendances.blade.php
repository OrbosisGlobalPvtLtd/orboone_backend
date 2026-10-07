@php
    $attendanceOpen = request()->routeIs('attendances.*') || request()->routeIs('attendance.*') || request()->routeIs('hrms.attendance.*');
    $allMenus = $menus ?? collect();

    $parentCollection = $allMenus->get('') ?? $allMenus->get(null) ?? $allMenus->get(0) ?? collect();
    $attendanceParent = $parentCollection->first(fn($item) => ($item->module_key ?? '') === 'attendance' || str_starts_with($item->route ?? '', 'hrms.attendance.'));

    $attendanceSubmenus = collect();
    if ($attendanceParent) {
        $attendanceSubmenus = $allMenus->get($attendanceParent->id) ?? $allMenus->get((string)$attendanceParent->id) ?? collect();
    }
@endphp

@if($attendanceParent && $attendanceSubmenus->isNotEmpty())
{{-- ========== SECTION: ATTENDANCE & TRACKING ========== --}}
<a href="#attendanceSubmenu" data-toggle="collapse" aria-expanded="{{ $attendanceOpen ? 'true' : 'false' }}" 
   class="nav-link sidebar-collapse-btn {{ $attendanceOpen ? '' : 'collapsed' }}">
    <i class="{{ $attendanceParent->icon ?? 'fas fa-calendar-check' }} mr-2"></i>
    <span class="flex-grow-1">{{ $attendanceParent->name }}</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<ul class="collapse list-unstyled {{ $attendanceOpen ? 'show' : '' }}" id="attendanceSubmenu" data-parent="#sidebarMenu">
    @foreach($attendanceSubmenus as $item)
        @if(!empty($item->route))
            <li>
                <a href="{{ route($item->route) }}" class="nav-link sub-nav-link {{ request()->routeIs($item->route) || request()->routeIs($item->route . '.*') ? 'active' : '' }}">
                    <i class="{{ $item->icon ?? 'fas fa-circle' }} small mr-2"></i> {{ $item->name }}
                </a>
            </li>
        @endif
    @endforeach
</ul>
@endif


