@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'My Documents')

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

    .eo-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .eo-card-head {
        padding: 16px;
        border-bottom: 1px solid var(--orb-border);
        background: #FCFCFD;
    }

    .eo-btn {
        min-height: 38px;
        border: 0;
        border-radius: 12px;
        padding: 0 13px;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-decoration: none !important;
        cursor: pointer;
        white-space: nowrap;
    }

    .eo-btn-primary {
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    }

    .eo-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    .eo-btn-view {
        background: #EEF4FF;
        color: #3538CD;
    }

    .eo-btn-view:hover {
        background: #3538CD;
        color: #fff;
    }

    .eo-btn-warning {
        background: #FFF7E8;
        color: #B54708;
    }

    .eo-btn-warning:hover {
        background: #F79009;
        color: #fff;
    }

    .eo-btn-danger {
        background: rgba(236, 78, 116, .10);
        color: #C01048;
    }

    .eo-btn-danger:hover {
        background: #EC4E74;
        color: #fff;
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

    .eo-empty {
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .eo-alert {
        border: 0;
        border-radius: 14px;
        font-weight: 800;
        padding: 12px 14px;
        margin-bottom: 12px;
    }

    .eo-alert-success {
        background: rgba(18, 183, 106, .12);
        color: #067647;
    }

    .eo-alert-danger {
        background: rgba(236, 78, 116, .10);
        color: #C01048;
    }

    .eo-modal .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(16, 24, 40, .22);
    }

    .eo-modal .modal-header {
        border: 0;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        padding: 16px 18px;
    }

    .eo-modal .modal-title {
        font-weight: 900;
        font-size: 16px;
    }

    .eo-modal .close {
        color: #fff;
        opacity: 1;
        text-shadow: none;
    }

    .eo-modal .modal-body {
        padding: 18px;
        background: #fff;
    }

    .eo-modal .modal-footer {
        border-top: 1px solid var(--orb-border);
        padding: 14px 18px;
        background: #FCFCFD;
    }

    .eo-field label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .eo-control {
        width: 100%;
        height: 44px;
        border-radius: 13px !important;
        border: 1px solid var(--orb-border) !important;
        background: #F9FAFB !important;
        color: var(--orb-text) !important;
        font-size: 13px;
        font-weight: 800;
        padding: 8px 12px;
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

    @media(max-width:992px) {
        .eo-header {
            flex-direction: column;
            align-items: flex-start
        }

        .eo-table {
            min-width: 850px
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch
        }
    }

    @media(max-width:576px) {
        .eo-page {
            padding: 12px 8px 24px
        }

        .eo-header,
        .eo-card {
            border-radius: 16px
        }

        .eo-title {
            font-size: 20px
        }

        .eo-actions {
            flex-direction: column;
            align-items: stretch
        }

        .eo-btn {
            width: 100%
        }
    }
</style>

@php
$isDisabled = ($employee->profile && in_array($employee->profile->profile_status, ['submitted', 'approved']));
@endphp

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header">
            <div>
                <h1 class="eo-title">My Documents</h1>
                <p class="eo-subtitle">Upload and manage your required documents</p>
            </div>

            <div>
                @if(!$employee->profile || $employee->profile->profile_status == 'pending' || $employee->profile->profile_status == 'rejected')
                <form action="{{ route('hrms.documents.self.submit_verification') }}" method="POST">
                    @csrf
                    <button type="submit" class="eo-btn eo-btn-primary" onclick="return confirm('Are you sure you want to submit? Editing will be disabled.')">
                        <i class="fas fa-paper-plane"></i> Submit for Verification
                    </button>
                </form>
                @elseif($employee->profile->profile_status == 'submitted')
                <span class="eo-pill eo-pill-warning"><span class="eo-dot"></span> Under Verification</span>
                @elseif($employee->profile->profile_status == 'approved')
                <span class="eo-pill eo-pill-active"><span class="eo-dot"></span> Verified</span>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="eo-alert eo-alert-success"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="eo-alert eo-alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif

        <div class="eo-card">
            <div class="eo-card-head">
                <h4 style="margin:0;font-size:16px;font-weight:900;color:var(--orb-text);">Document Checklist</h4>
            </div>

            <div class="table-responsive">
                <table class="table eo-table">
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

                                    @if(($type->has_expiry_date ?? false) || ($type->has_expiry ?? false))
                                    <span class="eo-mini-pill eo-pill-warning">Expiry Required</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if($doc)
                                @if($doc->verification_status == 'pending')
                                <span class="eo-pill eo-pill-warning"><span class="eo-dot"></span> Pending</span>
                                @elseif($doc->verification_status == 'verified')
                                <span class="eo-pill eo-pill-active"><span class="eo-dot"></span> Verified</span>
                                @else
                                <span class="eo-pill eo-pill-danger"><span class="eo-dot"></span> Rejected</span>
                                @if($doc->rejection_reason)
                                <div class="eo-doc-meta text-danger">{{ $doc->rejection_reason }}</div>
                                @endif
                                @endif
                                @else
                                <span class="eo-pill eo-pill-muted"><span class="eo-dot"></span> Missing</span>
                                @endif
                            </td>

                            <td>
                                @if($doc && $doc->file_path)
                                <a href="{{ route('hrms.documents.file', $doc->file_path) }}" target="_blank" class="eo-btn eo-btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @else
                                <span class="eo-empty">No file uploaded</span>
                                @endif
                            </td>

                            <td>
                                <div class="eo-actions">
                                    @if(!$isDisabled || ($doc && $doc->verification_status == 'rejected'))
                                    @if($doc)
                                    <button type="button" class="eo-btn eo-btn-warning" data-toggle="modal" data-target="#replaceModal{{ $type->id }}">
                                        <i class="fas fa-sync-alt"></i> Replace
                                    </button>

                                    <form action="{{ route('hrms.documents.self.destroy', $doc->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="eo-btn eo-btn-danger" onclick="return confirm('Delete this document?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" class="eo-btn eo-btn-primary" data-toggle="modal" data-target="#uploadModal{{ $type->id }}">
                                        <i class="fas fa-cloud-upload-alt"></i> Upload
                                    </button>
                                    @endif
                                    @else
                                    <span class="eo-empty">Locked after submission</span>
                                    @endif
                                </div>

                                <div class="modal fade eo-modal" id="{{ $doc ? 'replace' : 'upload' }}Modal{{ $type->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ $doc ? route('hrms.documents.self.replace', $doc->id) : route('hrms.documents.self.upload') }}"
                                                method="POST"
                                                enctype="multipart/form-data">
                                                @csrf

                                                @if(!$doc)
                                                <input type="hidden" name="document_type_id" value="{{ $type->id }}">
                                                @endif

                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ $doc ? 'Replace' : 'Upload' }} {{ $type->name }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="eo-field">
                                                        <label>Select File</label>
                                                        <label class="eo-file-upload">
                                                            <div class="eo-file-left">
                                                                <div class="eo-file-icon"><i class="fas fa-paperclip"></i></div>
                                                                <div class="eo-file-title selected-file-name">Choose file</div>
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
                                                    <div class="eo-field mt-3">
                                                        <label>Expiry Date</label>
                                                        <input type="date" name="expiry_date" class="eo-control" value="{{ $doc && $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : '' }}" required>
                                                    </div>
                                                    @endif
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="eo-btn eo-btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="eo-btn eo-btn-primary">
                                                        <i class="fas fa-save"></i> Save
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
</div>

<script>
    document.querySelectorAll('.js-file-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const box = this.closest('.eo-file-upload').querySelector('.selected-file-name');
            box.textContent = this.files && this.files.length ? this.files[0].name : 'Choose file';
        });
    });
</script>
@endsection