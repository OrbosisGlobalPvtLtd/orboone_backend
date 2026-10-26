@extends('layouts.panel')

@section('page_title', 'Compensatory Off Management')

@section('_head')
    @include('hrms.leave.shared.style')
    @include('hrms.leave.comp_offs.partials.styles')
@endsection

@section('_content')
<div class="leave-page-wrap">

    {{-- Hero Header --}}
    @include('hrms.leave.comp_offs.partials.hero')

    {{-- Flash Messages --}}
    @include('hrms.leave.shared.flash')

    {{-- Grant Comp-Off Modal --}}
    @include('hrms.leave.comp_offs.partials.grant-modal')

    {{-- Edit Comp-Off Modal --}}
    @include('hrms.leave.comp_offs.partials.edit-modal')

    {{-- Summary Stat Metric Cards --}}
    @include('hrms.leave.comp_offs.partials.stats')

    {{-- Pending Holiday Work Approvals (Admin/HR Only) --}}
    @include('hrms.leave.comp_offs.partials.pending-approvals')

    {{-- Modern View Tabs (Placed below cards, above table card - Leave Allocation Theme) --}}
    <div class="nav nav-pills d-flex flex-wrap align-items-center mb-3 border-0 bg-transparent p-0" id="compOffNavTabs" role="tablist" style="gap:10px;">
        <a class="leave-view-tab active" id="records-tab" data-toggle="pill" href="#tab-records" role="tab" aria-controls="tab-records" aria-selected="true">
            <i class="fas fa-calendar-check mr-1"></i>
            <span>Comp-Off Records</span>
            
        </a>
        <a class="leave-view-tab" id="transactions-tab" data-toggle="pill" href="#tab-transactions" role="tab" aria-controls="tab-transactions" aria-selected="false">
            <i class="fas fa-history mr-1"></i>
            <span>Comp-Off Transactions</span>
            
        </a>
    </div>

    {{-- Compensatory Off Records Table Card (Includes Filter Bar, Toolbars, Table & Pagination) --}}
    @include('hrms.leave.comp_offs.partials.table')

</div>
@endsection

@section('_script')
    @include('hrms.leave.comp_offs.partials.scripts')
@endsection
