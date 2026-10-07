{{-- Employee Documents Card --}}
<div class="profile-card">
    <div class="profile-card-head">
        <div class="profile-icon"><i class="fas fa-folder-open"></i></div>
        <div style="flex:1;">
            <h5>Employee Documents</h5>
            <p>{{ $isDocEditMode ? 'Select file to upload or re-upload document.' : 'Document name, status and verification actions.' }}</p>
        </div>
    </div>

    <div class="profile-card-body">
        @if($documents->count())
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Uploaded At</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($documents as $doc)
                    @php
                    $docStatus = strtolower($doc->verification_status ?? 'pending');
                    $docStatusClass = match($docStatus) {
                        'verified' => 'doc-verified',
                        'rejected' => 'doc-rejected',
                        default => 'doc-pending',
                    };

                    $docTitle = $doc->document_type_name ?? $doc->title ?? 'Document';
                    $docPath = $doc->file_path ?? null;
                    $docUrl = null;

                    if (!empty($doc->file_url)) {
                        $docUrl = $doc->file_url;
                    } elseif (!empty($docPath) && Route::has('hrms.documents.file')) {
                        $docUrl = route('hrms.documents.file', $docPath);
                    } elseif (!empty($docPath)) {
                        $docUrl = route('hrms.documents.file', ['path' => $docPath]);
                    }

                    $ext = strtolower(pathinfo($doc->file_original_name ?: $docPath, PATHINFO_EXTENSION));
                    $documentTypeId = $doc->document_type_id ?? $doc->category_id ?? null;
                    @endphp

                    <tr>
                        <td data-label="Document">
                            <div class="doc-name-cell">
                                <div class="doc-icon"><i class="fas fa-file-alt"></i></div>
                                <div>
                                    <div class="doc-title">{{ $docTitle }}</div>
                                    <div class="doc-sub">{{ $doc->file_original_name ?? 'No file uploaded' }}</div>
                                </div>
                            </div>
                        </td>

                        <td data-label="Status">
                            <span class="doc-pill {{ $docStatusClass }}">{{ ucfirst($docStatus) }}</span>
                        </td>

                        <td data-label="Uploaded At">
                            @if(!empty($doc->uploaded_at))
                            {{ \Carbon\Carbon::parse($doc->uploaded_at)->format('d M Y, h:i A') }}
                            @elseif(!empty($doc->created_at))
                            {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y, h:i A') }}
                            @else
                            -
                            @endif
                        </td>

                        <td data-label="Action">
                            <div class="doc-actions">
                                @if(!empty($docUrl))
                                <a href="{{ $docUrl }}" target="_blank" rel="noopener noreferrer" class="doc-action-btn doc-view-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @endif

                                @if($isDocEditMode)
                                    @if($documentTypeId && Route::has('hrms.documents.employee.upload_from_profile'))
                                    <form action="{{ route('documents.employee.upload_from_profile', [$profile->employee_id, $documentTypeId]) }}"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        class="doc-upload-card-form js-auto-upload-form">
                                        @csrf

                                        <label class="doc-upload-card">
                                            <input type="file"
                                                name="file"
                                                class="js-auto-upload-input"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                required>

                                            <span class="doc-upload-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </span>

                                            <span class="doc-upload-text">
                                                {{ !empty($doc->file_path) ? 'Re-upload' : 'Upload' }}
                                            </span>

                                            <small>PDF, JPG, PNG, WEBP</small>
                                        </label>
                                    </form>
                                    @endif
                                @else
                                    @if($docStatus === 'verified')
                                    <button type="button" class="doc-action-btn doc-disabled-btn" disabled>
                                        <i class="fas fa-lock"></i> Verified
                                    </button>
                                    @else
                                        @if(Route::has('hrms.documents.employee.verify'))
                                        <form action="{{ route('documents.employee.verify', $doc->id) }}" method="POST" style="display:inline-block;margin:0;">
                                            @csrf
                                            <button type="submit" class="doc-action-btn doc-verify-btn" onclick="return confirm('Verify this document?')">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                        </form>
                                        @endif

                                        @if(Route::has('hrms.documents.employee.reject'))
                                        <form action="{{ route('documents.employee.reject', $doc->id) }}" method="POST" style="display:inline-block;margin:0;">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" value="Document rejected by HR">
                                            <button type="submit" class="doc-action-btn doc-reject-btn" onclick="return confirm('Reject this document?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="profile-value muted">No documents uploaded yet.</div>
        @endif
    </div>
</div>

{{-- HR Review Card --}}
<div class="profile-card review-card">
    <div class="profile-card-head">
        <div class="profile-icon"><i class="fas fa-user-check"></i></div>
        <div>
            <h5>HR Review</h5>
            <p>
                @if($isFullEditMode)
                Update employee profile and documents.
                @elseif($isDocOnlyEditMode)
                Upload or re-upload employee documents only.
                @else
                Approve or reject employee profile.
                @endif
            </p>
        </div>
    </div>

    <div class="profile-card-body review-clean-body">
        <div class="review-clean-top">
            <div class="review-mini-stat">
                <span>Profile Status</span>
                <div class="status-badge {{ $statusClass }}">
                    <i class="fas fa-circle"></i>
                    {{ $statusText }}
                </div>
            </div>

            <div class="review-mini-stat">
                <span>Documents</span>
                <strong>{{ $verifiedDocs }}/{{ $documents->count() }}</strong>
                <small>Verified</small>
            </div>

            <div class="review-mini-stat warning">
                <span>Pending</span>
                <strong>{{ $pendingDocs }}</strong>
                <small>Documents</small>
            </div>

            <div class="review-mini-stat danger">
                <span>Rejected</span>
                <strong>{{ $rejectedDocs }}</strong>
                <small>Documents</small>
            </div>
        </div>

        @if(!empty($profile->rejection_reason))
        <div class="review-reason">
            <strong>Rejection Reason:</strong>
            {{ $profile->rejection_reason }}
        </div>
        @endif

        <div class="review-note-clean">
            <i class="fas fa-info-circle"></i>
            @if($isFullEditMode)
            Save button will update profile fields. Document file selection uploads separately.
            @elseif($isDocOnlyEditMode)
            Select a file in document list to auto upload or re-upload.
            @else
            Approve only after checking all submitted documents.
            @endif
        </div>

        <div class="review-clean-actions">
            @if($isFullEditMode)
            <button type="submit" form="profileInlineForm" class="btn-successx">
                <i class="fas fa-save"></i> Update Profile
            </button>

            <a href="{{ route('hrms.employees.profile.view', $profile->employee_id) }}" class="btn-dangerx">
                <i class="fas fa-times-circle"></i> Cancel
            </a>
            @elseif($isDocOnlyEditMode)
            <a href="{{ route('hrms.employees.profile.view', $profile->employee_id) }}" class="btn-dangerx">
                <i class="fas fa-times-circle"></i> Cancel Document Edit
            </a>
            @else
            @if($status === 'approved')
            <div class="review-approved-box">
                <i class="fas fa-check-circle"></i>
                Profile already approved
            </div>
            @else
            @if(Route::has('hrms.employees.profile.approve') && $status === 'submitted')
            <form action="{{ route('hrms.employees.profile.approve', $profile->employee_id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-successx"
                    onclick="return confirm('Approve profile and verify all uploaded documents?')">
                    <i class="fas fa-check-circle"></i>
                    Approve Profile
                </button>
            </form>
            @elseif($status === 'pending')
            <button type="button" class="btn-soft" disabled>
                <i class="fas fa-clock"></i>
                Waiting for Submission
            </button>
            @endif

            @if(Route::has('hrms.employees.profile.reject') && in_array($status, ['submitted', 'rejected']))
            <form action="{{ route('hrms.employees.profile.reject', $profile->employee_id) }}" method="POST" class="review-reject-form">
                @csrf
                <input type="text" name="rejection_reason" class="form-control" placeholder="Reject reason" value="{{ $profile->rejection_reason ?? '' }}">

                <button type="submit" class="btn-dangerx" onclick="return confirm('Reject this profile?')">
                    <i class="fas fa-times-circle"></i>
                    Reject
                </button>
            </form>
            @endif
            @endif
            @endif
        </div>
    </div>
</div>

{{-- Document Preview Modal --}}
<div class="modal fade" id="docPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="docPreviewDialog" role="document">
        <div class="modal-content">
            <div class="doc-preview-head">
                <h5 class="doc-preview-title" id="docPreviewTitle">Document Preview</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color:#fff;">&times;</span>
                </button>
            </div>

            <div id="docPreviewBody" class="doc-preview-body"></div>
        </div>
    </div>
</div>
