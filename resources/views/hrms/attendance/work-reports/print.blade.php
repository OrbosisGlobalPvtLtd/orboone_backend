<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $empName = optional($employee->user)->name ?? ($employee->name ?? 'Employee');
        $empCode = $employee->employee_code ?? 'N/A';
        $empDept = optional($employee->department)->name ?? 'Staff';
        $empDesig = optional($employee->designation)->name ?? 'Member';
        $workDateStr = $workLog->work_date ? $workLog->work_date->format('d M Y') : date('d M Y');
        $attendance = $workLog->attendance;
        $workMode = strtoupper(optional($attendance)->work_mode ?? 'WFO');
        $shiftName = optional(optional($attendance)->attendanceTime)->name ?? 'General Shift';
        $grossWork = optional($attendance)->gross_duration ?? ($workLog->duration_minutes ? floor($workLog->duration_minutes / 60) . 'h ' . ($workLog->duration_minutes % 60) . 'm' : '-');
        $punchIn = optional($attendance)->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-';
        $punchOut = optional($attendance)->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-';
        $photoUrl = resolveEmployeePassportPhoto($employee);
        $initials = resolveEmployeeInitials($employee);

        // JSON parsing
        $tasks = $workLog->work_summary_json;
        if (is_string($tasks)) {
            $tasks = json_decode($tasks, true);
        }

        $projectsList = [];
        $taskList = [];
        $issues = [];
        $notes = null;
        $summaryDesc = $workLog->work_summary;

        if (is_array($tasks)) {
            if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                $projectsList = $tasks['projects'];
            } elseif (isset($tasks['requirements']) && is_array($tasks['requirements'])) {
                $taskList = $tasks['requirements'];
            } elseif (isset($tasks['tasks']) && is_array($tasks['tasks'])) {
                $taskList = $tasks['tasks'];
            }

            if (!empty($tasks['description'])) {
                $summaryDesc = $tasks['description'];
            } elseif (!empty($tasks['today_work_description'])) {
                $summaryDesc = $tasks['today_work_description'];
            }

            if (!empty($tasks['issues'])) {
                $issues = is_array($tasks['issues']) ? $tasks['issues'] : [$tasks['issues']];
            }
            if (!empty($tasks['notes'])) {
                $notes = $tasks['notes'];
            }
        }

        if ($summaryDesc && (str_contains($summaryDesc, '☑') || str_contains($summaryDesc, '☐'))) {
            $lines = explode("\n", $summaryDesc);
            $cleanLines = array_filter($lines, fn($l) => !str_contains($l, '☑') && !str_contains($l, '☐') && !str_starts_with(trim($l), 'Project:'));
            $summaryDesc = trim(implode(' ', $cleanLines));
        }
        if (!$summaryDesc) {
            $summaryDesc = 'Work report submitted with project deliverables.';
        }

        $realIssues = array_filter($issues, function($item) {
            if (!is_string($item)) return true;
            $v = strtolower(trim($item));
            return $v !== '' && $v !== 'no issues' && $v !== 'none';
        });
    @endphp
    <title>Daily Work Report Slip - {{ $empName }} ({{ $workDateStr }})</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --brand-primary: {{ branding_primary_color() }};
            --brand-secondary: {{ branding_secondary_color() }};
            --brand-dark: #0F172A;
            --brand-slate: #334155;
            --brand-muted: #64748B;
            --brand-border: #E2E8F0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--brand-dark);
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
            font-size: 11.5px;
            line-height: 1.45;
        }

        .screen-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: #ffffff;
        }

        .btn-tb {
            height: 36px;
            padding: 0 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-tb-primary {
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: #ffffff;
        }

        .btn-tb-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .doc-wrapper {
            max-width: 860px;
            margin: 20px auto 40px;
            background: #ffffff;
            padding: 28px 32px;
            border-radius: 14px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
            border: 1px solid var(--brand-border);
        }

        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--brand-border);
            position: relative;
        }

        .doc-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 120px;
            height: 2px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo-img {
            max-height: 44px;
            max-width: 140px;
            object-fit: contain;
        }

        .brand-fallback-logo {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: #ffffff;
            font-size: 18px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 900;
            color: var(--brand-dark);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 10px;
            font-weight: 700;
            color: var(--brand-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        .doc-meta-box {
            text-align: right;
            font-size: 10px;
            color: var(--brand-muted);
            font-weight: 600;
        }

        .doc-slip-title {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 900;
            color: var(--brand-primary);
            margin: 0;
        }

        /* DOSSIER SUMMARY CARD */
        .emp-dossier {
            background: #F8FAFC;
            border: 1px solid var(--brand-border);
            border-radius: 10px;
            padding: 14px 18px;
            margin: 16px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .emp-profile-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .emp-avatar {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #ffffff;
            border: 1.5px solid #CBD5E1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 900;
            color: var(--brand-primary);
            overflow: hidden;
            flex-shrink: 0;
        }

        .emp-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .timing-pill-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        .timing-box {
            background: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 8px;
            padding: 8px 12px;
            text-align: center;
        }

        .timing-val {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 800;
            color: var(--brand-dark);
        }

        .timing-lbl {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-muted);
            letter-spacing: 0.04em;
        }

        /* PROJECT / TASK SECTION */
        .project-block {
            border: 1px solid var(--brand-border);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
            background: #ffffff;
        }

        .project-title {
            font-size: 11.5px;
            font-weight: 800;
            color: var(--brand-dark);
            padding-bottom: 4px;
            margin-bottom: 6px;
            border-bottom: 1px dashed var(--brand-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .task-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 8px;
            border-radius: 6px;
            background: #F8FAFC;
            border: 1px solid #F1F5F9;
            margin-bottom: 4px;
            font-size: 10.5px;
        }

        .task-row.done {
            background: #F0FDF4;
            border-color: #DCFCE7;
            color: #166534;
        }

        .task-badge {
            font-size: 8px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .task-badge.badge-done {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #BBF7D0;
        }

        .task-badge.badge-pending {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
        }

        .signoff-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
            padding-top: 14px;
            border-top: 1px solid var(--brand-border);
        }

        .signoff-box {
            border: 1px dashed var(--brand-border);
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            background: #F8FAFC;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media print {
            body {
                background: #ffffff !important;
                font-size: 10.5px !important;
            }
            .no-print {
                display: none !important;
            }
            .doc-wrapper {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Screen Toolbar -->
    <div class="screen-toolbar no-print">
        <div style="font-size: 13px; font-weight: 700;">
            <i class="fas fa-file-invoice text-primary mr-1"></i> Daily Work Report Slip &bull; {{ $empName }} &bull; {{ $workDateStr }}
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-tb btn-tb-primary">
                <i class="fas fa-print"></i> Print / Save PDF
            </button>
            <button onclick="window.close()" class="btn-tb btn-tb-secondary">
                <i class="fas fa-times"></i> Close Window
            </button>
        </div>
    </div>

    <div class="doc-wrapper">
        <!-- Header -->
        <div class="doc-header">
            <div class="brand-block">
                @php
                    $logoUrl = branding_logo();
                @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ company_name() }}" class="brand-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="brand-fallback-logo" style="display:none;">
                        {{ strtoupper(substr(company_name(), 0, 1)) }}
                    </div>
                @else
                    <div class="brand-fallback-logo">
                        {{ strtoupper(substr(company_name(), 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="brand-title">{{ company_name() }}</div>
                    <div class="brand-subtitle">Official Daily Work Report Slip</div>
                </div>
            </div>

            <div class="doc-meta-box">
                <h1 class="doc-slip-title">DAILY WORK REPORT</h1>
                <div><strong>Ref:</strong> WR-{{ $empCode }}-{{ $workLog->work_date ? $workLog->work_date->format('Ymd') : date('Ymd') }}</div>
                <div><strong>Date:</strong> {{ $workDateStr }} ({{ $workLog->work_date ? $workLog->work_date->format('l') : '' }})</div>
                <div><strong>Submitted:</strong> {{ $workLog->created_at ? $workLog->created_at->format('d M Y, h:i A') : '-' }}</div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="emp-dossier">
            <div class="emp-profile-left">
                <div class="emp-avatar">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $empName }}" onerror="this.style.display='none'; this.parentElement.innerText='{{ $initials }}';">
                    @else
                        <span>{{ $initials }}</span>
                    @endif
                </div>
                <div>
                    <div style="font-family:'Outfit', sans-serif; font-size: 15px; font-weight: 800; color: var(--brand-dark);">
                        {{ $empName }}
                    </div>
                    <div style="font-size: 10.5px; font-weight: 600; color: var(--brand-muted); margin-top: 2px;">
                        <span style="background: #EEF2FF; color: #4338CA; padding: 1px 6px; border-radius: 4px; font-weight: 700;">{{ $empCode }}</span> &bull; {{ $empDept }} &bull; {{ $empDesig }}
                    </div>
                </div>
            </div>
            <div style="text-align: right; font-size: 10.5px;">
                <div><strong>Reporting To:</strong> {{ $reportingManagerName }}</div>
                <div style="margin-top: 2px;"><strong>Mode:</strong> <span style="font-weight: 800; color: {{ $workMode === 'WFH' ? '#1D4ED8' : '#15803D' }};">{{ $workMode }}</span> &bull; <strong>Shift:</strong> {{ $shiftName }}</div>
            </div>
        </div>

        <!-- Timings Strip -->
        <div class="timing-pill-grid">
            <div class="timing-box">
                <div class="timing-val">{{ $punchIn }}</div>
                <div class="timing-lbl">Punch In Time</div>
            </div>
            <div class="timing-box">
                <div class="timing-val">{{ $punchOut }}</div>
                <div class="timing-lbl">Punch Out Time</div>
            </div>
            <div class="timing-box" style="border-color: #CBD5E1; background: #F8FAFC;">
                <div class="timing-val" style="color: var(--brand-primary);">{{ $grossWork }}</div>
                <div class="timing-lbl">Gross Work Duration</div>
            </div>
        </div>

        <!-- Summary Description -->
        <div style="background: #F8FAFC; border-left: 3px solid var(--brand-primary); padding: 8px 12px; border-radius: 0 6px 6px 0; margin-bottom: 14px; font-size: 11px; color: var(--brand-slate); font-weight: 600;">
            <strong>Work Summary:</strong> {{ $summaryDesc }}
        </div>

        <!-- Projects & Tasks -->
        <div style="margin-bottom: 14px;">
            <div style="font-family:'Outfit', sans-serif; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--brand-dark); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                <i class="fas fa-tasks text-primary"></i> Structured Deliverables & Tasks
            </div>

            @if(!empty($projectsList))
                @foreach($projectsList as $proj)
                    @php
                        $pName = $proj['project_name'] ?? ($proj['name'] ?? 'Project Tasks');
                        $pTasks = $proj['tasks'] ?? [];
                    @endphp
                    <div class="project-block">
                        <div class="project-title">
                            <span><i class="fas fa-folder-open text-primary mr-1"></i> {{ $pName }}</span>
                            <span style="font-size: 9px; color: var(--brand-muted);">{{ count($pTasks) }} Tasks</span>
                        </div>
                        @forelse($pTasks as $t)
                            @php
                                $tName = $t['task_name'] ?? ($t['task'] ?? ($t['description'] ?? ($t['title'] ?? 'Task')));
                                $isDone = (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true) : (isset($t['completed']) ? ($t['completed'] == 1 || $t['completed'] === true) : true));
                            @endphp
                            <div class="task-row {{ $isDone ? 'done' : '' }}">
                                <div>
                                    <i class="fas {{ $isDone ? 'fa-check-circle text-success' : 'fa-circle text-muted' }} mr-1"></i>
                                    {{ $tName }}
                                </div>
                                <span class="task-badge {{ $isDone ? 'badge-done' : 'badge-pending' }}">
                                    {{ $isDone ? 'COMPLETED' : 'PENDING' }}
                                </span>
                            </div>
                        @empty
                            <div class="text-muted font-italic" style="font-size: 10px;">No task details specified.</div>
                        @endforelse
                    </div>
                @endforeach
            @elseif(!empty($taskList))
                <div class="project-block">
                    <div class="project-title">
                        <span><i class="fas fa-list-check text-primary mr-1"></i> Task Checklist</span>
                        <span style="font-size: 9px; color: var(--brand-muted);">{{ count($taskList) }} Items</span>
                    </div>
                    @foreach($taskList as $tItem)
                        @php
                            $tName = is_string($tItem) ? $tItem : ($tItem['text'] ?? ($tItem['task'] ?? ($tItem['title'] ?? ($tItem['description'] ?? 'Task'))));
                            $isDone = is_array($tItem) ? (isset($tItem['done']) ? ($tItem['done'] === true || $tItem['done'] === 'true') : true) : true;
                        @endphp
                        <div class="task-row {{ $isDone ? 'done' : '' }}">
                            <div>
                                <i class="fas {{ $isDone ? 'fa-check-circle text-success' : 'fa-circle text-muted' }} mr-1"></i>
                                {{ $tName }}
                            </div>
                            <span class="task-badge {{ $isDone ? 'badge-done' : 'badge-pending' }}">
                                {{ $isDone ? 'COMPLETED' : 'PENDING' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-3 bg-light rounded text-center text-muted font-italic" style="font-size: 10.5px;">
                    No checklist items attached to this daily report.
                </div>
            @endif
        </div>

        <!-- Blockers / Issues -->
        <div style="display: flex; gap: 8px; margin-bottom: 14px;">
            @if(count($realIssues) > 0)
                <div style="background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; padding: 6px 10px; border-radius: 6px; font-size: 10.5px; font-weight: 600; flex-grow: 1;">
                    <i class="fas fa-exclamation-triangle text-danger mr-1"></i> <strong>Blocker:</strong> {{ implode(', ', $realIssues) }}
                </div>
            @else
                <div style="background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; padding: 6px 10px; border-radius: 6px; font-size: 10.5px; font-weight: 600; flex-grow: 1;">
                    <i class="fas fa-check-circle text-success mr-1"></i> No Issues or Blockers Reported
                </div>
            @endif

            @if($notes)
                <div style="background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; padding: 6px 10px; border-radius: 6px; font-size: 10.5px; font-weight: 600; flex-grow: 1;">
                    <i class="fas fa-sticky-note text-warning mr-1"></i> <strong>Note:</strong> {{ $notes }}
                </div>
            @endif
        </div>

        <!-- Sign-Off -->
        <div class="signoff-grid">
            <div class="signoff-box">
                <div style="font-size: 9.5px; font-weight: 800; text-transform: uppercase; color: var(--brand-muted);">Employee Signature</div>
                <div style="font-size: 11px; font-weight: 700;">{{ $empName }}</div>
                <div style="border-top: 1px solid #CBD5E1; padding-top: 4px; font-size: 9px; color: #64748B;">Date: ____/____/20____</div>
            </div>
            <div class="signoff-box">
                <div style="font-size: 9.5px; font-weight: 800; text-transform: uppercase; color: var(--brand-muted);">Supervisor Approval</div>
                <div style="font-size: 11px; font-weight: 700;">{{ $reportingManagerName }}</div>
                <div style="border-top: 1px solid #CBD5E1; padding-top: 4px; font-size: 9px; color: #64748B;">Verified & Approved</div>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 16px; padding-top: 8px; border-top: 1px solid var(--brand-border); display: flex; justify-content: space-between; font-size: 9px; color: var(--brand-muted); font-weight: 600;">
            <div>{{ company_name() }} &bull; System-generated work report slip</div>
            <div>Confidential &bull; Printed: {{ now()->format('d M Y, h:i A') }}</div>
        </div>
    </div>

</body>
</html>
