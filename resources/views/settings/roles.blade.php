@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'accounts'])

@section('_content')
<style>
    :root { --primary-orb: #1560ab; }
    .custom-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .btn-orb { background: var(--primary-orb); color: #fff; border-radius: 50px; padding: 10px 25px; font-weight: 600; transition: all 0.3s; }
    .btn-orb:hover { background: #0d4a8a; transform: translateY(-2px); color: #fff; }
    .table-orb thead th { background: #f8f9fc; color: var(--primary-orb); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; border: none; padding: 15px; }
    .table-orb tbody td { vertical-align: middle; padding: 15px; border-bottom: 1px solid #f1f4f8; }
    .role-badge { background: #f0f7ff; color: var(--primary-orb); padding: 5px 15px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; }
</style>

<div class="container-fluid py-4 px-4">
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6">
            <h4 class="font-weight-bold text-dark mb-1">Access Control Roles</h4>
            <p class="text-muted small mb-0">Define and manage system permissions and user levels</p>
        </div>
        <div class="col-12 col-md-6 text-md-right mt-3 mt-md-0">
            @if (collect($accesses)->where('menu_id', 10)->first()->status == 2)
                <a href="{{ route('roles.create') }}" class="btn btn-orb mr-2">
                    <i class="fas fa-plus mr-2"></i> Create New Role
                </a>
            @endif
            <a href="{{ route('roles.print') }}" class="btn btn-light" style="border-radius: 50px;" target="_blank">
                <i class="fas fa-print mr-2"></i> Print List
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
        </div>
    @endif

    <div class="card custom-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-orb mb-0 text-center">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Rank</th>
                            <th>Role Title</th>
                            <th class="text-right">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                        <tr>
                            <td class="text-muted small">#{{ $loop->iteration + $roles->firstItem() - 1 }}</td>
                            <td class="text-center">
                                <a href="{{ route('roles.show', $role->id) }}" class="role-badge text-decoration-none">
                                    <i class="fas fa-user-shield mr-2"></i> {{ $role->name }}
                                </a>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-light text-primary" title="Edit Permissions">
                                    <i class="fas fa-cog"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection