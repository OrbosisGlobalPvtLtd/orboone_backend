@if($canAssign ?? false)
<style>
    .orb-assign-modal .modal-dialog {
        max-width: 800px;
    }
    .orb-assign-modal .modal-body {
        padding: 18px 24px 10px;
    }
    .orb-assign-modal .orb-form-label {
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475467;
        margin-bottom: 5px;
    }
    .orb-emp-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        background: #FFFFFF;
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.18s ease;
        user-select: none;
        margin-bottom: 0;
        min-height: 48px;
    }
    .orb-emp-card:hover {
        border-color: #8B5CF6;
        background: #FAF8FF;
    }
    .orb-emp-card.is-selected {
        border-color: #7C3AED !important;
        background: #F5F0FF !important;
        box-shadow: 0 2px 6px rgba(124, 58, 237, 0.12);
    }
    .orb-emp-chk {
        width: 17px;
        height: 17px;
        accent-color: #7C3AED;
        cursor: pointer;
        margin: 0;
        flex-shrink: 0;
    }
    .orb-emp-name {
        font-size: 12.5px;
        font-weight: 700;
        color: #1E293B;
        line-height: 1.25;
    }
    .orb-emp-code {
        font-size: 11px;
        font-weight: 600;
        color: #64748B;
        margin-top: 2px;
    }
    .orb-emp-badge {
        font-size: 10px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 6px;
        background: #F1F5F9;
        color: #475467;
        letter-spacing: 0.03em;
        flex-shrink: 0;
    }
    .orb-emp-card.is-selected .orb-emp-badge {
        background: #EDE9FE;
        color: #6D28D9;
    }
    .orb-sub-btn {
        border: none;
        border-radius: 7px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
        line-height: 1.3;
        display: inline-flex;
        align-items: center;
    }
    .orb-sub-btn-primary {
        background: #EDE9FE;
        color: #6D28D9;
    }
    .orb-sub-btn-primary:hover {
        background: #DDD6FE;
        color: #5B21B6;
    }
    .orb-sub-btn-light {
        background: #F1F5F9;
        color: #475467;
    }
    .orb-sub-btn-light:hover {
        background: #E2E8F0;
        color: #1E293B;
    }
    .orb-search-wrap {
        position: relative;
    }
    .orb-search-icon {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 12px;
        pointer-events: none;
    }
    .orb-search-field {
        height: 35px !important;
        padding-left: 32px !important;
        font-size: 12.5px !important;
        border-radius: 8px !important;
        border: 1px solid #CBD5E1 !important;
        background: #FFFFFF !important;
    }
    .orb-search-field:focus {
        border-color: #7C3AED !important;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1) !important;
    }
</style>

<div class="modal fade orb-assign-modal" id="assignWfhModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('hrms.attendance.wfh.assign') }}" id="assignWfhForm" class="modal-content orb-modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Assign Company WFH</h5>
                    <small class="text-white-50 d-block">Assign approved WFH to single employee, multiple employees, or all active employees.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Row 1: Scope, Date From, Date To (All in 1 Row) -->
                    <div class="col-md-4 mb-2">
                        <label class="orb-form-label">Assignment Scope <span class="text-danger">*</span></label>
                        <select class="form-control js-assign-scope select2-modal-searchable" name="assignment_scope" data-allow-clear="false" required>
                            <option value="single" selected>Single Employee</option>
                            <option value="multiple">Multiple Employees</option>
                            <option value="all">All Employees</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="orb-form-label">Date From <span class="text-danger">*</span></label>
                        <x-form.date-picker name="date_from" id="assign_date_from" :value="date('Y-m-d')" required />
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="orb-form-label">Date To <span class="text-danger">*</span></label>
                        <x-form.date-picker name="date_to" id="assign_date_to" :value="date('Y-m-d')" required />
                    </div>

                    <!-- Row 2: Target Employee(s) -->
                    <!-- Single Employee Select -->
                    <div class="col-md-12 mb-2 js-scope-single">
                        <label class="orb-form-label">Select Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control select2-modal-searchable" data-allow-clear="false">
                            <option value="" disabled selected>-- Select Employee --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->display_name }} ({{ $emp->employee_code ?? 'EMP-' . $emp->id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- All Employees Notice -->
                    <div class="col-md-12 mb-2 d-none js-scope-all">
                        <div class="alert alert-info py-2 px-3 mb-0 d-flex align-items-center" style="border-radius: 10px; font-size: 13px; min-height: 40px;">
                            <i class="fas fa-info-circle mr-2 text-primary" style="font-size: 15px;"></i>
                            <span>WFH will be assigned to <strong>all active WFO employees</strong> ({{ count($employees) }} eligible).</span>
                        </div>
                    </div>

                    <!-- Multiple Employees Checkbox Grid -->
                    <div class="col-md-12 mb-2 d-none js-scope-multiple">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="orb-form-label mb-0">
                                Select Employees <span class="badge badge-primary font-weight-bold ml-1 js-selected-emp-count" style="font-size: 11px;">0 Selected</span>
                            </label>
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <button type="button" class="orb-sub-btn orb-sub-btn-primary js-btn-select-all">
                                    <i class="fas fa-check-double mr-1"></i> Select All
                                </button>
                                <button type="button" class="orb-sub-btn orb-sub-btn-light js-btn-deselect-all">
                                    <i class="fas fa-times mr-1"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Live Search Input -->
                        <div class="orb-search-wrap mb-2">
                            <i class="fas fa-search orb-search-icon"></i>
                            <input type="text" class="form-control orb-search-field js-emp-search-input" placeholder="Search employee name or code...">
                        </div>

                        <!-- Checkbox Container -->
                        <div class="orb-emp-checklist p-1" style="max-height: 145px; overflow-y: auto; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                            <div class="row m-0" id="assignEmpChecklist">
                                @foreach($employees as $emp)
                                <div class="col-md-6 p-1 js-emp-check-item" data-search="{{ strtolower($emp->display_name . ' ' . $emp->employee_code) }}">
                                    <label class="orb-emp-card js-emp-label-card" for="assign_emp_{{ $emp->id }}">
                                        <div class="d-flex align-items-center" style="gap: 10px; min-width: 0;">
                                            <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" id="assign_emp_{{ $emp->id }}" class="orb-emp-chk js-assign-emp-check">
                                            <div class="d-flex flex-column text-left" style="min-width: 0; line-height: 1.25;">
                                                <span class="orb-emp-name text-truncate">{{ $emp->display_name }}</span>
                                                <span class="orb-emp-code">{{ $emp->employee_code ?? 'EMP-' . $emp->id }}</span>
                                            </div>
                                        </div>
                                        <span class="orb-emp-badge">WFO</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div class="text-center py-2 text-muted d-none js-no-emp-match" style="font-size: 12px;">
                                <i class="fas fa-user-slash mr-1"></i> No matching employees found.
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Category, Payroll Impact, Quota Impact -->
                    <div class="col-md-4 mb-2">
                        <label class="orb-form-label">Reason Category</label>
                        <select name="reason_category" class="form-control select2-modal-searchable" data-allow-clear="false">
                            <option value="company_assigned" selected>Company Assigned</option>
                            <option value="manager_assigned">Manager Assigned</option>
                            <option value="normal">Normal</option>
                            <option value="personal_reason">Personal Reason</option>
                            <option value="internet_issue">Internet Issue</option>
                            <option value="electricity_issue">Electricity Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="orb-form-label">Payroll Impact</label>
                        <select name="payroll_impact" class="form-control select2-modal-searchable" data-allow-clear="false">
                            <option value="none" selected>None (Full Paid)</option>
                            <option value="lwp">LWP (Loss of Pay)</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="orb-form-label">Counts In Monthly Quota</label>
                        <select name="counts_in_monthly_quota" class="form-control select2-modal-searchable" data-allow-clear="false">
                            <option value="0" selected>No (Default)</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <!-- Row 4: Reason Description -->
                    <div class="col-md-12 mb-1">
                        <label class="orb-form-label">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="2" required placeholder="Why is company assigning WFH?" style="border-radius: 9px; font-size: 13px;"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 24px 16px;">
                <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="orb-btn orb-btn-gradient"><i class="fas fa-check"></i> Assign WFH</button>
            </div>
        </form>
    </div>
</div>
@endif
