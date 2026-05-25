@extends('layouts.panel', ['active' => 'my_documents'])

@section('page_title', 'My Documents')

@section('_head')
<style>
.document-page {
    background: var(--orb-bg, #F6F7FB);
    padding: 24px;
    min-height: calc(100vh - 80px);
}
.orb-card {
    background: white;
    border: 1px solid #E7EAF3;
    border-radius: 22px;
    box-shadow: 0 4px 15px rgba(16,24,40,.03);
    padding: 24px;
}
.doc-item {
    border: 1px solid #E7EAF3;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}
.doc-item:hover {
    box-shadow: 0 4px 15px rgba(16,24,40,.05);
    border-color: #4B00E8;
}
.doc-icon {
    width: 48px;
    height: 48px;
    background: #F4F2FF;
    color: #4B00E8;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
</style>
@endsection

@section('_content')
<div class="document-page">
    <div class="orb-card">
        <h4 class="fw-bold mb-4"><i class="fas fa-folder-open text-primary me-2"></i> My Official Documents</h4>
        
        <div class="row">
            @forelse($documents as $doc)
            <div class="col-md-6">
                <div class="doc-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="doc-icon"><i class="fas fa-file-pdf"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $doc->document_title }}</h6>
                            <p class="mb-0 text-muted small">Generated: {{ $doc->created_at->format('d M, Y') }} &nbsp;|&nbsp; Doc No: {{ $doc->document_number }}</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('hrms.document-generation.self.download', $doc->id) }}" class="btn btn-light border rounded-pill px-3">
                            <i class="fas fa-download text-primary me-1"></i> Download
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open text-muted fs-1 mb-3 opacity-50"></i>
                <h5 class="text-muted">No Documents Available</h5>
                <p class="text-muted small">Official documents generated for you will appear here.</p>
            </div>
            @endforelse
        </div>
        
        @if($documents->hasPages())
        <div class="mt-4">
            {{ $documents->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
