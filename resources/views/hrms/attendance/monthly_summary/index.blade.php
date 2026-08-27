@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Monthly Attendance Summary')

@section('_head')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
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
        overflow-x: hidden !important;
    }

    .att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 16px 12px 36px;
    }

    .att-container {
        max-width: 1480px;
        margin: 0 auto;
    }

    .att-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 26px !important;
        padding: 30px;
        margin-bottom: 18px;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .20);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .att-hero:before {
        content: "";
        position: absolute;
        right: -80px;
        top: -110px;
        width: 360px;
        height: 360px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .12);
        pointer-events: none;
    }

    .att-kicker {
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        opacity: .9;
        margin-bottom: 10px;
        display: flex;
        gap: 9px;
        align-items: center;
    }

    .att-title {
        font-size: 34px;
        font-weight: 950;
        margin: 0;
        line-height: 1.1;
        color: #fff;
    }

    .att-subtitle {
        font-size: 15px;
        font-weight: 650;
        margin-top: 10px;
        opacity: .92;
        max-width: 850px;
    }

    .att-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .att-btn {
        border-radius: 14px;
        padding: 10px 18px;
        font-weight: 950;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        text-decoration: none !important;
        white-space: nowrap;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 13px;
        height: 40px;
        line-height: 1;
    }

    .att-btn-light {
        background: #fff;
        color: #101828 !important;
        border-color: var(--orb-border);
        box-shadow: 0 10px 22px rgba(16, 24, 40, .08);
    }

    .att-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, 0.15);
    }

    .att-btn-gradient {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff !important;
        box-shadow: 0 12px 24px rgba(75, 0, 232, 0.18);
        border: 0;
    }

    .att-btn-gradient:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(75, 0, 232, 0.25);
    }

    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px !important;
        box-shadow: var(--orb-shadow);
        overflow: hidden !important;
    }

    .att-section-head {
        padding: 18px 22px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #fff, #FAFBFF);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .att-section-title {
        font-size: 19px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .att-section-title i {
        color: var(--orb-primary);
    }

    .att-section-sub {
        font-size: 13px;
        color: var(--orb-muted);
        font-weight: 650;
        margin-top: 4px;
    }

    .att-head-badges {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .att-total-pill {
        border: 1px solid var(--orb-border);
        background: #F8FAFC;
        color: var(--orb-text);
        border-radius: 12px;
        padding: 9px 12px;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .att-total-pill.purple {
        border-color: #E0D7FF;
        background: #F5F2FF;
        color: var(--orb-primary);
    }

    .att-filter-panel {
        padding: 16px 22px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .att-filter-grid label {
        font-size: 10px;
        font-weight: 950;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
        display: block;
    }

    .att-filter-grid .form-control {
        height: 43px;
        border-radius: 14px;
        border: 1px solid #E4E7EC;
        font-size: 13px;
        font-weight: 750;
        padding: 0 14px;
        box-shadow: none !important;
        background: #fff;
    }

    .att-filter-grid .form-control:focus {
        border-color: var(--orb-primary);
    }

    .att-table-wrap {
        padding: 0 16px 16px;
    }

    .att-table-responsive {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
    }

    .att-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .att-table thead th {
        background: #F8FAFC !important;
        color: #344054 !important;
        font-size: 10px !important;
        font-weight: 950 !important;
        text-transform: uppercase;
        padding: 14px 12px !important;
        border-top: 1px solid #EAECF0 !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
    }

    .att-table td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 14px 12px !important;
        vertical-align: middle;
        font-size: 13px;
        color: var(--orb-text);
    }

    .att-table tbody tr {
        transition: .2s ease;
    }

    .att-table tbody tr:hover td {
        background: #FAF8FF;
    }

    .att-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--orb-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 950;
        color: var(--orb-primary);
        flex-shrink: 0;
        position: relative !important;
        overflow: hidden !important;
    }

    .att-avatar-img {
        width: 42px !important;
        height: 42px !important;
        border-radius: 14px !important;
        object-fit: cover !important;
        display: block !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
        flex-shrink: 0 !important;
    }

    .att-emp {
        display: flex;
        gap: 10px;
        align-items: center;
        min-width: 0;
    }

    .att-emp-name {
        font-weight: 900;
        color: var(--orb-text);
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .att-dept {
        font-size: 11px;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 2px;
    }

    .att-emp-code {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 700;
    }

    .att-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge-locked {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .badge-unlocked {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .att-action-btn {
        width: 34px;
        height: 34px;
        border-radius: 11px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .att-action-btn:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .dropdown-menu {
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(16, 24, 40, .12);
        padding: 8px;
    }

    .dropdown-item {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        padding: 9px 12px;
        cursor: pointer;
    }

    .dropdown-item i {
        width: 16px;
    }

    .att-empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--orb-muted);
    }

    .att-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
    }

    .dataTables_wrapper {
        width: 100% !important;
    }

    .dataTables_wrapper>.row:first-child {
        display: none !important;
    }

    @media(max-width:1300px) {
        .att-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width:768px) {
        .att-page {
            padding: 12px 8px 25px;
        }

        .att-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px;
            border-radius: 24px;
        }

        .att-title {
            font-size: 25px;
        }

        .att-hero-actions {
            width: 100%;
        }

        .att-btn {
            width: 100%;
        }

        .att-section-head {
            flex-direction: column;
        }

        .att-head-badges {
            justify-content: flex-start;
        }

        .att-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="att-page">
    <div class="att-container">

        <!-- HERO SECTION -->
        <div class="att-hero">
            <div>
                <div class="att-kicker"><i class="fas fa-layer-group"></i> HRMS &bull; PAYROLL</div>
                <h3 class="att-title">Monthly Attendance Summary</h3>
                <div class="att-subtitle">Payroll-ready monthly employee attendance summaries with lock controls.</div>
            </div>
            <div class="att-hero-actions">
                <a href="{{ route('attendances.index') }}" class="att-btn att-btn-light">
                    <i class="fas fa-chart-line"></i> Attendance Dashboard
                </a>
                <a href="{{ route('attendances.record') }}" class="att-btn att-btn-light">
                    <i class="fas fa-list"></i> Attendance Records
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif
        @if(session('status'))
        <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('status') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3">{{ session('error') }}</div>
        @endif

        <!-- MAIN CARD -->
        <div class="att-card">
            
            <!-- SECTION HEADER -->
            <div class="att-section-head">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-calendar-check"></i> Monthly Employee Summaries</h5>
                    <div class="att-section-sub">Validate and lock employee summaries for payroll processing.</div>
                </div>
                <div class="att-head-badges align-items-center">
                    <span class="att-total-pill purple">
                        <i class="fas fa-database"></i> Total: {{ $rows->total() }}
                    </span>
                    
                    <a href="{{ route('hrms.attendance.monthly_summary.export-excel', request()->query()) }}" class="att-btn att-btn-light" style="padding: 9px 14px; font-size: 12px; height: 36px; border-radius: 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-file-csv text-success"></i> Export CSV
                    </a>

                    <form method="POST" action="{{ route('hrms.attendance.monthly_summary.generate') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="month" value="{{ request('month', now()->month) }}">
                        <input type="hidden" name="year" value="{{ request('year', now()->year) }}">
                        <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                        <button type="submit" class="att-btn att-btn-gradient" style="padding: 9px 14px; font-size: 12px; height: 36px; border-radius: 12px;">
                            <i class="fas fa-sync-alt"></i> Generate Summary
                        </button>
                    </form>

                    <a href="{{ route('hrms.attendance.monthly_summary.index') }}" class="att-btn att-btn-light" style="padding: 9px 14px; font-size: 12px; height: 36px; border-radius: 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-undo"></i> Reset Filters
                    </a>
                </div>
            </div>

            <!-- FILTERS PANEL -->
            <div class="att-filter-panel">
                <form method="GET" action="{{ route('hrms.attendance.monthly_summary.index') }}" id="filterForm">
                    <div class="att-filter-grid">
                        
                        <!-- Employee Filter -->
                        <div>
                            <label>Employee</label>
                            <select name="employee_id" class="form-control js-auto-filter">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->display_name }} ({{ $emp->employee_code }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Month Filter -->
                        <div>
                            <label>Month</label>
                            <select name="month" class="form-control js-auto-filter">
                                <option value="">All Months</option>
                                @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <label>Year</label>
                            <input type="number" name="year" class="form-control js-auto-filter" 
                                   value="{{ request('year', now()->year) }}" placeholder="Enter Year">
                        </div>

                        <!-- Locked Filter -->
                        <div>
                            <label>Locked Status</label>
                            <select name="locked" class="form-control js-auto-filter">
                                <option value="">All Statuses</option>
                                <option value="1" {{ request('locked') === '1' ? 'selected' : '' }}>Locked</option>
                                <option value="0" {{ request('locked') === '0' ? 'selected' : '' }}>Unlocked</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <!-- DATA TABLE AREA -->
            @if($rows->count() > 0)
            <div class="att-table-wrap">
                <div class="att-table-responsive">
                    <table class="att-table table" id="summaryDataTable">
                        <thead>
                            <tr>
                                <th style="width: 250px;">Employee</th>
                                <th style="text-align: center;">Month</th>
                                <th style="text-align: center;">Year</th>
                                <th style="text-align: center;">Present</th>
                                <th style="text-align: center;">Paid Leave</th>
                                <th style="text-align: center;">LWP</th>
                                <th style="text-align: center;">Half Day</th>
                                <th style="text-align: center;">Late Marks</th>
                                <th style="text-align: center;">Early Out</th>
                                <th style="text-align: center;">Missed Punch</th>
                                <th style="text-align: center;">Unresolved</th>
                                <th style="text-align: center;">Total Work Hours</th>
                                <th style="text-align: center; font-weight: 950; color: var(--orb-primary);">Payable Days</th>
                                <th style="text-align: center;">Locked</th>
                                <th style="width: 80px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            @php
                                $photoUrl = resolveEmployeePassportPhoto($row->employee_id);
                                $initials = resolveEmployeeInitials($row->employee_display_name);
                            @endphp
                            <tr>
                                <td>
                                    <div class="att-emp">
                                        <div class="att-avatar">
                                            @if($photoUrl)
                                            <img src="{{ $photoUrl }}" alt="{{ $row->employee_display_name }}" class="att-avatar-img">
                                            @else
                                            {{ $initials }}
                                            @endif
                                        </div>
                                        <div style="min-width: 0;">
                                            <div class="att-emp-name">{{ $row->employee_display_name }}</div>
                                            <div class="att-dept">
                                                <span class="att-emp-code">{{ $row->employee_code }}</span> 
                                                @if(!empty($row->department_name))
                                                &bull; {{ $row->department_name }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center; font-weight: 800;">
                                    {{ \Carbon\Carbon::create()->month($row->month)->format('F') }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $row->year }}
                                </td>
                                <td style="text-align: center; font-weight: 800;">
                                    {{ $row->present_days }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $row->paid_leave_days }}
                                </td>
                                <td style="text-align: center; color: #dc2626;">
                                    {{ $row->lwp_days }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $row->half_days }} <span class="text-muted">(0.5 each)</span>
                                </td>
                                <td style="text-align: center;">
                                    {{ $row->late_count }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $row->early_out_count }}
                                </td>
                                <td style="text-align: center; color: #d97706;">
                                    {{ $row->missed_punch_count }}
                                </td>
                                <td style="text-align: center; color: {{ (int) ($row->unresolved_count ?? 0) > 0 ? '#b42318' : '#027a48' }}; font-weight: 800;">
                                    {{ (int) ($row->unresolved_count ?? 0) }}
                                </td>
                                <td style="text-align: center; font-weight: 750;">
                                    {{ round($row->total_work_minutes / 60, 1) }} hrs
                                </td>
                                <td style="text-align: center; font-weight: 950; color: var(--orb-primary); background: #FAF8FF;">
                                    {{ $row->payable_days }}
                                </td>
                                <td style="text-align: center;">
                                    @if($row->is_locked)
                                    <span class="att-badge badge-locked"><i class="fas fa-lock mr-1"></i> Locked</span>
                                    @else
                                    <span class="att-badge badge-unlocked"><i class="fas fa-unlock mr-1"></i> Unlocked</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div class="dropdown">
                                        <button class="att-action-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(!$row->is_locked)
                                            <form method="POST" action="{{ route('hrms.attendance.monthly_summary.lock', $row->id) }}" onsubmit="return confirm('Lock this summary? Regularization and adjustments will be frozen for this period.')">
                                                @csrf
                                                <button class="dropdown-item text-success" type="submit">
                                                    <i class="fas fa-lock text-success mr-2"></i> Lock Summary
                                                </button>
                                            </form>
                                            @else
                                            <form method="POST" action="{{ route('hrms.attendance.monthly_summary.unlock', $row->id) }}" onsubmit="return confirm('Unlock this summary? Regularizations and manual punch adjustments will be enabled.')">
                                                @csrf
                                                <button class="dropdown-item text-danger" type="submit">
                                                    <i class="fas fa-unlock text-danger mr-2"></i> Unlock Summary
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION LINKS -->
                @if(method_exists($rows, 'links'))
                <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                    <div class="text-muted small">
                        Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} entries
                    </div>
                    <div>
                        {{ $rows->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif

            </div>
            @else
            <!-- PROFESSIONAL EMPTY STATE -->
            <div class="att-empty-state">
                <div class="att-empty-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h4>No monthly attendance summaries found for this period.</h4>
                <p class="text-muted small max-width-600 mx-auto">
                    There are no summaries generated for the selected employee, month, and year. Click the button below to generate a payroll-ready summary.
                </p>
                <div class="mt-4">
                    <form method="POST" action="{{ route('hrms.attendance.monthly_summary.generate') }}">
                        @csrf
                        <input type="hidden" name="month" value="{{ request('month', now()->month) }}">
                        <input type="hidden" name="year" value="{{ request('year', now()->year) }}">
                        <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                        <button type="submit" class="att-btn att-btn-gradient">
                            <i class="fas fa-magic"></i> Generate Monthly Summary
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>

    </div>
</div>

@section('_script')
<!-- DataTables JS Libraries -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    // Auto submit form on filter changes
    $('.js-auto-filter').on('change keyup', function (e) {
        if (e.type === 'keyup') {
            clearTimeout(this.interval);
            this.interval = setTimeout(function () {
                $('#filterForm').submit();
            }, 600);
        } else {
            $('#filterForm').submit();
        }
    });

    // Premium DataTable styling
    if ($.fn.DataTable) {
        $('#summaryDataTable').DataTable({
            paging: false, // handled by Laravel Pagination natively
            searching: false,
            info: false,
            lengthChange: false,
            ordering: true,
            order: [],
            columnDefs: [
                { orderable: false, targets: [0, 13, 14] }
            ]
        });
    }
});
</script>
@endsection
@endsection
