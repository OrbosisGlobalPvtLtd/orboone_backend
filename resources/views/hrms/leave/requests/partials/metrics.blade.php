<!-- Leaves Calculation Metadata -->
@php
    $paidRemaining = (float) ($centralBalance['leave_balance']['total_remaining_paid'] ?? 0);
    $paidUsed = (float) ($ledgerFields['used_pl'] ?? $allocation?->paid_used ?? 0);
    $sickRemaining = (float) ($centralBalance['leave_balance']['total_remaining_sick'] ?? 0);
    $sickUsed = (float) ($ledgerFields['used_sl'] ?? $allocation?->sick_used ?? 0);
    $compRemaining = (float) ($centralBalance['leave_balance']['total_remaining_comp_off'] ?? 0);
    $lwpUsed = $allocation ? (float) ($allocation->lwp_used ?? 0) : 0.0;
    $remainingThisMonth = $paidRemaining;

    // Requests Counts
    $countQuery = DB::table('leave_requests');
    if (!$isAdminOrHr && $employee) {
        $countQuery->where('employee_id', $employee->id);
    } elseif ($isAdminOrHr && request('employee_id')) {
        $countQuery->where('employee_id', request('employee_id'));
    }
    $pendingCount = (clone $countQuery)->where('status', 'pending')->count();
    $approvedCount = (clone $countQuery)->where('status', 'approved')->count();
    $rejectedCount = (clone $countQuery)->where('status', 'rejected')->count();
    $emergencyUsed = (clone $countQuery)->where('status', 'approved')->where('emergency_leave', 1)->count();
    $totalCount = (clone $countQuery)->count();
@endphp

<!-- 1. PRIMARY QUOTA CARDS -->
<div class="leave-metric-grid">
    @if($employee)
    <!-- 1. Paid Leave Remaining -->
    <div class="leave-metric" style="--metric-color:#10B981;--metric-soft:#ECFDF5;">
        <div class="leave-metric-top">
            <div class="leave-metric-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="leave-metric-value">{{ number_format((float) $paidRemaining, 2) }}</div>
        </div>
        <div class="leave-metric-label" title="Annual remaining Paid Leaves">Paid Remaining</div>
        <div class="leave-metric-line"></div>
    </div>
    
    <!-- 2. Sick Leave Remaining -->
    <div class="leave-metric" style="--metric-color:#EF4444;--metric-soft:#FEF2F2;">
        <div class="leave-metric-top">
            <div class="leave-metric-icon">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="leave-metric-value">{{ number_format((float) $sickRemaining, 2) }}</div>
        </div>
        <div class="leave-metric-label" title="Annual remaining Sick Leaves">Sick Remaining</div>
        <div class="leave-metric-line"></div>
    </div>

    <!-- 3. Comp-Off Balance -->
    <div class="leave-metric" style="--metric-color:#8B5CF6;--metric-soft:#F5F3FF;">
        <div class="leave-metric-top">
            <div class="leave-metric-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="leave-metric-value">{{ number_format((float) $compRemaining, 2) }}</div>
        </div>
        <div class="leave-metric-label" title="Available Comp-Off balance">Comp-Off Balance</div>
        <div class="leave-metric-line"></div>
    </div>

    <!-- 4. Available PL Balance -->
    <div class="leave-metric" style="--metric-color:#0284C7;--metric-soft:#E0F2FE;">
        <div class="leave-metric-top">
            <div class="leave-metric-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="leave-metric-value">{{ number_format((float) $remainingThisMonth, 2) }}</div>
        </div>
        <div class="leave-metric-label" title="Current accumulated paid leave balance available to use">Available PL Balance</div>
        <div class="leave-metric-line"></div>
    </div>
    @else
    <!-- Admin Directory Metrics -->
    <!-- 1. Total Requests -->
    <div class="leave-metric" style="--metric-color:#4F46E5;--metric-soft:#EEF2FF;">
        <div class="leave-metric-top">
            <div class="leave-metric-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="leave-metric-value">{{ $totalCount }}</div>
        </div>
        <div class="leave-metric-label">Total Requests</div>
        <div class="leave-metric-line"></div>
    </div>

    <!-- 2. Pending Review -->
    <div class="leave-metric" style="--metric-color:#D97706;--metric-soft:#FEF3C7;">
        <div class="leave-metric-top">
            <div class="leave-metric-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="leave-metric-value">{{ $pendingCount }}</div>
        </div>
        <div class="leave-metric-label">Pending Review</div>
        <div class="leave-metric-line"></div>
    </div>

    <!-- 3. Approved Leaves -->
    <div class="leave-metric" style="--metric-color:#059669;--metric-soft:#ECFDF5;">
        <div class="leave-metric-top">
            <div class="leave-metric-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="leave-metric-value">{{ $approvedCount }}</div>
        </div>
        <div class="leave-metric-label">Approved Leaves</div>
        <div class="leave-metric-line"></div>
    </div>

    <!-- 4. Rejected Leaves -->
    <div class="leave-metric" style="--metric-color:#DC2626;--metric-soft:#FEE2E2;">
        <div class="leave-metric-top">
            <div class="leave-metric-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="leave-metric-value">{{ $rejectedCount }}</div>
        </div>
        <div class="leave-metric-label">Rejected Leaves</div>
        <div class="leave-metric-line"></div>
    </div>
    @endif
</div>

<!-- 2. SECONDARY STATS COUNTER ROW -->
<div class="row mb-4">
    <div class="col-6 col-md-3 col-lg-2 mb-2">
        <div class="counter-pill-item">
            <span class="counter-pill-lbl"><i class="fas fa-check-circle text-indigo"></i> Paid Used</span>
            <span class="counter-pill-num" style="background: #EEF2FF; color: #4F46E5;">{{ number_format((float) $paidUsed, 1) }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2 mb-2">
        <div class="counter-pill-item">
            <span class="counter-pill-lbl"><i class="fas fa-heartbeat text-amber"></i> SL Used</span>
            <span class="counter-pill-num" style="background: #FEF3C7; color: #D97706;">{{ number_format((float) $sickUsed, 1) }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2 mb-2">
        <div class="counter-pill-item">
            <span class="counter-pill-lbl"><i class="fas fa-user-clock text-slate"></i> LWP Days</span>
            <span class="counter-pill-num" style="background: #F1F5F9; color: #475569;">{{ number_format((float) $lwpUsed, 1) }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2 mb-2">
        <div class="counter-pill-item">
            <span class="counter-pill-lbl"><i class="fas fa-spinner text-warning"></i> Pending</span>
            <span class="counter-pill-num" style="background: #FEF3C7; color: #B45309;">{{ $pendingCount }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2 mb-2">
        <div class="counter-pill-item">
            <span class="counter-pill-lbl"><i class="fas fa-thumbs-up text-success"></i> Approved</span>
            <span class="counter-pill-num" style="background: #D1FAE5; color: #047857;">{{ $approvedCount }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2 mb-2">
        <div class="counter-pill-item">
            <span class="counter-pill-lbl"><i class="fas fa-exclamation-triangle text-pink"></i> Emergency</span>
            <span class="counter-pill-num" style="background: #FCE7F3; color: #DB2777;">{{ $emergencyUsed }}</span>
        </div>
    </div>
</div>
