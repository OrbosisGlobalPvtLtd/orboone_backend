@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" style="border-radius: 12px; font-size: 13.5px; font-weight: 600; background: #ECFDF5; color: #065F46; border-left: 4px solid #10B981 !important; padding: 12px 18px;">
        <i class="fas fa-check-circle mr-2" style="font-size: 16px;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@php
    $singleErrorMessage = session('error');
    if (!$singleErrorMessage && isset($errors) && $errors->any()) {
        $singleErrorMessage = $errors->first();
    }
@endphp

@if(!empty($singleErrorMessage))
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" style="border-radius: 12px; font-size: 13.5px; font-weight: 600; background: #FEF2F2; color: #991B1B; border-left: 4px solid #EF4444 !important; padding: 12px 18px;">
        <i class="fas fa-exclamation-circle mr-2" style="font-size: 16px;"></i>
        <span>{{ $singleErrorMessage }}</span>
    </div>
@endif

