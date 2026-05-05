@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'leave-allocations'])

@section('_content')

@push('styles')
<style>
:root { --orb-blue: #1560ab; --orb-teal: #0d8c8c; }

.page-hero { background: linear-gradient(135deg, #1560ab 0%, #0d6efd 50%, #198484 100%); border-radius: 16px; padding: 28px 32px; color: #fff; margin-bottom: 28px; }
.page-hero h4 { font-weight: 800; font-size: 1.5rem; margin-bottom: 4px; }
.page-hero p  { opacity: .82; margin: 0; font-size: .95rem; }

.stat-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.07); transition: transform .2s; overflow: hidden; }
.stat-card:hover { transform: translateY(-3px); }
.stat-card .card-body { padding: 22px 24px; }
.stat-label { font-size: .72rem; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; }
.stat-value { font-size: 2rem; font-weight: 900; line-height: 1; margin: 5px 0 0; }

.panel-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.07); margin-bottom: 24px; }
.panel-card .card-header { background: #fff; padding: 18px 24px; border-bottom: 1px solid #f0f2f7; border-radius: 16px 16px 0 0; }
.panel-card .card-header h6 { font-weight: 800; margin: 0; color: #1a1f36; }

.tbl thead th { background: #f8f9fc; text-transform: uppercase; font-size: .72rem; letter-spacing: .9px; font-weight: 700; color: #6b7280; padding: 14px 16px; border: none; }
.tbl tbody td {
    padding: 9px 0px; vertical-align: middle; border-bottom: 1px solid #f0f2f7; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover { background: #f8faff; }

.half-badge { display: inline-flex; align-items: center; gap: 4px; background: #f0f4ff; color: #1560ab; border-radius: 6px; padding: 2px 8px; font-size: .78rem; font-weight: 700; }
.half-badge + .half-badge { background: #f0fff8; color: #059669; }

.balance-pill { font-weight: 800; font-size: .82rem; padding: 4px 12px; border-radius: 20px; }
.balance-ok   { background: #e7fef2; color: #059669; }
.balance-warn { background: #fff7e0; color: #d97706; }
.balance-zero { background: #ffe9e9; color: #dc2626; }
.lwp-pill     { background: #ffe9e9; color: #dc2626; font-size: .78rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }

.emp-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; }
.emp-initials { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #1560ab, #0d8c8c); color: #fff; font-weight: 800; font-size: .9rem; display: flex; align-items: center; justify-content: center; }

.alloc-form-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.07); }
.alloc-form-card .card-header { background: linear-gradient(135deg, #f8f9fc, #fff); border-bottom: 1px solid #f0f2f7; }

.emp-type-badge { font-size: .68rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; }
.badge-intern   { background: #fef3c7; color: #d97706; }
.badge-probation { background: #ede9fe; color: #7c3aed; }
.badge-permanent { background: #d1fae5; color: #059669; }
.badge-ft       { background: #dbeafe; color: #1d4ed8; }

@media (max-width:768px) {
  .page-hero { padding: 20px 16px; }
  .stat-value { font-size: 1.5rem; }
}
</style>
@endpush

<div class="container-fluid py-4 px-4">

  {{-- ── Hero ───────────────────────────────── --}}
  <div class="page-hero d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h4><i class="fas fa-layer-group mr-2"></i>Leave Allocation Centre</h4>
      <p>Manage and calculate mathematically-accurate leave allocations for all employees</p>
    </div>
    <form action="{{ route('leave-allocations.process') }}" method="POST" class="mt-3 mt-md-0"
          onsubmit="return confirm('Run allocations for all active employees ({{ $year }})? This will recalculate every record.');">
      @csrf
      <input type="hidden" name="year" value="{{ $year }}">
      <button type="submit" class="btn btn-light font-weight-bold shadow-sm px-4 py-2" style="border-radius: 50px;">
        <i class="fas fa-sync-alt mr-2"></i>Run All Allocations ({{ $year }})
      </button>
    </form>
  </div>

  {{-- ── Alerts ──────────────────────────────── --}}
  @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-lg d-flex align-items-center mb-4" style="border-radius:12px!important">
      <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-lg mb-4" style="border-radius:12px!important">
      <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
    </div>
  @endif

  {{-- ── Stats Row ──────────────────────────── --}}
  <div class="row mb-4">
    @php
      $totalEmp  = $allocations->count();
      $totalPl   = $allocations->sum('total_pl');
      $totalSl   = $allocations->sum('total_sl');
      $totalLwp  = $allocations->sum('lwp_days');
    @endphp

    <div class="col-6 col-md-3 mb-3">
      <div class="stat-card card" style="border-left: 5px solid #1560ab!important">
        <div class="card-body">
          <div class="stat-label text-primary">Employees Allocated</div>
          <div class="stat-value text-dark">{{ $totalEmp }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="stat-card card" style="border-left: 5px solid #059669!important">
        <div class="card-body">
          <div class="stat-label text-success">Total PL Allowed</div>
          <div class="stat-value text-dark">{{ round($totalPl, 1) }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="stat-card card" style="border-left: 5px solid #0d8c8c!important">
        <div class="card-body">
          <div class="stat-label" style="color:#0d8c8c">Total SL Allowed</div>
          <div class="stat-value text-dark">{{ round($totalSl, 1) }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="stat-card card" style="border-left: 5px solid #dc2626!important">
        <div class="card-body">
          <div class="stat-label text-danger">Reported LWP Days</div>
          <div class="stat-value text-dark">{{ round($totalLwp, 1) }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    {{-- ── Allocation Table ───────────────────── --}}
    <div class="col-lg-8">
      <div class="panel-card card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h6><i class="fas fa-table mr-2 text-primary"></i>{{ $year }} Allocation Records</h6>
          <span class="badge badge-primary badge-pill">{{ $allocations->count() }} records</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table tbl mb-0">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th class="text-center">Allocation (PL / SL)</th>
                  <th class="text-center">Used (PL / SL)</th>
                  <th class="text-center">Balance</th>
                  <th class="text-center">LWP</th>
                </tr>
              </thead>
              <tbody>
                @forelse($allocations as $alloc)
                @php
                  $bPl  = max(0, $alloc->total_pl - $alloc->used_pl);
                  $bSl  = max(0, $alloc->total_sl - $alloc->used_sl);
                  $balClass = ($bPl + $bSl) == 0 ? 'balance-zero' : (($bPl + $bSl) < 5 ? 'balance-warn' : 'balance-ok');
                  $emp  = $alloc->employee;
                  $name = $emp->name ?? 'Unknown';
                  $photo= $emp->employeeDetail->photo ?? null;
                  $photoUrl = $photo ? (str_starts_with($photo,'http') ? $photo : asset('storage/'.$photo)) : null;
                  $emType = $emp->employment_type ?? '';
                  $pStatus= strtolower($emp->probation_status ?? '');
                  $typeLabel = $emType === 'Intern' ? 'Intern' : ($pStatus === 'permanent' ? 'Permanent' : ($emType === 'Full-Time' ? 'Probation' : $emType));
                  $typeCls   = $emType === 'Intern' ? 'badge-intern' : ($pStatus === 'permanent' ? 'badge-permanent' : 'badge-probation');
                @endphp
                <tr>
                  <td>
                    <div class="d-flex align-items-center pl-3">
                      <div class="emp-initials mr-2">{{ strtoupper(substr($name,0,1)) }}</div>
                      <div>
                        <div class="font-weight-bold text-dark small">{{ $name }}</div>
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $emp->employee_id ?? '' }} · <span class="emp-type-badge {{ $typeCls }}">{{ $typeLabel }}</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="text-center">
                    <span class="half-badge">PL: {{ round($alloc->total_pl, 1) }}</span>
                    <span class="half-badge ml-1">SL: {{ round($alloc->total_sl, 1) }}</span>
                  </td>
                  <td class="text-center">
                    <span class="text-warning font-weight-bold small">PL: {{ round($alloc->used_pl, 1) }}</span><br>
                    <span class="text-info font-weight-bold small">SL: {{ round($alloc->used_sl, 1) }}</span>
                  </td>
                  <td class="text-center">
                    <span class="balance-pill {{ $balClass }}">PL {{ round($bPl, 1) }} / SL {{ round($bSl, 1) }}</span>
                  </td>
                  <td class="text-center">
                    @if($alloc->lwp_days > 0)
                      <span class="lwp-pill">{{ round($alloc->lwp_days, 1) }} day(s)</span>
                    @else
                      <span class="text-muted small">—</span>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fas fa-layer-group fa-3x text-light mb-3 d-block"></i>
                    No allocations found for {{ $year }}.<br>
                    <small>Click <strong>"Run All Allocations"</strong> to generate records for all employees.</small>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Single Employee Allocator ─────────── --}}
    <div class="col-lg-4">
      <div class="panel-card card alloc-form-card">
        <div class="card-header">
          <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-user-plus mr-2 text-primary"></i>Allocate for Single Employee</h6>
        </div>
        <div class="card-body p-4">
          <form action="{{ route('leave-allocations.single') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
              <label class="font-weight-bold small text-uppercase text-muted">Employee <span class="text-danger">*</span></label>
              <select name="employee_id" class="form-control" style="border-radius:10px;" required>
                <option value="">Select Employee…</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp->id }}">
                    {{ $emp->name }} ({{ $emp->employee_id ?? 'N/A' }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="form-group mb-4">
              <label class="font-weight-bold small text-uppercase text-muted">Year <span class="text-danger">*</span></label>
              <input type="number" name="year" class="form-control" style="border-radius:10px;" value="{{ $year }}" min="2020" max="2099" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block font-weight-bold" style="border-radius:10px;">
              <i class="fas fa-calculator mr-2"></i>Calculate & Allocate
            </button>
          </form>

          <hr class="my-4">

          {{-- Legend --}}
          <div>
            <p class="font-weight-bold text-dark small mb-2"><i class="fas fa-info-circle mr-1 text-primary"></i>Allocation Logic</p>
            <div class="d-flex align-items-start mb-2">
              <span class="emp-type-badge badge-intern mr-2 mt-1">Intern</span>
              <small class="text-muted">Fixed: <strong>1 PL</strong> (Total)</small>
            </div>
            <div class="d-flex align-items-start mb-2">
              <span class="emp-type-badge badge-probation mr-2 mt-1">Probation</span>
              <small class="text-muted">Fixed: <strong>1 PL</strong> (Total)</small>
            </div>
            <div class="d-flex align-items-start">
              <span class="emp-type-badge badge-permanent mr-2 mt-1">Permanent</span>
              <small class="text-muted">Pro-rata: <strong>18 PL + 7 SL</strong>/yr, calculated on remaining full months of joining year.</small>
            </div>
          </div>
        </div>
      </div>

      {{-- Carry Forward Info --}}
      <div class="panel-card card">
        <div class="card-body p-3">
          <p class="font-weight-bold small text-dark mb-2"><i class="fas fa-arrow-right text-success mr-1"></i>System Behavior Info</p>
          <small class="text-muted">Balance persists throughout the same year. All balances reset to 0 on Jan 1st (no carry-forward to next year). Any leave exceeding the balance is automatically marked as LWP.</small>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
