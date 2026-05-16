@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Company Policies')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
    }
    .eo-page { min-height: calc(100vh - 90px); padding: 16px 10px 30px; background: var(--orb-bg); }
    .eo-container { max-width: 1320px; margin: 0 auto; }
    .eo-header { background: #fff; border: 1px solid var(--orb-border); border-radius: 20px; padding: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
    .eo-card { background: #fff; border-radius: 20px; border: 1px solid var(--orb-border); padding: 20px; }
    .table th { background: #F8FAFC; color: #667085; font-size: 11px; text-transform: uppercase; }
</style>

<div class="eo-page">
    <div class="eo-container">
        <div class="eo-header">
            <div>
                <h1 style="margin:0; font-size: 24px; font-weight: 900; color: var(--orb-text);">Company Policies</h1>
            </div>
            <button type="button" class="btn btn-primary" style="background: var(--orb-primary); border: none;" onclick="openAddPolicyModal()">Add Policy</button>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="eo-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>File</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($policies as $policy)
                            <tr>
                                <td>{{ $policy->title }}</td>
                                <td>{{ $policy->documentType->name ?? '-' }}</td>
                                <td>
                                    @if($policy->file_path)
                                        <a href="{{ route('hrms.documents.file', $policy->file_path) }}" target="_blank">{{ $policy->file_original_name ?? 'View File' }}</a>
                                    @endif
                                </td>
                                <td>{{ $policy->is_active ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-light" onclick="openEditPolicyModal(this)"
                                        data-id="{{ $policy->id }}"
                                        data-document-type-id="{{ $policy->document_type_id }}"
                                        data-title="{{ $policy->title }}"
                                        data-description="{{ $policy->description }}"
                                        data-is-active="{{ $policy->is_active }}"
                                        data-file-name="{{ $policy->file_original_name }}"
                                        data-file-url="{{ $policy->file_path ? route('hrms.documents.file', $policy->file_path) : '' }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('hrms.documents.policies.destroy', $policy->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this policy?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Policy Modal -->
<div class="modal fade" id="policyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="policyForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="policyMethodContainer"></div>
                <div class="modal-header">
                    <h5 class="modal-title" id="policyModalTitle">Add Company Policy</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Document Type</label>
                        <select name="document_type_id" id="policyDocTypeId" class="form-control" required>
                            <option value="">Select Type...</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Title</label>
                        <input type="text" name="title" id="policyTitle" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Description</label>
                        <textarea name="description" id="policyDescription" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group mb-3" id="currentFileContainer" style="display: none;">
                        <label>Current File:</label>
                        <div>
                            <a href="#" id="currentFileLink" target="_blank"></a>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label id="policyFileLabel">File (PDF, DOC, DOCX, JPG, PNG)</label>
                        <input type="file" name="file" id="policyFile" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="policyIsActive" value="1" class="form-check-input" checked>
                        <label class="form-check-label" for="policyIsActive">Is Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddPolicyModal() {
        document.getElementById('policyModalTitle').innerText = 'Add Company Policy';
        document.getElementById('policyForm').action = "{{ route('hrms.documents.policies.store') }}";
        document.getElementById('policyMethodContainer').innerHTML = '';
        
        document.getElementById('policyDocTypeId').value = '';
        document.getElementById('policyTitle').value = '';
        document.getElementById('policyDescription').value = '';
        document.getElementById('policyFile').required = true;
        document.getElementById('policyFileLabel').innerText = 'File (Required)';
        document.getElementById('policyIsActive').checked = true;
        
        document.getElementById('currentFileContainer').style.display = 'none';

        $('#policyModal').modal('show');
    }

    function openEditPolicyModal(btn) {
        document.getElementById('policyModalTitle').innerText = 'Edit Company Policy';
        var id = btn.getAttribute('data-id');
        document.getElementById('policyForm').action = "{{ url('hrms/documents/policies') }}/" + id;
        document.getElementById('policyMethodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        document.getElementById('policyDocTypeId').value = btn.getAttribute('data-document-type-id');
        document.getElementById('policyTitle').value = btn.getAttribute('data-title');
        document.getElementById('policyDescription').value = btn.getAttribute('data-description');
        document.getElementById('policyIsActive').checked = btn.getAttribute('data-is-active') == '1';
        
        document.getElementById('policyFile').required = false;
        document.getElementById('policyFileLabel').innerText = 'File (Optional - upload to replace)';
        
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
