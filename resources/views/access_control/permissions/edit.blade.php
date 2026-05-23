@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Edit Permission')

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
                <h1 class="ac-title">Edit Permission Key</h1>
                <p class="ac-subtitle">Modify parameters for permission slug "{{ $permission->key }}"</p>
            </div>
            <a href="{{ route('permissions.index') }}" class="ac-btn ac-btn-soft">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @include('access_control.partials.flash')

        <!-- Form Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-key"></i></div>
                    <div>
                        <h5 class="ac-table-title">Update Permission Attributes</h5>
                        <p class="ac-table-subtitle">Adjust core identifiers, submodules, and checkmark validation codes.</p>
                    </div>
                </div>
            </div>

            <div class="ac-card-body" style="padding: 30px;">
                <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
                    @method('PUT')
                    @include('access_control.permissions._form', ['permission' => $permission])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
