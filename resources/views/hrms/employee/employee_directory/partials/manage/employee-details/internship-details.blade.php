                        <div class="em-section internship-section">
                            <h6 class="em-section-title"><i class="fas fa-user-graduate"></i>Internship Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Internship Start</label>
                                    <x-form.date-picker 
                                        name="internship_start_date" 
                                        id="internship_start_date" 
                                        class="em-control editable" 
                                        :value="old('internship_start_date', $employeeData->internship_start_date)" 
                                        disabled 
                                    />
                                </div>

                                <div class="em-field">
                                    <label>Internship End</label>
                                    <x-form.date-picker 
                                        name="internship_end_date" 
                                        id="internship_end_date" 
                                        class="em-control editable" 
                                        :value="old('internship_end_date', $employeeData->internship_end_date)" 
                                        disabled 
                                    />
                                </div>

                                <div class="em-field">
                                    <label>Extended To</label>
                                    <x-form.date-picker 
                                        name="internship_extended_to" 
                                        id="internship_extended_to" 
                                        class="em-control" 
                                        :value="$employeeData->internship_extended_to ?? ''" 
                                        disabled 
                                    />
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
                                    <x-form.select 
                                        name="is_paid_intern" 
                                        id="is_paid_intern" 
                                        class="em-control editable-select" 
                                        placeholder="Select"
                                        :options="[
                                            '1' => 'Yes',
                                            '0' => 'No'
                                        ]"
                                        :selected="old('is_paid_intern', (string)$employeeData->is_paid_intern)" 
                                        :searchable="true" 
                                        disabled 
                                        wrapper-class="m-0"
                                    />
                                </div>
                            </div>
                        </div>
