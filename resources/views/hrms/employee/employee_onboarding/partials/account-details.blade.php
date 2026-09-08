<div class="eo-card">
    <div class="eo-card-head">
        <div class="eo-section-title">
            <div class="eo-section-icon"><i class="fas fa-user-plus"></i></div>
            <div>
                <h5>Account Details</h5>
                <p>Login identity and basic contact information.</p>
            </div>
        </div>
    </div>

    <div class="eo-card-body">
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Employee Code</label>
                <input type="text" class="form-control readonly-field" value="{{ $employeeData->employee_code ?? $nextEmployeeCode ?? '' }}" readonly>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $employeeData->name ?? '') }}" placeholder="Enter full name" required>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $employeeData->email ?? '') }}" placeholder="employee@company.com" required>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 eo-field">
                <label>Phone <span class="required">*</span></label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employeeData->phone ?? '') }}" placeholder="Phone number" required>
            </div>
        </div>
    </div>
</div>
