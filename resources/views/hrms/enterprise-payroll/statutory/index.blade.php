@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
<style>
    .statutory-nav-tabs,
    .statutory-nav-tabs.nav-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 24px;
        border: none;
    }
    .statutory-tab-btn,
    .statutory-tab-btn.nav-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px !important;
        background: #ffffff !important;
        border: 1px solid var(--ep-border) !important;
        color: var(--ep-muted) !important;
        font-weight: 700;
        font-size: 13px;
        text-decoration: none !important;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }
    .statutory-tab-btn:hover,
    .statutory-tab-btn.nav-link:hover {
        background: #f8fafc !important;
        color: var(--ep-primary) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
    }
    .statutory-tab-btn.active,
    .statutory-tab-btn.nav-link.active {
        background: linear-gradient(135deg, var(--ep-primary), #6366f1) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25) !important;
    }
    .statutory-tab-btn.active i,
    .statutory-tab-btn.nav-link.active i {
        color: #ffffff !important;
    }
    .ep-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .ep-badge-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .ep-badge-secondary { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
    .ep-badge-info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .ep-badge-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

    .ep-checkbox-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        background: #f8fafc;
        height: 100%;
        transition: all 0.15s ease;
    }
    .ep-checkbox-card:hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    .ep-checkbox-card .custom-control-label {
        font-weight: 700;
        color: #1e293b;
        cursor: pointer;
    }

    .ep-json-code {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 12px;
        min-height: 110px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
    }

    .ep-statutory-table {
        font-size: 13px;
        margin-bottom: 0;
    }
    .ep-statutory-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        border-top: none;
    }
    .ep-statutory-table td {
        vertical-align: middle;
    }

    /* Modern Form Controls */
    .ep-form-control-modern {
        height: 44px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        background: #ffffff !important;
        padding: 8px 14px !important;
        transition: all 0.2s ease !important;
    }
    .ep-form-control-modern:focus {
        border-color: #4B00E8 !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1) !important;
        outline: none !important;
    }

    /* Fixed percentage input pill: number and % side-by-side in one unified sleek input */
    .ep-percent-input {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        background: #ffffff !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        height: 44px !important;
        transition: all 0.2s ease !important;
    }
    .ep-percent-input:focus-within {
        border-color: #4B00E8 !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1) !important;
    }
    .ep-percent-input input.ep-percent-field {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        width: 100% !important;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 16px !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        height: 100% !important;
        margin: 0 !important;
    }
    .ep-percent-input input.ep-percent-field:focus {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }
    .ep-percent-symbol {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 16px !important;
        background: #f8fafc !important;
        color: #64748b !important;
        font-weight: 800 !important;
        font-size: 15px !important;
        border-left: 1.5px solid #e2e8f0 !important;
        user-select: none !important;
        flex-shrink: 0 !important;
        height: 100% !important;
    }

    /* Dynamic Visual Allocation Progress Bar */
    .ep-split-progress-bar {
        display: flex;
        width: 100%;
        height: 28px;
        border-radius: 10px;
        overflow: hidden;
        background: #f1f5f9;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
    }
    .ep-progress-bar-basic {
        background: linear-gradient(135deg, #4338ca, #6366f1);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.3s ease;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .ep-progress-bar-hra {
        background: linear-gradient(135deg, #059669, #10b981);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.3s ease;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .ep-progress-bar-special {
        background: linear-gradient(135deg, #7e22ce, #a855f7);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.3s ease;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }

    /* Component breakdown pills in table */
    .ep-split-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        margin-right: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .ep-split-basic {
        background: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
    }
    .ep-split-hra {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .ep-split-special {
        background: #faf5ff;
        color: #6b21a8;
        border: 1px solid #e9d5ff;
    }

    .ep-btn-edit-rule {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        background: #ffffff;
        border: 1.5px solid var(--ep-border);
        color: var(--ep-primary);
        transition: all 0.15s ease;
        text-decoration: none !important;
        cursor: pointer;
    }
    .ep-btn-edit-rule:hover {
        background: var(--ep-soft);
        color: var(--ep-primary);
        border-color: rgba(75, 0, 232, 0.4);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(75, 0, 232, 0.1);
    }
</style>
@endsection

@section('_content')
<div class="ep-page">
    {{-- Hero Header --}}
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-sliders-h"></i> Enterprise Payroll</div>
            <h1>Statutory Compliance & Tax Rules</h1>
            <p>Manage CTC salary component breakdown, employee statutory eligibility (PF, ESI, PT, TDS), and annual tax profiles.</p>
        </div>
       
    </div>

    @include('hrms.enterprise-payroll.partials.flash')
    @if($errors->any())
        <div class="alert alert-danger shadow-sm border-0 mb-4" style="border-radius: 14px;">
            <div class="d-flex align-items-center mb-1">
                <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>
                <strong>Please correct the following errors:</strong>
            </div>
            <ul class="mb-0 pl-4 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Explanatory Banner --}}
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 14px; background: #eff6ff; color: #1e40af;">
        <i class="fas fa-shield-alt fa-2x mr-3 text-primary"></i>
        <div class="small">
            <strong class="d-block mb-1" style="font-size: 13px;">Dual-Verification Statutory Architecture:</strong>
            Statutory deductions (PF, ESI, Professional Tax, TDS) are calculated only when <strong>both</strong> the company-wide policy and the employee's individual eligibility are enabled. Income Tax TDS requires an active financial-year tax profile verified by HR.
        </div>
    </div>

    {{-- Modern Tab Navigation --}}
    <div class="nav nav-pills statutory-nav-tabs" id="statutoryTabs" role="tablist">
        <a class="nav-link statutory-tab-btn active" id="tab-components-btn" data-toggle="pill" href="#tab-components" role="tab" aria-controls="tab-components" aria-selected="true">
            <i class="fas fa-layer-group"></i> 1. CTC Salary Components
        </a>
        <a class="nav-link statutory-tab-btn" id="tab-eligibility-btn" data-toggle="pill" href="#tab-eligibility" role="tab" aria-controls="tab-eligibility" aria-selected="false">
            <i class="fas fa-user-shield"></i> 2. Employee Statutory Eligibility
        </a>
        <a class="nav-link statutory-tab-btn" id="tab-taxprofile-btn" data-toggle="pill" href="#tab-taxprofile" role="tab" aria-controls="tab-taxprofile" aria-selected="false">
            <i class="fas fa-file-invoice-dollar"></i> 3. Income Tax (TDS) Profiles
        </a>
        <a class="nav-link statutory-tab-btn" id="tab-taxrules-btn" data-toggle="pill" href="#tab-taxrules" role="tab" aria-controls="tab-taxrules" aria-selected="false">
            <i class="fas fa-calculator"></i> 4. FY Tax Slabs & Rules
        </a>
        <a class="nav-link statutory-tab-btn" id="tab-ptslabs-btn" data-toggle="pill" href="#tab-ptslabs" role="tab" aria-controls="tab-ptslabs" aria-selected="false">
            <i class="fas fa-landmark"></i> 5. Professional Tax Slabs
        </a>
    </div>

    {{-- Tab Content Panes --}}
    <div class="tab-content">

        {{-- PANE 1: CTC Salary Components --}}
        <div class="tab-pane fade show active" id="tab-components" role="tabpanel">
            <div class="ep-card mb-4">
                <div class="ep-table-header">
                    <div class="ep-table-head-left">
                        <div class="ep-icon-box"><i class="fas fa-layer-group"></i></div>
                        <div>
                            <h5 class="ep-table-title">CTC Salary Component Distribution</h5>
                            <p class="ep-table-subtitle">Define standard percentages to split annual CTC into Basic, HRA, and Special Allowance for new or revised structures.</p>
                        </div>
                    </div>
                </div>
                <div class="ep-card-body">
                    <form method="POST" action="{{ route('enterprise-payroll.statutory.salary-components') }}" class="ep-form" id="componentForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark small text-uppercase mb-1">Policy Scope</label>
                                <select name="policy_id" id="comp_policy_id" class="form-control ep-form-control-modern select2-searchable">
                                    <option value="">Global Default (All Policies)</option>
                                    @foreach($policies as $policy)
                                        <option value="{{ $policy->id }}">{{ $policy->policy_name }} (#{{ $policy->id }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Rule applies globally or to a specific policy.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark small text-uppercase mb-1">Effective From Date</label>
                                <x-form.date-picker name="effective_from" id="comp_effective_from" :value="now()->toDateString()" required="required" placeholder="dd-mm-yyyy" class="ep-form-control-modern" />
                                <small class="text-muted d-block mt-1">New or revised salary structures from this date will use this split.</small>
                            </div>
                        </div>

                        <div class="row pt-2 border-top">
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-dark small text-uppercase mb-1">Basic Salary (%)</label>
                                <div class="ep-percent-input">
                                    <input type="number" id="basic_pct" name="basic_percentage" min="0" max="100" step="0.01" class="ep-percent-field" value="50" required>
                                    <span class="ep-percent-symbol">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Recommended minimum: 50%</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-dark small text-uppercase mb-1">House Rent Allowance - HRA (%)</label>
                                <div class="ep-percent-input">
                                    <input type="number" id="hra_pct" name="hra_percentage" min="0" max="100" step="0.01" class="ep-percent-field" value="20" required>
                                    <span class="ep-percent-symbol">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Standard: 20% to 50%</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-dark small text-uppercase mb-1">Special Allowance (%)</label>
                                <div class="ep-percent-input">
                                    <input type="number" id="special_pct" name="special_allowance_percentage" min="0" max="100" step="0.01" class="ep-percent-field" value="30" required>
                                    <span class="ep-percent-symbol">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Balancing allowance to reach 100%</small>
                            </div>
                        </div>

                        {{-- Dynamic Visual Split Progress Bar --}}
                        <div class="mb-3 p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small font-weight-bold text-dark"><i class="fas fa-chart-pie mr-1 text-primary"></i> Live Split Allocation Preview:</span>
                                <span id="total_pct_badge" class="ep-badge-pill ep-badge-success font-weight-bold px-3 py-1">
                                    <i class="fas fa-check-circle"></i> 100% (Valid Split)
                                </span>
                            </div>
                            <div class="ep-split-progress-bar">
                                <div class="ep-progress-bar-basic" id="progress_basic" style="width: 50%;" title="Basic Salary">50% Basic</div>
                                <div class="ep-progress-bar-hra" id="progress_hra" style="width: 20%;" title="HRA">20% HRA</div>
                                <div class="ep-progress-bar-special" id="progress_special" style="width: 30%;" title="Special Allowance">30% Special</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end pt-2 border-top">
                            <button type="submit" class="ep-btn ep-btn-gradient" id="save_component_btn">
                                <i class="fas fa-save mr-1"></i> Save Component Rule
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-history mr-1 text-primary"></i> Active Salary Component Distribution Rules</h6>
                            <span class="badge badge-light text-muted">Auto-applied to new structures</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover ep-statutory-table">
                                <thead>
                                    <tr>
                                        <th>Policy Scope</th>
                                        <th>CTC Component Breakdown</th>
                                        <th class="text-center">Total Allocation</th>
                                        <th>Effective Period</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $groupedRules = $componentRules->groupBy(function($item) {
                                            return ($item->policy_id ?? 'global') . '_' . ($item->effective_from ?? 'default');
                                        });
                                    @endphp
                                    @forelse($groupedRules as $group)
                                    @php
                                        $first = $group->first();
                                        $policyId = $first->policy_id;
                                        $policyObj = $policyId ? $policies->firstWhere('id', $policyId) : null;
                                        $policyTitle = $policyObj ? $policyObj->policy_name : 'Global Default (All Policies)';
                                        $basic = $group->firstWhere('component_code', 'basic');
                                        $hra = $group->firstWhere('component_code', 'hra');
                                        $special = $group->firstWhere('component_code', 'special_allowance');
                                        $basicPct = $basic ? (float) $basic->percentage : 0;
                                        $hraPct = $hra ? (float) $hra->percentage : 0;
                                        $specialPct = $special ? (float) $special->percentage : 0;
                                        $totalPct = $basicPct + $hraPct + $specialPct;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $policyTitle }}</div>
                                            <small class="text-muted">{{ $policyId ? 'Policy #' . $policyId : 'Applies to unassigned policies' }}</small>
                                        </td>
                                        <td>
                                            <span class="ep-split-pill ep-split-basic"><i class="fas fa-coins mr-1"></i> Basic: {{ number_format($basicPct, 1) }}%</span>
                                            <span class="ep-split-pill ep-split-hra"><i class="fas fa-home mr-1"></i> HRA: {{ number_format($hraPct, 1) }}%</span>
                                            <span class="ep-split-pill ep-split-special"><i class="fas fa-gift mr-1"></i> Special: {{ number_format($specialPct, 1) }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="ep-badge-pill ep-badge-success font-weight-bold">
                                                <i class="fas fa-check-circle"></i> {{ number_format($totalPct, 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-muted small">
                                            @if($first->effective_from)
                                                {{ \Carbon\Carbon::parse($first->effective_from)->format('d M Y') }} to {{ $first->effective_to ? \Carbon\Carbon::parse($first->effective_to)->format('d M Y') : 'Ongoing' }}
                                            @else
                                                <span class="ep-badge-pill ep-badge-secondary">Standard / Permanent</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="ep-badge-pill ep-badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="ep-btn-edit-rule" onclick="loadComponentRule('{{ $policyId ?? '' }}', '{{ $first->effective_from ?? now()->toDateString() }}', {{ $basicPct }}, {{ $hraPct }}, {{ $specialPct }})">
                                                <i class="fas fa-pencil-alt"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No salary component rules configured.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANE 2: Employee Statutory Eligibility --}}
        <div class="tab-pane fade" id="tab-eligibility" role="tabpanel">
            <div class="row">
                <div class="col-lg-5 mb-4">
                    <div class="ep-card h-100">
                        <div class="ep-table-header">
                            <div class="ep-table-head-left">
                                <div class="ep-icon-box"><i class="fas fa-user-edit"></i></div>
                                <div>
                                    <h5 class="ep-table-title">Configure Employee Eligibility</h5>
                                    <p class="ep-table-subtitle">Set statutory deductions and overrides for a specific employee.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ep-card-body">
                            <form method="POST" action="{{ route('enterprise-payroll.statutory.employee') }}" class="ep-form" id="eligibilityForm">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase mb-1">Select Employee</label>
                                    <select name="employee_id" id="eligibility_employee_id" class="form-control ep-form-control-modern select2-searchable" required>
                                        <option value="">-- Choose Employee --</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->display_name }} ({{ $employee->employee_code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Effective From</label>
                                        <x-form.date-picker name="effective_from" :value="now()->toDateString()" required="required" placeholder="dd-mm-yyyy" class="ep-form-control-modern" />
                                    </div>
                                    <div class="col-6">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Jurisdiction Code</label>
                                        <input name="jurisdiction_code" class="form-control ep-form-control-modern" placeholder="e.g. MP or IN-MH">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6 mb-2">
                                        <div class="ep-checkbox-card">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="professional_tax_applicable" name="professional_tax_applicable" value="1" checked>
                                                <label class="custom-control-label" for="professional_tax_applicable">Professional Tax (PT)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="ep-checkbox-card">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="pf_applicable" name="pf_applicable" value="1">
                                                <label class="custom-control-label" for="pf_applicable">Provident Fund (PF)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="ep-checkbox-card">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="esi_applicable" name="esi_applicable" value="1">
                                                <label class="custom-control-label" for="esi_applicable">ESI Scheme</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="ep-checkbox-card">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="tds_applicable" name="tds_applicable" value="1">
                                                <label class="custom-control-label" for="tds_applicable">TDS (Income Tax)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">PF Ceiling Override (₹)</label>
                                        <input type="number" min="0" step="0.01" name="pf_wage_ceiling_override" class="form-control ep-form-control-modern" placeholder="Optional override">
                                    </div>
                                    <div class="col-6">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">ESI Ceiling Override (₹)</label>
                                        <input type="number" min="0" step="0.01" name="esi_wage_ceiling_override" class="form-control ep-form-control-modern" placeholder="Optional override">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase mb-1">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2" style="border-radius: 12px; border: 1.5px solid #e2e8f0;" placeholder="Optional compliance remarks"></textarea>
                                </div>

                                <button type="submit" class="ep-btn ep-btn-gradient w-100">
                                    <i class="fas fa-save mr-1"></i> Save Employee Eligibility
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Status Table of All Employees --}}
                <div class="col-lg-7 mb-4">
                    <div class="ep-card h-100">
                        <div class="ep-table-header">
                            <div class="ep-table-head-left">
                                <div class="ep-icon-box"><i class="fas fa-users-cog"></i></div>
                                <div>
                                    <h5 class="ep-table-title">Employee Statutory Roster ({{ $employees->count() }} Employees)</h5>
                                    <p class="ep-table-subtitle">Current active applicability status for all workforce members.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ep-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover ep-statutory-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th class="text-center">PT</th>
                                            <th class="text-center">PF</th>
                                            <th class="text-center">ESI</th>
                                            <th class="text-center">TDS</th>
                                            <th>Effective</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employees as $emp)
                                        @php
                                            $setting = $statutorySettings->get($emp->id);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $emp->display_name }}</div>
                                                <small class="text-muted">{{ $emp->employee_code }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if($setting && $setting->professional_tax_applicable)
                                                    <span class="ep-badge-pill ep-badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                                @else
                                                    <span class="ep-badge-pill ep-badge-secondary">Exempt</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($setting && $setting->pf_applicable)
                                                    <span class="ep-badge-pill ep-badge-success"><i class="fas fa-check-circle"></i> Yes</span>
                                                @else
                                                    <span class="ep-badge-pill ep-badge-secondary">No</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($setting && $setting->esi_applicable)
                                                    <span class="ep-badge-pill ep-badge-success"><i class="fas fa-check-circle"></i> Yes</span>
                                                @else
                                                    <span class="ep-badge-pill ep-badge-secondary">No</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($setting && $setting->tds_applicable)
                                                    <span class="ep-badge-pill ep-badge-info"><i class="fas fa-check-circle"></i> Yes</span>
                                                @else
                                                    <span class="ep-badge-pill ep-badge-secondary">No</span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">
                                                {{ $setting && $setting->effective_from ? \Carbon\Carbon::parse($setting->effective_from)->format('d M Y') : 'Default' }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">No active employees found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANE 3: Income Tax (TDS) Profiles --}}
        <div class="tab-pane fade" id="tab-taxprofile" role="tabpanel">
            <div class="row">
                <div class="col-lg-5 mb-4">
                    <div class="ep-card h-100">
                        <div class="ep-table-header">
                            <div class="ep-table-head-left">
                                <div class="ep-icon-box"><i class="fas fa-file-invoice-dollar"></i></div>
                                <div>
                                    <h5 class="ep-table-title">Employee Tax Profile & Declarations</h5>
                                    <p class="ep-table-subtitle">Annual Section 192 inputs for monthly TDS withholding calculations.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ep-card-body">
                            <form method="POST" action="{{ route('enterprise-payroll.statutory.tax-profile') }}" class="ep-form">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase mb-1">Select Employee</label>
                                    <select name="employee_id" class="form-control ep-form-control-modern select2-searchable" required>
                                        <option value="">-- Choose Employee --</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->display_name }} ({{ $employee->employee_code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Financial Year</label>
                                        <input type="number" name="financial_year_start" class="form-control ep-form-control-modern" value="{{ $financialYearStart }}" min="2000" max="2100" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Tax Regime</label>
                                        <select name="tax_regime" class="form-control ep-form-control-modern select2-searchable" required>
                                            <option value="new_regime">New Regime</option>
                                            <option value="old_regime">Old Regime</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Age Category</label>
                                        <select name="age_category" class="form-control ep-form-control-modern select2-searchable">
                                            <option value="all">Below 60 Years</option>
                                            <option value="senior">Senior (60–80)</option>
                                            <option value="super_senior">Super Senior (80+)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase mb-1">Residency Status</label>
                                    <select name="residency_status" class="form-control ep-form-control-modern select2-searchable">
                                        <option value="resident">Resident Indian</option>
                                        <option value="non_resident">Non-Resident Indian (NRI)</option>
                                        <option value="not_ordinary_resident">Not Ordinarily Resident</option>
                                    </select>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Prev Employer Income (₹)</label>
                                        <input type="number" min="0" step="0.01" name="previous_employer_income_annual" class="form-control ep-form-control-modern" value="0">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Prev Employer TDS (₹)</label>
                                        <input type="number" min="0" step="0.01" name="previous_employer_tds_annual" class="form-control ep-form-control-modern" value="0">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Other Taxable Income (₹)</label>
                                        <input type="number" min="0" step="0.01" name="other_taxable_income_annual" class="form-control ep-form-control-modern" value="0">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">80C/80D Deductions (₹)</label>
                                        <input type="number" min="0" step="0.01" name="deductions_annual" class="form-control ep-form-control-modern" value="0">
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Eligible Exemptions (HRA, etc.) (₹)</label>
                                        <input type="number" min="0" step="0.01" name="exemptions_annual" class="form-control ep-form-control-modern" value="0">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase mb-1">HR Verification Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2" style="border-radius: 12px; border: 1.5px solid #e2e8f0;" placeholder="Notes regarding tax declaration verification"></textarea>
                                </div>

                                <div class="ep-checkbox-card mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="tax_verified" name="verified" value="1" checked>
                                        <label class="custom-control-label" for="tax_verified">Tax declaration and proofs verified by HR</label>
                                    </div>
                                </div>

                                <button type="submit" class="ep-btn ep-btn-gradient w-100">
                                    <i class="fas fa-save mr-1"></i> Save Tax Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tax Profiles Table --}}
                <div class="col-lg-7 mb-4">
                    <div class="ep-card h-100">
                        <div class="ep-table-header">
                            <div class="ep-table-head-left">
                                <div class="ep-icon-box"><i class="fas fa-clipboard-check"></i></div>
                                <div>
                                    <h5 class="ep-table-title">Configured Tax Profiles (FY {{ $financialYearStart }}-{{ $financialYearStart + 1 }})</h5>
                                    <p class="ep-table-subtitle">Summary of employee tax regimes and verified declaration statuses.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ep-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover ep-statutory-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Tax Regime</th>
                                            <th class="text-right">Deductions</th>
                                            <th class="text-right">Prev. Income</th>
                                            <th class="text-center">Verification</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employees as $emp)
                                        @php
                                            $profile = $profiles->get($emp->id);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $emp->display_name }}</div>
                                                <small class="text-muted">{{ $emp->employee_code }}</small>
                                            </td>
                                            <td>
                                                @if($profile)
                                                    <span class="ep-badge-pill ep-badge-info">{{ ucwords(str_replace('_', ' ', $profile->tax_regime)) }}</span>
                                                @else
                                                    <span class="ep-badge-pill ep-badge-secondary">Not Configured</span>
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold text-dark">
                                                {{ $profile ? '₹' . number_format((float) $profile->deductions_annual, 2) : '-' }}
                                            </td>
                                            <td class="text-right text-muted">
                                                {{ $profile && (float) $profile->previous_employer_income_annual > 0 ? '₹' . number_format((float) $profile->previous_employer_income_annual, 2) : '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if($profile && $profile->verified_at)
                                                    <span class="ep-badge-pill ep-badge-success"><i class="fas fa-check-circle"></i> Verified</span>
                                                @elseif($profile)
                                                    <span class="ep-badge-pill ep-badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                                @else
                                                    <span class="ep-badge-pill ep-badge-secondary">No Profile</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No tax profiles found for this financial year.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANE 4: FY Tax Slabs & Rules --}}
        <div class="tab-pane fade" id="tab-taxrules" role="tabpanel">
            <div class="row">
                <div class="col-lg-5 mb-4">
                    <div class="ep-card h-100">
                        <div class="ep-table-header">
                            <div class="ep-table-head-left">
                                <div class="ep-icon-box"><i class="fas fa-calculator"></i></div>
                                <div>
                                    <h5 class="ep-table-title">Configure Income Tax Slabs & Parameters</h5>
                                    <p class="ep-table-subtitle">Set standard deduction, rebates, cess and tax bracket JSON for FY {{ $financialYearStart }}-{{ $financialYearStart + 1 }}.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ep-card-body">
                            <form method="POST" action="{{ route('enterprise-payroll.statutory.income-tax-rules') }}" class="ep-form">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">FY Starts</label>
                                        <input type="number" name="financial_year_start" class="form-control ep-form-control-modern" value="{{ $financialYearStart }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Tax Regime Key</label>
                                        <input name="tax_regime" id="regime_key_input" class="form-control ep-form-control-modern" placeholder="new_regime" value="new_regime" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Standard Deduction (₹)</label>
                                        <input type="number" min="0" step="0.01" name="standard_deduction" class="form-control ep-form-control-modern" value="75000">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Rebate Income Limit (₹)</label>
                                        <input type="number" min="0" step="0.01" name="rebate_income_limit" class="form-control ep-form-control-modern" value="700000">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Maximum Rebate (₹)</label>
                                        <input type="number" min="0" step="0.01" name="rebate_max_amount" class="form-control ep-form-control-modern" value="25000">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-1">Health & Edu Cess (%)</label>
                                        <input type="number" min="0" step="0.01" name="cess_percentage" class="form-control ep-form-control-modern" value="4">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="font-weight-bold small mb-0">Tax Slabs JSON</label>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0" onclick="loadDefaultTaxSlabs()">Load Template</button>
                                    </div>
                                    <textarea name="slabs_json" id="tax_slabs_json" class="form-control ep-json-code" required>[{"age_category":"all","income_from":0,"income_to":300000,"tax_rate":0},{"age_category":"all","income_from":300000,"income_to":700000,"tax_rate":5},{"age_category":"all","income_from":700000,"income_to":1000000,"tax_rate":10},{"age_category":"all","income_from":1000000,"income_to":1200000,"tax_rate":15},{"age_category":"all","income_from":1200000,"income_to":1500000,"tax_rate":20},{"age_category":"all","income_from":1500000,"income_to":null,"tax_rate":30}]</textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small text-muted mb-1">Surcharge Tiers JSON (Optional)</label>
                                    <textarea name="surcharges_json" class="form-control ep-json-code" placeholder='[{"income_threshold":5000000,"surcharge_rate":10,"marginal_relief":true}]'></textarea>
                                </div>

                                <div class="ep-checkbox-card mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="tax_rules_complete" name="is_complete" value="1" checked>
                                        <label class="custom-control-label" for="tax_rules_complete">All rules and slabs are complete for this regime</label>
                                    </div>
                                </div>

                                <button type="submit" class="ep-btn ep-btn-gradient w-100">
                                    <i class="fas fa-save mr-1"></i> Save Income Tax Rules
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 mb-4">
                    <div class="ep-card h-100">
                        <div class="ep-table-header">
                            <div class="ep-table-head-left">
                                <div class="ep-icon-box"><i class="fas fa-table"></i></div>
                                <div>
                                    <h5 class="ep-table-title">Configured Tax Rules · FY {{ $financialYearStart }}-{{ $financialYearStart + 1 }}</h5>
                                    <p class="ep-table-subtitle">Summary of income tax parameters and brackets currently active.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ep-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover ep-statutory-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Regime</th>
                                            <th>Standard Ded.</th>
                                            <th>Rebate Limit</th>
                                            <th>Cess</th>
                                            <th class="text-center">Slabs</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($taxRules as $rule)
                                        <tr>
                                            <td class="font-weight-bold text-dark">{{ ucwords(str_replace('_', ' ', $rule->tax_regime)) }}</td>
                                            <td>₹{{ number_format((float) $rule->standard_deduction, 2) }}</td>
                                            <td>₹{{ number_format((float) $rule->rebate_income_limit, 2) }}</td>
                                            <td>{{ $rule->cess_percentage }}%</td>
                                            <td class="text-center font-weight-bold">{{ $taxSlabs->where('tax_regime', $rule->tax_regime)->count() }} tiers</td>
                                            <td class="text-center">
                                                @if($rule->is_complete)
                                                    <span class="ep-badge-pill ep-badge-success"><i class="fas fa-check-circle"></i> Complete</span>
                                                @else
                                                    <span class="ep-badge-pill ep-badge-warning"><i class="fas fa-exclamation-circle"></i> Incomplete</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">No income tax rules configured for FY {{ $financialYearStart }}.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANE 5: Professional Tax (PT) Slabs --}}
        <div class="tab-pane fade" id="tab-ptslabs" role="tabpanel">
            <div class="ep-card">
                <div class="ep-table-header">
                    <div class="ep-table-head-left">
                        <div class="ep-icon-box"><i class="fas fa-landmark"></i></div>
                        <div>
                            <h5 class="ep-table-title">Active Professional Tax Slabs</h5>
                            <p class="ep-table-subtitle">Configured monthly gross tiers and March balancing amounts across jurisdictions.</p>
                        </div>
                    </div>
                    <div>
                        <a class="ep-btn ep-btn-gradient" href="{{ route('enterprise-payroll.policies.index') }}#pt-slabs-card">
                            <i class="fas fa-edit mr-1"></i> Open Visual Slabs Editor
                        </a>
                    </div>
                </div>
                <div class="ep-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover ep-statutory-table mb-0">
                            <thead>
                                <tr>
                                    <th>Jurisdiction</th>
                                    <th>Scope</th>
                                    <th>Monthly Gross Wage Range</th>
                                    <th class="text-center">Monthly Tax</th>
                                    <th class="text-center">12th Month Balancing</th>
                                    <th>Effective Period</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ptSlabs as $slab)
                                <tr>
                                    <td><span class="ep-badge-pill ep-badge-info font-weight-bold">{{ $slab->jurisdiction_code }}</span></td>
                                    <td>{{ optional($policies->firstWhere('id', $slab->policy_id))->policy_name ?? 'All Policies' }}</td>
                                    <td class="font-weight-bold">
                                        ₹{{ number_format((float) $slab->monthly_gross_min, 2) }}
                                        to
                                        {{ $slab->monthly_gross_max === null ? 'Above' : '₹' . number_format((float) $slab->monthly_gross_max, 2) }}
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        {{ (float) $slab->monthly_tax_amount > 0 ? '₹' . number_format((float) $slab->monthly_tax_amount, 2) . ' / mo' : 'Nil (₹0.00)' }}
                                    </td>
                                    <td class="text-center">
                                        @if($slab->balancing_month_amount !== null && (float) $slab->balancing_month_amount > 0)
                                            <strong class="text-primary">₹{{ number_format((float) $slab->balancing_month_amount, 2) }}</strong>
                                        @else
                                            <span class="text-muted">Same as regular</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $slab->effective_from }} – {{ $slab->effective_to ?: 'Open' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No active professional tax slabs configured.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('_script')
<script>
    (function() {
        // Component Percentage Live Validation
        var basic = document.getElementById('basic_pct');
        var hra = document.getElementById('hra_pct');
        var special = document.getElementById('special_pct');
        var badge = document.getElementById('total_pct_badge');
        var saveBtn = document.getElementById('save_component_btn');

        function checkComponentTotal() {
            if (!basic || !hra || !special || !badge) return;
            var b = parseFloat(basic.value) || 0;
            var h = parseFloat(hra.value) || 0;
            var s = parseFloat(special.value) || 0;
            var total = Math.round((b + h + s) * 100) / 100;

            var pBasic = document.getElementById('progress_basic');
            var pHra = document.getElementById('progress_hra');
            var pSpecial = document.getElementById('progress_special');

            if (pBasic) {
                pBasic.style.width = Math.max(0, Math.min(100, b)) + '%';
                pBasic.textContent = b > 0 ? b + '% Basic' : '';
            }
            if (pHra) {
                pHra.style.width = Math.max(0, Math.min(100, h)) + '%';
                pHra.textContent = h > 0 ? h + '% HRA' : '';
            }
            if (pSpecial) {
                pSpecial.style.width = Math.max(0, Math.min(100, s)) + '%';
                pSpecial.textContent = s > 0 ? s + '% Special' : '';
            }

            if (Math.abs(total - 100) < 0.01) {
                badge.className = 'ep-badge-pill ep-badge-success font-weight-bold px-3 py-1';
                badge.innerHTML = '<i class="fas fa-check-circle mr-1"></i> 100% (Valid Split)';
                if (saveBtn) saveBtn.disabled = false;
            } else {
                badge.className = 'ep-badge-pill ep-badge-danger font-weight-bold px-3 py-1';
                badge.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> ' + total + '% (Must equal 100%)';
                if (saveBtn) saveBtn.disabled = true;
            }
        }

        [basic, hra, special].forEach(function(input) {
            if (input) input.addEventListener('input', checkComponentTotal);
        });
        checkComponentTotal();

        // Load component rule helper for Edit button
        window.loadComponentRule = function(policyId, effectiveFrom, basicPct, hraPct, specialPct) {
            var policySelect = document.getElementById('comp_policy_id');
            var effDate = document.getElementById('comp_effective_from');
            if (policySelect) policySelect.value = policyId || '';
            if (effDate) {
                effDate.value = effectiveFrom || '';
                if (effDate._flatpickr) {
                    effDate._flatpickr.setDate(effectiveFrom || '', true);
                }
            }
            if (basic) basic.value = basicPct || 50;
            if (hra) hra.value = hraPct || 20;
            if (special) special.value = specialPct || 30;
            checkComponentTotal();
            var formEl = document.getElementById('componentForm');
            if (formEl) {
                formEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        };

        // Income tax slabs template helper
        window.loadDefaultTaxSlabs = function() {
            var el = document.getElementById('tax_slabs_json');
            if (el) {
                el.value = JSON.stringify([
                    {"age_category":"all","income_from":0,"income_to":300000,"tax_rate":0},
                    {"age_category":"all","income_from":300000,"income_to":700000,"tax_rate":5},
                    {"age_category":"all","income_from":700000,"income_to":1000000,"tax_rate":10},
                    {"age_category":"all","income_from":1000000,"income_to":1200000,"tax_rate":15},
                    {"age_category":"all","income_from":1200000,"income_to":1500000,"tax_rate":20},
                    {"age_category":"all","income_from":1500000,"income_to":null,"tax_rate":30}
                ], null, 2);
            }
        };

        // Explicit robust tab switching handler
        var tabButtons = document.querySelectorAll('.statutory-tab-btn');
        tabButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                // 1. Remove active from all tabs
                tabButtons.forEach(function(b) {
                    b.classList.remove('active');
                    b.setAttribute('aria-selected', 'false');
                });

                // 2. Hide all panes
                document.querySelectorAll('.tab-content .tab-pane').forEach(function(pane) {
                    pane.classList.remove('show', 'active');
                });

                // 3. Activate clicked tab
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');

                // 4. Show target pane
                var targetId = this.getAttribute('href');
                if (targetId) {
                    var targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }
                }
            });
        });
    })();
</script>
@endsection
