@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'my_payslips'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">My Payslips</h2>
            <p class="text-muted">Access and download all your generated salary slips.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-0 mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Quick Download Card -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-1 text-primary">Download Salary Slip by Month</h5>
                    <p class="text-muted small mb-0">Select a month to download your salary slip. If payroll hasn't been processed for that month, it will be generated on-demand.</p>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                    <form id="downloadForm" class="form-row align-items-end justify-content-md-end">
                        <div class="col-auto">
                            <label for="month" class="sr-only">Select Month</label>
                            <select id="month" name="month" class="form-control custom-select" required style="min-width: 200px;">
                                @for($i = 0; $i < 12; $i++)
                                    <option value="{{ date('Y-m', strtotime("-{$i} months")) }}">
                                        {{ date('F Y', strtotime("-{$i} months")) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="button" onclick="prepareDownload()" class="btn btn-primary px-4">
                                <i class="fas fa-download mr-2"></i> Download PDF
                            </button>
                            <a id="downloadLink" href="#" style="display: none;"></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Generated Payslips List -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">Previously Generated Payslips</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light text-uppercase small font-weight-bold text-muted">
                        <tr>
                            <th class="px-4">Month & Year</th>
                            <th>Payroll ID</th>
                            <th>Status</th>
                            <th class="text-right px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $p)
                            <tr>
                                <td class="px-4">
                                    <span class="font-weight-bold text-dark">{{ date('F Y', mktime(0, 0, 0, $p->month, 1, $p->year)) }}</span>
                                </td>
                                <td><span class="badge badge-light border">#{{ $p->payroll_id }}</span></td>
                                <td>
                                    <span class="badge badge-success-soft text-success px-2 py-1">
                                        <i class="fas fa-check mr-1"></i> Generated
                                    </span>
                                </td>
                                <td class="text-right px-4">
                                    @if($p->file_path)
                                        <a href="{{ route('pages.payroll.payslip.download', $p->id) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-file-pdf mr-1"></i> Download PDF
                                        </a>
                                    @else
                                        <span class="badge badge-warning">Not Generated</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-file-invoice fa-3x text-light"></i>
                                    </div>
                                    <p class="text-muted">No previously generated payslips found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 12px; }
    .badge-success-soft { background-color: rgba(40, 167, 69, 0.1); }
    .btn-primary { border-radius: 8px; font-weight: 600; }
    .custom-select { height: calc(1.5em + .75rem + 2px); border-radius: 8px; }
</style>
@endpush

@push('scripts')
<script>
    function prepareDownload() {
        const month = document.getElementById('month').value;
        const employeeId = {{ auth()->user()->employee->id ?? 'null' }};

        if (!employeeId || employeeId === 'null') {
            alert('Employee ID not found. Please contact administrator.');
            return;
        }

        if (!month) {
            alert('Please select a month.');
            return;
        }

        const url = `/payroll/payslip/${employeeId}/${month}/download`;
        const link = document.getElementById('downloadLink');
        link.href = url;
        link.click();
    }
</script>
@endpush
@endsection
