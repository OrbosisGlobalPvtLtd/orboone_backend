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
    <div class="orb-table-head-right">
        @if($isEmployee ?? false)
            <button type="button" class="orb-btn orb-btn-gradient" data-toggle="modal" data-target="#applyWfhModal">
                <i class="fas fa-plus-circle"></i> Request WFH
            </button>
        @elseif($canAssign ?? false)
            <button type="button" class="orb-btn orb-btn-gradient" data-toggle="modal" data-target="#assignWfhModal">
                <i class="fas fa-plus-circle"></i> Assign Company WFH
            </button>
        @endif
    </div>
</div>

<div class="orb-filter">
    <form method="GET" action="{{ route('hrms.attendance.wfh.index') }}" class="orb-filter-form" id="wfhFilterForm">
        <input type="hidden" name="per_page" value="{{ request('per_page', 25) }}">
        @php
            $currentMonthKey = \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m');
            $reqMonth = request('month');
            $hasCustomDates = request()->filled('from') || request()->filled('from_date') || request()->filled('to') || request()->filled('to_date');

            if ($hasCustomDates || $reqMonth === 'custom') {
                $activeMonth = 'custom';
            } elseif ($reqMonth !== null) {
                $activeMonth = $reqMonth;
            } else {
                $activeMonth = $currentMonthKey;
            }

            $empOptions = [
                'all' => 'All Employees',
            ];
            foreach($employees as $emp) {
                $name = trim((string) ($emp->display_name ?? $emp->user_name ?? ''));
                $code = trim((string) ($emp->employee_code ?? ''));
                if ($name !== '' && $code !== '' && !str_contains($name, $code)) {
                    $empOptions[$emp->id] = $name . ' (' . $code . ')';
                } else {
                    $empOptions[$emp->id] = $name ?: $code ?: ('EMP #' . $emp->id);
                }
            }

            $statusOptions = [
                'pending' => 'Pending',
                'manager_approved' => 'Pending HR',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'cancelled' => 'Cancelled'
            ];

            $typeOptions = [
                'working_day_wfh' => 'Working Day WFH',
                'holiday_wfh' => 'Holiday WFH',
                'weekoff_wfh' => 'Weekoff WFH',
                'company_assigned_wfh' => 'Company Assigned WFH'
            ];

            $reasonOptions = [
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
                :selected="request('employee_id', ($isEmployee ?? false) ? $userEmpId : 'all')"
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
                :searchable="true"
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
                :searchable="true"
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
                :searchable="true"
                wrapper-class="mb-0"
            />
        </div>

        <div class="orb-filter-item">
            <x-form.select 
                name="month"
                id="wfh_filter_month"
                label="Month"
                :options="$monthOptions ?? []"
                :selected="$activeMonth"
                :searchable="true"
                wrapper-class="mb-0"
            />
        </div>

        <div class="orb-filter-item js-custom-date-field" style="{{ $activeMonth !== 'custom' ? 'display: none;' : '' }}">
            <div class="orb-form-group">
                <label class="orb-form-label">Date From</label>
                <x-form.date-picker name="from" id="wfh_p_from" :value="request('from')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>
        </div>

        <div class="orb-filter-item js-custom-date-field" style="{{ $activeMonth !== 'custom' ? 'display: none;' : '' }}">
            <div class="orb-form-group">
                <label class="orb-form-label">Date To</label>
                <x-form.date-picker name="to" id="wfh_p_to" :value="request('to')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>
        </div>

        <div class="orb-filter-actions">
            <button type="submit" class="orb-btn orb-btn-search">
                <i class="fas fa-search"></i> Search
            </button>
            <a href="{{ route('hrms.attendance.wfh.index') }}?month={{ \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m') }}" class="orb-btn orb-btn-reset" title="Reset Filters">
                <i class="fas fa-undo"></i> Reset
            </a>
        </div>
    </form>
</div>
