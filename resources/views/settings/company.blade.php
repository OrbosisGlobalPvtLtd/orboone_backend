@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Company Settings')

@section('_content')
<style>
    .settings-page{min-height:calc(100vh - 90px);padding:16px 10px 30px;background:#F6F7FB;}
    .settings-container{max-width:1120px;margin:0 auto;}
    .settings-header,.settings-card{background:#fff;border:1px solid #E7EAF3;border-radius:20px;box-shadow:0 10px 28px rgba(16,24,40,.06);}
    .settings-header{padding:16px;margin-bottom:14px;}
    .settings-title{margin:0;color:#101828;font-size:24px;font-weight:900;}
    .settings-subtitle{margin:4px 0 0;color:#667085;font-size:13px;font-weight:700;}
    .settings-card{padding:18px;}
    .settings-label{display:block;margin:0 0 6px;color:#667085;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.4px;}
    .settings-control{width:100%;height:42px;border-radius:12px !important;border:1px solid #E7EAF3 !important;background:#F9FAFB !important;color:#101828 !important;font-size:13px;font-weight:700;padding:8px 12px;}
    textarea.settings-control{height:auto;min-height:96px;}
    .settings-btn{min-height:38px;border-radius:12px;padding:8px 13px;font-size:13px;font-weight:800;display:inline-flex;align-items:center;gap:8px;border:0;color:#fff;background:linear-gradient(135deg,#4B00E8,#8600EE);}
    .settings-preview{width:84px;height:84px;border:1px solid #E7EAF3;border-radius:16px;background:#F9FAFB;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#667085;font-size:12px;font-weight:800;}
    .settings-preview img{width:100%;height:100%;object-fit:contain;display:block;}
</style>

<div class="settings-page">
    <div class="settings-container">
        <div class="settings-header">
            <h1 class="settings-title">Company Settings</h1>
            <p class="settings-subtitle">Company identity used across HRMS documents and pages.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">{{ $errors->first() }}</div>
        @endif

        <div class="settings-card">
            <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">Company Name</label>
                        <input type="text" name="company_name" class="settings-control" value="{{ old('company_name', $company->company_name ?? '') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">Company Email</label>
                        <input type="email" name="email" class="settings-control" value="{{ old('email', $company->email ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">Phone</label>
                        <input type="text" name="phone" class="settings-control" value="{{ old('phone', $company->phone ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">Website</label>
                        <input type="url" name="website" class="settings-control" value="{{ old('website', $company->website ?? '') }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="settings-label">Address</label>
                        <textarea name="address" class="settings-control">{{ old('address', $company->address ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">Company Logo</label>
                        <div class="d-flex align-items-center" style="gap:12px;">
                            <div class="settings-preview">
                                @if(! empty($company?->logo))
                                    <img src="{{ asset('storage/'.$company->logo) }}" alt="Company Logo">
                                @else
                                    Logo
                                @endif
                            </div>
                            <input type="file" name="logo" class="settings-control" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">Company Seal</label>
                        <div class="d-flex align-items-center" style="gap:12px;">
                            <div class="settings-preview">
                                @if(! empty($company?->seal))
                                    <img src="{{ asset('storage/'.$company->seal) }}" alt="Company Seal">
                                @else
                                    Seal
                                @endif
                            </div>
                            <input type="file" name="seal" class="settings-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <button type="submit" class="settings-btn">
                    <i class="fas fa-save"></i>
                    Save Company
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
