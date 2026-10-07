@extends('layouts.panel', ['active' => 'notifications'])

@section('page_title', 'All Notifications')

@section('_content')
<style>
    /* CSS Variables for design consistency */
    :root {
        --orb-bg: linear-gradient(180deg, #F6F7FB 0%, #EEF2FF 100%);
        --orb-card: #ffffff;
        --orb-text: #101828;
        --orb-text-muted: #667085;
        --orb-border: #E7EAF3;
        --orb-unread-bg: rgba(75, 0, 232, 0.02);
        --orb-unread-border: var(--orb-primary);
        --orb-shadow: 0 10px 30px rgba(16, 24, 40, 0.04);
        --orb-shadow-hover: 0 14px 34px rgba(75, 0, 232, 0.08);
    }

    .notif-page {
        min-height: calc(100vh - 90px);
        padding: 24px;
        background: var(--orb-bg);
        width: 100%;
        box-sizing: border-box;
    }

    .notif-container {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Header Hero Card */
    .notif-hero-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 20px;
        padding: 20px 24px;
        box-shadow: var(--orb-shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .notif-hero-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .notif-hero-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.2);
    }

    .notif-hero-info h1 {
        margin: 0 0 2px;
        font-size: 20px;
        font-weight: 800;
        color: var(--orb-text);
        letter-spacing: -0.01em;
    }

    .notif-hero-info p {
        margin: 0;
        font-size: 12.5px;
        color: var(--orb-text-muted);
        font-weight: 500;
    }

    .notif-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .unread-badge {
        background: rgba(75, 0, 232, 0.1);
        color: var(--orb-primary);
        font-weight: 800;
        font-size: 11.5px;
        padding: 5px 12px;
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
        gap: 6px;
        padding: 8px 14px;
        border-radius: 12px;
        font-size: 12px;
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
    }

    /* Notification List Section */
    .notif-list-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--orb-shadow);
        width: 100%;
        box-sizing: border-box;
    }

    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* Notification Items */
    .notif-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 18px;
        border: 1px solid #EEF2F6;
        border-radius: 14px;
        background: #ffffff;
        text-decoration: none !important;
        color: inherit !important;
        position: relative;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
    }

    .notif-item:hover {
        background: #F8FAFC;
        border-color: #E2E8F0;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    /* Unread Card Style */
    .notif-item.unread {
        background: #FAF9FF;
        border-left: 3.5px solid var(--orb-primary);
    }

    /* Category Icon */
    .notif-icon-circle {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 15px;
        flex-shrink: 0;
        margin-top: 1px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .notif-body {
        flex: 1 1 0%;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .notif-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }

    .notif-item-title {
        font-size: 13.5px;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        line-height: 1.35;
        word-break: normal;
        overflow-wrap: break-word;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex: 1 1 auto;
    }

    .notif-header-right {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .notif-time-badge {
        font-size: 11px;
        font-weight: 700;
        color: #94A3B8;
        white-space: nowrap;
    }

    .pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--orb-primary);
        box-shadow: 0 0 0 0 rgba(75, 0, 232, 0.4);
        animation: pulse-dot 1.5s infinite;
        flex-shrink: 0;
        display: inline-block;
    }

    @keyframes pulse-dot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(75, 0, 232, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(75, 0, 232, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(75, 0, 232, 0); }
    }

    .notif-item-msg {
        font-size: 12px;
        color: #64748B;
        line-height: 1.45;
        font-weight: 500;
        margin: 0;
        word-break: normal;
        overflow-wrap: break-word;
    }

    /* Attachment Chip */
    .notif-attachment-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 700;
        color: #475569;
        margin-top: 3px;
        width: fit-content;
        text-decoration: none !important;
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

    .notif-attachment-badge.image {
        background: #ECFDF5;
        border-color: #D1FAE5;
        color: #10B981;
    }

    /* Footer Row & Meta */
    .notif-footer-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 5px;
        flex-wrap: wrap;
    }

    .notif-meta-row {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10.5px;
        font-weight: 600;
        color: #94A3B8;
        flex-wrap: wrap;
    }

    .notif-actions {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    .btn-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 700;
        text-decoration: none !important;
        transition: all 0.2s ease;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #64748B;
        cursor: pointer;
        line-height: 1.2;
    }

    .btn-pill:hover {
        background: #F8FAFC;
        color: #0F172A;
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

    /* Empty State */
    .notif-empty-state {
        padding: 60px 20px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .empty-bell-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 24px;
        background: linear-gradient(135deg, rgba(75, 0, 232, 0.05), rgba(134, 0, 238, 0.05));
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
    }

    .empty-bell-icon {
        font-size: 34px;
        color: var(--orb-primary);
    }

    .notif-empty-state h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--orb-text);
    }

    .notif-empty-state p {
        margin: 0;
        font-size: 13px;
        color: var(--orb-text-muted);
        max-width: 300px;
        line-height: 1.5;
        font-weight: 500;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    /* Responsive Breakpoints */
    @media (max-width: 768px) {
        .notif-page {
            padding: 10px 8px 30px;
        }
        .notif-container {
            gap: 10px;
        }
        .notif-hero-card {
            padding: 12px 14px;
            border-radius: 14px;
            gap: 10px;
            flex-direction: column;
            align-items: flex-start;
        }
        .notif-hero-left {
            width: 100%;
            gap: 10px;
        }
        .notif-hero-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 10px;
            font-size: 15px;
        }
        .notif-hero-info h1 {
            font-size: 16px;
        }
        .notif-hero-info p {
            font-size: 11px;
        }
        .notif-hero-actions {
            width: 100%;
            gap: 6px;
        }
        .unread-badge {
            width: 100%;
            justify-content: center;
            padding: 4px 10px;
            font-size: 11px;
        }
        .btn-hero-action {
            flex: 1 1 80px;
            padding: 5px 8px;
            font-size: 10.5px;
            border-radius: 8px;
            justify-content: center;
        }
        .notif-list-card {
            padding: 8px;
            border-radius: 14px;
        }
        .notif-item {
            padding: 10px;
            border-radius: 11px;
            gap: 9px;
        }
        .notif-icon-circle {
            width: 32px;
            height: 32px;
            min-width: 32px;
            font-size: 13px;
            border-radius: 8px;
        }
        .notif-item-title {
            font-size: 13px;
        }
        .notif-item-msg {
            font-size: 11.5px;
        }
        .notif-footer-row {
            gap: 6px;
        }
        .btn-pill {
            padding: 3px 6px;
            font-size: 10px;
        }
    }
</style>

<div class="notif-page">
    <div class="notif-container">
        <!-- Hero Header -->
        <div class="notif-hero-card">
            <div class="notif-hero-left">
                <div class="notif-hero-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="notif-hero-info">
                    <h1>Notifications</h1>
                    <p>Track announcements, approvals, reminders, and activities.</p>
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
                        <i class="fas fa-check-double"></i> Mark all read
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
                        $searchContext = strtolower(($notification->type ?? '') . ' ' . ($notification->title ?? ''));
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

                        // Parse Attachment URL safely
                        $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data, true) ?? []);
                        $attUrl = $data['attachment_url'] ?? $data['attachment'] ?? '';
                        $attName = $data['attachment_name'] ?? basename($attUrl) ?? 'Attachment';
                        $attType = $data['attachment_type'] ?? '';
                        if (empty($attType) && !empty($attUrl)) {
                            $ext = strtolower(pathinfo($attUrl, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) $attType = 'image';
                            elseif ($ext === 'pdf') $attType = 'pdf';
                            else $attType = 'document';
                        }
                    @endphp

                    <div id="notif-card-{{ $notification->id }}" class="notif-item {{ !$notification->is_read ? 'unread' : '' }}" onclick="handleNotificationClick(event, '{{ route('notifications.open', $notification->id) }}')">
                        <div class="notif-icon-circle" style="background: {{ $iconBg }}">
                            <i class="fas {{ $icon }}"></i>
                        </div>

                        <div class="notif-body">
                            <div class="notif-title-row">
                                <h3 class="notif-item-title">
                                    {{ $notification->title ?? 'Notification' }}
                                    @if(!$notification->is_read)
                                        <span class="pulse-dot"></span>
                                    @endif
                                </h3>
                                <div class="notif-header-right d-none d-md-inline-flex">
                                    <span class="notif-time-badge">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <p class="notif-item-msg">
                                {{ $notification->message ?? '-' }}
                            </p>

                            <!-- Attachment Chip badge -->
                            @if(!empty($attUrl))
                                <a href="{{ (str_starts_with($attUrl, 'http://') || str_starts_with($attUrl, 'https://') || str_starts_with($attUrl, '/')) ? $attUrl : asset('storage/' . $attUrl) }}" target="_blank" class="notif-attachment-badge {{ $attType }}" onclick="event.stopPropagation()">
                                    @if($attType === 'pdf')
                                        <i class="fas fa-file-pdf"></i> PDF
                                    @elseif($attType === 'image')
                                        <i class="fas fa-file-image"></i> Image
                                    @else
                                        <i class="fas fa-file-alt"></i> File
                                    @endif
                                </a>
                            @endif

                            <div class="notif-footer-row">
                                <div class="notif-meta-row">
                                    <span class="d-md-none"><i class="far fa-clock"></i> {{ $notification->created_at->diffForHumans() }} &bull; </span>
                                    <span><i class="far fa-calendar-alt"></i> {{ $notification->created_at->format('d M, h:i A') }}</span>
                                </div>

                                <!-- Action pills -->
                                <div class="notif-actions" onclick="event.stopPropagation()">
                                    <a href="{{ route('notifications.open', $notification->id) }}" class="btn-pill btn-pill-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if(!$notification->is_read)
                                        <button class="btn-pill" onclick="markSingleAsRead('{{ $notification->id }}', this)">
                                            <i class="fas fa-check"></i> Read
                                        </button>
                                    @endif
                                    <button class="btn-pill btn-pill-danger" onclick="dismissNotification('{{ $notification->id }}')" title="Dismiss">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
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

            <!-- Laravel Pagination Links -->
            @if($notifications->hasPages())
                <div class="pagination-wrapper">
                    {{ $notifications->onEachSide(1)->links() }}
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
        var routeUrl = "{{ route('notifications.mark_as_read', ':id') }}".replace(':id', id);
        
        fetch(routeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
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
        var routeUrl = "{{ route('notifications.destroy', ':id') }}".replace(':id', id);

        fetch(routeUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).catch(err => console.error('Error dismissing notification:', err));

        if (card) {
            card.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95) translateY(8px)';
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
            }, 300);
        }
    }

    function markAllNotificationsRead(btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch("{{ route('notifications.mark_all_read') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-double"></i> Mark all read';
            }
        })
        .catch(error => {
            console.error('Error marking all notifications read:', error);
            window.location.reload();
        });
    }
</script>
@endsection
