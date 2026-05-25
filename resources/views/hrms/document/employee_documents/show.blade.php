@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <a href="{{ route('documents.employee.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            <h4 class="fw-bold text-dark mt-3">Manage Digital File: {{ $employee->name }}</h4>
        </div>
        <div class="col-md-6 text-md-end">
            <span class="badge bg-light text-dark border p-2 px-3">
                <i class="fas fa-id-badge me-1"></i> {{ $employee->employeeDetail->identity_number ?? 'EMP-'.$employee->id }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        {{-- LEFT COLUMN: Employee Info & Upload Form --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i> Employee Summary</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="list-group list-group-flush small">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Department</span>
                            <span class="fw-bold">{{ $employee->department->name ?? 'N/A' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Position</span>
                            <span class="fw-bold">{{ $employee->position->name ?? 'N/A' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Email</span>
                            <span class="fw-bold">{{ $employee->user->email ?? 'N/A' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Experience Status</span>
                            <span class="badge {{ $employee->employment_type == 'Experienced' ? 'bg-info' : 'bg-secondary' }}">
                                {{ $employee->employment_type ?? 'Standard' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-upload me-2 text-primary"></i> Upload New Documents</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('documents.employee.store', $employee->id) }}" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Document Type <span class="text-danger">*</span></label>
                                <select name="document_type_id" class="form-control form-control-sm select2" required style="width: 100%;">
                                    <option value="">-- Select Category --</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12"><hr class="my-1"></div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Passport Photo <span class="text-danger">*</span></label>
                                <input type="file" name="passport_photo" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Aadhar Card <span class="text-danger">*</span></label>
                                <input type="file" name="aadhar_card" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">PAN Card <span class="text-danger">*</span></label>
                                <input type="file" name="pan_card" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Bank Proof <span class="text-danger">*</span></label>
                                <input type="file" name="bank_proof" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Educational Docs <span class="text-danger">*</span></label>
                                <input type="file" name="educational_documents" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-12 mt-3 mb-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="experiencedToggle">
                                    <label class="form-check-label small fw-bold" for="experiencedToggle">Experienced Employee? (Show extra fields)</label>
                                </div>
                            </div>

                            <div id="experienceFields" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Offer Letter</label>
                                        <input type="file" name="offer_letter" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Experience Letter</label>
                                        <input type="file" name="experience_letter" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Salary Slips (3M)</label>
                                        <input type="file" name="salary_slip_3_months" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Relieving Letter</label>
                                        <input type="file" name="relieving_letter" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">NDA / MOU Agreement</label>
                                        <input type="file" name="nda_agreement_mou" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary w-100 rounded-pill shadow-sm py-2 fw-bold" type="submit">
                                <i class="fas fa-cloud-upload-alt me-2"></i> Submit Document Set
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Uploaded Files Listing --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i> Uploaded Documents & History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small fw-bold">
                                <tr>
                                    <th class="ps-3">Document Set</th>
                                    <th>Status</th>
                                    <th>Uploaded</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $doc->type->name ?? $doc->document_type ?? 'Onboarding Files' }}</div>
                                            <div class="text-muted x-small">Files: Aadhar, PAN, Bank, Photo...</div>
                                        </td>
                                        <td>
                                            @if($doc->status === 'verified')
                                                <span class="badge bg-success-soft text-success px-2 py-1" style="background: rgba(25, 135, 84, 0.1);">Verified</span>
                                            @elseif($doc->status === 'rejected')
                                                <span class="badge bg-danger-soft text-danger px-2 py-1" style="background: rgba(220, 53, 69, 0.1);">Rejected</span>
                                            @else
                                                <span class="badge bg-warning-soft text-warning px-2 py-1" style="background: rgba(255, 193, 7, 0.1);">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small">{{ optional($doc->created_at)->format('d M Y') }}</div>
                                            <div class="x-small text-muted">By: {{ $doc->uploaded_by_user->name ?? 'System' }}</div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-toggle="dropdown">
                                                    Manage
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                                    <h6 class="dropdown-header">View Documents</h6>
                                                    @if($doc->passport_photo) <a class="dropdown-item small" href="{{ asset('storage/'.$doc->passport_photo) }}" target="_blank"><i class="fas fa-image me-2"></i>Photo</a> @endif
                                                    @if($doc->aadhar_card) <a class="dropdown-item small" href="{{ asset('storage/'.$doc->aadhar_card) }}" target="_blank"><i class="fas fa-id-card me-2"></i>Aadhar</a> @endif
                                                    @if($doc->pan_card) <a class="dropdown-item small" href="{{ asset('storage/'.$doc->pan_card) }}" target="_blank"><i class="fas fa-credit-card me-2"></i>PAN</a> @endif
                                                    @if($doc->bank_proof) <a class="dropdown-item small" href="{{ asset('storage/'.$doc->bank_proof) }}" target="_blank"><i class="fas fa-university me-2"></i>Bank Proof</a> @endif
                                                    @if($doc->educational_documents) <a class="dropdown-item small" href="{{ asset('storage/'.$doc->educational_documents) }}" target="_blank"><i class="fas fa-graduation-cap me-2"></i>Edu Docs</a> @endif
                                                    
                                                    <div class="dropdown-divider"></div>
                                                    <h6 class="dropdown-header">Approval Actions</h6>
                                                    <form method="POST" action="{{ route('documents.employee.approve', $doc->id) }}">
                                                        @csrf
                                                        <button class="dropdown-item text-success small" type="submit"><i class="fas fa-check-circle me-2"></i>Verify All</button>
                                                    </form>
                                                    <button class="dropdown-item text-danger small" data-toggle="modal" data-target="#rejectModal{{ $doc->id }}"><i class="fas fa-times-circle me-2"></i>Reject Set</button>
                                                </div>
                                            </div>

                                            {{-- Reject Modal --}}
                                            <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content orb-modal">
                                                        <form method="POST" action="{{ route('documents.employee.reject', $doc->id) }}" style="width: 100%;">
                                                            @csrf
                                                            <div class="orb-modal-header">
                                                                <div>
                                                                    <h5 class="modal-title">Reject Document Set</h5>
                                                                    <p class="orb-modal-subtitle">Reason for rejecting this set of documents</p>
                                                                </div>
                                                                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body orb-modal-body">
                                                                <div class="orb-form-section">
                                                                    <div class="orb-form-grid" style="grid-template-columns: 1fr;">
                                                                        <div>
                                                                            <label class="orb-form-label" for="rejection_reason_{{ $doc->id }}">Reason for Rejection <span class="text-danger">*</span></label>
                                                                            <textarea name="rejection_reason" id="rejection_reason_{{ $doc->id }}" class="form-control" rows="3" placeholder="e.g. Aadhar card is blurred or expired" required></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer orb-modal-footer">
                                                                <button type="button" class="orb-btn-light" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="orb-btn-primary" style="background: linear-gradient(135deg, #DC2626, #EC4E74) !important;"><i class="fas fa-times-circle"></i> Reject Documents</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-file-invoice fa-3x mb-3 opacity-25"></i>
                                            <p>No documents uploaded yet for this employee.</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('experiencedToggle');
        const fields = document.getElementById('experienceFields');
        
        toggle.addEventListener('change', function() {
            fields.style.display = this.checked ? 'block' : 'none';
        });
    });
</script>

<style>
    .avatar-sm { font-size: 1.1rem; font-weight: 700; }
    .x-small { font-size: 0.7rem; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .dropdown-item { cursor: pointer; }
</style>
@endsection

