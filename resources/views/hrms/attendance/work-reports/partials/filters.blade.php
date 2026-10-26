<div class="att-section-head">
    <div>
        <h5 class="att-section-title"><i class="fas fa-list-check"></i> Daily Work Report Listing</h5>
        <div class="text-muted small mt-1">Review detailed daily task breakdown, gross duration, and structured achievements.</div>
    </div>
</div>

<!-- Server-side Multi Filters Panel (Positioned Below Table Header) -->
<div class="att-filter-panel">
    <form method="GET" action="{{ url()->current() }}" id="reportFilterForm">
        <input type="hidden" name="per_page" value="{{ request('per_page', $filters['per_page'] ?? 25) }}">
        <div class="att-filter-grid">
            @php
                $currY = $filters['current_year'] ?? date('Y');
                $selY = $filters['selected_year'] ?? request('year', $currY);
                $currM = $filters['current_month'] ?? date('m');
                $selM = $filters['selected_month'] ?? request('month', $currM);
                if (request()->filled('date')) {
                    try {
                        $pDate = \Carbon\Carbon::parse(request('date'));
                        $selY = $pDate->format('Y');
                        $selM = $pDate->format('m');
                    } catch (\Throwable $e) {}
                }
                $isCustom = ($selM === 'custom' || (!request()->has('month') && !request()->filled('date') && (request()->filled('from_date') || request()->filled('to_date'))));
            @endphp

            @if($isAdminOrManager)
            <div class="att-filter-col att-filter-employee">
                <label>Employee</label>
                <select name="employee_id" id="filterEmployee" class="form-control select2-searchable" data-placeholder="All Staff Members">
                    <option value="">All Staff Members</option>
                    @foreach($employees as $emp)
                    <option value="{{ optional($emp->employee)->id }}" {{ (string) request('employee_id', $filters['employee_id'] ?? '') === (string) optional($emp->employee)->id ? 'selected' : '' }}>
                        {{ $emp->name }} ({{ optional($emp->employee)->employee_code ?? 'EMP' }})
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="att-filter-col att-filter-employee">
                <label>Employee</label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly disabled>
            </div>
            @endif

            <div class="att-filter-col att-filter-workmode">
                <label>Work Mode</label>
                <select name="work_mode" id="filterWorkMode" class="form-control select2-searchable" data-placeholder="All Modes">
                    <option value="">All Modes</option>
                    <option value="wfo" {{ strtolower(request('work_mode', $filters['work_mode'] ?? '')) === 'wfo' ? 'selected' : '' }}>Office (WFO)</option>
                    <option value="wfh" {{ strtolower(request('work_mode', $filters['work_mode'] ?? '')) === 'wfh' ? 'selected' : '' }}>Remote (WFH)</option>
                </select>
            </div>

            <div class="att-filter-col att-filter-year" id="yearFilterWrap">
                <label><i class="fas fa-calendar text-primary mr-1"></i> Year</label>
                <select name="year" id="yearFilterSelect" class="form-control select2-searchable" data-allow-clear="false">
                    @foreach($filters['years'] ?? get_filter_years(1, 4) as $yVal => $yLbl)
                        <option value="{{ $yVal }}" {{ (string) $selY === (string) $yVal ? 'selected' : '' }}>{{ $yLbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="att-filter-col att-filter-month" id="monthFilterWrap">
                <label><i class="fas fa-calendar-alt text-primary mr-1"></i> Month</label>
                <select name="month" id="monthFilterSelect" class="form-control select2-searchable" data-allow-clear="false">
                    <option value="custom" {{ $isCustom ? 'selected' : '' }}>Custom Date Range</option>
                    @foreach($filters['months'] ?? get_filter_months(true) as $val => $lbl)
                        @if($val !== 'custom')
                            @php
                                $isSel = !$isCustom && ((string) $selM === (string) $val || (is_numeric($selM) && is_numeric($val) && (int) $selM === (int) $val));
                            @endphp
                            <option value="{{ $val }}" {{ $isSel ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
<div class="att-filter-col att-filter-date" id="singleDateWrap" style="display: {{ $isCustom ? 'none' : 'block' }};">
                <label><i class="fas fa-calendar-day text-primary mr-1"></i> Date</label>
                <x-form.date-picker name="date" id="filterDate" :value="request('date', $filters['date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>
            <div class="att-filter-col att-filter-from" id="customFromWrap" style="display: {{ $isCustom ? 'block' : 'none' }};">
                <label>Date From</label>
                <x-form.date-picker name="from_date" id="filterFromDate" :value="request('from_date', $filters['from_date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>

            <div class="att-filter-col att-filter-to" id="customToWrap" style="display: {{ $isCustom ? 'block' : 'none' }};">
                <label>Date To</label>
                <x-form.date-picker name="to_date" id="filterToDate" :value="request('to_date', $filters['to_date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>

            <div class="att-filter-col att-filter-actions-col">
                <label class="d-none d-md-block">&nbsp;</label>
                <div class="att-filter-actions">
                    <button type="submit" id="btnFilterSubmit" class="att-search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ url()->current() }}" class="att-reset-btn" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
