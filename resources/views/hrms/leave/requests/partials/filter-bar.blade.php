<!-- Filters Header Bar -->
<div class="leave-filter-container" style="border-bottom: 1px solid #E2E8F0; background: #F8FAFC; padding: 18px 24px;">
    <form method="GET" action="{{ route('leave-requests.index') }}" id="leaveFilterForm">
        <input type="hidden" name="per_page" value="{{ request('per_page', 25) }}">
        <div class="leave-filter-grid {{ $isAdminOrHr && $employees->isNotEmpty() ? 'has-employee-filter' : '' }}">
            @if($isAdminOrHr && $employees->isNotEmpty())
            <div>
                <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Employee</label>
                <select name="employee_id" id="filterEmployee" class="filter-control select2-searchable" data-placeholder="All Employees">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ (string)request('employee_id') === (string)$emp->id ? 'selected' : '' }}>
                            {{ $emp->display_name }} ({{ $emp->employee_code ?? 'EMP' }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Leave Type</label>
                <select name="leave_type_id" id="filterLeaveType" class="filter-control select2-searchable" data-placeholder="All Types">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ (string)request('leave_type_id') === (string)$lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Status</label>
                <select name="status" id="filterStatus" class="filter-control select2-searchable" data-placeholder="All Statuses">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <div>
                <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Month</label>
                <select name="month" id="filterMonth" class="filter-control select2-searchable" data-placeholder="Select Month">
                    <option value="all" {{ ($selectedMonth ?? '') === 'all' ? 'selected' : '' }}>All Months</option>
                    @foreach([
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ] as $mNum => $mName)
                        <option value="{{ $mNum }}" {{ (string)($selectedMonth ?? '') === (string)$mNum ? 'selected' : '' }}>
                            {{ $mName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Year</label>
                <select name="year" id="filterYear" class="filter-control select2-searchable" data-placeholder="Select Year">
                    @php $selYear = (int) ($year ?? request('year', date('Y'))); @endphp
                    @for($y = 2024; $y <= 2030; $y++)
                        <option value="{{ $y }}" {{ $selYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Search Keyword</label>
                <input type="text" name="search" id="filterSearch" value="{{ request('search') }}" class="filter-control" placeholder="Search reason, code, employee...">
            </div>

            <div class="leave-filter-actions d-flex align-items-end" style="gap: 8px;">
                <button type="submit" class="filter-btn-search">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="{{ route('leave-requests.index') }}" class="filter-btn-reset" title="Reset Filters">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </div>
    </form>
</div>
