<!-- Attendance Audit Detail Modal -->
<div class="modal fade" id="attendanceAuditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document" style="max-width: 680px !important; width: 95% !important; margin: 1.75rem auto !important;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden; background: #fff;">
            
            <!-- Orbo Gradient Header -->
            <div class="modal-header d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #7000FF 0%, #E6007A 100%) !important; padding: 16px 22px; color: #fff; border: 0; margin: 0;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0 text-white d-flex align-items-center" style="font-size: 16px; letter-spacing: -0.2px;">
                        <i class="fas fa-calendar-check mr-2 text-white-50"></i> Attendance Audit Detail
                    </h5>
                    <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; opacity: 0.9;">
                        Comprehensive punch audit, shift tracking & compliance metrics.
                    </small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.22); display: inline-flex; align-items: center; justify-content: center; border: 0; color: #ffffff !important; font-size: 15px; opacity: 1; outline: none; cursor: pointer; padding: 0; margin: 0; line-height: 1; transition: all 0.2s ease;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <!-- Modal Body (Compact & Clean Standard) -->
            <div class="modal-body px-4 py-3" id="attendanceAuditModalBody" style="background: #fff; max-height: calc(85vh - 120px); overflow-y: auto;">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-circle-notch fa-spin fa-2x mb-2 text-primary"></i>
                    <div style="font-size: 13px;">Loading attendance details...</div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer d-flex justify-content-end" style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 12px 20px;">
                <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px; font-size: 13px; padding: 6px 18px; font-weight: 700; border: 1px solid #CBD5E1; background: #fff;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
