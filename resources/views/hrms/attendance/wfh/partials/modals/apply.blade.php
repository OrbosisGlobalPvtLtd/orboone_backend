<div class="modal fade" id="applyWfhModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('hrms.attendance.my-wfh.apply') }}" class="modal-content orb-modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-paper-plane mr-2"></i> Request Work From Home</h5>
                    <small class="text-white-50 d-block">Submit a WFH request range for manager and HR approval.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="orb-form-label">From Date <span class="text-danger">*</span></label>
                        <x-form.date-picker name="from_date" id="wfh_from_date" :value="date('Y-m-d')" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="orb-form-label">To Date <span class="text-danger">*</span></label>
                        <x-form.date-picker name="to_date" id="wfh_to_date" :value="date('Y-m-d')" required />
                    </div>
                </div>

                <div id="wfh_calc_box" class="p-3 mb-3 d-none" style="background:#F8F9FA; border-radius:12px; border:1px solid #E9ECEF;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-primary font-weight-bold" id="wfh_calc_period" style="font-size:13px;">Requested Period</strong>
                        <span class="badge badge-primary" id="wfh_calc_total">0 Days</span>
                    </div>
                    <div class="row text-center small">
                        <div class="col-4">
                            <div class="text-muted">Working Days</div>
                            <strong class="text-success" style="font-size:16px;" id="wfh_calc_working">0</strong>
                        </div>
                        <div class="col-4">
                            <div class="text-muted">Weekly Off</div>
                            <strong class="text-warning" style="font-size:16px;" id="wfh_calc_weekoff">0</strong>
                        </div>
                        <div class="col-4">
                            <div class="text-muted">Holidays</div>
                            <strong class="text-info" style="font-size:16px;" id="wfh_calc_holiday">0</strong>
                        </div>
                    </div>
                    <div class="mt-2 text-center text-dark font-weight-bold pt-2 border-top" style="font-size:12px;">
                        Actual WFH Days: <span class="text-success font-weight-bold" id="wfh_calc_actual">0</span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="orb-form-label">Reason Category <span class="text-danger">*</span></label>
                    <select name="reason_category" class="form-control select2-searchable" required style="border-radius: 10px;">
                        <option value="" disabled selected>-- Select WFH Reason Category --</option>
                        <option value="personal_reason">Personal / Family Work</option>
                        <option value="health_medical">Health & Medical Care</option>
                        <option value="commute_disruption">Commute & Transport Disruption</option>
                        <option value="home_maintenance">Home Maintenance & Delivery</option>
                        <option value="severe_weather">Severe Weather / Local Disruption</option>
                        <option value="focused_work">Deep Focused Project Work</option>
                        <option value="other">Other Valid Reason</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="orb-form-label">Reason Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="reason" rows="3" required placeholder="Describe your reason for requesting WFH..." style="border-radius: 10px;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="orb-btn orb-btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="orb-btn orb-btn-gradient"><i class="fas fa-check mr-1"></i> Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        $('#applyWfhModal form').on('submit', function(e) {
            var $cat = $(this).find('select[name="reason_category"]');
            if ($cat.length && !$cat.val()) {
                e.preventDefault();
                var $container = $cat.next('.select2-container');
                $container.find('.select2-selection').css('border-color', '#EF4444');
                $cat.select2('open');
                return false;
            }
        });

        $('#applyWfhModal select[name="reason_category"]').on('change.select2 change', function() {
            if ($(this).val()) {
                $(this).next('.select2-container').find('.select2-selection').css('border-color', '');
            }
        });
    })();
</script>
