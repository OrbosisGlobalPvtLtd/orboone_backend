@php
    $employee = $dashboard['employee'] ?? null;
    $cards = $dashboard['cards'] ?? [];
    $actions = $dashboard['quick_actions'] ?? [];
    $attendance = $dashboard['attendance_self'] ?? [];
    $month = $attendance['month'] ?? [];
    $announcements = $dashboard['latest_announcements'] ?? collect();
    $recentAttendance = $attendance['recent'] ?? collect();
@endphp

<div class="dash-page">
    <div class="dash-wrap">
        <div class="dash-hero">
            <div class="dash-hero-inner">
                <div>
                    <div class="dash-eyebrow">Employee Self Service</div>
                    <h1 class="dash-title">My Dashboard</h1>
                    <p class="dash-subtitle">Welcome back, {{ $dashboard['user_name'] ?? auth()->user()->name }}. Your
                        personal HRMS summary is ready.</p>
                </div>
                <div class="dash-hero-metrics">
                    <div class="dash-mini">
                        <span>Employee Code</span>
                        <strong>{{ $employee->employee_code ?? '-' }}</strong>
                    </div>
                    <div class="dash-mini">
                        <span>Profile</span>
                        <strong>{{ $employee->profile_completion ?? 0 }}%</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            @foreach ($cards as $card)
                <div class="dash-card">
                    <div class="dash-card-top">
                        <div>
                            <div class="dash-card-label">{{ $card['label'] }}</div>
                            <div class="dash-card-value">{{ $card['value'] }}</div>
                        </div>
                        <div class="dash-icon"><i class="{{ $card['icon'] }}"></i></div>
                    </div>
                    <div class="dash-card-sub">{{ $card['subtitle'] }}</div>
                </div>
            @endforeach
        </div>

        @if (!empty($actions))
            <div class="dash-section">
                <h2 class="dash-section-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="dash-actions">
                    @foreach ($actions as $action)
                        <a href="{{ $action['url'] }}" class="dash-action">
                            <div class="dash-action-icon"><i class="{{ $action['icon'] }}"></i></div>
                            <strong>{{ $action['label'] }}</strong>
                            <span>Open {{ strtolower($action['label']) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="dash-section dash-two">
            <div>
                <h2 class="dash-section-title"><i class="fas fa-fingerprint"></i> Today's Attendance</h2>
                <div class="dash-panel">
                    <div class="dash-stat-list">
                        <div class="dash-stat">
                            <span>Status</span><strong>{{ $attendance['today_status'] ?? 'Not Marked' }}</strong></div>
                        <div class="dash-stat"><span>Punch</span><strong
                                style="font-size:16px;">{{ $attendance['punch_summary'] ?? '-- to --' }}</strong></div>
                        <div class="dash-stat"><span>Work
                                Mode</span><strong>{{ strtoupper($attendance['today']->work_mode ?? '-') }}</strong>
                        </div>
                        <div class="dash-stat">
                            <span>Shift Left</span>
                            <strong>
                                @if (($attendance['remaining_shift_minutes'] ?? null) !== null)
                                    {{ $attendance['remaining_shift_minutes'] }}m
                                @else
                                    -
                                @endif
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="dash-section-title"><i class="fas fa-calendar-alt"></i> This Month</h2>
                <div class="dash-panel">
                    <div class="dash-stat-list">
                        <div class="dash-stat"><span>Present</span><strong>{{ $month['present'] ?? 0 }}</strong></div>
                        <div class="dash-stat"><span>Absent</span><strong>{{ $month['absent'] ?? 0 }}</strong></div>
                        <div class="dash-stat"><span>Half Day</span><strong>{{ $month['half_day'] ?? 0 }}</strong>
                        </div>
                        <div class="dash-stat"><span>Late</span><strong>{{ $month['late'] ?? 0 }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section dash-three">
            <div class="dash-panel">
                <h2 class="dash-section-title"><i class="fas fa-plane-departure"></i> Leave Balance</h2>
                <div class="dash-stat-list">
                    <div class="dash-stat"><span>PL</span><strong>{{ $dashboard['leave_self']['pl'] ?? 0 }}</strong>
                    </div>
                    <div class="dash-stat"><span>SL</span><strong>{{ $dashboard['leave_self']['sl'] ?? 0 }}</strong>
                    </div>
                    <div class="dash-stat">
                        <span>Pending</span><strong>{{ $dashboard['leave_self']['pending'] ?? 0 }}</strong></div>
                </div>
            </div>

            <div class="dash-panel">
                <h2 class="dash-section-title"><i class="fas fa-folder-open"></i> My Documents</h2>
                <div class="dash-stat-list">
                    <div class="dash-stat">
                        <span>Pending</span><strong>{{ $dashboard['documents_self']['pending'] ?? 0 }}</strong></div>
                    <div class="dash-stat">
                        <span>Verified</span><strong>{{ $dashboard['documents_self']['verified'] ?? 0 }}</strong></div>
                    <div class="dash-stat">
                        <span>Rejected</span><strong>{{ $dashboard['documents_self']['rejected'] ?? 0 }}</strong></div>
                </div>
            </div>

            <div class="dash-panel">
                <h2 class="dash-section-title"><i class="fas fa-user-tie"></i> Reporting</h2>
                <p class="mb-2"><span
                        class="dash-pill blue">{{ $employee->manager_name ?? 'No manager assigned' }}</span></p>
                <p class="mb-0 text-muted">{{ $employee->department_name ?? 'Department not assigned' }} @if (!empty($employee->designation_name))
                        / {{ $employee->designation_name }}
                    @endif
                </p>
            </div>
        </div>

        <div class="dash-section dash-two">
            <div>
                <h2 class="dash-section-title"><i class="fas fa-history"></i> Recent Attendance</h2>
                <div class="dash-panel table-responsive">
                    @if (count($recentAttendance))
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Punch In</th>
                                    <th>Punch Out</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentAttendance as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->attendance_date)->format('d M Y') }}</td>
                                        <td><span
                                                class="dash-pill green">{{ $row->type_name ?? ucfirst(str_replace('_', ' ', $row->type_code ?? 'Marked')) }}</span>
                                        </td>
                                        <td>{{ $row->punch_in_time ?? '-' }}</td>
                                        <td>{{ $row->punch_out_time ?? '-' }}</td>
                                        <td>{{ round(($row->total_work_minutes ?? 0) / 60, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="dash-empty">No attendance records yet.</div>
                    @endif
                </div>
            </div>

            <div>
                <h2 class="dash-section-title"><i class="fas fa-bullhorn"></i> Latest Announcements</h2>
                <div class="dash-panel">
                    @if (count($announcements))
                        @foreach ($announcements as $announcement)
                            <div class="mb-3 pb-3" style="border-bottom:1px solid #F1F3F8;">
                                <strong>{{ $announcement->title }}</strong>
                                <p class="mb-1 text-muted">
                                    {{ \Illuminate\Support\Str::limit($announcement->description, 90) }}</p>
                                <span
                                    class="dash-pill">{{ $announcement->created_at ? \Carbon\Carbon::parse($announcement->created_at)->diffForHumans() : '-' }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="dash-empty">No announcements yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
