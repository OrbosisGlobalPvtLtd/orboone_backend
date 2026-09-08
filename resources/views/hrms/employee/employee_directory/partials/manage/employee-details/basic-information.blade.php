                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-id-badge"></i>Basic Information</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Employee Code</label>
                                    <input type="text" class="em-control" value="{{ $employeeData->employee_code ?? '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Name</label>
                                    <input type="text" name="name" class="em-control editable" value="{{ old('name', $employeeData->name) }}" readonly>
                                    @error('name') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Email</label>
                                    <input type="email" name="email" class="em-control editable" value="{{ old('email', $employeeData->email) }}" readonly>
                                    @error('email') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="em-control editable" value="{{ old('phone', $employeeData->phone) }}" readonly>
                                    @error('phone') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
