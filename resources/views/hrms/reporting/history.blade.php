@extends('layouts.panel', ['active' => 'reporting_history'])

@section('page_title', 'Reporting History')

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

.rep-page {
    padding: 14px 16px 36px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
    font-family: 'Outfit', sans-serif;
}

.rep-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

/* Signature Hero Header Banner */
.rep-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px;
    padding: 22px 28px;
    margin-bottom: 20px;
    box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    flex-wrap: wrap;
}

.rep-hero:before {
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

.rep-hero-kicker {
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

.rep-hero-title {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    line-height: 1.15;
    color: #ffffff;
}

.rep-hero-subtitle {
    font-size: 13px;
    font-weight: 500;
    margin-top: 5px;
    opacity: .92;
    max-width: 800px;
}

.rep-btn-glass {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.38) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border-radius: 999px !important;
    padding: 8px 18px !important;
    font-size: 12.5px !important;
    font-weight: 750 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    transition: all 0.2s ease !important;
}

.rep-btn-glass:hover {
    background: rgba(255, 255, 255, 0.32) !important;
    border-color: rgba(255, 255, 255, 0.65) !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

/* Card & Table */
.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.rep-card-head {
    padding: 16px 22px;
    background: linear-gradient(180deg, #FFFFFF, #FAFBFF);
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.rep-card-title {
    font-size: 16.5px;
    font-weight: 850;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.rep-card-sub {
    font-size: 12px;
    color: var(--orb-muted);
    font-weight: 550;
    margin-top: 2px;
}

/* Table Toolbar */
.orb-table-tools-bar {
    padding: 12px 20px;
    background: #F8FAFC;
    border-bottom: 1px solid #EAECF0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.orb-table-length-box {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--orb-muted);
}

.orbo-export-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 10px;
    padding: 3px;
}

.orbo-export-btn {
    border: none;
    background: transparent;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 750;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.orbo-export-btn:hover {
    background: #F1F5F9;
    color: #0F172A;
}

.orbo-export-btn .icon-csv { color: #0284C7; }
.orbo-export-btn .icon-excel { color: #16A34A; }
.orbo-export-btn .icon-pdf { color: #DC2626; }
.orbo-export-btn .icon-print { color: #475569; }

.rep-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.rep-table thead th {
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

.rep-table tbody td {
    padding: 13px 16px !important;
    vertical-align: middle !important;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    border-bottom: 1px solid #F1F5F9;
    background: #FFFFFF;
}

.rep-table tbody tr:hover td {
    background: #F8FAFC !important;
}

@media (max-width: 768px) {
    .rep-page {
        padding: 10px 10px 30px;
    }
    .rep-hero {
        padding: 18px 16px;
        border-radius: 18px;
    }
    .rep-hero-title {
        font-size: 20px;
    }
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">

        <!-- Hero Header -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-history"></i> Team Management &bull; Audit Trail
                </div>
                <h1 class="rep-hero-title">Reporting History Audit Log</h1>
                <div class="rep-hero-subtitle">Historical audit trail of employee reporting manager assignments, transitions, and relieves.</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('reporting.dashboard') }}" class="rep-btn-glass">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <a href="{{ route('reporting.assignments') }}" class="rep-btn-glass">
                    <i class="fas fa-user-plus"></i> Manage Assignments
                </a>
                <a href="{{ route('reporting.my_employees') }}" class="rep-btn-glass">
                    <i class="fas fa-users"></i> My Team
                </a>
            </div>
        </div>

        <!-- History Table Card -->
        <div class="rep-card">
            <div class="rep-card-head">
                <div>
                    <h5 class="rep-card-title"><i class="fas fa-history text-primary"></i> Assignment Transition Logs</h5>
                    <div class="rep-card-sub">Complete chronological record of all supervisor changes and historical reporting lines.</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table rep-table mb-0" id="reportingHistoryTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>Employee</th>
                            <th>HR Designation</th>
                            <th>Previous Reporting Manager</th>
                            <th class="text-center">Assigned Date</th>
                            <th class="text-center">Relieved Date</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                        <tr>
                            <td class="text-center text-muted font-weight-bold" style="font-size: 11.5px;">
                                {{ $loop->iteration + ($history->currentPage() - 1) * $history->perPage() }}
                            </td>
                            <td>
                                <strong class="text-dark font-weight-bold d-block" style="font-size: 13.5px;">{{ $item->employee_name }}</strong>
                                <small class="text-muted"><span class="badge badge-light border font-weight-bold" style="font-size: 10.5px;">{{ $item->employee_code }}</span></small>
                            </td>
                            <td>
                                <span class="badge badge-light border text-dark font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                    {{ $item->designation_name ?? 'Staff Member' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <strong class="text-dark" style="font-size: 13px;">{{ $item->supervisor_name }}</strong>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border font-weight-bold text-dark" style="font-size: 11.5px; padding: 4px 8px;">
                                    <i class="far fa-calendar-alt text-muted mr-1"></i> {{ \Carbon\Carbon::parse($item->start_date ?? $item->created_at)->format('d M Y') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->end_date)
                                    <span class="badge badge-light border text-danger font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;">
                                        <i class="fas fa-calendar-times text-danger mr-1"></i> {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted font-weight-bold">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary px-3 py-1 font-weight-bold text-uppercase" style="border-radius: 20px; font-size: 10.5px; letter-spacing: 0.03em;">
                                    Relieved / Transferred
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div style="width: 70px; height: 70px; border-radius: 20px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px;">
                                    <i class="fas fa-history"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark mb-1">No Historical Reporting Records Found</h5>
                                <p class="small text-muted mb-0">Historical transition logs will appear here when reporting assignments are transferred or relieved.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($history->hasPages())
                <div class="p-3 bg-white border-top">
                    {{ $history->links('vendor.pagination.orbo') }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
