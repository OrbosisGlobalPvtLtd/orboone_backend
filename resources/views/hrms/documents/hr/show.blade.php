@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Employee Document Details')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .eo-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .eo-container {
        max-width: 1320px;
        margin: 0 auto;
    }

    .eo-header {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .eo-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 24px;
        font-weight: 900;
    }

    .eo-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        border: 1px solid var(--orb-border);
        padding: 20px;
        margin-bottom: 20px;
    }

    .table th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .table td {
        vertical-align: middle;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
    }

    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-pending {
        background: #FFF7E8;
        color: #B54708;
    }

    .badge-verified {
        background: rgba(18, 183, 106, .1);
        color: #12B76A;
    }

    .badge-rejected {
        background: rgba(236, 78, 116, .1);
        color: #EC4E74;
    }

    .badge-missing {
        background: #F2F4F7;
        color: #667085;
    }

    .btn-action {
        font-weight: 800;
        border-radius: 10px;
        padding: 6px 12px;
        font-size: 12px;
    }
</style>

<div class="eo-page">
    <div class="eo-container">
        <div class="eo-header">
            <div>
                <h1 class="eo-title">{{ $employee->user->name }} - Documents</h1>
                <p class="eo-subtitle">Code: {{ $employee->employee_code }} | Experience: {{ ucfirst($employee->experience_type ?? 'Fresher') }}</p>
            </div>
            <a href="{{ route('hrms.documents.hr.index') }}" class="btn btn-light btn-action">Back to List</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="eo-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Document Type</th>
                            <th>Mandatory</th>
                            <th>Status</th>
                            <th>View</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentTypes as $type)
                        @php
                        $doc = $documents->get($type->id);
                        @endphp
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td>{!! $type->is_mandatory ? '<span class="text-danger">* Yes</span>' : 'No' !!}</td>
                            <td>
                                @if($doc)
                                @if($doc->verification_status == 'pending')
                                <span class="badge badge-pending">Pending Verification</span>
                                @elseif($doc->verification_status == 'verified')
                                <span class="badge badge-verified">Verified</span>
                                @else
                                <span class="badge badge-rejected">Rejected</span><br>
                                <small class="text-danger">{{ $doc->rejection_reason }}</small>
                                @endif
                                @else
                                <span class="badge badge-missing">Missing</span>
                                @endif
                            </td>
                            <td>
                                @if($doc && $doc->file_path)
                                <!-- <a href="{{ route('hrms.documents.employee.download', $doc->id) }}" target="_blank" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i> View</a> -->
                                <a href="{{ route('hrms.documents.file', $doc->file_path) }}" target="_blank" class="btn btn-sm btn-info btn-action"><i class="fas fa-eye"></i> View</a>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if($doc && $doc->verification_status == 'pending')
                                <form action="{{ route('hrms.documents.hr.verify', $doc->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success btn-action" onclick="return confirm('Verify this document?')"><i class="fas fa-check"></i> Verify</button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger btn-action" data-toggle="modal" data-target="#rejectModal{{ $doc->id }}"><i class="fas fa-times"></i> Reject</button>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('hrms.documents.hr.reject', $doc->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Document: {{ $type->name }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Reason for Rejection</label>
                                                        <textarea name="rejection_reason" class="form-control" required rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Confirm Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection