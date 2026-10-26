@extends('layouts.panel')

@section('page_title', 'My Leave Requests')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@include('hrms.enterprise-payroll.partials.styles')
@include('hrms.leave.shared.style')
@include('hrms.leave.requests.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="leave-page-container">
        
        @include('hrms.leave.requests.partials.hero')

        @include('hrms.leave.shared.flash')

        @include('hrms.leave.requests.partials.metrics')

        @include('hrms.leave.requests.partials.table')

    </div>
</div>

@include('hrms.leave.requests.partials.view-modals')

@include('hrms.leave.requests.partials.edit-modal')
@endsection

@section('_script')
@include('hrms.leave.requests.partials.scripts')
@endsection
