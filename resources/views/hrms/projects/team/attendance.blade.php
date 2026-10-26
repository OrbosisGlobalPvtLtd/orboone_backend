@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Team Attendance Matrix')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F6F7FB;
    --orb-card: #FFFFFF;
    --orb-border: #E7EAF3;
    --orb-text: #101828;
    --orb-muted: #667085;
    --orb-soft: #F4F2FF;
    --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
}

.prj-page {
    padding: 14px 16px 36px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
    font-family: 'Outfit', sans-serif;
}

.prj-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

/* Signature Hero Banner */
.prj-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px;
    padding: 22px 28px;
    margin-bottom: 20px;
    color: #ffffff;
    box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    overflow: hidden;
}

.prj-hero:before {
    content: "";
    position: absolute;
    right: -60px;
    top: -80px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.prj-hero-kicker {
    font-size: 11.5px;
    font-weight: 850;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .92;
    margin-bottom: 6px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.prj-hero h1 {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    color: #ffffff;
    line-height: 1.15;
}

.prj-hero p {
    margin: 4px 0 0;
    font-size: 13px;
    opacity: 0.92;
}

.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.prj-card-head {
    padding: 16px 22px;
    background: linear-gradient(180deg, #FFFFFF, #FAFBFF);
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.prj-card-title {
    font-size: 16.5px;
    font-weight: 850;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.prj-card-sub {
    font-size: 12px;
    color: var(--orb-muted);
    font-weight: 550;
    margin-top: 2px;
}

.prj-filter-body {
    padding: 18px 22px;
    background: #FFFFFF;
}

.prj-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    align-items: end;
}

.prj-filter-group label {
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    color: #667085;
    margin-bottom: 6px;
    display: block;
    letter-spacing: .04em;
}

.prj-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.prj-table thead th {
    background: #F8FAFC !important;
    color: #475467 !important;
    font-size: 11px !important;
    font-weight: 850 !important;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 12px 16px !important;
    border-bottom: 1px solid #E2E8F0 !important;
    border-top: none !important;
    white-space: nowrap;
}

.prj-table tbody td {
    padding: 13px 16px !important;
    vertical-align: middle !important;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    border-bottom: 1px solid #F1F5F9;
    background: #FFFFFF;
}

.prj-table tbody tr:hover td {
    background: #F8FAFC !important;
}

/* Select2 Skin */
.select2-container--default .select2-selection--single {
    height: 42px !important;
    border: 1px solid #E4E7EC !important;
    border-radius: 12px !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 10px !important;
    background-color: #fff !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 40px !important;
    font-size: 13px !important;
    font-weight: 650 !important;
    color: #101828 !important;
    padding-left: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 8px !important;
}

@media (max-width: 768px) {
    .prj-page { padding: 10px 10px 30px; }
    .prj-hero { padding: 18px 16px; border-radius: 18px; }
    .prj-hero h1 { font-size: 20px; }
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">

        <!-- Hero Header -->
        <div class="prj-hero">
            <div>
                <div class="prj-hero-kicker">
                    <i class="fas fa-user-check"></i> Project Management &bull; Team Matrix
                </div>
                <h1>Team Attendance Matrix</h1>
                <p>Project-scoped attendance records and real-time punch status for team members.</p>
            </div>
            <div>
                <a href="{{ route('reporting.attendance') }}" class="btn text-white font-weight-bold px-4 py-2" style="background: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.38); border-radius: 999px;">
                    <i class="fas fa-calendar-check mr-1"></i> Full Team Attendance
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="prj-card">
            <div class="prj-card-head">
                <div>
                    <h5 class="prj-card-title"><i class="fas fa-filter text-primary"></i> Filter Attendance</h5>
                    <div class="prj-card-sub">Filter team logs by attendance date or work mode.</div>
                </div>
            </div>
            <div class="prj-filter-body">
                <form method="GET" action="{{ route('projects.team.attendance') }}">
                    <div class="prj-filter-grid">
                        <div class="prj-filter-group">
                            <label><i class="fas fa-calendar-day text-primary mr-1"></i> Attendance Date</label>
                            <x-form.date-picker name="date" :value="$date" placeholder="Attendance Date" />
                        </div>
                        <div class="prj-filter-group">
                            <label><i class="fas fa-laptop-house text-primary mr-1"></i> Work Mode</label>
                            <select name="work_mode" class="form-control select2-searchable">
                                <option value="">-- All Work Modes --</option>
                                <option value="wfo" {{ request('work_mode') == 'wfo' ? 'selected' : '' }}>Work From Office (WFO)</option>
                                <option value="wfh" {{ request('work_mode') == 'wfh' ? 'selected' : '' }}>Work From Home (WFH)</option>
                            </select>
                        </div>
                        <div class="prj-filter-group d-flex align-items-end" style="gap: 8px;">
                            <button type="submit" class="btn font-weight-bold text-white shadow-sm" style="height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="fas fa-search"></i> View Attendance
                            </button>
                            <a href="{{ route('projects.team.attendance') }}" class="btn btn-light" style="height: 42px; width: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 750; border: 1px solid #E4E7EC; background: #fff; color: #344054; flex-shrink: 0;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="prj-card">
            <div class="table-responsive">
                <table class="table prj-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>HR Designation</th>
                            <th>Status</th>
                            <th>Work Mode</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                        <tr>
                            <td><span class="small font-weight-bold text-dark">{{ $att->attendance_date->format('d M Y') }}</span></td>
                            <td>
                                <strong class="text-dark d-block" style="font-size: 13.5px;">{{ optional($att->employee)->display_name }}</strong>
                                <small class="text-muted"><span class="badge badge-light border font-weight-bold" style="font-size: 10px;">{{ optional($att->employee)->employee_code }}</span></small>
                            </td>
                            <td><span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11px;">{{ optional(optional($att->employee)->designation)->name ?? 'Team Member' }}</span></td>
                            <td>
                                <span class="badge badge-{{ $att->punch_in_time ? 'success' : 'danger' }} text-uppercase px-2.5 py-1 font-weight-bold" style="border-radius: 20px; font-size: 10.5px;">
                                    {{ $att->status_name }}
                                </span>
                                @if($att->is_late)<span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 9.5px; border-radius: 6px;">LATE</span>@endif
                                @if($att->is_half_day)<span class="badge badge-secondary font-weight-bold ml-1" style="font-size: 9.5px; border-radius: 6px;">HALF DAY</span>@endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $att->work_mode == 'wfh' ? 'info' : 'primary' }} text-uppercase px-2.5 py-1 font-weight-bold" style="border-radius: 6px; font-size: 10.5px;">
                                    {{ strtoupper($att->work_mode ?? 'wfo') }}
                                </span>
                            </td>
                            <td><span class="small font-weight-bold text-success">{{ $att->punch_in_time ? $att->punch_in_time->format('h:i A') : '—' }}</span></td>
                            <td><span class="small font-weight-bold text-primary">{{ $att->punch_out_time ? $att->punch_out_time->format('h:i A') : '—' }}</span></td>
                            <td><span class="small font-weight-bold text-dark">{{ $att->duration }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div style="width: 70px; height: 70px; border-radius: 20px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px;">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark mb-1">No Attendance Records Found</h5>
                                <p class="small text-muted mb-0">No attendance logs found for the selected team scope and date.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 bg-white border-top">
                {{ $attendances->links('vendor.pagination.orbo') }}
            </div>
        </div>

    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-searchable').select2({
            width: '100%',
            dropdownAutoWidth: true
        });
    }
});
</script>
@endsection
