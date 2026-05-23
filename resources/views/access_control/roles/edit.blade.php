@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Edit Role')

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
                    <i class="fas fa-edit"></i> HRMS &bull; ACCESS CONTROL
                </div>
                <h1 class="ac-title">Edit Role Profile</h1>
                <p class="ac-subtitle">Modify parameters for "{{ $role->name }}"</p>
            </div>
            <div class="d-flex flex-wrap" style="gap:8px;">
                <a href="{{ route('role_permissions.edit', $role->id) }}" class="ac-btn ac-btn-soft">
                    <i class="fas fa-key"></i> Permissions Mapping
                </a>
                <a href="{{ route('role_menus.edit', $role->id) }}" class="ac-btn ac-btn-soft">
                    <i class="fas fa-sitemap"></i> Sidebar Menu Access
                </a>
                <a href="{{ route('roles.index') }}" class="ac-btn ac-btn-soft">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        @include('access_control.partials.flash')

        <!-- Form Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <h5 class="ac-table-title">Update Profile Settings</h5>
                        <p class="ac-table-subtitle">Adjust core identifiers, status tags, and protected role configurations.</p>
                    </div>
                </div>
            </div>

            <div class="ac-card-body" style="padding: 30px;">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @method('PUT')
                    @include('access_control.roles._form', ['role' => $role])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
