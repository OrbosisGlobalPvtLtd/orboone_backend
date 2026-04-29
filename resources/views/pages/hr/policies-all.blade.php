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
                                        <form action="{{ route('hr.policies.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Delete this policy?')">
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
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <div class="modal-header bg-primary text-white border-0 py-4 px-4" style="border-radius: 25px 25px 0 0;">
                <h5 class="modal-title font-weight-bold">Upload Company Policy</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('hr.policies.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted uppercase">Policy Title</label>
                        <input type="text" name="title" class="form-control border-light bg-light" placeholder="e.g., Annual Leave Policy 2026" required style="border-radius: 12px; height: 50px;">
                    </div>
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted uppercase">Category</label>
                        <select name="category" class="form-control border-light bg-light" required style="border-radius: 12px; height: 50px;">
                            <option value="HR Policy">HR Policy</option>
                            <option value="IT Policy">IT Policy</option>
                            <option value="Finance">Finance</option>
                            <option value="Conduct">Code of Conduct</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted uppercase">Document File (PDF/Image)</label>
                        <input type="file" name="file" class="form-control-file border p-2 w-100" required style="border-radius: 12px;">
                    </div>
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted d-block uppercase mb-2">Visible To</label>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" name="visible_to[]" value="employee" class="custom-control-input" id="vis_emp" checked>
                            <label class="custom-control-label" for="vis_emp">Employees</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" name="visible_to[]" value="admin" class="custom-control-input" id="vis_adm" checked>
                            <label class="custom-control-label" for="vis_adm">Admins/HR</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4" style="border-radius: 0 0 25px 25px;">
                    <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-5 font-weight-bold" style="border-radius: 12px;">Publish Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
