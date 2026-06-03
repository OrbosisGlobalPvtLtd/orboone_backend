@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Compliance Management')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@include('hrms.documents.partials.styles')
<style>
    /* Compliance Command Center Premium Custom Styles */
    :root {

        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .06);

        --color-success: #12B76A;
        --color-success-light: #ECFDF3;
        --color-warning: #F79009;
        --color-warning-light: #FFFAEB;
        --color-danger: #F04438;
        --color-danger-light: #FEF3F2;
        --color-info: #0ea5e9;
        --color-info-light: #F0F9FF;
        --color-purple: var(--orb-secondary);
        --color-purple-light: #F4F2FF;
    }

    .compliance-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 20px !important;
        padding: 32px 40px !important;
        color: #fff !important;
        box-shadow: 0 12px 35px rgba(75, 0, 232, 0.12) !important;
        margin-bottom: 24px !important;
    }

    .compliance-hero-title h1 {
        font-size: 30px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .compliance-hero-title p {
        font-size: 14px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 8px 0 0 0 !important;
        font-weight: 500 !important;
    }

    /* Helper Utilities */
    .gap-1 {
        gap: 4px !important;
    }

    .gap-2 {
        gap: 8px !important;
    }

    .gap-3 {
        gap: 16px !important;
    }

    .gap-4 {
        gap: 24px !important;
    }

    .transition-all {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .border-right-lg {
        border-right: 1px solid var(--orb-border);
    }

    @media (max-width: 991px) {
        .border-right-lg {
            border-right: none !important;
            border-bottom: 1px solid var(--orb-border);
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
    }

    /* Colors and Backgrounds */
    .bg-success-light {
        background-color: var(--color-success-light) !important;
    }

    .bg-purple-light {
        background-color: var(--color-purple-light) !important;
    }

    .bg-warning-light {
        background-color: var(--color-warning-light) !important;
    }

    .bg-danger-light {
        background-color: var(--color-danger-light) !important;
    }

    .bg-info-light {
        background-color: var(--color-info-light) !important;
    }

    .text-purple {
        color: var(--color-purple) !important;
    }

    .bg-purple {
        background-color: var(--color-purple) !important;
    }

    /* Cards */
    .compliance-profile-card {
        background: #fff !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 16px !important;
        box-shadow: var(--orb-shadow) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }

    .compliance-profile-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 16px 36px rgba(16, 24, 40, 0.12) !important;
    }

    /* Custom Ledger Collapsible styling */
    #ledgerCollapse {
        transition: all 0.3s ease;
    }

    /* Font weight helpers */
    .font-weight-black {
        font-weight: 900 !important;
    }

    .font-weight-bold {
        font-weight: 700 !important;
    }

    .font-weight-semibold {
        font-weight: 600 !important;
    }

    .font-weight-medium {
        font-weight: 500 !important;
    }

    /* Action Pill buttons styling overrides */
    .dm-action-btn-pill {
        border-radius: 99px !important;
        padding: 4px 12px !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
    }

    /* DataTable toolbar styles */
    .dm-table-toolbar-row {
        background: #FAFBFC !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .dm-table-footer-row {
        background: #FAFBFC !important;
        border-top: 1px solid var(--orb-border) !important;
    }
</style>
@endsection

@section('_content')
<div class="container-fluid py-4" style="background-color: var(--orb-bg); min-height: 100vh;">

    <!-- ==========================================
         SECTION 1 — HERO SECTION
         ========================================== -->
    <div class="compliance-hero d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div class="compliance-hero-title">
            <div class="d-flex align-items-center gap-2 mb-2" style="color: rgba(255,255,255,0.85); font-size: 11px; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase;">
                <i class="fas fa-shield-alt"></i> COMPLIANCE COMMAND CENTER
            </div>
            <h1>Compliance Management</h1>
            <p>Monitor verified employee records, missing documents, compliance risk, and HR readiness.</p>
        </div>

        <div class="d-flex align-items-center gap-3 bg-white-10 p-3 rounded-lg border border-white-20 shadow-sm" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.18);">
            <div class="text-center px-3 border-right border-white-20">
                <span class="d-block font-weight-black text-white" style="font-size: 28px; line-height: 1;">{{ $summary['compliance_rate'] }}%</span>
                <span class="text-white font-weight-bold text-uppercase" style="font-size: 9px; opacity: 0.8; letter-spacing: 0.05em;">Overall Compliance</span>
            </div>
            <div class="text-center px-2">
                <span class="d-block font-weight-black text-white" style="font-size: 20px; line-height: 1;">{{ $summary['fully_compliant'] }}</span>
                <span class="text-white font-weight-bold text-uppercase" style="font-size: 9px; opacity: 0.8; letter-spacing: 0.05em;">Fully Compliant</span>
            </div>
            <div class="text-center px-2">
                <span class="d-block font-weight-bold text-white-50" style="font-size: 13px;">/ {{ $summary['total_employees'] }}</span>
                <span class="text-white font-weight-bold text-uppercase" style="font-size: 9px; opacity: 0.8; letter-spacing: 0.05em;">Employees</span>
            </div>
        </div>
    </div>


    <!-- ==========================================
         SECTION 2 — SCORE RING / BIG VISUAL BLOCK
         ========================================== -->
    <div class="card border-0 shadow-sm rounded-lg mb-4 bg-white overflow-hidden">
        <div class="row align-items-center p-4">
            <div class="col-lg-4 text-center border-right-lg">
                <!-- Circular Ring SVG -->
                <div class="position-relative d-inline-block">
                    <svg width="170" height="170" viewBox="0 0 170 170">
                        <!-- Background circle -->
                        <circle cx="85" cy="85" r="70" fill="none" stroke="#E4E7EC" stroke-width="12" />
                        <!-- Foreground circle with progress -->
                        <circle cx="85" cy="85" r="70" fill="none" stroke="url(#gradientStroke)" stroke-width="12"
                            stroke-dasharray="439.8" stroke-dashoffset="{{ 439.8 - (439.8 * $summary['compliance_rate'] / 100) }}"
                            stroke-linecap="round" transform="rotate(-90 85 85)" style="transition: stroke-dashoffset 1s ease-in-out;" />
                        <!-- Definitions for Gradient -->
                        <defs>
                            <linearGradient id="gradientStroke" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="var(--orb-primary)" />
                                <stop offset="100%" stop-color="var(--orb-secondary)" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <!-- Centered text -->
                    <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <span class="d-block font-weight-black text-dark" style="font-size: 34px; line-height: 1;">{{ $summary['compliance_rate'] }}%</span>
                        <span class="text-muted font-weight-bold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Compliance Rate</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 pl-lg-5 mt-4 mt-lg-0">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 10px; border-radius: 6px;">Live Metrics</span>
                    <h4 class="font-weight-black text-dark mb-0" style="font-size: 20px;">Radial Compliance Engine</h4>
                </div>
                <p class="text-muted font-weight-medium mb-4" style="font-size: 13px; line-height: 1.5;">This real-time system tracks mandatory verification progress across your organization. Meet audit parameters and onboarding stages with continuous document validation.</p>
                <div class="row mb-3">
                    <div class="col-sm-6 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 11px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <span class="d-block text-dark font-weight-bold mb-0" style="font-size: 14px;">{{ $summary['fully_compliant'] }} Employees</span>
                                <span class="text-muted font-weight-bold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Compliant (Zero Risks)</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 11px;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <span class="d-block text-dark font-weight-bold mb-0" style="font-size: 14px;">{{ $summary['non_compliant'] }} Employees</span>
                                <span class="text-muted font-weight-bold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Non-Compliant (At Risk)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compliance progress track -->
                <div>
                    <div class="d-flex justify-content-between font-weight-bold text-muted mb-1" style="font-size: 11px; letter-spacing: 0.02em;">
                        <span>ORGANIZATIONAL PROGRESS</span>
                        <span>{{ $summary['fully_compliant'] }} / {{ $summary['total_employees'] }} Employees Verified</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 99px; background-color: #E4E7EC;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $summary['compliance_rate'] }}%; border-radius: 99px;" aria-valuenow="{{ $summary['compliance_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- ==========================================
         SECTION 3 — FOUR STATUS SUMMARY TILES
         ========================================== -->
    <div class="row mb-4">
        <!-- Tile 1: Fully Compliant -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100 overflow-hidden transition-all compliance-profile-card" style="border-left: 5px solid var(--color-success) !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block text-muted font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Fully Compliant</span>
                        <span class="d-block text-dark font-weight-black my-1" style="font-size: 28px; line-height: 1;">{{ $summary['fully_compliant'] }}</span>
                        <span class="text-muted font-weight-medium" style="font-size: 11px;">Zero risk detected</span>
                    </div>
                    <div class="rounded-circle bg-success-light text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 20px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px; border-radius: 0;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $summary['total_employees'] > 0 ? round(($summary['fully_compliant'] / $summary['total_employees']) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Tile 2: Pending Verification -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100 overflow-hidden transition-all compliance-profile-card" style="border-left: 5px solid var(--color-purple) !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block text-muted font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Pending Verification</span>
                        <span class="d-block text-dark font-weight-black my-1" style="font-size: 28px; line-height: 1;">{{ $summary['pending_verification'] }}</span>
                        <span class="text-muted font-weight-medium" style="font-size: 11px;">Awaiting HR review</span>
                    </div>
                    <div class="rounded-circle bg-purple-light text-purple d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 20px;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px; border-radius: 0;">
                    <div class="progress-bar bg-purple" role="progressbar" style="width: {{ $summary['total_employees'] > 0 ? round(($summary['pending_verification'] / $summary['total_employees']) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Tile 3: Missing Documents -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100 overflow-hidden transition-all compliance-profile-card" style="border-left: 5px solid var(--color-warning) !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block text-muted font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Missing Documents</span>
                        <span class="d-block text-dark font-weight-black my-1" style="font-size: 28px; line-height: 1;">{{ $summary['missing_documents'] }}</span>
                        <span class="text-muted font-weight-medium" style="font-size: 11px;">Mandatory files missing</span>
                    </div>
                    <div class="rounded-circle bg-warning-light text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 20px;">
                        <i class="fas fa-file-excel"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px; border-radius: 0;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $summary['total_employees'] > 0 ? round(($summary['missing_documents'] / $summary['total_employees']) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Tile 4: Rejected / Expired Risk -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100 overflow-hidden transition-all compliance-profile-card" style="border-left: 5px solid var(--color-danger) !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block text-muted font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Rejected / Expired Risk</span>
                        <span class="d-block text-dark font-weight-black my-1" style="font-size: 28px; line-height: 1;">{{ $summary['rejected_documents'] + $summary['expired_documents'] }}</span>
                        <span class="text-muted font-weight-medium" style="font-size: 11px;">Requires urgent action</span>
                    </div>
                    <div class="rounded-circle bg-danger-light text-danger d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 20px;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px; border-radius: 0;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $summary['total_employees'] > 0 ? round((($summary['rejected_documents'] + $summary['expired_documents']) / $summary['total_employees']) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>


    <!-- ==========================================
         SECTION 4 — RISK BREAKDOWN BOARD
         ========================================== -->
    <h5 class="font-weight-black text-dark mb-3" style="font-size: 15px; letter-spacing: -0.01em;">
        <i class="fas fa-exclamation-triangle text-danger mr-1"></i> Compliance Risk Cohorts
    </h5>
    <div class="row mb-4">
        <!-- Card 1: High Risk Employees -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100 bg-white compliance-profile-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-pill badge-danger font-weight-bold text-uppercase px-2" style="font-size: 9px;">High Risk</span>
                            <span class="text-danger font-weight-black" style="font-size: 20px; line-height: 1;">{{ $riskPanels['high_risk']['count'] }}</span>
                        </div>
                        <p class="text-muted font-weight-medium mb-3" style="font-size: 11px; line-height: 1.3;">Employees with multiple missing, rejected, or expired documents.</p>

                        <div class="mb-3">
                            <span class="d-block text-muted font-weight-bold mb-2 text-uppercase" style="font-size: 8px; letter-spacing: 0.05em;">Top Risk Cases:</span>
                            @forelse($riskPanels['high_risk']['employees'] as $emp)
                            <div class="d-flex align-items-center mb-2 gap-2">
                                <div class="rounded-circle bg-danger-light text-danger d-flex align-items-center justify-content-center font-weight-bold" style="width: 22px; height: 22px; font-size: 9px;">
                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <span class="d-block text-dark font-weight-bold text-truncate" style="font-size: 11px; line-height: 1.1;">{{ $emp->name }}</span>
                                    <span class="text-muted font-weight-bold" style="font-size: 8px;">{{ $emp->code }}</span>
                                </div>
                            </div>
                            @empty
                            <span class="text-muted font-weight-bold" style="font-size: 10px; display: block; padding: 4px 0;">No high risk employees.</span>
                            @endforelse
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-danger btn-block font-weight-bold mt-2" onclick="applyRiskFilter('high_risk')" style="font-size: 10px; border-radius: 6px;">
                        View filtered employees
                    </button>
                </div>
            </div>
        </div>

        <!-- Card 2: Missing Mandatory Docs -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100 bg-white compliance-profile-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-pill badge-warning font-weight-bold text-uppercase px-2 text-white" style="font-size: 9px; background-color: var(--color-warning);">Missing Mandatory</span>
                            <span class="font-weight-black" style="font-size: 20px; line-height: 1; color: var(--color-warning);">{{ $riskPanels['missing_mandatory']['count'] }}</span>
                        </div>
                        <p class="text-muted font-weight-medium mb-3" style="font-size: 11px; line-height: 1.3;">Employees with required mandatory files missing from their files.</p>

                        <div class="mb-3">
                            <span class="d-block text-muted font-weight-bold mb-2 text-uppercase" style="font-size: 8px; letter-spacing: 0.05em;">Top Missing Cases:</span>
                            @forelse($riskPanels['missing_mandatory']['employees'] as $emp)
                            <div class="d-flex align-items-center mb-2 gap-2">
                                <div class="rounded-circle bg-warning-light text-warning d-flex align-items-center justify-content-center font-weight-bold" style="width: 22px; height: 22px; font-size: 9px;">
                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <span class="d-block text-dark font-weight-bold text-truncate" style="font-size: 11px; line-height: 1.1;">{{ $emp->name }}</span>
                                    <span class="text-muted font-weight-bold" style="font-size: 8px;">{{ $emp->code }} &bull; {{ $emp->missing_docs }} missing</span>
                                </div>
                            </div>
                            @empty
                            <span class="text-muted font-weight-bold" style="font-size: 10px; display: block; padding: 4px 0;">No missing documents.</span>
                            @endforelse
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-warning btn-block font-weight-bold text-warning mt-2" onclick="applyRiskFilter('missing_mandatory')" style="font-size: 10px; border-radius: 6px;">
                        View filtered employees
                    </button>
                </div>
            </div>
        </div>

        <!-- Card 3: Rejected Docs -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100 bg-white compliance-profile-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-pill font-weight-bold text-uppercase px-2" style="font-size: 9px; background-color: var(--color-danger-light); color: var(--color-danger);">Rejected Docs</span>
                            <span class="font-weight-black" style="font-size: 20px; line-height: 1; color: var(--color-danger);">{{ $riskPanels['rejected']['count'] }}</span>
                        </div>
                        <p class="text-muted font-weight-medium mb-3" style="font-size: 11px; line-height: 1.3;">Employees with files rejected by HR during verification check.</p>

                        <div class="mb-3">
                            <span class="d-block text-muted font-weight-bold mb-2 text-uppercase" style="font-size: 8px; letter-spacing: 0.05em;">Top Rejected Cases:</span>
                            @forelse($riskPanels['rejected']['employees'] as $emp)
                            <div class="d-flex align-items-center mb-2 gap-2">
                                <div class="rounded-circle bg-danger-light text-danger d-flex align-items-center justify-content-center font-weight-bold" style="width: 22px; height: 22px; font-size: 9px;">
                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <span class="d-block text-dark font-weight-bold text-truncate" style="font-size: 11px; line-height: 1.1;">{{ $emp->name }}</span>
                                    <span class="text-muted font-weight-bold" style="font-size: 8px;">{{ $emp->code }} &bull; {{ $emp->rejected_docs }} rejected</span>
                                </div>
                            </div>
                            @empty
                            <span class="text-muted font-weight-bold" style="font-size: 10px; display: block; padding: 4px 0;">No rejected documents.</span>
                            @endforelse
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-danger btn-block font-weight-bold mt-2" onclick="applyRiskFilter('rejected')" style="font-size: 10px; border-radius: 6px;">
                        View filtered employees
                    </button>
                </div>
            </div>
        </div>

        <!-- Card 4: Expiring/Expired Docs -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100 bg-white compliance-profile-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-pill font-weight-bold text-uppercase px-2" style="font-size: 9px; background-color: var(--color-info-light); color: var(--color-info);">Expired / Expiring</span>
                            <span class="font-weight-black" style="font-size: 20px; line-height: 1; color: var(--color-info);">{{ $riskPanels['expired']['count'] }}</span>
                        </div>
                        <p class="text-muted font-weight-medium mb-3" style="font-size: 11px; line-height: 1.3;">Employees with files that have already expired or are near expiry.</p>

                        <div class="mb-3">
                            <span class="d-block text-muted font-weight-bold mb-2 text-uppercase" style="font-size: 8px; letter-spacing: 0.05em;">Top Expired Cases:</span>
                            @forelse($riskPanels['expired']['employees'] as $emp)
                            <div class="d-flex align-items-center mb-2 gap-2">
                                <div class="rounded-circle bg-info-light text-info d-flex align-items-center justify-content-center font-weight-bold" style="width: 22px; height: 22px; font-size: 9px;">
                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <span class="d-block text-dark font-weight-bold text-truncate" style="font-size: 11px; line-height: 1.1;">{{ $emp->name }}</span>
                                    <span class="text-muted font-weight-bold" style="font-size: 8px;">{{ $emp->code }} &bull; {{ $emp->expired_docs }} expired</span>
                                </div>
                            </div>
                            @empty
                            <span class="text-muted font-weight-bold" style="font-size: 10px; display: block; padding: 4px 0;">No expired documents.</span>
                            @endforelse
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-info btn-block font-weight-bold mt-2" onclick="applyRiskFilter('expired')" style="font-size: 10px; border-radius: 6px;">
                        View filtered employees
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ==========================================
         SECTION 6 — FILTER BAR (ABOVE CARDS)
         ========================================== -->
    <h5 class="font-weight-black text-dark mb-3" style="font-size: 15px; letter-spacing: -0.01em;">
        <i class="fas fa-filter text-primary mr-1"></i> Filter Directory & Profiles
    </h5>
    <form method="GET" action="{{ route('documents.compliance.index') }}" id="complianceFilterForm">
        <!-- Hidden input to maintain risk cohort filter -->
        <input type="hidden" name="risk_type" id="filterRiskType" value="{{ request('risk_type') }}">

        <div class="card border-0 shadow-sm rounded-lg mb-4 bg-white">
            <div class="card-body p-3">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label class="font-weight-bold text-muted mb-1" style="font-size: 11px;">Search Employee</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0" style="border-radius: 6px 0 0 6px;"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="employee" id="filterSearch" value="{{ request('employee', request('search')) }}" class="form-control bg-light border-left-0" style="font-size: 13px; height: 38px; border-radius: 0 6px 6px 0;" placeholder="Search name, code, email...">
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                        <label class="font-weight-bold text-muted mb-1" style="font-size: 11px;">Department</label>
                        <select name="department_id" id="filterDepartment" class="form-control bg-light" style="font-size: 13px; height: 38px; border-radius: 6px;">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                        <label class="font-weight-bold text-muted mb-1" style="font-size: 11px;">Stage</label>
                        <select name="employee_stage" id="filterEmployeeStage" class="form-control bg-light" style="font-size: 13px; height: 38px; border-radius: 6px;">
                            <option value="">All Stages</option>
                            <option value="internship" {{ request('employee_stage') == 'internship' ? 'selected' : '' }}>Internship</option>
                            <option value="probation" {{ request('employee_stage') == 'probation' ? 'selected' : '' }}>Probation</option>
                            <option value="permanent" {{ request('employee_stage') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                            <option value="exit" {{ request('employee_stage') == 'exit' ? 'selected' : '' }}>Exit Process</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                        <label class="font-weight-bold text-muted mb-1" style="font-size: 11px;">Profile Status</label>
                        <select name="profile_status" id="filterProfileStatus" class="form-control bg-light" style="font-size: 13px; height: 38px; border-radius: 6px;">
                            <option value="">All Profiles</option>
                            <option value="pending" {{ request('profile_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="submitted" {{ request('profile_status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="approved" {{ request('profile_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('profile_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                        <label class="font-weight-bold text-muted mb-1" style="font-size: 11px;">Compliance Status</label>
                        <select name="compliance_status" id="filterStatus" class="form-control bg-light" style="font-size: 13px; height: 38px; border-radius: 6px;">
                            <option value="">All Statuses</option>
                            <option value="compliant" {{ request('compliance_status') == 'compliant' ? 'selected' : '' }}>Fully Compliant</option>
                            <option value="non_compliant" {{ request('compliance_status') == 'non_compliant' ? 'selected' : '' }}>Non-Compliant</option>
                            <option value="missing" {{ request('compliance_status') == 'missing' ? 'selected' : '' }}>Missing Docs</option>
                            <option value="rejected" {{ request('compliance_status') == 'rejected' ? 'selected' : '' }}>Rejected Docs</option>
                            <option value="expired" {{ request('compliance_status') == 'expired' ? 'selected' : '' }}>Expired Docs</option>
                            <option value="pending" {{ request('compliance_status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-12 text-lg-right text-left mt-2 mt-lg-0">
                        <a href="{{ route('documents.compliance.index') }}" class="btn btn-dark btn-block font-weight-bold d-flex align-items-center justify-content-center transition-all" style="font-size: 12px; height: 38px; border-radius: 6px; white-space: nowrap;">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <!-- Pagination -->
    @if($employees->hasPages())
    <div class="d-flex justify-content-center mt-2 mb-4">
        {{ $employees->links() }}
    </div>
    @endif


    <!-- ==========================================
         SECTION 7 — OPTIONAL LEDGER TABLE
         ========================================== -->
    <div class="card border-0 shadow-sm rounded-lg mb-5 bg-white overflow-hidden">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center" id="ledgerHeader" style="cursor: pointer;" data-toggle="collapse" data-target="#ledgerCollapse" aria-expanded="false" aria-controls="ledgerCollapse">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 16px;">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-black text-dark" style="font-size: 15px;">Compliance Ledger</h5>
                    <p class="text-muted mb-0 font-weight-medium" style="font-size: 11px; opacity: 0.8;">Collapsible detailed grid layout and document exporting system.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Target div where DT exports will append -->
                <div id="complianceExportButtonsTarget" class="d-inline-flex gap-2"></div>
                <button class="btn btn-sm btn-light rounded-circle" type="button" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chevron-down transition-all" id="ledgerChevron"></i>
                </button>
            </div>
        </div>

        <div id="ledgerCollapse" class="collapse" aria-labelledby="ledgerHeader">
            <div class="card-body p-0 border-top">
                <!-- DataTable Toolbar -->
                <div class="dm-table-toolbar-row px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div id="employeeLengthBox"></div>
                    <div id="employeeExportButtons"></div>
                </div>

                <!-- 16-Column Table Listing -->
                <div class="dm-table-wrap px-4 pb-4">
                    <table id="employeeDocDirectoryTable" class="table dm-table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Code</th>
                                <th>Department</th>
                                <th>Stage</th>
                                <th>Profile Status</th>
                                <th>Required</th>
                                <th>Uploaded</th>
                                <th>Verified</th>
                                <th>Missing</th>
                                <th>Pending</th>
                                <th>Rejected</th>
                                <th>Expired</th>
                                <th>Compliance %</th>
                                <th>Compliance Status</th>
                                <th>Last Verified</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($employees as $emp)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="dm-avatar-wrapper rounded-circle font-weight-black d-flex align-items-center justify-content-center text-primary bg-light" style="width: 32px; height: 32px; font-size: 12px; min-width: 32px;">
                                            {{ strtoupper(substr($emp->name ?? 'E', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 800; color: var(--orb-text); font-size: 13px;">{{ $emp->name }}</div>
                                            <div style="font-size: 10px; color: var(--orb-muted); font-weight: 700;">{{ $emp->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $emp->code ?? '-' }}</td>
                                <td>{{ $emp->department_name ?: '-' }}</td>
                                <td><span style="font-weight: 700; text-transform: uppercase; font-size: 9px; color: var(--orb-muted);">{{ $emp->stage ?: '-' }}</span></td>
                                <td>
                                    @php
                                    $profileBadgeClass = match($emp->profile_status) {
                                    'approved' => 'dm-badge-success',
                                    'rejected' => 'dm-badge-danger',
                                    'submitted' => 'dm-badge-purple',
                                    default => 'dm-badge-secondary',
                                    };
                                    @endphp
                                    <span class="dm-badge {{ $profileBadgeClass }}">{{ ucfirst($emp->profile_status ?? 'pending') }}</span>
                                </td>

                                <td>{{ $emp->total_required_docs }}</td>
                                <td>{{ $emp->uploaded_docs }}</td>
                                <td>{{ $emp->verified_docs }}</td>
                                <td>{{ $emp->missing_docs }}</td>
                                <td>{{ $emp->pending_docs }}</td>
                                <td>{{ $emp->rejected_docs }}</td>
                                <td>{{ $emp->expired_docs }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="font-weight: 800; font-size: 11px; color: var(--orb-text);">{{ $emp->compliance_percentage }}%</span>
                                        <div class="progress" style="width: 50px; height: 5px; border-radius: 99px;">
                                            <div class="progress-bar {{ $emp->compliance_percentage === 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $emp->compliance_percentage }}%;"></div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($emp->compliance_status === 'compliant')
                                    <span class="dm-badge dm-badge-success"><i class="fas fa-check-circle mr-1"></i> Fully Compliant</span>
                                    @elseif($emp->compliance_status === 'rejected')
                                    <span class="dm-badge dm-badge-danger"><i class="fas fa-times-circle mr-1"></i> Rejected Docs</span>
                                    @elseif($emp->compliance_status === 'missing')
                                    <span class="dm-badge dm-badge-warning"><i class="fas fa-exclamation-circle mr-1"></i> Missing Docs</span>
                                    @elseif($emp->compliance_status === 'expired')
                                    <span class="dm-badge dm-badge-warning text-white" style="background: var(--color-warning);"><i class="fas fa-calendar-times mr-1"></i> Expired Docs</span>
                                    @else
                                    <span class="dm-badge dm-badge-purple"><i class="fas fa-clock mr-1"></i> Pending Verification</span>
                                    @endif
                                </td>
                                <td>{{ $emp->last_verified_at ? \Carbon\Carbon::parse($emp->last_verified_at)->format('d M Y h:i A') : '-' }}</td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('documents.employee.show', $emp->id) }}" class="dm-action-btn-pill dm-action-btn-primary" style="white-space: nowrap;">
                                            <i class="fas fa-folder-open mr-1"></i> View Documents
                                        </a>
                                        <a href="{{ route('hrms.employees.edit', $emp->id) }}" target="_blank" class="dm-action-btn-pill dm-action-btn-light" style="white-space: nowrap;">
                                            <i class="fas fa-external-link-alt mr-1"></i> Open Profile
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="16" class="text-center text-muted py-4" style="font-weight: 700; color: var(--orb-muted);">No compliance records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- DataTable Footer -->
                <div class="dm-table-footer-row px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div id="employeeInfoBox"></div>
                    <div id="employeePaginationBox"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    function applyRiskFilter(riskType) {
        $('#filterRiskType').val(riskType);
        $('#complianceFilterForm').submit();
    }

    $(document).ready(function() {
        // Toggle chevron direction on ledger collapse
        $('#ledgerCollapse').on('show.bs.collapse', function() {
            $('#ledgerChevron').addClass('fa-rotate-180');
        }).on('hide.bs.collapse', function() {
            $('#ledgerChevron').removeClass('fa-rotate-180');
        });

        function cleanExportText(data) {
            return $('<div>').html(data).text().replace(/\s+/g, ' ').trim();
        }

        let table = $('#employeeDocDirectoryTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            order: [
                [0, 'asc']
            ],
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    title: 'Employee Compliance Overview',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    title: 'Employee Compliance Overview',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    title: 'Employee Compliance Overview',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    title: 'Employee Compliance Overview',
                    className: 'btn btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                }
            ],
            initComplete: function() {
                $('.dataTables_length').appendTo('#employeeLengthBox');
                $('.dt-buttons').appendTo('#complianceExportButtonsTarget');
                $('.dataTables_info').appendTo('#employeeInfoBox');
                $('.dataTables_paginate').appendTo('#employeePaginationBox');
            }
        });

        let searchTimer = null;

        $('#filterSearch').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                $('#complianceFilterForm').submit();
            }, 500);
        });

        $('#filterStatus, #filterDepartment, #filterProfileStatus, #filterEmployeeStage').on('change', function() {
            $('#complianceFilterForm').submit();
        });
    });
</script>
@endsection