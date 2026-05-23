@extends('layouts.panel')

@section('page_title', 'Comp Off Management')

@section('_head')
@include('hrms.leave.shared.style')

<style>
    :root {
        --leave-primary: var(--orb-primary, #4B00E8);
        --leave-secondary: var(--orb-secondary, #8600EE);
        --leave-border: var(--orb-border, #E7EAF3);
        --leave-text: var(--orb-text, #101828);
        --leave-muted: var(--orb-muted, #667085);
        --leave-soft: var(--orb-soft, #F4F2FF);
        --leave-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .leave-page-wrap {
        padding-bottom: 24px;
    }

    .leave-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 22px 24px;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, .26), transparent 35%),
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
        font-size: 26px;
        font-weight: 900;
        margin: 0;
        color: #fff;
        letter-spacing: -.03em;
    }

    .leave-hero-subtitle {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
        max-width: 780px;
        line-height: 1.6;
    }

    .leave-expire-btn {
        border: 1px solid rgba(255, 255, 255, .28) !important;
        border-radius: 14px;
        background: rgba(255, 255, 255, .16) !important;
        color: #fff !important;
        font-size: 13px;
        font-weight: 900;
        height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
    }

    .leave-expire-btn i {
        color: #fff !important;
    }

    .leave-expire-btn:hover {
        transform: translateY(-1px);
        background: rgba(255, 255, 255, .22) !important;
    }

    .leave-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .summary-card {
        position: relative;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 20px;
        box-shadow: var(--leave-shadow);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 92px;
        transition: all .2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(16, 24, 40, .10);
    }

    .summary-card::after {
        content: '';
        position: absolute;
        width: 74px;
        height: 74px;
        border-radius: 50%;
        right: -28px;
        bottom: -28px;
        background: var(--summary-soft);
    }

    .summary-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: var(--summary-gradient);
        box-shadow: 0 10px 22px var(--summary-shadow);
        position: relative;
        z-index: 2;
    }

    .summary-info {
        position: relative;
        z-index: 2;
    }

    .summary-info h4 {
        margin: 0;
        font-size: 26px;
        font-weight: 950;
        color: var(--leave-text);
        line-height: 1;
    }

    .summary-info p {
        margin: 5px 0 0;
        color: var(--leave-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .summary-primary {
        --summary-gradient: linear-gradient(135deg, #4B00E8, #8600EE);
        --summary-shadow: rgba(75, 0, 232, .22);
        --summary-soft: rgba(75, 0, 232, .08);
    }

    .summary-success {
        --summary-gradient: linear-gradient(135deg, #12B76A, #039855);
        --summary-shadow: rgba(18, 183, 106, .22);
        --summary-soft: rgba(18, 183, 106, .09);
    }

    .summary-danger {
        --summary-gradient: linear-gradient(135deg, #F04438, #D92D20);
        --summary-shadow: rgba(240, 68, 56, .22);
        --summary-soft: rgba(240, 68, 56, .09);
    }

    .leave-card {
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 24px;
        box-shadow: var(--leave-shadow);
        overflow: hidden;
        margin-bottom: 18px;
    }

    .leave-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--leave-border);
        background: linear-gradient(180deg, #fff, #FCFCFD);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .leave-card-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .leave-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--leave-soft);
        color: var(--leave-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .leave-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        color: var(--leave-text);
    }

    .leave-card-subtitle {
        margin: 2px 0 0;
        font-size: 12px;
        color: var(--leave-muted);
        font-weight: 600;
    }

    .leave-action-wrap {
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
    }

    .leave-light-btn:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .pending-approval-grid {
        padding: 16px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .approval-card {
        border: 1px solid var(--leave-border);
        border-radius: 18px;
        background: #fff;
        padding: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 8px 18px rgba(16, 24, 40, .04);
    }

    .approval-employee {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .approval-avatar,
    .employee-avatar {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(75, 0, 232, .12), rgba(134, 0, 238, .16));
        color: var(--leave-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .approval-name,
    .employee-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .approval-meta,
    .employee-meta {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 2px;
        font-weight: 700;
    }

    .approve-btn {
        border: 1px solid #ABEFC6;
        background: #ECFDF3;
        color: #027A48;
        border-radius: 12px;
        height: 34px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .2s ease;
    }

    .approve-btn:hover {
        background: #D1FADF;
        color: #027A48;
    }

    .leave-table-wrap {
        padding: 14px;
    }

    .leave-table-responsive {
        overflow-x: auto;
        border-radius: 18px;
        border: 1px solid var(--leave-border);
    }

    .leave-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--leave-text);
    }

    .leave-table thead th {
        background: #F9FAFB;
        color: #475467;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 950;
        padding: 14px;
        border-bottom: 1px solid var(--leave-border);
        white-space: nowrap;
    }

    .leave-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #F2F4F7;
        vertical-align: middle;
        font-size: 13px;
        white-space: nowrap;
    }

    .leave-table tbody tr {
        transition: all .15s ease;
    }

    .leave-table tbody tr:hover {
        background: #FAFAFF;
    }

    .leave-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 200px;
    }

    .date-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 850;
        color: var(--leave-text);
    }

    .date-pill i {
        color: var(--leave-muted);
    }

    .earned-used {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 950;
    }

    .earned-days {
        color: #027A48;
    }

    .used-days {
        color: #B42318;
    }

    .leave-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .pill-available {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .pill-used {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .pill-expired {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .pill-muted {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .dataTables_wrapper .dt-buttons {
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

    .empty-state {
        padding: 42px 18px;
        text-align: center;
    }

    .empty-state i {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--leave-soft);
        color: var(--leave-primary);
        font-size: 20px;
        margin-bottom: 12px;
    }

    @media(max-width:991px) {

        .leave-summary-grid,
        .pending-approval-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:767px) {
        .leave-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .leave-hero-title {
            font-size: 22px;
        }

        .leave-expire-btn {
            width: 100%;
        }

        .leave-card-head {
            padding: 16px;
        }

        .leave-action-wrap {
            width: 100%;
        }

        .leave-light-btn {
            flex: 1;
            justify-content: center;
        }

        .approval-card {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endsection

@section('_content')
<div class="leave-page-wrap">

    <div class="leave-hero">
        <div class="leave-hero-content">
            <div>
                <div class="leave-hero-kicker">
                    <i class="fas fa-calendar-plus"></i>
                    HRMS Comp Off
                </div>

                <h1 class="leave-hero-title">Comp Off Management</h1>

                <div class="leave-hero-subtitle">
                    Manage compensatory-off balances generated from holiday/weekoff work, approvals, usage and expiry tracking.
                </div>
            </div>

            <form method="POST"
                action="{{ route('hrms.comp_offs.expire') }}"
                onsubmit="return confirm('Expire all due comp offs?');">
                @csrf

                <button type="submit" class="leave-expire-btn">
                    <i class="fas fa-hourglass-end"></i>
                    Expire Due
                </button>
            </form>
        </div>
    </div>

    @include('hrms.leave.shared.flash')

    <div class="leave-summary-grid">
        <div class="summary-card summary-primary">
            <div class="summary-icon">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <div class="summary-info">
                <h4>{{ number_format((float) $compOffs->where('status', 'available')->sum('earned_days'), 1) }}</h4>
                <p>Total Available</p>
            </div>
        </div>

        <div class="summary-card summary-success">
            <div class="summary-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="summary-info">
                <h4>{{ number_format((float) ($compOffs->where('status', 'used')->sum('used_days') ?? 0), 1) }}</h4>
                <p>Total Used</p>
            </div>
        </div>

        <div class="summary-card summary-danger">
            <div class="summary-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="summary-info">
                <h4>{{ number_format((float) $compOffs->where('status', 'expired')->sum('earned_days'), 1) }}</h4>
                <p>Total Expired</p>
            </div>
        </div>
    </div>

    @if($holidayWorkRequests->count() > 0)
    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Pending Holiday Work Approvals</h5>
                    <div class="leave-card-subtitle">
                        Approve eligible holiday/weekoff work requests to generate comp-off credit.
                    </div>
                </div>
            </div>
        </div>

        <div class="pending-approval-grid">
            @foreach($holidayWorkRequests as $request)
            @php
            $employeeName = optional($request->employee)->display_name ?? optional(optional($request->employee)->user)->name ?? 'Unknown Employee';
            $initial = strtoupper(substr(trim($employeeName), 0, 1));
            @endphp

            <div class="approval-card">
                <div class="approval-employee">
                    <div class="approval-avatar">{{ $initial }}</div>
                    <div>
                        <div class="approval-name">{{ $employeeName }}</div>
                        <div class="approval-meta">
                            {{ optional($request->worked_date)->format('d M Y') }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('hrms.comp_offs.holiday_work.approve', $request->id) }}">
                    @csrf
                    <button class="approve-btn" type="submit">
                        <i class="fas fa-check"></i>
                        Approve
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Comp Off Records</h5>
                    <div class="leave-card-subtitle">
                        Track employee earned, used, expired and available comp-off balances.
                    </div>
                </div>
            </div>

            <div class="leave-action-wrap">
                <button type="button" class="leave-light-btn" onclick="triggerLeaveExport('csv');">
                    <i class="fas fa-file-csv"></i> CSV
                </button>
                <button type="button" class="leave-light-btn" onclick="triggerLeaveExport('excel');">
                    <i class="fas fa-file-excel text-success"></i> Excel
                </button>
                <button type="button" class="leave-light-btn" onclick="triggerLeaveExport('pdf');">
                    <i class="fas fa-file-pdf text-danger"></i> PDF
                </button>
                <button type="button" class="leave-light-btn" onclick="triggerLeaveExport('print');">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <div class="leave-table-wrap">
            <div class="leave-table-responsive">
                <table class="leave-table js-comp-off-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th>Worked Date</th>
                            <th>Earned / Used</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($compOffs as $compOff)
                        @php
                        $employeeName = optional($compOff->employee)->display_name ?? optional(optional($compOff->employee)->user)->name ?? 'Unknown Employee';
                        $employeeCode = optional($compOff->employee)->employee_code ?? optional($compOff->employee)->code ?? 'EMP';
                        $initial = strtoupper(substr(trim($employeeName), 0, 1));

                        $status = strtolower($compOff->status ?? 'available');
                        $statusClass = 'pill-muted';
                        if ($status === 'available') $statusClass = 'pill-available';
                        if ($status === 'used') $statusClass = 'pill-used';
                        if ($status === 'expired') $statusClass = 'pill-expired';
                        @endphp

                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>

                            <td>
                                <div class="employee-cell">
                                    <div class="employee-avatar">{{ $initial }}</div>
                                    <div>
                                        <div class="employee-name">{{ $employeeName }}</div>
                                        <div class="employee-meta">{{ $employeeCode }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="date-pill">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ optional($compOff->worked_date)->format('d M Y') ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <div class="earned-used">
                                    <span class="earned-days">+{{ number_format((float) $compOff->earned_days, 1) }}</span>
                                    <span class="text-muted">/</span>
                                    <span class="used-days">-{{ number_format((float) ($compOff->used_days ?? 0), 1) }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="date-pill">
                                    <i class="fas fa-clock text-warning"></i>
                                    {{ optional($compOff->expiry_date)->format('d M Y') ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="leave-pill {{ $statusClass }}">
                                    <i class="fas fa-circle" style="font-size:6px;"></i>
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.DataTable) {
            $('.js-comp-off-datatable').DataTable({
                pageLength: 25,
                responsive: true,
                language: {
                    emptyTable: `<div class="empty-state">
                                    <i class="fas fa-calendar-plus"></i>
                                    <div style="font-weight:900;color:var(--leave-text);">
                                        No Comp Off Records Found
                                    </div>
                                    <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                        Approved holiday/weekoff work will generate comp-off records here.
                                    </div>
                                </div>`,
                    loadingRecords: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                },
                dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'excel', className: 'btn btn-light border shadow-sm' },
                    { extend: 'csv', className: 'btn btn-light border shadow-sm' },
                    { extend: 'pdf', className: 'btn btn-light border shadow-sm' },
                    { extend: 'print', className: 'btn btn-light border shadow-sm' }
                ],
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded justify-content-end mb-0');
                }
            });
        }
    });

    function triggerLeaveExport(type) {
        let table = $('.js-comp-off-datatable').DataTable();

        let buttons = {
            csv: '.buttons-csv',
            excel: '.buttons-excel',
            pdf: '.buttons-pdf',
            print: '.buttons-print'
        };

        if (buttons[type]) {
            table.button(buttons[type]).trigger();
        }
    }
</script>
@endsection