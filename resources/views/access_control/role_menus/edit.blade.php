@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Role Menu Access')

@section('_head')
@include('access_control.partials.styles')
<style>
    .ac-filter-pill {
        border: 1px solid #E2E8F0;
        background: #fff;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .ac-filter-pill:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #1E293B;
    }
    .ac-filter-pill.active {
        background: var(--ac-primary, #4B00E8);
        border-color: var(--ac-primary, #4B00E8);
        color: #fff;
        box-shadow: 0 3px 10px rgba(75, 0, 232, 0.2);
    }
    .ac-filter-pill.active .badge {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }
    .badge-ess {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        font-size: 10px;
        font-weight: 700;
        border-radius: 6px;
        padding: 2px 6px;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .badge-admin {
        background: rgba(100, 116, 139, 0.1);
        color: #64748B;
        font-size: 10px;
        font-weight: 700;
        border-radius: 6px;
        padding: 2px 6px;
        border: 1px solid rgba(100, 116, 139, 0.2);
    }
    .menu-card-item.is-ess {
        border-left: 3px solid #10B981 !important;
    }
    .menu-card-item.is-admin {
        border-left: 3px solid #94A3B8 !important;
    }
    .ac-search-input {
        height: 38px;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        padding: 6px 14px 6px 36px;
        font-size: 13px;
        font-weight: 600;
        background: #fff;
        color: #1E293B;
        outline: none;
        width: 100%;
        max-width: 320px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .ac-search-input:focus {
        border-color: var(--ac-primary, #4B00E8);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12);
    }
    .ac-btn-action {
        font-size: 11px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background: #fff;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .ac-btn-action:hover {
        background: #F1F5F9;
        color: #0F172A;
    }
</style>
@endsection

@section('_content')
@php
    $isSelfServiceMenu = fn ($menu) => \App\Services\AccessControl\PermissionMapS::isEmployeeSelfServiceMenu($menu);

    $rawMenus = $allMenus ?? $menus;
    $menusGrouped = collect($rawMenus);
    $rootMenus = $menusGrouped->get('') ?: ($menusGrouped->get(null) ?: ($menusGrouped->get(0) ?: collect()));

    $getChildren = function($parentId) use ($menusGrouped) {
        return $menusGrouped->get($parentId) 
            ?: ($menusGrouped->get((int)$parentId) 
            ?: ($menusGrouped->get((string)$parentId) ?: collect()));
    };

    $totalCount = 0;
    $essCount = 0;
    $adminCount = 0;

    foreach ($rootMenus as $rm) {
        $totalCount++;
        if ($isSelfServiceMenu($rm)) { $essCount++; } else { $adminCount++; }
        $cList = $getChildren($rm->id);
        foreach ($cList as $cm) {
            $totalCount++;
            if ($isSelfServiceMenu($cm)) { $essCount++; } else { $adminCount++; }
        }
    }
@endphp

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

        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 18px; font-weight: 700; font-size: 13px; background: #EFF6FF; color: #1E40AF; border: 1px solid rgba(59, 130, 246, 0.2);">
            <i class="fas fa-shield-alt mr-2" style="font-size: 16px;"></i> Note: Super Administrator cannot remove from the sidebar navigation, as it contains essential administrative and system links.
        </div>

        <form action="{{ route('role_menus.update', $role->id) }}" method="POST" id="roleMenuForm">
            @csrf
            @method('PUT')

            <!-- Filter & Quick Action Control Center -->
            <div class="ac-card mb-4">
                <div class="p-3" style="background: #F8FAFC; border-bottom: 1px solid var(--ac-border);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 14px;">
                        <!-- Filter Tabs -->
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <button type="button" class="ac-filter-pill active" data-filter="all">
                                <i class="fas fa-layer-group"></i> All Menus <span class="badge badge-light ml-1" style="background:#E2E8F0; color:#334155;">{{ $totalCount }}</span>
                            </button>
                            <button type="button" class="ac-filter-pill" data-filter="ess">
                                <i class="fas fa-user-check text-success"></i> Employee Self Service (ESS) <span class="badge ml-1" style="background:rgba(16,185,129,0.15); color:#059669;">{{ $essCount }}</span>
                            </button>
                            <button type="button" class="ac-filter-pill" data-filter="admin">
                                <i class="fas fa-user-shield text-secondary"></i> Admin & Management <span class="badge ml-1" style="background:#E2E8F0; color:#475569;">{{ $adminCount }}</span>
                            </button>
                        </div>

                        <!-- Live Search Input -->
                        <div class="position-relative" style="min-width: 240px; flex: 1 1 280px; max-width: 380px;">
                            <i class="fas fa-search position-absolute" style="left: 12px; top: 12px; color: #94A3B8; font-size: 13px;"></i>
                            <input type="text" id="menuSearchInput" class="ac-search-input" style="max-width: 100%; width: 100%;" placeholder="Search menu or route..." autocomplete="off">
                        </div>
                    </div>

                    <!-- Batch Toggle Actions -->
                    <div class="d-flex align-items-center flex-wrap mt-3 pt-3 border-top" style="gap: 8px; border-color: #E2E8F0 !important;">
                        <span class="small font-weight-bold text-muted mr-1"><i class="fas fa-bolt text-warning mr-1"></i> Quick Actions:</span>
                        <button type="button" class="ac-btn-action" id="btnSelectAllEss">
                            <i class="fas fa-check-double text-success mr-1"></i> Select All Self-Service
                        </button>
                        <button type="button" class="ac-btn-action" id="btnDeselectAllEss">
                            <i class="fas fa-times text-danger mr-1"></i> Deselect All Self-Service
                        </button>
                        <button type="button" class="ac-btn-action" id="btnSelectAllActive">
                            <i class="fas fa-check-circle text-primary mr-1"></i> Select All Active
                        </button>
                        <button type="button" class="ac-btn-action" id="btnDeselectAll">
                            <i class="fas fa-minus-circle text-muted mr-1"></i> Deselect All
                        </button>
                    </div>
                </div>

                <div class="ac-card-body" style="padding: 24px;">
                    @forelse($rootMenus as $menu)
                        @php
                            $children = $getChildren($menu->id);
                            $rootIsEss = $isSelfServiceMenu($menu);
                            $childEssCount = $children->filter(fn($c) => $isSelfServiceMenu($c))->count();
                            $childTotalCount = $children->count();
                        @endphp

                        <div class="menu-folder-card p-3 mb-4 rounded border" 
                             data-folder-id="{{ $menu->id }}"
                             data-is-ess="{{ ($rootIsEss || $childEssCount > 0) ? '1' : '0' }}"
                             data-is-admin="{{ (!$rootIsEss || ($childTotalCount > $childEssCount)) ? '1' : '0' }}"
                             style="background: #FCFCFD; border-color: var(--ac-border);">
                            
                            <!-- Root Menu Row -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap: 10px;">
                                <label class="ac-check d-inline-flex align-items-start menu-node root-menu-node {{ $rootIsEss ? 'is-ess' : 'is-admin' }}" 
                                       data-menu-id="{{ $menu->id }}"
                                       data-is-ess="{{ $rootIsEss ? '1' : '0' }}"
                                       style="background: #fff; max-width: 440px; width: 100%; border: 1px solid rgba(75, 0, 232, 0.18); box-shadow: 0 4px 10px rgba(75,0,232,0.02);">
                                    <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}" class="root-menu-check" data-folder="{{ $menu->id }}" data-is-ess="{{ $rootIsEss ? '1' : '0' }}"
                                        {{ in_array((int) $menu->id, $selectedMenuIds, true) ? 'checked' : '' }}
                                        {{ ! $menu->is_active ? 'disabled' : '' }}>
                                    <span>
                                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                            <strong style="color: var(--ac-primary); font-size: 14px;">
                                                <i class="{{ $menu->icon ?: 'fas fa-folder-open' }} mr-1"></i> {{ $menu->name }}
                                            </strong>
                                            @if($rootIsEss)
                                                <span class="badge-ess"><i class="fas fa-user-check"></i> Self Service</span>
                                            @else
                                                <span class="badge-admin"><i class="fas fa-shield-alt"></i> Module Folder</span>
                                            @endif
                                        </div>
                                        <span class="d-inline-flex mt-1" style="font-family: monospace; font-size: 10px; background: #F1F5F9; border-radius: 4px; padding: 1px 6px;">
                                            {{ $menu->route ?: 'Parent Node folder' }}
                                        </span>
                                        @if(! $menu->is_active)<span class="d-inline-flex mt-1 text-danger" style="font-size:10px;font-weight:800;">Inactive — hidden from sidebar</span>@endif
                                    </span>
                                </label>

                                @if($children->count())
                                    <div class="d-flex align-items-center" style="gap: 6px;">
                                        <span class="small text-muted font-weight-bold mr-2">
                                            {{ $children->count() }} items @if($childEssCount > 0) &bull; <span class="text-success">{{ $childEssCount }} Self-Service</span> @endif
                                        </span>
                                        <button type="button" class="btn btn-sm btn-light border py-1 px-2 btn-toggle-folder" data-folder="{{ $menu->id }}" data-action="select" style="font-size: 11px; font-weight: 700;">
                                            <i class="fas fa-check mr-1 text-success"></i> Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border py-1 px-2 btn-toggle-folder" data-folder="{{ $menu->id }}" data-action="deselect" style="font-size: 11px; font-weight: 700;">
                                            <i class="fas fa-times mr-1 text-danger"></i> Clear
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Child Menus List -->
                            @if($children->count())
                                <div class="mt-2 pl-4 border-left child-container" style="border-width: 3px !important; border-color: rgba(75, 0, 232, 0.12) !important;">
                                    <div class="ac-check-list">
                                        @foreach($children as $child)
                                            @php
                                                $childIsEss = $isSelfServiceMenu($child);
                                            @endphp
                                            <label class="ac-check menu-card-item menu-node child-menu-node d-flex align-items-start {{ $childIsEss ? 'is-ess' : 'is-admin' }}" 
                                                   data-menu-id="{{ $child->id }}"
                                                   data-parent-id="{{ $menu->id }}"
                                                   data-is-ess="{{ $childIsEss ? '1' : '0' }}"
                                                   style="background: #fff;">
                                                <input type="checkbox" name="menu_ids[]" value="{{ $child->id }}" class="child-menu-check" data-folder="{{ $menu->id }}" data-is-ess="{{ $childIsEss ? '1' : '0' }}"
                                                    {{ in_array((int) $child->id, $selectedMenuIds, true) ? 'checked' : '' }}
                                                    {{ ! $child->is_active ? 'disabled' : '' }}>
                                                <span class="w-100">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                                        <strong>
                                                            <i class="{{ $child->icon ?: 'fas fa-link' }} mr-1 text-muted"></i> {{ $child->name }}
                                                        </strong>
                                                        @if($childIsEss)
                                                            <span class="badge-ess"><i class="fas fa-user-check"></i> Self Service</span>
                                                        @else
                                                            <span class="badge-admin"><i class="fas fa-shield-alt"></i> Admin / HR</span>
                                                        @endif
                                                    </div>
                                                    <span class="d-inline-flex mt-1" style="font-family: monospace; font-size: 10px; background: #F1F5F9; border-radius: 4px; padding: 1px 4px;">
                                                        {{ $child->route ?: 'Child Node' }}
                                                    </span>
                                                    @if(! $child->is_active)<span class="d-inline-flex mt-1 text-danger" style="font-size:10px;font-weight:800;">Inactive — hidden from sidebar</span>@endif
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
                <button type="submit" class="ac-btn ac-btn-primary" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; min-height: 42px; border-radius: 12px; font-weight: 800; padding: 0 24px;">
                    <i class="fas fa-save mr-1"></i> Save Menu Access Configuration
                </button>
                <a href="{{ route('role_menus.index') }}" class="ac-btn ac-btn-soft" style="background: #F1F5F9 !important; color: #475569 !important; border-color: #E2E8F0 !important; min-height: 42px; border-radius: 12px; font-weight: 800;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterPills = document.querySelectorAll('.ac-filter-pill');
    const searchInput = document.getElementById('menuSearchInput');
    const folderCards = document.querySelectorAll('.menu-folder-card');

    let currentFilter = 'all';

    function applyFilters() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();

        folderCards.forEach(card => {
            const rootNode = card.querySelector('.root-menu-node');
            const childNodes = card.querySelectorAll('.child-menu-node');
            const childContainer = card.querySelector('.child-container');

            let visibleChildCount = 0;

            childNodes.forEach(childNode => {
                const isEss = childNode.getAttribute('data-is-ess') === '1';
                const text = childNode.textContent.toLowerCase();

                let matchesFilter = true;
                if (currentFilter === 'ess') {
                    matchesFilter = isEss;
                } else if (currentFilter === 'admin') {
                    matchesFilter = !isEss;
                }

                let matchesSearch = true;
                if (query !== '') {
                    matchesSearch = text.includes(query);
                }

                if (matchesFilter && matchesSearch) {
                    childNode.style.setProperty('display', 'flex', 'important');
                    visibleChildCount++;
                } else {
                    childNode.style.setProperty('display', 'none', 'important');
                }
            });

            // Check if root node matches search query
            const rootText = rootNode ? rootNode.textContent.toLowerCase() : '';
            const rootIsEss = rootNode ? (rootNode.getAttribute('data-is-ess') === '1') : false;

            let rootMatchesFilter = true;
            if (currentFilter === 'ess') {
                rootMatchesFilter = rootIsEss;
            } else if (currentFilter === 'admin') {
                rootMatchesFilter = !rootIsEss;
            }

            let rootMatchesSearch = true;
            if (query !== '') {
                rootMatchesSearch = rootText.includes(query);
            }

            
            const cardVisible = (visibleChildCount > 0) || (rootMatchesFilter && rootMatchesSearch);

            if (cardVisible) {
                card.style.setProperty('display', 'block', 'important');
                if (rootNode) {
                    rootNode.style.setProperty('display', 'inline-flex', 'important');
                }
                if (childContainer) {
                    if (visibleChildCount > 0) {
                        childContainer.style.setProperty('display', 'block', 'important');
                    } else {
                        childContainer.style.setProperty('display', 'none', 'important');
                    }
                }
            } else {
                card.style.setProperty('display', 'none', 'important');
            }
        });
    }

    // Filter pills click
    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            filterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            applyFilters();
        });
    });

    // Search input
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    // Initial run
    applyFilters();

    // Auto check parent folder when any child is checked
    document.querySelectorAll('.child-menu-check').forEach(child => {
        child.addEventListener('change', function() {
            if (this.checked) {
                const folderId = this.getAttribute('data-folder');
                const rootCheck = document.querySelector(`.root-menu-check[data-folder="${folderId}"]`);
                if (rootCheck && !rootCheck.disabled) {
                    rootCheck.checked = true;
                }
            }
        });
    });

    // Select All Self-Service button
    const btnSelectAllEss = document.getElementById('btnSelectAllEss');
    if (btnSelectAllEss) {
        btnSelectAllEss.addEventListener('click', function() {
            document.querySelectorAll('input[type="checkbox"][data-is-ess="1"]').forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = true;
                    // Also check parent folder
                    const folderId = cb.getAttribute('data-folder');
                    if (folderId) {
                        const rootCheck = document.querySelector(`.root-menu-check[data-folder="${folderId}"]`);
                        if (rootCheck && !rootCheck.disabled) {
                            rootCheck.checked = true;
                        }
                    }
                }
            });
            // Also ensure dashboard (id 1) is checked
            const dashCheck = document.querySelector('.root-menu-check[value="1"]');
            if (dashCheck && !dashCheck.disabled) dashCheck.checked = true;
        });
    }

    // Deselect All Self-Service button
    const btnDeselectAllEss = document.getElementById('btnDeselectAllEss');
    if (btnDeselectAllEss) {
        btnDeselectAllEss.addEventListener('click', function() {
            document.querySelectorAll('input[type="checkbox"][data-is-ess="1"]').forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = false;
                }
            });
        });
    }

    // Select All Active
    const btnSelectAllActive = document.getElementById('btnSelectAllActive');
    if (btnSelectAllActive) {
        btnSelectAllActive.addEventListener('click', function() {
            document.querySelectorAll('#roleMenuForm input[type="checkbox"]').forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = true;
                }
            });
        });
    }

    // Deselect All
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', function() {
            document.querySelectorAll('#roleMenuForm input[type="checkbox"]').forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = false;
                }
            });
        });
    }

    // Folder select/deselect toggle buttons
    document.querySelectorAll('.btn-toggle-folder').forEach(btn => {
        btn.addEventListener('click', function() {
            const folderId = this.getAttribute('data-folder');
            const action = this.getAttribute('data-action');
            const shouldCheck = (action === 'select');

            const rootCheck = document.querySelector(`.root-menu-check[data-folder="${folderId}"]`);
            if (rootCheck && !rootCheck.disabled) {
                rootCheck.checked = shouldCheck;
            }

            document.querySelectorAll(`.child-menu-check[data-folder="${folderId}"]`).forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = shouldCheck;
                }
            });
        });
    });
});
</script>
@endsection
