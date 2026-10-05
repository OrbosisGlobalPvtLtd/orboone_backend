@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Pending Profiles')

@section('_head')
@include('hrms.employee.partials.styles')
@include('hrms.employee.profile.partials.pending.styles')
@endsection

@section('_content')
<div class="eo-page pp-page">
    <div class="eo-container">
        {{-- Flash Alerts, Header & Stats --}}
        @include('hrms.employee.profile.partials.pending.header-stats')

        {{-- Main Profiles Table Card with Filter Toolbar, Export Tools, DataTable & Pagination --}}
        @include('hrms.employee.profile.partials.pending.table')

        {{-- Modals (Approve & Reject) --}}
        @include('hrms.employee.profile.partials.pending.modals')
    </div>
</div>
@endsection

@section('_script')
@include('hrms.employee.profile.partials.pending.scripts')
@endsection