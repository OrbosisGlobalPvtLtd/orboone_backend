@extends('layouts.panel', ['active' => 'leave_approvals'])

@section('page_title', 'Leave Approvals')

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
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 20px;
    padding: 22px 26px;
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

/* 6 Rich Metric Summary Cards Grid */
.team-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
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
    transition: all 0.22s ease;
    text-decoration: none !important;
    color: inherit;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.team-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    color: inherit;
}

.team-stat-card.active {
    box-shadow: 0 8px 24px rgba(75, 0, 232, 0.14);
    border-width: 2px !important;
}
.team-stat-card.stat-all.active { border-color: #6366F1 !important; background: #F8FAFC; }
.team-stat-card.stat-pending.active { border-color: #4F46E5 !important; background: #EEF2FF; }
.team-stat-card.stat-pending-manager.active { border-color: #D97706 !important; background: #FFFBEB; }
.team-stat-card.stat-pending-hr.active { border-color: #0284C7 !important; background: #F0F9FF; }
.team-stat-card.stat-approved.active { border-color: #047857 !important; background: #ECFDF5; }
.team-stat-card.stat-rejected.active { border-color: #DC2626 !important; background: #FEF2F2; }

.lt-badge-sick { background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5; }
.lt-badge-casual { background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7; }
.lt-badge-comp { background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE; }
.lt-badge-earned { background: #EFF6FF; color: #1E40AF; border: 1px solid #93C5FD; }
.lt-badge-default { background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE; }

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

/* Responsive Modal Styles */
.leave-modal-dialog {
    max-width: 800px;
    width: 95%;
    margin: 1.75rem auto;
}

.leave-modal-content {
    border-radius: 16px;
    overflow: hidden;
    max-height: 86vh;
    display: flex;
    flex-direction: column;
    background: #FFFFFF;
}

.leave-modal-header {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    min-height: 56px;
    height: auto;
    flex-shrink: 0;
    border-radius: 16px 16px 0 0;
}

.leave-modal-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.leave-info-tile {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 58px;
    box-shadow: 0 1px 3px rgba(15,23,42,0.03);
    min-width: 0; /* Prevents overflow in flex child */
}

.leave-stage-pipeline-box {
    border-radius: 10px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(15,23,42,0.03);
    background: #FFFFFF;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.leave-balance-breakdown-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.leave-balance-tile {
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.leave-stage-arrow {
    font-size: 10px;
    transition: transform 0.2s ease;
}

@media (max-width: 992px) {
    .leave-modal-info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .leave-balance-breakdown-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .leave-modal-dialog {
        width: calc(100% - 16px) !important;
        max-width: 100% !important;
        margin: 8px auto !important;
    }
    .leave-modal-content {
        max-height: 92vh !important;
        border-radius: 14px !important;
    }
    .leave-modal-info-grid {
        grid-template-columns: 1fr !important;
        gap: 8px !important;
    }
    .leave-balance-breakdown-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 6px !important;
    }
    .leave-modal-header, .leave-modal-footer {
        padding: 10px 14px !important;
        min-height: 52px !important;
        height: auto !important;
    }
    .leave-stage-pipeline-box {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
    }
    .leave-stage-items-wrap {
        flex-direction: column !important;
        align-items: stretch !important;
        width: 100% !important;
        gap: 6px !important;
    }
    .leave-stage-arrow {
        transform: rotate(90deg);
        align-self: center;
        margin: 2px 0;
    }
    .leave-modal-footer {
        flex-direction: column !important;
        gap: 8px !important;
    }
    .leave-modal-footer > button[data-dismiss="modal"] {
        order: 2;
        width: 100% !important;
    }
    .leave-modal-footer-actions {
        order: 1;
        width: 100% !important;
        flex-direction: column !important;
        gap: 6px !important;
    }
    .leave-modal-footer-actions .btn,
    .leave-modal-footer-actions form,
    .leave-modal-footer-actions form .btn {
        width: 100% !important;
        display: block !important;
    }
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

.filter-control-sm {
    height: 38px !important;
    border-radius: 9px !important;
    font-size: 12.5px !important;
    border: 1px solid #CBD5E1 !important;
    background: #FFFFFF !important;
    padding: 0 10px !important;
    outline: none !important;
    width: 100% !important;
    color: #1E293B !important;
    font-weight: 600 !important;
    box-sizing: border-box !important;
}

.filter-control-sm:focus {
    border-color: var(--orb-primary) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
}

/* Filter Item Wrappers */
.filter-form-grid {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    width: 100%;
}

.filter-item-wrap {
    flex: 1 1 150px;
    min-width: 135px;
}

.filter-item-wrap.sm-wrap {
    flex: 1 1 125px;
    min-width: 115px;
}

.filter-item-wrap.date-wrap {
    flex: 1 1 135px;
    min-width: 125px;
}

.filter-item-wrap.lg-wrap {
    flex: 1.5 1 180px;
    min-width: 160px;
}

.filter-item-wrap .select2-container {
    width: 100% !important;
    display: block !important;
}

.filter-item-wrap .select2-container .select2-selection--single {
    height: 38px !important;
    border-radius: 9px !important;
    border: 1px solid #CBD5E1 !important;
    display: flex !important;
    align-items: center !important;
    background: #FFFFFF !important;
}

.filter-item-wrap .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 12.5px !important;
    color: #1E293B !important;
    font-weight: 600 !important;
    padding-left: 10px !important;
    padding-right: 24px !important;
}

.filter-item-wrap .select2-container .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
}

/* Active filters pill */
.active-filters-bar {
    padding: 8px 16px;
    background: #F8FAFC;
    border-bottom: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 11.5px;
}

.active-filter-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #EEF2FF;
    color: #4338CA;
    border: 1px solid #C7D2FE;
    border-radius: 20px;
    padding: 3px 10px;
    font-weight: 700;
}

.active-filter-badge a {
    color: #6366F1;
    text-decoration: none;
    margin-left: 3px;
    font-weight: 800;
}

.active-filter-badge a:hover {
    color: #DC2626;
}

/* Sticky Table Header */
.table thead th {
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

.table tbody td {
    padding: 11px 14px !important;
    border-bottom: 1px solid #F1F5F8 !important;
    vertical-align: middle !important;
    font-size: 12.5px !important;
}

.table tbody tr:hover {
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
    min-width: 180px;
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
@php
    $curStatus = request('status', 'all');
    if (empty($curStatus)) {
        $curStatus = 'all';
    }
@endphp
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header Banner for HR Admin Leave Approvals -->
        <div class="rep-hero">
            <div>
                <h3 class="text-white font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Leave Approvals</h3>
                <p class="mb-0 opacity-90 small">Review employee leave applications, manage 2-stage approval workflow, and view all past approvals.</p>
            </div>
        </div>

        <!-- 6 Rich Interactive Metric Summary Cards Grid -->
        <div class="team-stats-grid">
            <!-- 1. All Requests -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
               class="team-stat-card stat-all {{ ($curStatus === 'all') ? 'active' : '' }}">
                <div class="team-stat-icon" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <div class="team-stat-label">All Requests</div>
                    <div class="team-stat-val">{{ $totalAllCount ?? 0 }}</div>
                </div>
            </a>

            <!-- 2. Total Pending -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}"
               class="team-stat-card stat-pending {{ ($curStatus === 'pending') ? 'active' : '' }}">
                <div class="team-stat-icon" style="background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="team-stat-label">Total Pending</div>
                    <div class="team-stat-val">{{ $totalPendingCount ?? 0 }}</div>
                </div>
            </a>

            <!-- 3. Manager Pending -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_manager'])) }}"
               class="team-stat-card stat-pending-manager {{ ($curStatus === 'pending_manager') ? 'active' : '' }}">
                <div class="team-stat-icon" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A;">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div>
                    <div class="team-stat-label">Pending Manager</div>
                    <div class="team-stat-val">{{ $managerPendingCount ?? 0 }}</div>
                </div>
            </a>

            <!-- 4. HR Pending -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_hr'])) }}"
               class="team-stat-card stat-pending-hr {{ ($curStatus === 'pending_hr') ? 'active' : '' }}">
                <div class="team-stat-icon" style="background: #F0F9FF; color: #0284C7; border: 1px solid #BAE6FD;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="team-stat-label">Pending HR</div>
                    <div class="team-stat-val">{{ $hrPendingCount ?? 0 }}</div>
                </div>
            </a>

            <!-- 5. Approved Requests -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'approved'])) }}"
               class="team-stat-card stat-approved {{ ($curStatus === 'approved') ? 'active' : '' }}">
                <div class="team-stat-icon" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="team-stat-label">Approved</div>
                    <div class="team-stat-val">{{ $approvedLeaveCount ?? 0 }}</div>
                </div>
            </a>

            <!-- 6. Rejected Requests -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'rejected'])) }}"
               class="team-stat-card stat-rejected {{ ($curStatus === 'rejected') ? 'active' : '' }}">
                <div class="team-stat-icon" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <div class="team-stat-label">Rejected</div>
                    <div class="team-stat-val">{{ $rejectedLeaveCount ?? 0 }}</div>
                </div>
            </a>
        </div>

        <!-- Main Table Container Card -->
        <div class="rep-card">
            <!-- Card Header Title -->
            <div class="d-flex align-items-center justify-content-between border-bottom bg-white flex-wrap" style="padding: 14px 20px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 9px; background: #EEF2FF; color: #4F46E5; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="fas fa-check-double"></i>
                    </span>
                    <div>
                        <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">Leave Approvals Workbench</h5>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="badge badge-light border text-muted font-weight-bold px-2.5 py-1" style="font-size: 11.5px; border-radius: 7px;">
                        Showing {{ $leaveRequests->total() }} {{ \Illuminate\Support\Str::plural('Record', $leaveRequests->total()) }}
                    </span>
                </div>
            </div>

            <!-- Comprehensive Filter Toolbar Bar -->
            <div class="p-3 border-bottom bg-white">
                <form method="GET" action="{{ route('leave-approvals.index') }}" class="d-flex flex-column" style="gap: 12px;">
                    <div class="filter-form-grid">
                        <!-- Status / Stage Filter -->
                        <div class="filter-item-wrap">
                            <select name="status" class="filter-control-sm">
                                <option value="all" {{ $curStatus === 'all' ? 'selected' : '' }}>🌟 All Requests</option>
                                <option value="pending" {{ $curStatus === 'pending' ? 'selected' : '' }}>⏳ Total Pending</option>
                                <option value="pending_manager" {{ $curStatus === 'pending_manager' ? 'selected' : '' }}>🟠 Pending Manager</option>
                                <option value="pending_hr" {{ $curStatus === 'pending_hr' ? 'selected' : '' }}>🔵 Pending HR</option>
                                <option value="approved" {{ $curStatus === 'approved' ? 'selected' : '' }}>🟢 Approved (Past)</option>
                                <option value="rejected" {{ $curStatus === 'rejected' ? 'selected' : '' }}>🔴 Rejected</option>
                                <option value="void" {{ $curStatus === 'void' ? 'selected' : '' }}>⚪ Null & Void</option>
                                <option value="expired" {{ $curStatus === 'expired' ? 'selected' : '' }}>⚪ Expired</option>
                                <option value="cancelled" {{ $curStatus === 'cancelled' ? 'selected' : '' }}>⚪ Cancelled</option>
                            </select>
                        </div>

                        <!-- Employee Filter -->
                        <div class="filter-item-wrap lg-wrap">
                            <select name="employee_id" class="filter-control-sm select2-searchable" data-placeholder="All Employees" placeholder="All Employees">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Reporting Manager Filter -->
                        @if(!empty($reportingManagers) && count($reportingManagers) > 0)
                            <div class="filter-item-wrap lg-wrap">
                                <select name="reporting_manager_id" class="filter-control-sm select2-searchable" data-placeholder="All Managers" placeholder="All Managers">
                                    <option value="">All Managers</option>
                                    @foreach($reportingManagers as $rm)
                                        <option value="{{ $rm->id }}" {{ request('reporting_manager_id') == $rm->id ? 'selected' : '' }}>
                                            {{ $rm->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Leave Type Filter -->
                        <div class="filter-item-wrap sm-wrap">
                            <select name="leave_type_id" class="filter-control-sm">
                                <option value="">All Leave Types</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                        {{ $lt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Start Date (From) -->
                        <div class="filter-item-wrap date-wrap" title="From Date">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="filter-control-sm" placeholder="From Date">
                        </div>

                        <!-- End Date (To) -->
                        <div class="filter-item-wrap date-wrap" title="To Date">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="filter-control-sm" placeholder="To Date">
                        </div>

                        <!-- Search Input -->
                        <div class="filter-item-wrap lg-wrap">
                            <input type="text" name="search" value="{{ request('search') }}" class="filter-control-sm" placeholder="Search employee, type, reason...">
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex align-items-center" style="gap: 6px;">
                            <button type="submit" class="btn btn-sm text-white font-weight-bold" style="height: 38px; border-radius: 9px; padding: 0 16px; background: var(--orb-primary); border: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(75,0,232,0.2);">
                                <i class="fas fa-search" style="font-size: 11px;"></i> Search
                            </button>
                            <a href="{{ route('leave-approvals.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="height: 38px; border-radius: 9px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px;" title="Reset all filters">
                                <i class="fas fa-undo" style="font-size: 11px;"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Active Filters Chip Bar -->
            @php
                $hasActiveFilters = request()->filled('employee_id')
                    || request()->filled('reporting_manager_id')
                    || request()->filled('leave_type_id')
                    || request()->filled('start_date')
                    || request()->filled('end_date')
                    || request()->filled('search')
                    || (request()->filled('status') && request('status') !== 'all');
            @endphp
            @if($hasActiveFilters)
                <div class="active-filters-bar">
                    <span class="font-weight-bold text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                        <i class="fas fa-filter text-primary mr-1"></i> Active Filters:
                    </span>
                    @if(request()->filled('status') && request('status') !== 'all')
                        <span class="active-filter-badge">
                            Status: {{ ucwords(str_replace('_', ' ', request('status'))) }}
                            <a href="{{ route('leave-approvals.index', request()->except('status')) }}" title="Remove status filter">&times;</a>
                        </span>
                    @endif
                    @if(request()->filled('employee_id'))
                        @php $filterEmp = $employees->firstWhere('id', request('employee_id')); @endphp
                        <span class="active-filter-badge">
                            Employee: {{ $filterEmp?->display_name ?? 'ID #' . request('employee_id') }}
                            <a href="{{ route('leave-approvals.index', request()->except('employee_id')) }}" title="Remove employee filter">&times;</a>
                        </span>
                    @endif
                    @if(request()->filled('reporting_manager_id'))
                        @php $filterRm = $reportingManagers->firstWhere('id', request('reporting_manager_id')); @endphp
                        <span class="active-filter-badge">
                            Manager: {{ $filterRm?->display_name ?? 'ID #' . request('reporting_manager_id') }}
                            <a href="{{ route('leave-approvals.index', request()->except('reporting_manager_id')) }}" title="Remove manager filter">&times;</a>
                        </span>
                    @endif
                    @if(request()->filled('leave_type_id'))
                        @php $filterLt = $leaveTypes->firstWhere('id', request('leave_type_id')); @endphp
                        <span class="active-filter-badge">
                            Type: {{ $filterLt?->name ?? 'Type #' . request('leave_type_id') }}
                            <a href="{{ route('leave-approvals.index', request()->except('leave_type_id')) }}" title="Remove type filter">&times;</a>
                        </span>
                    @endif
                    @if(request()->filled('start_date'))
                        <span class="active-filter-badge">
                            From: {{ \Carbon\Carbon::parse(request('start_date'))->format('d M Y') }}
                            <a href="{{ route('leave-approvals.index', request()->except('start_date')) }}" title="Remove from-date filter">&times;</a>
                        </span>
                    @endif
                    @if(request()->filled('end_date'))
                        <span class="active-filter-badge">
                            To: {{ \Carbon\Carbon::parse(request('end_date'))->format('d M Y') }}
                            <a href="{{ route('leave-approvals.index', request()->except('end_date')) }}" title="Remove to-date filter">&times;</a>
                        </span>
                    @endif
                    @if(request()->filled('search'))
                        <span class="active-filter-badge">
                            Search: "{{ request('search') }}"
                            <a href="{{ route('leave-approvals.index', request()->except('search')) }}" title="Remove search filter">&times;</a>
                        </span>
                    @endif
                    <a href="{{ route('leave-approvals.index') }}" class="text-danger font-weight-bold ml-auto" style="text-decoration: none; font-size: 11.5px;">
                        <i class="fas fa-times-circle mr-1"></i> Clear All
                    </a>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 45px;">S.No.</th>
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
                                $ltClass = match(true) {
                                    str_contains($ltLower, 'sick') => 'lt-badge-sick',
                                    str_contains($ltLower, 'casual') => 'lt-badge-casual',
                                    str_contains($ltLower, 'comp') => 'lt-badge-comp',
                                    str_contains($ltLower, 'earned') || str_contains($ltLower, 'privilege') => 'lt-badge-earned',
                                    default => 'lt-badge-default'
                                };

                                $stLower = strtolower(trim($lr->status ?? 'pending'));
                                $startDateFormatted = !empty($lr->start_date) ? \Carbon\Carbon::parse($lr->start_date)->format('d M Y') : '—';
                                $endDateFormatted = !empty($lr->end_date) ? \Carbon\Carbon::parse($lr->end_date)->format('d M Y') : '—';
                                $isSingleDay = (!empty($lr->start_date) && !empty($lr->end_date) && $lr->start_date === $lr->end_date);

                                $daysVal = (float)($lr->requested_days ?? $lr->deducted_days ?? 1);
                                $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);

                                $managerEmpId = $lr->current_reporting_manager_id ?? $lr->reporting_manager_employee_id;
                                $hasManager = !empty($managerEmpId);

                                $isSuperAdminUser = $isSuperAdmin ?? false;
                                $isHrAdminUser = $isHrOrAdmin ?? false;
                                $isAssignedManager = (!empty($managerEmpId) && !empty($authEmpId) && (int)$managerEmpId === (int)$authEmpId);
                                $mgrApproved = !empty($lr->manager_approved_by) || !empty($lr->manager_approved_at) || ($lr->approval_level === 'manager_approved');
                                $mgrRejected = ($stLower === 'rejected' && empty($lr->manager_approved_by));
                                $hrApproved = ($stLower === 'approved' && (!empty($lr->hr_approved_by) || !empty($lr->hr_approved_at)));
                                $hrRejected = ($stLower === 'rejected' && !empty($lr->manager_approved_by));

                                $canApprove = $isSuperAdminUser || $isHrAdminUser || ($canApprovePermission ?? false) || ($isAssignedManager && ($canViewTeamPermission ?? false));
                                $canReject = $isSuperAdminUser || $isHrAdminUser || ($canRejectPermission ?? false) || ($isAssignedManager && ($canViewTeamPermission ?? false));
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
                                <span class="badge font-weight-bold px-2.5 py-1 {{ $ltClass }}" style="border-radius: 6px; font-size: 11px;">
                                    {{ $ltName }}
                                </span>
                            </td>

                            <!-- 5. Leave Period -->
                            <td class="py-3 align-middle text-center">
                                <div class="d-inline-flex align-items-center bg-light px-2.5 py-1" style="border-radius: 7px; border: 1px solid #E2E8F0; font-size: 11.5px; font-weight: 600; color: #1E293B;">
                                    @if($isSingleDay)
                                        <span>{{ $startDateFormatted }}</span>
                                    @else
                                        <span>{{ $startDateFormatted }}</span>
                                        <i class="fas fa-arrow-right text-muted" style="font-size: 9px; margin: 0 5px;"></i>
                                        <span>{{ $endDateFormatted }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 6. Days -->
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-light border font-weight-bold px-2.5 py-1 text-dark" style="border-radius: 6px; font-size: 11px;">
                                    {{ $daysText }}
                                </span>
                                <!-- @if((float)($lr->lwp_days ?? 0) > 0)
                                    <div class="mt-1">
                                        <span class="badge font-weight-bold px-1.5 py-0.5" style="font-size: 9.5px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5;" title="{{ (float)$lr->lwp_days }} Day(s) Loss of Pay (LWP)">
                                            {{ (float)$lr->lwp_days }} LWP
                                        </span>
                                    </div>
                                @elseif((float)($lr->paid_days ?? 0) > 0)
                                    <div class="mt-1">
                                        <span class="badge font-weight-bold px-1.5 py-0.5" style="font-size: 9.5px; background: #ECFDF5; color: #15803D; border: 1px solid #86EFAC;" title="Salary Protected Paid Leave">
                                            {{ (float)$lr->paid_days }} Paid
                                        </span>
                                    </div>
                                @endif -->
                            </td>

                            <!-- 7. Manager Approval -->
                            <td class="py-3 align-middle text-center">
                                @if($mgrApproved)
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
                                @if($hrApproved || ($stLower === 'approved' && !empty($lr->hr_approved_by)))
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
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;" title="{{ $lr->hr_note ?: 'Marked Null & Void' }}">
                                        ⚪ NULL & VOID
                                    </span>
                                @elseif($stLower === 'expired')
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1;" title="{{ $lr->rejection_reason ?: 'Auto-expired' }}">
                                        ⚪ EXPIRED
                                    </span>
                                @elseif($stLower === 'rejected' || $stLower === 'cancelled')
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                        🔴 {{ strtoupper($stLower) }}
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
                                        🟠 {{ strtoupper($stLower ?: 'PENDING') }}
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

                                        @if($stLower === 'approved' && ($isSuperAdminUser || $isHrAdminUser))
                                            <a class="dropdown-item text-danger font-weight-bold" href="#" data-toggle="modal" data-target="#voidModal{{ $lr->id }}">
                                                <i class="fas fa-ban text-danger"></i> Make Null & Void
                                            </a>
                                        @endif

                                        @if($stLower === 'pending' && Route::has('leave-approvals.approve') && ($canApprove || $canReject))
                                            @if($isSuperAdminUser)
                                                <!-- Super Admin Override Actions -->
                                                @if($canApprove)
                                                <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success border-0 bg-transparent font-weight-bold" onclick="return confirm('Super Admin Override: Approve leave request?')">
                                                        <i class="fas fa-crown text-warning"></i> Super Admin Approve
                                                    </button>
                                                </form>
                                                @endif
                                                @if($canReject)
                                                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}">
                                                    <i class="fas fa-times-circle text-danger"></i> Reject Request
                                                </a>
                                                @endif
                                            @elseif($hasManager && !$mgrApproved)
                                                <!-- Manager Pending Stage -->
                                                @if($isAssignedManager)
                                                    @if($canApprove)
                                                    <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success border-0 bg-transparent font-weight-bold" onclick="return confirm('Approve leave request at Manager stage?')">
                                                            <i class="fas fa-check-circle text-success"></i> Approve Request
                                                        </button>
                                                    </form>
                                                    @endif
                                                    @if($canReject)
                                                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}">
                                                        <i class="fas fa-times-circle text-danger"></i> Reject Request
                                                    </a>
                                                    @endif
                                                @elseif($isHrAdminUser)
                                                    <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-primary border-0 bg-transparent font-weight-bold" onclick="return confirm('HR Admin Direct Approval: Approve & finalize leave request?')">
                                                            <i class="fas fa-check-double text-primary"></i> HR Admin Approve
                                                        </button>
                                                    </form>
                                                    @if($canReject)
                                                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}">
                                                        <i class="fas fa-times-circle text-danger"></i> Reject Request
                                                    </a>
                                                    @endif
                                                @else
                                                    <button type="button" class="dropdown-item text-muted border-0 bg-transparent" onclick="alert('Reporting manager approval is required first.')">
                                                        <i class="fas fa-clock text-warning"></i> Waiting for Reporting Manager
                                                    </button>
                                                @endif
                                            @else
                                                <!-- HR Stage (No Manager OR Manager HAS Approved) -->
                                                @if($canApprove)
                                                <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success border-0 bg-transparent font-weight-bold" onclick="return confirm('Perform final HR approval & deduct leave balance?')">
                                                        <i class="fas fa-check-double text-success"></i> HR Approve & Finalize
                                                    </button>
                                                </form>
                                                @endif
                                                @if($canReject)
                                                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal{{ $lr->id }}">
                                                    <i class="fas fa-times-circle text-danger"></i> Reject Request
                                                </a>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="fas fa-inbox text-muted mb-3" style="font-size: 38px; opacity: 0.5;"></i>
                                    <h6 class="font-weight-bold text-dark mb-1">No Leave Approvals Found</h6>
                                    <p class="small text-muted mb-0">There are no leave requests matching your current filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leaveRequests->hasPages())
                <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center">
                    <div class="small text-muted font-weight-bold">
                        Showing {{ $leaveRequests->firstItem() }} to {{ $leaveRequests->lastItem() }} of {{ $leaveRequests->total() }} entries
                    </div>
                    <div>
                        {{ $leaveRequests->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- PARTIAL MODALS  -->
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
        $startDateFormatted = !empty($lr->start_date) ? \Carbon\Carbon::parse($lr->start_date)->format('d M Y') : '—';
        $endDateFormatted = !empty($lr->end_date) ? \Carbon\Carbon::parse($lr->end_date)->format('d M Y') : '—';
        $isSingleDay = (!empty($lr->start_date) && !empty($lr->end_date) && $lr->start_date === $lr->end_date);

        $daysVal = (float)($lr->requested_days ?? $lr->deducted_days ?? 1);
        $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);

        $managerEmpId = $lr->current_reporting_manager_id ?? $lr->reporting_manager_employee_id;
        $hasManager = !empty($managerEmpId);

        $isSuperAdminUser = $isSuperAdmin ?? false;
        $isHrAdminUser = $isHrOrAdmin ?? false;
        $isAssignedManager = (!empty($managerEmpId) && !empty($authEmpId) && (int)$managerEmpId === (int)$authEmpId);
        $mgrApproved = !empty($lr->manager_approved_by) || !empty($lr->manager_approved_at) || ($lr->approval_level === 'manager_approved');
        $mgrRejected = ($stLower === 'rejected' && empty($lr->manager_approved_by));
        $hrApproved = ($stLower === 'approved' && (!empty($lr->hr_approved_by) || !empty($lr->hr_approved_at)));
        $hrRejected = ($stLower === 'rejected' && !empty($lr->manager_approved_by));

        $canApprove = $isSuperAdminUser || $isHrAdminUser || ($canApprovePermission ?? false) || ($isAssignedManager && ($canViewTeamPermission ?? false));
        $canReject = $isSuperAdminUser || $isHrAdminUser || ($canRejectPermission ?? false) || ($isAssignedManager && ($canViewTeamPermission ?? false));
    @endphp

    @include('hrms.leave.approvals.partials.view-modal', compact('lr', 'ltName', 'ltStyle', 'stLower', 'startDateFormatted', 'endDateFormatted', 'isSingleDay', 'daysText', 'hasManager', 'isAssignedManager', 'isSuperAdminUser', 'isHrAdminUser', 'mgrApproved', 'mgrRejected', 'hrApproved', 'hrRejected', 'canApprove', 'canReject'))
    @include('hrms.leave.approvals.partials.reject-modal', ['lr' => $lr])
    @include('hrms.leave.approvals.partials.void-modal', compact('lr', 'stLower', 'isSuperAdminUser', 'isHrAdminUser', 'isSingleDay', 'startDateFormatted', 'endDateFormatted'))
@endforeach

@endsection