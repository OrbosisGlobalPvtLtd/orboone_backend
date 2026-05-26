@extends('layouts.admin', ['accesses' => $accesses ?? [], 'active' => 'data'])

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@endsection

@section('_content')
@php
    if (!isset($employees)) {
        $employees = \App\Models\HRMS\Employee\EmployeeM::with(['user', 'employeeDetail'])->get();
    }
@endphp
@include('hrms.employee.partials.styles')

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

    .asset-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .asset-container {
        max-width: 1500px;
        margin: 0 auto;
    }

    /* Premium Purple Gradient Hero Header */
    .asset-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 26px !important;
        padding: 32px 36px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 28px !important;
        border: none !important;
    }

    .asset-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 300px !important;
        height: 300px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .asset-header-premium .title-area h3 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .asset-header-premium .title-area p {
        font-size: 14px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .asset-header-premium .header-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.75) !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* Premium Pill Buttons */
    .asset-btn-pill {
        height: 42px !important;
        padding: 0 20px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: none !important;
        cursor: pointer !important;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }

    .asset-btn-pill-primary {
        background: #fff !important;
        color: var(--orb-primary) !important;
    }

    .asset-btn-pill-primary:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
    }

    /* Attached Filters */
    .asset-filters-attached {
        background: #F8FAFC !important;
        border-bottom: 1px solid var(--orb-border) !important;
        padding: 20px 26px 12px !important;
    }

    .asset-filter-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 12px !important;
        align-items: flex-end !important;
    }

    .asset-filter-grid label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .asset-filter-grid .form-control {
        height: 44px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        width: 100% !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .asset-filter-grid .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    /* Table Badges */
    .status-badge-premium {
        font-size: 10px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        padding: 5px 12px !important;
        border-radius: 50px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
    }

    .status-badge-active { background: #ECFDF5 !important; color: #047857 !important; }
    .status-badge-returned { background: #F1F5F9 !important; color: #475569 !important; }

    /* Modal Styling */
    .modal-content {
        border-radius: 24px !important;
        border: none !important;
        box-shadow: 0 20px 50px rgba(16, 24, 40, 0.15) !important;
        overflow: hidden !important;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border-bottom: none !important;
        padding: 24px !important;
    }

    .modal-header .modal-title {
        font-weight: 900 !important;
        font-size: 20px !important;
    }

    .modal-body {
        padding: 28px !important;
        background: #fff !important;
    }

    .modal-footer {
        padding: 16px 28px !important;
        background: #F8FAFC !important;
        border-top: 1px solid var(--orb-border) !important;
    }

    .modal-body label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .modal-body .form-control {
        height: 40px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
    }

    .modal-body .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    /* Entries Dropdown CSS */
    .dataTables_length,
    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
        margin: 0 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: var(--orb-muted) !important;
    }

    .dataTables_length select {
        width: 72px !important;
        height: 34px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
        border: 1px solid var(--orb-border) !important;
        outline: none !important;
    }

    /* Export button CSS */
    .orb-export-btn {
        height: 34px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        background: #fff !important;
        border: 1px solid #E7EAF3 !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        margin-left: 6px !important;
        transition: all 0.2s ease !important;
        color: #475467 !important;
    }

    .orb-export-btn:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
        transform: translateY(-1px) !important;
    }

    .btn-undo:hover {
        background: #F8FAFC !important;
        border-color: #cbd5e1 !important;
        color: #4B00E8 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }

    /* Table Card Layout styling */
    .asset-table-card {
        overflow: hidden !important;
        background: #fff !important;
        border-radius: 24px !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: 0 14px 35px rgba(16,24,40,.07) !important;
    }

    .asset-table-scroll {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        border: none !important;
    }

    .asset-table-scroll table {
        min-width: 1100px !important;
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .asset-table-scroll table thead th {
        background: #F8FAFC !important;
        color: #101828 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 16px 18px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .asset-table-scroll table tbody td {
        padding: 16px 18px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        background: #fff !important;
    }

    .asset-table-scroll table tbody tr:hover td {
        background: #FAFBFF !important;
    }

    .asset-dt-toolbar,
    .asset-dt-footer,
    .asset-filter-wrap,
    .asset-table-head {
        overflow: visible !important;
        background: #fff !important;
    }

    .asset-dt-toolbar {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
        padding: 16px 26px !important;
        border-top: 1px solid #F1F5F9 !important;
        border-bottom: 1px solid #F1F5F9 !important;
        background: #fff !important;
    }

    .asset-dt-toolbar .toolbar-left {
        display: flex !important;
        align-items: center !important;
    }

    .asset-dt-toolbar .toolbar-right {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .asset-dt-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        padding: 18px 26px 24px !important;
        border-top: 1px solid #E7EAF3 !important;
        flex-wrap: wrap !important;
        background: #fff !important;
    }

    .asset-dt-footer .footer-left {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-muted) !important;
    }

    .asset-dt-footer .footer-right {
        display: flex !important;
        align-items: center !important;
    }

    .dataTables_info,
    .dataTables_paginate {
        float: none !important;
        overflow: visible !important;
    }

    /* Modal inputs select fixes */
    select,
    select option,
    .form-select,
    .form-select option {
        color: #101828 !important;
        background: #fff !important;
    }

    .select2-container .select2-selection__rendered {
        color: #101828 !important;
    }

    @media (max-width: 991px) {
        .asset-header-premium {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 24px !important;
        }
        .asset-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 575px) {
        .asset-filter-grid {
            grid-template-columns: 1fr !important;
        }
        .asset-btn-pill {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>

<div class="asset-page">
    <div class="asset-container">

        <!-- Premium Page Header -->
        <div class="asset-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-boxes"></i> Inventory Allocations
                </div>
                <h3>Asset Allocation</h3>
                <p>Manage company assets assigned to employees</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 12px;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Main Content Card -->
        <div class="card orb-table-card asset-table-card">
            
            <div class="orb-table-card-header asset-table-head d-flex align-items-center justify-content-between" style="padding: 24px 26px 18px; border-bottom: 1px solid #EEF2F7; background: #fff; flex-wrap: wrap; gap: 16px;">
                <div class="orb-title-wrap d-flex align-items-center" style="gap: 16px;">
                    <span class="orb-card-icon" style="width: 46px; height: 46px; border-radius: 12px; background: #F4F2FF; color: #4B00E8; display: inline-flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-list-ul"></i>
                    </span>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #101828;">Asset Allocation Records</h3>
                        <p style="margin: 4px 0 0 0; font-size: 13px; color: #667085;">Manage company assets assigned to employees.</p>
                    </div>
                </div>

                <div class="d-flex align-items-center" style="gap: 12px;">
                    <!-- Reset Filters Button -->
                    <button type="button" class="btn btn-undo btn-outline-secondary btn-sm d-flex align-items-center" style="height: 40px !important; border-radius: 10px !important; padding: 0 16px !important; font-size: 13px !important; font-weight: 700 !important; border: 1px solid #e2e8f0 !important; color: #475467 !important; background: #fff !important; transition: all 0.2s ease !important; cursor: pointer;">
                        <i class="fas fa-undo mr-2" style="font-size: 11px;"></i> Reset Filters
                    </button>

                    <!-- Assign Asset Button -->
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center" data-toggle="modal" data-target="#assignAssetModal" style="height: 40px !important; border-radius: 10px !important; padding: 0 16px !important; font-size: 13px !important; font-weight: 700 !important; background: var(--orb-primary) !important; border-color: var(--orb-primary) !important; color: #fff !important; transition: all 0.2s ease !important; cursor: pointer; box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15) !important;">
                        <i class="fas fa-plus-circle mr-2" style="font-size: 13px;"></i> Assign Asset
                    </button>
                </div>
            </div>

            <!-- Attached Filters inside table card -->
            <div class="asset-filters-attached asset-filter-wrap">
                <form id="assetFilterForm" onsubmit="return false;">
                    <div class="asset-filter-grid">
                        
                        <div>
                            <label>Search Employee</label>
                            <input type="text" name="employee_name" class="form-control" placeholder="Search employee..." value="{{ request('employee_name') }}">
                        </div>

                        <div>
                            <label>Asset Type</label>
                            <select name="asset_type" class="form-control">
                                <option value="">All Asset Types</option>
                                <option value="Laptop" {{ request('asset_type') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                <option value="Mobile" {{ request('asset_type') == 'Mobile' ? 'selected' : '' }}>Mobile</option>
                                <option value="ID Card" {{ request('asset_type') == 'ID Card' ? 'selected' : '' }}>ID Card</option>
                            </select>
                        </div>

                        <div>
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Custom DataTables Toolbar Row -->
            <div class="asset-dt-toolbar">
                <div class="toolbar-left"></div>
                <div class="toolbar-right"></div>
            </div>
            
            <!-- Table Section -->
            <div class="asset-table-scroll">
                    <table class="table mb-0" id="assetAllocationsTable">
                        <thead>
                            <tr>
                                <th class="pl-4">Employee</th>
                                <th>Asset Type</th>
                                <th>Assigned Date</th>
                                <th class="text-center">Status</th>
                                <th class="text-right pr-4 no-export">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assetAllocations as $allocation)
                            <tr>
                                <td class="pl-4">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 p-2 rounded bg-light" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user-circle text-primary" style="font-size: 1.25rem;"></i>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold text-dark mb-1 d-block" style="font-size: 14px;">
                                                {{ $allocation->employee->user->name ?? ($allocation->employee->employeeDetail->name ?? 'Unknown Employee') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-dark">
                                        <i class="fas {{ $allocation->asset_type == 'Laptop' ? 'fa-laptop' : ($allocation->asset_type == 'Mobile' ? 'fa-mobile-alt' : 'fa-id-badge') }} mr-2 text-muted"></i>
                                        {{ $allocation->asset_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        {{ \Carbon\Carbon::parse($allocation->assigned_date)->format('d M Y') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($allocation->status == 'Active')
                                        <span class="status-badge-premium status-badge-active">
                                            Active
                                        </span>
                                    @else
                                        <span class="status-badge-premium status-badge-returned">
                                            Returned
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right pr-4">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-light text-primary hover-shadow" data-toggle="modal" data-target="#editAssetModal{{ $allocation->id }}" title="Edit Allocation" style="border-radius: 50% !important; width: 32px; height: 32px; padding: 0 !important; display: inline-flex; align-items: center; justify-content: center; background: #f8f9fc;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light text-danger hover-shadow ml-1" title="Delete Allocation" onclick="openDeleteModal({{ $allocation->id }})" style="border-radius: 50% !important; width: 32px; height: 32px; padding: 0 !important; display: inline-flex; align-items: center; justify-content: center; background: #f8f9fc;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <form id="delete-form-{{ $allocation->id }}" action="{{ route('hrms.assets.destroy', $allocation->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <!-- Empty block handled elegantly by DataTables -->
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer for Pagination & Info -->
                <div class="asset-dt-footer">
                    <div class="footer-left"></div>
                    <div class="footer-right"></div>
                </div>

            </div>
    </div>
</div>

<!-- ==================================================
     ASSIGN ASSET MODAL (CREATE)
     ================================================== -->
<div class="modal fade" id="assignAssetModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Assign Company Asset</h5>
                    <p class="mb-0 text-white-50 small">Allocate a new asset to an employee</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('hrms.assets.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label>Select Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="" disabled selected>-- Select an Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">
                                            {{ $emp->user->name ?? ($emp->employeeDetail->name ?? 'Employee #'.$emp->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label>Asset Type <span class="text-danger">*</span></label>
                                <select name="asset_type" class="form-control asset-type-select" required>
                                    <option value="" disabled selected>-- Select Asset Type --</option>
                                    <option value="Laptop">Laptop</option>
                                    <option value="Mobile">Mobile</option>
                                    <option value="SIM Card">SIM Card</option>
                                    <option value="ID Card">ID Card</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic fields wrapper -->
                    <div class="dynamic-section mb-4 p-4 bg-light rounded" style="display:none; border: 1px solid #e3e6f0;">
                        <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-cogs mr-1"></i> Asset Setup Details</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3 asset-brand-field">
                                <label>Brand / Model <span class="text-danger">*</span></label>
                                <input type="text" name="brand_model" class="form-control brand-model-input" placeholder="e.g. Dell XPS 15">
                            </div>
                            <div class="col-md-4 mb-3 asset-sn-field">
                                <label>Asset ID / Serial No. <span class="text-danger">*</span></label>
                                <input type="text" name="asset_id_sn" class="form-control asset-id-sn-input" placeholder="e.g. SN-98765">
                            </div>
                            <div class="col-md-4 mb-3 asset-condition-field">
                                <label>Condition <span class="text-danger">*</span></label>
                                <select name="condition" class="form-control">
                                    <option value="New">New</option>
                                    <option value="Used">Used</option>
                                    <option value="Refurbished">Refurbished</option>
                                    <option value="Damaged">Damaged</option>
                                </select>
                            </div>

                            <!-- Mobile / SIM Number -->
                            <div class="col-md-6 mb-3 mobile-sim-field" style="display:none;">
                                <label>Mobile / SIM Number <span class="text-danger">*</span></label>
                                <input type="text" name="mobile_sim_number" class="form-control mobile-sim-number-input" placeholder="+91 XXXXX XXXXX">
                            </div>
                            <div class="col-md-6 mb-3 plan-details-field" style="display:none;">
                                <label>Plan Details</label>
                                <input type="text" name="plan_details" class="form-control" placeholder="e.g. Corporate 5G Unlimited">
                            </div>

                            <!-- ID Card Options -->
                            <div class="col-md-12 mb-3 id-card-field" style="display:none;">
                                <label>ID Card Options <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 flex-wrap bg-white p-3 border rounded">
                                    <div class="form-check mr-4">
                                        <input class="form-check-input" type="checkbox" name="id_card_options[]" value="RFID Access" id="rfid_access_new">
                                        <label class="form-check-label font-weight-bold ml-1" for="rfid_access_new">RFID Access Room</label>
                                    </div>
                                    <div class="form-check mr-4">
                                        <input class="form-check-input" type="checkbox" name="id_card_options[]" value="Biometric" id="biometric_new">
                                        <label class="form-check-label font-weight-bold ml-1" for="biometric_new">Biometric Sync</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="id_card_options[]" value="Visual ID Only" id="visual_id_new">
                                        <label class="form-check-label font-weight-bold ml-1" for="visual_id_new">Visual Print Only</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Accessories -->
                            <div class="col-md-12 mb-3 accessories-field">
                                <label>Accessories Included</label>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="custom-control custom-switch mr-4">
                                        <input type="checkbox" class="custom-control-input" id="has_charger_new" name="has_charger" value="1">
                                        <label class="custom-control-label font-weight-bold" for="has_charger_new">Charger</label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="has_bag_new" name="has_bag" value="1">
                                        <label class="custom-control-label font-weight-bold" for="has_bag_new">Laptop Bag</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label>Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label>Asset Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="Active" selected>Assigned (Active)</option>
                                    <option value="Returned">Returned</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================================================
     EDIT ASSET ALLOCATION MODALS (One per allocation)
     ================================================== -->
@foreach ($assetAllocations as $allocation)
<div class="modal fade" id="editAssetModal{{ $allocation->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Edit Allocation Details</h5>
                    <p class="mb-0 text-white-50 small">Update the details of an existing asset allocation</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('hrms.assets.update', $allocation->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label>Select Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="" disabled>-- Select an Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ $allocation->employee_id == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->user->name ?? ($emp->employeeDetail->name ?? 'Employee #'.$emp->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label>Asset Type <span class="text-danger">*</span></label>
                                <select name="asset_type" class="form-control asset-type-select" required>
                                    <option value="" disabled>-- Select Asset Type --</option>
                                    <option value="Laptop" {{ $allocation->asset_type == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                    <option value="Mobile" {{ $allocation->asset_type == 'Mobile' ? 'selected' : '' }}>Mobile</option>
                                    <option value="SIM Card" {{ $allocation->asset_type == 'SIM Card' ? 'selected' : '' }}>SIM Card</option>
                                    <option value="ID Card" {{ $allocation->asset_type == 'ID Card' ? 'selected' : '' }}>ID Card</option>
                                    <option value="Other" {{ $allocation->asset_type == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic fields wrapper -->
                    <div class="dynamic-section mb-4 p-4 bg-light rounded" style="display:none; border: 1px solid #e3e6f0;">
                        <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-cogs mr-1"></i> Asset Setup Details</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3 asset-brand-field">
                                <label>Brand / Model <span class="text-danger">*</span></label>
                                <input type="text" name="brand_model" class="form-control brand-model-input" placeholder="e.g. Dell XPS 15" value="{{ $allocation->brand_model }}">
                            </div>
                            <div class="col-md-4 mb-3 asset-sn-field">
                                <label>Asset ID / Serial No. <span class="text-danger">*</span></label>
                                <input type="text" name="asset_id_sn" class="form-control asset-id-sn-input" placeholder="e.g. SN-98765" value="{{ $allocation->asset_id_sn }}">
                            </div>
                            <div class="col-md-4 mb-3 asset-condition-field">
                                <label>Condition <span class="text-danger">*</span></label>
                                <select name="condition" class="form-control">
                                    <option value="New" {{ $allocation->condition == 'New' ? 'selected' : '' }}>New</option>
                                    <option value="Used" {{ $allocation->condition == 'Used' ? 'selected' : '' }}>Used</option>
                                    <option value="Refurbished" {{ $allocation->condition == 'Refurbished' ? 'selected' : '' }}>Refurbished</option>
                                    <option value="Damaged" {{ $allocation->condition == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                                </select>
                            </div>

                            <!-- Mobile / SIM Number -->
                            <div class="col-md-6 mb-3 mobile-sim-field" style="display:none;">
                                <label>Mobile / SIM Number <span class="text-danger">*</span></label>
                                <input type="text" name="mobile_sim_number" class="form-control mobile-sim-number-input" placeholder="+91 XXXXX XXXXX" value="{{ $allocation->mobile_sim_number }}">
                            </div>
                            <div class="col-md-6 mb-3 plan-details-field" style="display:none;">
                                <label>Plan Details</label>
                                <input type="text" name="plan_details" class="form-control" placeholder="e.g. Corporate 5G Unlimited" value="{{ $allocation->plan_details }}">
                            </div>

                            @php 
                                $idOptions = is_string($allocation->id_card_options) ? json_decode($allocation->id_card_options, true) : [];
                                if(!is_array($idOptions)) $idOptions = [];
                            @endphp
                            <!-- ID Card Options -->
                            <div class="col-md-12 mb-3 id-card-field" style="display:none;">
                                <label>ID Card Options <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 flex-wrap bg-white p-3 border rounded">
                                    <div class="form-check mr-4">
                                        <input class="form-check-input" type="checkbox" name="id_card_options[]" value="RFID Access" id="rfid_access_{{ $allocation->id }}" {{ in_array('RFID Access', $idOptions) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold ml-1" for="rfid_access_{{ $allocation->id }}">RFID Access Room</label>
                                    </div>
                                    <div class="form-check mr-4">
                                        <input class="form-check-input" type="checkbox" name="id_card_options[]" value="Biometric" id="biometric_{{ $allocation->id }}" {{ in_array('Biometric', $idOptions) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold ml-1" for="biometric_{{ $allocation->id }}">Biometric Sync</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="id_card_options[]" value="Visual ID Only" id="visual_id_{{ $allocation->id }}" {{ in_array('Visual ID Only', $idOptions) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold ml-1" for="visual_id_{{ $allocation->id }}">Visual Print Only</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Accessories -->
                            <div class="col-md-12 mb-3 accessories-field">
                                <label>Accessories Included</label>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="custom-control custom-switch mr-4">
                                        <input type="checkbox" class="custom-control-input" id="has_charger_{{ $allocation->id }}" name="has_charger" value="1" {{ $allocation->has_charger ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="has_charger_{{ $allocation->id }}">Charger</label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="has_bag_{{ $allocation->id }}" name="has_bag" value="1" {{ $allocation->has_bag ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="has_bag_{{ $allocation->id }}">Laptop Bag</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label>Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" class="form-control" value="{{ \Carbon\Carbon::parse($allocation->issue_date ?? $allocation->assigned_date ?? now())->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label>Asset Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="Active" {{ $allocation->status == 'Active' ? 'selected' : '' }}>Assigned (Active)</option>
                                    <option value="Returned" {{ $allocation->status == 'Returned' ? 'selected' : '' }}>Returned</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAssetModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #EC4E74) !important;">
                <div>
                    <h5 class="modal-title">Delete Asset Record</h5>
                    <p class="mb-0 text-white-50 small">Permanently remove this asset allocation</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="mb-4 mt-2">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #ffe5e5; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="fas fa-exclamation-triangle fa-2x" style="color: #dc3545;"></i>
                    </div>
                </div>
                <h5 class="font-weight-bold mb-3" style="color: var(--orb-text);">Are you absolutely sure?</h5>
                <p class="text-muted mb-4" style="line-height: 1.6;">You are about to permanently delete this asset allocation. This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteActionBtn" class="btn btn-danger border-0" style="background: linear-gradient(135deg, #DC2626, #EC4E74) !important; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2) !important;">Yes, Delete it!</button>
            </div>
        </div>
    </div>
</div>
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
    let activeDeletionId = null;

    function openDeleteModal(id) {
        activeDeletionId = id;
        $('#deleteAssetModal').modal('show');
    }

    document.getElementById('confirmDeleteActionBtn').addEventListener('click', function() {
        if (activeDeletionId) {
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';
            this.classList.add('disabled');
            document.getElementById('delete-form-' + activeDeletionId).submit();
        }
    });

    $(document).ready(function() {
        // Safe DataTable Initialization
        if ($.fn.DataTable.isDataTable('#assetAllocationsTable')) {
            $('#assetAllocationsTable').DataTable().destroy();
        }

        var table = $('#assetAllocationsTable').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: false,
            autoWidth: false,
            scrollX: false,
            dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-right'B>>" +
                "<'row'<'col-md-12 orb-table-scroll't>>" +
                "<'row align-items-center mt-3 px-4 pb-4'<'col-md-5'i><'col-md-7'p>>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-info"></i> CSV',
                    className: 'orb-export-btn',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'orb-export-btn',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'orb-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Asset Allocation Records',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'orb-export-btn',
                    title: 'Asset Allocation Records',
                    exportOptions: { columns: ':not(.no-export)' }
                }
            ],
            language: {
                emptyTable: 'No records found.',
                zeroRecords: 'No matching records found.',
                paginate: {
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>'
                }
            }
        });

        // Relocate the generated controls to the custom asset-dt-toolbar and asset-dt-footer containers!
        $('.asset-dt-toolbar .toolbar-left').append($('.dataTables_length'));
        $('.asset-dt-toolbar .toolbar-right').append($('.dt-buttons'));
        $('.asset-dt-footer .footer-left').append($('.dataTables_info'));
        $('.asset-dt-footer .footer-right').append($('.dataTables_paginate'));

        // Auto-apply filters
        $('input[name="employee_name"]').on('keyup', function() {
            table.column(0).search(this.value).draw();
        });

        $('select[name="asset_type"]').on('change', function() {
            table.column(1).search(this.value).draw();
        });

        $('select[name="status"]').on('change', function() {
            table.column(3).search(this.value).draw();
        });

        // Reset Filters Action
        $('.btn-undo').on('click', function(e) {
            e.preventDefault();
            $('input[name="employee_name"]').val('');
            $('select[name="asset_type"]').val('');
            $('select[name="status"]').val('');
            table.search('').columns().search('').draw();
        });

        // Dynamic Field Toggling across modals
        $('.asset-type-select').each(function() {
            if ($(this).val()) {
                $(this).trigger('change');
            }
        });
    });

    $(document).on('change', '.asset-type-select', function() {
        const $modal = $(this).closest('.modal');
        const type = $(this).val();
        
        const $mainSection = $modal.find('.dynamic-section');
        const $mobileSim = $modal.find('.mobile-sim-field');
        const $planDetails = $modal.find('.plan-details-field');
        const $idCard = $modal.find('.id-card-field');
        const $brandField = $modal.find('.asset-brand-field');
        const $conditionField = $modal.find('.asset-condition-field');
        const $accessoriesField = $modal.find('.accessories-field');
        const $snField = $modal.find('.asset-sn-field');
        
        $modal.find('.brand-model-input').prop('required', false);
        $modal.find('.asset-id-sn-input').prop('required', false);
        $modal.find('.mobile-sim-number-input').prop('required', false);

        if (type) {
            $mainSection.show();
            
            $brandField.show();
            $conditionField.show();
            $accessoriesField.show();
            $snField.show();
            $mobileSim.hide();
            $planDetails.hide();
            $idCard.hide();
            
            if (type === 'Mobile' || type === 'SIM Card') {
                $mobileSim.show();
                $planDetails.show();
                $modal.find('.mobile-sim-number-input').prop('required', true);
                
                if (type === 'SIM Card') {
                    $brandField.hide();
                    $conditionField.hide();
                    $accessoriesField.hide();
                } else {
                    $modal.find('.brand-model-input').prop('required', true);
                    $modal.find('.asset-id-sn-input').prop('required', true);
                }
            } else if (type === 'ID Card') {
                $brandField.hide();
                $conditionField.hide();
                $accessoriesField.hide();
                $idCard.show();
                $modal.find('.asset-id-sn-input').prop('required', true);
            } else {
                $modal.find('.brand-model-input').prop('required', true);
                $modal.find('.asset-id-sn-input').prop('required', true);
            }
        } else {
            $mainSection.hide();
        }
    });
</script>
@endsection
