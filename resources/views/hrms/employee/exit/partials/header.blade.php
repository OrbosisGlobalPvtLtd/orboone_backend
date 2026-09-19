<!-- Hero Header -->
<div class="eo-header">
    <div>
        <div class="orb-page-kicker">
            <i class="fas fa-sign-out-alt"></i> HRMS • EMPLOYEE EXIT
        </div>
        <h1 class="eo-title">Exit Employees</h1>
        <p class="eo-subtitle">Track resigned, terminated, inactive employees, clearance, assets, FNF and documents.</p>
    </div>
</div>

@if (session('success'))
<div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

@if (isset($errors) && $errors->any())
<div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
    <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
</div>
@endif
