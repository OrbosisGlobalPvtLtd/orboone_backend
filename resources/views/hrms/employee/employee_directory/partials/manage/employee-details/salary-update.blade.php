                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-money-bill-wave"></i>Current Salary Update</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Actual Salary (Monthly CTC)</label>
                                    <input type="number" step="0.01" name="actual_salary" class="em-control editable" value="{{ old('actual_salary', $employeeData->actual_salary) }}" placeholder="Enter Monthly CTC (Example: 25000)" readonly>
                                    <div class="small-note">Annual CTC is calculated automatically by the system.</div>
                                    @error('actual_salary') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Salary Effective From</label>
                                    <input type="date" name="salary_effective_from" class="em-control editable" value="{{ old('salary_effective_from', now()->toDateString()) }}" readonly>
                                    @error('salary_effective_from') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" style="grid-column:1/-1;">
                                    <label>Salary Reason</label>
                                    <input type="text" name="salary_change_reason" class="em-control editable" value="{{ old('salary_change_reason') }}" placeholder="Increment / Stage change / Correction" readonly>
                                    @error('salary_change_reason') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
