@php
    $isAdmin = auth()->user()->isAdmin();
    $noticeOpen = request()->routeIs('announcements*');
@endphp

{{-- ========== SECTION: 6. NOTICE / ANNOUNCEMENT ========== --}}
<a href="{{ route('announcements.index') }}" class="nav-link {{ $noticeOpen ? 'active' : '' }}">
  <i class="fas fa-bullhorn mr-2"></i>
  <span class="flex-grow-1">6. Notice / Announcement</span>
  @if($isAdmin)
    <span class="badge badge-warning ml-2">HR</span>
  @endif
</a>
