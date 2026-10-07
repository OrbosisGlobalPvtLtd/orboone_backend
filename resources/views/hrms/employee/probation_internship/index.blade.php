@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Probation / Internship')

@section('_head')
@include('hrms.employee.probation_internship.partials.styles')
@endsection

@section('_content')
<div class="eo-page">
    <div class="eo-container">
        {{-- Hero Header & Flash Alerts --}}
        @include('hrms.employee.probation_internship.partials.header')

        {{-- Main Profiles Table Card with Filter Toolbar, Export Tools, DataTable & Pagination --}}
        @include('hrms.employee.probation_internship.partials.table')

        {{-- Lifecycle Action Modals --}}
        @include('hrms.employee.probation_internship.partials.modals')
    </div>
</div>
@endsection

@section('_script')
@include('hrms.employee.probation_internship.partials.scripts')
@endsection
