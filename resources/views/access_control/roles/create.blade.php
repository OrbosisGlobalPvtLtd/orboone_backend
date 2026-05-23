@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Create Role')

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
                <h1 class="ac-title">Create Role</h1>
                <p class="ac-subtitle">Configure a new access profile and authentication credentials.</p>
            </div>
            <a href="{{ route('roles.index') }}" class="ac-btn ac-btn-soft">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @include('access_control.partials.flash')

        <!-- Form Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <h5 class="ac-table-title">Role Configuration details</h5>
                        <p class="ac-table-subtitle">Publish system-protected roles or customized operations access profiles.</p>
                    </div>
                </div>
            </div>

            <div class="ac-card-body" style="padding: 30px;">
                <form action="{{ route('roles.store') }}" method="POST">
                    @include('access_control.roles._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
