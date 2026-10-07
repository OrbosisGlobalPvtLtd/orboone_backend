@extends('layouts.panel', [
    'accesses' => $accesses ?? [],
    'active' => $active ?? 'hrms'
])

@section('page_title', $pageTitle ?? 'Attendance Regularizations')

@section('_head')
    @include('hrms.attendance.regularizations.partials.styles')
@endsection

@section('_content')
<div class="att-page">
    <div class="att-container">

        @php
            $currentUser = auth()->user();
            $ownEmpId = $currentUser?->employee?->id ?? (DB::table('employees_new')->where('user_id', $currentUser?->id)->value('id') ?? null);
            $isEmpRole = (!empty($isEmployeeRole) || ($currentUser->role_id ?? null) == 7 || ($currentUser->system_role_id ?? null) == 7);
            
            $hasApprovePerm = $currentUser && method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission('attendance.regularization.approve');
            $hasRejectPerm = $currentUser && method_exists($currentUser, 'hasPermission') && ($currentUser->hasPermission('attendance.regularization.reject') || $currentUser->hasPermission('attendance.regularization.approve'));
            
            $canApproveGlobal = !$isEmpRole && $hasApprovePerm;
            $canRejectGlobal = !$isEmpRole && $hasRejectPerm;
            $canViewAll = $currentUser && (method_exists($currentUser, 'isSuperAdmin') && $currentUser->isSuperAdmin() || (method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission('attendance.regularization.view_all')));
            $canViewTeam = $currentUser && method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission('attendance.regularization.view_team');

            $typeLabels = [
                'missed_punch_in' => 'Missed Punch In',
                'missed_punch_out' => 'Missed Punch Out',
                'wrong_punch_time' => 'Punch Time Correction',
                'punch_time_correction' => 'Punch Time Correction',
                'regular_attendance' => 'Regular Attendance',
                'late_mark_exemption' => 'Late Mark Exemption',
                'early_logout_correction' => 'Early Logout Exemption',
                'early_logout_exemption' => 'Early Logout Exemption',
                'geofence_issue' => 'Geofence Issue',
                'system_error' => 'System/App Error',
                'attendance_status_correction' => 'Status Correction',
                'unlock_attendance' => 'Unlock Attendance',
                'other' => 'Other',
            ];
        @endphp

        {{-- Hero Header Component --}}
        @include('hrms.attendance.regularizations.partials.hero')

        {{-- Session Flash Alerts Component --}}
        @include('hrms.attendance.regularizations.partials.alerts')

        {{-- Metrics Grid Component --}}
        <div class="att-metric-grid">
            <div class="att-metric" style="--metric-color:var(--orb-primary);--metric-soft:#F4F2FF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-list-alt"></i></div>
                    <div class="att-metric-value">{{ $stats['total'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Total Requests</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#F59E0B;--metric-soft:#FEF3C7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-clock"></i></div>
                    <div class="att-metric-value">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Pending Approval</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#16A34A;--metric-soft:#DCFCE7;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="att-metric-value">{{ $stats['approved'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Approved</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#EF4444;--metric-soft:#FEE2E2;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="att-metric-value">{{ $stats['rejected'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Rejected</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#EA580C;--metric-soft:#FFEDD5;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-unlock-alt"></i></div>
                    <div class="att-metric-value">{{ $stats['unlock_requests'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Unlock Requests</div>
                <div class="att-metric-line"></div>
            </div>
            <div class="att-metric" style="--metric-color:#6366F1;--metric-soft:#E0E7FF;">
                <div class="att-metric-top">
                    <div class="att-metric-icon"><i class="fas fa-user-edit"></i></div>
                    <div class="att-metric-value">{{ $stats['punch_corrections'] ?? 0 }}</div>
                </div>
                <div class="att-metric-label">Punch Corrections</div>
                <div class="att-metric-line"></div>
            </div>
        </div>

        {{-- Main Regularizations Card --}}
        <div class="att-card">
            <div class="att-section-head">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-history"></i> Regularization Logs</h5>
                    <div class="att-section-sub">Track correction requests, employee submissions, and approval status logs.</div>
                </div>
                @if(!empty($canCreate))
                <div>
                    <button type="button" class="att-section-btn" data-toggle="modal" data-target="#createModal">
                        <i class="fas fa-plus-circle"></i> Apply Regularization
                    </button>
                </div>
                @endif
            </div>

            {{-- Filter Panel Component --}}
            @include('hrms.attendance.regularizations.partials.filters')

            {{-- Regularizations DataTable Component --}}
            @include('hrms.attendance.regularizations.partials.table')
        </div>

        {{-- Create Request Modal Component --}}
        @include('hrms.attendance.regularizations.partials.modals.create')

        {{-- Row Action Modals (View, Approve, Reject, Edit) Component --}}
        @include('hrms.attendance.regularizations.partials.row-modals')

    </div>
</div>
@endsection

@section('_script')
    @include('hrms.attendance.regularizations.partials.scripts')
@endsection
