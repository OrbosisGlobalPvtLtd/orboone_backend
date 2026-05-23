@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Employee Document Details')

@section('_head')
@include('hrms.documents.partials.styles')
@endsection

@section('_content')
<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; DOCUMENT MANAGEMENT
            </div>
            <h1>{{ $employee->user->name }} - Documents Overview</h1>
            <p>Code: {{ $employee->employee_code }} &bull; Experience: {{ ucfirst($employee->experience_type ?? 'Fresher') }}</p>
        </div>
        <div class="dm-hero-actions">
            <a href="{{ route('documents.hr.index') }}" class="dm-btn dm-btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

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
                        <th>Mandatory</th>
                        <th>Status</th>
                        <th>View File</th>
                        <th width="240">Verification Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documentTypes as $type)
                    @php
                    $doc = $documents->get($type->id);
                    @endphp
                    <tr>
                        <td><span style="font-weight: 800; color: var(--dm-text);">{{ $type->name }}</span></td>
                        <td>
                            @if($type->is_mandatory)
                            <span class="dm-badge dm-badge-danger" style="font-size: 9px; padding: 2px 8px;">Mandatory</span>
                            @else
                            <span class="dm-badge dm-badge-secondary" style="font-size: 9px; padding: 2px 8px;">Optional</span>
                            @endif
                        </td>
                        <td>
                            @if($doc)
                                @if($doc->verification_status == 'pending')
                                <span class="dm-badge dm-badge-warning"><i class="fas fa-hourglass-half mr-1"></i> Pending Verification</span>
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
                                <i class="fas fa-eye mr-1"></i> View File
                            </a>
                            @else
                            <span class="text-muted" style="font-size: 12px;">No file uploaded</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($doc && $doc->verification_status == 'pending')
                                <form action="{{ route('hrms.documents.hr.verify', $doc->id) }}" method="POST" style="display:inline;">
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
                                            <form action="{{ route('hrms.documents.hr.reject', $doc->id) }}" method="POST" style="width: 100%;">
                                                @csrf
                                                <div class="dm-modal-header" style="background: linear-gradient(135deg, #E11D48, #BE123C);">
                                                    <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i>Reject Document</h5>
                                                    <p>Specify the reason for rejecting "{{ $type->name }}" so the employee can upload a corrected version.</p>
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
                                @else
                                <span class="text-muted" style="font-size: 12px; font-weight: 700;">No actions available</span>
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
@endsection