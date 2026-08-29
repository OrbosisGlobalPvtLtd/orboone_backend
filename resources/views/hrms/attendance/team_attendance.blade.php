@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'attendances'])

@section('page_title', 'Team Attendance (Login & Logout)')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #FF5252;
        --orb-bg: #F8FAFC;
        --orb-card: #FFFFFF;
        --orb-border: #E2E8F0;
        --orb-text: #0F172A;
        --orb-muted: #64748B;
        --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .team-att-page {
        padding: 24px 20px 48px;
        background: var(--orb-bg);
        min-height: calc(100vh - 90px);
        font-family: 'Outfit', sans-serif;
    }

    .team-att-container {
        max-width: 1650px;
        margin: 0 auto;
    }

    /* Hero Banner */
    .team-hero-card {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 20px;
        padding: 24px 28px;
        margin-bottom: 24px;
        color: #ffffff;
        box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .team-hero-title {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 4px 0;
        color: #ffffff;
        letter-spacing: -0.02em;
    }

    .team-hero-subtitle {
        font-size: 13.5px;
        opacity: 0.92;
        margin: 0;
        font-weight: 400;
    }

    /* Metric Cards Grid */
    .team-metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .team-metric-card {
        background: var(--orb-card);
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 16px 18px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .team-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .team-metric-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .team-metric-val {
        font-size: 22px;
        font-weight: 800;
        color: var(--orb-text);
        line-height: 1.1;
    }

    .team-metric-label {
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 2px;
    }

    /* View Switcher Tabs */
    .view-switcher-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        background: #FFFFFF;
        padding: 8px 12px;
        border-radius: 14px;
        border: 1px solid var(--orb-border);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    }

    .view-tab-btn {
        padding: 8px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        color: var(--orb-muted);
        border: none;
        background: transparent;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none !important;
    }

    .view-tab-btn:hover {
        color: var(--orb-primary);
        background: #F1F5F9;
    }

    .view-tab-btn.active {
        background: var(--orb-primary);
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25);
    }

    /* Content Card */
    .rep-card {
        background: var(--orb-card);
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .rep-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--orb-border);
        background: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .rep-card-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-input {
        height: 38px;
        border-radius: 10px;
        font-size: 13px;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        padding: 6px 12px;
        outline: none;
    }

    .filter-input:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    }

    /* Table Styles */
    .team-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .team-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #F8FAFC !important;
        color: #475569 !important;
        font-size: 11.5px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
        border-bottom: 2px solid #E2E8F0 !important;
        white-space: nowrap !important;
        padding: 12px 16px !important;
    }

    .team-table tbody td {
        padding: 13px 16px !important;
        border-bottom: 1px solid #F1F5F9 !important;
        vertical-align: middle !important;
        font-size: 13px !important;
        color: #1E293B;
    }

    .team-table tbody tr:hover {
        background: #F8FAFC !important;
    }

    /* Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .status-working {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }

    .status-completed {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }

    .status-late {
        background: #FFFBEB;
        color: #B45309;
        border: 1px solid #FDE68A;
    }

    .status-wfh {
        background: #F5F3FF;
        color: #6D28D9;
        border: 1px solid #DDD6FE;
    }

    .status-leave {
        background: #FAF5FF;
        color: #7E22CE;
        border: 1px solid #E9D5FF;
    }

    .status-absent {
        background: #FEF2F2;
        color: #B91C1C;
        border: 1px solid #FECACA;
    }

    /* Live Pulse Dot */
    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-green 1.6s infinite;
    }

    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .avatar-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #EEF2FF, #E0E7FF);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
        flex-shrink: 0;
    }
</style>
@endsection

@section('_content')
<div class="team-att-page">
    <div class="team-att-container">
        
        <!-- Hero Header -->
        <div class="team-hero-card">
            <div>
                <h3 class="team-hero-title"><i class="fas fa-users-cog mr-2"></i>Team Attendance & Login Tracking</h3>
                <p class="team-hero-subtitle">Real-time team login, logout, working hours, work mode, and attendance logs.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-light px-3 py-2 font-weight-bold text-dark" style="border-radius: 10px; font-size: 13px;">
                    <i class="fas fa-calendar-day text-primary mr-1"></i> Today: {{ \Carbon\Carbon::parse($today)->format('d M Y') }}
                </span>
            </div>
        </div>

        <!-- View Mode Switcher -->
        <div class="view-switcher-bar">
            <a href="{{ route('attendances.team', ['view_mode' => 'today']) }}" class="view-tab-btn {{ $viewMode === 'today' ? 'active' : '' }}">
                <i class="fas fa-clock"></i> Today's Live Attendance
            </a>
            <a href="{{ route('attendances.team', ['view_mode' => 'history']) }}" class="view-tab-btn {{ $viewMode === 'history' ? 'active' : '' }}">
                <i class="fas fa-history"></i> Attendance History & Range
            </a>
            <div class="ml-auto small text-muted font-weight-semibold d-none d-md-block">
                <i class="fas fa-shield-alt text-success mr-1"></i> Team Management Scope
            </div>
        </div>

        <!-- Metrics Overview Grid -->
        <div class="team-metrics-grid">
            <div class="team-metric-card">
                <div class="team-metric-icon" style="background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="team-metric-label">Total Team</div>
                    <div class="team-metric-val">{{ $stats['total_team'] }}</div>
                </div>
            </div>

            <div class="team-metric-card">
                <div class="team-metric-icon" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                    <span class="pulse-dot mr-1"></span>
                    <i class="fas fa-user-clock"></i>
                </div>
                <div>
                    <div class="team-metric-label">Currently Working</div>
                    <div class="team-metric-val text-success">{{ $stats['currently_working'] }}</div>
                </div>
            </div>

            <div class="team-metric-card">
                <div class="team-metric-icon" style="background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="team-metric-label">Completed Shift</div>
                    <div class="team-metric-val text-primary">{{ $stats['completed_shift'] }}</div>
                </div>
            </div>

            <div class="team-metric-card">
                <div class="team-metric-icon" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="team-metric-label">Late Today</div>
                    <div class="team-metric-val text-warning">{{ $stats['late_today'] }}</div>
                </div>
            </div>

            <div class="team-metric-card">
                <div class="team-metric-icon" style="background: #F5F3FF; color: #7C3AED; border: 1px solid #DDD6FE;">
                    <i class="fas fa-home"></i>
                </div>
                <div>
                    <div class="team-metric-label">WFH Today</div>
                    <div class="team-metric-val" style="color: #7C3AED;">{{ $stats['wfh_today'] }}</div>
                </div>
            </div>

            <div class="team-metric-card">
                <div class="team-metric-icon" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;">
                    <i class="fas fa-user-times"></i>
                </div>
                <div>
                    <div class="team-metric-label">Not Punched / Absent</div>
                    <div class="team-metric-val text-danger">{{ $stats['not_punched_today'] }}</div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="rep-card">
            <div class="rep-card-header">
                <div class="rep-card-title">
                    <i class="fas fa-filter text-primary"></i> Filter Team Attendance
                </div>
                <form method="GET" action="{{ route('attendances.team') }}" class="form-inline flex-wrap gap-2">
                    <input type="hidden" name="view_mode" value="{{ $viewMode }}">

                    <!-- Team Member -->
                    <select name="employee_id" class="filter-input mr-2 mb-2" style="min-width: 190px;">
                        <option value="">All Team Members</option>
                        @foreach($teamEmployees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->display_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Work Mode -->
                    <select name="work_mode" class="filter-input mr-2 mb-2">
                        <option value="">All Work Modes</option>
                        <option value="wfo" {{ request('work_mode') === 'wfo' ? 'selected' : '' }}>WFO (Office)</option>
                        <option value="wfh" {{ request('work_mode') === 'wfh' ? 'selected' : '' }}>WFH (Home)</option>
                    </select>

                    <!-- Status Filter -->
                    <select name="status_filter" class="filter-input mr-2 mb-2">
                        <option value="">All Statuses</option>
                        <option value="working" {{ request('status_filter') === 'working' ? 'selected' : '' }}>Currently Working</option>
                        <option value="completed" {{ request('status_filter') === 'completed' ? 'selected' : '' }}>Completed Shift</option>
                        <option value="late" {{ request('status_filter') === 'late' ? 'selected' : '' }}>Late In</option>
                        <option value="half_day" {{ request('status_filter') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                        <option value="wfh" {{ request('status_filter') === 'wfh' ? 'selected' : '' }}>WFH</option>
                        <option value="blocked" {{ request('status_filter') === 'blocked' ? 'selected' : '' }}>Blocked / Lock</option>
                    </select>

                    @if($viewMode === 'history')
                        <!-- Date Range -->
                        <input type="date" name="from_date" class="filter-input mr-2 mb-2" value="{{ $fromDate }}" placeholder="From Date">
                        <input type="date" name="to_date" class="filter-input mr-2 mb-2" value="{{ $toDate }}" placeholder="To Date">
                    @else
                        <!-- Single Date -->
                        <input type="date" name="date" class="filter-input mr-2 mb-2" value="{{ $date }}">
                    @endif

                    <!-- Search Input -->
                    <input type="text" name="search" class="filter-input mr-2 mb-2" placeholder="Search name or code..." value="{{ request('search') }}" style="min-width: 170px;">

                    <!-- Buttons -->
                    <button type="submit" class="btn btn-primary font-weight-bold px-3 mr-2 mb-2" style="height: 38px; border-radius: 10px; background: var(--orb-primary); border: none;">
                        <i class="fas fa-search mr-1"></i> Search
                    </button>
                    <a href="{{ route('attendances.team', ['view_mode' => $viewMode]) }}" class="btn btn-light border font-weight-bold px-3 mb-2" style="height: 38px; border-radius: 10px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Attendance Data Table -->
            <div class="table-responsive">
                <table class="team-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Login (Punch In)</th>
                            <th>Logout (Punch Out)</th>
                            <th>Working Hours</th>
                            <th>Work Mode</th>
                            <th>Status</th>
                            <th>Work Summary</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $row)
                        @php
                            $isWorking = !empty($row->punch_in_time) && empty($row->punch_out_time) && !$row->is_blocked;
                            $isCompleted = !empty($row->punch_in_time) && !empty($row->punch_out_time);
                            $empName = $row->employee->display_name ?? optional($row->user)->name ?? 'Team Member';
                            $empCode = $row->employee->employee_code ?? '-';
                            $dept = $row->employee->department->name ?? '-';
                            $desig = $row->employee->designation->name ?? '-';
                            $initials = strtoupper(substr($empName, 0, 2));
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="avatar-box mr-2">{{ $initials }}</div>
                                    <div>
                                        <strong class="d-block text-dark font-weight-bold" style="font-size: 13.5px;">{{ $empName }}</strong>
                                        <small class="text-muted"><span class="badge badge-light border">{{ $empCode }}</span> {{ $desig }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>{{ $row->date_formatted }}</strong></td>
                            <td>
                                <span class="badge badge-light border font-weight-bold">
                                    <i class="far fa-clock text-primary mr-1"></i> {{ $row->attendanceTime->name ?? 'General Shift' }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($row->punch_in_time))
                                    <div>
                                        <strong class="text-success font-weight-bold" style="font-size: 13.5px;">
                                            <i class="fas fa-sign-in-alt mr-1"></i> {{ $row->punch_in_formatted }}
                                        </strong>
                                        @if($row->is_late)
                                            <span class="badge badge-warning text-dark ml-1 font-weight-bold" style="font-size: 10px;">LATE</span>
                                        @endif
                                    </div>
                                    @if(!empty($row->punch_in_ip))
                                        <small class="text-muted d-block" style="font-size: 11px;"><i class="fas fa-network-wired mr-1"></i> {{ $row->punch_in_ip }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">--:--</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($row->punch_out_time))
                                    <div>
                                        <strong class="text-primary font-weight-bold" style="font-size: 13.5px;">
                                            <i class="fas fa-sign-out-alt mr-1"></i> {{ $row->punch_out_formatted }}
                                        </strong>
                                    </div>
                                @elseif($isWorking)
                                    <span class="status-badge status-working">
                                        <span class="pulse-dot"></span> Active Now
                                    </span>
                                @else
                                    <span class="text-muted">--:--</span>
                                @endif
                            </td>
                            <td>
                                <strong class="font-weight-bold {{ $isWorking ? 'text-success' : 'text-dark' }}">
                                    {{ $row->working_hours_label }}
                                </strong>
                            </td>
                            <td>
                                @if(strtolower($row->work_mode ?? '') === 'wfh')
                                    <span class="status-badge status-wfh"><i class="fas fa-home mr-1"></i> WFH</span>
                                @else
                                    <span class="status-badge status-completed"><i class="fas fa-building mr-1"></i> WFO</span>
                                @endif
                            </td>
                            <td>
                                @if($isWorking)
                                    <span class="status-badge status-working">🟢 Working</span>
                                @elseif($isCompleted)
                                    <span class="status-badge status-completed">🔵 Shift Done</span>
                                @elseif($row->is_blocked || $row->is_punch_blocked)
                                    <span class="status-badge status-absent">🔴 Blocked</span>
                                @elseif($row->attendance_status === 'absent')
                                    <span class="status-badge status-absent">🔴 Absent</span>
                                @else
                                    <span class="status-badge status-completed">{{ ucwords($row->attendance_status ?? 'Recorded') }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $workSummary = optional($row->workLogs->first())->work_description ?? $row->punch_in_note ?? '-';
                                @endphp
                                <span class="d-inline-block text-truncate" style="max-width: 180px;" title="{{ $workSummary }}">
                                    {{ $workSummary }}
                                </span>
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-light border px-2.5 py-1 js-view-team-modal"
                                    data-row='@json($row)'
                                    data-empname="{{ $empName }}"
                                    data-empcode="{{ $empCode }}"
                                    data-dept="{{ $dept }}"
                                    data-desig="{{ $desig }}"
                                    style="border-radius: 8px;">
                                    <i class="fas fa-eye text-primary mr-1"></i> Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <div class="mb-2" style="font-size: 32px; opacity: 0.4;"><i class="fas fa-user-clock"></i></div>
                                <strong>No team attendance records found for the selected period.</strong>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted font-weight-semibold">
                    Showing {{ $attendances->firstItem() ?? 0 }} to {{ $attendances->lastItem() ?? 0 }} of {{ $attendances->total() }} records
                </div>
                <div>
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="teamAttDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header px-4 py-3" style="background: linear-gradient(135deg, var(--orb-primary), #6366F1); color: #fff;">
                <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 16px;">
                    <i class="fas fa-user-clock mr-2"></i> Attendance Details
                </h5>
                <button type="button" class="close text-white opacity-80" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="avatar-box mr-3" id="m_avatar" style="width: 46px; height: 46px; font-size: 16px;">MT</div>
                    <div>
                        <h6 class="font-weight-bold text-dark mb-0" id="m_name" style="font-size: 15px;">Meet Trivedi</h6>
                        <small class="text-muted" id="m_code_desig">OG-EMP-017 &bull; Tech Lead</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6 mb-2">
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10.5px;">Attendance Date</small>
                        <strong class="text-dark" id="m_date">-</strong>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10.5px;">Shift Assigned</small>
                        <strong class="text-dark" id="m_shift">-</strong>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10.5px;">Login (Punch In)</small>
                        <strong class="text-success" id="m_punch_in">-</strong>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10.5px;">Logout (Punch Out)</small>
                        <strong class="text-primary" id="m_punch_out">-</strong>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10.5px;">Work Mode</small>
                        <span id="m_work_mode">-</span>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10.5px;">Total Work Duration</small>
                        <strong class="text-dark" id="m_work_hours">-</strong>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-lg border mb-2" style="border-radius: 12px;">
                    <small class="text-muted d-block font-weight-bold text-uppercase mb-1" style="font-size: 10.5px;"><i class="fas fa-file-alt mr-1"></i> Work Summary / Notes</small>
                    <div id="m_work_summary" class="text-dark small" style="white-space: pre-line;">-</div>
                </div>

                <div class="small text-muted">
                    <i class="fas fa-info-circle mr-1"></i> Punch IP: <span id="m_ip">-</span> &bull; Device: <span id="m_device">-</span>
                </div>
            </div>
            <div class="modal-footer px-4 py-2.5 bg-light border-0">
                <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 10px; font-size: 13px;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-view-team-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = JSON.parse(this.getAttribute('data-row') || '{}');
            var name = this.getAttribute('data-empname') || '-';
            var code = this.getAttribute('data-empcode') || '-';
            var desig = this.getAttribute('data-desig') || '-';
            
            document.getElementById('m_avatar').textContent = name.substring(0, 2).toUpperCase();
            document.getElementById('m_name').textContent = name;
            document.getElementById('m_code_desig').textContent = code + ' • ' + desig;
            document.getElementById('m_date').textContent = row.date_formatted || row.attendance_date || '-';
            document.getElementById('m_shift').textContent = (row.attendance_time && row.attendance_time.name) ? row.attendance_time.name : 'General Shift';
            document.getElementById('m_punch_in').textContent = row.punch_in_formatted || row.punch_in_time || '--:--';
            document.getElementById('m_punch_out').textContent = row.punch_out_formatted || row.punch_out_time || '--:--';
            document.getElementById('m_work_mode').textContent = (row.work_mode || 'wfo').toUpperCase();
            document.getElementById('m_work_hours').textContent = row.working_hours_label || '--';
            document.getElementById('m_ip').textContent = row.punch_in_ip || 'N/A';
            document.getElementById('m_device').textContent = row.punch_in_device ? row.punch_in_device.substring(0, 50) + '...' : 'Web Panel';

            var summary = '-';
            if (row.work_logs && row.work_logs.length > 0 && row.work_logs[0].work_description) {
                summary = row.work_logs[0].work_description;
            } else if (row.punch_in_note) {
                summary = row.punch_in_note;
            }
            document.getElementById('m_work_summary').textContent = summary;

            $('#teamAttDetailsModal').modal('show');
        });
    });
});
</script>
@endsection
