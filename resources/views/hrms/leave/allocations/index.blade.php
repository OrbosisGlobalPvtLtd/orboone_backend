@extends('layouts.panel')

@section('page_title', 'Leave Allocation')

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
        letter-spacing: -.03em;
        color: #fff;
    }

    .leave-hero-subtitle {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
        max-width: 780px;
        line-height: 1.6;
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

    .leave-card-body {
        padding: 18px;
    }

    .leave-action-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 14px;
    }

    .leave-action-box {
        border: 1px solid var(--leave-border);
        border-radius: 18px;
        padding: 16px;
        background: #FCFCFD;
    }

    .leave-action-title {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        margin-bottom: 4px;
    }

    .leave-action-subtitle {
        font-size: 12px;
        font-weight: 600;
        color: var(--leave-muted);
        margin-bottom: 14px;
    }

    .leave-form-row {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .leave-control {
        height: 42px;
        border-radius: 14px;
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        padding: 0 14px;
        font-size: 13px;
        font-weight: 700;
        outline: none;
        transition: all .2s ease;
    }

    .leave-control:focus {
        border-color: rgba(75, 0, 232, .25);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
    }

    .leave-year-input {
        width: 115px;
    }

    .leave-employee-select {
        min-width: 240px;
        flex: 1;
    }

    .leave-btn {
        border: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 12px 24px rgba(75, 0, 232, .18);
        transition: all .2s ease;
        white-space: nowrap;
    }

    .leave-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(75, 0, 232, .24);
        color: #fff;
    }

    .leave-btn-light {
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        border-radius: 14px;
        height: 42px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .leave-btn-light:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border-color: rgba(75, 0, 232, .18);
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

    .leave-employee {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 210px;
    }

    .leave-avatar {
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
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .leave-employee-meta {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 2px;
        font-weight: 700;
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

    .pill-stage {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .pill-policy {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .pill-lwp {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .leave-metric {
        font-weight: 950;
        color: var(--leave-text);
        font-size: 14px;
    }

    .metric-muted {
        color: var(--leave-muted);
        font-size: 11px;
        font-weight: 700;
        margin-top: 2px;
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
        .leave-action-grid {
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

        .leave-card-head {
            padding: 16px;
        }

        .leave-card-body {
            padding: 14px;
        }

        .leave-form-row {
            align-items: stretch;
        }

        .leave-control,
        .leave-btn,
        .leave-btn-light {
            width: 100%;
        }

        .leave-year-input,
        .leave-employee-select {
            width: 100%;
            min-width: 100%;
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
                    <i class="fas fa-coins"></i>
                    HRMS Leave Allocation
                </div>
                <h1 class="leave-hero-title">Leave Allocation</h1>
                <div class="leave-hero-subtitle">
                    Generate and review policy-driven yearly leave allocations with used, remaining and LWP tracking.
                </div>
            </div>
        </div>
    </div>

    @include('hrms.leave.shared.flash')

    @if($canManageAllocations ?? false)
    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-magic"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Generate Leave Allocation</h5>
                    <div class="leave-card-subtitle">
                        Generate allocation for full year or for a selected employee only.
                    </div>
                </div>
            </div>
        </div>

        <div class="leave-card-body">
            <div class="leave-action-grid">

                <div class="leave-action-box">
                    <div class="leave-action-title">Generate Yearly Allocation</div>
                    <div class="leave-action-subtitle">Process leave allocation for all eligible employees.</div>

                    <form method="POST" action="{{ route('leave-allocations.process') }}">
                        @csrf
                        <div class="leave-form-row">
                            <input name="year"
                                class="leave-control leave-year-input"
                                value="{{ $year }}"
                                placeholder="Year">

                            <button class="leave-btn" type="submit">
                                <i class="fas fa-play"></i>
                                Generate Year
                            </button>
                        </div>
                    </form>
                </div>

                <div class="leave-action-box">
                    <div class="leave-action-title">Generate Single Employee</div>
                    <div class="leave-action-subtitle">Run allocation for one employee without affecting others.</div>

                    <form method="POST" action="{{ route('leave-allocations.single') }}">
                        @csrf
                        <div class="leave-form-row">
                            <input name="year"
                                class="leave-control leave-year-input"
                                value="{{ $year }}"
                                placeholder="Year">

                            <select name="employee_id" class="leave-control leave-employee-select">
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->user_name ?? $employee->display_name }}
                                </option>
                                @endforeach
                            </select>

                            <button class="leave-btn-light" type="submit">
                                <i class="fas fa-user-check"></i>
                                Generate Single
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @endif

    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Allocation Records</h5>
                    <div class="leave-card-subtitle">
                        View allocated, used, remaining and LWP leave balances by employee.
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
                <table class="leave-table js-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th>Stage</th>
                            <th>Policy</th>
                            <th>Allocated</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>LWP</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($allocations as $allocation)
                        @php
                        $employeeName = optional($allocation->employee)->display_name
                        ?? optional(optional($allocation->employee)->user)->name
                        ?? 'Unknown Employee';

                        $employeeCode = optional($allocation->employee)->employee_code
                        ?? optional($allocation->employee)->code
                        ?? 'EMP';

                        $initial = strtoupper(substr(trim($employeeName), 0, 1));
                        @endphp

                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>

                             <td>
                                 <div class="leave-employee">
                                     @php
                                         $passportPhotoUrl = resolveEmployeeAdminAvatar($allocation->employee);
                                     @endphp
                                     @if($passportPhotoUrl)
                                         <div class="leave-avatar">
                                             <img src="{{ $passportPhotoUrl }}"
                                                  alt="{{ $employeeName }}"
                                                  onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                             <span style="display: none;">{{ $initial }}</span>
                                         </div>
                                     @else
                                         <div class="leave-avatar">{{ $initial }}</div>
                                     @endif
                                    <div>
                                        <div class="leave-employee-name">{{ $employeeName }}</div>
                                        <div class="leave-employee-meta">{{ $employeeCode }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="leave-pill pill-stage">
                                    <i class="fas fa-user-clock"></i>
                                    {{ ucfirst(str_replace('_', ' ', $allocation->employment_stage ?? '-')) }}
                                </span>
                            </td>

                            <td>
                                <span class="leave-pill pill-policy">
                                    <i class="fas fa-shield-alt"></i>
                                    {{ optional($allocation->policy)->policy_name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <div class="leave-metric">{{ number_format((float) $allocation->total_allocated, 2) }}</div>
                                <div class="metric-muted">Total allocated</div>
                            </td>

                            <td>
                                <div class="leave-metric">{{ number_format((float) $allocation->total_used, 2) }}</div>
                                <div class="metric-muted">Consumed</div>
                            </td>

                            <td>
                                <div class="leave-metric">{{ number_format((float) $allocation->total_remaining, 2) }}</div>
                                <div class="metric-muted">Available</div>
                            </td>

                            <td>
                                @if((float) $allocation->lwp_used > 0)
                                <span class="leave-pill pill-lwp">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ number_format((float) $allocation->lwp_used, 2) }}
                                </span>
                                @else
                                <span class="text-muted font-weight-bold">0.00</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-wallet"></i>
                                    <div style="font-weight:900;color:var(--leave-text);">
                                        No Allocation Records Found
                                    </div>
                                    <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                        Generate yearly allocation to show employee leave balances here.
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($allocations, 'links'))
            <div class="mt-3">
                {{ $allocations->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

<script>
    function triggerLeaveExport(type) {
        if ($.fn.DataTable.isDataTable('.js-datatable')) {
            let table = $('.js-datatable').DataTable();

            let buttons = {
                csv: '.buttons-csv',
                excel: '.buttons-excel',
                pdf: '.buttons-pdf',
                print: '.buttons-print'
            };

            if (buttons[type]) {
                table.button(buttons[type]).trigger();
            }
        } else {
            alert('No records available to export.');
        }
    }
</script>
@endsection