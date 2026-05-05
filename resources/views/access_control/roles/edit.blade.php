@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Edit Role')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Edit Role</h1>
                <p class="ac-subtitle">{{ $role->name }}</p>
            </div>
            <div class="d-flex flex-wrap" style="gap:8px;">
                <a href="{{ route('role_permissions.edit', $role->id) }}" class="ac-btn ac-btn-soft">
                    <i class="fas fa-key"></i>
                    Permissions
                </a>
                <a href="{{ route('role_menus.edit', $role->id) }}" class="ac-btn ac-btn-soft">
                    <i class="fas fa-bars"></i>
                    Menus
                </a>
            </div>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="ac-card-body">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @method('PUT')
                    @include('access_control.roles._form', ['role' => $role])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
