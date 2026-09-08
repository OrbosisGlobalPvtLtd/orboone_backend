<div id="intern_box" class="eo-smart-panel">
    <div class="eo-panel-title">
        <i class="fas fa-user-graduate"></i> Internship Setup
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Internship Start Date <span class="required">*</span></label>
            <input type="date" name="internship_start_date" id="internship_start_date"
                class="form-control" value="{{ old('internship_start_date', $employeeData->internship_start_date ?? '') }}">
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Internship Duration <span class="required">*</span></label>
            @php
                $internDur = old('internship_duration_months', isset($employeeData->internship_start_date, $employeeData->internship_end_date) ? 'custom' : 3);
            @endphp
            <select name="internship_duration_months" id="internship_duration_months"
                class="form-select">
                <option value="">Select Duration</option>
                <option value="3" {{ (string)$internDur === '3' ? 'selected' : '' }}>3 Months</option>
                <option value="6" {{ (string)$internDur === '6' ? 'selected' : '' }}>6 Months</option>
                <option value="custom" {{ (string)$internDur === 'custom' ? 'selected' : '' }}>Custom End Date</option>
            </select>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Internship End Date <span class="required">*</span></label>
            <input type="date" name="internship_end_date" id="internship_end_date"
                class="form-control" value="{{ old('internship_end_date', $employeeData->internship_end_date ?? '') }}">
            <div class="small-note">Auto calculated for 3/6 months. Select manually if Custom is chosen.</div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Paid / Unpaid <span class="required">*</span></label>
            @php $paidVal = old('is_paid_intern', isset($employeeData->is_paid_intern) ? (string)$employeeData->is_paid_intern : ''); @endphp
            <select name="is_paid_intern" id="is_paid_intern" class="form-select">
                <option value="">Select</option>
                <option value="1" {{ (string)$paidVal === '1' ? 'selected' : '' }}>Paid / Stipend</option>
                <option value="0" {{ (string)$paidVal === '0' ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Duration Summary</label>
            <input type="text" id="internship_duration_display"
                class="form-control readonly-field" readonly>
        </div>
    </div>
</div>
