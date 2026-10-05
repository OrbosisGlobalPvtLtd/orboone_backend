<!-- APPROVE MODAL -->
<div class="modal fade glass-modal" id="approveModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669) !important;">
                <h5 class="modal-title"><i class="fas fa-check-circle mr-2"></i> Approve Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('hrms.attendance.regularizations.approve', $row->id) }}">
                @csrf
                <div class="modal-body text-center py-4">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: #ECFDF5; color: #10B981; font-size: 32px; border: 2px solid #A7F3D0;">
                        <i class="fas fa-check"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">Approve Regularization Request?</h5>
                    <p class="text-muted small mb-3">Are you sure you want to approve and apply this attendance correction request for <strong>{{ $row->employee_display_name ?? $row->name }}</strong>?</p>
                    
                    <div class="p-3 text-left" style="background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small font-weight-bold text-muted text-uppercase">Request Type</span>
                            <span class="font-weight-bold text-primary small">{{ $typeLabels[$row->request_type] ?? $row->request_type }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small font-weight-bold text-muted text-uppercase">Attendance Date</span>
                            <span class="font-weight-bold text-dark small">{{ \Carbon\Carbon::parse($row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at)))->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border font-weight-bold" style="border-radius: 10px; font-size: 13px;" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4 shadow-sm" style="border-radius: 10px; background: #10B981; border: 0; font-size: 13px;">
                        <i class="fas fa-check mr-1"></i> Approve Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
