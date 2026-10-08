@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Mobile App Updates')

@section('_head')
<!-- DataTables & Export CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
@include('settings.partials.styles')

<style>
    /* ================= ORBO SELECT2 THEME ================= */
    .select2-container--default .select2-selection--single {
        height: 40px !important;
        border: 1px solid var(--set-border, #E7EAF3) !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 12px !important;
        background-color: #ffffff !important;
        transition: all 0.2s ease;
    }
    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default .select2-selection--single:focus {
        border-color: var(--set-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--set-text, #101828) !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        right: 10px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-right: 18px !important;
        color: #94a3b8 !important;
        font-size: 16px !important;
    }
    .select2-dropdown {
        border: 1px solid var(--set-border, #E7EAF3) !important;
        border-radius: 12px !important;
        box-shadow: 0 12px 30px rgba(16, 24, 40, 0.12) !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        z-index: 9999 !important;
        overflow: hidden !important;
    }
    .select2-search--dropdown {
        padding: 8px !important;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 8px !important;
        border: 1px solid var(--set-border, #E7EAF3) !important;
        padding: 6px 10px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        outline: none !important;
    }
    .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--set-primary, #4B00E8) !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--set-primary, #4B00E8) !important;
        color: #ffffff !important;
    }
    .select2-results__option[aria-selected="true"] {
        background-color: var(--set-soft, #F4F2FF) !important;
        color: var(--set-primary, #4B00E8) !important;
        font-weight: 800 !important;
    }

    /* ================= ORBO MOBILE APP MANAGEMENT THEME ================= */
    .apk-page-container {
        max-width: 1380px;
        margin: 0 auto;
    }

    /* Glassmorphic Metrics Hero Badges */
    .apk-metrics-wrap {
        display: grid;
        grid-template-columns: repeat(4, minmax(110px, 1fr));
        gap: 12px;
    }

    .apk-glass-chip {
        background: rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 18px;
        padding: 12px 16px;
        text-align: center;
        color: #fff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .apk-glass-chip .chip-num {
        font-size: 20px;
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .apk-glass-chip .chip-title {
        font-size: 9px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 4px;
        opacity: 0.92;
        white-space: nowrap;
    }

    /* Alert Notes */
    .apk-guideline-box {
        background: #FFFDF5;
        border: 1px solid #FEEFC6;
        border-radius: 18px;
        padding: 16px 20px;
        box-shadow: 0 4px 14px rgba(247, 144, 9, 0.04);
    }

    /* Filter Toolbar Section */
    .apk-filter-card {
        background: #FAF9FE;
        border-bottom: 1px solid var(--set-border);
        padding: 18px 24px;
    }

    .apk-filter-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
    }

    .apk-filter-item label {
        display: block;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--set-muted);
    }

    .apk-filter-control {
        width: 100%;
        height: 40px;
        border: 1px solid var(--set-border);
        border-radius: 12px;
        padding: 0 12px;
        background: #ffffff;
        font-size: 13px;
        font-weight: 600;
        color: var(--set-text);
        outline: none;
        transition: all 0.2s ease;
    }

    .apk-filter-control:focus {
        border-color: var(--set-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08);
        background: #fff;
    }

    .apk-btn-reset {
        height: 40px;
        padding: 0 16px;
        border-radius: 12px;
        border: 1px solid var(--set-border);
        background: #ffffff;
        font-weight: 800;
        font-size: 12px;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .apk-btn-reset:hover {
        background: #F1F5F9;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    /* Table Toolbar Strip below Filter */
    .apk-table-toolbar {
        padding: 14px 24px;
        background: #ffffff;
        border-bottom: 1px solid var(--set-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    /* Table Toolbar Tools (Export & Add) */
    .dt-buttons {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        flex-wrap: wrap !important;
    }

    .dt-buttons .btn {
        height: 36px !important;
        padding: 0 14px !important;
        font-size: 12px !important;
        font-weight: 850 !important;
        border-radius: 10px !important;
        background: #ffffff !important;
        color: #475569 !important;
        border: 1px solid var(--set-border) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
        transition: all 0.2s ease !important;
    }

    .dt-buttons .btn:hover {
        background: var(--set-soft) !important;
        color: var(--set-primary) !important;
        border-color: rgba(75, 0, 232, 0.25) !important;
        transform: translateY(-1px);
    }

    /* Table & Row Styling */
    .apk-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .apk-table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .apk-table th {
        background: var(--set-soft) !important;
        color: var(--set-primary) !important;
        font-weight: 850 !important;
        text-transform: uppercase !important;
        font-size: 11px !important;
        letter-spacing: 0.6px !important;
        padding: 14px 16px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--set-border) !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .apk-table td {
        padding: 14px 16px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid var(--set-border) !important;
        color: var(--set-text) !important;
        font-size: 13px !important;
        background: #ffffff;
        transition: background 0.15s ease;
    }

    .apk-table tbody tr:hover td {
        background: #FAFAFD !important;
    }

    /* Cell Badges & Components */
    .apk-version-badge {
        font-size: 13.5px;
        font-weight: 850;
        color: var(--set-text);
        letter-spacing: -0.01em;
    }

    .apk-meta-path {
        font-size: 10.5px;
        color: var(--set-muted);
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
        margin-top: 3px;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .apk-code-chip {
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
        font-size: 11.5px;
        font-weight: 750;
        background: #F8FAFC;
        border: 1px solid var(--set-border);
        border-radius: 8px;
        padding: 3px 8px;
        display: inline-flex;
        align-items: center;
    }

    .platform-pill {
        font-weight: 800;
        font-size: 11.5px;
        padding: 3px 9px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .platform-android { background: #ECFDF5; color: #047857; }
    .platform-ios { background: #F1F5F9; color: #334155; }

    .status-pill {
        font-weight: 850;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        letter-spacing: 0.3px;
    }
    .status-active { background: #ECFDF3; color: #027A48; }
    .status-inactive { background: #F2F4F7; color: #475467; }
    .status-force-yes { background: #FEF2F2; color: #DC2626; }
    .status-force-no { background: #F8FAFC; color: #64748B; border: 1px solid var(--set-border); }

    /* Action Icon Buttons */
    .apk-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12.5px;
        border: 1px solid var(--set-border);
        background: #ffffff;
        color: #475569;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .apk-action-btn:hover {
        background: var(--set-soft);
        color: var(--set-primary);
        border-color: rgba(75, 0, 232, 0.25);
        transform: translateY(-1px);
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

    /* Orbo Pagination & Footer Controls */
    .dataTables_wrapper {
        padding: 0 !important;
    }

    .dataTables_filter {
        display: none !important;
    }

    .dataTables_length {
        display: none !important;
    }

    .dataTables_info {
        font-size: 12.5px !important;
        font-weight: 700 !important;
        color: var(--set-muted) !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .dataTables_paginate {
        padding: 0 !important;
        margin: 0 !important;
        display: flex !important;
        justify-content: flex-end !important;
    }

    .dataTables_paginate .pagination {
        margin: 0 !important;
        gap: 4px !important;
    }

    .dataTables_paginate .page-item .page-link {
        border-radius: 10px !important;
        border: 1px solid var(--set-border) !important;
        color: var(--set-text) !important;
        font-weight: 800 !important;
        font-size: 12px !important;
        padding: 6px 12px !important;
        min-width: 34px !important;
        text-align: center !important;
        background: #fff !important;
        transition: all 0.2s ease !important;
    }

    .dataTables_paginate .page-item.active .page-link {
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary)) !important;
        border-color: var(--set-primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25) !important;
    }

    .dataTables_paginate .page-item .page-link:hover {
        background: var(--set-soft) !important;
        color: var(--set-primary) !important;
    }

    /* Responsive Breakpoints */
    @media (max-width: 1024px) {
        .apk-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .apk-filter-item:last-child {
            grid-column: span 2;
        }
    }

    @media (max-width: 768px) {
        .apk-metrics-wrap {
            grid-template-columns: repeat(2, 1fr);
            width: 100%;
        }
        .apk-filter-grid {
            grid-template-columns: 1fr;
        }
        .apk-filter-item:last-child {
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
            
            <!-- Glassmorphic Metrics Hero Badges -->
            <div class="apk-metrics-wrap">
                <div class="apk-glass-chip">
                    <div class="chip-num">{{ $stats['latest_version'] }}</div>
                    <div class="chip-title">Latest Build</div>
                </div>
                <div class="apk-glass-chip">
                    <div class="chip-num">{{ $stats['latest_version_code'] }}</div>
                    <div class="chip-title">Version Code</div>
                </div>
                <div class="apk-glass-chip">
                    <div class="chip-num" style="font-size: 16px;">
                        @if($stats['force_update_status'] === 'Enabled')
                            <span class="text-warning"><i class="fas fa-bolt mr-1"></i>Enforced</span>
                        @else
                            <span style="opacity: 0.9;">Optional</span>
                        @endif
                    </div>
                    <div class="chip-title">Force Update</div>
                </div>
                <div class="apk-glass-chip">
                    <div class="chip-num">{{ $stats['releases_count'] }}</div>
                    <div class="chip-title">Total Builds</div>
                </div>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <div><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="background: none; border: none; font-size: 18px; line-height: 1;">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <div><i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}</div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="background: none; border: none; font-size: 18px; line-height: 1;">&times;</button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <div><i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}</div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="background: none; border: none; font-size: 18px; line-height: 1;">&times;</button>
            </div>
        @endif

        <!-- Distribution Guidelines Notice Card -->
        <div class="apk-guideline-box mb-4">
            <div class="d-flex align-items-start" style="gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #FEF0C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h6 class="font-weight-bold mb-1" style="color: #92400E; font-size: 13.5px;">Private Android Distribution Guidelines:</h6>
                    <ul class="mb-0 pl-3 small font-weight-bold" style="color: #B45309; line-height: 1.6;">
                        <li>Package ID must match <code>com.orbosis.orboone</code> strictly across all builds.</li>
                        <li>Ensure Flutter <code>versionCode</code> is strictly incremented on every upload.</li>
                        <li>Signing keystores must remain identical across successive releases to prevent installation errors.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Card Section -->
        <div class="set-card">
            <div class="set-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <h5 class="set-card-title">APK Release History</h5>
                        <p class="set-card-subtitle">Review deployed build histories, minimum supported API levels, and active states.</p>
                    </div>
                </div>

                <!-- Header Action Buttons -->
                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                    <a href="{{ route('mobile-app.download-latest') }}" class="set-btn set-btn-soft" title="Download latest published APK">
                        <i class="fas fa-download"></i> Latest APK
                    </a>
                    @if($permissions['canUpload'])
                        <button type="button" class="set-btn" data-toggle="modal" data-target="#uploadApkModal">
                            <i class="fas fa-upload"></i> Upload APK
                        </button>
                    @endif
                </div>
            </div>

            <!-- Auto Live Search & Filter Bar -->
            <div class="apk-filter-card">
                <div class="apk-filter-grid">
                    <div class="apk-filter-item">
                        <label><i class="fas fa-search mr-1"></i> Search Version / Code</label>
                        <input type="text" id="filterVersionVal" class="apk-filter-control" placeholder="e.g. 1.0.5 or 6" onkeyup="applyApkFilters()">
                    </div>
                    <div class="apk-filter-item">
                        <label><i class="fas fa-mobile-screen mr-1"></i> Platform</label>
                        <select id="filterPlatform" class="apk-filter-control select2-searchable" onchange="applyApkFilters()">
                            <option value="">All Platforms</option>
                            <option value="ANDROID">Android</option>
                            <option value="IOS">iOS</option>
                        </select>
                    </div>
                    <div class="apk-filter-item">
                        <label><i class="fas fa-bolt mr-1"></i> Force Update</label>
                        <select id="filterForce" class="apk-filter-control select2-searchable" onchange="applyApkFilters()">
                            <option value="">All Rules</option>
                            <option value="Yes">Force Update</option>
                            <option value="No">Optional Update</option>
                        </select>
                    </div>
                    <div class="apk-filter-item">
                        <label><i class="fas fa-toggle-on mr-1"></i> Active State</label>
                        <select id="filterActive" class="apk-filter-control select2-searchable" onchange="applyApkFilters()">
                            <option value="">All States</option>
                            <option value="Active">Active Only</option>
                            <option value="Inactive">Inactive Only</option>
                        </select>
                    </div>
                    <div class="apk-filter-item">
                        <button type="button" class="apk-btn-reset" onclick="resetApkFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Toolbar (Export Tools Below Filter) -->
            <div class="apk-table-toolbar">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span class="badge" style="background: var(--set-soft); color: var(--set-primary); font-weight: 850; font-size: 11.5px; padding: 6px 12px; border-radius: 8px;">
                        <i class="fas fa-list mr-1"></i> Releases: {{ $versions->count() }}
                    </span>
                </div>
                
                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                    <!-- DataTables Export Container (CSV, Excel, PDF, Print) -->
                    <div id="mobileAppExportButtons"></div>
                </div>
            </div>

            <!-- Responsive Table Section -->
            <div class="set-card-body p-0">
                <table class="apk-table" id="mobileAppVersionsTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th style="min-width: 180px;">Version Details</th>
                            <th style="white-space: nowrap;">Version Code</th>
                            <th style="white-space: nowrap;">Min API Code</th>
                            <th style="white-space: nowrap;">Platform</th>
                            <th style="white-space: nowrap;">Force Update</th>
                            <th style="white-space: nowrap;">Status</th>
                            <th style="white-space: nowrap;">Release Date</th>
                            <th style="white-space: nowrap;">Uploaded By</th>
                            <th style="white-space: nowrap;">APK Size</th>
                            <th style="width: 130px; white-space: nowrap;" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versions as $version)
                            <tr class="apk-data-row">
                                <td class="text-muted font-weight-bold text-center">{{ $loop->iteration }}</td>
                                
                                <td class="apk-version-cell">
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--set-soft); color: var(--set-primary); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                                            <i class="fab fa-android"></i>
                                        </div>
                                        <div>
                                            <div class="apk-version-badge">v{{ $version->version_name }}</div>
                                            <div class="apk-meta-path" title="{{ $version->apk_file }}">
                                                {{ basename($version->apk_file) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="apk-code-cell">
                                    <span class="apk-code-chip">
                                        Code: {{ $version->version_code }}
                                    </span>
                                </td>
                                
                                <td>
                                    <span class="text-muted font-weight-bold font-monospace small">
                                        v{{ $version->min_supported_version_code }}+
                                    </span>
                                </td>
                                
                                <td class="apk-platform-cell">
                                    @if(strtolower($version->platform) === 'android')
                                        <span class="platform-pill platform-android">
                                            <i class="fab fa-android"></i> Android
                                        </span>
                                    @else
                                        <span class="platform-pill platform-ios">
                                            <i class="fab fa-apple"></i> iOS
                                        </span>
                                    @endif
                                </td>
                                
                                <td class="apk-force-cell">
                                    @if($version->is_force_update)
                                        <span class="status-pill status-force-yes">
                                            <i class="fas fa-bolt"></i> Yes
                                        </span>
                                    @else
                                        <span class="status-pill status-force-no">
                                            <i class="fas fa-minus"></i> No
                                        </span>
                                    @endif
                                </td>
                                
                                <td class="apk-active-cell">
                                    @if($version->is_active)
                                        <span class="status-pill status-active">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="status-pill status-inactive">
                                            <i class="fas fa-circle"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                
                                <td class="text-nowrap small text-muted font-weight-bold">
                                    <i class="far fa-clock mr-1 text-primary opacity-75"></i>
                                    {{ optional($version->release_date)->format('d M Y, h:i A') ?? '-' }}
                                </td>
                                
                                <td>
                                    <span class="font-weight-bold text-dark small text-nowrap">
                                        {{ optional($version->uploader)->name ?? 'System' }}
                                    </span>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border font-weight-bold text-nowrap">
                                        {{ $version->apk_size ? number_format($version->apk_size / 1048576, 2) . ' MB' : '-' }}
                                    </span>
                                </td>
                                
                                <td class="text-right">
                                    <div class="d-flex align-items-center justify-content-end" style="gap: 5px;">
                                        <a href="{{ route('mobile-app.download-public', $version->id) }}" class="apk-action-btn" title="Download APK Binary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($permissions['canManage'] && ! $version->is_active)
                                            <form method="POST" action="{{ route('hrms.mobile-app-versions.toggle-active', $version->id) }}" class="m-0" onsubmit="return confirm('Activate version {{ $version->version_name }} as the live build?')">
                                                @csrf
                                                <button type="submit" class="apk-action-btn" title="Set Active Release" style="color: #027A48;">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="apk-action-btn" title="Release Notes" data-toggle="modal" data-target="#releaseNotesModal{{ $version->id }}">
                                            <i class="fas fa-file-alt"></i>
                                        </button>
                                        @if($permissions['canDelete'])
                                            <form method="POST" action="{{ route('hrms.mobile-app-versions.destroy', $version->id) }}" class="m-0" onsubmit="return confirm('Permanently delete APK version {{ $version->version_name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="apk-action-btn apk-action-danger" title="Delete Build">
                                                    <i class="fas fa-trash-alt"></i>
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
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="padding: 24px; background: #fff;">
                                                    <pre style="white-space:pre-wrap; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--set-text); margin: 0; background: #F8FAFC; border: 1px solid var(--set-border); padding: 15px; border-radius: 14px; text-align: left;">{{ $version->release_notes ?: 'No release notes specified for this build.' }}</pre>
                                                </div>
                                                <div class="modal-footer" style="background: #F8FAFC; border-top: 1px solid var(--set-border); padding: 12px 24px; display: flex; justify-content: flex-end;">
                                                    <button type="button" class="set-btn set-btn-soft" data-dismiss="modal" style="min-height: 36px;">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--set-soft); color: var(--set-primary); display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 12px;">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <h6 class="font-weight-bold mb-1">No Mobile App Releases Found</h6>
                                    <p class="text-muted small mb-0">Upload your first Android APK to start private enterprise distribution.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Upload APK Modal -->
@if($permissions['canUpload'])
<div class="modal fade" id="uploadApkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 24px; overflow: hidden; border: 0; box-shadow: var(--set-shadow);">
            <form method="POST" action="{{ route('hrms.mobile-app-versions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, var(--set-primary), var(--set-secondary)); color: #fff; padding: 20px 24px;">
                    <div>
                        <h5 class="modal-title font-weight-bold" style="margin: 0; font-size: 16px;"><i class="fas fa-upload mr-1"></i> Upload APK Release</h5>
                        <p style="margin: 4px 0 0; opacity: 0.85; font-size: 11px;">Deploy a new Android build for private distribution</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
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
                                <input type="text" name="app_name" class="set-control" value="{{ old('app_name', branding_name()) }}">
                            </div>
                            <div>
                                <label class="set-label">Platform</label>
                                <select name="platform" class="set-control select2-searchable select2-modal-searchable" required>
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
                                <label class="set-label">APK Binary File (.apk) <span class="text-danger">*</span></label>
                                <input type="file" name="apk_file" class="set-control" accept=".apk,application/vnd.android.package-archive" required style="padding: 6px 14px; height: 38px;">
                            </div>
                            <div>
                                <label class="set-label">Release Notes</label>
                                <textarea name="release_notes" class="set-control" rows="3" placeholder="Highlight features, improvements and bug fixes...">{{ old('release_notes') }}</textarea>
                            </div>
                            <div>
                                <label class="switch d-inline-flex align-items-center" style="gap: 12px; font-weight: 800; color: var(--set-text); cursor: pointer; width: auto; height: auto;">
                                    <input type="checkbox" name="is_force_update" value="1" {{ old('is_force_update') ? 'checked' : '' }}>
                                    <span class="slider" style="position: relative; display: inline-block; width: 42px; height: 24px; flex-shrink: 0;"></span>
                                    <span>Force Update (Enforce prompt on older versions)</span>
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
                autoWidth: false,
                dom: "<'table-responsive'tr><'card-footer bg-white border-top py-3 px-4 d-flex flex-wrap justify-content-between align-items-center'<'text-muted font-weight-bold small mb-2 mb-md-0'i><'p-0'p>>",
                buttons: [
                    { extend: 'csv', className: 'btn', text: '<i class="fas fa-file-csv mr-1 text-primary"></i> CSV' },
                    { extend: 'excel', className: 'btn', text: '<i class="fas fa-file-excel mr-1 text-success"></i> Excel' },
                    { extend: 'pdf', className: 'btn', text: '<i class="fas fa-file-pdf mr-1 text-danger"></i> PDF' },
                    { extend: 'print', className: 'btn', text: '<i class="fas fa-print mr-1 text-secondary"></i> Print' }
                ],
                language: {
                    emptyTable: 'No APK releases uploaded yet.',
                    zeroRecords: 'No matching records found.',
                    info: 'Showing _START_ to _END_ of _TOTAL_ builds',
                    infoEmpty: 'Showing 0 to 0 of 0 builds',
                    infoFiltered: '(filtered from _MAX_ total records)',
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                }
            });

            table.buttons().container().appendTo('#mobileAppExportButtons');
        }

        // Explicitly initialize Select2 for all filter and modal selects
        if (typeof $.fn.select2 !== 'undefined') {
            $('#filterPlatform, #filterForce, #filterActive').each(function() {
                $(this).select2({
                    minimumResultsForSearch: Infinity,
                    width: '100%'
                });
            });

            $('#uploadApkModal').on('shown.bs.modal', function() {
                $(this).find('select.select2-searchable, select.select2-modal-searchable').select2({
                    dropdownParent: $('#uploadApkModal'),
                    minimumResultsForSearch: Infinity,
                    width: '100%'
                });
            });
        }

        // Attach Select2 change listener for real-time filtering
        $('#filterPlatform, #filterForce, #filterActive').on('change select2:select select2:clear', function() {
            applyApkFilters();
        });

        @if($errors->any())
            $('#uploadApkModal').modal('show');
        @endif
    });

    function applyApkFilters() {
        var versionVal = document.getElementById('filterVersionVal').value.toLowerCase().trim();
        var platformVal = ($('#filterPlatform').val() || '').toLowerCase().trim();
        var forceVal = ($('#filterForce').val() || '').toLowerCase().trim();
        var activeVal = ($('#filterActive').val() || '').toLowerCase().trim();

        document.querySelectorAll('#mobileAppVersionsTable tbody tr.apk-data-row').forEach(function(row) {
            var versionCell = row.querySelector('.apk-version-cell');
            var codeCell = row.querySelector('.apk-code-cell');
            var platformCell = row.querySelector('.apk-platform-cell');
            var forceCell = row.querySelector('.apk-force-cell');
            var activeCell = row.querySelector('.apk-active-cell');

            if (!versionCell) return;

            var versionText = (versionCell.textContent + " " + (codeCell ? codeCell.textContent : '')).toLowerCase();
            var platformText = platformCell ? platformCell.textContent.toLowerCase() : '';
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
        $('#filterPlatform').val('').trigger('change');
        $('#filterForce').val('').trigger('change');
        $('#filterActive').val('').trigger('change');
        
        document.querySelectorAll('#mobileAppVersionsTable tbody tr.apk-data-row').forEach(function(row) {
            row.style.display = '';
        });
    }
</script>
@endsection
