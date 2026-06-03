@extends('layouts.panel')

@section('page_title', 'Leave Dashboard')

@section('_head')
@include('hrms.leave.shared.style')

<style>
    :root {
        --leave-primary: var(--orb-primary, #4B00E8);
        --leave-secondary: var(--orb-secondary, #8600EE);
        --leave-bg: var(--orb-bg, #F6F7FB);
        --leave-border: var(--orb-border, #E7EAF3);
        --leave-text: var(--orb-text, #101828);
        --leave-muted: var(--orb-muted, #667085);
        --leave-soft: var(--orb-soft, #F4F2FF);
        --leave-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .leave-dashboard-wrap {
        padding-bottom: 24px;
    }

    .leave-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 22px 24px;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .26), transparent 35%),
            linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .22);
        margin-bottom: 18px;
    }

    .leave-hero::after {
        content: '';
        position: absolute;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        right: -90px;
        bottom: -120px;
        background: rgba(255, 255, 255, .14);
    }

    .leave-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .leave-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        color: rgba(255, 255, 255, .92);
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .leave-hero-title {
        font-size: 24px;
        font-weight: 900;
        margin: 0;
        letter-spacing: -.03em;
        color: #fff;
    }

    .leave-hero-subtitle {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .80);
        font-size: 13px;
        max-width: 720px;
    }

    .leave-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .leave-hero-btn {
        border: 0;
        border-radius: 14px;
        background: rgba(255, 255, 255, .96);
        color: var(--leave-primary);
        font-size: 13px;
        font-weight: 900;
        padding: 10px 14px;
        box-shadow: 0 10px 25px rgba(16, 24, 40, .12);
        transition: all .2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .leave-hero-btn:hover {
        transform: translateY(-1px);
        color: var(--leave-primary);
        text-decoration: none;
        box-shadow: 0 14px 30px rgba(16, 24, 40, .16);
    }

    .leave-mini-card {
        position: relative;
        overflow: hidden;
        min-height: 96px;
        border-radius: 18px;
        padding: 14px;
        background: #fff;
        border: 1px solid rgba(231, 234, 243, .9);
        box-shadow: var(--leave-shadow);
        transition: all .22s ease;
        margin-bottom: 16px;
    }

    .leave-mini-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 40px rgba(16, 24, 40, .10);
    }

    .leave-mini-card::after {
        content: '';
        position: absolute;
        width: 72px;
        height: 72px;
        border-radius: 50%;
        right: -26px;
        bottom: -26px;
        background: var(--card-soft);
    }

    .leave-mini-top {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .leave-mini-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: var(--card-gradient);
        box-shadow: 0 10px 20px var(--card-shadow);
        font-size: 14px;
    }

    .leave-mini-title {
        margin: 0;
        color: var(--leave-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        line-height: 1.25;
    }

    .leave-mini-value {
        position: relative;
        z-index: 2;
        margin-top: 12px;
        color: var(--leave-text);
        font-size: 26px;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
    }

    .leave-mini-caption {
        position: relative;
        z-index: 2;
        margin-top: 5px;
        font-size: 11px;
        font-weight: 700;
        color: var(--leave-muted);
    }

    .card-primary {
        --card-gradient: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        --card-shadow: rgba(75, 0, 232, .25);
        --card-soft: rgba(75, 0, 232, .08);
    }

    .card-warning {
        --card-gradient: linear-gradient(135deg, #F79009, #DC6803);
        --card-shadow: rgba(247, 144, 9, .25);
        --card-soft: rgba(247, 144, 9, .10);
    }

    .card-success {
        --card-gradient: linear-gradient(135deg, #12B76A, #039855);
        --card-shadow: rgba(18, 183, 106, .22);
        --card-soft: rgba(18, 183, 106, .10);
    }

    .card-danger {
        --card-gradient: linear-gradient(135deg, #F04438, #D92D20);
        --card-shadow: rgba(240, 68, 56, .22);
        --card-soft: rgba(240, 68, 56, .10);
    }

    .card-info {
        --card-gradient: linear-gradient(135deg, #0BA5EC, #1570EF);
        --card-shadow: rgba(11, 165, 236, .22);
        --card-soft: rgba(11, 165, 236, .10);
    }

    .card-dark {
        --card-gradient: linear-gradient(135deg, #344054, #101828);
        --card-shadow: rgba(16, 24, 40, .18);
        --card-soft: rgba(16, 24, 40, .08);
    }

    .leave-section-card {
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 24px;
        box-shadow: var(--leave-shadow);
        overflow: hidden;
    }

    .leave-section-head {
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--leave-border);
        background: linear-gradient(180deg, #fff, #FCFCFD);
    }

    .leave-section-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .leave-section-icon {
        width: 42px;
        height: 42px;
        border-radius: 15px;
        background: var(--leave-soft);
        color: var(--leave-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .leave-section-title {
        margin: 0;
        color: var(--leave-text);
        font-size: 16px;
        font-weight: 950;
        letter-spacing: -.02em;
    }

    .leave-section-subtitle {
        margin: 2px 0 0;
        color: var(--leave-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .leave-section-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .leave-light-btn {
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all .2s ease;
        text-decoration: none;
    }

    .leave-light-btn:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        text-decoration: none;
        border-color: rgba(75, 0, 232, .18);
    }

    .leave-table-shell {
        padding: 14px;
    }

    .leave-table-responsive {
        width: 100%;
        overflow-x: auto;
        border-radius: 18px;
        border: 1px solid var(--leave-border);
        background: #fff;
    }

    table.leave-premium-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--leave-text);
    }

    .leave-premium-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #F9FAFB;
        color: #475467;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 950;
        padding: 13px 14px;
        border-bottom: 1px solid var(--leave-border);
        white-space: nowrap;
    }

    .leave-premium-table tbody td {
        padding: 13px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #F2F4F7;
        font-size: 13px;
        white-space: nowrap;
    }

    .leave-premium-table tbody tr {
        transition: all .15s ease;
    }

    .leave-premium-table tbody tr:hover {
        background: #FAFAFF;
    }

    .leave-premium-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .leave-employee {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 190px;
    }

    .leave-avatar {
        width: 36px;
        height: 36px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(75, 0, 232, .12), rgba(134, 0, 238, .16));
        color: var(--leave-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 950;
        border: 1px solid rgba(75, 0, 232, .12);
        overflow: hidden !important;
    }

    .leave-avatar img {
        width: 100% !important;
        height: 100% !important;
        border-radius: inherit !important;
        object-fit: cover !important;
        display: block !important;
    }

    .leave-employee-name {
        color: var(--leave-text);
        font-size: 13px;
        font-weight: 900;
        line-height: 1.2;
    }

    .leave-employee-meta {
        color: var(--leave-muted);
        font-size: 11px;
        font-weight: 700;
        margin-top: 2px;
    }

    .leave-type-pill,
    .leave-status-pill,
    .leave-lwp-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 10px;
        font-size: 11px;
        font-weight: 950;
        white-space: nowrap;
    }

    .leave-type-pill {
        color: var(--leave-primary);
        background: var(--leave-soft);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .leave-lwp-pill {
        color: #B42318;
        background: #FEF3F2;
        border: 1px solid #FECDCA;
    }

    .leave-muted-pill {
        color: #667085;
        background: #F2F4F7;
        border: 1px solid #EAECF0;
    }

    .status-approved {
        color: #027A48;
        background: #ECFDF3;
        border: 1px solid #ABEFC6;
    }

    .status-pending {
        color: #B54708;
        background: #FFFAEB;
        border: 1px solid #FEDF89;
    }

    .status-rejected {
        color: #B42318;
        background: #FEF3F2;
        border: 1px solid #FECDCA;
    }

    .status-cancelled {
        color: #475467;
        background: #F2F4F7;
        border: 1px solid #EAECF0;
    }

    .period-box {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--leave-text);
        font-weight: 800;
    }

    .period-box i {
        color: var(--leave-muted);
    }

    .empty-leave-state {
        padding: 34px 18px;
        text-align: center;
        color: var(--leave-muted);
    }

    .empty-leave-state i {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--leave-soft);
        color: var(--leave-primary);
        font-size: 20px;
        margin-bottom: 10px;
    }

    .dataTables_wrapper {
        padding: 0;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 10px 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Hide default DataTables export button container inside table
    | We use our own custom right-corner buttons above.
    |--------------------------------------------------------------------------
    */
    .dataTables_wrapper .dt-buttons,
    .dataTables_wrapper .dataTables_buttons {
        display: none !important;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--leave-border);
        border-radius: 12px;
        padding: 7px 10px;
        outline: none;
        font-size: 12px;
        color: var(--leave-text);
        background: #fff;
    }

    @media (max-width: 767px) {
        .leave-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .leave-hero-title {
            font-size: 20px;
        }

        .leave-mini-card {
            min-height: 92px;
            padding: 12px;
            border-radius: 16px;
        }

        .leave-mini-value {
            font-size: 23px;
        }

        .leave-section-head {
            padding: 15px;
        }

        .leave-table-shell {
            padding: 10px;
        }

        .leave-section-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
@endsection

@section('_content')
<div class="leave-page leave-dashboard-wrap">
    <div class="leave-container">

        <div class="leave-hero">
            <div class="leave-hero-content">
                <div>
                    <div class="leave-hero-kicker">
                        <i class="fas fa-calendar-check"></i>
                        HRMS Leave Management
                    </div>
                    <h3 class="leave-hero-title">Leave Dashboard</h3>
                    <p class="leave-hero-subtitle">
                        Operational overview of organization-wide leave volume, approvals, LWP impact and recent leave applications.
                    </p>
                </div>

                <div class="leave-hero-actions">
                    <button type="button"
                        class="leave-hero-btn"
                        onclick="triggerLeaveExport('excel');">
                        <i class="fas fa-file-excel"></i>
                        Export Report
                    </button>
                </div>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        @php
        $colors = ['primary', 'warning', 'success', 'danger', 'info', 'dark'];
        $icons = ['fa-chart-pie', 'fa-hourglass-half', 'fa-check-circle', 'fa-times-circle', 'fa-user-clock', 'fa-calendar-day'];
        $captions = [
        'total' => 'Overall requests',
        'pending' => 'Waiting approval',
        'approved' => 'Accepted requests',
        'rejected' => 'Rejected/cancelled',
        'lwp' => 'Leave without pay',
        'today' => 'Today impact',
        ];
        $i = 0;
        @endphp

        <div class="row">
            @foreach($stats as $label => $value)
            @php
            $color = $colors[$i % count($colors)];
            $icon = $icons[$i % count($icons)];
            $captionKey = strtolower($label);
            $caption = $captions[$captionKey] ?? 'Live HRMS metric';
            $i++;
            @endphp

            <div class="col-xl-2 col-lg-4 col-md-4 col-6">
                <div class="leave-mini-card card-{{ $color }}">
                    <div class="leave-mini-top">
                        <div>
                            <p class="leave-mini-title">{{ ucwords(str_replace('_', ' ', $label)) }}</p>
                        </div>
                        <div class="leave-mini-icon">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                    </div>

                    <div class="leave-mini-value">{{ $value }}</div>
                    <div class="leave-mini-caption">{{ $caption }}</div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="leave-section-card mt-1">
            <div class="leave-section-head">
                <div class="leave-section-title-wrap">
                    <div class="leave-section-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h5 class="leave-section-title">Recent Leave Applications</h5>
                        <p class="leave-section-subtitle">
                            Latest requests with leave type, period, LWP impact and approval status.
                        </p>
                    </div>
                </div>

                <div class="leave-section-actions">
                    <button type="button"
                        class="leave-light-btn"
                        onclick="triggerLeaveExport('csv');">
                        <i class="fas fa-file-csv"></i>
                        CSV
                    </button>

                    <button type="button"
                        class="leave-light-btn"
                        onclick="triggerLeaveExport('excel');">
                        <i class="fas fa-file-excel text-success"></i>
                        Excel
                    </button>

                    <button type="button"
                        class="leave-light-btn"
                        onclick="triggerLeaveExport('pdf');">
                        <i class="fas fa-file-pdf text-danger"></i>
                        PDF
                    </button>

                    <button type="button"
                        class="leave-light-btn"
                        onclick="triggerLeaveExport('print');">
                        <i class="fas fa-print"></i>
                        Print
                    </button>
                </div>
            </div>

            <div class="leave-table-shell">
                <div class="leave-table-responsive">
                    <table class="leave-premium-table js-datatable">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Period</th>
                                <th>LWP Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($recentRequests as $request)
                            @php
                            $employeeName = optional($request->employee)->display_name ?? optional(optional($request->employee)->user)->name ?? 'Unknown Employee';
                            $employeeCode = optional($request->employee)->employee_code ?? optional($request->employee)->code ?? 'EMP';
                            $leaveType = optional($request->leaveType)->name ?? 'Leave';
                            $status = strtolower($request->status ?? 'pending');

                            $badgeClass = 'status-pending';
                            if (in_array($status, ['approved', 'accepted'])) {
                            $badgeClass = 'status-approved';
                            } elseif (in_array($status, ['rejected'])) {
                            $badgeClass = 'status-rejected';
                            } elseif (in_array($status, ['cancelled', 'canceled'])) {
                            $badgeClass = 'status-cancelled';
                            }

                            $initial = resolveEmployeeInitials($request->employee);
                            @endphp

                            <tr>
                                <td>
                                    <span class="text-muted font-weight-bold">{{ $loop->iteration }}</span>
                                </td>

                                <td>
                                    <div class="leave-employee">
                                        @php
                                            $passportPhotoUrl = resolveEmployeePassportPhoto($request->employee);
                                            $employeeInitial = $initial;
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
                                            <div class="leave-employee-name">{{ $employeeName }}</div>
                                            <div class="leave-employee-meta">{{ $employeeCode }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="leave-type-pill">
                                        <i class="fas fa-tag"></i>
                                        {{ $leaveType }}
                                    </span>
                                </td>

                                <td>
                                    <span class="period-box">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>
                                            {{ optional($request->start_date)->format('d M') }}
                                            -
                                            {{ optional($request->end_date)->format('d M Y') }}
                                        </span>
                                    </span>
                                </td>

                                <td>
                                    @if((float) $request->lwp_days > 0)
                                    <span class="leave-lwp-pill">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ number_format((float) $request->lwp_days, 1) }} Days
                                    </span>
                                    @else
                                    <span class="leave-muted-pill">
                                        <i class="fas fa-minus"></i>
                                        No LWP
                                    </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="leave-status-pill {{ $badgeClass }}">
                                        <i class="fas fa-circle" style="font-size:6px;"></i>
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-leave-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <div style="font-weight:900;color:var(--leave-text);">No recent leave applications</div>
                                        <div style="font-size:12px;margin-top:4px;">New requests will appear here once employees apply for leave.</div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

<script>
    function triggerLeaveExport(type) {
        if ($.fn.DataTable.isDataTable('.js-datatable')) {
            var table = $('.js-datatable').DataTable();

            var buttonMap = {
                csv: '.buttons-csv',
                excel: '.buttons-excel',
                pdf: '.buttons-pdf',
                print: '.buttons-print'
            };

            if (buttonMap[type]) {
                table.button(buttonMap[type]).trigger();
            }
        } else {
            alert('No records available to export.');
        }
    }
</script>
@endsection