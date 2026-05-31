@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Mobile App Updates')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@include('settings.partials.styles')

<style>
    /* Styling overrides specifically for Mobile App Updates */
    .apk-page-container {
        max-width: 1380px;
        margin: 0 auto;
    }
    
    .dataTables_filter {
        display: none !important;
    }
    
    .dataTables_length select {
        width: auto !important;
        display: inline-block !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border-radius: 8px !important;
        border: 1px solid var(--set-border) !important;
    }

    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        font-weight: 850 !important;
        font-size: 12px !important;
        color: var(--set-muted) !important;
    }

    .dataTables_info {
        font-size: 12px !important;
        font-weight: 750 !important;
        color: var(--set-muted) !important;
        padding-top: 12px !important;
    }

    .dataTables_paginate {
        padding-top: 12px !important;
    }

    .dataTables_paginate .paginate_button.active a,
    .dataTables_paginate .paginate_button:hover a {
        background: var(--set-primary) !important;
        border-color: var(--set-primary) !important;
        color: #fff !important;
    }

    .dt-buttons .btn {
        height: 32px !important;
        padding: 0 12px !important;
        font-size: 12px !important;
        font-weight: 850 !important;
        border-radius: 9px !important;
        background: var(--set-soft) !important;
        color: var(--set-primary) !important;
        border: 1px solid rgba(75, 0, 232, 0.15) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: none !important;
    }

    .dt-buttons .btn:hover {
        background: var(--set-primary) !important;
        color: #fff !important;
    }

    .apk-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        padding: 0 !important;
        border: 1px solid var(--set-border);
        background: var(--set-bg);
        color: var(--set-text);
        transition: all 0.2s ease;
    }

    .apk-action-btn:hover {
        background: var(--set-soft);
        color: var(--set-primary);
        border-color: rgba(75, 0, 232, 0.18);
    }

    .apk-action-danger {
        color: #EF4444;
        background: #FEF2F2;
        border-color: #FEE2E2;
    }

    .apk-action-danger:hover {
        background: #FEE2E2;
        color: #DC2626;
        border-color: #FCA5A5;
    }

    /* Requested Premium Filters Layout Styles */
    .mobile-filter-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
    }

    .mobile-filter-group {
        display: flex;
        flex-direction: column;
    }

    .mobile-filter-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #667085;
        letter-spacing: .04em;
    }

    .mobile-filter-control {
        width: 100%;
        height: 40px;
        border: 1px solid #E7EAF3;
        border-radius: 12px;
        padding: 0 12px;
        background: #fff;
        font-size: 13px;
        color: #101828;
        outline: none;
        transition: all 0.2s ease;
    }

    .mobile-filter-control:focus {
        border-color: var(--set-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08);
    }

    .mobile-filter-reset {
        height: 40px;
        padding: 0 16px;
        border-radius: 12px;
        border: 1px solid #E7EAF3;
        background: #fff;
        font-weight: 800;
        font-size: 12px;
        color: #475569;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .mobile-filter-reset:hover {
        background: #F1F5F9;
        color: #1E293B;
    }

    @media (max-width: 991px) {
        .mobile-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .mobile-filter-group:last-child {
            grid-column: span 2;
        }
    }

    @media (max-width: 575px) {
        .mobile-filter-grid {
            grid-template-columns: 1fr;
        }
        .mobile-filter-group:last-child {
            grid-column: span 1;
        }
    }
</style>
@endsection

@section('_content')
<div class="set-page">
    <div class="apk-page-container">
        
        <!-- Premium Purple Gradient Hero -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-mobile-alt"></i> HRMS &bull; MOBILE APP
                </div>
                <h1 class="set-title">Mobile App Management</h1>
                <p class="set-subtitle">Manage Android APK versions, release notes, force updates, and active app releases.</p>
            </div>
            
            <!-- Glassmorphic Metrics Badges Grid -->
            <div class="d-flex flex-wrap" style="gap: 12px; justify-content: flex-end;">
                <div class="set-glass-badge">
                    <div style="font-size: 20px; font-weight: 900; line-height: 1;">{{ $stats['latest_version'] }}</div>
                    <div style="font-size: 8px; font-weight: 850; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; opacity: 0.9;">Latest Build</div>
                </div>
                <div class="set-glass-badge">
                    <div style="font-size: 20px; font-weight: 900; line-height: 1;">{{ $stats['latest_version_code'] }}</div>
                    <div style="font-size: 8px; font-weight: 850; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; opacity: 0.9;">Version Code</div>
                </div>
                <div class="set-glass-badge">
                    <div style="font-size: 20px; font-weight: 900; line-height: 1;">{{ $stats['force_update_status'] }}</div>
                    <div style="font-size: 8px; font-weight: 850; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; opacity: 0.9;">Force Update</div>
                </div>
                <div class="set-glass-badge">
                    <div style="font-size: 20px; font-weight: 900; line-height: 1;">{{ $stats['releases_count'] }}</div>
                    <div style="font-size: 8px; font-weight: 850; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; opacity: 0.9;">Total Builds</div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 18px; font-weight: 700; font-size: 13px; background: #FFF9EB; color: #93370D; border: 1px solid rgba(247, 144, 9, 0.15);">
            <i class="fas fa-info-circle mr-2" style="font-size: 16px;"></i> <strong>Private Android distribution guidelines:</strong>
            <ul class="mb-0 mt-2 pl-4">
                <li>Package ID must match <code>com.orbosis.orboone</code> exactly.</li>
                <li>Ensure Flutter <code>versionCode</code> is strictly incremented on every upload.</li>
                <li>Signing keystores must remain identical across successive releases to avoid installation failures.</li>
            </ul>
        </div>

        <!-- Table Listing Card -->
        <div class="set-card">
            <div class="set-card-header">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-mobile-alt"></i></div>
                    <div>
                        <h5 class="set-card-title">APK Release History</h5>
                        <p class="set-card-subtitle">Review deployed build histories, minimum supported API levels, and active states.</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <!-- Export buttons placeholder -->
                    <div id="mobileAppExportButtons"></div>
                    
                    <a href="{{ route('mobile-app.download-latest') }}" class="set-btn set-btn-soft" style="height: 38px; border-radius: 11px; padding: 0 16px; font-weight: 850; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--set-border);">
                        <i class="fas fa-download"></i> Latest APK
                    </a>
                    @if($permissions['canUpload'])
                        <button type="button" class="set-btn" data-toggle="modal" data-target="#uploadApkModal" style="background: linear-gradient(135deg, var(--set-primary), var(--set-secondary)) !important; color: #fff !important; height: 38px; border-radius: 11px; padding: 0 16px; font-weight: 850; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fas fa-upload"></i> Upload APK
                        </button>
                    @endif
                </div>
            </div>

            <!-- Attached real-time automatic filters in responsive grid -->
            <div style="border-bottom: 1px solid var(--set-border); background: #FAF9FE; padding: 20px 24px;">
                <div class="mobile-filter-grid">
                    <div class="mobile-filter-group">
                        <label>Search Version / Code</label>
                        <input type="text" id="filterVersionVal" class="mobile-filter-control" placeholder="e.g. 1.0.0" onkeyup="applyApkFilters()">
                    </div>
                    <div class="mobile-filter-group">
                        <label>Platform</label>
                        <select id="filterPlatform" class="mobile-filter-control" onchange="applyApkFilters()">
                            <option value="">All Platforms</option>
                            <option value="ANDROID">Android</option>
                            <option value="IOS">iOS</option>
                        </select>
                    </div>
                    <div class="mobile-filter-group">
                        <label>Force Update</label>
                        <select id="filterForce" class="mobile-filter-control" onchange="applyApkFilters()">
                            <option value="">All Rules</option>
                            <option value="Yes">Force Update</option>
                            <option value="No">Optional Update</option>
                        </select>
                    </div>
                    <div class="mobile-filter-group">
                        <label>Active State</label>
                        <select id="filterActive" class="mobile-filter-control" onchange="applyApkFilters()">
                            <option value="">All States</option>
                            <option value="Active">Active Only</option>
                            <option value="Inactive">Inactive Only</option>
                        </select>
                    </div>
                    <div class="mobile-filter-group">
                        <button type="button" class="mobile-filter-reset" onclick="resetApkFilters()">
                            <i class="fas fa-undo"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="set-card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="set-table" id="mobileAppVersionsTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Version Name</th>
                                <th>Version Code</th>
                                <th>Min Supported Version</th>
                                <th>Platform</th>
                                <th>Force Update</th>
                                <th>Active Status</th>
                                <th>Release Date</th>
                                <th>Uploaded By</th>
                                <th>APK Size</th>
                                <th width="160" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($versions->count())
                                @foreach($versions as $version)
                                    <tr class="apk-data-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="apk-version-cell">
                                            <span style="font-weight: 800; color: var(--set-text);">{{ $version->version_name }}</span>
                                            <div style="font-size: 10px; color: var(--set-muted); margin-top: 4px; font-family: monospace;">
                                                Path: {{ $version->apk_file }}
                                            </div>
                                            <div style="font-size: 10px; color: var(--set-primary); margin-top: 2px; font-family: monospace;">
                                                URL: /mobile-app/download/{{ $version->id }}
                                            </div>
                                        </td>
                                        <td class="apk-code-cell">
                                            <span class="d-inline-flex" style="font-family: monospace; font-size: 11px; background: #F1F5F9; border: 1px solid var(--set-border); border-radius: 6px; padding: 2px 6px;">
                                                {{ $version->version_code }}
                                            </span>
                                        </td>
                                        <td>{{ $version->min_supported_version_code }}</td>
                                        <td class="apk-platform-cell"><span style="font-weight: 700;">{{ strtoupper($version->platform) }}</span></td>
                                        <td class="apk-force-cell">
                                            @if($version->is_force_update)
                                            <span class="set-badge" style="background: #FEF2F2; color: #EF4444;"><i class="fas fa-exclamation-triangle mr-1"></i> Yes</span>
                                            @else
                                            <span class="set-badge" style="background: #F1F5F9; color: #475569;"><i class="fas fa-info-circle mr-1"></i> No</span>
                                            @endif
                                        </td>
                                        <td class="apk-active-cell">
                                            @if($version->is_active)
                                            <span class="set-badge" style="background: #ECFDF3; color: #027A48;"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                            @else
                                            <span class="set-badge" style="background: #F2F4F7; color: #475467;"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($version->release_date)->format('d M Y, h:i A') ?? '-' }}</td>
                                        <td><span class="text-muted">{{ optional($version->uploader)->name ?? 'System' }}</span></td>
                                        <td><span style="font-weight: 700;">{{ $version->apk_size ? number_format($version->apk_size / 1048576, 2) . ' MB' : '-' }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-end" style="gap: 6px;">
                                                <a href="{{ route('mobile-app.download-public', $version->id) }}" class="apk-action-btn" title="Download APK">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @if($permissions['canManage'] && ! $version->is_active)
                                                    <form method="POST" action="{{ route('hrms.mobile-app-versions.toggle-active', $version->id) }}" class="m-0" onsubmit="return confirm('Set this APK version as active?')">
                                                        @csrf
                                                        <button type="submit" class="apk-action-btn" title="Set Active">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <button type="button" class="apk-action-btn" title="View Release Notes" data-toggle="modal" data-target="#releaseNotesModal{{ $version->id }}">
                                                    <i class="fas fa-file-alt"></i>
                                                </button>
                                                @if($permissions['canDelete'])
                                                    <form method="POST" action="{{ route('hrms.mobile-app-versions.destroy', $version->id) }}" class="m-0" onsubmit="return confirm('Delete this APK release and its file?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="apk-action-btn apk-action-danger" title="Delete Build">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            <!-- Release Notes Modal -->
                                            <div class="modal fade" id="releaseNotesModal{{ $version->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content" style="border-radius: 24px; overflow: hidden; border: 0; box-shadow: var(--set-shadow);">
                                                        <div class="modal-header" style="background: linear-gradient(135deg, var(--set-primary), var(--set-secondary)); color: #fff; padding: 20px 24px;">
                                                            <div>
                                                                <h5 class="modal-title font-weight-bold" style="margin: 0; font-size: 16px;">Release Notes</h5>
                                                                <p style="margin: 4px 0 0; opacity: 0.85; font-size: 11px;">Version {{ $version->version_name }} (Code: {{ $version->version_code }})</p>
                                                            </div>
                                                            <button type="button" class="close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body" style="padding: 24px; background: #fff;">
                                                            <pre style="white-space:pre-wrap; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--set-text); margin: 0; background: #F8FAFC; border: 1px solid var(--set-border); padding: 15px; border-radius: 14px;">{{ $version->release_notes ?: 'No release notes added.' }}</pre>
                                                        </div>
                                                        <div class="modal-footer" style="background: #F8FAFC; border-top: 1px solid var(--set-border); padding: 12px 24px; display: flex; justify-content: flex-end;">
                                                            <button type="button" class="set-btn set-btn-soft btn-sm" data-dismiss="modal" style="min-height: 32px; border-radius: 8px;">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($permissions['canUpload'])
<div class="modal fade" id="uploadApkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 24px; overflow: hidden; border: 0; box-shadow: var(--set-shadow);">
            <form method="POST" action="{{ route('hrms.mobile-app-versions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, var(--set-primary), var(--set-secondary)); color: #fff; padding: 20px 24px;">
                    <div>
                        <h5 class="modal-title font-weight-bold" style="margin: 0; font-size: 16px;">Upload APK Release</h5>
                        <p style="margin: 4px 0 0; opacity: 0.85; font-size: 11px;">Deploy a new Android build for private distribution</p>
                    </div>
                    <button type="button" class="close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px; background: #fff;">
                    <!-- Section 1: Build Information -->
                    <div class="mb-4">
                        <h6 style="font-weight: 850; color: var(--set-primary); text-transform: uppercase; font-size: 11px; letter-spacing: 0.6px; margin-bottom: 15px;">
                            <i class="fas fa-info-circle mr-1"></i> Build Information
                        </h6>
                        <div class="set-grid">
                            <div>
                                <label class="set-label">App Name</label>
                                <input type="text" name="app_name" class="set-control" value="{{ old('app_name', 'OrboOne HRMS') }}">
                            </div>
                            <div>
                                <label class="set-label">Platform</label>
                                <select name="platform" class="set-control" required>
                                    <option value="android" {{ old('platform', 'android') === 'android' ? 'selected' : '' }}>Android</option>
                                </select>
                            </div>
                            <div>
                                <label class="set-label">Version Name</label>
                                <input type="text" name="version_name" class="set-control" value="{{ old('version_name') }}" placeholder="1.0.0" required>
                            </div>
                            <div>
                                <label class="set-label">Version Code</label>
                                <input type="number" name="version_code" class="set-control" value="{{ old('version_code') }}" min="1" required>
                            </div>
                            <div style="grid-column: span 2;">
                                <label class="set-label">Minimum Supported Version Code</label>
                                <input type="number" name="min_supported_version_code" class="set-control" value="{{ old('min_supported_version_code', 1) }}" min="1" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Release Artifacts -->
                    <div>
                        <h6 style="font-weight: 850; color: var(--set-primary); text-transform: uppercase; font-size: 11px; letter-spacing: 0.6px; margin-bottom: 15px;">
                            <i class="fas fa-file-archive mr-1"></i> Release Artifacts & Details
                        </h6>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                            <div>
                                <label class="set-label">APK Binary File <span class="text-danger">*</span></label>
                                <input type="file" name="apk_file" class="set-control" accept=".apk,application/vnd.android.package-archive" required style="padding: 6px 14px; height: 38px;">
                            </div>
                            <div>
                                <label class="set-label">Release Notes</label>
                                <textarea name="release_notes" class="set-control" rows="3" placeholder="Highlight features and fixes (one per line)">{{ old('release_notes') }}</textarea>
                            </div>
                            <div>
                                <label class="switch d-inline-flex align-items-center" style="gap: 12px; font-weight: 800; color: var(--set-text); cursor: pointer; width: auto; height: auto;">
                                    <input type="checkbox" name="is_force_update" value="1" {{ old('is_force_update') ? 'checked' : '' }}>
                                    <span class="slider" style="position: relative; display: inline-block; width: 42px; height: 24px; flex-shrink: 0;"></span>
                                    <span>Force Update (Block access to older versions)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #F8FAFC; border-top: 1px solid var(--set-border); padding: 16px 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="set-btn set-btn-soft" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="set-btn">
                        <i class="fas fa-save mr-1"></i> Publish & Save Build
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    $(function () {
        var table = null;
        if ($.fn.DataTable && $('#mobileAppVersionsTable tbody tr.apk-data-row').length) {
            table = $('#mobileAppVersionsTable').DataTable({
                pageLength: 10,
                order: [[2, 'desc']],
                responsive: false,
                dom: 'Bfrtip',
                buttons: ['csv', 'excel', 'pdf', 'print'],
                language: {
                    emptyTable: 'No APK releases uploaded yet.',
                    zeroRecords: 'No matching records found.'
                }
            });

            table.buttons().container().appendTo('#mobileAppExportButtons');
        }

        @if($errors->any())
            $('#uploadApkModal').modal('show');
        @endif
    });

    function applyApkFilters() {
        var versionVal = document.getElementById('filterVersionVal').value.toLowerCase().trim();
        var platformVal = document.getElementById('filterPlatform').value.toLowerCase().trim();
        var forceVal = document.getElementById('filterForce').value.toLowerCase().trim();
        var activeVal = document.getElementById('filterActive').value.toLowerCase().trim();

        document.querySelectorAll('.set-table tbody tr.apk-data-row').forEach(function(row) {
            var versionCell = row.querySelector('.apk-version-cell');
            var codeCell = row.querySelector('.apk-code-cell');
            var platformCell = row.querySelector('.apk-platform-cell');
            var forceCell = row.querySelector('.apk-force-cell');
            var activeCell = row.querySelector('.apk-active-cell');

            if (!versionCell) return;

            var versionText = versionCell.textContent.toLowerCase() + " " + codeCell.textContent.trim();
            var platformText = platformCell.textContent.toLowerCase();
            var forceText = forceCell ? forceCell.textContent.trim().toLowerCase() : '';
            var activeText = activeCell ? activeCell.textContent.trim().toLowerCase() : '';

            var matchesVersion = !versionVal || versionText.includes(versionVal);
            var matchesPlatform = !platformVal || platformText.includes(platformVal);
            var matchesForce = !forceVal || forceText.includes(forceVal);
            var matchesActive = !activeVal || activeText.includes(activeVal);

            if (matchesVersion && matchesPlatform && matchesForce && matchesActive) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function resetApkFilters() {
        document.getElementById('filterVersionVal').value = '';
        document.getElementById('filterPlatform').value = '';
        document.getElementById('filterForce').value = '';
        document.getElementById('filterActive').value = '';
        
        document.querySelectorAll('.set-table tbody tr.apk-data-row').forEach(function(row) {
            row.style.display = '';
        });
    }
</script>
@endsection
