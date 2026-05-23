@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Employee Document Details')

@section('_head')
@include('hrms.documents.partials.styles')
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
    <!-- Premium Purple Gradient Hero -->
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
            <a href="{{ route('hrms.documents.employee.index') }}" class="dm-btn dm-btn-light">
                <i class="fas fa-arrow-left"></i> Back to Directory
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <!-- Metrics Cards Grid -->
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

    <!-- Upload/Re-upload Card -->
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
            <form action="{{ route('hrms.documents.employee.store', $employee->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="documentUploadForm">
                @csrf

                <div class="row align-items-end">
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
                            <label class="eo-file-upload" style="margin: 0;">
                                <div class="eo-file-left">
                                    <div class="eo-file-icon"><i class="fas fa-paperclip"></i></div>
                                    <div class="eo-file-title selected-file-name" id="selectedFileName" style="font-weight: 700;">Choose file...</div>
                                </div>

                                <span class="eo-file-browse">
                                    <i class="fas fa-folder-open"></i> Browse
                                </span>

                                <input type="file"
                                    name="file"
                                    id="documentFileInput"
                                    class="eo-file-input"
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

    <!-- Document Checklist Table -->
    <div class="dm-card">
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <h5 class="dm-table-title">Document Checklist Requirements</h5>
                    <p class="dm-table-subtitle">Review, verify, reject, or edit individual files uploaded by the employee.</p>
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
                        <th>File Name & Details</th>
                        <th width="320">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documentTypes as $type)
                    @php $doc = $documents->get($type->id); @endphp
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

                                @if($type->has_expiry_date ?? false)
                                <span class="dm-badge dm-badge-warning" style="font-size: 9px; padding: 2px 8px;">Expiry Required</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($doc)
                                @if($doc->verification_status == 'verified')
                                <span class="dm-badge dm-badge-success"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                @elseif($doc->verification_status == 'rejected')
                                <span class="dm-badge dm-badge-danger"><i class="fas fa-times-circle mr-1"></i> Rejected</span>
                                @else
                                <span class="dm-badge dm-badge-warning"><i class="fas fa-hourglass-half mr-1"></i> Pending</span>
                                @endif
                            @else
                            <span class="dm-badge dm-badge-secondary"><i class="fas fa-minus-circle mr-1"></i> Missing</span>
                            @endif
                        </td>

                        <td>
                            @if($doc && $doc->file_path)
                            <div style="font-weight: 800; color: var(--dm-text); font-size: 13px;">{{ $doc->file_original_name ?? $doc->title ?? 'Document File' }}</div>
                            <div style="font-size: 11px; color: var(--dm-muted); font-weight: 700; margin-top: 3px;">
                                @if($doc->expiry_date)
                                <i class="fas fa-calendar-alt mr-1"></i> Expiry: {{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') }}
                                @else
                                <i class="fas fa-calendar-times mr-1"></i> No expiry date set
                                @endif
                            </div>
                            @else
                            <span class="text-muted" style="font-size: 12px;">No file uploaded</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($doc && $doc->file_path)
                                <a href="{{ route('hrms.documents.file', $doc->file_path) }}"
                                    target="_blank"
                                    class="dm-action-btn-pill dm-action-btn-primary">
                                    <i class="fas fa-eye"></i> View File
                                </a>

                                <button type="button"
                                    class="dm-action-btn-pill dm-action-btn-light js-edit-doc"
                                    data-type-id="{{ $type->id }}"
                                    data-file="{{ $doc->file_original_name ?? $doc->title ?? 'Current document' }}"
                                    data-expiry="{{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : '' }}">
                                    <i class="fas fa-edit text-warning"></i> Edit
                                </button>

                                @if($doc->verification_status != 'verified')
                                <form action="{{ route('hrms.documents.employee.verify', $doc->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="dm-action-btn-pill dm-action-btn-success">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                </form>
                                @endif

                                @if($doc->verification_status != 'rejected')
                                <form action="{{ route('hrms.documents.employee.reject', $doc->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="rejection_reason" value="Rejected by admin.">
                                    <button type="submit" class="dm-action-btn-pill dm-action-btn-danger">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                                @endif
                                @else
                                <span class="text-muted" style="font-size: 12px;">Upload from form above</span>
                                @endif
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
    const fileInput = document.getElementById('documentFileInput');
    const fileNameBox = document.getElementById('selectedFileName');
    const typeSelect = document.getElementById('documentTypeSelect');
    const expiryInput = document.getElementById('expiryDateInput');
    const uploadTitle = document.getElementById('uploadTitle');
    const uploadSubtitle = document.getElementById('uploadSubtitle');
    const uploadSubmitBtn = document.getElementById('uploadSubmitBtn');

    fileInput?.addEventListener('change', function() {
        fileNameBox.textContent = this.files && this.files.length ? this.files[0].name : 'Choose file...';
    });

    document.querySelectorAll('.js-edit-doc').forEach(function(btn) {
        btn.addEventListener('click', function() {
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
            uploadSubtitle.textContent = 'You are replacing the selected document. Please select a new file and upload.';
            uploadSubmitBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Re-upload';

            document.getElementById('uploadCard').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            setTimeout(() => fileInput.click(), 350);
        });
    });
</script>
@endsection