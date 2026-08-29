@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'attendances'])

@section('page_title', "Today's Attendance")

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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

    body {
        background: var(--orb-bg) !important;
        font-family: 'Outfit', sans-serif !important;
        overflow-x: hidden !important;
    }

    .att-page {
        min-height: calc(100vh - 90px);
        padding: 24px;
        background: var(--orb-bg);
        font-family: 'Outfit', sans-serif;
    }

    .att-container {
        max-width: 1500px;
        margin: 0 auto;
    }

    /* Dynamic DB Theme Premium Hero Header */
    .att-header-premium {
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

    .att-header-premium::before {
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

    .att-header-premium .title-area h3 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .att-header-premium .title-area p {
        font-size: 14px !important;
        color: rgba(255, 255, 255, 0.88) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .att-header-premium .header-kicker {
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

    .orb-card-theme {
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
        padding: 24px;
        margin-bottom: 24px;
    }

    .stat-metric-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
        text-align: center;
        height: 100%;
        transition: transform .2s ease;
    }

    .stat-metric-card:hover {
        transform: translateY(-2px);
    }

    .metric-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--orb-text);
        margin: 6px 0;
        font-feature-settings: "tnum";
    }

    .metric-label {
        font-size: 12px;
        font-weight: 800;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pulse-live {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 rgba(34, 197, 94, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
</style>

<div class="att-page">
    <div class="att-container">



        {{-- Dynamic DB Theme Hero Header --}}
        <div class="att-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-fingerprint"></i> Live Attendance Engine
                </div>
                <h3>Today's Attendance</h3>
                <p>{{ \Carbon\Carbon::now()->format('l, d F Y') }} &bull; Live Mobile & Web Synchronization</p>
            </div>
            <div class="text-right">
                <div class="badge badge-light px-3 py-2 font-weight-bold" style="font-size: 14px; border-radius: 50px; color: var(--orb-primary);">
                    <i class="fas fa-clock mr-1 text-warning"></i> <span id="liveCurrentTime">--:--:-- --</span>
                </div>
            </div>
        </div>

        {{-- Flash Messages & Validation Alerts --}}
        @if (session('error') || session('danger'))
            <div class="alert alert-danger border-0 shadow-lg mb-4" style="border-radius: 18px; background: #fef2f2; border-left: 6px solid #ef4444 !important; padding: 18px 22px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle text-danger fa-2x mr-3"></i>
                    <div>
                        <h6 style="color: #991b1b; font-weight: 800; margin: 0 0 2px 0;">Punch-In / Attendance Alert</h6>
                        <p class="mb-0 text-dark font-weight-bold" style="font-size: 14px;">{{ session('error') ?? session('danger') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-lg mb-4" style="border-radius: 18px; background: #f0fdf4; border-left: 6px solid #22c55e !important; padding: 18px 22px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle text-success fa-2x mr-3"></i>
                    <div>
                        <h6 style="color: #166534; font-weight: 800; margin: 0 0 2px 0;">Success</h6>
                        <p class="mb-0 text-dark font-weight-bold" style="font-size: 14px;">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-lg mb-4" style="border-radius: 18px; background: #fef2f2; border-left: 6px solid #ef4444 !important; padding: 18px 22px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-danger fa-2x mr-3"></i>
                    <div>
                        <h6 style="color: #991b1b; font-weight: 800; margin: 0 0 2px 0;">Punch-In Form Errors</h6>
                        <ul class="mb-0 text-dark font-weight-bold pl-3" style="font-size: 14px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @php
            $todayData = $attendancePayload['attendance'] ?? [];
            $policyData = $attendancePayload['policy'] ?? [];
            $dayCtx = $attendancePayload['day_context'] ?? [];
            $hasPunchedIn = !empty($attendanceRecord->punch_in_time);
            $hasPunchedOut = !empty($attendanceRecord->punch_out_time);
            $statusCode = strtolower((string) ($attendanceRecord->attendance_status ?? ($attendancePayload['status_code'] ?? 'not_punched')));

            if ($statusCode === 'lwp') {
                $statusName = 'Absent';
            } else {
                $statusName = ucwords(str_replace('_', ' ', $statusCode));
            }

            $workMode = strtoupper($attendanceRecord->work_mode ?? ($attendancePayload['today_work_mode'] ?? 'WFO'));
            $src = strtolower((string) ($attendanceRecord->attendance_source ?? 'web'));
            $isPunchBlocked = ($attendanceRecord?->is_blocked || $attendanceRecord?->is_punch_blocked || $statusCode === 'punch_blocked') && !$attendanceRecord?->is_admin_unlocked;
        @endphp

        {{-- Access Control Notice if Web Attendance Disabled --}}
        @if (!$canWebPunch)
            <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 16px; background: #fff8e6; border-left: 6px solid #f59e0b !important; padding: 20px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle text-warning fa-3x mr-3"></i>
                    <div>
                        <h5 style="color: #854d0e; font-weight: 800; margin: 0 0 4px 0;">Web Attendance Disabled</h5>
                        <p class="mb-0 text-muted font-weight-bold">Web Attendance is disabled for your account. Please contact HR Administrator.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Punch Blocked Warning Banner --}}
        @if ($isPunchBlocked)
            <div class="alert alert-danger border-0 shadow-lg mb-4" style="border-radius: 20px; background: #fef2f2; border-left: 6px solid #ef4444 !important; padding: 22px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-lock text-danger fa-3x mr-3"></i>
                    <div>
                        <h5 style="color: #991b1b; font-weight: 800; margin: 0 0 4px 0;">Punch In Blocked for Today</h5>
                        <p class="mb-0 text-dark font-weight-bold">Your attendance punch-in is currently blocked by HR system for today. Please contact HR Administrator to request an unlock.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Metrics Row --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="stat-metric-card">
                    <div class="metric-label">Current Status</div>
                    <div class="metric-value" style="font-size: 20px;">
                        @if ($isPunchBlocked)
                            <span class="badge badge-danger px-3 py-2"><i class="fas fa-lock mr-1"></i> Punch Blocked</span>
                        @elseif ($statusCode === 'lwp')
                            <span class="badge badge-danger px-3 py-2"><i class="fas fa-times-circle mr-1"></i> Absent</span>
                        @elseif ($hasPunchedOut)
                            <span class="badge badge-success px-3 py-2">Attendance Completed</span>
                        @elseif ($hasPunchedIn)
                            <span class="badge badge-primary px-3 py-2"><span class="pulse-live mr-1"></span> Punched In</span>
                        @else
                            <span class="badge badge-secondary px-3 py-2">{{ $statusName }}</span>
                        @endif
                    </div>
                    <div class="small text-muted font-weight-bold">Work Mode: {{ $workMode }}</div>
                </div>
            </div>

            <div class="col-md-3 mb-3 mb-md-0">
                <div class="stat-metric-card">
                    <div class="metric-label">Shift Details</div>
                    <div class="metric-value" style="font-size: 18px;">
                        {{ $policyData['shift_name'] ?? 'General Shift' }}
                    </div>
                    <div class="small text-muted font-weight-bold">
                        {{ $policyData['shift_start_formatted'] ?? '09:30 AM' }} - {{ $policyData['shift_end_formatted'] ?? '06:30 PM' }}
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3 mb-md-0">
                <div class="stat-metric-card">
                    <div class="metric-label">Working Hours</div>
                    <div class="metric-value text-success" id="liveWorkingTimer">
                        @if ($hasPunchedIn && !$hasPunchedOut)
                            00:00:00
                        @elseif ($hasPunchedOut)
                            @php
                                $wMins = (int) ($attendanceRecord->gross_work_minutes ?? 0);
                                if ($wMins <= 0 && !empty($attendanceRecord->punch_in_time) && !empty($attendanceRecord->punch_out_time)) {
                                    $wMins = \Carbon\Carbon::parse($attendanceRecord->punch_in_time)->diffInMinutes(\Carbon\Carbon::parse($attendanceRecord->punch_out_time));
                                }
                                $wh = floor($wMins / 60);
                                $wm = $wMins % 60;
                            @endphp
                            {{ sprintf('%02dh %02dm', $wh, $wm) }}
                        @else
                            00:00:00
                        @endif
                    </div>
                    <div class="small text-muted font-weight-bold">Punched In: {{ optional($attendanceRecord)->punch_in_time ? \Carbon\Carbon::parse($attendanceRecord->punch_in_time)->format('h:i A') : '--:--' }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-metric-card">
                    <div class="metric-label">Remaining Work Timer</div>
                    <div class="metric-value text-danger" id="liveRemainingTimer">
                        @if ($hasPunchedIn && !$hasPunchedOut)
                            --:--:--
                        @else
                            00:00:00
                        @endif
                    </div>
                    <div class="small text-muted font-weight-bold">Target Out: {{ optional($attendanceRecord)->target_punch_out_time ? \Carbon\Carbon::parse($attendanceRecord->target_punch_out_time)->format('h:i A') : '--:--' }}</div>
                </div>
            </div>
        </div>

        {{-- Action Panel Card --}}
        @if ($canWebPunch)
            <div class="orb-card-theme text-center py-4">
                @if ($isPunchBlocked)
                    <h4 class="font-weight-bold text-danger mb-2"><i class="fas fa-lock mr-2"></i> Attendance Punch In Blocked</h4>
                    <p class="text-muted mb-4">Your punch-in is blocked for today. Please contact HR / Administrator to unlock your attendance.</p>
                    <button type="button" class="btn font-weight-bold px-5 py-3 shadow" data-toggle="modal" data-target="#webPunchInModal" style="border-radius: 30px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important; border: none; color: #fff;">
                        <i class="fas fa-ban fa-lg mr-2"></i> PUNCH IN BLOCKED (VIEW DETAILS)
                    </button>
                @elseif (!$hasPunchedIn)
                    <h4 class="font-weight-bold text-dark mb-2">Ready to Start Your Shift?</h4>
                    <p class="text-muted mb-4">Click below to mark your Punch In. GPS & Browser metadata will be logged securely.</p>
                    <button type="button" class="btn btn-lg px-5 py-3 font-weight-bold shadow" data-toggle="modal" data-target="#webPunchInModal" style="border-radius: 30px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important; color: #fff !important; border: none;">
                        <i class="fas fa-fingerprint fa-lg mr-2"></i> PUNCH IN NOW
                    </button>
                @elseif (!$hasPunchedOut)
                    <h4 class="font-weight-bold text-dark mb-2">Punched In & Active Shift</h4>
                    <p class="text-muted mb-4">Submit your Daily Work Report (Summary, Completed & Pending Tasks) to Punch Out.</p>
                    <button type="button" class="btn btn-danger btn-lg px-5 py-3 font-weight-bold shadow" data-toggle="modal" data-target="#webPunchOutModal" style="border-radius: 30px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none;">
                        <i class="fas fa-sign-out-alt fa-lg mr-2"></i> PUNCH OUT
                    </button>
                @else
                    <div class="py-2">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h4 class="font-weight-bold text-dark mb-1">Attendance Completed Today</h4>
                        <p class="text-muted mb-0">Your Punch Out has been recorded successfully. Have a great day!</p>
                    </div>
                @endif
            </div>

            {{-- Floating Bottom-Right Fixed Web Punch Button Overlay --}}
            <div style="position: fixed; bottom: 32px; right: 32px; z-index: 9999;">
                @if ($isPunchBlocked)
                    <button type="button" class="btn font-weight-bold px-4 py-3 shadow-lg d-flex align-items-center" data-toggle="modal" data-target="#webPunchInModal" style="border-radius: 50px; font-size: 15px; font-weight: 900; background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important; color: #fff; border: 2px solid #ffffff; cursor: pointer;">
                        <i class="fas fa-ban fa-lg mr-2"></i> PUNCH BLOCKED
                    </button>
                @elseif (!$hasPunchedIn)
                    <button type="button" class="btn font-weight-bold px-4 py-3 shadow-lg d-flex align-items-center" data-toggle="modal" data-target="#webPunchInModal" style="border-radius: 50px; font-size: 15px; font-weight: 900; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important; color: #fff !important; border: 2px solid #ffffff; box-shadow: 0 12px 30px rgba(75, 0, 232, 0.4) !important;">
                        <i class="fas fa-fingerprint fa-lg mr-2"></i> PUNCH IN
                    </button>
                @elseif (!$hasPunchedOut)
                    <button type="button" class="btn btn-danger font-weight-bold px-4 py-3 shadow-lg d-flex align-items-center" data-toggle="modal" data-target="#webPunchOutModal" style="border-radius: 50px; font-size: 15px; font-weight: 900; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: 2px solid #ffffff; box-shadow: 0 12px 30px rgba(239, 68, 68, 0.45) !important;">
                        <i class="fas fa-sign-out-alt fa-lg mr-2"></i> PUNCH OUT
                    </button>
                @endif
            </div>
        @endif

        {{-- Today's Summary & Audit Card --}}
        <div class="orb-card-theme">
            <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-clipboard-list text-primary mr-2"></i> Today's Summary & Audit Log</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="p-3 bg-light rounded-lg border">
                        <div class="small text-muted font-weight-bold mb-1">PUNCH IN INFO</div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Time:</span> <strong>{{ optional($attendanceRecord)->punch_in_time ? \Carbon\Carbon::parse($attendanceRecord->punch_in_time)->format('h:i A') : 'Not Punched' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Source:</span> 
                            <span class="badge {{ $src === 'web' ? 'badge-primary' : ($src === 'mobile' ? 'badge-success' : 'badge-dark') }}">
                                {{ strtoupper($src) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Work Mode:</span> 
                            <strong class="text-primary">{{ $workMode }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span>Status:</span> 
                            @php
                                $badgeClass = match($statusCode) {
                                    'lwp', 'absent' => 'badge-danger',
                                    'half_day' => 'badge-warning',
                                    'present' => 'badge-success',
                                    'punch_blocked' => 'badge-danger',
                                    default => ($hasPunchedOut ? 'badge-success' : ($hasPunchedIn ? 'badge-primary' : 'badge-secondary'))
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">{{ $statusName }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="p-3 bg-light rounded-lg border">
                        <div class="small text-muted font-weight-bold mb-1">PUNCH OUT INFO</div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Time:</span> <strong>{{ optional($attendanceRecord)->punch_out_time ? \Carbon\Carbon::parse($attendanceRecord->punch_out_time)->format('h:i A') : 'Not Punched Out' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Gross Work:</span> 
                            <strong>
                                @if (!empty($attendanceRecord->punch_in_time) && !empty($attendanceRecord->punch_out_time))
                                    @php
                                        $gMins = (int) ($attendanceRecord->gross_work_minutes ?? 0);
                                        if ($gMins <= 0) {
                                            $inT = \Carbon\Carbon::parse($attendanceRecord->punch_in_time);
                                            $outT = \Carbon\Carbon::parse($attendanceRecord->punch_out_time);
                                            $gMins = $inT->diffInMinutes($outT);
                                        }
                                        $gh = floor($gMins / 60);
                                        $gm = $gMins % 60;
                                    @endphp
                                    {{ $gh }} hours {{ $gm }} mins
                                @else
                                    -
                                @endif
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Net Work:</span> 
                            <strong>
                                @if (!empty($attendanceRecord->punch_in_time) && !empty($attendanceRecord->punch_out_time))
                                    @php
                                        $nMins = (int) ($attendanceRecord->total_work_minutes ?? 0);
                                        if ($nMins <= 0) { $nMins = $gMins; }
                                        $nh = floor($nMins / 60);
                                        $nm = $nMins % 60;
                                    @endphp
                                    {{ $nh }} hours {{ $nm }} mins
                                @else
                                    -
                                @endif
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span>Flags:</span> 
                            <div>
                                @if(optional($attendanceRecord)->is_late) <span class="badge badge-warning">Late</span> @endif
                                @if(optional($attendanceRecord)->is_early_out) <span class="badge badge-warning">Early Out</span> @endif
                                @if(!$attendanceRecord || (!optional($attendanceRecord)->is_late && !optional($attendanceRecord)->is_early_out)) <span class="badge badge-light text-muted">On Time</span> @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($workSummaryLog)
                @php
                    $rawJson = $workSummaryLog->work_summary_json;
                    if (is_string($rawJson)) {
                        $json = json_decode($rawJson, true) ?? [];
                    } elseif (is_array($rawJson)) {
                        $json = $rawJson;
                    } else {
                        $json = [];
                    }

                    $taskName = $json['task_name'] ?? ($json['title'] ?? null);
                    $rawSummary = $workSummaryLog->work_summary ?? null;
                    $workDesc = $json['today_work_description'] ?? null;
                    $repStatus = $json['today_work_status'] ?? ($json['current_status'] ?? ($json['status'] ?? null));
                    
                    // Structured projects & tasks extraction
                    $structuredProjects = [];
                    if (isset($json['projects']) && is_array($json['projects'])) {
                        foreach ($json['projects'] as $p) {
                            $pName = $p['project_name'] ?? $p['name'] ?? 'Project';
                            $pTasks = [];
                            if (isset($p['tasks']) && is_array($p['tasks'])) {
                                foreach ($p['tasks'] as $t) {
                                    $tName = $t['task_name'] ?? $t['description'] ?? $t['task'] ?? $t['title'] ?? 'Task';
                                    $tDone = (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true || $t['is_completed'] === 'true') : (isset($t['completed']) ? ($t['completed'] == 1 || $t['completed'] === true || $t['completed'] === 'true') : true));
                                    $pTasks[] = ['text' => $tName, 'done' => $tDone];
                                }
                            }
                            $structuredProjects[] = ['name' => $pName, 'tasks' => $pTasks];
                        }
                    }

                    // Fallback parser if structured projects array is empty but rawSummary contains text
                    if (empty($structuredProjects) && !empty($rawSummary)) {
                        $lines = explode("\n", $rawSummary);
                        $currPName = null;
                        $currPTasks = [];
                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if (!$trimmed) continue;
                            if (str_starts_with($trimmed, 'Project:')) {
                                if ($currPName !== null && !empty($currPTasks)) {
                                    $structuredProjects[] = ['name' => $currPName, 'tasks' => $currPTasks];
                                    $currPTasks = [];
                                }
                                $currPName = trim(substr($trimmed, 8));
                            } elseif (str_starts_with($trimmed, '☑') || str_starts_with($trimmed, '✓') || str_starts_with($trimmed, '[x]')) {
                                $tText = trim(mb_substr($trimmed, 1));
                                if (str_starts_with($tText, '[x]')) $tText = trim(substr($tText, 3));
                                $currPTasks[] = ['text' => $tText, 'done' => true];
                            } elseif (str_starts_with($trimmed, '☐') || str_starts_with($trimmed, '○') || str_starts_with($trimmed, '[ ]')) {
                                $tText = trim(mb_substr($trimmed, 1));
                                if (str_starts_with($tText, '[ ]')) $tText = trim(substr($tText, 3));
                                $currPTasks[] = ['text' => $tText, 'done' => false];
                            } elseif (str_starts_with($trimmed, "Today's Work Status:")) {
                                if (!$repStatus) {
                                    $repStatus = trim(substr($trimmed, 19));
                                }
                            }
                        }
                        if ($currPName !== null && !empty($currPTasks)) {
                            $structuredProjects[] = ['name' => $currPName, 'tasks' => $currPTasks];
                        }
                    }

                    $rawIssues = $json['issues_blockers'] ?? ($json['issues'] ?? null);
                    $issuesText = null;
                    if (is_array($rawIssues)) {
                        $issuesText = implode(', ', array_filter($rawIssues));
                    } elseif (is_string($rawIssues) && trim($rawIssues) !== '' && strtolower(trim($rawIssues)) !== 'no issues' && strtolower(trim($rawIssues)) !== 'none') {
                        $issuesText = trim($rawIssues);
                    }

                    $stLower = strtolower(trim((string)$repStatus));
                    $statusBadgeStyle = match(true) {
                        in_array($stLower, ['completed', 'done', 'success']) => 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;',
                        in_array($stLower, ['testing', 'tested']) => 'background: #E0F2FE; color: #0369A1; border: 1px solid #7DD3FC;',
                        in_array($stLower, ['in-progress', 'in_progress', 'progress', 'pending']) => 'background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;',
                        in_array($stLower, ['blocked', 'failed']) => 'background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5;',
                        default => 'background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;'
                    };
                    $statusLabel = $repStatus ? ucwords(str_replace('_', ' ', $repStatus)) : 'Submitted';
                @endphp

                <div class="mt-4 p-4 bg-white border shadow-sm" style="border-radius: 20px;">
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #EEF2FF; border-radius: 14px; color: #4F46E5; font-size: 20px;">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div>
                                <h5 class="font-weight-bold text-dark mb-0" style="font-size: 17px;">Daily Work Report Submission</h5>
                                <span class="text-muted small"><i class="fas fa-clock mr-1"></i> Submitted at Punch Out &bull; {{ $workSummaryLog->created_at ? $workSummaryLog->created_at->format('h:i A') : 'Recorded' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge px-3 py-2 font-weight-bold text-uppercase" style="border-radius: 20px; font-size: 11px; letter-spacing: 0.04em; {{ $statusBadgeStyle }}">
                                Status: {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    @if(!empty($structuredProjects))
                        <div class="row">
                            @foreach($structuredProjects as $proj)
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 bg-light rounded-lg border h-100" style="border-radius: 14px; border: 1px solid #E2E8F0;">
                                        <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                            <i class="fas fa-folder text-primary mr-2" style="font-size: 15px;"></i>
                                            <strong class="text-dark font-weight-bold" style="font-size: 14px;">{{ $proj['name'] }}</strong>
                                            <span class="badge badge-primary badge-pill ml-auto px-2 py-0.5" style="font-size: 10px;">{{ count($proj['tasks']) }} Tasks</span>
                                        </div>
                                        <ul class="list-unstyled mb-0">
                                            @foreach($proj['tasks'] as $t)
                                                <li class="py-1.5 d-flex align-items-start" style="font-size: 13px;">
                                                    @if($t['done'])
                                                        <i class="fas fa-check-circle text-success mr-2 mt-1" style="font-size: 14px;"></i>
                                                        <span class="text-dark font-weight-medium">{{ $t['text'] }}</span>
                                                    @else
                                                        <i class="far fa-circle text-warning mr-2 mt-1" style="font-size: 14px;"></i>
                                                        <span class="text-muted">{{ $t['text'] }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($workDesc)
                        <div class="mb-3">
                            <div class="small text-muted font-weight-bold text-uppercase mb-1"><i class="fas fa-align-left text-primary mr-1"></i> Today Work Summary</div>
                            <div class="p-3 bg-light rounded-lg text-dark border" style="border-radius: 12px; font-size: 13.5px; white-space: pre-line; line-height: 1.6;">
                                {{ $workDesc }}
                            </div>
                        </div>
                    @elseif($rawSummary)
                        <div class="mb-3">
                            <div class="small text-muted font-weight-bold text-uppercase mb-1"><i class="fas fa-align-left text-primary mr-1"></i> Today Work Summary</div>
                            <div class="p-3 bg-light rounded-lg text-dark border" style="border-radius: 12px; font-size: 13.5px; white-space: pre-line; line-height: 1.6;">
                                {{ $rawSummary }}
                            </div>
                        </div>
                    @endif

                    @if($issuesText)
                        <div class="mt-2 p-3 rounded-lg border" style="border-radius: 12px; background: #FEF2F2; border-color: #FCA5A5 !important; color: #991B1B;">
                            <strong class="d-block mb-1" style="font-size: 13px;"><i class="fas fa-bug text-danger mr-1.5"></i> Issues & Blockers:</strong>
                            <span style="font-size: 13px;">{{ $issuesText }}</span>
                        </div>
                    @endif
                </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Modals for Web Punch In & Punch Out --}}
@if ($canWebPunch)
    @include('dashboard.partials.employee-dashboard', ['only_modals' => true])
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Real-time clock update
    function updateClock() {
        const now = new Date();
        document.getElementById('liveCurrentTime').textContent = now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Live Timers calculation
    const punchInTimeStr = "{{ $attendanceRecord->punch_in_time ?? '' }}";
    const targetOutTimeStr = "{{ $attendanceRecord->target_punch_out_time ?? '' }}";
    const todayDateStr = "{{ \Carbon\Carbon::now()->toDateString() }}";
    const isPunchedIn = {{ $hasPunchedIn && !$hasPunchedOut ? 'true' : 'false' }};

    if (isPunchedIn && punchInTimeStr) {
        let punchInDate = null;
        if (punchInTimeStr.includes('-') && punchInTimeStr.includes(' ')) {
            punchInDate = new Date(punchInTimeStr.replace(' ', 'T'));
        } else if (punchInTimeStr.includes('T')) {
            punchInDate = new Date(punchInTimeStr);
        } else {
            punchInDate = new Date(todayDateStr + 'T' + punchInTimeStr);
        }

        let targetOutDate = null;
        if (targetOutTimeStr) {
            if (targetOutTimeStr.includes('-') && targetOutTimeStr.includes(' ')) {
                targetOutDate = new Date(targetOutTimeStr.replace(' ', 'T'));
            } else if (targetOutTimeStr.includes('T')) {
                targetOutDate = new Date(targetOutTimeStr);
            } else {
                targetOutDate = new Date(todayDateStr + 'T' + targetOutTimeStr);
            }
        }

        function updateTimers() {
            if (!punchInDate || isNaN(punchInDate.getTime())) return;
            const now = new Date();
            let elapsedSec = Math.floor((now.getTime() - punchInDate.getTime()) / 1000);
            if (elapsedSec < 0) elapsedSec = 0;

            const hours = String(Math.floor(elapsedSec / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((elapsedSec % 3600) / 60)).padStart(2, '0');
            const seconds = String(elapsedSec % 60).padStart(2, '0');
            const liveWorkingEl = document.getElementById('liveWorkingTimer');
            if (liveWorkingEl) {
                liveWorkingEl.textContent = `${hours}:${minutes}:${seconds}`;
            }

            if (targetOutDate && !isNaN(targetOutDate.getTime())) {
                let remainSec = Math.floor((targetOutDate.getTime() - now.getTime()) / 1000);
                if (remainSec < 0) remainSec = 0;

                const rHours = String(Math.floor(remainSec / 3600)).padStart(2, '0');
                const rMinutes = String(Math.floor((remainSec % 3600) / 60)).padStart(2, '0');
                const rSeconds = String(remainSec % 60).padStart(2, '0');
                const liveRemainingEl = document.getElementById('liveRemainingTimer');
                if (liveRemainingEl) {
                    liveRemainingEl.textContent = `${rHours}:${rMinutes}:${rSeconds}`;
                }
            }
        }

        setInterval(updateTimers, 1000);
        updateTimers();
    }
});
</script>
@endsection
