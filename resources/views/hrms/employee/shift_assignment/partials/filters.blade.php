<!-- Auto-Submitting Filter Grid Bar -->
<div class="report-filters-attached">
    <form id="shiftFilterForm" method="GET" action="{{ route('employee.shift-assignment.index') }}">
        <div class="report-filter-grid">

            <div>
                <label>Employee</label>
                <select name="employee_id" class="form-control select2-searchable">
                    <option value="">All Staff</option>
                    @foreach($allEmployeesList as $empOption)
                        <option value="{{ $empOption->id }}" {{ request('employee_id') == $empOption->id ? 'selected' : '' }}>
                            {{ optional($empOption->user)->name ?? 'Employee #' . $empOption->id }} ({{ $empOption->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Department</label>
                <select name="department_id" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Search Keyword</label>
                <input type="text" name="search" class="form-control" placeholder="Search code or name..." value="{{ request('search') }}">
            </div>

            <div>
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block rounded-12 font-weight-bold" style="height: 44px; background: var(--orb-primary); border: none;">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
            </div>

        </div>
    </form>
</div>
