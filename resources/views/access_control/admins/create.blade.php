@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Create Admin')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Create Admin</h1>
                <p class="ac-subtitle">Create a web admin user with an admin role.</p>
            </div>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="ac-card-body">
                <form action="{{ route('admins.store') }}" method="POST">
                    @include('access_control.admins._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
