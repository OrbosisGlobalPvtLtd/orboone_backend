@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Employee Document Details')

@section('_head')
@include('hrms.documents.partials.styles')
@endsection

@section('_content')
<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding: 24px; border-radius: 16px; margin-bottom: 24px; background: linear-gradient(135deg, var(--dm-primary) 0%, var(--dm-secondary) 100%);">
        <div>
            <div class="dm-kicker" style="margin-bottom: 8px; font-size: 11px; font-weight: 800; letter-spacing: 1px; color: rgba(255,255,255,0.9);">
                <i class="fas fa-file-signature mr-1"></i> HRMS &bull; DOCUMENT VERIFICATION
            </div>
            <h1 style="margin-bottom: 8px; font-size: 24px; font-weight: 800; color: #fff;">{{ $employee->user->name }} ({{ $employee->employee_code }})</h1>
            <p style="margin-bottom: 0; font-size: 13px; color: rgba(255,255,255,0.85); font-weight: 600;">
                Experience: {{ ucfirst($employee->experience_type ?? 'Fresher') }} &bull; Uploaded: {{ $doc_total }} &bull; Verified: {{ $doc_verified }} &bull; Pending: {{ $doc_pending }}
            </p>
        </div>
        <div class="dm-hero-actions" style="display: flex; gap: 8px; align-items: center;">
            <a href="{{ route('documents.hr.index') }}" class="dm-btn dm-btn-light" style="border-radius: 8px; font-weight: 700; height: 38px;">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
            <a href="{{ route('hrms.employees.profile.view', $employee->id) }}" class="dm-btn dm-btn-light" style="border-radius: 8px; font-weight: 700; height: 38px;">
                <i class="fas fa-user mr-1"></i> View Profile
            </a>
            @if($documents->whereIn('verification_status', ['pending', 'rejected'])->count() > 0)
            <form action="{{ route('documents.hr.verify_employee', $employee->id) }}" method="POST" style="display:inline-block; margin: 0;">
                @csrf
                <button type="submit" class="dm-btn" style="background: #10B981; color: #fff; border: none; font-weight: 800; border-radius: 8px; height: 38px;" onclick="return confirm('Verify all submitted pending/rejected documents for this employee?')">
                    <i class="fas fa-check-double mr-1"></i> Verify All Documents
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row dm-metrics-grid mb-1">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="dm-metric-card border-bottom-primary">
                <div class="dm-metric-icon dm-icon-primary"><i class="fas fa-folder"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Total Uploaded</div>
                    <div class="dm-metric-value">{{ $doc_total }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="dm-metric-card border-bottom-info">
                <div class="dm-metric-icon dm-icon-info"><i class="fas fa-tasks"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Required Docs</div>
                    <div class="dm-metric-value">{{ $doc_required }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="dm-metric-card border-bottom-success">
                <div class="dm-metric-icon dm-icon-success"><i class="fas fa-check-circle"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Verified</div>
                    <div class="dm-metric-value">{{ $doc_verified }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="dm-metric-card border-bottom-warning">
                <div class="dm-metric-icon dm-icon-warning"><i class="fas fa-hourglass-half"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Pending Review</div>
                    <div class="dm-metric-value">{{ $doc_pending }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="dm-metric-card border-bottom-danger">
                <div class="dm-metric-icon dm-icon-danger"><i class="fas fa-times-circle"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Rejected</div>
                    <div class="dm-metric-value">{{ $doc_rejected }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="dm-metric-card" style="border-bottom: 3px solid #64748B !important;">
                <div class="dm-metric-icon" style="background: #F1F5F9 !important; color: #64748B !important;"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Missing Required</div>
                    <div class="dm-metric-value">{{ $doc_missing }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Checklist Table -->
    <div class="dm-card">
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <h5 class="dm-table-title">Employee Document Listing</h5>
                    <p class="dm-table-subtitle">Review verification states of all requested compliance items for this employee.</p>
                </div>
            </div>
        </div>

        <div class="dm-table-wrap">
            <table class="table dm-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>File Name</th>
                        <th>Uploaded At</th>
                        <th>Status</th>
                        <th>Verified By</th>
                        <th>Verified At</th>
                        <th>Rejection Reason</th>
                        <th width="300" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr>
                        <td>
                            <span style="font-weight: 800; color: var(--dm-text);">
                                {{ $doc->documentType->name ?? $doc->title ?? 'Other Document' }}
                            </span>
                            @if($doc->documentType && $doc->documentType->is_mandatory)
                            <span class="dm-badge dm-badge-danger" style="font-size: 8px; padding: 2px 6px; margin-left: 6px;">Mandatory</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 12px; color: var(--dm-muted);">
                                {{ $doc->file_original_name ?: basename($doc->file_path) }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--dm-muted);">
                                {{ $doc->uploaded_at ? \Carbon\Carbon::parse($doc->uploaded_at)->format('d M Y, h:i A') : ($doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->format('d M Y, h:i A') : '-') }}
                            </span>
                        </td>
                        <td>
                            @if($doc->verification_status == 'pending')
                            <span class="dm-badge dm-badge-warning"><i class="fas fa-hourglass-half mr-1"></i> Pending</span>
                            @elseif($doc->verification_status == 'verified')
                            <span class="dm-badge dm-badge-success"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                            @else
                            <span class="dm-badge dm-badge-danger"><i class="fas fa-times-circle mr-1"></i> Rejected</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--dm-muted);">
                                {{ $doc->verifiedBy->name ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--dm-muted);">
                                {{ $doc->verified_at ? \Carbon\Carbon::parse($doc->verified_at)->format('d M Y, h:i A') : '-' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--dm-danger-text); font-weight: 700;">
                                {{ $doc->rejection_reason ?: '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                @if($doc->file_path)
                                <a href="{{ route('hrms.documents.file', $doc->file_path) }}" target="_blank" class="dm-action-btn-pill dm-action-btn-primary" title="View Document">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                @endif

                                @if($doc->verification_status == 'pending')
                                <form action="{{ route('documents.hr.verify', $doc->id) }}" method="POST" style="display:inline-block; margin:0;">
                                    @csrf
                                    <button type="submit" class="dm-action-btn-pill dm-action-btn-success" onclick="return confirm('Verify this document?')">
                                        <i class="fas fa-check mr-1"></i> Verify
                                    </button>
                                </form>
                                <button type="button" class="dm-action-btn-pill dm-action-btn-danger" data-toggle="modal" data-target="#rejectModal{{ $doc->id }}">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                                        <div class="modal-content" style="border: none; border-radius: 24px; overflow: hidden; box-shadow: var(--dm-shadow);">
                                            <form action="{{ route('documents.hr.reject', $doc->id) }}" method="POST" style="width: 100%;">
                                                @csrf
                                                <div class="dm-modal-header" style="background: linear-gradient(135deg, #E11D48, #BE123C);">
                                                    <h5 class="modal-title" style="color: #fff !important;"><i class="fas fa-times-circle mr-2"></i>Reject Document</h5>
                                                    <p style="color: rgba(255,255,255,0.9) !important; margin-top: 4px;">Specify the reason for rejecting "{{ $doc->documentType->name ?? $doc->title ?? 'Document' }}" so the employee can upload a corrected version.</p>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                                                </div>
                                                <div class="dm-modal-body">
                                                    <div class="dm-form-group">
                                                        <label>Reason for Rejection <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" id="rejection_reason_{{ $doc->id }}" class="form-control" required rows="3" placeholder="Explain what is missing or incorrect..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="dm-modal-footer">
                                                    <button type="button" class="dm-btn dm-btn-dark-light" style="height: 38px;" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="dm-btn dm-btn-danger" style="height: 38px; background: #E11D48; border-color: #E11D48;">
                                                        <i class="fas fa-check mr-1"></i> Confirm Reject
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 32px; color: var(--dm-muted); font-size: 14px;">
                            <i class="fas fa-file-excel fa-2x mb-2" style="display: block; opacity: 0.5;"></i>
                            No documents submitted by this employee yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection