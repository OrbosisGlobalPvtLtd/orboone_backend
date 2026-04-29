@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'leave-summary'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1 font-weight-bold text-dark">Leave Summary</h2>
                <p class="text-muted">Comprehensive overview of leave allocations and balances for all employees ({{ Carbon\Carbon::now()->year }}).</p>
            </div>
            <div>
                <a href="{{ route('employees-leave-request') }}" class="btn btn-outline-primary shadow-sm px-4">
                    <i class="fas fa-check-double mr-2"></i> Leave Approval
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-2"></i>Upcoming Holidays</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle text-center">
                            <thead class="bg-light text-uppercase small font-weight-bold text-muted">
                                <tr>
                                    <th class="px-4 text-left">Holiday</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($holidays as $holiday)
                                    <tr>
                                        <td class="px-4 text-left font-weight-bold text-dark">{{ $holiday->name }}</td>
                                        <td>{{ Carbon\Carbon::parse($holiday->date)->format('d M, Y') }}</td>
                                        <td>{{ Carbon\Carbon::parse($holiday->date)->format('l') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">No holidays found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-users-cog mr-2"></i>Employee Leave Balances</h5>
                    <div class="small text-muted">
                        <span class="mr-3"><i class="fas fa-circle text-primary mr-1"></i> PL: Paid Leave</span>
                        <span><i class="fas fa-circle text-success mr-1"></i> SL: Sick Leave</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle text-center">
                            <thead class="bg-light text-uppercase small font-weight-bold text-muted">
                                <tr>
                                    <th class="px-4 text-left">Employee</th>
                                    <th>Joining Date</th>
                                    <th>PL (Used/Quota)</th>
                                    <th>SL (Used/Quota)</th>
                                    <th class="text-danger">LWP</th>
                                    <th class="text-right px-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    @php
                                        $alloc    = $employee->leaveAllocations->first();
                                        $plQuota  = $alloc->total_pl ?? 0;
                                        $plUsed   = $alloc->used_pl  ?? 0;
                                        $slQuota  = $alloc->total_sl ?? 0;
                                        $slUsed   = $alloc->used_sl  ?? 0;
                                        $lwpCount = $alloc->lwp_days ?? 0;
                                    @endphp
                                    <tr>
                                        <td class="px-4 text-left">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle mr-3" style="width: 40px; height: 40px; border-radius: 50%; background: #f4f6f9; display: flex; align-items: center; justify-content: center; color: #4e73df; font-weight: bold; border: 1px solid #eaecf4;">
                                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="font-weight-bold d-block text-dark">{{ $employee->name }}</span>
                                                    <small class="text-muted">{{ $employee->department->name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small font-weight-bold">{{ $employee->start_of_contract ? Carbon\Carbon::parse($employee->start_of_contract)->format('d M, Y') : 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="font-weight-bold text-dark">{{ $plUsed }} / {{ $plQuota }}</span>
                                                <div class="progress mt-1" style="width: 60px; height: 4px;">
                                                    @php $plP = $plQuota > 0 ? ($plUsed / $plQuota) * 100 : 0; @endphp
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $plP }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="font-weight-bold text-dark">{{ $slUsed }} / {{ $slQuota }}</span>
                                                <div class="progress mt-1" style="width: 60px; height: 4px;">
                                                    @php $slP = $slQuota > 0 ? ($slUsed / $slQuota) * 100 : 0; @endphp
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $slP }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                       
                                        <td>
                                            <span class="badge badge-pill badge-light border text-danger font-weight-bold">Count: {{ $lwpCount }}</span>
                                        </td>
                                        <td class="text-right px-4">
                                            <button class="btn btn-sm btn-outline-info shadow-sm" data-toggle="collapse" data-target="#history{{ $employee->id }}">
                                                <i class="fas fa-history mr-1"></i> History
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="history{{ $employee->id }}">
                                        <td colspan="7" class="bg-light p-4">
                                            <div class="card border-0 shadow-none bg-transparent">
                                                <h6 class="font-weight-bold mb-3 text-dark"><i class="fas fa-stream mr-2 text-primary"></i>Detailed Leave History: {{ $employee->name }}</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered bg-white shadow-sm">
                                                        <thead class="bg-light">
                                                            <tr class="small text-uppercase">
                                                                <th>Type</th>
                                                                <th>Period</th>
                                                                <th>Days</th>
                                                                <th>Reason</th>
                                                                <th>Status</th>
                                                                <th>Comment</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($employee->leaveRequests->sortByDesc('created_at') as $req)
                                                                <tr>
                                                                    <td><span class="badge badge-pill badge-light border">{{ $req->leave_type_name }}</span></td>
                                                                    <td class="small font-weight-bold">{{ \Carbon\Carbon::parse($req->start_date)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($req->end_date)->format('Y-m-d') }}</td>
                                                                    <td><span class="font-weight-bold">{{ $req->total_days }}</span></td>
                                                                    <td class="small text-muted">{{ $req->reason }}</td>
                                                                    <td>
                                                                        @php
                                                                            $s = strtoupper($req->status);
                                                                            $bc = 'warning';
                                                                            if($s == 'APPROVED' || $s == 'ACCEPTED') $bc = 'success';
                                                                            elseif($s == 'REJECTED') $bc = 'danger';
                                                                        @endphp
                                                                        <span class="badge badge-{{ $bc }} small">{{ $req->status }}</span>
                                                                    </td>
                                                                    <td class="small text-muted italic">-</td>
                                                                </tr>
                                                            @empty
                                                                <tr><td colspan="6" class="text-center py-3">No leave history found.</td></tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No active employees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 12px; }
    .table thead th { border-top: 0; letter-spacing: 0.5px; }
    .badge-pill { font-weight: 600; font-size: 0.75rem; }
    .progress { border-radius: 10px; background-color: #f1f3f9; }
    .avatar-circle { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
</style>
@endpush
@endsection
