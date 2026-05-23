@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Notification Retention Settings')

@section('_head')
@include('settings.partials.styles')
<style>
    .w-100px {
        width: 100px !important;
    }
</style>
@endsection

@section('_content')
<div class="set-page">
    <div class="set-container">
        <!-- Premium Purple Gradient Hero -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-clock"></i> HRMS &bull; SETTINGS
                </div>
                <h1 class="set-title">Notification Retention</h1>
                <p class="set-subtitle">Define automatic message cleanup policies and retention duration states across hrms activity feeds.</p>
            </div>
            <!-- Glassmorphic Info Badge -->
            <div class="set-glass-badge">
                <div style="font-size: 24px; font-weight: 900; line-height: 1;"><i class="fas fa-clock"></i></div>
                <div style="font-size: 9px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; opacity: 0.9;">Logs Retention</div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Settings Table Card -->
        <div class="set-card">
            <div class="set-card-header">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-bell"></i></div>
                    <div>
                        <h5 class="set-card-title">Notification Retention Rules</h5>
                        <p class="set-card-subtitle">Manage purge triggers, select only-read delete tags, and set life spans for alert types.</p>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="set-table">
                    <thead>
                        <tr>
                            <th>Category / Trigger</th>
                            <th>Retention (Days)</th>
                            <th>Delete Only Read</th>
                            <th>Active Status</th>
                            <th>Last Updated</th>
                            <th width="120" class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $item)
                            <tr>
                                <td>
                                    <span class="set-badge">{{ $item->display_name }}</span>
                                    <div class="small text-muted mt-1" style="font-family: monospace; font-size: 10px;">{{ $item->notification_type }}</div>
                                </td>
                                <td>
                                    <input type="number" 
                                           id="days_{{ $item->id }}" 
                                           class="set-control w-100px" 
                                           style="height: 36px; padding: 4px 10px;"
                                           value="{{ $item->retention_days }}" 
                                           min="7" max="3650">
                                </td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" id="read_{{ $item->id }}" {{ $item->delete_only_read ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" id="active_{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="small font-weight-bold" style="color: var(--set-text);">{{ optional($item->updated_at)->format('d M Y') ?? '-' }}</div>
                                    <div class="small text-muted" style="font-size: 11px;">{{ optional($item->updater)->name ?? 'System' }}</div>
                                </td>
                                <td>
                                    <div class="text-right">
                                        <button type="button" class="set-btn btn-sm save-btn" data-id="{{ $item->id }}" style="min-height: 32px; padding: 6px 14px; border-radius: 8px;">
                                            Save Settings
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <div style="font-size: 24px; color: var(--set-muted);"><i class="fas fa-clock"></i></div>
                                    <h6 class="mt-3 font-weight-bold">No Retention Settings Found</h6>
                                    <p class="small mb-0">System default cleanups will apply automatically.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
    $(document).ready(function() {
        $('.save-btn').on('click', function() {
            const id = $(this).data('id');
            const btn = $(this);
            
            const data = {
                id: id,
                retention_days: $('#days_' + id).val(),
                delete_only_read: $('#read_' + id).is(':checked') ? 1 : 0,
                is_active: $('#active_' + id).is(':checked') ? 1 : 0,
                _token: '{{ csrf_token() }}',
                _method: 'PATCH'
            };

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: '{{ route("settings.notification-retention.update") }}',
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(err) {
                    toastr.error('Error updating settings');
                },
                complete: function() {
                    btn.prop('disabled', false).html('Save Settings');
                }
            });
        });
    });
</script>
@endsection
