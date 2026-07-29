@php
    $user = auth()->user();
    $isAdmin = $user ? (method_exists($user, 'isAdmin') ? $user->isAdmin() : false) : false;
    $empNav = $user ? \Illuminate\Support\Facades\DB::table('employees_new')->where('user_id', $user->id)->first() : null;
    $canWebPunchNav = $empNav ? (bool) ($empNav->allow_web_attendance ?? false) : false;
    $attendanceOpen = request()->routeIs('attendances.*') || request()->routeIs('attendance.*') || request()->routeIs('hrms.attendance.*') || request()->routeIs('project_management.tasks.*');
@endphp

{{-- ========== SECTION: 2. ATTENDANCE & TRACKING ========== --}}
<a href="#attendanceSubmenu" data-toggle="collapse" aria-expanded="{{ $attendanceOpen ? 'true' : 'false' }}" 
   class="nav-link sidebar-collapse-btn {{ $attendanceOpen ? '' : 'collapsed' }}">
    <i class="fas fa-calendar-check mr-2"></i>
    <span class="flex-grow-1">2. Attendance & Tracking</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<ul class="collapse list-unstyled {{ $attendanceOpen ? 'show' : '' }}" id="attendanceSubmenu" data-parent="#sidebarMenu">
    
    @if ($canWebPunchNav || $isAdmin)
    {{-- Sub-module: Today's Attendance (Visible if Web Attendance Enabled or Admin) --}}
    <li>
        <a href="{{ route('attendances.today') }}" class="nav-link sub-nav-link {{ request()->routeIs('attendances.today') ? 'active' : '' }}">
            <i class="fas fa-fingerprint small mr-2 text-success"></i> Today's Attendance
        </a>
    </li>
    @endif

    {{-- Sub-module: Attendance History / Daily Records --}}
    <li>
        <a href="{{ route('attendances.daily') }}" class="nav-link sub-nav-link {{ request()->routeIs('attendances.daily') ? 'active' : '' }}">
            <i class="fas fa-history small mr-2"></i> Attendance History
        </a>
    </li>

    {{-- Sub-module: Monthly Attendance Report --}}
    <li>
        <a href="{{ route('attendances.monthly-report') }}" class="nav-link sub-nav-link {{ request()->routeIs('attendances.monthly-report') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt small mr-2"></i> Monthly Attendance
        </a>
    </li>

    {{-- Sub-module: Task Management (Linked here for tracking) --}}
    <li>
        <a href="{{ route('project_management.tasks.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('project_management.tasks.index') ? 'active' : '' }}">
            <i class="fas fa-tasks small mr-2"></i> Task Tracking
        </a>
    </li>

    @if ($isAdmin)
    <div class="border-top mx-3 my-2" style="border-color:rgba(255,255,255,0.1) !important;"></div>
    
    {{-- Sub-module: Attendance Access Control --}}
    <li>
        <a href="{{ route('attendances.access-control') }}" class="nav-link sub-nav-link {{ request()->routeIs('attendances.access-control') ? 'active' : '' }}">
            <i class="fas fa-user-lock small mr-2 text-info"></i> Access Control
        </a>
    </li>

    {{-- Sub-module: Late Coming Rules (Admin placeholder) --}}
    <li>
        <a href="{{ route('attendance.rules.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('attendance.rules.*') ? 'active' : '' }}">
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

