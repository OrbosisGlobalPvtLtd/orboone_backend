{{-- Card Header with Title & View Toggles --}}
<div class="eo-card-header-premium">
    <div class="eo-card-header-left">
        <div class="eo-header-icon-circle">
            <i class="fas fa-sitemap"></i>
        </div>
        <div>
            <h4 class="eo-card-title-premium">Organization Chart</h4>
            <p class="eo-card-subtitle-premium">Explore interactive team hierarchy reporting structures.</p>
        </div>
    </div>

    <div class="eo-view-toggle">
        <button type="button" id="btnTreeView" class="eo-view-btn active" title="Switch to Tree View">
            <i class="fas fa-network-wired"></i> Tree Chart
        </button>
        <button type="button" id="btnListView" class="eo-view-btn" title="Switch to List View">
            <i class="fas fa-list-ul"></i> Stacked List
        </button>
    </div>
</div>

{{-- Toolbar Filter Controls --}}
<div class="eo-filter-inside">
    <div class="eo-filter-grid">
        <div class="eo-field">
            <label for="filterSearch">Search Employee</label>
            <input type="text" id="filterSearch" class="eo-control" placeholder="Search by name or code...">
        </div>

        <div class="eo-field">
            <label for="filterDepartment">Department</label>
            <select id="filterDepartment" class="eo-control select2-searchable" style="width: 100%;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ strtolower($dept) }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        <div class="eo-field">
            <label for="filterDesignation">Designation</label>
            <select id="filterDesignation" class="eo-control select2-searchable" style="width: 100%;">
                <option value="">All Designations</option>
                @foreach($designations as $desg)
                    <option value="{{ strtolower($desg) }}">{{ $desg }}</option>
                @endforeach
            </select>
        </div>

        <div class="eo-field eo-filter-actions-col">
            <label class="d-none d-sm-block">&nbsp;</label>
            <div class="eo-filter-actions-wrap">
                <button type="button" id="btnApplyFilters" class="eo-btn eo-btn-primary" title="Search / Apply Filter">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <button type="button" id="btnResetFilters" class="eo-btn eo-btn-reset" title="Reset Filters">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>
