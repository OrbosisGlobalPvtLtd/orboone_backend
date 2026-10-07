                <div class="em-card em-card-full animate__animated animate__fadeInUp" id="documentCard">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-folder-open mr-2"></i>Employee Documents</h5>
                            <div class="em-card-sub">Manage uploaded documents. Upload new or replace files with instant auto-save.</div>
                        </div>
                    </div>

                    <div class="em-card-body" style="padding: 24px;">
                        @if($employeeDocuments && $employeeDocuments->count() > 0)
                        <div class="em-doc-grid">
                            @foreach($employeeDocuments as $doc)
                            @php
                            $docTitle = $doc->document_type_name ?? $doc->title ?? 'Document';
                            $docStatus = strtolower($doc->verification_status ?? 'pending');

                            $docStatusClass = match($docStatus) {
                            'verified' => 'em-doc-verified',
                            'rejected' => 'em-doc-rejected',
                            default => 'em-doc-pending',
                            };

                            $docStatusText = match($docStatus) {
                            'verified' => 'Verified & Locked',
                            'rejected' => 'Rejected / Needs Reupload',
                            default => 'Pending Verification',
                            };

                            $docPath = $doc->file_path ?? null;
                            $docUrl = !empty($docPath) && Route::has('hrms.documents.file')
                            ? route('hrms.documents.file', $docPath)
                            : (!empty($docPath) ? route('hrms.documents.file', ['path' => $docPath]) : null);

                            $documentTypeId = $doc->document_type_id ?? $doc->category_id ?? null;
                            $fileName = $doc->file_original_name ?? null;
                            $fileExt = strtolower(pathinfo($fileName ?? '', PATHINFO_EXTENSION));

                            $iconClass = 'fa-file-alt text-primary';
                            if ($fileExt === 'pdf') {
                            $iconClass = 'fa-file-pdf text-danger';
                            } elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $iconClass = 'fa-file-image text-success';
                            }
                            @endphp

                            <div class="em-doc-item-card em-doc-row">
                                <div class="em-doc-item-main">
                                    <div class="em-doc-item-icon-box">
                                        <i class="fas {{ $iconClass }} fa-2x"></i>
                                    </div>
                                    <div class="em-doc-item-details">
                                        <div class="em-doc-item-title-row">
                                            <span class="em-doc-item-title">{{ $docTitle }}</span>
                                            <span class="em-doc-pill {{ !empty($doc->is_required) ? 'em-doc-required' : 'em-doc-optional' }}">
                                                {{ !empty($doc->is_required) ? 'Required' : 'Optional' }}
                                            </span>
                                        </div>
                                        <div class="em-doc-item-filename" title="{{ $fileName ?? 'No file uploaded' }}">
                                            {{ $fileName ?? 'No file uploaded' }}
                                        </div>
                                        <div class="em-doc-item-meta">
                                            <span class="em-doc-badge {{ $docStatusClass }}">
                                                <i class="fas {{ $docStatus === 'verified' ? 'fa-check-circle' : ($docStatus === 'rejected' ? 'fa-times-circle' : 'fa-clock') }} mr-1"></i>
                                                {{ $docStatusText }}
                                            </span>
                                            @if(!empty($doc->uploaded_at) || !empty($doc->created_at))
                                            <span class="em-doc-item-date">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                {{ \Carbon\Carbon::parse($doc->uploaded_at ?? $doc->created_at)->format('d M Y, h:i A') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="em-doc-item-actions em-doc-actions" style="flex-wrap: wrap; gap: 8px;">
                                    @if($docUrl)
                                    <a href="{{ $docUrl }}" target="_blank" class="btn-doc-action btn-doc-view" title="View Document">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    @endif

                                    @if($doc->id && Route::has('hrms.documents.employee.download'))
                                    <a href="{{ route('documents.employee.download', $doc->id) }}" class="btn-doc-action btn-doc-download" title="Download Document">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                    @endif

                                    @if($documentTypeId && Route::has('hrms.documents.employee.upload_from_profile') && ($docStatus !== 'verified' || auth()->user()->can('company_documents.manage')))
                                    <label class="btn-doc-action btn-doc-edit-pill btn-doc-reupload" data-doc-title="{{ $docTitle }}" title="Re-upload or Edit Document">
                                        <i class="fas fa-edit mr-1"></i>
                                        <span>Re-upload / Edit</span>

                                        <input type="file"
                                            name="file"
                                            data-action="{{ route('documents.employee.upload_from_profile', [$employeeData->id, $documentTypeId]) }}"
                                            class="js-ajax-doc-upload"
                                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                                            required>
                                    </label>
                                    @endif

                                    @if($docStatus === 'pending' && $doc->id && auth()->user()->can('company_documents.manage'))
                                    <button type="button" class="btn-doc-action btn-doc-verify js-ajax-doc-verify" data-action="{{ route('documents.employee.verify', $doc->id) }}" data-doc-title="{{ $docTitle }}" title="Verify Document">
                                        <i class="fas fa-check-circle mr-1"></i> Verify
                                    </button>
                                    <button type="button" class="btn-doc-action btn-doc-reject js-ajax-doc-reject" data-action="{{ route('documents.employee.reject', $doc->id) }}" data-doc-title="{{ $docTitle }}" title="Reject Document">
                                        <i class="fas fa-times-circle mr-1"></i> Reject
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-history text-center py-5">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 24 24' fill='none' stroke='%23d1d5db' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'%3E%3C/path%3E%3Cpolyline points='14 2 14 8 20 8'%3E%3C/polyline%3E%3Cline x1='16' y1='13' x2='8' y2='13'%3E%3C/line%3E%3Cline x1='16' y1='17' x2='8' y2='17'%3E%3C/line%3E%3Cpolyline points='10 9 9 9 8 9'%3E%3C/polyline%3E%3C/svg%3E" alt="No documents" class="mb-3" style="opacity: 0.5;">
                            <div class="font-weight-bold text-muted">No documents found for this employee.</div>
                        </div>
                        @endif
                    </div>
                </div>
