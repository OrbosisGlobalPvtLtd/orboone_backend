<div id="intern_box" class="eo-smart-panel">
    <div class="eo-panel-title">
        <i class="fas fa-user-graduate"></i> Internship Setup
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Internship Start Date <span class="required">*</span></label>
            <x-form.date-picker 
                name="internship_start_date" 
                id="internship_start_date"
                :value="old('internship_start_date', $employeeData->internship_start_date ?? '')"
            />
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Internship Duration <span class="required">*</span></label>
            @php
                $internDur = old('internship_duration_months', isset($employeeData->internship_start_date, $employeeData->internship_end_date) ? 'custom' : 3);
            @endphp
            <x-form.select 
                name="internship_duration_months" 
                id="internship_duration_months"
                placeholder="Select Duration"
                :options="[
                    '3' => '3 Months',
                    '6' => '6 Months',
                    'custom' => 'Custom End Date'
                ]"
                :selected="(string)$internDur"
                :searchable="true"
                wrapper-class="m-0"
            />
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Internship End Date <span class="required">*</span></label>
            <x-form.date-picker 
                name="internship_end_date" 
                id="internship_end_date"
                :value="old('internship_end_date', $employeeData->internship_end_date ?? '')"
            />
            <div class="small-note">Auto calculated for 3/6 months. Select manually if Custom is chosen.</div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Paid / Unpaid <span class="required">*</span></label>
            @php $paidVal = old('is_paid_intern', isset($employeeData->is_paid_intern) ? (string)$employeeData->is_paid_intern : ''); @endphp
            <x-form.select 
                name="is_paid_intern" 
                id="is_paid_intern" 
                placeholder="Select"
                :options="[
                    '1' => 'Paid / Stipend',
                    '0' => 'Unpaid'
                ]"
                :selected="(string)$paidVal"
                :searchable="true"
                wrapper-class="m-0"
            />
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
            <label>Duration Summary</label>
            <input type="text" id="internship_duration_display"
                class="form-control readonly-field" placeholder="Auto calculated from dates" readonly>
        </div>
    </div>
</div>
