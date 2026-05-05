@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Create Permission')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Create Permission</h1>
                <p class="ac-subtitle">Add a permission key for role mapping.</p>
            </div>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="ac-card-body">
                <form action="{{ route('permissions.store') }}" method="POST">
                    @include('access_control.permissions._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
