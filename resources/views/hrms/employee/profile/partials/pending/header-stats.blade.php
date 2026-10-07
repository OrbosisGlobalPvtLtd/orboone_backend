@if (session('success'))
<div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

{{-- Page Header --}}
<div class="orb-page-header">
    <div class="orb-page-header-content">
        <div class="orb-page-kicker">
            <i class="fas fa-user-clock"></i> HRMS &bull; Review
        </div>
        <h1 class="orb-page-title">Profile Review Management</h1>
        <p class="orb-page-subtitle">Pending, submitted and rejected profiles appear here. Approved profiles move to Employee Directory.</p>
    </div>

    <div class="orb-page-actions">
        @if (Route::has('hrms.employees.index'))
        <a href="{{ route('hrms.employees.index') }}" class="orb-btn-light">
            <i class="fas fa-users"></i> Employee Directory
        </a>
        @endif
        <div class="orb-btn-light" style="pointer-events: none; opacity: 0.85;">
            <i class="fas fa-lock mr-1"></i> Approved profiles are hidden
        </div>
    </div>
</div>

{{-- Top Metrics Grid --}}
<div class="eo-stat-grid">
    <div class="eo-stat border-bottom-primary">
        <div class="eo-stat-icon primary"><i class="fas fa-users"></i></div>
        <div>
            <p class="eo-stat-label">Total Active</p>
            <h3 class="eo-stat-value">{{ $total ?? 0 }}</h3>
        </div>
    </div>
    <div class="eo-stat border-bottom-warning">
        <div class="eo-stat-icon warning"><i class="fas fa-clock"></i></div>
        <div>
            <p class="eo-stat-label">Pending</p>
            <h3 class="eo-stat-value">{{ $pending ?? 0 }}</h3>
        </div>
    </div>
    <div class="eo-stat border-bottom-info">
        <div class="eo-stat-icon info"><i class="fas fa-paper-plane"></i></div>
        <div>
            <p class="eo-stat-label">Submitted</p>
            <h3 class="eo-stat-value">{{ $submitted ?? 0 }}</h3>
        </div>
    </div>
    <div class="eo-stat border-bottom-success">
        <div class="eo-stat-icon success"><i class="fas fa-check-circle"></i></div>
        <div>
            <p class="eo-stat-label">Approved</p>
            <h3 class="eo-stat-value">{{ $approved ?? 0 }}</h3>
        </div>
    </div>
    <div class="eo-stat border-bottom-danger">
        <div class="eo-stat-icon danger"><i class="fas fa-times-circle"></i></div>
        <div>
            <p class="eo-stat-label">Rejected</p>
            <h3 class="eo-stat-value">{{ $rejected ?? 0 }}</h3>
        </div>
    </div>
</div>
