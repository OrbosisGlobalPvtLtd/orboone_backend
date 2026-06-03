@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-building"></i> Enterprise Payroll</div>
            <h1>Payroll Policy Settings</h1>
            <p>Configure salary basis, working-day rules, statutory deductions, payable ratios and salary credit windows.</p>
        </div>
    </div>

    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-card">
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-cogs"></i></div>
                <div>
                    <h5 class="ep-table-title">Payroll Policy Settings</h5>
                    <p class="ep-table-subtitle">Manage company payroll calculation rules and salary deduction policies.</p>
                </div>
            </div>
            @if($canUpdate)
            <div class="ep-hero-actions">
                <button class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#policyModal"><i class="fas fa-edit"></i> Edit Policy</button>
            </div>
            @endif
        </div>

        <div class="ep-card-body">
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="ep-section-card h-100">
                        <div class="ep-section-title"><i class="fas fa-calculator"></i> Salary Calculation Policy</div>
                        <div class="row">
                            <div class="col-6 mb-3"><small class="text-muted d-block">Salary Day Basis</small><strong>{{ $policy->salary_day_basis }}</strong></div>
                            <div class="col-6 mb-3"><small class="text-muted d-block">Working Day Mode</small><strong>{{ $policy->working_day_mode }}</strong></div>
                            <div class="col-6 mb-1"><small class="text-muted d-block">Custom Fixed Days</small><strong>{{ $policy->custom_fixed_days ?: '-' }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="ep-section-card h-100">
                        <div class="ep-section-title"><i class="fas fa-university"></i> Statutory Deductions</div>
                        <div class="row">
                            <div class="col-6 mb-2"><small class="text-muted d-block">Professional Tax</small><strong>{{ $policy->professional_tax_enabled ? 'Enabled' : 'Disabled' }} (₹{{ number_format((float) $policy->professional_tax_amount, 2) }})</strong></div>
                            <div class="col-6 mb-2"><small class="text-muted d-block">PF</small><strong>{{ $policy->pf_enabled ? 'Enabled' : 'Disabled' }} ({{ (float) $policy->pf_percentage }}%)</strong></div>
                            <div class="col-6 mb-2"><small class="text-muted d-block">ESI</small><strong>{{ $policy->esi_enabled ? 'Enabled' : 'Disabled' }} ({{ (float) $policy->esi_percentage }}%)</strong></div>
                            <div class="col-6 mb-2"><small class="text-muted d-block">TDS</small><strong>{{ $policy->tds_enabled ? 'Enabled' : 'Disabled' }} ({{ (float) $policy->tds_percentage }}%)</strong></div>
                            <div class="col-12"><small class="text-muted d-block">TDS Source</small><strong>{{ $policy->tds_source ?: 'policy' }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 mb-3">
                    <div class="ep-section-card h-100">
                        <div class="ep-section-title"><i class="fas fa-percent"></i> Payable Ratios</div>
                        <div class="row">
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Half Day</small><strong>{{ (float) $policy->half_day_payable_ratio }}</strong></div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Absent</small><strong>{{ (float) $policy->absent_payable_ratio }}</strong></div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">LWP</small><strong>{{ (float) $policy->lwp_payable_ratio }}</strong></div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Paid Leave</small><strong>{{ (float) $policy->paid_leave_payable_ratio }}</strong></div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Weekoff</small><strong>{{ (float) $policy->weekoff_payable_ratio }}</strong></div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Holiday</small><strong>{{ (float) $policy->holiday_payable_ratio }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="ep-section-card h-100">
                        <div class="ep-section-title"><i class="fas fa-clock"></i> Salary Credit Window</div>
                        <div class="mb-2"><small class="text-muted d-block">Current Credit Window</small><strong>{{ $policy->salary_credit_start_day }} to {{ $policy->salary_credit_end_day }}</strong></div>
                        <div><small class="text-muted d-block">Future Target Window</small><strong>{{ $policy->future_salary_credit_start_day }} to {{ $policy->future_salary_credit_end_day }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="ep-section-card mb-0">
                <div class="ep-section-title"><i class="fas fa-chart-line"></i> Live Sample Calculation Preview</div>
                <div class="row">
                    <div class="col-md-3"><small class="text-muted d-block">Salary</small><strong>₹{{ number_format($preview['salary'], 2) }}</strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Working Days</small><strong>{{ number_format($preview['working_days'], 2) }}</strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Absent Days</small><strong>{{ number_format($preview['absent_days'], 2) }}</strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Professional Tax</small><strong>₹{{ number_format($preview['professional_tax'], 2) }}</strong></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4"><small class="text-muted d-block">Per Day Salary</small><strong>₹{{ number_format($preview['per_day_salary'], 2) }}</strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Attendance Deduction</small><strong>₹{{ number_format($preview['attendance_deduction'], 2) }}</strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Net Salary</small><strong>₹{{ number_format($preview['net_salary'], 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canUpdate)
<div class="modal fade" id="policyModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form method="POST" action="{{ route('enterprise-payroll.policies.update') }}" class="modal-content ep-form border-0 shadow-lg">
            @csrf
            <div class="ep-modal-header">
                <h5 class="modal-title">Edit Payroll Policy</h5>
                <p>Update salary basis, deductions, payable ratios and credit window.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-calculator"></i> Salary Basis</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>Salary Day Basis</label>
                                <select name="salary_day_basis" id="salary_day_basis" class="form-control" required>
                                    <option value="working_days" @selected(old('salary_day_basis', $policy->salary_day_basis ?? 'working_days') === 'working_days')>Working Days</option>
                                    <option value="calendar_days" @selected(old('salary_day_basis', $policy->salary_day_basis ?? 'working_days') === 'calendar_days')>Calendar Days</option>
                                    <option value="fixed_30_days" @selected(old('salary_day_basis', $policy->salary_day_basis ?? 'working_days') === 'fixed_30_days')>Fixed 30 Days</option>
                                    <option value="custom_fixed_days" @selected(old('salary_day_basis', $policy->salary_day_basis ?? 'working_days') === 'custom_fixed_days')>Custom Fixed Days</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>Working Day Mode</label>
                                <select name="working_day_mode" class="form-control" required>
                                    <option value="include_all_days" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'include_all_days')>Include All Days</option>
                                    <option value="exclude_sundays" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'exclude_sundays')>Exclude Sundays</option>
                                    <option value="exclude_weekoffs" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'exclude_weekoffs')>Exclude Weekoffs</option>
                                    <option value="exclude_holidays" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'exclude_holidays')>Exclude Holidays</option>
                                    <option value="exclude_weekoffs_and_holidays" @selected(old('working_day_mode', $policy->working_day_mode ?? 'exclude_weekoffs') === 'exclude_weekoffs_and_holidays')>Exclude Weekoffs & Holidays</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>Custom Fixed Days</label>
                                <input type="number" min="1" max="31" id="custom_fixed_days" name="custom_fixed_days" value="{{ old('custom_fixed_days', $policy->custom_fixed_days ?? '') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-university"></i> Deductions</div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="professional_tax_enabled" value="1" class="form-check-input" @checked(old('professional_tax_enabled', $policy->professional_tax_enabled ?? true))>
                                    <span class="form-check-label">Professional Tax Enabled</span>
                                </label>
                                <input type="number" step="0.01" name="professional_tax_amount" value="{{ old('professional_tax_amount', $policy->professional_tax_amount ?? 200) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="pf_enabled" value="1" class="form-check-input" @checked(old('pf_enabled', $policy->pf_enabled ?? true))>
                                    <span class="form-check-label">PF Enabled</span>
                                </label>
                                <input type="number" step="0.01" min="0" max="100" name="pf_percentage" value="{{ old('pf_percentage', $policy->pf_percentage ?? 12) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="esi_enabled" value="1" class="form-check-input" @checked(old('esi_enabled', $policy->esi_enabled ?? true))>
                                    <span class="form-check-label">ESI Enabled</span>
                                </label>
                                <input type="number" step="0.01" min="0" max="100" name="esi_percentage" value="{{ old('esi_percentage', $policy->esi_percentage ?? 0.75) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="tds_enabled" value="1" class="form-check-input" @checked(old('tds_enabled', $policy->tds_enabled ?? true))>
                                    <span class="form-check-label">TDS Enabled</span>
                                </label>
                                <input type="number" step="0.01" min="0" max="100" name="tds_percentage" value="{{ old('tds_percentage', $policy->tds_percentage ?? 10) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="ep-form-group">
                                <label>TDS Source</label>
                                <select name="tds_source" class="form-control">
                                    <option value="policy" @selected(old('tds_source', $policy->tds_source ?? 'policy') === 'policy')>Policy Percentage</option>
                                    <option value="salary_structure" @selected(old('tds_source', $policy->tds_source ?? 'policy') === 'salary_structure')>Salary Structure Monthly TDS</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="allow_negative_salary" value="1" class="form-check-input" @checked(old('allow_negative_salary', $policy->allow_negative_salary ?? false))>
                                    <span class="form-check-label">Allow Negative Salary</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="payroll_lock_after_generation" value="1" class="form-check-input" @checked(old('payroll_lock_after_generation', $policy->payroll_lock_after_generation ?? false))>
                                    <span class="form-check-label">Lock After Generation</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-percent"></i> Ratios</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>Half Day Payable Ratio</label>
                                <input type="number" step="0.01" min="0" max="1" name="half_day_payable_ratio" value="{{ old('half_day_payable_ratio', $policy->half_day_payable_ratio ?? 0.5) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>Absent Payable Ratio</label>
                                <input type="number" step="0.01" min="0" max="1" name="absent_payable_ratio" value="{{ old('absent_payable_ratio', $policy->absent_payable_ratio ?? 0) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>LWP Payable Ratio</label>
                                <input type="number" step="0.01" min="0" max="1" name="lwp_payable_ratio" value="{{ old('lwp_payable_ratio', $policy->lwp_payable_ratio ?? 0) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>Paid Leave Payable Ratio</label>
                                <input type="number" step="0.01" min="0" max="1" name="paid_leave_payable_ratio" value="{{ old('paid_leave_payable_ratio', $policy->paid_leave_payable_ratio ?? 1.0) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>Weekoff Payable Ratio</label>
                                <input type="number" step="0.01" min="0" max="1" name="weekoff_payable_ratio" value="{{ old('weekoff_payable_ratio', $policy->weekoff_payable_ratio ?? 1.0) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ep-form-group">
                                <label>Holiday Payable Ratio</label>
                                <input type="number" step="0.01" min="0" max="1" name="holiday_payable_ratio" value="{{ old('holiday_payable_ratio', $policy->holiday_payable_ratio ?? 1.0) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="ep-form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="include_weekoff_in_payable" value="1" class="form-check-input" @checked(old('include_weekoff_in_payable', $policy->include_weekoff_in_payable ?? true))>
                                    <span class="form-check-label">Include Weekoff In Payable</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="ep-form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="include_holiday_in_payable" value="1" class="form-check-input" @checked(old('include_holiday_in_payable', $policy->include_holiday_in_payable ?? true))>
                                    <span class="form-check-label">Include Holiday In Payable</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ep-section-card mb-0">
                    <div class="ep-section-title"><i class="fas fa-clock"></i> Credit Window</div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label>Current Start Day</label>
                                <input type="number" min="1" max="31" name="salary_credit_start_day" value="{{ old('salary_credit_start_day', $policy->salary_credit_start_day ?? 1) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label>Current End Day</label>
                                <input type="number" min="1" max="31" name="salary_credit_end_day" value="{{ old('salary_credit_end_day', $policy->salary_credit_end_day ?? 30) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label>Future Start Day</label>
                                <input type="number" min="1" max="31" name="future_salary_credit_start_day" value="{{ old('future_salary_credit_start_day', $policy->future_salary_credit_start_day ?? 1) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="ep-form-group">
                                <label>Future End Day</label>
                                <input type="number" min="1" max="31" name="future_salary_credit_end_day" value="{{ old('future_salary_credit_end_day', $policy->future_salary_credit_end_day ?? 30) }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-save"></i> Update Policy</button>
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
            if (!custom.required) custom.value = '';
        }
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'salary_day_basis') toggleCustomDays();
        });
        toggleCustomDays();
    })();
</script>
@endsection