@extends('layouts.panel')

@section('page_title', 'Holiday Management')

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

    .leave-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .leave-add-btn,
    .leave-import-btn {
        border: 0 !important;
        border-radius: 14px;
        background: #fff !important;
        color: var(--leave-primary) !important;
        font-size: 13px;
        font-weight: 900;
        height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 12px 24px rgba(16, 24, 40, .14);
        transition: all .2s ease;
    }

    .leave-import-btn {
        background: rgba(255, 255, 255, .16) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, .28) !important;
        box-shadow: none;
    }

    .leave-add-btn i {
        color: var(--leave-primary) !important;
    }

    .leave-import-btn i {
        color: #fff !important;
    }

    .leave-add-btn:hover,
    .leave-import-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
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
        width: 74px;
        height: 74px;
        border-radius: 50%;
        position: absolute;
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
        --summary-gradient: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        --summary-shadow: rgba(75, 0, 232, .22);
        --summary-soft: rgba(75, 0, 232, .08);
    }

    .summary-success {
        --summary-gradient: linear-gradient(135deg, #12B76A, #039855);
        --summary-shadow: rgba(18, 183, 106, .22);
        --summary-soft: rgba(18, 183, 106, .09);
    }

    .summary-warning {
        --summary-gradient: linear-gradient(135deg, #F79009, #DC6803);
        --summary-shadow: rgba(247, 144, 9, .22);
        --summary-soft: rgba(247, 144, 9, .10);
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

    .date-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 180px;
    }

    .date-box {
        min-width: 54px;
        border-radius: 16px;
        border: 1px solid #EAECF0;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(16, 24, 40, .06);
        text-align: center;
    }

    .date-month {
        background: #FEF3F2;
        color: #B42318;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 5px 4px;
    }

    .date-day {
        color: var(--leave-text);
        font-size: 20px;
        font-weight: 950;
        line-height: 1;
        padding: 8px 4px;
    }

    .holiday-title {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .holiday-meta {
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

    .pill-company {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .pill-public {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .pill-restricted {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .pill-off {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .pill-working {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid var(--leave-border);
        background: #fff;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
    }

    .icon-btn:hover {
        color: var(--leave-primary);
        background: var(--leave-soft);
        border-color: rgba(75, 0, 232, .18);
    }

    .leave-action-menu {
        border: 1px solid var(--leave-border);
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(16, 24, 40, .12);
        padding: 8px;
    }

    .leave-action-menu .dropdown-item {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        padding: 9px 12px;
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

    .leave-modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(16, 24, 40, .22);
    }

    .leave-modal-header {
        border: 0;
        padding: 20px 22px;
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
    }

    .leave-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        color: #fff;
    }

    .leave-modal-subtitle {
        margin-top: 4px;
        font-size: 12px;
        font-weight: 650;
        color: rgba(255, 255, 255, .78);
    }

    .leave-modal-header .close {
        color: #fff;
        opacity: .9;
        text-shadow: none;
        outline: none;
    }

    .leave-modal-body {
        padding: 18px;
        background: #F8FAFC;
    }

    .leave-modal-section {
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 18px;
        padding: 16px;
        margin-bottom: 14px;
    }

    .leave-modal-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--leave-text);
        font-size: 13px;
        font-weight: 950;
        margin-bottom: 14px;
    }

    .leave-modal-section-title i {
        color: var(--leave-primary);
    }

    .leave-field {
        margin-bottom: 14px;
    }

    .leave-field label {
        display: block;
        margin-bottom: 7px;
        color: #344054;
        font-size: 12px;
        font-weight: 900;
    }

    .leave-input {
        width: 100%;
        height: 44px;
        border-radius: 14px;
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        padding: 0 13px;
        font-size: 13px;
        font-weight: 700;
        outline: none;
        transition: all .2s ease;
    }

    .leave-input:focus {
        border-color: rgba(75, 0, 232, .28);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
    }

    .leave-input::placeholder {
        color: #98A2B3;
        font-weight: 600;
    }

    .leave-check-card {
        border: 1px solid var(--leave-border);
        background: #fff;
        border-radius: 14px;
        padding: 11px 12px;
        cursor: pointer;
        transition: all .2s ease;
        min-height: 44px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #344054;
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 10px;
    }

    .leave-check-card:hover {
        background: var(--leave-soft);
        border-color: rgba(75, 0, 232, .20);
    }

    .leave-check-card input {
        width: 16px;
        height: 16px;
        accent-color: var(--leave-primary);
    }

    .leave-modal-footer {
        border: 0;
        padding: 16px 20px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .leave-modal-btn {
        border: 0;
        border-radius: 14px;
        height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 900;
        transition: all .2s ease;
    }

    .leave-modal-btn-primary {
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        box-shadow: 0 12px 24px rgba(75, 0, 232, .18);
    }

    .leave-modal-btn-primary:hover {
        color: #fff;
        transform: translateY(-1px);
    }

    .leave-modal-btn-light {
        background: #fff;
        color: var(--leave-text);
        border: 1px solid var(--leave-border);
    }

    .leave-modal-btn-light:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
    }

    @media(max-width:991px) {
        .leave-summary-grid {
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

        .leave-hero-actions {
            width: 100%;
        }

        .leave-add-btn,
        .leave-import-btn {
            flex: 1;
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

        .leave-modal-body {
            padding: 14px;
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
                    <i class="fas fa-glass-cheers"></i>
                    HRMS Holiday Calendar
                </div>

                <h1 class="leave-hero-title">Holiday Management</h1>

                <div class="leave-hero-subtitle">
                    Manage company holidays, public holidays, restricted holidays and working-day overrides for attendance and leave calculations.
                </div>
            </div>

            <div class="leave-hero-actions">
                <button type="button" class="leave-import-btn">
                    <i class="fas fa-file-import"></i>
                    Import
                </button>

                <button type="button" class="leave-add-btn" data-toggle="modal" data-target="#createHolidayModal">
                    <i class="fas fa-plus"></i>
                    Add Holiday
                </button>
            </div>
        </div>
    </div>

    @include('hrms.leave.shared.flash')

    <div class="leave-summary-grid">
        <div class="summary-card summary-primary">
            <div class="summary-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $holidays->count() }}</h4>
                <p>Total Holidays</p>
            </div>
        </div>

        <div class="summary-card summary-success">
            <div class="summary-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $holidays->where('is_working_day_override', 0)->count() }}</h4>
                <p>Days Off</p>
            </div>
        </div>

        <div class="summary-card summary-warning">
            <div class="summary-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="summary-info">
                <h4>{{ $holidays->where('is_working_day_override', 1)->count() }}</h4>
                <p>Working Overrides</p>
            </div>
        </div>
    </div>

    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-list"></i>
                </div>

                <div>
                    <h5 class="leave-card-title">Holiday Records</h5>
                    <div class="leave-card-subtitle">
                        Holidays used by attendance blocking, leave sandwich rules and payroll-ready working day calculations.
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
                            <th>Date</th>
                            <th>Holiday</th>
                            <th>Type</th>
                            <th>Working Override</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($holidays as $holiday)
                        @php
                        $typeClass = 'pill-company';
                        if (($holiday->holiday_type ?? '') === 'public') $typeClass = 'pill-public';
                        if (($holiday->holiday_type ?? '') === 'restricted') $typeClass = 'pill-restricted';
                        @endphp

                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>

                            <td>
                                <div class="date-cell">
                                    <div class="date-box">
                                        <div class="date-month">{{ optional($holiday->holiday_date)->format('M') }}</div>
                                        <div class="date-day">{{ optional($holiday->holiday_date)->format('d') }}</div>
                                    </div>

                                    <div>
                                        <div class="holiday-title">{{ optional($holiday->holiday_date)->format('l') }}</div>
                                        <div class="holiday-meta">{{ optional($holiday->holiday_date)->format('d M Y') }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="holiday-title">{{ $holiday->title }}</div>
                                <div class="holiday-meta">Holiday record</div>
                            </td>

                            <td>
                                <span class="leave-pill {{ $typeClass }}">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ ucfirst($holiday->holiday_type) }}
                                </span>
                            </td>

                            <td>
                                @if($holiday->is_working_day_override)
                                <span class="leave-pill pill-working">
                                    <i class="fas fa-briefcase"></i>
                                    Working Day
                                </span>
                                @else
                                <span class="leave-pill pill-off">
                                    <i class="fas fa-moon"></i>
                                    Day Off
                                </span>
                                @endif
                            </td>

                            <td class="text-right">
                                <div class="dropdown d-inline-block">
                                    <button class="icon-btn" type="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right leave-action-menu">
                                        <form method="POST"
                                            action="{{ route('hrms.holidays.destroy', $holiday->id) }}"
                                            onsubmit="return confirm('Delete this holiday?');"
                                            style="margin:0;">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="dropdown-item text-danger bg-transparent border-0 w-100 text-left">
                                                <i class="fas fa-trash mr-2"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <div style="font-weight:900;color:var(--leave-text);">
                                        No Holidays Found
                                    </div>
                                    <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                        Add holidays to control attendance, leave and payroll calculations.
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade orb-type-modal" id="createHolidayModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('hrms.holidays.store') }}" class="modal-content leave-modal-content">
                @csrf

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">Add Holiday</h5>
                        <div class="leave-modal-subtitle">Create a new holiday or working-day override.</div>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="leave-modal-section">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-calendar-day"></i>
                            Holiday Details
                        </div>

                        <div class="leave-field">
                            <label>Holiday Title</label>
                            <input type="text"
                                name="title"
                                class="leave-input"
                                placeholder="Example: Independence Day"
                                required>
                        </div>

                        <div class="leave-field">
                            <label>Holiday Date</label>
                            <input type="date"
                                name="holiday_date"
                                class="leave-input"
                                required>
                        </div>

                        <div class="leave-field">
                            <label>Holiday Type</label>
                            <select name="holiday_type" class="leave-input">
                                <option value="company">Company</option>
                                <option value="public">Public</option>
                                <option value="restricted">Restricted</option>
                            </select>
                        </div>

                        <label class="leave-check-card" for="create_working">
                            <input type="checkbox"
                                id="create_working"
                                name="is_working_day_override"
                                value="1">
                            <span>Working Day Override</span>
                        </label>

                        <label class="leave-check-card" for="create_active">
                            <input type="checkbox"
                                id="create_active"
                                name="is_active"
                                value="1"
                                checked>
                            <span>Active</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-modal-btn leave-modal-btn-light" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="leave-modal-btn leave-modal-btn-primary">
                        <i class="fas fa-save"></i>
                        Save Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

<script>
    function triggerLeaveExport(type) {
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
    }
</script>
@endsection