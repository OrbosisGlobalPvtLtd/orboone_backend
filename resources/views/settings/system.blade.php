@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'System Settings')

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
                    <i class="fas fa-sliders-h"></i> HRMS &bull; SETTINGS
                </div>
                <h1 class="set-title">System Settings</h1>
                <p class="set-subtitle">Configure application names, support emails, timezones, and global mail SMTP properties.</p>
            </div>
            <!-- Glassmorphic Info Badge -->
            <div class="set-glass-badge">
                <div style="font-size: 24px; font-weight: 900; line-height: 1;"><i class="fas fa-cog"></i></div>
                <div style="font-size: 9px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; opacity: 0.9;">System Core</div>
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

        <form action="{{ route('settings.system.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- General Settings Section -->
            <div class="set-card">
                <div class="set-card-header">
                    <div class="set-head-left">
                        <div class="set-icon-box"><i class="fas fa-cog"></i></div>
                        <div>
                            <h5 class="set-card-title">General Settings</h5>
                            <p class="set-card-subtitle">Set primary application title, location timezone, support logs, and date parameters.</p>
                        </div>
                    </div>
                </div>
                <div class="set-card-body">
                    <div class="set-grid">
                        <div>
                            <label class="set-label">App Name</label>
                            <input type="text" name="app_name" class="set-control" value="{{ old('app_name', $settings['app_name']) }}" placeholder="e.g. Orbosis HRMS" required>
                        </div>
                        <div>
                            <label class="set-label">Timezone</label>
                            <input type="text" name="timezone" class="set-control" value="{{ old('timezone', $settings['timezone']) }}" placeholder="e.g. Asia/Kolkata" required>
                        </div>
                        <div>
                            <label class="set-label">Date Format</label>
                            <input type="text" name="date_format" class="set-control" value="{{ old('date_format', $settings['date_format']) }}" placeholder="e.g. Y-m-d" required>
                        </div>
                        <div>
                            <label class="set-label">HRMS Support Email</label>
                            <input type="email" name="hrms_support_email" class="set-control" value="{{ old('hrms_support_email', $settings['hrms_support_email']) }}" placeholder="e.g. support@company.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Defaults Section -->
            <div class="set-card">
                <div class="set-card-header">
                    <div class="set-head-left">
                        <div class="set-icon-box"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <h5 class="set-card-title">Attendance Defaults</h5>
                            <p class="set-card-subtitle">Set global office shift times, flexi parameters, and late-mark grace intervals.</p>
                        </div>
                    </div>
                </div>
                <div class="set-card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="set-label">Start Time</label>
                            <input type="time" name="attendance_start_time" class="set-control" value="{{ old('attendance_start_time', $settings['attendance_start_time']) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="set-label">End Time</label>
                            <input type="time" name="attendance_end_time" class="set-control" value="{{ old('attendance_end_time', $settings['attendance_end_time']) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="set-label">Late Mark Grace (Minutes)</label>
                            <input type="number" name="late_mark_after_minutes" class="set-control" value="{{ old('late_mark_after_minutes', $settings['late_mark_after_minutes']) }}" min="0" placeholder="e.g. 15">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mail SMTP Settings Section -->
            <div class="set-card">
                <div class="set-card-header">
                    <div class="set-head-left">
                        <div class="set-icon-box"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h5 class="set-card-title">Mail & SMTP Configurations</h5>
                            <p class="set-card-subtitle">Active environment mailer settings. (Read-only properties configured in the env variables)</p>
                        </div>
                    </div>
                </div>
                <div class="set-card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="set-label">Mailer Driver</label>
                            <input type="text" class="set-control" value="{{ $mailSettings['mailer'] }}" readonly style="cursor: not-allowed; opacity: 0.85;">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="set-label">SMTP Host</label>
                            <input type="text" class="set-control" value="{{ $mailSettings['host'] }}" readonly style="cursor: not-allowed; opacity: 0.85;">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="set-label">SMTP Port</label>
                            <input type="text" class="set-control" value="{{ $mailSettings['port'] }}" readonly style="cursor: not-allowed; opacity: 0.85;">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="set-label">Sender Email Address</label>
                            <input type="text" class="set-control" value="{{ $mailSettings['from'] }}" readonly style="cursor: not-allowed; opacity: 0.85;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Save Control Panel -->
            <div class="d-flex align-items-center flex-wrap pt-2" style="gap:10px;">
                <button type="submit" class="set-btn">
                    <i class="fas fa-save"></i> Save Configuration Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
