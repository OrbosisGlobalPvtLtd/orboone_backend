@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Create Role')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Create Role</h1>
                <p class="ac-subtitle">Add a new access control role.</p>
            </div>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="ac-card-body">
                <form action="{{ route('roles.store') }}" method="POST">
                    @include('access_control.roles._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
