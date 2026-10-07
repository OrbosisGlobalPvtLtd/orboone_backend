                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-id-card"></i>Profile Approval Status</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Profile Status</label>
                                    <input type="text" class="em-control" value="{{ ucfirst($employeeData->profile_status ?? 'pending') }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Profile Completed</label>
                                    <input type="text" class="em-control" value="{{ $isCompleted ? 'Yes' : 'No' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Approved At</label>
                                    <input type="text" class="em-control" value="{{ $approvedAt ?? '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Rejection Reason</label>
                                    <input type="text" class="em-control" value="{{ $employeeData->rejection_reason ?? '-' }}" readonly>
                                </div>
                            </div>
                        </div>
