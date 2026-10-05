<!-- VIEW DETAILS MODAL -->
<div class="modal fade glass-modal" id="viewModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt"></i> Request Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Employee Passport / Profile Card -->
                <div class="modal-emp-card">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" class="avatar-modal rounded-circle" alt="">
                    @else
                        <div class="avatar-modal rounded-circle d-inline-flex align-items-center justify-content-center" style="background: var(--orb-soft); color: var(--orb-primary); font-weight: 900; font-size: 16px;">
                            {{ $initials }}
                        </div>
                    @endif
                    <div class="modal-emp-info flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="mb-0">{{ $row->employee_display_name ?? $row->name }}</h6>
                            @if($row->status === 'approved')
                                <span class="orb-badge orb-badge-success"><i class="fas fa-check-circle"></i> Approved</span>
                            @elseif($row->status === 'rejected')
                                <span class="orb-badge orb-badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                            @else
                                <span class="orb-badge orb-badge-warning"><i class="fas fa-clock"></i> Pending</span>
                            @endif
                        </div>
                        <div class="modal-emp-meta mt-1">
                            <span class="modal-emp-code-pill"><i class="fas fa-id-badge mr-1"></i> {{ $row->employee_code }}</span>
                            <span class="modal-emp-dept-pill">{{ $deptName }} &bull; {{ $desigName }}</span>
                        </div>
                    </div>
                </div>

                <!-- Attendance Information Card -->
                <div class="detail-card">
                    <div class="detail-card-title">
                        <i class="fas fa-calendar-alt text-primary"></i> Attendance Information
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Attendance Date</span>
                            <span class="detail-value text-dark">
                                <i class="far fa-calendar-check mr-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at)))->format('d M Y') }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Current Shift</span>
                            <span class="detail-value text-dark">{{ $shiftName }}</span>
                        </div>
                        <div class="detail-item full-width">
                            <span class="detail-label">Current Attendance Status</span>
                            <span class="detail-value text-dark">
                                <span class="badge badge-light border px-2 py-1">{{ ucfirst($currentStatusText) }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Request Information Card -->
                <div class="detail-card">
                    <div class="detail-card-title">
                        <i class="fas fa-sliders-h text-primary"></i> Request Information
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item {{ !in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out', 'missed_punch_in'], true) ? 'full-width' : '' }}">
                            <span class="detail-label">Request Type</span>
                            <span class="detail-value text-primary font-weight-bold">
                                {{ $typeLabels[$row->request_type] ?? ucfirst(str_replace('_', ' ', $row->request_type)) }}
                            </span>
                        </div>

                        @if($row->request_type === 'other' && str_contains($row->reason, '[Attendance Status Correction:'))
                            @php
                                preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                                $reqStatus = $matches[1] ?? 'N/A';
                            @endphp
                            <div class="detail-item">
                                <span class="detail-label">Requested Status</span>
                                <span class="detail-value text-success font-weight-bold">{{ $reqStatus }}</span>
                            </div>
                        @endif

                        @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out', 'missed_punch_in', 'late_mark_exemption'], true))
                            <div class="detail-item">
                                <span class="detail-label">Current Punch In</span>
                                <span class="detail-value text-muted">
                                    {{ $row->existing_punch_in ? \Carbon\Carbon::parse($row->existing_punch_in)->format('h:i A') : 'N/A' }}
                                </span>
                            </div>
                        @endif

                        @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_in'], true))
                            <div class="detail-item">
                                <span class="detail-label">Requested Punch In</span>
                                <span class="detail-value text-success font-weight-bold">
                                    <i class="fas fa-arrow-circle-right text-success mr-1"></i>
                                    {{ $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('h:i A') : 'N/A' }}
                                </span>
                            </div>
                        @endif

                        @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out', 'missed_punch_in', 'early_logout_correction'], true))
                            <div class="detail-item">
                                <span class="detail-label">Current Punch Out</span>
                                <span class="detail-value text-muted">
                                    {{ $row->existing_punch_out ? \Carbon\Carbon::parse($row->existing_punch_out)->format('h:i A') : 'N/A' }}
                                </span>
                            </div>
                        @endif

                        @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out'], true))
                            <div class="detail-item">
                                <span class="detail-label">Requested Punch Out</span>
                                <span class="detail-value text-success font-weight-bold">
                                    <i class="fas fa-arrow-circle-right text-success mr-1"></i>
                                    {{ $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('h:i A') : 'N/A' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Reason Card -->
                <div class="detail-card">
                    <div class="detail-card-title">
                        <i class="fas fa-comment-alt text-primary"></i> Employee Submission Reason
                    </div>
                    <div class="detail-reason-box">
                        {{ str_replace(['[Attendance Status Correction: Present]', '[Attendance Status Correction: Half Day]', '[Attendance Status Correction: Absent]', '[Attendance Status Correction: present]', '[Attendance Status Correction: half_day]', '[Attendance Status Correction: absent]'], '', $row->reason) ?: 'No specific reason provided.' }}
                    </div>
                </div>

                @if($row->rejection_reason)
                <div class="detail-card" style="border-color: #FEE2E2;">
                    <div class="detail-card-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Rejection Reason / Note
                    </div>
                    <div class="detail-reject-box">
                        {{ $row->rejection_reason }}
                    </div>
                </div>
                @endif

                <!-- Timeline Card -->
                <div class="timeline-card">
                    <div class="detail-card-title">
                        <i class="fas fa-history text-primary"></i> Request History Timeline
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-label">Submitted for Approval</div>
                        <div class="timeline-time">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}</div>
                    </div>
                    @if($row->status === 'approved')
                    <div class="timeline-item success">
                        <div class="timeline-label text-success">Approved & Synced to Log</div>
                        <div class="timeline-time">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d M Y, h:i A') : 'N/A' }}</div>
                    </div>
                    @elseif($row->status === 'rejected')
                    <div class="timeline-item danger">
                        <div class="timeline-label text-danger">Rejected by HR / Admin</div>
                        <div class="timeline-time">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d M Y, h:i A') : 'N/A' }}</div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border font-weight-bold" style="border-radius: 10px; font-size: 13px;" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                @if($row->status === 'pending' && !empty($canApproveThisRow))
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <button type="button" class="btn btn-danger font-weight-bold shadow-sm" style="border-radius: 10px; padding: 7px 16px; font-size: 13px;" data-toggle="modal" data-target="#rejectModal{{ $row->id }}" data-dismiss="modal">
                            <i class="fas fa-times-circle mr-1"></i> Reject
                        </button>
                        <button type="button" class="btn btn-success font-weight-bold shadow-sm" style="border-radius: 10px; background: #10B981; border: 0; padding: 7px 16px; font-size: 13px;" data-toggle="modal" data-target="#approveModal{{ $row->id }}" data-dismiss="modal">
                            <i class="fas fa-check-circle mr-1"></i> Approve
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
