@extends('layouts.panel', ['active' => 'team_my_team'])

@section('page_title', 'My Team Management')

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
    max-width: 1600px;
    margin: 0 auto;
}

/* Signature Hero Header Banner */
.rep-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 22px 26px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.rep-hero h3 {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 4px 0;
    color: #ffffff;
}

.rep-hero p {
    font-size: 13px;
    opacity: 0.92;
    margin: 0;
}

/* Rich Metric Summary Cards Grid */
.team-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}

.team-stat-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 16px 18px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.team-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.team-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.team-stat-val {
    font-size: 22px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.1;
}

.team-stat-label {
    font-size: 10.5px;
    font-weight: 800;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 2px;
}

/* Main Table Container Card */
.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

/* Primary Filter Bar */
.filter-bar-primary {
    padding: 14px 18px;
    background: #FAFAFA;
    border-bottom: 1px solid #E2E8F0;
}

.filter-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.filter-controls-left {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    flex: 1;
}

.filter-control-sm {
    height: 36px;
    border-radius: 9px;
    font-size: 12.5px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    padding: 4px 10px;
    outline: none;
    transition: all 0.2s;
}

.filter-control-sm:focus {
    border-color: var(--orb-primary);
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
}

/* Collapsible Secondary Filters */
.more-filters-box {
    background: #F1F5F9;
    padding: 14px 18px;
    border-bottom: 1px solid #E2E8F0;
    display: none;
}

/* DataTables Toolbar */
.orb-table-toolbar {
    background: #FFFFFF;
    border-bottom: 1px solid #E2E8F0;
    padding: 10px 18px;
}

.dataTables_length,
.dataTables_length label {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    white-space: nowrap !important;
    margin: 0 !important;
    font-weight: 600 !important;
    font-size: 12.5px !important;
    color: #475467 !important;
}

.dataTables_length select {
    width: 68px !important;
    height: 32px !important;
    padding: 2px 8px !important;
    border-radius: 8px !important;
    border: 1px solid #CBD5E1 !important;
    outline: none !important;
}

.orb-export-btn {
    height: 32px !important;
    padding: 0 10px !important;
    border-radius: 8px !important;
    background: #fff !important;
    border: 1px solid #E7EAF3 !important;
    font-size: 11.5px !important;
    font-weight: 800 !important;
    margin-left: 5px !important;
    transition: all 0.2s ease !important;
    color: #475467 !important;
}

.orb-export-btn:hover {
    background: #F1F5F9 !important;
    color: var(--orb-primary) !important;
    border-color: rgba(75, 0, 232, 0.2) !important;
}

/* Sticky Table Header */
#myTeamTable thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #F8FAFC !important;
    color: #475569 !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.35px !important;
    border-bottom: 2px solid #E2E8F0 !important;
    white-space: nowrap !important;
    padding: 11px 14px !important;
}

#myTeamTable tbody td {
    padding: 11px 14px !important;
    border-bottom: 1px solid #F1F5F8 !important;
    vertical-align: middle !important;
    font-size: 12.5px !important;
}

#myTeamTable tbody tr:hover {
    background: #F8FAFC !important;
}

/* 3-Dot Action Button */
.btn-action-dots {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #F1F5F9;
    color: #475569;
    border: 1px solid #CBD5E1;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-action-dots:hover,
.btn-action-dots:focus {
    background: #EEF2FF;
    color: var(--orb-primary);
    border-color: #C7D2FE;
}

.dropdown-menu-action {
    min-width: 165px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
    border: 1px solid #E2E8F0;
    padding: 6px;
}

.dropdown-menu-action .dropdown-item {
    font-size: 12px;
    font-weight: 600;
    padding: 7px 12px;
    border-radius: 8px;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dropdown-menu-action .dropdown-item:hover {
    background: #EEF2FF;
    color: var(--orb-primary);
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header Banner -->
        <div class="rep-hero">
            <div>
                <h3 class="text-white font-weight-bold mb-1"><i class="fas fa-users mr-2"></i>My Team Management</h3>
                <p class="mb-0 opacity-90 small">Unified operational workspace for team members under your supervision (Project Team & Reporting Scope).</p>
            </div>
        </div>

        <!-- Rich Summary Cards Grid -->
        <div class="team-stats-grid">
            <!-- Total Team Members -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="team-stat-label">Total Members</div>
                    <div class="team-stat-val">{{ $summaryStats['total'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Reporting Team -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #E0F2FE; color: #0284C7; border: 1px solid #BAE6FD;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <div class="team-stat-label">Reporting Team</div>
                    <div class="team-stat-val">{{ $summaryStats['reporting_team'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Project Team -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div>
                    <div class="team-stat-label">Project Team</div>
                    <div class="team-stat-val">{{ $summaryStats['project_team'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Both Scope -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #F3E8FF; color: #7E22CE; border: 1px solid #D8B4FE;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <div class="team-stat-label">Both Scope</div>
                    <div class="team-stat-val">{{ $summaryStats['both'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Paid Employees -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="team-stat-label">Paid Employees</div>
                    <div class="team-stat-val">{{ $summaryStats['paid'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Unpaid Employees -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div>
                    <div class="team-stat-label">Unpaid Employees</div>
                    <div class="team-stat-val">{{ $summaryStats['unpaid'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Interns -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div class="team-stat-label">Interns</div>
                    <div class="team-stat-val">{{ $summaryStats['interns'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="rep-card">
            <!-- Card Header Title -->
            <div class="d-flex align-items-center justify-content-between border-bottom bg-white flex-wrap" style="padding: 14px 20px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 9px; background: #EEF2FF; color: #4F46E5; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="fas fa-id-badge"></i>
                    </span>
                    <div>
                        <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">My Team Members</h5>
                    </div>
                </div>
            </div>

            <!-- Instant Live Filter Toolbar -->
            <div class="filter-bar-primary">
                <div class="filter-row">
                    <div class="filter-controls-left">
                        <!-- Search Input -->
                        <input type="text" id="filter-search-keyword" class="filter-control-sm" placeholder="Search Employee (Name, ID, Project...)" style="min-width: 220px;">

                        <!-- Team Source -->
                        <select id="filter-team-source" class="filter-control-sm" style="min-width: 140px;">
                            <option value="">Team Source</option>
                            <option value="Reporting Team">Reporting</option>
                            <option value="Project Team">Project</option>
                            <option value="Both">Both</option>
                        </select>

                        <!-- Combined Employment Filter -->
                        <select id="filter-employment-combo" class="filter-control-sm" style="min-width: 140px;">
                            <option value="">Employment</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Probation">Probation</option>
                            <option value="Internship">Internship</option>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                        </select>

                        <!-- Department -->
                        <select id="filter-department" class="filter-control-sm" style="min-width: 150px;">
                            <option value="">Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>

                        <!-- Project -->
                        <select id="filter-project" class="filter-control-sm" style="min-width: 140px;">
                            <option value="">Project</option>
                            @foreach($projects as $prj)
                                <option value="{{ $prj->name }}">{{ $prj->name }}</option>
                            @endforeach
                        </select>

                        <!-- Toggle More Filters -->
                        <button type="button" class="btn btn-sm btn-light border font-weight-bold" id="btn-toggle-more-filters" style="height: 36px; border-radius: 9px; font-size: 12px; color: #475467;">
                            <i class="fas fa-sliders-h mr-1 text-muted"></i> More Filters
                        </button>
                    </div>

                    <!-- Reset Button aligned on the right -->
                    <div>
                        <button type="button" class="btn btn-sm btn-light border font-weight-bold" id="btn-reset-my-team-filters" style="height: 36px; border-radius: 9px; font-size: 12px; color: #475467; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-undo text-muted" style="font-size: 11px;"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- More Filters Collapsible Section -->
            <div class="more-filters-box" id="more-filters-collapse">
                <div class="row gap-2">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="small font-weight-bold text-muted uppercase mb-1" style="font-size: 10.5px;">Designation</label>
                        <select id="filter-designation" class="filter-control-sm w-100">
                            <option value="">All Designations</option>
                            @foreach($designations as $desig)
                                <option value="{{ $desig->name }}">{{ $desig->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="small font-weight-bold text-muted uppercase mb-1" style="font-size: 10.5px;">Reporting Manager</label>
                        <select id="filter-manager" class="filter-control-sm w-100">
                            <option value="">All Managers</option>
                            @foreach($reportingManagers as $mgr)
                                <option value="{{ $mgr->display_name ?? optional($mgr->user)->name }}">{{ $mgr->display_name ?? optional($mgr->user)->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="small font-weight-bold text-muted uppercase mb-1" style="font-size: 10.5px;">Employment Stage</label>
                        <select id="filter-emp-stage" class="filter-control-sm w-100">
                            <option value="">All Stages</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Probation">Probation</option>
                            <option value="Internship">Internship</option>
                            <option value="Contract">Contract</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="small font-weight-bold text-muted uppercase mb-1" style="font-size: 10.5px;">Employment Type</label>
                        <select id="filter-emp-type" class="filter-control-sm w-100">
                            <option value="">All Pay Types</option>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Toolbar for Entries & Export Buttons -->
            <div class="orb-table-toolbar d-flex align-items-center justify-content-between">
                <div class="toolbar-left"></div>
                <div class="toolbar-right d-flex align-items-center"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="myTeamTable">
                    <thead>
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 45px;">#</th>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">Organization</th>
                            <th class="py-3 text-center">Employment</th>
                            <th class="py-3">Reporting</th>
                            <th class="py-3 text-center">Team</th>
                            <th class="py-3">Projects & Teams</th>
                            <th class="py-3 text-center" style="width: 60px;">⋮</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            @php
                                $projectAssignments = $emp->project_assignments_list ?? [];
                                $reportingManager = $emp->reportingManager ?? null;

                                $displayName = $emp->display_name ?? (optional($emp->user ?? null)->name ?? 'Employee');
                                $empCode = $emp->employee_code ?? 'N/A';
                                $empExportText = $displayName . ' (' . $empCode . ')';

                                // Employment Stage & Type Determination
                                $stageRaw = strtolower($emp->employee_stage ?? '');
                                $typeRaw = strtolower($emp->employment_type ?? '');
                                $isPaidIntern = $emp->is_paid_intern ?? null;
                                $isIntern = ($stageRaw === 'internship' || $typeRaw === 'intern');

                                $stageLabel = 'Permanent';
                                $stageBadgeCss = 'background: #DEF7EC; color: #03543F; border: 1px solid #84E1BC;';
                                $stageIcon = 'fas fa-user-check';

                                $payLabel = 'Paid';
                                $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                $payIcon = 'fas fa-rupee-sign';

                                if ($isIntern) {
                                    $stageLabel = 'Internship';
                                    $stageBadgeCss = 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;';
                                    $stageIcon = 'fas fa-user-graduate';

                                    if ($isPaidIntern === 1 || $isPaidIntern === true) {
                                        $payLabel = 'Paid';
                                        $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                        $payIcon = 'fas fa-rupee-sign';
                                    } else {
                                        $payLabel = 'Unpaid';
                                        $payBadgeCss = 'background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;';
                                        $payIcon = 'fas fa-hand-holding-usd';
                                    }
                                } elseif ($stageRaw === 'probation' || ($emp->probation_status && in_array($emp->probation_status, ['pending', 'ongoing']))) {
                                    $stageLabel = 'Probation';
                                    $stageBadgeCss = 'background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;';
                                    $stageIcon = 'fas fa-user-clock';
                                    $payLabel = 'Paid';
                                    $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                } elseif ($stageRaw === 'contract' || $typeRaw === 'contract') {
                                    $stageLabel = 'Contract';
                                    $stageBadgeCss = 'background: #E0F2FE; color: #075985; border: 1px solid #7DD3FC;';
                                    $stageIcon = 'fas fa-file-contract';
                                    $payLabel = 'Paid';
                                    $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                } elseif ($stageRaw === 'freelance' || $typeRaw === 'freelancer') {
                                    $stageLabel = 'Freelance';
                                    $stageBadgeCss = 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;';
                                    $stageIcon = 'fas fa-laptop-code';
                                    $payLabel = 'Paid';
                                    $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                } else {
                                    $stageLabel = ucfirst($stageRaw ?: ($typeRaw ?: 'Permanent'));
                                    $stageBadgeCss = 'background: #DEF7EC; color: #03543F; border: 1px solid #84E1BC;';
                                    $stageIcon = 'fas fa-user-check';
                                    $payLabel = 'Paid';
                                    $payBadgeCss = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                }

                                $teamSourceStr = match($emp->team_source ?? 'Reporting Team') {
                                    'Both' => 'Both',
                                    'Reporting Team' => 'Reporting',
                                    default => 'Project'
                                };
                                $teamSourceBadge = match($teamSourceStr) {
                                    'Both' => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
                                    'Reporting' => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;',
                                    default => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;'
                                };

                                $deptName = optional($emp->department ?? null)->name ?? 'General';
                                $desigName = optional($emp->designation ?? null)->name ?? 'Staff';
                                $mgrName = $reportingManager ? ($reportingManager->display_name ?? 'Manager') : 'Not Assigned';
                                $exportEmployment = $stageLabel . ' (' . $payLabel . ')' . ($isIntern && isset($emp->internship_period) && $emp->internship_period !== '—' ? ' ' . $emp->internship_period : '');
                            @endphp
                        <tr>
                            <!-- 1. S.No. -->
                            <td class="py-3 px-3 align-middle text-center font-weight-bold text-muted" style="font-size: 12px;" data-export="{{ $loop->iteration }}">
                                {{ $loop->iteration }}
                            </td>

                            <!-- 2. Employee Column -->
                            <td class="py-3 px-4 align-middle" data-export="{{ $empExportText }}">
                                <div>
                                    @if(Route::has('employees.show'))
                                        <a href="{{ route('employees.show', $emp->id) }}" class="text-dark font-weight-bold d-block text-hover-primary" style="line-height: 1.25; font-size: 13px;">
                                            {{ $displayName }}
                                        </a>
                                    @else
                                        <strong class="text-dark font-weight-bold d-block" style="line-height: 1.25; font-size: 13px;">{{ $displayName }}</strong>
                                    @endif
                                    <small class="text-muted font-weight-bold" style="font-size: 10.5px;">{{ $empCode }}</small>
                                </div>
                            </td>

                            <!-- 3. Organization Column -->
                            <td class="py-3 align-middle" data-export="{{ $deptName }} - {{ $desigName }}">
                                <div>
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 12.5px; line-height: 1.25;">
                                        {{ $deptName }}
                                    </span>
                                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600;">{{ $desigName }}</small>
                                </div>
                            </td>

                            <!-- 4. Consolidated Employment Column -->
                            <td class="py-3 align-middle text-center" data-export="{{ $exportEmployment }}">
                                <div class="d-flex flex-column align-items-center justify-content-center" style="gap: 4px;">
                                    <div class="d-flex align-items-center gap-1" style="gap: 4px;">
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10px; {{ $stageBadgeCss }}">
                                            <i class="{{ $stageIcon }} mr-1"></i> {{ $stageLabel }}
                                        </span>
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; {{ $payBadgeCss }}">
                                            {{ $payLabel }}
                                        </span>
                                    </div>
                                    @if($isIntern && isset($emp->internship_period) && $emp->internship_period !== '—')
                                        <small class="text-muted font-weight-bold" style="font-size: 10px; white-space: nowrap;">
                                            {{ $emp->internship_period }}
                                        </small>
                                    @endif
                                </div>
                            </td>

                            <!-- 5. Reporting Column -->
                            <td class="py-3 align-middle" data-export="{{ $mgrName }}">
                                @if($reportingManager)
                                    <div>
                                        <strong class="text-dark font-weight-bold d-block" style="font-size: 12px; line-height: 1.2;">
                                            {{ $reportingManager->display_name ?? 'Manager' }}
                                        </strong>
                                        <small class="text-muted" style="font-size: 10px;">{{ $reportingManager->employee_code ?? '' }}</small>
                                    </div>
                                @else
                                    <span class="small text-muted font-weight-bold">Not Assigned</span>
                                @endif
                            </td>

                            <!-- 6. Team Column -->
                            <td class="py-3 align-middle text-center" data-export="{{ $teamSourceStr }}">
                                <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10px; {{ $teamSourceBadge }}">
                                    {{ $teamSourceStr }}
                                </span>
                            </td>

                            <!-- 7. Projects & Teams Column -->
                            <td class="py-3 align-middle">
                                @php
                                    $firstPrj = $projectAssignments->first();
                                    $remainingCount = count($projectAssignments) - 1;
                                @endphp
                                @if($firstPrj)
                                    <div class="mb-0">
                                        <span class="font-weight-bold text-primary d-block" style="font-size: 12px; line-height: 1.2;">
                                            <i class="fas fa-folder text-primary mr-1" style="font-size: 10px;"></i>{{ $firstPrj->project_name }}
                                        </span>
                                        <small class="text-muted" style="font-size: 10.5px;">
                                            @if($firstPrj->team_name)<span class="badge badge-light border" style="font-size: 9.5px;">{{ $firstPrj->team_name }}</span>@endif
                                            @if($firstPrj->role_name)<span class="text-info font-weight-bold ml-1">({{ $firstPrj->role_name }})</span>@endif
                                        </small>
                                        @if($remainingCount > 0)
                                            <span class="badge badge-primary font-weight-bold ml-1" style="border-radius: 6px; font-size: 9px;" title="{{ $projectAssignments->pluck('project_name')->join(', ') }}">
                                                +{{ $remainingCount }} more
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="small text-muted font-weight-bold">No Active Projects</span>
                                @endif
                            </td>

                            <!-- 8. Actions Three-Dot Column (⋮) -->
                            <td class="py-3 align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn-action-dots" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-action">
                                        @if(Route::has('employees.show'))
                                            <a class="dropdown-item" href="{{ route('employees.show', $emp->id) }}">
                                                <i class="fas fa-user-circle text-primary"></i> View Profile
                                            </a>
                                        @endif
                                        @if(Route::has('reporting.assignments'))
                                            <a class="dropdown-item" href="{{ route('reporting.assignments', ['search' => $emp->employee_code]) }}">
                                                <i class="fas fa-users-cog text-info"></i> View Team Details
                                            </a>
                                        @endif
                                        @if(Route::has('projects.my'))
                                            <a class="dropdown-item" href="{{ route('projects.my') }}">
                                                <i class="fas fa-project-diagram text-success"></i> View Projects
                                            </a>
                                        @elseif(Route::has('projects.index'))
                                            <a class="dropdown-item" href="{{ route('projects.index') }}">
                                                <i class="fas fa-project-diagram text-success"></i> View Projects
                                            </a>
                                        @endif
                                        @if(Route::has('project_management.tasks.my'))
                                            <a class="dropdown-item" href="{{ route('project_management.tasks.my') }}">
                                                <i class="fas fa-tasks text-warning"></i> View Tasks
                                            </a>
                                        @elseif(Route::has('projects.tasks.index'))
                                            <a class="dropdown-item" href="{{ route('projects.tasks.index') }}">
                                                <i class="fas fa-tasks text-warning"></i> View Tasks
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-users-slash fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Team Members Found</h5>
                                <p class="small mb-0">No employees match the selected filters.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer for Pagination & Info -->
            <div class="orb-table-footer p-3 bg-light border-top d-flex align-items-center justify-content-between"></div>

            @if($employees->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
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
    $(function() {
        $.fn.dataTable.ext.errMode = 'none';

        if ($('#myTeamTable tbody tr td[colspan]').length > 0) {
            $('#myTeamTable tbody').empty();
        }

        if ($.fn.DataTable.isDataTable('#myTeamTable')) {
            $('#myTeamTable').DataTable().destroy();
        }

        const exportOptionsDefault = {
            columns: [0, 1, 2, 3, 4, 5, 6],
            format: {
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
            }
        };

        var table = $('#myTeamTable').DataTable({
            pageLength: 25,
            ordering: false,
            searching: true, 
            paging: true,
            info: true,
            responsive: false,
            autoWidth: false,
            dom: "t<'d-none'ip>",
            language: {
                emptyTable: '<div class="text-center text-muted py-5"><i class="fas fa-users-slash fa-3x mb-3 text-muted"></i><h5 class="font-weight-bold text-dark">No Team Members Found</h5><p class="small mb-0">No employees match the selected filters.</p></div>',
                zeroRecords: '<div class="text-center text-muted py-4"><i class="fas fa-search fa-2x mb-2 text-muted"></i><h6 class="font-weight-bold text-dark mb-0">No matching team members found</h6></div>'
            },
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-info"></i> CSV',
                    className: 'orb-export-btn',
                    exportOptions: exportOptionsDefault
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'orb-export-btn',
                    exportOptions: exportOptionsDefault
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'orb-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'OrboOne HRMS - My Team Directory',
                    exportOptions: exportOptionsDefault,
                    customize: function (doc) {
                        doc.pageOrientation = 'landscape';
                        doc.pageSize = 'A4';
                        doc.pageMargins = [15, 45, 15, 35];

                        doc['header'] = function(currentPage, pageCount) {
                            return {
                                margin: [15, 15, 15, 0],
                                columns: [
                                    {
                                        text: 'ORBOONE HRMS — MY TEAM DIRECTORY',
                                        fontSize: 9,
                                        bold: true,
                                        color: '#4B00E8'
                                    },
                                    {
                                        text: 'Page ' + currentPage.toString() + ' of ' + pageCount,
                                        alignment: 'right',
                                        fontSize: 9,
                                        color: '#64748B'
                                    }
                                ]
                            };
                        };

                        var objLayout = {};
                        objLayout['hLineWidth'] = function(i) { return 0.5; };
                        objLayout['vLineWidth'] = function(i) { return 0; };
                        objLayout['hLineColor'] = function(i) { return '#CBD5E1'; };
                        objLayout['paddingLeft'] = function(i) { return 6; };
                        objLayout['paddingRight'] = function(i) { return 6; };
                        objLayout['paddingTop'] = function(i) { return 5; };
                        objLayout['paddingBottom'] = function(i) { return 5; };
                        doc.content[1].layout = objLayout;

                        var headerRow = doc.content[1].table.body[0];
                        for (var i = 0; i < headerRow.length; i++) {
                            headerRow[i].fillColor = '#1E293B';
                            headerRow[i].color = '#FFFFFF';
                            headerRow[i].fontSize = 9;
                            headerRow[i].bold = true;
                        }

                        doc.content[1].table.widths = ['5%', '18%', '16%', '15%', '14%', '10%', '15%'];
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'orb-export-btn',
                    title: '',
                    exportOptions: exportOptionsDefault,
                    customize: function (win) {
                        var body = $(win.document.body);

                        $(win.document.head).append(`
                            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
                            <style>
                                @media print {
                                    @page {
                                        size: A4 landscape;
                                        margin: 10mm 12mm;
                                    }
                                }
                                body {
                                    font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
                                    color: #0F172A !important;
                                    background: #FFFFFF !important;
                                    padding: 15px !important;
                                    margin: 0 !important;
                                }
                                .print-hero {
                                    background: linear-gradient(135deg, #4B00E8 0%, #FF5252 100%) !important;
                                    border-radius: 12px !important;
                                    padding: 16px 22px !important;
                                    color: #FFFFFF !important;
                                    margin-bottom: 20px !important;
                                    display: flex !important;
                                    align-items: center !important;
                                    justify-content: space-between !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .print-hero h2 {
                                    margin: 0 !important;
                                    font-size: 20px !important;
                                    font-weight: 800 !important;
                                    color: #FFFFFF !important;
                                }
                                .print-hero p {
                                    margin: 2px 0 0 0 !important;
                                    font-size: 12px !important;
                                    opacity: 0.92 !important;
                                    color: #FFFFFF !important;
                                }
                                table.dataTable {
                                    width: 100% !important;
                                    border-collapse: separate !important;
                                    border-spacing: 0 !important;
                                    border-radius: 10px !important;
                                    overflow: hidden !important;
                                    border: 1px solid #CBD5E1 !important;
                                    margin-top: 10px !important;
                                }
                                table.dataTable thead th {
                                    background: #1E293B !important;
                                    color: #FFFFFF !important;
                                    font-size: 11px !important;
                                    font-weight: 800 !important;
                                    text-transform: uppercase !important;
                                    padding: 10px 14px !important;
                                    border: none !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                table.dataTable tbody td {
                                    padding: 10px 14px !important;
                                    border-bottom: 1px solid #E2E8F0 !important;
                                    font-size: 11.5px !important;
                                }
                                table.dataTable tbody tr:nth-child(even) {
                                    background: #F8FAFC !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                            </style>
                        `);

                        body.find('h1').remove();

                        var printDate = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                        body.prepend(`
                            <div class="print-hero">
                                <div>
                                    <h2>OrboOne HRMS</h2>
                                    <p>Team Management — My Team Workspace</p>
                                </div>
                                <div style="background: rgba(255, 255, 255, 0.22); padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700;">
                                    Date: ${printDate}
                                </div>
                            </div>
                        `);
                    }
                }
            ],
            language: {
                emptyTable: 'No team members currently assigned.',
                zeroRecords: 'No matching team members found.',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ team members',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
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

        // Toggle More Filters Collapse
        $('#btn-toggle-more-filters').on('click', function() {
            $('#more-filters-collapse').slideToggle(200);
        });

        // Automatic Instant Live Filter Execution
        function applyInstantFilters() {
            var teamSourceVal = $('#filter-team-source').val();
            var empComboVal = $('#filter-employment-combo').val();
            var deptVal = $('#filter-department').val();
            var prjVal = $('#filter-project').val();
            
            var desigVal = $('#filter-designation').val();
            var mgrVal = $('#filter-manager').val();
            var empStageVal = $('#filter-emp-stage').val();
            var empTypeVal = $('#filter-emp-type').val();
            var searchVal = $('#filter-search-keyword').val();

            // col 5: Team (Reporting / Project / Both)
            table.column(5).search(teamSourceVal ? '^' + $.fn.dataTable.util.escapeRegex(teamSourceVal) : '', true, false);
            
            // col 3: Employment (Stage / Type / Combo)
            var empSearchTerm = empComboVal || empStageVal || empTypeVal || '';
            table.column(3).search(empSearchTerm ? $.fn.dataTable.util.escapeRegex(empSearchTerm) : '', true, false);

            // col 2: Organization (Department & Designation)
            var orgSearch = [];
            if (deptVal) orgSearch.push($.fn.dataTable.util.escapeRegex(deptVal));
            if (desigVal) orgSearch.push($.fn.dataTable.util.escapeRegex(desigVal));
            table.column(2).search(orgSearch.length > 0 ? orgSearch.join('.*') : '', true, false);

            // col 4: Reporting Manager
            table.column(4).search(mgrVal ? $.fn.dataTable.util.escapeRegex(mgrVal) : '', true, false);

            // col 6: Projects & Teams
            table.column(6).search(prjVal ? $.fn.dataTable.util.escapeRegex(prjVal) : '', true, false);

            // Global Search
            table.search(searchVal || '');

            table.draw();
        }

        // Real-Time Event Handlers for Automatic Filtering
        $('#filter-team-source, #filter-employment-combo, #filter-department, #filter-project, #filter-designation, #filter-manager, #filter-emp-stage, #filter-emp-type').on('change', function() {
            applyInstantFilters();
        });

        $('#filter-search-keyword').on('keyup change input clear', function() {
            applyInstantFilters();
        });

        // Reset Filters Handler
        $('#btn-reset-my-team-filters').on('click', function() {
            $('#filter-team-source').val('');
            $('#filter-employment-combo').val('');
            $('#filter-department').val('');
            $('#filter-project').val('');
            $('#filter-designation').val('');
            $('#filter-manager').val('');
            $('#filter-emp-stage').val('');
            $('#filter-emp-type').val('');
            $('#filter-search-keyword').val('');
            table.search('').columns().search('').draw();
        });
    });
</script>
@endsection
