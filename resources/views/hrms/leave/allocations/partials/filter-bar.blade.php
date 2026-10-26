<form method="GET" action="{{ route('leave-allocations.index') }}" id="allocationFilterForm" class="eo-filter-grid">
    <input type="hidden" name="tab" value="{{ $activeTab }}">

    <div class="eo-field">
        <label for="filterSearch"><i class="fas fa-search mr-1"></i> Quick Search</label>
        <input type="text" id="filterSearch" name="search" class="eo-control" placeholder="Search by employee name, code..." autocomplete="off" value="{{ request('search', '') }}">
    </div>

    <div class="eo-field">
        <label for="filterYear"><i class="fas fa-calendar-alt mr-1"></i> Year</label>
        <select id="filterYear" name="year" class="eo-control select2-searchable">
            @php
                $selectedYear = (int) ($year ?? request('year', date('Y')));
            @endphp
            @for($y = 2024; $y <= 2030; $y++)
                <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    @if($activeTab === 'monthly')
        <div class="eo-field">
            <label for="filterMonth"><i class="far fa-calendar-check mr-1"></i> Month</label>
            <select id="filterMonth" name="month" class="eo-control select2-searchable">
                <option value="all" {{ ($selectedMonth ?? request('month')) === 'all' ? 'selected' : '' }}>All Months</option>
                @foreach([
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ] as $mNum => $mName)
                    <option value="{{ $mNum }}" {{ (string) ($selectedMonth ?? request('month')) === (string) $mNum ? 'selected' : '' }}>
                        {{ $mName }} {{ (string) $mNum === (string) now('Asia/Kolkata')->month && (int) ($selectedYear ?? date('Y')) === (int) now('Asia/Kolkata')->year ? '(Current)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="eo-field">
        <label for="filterStage"><i class="fas fa-user-tag mr-1"></i> Stage</label>
        <select id="filterStage" name="stage" class="eo-control select2-searchable">
            <option value="">All Stages</option>
            <option value="permanent" {{ request('stage') === 'permanent' ? 'selected' : '' }}>Permanent</option>
            <option value="probation" {{ request('stage') === 'probation' ? 'selected' : '' }}>Probation</option>
            <option value="internship" {{ request('stage') === 'internship' ? 'selected' : '' }}>Internship</option>
        </select>
    </div>

    <div class="eo-field">
        <label for="filterPolicy"><i class="fas fa-shield-alt mr-1"></i> Policy</label>
        <select id="filterPolicy" name="policy" class="eo-control select2-searchable">
            <option value="">All Policies</option>
            @foreach($policies ?? [] as $pol)
                <option value="{{ strtolower($pol->policy_name) }}" {{ request('policy') === strtolower($pol->policy_name) ? 'selected' : '' }}>{{ $pol->policy_name }}</option>
            @endforeach
        </select>
    </div>

    @if($activeTab !== 'monthly')
        <div class="eo-field">
            <label for="filterStatus"><i class="fas fa-toggle-on mr-1"></i> Status</label>
            <select id="filterStatus" name="status" class="eo-control select2-searchable">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    @endif

    <div class="eo-filter-actions-col">
        <div class="eo-filter-actions-wrap">
            <button type="submit" id="btnFilterSubmit">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            <a href="{{ route('leave-allocations.index', ['tab' => $activeTab, 'year' => date('Y')]) }}" id="resetFilter" style="text-decoration:none;">
                <i class="fas fa-undo mr-1"></i> Reset
            </a>
        </div>
    </div>
</form>
