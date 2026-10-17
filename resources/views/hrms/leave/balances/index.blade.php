@extends('layouts.panel')

@section('page_title', 'Leave Balances')

@section('_head')
@include('settings.partials.styles')

<style>
    .set-page {
        padding: 24px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .leave-page-container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        box-sizing: border-box !important;
    }

    @media (max-width: 768px) {
        .set-page {
            padding: 14px !important;
        }
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

    /* Premium Stat Cards (styled exactly like Image 2) */
    .leave-metric-grid,
    .premium-stat-row {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)) !important;
        gap: 14px !important;
        margin-bottom: 22px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    @media (max-width: 768px) {
        .leave-metric-grid,
        .premium-stat-row {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px !important;
        }
    }

    @media (max-width: 420px) {
        .leave-metric-grid,
        .premium-stat-row {
            grid-template-columns: 1fr !important;
        }
    }

    .leave-metric,
    .premium-stat-card {
        background: #FFFFFF !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        border-radius: 18px !important;
        padding: 16px 16px 12px !important;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .055) !important;
        position: relative !important;
        overflow: hidden !important;
        min-height: 96px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        transition: transform 0.22s ease, box-shadow 0.22s ease !important;
    }

    .leave-metric:hover,
    .premium-stat-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 14px 30px rgba(16, 24, 40, .09) !important;
    }

    /* Top-Right Soft Circle Watermark Bubble */
    .leave-metric::after,
    .premium-stat-card::after {
        content: "" !important;
        position: absolute !important;
        right: -22px !important;
        top: -30px !important;
        width: 86px !important;
        height: 86px !important;
        border-radius: 50% !important;
        background: var(--metric-soft, #F4F2FF) !important;
        pointer-events: none !important;
        z-index: 0 !important;
    }

    .leave-metric-top,
    .premium-stat-card-top {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        position: relative !important;
        z-index: 1 !important;
        width: 100% !important;
    }

    .leave-metric-icon,
    .premium-stat-icon-wrapper {
        width: 38px !important;
        height: 38px !important;
        border-radius: 13px !important;
        background: var(--metric-soft, #F4F2FF) !important;
        color: var(--metric-color, var(--orb-primary, #4B00E8)) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        flex-shrink: 0 !important;
    }

    .leave-metric-value,
    .premium-stat-value {
        font-size: 26px !important;
        font-weight: 950 !important;
        color: #101828 !important;
        line-height: 1 !important;
        letter-spacing: -0.02em !important;
        text-align: right !important;
    }

    .leave-metric-label,
    .premium-stat-label {
        font-size: 11px !important;
        font-weight: 950 !important;
        color: #475467 !important;
        text-transform: uppercase !important;
        margin-top: 14px !important;
        position: relative !important;
        z-index: 1 !important;
        letter-spacing: 0.04em !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .leave-metric-line,
    .premium-stat-indicator {
        position: relative !important;
        height: 3px !important;
        border-radius: 999px !important;
        background: linear-gradient(90deg, var(--metric-color, var(--orb-primary, #4B00E8)), transparent) !important;
        margin-top: 8px !important;
        width: 100% !important;
        z-index: 1 !important;
    }

    /* Color palettes */
    .bg-soft-purple { background: #F4F2FF !important; color: #7E22CE !important; }
    .bg-soft-green { background: #ECFDF5 !important; color: #10B981 !important; }
    .bg-soft-blue { background: #EFF6FF !important; color: #2563EB !important; }
    .bg-soft-orange { background: #FFF7ED !important; color: #EA580C !important; }
    .bg-soft-red { background: #FEF2F2 !important; color: #EF4444 !important; }

    .indicator-purple { background: linear-gradient(90deg, #7E22CE, transparent) !important; }
    .indicator-green { background: linear-gradient(90deg, #10B981, transparent) !important; }
    .indicator-blue { background: linear-gradient(90deg, #2563EB, transparent) !important; }
    .indicator-orange { background: linear-gradient(90deg, #EA580C, transparent) !important; }
    .indicator-red { background: linear-gradient(90deg, #EF4444, transparent) !important; }

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

        @php
            $myBalance = $allocation ?? ($balances instanceof \Illuminate\Support\Collection ? $balances->first() : null);
            $isLedgerBacked = !empty($ledgerBalances);
            $isConfirmed = optional($targetEmployee)->is_permanent ?? true;

            $totalAlloc = $myBalance ? (float) $myBalance->total_allocated : 0.0;
            $paidAlloc = $myBalance ? (float) $myBalance->paid_allocated : 0.0;
            $sickAlloc = $myBalance ? (float) $myBalance->sick_allocated : 0.0;
            $compAlloc = $myBalance ? (float) $myBalance->comp_off_allocated : 0.0;

            $totalRem = $myBalance ? (float) $myBalance->total_remaining : 0.0;
            $paidRem = $myBalance ? (float) $myBalance->paid_remaining : 0.0;
            $sickRem = $myBalance ? (float) $myBalance->sick_remaining : 0.0;
            $compRem = $myBalance ? (float) $myBalance->comp_off_remaining : 0.0;

            if (in_array((int) Carbon\Carbon::now('Asia/Kolkata')->month, [11, 12], true) && $totalRem > 10.0) {
                $totalRem = round($totalRem * 0.5, 2);
                $paidRem = round($paidRem * 0.5, 2);
                $sickRem = round($sickRem * 0.5, 2);
                $compRem = round($compRem * 0.5, 2);
            }

            $monthlyPaidBalance = $monthlyQuotaInfo ? (float) ($monthlyQuotaInfo['total_monthly_remaining_paid'] ?? 0.0) : ($myBalance ? (float) $myBalance->paid_remaining : 0.0);
            $monthlyCarryForward = $monthlyQuotaInfo ? (float) ($monthlyQuotaInfo['carry_forward_available'] ?? 0.0) : 0.0;
            if ($isLedgerBacked) {
                $paidRem = (float) $ledgerBalances['available_pl'];
                $sickRem = (float) $ledgerBalances['available_sl'];
                $totalRem = $paidRem + $sickRem;
                $monthlyPaidBalance = 0.0;
            }
        @endphp

        <!-- Premium Stat Cards (Target Employee's Personal Leave Balance) -->
        <div class="leave-metric-grid">
            <!-- 1. Total Leave Allocated -->
            <div class="leave-metric" style="--metric-color:#7C3AED;--metric-soft:#F4F2FF;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="leave-metric-value">{{ number_format((float) $totalAlloc, 2) }}</div>
                </div>
                <div class="leave-metric-label">Total Leave Allocated</div>
                <div class="leave-metric-line"></div>
            </div>

            <!-- 2. Total Leave Available Balance -->
            <div class="leave-metric" style="--metric-color:#16A34A;--metric-soft:#DCFCE7;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="leave-metric-value">{{ number_format((float) $totalRem, 2) }}</div>
                </div>
                <div class="leave-metric-label">Total Available Balance</div>
                <div class="leave-metric-line"></div>
            </div>

            <!-- 3. Annual Paid Leave Remaining -->
            <div class="leave-metric" style="--metric-color:#2563EB;--metric-soft:#EFF6FF;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <div class="leave-metric-value">{{ number_format((float) $paidRem, 2) }}</div>
                </div>
                <div class="leave-metric-label">Paid Leave (PL) Remaining</div>
                <div class="leave-metric-line"></div>
            </div>

            <!-- 4. Sick Leave Remaining -->
            <div class="leave-metric" style="--metric-color:#EA580C;--metric-soft:#FFF7ED;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="leave-metric-value">{{ number_format((float) $sickRem, 2) }}</div>
                </div>
                <div class="leave-metric-label">Sick Leave (SL) Remaining</div>
                <div class="leave-metric-line"></div>
            </div>

            <!-- 5. Comp-Off Balance -->
            @if($compAlloc > 0 || $compRem > 0)
            <div class="leave-metric" style="--metric-color:#8B5CF6;--metric-soft:#F5F3FF;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="leave-metric-value">{{ number_format((float) $compRem, 2) }}</div>
                </div>
                <div class="leave-metric-label">Comp-Off Balance</div>
                <div class="leave-metric-line"></div>
            </div>
            @endif

            <!-- 6. Monthly Paid Leave Balance (Non-Ledger mode only) -->
            @if(!$isLedgerBacked && isset($monthlyPaidBalance) && $monthlyPaidBalance > 0)
            <div class="leave-metric" style="--metric-color:#0284C7;--metric-soft:#E0F2FE;">
                <div class="leave-metric-top">
                    <div class="leave-metric-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="leave-metric-value">{{ number_format((float) $monthlyPaidBalance, 2) }}</div>
                </div>
                <div class="leave-metric-label">Monthly PL Balance</div>
                <div class="leave-metric-line"></div>
            </div>
            @endif
        </div>

        <!-- Year & Employee Swapper Bar -->
        <div class="year-swapper-bar mb-4">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-soft-purple font-weight-bold" style="padding: 6px 12px; border-radius: 8px; font-size: 12px;">
                    <i class="fas fa-calendar-alt"></i> {{ $year }} Leave Quota Plan
                </span>
                @if($targetEmployee)
                    <span class="text-muted small">
                        Active quota profile for: <strong class="text-dark">{{ $targetEmployee->user->name ?? $targetEmployee->display_name ?? 'My Profile' }}</strong> ({{ $targetEmployee->employee_code }})
                    </span>
                @endif
            </div>
            <div>
                <form class="form-inline d-flex align-items-center flex-wrap" method="GET" action="" style="gap: 8px;">
                    @if(!empty($canViewOthers) && isset($employees) && $employees->count() > 1)
                        <div class="form-group mb-0">
                            <select name="employee_id" class="form-control select2-searchable" style="height: 38px; border-radius: 10px; font-size: 13px; font-weight: 700; max-width: 240px;">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ ($targetEmployee && $targetEmployee->id == $emp->id) ? 'selected' : '' }}>
                                        {{ $emp->user_name ?? $emp->display_name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="form-group mb-0">
                        <input name="year" type="number" class="form-control" value="{{ $year }}" placeholder="Select Year" style="height: 38px; border-radius: 10px; width: 110px; font-size: 13px; font-weight: 700;">
                    </div>
                    <button type="submit" class="set-btn" style="height: 38px; border-radius: 10px; padding: 0 16px; font-size: 12px;">
                        <i class="fas fa-sync-alt"></i> Switch
                    </button>
                    @if(request()->filled('employee_id') || request()->filled('year'))
                        <a href="{{ route('hrms.leave.balances.index') }}" class="btn btn-light" style="height: 38px; border-radius: 10px; padding: 0 12px; font-size: 12px; display: inline-flex; align-items: center;">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
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
                                <h5 class="emp-quota-title">Quota Breakdown</h5>
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
                                        <div class="breakdown-stats flex-wrap" style="gap: 12px;">
                                             <span><span class="breakdown-stat-dot" style="background: #2563EB;"></span>Annual Allocated: {{ number_format($paidAllocated, 2) }}</span>
                                             <span><span class="breakdown-stat-dot" style="background: #64748B;"></span>Annual Used: {{ number_format($paidUsed, 2) }}</span>
                                             @if(isset($monthlyPaidBalance) && !$isLedgerBacked)
                                                 <span class="text-success font-weight-bold"><span class="breakdown-stat-dot" style="background: #10B981;"></span>Monthly Paid Leave Balance: {{ number_format($monthlyPaidBalance, 2) }} @if($monthlyCarryForward > 0)<small class="text-muted">(Includes {{ number_format($monthlyCarryForward, 2) }} Carry Forward)</small>@endif</span>
                                             @endif
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

                <!-- Right: Overall Health Ring & Policy Status -->
                <div>
                    @php
                        $totalAllocVal = (float) $totalAlloc;
                        $totalRemVal = (float) $totalRem;
                        $overallPct = $totalAllocVal > 0 ? round(($totalRemVal / $totalAllocVal) * 100) : 0;
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
                                <p class="emp-quota-subtitle">Aggregated metrics of leave standing.</p>
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
                                <div style="font-size: 18px; font-weight: 900; color: #101828;">{{ number_format($totalRem, 2) }}</div>
                                <small class="text-muted font-weight-bold" style="font-size: 9px; text-transform: uppercase;">Available Days</small>
                            </div>
                            <div class="col-6">
                                <div style="font-size: 18px; font-weight: 900; color: #7E22CE;">{{ number_format($totalAlloc, 2) }}</div>
                                <small class="text-muted font-weight-bold" style="font-size: 9px; text-transform: uppercase;">Total Quota</small>
                            </div>
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
                <p class="text-muted small max-w-md mx-auto mb-0">There are no leave allocations mapped to this profile for the year {{ $year }}. Please contact HR or your system administrator to setup the quota plan.</p>
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

            $.fn.dataTable.ext.errMode = 'none';

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
