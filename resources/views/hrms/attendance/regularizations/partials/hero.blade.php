<div class="att-hero">
    <div>
        <div class="att-kicker"><i class="fas fa-calendar-check"></i> HRMS &bull; ATTENDANCE</div>
        <h3 class="att-title">{{ $pageTitle ?? 'Attendance Regularizations' }}</h3>
        <div class="att-subtitle">{{ $pageSubtitle ?? 'Manage missed punch, correction and regularization requests.' }}</div>
    </div>
    <div class="att-hero-actions">
        @if(!empty($canCreate))
        <button type="button" class="att-btn att-btn-light font-weight-bold" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus"></i> Apply Regularization
        </button>
        @endif
    </div>
</div>
