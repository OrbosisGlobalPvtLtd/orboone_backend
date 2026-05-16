@extends('layouts.panel')

@section('page_title', 'Leave Policies')

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

    .leave-add-btn {
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

    .leave-add-btn i {
        color: var(--leave-primary) !important;
    }

    .leave-add-btn:hover,
    .leave-add-btn:focus {
        background: #fff !important;
        color: var(--leave-primary) !important;
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 24, 40, .20);
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

    .policy-name-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 220px;
    }

    .policy-icon {
        width: 40px;
        height: 40px;
        border-radius: 15px;
        background: linear-gradient(135deg, rgba(75, 0, 232, .14), rgba(134, 0, 238, .18));
        color: var(--leave-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .policy-title {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .policy-meta {
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
        margin: 2px;
    }

    .pill-primary {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
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

    .pill-warning {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .pill-danger {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .pill-muted {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
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

    .policy-split {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 145px;
    }

    .policy-split-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        font-size: 11px;
        font-weight: 800;
        color: var(--leave-muted);
    }

    .policy-split-value {
        color: var(--leave-text);
        font-weight: 950;
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

    .leave-check-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
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
            grid-template-columns: 1fr;
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
                    <i class="fas fa-file-contract"></i>
                    HRMS Leave Policy
                </div>

                <h1 class="leave-hero-title">Leave Policies</h1>

                <div class="leave-hero-subtitle">
                    Manage yearly allocation rules, monthly limits, sandwich policy, Nov/Dec usage cap and comp-off expiry behavior.
                </div>
            </div>

            <div class="leave-hero-actions">
                <button type="button" class="leave-add-btn" data-toggle="modal" data-target="#createPolicyModal">
                    <i class="fas fa-plus"></i>
                    Add Policy
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
                    <h5 class="leave-card-title">Policy Records</h5>
                    <div class="leave-card-subtitle">
                        Configure active leave policies used for allocation, deductions and approval rules.
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
                            <th>Policy</th>
                            <th>Annual Leaves</th>
                            <th>Monthly</th>
                            <th>Sandwich Rule</th>
                            <th>Nov/Dec Cap</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($policies as $policy)
                        <tr>
                            <td><strong>{{ $loop->iteration }}</strong></td>

                            <td>
                                <div class="policy-name-cell">
                                    <div class="policy-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <div class="policy-title">{{ $policy->policy_name }}</div>
                                        <div class="policy-meta">Rounding: {{ ucfirst($policy->rounding_method ?? 'nearest') }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="policy-split">
                                    <div class="policy-split-item">
                                        <span>Total</span>
                                        <span class="policy-split-value">{{ $policy->annual_total_leaves }}</span>
                                    </div>
                                    <div class="policy-split-item">
                                        <span>Paid</span>
                                        <span class="policy-split-value">{{ $policy->annual_paid_leaves }}</span>
                                    </div>
                                    <div class="policy-split-item">
                                        <span>Sick</span>
                                        <span class="policy-split-value">{{ $policy->annual_sick_leaves }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="leave-pill pill-primary">
                                    <i class="fas fa-calendar-alt"></i>
                                    Max {{ $policy->monthly_leave_limit }}
                                </span>
                            </td>

                            <td>
                                @if($policy->sandwich_enabled)
                                <span class="leave-pill pill-warning">
                                    <i class="fas fa-link"></i>
                                    Enabled
                                </span>
                                <div class="mt-1">
                                    @if($policy->weekoff_included_in_sandwich)
                                    <span class="leave-pill pill-muted">Weekoff</span>
                                    @endif
                                    @if($policy->holiday_included_in_sandwich)
                                    <span class="leave-pill pill-muted">Holiday</span>
                                    @endif
                                </div>
                                @else
                                <span class="leave-pill pill-muted">
                                    <i class="fas fa-unlink"></i>
                                    Disabled
                                </span>
                                @endif
                            </td>

                            <td>
                                @if($policy->nov_dec_half_usage_enabled)
                                <span class="leave-pill pill-danger">
                                    <i class="fas fa-percentage"></i>
                                    {{ $policy->nov_dec_usage_percentage }}%
                                </span>
                                <div class="policy-meta mt-1">
                                    Above {{ $policy->nov_dec_threshold_balance }} balance
                                </div>
                                @else
                                <span class="leave-pill pill-muted">Not Applied</span>
                                @endif
                            </td>

                            <td>
                                <span class="leave-pill {{ $policy->is_active ? 'pill-active' : 'pill-inactive' }}">
                                    <i class="fas fa-circle" style="font-size:6px;"></i>
                                    {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="text-right">
                                <div class="dropdown d-inline-block">
                                    <button class="icon-btn" type="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right leave-action-menu">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editPolicyModal{{ $policy->id }}">
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
                                    <i class="fas fa-file-contract"></i>
                                    <div style="font-weight:900;color:var(--leave-text);">
                                        No Leave Policies Found
                                    </div>
                                    <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                        Add your first leave policy to start allocation configuration.
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

    @php
    $policyFields = [
    'policy_name' => ['Policy Name', 'text', 'Example: Default Leave Policy', true],
    'annual_total_leaves' => ['Annual Total Leaves', 'number', '25', false],
    'annual_paid_leaves' => ['Annual Paid Leaves', 'number', '18', false],
    'annual_sick_leaves' => ['Annual Sick Leaves', 'number', '7', false],
    'monthly_leave_limit' => ['Monthly Leave Limit', 'number', '2', false],
    'max_leave_at_once' => ['Max Leave At Once', 'number', '15', false],
    'probation_leave_limit' => ['Probation Limit', 'number', '1', false],
    'internship_leave_limit' => ['Internship Limit', 'number', '1', false],
    'medical_certificate_after_days' => ['Medical Certificate After Days', 'number', '2', false],
    'nov_dec_threshold_balance' => ['Nov/Dec Threshold Balance', 'number', '20', false],
    'nov_dec_usage_percentage' => ['Nov/Dec Usage Percentage', 'number', '50', false],
    ];

    $policyRules = [
    'allow_monthly_balance_accumulation' => 'Monthly Accumulation',
    'carry_forward_enabled' => 'Carry Forward',
    'sandwich_enabled' => 'Sandwich Rule',
    'weekoff_included_in_sandwich' => 'Include Weekoff',
    'holiday_included_in_sandwich' => 'Include Holiday',
    'nov_dec_half_usage_enabled' => 'Nov/Dec Cap',
    'comp_off_expiry_same_month' => 'Comp Off Same Month',
    'is_active' => 'Active',
    ];
    @endphp

    @foreach($policies as $policy)
    <div class="modal fade orb-type-modal" id="editPolicyModal{{ $policy->id }}" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form method="POST" action="{{ route('hrms.leave.policies.update', $policy->id ?? 0) }}" class="modal-content leave-modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">Edit Leave Policy</h5>
                        <div class="leave-modal-subtitle">{{ $policy->policy_name }}</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="leave-modal-section">
                                <div class="leave-modal-section-title">
                                    <i class="fas fa-file-contract"></i>
                                    Limits & Balances
                                </div>

                                <div class="row">
                                    @foreach($policyFields as $field => $meta)
                                    <div class="col-md-4">
                                        <div class="leave-field">
                                            <label>{{ $meta[0] }}</label>
                                            <input type="{{ $meta[1] }}"
                                                name="{{ $field }}"
                                                class="leave-input"
                                                value="{{ old($field, $policy->$field) }}"
                                                placeholder="{{ $meta[2] }}"
                                                @if($meta[1]==='number' ) min="0" step="0.01" @endif
                                                {{ $meta[3] ? 'required' : '' }}>
                                        </div>
                                    </div>
                                    @endforeach

                                    <div class="col-md-4">
                                        <div class="leave-field">
                                            <label>Rounding Method</label>
                                            <select class="leave-input" name="rounding_method">
                                                <option value="nearest" {{ $policy->rounding_method == 'nearest' ? 'selected' : '' }}>Nearest</option>
                                                <option value="floor" {{ $policy->rounding_method == 'floor' ? 'selected' : '' }}>Floor</option>
                                                <option value="ceil" {{ $policy->rounding_method == 'ceil' ? 'selected' : '' }}>Ceil</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="leave-modal-section">
                                <div class="leave-modal-section-title">
                                    <i class="fas fa-toggle-on"></i>
                                    Rules
                                </div>

                                <div class="leave-check-grid">
                                    @foreach($policyRules as $field => $label)
                                    <label class="leave-check-card" for="edit_{{ $field }}_{{ $policy->id }}">
                                        <input type="checkbox"
                                            id="edit_{{ $field }}_{{ $policy->id }}"
                                            name="{{ $field }}"
                                            value="1"
                                            {{ $policy->$field ? 'checked' : '' }}>
                                        <span>{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
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

    <div class="modal fade orb-type-modal" id="createPolicyModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form method="POST" action="{{ route('hrms.leave.policies.store') }}" class="modal-content leave-modal-content">
                @csrf

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">Create Leave Policy</h5>
                        <div class="leave-modal-subtitle">Define leave thresholds, rules, and sandwich configurations.</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="leave-modal-section">
                                <div class="leave-modal-section-title">
                                    <i class="fas fa-file-contract"></i>
                                    Limits & Balances
                                </div>

                                <div class="row">
                                    @foreach($policyFields as $field => $meta)
                                    @php
                                    $defaultValue = '';
                                    if ($field === 'annual_total_leaves') $defaultValue = 25;
                                    if ($field === 'annual_paid_leaves') $defaultValue = 18;
                                    if ($field === 'annual_sick_leaves') $defaultValue = 7;
                                    if ($field === 'monthly_leave_limit') $defaultValue = 2;
                                    if ($field === 'max_leave_at_once') $defaultValue = 15;
                                    if ($field === 'probation_leave_limit') $defaultValue = 1;
                                    if ($field === 'internship_leave_limit') $defaultValue = 1;
                                    if ($field === 'medical_certificate_after_days') $defaultValue = 2;
                                    if ($field === 'nov_dec_threshold_balance') $defaultValue = 20;
                                    if ($field === 'nov_dec_usage_percentage') $defaultValue = 50;
                                    @endphp

                                    <div class="col-md-4">
                                        <div class="leave-field">
                                            <label>{{ $meta[0] }}</label>
                                            <input type="{{ $meta[1] }}"
                                                name="{{ $field }}"
                                                class="leave-input"
                                                value="{{ old($field, $defaultValue) }}"
                                                placeholder="{{ $meta[2] }}"
                                                @if($meta[1]==='number' ) min="0" step="0.01" @endif
                                                {{ $meta[3] ? 'required' : '' }}>
                                        </div>
                                    </div>
                                    @endforeach

                                    <div class="col-md-4">
                                        <div class="leave-field">
                                            <label>Rounding Method</label>
                                            <select class="leave-input" name="rounding_method">
                                                <option value="nearest">Nearest</option>
                                                <option value="floor">Floor</option>
                                                <option value="ceil">Ceil</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="leave-modal-section">
                                <div class="leave-modal-section-title">
                                    <i class="fas fa-toggle-on"></i>
                                    Rules
                                </div>

                                <div class="leave-check-grid">
                                    @foreach($policyRules as $field => $label)
                                    <label class="leave-check-card" for="create_{{ $field }}">
                                        <input type="checkbox"
                                            id="create_{{ $field }}"
                                            name="{{ $field }}"
                                            value="1"
                                            {{ !in_array($field, ['carry_forward_enabled'], true) ? 'checked' : '' }}>
                                        <span>{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-modal-btn leave-modal-btn-light" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="leave-modal-btn leave-modal-btn-primary">
                        <i class="fas fa-save"></i>
                        Save Policy
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