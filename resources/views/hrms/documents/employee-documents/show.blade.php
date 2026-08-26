@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Employee Document Details')

@section('_head')
@include('hrms.documents.partials.styles')
<style>
    .dm-table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .dm-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dm-header-edit-btn,
    .dm-header-verify-btn {
        border: 0;
        border-radius: 14px;
        padding: 11px 18px;
        font-weight: 800;
        font-size: 13px;
        color: #fff;
        transition: .2s ease;
        white-space: nowrap;
    }

    .dm-header-edit-btn {
        background: linear-gradient(135deg, var(--dm-primary), #7c3aed);
        box-shadow: 0 10px 24px rgba(124, 58, 237, .22);
    }

    .dm-header-verify-btn {
        background: linear-gradient(135deg, #16a34a, #22c55e);
        box-shadow: 0 10px 24px rgba(34, 197, 94, .22);
    }

    .dm-header-edit-btn:hover,
    .dm-header-verify-btn:hover {
        transform: translateY(-1px);
        color: #fff;
    }

    .dm-header-edit-btn.is-active {
        background: linear-gradient(135deg, #ef4444, #f97316);
    }

    .dm-upload-row {
        align-items: end;
    }

    .dm-line-upload {
        height: 46px;
        width: 100%;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 10px;
        padding: 0 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: .2s ease;
    }

    .dm-line-upload:hover {
        border-color: var(--dm-primary);
        box-shadow: 0 8px 18px rgba(124, 58, 237, .10);
    }

    .dm-line-upload-left {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        flex: 1;
    }

    .dm-line-upload-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: var(--dm-soft);
        color: var(--dm-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 12px;
    }

    .dm-line-upload-name {
        font-size: 13px;
        font-weight: 800;
        color: var(--dm-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
    }

    .dm-line-upload-format {
        font-size: 10px;
        font-weight: 800;
        color: var(--dm-muted);
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .dm-file-input-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .dm-reupload-action {
        display: none !important;
    }

    body.dm-edit-mode .dm-normal-action {
        display: none !important;
    }

    body.dm-edit-mode .dm-reupload-action {
        display: inline-flex !important;
    }

    .dm-action-stack {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
</style>
@endsection

@section('_content')
@php
$totalDocs = $documentTypes->count();
$uploadedDocs = 0;
$verifiedDocs = 0;
$pendingDocs = 0;
$rejectedDocs = 0;

foreach($documentTypes as $type){
$doc = $documents->get($type->id);
if($doc){
$uploadedDocs++;
if($doc->verification_status === 'verified') $verifiedDocs++;
elseif($doc->verification_status === 'rejected') $rejectedDocs++;
else $pendingDocs++;
}
}

$missingDocs = max($totalDocs - $uploadedDocs, 0);
$missingTypes = $documentTypes->filter(fn($type) => ! $documents->get($type->id));
@endphp

<div class="dm-page">
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; DOCUMENT MANAGEMENT
            </div>
            <h1>{{ $employee->user->name ?? 'Employee' }} - Documents</h1>
            <p>
                Code: {{ $employee->employee_code ?? '-' }}
                &bull; Experience: {{ ucfirst($employee->experience_type ?? 'Fresher') }}
                @if($employee->user?->email)
                &bull; {{ $employee->user->email }}
                @endif
            </p>
        </div>
        <div class="dm-hero-actions">
            <a href="{{ route('documents.employee.index') }}" class="dm-btn dm-btn-light">
                <i class="fas fa-arrow-left"></i> Back to Directory
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="row dm-metrics-grid">
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-primary">
                <div class="dm-metric-icon dm-icon-primary"><i class="fas fa-folder"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Total Required</div>
                    <div class="dm-metric-value">{{ $totalDocs }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-info">
                <div class="dm-metric-icon dm-icon-info"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Uploaded</div>
                    <div class="dm-metric-value">{{ $uploadedDocs }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-success">
                <div class="dm-metric-icon dm-icon-success"><i class="fas fa-check-circle"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Verified</div>
                    <div class="dm-metric-value">{{ $verifiedDocs }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-warning">
                <div class="dm-metric-icon dm-icon-warning"><i class="fas fa-hourglass-half"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Pending / Missing</div>
                    <div class="dm-metric-value">{{ $missingDocs + $pendingDocs + $rejectedDocs }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="dm-card" id="uploadCard">
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-cloud-upload-alt"></i></div>
                <div>
                    <h5 class="dm-table-title" id="uploadTitle">Upload Document</h5>
                    <p class="dm-table-subtitle" id="uploadSubtitle">Select a missing document type from the list to upload a file on behalf of the employee.</p>
                </div>
            </div>
        </div>

        <div style="padding: 24px;">
            <form action="{{ route('documents.employee.store', $employee->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="documentUploadForm">
                @csrf

                <div class="row dm-upload-row">
                    <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
                        <div class="dm-form-group">
                            <label>Document Type</label>
                            <select name="document_type_id" id="documentTypeSelect" class="form-control" required>
                                <option value="">Select Missing Document</option>

                                @foreach($missingTypes as $type)
                                <option value="{{ $type->id }}" data-mode="new">
                                    {{ $type->name }} {{ $type->is_mandatory ? '(Mandatory)' : '(Optional)' }}
                                </option>
                                @endforeach

                                @foreach($documentTypes as $type)
                                @php $doc = $documents->get($type->id); @endphp
                                @if($doc)
                                <option value="{{ $type->id }}"
                                    data-mode="edit"
                                    data-file="{{ $doc->file_original_name ?? $doc->title ?? 'Current document' }}"
                                    data-expiry="{{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : '' }}"
                                    style="display:none;">
                                    {{ $type->name }} - Re-upload
                                </option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                        <div class="dm-form-group">
                            <label>Upload File</label>
                            <label class="dm-line-upload" style="margin: 0;">
                                <div class="dm-line-upload-left">
                                    <span class="dm-line-upload-icon"><i class="fas fa-paperclip"></i></span>
                                    <span class="dm-line-upload-name selected-file-name" id="selectedFileName">Choose document</span>
                                </div>
                                <span class="dm-line-upload-format">PDF/JPG/PNG</span>
                                <input type="file"
                                    name="file"
                                    id="documentFileInput"
                                    class="dm-file-input-hidden"
                                    required
                                    accept=".pdf,.jpg,.jpeg,.png,.webp">
                            </label>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                        <div class="dm-form-group">
                            <label>Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiryDateInput" class="form-control">
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-2 col-md-6 col-12 mb-3">
                        <button type="submit" class="dm-btn dm-btn-gradient w-100" id="uploadSubmitBtn">
                            <i class="fas fa-cloud-upload-alt"></i> Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="dm-card">
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <h5 class="dm-table-title">Document Checklist Requirements</h5>
                    <p class="dm-table-subtitle">Review, verify, reject, or re-upload individual files uploaded by the employee.</p>
                </div>
            </div>

            <div class="dm-header-actions">
                @if($documents->whereIn('verification_status', ['pending', 'rejected'])->count() > 0)
                <form action="{{ route('documents.hr.verify_employee', $employee->id) }}"
                    method="POST"
                    onsubmit="return confirm('Are you sure you want to verify all uploaded documents?');"
                    style="display: inline-block; margin: 0;">
                    @csrf
                    <button type="submit" class="dm-header-verify-btn">
                        <i class="fas fa-check-double mr-1"></i> Verify All
                    </button>
                </form>
                @endif

                <button type="button" class="dm-header-edit-btn" id="toggleChecklistEdit">
                    <i class="fas fa-pen-to-square mr-1"></i> Edit
                </button>
            </div>
        </div>

        <div class="dm-table-wrap">
            <table class="table dm-table">
                <thead>
                    <tr>
                        <th width="70">S.No</th>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>File Name & Details</th>
                        <th width="320">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documentTypes as $type)
                    @php $doc = $documents->get($type->id); @endphp

                    @if($doc && $doc->file_path)
                    <tr>
                        <td>
                            <span class="d-inline-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; border-radius: 10px; background: var(--dm-soft); color: var(--dm-primary); font-weight: 800; font-size: 12px;">
                                {{ $loop->iteration }}
                            </span>
                        </td>

                        <td>
                            <div style="font-weight: 800; color: var(--dm-text); font-size: 14px;">
                                {{ $type->name }}
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @if($type->is_mandatory)
                                <span class="dm-badge dm-badge-danger"
                                    style="font-size: 9px; padding: 2px 8px;">
                                    Mandatory
                                </span>
                                @else
                                <span class="dm-badge dm-badge-secondary"
                                    style="font-size: 9px; padding: 2px 8px;">
                                    Optional
                                </span>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($doc->verification_status == 'verified')
                            <span class="dm-badge dm-badge-success">
                                <i class="fas fa-check-circle mr-1"></i> Verified
                            </span>
                            @elseif($doc->verification_status == 'rejected')
                            <span class="dm-badge dm-badge-danger">
                                <i class="fas fa-times-circle mr-1"></i> Rejected
                            </span>
                            @else
                            <span class="dm-badge dm-badge-warning">
                                <i class="fas fa-hourglass-half mr-1"></i> Pending
                            </span>
                            @endif
                        </td>

                        <td>
                            <div style="font-weight: 800; color: var(--dm-text); font-size: 13px;">
                                {{ $doc->file_original_name ?? $doc->title ?? 'Document File' }}
                            </div>

                            <div style="font-size: 11px; color: var(--dm-muted); font-weight: 700; margin-top: 3px;">
                                @if($doc->expiry_date)
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Expiry: {{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') }}
                                @else
                                <i class="fas fa-calendar-times mr-1"></i>
                                No expiry date set
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="dm-action-stack">

                                <a href="{{ route('hrms.documents.file', $doc->file_path) }}"
                                    target="_blank"
                                    class="dm-action-btn-pill dm-action-btn-primary dm-normal-action">
                                    <i class="fas fa-eye"></i> View File
                                </a>

                                @if($doc->verification_status != 'verified')
                                <form action="{{ route('documents.employee.verify', $doc->id) }}"
                                    method="POST"
                                    style="display:inline;"
                                    class="dm-normal-action">
                                    @csrf
                                    <button type="submit"
                                        class="dm-action-btn-pill dm-action-btn-success">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                </form>
                                @endif

                                @if($doc->verification_status != 'rejected')
                                <button type="button"
                                    class="dm-action-btn-pill dm-action-btn-danger dm-normal-action js-open-reject-modal"
                                    data-action="{{ route('documents.employee.reject', $doc->id) }}"
                                    data-doc-name="{{ $type->name }}">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                                @endif

                                <button type="button"
                                    class="dm-action-btn-pill dm-action-btn-light dm-reupload-action js-reupload-doc"
                                    data-type-id="{{ $type->id }}"
                                    data-file="{{ $doc->file_original_name ?? $doc->title ?? 'Current document' }}"
                                    data-expiry="{{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : '' }}">

                                    <i class="fas fa-sync-alt text-warning"></i> Re-upload
                                </button>

                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Rejection Reason -->
<div class="modal fade" id="rejectDocModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content" style="border: none; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.18);">
            <form id="rejectDocForm" action="" method="POST" style="width: 100%;">
                @csrf
                <div class="dm-modal-header" style="background: linear-gradient(135deg, #d946ef 0%, #ec4899 50%, #f43f5e 100%); padding: 24px 28px; color: #fff; position: relative;">
                    <h5 class="modal-title" style="color: #fff !important; font-weight: 800; font-size: 18px; display: flex; align-items: center; gap: 8px; margin: 0;">
                        <i class="fas fa-times-circle"></i> Reject Document
                    </h5>
                    <p id="rejectModalSubtitle" style="color: rgba(255,255,255,0.92) !important; margin: 6px 0 0 0; font-size: 13px; font-weight: 500; line-height: 1.45;">
                        Specify the reason for rejecting document so the employee can upload a corrected version.
                    </p>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="position: absolute; top: 20px; right: 24px; opacity: 0.9; text-shadow: none; font-size: 24px; border: none; background: none;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="dm-modal-body" style="padding: 24px 28px;">
                    <div class="form-group mb-0">
                        <label style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; display: block; margin-bottom: 8px;">
                            REASON FOR REJECTION <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" id="rejection_reason_input" class="form-control" required rows="4" style="border-radius: 14px; border: 1.5px solid #e2e8f0; padding: 12px 14px; font-size: 13px; font-weight: 500; outline: none;" placeholder="Explain what is missing or incorrect..."></textarea>
                    </div>
                </div>
                <div class="dm-modal-footer" style="padding: 16px 28px 24px 28px; background: #fff; display: flex; align-items: center; justify-content: flex-end; gap: 10px; border-top: none;">
                    <button type="button" class="btn font-weight-bold" style="height: 40px; border-radius: 50px; padding: 0 22px; font-size: 12px; background: #f1f5f9; color: #475569; border: none;" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="rejectConfirmBtn" class="btn text-white font-weight-bold" style="height: 40px; border-radius: 50px; padding: 0 24px; font-size: 12px; background: linear-gradient(135deg, #f43f5e, #e11d48); border: none; box-shadow: 0 4px 14px rgba(244, 63, 94, 0.35);">
                        <i class="fas fa-check mr-1"></i> Confirm Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('documentFileInput');
    const fileNameBox = document.getElementById('selectedFileName');
    const typeSelect = document.getElementById('documentTypeSelect');
    const expiryInput = document.getElementById('expiryDateInput');
    const uploadTitle = document.getElementById('uploadTitle');
    const uploadSubtitle = document.getElementById('uploadSubtitle');
    const uploadSubmitBtn = document.getElementById('uploadSubmitBtn');
    const uploadForm = document.getElementById('documentUploadForm');
    const toggleEditBtn = document.getElementById('toggleChecklistEdit');

    let autoSubmitAfterPick = false;

    function resetUploadCard() {
        autoSubmitAfterPick = false;
        fileNameBox.textContent = 'Choose document';
        uploadTitle.textContent = 'Upload Document';
        uploadSubtitle.textContent = 'Select a missing document type from the list to upload a file on behalf of the employee.';
        uploadSubmitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload';
        uploadSubmitBtn.style.display = '';

        Array.from(typeSelect.options).forEach(function(option) {
            if (option.dataset.mode === 'edit') option.style.display = 'none';
        });
    }

    fileInput?.addEventListener('change', function() {
        fileNameBox.textContent = this.files && this.files.length ? this.files[0].name : 'Choose document';

        if (autoSubmitAfterPick && this.files && this.files.length) {
            uploadSubmitBtn.disabled = true;
            uploadSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadForm.submit();
        }
    });

    toggleEditBtn?.addEventListener('click', function() {
        const isEditMode = document.body.classList.toggle('dm-edit-mode');
        this.classList.toggle('is-active', isEditMode);
        this.innerHTML = isEditMode ?
            '<i class="fas fa-times mr-1"></i> Cancel Edit' :
            '<i class="fas fa-pen-to-square mr-1"></i> Edit';

        if (!isEditMode) resetUploadCard();
    });

    document.querySelectorAll('.js-reupload-doc').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!document.body.classList.contains('dm-edit-mode')) return;

            const typeId = this.dataset.typeId;
            const fileName = this.dataset.file || 'Current document selected';
            const expiry = this.dataset.expiry || '';

            Array.from(typeSelect.options).forEach(function(option) {
                if (option.dataset.mode === 'edit' && option.value === typeId) {
                    option.style.display = '';
                }
            });

            typeSelect.value = typeId;
            expiryInput.value = expiry;
            fileNameBox.textContent = fileName;
            uploadTitle.textContent = 'Re-upload Document';
            uploadSubtitle.textContent = 'Select a replacement file. File choose karte hi document automatically upload ho jayega.';
            uploadSubmitBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Re-upload';
            uploadSubmitBtn.style.display = 'none';
            autoSubmitAfterPick = true;

            fileInput.value = '';
            fileInput.click();
        });
    });

    document.querySelectorAll('.js-open-reject-modal').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const actionUrl = this.dataset.action;
            const docName = this.dataset.docName || 'Document';

            const form = document.getElementById('rejectDocForm');
            form.action = actionUrl;

            const subtitle = document.getElementById('rejectModalSubtitle');
            subtitle.textContent = `Specify the reason for rejecting "${docName}" so the employee can upload a corrected version.`;

            document.getElementById('rejection_reason_input').value = '';

            $('#rejectDocModal').modal('show');
        });
    });

    document.getElementById('rejectDocForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('rejectConfirmBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Rejecting...';
        }
    });
</script>
@endsection