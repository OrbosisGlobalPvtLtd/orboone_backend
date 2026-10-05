<!-- Summary KPI Cards -->
<div class="audit-kpi-grid">
    <div class="att-kpi tone-purple">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="att-kpi-value">{{ number_format($summaryMetrics['total_today'] ?? 0) }}</div>
        </div>
        <div class="att-kpi-label">Total Violations</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-warning">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-user-clock"></i></div>
            <div class="att-kpi-value">{{ number_format($summaryMetrics['late_today'] ?? 0) }}</div>
        </div>
        <div class="att-kpi-label">Late Login</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-orange">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-running"></i></div>
            <div class="att-kpi-value">{{ number_format($summaryMetrics['early_today'] ?? 0) }}</div>
        </div>
        <div class="att-kpi-label">Early Logout</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-danger">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-user-times"></i></div>
            <div class="att-kpi-value">{{ number_format($summaryMetrics['missed_today'] ?? 0) }}</div>
        </div>
        <div class="att-kpi-label">Missed Punch</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-amber">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-adjust"></i></div>
            <div class="att-kpi-value">{{ number_format($summaryMetrics['half_day_applied'] ?? 0) }}</div>
        </div>
        <div class="att-kpi-label">Half Day Applied</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-blocked">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-calendar-minus"></i></div>
            <div class="att-kpi-value">{{ number_format($summaryMetrics['lwp_applied'] ?? 0) }}</div>
        </div>
        <div class="att-kpi-label">LWP Applied</div>
        <div class="att-kpi-line"></div>
    </div>
</div>
