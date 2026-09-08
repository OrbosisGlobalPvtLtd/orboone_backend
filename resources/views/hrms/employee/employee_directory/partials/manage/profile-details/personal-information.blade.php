                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-user"></i>Personal Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Profile Image</label>

                                    <div class="em-file-view-box">
                                        <span>{{ !empty($employeeData->profile_image) ? 'Image uploaded' : 'No image uploaded' }}</span>
                                        @if (!empty($employeeData->profile_image))
                                        <a href="{{ $fileUrl($employeeData->profile_image) }}" target="_blank"><i class="fas fa-eye"></i> View</a>
                                        @endif
                                    </div>

                                    <div class="em-upload-control">
                                        <label class="em-upload-label">
                                            <input type="file" name="profile_image" class="editable-file" disabled accept=".jpg,.jpeg,.png,.webp">
                                            <span class="em-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="em-upload-text">
                                                <strong>{{ !empty($employeeData->profile_image) ? 'Replace Profile Image' : 'Upload Profile Image' }}</strong>
                                                <small>JPG, PNG, WEBP supported</small>
                                            </span>
                                        </label>
                                    </div>

                                    @error('profile_image') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="em-control editable" value="{{ old('date_of_birth', $employeeData->date_of_birth) }}" readonly>
                                    @error('date_of_birth') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Gender</label>
                                    <select name="gender" class="em-control editable-select" disabled>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $employeeData->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $employeeData->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $employeeData->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Address</label>
                                    <textarea name="address" class="em-control editable" readonly>{{ old('address', $employeeData->address) }}</textarea>
                                    @error('address') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Emergency Contact Number</label>
                                    <input type="text" name="emergency_contact_number" class="em-control editable" value="{{ old('emergency_contact_number', $employeeData->emergency_contact_number) }}" readonly>
                                    @error('emergency_contact_number') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
