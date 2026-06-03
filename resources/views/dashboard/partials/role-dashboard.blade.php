@php
    $cards = $dashboard['cards'] ?? [];
    $actions = $dashboard['quick_actions'] ?? [];
    $attendance = $dashboard['attendance_today'] ?? null;
    $monthly = $dashboard['charts']['monthly'] ?? ['labels' => [], 'present' => [], 'late' => []];
    $departments = $dashboard['charts']['departments'] ?? ['labels' => [], 'values' => []];
    $maxDept = max($departments['values'] ?: [1]);
    $activities = $dashboard['recent_activities'] ?? collect();

    $monthlyLabels = $monthly['labels'] ?? [];
    $monthlyPresent = $monthly['present'] ?? [];
    $monthlyLate = $monthly['late'] ?? [];
@endphp

<div class="dash-page">
    <div class="dash-wrap">

        <div class="dash-hero">
            <div class="dash-hero-inner">
                <div>
                    <div class="dash-eyebrow">Orbosis HRMS</div>
                    <h1 class="dash-title">{{ $dashboard['role_title'] ?? 'Dashboard' }}</h1>
                    <p class="dash-subtitle">
                        Welcome back, {{ $dashboard['user_name'] ?? auth()->user()->name }}.
                    </p>
                </div>

                <div class="dash-hero-metrics">
                    <div class="dash-mini">
                        <span>Today</span>
                        <strong>{{ now()->format('d M') }}</strong>
                    </div>
                    <div class="dash-mini">
                        <span>Role</span>
                        <strong>{{ str_replace('_', ' ', ucwords($dashboard['role'] ?? 'employee', '_')) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($dashboard['empty_message']))
            <div class="dash-alert">
                <i class="fas fa-info-circle mr-1"></i>
                {{ $dashboard['empty_message'] }}
            </div>
        @endif

        <div class="dash-grid">
            @forelse($cards as $card)
                <div class="dash-card">
                    <div class="dash-card-top">
                        <div>
                            <div class="dash-card-label">{{ $card['label'] ?? '' }}</div>
                            <div class="dash-card-value">{{ $card['value'] ?? 0 }}</div>
                        </div>
                        <div class="dash-icon">
                            <i class="{{ $card['icon'] ?? 'fas fa-chart-bar' }}"></i>
                        </div>
                    </div>
                    <div class="dash-card-sub">{{ $card['subtitle'] ?? '' }}</div>
                </div>
            @empty
                <div class="dash-card">
                    <div class="dash-empty">No dashboard cards</div>
                </div>
            @endforelse
        </div>

        @if (!empty($actions))
            <div class="dash-section">
                <h2 class="dash-section-title">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h2>

                <div class="dash-actions">
                    @php $hasDashActions = false; @endphp
                    @foreach ($actions as $action)
                        @php
                            $url = $action['url'] ?? '';
                            $title = trim($action['title'] ?? $action['label'] ?? '');
                        @endphp

                        @if($url && $url !== '#' && $title !== '')
                            @php $hasDashActions = true; @endphp
                            <a href="{{ $url }}" class="dash-action">
                                <div class="dash-action-icon">
                                    <i class="{{ $action['icon'] ?? 'fas fa-link' }}"></i>
                                </div>
                                <strong>{{ $title }}</strong>
                                <span>Open {{ strtolower($title) }}</span>
                            </a>
                        @endif
                    @endforeach

                    @if(!$hasDashActions)
                        <div class="dash-empty" style="grid-column: 1 / -1; padding: 20px; border: 1px dashed #e2e8f0; border-radius: 12px; text-align: center; color: #64748b; font-weight: 600;">
                            No quick actions available
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($attendance)
            <div class="dash-section">
                <h2 class="dash-section-title">
                    <i class="fas fa-clock"></i> Today's Attendance
                </h2>

                <div class="dash-panel">
                    <div class="dash-stat-list">
                        @foreach ([
        'present' => 'Present',
        'absent' => 'Absent',
        'half_day' => 'Half Day',
        'leave' => 'Leave',
        'week_off' => 'Week Off',
        'pending_hr' => 'Pending HR',
        'late' => 'Late',
        'early_out' => 'Early Out',
    ] as $key => $label)
                            <div class="dash-stat">
                                <span>{{ $label }}</span>
                                <strong>{{ $attendance[$key] ?? 0 }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="dash-section dash-two">

            {{-- MONTHLY CHART --}}
            <div>
                <h2 class="dash-section-title">
                    <i class="fas fa-chart-line"></i> Monthly Attendance Trend
                </h2>

                <div class="dash-panel chart-panel">
                    @if (!empty($monthlyLabels))
                        <div class="chart-head">
                            <div>
                                <strong>{{ now()->format('F') }} Attendance</strong>
                                <span>Present vs Late employee trend</span>
                            </div>
                            <div class="chart-legend">
                                <span><i class="dot present"></i> Present</span>
                                <span><i class="dot late"></i> Late</span>
                            </div>
                        </div>

                        <div class="chart-box">
                            <canvas id="monthlyAttendanceChart"></canvas>
                        </div>
                    @else
                        <div class="dash-empty">No monthly attendance data yet.</div>
                    @endif
                </div>
            </div>

            {{-- DEPARTMENT --}}
            <div>
                <h2 class="dash-section-title">
                    <i class="fas fa-sitemap"></i> Departments
                </h2>

                <div class="dash-panel">
                    @forelse($departments['labels'] as $i => $label)
                        @php
                            $val = $departments['values'][$i] ?? 0;
                            $deptWidth = $maxDept > 0 ? max(4, round(($val / $maxDept) * 100, 2)) : 0;
                        @endphp

                        <div class="dash-bar-row">
                            <div class="dash-bar-label">{{ $label }}</div>
                            <div class="dash-bar-track">
                                <div class="dash-bar-fill" style="width:{{ $deptWidth }}%"></div>
                            </div>
                            <div class="dash-bar-value">{{ $val }}</div>
                        </div>
                    @empty
                        <div class="dash-empty">No departments</div>
                    @endforelse
                </div>
            </div>

        </div>

        <div class="dash-section">
            <h2 class="dash-section-title">
                <i class="fas fa-history"></i> Recent Activities
            </h2>

            <div class="dash-panel">
                @if (count($activities))
                    <div class="table-responsive">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Description</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activities as $a)
                                    <tr>
                                        <td>
                                            <i class="{{ $a['icon'] ?? 'fas fa-circle' }} mr-2 text-primary"></i>
                                            {{ $a['title'] ?? '-' }}
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($a['description'] ?? '-', 80) }}</td>
                                        <td>
                                            {{ !empty($a['time']) ? \Carbon\Carbon::parse($a['time'])->diffForHumans() : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="dash-empty">No activity</div>
                @endif
            </div>
        </div>

    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartCanvas = document.getElementById('monthlyAttendanceChart');

        if (!chartCanvas) {
            return;
        }

        const labels = @json($monthlyLabels);
        const presentData = @json($monthlyPresent);
        const lateData = @json($monthlyLate);

        new Chart(chartCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Present',
                        data: presentData,
                        borderColor: var(--orb-primary),
                        backgroundColor: 'rgba(75, 0, 232, 0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Late',
                        data: lateData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#64748b'
                        },
                        grid: {
                            color: '#eef2f7'
                        }
                    }
                }
            }
        });
    });
</script>
