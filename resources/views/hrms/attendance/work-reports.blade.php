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

    /* Premium Pill Buttons */
    .report-btn-pill {
        height: 42px !important;
        padding: 0 20px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        cursor: pointer !important;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
    }

    .report-btn-pill:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
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

    /* Table Toolbar */
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

    .orb-table-toolbar .toolbar-left {
        display: flex !important;
        align-items: center !important;
    }

    .orb-table-toolbar .toolbar-right {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    /* Attached Filters Area inside Table Card */
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

    .report-filter-grid .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    /* Entries Dropdown CSS */
    .dataTables_length,
    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
        margin: 0 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: var(--orb-muted) !important;
    }

    .dataTables_length select {
        width: 72px !important;
        height: 34px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
        border: 1px solid var(--orb-border) !important;
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
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
        transform: translateY(-1px) !important;
    }

    /* Table Scroll area */
    .orb-table-scroll {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        border: none !important;
    }

    .orb-table-scroll table {
        min-width: 1100px !important;
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    /* Table Header CSS */
    .orb-table-scroll table thead th {
        background: #F8FAFC !important;
        color: #101828 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 16px 18px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .orb-table-scroll table tbody td {
        padding: 16px 18px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        background: #fff !important;
    }

    .orb-table-scroll table tbody tr:hover td {
        background: #FAFBFF !important;
    }

    /* Table Footer styling */
    .orb-table-footer {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
        padding: 18px 26px 24px !important;
        background: #fff !important;
        border-top: 1px solid var(--orb-border) !important;
    }

    .dataTables_info {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-muted) !important;
    }

    .page-link {
        border-radius: 8px !important;
        margin: 0 2px;
        padding: 6px 12px !important;
        border-color: var(--orb-border);
        color: var(--orb-primary);
        font-weight: 700;
        font-size: 12.5px;
    }

    .page-item.active .page-link {
        background: var(--orb-primary) !important;
        border-color: var(--orb-primary) !important;
        color: #fff !important;
    }

    .btn-undo:hover {
        background: #F8FAFC !important;
        border-color: #cbd5e1 !important;
        color: var(--orb-primary) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }

    /* Avatar & Details Badge */
    .att-emp {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
    }

    .att-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #E0D4FF;
        color: var(--orb-primary);
        font-weight: 800;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative !important;
        overflow: hidden !important;
    }

    .att-avatar-img {
        width: 38px !important;
        height: 38px !important;
        border-radius: 12px !important;
        object-fit: cover !important;
        display: block !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
        flex-shrink: 0 !important;
    }

    .att-emp-name {
        font-weight: 700;
        color: #101828;
        font-size: 13.5px;
    }

    .att-emp-code {
        color: #667085;
        font-size: 11px;
        font-weight: 600;
        margin-top: 2px;
    }

    .badge-premium-pill {
        padding: 6px 14px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-wfo {
        background: #E6F4EA;
        color: #137333;
    }

    .badge-wfh {
        background: #E8F0FE;
        color: #1A73E8;
    }

    .work-summary-bubble {
        background: #FAFBFC;
        border: 1px solid #EEF2F6;
        border-radius: 14px;
        padding: 12px 16px;
        color: #344054;
        font-size: 13px;
        line-height: 1.5;
        max-width: 500px;
        word-break: break-word;
    }

    .structured-tasks-count {
        background: #EEF2F6;
        color: #344054;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    @media (max-width: 991px) {
        .report-header-premium {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 24px !important;
        }
        .report-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 575px) {
        .report-filter-grid {
            grid-template-columns: 1fr !important;
        }
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

            @if($isAdminOrManager)
            <div class="d-flex align-items-center" style="gap:12px;">
                <a href="{{ route('attendances.daily') }}" class="report-btn-pill text-white">
                    <i class="fas fa-calendar-check"></i>
                    Daily Attendance Sheet
                </a>
            </div>
            @endif
        </div>

        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 12px;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
        @endif

        <!-- Main Card -->
        <div class="card orb-table-card">

            <div class="orb-table-card-header d-flex align-items-center justify-content-between" style="padding: 24px 26px 18px; border-bottom: 1px solid #EEF2F7; background: #fff; flex-wrap: wrap; gap: 16px;">
                <div class="orb-title-wrap d-flex align-items-center" style="gap: 16px;">
                    <span class="orb-card-icon" style="width: 46px; height: 46px; border-radius: 12px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-tasks"></i>
                    </span>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #101828;">Work Reports List</h3>
                        <p style="margin: 4px 0 0 0; font-size: 13px; color: #667085;">Review structured logs and summaries of daily tasks submitted at punch-out.</p>
                    </div>
                </div>

                <!-- Reset Filters Button in Card Header -->
                <button type="button" class="btn btn-undo btn-outline-secondary btn-sm d-flex align-items-center" style="height: 40px !important; border-radius: 10px !important; padding: 0 16px !important; font-size: 13px !important; font-weight: 700 !important; border: 1px solid #e2e8f0 !important; color: #475467 !important; background: #fff !important; transition: all 0.2s ease !important; cursor: pointer;" onclick="resetFilters()">
                    <i class="fas fa-undo mr-2" style="font-size: 11px;"></i> Reset Filters
                </button>
            </div>

            <!-- Attached Filters inside the Card (No page reload, filters apply instantly) -->
            <div class="report-filters-attached">
                <form id="reportFilterForm" onsubmit="event.preventDefault();">
                    <div class="report-filter-grid">

                        @if($isAdminOrManager)
                        <div>
                            <label>Employee</label>
                            <select name="employee_id" class="form-control">
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
                            <input type="date" name="from_date" class="form-control">
                        </div>

                        <div>
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control">
                        </div>

                        <div>
                            <label>Search Keyword</label>
                            <input type="text" name="search" class="form-control" placeholder="Search tasks...">
                        </div>

                    </div>
                </form>
            </div>

            <!-- Custom DataTables Toolbar Row -->
            <div class="orb-table-toolbar">
                <div class="toolbar-left"></div>
                <div class="toolbar-right"></div>
            </div>

            <!-- Scrollable Table Body Only -->
            <div class="orb-table-scroll">
                <table class="report-table table mb-0" id="workReportsTable">
                    <thead>
                        <tr>
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
                            
                            $punchIn = $attendance && $attendance->punch_in_time 
                                ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') 
                                : '-';
                            $punchOut = $attendance && $attendance->punch_out_time 
                                ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') 
                                : '-';
                            $grossWork = $attendance && $attendance->gross_duration 
                                ? $attendance->gross_duration 
                                : '-';
                                
                            $tasks = $log->work_summary_json;
                            if (is_string($tasks)) {
                                $tasks = json_decode($tasks, true);
                            }
                            
                            $title = 'Work Report Submitted';
                            $description = $log->work_summary;
                            $status = 'Completed';
                            $requirementsList = [];
                            $testStatus = ['tested' => false, 'completed' => false];
                            $issues = [];
                            $notes = null;

                            if (is_array($tasks)) {
                                if (array_keys($tasks) !== range(0, count($tasks) - 1)) {
                                    $title = $tasks['title'] ?? ($tasks['task_title'] ?? 'Work Report Submitted');
                                    $description = $tasks['description'] ?? $log->work_summary;
                                    $status = $tasks['status'] ?? 'Completed';
                                    $requirementsList = $tasks['requirements'] ?? ($tasks['tasks'] ?? []);
                                    
                                    // Extract test status
                                    if (isset($tasks['test_status']) && is_array($tasks['test_status'])) {
                                        $testStatus = [
                                            'tested' => $tasks['test_status']['tested'] ?? false,
                                            'completed' => $tasks['test_status']['completed'] ?? false
                                        ];
                                    } else {
                                        $testedVal = $tasks['tested'] ?? false;
                                        $testStatus = [
                                            'tested' => ($testedVal === true || $testedVal === 'yes' || $testedVal === 'tested' || $testedVal === 'Completed'),
                                            'completed' => ($testedVal === true || $testedVal === 'yes' || $testedVal === 'tested' || $testedVal === 'Completed')
                                        ];
                                    }
                                    
                                    $issues = $tasks['issues'] ?? [];
                                    $notes = $tasks['notes'] ?? null;
                                } else {
                                    $requirementsList = $tasks;
                                }
                            }
                            
                            if (!is_array($issues)) {
                                $issues = $issues ? [$issues] : [];
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
                                'attendance_status' => (optional($log->attendance)->attendance_status ?? 'present') === 'absent' && (optional($log->attendance)->is_lwp ?? false) ? '🔴 ABSENT' : (optional($log->attendance)->attendance_status ?? 'present'),
                                'is_lwp' => (bool) (optional($log->attendance)->is_lwp ?? false),
                                'title' => $title,
                                'description' => $description,
                                'status' => $status,
                                'work_mode' => strtoupper(optional($log->attendance)->work_mode ?? 'WFO'),
                                'submitted_time' => $log->created_at ? $log->created_at->format('h:i A') : '-',
                                'requirements' => $requirementsList,
                                'test_status' => $testStatus,
                                'issues' => $issues,
                                'notes' => $notes,
                            ];

                            $exportReport = $title;
                            if (!empty($description)) {
                                $exportReport .= " - " . $description;
                            }
                            if (!empty($issues)) {
                                $realIssues = array_filter($issues, function($item) {
                                    if (!is_string($item)) return true;
                                    $val = strtolower(trim($item));
                                    return $val !== 'no issues' && $val !== 'none' && strlen($val) > 0;
                                });
                                if (!empty($realIssues)) {
                                    $exportReport .= " (Blocker: " . implode(', ', $realIssues) . ")";
                                }
                            }
                            if ($notes) {
                                $exportReport .= " (Notes: " . $notes . ")";
                            }

                            $exportTasks = 'None';
                            if ($tasksCount > 0 && is_array($requirementsList)) {
                                $taskTexts = [];
                                foreach ($requirementsList as $taskItem) {
                                    if (is_string($taskItem)) {
                                        $taskTexts[] = $taskItem;
                                    } elseif (is_array($taskItem)) {
                                        $tText = $taskItem['text'] ?? $taskItem['task'] ?? $taskItem['title'] ?? $taskItem['description'] ?? '';
                                        if ($tText) {
                                            $isDone = false;
                                            if (isset($taskItem['done'])) {
                                                $isDone = ($taskItem['done'] === true || $taskItem['done'] === 'true');
                                            } else {
                                                $tStatus = strtolower($taskItem['status'] ?? 'completed');
                                                $isDone = ($tStatus === 'completed' || $tStatus === 'done' || $tStatus === 'success');
                                            }
                                            $taskTexts[] = ($isDone ? '✓ ' : '  ') . $tText;
                                        }
                                    }
                                }
                                if (!empty($taskTexts)) {
                                    $exportTasks = implode("\n", $taskTexts);
                                }
                            }
                        @endphp
                        <tr>
                            @if($isAdminOrManager)
                            <td data-export="{{ $employeeName }} ({{ $employeeCode }})">
                                <div class="att-emp">
                                    @php
                                        $passportPhotoUrl = resolveEmployeePassportPhoto($log->employee ?? $log);
                                        $employeeInitial = resolveEmployeeInitials($log->employee ?? $log);
                                    @endphp
                                    <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                        @if($passportPhotoUrl)
                                            <img
                                                src="{{ $passportPhotoUrl }}"
                                                alt="{{ $employeeName }}"
                                                class="hrms-emp-avatar-img"
                                                onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');"
                                            >
                                            <span class="hrms-emp-avatar-fallback is-hidden">
                                                {{ $employeeInitial }}
                                            </span>
                                        @else
                                            <span class="hrms-emp-avatar-fallback is-visible">
                                                {{ $employeeInitial }}
                                            </span>
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

                            <td data-export="{{ $modeText }}">
                                <span class="badge-premium-pill {{ $modeBadgeClass }}">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor; display: inline-block;"></span>
                                    {{ $modeText }}
                                </span>
                            </td>

                            <td>
                                {{ optional($attendance)->attendanceTime->name ?? 'Default Shift' }}
                            </td>

                            <td data-export="{{ $grossWork }}">
                                 <span style="font-weight: 700; color: #344054;">{{ $grossWork }}</span>
                             </td>

                            <td data-export="{{ $exportReport }}">
                                <div class="work-summary-bubble" style="font-weight: 700; color: #1D2939; background: #F8FAFC; border: 1px solid #E4E7EC; border-radius: 12px; padding: 10px 14px;">
                                    <i class="fas fa-file-alt text-primary mr-1"></i> {{ $title }}
                                </div>
                            </td>

                            <td data-export="{{ $exportTasks }}">
                                @if($tasksCount > 0)
                                <span class="structured-tasks-count">
                                    <i class="fas fa-list-check"></i> {{ $tasksCount }} {{ Str::plural('Task', $tasksCount) }}
                                </span>
                                @else
                                <span class="text-muted" style="font-size: 12px; font-style: italic;">None</span>
                                @endif
                            </td>

                            <td class="text-right pr-4">
                                <button type="button" class="btn btn-sm btn-light border p-2" style="border-radius: 10px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;" data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" onclick="parseAndOpenWorkReport(this)">
                                    <i class="fas fa-eye text-primary"></i> View Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer for Pagination & Info (Populated by DataTables) -->
            <div class="orb-table-footer"></div>

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
    function resetFilters() {
        $('input[name="from_date"]').val('');
        $('input[name="to_date"]').val('');
        $('input[name="search"]').val('');
        @if($isAdminOrManager)
        $('select[name="employee_id"]').val('');
        @endif
        
        var table = $('#workReportsTable').DataTable();
        table.search('');
        @if($isAdminOrManager)
        table.column(0).search('');
        @endif
        table.draw();
    }

    $(function() {
        if ($.fn.DataTable.isDataTable('#workReportsTable')) {
            $('#workReportsTable').DataTable().destroy();
        }

        const exportOptionsExcel = {
            columns: ':not(.no-export)',
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

        const exportOptionsPdf = {
            columns: ':not(.no-export)',
            format: {
                body: function ( data, row, column, node ) {
                    if (node && node.hasAttribute('data-export')) {
                        let val = node.getAttribute('data-export');
                        return val.replace(/✓/g, '[Done]');
                    }
                    if (typeof data === 'string') {
                        var temp = document.createElement("div");
                        temp.innerHTML = data;
                        return (temp.textContent || temp.innerText || "").trim().replace(/✓/g, '[Done]');
                    }
                    return data;
                }
            }
        };

        const exportOptionsPrint = {
            columns: ':not(.no-export)',
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

        var table = $('#workReportsTable').DataTable({
            pageLength: 25,
            order: [[{{ $isAdminOrManager ? 1 : 0 }}, 'desc']],
            ordering: true,
            searching: true, 
            paging: true,
            info: true,
            responsive: false,
            autoWidth: false,
            dom: "t<'d-none'ip>", // Customize standard pagination location
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-info"></i> CSV',
                    className: 'orb-export-btn',
                    exportOptions: exportOptionsExcel
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'orb-export-btn',
                    exportOptions: exportOptionsExcel,
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        $('row c', sheet).each(function () {
                            if ($('is t', this).text().indexOf('\n') !== -1) {
                                $(this).attr('s', '55'); // wrapped text style
                            }
                        });
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'orb-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Daily Work Reports',
                    exportOptions: exportOptionsPdf
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'orb-export-btn',
                    title: 'Daily Work Reports',
                    exportOptions: exportOptionsPrint,
                    customize: function (win) {
                        $(win.document.body).find('table').find('td').css('white-space', 'pre-line');
                    }
                }
            ],
            language: {
                emptyTable: 'No work reports found.',
                zeroRecords: 'No matching work reports found.',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ reports',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        // Initialize DataTable Custom Toolbar inside the premium card
        const toolbar = $('#workReportsTable').DataTable({
            destroy: true,
            retrieve: true
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

        // Inject Pagination and Info into .orb-table-footer
        function updateFooterControls() {
            const info = table.page.info();
            $('.orb-table-footer').html(`
                <div class="footer-left">
                    Showing ${info.start + 1} to ${info.end} of ${info.recordsDisplay} reports ${info.recordsDisplay < info.recordsTotal ? `(filtered from ${info.recordsTotal} total)` : ''}
                </div>
                <div class="footer-right" id="dt-pagination-area"></div>
            `);

            // Copy DataTable actual pagination to our custom footer element
            const dtPaging = $('#workReportsTable_paginate');
            if (dtPaging.length) {
                $('#dt-pagination-area').html(dtPaging.html());
                
                // Bind click events of pagination to draw again
                $('#dt-pagination-area .paginate_button').on('click', function(e) {
                    e.preventDefault();
                    if ($(this).hasClass('disabled') || $(this).hasClass('active')) return;
                    
                    if ($(this).hasClass('previous')) {
                        table.page('previous').draw('page');
                    } else if ($(this).hasClass('next')) {
                        table.page('next').draw('page');
                    } else {
                        const pageNum = parseInt($(this).text()) - 1;
                        table.page(pageNum).draw('page');
                    }
                });
            }
        }

        // Trigger footer update on drawing table
        table.on('draw', function() {
            updateFooterControls();
        });

        // Initial draw & footer update
        table.draw();

        // ----------------------------------------------------
        // CLIENT SIDE INSTANT AUTO FILTERING (NO PAGE RELOADS)
        // ----------------------------------------------------
        const dateColIndex = {{ $isAdminOrManager ? 1 : 0 }};

        // Custom search function for Date Range Filtering
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'workReportsTable') return true;

                const fromVal = $('input[name="from_date"]').val(); // YYYY-MM-DD
                const toVal = $('input[name="to_date"]').val(); // YYYY-MM-DD
                if (!fromVal && !toVal) return true;

                // Try to get YYYY-MM-DD from the cell's data-order attribute
                const cellNode = settings.aoData[dataIndex].anCells ? settings.aoData[dataIndex].anCells[dateColIndex] : null;
                let dateVal = cellNode ? cellNode.getAttribute('data-order') : null;

                // Fallback to manual text parsing if data-order attribute isn't set or found
                if (!dateVal) {
                    const dateStr = data[dateColIndex] ? data[dateColIndex].trim() : '';
                    if (dateStr && dateStr !== '-') {
                        const parts = dateStr.replace(/,/g, '').replace(/\s+/g, ' ').split(' ');
                        if (parts.length === 3) {
                            const day = parseInt(parts[0], 10);
                            const months = {
                                jan:0, feb:1, mar:2, apr:3, may:4, jun:5, jul:6, aug:7, sep:8, oct:9, nov:10, dec:11
                            };
                            const mStr = parts[1].toLowerCase().substring(0, 3);
                            const month = months[mStr];
                            const year = parseInt(parts[2], 10);
                            if (!isNaN(day) && month !== undefined && !isNaN(year)) {
                                const dd = String(day).padStart(2, '0');
                                const mm = String(month + 1).padStart(2, '0');
                                dateVal = `${year}-${mm}-${dd}`;
                            }
                        }
                    }
                }

                if (!dateVal) return false;

                if (fromVal && dateVal < fromVal) return false;
                if (toVal && dateVal > toVal) return false;

                return true;
            }
        );

        // Employee dropdown filter change
        @if($isAdminOrManager)
        $('select[name="employee_id"]').on('change', function() {
            const selectedText = $(this).find('option:selected').text().trim();
            const val = $(this).val();
            if (!val) {
                table.column(0).search('').draw();
            } else {
                table.column(0).search(selectedText).draw();
            }
        });
        @endif

        // Date input change triggers draw (which calls custom search function above)
        $('input[name="from_date"], input[name="to_date"]').on('change', function() {
            table.draw();
        });

        // Debounced search typing keyword
        let typingTimer;
        $('input[name="search"]').on('keyup', function() {
            clearTimeout(typingTimer);
            const val = $(this).val();
            typingTimer = setTimeout(function() {
                table.search(val).draw();
            }, 300);
        });

    });
</script>
@endsection
