<!-- Filters Section (One Single Compact Row on Desktop) -->
<div class="eo-filter-inside">
    <div class="exit-filter-grid">
        <div class="eo-field">
            <label>Search Employee</label>
            <input type="text" id="filterSearch" class="eo-control" placeholder="Name, code or email...">
        </div>
        <div class="eo-field">
            <label>Department</label>
            <select id="filterDepartment" class="eo-control select2-filter">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                <option value="{{ strtolower($dept) }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <div class="eo-field">
            <label>Status</label>
            <select id="filterStatus" class="eo-control select2-filter">
                <option value="">All Statuses</option>
                <option value="notice_period">Notice Period</option>
                <option value="ready_for_final_approval">Ready For Final Approval</option>
                <option value="exit_completed">Exit Completed</option>
                <option value="exit_initiated">Exit Initiated</option>
                <option value="terminated">Terminated</option>
                <option value="discontinued">Discontinuation</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="eo-field">
            <label>Exit Type</label>
            <select id="filterExitType" class="eo-control select2-filter">
                <option value="">All Exit Types</option>
                <option value="resignation">Resignation</option>
                <option value="termination">Termination</option>
                <option value="discontinued">Discontinuation</option>
                <option value="absconding">Absconding</option>
                <option value="retirement">Retirement</option>
                <option value="contract_end">End of Contract</option>
                <option value="internship_completed">Completion of Internship</option>
                <option value="deceased">Death</option>
            </select>
        </div>
        <div class="eo-field">
            <label>Asset Status</label>
            <select id="filterAssetStatus" class="eo-control select2-filter">
                <option value="">All Asset Statuses</option>
                @foreach ($assetStatuses as $ast)
                <option value="{{ strtolower($ast) }}">{{ ucfirst(str_replace('_', ' ', $ast)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="eo-field">
            <label>FNF Status</label>
            <select id="filterFnfStatus" class="eo-control select2-filter">
                <option value="">All FNF Statuses</option>
                @foreach ($fnfStatuses as $fnf)
                <option value="{{ strtolower($fnf) }}">{{ ucfirst(str_replace('_', ' ', $fnf)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="eo-field eo-filter-btns">
            <button type="button" id="btnExitFilterSubmit" class="btn-search-primary">
                <i class="fas fa-search"></i> Search
            </button>
            <button type="button" id="resetFilter" class="btn-reset-secondary">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
        </div>
    </div>
</div>
