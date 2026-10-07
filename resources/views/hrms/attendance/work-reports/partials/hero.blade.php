<!-- Premium Header Area -->
<div class="report-header-premium">
    <div class="title-area">
        <div class="header-kicker">
            <i class="fas fa-clipboard-list"></i> Daily Work Logging
        </div>
        <h3>Daily Work Reports</h3>
        <p>Track, manage, and review employee tasks, daily progress summaries, and work details.</p>
    </div>

    <div class="d-flex align-items-center flex-wrap" style="gap:12px;">
        <!-- View Mode Switcher Toggle Pill (Listing default active) -->
        <div class="view-switcher-pill">
            <button type="button" class="view-switcher-btn active" id="btnTableView" onclick="switchWorkReportView('table')">
                <i class="fas fa-list-ul"></i> Listing View
            </button>
            <button type="button" class="view-switcher-btn" id="btnCardsView" onclick="switchWorkReportView('cards')">
                <i class="fas fa-th-large"></i> Employee Summaries
            </button>
        </div>

        {{-- @if($isAdminOrManager)
        <a href="{{ route('attendances.daily') }}" class="report-btn-pill">
            <i class="fas fa-calendar-check"></i>
            Daily Attendance
        </a>
        @endif --}}
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 14px; background: #F0FDF4; border-left: 5px solid #22C55E !important;">
    <i class="fas fa-check-circle text-success mr-2"></i> {{ session('success') }}
</div>
@endif
