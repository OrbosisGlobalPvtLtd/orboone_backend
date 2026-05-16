@extends('layouts.panel', ['active' => 'notifications'])

@section('page_title', 'All Notifications')

@section('_content')
<style>
    .notif-page { min-height: calc(100vh - 90px); padding: 16px 10px 30px; background: #F6F7FB; }
    .notif-container { max-width: 1000px; margin: 0 auto; }
    .notif-card { background: #fff; border: 1px solid #E7EAF3; border-radius: 20px; box-shadow: 0 10px 28px rgba(16, 24, 40, .06); padding: 18px; }
    .notif-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .notif-title { margin: 0; color: #101828; font-size: 20px; font-weight: 900; }
    .notif-list { display: flex; flex-direction: column; gap: 12px; }
    .notif-item { 
        display: flex; gap: 16px; padding: 16px; border: 1px solid #E7EAF3; border-radius: 16px; 
        background: #fff; transition: 0.2s; text-decoration: none !important; color: inherit;
        position: relative;
    }
    .notif-item:hover { background: #F9FAFB; border-color: #D1D5DB; }
    .notif-item.unread { background: #FDFBFF; border-left: 4px solid #4B00E8; }
    .notif-icon { 
        width: 44px; height: 44px; border-radius: 12px; background: #F4F2FF; color: #4B00E8; 
        display: flex; align-items:center; justify-content:center; flex: 0 0 auto; font-size: 18px;
    }
    .notif-content { flex: 1; min-width: 0; }
    .notif-item-title { font-size: 15px; font-weight: 900; color: #101828; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
    .notif-item-msg { font-size: 13px; font-weight: 600; color: #667085; line-height: 1.5; }
    .notif-meta { margin-top: 8px; font-size: 11px; font-weight: 700; color: #98A2B3; display: flex; align-items: center; gap: 12px; }
    .notif-badge { 
        display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 10px; font-weight: 900; 
        text-transform: uppercase; 
    }
    .badge-new { background: rgba(75, 0, 232, 0.1); color: #4B00E8; }
    .badge-read { background: #F2F4F7; color: #667085; }
    
    .pagination-wrapper { margin-top: 24px; }
    .pagination { justify-content: center; }
</style>

<div class="notif-page">
    <div class="notif-container">
        <div class="notif-card">
            <div class="notif-header">
                <h1 class="notif-title">
                    <i class="fas fa-bell mr-2" style="color:#4B00E8;"></i> Notifications
                </h1>
            </div>

            <div class="notif-list">
                @forelse($notifications as $notification)
                    <a href="{{ route('notifications.open', $notification->id) }}" class="notif-item {{ !$notification->is_read ? 'unread' : '' }}">
                        <div class="notif-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-item-title">
                                {{ $notification->title ?? 'Notification' }}
                                @if(!$notification->is_read)
                                    <span class="notif-badge badge-new">New</span>
                                @else
                                    <span class="notif-badge badge-read">Read</span>
                                @endif
                            </div>
                            <div class="notif-item-msg">
                                {{ $notification->message ?? '-' }}
                            </div>
                            <div class="notif-meta">
                                <span><i class="far fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}</span>
                                <span><i class="far fa-calendar-alt mr-1"></i> {{ $notification->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="padding:40px; text-align:center; color:#667085;">
                        <h5 class="fw-bold">All caught up!</h5>
                        <p class="mb-0">You have no notifications yet.</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="pagination-wrapper">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
