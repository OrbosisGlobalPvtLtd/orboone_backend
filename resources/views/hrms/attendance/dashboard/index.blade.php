@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Attendance Dashboard')

@section('_head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')
@php
$attendanceRows = ($attendances instanceof \Illuminate\Pagination\AbstractPaginator
    ? collect($attendances->items())
    : collect($attendances ?? []))->filter(function ($item) {
        return is_object($item) && !($item->is_punch_blocked || $item->is_blocked || $item->attendance_status === 'punch_blocked' || (optional($item->attendanceType)->code === 'punch_blocked'));
    });

$blockedRows = ($blockedAttendances instanceof \Illuminate\Pagination\AbstractPaginator
    ? collect($blockedAttendances->items())
    : collect($blockedAttendances ?? []))->filter(function ($item) {
        return is_object($item);
    });

$unmarkedRows = ($unmarkedEmployees instanceof \Illuminate\Pagination\AbstractPaginator
    ? collect($unmarkedEmployees->items())
    : collect($unmarkedEmployees ?? []))->filter(function ($item) {
        return is_object($item);
    });

$currentUnmarkedPerPage = (int) request('unmarked_per_page', 10);
$currentBlockedPerPage = (int) request('blocked_per_page', 10);
$currentAttendancePerPage = (int) request('attendance_per_page', request('per_page', 25));

$kpis = [
    ['label' => 'Present Today', 'value' => $stats['present_today'] ?? 0, 'icon' => 'fa-check-circle', 'tone' => 'success'],
    ['label' => 'Not Marked Today', 'value' => $stats['unmarked_today'] ?? (method_exists($unmarkedEmployees, 'total') ? $unmarkedEmployees->total() : $unmarkedRows->count()), 'icon' => 'fa-user-slash', 'tone' => 'danger'],
    ['label' => 'Absent Today', 'value' => $stats['absent_today'] ?? 0, 'icon' => 'fa-user-times', 'tone' => 'danger'],
    ['label' => 'Late Employees', 'value' => $stats['late_employees'] ?? $stats['late_today'] ?? 0, 'icon' => 'fa-user-clock', 'tone' => 'warning'],
    ['label' => 'Early Logout', 'value' => $stats['early_logout'] ?? $stats['early_out_today'] ?? 0, 'icon' => 'fa-running', 'tone' => 'orange'],
    ['label' => 'Pending Unlock', 'value' => $stats['total_blocked'] ?? $stats['punch_blocked'] ?? 0, 'icon' => 'fa-user-lock', 'tone' => 'purple'],
    ['label' => 'Pending Punch Out', 'value' => $stats['pending_punch_out'] ?? 0, 'icon' => 'fa-clock', 'tone' => 'info'],
    ['label' => 'Half Day', 'value' => $stats['half_day'] ?? $stats['half_day_today'] ?? 0, 'icon' => 'fa-adjust', 'tone' => 'amber'],
    ['label' => 'LWP', 'value' => $stats['lwp'] ?? $stats['lwp_today'] ?? 0, 'icon' => 'fa-calendar-minus', 'tone' => 'danger'],
    ['label' => 'Punch Blocked', 'value' => $stats['punch_blocked'] ?? $stats['punch_blocked_today'] ?? 0, 'icon' => 'fa-user-lock', 'tone' => 'blocked'],
    ['label' => 'Missed Punches', 'value' => $stats['missed_punches'] ?? $stats['missed_punch_today'] ?? 0, 'icon' => 'fa-exclamation-circle', 'tone' => 'warning'],
    ['label' => 'Currently Working', 'value' => $stats['currently_working'] ?? 0, 'icon' => 'fa-laptop-house', 'tone' => 'blue'],
    //['label' => 'Completed Shift', 'value' => $stats['completed_shift'] ?? 0, 'icon' => 'fa-clipboard-check', 'tone' => 'success'],
    ['label' => 'WFO Today', 'value' => $stats['wfo_today'] ?? 0, 'icon' => 'fa-building', 'tone' => 'indigo'],
    ['label' => 'WFH Today', 'value' => $stats['wfh_today'] ?? 0, 'icon' => 'fa-home', 'tone' => 'teal'],
];
@endphp

@include('hrms.attendance.dashboard.partials.styles')

<div class="att-page">
    <div class="att-container">
        {{-- Hero Banner & Key Metrics --}}
        @include('hrms.attendance.dashboard.partials.hero-and-kpis')

        {{-- Punch-In Blocked Employees Table --}}
        @include('hrms.attendance.dashboard.partials.blocked-table')

        {{-- Employees Not Marked Attendance Today Table --}}
        @include('hrms.attendance.dashboard.partials.unmarked-table')

        {{-- Today's Attendance Table --}}
        @include('hrms.attendance.dashboard.partials.today-table')
    </div>
</div>

{{-- Attendance Edit & Unlock Modals --}}
@include('hrms.attendance.dashboard.partials.modals')
@endsection

@section('_script')
@include('hrms.attendance.dashboard.partials.scripts')
@endsection
