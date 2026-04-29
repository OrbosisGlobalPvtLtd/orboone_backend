@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'hr-documents'])

@section('_content')
<style>
    :root { --orb-1: #4b00e8; --orb-2: #ffb101; --surface: #ffffff; --background: #f8fafc; }
    
    .page-header {
        background: linear-gradient(135deg, #4b00e8 0%, #ec4e74 100%);
        padding: 60px 40px 100px;
        border-radius: 0 0 50px 50px;
        color: white;
        box-shadow: 0 10px 30px rgba(75,0,232,0.2);
    }

    .card-orb {
        background: var(--surface);
        border-radius: 24px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.03);
    }

    .emp-card {
        display: flex;
        align-items: center;
        padding: 20px;
        background: white;
        border-radius: 20px;
        border: 1px solid #eef2f6;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        margin-bottom: 16px;
        gap: 18px;
    }
    .emp-card:hover {
        border-color: var(--orb-1);
        box-shadow: 0 12px 25px rgba(75,0,232,0.1);
        transform: translateY(-5px);
        background: #f9faff;
    }

    .emp-avatar {
        width: 60px; height: 60px;
        border-radius: 18px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }

    .badge-orb {
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
    }
    .badge-full    { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .badge-partial { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .badge-none    { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    .search-input {
        border-radius: 16px;
        border: 2px solid #eef2f6;
        padding: 12px 20px;
        font-weight: 600;
        height: 54px;
        background: #f8fafc;
        transition: all 0.3s;
    }
    .search-input:focus {
        border-color: var(--orb-1);
        background: white;
        box-shadow: 0 0 0 4px rgba(75,0,232,0.1);
    }

    .stat-pill {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 15px 25px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.2);
    }

    .offset-card { margin-top: -70px; position: relative; z-index: 10; }
    
    .section-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #64748b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

<div class="container-fluid p-0">
    {{-- Header --}}
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div>
                <p class="small mb-2 font-weight-bold" style="opacity:0.8; letter-spacing:2px; text-transform:uppercase;">
                    <i class="fas fa-shield-check mr-2"></i>Institutional Compliance
                </p>
                <h1 class="font-weight-bold mb-2">Document Control Center</h1>
                <p class="mb-0 opacity-75 max-width-600">
                    Oversee employee credential verification and manage regulatory document tracking in a unified workspace.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <div class="stat-pill">
                    <div class="font-weight-bold h3 mb-0">{{ $employees->count() }}</div>
                    <div class="small text-uppercase font-weight-bold opacity-75">Headcount</div>
                </div>
                @php $totalPending = 0; foreach($employees as $e) $totalPending += $e->documents->where('status','pending')->count(); @endphp
                <div class="stat-pill">
                    <div class="font-weight-bold h3 mb-0">{{ $totalPending }}</div>
                    <div class="small text-uppercase font-weight-bold opacity-75">Pending Action</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="container-fluid px-3 px-md-5 offset-card">

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-lg mb-4 p-3 font-weight-bold">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row">
            {{-- Search & List --}}
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card-orb p-4 h-100">
                    <span class="section-label"><i class="fas fa-search text-primary"></i>Personnel Navigator</span>
                    
                    <div class="mb-4 position-relative">
                        <i class="fas fa-search position-absolute" style="left:20px; top:19px; color:#64748b;"></i>
                        <input type="text" id="empSearch" class="form-control search-input pl-5" 
                               placeholder="Filter by name..." onkeyup="filterEmployees()">
                    </div>

                    <div id="empList" style="max-height:800px; overflow-y:auto; padding-right:5px;">
                        @forelse($employees as $emp)
                            @php
                                $empDocs = $emp->documents;
                                $pending = $empDocs->where('status','pending')->count();
                                $verified = $empDocs->where('status','verified')->count();
                                $mandatory = 5; // Configurable
                                
                                $badgeClass = $verified >= $mandatory ? 'badge-full' : ($verified > 0 ? 'badge-partial' : 'badge-none');
                                $badgeLabel = $verified >= $mandatory ? 'Verified' : "$verified/$mandatory";
                                
                                $empDetail = $emp->employee->employeeDetail ?? null;
                                $photo = $empDetail->photo ?? ($empDetail->image ?? 'images/profile.png');
                            @endphp
                            <a href="{{ route('hr.documents.show', $emp->id) }}" class="emp-card" data-name="{{ strtolower($emp->name) }}">
                                <img src="{{ asset($photo) }}" class="emp-avatar" onerror="this.src='{{ asset('images/profile.png') }}'">
                                <div class="emp-info">
                                    <div class="emp-name text-truncate">{{ $emp->name }}</div>
                                    <div class="small text-muted font-weight-bold">{{ $emp->employee->department->name ?? 'N/A' }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="badge-orb {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                    @if($pending > 0)
                                        <div class="mt-1">
                                            <span class="badge badge-warning text-white rounded-pill px-2 py-1" style="font-size:0.6rem;">{{ $pending }} Pending</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-5 opacity-50">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>No records found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Queue & Stats --}}
            <div class="col-xl-8 col-lg-7">
                <div class="card-orb p-4 mb-4">
                    <span class="section-label"><i class="fas fa-chart-line text-success"></i>Verification Health</span>
                    <div class="row">
                        @php
                            $allDocs = $employees->pluck('documents')->flatten();
                            $waitCount = $allDocs->where('status','pending')->count();
                            $doneCount = $allDocs->where('status','verified')->count();
                            $rejCount = $allDocs->where('status','rejected')->count();
                        @endphp
                        <div class="col-md-4 mb-3">
                            <div class="p-4 rounded-lg" style="background:#fff7ed; border:1px solid #ffedd5;">
                                <div class="h2 font-weight-bold text-warning mb-1">{{ $waitCount }}</div>
                                <div class="small font-weight-bold text-uppercase opacity-50">Awaiting Review</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-4 rounded-lg" style="background:#f0fdf4; border:1px solid #dcfce7;">
                                <div class="h2 font-weight-bold text-success mb-1">{{ $doneCount }}</div>
                                <div class="small font-weight-bold text-uppercase opacity-50">Authenticated</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-4 rounded-lg" style="background:#fef2f2; border:1px solid #fee2e2;">
                                <div class="h2 font-weight-bold text-danger mb-1">{{ $rejCount }}</div>
                                <div class="small font-weight-bold text-uppercase opacity-50">Action Needed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-orb p-4">
                    <span class="section-label"><i class="fas fa-history text-muted"></i>Recent Submission Queue</span>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="border-0 text-muted small font-weight-bold">Staff Member</th>
                                    <th class="border-0 text-muted small font-weight-bold">Document Title</th>
                                    <th class="border-0 text-muted small font-weight-bold">Timestamp</th>
                                    <th class="border-0 text-right text-muted small font-weight-bold">Review</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allDocs->where('status','pending')->sortByDesc('created_at')->take(10) as $doc)
                                    <tr>
                                        <td class="align-middle font-weight-bold">{{ $doc->user->name ?? 'System' }}</td>
                                        <td class="align-middle"><span class="badge badge-light px-3 py-2">{{ $doc->type->name ?? $doc->document_type }}</span></td>
                                        <td class="align-middle small text-muted">{{ $doc->created_at->diffForHumans() }}</td>
                                        <td class="text-right align-middle">
                                            <a href="{{ route('hr.documents.show', $doc->user_id) }}" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function filterEmployees() {
        let q = document.getElementById('empSearch').value.toLowerCase();
        document.querySelectorAll('#empList .emp-card').forEach(card => {
            card.style.display = card.getAttribute('data-name').includes(q) ? 'flex' : 'none';
        });
    }
</script>
@endsection
