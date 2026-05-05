@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Role Permissions')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Role Permission Mapping</h1>
                <p class="ac-subtitle">Choose a role to assign permissions.</p>
            </div>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 ac-table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->slug ?? '-' }}</td>
                                <td><span class="ac-pill {{ $role->status ? 'ac-pill-on' : 'ac-pill-off' }}">{{ $role->status ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <a href="{{ route('role_permissions.edit', $role->id) }}" class="ac-btn ac-btn-soft">
                                        <i class="fas fa-key"></i>
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="ac-card-body">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
