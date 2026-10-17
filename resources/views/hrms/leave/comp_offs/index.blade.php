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

    {{-- Summary Stat Metric Cards --}}
    @include('hrms.leave.comp_offs.partials.stats')

    {{-- Pending Holiday Work Approvals (Admin/HR Only) --}}
    @include('hrms.leave.comp_offs.partials.pending-approvals')

    {{-- Compensatory Off Records Table Card (Includes Filter Bar, Toolbars, Table & Pagination) --}}
    @include('hrms.leave.comp_offs.partials.table')

</div>
@endsection

@section('_script')
    @include('hrms.leave.comp_offs.partials.scripts')
@endsection
