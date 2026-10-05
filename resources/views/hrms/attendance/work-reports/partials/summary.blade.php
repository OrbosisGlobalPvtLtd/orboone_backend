<!-- Summary KPI Cards matching Violations design -->
<div class="report-kpi-grid audit-kpi-grid">
    <div class="att-kpi tone-purple">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-clipboard-check"></i></div>
            <div class="att-kpi-value">{{ number_format($statsSummary['total_reports'] ?? count($workLogs)) }}</div>
        </div>
        <div class="att-kpi-label">Work Reports</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-warning">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-users"></i></div>
            <div class="att-kpi-value">{{ number_format($statsSummary['unique_employees'] ?? count($employeeSummaries)) }}</div>
        </div>
        <div class="att-kpi-label">Active Staff</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-amber">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-clock"></i></div>
            <div class="att-kpi-value" style="font-size: 19px; line-height: 1.2;">{{ $statsSummary['total_gross_formatted'] ?? '0 mins' }}</div>
        </div>
        <div class="att-kpi-label">Gross Work Duration</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-success">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-tasks"></i></div>
            <div class="att-kpi-value">{{ number_format($statsSummary['total_tasks'] ?? 0) }}</div>
        </div>
        <div class="att-kpi-label">Structured Tasks</div>
        <div class="att-kpi-line"></div>
    </div>

    <div class="att-kpi tone-orange">
        <div class="att-kpi-top">
            <div class="att-kpi-icon"><i class="fas fa-laptop-house"></i></div>
            <div class="att-kpi-value" style="font-size: 19px; line-height: 1.2;">
                <span>{{ $statsSummary['wfo_count'] ?? 0 }}</span>
                <span style="font-size: 14px; opacity: 0.5;">/</span>
                <span>{{ $statsSummary['wfh_count'] ?? 0 }}</span>
            </div>
        </div>
        <div class="att-kpi-label">WFO / WFH Modes</div>
        <div class="att-kpi-line"></div>
    </div>
</div>

