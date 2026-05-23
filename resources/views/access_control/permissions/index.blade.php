@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Permissions')

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
                    <i class="fas fa-key"></i> HRMS &bull; ACCESS CONTROL
                </div>
                <h1 class="ac-title">Permissions Registry</h1>
                <p class="ac-subtitle">Manage individual module actions, feature levels, and security keys.</p>
            </div>
            <!-- Glassmorphic Permissions Counter Badge -->
            <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 12px 20px; min-width: 145px; text-align: center; color: #fff; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="font-size: 26px; font-weight: 900; line-height: 1;">{{ $permissions->total() ?? $permissions->count() }}</div>
                <div style="font-size: 9px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; color: rgba(255, 255, 255, 0.85); white-space: nowrap;">Total Keys</div>
            </div>
        </div>

        @include('access_control.partials.flash')

        <!-- Table Listing Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-key"></i></div>
                    <div>
                        <h5 class="ac-table-title">System Authorization Keys</h5>
                        <p class="ac-table-subtitle">Review active module permissions used by role templates.</p>
                    </div>
                </div>
                <!-- Add Button moved here -->
                <div>
                    <a href="{{ route('permissions.create') }}" class="ac-btn ac-btn-primary" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; height: 38px; border-radius: 11px; padding: 0 16px; font-weight: 850; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus-circle"></i> Add Permission
                    </a>
                </div>
            </div>

            <!-- Attached real-time automatic filters -->
            <div class="ac-filter-wrapper">
                <div class="ac-filter-row">
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Search Name</label>
                        <input type="text" id="filterPermName" class="ac-filter-control" placeholder="Search action name..." onkeyup="applyPermissionFilters()">
                    </div>
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Module Filter</label>
                        <select id="filterPermModule" class="ac-filter-control" onchange="applyPermissionFilters()">
                            <option value="">All Modules</option>
                        </select>
                    </div>
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Submodule</label>
                        <input type="text" id="filterPermSubmodule" class="ac-filter-control" placeholder="Search submodule..." onkeyup="applyPermissionFilters()">
                    </div>
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Permission Key</label>
                        <input type="text" id="filterPermKey" class="ac-filter-control" placeholder="Search slug key..." onkeyup="applyPermissionFilters()">
                    </div>
                    <div style="flex: 0 0 auto; display: flex; align-items: flex-end;">
                        <button type="button" class="ac-btn" style="height: 38px; border-radius: 9px; background: #F1F5F9; border-color: #E2E8F0; color: #475569; font-weight: 850; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" onclick="resetPermissionFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="ac-table-wrap">
                <table class="table mb-0 ac-table">
                    <thead>
                        <tr>
                            <th>Action Name</th>
                            <th>Module Key</th>
                            <th>Submodule Key</th>
                            <th>Permission Key</th>
                            <th>Description</th>
                            <th width="110" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr class="perm-data-row">
                                <td class="perm-name-cell"><span style="font-weight: 800; color: var(--ac-text);">{{ $permission->action ?? '-' }}</span></td>
                                <td class="perm-module-cell">
                                    <span class="perm-module-badge d-inline-flex" style="font-family: monospace; font-size: 11px; background: #FAF8FF; border: 1px solid var(--ac-border); border-radius: 6px; padding: 2px 6px; color: var(--ac-primary); font-weight: 700;">
                                        {{ $permission->module ?? '-' }}
                                    </span>
                                </td>
                                <td class="perm-submodule-cell"><span style="font-weight: 650; color: var(--ac-text);">{{ $permission->submodule ?? '-' }}</span></td>
                                <td class="perm-key-cell">
                                    <span class="d-inline-flex" style="font-family: monospace; font-size: 11px; background: #F1F5F9; border: 1px solid var(--ac-border); border-radius: 6px; padding: 2px 6px;">
                                        {{ $permission->key ?? '-' }}
                                    </span>
                                </td>
                                <td class="perm-desc-cell"><span class="text-muted">{{ $permission->description ?? 'No description provided' }}</span></td>
                                <td>
                                    <div class="ac-actions justify-content-end">
                                        <a href="{{ route('permissions.edit', $permission->id) }}" class="ac-icon-btn" title="Edit Permission">
                                            <i class="fas fa-edit text-warning"></i>
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
                                <td colspan="6" class="text-center text-muted py-5">
                                    <div style="font-size: 24px; color: var(--ac-muted);"><i class="fas fa-folder-open"></i></div>
                                    <h6 class="mt-3 font-weight-bold">No Permissions Found</h6>
                                    <p class="small mb-0">Create permission keys to regulate admin controls.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($permissions->hasPages())
            <div class="ac-card-body border-top" style="padding: 16px 24px;">
                {{ $permissions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dynamically extract unique module names from table records to populate filter options
        var moduleSelect = document.getElementById('filterPermModule');
        var modules = new Set();
        document.querySelectorAll('.ac-table tbody tr.perm-data-row').forEach(function(row) {
            var moduleCell = row.querySelector('.perm-module-badge');
            if (moduleCell) {
                var text = moduleCell.textContent.trim();
                if (text && text !== '-') {
                    modules.add(text);
                }
            }
        });
        
        modules.forEach(function(mod) {
            var opt = document.createElement('option');
            opt.value = mod;
            opt.textContent = mod;
            moduleSelect.appendChild(opt);
        });
    });

    function applyPermissionFilters() {
        var nameVal = document.getElementById('filterPermName').value.toLowerCase().trim();
        var moduleVal = document.getElementById('filterPermModule').value.toLowerCase().trim();
        var subVal = document.getElementById('filterPermSubmodule').value.toLowerCase().trim();
        var keyVal = document.getElementById('filterPermKey').value.toLowerCase().trim();

        document.querySelectorAll('.ac-table tbody tr.perm-data-row').forEach(function(row) {
            var nameCell = row.querySelector('.perm-name-cell');
            var moduleCell = row.querySelector('.perm-module-badge');
            var subCell = row.querySelector('.perm-submodule-cell');
            var keyCell = row.querySelector('.perm-key-cell');

            if (!nameCell) return;

            var nameText = nameCell.textContent.toLowerCase();
            var moduleText = moduleCell ? moduleCell.textContent.toLowerCase() : '';
            var subText = subCell ? subCell.textContent.toLowerCase() : '';
            var keyText = keyCell ? keyCell.textContent.toLowerCase() : '';

            var matchesName = !nameVal || nameText.includes(nameVal);
            var matchesModule = !moduleVal || moduleText.includes(moduleVal);
            var matchesSub = !subVal || subText.includes(subVal);
            var matchesKey = !keyVal || keyText.includes(keyVal);

            if (matchesName && matchesModule && matchesSub && matchesKey) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function resetPermissionFilters() {
        document.getElementById('filterPermName').value = '';
        document.getElementById('filterPermModule').value = '';
        document.getElementById('filterPermSubmodule').value = '';
        document.getElementById('filterPermKey').value = '';
        
        document.querySelectorAll('.ac-table tbody tr.perm-data-row').forEach(function(row) {
            row.style.display = '';
        });
    }
</script>
@endsection
