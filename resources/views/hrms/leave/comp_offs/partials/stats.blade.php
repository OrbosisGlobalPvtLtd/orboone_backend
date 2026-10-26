    <!-- 4 Summary Stat Cards in Single Row on Desktop (Image 2 Design) -->
    <div class="leave-metric-grid">
        <!-- 1. Total Available / Remaining Balance -->
        <div class="leave-metric-card" style="--metric-color:#10B981;--metric-soft:#ECFDF5;">
            <div class="leave-metric-top">
                <div class="leave-metric-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="leave-metric-value">{{ number_format((float) ($stats['total_available'] ?? 0.0), 1) }}</div>
            </div>
            <div class="leave-metric-label">Available Comp-Off Balance</div>
            <div class="leave-metric-line"></div>
        </div>

        <!-- 2. Quota Allocated in Leave Plan -->
        <div class="leave-metric-card" style="--metric-color:#7C3AED;--metric-soft:#F4F2FF;">
            <div class="leave-metric-top">
                <div class="leave-metric-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="leave-metric-value">{{ number_format((float) ($stats['comp_off_allocated'] ?? 0.0), 1) }}</div>
            </div>
            <div class="leave-metric-label">Total Quota Allocated</div>
            <div class="leave-metric-line"></div>
        </div>

        <!-- 3. Total Used -->
        <div class="leave-metric-card" style="--metric-color:#2563EB;--metric-soft:#EFF6FF;">
            <div class="leave-metric-top">
                <div class="leave-metric-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="leave-metric-value">{{ number_format((float) ($stats['total_used'] ?? 0.0), 1) }}</div>
            </div>
            <div class="leave-metric-label">Total Comp-Off Used</div>
            <div class="leave-metric-line"></div>
        </div>

        <!-- 4. Total Expired -->
        <div class="leave-metric-card" style="--metric-color:#EF4444;--metric-soft:#FEF2F2;">
            <div class="leave-metric-top">
                <div class="leave-metric-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div class="leave-metric-value">{{ number_format((float) ($stats['total_expired'] ?? 0.0), 1) }}</div>
            </div>
            <div class="leave-metric-label">Total Comp-Off Expired</div>
            <div class="leave-metric-line"></div>
        </div>
    </div>
