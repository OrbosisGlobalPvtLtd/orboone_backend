@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'document-management'])

@section('_content')
<style>
    :root {
        --primary-orb: #1560ab;
        --secondary-orb: #0099cc;
        --soft-bg: #f8fafc;
    }

    .premium-header {
        background: linear-gradient(135deg, var(--primary-orb), var(--secondary-orb));
        padding: 40px 30px;
        border-radius: 0 0 40px 40px;
        margin-bottom: -60px;
        color: white;
        box-shadow: 0 10px 30px rgba(21, 96, 171, 0.2);
    }

    .custom-table-card {
        background: white;
        border-radius: 25px;
        border: none;
        box-shadow: 0 15px 40px rgba(0,0,0,0.04);
        margin-top: 20px;
        overflow: hidden;
    }

    .orb-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 20px;
        border: none;
    }

    .orb-table tbody td {
        padding: 20px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
    }

    .badge-orb {
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .badge-orb-info { background: rgba(54, 162, 235, 0.1); color: #36a2eb; }
    .badge-orb-secondary { background: rgba(100, 116, 139, 0.1); color: #64748b; }

    .btn-orb-add {
        background: white;
        color: var(--primary-orb) !important;
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 700;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: 0.3s;
    }

    .btn-orb-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
</style>

<div class="container-fluid p-0">
    <!-- Premium Header -->
    <div class="premium-header">
        <div class="row align-items-center">
            <div class="col-md-8 px-5">
                <h1 class="font-weight-bold mb-2">Company Policy Repository</h1>
                <p class="mb-0 opacity-75">Upload and manage official organizational policies and guidelines.</p>
            </div>
            <div class="col-md-4 text-md-right px-5 pt-3 pt-md-0">
                <button class="btn btn-orb-add" data-toggle="modal" data-target="#addPolicyModal">
                    <i class="fas fa-plus-circle mr-2"></i> Create New Policy
                </button>
            </div>
        </div>
    </div>

    <div class="px-5 pb-5">
        @include('components.alerts')

        <div class="card custom-table-card shadow-sm">
            <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold text-dark mb-0">Active Policies</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table orb-table mb-0">
                    <thead>
                        <tr>
                            <th class="px-4">Policy Details</th>
                            <th>Category</th>
                            <th>Visibility</th>
                            <th>Upload Info</th>
                            <th class="text-right px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docs as $doc)
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 p-3 rounded-lg bg-light text-primary">
                                            <i class="fas fa-file-pdf fa-lg"></i>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold d-block text-dark">{{ $doc->title }}</span>
                                            <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem;">ID: #POL-{{ $doc->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-orb badge-orb-info">{{ $doc->category }}</span>
                                </td>
                                <td>
                                    @foreach($doc->visible_to ?? ['employee', 'admin'] as $v)
                                        <span class="badge badge-pill badge-light border px-2 py-1 small">{{ ucfirst($v) }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <small class="text-muted d-block">By: Admin</small>
                                    <small class="text-muted">{{ $doc->created_at->format('d M, Y') }}</small>
                                </td>
                                <td class="text-right px-4">
                                    <div class="btn-group">
                                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-light border mr-2" style="border-radius: 8px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('hrms.documents.policies.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Delete this policy?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" style="border-radius: 8px;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-4x text-light mb-3"></i>
                                    <h5 class="text-muted">No policies uploaded yet.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Policy Modal -->
<div class="modal fade" id="addPolicyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content orb-modal">
            <form action="{{ route('hrms.documents.policies.store') }}" method="POST" enctype="multipart/form-data" style="width: 100%;">
                @csrf
                <div class="orb-modal-header">
                    <div>
                        <h5 class="modal-title">Upload Company Policy</h5>
                        <p class="orb-modal-subtitle">Publish a new policy file for employees and admins</p>
                    </div>
                    <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body orb-modal-body">
                    <div class="orb-form-section">
                        <div class="orb-form-grid" style="grid-template-columns: 1fr;">
                            <div>
                                <label class="orb-form-label" for="policy_title">Policy Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="policy_title" class="form-control" placeholder="e.g., Annual Leave Policy 2026" required>
                            </div>
                            <div>
                                <label class="orb-form-label" for="policy_category">Category <span class="text-danger">*</span></label>
                                <select name="category" id="policy_category" class="form-control" required>
                                    <option value="HR Policy">HR Policy</option>
                                    <option value="IT Policy">IT Policy</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Conduct">Code of Conduct</option>
                                    <option value="General">General</option>
                                </select>
                            </div>
                            <div>
                                <label class="orb-form-label" for="policy_file">Document File (PDF/Image) <span class="text-danger">*</span></label>
                                <input type="file" name="file" id="policy_file" class="form-control p-1" required>
                            </div>
                            <div>
                                <label class="orb-form-label">Visible To</label>
                                <div class="d-flex align-items-center gap-4 mt-2">
                                    <div class="form-check form-check-inline mr-3">
                                        <input class="form-check-input" type="checkbox" name="visible_to[]" value="employee" id="vis_emp" checked>
                                        <label class="form-check-label font-weight-bold ml-1" for="vis_emp" style="font-size: 13px; color: var(--orb-text);">Employees</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="visible_to[]" value="admin" id="vis_adm" checked>
                                        <label class="form-check-label font-weight-bold ml-1" for="vis_adm" style="font-size: 13px; color: var(--orb-text);">Admins/HR</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-primary"><i class="fas fa-bullhorn mr-1"></i> Publish Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
