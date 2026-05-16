@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Expiring Documents')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
    }
    .eo-page { min-height: calc(100vh - 90px); padding: 16px 10px 30px; background: var(--orb-bg); }
    .eo-container { max-width: 1320px; margin: 0 auto; }
    .eo-header { background: #fff; border: 1px solid var(--orb-border); border-radius: 20px; padding: 16px; margin-bottom: 20px; }
    .eo-card { background: #fff; border-radius: 20px; border: 1px solid var(--orb-border); padding: 20px; }
    .table th { background: #F8FAFC; font-size: 11px; text-transform: uppercase; }
</style>

<div class="eo-page">
    <div class="eo-container">
        <div class="eo-header">
            <h1 style="margin:0; font-size: 24px; font-weight: 900; color: var(--orb-text);">Expiring Documents</h1>
            <p>Documents expiring within {{ $days }} days.</p>
        </div>

        <div class="eo-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee Code</th>
                            <th>Name</th>
                            <th>Document Type</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td><code>{{ $doc->employee->employee_code ?? '-' }}</code></td>
                                <td>{{ $doc->employee->user->name ?? '-' }}</td>
                                <td>{{ $doc->documentType->name ?? '-' }}</td>
                                <td class="text-danger font-weight-bold">{{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('hrms.documents.hr.show', $doc->employee->user_id) }}" class="btn btn-sm btn-primary">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No expiring documents found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
