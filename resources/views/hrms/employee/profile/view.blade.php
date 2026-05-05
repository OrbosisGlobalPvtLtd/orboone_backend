@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'View Employee Profile')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-pink: #D400D5;
        --orb-rose: #EC4E74;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 34px rgba(16, 24, 40, .07);
    }

    .profile-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 32px;
        background:
            radial-gradient(circle at top left, rgba(75,0,232,.07), transparent 28%),
            radial-gradient(circle at top right, rgba(212,0,213,.06), transparent 28%),
            var(--orb-bg);
    }

    .profile-container {
        max-width: 1220px;
        margin: 0 auto;
    }

    .profile-hero {
        border-radius: 24px;
        padding: 22px;
        color: #fff;
        background: linear-gradient(135deg, #4B00E8, #8600EE, #D400D5);
        box-shadow: 0 18px 42px rgba(75, 0, 232, .18);
        margin-bottom: 16px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 18px;
        align-items: center;
    }

    .profile-main {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .profile-avatar {
        width: 92px;
        height: 92px;
        border-radius: 26px;
        background: rgba(255,255,255,.16);
        border: 2px solid rgba(255,255,255,.28);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 30px;
        font-weight: 950;
        flex: 0 0 auto;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-name {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 950;
    }

    .profile-meta {
        margin-top: 6px;
        color: rgba(255,255,255,.82);
        font-size: .86rem;
        font-weight: 700;
    }

    .status-panel {
        min-width: 250px;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 20px;
        padding: 14px;
        backdrop-filter: blur(10px);
    }

    .status-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .4px;
        font-weight: 900;
        color: rgba(255,255,255,.76);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 950;
        margin-top: 8px;
    }

    .status-pending { background: #FFF4D6; color: #B54708; }
    .status-submitted { background: #E0F2FE; color: #0369A1; }
    .status-approved { background: #DCFCE7; color: #166534; }
    .status-rejected { background: #FEE2E2; color: #991B1B; }

    .profile-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .btn-soft,
    .btn-orb,
    .btn-successx,
    .btn-dangerx {
        border-radius: 13px;
        padding: 9px 13px;
        font-size: .8rem;
        font-weight: 950;
        border: 0;
        text-decoration: none;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-soft {
        background: #F4F6FB;
        color: #111827 !important;
        border: 1px solid #E5E7EB;
    }

    .btn-orb {
        background: linear-gradient(135deg, #4B00E8, #8600EE);
        color: #fff !important;
    }

    .btn-successx {
        background: #16A34A;
        color: #fff !important;
    }

    .btn-dangerx {
        background: #DC2626;
        color: #fff !important;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 16px;
        align-items: start;
    }

    .profile-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .profile-card-head {
        padding: 15px 17px;
        border-bottom: 1px solid #EEF1F6;
        display: flex;
        align-items: center;
        gap: 11px;
        background: linear-gradient(180deg,#fff,#FCFCFF);
    }

    .profile-icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--orb-primary);
        background: var(--orb-soft);
        flex: 0 0 auto;
    }

    .profile-card-head h5 {
        margin: 0;
        color: var(--orb-text);
        font-size: .98rem;
        font-weight: 950;
    }

    .profile-card-head p {
        margin: 2px 0 0;
        color: var(--orb-muted);
        font-size: .75rem;
        font-weight: 650;
    }

    .profile-card-body {
        padding: 17px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .profile-info {
        padding: 13px;
        border: 1px solid #EEF1F6;
        border-radius: 15px;
        background: #FCFCFD;
        min-height: 78px;
    }

    .profile-label {
        display: block;
        color: var(--orb-muted);
        font-size: .68rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 6px;
    }

    .profile-value {
        color: var(--orb-text);
        font-size: .84rem;
        font-weight: 850;
        word-break: break-word;
    }

    .muted {
        color: #98A2B3 !important;
    }

    .wide {
        grid-column: 1 / -1;
    }

    .file-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 12px;
        border-radius: 12px;
        background: #F4F2FF;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .14);
        font-size: .8rem;
        font-weight: 950;
        text-decoration: none;
    }

    .review-card {
        position: sticky;
        top: 88px;
    }

    .review-card .profile-card-body {
        min-height: 430px;
        display: flex;
        flex-direction: column;
    }

    .review-box {
        border-radius: 16px;
        padding: 14px;
        background: #FAF8FF;
        border: 1px solid rgba(75,0,232,.12);
    }

    .review-box h6 {
        margin: 0 0 8px;
        font-weight: 950;
        color: var(--orb-text);
        font-size: .88rem;
    }

    .review-box p {
        margin: 0;
        color: var(--orb-muted);
        font-size: .8rem;
        font-weight: 650;
        line-height: 1.5;
    }

    .review-status-box {
        border-radius: 16px;
        padding: 14px;
        background: #F8FAFC;
        border: 1px solid #EEF1F6;
        margin-bottom: 12px;
    }

    .review-actions {
        margin-top: auto;
        padding-top: 14px;
    }

    .reject-form textarea {
        border-radius: 13px;
        font-size: .82rem;
        font-weight: 650;
        min-height: 92px;
        resize: vertical;
    }

    .reject-reason-box {
        margin-top: 12px;
        border-radius: 13px;
        padding: 11px;
        background: #FFF5F5;
        border: 1px solid #FEE2E2;
    }

    .reject-reason-box strong {
        display: block;
        color: #991B1B;
        font-size: .76rem;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .reject-reason-box p {
        margin: 0;
        color: #7F1D1D;
        font-size: .8rem;
        font-weight: 700;
    }

    @media(max-width: 991px) {
        .profile-hero,
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .status-panel {
            min-width: 100%;
        }

        .review-card {
            position: static;
        }

        .review-card .profile-card-body {
            min-height: auto;
        }
    }

    @media(max-width: 767px) {
        .profile-page {
            padding: 10px 8px 26px;
        }

        .profile-main {
            align-items: flex-start;
        }

        .profile-avatar {
            width: 76px;
            height: 76px;
            border-radius: 21px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .profile-actions a,
        .profile-actions button,
        .profile-actions form {
            width: 100%;
        }

        .profile-actions button,
        .profile-actions a {
            text-align: center;
            justify-content: center;
        }
    }
</style>

@php
    $initial = strtoupper(substr($profile->name ?? 'E', 0, 1));
    $status = $profile->profile_status ?? 'pending';

    $statusClass = match($status) {
        'submitted' => 'status-submitted',
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        default => 'status-pending',
    };

    $statusText = match($status) {
        'submitted' => 'Submitted For Review',
        'approved' => 'Completed / Approved',
        'rejected' => 'Rejected',
        default => 'Pending',
    };
@endphp

<div class="profile-page">
    <div class="profile-container">

        @if(session('success'))
            <div class="alert alert-success rounded-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
        @endif

        <div class="profile-hero">
            <div class="profile-main">
                <div class="profile-avatar">
                    @if (!empty($profile->profile_image) && Route::has('hrms.documents.file'))
                        <img src="{{ route('hrms.documents.file', $profile->profile_image) }}" alt="Profile">
                    @else
                        {{ $initial }}
                    @endif
                </div>

                <div>
                    <h2 class="profile-name">{{ $profile->name ?? '-' }}</h2>

                    <div class="profile-meta">
                        <i class="fas fa-id-badge mr-1"></i>
                        {{ $profile->employee_code ?? '-' }}
                    </div>

                    <div class="profile-meta">
                        <i class="fas fa-envelope mr-1"></i>
                        {{ $profile->email ?? '-' }}
                    </div>

                    <div class="profile-meta">
                        <i class="fas fa-building mr-1"></i>
                        {{ $profile->department_name ?? '-' }}
                        @if(!empty($profile->designation_name))
                            • {{ $profile->designation_name }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="status-panel">
                <div class="status-label">Profile Status</div>

                <div class="status-badge {{ $statusClass }}">
                    <i class="fas fa-circle"></i>
                    {{ $statusText }}
                </div>

                <div class="profile-actions">
                    <a href="{{ route('hrms.employees.pending_profiles') }}" class="btn-soft">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>

                    @if(Route::has('hrms.employees.profile.edit'))
                        <a href="{{ route('hrms.employees.profile.edit', $profile->employee_id) }}" class="btn-orb">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="profile-grid">
            <div>
                <div class="profile-card">
                    <div class="profile-card-head">
                        <div class="profile-icon"><i class="fas fa-user"></i></div>
                        <div>
                            <h5>Personal Details</h5>
                            <p>Basic identity and address information.</p>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <div class="info-grid">
                            <div class="profile-info">
                                <span class="profile-label">Date of Birth</span>
                                <div class="profile-value {{ empty($profile->date_of_birth) ? 'muted' : '' }}">
                                    {{ !empty($profile->date_of_birth) ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d M Y') : '-' }}
                                </div>
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Gender</span>
                                <div class="profile-value {{ empty($profile->gender) ? 'muted' : '' }}">
                                    {{ !empty($profile->gender) ? ucfirst($profile->gender) : '-' }}
                                </div>
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Phone</span>
                                <div class="profile-value {{ empty($profile->phone) ? 'muted' : '' }}">
                                    {{ $profile->phone ?? '-' }}
                                </div>
                            </div>

                            <div class="profile-info wide">
                                <span class="profile-label">Address</span>
                                <div class="profile-value {{ empty($profile->address) ? 'muted' : '' }}">
                                    {{ $profile->address ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="profile-card-head">
                        <div class="profile-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div>
                            <h5>Education & Experience</h5>
                            <p>Qualification, score and total work experience.</p>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <div class="info-grid">
                            <div class="profile-info">
                                <span class="profile-label">Qualification</span>
                                <div class="profile-value {{ empty($profile->highest_qualification) ? 'muted' : '' }}">
                                    {{ $profile->highest_qualification ?? '-' }}
                                </div>
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">CGPA / Percentage</span>
                                <div class="profile-value {{ empty($profile->cgpa_percentage) ? 'muted' : '' }}">
                                    {{ $profile->cgpa_percentage ?? '-' }}
                                </div>
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Experience</span>
                                <div class="profile-value {{ empty($profile->total_experience) ? 'muted' : '' }}">
                                    {{ $profile->total_experience ?? '-' }}
                                </div>
                            </div>

                            <div class="profile-info wide">
                                <span class="profile-label">Resume</span>
                                @if (!empty($profile->resume_file) && Route::has('hrms.documents.file'))
                                    <a href="{{ route('hrms.documents.file', $profile->resume_file) }}" target="_blank" class="file-link">
                                        <i class="fas fa-eye"></i> View Resume
                                    </a>
                                @else
                                    <div class="profile-value muted">No resume uploaded</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="profile-card-head">
                        <div class="profile-icon"><i class="fas fa-university"></i></div>
                        <div>
                            <h5>Bank Details</h5>
                            <p>Salary account and banking information.</p>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <div class="info-grid">
                            <div class="profile-info">
                                <span class="profile-label">Account Holder</span>
                                <div class="profile-value {{ empty($profile->bank_holder_name) ? 'muted' : '' }}">
                                    {{ $profile->bank_holder_name ?? '-' }}
                                </div>
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Account No</span>
                                <div class="profile-value {{ empty($profile->bank_account_no) ? 'muted' : '' }}">
                                    {{ $profile->bank_account_no ?? '-' }}
                                </div>
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Account Type</span>
                                <div class="profile-value {{ empty($profile->bank_account_type) ? 'muted' : '' }}">
                                    {{ !empty($profile->bank_account_type) ? ucfirst($profile->bank_account_type) : '-' }}
                                </div>
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">IFSC</span>
                                <div class="profile-value {{ empty($profile->ifsc_code) ? 'muted' : '' }}">
                                    {{ $profile->ifsc_code ?? '-' }}
                                </div>
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Bank Branch</span>
                                <div class="profile-value {{ empty($profile->bank_branch) ? 'muted' : '' }}">
                                    {{ $profile->bank_branch ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card review-card">
                <div class="profile-card-head">
                    <div class="profile-icon"><i class="fas fa-user-check"></i></div>
                    <div>
                        <h5>HR Review</h5>
                        <p>Review and update profile status.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="review-status-box">
                        <span class="profile-label">Current Status</span>
                        <div class="status-badge {{ $statusClass }}">
                            <i class="fas fa-circle"></i>
                            {{ $statusText }}
                        </div>
                    </div>

                    <div class="review-box mt-3">
                        <h6>Review Process</h6>
                        <p>
                            Verify the employee’s personal details, education details, bank information and uploaded documents before approving or rejecting this profile.
                            Once approved, the profile will be locked and removed from the pending review list.
                        </p>
                    </div>

                    @if(!empty($profile->rejection_reason))
                        <div class="reject-reason-box">
                            <strong>Rejection Reason</strong>
                            <p>{{ $profile->rejection_reason }}</p>
                        </div>
                    @endif

                    <div class="review-actions">
                        @if($status === 'approved')
                            <div class="alert alert-success rounded-4 mb-0">
                                <strong>Completed:</strong>
                                This profile has already been approved.
                            </div>
                        @else
                            @if(Route::has('hrms.employees.profile.approve'))
                                <form action="{{ route('hrms.employees.profile.approve', $profile->employee_id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn-successx w-100">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Mark as Completed
                                    </button>
                                </form>
                            @endif

                            @if(Route::has('hrms.employees.profile.reject'))
                                <form action="{{ route('hrms.employees.profile.reject', $profile->employee_id) }}" method="POST" class="reject-form">
                                    @csrf
                                    <textarea name="rejection_reason" class="form-control mb-2" rows="3" placeholder="Enter rejection reason (optional)">{{ $profile->rejection_reason ?? '' }}</textarea>

                                    <button type="submit" class="btn-dangerx w-100">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Mark as Rejected
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection