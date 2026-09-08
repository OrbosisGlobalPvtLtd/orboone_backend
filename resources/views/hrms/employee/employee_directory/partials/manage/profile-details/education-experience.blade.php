                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-graduation-cap"></i>Education & Experience</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Highest Qualification</label>
                                    <input type="text" name="highest_qualification" class="em-control editable" value="{{ old('highest_qualification', $employeeData->highest_qualification) }}" readonly>
                                    @error('highest_qualification') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>CGPA / Percentage</label>
                                    <input type="text" name="cgpa_percentage" class="em-control editable" value="{{ old('cgpa_percentage', $employeeData->cgpa_percentage) }}" readonly>
                                    @error('cgpa_percentage') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Experience Type</label>
                                    <select name="experience_type" id="manage_experience_type" class="em-control editable-select" disabled onchange="toggleManageExperienceFields(this.value)">
                                        <option value="">Select Experience Type</option>
                                        <option value="fresher" {{ old('experience_type', $employeeData->experience_type ?? '') == 'fresher' ? 'selected' : '' }}>Fresher</option>
                                        <option value="experienced" {{ old('experience_type', $employeeData->experience_type ?? '') == 'experienced' ? 'selected' : '' }}>Experienced</option>
                                    </select>
                                    @error('experience_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" id="manage_total_experience_container">
                                    <label>Total Experience</label>
                                    <input type="text" name="total_experience" id="manage_total_experience" class="em-control editable" value="{{ old('total_experience', $employeeData->total_experience) }}" readonly>
                                    @error('total_experience') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" style="grid-column:1/-1;">
                                    <label>Resume File</label>

                                    <div class="em-file-view-box">
                                        <span>{{ !empty($employeeData->resume_file) ? 'Resume uploaded' : 'No resume uploaded' }}</span>
                                        @if (!empty($employeeData->resume_file))
                                        <a href="{{ $fileUrl($employeeData->resume_file) }}" target="_blank"><i class="fas fa-eye"></i> View</a>
                                        @endif
                                    </div>

                                    <div class="em-upload-control">
                                        <label class="em-upload-label">
                                            <input type="file" name="resume_file" class="editable-file" disabled accept=".pdf,.jpg,.jpeg,.png,.webp">
                                            <span class="em-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="em-upload-text">
                                                <strong>{{ !empty($employeeData->resume_file) ? 'Replace Resume' : 'Upload Resume' }}</strong>
                                                <small>PDF, JPG, PNG, WEBP supported</small>
                                            </span>
                                        </label>
                                    </div>

                                    @error('resume_file') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
