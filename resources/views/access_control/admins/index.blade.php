@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Admin Users')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Admin Users</h1>
                <p class="ac-subtitle">Manage web admin access without changing employee records.</p>
            </div>
            <a href="{{ route('admins.create') }}" class="ac-btn ac-btn-primary">
                <i class="fas fa-plus-circle"></i>
                Add Admin
            </a>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 ac-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Web</th>
                            <th>App</th>
                            <th>Status</th>
                            <th width="110">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role_name ?? '-' }}</td>
                                <td><span class="ac-pill {{ $user->is_web_access ? 'ac-pill-on' : 'ac-pill-off' }}">{{ $user->is_web_access ? 'Yes' : 'No' }}</span></td>
                                <td><span class="ac-pill {{ $user->is_app_access ? 'ac-pill-on' : '' }}">{{ $user->is_app_access ? 'Yes' : 'No' }}</span></td>
                                <td><span class="ac-pill {{ $user->is_active ? 'ac-pill-on' : 'ac-pill-off' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <div class="ac-actions">
                                        <a href="{{ route('admins.edit', $user->id) }}" class="ac-icon-btn" title="Edit Admin">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(! in_array('super_admin', $user->role_slugs ?? [], true) && (int) $user->id !== (int) auth()->id())
                                            <form action="{{ route('admins.destroy', $user->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this admin user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ac-icon-btn ac-icon-danger" title="Delete Admin">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No admin users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="ac-card-body">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
