<div class="orb-table-card">
    {{-- 1. Table Card Header --}}
    <div class="orb-table-head d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="orb-table-title-wrap">
            <span class="orb-table-icon"><i class="fas fa-user-clock"></i></span>
            <div>
                <h3>Employee Profiles</h3>
                <p>Approve completed profiles or reject with reason for correction.</p>
            </div>
        </div>
    </div>

    {{-- 2. Filters Toolbar under Table Header --}}
    <div class="orb-table-tools border-bottom">
        <div class="eo-filter-grid">
            <div class="eo-field">
                <label>Search</label>
                <input type="text" id="filterSearch" class="eo-control" style="height: 38px !important;" placeholder="Search name, code, email, designation...">
            </div>

            <x-form.select
                id="filterDepartment"
                name="department"
                label="Department"
                :options="$departments ?? []"
                placeholder="All Departments"
                :searchable="true"
                wrapper-class="eo-field mb-0"
                class="eo-control"
            />

            <x-form.select
                id="filterStatus"
                name="status"
                label="Profile Status"
                :options="[
                    'pending' => 'Pending',
                    'submitted' => 'Submitted',
                    'rejected' => 'Rejected'
                ]"
                placeholder="All Status"
                :searchable="true"
                wrapper-class="eo-field mb-0"
                class="eo-control"
            />

            <div class="eo-field eo-filter-actions-col">
                <label class="d-none d-sm-block">&nbsp;</label>
                <div class="eo-filter-actions-wrap">
                    <x-ui.button
                        type="button"
                        id="btnPendingFilterSubmit"
                        variant="search"
                        icon="fas fa-search mr-1"
                        title="Search / Apply Filter"
                        class="orbo-button-flex"
                    >
                        Search
                    </x-ui.button>
                    <x-ui.button
                        type="button"
                        id="resetFilter"
                        variant="reset"
                        icon="fas fa-undo mr-1"
                        title="Reset Filters"
                        class="orbo-button-flex"
                    >
                        Reset
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. DataTable Toolbar (Length left, Reusable Export Buttons Component right) --}}
    <div class="orb-table-tools-bar">
        <div id="pendingProfilesLengthBox" class="orb-table-length-box"></div>
        <div id="pendingProfilesExportButtons" class="orb-table-export-buttons">
            <x-ui.export-buttons table="pendingProfilesTable" />
        </div>
    </div>

    {{-- 4. Main Profiles Table (Responsive Horizontal Scroll) --}}
    <div class="orb-table-wrap">
        <table id="pendingProfilesTable" class="table pp-table">
            <thead>
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th>Employee</th>
                    <th>Code</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 110px;">Approve</th>
                    <th style="width: 120px;">Updated</th>
                    <th class="text-center" style="width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    {{-- 6. Table Footer (Info & Pagination matching vendor/pagination/orbo theme) --}}
    <div class="eo-table-footer orb-pagination-wrapper">
        <div id="pendingProfilesInfoBox" class="orb-pagination-info"></div>
        <div id="pendingProfilesPaginationBox"></div>
    </div>
</div>
