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
                    <i class="fas fa-check-circle text-success mb-3" style="font-size: 56px;"></i>
                    <h4 class="font-weight-bold mb-2">Approve Request?</h4>
                    <p class="text-muted mb-3">Are you sure you want to approve and apply this attendance correction request for <strong>{{ $row->employee_display_name ?? $row->name }}</strong>?</p>
                    
                    <div class="p-3 text-left" style="background: rgba(0, 0, 0, 0.02); border-radius: 12px; border: 1px solid #E2E8F0;">
                        <div class="small text-muted font-weight-bold mb-1">REQUEST TYPE</div>
                        <div class="font-weight-bold text-dark mb-2">{{ $typeLabels[$row->request_type] ?? $row->request_type }}</div>
                        <div class="small text-muted font-weight-bold mb-1">DATE</div>
                        <div class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at)))->format('d M Y') }}</div>
                    </div>
                </div>
                <div class="modal-footer" style="background: rgba(255,255,255,0.5);">
                    <button type="button" class="btn btn-light" style="border-radius: 10px;" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4" style="border-radius: 10px; background: #10B981; border: 0;">Approve Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
