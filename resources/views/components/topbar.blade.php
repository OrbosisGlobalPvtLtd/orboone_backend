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
                    <i class="fa-solid fa-bars-staggered" style="color:var(--orb-primary);"></i>
                </button>

                <div style="min-width:0;">
                    <h5 class="mb-0 fw-bold text-dark" style="line-height:1.2;">
                        {{ ucfirst($active ?? 'dashboard') }}
                    </h5>
                    <small class="text-muted d-block" style="font-size:12px;">
                        {{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }}
                    </small>
                </div>
            </div>

            <div class="d-flex align-items-center" style="gap:10px;">

                <button type="button" class="d-none d-md-block"
                    style="width:40px;height:40px;border-radius:12px;border:1px solid #e5e7eb;background:#fff;">
                    <i class="fas fa-search text-muted"></i>
                </button>

                <a href="{{ $announcementRoute }}" class="d-none d-md-flex"
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
                            
                            // Safely decode JSON for DB query results
                            $data = [];
                            if (!empty($notification->data)) {
                                $decoded = json_decode($notification->data, true);
                                if (is_array($decoded)) {
                                    $data = $decoded;
                                }
                            }
                            
                            // Map icon & background based on resolved type
                            $type = strtolower($notification->type ?? $data['type'] ?? 'general');
                            $icon = 'fa-bell';
                            $iconBg = 'linear-gradient(135deg, var(--orb-primary), var(--orb-secondary))';
                            
                            if (str_contains($type, 'announcement')) {
                                $icon = 'fa-bullhorn';
                                $iconBg = 'linear-gradient(135deg, #06B6D4, #0891B2)';
                            } elseif (str_contains($type, 'leave')) {
                                $icon = 'fa-calendar-alt';
                                $iconBg = 'linear-gradient(135deg, #3B82F6, #1D4ED8)';
                            } elseif (str_contains($type, 'attendance')) {
                                $icon = 'fa-clock';
                                $iconBg = 'linear-gradient(135deg, #F59E0B, #D97706)';
                            } elseif (str_contains($type, 'document')) {
                                $icon = 'fa-file-alt';
                                $iconBg = 'linear-gradient(135deg, #10B981, #047857)';
                            } elseif (str_contains($type, 'payroll') || str_contains($type, 'salary')) {
                                $icon = 'fa-wallet';
                                $iconBg = 'linear-gradient(135deg, #F97316, #C2410C)';
                            } elseif (str_contains($type, 'system') || str_contains($type, 'security')) {
                                $icon = 'fa-shield-alt';
                                $iconBg = 'linear-gradient(135deg, #EF4444, #B91C1C)';
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
                                View All Notifications
                                <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                @auth
                @php
                $topbarAvatar = resolveEmployeeAvatar($topbarUser);
                $topbarInitial = resolveEmployeeInitials($topbarUser);
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
                        <div class="topbar-avatar-fallback" style="width:36px;height:36px;border-radius:50%;background:#F4F2FF;color:var(--orb-primary);display:none;align-items:center;justify-content:center;font-size:13px;font-weight:900;margin-right:8px;">
                            {{ $topbarInitial ?: '' }}
                        </div>
                        @else
                        <div class="topbar-avatar-fallback" style="width:36px;height:36px;border-radius:50%;background:#F4F2FF;color:var(--orb-primary);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:900;margin-right:8px;">
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
    .orb-notification-menu {
        width: 420px;
        max-width: calc(100vw - 24px);
        border-radius: 24px;
        overflow: hidden;
        margin-top: 12px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 0 20px 40px rgba(16, 24, 40, 0.08) !important;
        animation: dropdownSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes dropdownSlideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .orb-notification-head {
        position: sticky;
        top: 0;
        z-index: 10;
        padding: 16px 18px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
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
        max-height: 380px;
        overflow-y: auto;
        background: #fff;
        scroll-behavior: smooth;
    }

    .orb-notification-item {
        display: flex;
        gap: 12px;
        padding: 16px;
        text-decoration: none !important;
        border-bottom: 1px solid #F0F2F7;
        color: inherit !important;
        transition: .2s;
    }

    .orb-notification-item:hover {
        background: rgba(75, 0, 232, 0.03);
    }

    .orb-notification-item.unread {
        background: rgba(75, 0, 232, 0.01);
    }

    .orb-notification-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 12px;
        background: #F4F2FF;
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .orb-notification-title {
        font-size: 13.5px;
        font-weight: 800;
        color: #101828;
    }

    .orb-notification-message {
        font-size: 12.5px;
        color: #667085;
        margin-top: 4px;
        line-height: 1.45;
        word-break: break-word;
    }

    .orb-notification-time {
        font-size: 11px;
        color: #98A2B3;
        margin-top: 6px;
        font-weight: 600;
    }

    .orb-unread-dot {
        width: 8px;
        height: 8px;
        min-width: 8px;
        border-radius: 50%;
        background: var(--orb-primary);
        margin-top: 5px;
        animation: pulse-dot 1.5s infinite;
    }

    .orb-notification-footer {
        position: sticky;
        bottom: 0;
        z-index: 10;
        padding: 12px;
        background: #fff;
        border-top: 1px solid #F0F2F7;
    }

    .orb-view-all-btn {
        width: 100%;
        height: 42px;
        border-radius: 14px;
        background: #F4F2FF;
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .orb-view-all-btn:hover {
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    }

    .orb-empty-bell {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: #F4F2FF;
        color: var(--orb-primary);
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Responsiveness for Topbar Dropdown */
    @media (max-width: 992px) {
        .orb-notification-menu {
            width: 95vw;
            right: -10px !important;
        }
    }

    @media (max-width: 576px) {
        .orb-notification-menu {
            width: calc(100vw - 24px);
            right: -12px !important;
        }
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

        @if($errors->has('current_password') || $errors->has('password'))
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
