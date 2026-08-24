@extends('layouts.panel', ['active' => 'reporting_work_reports'])

@section('page_title', 'Reporting Employee Work Reports')

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
    padding: 20px 24px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
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
        <div class="rep-hero" style="padding: 20px 24px;">
            <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-file-signature mr-2"></i>Reporting Employee Work Reports</h3>
            <p class="mb-0 opacity-90 small">Daily work summaries submitted by your reporting employees upon punch-out.</p>
        </div>

        <!-- Filter Card -->
        <div class="rep-card p-3 mb-4">
            <form method="GET" action="{{ route('reporting.work_reports') }}" class="form-inline flex-wrap gap-2">
                <input type="date" name="date" class="form-control mr-2 mb-2" value="{{ request('date') }}" style="border-radius: 10px;" placeholder="Select Date">

                <select name="employee_id" class="form-control mr-2 mb-2" style="border-radius: 10px;">
                    <option value="">-- All Reporting Employees --</option>
                    @foreach($teamEmployees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->display_name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>

                <select name="project_id" class="form-control mr-2 mb-2" style="border-radius: 10px;">
                    <option value="">-- All Projects --</option>
                    @foreach($teamProjects as $prj)
                        <option value="{{ $prj->id }}" {{ request('project_id') == $prj->id ? 'selected' : '' }}>
                            {{ $prj->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary font-weight-bold px-4 mb-2" style="border-radius: 10px; background: var(--orb-primary); border-color: var(--orb-primary);"><i class="fas fa-filter mr-1"></i> Filter</button>
                @if(request('date') || request('employee_id') || request('project_id'))
                    <a href="{{ route('reporting.work_reports') }}" class="btn btn-light border text-muted font-weight-bold ml-2 mb-2" style="border-radius: 10px;">Clear</a>
                @endif
            </form>
        </div>

        <div class="rep-card">
            <!-- Toolbar for Entries Length & Export Buttons -->
            <div class="orb-table-toolbar d-flex align-items-center justify-content-between p-3 border-bottom">
                <div class="toolbar-left"></div>
                <div class="toolbar-right d-flex align-items-center"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="reportingWorkReportsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 55px;">S.No.</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3">Employee Name</th>
                            <th class="py-3">Project Name</th>
                            <th class="py-3">Tasks & Status</th>
                            <th class="py-3 text-center">Submitted At</th>
                            <th class="py-3 text-right pr-4 no-export">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workReports as $report)
                            @php
                                $tasks = $report->work_summary_json;
                                if (is_string($tasks)) {
                                    $tasks = json_decode($tasks, true);
                                }

                                $repTitle = $report->project_name ?? 'General Work';
                                $repDesc = null;
                                $repStatus = 'Completed';
                                $projectsList = [];
                                $requirementsList = [];
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
                                                    $tDone = (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true || $t['is_completed'] === 'true') : (isset($t['completed']) ? ($t['completed'] == 1 || $t['completed'] === true || $t['completed'] === 'true') : true));
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

                                    $repStatus = $tasks['today_work_status'] ?? ($tasks['current_status'] ?? ($tasks['status'] ?? 'Completed'));

                                    if (!empty($projectsList) && !empty($projectsList[0]['project_name'])) {
                                        $repTitle = $projectsList[0]['project_name'];
                                    } elseif (!empty($tasks['task_name'])) {
                                        $repTitle = $tasks['task_name'];
                                    } elseif (!empty($tasks['title'])) {
                                        $repTitle = $tasks['title'];
                                    }

                                    $rawDesc = $tasks['description'] ?? ($tasks['today_work_description'] ?? null);
                                    if ($rawDesc && !str_contains($rawDesc, '☑') && !str_contains($rawDesc, '☐')) {
                                        $repDesc = $rawDesc;
                                    } else {
                                        if ($repStatus) {
                                            $repDesc = "Today's Work Status: " . ucfirst($repStatus);
                                        } else {
                                            $repDesc = "Work report submitted with project tasks.";
                                        }
                                    }

                                    $rawIssues = $tasks['issues_blockers'] ?? ($tasks['issues'] ?? []);
                                    if (is_array($rawIssues)) {
                                        $issues = $rawIssues;
                                    } elseif (is_string($rawIssues) && trim($rawIssues) !== '' && strtolower(trim($rawIssues)) !== 'no issues' && strtolower(trim($rawIssues)) !== 'none') {
                                        $issues = [$rawIssues];
                                    }

                                    $notes = $tasks['additional_notes'] ?? ($tasks['remarks'] ?? ($tasks['notes'] ?? null));
                                } else {
                                    $repDesc = $report->work_description ?? ($report->work_summary ?? 'No summary provided.');
                                }

                                $taskCount = is_array($requirementsList) ? count($requirementsList) : 0;
                                $tasksLabel = $taskCount . ' ' . \Illuminate\Support\Str::plural('Task', $taskCount);
                                $stLower = strtolower(trim($repStatus));
                                $statusBadgeStyle = match(true) {
                                    in_array($stLower, ['completed', 'done', 'success']) => 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;',
                                    in_array($stLower, ['testing', 'tested']) => 'background: #E0F2FE; color: #0369A1; border: 1px solid #7DD3FC;',
                                    in_array($stLower, ['in-progress', 'in_progress', 'progress', 'pending']) => 'background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;',
                                    in_array($stLower, ['blocked', 'failed']) => 'background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5;',
                                    default => 'background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;'
                                };

                                $passportPhotoUrl = resolveEmployeePassportPhoto($report);
                                $employeeInitial = resolveEmployeeInitials($report);

                                $reportDate = $report->work_date ? \Carbon\Carbon::parse($report->work_date)->format('d M Y') : '-';
                                $submittedAt = $report->created_at ? \Carbon\Carbon::parse($report->created_at)->format('h:i A') : '—';
                                $empFullText = ($report->display_name ?? 'Employee') . ' (' . ($report->employee_code ?? 'N/A') . ')';

                                // Construct clean template string for exports (CSV, Excel, PDF, Print)
                                $exportTaskLines = [];
                                if (!empty($requirementsList)) {
                                    foreach ($requirementsList as $tItem) {
                                        $tText = is_string($tItem) ? $tItem : ($tItem['text'] ?? $tItem['task'] ?? $tItem['title'] ?? '');
                                        $tDone = is_array($tItem) ? ($tItem['done'] ?? true) : true;
                                        if ($tText) {
                                            $exportTaskLines[] = ($tDone ? '[Done] ' : '[Pending] ') . $tText;
                                        }
                                    }
                                }
                                $exportTaskText = "Status: " . strtoupper($repStatus);
                                if (!empty($exportTaskLines)) {
                                    $exportTaskText .= "\nTasks:\n" . implode("\n", $exportTaskLines);
                                }

                                $logPayload = [
                                    'employee_name' => $report->display_name ?? 'Employee',
                                    'employee_code' => $report->employee_code ?? 'N/A',
                                    'passport_photo_url' => $passportPhotoUrl,
                                    'employee_initial' => $employeeInitial,
                                    'department' => $report->department_name ?? 'Staff',
                                    'designation' => $report->designation_name ?? 'Member',
                                    'work_date' => $reportDate,
                                    'shift_name' => 'Default Shift',
                                    'attendance_status' => ($report->attendance_status ?? 'present') === 'absent' && ($report->is_lwp ?? false) ? '🔴 ABSENT' : ($report->attendance_status ?? 'present'),
                                    'is_lwp' => (bool) ($report->is_lwp ?? false),
                                    'title' => $repTitle,
                                    'description' => $repDesc,
                                    'status' => $repStatus,
                                    'work_mode' => strtoupper($report->work_mode ?? 'WFO'),
                                    'submitted_time' => $submittedAt,
                                    'projects' => $projectsList,
                                    'requirements' => $requirementsList,
                                    'issues' => $issues,
                                    'notes' => $notes,
                                ];
                            @endphp
                        <tr>
                            <!-- S.No. -->
                            <td class="py-3 px-3 align-middle text-center font-weight-bold text-muted" style="font-size: 12.5px;" data-export="{{ $loop->iteration }}">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-3 px-4 align-middle font-weight-bold text-dark" style="white-space: nowrap;" data-export="{{ $reportDate }}">
                                {{ $reportDate }}
                            </td>
                            <td class="py-3 align-middle" data-export="{{ $empFullText }}">
                                <div class="d-flex align-items-center">
                                    <!-- <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2" style="width: 34px; height: 34px; border-radius: 10px; background: #EEF2FF; color: #4F46E5; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; overflow: hidden;">
                                        @if($passportPhotoUrl)
                                            <img src="{{ $passportPhotoUrl }}" alt="{{ $report->display_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            {{ $employeeInitial }}
                                        @endif
                                    </span> -->
                                    <div>
                                        <strong class="text-dark font-weight-bold d-block" style="line-height: 1.2;">{{ $report->display_name }}</strong>
                                        <small class="text-muted" style="font-size: 11px;">{{ $report->employee_code }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 align-middle" data-export="{{ $repTitle }}">
                                <span class="font-weight-bold text-primary" style="font-size: 13px;">
                                    <i class="fas fa-folder mr-1 text-primary"></i> {{ $repTitle }}
                                </span>
                            </td>
                            <td class="py-3 align-middle" data-export="{{ $exportTaskText }}">
                                <div class="d-flex align-items-center gap-2" style="gap: 8px;">
                                    <span class="badge badge-light border font-weight-bold px-2.5 py-1 text-dark" style="border-radius: 8px; font-size: 11px;">
                                        <i class="fas fa-list-check text-primary mr-1"></i> {{ $tasksLabel }}
                                    </span>
                                    <span class="badge font-weight-bold text-uppercase px-2.5 py-1" style="border-radius: 8px; font-size: 10px; letter-spacing: 0.05em; {{ $statusBadgeStyle }}">
                                        {{ str_replace('_', ' ', $repStatus) }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 align-middle text-center font-weight-bold text-muted small" data-export="{{ $submittedAt }}">
                                {{ $submittedAt }}
                            </td>
                            <td class="py-3 align-middle text-right pr-4 no-export">
                                <button type="button" class="btn btn-sm btn-light border p-2 font-weight-bold" style="border-radius: 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;" data-work-log="{{ json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" onclick="parseAndOpenWorkReport(this)">
                                    <i class="fas fa-eye text-primary"></i> View Report
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-file-signature fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Daily Work Reports Found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer for Pagination & Info (Populated by DataTables) -->
            <div class="orb-table-footer p-3 bg-light border-top d-flex align-items-center justify-content-between"></div>

            @if($workReports->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $workReports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

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
        if ($.fn.DataTable.isDataTable('#reportingWorkReportsTable')) {
            $('#reportingWorkReportsTable').DataTable().destroy();
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
                        return val;
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

        var table = $('#reportingWorkReportsTable').DataTable({
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
                    title: 'OrboOne HRMS - Team Management Daily Work Reports',
                    exportOptions: exportOptionsPdf,
                    customize: function (doc) {
                        doc.pageOrientation = 'landscape';
                        doc.pageSize = 'A4';
                        doc.pageMargins = [20, 45, 20, 35];

                        doc['header'] = function(currentPage, pageCount) {
                            return {
                                margin: [20, 15, 20, 0],
                                columns: [
                                    {
                                        text: 'ORBOONE HRMS — DAILY WORK REPORTS SUMMARY',
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

                        doc.content[1].table.widths = ['6%', '11%', '22%', '18%', '33%', '10%'];
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'orb-export-btn',
                    title: '',
                    exportOptions: exportOptionsPrint,
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
                                    letter-spacing: -0.01em !important;
                                    color: #FFFFFF !important;
                                }
                                .print-hero p {
                                    margin: 2px 0 0 0 !important;
                                    font-size: 12px !important;
                                    opacity: 0.92 !important;
                                    color: #FFFFFF !important;
                                }
                                .print-meta-badge {
                                    background: rgba(255, 255, 255, 0.22) !important;
                                    padding: 6px 14px !important;
                                    border-radius: 8px !important;
                                    font-size: 11px !important;
                                    font-weight: 700 !important;
                                    color: #FFFFFF !important;
                                    border: 1px solid rgba(255, 255, 255, 0.3) !important;
                                    white-space: nowrap !important;
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
                                    letter-spacing: 0.05em !important;
                                    padding: 10px 14px !important;
                                    border: none !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                table.dataTable tbody td {
                                    padding: 10px 14px !important;
                                    border-bottom: 1px solid #E2E8F0 !important;
                                    font-size: 11.5px !important;
                                    vertical-align: top !important;
                                }
                                table.dataTable tbody tr:nth-child(even) {
                                    background: #F8FAFC !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .print-status-badge {
                                    display: inline-block !important;
                                    padding: 3px 8px !important;
                                    border-radius: 6px !important;
                                    font-size: 10px !important;
                                    font-weight: 800 !important;
                                    text-transform: uppercase !important;
                                    letter-spacing: 0.04em !important;
                                    margin-bottom: 6px !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .print-st-completed { background: #DCFCE7 !important; color: #15803D !important; border: 1px solid #86EFAC !important; }
                                .print-st-testing { background: #E0F2FE !important; color: #0369A1 !important; border: 1px solid #7DD3FC !important; }
                                .print-st-progress { background: #FEF3C7 !important; color: #B45309 !important; border: 1px solid #FCD34D !important; }
                                .print-st-blocked { background: #FEE2E2 !important; color: #B91C1C !important; border: 1px solid #FCA5A5 !important; }
                                .print-task-box {
                                    background: #F8FAFC !important;
                                    border: 1px solid #E2E8F0 !important;
                                    border-radius: 8px !important;
                                    padding: 8px 10px !important;
                                    margin-top: 4px !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .print-task-line {
                                    font-size: 11px !important;
                                    line-height: 1.5 !important;
                                    color: #334155 !important;
                                    display: flex !important;
                                    align-items: flex-start !important;
                                    margin-bottom: 2px !important;
                                }
                                .print-icon-done { color: #16A34A !important; font-weight: 800 !important; margin-right: 6px !important; }
                                .print-icon-pend { color: #D97706 !important; font-weight: 800 !important; margin-right: 6px !important; }
                            </style>
                        `);

                        body.find('h1').remove();

                        var printDate = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                        body.prepend(`
                            <div class="print-hero">
                                <div>
                                    <h2>OrboOne HRMS</h2>
                                    <p>Team Management — Daily Work Reports & Project Tasks Summary</p>
                                </div>
                                <div class="print-meta-badge">
                                    Date: ${printDate}
                                </div>
                            </div>
                        `);

                        body.find('table tbody tr').each(function() {
                            var $td = $(this).find('td:nth-child(5)');
                            var rawText = $td.text() || '';
                            if (!rawText.trim()) return;

                            var lines = rawText.split('\n');
                            var html = '';
                            var taskLinesHtml = '';

                            lines.forEach(function(line) {
                                var trimmed = line.trim();
                                if (!trimmed) return;

                                if (trimmed.startsWith('Status:')) {
                                    var statusStr = trimmed.replace('Status:', '').trim().toUpperCase();
                                    var cls = 'print-st-progress';
                                    if (statusStr === 'COMPLETED' || statusStr === 'DONE') cls = 'print-st-completed';
                                    else if (statusStr === 'TESTING' || statusStr === 'TESTED') cls = 'print-st-testing';
                                    else if (statusStr === 'BLOCKED') cls = 'print-st-blocked';

                                    html += `<div class="print-status-badge ${cls}">${statusStr}</div>`;
                                } else if (trimmed === 'Tasks:') {
                                } else if (trimmed.startsWith('[Done]')) {
                                    var taskName = trimmed.replace('[Done]', '').trim();
                                    taskLinesHtml += `<div class="print-task-line"><span class="print-icon-done">✓</span><span>${taskName}</span></div>`;
                                } else if (trimmed.startsWith('[Pending]')) {
                                    var taskName = trimmed.replace('[Pending]', '').trim();
                                    taskLinesHtml += `<div class="print-task-line"><span class="print-icon-pend">○</span><span>${taskName}</span></div>`;
                                } else {
                                    taskLinesHtml += `<div class="print-task-line"><span>${trimmed}</span></div>`;
                                }
                            });

                            if (taskLinesHtml) {
                                html += `<div class="print-task-box">${taskLinesHtml}</div>`;
                            }

                            if (html) {
                                $td.html(html);
                            }
                        });
                    }
                }
            ],
            language: {
                emptyTable: 'No daily work reports found.',
                zeroRecords: 'No matching work reports found.',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ reports',
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
    });
</script>
@endsection
