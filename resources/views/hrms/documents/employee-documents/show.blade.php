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

    .dm-upload-row .select2-container {
        width: 100% !important;
    }

    .dm-upload-row .select2-container--default .select2-selection--single {
        height: 46px !important;
        border-radius: 10px !important;
        border: 1px solid #e5e7eb !important;
        background: #fff !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 14px !important;
        box-shadow: none !important;
        transition: .2s ease !important;
    }

    .dm-upload-row .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px !important;
        padding-left: 0 !important;
        padding-right: 24px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--dm-text) !important;
    }

    .dm-upload-row .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 12px !important;
    }

    .dm-upload-row .select2-container--default.select2-container--open .select2-selection--single,
    .dm-upload-row .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--dm-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
        outline: none !important;
    }

    .dm-upload-row .orbo-date-picker,
    .dm-upload-row .orbo-date-picker-display,
    .dm-upload-row .flatpickr-input,
    .dm-upload-row input[data-date-picker] {
        height: 46px !important;
        border-radius: 10px !important;
        border: 1px solid #e5e7eb !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234B00E8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 14px center !important;
        background-size: 16px 16px !important;
        color: var(--dm-text) !important;
        padding: 0 40px 0 14px !important;
        cursor: pointer !important;
        transition: .2s ease !important;
    }

    .dm-upload-row .orbo-date-picker:focus,
    .dm-upload-row .orbo-date-picker-display:focus,
    .dm-upload-row .flatpickr-input:focus,
    .dm-upload-row input[data-date-picker]:focus {
        border-color: var(--dm-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
        outline: none !important;
    }

    .dm-upload-row #uploadSubmitBtn {
        height: 46px !important;
        border-radius: 10px !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
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

    .dm-card-body {
        padding: 24px;
    }

    /* Full Responsive Design for All Screen Sizes */
    @media (max-width: 991px) {
        .dm-hero {
            padding: 24px 28px !important;
            border-radius: 20px !important;
        }

        .dm-hero h1 {
            font-size: 22px !important;
        }

        .dm-table-header {
            padding: 18px 20px !important;
        }
    }

    @media (max-width: 767px) {
        .dm-hero {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 16px !important;
            padding: 20px !important;
            border-radius: 18px !important;
        }

        .dm-hero h1 {
            font-size: 19px !important;
        }

        .dm-hero p {
            font-size: 12px !important;
        }

        .dm-hero-actions {
            width: 100% !important;
        }

        .dm-hero-actions .dm-btn {
            width: 100% !important;
            justify-content: center !important;
        }

        .dm-metrics-grid {
            margin: 0 -4px !important;
        }

        .dm-metrics-grid [class*="col-"] {
            padding: 0 4px !important;
        }

        .dm-metric-card {
            padding: 12px 12px !important;
            gap: 10px !important;
            border-radius: 14px !important;
        }

        .dm-metric-icon {
            width: 36px !important;
            height: 36px !important;
            font-size: 14px !important;
            border-radius: 10px !important;
        }

        .dm-metric-value {
            font-size: 18px !important;
        }

        .dm-metric-label {
            font-size: 10px !important;
            letter-spacing: 0.02em !important;
        }

        .dm-card-body {
            padding: 16px !important;
        }

        .dm-table-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 12px !important;
            padding: 16px !important;
        }

        .dm-header-actions {
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .dm-header-actions form {
            flex: 1 !important;
        }

        .dm-header-verify-btn,
        .dm-header-edit-btn {
            width: 100% !important;
            justify-content: center !important;
            padding: 9px 14px !important;
            font-size: 12px !important;
            border-radius: 10px !important;
        }

        .dm-table thead th {
            padding: 12px 14px !important;
            font-size: 10px !important;
            white-space: nowrap !important;
        }

        .dm-table tbody td {
            padding: 12px 14px !important;
            white-space: nowrap !important;
        }

        .dm-action-stack {
            flex-wrap: nowrap !important;
        }
    }

    @media (max-width: 575px) {
        .dm-upload-row {
            align-items: stretch !important;
        }
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
        <div class="col-xl-3 col-lg-3 col-md-6 col-6 mb-3">
            <div class="dm-metric-card border-bottom-primary">
                <div class="dm-metric-icon dm-icon-primary"><i class="fas fa-folder"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Total Required</div>
                    <div class="dm-metric-value">{{ $totalDocs }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-6 mb-3">
            <div class="dm-metric-card border-bottom-info">
                <div class="dm-metric-icon dm-icon-info"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Uploaded</div>
                    <div class="dm-metric-value">{{ $uploadedDocs }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-6 mb-3">
            <div class="dm-metric-card border-bottom-success">
                <div class="dm-metric-icon dm-icon-success"><i class="fas fa-check-circle"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Verified</div>
                    <div class="dm-metric-value">{{ $verifiedDocs }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-6 mb-3">
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

        <div class="dm-card-body">
            <form action="{{ route('documents.employee.store', $employee->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="documentUploadForm">
                @csrf

                <div class="row dm-upload-row">
                    <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
                        <div class="dm-form-group">
                            <label>Document Type</label>
                            <select name="document_type_id" id="documentTypeSelect" class="form-control select2-searchable" data-placeholder="Select Missing Document" required style="width: 100%;">
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
                                    style="display:none;"
                                    hidden>
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
                            <x-form.date-picker name="expiry_date" id="expiryDateInput" placeholder="dd-mm-yyyy" />
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

                @if($uploadedDocs > 0)
                <button type="button" class="dm-header-edit-btn" id="toggleChecklistEdit">
                    <i class="fas fa-pen-to-square mr-1"></i> Edit
                </button>
                @endif
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

                    @if($uploadedDocs === 0)
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px;">
                                <div style="width: 72px; height: 72px; border-radius: 20px; background: rgba(75, 0, 232, 0.06); color: var(--dm-primary); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 14px; box-shadow: 0 8px 24px rgba(75, 0, 232, 0.08);">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h5 style="font-weight: 800; font-size: 16px; color: var(--dm-text); margin-bottom: 6px;">No Documents Uploaded Yet</h5>
                                <p style="font-size: 13px; color: var(--dm-muted); margin: 0; max-width: 440px; line-height: 1.5; font-weight: 500;">
                                    This employee has not uploaded any documents yet. You can select a missing document type from the upload form above to upload on their behalf.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endif
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

    function setExpiryDate(dateStr) {
        const input = document.getElementById('expiryDateInput');
        if (!input) return;
        if (input._flatpickr) {
            if (dateStr) {
                input._flatpickr.setDate(dateStr, true);
            } else {
                input._flatpickr.clear();
            }
        } else {
            input.value = dateStr || '';
        }
    }

    function resetUploadCard() {
        autoSubmitAfterPick = false;
        fileNameBox.textContent = 'Choose document';
        uploadTitle.textContent = 'Upload Document';
        uploadSubtitle.textContent = 'Select a missing document type from the list to upload a file on behalf of the employee.';
        uploadSubmitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload';
        uploadSubmitBtn.style.display = '';

        if (typeSelect) {
            Array.from(typeSelect.options).forEach(function(option) {
                if (option.dataset.mode === 'edit') {
                    option.style.display = 'none';
                    option.hidden = true;
                }
            });

            if (window.jQuery && $(typeSelect).data('select2')) {
                $(typeSelect).val('').trigger('change');
            } else {
                typeSelect.value = '';
            }
        }

        setExpiryDate('');
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
                if (option.dataset.mode === 'edit') {
                    if (option.value === typeId) {
                        option.style.display = '';
                        option.hidden = false;
                    } else {
                        option.style.display = 'none';
                        option.hidden = true;
                    }
                }
            });

            if (window.jQuery && $(typeSelect).data('select2')) {
                $(typeSelect).val(typeId).trigger('change');
            } else {
                typeSelect.value = typeId;
            }

            setExpiryDate(expiry);

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

    if (window.jQuery && $.fn.select2) {
        $('#documentTypeSelect').select2({
            placeholder: 'Select Missing Document',
            allowClear: false,
            width: '100%',
            templateResult: function(data) {
                if (!data.id) return data.text;
                const el = data.element;
                if (el && (el.style.display === 'none' || el.hidden)) {
                    return null;
                }
                return data.text;
            }
        });
    }
</script>
@endsection