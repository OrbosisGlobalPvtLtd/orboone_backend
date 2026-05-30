@extends('layouts.panel', ['active' => 'announcements'])

@section('page_title', 'Announcement Details')

@section('_head')
@include('settings.partials.styles')
<style>
    .show-container {
        max-width: 960px;
        margin: 0 auto;
        padding: 24px 10px;
        font-family: 'Inter', sans-serif;
    }
    
    .set-header {
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary));
        color: #fff;
        border-radius: 26px;
        padding: 28px 32px;
        box-shadow: var(--set-shadow);
        margin-bottom: 24px;
    }
    
    .detail-card {
        background: #fff;
        border: 1px solid var(--set-border);
        border-radius: 22px;
        box-shadow: var(--set-shadow);
        padding: 32px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .detail-item {
        border-bottom: 1px solid var(--set-border);
        padding-bottom: 14px;
    }

    .detail-label {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        color: var(--set-muted);
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .detail-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--set-text);
    }

    .detail-body-text {
        font-size: 15px;
        line-height: 1.7;
        color: var(--set-text);
        background: #F8FAFC;
        border: 1px solid var(--set-border);
        border-radius: 16px;
        padding: 20px;
        font-weight: 500;
    }

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

    .priority-low { background: #F2F4F7; color: #344054; }
    .priority-normal { background: #EEF2FF; color: #3538CD; }
    .priority-high { background: #FFF9E6; color: #B54708; }
    .priority-urgent { background: #FFE4E8; color: #C01048; }

    .status-on { background: #ECFDF3; color: #027A48; }
    .status-off { background: #F2F4F7; color: #667085; }
    
    .gap-3 {
        gap: 12px;
    }
</style>
@endsection

@section('_content')
<div class="show-container">
    
    <!-- Hero Header -->
    <div class="set-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="set-kicker" style="color: rgba(255,255,255,0.9); font-weight: 850; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">
                <i class="fas fa-bullhorn"></i> Announcement Detail
            </div>
            <h1 class="set-title text-white font-weight-black mb-1" style="font-size: 26px; line-height: 1.2;">{{ $announcement->title }}</h1>
            <p class="set-subtitle mb-0 opacity-75" style="font-size: 13px; font-weight: 600;">Published on {{ $announcement->created_at->format('d M, Y') }}</p>
        </div>
        <div>
            <a href="{{ auth()->user()->hasPermission('announcements.view') || auth()->user()->hasPermission('announcements.manage') ? route('announcements.index') : route('employee.announcements.index') }}" class="set-btn" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: #fff !important; border-radius: 999px;">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Detail Card -->
    <div class="detail-card">
        
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Notice Type</div>
                <div class="detail-value">
                    <span class="ann-badge badge-{{ $announcement->type }}">{{ $announcement->type }}</span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Priority Level</div>
                <div class="detail-value">
                    <span class="ann-badge priority-{{ $announcement->priority }}">{{ $announcement->priority }}</span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Target Audience</div>
                <div class="detail-value">
                    <span class="badge badge-pill badge-light border px-2.5 py-1.5 font-weight-bold" style="font-size: 11px; color: var(--set-muted); text-transform: uppercase;">
                        {{ $announcement->target_type }}
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Publishing Status</div>
                <div class="detail-value">
                    @if($announcement->is_active)
                        <span class="ann-badge status-on"><i class="fas fa-check-circle mr-1"></i>Active / Published</span>
                    @else
                        <span class="ann-badge status-off"><i class="fas fa-times-circle mr-1"></i>Inactive / Draft</span>
                    @endif
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Published By</div>
                <div class="detail-value" style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--set-soft); color: var(--set-primary); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                        {{ strtoupper(substr($announcement->creator->name ?? 'S', 0, 1)) }}
                    </div>
                    <span>{{ $announcement->creator->name ?? 'System Admin' }}</span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Distribution Window</div>
                <div class="detail-value" style="font-size: 13px;">
                    @if($announcement->start_date || $announcement->end_date)
                        <i class="far fa-calendar-alt text-primary mr-1"></i>
                        {{ $announcement->start_date ? \Carbon\Carbon::parse($announcement->start_date)->format('d M Y') : 'Start Now' }}
                        to
                        {{ $announcement->end_date ? \Carbon\Carbon::parse($announcement->end_date)->format('d M Y') : 'Indefinite' }}
                    @else
                        <span class="text-muted font-weight-semibold">No date range restriction (Always visible)</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Description/Body Section -->
        <div class="mb-4">
            <div class="detail-label">Announcement Content</div>
            <div class="detail-body-text">
                {!! nl2br(e($announcement->description)) !!}
            </div>
        </div>

        <!-- Attachment Section -->
        @if($announcement->attachment)
        <div class="mb-4">
            <div class="detail-label">Attached Document / Asset</div>
            <div class="p-3 border rounded-lg bg-light d-flex align-items-center justify-content-between" style="border-radius: 14px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 text-danger bg-white border rounded-lg d-inline-flex align-items-center justify-content-center" style="border-radius: 10px; width: 40px; height: 40px;">
                        <i class="fas fa-file-pdf fa-lg"></i>
                    </div>
                    <div>
                        <span class="font-weight-bold d-block text-dark" style="font-size: 13px;">{{ basename($announcement->attachment) }}</span>
                        <small class="text-muted">Attachment File</small>
                    </div>
                </div>
                <a href="{{ route('hrms.announcements.attachment', $announcement->id) }}" target="_blank" class="set-btn" style="height: 38px; border-radius: 10px; padding: 0 16px;">
                    <i class="fas fa-download mr-1"></i> View / Download
                </a>
            </div>
        </div>
        @endif

        <!-- Action Row (Admin specific buttons if they have privileges) -->
        @php
            $hasAccessControl = isset($accesses) && collect($accesses)->where('menu_id', 6)->first();
            $canEdit = $hasAccessControl ? collect($accesses)->where('menu_id', 6)->first()->status == 2 : auth()->user()->hasPermission('announcements.edit');
        @endphp

        @if($canEdit && (auth()->user()->isAdmin() || $announcement->created_by_user_id == auth()->id()))
        <div class="border-top pt-4 mt-4 d-flex gap-3 justify-content-end">
            <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?')" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="set-btn set-btn-soft" style="color: #ef4444 !important; background: rgba(239,68,68,0.08); box-shadow: none;">
                    <i class="fas fa-trash-alt mr-1"></i> Delete Announcement
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection
