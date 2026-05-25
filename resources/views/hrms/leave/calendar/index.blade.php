@extends('layouts.panel', ['active' => 'leave_management'])

@section('page_title', 'Team Leave Calendar')

@section('_head')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .calendar-page {
        background: var(--orb-bg);
        padding: 24px;
        min-height: calc(100vh - 90px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Hero Header */
    .cal-hero {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 32px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 10px 30px rgba(75, 0, 232, 0.15);
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
        font-size: 28px;
        font-weight: 900;
        margin: 0;
        line-height: 1.15;
    }

    .cal-subtitle {
        font-size: 13px;
        font-weight: 600;
        margin: 8px 0 0;
        opacity: 0.85;
    }

    .cal-hero-btn {
        height: 40px;
        border-radius: 12px;
        padding: 0 20px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff !important;
        background: rgba(255,255,255,0.18);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .cal-hero-btn:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    /* Metric Cards */
    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 16px;
        box-shadow: var(--orb-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(16, 24, 40, .09);
    }

    /* Filter panel */
    .cal-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .cal-filters-wrapper {
        padding: 18px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: #fafafa;
    }

    .form-select, .form-control {
        height: 40px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        background-color: #fff !important;
    }

    /* Calendar Design Layout */
    .cal-content-row {
        display: flex;
        gap: 24px;
        margin-top: 24px;
    }

    .cal-main-col {
        flex: 1;
        min-width: 0;
    }

    .cal-sidebar-col {
        width: 340px;
        flex-shrink: 0;
    }

    .calendar-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .calendar-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }

    .calendar-week-headers {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #F8FAFC;
        border-bottom: 1px solid var(--orb-border);
        text-align: center;
    }

    .week-day-header {
        padding: 12px 6px;
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        color: var(--orb-muted);
        letter-spacing: 0.5px;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: var(--orb-border);
        gap: 1px;
    }

    .calendar-cell {
        min-height: 115px;
        background: #fff;
        padding: 8px;
        transition: all 0.2s ease;
        position: relative;
        cursor: pointer;
        display: flex;
        flex-direction: column;
    }

    .calendar-cell:hover {
        background: #F4F2FF;
    }

    .calendar-cell.other-month {
        background: #fbfbfb;
    }

    .calendar-cell.other-month .cell-date-num {
        color: #ccd0d9;
    }

    .calendar-cell.today {
        background: rgba(75, 0, 232, 0.02) !important;
    }

    .calendar-cell.today .cell-date-num {
        background: var(--orb-primary);
        color: #fff !important;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        box-shadow: 0 2px 6px rgba(75, 0, 232, 0.3);
    }

    .calendar-cell.active-cell {
        background: #F0EDFF !important;
        box-shadow: inset 0 0 0 2px var(--orb-primary) !important;
    }

    .cell-date-num {
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-text);
        margin-bottom: 8px;
    }

    .cell-weekend {
        background: #F9FAFB;
    }

    /* Mini Leave Chips inside cell */
    .leave-chips-list {
        display: flex;
        flex-column: column;
        flex-direction: column;
        gap: 4px;
        overflow-y: auto;
        flex: 1;
    }

    .mini-leave-chip {
        font-size: 10px;
        font-weight: 800;
        padding: 3px 6px;
        border-radius: 6px;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s ease;
    }

    .mini-leave-chip.pending {
        background: #FFFBEB;
        color: #B45309;
        border: 1px dashed #FCD34D;
    }

    .mini-leave-chip.approved {
        background: #ECFDF3;
        color: #047857;
        border: 1px solid #D1FAE5;
    }

    .mini-leave-chip.rejected {
        background: #FEF2F2;
        color: #B91C1C;
        border: 1px solid #FEE2E2;
        text-decoration: line-through;
    }

    .mini-leave-chip.lwp-badge {
        background: #FEF2F2 !important;
        color: #DC2626 !important;
        border: 1px solid #FECACA !important;
    }

    .mini-leave-chip .dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        display: inline-block;
        background: currentColor;
    }

    /* Sidebar Detail Panel */
    .detail-panel {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        position: sticky;
        top: 24px;
    }

    .detail-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        padding: 20px 24px;
    }

    .detail-date-title {
        font-size: 16px;
        font-weight: 900;
        margin: 0;
    }

    .detail-subtitle {
        font-size: 11px;
        opacity: 0.9;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 4px;
        letter-spacing: 0.5px;
    }

    .detail-body {
        padding: 24px;
        max-height: 600px;
        overflow-y: auto;
    }

    .panel-leave-card {
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        background: #fff;
        transition: box-shadow 0.2s ease;
    }

    .panel-leave-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .panel-leave-card-title {
        font-size: 13px;
        font-weight: 850;
        color: var(--orb-text);
        margin: 0 0 6px 0;
    }

    .panel-info-row {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        margin-bottom: 6px;
    }

    .panel-info-lbl {
        color: var(--orb-muted);
        font-weight: 750;
        text-transform: uppercase;
        font-size: 9px;
        letter-spacing: 0.5px;
    }

    .panel-info-val {
        color: var(--orb-text);
        font-weight: 800;
        text-align: right;
    }

    .panel-actions {
        display: flex;
        gap: 8px;
        margin-top: 14px;
        border-top: 1px solid var(--orb-border);
        padding-top: 12px;
    }

    .panel-btn {
        flex: 1;
        height: 34px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        border: 0;
        transition: all 0.2s ease;
    }

    .panel-btn-approve {
        background: #10b981;
        color: #fff;
    }

    .panel-btn-approve:hover {
        background: #059669;
    }

    .panel-btn-reject {
        background: #ef4444;
        color: #fff;
    }

    .panel-btn-reject:hover {
        background: #dc2626;
    }

    /* List Table section */
    .table-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-top: 24px;
    }

    .table-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .table-card-title {
        font-size: 15px;
        font-weight: 850;
        color: var(--orb-text);
        margin: 0;
    }

    .table thead th {
        background: #F8FAFC;
        color: var(--orb-muted);
        font-weight: 850;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.6px;
        padding: 14px 20px !important;
        border-bottom: 1px solid var(--orb-border) !important;
        border-top: 0 !important;
    }

    .table tbody td {
        padding: 14px 20px !important;
        vertical-align: middle;
        border-bottom: 1px solid var(--orb-border) !important;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 600;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-block;
    }

    .status-approved { background: #ECFDF3; color: #047857; }
    .status-pending { background: #FFFBEB; color: #B45309; }
    .status-rejected { background: #FEF2F2; color: #B91C1C; }
    .status-cancelled { background: #F3F4F6; color: #4B5563; }

    @media (max-width: 992px) {
        .cal-content-row {
            flex-direction: column;
        }

        .cal-sidebar-col {
            width: 100%;
        }

        .calendar-cell {
            min-height: 80px;
        }
    }

    @media (max-width: 600px) {
        .calendar-week-headers {
            font-size: 9px;
        }

        .calendar-cell {
            min-height: 60px;
            padding: 4px;
        }

        .cell-date-num {
            font-size: 11px;
        }

        .mini-leave-chip {
            padding: 1px 3px;
            font-size: 8px;
        }
    }
</style>
@endsection

@section('_content')
<div class="calendar-page">

    <!-- Premium Purple Gradient Hero Header -->
    <div class="cal-hero">
        <div>
            <div class="cal-kicker">
                <i class="fas fa-calendar-week"></i> HRMS &bull; TEAM AVAILABILITY
            </div>
            <h1 class="cal-title">Team Leave Calendar</h1>
            <p class="cal-subtitle">View team availability, approved leaves, pending leaves, and department-wise leave planning.</p>
        </div>
        <div>
            <a href="{{ route('leave-approvals.index') }}" class="cal-hero-btn">
                <i class="fas fa-check-circle"></i> Leave Approvals
            </a>
        </div>
    </div>

    @include('components.alerts')

    <!-- dynamic database summary cards -->
    <div class="row g-3">
        <div class="col-12 col-md-4 col-lg">
            <div class="stat-card" style="border-bottom: 4px solid var(--orb-primary); height: 90px; display: flex; align-items: center; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(75, 0, 232, 0.08); color: var(--orb-primary); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-plane-departure"></i>
                </div>
                <div>
                    <small style="text-transform: uppercase; font-size: 10px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">On Leave Today</small>
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['on_leave_today'] }}</h4>
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
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['upcoming_leaves'] }}</h4>
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
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['pending_requests'] }}</h4>
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
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['approved_this_month'] }}</h4>
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
                    <h4 style="margin: 2px 0 0; font-size: 20px; font-weight: 900; color: var(--orb-text);">{{ $stats['lwp_this_month'] }}</h4>
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
                        <select name="department_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Employee</label>
                        <select name="employee_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->display_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Leave Type</label>
                        <select name="leave_type_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
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
                            <select name="month" class="form-select" onchange="this.form.submit()">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                            <select name="year" class="form-select" onchange="this.form.submit()">
                                @for($y = today()->year - 2; $y <= today()->year + 2; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <a href="{{ route('hrms.leave.team_calendar.index') }}" class="btn btn-light w-100" style="height: 40px; border-radius: 12px; border: 1px solid var(--orb-border); background: #fff; color: var(--orb-primary); font-weight: 800; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <div class="cal-content-row p-4 pt-2">
            <!-- Left Main Calendar Grid -->
            <div class="cal-main-col">
                <div class="calendar-card">
                    <div class="calendar-header">
                        <div class="fw-black text-dark" style="font-size: 16px;">
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
                                    @foreach($leavesList as $leave)
                                        @php
                                            $isLwp = ($leave->lwp_days > 0) || str_contains(strtolower($leave->leaveType->name ?? ''), 'lwp');
                                            $isHalf = $leave->is_half_day;
                                        @endphp
                                        <div class="mini-leave-chip {{ $leave->status }} {{ $isLwp ? 'lwp-badge' : '' }}" title="{{ $leave->employee->display_name }} ({{ $leave->leaveType->name }})">
                                            <span class="dot"></span>
                                            <span class="text-truncate">{{ $leave->employee->display_name }}</span>
                                            @if($isHalf)
                                                <small style="font-size: 8px; font-weight: 900; opacity: 0.85;">Half</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Hidden Div containing side-panel card elements to instantly render via JavaScript -->
                                <input type="hidden" id="leaves-count-{{ $dateStr }}" value="{{ count($leavesList) }}">
                                <div id="leaves-data-{{ $dateStr }}" style="display: none;">
                                    @foreach($leavesList as $leave)
                                        @php
                                            $isLwp = ($leave->lwp_days > 0) || str_contains(strtolower($leave->leaveType->name ?? ''), 'lwp');
                                        @endphp
                                        <div class="panel-leave-card">
                                            <h6 class="panel-leave-card-title">{{ $leave->employee->display_name }}</h6>
                                            
                                            <div class="panel-info-row">
                                                <span class="panel-info-lbl">Code</span>
                                                <span class="panel-info-val">{{ $leave->employee->employee_code }}</span>
                                            </div>
                                            <div class="panel-info-row">
                                                <span class="panel-info-lbl">Department</span>
                                                <span class="panel-info-val">{{ $leave->employee->department->name ?? 'General' }}</span>
                                            </div>
                                            <div class="panel-info-row">
                                                <span class="panel-info-lbl">Leave Type</span>
                                                <span class="panel-info-val {{ $isLwp ? 'text-danger fw-bold' : '' }}">
                                                    {{ $leave->leaveType->name }}
                                                    {!! $isLwp ? '<small class="badge badge-danger p-0.5 ml-1" style="font-size:8px;">LWP</small>' : '' !!}
                                                </span>
                                            </div>
                                            <div class="panel-info-row">
                                                <span class="panel-info-lbl">Period</span>
                                                <span class="panel-info-val text-primary">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}</span>
                                            </div>
                                            <div class="panel-info-row">
                                                <span class="panel-info-lbl">Duration</span>
                                                <span class="panel-info-val">{{ floatval($leave->requested_days) }} Days {{ $leave->is_half_day ? '(Half Day)' : '' }}</span>
                                            </div>
                                            <div class="panel-info-row">
                                                <span class="panel-info-lbl">Status</span>
                                                <span class="panel-info-val text-uppercase {{ $leave->status === 'approved' ? 'text-success' : ($leave->status === 'pending' ? 'text-warning' : 'text-danger') }}">{{ $leave->status }}</span>
                                            </div>
                                            
                                            @if($leave->reason)
                                            <div class="panel-info-row flex-column text-left">
                                                <span class="panel-info-lbl">Reason</span>
                                                <div style="font-size: 11px; font-weight: 500; color: var(--orb-text); background: #f8fafc; padding: 6px; border-radius: 8px; margin-top: 4px; border: 1px solid var(--orb-border);">
                                                    {{ $leave->reason }}
                                                </div>
                                            </div>
                                            @endif

                                            @if($leave->status === 'approved' && $leave->approver)
                                            <div class="panel-info-row mt-2">
                                                <span class="panel-info-lbl">Approved By</span>
                                                <span class="panel-info-val text-muted" style="font-size: 10px;">{{ $leave->approver->name }}</span>
                                            </div>
                                            @endif

                                            <!-- Approval Actions in detail panel (Authorized Admin/HR only) -->
                                            @if($leave->status === 'pending')
                                                @if(auth()->user()->hasPermission('leave.approvals.approve') || auth()->user()->hasPermission('leave.approvals.reject'))
                                                    <div class="panel-actions">
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

    <!-- Calendar Footer: Leave List Table -->
    <div class="table-card">
        <div class="table-card-header">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--orb-soft); color: var(--orb-primary); display: flex; align-items: center; justify-content: center; font-size: 14px;">
                <i class="fas fa-list-ul"></i>
            </div>
            <div>
                <h5 class="table-card-title">Leave Records Summary List</h5>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover w-100 nowrap">
                <thead>
                    <tr>
                        <th style="padding-left: 24px;">Employee</th>
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Period</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Reason</th>
                        @if(auth()->user()->hasPermission('leave.approvals.approve') || auth()->user()->hasPermission('leave.approvals.reject'))
                        <th class="text-right" style="padding-right: 24px;">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $item)
                        @php
                            $isLwp = ($item->lwp_days > 0) || str_contains(strtolower($item->leaveType->name ?? ''), 'lwp');
                        @endphp
                        <tr>
                            <td style="padding-left: 24px;">
                                <div class="fw-bold text-dark">{{ $item->employee->display_name }}</div>
                                <small class="text-muted">{{ $item->employee->employee_code }}</small>
                            </td>
                            <td>{{ $item->employee->department->name ?? 'General' }}</td>
                            <td>
                                <span class="badge badge-light border font-weight-bold px-2 py-1 {{ $isLwp ? 'text-danger border-danger' : '' }}" style="font-size: 11px;">
                                    {{ $item->leaveType->name }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 12px; font-weight: 700;">
                                    {{ $item->start_date->format('d M') }} - {{ $item->end_date->format('d M Y') }}
                                </div>
                            </td>
                            <td>{{ floatval($item->requested_days) }} Days</td>
                            <td>
                                <span class="status-badge status-{{ $item->status }}">{{ $item->status }}</span>
                            </td>
                            <td>
                                <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $item->reason ?? '-' }}</small>
                            </td>
                            @if(auth()->user()->hasPermission('leave.approvals.approve') || auth()->user()->hasPermission('leave.approvals.reject'))
                            <td class="text-right" style="padding-right: 24px;">
                                @if($item->status === 'pending')
                                    <div class="d-inline-flex gap-1" style="gap: 6px;">
                                        <form method="POST" action="{{ route('leave-approvals.approve', $item->id) }}" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="remark" value="Approved via list view">
                                            <button type="submit" class="btn btn-sm btn-success rounded-lg font-weight-bold px-3" onclick="return confirm('Approve request?')" style="border-radius: 8px;">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('leave-approvals.reject', $item->id) }}" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="reason" value="Rejected via list view">
                                            <button type="submit" class="btn btn-sm btn-danger rounded-lg font-weight-bold px-3" onclick="return confirm('Reject request?')" style="border-radius: 8px;">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted font-weight-bold small">-</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                                    <h5 class="font-weight-black">No team leave records found for this month</h5>
                                    <p class="small text-muted mb-0">Try clearing filters or checking another month.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('_script')
<script>
    $(document).ready(function() {
        // Automatically select today's cell, or the first cell of the current month
        const todayStr = '{{ today()->format('Y-m-d') }}';
        const initialCell = $(`.calendar-cell[data-date="${todayStr}"]`);
        
        if (initialCell.length > 0) {
            initialCell.click();
        } else {
            // Click the first current month cell
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
        
        // Update Side Panel Info
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
