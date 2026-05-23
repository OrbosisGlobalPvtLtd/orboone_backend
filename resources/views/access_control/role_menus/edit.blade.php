@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Role Menu Access')

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
                <h1 class="ac-title">Role Menu Access</h1>
                <p class="ac-subtitle">Customize visible navigation links and modules for role "{{ $role->name }}"</p>
            </div>
            <a href="{{ route('role_menus.index') }}" class="ac-btn ac-btn-soft">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        @include('access_control.partials.flash')

        @if($role->slug === 'super_admin')
            <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 18px; font-weight: 700; font-size: 13px; background: #FFF9EB; color: #B25E00; border: 1px solid rgba(247, 144, 9, 0.15);">
                <i class="fas fa-shield-alt mr-2" style="font-size: 16px;"></i> Note: As the ultimate Super Administrator, all sidebar modules and navigation links are visible by default.
            </div>
        @endif

        @php
            $rootMenus = $menus->get('') ?: ($menus->get(null) ?: collect());
        @endphp

        <form action="{{ route('role_menus.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Parent Card for all Menu Settings -->
            <div class="ac-card mb-4">
                <div class="ac-table-header" style="background: #F8FAFC;">
                    <div class="ac-table-head-left">
                        <div class="ac-icon-box" style="background: #fff; border: 1px solid var(--ac-border);"><i class="fas fa-bars text-success"></i></div>
                        <div>
                            <h5 class="ac-table-title">Navigation Hierarchy Mapping</h5>
                            <p class="ac-table-subtitle">Select root folders and sub-nodes to render in this user's sidebar panel.</p>
                        </div>
                    </div>
                </div>

                <div class="ac-card-body" style="padding: 30px;">
                    @forelse($rootMenus as $menu)
                        @php
                            $children = $menus->get($menu->id) ?: collect();
                        @endphp

                        <div class="p-3 mb-4 rounded border" style="background: #FCFCFD; border-color: var(--ac-border);">
                            <!-- Root Menu Checkbox -->
                            <div class="mb-3">
                                <label class="ac-check d-inline-flex align-items-start {{ $role->slug === 'super_admin' ? 'disabled' : '' }}" style="background: #fff; max-width: 420px; width: 100%; border: 1px solid rgba(75, 0, 232, 0.18); box-shadow: 0 4px 10px rgba(75,0,232,0.02); {{ $role->slug === 'super_admin' ? 'opacity: 0.8; cursor: not-allowed;' : '' }}">
                                    <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}"
                                        {{ in_array((int) $menu->id, $selectedMenuIds, true) ? 'checked' : '' }}
                                        {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                    <span>
                                        <strong style="color: var(--ac-primary); font-size: 14px;"><i class="fas fa-folder-open mr-1"></i> {{ $menu->name }}</strong>
                                        <span class="d-inline-flex mt-1" style="font-family: monospace; font-size: 10px; background: #F1F5F9; border-radius: 4px; padding: 1px 6px;">
                                            {{ $menu->route ?: 'Parent Node folder' }}
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <!-- Child Menus list -->
                            @if($children->count())
                                <div class="mt-2 pl-4 border-left" style="border-width: 3px !important; border-color: rgba(75, 0, 232, 0.1) !important;">
                                    <div class="ac-check-list">
                                        @foreach($children as $child)
                                            <label class="ac-check d-flex align-items-start {{ $role->slug === 'super_admin' ? 'disabled' : '' }}" style="background: #fff; {{ $role->slug === 'super_admin' ? 'opacity: 0.8; cursor: not-allowed;' : '' }}">
                                                <input type="checkbox" name="menu_ids[]" value="{{ $child->id }}"
                                                    {{ in_array((int) $child->id, $selectedMenuIds, true) ? 'checked' : '' }}
                                                    {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                                <span>
                                                    <strong><i class="fas fa-link mr-1 text-muted"></i> {{ $child->name }}</strong>
                                                    <span class="d-inline-flex mt-1" style="font-family: monospace; font-size: 10px; background: #F1F5F9; border-radius: 4px; padding: 1px 4px;">
                                                        {{ $child->route ?: 'Child Node' }}
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <div style="font-size: 32px; color: var(--ac-muted);"><i class="fas fa-sitemap"></i></div>
                            <h6 class="mt-3 font-weight-bold">No Menus Found</h6>
                            <p class="small mb-0">The database does not contain any sidebar menus registered.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex align-items-center flex-wrap pt-3" style="gap:8px;">
                @if($role->slug !== 'super_admin')
                    <button type="submit" class="ac-btn ac-btn-primary" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; min-height: 42px; border-radius: 12px; font-weight: 800; padding: 0 24px;">
                        <i class="fas fa-save mr-1"></i> Save Menu Access Configuration
                    </button>
                @endif
                <a href="{{ route('role_menus.index') }}" class="ac-btn ac-btn-soft" style="background: #F1F5F9 !important; color: #475569 !important; border-color: #E2E8F0 !important; min-height: 42px; border-radius: 12px; font-weight: 800;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
