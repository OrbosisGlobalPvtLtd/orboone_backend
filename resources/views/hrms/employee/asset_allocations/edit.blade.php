@extends('layouts.admin', ['accesses' => $accesses ?? [], 'active' => 'data'])

@section('_content')
<style>
    :root {
        --primary-orb: #1560ab;
        --secondary-orb: #0099cc;
    }

    .custom-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        background: white;
        overflow: hidden;
    }

    .btn-orb {
        background: linear-gradient(135deg, var(--primary-orb), var(--secondary-orb));
        color: white !important;
        border-radius: 50px;
        padding: 10px 30px;
        font-weight: 600;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(21, 96, 171, 0.2);
    }

    .btn-orb:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(21, 96, 171, 0.3);
    }

.form-control {
    border-radius: -175px;
    padding: 9px 15px;
    border: 1px solid #e3e6f0;
    font-size: 0.9rem;
}

    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(21, 96, 171, 0.25);
        border-color: var(--primary-orb);
    }

    label {
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }
</style>

<div class="container-fluid py-4 px-4">
    <!-- Page Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-6">
            <h3 class="font-weight-bold text-dark mb-1">Edit Asset Allocation</h3>
            <p class="text-muted m-0">Update the details of an existing asset allocation</p>
        </div>
        <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
            <a href="{{ route('hrms.assets.index') }}" class="btn btn-light shadow-sm" style="border-radius: 50px;">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4 rounded" style="border-radius: 15px !important;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle mr-2"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Main Content Card -->
            <div class="card custom-card">
                <div class="card-body p-5">
                    <form action="{{ route('hrms.assets.update', $assetAllocation->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-4 mb-md-0">
                                <div class="form-group">
                                    <label for="employee_id">Select Employee <span class="text-danger">*</span></label>
                                    <select name="employee_id" id="employee_id" class="form-control" required>
                                        <option value="" disabled>-- Select an Employee --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" {{ (old('employee_id') ?? $assetAllocation->employee_id) == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->user->name ?? ($emp->employeeDetail->name ?? 'Employee #'.$emp->id) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                                    <select name="asset_type" id="asset_type" class="form-control @error('asset_type') is-invalid @enderror" onchange="toggleAssetFields()" required>
                                        <option value="" disabled>-- Select Asset Type --</option>
                                        <option value="Laptop" {{ (old('asset_type') ?? $assetAllocation->asset_type) == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                        <option value="Mobile" {{ (old('asset_type') ?? $assetAllocation->asset_type) == 'Mobile' ? 'selected' : '' }}>Mobile</option>
                                        <option value="SIM Card" {{ (old('asset_type') ?? $assetAllocation->asset_type) == 'SIM Card' ? 'selected' : '' }}>SIM Card</option>
                                        <option value="ID Card" {{ (old('asset_type') ?? $assetAllocation->asset_type) == 'ID Card' ? 'selected' : '' }}>ID Card</option>
                                        <option value="Other" {{ (old('asset_type') ?? $assetAllocation->asset_type) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('asset_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- DYNAMIC SECTIONS -->
                        <div id="asset_fields" class="dynamic-section mb-4 p-4 bg-light rounded" style="display:none; border: 1px solid #e3e6f0;">
                            <h5 class="font-weight-bold text-info mb-3"><i class="fas fa-laptop-medical mr-2"></i> Asset Setup Details</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3" id="asset_brand_field">
                                    <label class="form-label">Brand / Model <span class="text-danger">*</span></label>
                                    <input type="text" name="brand_model" id="brand_model" class="form-control" placeholder="e.g. Dell XPS 15" value="{{ old('brand_model') ?? $assetAllocation->brand_model }}">
                                </div>
                                <div class="col-md-4 mb-3" id="asset_sn_field">
                                    <label class="form-label">Asset ID / Serial No. <span class="text-danger">*</span></label>
                                    <input type="text" name="asset_id_sn" id="asset_id_sn" class="form-control" placeholder="e.g. SN-98765" value="{{ old('asset_id_sn') ?? $assetAllocation->asset_id_sn }}">
                                </div>
                                <div class="col-md-4 mb-3" id="asset_condition_field">
                                    <label class="form-label">Condition <span class="text-danger">*</span></label>
                                    <select name="condition" id="condition" class="form-control">
                                        <option value="New" {{ (old('condition') ?? $assetAllocation->condition) == 'New' ? 'selected' : '' }}>New</option>
                                        <option value="Used" {{ (old('condition') ?? $assetAllocation->condition) == 'Used' ? 'selected' : '' }}>Used</option>
                                        <option value="Refurbished" {{ (old('condition') ?? $assetAllocation->condition) == 'Refurbished' ? 'selected' : '' }}>Refurbished</option>
                                        <option value="Damaged" {{ (old('condition') ?? $assetAllocation->condition) == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                                    </select>
                                </div>

                                <!-- Mobile / SIM conditional fields -->
                                <div class="col-md-6 mb-3" id="mobile_sim_field" style="display:none;">
                                    <label class="form-label">Mobile / SIM Number <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_sim_number" id="mobile_sim_number" class="form-control" placeholder="+91 XXXXX XXXXX" value="{{ old('mobile_sim_number') ?? $assetAllocation->mobile_sim_number }}">
                                </div>
                                <div class="col-md-6 mb-3" id="plan_details_field" style="display:none;">
                                    <label class="form-label">Plan Details</label>
                                    <input type="text" name="plan_details" id="plan_details" class="form-control" placeholder="e.g. Corporate 5G Unlimited" value="{{ old('plan_details') ?? $assetAllocation->plan_details }}">
                                </div>

                                @php 
                                    $idOptions = old('id_card_options') ?? (is_string($assetAllocation->id_card_options) ? json_decode($assetAllocation->id_card_options, true) : []);
                                    if(!is_array($idOptions)) $idOptions = [];
                                @endphp
                                <!-- ID Card conditional fields -->
                                <div class="col-md-12 mb-3" id="id_card_field" style="display:none;">
                                    <label class="form-label">ID Card Options <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3 flex-wrap bg-white p-3 border rounded shadow-sm">
                                        <div class="form-check mr-4">
                                            <input class="form-check-input" type="checkbox" name="id_card_options[]" value="RFID Access" id="rfid_access" {{ in_array('RFID Access', $idOptions) ? 'checked' : '' }}>
                                            <label class="form-check-label font-weight-bold ml-1 cursor-pointer" for="rfid_access">RFID Access Room</label>
                                        </div>
                                        <div class="form-check mr-4">
                                            <input class="form-check-input" type="checkbox" name="id_card_options[]" value="Biometric" id="biometric" {{ in_array('Biometric', $idOptions) ? 'checked' : '' }}>
                                            <label class="form-check-label font-weight-bold ml-1 cursor-pointer" for="biometric">Biometric Sync</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="id_card_options[]" value="Visual ID Only" id="visual_id" {{ in_array('Visual ID Only', $idOptions) ? 'checked' : '' }}>
                                            <label class="form-check-label font-weight-bold ml-1 cursor-pointer" for="visual_id">Visual Print Only</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accessories Checkboxes -->
                                <div class="col-md-12 mb-3" id="accessories_field">
                                    <label class="form-label">Accessories Included</label>
                                    <div class="d-flex align-items-center mt-2">
                                        <div class="custom-control custom-switch mr-4">
                                            <input type="checkbox" class="custom-control-input border" id="has_charger" name="has_charger" value="1" {{ old('has_charger') || $assetAllocation->has_charger ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="has_charger">Charger</label>
                                        </div>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input border" id="has_bag" name="has_bag" value="1" {{ old('has_bag') || $assetAllocation->has_bag ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="has_bag">Laptop Bag</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                                    <input type="date" name="issue_date" id="issue_date" class="form-control" value="{{ old('issue_date') ?? (\Carbon\Carbon::parse($assetAllocation->issue_date ?? $assetAllocation->assigned_date ?? now())->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Asset Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="Active" {{ (old('status') ?? $assetAllocation->status) == 'Active' ? 'selected' : '' }}>Assigned (Active)</option>
                                        <option value="Returned" {{ (old('status') ?? $assetAllocation->status) == 'Returned' ? 'selected' : '' }}>Returned</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-orb">
                                <i class="fas fa-save mr-2"></i> Update Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function toggleAssetFields() {
        const type = document.getElementById('asset_type').value;
        const mainSection = document.getElementById('asset_fields');
        const mobileSim = document.getElementById('mobile_sim_field');
        const planDetails = document.getElementById('plan_details_field');
        const idCard = document.getElementById('id_card_field');
        const standardFields = ['asset_brand_field', 'asset_condition_field', 'accessories_field'];
        const snField = document.getElementById('asset_sn_field');
        
        document.getElementById('brand_model').required = false;
        document.getElementById('asset_id_sn').required = false;
        document.getElementById('mobile_sim_number').required = false;

        if (type) {
            mainSection.style.display = 'block';
            
            standardFields.forEach(id => document.getElementById(id).style.display = 'block');
            snField.style.display = 'block';
            mobileSim.style.display = 'none';
            planDetails.style.display = 'none';
            idCard.style.display = 'none';
            
            if (type === 'Mobile' || type === 'SIM Card') {
                mobileSim.style.display = 'block';
                planDetails.style.display = 'block';
                document.getElementById('mobile_sim_number').required = true;
                
                if (type === 'SIM Card') {
                    standardFields.forEach(id => document.getElementById(id).style.display = 'none');
                } else {
                    document.getElementById('brand_model').required = true;
                    document.getElementById('asset_id_sn').required = true;
                }
            } else if (type === 'ID Card') {
                standardFields.forEach(id => document.getElementById(id).style.display = 'none');
                idCard.style.display = 'block';
                document.getElementById('asset_id_sn').required = true;
            } else {
                document.getElementById('brand_model').required = true;
                document.getElementById('asset_id_sn').required = true;
            }
        } else {
            mainSection.style.display = 'none';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('asset_type').value) {
            toggleAssetFields();
        }
    });
</script>
@endsection
