@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'System Settings')

@section('_content')
<style>
    .settings-page{min-height:calc(100vh - 90px);padding:16px 10px 30px;background:#F6F7FB;}
    .settings-container{max-width:1120px;margin:0 auto;}
    .settings-header,.settings-card{background:#fff;border:1px solid #E7EAF3;border-radius:20px;box-shadow:0 10px 28px rgba(16,24,40,.06);}
    .settings-header{padding:16px;margin-bottom:14px;}
    .settings-title{margin:0;color:#101828;font-size:24px;font-weight:900;}
    .settings-subtitle{margin:4px 0 0;color:#667085;font-size:13px;font-weight:700;}
    .settings-card{padding:18px;}
    .settings-card h2{font-size:16px;font-weight:900;color:#101828;margin:0 0 14px;}
    .settings-label{display:block;margin:0 0 6px;color:#667085;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.4px;}
    .settings-control{width:100%;height:42px;border-radius:12px !important;border:1px solid #E7EAF3 !important;background:#F9FAFB !important;color:#101828 !important;font-size:13px;font-weight:700;padding:8px 12px;}
    .settings-btn{min-height:38px;border-radius:12px;padding:8px 13px;font-size:13px;font-weight:800;display:inline-flex;align-items:center;gap:8px;border:0;color:#fff;background:linear-gradient(135deg,#4B00E8,#8600EE);}
</style>

<div class="settings-page">
    <div class="settings-container">
        <div class="settings-header">
            <h1 class="settings-title">System Settings</h1>
            <p class="settings-subtitle">General HRMS and application settings.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('settings.system.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="settings-card mb-3">
                <h2>General</h2>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">App Name</label>
                        <input type="text" name="app_name" class="settings-control" value="{{ old('app_name', $settings['app_name']) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">Timezone</label>
                        <input type="text" name="timezone" class="settings-control" value="{{ old('timezone', $settings['timezone']) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">Date Format</label>
                        <input type="text" name="date_format" class="settings-control" value="{{ old('date_format', $settings['date_format']) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="settings-label">HRMS Support Email</label>
                        <input type="email" name="hrms_support_email" class="settings-control" value="{{ old('hrms_support_email', $settings['hrms_support_email']) }}">
                    </div>
                </div>
            </div>

            <div class="settings-card mb-3">
                <h2>Attendance Defaults</h2>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="settings-label">Start Time</label>
                        <input type="time" name="attendance_start_time" class="settings-control" value="{{ old('attendance_start_time', $settings['attendance_start_time']) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="settings-label">End Time</label>
                        <input type="time" name="attendance_end_time" class="settings-control" value="{{ old('attendance_end_time', $settings['attendance_end_time']) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="settings-label">Late Mark After Minutes</label>
                        <input type="number" name="late_mark_after_minutes" class="settings-control" value="{{ old('late_mark_after_minutes', $settings['late_mark_after_minutes']) }}" min="0">
                    </div>
                </div>
            </div>

            <div class="settings-card mb-3">
                <h2>Mail Settings</h2>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="settings-label">Mailer</label>
                        <input type="text" class="settings-control" value="{{ $mailSettings['mailer'] }}" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="settings-label">Host</label>
                        <input type="text" class="settings-control" value="{{ $mailSettings['host'] }}" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="settings-label">Port</label>
                        <input type="text" class="settings-control" value="{{ $mailSettings['port'] }}" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="settings-label">From</label>
                        <input type="text" class="settings-control" value="{{ $mailSettings['from'] }}" readonly>
                    </div>
                </div>
            </div>

            <button type="submit" class="settings-btn">
                <i class="fas fa-save"></i>
                Save Settings
            </button>
        </form>
    </div>
</div>
@endsection
