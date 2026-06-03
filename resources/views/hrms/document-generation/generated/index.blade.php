@extends('layouts.panel', ['active' => 'document_generation'])

@section('page_title', 'Generated Documents')

@section('_head')
<style>
    .document-page {
        background: var(--orb-bg, #F6F7FB);
        padding: 24px;
        min-height: calc(100vh - 80px);
    }

    .orb-table-card {
        background: white;
        border: 1px solid #E7EAF3;
        border-radius: 22px;
        box-shadow: 0 4px 15px rgba(16, 24, 40, .03);
        overflow: hidden;
    }

    .orb-table-head {
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #E7EAF3;
    }

    .orb-table-title-wrap {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .orb-table-icon {
        width: 42px;
        height: 42px;
        background: #F4F2FF;
        color: var(--orb-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .orb-table-title-wrap h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #101828;
    }

    .orb-table-title-wrap p {
        margin: 0;
        font-size: 13px;
        color: #667085;
    }

    .orb-filter-row {
        padding: 15px 24px;
        background: #F8FAFC;
        border-bottom: 1px solid #E7EAF3;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .orb-filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .orb-filter-group label {
        font-size: 11px;
        text-transform: uppercase;
        color: #667085;
        font-weight: 600;
        margin: 0;
    }

    .orb-filter-group select,
    .orb-filter-group input {
        height: 38px;
        border-radius: 8px;
        border: 1px solid #E7EAF3;
        padding: 0 12px;
        font-size: 13px;
        min-width: 150px;
    }

    .orb-dt-toolbar {
        padding: 15px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #E7EAF3;
    }

    .orb-table-scroll {
        overflow-x: auto;
    }

    .table>thead>tr>th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        border-bottom: 1px solid #E7EAF3;
        padding: 12px 16px;
        white-space: nowrap;
    }

    .table>tbody>tr>td {
        padding: 12px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #E7EAF3;
        font-size: 14px;
        color: #101828;
    }

    .btn-orb-pill {
        background: #F4F2FF;
        color: var(--orb-primary);
        border: none;
        border-radius: 50px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-orb-pill:hover {
        background: var(--orb-primary);
        color: white;
    }

    .btn-orb-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: white;
        border: none;
        border-radius: 50px;
        padding: 8px 18px;
        font-weight: 600;
        font-size: 13px;
    }

    .btn-orb-primary:hover {
        opacity: 0.9;
        color: white;
    }

    @media(max-width: 991px) {
        .document-page {
            padding: 18px;
        }
    }

    /* Prevent dropdown clipping in responsive table scroll */
    .orb-table-scroll {
        position: relative;
        min-height: 250px; /* Ensure space for dropdowns */
    }
    
    .orb-table-scroll, .table-responsive {
        overflow: visible !important;
    }
    
    .dropdown-menu {
        z-index: 1050 !important;
    }
</style>
@endsection

@section('_content')
<div class="document-page">
    <div class="orb-table-card">
        <div class="orb-table-head">
            <div class="orb-table-title-wrap">
                <span class="orb-table-icon"><i class="fas fa-file-invoice"></i></span>
                <div>
                    <h3>Generated Documents</h3>
                    <p>All HR documents generated from templates.</p>
                </div>
            </div>

            <div class="orb-table-actions">
                @if(Route::has('hrms.document-generation.generated.create'))
                <a href="{{ route('hrms.document-generation.generated.create') }}" class="btn-orb-primary">
                    <i class="fas fa-plus"></i> Generate New
                </a>
                @endif
            </div>
        </div>

        <form method="GET" class="orb-filter-row">
            <div class="orb-filter-group">
                <label>Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->display_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="orb-filter-group">
                <label>Format</label>
                <select name="template_class" class="form-select">
                    <option value="modern" {{ request('template_class', 'modern') == 'modern' ? 'selected' : '' }}>Modern HTML</option>
                    <option value="legacy" {{ request('template_class') == 'legacy' ? 'selected' : '' }}>Legacy DOCX</option>
                    <option value="all" {{ request('template_class') == 'all' ? 'selected' : '' }}>All Formats</option>
                </select>
            </div>
            <div class="orb-filter-group">
                <label>Type</label>
                <select name="document_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="offer_letter" {{ request('document_type') == 'offer_letter' ? 'selected' : '' }}>Offer Letter</option>
                    <option value="appointment_letter" {{ request('document_type') == 'appointment_letter' ? 'selected' : '' }}>Appointment Letter</option>
                    <option value="confirmation_letter" {{ request('document_type') == 'confirmation_letter' ? 'selected' : '' }}>Confirmation Letter</option>
                    <option value="relieving_letter" {{ request('document_type') == 'relieving_letter' ? 'selected' : '' }}>Relieving Letter</option>
                    <option value="experience_certificate" {{ request('document_type') == 'experience_certificate' ? 'selected' : '' }}>Experience Certificate</option>
                    <option value="internship_certificate" {{ request('document_type') == 'internship_certificate' ? 'selected' : '' }}>Internship Certificate</option>
                </select>
            </div>
            <div class="orb-filter-group">
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="generated" {{ request('status') == 'generated' ? 'selected' : '' }}>Generated</option>
                    <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="orb-filter-group">
                <button type="submit" class="btn btn-dark" style="height: 38px; border-radius: 8px;"><i class="fas fa-filter"></i> Filter</button>
                @if(request()->anyFilled(['employee_id', 'document_type', 'status', 'template_class']))
                <a href="{{ route('hrms.document-generation.generated.index') }}" class="btn btn-light border mt-1" style="height: 38px; border-radius: 8px;">Reset</a>
                @endif
            </div>
        </form>

        <div class="orb-table-scroll">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Doc No.</th>
                        <th>Employee</th>
                        <th>Template</th>
                        <th>Template Type</th>
                        <th>Status</th>
                        <th>PDF Status</th>
                        <th>Generated By</th>
                        <th>File Type</th>
                        <th>Generated Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr>
                        <td class="fw-bold">{{ $doc->document_number }}</td>
                        <td>
                            @if($doc->employee)
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-weight:bold;">
                                    {{ substr($doc->employee->display_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $doc->employee->display_name }}</div>
                                    <div class="text-muted" style="font-size:11px;">{{ $doc->employee->employee_code }}</div>
                                </div>
                            </div>
                            @else
                            <span class="text-muted">Candidate</span>
                            @endif
                        </td>
                        <td>{{ $doc->template->name ?? ucwords(str_replace('_', ' ', $doc->document_type)) }} <small class="text-muted d-block">{{ $doc->template_version ?: 'v1' }}</small></td>
                        <td>
                            @if(($doc->template_type ?? ($doc->template->template_type ?? 'html')) === 'docx')
                                <span class="badge bg-secondary border"><i class="fas fa-file-word me-1"></i> Legacy DOCX</span>
                            @else
                                <span class="badge bg-primary-soft text-primary border border-primary"><i class="fas fa-code me-1"></i> HTML</span>
                            @endif
                        </td>
                        <td>
                            @if($doc->status == 'sent')
                            <span class="badge bg-success rounded-pill px-2">Sent</span>
                            @elseif($doc->status == 'reviewed')
                            <span class="badge bg-info rounded-pill px-2">Reviewed</span>
                            @elseif($doc->status == 'generated')
                            <span class="badge bg-primary rounded-pill px-2">Generated</span>
                            @elseif($doc->status == 'cancelled')
                            <span class="badge bg-danger rounded-pill px-2">Cancelled</span>
                            @else
                            <span class="badge bg-secondary rounded-pill px-2">{{ ucfirst($doc->status) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(($doc->template_type ?? ($doc->template->template_type ?? 'html')) !== 'docx')
                                <span class="badge bg-success rounded-pill px-2">Generated</span>
                            @else
                                @if(($doc->pdf_status ?? '') === 'converted')
                                <span class="badge bg-success rounded-pill px-2">Available</span>
                                @elseif(($doc->pdf_status ?? '') === 'libreoffice_missing')
                                <span class="badge bg-warning rounded-pill px-2">LibreOffice Missing</span>
                                @elseif(($doc->pdf_status ?? '') === 'conversion_failed')
                                <span class="badge bg-danger rounded-pill px-2">Failed</span>
                                @else
                                <span class="badge bg-secondary rounded-pill px-2">N/A</span>
                                @endif
                            @endif
                        </td>
                        <td>{{ $doc->generatedBy->name ?? '-' }}</td>
                        <td>
                            @if(($doc->template_type ?? ($doc->template->template_type ?? 'html')) !== 'docx')
                                <span class="badge bg-info-soft text-info border border-info px-2" style="background-color: #e0f2fe; color: #0369a1; font-weight: bold;">PDF</span>
                            @else
                                @if($doc->generated_docx_path && $doc->generated_pdf_path)
                                DOCX + PDF
                                @elseif($doc->generated_docx_path)
                                DOCX
                                @elseif($doc->generated_pdf_path || $doc->pdf_path)
                                PDF
                                @else
                                -
                                @endif
                            @endif
                        </td>
                        <td>{{ $doc->created_at->format('d M, Y') }}</td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                    Action
                                </button>
                                @php
                                    $pdfExists = false;
                                    if ($doc->generated_pdf_path) {
                                        $pdfExists = \Illuminate\Support\Facades\Storage::disk('private')->exists($doc->generated_pdf_path);
                                    } elseif ($doc->pdf_path) {
                                        $pdfExists = \Illuminate\Support\Facades\Storage::disk('private')->exists($doc->pdf_path);
                                    }
                                    $isHtml = ($doc->template_type ?? ($doc->template->template_type ?? 'html')) !== 'docx';
                                @endphp
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; z-index: 1060;">
                                    @if(Route::has('hrms.document-generation.generated.stream'))
                                        @if($pdfExists)
                                            <li><a class="dropdown-item py-2" href="#" onclick="previewPdf('{{ route('hrms.document-generation.generated.stream', $doc->id) }}'); return false;"><i class="fas fa-eye text-primary me-2"></i> Preview</a></li>
                                        @else
                                            <li><a class="dropdown-item py-2 text-muted disabled" href="#" title="PDF file is missing on server" onclick="return false;" style="pointer-events: auto; cursor: not-allowed;"><i class="fas fa-eye text-secondary me-2"></i> Preview <small class="text-danger">(Missing)</small></a></li>
                                        @endif
                                    @endif

                                    @if(Route::has('hrms.document-generation.generated.download'))
                                        @if($pdfExists)
                                            <li><a class="dropdown-item py-2" href="{{ route('hrms.document-generation.generated.download', $doc->id) }}"><i class="fas fa-download text-success me-2"></i> Download PDF</a></li>
                                        @else
                                            <li><a class="dropdown-item py-2 text-muted disabled" href="#" title="PDF file is missing on server" onclick="return false;" style="pointer-events: auto; cursor: not-allowed;"><i class="fas fa-download text-secondary me-2"></i> Download PDF <small class="text-danger">(Missing)</small></a></li>
                                        @endif
                                    @endif
                                    @if(Route::has('hrms.document-generation.generated.download-docx') && $doc->generated_docx_path)
                                    <li><a class="dropdown-item py-2" href="{{ route('hrms.document-generation.generated.download-docx', $doc->id) }}"><i class="fas fa-file-word text-primary me-2"></i> Download DOCX</a></li>
                                    @endif

                                    @if(Route::has('hrms.document-generation.generated.email'))
                                        @if($pdfExists)
                                            <li><a class="dropdown-item py-2" href="#" onclick="openEmailModal({{ $doc->id }}); return false;"><i class="fas fa-envelope text-warning me-2"></i> Email Document</a></li>
                                        @else
                                            <li><a class="dropdown-item py-2 text-muted disabled" href="#" title="PDF file is missing on server" onclick="return false;" style="pointer-events: auto; cursor: not-allowed;"><i class="fas fa-envelope text-secondary me-2"></i> Email Document <small class="text-danger">(Missing)</small></a></li>
                                        @endif
                                    @endif

                                    @if(!$pdfExists && $isHtml && Route::has('hrms.document-generation.generated.regenerate'))
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('hrms.document-generation.generated.regenerate', $doc->id) }}" method="POST" onsubmit="return confirm('Regenerate this missing PDF?');">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 text-info"><i class="fas fa-sync-alt me-2"></i> Retry/Regenerate PDF</button>
                                        </form>
                                    </li>
                                    @endif

                                    @if($doc->status != 'cancelled')
                                    @if(Route::has('hrms.document-generation.generated.cancel'))
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('hrms.document-generation.generated.cancel', $doc->id) }}" method="POST" onsubmit="return confirm('Cancel this document?');">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 text-danger"><i class="fas fa-times-circle me-2"></i> Cancel</button>
                                        </form>
                                    </li>
                                    @endif
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">No generated documents found matching criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $documents->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); color: white; border-radius: 24px 24px 0 0; padding: 20px;">
                <h5 class="modal-title m-0 fw-bold">Send Document via Email</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="emailForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted text-uppercase" style="font-size:11px; font-weight:600;">Recipient Email</label>
                        <input type="email" name="email_to" class="form-control" style="height:42px; border-radius:10px;" required placeholder="employee@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted text-uppercase" style="font-size:11px; font-weight:600;">Subject</label>
                        <input type="text" name="email_subject" class="form-control" style="height:42px; border-radius:10px;" required value="HR Document from Orbosis">
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted text-uppercase" style="font-size:11px; font-weight:600;">Message Body</label>
                        <textarea name="email_body" class="form-control" rows="4" style="border-radius:10px;" required>Please find your attached HR document.</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3" style="border-radius: 0 0 24px 24px; border-top: 1px solid #E7EAF3;">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" style="background: var(--orb-primary); border: none;">Send Email <i class="fas fa-paper-plane ms-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; border: none; overflow: hidden;">
            <div class="modal-header bg-light p-3 border-bottom">
                <h5 class="modal-title m-0 fw-bold">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="height: 70vh;">
                <iframe id="previewIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEmailModal(id) {
        let url = '{{ route("hrms.document-generation.generated.email", ":id") }}';
        url = url.replace(':id', id);
        document.getElementById('emailForm').action = url;
        var modal = new bootstrap.Modal(document.getElementById('emailModal'));
        modal.show();
    }

    function previewPdf(url) {
        document.getElementById('previewIframe').src = url;
        var modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    }
</script>
@endpush