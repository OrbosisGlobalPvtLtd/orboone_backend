@extends('layouts.panel', ['active' => 'team_leave'])

@section('page_title', 'Team Leave')

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

.rep-metric-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 20px;
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
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
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
                <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-plane-departure mr-2"></i>Team Leave Management</h3>
                <p class="mb-0 opacity-90 small">Track team leave requests, approvals, balances, and availability in real-time.</p>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row mb-4">
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(75, 0, 232, 0.08); color: #4B00E8;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Total Team</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 24px;">{{ $totalTeamCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(245, 158, 11, 0.1); color: #D97706;">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">On Leave Today</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 24px;">{{ $onLeaveTodayCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Approved Requests</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 24px;">{{ $approvedLeaveCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Pending Approvals</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark" style="font-size: 24px;">{{ $pendingLeaveCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Team Leave Status Alert -->
        <div class="rep-card p-4 mb-4">
            <h6 class="font-weight-bold text-dark mb-3" style="font-size: 14px;"><i class="fas fa-calendar-day text-primary mr-2"></i>Today's Team Leave Status ({{ \Carbon\Carbon::parse(date('Y-m-d'))->format('d M Y') }})</h6>
            <div class="d-flex flex-wrap gap-2" style="gap: 8px;">
                @forelse($todayLeaves as $tl)
                    <span class="badge px-3 py-2 font-weight-bold" style="border-radius: 12px; background-color: #FEF3C7; color: #B45309; border: 1px solid #FCD34D; font-size: 12.5px; display: inline-flex; align-items: center;">
                        <i class="fas fa-user-clock mr-2 text-warning"></i> {{ $tl->display_name }} &bull; {{ $tl->leave_type_name ?? 'Leave' }}
                    </span>
                @empty
                    <div class="p-2.5 px-3 bg-light border rounded-lg text-muted small font-weight-bold d-inline-flex align-items-center" style="border-radius: 10px; font-size: 12.5px;">
                        <i class="fas fa-check-circle text-success mr-2" style="font-size: 14px;"></i> No team members are on leave today. All active members are available.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Leave Requests Table Card -->
        <div class="rep-card">
            <!-- Card Header Title (Compact height) -->
            <div class="d-flex align-items-center justify-content-between border-bottom bg-white flex-wrap" style="padding: 12px 20px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 9px; background: #EEF2FF; color: #4F46E5; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <div>
                        <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">Team Leave Applications</h5>
                    </div>
                </div>
            </div>

            <!-- Embedded Attached Filters inside Card (Live auto-filter without Apply button! Reset button on the right) -->
            <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="form-inline flex-wrap gap-2" style="gap: 12px;">
                    <select id="filter-team-member" class="form-control" style="border-radius: 10px; font-size: 13px; height: 38px; min-width: 220px;">
                        <option value="">-- All Team Members --</option>
                        @foreach($teamEmployees as $emp)
                            <option value="{{ $emp->display_name }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->display_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>

                    <select id="filter-leave-status" class="form-control" style="border-radius: 10px; font-size: 13px; height: 38px; min-width: 180px;">
                        <option value="">-- All Statuses --</option>
                        <option value="APPROVED" {{ strtolower(request('status')) == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="PENDING" {{ strtolower(request('status')) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="REJECTED" {{ strtolower(request('status')) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>

                    <input type="text" id="filter-search-keyword" class="form-control" placeholder="Search employee, leave type..." style="border-radius: 10px; font-size: 13px; height: 38px; min-width: 240px;">
                </div>

                <!-- Reset Button on the right side of the filter bar -->
                <button type="button" class="btn btn-light border font-weight-bold" id="btn-reset-team-leave-filters" style="border-radius: 10px; font-size: 13px; color: #475467; height: 38px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-undo text-muted" style="font-size: 11px;"></i> Reset Filters
                </button>
            </div>

            <!-- Toolbar for Entries & Export Buttons -->
            <div class="orb-table-toolbar d-flex align-items-center justify-content-between p-3 border-bottom">
                <div class="toolbar-left"></div>
                <div class="toolbar-right d-flex align-items-center"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="teamLeaveTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 55px;">S.No.</th>
                            <th class="py-3 px-4">Employee Name</th>
                            <th class="py-3">Department & Designation</th>
                            <th class="py-3">Leave Type</th>
                            <th class="py-3 text-center">Dates</th>
                            <th class="py-3 text-center">Duration</th>
                            <th class="py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $lr)
                            @php
                                $ltName = $lr->leave_type_name ?? 'Leave';
                                $ltLower = strtolower($ltName);
                                $ltStyle = match(true) {
                                    str_contains($ltLower, 'sick') => 'background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5;',
                                    str_contains($ltLower, 'casual') => 'background: #ECFDF5; color: #065F46; border: 1px solid #6EE7B7;',
                                    str_contains($ltLower, 'comp') => 'background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;',
                                    str_contains($ltLower, 'earned') || str_contains($ltLower, 'privilege') => 'background: #EFF6FF; color: #1E40AF; border: 1px solid #93C5FD;',
                                    default => 'background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;'
                                };

                                $stLower = strtolower(trim($lr->status ?? 'approved'));
                                $stBadgeStyle = match(true) {
                                    $stLower === 'approved' => 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;',
                                    $stLower === 'pending' => 'background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;',
                                    $stLower === 'rejected' || $stLower === 'cancelled' => 'background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5;',
                                    default => 'background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;'
                                };
                                $stIcon = match(true) {
                                    $stLower === 'approved' => 'fas fa-check-circle',
                                    $stLower === 'pending' => 'fas fa-hourglass-half',
                                    default => 'fas fa-times-circle'
                                };

                                $startDateFormatted = \Carbon\Carbon::parse($lr->start_date)->format('d M Y');
                                $endDateFormatted = \Carbon\Carbon::parse($lr->end_date)->format('d M Y');
                                $isSingleDay = ($lr->start_date === $lr->end_date);
                                $datesExportText = $isSingleDay ? $startDateFormatted : ($startDateFormatted . ' to ' . $endDateFormatted);

                                $daysVal = (float)($lr->days ?? 1);
                                $daysText = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . \Illuminate\Support\Str::plural('Day', $daysVal);
                                $empExportText = ($lr->display_name ?? 'Employee') . ' (' . ($lr->employee_code ?? 'N/A') . ')';
                            @endphp
                        <tr>
                            <!-- S.No. -->
                            <td class="py-3 px-3 align-middle text-center font-weight-bold text-muted" style="font-size: 12.5px;" data-export="{{ $loop->iteration }}">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Employee Name -->
                            <td class="py-3 px-4 align-middle" data-export="{{ $empExportText }}">
                                <div>
                                    <strong class="text-dark font-weight-bold d-block" style="line-height: 1.25; font-size: 13.5px;">{{ $lr->display_name }}</strong>
                                    <small class="text-muted" style="font-size: 11px; font-weight: 600;">{{ $lr->employee_code }}</small>
                                </div>
                            </td>

                            <!-- Department & Designation -->
                            <td class="py-3 align-middle" data-export="{{ $lr->department_name ?? 'General' }} - {{ $lr->designation_name ?? 'Staff' }}">
                                <div>
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 12.5px; line-height: 1.2;">
                                        <i class="fas fa-building text-muted mr-1" style="font-size: 11px;"></i> {{ $lr->department_name ?? 'General' }}
                                    </span>
                                    <small class="text-muted" style="font-size: 11px; font-weight: 600;">{{ $lr->designation_name ?? 'Staff Member' }}</small>
                                </div>
                            </td>

                            <!-- Leave Type -->
                            <td class="py-3 align-middle" data-export="{{ $ltName }}">
                                <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 8px; font-size: 11.5px; {{ $ltStyle }}">
                                    <i class="fas fa-umbrella-beach mr-1" style="font-size: 10.5px;"></i> {{ $ltName }}
                                </span>
                            </td>

                            <!-- Dates -->
                            <td class="py-3 align-middle text-center" data-export="{{ $datesExportText }}">
                                <div class="d-inline-flex align-items-center bg-light px-2.5 py-1" style="border-radius: 8px; border: 1px solid #E2E8F0; font-size: 12px; font-weight: 600; color: #1E293B;">
                                    <i class="far fa-calendar-alt text-primary" style="font-size: 11px; margin-right: 6px;"></i>
                                    @if($isSingleDay)
                                        <span>{{ $startDateFormatted }}</span>
                                    @else
                                        <span>{{ $startDateFormatted }}</span>
                                        <i class="fas fa-arrow-right text-muted" style="font-size: 9.5px; margin: 0 6px;"></i>
                                        <span>{{ $endDateFormatted }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Duration -->
                            <td class="py-3 align-middle text-center" data-export="{{ $daysText }}">
                                <span class="badge badge-light border font-weight-bold px-2.5 py-1.5 text-dark" style="border-radius: 8px; font-size: 11.5px;">
                                    <i class="fas fa-clock text-warning mr-1"></i> {{ $daysText }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-3 align-middle text-center" data-export="{{ strtoupper($lr->status ?? 'Approved') }}">
                                <span class="badge font-weight-bold text-uppercase px-3 py-1" style="border-radius: 8px; font-size: 10.5px; letter-spacing: 0.04em; {{ $stBadgeStyle }}">
                                    <i class="{{ $stIcon }} mr-1"></i> {{ ucfirst($stLower) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-minus fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Leave Requests Found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer for Pagination & Info (Populated by DataTables) -->
            <div class="orb-table-footer p-3 bg-light border-top d-flex align-items-center justify-content-between"></div>

            @if($leaveRequests->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $leaveRequests->links() }}
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
        if ($.fn.DataTable.isDataTable('#teamLeaveTable')) {
            $('#teamLeaveTable').DataTable().destroy();
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

        var table = $('#teamLeaveTable').DataTable({
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
                    title: 'OrboOne HRMS - Team Management Leave Summary',
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
                                        text: 'ORBOONE HRMS — TEAM LEAVE SUMMARY',
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

                        doc.content[1].table.widths = ['6%', '22%', '20%', '16%', '20%', '8%', '8%'];
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
                                    <p>Team Management — Leave Applications & Status Summary</p>
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
                emptyTable: 'No leave requests found.',
                zeroRecords: 'No matching leave requests found.',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ leave requests',
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
            var empVal = $('#filter-team-member').val();
            var statusVal = $('#filter-leave-status').val();
            var searchVal = $('#filter-search-keyword').val();

            if (empVal) {
                table.column(1).search(empVal ? '^' + $.fn.dataTable.util.escapeRegex(empVal) : '', true, false);
            } else {
                table.column(1).search('');
            }

            if (statusVal) {
                table.column(6).search(statusVal ? '^' + $.fn.dataTable.util.escapeRegex(statusVal) : '', true, false);
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

        $('#filter-team-member, #filter-leave-status').on('change', function() {
            applyInstantFilters();
        });

        $('#filter-search-keyword').on('keyup change clear', function() {
            applyInstantFilters();
        });

        // Reset Filters Button Handler
        $('#btn-reset-team-leave-filters').on('click', function() {
            $('#filter-team-member').val('');
            $('#filter-leave-status').val('');
            $('#filter-search-keyword').val('');
            table.search('').columns().search('').draw();
        });

        // Trigger initial filter if present
        if ($('#filter-team-member').val() || $('#filter-leave-status').val()) {
            applyInstantFilters();
        }
    });
</script>
@endsection
