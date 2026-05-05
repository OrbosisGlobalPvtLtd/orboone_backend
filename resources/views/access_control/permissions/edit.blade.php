@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Edit Permission')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Edit Permission</h1>
                <p class="ac-subtitle">{{ $permission->key }}</p>
            </div>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="ac-card-body">
                <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
                    @method('PUT')
                    @include('access_control.permissions._form', ['permission' => $permission])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
