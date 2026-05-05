@extends('layouts.admin', ['accesses' => $accesses ?? [], 'active' => 'data'])

@section('_content')
<style>
    .asset-show-page {
        --orbo-primary: #4B00E8;
        --orbo-purple: #8600EE;
        --orbo-magenta: #D400D5;
        --orbo-coral: #EC4E74;
        --orbo-gold: #FFB101;
        --orbo-border: #ece8f7;
        --orbo-surface: #ffffff;
        --orbo-soft: #f8f6ff;
        --orbo-text: #222138;
    }

    .custom-card {
        border: 1px solid var(--orbo-border);
        border-radius: 14px;
        box-shadow: 0 14px 34px rgba(75, 0, 232, 0.08);
        background: var(--orbo-surface);
        overflow: hidden;
    }

    .card-gradient-header {
        background: linear-gradient(135deg, var(--orbo-primary), var(--orbo-purple) 52%, var(--orbo-magenta));
        color: white;
        padding: 24px 26px;
        position: relative;
        border-bottom: 4px solid var(--orbo-gold);
    }

    .header-icon {
        position: absolute;
        right: 24px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 3rem;
        opacity: 0.14;
    }

    .detail-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.03rem;
        color: var(--orbo-primary);
        margin-bottom: 7px;
        display: block;
    }

    .detail-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--orbo-text);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        padding: 9px 12px;
        background: var(--orbo-soft);
        border-radius: 10px;
        border: 1px solid var(--orbo-border);
        min-height: 42px;
        word-break: break-word;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }

    .status-active {
        background: rgba(255, 177, 1, 0.16);
        color: #9a6900;
    }

    .status-returned {
        background: rgba(236, 78, 116, 0.12);
        color: var(--orbo-coral);
    }

    .btn-orb {
        background: linear-gradient(135deg, var(--orbo-primary), var(--orbo-purple));
        color: white !important;
        border-radius: 50px;
        padding: 9px 22px;
        font-weight: 600;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 8px 18px rgba(75, 0, 232, 0.18);
    }

    .btn-orb:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(75, 0, 232, 0.24);
    }

    .asset-show-page .btn-light {
        border: 1px solid var(--orbo-border);
        color: var(--orbo-primary);
        font-weight: 600;
    }

    .asset-show-page .badge-light {
        background: #fff !important;
        border-color: rgba(134, 0, 238, 0.18) !important;
        color: var(--orbo-primary);
        font-weight: 600;
        padding: 6px 10px;
        border-radius: 50px;
    }

    .asset-show-page .text-muted i,
    .asset-show-page .detail-value i {
        color: var(--orbo-purple) !important;
    }

    .asset-summary-title {
        color: var(--orbo-text);
        font-size: 1.35rem;
    }

    .asset-summary-subtitle {
        font-size: 0.9rem;
    }

    .asset-card-body {
        padding: 28px;
    }

    .asset-detail-item {
        margin-bottom: 18px;
    }

    @media (max-width: 991.98px) {
        .asset-show-page {
            padding-left: 18px !important;
            padding-right: 18px !important;
        }

        .asset-show-page .text-lg-right .btn {
            margin-top: 8px;
        }
    }

    @media (max-width: 575.98px) {
        .asset-show-page {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .card-gradient-header {
            padding: 20px;
        }

        .header-icon {
            font-size: 2.4rem;
            right: 18px;
        }

        .asset-card-body {
            padding: 20px;
        }

        .asset-show-page .btn {
            width: 100%;
            margin-right: 0 !important;
            margin-bottom: 8px;
        }

        .asset-summary-title {
            font-size: 1.2rem;
        }
    }
</style>

@php
    $employeeName = $assetAllocation->employee->user->name
        ?? ($assetAllocation->employee->employeeDetail->name ?? 'Unknown Employee');
    $issueDate = $assetAllocation->issue_date ?? $assetAllocation->assigned_date ?? null;
    $idCardOptions = $assetAllocation->id_card_options;

    if (is_string($idCardOptions)) {
        $decodedOptions = json_decode($idCardOptions, true);
        $idCardOptions = is_array($decodedOptions) ? $decodedOptions : [];
    }

    if (! is_array($idCardOptions)) {
        $idCardOptions = [];
    }
@endphp

<div class="container-fluid py-4 px-4 asset-show-page">
    <div class="row mb-4 align-items-center">
        <div class="col-lg-6">
            <h3 class="font-weight-bold mb-1 asset-summary-title">Asset Allocation Detail</h3>
            <p class="text-muted m-0 asset-summary-subtitle">View assigned asset information</p>
        </div>
        <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
            <a href="{{ route('hrms.assets.index') }}" class="btn btn-light shadow-sm mr-2" style="border-radius: 50px;">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
            <a href="{{ route('hrms.assets.edit', $assetAllocation->id) }}" class="btn btn-orb">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card custom-card">
                <div class="card-gradient-header">
                    <div class="header-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <h2 class="font-weight-bold mb-1">{{ $assetAllocation->asset_type ?? 'Asset' }}</h2>
                    <p class="mb-0 text-white-50">
                        Assigned to <span class="text-white">{{ $employeeName }}</span>
                    </p>
                </div>

                <div class="card-body asset-card-body">
                    <div class="row">
                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Employee</label>
                            <div class="detail-value">
                                <i class="fas fa-user-circle mr-2 text-muted"></i> {{ $employeeName }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Status</label>
                            <div class="detail-value">
                                @if ($assetAllocation->status == 'Active')
                                    <span class="status-badge status-active">Active</span>
                                @else
                                    <span class="status-badge status-returned">{{ $assetAllocation->status ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Asset Type</label>
                            <div class="detail-value">
                                <i class="fas fa-box mr-2 text-muted"></i> {{ $assetAllocation->asset_type ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Asset ID / Serial No.</label>
                            <div class="detail-value">
                                {{ $assetAllocation->asset_id_sn ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Brand / Model</label>
                            <div class="detail-value">
                                {{ $assetAllocation->brand_model ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Condition</label>
                            <div class="detail-value">
                                {{ $assetAllocation->condition ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Issue Date</label>
                            <div class="detail-value">
                                <i class="far fa-calendar-alt mr-2 text-muted"></i>
                                {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('d M Y') : 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Mobile / SIM Number</label>
                            <div class="detail-value">
                                {{ $assetAllocation->mobile_sim_number ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Plan Details</label>
                            <div class="detail-value">
                                {{ $assetAllocation->plan_details ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">SIM Details</label>
                            <div class="detail-value">
                                {{ $assetAllocation->sim_details ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">Accessories</label>
                            <div class="detail-value">
                                @if ($assetAllocation->has_charger)
                                    <span class="badge badge-light border mr-2">Charger</span>
                                @endif
                                @if ($assetAllocation->has_bag)
                                    <span class="badge badge-light border mr-2">Bag</span>
                                @endif
                                @if (! $assetAllocation->has_charger && ! $assetAllocation->has_bag)
                                    N/A
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 asset-detail-item">
                            <label class="detail-label">ID Card Options</label>
                            <div class="detail-value">
                                @forelse ($idCardOptions as $option)
                                    <span class="badge badge-light border mr-2">{{ $option }}</span>
                                @empty
                                    N/A
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
