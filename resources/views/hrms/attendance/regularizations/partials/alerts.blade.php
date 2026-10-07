@if(session('success') || session('status'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') ?: session('status') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
@endif
@if(isset($errors) && $errors->any())
    <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
    </div>
@endif
