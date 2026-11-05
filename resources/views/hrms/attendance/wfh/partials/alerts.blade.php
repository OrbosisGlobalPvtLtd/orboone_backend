@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius: 14px; background: #ECFDF3; color: #027A48;">
    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning border-0 shadow-sm mb-3" style="border-radius: 14px; background: #FFFAEB; color: #B54708;">
    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
</div>
@endif

@if(session('info'))
<div class="alert alert-info border-0 shadow-sm mb-3" style="border-radius: 14px; background: #F0F9FF; color: #026AA2;">
    <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 14px; background: #FEF3F2; color: #B42318;">
    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
</div>
@endif

@if(isset($errors) && $errors->any())
<div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 14px; background: #FEF3F2; color: #B42318;">
    <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
</div>
@endif
