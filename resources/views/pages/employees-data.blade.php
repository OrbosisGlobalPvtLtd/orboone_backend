@extends('layouts.panel')

@section('page_title', 'Employee Onboarding')

@section('_content')
<style>
    :root {
        --orb-primary: #4b00e8;
        --orb-secondary: #8600ee;
        --orb-accent: #ec4e74;
        --orb-surface: rgba(255, 255, 255, 0.95);
        --orb-shadow: 0 10px 40px rgba(75, 0, 232, 0.08);
        --orb-radius: 24px;
        --orb-text: #1e293b;
        --orb-muted: #64748b;
    }

    .page-wrapper {
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Premium Header */
    .header-section {
        background: transparent;
        margin-bottom: 2.5rem;
    }

    .page-title {
        font-size: 2.2rem;
        font-weight: 800;
        letter-spacing: -1px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }

    .stats-badge {
        background: rgba(75, 0, 232, 0.05);
        color: var(--orb-primary);
        padding: 6px 16px;
        border-radius: 100px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    /* Action Buttons */
    .btn-premium {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: white !important;
        border: none;
        border-radius: 18px;
        padding: 14px 32px;
        font-weight: 700;
        box-shadow: 0 10px 30px rgba(75, 0, 232, 0.2);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .btn-premium:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 15px 40px rgba(75, 0, 232, 0.3);
    }

    .btn-outline-premium {
        background: white;
        color: var(--orb-text) !important;
        border: 2px solid #edf2f7;
        border-radius: 18px;
        padding: 12px 28px;
        font-weight: 700;
        transition: all 0.3s;
    }

    .btn-outline-premium:hover {
        background: #f8fafc;
        border-color: var(--orb-primary);
        transform: translateY(-2px);
    }

    /* Glassmorphism Filter Card */
    .filter-card {
        background: var(--orb-surface);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: var(--orb-radius);
        box-shadow: var(--orb-shadow);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .form-control-premium {
        background: #f8fafc !important;
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        height: 56px;
        padding-left: 20px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .form-control-premium:focus {
        border-color: var(--orb-primary);
        background: white !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1);
    }

    /* Responsive Views */
    .desktop-view { display: block; }
    .mobile-view { display: none; }

    /* Premium Table (Desktop) */
    .table-container {
        border-radius: var(--orb-radius);
        background: white;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .emp-table { margin: 0; border-collapse: separate; border-spacing: 0; }
    .emp-table thead th {
        background: #f8fafc;
        padding: 20px;
        color: var(--orb-muted);
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1.5px;
        font-weight: 800;
        border: none;
    }

    .emp-table tbody td {
        padding: 24px 20px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        font-weight: 600;
        color: var(--orb-text);
    }

    .emp-table tbody tr { transition: all 0.3s; }
    .emp-table tbody tr:hover { background: #fdfdff; }

    /* Avatar and Status Styles */
    .avatar-wrapper {
        width: 54px; height: 54px;
        border-radius: 18px;
        overflow: hidden;
        border: 3px solid white;
        box-shadow: 0 8px 15px rgba(0,0,0,0.06);
    }

    .avatar-img { width: 100%; height: 100%; object-fit: cover; }

    .status-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .badge-premium {
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    /* Mobile Cards */
    .mobile-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #f1f5f9;
        transition: transform 0.3s;
    }

    .mobile-card:active { transform: scale(0.98); }

    .action-btn-circle {
        width: 42px; height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        color: var(--orb-muted);
        margin-left: 8px;
        transition: all 0.3s;
        border: none;
    }

    .action-btn-circle:hover { 
        transform: translateY(-3px) rotate(8deg); 
        color: white; 
    }

    .btn-view:hover { background: var(--orb-primary) !important; }
    .btn-edit:hover { background: var(--orb-secondary) !important; }
    .btn-pdf:hover { background: #00bcd4 !important; }
    .btn-delete:hover { background: var(--orb-accent) !important; }

    /* Responsiveness */
    @media (max-width: 1024px) {
        .custom-container { padding-left: 15px !important; padding-right: 15px !important; }
        .desktop-view { display: none; }
        .mobile-view { display: block; }
        
        .header-section { flex-direction: column; text-align: center; gap: 20px; }
        .stats-badge { margin-bottom: 15px; }
        .page-title { font-size: 1.8rem; }
    }

    @media (max-width: 576px) {
        .filter-card { padding: 1.2rem; }
        .btn-premium, .btn-outline-premium { width: 100%; text-align: center; }
    }
</style>

<div class="container-fluid py-4 px-4 page-wrapper custom-container">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between header-section">
        <div>
            <span class="stats-badge"><i class="fas fa-users-cog"></i> Workforce Center</span>
            <h1 class="page-title">Employee Directory</h1>
            <p class="text-muted font-weight-600">Managing {{ $employees->total() }} professionals across all departments.</p>
        </div>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="{{ route('employees-data.print') }}" class="btn btn-outline-premium" target="_blank">
                <i class="fas fa-file-export mr-2"></i> Export Data
            </a>
            <a href="{{ route('employees-data.create') }}" class="btn btn-premium">
                <i class="fas fa-plus-circle mr-2"></i> Add Employee
            </a>
        </div>
    </div>

    <!-- Feedback -->
    @if (session('status'))
    <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 18px; background: white;">
        <div class="mr-3 p-2 bg-success rounded-circle" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-check text-white"></i>
        </div>
        <div class="font-weight-bold text-dark">{{ session('status') }}</div>
    </div>
    @endif

    <!-- Search & Filters -->
    <div class="filter-card">
        <form action="{{ route('employees-data') }}" method="GET" class="row g-3">
            <div class="col-lg-4 col-md-12">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control form-control-premium" placeholder="Search name or ID..." value="{{ request('search') }}">
                    <i class="fas fa-search position-absolute" style="right: 20px; top: 20px; color: #cbd5e1;"></i>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4">
                <select name="department" class="form-control form-control-premium">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-md-4">
                <select name="status" class="form-control form-control-premium">
                    <option value="">All Statuses</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Probation" {{ request('status') == 'Probation' ? 'selected' : '' }}>Probation</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn btn-premium w-100 py-3" style="box-shadow: none;">Filter</button>
            </div>
        </form>
    </div>

    <!-- Desktop Table View -->
    <div class="desktop-view">
        <div class="table-container">
            <table class="table emp-table">
                <thead>
                    <tr>
                        <th>Professional</th>
                        <th>Identifier</th>
                        <th>Contacts</th>
                        <th>Work Context</th>
                        <th>Mode</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-wrapper mr-3">
                                    @php
                                        $photoPath = $employee->employeeDetail->photo ?? 'images/profile.png';
                                        $finalUrl = asset($photoPath);
                                        if (strpos($photoPath, 'http') === 0) $finalUrl = $photoPath;
                                        elseif (!file_exists(public_path($photoPath))) $finalUrl = asset('images/profile.png');
                                    @endphp
                                    <img src="{{ $finalUrl }}" class="avatar-img" alt="Avatar">
                                </div>
                                <div>
                                    <div class="font-weight-bold" style="font-size: 1.05rem;">{{ $employee->name }}</div>
                                    <small class="text-muted font-weight-700 uppercase" style="font-size: 0.65rem; color: var(--orb-primary) !important;">{{ $employee->department->name ?? 'GENERAL' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-dark font-weight-800" style="letter-spacing: 0.5px;">{{ $employee->employee_id ?? 'ID-'.$employee->id }}</span>
                        </td>
                        <td>
                            <div class="small font-weight-bold">{{ $employee->user->email }}</div>
                            <small class="text-muted">{{ $employee->employeeDetail->phone ?? '---' }}</small>
                        </td>
                        <td>
                            <div class="small font-weight-bold">{{ $employee->position->name ?? 'Executive' }}</div>
                            <span class="badge badge-light border font-weight-800" style="font-size: 0.65rem;">{{ $employee->employment_type }}</span>
                        </td>
                        <td>
                            @if($employee->employee_status == 'WFH')
                                <span class="badge" style="background: rgba(0, 188, 212, 0.1); color: #00bcd4; border-radius: 8px; font-weight: 800; font-size: 0.7rem;"><i class="fas fa-home mr-1"></i> WFH</span>
                            @else
                                <span class="badge" style="background: rgba(75, 0, 232, 0.05); color: var(--orb-primary); border-radius: 8px; font-weight: 800; font-size: 0.7rem;"><i class="fas fa-building mr-1"></i> WFO</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $statusColor = [
                                    'Active' => '#4b00e8',
                                    'Probation' => '#ffb101',
                                    'Inactive' => '#ec4e74',
                                    'Completed' => '#8600ee'
                                ][$employee->status] ?? '#64748b';
                            @endphp
                            <span class="badge-premium" style="background: {{ $statusColor }}15; color: {{ $statusColor }};">
                                <span class="status-dot" style="background: {{ $statusColor }};"></span>{{ $employee->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('employees-data.show', $employee->id) }}" class="action-btn-circle btn-view" title="View Profile"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('employees-data.edit', $employee->id) }}" class="action-btn-circle btn-edit" title="Edit Record"><i class="fas fa-edit"></i></a>
                                
                                @if($employee->employeeDetail && $employee->employeeDetail->cv)
                                    @php
                                        $cvPath = $employee->employeeDetail->cv;
                                        $cvUrl = (strpos($cvPath, 'http') === 0) ? $cvPath : asset($cvPath);
                                    @endphp
                                    <a href="{{ $cvUrl }}" target="_blank" class="action-btn-circle btn-pdf" title="Download Resume"><i class="fas fa-file-pdf"></i></a>
                                @endif

                                <form action="{{ route('employees-data.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Confirm employee deletion? This action cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn-circle btn-delete" title="Delete Account"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted font-weight-bold">No employees matched your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="mobile-view">
        @forelse ($employees as $employee)
        <div class="mobile-card">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-wrapper mr-3" style="width: 60px; height: 60px;">
                    <img src="{{ Str::startsWith($employee->employeeDetail->photo ?? '', 'http') ? ($employee->employeeDetail->photo) : asset($employee->employeeDetail->photo ?? 'images/profile.png') }}" class="avatar-img" alt="Avatar">
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-0 font-weight-bold" style="font-size: 1.1rem;">{{ $employee->name }}</h5>
                            <small class="font-weight-bold" style="color: var(--orb-primary);">{{ $employee->employee_id ?? 'ID-'.$employee->id }}</small>
                        </div>
                        @php
                            $statusColor = ['Active' => '#4b00e8', 'Probation' => '#ffb101', 'Inactive' => '#ec4e74', 'Completed' => '#8600ee'][$employee->status] ?? '#64748b';
                        @endphp
                        <span class="badge" style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-radius: 8px; font-size: 0.7rem; font-weight: 800;">
                            {{ $employee->status }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <small class="text-muted d-block uppercase font-weight-800" style="font-size: 0.55rem; letter-spacing: 0.5px;">Department</small>
                    <span class="font-weight-bold small">{{ $employee->department->name ?? '---' }}</span>
                </div>
                <div class="col-6 text-right">
                    <small class="text-muted d-block uppercase font-weight-800" style="font-size: 0.55rem; letter-spacing: 0.5px;">Mode</small>
                    <span class="font-weight-bold small text-primary">{{ $employee->employee_status }}</span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 mt-2" style="border-top: 1px dashed #e2e8f0;">
                <div class="small text-muted font-weight-bold text-dark">{{ $employee->position->name ?? 'Associate' }}</div>
                <div class="d-flex gap-1">
                    <a href="{{ route('employees-data.show', $employee->id) }}" class="action-btn-circle btn-view" title="View"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('employees-data.edit', $employee->id) }}" class="action-btn-circle btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    @if($employee->employeeDetail && $employee->employeeDetail->cv)
                        <a href="{{ (strpos($employee->employeeDetail->cv, 'http') === 0) ? $employee->employeeDetail->cv : asset($employee->employeeDetail->cv) }}" target="_blank" class="action-btn-circle btn-pdf" title="Resume"><i class="fas fa-file-pdf"></i></a>
                    @endif
                    <form action="{{ route('employees-data.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Delete employee?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn-circle btn-delete" title="Delete"><i class="fas fa-trash-alt"></i></button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <h5 class="text-muted">No records found</h5>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 gap-3">
        <span class="text-muted font-weight-bold">Showing {{ $employees->firstItem() }}-{{ $employees->lastItem() }} of {{ $employees->total() }}</span>
        <div class="pagination-premium">
            {{ $employees->links() }}
        </div>
    </div>
</div>
@endsection
