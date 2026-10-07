<!-- Slim & Compact Clearance Status Bar -->
<div class="card border-0 shadow-sm mb-3 eo-overview-card" style="border-radius:14px; background: #FAF5FF; border: 1px solid #E9D5FF;">
    <div class="card-body p-2 px-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center mb-1 mb-sm-0">
                <i class="fas fa-shield-alt text-primary mr-2"></i>
                <strong style="font-size:12.5px;" class="text-dark">Clearance Overview:</strong>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center eo-overview-pills" style="font-size:11.5px;">
                <span class="d-inline-flex align-items-center gap-1">Asset: <span class="eo-pill {{ $statusPill($assetStatus) }} js-top-pill-asset-{{ $employee->id }}">{{ ucfirst(str_replace('_', ' ', $assetStatus)) }}</span></span>
                <span class="d-inline-flex align-items-center gap-1">FNF: <span class="eo-pill {{ $statusPill($fnfStatus) }} js-top-pill-fnf-{{ $employee->id }}">{{ ucfirst(str_replace('_', ' ', $fnfStatus)) }}</span></span>
                <span class="d-inline-flex align-items-center gap-1">Docs: <span class="eo-pill {{ $statusPill($documentStatus) }} js-top-pill-document-{{ $employee->id }}">{{ ucfirst(str_replace('_', ' ', $documentStatus)) }}</span></span>
                <span class="d-inline-flex align-items-center gap-1">Handover: <span class="eo-pill {{ $statusPill($handoverStatus) }} js-top-pill-handover-{{ $employee->id }}">{{ ucfirst(str_replace('_', ' ', $handoverStatus)) }}</span></span>
            </div>
        </div>
    </div>
</div>
