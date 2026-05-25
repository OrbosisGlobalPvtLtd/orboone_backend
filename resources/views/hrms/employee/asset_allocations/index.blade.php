@extends('layouts.admin', ['accesses' => $accesses ?? [], 'active' => 'data'])

@section('_content')
@include('hrms.employee.partials.styles')

<div class="container-fluid py-4 px-4">
    <!-- Page Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-6">
            <h3 class="font-weight-bold text-dark mb-1">Asset Allocation</h3>
            <p class="text-muted m-0">Manage company assets assigned to employees</p>
        </div>
        <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
            <a href="{{ route('hrms.assets.create') }}" class="btn btn-orb mr-2">
                <i class="fas fa-plus-circle mr-2"></i> Assign Asset
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 py-3">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Main Content Card -->
    <div class="card custom-card">
        <div class="card-header bg-white border-0 py-4 px-4">
            <form action="{{ route('hrms.assets.index') }}" method="GET">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="employee_name" class="form-control border-0" placeholder="Search employee..." value="{{ request('employee_name') }}">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select name="asset_type" class="form-control form-control-sm rounded-pill border" onchange="this.form.submit()">
                            <option value="">All Asset Types</option>
                            <option value="Laptop" {{ request('asset_type') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                            <option value="Mobile" {{ request('asset_type') == 'Mobile' ? 'selected' : '' }}>Mobile</option>
                            <option value="ID Card" {{ request('asset_type') == 'ID Card' ? 'selected' : '' }}>ID Card</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select name="status" class="form-control form-control-sm rounded-pill border" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="{{ route('hrms.assets.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Clear</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-orb mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Employee</th>
                            <th>Asset Type</th>
                            <th>Assigned Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assetAllocations as $allocation)
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3 p-2 rounded bg-light">
                                        <i class="fas fa-user-circle text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="font-weight-bold text-dark h6 mb-1 d-block">
                                            {{ $allocation->employee->user->name ?? ($allocation->employee->employeeDetail->name ?? 'Unknown Employee') }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-weight-bold text-dark">
                                    <i class="fas {{ $allocation->asset_type == 'Laptop' ? 'fa-laptop' : ($allocation->asset_type == 'Mobile' ? 'fa-mobile-alt' : 'fa-id-badge') }} mr-2 text-muted"></i>
                                    {{ $allocation->asset_type }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ \Carbon\Carbon::parse($allocation->assigned_date)->format('d M Y') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($allocation->status == 'Active')
                                    <span class="status-badge status-active">
                                        Active
                                    </span>
                                @else
                                    <span class="status-badge status-returned">
                                        Returned
                                    </span>
                                @endif
                            </td>
                            <td class="text-right pr-4">
                                <a href="{{ route('hrms.assets.edit', $allocation->id) }}" class="action-btn" title="Edit Allocation">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="action-btn border-0" title="Delete Allocation" style="color: #e74a3b;" onclick="openDeleteModal({{ $allocation->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                
                                <form id="delete-form-{{ $allocation->id }}" action="{{ route('hrms.assets.destroy', $allocation->id) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <img src="{{ asset('images/no-data.png') }}" alt="No Data" style="width: 120px; opacity: 0.5;" onerror="this.style.display='none'">
                                <p class="text-muted mt-3">No asset allocations found.</p>
                                <a href="{{ route('hrms.assets.create') }}" class="btn btn-sm btn-orb">Assign First Asset</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($assetAllocations->hasPages())
                <div class="px-4 py-3 bg-light border-top">
                    {{ $assetAllocations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAssetModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header" style="background: linear-gradient(135deg, #DC2626, #EC4E74) !important;">
                <div>
                    <h5 class="modal-title">Delete Asset Record</h5>
                    <p class="orb-modal-subtitle">Permanently remove this asset allocation</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body orb-modal-body text-center p-4">
                <div class="mb-4 mt-2">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #ffe5e5; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="fas fa-exclamation-triangle fa-2x" style="color: #dc3545;"></i>
                    </div>
                </div>
                <h5 class="font-weight-bold mb-3" style="color: var(--orb-text);">Are you absolutely sure?</h5>
                <p class="text-muted mb-4" style="line-height: 1.6;">You are about to permanently delete this asset allocation. This action cannot be undone.</p>
            </div>
            <div class="modal-footer orb-modal-footer">
                <button type="button" class="orb-btn-light" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteActionBtn" class="orb-btn-primary" style="background: linear-gradient(135deg, #DC2626, #EC4E74) !important; border: 0 !important;">Yes, Delete it!</button>
            </div>
        </div>
    </div>
</div>

<script>
    let activeDeletionId = null;

    function openDeleteModal(id) {
        activeDeletionId = id;
        $('#deleteAssetModal').modal('show');
    }

    document.getElementById('confirmDeleteActionBtn').addEventListener('click', function() {
        if (activeDeletionId) {
            // Initiate a visual loading state to prevent double clicks
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';
            this.classList.add('disabled');
            
            document.getElementById('delete-form-' + activeDeletionId).submit();
        }
    });
</script>
@endsection
