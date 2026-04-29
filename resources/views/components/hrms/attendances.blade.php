@php
    $user = auth()->user();

    $canAttendanceMark = $user->hasPermission('attendance.mark');
    $canTaskTrack = $user->hasPermission('task.view');
    $canAttendanceRules = $user->hasPermission('attendance.rules.manage');
    $canAttendanceReport = $user->hasPermission('attendance.report.generate');

    $showAttendanceMenu =
        $canAttendanceMark ||
        $canTaskTrack ||
        $canAttendanceRules ||
        $canAttendanceReport;

    $attendanceOpen =
        request()->routeIs('attendances*') ||
        request()->routeIs('task_management*');
@endphp

@if($showAttendanceMenu)
<div class="sidebar-group {{ $attendanceOpen ? 'open' : '' }}">
    <button
        type="button"
        class="sidebar-group-toggle {{ $attendanceOpen ? '' : 'collapsed' }}"
        data-toggle="collapse"
        data-target="#attendanceSubmenu"
        aria-expanded="{{ $attendanceOpen ? 'true' : 'false' }}"
        aria-controls="attendanceSubmenu"
    >
        <span class="menu-icon"><i class="fas fa-calendar-check"></i></span>
        <span class="menu-text flex-grow-1">Attendance & Tracking</span>
        <span class="group-chevron"><i class="fas fa-chevron-down"></i></span>
    </button>

    <div class="sidebar-submenu collapse {{ $attendanceOpen ? 'show' : '' }}" id="attendanceSubmenu" data-parent="#sidebarMenu">

        @if ($canAttendanceMark)
            <a href="{{ route('attendances') }}"
               class="sub-link {{ request()->routeIs('attendances') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-fingerprint"></i></span>
                <span class="sub-link-text">Attendance Marking</span>
            </a>
        @endif

        @if ($canTaskTrack)
            <a href="{{ route('task_management') }}"
               class="sub-link {{ request()->routeIs('task_management') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-tasks"></i></span>
                <span class="sub-link-text">Task Tracking</span>
            </a>
        @endif

        @if ($canAttendanceRules || $canAttendanceReport)
            <div class="submenu-divider"></div>
        @endif

        @if ($canAttendanceRules)
            <a href="{{ route('attendances') }}"
               class="sub-link">
                <span class="sub-link-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <span class="sub-link-text">Attendance Rules</span>
            </a>
        @endif

        @if ($canAttendanceReport)
            <a href="{{ route('attendances.export-pdf') }}"
               class="sub-link"
               target="_blank">
                <span class="sub-link-icon"><i class="fas fa-file-pdf"></i></span>
                <span class="sub-link-text">Generate Report</span>
            </a>
        @endif

    </div>
</div>
@endif