<!-- VIEW MODE 2: EMPLOYEE CARDS GRID VIEW (TOGGLED ON DEMAND) -->
<div id="cardsViewArea" style="display: none;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="font-weight-bold text-dark mb-0" style="font-size:18px;">
            <i class="fas fa-users text-primary mr-2"></i>Employee Work Summaries
        </h4>
        <span class="text-muted font-weight-bold" style="font-size:13px;" id="cardsCountLabel">
            Showing {{ count($employeeSummaries) }} Staff Members
        </span>
    </div>

    <div class="emp-cards-grid" id="employeeCardsGrid">
        @forelse($employeeSummaries as $sum)
        <div class="emp-summary-card card-item" 
             data-employee-id="{{ $sum['employee_id'] }}"
             data-employee-name="{{ strtolower($sum['user_name']) }}">
            <div>
                <div class="emp-card-header">
                    <div class="emp-card-avatar">
                        @if($sum['passport_photo_url'])
                            <img src="{{ $sum['passport_photo_url'] }}" alt="{{ $sum['user_name'] }}">
                        @else
                            <span>{{ $sum['employee_initial'] }}</span>
                        @endif
                    </div>
                    <div>
                        <h5 class="emp-card-name">{{ $sum['user_name'] }}</h5>
                        <div class="emp-card-meta">{{ $sum['employee_code'] }} &bull; {{ $sum['department'] }}</div>
                    </div>
                </div>

                <div class="emp-stats-bar">
                    <div class="emp-stat-item">
                        <div class="stat-val">{{ $sum['total_reports'] }}</div>
                        <div class="stat-lbl">Reports</div>
                    </div>
                    <div class="emp-stat-item">
                        <div class="stat-val">{{ $sum['total_gross_formatted'] }}</div>
                        <div class="stat-lbl">Gross Work</div>
                    </div>
                    <div class="emp-stat-item">
                        <div class="stat-val">{{ $sum['total_tasks'] }}</div>
                        <div class="stat-lbl">Tasks</div>
                    </div>
                </div>

                <div class="emp-latest-snippet">
                    <div class="snippet-title">
                        <span><i class="fas fa-clock text-primary mr-1"></i> Latest Log</span>
                        <span class="badge badge-light border">{{ $sum['latest_date'] }}</span>
                    </div>
                    <div class="text-truncate">{{ $sum['latest_summary'] }}</div>
                </div>
            </div>

            <div>
                <a href="{{ route('hrms.attendance.work-reports.employee-history', $sum['employee_id']) }}" target="_blank" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #6B11F4) 100%); border: none;">
                    <i class="fas fa-history mr-2"></i> View Daily History ({{ $sum['total_reports'] }})
                </a>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 bg-white border" style="border-radius: 20px;">
            <i class="fas fa-folder-open text-muted fa-3x mb-3"></i>
            <h5 class="font-weight-bold text-dark">No Employee Work Reports Found</h5>
            <p class="text-muted">Adjust search filters or select a different date range.</p>
        </div>
        @endforelse
    </div>
</div>
