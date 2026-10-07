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
            @if($isAdminOrManager)
            <div>
                <label>Employee</label>
                <select name="employee_id" id="filterEmployee" class="form-control select2-searchable">
                    <option value="">All Staff Members</option>
                    @foreach($employees as $emp)
                    <option value="{{ optional($emp->employee)->id }}" {{ (string) request('employee_id', $filters['employee_id'] ?? '') === (string) optional($emp->employee)->id ? 'selected' : '' }}>
                        {{ $emp->name }} ({{ optional($emp->employee)->employee_code ?? 'EMP' }})
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            <div>
                <label>Employee</label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly disabled>
            </div>
            @endif

            <div>
                <label>Work Mode</label>
                <select name="work_mode" id="filterWorkMode" class="form-control select2-searchable">
                    <option value="">All Modes</option>
                    <option value="wfo" {{ strtolower(request('work_mode', $filters['work_mode'] ?? '')) === 'wfo' ? 'selected' : '' }}>Office (WFO)</option>
                    <option value="wfh" {{ strtolower(request('work_mode', $filters['work_mode'] ?? '')) === 'wfh' ? 'selected' : '' }}>Remote (WFH)</option>
                </select>
            </div>

            <div>
                <label><i class="fas fa-calendar-day text-primary mr-1"></i> Date</label>
                <x-form.date-picker name="date" id="filterDate" :value="request('date', $filters['date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>

            <div>
                <label>Month</label>
                <select name="month" id="monthFilterSelect" class="form-control select2-searchable">
                    @php
                        $currM = $filters['current_month'] ?? date('Y-m');
                        $selM = $filters['selected_month'] ?? $currM;
                        if (request()->filled('date')) {
                            try {
                                $selM = \Carbon\Carbon::parse(request('date'))->format('Y-m');
                            } catch (\Throwable $e) {}
                        }
                        $isCustom = ($selM === 'custom' || (!request()->has('month') && !request()->filled('date') && (request()->filled('from_date') || request()->filled('to_date'))));
                    @endphp
                    <option value="custom" {{ $isCustom ? 'selected' : '' }}>Custom Date Range</option>
                    @foreach($filters['months'] ?? [] as $val => $lbl)
                        <option value="{{ $val }}" {{ ($selM === $val && !$isCustom) ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div id="customFromWrap" style="display: {{ $isCustom ? 'block' : 'none' }};">
                <label>Date From</label>
                <x-form.date-picker name="from_date" id="filterFromDate" :value="request('from_date', $filters['from_date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>

            <div id="customToWrap" style="display: {{ $isCustom ? 'block' : 'none' }};">
                <label>Date To</label>
                <x-form.date-picker name="to_date" id="filterToDate" :value="request('to_date', $filters['to_date'] ?? '')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>

            <div>
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
