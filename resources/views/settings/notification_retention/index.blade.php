@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Notification Retention Settings')

@section('_head')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .settings-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .settings-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        padding: 30px;
    }

    .settings-header h3 {
        font-weight: 800;
        margin: 0;
    }

    .settings-header p {
        opacity: 0.8;
        margin: 10px 0 0;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-weight: 800;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 15px 20px;
        border: 0;
    }

    .table td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid var(--orb-border);
    }

    .category-badge {
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-weight: 800;
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 13px;
    }

    .btn-orb {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border: 0;
        color: #fff;
        border-radius: 12px;
        font-weight: 700;
        padding: 8px 16px;
    }

    .btn-orb:hover {
        opacity: 0.9;
        color: #fff;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border-color: var(--orb-border);
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: var(--orb-primary);
    }

    input:checked + .slider:before {
        transform: translateX(18px);
    }
</style>
@endsection

@section('_content')
<div class="container-fluid py-4">
    <div class="settings-card">
        <div class="settings-header">
            <h3>Notification Retention Settings</h3>
            <p>Define how many days notifications should be kept in the system before automatic cleanup.</p>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Retention (Days)</th>
                        <th>Delete Only Read</th>
                        <th>Active</th>
                        <th>Last Updated</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settings as $item)
                    <tr>
                        <td>
                            <span class="category-badge">{{ $item->display_name }}</span>
                            <div class="small text-muted mt-1">{{ $item->notification_type }}</div>
                        </td>
                        <td>
                            <input type="number" 
                                   id="days_{{ $item->id }}" 
                                   class="form-control form-control-sm w-100px" 
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
                            <div class="small fw-bold">{{ optional($item->updated_at)->format('d M Y') ?? '-' }}</div>
                            <div class="small text-muted">{{ optional($item->updater)->name ?? 'System' }}</div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-orb btn-sm save-btn" data-id="{{ $item->id }}">
                                Save
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

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
                    btn.prop('disabled', false).html('Save');
                }
            });
        });
    });
</script>
@endsection
