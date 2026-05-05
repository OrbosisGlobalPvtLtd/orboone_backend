@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-dark">Employee Digital Files</h4>
            <p class="text-muted small">Manage employee-wise document uploads, verifications, and approvals.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-folder-open me-2 text-primary"></i> Employee Documents</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-toggle="modal" data-target="#globalUploadModal">
                    <i class="fas fa-plus me-1"></i> New Upload
                </button>
                <input type="text" class="form-control form-control-sm" placeholder="Search employee...">
            </div>
        </div>

        {{-- Global Upload Modal --}}
        <div class="modal fade" id="globalUploadModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('hrms.documents.employee.store_global') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Global Document Upload</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Select Employee <span class="text-danger">*</span></label>
                                    <select name="employee_id" class="form-control select2" required style="width: 100%;">
                                        <option value="">-- Search Employee --</option>
                                        @foreach($allEmployees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employeeDetail->identity_number ?? 'ID:'.$emp->id }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Document Type <span class="text-danger">*</span></label>
                                    <select name="document_type_id" class="form-control select2" required style="width: 100%;">
                                        <option value="">-- Select Category --</option>
                                        @foreach($documentTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-12"><hr class="my-1"></div>

                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Aadhar Card <span class="text-danger">*</span></label>
                                    <input type="file" name="aadhar_card" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">PAN Card <span class="text-danger">*</span></label>
                                    <input type="file" name="pan_card" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Passport Photo <span class="text-danger">*</span></label>
                                    <input type="file" name="passport_photo" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Bank Proof <span class="text-danger">*</span></label>
                                    <input type="file" name="bank_proof" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Educational Documents <span class="text-danger">*</span></label>
                                    <input type="file" name="educational_documents" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <p class="small text-muted mt-3 mb-0">For experienced employees, additional documents can be uploaded from the employee's detail page.</p>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Upload All</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success mx-3 mt-3 border-0 shadow-sm">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small fw-bold">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Employee ID</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary-soft text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(13, 110, 253, 0.1);">
                                            {{ substr($employee->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $employee->name }}</div>
                                            <div class="text-muted x-small">{{ $employee->user->email ?? 'No Email' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $employee->employeeDetail->identity_number ?? 'EMP-'.$employee->id }}</span></td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>{{ $employee->position->name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @php $doc = $employee->documents->first(); @endphp
                                    @if($doc)
                                        @if($doc->status === 'verified')
                                            <span class="badge rounded-pill bg-success-soft text-success px-3" style="background: rgba(25, 135, 84, 0.1);">Verified</span>
                                        @elseif($doc->status === 'rejected')
                                            <span class="badge rounded-pill bg-danger-soft text-danger px-3" style="background: rgba(220, 53, 69, 0.1);">Rejected</span>
                                        @else
                                            <span class="badge rounded-pill bg-warning-soft text-warning px-3" style="background: rgba(255, 193, 7, 0.1);">Pending</span>
                                        @endif
                                    @else
                                        <span class="badge rounded-pill bg-secondary-soft text-secondary px-3" style="background: rgba(108, 117, 125, 0.1);">No Uploads</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                       href="{{ route('hrms.documents.employee.show', $employee->id) }}">
                                        <i class="fas fa-eye me-1"></i> Manage
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                    <p>No employees found matching your criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 bg-light border-top">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm { font-size: 1.1rem; font-weight: 700; }
    .x-small { font-size: 0.75rem; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
</style>
@endsection

