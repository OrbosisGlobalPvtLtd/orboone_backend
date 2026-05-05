@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Role Menu Access')

@section('_content')
@include('access_control.partials.styles')

<div class="ac-page">
    <div class="ac-container">
        <div class="ac-header">
            <div>
                <h1 class="ac-title">Role Menu Access</h1>
                <p class="ac-subtitle">{{ $role->name }}</p>
            </div>
            <a href="{{ route('role_menus.index') }}" class="ac-btn ac-btn-soft">Back</a>
        </div>

        @include('access_control.partials.flash')

        @php
            $rootMenus = $menus->get('') ?: ($menus->get(null) ?: collect());
        @endphp

        <form action="{{ route('role_menus.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="ac-card mb-3">
                <div class="ac-card-body">
                    @forelse($rootMenus as $menu)
                        @php
                            $children = $menus->get($menu->id) ?: collect();
                        @endphp

                        <div class="mb-3">
                            <label class="ac-check">
                                <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}"
                                    {{ in_array((int) $menu->id, $selectedMenuIds, true) ? 'checked' : '' }}
                                    {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                <span>
                                    <strong>{{ $menu->name }}</strong>
                                    <span>{{ $menu->route ?: 'Parent menu' }}</span>
                                </span>
                            </label>

                            @if($children->count())
                                <div class="ac-check-list mt-2" style="padding-left:20px;">
                                    @foreach($children as $child)
                                        <label class="ac-check">
                                            <input type="checkbox" name="menu_ids[]" value="{{ $child->id }}"
                                                {{ in_array((int) $child->id, $selectedMenuIds, true) ? 'checked' : '' }}
                                                {{ $role->slug === 'super_admin' ? 'checked disabled' : '' }}>
                                            <span>
                                                <strong>{{ $child->name }}</strong>
                                                <span>{{ $child->route ?: 'Child menu' }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No menus found.</div>
                    @endforelse
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                <button type="submit" class="ac-btn ac-btn-primary">
                    <i class="fas fa-save"></i>
                    Save Mapping
                </button>
                <a href="{{ route('role_menus.index') }}" class="ac-btn ac-btn-soft">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
