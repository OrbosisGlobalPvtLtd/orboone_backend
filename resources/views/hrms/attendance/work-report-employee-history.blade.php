@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Employee Work Report History')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')

@include('hrms.employee.partials.styles')

<style>
    .report-page {
        min-height: calc(100vh - 90px);
        background: #F6F7FB;
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }
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
        margin-bottom: 28px !important;
    }
    .emp-profile-pill {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .emp-profile-avatar {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 900;
        color: #fff;
        overflow: hidden;
        flex-shrink: 0;
    }
    .emp-profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .action-toolbar-pill {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-pill-action {
        height: 42px;
        padding: 0 20px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.2);
        color: #FFFFFF !important;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }
    .btn-pill-action:hover {
        background: #FFFFFF;
        color: var(--orb-primary) !important;
        transform: translateY(-2px);
    }
    .stats-card-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }
    .stat-card-box {
        background: #FFFFFF;
        border: 1px solid #E7EAF3;
        border-radius: 20px;
        padding: 20px 24px;
        box-shadow: 0 10px 25px rgba(16, 24, 40, .03);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .stat-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-card-val {
        font-size: 22px;
        font-weight: 900;
        color: #101828;
        line-height: 1.2;
    }
    .stat-card-lbl {
        font-size: 12px;
        font-weight: 700;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 2px;
    }
    .orb-table-card {
        background: #fff;
        border-radius: 24px;
        border: 1px solid #E7EAF3;
        box-shadow: 0 14px 35px rgba(16,24,40,.07);
        overflow: hidden;
        margin-bottom: 30px;
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
</style>

<div class="report-page">
    <div class="container-fluid max-w-1500">

        <!-- Hero Header -->
        <div class="report-header-premium">
            <div class="emp-profile-pill">
                <div class="emp-profile-avatar">
                    @if($summary['passport_photo_url'])
                        <img src="{{ $summary['passport_photo_url'] }}" alt="{{ $summary['employee_name'] }}">
                    @else
                        <span>{{ $summary['employee_initial'] }}</span>
                    @endif
                </div>
                <div>
                    <h3 class="m-0 font-weight-bold text-white">{{ $summary['employee_name'] }}</h3>
                    <div class="text-white-50 font-weight-bold mt-1" style="font-size: 14px;">
                        Code: {{ $summary['employee_code'] }} &bull; {{ $summary['department'] }} &bull; {{ $summary['designation'] }}
                    </div>
                </div>
            </div>

            <div class="action-toolbar-pill">
                <a href="{{ route('hrms.attendance.work-reports.employee-history.print', ['employee' => $employee->id, 'from_date' => request('from_date'), 'to_date' => request('to_date')]) }}" 
                   target="_blank" class="btn-pill-action" style="background: rgba(255, 255, 255, 0.95); color: var(--orb-primary) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <i class="fas fa-file-pdf text-danger"></i> Print / Save PDF
                </a>
                <a href="{{ route('hrms.attendance.work-reports') }}" class="btn-pill-action">
                    <i class="fas fa-arrow-left"></i> All Work Reports
                </a>
            </div>
        </div>

        <!-- Metric Summary Cards Grid -->
        <div class="stats-card-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card-box">
                <div class="stat-card-icon"><i class="fas fa-clipboard-check"></i></div>
                <div>
                    <div class="stat-card-val">{{ $summary['total_reports'] }}</div>
                    <div class="stat-card-lbl">Daily Reports Logged</div>
                </div>
            </div>

            <div class="stat-card-box">
                <div class="stat-card-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-card-val" style="font-size: 19px;">{{ $summary['total_gross_formatted'] }}</div>
                    <div class="stat-card-lbl">Avg {{ $summary['avg_daily_formatted'] }}</div>
                </div>
            </div>

            <div class="stat-card-box">
                <div class="stat-card-icon"><i class="fas fa-tasks"></i></div>
                <div>
                    <div class="stat-card-val" style="font-size: 19px;">{{ $summary['completed_tasks'] }} <span style="font-size: 13px; color: #667085; font-weight: 700;">/ {{ $summary['total_tasks'] }}</span></div>
                    <div class="stat-card-lbl">{{ $summary['completion_rate'] }}% Tasks Completed</div>
                </div>
            </div>

            <div class="stat-card-box">
                <div class="stat-card-icon"><i class="fas fa-laptop-house"></i></div>
                <div>
                    <div class="stat-card-val" style="font-size: 19px;">{{ $summary['wfo_count'] }} <span style="font-size: 12px; color: #166534; font-weight: 800;">WFO</span> &bull; {{ $summary['wfh_count'] }} <span style="font-size: 12px; color: #1E40AF; font-weight: 800;">WFH</span></div>
                    <div class="stat-card-lbl">Work Mode Distribution</div>
                </div>
            </div>
        </div>

        <!-- Data Card -->
        <div class="card orb-table-card">
            <!-- Filter Row -->
            <div class="p-4 bg-light border-bottom">
                <form method="GET" action="{{ route('hrms.attendance.work-reports.employee-history', $employee->id) }}" class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="font-weight-bold text-muted small text-uppercase mb-1">From Date</label>
                        <input type="date" name="from_date" class="form-control rounded-10" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="font-weight-bold text-muted small text-uppercase mb-1">To Date</label>
                        <input type="date" name="to_date" class="form-control rounded-10" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="font-weight-bold text-muted small text-uppercase mb-1">Search Keyword</label>
                        <input type="text" id="dtSearchInput" class="form-control rounded-10" placeholder="Search tasks or summary...">
                    </div>
                    <div class="col-md-3 text-md-right d-flex align-items-end justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-10 font-weight-bold px-3 mr-2" style="background: var(--orb-primary); border: none;">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                        <a href="{{ route('hrms.attendance.work-reports.employee-history', $employee->id) }}" class="btn btn-outline-secondary rounded-10 font-weight-bold px-3">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-responsive p-3">
                <table class="table table-hover align-middle mb-0" id="employeeHistoryTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">S.No.</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Shift Context</th>
                            <th>Gross Duration</th>
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

                            $logPayload = [
                                'id' => $log->id,
                                'work_log_id' => $log->id,
                                'employee_name' => $summary['employee_name'],
                                'employee_code' => $summary['employee_code'],
                                'passport_photo_url' => $summary['passport_photo_url'],
                                'employee_initial' => $summary['employee_initial'],
                                'department' => $summary['department'],
                                'designation' => $summary['designation'],
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
                            <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                            <td><strong>{{ $log->work_date ? $log->work_date->format('d M Y') : '-' }}</strong></td>
                            <td><span class="badge-premium-pill {{ $modeBadgeClass }}">{{ $modeText }}</span></td>
                            <td>{{ optional($attendance)->attendanceTime->name ?? 'Default Shift' }}</td>
                            <td><strong class="text-dark">{{ $grossWork }}</strong></td>
                            <td>
                                <div class="p-2 bg-light rounded-10 font-weight-bold text-dark" style="font-size: 13px;">
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
                                <button type="button" class="btn btn-sm btn-light border rounded-10 px-3 font-weight-bold"
                                        data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
                                        onclick="parseAndOpenWorkReport(this)">
                                    <i class="fas fa-eye text-primary"></i> Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted font-weight-bold">
                                No work report history found for this employee in the selected date range.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
$(function() {
    var table = $('#employeeHistoryTable').DataTable({
        pageLength: 25,
        ordering: true,
        searching: true,
        paging: true,
        info: true,
        buttons: [
            { extend: 'csvHtml5', text: '<i class="fas fa-file-csv text-info"></i> CSV', className: 'btn btn-sm btn-outline-secondary rounded-8 mr-1' },
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel text-success"></i> Excel', className: 'btn btn-sm btn-outline-secondary rounded-8 mr-1' },
            { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf text-danger"></i> PDF', className: 'btn btn-sm btn-outline-secondary rounded-8 mr-1', orientation: 'landscape' },
            { extend: 'print', text: '<i class="fas fa-print text-primary"></i> Print Table', className: 'btn btn-sm btn-outline-secondary rounded-8' }
        ]
    });

    table.buttons().container().appendTo('#employeeHistoryTable_wrapper .col-md-6:eq(0)');

    $('#dtSearchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
});
</script>
@endsection
