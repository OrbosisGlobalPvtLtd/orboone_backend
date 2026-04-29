@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'performance'])

@section('_content')
<style>
    :root {
        --orbosis-gradient: linear-gradient(135deg, #4b00e8 0%, #ffb101 100%);
        --soft-bg: #f8faff;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        --border-radius: 20px;
    }

    .premium-header {
        background: var(--orbosis-gradient);
        border-radius: 0 0 var(--border-radius) var(--border-radius);
        padding: 40px 40px 80px;
        color: white;
        margin-bottom: -50px;
        position: relative;
        overflow: hidden;
    }

    .premium-header::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .content-card {
        background: white;
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--card-shadow);
        padding: 30px;
        margin-bottom: 30px;
    }

    .filter-section {
        background: #fff;
        border-radius: 18px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.04);
        border: 1px solid #edf2f9;
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #858796;
        margin-bottom: 8px;
        letter-spacing: 1px;
    }

    .orb-input {
        border-radius: 12px;
        height: 48px;
        border: 2px solid #ebf0f6;
        font-weight: 600;
        background: #f8faff;
        box-shadow: none !important;
        transition: all 0.3s;
    }
    
    .orb-input:focus {
        border-color: #4b00e8;
        background: #fff;
    }

    .score-badge {
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.8rem;
        display: inline-block;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .score-high { background: rgba(28, 200, 138, 0.15); color: #15a06d; border: 1px solid #1cc88a; }
    .score-mid { background: rgba(246, 194, 62, 0.15); color: #d6a121; border: 1px solid #f6c23e; }
    .score-low { background: rgba(231, 74, 59, 0.15); color: #c43224; border: 1px solid #e74a3b; }

    .employee-avatar {
        width: 45px;
        height: 45px;
        border-radius: 14px;
        object-fit: cover;
        margin-right: 15px;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .table thead th {
        background: #f8faff;
        border: none;
        color: #4e73df;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1px;
        padding: 18px 15px;
    }

    .table td {
        vertical-align: middle;
        padding: 18px 15px;
        border-top: 1px solid #f1f4f8;
        color: #5a5c69;
        font-weight: 500;
    }

    .table tbody tr {
        transition: all 0.2s;
    }

    .table tbody tr:hover {
        background: #fafbff;
        transform: translateY(-1px);
    }

    .btn-premium {
        background: var(--orbosis-gradient);
        color: white !important;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(75, 0, 232, 0.3);
    }

    .btn-premium:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(75, 0, 232, 0.4);
        filter: brightness(1.1);
    }

    .btn-action {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 4px;
        transition: all 0.3s;
        border: none;
    }

    .btn-view { background: rgba(75, 0, 232, 0.1); color: #4b00e8; border: 1px solid rgba(75, 0, 232, 0.2); }
    .btn-edit { background: rgba(246, 194, 62, 0.15); color: #d6a121; border: 1px solid rgba(246, 194, 62, 0.3); }
    .btn-delete { background: rgba(231, 74, 59, 0.15); color: #c43224; border: 1px solid rgba(231, 74, 59, 0.3); }

    .btn-action:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .btn-view:hover { background: #4b00e8; color: white; }
    .btn-edit:hover { background: #f6c23e; color: white; }
    .btn-delete:hover { background: #e74a3b; color: white; }

    /* Mobile Responsive Customizations */
    @media (max-width: 768px) {
        .premium-header {
            padding: 30px 20px 70px;
        }
        .filter-section .row > div {
            margin-bottom: 15px;
        }
        .content-card {
            padding: 15px;
        }
        
        .desktop-table-header { display: none; }
        
        .table, .table tbody, .table tr, .table td {
            display: block;
            width: 100%;
        }
        
        .table tr {
            margin-bottom: 20px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #edf2f9;
            padding: 15px;
        }
        
        .table td {
            text-align: right;
            padding: 10px 0;
            border-top: 1px solid #f8faff;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table td::before {
            content: attr(data-label);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.7rem;
            color: #858796;
            margin-right: 15px;
        }
        
        .table td.emp-col {
            display: flex;
            text-align: left;
            border-top: none;
            padding-top: 0;
            justify-content: flex-start;
        }
        
        .table td.emp-col::before { display: none; }
        
        .table td.action-col {
            justify-content: flex-end;
            padding-bottom: 0;
        }
    }
</style>

<div class="premium-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0 text-center text-lg-left">
                <h2 class="font-weight-bold mb-2" style="font-size: 2.2rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    <i class="fas fa-medal mr-2"></i> Performance Intelligence
                </h2>
                <p class="mb-0" style="font-weight: 500; font-size: 1.1rem; opacity: 0.9;">
                    Track and analyze employee performance insights
                </p>
            </div>
            <div class="col-lg-6 col-md-12 text-center text-lg-right">
                @if (collect($accesses)->where('menu_id', 3)->first()->status == 2)
                <a href="{{ route('employees-performance-score.create') }}" class="btn btn-light rounded-pill px-4 py-2 font-weight-bold shadow-sm" style="color: #4b00e8;">
                    <i class="fas fa-plus-circle mr-2"></i> Create Assessment
                </a>
                @endif
                <a href="{{ route('employees-performance-score.print') }}" class="btn btn-outline-light ml-2 rounded-pill px-4 py-2 font-weight-bold" target="_blank" style="border-width: 2px;">
                    <i class="fas fa-print mr-2"></i> Export Report
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-2 px-md-4 py-4" style="position: relative; z-index: 10;">
    <div class="filter-section mt-2 shadow-lg">
        <form action="{{ route('employees-performance-score') }}" method="GET" class="row align-items-end">
            <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                <label class="filter-label">Search Employee</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0" style="border-radius: 12px 0 0 12px; border-color: #ebf0f6;"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="search" class="form-control border-left-0 orb-input" placeholder="Name or ID..." value="{{ request('search') }}" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <label class="filter-label">From Date</label>
                <input type="date" name="date_from" class="form-control orb-input" value="{{ request('date_from') }}">
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <label class="filter-label">To Date</label>
                <input type="date" name="date_to" class="form-control orb-input" value="{{ request('date_to') }}">
            </div>
            <div class="col-lg-2 col-md-12">
                <button type="submit" class="btn btn-premium w-100">
                    <i class="fas fa-filter mr-1"></i> Apply Filter
                </button>
            </div>
        </form>
    </div>

    <div class="content-card shadow-lg">
        @if (session('status'))
        <div class="alert alert-dismissible fade show border-0 mb-4" role="alert" style="border-radius: 15px; background: #f0fdf4; color: #16a34a; font-weight: 600; padding: 15px 20px; box-shadow: 0 5px 15px rgba(22, 163, 74, 0.1);">
            <i class="fas fa-check-circle mr-2 fa-lg"></i> {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #16a34a;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="table-responsive" style="overflow-x: hidden;">
            <table class="table mb-0">
                <thead class="desktop-table-header">
                    <tr>
                        <th width="60" class="text-center">#</th>
                        <th>Employee Details</th>
                        <th class="text-center">Avg Score</th>
                        <th>Assessed By</th>
                        <th>Review Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employeeScores as $score)
                    @php
                        // robust employee photo resolver
                        $empPhoto = $score->employee->employeeDetail->photo ?? null;
                        $empPhotoUrl = asset('images/profile.png');
                        if($empPhoto) {
                            $cleanEmpPath = str_replace('public/', '', $empPhoto);
                            $empPhotoUrl = \Illuminate\Support\Str::startsWith($cleanEmpPath, ['http', 'uploads/']) ? asset($cleanEmpPath) : asset("storage/{$cleanEmpPath}");
                        }
                        
                        // robust scorer photo resolver
                        $scorerPhoto = $score->scoredBy->employeeDetail->photo ?? null;
                        $scorerPhotoUrl = asset('images/profile.png');
                        if($scorerPhoto) {
                            $cleanScorerPath = str_replace('public/', '', $scorerPhoto);
                            $scorerPhotoUrl = \Illuminate\Support\Str::startsWith($cleanScorerPath, ['http', 'uploads/']) ? asset($cleanScorerPath) : asset("storage/{$cleanScorerPath}");
                        }
                    @endphp
                    <tr>
                        <td data-label="ID" class="text-md-center font-weight-bold" style="color: #4b00e8;">
                            #{{ str_pad($loop->iteration + $employeeScores->firstItem() - 1, 2, '0', STR_PAD_LEFT) }}
                        </td>
                        <td data-label="Employee" class="emp-col">
                            <div class="d-flex align-items-center">
                                <img src="{{ $empPhotoUrl }}" class="employee-avatar" onerror="this.src='{{ asset('images/profile.png') }}'" alt="">
                                <div>
                                    <h6 class="mb-1 font-weight-bold text-dark" style="font-size: 0.95rem;">{{ $score->employee->name }}</h6>
                                    <span class="badge badge-pill" style="background: #f1f5f9; color: #475569; font-weight: 700;">{{ $score->employee->employee_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Score" class="text-md-center">
                            @php
                                $avg = $score->average_score;
                                $color = $avg >= 8 ? 'score-high' : ($avg >= 5 ? 'score-mid' : 'score-low');
                                $icon = $avg >= 8 ? 'fa-arrow-up' : ($avg >= 5 ? 'fa-minus' : 'fa-arrow-down');
                            @endphp
                            <span class="score-badge {{ $color }}">
                                <i class="fas {{ $icon }} mr-1"></i> {{ number_format($avg, 1) }} <span style="opacity:0.6; font-size:0.7rem;">/10.0</span>
                            </span>
                        </td>
                        <td data-label="Assessed By">
                            <div class="d-flex align-items-center justify-content-end justify-content-md-start">
                                <img src="{{ $scorerPhotoUrl }}" class="employee-avatar" onerror="this.src='{{ asset('images/profile.png') }}'" style="width: 35px; height: 35px; margin-right: 10px;" alt="">
                                <span class="font-weight-bold" style="font-size: 0.85rem;">{{ $score->scoredBy->name }}</span>
                            </div>
                        </td>
                        <td data-label="Date">
                            <div class="font-weight-bold" style="font-size: 0.85rem;">
                                <i class="far fa-calendar-check mr-2" style="color: #4b00e8; opacity: 0.7;"></i> 
                                {{ \Carbon\Carbon::parse($score->created_at)->format('d M, Y') }}
                            </div>
                        </td>
                        <td data-label="Actions" class="text-md-right action-col">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('employees-performance-score.show', ['employeeScore' => $score->group_id]) }}" class="btn-action btn-view shadow-sm" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if (collect($accesses)->where('menu_id', 3)->first()->status == 2)
                                <a href="{{ route('employees-performance-score.edit', ['employeeScore' => $score->group_id]) }}" class="btn-action btn-edit shadow-sm" title="Edit Assessment">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('employees-performance-score.destroy', ['employeeScore' => $score->group_id]) }}" method="POST" class="d-inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete shadow-sm" onclick="return confirm('Are you sure you want to permanently delete this performance record?')" title="Delete Record">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 border-0">
                            <i class="fas fa-clipboard-list mb-3" style="font-size: 4rem; color: #4b00e8; opacity: 0.15;"></i>
                            <h5 class="font-weight-bold text-dark mt-2">No Assessments Found</h5>
                            <p class="text-muted mb-4">You haven't recorded any performance scores matching your criteria.</p>
                            @if (collect($accesses)->where('menu_id', 3)->first()->status == 2)
                            <a href="{{ route('employees-performance-score.create') }}" class="btn btn-premium mt-2">
                                <i class="fas fa-plus mr-2"></i> Create First Assessment
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4 pt-3" style="border-top: 1px solid #f1f4f8;">
            {{ $employeeScores->links() }}
        </div>
    </div>
</div>
@endsection