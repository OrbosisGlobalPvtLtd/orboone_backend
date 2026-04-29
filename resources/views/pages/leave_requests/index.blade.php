@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'leave-requests'])

@section('_content')

@push('styles')
<style>
:root { --orb: #1560ab; }

.lms-hero { background: linear-gradient(135deg,#1560ab 0%,#0d6efd 60%,#198484 100%); border-radius: 16px; padding: 28px 32px; color:#fff; margin-bottom: 28px; }
.lms-hero h4 { font-weight: 800; font-size: 1.4rem; margin: 0 0 4px; }
.lms-hero p  { margin: 0; opacity: .85; }

/* Balance cards */
.bal-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.07); transition: transform .18s; overflow: hidden; }
.bal-card:hover { transform: translateY(-3px); }
.bal-card .card-body { padding: 22px 20px; }
.bal-label { font-size: .7rem; font-weight: 800; letter-spacing: 1.1px; text-transform: uppercase; margin-bottom: 6px; }
.bal-num   { font-size: 2rem; font-weight: 900; line-height: 1; }
.bal-sub   { font-size: .8rem; opacity: .75; margin-top: 4px; }

/* Progress bar */
.mini-prog { height: 6px; border-radius: 4px; background: rgba(255,255,255,.3); margin-top: 10px; }
.mini-prog-fill { height: 100%; border-radius: 4px; background: rgba(255,255,255,.9); transition: width .4s; }

/* History table */
.hist-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.07); }
.hist-card .card-header { background: #fff; padding: 18px 24px; border-bottom: 1px solid #f0f2f7; }
.hist-card .card-header h6 { font-weight: 800; margin: 0; }

.tbl thead th { background: #f8f9fc; text-transform: uppercase; font-size: .7rem; letter-spacing: .9px; font-weight: 700; color: #6b7280; padding: 12px 16px; border: none; }
.tbl tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f0f2f7; font-size: .9rem; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover { background: #f9fbff; }

.status-pill { font-size: .7rem; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
.pill-pending  { background: #fff8e1; color: #d97706; }
.pill-approved { background: #dcfce7; color: #059669; }
.pill-rejected { background: #fee2e2; color: #dc2626; }
.type-pill  { background: #dbeafe; color: #1d4ed8; font-size: .73rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; }
.type-pill-sl { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
.type-pill-lwp { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

.apply-btn { background: #fff; color: var(--orb); border: 2px solid var(--orb); border-radius: 50px; font-weight: 700; padding: 9px 26px; transition: all .2s; }
.apply-btn:hover { background: var(--orb); color: #fff; }

@media(max-width:768px) {
  .lms-hero { padding: 20px 16px; }
  .bal-num  { font-size: 1.6rem; }
}
</style>
@endpush

<div class="container-fluid py-4 px-4">

  {{-- ── Hero ────────────────────────────────── --}}
  <div class="lms-hero d-flex justify-content-between align-items-start flex-wrap">
    <div>
      <h4><i class="fas fa-paper-plane mr-2"></i>My Leave Dashboard</h4>
      <p>Track your leave balance, apply for time off, and view your full history</p>
    </div>
    <a href="{{ route('leave-requests.create') }}" class="apply-btn btn mt-3 mt-md-0">
      <i class="fas fa-plus mr-2"></i>Apply Leave
    </a>
  </div>

  {{-- ── Flash ───────────────────────────────── --}}
  @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius:12px">
      <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius:12px">
      <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
    </div>
  @endif

  {{-- ── Balance Cards ───────────────────────── --}}
  <div class="row mb-4" id="balance-row">
    @php
      $plUsedPct = $totalPl > 0 ? round(($allocation->used_pl ?? 0) / $totalPl * 100) : 0;
      $slUsedPct = $totalSl > 0 ? round(($allocation->used_sl ?? 0) / $totalSl * 100) : 0;
    @endphp

    {{-- PL Total --}}
    <div class="col-6 col-md-3 mb-3">
      <div class="bal-card card" style="background: linear-gradient(135deg,#1560ab,#0d6efd)">
        <div class="card-body text-white">
          <div class="bal-label"><i class="fas fa-calendar-check mr-1"></i>PL Allocated</div>
          <div class="bal-num" id="pl_total">{{ $totalPl }}</div>
          <div class="bal-sub">days this period</div>
          <div class="mini-prog"><div class="mini-prog-fill" style="width:100%"></div></div>
        </div>
      </div>
    </div>

    {{-- PL Used --}}
    <div class="col-6 col-md-3 mb-3">
      <div class="bal-card card" style="background: linear-gradient(135deg,#d97706,#f59e0b)">
        <div class="card-body text-white">
          <div class="bal-label"><i class="fas fa-calendar-minus mr-1"></i>PL Used</div>
          <div class="bal-num" id="pl_used">{{ $allocation->used_pl ?? 0 }}</div>
          <div class="bal-sub">days taken</div>
          <div class="mini-prog"><div class="mini-prog-fill" style="width:{{ $plUsedPct }}%"></div></div>
        </div>
      </div>
    </div>

    {{-- PL Balance --}}
    <div class="col-6 col-md-3 mb-3">
      <div class="bal-card card" style="background: linear-gradient(135deg,#059669,#10b981)">
        <div class="card-body text-white">
          <div class="bal-label"><i class="fas fa-calendar-plus mr-1"></i>PL Remaining</div>
          <div class="bal-num" id="pl_balance">{{ $balancePl }}</div>
          <div class="bal-sub">days available</div>
          <div class="mini-prog"><div class="mini-prog-fill" style="width:{{ $totalPl > 0 ? round($balancePl / $totalPl * 100) : 0 }}%"></div></div>
        </div>
      </div>
    </div>

    {{-- SL Balance --}}
    <div class="col-6 col-md-3 mb-3">
      <div class="bal-card card" style="background: linear-gradient(135deg,#0d8c8c,#0891b2)">
        <div class="card-body text-white">
          <div class="bal-label"><i class="fas fa-heartbeat mr-1"></i>SL Remaining</div>
          <div class="bal-num" id="sl_balance">{{ $balanceSl }}</div>
          <div class="bal-sub">of {{ $totalSl }} SL days</div>
          <div class="mini-prog"><div class="mini-prog-fill" style="width:{{ $totalSl > 0 ? round($balanceSl / $totalSl * 100) : 0 }}%"></div></div>
        </div>
      </div>
    </div>
  </div>

  {{-- LWP notice --}}
  @if($lwpCount > 0)
    <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center" style="background:#ffe9e9;border-radius:12px;color:#dc2626">
      <i class="fas fa-exclamation-triangle mr-2"></i>
      You have <strong class="mx-1">{{ $lwpCount }}</strong> Leave Without Pay day(s) recorded this year.
    </div>
  @endif

  {{-- ── History Table ───────────────────────── --}}
  <div class="hist-card card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h6><i class="fas fa-history mr-2 text-primary"></i>Leave Application History</h6>
      <span class="badge badge-light text-muted border">{{ $requests->count() }} records</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table tbl mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Type</th>
              <th>Period</th>
              <th class="text-center">Days</th>
              <th>Reason</th>
              <th class="text-center">Status</th>
              <th class="text-center">Applied On</th>
            </tr>
          </thead>
          <tbody>
            @forelse($requests as $i => $req)
            <tr>
              <td class="text-muted small">{{ $i + 1 }}</td>
              <td>
                @php
                  $lt = $req->leave_type;
                  $ltCls = $lt === 'SL' ? 'type-pill-sl' : ($lt === 'LWP' ? 'type-pill-lwp' : '');
                @endphp
                <span class="type-pill {{ $ltCls }}">
                  {{ $lt }}
                </span>
              </td>
              <td class="small font-weight-bold text-dark">
                {{ \Carbon\Carbon::parse($req->start_date)->format('d M Y') }}
                @if($req->start_date !== $req->end_date)
                  → {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}
                @endif
              </td>
              <td class="text-center font-weight-bold">{{ $req->total_days }}</td>
              <td class="text-muted small" style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $req->reason }}</td>
              <td class="text-center">
                @php
                  $sc = strtolower($req->status);
                  $pc = $sc === 'approved' ? 'pill-approved' : ($sc === 'rejected' ? 'pill-rejected' : 'pill-pending');
                  $ico= $sc === 'approved' ? 'check-circle' : ($sc === 'rejected' ? 'times-circle' : 'hourglass-half');
                @endphp
                <span class="status-pill {{ $pc }}">
                  <i class="fas fa-{{ $ico }} mr-1"></i>{{ ucfirst($req->status) }}
                </span>
              </td>
              <td class="text-center text-muted small">{{ $req->created_at->format('d M, Y') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x text-light d-block mb-3"></i>
                No leave requests yet. <a href="{{ route('leave-requests.create') }}">Apply for leave</a> to get started.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
// Live balance refresh every 60 seconds
function refreshBalance() {
  $.getJSON('{{ route("leave-allocations.balance") }}', function(data) {
    if (data.error) return;
    $('#pl_total').text(data.pl_total);
    $('#pl_used').text(data.pl_used);
    $('#pl_balance').text(data.pl_balance);
    $('#sl_balance').text(data.sl_balance);
  });
}
setInterval(refreshBalance, 60000);
</script>
@endpush

@endsection
