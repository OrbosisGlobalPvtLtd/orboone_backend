@extends('layouts.panel', ['active' => 'document_generation'])

@section('page_title', 'Document Generation')

@section('_head')
<style>
.document-page {
    background: var(--orb-bg, #F6F7FB);
    padding: 24px;
    min-height: calc(100vh - 80px);
}
.orb-hero {
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    border-radius: 26px;
    padding: 30px 40px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    box-shadow: 0 10px 25px rgba(75, 0, 232, 0.2);
}
.orb-kicker {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.orb-hero h1 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 5px 0;
}
.orb-hero p {
    margin: 0;
    opacity: 0.85;
    font-size: 14px;
}
.orb-hero-actions {
    display: flex;
    gap: 12px;
}
.btn-orb-white {
    background: white;
    color: var(--orb-primary);
    border: none;
    border-radius: 50px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}
.btn-orb-white:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.btn-orb-soft {
    background: rgba(255,255,255,0.15);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 14px;
    backdrop-filter: blur(5px);
    transition: all 0.3s;
}
.btn-orb-soft:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-2px);
}
.stat-card {
    background: white;
    border-radius: 18px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(16,24,40,.03);
    border: 1px solid #E7EAF3;
    border-bottom: 3px solid var(--orb-primary);
    margin-bottom: 24px;
    transition: all 0.3s;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(16,24,40,.08);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #F4F2FF;
    color: var(--orb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.stat-info h4 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    color: #101828;
}
.stat-info p {
    font-size: 12px;
    text-transform: uppercase;
    color: #667085;
    margin: 0;
    font-weight: 600;
    letter-spacing: 0.5px;
}
@media(max-width: 991px) {
    .document-page { padding: 18px; }
    .orb-hero { flex-direction: column; text-align: center; gap: 20px; padding: 25px; }
    .orb-kicker { justify-content: center; }
}
@media(max-width: 575px) {
    .document-page { padding: 12px; }
}
</style>
@endsection

@section('_content')
<div class="document-page">
    <div class="orb-hero">
        <div>
            <div class="orb-kicker">
                <i class="fas fa-file-signature"></i>
                HR Document Center
            </div>
            <h1>Document Generation</h1>
            <p>Create, preview, approve, download and send HR letters dynamically.</p>
        </div>

        <div class="orb-hero-actions">
            @if(Route::has('hrms.document-generation.generated.index'))
            <a href="{{ route('hrms.document-generation.generated.index') }}" class="btn-orb-soft">
                <i class="fas fa-file-invoice"></i> Generated Documents
            </a>
            @endif
            @if(Route::has('hrms.document-generation.templates.index'))
            <a href="{{ route('hrms.document-generation.templates.index') }}" class="btn-orb-soft">
                <i class="fas fa-file-code"></i> Templates
            </a>
            @endif
            @if(Route::has('hrms.document-generation.generated.create'))
            <a href="{{ route('hrms.document-generation.generated.create') }}" class="btn-orb-white">
                <i class="fas fa-magic"></i> Generate Document
            </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="border-bottom-color: var(--orb-primary);">
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                <div class="stat-info">
                    <h4>{{ $generatedDocuments }}</h4>
                    <p>Total Generated</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="border-bottom-color: #F59E0B;">
                <div class="stat-icon" style="background:#FFFBEB; color:#F59E0B;"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <h4>{{ $pendingReview }}</h4>
                    <p>Pending Review</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="border-bottom-color: #10B981;">
                <div class="stat-icon" style="background:#ECFDF5; color:#10B981;"><i class="fas fa-paper-plane"></i></div>
                <div class="stat-info">
                    <h4>{{ $sentDocuments }}</h4>
                    <p>Sent Documents</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="border-bottom-color: #6366F1;">
                <div class="stat-icon" style="background:#EEF2FF; color:#6366F1;"><i class="fas fa-layer-group"></i></div>
                <div class="stat-info">
                    <h4>{{ $activeTemplates }}</h4>
                    <p>Active Templates</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Documents can go here using standard card -->
    <div class="card" style="border-radius: 22px; border: 1px solid #E7EAF3; box-shadow: 0 4px 15px rgba(16,24,40,.03);">
        <div class="card-header bg-white" style="border-radius: 22px 22px 0 0; padding: 20px; border-bottom: 1px solid #E7EAF3; display: flex; justify-content: space-between; align-items: center;">
            <h5 class="mb-0" style="font-weight: 700; color: #101828;">Recent Generated Documents</h5>
            <div>
                @if(Route::has('hrms.document-generation.generated.index'))
                <a href="{{ route('hrms.document-generation.generated.index') }}" class="btn btn-sm btn-light rounded-pill px-3 me-2 border">View All</a>
                @endif
                @if(Route::has('hrms.document-generation.generated.create'))
                <a href="{{ route('hrms.document-generation.generated.create') }}" class="btn btn-sm rounded-pill px-3 text-white" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));">Generate New</a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead style="background: #F8FAFC;">
                        <tr>
                            <th class="px-4 py-3 text-uppercase text-muted" style="font-size:11px; font-weight:600;">Doc No</th>
                            <th class="py-3 text-uppercase text-muted" style="font-size:11px; font-weight:600;">Employee</th>
                            <th class="py-3 text-uppercase text-muted" style="font-size:11px; font-weight:600;">Type</th>
                            <th class="py-3 text-uppercase text-muted" style="font-size:11px; font-weight:600;">Status</th>
                            <th class="px-4 py-3 text-uppercase text-muted text-end" style="font-size:11px; font-weight:600;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDocuments as $doc)
                        <tr>
                            <td class="px-4 py-3 fw-bold">{{ $doc->document_number }}</td>
                            <td class="py-3">{{ optional($doc->employee)->display_name ?? 'N/A' }}</td>
                            <td class="py-3"><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', ucwords($doc->document_type)) }}</span></td>
                            <td class="py-3">
                                @if($doc->status == 'sent')
                                    <span class="badge bg-success rounded-pill px-2">Sent</span>
                                @elseif($doc->status == 'reviewed')
                                    <span class="badge bg-info rounded-pill px-2">Reviewed</span>
                                @elseif($doc->status == 'generated')
                                    <span class="badge bg-primary rounded-pill px-2">Generated</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2">{{ ucfirst($doc->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end text-muted">{{ $doc->created_at->format('d M, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No documents generated yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
