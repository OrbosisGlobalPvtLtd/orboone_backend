@php
    $fileExists = $policy->file_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($policy->file_path);
    $isAdmin = auth()->user() && auth()->user()->isAdmin();
@endphp

<div class="col-xl-3 col-lg-4 col-md-6 mb-4 policy-card-item" data-title="{{ strtolower($policy->title) }}" data-category="{{ strtolower($policy->category ?? '') }}">
    <div class="set-card h-100" style="padding: 22px; display: flex; flex-direction: column;">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="policy-icon-box shadow-sm">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="d-flex align-items-center">
                <span class="policy-rev-pill mr-2">
                    Rev: {{ $policy->updated_at ? $policy->updated_at->format('M Y') : 'N/A' }}
                </span>
                @if($isAdmin)
                    <form action="{{ route('documents.policies.destroy', $policy->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document/policy?')" style="margin: 0; display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link text-danger p-0 ml-2" style="font-size: 14px; border: none; background: none; line-height: 1;" title="Delete Document/Policy">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Category Pill Badge & Missing File Indicator -->
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="set-badge set-badge-paid" style="font-size: 9px; font-weight: 850; letter-spacing: 0.05em; padding: 4px 10px; border-radius: 6px;">
                {{ $policy->category ?? 'Company General' }}
            </span>
            @if(!$fileExists)
                <span class="badge badge-warning" style="font-size: 9px; font-weight: 850; padding: 4px 8px; border-radius: 6px; background: rgba(245, 158, 11, 0.1); color: #D97706; border: 1px solid rgba(245, 158, 11, 0.2);">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Missing File
                </span>
            @endif
        </div>

        <h5 class="policy-card-title" title="{{ $policy->title }}">
            {{ $policy->title }}
        </h5>
        
        <!-- Split Actions -->
        <div class="policy-btn-split mt-auto">
            @if ($fileExists)
                <a href="{{ route('hrms.company-documents.preview', $policy->id) }}" target="_blank" class="policy-btn policy-btn-view">
                    <i class="fas fa-eye"></i> Preview
                </a>
                <a href="{{ route('hrms.company-documents.download', $policy->id) }}" class="policy-btn policy-btn-download">
                    <i class="fas fa-download"></i> PDF
                </a>
            @else
                <button class="policy-btn" disabled style="background: #F1F5F9; color: #94A3B8; border: 1px solid #E2E8F0; cursor: not-allowed;" title="Document file is not uploaded yet.">
                    <i class="fas fa-eye-slash"></i> Preview
                </button>
                <button class="policy-btn" disabled style="background: #F1F5F9; color: #94A3B8; border: 1px solid #E2E8F0; cursor: not-allowed;" title="Document file is not uploaded yet.">
                    <i class="fas fa-times-circle"></i> PDF
                </button>
            @endif
        </div>
    </div>
</div>
