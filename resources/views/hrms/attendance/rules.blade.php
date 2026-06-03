@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'attendances'])

@section('_content')
<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg) !important;
        overflow-x: hidden !important;
    }

    .att-page {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 80px);
        padding: 24px;
        background: var(--orb-bg);
        overflow-x: hidden;
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* HERO */

    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 26px 28px;
        color: #fff;
        box-shadow: 0 20px 45px rgba(75, 0, 232, .22);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin: 0 0 18px;
    }

    .orb-hero::after {
        content: '';
        position: absolute;
        width: 230px;
        height: 230px;
        border-radius: 50%;
        right: -95px;
        bottom: -115px;
        background: rgba(255, 255, 255, .10);
    }

    .orb-hero-content,
    .orb-hero-actions {
        position: relative;
        z-index: 2;
    }

    .orb-hero-content {
        min-width: 0;
    }

    .orb-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .15);
        color: rgba(255, 255, 255, .94);
        font-size: 11px;
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .orb-hero h1 {
        font-size: 28px;
        font-weight: 950;
        margin: 0;
        letter-spacing: -.03em;
        color: #fff;
    }

    .orb-hero p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .84);
        font-size: 13px;
        line-height: 1.6;
        max-width: 780px;
    }

    /* BUTTONS */

    .orb-btn {
        border-radius: 14px;
        min-height: 40px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none !important;
        border: 1px solid transparent;
        line-height: 1;
        white-space: nowrap;
    }

    .orb-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .orb-btn-primary {
        background: #fff;
        color: var(--orb-primary);
        border-color: rgba(255, 255, 255, .65);
        box-shadow: 0 12px 24px rgba(16, 24, 40, .12);
    }

    .orb-btn-primary:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .orb-btn-light {
        background: #fff;
        color: var(--orb-text);
        border-color: var(--orb-border);
    }

    .orb-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .orb-btn-reset {
        min-height: 34px;
        height: 34px;
        padding: 0 12px;
        border-radius: 11px;
        font-size: 12px;
        box-shadow: none;
    }

    /* SUMMARY GRID */

    .orb-summary-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .orb-summary-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        padding: 16px 18px;
        box-shadow: var(--orb-shadow);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.2s ease;
    }

    .orb-summary-card:hover {
        transform: translateY(-2px);
    }

    .orb-summary-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .card-purple .orb-summary-icon {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }
    .card-success .orb-summary-icon {
        background: #ECFDF3;
        color: #027A48;
    }
    .card-danger .orb-summary-icon {
        background: #FEF3F2;
        color: #B42318;
    }
    .card-warning .orb-summary-icon {
        background: #FFFAEB;
        color: #B54708;
    }
    .card-info .orb-summary-icon {
        background: #F0F9FF;
        color: #026AA2;
    }

    .orb-summary-label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--orb-muted);
        font-weight: 900;
        margin-bottom: 4px;
        letter-spacing: .04em;
    }

    .orb-summary-value {
        font-size: 20px;
        font-weight: 950;
        color: var(--orb-text);
        line-height: 1.1;
        white-space: nowrap;
    }

    /* CARDS */

    .orb-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        margin-bottom: 18px;
        overflow: hidden;
    }

    .orb-table-card .orb-card-body {
        padding: 0;
        overflow: hidden;
    }

    .orb-table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #EEF2F6;
        background: #fff;
    }

    .orb-table-head-left {
        min-width: 0;
    }

    .orb-table-head-right {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 0 0 auto;
    }

    .orb-table-title {
        margin: 0;
        font-size: 16px;
        font-weight: 950;
        color: var(--orb-text);
        letter-spacing: -.02em;
    }

    .orb-table-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 600;
    }

    .orb-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, .10);
    }

    /* TABLES */

    .att-table-wrap {
        padding: 0;
    }

    .att-table-responsive {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        background: #fff;
    }

    .att-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }

    .att-table th {
        background: #F8FAFC;
        color: #475467;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        padding: 13px 14px;
        border-top: 0 !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .att-table td {
        vertical-align: middle !important;
        white-space: nowrap;
        padding: 13px 14px !important;
        border-color: #F2F4F7 !important;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
        border-bottom: 1px solid #F2F4F7 !important;
    }

    .att-table tbody tr {
        transition: all .15s ease;
    }

    .att-table tbody tr:hover td {
        background: #FAF8FF !important;
    }

    /* BADGES */

    .att-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge-active {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .badge-muted {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .badge-default {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    /* ACTION BUTTONS */

    .icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 11px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: var(--orb-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
        box-shadow: none;
    }

    .icon-btn:hover {
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .18);
        background: var(--orb-soft);
    }

    /* PREMIUM MODAL SYSTEM */

    .modal-backdrop {
        z-index: 1040 !important;
        background: #0F172A !important;
    }
    .modal-backdrop.show {
        opacity: .58 !important;
    }
    .modal {
        z-index: 1050 !important;
    }

    .orb-rule-modal .modal-dialog {
        max-width: 860px;
    }

    .att-modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #fff !important;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    .att-modal-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        border-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .att-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        color: #fff;
    }

    .att-modal-subtitle {
        margin-top: 4px;
        font-size: 12px;
        color: rgba(255, 255, 255, .82);
        font-weight: 600;
    }

    .att-modal-header .close {
        color: #fff;
        opacity: 0.85;
        text-shadow: none;
        outline: none;
        font-size: 24px;
        font-weight: 300;
        transition: all 0.2s ease;
    }

    .att-modal-header .close:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .att-modal-body {
        padding: 24px;
        background: #fff !important;
    }

    .att-modal-body label {
        font-size: 10.5px;
        font-weight: 900;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
        display: block;
    }

    .att-modal-body .form-control {
        height: 40px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        font-size: 13px;
        font-weight: 700;
        color: var(--orb-text);
        box-shadow: none !important;
        background-color: #fff;
    }

    .att-modal-body .form-control:focus {
        border-color: rgba(75, 0, 232, .30);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    .att-modal-section {
        border: 1px solid #EEF2F6;
        background: #FCFCFD;
        border-radius: 20px;
        padding: 18px;
        margin-bottom: 16px;
    }

    .att-modal-section-title {
        font-size: 13px;
        font-weight: 950;
        color: var(--orb-text);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .att-modal-section-title i {
        color: var(--orb-primary);
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--orb-primary) !important;
        border-color: var(--orb-primary) !important;
    }

    .custom-control-label {
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-text);
        cursor: pointer;
        padding-top: 2px;
    }

    .att-modal-footer {
        padding: 16px 24px;
        background: #F8FAFC;
        border-top: 1px solid #EEF2F6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .att-modal-footer .orb-btn {
        min-height: 38px;
        height: 38px;
        border-radius: 12px;
    }

    /* RESPONSIVE LAYOUTS */

    @media(max-width: 1440px) {
        .orb-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width: 1199px) {
        .att-page {
            padding: 18px;
        }
    }

    @media(max-width: 991px) {
        .orb-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .orb-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .orb-hero-actions,
        .orb-hero-actions .orb-btn {
            width: 100%;
        }
    }

    @media(max-width: 768px) {
        .att-page {
            padding: 12px;
        }

        .orb-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .orb-hero h1 {
            font-size: 22px;
        }

        .orb-summary-grid {
            grid-template-columns: 1fr;
        }

        .orb-table-header {
            flex-direction: column;
            align-items: stretch;
            padding: 14px;
        }

        .orb-table-head-right {
            justify-content: space-between;
            width: 100%;
        }

        .orb-rule-modal .modal-dialog {
            margin: 12px;
        }
    }
</style>

<div class="att-page">
    <div class="att-container">

        <!-- Hero Header -->
        <div class="orb-hero">
            <div class="orb-hero-content">
                <div class="orb-hero-kicker">
                    <i class="fas fa-cog"></i>
                    HRMS &bull; ATTENDANCE SETTINGS
                </div>
                <h1>Attendance Rules</h1>
                <p>Configure punch timings, late rules, half-day rules, missed punch rules, and work mode policies.</p>
            </div>

            <div class="orb-hero-actions">
                <a href="{{ route('attendances.index') }}" class="orb-btn orb-btn-primary">
                    <i class="fas fa-chart-line text-primary"></i> Dashboard
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('status') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
        @endif

        <!-- Dynamic Rules Summary Cards -->
        @php
            $defaultShift = $attendanceTimes->where('is_default', 1)->first() ?? $attendanceTimes->first();
            $defaultPolicy = $attendancePolicies->first();

            $summaryCards = [
                [
                    'label' => 'Punch Window',
                    'value' => $defaultShift ? \Carbon\Carbon::parse($defaultShift->punch_allowed_from)->format('h:i A') : '08:00 AM',
                    'icon' => 'fa-clock',
                    'color' => 'purple'
                ],
                [
                    'label' => 'Late Mark Rule',
                    'value' => $defaultShift ? \Carbon\Carbon::parse($defaultShift->late_after_time)->format('h:i A') : '09:15 AM',
                    'icon' => 'fa-exclamation-triangle',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Block After',
                    'value' => $defaultPolicy ? \Carbon\Carbon::parse($defaultPolicy->block_after_time)->format('h:i A') : '10:00 AM',
                    'icon' => 'fa-ban',
                    'color' => 'danger'
                ],
                [
                    'label' => 'Half Day Minimum',
                    'value' => $defaultShift ? ($defaultShift->half_day_min_minutes . ' mins') : '240 mins',
                    'icon' => 'fa-adjust',
                    'color' => 'info'
                ],
                [
                    'label' => 'Required Work',
                    'value' => $defaultShift ? (number_format($defaultShift->required_work_minutes / 60, 1) . ' hours') : '8.0 hours',
                    'icon' => 'fa-business-time',
                    'color' => 'success'
                ],
                [
                    'label' => 'Missed Punch Policy',
                    'value' => $defaultPolicy ? ($defaultPolicy->allowed_missed_punches . ' Max') : '3 Max',
                    'icon' => 'fa-fingerprint',
                    'color' => 'purple'
                ]
            ];
        @endphp

        <div class="orb-summary-grid">
            @foreach($summaryCards as $card)
            <div class="orb-summary-card card-{{ $card['color'] }}">
                <div class="orb-summary-icon">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div>
                    <div class="orb-summary-label">
                        {{ $card['label'] }}
                    </div>
                    <div class="orb-summary-value">
                        {{ $card['value'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Shift Timing Card -->
        <div class="orb-card orb-table-card">
            <div class="orb-card-body">
                
                <div class="orb-table-header">
                    <div class="orb-table-head-left d-flex align-items-center" style="gap: 14px;">
                        <div class="orb-icon-box">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="orb-table-title">Shift Timings & Rules</h3>
                            <p class="orb-table-subtitle">Shift timing controls used by core attendance calculations.</p>
                        </div>
                    </div>

                    <div class="orb-table-head-right"></div>
                </div>

                <div class="att-table-wrap">
                    <div class="att-table-responsive">
                        <table class="att-table">
                            <thead>
                                <tr>
                                    <th>Shift Name</th>
                                    <th>Punch Allowed From</th>
                                    <th>Shift Start</th>
                                    <th>Late After</th>
                                    <th>Half Day After</th>
                                    <th>Shift End</th>
                                    <th>Required Minutes</th>
                                    <th>Half Day Min</th>
                                    <th>Lunch</th>
                                    <th>Default</th>
                                    <th>Active</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($attendanceTimes as $time)
                                    <tr>
                                        <td>
                                            <strong>{{ $time->name }}</strong>
                                            <div class="text-muted small">{{ $time->code }}</div>
                                        </td>

                                        <td>{{ \Carbon\Carbon::parse($time->punch_allowed_from)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($time->shift_start_time)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($time->late_after_time)->format('h:i A') }}</td>
                                        <td>{{ $time->half_day_after_time ? \Carbon\Carbon::parse($time->half_day_after_time)->format('h:i A') : '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($time->shift_end_time)->format('h:i A') }}</td>
                                        <td>{{ $time->required_work_minutes }} mins</td>
                                        <td>{{ $time->half_day_min_minutes }} mins</td>
                                        <td>{{ $time->lunch_break_minutes }} mins</td>

                                        <td>
                                            <span class="att-badge {{ $time->is_default ? 'badge-default' : 'badge-muted' }}">
                                                {{ $time->is_default ? 'Default' : 'No' }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="att-badge {{ $time->is_active ? 'badge-active' : 'badge-muted' }}">
                                                {{ $time->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>

                                        <td class="text-right">
                                            <button
                                                type="button"
                                                class="icon-btn"
                                                data-toggle="modal"
                                                data-target="#ruleModal{{ $time->id }}"
                                                title="Edit Rule">
                                                <i class="fas fa-edit text-primary"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-5">
                                            No attendance rules found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Policy Card -->
        <div class="orb-card orb-table-card mt-4">
            <div class="orb-card-body">
                
                <div class="orb-table-header">
                    <div class="orb-table-head-left d-flex align-items-center" style="gap: 14px;">
                        <div class="orb-icon-box">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <div>
                            <h3 class="orb-table-title">Attendance Policy Rules</h3>
                            <p class="orb-table-subtitle">Mobile app and automation policy values resolved per employee.</p>
                        </div>
                    </div>

                    <div class="orb-table-head-right">
                        <button type="button" class="orb-btn orb-btn-primary" data-toggle="modal" data-target="#createPolicyRuleModal" style="height: 34px; min-height: 34px; padding: 0 14px; border-radius: 11px; font-size: 12px;">
                            <i class="fas fa-plus"></i> Add Policy
                        </button>
                    </div>
                </div>

                <div class="att-table-wrap">
                    <div class="att-table-responsive">
                        <table class="att-table">
                            <thead>
                                <tr>
                                    <th>Policy Name</th>
                                    <th>Punch From</th>
                                    <th>Late After</th>
                                    <th>Warning After</th>
                                    <th>Block After</th>
                                    <th>Shift End</th>
                                    <th>Req. Work</th>
                                    <th>Half Day Min</th>
                                    <th>Absent Below</th>
                                    <th>Lunch</th>
                                    <th>Limits</th>
                                    <th>Automation</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendancePolicies ?? [] as $policy)
                                    <tr>
                                        <td>
                                            <strong>{{ $policy->policy_name }}</strong>
                                            <div class="text-muted small">#{{ $policy->id }}</div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($policy->punch_allowed_from)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($policy->late_after_time)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($policy->warning_after_time)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($policy->block_after_time)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($policy->shift_end_time)->format('h:i A') }}</td>
                                        <td>{{ $policy->required_work_minutes }} mins</td>
                                        <td>{{ $policy->half_day_min_minutes }} mins</td>
                                        <td>{{ $policy->absent_below_minutes }} mins</td>
                                        <td>{{ $policy->lunch_break_minutes }} mins</td>
                                        <td>{{ $policy->allowed_missed_punches }} missed / {{ $policy->combined_violation_limit }} total</td>
                                        <td>
                                            <span class="att-badge {{ $policy->auto_block_enabled ? 'badge-active' : 'badge-muted' }} mr-1">Block</span>
                                            <span class="att-badge {{ $policy->auto_absent_enabled ? 'badge-active' : 'badge-muted' }}">Absent</span>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="icon-btn" data-toggle="modal" data-target="#policyRuleModal{{ $policy->id }}" title="Edit Policy">
                                                <i class="fas fa-edit text-primary"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="13" class="text-center text-muted py-5">No attendance policies found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- Modals Outside Tables --}}
        @foreach($attendanceTimes as $time)
            <div class="modal fade orb-rule-modal" id="ruleModal{{ $time->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <form method="POST" action="{{ route('attendance.rules.update', $time) }}" class="modal-content att-modal-content">
                        @csrf
                        @method('PUT')

                        <div class="modal-header att-modal-header">
                            <div>
                                <h5 class="att-modal-title">Edit Shift Timing</h5>
                                <div class="att-modal-subtitle">{{ $time->name }} · {{ $time->code }}</div>
                            </div>

                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body att-modal-body">
                            <div class="att-modal-section">
                                <div class="att-modal-section-title">
                                    <i class="fas fa-clock"></i> Basic Shift Details
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Shift Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $time->name) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Punch Allowed From</label>
                                        <input type="time" name="punch_allowed_from" class="form-control" value="{{ \Carbon\Carbon::parse($time->punch_allowed_from)->format('H:i') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Shift Start</label>
                                        <input type="time" name="shift_start_time" class="form-control" value="{{ \Carbon\Carbon::parse($time->shift_start_time)->format('H:i') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Late After</label>
                                        <input type="time" name="late_after_time" class="form-control" value="{{ \Carbon\Carbon::parse($time->late_after_time)->format('H:i') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Half Day After</label>
                                        <input type="time" name="half_day_after_time" class="form-control" value="{{ $time->half_day_after_time ? \Carbon\Carbon::parse($time->half_day_after_time)->format('H:i') : '' }}">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Shift End</label>
                                        <input type="time" name="shift_end_time" class="form-control" value="{{ \Carbon\Carbon::parse($time->shift_end_time)->format('H:i') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Required Work Minutes</label>
                                        <input type="number" name="required_work_minutes" class="form-control" min="1" value="{{ old('required_work_minutes', $time->required_work_minutes) }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Half Day Min Minutes</label>
                                        <input type="number" name="half_day_min_minutes" class="form-control" min="1" value="{{ old('half_day_min_minutes', $time->half_day_min_minutes) }}" required>
                                    </div>

                                    <div class="col-md-4 mb-0">
                                        <label>Lunch Break Minutes</label>
                                        <input type="number" name="lunch_break_minutes" class="form-control" min="0" value="{{ old('lunch_break_minutes', $time->lunch_break_minutes) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="att-modal-section mb-0">
                                <div class="att-modal-section-title">
                                    <i class="fas fa-toggle-on"></i> Shift Status
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="default{{ $time->id }}" name="is_default" value="1" {{ $time->is_default ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="default{{ $time->id }}">
                                                Default Shift
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="active{{ $time->id }}" name="is_active" value="1" {{ $time->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="active{{ $time->id }}">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer att-modal-footer">
                            <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">
                                Cancel
                            </button>

                            <button class="orb-btn orb-btn-primary" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); color: #fff;">
                                <i class="fas fa-save"></i> Save Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        @php
            $policyFields = [
                ['policy_name', 'Policy Name', 'text'],
                ['punch_allowed_from', 'Punch Allowed From', 'time'],
                ['shift_start_time', 'Shift Start', 'time'],
                ['late_after_time', 'Late After', 'time'],
                ['warning_after_time', 'Warning After', 'time'],
                ['block_after_time', 'Block After', 'time'],
                ['shift_end_time', 'Shift End', 'time'],
                ['required_work_minutes', 'Required Work Minutes', 'number'],
                ['half_day_min_minutes', 'Half Day Min Minutes', 'number'],
                ['absent_below_minutes', 'Absent Below Minutes', 'number'],
                ['lunch_break_minutes', 'Lunch Break Minutes', 'number'],
                ['allowed_missed_punches', 'Allowed Missed Punches', 'number'],
                ['combined_violation_limit', 'Combined Violation Limit', 'number'],
                ['late_violation_limit', 'Late Violation Limit', 'number'],
                ['early_violation_limit', 'Early Violation Limit', 'number'],
            ];
            $policyDefaults = [
                'policy_name' => '',
                'punch_allowed_from' => '',
                'shift_start_time' => '',
                'late_after_time' => '',
                'warning_after_time' => '',
                'block_after_time' => '',
                'shift_end_time' => '',
                'required_work_minutes' => '',
                'half_day_min_minutes' => '',
                'absent_below_minutes' => '',
                'lunch_break_minutes' => '',
                'allowed_missed_punches' => '',
                'combined_violation_limit' => '',
                'late_violation_limit' => '',
                'early_violation_limit' => '',
                'auto_block_enabled' => true,
                'auto_absent_enabled' => true,
                'is_active' => true,
            ];
        @endphp

        @foreach($attendancePolicies ?? [] as $policy)
            <div class="modal fade orb-rule-modal" id="policyRuleModal{{ $policy->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <form method="POST" action="{{ route('attendance.policy_rules.update', $policy) }}" class="modal-content att-modal-content">
                        @csrf
                        @method('PUT')
                        <div class="modal-header att-modal-header">
                            <div>
                                <h5 class="att-modal-title">Edit Attendance Policy</h5>
                                <div class="att-modal-subtitle">{{ $policy->policy_name }}</div>
                            </div>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body att-modal-body">
                            <div class="att-modal-section">
                                <div class="row">
                                    @foreach($policyFields as [$field, $label, $type])
                                        <div class="col-md-4 mb-3">
                                            <label>{{ $label }}</label>
                                            <input type="{{ $type }}" name="{{ $field }}" class="form-control" value="{{ old($field, $type === 'time' && $policy->{$field} ? \Carbon\Carbon::parse($policy->{$field})->format('H:i') : $policy->{$field}) }}" {{ $type === 'number' ? 'min=0' : '' }} required>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="att-modal-section mb-0">
                                <div class="row">
                                    @foreach(['auto_block_enabled' => 'Auto Block', 'auto_absent_enabled' => 'Auto Absent', 'is_active' => 'Active'] as $field => $label)
                                        <div class="col-md-4 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="{{ $field }}{{ $policy->id }}" name="{{ $field }}" value="1" {{ $policy->{$field} ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold" for="{{ $field }}{{ $policy->id }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer att-modal-footer">
                            <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">Cancel</button>
                            <button class="orb-btn orb-btn-primary" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); color: #fff;"><i class="fas fa-save"></i> Save Policy</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        <div class="modal fade orb-rule-modal" id="createPolicyRuleModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('attendance.policy_rules.store') }}" class="modal-content att-modal-content">
                    @csrf
                    <div class="modal-header att-modal-header">
                        <div>
                            <h5 class="att-modal-title">Add Attendance Policy</h5>
                            <div class="att-modal-subtitle">Create a database-driven policy for employee assignment.</div>
                        </div>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body att-modal-body">
                        <div class="att-modal-section">
                            <div class="row">
                                @foreach($policyFields as [$field, $label, $type])
                                    <div class="col-md-4 mb-3">
                                        <label>{{ $label }}</label>
                                        <input type="{{ $type }}" name="{{ $field }}" class="form-control" value="{{ old($field, $policyDefaults[$field]) }}" {{ $type === 'number' ? 'min=0' : '' }} required>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="att-modal-section mb-0">
                            <div class="row">
                                @foreach(['auto_block_enabled' => 'Auto Block', 'auto_absent_enabled' => 'Auto Absent', 'is_active' => 'Active'] as $field => $label)
                                    <div class="col-md-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="new{{ $field }}" name="{{ $field }}" value="1" checked>
                                            <label class="custom-control-label font-weight-bold" for="new{{ $field }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer att-modal-footer">
                        <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">Cancel</button>
                        <button class="orb-btn orb-btn-primary" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); color: #fff;"><i class="fas fa-save"></i> Create Policy</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
