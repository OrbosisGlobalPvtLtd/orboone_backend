@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'attendances'])

@section('_content')
<style>
    :root {
        --orb-primary: #4b00e8;
        --orb-secondary: #8600ee;
        --orb-success: #28a745;
        --orb-danger: #ec4e74;
        --orb-warning: #ffb101;
        --orb-info: #74b9ff;
        --orb-glass: rgba(255, 255, 255, 0.95);
        --orb-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .attendance-container {
        padding: 2rem;
        background: #f8fafc;
        min-height: 100vh;
    }

    .glass-card {
        background: var(--orb-glass);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .stat-card-new {
        border-radius: 18px;
        padding: 1.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .stat-card-new:hover { transform: translateY(-8px); }
    .stat-card-new i { position: absolute; right: -10px; top: -10px; font-size: 5rem; opacity: 0.15; transform: rotate(-15deg); }

    .bg-orb-primary { background: linear-gradient(135deg, #4b00e8, #8600ee); }
    .bg-orb-warning { background: linear-gradient(135deg, #ffb101, #f08c00); }
    .bg-orb-danger { background: linear-gradient(135deg, #ec4e74, #ff7675); }
    .bg-orb-info { background: linear-gradient(135deg, #74b9ff, #0984e3); }

    .table-orb { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .table-orb thead th { 
        background: transparent; 
        border: none; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        font-weight: 800; 
        color: #64748b; 
        padding: 15px 20px;
        letter-spacing: 1px;
    }
    .table-orb tbody tr { 
        background: white; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }
    .table-orb tbody tr:hover { transform: scale(1.005); box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .table-orb td { padding: 15px 20px; border: none; vertical-align: middle; }
    .table-orb td:first-child { border-radius: 12px 0 0 12px; }
    .table-orb td:last-child { border-radius: 0 12px 12px 0; }

    .badge-orb {
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: none;
        margin: 0 2px;
    }
    .btn-edit-orb { background: #eff6ff; color: #2563eb; }
    .btn-edit-orb:hover { background: #2563eb; color: white; }
    .btn-delete-orb { background: #fef2f2; color: #dc2626; }
    .btn-delete-orb:hover { background: #dc2626; color: white; }
    .btn-unlock-orb { background: #fdf2f8; color: #db2777; }
    .btn-unlock-orb:hover { background: #db2777; color: white; }

    .mobile-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.2rem;
        box-shadow: var(--orb-shadow);
        border: 1px solid #f1f5f9;
        display: none;
    }

    .avatar-orb { width: 45px; height: 45px; border-radius: 14px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

    @media (max-width: 1024px) {
        .desktop-view { display: none; }
        .mobile-card { display: block; }
        .attendance-container { padding: 1rem; }
    }

    .search-input-orb {
        border-radius: 15px;
        border: 1px solid #e2e8f0;
        padding: 10px 20px;
        transition: all 0.3s;
    }
    .search-input-orb:focus { box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1); border-color: var(--orb-primary); }

    .btn-primary-orb {
        background: linear-gradient(135deg, #4b00e8, #8600ee);
        color: white;
        border-radius: 15px;
        padding: 10px 25px;
        font-weight: 700;
        border: none;
        box-shadow: 0 4px 15px rgba(75, 0, 232, 0.3);
    }
    .btn-primary-orb:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(75, 0, 232, 0.4); color: white; }
</style>

<div class="attendance-container">
    
    <!-- Professional Header -->
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-5 gap-4">
        <div class="header-content">
            <h3 class="font-weight-bold text-dark m-0" style="letter-spacing: -0.5px; font-size: 1.75rem;">Attendance Tracker</h3>
            <p class="text-muted small mb-0">Monitor real-time presence and export detailed reports.</p>
        </div>
        <div class="header-actions d-flex flex-wrap align-items-center gap-3">
            <div class="action-group d-flex gap-2">
                <button class="btn btn-primary-orb px-4 shadow-sm" data-toggle="modal" data-target="#adminPunchInModal" style="height: 48px; border-radius: 12px;">
                    <i class="fas fa-sign-in-alt mr-2"></i> Punch In
                </button>
                <button class="btn btn-primary-orb px-4 shadow-sm" style="background: linear-gradient(135deg, #ec4e74, #ff7675); box-shadow: 0 4px 15px rgba(236, 78, 116, 0.3); height: 48px; border-radius: 12px;" data-toggle="modal" data-target="#adminPunchOutModal">
                    <i class="fas fa-sign-out-alt mr-2"></i> Punch Out
                </button>
            </div>
            <div class="action-group d-flex gap-2">
                <button class="btn btn-primary-orb px-4 shadow-sm" style="background: linear-gradient(135deg, #8600ee, #4b00e8); height: 48px; border-radius: 12px;" data-toggle="modal" data-target="#clockModal">
                    <i class="fas fa-fingerprint mr-2"></i> My Punch
                </button>
                <div class="d-flex gap-1">
                    <a href="{{ route('attendances.export-pdf', ['date' => request('date')]) }}" class="btn btn-outline-dark d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; height: 48px; min-width: 48px; padding: 0 20px; font-weight: 700; border: 2px solid #e2e8f0; background: white; transition: all 0.3s;" title="Export Daily PDF">
                        <i class="fas fa-file-pdf mr-2 text-danger"></i> Export
                    </a>
                    <a href="{{ route('attendances_print') }}" target="_blank" class="btn btn-light d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; height: 48px; width: 48px; background: white; border: 2px solid #e2e8f0; transition: all 0.3s;" title="Print All Records">
                        <i class="fas fa-print text-primary"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Stats Row -->
    <div class="row mb-4 g-4">
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="stat-card-new bg-orb-primary">
                <i class="fas fa-stopwatch"></i>
                <div class="small text-uppercase opacity-7">Total Hours</div>
                <div class="h2 font-weight-bold mb-0">{{ number_format($stats['total_hours'], 1) }}h</div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="stat-card-new bg-orb-warning">
                <i class="fas fa-clock"></i>
                <div class="small text-uppercase opacity-7">Late Marks</div>
                <div class="h2 font-weight-bold mb-0">{{ $stats['total_late'] }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="stat-card-new bg-orb-danger">
                <i class="fas fa-sign-out-alt"></i>
                <div class="small text-uppercase opacity-7">Early Outs</div>
                <div class="h2 font-weight-bold mb-0">{{ $stats['total_early_out'] }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="stat-card-new bg-orb-info">
                <i class="fas fa-user-lock"></i>
                <div class="small text-uppercase opacity-7">Blocked</div>
                <div class="h2 font-weight-bold mb-0">{{ $stats['total_blocked'] }}</div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show glass-card border-0 p-3 mb-4" role="alert" style="background: rgba(40, 167, 69, 0.1); color: #1e7e34;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show glass-card border-0 p-3 mb-4" role="alert" style="background: rgba(236, 78, 116, 0.1); color: #c21a42;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <!-- Optimized Filters -->
    <div class="glass-card p-4 mb-4">
        <form action="{{ route('attendances') }}" method="GET" class="row align-items-end g-3">
            <div class="col-lg-3 col-md-6 mb-2">
                <label class="small font-weight-bold text-dark text-uppercase mb-2">Quick Search</label>
                <input type="text" name="search" class="form-control search-input-orb" placeholder="Name or ID..." value="{{ request('search') }}">
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
                <label class="small font-weight-bold text-dark text-uppercase mb-2">Employee</label>
                <select name="employee_id" class="form-control search-input-orb">
                    <option value="">All Staff</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 mb-2">
                <label class="small font-weight-bold text-dark text-uppercase mb-2">Target Date</label>
                <input type="date" name="date" class="form-control search-input-orb" value="{{ request('date') }}">
            </div>
            <div class="col-lg-2 col-md-4 mb-2">
                <label class="small font-weight-bold text-dark text-uppercase mb-2">Period</label>
                <select name="filter" class="form-control search-input-orb">
                    <option value="">Full History</option>
                    <option value="weekly" {{ request('filter') == 'weekly' ? 'selected' : '' }}>This Week</option>
                    <option value="monthly" {{ request('filter') == 'monthly' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4 mb-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-orb flex-grow-1">Filter</button>
                    <a href="{{ route('attendances') }}" class="btn btn-light" style="border-radius:15px;"><i class="fas fa-sync-alt"></i></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Desktop Data Table -->
    <div class="desktop-view glass-card">
        <div class="table-responsive">
            <table class="table-orb text-center">
                <thead>
                    <tr>
                        <th class="text-left">Employee</th>
                        <th>Date</th>
                        <th>In / Out</th>
                        <th>Net Hours</th>
                        <th>Status</th>
                        <th>Rules</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td class="text-left">
                                <div class="d-flex align-items-center">
                                    @php
                                        $photo = $attendance->user->employee->employeeDetail->photo ?? 'images/profile.png';
                                        // Remove 'public/' prefix if exists for asset() helper
                                        $photo = str_replace('public/', '', $photo);
                                        $finalPhoto = Str::startsWith($photo, 'http') ? $photo : asset($photo);
                                    @endphp
                                    <img src="{{ $finalPhoto }}" class="avatar-orb mr-3" onerror="this.src='{{ asset('images/profile.png') }}'">
                                    <div>
                                        <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">{{ $attendance->user->name ?? 'N/A' }}</div>
                                        <small class="text-muted font-weight-bold" style="letter-spacing: 0.5px;">{{ $attendance->user->employee->employee_id ?? 'ID-'.$attendance->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}</div>
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700;">{{ \Carbon\Carbon::parse($attendance->date)->format('l') }}</small>
                            </td>
                            <td>
                                <div class="d-flex flex-column align-items-center">
                                    <div class="text-success font-weight-bold" style="font-size: 0.9rem;">
                                        <i class="fas fa-sign-in-alt mr-1"></i> {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : '--:--' }}
                                    </div>
                                    <div class="text-danger font-weight-bold" style="font-size: 0.9rem;">
                                        <i class="fas fa-sign-out-alt mr-1"></i> {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') : '--:--' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="h6 mb-0 font-weight-bold text-dark">{{ $attendance->net_duration }}</div>
                                <span class="badge badge-light text-muted" style="font-size: 0.6rem; border-radius: 4px;">Stay: {{ $attendance->duration }}</span>
                            </td>
                            <td>
                                @php
                                    $status = strtolower($attendance->status ?? 'present');
                                    $sColor = '#22c55e'; 
                                    if(str_contains($status, 'absent') || str_contains($status, 'leave')) $sColor = '#ef4444';
                                    if(str_contains($status, 'half')) $sColor = '#f59e0b';
                                    if($attendance->is_blocked || str_contains($status, 'blocked')) $sColor = '#000000';
                                @endphp
                                <span class="badge-orb" style="background: {{ $sColor }}15; color: {{ $sColor }}; border: 1px solid {{ $sColor }}20;">
                                    {{ ($attendance->is_blocked || str_contains($status, 'blocked')) ? 'BLOCKED' : ($attendance->status ?? 'Present') }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @if($attendance->work_type == 'WFO')
                                        <div class="action-btn" style="width: 32px; height: 32px; background: rgba(34, 197, 94, 0.1); color: #16a34a;" title="Work From Office">
                                            <i class="fas fa-building"></i>
                                        </div>
                                    @elseif($attendance->work_type == 'WFH')
                                        <div class="action-btn" style="width: 32px; height: 32px; background: rgba(14, 165, 233, 0.1); color: #0284c7;" title="Work From Home">
                                            <i class="fas fa-house-user"></i>
                                        </div>
                                    @endif

                                    @if($attendance->is_late)
                                        <div class="action-btn" style="width: 32px; height: 32px; background: rgba(255, 177, 1, 0.1); color: var(--orb-warning);" title="Late Log In"><i class="fas fa-clock"></i></div>
                                    @endif
                                    @if($attendance->is_early_out)
                                        <div class="action-btn" style="width: 32px; height: 32px; background: rgba(236, 78, 116, 0.1); color: var(--orb-danger);" title="Early Clock Out"><i class="fas fa-running"></i></div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="d-flex justify-content-end">
                                    @if($attendance->is_blocked)
                                        <form action="{{ route('attendances.unlock') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $attendance->id }}">
                                            <button type="submit" class="action-btn btn-unlock-orb" title="Unlock"><i class="fas fa-unlock"></i></button>
                                        </form>
                                    @endif
                                    <button class="action-btn btn-edit-orb" data-toggle="modal" data-target="#editModal{{ $attendance->id }}" title="Edit Record"><i class="fas fa-pen-nib"></i></button>
                                    <form action="{{ route('attendances.destroy', $attendance->id) }}" method="POST" onsubmit="return confirm('Secure Delete: Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn btn-delete-orb" title="Remove Log"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-5">
                            <div class="text-center">
                                <i class="fas fa-search fa-3x text-muted mb-3 opacity-2"></i>
                                <h6 class="text-muted font-weight-bold">Zero records match your criteria.</h6>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="mobile-view">
        @forelse ($attendances as $attendance)
            <div class="mobile-card shadow-lg p-0 overflow-hidden" style="border: none; border-radius: 25px; margin-bottom: 2rem;">
                <div class="p-4 bg-white">
                    <div class="d-flex align-items-center mb-4">
                        @php
                            $p = $attendance->user->employee->employeeDetail->photo ?? 'images/profile.png';
                            $p = str_replace('public/', '', $p);
                            $fp = Str::startsWith($p, 'http') ? $p : asset($p);
                        @endphp
                        <img src="{{ $fp }}" class="avatar-orb" style="width: 60px; height: 60px; border-radius: 20px;" onerror="this.src='{{ asset('images/profile.png') }}'">
                        <div class="ml-3 flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 1.1rem;">{{ $attendance->user->name }}</h6>
                                    <small class="text-muted font-weight-bold">{{ $attendance->user->employee->employee_id ?? '---' }}</small>
                                </div>
                                @php
                                    $st = strtolower($attendance->status ?? 'present');
                                    $sc = '#22c55e';
                                    if(str_contains($st, 'absent') || str_contains($st, 'leave')) $sc = '#ef4444';
                                    if(str_contains($st, 'half')) $sc = '#f59e0b';
                                @endphp
                                <span class="badge-orb" style="background: {{ $sc }}15; color: {{ $sc }}; border: 1px solid {{ $sc }}20; font-size: 0.65rem;">
                                    {{ $attendance->status ?? 'Present' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-light p-3 border-radius mb-4" style="border-radius: 20px;">
                        <div class="row text-center">
                            <div class="col-4 border-right">
                                <small class="text-muted d-block font-weight-bold text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">Log Date</small>
                                <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($attendance->date)->format('d M') }}</div>
                            </div>
                            <div class="col-4 border-right">
                                <small class="text-muted d-block font-weight-bold text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">Schedule</small>
                                <div class="font-weight-bold text-success" style="font-size: 0.85rem;">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : '--' }}</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block font-weight-bold text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">Duration</small>
                                <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">{{ $attendance->net_duration }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <div class="d-flex gap-2">
                            @if($attendance->work_type == 'WFO')
                                <div class="action-btn" style="width: 35px; height: 35px; background: #f0fdf4; color: #16a34a; border-radius: 10px;" title="Office"><i class="fas fa-building"></i></div>
                            @elseif($attendance->work_type == 'WFH')
                                <div class="action-btn" style="width: 35px; height: 35px; background: #f0f9ff; color: #0284c7; border-radius: 10px;" title="Remote"><i class="fas fa-house-user"></i></div>
                            @endif

                            @if($attendance->is_late)
                                <div class="action-btn" style="width: 35px; height: 35px; background: #fffbeb; color: #d97706; border-radius: 10px;"><i class="fas fa-clock"></i></div>
                            @endif
                            @if($attendance->is_early_out)
                                <div class="action-btn" style="width: 35px; height: 35px; background: #fff1f2; color: #e11d48; border-radius: 10px;"><i class="fas fa-running"></i></div>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light d-flex align-items-center justify-content-center shadow-sm" data-toggle="modal" data-target="#editModal{{ $attendance->id }}" style="width: 45px; height: 45px; border-radius: 15px; border: 1px solid #e2e8f0; background: white; color: var(--orb-primary);">
                                <i class="fas fa-pen-nib"></i>
                            </button>
                            <form action="{{ route('attendances.destroy', $attendance->id) }}" method="POST" onsubmit="return confirm('Remove log?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-light d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px; border-radius: 15px; border: 1px solid #fee2e2; background: #fef2f2; color: #dc2626;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @if($attendance->note)
                    <div class="px-4 py-3 bg-light border-top" style="font-size: 0.8rem; color: #64748b; font-style: italic;">
                        <i class="fas fa-comment-dots mr-2 text-primary opacity-5"></i> "{{ Str::limit($attendance->note, 50) }}"
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-5 glass-card bg-white" style="border-radius: 30px;">
                <div class="opacity-1 mb-3">
                    <i class="fas fa-calendar-times fa-4x text-muted"></i>
                </div>
                <h6 class="font-weight-bold text-dark">No records found for this period.</h6>
                <p class="small text-muted">Try adjusting your filters or date range.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4 glass-card p-3 d-flex flex-column flex-md-row align-items-center justify-content-between bg-white">
        <div class="small text-muted mb-2 mb-md-0">Showing {{ $attendances->firstItem() ?? 0 }} - {{ $attendances->lastItem() ?? 0 }} of {{ $attendances->total() }} results</div>
        {{ $attendances->links() }}
    </div>

</div>

<!-- Clock Modal -->
<div class="modal fade" id="clockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card shadow-lg border-0" style="border-radius: 30px;">
            <div class="modal-header bg-orb-primary text-white py-4 border-0">
                <h5 class="modal-title font-weight-bold mx-auto"><i class="fas fa-clock mr-2"></i> Instant Punch</h5>
                <button type="button" class="close text-white position-absolute" style="right: 20px;" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('attendances.store') }}" method="POST">
                @csrf
                <div class="modal-body text-center p-5">
                    <div id="orb-real-time" class="h1 font-weight-bold text-primary mb-2" style="font-size: 3.5rem; letter-spacing: -2px;">{{ date('H:i:s') }}</div>
                    <p class="text-muted font-weight-bold mb-4"><i class="fas fa-calendar-check mr-1 text-primary"></i> {{ date('l, d M Y') }}</p>
                    
                    <div class="form-group text-left mb-4">
                        <label class="small font-weight-bold text-uppercase opacity-5">Environment</label>
                        <select name="work_type" class="form-control search-input-orb" required style="height: 55px;">
                            <option value="WFO">🏢 Work From Office</option>
                            <option value="WFH">🏠 Work From Home</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 px-5 pb-5">
                    <button type="submit" class="btn btn-primary-orb btn-lg btn-block font-weight-bold py-3 shadow-lg" style="border-radius: 20px;">
                        Capture My Presence <i class="fas fa-fingerprint ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Admin Punch In Modal -->
<div class="modal fade" id="adminPunchInModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0" style="border-radius: 25px;">
            <div class="modal-header bg-success text-white py-4 border-0">
                <h5 class="modal-title font-weight-bold mx-auto"><i class="fas fa-sign-in-alt mr-2"></i> Admin Manual Entry</h5>
                <button type="button" class="close text-white position-absolute" style="right: 20px;" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('attendances.admin.punch-in') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Employee Name</label>
                        <select name="user_id" class="form-control search-input-orb" required>
                            <option value="">Choose Employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-uppercase">Log Time</label>
                                <input type="time" name="time" class="form-control search-input-orb" required value="{{ date('H:i') }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-uppercase">Mode</label>
                                <select name="work_type" class="form-control search-input-orb" required>
                                    <option value="WFO">Office</option>
                                    <option value="WFH">Remote</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-uppercase">Forced Attendance State</label>
                        <select name="status" class="form-control search-input-orb" required>
                            @foreach(['Present', 'Half Day Leave', 'Full Day Leave', 'LWP', 'Absent', 'Blocked'] as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-success btn-lg btn-block font-weight-bold shadow" style="border-radius: 18px;">Confirm Punch In</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Admin Punch Out Modal -->
<div class="modal fade" id="adminPunchOutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0" style="border-radius: 25px;">
            <div class="modal-header bg-danger text-white py-4 border-0">
                <h5 class="modal-title font-weight-bold mx-auto"><i class="fas fa-sign-out-alt mr-2"></i> Manual Punch Out</h5>
                <button type="button" class="close text-white position-absolute" style="right: 20px;" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('attendances.admin.punch-out') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Employee Name</label>
                        <select name="user_id" class="form-control search-input-orb" required>
                            <option value="">Choose Employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-uppercase">Clock Out Time</label>
                        <input type="time" name="time" class="form-control search-input-orb" required value="{{ date('H:i') }}">
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-danger btn-lg btn-block font-weight-bold shadow" style="border-radius: 18px;">Confirm Punch Out</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Consolidated Row-specific Modals -->
@foreach($attendances as $attendance)
    <div class="modal fade" id="editModal{{ $attendance->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title font-weight-bold">Modify Daily Entry</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('attendances.update') }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="id" value="{{ $attendance->id }}">
                    <div class="modal-body p-4 text-left">
                        <div class="p-3 mb-4 d-flex align-items-center" style="background: #f8fafc; border-radius: 15px; border: 1px solid #e2e8f0;">
                            @php
                                $modalPhoto = $attendance->user->employee->employeeDetail->photo ?? 'images/profile.png';
                                $modalPhoto = str_replace('public/', '', $modalPhoto);
                                $modalFinalPhoto = Str::startsWith($modalPhoto, 'http') ? $modalPhoto : asset($modalPhoto);
                            @endphp
                            <img src="{{ $modalFinalPhoto }}" class="avatar-orb mr-3" style="width: 40px; height: 40px;" onerror="this.src='{{ asset('images/profile.png') }}'">
                            <div>
                                <div class="font-weight-bold text-dark">{{ $attendance->user->name }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($attendance->date)->format('l, d M Y') }}</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="small font-weight-bold text-uppercase text-muted mb-2">Clock In</label>
                                <input type="time" name="clock_in" class="form-control search-input-orb" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small font-weight-bold text-uppercase text-muted mb-2">Clock Out</label>
                                <input type="time" name="clock_out" class="form-control search-input-orb" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small font-weight-bold text-uppercase text-muted mb-2">Admin Override Status</label>
                            <select name="status" class="form-control search-input-orb">
                                @foreach(['Present', 'Half Day Leave', 'Full Day Leave', 'Absent', 'LWP', 'Blocked'] as $opt)
                                    <option value="{{ $opt }}" {{ $attendance->status == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="small font-weight-bold text-uppercase text-muted mb-2">Internal Note</label>
                            <textarea name="note" class="form-control search-input-orb" rows="2" placeholder="Describe the adjustment reasons...">{{ $attendance->note }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="submit" class="btn btn-primary-orb btn-block py-3" style="border-radius: 18px;">Save Override</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
    function updateClock() {
        const clockElement = document.getElementById('orb-real-time');
        if (clockElement) {
            const now = new Date();
            clockElement.textContent = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }
    setInterval(updateClock, 1000);
</script>

@endsection
