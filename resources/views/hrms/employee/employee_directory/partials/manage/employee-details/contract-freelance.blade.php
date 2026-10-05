                        <div class="em-section contract-section">
                            <h6 class="em-section-title"><i class="fas fa-file-contract"></i>Contract / Freelance Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Contract End / Review Date</label>
                                    <x-form.date-picker 
                                        name="contract_end_date" 
                                        id="contract_end_date" 
                                        class="em-control editable" 
                                        :value="old('contract_end_date', $employeeData->contract_end_date ?? '')" 
                                        disabled 
                                    />
                                    @error('contract_end_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
