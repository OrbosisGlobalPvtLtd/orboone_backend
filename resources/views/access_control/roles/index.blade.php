@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Roles')

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
                    <i class="fas fa-user-shield"></i> HRMS &bull; ACCESS CONTROL
                </div>
                <h1 class="ac-title">Roles & Profiles</h1>
                <p class="ac-subtitle">Manage administrative, operational, and system authentication roles.</p>
            </div>
            <!-- Glassmorphic Roles Counter Badge -->
            <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 12px 20px; min-width: 145px; text-align: center; color: #fff; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="font-size: 26px; font-weight: 900; line-height: 1;">{{ $roles->total() ?? $roles->count() }}</div>
                <div style="font-size: 9px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; color: rgba(255, 255, 255, 0.85); white-space: nowrap;">Total Roles</div>
            </div>
        </div>

        @include('access_control.partials.flash')

        <!-- Table Listing Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <h5 class="ac-table-title">Configured Security Profiles</h5>
                        <p class="ac-table-subtitle">Review active permission scopes, protection states, and profile parameters.</p>
                    </div>
                </div>
                <!-- Add Button moved here -->
                <div>
                    <a href="{{ route('roles.create') }}" class="ac-btn ac-btn-primary" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; height: 38px; border-radius: 11px; padding: 0 16px; font-weight: 850; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus-circle"></i> Add Role
                    </a>
                </div>
            </div>

            <!-- Attached real-time automatic filters -->
            <div class="ac-filter-wrapper">
                <div class="ac-filter-row">
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Search Name</label>
                        <input type="text" id="filterRoleName" class="ac-filter-control" placeholder="Search by role name..." onkeyup="applyRoleFilters()">
                    </div>
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Search Code</label>
                        <input type="text" id="filterRoleCode" class="ac-filter-control" placeholder="Search by code slug..." onkeyup="applyRoleFilters()">
                    </div>
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Status State</label>
                        <select id="filterRoleStatus" class="ac-filter-control" onchange="applyRoleFilters()">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; display: flex; align-items: flex-end;">
                        <button type="button" class="ac-btn" style="height: 38px; border-radius: 9px; background: #F1F5F9; border-color: #E2E8F0; color: #475569; font-weight: 850; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" onclick="resetRoleFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="ac-table-wrap">
                <table class="table mb-0 ac-table">
                    <thead>
                        <tr>
                            <th>Name / Profile</th>
                            <th>Role Code</th>
                            <th>Description</th>
                            <th>System Role</th>
                            <th>Status</th>
                            <th width="220" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr class="role-data-row">
                                <td class="role-name-cell"><span style="font-weight: 800; color: var(--ac-text);">{{ $role->name }}</span></td>
                                <td class="role-code-cell">
                                    <span class="d-inline-flex" style="font-family: monospace; font-size: 11px; background: #F1F5F9; border: 1px solid var(--ac-border); border-radius: 6px; padding: 2px 6px;">
                                        {{ $role->slug ?? '-' }}
                                    </span>
                                </td>
                                <td class="role-desc-cell"><span class="text-muted">{{ $role->description ?? 'No description provided' }}</span></td>
                                <td class="role-system-cell">
                                    @if($role->is_system)
                                    <span class="ac-pill ac-pill-on"><i class="fas fa-shield-alt mr-1"></i> Yes</span>
                                    @else
                                    <span class="ac-pill"><i class="fas fa-user mr-1"></i> No</span>
                                    @endif
                                </td>
                                <td class="role-status-cell">
                                    @if($role->status)
                                    <span class="ac-pill ac-pill-on"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                    @else
                                    <span class="ac-pill ac-pill-off"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ac-actions justify-content-end">
                                        <a href="{{ route('roles.edit', $role->id) }}" class="ac-icon-btn" title="Edit Role">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>
                                        <a href="{{ route('role_permissions.edit', $role->id) }}" class="ac-icon-btn" title="Permissions Mapping">
                                            <i class="fas fa-key text-primary"></i>
                                        </a>
                                        <a href="{{ route('role_menus.edit', $role->id) }}" class="ac-icon-btn" title="Menu Access">
                                            <i class="fas fa-sitemap text-success"></i>
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
                                <td colspan="6" class="text-center text-muted py-5">
                                    <div style="font-size: 24px; color: var(--ac-muted);"><i class="fas fa-folder-open"></i></div>
                                    <h6 class="mt-3 font-weight-bold">No Roles Configured</h6>
                                    <p class="small mb-0">Create roles to map custom personnel access levels.</p>
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

<script>
    function applyRoleFilters() {
        var nameVal = document.getElementById('filterRoleName').value.toLowerCase().trim();
        var codeVal = document.getElementById('filterRoleCode').value.toLowerCase().trim();
        var statusVal = document.getElementById('filterRoleStatus').value.toLowerCase().trim();

        document.querySelectorAll('.ac-table tbody tr.role-data-row').forEach(function(row) {
            var nameCell = row.querySelector('.role-name-cell');
            var codeCell = row.querySelector('.role-code-cell');
            var statusCell = row.querySelector('.role-status-cell');

            if (!nameCell) return;

            var nameText = nameCell.textContent.toLowerCase();
            var codeText = codeCell.textContent.toLowerCase();
            var statusText = statusCell ? statusCell.textContent.trim().toLowerCase() : '';

            var matchesName = !nameVal || nameText.includes(nameVal);
            var matchesCode = !codeVal || codeText.includes(codeVal);
            
            var matchesStatus = true;
            if (statusVal === 'active') {
                matchesStatus = statusText.includes('active');
            } else if (statusVal === 'inactive') {
                matchesStatus = statusText.includes('inactive');
            }

            if (matchesName && matchesCode && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function resetRoleFilters() {
        document.getElementById('filterRoleName').value = '';
        document.getElementById('filterRoleCode').value = '';
        document.getElementById('filterRoleStatus').value = '';
        
        document.querySelectorAll('.ac-table tbody tr.role-data-row').forEach(function(row) {
            row.style.display = '';
        });
    }
</script>
@endsection
