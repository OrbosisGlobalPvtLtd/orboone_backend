@extends('layouts.panel')

@section('page_title', 'Leave Types')

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

    .leave-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .leave-btn-primary {
        border: 0 !important;
        border-radius: 14px;
        background: #ffffff !important;
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

    .leave-btn-primary i {
        color: var(--leave-primary) !important;
    }

    .leave-btn-primary:hover,
    .leave-btn-primary:focus {
        background: #ffffff !important;
        color: var(--leave-primary) !important;
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 24, 40, .20);
    }

    .leave-btn-primary:hover i,
    .leave-btn-primary:focus i {
        color: var(--leave-primary) !important;
    }

    /* .leave-btn-primary:hover {
        transform: translateY(-1px);
        color: var(--leave-primary);
        box-shadow: 0 16px 28px rgba(16, 24, 40, .20);
    } */

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

    .leave-name-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 180px;
    }

    .leave-type-icon {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        box-shadow: 0 12px 20px rgba(16, 24, 40, .12);
    }

    .leave-type-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .leave-type-meta {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 2px;
        font-weight: 700;
    }

    .leave-code {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 10px;
        background: #F9FAFB;
        border: 1px solid #EAECF0;
        color: #344054;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
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
        margin: 2px;
    }

    .pill-paid {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .pill-sick {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .pill-lwp {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .pill-comp {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .pill-active {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .pill-inactive {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .limit-box {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .limit-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid #EAECF0;
        background: #F9FAFB;
        color: #475467;
        border-radius: 10px;
        padding: 6px 8px;
        font-size: 11px;
        font-weight: 850;
    }

    .color-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 850;
        color: #475467;
    }

    .type-dot {
        width: 22px;
        height: 22px;
        min-width: 22px;
        border-radius: 8px;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #EAECF0, 0 8px 16px rgba(16, 24, 40, .10);
        display: inline-block;
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

    input[type="color"].leave-color-input {
        padding: 6px;
        cursor: pointer;
    }

    .leave-check-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .leave-check-card {
        position: relative;
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

        .leave-action-wrap {
            width: 100%;
        }

        .leave-light-btn {
            flex: 1;
            justify-content: center;
        }

        .leave-check-grid {
            grid-template-columns: repeat(2, 1fr);
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
                    <i class="fas fa-tags"></i>
                    HRMS Leave Configuration
                </div>

                <h1 class="leave-hero-title">Leave Types</h1>

                <div class="leave-hero-subtitle">
                    Configure paid leave, sick leave, LWP and comp-off categories used across employee leave policies and payroll calculations.
                </div>
            </div>

            <div class="leave-hero-actions">
                <button type="button" class="leave-btn-primary" data-toggle="modal" data-target="#createTypeModal">
                    <i class="fas fa-plus"></i>
                    Add Leave Type
                </button>
            </div>
        </div>
    </div>

    @include('hrms.leave.shared.flash')

    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-list"></i>
                </div>

                <div>
                    <h5 class="leave-card-title">Leave Type Records</h5>
                    <div class="leave-card-subtitle">
                        Manage active leave categories, flags, limits and payroll behavior.
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
                            <th>Name</th>
                            <th>Code</th>
                            <th>Flags</th>
                            <th>Limits</th>
                            <th>Color</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($types as $type)
                        @php
                        $typeColor = $type->color ?: '#64748b';
                        $initial = strtoupper(substr(trim($type->name ?? 'L'), 0, 1));
                        @endphp

                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>

                            <td>
                                <div class="leave-name-cell">
                                    <div class="leave-type-icon" style="background:{{ $typeColor }};">
                                        {{ $initial }}
                                    </div>
                                    <div>
                                        <div class="leave-type-name">{{ $type->name }}</div>
                                        <div class="leave-type-meta">Leave category</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="leave-code">{{ $type->code }}</span>
                            </td>

                            <td>
                                @if($type->is_paid)
                                <span class="leave-pill pill-paid"><i class="fas fa-check-circle"></i> Paid</span>
                                @endif

                                @if($type->is_sick)
                                <span class="leave-pill pill-sick"><i class="fas fa-briefcase-medical"></i> Sick</span>
                                @endif

                                @if($type->is_lwp)
                                <span class="leave-pill pill-lwp"><i class="fas fa-exclamation-circle"></i> LWP</span>
                                @endif

                                @if($type->is_comp_off)
                                <span class="leave-pill pill-comp"><i class="fas fa-calendar-plus"></i> Comp Off</span>
                                @endif

                                @if(!$type->is_paid && !$type->is_sick && !$type->is_lwp && !$type->is_comp_off)
                                <span class="text-muted font-weight-bold">No flags</span>
                                @endif
                            </td>

                            <td>
                                <div class="limit-box">
                                    <span class="limit-chip">
                                        <i class="fas fa-calendar-alt"></i>
                                        M: {{ $type->max_days_per_month ?: '∞' }}
                                    </span>

                                    <span class="limit-chip">
                                        <i class="fas fa-file-alt"></i>
                                        R: {{ $type->max_days_per_request ?: '∞' }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="color-chip">
                                    <span class="type-dot" style="background:{{ $typeColor }}"></span>
                                    {{ $typeColor }}
                                </span>
                            </td>

                            <td>
                                <span class="leave-pill {{ $type->is_active ? 'pill-active' : 'pill-inactive' }}">
                                    <i class="fas fa-circle" style="font-size:6px;"></i>
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="text-right">
                                <div class="dropdown d-inline-block">
                                    <button class="icon-btn" type="button" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right leave-action-menu">
                                        <a class="dropdown-item"
                                            href="#"
                                            data-toggle="modal"
                                            data-target="#editTypeModal{{ $type->id }}">
                                            <i class="fas fa-edit text-primary mr-2"></i>
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-tags"></i>
                                    <div style="font-weight:900;color:var(--leave-text);">
                                        No Leave Types Found
                                    </div>
                                    <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                        Add your first leave type to start configuring leave policies.
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

    @foreach($types as $type)
    <div class="modal fade orb-type-modal" id="editTypeModal{{ $type->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <form method="POST" action="{{ route('hrms.leave.types.update', $type->id ?? 0) }}" class="modal-content leave-modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">Edit Leave Type</h5>
                        <div class="leave-modal-subtitle">{{ $type->name }} · {{ $type->code }}</div>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="leave-modal-section">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-tag"></i>
                            Basic Info
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="leave-field">
                                    <label>Leave Type Name</label>
                                    <input type="text"
                                        name="name"
                                        class="leave-input"
                                        value="{{ old('name', $type->name) }}"
                                        placeholder="Example: Paid Leave"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="leave-field">
                                    <label>Unique Code</label>
                                    <input type="text"
                                        name="code"
                                        class="leave-input"
                                        value="{{ old('code', $type->code) }}"
                                        placeholder="Example: paid_leave"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="leave-modal-section">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-sliders-h"></i>
                            Limits & Color
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="leave-field">
                                    <label>Monthly Limit</label>
                                    <input type="number"
                                        name="max_days_per_month"
                                        class="leave-input"
                                        value="{{ old('max_days_per_month', $type->max_days_per_month) }}"
                                        min="0"
                                        step="0.5"
                                        placeholder="No limit">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="leave-field">
                                    <label>Request Limit</label>
                                    <input type="number"
                                        name="max_days_per_request"
                                        class="leave-input"
                                        value="{{ old('max_days_per_request', $type->max_days_per_request) }}"
                                        min="0"
                                        step="0.5"
                                        placeholder="No limit">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="leave-field">
                                    <label>Display Color</label>
                                    <input type="color"
                                        name="color"
                                        class="leave-input leave-color-input"
                                        value="{{ old('color', $type->color ?: '#4B00E8') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="leave-modal-section mb-0">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-toggle-on"></i>
                            Flags & Rules
                        </div>

                        <div class="leave-check-grid">
                            @foreach(['is_paid'=>'Paid','is_sick'=>'Sick','is_lwp'=>'LWP','is_comp_off'=>'Comp Off','requires_attachment'=>'Attachment','allow_half_day'=>'Half Day','applicable_after_confirmation'=>'After Confirmation','is_active'=>'Active'] as $field => $label)
                            <label class="leave-check-card" for="edit_{{ $field }}_{{ $type->id }}">
                                <input type="checkbox"
                                    id="edit_{{ $field }}_{{ $type->id }}"
                                    name="{{ $field }}"
                                    value="1"
                                    {{ $type->$field ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-modal-btn leave-modal-btn-light" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="leave-modal-btn leave-modal-btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <div class="modal fade orb-type-modal" id="createTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <form method="POST" action="{{ route('hrms.leave.types.store') }}" class="modal-content leave-modal-content">
                @csrf

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">Add Leave Type</h5>
                        <div class="leave-modal-subtitle">Create a new leave type category for policies and payroll rules.</div>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="leave-modal-section">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-tag"></i>
                            Basic Info
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="leave-field">
                                    <label>Leave Type Name</label>
                                    <input type="text"
                                        name="name"
                                        class="leave-input"
                                        value="{{ old('name') }}"
                                        placeholder="Example: Paid Leave"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="leave-field">
                                    <label>Unique Code</label>
                                    <input type="text"
                                        name="code"
                                        class="leave-input"
                                        value="{{ old('code') }}"
                                        placeholder="Example: paid_leave"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="leave-modal-section">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-sliders-h"></i>
                            Limits & Color
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="leave-field">
                                    <label>Monthly Limit</label>
                                    <input type="number"
                                        name="max_days_per_month"
                                        class="leave-input"
                                        value="{{ old('max_days_per_month') }}"
                                        min="0"
                                        step="0.5"
                                        placeholder="No limit">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="leave-field">
                                    <label>Request Limit</label>
                                    <input type="number"
                                        name="max_days_per_request"
                                        class="leave-input"
                                        value="{{ old('max_days_per_request') }}"
                                        min="0"
                                        step="0.5"
                                        placeholder="No limit">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="leave-field">
                                    <label>Display Color</label>
                                    <input type="color"
                                        name="color"
                                        class="leave-input leave-color-input"
                                        value="{{ old('color', '#4B00E8') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="leave-modal-section mb-0">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-toggle-on"></i>
                            Flags & Rules
                        </div>

                        <div class="leave-check-grid">
                            @foreach(['is_paid'=>'Paid','is_sick'=>'Sick','is_lwp'=>'LWP','is_comp_off'=>'Comp Off','requires_attachment'=>'Attachment','allow_half_day'=>'Half Day','applicable_after_confirmation'=>'After Confirmation','is_active'=>'Active'] as $field => $label)
                            <label class="leave-check-card" for="create_{{ $field }}">
                                <input type="checkbox"
                                    id="create_{{ $field }}"
                                    name="{{ $field }}"
                                    value="1"
                                    {{ in_array($field, ['allow_half_day','is_active'], true) ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-modal-btn leave-modal-btn-light" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="leave-modal-btn leave-modal-btn-primary">
                        <i class="fas fa-save"></i>
                        Save Leave Type
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

    $(document).on('input', 'input[name="name"]', function() {
        let form = $(this).closest('form');
        let codeInput = form.find('input[name="code"]');

        if (!codeInput.val()) {
            let generated = $(this).val()
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');

            codeInput.val(generated);
        }
    });
</script>
@endsection