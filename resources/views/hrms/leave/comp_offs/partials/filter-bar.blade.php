<div class="p-3 border-bottom bg-white">
    <form method="GET" action="{{ route('hrms.comp_offs.index') }}" class="d-flex flex-column" style="gap: 12px;">
        <div class="filter-form-grid">
                    @if(!empty($canViewOthers) && isset($employees) && $employees->count() > 1)
                        <div class="filter-item-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-user text-primary"></i> Employee
                            </label>
                            <select id="filter_employee_select" name="employee_id" class="filter-control-sm select2-searchable">
                                <option value="">-- All Employees --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ (request('employee_id') == $emp->id || ($targetEmployee && $targetEmployee->id == $emp->id && request()->filled('employee_id'))) ? 'selected' : '' }}>
                                        {{ $emp->user_name ?? $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="filter-item-wrap">
                        <label class="filter-item-lbl">
                            <i class="fas fa-tasks text-primary"></i> Status
                        </label>
                        <select name="status" class="filter-control-sm select2-searchable">
                            <option value="">All Statuses</option>
                            <option value="earned" {{ request('status') === 'earned' ? 'selected' : '' }}>Earned / Available</option>
                            <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Used</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="filter-item-wrap">
                        <label class="filter-item-lbl">
                            <i class="fas fa-calendar-alt text-primary"></i> Month
                        </label>
                        <select name="month" class="filter-control-sm select2-searchable">
                            <option value="all" {{ ($selectedMonth ?? '') === 'all' ? 'selected' : '' }}>All Months</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (string) ($selectedMonth ?? '') === (string) $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-item-wrap">
                        <label class="filter-item-lbl">
                            <i class="fas fa-calendar-alt text-primary"></i> Year
                        </label>
                        <input name="year" type="number" class="filter-control-sm" value="{{ $year }}" placeholder="Year">
                    </div>

                    <div class="d-flex align-items-center filter-actions-box" style="gap: 8px;">
                        <button type="submit" class="btn btn-sm btn-orb-search">
                            <i class="fas fa-search" style="font-size: 11px;"></i> Search
                        </button>
                        @if(request()->filled('employee_id') || request()->filled('status') || request()->filled('month') || request()->filled('year'))
                            <a href="{{ route('hrms.comp_offs.index') }}" class="btn btn-sm btn-orb-reset" title="Reset filters">
                                <i class="fas fa-undo" style="font-size: 11px;"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

