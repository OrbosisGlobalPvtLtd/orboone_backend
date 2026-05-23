@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'Admin Users')

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
                    <i class="fas fa-users-cog"></i> HRMS &bull; ACCESS CONTROL
                </div>
                <h1 class="ac-title">Administrative Access</h1>
                <p class="ac-subtitle">Manage console administrators and system operators without affecting general employee data.</p>
            </div>
            <!-- Dynamic Glassmorphic Account Count Badge -->
            <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 16px; padding: 12px 20px; min-width: 145px; text-align: center; color: #fff; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="font-size: 26px; font-weight: 900; line-height: 1;">{{ $users->total() ?? $users->count() }}</div>
                <div style="font-size: 9px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; color: rgba(255, 255, 255, 0.85); white-space: nowrap;">Admin Accounts</div>
            </div>
        </div>

        @include('access_control.partials.flash')

        <!-- Table Listing Card -->
        <div class="ac-card">
            <div class="ac-table-header">
                <div class="ac-table-head-left">
                    <div class="ac-icon-box"><i class="fas fa-users-cog"></i></div>
                    <div>
                        <h5 class="ac-table-title">Admin Account Registry</h5>
                        <p class="ac-table-subtitle">Review active administrator logins, assigned roles, and application credentials.</p>
                    </div>
                </div>
                <!-- Add Button moved here -->
                <div>
                    <a href="{{ route('admins.create') }}" class="ac-btn ac-btn-primary" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; height: 38px; border-radius: 11px; padding: 0 16px; font-weight: 850; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus-circle"></i> Add Admin
                    </a>
                </div>
            </div>

            <!-- Attached real-time automatic filters -->
            <div class="ac-filter-wrapper">
                <div class="ac-filter-row">
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Search Name</label>
                        <input type="text" id="filterName" class="ac-filter-control" placeholder="Search by name..." onkeyup="applyAdminFilters()">
                    </div>
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Search Email</label>
                        <input type="text" id="filterEmail" class="ac-filter-control" placeholder="Search by email..." onkeyup="applyAdminFilters()">
                    </div>
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Role Filter</label>
                        <select id="filterRole" class="ac-filter-control" onchange="applyAdminFilters()">
                            <option value="">All Roles</option>
                        </select>
                    </div>
                    <div class="ac-filter-col">
                        <label class="ac-filter-label">Status State</label>
                        <select id="filterStatus" class="ac-filter-control" onchange="applyAdminFilters()">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="blocked">Blocked / Inactive</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; display: flex; align-items: flex-end;">
                        <button type="button" class="ac-btn" style="height: 38px; border-radius: 9px; background: #F1F5F9; border-color: #E2E8F0; color: #475569; font-weight: 850; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" onclick="resetAdminFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="ac-table-wrap">
                <table class="table mb-0 ac-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email Address</th>
                            <th>Primary Role</th>
                            <th>Web Console</th>
                            <th>Mobile App</th>
                            <th>Status</th>
                            <th width="110" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="admin-name-cell">
                                    <div style="font-weight: 800; color: var(--ac-text); font-size: 14px;">{{ $user->name }}</div>
                                </td>
                                <td class="admin-email-cell"><span class="text-muted" style="font-weight: 600;">{{ $user->email }}</span></td>
                                <td class="admin-role-cell">
                                    <span class="admin-role-badge d-inline-flex" style="font-size: 11px; background: var(--ac-soft); color: var(--ac-primary); font-weight: 800; border-radius: 6px; padding: 2px 8px; border: 1px solid rgba(75, 0, 232, 0.12);">
                                        {{ $user->role_name ?? 'No Role' }}
                                    </span>
                                </td>
                                <td class="admin-web-cell">
                                    @if($user->is_web_access)
                                    <span class="ac-pill ac-pill-on"><i class="fas fa-desktop mr-1"></i> Enabled</span>
                                    @else
                                    <span class="ac-pill ac-pill-off"><i class="fas fa-ban mr-1"></i> Disabled</span>
                                    @endif
                                </td>
                                <td class="admin-app-cell">
                                    @if($user->is_app_access)
                                    <span class="ac-pill ac-pill-on"><i class="fas fa-mobile-alt mr-1"></i> Enabled</span>
                                    @else
                                    <span class="ac-pill"><i class="fas fa-times mr-1"></i> Disabled</span>
                                    @endif
                                </td>
                                <td class="admin-status-cell">
                                    @if($user->is_active)
                                    <span class="ac-pill ac-pill-on"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                    @else
                                    <span class="ac-pill ac-pill-off"><i class="fas fa-user-slash mr-1"></i> Blocked</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ac-actions justify-content-end">
                                        <a href="{{ route('admins.edit', $user->id) }}" class="ac-icon-btn" title="Edit Access Details">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>
                                        @if(! in_array('super_admin', $user->role_slugs ?? [], true) && (int) $user->id !== (int) auth()->id())
                                            <form action="{{ route('admins.destroy', $user->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this admin user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ac-icon-btn ac-icon-danger" title="Revoke Admin Access">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <div style="font-size: 24px; color: var(--ac-muted);"><i class="fas fa-users-slash"></i></div>
                                    <h6 class="mt-3 font-weight-bold">No Admin Users Configured</h6>
                                    <p class="small mb-0">Publish custom admin keys to allocate administrative privileges.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="ac-card-body border-top" style="padding: 16px 24px;">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dynamically extract unique role names from table records to populate filter options
        var roleSelect = document.getElementById('filterRole');
        var roles = new Set();
        document.querySelectorAll('.ac-table tbody tr').forEach(function(row) {
            var roleCell = row.querySelector('.admin-role-badge');
            if (roleCell) {
                var text = roleCell.textContent.trim();
                if (text && text !== '-' && text !== 'No Role') {
                    // Extract comma separated roles if multiple exist
                    text.split(',').forEach(function(r) {
                        var roleName = r.trim();
                        if (roleName) {
                            roles.add(roleName);
                        }
                    });
                }
            }
        });
        
        roles.forEach(function(role) {
            var opt = document.createElement('option');
            opt.value = role;
            opt.textContent = role;
            roleSelect.appendChild(opt);
        });
    });

    function applyAdminFilters() {
        var nameVal = document.getElementById('filterName').value.toLowerCase().trim();
        var emailVal = document.getElementById('filterEmail').value.toLowerCase().trim();
        var roleVal = document.getElementById('filterRole').value.toLowerCase().trim();
        var statusVal = document.getElementById('filterStatus').value.toLowerCase().trim();

        document.querySelectorAll('.ac-table tbody tr').forEach(function(row) {
            var nameCell = row.querySelector('.admin-name-cell');
            var emailCell = row.querySelector('.admin-email-cell');
            var roleCell = row.querySelector('.admin-role-badge');
            var statusCell = row.querySelector('.admin-status-cell');

            if (!nameCell) return; // skip empty state row

            var nameText = nameCell.textContent.toLowerCase();
            var emailText = emailCell.textContent.toLowerCase();
            var roleText = roleCell ? roleCell.textContent.toLowerCase() : '';
            var statusText = statusCell ? statusCell.textContent.trim().toLowerCase() : '';

            var matchesName = !nameVal || nameText.includes(nameVal);
            var matchesEmail = !emailVal || emailText.includes(emailVal);
            var matchesRole = !roleVal || roleText.includes(roleVal);
            
            var matchesStatus = true;
            if (statusVal === 'active') {
                matchesStatus = statusText.includes('active');
            } else if (statusVal === 'blocked') {
                matchesStatus = statusText.includes('blocked') || statusText.includes('inactive');
            }

            if (matchesName && matchesEmail && matchesRole && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function resetAdminFilters() {
        document.getElementById('filterName').value = '';
        document.getElementById('filterEmail').value = '';
        document.getElementById('filterRole').value = '';
        document.getElementById('filterStatus').value = '';
        
        document.querySelectorAll('.ac-table tbody tr').forEach(function(row) {
            row.style.display = '';
        });
    }
</script>
@endsection
