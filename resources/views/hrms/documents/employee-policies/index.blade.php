@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Company Policies')

@section('_head')
@include('hrms.documents.partials.styles')
@endsection

@section('_content')
<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; POLICY PORTAL
            </div>
            <h1>Company Policies</h1>
            <p>Access the latest organization compliance policies, employee handbooks, and operational guidelines.</p>
        </div>
    </div>

    <!-- Policies Grid -->
    <div class="row">
        @forelse($policies as $policy)
            <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-4">
                <div class="dm-card h-100 d-flex flex-column justify-content-between" style="padding: 24px;">
                    <div>
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <span class="dm-badge dm-badge-success" style="font-size: 10px; padding: 4px 10px;">
                                <i class="fas fa-gavel mr-1"></i> Policy
                            </span>
                            <span class="text-muted" style="font-size: 11px; font-weight: 700;">
                                {{ $policy->documentType->name ?? 'General' }}
                            </span>
                        </div>

                        <h4 style="font-weight: 900; color: var(--dm-text); font-size: 16px; margin: 0 0 10px;">
                            {{ $policy->title }}
                        </h4>

                        @if($policy->description)
                            <p class="text-muted" style="font-size: 13px; font-weight: 600; line-height: 1.5; margin-bottom: 20px;">
                                {{ Str::limit($policy->description, 120) }}
                            </p>
                        @else
                            <p class="text-muted" style="font-size: 13px; font-style: italic; margin-bottom: 20px;">
                                No summary description provided.
                            </p>
                        @endif
                    </div>

                    @if($policy->file_path)
                        <a href="{{ route('hrms.documents.file', $policy->file_path) }}" target="_blank" class="dm-btn dm-btn-gradient w-100" style="height: 38px; border-radius: 9px; font-size: 12px;">
                            <i class="fas fa-file-pdf mr-1"></i> View Policy Document
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dm-card text-center py-5">
                    <div style="font-size: 40px; color: var(--dm-muted);"><i class="fas fa-folder-open"></i></div>
                    <h5 class="mt-3" style="font-weight: 800; color: var(--dm-text);">No Policies Published</h5>
                    <p class="text-muted" style="font-size: 13px;">Check back later for updated organization policies.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
