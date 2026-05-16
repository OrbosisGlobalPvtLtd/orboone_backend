@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Document Dashboard')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #fff;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-success: #12B76A;
        --orb-warning: #F79009;
        --orb-danger: #EC4E74;
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .doc-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .doc-container {
        max-width: 1320px;
        margin: 0 auto;
    }

    .doc-header {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .doc-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 25px;
        font-weight: 950;
    }

    .doc-subtitle {
        margin: 5px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .doc-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .doc-btn {
        min-height: 40px;
        border-radius: 13px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
    }

    .doc-btn-primary {
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        box-shadow: 0 10px 22px rgba(75, 0, 232, .16);
    }

    .doc-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 16px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 94px;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        flex: 0 0 auto;
    }

    .stat-label {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .stat-value {
        margin-top: 4px;
        color: var(--orb-text);
        font-size: 25px;
        font-weight: 950;
        line-height: 1;
    }

    .stat-success .stat-icon {
        background: rgba(18, 183, 106, .10);
        color: var(--orb-success);
    }

    .stat-warning .stat-icon {
        background: #FFF7E8;
        color: #B54708;
    }

    .stat-danger .stat-icon {
        background: rgba(236, 78, 116, .10);
        color: var(--orb-danger);
    }

    .doc-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .doc-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .doc-card.full {
        grid-column: 1 / -1;
    }

    .doc-card-head {
        padding: 16px;
        border-bottom: 1px solid var(--orb-border);
        background: #FCFCFD;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .doc-card-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 16px;
        font-weight: 950;
    }

    .doc-card-subtitle {
        margin: 4px 0 0;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .doc-table {
        width: 100%;
        margin: 0 !important;
    }

    .doc-table th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        padding: 12px 14px;
        border-bottom: 1px solid var(--orb-border);
        white-space: nowrap;
    }

    .doc-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #F1F3F8;
        vertical-align: middle;
        font-size: 13px;
        font-weight: 700;
        color: var(--orb-text);
    }

    .emp-name {
        font-weight: 900;
        color: var(--orb-text);
    }

    .emp-meta {
        font-size: 11px;
        color: var(--orb-muted);
        font-weight: 700;
        margin-top: 2px;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .pill-success {
        color: #067647;
        background: rgba(18, 183, 106, .10);
    }

    .pill-warning {
        color: #B54708;
        background: #FFF7E8;
    }

    .pill-danger {
        color: #C01048;
        background: rgba(236, 78, 116, .10);
    }

    .pill-muted {
        color: #667085;
        background: #F2F4F7;
    }

    .empty-box {
        padding: 34px 16px;
        text-align: center;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 800;
    }

    .missing-list {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        max-width: 380px;
    }

    @media(max-width:1100px) {
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .doc-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:576px) {
        .doc-page {
            padding: 12px 8px 24px;
        }

        .doc-header {
            flex-direction: column;
            align-items: flex-start;
            border-radius: 16px;
        }

        .doc-title {
            font-size: 21px;
        }

        .stat-grid {
            grid-template-columns: 1fr;
        }

        .doc-actions,
        .doc-btn {
            width: 100%;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .doc-table {
            min-width: 720px;
        }
    }
</style>

@php
$totalEmployees = $stats['total_employees'] ?? 0;
$verifiedEmployees = $stats['verified_employees'] ?? 0;
$compliance = $totalEmployees > 0 ? round(($verifiedEmployees / max($totalEmployees, 1)) * 100) : 0;
@endphp

<div class="doc-page">
    <div class="doc-container">
        <div class="doc-header">
            <div>
                <h1 class="doc-title">Document Dashboard</h1>
                <p class="doc-subtitle">Employee-wise verification, missing mandatory documents and expiring records.</p>
            </div>

            <div class="doc-actions">
                <a href="{{ route('hrms.documents.hr.index') }}" class="doc-btn doc-btn-primary">
                    <i class="fas fa-user-check"></i> Pending Verification
                </a>
                <a href="{{ route('hrms.documents.employee.index') }}" class="doc-btn doc-btn-light">
                    <i class="fas fa-folder-open"></i> Employee Documents
                </a>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-label">Total Employees</div>
                    <div class="stat-value">{{ $stats['total_employees'] ?? 0 }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
                <div>
                    <div class="stat-label">Employees With Docs</div>
                    <div class="stat-value">{{ $stats['employees_with_documents'] ?? 0 }}</div>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Verified Employees</div>
                    <div class="stat-value">{{ $stats['verified_employees'] ?? 0 }}</div>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                <div>
                    <div class="stat-label">Pending Employees</div>
                    <div class="stat-value">{{ $stats['pending_employees'] ?? 0 }}</div>
                </div>
            </div>

            <div class="stat-card stat-danger">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-label">Issue Employees</div>
                    <div class="stat-value">{{ $stats['issue_employees'] ?? 0 }}</div>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="stat-label">Missing Mandatory</div>
                    <div class="stat-value">{{ $stats['missing_mandatory_employees'] ?? 0 }}</div>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon"><i class="fas fa-calendar-times"></i></div>
                <div>
                    <div class="stat-label">Expiring Soon</div>
                    <div class="stat-value">{{ $stats['expiring_documents'] ?? 0 }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                <div>
                    <div class="stat-label">Compliance</div>
                    <div class="stat-value">{{ $compliance }}%</div>
                </div>
            </div>
        </div>

        <div class="doc-grid">
            <div class="doc-card">
                <div class="doc-card-head">
                    <div>
                        <h4 class="doc-card-title">Pending Verification Employees</h4>
                        <p class="doc-card-subtitle">Employee-wise pending document verification.</p>
                    </div>
                    <a href="{{ route('hrms.documents.hr.index') }}" class="doc-btn doc-btn-light">View All</a>
                </div>

                <div class="table-responsive">
                    <table class="table doc-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Uploaded</th>
                                <th>Pending</th>
                                <th>Missing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingEmployeesList ?? [] as $employee)
                            <tr>
                                <td>
                                    <div class="emp-name">{{ $employee->user->name ?? '-' }}</div>
                                    <div class="emp-meta">{{ $employee->employee_code ?? '-' }}</div>
                                </td>
                                <td><span class="pill pill-muted">{{ $employee->doc_total ?? 0 }}</span></td>
                                <td><span class="pill pill-warning">{{ $employee->doc_pending ?? 0 }}</span></td>
                                <td><span class="pill pill-danger">{{ $employee->missing_mandatory ?? 0 }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="empty-box">No pending employees found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card-head">
                    <div>
                        <h4 class="doc-card-title">Missing Mandatory Documents</h4>
                        <p class="doc-card-subtitle">Employees whose required documents are missing.</p>
                    </div>
                    <a href="{{ route('hrms.documents.employee.index') }}" class="doc-btn doc-btn-light">View All</a>
                </div>

                <div class="table-responsive">
                    <table class="table doc-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Missing Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($missingMandatoryEmployeesList ?? [] as $employee)
                            <tr>
                                <td>
                                    <div class="emp-name">{{ $employee->user->name ?? '-' }}</div>
                                    <div class="emp-meta">{{ $employee->employee_code ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="missing-list">
                                        @foreach($employee->missing_documents ?? [] as $missing)
                                        <span class="pill pill-danger">{{ $missing }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="empty-box">No missing mandatory documents found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card-head">
                    <div>
                        <h4 class="doc-card-title">Expiring Documents</h4>
                        <p class="doc-card-subtitle">Documents expiring within next 30 days.</p>
                    </div>
                    <a href="{{ route('hrms.documents.expiring') }}" class="doc-btn doc-btn-light">View All</a>
                </div>

                <div class="table-responsive">
                    <table class="table doc-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Document</th>
                                <th>Expiry</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringDocuments ?? [] as $doc)
                            <tr>
                                <td>
                                    <div class="emp-name">{{ $doc->employee->user->name ?? '-' }}</div>
                                    <div class="emp-meta">{{ $doc->employee->employee_code ?? '-' }}</div>
                                </td>
                                <td>{{ $doc->documentType->name ?? $doc->title ?? '-' }}</td>
                                <td>
                                    <span class="pill pill-danger">
                                        {{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') : '-' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="empty-box">No expiring documents found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card-head">
                    <div>
                        <h4 class="doc-card-title">Recently Verified Employees</h4>
                        <p class="doc-card-subtitle">Latest employee document verification activity.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table doc-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Verified Docs</th>
                                <th>Verified By</th>
                                <th>Verified At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentVerifiedEmployees ?? [] as $employee)
                            <tr>
                                <td>
                                    <div class="emp-name">{{ $employee->user->name ?? '-' }}</div>
                                    <div class="emp-meta">{{ $employee->employee_code ?? '-' }}</div>
                                </td>
                                <td><span class="pill pill-success">{{ $employee->verified_docs_count ?? 0 }}</span></td>
                                <td>{{ $employee->last_verified_by ?? '-' }}</td>
                                <td>
                                    {{ $employee->last_verified_at ? \Carbon\Carbon::parse($employee->last_verified_at)->format('d M Y, h:i A') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="empty-box">No verified activity found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection