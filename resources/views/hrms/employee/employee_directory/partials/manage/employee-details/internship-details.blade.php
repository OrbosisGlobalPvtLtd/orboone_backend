                        <div class="em-section internship-section">
                            <h6 class="em-section-title"><i class="fas fa-user-graduate"></i>Internship Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Internship Start</label>
                                    <input type="date" name="internship_start_date" class="em-control editable" value="{{ old('internship_start_date', $employeeData->internship_start_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Internship End</label>
                                    <input type="date" name="internship_end_date" class="em-control editable" value="{{ old('internship_end_date', $employeeData->internship_end_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Extended To</label>
                                    <input type="date" class="em-control" value="{{ $employeeData->internship_extended_to ?? '' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Internship Status</label>
                                    <input type="text" class="em-control" value="{{ $internshipStatus ? ucfirst(str_replace('_', ' ', $internshipStatus)) : '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Completed At</label>
                                    <input type="text" class="em-control" value="{{ !empty($employeeData->internship_completed_at) ? \Carbon\Carbon::parse($employeeData->internship_completed_at)->format('d M Y h:i A') : '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Paid Intern</label>
                                    <select name="is_paid_intern" class="em-control editable-select" disabled>
                                        <option value="">Select</option>
                                        <option value="1" {{ (string) old('is_paid_intern', $employeeData->is_paid_intern) === '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ (string) old('is_paid_intern', $employeeData->is_paid_intern) === '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
