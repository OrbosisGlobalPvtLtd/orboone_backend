@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
@include('hrms.employee.partials.styles')

<div class="container-fluid py-4 px-4">
    <!-- Page Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-6">
            <h3 class="font-weight-bold text-dark mb-1">Departments Directory</h3>
            <p class="text-muted m-0">Define and manage your organizational organizational structure</p>
        </div>
        <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
            @if (collect($accesses)->where('menu_id', 11)->first()->status == 10)
                <a href="{{ route('hrms.departments.create') }}" class="btn btn-orb mr-2">
                    <i class="fas fa-plus-circle mr-2"></i> New Department
                </a>
            @endif
            <a href="{{ route('hrms.departments.export') }}" class="btn btn-light shadow-sm" style="border-radius: 50px;" target="_blank">
                <i class="fas fa-print mr-2"></i> Export Report
            </a>

            <a href="{{ route('hrms.departments.create') }}" class="btn btn-orb mr-2">
                    <i class="fas fa-plus-circle mr-2"></i> New Department
                </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm mb-4 py-3">
            <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
        </div>
    @endif

    <!-- Main Content Card -->
    <div class="card custom-card">
        <div class="card-header bg-white border-0 py-4 px-4">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <form action="{{ route('hrms.departments.index') }}" method="GET">
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control border-0" placeholder="Search departments..." value="{{ $search ?? '' }}">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-orb mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Dept Name</th>
                            <th>Code</th>
                            <th>Headcount</th>
                            <th>Team Members</th>
                            <th>Location</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3 p-2 rounded bg-light">
                                        <i class="fas fa-sitemap text-primary"></i>
                                    </div>
                                    <a href="{{ route('hrms.departments.show', $department->id) }}" class="font-weight-bold text-dark h6 mb-0">
                                        {{ $department->name }}
                                    </a>
                                </div>
                            </td>
                            <td><span class="dept-badge">{{ $department->code }}</span></td>
                            <td>
                                <span class="count-badge">
                                    <i class="fas fa-users mr-1"></i> {{ $department->employees_count }} Staff
                                </span>
                            </td>
                            <td>
                                <div class="avatar-stack">
                                    @foreach($department->employees as $emp)
                                        @php
                                            $img_path = $emp->image && file_exists(public_path('storage/'.$emp->image)) 
                                                ? asset('storage/'.$emp->image) 
                                                : asset('images/profile.png');
                                        @endphp
                                        <img src="{{ $img_path }}" class="avatar-item" title="{{ $emp->employeeDetail->name ?? $emp->name }}"
                                             onerror="this.src='{{ asset('images/profile.png') }}'">
                                    @endforeach
                                    @if($department->employees_count > 5)
                                        <div class="avatar-item d-flex align-items-center justify-content-center bg-light text-primary small font-weight-bold">
                                            +{{ $department->employees_count - 5 }}
                                        </div>
                                    @endif
                                    @if($department->employees_count == 0)
                                        <span class="text-muted small italic">No staff assigned</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-muted">
                                <div class="small">
                                    <i class="fas fa-map-marker-alt mr-1 text-danger"></i> {{ Str::limit($department->address, 30) }}
                                </div>
                            </td>
                            <td class="text-right pr-4">
                                <a href="{{ route('hrms.departments.edit', $department->id) }}" class="action-btn" title="Edit Settings">
                                    <i class="fas fa-cog"></i>
                                </a>
                                <a href="{{ route('hrms.departments.show', $department->id) }}" class="action-btn ml-1" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="{{ asset('images/no-data.png') }}" alt="No Data" style="width: 120px; opacity: 0.5;">
                                <p class="text-muted mt-3">No departments found in the system.</p>
                                <a href="{{ route('hrms.departments.create') }}" class="btn btn-sm btn-orb">Add First Department</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($departments->hasPages())
                <div class="px-4 py-3 bg-light border-top">
                    {{ $departments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection