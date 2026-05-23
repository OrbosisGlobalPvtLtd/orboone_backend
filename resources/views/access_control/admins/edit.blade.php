@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Edit Admin')

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
                <h1 class="ac-title">Edit Admin Profile</h1>
                <p class="ac-subtitle">Modify credentials and active role states for "{{ $admin->email }}"</p>
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
                        <h5 class="ac-table-title">Update Administrator Settings</h5>
                        <p class="ac-table-subtitle">Adjust core usernames, email routing, optional passwords, and security profile roles.</p>
                    </div>
                </div>
            </div>

            <div class="ac-card-body" style="padding: 30px;">
                <form action="{{ route('admins.update', $admin->id) }}" method="POST">
                    @method('PUT')
                    @include('access_control.admins._form', ['admin' => $admin])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
