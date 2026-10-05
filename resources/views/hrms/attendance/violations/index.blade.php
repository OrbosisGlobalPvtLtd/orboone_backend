@extends('layouts.panel', [
    'accesses' => $accesses ?? [],
    'active' => $active ?? 'hrms'
])

@section('page_title', $pageTitle ?? 'Attendance Violations')

@section('_head')
    @include('hrms.attendance.violations.partials.styles')
@endsection

@section('_content')
<div class="att-page">
    <div class="att-container">

        <!-- Hero Header -->
        @include('hrms.attendance.violations.partials.hero')

        <!-- Summary KPI Cards -->
        @include('hrms.attendance.violations.partials.summary')

        <!-- Main Card Container (Filters, Table & Pagination) -->
        <div class="att-card">
            <!-- Filter Section -->
            @include('hrms.attendance.violations.partials.filters')

            <!-- Table & Pagination Section -->
            @include('hrms.attendance.violations.partials.table')
        </div>
    </div>
</div>

<!-- Employee Audit Side Drawer -->
@include('hrms.attendance.violations.partials.drawers.employee-audit-drawer')

<!-- Attendance Audit Detail Modal -->
@include('hrms.attendance.violations.partials.modals.attendance-audit-modal')
@endsection

@section('_script')
    @include('hrms.attendance.violations.partials.scripts')
@endsection