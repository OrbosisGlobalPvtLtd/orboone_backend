@extends('layouts.panel', ['active' => 'leave_management'])

@section('page_title', 'Team Leave Calendar')

@section('_head')
<style>
    :root {
        --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
        --orb-secondary: {{ $branding['secondary_color'] ?? '#8600EE' }};
        --orb-bg: #F8FAFC;
        --orb-card: #FFFFFF;
        --orb-border: #E2E8F0;
        --orb-text: #0F172A;
        --orb-muted: #64748B;
        --orb-soft: rgba(75, 0, 232, 0.08);
        --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .calendar-page {
        background: var(--orb-bg);
        padding: 24px 20px 48px;
        min-height: calc(100vh - 90px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Hero Header */
    .cal-hero {
        background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#8600EE' }} 100%);
        border-radius: 20px;
        padding: 28px 32px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        margin-bottom: 24px;
    }

    .cal-kicker {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.9;
        margin-bottom: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .cal-title {
        font-size: 26px;
        font-weight: 900;
        margin: 0;
        line-height: 1.15;
    }

    .cal-subtitle {
        font-size: 13.5px;
        font-weight: 600;
        margin: 6px 0 0;
        opacity: 0.9;
    }

    .cal-hero-btn {
        height: 42px;
        border-radius: 12px;
        padding: 0 22px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255,255,255,0.35);
        color: #fff !important;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(8px);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .cal-hero-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Metric Cards */
    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 16px;
        box-shadow: var(--orb-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, .07);
    }

    /* Filter panel */
    .cal-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .cal-filters-wrapper {
        padding: 18px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: #FAF9FF;
    }

    .form-select, .form-control {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid var(--orb-border) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        background-color: #FAFAFA !important;
    }

    .cal-content-row {
        display: flex;
        gap: 24px;
    }

    .cal-main-col {
        flex: 1;
        min-width: 0;
    }

    .cal-sidebar-col {
        width: 380px;
        flex-shrink: 0;
    }

    /* Calendar Grid */
    .calendar-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        overflow: hidden;
    }

    .calendar-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--orb-border);
        background: #FAF9FF;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .calendar-week-headers {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #F8FAFC;
        border-bottom: 1px solid var(--orb-border);
        text-align: center;
    }

    .week-day-header {
        padding: 10px 4px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--orb-muted);
        letter-spacing: 0.05em;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: var(--orb-border);
        gap: 1px;
    }

    .calendar-cell {
        background: #fff;
        min-height: 115px;
        max-height: 145px;
        padding: 8px;
        position: relative;
        cursor: pointer;
        transition: background 0.15s ease;
        overflow: hidden;
    }

    .calendar-cell:hover {
        background: #F8FAFC;
    }

    .calendar-cell.other-month {
        background: #FAFAFA;
        opacity: 0.6;
    }

    .calendar-cell.today {
        background: #EEF2FF;
    }

    .calendar-cell.today .cell-date-num {
        background: var(--orb-primary);
        color: #fff;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .calendar-cell.active-cell {
        box-shadow: inset 0 0 0 2px var(--orb-primary);
        background: #F4F2FF;
    }

    .cell-date-num {
        font-size: 12px;
        font-weight: 800;
        color: var(--orb-text);
        margin-bottom: 6px;
    }

    .leave-chips-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .mini-leave-chip {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }

    .mini-leave-chip.pending {
        background: #FFFBEB;
        color: #B45309;
        border-color: #FDE68A;
    }

    .mini-leave-chip.rejected {
        background: #FEF2F2;
        color: #B91C1C;
        border-color: #FECACA;
    }

    .mini-leave-chip .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }

    /* Side Panel */
    .detail-panel {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--orb-shadow);
        position: sticky;
        top: 100px;
    }

    .detail-header {
        background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#8600EE' }} 100%);
        padding: 20px 24px;
        color: #fff;
    }

    .detail-date-title {
        font-size: 16px;
        font-weight: 900;
        margin: 0 0 4px;
        color: #fff;
    }

    .detail-subtitle {
        font-size: 12px;
        opacity: 0.9;
        font-weight: 600;
    }

    .detail-body {
        padding: 20px;
        max-height: 520px;
        overflow-y: auto;
    }

    .panel-leave-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 14px;
        box-shadow: 0 4px 12px rgba(15,23,42,0.03);
    }

    .panel-leave-card:last-child {
        margin-bottom: 0;
    }

    .panel-leave-card-title {
        font-size: 14.5px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0 0 10px;
    }

    .panel-info-row {
        font-size: 12.5px;
        margin-bottom: 6px;
    }

    .panel-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .panel-btn {
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 800;
        border: none;
    }

    .panel-btn-approve { background: #10B981; color: #fff; }
    .panel-btn-reject { background: #EF4444; color: #fff; }

    @media (max-width: 1200px) {
        .cal-content-row { flex-direction: column; }
        .cal-sidebar-col { width: 100%; }
        .detail-panel { position: static; }
    }
</style>
@endsection

@section('_content')
<div class="calendar-page">

    <!-- Premium Header with Dynamic DB Branding -->
    <div class="cal-hero">
        <div>
            <div class="cal-kicker">
                <i class="fas fa-calendar-week"></i> HRMS &bull; TEAM AVAILABILITY
            </div>
            <h1 class="cal-title">Team Leave Calendar</h1>
            <p class="cal-subtitle">View team availability, approved leaves, pending applications, and department-wise leave planning.</p>
        </div>
        <div>
            <a href="{{ route('leave-approvals.index') }}" class="cal-hero-btn">
                <i class="fas fa-check-circle"></i> Leave Approvals
            </a>
        </div>
    </div>

    @include('hrms.leave.shared.flash')

    <!-- Dynamic DB Summary Stats Cards -->
    <div class="row g-3">
        <div class="col-12 col-md-4 col-lg">
            <div class="stat-card" style="border-bottom: 4px solid var(--orb-primary); height: 90px; display: flex; align-items: center; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(75,0,232,0.08); color: var(--orb-primary); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-plane-departure"></i>
                </div>
                <div>
                    <small style="text-transform: uppercase; font-size: 10px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">On Leave Today</small>
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['on_leave_today'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg">
            <div class="stat-card" style="border-bottom: 4px solid #3b82f6; height: 90px; display: flex; align-items: center; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(59, 130, 246, 0.08); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <small style="text-transform: uppercase; font-size: 10px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">Upcoming Leaves</small>
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['upcoming_leaves'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg">
            <div class="stat-card" style="border-bottom: 4px solid #f59e0b; height: 90px; display: flex; align-items: center; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245, 158, 11, 0.08); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <small style="text-transform: uppercase; font-size: 10px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">Pending Requests</small>
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['pending_requests'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg">
            <div class="stat-card" style="border-bottom: 4px solid #10b981; height: 90px; display: flex; align-items: center; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.08); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <small style="text-transform: uppercase; font-size: 10px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">Approved Month</small>
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['approved_this_month'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg">
            <div class="stat-card" style="border-bottom: 4px solid #ef4444; height: 90px; display: flex; align-items: center; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(239, 68, 68, 0.08); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <small style="text-transform: uppercase; font-size: 10px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">LWP Month</small>
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['lwp_this_month'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="cal-card mt-4">
        <form method="GET" action="{{ route('hrms.leave.team_calendar.index') }}" id="filterForm">
            <div class="cal-filters-wrapper">
                <div class="row align-items-end g-2">
                    <div class="col-12 col-md-2">
                        <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Employee</label>
                        <select name="employee_id" class="form-select select2-searchable">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->display_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Leave Type</label>
                        <select name="leave_type_id" class="form-select">
                            <option value="">All Types</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Status</label>
                        <select name="status" class="form-select">
                            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses (Active)</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Month &amp; Year</label>
                        <div class="d-flex gap-1">
                            <select name="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                            <select name="year" class="form-select">
                                @for($y = today()->year - 2; $y <= today()->year + 2; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn text-white w-100" style="height: 40px; border-radius: 12px; background: var(--orb-primary); font-weight: 800; font-size: 13px; border: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('hrms.leave.team_calendar.index') }}" class="btn btn-light w-100" style="height: 40px; border-radius: 12px; border: 1px solid var(--orb-border); background: #fff; color: var(--orb-primary); font-weight: 800; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <div class="cal-content-row p-4 pt-3">
            <!-- Left Main Calendar Grid -->
            <div class="cal-main-col">
                <div class="calendar-card">
                    <div class="calendar-header">
                        <div class="fw-black text-dark font-weight-bold" style="font-size: 16px;">
                            <i class="far fa-calendar-alt text-primary mr-1"></i>
                            {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                        </div>
                    </div>
                    
                    <div class="calendar-week-headers">
                        <div class="week-day-header">Mon</div>
                        <div class="week-day-header">Tue</div>
                        <div class="week-day-header">Wed</div>
                        <div class="week-day-header">Thu</div>
                        <div class="week-day-header">Fri</div>
                        <div class="week-day-header">Sat</div>
                        <div class="week-day-header">Sun</div>
                    </div>

                    <div class="calendar-grid">
                        @foreach($calendarData as $dateStr => $data)
                            @php
                                $cellDate = $data['date'];
                                $isToday = $cellDate->isToday();
                                $isWeekend = $cellDate->isWeekend();
                                $leavesList = $data['leaves'];
                                $formattedDate = $cellDate->format('l, d F Y');
                            @endphp
                            <div class="calendar-cell {{ !$data['is_current_month'] ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'cell-weekend' : '' }}" 
                                 data-date="{{ $dateStr }}" 
                                 data-formatted="{{ $formattedDate }}"
                                 onclick="selectDate('{{ $dateStr }}', '{{ $formattedDate }}')">
                                
                                <div class="cell-date-num">{{ $cellDate->day }}</div>
                                
                                <div class="leave-chips-list">
                                    @foreach(collect($leavesList)->take(3) as $leave)
                                        @php
                                            $empName = optional(optional($leave->employee)->user)->name 
                                                ?? optional($leave->employee)->display_name 
                                                ?? optional($leave->employee)->employee_code 
                                                ?? 'Employee';
                                            $isLwp = ($leave->lwp_days > 0) || str_contains(strtolower($leave->leaveType->name ?? ''), 'lwp');
                                            $isHalf = $leave->is_half_day;
                                        @endphp
                                        <div class="mini-leave-chip {{ $leave->status }} {{ $isLwp ? 'lwp-badge' : '' }}" title="{{ $empName }} ({{ $leave->leaveType->name ?? 'Leave' }})">
                                            <span class="dot"></span>
                                            <span class="text-truncate">{{ $empName }}</span>
                                            @if($isHalf)
                                                <small style="font-size: 8px; font-weight: 900; opacity: 0.85;">Half</small>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if(count($leavesList) > 3)
                                        <div class="more-leaves-chip mt-1" style="font-size: 9.5px; font-weight: 800; color: var(--orb-primary); background: var(--orb-soft); padding: 2px 6px; border-radius: 6px; text-align: center;">
                                            +{{ count($leavesList) - 3 }} More
                                        </div>
                                    @endif
                                </div>

                                <!-- Hidden Div containing side-panel card elements to instantly render via JavaScript -->
                                <input type="hidden" id="leaves-count-{{ $dateStr }}" value="{{ count($leavesList) }}">
                                <div id="leaves-data-{{ $dateStr }}" style="display: none;">
                                    @foreach($leavesList as $leave)
                                        @php
                                            $empName = optional(optional($leave->employee)->user)->name 
                                                ?? optional($leave->employee)->display_name 
                                                ?? optional($leave->employee)->employee_code 
                                                ?? 'Employee';
                                            $isLwp = ($leave->lwp_days > 0) || str_contains(strtolower($leave->leaveType->name ?? ''), 'lwp');
                                            $daysCount = $leave->total_days ?? $leave->days ?? $leave->requested_days;
                                            if (!$daysCount || $daysCount <= 0) {
                                                $daysCount = ($leave->start_date && $leave->end_date ? \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 : 1);
                                            }
                                            if ($leave->is_half_day) {
                                                $daysCount = 0.5;
                                            }
                                        @endphp
                                        <div class="panel-leave-card">
                                            <h6 class="panel-leave-card-title">{{ $empName }}</h6>
                                            
                                            <div class="panel-info-row d-flex justify-content-between">
                                                <span class="panel-info-lbl text-muted">Code</span>
                                                <span class="panel-info-val font-weight-bold">{{ $leave->employee->employee_code }}</span>
                                            </div>
                                            <div class="panel-info-row d-flex justify-content-between">
                                                <span class="panel-info-lbl text-muted">Department</span>
                                                <span class="panel-info-val font-weight-bold">{{ optional($leave->employee->department)->name ?? 'General' }}</span>
                                            </div>
                                            <div class="panel-info-row d-flex justify-content-between">
                                                <span class="panel-info-lbl text-muted">Leave Type</span>
                                                <span class="panel-info-val font-weight-bold {{ $isLwp ? 'text-danger' : 'text-primary' }}">
                                                    {{ optional($leave->leaveType)->name ?? 'Paid Leave' }}
                                                    {!! $isLwp ? '<small class="badge badge-danger ml-1" style="font-size:8px;">LWP</small>' : '' !!}
                                                </span>
                                            </div>
                                            <div class="panel-info-row d-flex justify-content-between">
                                                <span class="panel-info-lbl text-muted">Period</span>
                                                <span class="panel-info-val font-weight-bold text-dark">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} &rarr; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</span>
                                            </div>
                                            <div class="panel-info-row d-flex justify-content-between">
                                                <span class="panel-info-lbl text-muted">Duration</span>
                                                <span class="panel-info-val font-weight-bold text-dark">{{ $daysCount }} {{ Str::plural('Day', $daysCount) }} {{ $leave->is_half_day ? '(Half Day)' : '' }}</span>
                                            </div>
                                            <div class="panel-info-row d-flex justify-content-between">
                                                <span class="panel-info-lbl text-muted">Status</span>
                                                <span class="panel-info-val font-weight-bold text-uppercase {{ $leave->status === 'approved' ? 'text-success' : ($leave->status === 'pending' ? 'text-warning' : 'text-danger') }}">{{ ucfirst($leave->status) }}</span>
                                            </div>
                                            
                                            @if($leave->reason)
                                            <div class="panel-info-row flex-column text-left mt-2">
                                                <span class="panel-info-lbl text-muted">Reason</span>
                                                <div style="font-size: 12px; font-weight: 600; color: var(--orb-text); background: #f8fafc; padding: 8px; border-radius: 8px; margin-top: 4px; border: 1px solid var(--orb-border);">
                                                    {{ $leave->reason }}
                                                </div>
                                            </div>
                                            @endif

                                            @if($leave->status === 'approved' && $leave->approver)
                                            <div class="panel-info-row mt-2 d-flex justify-content-between">
                                                <span class="panel-info-lbl text-muted">Approved By</span>
                                                <span class="panel-info-val text-muted" style="font-size: 11px;">{{ $leave->approver->name }}</span>
                                            </div>
                                            @endif

                                            <!-- Approval Actions in detail panel (Authorized Admin/HR only) -->
                                            @if($leave->status === 'pending')
                                                @if(auth()->user()->hasPermission('leave.approvals.approve') || auth()->user()->hasPermission('leave.approvals.reject'))
                                                    <div class="panel-actions mt-3">
                                                        <form method="POST" action="{{ route('leave-approvals.approve', $leave->id) }}" style="flex: 1; margin: 0;">
                                                            @csrf
                                                            <input type="hidden" name="remark" value="Approved via Team Calendar">
                                                            <button type="submit" class="panel-btn panel-btn-approve w-100" onclick="return confirm('Approve this request?')">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('leave-approvals.reject', $leave->id) }}" style="flex: 1; margin: 0;">
                                                            @csrf
                                                            <input type="hidden" name="reason" value="Rejected via Team Calendar">
                                                            <button type="submit" class="panel-btn panel-btn-reject w-100" onclick="return confirm('Reject this request?')">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Selected Date detail panel -->
            <div class="cal-sidebar-col">
                <div class="detail-panel">
                    <div class="detail-header">
                        <h5 class="detail-date-title" id="detail-panel-date">
                            {{ today()->format('l, d F Y') }}
                        </h5>
                        <div class="detail-subtitle">
                            Total on Leave: <span id="total-on-leave-count" class="font-weight-black">0</span> Employees
                        </div>
                    </div>
                    
                    <div class="detail-body" id="detail-leaves-list">
                        <!-- Instantiated by JS click handler -->
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('_script')
<script>
    $(document).ready(function() {
        const todayStr = '{{ today()->format('Y-m-d') }}';
        const initialCell = $(`.calendar-cell[data-date="${todayStr}"]`);
        
        if (initialCell.length > 0) {
            initialCell.click();
        } else {
            const firstCell = $('.calendar-cell').not('.other-month').first();
            if (firstCell.length > 0) {
                firstCell.click();
            } else {
                $('.calendar-cell').first().click();
            }
        }
    });

    let currentSelectedDate = '';
    
    function selectDate(dateStr, formattedDate) {
        $('.calendar-cell').removeClass('active-cell');
        $(`.calendar-cell[data-date="${dateStr}"]`).addClass('active-cell');
        
        currentSelectedDate = dateStr;
        
        $('#detail-panel-date').text(formattedDate);
        
        const leavesHtml = $(`#leaves-data-${dateStr}`).html();
        const totalLeaves = parseInt($(`#leaves-count-${dateStr}`).val() || 0);
        
        $('#total-on-leave-count').text(totalLeaves);
        
        if (totalLeaves > 0) {
            $('#detail-leaves-list').html(leavesHtml);
        } else {
            $('#detail-leaves-list').html(`
                <div class="text-center py-5" style="color: var(--orb-muted);">
                    <i class="fas fa-calendar-check fa-3x mb-3" style="color: #E2E8F0;"></i>
                    <p class="mb-0 fw-bold" style="font-size: 13px; color: var(--orb-muted);">No employees on leave on this date.</p>
                </div>
            `);
        }
    }
</script>
@endsection
