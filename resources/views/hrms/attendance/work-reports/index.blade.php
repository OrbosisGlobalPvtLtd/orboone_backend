@extends('layouts.panel', [
    'accesses' => $accesses ?? [],
    'active' => $active ?? 'attendances'
])

@section('page_title', 'Daily Work Reports')

@section('_head')
    @include('hrms.attendance.work-reports.partials.styles')
@endsection

@section('_content')
<div class="report-page att-page">
    <div class="report-container att-container">

        <!-- Hero Header -->
        @include('hrms.attendance.work-reports.partials.hero')

        <!-- Summary KPI Cards -->
        @include('hrms.attendance.work-reports.partials.summary')

        <!-- Main Card Container: Table Listing View (Default) -->
        <div id="tableViewArea">
            <div class="att-card orb-table-card">
                <!-- Section Head & Multi-Filter Bar Positioned Below Section Head -->
                @include('hrms.attendance.work-reports.partials.filters')

                <!-- Table & Server-Side Pagination -->
                @include('hrms.attendance.work-reports.partials.table')
            </div>
        </div>

        <!-- Employee Summaries Cards View Mode -->
        @include('hrms.attendance.work-reports.partials.cards')

    </div>
</div>

<!-- Employee History Drawer -->
@include('hrms.attendance.work-reports.partials.drawers.timeline-drawer')

<!-- Shared Work Report Details Modal -->
@include('hrms.attendance.partials.work-report-modal')
@endsection

@section('_script')
    @include('hrms.attendance.work-reports.partials.scripts')
@endsection
