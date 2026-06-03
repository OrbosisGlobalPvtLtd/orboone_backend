@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Exit Policies')

@section('_content')
<style>
    .exit-policy-page {
        padding: 24px !important;
        background: #F6F7FB !important;
        font-family: 'Outfit', 'Inter', sans-serif !important;
        width: 100% !important;
        max-width: 1500px !important;
        margin: 0 auto !important;
    }

    /* 1. Premium Hero Header */
    .exit-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 28px 36px;
        border-radius: 28px;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 35%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff;
        margin-bottom: 24px;
        box-shadow: 0 18px 45px rgba(16, 24, 40, 0.12);
    }

    .exit-hero-content {
        max-width: 70%;
    }

    .exit-hero-kicker {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .exit-hero-title {
        font-size: 28px;
        font-weight: 900;
        margin: 0 0 8px 0;
        letter-spacing: -0.5px;
        color: #fff;
    }

    .exit-hero-subtitle {
        font-size: 13px;
        font-weight: 550;
        color: rgba(255, 255, 255, 0.88);
        margin: 0;
        line-height: 1.5;
    }

    .exit-hero-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* 2. Exit Policy Card */
    .exit-policy-card {
        background: #ffffff !important;
        border: 1px solid #E7EAF3 !important;
        border-radius: 22px !important;
        box-shadow: 0 18px 45px rgba(16, 24, 40, 0.06) !important;
        padding: 24px !important;
        margin-bottom: 24px !important;
    }

    .exit-card-title-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid #F1F3F8;
        padding-bottom: 14px;
        flex-wrap: wrap;
    }

    .exit-card-info-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .exit-card-icon {
        width: 38px;
        height: 38px;
        background: #F4F2FF;
        color: var(--orb-primary);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .exit-card-info-wrap h3 {
        font-size: 16px;
        font-weight: 850;
        color: #101828;
        margin: 0;
    }

    .exit-card-info-wrap p {
        font-size: 12px;
        color: #667085;
        margin: 2px 0 0 0;
        font-weight: 550;
    }

    .exit-control {
        height: 38px !important;
        border: 1px solid #DDE3EE !important;
        border-radius: 12px !important;
        padding: 8px 14px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: #101828 !important;
        background: #fff !important;
        outline: none !important;
        transition: all 0.2s !important;
        width: 100% !important;
    }

    .exit-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.08) !important;
    }

    select.exit-control {
        cursor: pointer !important;
        padding-right: 28px !important;
        appearance: none !important;
        background: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%23667085' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat right 12px center #fff !important;
    }

    /* Checkbox Group */
    .exit-checkbox-row {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .exit-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 750;
        color: #344054;
        cursor: pointer;
        user-select: none;
        margin: 0;
    }

    .exit-checkbox-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 1.5px solid #DDE3EE;
        accent-color: var(--orb-primary);
        cursor: pointer;
    }

    .exit-btn-submit {
        height: 38px !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 0 20px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
        cursor: pointer !important;
    }

    .exit-btn-submit:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 8px 20px rgba(75, 0, 232, 0.15) !important;
    }

    /* 3. Table Styling */
    .exit-table-wrap {
        border-radius: 16px !important;
        border: 1px solid #E7EAF3 !important;
        overflow: hidden !important;
    }

    .exit-policy-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    .exit-policy-table thead th {
        background: #F8FAFC !important;
        color: #475467 !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        border-bottom: 1px solid #E7EAF3 !important;
        padding: 14px 16px !important;
        border-top: 0 !important;
        white-space: nowrap !important;
    }

    .exit-policy-table tbody td {
        padding: 14px 16px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: #101828 !important;
        border-bottom: 1px solid #F2F4F7 !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .exit-policy-table tbody tr:hover td {
        background: #FDFDFF !important;
    }

    /* Badges */
    .exit-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .exit-badge-primary {
        background: #EFF8FF;
        color: #175CD3;
    }

    .exit-badge-warning {
        background: #FFFAEB;
        color: #B54708;
    }

    .exit-badge-info {
        background: #F4F3FF;
        color: #5925DC;
    }

    .exit-badge-success {
        background: #ECFDF5;
        color: #027A48;
    }

    .exit-badge-danger {
        background: #FEF2F2;
        color: #B42318;
    }

    .exit-badge-muted {
        background: #F2F4F7;
        color: #344054;
    }

    .exit-btn-action {
        height: 32px !important;
        padding: 0 14px !important;
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        border: 1px solid rgba(75, 0, 232, 0.15) !important;
        font-weight: 800 !important;
        font-size: 12px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.2s !important;
        cursor: pointer !important;
        gap: 4px;
    }

    .exit-btn-action:hover {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        border-color: transparent !important;
        transform: translateY(-1px) !important;
    }

    .exit-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .exit-field label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #667085;
        margin: 0;
        letter-spacing: 0.4px;
    }
</style>

<div class="exit-policy-page">
    <!-- 1. Hero Header -->
    <div class="exit-hero">
        <div class="exit-hero-content">
            <div class="exit-hero-kicker">
                <i class="fas fa-sign-out-alt"></i> Settings &bull; Exit Rules
            </div>
            <h1 class="exit-hero-title">Exit Policies</h1>
            <p class="exit-hero-subtitle">
                Manage notice period, FNF processing days, waiver, buyout and immediate exit rules from database-driven policies.
            </p>
        </div>
        <div class="exit-hero-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 14px; font-weight: 800;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 14px; font-weight: 800;">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
    </div>
    @endif

    <!-- 2. Configured Exit Policies Card -->
    <div class="exit-policy-card">
        <div class="exit-card-title-wrap">
            <div class="exit-card-info-wrap">
                <div class="exit-card-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <div>
                    <h3>Configured Exit Policies</h3>
                    <p>Database-driven exit rules applied during employee resignation and FNF settlement.</p>
                </div>
            </div>
            <div>
                <button type="button" class="exit-btn-submit" data-toggle="modal" data-target="#createPolicyModal">
                    <i class="fas fa-plus-circle mr-1"></i> Create Exit Policy
                </button>
            </div>
        </div>

        <div class="exit-table-wrap">
            <div class="table-responsive">
                <table class="exit-policy-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Policy Name</th>
                            <th style="width: 12%;">Applies To</th>
                            <th style="width: 15%;">Exit Type</th>
                            <th style="width: 10%; text-align: center;">Notice Period</th>
                            <th style="width: 10%; text-align: center;">FNF Days</th>
                            <th style="width: 18%; text-align: center;">Waiver / Buyout / Immediate</th>
                            <th style="width: 10%;">Effective</th>
                            <th style="width: 8%; text-align: center;">Status</th>
                            <th style="width: 7%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($policies as $policy)
                        <tr>
                            <td style="font-weight: 900; color: #101828;">{{ $policy->name }}</td>
                            <td>
                                <span class="exit-badge exit-badge-info">{{ ucwords($policy->applies_to) }}</span>
                            </td>
                            <td>
                                <span class="exit-badge exit-badge-primary">
                                    {{ ucwords(str_replace('_', ' ', $policy->exit_type ?? 'All')) }}
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 800;">
                                {{ $policy->notice_period_days }} Days
                            </td>
                            <td style="text-align: center; font-weight: 800;">
                                {{ $policy->fnf_processing_days }} Days
                            </td>
                            <td style="text-align: center;">
                                <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                    @if($policy->allow_waiver)
                                    <span class="exit-badge exit-badge-success">Waiver</span>
                                    @endif
                                    @if($policy->allow_buyout)
                                    <span class="exit-badge exit-badge-warning">Buyout</span>
                                    @endif
                                    @if($policy->allow_immediate_exit)
                                    <span class="exit-badge exit-badge-danger">Immediate</span>
                                    @endif
                                    @if(!$policy->allow_waiver && !$policy->allow_buyout && !$policy->allow_immediate_exit)
                                    <span class="text-muted" style="font-size: 11px;">None</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                {{ $policy->effective_from ? \Carbon\Carbon::parse($policy->effective_from)->format('d-M-Y') : 'Immediate' }}
                            </td>
                            <td style="text-align: center;">
                                @if($policy->is_active)
                                <span class="exit-badge exit-badge-success">
                                    <i class="fas fa-check-circle mr-1"></i> Active
                                </span>
                                @else
                                <span class="exit-badge exit-badge-muted">
                                    <i class="fas fa-times-circle mr-1"></i> Inactive
                                </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button class="exit-btn-action" type="button" data-toggle="modal" data-target="#editPolicyModal_{{ $policy->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle mr-1"></i> No exit policies configured.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

<!-- 1. Create Policy Modal -->
<div class="modal fade" id="createPolicyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Create Exit Policy</h5>
                    <p class="orb-modal-subtitle">Define new regulatory notice periods and FNF settlement workflows.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; outline: none; border: none; background: transparent;">
                    <span aria-hidden="true" style="font-size: 28px;">&times;</span>
                </button>
            </div>
            <form action="{{ route('settings.hrms_exit_policies.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px; overflow-y: auto; max-height: calc(100vh - 220px);">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="exit-field">
                                <label>Policy Name</label>
                                <input type="text" name="name" class="exit-control" placeholder="e.g. Standard Permanent Resignation" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>Applies To</label>
                                <select name="applies_to" class="exit-control" required>
                                    <option value="all">All</option>
                                    <option value="internship">Internship</option>
                                    <option value="probation">Probation</option>
                                    <option value="permanent">Permanent</option>
                                    <option value="contract">Contract</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>Exit Type</label>
                                <select name="exit_type" class="exit-control">
                                    <option value="">All Exit Types</option>
                                    <option value="resignation">Resignation</option>
                                    <option value="termination">Termination</option>
                                    <option value="internship_exit">Internship Exit</option>
                                    <option value="internship_completed">Internship Completed</option>
                                    <option value="contract_end">Contract End</option>
                                    <option value="absconding">Absconding</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>Notice Period (Days)</label>
                                <input type="number" name="notice_period_days" class="exit-control" min="0" value="15" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>FNF Settlement (Days)</label>
                                <input type="number" name="fnf_processing_days" class="exit-control" min="0" value="15" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>Effective From</label>
                                <input type="date" name="effective_from" class="exit-control" value="{{ now()->toDateString() }}">
                            </div>
                        </div>
                    </div>
                    <div class="exit-checkbox-row" style="margin-top: 10px; border-top: 1px dashed #E7EAF3; padding-top: 16px;">
                        <label class="exit-checkbox-label">
                            <input type="checkbox" name="allow_waiver" value="1" checked> Allow Waiver
                        </label>
                        <label class="exit-checkbox-label">
                            <input type="checkbox" name="allow_buyout" value="1" checked> Allow Buyout
                        </label>
                        <label class="exit-checkbox-label">
                            <input type="checkbox" name="allow_immediate_exit" value="1" checked> Allow Immediate Exit
                        </label>
                        <label class="exit-checkbox-label">
                            <input type="checkbox" name="is_active" value="1" checked> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="background: #F8FAFC; border-top: 1px solid #E7EAF3; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-light" data-dismiss="modal" style="border-radius: 12px; font-weight: 750; font-size: 13px; height: 38px;">Cancel</button>
                    <button type="submit" class="exit-btn-submit" style="width: auto; height: 38px !important;">
                        <i class="fas fa-save mr-1"></i> Save Policy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Edit Policy Modals Loop -->
@foreach($policies as $policy)
<div class="modal fade" id="editPolicyModal_{{ $policy->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Exit Policy</h5>
                    <p class="orb-modal-subtitle">Modify parameters for the exit policy: {{ $policy->name }}</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; outline: none; border: none; background: transparent;">
                    <span aria-hidden="true" style="font-size: 28px;">&times;</span>
                </button>
            </div>
            <form action="{{ route('settings.hrms_exit_policies.update', $policy->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding: 24px; overflow-y: auto; max-height: calc(100vh - 220px);">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="exit-field">
                                <label>Policy Name</label>
                                <input type="text" name="name" value="{{ $policy->name }}" class="exit-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>Applies To</label>
                                <select name="applies_to" class="exit-control" required>
                                    @foreach(['all' => 'All', 'internship' => 'Internship', 'probation' => 'Probation', 'permanent' => 'Permanent', 'contract' => 'Contract'] as $key => $label)
                                    <option value="{{ $key }}" {{ $policy->applies_to === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>Exit Type</label>
                                <select name="exit_type" class="exit-control">
                                    <option value="" {{ empty($policy->exit_type) ? 'selected' : '' }}>All Exit Types</option>
                                    @foreach(['resignation', 'termination', 'internship_exit', 'internship_completed', 'contract_end', 'absconding'] as $type)
                                    <option value="{{ $type }}" {{ $policy->exit_type === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>Notice Period (Days)</label>
                                <input type="number" name="notice_period_days" min="0" value="{{ $policy->notice_period_days }}" class="exit-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>FNF Settlement (Days)</label>
                                <input type="number" name="fnf_processing_days" min="0" value="{{ $policy->fnf_processing_days }}" class="exit-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="exit-field">
                                <label>Effective From</label>
                                <input type="date" name="effective_from" value="{{ $policy->effective_from ? \Carbon\Carbon::parse($policy->effective_from)->format('Y-m-d') : '' }}" class="exit-control">
                            </div>
                        </div>
                    </div>
                    <div class="exit-checkbox-row" style="margin-top: 10px; border-top: 1px dashed #E7EAF3; padding-top: 16px;">
                        <label class="exit-checkbox-label">
                            <input type="checkbox" name="allow_waiver" value="1" {{ $policy->allow_waiver ? 'checked' : '' }}> Allow Waiver
                        </label>
                        <label class="exit-checkbox-label">
                            <input type="checkbox" name="allow_buyout" value="1" {{ $policy->allow_buyout ? 'checked' : '' }}> Allow Buyout
                        </label>
                        <label class="exit-checkbox-label">
                            <input type="checkbox" name="allow_immediate_exit" value="1" {{ $policy->allow_immediate_exit ? 'checked' : '' }}> Allow Immediate Exit
                        </label>
                        <label class="exit-checkbox-label">
                            <input type="checkbox" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }}> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="background: #F8FAFC; border-top: 1px solid #E7EAF3; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-light" data-dismiss="modal" style="border-radius: 12px; font-weight: 750; font-size: 13px; height: 38px;">Cancel</button>
                    <button type="submit" class="exit-btn-submit" style="width: auto; height: 38px !important;">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection