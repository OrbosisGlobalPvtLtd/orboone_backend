@extends('layouts.panel', ['active' => 'technical_lead_work_reports'])

@section('page_title', 'Technical Lead Daily Work Reports')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: rgba(75, 0, 232, 0.08);
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.tl-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.tl-container {
    max-width: 1550px;
    margin: 0 auto;
}

.tl-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.tl-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.field-kv-box {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 14px 18px;
    height: 100%;
}

.field-kv-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748B;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}

.field-kv-val {
    font-size: 14px;
    font-weight: 700;
    color: #0F172A;
    word-break: break-word;
}

.status-pill-completed { background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; }
.status-pill-testing { background: #E0E7FF; color: #3730A3; border: 1px solid #A5B4FC; }
.status-pill-in_progress { background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }
.status-pill-blocked { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
</style>
@endsection

@section('_content')
<div class="tl-page">
    <div class="tl-container">
        <!-- Hero Header -->
        <div class="tl-hero">
            <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-file-signature mr-2"></i>Technical Lead Daily Work Reports</h1>
            <p class="mb-0 opacity-90">Review daily work logs submitted by developers under Technical Lead supervision.</p>
        </div>

        <!-- Filter Card -->
        <div class="tl-card p-4">
            <form method="GET" action="{{ route('technical_lead.work_reports') }}" class="form-row align-items-center">
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">Project Filter</label>
                    <select name="project_id" class="form-control">
                        <option value="">-- All Projects --</option>
                        @foreach($projects as $prj)
                            <option value="{{ $prj->id }}" {{ request('project_id') == $prj->id ? 'selected' : '' }}>{{ $prj->name }} ({{ $prj->project_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">From Date</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3 my-1">
                    <label class="small font-weight-bold text-muted">To Date</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 my-1 text-right align-self-end">
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('technical_lead.work_reports') }}" class="btn btn-light border px-3 ml-1" style="border-radius: 10px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Work Reports Table -->
        <div class="tl-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4" style="width: 140px;">Date</th>
                            <th class="py-3">Developer</th>
                            <th class="py-3">Task / Work Title</th>
                            <th class="py-3 text-center" style="width: 120px;">Duration</th>
                            <th class="py-3 px-4 text-right" style="width: 160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workLogs as $log)
                        @php
                            $jsonPayload = is_array($log->work_summary_json) 
                                ? $log->work_summary_json 
                                : json_decode($log->work_summary_json ?? '[]', true);
                            
                            $title = $jsonPayload['title'] ?? null;
                            $status = strtolower($jsonPayload['status'] ?? '');
                            $statusClass = isset($status) && in_array($status, ['completed', 'testing', 'in_progress', 'blocked']) 
                                ? "status-pill-{$status}" 
                                : "badge-secondary";
                            
                            if (empty($title)) {
                                $title = Str::limit($log->work_summary ?? 'Work Log Entry', 45);
                            }
                        @endphp
                        <tr>
                            <td class="py-3 px-4 align-middle">
                                <span class="badge badge-light border font-weight-bold px-3 py-1.5 text-dark" style="border-radius: 8px;">
                                    <i class="far fa-calendar-alt text-primary mr-1"></i> {{ $log->work_date ? $log->work_date->format('d M Y') : 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ optional($log->employee)->display_name }}</strong>
                                <small class="text-muted">{{ optional($log->employee)->employee_code }} &bull; {{ optional(optional($log->employee)->designation)->name ?? 'Developer' }}</small>
                            </td>
                            <td class="py-3 align-middle">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <span class="font-weight-bold text-dark"><i class="fas fa-tasks text-primary mr-1.5"></i>{{ $title }}</span>
                                    @if(!empty($status))
                                        <span class="badge {{ $statusClass }} font-weight-bold text-uppercase px-2.5 py-1" style="border-radius: 6px; font-size: 11px;">{{ str_replace('_', ' ', $status) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 align-middle text-center">
                                <span class="badge font-weight-bold px-3 py-1.5 text-white" style="background: var(--orb-primary); border-radius: 8px;">
                                    <i class="far fa-clock mr-1"></i> {{ $log->duration_minutes ? round($log->duration_minutes / 60, 1) . ' hrs' : 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 align-middle text-right">
                                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold px-3 py-1.5 view-report-btn" 
                                    style="border-radius: 8px;"
                                    data-employee="{{ optional($log->employee)->display_name }}"
                                    data-code="{{ optional($log->employee)->employee_code }}"
                                    data-designation="{{ optional(optional($log->employee)->designation)->name ?? 'Developer' }}"
                                    data-date="{{ $log->work_date ? $log->work_date->format('d M Y') : 'N/A' }}"
                                    data-duration="{{ $log->duration_minutes ? round($log->duration_minutes / 60, 1) . ' hrs' : 'N/A' }}"
                                    data-summary="{{ $log->work_summary }}"
                                    data-json='@json($jsonPayload)'
                                    data-toggle="modal" data-target="#viewReportModal">
                                    <i class="fas fa-eye mr-1"></i> View Report
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">No daily work reports submitted by supervised developers.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $workLogs->links() }}
        </div>
    </div>
</div>

<!-- Detailed Work Report Preview Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0" id="modalReportTitle"><i class="fas fa-file-alt mr-2"></i>Daily Work Report Details</h5>
                    <small id="modalReportSubtitle" class="text-white-50 opacity-90"></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <!-- Meta Info Pill Bar -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-4 rounded bg-light border" style="border-radius: 14px !important;">
                    <div>
                        <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 10px;">Developer</small>
                        <strong class="text-dark" id="modalEmpName"></strong> <span class="text-muted small" id="modalEmpMeta"></span>
                    </div>
                    <div>
                        <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 10px;">Report Date</small>
                        <strong class="text-dark" id="modalReportDate"></strong>
                    </div>
                    <div>
                        <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 10px;">Duration</small>
                        <span class="badge badge-primary px-3 py-1 font-weight-bold" id="modalReportDuration" style="background: var(--orb-primary); border-radius: 8px;"></span>
                    </div>
                </div>

                <!-- Structured Dynamic Fields Container -->
                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-list-check mr-2 text-primary"></i>Submitted Report Fields</h6>
                <div id="modalStructuredFieldsContainer" class="row">
                    <!-- Populated dynamically via JavaScript -->
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-secondary px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 10px;">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
$(document).ready(function() {
    $('.view-report-btn').on('click', function() {
        var emp = $(this).data('employee');
        var code = $(this).data('code');
        var designation = $(this).data('designation');
        var date = $(this).data('date');
        var duration = $(this).data('duration');
        var summary = $(this).data('summary');
        var payload = $(this).data('json');

        $('#modalReportSubtitle').text(emp + ' — ' + date);
        $('#modalEmpName').text(emp);
        $('#modalEmpMeta').text('(' + code + ' • ' + designation + ')');
        $('#modalReportDate').text(date);
        $('#modalReportDuration').text(duration);

        var container = $('#modalStructuredFieldsContainer');
        container.empty();

        if (payload && typeof payload === 'object' && Object.keys(payload).length > 0) {
            $.each(payload, function(key, val) {
                if (val === null || val === '') return;

                var formattedKey = key.replace(/_/g, ' ').toUpperCase();
                var formattedVal = val;

                if (Array.isArray(val)) {
                    formattedVal = '<ul class="pl-3 mb-0 text-dark small">' + val.map(function(item) {
                        return '<li>' + (typeof item === 'object' ? JSON.stringify(item) : item) + '</li>';
                    }).join('') + '</ul>';
                } else if (typeof val === 'boolean') {
                    formattedVal = val 
                        ? '<span class="badge badge-success px-2.5 py-1"><i class="fas fa-check mr-1"></i> Yes</span>' 
                        : '<span class="badge badge-secondary px-2.5 py-1"><i class="fas fa-times mr-1"></i> No</span>';
                } else if (typeof val === 'object' && val !== null) {
                    formattedVal = '<pre class="bg-light p-2 rounded small mb-0">' + JSON.stringify(val, null, 2) + '</pre>';
                } else if (key === 'status') {
                    formattedVal = '<span class="badge badge-info font-weight-bold text-uppercase px-3 py-1" style="border-radius: 6px;">' + val + '</span>';
                }

                var colHtml = `
                    <div class="col-md-6 col-lg-6 mb-3">
                        <div class="field-kv-box">
                            <div class="field-kv-label">${formattedKey}</div>
                            <div class="field-kv-val">${formattedVal}</div>
                        </div>
                    </div>
                `;
                container.append(colHtml);
            });
        } else if (summary) {
            var summaryHtml = `
                <div class="col-12 mb-3">
                    <div class="field-kv-box">
                        <div class="field-kv-label">WORK SUMMARY</div>
                        <div class="field-kv-val">${summary}</div>
                    </div>
                </div>
            `;
            container.append(summaryHtml);
        } else {
            container.append('<div class="col-12 text-muted small py-3">No report details available.</div>');
        }
    });
});
</script>
@endsection
