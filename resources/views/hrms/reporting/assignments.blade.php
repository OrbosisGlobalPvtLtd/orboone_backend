@extends('layouts.panel', ['active' => 'reporting_assignments'])

@section('page_title', isset($selectedSupervisor) && $selectedSupervisor ? 'Reporting Assignments - ' . $selectedSupervisor->display_name : 'Employee Reporting Assignments')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F6F7FB;
    --orb-card: #FFFFFF;
    --orb-border: #E7EAF3;
    --orb-text: #101828;
    --orb-muted: #667085;
    --orb-soft: #F4F2FF;
    --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
}

.rep-page {
    padding: 14px 16px 36px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
    font-family: 'Outfit', sans-serif;
}

.rep-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

/* Signature Hero Header Banner */
.rep-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px;
    padding: 22px 28px;
    margin-bottom: 20px;
    box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    flex-wrap: wrap;
}

.rep-hero:before {
    content: "";
    position: absolute;
    right: -60px;
    top: -80px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.rep-hero-kicker {
    font-size: 11.5px;
    font-weight: 850;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .92;
    margin-bottom: 6px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.rep-hero-title {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    line-height: 1.15;
    color: #ffffff;
}

.rep-hero-subtitle {
    font-size: 13px;
    font-weight: 500;
    margin-top: 5px;
    opacity: .92;
    max-width: 800px;
}

.rep-btn-glass {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.38) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border-radius: 999px !important;
    padding: 8px 18px !important;
    font-size: 12.5px !important;
    font-weight: 750 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    transition: all 0.2s ease !important;
    cursor: pointer;
}

.rep-btn-glass:hover {
    background: rgba(255, 255, 255, 0.32) !important;
    border-color: rgba(255, 255, 255, 0.65) !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

/* Main Card Container */
.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.rep-section-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--orb-border);
    background: linear-gradient(180deg, #fff, #FAFBFF);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.rep-section-title {
    font-size: 16px;
    font-weight: 850;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rep-section-title i {
    color: var(--orb-primary);
}

.rep-section-icon {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    background: var(--orb-soft);
    color: var(--orb-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

/* Horizontal Theme Filter Bar */
.rep-filter-bar {
    padding: 14px 18px;
    background: #FFFFFF;
    border-bottom: 1px solid var(--orb-border);
}

.rep-filter-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    width: 100%;
}

.rep-filter-col-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
    max-width: 380px;
}

.rep-filter-col-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
    font-size: 13px;
    pointer-events: none;
    z-index: 2;
}

.rep-search-input {
    height: 38px;
    padding-left: 34px !important;
    padding-right: 12px;
    border-radius: 10px;
    border: 1px solid #CBD5E1;
    font-size: 12.5px;
    font-weight: 600;
    background: #fff;
    width: 100%;
    transition: all 0.2s ease;
}

.rep-search-input:focus {
    border-color: var(--orb-primary);
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    outline: none;
}

.rep-filter-col-sup {
    min-width: 220px;
    width: 260px;
}

@media (max-width: 768px) {
    .rep-filter-col-sup,
    .rep-filter-col-search {
        width: 100% !important;
        flex: 1 1 100% !important;
        max-width: 100% !important;
    }
}

/* Select2 Standard Filter Skin */
.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid #CBD5E1 !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 10px !important;
    background-color: #fff !important;
    transition: all 0.2s ease;
}

.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--orb-primary) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 12.5px !important;
    font-weight: 650 !important;
    color: #101828 !important;
    padding-left: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
}

.select2-dropdown {
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 12px !important;
    box-shadow: 0 12px 30px rgba(16, 24, 40, 0.12) !important;
    font-size: 12.5px !important;
    font-weight: 550 !important;
    overflow: hidden !important;
    z-index: 9999 !important;
}

.select2-search--dropdown .select2-search__field {
    border-radius: 8px !important;
    border: 1px solid #E2E8F0 !important;
    padding: 6px 10px !important;
    font-size: 12px !important;
}

.select2-results__option--highlighted[aria-selected] {
    background-color: var(--orb-primary, #4B00E8) !important;
    color: #ffffff !important;
}

.rep-filter-actions-right {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .rep-filter-actions-right {
        margin-left: 0;
        width: 100%;
    }
}

/* Brand Theme Search Button */
.rep-search-btn {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 800 !important;
    font-size: 12.5px !important;
    padding: 0 16px !important;
    height: 38px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2) !important;
    transition: all 0.2s ease !important;
    cursor: pointer;
    white-space: nowrap;
}

.rep-search-btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3) !important;
    color: #ffffff !important;
    opacity: 0.95;
}

.rep-reset-btn {
    background: #F8FAFC !important;
    color: #475467 !important;
    border: 1px solid #CBD5E1 !important;
    border-radius: 10px !important;
    font-weight: 750 !important;
    font-size: 12.5px !important;
    padding: 0 14px !important;
    height: 38px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap;
}

.rep-reset-btn:hover {
    background: #F1F5F9 !important;
    color: var(--orb-primary) !important;
    border-color: #94A3B8 !important;
}

/* Compact Select2 for Table Length Entries */
.select2-container--per-page.select2-container--default .select2-selection--single {
    min-height: 36px !important;
    height: 36px !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 10px !important;
    padding: 0 6px !important;
    width: 75px !important;
    background-color: #fff !important;
    display: flex !important;
    align-items: center !important;
}

.select2-container--per-page.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px !important;
    font-size: 12.5px !important;
    font-weight: 750 !important;
    color: var(--orb-text, #101828) !important;
    padding-left: 4px !important;
}

.select2-container--per-page.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px !important;
    right: 6px !important;
}

.select2-dropdown-per-page {
    min-width: 75px !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    z-index: 99999 !important;
}

.select2-dropdown-per-page .select2-results__option {
    padding: 6px 10px !important;
    text-align: center !important;
}

.select2-dropdown-per-page .select2-results__option--highlighted[aria-selected] {
    background-color: var(--orb-primary, #4B00E8) !important;
    color: #ffffff !important;
}

/* Table Tools Bar & Export Buttons */
.orb-table-tools-bar {
    padding: 10px 18px;
    background: #F8FAFC;
    border-bottom: 1px solid #EAECF0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.orb-table-length-box {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 750;
    color: #475467;
}

.orbo-export-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.orbo-export-btn {
    height: 34px !important;
    border-radius: 9px !important;
    padding: 5px 12px !important;
    font-size: 12px !important;
    font-weight: 750 !important;
    color: #344054 !important;
    background: #ffffff !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: 0 1px 2px rgba(16,24,40,0.04) !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    cursor: pointer;
}

.orbo-export-btn:hover {
    background: #F8FAFC !important;
    color: var(--orb-primary) !important;
    border-color: #CBD5E1 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06) !important;
}

.orbo-export-btn .icon-csv { color: #0284C7; }
.orbo-export-btn .icon-excel { color: #16A34A; }
.orbo-export-btn .icon-pdf { color: #DC2626; }
.orbo-export-btn .icon-print { color: var(--orb-primary); }

/* Table Design */
.rep-table-wrap {
    padding: 0 !important;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #CBD5E1 #F1F5F9;
}

.rep-table-wrap::-webkit-scrollbar {
    height: 6px;
}

.rep-table-wrap::-webkit-scrollbar-track {
    background: #F1F5F9;
    border-radius: 999px;
}

.rep-table-wrap::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 999px;
}

.rep-table {
    width: 100% !important;
    min-width: 900px;
    border-collapse: separate !important;
    border-spacing: 0;
    margin: 0 !important;
}

.rep-table thead th {
    background: #F8FAFC !important;
    color: #475467 !important;
    font-size: 11px !important;
    font-weight: 850 !important;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 12px 14px !important;
    border-top: none !important;
    border-bottom: 1px solid #EAECF0 !important;
    white-space: nowrap;
    vertical-align: middle !important;
}

.rep-table tbody td {
    background: #fff;
    border-bottom: 1px solid #F2F4F7 !important;
    padding: 11px 14px !important;
    vertical-align: middle !important;
    white-space: nowrap;
    font-size: 12.5px;
    color: #1E293B;
}

.rep-table tbody tr:hover td {
    background: #FCFAFF !important;
}

.emp-name-link {
    font-size: 13px;
    font-weight: 800;
    color: #0F172A;
    text-decoration: none !important;
    display: block;
    line-height: 1.25;
    transition: color 0.15s ease;
}

.emp-name-link:hover {
    color: var(--orb-primary);
}

.emp-code-badge {
    font-size: 11px;
    font-weight: 750;
    color: #64748B;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

/* Status Badges */
.status-pill-active {
    background: #DCFCE7;
    color: #15803D;
    border: 1px solid #86EFAC;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-transform: uppercase;
    letter-spacing: 0.2px;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">

        <!-- Signature Hero Header Banner -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-users-cog"></i> Team Management &bull; Reporting Relationships
                </div>
                @if(isset($selectedSupervisor) && $selectedSupervisor)
                    <h1 class="rep-hero-title">Reporting Assignments: {{ $selectedSupervisor->display_name }}</h1>
                    <div class="rep-hero-subtitle">
                        Managing active reporting employees assigned to <strong>{{ $selectedSupervisor->display_name }}</strong> ({{ $selectedSupervisor->employee_code }} &bull; {{ optional($selectedSupervisor->department)->name ?? 'General' }}).
                    </div>
                @else
                    <h1 class="rep-hero-title">Employee Reporting Assignments</h1>
                    <div class="rep-hero-subtitle">
                        Manage reporting relationships between Reporting Managers and employees across all enterprise departments.
                    </div>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(!empty($isAdminOrHR))
                    <button type="button" class="rep-btn-glass" data-toggle="modal" data-target="#assignModal">
                        <i class="fas fa-user-plus mr-1"></i>
                        @if(isset($selectedSupervisor) && $selectedSupervisor)
                            Assign to {{ $selectedSupervisor->display_name }}
                        @else
                            Assign Reporting Manager
                        @endif
                    </button>
                @endif
                
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 14px; border: 1px solid #86EFAC; background: #ECFDF5; color: #065F46; font-weight: 650; font-size: 13px;">
                <i class="fas fa-check-circle mr-2" style="font-size: 16px;"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="close text-success" data-dismiss="alert" style="outline: none;"><span>&times;</span></button>
            </div>
        @endif

        <!-- Main Assignments Card -->
        <div class="rep-card">
            <!-- Section Header -->
            <div class="rep-section-head">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div class="rep-section-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h4 class="rep-section-title">Active Reporting Assignments</h4>
                        <small class="text-muted font-weight-bold" style="font-size: 11.5px;">All verified employee-supervisor mapping records</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge" style="background: var(--orb-soft); color: var(--orb-primary); font-size: 12px; font-weight: 800; padding: 6px 12px; border-radius: 10px; border: 1px solid rgba(75, 0, 232, 0.15);">
                        <i class="fas fa-link mr-1"></i> {{ $assignments->total() }} Total Active Assignments
                    </span>
                </div>
            </div>

            <!-- Horizontal Live Filter Form -->
            <form method="GET" action="{{ route('reporting.assignments') }}" id="assignmentsFilterForm">
                <input type="hidden" name="per_page" id="filterPerPageHidden" value="{{ request('per_page', 25) }}">

                <div class="rep-filter-bar">
                    <div class="rep-filter-row">
                        <!-- 1. Search Input -->
                        <div class="rep-filter-col-search">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" id="filterSearchInput" class="rep-search-input" placeholder="Search employee, manager, code..." value="{{ request('search') }}">
                        </div>

                        <!-- 2. Supervisor Filter Dropdown -->
                        <div class="rep-filter-col-sup">
                            <select name="supervisor_id" id="filterSupervisorSelect" class="select2-filter">
                                <option value="">All Reporting Managers</option>
                                @foreach($supervisors as $sup)
                                    <option value="{{ $sup->id }}" {{ (request('supervisor_id') == $sup->id || (isset($selectedSupervisor) && $selectedSupervisor && $selectedSupervisor->id == $sup->id)) ? 'selected' : '' }}>
                                        {{ $sup->display_name }} ({{ $sup->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 3. Action Buttons -->
                        <div class="rep-filter-actions-right">
                            <button type="submit" class="rep-search-btn">
                                <i class="fas fa-search"></i> Search
                            </button>
                            @if(request('search') || request('supervisor_id') || (isset($selectedSupervisor) && $selectedSupervisor))
                                <a href="{{ route('reporting.assignments') }}" class="rep-reset-btn" title="View All Assignments">
                                    <i class="fas fa-undo"></i> View All
                                </a>
                            @else
                                <a href="{{ route('reporting.assignments') }}" class="rep-reset-btn" title="Reset Filters">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

            <!-- Table Tools Bar: Show [ 25 ] entries + Export Buttons -->
            <div class="orb-table-tools-bar">
                <div class="orb-table-length-box">
                    <span>Show</span>
                    <select id="recordsPerPageSelect" class="table-per-page-select">
                        <option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="orbo-export-group">
                    <button type="button" class="orbo-export-btn" id="btnExportCSV">
                        <i class="fas fa-file-csv icon-csv"></i> CSV
                    </button>
                    <button type="button" class="orbo-export-btn" id="btnExportExcel">
                        <i class="fas fa-file-excel icon-excel"></i> Excel
                    </button>
                    <button type="button" class="orbo-export-btn" id="btnExportPDF">
                        <i class="fas fa-file-pdf icon-pdf"></i> PDF
                    </button>
                    <button type="button" class="orbo-export-btn" id="btnPrint">
                        <i class="fas fa-print icon-print"></i> Print
                    </button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="rep-table-wrap">
                <table class="rep-table" id="assignmentsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Employee</th>
                            <th>Department & Designation</th>
                            <th>Reporting Manager</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Assigned Since</th>
                            @if(!empty($isAdminOrHR))
                            <th class="text-right" style="width: 100px;">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assign)
                        <tr>
                            <!-- 1. S.No -->
                            <td class="text-center font-weight-bold text-muted" style="font-size: 12px;">
                                {{ ($assignments->currentPage() - 1) * $assignments->perPage() + $loop->iteration }}
                            </td>

                            <!-- 2. Employee -->
                            <td>
                                <div>
                                    <strong class="emp-name-link">{{ $assign->employee_name }}</strong>
                                    <div class="emp-code-badge">
                                        <i class="fas fa-id-badge text-muted" style="font-size: 10px;"></i> {{ $assign->employee_code }}
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Department & Designation -->
                            <td>
                                <div>
                                    <span class="badge badge-light border text-primary font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 11px;">
                                        {{ $assign->department_name ?? 'General' }}
                                    </span>
                                    <small class="text-muted font-weight-bold d-block mt-0.5" style="font-size: 11px;">
                                        {{ $assign->designation_name ?? 'Employee' }}
                                    </small>
                                </div>
                            </td>

                            <!-- 4. Reporting Manager -->
                            <td>
                                <div>
                                    <strong class="text-dark font-weight-bold d-block" style="font-size: 12.5px; line-height: 1.2;">
                                        <i class="fas fa-user-shield text-warning mr-1" style="font-size: 11px;"></i> {{ $assign->supervisor_name }}
                                    </strong>
                                </div>
                            </td>

                            <!-- 5. Status -->
                            <td class="text-center">
                                <span class="status-pill-active">
                                    <i class="fas fa-check-circle mr-1" style="font-size: 10px;"></i> Active
                                </span>
                            </td>

                            <!-- 6. Assigned Since -->
                            <td class="text-center">
                                <span class="text-muted font-weight-bold" style="font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($assign->start_date ?? $assign->created_at)->format('d M Y') }}
                                </span>
                            </td>

                            <!-- 7. Actions -->
                            @if(!empty($isAdminOrHR))
                            <td class="text-right">
                                <form method="POST" action="{{ route('reporting.assignments.relieve', $assign->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to relieve this reporting assignment?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold px-2.5 py-1" style="border-radius: 8px; font-size: 11px;">
                                        <i class="fas fa-user-minus mr-1"></i> Relieve
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ !empty($isAdminOrHR) ? 7 : 6 }}" class="text-center text-muted py-5">
                                <i class="fas fa-users-cog fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                <h5 class="font-weight-bold text-dark">No Active Reporting Assignments Found</h5>
                                <p class="small mb-0">No assignments match your search filter or selected reporting manager.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination Bar using Orbo Theme -->
            @if(method_exists($assignments, 'links'))
                <div>
                    {{ $assignments->appends(request()->query())->links('vendor.pagination.orbo') }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Assign Employees Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form method="POST" action="{{ route('reporting.assignments.assign') }}">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);">
                    <h5 class="modal-title font-weight-bold" style="font-size: 16px;">
                        <i class="fas fa-user-plus mr-2"></i>
                        @if(isset($selectedSupervisor) && $selectedSupervisor)
                            Assign Employees to {{ $selectedSupervisor->display_name }}
                        @else
                            Assign Employees to Reporting Manager
                        @endif
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="outline: none;"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12.5px;">Select Reporting Manager <span class="text-danger">*</span></label>
                        <select name="supervisor_employee_id" class="form-control select2-modal" required style="width: 100%;">
                            <option value="">-- Select Reporting Manager --</option>
                            @foreach($allSupervisors ?? $supervisors as $sup)
                                <option value="{{ $sup->id }}" {{ (old('supervisor_employee_id', optional($selectedSupervisor)->id) == $sup->id) ? 'selected' : '' }}>
                                    {{ $sup->display_name }} ({{ $sup->employee_code }} &bull; {{ optional($sup->department)->name ?? 'General' }} &bull; {{ optional($sup->designation)->name ?? 'Manager' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark" style="font-size: 12.5px;">Assigned From Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required style="height: 38px; border-radius: 10px; font-weight: 600;">
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark" style="font-size: 12.5px;">Select Reporting Employees <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <input type="text" id="empSearchInput" class="form-control" placeholder="Search by name, department or code..." style="height: 38px; border-radius: 10px 0 0 10px; font-size: 12.5px;">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold px-3" id="selectAllBtn" style="font-size: 11px;">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3" id="clearAllBtn" style="font-size: 11px;">Clear</button>
                            </div>
                        </div>

                        <div class="border rounded p-3 bg-white" id="empCheckboxContainer" style="max-height: 260px; overflow-y: auto; border-radius: 12px !important;">
                            @foreach($employees as $emp)
                            <div class="custom-control custom-checkbox py-2 border-bottom border-light emp-item" data-emp-id="{{ $emp->id }}">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="custom-control-input emp-checkbox" id="emp_cb_{{ $emp->id }}">
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer pl-1" for="emp_cb_{{ $emp->id }}" style="user-select: none;">
                                    <span class="text-dark" style="font-size: 13px;">{{ $emp->display_name }}</span>
                                    <small class="text-muted ml-1" style="font-size: 11px;">({{ $emp->employee_code }} &bull; {{ optional($emp->department)->name ?? 'Dept' }} &bull; {{ optional($emp->designation)->name ?? 'Employee' }})</small>

                                    @if($emp->current_supervisor_name)
                                        <div class="mt-1">
                                            <span class="badge badge-warning text-dark px-2 py-0.5" style="font-size: 10px; border-radius: 6px;">
                                                <i class="fas fa-exchange-alt mr-1"></i>Reporting Manager: {{ $emp->current_supervisor_name }} (Will Transfer)
                                            </span>
                                        </div>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 10px; font-size: 12.5px;">Cancel</button>
                    <button type="submit" class="rep-search-btn px-4" style="height: 38px;">
                        <i class="fas fa-check-circle mr-1"></i> Assign Reporting Employees
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function getPdfMake() {
    if (typeof window.pdfMake === 'undefined') {
        return null;
    }
    if (typeof window.vfsFonts !== 'undefined' && window.vfsFonts.pdfMake) {
        window.pdfMake.vfs = window.vfsFonts.pdfMake.vfs;
    } else if (typeof window.pdfMake.vfs === 'undefined' && typeof window.pdfMake_vfs !== 'undefined') {
        window.pdfMake.vfs = window.pdfMake_vfs;
    }
    return window.pdfMake;
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-filter').select2({
            width: '100%'
        });

        $('#assignModal').on('shown.bs.modal', function () {
            $(this).find('.select2-modal').select2({
                dropdownParent: $('#assignModal'),
                width: '100%'
            });
        });

        // Initialize Per-Page Select2
        $('.table-per-page-select').select2({
            minimumResultsForSearch: -1,
            containerCssClass: 'select2-container--per-page',
            dropdownCssClass: 'select2-dropdown-per-page',
            width: '75px'
        });
    }

    // 2. Per-page change handler
    $('#recordsPerPageSelect').on('change', function() {
        $('#filterPerPageHidden').val(this.value);
        $('#assignmentsFilterForm').submit();
    });

    // 3. Modal employee search & filtering
    var searchInput = document.getElementById('empSearchInput');
    var items = document.querySelectorAll('.emp-item');
    var supervisorSelect = document.querySelector('select[name="supervisor_employee_id"]');

    function filterEmployees() {
        var searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
        var selectedSupId = supervisorSelect ? supervisorSelect.value : '';

        items.forEach(function(item) {
            var empId = item.getAttribute('data-emp-id');
            var cb = item.querySelector('.emp-checkbox');
            var text = item.textContent.toLowerCase();

            if (selectedSupId && empId === selectedSupId) {
                item.style.display = 'none';
                if (cb) cb.checked = false;
            } else {
                if (searchVal === '' || text.indexOf(searchVal) !== -1) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterEmployees);
    }

    if (supervisorSelect) {
        $(supervisorSelect).on('change', filterEmployees);
    }

    filterEmployees();

    var selectAll = document.getElementById('selectAllBtn');
    var clearAll = document.getElementById('clearAllBtn');

    if (selectAll) {
        selectAll.addEventListener('click', function() {
            document.querySelectorAll('.emp-checkbox').forEach(function(cb) {
                if (cb.closest('.emp-item').style.display !== 'none') {
                    cb.checked = true;
                }
            });
        });
    }

    if (clearAll) {
        clearAll.addEventListener('click', function() {
            document.querySelectorAll('.emp-checkbox').forEach(function(cb) {
                cb.checked = false;
            });
        });
    }

    // 4. CSV Export
    $('#btnExportCSV').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let csv = [];
        let headers = [];
        $('#assignmentsTable thead th').each(function(i) {
            let totalCols = $('#assignmentsTable thead th').length;
            let limit = {{ !empty($isAdminOrHR) ? 'totalCols - 1' : 'totalCols' }};
            if (i < limit) {
                headers.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
            }
        });
        csv.push(headers.join(','));

        $('#assignmentsTable tbody tr').each(function() {
            let cols = $(this).find('td');
            if (cols.length >= 6) {
                let row = [];
                // 0. S.No
                row.push('"' + $(cols[0]).text().trim().replace(/"/g, '""') + '"');
                // 1. Employee
                let empName = $(cols[1]).find('.emp-name-link').text().trim() || $(cols[1]).text().trim();
                let empCode = $(cols[1]).find('.emp-code-badge').text().trim();
                row.push('"' + (empName + (empCode ? ' (' + empCode + ')' : '')).replace(/"/g, '""') + '"');
                // 2. Department & Designation
                let dept = $(cols[2]).find('span').text().trim() || $(cols[2]).text().trim();
                let desig = $(cols[2]).find('small').text().trim();
                row.push('"' + (dept + (desig ? ' - ' + desig : '')).replace(/"/g, '""') + '"');
                // 3. Reporting Manager
                let manager = $(cols[3]).text().replace(/\s+/g, ' ').trim();
                row.push('"' + manager.replace(/"/g, '""') + '"');
                // 4. Status
                let status = $(cols[4]).text().trim();
                row.push('"' + status.replace(/"/g, '""') + '"');
                // 5. Assigned Since
                let date = $(cols[5]).text().trim();
                row.push('"' + date.replace(/"/g, '""') + '"');

                csv.push(row.join(','));
            }
        });

        let csvString = '\uFEFF' + csv.join('\r\n');
        let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'Reporting_Assignments_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(function() { URL.revokeObjectURL(url); }, 500);
    });

    // 5. Excel Export
    $('#btnExportExcel').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let rowsHtml = '';
        
        // Header
        rowsHtml += '<tr style="background-color: #4B00E8; color: #FFFFFF; font-weight: bold;">';
        $('#assignmentsTable thead th').each(function(i) {
            let totalCols = $('#assignmentsTable thead th').length;
            let limit = {{ !empty($isAdminOrHR) ? 'totalCols - 1' : 'totalCols' }};
            if (i < limit) {
                rowsHtml += '<th style="padding: 10px; border: 1px solid #CBD5E1;">' + $(this).text().trim() + '</th>';
            }
        });
        rowsHtml += '</tr>';

        // Body
        $('#assignmentsTable tbody tr').each(function(rowIndex) {
            let cols = $(this).find('td');
            if (cols.length >= 6) {
                let bg = rowIndex % 2 === 0 ? '#FFFFFF' : '#F8FAFC';
                rowsHtml += '<tr style="background-color: ' + bg + ';">';
                
                // S.No
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0; text-align: center;">' + $(cols[0]).text().trim() + '</td>';
                // Employee
                let empName = $(cols[1]).find('.emp-name-link').text().trim() || $(cols[1]).text().trim();
                let empCode = $(cols[1]).find('.emp-code-badge').text().trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0;"><strong>' + empName + '</strong><br><small style="color: #64748B;">' + empCode + '</small></td>';
                // Department & Designation
                let dept = $(cols[2]).find('span').text().trim() || $(cols[2]).text().trim();
                let desig = $(cols[2]).find('small').text().trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0;">' + dept + '<br><small style="color: #64748B;">' + desig + '</small></td>';
                // Reporting Manager
                let manager = $(cols[3]).text().replace(/\s+/g, ' ').trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0;">' + manager + '</td>';
                // Status
                let status = $(cols[4]).text().trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0; text-align: center;">' + status + '</td>';
                // Date
                let date = $(cols[5]).text().trim();
                rowsHtml += '<td style="padding: 8px; border: 1px solid #E2E8F0; text-align: center;">' + date + '</td>';

                rowsHtml += '</tr>';
            }
        });

        let template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' +
            '<head><meta charset="utf-8"></head>' +
            '<body><table border="1">' + rowsHtml + '</table></body></html>';

        let blob = new Blob(['\uFEFF' + template], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'Reporting_Assignments_' + new Date().toISOString().slice(0,10) + '.xls');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(function() { URL.revokeObjectURL(url); }, 500);
    });

    // 6. PDF Export (Direct PDF Download using pdfMake)
    $('#btnExportPDF').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let tableBody = [];
        
        // Header
        let headerRow = [];
        $('#assignmentsTable thead th').each(function(i) {
            let totalCols = $('#assignmentsTable thead th').length;
            let limit = {{ !empty($isAdminOrHR) ? 'totalCols - 1' : 'totalCols' }};
            if (i < limit) {
                headerRow.push({
                    text: $(this).text().trim(),
                    bold: true,
                    color: '#FFFFFF',
                    fillColor: '#4B00E8',
                    fontSize: 9,
                    margin: [0, 4, 0, 4]
                });
            }
        });
        tableBody.push(headerRow);

        // Body
        $('#assignmentsTable tbody tr').each(function() {
            let cols = $(this).find('td');
            if (cols.length >= 6) {
                let row = [];
                // 0. S.No
                row.push({ text: $(cols[0]).text().trim(), alignment: 'center', fontSize: 8.5 });
                // 1. Employee
                let empName = $(cols[1]).find('.emp-name-link').text().trim() || $(cols[1]).text().trim();
                let empCode = $(cols[1]).find('.emp-code-badge').text().trim();
                row.push({ text: empName + '\n' + empCode, bold: true, fontSize: 8.5 });
                // 2. Department & Designation
                let dept = $(cols[2]).find('span').text().trim() || $(cols[2]).text().trim();
                let desig = $(cols[2]).find('small').text().trim();
                row.push({ text: dept + '\n' + desig, fontSize: 8.5 });
                // 3. Reporting Manager
                let manager = $(cols[3]).text().replace(/\s+/g, ' ').trim();
                row.push({ text: manager, fontSize: 8.5 });
                // 4. Status
                let status = $(cols[4]).text().trim();
                row.push({ text: status, alignment: 'center', fontSize: 8.5 });
                // 5. Assigned Since
                let date = $(cols[5]).text().trim();
                row.push({ text: date, alignment: 'center', fontSize: 8.5 });

                tableBody.push(row);
            }
        });

        let printDate = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

        let docDefinition = {
            pageOrientation: 'landscape',
            pageSize: 'A4',
            pageMargins: [20, 25, 20, 25],
            header: function(currentPage, pageCount) {
                return {
                    margin: [20, 10, 20, 0],
                    columns: [
                        { text: 'OrboOne HRMS — Reporting Assignments', fontSize: 8, bold: true, color: '#4B00E8' },
                        { text: 'Page ' + currentPage + ' of ' + pageCount, alignment: 'right', fontSize: 8, color: '#64748B' }
                    ]
                };
            },
            content: [
                {
                    text: 'Employee Reporting Assignments',
                    fontSize: 16,
                    bold: true,
                    color: '#101828',
                    margin: [0, 0, 0, 3]
                },
                {
                    text: 'Exported on: ' + printDate,
                    fontSize: 9,
                    color: '#64748B',
                    margin: [0, 0, 0, 12]
                },
                {
                    table: {
                        headerRows: 1,
                        widths: ['6%', '22%', '24%', '24%', '10%', '14%'],
                        body: tableBody
                    },
                    layout: {
                        fillColor: function (rowIndex) {
                            if (rowIndex === 0) return '#4B00E8';
                            return (rowIndex % 2 === 0) ? '#F8FAFC' : null;
                        },
                        hLineWidth: function () { return 0.5; },
                        vLineWidth: function () { return 0.5; },
                        hLineColor: function () { return '#E2E8F0'; },
                        vLineColor: function () { return '#E2E8F0'; },
                        paddingLeft: function () { return 6; },
                        paddingRight: function () { return 6; },
                        paddingTop: function () { return 5; },
                        paddingBottom: function () { return 5; }
                    }
                }
            ],
            defaultStyle: {
                font: 'Roboto'
            }
        };

        const pMake = getPdfMake();
        if (pMake && typeof pMake.createPdf === 'function') {
            try {
                pMake.createPdf(docDefinition).download('Reporting_Assignments_' + new Date().toISOString().slice(0,10) + '.pdf');
            } catch (err) {
                console.error('PDF Generation Error:', err);
                alert('PDF generation encountered an error. Please try again.');
            }
        } else {
            alert('PDF engine is loading. Please wait a moment and try again.');
        }
    });

    // 7. Print (Dedicated Styled Print Window)
    $('#btnPrint').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let printWin = window.open('', '_blank', 'width=1100,height=750');
        if (!printWin) {
            window.print();
            return;
        }

        let rowsHtml = '';
        
        // Header
        rowsHtml += '<thead><tr>';
        $('#assignmentsTable thead th').each(function(i) {
            let totalCols = $('#assignmentsTable thead th').length;
            let limit = {{ !empty($isAdminOrHR) ? 'totalCols - 1' : 'totalCols' }};
            if (i < limit) {
                rowsHtml += '<th>' + $(this).text().trim() + '</th>';
            }
        });
        rowsHtml += '</tr></thead><tbody>';

        // Body
        $('#assignmentsTable tbody tr').each(function() {
            let cols = $(this).find('td');
            if (cols.length >= 6) {
                rowsHtml += '<tr>';
                // S.No
                rowsHtml += '<td style="text-align: center;">' + $(cols[0]).text().trim() + '</td>';
                // Employee
                let empName = $(cols[1]).find('.emp-name-link').text().trim() || $(cols[1]).text().trim();
                let empCode = $(cols[1]).find('.emp-code-badge').text().trim();
                rowsHtml += '<td><strong>' + empName + '</strong><br><small style="color: #64748B;">' + empCode + '</small></td>';
                // Department & Designation
                let dept = $(cols[2]).find('span').text().trim() || $(cols[2]).text().trim();
                let desig = $(cols[2]).find('small').text().trim();
                rowsHtml += '<td>' + dept + '<br><small style="color: #64748B;">' + desig + '</small></td>';
                // Reporting Manager
                let manager = $(cols[3]).text().replace(/\s+/g, ' ').trim();
                rowsHtml += '<td>' + manager + '</td>';
                // Status
                let status = $(cols[4]).text().trim();
                rowsHtml += '<td style="text-align: center;">' + status + '</td>';
                // Date
                let date = $(cols[5]).text().trim();
                rowsHtml += '<td style="text-align: center;">' + date + '</td>';
                rowsHtml += '</tr>';
            }
        });
        rowsHtml += '</tbody>';

        let printDate = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

        let html = `<!DOCTYPE html>
        <html>
        <head>
            <title>Reporting Assignments (Print)</title>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
            <style>
                @media print {
                    @page { size: A4 landscape; margin: 10mm 12mm; }
                }
                body {
                    font-family: 'Outfit', sans-serif;
                    color: #0F172A;
                    background: #FFFFFF;
                    margin: 0;
                    padding: 16px;
                }
                .print-hero {
                    background: linear-gradient(135deg, #4B00E8 0%, #FF5252 100%) !important;
                    border-radius: 14px;
                    padding: 18px 24px;
                    color: #ffffff;
                    margin-bottom: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                .print-hero h2 { margin: 0; font-size: 20px; font-weight: 800; color: #fff; }
                .print-hero p { margin: 4px 0 0 0; font-size: 12px; opacity: 0.9; color: #fff; }
                .print-meta { background: rgba(255,255,255,0.22); padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; color: #fff; }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px;
                    margin-top: 10px;
                }
                th {
                    background: #1E293B !important;
                    color: #FFFFFF !important;
                    font-size: 11px;
                    font-weight: 800;
                    text-transform: uppercase;
                    padding: 9px 12px;
                    text-align: left;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                    border: 1px solid #1E293B;
                }
                td {
                    padding: 8px 12px;
                    border: 1px solid #E2E8F0;
                    vertical-align: middle;
                }
                tr:nth-child(even) td {
                    background: #F8FAFC !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            </style>
        </head>
        <body>
            <div class="print-hero">
                <div>
                    <h2>OrboOne HRMS</h2>
                    <p>Team Management &bull; Employee Reporting Assignments</p>
                </div>
                <div class="print-meta">
                    Date: ${printDate}
                </div>
            </div>
            <table>${rowsHtml}</table>
        </body>
        </html>`;

        printWin.document.open();
        printWin.document.write(html);
        printWin.document.close();
        
        printWin.onload = function() {
            printWin.focus();
            setTimeout(function() {
                printWin.print();
            }, 300);
        };
    });
});
</script>
@endsection
