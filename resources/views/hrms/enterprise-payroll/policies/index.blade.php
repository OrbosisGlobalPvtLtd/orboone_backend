@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
<style>
    .ep-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .ep-badge-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .ep-badge-secondary { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
    .ep-badge-info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .ep-badge-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .ep-ratio-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .ep-ratio-box .ep-ratio-label { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 2px; }
    .ep-ratio-box .ep-ratio-value { font-size: 16px; font-weight: 800; color: #1e293b; display: flex; align-items: center; justify-content: space-between; }
    .ep-table-slabs { font-size: 13px; margin-bottom: 0; }
    .ep-table-slabs th { background: #f8fafc; color: #475569; font-weight: 700; border-top: none; }
    .ep-table-slabs td { vertical-align: middle; }



    /* Currency input pill: rupee sign nicely beside the number */
    .ep-currency-input {
        display: flex !important;
        align-items: center !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        overflow: hidden !important;
        height: 38px !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }
    .ep-currency-input:focus-within {
        border-color: #4B00E8 !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
    }
    .ep-currency-symbol {
        padding: 0 12px !important;
        background: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        border-right: 1px solid #cbd5e1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 100% !important;
        user-select: none !important;
        flex-shrink: 0 !important;
    }
    .ep-currency-input input.ep-currency-field {
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        padding: 6px 12px !important;
        width: 100% !important;
        flex: 1 1 auto !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        background: transparent !important;
        height: 100% !important;
    }
</style>
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-building"></i> Enterprise Payroll</div>
            <h1>Payroll Policy Settings</h1>
            <p>Configure salary calculation rules, statutory deductions (PT, PF, ESI, TDS), payable ratios, and credit windows.</p>
        </div>
    </div>

    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-card">
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-cogs"></i></div>
                <div>
                    <h5 class="ep-table-title">Payroll Policy & Deduction Rules</h5>
                    <p class="ep-table-subtitle">Manage company salary calculation basis, statutory tax slabs, and attendance proration rules.</p>
                </div>
            </div>
            @if($canUpdate)
            <div class="ep-hero-actions">
                <a class="ep-btn ep-btn-outline" href="{{ route('enterprise-payroll.statutory.index') }}">
                    <i class="fas fa-sliders-h"></i> Employee Tax Profiles
                </a>
                <button class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#policyModal">
                    <i class="fas fa-edit"></i> Edit Policy Settings
                </button>
            </div>
            @endif
        </div>

        <div class="ep-card-body">
            <div class="row">
                {{-- Card 1: Salary Calculation Policy --}}
                <div class="col-lg-6 mb-3">
                    <div class="ep-section-card h-100">
                        <div class="ep-section-title"><i class="fas fa-calculator"></i> Salary Calculation Basis</div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block mb-1">Salary Day Basis</small>
                                <span class="ep-badge-pill ep-badge-info">
                                    <i class="fas fa-calendar-check"></i>
                                    {{ ucwords(str_replace('_', ' ', $policy->salary_day_basis)) }}
                                </span>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block mb-1">Working Day Mode</small>
                                <span class="ep-badge-pill ep-badge-secondary">
                                    <i class="fas fa-calendar-day"></i>
                                    {{ ucwords(str_replace('_', ' ', $policy->working_day_mode)) }}
                                </span>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block mb-1">Salary Proration Method</small>
                                <strong>{{ (int) ($policy->salary_day_base ?? 26) }}-Day Base / Calendar Days</strong>
                                <span class="text-muted d-block small font-italic mt-1">Formula: (Payable Days × 26) / Calendar Days</span>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block mb-1">Custom Fixed Days</small>
                                <strong>{{ $policy->custom_fixed_days ? $policy->custom_fixed_days . ' Days' : 'Not Configured (-)' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Statutory Deductions --}}
                <div class="col-lg-6 mb-3">
                    <div class="ep-section-card h-100">
                        <div class="ep-section-title"><i class="fas fa-university"></i> Statutory Deductions & Taxes</div>
                        <div class="row">
                            
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block mb-1">Professional Tax (PT)</small>
                                @if($policy->professional_tax_enabled)
                                    <span class="ep-badge-pill ep-badge-success mb-1">
                                        <i class="fas fa-check-circle"></i> Enabled (Policy On)
                                    </span>
                                    <div class="small text-dark font-weight-bold">Statutory Slabs Active</div>
                                    <div class="small text-muted">₹0 – ₹2,500/year · Balancing in March</div>
                                @else
                                    <span class="ep-badge-pill ep-badge-secondary">
                                        <i class="fas fa-times-circle"></i> Disabled
                                    </span>
                                @endif
                            </div>

                            {{-- Provident Fund (PF) --}}
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block mb-1">Provident Fund (PF)</small>
                                @if($policy->pf_enabled)
                                    <span class="ep-badge-pill ep-badge-success mb-1">
                                        <i class="fas fa-check-circle"></i> Enabled ({{ (float) $policy->pf_percentage }}%)
                                    </span>
                                    <div class="small text-muted">Basis: <strong>{{ ucfirst($policy->pf_wage_basis ?? 'basic') }}</strong></div>
                                    <div class="small text-muted">Ceiling: <strong>{{ $policy->pf_wage_ceiling ? '₹' . number_format($policy->pf_wage_ceiling, 2) : 'No Ceiling' }}</strong></div>
                                @else
                                    <span class="ep-badge-pill ep-badge-secondary">
                                        <i class="fas fa-times-circle"></i> Disabled (0%)
                                    </span>
                                @endif
                            </div>

                            {{-- Employee State Insurance (ESI) --}}
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block mb-1">Employee State Insurance (ESI)</small>
                                @if($policy->esi_enabled)
                                    <span class="ep-badge-pill ep-badge-success mb-1">
                                        <i class="fas fa-check-circle"></i> Enabled ({{ (float) $policy->esi_percentage }}%)
                                    </span>
                                    <div class="small text-muted">Basis: <strong>{{ ucfirst($policy->esi_wage_basis ?? 'gross') }}</strong></div>
                                    <div class="small text-muted">Ceiling: <strong>{{ $policy->esi_wage_ceiling ? '₹' . number_format($policy->esi_wage_ceiling, 2) : '₹21,000.00' }}</strong></div>
                                @else
                                    <span class="ep-badge-pill ep-badge-secondary">
                                        <i class="fas fa-times-circle"></i> Disabled (0%)
                                    </span>
                                @endif
                            </div>

                            {{-- TDS (Income Tax) --}}
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block mb-1">TDS (Income Tax)</small>
                                @if($policy->tds_enabled)
                                    <span class="ep-badge-pill ep-badge-success mb-1">
                                        <i class="fas fa-check-circle"></i> Enabled
                                    </span>
                                    <div class="small text-muted">Section 192 Projection</div>
                                    <div class="small text-muted">Requires Verified Tax Profile</div>
                                @else
                                    <span class="ep-badge-pill ep-badge-secondary">
                                        <i class="fas fa-times-circle"></i> Policy Off
                                    </span>
                                    <div class="small text-muted">No TDS Withholding</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Payable Ratios --}}
                <div class="col-lg-8 mb-3">
                    <div class="ep-section-card h-100">
                        <div class="ep-section-title"><i class="fas fa-percent"></i> Attendance & Payable Ratios</div>
                        <div class="row">
                            <div class="col-md-4 col-6 mb-2">
                                <div class="ep-ratio-box">
                                    <div class="ep-ratio-label">Half Day</div>
                                    <div class="ep-ratio-value">
                                        <span>{{ (float) $policy->half_day_payable_ratio }}</span>
                                        <span class="badge badge-info">{{ (float) $policy->half_day_payable_ratio * 100 }}% Pay</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="ep-ratio-box">
                                    <div class="ep-ratio-label">Absent Day</div>
                                    <div class="ep-ratio-value">
                                        <span>{{ (float) $policy->absent_payable_ratio }}</span>
                                        <span class="badge badge-danger">Unpaid</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="ep-ratio-box">
                                    <div class="ep-ratio-label">Leave Without Pay (LWP)</div>
                                    <div class="ep-ratio-value">
                                        <span>{{ (float) $policy->lwp_payable_ratio }}</span>
                                        <span class="badge badge-danger">Unpaid</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="ep-ratio-box">
                                    <div class="ep-ratio-label">Paid Leave (PL / SL)</div>
                                    <div class="ep-ratio-value">
                                        <span>{{ (float) $policy->paid_leave_payable_ratio }}</span>
                                        <span class="badge badge-success">100% Pay</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="ep-ratio-box">
                                    <div class="ep-ratio-label">Week Off</div>
                                    <div class="ep-ratio-value">
                                        <span>{{ (float) $policy->weekoff_payable_ratio }}</span>
                                        <span class="badge {{ $policy->include_weekoff_in_payable ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $policy->include_weekoff_in_payable ? 'Paid' : 'Excluded' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="ep-ratio-box">
                                    <div class="ep-ratio-label">Public Holiday</div>
                                    <div class="ep-ratio-value">
                                        <span>{{ (float) $policy->holiday_payable_ratio }}</span>
                                        <span class="badge {{ $policy->include_holiday_in_payable ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $policy->include_holiday_in_payable ? 'Paid' : 'Excluded' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Salary Credit Window & Operational Controls --}}
                <div class="col-lg-4 mb-3">
                    <div class="ep-section-card h-100">
                        <div class="ep-section-title"><i class="fas fa-clock"></i> Credit Window & Controls</div>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Monthly Salary Credit Window</small>
                            <span class="ep-badge-pill ep-badge-info font-weight-bold" style="font-size: 13px;">
                                <i class="fas fa-calendar-alt"></i> {{ $policy->salary_credit_start_day }}th to {{ $policy->salary_credit_end_day }}th of every month
                            </span>
                        </div>
                        <div class="row pt-2 border-top">
                            <div class="col-6">
                                <small class="text-muted d-block">Negative Salary</small>
                                <strong>{{ $policy->allow_negative_salary ? 'Allowed' : 'Disallowed (Min 0)' }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Lock On Generate</small>
                                <strong>{{ $policy->payroll_lock_after_generation ? 'Auto-Locked' : 'Draft / Manual' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 5: State Professional Tax (PT) Slabs Detail --}}
            <div class="ep-section-card mb-4" id="pt-slabs-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="ep-section-title mb-0">
                        <i class="fas fa-landmark text-primary"></i> Professional Tax (PT) Slabs
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Statutory Rules Active</span>
                        @if($canUpdate)
                        <button class="btn btn-sm btn-outline-primary ml-2 font-weight-bold" data-toggle="modal" data-target="#ptSlabsModal">
                            <i class="fas fa-edit"></i> Edit Slabs
                        </button>
                        @endif
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Configured monthly slab rates and applicable deductions. Month 12 (March) acts as the balancing reconciliation month.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover ep-table-slabs">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Monthly Gross Wage Range</th>
                                <th>Annual Wage Equivalent</th>
                                <th class="text-center">Regular Months (Apr – Feb)</th>
                                <th class="text-center">12th Month (March Balancing)</th>
                                <th class="text-center">Total Annual Tax</th>
                                <th class="text-center">Rule Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ptSlabs as $index => $slab)
                            <tr>
                                <td class="font-weight-bold text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>₹{{ number_format($slab->monthly_gross_min, 2) }}</strong>
                                    to
                                    <strong>{{ $slab->monthly_gross_max ? '₹' . number_format($slab->monthly_gross_max, 2) : 'Above' }}</strong>
                                </td>
                                <td class="text-muted">
                                    @if($slab->monthly_gross_max)
                                        Up to ₹{{ number_format($slab->monthly_gross_max * 12, 0) }}
                                    @else
                                        Above ₹4,00,000 / year
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">
                                    {{ (float) $slab->monthly_tax_amount > 0 ? '₹' . number_format($slab->monthly_tax_amount, 2) . ' / mo' : 'Nil (₹0.00)' }}
                                </td>
                                <td class="text-center font-weight-bold {{ $slab->balancing_month_amount != $slab->monthly_tax_amount && $slab->balancing_month_amount ? 'text-primary' : '' }}">
                                    @if($slab->balancing_month_amount !== null && (float) $slab->balancing_month_amount > 0)
                                        ₹{{ number_format($slab->balancing_month_amount, 2) }}
                                        @if($slab->balancing_month_amount != $slab->monthly_tax_amount)
                                            <span class="badge badge-primary ml-1">Balancing</span>
                                        @endif
                                    @else
                                        Nil (₹0.00)
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold text-dark">
                                    @php
                                        $annualTotal = ((float) $slab->monthly_tax_amount * 11) + ((float) ($slab->balancing_month_amount ?? $slab->monthly_tax_amount));
                                    @endphp
                                    {{ $annualTotal > 0 ? '₹' . number_format($annualTotal, 2) : 'Nil' }}
                                </td>
                                <td class="text-center">
                                    <span class="ep-badge-pill ep-badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No professional tax slabs configured.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canUpdate)
{{-- Edit Policy Modal --}}
<div class="modal fade" id="policyModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('enterprise-payroll.policies.update') }}" class="modal-content ep-form border-0 shadow-lg ep-modal-content-scrollable">
            @csrf
            <div class="ep-modal-header">
                <div>
                    <h5 class="modal-title font-weight-bold">Edit Payroll Policy Settings</h5>
                    <p class="text-muted small mb-0">Update calculation basis, working-day modes, statutory parameters and credit windows.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body p-4">

                {{-- Section 1: Salary Basis & Proration --}}
                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-calculator text-primary"></i> 1. Salary Calculation & Proration</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label class="font-weight-bold">Salary Day Basis</label>
                                <select name="salary_day_basis" id="salary_day_basis" class="form-control select2-searchable" required>
                                    <option value="working_days" @selected(old('salary_day_basis', $policy->salary_day_basis ?? 'working_days') === 'working_days')>Working Days</option>
                                    <option value="calendar_days" @selected(old('salary_day_basis', $policy->salary_day_basis ?? 'working_days') === 'calendar_days')>Calendar Days</option>
                                    <option value="fixed_30_days" @selected(old('salary_day_basis', $policy->salary_day_basis ?? 'working_days') === 'fixed_30_days')>Fixed 30 Days</option>
                                    <option value="custom_fixed_days" @selected(old('salary_day_basis', $policy->salary_day_basis ?? 'working_days') === 'custom_fixed_days')>Custom Fixed Days</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label class="font-weight-bold">Working Day Mode</label>
                                <select name="working_day_mode" class="form-control select2-searchable" required>
                                    <option value="exclude_weekoffs" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'exclude_weekoffs')>Exclude Weekoffs (Sundays & Alt Saturdays)</option>
                                    <option value="include_all_days" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'include_all_days')>Include All Days</option>
                                    <option value="exclude_sundays" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'exclude_sundays')>Exclude Sundays Only</option>
                                    <option value="exclude_holidays" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'exclude_holidays')>Exclude Holidays Only</option>
                                    <option value="exclude_weekoffs_and_holidays" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'exclude_weekoffs_and_holidays')>Exclude Weekoffs & Holidays</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="ep-form-group">
                                <label class="font-weight-bold">Salary Day Base</label>
                                <input type="number" min="1" max="31" name="salary_day_base" value="{{ old('salary_day_base', $policy->salary_day_base ?? 26) }}" class="form-control" required>
                                <small class="text-muted">Standard is 26 days</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="ep-form-group">
                                <label class="font-weight-bold">Custom Fixed Days</label>
                                <input type="number" min="1" max="31" id="custom_fixed_days" name="custom_fixed_days" value="{{ old('custom_fixed_days', $policy->custom_fixed_days ?? '') }}" class="form-control" placeholder="e.g. 26">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Statutory Deductions --}}
                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-university text-primary"></i> 2. Statutory Deductions (Taxes & Social Security)</div>
                    <div class="row">
                        {{-- PT --}}
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light h-100">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" name="professional_tax_enabled" id="pt_enabled" value="1" class="custom-control-input" @checked(old('professional_tax_enabled', $policy->professional_tax_enabled ?? true))>
                                    <label class="custom-control-label font-weight-bold" for="pt_enabled">Professional Tax (PT)</label>
                                </div>
                                <small class="text-muted d-block">Applies active statutory tax slabs (₹0 to ₹2,500/yr with March balancing).</small>
                            </div>
                        </div>

                        {{-- PF --}}
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light h-100">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" name="pf_enabled" id="pf_enabled_check" value="1" class="custom-control-input" @checked(old('pf_enabled', $policy->pf_enabled ?? false))>
                                    <label class="custom-control-label font-weight-bold" for="pf_enabled_check">Provident Fund (PF)</label>
                                </div>
                                <div class="mb-2">
                                    <label class="small text-muted mb-0">PF Rate (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="pf_percentage" value="{{ old('pf_percentage', $policy->pf_percentage ?? 12) }}" class="form-control form-control-sm">
                                </div>
                                <div class="mb-2">
                                    <label class="small text-muted mb-0">Wage Ceiling (Optional)</label>
                                    <input type="number" step="0.01" min="0" name="pf_wage_ceiling" value="{{ old('pf_wage_ceiling', $policy->pf_wage_ceiling ?? 15000) }}" class="form-control form-control-sm" placeholder="e.g. 15000">
                                </div>
                                <div>
                                    <label class="small text-muted mb-0">Wage Basis</label>
                                    <select name="pf_wage_basis" class="form-control form-control-sm select2-searchable">
                                        <option value="basic" @selected(old('pf_wage_basis', $policy->pf_wage_basis ?? 'basic') === 'basic')>Basic Salary</option>
                                        <option value="gross" @selected(old('pf_wage_basis', $policy->pf_wage_basis ?? 'basic') === 'gross')>Gross Salary</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- ESI --}}
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light h-100">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" name="esi_enabled" id="esi_enabled_check" value="1" class="custom-control-input" @checked(old('esi_enabled', $policy->esi_enabled ?? false))>
                                    <label class="custom-control-label font-weight-bold" for="esi_enabled_check">ESI</label>
                                </div>
                                <div class="mb-2">
                                    <label class="small text-muted mb-0">ESI Rate (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="esi_percentage" value="{{ old('esi_percentage', $policy->esi_percentage ?? 0.75) }}" class="form-control form-control-sm">
                                </div>
                                <div class="mb-2">
                                    <label class="small text-muted mb-0">Gross Ceiling</label>
                                    <input type="number" step="0.01" min="0" name="esi_wage_ceiling" value="{{ old('esi_wage_ceiling', $policy->esi_wage_ceiling ?? 21000) }}" class="form-control form-control-sm" placeholder="e.g. 21000">
                                </div>
                                <div>
                                    <label class="small text-muted mb-0">Wage Basis</label>
                                    <select name="esi_wage_basis" class="form-control form-control-sm select2-searchable">
                                        <option value="gross" @selected(old('esi_wage_basis', $policy->esi_wage_basis ?? 'gross') === 'gross')>Gross Salary</option>
                                        <option value="basic" @selected(old('esi_wage_basis', $policy->esi_wage_basis ?? 'gross') === 'basic')>Basic Salary</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- TDS & Operational --}}
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light h-100">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" name="tds_enabled" id="tds_enabled_check" value="1" class="custom-control-input" @checked(old('tds_enabled', $policy->tds_enabled ?? false))>
                                    <label class="custom-control-label font-weight-bold" for="tds_enabled_check">TDS (Income Tax)</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" name="allow_negative_salary" id="neg_sal_check" value="1" class="custom-control-input" @checked(old('allow_negative_salary', $policy->allow_negative_salary ?? false))>
                                    <label class="custom-control-label" for="neg_sal_check">Allow Negative Salary</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="payroll_lock_after_generation" id="lock_gen_check" value="1" class="custom-control-input" @checked(old('payroll_lock_after_generation', $policy->payroll_lock_after_generation ?? false))>
                                    <label class="custom-control-label" for="lock_gen_check">Lock On Generation</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Payable Ratios --}}
                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-percent text-primary"></i> 3. Payable Attendance Ratios</div>
                    <div class="row">
                        <div class="col-md-4 col-6 mb-3">
                            <label class="small font-weight-bold">Half Day Ratio</label>
                            <input type="number" step="0.01" min="0" max="1" name="half_day_payable_ratio" value="{{ old('half_day_payable_ratio', $policy->half_day_payable_ratio ?? 0.5) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="small font-weight-bold">Absent Ratio</label>
                            <input type="number" step="0.01" min="0" max="1" name="absent_payable_ratio" value="{{ old('absent_payable_ratio', $policy->absent_payable_ratio ?? 0) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="small font-weight-bold">LWP Ratio</label>
                            <input type="number" step="0.01" min="0" max="1" name="lwp_payable_ratio" value="{{ old('lwp_payable_ratio', $policy->lwp_payable_ratio ?? 0) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="small font-weight-bold">Paid Leave Ratio</label>
                            <input type="number" step="0.01" min="0" max="1" name="paid_leave_payable_ratio" value="{{ old('paid_leave_payable_ratio', $policy->paid_leave_payable_ratio ?? 1.0) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="small font-weight-bold">Weekoff Ratio</label>
                            <input type="number" step="0.01" min="0" max="1" name="weekoff_payable_ratio" value="{{ old('weekoff_payable_ratio', $policy->weekoff_payable_ratio ?? 1.0) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="small font-weight-bold">Holiday Ratio</label>
                            <input type="number" step="0.01" min="0" max="1" name="holiday_payable_ratio" value="{{ old('holiday_payable_ratio', $policy->holiday_payable_ratio ?? 1.0) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="include_weekoff_in_payable" id="inc_wo_check" value="1" class="custom-control-input" @checked(old('include_weekoff_in_payable', $policy->include_weekoff_in_payable ?? true))>
                                <label class="custom-control-label font-weight-bold" for="inc_wo_check">Include Weekoff in Payable Days</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="include_holiday_in_payable" id="inc_hol_check" value="1" class="custom-control-input" @checked(old('include_holiday_in_payable', $policy->include_holiday_in_payable ?? true))>
                                <label class="custom-control-label font-weight-bold" for="inc_hol_check">Include Holiday in Payable Days</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Salary Credit Window --}}
                <div class="ep-section-card mb-0">
                    <div class="ep-section-title"><i class="fas fa-clock text-primary"></i> 4. Salary Credit Window</div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold">Credit Start Day (Day of month)</label>
                            <input type="number" min="1" max="31" name="salary_credit_start_day" value="{{ old('salary_credit_start_day', $policy->salary_credit_start_day ?? 7) }}" class="form-control" required placeholder="e.g. 7">
                            <small class="text-muted">Salary disbursement starts on this day</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold">Credit End Day (Day of month)</label>
                            <input type="number" min="1" max="31" name="salary_credit_end_day" value="{{ old('salary_credit_end_day', $policy->salary_credit_end_day ?? 10) }}" class="form-control" required placeholder="e.g. 10">
                            <small class="text-muted">Target salary disbursement completion day</small>
                        </div>
                    </div>
                </div>

            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit PT Slabs Modal --}}
<div class="modal fade" id="ptSlabsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('enterprise-payroll.policies.pt-slabs.update') }}" class="modal-content ep-form border-0 shadow-lg ep-modal-content-scrollable">
            @csrf
            <div class="ep-modal-header">
                <div>
                    <h5 class="modal-title font-weight-bold">Edit Professional Tax (PT) Slabs</h5>
                    <p class="text-muted small mb-0">Statutory Tax Tiers & March Reconciliation</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="bg-light text-muted small">
                            <tr>
                                <th>Monthly Wage Range</th>
                                <th style="width: 170px;">Regular Month (₹)</th>
                                <th style="width: 170px;">12th Mo Balancing (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ptSlabs as $index => $slab)
                            <tr>
                                <td class="align-middle">
                                    <input type="hidden" name="slabs[{{ $index }}][id]" value="{{ $slab->id }}">
                                    <strong>₹{{ number_format($slab->monthly_gross_min, 0) }}</strong>
                                    to
                                    <strong>{{ $slab->monthly_gross_max ? '₹' . number_format($slab->monthly_gross_max, 0) : 'Above' }}</strong>
                                    <div class="text-muted small">
                                        @if($slab->monthly_gross_max)
                                            Annual: Up to ₹{{ number_format($slab->monthly_gross_max * 12, 0) }}
                                        @else
                                            Annual: Above ₹4,00,000
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="ep-currency-input">
                                        <span class="ep-currency-symbol">₹</span>
                                        <input type="number" step="0.01" min="0" class="ep-currency-field" name="slabs[{{ $index }}][monthly_tax_amount]" value="{{ (float) $slab->monthly_tax_amount }}" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="ep-currency-input">
                                        <span class="ep-currency-symbol">₹</span>
                                        <input type="number" step="0.01" min="0" class="ep-currency-field" name="slabs[{{ $index }}][balancing_month_amount]" value="{{ (float) ($slab->balancing_month_amount ?? $slab->monthly_tax_amount) }}">
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-save"></i> Save Slabs</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('_script')
<script>
    (function() {
        function toggleCustomDays() {
            var basis = document.getElementById('salary_day_basis');
            var custom = document.getElementById('custom_fixed_days');
            if (!basis || !custom) return;
            custom.required = basis.value === 'custom_fixed_days';
            if (!custom.required && basis.value !== 'custom_fixed_days') {
                custom.placeholder = 'Only for custom fixed days';
            } else {
                custom.placeholder = 'e.g. 26';
            }
        }
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'salary_day_basis') toggleCustomDays();
        });
        toggleCustomDays();
    })();
</script>
@endsection
