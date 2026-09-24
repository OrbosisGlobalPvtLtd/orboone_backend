<!-- VIEW DETAILS MODAL -->
<div class="modal fade glass-modal" id="viewModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i> Request Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Employee Passport / Profile Card -->
                <div class="d-flex align-items-center mb-4 p-3" style="background: rgba(255, 255, 255, 0.7); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px;">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" class="avatar-modal rounded-circle" alt="">
                    @else
                        <div class="avatar-modal rounded-circle d-inline-flex align-items-center justify-content-center" style="background: var(--orb-soft); color: var(--orb-primary); font-weight: 900; font-size: 18px;">
                            {{ $initials }}
                        </div>
                    @endif
                    <div class="ml-3">
                        <h5 class="font-weight-bold text-dark mb-1">{{ $row->employee_display_name ?? $row->name }}</h5>
                        <div class="text-muted small mb-1"><i class="fas fa-id-badge mr-1"></i> {{ $row->employee_code }}</div>
                        <div class="badge badge-light px-2 py-1 font-weight-bold text-primary">{{ $deptName }} &bull; {{ $desigName }}</div>
                    </div>
                </div>

                <!-- Attendance info card -->
                <div class="detail-card">
                    <div class="detail-card-title"><i class="fas fa-calendar-alt mr-2"></i> Attendance Information</div>
                    <div class="detail-row">
                        <span class="detail-label">Attendance Date</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at)))->format('d M Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Current Shift</span>
                        <span class="detail-value">{{ $shiftName }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Current Status</span>
                        <span class="detail-value">{{ ucfirst($currentStatusText) }}</span>
                    </div>
                </div>

                <!-- Request information card -->
                <div class="detail-card">
                    <div class="detail-card-title"><i class="fas fa-align-left mr-2"></i> Request Information</div>
                    <div class="detail-row">
                        <span class="detail-label">Request Type</span>
                        <span class="detail-value text-primary">{{ $typeLabels[$row->request_type] ?? ucfirst(str_replace('_', ' ', $row->request_type)) }}</span>
                    </div>
                    
                    @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out', 'missed_punch_in', 'late_mark_exemption'], true))
                    <div class="detail-row">
                        <span class="detail-label">Current Punch In</span>
                        <span class="detail-value">{{ $row->existing_punch_in ? \Carbon\Carbon::parse($row->existing_punch_in)->format('h:i A') : 'N/A' }}</span>
                    </div>
                    @endif
                    
                    @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out', 'missed_punch_in', 'early_logout_correction'], true))
                    <div class="detail-row">
                        <span class="detail-label">Current Punch Out</span>
                        <span class="detail-value">{{ $row->existing_punch_out ? \Carbon\Carbon::parse($row->existing_punch_out)->format('h:i A') : 'N/A' }}</span>
                    </div>
                    @endif

                    @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_in'], true))
                    <div class="detail-row">
                        <span class="detail-label">Requested Punch In</span>
                        <span class="detail-value text-success">{{ $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('h:i A') : 'N/A' }}</span>
                    </div>
                    @endif

                    @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out'], true))
                    <div class="detail-row">
                        <span class="detail-label">Requested Punch Out</span>
                        <span class="detail-value text-success">{{ $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('h:i A') : 'N/A' }}</span>
                    </div>
                    @endif

                    @if($row->request_type === 'other' && str_contains($row->reason, '[Attendance Status Correction:'))
                        @php
                            preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                            $reqStatus = $matches[1] ?? 'N/A';
                        @endphp
                        <div class="detail-row">
                            <span class="detail-label">Requested Status</span>
                            <span class="detail-value text-success font-weight-bold">{{ $reqStatus }}</span>
                        </div>
                    @endif
                </div>

                <!-- Reason text -->
                <div class="detail-card">
                    <div class="detail-card-title"><i class="fas fa-question-circle mr-2"></i> Employee Submission Reason</div>
                    <p class="mb-0 text-dark font-weight-bold" style="font-size: 13px; line-height: 1.5;">
                        {{ str_replace(['[Attendance Status Correction: Present]', '[Attendance Status Correction: Half Day]', '[Attendance Status Correction: Absent]', '[Attendance Status Correction: present]', '[Attendance Status Correction: half_day]', '[Attendance Status Correction: absent]'], '', $row->reason) }}
                    </p>
                </div>

                @if($row->rejection_reason)
                <div class="detail-card" style="background: rgba(254, 242, 242, 0.7); border-color: rgba(254, 202, 202, 0.8);">
                    <div class="detail-card-title text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Rejection Reason / Note</div>
                    <p class="mb-0 text-danger font-weight-bold" style="font-size: 13px; line-height: 1.5;">
                        {{ $row->rejection_reason }}
                    </p>
                </div>
                @endif

                <!-- Approval timeline -->
                <div class="detail-card">
                    <div class="detail-card-title"><i class="fas fa-clock mr-2"></i> Request History Timeline</div>
                    <div class="timeline-item">
                        <div class="timeline-label">Submitted for Approval</div>
                        <div class="timeline-time">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y h:i A') }}</div>
                    </div>
                    @if($row->status === 'approved')
                    <div class="timeline-item success">
                        <div class="timeline-label text-success">Approved & Synced to Log</div>
                        <div class="timeline-time">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d M Y h:i A') : 'N/A' }}</div>
                    </div>
                    @elseif($row->status === 'rejected')
                    <div class="timeline-item danger">
                        <div class="timeline-label text-danger">Rejected by HR / Admin</div>
                        <div class="timeline-time">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d M Y h:i A') : 'N/A' }}</div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer" style="background: rgba(255,255,255,0.5); display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn btn-secondary font-weight-bold" style="border-radius: 10px;" data-dismiss="modal">Close</button>
                @if($row->status === 'pending' && !empty($canApproveThisRow))
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <button type="button" class="btn btn-danger font-weight-bold shadow-sm" style="border-radius: 10px; padding: 6px 16px;" data-toggle="modal" data-target="#rejectModal{{ $row->id }}" data-dismiss="modal">
                            <i class="fas fa-times-circle mr-1"></i> Reject Request
                        </button>
                        <button type="button" class="btn btn-success font-weight-bold shadow-sm" style="border-radius: 10px; background: #10B981; border: 0; padding: 6px 16px;" data-toggle="modal" data-target="#approveModal{{ $row->id }}" data-dismiss="modal">
                            <i class="fas fa-check-circle mr-1"></i> Approve Request
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
