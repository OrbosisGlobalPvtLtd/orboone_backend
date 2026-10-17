    <div class="leave-hero">
        <div class="leave-hero-content">
            <div>
                <div class="leave-hero-kicker">
                    <i class="fas fa-calendar-check"></i>
                    @if($isEmployeeRole)
                        <span>EMPLOYEE &bull; COMPENSATORY OFF</span>
                    @else
                        <span>HRMS &bull; COMPENSATORY OFF MANAGEMENT</span>
                    @endif
                </div>

                <h1 class="leave-hero-title">
                    @if($isEmployeeRole)
                        My Compensatory Off
                    @else
                        Compensatory Off Management
                    @endif
                </h1>

                <div class="leave-hero-subtitle">
                    @if($isEmployeeRole)
                        Review your earned, used, available compensatory-off leave balances and holiday work credits.
                    @else
                        Manage employee compensatory-off balances generated from holiday/weekoff work, approvals, usage and expiry tracking.
                    @endif
                </div>
            </div>
        </div>
    </div>
