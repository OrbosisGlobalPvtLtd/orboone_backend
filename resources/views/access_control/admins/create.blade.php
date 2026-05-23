@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Create Admin')

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
                <h1 class="ac-title">Add Admin User</h1>
                <p class="ac-subtitle">Register a console administrator and assign access control role tags.</p>
            </div>
            <a href="{{ route('admins.index') }}" class="ac-btn ac-btn-soft">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @include('access_control.partials.flash')

        <!-- Form Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-users-cog"></i></div>
                    <div>
                        <h5 class="ac-table-title">Admin Account Credentials</h5>
                        <p class="ac-table-subtitle">Configure email identities, security passwords, interface accesses, and roles.</p>
                    </div>
                </div>
            </div>

            <div class="ac-card-body" style="padding: 30px;">
                <form action="{{ route('admins.store') }}" method="POST">
                    @include('access_control.admins._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
