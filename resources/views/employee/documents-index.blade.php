@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'employee-documents'])

@section('_content')
<style>
    :root {
        --doc-primary: #4b00e8;
        --doc-secondary: #0099cc;
        --doc-success: #16a34a;
        --doc-danger: #dc2626;
        --doc-warning: #d97706;
        --card-radius: 22px;
    }

    body { background: #f8fafc; }

    /* ── Hero Header ── */
.doc-hero {
    background: linear-gradient(135deg, #0d0d2b 0%, #8600ee 60%, #ffb101 100%);
    padding: 50px 40px 90px;
    border-radius: 0 0 50px 50px;
    color: white;
    position: relative;
    overflow: hidden;
}
    .doc-hero::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 280px; height: 280px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .doc-hero::after {
        content: '';
        position: absolute;
        bottom: -60px; left: -60px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.03);
        border-radius: 50%;
    }

    .compliance-glass {
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 20px;
        padding: 22px;
        transition: transform 0.3s;
    }

    /* ── Cards ── */
    .doc-panel {
        background: white;
        border-radius: var(--card-radius);
        box-shadow: 0 15px 40px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.03);
    }
    .doc-panel .panel-head {
        padding: 22px 28px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 800;
        font-size: 0.95rem;
        color: #1e293b;
    }
    .panel-head-icon {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, var(--doc-primary), #8b00ff);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 0.9rem;
        flex-shrink: 0;
    }
    .doc-panel .panel-body { padding: 24px 28px 28px; }

    /* ── Labels & Inputs ── */
    .doc-label {
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 8px;
        display: block;
    }
    .doc-control {
        border-radius: 14px;
        height: 52px;
        border: 2px solid #f1f5f9;
        background: #f8fafc;
        font-weight: 600;
        padding: 0 18px;
        transition: all 0.3s;
        width: 100%;
        color: #1e293b;
        font-size: 0.9rem;
    }
    .doc-control:focus {
        border-color: var(--doc-primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(75,0,232,0.08);
        outline: none;
    }

    /* ── Upload Zone ── */
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 18px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        background: #f8fafc;
        transition: all 0.3s;
    }
    .upload-zone:hover {
        border-color: var(--doc-primary);
        background: #f5f0ff;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(75,0,232,0.08);
    }
    .upload-zone .upload-icon {
        font-size: 2.5rem;
        background: linear-gradient(135deg, var(--doc-primary), #0099cc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 12px;
        display: block;
    }
    .upload-zone.drag-active {
        border-color: var(--doc-primary);
        background: #eef0ff;
        box-shadow: 0 0 0 6px rgba(75,0,232,0.1);
    }

    /* ── Submit Button ── */
    .btn-submit-doc {
        background: linear-gradient(135deg, var(--doc-primary), #0099cc);
        color: white;
        border: none;
        border-radius: 14px;
        padding: 16px;
        font-weight: 800;
        letter-spacing: 0.5px;
        width: 100%;
        box-shadow: 0 10px 25px rgba(75,0,232,0.25);
        transition: all 0.3s;
        cursor: pointer;
        font-size: 0.9rem;
    }
    .btn-submit-doc:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(75,0,232,0.35);
        filter: brightness(1.1);
    }

    /* ── Table ── */
    .doc-table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 1.2px;
        padding: 16px 18px;
        border: none;
    }
    .doc-table tbody td {
        padding: 18px 18px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .doc-table tbody tr:hover { background: #fafbff; }
    .doc-table tbody tr:last-child td { border-bottom: none; }

    /* ── Status Badge ── */
    .status-chip {
        padding: 6px 16px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.63rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .chip-verified, .chip-approved { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .chip-pending  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .chip-rejected { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    /* ── Doc Icon ── */
    .doc-icon-box {
        width: 46px; height: 46px;
        border-radius: 14px;
        background: #eef0ff;
        display: flex; align-items: center; justify-content: center;
        color: var(--doc-primary);
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    /* ── Alert Styles ── */
    .alert-orb {
        border-radius: 14px;
        border: none;
        font-weight: 600;
        font-size: 0.88rem;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .alert-success-orb { background: #f0fdf4; color: #16a34a; }
    .alert-error-orb   { background: #fef2f2; color: #dc2626; }

    /* ── Responsive ── */
    .row-offset { margin-top: -55px; }
    @media (max-width: 768px) {
        .doc-hero { padding: 35px 20px 80px; }
        .doc-panel .panel-body { padding: 18px; }
        .panel-head { padding: 16px 18px; }
    }
</style>

<div class="container-fluid p-0">

    {{-- ── Hero Header ── --}}
    <div class="doc-hero">
        <div class="row align-items-center">
            <div class="col-lg-7 col-md-6">
                <p class="text-uppercase font-weight-bold mb-2" style="font-size: 0.7rem; letter-spacing: 2px; opacity: 0.6;">
                    <i class="fas fa-folder-open mr-2"></i> Document Management
                </p>
                <h2 class="font-weight-bold mb-2" style="font-size: 2rem; letter-spacing: -0.5px;">Documents Hub</h2>
                <p class="mb-0" style="opacity: 0.65; font-size: 0.92rem;">
                    Upload, track, and manage your official documents for HR compliance.
                </p>
            </div>
            <div class="col-lg-5 col-md-6 mt-4 mt-md-0">
                @php
                    $mandatoryCount = $types->where('is_mandatory', true)->count();
                    $uploadedMandatory = $documents
                        ->whereIn('status', ['verified', 'approved'])
                        ->whereIn('document_type_id', $types->where('is_mandatory', true)->pluck('id'))
                        ->unique('document_type_id')->count();
                    $progress = $mandatoryCount > 0 ? round(($uploadedMandatory / $mandatoryCount) * 100) : 0;
                    $progressColor = $progress >= 100 ? '#4ade80' : ($progress >= 50 ? '#fbbf24' : '#f87171');
                @endphp
                <div class="compliance-glass">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="d-block small font-weight-bold mb-1" style="opacity: 0.7; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">Compliance Progress</span>
                            <h3 class="font-weight-bold mb-0">{{ $progress }}% Complete</h3>
                        </div>
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-medal fa-lg" style="opacity: 0.7;"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 20px; background: rgba(255,255,255,0.15);">
                        <div class="progress-bar" style="width: {{ $progress }}%; background: {{ $progressColor }}; border-radius: 20px;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2" style="font-size: 0.72rem; opacity: 0.65;">
                        <span>{{ $uploadedMandatory }}/{{ $mandatoryCount }} mandatory docs verified</span>
                        <span>{{ $documents->count() }} total uploaded</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Content (pulls up over hero) ── --}}
    <div class="container-fluid px-3 px-md-4 row-offset">
        <div class="row">

            {{-- ── LEFT: Upload Form ── --}}
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="doc-panel shadow-lg">
                    <div class="panel-head">
                        <div class="panel-head-icon"><i class="fas fa-file-import"></i></div>
                        Submit New Document
                    </div>
                    <div class="panel-body">

                        {{-- Alerts --}}
                        @if(session('success'))
                            <div class="alert-orb alert-success-orb mb-4">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert-orb alert-error-orb mb-4">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('employee.documents.upload') }}" enctype="multipart/form-data">
                            @csrf

                            {{-- Employee Select --}}
                            <div class="mb-4">
                                <label class="doc-label"><i class="fas fa-user mr-1"></i> Select Employee</label>
                                <select name="user_id" class="doc-control" required style="height:52px;">
                                    <option value="" disabled selected>-- Search Employee --</option>
                                    @foreach($allEmployees as $emp)
                                        <option value="{{ $emp->user->id }}"
                                            {{ auth()->id() == $emp->user->id ? 'selected' : '' }}>
                                            {{ $emp->name }} ({{ $emp->employee_id ?? 'EMP-'.$emp->id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                            </div>

                            {{-- Document Type --}}
                            <div class="mb-4">
                                <label class="doc-label"><i class="fas fa-tag mr-1"></i> Document Category</label>
                                <select name="document_type_id" class="doc-control" required style="height:52px;">
                                    <option value="" disabled selected>-- Select Type --</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}">
                                            {{ $type->name }}{{ $type->is_mandatory ? ' ★ Required' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('document_type_id') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                            </div>

                            {{-- File Upload Zone --}}
                            <div class="mb-4">
                                <label class="doc-label"><i class="fas fa-paperclip mr-1"></i> Upload File</label>
                                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <h6 class="font-weight-bold text-dark mb-1" id="fileLabel">Drop your file here</h6>
                                    <p class="small text-muted mb-0">or click to browse · PDF, JPG, PNG · Max 5MB</p>
                                    <input type="file" name="file" id="fileInput" hidden
                                           accept=".pdf,.jpg,.jpeg,.png,.docx"
                                           onchange="handleFileChange(this)">
                                </div>
                                @error('file') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                            </div>

                            <button type="submit" class="btn-submit-doc">
                                <i class="fas fa-paper-plane mr-2"></i> Submit for Verification
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── RIGHT: Document History ── --}}
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="doc-panel shadow-lg">
                    <div class="panel-head">
                        <div class="panel-head-icon" style="background: linear-gradient(135deg, #0099cc, #1560ab);">
                            <i class="fas fa-history"></i>
                        </div>
                        <span class="flex-grow-1">Submission History</span>
                        <span class="badge badge-pill" style="background:#f1f5f9; color:#475569; font-size:0.75rem; padding:6px 14px; font-weight:700;">
                            {{ $documents->count() }} records
                        </span>
                    </div>
                    <div class="panel-body" style="padding-top:10px;">

                        <div class="table-responsive">
                            <table class="table doc-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $doc)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="doc-icon-box mr-3">
                                                        @php
                                                            $ext = pathinfo($doc->file_path ?? '', PATHINFO_EXTENSION);
                                                            $icon = in_array($ext, ['jpg','jpeg','png']) ? 'fa-file-image' : 'fa-file-pdf';
                                                        @endphp
                                                        <i class="fas {{ $icon }}"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold text-dark" style="font-size:0.9rem;">
                                                            {{ $doc->type->name ?? $doc->document_type ?? 'Document' }}
                                                        </div>
                                                        <div class="small text-muted">
                                                            <i class="fas fa-user-circle mr-1"></i> {{ $doc->user->name ?? 'N/A' }}
                                                        </div>
                                                        @if($doc->status == 'rejected' && $doc->rejection_reason)
                                                            <div class="small mt-1" style="color:#dc2626;">
                                                                <i class="fas fa-comment-times mr-1"></i>
                                                                <em>{{ Str::limit($doc->rejection_reason, 50) }}</em>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $s = $doc->status;
                                                    $chipClass = ($s == 'verified' || $s == 'approved') ? 'chip-verified' : ($s == 'rejected' ? 'chip-rejected' : 'chip-pending');
                                                    $chipIcon  = ($s == 'verified' || $s == 'approved') ? 'fa-check-double' : ($s == 'rejected' ? 'fa-times-circle' : 'fa-hourglass-half');
                                                @endphp
                                                <span class="status-chip {{ $chipClass }}">
                                                    <i class="fas {{ $chipIcon }}"></i> {{ ucfirst($s) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="small text-muted font-weight-bold">
                                                    {{ $doc->created_at->format('d M, Y') }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div class="d-flex justify-content-end align-items-center" style="gap:8px;">
                                                    @if($doc->file_path)
                                                        @php
                                                            $cleanPath = str_replace('public/', '', $doc->file_path);
                                                            $finalUrl = Str::startsWith($cleanPath, 'uploads/') ? asset($cleanPath) : asset("storage/{$cleanPath}");
                                                        @endphp


                                                        <a href="{{ $finalUrl }}" target="_blank"
                                                           class="btn btn-sm btn-outline-primary font-weight-bold"
                                                           style="border-radius:10px; font-size:0.75rem; padding:6px 14px;"
                                                           title="View Document">
                                                            <i class="fas fa-eye mr-1"></i> View
                                                        </a>
                                                    @endif

                                                    @if(!in_array($doc->status, ['verified','approved']))
                                                        <form action="{{ route('employee.documents.destroy', $doc->id) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Remove this document? This cannot be undone.');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger font-weight-bold"
                                                                    style="border-radius:10px; font-size:0.75rem; padding:6px 12px;"
                                                                    title="Delete Document">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="small text-muted" title="Approved — cannot delete" style="font-size:0.72rem;">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div style="opacity: 0.25; font-size: 3rem;" class="mb-3">
                                                    <i class="fas fa-folder-open"></i>
                                                </div>
                                                <h6 class="text-muted font-weight-bold">No documents submitted yet.</h6>
                                                <p class="small text-muted">Upload your first document using the form on the left.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function handleFileChange(input) {
        const label = document.getElementById('fileLabel');
        const zone = document.getElementById('uploadZone');
        if (input.files[0]) {
            label.textContent = input.files[0].name;
            zone.style.borderColor = 'var(--doc-primary)';
            zone.style.background = '#f5f0ff';
        }
    }

    // Drag & Drop support
    const zone = document.getElementById('uploadZone');
    if (zone) {
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-active'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-active'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-active');
            const file = e.dataTransfer.files[0];
            if (file) {
                const input = document.getElementById('fileInput');
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                handleFileChange(input);
            }
        });
    }
</script>
@endsection
