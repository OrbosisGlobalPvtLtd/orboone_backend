@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Company Settings')

@section('_head')
@include('settings.partials.styles')
@endsection

@section('_content')
<div class="set-page">
    <div class="set-container">
        <!-- Premium Purple Gradient Hero -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-building"></i> HRMS &bull; SETTINGS
                </div>
                <h1 class="set-title">Company Settings</h1>
                <p class="set-subtitle">Establish company identities, active addresses, and official logos/seals used across HRMS document templates.</p>
            </div>
            <!-- Glassmorphic Info Badge -->
            <div class="set-glass-badge">
                <div style="font-size: 24px; font-weight: 900; line-height: 1;"><i class="fas fa-building"></i></div>
                <div style="font-size: 9px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; opacity: 0.9;">Company Profile</div>
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

        <div class="set-card">
            <div class="set-card-header">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-building"></i></div>
                    <div>
                        <h5 class="set-card-title">Corporate Profile details</h5>
                        <p class="set-card-subtitle">Adjust public identifiers, web portals, physical addresses, and corporate seals.</p>
                    </div>
                </div>
            </div>

            <div class="set-card-body" style="padding: 30px;">
                <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="set-grid mb-4">
                        <div>
                            <label class="set-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="set-control" value="{{ old('company_name', $company->company_name ?? '') }}" placeholder="e.g. {{ branding_name() }}" required>
                        </div>
                        <div>
                            <label class="set-label">Corporate Email Address</label>
                            <input type="email" name="email" class="set-control" value="{{ old('email', $company->email ?? '') }}" placeholder="e.g. contact@company.com">
                        </div>
                        <div>
                            <label class="set-label">Contact Phone Number</label>
                            <input type="text" name="phone" class="set-control" value="{{ old('phone', $company->phone ?? '') }}" placeholder="e.g. +91 9876543210">
                        </div>
                        <div>
                            <label class="set-label">Corporate Web URL</label>
                            <input type="url" name="website" class="set-control" value="{{ old('website', $company->website ?? '') }}" placeholder="e.g. https://www.company.com">
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label class="set-label">Physical Address</label>
                            <textarea name="address" class="set-control" placeholder="Enter physical HQ address locations...">{{ old('address', $company->address ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="set-label">Company Corporate Logo</label>
                            <div class="d-flex align-items-center" style="gap:16px;">
                                <div class="set-preview">
                                    @if(! empty($company?->logo))
                                        <img src="{{ route('hrms.documents.file', ['path' => $company->logo]) }}" alt="Company Logo">
                                    @else
                                        <span>Logo</span>
                                    @endif
                                </div>
                                <div style="flex: 1;">
                                    <input type="file" name="logo" class="set-control" accept="image/*" style="padding: 6px 12px; height: 38px;">
                                    <span class="small text-muted mt-1 d-block" style="font-size: 11px;">PNG, JPG formats (Max 2MB)</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="set-label">Company Corporate Seal</label>
                            <div class="d-flex align-items-center" style="gap:16px;">
                                <div class="set-preview">
                                    @if(! empty($company?->seal))
                                        <img src="{{ route('hrms.documents.file', ['path' => $company->seal]) }}" alt="Company Seal">
                                    @else
                                        <span>Seal</span>
                                    @endif
                                </div>
                                <div style="flex: 1;">
                                    <input type="file" name="seal" class="set-control" accept="image/*" style="padding: 6px 12px; height: 38px;">
                                    <span class="small text-muted mt-1 d-block" style="font-size: 11px;">PNG formats with transparency preferred</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center flex-wrap pt-3 border-top" style="gap:10px;">
                        <button type="submit" class="set-btn">
                            <i class="fas fa-save"></i> Save Corporate Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
