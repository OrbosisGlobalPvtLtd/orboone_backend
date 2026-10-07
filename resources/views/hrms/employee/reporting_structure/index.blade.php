@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Reporting Structure')

@section('_head')
@include('hrms.employee.reporting_structure.partials.styles')
@endsection

@section('_content')
<div class="eo-page">
    <div class="eo-container">
        {{-- Top Header Hero Section --}}
        @include('hrms.employee.reporting_structure.partials.header')

        {{-- Main Org Chart Card --}}
        <div class="eo-card">
            {{-- Card Header & Filter Toolbar --}}
            @include('hrms.employee.reporting_structure.partials.filters')

            {{-- Tree & Stacked List Viewports --}}
            @include('hrms.employee.reporting_structure.partials.chart')
        </div>
    </div>
</div>
@endsection

@section('_script')
@include('hrms.employee.reporting_structure.partials.scripts')
@endsection
