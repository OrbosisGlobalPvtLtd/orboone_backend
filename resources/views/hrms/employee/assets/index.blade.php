@extends('layouts.panel', ['active' => 'assets'])

@section('page_title', 'My Assets')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    :root {
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
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Premium Purple Gradient Hero Header */
    .asset-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 20px !important;
        padding: 28px 32px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.12) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
        border: none !important;
    }

    .asset-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 260px !important;
        height: 260px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border-radius: 50% !important;
        filter: blur(30px) !important;
        pointer-events: none !important;
    }

    .asset-header-premium .title-area h3 {
        font-size: 24px !important;
        font-weight: 800 !important;
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
        margin-bottom: 6px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
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

    /* Table Card Layout styling */
    .asset-table-card {
        overflow: hidden !important;
        background: #fff !important;
        border-radius: 20px !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: var(--orb-shadow) !important;
    }

    .asset-table-scroll {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .asset-table-scroll table {
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
        padding: 16px 20px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .asset-table-scroll table tbody td {
        padding: 16px 20px !important;
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

    /* Modal Styling */
    .modal-content {
        border-radius: 20px !important;
        border: none !important;
        box-shadow: 0 20px 50px rgba(16, 24, 40, 0.15) !important;
        overflow: hidden !important;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border-bottom: none !important;
        padding: 20px 24px !important;
    }

    .modal-header .modal-title {
        font-weight: 800 !important;
        font-size: 18px !important;
        color: #fff !important;
    }

    .modal-header .close {
        color: #fff !important;
        opacity: 0.8;
    }

    .modal-body {
        padding: 24px !important;
        background: #fff !important;
    }

    .modal-footer {
        padding: 14px 24px !important;
        background: #F8FAFC !important;
        border-top: 1px solid var(--orb-border) !important;
    }

    .detail-row {
        margin-bottom: 14px;
        border-bottom: 1px dashed #F1F5F9;
        padding-bottom: 10px;
    }

    .detail-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .detail-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 4px !important;
    }

    .detail-val {
        font-size: 14px !important;
        font-weight: 700 !important;
        color: var(--orb-text) !important;
    }
</style>
@endsection

@section('_content')
<div class="asset-page">
    <div class="asset-container">

        <!-- Page Header -->
        <div class="asset-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-boxes"></i> My Inventory
                </div>
                <h3>My Allocated Assets</h3>
                <p>View company hardware and assets assigned to you.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 12px;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Table Card -->
        <div class="card asset-table-card">
            
            <div class="d-flex align-items-center justify-content-between" style="padding: 20px 24px; border-bottom: 1px solid #EEF2F7; background: #fff;">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <span style="width: 38px; height: 38px; border-radius: 10px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-laptop"></i>
                    </span>
                    <div>
                        <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #101828;">Allocated Assets</h4>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="asset-table-scroll">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Asset Type</th>
                            <th>Brand / Model</th>
                            <th>Serial / ID</th>
                            <th>Assigned Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assets as $allocation)
                        <tr>
                            <td>
                                <span class="font-weight-bold text-dark">
                                    <i class="fas {{ $allocation->asset_type == 'Laptop' ? 'fa-laptop' : ($allocation->asset_type == 'Mobile' ? 'fa-mobile-alt' : 'fa-id-badge') }} mr-2 text-muted"></i>
                                    {{ $allocation->asset_type }}
                                </span>
                            </td>
                            <td>
                                <span class="text-dark font-weight-bold">{{ $allocation->brand_model ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <code class="text-primary font-weight-bold">{{ $allocation->asset_id_sn ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ $allocation->issue_date ? \Carbon\Carbon::parse($allocation->issue_date)->format('d M Y') : 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($allocation->status == 'Active')
                                    <span class="status-badge-premium status-badge-active">Active</span>
                                @else
                                    <span class="status-badge-premium status-badge-returned">Returned</span>
                                @endif
                            </td>
                            <td class="text-right pr-4">
                                <button type="button" class="btn btn-sm btn-light text-primary font-weight-bold" data-toggle="modal" data-target="#assetModal{{ $allocation->id }}" style="border-radius: 8px; padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                                <p class="mb-0 font-weight-bold">No assets currently allocated to you.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assets->hasPages())
            <div class="px-4 py-3 border-top" style="background: #F8FAFC;">
                {{ $assets->links() }}
            </div>
            @endif

        </div>
    </div>
</div>

<!-- Details Modals -->
@foreach ($assets as $allocation)
<div class="modal fade" id="assetModal{{ $allocation->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i> Asset Allocation Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Asset Category</div>
                        <div class="detail-val">{{ $allocation->asset_type }}</div>
                    </div>
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Status</div>
                        <div class="detail-val">
                            @if ($allocation->status == 'Active')
                                <span class="badge badge-success px-2 py-1">Active</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">Returned</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Brand / Model</div>
                        <div class="detail-val">{{ $allocation->brand_model ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Serial / Asset ID</div>
                        <div class="detail-val"><code>{{ $allocation->asset_id_sn ?? 'N/A' }}</code></div>
                    </div>
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Issue Date</div>
                        <div class="detail-val">{{ $allocation->issue_date ? \Carbon\Carbon::parse($allocation->issue_date)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Condition</div>
                        <div class="detail-val">{{ $allocation->condition ?? 'New' }}</div>
                    </div>

                    @if($allocation->asset_type == 'Mobile' || $allocation->asset_type == 'SIM Card')
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">SIM / Mobile Number</div>
                        <div class="detail-val">{{ $allocation->mobile_sim_number ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Plan Details</div>
                        <div class="detail-val">{{ $allocation->plan_details ?? 'N/A' }}</div>
                    </div>
                    @endif

                    @if($allocation->asset_type == 'Laptop')
                    <div class="col-12 detail-row">
                        <div class="detail-label">Accessories Included</div>
                        <div class="detail-val">
                            <span class="mr-3"><i class="fas {{ $allocation->has_charger ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} mr-1"></i> Charger</span>
                            <span><i class="fas {{ $allocation->has_bag ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} mr-1"></i> Laptop Bag</span>
                        </div>
                    </div>
                    @endif

                    @if($allocation->status == 'Returned')
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Returned Date</div>
                        <div class="detail-val">{{ $allocation->returned_date ? \Carbon\Carbon::parse($allocation->returned_date)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 detail-row">
                        <div class="detail-label">Return Condition</div>
                        <div class="detail-val">{{ $allocation->return_condition ?? 'N/A' }}</div>
                    </div>
                    <div class="col-12 detail-row">
                        <div class="detail-label">Return Remarks</div>
                        <div class="detail-val">{{ $allocation->return_remarks ?? 'No remarks' }}</div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
