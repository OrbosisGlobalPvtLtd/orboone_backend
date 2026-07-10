@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Pending Profiles')

@section('_head')
@include('hrms.employee.partials.styles')
@endsection

@section('_content')
<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-card: #fff;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .pp-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .pp-container {
        max-width: 1280px;
        margin: 0 auto;
    }

    .pp-header {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        box-shadow: var(--orb-shadow);
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 14px;
    }

    .pp-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 24px;
        font-weight: 900;
    }

    .pp-subtitle {
        margin: 4px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .pp-header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pp-btn {
        border: 0;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .pp-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    .pp-chip {
        padding: 9px 13px;
        border-radius: 999px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .pp-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        margin-bottom: 14px;
    }

    .pp-stat {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        box-shadow: var(--orb-shadow);
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .pp-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pp-stat h4 {
        margin: 0;
        color: var(--orb-text);
        font-size: 20px;
        font-weight: 900;
        line-height: 1;
    }

    .pp-stat small {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 800;
    }

    .pp-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .pp-card-head {
        padding: 14px 16px;
        border-bottom: 1px solid #EEF1F6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .pp-card-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 15px;
        font-weight: 900;
    }

    .pp-card-sub {
        margin: 2px 0 0;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .pp-table-wrap {
        overflow-x: auto;
    }

    .pp-table {
        margin: 0;
        min-width: 1050px;
    }

    .pp-table thead th {
        border-top: 0;
        border-bottom: 1px solid #EEF1F6;
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: 12px 14px;
        white-space: nowrap;
    }

    .pp-table tbody td {
        border-top: 1px solid #F0F2F7;
        padding: 12px 14px;
        vertical-align: middle;
        font-size: 13px;
        font-weight: 650;
        color: #344054;
    }

    .pp-table tbody tr:hover {
        background: #FCFAFF;
    }

    .emp-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 230px;
    }

    .emp-avatar {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-weight: 900;
        flex: 0 0 auto;
        border: 1px solid rgba(75, 0, 232, 0.08) !important;
        overflow: hidden !important;
    }

    .emp-avatar img {
        width: 100% !important;
        height: 100% !important;
        border-radius: inherit !important;
        object-fit: cover !important;
        display: block !important;
    }

    .emp-name {
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 900;
    }

    .emp-email {
        color: var(--orb-muted);
        font-size: 11px;
        margin-top: 2px;
        font-weight: 700;
    }

    .code-badge,
    .status-badge,
    .lock-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 9px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .code-badge {
        background: #F4F2FF;
        color: var(--orb-primary);
    }

    .status-pending {
        background: #FFF4D6;
        color: #B54708;
    }

    .status-submitted {
        background: #E0F2FE;
        color: #0369A1;
    }

    .status-approved {
        background: #DCFCE7;
        color: #166534;
    }

    .status-rejected {
        background: #FEE2E2;
        color: #991B1B;
    }

    .lock-badge {
        background: #F2F4F7;
        color: #475467;
    }

    .complete-cell {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .complete-switch {
        position: relative;
        width: 46px;
        height: 25px;
        margin: 0;
    }

    .complete-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #E4E7EC;
        transition: .25s;
        border-radius: 999px;
    }

    .slider:before {
        content: "";
        position: absolute;
        height: 19px;
        width: 19px;
        left: 3px;
        top: 3px;
        background: #fff;
        transition: .25s;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .18);
    }

    .complete-switch input:checked+.slider {
        background: #16A34A;
    }

    .complete-switch input:checked+.slider:before {
        transform: translateX(21px);
    }

    .actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: nowrap;
    }

    .action-btn {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        transition: .18s ease;
        background: #F8FAFC;
        color: #667085;
    }

    .action-btn:hover {
        color: #fff;
        transform: translateY(-1px);
    }

    .action-view:hover {
        background: var(--orb-primary);
    }

    .action-edit:hover {
        background: #F79009;
    }

    .action-reject:hover {
        background: #F04438;
    }

    .complete-profile-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        color: #B54708;
        background: #FFF4D6;
        border: 1px solid rgba(181, 71, 8, 0.15);
        text-decoration: none !important;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .complete-profile-btn:hover {
        background: #F79009;
        color: #fff;
        border-color: #F79009;
    }

    .pp-mobile-list {
        display: none;
        padding: 12px;
        background: #F8FAFC;
    }

    .pp-mobile-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: var(--orb-shadow);
    }

    .pp-mobile-head {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .pp-mobile-info {
        flex: 1;
        min-width: 0;
    }

    .pp-mobile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 10px 0;
        border-top: 1px dashed #E4E7EC;
    }

    .pp-label {
        display: block;
        color: var(--orb-muted);
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .pp-value {
        color: var(--orb-text);
        font-size: 12px;
        font-weight: 750;
        word-break: break-word;
    }

    .pp-mobile-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px dashed #E4E7EC;
    }

    .empty-state {
        padding: 46px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 26px;
    }

    .empty-state h4 {
        margin: 0;
        color: var(--orb-text);
        font-weight: 900;
    }

    .empty-state p {
        margin: 7px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 650;
    }

    .confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 23, 42, .48);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .confirm-box {
        width: min(460px, 100%);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 25px 70px rgba(15, 23, 42, .22);
        padding: 22px;
    }

    .confirm-icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #16A34A;
        background: #DCFCE7;
        font-size: 22px;
        margin-bottom: 14px;
    }

    .confirm-icon.reject {
        color: #DC2626;
        background: #FEE2E2;
    }

    .confirm-box h4 {
        margin: 0;
        color: var(--orb-text);
        font-size: 18px;
        font-weight: 900;
    }

    .confirm-box p {
        margin: 8px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        line-height: 1.5;
        font-weight: 650;
    }

    .confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 18px;
    }

    .btn-cancel,
    .btn-confirm,
    .btn-reject {
        border: 0;
        border-radius: 13px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 900;
    }

    .btn-cancel {
        background: #F4F6FB;
        color: #111827;
        border: 1px solid #E5E7EB;
    }

    .btn-confirm {
        background: #16A34A;
        color: #fff;
    }

    .btn-reject {
        background: #DC2626;
        color: #fff;
    }

    .reject-textarea {
        width: 100%;
        min-height: 96px;
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        padding: 10px 12px;
        margin-top: 12px;
        font-size: 13px;
        font-weight: 650;
        outline: none;
    }

    .reject-textarea:focus {
        border-color: #F04438;
        box-shadow: 0 0 0 4px rgba(240, 68, 56, .08);
    }

    @media(max-width:991px) {
        .pp-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .pp-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .pp-desktop {
            display: none;
        }

        .pp-mobile-list {
            display: block;
        }
    }

    @media(max-width:575px) {
        .pp-page {
            padding: 10px 8px 24px;
        }

        .pp-title {
            font-size: 21px;
        }

        .pp-chip,
        .pp-btn {
            width: 100%;
            justify-content: center;
        }

        .pp-header-actions {
            width: 100%;
        }

        .pp-stats {
            grid-template-columns: 1fr;
        }

        .pp-mobile-grid {
            grid-template-columns: 1fr;
        }

        .confirm-actions {
            flex-direction: column;
        }

        .btn-cancel,
        .btn-confirm,
        .btn-reject {
            width: 100%;
        }
    }

    /* Modern Compact Metric Cards Overrides */
    .eo-page.pp-page {
        width: 100% !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    .pp-page .eo-stat-grid {
        display: grid !important;
        grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
        gap: 14px !important;
        margin-bottom: 24px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .pp-page .eo-stat {
        min-height: 110px !important;
        padding: 18px !important;
        border-radius: 20px !important;
        background: #fff !important;
        border: 1px solid var(--orb-border) !important;
        box-shadow: 0 14px 35px rgba(16, 24, 40, .07) !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        box-sizing: border-box !important;
        transition: all 0.3s ease !important;
    }

    .pp-page .eo-stat:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 18px 45px rgba(16, 24, 40, .1) !important;
    }

    .pp-page .eo-stat.border-bottom-primary {
        border-bottom: 4px solid var(--orb-primary) !important;
    }

    .pp-page .eo-stat.border-bottom-warning {
        border-bottom: 4px solid #F79009 !important;
    }

    .pp-page .eo-stat.border-bottom-info {
        border-bottom: 4px solid #15B79E !important;
    }

    .pp-page .eo-stat.border-bottom-success {
        border-bottom: 4px solid #12B76A !important;
    }

    .pp-page .eo-stat.border-bottom-danger {
        border-bottom: 4px solid #F04438 !important;
    }

    .pp-page .eo-stat-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        flex-shrink: 0 !important;
    }

    .pp-page .eo-stat-icon.primary {
        background: rgba(75, 0, 232, 0.08) !important;
        color: var(--orb-primary) !important;
    }

    .pp-page .eo-stat-icon.warning {
        background: rgba(247, 144, 9, 0.08) !important;
        color: #F79009 !important;
    }

    .pp-page .eo-stat-icon.info {
        background: rgba(21, 183, 158, 0.08) !important;
        color: #15B79E !important;
    }

    .pp-page .eo-stat-icon.success {
        background: rgba(18, 183, 106, 0.08) !important;
        color: #12B76A !important;
    }

    .pp-page .eo-stat-icon.danger {
        background: rgba(240, 68, 56, 0.08) !important;
        color: #F04438 !important;
    }

    .pp-page .eo-stat-label {
        margin: 0 0 4px 0 !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        color: var(--orb-muted) !important;
        line-height: 1.2 !important;
    }

    .pp-page .eo-stat-value {
        margin: 0 !important;
        font-size: 24px !important;
        font-weight: 900 !important;
        color: var(--orb-text) !important;
        line-height: 1 !important;
    }

    @media (max-width: 1400px) {
        .pp-page .eo-stat {
            padding: 14px 12px !important;
        }

        .pp-page .eo-stat-value {
            font-size: 20px !important;
        }
    }

    @media (max-width: 1200px) {
        .pp-page .eo-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 992px) {
        .pp-page .eo-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 576px) {
        .pp-page .eo-stat-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
        }
    }
</style>

<div class="eo-page pp-page">
    <div class="eo-container">

        @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        <div class="orb-page-header">
            <div class="orb-page-header-content">
                <div class="orb-page-kicker">
                    <i class="fas fa-user-clock"></i> HRMS &bull; Review
                </div>
                <h1 class="orb-page-title">Profile Review Management</h1>
                <p class="orb-page-subtitle">Pending, submitted and rejected profiles appear here. Approved profiles move to Employee Directory.</p>
            </div>

            <div class="orb-page-actions">
                @if (Route::has('hrms.employees.index'))
                <a href="{{ route('hrms.employees.index') }}" class="orb-btn-light">
                    <i class="fas fa-users"></i> Employee Directory
                </a>
                @endif
                <div class="orb-btn-light" style="pointer-events: none; opacity: 0.85;"><i class="fas fa-lock mr-1"></i> Approved profiles are hidden</div>
            </div>
        </div>

        <div class="eo-stat-grid">
            <div class="eo-stat border-bottom-primary">
                <div class="eo-stat-icon primary"><i class="fas fa-users"></i></div>
                <div>
                    <p class="eo-stat-label">Total Active</p>
                    <h3 class="eo-stat-value">{{ $total ?? 0 }}</h3>
                </div>
            </div>
            <div class="eo-stat border-bottom-warning">
                <div class="eo-stat-icon warning"><i class="fas fa-clock"></i></div>
                <div>
                    <p class="eo-stat-label">Pending</p>
                    <h3 class="eo-stat-value">{{ $pending ?? 0 }}</h3>
                </div>
            </div>
            <div class="eo-stat border-bottom-info">
                <div class="eo-stat-icon info"><i class="fas fa-paper-plane"></i></div>
                <div>
                    <p class="eo-stat-label">Submitted</p>
                    <h3 class="eo-stat-value">{{ $submitted ?? 0 }}</h3>
                </div>
            </div>
            <div class="eo-stat border-bottom-success">
                <div class="eo-stat-icon success"><i class="fas fa-check-circle"></i></div>
                <div>
                    <p class="eo-stat-label">Approved</p>
                    <h3 class="eo-stat-value">{{ $approved ?? 0 }}</h3>
                </div>
            </div>
            <div class="eo-stat border-bottom-danger">
                <div class="eo-stat-icon danger"><i class="fas fa-times-circle"></i></div>
                <div>
                    <p class="eo-stat-label">Rejected</p>
                    <h3 class="eo-stat-value">{{ $rejected ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="orb-table-card">
            <div class="orb-table-toolbar">
                <div>
                    <h5 class="pp-card-title m-0" style="font-size:18px; font-weight:900; color:var(--orb-text);">Employee Profiles</h5>
                    <p class="pp-card-sub m-0 mt-1" style="font-size:13px; color:var(--orb-muted);">Approve completed profiles or reject with reason for correction.</p>
                </div>
            </div>

            @if ($employees->count())
            <div class="pp-desktop pp-table-wrap">
                <table class="table pp-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th class="text-center">Approve</th>
                            <!-- <th>Complete Profile</th> -->
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $emp)
                        @php
                        $status = $emp->profile_status ?? 'pending';
                        $isCompleted = (int) ($emp->is_profile_completed ?? 0) === 1;
                        $statusClass = match ($status) {
                        'submitted' => 'status-submitted',
                        'approved' => 'status-approved',
                        'rejected' => 'status-rejected',
                        default => 'status-pending',
                        };
                        $statusLabel = match ($status) {
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => 'Pending',
                        };
                        $initial = resolveEmployeeInitials($emp);
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="emp-cell">
                                    @php
                                    $passportPhotoUrl = resolveEmployeePassportPhoto($emp);
                                    $employeeInitial = $initial;
                                    $employeeName = $emp->name ?? 'Employee';
                                    @endphp
                                    <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                        @if($passportPhotoUrl)
                                        <img
                                            src="{{ $passportPhotoUrl }}"
                                            alt="{{ $employeeName }}"
                                            class="hrms-emp-avatar-img"
                                            onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');">
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
                                        <div class="emp-name">{{ $emp->name ?? '-' }}</div>
                                        <div class="emp-email">{{ $emp->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="code-badge">{{ $emp->employee_code ?? '-' }}</span></td>
                            <td>{{ $emp->department_name ?? '-' }}</td>
                            <td>{{ $emp->designation_name ?? '-' }}</td>
                            <td><span class="status-badge {{ $statusClass }}"><i
                                        class="fas fa-circle"></i>{{ $statusLabel }}</span></td>
                            <td>
                                <div class="complete-cell">
                                    @if ($status === 'approved')
                                    <span class="lock-badge"><i class="fas fa-lock"></i> Approved</span>
                                    @elseif ($status === 'submitted')
                                    <label class="complete-switch" title="Approve Profile">
                                        <input type="checkbox" class="profile-approve-toggle"
                                            data-form-id="approveForm{{ $emp->id }}">
                                        <span class="slider"></span>
                                    </label>

                                    <form id="approveForm{{ $emp->id }}"
                                        action="{{ route('hrms.employees.profile.approve', $emp->id) }}"
                                        method="POST" class="d-none">
                                        @csrf
                                    </form>
                                    @else
                                    <span class="lock-badge"><i class="fas fa-clock"></i> Pending</span>
                                    @endif
                                </div>
                            </td>
                            <!-- <td>
                                @if ($status !== 'approved')
                                <a href="{{ route('hrms.employees.profile.edit', $emp->id) }}" class="complete-profile-btn" title="Complete Profile">
                                    <i class="fas fa-user-edit mr-1"></i> Complete Profile
                                </a>
                                @else
                                <span class="text-muted" style="font-size: 11px; font-weight: 750;"><i class="fas fa-check-circle text-success mr-1"></i> Completed</span>
                                @endif
                            </td> -->
                            <td>
                                @if (!empty($emp->updated_at))
                                {{ \Carbon\Carbon::parse($emp->updated_at)->diffForHumans() }}
                                @else
                                <span class="text-muted">Not updated</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('hrms.employees.profile.view', $emp->id) }}"
                                        class="action-btn action-view" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if (Route::has('hrms.employees.edit'))
                                    <a href="{{ route('hrms.employees.edit', $emp->id) }}"
                                        class="action-btn action-edit" title="Edit Onboarding">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif

                                    @if ($status !== 'approved')
                                    @if (Route::has('hrms.employees.profile.reject'))
                                    <button type="button"
                                        class="action-btn action-reject reject-profile-btn"
                                        title="Reject Profile"
                                        data-form-id="rejectForm{{ $emp->id }}">
                                        <i class="fas fa-times"></i>
                                    </button>

                                    <form id="rejectForm{{ $emp->id }}"
                                        action="{{ route('hrms.employees.profile.reject', $emp->id) }}"
                                        method="POST" class="d-none">
                                        @csrf
                                        <input type="hidden" name="rejection_reason"
                                            class="reject-reason-input">
                                    </form>
                                    @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pp-mobile-list">
                @foreach ($employees as $emp)
                @php
                $status = $emp->profile_status ?? 'pending';
                $isCompleted = (int) ($emp->is_profile_completed ?? 0) === 1;
                $statusClass = match ($status) {
                'submitted' => 'status-submitted',
                'approved' => 'status-approved',
                'rejected' => 'status-rejected',
                default => 'status-pending',
                };
                $statusLabel = match ($status) {
                'submitted' => 'Submitted',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default => 'Pending',
                };
                $initial = resolveEmployeeInitials($emp);
                @endphp

                <div class="pp-mobile-card">
                    <div class="pp-mobile-head">
                        @php
                        $passportPhotoUrl = resolveEmployeePassportPhoto($emp);
                        $employeeInitial = $initial;
                        $employeeName = $emp->name ?? 'Employee';
                        @endphp
                        <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                            @if($passportPhotoUrl)
                            <img
                                src="{{ $passportPhotoUrl }}"
                                alt="{{ $employeeName }}"
                                class="hrms-emp-avatar-img"
                                onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');">
                            <span class="hrms-emp-avatar-fallback is-hidden">
                                {{ $employeeInitial }}
                            </span>
                            @else
                            <span class="hrms-emp-avatar-fallback is-visible">
                                {{ $employeeInitial }}
                            </span>
                            @endif
                        </span>
                        <div class="pp-mobile-info">
                            <div class="emp-name">{{ $emp->name ?? '-' }}</div>
                            <div class="emp-email">{{ $emp->email ?? '-' }}</div>
                            <div class="mt-2"><span
                                    class="code-badge">{{ $emp->employee_code ?? '-' }}</span></div>
                        </div>
                        <span class="status-badge {{ $statusClass }}"><i
                                class="fas fa-circle"></i>{{ $statusLabel }}</span>
                    </div>

                    <div class="pp-mobile-grid">
                        <div><span class="pp-label">Department</span><span
                                class="pp-value">{{ $emp->department_name ?? '-' }}</span></div>
                        <div><span class="pp-label">Designation</span><span
                                class="pp-value">{{ $emp->designation_name ?? '-' }}</span></div>
                        <div><span class="pp-label">Updated</span><span
                                class="pp-value">{{ !empty($emp->updated_at) ? \Carbon\Carbon::parse($emp->updated_at)->diffForHumans() : 'Not updated' }}</span>
                        </div>
                        <div>
                            <span class="pp-label">Approve</span>

                            @if ($status === 'approved')
                            <span class="lock-badge"><i class="fas fa-lock"></i> Approved</span>
                            @elseif ($status === 'submitted')
                            <label class="complete-switch" title="Approve Profile">
                                <input type="checkbox" class="profile-approve-toggle"
                                    data-form-id="approveMobileForm{{ $emp->id }}">
                                <span class="slider"></span>
                            </label>

                            <form id="approveMobileForm{{ $emp->id }}"
                                action="{{ route('hrms.employees.profile.approve', $emp->id) }}"
                                method="POST" class="d-none">
                                @csrf
                            </form>
                            @else
                            <span class="lock-badge"><i class="fas fa-clock"></i> Pending</span>
                            @endif
                        </div>
                    </div>

                    <div class="pp-mobile-actions">
                        <div class="actions">
                            <a href="{{ route('hrms.employees.profile.view', $emp->id) }}"
                                class="action-btn action-view" title="View Profile">
                                <i class="fas fa-eye"></i>
                            </a>

                            @if (Route::has('hrms.employees.edit'))
                            <a href="{{ route('hrms.employees.edit', $emp->id) }}"
                                class="action-btn action-edit" title="Edit Onboarding">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif

                            @if ($status !== 'approved')
                            <a href="{{ route('hrms.employees.profile.edit', $emp->id) }}"
                                class="action-btn text-warning" style="background: rgba(247, 144, 9, 0.08);" title="Complete Profile">
                                <i class="fas fa-user-edit"></i>
                            </a>

                            @if (Route::has('hrms.employees.profile.reject'))
                            <button type="button" class="action-btn action-reject reject-profile-btn"
                                title="Reject Profile"
                                data-form-id="rejectMobileForm{{ $emp->id }}">
                                <i class="fas fa-times"></i>
                            </button>

                            <form id="rejectMobileForm{{ $emp->id }}"
                                action="{{ route('hrms.employees.profile.reject', $emp->id) }}"
                                method="POST" class="d-none">
                                @csrf
                                <input type="hidden" name="rejection_reason"
                                    class="reject-reason-input">
                            </form>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-user-check"></i></div>
                <h4>All profiles are reviewed</h4>
                <p>No pending, submitted or rejected profile found.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="confirm-modal" id="approveModal">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="fas fa-check"></i></div>
        <h4>Approve this profile?</h4>
        <p>After approval, this employee will appear in Employee Directory and this profile will be removed from Pending
            Profiles.</p>
        <div class="confirm-actions">
            <button type="button" class="btn-cancel" id="cancelApprove">Cancel</button>
            <button type="button" class="btn-confirm" id="confirmApprove">Yes, Approve</button>
        </div>
    </div>
</div>

<div class="confirm-modal" id="rejectModal">
    <div class="confirm-box">
        <div class="confirm-icon reject"><i class="fas fa-times"></i></div>
        <h4>Reject this profile?</h4>
        <p>Add a reason so the employee/HR can correct the profile details.</p>
        <textarea id="rejectReasonBox" class="reject-textarea" placeholder="Enter rejection reason..."></textarea>
        <div class="confirm-actions">
            <button type="button" class="btn-cancel" id="cancelReject">Cancel</button>
            <button type="button" class="btn-reject" id="confirmReject">Reject Profile</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let selectedApproveForm = null;
        let selectedRejectForm = null;

        const approveModal = document.getElementById('approveModal');
        const rejectModal = document.getElementById('rejectModal');

        function resetApproveToggles() {
            document.querySelectorAll('.profile-approve-toggle').forEach(toggle => toggle.checked = false);
        }

        function closeApprove() {
            selectedApproveForm = null;
            approveModal.style.display = 'none';
            resetApproveToggles();
        }

        function closeReject() {
            selectedRejectForm = null;
            rejectModal.style.display = 'none';
            document.getElementById('rejectReasonBox').value = '';
        }

        document.querySelectorAll('.profile-approve-toggle').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                if (this.checked) {
                    selectedApproveForm = document.getElementById(this.getAttribute(
                        'data-form-id'));
                    approveModal.style.display = 'flex';
                }
            });
        });

        document.querySelectorAll('.reject-profile-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                selectedRejectForm = document.getElementById(this.getAttribute('data-form-id'));
                rejectModal.style.display = 'flex';
                setTimeout(() => document.getElementById('rejectReasonBox').focus(), 100);
            });
        });

        document.getElementById('cancelApprove').addEventListener('click', closeApprove);
        document.getElementById('confirmApprove').addEventListener('click', function() {
            if (selectedApproveForm) selectedApproveForm.submit();
        });

        document.getElementById('cancelReject').addEventListener('click', closeReject);
        document.getElementById('confirmReject').addEventListener('click', function() {
            if (!selectedRejectForm) return;

            const reason = document.getElementById('rejectReasonBox').value.trim() ||
                'Profile rejected by HR';
            selectedRejectForm.querySelector('.reject-reason-input').value = reason;
            selectedRejectForm.submit();
        });

        approveModal.addEventListener('click', function(e) {
            if (e.target === approveModal) closeApprove();
        });

        rejectModal.addEventListener('click', function(e) {
            if (e.target === rejectModal) closeReject();
        });
    });
</script>
@endsection