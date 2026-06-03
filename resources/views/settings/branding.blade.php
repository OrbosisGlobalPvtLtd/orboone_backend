@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Company Branding & Theme')

@section('_head')
@include('settings.partials.styles')
@endsection

@section('_content')
<div class="set-page">
    <div class="set-container">
        <!-- Premium Purple Gradient Hero -->
        <div class="set-header" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }}, {{ $branding['secondary_color'] ?? '#8600EE' }}); box-shadow: 0 10px 30px rgba(75, 0, 232, 0.15);">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-palette"></i> HRMS &bull; BRANDING
                </div>
                <h1 class="set-title">Company Branding Settings</h1>
                <p class="set-subtitle">Customize application logos, icons, titles, and layout themes for a personalized enterprise experience.</p>
            </div>
            <!-- Glassmorphic Info Badge -->
            <div class="set-glass-badge">
                <div style="font-size: 24px; font-weight: 900; line-height: 1;"><i class="fas fa-magic"></i></div>
                <div style="font-size: 9px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; opacity: 0.9;">UI Branding</div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('settings.branding.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Identity & Company Profile -->
            <div class="set-card">
                <div class="set-card-header">
                    <div class="set-head-left">
                        <div class="set-icon-box"><i class="fas fa-building"></i></div>
                        <div>
                            <h5 class="set-card-title">Corporate Identity</h5>
                            <p class="set-card-subtitle">Set primary application name and portal titles visible to all employee dashboards.</p>
                        </div>
                    </div>
                </div>
                <div class="set-card-body">
                    <div>
                        <label class="set-label">Company / Portal Name</label>
                        <input type="text" name="company_name" class="set-control" value="{{ old('company_name', $brandingData['company_name']) }}" placeholder="e.g. OrboOne HRMS" required>
                        <p style="font-size: 11px; color: var(--set-muted); margin-top: 6px; font-weight: 600;">This title will replace default "OrboOne HRMS" in layouts, sidebar alts, and authentication titles.</p>
                    </div>
                </div>
            </div>

            <!-- UI Aesthetics & Colors -->
            <div class="set-card">
                <div class="set-card-header">
                    <div class="set-head-left">
                        <div class="set-icon-box"><i class="fas fa-paint-brush"></i></div>
                        <div>
                            <h5 class="set-card-title">Theme Aesthetics & Colors</h5>
                            <p class="set-card-subtitle">Define UI primary and secondary theme colors. Use hex format or visual color selectors.</p>
                        </div>
                    </div>
                </div>
                <div class="set-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="set-label">Primary Theme Color</label>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <input type="color" id="primary_picker" class="form-control" style="width: 48px; height: 42px; padding: 2px; border-radius: 12px; border: 1px solid var(--set-border); cursor: pointer;" value="{{ old('primary_color', $brandingData['primary_color']) }}">
                                <input type="text" name="primary_color" id="primary_color" class="set-control" value="{{ old('primary_color', $brandingData['primary_color']) }}" placeholder="#4B00E8" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="set-label">Secondary Theme Color</label>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <input type="color" id="secondary_picker" class="form-control" style="width: 48px; height: 42px; padding: 2px; border-radius: 12px; border: 1px solid var(--set-border); cursor: pointer;" value="{{ old('secondary_color', $brandingData['secondary_color']) }}">
                                <input type="text" name="secondary_color" id="secondary_color" class="set-control" value="{{ old('secondary_color', $brandingData['secondary_color']) }}" placeholder="#8600EE" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label class="set-label">Sidebar Theme Color (Optional)</label>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <input type="color" id="sidebar_picker" class="form-control" style="width: 48px; height: 42px; padding: 2px; border-radius: 12px; border: 1px solid var(--set-border); cursor: pointer;" value="{{ old('sidebar_color', $brandingData['sidebar_color']) }}">
                                <input type="text" name="sidebar_color" id="sidebar_color" class="set-control" value="{{ old('sidebar_color', $brandingData['sidebar_color']) }}" placeholder="#4B00E8">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="set-label">Header BG Color (Optional)</label>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <input type="color" id="header_picker" class="form-control" style="width: 48px; height: 42px; padding: 2px; border-radius: 12px; border: 1px solid var(--set-border); cursor: pointer;" value="{{ old('header_color', $brandingData['header_color']) }}">
                                <input type="text" name="header_color" id="header_color" class="set-control" value="{{ old('header_color', $brandingData['header_color']) }}" placeholder="#ffffff">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assets & Uploads -->
            <div class="set-card">
                <div class="set-card-header">
                    <div class="set-head-left">
                        <div class="set-icon-box"><i class="fas fa-images"></i></div>
                        <div>
                            <h5 class="set-card-title">Brand Assets & Uploads</h5>
                            <p class="set-card-subtitle">Upload custom company logo and tab favicon icon. View instant uploads preview below.</p>
                        </div>
                    </div>
                </div>
                <div class="set-card-body">
                    <div class="row">
                        <!-- Logo Upload -->
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-start" style="gap: 20px;">
                                <div class="set-preview" style="width: 100px; height: 100px; border-radius: 16px; background: #f8fafc; border: 1px solid var(--set-border); display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                                    @if(!empty($brandingData['logo_path']))
                                        <img src="{{ route('hrms.branding.file', ['type' => 'logo', 'filename' => basename($brandingData['logo_path'])]) }}" id="logo_img_preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('images/Picsart_26-04-02_12-19-10-396.png') }}" id="logo_img_preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    @endif
                                </div>
                                <div style="flex-grow: 1;">
                                    <label class="set-label">Upload Portal Logo</label>
                                    <input type="file" name="logo" id="logo_input" class="set-control" style="padding-top: 10px;" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                                    <p style="font-size: 11px; color: var(--set-muted); margin-top: 6px; font-weight: 600;">Recommended: Transparent PNG (max 2MB).</p>
                                </div>
                            </div>
                        </div>

                        <!-- Favicon Upload -->
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-start" style="gap: 20px;">
                                <div class="set-preview" style="width: 100px; height: 100px; border-radius: 16px; background: #f8fafc; border: 1px solid var(--set-border); display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                                    @if(!empty($brandingData['favicon_path']))
                                        <img src="{{ route('hrms.branding.file', ['type' => 'favicon', 'filename' => basename($brandingData['favicon_path'])]) }}" id="favicon_img_preview" style="max-width: 32px; max-height: 32px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('favicon.ico') }}" id="favicon_img_preview" style="max-width: 32px; max-height: 32px; object-fit: contain;">
                                    @endif
                                </div>
                                <div style="flex-grow: 1;">
                                    <label class="set-label">Upload Favicon</label>
                                    <input type="file" name="favicon" id="favicon_input" class="set-control" style="padding-top: 10px;" accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/jpeg,image/jpg">
                                    <p style="font-size: 11px; color: var(--set-muted); margin-top: 6px; font-weight: 600;">Recommended: Square PNG or ICO format (max 1MB).</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Save Control Panel -->
            <div class="d-flex align-items-center flex-wrap pt-2" style="gap:15px; justify-content: space-between; width: 100%;">
                <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                    <button type="submit" class="set-btn">
                        <i class="fas fa-save"></i> Save Branding Settings
                    </button>
                    <a href="{{ route('settings.company.index') }}" class="set-btn set-btn-soft text-decoration-none">
                        Cancel
                    </a>
                </div>
            </div>
        </form>

        <!-- Danger Zone Reset Option -->
        @if(auth()->user()->hasPermission('settings.branding.update'))
        <div class="set-card mt-5" style="border: 1px solid #fee2e2;">
            <div class="set-card-header" style="background-color: #fff5f5; border-bottom: 1px solid #fee2e2;">
                <div class="set-head-left">
                    <div class="set-icon-box" style="background-color: #fee2e2; color: #ef4444;"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <h5 class="set-card-title" style="color: #991b1b;">Reset Default Theme</h5>
                        <p class="set-card-subtitle" style="color: #b91c1c;">Revert all portal colors, names, logos and favicons back to default OrboOne HRMS style.</p>
                    </div>
                </div>
            </div>
            <div class="set-card-body" style="background-color: #fffcfc;">
                <p style="font-size: 13px; color: #7f1d1d; font-weight: 600; margin-bottom: 16px;">This action will permanently delete custom uploaded brand logo/favicons and clear colors in database. Fallbacks to original purple styling will apply instantly.</p>
                <form action="{{ route('settings.branding.update') }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete all custom corporate branding and reset OrboOne default purple theme?');">
                    @csrf
                    <input type="hidden" name="action" value="reset">
                    <button type="submit" class="set-btn set-btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete & Reset Branding Settings
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Color Picker to Hex text linkage
        function linkColorPicker(pickerId, textId) {
            const picker = document.getElementById(pickerId);
            const text = document.getElementById(textId);

            if (picker && text) {
                picker.addEventListener('input', function() {
                    text.value = this.value.toUpperCase();
                });

                text.addEventListener('input', function() {
                    let val = this.value;
                    if (!val.startsWith('#')) {
                        val = '#' + val;
                        this.value = val;
                    }
                    if (/^#[0-9A-F]{6}$/i.test(val)) {
                        picker.value = val;
                    }
                });
            }
        }

        linkColorPicker('primary_picker', 'primary_color');
        linkColorPicker('secondary_picker', 'secondary_color');
        linkColorPicker('sidebar_picker', 'sidebar_color');
        linkColorPicker('header_picker', 'header_color');

        // File upload real-time previews
        function setupFilePreview(inputId, imgId) {
            const input = document.getElementById(inputId);
            const img = document.getElementById(imgId);

            if (input && img) {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.setAttribute('src', e.target.result);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        }

        setupFilePreview('logo_input', 'logo_img_preview');
        setupFilePreview('favicon_input', 'favicon_img_preview');
    });
</script>
@endsection
