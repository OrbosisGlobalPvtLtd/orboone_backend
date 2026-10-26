@extends('layouts.panel', ['active' => 'leave_approvals'])

@section('page_title', 'Leave Approvals')

@section('_head')
@include('hrms.leave.shared.style')
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
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.rep-container {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 auto;
    box-sizing: border-box !important;
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

/* 6 Responsive Leave Metric Cards in a Single Row on Desktop (Image 2 Design) */
.leave-metric-grid {
    display: grid !important;
    grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
    gap: 12px !important;
    margin-bottom: 22px !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.leave-metric-card {
    background: #FFFFFF !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 16px !important;
    padding: 12px 14px 10px !important;
    box-shadow: 0 10px 24px rgba(16, 24, 40, .055) !important;
    position: relative !important;
    overflow: hidden !important;
    min-height: 88px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease !important;
    text-decoration: none !important;
    color: inherit !important;
    cursor: pointer !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.leave-metric-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 14px 30px rgba(16, 24, 40, .09) !important;
    text-decoration: none !important;
    color: inherit !important;
}

.leave-metric-card::after {
    content: "" !important;
    position: absolute !important;
    right: -22px !important;
    top: -30px !important;
    width: 80px !important;
    height: 80px !important;
    border-radius: 50% !important;
    background: var(--metric-soft, #F4F2FF) !important;
    pointer-events: none !important;
    z-index: 0 !important;
}

.leave-metric-card.active {
    border: 2px solid var(--metric-color, var(--orb-primary)) !important;
    box-shadow: 0 12px 28px rgba(75, 0, 232, 0.14) !important;
    transform: translateY(-2px) !important;
    background: #FFFFFF !important;
}

.leave-metric-card .leave-metric-top {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    position: relative !important;
    z-index: 1 !important;
    width: 100% !important;
}

.leave-metric-card .leave-metric-icon {
    width: 36px !important;
    height: 36px !important;
    border-radius: 12px !important;
    background: var(--metric-soft, #F4F2FF) !important;
    color: var(--metric-color, var(--orb-primary)) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 15px !important;
    flex-shrink: 0 !important;
}

.leave-metric-card .leave-metric-value {
    font-size: 24px !important;
    font-weight: 950 !important;
    color: #101828 !important;
    line-height: 1 !important;
    letter-spacing: -0.02em !important;
    text-align: right !important;
}

.leave-metric-card .leave-metric-label {
    font-size: 10.5px !important;
    font-weight: 900 !important;
    color: #475467 !important;
    text-transform: uppercase !important;
    margin-top: 10px !important;
    position: relative !important;
    z-index: 1 !important;
    letter-spacing: 0.03em !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.leave-metric-card .leave-metric-line {
    position: relative !important;
    height: 3px !important;
    border-radius: 999px !important;
    background: linear-gradient(90deg, var(--metric-color, var(--orb-primary)), transparent) !important;
    margin-top: 8px !important;
    width: 100% !important;
    z-index: 1 !important;
}

@media (max-width: 1100px) {
    .leave-metric-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 10px !important;
    }
}

@media (max-width: 640px) {
    .leave-metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 8px !important;
    }
    .leave-metric-card {
        padding: 10px 12px 8px !important;
        border-radius: 14px !important;
    }
    .leave-metric-card .leave-metric-value {
        font-size: 20px !important;
    }
}

/* Filter Labels & Button Theme Styles */
.filter-item-lbl {
    font-size: 11px !important;
    font-weight: 850 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.04em !important;
    color: #475467 !important;
    margin-bottom: 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 5px !important;
    white-space: nowrap !important;
}

.btn-orb-search {
    height: 38px !important;
    border-radius: 10px !important;
    padding: 0 18px !important;
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 850 !important;
    font-size: 12.5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25) !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    white-space: nowrap !important;
}

.btn-orb-search:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(75, 0, 232, 0.35) !important;
    color: #ffffff !important;
    filter: brightness(1.05) !important;
}

.btn-orb-reset {
    height: 38px !important;
    border-radius: 10px !important;
    padding: 0 16px !important;
    background: #ffffff !important;
    color: #475467 !important;
    border: 1px solid #CBD5E1 !important;
    font-weight: 750 !important;
    font-size: 12.5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
    text-decoration: none !important;
}

.btn-orb-reset:hover {
    background: #F8FAFC !important;
    border-color: #94A3B8 !important;
    color: #1E293B !important;
    text-decoration: none !important;
}

.filter-control-sm.orbo-datepicker,
.flatpickr-input.orbo-datepicker {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234B00E8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px 16px !important;
    padding-right: 36px !important;
    cursor: pointer !important;
}

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
    align-items: flex-end;
    gap: 10px;
    width: 100%;
}

.filter-item-wrap {
    flex: 1 1 150px;
    min-width: 135px;
    display: flex;
    flex-direction: column;
    gap: 4px;
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

.filter-actions-box {
    display: flex;
    align-items: center;
    gap: 8px;
    align-self: flex-end;
    margin-bottom: 0;
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
    $curStatus = request('status', 'pending');
    if (empty($curStatus)) {
        $curStatus = 'pending';
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

        @include('hrms.leave.shared.flash')

        <!-- 6 Rich Interactive Metric Summary Cards Grid (Image 2 Design) -->
        <div class="leave-metric-grid">
            <!-- 1. All Requests -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
               class="leave-metric-card stat-all {{ ($curStatus === 'all') ? 'active' : '' }}"
               style="--metric-color:#6366F1;--metric-soft:#EEF2FF;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="leave-metric-value">{{ $totalAllCount ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">All Requests</div>
                <div class="leave-metric-line"></div>
            </a>

            <!-- 2. Total Pending (Default Active) -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}"
               class="leave-metric-card stat-pending {{ ($curStatus === 'pending') ? 'active' : '' }}"
               style="--metric-color:#F59E0B;--metric-soft:#FFFBEB;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="leave-metric-value">{{ $totalPendingCount ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Total Pending</div>
                <div class="leave-metric-line"></div>
            </a>

            <!-- 3. Manager Pending -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_manager'])) }}"
               class="leave-metric-card stat-pending-manager {{ ($curStatus === 'pending_manager') ? 'active' : '' }}"
               style="--metric-color:#EA580C;--metric-soft:#FFF7ED;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="leave-metric-value">{{ $managerPendingCount ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Pending Manager</div>
                <div class="leave-metric-line"></div>
            </a>

            <!-- 4. HR Pending -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_hr'])) }}"
               class="leave-metric-card stat-pending-hr {{ ($curStatus === 'pending_hr') ? 'active' : '' }}"
               style="--metric-color:#0284C7;--metric-soft:#F0F9FF;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="leave-metric-value">{{ $hrPendingCount ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Pending HR</div>
                <div class="leave-metric-line"></div>
            </a>

            <!-- 5. Approved Requests -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'approved'])) }}"
               class="leave-metric-card stat-approved {{ ($curStatus === 'approved') ? 'active' : '' }}"
               style="--metric-color:#10B981;--metric-soft:#ECFDF5;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="leave-metric-value">{{ $approvedLeaveCount ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Approved</div>
                <div class="leave-metric-line"></div>
            </a>

            <!-- 6. Rejected Requests -->
            <a href="{{ route('leave-approvals.index', array_merge(request()->except(['status', 'page']), ['status' => 'rejected'])) }}"
               class="leave-metric-card stat-rejected {{ ($curStatus === 'rejected') ? 'active' : '' }}"
               style="--metric-color:#EF4444;--metric-soft:#FEF2F2;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="leave-metric-value">{{ $rejectedLeaveCount ?? 0 }}</div>
                </div>
                <div class="leave-metric-label">Rejected</div>
                <div class="leave-metric-line"></div>
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
                
            </div>

            <!-- Comprehensive Filter Toolbar Bar with Labels -->
            <div class="p-3 border-bottom bg-white">
                <form method="GET" action="{{ route('leave-approvals.index') }}" class="d-flex flex-column" style="gap: 12px;">
                    <div class="filter-form-grid">
                        <!-- 1. Employee Filter -->
                        <div class="filter-item-wrap lg-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-user text-primary"></i> Employee
                            </label>
                            <select name="employee_id" class="filter-control-sm select2-searchable" data-placeholder="All Employees">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- 2. Status / Approval Stage Filter -->
                        <div class="filter-item-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-tasks text-primary"></i> Status
                            </label>
                            <select name="status" class="filter-control-sm select2-searchable">
                                <option value="all" {{ $curStatus === 'all' ? 'selected' : '' }}>All Requests</option>
                                <option value="pending" {{ $curStatus === 'pending' ? 'selected' : '' }}> Total Pending</option>
                                <option value="pending_manager" {{ $curStatus === 'pending_manager' ? 'selected' : '' }}> Pending Manager</option>
                                <option value="pending_hr" {{ $curStatus === 'pending_hr' ? 'selected' : '' }}> Pending HR</option>
                                <option value="approved" {{ $curStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $curStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                
                                <option value="void" {{ $curStatus === 'void' ? 'selected' : '' }}>Null & Void</option>
                                <option value="expired" {{ $curStatus === 'expired' ? 'selected' : '' }}> Expired</option>
                                <option value="cancelled" {{ $curStatus === 'cancelled' ? 'selected' : '' }}> Cancelled</option>
                            </select>
                        </div>


                        

                        <!-- 4. Reporting Manager Filter -->
                        @if(!empty($reportingManagers) && count($reportingManagers) > 0)
                            <div class="filter-item-wrap lg-wrap">
                                <label class="filter-item-lbl">
                                    <i class="fas fa-user-tie text-primary"></i> Manager
                                </label>
                                <select name="reporting_manager_id" class="filter-control-sm select2-searchable" data-placeholder="All Managers">
                                    <option value="">All Managers</option>
                                    @foreach($reportingManagers as $rm)
                                        <option value="{{ $rm->id }}" {{ request('reporting_manager_id') == $rm->id ? 'selected' : '' }}>
                                            {{ $rm->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- 5. Leave Type Filter -->
                        <div class="filter-item-wrap sm-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-tag text-primary"></i> Type
                            </label>
                            <select name="leave_type_id" class="filter-control-sm select2-searchable">
                                <option value="">All Leave Types</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                        {{ $lt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Month Filter -->
                        <div class="filter-item-wrap sm-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-calendar-alt text-primary"></i> Month
                            </label>
                            <select name="month" class="filter-control-sm select2-searchable" data-placeholder="Select Month">
                                <option value="all" {{ ($selectedMonth ?? '') === 'all' ? 'selected' : '' }}>All Months</option>
                                @foreach([
                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                ] as $mNum => $mName)
                                    <option value="{{ $mNum }}" {{ (string)($selectedMonth ?? '') === (string)$mNum ? 'selected' : '' }}>
                                        {{ $mName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div class="filter-item-wrap sm-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-calendar text-primary"></i> Year
                            </label>
                            <select name="year" class="filter-control-sm select2-searchable" data-placeholder="Select Year">
                                @php $selYear = (int) ($year ?? request('year', date('Y'))); @endphp
                                @for($y = 2024; $y <= 2030; $y++)
                                    <option value="{{ $y }}" {{ $selYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{--
                        <!-- 6. Start Date (From) -->
                        <div class="filter-item-wrap date-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-calendar-day text-primary"></i> From
                            </label>
                            <x-form.date-picker name="start_date" id="approval_start_date" :value="request('start_date')" placeholder="dd-mm-yyyy" class="filter-control-sm" />
                        </div>

                        <!-- 7. End Date (To) -->
                        <div class="filter-item-wrap date-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-calendar-day text-primary"></i> To
                            </label>
                            <x-form.date-picker name="end_date" id="approval_end_date" :value="request('end_date')" placeholder="dd-mm-yyyy" class="filter-control-sm" />
                        </div>
                        --}}

                        <!-- 8. Search Input -->
                        <div class="filter-item-wrap lg-wrap">
                            <label class="filter-item-lbl">
                                <i class="fas fa-search text-primary"></i> Keyword
                            </label>
                            <input type="text" name="search" value="{{ request('search') }}" class="filter-control-sm" placeholder="Search employee, reason...">
                        </div>

                        <!-- 9. Search & Reset Buttons -->
                        <div class="d-flex align-items-center filter-actions-box" style="gap: 8px;">
                            <button type="submit" class="btn btn-sm btn-orb-search">
                                <i class="fas fa-search" style="font-size: 11px;"></i> Search
                            </button>
                            <a href="{{ route('leave-approvals.index') }}" class="btn btn-sm btn-orb-reset" title="Reset all filters">
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
                    || (request()->filled('month') && request('month') !== 'all')
                    || request()->filled('year')
                    || (request()->filled('status') && request('status') !== 'pending');
            @endphp
            @if($hasActiveFilters)
                <div class="active-filters-bar">
                    <span class="font-weight-bold text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                        <i class="fas fa-filter text-primary mr-1"></i> Active Filters:
                    </span>
                    @if(request()->filled('status') && request('status') !== 'pending')
                        <span class="active-filter-badge">
                            Status: {{ ucwords(str_replace('_', ' ', request('status'))) }}
                            <a href="{{ route('leave-approvals.index', request()->except('status')) }}" title="Reset status filter">&times;</a>
                        </span>
                    @endif
                    @if(request()->filled('month') && request('month') !== 'all')
                        <span class="active-filter-badge">
                            Month: {{ \Carbon\Carbon::createFromDate(null, (int) request('month'), 1)->format('F') }}
                            <a href="{{ route('leave-approvals.index', array_merge(request()->except('month'), ['month' => 'all'])) }}" title="Reset month filter">&times;</a>
                        </span>
                    @endif
                    @if(request()->filled('year'))
                        <span class="active-filter-badge">
                            Year: {{ request('year') }}
                            <a href="{{ route('leave-approvals.index', request()->except('year')) }}" title="Reset year filter">&times;</a>
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
                            <td class="py-3 align-middle text-center">
                                <div class="d-inline-flex flex-column align-items-center" style="gap: 3px;">
                                    <span class="badge font-weight-bold px-2.5 py-1 {{ $ltClass }}" style="border-radius: 6px; font-size: 11px;">
                                        {{ $ltName }}
                                    </span>
                                    @if(!empty($lr->attachment_path))
                                        <a href="{{ route('leave-requests.attachment', $lr->id) }}" target="_blank" class="badge badge-pill font-weight-bold" style="background: rgba(75, 0, 232, 0.08); color: var(--orb-primary, #4B00E8); border: 1px solid rgba(75, 0, 232, 0.2); font-size: 9.5px; padding: 2px 7px; text-decoration: none;" title="View Attachment / Medical Document">
                                            <i class="fas fa-paperclip mr-0.5"></i> Doc
                                        </a>
                                    @endif
                                </div>
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

                            <!-- 6. Days & Deduction -->
                            <td class="py-3 align-middle text-center">
                                <span class="badge badge-light border font-weight-bold px-2.5 py-1 text-dark" style="border-radius: 6px; font-size: 11px;">
                                    {{ $daysText }}
                                </span>
                                @if(!empty($lr->is_half_day))
                                    <div class="mt-1">
                                        <span class="badge badge-pill font-weight-bold" style="background: rgba(14, 165, 233, 0.12); color: #0284C7; border: 1px solid rgba(14, 165, 233, 0.28); font-size: 10px; padding: 2px 7px;">
                                            <i class="far fa-clock mr-0.5"></i> {{ ($lr->half_day_type ?? '') === 'second_half' ? '2nd Half' : '1st Half' }}
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($lr->sandwich_applied))
                                    <div class="mt-0.5">
                                        <span class="badge badge-pill font-weight-bold" style="background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D; font-size: 10px; padding: 2px 7px;" title="Sandwich Policy Applied">
                                            <i class="fas fa-layer-group mr-0.5"></i> Sandwich
                                        </span>
                                    </div>
                                @endif
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
                                            <a class="dropdown-item text-danger font-weight-bold" href="javascript:void(0)" data-toggle="modal" data-target="#voidModal{{ $lr->id }}">
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
                <div class="p-3 border-top bg-white">
                    {{ $leaveRequests->links('vendor.pagination.orbo') }}
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
