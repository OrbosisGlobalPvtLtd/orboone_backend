@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$topbarUser = Auth::user();

$announcementRoute = Route::has('announcements.index')
? route('announcements.index')
: (Route::has('hrms.announcements.index') ? route('hrms.announcements.index') : 'javascript:void(0)');

$notificationRoute = Route::has('notifications.index')
? route('notifications.index')
: (Route::has('hrms.notifications.index') ? route('hrms.notifications.index') : 'javascript:void(0)');

$unreadCount = 0;
$topbarNotifications = collect();

if ($topbarUser) {
try {
$unreadCount = DB::table('notifications')
->where('user_id', $topbarUser->id)
->where(function ($query) {
$query->where('is_read', 0)->orWhereNull('read_at');
})
->count();

$topbarNotifications = DB::table('notifications')
->where('user_id', $topbarUser->id)
->latest('created_at')
->limit(5)
->get();
} catch (\Throwable $e) {
$unreadCount = 0;
$topbarNotifications = collect();
}
}
@endphp

<nav class="navbar navbar-expand-lg bg-white w-100"
    style="border-bottom:1px solid #e5e7eb;min-height:72px;position:sticky;top:0;z-index:1050;backdrop-filter:blur(8px);padding:0 20px;">

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between w-100">

            <div class="d-flex align-items-center" style="min-width:0;">
                <button type="button" class="sidebar-toggle" onclick="toggleSidebar()"
                    style="width:42px;height:42px;border-radius:12px;border:1px solid #e5e7eb;background:#fff;margin-right:14px;transition:.2s;"
                    onmouseover="this.style.background='#f5f3ff'"
                    onmouseout="this.style.background='#fff'">
                    <i class="fa-solid fa-bars-staggered" style="color:#4B00E8;"></i>
                </button>

                <div style="min-width:0;">
                    <h5 class="mb-0 fw-bold text-dark" style="line-height:1.2;">
                        {{ ucfirst($active ?? 'dashboard') }}
                    </h5>
                    <small class="text-muted d-block" style="font-size:12px;">
                        Orbosis HRMS Panel
                    </small>
                </div>
            </div>

            <div class="d-flex align-items-center" style="gap:10px;">

                <button type="button"
                    style="width:40px;height:40px;border-radius:12px;border:1px solid #e5e7eb;background:#fff;">
                    <i class="fas fa-search text-muted"></i>
                </button>

                <a href="{{ $announcementRoute }}"
                    style="width:40px;height:40px;border-radius:12px;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;"
                    onmouseover="this.style.background='#f9f9ff'"
                    onmouseout="this.style.background='#fff'">
                    <i class="fas fa-bullhorn text-muted"></i>
                </a>

                {{-- NOTIFICATION DROPDOWN --}}
                <div class="dropdown orb-notification-dropdown">
                    <button type="button"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        style="width:40px;height:40px;border-radius:12px;border:1px solid #e5e7eb;background:#fff;position:relative;display:flex;align-items:center;justify-content:center;"
                        onmouseover="this.style.background='#f9f9ff'"
                        onmouseout="this.style.background='#fff'">
                        <i class="fas fa-bell text-muted"></i>

                        @if($unreadCount > 0)
                        <span style="position:absolute;top:2px;right:2px;background:#ec4e74;color:#fff;font-size:9px;font-weight:900;padding:2px 5px;border-radius:10px;line-height:1;">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                        @endif
                    </button>

                    <div class="dropdown-menu dropdown-menu-right shadow border-0 p-0 orb-notification-menu">
                        <div class="orb-notification-head">
                            <div>
                                <div class="fw-bold text-dark">Notifications</div>
                                <small class="text-muted">{{ $unreadCount }} unread notification</small>
                            </div>
                            <span class="orb-notification-badge">{{ $topbarNotifications->count() }}</span>
                        </div>

                        <div class="orb-notification-list">
                            @forelse($topbarNotifications as $notification)
                            @php
                            $isUnread = ((int)($notification->is_read ?? 0) === 0) || empty($notification->read_at);
                            @endphp

                            <a href="{{ $notificationRoute }}"
                                class="orb-notification-item {{ $isUnread ? 'unread' : '' }}">
                                <div class="orb-notification-icon">
                                    <i class="fas fa-bell"></i>
                                </div>

                                <div class="flex-grow-1" style="min-width:0;">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="orb-notification-title text-truncate">
                                            {{ $notification->title ?? 'Notification' }}
                                        </div>

                                        @if($isUnread)
                                        <span class="orb-unread-dot"></span>
                                        @endif
                                    </div>

                                    <div class="orb-notification-message">
                                        {{ Str::limit(strip_tags((string)($notification->message ?? '')), 70) }}
                                    </div>

                                    <div class="orb-notification-time">
                                        {{ !empty($notification->created_at) ? \Carbon\Carbon::parse($notification->created_at)->diffForHumans() : '' }}
                                    </div>
                                </div>
                            </a>
                            @empty
                            <div class="text-center py-4 px-3">
                                <div class="orb-empty-bell mb-2">
                                    <i class="fas fa-bell-slash"></i>
                                </div>
                                <div class="fw-bold text-dark">No notifications</div>
                                <small class="text-muted">Latest updates will appear here.</small>
                            </div>
                            @endforelse
                        </div>

                        <div class="orb-notification-footer">
                            <a href="{{ $notificationRoute }}" class="orb-view-all-btn">
                                View All Notifications
                                <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                @auth
                @php
                $topbarEmployee = $topbarUser?->employee;
                $topbarUserImage = trim((string) data_get($topbarUser, 'profile_image'));
                $topbarEmployeeImage = trim((string) data_get($topbarEmployee, 'profile.profile_image'));
                $topbarImage = $topbarUserImage !== '' ? $topbarUserImage : $topbarEmployeeImage;

                if ($topbarImage !== '' && preg_match('/^https?:\/\//i', $topbarImage)) {
                $topbarAvatar = $topbarImage;
                } elseif ($topbarImage !== '' && substr($topbarImage, 0, 1) === '/') {
                $topbarAvatar = $topbarImage;
                } elseif ($topbarImage !== '' && substr($topbarImage, 0, 8) === 'storage/') {
                $topbarAvatar = asset($topbarImage);
                } elseif ($topbarImage !== '') {
                $topbarAvatar = asset('storage/'.$topbarImage);
                } else {
                $topbarAvatar = null;
                }

                $topbarInitial = strtoupper(substr(trim($topbarUser?->name ?? ''), 0, 1));
                @endphp

                <div class="dropdown">
                    <div class="d-flex align-items-center"
                        data-toggle="dropdown"
                        style="cursor:pointer;border:1px solid #e5e7eb;border-radius:30px;padding:5px 10px;background:#fff;transition:.2s;"
                        onmouseover="this.style.background='#f9f9ff'"
                        onmouseout="this.style.background='#fff'">

                        @if(!empty($topbarAvatar))
                        <img src="{{ $topbarAvatar }}" alt="{{ $topbarUser?->name ?? 'User' }}"
                            style="width:36px;height:36px;border-radius:50%;object-fit:cover;margin-right:8px;">
                        <div class="topbar-avatar-fallback" style="width:36px;height:36px;border-radius:50%;background:#F4F2FF;color:#4B00E8;display:none;align-items:center;justify-content:center;font-size:13px;font-weight:900;margin-right:8px;">
                            {{ $topbarInitial ?: '' }}
                        </div>
                        @else
                        <div class="topbar-avatar-fallback" style="width:36px;height:36px;border-radius:50%;background:#F4F2FF;color:#4B00E8;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:900;margin-right:8px;">
                            @if($topbarInitial)
                            {{ $topbarInitial }}
                            @else
                            <i class="fas fa-user"></i>
                            @endif
                        </div>
                        @endif

                        <div class="d-none d-md-block" style="line-height:1.1;">
                            <div style="font-size:14px;font-weight:600;color:#111;">
                                {{ $topbarUser->name }}
                            </div>
                        </div>

                        <i class="fas fa-chevron-down ml-2 text-muted" style="font-size:10px;"></i>
                    </div>

                    <div class="dropdown-menu dropdown-menu-right shadow border-0"
                        style="border-radius:12px;padding:10px;min-width:180px;">

                        <a class="dropdown-item py-2 rounded" href="{{ Route::has('profile.index') ? route('profile.index') : 'javascript:void(0)' }}">
                            <i class="fas fa-user mr-2 text-muted"></i> My Profile
                        </a>

                        <a class="dropdown-item py-2 rounded" href="{{ Route::has('profile.index') ? route('profile.index').'#change-password' : 'javascript:void(0)' }}">
                            <i class="fas fa-lock mr-2 text-muted"></i> Change Password
                        </a>

                        <div class="dropdown-divider"></div>

                        @if(Route::has('logout'))
                        <a class="dropdown-item py-2 text-danger rounded"
                            href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                        @endif
                    </div>
                </div>
                @endauth

            </div>
        </div>
    </div>
</nav>

<style>
    .orb-notification-menu {
        width: 390px;
        max-width: calc(100vw - 24px);
        border-radius: 22px;
        overflow: hidden;
        margin-top: 12px;
    }

    .orb-notification-head {
        padding: 16px 18px;
        background: linear-gradient(135deg, #4B00E8 0%, #8600EE 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .orb-notification-head .fw-bold,
    .orb-notification-head small {
        color: #fff !important;
    }

    .orb-notification-badge {
        background: rgba(255, 255, 255, .18);
        color: #fff;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
    }

    .orb-notification-list {
        max-height: 360px;
        overflow-y: auto;
        background: #fff;
    }

    .orb-notification-item {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        text-decoration: none;
        border-bottom: 1px solid #F0F2F7;
        color: inherit;
        transition: .2s;
    }

    .orb-notification-item:hover {
        background: #F8F6FF;
        text-decoration: none;
    }

    .orb-notification-item.unread {
        background: #FBFAFF;
    }

    .orb-notification-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 13px;
        background: #F4F2FF;
        color: #4B00E8;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .orb-notification-title {
        font-size: 13px;
        font-weight: 900;
        color: #101828;
    }

    .orb-notification-message {
        font-size: 12px;
        color: #667085;
        margin-top: 3px;
        line-height: 1.35;
    }

    .orb-notification-time {
        font-size: 11px;
        color: #98A2B3;
        margin-top: 6px;
    }

    .orb-unread-dot {
        width: 8px;
        height: 8px;
        min-width: 8px;
        border-radius: 50%;
        background: #EC4E74;
        margin-top: 5px;
    }

    .orb-notification-footer {
        padding: 12px;
        background: #fff;
        border-top: 1px solid #F0F2F7;
    }

    .orb-view-all-btn {
        width: 100%;
        height: 42px;
        border-radius: 14px;
        background: #F4F2FF;
        color: #4B00E8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        text-decoration: none;
    }

    .orb-view-all-btn:hover {
        color: #fff;
        background: linear-gradient(135deg, #4B00E8 0%, #8600EE 100%);
        text-decoration: none;
    }

    .orb-empty-bell {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: #F4F2FF;
        color: #4B00E8;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.dropdown img').forEach(function(image) {
            image.addEventListener('error', function() {
                var fallback = image.nextElementSibling;
                image.style.display = 'none';

                if (fallback && fallback.classList.contains('topbar-avatar-fallback')) {
                    fallback.style.display = 'flex';
                }
            });
        });
    });
</script>