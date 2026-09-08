<div id="contract_box" class="eo-smart-panel">
    <div class="eo-panel-title">
        <i class="fas fa-file-contract"></i> Contract / Freelance Setup
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Contract End / Review Date</label>
            <input type="date" name="contract_end_date" id="contract_end_date"
                class="form-control" value="{{ old('contract_end_date', $employeeData->relieving_date ?? '') }}">
            <div class="small-note">This will be stored as the contract relieving date.</div>
        </div>
    </div>
</div>
