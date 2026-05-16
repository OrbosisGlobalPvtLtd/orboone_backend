@php
$user = auth()->user();

$canAnnouncementView = $user->hasPermission('announcements.view');
$canAnnouncementCreate = $user->hasPermission('announcements.create');

$noticeOpen = request()->routeIs('announcements*');
@endphp

@if($canAnnouncementView || $canAnnouncementCreate)
<a href="{{ route('announcements.index') }}" class="{{ $noticeOpen ? 'active' : '' }}">
    <span class="menu-icon"><i class="fas fa-bullhorn"></i></span>
    <span class="menu-text flex-grow-1">Notice / Announcement</span>

    @if($canAnnouncementCreate)
    <span class="menu-badge">HR</span>
    @endif
</a>
@endif