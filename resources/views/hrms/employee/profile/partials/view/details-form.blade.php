<form id="profileInlineForm"
    action="{{ route('hrms.employees.profile.inline_update', $profile->employee_id) }}"
    method="POST"
    enctype="multipart/form-data">
    @csrf

    <div class="section-two-grid">
        {{-- Personal Details Card --}}
        <div class="profile-card">
            <div class="profile-card-head">
                <div class="profile-icon"><i class="fas fa-user"></i></div>
                <div>
                    <h5>Personal Details</h5>
                    <p>Basic identity and address information.</p>
                </div>
            </div>

            <div class="profile-card-body">
                <div class="info-grid">
                    <div class="profile-info">
                        <span class="profile-label">Date of Birth</span>
                        @php
                        $dobRaw = $profile->date_of_birth ?? null;
                        $dobValue = !empty($dobRaw) ? \Carbon\Carbon::parse($dobRaw)->format('Y-m-d') : '';
                        $dobDisplay = !empty($dobRaw) ? \Carbon\Carbon::parse($dobRaw)->format('d M Y') : '-';
                        @endphp
                        @if($isProfileEditMode)
                        <x-form.date-picker 
                            name="date_of_birth" 
                            id="view_date_of_birth" 
                            class="profile-edit-control" 
                            :value="old('date_of_birth', $dobValue)" 
                            placeholder="dd-mm-yyyy"
                        />
                        @else
                        <div class="profile-value {{ empty($dobRaw) ? 'muted' : '' }}">
                            {{ $dobDisplay }}
                        </div>
                        @endif
                    </div>

                    <div class="profile-info">
                        <span class="profile-label">Gender</span>
                        @if($isProfileEditMode)
                        <x-form.select 
                            name="gender" 
                            id="view_gender" 
                            class="profile-edit-control" 
                            placeholder="Select Gender"
                            :options="[
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other'
                            ]"
                            :selected="old('gender', $profile->gender ?? '')" 
                            :searchable="true" 
                            wrapper-class="m-0"
                        />
                        @else
                        <div class="profile-value {{ empty($profile->gender) ? 'muted' : '' }}">
                            {{ !empty($profile->gender) ? ucfirst($profile->gender) : '-' }}
                        </div>
                        @endif
                    </div>

                    <div class="profile-info">
                        <span class="profile-label">Phone</span>
                        @if($isProfileEditMode)
                        <input type="text" name="phone" class="form-control profile-edit-control"
                            value="{{ old('phone', $profile->phone ?? '') }}">
                        @else
                        <div class="profile-value {{ empty($profile->phone) ? 'muted' : '' }}">{{ $profile->phone ?? '-' }}</div>
                        @endif
                    </div>

                    <div class="profile-info">
                        <span class="profile-label">Email</span>
                        <div class="profile-value {{ empty($profile->email) ? 'muted' : '' }}">{{ $profile->email ?? '-' }}</div>
                    </div>

                    <div class="profile-info wide">
                        <span class="profile-label">Address</span>
                        @if($isProfileEditMode)
                        <textarea name="address" rows="2" class="form-control profile-edit-control">{{ old('address', $profile->address ?? '') }}</textarea>
                        @else
                        <div class="profile-value {{ empty($profile->address) ? 'muted' : '' }}">{{ $profile->address ?? '-' }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Education & Experience Card --}}
        <div class="profile-card">
            <div class="profile-card-head">
                <div class="profile-icon"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <h5>Education & Experience</h5>
                    <p>Qualification, score and total work experience.</p>
                </div>
            </div>

            <div class="profile-card-body">
                <div class="info-grid">
                    <div class="profile-info">
                        <span class="profile-label">Qualification</span>
                        @if($isProfileEditMode)
                        <input type="text" name="highest_qualification" class="form-control profile-edit-control"
                            value="{{ old('highest_qualification', $profile->highest_qualification ?? '') }}">
                        @else
                        <div class="profile-value {{ empty($profile->highest_qualification) ? 'muted' : '' }}">{{ $profile->highest_qualification ?? '-' }}</div>
                        @endif
                    </div>

                    <div class="profile-info">
                        <span class="profile-label">CGPA / Percentage</span>
                        @if($isProfileEditMode)
                        <input type="text" name="cgpa_percentage" class="form-control profile-edit-control"
                            value="{{ old('cgpa_percentage', $profile->cgpa_percentage ?? '') }}">
                        @else
                        <div class="profile-value {{ empty($profile->cgpa_percentage) ? 'muted' : '' }}">{{ $profile->cgpa_percentage ?? '-' }}</div>
                        @endif
                    </div>

                    <div class="profile-info">
                        <span class="profile-label">Experience Type</span>
                        @if($isProfileEditMode)
                        <x-form.select 
                            name="experience_type" 
                            id="view_experience_type" 
                            class="profile-edit-control" 
                            placeholder="Select Experience Type"
                            :options="[
                                'fresher' => 'Fresher',
                                'experienced' => 'Experienced'
                            ]"
                            :selected="old('experience_type', $profile->experience_type ?? 'fresher')" 
                            :searchable="true" 
                            wrapper-class="m-0"
                        />
                        @else
                        <div class="profile-value {{ empty($profile->experience_type) ? 'muted' : '' }}">{{ !empty($profile->experience_type) ? ucfirst($profile->experience_type) : 'Fresher' }}</div>
                        @endif
                    </div>

                    <div class="profile-info" id="view_total_experience_container">
                        <span class="profile-label">Total Experience</span>
                        @if($isProfileEditMode)
                        <input type="text" name="total_experience" id="view_total_experience" class="form-control profile-edit-control"
                            value="{{ old('total_experience', $profile->total_experience ?? '') }}">
                        @else
                        <div class="profile-value {{ empty($profile->total_experience) ? 'muted' : '' }}">{{ $profile->total_experience ?? '-' }}</div>
                        @endif
                    </div>

                    <div class="profile-info">
                        <span class="profile-label">Employee Code</span>
                        <div class="profile-value {{ empty($profile->employee_code) ? 'muted' : '' }}">{{ $profile->employee_code ?? '-' }}</div>
                    </div>

                    <div class="profile-info">
                        <span class="profile-label">Resume</span>
                        @if($isProfileEditMode)
                        <div class="d-flex flex-column gap-2">
                            <input type="file" name="resume_file" class="form-control profile-edit-control" accept=".pdf,.doc,.docx">
                            @if (!empty($profile->resume_file) && Route::has('hrms.documents.file'))
                            @php $resumeUrl = route('hrms.documents.file', $profile->resume_file); @endphp
                            <div class="small text-muted mt-1">
                                Current: 
                                <button type="button" class="file-link js-doc-preview text-primary p-0 border-0 bg-transparent fw-bold"
                                    data-title="Resume"
                                    data-url="{{ $resumeUrl }}"
                                    data-ext="{{ strtolower(pathinfo($profile->resume_file, PATHINFO_EXTENSION)) }}">
                                    <i class="fas fa-eye"></i> View Current Resume
                                </button>
                            </div>
                            @endif
                        </div>
                        @else
                        @if (!empty($profile->resume_file) && Route::has('hrms.documents.file'))
                        @php $resumeUrl = route('hrms.documents.file', $profile->resume_file); @endphp
                        <button type="button" class="file-link js-doc-preview"
                            data-title="Resume"
                            data-url="{{ $resumeUrl }}"
                            data-ext="{{ strtolower(pathinfo($profile->resume_file, PATHINFO_EXTENSION)) }}">
                            <i class="fas fa-eye"></i> View Resume
                        </button>
                        @else
                        <div class="profile-value muted">No resume uploaded</div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bank Details Card --}}
    <div class="profile-card">
        <div class="profile-card-head">
            <div class="profile-icon"><i class="fas fa-university"></i></div>
            <div>
                <h5>Bank Details</h5>
                <p>Salary account and banking information.</p>
            </div>
        </div>

        <div class="profile-card-body">
            <div class="bank-grid">
                @foreach([
                'bank_holder_name' => 'Account Holder',
                'bank_account_no' => 'Account No',
                'bank_account_type' => 'Account Type',
                'ifsc_code' => 'IFSC',
                'bank_branch' => 'Bank Branch',
                ] as $field => $label)
                <div class="profile-info">
                    <span class="profile-label">{{ $label }}</span>
                    @if($isProfileEditMode)
                        @if($field === 'bank_account_type')
                        <x-form.select 
                            name="bank_account_type" 
                            id="view_bank_account_type" 
                            class="profile-edit-control" 
                            placeholder="Select Account Type"
                            :options="[
                                'saving' => 'Saving',
                                'savings' => 'Savings',
                                'current' => 'Current',
                                'salary' => 'Salary'
                            ]"
                            :selected="old('bank_account_type', strtolower($profile->bank_account_type ?? 'saving'))" 
                            :searchable="true" 
                            wrapper-class="m-0"
                        />
                        @else
                        <input type="text" name="{{ $field }}" class="form-control profile-edit-control"
                            value="{{ old($field, $profile->{$field} ?? '') }}">
                        @endif
                    @else
                    <div class="profile-value {{ empty($profile->{$field}) ? 'muted' : '' }}">
                        {{ !empty($profile->{$field}) ? ($field === 'bank_account_type' ? ucfirst($profile->{$field}) : $profile->{$field}) : '-' }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</form>
