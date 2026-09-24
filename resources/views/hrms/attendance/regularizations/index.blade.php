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
                'late_mark_exemption' => 'Late Mark Exemption',
                'early_logout_correction' => 'Early Logout Exemption',
                'early_logout_exemption' => 'Early Logout Exemption',
                'geofence_issue' => 'Geofence Issue',
                'system_error' => 'System/App Error',
                'attendance_status_correction' => 'Attendance Status Correction',
                'other' => 'Other',
            ];
        @endphp

        {{-- Hero Header Component --}}
        @include('hrms.attendance.regularizations.partials.hero')

        {{-- Session Flash Alerts Component --}}
        @include('hrms.attendance.regularizations.partials.alerts')

        {{-- Main Regularizations Card --}}
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
