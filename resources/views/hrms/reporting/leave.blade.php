@extends('layouts.panel', ['active' => 'team_leave'])

@section('page_title', 'Team Leave')

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
    padding: 22px 28px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
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

/* 5 Rich Metric Summary Cards Grid */
.team-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.team-stat-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 16px 20px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.team-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.team-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
}

.team-stat-val {
    font-size: 24px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.1;
}

.team-stat-label {
    font-size: 11px;
    font-weight: 800;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 3px;
}

/* Today's Status Banner Card */
.today-status-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 18px 22px;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
}

.today-status-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #1E293B;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.today-leave-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 50px;
    background: #FEF3C7;
    color: #92400E;
    border: 1px solid #FCD34D;
    font-size: 12px;
    font-weight: 700;
    transition: transform 0.15s ease;
}

.today-leave-pill:hover {
    transform: translateY(-1px);
}

/* Filter Card Styling */
.rep-filter-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 18px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
    margin-bottom: 24px;
    overflow: hidden;
}

.filter-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: #F8FAFC;
    border-bottom: 1px solid #EDF2F7;
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

.filter-card-body {
    padding: 18px 20px;
}

.filter-grid {
    display: grid;
    grid-template-columns: minmax(160px, 1.2fr) minmax(200px, 1.6fr) @if(!empty($reportingManagers) && count($reportingManagers) > 0) minmax(180px, 1.4fr) @endif minmax(140px, 1fr) minmax(180px, 1.4fr) auto;
    gap: 14px;
    align-items: flex-end;
}

@media (max-width: 1200px) {
    .filter-grid {
        grid-template-columns: repeat(3, 1fr) auto;
    }
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
    width: 100%;
}

.filter-input-icon {
    position: absolute;
    left: 13px;
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
    padding: 8px 14px 8px 36px;
    font-size: 13px;
    font-weight: 600;
    color: #0F172A;
    outline: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    appearance: none;
    -webkit-appearance: none;
}

select.filter-control {
    padding-right: 34px;
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

/* Select2 integration in Filter Inputs */
.filter-input-wrapper .select2-container {
    width: 100% !important;
}

.filter-input-wrapper .select2-container .select2-selection--single {
    height: 42px !important;
    border-radius: 10px !important;
    border: 1px solid #CBD5E1 !important;
    background: #FFFFFF !important;
    padding-left: 36px !important;
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
    height: 42px;
}

/* Table Card Design */
.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.rep-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 22px;
    background: #FFFFFF;
    border-bottom: 1px solid #EDF2F7;
    flex-wrap: wrap;
    gap: 12px;
}

/* Sticky Table Header */
.table-leave thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #F8FAFC !important;
    color: #475569 !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.4px !important;
    border-bottom: 2px solid #E2E8F0 !important;
    white-space: nowrap !important;
    padding: 13px 14px !important;
}

.table-leave tbody td {
    padding: 12px 14px !important;
    border-bottom: 1px solid #F1F5F8 !important;
    vertical-align: middle !important;
    font-size: 12.5px !important;
}

.table-leave tbody tr:hover {
    background: #F8FAFC !important;
}

/* 3-Dot Action Button */
.btn-action-dots {
    width: 32px;
    height: 32px;
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
    min-width: 200px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
    border: 1px solid #E2E8F0;
    padding: 6px;
}

.dropdown-menu-action .dropdown-item {
    font-size: 12.5px;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 8px;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.15s ease;
}

.dropdown-menu-action .dropdown-item:hover {
    background: #EEF2FF;
    color: var(--orb-primary);
}

/* Timeline Stepper CSS */
.approval-timeline {
    position: relative;
    padding-left: 24px;
    margin-top: 10px;
}

.approval-timeline::before {
    content: '';
    position: absolute;
    top: 6px;
    left: 8px;
    bottom: 6px;
    width: 2px;
    background: #E2E8F0;
}

.timeline-step {
    position: relative;
    margin-bottom: 16px;
}

.timeline-step:last-child {
    margin-bottom: 0;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header Banner -->
        <div class="rep-hero">
            <div>
                <h3 class="text-white font-weight-bold mb-1"><i class="fas fa-plane-departure mr-2"></i>Team Leave Workbench</h3>
                <p class="mb-0 opacity-90 small">Monitor leave requests, manage multi-stage approvals (Manager → HR), and track team availability.</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <span class="badge px-3 py-2 text-white font-weight-bold" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); border-radius: 10px; font-size: 12.5px;">
                    <i class="fas fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::now()->format('d M Y') }}
                </span>
            </div>
        </div>

        <!-- 5 Rich Metric Summary Cards Grid -->
        <div class="team-stats-grid">
            <!-- Total Pending -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="team-stat-label">Total Pending</div>
                    <div class="team-stat-val">{{ $totalPendingCount ?? 0 }}</div>
                </div>
            </div>

            <!-- Manager Pending -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A;">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div>
                    <div class="team-stat-label">Pending Manager</div>
                    <div class="team-stat-val">{{ $managerPendingCount ?? 0 }}</div>
                </div>
            </div>

            <!-- HR Pending -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #F0F9FF; color: #0284C7; border: 1px solid #BAE6FD;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="team-stat-label">Pending HR</div>
                    <div class="team-stat-val">{{ $hrPendingCount ?? 0 }}</div>
                </div>
            </div>

            <!-- Approved Requests -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="team-stat-label">Approved</div>
                    <div class="team-stat-val">{{ $approvedLeaveCount ?? 0 }}</div>
                </div>
            </div>

            <!-- Rejected Requests -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <div class="team-stat-label">Rejected</div>
                    <div class="team-stat-val">{{ $rejectedLeaveCount ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Today's Team Leave Status Alert Card -->
        <div class="today-status-card">
            <div class="today-status-title">
                <i class="fas fa-calendar-day text-primary" style="font-size: 15px;"></i>
                <span>Today's Team Leave Status ({{ \Carbon\Carbon::now()->format('d M Y') }})</span>
            </div>
            <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
                @if(!empty($todayLeaves) && count($todayLeaves) > 0)
                    @foreach($todayLeaves as $tl)
                        <div class="today-leave-pill">
                            <i class="fas fa-user-clock text-warning"></i>
                            <span>{{ $tl->display_name }}</span>
                            <span class="text-muted font-weight-normal">&bull;</span>
                            <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 10.5px; border-radius: 6px;">{{ $tl->leave_type_name ?? 'Leave' }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="p-2.5 px-3.5 bg-light border text-muted font-weight-bold d-inline-flex align-items-center" style="border-radius: 12px; font-size: 12.5px; gap: 8px;">
                        <i class="fas fa-check-circle text-success" style="font-size: 15px;"></i>
                        <span>No team members are on leave today. All active members are available.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modern Filter Card -->
        <div class="rep-filter-card">
            <div class="filter-card-header">
                <div class="filter-header-title">
                    <span class="filter-header-icon"><i class="fas fa-filter"></i></span>
                    <span>Filter Leave Applications</span>
                </div>
            </div>

            <div class="filter-card-body">
                <form method="GET" action="{{ route('reporting.leave') }}" id="leaveFilterForm">
                    <div class="filter-grid">
                        <!-- 1. Status / Stage -->
                        <div class="filter-field">
                            <label class="filter-label"><i class="fas fa-tasks"></i> Status / Stage</label>
                            <div class="filter-input-wrapper">
                                <i class="fas fa-layer-group filter-input-icon"></i>
                                <select name="status" class="filter-control">
                                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>⏳ Pending Requests (Default)</option>
                                    <option value="pending_manager" {{ request('status') == 'pending_manager' ? 'selected' : '' }}>🟠 Pending Manager</option>
                                    <option value="pending_hr" {{ request('status') == 'pending_hr' ? 'selected' : '' }}>🔵 Pending HR</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>🟢 Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>🔴 Rejected</option>
                                    <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>⚪ Null & Void</option>
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Requests</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2. Employee -->
                        <div class="filter-field">
                            <label class="filter-label"><i class="fas fa-user"></i> Employee</label>
                            <div class="filter-input-wrapper">
                                <i class="fas fa-search filter-input-icon"></i>
                                <select name="employee_id" class="filter-control select2-searchable">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->display_name }} ({{ $emp->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 3. Reporting Manager (if applicable) -->
                        @if(!empty($reportingManagers) && count($reportingManagers) > 0)
                        <div class="filter-field">
                            <label class="filter-label"><i class="fas fa-user-tie"></i> Reporting Manager</label>
                            <div class="filter-input-wrapper">
                                <i class="fas fa-users filter-input-icon"></i>
                                <select name="reporting_manager_id" class="filter-control select2-searchable">
                                    <option value="">All Managers</option>
                                    @foreach($reportingManagers as $rm)
                                        <option value="{{ $rm->id }}" {{ request('reporting_manager_id') == $rm->id ? 'selected' : '' }}>
                                            {{ $rm->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <!-- 4. Leave Type -->
                        <div class="filter-field">
                            <label class="filter-label"><i class="fas fa-tag"></i> Leave Type</label>
                            <div class="filter-input-wrapper">
                                <i class="fas fa-bookmark filter-input-icon"></i>
                                <select name="leave_type_id" class="filter-control">
                                    <option value="">All Leave Types</option>
                                    @foreach($leaveTypes as $lt)
                                        <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                            {{ $lt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 5. Search Text -->
                        <div class="filter-field">
                            <label class="filter-label"><i class="fas fa-keyboard"></i> Search Keyword</label>
                            <div class="filter-input-wrapper">
                                <i class="fas fa-search filter-input-icon"></i>
                                <input type="text" name="search" value="{{ request('search') }}" class="filter-control" placeholder="Search by name, code, reason...">
                            </div>
                        </div>

                        <!-- 6. Action Buttons -->
                        <div class="filter-field">
                            <label class="filter-label" style="visibility: hidden;">Action</label>
                            <div class="filter-actions">
                                <button type="submit" class="btn text-white font-weight-bold" style="height: 42px; border-radius: 10px; padding: 0 18px; background: var(--orb-primary); border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25);">
                                    <i class="fas fa-search" style="font-size: 12px;"></i> Search
                                </button>
                                <a href="{{ route('reporting.leave') }}" class="btn btn-outline-secondary font-weight-bold" style="height: 42px; border-radius: 10px; padding: 0 16px; display: inline-flex; align-items: center; gap: 8px; background: #FFFFFF;">
                                    <i class="fas fa-undo" style="font-size: 12px;"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Table Container Card -->
        <div class="rep-card">
            <!-- Card Header Title -->
            <div class="rep-card-header">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <span style="width: 38px; height: 38px; border-radius: 10px; background: #EEF2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                    <div>
                        <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 16px;">Leave Approval Workbench</h5>
                        <div class="text-muted small font-weight-bold mt-0.5">Showing {{ $leaveRequests->total() }} total leave applications</div>
                    </div>
                </div>
            </div>

            <!-- Table Responsive -->
            <div class="table-responsive">
                <table class="table table-leave table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 50px;">S.No.</th>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">Reporting Manager</th>
                            <th class="py-3">Leave Type</th>
                            <th class="py-3 text-center">Leave Period</th>
                            <th class="py-3 text-center">Days</th>
                            <th class="py-3 text-center">Manager Approval</th>
                            <th class="py-3 text-center">HR Approval</th>
                            <th class="py-3 text-center">Overall Status</th>
                            <th class="py-3 text-center" style="width: 60px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $lr)
                            @php
                                $ltName = $lr->leave_type_name ?? 'Leave';
                                $ltLower = strtolower($ltName);
                                $ltStyle = match(true) {
                                    str_contains($ltLower, 'sick') => 'background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5;',
                                    str_contains($ltLower, 'casual') => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;',
                                    str_contains($ltLower, 'comp') => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
                                    str_contains($ltLower, 'earned') || str_contains($ltLower, 'privilege') => 'background: #EFF6FF; color: #1E40AF; border: 1px solid #93C5FD;',
                                    default => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;'
                                };

                                $stLower = strtolower(trim($lr->status ?? 'pending'));
                                $startDateFormatted = \Carbon\Carbon::parse($lr->start_date)->format('d M Y');
                                $endDateFormatted = \Carbon\Carbon::parse($lr->end_date)->format('d M Y');
                                $isSingleDay = ($lr->start_date === $lr->end_date);

                                $daysVal = (float)($lr->requested_days ?? $lr->deducted_days ?? 1);
                                $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);

                                $managerEmpId = $lr->current_reporting_manager_id ?? $lr->reporting_manager_employee_id;
                                $hasManager = !empty($managerEmpId);

                                $user = auth()->user();
                                $userEmpId = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->value('id');
                                $isAssignedManager = (!empty($managerEmpId) && (int)$managerEmpId === (int)$userEmpId);
                                $isSuperAdminUser = method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : (in_array((int)($user->system_role_id ?? $user->role_id ?? 0), [1, 2], true));

                                $mgrApproved = !empty($lr->manager_approved_by) || !empty($lr->manager_approved_at) || ($lr->approval_level === 'manager_approved');
                                $mgrRejected = ($stLower === 'rejected' && empty($lr->manager_approved_by));
                                $hrApproved = ($stLower === 'approved');
                                $hrRejected = ($stLower === 'rejected' && !empty($lr->manager_approved_by));
                            @endphp
                        <tr>
                            <!-- 1. S.No. -->
                            <td class="py-3 px-3 align-middle text-center font-weight-bold text-muted" style="font-size: 12px;">
                                {{ $loop->iteration + ($leaveRequests->currentPage() - 1) * $leaveRequests->perPage() }}
                            </td>

                            <!-- 2. Employee -->
                            <td class="py-3 px-4 align-middle">
                                <div>
                                    <strong class="text-dark font-weight-bold d-block" style="line-height: 1.25; font-size: 13px;">{{ $lr->display_name }}</strong>
                                    <small class="text-muted font-weight-bold" style="font-size: 10.5px;">{{ $lr->employee_code }}</small>
                                </div>
                            </td>

                            <!-- 3. Reporting Manager -->
                            <td class="py-3 align-middle">
                                @if($hasManager && !empty($lr->reporting_manager_name))
                                    <div>
                                        <strong class="text-dark font-weight-bold d-block" style="line-height: 1.25; font-size: 12.5px;">{{ $lr->reporting_manager_name }}</strong>
                                        <small class="text-muted d-block" style="font-size: 10.5px; font-weight: 600;">Reporting Manager</small>
                                    </div>
                                @else
                                    <span class="text-muted font-weight-bold small" style="font-size: 11px;">— Not Assigned</span>
                                @endif
                            </td>

                            <!-- 4. Leave Type -->
                            <td class="py-3 align-middle">
                                <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 6px; font-size: 11px; {{ $ltStyle }}">
                                    {{ $ltName }}
                                </span>
                            </td>

                            <!-- 5. Leave Period -->
                            <td class="py-3 align-middle text-center">
                                <div class="d-inline-flex align-items-center bg-light px-2.5 py-1" style="border-radius: 8px; border: 1px solid #E2E8F0; font-size: 11.5px; font-weight: 600; color: #1E293B;">
                                    @if($isSingleDay)
                                        <span>{{ $startDateFormatted }}</span>
                                    @else
                                        <span>{{ $startDateFormatted }}</span>
                                        <i class="fas fa-arrow-right text-muted" style="font-size: 9px; margin: 0 6px;"></i>
                                        <span>{{ $endDateFormatted }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 6. Days -->
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-light border font-weight-bold px-2.5 py-1 text-dark" style="border-radius: 6px; font-size: 11px;">
                                    {{ $daysText }}
                                </span>
                            </td>

                            <!-- 7. Manager Approval -->
                            <td class="py-3 align-middle text-center">
                                @if($hrApproved)
                                    @if($hasManager && $mgrApproved)
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                            ✓ Approved
                                        </span>
                                        @if(!empty($lr->manager_approver_name))
                                            <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->manager_approver_name }}</small>
                                        @endif
                                    @else
                                        <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #F8FAFC; color: #64748B;">⚪ NOT REQUIRED</span>
                                    @endif
                                @elseif($mgrApproved)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                        ✓ Approved
                                    </span>
                                    @if(!empty($lr->manager_approver_name))
                                        <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->manager_approver_name }}</small>
                                    @endif
                                @elseif($mgrRejected)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                        ✕ Rejected
                                    </span>
                                    @if(!empty($lr->rejected_by_name))
                                        <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->rejected_by_name }}</small>
                                    @endif
                                @else
                                    @if($hasManager)
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                            🟠 PENDING
                                        </span>
                                    @else
                                        <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #F8FAFC; color: #64748B;">⚪ NOT REQUIRED</span>
                                    @endif
                                @endif
                            </td>

                            <!-- 8. HR Approval -->
                            <td class="py-3 align-middle text-center">
                                @if($hrApproved)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                        ✓ Approved
                                    </span>
                                    @if(!empty($lr->hr_approver_name))
                                        <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->hr_approver_name }}</small>
                                    @endif
                                @elseif($hrRejected)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                        ✕ Rejected
                                    </span>
                                    @if(!empty($lr->rejected_by_name))
                                        <small class="text-muted d-block" style="font-size: 9.5px; font-weight: 600;">by {{ $lr->rejected_by_name }}</small>
                                    @endif
                                @elseif($stLower === 'pending')
                                    @if($hasManager && !$mgrApproved)
                                        <span class="text-muted small" style="font-size: 11px;">— Waiting for Manager</span>
                                    @else
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10.5px; background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;">
                                            🔵 PENDING HR
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted" style="font-size: 11px;">—</span>
                                @endif
                            </td>

                            <!-- 9. Overall Status -->
                            <td class="py-3 align-middle text-center">
                                @if($stLower === 'approved')
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                        🟢 APPROVED
                                    </span>
                                @elseif($stLower === 'void')
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">
                                        ⚪ NULL & VOID
                                    </span>
                                @elseif($stLower === 'rejected' || $stLower === 'cancelled')
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                        🔴 REJECTED
                                    </span>
                                @elseif($stLower === 'pending')
                                    @if($hasManager && !$mgrApproved)
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                            🟠 PENDING MANAGER
                                        </span>
                                    @else
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;">
                                            🔵 PENDING HR
                                        </span>
                                    @endif
                                @else
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                        🟠 PENDING
                                    </span>
                                @endif
                            </td>

                            <!-- 10. Actions Three-Dot Column (⋮) -->
                            <td class="py-3 align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn-action-dots" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-action">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#viewModal{{ $lr->id }}">
                                            <i class="fas fa-eye text-primary"></i> View Details & Timeline
                                        </a>

                                        @if($stLower === 'pending' && Route::has('leave-approvals.approve'))
                                            @if($isAssignedManager)
                                                @if(!$mgrApproved)
                                                    <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success border-0 bg-transparent font-weight-bold" onclick="return confirm('Approve leave request at Manager stage?')">
                                                            <i class="fas fa-check-circle text-success"></i> Approve Request
                                                        </button>
                                                    </form>
                                                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}">
                                                        <i class="fas fa-times-circle text-danger"></i> Reject Request
                                                    </a>
                                                @else
                                                    <span class="dropdown-item text-muted disabled" style="cursor: not-allowed; opacity: 0.8;">
                                                        <i class="fas fa-check text-success"></i> Approved by You (Sent to HR)
                                                    </span>
                                                @endif
                                            @elseif($isSuperAdminUser)
                                                <!-- Super Admin Override Actions -->
                                                <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success border-0 bg-transparent font-weight-bold" onclick="return confirm('Super Admin Override: Approve leave request?')">
                                                        <i class="fas fa-crown text-warning"></i> Super Admin Approve
                                                    </button>
                                                </form>
                                                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}">
                                                    <i class="fas fa-times-circle text-danger"></i> Reject Request
                                                </a>
                                            @else
                                                @if($hasManager && !$mgrApproved)
                                                    <span class="dropdown-item text-muted disabled" style="cursor: not-allowed; opacity: 0.8;">
                                                        <i class="fas fa-clock text-warning"></i> Waiting for Reporting Manager
                                                    </span>
                                                @else
                                                    <span class="dropdown-item text-muted disabled" style="cursor: not-allowed; opacity: 0.8;">
                                                        <i class="fas fa-hourglass-half text-primary"></i> Pending HR Final Approval
                                                    </span>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-minus fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Team Leave Applications Found</h5>
                                <p class="small mb-0">No leave requests match the selected filters.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leaveRequests->hasPages())
                {{ $leaveRequests->withQueryString()->links('vendor.pagination.orbo') }}
            @endif
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS PLACED OUTSIDE THE TABLE FOR CLEAN DOM HIERARCHY -->
<!-- ========================================================================= -->
@foreach($leaveRequests as $lr)
    @php
        $ltName = $lr->leave_type_name ?? 'Leave';
        $ltLower = strtolower($ltName);
        $ltStyle = match(true) {
            str_contains($ltLower, 'sick') => 'background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5;',
            str_contains($ltLower, 'casual') => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;',
            str_contains($ltLower, 'comp') => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
            str_contains($ltLower, 'earned') || str_contains($ltLower, 'privilege') => 'background: #EFF6FF; color: #1E40AF; border: 1px solid #93C5FD;',
            default => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;'
        };

        $stLower = strtolower(trim($lr->status ?? 'pending'));
        $startDateFormatted = \Carbon\Carbon::parse($lr->start_date)->format('d M Y');
        $endDateFormatted = \Carbon\Carbon::parse($lr->end_date)->format('d M Y');
        $isSingleDay = ($lr->start_date === $lr->end_date);

        $daysVal = (float)($lr->requested_days ?? $lr->deducted_days ?? 1);
        $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);

        $managerEmpId = $lr->current_reporting_manager_id ?? $lr->reporting_manager_employee_id;
        $hasManager = !empty($managerEmpId);

        $user = auth()->user();
        $userEmpId = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->value('id');
        $isAssignedManager = (!empty($managerEmpId) && (int)$managerEmpId === (int)$userEmpId);
        $isSuperAdminUser = method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : (in_array((int)($user->system_role_id ?? $user->role_id ?? 0), [1, 2], true));

        $mgrApproved = !empty($lr->manager_approved_by) || !empty($lr->manager_approved_at) || ($lr->approval_level === 'manager_approved');
        $mgrRejected = ($stLower === 'rejected' && empty($lr->manager_approved_by));
        $hrApproved = ($stLower === 'approved');
        $hrRejected = ($stLower === 'rejected' && !empty($lr->manager_approved_by));
    @endphp

    <!-- VIEW TIMELINE & DETAILS MODAL -->
    <div class="modal fade" id="viewModal{{ $lr->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 800px; width: 94%;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden; max-height: 85vh; display: flex; flex-direction: column; background: #FFFFFF;">
                
                <!-- Dynamic DB Branded Header -->
                <div class="modal-header text-white px-4 py-3 align-items-center justify-content-between" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%); height: 58px; flex-shrink: 0; border-radius: 18px 18px 0 0;">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); border: 1px solid rgba(255, 255, 255, 0.3); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                            <i class="fas fa-calendar-check text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 15px; letter-spacing: 0.2px;">
                                Leave Request Details
                            </h5>
                            <div class="text-white-50" style="font-size: 10.5px; font-weight: 500; opacity: 0.92;">
                                Request ID: #LR-{{ str_pad($lr->id, 4, '0', STR_PAD_LEFT) }} &bull; Submitted {{ \Carbon\Carbon::parse($lr->created_at)->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    <button type="button" class="close text-white opacity-10 border-0" style="width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 18px; outline: none; line-height: 1;" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Scrollable Modal Body -->
                <div class="modal-body px-4 py-3" style="overflow-y: auto; flex: 1; background: #F8FAFC;">
                    
                    <!-- 3-Column x 2-Row Information Grid -->
                    <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        
                        <!-- Employee Card -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%); color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13.5px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(75,0,232,0.25);">
                                {{ strtoupper(substr($lr->display_name ?? 'E', 0, 1)) }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">EMPLOYEE</div>
                                <div class="font-weight-bold text-dark text-truncate" style="font-size: 13px; line-height: 1.2;">{{ $lr->display_name }}</div>
                                <div class="text-muted font-weight-bold" style="font-size: 10px;">{{ $lr->employee_code }}</div>
                            </div>
                        </div>

                        <!-- Department & Designation -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(75, 0, 232, 0.08); color: {{ $branding['primary_color'] ?? '#4B00E8' }}; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">DEPARTMENT & DESIGNATION</div>
                                <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px; line-height: 1.2;">{{ $lr->department_name ?? 'General' }}</div>
                                <div class="text-muted font-weight-bold text-truncate" style="font-size: 10.5px;">{{ $lr->designation_name ?? 'Employee' }}</div>
                            </div>
                        </div>

                        <!-- Leave Type -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #F3E8FF; color: #9333EA; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">LEAVE TYPE</div>
                                <div class="mt-0.5">
                                    <span class="badge font-weight-bold px-2.5 py-0.5" style="border-radius: 6px; font-size: 10.5px; {{ $ltStyle }}">
                                        {{ $ltName }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Period -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEF2F2; color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">LEAVE PERIOD</div>
                                <div class="font-weight-bold text-dark mt-0.5" style="font-size: 12px; line-height: 1.2;">
                                    @if($isSingleDay)
                                        {{ $startDateFormatted }}
                                    @else
                                        {{ $startDateFormatted }} — {{ $endDateFormatted }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="far fa-clock"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">DURATION</div>
                                <div class="font-weight-bold text-success mt-0.5" style="font-size: 12.5px; line-height: 1.2;">
                                    {{ $daysText }}
                                </div>
                            </div>
                        </div>

                        <!-- Reporting Manager -->
                        <div class="px-3 py-2.5 rounded-lg border bg-white d-flex align-items-center" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 10px; min-height: 58px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">REPORTING MANAGER</div>
                                <div class="font-weight-bold text-dark mt-0.5 text-truncate" style="font-size: 12px; line-height: 1.2;">
                                    {{ $lr->reporting_manager_name ?? '— Not Assigned' }}
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Approval Pipeline Stage Tracker -->
                    <div class="mb-3 px-3.5 py-2.5 rounded-lg border bg-white d-flex align-items-center justify-content-between flex-wrap" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03); gap: 12px;">
                        
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <div class="d-flex align-items-center" style="gap: 5px;">
                                <i class="fas fa-layer-group text-muted" style="font-size: 11px;"></i>
                                <span class="text-muted font-weight-bold uppercase" style="font-size: 9.5px; letter-spacing: 0.5px; color: #64748B;">STAGES:</span>
                            </div>

                            <!-- Manager Stage -->
                            <div class="d-flex align-items-center px-2.5 py-1 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; gap: 6px;">
                                <span class="text-dark font-weight-bold" style="font-size: 11px;">1. Manager:</span>
                                @if($hrApproved)
                                    @if($hasManager && $mgrApproved)
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">🟢 Approved</span>
                                    @else
                                        <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #F8FAFC; color: #64748B;">⚪ Not Required</span>
                                    @endif
                                @elseif($mgrApproved)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">🟢 Approved</span>
                                @elseif($mgrRejected)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">🔴 Rejected</span>
                                @else
                                    @if($hasManager)
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">🟠 Pending</span>
                                    @else
                                        <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #F8FAFC; color: #64748B;">⚪ Not Required</span>
                                    @endif
                                @endif
                            </div>

                            <i class="fas fa-arrow-right text-muted opacity-50" style="font-size: 10px;"></i>

                            <!-- HR Stage -->
                            <div class="d-flex align-items-center px-2.5 py-1 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; gap: 6px;">
                                <span class="text-dark font-weight-bold" style="font-size: 11px;">2. HR Final:</span>
                                @if($hrApproved)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">🟢 Approved</span>
                                @elseif($hrRejected)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">🔴 Rejected</span>
                                @elseif($stLower === 'pending')
                                    @if($hasManager && !$mgrApproved)
                                        <span class="text-muted" style="font-size: 10px;">— Waiting Manager</span>
                                    @else
                                        <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;">🔵 Pending HR</span>
                                    @endif
                                @else
                                    <span class="text-muted" style="font-size: 10px;">—</span>
                                @endif
                            </div>
                        </div>

                        <!-- Overall Badge -->
                        <div>
                            @if($stLower === 'approved')
                                <span class="badge font-weight-bold px-3 py-1" style="border-radius: 50px; font-size: 11px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                    🟢 FULLY APPROVED
                                </span>
                            @elseif($stLower === 'void')
                                <span class="badge font-weight-bold px-3 py-1" style="border-radius: 50px; font-size: 11px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">
                                    ⚪ NULL & VOID
                                </span>
                            @elseif($stLower === 'rejected' || $stLower === 'cancelled')
                                <span class="badge font-weight-bold px-3 py-1" style="border-radius: 50px; font-size: 11px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                    🔴 REJECTED
                                </span>
                            @else
                                <span class="badge font-weight-bold px-3 py-1" style="border-radius: 50px; font-size: 11px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                    ⏳ IN REVIEW
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Leave Reason & Attachments -->
                    <div class="mb-3 px-3.5 py-2.5 rounded-lg border bg-white" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03);">
                        <div class="text-muted font-weight-bold uppercase mb-1" style="font-size: 9.5px; letter-spacing: 0.5px; color: #64748B;">REASON FOR LEAVE</div>
                        <p class="text-dark mb-0 font-weight-medium" style="font-size: 12.5px; line-height: 1.5; white-space: pre-line;">
                            {{ $lr->reason ?? $lr->employee_comment ?? 'No specific reason provided.' }}
                        </p>
                    </div>

                    <!-- Stage-by-Stage History Log -->
                    <div class="px-3.5 py-3 rounded-lg border bg-white" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03);">
                        <div class="text-muted font-weight-bold uppercase mb-2" style="font-size: 9.5px; letter-spacing: 0.5px; color: #64748B;">APPROVAL TIMELINE LOG</div>
                        
                        <div class="approval-timeline">
                            <!-- Step 1: Submission -->
                            <div class="timeline-step">
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-light" style="border: 1px solid #E2E8F0; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <strong class="text-dark font-weight-bold" style="font-size: 12.5px;">Application Submitted</strong>
                                        <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 9.5px;">{{ \Carbon\Carbon::parse($lr->created_at)->format('d M Y, h:i A') }}</span>
                                    </div>
                                    <div class="text-muted mt-0.5" style="font-size: 10.5px;">Applied by <strong>{{ $lr->display_name }}</strong></div>
                                </div>
                            </div>

                            <!-- Step 2: Manager Stage -->
                            <div class="timeline-step">
                                @if(!$hasManager)
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #64748B; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px;">
                                        <i class="fas fa-minus"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-light" style="border: 1px solid #E2E8F0; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-muted font-weight-bold" style="font-size: 12.5px;">Manager Approval Skipped</strong>
                                            <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 9.5px;">Direct to HR</span>
                                        </div>
                                        <div class="text-muted mt-0.5" style="font-size: 10.5px;">No reporting manager assigned to employee</div>
                                    </div>
                                @elseif($mgrApproved)
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #86EFAC; border-left: 3px solid #10B981; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-success font-weight-bold" style="font-size: 12.5px;">✓ Manager Approved</strong>
                                            <span class="badge font-weight-bold" style="background: #DCFCE7; color: #15803D; font-size: 9.5px;">Passed to HR</span>
                                        </div>
                                        <div class="text-muted mt-0.5" style="font-size: 10.5px;">Approved by <strong>{{ $lr->manager_approver_name ?? $lr->reporting_manager_name }}</strong> @if(!empty($lr->manager_approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->manager_approved_at)->format('d M Y, h:i A') }} @endif</div>
                                        @if(!empty($lr->manager_comment))
                                            <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #F8FAFC; padding: 4px 8px; border-radius: 4px; border: 1px solid #E2E8F0;">Note: "{{ $lr->manager_comment }}"</div>
                                        @endif
                                    </div>
                                @elseif($mgrRejected)
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #EF4444; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #FCA5A5; border-left: 3px solid #EF4444; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-danger font-weight-bold" style="font-size: 12.5px;">✕ Manager Rejected</strong>
                                            <span class="badge font-weight-bold" style="background: #FEE2E2; color: #991B1B; font-size: 9.5px;">Rejected</span>
                                        </div>
                                        <div class="text-danger mt-0.5" style="font-size: 10.5px;">Rejected by <strong>{{ $lr->rejected_by_name ?? $lr->reporting_manager_name }}</strong> @if(!empty($lr->approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->approved_at)->format('d M Y, h:i A') }} @endif</div>
                                        @if(!empty($lr->rejection_reason))
                                            <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #FEF2F2; padding: 4px 8px; border-radius: 4px; border: 1px solid #FCA5A5;">Reason: "{{ $lr->rejection_reason }}"</div>
                                        @endif
                                    </div>
                                @else
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #F59E0B; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #FDE68A; border-left: 3px solid #F59E0B; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-warning font-weight-bold" style="font-size: 12.5px;">🟠 Manager Approval Pending</strong>
                                            <span class="badge font-weight-bold" style="background: #FEF3C7; color: #92400E; font-size: 9.5px;">Awaiting Manager</span>
                                        </div>
                                        <div class="text-muted mt-0.5" style="font-size: 10.5px;">Assigned to: <strong>{{ $lr->reporting_manager_name ?? 'Reporting Manager' }}</strong></div>
                                    </div>
                                @endif
                            </div>

                            <!-- Step 3: HR Final Approval -->
                            <div class="timeline-step">
                                @if($hrApproved)
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #86EFAC; border-left: 3px solid #10B981; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-success font-weight-bold" style="font-size: 12.5px;">✓ HR Final Approved</strong>
                                            <span class="badge font-weight-bold" style="background: #DCFCE7; color: #15803D; font-size: 9.5px;">Completed</span>
                                        </div>
                                        <div class="text-muted mt-0.5" style="font-size: 10.5px;">Approved by <strong>{{ $lr->hr_approver_name ?? 'HR Admin' }}</strong> @if(!empty($lr->approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->approved_at)->format('d M Y, h:i A') }} @endif</div>
                                        @if(!empty($lr->hr_note))
                                            <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #F8FAFC; padding: 4px 8px; border-radius: 4px; border: 1px solid #E2E8F0;">Note: "{{ $lr->hr_note }}"</div>
                                        @endif
                                    </div>
                                @elseif($stLower === 'void')
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #64748B; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px;">
                                        <i class="fas fa-ban"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #CBD5E1; border-left: 3px solid #64748B; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-muted font-weight-bold" style="font-size: 12.5px;">⚪ Null & Void</strong>
                                            <span class="badge font-weight-bold" style="background: #F1F5F9; color: #475569; font-size: 9.5px;">Cancelled / Voided</span>
                                        </div>
                                        <div class="text-muted mt-0.5" style="font-size: 10.5px;">Voided by <strong>{{ $lr->hr_approver_name ?? $lr->rejected_by_name ?? 'HR Admin' }}</strong> @if(!empty($lr->approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->approved_at)->format('d M Y, h:i A') }} @endif</div>
                                    </div>
                                @elseif($hrRejected)
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #EF4444; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #FCA5A5; border-left: 3px solid #EF4444; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-danger font-weight-bold" style="font-size: 12.5px;">✕ HR Rejected</strong>
                                            <span class="badge font-weight-bold" style="background: #FEE2E2; color: #991B1B; font-size: 9.5px;">Rejected</span>
                                        </div>
                                        <div class="text-danger mt-0.5" style="font-size: 10.5px;">Rejected by <strong>{{ $lr->rejected_by_name ?? 'HR Admin' }}</strong> @if(!empty($lr->approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->approved_at)->format('d M Y, h:i A') }} @endif</div>
                                        @if(!empty($lr->rejection_reason))
                                            <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #FEF2F2; padding: 4px 8px; border-radius: 4px; border: 1px solid #FCA5A5;">Reason: "{{ $lr->rejection_reason }}"</div>
                                        @endif
                                    </div>
                                @elseif($mgrApproved || !$hasManager)
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: {{ $branding['primary_color'] ?? '#4B00E8' }}; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.25);">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid {{ $branding['primary_color'] ?? '#4B00E8' }}; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="font-weight-bold" style="font-size: 12.5px; color: {{ $branding['primary_color'] ?? '#4B00E8' }};">🔵 HR Approval Pending</strong>
                                            <span class="badge font-weight-bold" style="background: #EEF2FF; color: {{ $branding['primary_color'] ?? '#4B00E8' }}; border: 1px solid #C7D2FE; font-size: 9.5px;">Action Required</span>
                                        </div>
                                        <div class="text-muted mt-0.5" style="font-size: 10.5px;">Pending HR final verification & approval</div>
                                    </div>
                                @else
                                    <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; border: 1px solid #CBD5E1; display: flex; align-items: center; justify-content: center; font-size: 9.5px;">
                                        <i class="far fa-circle"></i>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid #CBD5E1; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-muted font-weight-bold" style="font-size: 12.5px;">○ HR Approval</strong>
                                            <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 9.5px;">Awaiting Manager</span>
                                        </div>
                                        <div class="text-muted mt-0.5" style="font-size: 10.5px;">Waiting for Manager approval first</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-top bg-white px-4 py-2.5 align-items-center justify-content-between" style="height: 58px; flex-shrink: 0; border-radius: 0 0 18px 18px;">
                    <button type="button" class="btn btn-sm btn-light font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; border: 1px solid #CBD5E1; color: #475569; font-size: 12.5px;" data-dismiss="modal">
                        Close
                    </button>

                    <div class="d-flex align-items-center" style="gap: 8px;">
                        @if($stLower === 'pending' && Route::has('leave-approvals.approve'))
                            @if($isAssignedManager)
                                @if(!$mgrApproved)
                                    <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; font-size: 12.5px;" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}" data-dismiss="modal">
                                        <i class="fas fa-times mr-1"></i> Reject Request
                                    </button>
                                    <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm text-white font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.3); font-size: 12.5px;" onclick="return confirm('Approve leave request at Manager stage for {{ addslashes($lr->display_name) }}?')">
                                            <i class="fas fa-check mr-1"></i> Approve Request
                                        </button>
                                    </form>
                                @endif
                            @elseif($isSuperAdminUser)
                                <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; font-size: 12.5px;" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i> Reject Request
                                </button>
                                <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm text-white font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.3); font-size: 12.5px;" onclick="return confirm('Super Admin Override: Approve leave request for {{ addslashes($lr->display_name) }}?')">
                                        <i class="fas fa-crown mr-1 text-warning"></i> Super Admin Approve
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- REJECT MODAL -->
    @if(Route::has('leave-approvals.reject'))
    <div class="modal fade" id="rejectModal{{ $lr->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 460px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
                <form method="POST" action="{{ route('leave-approvals.reject', $lr->id) }}">
                    @csrf
                    <div class="modal-header bg-danger text-white p-3">
                        <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 15px;">
                            <i class="fas fa-times-circle mr-2"></i> Reject Leave Request
                        </h5>
                        <button type="button" class="close text-white opacity-10" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-muted font-weight-bold mb-2">Rejecting request for <strong>{{ $lr->display_name }}</strong> ({{ $startDateFormatted }}@if(!$isSingleDay) to {{ $endDateFormatted }}@endif):</p>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide reason for rejection..." style="border-radius: 10px; font-size: 13px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3 border-top">
                        <button type="button" class="btn btn-light border font-weight-bold" style="border-radius: 9px;" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger font-weight-bold px-4" style="border-radius: 9px;">Reject Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection

@section('_scripts')
<script>
    $(document).ready(function() {
        // Initialize searchable select2 dropdowns
        if ($.fn.select2) {
            $('.select2-searchable').select2({
                width: '100%',
                placeholder: 'Select...',
                allowClear: true
            });
        }
    });
</script>
@endsection
