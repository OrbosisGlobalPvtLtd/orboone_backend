@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'announcements'])

@section('_content')
<style>
    /* ----- Page Signature Header ----- */
    .hero-header {
        background: linear-gradient(135deg, #4b00e8 0%, #ec4e74 100%);
        border-radius: 16px;
        padding: 40px 30px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(75, 0, 232, 0.2);
        position: relative;
        overflow: hidden;
    }
    
    .hero-header::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    /* ----- Announcement Cards ----- */
    .announcement-card {
        background: white;
        border: none;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        border-left: 5px solid transparent;
    }
    
    .announcement-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        border-left-color: #4b00e8;
    }

    .card-meta {
        font-size: 0.75rem;
        color: #777;
        margin-bottom: 10px;
    }

    .badge-dept {
        background: rgba(75, 0, 232, 0.08);
        color: #4b00e8;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.65rem;
        padding: 5px 10px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }

    .avatar-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f0f2f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4b00e8;
    }

    /* ----- Custom Buttons ----- */
    .btn-create {
        background: white;
        color: #4b00e8;
        font-weight: 700;
        border: none;
        padding: 10px 25px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    .btn-create:hover {
        background: #f8f9fa;
        transform: scale(1.05);
        color: #ec4e74;
    }

    .btn-print {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        border-radius: 10px;
        padding: 10px 20px;
    }
    .btn-print:hover { color: white; background: rgba(255,255,255,0.3); }

    .desc-preview {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: #555;
        font-size: 0.9rem;
    }
</style>

<div class="container-fluid py-4 px-md-5">
    
    {{-- Header Section --}}
    <div class="hero-header d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-left">
        <div class="mb-3 mb-md-0">
            <h1 class="font-weight-bold mb-1">Notice / Announcements</h1>
            <p class="opacity-75 mb-0">Stay updated with the latest company news and official directives.</p>
        </div>
        <div class="d-flex gap-3">
            @if (collect($accesses)->where('menu_id', 6)->first()->status == 2)
                <a href="{{ route('announcements.create') }}" class="btn-create text-decoration-none">
                    <i class="fas fa-plus mr-1"></i> New Notice
                </a>
            @endif
            <a href="{{ route('announcements.print') }}" class="btn-print ml-2 text-decoration-none" target="_blank">
                <i class="fas fa-print mr-1"></i> Print List
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-lg mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
        </div>
    @endif

    {{-- Announcement Grid --}}
    <div class="row">
        @forelse ($announcements as $announcement)
            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                <div class="announcement-card card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge-dept">
                            {{ $announcement->department_id ? $announcement->department->name : 'Global Access' }}
                        </span>
                        <div class="avatar-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                    </div>

                    <h5 class="font-weight-bold mb-2">
                        <a href="{{ route('announcements.show', ['announcement' => $announcement->id]) }}" class="text-dark text-decoration-none">
                            {{ $announcement->title }}
                        </a>
                    </h5>

                    <p class="desc-preview mb-4">
                        {{ $announcement->description }}
                    </p>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <div class="card-meta d-flex flex-column">
                            <span class="font-weight-bold text-dark">{{ $announcement->creator->name ?? 'Admin' }}</span>
                            <span>{{ \Carbon\Carbon::parse($announcement->created_at)->format('d M, Y') }}</span>
                        </div>
                        
                        <div class="d-flex gap-2">
                            @if($announcement->attachment)
                                <a href="{{ asset('/storage/'. $announcement->attachment ) }}" 
                                   target="_blank" class="btn btn-sm btn-light rounded-pill" title="View Attachment">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                            @endif
                            <a href="{{ route('announcements.show', ['announcement' => $announcement->id]) }}" 
                               class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="opacity-25 mb-3">
                    <i class="fas fa-bullhorn fa-4x"></i>
                </div>
                <h5 class="text-muted">No announcements found at the moment.</h5>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $announcements->links() }}
    </div>

</div>
@endsection