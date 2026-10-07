@php
    $fileExists = $policy->file_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($policy->file_path);
    $isAdmin = auth()->user() && auth()->user()->isAdmin();
@endphp

<div class="policy-card-item" data-title="{{ strtolower($policy->title) }}" data-category="{{ strtolower($policy->category ?? '') }}">
    <div class="policy-card">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="policy-icon-box">
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
                        <button type="submit" class="btn btn-link text-danger p-0 ml-1" style="font-size: 13px; border: none; background: none; line-height: 1; opacity: 0.75; transition: opacity 0.15s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.75'" title="Delete Document/Policy">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Category Pill Badge & Missing File Indicator -->
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span style="font-size: 10px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; padding: 4px 10px; border-radius: 6px; background: rgba(75, 0, 232, 0.08); color: var(--orb-primary, #4B00E8); border: 1px solid rgba(75, 0, 232, 0.12);">
                {{ $policy->category ?? 'Company General' }}
            </span>
            @if(!$fileExists)
                <span style="font-size: 10px; font-weight: 750; padding: 4px 8px; border-radius: 6px; background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fas fa-exclamation-triangle"></i> Missing File
                </span>
            @endif
        </div>

        <h5 class="policy-card-title" title="{{ $policy->title }}">
            {{ $policy->title }}
        </h5>
        
        <!-- Split Actions -->
        <div class="policy-btn-split">
            @if ($fileExists)
                <a href="{{ route('hrms.company-documents.preview', $policy->id) }}" target="_blank" class="policy-btn policy-btn-view">
                    <i class="fas fa-eye"></i> Preview
                </a>
                <a href="{{ route('hrms.company-documents.download', $policy->id) }}" class="policy-btn policy-btn-download">
                    <i class="fas fa-download"></i> PDF
                </a>
            @else
                <button class="policy-btn policy-btn-disabled" disabled title="Document file is not uploaded yet.">
                    <i class="fas fa-eye-slash"></i> Preview
                </button>
                <button class="policy-btn policy-btn-disabled" disabled title="Document file is not uploaded yet.">
                    <i class="fas fa-times-circle"></i> PDF
                </button>
            @endif
        </div>
    </div>
</div>

