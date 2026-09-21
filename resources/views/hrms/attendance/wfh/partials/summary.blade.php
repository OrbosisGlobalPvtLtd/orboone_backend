<div class="orb-summary-grid">
    <div class="orb-summary-card">
        <div class="orb-summary-label">Total Requests</div>
        <div class="orb-summary-value">{{ $stats['total'] ?? 0 }}</div>
    </div>
    <div class="orb-summary-card">
        <div class="orb-summary-label">Pending</div>
        <div class="orb-summary-value text-warning">{{ $stats['pending'] ?? 0 }}</div>
    </div>
    <div class="orb-summary-card">
        <div class="orb-summary-label">Approved</div>
        <div class="orb-summary-value text-success">{{ $stats['approved'] ?? 0 }}</div>
    </div>
    <div class="orb-summary-card">
        <div class="orb-summary-label">Rejected</div>
        <div class="orb-summary-value text-danger">{{ $stats['rejected'] ?? 0 }}</div>
    </div>
    <div class="orb-summary-card">
        <div class="orb-summary-label">Company Assigned</div>
        <div class="orb-summary-value" style="color: var(--orb-primary, #4B00E8);">{{ $stats['company_assigned'] ?? 0 }}</div>
    </div>
    <div class="orb-summary-card">
        <div class="orb-summary-label">Converted To LWP</div>
        <div class="orb-summary-value text-danger">{{ $stats['lwp'] ?? 0 }}</div>
    </div>
</div>
