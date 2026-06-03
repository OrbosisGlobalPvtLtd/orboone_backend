@extends('layouts.panel', ['active' => 'notifications'])

@section('page_title', 'All Notifications')

@section('_content')
<style>
    /* CSS Variables for design consistency & Dark Mode readiness */
    :root {

        --orb-bg: linear-gradient(180deg, #F6F7FB 0%, #EEF2FF 100%);
        --orb-card: #ffffff;
        --orb-text: #101828;
        --orb-text-muted: #667085;
        --orb-border: #E7EAF3;
        --orb-unread-bg: rgba(75, 0, 232, 0.02);
        --orb-unread-border: var(--orb-primary);
        --orb-shadow: 0 10px 30px rgba(16, 24, 40, 0.04);
        --orb-shadow-hover: 0 16px 40px rgba(75, 0, 232, 0.08);
    }

    .notif-page {
        min-height: calc(100vh - 90px);
        padding: 30px 20px 50px;
        background: var(--orb-bg);
    }

    .notif-container {
        max-width: 1100px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Premium Header Hero Card */
    .notif-hero-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 24px;
        padding: 24px 32px;
        box-shadow: var(--orb-shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        transition: all 0.3s ease;
    }

    .notif-hero-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .notif-hero-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 24px;
        box-shadow: 0 8px 20px rgba(75, 0, 232, 0.2);
        animation: hero-bell-ring 4s infinite ease-in-out;
    }

    @keyframes hero-bell-ring {
        0%, 100% { transform: rotate(0deg); }
        5% { transform: rotate(12deg); }
        10% { transform: rotate(-12deg); }
        15% { transform: rotate(8deg); }
        20% { transform: rotate(-8deg); }
        25% { transform: rotate(0deg); }
    }

    .notif-hero-info h1 {
        margin: 0 0 4px;
        font-size: 24px;
        font-weight: 900;
        color: var(--orb-text);
        letter-spacing: -0.02em;
    }

    .notif-hero-info p {
        margin: 0;
        font-size: 13.5px;
        color: var(--orb-text-muted);
        font-weight: 600;
    }

    .notif-hero-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .unread-badge {
        background: rgba(75, 0, 232, 0.1);
        color: var(--orb-primary);
        font-weight: 800;
        font-size: 12px;
        padding: 6px 14px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .unread-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--orb-primary);
        animation: pulse-dot 1.5s infinite;
    }

    .btn-hero-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none !important;
        transition: all 0.2s ease;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: var(--orb-text);
        cursor: pointer;
    }

    .btn-hero-action:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        transform: translateY(-1px);
    }

    .btn-hero-action-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        border: none;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15);
    }

    .btn-hero-action-primary:hover {
        background: linear-gradient(135deg, #3d00be, #7000c9);
        color: #fff;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.25);
    }

    /* Notification List Section */
    .notif-list-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        padding: 24px;
        box-shadow: var(--orb-shadow);
    }

    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* Notification Items */
    .notif-item {
        display: flex;
        gap: 16px;
        padding: 20px;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        background: var(--orb-card);
        text-decoration: none !important;
        color: inherit !important;
        position: relative;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
    }

    .notif-item:hover {
        transform: translateY(-3px);
        box-shadow: var(--orb-shadow-hover);
        border-color: rgba(75, 0, 232, 0.2);
    }

    /* Unread Card Style */
    .notif-item.unread {
        background: var(--orb-unread-bg);
        border-left: 5px solid var(--orb-unread-border);
        box-shadow: 0 4px 20px rgba(75, 0, 232, 0.02);
    }

    /* Gradient Circular Icons */
    .notif-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .notif-body {
        flex-grow: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .notif-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .notif-item-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notif-item-msg {
        font-size: 13.5px;
        color: var(--orb-text-muted);
        line-height: 1.55;
        font-weight: 500;
        margin: 0;
        word-break: break-word;
    }

    /* Attachment Chip */
    .notif-attachment-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        margin-top: 4px;
        width: fit-content;
        transition: all 0.2s ease;
    }

    .notif-attachment-badge:hover {
        background: #E2E8F0;
        color: #1e293b;
    }

    .notif-attachment-badge.pdf {
        background: #FEF2F2;
        border-color: #FEE2E2;
        color: #EF4444;
    }
    .notif-attachment-badge.pdf:hover { background: #FEE2E2; }

    .notif-attachment-badge.image {
        background: #ECFDF5;
        border-color: #D1FAE5;
        color: #10B981;
    }
    .notif-attachment-badge.image:hover { background: #D1FAE5; }

    /* Metadata Row */
    .notif-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        font-size: 11.5px;
        font-weight: 700;
        color: #94A3B8;
        margin-top: 4px;
        align-items: center;
    }

    .notif-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* Right column */
    .notif-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }

    .notif-time-ago {
        font-size: 12px;
        font-weight: 700;
        color: #94A3B8;
        white-space: nowrap;
    }

    .notif-chevron {
        color: #CBD5E1;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .notif-item:hover .notif-chevron {
        transform: translateX(3px);
        color: var(--orb-primary);
    }

    .notif-status-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pulse-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--orb-primary);
        box-shadow: 0 0 0 0 rgba(75, 0, 232, 0.4);
        animation: pulse-dot 1.5s infinite;
    }

    @keyframes pulse-dot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(75, 0, 232, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(75, 0, 232, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(75, 0, 232, 0); }
    }

    /* Dynamic Action Pill Buttons Row */
    .notif-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
        border-top: 1px dashed var(--orb-border);
        padding-top: 12px;
    }

    .btn-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none !important;
        transition: all 0.2s ease;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: var(--orb-text-muted);
        cursor: pointer;
    }

    .btn-pill:hover {
        background: #F8FAFC;
        color: var(--orb-text);
        border-color: #CBD5E1;
    }

    .btn-pill-primary {
        background: rgba(75, 0, 232, 0.06);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, 0.15);
    }
    .btn-pill-primary:hover {
        background: var(--orb-primary);
        color: #fff;
        border-color: var(--orb-primary);
    }

    .btn-pill-danger {
        background: #FFF5F5;
        color: #E53E3E;
        border-color: #FED7D7;
    }
    .btn-pill-danger:hover {
        background: #E53E3E;
        color: #fff;
        border-color: #E53E3E;
    }

    /* Premium Empty State */
    .notif-empty-state {
        padding: 80px 40px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }

    .empty-bell-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 30px;
        background: linear-gradient(135deg, rgba(75, 0, 232, 0.05), rgba(134, 0, 238, 0.05));
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        position: relative;
        animation: float-empty 4s ease-in-out infinite;
    }

    @keyframes float-empty {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .empty-bell-icon {
        font-size: 42px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .notif-empty-state h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: var(--orb-text);
    }

    .notif-empty-state p {
        margin: 0;
        font-size: 14px;
        color: var(--orb-text-muted);
        max-width: 320px;
        line-height: 1.6;
        font-weight: 500;
    }

    /* Pagination Redesign Styling */
    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper .pagination {
        display: flex;
        gap: 8px;
        border-radius: 999px;
        padding: 6px;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid var(--orb-border);
        width: fit-content;
        margin: 0 auto;
    }

    .pagination-wrapper .page-item .page-link {
        border: none;
        background: transparent;
        color: var(--orb-text-muted);
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 999px;
        transition: all 0.3s ease;
    }

    .pagination-wrapper .page-item.active .page-link {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(75, 0, 232, 0.2);
    }

    .pagination-wrapper .page-item .page-link:hover {
        background: rgba(75, 0, 232, 0.08);
        color: var(--orb-primary);
    }

    /* Responsive Breakpoints & Viewport Wrappings */
    @media (max-width: 992px) {
        .notif-hero-card {
            padding: 20px 24px;
        }
    }

    @media (max-width: 768px) {
        .notif-hero-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        .notif-hero-actions {
            width: 100%;
            justify-content: flex-start;
        }
        .notif-item {
            flex-direction: column;
            gap: 14px;
        }
        .notif-right {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            border-top: 1px solid var(--orb-border);
            padding-top: 12px;
        }
    }

    @media (max-width: 576px) {
        .notif-page {
            padding: 16px 10px 30px;
        }
        .notif-hero-left {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        .notif-hero-icon {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
        .notif-hero-actions {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
        }
        .btn-hero-action {
            justify-content: center;
            width: 100%;
        }
        .unread-badge {
            justify-content: center;
            width: 100%;
        }
    }
</style>

<div class="notif-page">
    <div class="notif-container">
        <!-- Premium Hero Header -->
        <div class="notif-hero-card">
            <div class="notif-hero-left">
                <div class="notif-hero-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="notif-hero-info">
                    <h1>Notifications</h1>
                    <p>Track announcements, approvals, reminders, and system activities.</p>
                </div>
            </div>
            
            <div class="notif-hero-actions">
                @php
                    $totalUnread = $notifications->where('is_read', false)->count();
                @endphp
                @if($totalUnread > 0)
                    <div class="unread-badge">
                        <span class="unread-badge-dot"></span>
                        {{ $totalUnread }} New
                    </div>
                    <button class="btn-hero-action btn-hero-action-primary" onclick="markAllNotificationsRead(this)">
                        <i class="fas fa-check-double"></i> Mark all as read
                    </button>
                @endif
                <button class="btn-hero-action" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Notification List Container Card -->
        <div class="notif-list-card">
            <div class="notif-list">
                @forelse($notifications as $notification)
                    @php
                        // Resolve Type, Icon, and Gradient by type category
                        $type = strtolower($notification->type ?? $notification->data['type'] ?? 'general');
                        $icon = 'fa-bell';
                        $gradient = 'linear-gradient(135deg, var(--orb-primary), var(--orb-secondary))';
                        
                        if (str_contains($type, 'announcement')) {
                            $icon = 'fa-bullhorn';
                            $gradient = 'linear-gradient(135deg, #06B6D4, #0891B2)';
                        } elseif (str_contains($type, 'leave')) {
                            $icon = 'fa-calendar-alt';
                            $gradient = 'linear-gradient(135deg, #3B82F6, #1D4ED8)';
                        } elseif (str_contains($type, 'attendance')) {
                            $icon = 'fa-clock';
                            $gradient = 'linear-gradient(135deg, #F59E0B, #D97706)';
                        } elseif (str_contains($type, 'document')) {
                            $icon = 'fa-file-alt';
                            $gradient = 'linear-gradient(135deg, #10B981, #047857)';
                        } elseif (str_contains($type, 'payroll') || str_contains($type, 'salary')) {
                            $icon = 'fa-wallet';
                            $gradient = 'linear-gradient(135deg, #F97316, #C2410C)';
                        } elseif (str_contains($type, 'system') || str_contains($type, 'security')) {
                            $icon = 'fa-shield-alt';
                            $gradient = 'linear-gradient(135deg, #EF4444, #B91C1C)';
                        }

                        // Parse Attachment URL safely
                        $attUrl = $notification->data['attachment_url'] ?? $notification->data['attachment'] ?? '';
                        $attName = $notification->data['attachment_name'] ?? basename($attUrl) ?? 'Attachment';
                        $attType = $notification->data['attachment_type'] ?? '';
                        if (empty($attType) && !empty($attUrl)) {
                            $ext = strtolower(pathinfo($attUrl, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) $attType = 'image';
                            elseif ($ext === 'pdf') $attType = 'pdf';
                            else $attType = 'document';
                        }
                    @endphp

                    <div id="notif-card-{{ $notification->id }}" class="notif-item {{ !$notification->is_read ? 'unread' : '' }}" onclick="handleNotificationClick(event, '{{ route('notifications.open', $notification->id) }}')">
                        <div class="notif-icon-circle" style="background: {{ $gradient }}">
                            <i class="fas {{ $icon }}"></i>
                        </div>

                        <div class="notif-body">
                            <div class="notif-title-row">
                                <h3 class="notif-item-title">
                                    {{ $notification->title ?? 'Notification' }}
                                </h3>
                            </div>

                            <p class="notif-item-msg">
                                {{ $notification->message ?? '-' }}
                            </p>

                            <!-- Attachment Chip badge -->
                            @if(!empty($attUrl))
                                <a href="{{ (str_starts_with($attUrl, 'http://') || str_starts_with($attUrl, 'https://') || str_starts_with($attUrl, '/')) ? $attUrl : asset('storage/' . $attUrl) }}" target="_blank" class="notif-attachment-badge {{ $attType }}" onclick="event.stopPropagation()">
                                    @if($attType === 'pdf')
                                        <i class="fas fa-file-pdf"></i> [PDF Attachment]
                                    @elseif($attType === 'image')
                                        <i class="fas fa-file-image"></i> [Image Attachment]
                                    @else
                                        <i class="fas fa-file-alt"></i> [Document Attachment]
                                    @endif
                                </a>
                            @endif

                            <div class="notif-meta-row">
                                <span class="notif-meta-item">
                                    <i class="far fa-clock"></i> {{ $notification->created_at->diffForHumans() }}
                                </span>
                                <span class="notif-meta-item">
                                    <i class="far fa-calendar-alt"></i> {{ $notification->created_at->format('d M Y, h:i A') }}
                                </span>
                            </div>

                            <!-- Wrap Layout Action pills -->
                            <div class="notif-actions" onclick="event.stopPropagation()">
                                <a href="{{ route('notifications.open', $notification->id) }}" class="btn-pill btn-pill-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                @if(!$notification->is_read)
                                    <button class="btn-pill" onclick="markSingleAsRead('{{ $notification->id }}', this)">
                                        <i class="fas fa-check"></i> Mark Read
                                    </button>
                                @endif
                                <button class="btn-pill btn-pill-danger" onclick="dismissNotification('{{ $notification->id }}')">
                                    <i class="fas fa-trash-alt"></i> Dismiss
                                </button>
                            </div>
                        </div>

                        <div class="notif-right">
                            <span class="notif-time-ago">{{ $notification->created_at->diffForHumans(null, true) }}</span>
                            @if(!$notification->is_read)
                                <div class="notif-status-indicator">
                                    <span class="pulse-dot"></span>
                                </div>
                            @endif
                            <i class="fas fa-chevron-right notif-chevron"></i>
                        </div>
                    </div>
                @empty
                    <!-- Catch-up Empty State -->
                    <div class="notif-empty-state">
                        <div class="empty-bell-wrapper">
                            <i class="fas fa-bell-slash empty-bell-icon"></i>
                        </div>
                        <h3>You're all caught up</h3>
                        <p>You don't have any notifications at the moment. We'll let you know when something comes up!</p>
                    </div>
                @endforelse
            </div>

            <!-- Laravel standard Pagination Links -->
            @if($notifications->hasPages())
                <div class="pagination-wrapper">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function handleNotificationClick(event, url) {
        if (event.target.closest('a') || event.target.closest('button')) {
            return;
        }
        window.location.href = url;
    }

    function markSingleAsRead(id, btnElement) {
        var card = document.getElementById('notif-card-' + id);
        
        fetch('/api/v1/notifications/' + id + '/read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (card) {
                    card.classList.remove('unread');
                    var dot = card.querySelector('.pulse-dot');
                    if (dot) dot.remove();
                    btnElement.remove();
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
            if (card) {
                card.classList.remove('unread');
                btnElement.remove();
            }
        });
    }

    function dismissNotification(id) {
        var card = document.getElementById('notif-card-' + id);
        if (card) {
            card.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9) translateY(15px)';
            setTimeout(function() {
                card.remove();
                // Check if last element was dismissed to inject empty state
                var list = document.querySelector('.notif-list');
                if (list && list.querySelectorAll('.notif-item').length === 0) {
                    list.innerHTML = `
                        <div class="notif-empty-state">
                            <div class="empty-bell-wrapper">
                                <i class="fas fa-bell-slash empty-bell-icon"></i>
                            </div>
                            <h3>You're all caught up</h3>
                            <p>You don't have any notifications at the moment. We'll let you know when something comes up!</p>
                        </div>
                    `;
                }
            }, 400);
        }
    }

    function markAllNotificationsRead(btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch('/api/v1/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error marking all notifications read:', error);
            window.location.reload();
        });
    }
</script>
@endsection
