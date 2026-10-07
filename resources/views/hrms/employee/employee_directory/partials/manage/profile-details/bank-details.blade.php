                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-university"></i>Bank Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Account Holder</label>
                                    <input type="text" name="bank_holder_name" class="em-control editable" value="{{ old('bank_holder_name', $employeeData->bank_holder_name) }}" readonly>
                                    @error('bank_holder_name') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Account Number</label>
                                    <input type="text" name="bank_account_no" class="em-control editable" value="{{ old('bank_account_no', $employeeData->bank_account_no) }}" readonly>
                                    @error('bank_account_no') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Account Type</label>
                                    <x-form.select 
                                        name="bank_account_type" 
                                        id="bank_account_type" 
                                        class="em-control editable-select" 
                                        placeholder="Select Account Type"
                                        :options="[
                                            'saving' => 'Saving',
                                            'savings' => 'Savings',
                                            'current' => 'Current',
                                            'salary' => 'Salary'
                                        ]"
                                        :selected="old('bank_account_type', $employeeData->bank_account_type)" 
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    />
                                    @error('bank_account_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>IFSC Code</label>
                                    <input type="text" name="ifsc_code" class="em-control editable" value="{{ old('ifsc_code', $employeeData->ifsc_code) }}" readonly>
                                    @error('ifsc_code') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Bank Branch</label>
                                    <input type="text" name="bank_branch" class="em-control editable" value="{{ old('bank_branch', $employeeData->bank_branch) }}" readonly>
                                    @error('bank_branch') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
