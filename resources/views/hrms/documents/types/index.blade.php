@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'documents'])

@section('page_title', 'Document Types')

@section('_head')
@include('hrms.documents.partials.styles')
<style>
    /* Status Switch styles */
    .status-switch-wrap {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .status-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
        margin: 0;
    }

    .status-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .status-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #CBD5E1;
        transition: .2s;
        border-radius: 999px;
    }

    .status-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background: white;
        transition: .2s;
        border-radius: 50%;
        box-shadow: 0 3px 8px rgba(15, 23, 42, .2);
    }

    .status-switch input:checked+.status-slider {
        background: linear-gradient(135deg, var(--dm-primary), var(--dm-secondary));
    }

    .status-switch input:checked+.status-slider:before {
        transform: translateX(22px);
    }

    .status-label {
        font-size: 11px;
        font-weight: 800;
    }

    /* Modal layout custom enhancements */
    .doc-modal-section {
        border: 1px solid var(--dm-border);
        background: #FAFBFF;
        border-radius: 18px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .doc-modal-section-title {
        font-size: 13px;
        font-weight: 800;
        color: var(--dm-text);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .doc-modal-section-title i {
        color: var(--dm-primary);
    }

    /* Extension options styles */
    .doc-ext-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
    }

    .doc-ext-card {
        background: #fff;
        border: 1px solid var(--dm-border);
        border-radius: 12px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .doc-ext-card:hover {
        border-color: var(--dm-primary);
        background: var(--dm-soft);
    }

    .doc-ext-card label {
        margin: 0 !important;
        cursor: pointer;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--dm-text) !important;
    }

    .doc-check-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .doc-check-card {
        background: #fff;
        border: 1px solid var(--dm-border);
        border-radius: 14px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .doc-check-card:hover {
        border-color: var(--dm-primary);
        background: var(--dm-soft);
    }

    .doc-check-card label {
        margin: 0 !important;
        cursor: pointer;
        font-size: 12px !important;
        font-weight: 800 !important;
        color: var(--dm-text) !important;
    }

    @media (max-width: 768px) {
        .doc-ext-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .doc-check-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('_content')
@php
$extensionOptions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
@endphp

<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; DOCUMENT MANAGEMENT
            </div>
            <h1>Document Types Configuration</h1>
            <p>Define global document parameters, verification scopes, size upload constraints, and policy compliance workflows.</p>
        </div>
        <div class="dm-hero-actions">
            <button type="button" class="dm-btn dm-btn-primary" data-toggle="modal" data-target="#createDocumentTypeModal">
                <i class="fas fa-plus"></i> Add Document Type
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('status'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
    </div>
    @endif

    <!-- Document Types Card -->
    <div class="dm-card">
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-cog"></i></div>
                <div>
                    <h5 class="dm-table-title">Configured Document Checklist</h5>
                    <p class="dm-table-subtitle">Review file upload rules, mandatory status, expiry date constraints, and scopes.</p>
                </div>
            </div>
        </div>

        <div class="dm-table-wrap">
            <table class="table dm-table">
                <thead>
                    <tr>
                        <th>Name / Code</th>
                        <th>Scope</th>
                        <th>Applies To</th>
                        <th>Mandatory</th>
                        <th>Expiry Tracking</th>
                        <th>Rules & File Details</th>
                        <th>Status</th>
                        <th width="140" class="text-right">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($documentTypes as $type)
                    @php
                    $allowedExtensions = is_array($type->allowed_extensions)
                    ? $type->allowed_extensions
                    : (json_decode($type->allowed_extensions ?? '[]', true) ?: []);
                    @endphp

                    <tr>
                        <td>
                            <div style="font-weight: 800; color: var(--dm-text); font-size: 14px;">{{ $type->name }}</div>
                            <span class="d-inline-flex" style="font-size: 11px; color: var(--dm-muted); background: #F1F5F9; border: 1px solid var(--dm-border); border-radius: 6px; padding: 2px 6px; margin-top: 4px; font-family: monospace;">
                                {{ $type->code }}
                            </span>
                        </td>

                        <td>
                            @if($type->scope === 'policy')
                            <span class="dm-badge dm-badge-success" style="font-size: 9px; padding: 2px 8px;">Policy</span>
                            @else
                            <span class="dm-badge dm-badge-primary" style="font-size: 9px; padding: 2px 8px;">Employee</span>
                            @endif
                        </td>

                        <td>
                            <span class="dm-badge dm-badge-secondary" style="font-size: 9px; padding: 2px 8px;">
                                {{ ucfirst($type->applies_to ?? 'all') }}
                            </span>
                        </td>

                        <td>
                            @if($type->is_mandatory)
                            <span class="dm-badge dm-badge-danger" style="font-size: 9px; padding: 2px 8px;">Mandatory</span>
                            @else
                            <span class="dm-badge dm-badge-secondary" style="font-size: 9px; padding: 2px 8px;">Optional</span>
                            @endif
                        </td>

                        <td>
                            @if($type->has_expiry)
                            <span class="dm-badge dm-badge-warning" style="font-size: 9px; padding: 2px 8px;">Expiry Required</span>
                            @else
                            <span class="dm-badge dm-badge-secondary" style="font-size: 9px; padding: 2px 8px;">No Expiry</span>
                            @endif
                        </td>

                        <td>
                            <div style="min-width: 200px;">
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @forelse($allowedExtensions as $ext)
                                    <span class="d-inline-flex" style="background: var(--dm-soft); color: var(--dm-primary); border-radius: 6px; padding: 2px 6px; font-size: 10px; font-weight: 800; text-transform: uppercase;">
                                        {{ $ext }}
                                    </span>
                                    @empty
                                    <span class="d-inline-flex" style="background: var(--dm-soft); color: var(--dm-primary); border-radius: 6px; padding: 2px 6px; font-size: 10px; font-weight: 800; text-transform: uppercase;">
                                        pdf
                                    </span>
                                    @endforelse
                                </div>

                                <div style="font-size: 12px; color: var(--dm-muted); font-weight: 700;">
                                    Max Size: <strong style="color: var(--dm-text);">{{ $type->max_file_size_mb ?? 5 }} MB</strong> &bull; Multiple: <strong style="color: var(--dm-text);">{{ $type->allow_multiple ? 'Yes' : 'No' }}</strong>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="status-switch-wrap">
                                <label class="status-switch">
                                    <input
                                        type="checkbox"
                                        class="status-toggle"
                                        data-url="{{ route('hrms.documents.types.toggle-status', $type) }}"
                                        {{ $type->is_active ? 'checked' : '' }}>
                                    <span class="status-slider"></span>
                                </label>
                            </div>

                            <small class="status-label d-block mt-1 {{ $type->is_active ? 'text-success font-weight-bold' : 'text-danger font-weight-bold' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </small>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button"
                                    class="dm-action-btn-pill dm-action-btn-light"
                                    data-toggle="modal"
                                    data-target="#editDocumentTypeModal{{ $type->id }}"
                                    title="Edit">
                                    <i class="fas fa-edit text-warning"></i> Edit
                                </button>

                                <form method="POST"
                                    action="{{ route('hrms.documents.types.destroy', $type) }}"
                                    onsubmit="return confirm('Delete this document type?')"
                                    style="display:inline-block;margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="dm-action-btn-pill dm-action-btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            No document types configured yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Edit Modals Outside Table --}}
@foreach($documentTypes as $type)
@php
$selectedExtensions = is_array($type->allowed_extensions)
? $type->allowed_extensions
: (json_decode($type->allowed_extensions ?? '[]', true) ?: ['pdf']);
@endphp

<div class="modal fade" id="editDocumentTypeModal{{ $type->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form method="POST" action="{{ route('hrms.documents.types.update', $type) }}" class="modal-content" style="border: none; border-radius: 24px; overflow: hidden; box-shadow: var(--dm-shadow);">
            @csrf
            @method('PUT')

            <div class="dm-modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Document Type</h5>
                <p>Modify settings for "{{ $type->name }}" ({{ $type->code }})</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>

            <div class="dm-modal-body">
                <div class="doc-modal-section">
                    <div class="doc-modal-section-title">
                        <i class="fas fa-file-alt"></i> Document Details
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $type->name) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Code</label>
                                <input type="text" name="code" class="form-control" value="{{ old('code', $type->code) }}" placeholder="e.g. aadhaar_card">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Scope <span class="text-danger">*</span></label>
                                <select name="scope" class="form-control" required>
                                    <option value="employee" {{ old('scope', $type->scope) === 'employee' ? 'selected' : '' }}>Employee Document</option>
                                    <option value="policy" {{ old('scope', $type->scope) === 'policy' ? 'selected' : '' }}>Company Policy</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Applies To <span class="text-danger">*</span></label>
                                <select name="applies_to" class="form-control" required>
                                    <option value="all" {{ old('applies_to', $type->applies_to ?? 'all') === 'all' ? 'selected' : '' }}>All Employees</option>
                                    <option value="fresher" {{ old('applies_to', $type->applies_to) === 'fresher' ? 'selected' : '' }}>Fresher Only</option>
                                    <option value="experienced" {{ old('applies_to', $type->applies_to) === 'experienced' ? 'selected' : '' }}>Experienced Only</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-modal-section">
                    <div class="doc-modal-section-title">
                        <i class="fas fa-upload"></i> Upload Constraints & Rules
                    </div>

                    <div class="dm-form-group mb-3">
                        <label>Allowed File Formats <span class="text-danger">*</span></label>
                        <div class="doc-ext-grid">
                            @foreach($extensionOptions as $ext)
                            <div class="doc-ext-card">
                                <div class="custom-control custom-checkbox w-100 text-center">
                                    <input type="checkbox"
                                        class="custom-control-input"
                                        id="ext{{ $type->id }}{{ $ext }}"
                                        name="allowed_extensions[]"
                                        value="{{ $ext }}"
                                        {{ in_array($ext, old('allowed_extensions', $selectedExtensions)) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="ext{{ $type->id }}{{ $ext }}">
                                        {{ strtoupper($ext) }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Max File Size (MB) <span class="text-danger">*</span></label>
                                <input type="number"
                                    name="max_file_size_mb"
                                    class="form-control"
                                    min="1"
                                    max="100"
                                    value="{{ old('max_file_size_mb', $type->max_file_size_mb ?? 5) }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="doc-check-card" style="height: 42px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                        class="custom-control-input"
                                        id="multiple{{ $type->id }}"
                                        name="allow_multiple"
                                        value="1"
                                        {{ old('allow_multiple', $type->allow_multiple) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="multiple{{ $type->id }}">
                                        Allow Multiple File Uploads
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-modal-section mb-0">
                    <div class="doc-modal-section-title">
                        <i class="fas fa-toggle-on"></i> Document Properties
                    </div>

                    <div class="doc-check-row">
                        <div class="doc-check-card">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="mandatory{{ $type->id }}" name="is_mandatory" value="1" {{ $type->is_mandatory ? 'checked' : '' }}>
                                <label class="custom-control-label" for="mandatory{{ $type->id }}">Is Mandatory</label>
                            </div>
                        </div>

                        <div class="doc-check-card">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="expiry{{ $type->id }}" name="has_expiry" value="1" {{ $type->has_expiry ? 'checked' : '' }}>
                                <label class="custom-control-label" for="expiry{{ $type->id }}">Requires Expiry Date</label>
                            </div>
                        </div>

                        <div class="doc-check-card">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="active{{ $type->id }}" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label" for="active{{ $type->id }}">Is Active & Visible</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dm-modal-footer">
                <button type="button" class="dm-btn dm-btn-dark-light" style="height: 38px;" data-dismiss="modal">Cancel</button>
                <button type="submit" class="dm-btn dm-btn-gradient" style="height: 38px;">
                    <i class="fas fa-save mr-1"></i> Save Document Type
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Create Modal Outside Table --}}
<div class="modal fade" id="createDocumentTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form method="POST" action="{{ route('hrms.documents.types.store') }}" class="modal-content" style="border: none; border-radius: 24px; overflow: hidden; box-shadow: var(--dm-shadow);">
            @csrf

            <div class="dm-modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Add Document Type</h5>
                <p>Configure a new mandatory compliance credential, optional document, or company policy category.</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>

            <div class="dm-modal-body">
                <div class="doc-modal-section">
                    <div class="doc-modal-section-title">
                        <i class="fas fa-plus-circle"></i> Document Details
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Aadhaar Card" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Code</label>
                                <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="Auto generated if empty">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Scope <span class="text-danger">*</span></label>
                                <select name="scope" class="form-control" required>
                                    <option value="employee" {{ old('scope') === 'employee' ? 'selected' : '' }}>Employee Document</option>
                                    <option value="policy" {{ old('scope') === 'policy' ? 'selected' : '' }}>Company Policy</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Applies To <span class="text-danger">*</span></label>
                                <select name="applies_to" class="form-control" required>
                                    <option value="all" {{ old('applies_to', 'all') === 'all' ? 'selected' : '' }}>All Employees</option>
                                    <option value="fresher" {{ old('applies_to') === 'fresher' ? 'selected' : '' }}>Fresher Only</option>
                                    <option value="experienced" {{ old('applies_to') === 'experienced' ? 'selected' : '' }}>Experienced Only</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-modal-section">
                    <div class="doc-modal-section-title">
                        <i class="fas fa-upload"></i> Upload Constraints & Rules
                    </div>

                    <div class="dm-form-group mb-3">
                        <label>Allowed File Formats <span class="text-danger">*</span></label>
                        <div class="doc-ext-grid">
                            @foreach($extensionOptions as $ext)
                            <div class="doc-ext-card">
                                <div class="custom-control custom-checkbox w-100 text-center">
                                    <input type="checkbox"
                                        class="custom-control-input"
                                        id="newExt{{ $ext }}"
                                        name="allowed_extensions[]"
                                        value="{{ $ext }}"
                                        {{ in_array($ext, old('allowed_extensions', ['pdf'])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="newExt{{ $ext }}">
                                        {{ strtoupper($ext) }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3">
                            <div class="dm-form-group">
                                <label>Max File Size (MB) <span class="text-danger">*</span></label>
                                <input type="number"
                                    name="max_file_size_mb"
                                    class="form-control"
                                    min="1"
                                    max="100"
                                    value="{{ old('max_file_size_mb', 5) }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="doc-check-card" style="height: 42px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                        class="custom-control-input"
                                        id="newMultiple"
                                        name="allow_multiple"
                                        value="1"
                                        {{ old('allow_multiple') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="newMultiple">
                                        Allow Multiple File Uploads
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doc-modal-section mb-0">
                    <div class="doc-modal-section-title">
                        <i class="fas fa-toggle-on"></i> Document Properties
                    </div>

                    <div class="doc-check-row">
                        <div class="doc-check-card">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="newMandatory" name="is_mandatory" value="1">
                                <label class="custom-control-label" for="newMandatory">Is Mandatory</label>
                            </div>
                        </div>

                        <div class="doc-check-card">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="newExpiry" name="has_expiry" value="1">
                                <label class="custom-control-label" for="newExpiry">Requires Expiry Date</label>
                            </div>
                        </div>

                        <div class="doc-check-card">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="newActive" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="newActive">Is Active & Visible</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dm-modal-footer">
                <button type="button" class="dm-btn dm-btn-dark-light" style="height: 38px;" data-dismiss="modal">Cancel</button>
                <button type="submit" class="dm-btn dm-btn-gradient" style="height: 38px;">
                    <i class="fas fa-save mr-1"></i> Create Document Type
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.status-toggle').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                let checkbox = this;
                let label = checkbox.closest('td').querySelector('.status-label');
                let oldChecked = !checkbox.checked;

                fetch(checkbox.dataset.url, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            checkbox.checked = data.is_active;
                            label.innerText = data.is_active ? 'Active' : 'Inactive';

                            label.classList.remove('text-success', 'text-danger');
                            label.classList.add(data.is_active ? 'text-success' : 'text-danger');
                        } else {
                            checkbox.checked = oldChecked;
                            label.innerText = oldChecked ? 'Active' : 'Inactive';
                            alert('Status update failed.');
                        }
                    })
                    .catch(function() {
                        checkbox.checked = oldChecked;
                        label.innerText = oldChecked ? 'Active' : 'Inactive';

                        label.classList.remove('text-success', 'text-danger');
                        label.classList.add(oldChecked ? 'text-success' : 'text-danger');

                        alert('Status update failed.');
                    });
            });
        });
    });
</script>
@endsection