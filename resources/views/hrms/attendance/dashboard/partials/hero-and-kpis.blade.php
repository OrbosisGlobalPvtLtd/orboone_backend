        <!-- Hero Header -->
        <div class="att-hero">
            <div class="att-hero-content">
                <div class="att-hero-kicker">
                    <i class="fas fa-calendar-check"></i> HRMS &bull; ATTENDANCE
                </div>
                <h1 class="att-hero-title">Attendance Dashboard</h1>
                <p class="att-hero-subtitle">Live daily overview with punch status, blocked employees, WFO/WFH, late marks and shift completion.</p>
            </div>
            <div class="att-hero-actions">
                <a href="{{ route('attendances.daily') }}" class="att-btn att-btn-glass">
                    <i class="fas fa-list"></i> Attendance Records
                </a>
            </div>
        </div>

        @if(session('status'))
        <div class="alert alert-success" style="border-radius:16px;font-weight:800;">{{ session('status') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger" style="border-radius:16px;font-weight:800;">{{ session('error') }}</div>
        @endif

        <div class="att-kpi-grid">
            @foreach($kpis as $kpi)
            <div class="att-kpi tone-{{ $kpi['tone'] }}">
                <div class="att-kpi-top">
                    <div class="att-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></div>
                    <div class="att-kpi-value">{{ $kpi['value'] }}</div>
                </div>
                <div class="att-kpi-label" title="{{ $kpi['label'] }}">{{ $kpi['label'] }}</div>
                <div class="att-kpi-line"></div>
            </div>
            @endforeach
        </div>

