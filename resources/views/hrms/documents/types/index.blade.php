@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'documents'])

@section('page_title', 'Document Types')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .doc-page {
        min-height: calc(100vh - 90px);
        padding: 18px 12px 35px;
        background: var(--orb-bg);
    }

    .doc-container {
        max-width: 1280px;
        margin: 0 auto;
    }

    .doc-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .doc-header {
        padding: 22px;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #fff, #f8f5ff);
        border: 1px solid var(--orb-border);
        border-radius: 26px;
        box-shadow: var(--orb-shadow);
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
    }

    .doc-title {
        font-size: 26px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
    }

    .doc-subtitle {
        font-size: 13px;
        color: var(--orb-muted);
        margin: 5px 0 0;
    }

    .doc-btn {
        border: 0;
        border-radius: 14px;
        padding: 10px 16px;
        font-weight: 900;
        display: inline-flex;
        gap: 8px;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
    }

    .doc-btn-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff !important;
    }

    .doc-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    .doc-btn-danger {
        background: linear-gradient(135deg, #ec4e74, #ff7675);
        color: #fff !important;
    }

    .doc-table-wrap {
        padding: 16px;
    }

    .doc-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .doc-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse !important;
    }

    .doc-table th {
        background: #F8FAFC;
        color: #475467;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 13px 14px;
        border-top: 1px solid #EAECF0;
        border-bottom: 1px solid #EAECF0;
        white-space: nowrap;
    }

    .doc-table td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6;
        padding: 14px;
        vertical-align: middle;
    }

    .doc-table tbody tr:hover td {
        background: #FAF8FF;
    }

    .doc-name {
        font-size: 14px;
        font-weight: 950;
        color: var(--orb-text);
    }

    .doc-code {
        display: inline-block;
        margin-top: 4px;
        font-size: 12px;
        color: var(--orb-muted);
        background: #F8FAFC;
        border: 1px solid #EEF2F6;
        border-radius: 8px;
        padding: 3px 7px;
    }

    .doc-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 11px;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge-active {
        background: #dcfce7;
        color: #166534
    }

    .badge-inactive {
        background: #fee2e2;
        color: #991b1b
    }

    .badge-muted {
        background: #f1f5f9;
        color: #475569
    }

    .badge-required {
        background: #dbeafe;
        color: #1e40af
    }

    .badge-optional {
        background: #f1f5f9;
        color: #475569
    }

    .badge-expiry {
        background: #fff7ed;
        color: #9a3412
    }

    .badge-no-expiry {
        background: #f8fafc;
        color: #64748b
    }

    .badge-employee {
        background: #f4f2ff;
        color: #4B00E8
    }

    .badge-policy {
        background: #ecfeff;
        color: #155e75
    }

    .doc-ext-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 8px;
    }

    .doc-ext-chip {
        background: #f4f2ff;
        color: #4B00E8;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
    }

    .doc-upload-info {
        min-width: 220px;
    }

    .doc-upload-row {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 800;
        margin-top: 5px;
    }

    .doc-upload-row strong {
        color: var(--orb-text);
    }

    .doc-actions {
        display: flex;
        gap: 7px;
        justify-content: flex-end;
    }

    .icon-btn {
        width: 37px;
        height: 37px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

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
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    }

    .status-switch input:checked+.status-slider:before {
        transform: translateX(22px);
    }

    .status-label {
        font-size: 12px;
        font-weight: 950;
        color: var(--orb-text);
    }

    /* Premium Modal */
    .modal-backdrop {
        z-index: 1240 !important;
        background: #0F172A !important;
    }

    .modal-backdrop.show {
        opacity: .58 !important;
    }

    .modal {
        z-index: 1250 !important;
    }

    .orb-doc-modal .modal-dialog {
        max-width: 820px;
    }

    .doc-modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #fff !important;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    .doc-modal-header {
        padding: 18px 22px;
        background: linear-gradient(135deg, #4B00E8, #8600EE);
        color: #fff;
        border-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .doc-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
    }

    .doc-modal-subtitle {
        margin-top: 3px;
        font-size: 12px;
        color: rgba(255, 255, 255, .78);
    }

    .doc-modal-header .close {
        color: #fff;
        opacity: 1;
        text-shadow: none;
        outline: none;
    }

    .doc-modal-body {
        padding: 22px;
        background: #fff !important;
    }

    .doc-modal-body label {
        font-size: 11px;
        font-weight: 900;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .doc-modal-body .form-control {
        height: 43px;
        border-radius: 13px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        background: #fff;
    }

    .doc-modal-body select.form-control {
        padding-top: 8px;
    }

    .doc-modal-body .form-control:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .12);
    }

    .doc-modal-section {
        border: 1px solid #EEF2F6;
        background: #FAFBFF;
        border-radius: 18px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .doc-modal-section-title {
        font-size: 13px;
        font-weight: 950;
        color: var(--orb-text);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .doc-modal-section-title i {
        color: var(--orb-primary);
    }

    .doc-check-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .doc-check-card {
        background: #fff;
        border: 1px solid #E4E7EC;
        border-radius: 15px;
        padding: 13px;
        min-height: 52px;
        display: flex;
        align-items: center;
    }

    .doc-check-card .custom-control-label {
        font-size: 13px !important;
        color: var(--orb-text) !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        font-weight: 900 !important;
    }

    .doc-ext-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .doc-ext-card {
        background: #fff;
        border: 1px solid #E4E7EC;
        border-radius: 13px;
        padding: 10px;
    }

    .doc-ext-card .custom-control-label {
        font-size: 12px !important;
        color: var(--orb-text) !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        font-weight: 900 !important;
    }

    .doc-modal-footer {
        padding: 16px 22px;
        background: #F8FAFC;
        border-top: 1px solid #EEF2F6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    @media(max-width:768px) {
        .doc-header {
            flex-direction: column;
            align-items: flex-start
        }

        .doc-page {
            padding: 12px 8px 25px;
        }

        .doc-title {
            font-size: 22px;
        }

        .orb-doc-modal .modal-dialog {
            margin: 12px;
        }

        .doc-check-row {
            grid-template-columns: 1fr;
        }

        .doc-ext-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

@php
$extensionOptions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
@endphp

<div class="doc-page">
    <div class="doc-container">

        <div class="doc-header">
            <div>
                <h3 class="doc-title">Document Types</h3>
                <p class="doc-subtitle">Manage employee required documents, optional documents, company policy types, and upload rules.</p>
            </div>

            <button type="button" class="doc-btn doc-btn-primary" data-toggle="modal" data-target="#createDocumentTypeModal">
                <i class="fas fa-plus"></i> Add Document Type
            </button>
        </div>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="doc-card">
            <div class="doc-table-wrap">
                <div class="doc-table-responsive">
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>Name / Code</th>
                                <th>Scope</th>
                                <th>Applies To</th>
                                <th>Mandatory</th>
                                <th>Expiry</th>
                                <th>Extension / Max Size / Multiple</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
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
                                    <div class="doc-name">{{ $type->name }}</div>
                                    <span class="doc-code">{{ $type->code }}</span>
                                </td>

                                <td>
                                    <span class="doc-badge {{ $type->scope === 'policy' ? 'badge-policy' : 'badge-employee' }}">
                                        {{ ucfirst($type->scope) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="doc-badge badge-muted">
                                        {{ ucfirst($type->applies_to ?? 'all') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="doc-badge {{ $type->is_mandatory ? 'badge-required' : 'badge-optional' }}">
                                        {{ $type->is_mandatory ? 'Mandatory' : 'Optional' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="doc-badge {{ $type->has_expiry ? 'badge-expiry' : 'badge-no-expiry' }}">
                                        {{ $type->has_expiry ? 'Has Expiry' : 'No Expiry' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="doc-upload-info">
                                        <div class="doc-ext-wrap">
                                            @forelse($allowedExtensions as $ext)
                                            <span class="doc-ext-chip">{{ $ext }}</span>
                                            @empty
                                            <span class="doc-ext-chip">pdf</span>
                                            @endforelse
                                        </div>

                                        <div class="doc-upload-row">
                                            <strong>Max Size:</strong> {{ $type->max_file_size_mb ?? 5 }} MB
                                        </div>

                                        <div class="doc-upload-row">
                                            <strong>Multiple:</strong> {{ $type->allow_multiple ? 'Yes' : 'No' }}
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

                                    <small class="status-label d-block mt-1 {{ $type->is_active ? 'text-success' : 'text-danger' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </small>
                                </td>

                                <td>
                                    <div class="doc-actions">
                                        <button type="button"
                                            class="icon-btn text-primary"
                                            data-toggle="modal"
                                            data-target="#editDocumentTypeModal{{ $type->id }}"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form method="POST"
                                            action="{{ route('hrms.documents.types.destroy', $type) }}"
                                            onsubmit="return confirm('Delete this document type?')"
                                            style="display:inline-block;margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-btn text-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    No document types found.
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

        <div class="modal fade orb-doc-modal" id="editDocumentTypeModal{{ $type->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('hrms.documents.types.update', $type) }}" class="modal-content doc-modal-content">
                    @csrf
                    @method('PUT')

                    <div class="modal-header doc-modal-header">
                        <div>
                            <h5 class="doc-modal-title">Edit Document Type</h5>
                            <div class="doc-modal-subtitle">{{ $type->name }} · {{ $type->code }}</div>
                        </div>

                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body doc-modal-body">
                        <div class="doc-modal-section">
                            <div class="doc-modal-section-title">
                                <i class="fas fa-file-alt"></i> Document Type Details
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $type->name) }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code', $type->code) }}" placeholder="aadhaar_card">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Scope</label>
                                    <select name="scope" class="form-control" required>
                                        <option value="employee" {{ old('scope', $type->scope) === 'employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="policy" {{ old('scope', $type->scope) === 'policy' ? 'selected' : '' }}>Policy</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Applies To</label>
                                    <select name="applies_to" class="form-control" required>
                                        <option value="all" {{ old('applies_to', $type->applies_to ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                                        <option value="fresher" {{ old('applies_to', $type->applies_to) === 'fresher' ? 'selected' : '' }}>Fresher</option>
                                        <option value="experienced" {{ old('applies_to', $type->applies_to) === 'experienced' ? 'selected' : '' }}>Experienced</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="doc-modal-section">
                            <div class="doc-modal-section-title">
                                <i class="fas fa-upload"></i> Upload Rules
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label>Allowed Extensions</label>
                                    <div class="doc-ext-grid">
                                        @foreach($extensionOptions as $ext)
                                        <div class="doc-ext-card">
                                            <div class="custom-control custom-checkbox">
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

                                <div class="col-md-6 mb-3">
                                    <label>Max File Size MB</label>
                                    <input type="number"
                                        name="max_file_size_mb"
                                        class="form-control"
                                        min="1"
                                        max="100"
                                        value="{{ old('max_file_size_mb', $type->max_file_size_mb ?? 5) }}">
                                </div>

                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <div class="doc-check-card w-100">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                class="custom-control-input"
                                                id="multiple{{ $type->id }}"
                                                name="allow_multiple"
                                                value="1"
                                                {{ old('allow_multiple', $type->allow_multiple) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="multiple{{ $type->id }}">
                                                Allow Multiple Files
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="doc-modal-section mb-0">
                            <div class="doc-modal-section-title">
                                <i class="fas fa-toggle-on"></i> Document Settings
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
                                        <label class="custom-control-label" for="expiry{{ $type->id }}">Has Expiry Date</label>
                                    </div>
                                </div>

                                <div class="doc-check-card">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="active{{ $type->id }}" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="active{{ $type->id }}">Is Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer doc-modal-footer">
                        <button type="button" class="doc-btn doc-btn-light" data-dismiss="modal">
                            Cancel
                        </button>

                        <button class="doc-btn doc-btn-primary">
                            <i class="fas fa-save"></i> Save Document Type
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach

        {{-- Create Modal Outside Table --}}
        <div class="modal fade orb-doc-modal" id="createDocumentTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('hrms.documents.types.store') }}" class="modal-content doc-modal-content">
                    @csrf

                    <div class="modal-header doc-modal-header">
                        <div>
                            <h5 class="doc-modal-title">Add Document Type</h5>
                            <div class="doc-modal-subtitle">Create employee document or company policy document type.</div>
                        </div>

                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body doc-modal-body">
                        <div class="doc-modal-section">
                            <div class="doc-modal-section-title">
                                <i class="fas fa-plus-circle"></i> Document Type Details
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Aadhaar Card" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="auto generated if empty">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Scope</label>
                                    <select name="scope" class="form-control" required>
                                        <option value="employee" {{ old('scope') === 'employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="policy" {{ old('scope') === 'policy' ? 'selected' : '' }}>Policy</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Applies To</label>
                                    <select name="applies_to" class="form-control" required>
                                        <option value="all" {{ old('applies_to', 'all') === 'all' ? 'selected' : '' }}>All</option>
                                        <option value="fresher" {{ old('applies_to') === 'fresher' ? 'selected' : '' }}>Fresher</option>
                                        <option value="experienced" {{ old('applies_to') === 'experienced' ? 'selected' : '' }}>Experienced</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="doc-modal-section">
                            <div class="doc-modal-section-title">
                                <i class="fas fa-upload"></i> Upload Rules
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label>Allowed Extensions</label>
                                    <div class="doc-ext-grid">
                                        @foreach($extensionOptions as $ext)
                                        <div class="doc-ext-card">
                                            <div class="custom-control custom-checkbox">
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

                                <div class="col-md-6 mb-3">
                                    <label>Max File Size MB</label>
                                    <input type="number"
                                        name="max_file_size_mb"
                                        class="form-control"
                                        min="1"
                                        max="100"
                                        value="{{ old('max_file_size_mb', 5) }}">
                                </div>

                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <div class="doc-check-card w-100">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                class="custom-control-input"
                                                id="newMultiple"
                                                name="allow_multiple"
                                                value="1"
                                                {{ old('allow_multiple') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="newMultiple">
                                                Allow Multiple Files
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="doc-modal-section mb-0">
                            <div class="doc-modal-section-title">
                                <i class="fas fa-toggle-on"></i> Document Settings
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
                                        <label class="custom-control-label" for="newExpiry">Has Expiry Date</label>
                                    </div>
                                </div>

                                <div class="doc-check-card">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="newActive" name="is_active" value="1" checked>
                                        <label class="custom-control-label" for="newActive">Is Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer doc-modal-footer">
                        <button type="button" class="doc-btn doc-btn-light" data-dismiss="modal">
                            Cancel
                        </button>

                        <button class="doc-btn doc-btn-primary">
                            <i class="fas fa-save"></i> Create Document Type
                        </button>
                    </div>
                </form>
            </div>
        </div>

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