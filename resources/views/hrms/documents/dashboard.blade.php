@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Document Dashboard')

@section('_head')
@include('hrms.documents.partials.styles')
@endsection

@section('_content')
@php
$totalEmployees = $stats['total_employees'] ?? 0;
$verifiedEmployees = $stats['verified_employees'] ?? 0;
$compliance = $totalEmployees > 0 ? round(($verifiedEmployees / max($totalEmployees, 1)) * 100) : 0;
@endphp

<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; DOCUMENT MANAGEMENT
            </div>
            <h1>Document Dashboard</h1>
            <p>Employee-wise verification tracking, missing mandatory records, and expiring compliance metrics.</p>
        </div>
        <div class="dm-hero-actions">
            <a href="{{ route('hrms.documents.hr.index') }}" class="dm-btn dm-btn-primary">
                <i class="fas fa-user-check"></i> Pending Verification
            </a>
            <a href="{{ route('hrms.documents.employee.index') }}" class="dm-btn dm-btn-light">
                <i class="fas fa-folder-open"></i> Employee Documents
            </a>
        </div>
    </div>

    <!-- Summary Metrics (8 compact cards) -->
    <div class="row dm-metrics-grid">
        <!-- Card 1: Total Employees -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-primary">
                <div class="dm-metric-icon dm-icon-primary"><i class="fas fa-users"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Total Employees</div>
                    <div class="dm-metric-value">{{ $stats['total_employees'] ?? 0 }}</div>
                    <div class="dm-metric-trend text-primary"><i class="fas fa-user-check"></i> Total headcount</div>
                </div>
            </div>
        </div>

        <!-- Card 2: Employees With Docs -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-info">
                <div class="dm-metric-icon dm-icon-info"><i class="fas fa-folder-open"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Employees With Docs</div>
                    <div class="dm-metric-value">{{ $stats['employees_with_documents'] ?? 0 }}</div>
                    <div class="dm-metric-trend text-info"><i class="fas fa-file-invoice"></i> Uploaded accounts</div>
                </div>
            </div>
        </div>

        <!-- Card 3: Verified Employees -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-success">
                <div class="dm-metric-icon dm-icon-success"><i class="fas fa-check-circle"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Verified Employees</div>
                    <div class="dm-metric-value">{{ $stats['verified_employees'] ?? 0 }}</div>
                    <div class="dm-metric-trend text-success"><i class="fas fa-shield-alt"></i> Fully compliant</div>
                </div>
            </div>
        </div>

        <!-- Card 4: Pending Employees -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-warning">
                <div class="dm-metric-icon dm-icon-warning"><i class="fas fa-user-clock"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Pending Employees</div>
                    <div class="dm-metric-value">{{ $stats['pending_employees'] ?? 0 }}</div>
                    <div class="dm-metric-trend text-warning"><i class="fas fa-hourglass-half"></i> Awaiting verification</div>
                </div>
            </div>
        </div>

        <!-- Card 5: Issue Employees -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-danger">
                <div class="dm-metric-icon dm-icon-danger"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Issue Employees</div>
                    <div class="dm-metric-value">{{ $stats['issue_employees'] ?? 0 }}</div>
                    <div class="dm-metric-trend text-danger"><i class="fas fa-times-circle"></i> Rejected records</div>
                </div>
            </div>
        </div>

        <!-- Card 6: Missing Mandatory -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-warning">
                <div class="dm-metric-icon dm-icon-warning"><i class="fas fa-clipboard-list"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Missing Mandatory</div>
                    <div class="dm-metric-value">{{ $stats['missing_mandatory_employees'] ?? 0 }}</div>
                    <div class="dm-metric-trend text-warning"><i class="fas fa-exclamation-circle"></i> Incomplete files</div>
                </div>
            </div>
        </div>

        <!-- Card 7: Expiring Soon -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-danger">
                <div class="dm-metric-icon dm-icon-danger"><i class="fas fa-calendar-times"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Expiring Soon</div>
                    <div class="dm-metric-value">{{ $stats['expiring_documents'] ?? 0 }}</div>
                    <div class="dm-metric-trend text-danger"><i class="fas fa-bell"></i> Expiry warnings</div>
                </div>
            </div>
        </div>

        <!-- Card 8: Compliance -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="dm-metric-card border-bottom-primary">
                <div class="dm-metric-icon dm-icon-primary"><i class="fas fa-shield-alt"></i></div>
                <div class="dm-metric-content">
                    <div class="dm-metric-label">Compliance</div>
                    <div class="dm-metric-value">{{ $compliance }}%</div>
                    <div class="dm-metric-trend text-primary"><i class="fas fa-check-double"></i> Total score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid Cards -->
    <div class="row">
        <!-- Pending Verification Employees -->
        <div class="col-md-6 mb-4">
            <div class="dm-card h-100">
                <div class="dm-table-header">
                    <div class="dm-table-head-left">
                        <div class="dm-icon-box"><i class="fas fa-clock"></i></div>
                        <div>
                            <h5 class="dm-table-title">Pending Verification Employees</h5>
                            <p class="dm-table-subtitle">Employee-wise count of uploaded files awaiting review.</p>
                        </div>
                    </div>
                    <a href="{{ route('hrms.documents.hr.index') }}" class="dm-action-btn-pill dm-action-btn-light">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="dm-table-wrap">
                    <table class="table dm-table">
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
                                <td><span class="dm-badge dm-badge-secondary">{{ $employee->doc_total ?? 0 }}</span></td>
                                <td><span class="dm-badge dm-badge-warning">{{ $employee->doc_pending ?? 0 }}</span></td>
                                <td><span class="dm-badge dm-badge-danger">{{ $employee->missing_mandatory ?? 0 }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-muted text-center py-4">No pending employees found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Missing Mandatory Documents -->
        <div class="col-md-6 mb-4">
            <div class="dm-card h-100">
                <div class="dm-table-header">
                    <div class="dm-table-head-left">
                        <div class="dm-icon-box"><i class="fas fa-exclamation-circle"></i></div>
                        <div>
                            <h5 class="dm-table-title">Missing Mandatory Documents</h5>
                            <p class="dm-table-subtitle">Employees whose required documents are missing.</p>
                        </div>
                    </div>
                    <a href="{{ route('hrms.documents.employee.index') }}" class="dm-action-btn-pill dm-action-btn-light">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="dm-table-wrap">
                    <table class="table dm-table">
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
                                    <div class="missing-list d-flex flex-wrap gap-2">
                                        @foreach($employee->missing_documents ?? [] as $missing)
                                        <span class="dm-badge dm-badge-danger">{{ $missing }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-muted text-center py-4">No missing mandatory documents found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expiring Documents -->
        <div class="col-md-6 mb-4">
            <div class="dm-card h-100">
                <div class="dm-table-header">
                    <div class="dm-table-head-left">
                        <div class="dm-icon-box"><i class="fas fa-calendar-times"></i></div>
                        <div>
                            <h5 class="dm-table-title">Expiring Documents</h5>
                            <p class="dm-table-subtitle">Documents expiring within next 30 days.</p>
                        </div>
                    </div>
                    <a href="{{ route('hrms.documents.expiring') }}" class="dm-action-btn-pill dm-action-btn-light">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="dm-table-wrap">
                    <table class="table dm-table">
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
                                    <span class="dm-badge dm-badge-danger">
                                        {{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') : '-' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-muted text-center py-4">No expiring documents found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recently Verified Employees -->
        <div class="col-md-6 mb-4">
            <div class="dm-card h-100">
                <div class="dm-table-header">
                    <div class="dm-table-head-left">
                        <div class="dm-icon-box"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <h5 class="dm-table-title">Recently Verified Employees</h5>
                            <p class="dm-table-subtitle">Latest employee document verification activity logs.</p>
                        </div>
                    </div>
                </div>

                <div class="dm-table-wrap">
                    <table class="table dm-table">
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
                                <td><span class="dm-badge dm-badge-success">{{ $employee->verified_docs_count ?? 0 }}</span></td>
                                <td>{{ $employee->last_verified_by ?? '-' }}</td>
                                <td>
                                    {{ $employee->last_verified_at ? \Carbon\Carbon::parse($employee->last_verified_at)->format('d M Y, h:i A') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-muted text-center py-4">No verified activity found.</td>
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