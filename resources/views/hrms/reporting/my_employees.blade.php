@extends('layouts.panel', ['active' => 'team_my_team'])

@section('page_title', 'My Team')

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
    padding: 22px 26px;
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
        <!-- Hero Header -->
        <div class="rep-hero">
            <div>
                <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-users mr-2"></i>My Team Management</h3>
                <p class="mb-0 opacity-90 small">Unified operational workspace for team members under your supervision (Project Team & Reporting Scope).</p>
            </div>
        </div>

        <div class="rep-card">
            <!-- Card Header Title (Compact height) -->
            <div class="d-flex align-items-center justify-content-between border-bottom bg-white flex-wrap" style="padding: 12px 20px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 9px; background: #EEF2FF; color: #4F46E5; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="fas fa-users"></i>
                    </span>
                    <div>
                        <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">My Team Members</h5>
                    </div>
                </div>
            </div>

            <!-- Embedded Attached Filters inside Card (Live auto-filter without Apply button! Reset button on the right) -->
            <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="form-inline flex-wrap gap-2" style="gap: 12px;">
                    <select id="filter-team-source" class="form-control" style="border-radius: 10px; font-size: 13px; height: 38px; min-width: 180px;">
                        <option value="">-- All Team Sources --</option>
                        <option value="Both">Both</option>
                        <option value="Reporting Team">Reporting Team</option>
                        <option value="Project Team">Project Team</option>
                    </select>

                    <select id="filter-attendance-today" class="form-control" style="border-radius: 10px; font-size: 13px; height: 38px; min-width: 180px;">
                        <option value="">-- All Attendance --</option>
                        <option value="PRESENT">Present</option>
                        <option value="NOT PUNCHED">Not Punched</option>
                        <option value="ON LEAVE">On Leave</option>
                    </select>

                    <input type="text" id="filter-search-keyword" class="form-control" placeholder="Search employee, project, designation..." style="border-radius: 10px; font-size: 13px; height: 38px; min-width: 250px;">
                </div>

                <!-- Reset Button on the right side of the filter bar -->
                <button type="button" class="btn btn-light border font-weight-bold" id="btn-reset-my-team-filters" style="border-radius: 10px; font-size: 13px; color: #475467; height: 38px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-undo text-muted" style="font-size: 11px;"></i> Reset Filters
                </button>
            </div>

            <!-- Toolbar for Entries & Export Buttons -->
            <div class="orb-table-toolbar d-flex align-items-center justify-content-between p-3 border-bottom">
                <div class="toolbar-left"></div>
                <div class="toolbar-right d-flex align-items-center"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="myTeamTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 55px;">S.No.</th>
                            <th class="py-3 px-4">Employee Name</th>
                            <th class="py-3">Department & Designation</th>
                            <th class="py-3">Reporting Manager</th>
                            <th class="py-3 text-center">Team Source</th>
                            <th class="py-3">Current Projects & Teams</th>
                            <th class="py-3 text-center">Attendance Today</th>
                            <th class="py-3 text-center">Leave Status</th>
                            <th class="py-3 text-center">Work Report</th>
                            <th class="py-3">Tasks & Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            @php
                                $leaveToday = $emp->leave_today ?? false;
                                $attendanceToday = $emp->attendance_today ?? null;
                                $workReportToday = $emp->work_report_today ?? false;
                                $projectAssignments = $emp->project_assignments_list ?? [];
                                $activeTask = $emp->active_task ?? null;
                                $totalTasksCount = $emp->total_tasks_count ?? 0;
                                $completedTasksCount = $emp->completed_tasks_count ?? 0;
                                $reportingManager = $emp->reportingManager ?? null;

                                $displayName = $emp->display_name ?? (optional($emp->user ?? null)->name ?? 'Employee');
                                $empCode = $emp->employee_code ?? 'N/A';
                                $empExportText = $displayName . ' (' . $empCode . ')';

                                $teamSourceStr = $emp->team_source ?? 'Reporting Team';
                                $teamSourceBadge = match($teamSourceStr) {
                                    'Both' => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
                                    'Reporting Team' => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;',
                                    default => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;'
                                };

                                $attTodayText = 'NOT PUNCHED';
                                $attBadgeStyle = 'background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;';
                                $attIcon = 'far fa-circle';

                                if ($leaveToday) {
                                    $attTodayText = 'ON LEAVE';
                                    $attBadgeStyle = 'background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;';
                                    $attIcon = 'fas fa-umbrella-beach';
                                } elseif ($attendanceToday) {
                                    $attTodayText = strtoupper($attendanceToday->work_type ?? 'Present');
                                    $attBadgeStyle = 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;';
                                    $attIcon = 'fas fa-check-circle';
                                }

                                $leaveText = $leaveToday ? 'On Leave' : 'No Leave';

                                $reportText = $workReportToday ? 'Submitted' : 'Pending';
                                $reportStyle = $workReportToday 
                                    ? 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;'
                                    : 'background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;';
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

                            <!-- Department & Designation -->
                            <td class="py-3 align-middle" data-export="{{ optional($emp->department ?? null)->name ?? 'General' }} - {{ optional($emp->designation ?? null)->name ?? 'Staff' }}">
                                <div>
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 12.5px; line-height: 1.2;">
                                        <i class="fas fa-building text-muted mr-1" style="font-size: 11px;"></i> {{ optional($emp->department ?? null)->name ?? 'General' }}
                                    </span>
                                    <small class="text-muted" style="font-size: 11px; font-weight: 600;">{{ optional($emp->designation ?? null)->name ?? 'Employee' }}</small>
                                </div>
                            </td>

                            <!-- Reporting Manager -->
                            <td class="py-3 align-middle" data-export="{{ $reportingManager ? ($reportingManager->display_name ?? 'Manager') : 'N/A' }}">
                                @if($reportingManager)
                                    <div>
                                        <strong class="text-dark font-weight-bold d-block" style="font-size: 12.5px; line-height: 1.2;">
                                            <i class="fas fa-user-shield text-primary mr-1" style="font-size: 11px;"></i>{{ $reportingManager->display_name ?? 'Manager' }}
                                        </strong>
                                        <small class="text-muted" style="font-size: 11px;">{{ $reportingManager->employee_code ?? '' }}</small>
                                    </div>
                                @else
                                    <span class="small text-muted font-weight-bold">—</span>
                                @endif
                            </td>

                            <!-- Team Source -->
                            <td class="py-3 align-middle text-center" data-export="{{ $teamSourceStr }}">
                                <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 8px; font-size: 10.5px; {{ $teamSourceBadge }}">
                                    <i class="fas fa-layer-group mr-1" style="font-size: 9.5px;"></i> {{ $teamSourceStr }}
                                </span>
                            </td>

                            <!-- Projects & Teams -->
                            <td class="py-3 align-middle">
                                @forelse($projectAssignments as $prj)
                                    <div class="mb-1">
                                        <span class="font-weight-bold text-primary d-block" style="font-size: 12px; line-height: 1.2;">
                                            <i class="fas fa-folder text-primary mr-1" style="font-size: 10.5px;"></i>{{ $prj->project_name }}
                                        </span>
                                        <small class="text-muted" style="font-size: 10.5px;">
                                            @if($prj->team_name)<span class="badge badge-light border" style="font-size: 10px;">{{ $prj->team_name }}</span>@endif
                                            @if($prj->role_name)<span class="text-info font-weight-bold ml-1">({{ $prj->role_name }})</span>@endif
                                        </small>
                                    </div>
                                @empty
                                    <span class="small text-muted font-weight-bold">No Active Projects</span>
                                @endforelse
                            </td>

                            <!-- Attendance Today -->
                            <td class="py-3 align-middle text-center" data-export="{{ $attTodayText }}">
                                <span class="badge font-weight-bold text-uppercase px-2.5 py-1" style="border-radius: 8px; font-size: 10px; letter-spacing: 0.04em; {{ $attBadgeStyle }}">
                                    <i class="{{ $attIcon }} mr-1"></i> {{ $attTodayText }}
                                </span>
                                @if($attendanceToday && isset($attendanceToday->punch_in_time))
                                    <small class="d-block text-muted mt-1 font-weight-bold" style="font-size: 10.5px;">
                                        {{ \Carbon\Carbon::parse($attendanceToday->punch_in_time)->format('h:i A') }}
                                    </small>
                                @endif
                            </td>

                            <!-- Leave Status -->
                            <td class="py-3 align-middle text-center" data-export="{{ $leaveText }}">
                                @if($leaveToday)
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

                            <!-- Active Task & Progress -->
                            <td class="py-3 align-middle" style="min-width: 170px;" data-export="{{ $activeTask ? $activeTask->title : ($totalTasksCount > 0 ? $completedTasksCount . '/' . $totalTasksCount . ' Done' : 'No Tasks') }}">
                                @if($activeTask)
                                    <strong class="text-dark small d-block text-truncate" style="max-width: 170px; font-size: 12px;">{{ $activeTask->title }}</strong>
                                    <div class="d-flex align-items-center mt-1" style="gap: 6px;">
                                        <div class="progress flex-grow-1" style="height: 6px; border-radius: 4px; background: #E2E8F0;">
                                            <div class="progress-bar bg-primary" style="width: {{ $activeTask->progress_percentage ?? 0 }}%;"></div>
                                        </div>
                                        <small class="font-weight-bold text-dark" style="font-size: 10.5px;">{{ $activeTask->progress_percentage ?? 0 }}%</small>
                                    </div>
                                @elseif($totalTasksCount > 0)
                                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="border-radius: 6px; font-size: 11px;">
                                        {{ $completedTasksCount }}/{{ $totalTasksCount }} Tasks Done
                                    </span>
                                @else
                                    <span class="small text-muted font-weight-bold">No Active Tasks</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Team Members Currently Assigned</h5>
                                <p class="small mb-0">Employees belonging to your project team or reporting scope will appear here.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer for Pagination & Info (Populated by DataTables) -->
            <div class="orb-table-footer p-3 bg-light border-top d-flex align-items-center justify-content-between"></div>

            @if($employees->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $employees->links() }}
                </div>
            @endif
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
        if ($.fn.DataTable.isDataTable('#myTeamTable')) {
            $('#myTeamTable').DataTable().destroy();
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

        var table = $('#myTeamTable').DataTable({
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
                    title: 'OrboOne HRMS - My Team Management Summary',
                    exportOptions: exportOptionsDefault,
                    customize: function (doc) {
                        doc.pageOrientation = 'landscape';
                        doc.pageSize = 'A4';
                        doc.pageMargins = [15, 45, 15, 35];

                        doc['header'] = function(currentPage, pageCount) {
                            return {
                                margin: [15, 15, 15, 0],
                                columns: [
                                    {
                                        text: 'ORBOONE HRMS — MY TEAM OVERVIEW',
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
                        objLayout['paddingLeft'] = function(i) { return 6; };
                        objLayout['paddingRight'] = function(i) { return 6; };
                        objLayout['paddingTop'] = function(i) { return 5; };
                        objLayout['paddingBottom'] = function(i) { return 5; };
                        doc.content[1].layout = objLayout;

                        var headerRow = doc.content[1].table.body[0];
                        for (var i = 0; i < headerRow.length; i++) {
                            headerRow[i].fillColor = '#1E293B';
                            headerRow[i].color = '#FFFFFF';
                            headerRow[i].fontSize = 9;
                            headerRow[i].bold = true;
                        }

                        doc.content[1].table.widths = ['5%', '15%', '14%', '12%', '10%', '14%', '10%', '7%', '7%', '6%'];
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
                                    <p>Team Management — My Team Workspace & Status Overview</p>
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

        // Auto-filter listeners (No Apply button needed! Works live on change)
        function applyInstantFilters() {
            var teamSourceVal = $('#filter-team-source').val();
            var attVal = $('#filter-attendance-today').val();
            var searchVal = $('#filter-search-keyword').val();

            if (teamSourceVal) {
                table.column(4).search(teamSourceVal ? '^' + $.fn.dataTable.util.escapeRegex(teamSourceVal) : '', true, false);
            } else {
                table.column(4).search('');
            }

            if (attVal) {
                table.column(6).search(attVal ? '^' + $.fn.dataTable.util.escapeRegex(attVal) : '', true, false);
            } else {
                table.column(6).search('');
            }

            if (searchVal) {
                table.search(searchVal);
            } else {
                table.search('');
            }

            table.draw();
        }

        $('#filter-team-source, #filter-attendance-today').on('change', function() {
            applyInstantFilters();
        });

        $('#filter-search-keyword').on('keyup change clear', function() {
            applyInstantFilters();
        });

        // Reset Filters Button Handler
        $('#btn-reset-my-team-filters').on('click', function() {
            $('#filter-team-source').val('');
            $('#filter-attendance-today').val('');
            $('#filter-search-keyword').val('');
            table.search('').columns().search('').draw();
        });
    });
</script>
@endsection
