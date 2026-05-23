@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Company Policies')

@section('_head')
@include('hrms.documents.partials.styles')
@endsection

@section('_content')
<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; POLICY MANAGEMENT
            </div>
            <h1>Company Policies</h1>
            <p>Publish, update, and manage global organization compliance standards, handbooks, and policies.</p>
        </div>
        <div class="dm-hero-actions">
            <button type="button" class="dm-btn dm-btn-primary" onclick="openAddPolicyModal()">
                <i class="fas fa-plus"></i> Add Policy
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Company Policies Card -->
    <div class="dm-card">
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-gavel"></i></div>
                <div>
                    <h5 class="dm-table-title">Published Organization Policies</h5>
                    <p class="dm-table-subtitle">Review active corporate handbooks, security standards, and administrative guidelines.</p>
                </div>
            </div>
        </div>

        <div class="dm-table-wrap">
            <table class="table dm-table">
                <thead>
                    <tr>
                        <th>Title & Summary</th>
                        <th>Type / Category</th>
                        <th>File Attachment</th>
                        <th>Status</th>
                        <th width="180" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: var(--dm-text); font-size: 14px;">{{ $policy->title }}</div>
                                @if($policy->description)
                                    <div style="font-size: 11px; color: var(--dm-muted); font-weight: 600; margin-top: 3px; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $policy->description }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="dm-badge dm-badge-secondary" style="font-size: 9px; padding: 2px 8px;">
                                    {{ $policy->documentType->name ?? 'General' }}
                                </span>
                            </td>
                            <td>
                                @if($policy->file_path)
                                    <a href="{{ route('hrms.documents.file', $policy->file_path) }}" target="_blank" style="font-weight: 700; color: var(--dm-primary); font-size: 13px; text-decoration: none;">
                                        <i class="fas fa-file-alt mr-1 text-primary"></i> {{ $policy->file_original_name ?? 'View Document' }}
                                    </a>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">No file attached</span>
                                @endif
                            </td>
                            <td>
                                @if($policy->is_active)
                                <span class="dm-badge dm-badge-success"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                @else
                                <span class="dm-badge dm-badge-secondary"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="dm-action-btn-pill dm-action-btn-light" onclick="openEditPolicyModal(this)"
                                        data-id="{{ $policy->id }}"
                                        data-document-type-id="{{ $policy->document_type_id }}"
                                        data-title="{{ $policy->title }}"
                                        data-description="{{ $policy->description }}"
                                        data-is-active="{{ $policy->is_active }}"
                                        data-file-name="{{ $policy->file_original_name }}"
                                        data-file-url="{{ $policy->file_path ? route('hrms.documents.file', $policy->file_path) : '' }}">
                                        <i class="fas fa-edit text-warning mr-1"></i> Edit
                                    </button>

                                    <form action="{{ route('documents.policies.destroy', $policy->id) }}" method="POST" style="display:inline; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this policy?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dm-action-btn-pill dm-action-btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                No company policies published yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Policy Modal -->
<div class="modal fade" id="policyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content" style="border: none; border-radius: 24px; overflow: hidden; box-shadow: var(--dm-shadow);">
            <form id="policyForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="policyMethodContainer"></div>

                <div class="dm-modal-header">
                    <h5 class="modal-title" id="policyModalTitle">Add Company Policy</h5>
                    <p>Publish a new regulatory policy, operational handbook, or code of conduct.</p>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>

                <div class="dm-modal-body">
                    <div class="dm-form-group mb-3">
                        <label>Category Type <span class="text-danger">*</span></label>
                        <select name="document_type_id" id="policyDocTypeId" class="form-control" required>
                            <option value="">Select Document Type...</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="dm-form-group mb-3">
                        <label>Policy Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="policyTitle" class="form-control" placeholder="e.g. Remote Work Policy" required>
                    </div>

                    <div class="dm-form-group mb-3">
                        <label>Policy Summary / Description</label>
                        <textarea name="description" id="policyDescription" class="form-control" rows="3" placeholder="Provide a brief summary of the policy changes or rules..."></textarea>
                    </div>
                    
                    <div id="currentFileContainer" class="mb-3" style="display: none;">
                        <label>Current Attachment File</label>
                        <div class="p-3 rounded d-flex align-items-center gap-2" style="background: var(--dm-soft); border: 1px solid rgba(75, 0, 232, 0.15);">
                            <i class="fas fa-file-pdf text-danger" style="font-size: 16px;"></i>
                            <a href="#" id="currentFileLink" target="_blank" style="font-weight: 800; color: var(--dm-primary); text-decoration: none; font-size: 13px;"></a>
                        </div>
                    </div>

                    <div class="dm-form-group mb-3">
                        <label id="policyFileLabel">Upload Document File <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="policyFile" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <p class="text-muted mt-1" style="font-size: 11px;">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 5MB)</p>
                    </div>
                    
                    <div class="p-3 rounded mb-2" style="background: #F8FAFC; border: 1px solid var(--dm-border);">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="is_active" id="policyIsActive" value="1" class="custom-control-input" checked>
                            <label class="custom-control-label" for="policyIsActive" style="font-weight: 800; color: var(--dm-text); cursor: pointer;">
                                Mark this policy as Active and publish immediately
                            </label>
                        </div>
                    </div>
                </div>

                <div class="dm-modal-footer">
                    <button type="button" class="dm-btn dm-btn-dark-light" style="height: 38px;" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="dm-btn dm-btn-gradient" style="height: 38px;">
                        <i class="fas fa-save mr-1"></i> Save Policy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle custom file changes nicely if needed
    });

    function openAddPolicyModal() {
        document.getElementById('policyModalTitle').innerHTML = '<i class="fas fa-plus-circle mr-2"></i>Add Company Policy';
        document.getElementById('policyForm').action = "{{ route('documents.policies.store') }}";
        document.getElementById('policyMethodContainer').innerHTML = '';
        
        document.getElementById('policyDocTypeId').value = '';
        document.getElementById('policyTitle').value = '';
        document.getElementById('policyDescription').value = '';
        document.getElementById('policyFile').required = true;
        document.getElementById('policyFileLabel').innerHTML = 'Upload Document File <span class="text-danger">*</span>';
        document.getElementById('policyIsActive').checked = true;
        
        document.getElementById('currentFileContainer').style.display = 'none';

        $('#policyModal').modal('show');
    }

    function openEditPolicyModal(btn) {
        document.getElementById('policyModalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Company Policy';
        var id = btn.getAttribute('data-id');
        document.getElementById('policyForm').action = "{{ url('hrms/documents/policies') }}/" + id;
        document.getElementById('policyMethodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        document.getElementById('policyDocTypeId').value = btn.getAttribute('data-document-type-id');
        document.getElementById('policyTitle').value = btn.getAttribute('data-title');
        document.getElementById('policyDescription').value = btn.getAttribute('data-description');
        document.getElementById('policyIsActive').checked = btn.getAttribute('data-is-active') == '1';
        
        document.getElementById('policyFile').required = false;
        document.getElementById('policyFileLabel').innerHTML = 'Upload Document File <span class="text-muted">(Optional - upload to replace)</span>';
        
        var fileName = btn.getAttribute('data-file-name');
        var fileUrl = btn.getAttribute('data-file-url');
        
        if (fileName && fileUrl) {
            document.getElementById('currentFileContainer').style.display = 'block';
            var link = document.getElementById('currentFileLink');
            link.href = fileUrl;
            link.innerText = fileName;
        } else {
            document.getElementById('currentFileContainer').style.display = 'none';
        }

        $('#policyModal').modal('show');
    }
</script>
@endsection
