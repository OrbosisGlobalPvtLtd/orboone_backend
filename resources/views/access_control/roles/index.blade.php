@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Roles')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Roles</h1>
                <p class="ac-subtitle">Manage admin and system roles.</p>
            </div>
            <a href="{{ route('roles.create') }}" class="ac-btn ac-btn-primary">
                <i class="fas fa-plus-circle"></i>
                Add Role
            </a>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 ac-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>System</th>
                            <th>Status</th>
                            <th width="220">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->slug ?? '-' }}</td>
                                <td>{{ $role->description ?? '-' }}</td>
                                <td><span class="ac-pill {{ $role->is_system ? 'ac-pill-on' : '' }}">{{ $role->is_system ? 'Yes' : 'No' }}</span></td>
                                <td><span class="ac-pill {{ $role->status ? 'ac-pill-on' : 'ac-pill-off' }}">{{ $role->status ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <div class="ac-actions">
                                        <a href="{{ route('roles.edit', $role->id) }}" class="ac-icon-btn" title="Edit Role">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('role_permissions.edit', $role->id) }}" class="ac-icon-btn" title="Permissions">
                                            <i class="fas fa-key"></i>
                                        </a>
                                        <a href="{{ route('role_menus.edit', $role->id) }}" class="ac-icon-btn" title="Menu Access">
                                            <i class="fas fa-bars"></i>
                                        </a>
                                        @if(! $role->is_system && $role->slug !== 'super_admin')
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this role?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ac-icon-btn ac-icon-danger" title="Delete Role">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No roles found.</td>
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
