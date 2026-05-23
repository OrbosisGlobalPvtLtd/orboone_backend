@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'My Documents')

@section('_head')
@include('hrms.documents.partials.styles')
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

        <div class="dm-table-wrap">
            <table class="table dm-table">
                <thead>
                    <tr>
                        <th width="70">S.No</th>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>File</th>
                        <th width="260">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($documentTypes as $type)
                    @php
                    $doc = $documents->get($type->id);
                    @endphp

                    <tr>
                        <td>
                            <span class="d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 10px; background: var(--dm-soft); color: var(--dm-primary); font-weight: 800; font-size: 12px;">
                                {{ $loop->iteration }}
                            </span>
                        </td>

                        <td>
                            <div style="font-weight: 800; color: var(--dm-text); font-size: 14px;">{{ $type->name }}</div>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @if($type->is_mandatory)
                                <span class="dm-badge dm-badge-danger" style="font-size: 9px; padding: 2px 8px;">Mandatory</span>
                                @else
                                <span class="dm-badge dm-badge-secondary" style="font-size: 9px; padding: 2px 8px;">Optional</span>
                                @endif

                                @if(($type->has_expiry_date ?? false) || ($type->has_expiry ?? false))
                                <span class="dm-badge dm-badge-warning" style="font-size: 9px; padding: 2px 8px;">Expiry Required</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($doc)
                                @if($doc->verification_status == 'pending')
                                <span class="dm-badge dm-badge-warning"><i class="fas fa-hourglass-half mr-1"></i> Pending</span>
                                @elseif($doc->verification_status == 'verified')
                                <span class="dm-badge dm-badge-success"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                @else
                                <span class="dm-badge dm-badge-danger"><i class="fas fa-times-circle mr-1"></i> Rejected</span>
                                @if($doc->rejection_reason)
                                <div class="text-danger mt-1" style="font-size: 11px; font-weight: 700;">{{ $doc->rejection_reason }}</div>
                                @endif
                                @endif
                            @else
                            <span class="dm-badge dm-badge-secondary"><i class="fas fa-minus-circle mr-1"></i> Missing</span>
                            @endif
                        </td>

                        <td>
                            @if($doc && $doc->file_path)
                            <a href="{{ route('hrms.documents.file', $doc->file_path) }}" target="_blank" class="dm-action-btn-pill dm-action-btn-primary">
                                <i class="fas fa-eye"></i> View File
                            </a>
                            @else
                            <span class="text-muted" style="font-size: 12px;">No file uploaded</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if(!$isDisabled || ($doc && $doc->verification_status == 'rejected'))
                                    @if($doc)
                                    <button type="button" class="dm-action-btn-pill dm-action-btn-light" data-toggle="modal" data-target="#replaceModal{{ $type->id }}">
                                        <i class="fas fa-sync-alt text-warning"></i> Replace
                                    </button>

                                    <form action="{{ route('hrms.documents.self.destroy', $doc->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dm-action-btn-pill dm-action-btn-danger" onclick="return confirm('Delete this document?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" class="dm-action-btn-pill dm-action-btn-primary" data-toggle="modal" data-target="#uploadModal{{ $type->id }}">
                                        <i class="fas fa-cloud-upload-alt mr-1"></i> Upload
                                    </button>
                                    @endif
                                @else
                                <span class="text-muted" style="font-size: 12px; font-weight: 700;"><i class="fas fa-lock mr-1"></i> Locked</span>
                                @endif
                            </div>

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
                                                <div class="dm-form-group">
                                                    <label>Select File</label>
                                                    <label class="eo-file-upload" style="margin: 0;">
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
                                                <div class="dm-form-group mt-3">
                                                    <label>Expiry Date</label>
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
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.js-file-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const box = this.closest('.eo-file-upload').querySelector('.selected-file-name');
            box.textContent = this.files && this.files.length ? this.files[0].name : 'Choose file...';
        });
    });
</script>
@endsection