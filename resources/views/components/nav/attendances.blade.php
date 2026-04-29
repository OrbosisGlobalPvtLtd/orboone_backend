@php
    $isAdmin = auth()->user()->isAdmin();
    $attendanceOpen = request()->routeIs('attendances*') || request()->routeIs('task_management*');
@endphp

{{-- ========== SECTION: 2. ATTENDANCE & TRACKING ========== --}}
<a href="#attendanceSubmenu" data-toggle="collapse" aria-expanded="{{ $attendanceOpen ? 'true' : 'false' }}" 
   class="nav-link sidebar-collapse-btn {{ $attendanceOpen ? '' : 'collapsed' }}">
    <i class="fas fa-calendar-check mr-2"></i>
    <span class="flex-grow-1">2. Attendance & Tracking</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<ul class="collapse list-unstyled {{ $attendanceOpen ? 'show' : '' }}" id="attendanceSubmenu" data-parent="#sidebarMenu">
    
    {{-- Sub-module: Attendance Marking (Employee/Both) --}}
    <li>
        <a href="{{ route('attendances') }}" class="nav-link sub-nav-link {{ request()->routeIs('attendances') ? 'active' : '' }}">
            <i class="fas fa-fingerprint small mr-2"></i> Attendance Marking
        </a>
    </li>

    {{-- Sub-module: Task Management (Linked here for tracking) --}}
    <li>
        <a href="{{ route('task_management') }}" class="nav-link sub-nav-link {{ request()->routeIs('task_management') ? 'active' : '' }}">
            <i class="fas fa-tasks small mr-2"></i> Task Tracking
        </a>
    </li>

    @if ($isAdmin)
    <div class="border-top mx-3 my-2" style="border-color:rgba(255,255,255,0.1) !important;"></div>
    
    {{-- Sub-module: Late Coming Rules (Admin placeholder) --}}
    <li>
        <a href="{{ route('attendances') }}" class="nav-link sub-nav-link">
            <i class="fas fa-exclamation-triangle small mr-2 text-warning"></i> Attendance Rules
        </a>
    </li>

    {{-- Sub-module: Generate Report (Admin) --}}
    <li>
        <a href="{{ route('attendances.export-pdf') }}" class="nav-link sub-nav-link" target="_blank">
            <i class="fas fa-file-pdf small mr-2 text-danger"></i> Generate Report
        </a>
    </li>
    @endif
</ul>
