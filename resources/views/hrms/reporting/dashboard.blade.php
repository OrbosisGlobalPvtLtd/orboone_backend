@extends('layouts.panel', ['active' => 'reporting_dashboard'])

@section('page_title', 'Team Dashboard')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.rep-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.rep-container {
    max-width: 1550px;
    margin: 0 auto;
}

.rep-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
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

.rep-metric-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: var(--orb-shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    height: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.rep-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
}

.rep-metric-icon {
    width: 46px;
    height: 46px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
}

.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.shortcut-btn {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1E293B;
    font-weight: 700;
    font-size: 13px;
    transition: all 0.2s ease;
    text-decoration: none !important;
}

.shortcut-btn:hover {
    background: #EEF2FF;
    border-color: #C7D2FE;
    color: var(--orb-primary);
    transform: translateY(-1px);
}

/* Toolbar & Length Dropdown CSS */
.orb-table-toolbar {
    background: #FAFAFA;
    border-bottom: 1px solid #E2E8F0;
}

.dataTables_length,
.dataTables_length label {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    white-space: nowrap !important;
    margin: 0 !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    color: #475467 !important;
}

.dataTables_length select {
    width: 72px !important;
    height: 34px !important;
    padding: 4px 10px !important;
    border-radius: 8px !important;
    border: 1px solid #CBD5E1 !important;
    outline: none !important;
}

/* Export button CSS */
.orb-export-btn {
    height: 34px !important;
    padding: 0 12px !important;
    border-radius: 10px !important;
    background: #fff !important;
    border: 1px solid #E7EAF3 !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    margin-left: 6px !important;
    transition: all 0.2s ease !important;
    color: #475467 !important;
}

.orb-export-btn:hover {
    background: #F1F5F9 !important;
    color: var(--orb-primary) !important;
    border-color: rgba(75, 0, 232, 0.2) !important;
    transform: translateY(-1px) !important;
}

.dt-buttons {
    display: inline-flex !important;
    align-items: center !important;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header -->
        <div class="rep-hero">
            <div>
                <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-tachometer-alt mr-2"></i>Team Management Dashboard</h3>
                <p class="mb-0 opacity-90 small">Real-time operational monitoring, attendance tracking, daily work logs, and team performance overview.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap" style="gap: 10px;">
                <a href="{{ route('reporting.my_employees') }}" class="btn btn-light font-weight-bold px-3.5 py-2" style="border-radius: 10px; color: var(--orb-primary); font-size: 13px;">
                    <i class="fas fa-users mr-1.5"></i> My Team
                </a>
                <a href="{{ route('reporting.work_reports') }}" class="btn btn-light font-weight-bold px-3.5 py-2" style="border-radius: 10px; color: var(--orb-primary); font-size: 13px; background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.3);">
                    <i class="fas fa-file-alt mr-1.5"></i> Daily Work Reports
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row mb-4">
            <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(75, 0, 232, 0.08); color: #4B00E8;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Total Team</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 22px;">{{ $employeesCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Present Today</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 22px;">{{ $presentCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(14, 165, 233, 0.1); color: #0284C7;">
                        <i class="fas fa-laptop-house"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">WFH Today</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 22px;">{{ $wfhCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2 mb-3 mb-sm-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(245, 158, 11, 0.1); color: #D97706;">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">On Leave</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 22px;">{{ $onLeaveCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2 mb-3 mb-sm-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(168, 85, 247, 0.1); color: #9333EA;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Work Reports</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 22px;">{{ $workReportsSubmittedToday }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(99, 102, 241, 0.1); color: #4F46E5;">
                        <i class="fas fa-diagram-project"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Active Projects</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 22px;">{{ $projectsCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Breakdown & Quick Shortcuts Row -->
        <div class="row mb-4">
            <!-- Tasks Breakdown Progress Widget -->
            <div class="col-12 col-lg-7 mb-4 mb-lg-0">
                <div class="rep-card h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between border-bottom bg-white" style="padding: 12px 20px;">
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <span style="width: 34px; height: 34px; border-radius: 9px; background: #EEF2FF; color: #4F46E5; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">
                                <i class="fas fa-tasks"></i>
                            </span>
                            <div>
                                <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">Team Task Completion Health</h5>
                            </div>
                        </div>
                        <span class="badge badge-light border font-weight-bold text-dark px-2.5 py-1" style="font-size: 12px; border-radius: 8px;">
                            Total Tasks: {{ $taskStats['total'] }}
                        </span>
                    </div>

                    <div class="p-4">
                        @php
                            $completedPct = $taskStats['total'] > 0 ? round(($taskStats['completed'] / $taskStats['total']) * 100) : 0;
                            $inProgressPct = $taskStats['total'] > 0 ? round(($taskStats['in_progress'] / $taskStats['total']) * 100) : 0;
                            $todoPct = $taskStats['total'] > 0 ? round(($taskStats['todo'] / $taskStats['total']) * 100) : 0;
                            $blockedPct = $taskStats['total'] > 0 ? round(($taskStats['blocked'] / $taskStats['total']) * 100) : 0;
                        @endphp

                        <!-- Overall Progress Bar -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="font-weight-bold text-dark small">Overall Completion Rate</span>
                                <span class="font-weight-extrabold text-success" style="font-size: 16px;">{{ $completedPct }}%</span>
                            </div>
                            <div class="progress" style="height: 12px; border-radius: 8px; background: #F1F5F9; overflow: hidden;">
                                <div class="progress-bar bg-success" style="width: {{ $completedPct }}%;"></div>
                                <div class="progress-bar bg-info" style="width: {{ $inProgressPct }}%;"></div>
                                <div class="progress-bar bg-secondary" style="width: {{ $todoPct }}%;"></div>
                                <div class="progress-bar bg-danger" style="width: {{ $blockedPct }}%;"></div>
                            </div>
                        </div>

                        <!-- Task Breakdown Badges Grid -->
                        <div class="row">
                            <div class="col-6 col-sm-3 mb-2 mb-sm-0">
                                <div class="p-3 rounded-lg border text-center" style="background: #F0FDF4; border-color: #DCFCE7 !important;">
                                    <div class="text-muted small font-weight-bold text-uppercase mb-1" style="font-size: 10px; color: #166534;">Completed</div>
                                    <div class="h5 font-weight-extrabold mb-0 text-success" style="font-size: 18px;">{{ $taskStats['completed'] }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 mb-2 mb-sm-0">
                                <div class="p-3 rounded-lg border text-center" style="background: #F0F9FF; border-color: #E0F2FE !important;">
                                    <div class="text-muted small font-weight-bold text-uppercase mb-1" style="font-size: 10px; color: #075985;">In Progress</div>
                                    <div class="h5 font-weight-extrabold mb-0 text-info" style="font-size: 18px;">{{ $taskStats['in_progress'] }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="p-3 rounded-lg border text-center" style="background: #F8FAFC; border-color: #E2E8F0 !important;">
                                    <div class="text-muted small font-weight-bold text-uppercase mb-1" style="font-size: 10px; color: #475569;">To Do</div>
                                    <div class="h5 font-weight-extrabold mb-0 text-secondary" style="font-size: 18px;">{{ $taskStats['todo'] }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="p-3 rounded-lg border text-center" style="background: #FEF2F2; border-color: #FEE2E2 !important;">
                                    <div class="text-muted small font-weight-bold text-uppercase mb-1" style="font-size: 10px; color: #991B1B;">Blocked</div>
                                    <div class="h5 font-weight-extrabold mb-0 text-danger" style="font-size: 18px;">{{ $taskStats['blocked'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Quick Actions & Shortcuts -->
            <div class="col-12 col-lg-5">
                <div class="rep-card h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between border-bottom bg-white" style="padding: 12px 20px;">
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <span style="width: 34px; height: 34px; border-radius: 9px; background: #EEF2FF; color: #4F46E5; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">
                                <i class="fas fa-th-large"></i>
                            </span>
                            <div>
                                <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">Team Management Modules</h5>
                            </div>
                        </div>
                    </div>

                    <div class="p-3">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                            <a href="{{ route('reporting.my_employees') }}" class="shortcut-btn">
                                <i class="fas fa-users text-primary" style="font-size: 16px;"></i>
                                <span>My Team</span>
                            </a>
                            <a href="{{ route('reporting.attendance') }}" class="shortcut-btn">
                                <i class="fas fa-user-clock text-success" style="font-size: 16px;"></i>
                                <span>Attendance</span>
                            </a>
                            <a href="{{ route('reporting.leave') }}" class="shortcut-btn">
                                <i class="fas fa-plane-departure text-warning" style="font-size: 16px;"></i>
                                <span>Team Leave</span>
                            </a>
                            <a href="{{ route('reporting.work_reports') }}" class="shortcut-btn">
                                <i class="fas fa-file-invoice text-info" style="font-size: 16px;"></i>
                                <span>Daily Reports</span>
                            </a>
                            <a href="{{ route('reporting.assignments') }}" class="shortcut-btn">
                                <i class="fas fa-user-shield text-purple" style="color: #9333EA; font-size: 16px;"></i>
                                <span>Supervision</span>
                            </a>
                            <a href="{{ route('reporting.projects') }}" class="shortcut-btn">
                                <i class="fas fa-project-diagram text-indigo" style="color: #4F46E5; font-size: 16px;"></i>
                                <span>Projects & Tasks</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Team Operational Live Status Card -->
        <div class="rep-card">
            <!-- Card Header Title (Compact height) -->
            <div class="d-flex align-items-center justify-content-between border-bottom bg-white flex-wrap" style="padding: 12px 20px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 9px; background: #EEF2FF; color: #4F46E5; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="fas fa-stream"></i>
                    </span>
                    <div>
                        <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">Today's Team Operational Live Status</h5>
                    </div>
                </div>
            </div>

            <!-- Embedded Attached Filters inside Card -->
            <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="form-inline flex-wrap gap-2" style="gap: 12px;">
                    <input type="text" id="filter-search-dashboard" class="form-control" placeholder="Search employee, designation..." style="border-radius: 10px; font-size: 13px; height: 38px; min-width: 280px;">
                </div>

                <button type="button" class="btn btn-light border font-weight-bold" id="btn-reset-dashboard-filters" style="border-radius: 10px; font-size: 13px; color: #475467; height: 38px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-undo text-muted" style="font-size: 11px;"></i> Reset Filter
                </button>
            </div>

            <!-- Toolbar for Entries & Export Buttons -->
            <div class="orb-table-toolbar d-flex align-items-center justify-content-between p-3 border-bottom">
                <div class="toolbar-left"></div>
                <div class="toolbar-right d-flex align-items-center"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="dashboardTeamTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 55px;">S.No.</th>
                            <th class="py-3 px-4">Employee Name</th>
                            <th class="py-3">Designation & Department</th>
                            <th class="py-3 text-center">Attendance Today</th>
                            <th class="py-3 text-center">Leave Status</th>
                            <th class="py-3 text-center">Work Report</th>
                            <th class="py-3">Tasks & Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDevelopers as $item)
                            @php
                                $emp = $item['employee'];
                                $displayName = $emp->display_name ?? (optional($emp->user ?? null)->name ?? 'Employee');
                                $empCode = $emp->employee_code ?? 'N/A';
                                $empExportText = $displayName . ' (' . $empCode . ')';

                                $att = $item['attendance'];
                                $lve = $item['leave'];
                                $wlog = $item['work_log'];
                                $totTasks = (int)($item['total_tasks'] ?? 0);
                                $compTasks = (int)($item['completed_tasks'] ?? 0);

                                $attText = 'NOT PUNCHED';
                                $attStyle = 'background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;';
                                $attIcon = 'far fa-circle';

                                if ($lve) {
                                    $attText = 'ON LEAVE';
                                    $attStyle = 'background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;';
                                    $attIcon = 'fas fa-umbrella-beach';
                                } elseif ($att) {
                                    $attText = strtoupper($att->work_type ?? 'Present');
                                    $attStyle = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                    $attIcon = 'fas fa-check-circle';
                                }

                                $leaveText = $lve ? 'On Leave' : 'No Leave';

                                $reportText = $wlog ? 'Submitted' : 'Pending';
                                $reportStyle = $wlog 
                                    ? 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;'
                                    : 'background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;';

                                $taskPct = $totTasks > 0 ? round(($compTasks / $totTasks) * 100) : 0;
                            @endphp
                        <tr>
                            <!-- S.No. -->
                            <td class="py-3 px-3 align-middle text-center font-weight-bold text-muted" style="font-size: 12.5px;" data-export="{{ $loop->iteration }}">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Employee Name -->
                            <td class="py-3 px-4 align-middle" data-export="{{ $empExportText }}">
                                <div>
                                    <strong class="text-dark font-weight-bold d-block" style="line-height: 1.25; font-size: 13.5px;">{{ $displayName }}</strong>
                                    <small class="text-muted" style="font-size: 11px; font-weight: 600;">{{ $empCode }}</small>
                                </div>
                            </td>

                            <!-- Designation & Department -->
                            <td class="py-3 align-middle" data-export="{{ optional($emp->designation ?? null)->name ?? 'Staff' }} - {{ optional($emp->department ?? null)->name ?? 'General' }}">
                                <div>
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 12.5px; line-height: 1.2;">
                                        {{ optional($emp->designation ?? null)->name ?? 'Employee' }}
                                    </span>
                                    <small class="text-muted" style="font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-building text-muted mr-1" style="font-size: 10px;"></i>{{ optional($emp->department ?? null)->name ?? 'General' }}
                                    </small>
                                </div>
                            </td>

                            <!-- Attendance Today -->
                            <td class="py-3 align-middle text-center" data-export="{{ $attText }}">
                                <span class="badge font-weight-bold text-uppercase px-2.5 py-1" style="border-radius: 8px; font-size: 10px; letter-spacing: 0.04em; {{ $attStyle }}">
                                    <i class="{{ $attIcon }} mr-1"></i> {{ $attText }}
                                </span>
                                @if($att && isset($att->punch_in_time))
                                    <small class="d-block text-muted mt-1 font-weight-bold" style="font-size: 10.5px;">
                                        {{ \Carbon\Carbon::parse($att->punch_in_time)->format('h:i A') }}
                                    </small>
                                @endif
                            </td>

                            <!-- Leave Status -->
                            <td class="py-3 align-middle text-center" data-export="{{ $leaveText }}">
                                @if($lve)
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 8px; font-size: 10.5px; background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;">
                                        <i class="fas fa-umbrella-beach mr-1"></i> On Leave
                                    </span>
                                @else
                                    <span class="small text-muted font-weight-bold">No Leave</span>
                                @endif
                            </td>

                            <!-- Work Report Status -->
                            <td class="py-3 align-middle text-center" data-export="{{ $reportText }}">
                                <span class="badge font-weight-bold text-uppercase px-2.5 py-1" style="border-radius: 8px; font-size: 10px; letter-spacing: 0.04em; {{ $reportStyle }}">
                                    <i class="fas fa-check-circle mr-1"></i> {{ $reportText }}
                                </span>
                            </td>

                            <!-- Task Progress -->
                            <td class="py-3 align-middle" style="min-width: 170px;" data-export="{{ $totTasks > 0 ? $compTasks . '/' . $totTasks . ' Done (' . $taskPct . '%)' : 'No Tasks' }}">
                                @if($totTasks > 0)
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="small font-weight-bold text-dark" style="font-size: 11.5px;">{{ $compTasks }}/{{ $totTasks }} Done</span>
                                        <small class="font-weight-extrabold text-primary" style="font-size: 11px;">{{ $taskPct }}%</small>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 4px; background: #E2E8F0;">
                                        <div class="progress-bar bg-success" style="width: {{ $taskPct }}%;"></div>
                                    </div>
                                @else
                                    <span class="small text-muted font-weight-bold">No Active Tasks</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Active Reporting Employees Found</h5>
                                <p class="small mb-0">Employees under your supervision will appear here once assigned.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer for Pagination & Info (Populated by DataTables) -->
            <div class="orb-table-footer p-3 bg-light border-top d-flex align-items-center justify-content-between"></div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(function() {
        if ($.fn.DataTable.isDataTable('#dashboardTeamTable')) {
            $('#dashboardTeamTable').DataTable().destroy();
        }

        const exportOptionsDefault = {
            format: {
                body: function ( data, row, column, node ) {
                    if (node && node.hasAttribute('data-export')) {
                        return node.getAttribute('data-export');
                    }
                    if (typeof data === 'string') {
                        var temp = document.createElement("div");
                        temp.innerHTML = data;
                        return (temp.textContent || temp.innerText || "").trim();
                    }
                    return data;
                }
            }
        };

        var table = $('#dashboardTeamTable').DataTable({
            pageLength: 25,
            ordering: false,
            searching: true, 
            paging: true,
            info: true,
            responsive: false,
            autoWidth: false,
            dom: "t<'d-none'ip>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-info"></i> CSV',
                    className: 'orb-export-btn',
                    exportOptions: exportOptionsDefault
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'orb-export-btn',
                    exportOptions: exportOptionsDefault
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'orb-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'OrboOne HRMS - Team Management Dashboard Overview',
                    exportOptions: exportOptionsDefault,
                    customize: function (doc) {
                        doc.pageOrientation = 'landscape';
                        doc.pageSize = 'A4';
                        doc.pageMargins = [20, 45, 20, 35];

                        doc['header'] = function(currentPage, pageCount) {
                            return {
                                margin: [20, 15, 20, 0],
                                columns: [
                                    {
                                        text: 'ORBOONE HRMS — TEAM MANAGEMENT DASHBOARD',
                                        fontSize: 9,
                                        bold: true,
                                        color: '#4B00E8'
                                    },
                                    {
                                        text: 'Page ' + currentPage.toString() + ' of ' + pageCount,
                                        alignment: 'right',
                                        fontSize: 9,
                                        color: '#64748B'
                                    }
                                ]
                            };
                        };

                        var objLayout = {};
                        objLayout['hLineWidth'] = function(i) { return 0.5; };
                        objLayout['vLineWidth'] = function(i) { return 0; };
                        objLayout['hLineColor'] = function(i) { return '#CBD5E1'; };
                        objLayout['paddingLeft'] = function(i) { return 8; };
                        objLayout['paddingRight'] = function(i) { return 8; };
                        objLayout['paddingTop'] = function(i) { return 6; };
                        objLayout['paddingBottom'] = function(i) { return 6; };
                        doc.content[1].layout = objLayout;

                        var headerRow = doc.content[1].table.body[0];
                        for (var i = 0; i < headerRow.length; i++) {
                            headerRow[i].fillColor = '#1E293B';
                            headerRow[i].color = '#FFFFFF';
                            headerRow[i].fontSize = 9.5;
                            headerRow[i].bold = true;
                        }

                        doc.content[1].table.widths = ['6%', '22%', '22%', '14%', '12%', '12%', '12%'];
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'orb-export-btn',
                    title: '',
                    exportOptions: exportOptionsDefault,
                    customize: function (win) {
                        var body = $(win.document.body);

                        $(win.document.head).append(`
                            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
                            <style>
                                @media print {
                                    @page {
                                        size: A4 landscape;
                                        margin: 10mm 12mm;
                                    }
                                }
                                body {
                                    font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
                                    color: #0F172A !important;
                                    background: #FFFFFF !important;
                                    padding: 15px !important;
                                    margin: 0 !important;
                                }
                                .print-hero {
                                    background: linear-gradient(135deg, #4B00E8 0%, #FF5252 100%) !important;
                                    border-radius: 12px !important;
                                    padding: 16px 22px !important;
                                    color: #FFFFFF !important;
                                    margin-bottom: 20px !important;
                                    display: flex !important;
                                    align-items: center !important;
                                    justify-content: space-between !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .print-hero h2 {
                                    margin: 0 !important;
                                    font-size: 20px !important;
                                    font-weight: 800 !important;
                                    color: #FFFFFF !important;
                                }
                                .print-hero p {
                                    margin: 2px 0 0 0 !important;
                                    font-size: 12px !important;
                                    opacity: 0.92 !important;
                                    color: #FFFFFF !important;
                                }
                                table.dataTable {
                                    width: 100% !important;
                                    border-collapse: separate !important;
                                    border-spacing: 0 !important;
                                    border-radius: 10px !important;
                                    overflow: hidden !important;
                                    border: 1px solid #CBD5E1 !important;
                                    margin-top: 10px !important;
                                }
                                table.dataTable thead th {
                                    background: #1E293B !important;
                                    color: #FFFFFF !important;
                                    font-size: 11px !important;
                                    font-weight: 800 !important;
                                    text-transform: uppercase !important;
                                    padding: 10px 14px !important;
                                    border: none !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                table.dataTable tbody td {
                                    padding: 10px 14px !important;
                                    border-bottom: 1px solid #E2E8F0 !important;
                                    font-size: 11.5px !important;
                                }
                                table.dataTable tbody tr:nth-child(even) {
                                    background: #F8FAFC !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                            </style>
                        `);

                        body.find('h1').remove();

                        var printDate = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                        body.prepend(`
                            <div class="print-hero">
                                <div>
                                    <h2>OrboOne HRMS</h2>
                                    <p>Team Management — Live Operational Status Summary</p>
                                </div>
                                <div style="background: rgba(255, 255, 255, 0.22); padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700;">
                                    Date: ${printDate}
                                </div>
                            </div>
                        `);
                    }
                }
            ],
            language: {
                emptyTable: 'No team members currently assigned.',
                zeroRecords: 'No matching team members found.',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ team members',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        // Inject the entries dropdown on the left, and print/export buttons on the right
        $('.orb-table-toolbar .toolbar-left').html(`
            <div class="dataTables_length">
                <label>Show 
                    <select class="form-control" id="custom-length-select">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="-1">All</option>
                    </select> entries
                </label>
            </div>
        `);
        
        $('.orb-table-toolbar .toolbar-right').append(table.buttons().container());

        $('#custom-length-select').on('change', function() {
            table.page.len($(this).val()).draw();
        });

        // Instant Filter search listener
        $('#filter-search-dashboard').on('keyup change clear', function() {
            table.search($(this).val()).draw();
        });

        $('#btn-reset-dashboard-filters').on('click', function() {
            $('#filter-search-dashboard').val('');
            table.search('').draw();
        });
    });
</script>
@endsection
