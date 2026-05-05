@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Edit Admin')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Edit Admin</h1>
                <p class="ac-subtitle">{{ $admin->email }}</p>
            </div>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="ac-card-body">
                <form action="{{ route('admins.update', $admin->id) }}" method="POST">
                    @method('PUT')
                    @include('access_control.admins._form', ['admin' => $admin])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
