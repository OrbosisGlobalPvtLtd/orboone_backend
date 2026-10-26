<form method="GET" action="{{ route('hrms.comp_offs.index') }}" id="compOffFilterForm" class="eo-filter-grid {{ empty($canViewOthers) || !isset($employees) || $employees->count() <= 1 ? 'no-employee' : '' }}">
    <input type="hidden" name="per_page" value="{{ request('per_page', 25) }}">

    @if(!empty($canViewOthers) && isset($employees) && $employees->count() > 1)
        <div class="eo-field">
            <label for="filter_employee_select"><i class="fas fa-user mr-1 text-primary"></i> Employee</label>
            <select id="filter_employee_select" name="employee_id" class="eo-control select2-searchable">
                <option value="">-- All Employees --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ (request('employee_id') == $emp->id || ($targetEmployee && $targetEmployee->id == $emp->id && request()->filled('employee_id'))) ? 'selected' : '' }}>
                        {{ $emp->user_name ?? $emp->display_name }} ({{ $emp->employee_code }})
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="eo-field">
        <label for="filter_status"><i class="fas fa-tasks mr-1 text-primary"></i> Status</label>
        <select id="filter_status" name="status" class="eo-control select2-searchable">
            <option value="">All Statuses</option>
            <option value="earned" {{ request('status') === 'earned' ? 'selected' : '' }}>Earned / Available</option>
            <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Used</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
        </select>
    </div>

    <div class="eo-field">
        <label for="filter_month"><i class="far fa-calendar-check mr-1 text-primary"></i> Month</label>
        <select id="filter_month" name="month" class="eo-control select2-searchable">
            <option value="all" {{ ($selectedMonth ?? '') === 'all' ? 'selected' : '' }}>All Months</option>
            @foreach(range(1, 12) as $m)
                @php
                    $monthName = date('F', mktime(0, 0, 0, $m, 1));
                @endphp
                <option value="{{ $m }}" {{ (string) ($selectedMonth ?? '') === (string) $m ? 'selected' : '' }}>
                    {{ $monthName }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="eo-field">
        <label for="filter_year"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Year</label>
        <select id="filter_year" name="year" class="eo-control select2-searchable">
            @php $selYear = (int) ($year ?? request('year', date('Y'))); @endphp
            @for($y = 2024; $y <= 2030; $y++)
                <option value="{{ $y }}" {{ $selYear === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    <div class="eo-filter-actions-col">
        <div class="eo-filter-actions-wrap">
            <button type="submit" id="btnFilterSubmit">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            @if(request()->filled('employee_id') || request()->filled('status') || request()->filled('month') || (request()->filled('year') && (int)request('year') !== (int)date('Y')))
                <a href="{{ route('hrms.comp_offs.index') }}" id="resetFilter" style="text-decoration:none;">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            @endif
        </div>
    </div>
</form>
