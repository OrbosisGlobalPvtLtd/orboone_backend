@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'document-management'])

@section('page_title', $policy->title)

@section('_head')
@include('settings.partials.styles')
<style>
    .policies-container {
        max-width: 800px;
        margin: 0 auto;
    }
</style>
@endsection

@section('_content')
<div class="set-page">
    <div class="policies-container">
        <!-- Premium Purple Gradient Hero Header -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-folder-open"></i> COMPANY &bull; DOCUMENT DETAILS
                </div>
                <h1 class="set-title">{{ $policy->title }}</h1>
                <p class="set-subtitle">Published on {{ $policy->created_at->format('d M, Y') }}</p>
            </div>
            <div>
                <a href="{{ route('policies.index') }}" class="set-btn set-btn-soft">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Repository
                </a>
            </div>
        </div>

        <div class="set-card mt-4 p-4">
            <div class="d-flex align-items-center mb-4">
                <div class="mr-3 p-3 text-danger d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; background: rgba(239, 68, 68, 0.08); width: 60px; height: 60px;">
                    <i class="fas fa-file-pdf fa-2x"></i>
                </div>
                <div>
                    <h4 class="font-weight-black mb-1" style="color: var(--set-text);">{{ $policy->title }}</h4>
                    <span class="set-badge">{{ $policy->category ?? 'General' }}</span>
                </div>
            </div>

            <hr>

            <div class="row my-4">
                <div class="col-sm-6">
                    <div class="text-muted small font-weight-bold uppercase mb-1">Target Audience</div>
                    <div style="font-size: 14px; font-weight: 700; color: var(--set-text);">
                        @php
                            $visibilities = $policy->visible_to;
                            if (is_string($visibilities)) {
                                $visibilities = json_decode($visibilities, true) ?? [$visibilities];
                            }
                            $visibilities = $visibilities ?? ['employee', 'admin'];
                        @endphp
                        @foreach($visibilities as $v)
                            <span class="badge badge-pill badge-light border px-2 py-1 small mr-1" style="font-weight: 700; color: var(--set-muted);">{{ ucfirst($v) }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-sm-6 mt-3 mt-sm-0">
                    <div class="text-muted small font-weight-bold uppercase mb-1">Revision Date</div>
                    <div style="font-size: 14px; font-weight: 700; color: var(--set-text);">
                        {{ $policy->updated_at ? $policy->updated_at->format('d M, Y H:i') : 'N/A' }}
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3">
                @php
                    $fileExists = $policy->file_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($policy->file_path);
                @endphp
                @if ($fileExists)
                    <a href="{{ route('hrms.company-documents.preview', $policy->id) }}" target="_blank" class="set-btn" style="height: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-eye"></i> Preview Document
                    </a>
                    <a href="{{ route('hrms.company-documents.download', $policy->id) }}" class="set-btn set-btn-soft" style="height: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                @else
                    <button class="set-btn" disabled style="opacity: 0.5; cursor: not-allowed; height: 42px; border-radius: 12px;">
                        <i class="fas fa-eye-slash"></i> Preview (N/A)
                    </button>
                    <button class="set-btn set-btn-soft" disabled style="opacity: 0.5; cursor: not-allowed; height: 42px; border-radius: 12px;">
                        <i class="fas fa-times"></i> Download (Missing)
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
