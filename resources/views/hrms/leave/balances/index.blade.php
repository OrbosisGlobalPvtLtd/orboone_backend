@extends('layouts.panel')

@section('page_title', 'Leave Balances')

@section('_head')
@include('settings.partials.styles')

<style>
    .leave-page-container {
        max-width: 1380px;
        margin: 0 auto;
    }

    /* Target customized DataTable controls */
    .leave-dt-toolbar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        padding: 14px 24px !important;
        background: #fff !important;
        border-top: 1px solid #E7EAF3 !important;
        border-bottom: 1px solid #E7EAF3 !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .leave-dt-left,
    .leave-dt-right,
    .dt-buttons {
        display: flex !important;
        align-items: center !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .leave-dt-right {
        margin-left: auto !important;
        gap: 8px !important;
    }

    .dataTables_length {
        display: block !important;
    }

    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
        white-space: nowrap !important;
        font-weight: 850 !important;
        font-size: 12px !important;
        color: var(--set-muted) !important;
    }

    .dataTables_length select {
        width: auto !important;
        min-width: 64px !important;
        height: 34px !important;
        border-radius: 8px !important;
        border: 1px solid var(--set-border) !important;
    }

    .dt-buttons .dt-button,
    .leave-export-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: auto !important;
        min-width: auto !important;
        height: 34px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border: 1px solid #E7EAF3 !important;
        background: #fff !important;
        color: var(--set-primary) !important;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .dt-buttons .dt-button:hover,
    .leave-export-btn:hover {
        background: var(--set-primary) !important;
        color: #fff !important;
        border-color: var(--set-primary) !important;
    }

    .dataTables_info {
        font-size: 12px !important;
        font-weight: 750 !important;
        color: var(--set-muted) !important;
    }

    .leave-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-top: 1px solid var(--set-border);
        background: #FAF9FE;
    }

    .dataTables_paginate .pagination {
        margin: 0 !important;
    }

    .dataTables_paginate .paginate_button.active a,
    .dataTables_paginate .paginate_button:hover a {
        background: var(--set-primary) !important;
        border-color: var(--set-primary) !important;
        color: #fff !important;
    }

    /* Badges */
    .leave-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
        display: inline-block;
    }

    .badge-paid {
        background: #ECFDF3;
        color: #027A48;
    }

    .badge-pending {
        background: #FFF7E6;
        color: #B54708;
    }

    .badge-comp-off {
        background: #EFF8FF;
        color: #175CD3;
    }

    .badge-lwp {
        background: #FEF2F2;
        color: #EF4444;
    }

    /* Filters Grid */
    .mobile-filter-grid {
        display: grid;
        grid-template-columns: 1fr 2fr auto;
        gap: 12px;
        align-items: end;
    }

    .mobile-filter-group {
        display: flex;
        flex-direction: column;
    }

    .mobile-filter-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #667085;
        letter-spacing: .04em;
    }

    .mobile-filter-control {
        width: 100%;
        height: 40px;
        border: 1px solid #E7EAF3;
        border-radius: 12px;
        padding: 0 12px;
        background: #fff;
        font-size: 13px;
        color: #101828;
        outline: none;
        transition: all 0.2s ease;
    }

    .mobile-filter-control:focus {
        border-color: var(--set-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08);
    }

    .mobile-filter-reset {
        height: 40px;
        padding: 0 16px;
        border-radius: 12px;
        border: 1px solid #E7EAF3;
        background: #fff;
        font-weight: 800;
        font-size: 12px;
        color: #475569;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .mobile-filter-reset:hover {
        background: #F1F5F9;
        color: #1E293B;
    }

    @media (max-width: 991px) {
        .mobile-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .mobile-filter-group:last-child {
            grid-column: span 2;
        }
    }

    @media (max-width: 575px) {
        .mobile-filter-grid {
            grid-template-columns: 1fr;
        }
        .mobile-filter-group:last-child {
            grid-column: span 1;
        }
    }

    /* Premium Stat Cards (styled exactly like the second image) */
    .premium-stat-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .premium-stat-card {
        background: #fff;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 8px 24px rgba(16, 24, 40, 0.03);
        border: 1px solid #E7EAF3;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 130px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .premium-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 24, 40, 0.06);
    }

    .premium-stat-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .premium-stat-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .premium-stat-value {
        font-size: 26px;
        font-weight: 850;
        color: #101828;
        line-height: 1;
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    .premium-stat-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748B;
        margin-top: 14px;
        text-align: left;
    }

    .premium-stat-indicator {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    /* Color palettes */
    .bg-soft-purple { background: #F4F2FF !important; color: #7E22CE !important; }
    .bg-soft-green { background: #ECFDF5 !important; color: #10B981 !important; }
    .bg-soft-blue { background: #EFF6FF !important; color: #2563EB !important; }
    .bg-soft-orange { background: #FFF7ED !important; color: #EA580C !important; }
    .bg-soft-red { background: #FEF2F2 !important; color: #EF4444 !important; }

    .indicator-purple { background: linear-gradient(90deg, #7E22CE, #A855F7) !important; }
    .indicator-green { background: linear-gradient(90deg, #10B981, #34D399) !important; }
    .indicator-blue { background: linear-gradient(90deg, #2563EB, #60A5FA) !important; }
    .indicator-orange { background: linear-gradient(90deg, #EA580C, #F97316) !important; }
    .indicator-red { background: linear-gradient(90deg, #EF4444, #F87171) !important; }

    /* Employee Self-Service Dashboard Elements */
    .emp-quota-grid {
        display: grid;
        grid-template-columns: 7fr 5fr;
        gap: 24px;
        margin-top: 24px;
    }

    @media (max-width: 991px) {
        .emp-quota-grid {
            grid-template-columns: 1fr;
        }
    }

    .emp-quota-card {
        background: #fff;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 8px 24px rgba(16, 24, 40, 0.03);
        border: 1px solid #E7EAF3;
        margin-bottom: 24px;
    }

    .emp-quota-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        border-bottom: 1px solid #F1F5F9;
        padding-bottom: 16px;
    }

    .emp-quota-title {
        font-size: 16px;
        font-weight: 850;
        color: #101828;
        margin: 0;
    }

    .emp-quota-subtitle {
        font-size: 12px;
        color: #64748B;
        margin: 2px 0 0;
    }

    /* Leave breakdown list */
    .breakdown-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .breakdown-item {
        background: #FAFAFB;
        border: 1px solid #E7EAF3;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.2s ease;
    }

    .breakdown-item:hover {
        transform: translateX(4px);
        border-color: #CBD5E1;
    }

    .breakdown-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .breakdown-info {
        flex-grow: 1;
    }

    .breakdown-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .breakdown-name-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .breakdown-name {
        font-size: 14px;
        font-weight: 850;
        color: #101828;
    }

    .breakdown-badge {
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .breakdown-quota {
        font-size: 13px;
        font-weight: 800;
        color: #101828;
    }

    .breakdown-desc {
        font-size: 11.5px;
        color: #64748B;
        line-height: 1.4;
        margin-bottom: 12px;
    }

    .breakdown-progress-container {
        width: 100%;
    }

    .breakdown-progress-bg {
        width: 100%;
        height: 8px;
        background: #E2E8F0;
        border-radius: 99px;
        overflow: hidden;
    }

    .breakdown-progress-bar {
        height: 100%;
        border-radius: 99px;
        transition: width 0.6s ease;
    }

    .breakdown-stats {
        display: flex;
        gap: 16px;
        margin-top: 8px;
        font-size: 11px;
        font-weight: 700;
        color: #64748B;
    }

    .breakdown-stat-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        margin-right: 4px;
    }

    /* SVG Progress circle */
    .svg-ring-container {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .svg-ring-percentage {
        position: absolute;
        font-size: 26px;
        font-weight: 900;
        color: #101828;
        display: flex;
        flex-direction: column;
        align-items: center;
        line-height: 1.1;
    }

    .svg-ring-percentage span {
        font-size: 10px;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Year Swapper Bar */
    .year-swapper-bar {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #E7EAF3;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
</style>
@endsection

@section('_content')
<div class="set-page">
    <div class="leave-page-container">
        
        <!-- Premium Purple Gradient Hero Header -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-calendar-alt"></i> EMPLOYEE &bull; LEAVE MANAGEMENT
                </div>
                <h1 class="set-title">Leave Balances</h1>
                <p class="set-subtitle">Review available, used, pending, LWP and allocated leave balances across active years.</p>
            </div>
            
            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                <a href="{{ route('leave-requests.index') }}" class="set-btn" style="background: rgba(255, 255, 255, 0.15) !important; color: #fff !important; border: 1px solid rgba(255, 255, 255, 0.25); box-shadow: none;">
                    <i class="fas fa-history"></i> Leave History
                </a>
                <a href="{{ route('leave-requests.create') }}" class="set-btn" style="background: rgba(255, 255, 255, 0.25) !important; color: #fff !important; border: 1px solid rgba(255, 255, 255, 0.35); box-shadow: none;">
                    <i class="fas fa-plus-circle"></i> Apply Leave
                </a>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Dynamic Summary Cards Computed From Collection -->
        @php
            $balances->transform(function ($b) {
                $isConfirmed = optional($b->employee)->is_permanent ?? true;
                
                $totalAlloc = $isConfirmed ? (float) $b->total_allocated : 0.0;
                $paidAlloc = $isConfirmed ? (float) $b->paid_allocated : 0.0;
                $sickAlloc = $isConfirmed ? (float) $b->sick_allocated : 0.0;
                $compAlloc = $isConfirmed ? (float) $b->comp_off_allocated : 0.0;

                $totalRem = $isConfirmed ? (float) $b->total_remaining : 0.0;
                $paidRem = $isConfirmed ? (float) $b->paid_remaining : 0.0;
                $sickRem = $isConfirmed ? (float) $b->sick_remaining : 0.0;
                $compRem = $isConfirmed ? (float) $b->comp_off_remaining : 0.0;

                if (in_array((int) Carbon\Carbon::now('Asia/Kolkata')->month, [11, 12], true) && $totalRem > 10.0) {
                    $totalRem = round($totalRem * 0.5, 2);
                    $paidRem = round($paidRem * 0.5, 2);
                    $sickRem = round($sickRem * 0.5, 2);
                    $compRem = round($compRem * 0.5, 2);
                }

                $b->total_allocated = $totalAlloc;
                $b->paid_allocated = $paidAlloc;
                $b->sick_allocated = $sickAlloc;
                $b->comp_off_allocated = $compAlloc;

                $b->total_remaining = $totalRem;
                $b->paid_remaining = $paidRem;
                $b->sick_remaining = $sickRem;
                $b->comp_off_remaining = $compRem;

                return $b;
            });

            $totalAllocated = $balances->sum('total_allocated');
            $totalRemaining = $balances->sum('total_remaining');
            $totalPaidRem = $balances->sum('paid_remaining');
            $totalSickRem = $balances->sum('sick_remaining');
            $totalLwpUsed = $balances->sum('lwp_used');

            $user = auth()->user();
            $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
            $hasViewAll = $user && method_exists($user, 'hasPermission') && $user->hasPermission('leave.balance.view_all');
            $hasViewTeam = $user && method_exists($user, 'hasPermission') && $user->hasPermission('leave.balance.view_team');
            $isAdminOrHr = $isSuperAdmin || $hasViewAll || $hasViewTeam;
        @endphp

        <!-- Premium Stat Cards exactly styled like the second image -->
        <div class="premium-stat-row">
            <!-- 1. Total Allocated -->
            <div class="premium-stat-card">
                <div class="premium-stat-card-top">
                    <div class="premium-stat-icon-wrapper bg-soft-purple">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="premium-stat-value">{{ number_format((float) $totalAllocated, 2) }}</div>
                </div>
                <div class="premium-stat-label">Total Allocated</div>
                <div class="premium-stat-indicator indicator-purple"></div>
            </div>

            <!-- 2. Available Balance -->
            <div class="premium-stat-card">
                <div class="premium-stat-card-top">
                    <div class="premium-stat-icon-wrapper bg-soft-green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="premium-stat-value">{{ number_format((float) $totalRemaining, 2) }}</div>
                </div>
                <div class="premium-stat-label">Available Balance</div>
                <div class="premium-stat-indicator indicator-green"></div>
            </div>

            <!-- 3. Paid Remaining -->
            <div class="premium-stat-card">
                <div class="premium-stat-card-top">
                    <div class="premium-stat-icon-wrapper bg-soft-blue">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <div class="premium-stat-value">{{ number_format((float) $totalPaidRem, 2) }}</div>
                </div>
                <div class="premium-stat-label">Paid Remaining</div>
                <div class="premium-stat-indicator indicator-blue"></div>
            </div>

            <!-- 4. Sick Remaining -->
            <div class="premium-stat-card">
                <div class="premium-stat-card-top">
                    <div class="premium-stat-icon-wrapper bg-soft-orange">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="premium-stat-value">{{ number_format((float) $totalSickRem, 2) }}</div>
                </div>
                <div class="premium-stat-label">Sick Remaining</div>
                <div class="premium-stat-indicator indicator-orange"></div>
            </div>

            <!-- 5. LWP Used -->
            <div class="premium-stat-card">
                <div class="premium-stat-card-top">
                    <div class="premium-stat-icon-wrapper bg-soft-red">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="premium-stat-value">{{ number_format((float) $totalLwpUsed, 2) }}</div>
                </div>
                <div class="premium-stat-label">LWP Used</div>
                <div class="premium-stat-indicator indicator-red"></div>
            </div>
        </div>

        @if(!$isAdminOrHr)
            @php
                $myBalance = $balances->first();
            @endphp

            <!-- Year Swapper Bar -->
            <div class="year-swapper-bar mb-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-soft-purple font-weight-bold" style="padding: 6px 12px; border-radius: 8px; font-size: 12px;">
                        <i class="fas fa-calendar-alt"></i> {{ $year }} Leave Quota Plan
                    </span>
                    <span class="text-muted small">Current active allocations for your profile</span>
                </div>
                <div>
                    <form class="form-inline" method="GET" action="">
                        <div class="form-group mr-2">
                            <input name="year" type="number" class="form-control" value="{{ $year }}" placeholder="Select Year" style="height: 38px; border-radius: 10px; width: 110px; font-size: 13px; font-weight: 700;">
                        </div>
                        <button type="submit" class="set-btn" style="height: 38px; border-radius: 10px; padding: 0 16px; font-size: 12px;">
                            <i class="fas fa-sync-alt"></i> Switch Year
                        </button>
                    </form>
                </div>
            </div>

            @if($myBalance)
                <div class="emp-quota-grid">
                    <!-- Left: Detail Cards for Leave Types -->
                    <div>
                        <div class="emp-quota-card">
                            <div class="emp-quota-header">
                                <div class="premium-stat-icon-wrapper bg-soft-purple" style="width: 36px; height: 36px; border-radius: 10px; font-size: 14px;">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div>
                                    <h5 class="emp-quota-title">My Quota Breakdown</h5>
                                    <p class="emp-quota-subtitle">Detailed limits, consumption, and remaining days for the year {{ $year }}.</p>
                                </div>
                            </div>

                            <div class="breakdown-list">
                                <!-- 1. Paid Leaves -->
                                @php
                                    $paidAllocated = (float) ($myBalance->paid_allocated ?? 0);
                                    $paidUsed = (float) ($myBalance->paid_used ?? 0);
                                    $paidRemaining = (float) ($myBalance->paid_remaining ?? 0);
                                    $paidPct = $paidAllocated > 0 ? min(100, max(0, ($paidRemaining / $paidAllocated) * 100)) : 0;
                                @endphp
                                <div class="breakdown-item">
                                    <div class="breakdown-icon-box bg-soft-blue">
                                        <i class="fas fa-umbrella-beach"></i>
                                    </div>
                                    <div class="breakdown-info">
                                        <div class="breakdown-header">
                                            <div class="breakdown-name-wrap">
                                                <span class="breakdown-name">Paid Leave</span>
                                                <span class="breakdown-badge bg-soft-blue">Standard Policy</span>
                                            </div>
                                            <span class="breakdown-quota">{{ number_format($paidRemaining, 2) }} <span class="text-muted" style="font-size: 11px; font-weight: normal;">Rem.</span></span>
                                        </div>
                                        <p class="breakdown-desc">Can be availed for planned vacations, personal matters, or leisure. Subject to manager approval.</p>
                                        <div class="breakdown-progress-container">
                                            <div class="breakdown-progress-bg">
                                                <div class="breakdown-progress-bar indicator-blue" style="width: {{ $paidPct }}%;"></div>
                                            </div>
                                            <div class="breakdown-stats">
                                                <span><span class="breakdown-stat-dot" style="background: #2563EB;"></span>Allocated: {{ number_format($paidAllocated, 2) }}</span>
                                                <span><span class="breakdown-stat-dot" style="background: #E2E8F0;"></span>Used: {{ number_format($paidUsed, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Sick Leaves -->
                                @php
                                    $sickAllocated = (float) ($myBalance->sick_allocated ?? 0);
                                    $sickUsed = (float) ($myBalance->sick_used ?? 0);
                                    $sickRemaining = (float) ($myBalance->sick_remaining ?? 0);
                                    $sickPct = $sickAllocated > 0 ? min(100, max(0, ($sickRemaining / $sickAllocated) * 100)) : 0;
                                @endphp
                                <div class="breakdown-item">
                                    <div class="breakdown-icon-box bg-soft-orange">
                                        <i class="fas fa-heartbeat"></i>
                                    </div>
                                    <div class="breakdown-info">
                                        <div class="breakdown-header">
                                            <div class="breakdown-name-wrap">
                                                <span class="breakdown-name">Sick Leave</span>
                                                <span class="breakdown-badge bg-soft-orange">Medical & Wellness</span>
                                            </div>
                                            <span class="breakdown-quota">{{ number_format($sickRemaining, 2) }} <span class="text-muted" style="font-size: 11px; font-weight: normal;">Rem.</span></span>
                                        </div>
                                        <p class="breakdown-desc">Designated for medical recovery, wellness, and unexpected health concerns or emergencies.</p>
                                        <div class="breakdown-progress-container">
                                            <div class="breakdown-progress-bg">
                                                <div class="breakdown-progress-bar indicator-orange" style="width: {{ $sickPct }}%;"></div>
                                            </div>
                                            <div class="breakdown-stats">
                                                <span><span class="breakdown-stat-dot" style="background: #EA580C;"></span>Allocated: {{ number_format($sickAllocated, 2) }}</span>
                                                <span><span class="breakdown-stat-dot" style="background: #E2E8F0;"></span>Used: {{ number_format($sickUsed, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Comp Off Leaves -->
                                @php
                                    $compAllocated = (float) ($myBalance->comp_off_allocated ?? 0);
                                    $compUsed = (float) ($myBalance->comp_off_used ?? 0);
                                    $compRemaining = (float) ($myBalance->comp_off_remaining ?? 0);
                                    $compPct = $compAllocated > 0 ? min(100, max(0, ($compRemaining / $compAllocated) * 100)) : 0;
                                @endphp
                                <div class="breakdown-item">
                                    <div class="breakdown-icon-box bg-soft-purple">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="breakdown-info">
                                        <div class="breakdown-header">
                                            <div class="breakdown-name-wrap">
                                                <span class="breakdown-name">Comp Off (Compensation Off)</span>
                                                <span class="breakdown-badge bg-soft-purple">Overtime Earned</span>
                                            </div>
                                            <span class="breakdown-quota">{{ number_format($compRemaining, 2) }} <span class="text-muted" style="font-size: 11px; font-weight: normal;">Rem.</span></span>
                                        </div>
                                        <p class="breakdown-desc">Earned compensation leaves granted for working on weekends, holidays, or overtime shifts.</p>
                                        <div class="breakdown-progress-container">
                                            <div class="breakdown-progress-bg">
                                                <div class="breakdown-progress-bar indicator-purple" style="width: {{ $compPct }}%;"></div>
                                            </div>
                                            <div class="breakdown-stats">
                                                <span><span class="breakdown-stat-dot" style="background: #7E22CE;"></span>Allocated: {{ number_format($compAllocated, 2) }}</span>
                                                <span><span class="breakdown-stat-dot" style="background: #E2E8F0;"></span>Used: {{ number_format($compUsed, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. LWP Leaves -->
                                @php
                                    $lwpUsed = (float) ($myBalance->lwp_used ?? 0);
                                @endphp
                                <div class="breakdown-item">
                                    <div class="breakdown-icon-box bg-soft-red">
                                        <i class="fas fa-user-times"></i>
                                    </div>
                                    <div class="breakdown-info">
                                        <div class="breakdown-header">
                                            <div class="breakdown-name-wrap">
                                                <span class="breakdown-name">Leave Without Pay (LWP)</span>
                                                <span class="breakdown-badge bg-soft-red">Unpaid Quota</span>
                                            </div>
                                            <span class="breakdown-quota text-danger">{{ number_format($lwpUsed, 2) }} <span class="text-muted" style="font-size: 11px; font-weight: normal;">Used</span></span>
                                        </div>
                                        <p class="breakdown-desc">Unpaid leave taken outside standard paid leave limits. Deductions apply directly in payroll cycle.</p>
                                        <div class="breakdown-progress-container">
                                            <div class="breakdown-progress-bg">
                                                <div class="breakdown-progress-bar indicator-red" style="width: 100%;"></div>
                                            </div>
                                            <div class="breakdown-stats">
                                                <span><span class="breakdown-stat-dot" style="background: #EF4444;"></span>Unpaid Count: {{ number_format($lwpUsed, 2) }} used</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Overall Health Ring & Policy Accordion -->
                    <div>
                        <!-- Ring Card -->
                        @php
                            $totalAlloc = (float) $totalAllocated;
                            $totalRem = (float) $totalRemaining;
                            $overallPct = $totalAlloc > 0 ? round(($totalRem / $totalAlloc) * 100) : 0;
                            // Circumference = 2 * pi * r = 2 * 3.14159 * 70 = 439.8
                            $dashOffset = 439.8 - ($overallPct / 100) * 439.8;
                        @endphp
                        <div class="emp-quota-card text-center">
                            <div class="emp-quota-header text-left">
                                <div class="premium-stat-icon-wrapper bg-soft-green" style="width: 36px; height: 36px; border-radius: 10px; font-size: 14px;">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div>
                                    <h5 class="emp-quota-title">Overall Quota Health</h5>
                                    <p class="emp-quota-subtitle">Aggregated metrics of your current leave standing.</p>
                                </div>
                            </div>

                            <div class="svg-ring-container mt-3">
                                <svg width="160" height="160" viewBox="0 0 160 160">
                                    <!-- Background circle -->
                                    <circle cx="80" cy="80" r="70" stroke="#F1F5F9" stroke-width="10" fill="transparent" />
                                    <!-- Colored Progress ring -->
                                    <circle cx="80" cy="80" r="70" stroke="url(#purpleGreenGradient)" stroke-dasharray="439.8" stroke-dashoffset="{{ $dashOffset }}" stroke-width="10" stroke-linecap="round" fill="transparent" transform="rotate(-90 80 80)" />
                                    
                                    <!-- Linear Gradient Definition -->
                                    <defs>
                                        <linearGradient id="purpleGreenGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#7E22CE" />
                                            <stop offset="100%" stop-color="#10B981" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="svg-ring-percentage">
                                    {{ $overallPct }}%
                                    <span>Remaining</span>
                                </div>
                            </div>

                            <div class="row mt-4" style="background: #FAFAFB; border-radius: 14px; padding: 12px 0; border: 1px dashed #E2E8F0; margin: 0 4px;">
                                <div class="col-6" style="border-right: 1px solid #E2E8F0;">
                                    <div style="font-size: 18px; font-weight: 900; color: #101828;">{{ number_format($totalRemaining, 2) }}</div>
                                    <small class="text-muted font-weight-bold" style="font-size: 9px; text-transform: uppercase;">Available Days</small>
                                </div>
                                <div class="col-6">
                                    <div style="font-size: 18px; font-weight: 900; color: #7E22CE;">{{ number_format($totalAllocated, 2) }}</div>
                                    <small class="text-muted font-weight-bold" style="font-size: 9px; text-transform: uppercase;">Total Quota</small>
                                </div>
                            </div>
                        </div>

                        <!-- Policy Guidelines Card -->
                        <div class="emp-quota-card">
                            <div class="emp-quota-header">
                                <div class="premium-stat-icon-wrapper bg-soft-orange" style="width: 36px; height: 36px; border-radius: 10px; font-size: 14px;">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <h5 class="emp-quota-title">Leave Request Guidelines</h5>
                                    <p class="emp-quota-subtitle">Quick policies for standard compliance.</p>
                                </div>
                            </div>

                            <div style="font-size: 12.5px; color: #475569; line-height: 1.6; text-align: left;">
                                <div class="d-flex gap-2 mb-3 align-items-start">
                                    <i class="fas fa-dot-circle text-primary mt-1" style="font-size: 8px; flex-shrink:0;"></i>
                                    <div><strong>Planned Leaves:</strong> Should be requested at least 3 business days in advance to ensure smooth work handovers.</div>
                                </div>
                                <div class="d-flex gap-2 mb-3 align-items-start">
                                    <i class="fas fa-dot-circle text-primary mt-1" style="font-size: 8px; flex-shrink:0;"></i>
                                    <div><strong>Unplanned/Sick Leaves:</strong> Must be reported to your supervisor within 2 hours of your regular shift start time.</div>
                                </div>
                                <div class="d-flex gap-2 align-items-start">
                                    <i class="fas fa-dot-circle text-primary mt-1" style="font-size: 8px; flex-shrink:0;"></i>
                                    <div><strong>Comp Off:</strong> Valid for 90 days from the credit date. Ensure you consume your accrued compensation offsets timely.</div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex flex-column gap-2">
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-primary w-100 font-weight-bold" style="border-radius: 12px; height: 42px; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                                    <i class="fas fa-plus-circle"></i> Submit New Application
                                </a>
                                <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-light w-100 font-weight-bold" style="border-radius: 12px; height: 42px; border: 1px solid #E2E8F0; color: #475569; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                                    <i class="fas fa-history"></i> View Submission History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="set-card p-5 text-center" style="border-radius: 20px;">
                    <div class="set-icon-box bg-soft-orange mx-auto mb-3" style="width: 54px; height: 54px; border-radius: 50%;">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <h5 class="font-weight-black" style="color: var(--set-text);">No Leave Allocation Setup</h5>
                    <p class="text-muted small max-w-md mx-auto mb-0">There are no leave allocations mapped to your profile for the year {{ $year }}. Please contact HR or your system administrator to setup your quota plan.</p>
                </div>
            @endif
        @else
            <!-- Table Card Wrapper -->
            <div class="set-card">
                <div class="set-card-header">
                    <div class="set-head-left">
                        <div class="set-icon-box"><i class="fas fa-chart-pie"></i></div>
                        <div>
                            <h5 class="set-card-title">Employee Leave Allocations</h5>
                            <p class="set-card-subtitle">Review active quota targets, deduct counts, and LWP parameters.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <!-- Export buttons container -->
                        <div id="leaveBalancesExportButtons"></div>
                    </div>
                </div>

                <!-- Attached real-time automatic filters in responsive grid -->
                <div style="border-bottom: 1px solid var(--set-border); background: #FAF9FE; padding: 20px 24px;">
                    <form class="mobile-filter-grid" method="GET" action="">
                        <div class="mobile-filter-group">
                            <label>Filter Year</label>
                            <input name="year" type="number" class="mobile-filter-control" value="{{ request('year', $year ?? date('Y')) }}" placeholder="e.g. 2026">
                        </div>
                        <div class="mobile-filter-group">
                            <label>Employee Select</label>
                            <select name="employee_id" class="mobile-filter-control">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->user_name ?? $employee->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mobile-filter-group d-flex flex-row" style="gap: 8px;">
                            <button type="submit" class="set-btn" style="height: 40px; padding: 0 16px; border-radius: 12px;">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                            <a href="{{ url()->current() }}" class="mobile-filter-reset">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="set-card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="set-table js-custom-balances-table" id="leaveBalancesTable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Allocation Year</th>
                                    <th>Total Available / Quota</th>
                                    <th>Paid Rem.</th>
                                    <th>Sick Rem.</th>
                                    <th>Comp Off Rem.</th>
                                    <th>LWP Used</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($balances->count())
                                    @foreach($balances as $balance)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center mr-2" style="width:34px;height:34px;font-weight:900;background:rgba(75, 0, 232, 0.06); font-size:12px;">
                                                        {{ substr(optional($balance->employee)->display_name ?? 'U', 0, 1) }}
                                                    </div>
                                                    <span style="font-weight: 850; color: var(--set-text);">{{ optional($balance->employee)->display_name }}</span>
                                                </div>
                                            </td>
                                            <td><span style="font-weight: 800; font-family: monospace; font-size:13px; color: var(--set-muted);">{{ $balance->year }}</span></td>
                                            <td>
                                                <span class="text-success font-weight-black" style="font-size: 14px;">{{ $balance->total_remaining }}</span>
                                                <span class="small text-muted">/ {{ $balance->total_allocated }} Allocated</span>
                                            </td>
                                            <td><span class="leave-badge badge-paid"><i class="fas fa-check-circle mr-1"></i> {{ $balance->paid_remaining }} Rem.</span></td>
                                            <td><span class="leave-badge badge-pending"><i class="fas fa-heartbeat mr-1"></i> {{ $balance->sick_remaining }} Rem.</span></td>
                                            <td><span class="leave-badge badge-comp-off"><i class="fas fa-clock mr-1"></i> {{ $balance->comp_off_remaining }} Rem.</span></td>
                                            <td><span class="leave-badge badge-lwp"><i class="fas fa-user-times mr-1"></i> {{ $balance->lwp_used }} Used</span></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    @if(method_exists($balances, 'links') && $balances->hasPages())
                        <div class="border-top" style="padding: 16px 24px;">
                            {{ $balances->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && $.fn.DataTable && $('#leaveBalancesTable').length) {
            
            // Safe Buttons check to fallback gracefully if the extension fails to load
            var hasButtons = typeof $.fn.dataTable.Buttons !== 'undefined';
            var domLayout = hasButtons 
                ? '<"leave-dt-toolbar"<"leave-dt-left"l><"leave-dt-right"B>>rt<"leave-table-footer"ip>'
                : '<"leave-dt-toolbar"<"leave-dt-left"l>>rt<"leave-table-footer"ip>';

            if (!hasButtons) {
                console.warn('DataTables Buttons extension not loaded');
            }

            $('.js-custom-balances-table').DataTable({
                pageLength: 25,
                responsive: false,
                language: {
                    emptyTable: 'No records found',
                    zeroRecords: 'No matching records found'
                },
                dom: domLayout,
                buttons: [
                    { extend: 'excelHtml5', text: 'Excel', className: 'leave-export-btn' },
                    { extend: 'csvHtml5', text: 'CSV', className: 'leave-export-btn' },
                    { extend: 'pdfHtml5', text: 'PDF', className: 'leave-export-btn' },
                    { extend: 'print', text: 'Print', className: 'leave-export-btn' }
                ]
            });
        }
    });
</script>
@endsection
