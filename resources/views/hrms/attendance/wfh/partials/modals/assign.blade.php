@if($canAssign ?? false)
<div class="modal fade" id="assignWfhModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('hrms.attendance.wfh.assign') }}" class="modal-content orb-modal-content border-0">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Assign Company WFH</h5>
                    <small class="text-white-50 d-block">Assign approved WFH to employee, department, or all active employees.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="orb-form-label">Assignment Scope</label>
                        <select class="form-control js-assign-scope" name="assignment_scope" required>
                            <option value="single">Single Employee</option>
                            <option value="multiple">Multiple Employees</option>
                            <option value="department">Department</option>
                            <option value="designation">Designation</option>
                            <option value="all">All Employees</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 js-scope-single">
                        <label class="orb-form-label">Employee</label>
                        <select name="employee_id" class="form-control select2-modal-searchable">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-none js-scope-multiple">
                        <label class="orb-form-label">Employees</label>
                        <select name="employee_ids[]" class="form-control select2-modal-searchable" multiple>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-none js-scope-department">
                        <label class="orb-form-label">Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">Select Department</option>
                            @foreach(($departments ?? []) as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-none js-scope-designation">
                        <label class="orb-form-label">Designation</label>
                        <select name="designation_id" class="form-control">
                            <option value="">Select Designation</option>
                            @foreach(($designations ?? []) as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="orb-form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="orb-form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="orb-form-label">Reason Category</label>
                        <select name="reason_category" class="form-control">
                            <option value="company_assigned">Company Assigned</option>
                            <option value="manager_assigned">Manager Assigned</option>
                            <option value="normal">Normal</option>
                            <option value="personal_reason">Personal Reason</option>
                            <option value="internet_issue">Internet Issue</option>
                            <option value="electricity_issue">Electricity Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="orb-form-label">Payroll Impact</label>
                        <select name="payroll_impact" class="form-control">
                            <option value="none">None (Full Paid)</option>
                            <option value="lwp">LWP (Loss of Pay)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="orb-form-label">Counts In Monthly Quota</label>
                        <select name="counts_in_monthly_quota" class="form-control">
                            <option value="0" selected>No (Default)</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="orb-form-label">Reason</label>
                        <textarea class="form-control" name="reason" rows="2" required placeholder="Why is company assigning WFH?"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">Cancel</button>
                <button class="orb-btn orb-btn-gradient"><i class="fas fa-check"></i> Assign WFH</button>
            </div>
        </form>
    </div>
</div>
@endif
