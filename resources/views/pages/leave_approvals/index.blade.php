@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'leave-approvals'])

@section('_content')

@push('styles')
<style>
:root { --orb: #1560ab; }

.appr-hero { background: linear-gradient(135deg,#0b1437 0%,#0e1f5e 100%); border-radius: 20px; padding: 35px 40px; color:#fff; margin-bottom: 30px; position: relative; overflow: hidden; }
.appr-hero h4 { font-weight: 800; margin: 0 0 8px; letter-spacing: -0.5px; }
.appr-hero::after { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%; }

.panel-card { border: none; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,.04); background: #fff; overflow: hidden; }
.panel-card .card-header { background: #fff; padding: 22px 30px; border-bottom: 1px solid #f1f5f9; }

.tbl thead th { background: #f8fafc; text-transform: uppercase; font-size: .7rem; letter-spacing: 1.2px; font-weight: 800; color: #64748b; padding: 18px 24px; border: none; }
.tbl tbody td { padding: 20px 24px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fcfdfe; }

/* Status pills */
.s-pill { font-size: .65rem; font-weight: 800; padding: 6px 14px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.8px; display: inline-flex; align-items: center; gap: 6px; }
.s-pending  { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
.s-approved { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
.s-rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

/* Leave type */
.lt-pl { background: #eff6ff; color: #1d4ed8; font-size: .7rem; font-weight: 800; padding: 4px 12px; border-radius: 8px; border: 1px solid #dbeafe; }
.lt-sl { background: #f0fdf4; color: #15803d; font-size: .7rem; font-weight: 800; padding: 4px 12px; border-radius: 8px; border: 1px solid #dcfce7; }
.lt-lwp { background: #fef2f2; color: #b91c1c; font-size: .7rem; font-weight: 800; padding: 4px 12px; border-radius: 8px; border: 1px solid #fee2e2; }

/* Employee chip */
.emp-chip { display: flex; align-items: center; gap: 12px; }
.emp-init { width: 42px; height: 42px; border-radius: 12px; background: #f1f5f9; color: #1e293b; font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* Action btns */
.btn-action { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: none; transition: all 0.2s; cursor: pointer; }
.btn-appr { background: #f0fdf4; color: #16a34a; }
.btn-appr:hover { background: #16a34a; color: #fff; transform: translateY(-2px); }
.btn-rejt { background: #fef2f2; color: #dc2626; }
.btn-rejt:hover { background: #dc2626; color: #fff; transform: translateY(-2px); }

/* Filter tabs */
.filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
.ftab { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; font-size: .85rem; font-weight: 700; color: #64748b; padding: 10px 20px; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.ftab.active { background: #0f172a; color: #fff; border-color: #0f172a; }
.ftab:hover:not(.active) { background: #f8fafc; color: #0f172a; }

@media(max-width:768px){ .appr-hero { padding: 25px; } .ftab { padding: 8px 15px; font-size: .8rem; } }
</style>
@endpush

<div class="container-fluid py-4 px-4">

  {{-- Hero Section --}}
  <div class="appr-hero d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h4><i class="fas fa-user-check mr-3"></i>Leave Approvals</h4>
      <p class="mb-0 opacity-75">Review and manage professional leave requests for your team.</p>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="{{ route('leave-allocations.index') }}" class="btn btn-white px-4 font-weight-bold" style="border-radius:12px; background: #fff; color: #0f172a;">
        <i class="fas fa-cog mr-2"></i>Allocation Settings
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius:15px">
      <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
  @endif

  {{-- Filters --}}
  <div class="filter-tabs">
    <button class="ftab active" data-filter="all">All Submissions</button>
    <button class="ftab" data-filter="pending">⏳ Pending Review</button>
    <button class="ftab" data-filter="approved">✅ Approved</button>
    <button class="ftab" data-filter="rejected">❌ Rejected</button>
  </div>

  {{-- Main Table --}}
  <div class="panel-card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-ul mr-2 text-primary"></i>Recent Requests</h6>
      <span class="text-muted small font-weight-bold">{{ $requests->total() }} records found</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table tbl mb-0">
          <thead>
            <tr>
              <th>Employee Details</th>
              <th>Category</th>
              <th>Period & Days</th>
              <th style="width:25%">Reason</th>
              <th class="text-center">Status</th>
              <th class="text-right pr-4">Actions</th>
            </tr>
          </thead>
          <tbody>

            @forelse($requests as $req)
            @php
              $name = $req->employee->name ?? 'Unknown';
              $empId = $req->employee->employee_id ?? 'N/A';
              $sc = strtolower($req->status);
              $pillCls = 's-'.$sc;
              $ico = $sc === 'approved' ? 'check' : ($sc === 'rejected' ? 'times' : 'clock');
              $ltCls = 'lt-'.strtolower($req->leave_type);
            @endphp

            <tr class="req-row" data-status="{{ $sc }}">
              <td>
                <div class="emp-chip">
                  <div class="emp-init">{{ strtoupper(substr($name,0,1)) }}</div>
                  <div>
                    <div class="font-weight-bold text-dark mb-0">{{ $name }}</div>
                    <div class="text-muted x-small font-weight-bold">{{ $empId }}</div>
                  </div>
                </div>
              </td>
              <td><span class="{{ $ltCls }}">{{ $req->leave_type }}</span></td>
              <td>

                <div class="small font-weight-bold text-dark">
                  {{ \Carbon\Carbon::parse($req->start_date)->format('d M') }}
                  @if($req->start_date !== $req->end_date)
                    - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}
                  @endif
                </div>

                <div class="text-muted x-small font-weight-bold mt-1">{{ $req->total_days }} day(s) total
                </div>
              </td>
              <td>
                <div class="small text-muted text-truncate" style="max-width:250px" title="{{ $req->reason }}">
                  {{ $req->reason }}
                </div>
              </td>
              <td class="text-center">
                <span class="s-pill {{ $pillCls }}">
                  <i class="fas fa-{{ $ico }} mr-1"></i>{{ strtoupper($req->status) }}
                </span>
              </td>
              <td class="text-right pr-4">
                @if($sc === 'pending')
                  <div class="d-flex justify-content-end gap-2">
                    {{-- Approve Trigger --}}
                    <button class="btn-action btn-appr" data-toggle="modal" data-target="#approveModal{{ $req->id }}">
                      <i class="fas fa-check"></i>
                    </button>
                    
                    {{-- Reject Trigger --}}
                    <button class="btn-action btn-rejt" data-toggle="modal" data-target="#rejectModal{{ $req->id }}">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>

                  {{-- Approval Modal --}}
                  <div class="modal fade" id="approveModal{{ $req->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
                        <form action="{{ route('leave-approvals.approve', $req->id) }}" method="POST">
                          @csrf
                          <div class="modal-header border-0 pt-4 px-4">
                            <h5 class="modal-title font-weight-bold text-success">Approve Leave Request</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                          </div>
                          <div class="modal-body p-4 text-left">
                            <div class="alert alert-success border-0 small mb-4" style="border-radius:12px">
                              <i class="fas fa-info-circle mr-2"></i> Approving this request will automatically update the employee's leave balance.
                            </div>
                            <label class="small font-weight-bold text-muted uppercase">Admin Remarks (Optional)</label>
                            <textarea name="remark" class="form-control" rows="3" style="border-radius:12px; background:#f8fafc" placeholder="E.g., Approved for family emergency..."></textarea>
                          </div>
                          <div class="modal-footer border-0 p-4">
                            <button type="button" class="btn btn-light px-4" data-dismiss="modal" style="border-radius:10px">Cancel</button>
                            <button type="submit" class="btn btn-success px-4 font-weight-bold" style="border-radius:10px">Approve Now</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                  {{-- Rejection Modal --}}
                  <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
                        <form action="{{ route('leave-approvals.reject', $req->id) }}" method="POST">
                          @csrf
                          <div class="modal-header border-0 pt-4 px-4">
                            <h5 class="modal-title font-weight-bold text-danger">Reject Leave Request</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                          </div>
                          <div class="modal-body p-4 text-left">
                            <label class="small font-weight-bold text-muted uppercase">Reason for Rejection</label>
                            <textarea name="remark" class="form-control" rows="4" style="border-radius:12px; background:#f8fafc" placeholder="Please provide a brief reason for rejection..." required></textarea>
                          </div>
                          <div class="modal-footer border-0 p-4">
                            <button type="button" class="btn btn-light px-4" data-dismiss="modal" style="border-radius:10px">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4 font-weight-bold" style="border-radius:10px">Reject Now</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                @else
                  <span class="text-muted x-small font-weight-bold italic">PROCESSED</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">
                <i class="fas fa-coffee fa-3x mb-3 opacity-25"></i>
                <p class="font-weight-bold">No pending leave requests at the moment.</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($requests->hasPages())
    <div class="card-footer bg-white border-0 py-4 px-4">
      {{ $requests->links() }}
    </div>
    @endif
  </div>
</div>

@push('scripts')
<script>
  $('.ftab').on('click', function() {
    $('.ftab').removeClass('active');
    $(this).addClass('active');
    const f = $(this).data('filter');
    $('.req-row').each(function() {
      $(this).toggle(f === 'all' || $(this).data('status') === f);
    });
  });
</script>
@endpush

@endsection