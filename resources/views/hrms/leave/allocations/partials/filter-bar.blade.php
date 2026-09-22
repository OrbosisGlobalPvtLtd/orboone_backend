<div class="eo-filter-grid">
    <div class="eo-field">
        <label for="filterSearch"><i class="fas fa-search mr-1"></i> Quick Search</label>
        <input type="text" id="filterSearch" class="eo-control" placeholder="Search by employee name, code or year..." autocomplete="off">
    </div>

    <div class="eo-field">
        <label for="filterYear"><i class="fas fa-calendar-alt mr-1"></i> Year</label>
        <select id="filterYear" class="eo-control">
            @php
                $selectedYear = (int) ($year ?? date('Y'));
            @endphp
            @for($y = 2024; $y <= 2030; $y++)
                <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    <div class="eo-field">
        <label for="filterStage"><i class="fas fa-user-tag mr-1"></i> Stage</label>
        <select id="filterStage" class="eo-control">
            <option value="">All Stages</option>
            <option value="permanent">Permanent</option>
            <option value="probation">Probation</option>
            <option value="internship">Internship</option>
        </select>
    </div>

    <div class="eo-field">
        <label for="filterPolicy"><i class="fas fa-shield-alt mr-1"></i> Policy</label>
        <select id="filterPolicy" class="eo-control">
            <option value="">All Policies</option>
            @foreach($policies ?? [] as $pol)
                <option value="{{ strtolower($pol->policy_name) }}">{{ $pol->policy_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="eo-field">
        <label for="filterStatus"><i class="fas fa-toggle-on mr-1"></i> Status</label>
        <select id="filterStatus" class="eo-control">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="upcoming">Upcoming</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div class="eo-filter-actions-col">
        <div class="eo-filter-actions-wrap">
            <button type="button" id="btnFilterSubmit">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            <button type="button" id="resetFilter">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
        </div>
    </div>
</div>
