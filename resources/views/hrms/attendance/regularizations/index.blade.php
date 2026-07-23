@extends('layouts.panel', [
    'accesses' => $accesses ?? [],
    'active' => $active ?? 'hrms'
])

@section('page_title', $pageTitle ?? 'Attendance Regularizations')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
:root {

    --orb-primary-hover: #3A00B7;

    --orb-bg: #F8FAFC;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: #F1EDFF;
    --orb-shadow: 0 10px 30px -10px rgba(75, 0, 232, 0.08);
}

body {
    overflow-x: hidden !important;
}

.att-page {
    min-height: calc(100vh - 90px);
    background: var(--orb-bg);
    padding: 24px 20px 48px;
}

.att-container {
    max-width: 1480px;
    margin: 0 auto;
}

.att-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px !important;
    padding: 36px;
    margin-bottom: 24px;
    box-shadow: 0 20px 50px rgba(75, 0, 232, 0.15);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
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
    background: rgba(255, 255, 255, 0.1);
    pointer-events: none;
}

.att-kicker {
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    opacity: 0.9;
    margin-bottom: 8px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.att-title {
    font-size: 32px;
    font-weight: 900;
    margin: 0;
    line-height: 1.2;
    color: #fff;
}

.att-subtitle {
    font-size: 15px;
    opacity: 0.9;
    margin-top: 8px;
    max-width: 800px;
}

.att-hero-actions {
    display: flex;
    gap: 12px;
    position: relative;
    z-index: 1;
}

.att-btn {
    border-radius: 12px;
    padding: 12px 20px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none !important;
    white-space: nowrap;
    border: 0;
    cursor: pointer;
    transition: all 0.2s ease;
}

.att-btn-light {
    background: #fff;
    color: var(--orb-primary) !important;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
}

.att-btn-light:hover {
    background: var(--orb-soft);
    transform: translateY(-1px);
}

.att-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 20px !important;
    box-shadow: var(--orb-shadow);
    overflow: hidden !important;
}

.att-section-head {
    padding: 24px;
    border-bottom: 1px solid var(--orb-border);
    background: linear-gradient(180deg, #fff, #FCFDFF);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.att-section-title {
    font-size: 20px;
    font-weight: 900;
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
    margin-top: 4px;
}

.att-head-badges {
    display: flex;
    gap: 10px;
}

.att-total-pill {
    border: 1px solid var(--orb-border);
    background: #F8FAFC;
    color: var(--orb-text);
    border-radius: 12px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.att-total-pill.purple {
    border-color: #E2D9FF;
    background: #F5F1FF;
    color: var(--orb-primary);
}

.att-filter-panel {
    padding: 20px 24px;
    border-bottom: 1px solid var(--orb-border);
    background: #fff;
}

.att-filter-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 16px;
    align-items: end;
}

.att-filter-grid label {
    font-size: 11px;
    font-weight: 800;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
    display: block;
}

.att-filter-grid .form-control,
.att-filter-grid .custom-select {
    height: 44px;
    border-radius: 12px;
    border: 1px solid var(--orb-border);
    font-size: 13px;
    font-weight: 600;
    padding: 0 14px;
    box-shadow: none !important;
    background: #fff;
    width: 100%;
}

.att-filter-grid .form-control:focus,
.att-filter-grid .custom-select:focus {
    border-color: var(--orb-primary);
}

.att-table-wrap {
    padding: 16px;
}

.att-table-responsive {
    width: 100% !important;
    overflow-x: auto !important;
}

.att-table {
    width: 100% !important;
    border-collapse: collapse !important;
}

.att-table thead th {
    background: #F8FAFC !important;
    color: var(--orb-muted) !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    padding: 16px 14px !important;
    border-top: 1px solid var(--orb-border) !important;
    border-bottom: 1px solid var(--orb-border) !important;
    white-space: nowrap;
}

.att-table td {
    background: #fff;
    border-bottom: 1px solid #F1F5F9 !important;
    padding: 16px 14px !important;
    vertical-align: middle;
    font-size: 14px;
    color: var(--orb-text);
}

.att-table tbody tr {
    transition: 0.2s ease;
}

.att-table tbody tr:hover td {
    background: #FAF9FF;
}

.orb-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    border: 1px solid transparent;
}

.orb-badge-success {
    background: #ECFDF3;
    color: #027A48;
    border-color: #ABEFC6;
}

.orb-badge-warning {
    background: #FFFAEB;
    color: #B54708;
    border-color: #FEDF89;
}

.orb-badge-danger {
    background: #FEF3F2;
    color: #B42318;
    border-color: #FECDCA;
}

.orb-badge-secondary {
    background: #F2F4F7;
    color: #344054;
    border-color: #D0D5DD;
}

.orb-badge-primary {
    background: var(--orb-soft);
    color: var(--orb-primary);
    border-color: rgba(75, 0, 232, 0.15);
}

.orb-action-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid var(--orb-border);
    background: #fff;
    color: var(--orb-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.orb-action-btn:hover {
    background: var(--orb-soft);
    color: var(--orb-primary);
    border-color: rgba(75, 0, 232, 0.2);
}

.avatar-table {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    min-height: 36px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
    border: 2px solid #E2E8F0;
}

.avatar-modal {
    width: 48px !important;
    height: 48px !important;
    min-width: 48px !important;
    min-height: 48px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
    border: 2px solid rgba(255, 255, 255, 0.6);
}

/* Glassmorphism View Details Modal styling */
.glass-modal .modal-content {
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(20px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
    border: 1px solid rgba(255, 255, 255, 0.5) !important;
    border-radius: 24px !important;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.15) !important;
}

.glass-modal .modal-header {
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
    border: 0 !important;
    color: #fff !important;
    padding: 24px !important;
}

.glass-modal .modal-title {
    color: #fff !important;
    font-weight: 900 !important;
    font-size: 20px !important;
}

.glass-modal .close {
    color: #fff !important;
    opacity: 0.8;
}

.glass-modal .close:hover {
    opacity: 1;
}

.glass-modal .modal-body {
    padding: 24px !important;
    background: rgba(255, 255, 255, 0.4) !important;
}

.detail-card {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.detail-card-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 12px;
    border-bottom: 1px solid #EDF2F7;
    padding-bottom: 6px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #F1F5F9;
}

.detail-row:last-child {
    border-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: var(--orb-muted);
    font-size: 13px;
}

.detail-value {
    font-weight: 800;
    color: var(--orb-text);
    font-size: 13px;
}

.timeline-item {
    position: relative;
    padding-left: 28px;
    padding-bottom: 16px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 6px;
    bottom: 0;
    width: 2px;
    background: #E2E8F0;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-item::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 6px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--orb-primary);
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px var(--orb-soft);
}

.timeline-item.success::after {
    background: #10B981;
    box-shadow: 0 0 0 2px #D1FAE5;
}

.timeline-item.danger::after {
    background: #EF4444;
    box-shadow: 0 0 0 2px #FEE2E2;
}

.timeline-label {
    font-weight: 800;
    font-size: 13px;
    color: var(--orb-text);
}

.timeline-time {
    font-size: 11px;
    color: var(--orb-muted);
}

@media(max-width: 1300px) {
    .att-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media(max-width: 768px) {
    .att-page {
        padding: 16px 12px 32px;
    }

    .att-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 24px;
        border-radius: 20px;
    }

    .att-title {
        font-size: 26px;
    }

    .att-hero-actions {
        width: 100%;
    }

    .att-btn {
        width: 100%;
    }

    .att-section-head {
        flex-direction: column;
        align-items: flex-start;
        padding: 18px 24px;
    }

    .att-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media(max-width: 480px) {
    .att-filter-grid {
        grid-template-columns: 1fr;
    }
}

/* Custom modal layout and compact columns adjustments */
.glass-modal .modal-dialog {
    max-width: 900px !important;
    width: 90% !important;
    margin: 1.75rem auto !important;
}
.glass-modal .modal-content {
    max-height: 82vh !important;
    overflow: hidden !important;
    height: auto !important;
    border-radius: 24px !important;
}
.glass-modal .modal-header {
    padding: 14px 20px !important;
}
.glass-modal .modal-body {
    max-height: calc(82vh - 120px) !important;
    overflow-y: auto !important;
    padding: 20px !important;
}
.att-table-responsive {
    width: 100% !important;
    overflow-x: visible !important;
    overflow-y: visible !important;
}
.dataTables_wrapper {
    width: 100% !important;
    overflow-x: visible !important;
}
.dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    width: 100% !important;
}
.dataTables_paginate,
.dataTables_info,
.regularization-pagination {
    overflow-x: hidden !important;
}
.dt-buttons.btn-group {
    display: inline-flex !important;
    gap: 8px !important;
    float: right !important;
}
.dt-buttons .btn {
    border-radius: 10px !important;
    padding: 6px 14px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    border: 1px solid var(--orb-border) !important;
    background: #fff !important;
    color: var(--orb-text) !important;
    box-shadow: none !important;
}
.dt-buttons .btn:hover {
    background: var(--orb-soft) !important;
    color: var(--orb-primary) !important;
}
.dataTables_length {
    float: left !important;
    margin-bottom: 0 !important;
}
.dataTables_length select {
    height: 34px !important;
    border-radius: 8px !important;
    border: 1px solid var(--orb-border) !important;
    padding: 0 8px !important;
}
.dataTables_info {
    padding-top: 8px !important;
}
</style>
@endsection

@section('_content')
<div class="att-page">
    <div class="att-container">

        @php
            $currentUser = auth()->user();
            $canApprove = $currentUser && method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission('attendance.regularization.approve');
            $canReject = $currentUser && method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission('attendance.regularization.reject');
            $canViewAll = $currentUser && (method_exists($currentUser, 'isSuperAdmin') && $currentUser->isSuperAdmin() || (method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission('attendance.regularization.view_all')));
            $canViewTeam = $currentUser && method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission('attendance.regularization.view_team');

            $typeLabels = [
                'missed_punch_in' => 'Missed Punch In',
                'missed_punch_out' => 'Missed Punch Out',
                'wrong_punch_time' => 'Punch Time Correction',
                'punch_time_correction' => 'Punch Time Correction',
                'late_mark_exemption' => 'Late Mark Exemption',
                'early_logout_correction' => 'Early Logout Exemption',
                'early_logout_exemption' => 'Early Logout Exemption',
                'geofence_issue' => 'Geofence Issue',
                'system_error' => 'System/App Error',
                'attendance_status_correction' => 'Attendance Status Correction',
                'other' => 'Other',
            ];
        @endphp

        <div class="att-hero">
            <div>
                <div class="att-kicker"><i class="fas fa-calendar-check"></i> HRMS &bull; ATTENDANCE</div>
                <h3 class="att-title">{{ $pageTitle ?? 'Attendance Regularizations' }}</h3>
                <div class="att-subtitle">{{ $pageSubtitle ?? 'Manage missed punch, correction and regularization requests.' }}</div>
            </div>
            <div class="att-hero-actions">
                @if(!empty($canCreate))
                <button type="button" class="att-btn att-btn-light font-weight-bold" data-toggle="modal" data-target="#createModal">
                    <i class="fas fa-plus"></i> Apply Regularization
                </button>
                @endif
            </div>
        </div>

        @if(session('success') || session('status'))
            <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') ?: session('status') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
            </div>
        @endif

        <div class="att-card">
            <div class="att-section-head">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-history"></i> Regularization Logs</h5>
                    <div class="att-section-sub">Track correction requests, employee submissions, and approval status logs.</div>
                </div>
                <div class="att-head-badges align-items-center">
                    <span class="att-total-pill purple"><i class="fas fa-list"></i> Total Requests: {{ optional($rows)->total() ?? collect($rows)->count() }}</span>
                </div>
            </div>

            @if(!empty($filters))
            <div class="att-filter-panel">
                <form method="GET" id="filterForm">
                    <div class="att-filter-grid">
                        @foreach($filters as $filter)
                            <div>
                                <label>{{ $filter['label'] }}</label>
                                @if(($filter['type'] ?? 'text') === 'select')
                                    <select name="{{ $filter['name'] }}" class="form-control js-auto-filter">
                                        <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                                        @foreach($filter['options'] as $value => $label)
                                            @php
                                                $displayLabel = $label;
                                                if ($filter['name'] === 'request_type') {
                                                    $displayLabel = $typeLabels[$value] ?? ucfirst(str_replace('_', ' ', $value));
                                                }
                                            @endphp
                                            <option value="{{ $value }}" {{ (string) request($filter['name']) === (string) $value ? 'selected' : '' }}>
                                                {{ $displayLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $filter['type'] ?? 'text' }}" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}" class="form-control js-auto-filter" placeholder="{{ $filter['placeholder'] ?? '' }}">
                                @endif
                            </div>
                        @endforeach
                        <div>
                            <a href="{{ url()->current() }}" class="att-btn att-btn-light w-100 justify-content-center" style="height: 44px !important; border-radius: 12px !important; font-size: 13px !important; font-weight: 800 !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; border: 1px solid var(--orb-border) !important;">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <div class="att-table-wrap">
                <div class="att-table-responsive">
                    <table class="att-table table table-hover js-orb-datatable" id="regularizationDataTable">
                        <thead>
                            <tr>
                                <th style="width: 50px; min-width: 50px;">S.No.</th>
                                <th style="width: 220px; min-width: 220px;">Employee</th>
                                <th style="width: 90px; min-width: 90px;">Code</th>
                                <th style="width: 95px; min-width: 95px;">Date</th>
                                <th style="width: 130px; min-width: 130px;">Request Type</th>
                                <th style="width: 90px; min-width: 90px;">Current In</th>
                                <th style="width: 90px; min-width: 90px;">Current Out</th>
                                <th style="width: 100px; min-width: 100px;">Requested In</th>
                                <th style="width: 110px; min-width: 110px;">Requested Out</th>
                                <th style="width: 160px; min-width: 160px;">Reason</th>
                                <th style="width: 100px; min-width: 100px;">Status</th>
                                <th style="width: 120px; min-width: 120px;">Submitted At</th>
                                <th style="width: 80px; min-width: 80px;" class="text-right no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php
                                    $photoUrl = resolveEmployeePassportPhoto($row->employee_id);
                                    $initials = resolveEmployeeInitials($row->employee_id);
                                    $emp = \App\Models\HRMS\Employee\EmployeeM::with(['department', 'designation', 'profile'])->find($row->employee_id);
                                    $deptName = $emp?->department?->name ?? 'N/A';
                                    $desigName = $emp?->designation?->name ?? 'N/A';
                                    
                                    $attendanceRecord = $row->attendance_id ? \App\Models\HRMS\Attendance\AttendanceM::find($row->attendance_id) : null;
                                    $currentStatusText = $attendanceRecord ? resolve(\App\Services\HRMS\Attendance\AttendanceS::class)->resolveFinalStatus($attendanceRecord)['status_name'] : 'N/A';
                                    
                                    $type = $row->request_type;
                                    
                                    $currentInText = 'N/A';
                                    $currentOutText = 'N/A';
                                    $requestedInText = 'N/A';
                                    $requestedOutText = 'N/A';
                                    
                                    if ($type === 'missed_punch_in') {
                                        $currentOutText = $row->existing_punch_out ? \Carbon\Carbon::parse($row->existing_punch_out)->format('h:i A') : 'N/A';
                                        $requestedInText = $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('h:i A') : 'N/A';
                                    } elseif ($type === 'missed_punch_out') {
                                        $currentInText = $row->existing_punch_in ? \Carbon\Carbon::parse($row->existing_punch_in)->format('h:i A') : 'N/A';
                                        $requestedOutText = $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('h:i A') : 'N/A';
                                    } elseif ($type === 'wrong_punch_time' || $type === 'punch_time_correction') {
                                        $currentInText = $row->existing_punch_in ? \Carbon\Carbon::parse($row->existing_punch_in)->format('h:i A') : 'N/A';
                                        $currentOutText = $row->existing_punch_out ? \Carbon\Carbon::parse($row->existing_punch_out)->format('h:i A') : 'N/A';
                                        $requestedInText = $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('h:i A') : 'N/A';
                                        $requestedOutText = $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('h:i A') : 'N/A';
                                    } elseif ($type === 'late_mark_exemption') {
                                        $currentInText = $row->existing_punch_in ? \Carbon\Carbon::parse($row->existing_punch_in)->format('h:i A') : 'N/A';
                                    } elseif ($type === 'early_logout_correction' || $type === 'early_logout_exemption') {
                                        $currentOutText = $row->existing_punch_out ? \Carbon\Carbon::parse($row->existing_punch_out)->format('h:i A') : 'N/A';
                                    } elseif ($type === 'other' && str_contains($row->reason, '[Attendance Status Correction:')) {
                                        $currentInText = $currentStatusText;
                                        preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                                        $requestedInText = $matches[1] ?? 'Correction';
                                    }
                                @endphp
                                <tr>
                                    <td style="white-space: nowrap;"><strong>{{ (($rows->currentPage() - 1) * $rows->perPage()) + $loop->iteration }}</strong></td>
                                    <td style="white-space: nowrap;">
                                        <div class="d-flex align-items-center">
                                            @if($photoUrl)
                                                <img src="{{ $photoUrl }}" class="avatar-table rounded-circle" alt="">
                                            @else
                                                <div class="avatar-table rounded-circle d-inline-flex align-items-center justify-content-center" style="background: var(--orb-soft); color: var(--orb-primary); font-weight: 900; font-size: 14px;">
                                                    {{ $initials }}
                                                </div>
                                            @endif
                                            <div class="ml-3">
                                                <div class="font-weight-bold text-dark">{{ $row->employee_display_name ?? $row->name }}</div>
                                                <div class="text-muted small">{{ $deptName }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;"><code>{{ $row->employee_code }}</code></td>
                                    <td style="white-space: nowrap;">
                                        @php
                                            $rowDate = $row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at));
                                        @endphp
                                        <span class="font-weight-bold">{{ \Carbon\Carbon::parse($rowDate)->format('d M Y') }}</span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <span class="orb-badge orb-badge-primary">
                                            {{ $typeLabels[$row->request_type] ?? ucfirst(str_replace('_', ' ', $row->request_type)) }}
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;"><span class="font-weight-bold">{{ $currentInText }}</span></td>
                                    <td style="white-space: nowrap;"><span class="font-weight-bold">{{ $currentOutText }}</span></td>
                                    <td style="white-space: nowrap;"><span class="text-primary font-weight-bold">{{ $requestedInText }}</span></td>
                                    <td style="white-space: nowrap;"><span class="text-primary font-weight-bold">{{ $requestedOutText }}</span></td>
                                    <td style="max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <span title="{{ $row->reason }}" data-toggle="tooltip" style="cursor: help;">
                                            {{ Str::limit(str_replace(['[Attendance Status Correction: Present]', '[Attendance Status Correction: Half Day]', '[Attendance Status Correction: Absent]', '[Attendance Status Correction: present]', '[Attendance Status Correction: half_day]', '[Attendance Status Correction: absent]'], '', $row->reason), 35) }}
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        @php
                                            $badge = $row->status === 'approved'
                                                ? 'orb-badge-success'
                                                : ($row->status === 'pending'
                                                    ? 'orb-badge-warning'
                                                    : ($row->status === 'rejected'
                                                        ? 'orb-badge-danger'
                                                        : 'orb-badge-secondary'));
                                        @endphp
                                        <span class="orb-badge {{ $badge }}">
                                            {{ ucfirst((string) $row->status) }}
                                        </span>
                                    </td>
                                    <td class="small">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y h:i A') }}</td>
                                    <td class="text-right no-export">
                                        <div class="dropdown">
                                            <button class="orb-action-btn" type="button" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <button type="button" class="dropdown-item" data-toggle="modal" data-target="#viewModal{{ $row->id }}">
                                                    <i class="fas fa-eye mr-2 text-info"></i> View Details
                                                </button>

                                                @if($row->status === 'pending')
                                                    @if($canApprove)
                                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#approveModal{{ $row->id }}">
                                                            <i class="fas fa-check mr-2 text-success"></i> Approve
                                                        </button>
                                                    @endif
                                                    @if($canReject)
                                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#rejectModal{{ $row->id }}">
                                                            <i class="fas fa-times mr-2 text-danger"></i> Reject
                                                        </button>
                                                    @endif
                                                    @if(!empty($canEdit))
                                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $row->id }}">
                                                            <i class="fas fa-edit mr-2 text-primary"></i> Edit
                                                        </button>
                                                    @endif
                                                @endif

                                                @if(!empty($canDelete))
                                                    <form method="POST" action="{{ route($deleteRoute, $row->id) }}" onsubmit="return confirm('Delete this record?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="fas fa-trash mr-2"></i> Delete
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
            </div>

            @if(method_exists($rows, 'links'))
                <div class="mt-3 px-3 pb-3">
                    {{ $rows->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <!-- CREATE REGULARIZATION REQUEST MODAL -->
        @if(!empty($canCreate))
        <div class="modal fade glass-modal" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Apply Regularization</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="{{ route($storeRoute) }}" class="js-regularization-form">
                        @csrf
                        <div class="modal-body">
                            @if($canViewAll || $canViewTeam)
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Employee <span class="text-danger">*</span></label>
                                    <select name="employee_id" class="form-control custom-select" required>
                                        <option value="">Select Employee</option>
                                        @foreach($filters[0]['options'] ?? [] as $empId => $empName)
                                            <option value="{{ $empId }}">{{ $empName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="employee_id" value="{{ auth()->user()->employee->id ?? '' }}">
                            @endif

                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Attendance Date <span class="text-danger">*</span></label>
                                <input type="date" name="attendance_date" class="form-control" max="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Request Type <span class="text-danger">*</span></label>
                                <select name="request_type" class="form-control custom-select" required>
                                    <option value="">Select Type</option>
                                    <option value="missed_punch_in">Missed Punch In</option>
                                    <option value="missed_punch_out">Missed Punch Out</option>
                                    <option value="wrong_punch_time">Punch Time Correction</option>
                                    <option value="late_mark_exemption">Late Mark Exemption</option>
                                    <option value="early_logout_correction">Early Logout Exemption</option>
                                    <option value="other">Attendance Status Correction</option>
                                </select>
                            </div>

                            <!-- Conditionally displayed time pickers -->
                            <div class="form-group js-in-group" style="display: none;">
                                <label class="font-weight-bold text-dark">Requested Punch In Time <span class="text-danger">*</span></label>
                                <input type="time" name="requested_punch_in" class="form-control">
                            </div>

                            <div class="form-group js-out-group" style="display: none;">
                                <label class="font-weight-bold text-dark">Requested Punch Out Time <span class="text-danger">*</span></label>
                                <input type="time" name="requested_punch_out" class="form-control">
                            </div>

                            <!-- Requested status correction dropdown (maps to other) -->
                            <div class="form-group js-status-group" style="display: none;">
                                <label class="font-weight-bold text-dark">Requested Status <span class="text-danger">*</span></label>
                                <select name="requested_status" class="form-control custom-select">
                                    <option value="Present">Present</option>
                                    <option value="Half Day">Half Day</option>
                                    <option value="Absent">Absent</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Reason <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" minlength="5" required placeholder="Describe the reason for correction..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer" style="background: rgba(255,255,255,0.5);">
                            <button type="button" class="btn btn-light" style="border-radius: 10px;" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary font-weight-bold px-4" style="background: var(--orb-primary); border: 0; border-radius: 10px;">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- DYNAMIC MODALS FOR EACH ROW -->
        @foreach($rows as $row)
            @php
                $photoUrl = resolveEmployeePassportPhoto($row->employee_id);
                $initials = resolveEmployeeInitials($row->employee_id);
                $emp = \App\Models\HRMS\Employee\EmployeeM::with(['department', 'designation', 'profile'])->find($row->employee_id);
                $deptName = $emp?->department?->name ?? 'N/A';
                $desigName = $emp?->designation?->name ?? 'N/A';
                
                $attendanceRecord = $row->attendance_id ? \App\Models\HRMS\Attendance\AttendanceM::find($row->attendance_id) : null;
                $currentStatusText = $attendanceRecord ? resolve(\App\Services\HRMS\Attendance\AttendanceS::class)->resolveFinalStatus($attendanceRecord)['status_name'] : 'N/A';
                $shiftName = $attendanceRecord?->shift?->name ?? 'Regular Shift';
            @endphp

            <!-- VIEW DETAILS MODAL -->
            <div class="modal fade glass-modal" id="viewModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i> Request Details</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Employee Passport / Profile Card -->
                            <div class="d-flex align-items-center mb-4 p-3" style="background: rgba(255, 255, 255, 0.7); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px;">
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" class="avatar-modal rounded-circle" alt="">
                                @else
                                    <div class="avatar-modal rounded-circle d-inline-flex align-items-center justify-content-center" style="background: var(--orb-soft); color: var(--orb-primary); font-weight: 900; font-size: 18px;">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <div class="ml-3">
                                    <h5 class="font-weight-bold text-dark mb-1">{{ $row->employee_display_name ?? $row->name }}</h5>
                                    <div class="text-muted small mb-1"><i class="fas fa-id-badge mr-1"></i> {{ $row->employee_code }}</div>
                                    <div class="badge badge-light px-2 py-1 font-weight-bold text-primary">{{ $deptName }} &bull; {{ $desigName }}</div>
                                </div>
                            </div>

                            <!-- Attendance info card -->
                            <div class="detail-card">
                                <div class="detail-card-title"><i class="fas fa-calendar-alt mr-2"></i> Attendance Information</div>
                                <div class="detail-row">
                                    <span class="detail-label">Attendance Date</span>
                                    <span class="detail-value">{{ \Carbon\Carbon::parse($row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at)))->format('d M Y') }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Current Shift</span>
                                    <span class="detail-value">{{ $shiftName }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Current Status</span>
                                    <span class="detail-value">{{ ucfirst($currentStatusText) }}</span>
                                </div>
                            </div>

                            <!-- Request information card -->
                            <div class="detail-card">
                                <div class="detail-card-title"><i class="fas fa-align-left mr-2"></i> Request Information</div>
                                <div class="detail-row">
                                    <span class="detail-label">Request Type</span>
                                    <span class="detail-value text-primary">{{ $typeLabels[$row->request_type] ?? ucfirst(str_replace('_', ' ', $row->request_type)) }}</span>
                                </div>
                                
                                @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out', 'missed_punch_in', 'late_mark_exemption'], true))
                                <div class="detail-row">
                                    <span class="detail-label">Current Punch In</span>
                                    <span class="detail-value">{{ $row->existing_punch_in ? \Carbon\Carbon::parse($row->existing_punch_in)->format('h:i A') : 'N/A' }}</span>
                                </div>
                                @endif
                                
                                @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out', 'missed_punch_in', 'early_logout_correction'], true))
                                <div class="detail-row">
                                    <span class="detail-label">Current Punch Out</span>
                                    <span class="detail-value">{{ $row->existing_punch_out ? \Carbon\Carbon::parse($row->existing_punch_out)->format('h:i A') : 'N/A' }}</span>
                                </div>
                                @endif

                                @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_in'], true))
                                <div class="detail-row">
                                    <span class="detail-label">Requested Punch In</span>
                                    <span class="detail-value text-success">{{ $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('h:i A') : 'N/A' }}</span>
                                </div>
                                @endif

                                @if(in_array($row->request_type, ['wrong_punch_time', 'missed_punch_out'], true))
                                <div class="detail-row">
                                    <span class="detail-label">Requested Punch Out</span>
                                    <span class="detail-value text-success">{{ $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('h:i A') : 'N/A' }}</span>
                                </div>
                                @endif

                                @if($row->request_type === 'other' && str_contains($row->reason, '[Attendance Status Correction:'))
                                    @php
                                        preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                                        $reqStatus = $matches[1] ?? 'N/A';
                                    @endphp
                                    <div class="detail-row">
                                        <span class="detail-label">Requested Status</span>
                                        <span class="detail-value text-success font-weight-bold">{{ $reqStatus }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Reason text -->
                            <div class="detail-card">
                                <div class="detail-card-title"><i class="fas fa-question-circle mr-2"></i> Employee Submission Reason</div>
                                <p class="mb-0 text-dark font-weight-bold" style="font-size: 13px; line-height: 1.5;">
                                    {{ str_replace(['[Attendance Status Correction: Present]', '[Attendance Status Correction: Half Day]', '[Attendance Status Correction: Absent]', '[Attendance Status Correction: present]', '[Attendance Status Correction: half_day]', '[Attendance Status Correction: absent]'], '', $row->reason) }}
                                </p>
                            </div>

                            @if($row->rejection_reason)
                            <div class="detail-card" style="background: rgba(254, 242, 242, 0.7); border-color: rgba(254, 202, 202, 0.8);">
                                <div class="detail-card-title text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Rejection Reason / Note</div>
                                <p class="mb-0 text-danger font-weight-bold" style="font-size: 13px; line-height: 1.5;">
                                    {{ $row->rejection_reason }}
                                </p>
                            </div>
                            @endif

                            <!-- Approval timeline -->
                            <div class="detail-card">
                                <div class="detail-card-title"><i class="fas fa-clock mr-2"></i> Request History Timeline</div>
                                <div class="timeline-item">
                                    <div class="timeline-label">Submitted for Approval</div>
                                    <div class="timeline-time">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y h:i A') }}</div>
                                </div>
                                @if($row->status === 'approved')
                                <div class="timeline-item success">
                                    <div class="timeline-label text-success">Approved & Synced to Log</div>
                                    <div class="timeline-time">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d M Y h:i A') : 'N/A' }}</div>
                                </div>
                                @elseif($row->status === 'rejected')
                                <div class="timeline-item danger">
                                    <div class="timeline-label text-danger">Rejected by HR / Admin</div>
                                    <div class="timeline-time">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d M Y h:i A') : 'N/A' }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer" style="background: rgba(255,255,255,0.5);">
                            <button type="button" class="btn btn-secondary font-weight-bold" style="border-radius: 10px;" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- APPROVE MODAL -->
            <div class="modal fade glass-modal" id="approveModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669) !important;">
                            <h5 class="modal-title"><i class="fas fa-check-circle mr-2"></i> Approve Request</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('hrms.attendance.regularizations.approve', $row->id) }}">
                            @csrf
                            <div class="modal-body text-center py-4">
                                <i class="fas fa-check-circle text-success mb-3" style="font-size: 56px;"></i>
                                <h4 class="font-weight-bold mb-2">Approve Request?</h4>
                                <p class="text-muted mb-3">Are you sure you want to approve and apply this attendance correction request for <strong>{{ $row->employee_display_name ?? $row->name }}</strong>?</p>
                                
                                <div class="p-3 text-left" style="background: rgba(0, 0, 0, 0.02); border-radius: 12px; border: 1px solid #E2E8F0;">
                                    <div class="small text-muted font-weight-bold mb-1">REQUEST TYPE</div>
                                    <div class="font-weight-bold text-dark mb-2">{{ $typeLabels[$row->request_type] ?? $row->request_type }}</div>
                                    <div class="small text-muted font-weight-bold mb-1">DATE</div>
                                    <div class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at)))->format('d M Y') }}</div>
                                </div>
                            </div>
                            <div class="modal-footer" style="background: rgba(255,255,255,0.5);">
                                <button type="button" class="btn btn-light" style="border-radius: 10px;" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success font-weight-bold px-4" style="border-radius: 10px; background: #10B981; border: 0;">Approve Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- REJECT MODAL -->
            <div class="modal fade glass-modal" id="rejectModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #EF4444, #DC2626) !important;">
                            <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i> Reject Request</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('hrms.attendance.regularizations.reject', $row->id) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <i class="fas fa-times-circle text-danger mb-3" style="font-size: 56px;"></i>
                                    <h4 class="font-weight-bold mb-2">Reject Request?</h4>
                                    <p class="text-muted">Explain the reason for rejecting <strong>{{ $row->employee_display_name ?? $row->name }}</strong>'s regularization request.</p>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Type the reason for rejection here..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer" style="background: rgba(255,255,255,0.5);">
                                <button type="button" class="btn btn-light" style="border-radius: 10px;" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger font-weight-bold px-4" style="border-radius: 10px; background: #EF4444; border: 0;">Reject Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- EDIT REGULARIZATION REQUEST MODAL -->
            @if(!empty($canEdit) && $row->status === 'pending')
            <div class="modal fade glass-modal" id="editModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Edit Request</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route($updateRoute, $row->id) }}" class="js-regularization-form">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <input type="hidden" name="employee_id" value="{{ $row->employee_id }}">
                                
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Attendance Date</label>
                                    <input type="text" class="form-control" style="background: #F1F5F9;" value="{{ \Carbon\Carbon::parse($row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at)))->format('d M Y') }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Request Type <span class="text-danger">*</span></label>
                                    <select name="request_type" class="form-control custom-select" required>
                                        <option value="">Select Type</option>
                                        <option value="missed_punch_in" {{ $row->request_type === 'missed_punch_in' ? 'selected' : '' }}>Missed Punch In</option>
                                        <option value="missed_punch_out" {{ $row->request_type === 'missed_punch_out' ? 'selected' : '' }}>Missed Punch Out</option>
                                        <option value="wrong_punch_time" {{ $row->request_type === 'wrong_punch_time' ? 'selected' : '' }}>Punch Time Correction</option>
                                        <option value="late_mark_exemption" {{ $row->request_type === 'late_mark_exemption' ? 'selected' : '' }}>Late Mark Exemption</option>
                                        <option value="early_logout_correction" {{ $row->request_type === 'early_logout_correction' ? 'selected' : '' }}>Early Logout Exemption</option>
                                        <option value="other" {{ $row->request_type === 'other' ? 'selected' : '' }}>Attendance Status Correction</option>
                                    </select>
                                </div>

                                <!-- Conditionally displayed time pickers -->
                                <div class="form-group js-in-group" style="display: none;">
                                    <label class="font-weight-bold text-dark">Requested Punch In Time <span class="text-danger">*</span></label>
                                    <input type="time" name="requested_punch_in" class="form-control" value="{{ $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('H:i') : '' }}">
                                </div>

                                <div class="form-group js-out-group" style="display: none;">
                                    <label class="font-weight-bold text-dark">Requested Punch Out Time <span class="text-danger">*</span></label>
                                    <input type="time" name="requested_punch_out" class="form-control" value="{{ $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('H:i') : '' }}">
                                </div>

                                <!-- Requested status correction dropdown (maps to other) -->
                                @php
                                    $savedStatus = 'Present';
                                    if ($row->request_type === 'other' && str_contains($row->reason, '[Attendance Status Correction:')) {
                                        preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                                        $savedStatus = $matches[1] ?? 'Present';
                                    }
                                @endphp
                                <div class="form-group js-status-group" style="display: none;">
                                    <label class="font-weight-bold text-dark">Requested Status <span class="text-danger">*</span></label>
                                    <select name="requested_status" class="form-control custom-select">
                                        <option value="Present" {{ $savedStatus === 'Present' ? 'selected' : '' }}>Present</option>
                                        <option value="Half Day" {{ $savedStatus === 'Half Day' ? 'selected' : '' }}>Half Day</option>
                                        <option value="Absent" {{ $savedStatus === 'Absent' ? 'selected' : '' }}>Absent</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Reason <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control" rows="3" minlength="5" required placeholder="Describe the reason for correction...">{{ str_replace(['[Attendance Status Correction: Present]', '[Attendance Status Correction: Half Day]', '[Attendance Status Correction: Absent]', '[Attendance Status Correction: present]', '[Attendance Status Correction: half_day]', '[Attendance Status Correction: absent]'], '', $row->reason) }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer" style="background: rgba(255,255,255,0.5);">
                                <button type="button" class="btn btn-light" style="border-radius: 10px;" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4" style="background: var(--orb-primary); border: 0; border-radius: 10px;">Update Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

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
document.addEventListener('DOMContentLoaded', function () {
    // Tooltips activation
    if (window.jQuery && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    function initRegularizationForm(form) {
        if (!form) return;
        const typeSelect = form.querySelector('select[name="request_type"]');
        const inGroup = form.querySelector('.js-in-group');
        const outGroup = form.querySelector('.js-out-group');
        const statusGroup = form.querySelector('.js-status-group');
        
        const inInput = form.querySelector('input[name="requested_punch_in"]');
        const outInput = form.querySelector('input[name="requested_punch_out"]');
        const statusSelect = form.querySelector('select[name="requested_status"]');
        
        if (!typeSelect) return;

        const updateFields = () => {
            const type = typeSelect.value;
            
            // Hide all conditional groups first
            if (inGroup) inGroup.style.display = 'none';
            if (outGroup) outGroup.style.display = 'none';
            if (statusGroup) statusGroup.style.display = 'none';
            
            if (inInput) inInput.removeAttribute('required');
            if (outInput) outInput.removeAttribute('required');
            if (statusSelect) statusSelect.removeAttribute('required');
            
            if (type === 'missed_punch_in') {
                if (inGroup) inGroup.style.display = '';
                if (inInput) inInput.setAttribute('required', 'required');
            } else if (type === 'missed_punch_out') {
                if (outGroup) outGroup.style.display = '';
                if (outInput) outInput.setAttribute('required', 'required');
            } else if (type === 'wrong_punch_time') {
                if (inGroup) inGroup.style.display = '';
                if (outGroup) outGroup.style.display = '';
                if (inInput) inInput.setAttribute('required', 'required');
                if (outInput) outInput.setAttribute('required', 'required');
            } else if (type === 'other') {
                if (statusGroup) statusGroup.style.display = '';
                if (statusSelect) statusSelect.setAttribute('required', 'required');
            }
        };

        typeSelect.addEventListener('change', updateFields);
        updateFields();

        // Handle prepending dynamic status to reason before submit
        form.addEventListener('submit', function(e) {
            const type = typeSelect.value;
            const reasonTextarea = form.querySelector('textarea[name="reason"]');
            
            if (type === 'other' && statusSelect && reasonTextarea) {
                const originalReason = reasonTextarea.value;
                if (!originalReason.startsWith('[Attendance Status Correction:')) {
                    reasonTextarea.value = `[Attendance Status Correction: ${statusSelect.value}] ${originalReason}`;
                }
            }
        });
    }

    document.querySelectorAll('.js-regularization-form').forEach(function(form) {
        initRegularizationForm(form);
    });

    // Auto filter triggering
    document.querySelectorAll('.js-auto-filter').forEach(function (input) {
        input.addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });

        if (input.tagName === 'INPUT') {
            let timeout = null;
            input.addEventListener('keyup', function () {
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
        }
    });

    // DataTable initialization
    if (window.jQuery && $.fn.DataTable) {
        if ($.fn.DataTable.isDataTable('#regularizationDataTable')) {
            $('#regularizationDataTable').DataTable().destroy();
        }

        $('#regularizationDataTable').DataTable({
            destroy: true,
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            searching: false,
            lengthChange: true,
            info: true,
            responsive: false,
            autoWidth: false,
            order: [],
            scrollX: true,
            scrollCollapse: true,
            dom: "<'row mx-0 px-2 py-3 align-items-center'<'col-sm-12 col-md-6 px-0'l><'col-sm-12 col-md-6 px-0 text-right'B>>rt<'row align-items-center mt-3 px-3 pb-3'<'col-md-5'i><'col-md-7'p>>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-light border',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: '{{ branding_name() }} Attendance Regularizations',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-light border',
                    title: '{{ branding_name() }} Attendance Regularizations',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ],
            language: {
                emptyTable: 'No regularization records found.'
            }
        });

        setTimeout(function() {
            $('#regularizationDataTable').DataTable().columns.adjust();
        }, 250);
    }
});
</script>
@endsection
