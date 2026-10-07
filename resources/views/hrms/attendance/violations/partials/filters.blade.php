<div class="att-section-head">
    <div>
        <h5 class="att-section-title"><i class="fas fa-list-alt"></i> Violation & Penalty Audit Logs</h5>
        <div class="text-muted small mt-1">Server side audited violation records with active cycle counts and penalty statuses.</div>
    </div>
</div>

<!-- Server-side Multi Filters -->
<div class="att-filter-panel">
    <form method="GET" id="filterForm">
        <input type="hidden" name="per_page" value="{{ request('per_page', 25) }}">
        <div class="att-filter-grid">
            @if(!empty($filters['employee_options']) && ($canViewAll || $canViewTeam))
            <div>
                <label>Employee</label>
                <select name="employee_id" class="form-control select2-searchable">
                    <option value="">All Employees</option>
                    @foreach($filters['employee_options'] ?? [] as $id => $name)
                    <option value="{{ $id }}" {{ (string) request('employee_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label>Violation Type</label>
                <select name="type" class="form-control select2-searchable">
                    <option value="">All Types</option>
                    @foreach($filters['types'] ?? [] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Penalty Status</label>
                <select name="penalty_status" class="form-control select2-searchable">
                    <option value="">All Statuses</option>
                    @foreach($filters['penalty_statuses'] ?? [] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('penalty_status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Month</label>
                <select name="month" id="monthFilterSelect" class="form-control select2-searchable">
                    @php
                        $currM = $filters['current_month'] ?? date('Y-m');
                        $selM = $filters['selected_month'] ?? $currM;
                        $isCustom = ($selM === 'custom' || (!request()->has('month') && (request()->filled('from') || request()->filled('to'))));
                    @endphp
                    <option value="custom" {{ $isCustom ? 'selected' : '' }}>Custom Date Range</option>
                    @foreach($filters['months'] ?? [] as $val => $lbl)
                        <option value="{{ $val }}" {{ ($selM === $val && !$isCustom) ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div id="customFromWrap" style="display: {{ $isCustom ? 'block' : 'none' }};">
                <label>Date From</label>
                <x-form.date-picker name="from" id="fromDateInput" :value="request('from')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>

            <div id="customToWrap" style="display: {{ $isCustom ? 'block' : 'none' }};">
                <label>Date To</label>
                <x-form.date-picker name="to" id="toDateInput" :value="request('to')" placeholder="dd-mm-yyyy" class="form-control" />
            </div>

            <div class="att-filter-actions-col">
                <label class="d-none d-md-block">&nbsp;</label>
                <div class="att-filter-actions">
                    <button type="submit" class="att-search-btn">
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
