<!-- If exit is NOT yet initiated, show the initiation form -->
@if (Route::has('hrms.employees.exit.mark'))
<form action="{{ route('hrms.employees.exit.mark', $employee->id) }}" method="POST" class="mb-0 eo-exit-init-form">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="eo-label">Exit Type <span class="required">*</span></label>
            <select name="exit_type" class="eo-control eo-exit-type" required>
                <option value="" disabled selected>Select Exit Type</option>
                <option value="resignation">Resignation</option>
                <option value="termination">Termination</option>
                <option value="discontinued">Discontinuation</option>
                <option value="absconding">Absconding</option>
                <option value="retirement">Retirement</option>
                <option value="contract_end">End of Contract</option>
                <option value="internship_completed">Completion of Internship</option>
                <option value="deceased">Death</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="eo-label">Resignation Date</label>
            <input type="date" name="resignation_date" class="eo-control eo-resignation-date" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="eo-label">Termination/Absconding Date</label>
            <input type="date" name="termination_date" class="eo-control eo-termination-date" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="eo-label">Last Working Day (Optional)</label>
            <input type="date" name="last_working_day" class="eo-control eo-last-working-day" value="{{ $employee->relieving_date ?? '' }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="eo-label">Notice Period (Days)</label>
            <input type="number" name="notice_period_days" class="eo-control eo-notice-days" min="0" value="{{ $defaultNoticeDays }}" placeholder="Auto from policy">
        </div>
        <div class="col-md-6 mb-3">
            <label class="eo-label">Reason</label>
            <input type="text" name="reason" class="eo-control" placeholder="Reason for exit...">
        </div>
        <div class="col-md-6 mb-3">
            <label class="eo-label">Remarks</label>
            <input type="text" name="remarks" class="eo-control" placeholder="Additional remarks...">
        </div>
        <div class="col-md-12 mb-2">
            <label class="mr-3"><input type="checkbox" name="notice_waived" value="1" class="eo-notice-waived"> Notice Waived</label>
            <label class="mr-3"><input type="checkbox" name="immediate_exit" value="1" class="eo-immediate-exit"> Immediate Exit</label>
            <label class="mr-3"><input type="checkbox" name="buyout_recovery" value="1"> Buyout/Recovery Applicable</label>
            <label><input type="checkbox" name="immediate_disable_login" value="1"> Disable Login Immediately</label>
        </div>
    </div>

    <div class="modal-footer px-0 pb-0" style="background: transparent; border-top: none; margin-top: 15px;">
        <button type="button" class="btn btn-secondary btn-soft" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-orb" onclick="return confirm('Initiate employee exit process?')">
            <i class="fas fa-play-circle mr-1"></i> Initiate Exit
        </button>
    </div>
</form>
@else
<div class="text-center py-4 text-muted">
    <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i>
    <p class="mb-0">Initiation route is not accessible at this moment.</p>
</div>
@endif
