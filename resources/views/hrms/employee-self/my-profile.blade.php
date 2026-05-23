@extends('layouts.panel', ['active' => 'my-profile'])

@section('page_title', 'My Profile')

@section('_content')
<style>
:root{
    --orb-primary:#4B00E8;
    --orb-secondary:#8600EE;
    --orb-bg:#F6F7FB;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 10px 28px rgba(16,24,40,.06);
}

.profile-page{
    min-height:calc(100vh - 90px);
    padding:16px 10px 30px;
    background:var(--orb-bg);
}

.profile-container{
    max-width:1180px;
    margin:0 auto;
}

.status-card {
    background: #fff;
    border-radius: 18px;
    padding: 24px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    border: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    gap: 16px;
}

.status-card.pending {
    border-left: 6px solid #f59e0b;
}

.status-card.approved {
    border-left: 6px solid #22c55e;
}

.status-card h4 {
    margin: 0;
    font-weight: 800;
}

.status-card p {
    margin: 4px 0 0;
    color: var(--orb-muted);
}

.profile-section {
    background: #fff;
    border-radius: 18px;
    padding: 24px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    border: 1px solid var(--orb-border);
}

.profile-section h5 {
    margin: 0 0 16px;
    font-weight: 800;
    border-bottom: 1px solid var(--orb-border);
    padding-bottom: 12px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.info-item label {
    display: block;
    font-size: 12px;
    color: var(--orb-muted);
    margin-bottom: 4px;
    font-weight: 600;
}

.info-item span {
    display: block;
    font-size: 14px;
    color: var(--orb-text);
    font-weight: 700;
}

.document-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.document-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border: 1px solid var(--orb-border);
    border-radius: 12px;
}

.doc-name {
    font-weight: 700;
}

.doc-status {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 600;
}
.doc-status.pending { background: #fef3c7; color: #f59e0b; }
.doc-status.verified { background: #dcfce7; color: #22c55e; }
.doc-status.rejected { background: #fee2e2; color: #ef4444; }

</style>

<div class="profile-page">
    <div class="profile-container">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($status['profile_verification_status'] === 'submitted')
            <div class="status-card pending">
                <i class="fas fa-clock fa-3x text-warning"></i>
                <div>
                    <h4>Pending for HR Approval</h4>
                    <p>Your profile and documents have been submitted. HR will verify and approve them.</p>
                </div>
            </div>
        @elseif($status['profile_verification_status'] === 'approved')
            <div class="status-card approved">
                <i class="fas fa-check-circle fa-3x text-success"></i>
                <div>
                    <h4>Profile Approved</h4>
                    <p>Your profile and documents have been successfully verified.</p>
                </div>
            </div>
        @endif

        <div class="profile-section">
            <h5>Personal Details</h5>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <span>{{ $employee->user->name }}</span>
                </div>
                <div class="info-item">
                    <label>Email Address</label>
                    <span>{{ $employee->user->email }}</span>
                </div>
                <div class="info-item">
                    <label>Phone Number</label>
                    <span>{{ $employee->user->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Date of Birth</label>
                    <span>{{ $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d M, Y') : 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Gender</label>
                    <span>{{ ucfirst($profile->gender) }}</span>
                </div>
                <div class="info-item">
                    <label>Address</label>
                    <span>{{ $profile->address }}</span>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h5>Education & Experience</h5>
            <div class="info-grid">
                <div class="info-item">
                    <label>Experience Type</label>
                    <span>{{ ucfirst($profile->experience_type ?? 'Fresher') }}</span>
                </div>
                <div class="info-item">
                    <label>Highest Qualification</label>
                    <span>{{ $profile->highest_qualification }}</span>
                </div>
                <div class="info-item">
                    <label>CGPA / Percentage</label>
                    <span>{{ $profile->cgpa_percentage ?? 'N/A' }}</span>
                </div>
                @if($profile->experience_type == 'experienced')
                <div class="info-item">
                    <label>Total Experience</label>
                    <span>{{ $profile->total_experience }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="profile-section">
            <h5>Bank Details</h5>
            <div class="info-grid">
                <div class="info-item">
                    <label>Bank Account No</label>
                    <span>{{ $profile->bank_account_no }}</span>
                </div>
                <div class="info-item">
                    <label>Account Holder Name</label>
                    <span>{{ $profile->bank_holder_name }}</span>
                </div>
                <div class="info-item">
                    <label>Account Type</label>
                    <span>{{ ucfirst($profile->bank_account_type) }}</span>
                </div>
                <div class="info-item">
                    <label>IFSC Code</label>
                    <span>{{ $profile->ifsc_code }}</span>
                </div>
                <div class="info-item">
                    <label>Bank Branch</label>
                    <span>{{ $profile->bank_branch }}</span>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h5>My Documents</h5>
            <div class="document-list">
                @foreach($payload['uploaded_documents'] as $doc)
                <div class="document-item">
                    <div>
                        <div class="doc-name">{{ $doc['document_type']['name'] ?? $doc['title'] }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ $doc['file_original_name'] }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="doc-status {{ $doc['verification_status'] }}">
                            {{ ucfirst($doc['verification_status']) }}
                        </div>
                        <a href="{{ route('hrms.documents.file', ['path' => $doc['file_path']]) }}" target="_blank" class="btn btn-sm btn-outline-primary">View File</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
