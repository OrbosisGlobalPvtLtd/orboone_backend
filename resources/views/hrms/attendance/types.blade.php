@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'attendances'])

@section('_content')
<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg) !important;
        overflow-x: hidden !important;
    }

    .att-page {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 80px);
        padding: 24px;
        background: var(--orb-bg);
        overflow-x: hidden;
    }

    .att-container {
        max-width: 1380px;
        margin: 0 auto;
    }

    /* HERO */

    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 26px 28px;
        color: #fff;
        box-shadow: 0 20px 45px rgba(75, 0, 232, .22);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin: 0 0 18px;
    }

    .orb-hero::after {
        content: '';
        position: absolute;
        width: 230px;
        height: 230px;
        border-radius: 50%;
        right: -95px;
        bottom: -115px;
        background: rgba(255, 255, 255, .10);
    }

    .orb-hero-content,
    .orb-hero-actions {
        position: relative;
        z-index: 2;
    }

    .orb-hero-content {
        min-width: 0;
    }

    .orb-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .15);
        color: rgba(255, 255, 255, .94);
        font-size: 11px;
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .orb-hero h1 {
        font-size: 28px;
        font-weight: 950;
        margin: 0;
        letter-spacing: -.03em;
        color: #fff;
    }

    .orb-hero p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .84);
        font-size: 13px;
        line-height: 1.6;
        max-width: 780px;
    }

    /* BUTTONS */

    .orb-btn {
        border-radius: 14px;
        min-height: 40px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none !important;
        border: 1px solid transparent;
        line-height: 1;
        white-space: nowrap;
    }

    .orb-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .orb-btn-primary {
        background: #fff;
        color: var(--orb-primary);
        border-color: rgba(255, 255, 255, .65);
        box-shadow: 0 12px 24px rgba(16, 24, 40, .12);
    }

    .orb-btn-primary:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .orb-btn-light {
        background: #fff;
        color: var(--orb-text);
        border-color: var(--orb-border);
    }

    .orb-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    /* SUMMARY / CARDS */

    .orb-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        margin-bottom: 18px;
        overflow: hidden;
    }

    .orb-table-card .orb-card-body {
        padding: 0;
        overflow: hidden;
    }

    .orb-table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #EEF2F6;
        background: #fff;
    }

    .orb-table-head-left {
        min-width: 0;
    }

    .orb-table-head-right {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 0 0 auto;
    }

    .orb-table-title {
        margin: 0;
        font-size: 16px;
        font-weight: 950;
        color: var(--orb-text);
        letter-spacing: -.02em;
    }

    .orb-table-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 600;
    }

    .orb-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, .10);
    }

    /* TABLES */

    .att-table-wrap {
        padding: 0;
    }

    .att-table-responsive {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        background: #fff;
    }

    .att-table {
        width: 100%;
        min-width: 900px;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }

    .att-table th {
        background: #F8FAFC;
        color: #475467;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        padding: 13px 14px;
        border-top: 0 !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .att-table td {
        vertical-align: middle !important;
        white-space: nowrap;
        padding: 13px 14px !important;
        border-color: #F2F4F7 !important;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
        border-bottom: 1px solid #F2F4F7 !important;
    }

    .att-table tbody tr {
        transition: all .15s ease;
    }

    .att-table tbody tr:hover td {
        background: #FAF8FF !important;
    }

    /* BADGES & DOTS */

    .att-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge-active {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .badge-muted {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .badge-paid {
        background: #F0F9FF;
        color: #026AA2;
        border: 1px solid #B9E6FE;
    }

    .badge-unpaid {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .type-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        border: 1px solid rgba(0,0,0,.08);
        vertical-align: middle;
        margin-right: 8px;
    }

    .att-actions {
        display: flex;
        gap: 7px;
        justify-content: flex-end;
    }

    .icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 11px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: var(--orb-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
        box-shadow: none;
    }

    .icon-btn:hover {
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .18);
        background: var(--orb-soft);
    }

    /* PREMIUM MODAL SYSTEM */

    .modal-backdrop {
        z-index: 1040 !important;
        background: #0F172A !important;
    }
    .modal-backdrop.show {
        opacity: .58 !important;
    }
    .modal {
        z-index: 1050 !important;
    }

    .orb-type-modal .modal-dialog {
        max-width: 620px;
    }

    .att-modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #fff !important;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    .att-modal-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        border-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .att-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        color: #fff;
    }

    .att-modal-subtitle {
        margin-top: 4px;
        font-size: 12px;
        color: rgba(255, 255, 255, .82);
        font-weight: 600;
    }

    .att-modal-header .close {
        color: #fff;
        opacity: 0.85;
        text-shadow: none;
        outline: none;
        font-size: 24px;
        font-weight: 300;
        transition: all 0.2s ease;
        border: 0;
        background: transparent;
        line-height: 1;
        padding: 0;
    }

    .att-modal-header .close:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .att-modal-body {
        padding: 24px;
        background: #fff !important;
    }

    .att-modal-body label {
        font-size: 10.5px;
        font-weight: 900;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
        display: block;
    }

    .att-modal-body .form-control {
        height: 40px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        font-size: 13px;
        font-weight: 700;
        color: var(--orb-text);
        box-shadow: none !important;
        background-color: #fff;
    }

    .att-modal-body .form-control[type="color"] {
        padding: 4px 8px;
    }

    .att-modal-body .form-control:focus {
        border-color: rgba(75, 0, 232, .30);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    .att-modal-section {
        border: 1px solid #EEF2F6;
        background: #FCFCFD;
        border-radius: 20px;
        padding: 18px;
        margin-bottom: 16px;
    }

    .att-modal-section-title {
        font-size: 13px;
        font-weight: 950;
        color: var(--orb-text);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .att-modal-section-title i {
        color: var(--orb-primary);
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--orb-primary) !important;
        border-color: var(--orb-primary) !important;
    }

    .custom-control-label {
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-text);
        cursor: pointer;
        padding-top: 2px;
    }

    .att-modal-footer {
        padding: 16px 24px;
        background: #F8FAFC;
        border-top: 1px solid #EEF2F6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .att-modal-footer .orb-btn {
        min-height: 38px;
        height: 38px;
        border-radius: 12px;
    }

    .orb-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    /* RESPONSIVE LAYOUTS */

    @media(max-width: 991px) {
        .orb-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .orb-hero-actions,
        .orb-hero-actions .orb-btn {
            width: 100%;
        }
    }

    @media(max-width: 768px) {
        .att-page {
            padding: 12px;
        }

        .orb-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .orb-hero h1 {
            font-size: 22px;
        }

        .orb-table-header {
            flex-direction: column;
            align-items: stretch;
            padding: 14px;
        }

        .orb-table-head-right {
            justify-content: space-between;
            width: 100%;
        }

        .orb-type-modal .modal-dialog {
            margin: 12px;
        }

        .orb-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="att-page">
    <div class="att-container">

        <!-- Hero Header -->
        <div class="orb-hero">
            <div class="orb-hero-content">
                <div class="orb-hero-kicker">
                    <i class="fas fa-tags"></i>
                    HRMS &bull; ATTENDANCE SETTINGS
                </div>
                <h1>Attendance Types</h1>
                <p>Manage attendance status identifiers and payroll impact guidance used by attendance, leave, and payroll workflows.</p>
            </div>

            <div class="orb-hero-actions">
                <button type="button" class="orb-btn orb-btn-primary" data-toggle="modal" data-target="#createTypeModal">
                    <i class="fas fa-plus text-primary"></i> Add Type
                </button>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('status') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
        @endif

        <!-- Main Content Card -->
        <div class="orb-card orb-table-card">
            <div class="orb-card-body">
                
                <div class="orb-table-header">
                    <div class="orb-table-head-left d-flex align-items-center" style="gap: 14px;">
                        <div class="orb-icon-box">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div>
                            <h3 class="orb-table-title">Attendance Types</h3>
                            <p class="orb-table-subtitle">Final payroll payable days are calculated by policy, leave type, and monthly summary.</p>
                        </div>
                    </div>

                    <div class="orb-table-head-right"></div>
                </div>

                <div class="att-table-wrap">
                    <div class="att-table-responsive">
                        <table class="att-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Payroll Impact</th>
                                    <th>Branding Color</th>
                                    <th>Linked Records</th>
                                    <th>System Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($attendanceTypes as $type)
                                    <tr>
                                        <td><strong>{{ $type->name }}</strong></td>
                                        <td><code>{{ $type->code }}</code></td>

                                        @php
                                            $impactMap = [
                                                'present' => ['Fully Paid', 'badge-paid'],
                                                'holiday' => ['Fully Paid', 'badge-paid'],
                                                'week_off' => ['Fully Paid', 'badge-paid'],
                                                'leave' => ['Leave Type Based', 'badge-muted'],
                                                'half_day' => ['Partial Paid (0.5 day)', 'badge-muted'],
                                                'late' => ['Policy Based / Violation Flag', 'badge-muted'],
                                                'early_leave' => ['Policy Based / Violation Flag', 'badge-muted'],
                                                'pending_hr' => ['Requires Resolution', 'badge-unpaid'],
                                                'punch_blocked' => ['Requires Resolution / Unpaid Until Resolved', 'badge-unpaid'],
                                                'absent' => ['Unpaid', 'badge-unpaid'],
                                                'lwp' => ['Unpaid', 'badge-unpaid'],
                                            ];
                                            $impact = $impactMap[$type->code] ?? [($type->is_paid ? 'Policy Based (Paid Default)' : 'Policy Based (Unpaid Default)'), ($type->is_paid ? 'badge-paid' : 'badge-unpaid')];
                                        @endphp
                                        <td>
                                            <span class="att-badge {{ $impact[1] }}">{{ $impact[0] }}</span>
                                        </td>

                                        <td>
                                            <span class="type-dot" style="background:{{ $type->color ?: '#64748b' }}"></span>
                                            {{ $type->color ?: '#64748b' }}
                                        </td>

                                        <td><strong>{{ $type->attendances_count ?? 0 }}</strong> linked</td>

                                        <td>
                                            <span class="att-badge {{ $type->is_active ? 'badge-active' : 'badge-muted' }}">
                                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="att-actions">
                                                <button type="button" class="icon-btn" data-toggle="modal" data-target="#editTypeModal{{ $type->id }}" title="Edit">
                                                    <i class="fas fa-edit text-primary"></i>
                                                </button>

                                                <form method="POST" action="{{ route('attendance.types.destroy', $type) }}" onsubmit="return confirm('Delete this attendance type? In-use/system types will be deactivated.')" style="display:inline-block;margin:0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="icon-btn" title="Delete / Deactivate">
                                                        <i class="fas fa-trash text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            No attendance types found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- Edit Modals Outside Table --}}
        @foreach($attendanceTypes as $type)
            <div class="modal fade orb-type-modal" id="editTypeModal{{ $type->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <form method="POST" action="{{ route('attendance.types.update', $type) }}" class="modal-content att-modal-content">
                        @csrf
                        @method('PUT')

                        <div class="modal-header att-modal-header">
                            <div>
                                <h5 class="att-modal-title">Edit Attendance Type</h5>
                                <div class="att-modal-subtitle">{{ $type->name }} · {{ $type->code }}</div>
                            </div>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body att-modal-body">
                            <div class="att-modal-section">
                                <div class="att-modal-section-title">
                                    <i class="fas fa-tag"></i> Type Details
                                </div>

                                <div class="orb-form-grid">
                                    <div class="mb-3">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $type->name) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Code</label>
                                        <input type="text" name="code" class="form-control" value="{{ old('code', $type->code) }}" required>
                                    </div>

                                    <div class="mb-0" style="grid-column: span 2;">
                                        <label>Color</label>
                                        <input type="color" name="color" class="form-control" value="{{ old('color', $type->color ?: '#64748b') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="att-modal-section mb-0">
                                <div class="att-modal-section-title">
                                    <i class="fas fa-toggle-on"></i> Type Settings
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="paid{{ $type->id }}" name="is_paid" value="1" {{ $type->is_paid ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="paid{{ $type->id }}">Paid Type</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="active{{ $type->id }}" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="active{{ $type->id }}">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer att-modal-footer">
                            <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">
                                Cancel
                            </button>

                            <button class="orb-btn orb-btn-primary" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); color: #fff;">
                                <i class="fas fa-save"></i> Save Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        {{-- Create Modal Outside Table --}}
        <div class="modal fade orb-type-modal" id="createTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('attendance.types.store') }}" class="modal-content att-modal-content">
                    @csrf

                    <div class="modal-header att-modal-header">
                        <div>
                            <h5 class="att-modal-title">Add Attendance Type</h5>
                            <div class="att-modal-subtitle">Create a custom attendance status identifier.</div>
                        </div>

                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body att-modal-body">
                        <div class="att-modal-section">
                            <div class="att-modal-section-title">
                                <i class="fas fa-plus-circle"></i> Type Details
                            </div>

                            <div class="orb-form-grid">
                                <div class="mb-3">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label>Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="custom_type" required>
                                </div>

                                <div class="mb-0" style="grid-column: span 2;">
                                    <label>Color</label>
                                    <input type="color" name="color" class="form-control" value="{{ old('color', '#64748b') }}">
                                </div>
                            </div>
                        </div>

                        <div class="att-modal-section mb-0">
                            <div class="att-modal-section-title">
                                <i class="fas fa-toggle-on"></i> Type Settings
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="newPaid" name="is_paid" value="1">
                                        <label class="custom-control-label font-weight-bold" for="newPaid">Paid Type</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="newActive" name="is_active" value="1" checked>
                                        <label class="custom-control-label font-weight-bold" for="newActive">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer att-modal-footer">
                        <button type="button" class="orb-btn orb-btn-light" data-dismiss="modal">
                            Cancel
                        </button>

                        <button class="orb-btn orb-btn-primary" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); color: #fff;">
                            <i class="fas fa-save"></i> Create Type
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
