@extends('layouts.panel', ['active' => 'reporting_work_reports'])

@section('page_title', 'Reporting Employee Work Reports')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.rep-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.rep-container {
    max-width: 1550px;
    margin: 0 auto;
}

.rep-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

/* Toolbar & Length Dropdown CSS */
.orb-table-toolbar {
    background: #FAFAFA;
    border-bottom: 1px solid #E2E8F0;
}

.dataTables_length,
.dataTables_length label {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    white-space: nowrap !important;
    margin: 0 !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    color: #475467 !important;
}

.dataTables_length select {
    width: 72px !important;
    height: 34px !important;
    padding: 4px 10px !important;
    border-radius: 8px !important;
    border: 1px solid #CBD5E1 !important;
    outline: none !important;
}

/* Export button CSS */
.orb-export-btn {
    height: 34px !important;
    padding: 0 12px !important;
    border-radius: 10px !important;
    background: #fff !important;
    border: 1px solid #E7EAF3 !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    margin-left: 6px !important;
    transition: all 0.2s ease !important;
    color: #475467 !important;
}

.orb-export-btn:hover {
    background: #F1F5F9 !important;
    color: var(--orb-primary) !important;
    border-color: rgba(75, 0, 232, 0.2) !important;
    transform: translateY(-1px) !important;
}

.dt-buttons {
    display: inline-flex !important;
    align-items: center !important;
}

/* Badge Pills */
.badge-premium-pill {
    padding: 4px 10px;
    border-radius: 50px;
    font-weight: 800;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    letter-spacing: 0.02em;
}
.badge-wfo { background: #ECFDF3; color: #027A48; border: 1px solid #D1FADF; }
.badge-wfh { background: #EFF8FF; color: #175CD3; border: 1px solid #D1E9FF; }
.badge-gross-pill {
    background: #FEF7C3;
    color: #B54708;
    border: 1px solid #FEF08A;
    font-weight: 800;
    font-size: 11.5px;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.structured-task-item {
    font-size: 11.5px;
    line-height: 1.45;
    margin-bottom: 3px;
}
/* Filter Card Redesign */
.rep-filter-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 18px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
    margin-bottom: 24px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.rep-filter-card:hover {
    border-color: #CBD5E1;
    box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.08);
}

.filter-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: #F8FAFC;
    border-bottom: 1px solid #EDF2F7;
    flex-wrap: wrap;
    gap: 10px;
}

.filter-header-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    font-weight: 700;
    color: #1E293B;
    margin: 0;
}

.filter-header-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(75, 0, 232, 0.08);
    color: var(--orb-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.filter-quick-dates {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.quick-date-btn {
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    color: #475569;
    font-size: 11.5px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.quick-date-btn:hover {
    background: #EEF2FF;
    border-color: #C7D2FE;
    color: var(--orb-primary);
    transform: translateY(-1px);
}

.quick-date-btn.active {
    background: var(--orb-primary);
    border-color: var(--orb-primary);
    color: #FFFFFF;
}

.filter-card-body {
    padding: 20px;
}

.filter-grid {
    display: grid;
    grid-template-columns: minmax(170px, 1.2fr) minmax(220px, 1.8fr) minmax(200px, 1.6fr) auto;
    gap: 16px;
    align-items: flex-end;
}

@media (max-width: 992px) {
    .filter-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 576px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
}

.filter-field {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 11px;
    font-weight: 800;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.filter-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.filter-input-icon {
    position: absolute;
    left: 14px;
    color: #94A3B8;
    font-size: 13px;
    pointer-events: none;
    z-index: 2;
    transition: color 0.2s ease;
}

.filter-control {
    width: 100%;
    height: 42px;
    border-radius: 10px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    padding: 8px 14px 8px 38px;
    font-size: 13px;
    font-weight: 600;
    color: #0F172A;
    outline: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    appearance: none;
    -webkit-appearance: none;
}

select.filter-control {
    padding-right: 36px;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748B' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px 12px;
    cursor: pointer;
}

.filter-control:focus {
    border-color: var(--orb-primary);
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12);
    background-color: #FFFFFF;
}

/* Select2 integration within filter input wrapper */
.filter-input-wrapper .select2-container {
    width: 100% !important;
}

.filter-input-wrapper .select2-container .select2-selection--single {
    height: 42px !important;
    border-radius: 10px !important;
    border: 1px solid #CBD5E1 !important;
    background: #FFFFFF !important;
    padding-left: 38px !important;
    display: flex !important;
    align-items: center !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.filter-input-wrapper .select2-container--open .select2-selection--single,
.filter-input-wrapper .select2-container--focus .select2-selection--single {
    border-color: var(--orb-primary) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
}

.filter-input-wrapper .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #0F172A !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    padding-left: 0 !important;
    line-height: 40px !important;
}

.filter-input-wrapper .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #94A3B8 !important;
    font-weight: 500 !important;
}

.filter-input-wrapper .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 10px !important;
}

.filter-input-wrapper:focus-within .filter-input-icon {
    color: var(--orb-primary);
}

.filter-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-filter-apply {
    height: 42px;
    padding: 0 20px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--orb-primary) 0%, #6319ED 100%);
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 800;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25);
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
}

.btn-filter-apply:hover {
    background: linear-gradient(135deg, #3D00C2 0%, #520DD6 100%);
    color: #FFFFFF;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(75, 0, 232, 0.35);
}

.btn-filter-reset {
    height: 42px;
    padding: 0 16px;
    border-radius: 10px;
    background: #F1F5F9;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    border: 1px solid #E2E8F0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
    white-space: nowrap;
}

.btn-filter-reset:hover {
    background: #E2E8F0;
    color: #0F172A;
    text-decoration: none;
    transform: translateY(-1px);
}

/* Active Filter Badges Ribbon */
.active-filters-ribbon {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px dashed #E2E8F0;
}

.active-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #EEF2FF;
    border: 1px solid #C7D2FE;
    color: var(--orb-primary);
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

.active-filter-tag .tag-remove {
    color: #818CF8;
    cursor: pointer;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    transition: color 0.15s ease;
}

.active-filter-tag .tag-remove:hover {
    color: #EF4444;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <div class="rep-hero" style="padding: 20px 24px;">
            <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-file-signature mr-2"></i>Reporting Employee Work Reports</h3>
            <p class="mb-0 opacity-90 small">Daily work summaries submitted by your reporting employees upon punch-out.</p>
        </div>

        <!-- Filter Card Redesign -->
        @php
            $todayDate = \Carbon\Carbon::today()->toDateString();
            $yesterdayDate = \Carbon\Carbon::yesterday()->toDateString();
            $currentDate = request('date');
            $currentEmpId = request('employee_id');
            $currentProjId = request('project_id');
            
            $hasActiveFilters = !empty($currentDate) || !empty($currentEmpId) || !empty($currentProjId);
            $activeCount = (!empty($currentDate) ? 1 : 0) + (!empty($currentEmpId) ? 1 : 0) + (!empty($currentProjId) ? 1 : 0);
            
            $selectedEmp = !empty($currentEmpId) ? $teamEmployees->firstWhere('id', $currentEmpId) : null;
            $selectedProj = !empty($currentProjId) ? $teamProjects->firstWhere('id', $currentProjId) : null;
        @endphp

        <div class="rep-filter-card">
            <div class="filter-card-header">
                <div class="filter-header-title">
                    <span class="filter-header-icon"><i class="fas fa-sliders-h"></i></span>
                    <span>Filter & Search Reports</span>
                    @if($hasActiveFilters)
                        <span class="badge badge-pill badge-primary ml-1" style="background: var(--orb-primary); font-size: 11px; padding: 4px 9px;">
                            {{ $activeCount }} Active {{ Str::plural('Filter', $activeCount) }}
                        </span>
                    @endif
                </div>

                <div class="filter-quick-dates">
                    <span class="text-muted small font-weight-bold mr-1 d-none d-sm-inline" style="font-size: 11px;">Quick Date:</span>
                    <button type="button" class="quick-date-btn {{ $currentDate === $todayDate ? 'active' : '' }}" onclick="applyQuickDate('{{ $todayDate }}')">
                        <i class="far fa-calendar-check"></i> Today
                    </button>
                    <button type="button" class="quick-date-btn {{ $currentDate === $yesterdayDate ? 'active' : '' }}" onclick="applyQuickDate('{{ $yesterdayDate }}')">
                        <i class="far fa-calendar-minus"></i> Yesterday
                    </button>
                    @if($currentDate)
                        <button type="button" class="quick-date-btn text-danger" onclick="applyQuickDate('')" title="Clear Date Filter">
                            <i class="fas fa-times"></i> Clear Date
                        </button>
                    @endif
                </div>
            </div>

            <div class="filter-card-body">
                <form method="GET" action="{{ route('reporting.work_reports') }}" id="workReportFilterForm">
                    <div class="filter-grid">
                        <!-- Date Input -->
                        <div class="filter-field">
                            <label class="filter-label" for="filterDateInput">
                                <i class="far fa-calendar-alt text-muted"></i> Work Date
                            </label>
                            <div class="filter-input-wrapper">
                                <i class="fas fa-calendar-day filter-input-icon"></i>
                                <input type="date" name="date" id="filterDateInput" class="filter-control" value="{{ $currentDate }}">
                            </div>
                        </div>

                        <!-- Reporting Employee (Searchable with Developer / QA / Team) -->
                        <div class="filter-field">
                            <label class="filter-label" for="filterEmployeeSelect">
                                <i class="far fa-user text-muted"></i> Employee (Developers & QA)
                            </label>
                            <div class="filter-input-wrapper">
                                <i class="fas fa-user-tie filter-input-icon"></i>
                                <select name="employee_id" id="filterEmployeeSelect" class="filter-control select2-searchable">
                                    <option value="">-- All Developers, QA & Team ({{ $teamEmployees->count() }}) --</option>
                                    @foreach($teamEmployees as $emp)
                                        <option value="{{ $emp->id }}" {{ $currentEmpId == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->display_name }} ({{ $emp->employee_code }}){{ $emp->designation ? ' • ' . $emp->designation->name : ($emp->department ? ' • ' . $emp->department->name : '') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Project (Searchable) -->
                        <div class="filter-field">
                            <label class="filter-label" for="filterProjectSelect">
                                <i class="far fa-folder text-muted"></i> Project
                            </label>
                            <div class="filter-input-wrapper">
                                <i class="fas fa-project-diagram filter-input-icon"></i>
                                <select name="project_id" id="filterProjectSelect" class="filter-control select2-searchable">
                                    <option value="">-- All Projects ({{ $teamProjects->count() }}) --</option>
                                    @foreach($teamProjects as $prj)
                                        <option value="{{ $prj->id }}" {{ $currentProjId == $prj->id ? 'selected' : '' }}>
                                            {{ $prj->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="filter-actions">
                            <button type="submit" class="btn-filter-apply">
                                <i class="fas fa-filter"></i>
                                <span>Apply</span>
                            </button>
                            @if($hasActiveFilters)
                                <a href="{{ route('reporting.work_reports') }}" class="btn-filter-reset" title="Reset all filters">
                                    <i class="fas fa-undo-alt"></i>
                                    <span>Reset</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                @if($hasActiveFilters)
                    <div class="active-filters-ribbon">
                        <span class="small font-weight-bold text-muted" style="font-size: 11px;">Active Filters:</span>
                        @if($currentDate)
                            <div class="active-filter-tag">
                                <span><i class="far fa-calendar-alt mr-1"></i> Date: <strong>{{ \Carbon\Carbon::parse($currentDate)->format('d M Y') }}</strong></span>
                                <a href="{{ route('reporting.work_reports', request()->except('date', 'page')) }}" class="tag-remove" title="Remove Date Filter"><i class="fas fa-times"></i></a>
                            </div>
                        @endif

                        @if($selectedEmp)
                            <div class="active-filter-tag">
                                <span><i class="far fa-user mr-1"></i> Employee: <strong>{{ $selectedEmp->display_name }}</strong></span>
                                <a href="{{ route('reporting.work_reports', request()->except('employee_id', 'page')) }}" class="tag-remove" title="Remove Employee Filter"><i class="fas fa-times"></i></a>
                            </div>
                        @endif

                        @if($selectedProj)
                            <div class="active-filter-tag">
                                <span><i class="far fa-folder mr-1"></i> Project: <strong>{{ $selectedProj->name }}</strong></span>
                                <a href="{{ route('reporting.work_reports', request()->except('project_id', 'page')) }}" class="tag-remove" title="Remove Project Filter"><i class="fas fa-times"></i></a>
                            </div>
                        @endif

                        <a href="{{ route('reporting.work_reports') }}" class="text-danger font-weight-bold ml-2" style="font-size: 11.5px; text-decoration: underline;">
                            Clear All
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="rep-card">
            <!-- Toolbar for Entries Length & Export Buttons -->
            <div class="orb-table-toolbar d-flex align-items-center justify-content-between p-3 border-bottom">
                <div class="toolbar-left"></div>
                <div class="toolbar-right d-flex align-items-center"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="reportingWorkReportsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 45px;">#</th>
                            <th class="py-3 px-3" style="min-width: 170px;">Employee</th>
                            <th class="py-3 px-3" style="min-width: 110px;">Date</th>
                            <th class="py-3 px-2" style="min-width: 80px;">Mode</th>
                            <th class="py-3 px-3" style="min-width: 120px;">Shift Context</th>
                            <th class="py-3 px-3" style="min-width: 110px;">Gross Work</th>
                            <th class="py-3 px-3" style="min-width: 380px; width: 34%;">Work Summary Description</th>
                            <th class="py-3 px-3" style="min-width: 250px; width: 22%;">Structured Tasks</th>
                            <th class="py-3 text-right pr-4 no-export" style="width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workReports as $report)
                            @php
                                $row = formatWorkReportRow($report);
                                $modeBadgeClass = $row['mode'] === 'WFH' ? 'badge-wfh' : 'badge-wfo';

                                $logPayload = [
                                    'id' => $report->id,
                                    'work_log_id' => $report->id,
                                    'employee_name' => $row['employee_name'],
                                    'employee_code' => $row['employee_code'],
                                    'passport_photo_url' => resolveEmployeePassportPhoto($report),
                                    'employee_initial' => resolveEmployeeInitials($report),
                                    'department' => $row['department'],
                                    'designation' => $row['designation'],
                                    'work_date' => $row['date'],
                                    'shift_name' => $row['shift_context'],
                                    'attendance_status' => ($report->attendance_status ?? 'present') === 'absent' && ($report->is_lwp ?? false) ? '🔴 ABSENT' : ($report->attendance_status ?? 'present'),
                                    'is_lwp' => (bool) ($report->is_lwp ?? false),
                                    'title' => $row['title'] ?? 'Work Report',
                                    'description' => $row['summary_desc'],
                                    'status' => $row['status'],
                                    'work_mode' => $row['mode'],
                                    'submitted_time' => $row['submitted_time'],
                                    'projects' => [],
                                    'requirements' => array_map(fn($t) => ['text' => $t['text'], 'done' => $t['done']], $row['structured_tasks']),
                                    'issues' => [],
                                    'notes' => null,
                                ];
                            @endphp
                        <tr>
                            <td class="py-3 px-3 align-middle text-center font-weight-bold text-muted table-sr-no" style="font-size: 12px;" data-export="{{ $loop->iteration }}">
                                {{ $loop->iteration }}
                            </td>

                            <td class="py-3 px-3 align-middle" data-export="{{ $row['employee'] }}">
                                <div>
                                    <strong class="text-dark font-weight-bold d-block" style="line-height: 1.2; font-size: 13px;">{{ $row['employee_name'] }}</strong>
                                    <small class="text-muted font-weight-bold" style="font-size: 11px;">({{ $row['employee_code'] }})</small>
                                </div>
                            </td>

                            <td class="py-3 px-3 align-middle font-weight-bold text-dark" style="white-space: nowrap; font-size: 13px;" data-export="{{ $row['date'] }}" data-order="{{ $row['date_raw'] }}">
                                {{ $row['date'] }}
                                @if($row['day_name'])
                                    <div class="small text-muted font-weight-semibold" style="font-size: 11px;">{{ $row['day_name'] }}</div>
                                @endif
                            </td>

                            <td class="py-3 px-2 align-middle" data-export="{{ $row['mode'] }}">
                                <span class="badge-premium-pill {{ $modeBadgeClass }}">
                                    @if($row['mode'] === 'WFH')
                                        <i class="fas fa-laptop-house mr-1"></i> WFH
                                    @else
                                        <i class="fas fa-building mr-1"></i> WFO
                                    @endif
                                </span>
                            </td>

                            <td class="py-3 px-3 align-middle font-weight-bold text-dark" style="font-size: 12.5px;" data-export="{{ $row['shift_context'] }}">
                                {{ $row['shift_context'] }}
                            </td>

                            <td class="py-3 px-3 align-middle" data-export="{{ $row['gross_work'] }}">
                                <div class="badge-gross-pill" style="white-space: nowrap;">
                                    <i class="fas fa-stopwatch mr-1"></i> {{ $row['gross_work'] }}
                                </div>
                            </td>

                            <td class="py-3 px-3 align-middle" style="min-width: 350px;" data-export="{{ $row['summary_desc'] }}">
                                <div class="work-summary-full-text">
                                    @foreach($row['summary_paragraphs'] as $para)
                                        <p class="mb-2" style="line-height: 1.5; color: #1E293B; font-size: 12.5px; font-weight: 500;">
                                            {{ $para }}
                                        </p>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-3 px-3 align-middle" style="min-width: 240px;" data-export="{{ $row['structured_tasks_text'] }}">
                                <div class="structured-tasks-list">
                                    @foreach($row['structured_tasks'] as $tItem)
                                        <div class="structured-task-item {{ $tItem['done'] ? 'done' : 'pending' }}" style="line-height: 1.5; margin-bottom: 3px; font-size: 12px; font-weight: 600;">
                                            <span class="task-tag font-weight-bold {{ $tItem['done'] ? 'text-success' : 'text-warning' }}">{{ $tItem['done'] ? '[Done]' : '[Pending]' }}</span>
                                            <span class="text-dark">{{ $tItem['text'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-3 align-middle text-right pr-4 no-export" style="white-space: nowrap;">
                                <button type="button" class="btn btn-sm btn-light border p-2 font-weight-bold" style="border-radius: 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;" data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" onclick="parseAndOpenWorkReport(this)">
                                    <i class="fas fa-eye text-primary"></i> Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-file-signature fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Daily Work Reports Found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer for Pagination & Info (Populated by DataTables) -->
            <div class="orb-table-footer p-3 bg-light border-top d-flex align-items-center justify-content-between"></div>

            @if($workReports->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $workReports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@include('hrms.attendance.partials.work-report-modal')
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    window.applyQuickDate = function(dateVal) {
        var dateInput = document.getElementById('filterDateInput');
        if (dateInput) {
            dateInput.value = dateVal;
            document.getElementById('workReportFilterForm').submit();
        }
    };

    $(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('#filterEmployeeSelect').select2({
                placeholder: 'Search employee by name, code or designation...',
                allowClear: true,
                width: '100%'
            });

            $('#filterProjectSelect').select2({
                placeholder: 'Search or select project...',
                allowClear: true,
                width: '100%'
            });
        }

        $.fn.dataTable.ext.errMode = 'none';

        if ($('#reportingWorkReportsTable tbody tr td[colspan]').length > 0) {
            $('#reportingWorkReportsTable tbody').empty();
        }

        if ($.fn.DataTable.isDataTable('#reportingWorkReportsTable')) {
            $('#reportingWorkReportsTable').DataTable().destroy();
        }

        const exportFormatFn = {
            body: function ( data, row, column, node ) {
                if (node && node.hasAttribute('data-export')) {
                    return node.getAttribute('data-export');
                }
                if (typeof data === 'string') {
                    var temp = document.createElement("div");
                    temp.innerHTML = data;
                    return (temp.textContent || temp.innerText || "").trim();
                }
                return data;
            }
        };

        var table = $('#reportingWorkReportsTable').DataTable({
            pageLength: 25,
            order: [[2, 'desc']], // Sort by Date desc (Date is col 2, Sr No is col 0)
            ordering: true,
            searching: true, 
            paging: true,
            info: true,
            responsive: false,
            autoWidth: false,
            dom: "t<'d-none'ip>",
            language: {
                emptyTable: '<div class="text-center text-muted py-5"><i class="fas fa-file-alt fa-3x mb-3 text-muted"></i><h5 class="font-weight-bold text-dark">No Work Reports Found</h5></div>',
                zeroRecords: '<div class="text-center text-muted py-4"><i class="fas fa-search fa-2x mb-2 text-muted"></i><h6 class="font-weight-bold text-dark mb-0">No matching work reports found</h6></div>'
            },
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="far fa-file-excel text-success mr-1"></i> Excel',
                    className: 'orb-export-btn',
                    title: 'Daily Work Reports',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: exportFormatFn
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-primary mr-1"></i> CSV',
                    className: 'orb-export-btn',
                    title: 'Daily Work Reports',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: exportFormatFn
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="far fa-file-pdf text-danger mr-1"></i> PDF',
                    className: 'orb-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Daily Work Reports',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: exportFormatFn
                    },
                    customize: function(doc) {
                        doc.pageMargins = [18, 20, 18, 20];
                        doc.defaultStyle.fontSize = 8;
                        if (doc.styles.tableHeader) {
                            doc.styles.tableHeader.fontSize = 9;
                            doc.styles.tableHeader.fillColor = '#243746';
                            doc.styles.tableHeader.color = '#FFFFFF';
                            doc.styles.tableHeader.alignment = 'left';
                            doc.styles.tableHeader.bold = true;
                        }
                        if (doc.content && doc.content[1] && doc.content[1].table) {
                            doc.content[1].table.widths = ['4%', '14%', '9%', '6%', '10%', '10%', '29%', '18%'];
                        }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    className: 'orb-export-btn',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: exportFormatFn
                    },
                    customize: function(win) {
                        var $winBody = $(win.document.body);
                        $winBody.empty();

                        var printHtml = `
                            <div class="print-container">
                                <h1 class="print-title">Daily Work Reports</h1>
                                <table class="print-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 4%; text-align: center;">#</th>
                                            <th style="width: 14%;">Employee</th>
                                            <th style="width: 9%;">Date</th>
                                            <th style="width: 6%;">Mode</th>
                                            <th style="width: 10%;">Shift Context</th>
                                            <th style="width: 10%;">Gross Work</th>
                                            <th style="width: 29%;">Work Summary Description</th>
                                            <th style="width: 18%;">Structured Tasks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        var srNo = 1;
                        table.rows({ filter: 'applied' }).every(function() {
                            var $tr = $(this.node());
                            var emp = $tr.find('td:nth-child(2)').attr('data-export') || $tr.find('td:nth-child(2)').text().trim();
                            var date = $tr.find('td:nth-child(3)').attr('data-export') || $tr.find('td:nth-child(3)').text().trim();
                            var mode = $tr.find('td:nth-child(4)').attr('data-export') || $tr.find('td:nth-child(4)').text().trim();
                            var shift = $tr.find('td:nth-child(5)').attr('data-export') || $tr.find('td:nth-child(5)').text().trim();
                            var gross = $tr.find('td:nth-child(6)').attr('data-export') || $tr.find('td:nth-child(6)').text().trim();
                            var summary = $tr.find('td:nth-child(7)').attr('data-export') || $tr.find('td:nth-child(7)').text().trim();
                            var tasks = $tr.find('td:nth-child(8)').attr('data-export') || $tr.find('td:nth-child(8)').text().trim();

                            var summaryHtml = summary.split('\n\n').map(function(p) {
                                var esc = $('<div>').text(p).html();
                                return '<p style="margin: 0 0 8px 0; line-height: 1.45;">' + esc + '</p>';
                            }).join('');

                            var tasksHtml = tasks.split('\n').map(function(t) {
                                if (!t.trim()) return '';
                                var esc = $('<div>').text(t).html();
                                return '<div style="margin-bottom: 3px; line-height: 1.45;">' + esc + '</div>';
                            }).join('');

                            printHtml += `
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748B;">${srNo++}</td>
                                    <td style="font-weight: 500;">${$('<div>').text(emp).html()}</td>
                                    <td style="white-space: nowrap;">${$('<div>').text(date).html()}</td>
                                    <td style="font-weight: 500;">${$('<div>').text(mode).html()}</td>
                                    <td>${$('<div>').text(shift).html()}</td>
                                    <td style="white-space: nowrap;">${$('<div>').text(gross).html()}</td>
                                    <td>${summaryHtml}</td>
                                    <td>${tasksHtml}</td>
                                </tr>
                            `;
                        });

                        printHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;

                        var customStyles = `
                            @page {
                                size: landscape A4;
                                margin: 10mm 12mm;
                            }
                            * {
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                box-sizing: border-box;
                            }
                            body {
                                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
                                color: #111827 !important;
                                background: #ffffff !important;
                                padding: 0 !important;
                                margin: 0 !important;
                                font-size: 11px !important;
                            }
                            .print-container {
                                width: 100%;
                                padding: 10px 15px;
                            }
                            .print-title {
                                text-align: center;
                                font-size: 21px;
                                font-weight: 800;
                                color: #111827;
                                margin: 0 0 16px 0;
                                letter-spacing: -0.01em;
                            }
                            .print-table {
                                width: 100%;
                                border-collapse: collapse;
                                font-size: 11px;
                            }
                            .print-table thead th {
                                background-color: #243746 !important;
                                color: #ffffff !important;
                                font-weight: 700;
                                font-size: 11px;
                                padding: 8px 10px;
                                text-align: left;
                                border: 1px solid #243746;
                                vertical-align: middle;
                            }
                            .print-table tbody tr {
                                page-break-inside: avoid;
                                break-inside: avoid;
                            }
                            .print-table tbody tr:nth-child(odd) td {
                                background-color: #F2F4F8 !important;
                            }
                            .print-table tbody tr:nth-child(even) td {
                                background-color: #FFFFFF !important;
                            }
                            .print-table tbody td {
                                padding: 8px 10px;
                                border: 1px solid #E2E8F0;
                                vertical-align: top;
                                color: #1E293B;
                                font-size: 11px;
                                line-height: 1.45;
                            }
                        `;

                        var styleElem = win.document.createElement('style');
                        styleElem.type = 'text/css';
                        styleElem.innerHTML = customStyles;
                        win.document.head.appendChild(styleElem);

                        $winBody.html(printHtml);
                    }
                }
            ],
            language: {
                emptyTable: 'No daily work reports found.',
                zeroRecords: 'No matching work reports found.',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ reports',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        // Dynamic sequential numbering for Sr. No. across pagination, filtering & sorting
        table.on('draw.dt', function () {
            var info = table.page.info();
            table.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
                var num = i + 1 + info.start;
                cell.innerHTML = num;
                cell.setAttribute('data-export', num);
            });
        });

        // Inject the entries dropdown on the left, and print/export buttons on the right
        $('.orb-table-toolbar .toolbar-left').html(`
            <div class="dataTables_length">
                <label>Show 
                    <select class="form-control" id="custom-length-select">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="-1">All</option>
                    </select> entries
                </label>
            </div>
        `);
        
        $('.orb-table-toolbar .toolbar-right').append(table.buttons().container());

        $('#custom-length-select').on('change', function() {
            table.page.len($(this).val()).draw();
        });
    });
</script>
@endsection
