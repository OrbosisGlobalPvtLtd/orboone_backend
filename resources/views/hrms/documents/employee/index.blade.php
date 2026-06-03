@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'My Documents')

@section('_head')
@include('hrms.documents.partials.styles')
<style>
    .doc-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
        gap: 20px !important;
        padding: 24px !important;
    }

    /* Premium Status-Aware Enterprise Cards */
    .doc-card {
        background: #ffffff !important;
        border: 1.5px solid #F1F5F9 !important;
        border-radius: 22px !important;
        padding: 24px 22px !important;
        box-shadow: 0 10px 25px -5px rgba(75, 0, 232, 0.02), 0 8px 16px -6px rgba(75, 0, 232, 0.01) !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: hidden !important;
        min-height: 235px !important;
    }

    .doc-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 20px 35px -5px rgba(75, 0, 232, 0.08), 0 10px 20px -8px rgba(75, 0, 232, 0.06) !important;
    }

    /* Dynamic Border Accents by Status */
    .doc-card.status-verified {
        border-left: 4.5px solid #10B981 !important;
        border-color: rgba(16, 185, 129, 0.12) !important;
    }
    .doc-card.status-verified:hover {
        border-color: rgba(16, 185, 129, 0.35) !important;
        box-shadow: 0 20px 35px -5px rgba(16, 185, 129, 0.12), 0 10px 20px -8px rgba(16, 185, 129, 0.06) !important;
    }

    .doc-card.status-pending {
        border-left: 4.5px solid #F59E0B !important;
        border-color: rgba(245, 158, 11, 0.12) !important;
    }
    .doc-card.status-pending:hover {
        border-color: rgba(245, 158, 11, 0.35) !important;
        box-shadow: 0 20px 35px -5px rgba(245, 158, 11, 0.12), 0 10px 20px -8px rgba(245, 158, 11, 0.06) !important;
    }

    .doc-card.status-rejected {
        border-left: 4.5px solid #EF4444 !important;
        border-color: rgba(239, 68, 68, 0.12) !important;
    }
    .doc-card.status-rejected:hover {
        border-color: rgba(239, 68, 68, 0.35) !important;
        box-shadow: 0 20px 35px -5px rgba(239, 68, 68, 0.12), 0 10px 20px -8px rgba(239, 68, 68, 0.06) !important;
    }

    .doc-card.status-missing {
        border-left: 4.5px solid #64748B !important;
        border-color: rgba(100, 116, 139, 0.12) !important;
    }
    .doc-card.status-missing:hover {
        border-color: rgba(100, 116, 139, 0.35) !important;
        box-shadow: 0 20px 35px -5px rgba(100, 116, 139, 0.12), 0 10px 20px -8px rgba(100, 116, 139, 0.06) !important;
    }

    /* Icon Avatars by Status */
    .doc-avatar-box {
        width: 42px !important;
        height: 42px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 15px !important;
        flex-shrink: 0 !important;
        transition: all 0.2s ease !important;
    }

    .status-verified .doc-avatar-box { background: rgba(16, 185, 129, 0.08) !important; color: #10B981 !important; }
    .status-pending .doc-avatar-box { background: rgba(245, 158, 11, 0.08) !important; color: #F59E0B !important; }
    .status-rejected .doc-avatar-box { background: rgba(239, 68, 68, 0.08) !important; color: #EF4444 !important; }
    .status-missing .doc-avatar-box { background: rgba(100, 116, 139, 0.08) !important; color: #64748B !important; }

    .doc-top {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        gap: 12px !important;
        margin-bottom: 14px !important;
    }

    .doc-name-wrapper {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
        min-width: 0 !important;
        flex: 1 !important;
    }

    .doc-middle {
        margin: 14px 0 !important;
        flex-grow: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
    }

    .doc-file-info {
        background: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 12px !important;
        padding: 10px 14px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
    }

    .doc-file-name {
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--dm-text) !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 170px !important;
    }

    .doc-bottom {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px !important;
        border-top: 1px solid #F1F5F9 !important;
        padding-top: 14px !important;
    }
</style>
@endsection

@section('_content')
@php
$isDisabled = ($employee->profile && in_array($employee->profile->profile_status, ['submitted', 'approved']));
@endphp

<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; DOCUMENT MANAGEMENT
            </div>
            <h1>My Documents</h1>
            <p>Upload, update, and manage your required compliance and verification documents.</p>
        </div>
        <div class="dm-hero-actions">
            @if(!$employee->profile || $employee->profile->profile_status == 'pending' || $employee->profile->profile_status == 'rejected')
            <form action="{{ route('hrms.documents.self.submit_verification') }}" method="POST">
                @csrf
                <button type="submit" class="dm-btn dm-btn-primary" onclick="return confirm('Are you sure you want to submit? Editing will be disabled.')">
                    <i class="fas fa-paper-plane"></i> Submit for Verification
                </button>
            </form>
            @elseif($employee->profile->profile_status == 'submitted')
            <span class="dm-badge dm-badge-warning" style="font-size: 13px; padding: 8px 16px;"><i class="fas fa-hourglass-half mr-1"></i> Under Verification</span>
            @elseif($employee->profile->profile_status == 'approved')
            <span class="dm-badge dm-badge-success" style="font-size: 13px; padding: 8px 16px;"><i class="fas fa-check-circle mr-1"></i> Verified</span>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <!-- Checklist Card -->
    <div class="dm-card">
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <h5 class="dm-table-title">Document Checklist</h5>
                    <p class="dm-table-subtitle">Complete all mandatory document uploads to enable verification submission.</p>
                </div>
            </div>
        </div>

        <div class="doc-grid">
            @foreach($documentTypes as $type)
            @php
            $doc = $documents->get($type->id);
            // Lock only if the profile is disabled AND (the document already exists OR the document is mandatory).
            // If the document is optional AND has not been uploaded yet, do NOT lock it!
            $isCardLocked = $isDisabled && ($doc || $type->is_mandatory);
            
            // Resolve Status Class
            $statusClass = 'status-missing';
            if ($doc) {
                if ($doc->verification_status == 'verified') $statusClass = 'status-verified';
                elseif ($doc->verification_status == 'pending') $statusClass = 'status-pending';
                else $statusClass = 'status-rejected';
            }
            @endphp

            <div class="doc-card {{ $statusClass }}">
                <div class="doc-top" style="display: flex !important; align-items: center !important; justify-content: space-between !important; width: 100% !important; margin-bottom: 0 !important;">
                    <div class="d-flex align-items-center gap-3" style="min-width: 0; flex: 1;">
                        <div class="doc-avatar-box shadow-sm">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="doc-name-wrapper" style="min-width: 0; flex: 1;">
                            <h5 style="font-size: 14px; font-weight: 850; color: var(--dm-text); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $type->name }}">
                                {{ $type->name }}
                            </h5>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @if($type->is_mandatory)
                                <span class="dm-badge dm-badge-danger" style="font-size: 8px; font-weight: 850; padding: 2px 6px; border-radius: 5px; letter-spacing: 0.02em;">MANDATORY</span>
                                @else
                                <span class="dm-badge dm-badge-secondary" style="font-size: 8px; font-weight: 850; padding: 2px 6px; border-radius: 5px; letter-spacing: 0.02em; background: #F1F5F9 !important; color: #475569 !important;">OPTIONAL</span>
                                @endif

                                @if(($type->has_expiry_date ?? false) || ($type->has_expiry ?? false))
                                <span class="dm-badge dm-badge-warning" style="font-size: 8px; font-weight: 850; padding: 2px 6px; border-radius: 5px; letter-spacing: 0.02em;">EXPIRY REQ</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="flex-shrink: 0; margin-left: 8px;">
                        @if($doc)
                        @if($doc->verification_status == 'pending')
                        <span class="dm-badge dm-badge-warning" style="font-size: 9px; font-weight: 850; padding: 4px 10px; border-radius: 8px;"><i class="fas fa-hourglass-half mr-1"></i> PENDING</span>
                        @elseif($doc->verification_status == 'verified')
                        <span class="dm-badge dm-badge-success" style="font-size: 9px; font-weight: 850; padding: 4px 10px; border-radius: 8px;"><i class="fas fa-check-circle mr-1"></i> VERIFIED</span>
                        @else
                        <span class="dm-badge dm-badge-danger" style="font-size: 9px; font-weight: 850; padding: 4px 10px; border-radius: 8px;"><i class="fas fa-times-circle mr-1"></i> REJECTED</span>
                        @endif
                        @else
                        <span class="dm-badge dm-badge-secondary" style="font-size: 9px; font-weight: 850; padding: 4px 10px; border-radius: 8px; background: #F1F5F9 !important; color: #64748B !important;"><i class="fas fa-minus-circle mr-1"></i> MISSING</span>
                        @endif
                    </div>
                </div>

                <div class="doc-middle" style="margin: 18px 0 !important;">
                    @if($doc && $doc->file_path)
                    <div class="doc-file-info">
                        <div class="d-flex align-items-center gap-2" style="min-width: 0; flex: 1;">
                            <i class="fas fa-paperclip text-muted" style="font-size: 14px; flex-shrink: 0;"></i>
                            <span class="doc-file-name" title="{{ $doc->file_original_name ?? basename($doc->file_path) }}" style="flex: 1; min-width: 0;">
                                {{ $doc->file_original_name ?? basename($doc->file_path) }}
                            </span>
                        </div>
                        <a href="{{ route('hrms.documents.file', $doc->file_path) }}" target="_blank" class="dm-action-btn-pill dm-action-btn-primary" style="height: 28px; padding: 0 10px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-weight: 850;">
                            <i class="fas fa-eye" style="font-size: 11px;"></i> View
                        </a>
                    </div>
                    @else
                    <div class="text-center py-3 text-muted" style="font-size: 12px; font-weight: 850; border: 1.5px dashed #E2E8F0; border-radius: 14px; background: #F8FAFC; color: #94A3B8;">
                        <i class="fas fa-cloud-upload-alt mr-1" style="font-size: 14px;"></i> No file uploaded
                    </div>
                    @endif

                    @if($doc && $doc->rejection_reason)
                    <div class="text-danger mt-2 p-2.5 rounded" style="font-size: 11px; font-weight: 800; background: #FFF5F5; border-left: 3px solid #E53E3E; line-height: 1.4;">
                        <strong>Reason:</strong> {{ $doc->rejection_reason }}
                    </div>
                    @endif
                </div>

                <div class="doc-bottom">
                    <div class="d-flex align-items-center gap-2 w-100 justify-content-end">
                        @if(!$isCardLocked || ($doc && $doc->verification_status == 'rejected'))
                        @if($doc)
                        <button type="button" class="dm-action-btn-pill dm-action-btn-light" data-toggle="modal" data-target="#replaceModal{{ $type->id }}" style="height: 32px; font-size: 12px; padding: 0 14px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                            <i class="fas fa-sync-alt text-warning"></i> Replace
                        </button>

                        <form action="{{ route('hrms.documents.self.destroy', $doc->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dm-action-btn-pill dm-action-btn-danger" onclick="return confirm('Delete this document?')" style="height: 32px; font-size: 12px; padding: 0 14px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                        @else
                        <button type="button" class="dm-action-btn-pill dm-action-btn-primary" data-toggle="modal" data-target="#uploadModal{{ $type->id }}" style="height: 32px; font-size: 12px; padding: 0 16px; border-radius: 10px; background: var(--dm-primary); color: #fff; box-shadow: 0 4px 10px rgba(75, 0, 232, 0.15); display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Upload Document
                        </button>
                        @endif
                        @else
                        <span class="dm-badge dm-badge-secondary" style="font-size: 10px; font-weight: 850; padding: 6px 14px; border-radius: 8px; background: #F8FAFC !important; border: 1px solid #E2E8F0; color: #64748B !important; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-lock text-muted" style="font-size: 11px;"></i> Locked for Verification
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Modals Container (Rendered outside card grid to prevent flex/overflow clipping) --}}
@foreach($documentTypes as $type)
@php
$doc = $documents->get($type->id);
@endphp
<!-- Upload/Replace Modal -->
<div class="modal fade" id="{{ $doc ? 'replace' : 'upload' }}Modal{{ $type->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content" style="border: none; border-radius: 24px; overflow: hidden; box-shadow: var(--dm-shadow);">
            <form action="{{ $doc ? route('hrms.documents.self.replace', $doc->id) : route('hrms.documents.self.upload') }}"
                method="POST"
                enctype="multipart/form-data">
                @csrf

                @if(!$doc)
                <input type="hidden" name="document_type_id" value="{{ $type->id }}">
                @endif

                <div class="dm-modal-header">
                    <h5 class="modal-title"><i class="fas fa-cloud-upload-alt mr-2"></i>{{ $doc ? 'Replace' : 'Upload' }} {{ $type->name }}</h5>
                    <p>Supported formats: PDF, JPG, JPEG, PNG, WEBP (Max 5MB)</p>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>

                <div class="dm-modal-body">
                    <div class="dm-form-group" style="text-align: left;">
                        <label style="font-weight: 700; font-size: 13px; color: var(--dm-text); margin-bottom: 8px; display: block;">Select File</label>
                        <label class="eo-file-upload" style="margin: 0; width: 100%;">
                            <div class="eo-file-left">
                                <div class="eo-file-icon"><i class="fas fa-paperclip"></i></div>
                                <div class="eo-file-title selected-file-name" style="font-weight: 700;">Choose file...</div>
                            </div>

                            <span class="eo-file-browse">
                                <i class="fas fa-folder-open"></i> Browse
                            </span>

                            <input type="file"
                                name="file"
                                class="eo-file-input js-file-input"
                                required
                                accept=".pdf,.jpg,.jpeg,.png,.webp">
                        </label>
                    </div>

                    @if(($type->has_expiry_date ?? false) || ($type->has_expiry ?? false))
                    <div class="dm-form-group mt-3" style="text-align: left;">
                        <label style="font-weight: 700; font-size: 13px; color: var(--dm-text); margin-bottom: 8px; display: block;">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" value="{{ $doc && $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : '' }}" required>
                    </div>
                    @endif
                </div>

                <div class="dm-modal-footer">
                    <button type="button" class="dm-btn dm-btn-dark-light" style="height: 38px;" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="dm-btn dm-btn-gradient" style="height: 38px;">
                        <i class="fas fa-save"></i> Save Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    document.querySelectorAll('.js-file-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const box = this.closest('.eo-file-upload').querySelector('.selected-file-name');
            box.textContent = this.files && this.files.length ? this.files[0].name : 'Choose file...';
        });
    });
</script>
@endsection