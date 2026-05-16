@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Mobile App Updates')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16,24,40,.07);
    }
    .mobile-app-page { min-height: calc(100vh - 90px); padding: 16px 10px 30px; background: var(--orb-bg); }
    .mobile-app-container { max-width: 1380px; margin: 0 auto; }
    .orb-page-head, .orb-card, .orb-stat { background: #fff; border: 1px solid var(--orb-border); border-radius: 20px; box-shadow: var(--orb-shadow); }
    .orb-page-head { padding: 18px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
    .orb-title { margin: 0; color: var(--orb-text); font-size: 24px; font-weight: 900; }
    .orb-subtitle { margin: 5px 0 0; color: var(--orb-muted); font-size: 13px; font-weight: 700; }
    .orb-btn { min-height: 38px; border-radius: 12px; padding: 8px 13px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; border: 0; color: #fff; background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); }
    .orb-btn:hover { color: #fff; opacity: .94; }
    .orb-btn-soft { background: var(--orb-soft); color: var(--orb-primary); border: 1px solid #DDD6FE; }
    .orb-btn-soft:hover { color: var(--orb-primary); }
    .orb-btn-danger { background: #FEF3F2; color: #B42318; border: 1px solid #FECDCA; }
    .orb-btn-danger:hover { color: #B42318; }
    .orb-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
    .orb-stat { padding: 16px; min-height: 104px; display: flex; align-items: center; gap: 13px; }
    .orb-stat-icon { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: var(--orb-soft); color: var(--orb-primary); font-size: 18px; flex: 0 0 44px; }
    .orb-stat-label { color: var(--orb-muted); font-size: 11px; text-transform: uppercase; font-weight: 900; letter-spacing: .4px; }
    .orb-stat-value { color: var(--orb-text); font-size: 21px; font-weight: 900; line-height: 1.2; margin-top: 3px; overflow-wrap: anywhere; }
    .orb-card { overflow: hidden; }
    .orb-card-head { padding: 16px 18px; border-bottom: 1px solid var(--orb-border); display: flex; justify-content: space-between; gap: 12px; align-items: center; flex-wrap: wrap; }
    .orb-card-title { margin: 0; color: var(--orb-text); font-weight: 900; font-size: 16px; }
    .orb-note { background: #FFFAEB; border: 1px solid #FEDF89; color: #93370D; border-radius: 16px; padding: 13px 15px; font-size: 13px; font-weight: 700; margin-bottom: 14px; }
    .orb-note ul { margin: 8px 0 0; padding-left: 18px; }
    .orb-table { margin-bottom: 0 !important; }
    .orb-table th { background: var(--orb-soft); color: var(--orb-primary); border: 0 !important; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; font-weight: 900; white-space: nowrap; }
    .orb-table td { vertical-align: middle !important; color: var(--orb-text); font-size: 13px; font-weight: 700; border-top: 1px solid var(--orb-border) !important; }
    .orb-badge { display: inline-flex; align-items: center; gap: 6px; min-height: 26px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 900; }
    .orb-badge-success { background: #ECFDF3; color: #027A48; }
    .orb-badge-muted { background: #F2F4F7; color: #475467; }
    .orb-badge-danger { background: #FEF3F2; color: #B42318; }
    .orb-actions { display: flex; gap: 7px; flex-wrap: wrap; }
    .orb-action { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; }
    .orb-form-label { display: block; margin: 0 0 6px; color: var(--orb-muted); font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .4px; }
    .orb-control { width: 100%; min-height: 42px; border-radius: 12px !important; border: 1px solid var(--orb-border) !important; background: #F9FAFB !important; color: var(--orb-text) !important; font-size: 13px; font-weight: 700; padding: 8px 12px; }
    textarea.orb-control { min-height: 110px; }
    .dataTables_wrapper .dt-buttons .btn { border-radius: 10px; margin-right: 6px; background: var(--orb-soft); color: var(--orb-primary); border: 1px solid #DDD6FE; font-weight: 800; }
    @media (max-width: 991px) { .orb-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575px) { .orb-stats { grid-template-columns: 1fr; } .orb-page-head { align-items: stretch; } .orb-btn { justify-content: center; width: 100%; } }
</style>
@endsection

@section('_content')
<div class="mobile-app-page">
    <div class="mobile-app-container">
        <div class="orb-page-head">
            <div>
                <h1 class="orb-title">Mobile App Updates</h1>
                <p class="orb-subtitle">Private Android APK release management for OrboOne HRMS.</p>
            </div>
            @if($permissions['canUpload'])
                <button type="button" class="orb-btn" data-toggle="modal" data-target="#uploadApkModal">
                    <i class="fas fa-upload"></i>
                    Upload APK
                </button>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm" style="border-radius:14px;font-weight:800;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm" style="border-radius:14px;font-weight:800;">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm" style="border-radius:14px;font-weight:800;">{{ $errors->first() }}</div>
        @endif

        <div class="orb-note">
            <strong>Private Android distribution notes</strong>
            <ul>
                <li>Android package name must never change: <code>com.orbosis.orboone</code>.</li>
                <li>Release signing key must never change.</li>
                <li>Flutter <code>versionCode</code> must increase on every release.</li>
                <li>Upload only signed release APK builds. This is private distribution, not Play Store.</li>
                <li>Run <code>php artisan storage:link</code> on hosting if public storage URLs are not available.</li>
            </ul>
        </div>

        <div class="orb-stats">
            <div class="orb-stat">
                <div class="orb-stat-icon"><i class="fas fa-code-branch"></i></div>
                <div>
                    <div class="orb-stat-label">Latest Version</div>
                    <div class="orb-stat-value">{{ $stats['latest_version'] }}</div>
                </div>
            </div>
            <div class="orb-stat">
                <div class="orb-stat-icon"><i class="fas fa-hashtag"></i></div>
                <div>
                    <div class="orb-stat-label">Latest Version Code</div>
                    <div class="orb-stat-value">{{ $stats['latest_version_code'] }}</div>
                </div>
            </div>
            <div class="orb-stat">
                <div class="orb-stat-icon"><i class="fas fa-shield-alt"></i></div>
                <div>
                    <div class="orb-stat-label">Force Update Status</div>
                    <div class="orb-stat-value">{{ $stats['force_update_status'] }}</div>
                </div>
            </div>
            <div class="orb-stat">
                <div class="orb-stat-icon"><i class="fas fa-download"></i></div>
                <div>
                    <div class="orb-stat-label">APK Releases / Downloads</div>
                    <div class="orb-stat-value">{{ $stats['releases_count'] }}</div>
                </div>
            </div>
        </div>

        <div class="orb-card">
            <div class="orb-card-head">
                <h2 class="orb-card-title">APK Release History</h2>
                <div id="mobileAppExportButtons"></div>
            </div>
            <div class="table-responsive">
                <table class="table orb-table" id="mobileAppVersionsTable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Version Name</th>
                            <th>Version Code</th>
                            <th>Minimum Supported Version</th>
                            <th>Platform</th>
                            <th>Force Update</th>
                            <th>Active</th>
                            <th>Release Date</th>
                            <th>Uploaded By</th>
                            <th>APK Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versions as $version)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $version->version_name }}</td>
                                <td>{{ $version->version_code }}</td>
                                <td>{{ $version->min_supported_version_code }}</td>
                                <td>{{ strtoupper($version->platform) }}</td>
                                <td>
                                    <span class="orb-badge {{ $version->is_force_update ? 'orb-badge-danger' : 'orb-badge-muted' }}">
                                        {{ $version->is_force_update ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="orb-badge {{ $version->is_active ? 'orb-badge-success' : 'orb-badge-muted' }}">
                                        {{ $version->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ optional($version->release_date)->format('d M Y, h:i A') ?? '-' }}</td>
                                <td>{{ optional($version->uploader)->name ?? 'System' }}</td>
                                <td>{{ $version->apk_size ? number_format($version->apk_size / 1048576, 2) . ' MB' : '-' }}</td>
                                <td>
                                    <div class="orb-actions">
                                        <a href="{{ route('hrms.mobile-app-versions.download', $version->id) }}" class="btn orb-btn-soft orb-action" title="Download APK">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($permissions['canManage'] && ! $version->is_active)
                                            <form method="POST" action="{{ route('hrms.mobile-app-versions.toggle-active', $version->id) }}" onsubmit="return confirm('Set this APK version as active?')">
                                                @csrf
                                                <button type="submit" class="btn orb-btn-soft orb-action" title="Set Active">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="btn orb-btn-soft orb-action" title="View Release Notes" data-toggle="modal" data-target="#releaseNotesModal{{ $version->id }}">
                                            <i class="fas fa-note-sticky"></i>
                                        </button>
                                        @if($permissions['canDelete'])
                                            <form method="POST" action="{{ route('hrms.mobile-app-versions.destroy', $version->id) }}" onsubmit="return confirm('Delete this APK release and its file?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn orb-btn-danger orb-action" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="modal fade" id="releaseNotesModal{{ $version->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content" style="border:0;border-radius:18px;">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" style="font-weight:900;">Release Notes - {{ $version->version_name }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre style="white-space:pre-wrap;font-family:inherit;font-weight:700;color:#101828;">{{ $version->release_notes ?: 'No release notes added.' }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">No APK releases uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($permissions['canUpload'])
<div class="modal fade" id="uploadApkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border:0;border-radius:20px;">
            <form method="POST" action="{{ route('hrms.mobile-app-versions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid var(--orb-border);">
                    <h5 class="modal-title" style="font-weight:900;color:var(--orb-text);">Upload APK Release</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="orb-form-label">App Name</label>
                            <input type="text" name="app_name" class="orb-control" value="{{ old('app_name', 'OrboOne HRMS') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="orb-form-label">Platform</label>
                            <select name="platform" class="orb-control" required>
                                <option value="android" {{ old('platform', 'android') === 'android' ? 'selected' : '' }}>Android</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="orb-form-label">Version Name</label>
                            <input type="text" name="version_name" class="orb-control" value="{{ old('version_name') }}" placeholder="1.0.0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="orb-form-label">Version Code</label>
                            <input type="number" name="version_code" class="orb-control" value="{{ old('version_code') }}" min="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="orb-form-label">Minimum Supported Version Code</label>
                            <input type="number" name="min_supported_version_code" class="orb-control" value="{{ old('min_supported_version_code', 1) }}" min="1" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="orb-form-label">APK File</label>
                            <input type="file" name="apk_file" class="orb-control" accept=".apk,application/vnd.android.package-archive" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="orb-form-label">Release Notes</label>
                            <textarea name="release_notes" class="orb-control" placeholder="One change per line">{{ old('release_notes') }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="d-inline-flex align-items-center" style="gap:10px;font-weight:900;color:var(--orb-text);">
                                <input type="checkbox" name="is_force_update" value="1" {{ old('is_force_update') ? 'checked' : '' }}>
                                Force Update
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--orb-border);">
                    <button type="button" class="btn orb-btn-soft" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn">
                        <i class="fas fa-save"></i>
                        Publish / Save
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
        if ($.fn.DataTable && $('#mobileAppVersionsTable tbody tr').length) {
            var table = $('#mobileAppVersionsTable').DataTable({
                pageLength: 10,
                order: [[2, 'desc']],
                responsive: false,
                dom: 'Bfrtip',
                buttons: ['csv', 'excel', 'pdf', 'print']
            });

            table.buttons().container().appendTo('#mobileAppExportButtons');
        }

        @if($errors->any())
            $('#uploadApkModal').modal('show');
        @endif
    });
</script>
@endsection
