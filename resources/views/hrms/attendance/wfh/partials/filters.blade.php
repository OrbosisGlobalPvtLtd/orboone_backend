<div class="orb-table-header">
    <div class="orb-table-head-left d-flex align-items-center" style="gap: 14px;">
        <div class="orb-icon-box">
            <i class="fas fa-home"></i>
        </div>
        <div>
            <h3 class="orb-table-title">WFH Requests</h3>
            <p class="orb-table-subtitle">Review request details, quota impact and payroll impact with clear approvals.</p>
        </div>
    </div>
    @if($canAssign ?? false)
    <div class="orb-table-head-right">
        <button type="button" class="orb-btn orb-btn-gradient" data-toggle="modal" data-target="#assignWfhModal">
            <i class="fas fa-plus-circle"></i> Assign Company WFH
        </button>
    </div>
    @endif
</div>

<div class="orb-filter">
    <form method="GET" action="{{ route('hrms.attendance.wfh.index') }}" class="orb-filter-form">
        @php
            $empOptions = ['' => 'All Employees'];
            foreach($employees as $emp) {
                $empOptions[$emp->id] = $emp->display_name;
            }

            $statusOptions = [
                '' => 'All Status',
                'pending' => 'Pending',
                'manager_approved' => 'Pending HR',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'cancelled' => 'Cancelled'
            ];

            $typeOptions = [
                '' => 'All Type',
                'working_day_wfh' => 'Working Day WFH',
                'holiday_wfh' => 'Holiday WFH',
                'weekoff_wfh' => 'Weekoff WFH',
                'company_assigned_wfh' => 'Company Assigned WFH'
            ];

            $reasonOptions = [
                '' => 'All Reason',
                'personal_reason' => 'Personal Reason',
                'health_medical' => 'Health / Medical',
                'commute_disruption' => 'Commute Disruption',
                'home_maintenance' => 'Home Maintenance',
                'severe_weather' => 'Severe Weather',
                'focused_work' => 'Focused Work',
                'company_assigned' => 'Company Assigned',
                'other' => 'Other'
            ];
        @endphp

        <div class="orb-filter-item">
            <x-form.select 
                name="employee_id"
                label="Employee"
                :options="$empOptions"
                :selected="request('employee_id')"
                placeholder="All Employees"
                :searchable="true"
                wrapper-class="mb-0"
            />
        </div>

        <div class="orb-filter-item">
            <x-form.select 
                name="status"
                label="Status"
                :options="$statusOptions"
                :selected="request('status')"
                placeholder="All Status"
                wrapper-class="mb-0"
            />
        </div>

        <div class="orb-filter-item">
            <x-form.select 
                name="request_type"
                label="Request Type"
                :options="$typeOptions"
                :selected="request('request_type')"
                placeholder="All Type"
                wrapper-class="mb-0"
            />
        </div>

        <div class="orb-filter-item">
            <x-form.select 
                name="reason_category"
                label="Reason Category"
                :options="$reasonOptions"
                :selected="request('reason_category')"
                placeholder="All Reason"
                wrapper-class="mb-0"
            />
        </div>

        <div class="orb-filter-item">
            <div class="orb-form-group">
                <label class="orb-form-label">Date From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
        </div>

        <div class="orb-filter-item">
            <div class="orb-form-group">
                <label class="orb-form-label">Date To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
        </div>

        <div class="orb-filter-actions">
            <button type="submit" class="orb-btn orb-btn-search">
                <i class="fas fa-search"></i> Search
            </button>
            <a href="{{ route('hrms.attendance.wfh.index') }}" class="orb-btn orb-btn-reset" title="Reset Filters">
                <i class="fas fa-undo"></i> Reset
            </a>
        </div>
    </form>
</div>
