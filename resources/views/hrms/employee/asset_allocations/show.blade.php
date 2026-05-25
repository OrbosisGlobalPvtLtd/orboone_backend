@extends('layouts.admin', ['accesses' => $accesses ?? [], 'active' => 'data'])

@section('_content')
@include('hrms.employee.partials.styles')

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
