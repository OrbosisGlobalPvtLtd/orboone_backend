<!-- Slim Collapsible Auto Module Verification Summary Cards -->
@if(!empty($employee->module_summary))
<div class="card border-0 shadow-sm mb-3" style="border-radius:12px; background: #fff; border: 1px solid #E7EAF3;">
    <div class="card-header bg-white border-0 py-2 px-3 d-flex justify-content-between align-items-center" 
         style="cursor:pointer;" 
         data-toggle="collapse" 
         data-target="#modSummaryCollapse-{{ $employee->id }}" 
         aria-expanded="false">
        <div class="d-flex align-items-center">
            <i class="fas fa-search-dollar text-primary mr-2"></i>
            <span class="font-weight-bold text-dark" style="font-size:13px;">Auto Module Verification Summary</span>
            <span class="text-muted small ml-2 d-none d-md-inline">(Synced live from HRMS modules)</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge badge-light border text-muted mr-2" style="font-size:11px;">9 Checks</span>
            <i class="fas fa-chevron-down text-muted fa-sm"></i>
        </div>
    </div>
    <div class="collapse" id="modSummaryCollapse-{{ $employee->id }}">
        <div class="card-body p-2 px-3 bg-light">
            <div class="row">
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">Attendance Issues</div>
                        <div class="font-weight-bold {{ $employee->module_summary['attendance_pending'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size:13px;">{{ $employee->module_summary['attendance_pending'] }} Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">Leave Balance</div>
                        <div class="font-weight-bold text-primary" style="font-size:13px;">{{ $employee->module_summary['leave_remaining'] }} Days Left</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">Assigned Assets</div>
                        <div class="font-weight-bold {{ $employee->module_summary['assets_assigned'] > 0 ? 'text-warning' : 'text-success' }}" style="font-size:13px;">{{ $employee->module_summary['assets_assigned'] }} Assigned</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">Pending Payroll</div>
                        <div class="font-weight-bold {{ $employee->module_summary['payroll_pending'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size:13px;">{{ $employee->module_summary['payroll_pending'] }} Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">Exit Documents</div>
                        <div class="font-weight-bold text-success" style="font-size:13px;">{{ $employee->module_summary['documents_count'] }} Generated</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">Loans/Adjustments</div>
                        <div class="font-weight-bold {{ $employee->module_summary['loans_pending'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size:13px;">{{ $employee->module_summary['loans_pending'] }} Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">WFH Requests</div>
                        <div class="font-weight-bold text-dark" style="font-size:13px;">{{ $employee->module_summary['wfh_pending'] }} Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">Comp-Off</div>
                        <div class="font-weight-bold text-dark" style="font-size:13px;">{{ $employee->module_summary['holiday_work_pending'] }} Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="text-muted small font-weight-bold">Notice Remaining</div>
                        <div class="font-weight-bold text-dark" style="font-size:13px;">{{ $employee->module_summary['notice_days_remaining'] }} Days</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
