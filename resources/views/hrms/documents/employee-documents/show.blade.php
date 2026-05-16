@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Employee Document Details')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #fff;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-success: #12B76A;
        --orb-warning: #F79009;
        --orb-danger: #EC4E74;
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .eo-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .eo-container {
        max-width: 1320px;
        margin: 0 auto;
    }

    .eo-header {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .eo-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 24px;
        font-weight: 900;
    }

    .eo-subtitle {
        margin: 4px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .eo-btn {
        min-height: 46px;
        border-radius: 12px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid transparent;
        text-decoration: none !important;
        cursor: pointer;
        white-space: nowrap;
    }

    .eo-btn-primary {
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        box-shadow: 0 10px 22px rgba(75, 0, 232, .16);
    }

    .eo-btn-light {
        background: #fff;
        color: var(--orb-text);
        border-color: var(--orb-border);
    }

    .eo-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 16px;
    }

    .eo-card-head {
        padding: 16px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #FCFCFD;
    }

    .eo-card-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 16px;
        font-weight: 900;
    }

    .eo-card-subtitle {
        margin: 3px 0 0;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .eo-card-body {
        padding: 16px;
    }

    .eo-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }

    .eo-summary {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 14px;
        box-shadow: 0 6px 16px rgba(16, 24, 40, .035);
    }

    .eo-summary-label {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .eo-summary-value {
        color: var(--orb-text);
        font-size: 22px;
        font-weight: 950;
    }

    .eo-upload-grid {
        display: grid;
        grid-template-columns: 1.15fr 1.45fr .8fr auto;
        gap: 12px;
        align-items: end;
    }

    .eo-field label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .eo-control {
        width: 100%;
        height: 46px;
        border-radius: 13px !important;
        border: 1px solid var(--orb-border) !important;
        background: #F9FAFB !important;
        color: var(--orb-text) !important;
        font-size: 13px;
        font-weight: 800;
        padding: 8px 12px;
        outline: none;
    }

    .eo-control:focus {
        border-color: rgba(75, 0, 232, .45) !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    .eo-file-input {
        display: none;
    }

    .eo-file-upload {
        height: 46px;
        border: 1px solid var(--orb-border);
        border-radius: 13px;
        background: #F9FAFB;
        padding: 0 8px 0 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        cursor: pointer;
        transition: .18s ease;
        overflow: hidden;
    }

    .eo-file-upload:hover {
        background: #fff;
        border-color: rgba(75, 0, 232, .45);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
    }

    .eo-file-left {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        flex: 1;
    }

    .eo-file-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex: 0 0 auto;
    }

    .eo-file-title {
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 850;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .eo-file-browse {
        height: 30px;
        padding: 0 11px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 900;
        flex: 0 0 auto;
    }

    .eo-table {
        width: 100%;
        margin: 0 !important;
    }

    .eo-table thead th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .45px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--orb-border);
        white-space: nowrap;
    }

    .eo-table tbody td {
        padding: 13px 14px;
        border-bottom: 1px solid #F1F3F8;
        vertical-align: middle;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 700;
    }

    .eo-table tbody tr:hover {
        background: #FCFAFF;
    }

    .eo-sno {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: #F4F2FF;
        color: var(--orb-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 950;
    }

    .eo-doc-name {
        font-weight: 900;
        color: var(--orb-text);
    }

    .eo-doc-meta {
        font-size: 11px;
        color: var(--orb-muted);
        font-weight: 750;
        margin-top: 5px;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .eo-pill,
    .eo-mini-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .eo-pill {
        padding: 7px 10px;
        font-size: 11px;
    }

    .eo-mini-pill {
        padding: 5px 8px;
        font-size: 10px;
    }

    .eo-dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
    }

    .eo-pill-active {
        color: #12B76A;
        background: rgba(18, 183, 106, .10);
    }

    .eo-pill-warning {
        color: #B54708;
        background: #FFF7E8;
    }

    .eo-pill-danger {
        color: #EC4E74;
        background: rgba(236, 78, 116, .10);
    }

    .eo-pill-muted {
        color: #667085;
        background: #F2F4F7;
    }

    .eo-pill-required {
        color: #C01048;
        background: rgba(236, 78, 116, .10);
    }

    .eo-pill-optional {
        color: #667085;
        background: #F2F4F7;
    }

    .eo-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .eo-action-btn {
        min-height: 34px;
        border: 0;
        border-radius: 11px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none !important;
        cursor: pointer;
    }

    .eo-action-view {
        background: #EEF4FF;
        color: #3538CD;
    }

    .eo-action-view:hover {
        background: #3538CD;
        color: #fff;
    }

    .eo-action-edit {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .eo-action-edit:hover {
        background: var(--orb-primary);
        color: #fff;
    }

    .eo-action-verify {
        background: rgba(18, 183, 106, .10);
        color: #067647;
    }

    .eo-action-verify:hover {
        background: #12B76A;
        color: #fff;
    }

    .eo-action-reject {
        background: rgba(236, 78, 116, .10);
        color: #C01048;
    }

    .eo-empty-action {
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .alert-success {
        border: 0;
        border-radius: 14px;
        background: rgba(18, 183, 106, .12);
        color: #067647;
        font-weight: 800;
    }

    @media(max-width:1100px) {
        .eo-summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .eo-upload-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media(max-width:768px) {

        .eo-header,
        .eo-card-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .eo-summary-grid,
        .eo-upload-grid {
            grid-template-columns: 1fr;
        }

        .eo-btn {
            width: 100%;
        }
    }
</style>

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

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header">
            <div>
                <h1 class="eo-title">{{ $employee->user->name ?? 'Employee' }} - Documents</h1>
                <p class="eo-subtitle">
                    Code: {{ $employee->employee_code ?? '-' }}
                    • Experience: {{ ucfirst($employee->experience_type ?? 'Fresher') }}
                    @if($employee->user?->email)
                    • {{ $employee->user->email }}
                    @endif
                </p>
            </div>

            <a href="{{ route('hrms.documents.employee.index') }}" class="eo-btn eo-btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @if(session('success'))
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        <div class="eo-summary-grid">
            <div class="eo-summary">
                <div class="eo-summary-label">Total Types</div>
                <div class="eo-summary-value">{{ $totalDocs }}</div>
            </div>
            <div class="eo-summary">
                <div class="eo-summary-label">Uploaded</div>
                <div class="eo-summary-value">{{ $uploadedDocs }}</div>
            </div>
            <div class="eo-summary">
                <div class="eo-summary-label">Verified</div>
                <div class="eo-summary-value">{{ $verifiedDocs }}</div>
            </div>
            <div class="eo-summary">
                <div class="eo-summary-label">Pending / Missing</div>
                <div class="eo-summary-value">{{ $missingDocs + $pendingDocs + $rejectedDocs }}</div>
            </div>
        </div>

        <div class="eo-card" id="uploadCard">
            <div class="eo-card-head">
                <div>
                    <h4 class="eo-card-title" id="uploadTitle">Upload Document</h4>
                    <!-- <p class="eo-card-subtitle" id="uploadSubtitle">Dropdown me sirf missing documents aayenge. Existing document ke liye Edit/Re-upload button use karein.</p> -->
                </div>
            </div>

            <div class="eo-card-body">
                <form action="{{ route('hrms.documents.employee.store', $employee->id) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="eo-upload-grid"
                    id="documentUploadForm">
                    @csrf

                    <div class="eo-field">
                        <label>Document Type</label>
                        <select name="document_type_id" id="documentTypeSelect" class="eo-control" required>
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

                    <div class="eo-field">
                        <label>Upload File</label>
                        <label class="eo-file-upload">
                            <div class="eo-file-left">
                                <div class="eo-file-icon"><i class="fas fa-paperclip"></i></div>
                                <div class="eo-file-title" id="selectedFileName">Choose file Browse</div>
                            </div>

                            <!-- <span class="eo-file-browse">
                                <i class="fas fa-folder-open"></i> Browse
                            </span> -->

                            <input type="file"
                                name="file"
                                id="documentFileInput"
                                class="eo-file-input"
                                required
                                accept=".pdf,.jpg,.jpeg,.png,.webp">
                        </label>
                    </div>

                    <div class="eo-field">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiryDateInput" class="eo-control">
                    </div>

                    <div class="eo-field">
                        <label>&nbsp;</label>
                        <button type="submit" class="eo-btn eo-btn-primary" style="width:100%;" id="uploadSubmitBtn">
                            <i class="fas fa-cloud-upload-alt"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="eo-card">
            <div class="eo-card-head">
                <div>
                    <h4 class="eo-card-title">Document Checklist</h4>
                    <p class="eo-card-subtitle">Requirement document type ke niche show hoga.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table eo-table">
                    <thead>
                        <tr>
                            <th width="70">S.No</th>
                            <th>Document Type</th>
                            <th>Status</th>
                            <th>File</th>
                            <th width="320">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentTypes as $type)
                        @php $doc = $documents->get($type->id); @endphp
                        <tr>
                            <td><span class="eo-sno">{{ $loop->iteration }}</span></td>

                            <td>
                                <div class="eo-doc-name">{{ $type->name }}</div>
                                <div class="eo-doc-meta">
                                    @if($type->is_mandatory)
                                    <span class="eo-mini-pill eo-pill-required"><span class="eo-dot"></span> Mandatory</span>
                                    @else
                                    <span class="eo-mini-pill eo-pill-optional"><span class="eo-dot"></span> Optional</span>
                                    @endif

                                    <!-- <span class="eo-mini-pill eo-pill-muted">{{ ucfirst($type->applies_to ?? 'all') }}</span> -->

                                    @if($type->has_expiry_date ?? false)
                                    <span class="eo-mini-pill eo-pill-warning">Expiry Required</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if($doc)
                                @if($doc->verification_status == 'verified')
                                <span class="eo-pill eo-pill-active"><span class="eo-dot"></span> Verified</span>
                                @elseif($doc->verification_status == 'rejected')
                                <span class="eo-pill eo-pill-danger"><span class="eo-dot"></span> Rejected</span>
                                @else
                                <span class="eo-pill eo-pill-warning"><span class="eo-dot"></span> Pending</span>
                                @endif
                                @else
                                <span class="eo-pill eo-pill-muted"><span class="eo-dot"></span> Missing</span>
                                @endif
                            </td>

                            <td>
                                @if($doc && $doc->file_path)
                                <div class="eo-doc-name">{{ $doc->file_original_name ?? $doc->title ?? 'Document' }}</div>
                                <div class="eo-doc-meta">
                                    @if($doc->expiry_date)
                                    Expiry: {{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') }}
                                    @else
                                    No expiry
                                    @endif
                                </div>
                                @else
                                <span class="eo-empty-action">No file uploaded</span>
                                @endif
                            </td>

                            <td>
                                <div class="eo-actions">
                                    @if($doc && $doc->file_path)
                                    <a href="{{ route('hrms.documents.file', $doc->file_path) }}"
                                        target="_blank"
                                        class="eo-action-btn eo-action-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    <button type="button"
                                        class="eo-action-btn eo-action-edit js-edit-doc"
                                        data-type-id="{{ $type->id }}"
                                        data-file="{{ $doc->file_original_name ?? $doc->title ?? 'Current document' }}"
                                        data-expiry="{{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : '' }}">
                                        <i class="fas fa-edit"></i> Edit / Re-upload
                                    </button>

                                    @if($doc->verification_status != 'verified')
                                    <form action="{{ route('hrms.documents.employee.verify', $doc->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="eo-action-btn eo-action-verify">
                                            <i class="fas fa-check"></i> Verify
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Future use ke liye reject code comment rakha hai
                                            @if($doc->verification_status != 'rejected')
                                                <form action="{{ route('hrms.documents.employee.reject', $doc->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="rejection_reason" value="Rejected by admin.">
                                    <button type="submit" class="eo-action-btn eo-action-reject">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    </form>
                                    @endif
                                    --}}
                                    @else
                                    <span class="eo-empty-action">Upload from form above</span>
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
        fileNameBox.textContent = this.files && this.files.length ? this.files[0].name : 'Choose file';
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
            uploadSubtitle.textContent = 'Aap selected document ko replace kar rahe hain. Nayi file select karke Upload karein.';
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