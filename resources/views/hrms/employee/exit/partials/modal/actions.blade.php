<!-- Flow Management Section Cards -->
<div class="eo-action-card mb-3">
    <div class="eo-action-card-head">
        <div class="eo-action-icon" style="background: var(--orb-soft); color: var(--orb-primary);"><i class="fas fa-sync-alt"></i></div>
        <div>
            <div class="eo-action-title">Refresh Exit Status</div>
            <div class="eo-action-sub">Sync clearance levels, assets, FNF and documents from modules.</div>
        </div>
    </div>
    <form action="{{ route('hrms.employees.exit.refresh', $employee->id) }}" method="POST" class="mb-0">
        @csrf
        <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
        <div class="eo-action-body d-flex justify-content-between align-items-center flex-wrap gap-2 eo-action-flex-row">
            <span class="text-muted small">Update status values dynamically based on live asset/clearance records.</span>
            <button type="submit" class="btn btn-primary btn-orb px-3 py-2 eo-action-submit-btn" style="height:36px; min-height:36px; font-size:12px;">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Status
            </button>
        </div>
    </form>
</div>

<div class="eo-action-card mb-3">
    <div class="eo-action-card-head">
        <div class="eo-action-icon" style="background: #EEF2FF; color: #3730A3;"><i class="fas fa-sliders-h"></i></div>
        <div>
            <div class="eo-action-title">Update Clearance Status</div>
            <div class="eo-action-sub">Set cleared/waived statuses before final exit approval.</div>
        </div>
    </div>
    <form action="{{ route('hrms.employees.exit.clearance.update', $employee->id) }}" method="POST" class="mb-0 js-overall-clearance-form" data-employee-id="{{ $employee->id }}">
        @csrf
        <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
        <div class="eo-action-body">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="eo-label">Exit Type</label>
                    <select name="exit_type" class="eo-control">
                        <option value="resignation" {{ strtolower($exitType) === 'resignation' ? 'selected' : '' }}>Resignation</option>
                        <option value="termination" {{ strtolower($exitType) === 'termination' ? 'selected' : '' }}>Termination</option>
                        <option value="discontinued" {{ strtolower($exitType) === 'discontinued' ? 'selected' : '' }}>Discontinuation</option>
                        <option value="absconding" {{ strtolower($exitType) === 'absconding' ? 'selected' : '' }}>Absconding</option>
                        <option value="retirement" {{ strtolower($exitType) === 'retirement' ? 'selected' : '' }}>Retirement</option>
                        <option value="contract_end" {{ strtolower($exitType) === 'contract_end' ? 'selected' : '' }}>End of Contract</option>
                        <option value="internship_completed" {{ strtolower($exitType) === 'internship_completed' ? 'selected' : '' }}>Completion of Internship</option>
                        <option value="deceased" {{ strtolower($exitType) === 'deceased' ? 'selected' : '' }}>Death</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="eo-label">Asset Status</label>
                    <select name="asset_status" class="eo-control">
                        <option value="pending" {{ strtolower($assetStatus) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cleared" {{ strtolower($assetStatus) === 'cleared' ? 'selected' : '' }}>Cleared</option>
                        <option value="waived" {{ strtolower($assetStatus) === 'waived' ? 'selected' : '' }}>Waived</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="eo-label">FnF Status</label>
                    <select name="fnf_status" class="eo-control">
                        <option value="pending" {{ strtolower($fnfStatus) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ strtolower($fnfStatus) === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="approved" {{ strtolower($fnfStatus) === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ strtolower($fnfStatus) === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="completed" {{ strtolower($fnfStatus) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="waived" {{ strtolower($fnfStatus) === 'waived' ? 'selected' : '' }}>Waived</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="eo-label">Document Status</label>
                    <select name="document_status" class="eo-control">
                        <option value="pending" {{ strtolower($documentStatus) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="generated" {{ strtolower($documentStatus) === 'generated' ? 'selected' : '' }}>Generated</option>
                        <option value="sent" {{ strtolower($documentStatus) === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="completed" {{ strtolower($documentStatus) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="waived" {{ strtolower($documentStatus) === 'waived' ? 'selected' : '' }}>Waived</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="eo-label">Handover Status</label>
                    <select name="handover_status" class="eo-control">
                        <option value="pending" {{ strtolower($handoverStatus) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cleared" {{ strtolower($handoverStatus) === 'cleared' ? 'selected' : '' }}>Cleared</option>
                        <option value="completed" {{ strtolower($handoverStatus) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="waived" {{ strtolower($handoverStatus) === 'waived' ? 'selected' : '' }}>Waived</option>
                    </select>
                </div>
                <div class="col-md-12 mb-2">
                    <label class="eo-label">Remarks</label>
                    <input type="text" name="remarks" class="eo-control" value="{{ $employee->exit_remarks ?? '' }}" placeholder="Optional clearance remarks">
                </div>
            </div>
        </div>
    </form>
</div>

<div class="eo-action-card mb-3">
    <div class="eo-action-card-head">
        <div class="eo-action-icon" style="background: #DCFCE7; color: #15803D;"><i class="fas fa-check-double"></i></div>
        <div>
            <div class="eo-action-title">Complete Exit Process (Final Settlement)</div>
            <div class="eo-action-sub">Finalize full-and-final, lock user account, and mark inactive.</div>
        </div>
    </div>
    <form action="{{ route('hrms.employees.exit.complete', $employee->id) }}" method="POST" class="mb-0">
        @csrf
        <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
        <div class="eo-action-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <span class="text-muted small">This action is permanent and will disable login credentials.</span>
            </div>
            @php
            $clearanceApproved = true;
            $mandatoryDepts = ['hr', 'manager', 'it', 'admin', 'finance', 'asset'];
            foreach ($mandatoryDepts as $mDept) {
                $status = isset($employee->clearances[$mDept]) ? $employee->clearances[$mDept]->status : 'pending';
                if ($status !== 'approved') {
                    $clearanceApproved = false;
                    break;
                }
            }
            @endphp
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 eo-action-flex-row">
                <label class="mb-0 small" style="cursor:pointer;">
                    <input type="checkbox" name="waive_incomplete" value="1" class="mr-1"> Waive incomplete items (force complete)
                </label>
                <div class="js-complete-exit-btn-container d-inline-block text-right eo-complete-btn-wrap">
                    @if($clearanceApproved)
                    <button type="submit" class="btn btn-success em-btn-success px-3 py-2 eo-action-submit-btn" style="height:36px; min-height:36px; font-size:12px; border-radius:50px; font-weight:800; border:none;" onclick="return confirm('Complete exit and disable login?')">
                        <i class="fas fa-user-check mr-1"></i> Complete Exit
                    </button>
                    @else
                    <button type="button" class="btn btn-success em-btn-success px-3 py-2 eo-action-submit-btn" style="height:36px; min-height:36px; font-size:12px; border-radius:50px; font-weight:800; border:none; opacity: 0.5; cursor: not-allowed;" disabled title="Clearances are pending approval">
                        <i class="fas fa-ban mr-1"></i> Complete Exit (Blocked)
                    </button>
                    <span class="text-danger small mt-1 d-block w-100 text-left text-sm-right"><i class="fas fa-exclamation-triangle mr-1"></i> All mandatory clearances (HR, Manager, IT, Admin, Finance, Assets) must be approved.</span>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

<div class="eo-action-card">
    <div class="eo-action-card-head">
        <div class="eo-action-icon" style="background: #FEE2E2; color: #B91C1C;"><i class="fas fa-times-circle"></i></div>
        <div>
            <div class="eo-action-title">Cancel Exit Process</div>
            <div class="eo-action-sub">Abort the exit sequence and restore active employment status.</div>
        </div>
    </div>
    <form action="{{ route('hrms.employees.exit.cancel', $employee->id) }}" method="POST" class="mb-0">
        @csrf
        <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
        <div class="eo-action-body d-flex justify-content-between align-items-center flex-wrap gap-2 eo-action-flex-row">
            <span class="text-muted small">Restores the employee's active status and deletes exit record.</span>
            <button type="submit" class="btn btn-danger px-3 py-2 eo-action-submit-btn" style="height:36px; min-height:36px; font-size:12px; border-radius:50px; font-weight:800; border:none; background:#DC2626;" onclick="return confirm('Cancel this exit process?')">
                <i class="fas fa-ban mr-1"></i> Cancel Exit
            </button>
        </div>
    </form>
</div>

<div class="modal-footer px-0 pb-0" style="background: transparent; border-top: none; margin-top: 15px;">
    <button type="button" class="btn btn-secondary btn-soft w-100 w-sm-auto" data-dismiss="modal">Close</button>
</div>
