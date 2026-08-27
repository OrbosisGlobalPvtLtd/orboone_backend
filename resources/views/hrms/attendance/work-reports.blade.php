@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Daily Work Reports')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')

@include('hrms.employee.partials.styles')

<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .report-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .report-container {
        max-width: 1500px;
        margin: 0 auto;
    }

    /* Premium Purple Gradient Hero Header */
    .report-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 26px !important;
        padding: 32px 36px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 28px !important;
        border: none !important;
    }

    .report-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 300px !important;
        height: 300px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .report-header-premium .title-area h3 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .report-header-premium .title-area p {
        font-size: 14px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .report-header-premium .header-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.75) !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* View Switcher Toggle Pill */
    .view-switcher-pill {
        display: inline-flex;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px;
        border-radius: 50px;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .view-switcher-btn {
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.85);
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .view-switcher-btn.active {
        background: #FFFFFF;
        color: var(--orb-primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Cards Grid View */
    .emp-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 24px;
        margin-bottom: 30px;
    }

    .emp-summary-card {
        background: #FFFFFF;
        border: 1px solid #E7EAF3;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 25px rgba(16, 24, 40, .03);
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    .emp-summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 35px rgba(75, 0, 232, 0.08);
        border-color: rgba(75, 0, 232, 0.2);
    }

    .emp-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }

    .emp-card-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-weight: 800;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, 0.1);
    }

    .emp-card-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .emp-card-name {
        font-weight: 800;
        font-size: 16px;
        color: #101828;
        margin: 0 0 2px 0;
    }

    .emp-card-meta {
        font-size: 12px;
        color: #667085;
        font-weight: 600;
    }

    .emp-stats-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        background: #F8FAFC;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid #F1F5F9;
        margin-bottom: 18px;
        text-align: center;
    }

    .emp-stat-item .stat-val {
        font-size: 15px;
        font-weight: 900;
        color: var(--orb-primary);
        line-height: 1.2;
    }

    .emp-stat-item .stat-lbl {
        font-size: 10px;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 2px;
    }

    .emp-latest-snippet {
        background: #FAFAFA;
        border: 1px solid #F1F5F9;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 18px;
        font-size: 12.5px;
        color: #334155;
    }

    .emp-latest-snippet .snippet-title {
        font-weight: 800;
        color: #0F172A;
        font-size: 12px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Slide-Out Timeline Drawer (Offcanvas) */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .drawer-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .timeline-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: 600px;
        max-width: 90vw;
        height: 100vh;
        background: #FFFFFF;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        z-index: 1060;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }

    .timeline-drawer.active {
        transform: translateX(0);
    }

    .drawer-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        color: #FFFFFF;
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .drawer-header .close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: #FFFFFF;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        transition: background 0.2s ease;
    }

    .drawer-header .close-btn:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    .drawer-body {
        padding: 28px;
        overflow-y: auto;
        flex: 1;
        background: #F8FAFC;
    }

    /* Timeline Items */
    .timeline-list {
        position: relative;
        padding-left: 24px;
    }

    .timeline-list::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 7px;
        bottom: 10px;
        width: 2px;
        background: #E2E8F0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        top: 14px;
        left: -21px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--orb-primary);
        border: 3px solid #FFFFFF;
        box-shadow: 0 0 0 2px var(--orb-primary);
    }

    .timeline-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }

    /* Table card styling */
    .orb-table-card {
        background: #fff !important;
        border-radius: 24px !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: 0 14px 35px rgba(16,24,40,.07) !important;
        overflow: hidden !important;
        margin-bottom: 30px !important;
    }

    .report-filters-attached {
        background: #F8FAFC !important;
        border-bottom: 1px solid var(--orb-border) !important;
        padding: 20px 26px 12px !important;
    }

    .report-filter-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 12px !important;
        align-items: flex-end !important;
    }

    .report-filter-grid label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .report-filter-grid .form-control {
        height: 44px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        width: 100% !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .orb-table-toolbar {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
        padding: 16px 26px !important;
        border-top: 1px solid #F1F5F9 !important;
        border-bottom: 1px solid #F1F5F9 !important;
        background: #fff !important;
    }

    .orb-table-scroll {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .orb-table-scroll table {
        min-width: 1100px !important;
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    .orb-table-scroll table thead th {
        background: #F8FAFC !important;
        color: #101828 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 16px 18px !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .orb-table-scroll table tbody td {
        padding: 16px 18px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid var(--orb-border) !important;
        background: #fff !important;
    }

    .badge-wfo { background: #E6F4EA; color: #137333; }
    .badge-wfh { background: #E8F0FE; color: #1A73E8; }
    .badge-premium-pill {
        padding: 6px 14px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .work-summary-bubble {
        background: #FAFBFC;
        border: 1px solid #EEF2F6;
        border-radius: 14px;
        padding: 12px 16px;
        color: #344054;
        font-size: 13px;
        line-height: 1.5;
    }
</style>

<div class="report-page">
    <div class="report-container">

        <!-- Premium Header Area -->
        <div class="report-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-clipboard-list"></i> Daily Work Logging
                </div>
                <h3>Daily Work Reports</h3>
                <p>Track, manage, and review employee tasks, daily progress summaries, and work details.</p>
            </div>

            <div class="d-flex align-items-center" style="gap:12px;">
                <!-- View Mode Switcher Toggle Pill -->
                <div class="view-switcher-pill">
                    <button type="button" class="view-switcher-btn active" id="btnCardsView" onclick="switchWorkReportView('cards')">
                        <i class="fas fa-th-large"></i> Employee Cards
                    </button>
                    <button type="button" class="view-switcher-btn" id="btnTableView" onclick="switchWorkReportView('table')">
                        <i class="fas fa-list"></i> All Daily Logs
                    </button>
                </div>

                @if($isAdminOrManager)
                <a href="{{ route('attendances.daily') }}" class="report-btn-pill text-white ml-2">
                    <i class="fas fa-calendar-check"></i>
                    Daily Attendance
                </a>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 12px;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
        @endif

        <!-- Filter Card Bar -->
        <div class="card orb-table-card mb-4">
            <div class="report-filters-attached">
                <form id="reportFilterForm" onsubmit="event.preventDefault();">
                    <div class="report-filter-grid">
                        @if($isAdminOrManager)
                        <div>
                            <label>Employee</label>
                            <select name="employee_id" id="filterEmployee" class="form-control select2-searchable">
                                <option value="">All Staff</option>
                                @foreach($employees as $emp)
                                <option value="{{ optional($emp->employee)->id }}">
                                    {{ $emp->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <div>
                            <label>Employee</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly disabled>
                        </div>
                        @endif

                        <div>
                            <label>From Date</label>
                            <input type="date" name="from_date" id="filterFromDate" class="form-control">
                        </div>

                        <div>
                            <label>To Date</label>
                            <input type="date" name="to_date" id="filterToDate" class="form-control">
                        </div>

                        <div>
                            <label>Search Keyword</label>
                            <input type="text" name="search" id="filterSearch" class="form-control" placeholder="Search tasks or summary...">
                        </div>

                        <div>
                            <label>&nbsp;</label>
                            <button type="submit" id="btnFilterSubmit" class="btn btn-primary btn-block rounded-12 font-weight-bold" style="height: 44px; background: var(--orb-primary); border: none;">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- VIEW MODE 1: EMPLOYEE CARDS GRID VIEW -->
        <div id="cardsViewArea">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="font-weight-bold text-dark mb-0" style="font-size:18px;">
                    <i class="fas fa-users text-primary mr-2"></i>Employee Work Summaries
                </h4>
                <span class="text-muted font-weight-bold" style="font-size:13px;" id="cardsCountLabel">
                    Showing {{ count($employeeSummaries) }} Employees
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
                        <a href="{{ route('hrms.attendance.work-reports.employee-history', $sum['employee_id']) }}" target="_blank" class="btn btn-primary btn-block rounded-12 font-weight-bold py-2 shadow-sm">
                            <i class="fas fa-history mr-2"></i> View Daily History ({{ $sum['total_reports'] }})
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5 bg-white rounded-20 border">
                    <i class="fas fa-folder-open text-muted fa-3x mb-3"></i>
                    <h5 class="font-weight-bold text-dark">No Employee Work Reports Found</h5>
                    <p class="text-muted">Adjust search filters or select a different date range.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- VIEW MODE 2: ALL DAILY LOGS TABLE VIEW (HIDDEN BY DEFAULT) -->
        <div id="tableViewArea" style="display: none;">
            <div class="card orb-table-card">
                <div class="orb-table-toolbar">
                    <div class="toolbar-left"></div>
                    <div class="toolbar-right"></div>
                </div>

                <div class="orb-table-scroll">
                    <table class="report-table table mb-0" id="workReportsTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">S.No.</th>
                                @if($isAdminOrManager)
                                <th>Employee</th>
                                @endif
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Shift Context</th>
                                <th>Gross Work</th>
                                <th>Work Summary Description</th>
                                <th>Structured Tasks</th>
                                <th class="text-right pr-4 no-export">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($workLogs as $log)
                            @php
                                $attendance = $log->attendance;
                                $mode = strtolower($attendance->work_mode ?? 'wfo');
                                $modeText = strtoupper($mode);
                                $modeBadgeClass = $mode === 'wfh' ? 'badge-wfh' : 'badge-wfo';
                                
                                $grossWork = $attendance && $attendance->gross_duration ? $attendance->gross_duration : '-';
                                    
                                $tasks = $log->work_summary_json;
                                if (is_string($tasks)) {
                                    $tasks = json_decode($tasks, true);
                                }
                                
                                $title = 'Work Report Submitted';
                                $description = null;
                                $status = 'Completed';
                                $projectsList = [];
                                $requirementsList = [];
                                $testStatus = ['tested' => false, 'completed' => false];
                                $issues = [];
                                $notes = null;

                                if (is_array($tasks)) {
                                    if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                                        $projectsList = $tasks['projects'];
                                        foreach ($projectsList as $p) {
                                            $pName = $p['project_name'] ?? $p['name'] ?? 'Project';
                                            if (isset($p['tasks']) && is_array($p['tasks'])) {
                                                foreach ($p['tasks'] as $t) {
                                                    $tName = $t['task_name'] ?? $t['description'] ?? $t['task'] ?? $t['title'] ?? 'Task';
                                                    $tDone = (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true) : true);
                                                    $requirementsList[] = [
                                                        'text' => $tName,
                                                        'done' => $tDone,
                                                        'project' => $pName
                                                    ];
                                                }
                                            }
                                        }
                                    }

                                    if (empty($requirementsList)) {
                                        $reqItems = $tasks['requirements'] ?? ($tasks['tasks'] ?? []);
                                        if (is_array($reqItems)) {
                                            $requirementsList = $reqItems;
                                        }
                                    }

                                    $status = $tasks['today_work_status'] ?? ($tasks['status'] ?? 'Completed');

                                    if (!empty($projectsList) && !empty($projectsList[0]['project_name'])) {
                                        $title = $projectsList[0]['project_name'];
                                    } elseif (!empty($tasks['task_name'])) {
                                        $title = $tasks['task_name'];
                                    } elseif (!empty($tasks['title'])) {
                                        $title = $tasks['title'];
                                    }

                                    $description = $tasks['description'] ?? ($tasks['today_work_description'] ?? 'Work report submitted.');
                                } else {
                                    $description = $log->work_summary ?? 'No summary provided.';
                                }
                                
                                $tasksCount = is_array($requirementsList) ? count($requirementsList) : 0;
                                $employeeName = optional($log->user)->name ?? 'Employee';
                                $employeeCode = optional($log->employee)->employee_code ?? 'N/A';

                                $logPayload = [
                                    'employee_name' => $employeeName,
                                    'employee_code' => $employeeCode,
                                    'passport_photo_url' => resolveEmployeePassportPhoto($log->employee ?? $log),
                                    'employee_initial' => resolveEmployeeInitials($log->employee ?? $log),
                                    'department' => optional(optional($log->employee)->department)->name ?? 'Staff',
                                    'designation' => optional(optional($log->employee)->designation)->name ?? 'Member',
                                    'work_date' => $log->work_date ? $log->work_date->format('d M Y') : '-',
                                    'shift_name' => optional(optional($log->attendance)->attendanceTime)->name ?? 'Default Shift',
                                    'attendance_status' => (optional($log->attendance)->attendance_status ?? 'present'),
                                    'title' => $title,
                                    'description' => $description,
                                    'status' => $status,
                                    'work_mode' => strtoupper(optional($log->attendance)->work_mode ?? 'WFO'),
                                    'submitted_time' => $log->created_at ? $log->created_at->format('h:i A') : '-',
                                    'projects' => $projectsList,
                                    'requirements' => $requirementsList,
                                    'test_status' => $testStatus,
                                    'issues' => $issues,
                                    'notes' => $notes,
                                ];
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold text-muted align-middle" style="font-size: 12.5px;">
                                    {{ $loop->iteration }}
                                </td>
                                @if($isAdminOrManager)
                                <td>
                                    <div class="att-emp">
                                        <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                            @if(resolveEmployeePassportPhoto($log->employee ?? $log))
                                                <img src="{{ resolveEmployeePassportPhoto($log->employee ?? $log) }}" alt="{{ $employeeName }}" class="hrms-emp-avatar-img">
                                            @else
                                                <span class="hrms-emp-avatar-fallback is-visible">{{ resolveEmployeeInitials($log->employee ?? $log) }}</span>
                                            @endif
                                        </span>
                                        <div>
                                            <div class="att-emp-name">{{ $employeeName }}</div>
                                            <div class="att-emp-code">{{ $employeeCode }}</div>
                                        </div>
                                    </div>
                                </td>
                                @endif

                                <td data-order="{{ $log->work_date ? $log->work_date->format('Y-m-d') : '' }}">
                                    <strong>{{ $log->work_date ? $log->work_date->format('d M Y') : '-' }}</strong>
                                </td>

                                <td>
                                    <span class="badge-premium-pill {{ $modeBadgeClass }}">
                                        {{ $modeText }}
                                    </span>
                                </td>

                                <td>
                                    {{ optional($attendance)->attendanceTime->name ?? 'Default Shift' }}
                                </td>

                                <td>
                                    <span style="font-weight: 700; color: #344054;">{{ $grossWork }}</span>
                                </td>

                                <td>
                                    <div class="work-summary-bubble">
                                        <i class="fas fa-file-alt text-primary mr-1"></i> {{ $title }}
                                    </div>
                                </td>

                                <td>
                                    @if($tasksCount > 0)
                                    <span class="badge badge-light border px-2 py-1 font-weight-bold">
                                        <i class="fas fa-list-check text-primary"></i> {{ $tasksCount }} Tasks
                                    </span>
                                    @else
                                    <span class="text-muted font-italic" style="font-size:12px;">None</span>
                                    @endif
                                </td>

                                <td class="text-right pr-4">
                                    <button type="button" class="btn btn-sm btn-light border p-2 rounded-10" 
                                            data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" 
                                            onclick="parseAndOpenWorkReport(this)">
                                        <i class="fas fa-eye text-primary"></i> Details
                                    </button>
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="orb-table-footer"></div>
            </div>
        </div>

    </div>
</div>

<!-- Slide-Out Employee Daily History Drawer -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeEmployeeTimelineDrawer()"></div>

<div class="timeline-drawer" id="employeeTimelineDrawer">
    <div class="drawer-header">
        <div class="d-flex align-items-center gap-3">
            <div class="emp-card-avatar border-white bg-white text-primary" id="drawerAvatar" style="width:44px; height:44px;">
                <span>E</span>
            </div>
            <div>
                <h5 class="mb-0 text-white font-weight-bold" id="drawerEmpName">Employee Name</h5>
                <div class="text-white-50 font-weight-bold" style="font-size:12px;" id="drawerEmpMeta">Code &bull; Department</div>
            </div>
        </div>
        <button type="button" class="close-btn" onclick="closeEmployeeTimelineDrawer()">&times;</button>
    </div>

    <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between text-muted font-weight-bold" style="font-size:13px;">
        <span><i class="fas fa-calendar-alt text-primary mr-1"></i> Daily Work Log Timeline</span>
        <span class="badge badge-primary px-3 py-1 rounded-50" id="drawerTotalCount">0 Logs</span>
    </div>

    <div class="drawer-body">
        <div class="timeline-list" id="drawerTimelineList">
            <!-- Rendered dynamically -->
        </div>
    </div>
</div>

<!-- Shared Premium Modal -->
@include('hrms.attendance.partials.work-report-modal')

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
    // View Switcher Function
    function switchWorkReportView(mode) {
        if (mode === 'cards') {
            $('#cardsViewArea').show();
            $('#tableViewArea').hide();
            $('#btnCardsView').addClass('active');
            $('#btnTableView').removeClass('active');
        } else {
            $('#cardsViewArea').hide();
            $('#tableViewArea').show();
            $('#btnTableView').addClass('active');
            $('#btnCardsView').removeClass('active');
            if ($.fn.DataTable.isDataTable('#workReportsTable')) {
                $('#workReportsTable').DataTable().columns.adjust().draw();
            }
        }
    }

    // Open Slide-Out Timeline Drawer
    function openEmployeeTimelineDrawer(empSummary) {
        $('#drawerEmpName').text(empSummary.user_name);
        $('#drawerEmpMeta').text(`${empSummary.employee_code} • ${empSummary.department}`);
        $('#drawerTotalCount').text(`${empSummary.total_reports} Logs`);

        if (empSummary.passport_photo_url) {
            $('#drawerAvatar').html(`<img src="${empSummary.passport_photo_url}" style="width:100%;height:100%;object-fit:cover;">`);
        } else {
            $('#drawerAvatar').html(`<span>${empSummary.employee_initial}</span>`);
        }

        let html = '';
        if (empSummary.logs && empSummary.logs.length > 0) {
            empSummary.logs.forEach(function(log) {
                const workDate = log.work_date ? new Date(log.work_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                const gross = (log.attendance && log.attendance.gross_duration) ? log.attendance.gross_duration : '-';
                const mode = (log.attendance && log.attendance.work_mode) ? log.attendance.work_mode.toUpperCase() : 'WFO';
                const modeBadge = mode === 'WFH' ? 'badge-wfh' : 'badge-wfo';
                const summaryText = log.work_summary || 'Work report submitted.';

                html += `
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="font-weight-bold text-dark" style="font-size:14px;"><i class="fas fa-calendar-day text-primary mr-1"></i> ${workDate}</span>
                                <span class="badge-premium-pill ${modeBadge}">${mode}</span>
                            </div>
                            <div class="text-muted font-weight-bold mb-2" style="font-size:12px;">
                                <i class="fas fa-clock mr-1"></i> Gross Work: <span class="text-dark">${gross}</span>
                            </div>
                            <div class="p-2 bg-light rounded-10 text-dark font-weight-bold mb-3" style="font-size:12.5px;">
                                ${summaryText}
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html = `<div class="text-center py-4 text-muted font-weight-bold">No daily work logs found for this employee.</div>`;
        }

        $('#drawerTimelineList').html(html);
        $('#drawerOverlay').addClass('active');
        $('#employeeTimelineDrawer').addClass('active');
    }

    function closeEmployeeTimelineDrawer() {
        $('#drawerOverlay').removeClass('active');
        $('#employeeTimelineDrawer').removeClass('active');
    }

    $(function() {
        // Initialize DataTables for Table View
        var table = $('#workReportsTable').DataTable({
            pageLength: 25,
            order: [[{{ $isAdminOrManager ? 1 : 0 }}, 'desc']],
            ordering: true,
            searching: true, 
            paging: true,
            info: true,
            dom: "t<'d-none'ip>",
            buttons: ['csvHtml5', 'excelHtml5', 'pdfHtml5', 'print'],
            language: {
                emptyTable: 'No work reports found.'
            }
        });

        // Instant Cards Search & Filter
        function filterEmployeeCards() {
            const empId = $('#filterEmployee').val();
            const searchVal = $('#filterSearch').val().toLowerCase();
            let visibleCount = 0;

            $('#employeeCardsGrid .card-item').each(function() {
                const cardEmpId = $(this).data('employee-id');
                const cardText = $(this).text().toLowerCase();

                let matchEmp = !empId || cardEmpId == empId;
                let matchSearch = !searchVal || cardText.includes(searchVal);

                if (matchEmp && matchSearch) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            $('#cardsCountLabel').text(`Showing ${visibleCount} Employees`);
        }

        $('#reportFilterForm').on('submit', function(e) {
            e.preventDefault();
            filterEmployeeCards();
            if (typeof table !== 'undefined') {
                const searchVal = $('#filterSearch').val();
                table.search(searchVal).draw();
            }
        });
    });
</script>
@endsection
