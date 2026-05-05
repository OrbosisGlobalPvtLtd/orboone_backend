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

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
    </div>
@endif
