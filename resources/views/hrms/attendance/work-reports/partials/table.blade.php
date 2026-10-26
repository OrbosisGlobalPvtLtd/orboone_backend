<!-- Entries & Export Toolbar -->
<div class="orb-table-tools-bar eo-toolbar">
    <div class="orb-table-length-box eo-toolbar-left">
        <div class="eo-entries-wrapper">
            <label class="eo-entries-label">
                Show
                <select id="recordsPerPageSelect" class="eo-entries-select">
                    @foreach([10, 25, 50, 100, 250] as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                    <option value="all" {{ (request('per_page') === 'all' || request('per_page') == -1) ? 'selected' : '' }}>All</option>
                </select>
                entries
            </label>
        </div>
    </div>

    <div id="workReportsExportButtons" class="orb-table-export-buttons eo-toolbar-right">
        <x-ui.export-buttons table="workReportsTable" />
    </div>
</div>

<!-- Table Section -->
<div class="att-table-wrap">
    <table class="att-table table table-hover" id="workReportsTable">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px; min-width: 50px;">#</th>
                <th style="min-width: 200px;">Employee</th>
                <th style="min-width: 110px;">Date</th>
                <th style="min-width: 90px;">Mode</th>
                <th style="min-width: 130px;">Shift Context</th>
                <th style="min-width: 120px;">Gross Work</th>
                <th style="min-width: 380px; width: 34%;">Work Summary Description</th>
                <th style="min-width: 250px; width: 22%;">Structured Tasks</th>
                <th class="text-right pr-4 no-export" style="width: 110px; min-width: 110px;">Actions</th>
            </tr>
        </thead>

        <tbody>
            @php
                $startIdx = method_exists($workLogs, 'currentPage') ? (($workLogs->currentPage() - 1) * $workLogs->perPage()) : 0;
            @endphp
            @forelse($workLogs as $log)
            @php
                $row = formatWorkReportRow($log);
                $empId = $log->employee_id ?: ($log->user_id ?: 0);
                $modeBadgeClass = $row['mode'] === 'WFH' ? 'badge-wfh' : 'badge-wfo';

                $logPayload = [
                    'id' => $log->id,
                    'work_log_id' => $log->id,
                    'employee_name' => $row['employee_name'],
                    'employee_code' => $row['employee_code'],
                    'passport_photo_url' => resolveEmployeePassportPhoto($log->employee ?? $log),
                    'employee_initial' => resolveEmployeeInitials($log->employee ?? $log),
                    'department' => $row['department'],
                    'designation' => $row['designation'],
                    'work_date' => $row['date'],
                    'shift_name' => $row['shift_context'],
                    'attendance_status' => (optional($log->attendance)->attendance_status ?? 'present'),
                    'title' => $row['title'] ?? 'Work Report',
                    'description' => $row['summary_desc'],
                    'status' => $row['status'],
                    'work_mode' => $row['mode'],
                    'submitted_time' => $row['submitted_time'],
                    'projects' => $row['projects'] ?? [],
                    'requirements' => array_map(fn($t) => ['text' => $t['text'], 'done' => $t['done']], $row['structured_tasks']),
                    'test_status' => $row['test_status'] ?? ['tested' => false, 'completed' => true],
                    'issues' => $row['issues'] ?? [],
                    'notes' => $row['notes'] ?? null,
                ];
            @endphp
            <tr>
                <td class="text-center font-weight-bold text-muted table-sr-no" style="font-size: 12px;" data-export="{{ $startIdx + $loop->iteration }}">
                    {{ $startIdx + $loop->iteration }}
                </td>

                <td data-export="{{ $row['employee'] }}">
                    <div>
                        <div class="table-emp-name font-weight-bold text-dark">{{ $row['employee_name'] }}</div>
                        <div class="table-emp-meta text-muted small mt-0.5">
                            <span class="font-weight-bold">({{ $row['employee_code'] }})</span> &bull; {{ $row['department'] }}
                        </div>
                    </div>
                </td>

                <td data-export="{{ $row['date'] }}" data-order="{{ $row['date_raw'] }}">
                    <div class="font-weight-bold text-dark" style="font-size: 13px; white-space: nowrap;">
                        {{ $row['date'] }}
                    </div>
                    @if($row['day_name'])
                    <div class="small text-muted font-weight-semibold">
                        {{ $row['day_name'] }}
                    </div>
                    @endif
                </td>

                <td data-export="{{ $row['mode'] }}">
                    <span class="badge-premium-pill {{ $modeBadgeClass }}">
                        @if($row['mode'] === 'WFH')
                            <i class="fas fa-laptop-house mr-1"></i> WFH
                        @else
                            <i class="fas fa-building mr-1"></i> WFO
                        @endif
                    </span>
                </td>

                <td data-export="{{ $row['shift_context'] }}">
                    <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                        {{ $row['shift_context'] }}
                    </div>
                </td>

                <td data-export="{{ $row['gross_work'] }}">
                    <div class="badge-gross-pill" style="white-space: nowrap;">
                        <i class="fas fa-stopwatch mr-1"></i> {{ $row['gross_work'] }}
                    </div>
                </td>

                <td data-export="{{ $row['summary_desc'] }}" style="min-width: 300px;">
                    @if(!empty($row['projects']) && count($row['projects']) > 0)
                        <div class="d-flex flex-column" style="gap: 6px;">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                @foreach($row['projects'] as $pItem)
                                    @php
                                        $pName = $pItem['project_name'] ?? ($pItem['name'] ?? 'Project');
                                        $pTasks = $pItem['tasks'] ?? [];
                                        $pDoneCount = 0;
                                        foreach($pTasks as $pt) {
                                            if (is_array($pt) && (!empty($pt['is_completed']) || !empty($pt['completed']))) $pDoneCount++;
                                            elseif (!is_array($pt)) $pDoneCount++;
                                        }
                                    @endphp
                                    <div class="p-1.5 px-2.5 rounded border" style="background: #F8FAFC; border: 1px solid #E2E8F0 !important; border-radius: 8px;">
                                        <div class="d-flex align-items-center justify-content-between" style="gap: 10px;">
                                            <span class="font-weight-bold text-dark" style="font-size: 12px;">
                                                <i class="fas fa-folder text-primary mr-1" style="font-size: 11px;"></i> {{ $pName }}
                                            </span>
                                            <span class="badge border" style="font-size: 10px; font-weight: 700; background: #fff; color: #475467; padding: 2px 6px; border-radius: 6px;">
                                                {{ count($pTasks) }} {{ count($pTasks) === 1 ? 'task' : 'tasks' }} &bull; <span class="{{ $pDoneCount === count($pTasks) ? 'text-success' : 'text-primary' }}">{{ $pDoneCount }}/{{ count($pTasks) }}</span>
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if(!empty($row['notes']))
                                <div class="mt-1 p-1.5 px-2 rounded" style="background: #FFFBEB; border: 1px solid #FEF3C7; color: #92400E; font-size: 11.5px; border-radius: 6px;">
                                    <strong>Note:</strong> {{ $row['notes'] }}
                                </div>
                            @endif
                            @if(!empty($row['issues']) && count($row['issues']) > 0)
                                <div class="mt-1 p-1.5 px-2 rounded" style="background: #FEF2F2; border: 1px solid #FEE2E2; color: #991B1B; font-size: 11.5px; border-radius: 6px;">
                                    <strong>Issue:</strong> {{ implode(', ', $row['issues']) }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div>
                            @if(!empty($row['title']))
                                <div class="mb-1.5">
                                    <div class="p-1.5 px-2.5 rounded border d-inline-flex align-items-center" style="background: #F8FAFC; border: 1px solid #E2E8F0 !important; border-radius: 8px; gap: 8px;">
                                        <span class="font-weight-bold text-dark" style="font-size: 12px;">
                                            <i class="fas fa-folder text-primary mr-1" style="font-size: 11px;"></i> {{ $row['title'] }}
                                        </span>
                                        @if(!empty($row['structured_tasks']))
                                            <span class="badge border" style="font-size: 10px; font-weight: 700; background: #fff; color: #475467; padding: 2px 6px; border-radius: 6px;">
                                                {{ count($row['structured_tasks']) }} {{ count($row['structured_tasks']) === 1 ? 'task' : 'tasks' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="work-summary-full-text">
                                @foreach($row['summary_paragraphs'] as $para)
                                    @php
                                        $paraDisplay = $para;
                                        if (!empty($row['title'])) {
                                            $paraDisplay = preg_replace('/^' . preg_quote($row['title'], '/') . '\s*[-–:]\s*/i', '', $paraDisplay);
                                        }
                                    @endphp
                                    @if(trim($paraDisplay) !== '')
                                        <p class="mb-1" style="line-height: 1.5; color: #1E293B; font-size: 12.5px; font-weight: 500;">
                                            {{ $paraDisplay }}
                                        </p>
                                    @endif
                                @endforeach
                            </div>
                            @if(!empty($row['notes']))
                                <div class="mt-1 p-1.5 px-2 rounded" style="background: #FFFBEB; border: 1px solid #FEF3C7; color: #92400E; font-size: 11.5px; border-radius: 6px;">
                                    <strong>Note:</strong> {{ $row['notes'] }}
                                </div>
                            @endif
                        </div>
                    @endif
                </td>

                <td data-export="{{ $row['structured_tasks_text'] }}" style="min-width: 240px;">
                    @if(!empty($row['projects']) && count($row['projects']) > 0)
                        <div class="structured-tasks-grouped d-flex flex-column" style="gap: 6px;">
                            @foreach($row['projects'] as $pItem)
                                @php
                                    $pName = $pItem['project_name'] ?? ($pItem['name'] ?? 'Project');
                                    $pTasks = $pItem['tasks'] ?? [];
                                @endphp
                                <div class="structured-project-group">
                                    <div class="font-weight-bold text-dark mb-1" style="font-size: 11.5px; opacity: 0.9;">
                                        <i class="fas fa-folder text-primary mr-1" style="font-size: 10.5px;"></i> {{ $pName }}:
                                    </div>
                                    @foreach($pTasks as $pt)
                                        @php
                                            $ptName = is_array($pt) ? ($pt['task_name'] ?? ($pt['description'] ?? 'Task')) : $pt;
                                            $ptDone = is_array($pt) ? (!empty($pt['is_completed']) || !empty($pt['completed'])) : true;
                                        @endphp
                                        <div class="structured-task-item {{ $ptDone ? 'done' : 'pending' }} d-flex align-items-center pl-2 mb-1" style="line-height: 1.4; font-size: 12px; font-weight: 600;">
                                            <span class="task-tag font-weight-bold {{ $ptDone ? 'text-success' : 'text-warning' }}" style="font-size: 11px; margin-right: 6px; white-space: nowrap;">
                                                {{ $ptDone ? '[Done]' : '[Pending]' }}
                                            </span>
                                            <span class="text-dark">{{ $ptName }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="structured-tasks-list">
                            @if(!empty($row['title']))
                                <div class="font-weight-bold text-dark mb-1" style="font-size: 11.5px; opacity: 0.9;">
                                    <i class="fas fa-folder text-primary mr-1" style="font-size: 10.5px;"></i> {{ $row['title'] }}:
                                </div>
                            @endif
                            @foreach($row['structured_tasks'] as $tItem)
                                <div class="structured-task-item {{ $tItem['done'] ? 'done' : 'pending' }} d-flex align-items-center pl-2 mb-1" style="line-height: 1.4; font-size: 12px; font-weight: 600;">
                                    <span class="task-tag font-weight-bold {{ $tItem['done'] ? 'text-success' : 'text-warning' }}" style="font-size: 11px; margin-right: 6px; white-space: nowrap;">
                                        {{ $tItem['done'] ? '[Done]' : '[Pending]' }}
                                    </span>
                                    <span class="text-dark">{{ $tItem['text'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </td>

                <td class="text-right pr-4 no-export" style="white-space: nowrap;">
                    <div class="action-btn-group">
                        <button type="button" class="btn-action-primary" 
                                data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" 
                                onclick="parseAndOpenWorkReport(this)"
                                title="View Full Report Details">
                            <i class="fas fa-eye"></i> Details
                        </button>
                        @if($empId)
                        <a href="{{ route('hrms.attendance.work-reports.employee-history', $empId) }}" 
                           target="_blank" 
                           class="btn-action-secondary" 
                           title="View Employee Work History">
                            <i class="fas fa-history"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-3x d-block mb-3 opacity-50"></i>
                    <h5 class="font-weight-bold text-dark mb-1">No Daily Work Reports Found</h5>
                    <p class="text-muted font-weight-semibold mb-0">Try adjusting your filters or date range to see results.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Server-Side Pagination using Orbo Theme Template -->
@if(method_exists($workLogs, 'links'))
    {{ $workLogs->appends(request()->query())->links('vendor.pagination.orbo') }}
@endif
