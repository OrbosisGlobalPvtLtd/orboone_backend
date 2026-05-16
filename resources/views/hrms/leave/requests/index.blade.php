@extends('layouts.panel')

@section('_head')
<style>
    .orb-page{background:#F6F7FB;color:#101828}.orb-header{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:18px}.orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:10px 14px;font-weight:700}.orb-btn:hover{background:#8600EE;color:#fff}.orb-soft{background:#F4F2FF;color:#4B00E8}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}.orb-pill{border-radius:999px;padding:5px 10px;font-size:12px;font-weight:700}.orb-pill.pending{background:#FFF7E6;color:#B54708}.orb-pill.approved{background:#ECFDF3;color:#027A48}.orb-pill.rejected,.orb-pill.cancelled{background:#FEF3F2;color:#B42318}
</style>
@endsection

@section('_content')
<div class="orb-page">
    <div class="orb-header">
        <div>
            <h1 class="orb-title">My Leave Requests</h1>
            <div class="orb-muted">Balance, requests, and approval status.</div>
        </div>
        <a href="{{ route('leave-requests.create') }}" class="orb-btn"><i class="fas fa-plus mr-1"></i> Apply Leave</a>
    </div>

    @include('hrms.leave.shared.flash')

    <div class="row mb-3">
        @foreach([
            'Total' => $allocation->total_remaining ?? 0,
            'Paid' => $allocation->paid_remaining ?? 0,
            'Sick' => $allocation->sick_remaining ?? 0,
            'Comp Off' => $allocation->comp_off_remaining ?? 0,
            'LWP Used' => $allocation->lwp_used ?? 0,
        ] as $label => $value)
            <div class="col-6 col-lg mb-3">
                <div class="orb-card"><div class="orb-card-body">
                    <div class="orb-muted small">{{ $label }}</div>
                    <div class="h4 mb-0">{{ number_format((float) $value, 2) }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="orb-card">
        <div class="orb-card-body table-responsive">
            <table class="table orb-table js-datatable">
                <thead><tr><th>#</th><th>Type</th><th>Dates</th><th>Days</th><th>Split</th><th>Status</th><th>Reason</th><th></th></tr></thead>
                <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ $request->id }}</td>
                        <td>{{ optional($request->leaveType)->name }}</td>
                        <td>{{ optional($request->start_date)->format('d M Y') }} - {{ optional($request->end_date)->format('d M Y') }}</td>
                        <td>{{ $request->deducted_days }}</td>
                        <td class="small">P {{ $request->paid_days }} / S {{ $request->sick_days }} / C {{ $request->comp_off_days }} / LWP {{ $request->lwp_days }}</td>
                        <td><span class="orb-pill {{ $request->status }}">{{ ucfirst($request->status) }}</span></td>
                        <td class="orb-muted">{{ \Illuminate\Support\Str::limit($request->reason, 60) }}</td>
                        <td>
                            @if(in_array($request->status, ['pending','approved'], true))
                                <form method="POST" action="{{ route('leave-requests.cancel', $request->id) }}" onsubmit="return confirm('Cancel this leave request?')">
                                    @csrf
                                    <button class="btn btn-sm btn-light border" type="submit">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center orb-muted py-4">No leave requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ method_exists($requests, 'links') ? $requests->links() : '' }}
        </div>
    </div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
