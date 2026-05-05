@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'leave-requests'])

@section('_content')

@push('styles')
<style>
.form-hero { background: linear-gradient(135deg,#1560ab 0%,#0d6efd 60%,#198484 100%); border-radius: 16px; padding: 28px 32px; color:#fff; margin-bottom: 28px; }
.form-hero h4 { font-weight: 800; margin: 0 0 4px; }
.form-hero p  { opacity: .85; margin: 0; }

.form-card { border: none; border-radius: 16px; box-shadow: 0 6px 30px rgba(0,0,0,.09); }
.form-card .card-body { padding: 36px 40px; }

.form-label-orb { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .8px; color: #4b5563; margin-bottom: 6px; }
.form-control-orb { border: 2px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; font-size: .93rem; transition: border-color .2s; }
.form-control-orb:focus { border-color: #1560ab; box-shadow: 0 0 0 3px rgba(21,96,171,.12); }

.submit-btn { background: linear-gradient(135deg,#1560ab,#0d6efd); color: #fff; border: none; border-radius: 50px; font-weight: 700; padding: 12px 36px; font-size: 1rem; transition: transform .18s, box-shadow .18s; }
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(21,96,171,.35); color:#fff; }

.note-box { background: #f0f7ff; border-left: 4px solid #1560ab; border-radius: 0 10px 10px 0; padding: 14px 18px; font-size: .85rem; color:#374151; }
.note-box strong { color: #1560ab; }

.day-preview { background: #f8f9fc; border-radius: 12px; padding: 16px 20px; border: 2px solid #e5e7eb; display: none; }
.day-preview.show { display: block; }
.day-count { font-size: 2rem; font-weight: 900; color: #1560ab; }

@media(max-width:768px){
  .form-card .card-body { padding: 24px 18px; }
  .form-hero { padding: 20px 16px; }
}
</style>
@endpush

<div class="container-fluid py-4 px-4">

  {{-- Hero --}}
  <div class="form-hero d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h4><i class="fas fa-paper-plane mr-2"></i>Apply for Leave</h4>
      <p>Submit a leave request for admin approval. Working days are counted automatically.</p>
    </div>
    <a href="{{ route('leave-requests.index') }}" class="btn btn-light font-weight-bold shadow-sm mt-3 mt-md-0" style="border-radius:50px;">
      <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
    </a>
  </div>

  {{-- Errors --}}
  @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius:12px">
      <strong><i class="fas fa-exclamation-circle mr-2"></i>Please fix the following:</strong>
      <ul class="mb-0 mt-2 pl-4">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="row justify-content-center">
    <div class="col-lg-8">

      <div class="form-card card">
        <div class="card-body">
          <form action="{{ route('leave-requests.store') }}" method="POST" id="leaveForm">
            @csrf

            {{-- Leave Type --}}
            <div class="form-group mb-4">
              <label class="form-label-orb">Leave Type <span class="text-danger">*</span></label>
              <select name="leave_type" class="form-control form-control-orb" required id="leaveType">
                <option value="">— Select Leave Category —</option>
                <option value="PL"  {{ old('leave_type') === 'PL'  ? 'selected' : '' }}>Paid Leave (PL)</option>
                <option value="SL"  {{ old('leave_type') === 'SL'  ? 'selected' : '' }}>Sick Leave (SL)</option>
              </select>
              <small class="text-muted">Casual Leave has been removed from this system. Only PL and SL are accepted.</small>
            </div>

            {{-- Date Range --}}
            <div class="row">
              <div class="col-md-6 mb-4">
                <label class="form-label-orb">From Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="startDate"
                       class="form-control form-control-orb"
                       value="{{ old('start_date') }}"
                       min="{{ date('Y-m-d') }}" required>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label-orb">To Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="endDate"
                       class="form-control form-control-orb"
                       value="{{ old('end_date') }}"
                       min="{{ date('Y-m-d') }}" required>
              </div>
            </div>

            {{-- Day Preview --}}
            <div class="day-preview mb-4" id="dayPreview">
              <div class="d-flex align-items-center">
                <div>
                  <div class="day-count" id="previewDays">—</div>
                  <div class="text-muted small font-weight-600">Estimated Working Days</div>
                  <div class="text-muted" style="font-size:.75rem">(Sundays and public holidays excluded — final count confirmed on submission)</div>
                </div>
                <i class="fas fa-calendar-check fa-3x text-primary ml-auto opacity-25"></i>
              </div>
            </div>

            {{-- Reason --}}
            <div class="form-group mb-4">
              <label class="form-label-orb">Reason / Justification <span class="text-danger">*</span></label>
              <textarea name="reason" class="form-control form-control-orb" rows="4"
                placeholder="Clearly state the reason for your absence…" required
                maxlength="1000">{{ old('reason') }}</textarea>
              <small class="text-muted float-right" id="reasonCount">0/1000</small>
            </div>

            {{-- Note --}}
            <div class="note-box mb-4">
              <strong><i class="fas fa-info-circle mr-1"></i>Important:</strong>
              Working days are calculated excluding Sundays and all registered public holidays.
              If your leave balance is exceeded, the extra days will be recorded as <strong>Leave Without Pay (LWP)</strong>.
            </div>

            {{-- Submit --}}
            <div class="text-right">
              <button type="submit" class="submit-btn btn">
                <i class="fas fa-paper-plane mr-2"></i>Submit Leave Request
              </button>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>

@push('scripts')
<script>
const startDate = document.getElementById('startDate');
const endDate   = document.getElementById('endDate');
const preview   = document.getElementById('dayPreview');
const days      = document.getElementById('previewDays');
const reason    = document.querySelector('textarea[name=reason]');
const reasonCount = document.getElementById('reasonCount');

function estimateDays() {
  const s = new Date(startDate.value);
  const e = new Date(endDate.value);
  if (!startDate.value || !endDate.value || e < s) { preview.classList.remove('show'); return; }

  // Simple client-side estimate (skips Sundays only; server will remove holidays)
  let count = 0, cur = new Date(s);
  while (cur <= e) {
    if (cur.getDay() !== 0) count++; // 0 = Sunday
    cur.setDate(cur.getDate() + 1);
  }
  days.textContent = count;
  preview.classList.add('show');

  // Keep end >= start
  endDate.min = startDate.value;
}

startDate.addEventListener('change', estimateDays);
endDate.addEventListener('change', estimateDays);

reason.addEventListener('input', function() {
  reasonCount.textContent = this.value.length + '/1000';
});
</script>
@endpush
@endsection
