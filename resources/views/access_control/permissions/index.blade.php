@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Permissions')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Permissions</h1>
                <p class="ac-subtitle">Manage module permissions used by roles.</p>
            </div>
            <a href="{{ route('permissions.create') }}" class="ac-btn ac-btn-primary">
                <i class="fas fa-plus-circle"></i>
                Add Permission
            </a>
        </div>

        @include('access_control.partials.flash')

        <div class="ac-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 ac-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Module</th>
                            <th>Submodule</th>
                            <th>Key</th>
                            <th>Description</th>
                            <th width="110">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr>
                                <td>{{ $permission->action ?? '-' }}</td>
                                <td>{{ $permission->module ?? '-' }}</td>
                                <td>{{ $permission->submodule ?? '-' }}</td>
                                <td>{{ $permission->key ?? '-' }}</td>
                                <td>{{ $permission->description ?? '-' }}</td>
                                <td>
                                    <div class="ac-actions">
                                        <a href="{{ route('permissions.edit', $permission->id) }}" class="ac-icon-btn" title="Edit Permission">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this permission?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ac-icon-btn ac-icon-danger" title="Delete Permission">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No permissions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="ac-card-body">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
