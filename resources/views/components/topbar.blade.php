@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$topbarUser = Auth::user();

$announcementRoute = Route::has('announcements.index')
? route('announcements.index')
: (Route::has('employee.announcements.index') ? route('employee.announcements.index') : 'javascript:void(0)');

$notificationRoute = Route::has('notifications.index')
? route('notifications.index')
: 'javascript:void(0)';

$unreadCount = 0;
$topbarNotifications = collect();

if ($topbarUser) {
    static $topbarNotifCache = null;
    if ($topbarNotifCache !== null && ($topbarNotifCache['user_id'] ?? null) === $topbarUser->id) {
        $unreadCount = $topbarNotifCache['unreadCount'];
        $topbarNotifications = $topbarNotifCache['notifications'];
    } else {
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

            $topbarNotifCache = [
                'user_id' => $topbarUser->id,
                'unreadCount' => $unreadCount,
                'notifications' => $topbarNotifications,
            ];
        } catch (\Throwable $e) {
            $unreadCount = 0;
            $topbarNotifications = collect();
        }
    }
}
@endphp

<nav class="navbar navbar-expand-lg bg-white w-100 topbar-nav">
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between w-100 topbar-inner">

            <div class="d-flex align-items-center topbar-left-box">
                <button type="button" class="sidebar-toggle topbar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>

                <div class="topbar-title-wrap">
                    @php
                        $titleOverrides = [
                            'reporting_supervisors' => 'Reporting Managers',
                            'reporting_managers' => 'Reporting Managers',
                            'reporting_my_employees' => 'My Reporting Employees',
                            'reporting_work_reports' => 'Daily Work Reports',
                            'reporting_projects' => 'Projects & Tasks',
                            'reporting_attendance' => 'Reporting Employee Attendance',
                            'reporting_leave' => 'Reporting Employee Leave',
                            'reporting_dashboard' => 'Reporting Manager Dashboard',
                        ];
                        $displayActiveTitle = $titleOverrides[$active ?? ''] ?? ucwords(str_replace(['_', '-'], ' ', $active ?? 'dashboard'));
                    @endphp
                    <h5 class="mb-0 fw-bold text-dark topbar-title" title="{{ $displayActiveTitle }}">
                        {{ $displayActiveTitle }}
                    </h5>
                </div>
            </div>

            <div class="d-flex align-items-center topbar-right-box">

                <button type="button" class="d-none d-md-flex align-items-center justify-content-center topbar-icon-btn" aria-label="Search">
                    <i class="fas fa-search text-muted"></i>
                </button>

                <a href="{{ $announcementRoute }}" class="d-none d-md-flex align-items-center justify-content-center topbar-icon-btn" aria-label="Announcements">
                    <i class="fas fa-bullhorn text-muted"></i>
                </a>

                {{-- NOTIFICATION ICON (MOBILE: Direct link to notifications view page) --}}
                <a href="{{ $notificationRoute }}" class="d-flex d-md-none align-items-center justify-content-center topbar-icon-btn topbar-notif-mobile-btn" title="Notifications" aria-label="Notifications">
                    <i class="fas fa-bell text-muted"></i>

                    @if($unreadCount > 0)
                    <span class="topbar-notif-badge">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                    @endif
                </a>

                {{-- NOTIFICATION DROPDOWN (DESKTOP) --}}
                <div class="dropdown orb-notification-dropdown d-none d-md-block">
                    <button type="button"
                        class="topbar-icon-btn"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        onclick="if (window.innerWidth < 768) { window.location.href = '{{ $notificationRoute }}'; }"
                        aria-label="Notifications">
                        <i class="fas fa-bell text-muted"></i>

                        @if($unreadCount > 0)
                        <span class="topbar-notif-badge">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                        @endif
                    </button>

                    <div class="dropdown-menu dropdown-menu-right shadow border-0 p-0 orb-notification-menu">
                        <div class="orb-notification-head">
                            <div>
                                <div class="orb-notification-head-title">Notifications</div>
                                <div class="orb-notification-head-sub">{{ $unreadCount }} unread notification</div>
                            </div>
                            <span class="orb-notification-badge">{{ $topbarNotifications->count() }}</span>
                        </div>

                        <div class="orb-notification-list">
                            @forelse($topbarNotifications as $notification)
                            @php
                            $isUnread = ((int)($notification->is_read ?? 0) === 0) || empty($notification->read_at);
                            
                            // Safely decode JSON for DB query results
                            $data = [];
                            if (!empty($notification->data)) {
                                $decoded = json_decode($notification->data, true);
                                if (is_array($decoded)) {
                                    $data = $decoded;
                                }
                            }
                            
                            // Map icon & background based on resolved type and title
                            $searchContext = strtolower(($notification->type ?? '') . ' ' . ($data['type'] ?? '') . ' ' . ($notification->title ?? ''));
                            $icon = 'fa-bell';
                            $iconBg = 'linear-gradient(135deg, var(--orb-primary), var(--orb-secondary))';
                            
                            if (str_contains($searchContext, 'leave')) {
                                $icon = 'fa-calendar-alt';
                                $iconBg = '#3B82F6';
                            } elseif (str_contains($searchContext, 'attendance') || str_contains($searchContext, 'punch') || str_contains($searchContext, 'regularization')) {
                                $icon = 'fa-clock';
                                $iconBg = '#F59E0B';
                            } elseif (str_contains($searchContext, 'announcement')) {
                                $icon = 'fa-bullhorn';
                                $iconBg = '#06B6D4';
                            } elseif (str_contains($searchContext, 'document')) {
                                $icon = 'fa-file-alt';
                                $iconBg = '#10B981';
                            } elseif (str_contains($searchContext, 'payroll') || str_contains($searchContext, 'salary')) {
                                $icon = 'fa-wallet';
                                $iconBg = '#F97316';
                            } elseif (str_contains($searchContext, 'system') || str_contains($searchContext, 'security')) {
                                $icon = 'fa-shield-alt';
                                $iconBg = '#EF4444';
                            }

                            // Attachment extraction
                            $attUrl = $data['attachment_url'] ?? $data['attachment'] ?? '';
                            $attType = $data['attachment_type'] ?? '';
                            if (empty($attType) && !empty($attUrl)) {
                                $ext = strtolower(pathinfo($attUrl, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) $attType = 'image';
                                elseif ($ext === 'pdf') $attType = 'pdf';
                                else $attType = 'document';
                            }
                            @endphp

                            <a href="{{ route('notifications.open', $notification->id) }}"
                                class="orb-notification-item {{ $isUnread ? 'unread' : '' }}">
                                <div class="orb-notification-icon" style="background: {{ $iconBg }}; color: #fff;">
                                    <i class="fas {{ $icon }}"></i>
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

                                    <!-- Compact Attachment Badge inside Dropdown -->
                                    @if(!empty($attUrl))
                                        <div style="margin-top: 4px;">
                                            <span class="orb-notification-att-badge" style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; background:#F1F5F9; color:#475569; display:inline-flex; align-items:center; gap:4px;">
                                                @if($attType === 'pdf')
                                                    <i class="fas fa-file-pdf" style="color:#EF4444;"></i> PDF
                                                @elseif($attType === 'image')
                                                    <i class="fas fa-file-image" style="color:#10B981;"></i> Image
                                                @else
                                                    <i class="fas fa-file-alt" style="color:#3B82F6;"></i> Doc
                                                @endif
                                            </span>
                                        </div>
                                    @endif

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
                                View All Notifications &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                @auth
                @php
                $topbarAvatar = !empty($authEmployee) ? resolveEmployeeAvatar($authEmployee) : resolveEmployeeAvatar($topbarUser);
                $topbarInitial = !empty($authEmployee) ? resolveEmployeeInitials($authEmployee) : resolveEmployeeInitials($topbarUser);
                @endphp

                <div class="dropdown topbar-user-dropdown">
                    <div class="d-flex align-items-center topbar-user-pill"
                        data-toggle="dropdown"
                        role="button"
                        aria-haspopup="true"
                        aria-expanded="false">

                        @if(!empty($topbarAvatar))
                        <img src="{{ $topbarAvatar }}" alt="{{ $topbarUser?->name ?? 'User' }}" class="topbar-avatar-img">
                        <div class="topbar-avatar-fallback">
                            {{ $topbarInitial ?: '' }}
                        </div>
                        @else
                        <div class="topbar-avatar-fallback">
                            @if($topbarInitial)
                            {{ $topbarInitial }}
                            @else
                            <i class="fas fa-user"></i>
                            @endif
                        </div>
                        @endif

                        <div class="d-none d-md-block topbar-user-name-box">
                            <div class="topbar-user-name text-truncate">
                                {{ $topbarUser->name }}
                            </div>
                        </div>

                        <i class="fas fa-chevron-down text-muted topbar-user-chevron"></i>
                    </div>

                    <div class="dropdown-menu dropdown-menu-right shadow border-0 topbar-user-menu">

                        @if(!empty($isEmployeeUser))
                        <a class="dropdown-item py-2 rounded" href="{{ Route::has('profile.index') ? route('profile.index') : 'javascript:void(0)' }}">
                            <i class="fas fa-user mr-2 text-muted"></i> My Profile
                        </a>
                        @endif

                        <a class="dropdown-item py-2 rounded" href="javascript:void(0)" data-toggle="modal" data-target="#topbarChangePasswordModal">
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
    .topbar-nav {
        border-bottom: 1px solid #e5e7eb;
        min-height: 56px;
        position: sticky !important;
        top: 0 !important;
        z-index: 1050 !important;
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        padding: 0 16px;
    }

    .topbar-inner {
        min-width: 0;
        gap: 10px;
    }

    .topbar-left-box {
        min-width: 0;
        flex: 1 1 auto;
        gap: 10px;
        overflow: hidden;
    }

    .topbar-toggle-btn {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
        flex-shrink: 0;
        cursor: pointer;
        padding: 0;
        color: var(--orb-primary);
        font-size: 13px;
        margin-right: 0 !important;
    }

    .topbar-toggle-btn:hover {
        background: #f5f3ff;
    }

    .topbar-title-wrap {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
    }

    .topbar-title {
        line-height: 1.25;
        font-size: 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0;
    }

    .topbar-right-box {
        gap: 8px;
        flex-shrink: 0;
    }

    .topbar-icon-btn {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        text-decoration: none !important;
        transition: all .2s ease;
        flex-shrink: 0;
        padding: 0;
        font-size: 13px;
        cursor: pointer;
    }

    .topbar-icon-btn:hover {
        background: #f9f9ff;
    }

    .topbar-notif-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ec4e74;
        color: #fff;
        font-size: 8.5px;
        font-weight: 800;
        min-width: 17px;
        height: 17px;
        border-radius: 999px;
        padding: 0 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #fff;
        line-height: 1;
        box-shadow: 0 2px 4px rgba(236,78,116,0.35);
        z-index: 2;
    }

    .topbar-user-pill {
        cursor: pointer;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        padding: 4px 10px 4px 4px;
        background: #fff;
        transition: all .2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        user-select: none;
    }

    .topbar-user-pill:hover {
        background: #f9f9ff;
    }

    .topbar-avatar-img,
    .topbar-avatar-fallback {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .topbar-avatar-fallback {
        background: #F4F2FF;
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 900;
    }

    .topbar-user-name-box {
        line-height: 1.1;
        max-width: 130px;
    }

    .topbar-user-name {
        font-size: 13.5px;
        font-weight: 600;
        color: #111827;
    }

    .topbar-user-chevron {
        font-size: 10px;
        color: #9ca3af;
        transition: transform .2s ease;
    }

    .topbar-user-menu {
        border-radius: 14px;
        padding: 8px;
        min-width: 190px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08) !important;
        margin-top: 6px;
    }

    /* NOTIFICATION DROPDOWN STYLES */
    .orb-notification-menu {
        width: 360px !important;
        max-width: 92vw !important;
        border-radius: 20px !important;
        border: none !important;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18) !important;
        overflow: hidden !important;
        margin-top: 8px !important;
        background: #ffffff !important;
        padding: 0 !important;
    }

    .orb-notification-head {
        padding: 16px 20px !important;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #EC4E74)) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        border-bottom: none !important;
    }

    .orb-notification-head-title {
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
    }

    .orb-notification-head-sub {
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        margin-top: 2px !important;
    }

    .orb-notification-badge {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        min-width: 24px !important;
        height: 24px !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 6px !important;
    }

    .orb-notification-list {
        max-height: 350px !important;
        overflow-y: auto !important;
        background: #ffffff !important;
    }

    .orb-notification-list::-webkit-scrollbar {
        width: 5px;
    }
    .orb-notification-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .orb-notification-item {
        display: flex !important;
        align-items: flex-start !important;
        gap: 12px !important;
        padding: 14px 18px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        text-decoration: none !important;
        color: #1e293b !important;
        background: #ffffff !important;
        transition: background .15s ease !important;
    }

    .orb-notification-item:hover {
        background: #f8fafc !important;
        text-decoration: none !important;
    }

    .orb-notification-item.unread {
        background: #ffffff !important;
    }

    .orb-notification-icon {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
        color: #ffffff !important;
        flex-shrink: 0 !important;
    }

    .orb-notification-title {
        font-size: 13.5px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        line-height: 1.25 !important;
    }

    .orb-unread-dot {
        width: 8px !important;
        height: 8px !important;
        border-radius: 50% !important;
        background: var(--orb-primary, #4B00E8) !important;
        flex-shrink: 0 !important;
        margin-top: 4px !important;
    }

    .orb-notification-message {
        font-size: 12px !important;
        color: #64748b !important;
        line-height: 1.4 !important;
        margin-top: 2px !important;
        word-break: break-word !important;
    }

    .orb-notification-time {
        font-size: 11px !important;
        color: #94a3b8 !important;
        font-weight: 600 !important;
        margin-top: 4px !important;
    }

    .orb-notification-footer {
        padding: 12px 18px 16px !important;
        border-top: none !important;
        text-align: center !important;
        background: #ffffff !important;
    }

    .orb-view-all-btn {
        display: block !important;
        width: 100% !important;
        padding: 10px !important;
        border-radius: 12px !important;
        background: #F4F2FF !important;
        color: var(--orb-primary, #4B00E8) !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        text-align: center !important;
        text-decoration: none !important;
        transition: all .2s ease !important;
    }

    .orb-view-all-btn:hover {
        background: #EDE9FE !important;
        color: var(--orb-primary, #4B00E8) !important;
        text-decoration: none !important;
    }

    .orb-empty-bell {
        font-size: 28px !important;
        color: #cbd5e1 !important;
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

        @if(isset($errors) && ($errors->has('current_password') || $errors->has('password')))
            $('#topbarChangePasswordModal').modal('show');
        @endif
    });
</script>

<!-- TOPBAR CHANGE PASSWORD MODAL -->
<div class="modal fade" id="topbarChangePasswordModal" tabindex="-1" role="dialog" aria-labelledby="topbarChangePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border: none; border-radius: 24px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.12);">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); color: white; border-bottom: none; padding: 20px 24px;">
                <h5 class="modal-title" id="topbarChangePasswordModalLabel" style="font-weight: 800; font-size: 18px; color: white;"><i class="fas fa-lock mr-2"></i>Change Password</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8; font-size: 24px; border: none; background: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding: 24px;">
                    <p class="text-muted mb-4" style="font-size: 13px; font-weight: 600;">Update your account password. Make sure it's secure and at least 8 characters long.</p>
                    
                    <div class="mb-3">
                        <label style="display: block; color: #667085; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Current Password</label>
                        <input type="password" name="current_password" required style="width: 100%; height: 42px; border-radius: 12px; border: 1px solid #E7EAF3; background: #F9FAFB; color: #101828; font-size: 13px; font-weight: 700; padding: 8px 14px; transition: all 0.2s ease;">
                    </div>
                    <div class="mb-3">
                        <label style="display: block; color: #667085; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">New Password</label>
                        <input type="password" name="password" required style="width: 100%; height: 42px; border-radius: 12px; border: 1px solid #E7EAF3; background: #F9FAFB; color: #101828; font-size: 13px; font-weight: 700; padding: 8px 14px; transition: all 0.2s ease;">
                    </div>
                    <div class="mb-3">
                        <label style="display: block; color: #667085; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required style="width: 100%; height: 42px; border-radius: 12px; border: 1px solid #E7EAF3; background: #F9FAFB; color: #101828; font-size: 13px; font-weight: 700; padding: 8px 14px; transition: all 0.2s ease;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none; padding: 16px 24px; gap: 8px; display: flex; justify-content: flex-end;">
                    <button type="button" data-dismiss="modal" style="background: #E7EAF3; border: none; color: #4B5563; min-height:38px; border-radius: 12px; padding: 8px 16px; font-size: 13px; font-weight: 800; cursor: pointer; transition: all 0.2s ease;">Cancel</button>
                    <button type="submit" style="color: white; border-color: transparent; background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); box-shadow: 0 4px 14px rgba(75, 0, 232, 0.2); min-height:38px; border-radius: 12px; padding: 8px 16px; font-size: 13px; font-weight: 800; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
