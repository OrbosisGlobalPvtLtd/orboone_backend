@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Expiring Documents')

@section('_head')
@include('hrms.documents.partials.styles')
@endsection

@section('_content')
<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div>
            <div class="dm-kicker">
                <i class="fas fa-file-alt"></i> HRMS &bull; DOCUMENT MANAGEMENT
            </div>
            <h1>Expiring Documents</h1>
            <p>List of employee compliance records and credentials expiring within the next {{ $days }} days.</p>
        </div>
    </div>

    <!-- Expiring Docs Card -->
    <div class="dm-card">
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-calendar-times"></i></div>
                <div>
                    <h5 class="dm-table-title">Compliance Expiry Tracking</h5>
                    <p class="dm-table-subtitle">Review upcoming credential expirations and request document renewals from employees.</p>
                </div>
            </div>
        </div>

        <div class="dm-table-wrap">
            <table class="table dm-table">
                <thead>
                    <tr>
                        <th>Employee Code</th>
                        <th>Name</th>
                        <th>Document Type</th>
                        <th>Expiry Date</th>
                        <th width="160">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td>
                                <span class="d-inline-flex align-items-center justify-content-center" style="padding: 4px 10px; border-radius: 6px; background: #F1F5F9; color: #475569; font-weight: 700; font-size: 12px; font-family: monospace;">
                                    {{ $doc->employee->employee_code ?? '-' }}
                                </span>
                            </td>
                            <td><span style="font-weight: 800;">{{ $doc->employee->user->name ?? '-' }}</span></td>
                            <td><span style="font-weight: 700; color: var(--dm-primary);">{{ $doc->documentType->name ?? '-' }}</span></td>
                            <td>
                                <span class="dm-badge dm-badge-danger">
                                    <i class="fas fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('hrms.documents.hr.show', $doc->employee->user_id) }}" class="dm-action-btn-pill dm-action-btn-primary">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No expiring documents found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
        <div class="p-3 d-flex justify-content-end" style="border-top: 1px solid var(--dm-border); background: #F8FAFC;">
            {{ $documents->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
