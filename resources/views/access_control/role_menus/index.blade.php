@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Role Menus')

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
                    <i class="fas fa-sitemap"></i> HRMS &bull; ACCESS CONTROL
                </div>
                <h1 class="ac-title">Sidebar Menu Access</h1>
                <p class="ac-subtitle">Map visible navigation nodes and modules inside the employee sidebars.</p>
            </div>
        </div>

        @include('access_control.partials.flash')

        <!-- Table Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-sitemap"></i></div>
                    <div>
                        <h5 class="ac-table-title">Menu Mapping Registers</h5>
                        <p class="ac-table-subtitle">Choose a role to customize visible sub-panels and modules.</p>
                    </div>
                </div>
            </div>

            <div class="ac-table-wrap">
                <table class="table mb-0 ac-table">
                    <thead>
                        <tr>
                            <th>Role / Profile</th>
                            <th>Role Code</th>
                            <th>Status</th>
                            <th width="140" class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>
                                    <div style="font-weight: 800; color: var(--ac-text); font-size: 14px;">{{ $role->name }}</div>
                                </td>
                                <td>
                                    <span class="d-inline-flex" style="font-family: monospace; font-size: 11px; background: #F1F5F9; border: 1px solid var(--ac-border); border-radius: 6px; padding: 2px 6px;">
                                        {{ $role->slug ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($role->status)
                                    <span class="ac-pill ac-pill-on"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                    @else
                                    <span class="ac-pill ac-pill-off"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-right">
                                        <a href="{{ route('role_menus.edit', $role->id) }}" class="ac-btn ac-btn-soft text-primary" style="border-color: rgba(75, 0, 232, 0.15) !important; background: var(--ac-soft) !important;">
                                            <i class="fas fa-bars mr-1"></i> Manage Menus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <div style="font-size: 24px; color: var(--ac-muted);"><i class="fas fa-folder-open"></i></div>
                                    <h6 class="mt-3 font-weight-bold">No Roles Found</h6>
                                    <p class="small mb-0">Create roles first before managing visible sidebar items.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($roles->hasPages())
            <div class="ac-card-body border-top" style="padding: 16px 24px;">
                {{ $roles->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
