@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Create Permission')

@section('_head')
@include('access_control.partials.styles')
@endsection

@section('_content')
<div class="ac-page">
    <div class="ac-container">
        <!-- Premium Purple Gradient Hero -->
        <div class="ac-header">
            <div>
                <div class="ac-kicker">
                    <i class="fas fa-plus-circle"></i> HRMS &bull; ACCESS CONTROL
                </div>
                <h1 class="ac-title">Create Permission</h1>
                <p class="ac-subtitle">Add a new authorization key for system or module Regulators.</p>
            </div>
            <a href="{{ route('permissions.index') }}" class="ac-btn ac-btn-soft">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @include('access_control.partials.flash')

        <!-- Form Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-key"></i></div>
                    <div>
                        <h5 class="ac-table-title">Permission Settings</h5>
                        <p class="ac-table-subtitle">Configure module, submodule keys, action titles, and validation hooks.</p>
                    </div>
                </div>
            </div>

            <div class="ac-card-body" style="padding: 30px;">
                <form action="{{ route('permissions.store') }}" method="POST">
                    @include('access_control.permissions._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
