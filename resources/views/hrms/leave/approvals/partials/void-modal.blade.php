<!-- VOID (NULL & VOID) MODAL -->
@if($stLower === 'approved' && ($isSuperAdminUser || $isHrAdminUser) && Route::has('leave-approvals.void'))
<div class="modal fade" id="voidModal{{ $lr->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered leave-modal-dialog" role="document" style="max-width: 540px;">
        <div class="modal-content border-0 shadow-lg leave-modal-content">
            <div class="modal-header text-white px-3 px-sm-4 py-3 align-items-center justify-content-between leave-modal-header" style="background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);">
                <div class="d-flex align-items-center" style="gap: 10px; min-width: 0;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(225, 29, 72, 0.2); border: 1px solid rgba(225, 29, 72, 0.4); display: flex; align-items: center; justify-content: center; font-size: 15px; color: #FDA4AF; flex-shrink: 0;">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div style="min-width: 0;">
                        <h5 class="modal-title font-weight-bold text-white mb-0 text-truncate" style="font-size: 15px; letter-spacing: 0.2px;">
                            Mark Leave as Null & Void
                        </h5>
                        <small class="text-white-50 text-truncate" style="font-size: 10.5px;">Request ID: #LR-{{ str_pad($lr->id, 4, '0', STR_PAD_LEFT) }}</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-10 border-0 ml-2" data-dismiss="modal" style="width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 18px; outline: none; line-height: 1; flex-shrink: 0;">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('leave-approvals.void', $lr->id) }}" onsubmit="var btn = this.querySelector('button[type=submit]'); if(btn) { btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin mr-1\'></i> Voiding Leave...'; }">
                @csrf
                <div class="modal-body px-3 px-sm-4 py-3 bg-white" style="overflow-y: auto;">
                    <div class="alert alert-warning mb-3 p-2.5 rounded-lg" style="font-size: 11.5px; border-left: 4px solid #F59E0B; background: #FFFBEB; border-color: #FDE68A; color: #92400E; line-height: 1.45;">
                        <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                        <strong>Impact Notice:</strong> This action will reverse deducted leave balances (Paid/Sick/Comp-off/LWP), restore monthly quota, unlock attendance for the dates, and log a permanent HR audit note.
                    </div>
                    <p class="text-dark font-weight-bold mb-2" style="font-size: 13px; line-height: 1.4;">
                        Are you sure you want to void approved leave for <strong>{{ $lr->display_name }}</strong> ({{ $isSingleDay ? $startDateFormatted : ($startDateFormatted . ' — ' . $endDateFormatted) }})?
                    </p>
                    <div class="form-group mb-1">
                        <label class="font-weight-bold text-muted uppercase mb-1" style="font-size: 10px; letter-spacing: 0.4px;">
                            HR Note / Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="note" class="form-control" rows="3" required minlength="3" maxlength="1000" style="border-radius: 10px; font-size: 12.5px;" placeholder="e.g. Employee worked on this day / informed HR they were working..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-3 px-sm-4 py-2.5 align-items-center justify-content-between leave-modal-footer">
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; color: #475569; font-size: 12.5px;" data-dismiss="modal">
                        Cancel
                    </button>
                    <div class="leave-modal-footer-actions">
                        <button type="submit" class="btn btn-sm font-weight-bold px-3.5 text-white" style="border-radius: 8px; height: 38px; background: #E11D48; border-color: #E11D48; box-shadow: 0 2px 8px rgba(225, 29, 72, 0.3); font-size: 12.5px;">
                            <i class="fas fa-ban mr-1"></i> Confirm Null & Void
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

