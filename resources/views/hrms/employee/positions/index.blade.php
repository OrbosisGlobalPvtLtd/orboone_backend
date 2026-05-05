@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
<style>
    :root {
        --primary-orb: #1560ab;
        --secondary-orb: #0099cc;
        --soft-bg: #f4f7fa;
    }

    .custom-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        background: white;
        overflow: hidden;
    }

    .table-orb thead th {
        background: #f8f9fc;
        color: var(--primary-orb);
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1px;
        border: none;
        padding: 20px 15px;
    }

    .table-orb tbody tr {
        transition: all 0.3s;
    }

    .table-orb tbody tr:hover {
        background: rgba(21, 96, 171, 0.02);
    }

    .table-orb tbody td {
        vertical-align: middle;
        padding: 20px 15px;
        border-bottom: 1px solid #f1f4f8;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }

    .status-open {
        background: rgba(28, 200, 138, 0.1);
        color: #1cc88a;
    }

    .status-closed {
        background: rgba(133, 135, 150, 0.1);
        color: #858796;
    }

    .exp-badge {
        background: rgba(253, 126, 20, 0.1);
        color: #fd7e14;
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .avatar-stack {
        display: flex;
        align-items: center;
    }

    .avatar-item {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid white;
        margin-left: -10px;
        background: #eee;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .avatar-item:first-child {
        margin-left: 0;
    }

    .btn-orb {
        background: linear-gradient(135deg, var(--primary-orb), var(--secondary-orb));
        color: white !important;
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 600;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(21, 96, 171, 0.2);
    }

    .btn-orb:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(21, 96, 171, 0.3);
    }

    .action-btn {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.3s;
        background: #f8f9fc;
        color: var(--primary-orb);
        border: 1px solid #e3e6f0;
    }

    .action-btn:hover {
        background: var(--primary-orb);
        color: white;
        transform: scale(1.1);
    }
</style>

<div class="container-fluid py-4 px-4">
    <!-- Page Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-6">
            <h3 class="font-weight-bold text-dark mb-1">Job Roles & Designation</h3>
            <p class="text-muted m-0">Define designations, requirements, and salary brackets</p>
        </div>
        <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
            @if (collect($accesses)->where('menu_id', 11)->first()->status == 10)
                <a href="{{ route('hrms.designations.create') }}" class="btn btn-orb mr-2">
                    <i class="fas fa-plus-circle mr-2"></i> Create Designation
                </a>
            @endif
            <a href="{{ route('hrms.designations.export') }}" class="btn btn-light shadow-sm" style="border-radius: 50px;" target="_blank">
                <i class="fas fa-print mr-2"></i> Export Report
            </a>

             <a href="{{ route('hrms.designations.create') }}" class="btn btn-orb mr-2">
                    <i class="fas fa-plus-circle mr-2"></i> Create Designation
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
                    <form action="{{ route('hrms.designations.index') }}" method="GET">
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control border-0" placeholder="Search roles..." value="{{ $search ?? '' }}">
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
                            <th class="pl-4">Designation</th>
                            <th>Min. Exp</th>
                            <th>Salary Range</th>
                            <th>Team Members</th>
                            <th class="text-center">Hiring Status</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($positions as $position)
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3 p-2 rounded bg-light">
                                        <i class="fas fa-briefcase text-primary"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('hrms.designations.show', $position->id) }}" class="font-weight-bold text-dark h6 mb-1 d-block">
                                            {{ $position->name }}
                                        </a>
                                        <small class="text-muted d-block">{{ Str::limit($position->description, 40) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="exp-badge">{{ $position->min_year_exp_required }}Y+ Exp</span></td>
                            <td>
                                <div class="font-weight-bold text-success">
                                    {{ is_numeric($position->salary) ? '₹' . number_format($position->salary) : $position->salary }}
                                </div>
                                <small class="text-muted small">Standard Bracket</small>
                            </td>
                            <td>
                                <div class="avatar-stack">
                                    @foreach($position->employees as $emp)
                                        @php
                                            $img_path = $emp->image && file_exists(public_path('storage/'.$emp->image)) 
                                                ? asset('storage/'.$emp->image) 
                                                : asset('images/profile.png');
                                        @endphp
                                        <img src="{{ $img_path }}" class="avatar-item" title="{{ $emp->employeeDetail->name ?? $emp->name }}"
                                             onerror="this.src='{{ asset('images/profile.png') }}'">
                                    @endforeach
                                    @if($position->employees_count > 5)
                                        <div class="avatar-item d-flex align-items-center justify-content-center bg-light text-primary small font-weight-bold">
                                            +{{ $position->employees_count - 5 }}
                                        </div>
                                    @endif
                                    @if($position->employees_count == 0)
                                        <span class="text-muted small italic">No staff assigned</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($position->open_for_recruitment)
                                    <span class="status-badge status-open">
                                        <i class="fas fa-bullhorn mr-1"></i> Hiring
                                    </span>
                                @else
                                    <span class="status-badge status-closed">
                                        <i class="fas fa-lock mr-1"></i> Filled
                                    </span>
                                @endif
                            </td>
                            <td class="text-right pr-4">
                                <a href="{{ route('hrms.designations.edit', $position->id) }}" class="action-btn" title="Edit Role">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('hrms.designations.show', $position->id) }}" class="action-btn ml-1" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="{{ asset('images/no-data.png') }}" alt="No Data" style="width: 120px; opacity: 0.5;">
                                <p class="text-muted mt-3">No positions found in the system.</p>
                                <a href="{{ route('hrms.designations.create') }}" class="btn btn-sm btn-orb">Add First Position</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($positions->hasPages())
                <div class="px-4 py-3 bg-light border-top">
                    {{ $positions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection