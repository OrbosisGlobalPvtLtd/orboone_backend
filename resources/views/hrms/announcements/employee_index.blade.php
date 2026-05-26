@extends('layouts.panel', ['active' => 'announcements'])

@section('page_title', 'Notice & Announcements')

@section('_head')
@include('settings.partials.styles')
<style>
    .ann-page {
        background: var(--set-bg);
        padding: 24px;
        min-height: calc(100vh - 90px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .ann-hero {
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary));
        border-radius: 26px;
        padding: 32px;
        color: #fff;
        box-shadow: 0 10px 30px rgba(75, 0, 232, 0.15);
        margin-bottom: 28px;
    }

    .ann-kicker {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.9;
        margin-bottom: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .ann-title-hero {
        font-size: 28px;
        font-weight: 900;
        margin: 0;
        line-height: 1.15;
    }

    .ann-subtitle-hero {
        font-size: 13px;
        font-weight: 600;
        margin: 8px 0 0;
        opacity: 0.85;
    }

    .ann-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    @media (max-width: 1200px) {
        .ann-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .ann-grid {
            grid-template-columns: 1fr;
        }
    }

    .ann-card {
        background: #fff;
        border: 1px solid var(--set-border);
        border-radius: 22px;
        box-shadow: var(--set-shadow);
        padding: 26px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .ann-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(16, 24, 40, .12);
    }

    .ann-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--set-primary);
    }

    .ann-card.priority-urgent::before { background: #ef4444; }
    .ann-card.priority-high::before { background: #f59e0b; }
    .ann-card.priority-normal::before { background: #3b82f6; }
    .ann-card.priority-low::before { background: #9ca3af; }

    .ann-badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        text-transform: capitalize;
        display: inline-block;
    }

    .badge-general { background: #EEF2FF; color: #3538CD; }
    .badge-holiday { background: #ECFDF3; color: #027A48; }
    .badge-emergency { background: #FEF3F2; color: #B42318; }
    .badge-policy { background: #F4F3FF; color: #5925DC; }
    .badge-meeting { background: #FFF7E6; color: #B54708; }

    .priority-indicator {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.5px;
    }

    .priority-urgent { color: #ef4444; }
    .priority-high { color: #f59e0b; }
    .priority-normal { color: #3b82f6; }
    .priority-low { color: #9ca3af; }

    .ann-card-title {
        font-size: 18px;
        font-weight: 850;
        color: var(--set-text);
        margin: 12px 0 8px;
        line-height: 1.35;
    }

    .ann-desc {
        font-size: 13px;
        color: var(--set-muted);
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-weight: 500;
    }

    .ann-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--set-border);
        padding-top: 16px;
        margin-top: auto;
    }

    .ann-date {
        font-size: 12px;
        color: var(--set-muted);
        font-weight: 700;
    }

    .btn-view {
        background: var(--set-soft);
        color: var(--set-primary);
        border: 1px solid var(--set-border);
        border-radius: 12px;
        font-weight: 800;
        font-size: 12px;
        padding: 8px 16px;
        transition: all 0.2s;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-view:hover {
        background: var(--set-primary);
        color: #fff;
        border-color: var(--set-primary);
    }

    .empty-state {
        text-align: center;
        padding: 64px 20px;
        background: #fff;
        border-radius: 26px;
        box-shadow: var(--set-shadow);
        border: 1px solid var(--set-border);
    }
    
    .empty-state i {
        font-size: 60px;
        color: var(--set-soft);
        margin-bottom: 20px;
    }
    
    .gap-2 {
        gap: 8px;
    }
</style>
@endsection

@section('_content')
<div class="ann-page">
    
    <!-- Hero Header -->
    <div class="ann-hero">
        <div>
            <div class="ann-kicker">
                <i class="fas fa-bullhorn"></i> EMPLOYEE &bull; BROADCASTS
            </div>
            <h1 class="ann-title-hero">Notice &amp; Announcements</h1>
            <p class="ann-subtitle-hero">Stay updated with the latest company news, policies, holidays, and official updates.</p>
        </div>
    </div>

    @if($announcements->count() > 0)
    <div class="ann-grid">
        @foreach($announcements as $item)
        <div class="ann-card priority-{{ $item->priority }}">
            <div class="d-flex justify-content-between align-items-start">
                <span class="ann-badge badge-{{ $item->type }}">{{ $item->type }}</span>
                <div class="priority-indicator priority-{{ $item->priority }}">
                    <i class="fas fa-circle" style="font-size: 7px;"></i>
                    {{ ucfirst($item->priority) }}
                </div>
            </div>
            
            <h4 class="ann-card-title">{{ $item->title }}</h4>
            <div class="ann-desc">
                {!! strip_tags($item->description) !!}
            </div>

            <div class="ann-footer">
                <div class="ann-date">
                    <i class="far fa-calendar-alt me-1 text-primary"></i>
                    {{ $item->created_at->format('d M, Y') }}
                </div>
                <div class="d-flex gap-2">
                    @if($item->attachment)
                    <a href="{{ $item->attachment_url ?? route('hrms.documents.file', ['path' => $item->attachment]) }}" target="_blank" class="btn-view p-2" title="View Attachment" style="width: 34px; height: 34px; justify-content: center; padding: 0 !important;">
                        <i class="fas fa-paperclip"></i>
                    </a>
                    @endif
                    <a href="{{ route('employee.announcements.show', $item->id) }}" class="btn-view">
                        Read Full <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $announcements->links() }}
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-bullhorn text-light"></i>
        <h4 class="font-weight-black">No Announcements Found</h4>
        <p class="text-muted mt-2">There are no active notices or announcements at the moment.</p>
    </div>
    @endif
</div>
@endsection
