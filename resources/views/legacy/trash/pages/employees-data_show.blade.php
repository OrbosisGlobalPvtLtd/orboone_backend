@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'employees'])

@section('_content')
<style>
    :root {
        --orb-1: #4b00e8;
        --orb-2: #8600ee;
        --orb-3: #d400d5;
        --orb-4: #ec4e74;
        --orb-5: #ffb101;
    }

    body { background-color: #f8fafc !important; }

    /* ── Profile Cover ── */
    .profile-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.03);
        margin-bottom: 2.5rem;
    }
    .profile-cover {
        background: linear-gradient(135deg, var(--orb-1) 0%, var(--orb-2) 50%, var(--orb-3) 100%);
        height: 180px;
        position: relative;
        overflow: hidden;
    }
    .profile-cover::after {
        content: "";
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.08) 0%, transparent 60%),
                          radial-gradient(circle at 80% 20%, rgba(255,255,255,0.06) 0%, transparent 50%);
    }
    .profile-header-meta { padding: 0 40px 30px 40px; }

    .profile-avatar-wrapper { margin-top: -80px; position: relative; z-index: 10; }
    .profile-avatar-img {
        width: 160px;
        height: 160px;
        border-radius: 30px;
        border: 7px solid white;
        object-fit: cover;
        background: #f1f5f9;
        box-shadow: 0 20px 40px rgba(75, 0, 232, 0.18);
    }

    /* ── Info Items ── */
    .info-label {
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        font-size: 0.64rem;
        letter-spacing: 1.5px;
        margin-bottom: 0.5rem;
        display: block;
    }
    .info-value {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.97rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-value i { color: var(--orb-1); opacity: 0.75; font-size: 1rem; flex-shrink: 0; }
    .info-group { margin-bottom: 1.8rem; }

    /* ── Status Pill ── */
    .status-pill {
        padding: 7px 18px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.72rem;
        letter-spacing: 0.8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* ── Cards ── */
    .card-luxury {
        border: none;
        border-radius: 22px;
        background: white;
        box-shadow: 0 8px 25px rgba(0,0,0,0.03);
        margin-bottom: 1.8rem;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .card-luxury .card-header {
        background: transparent;
        padding: 22px 28px 10px 28px;
        border: none;
    }
    .card-luxury .card-header h5 {
        font-size: 1rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .card-luxury .card-header h5 i { color: var(--orb-1); font-size: 1rem; }
    .card-luxury .card-body { padding: 18px 28px 28px 28px; }

    /* ── Buttons ── */
    .btn-brand {
        background: linear-gradient(45deg, var(--orb-1), var(--orb-2));
        color: white !important;
        border: none;
        border-radius: 12px;
        padding: 10px 22px;
        font-weight: 700;
        font-size: 0.875rem;
        box-shadow: 0 8px 18px rgba(75, 0, 232, 0.22);
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-brand:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(75, 0, 232, 0.3); }

    /* ── Soft Badges ── */
    .badge-success-soft { background: rgba(22, 163, 74, 0.1); color: #16a34a; border-radius: 8px; font-weight: 700; }
    .badge-warning-soft { background: rgba(217, 119, 6, 0.1); color: #d97706; border-radius: 8px; font-weight: 700; }
    .badge-danger-soft  { background: rgba(220, 38, 38, 0.1);  color: #dc2626; border-radius: 8px; font-weight: 700; }
    .badge-info-soft    { background: rgba(14, 165, 233, 0.1);  color: #0ea5e9; border-radius: 8px; font-weight: 700; }

    /* ── Manager / Subordinate Links ── */
    .manager-link {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        text-decoration: none !important;
        color: #1e293b;
        transition: all 0.25s;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .manager-link:hover { background: #f8f9ff; border-color: var(--orb-1); box-shadow: 0 4px 15px rgba(75,0,232,0.1); }
    .manager-link img { width: 45px; height: 45px; border-radius: 14px; object-fit: cover; border: 2px solid #f1f5f9; margin-right: 12px; flex-shrink: 0; }

    /* ── Asset Item ── */
    .asset-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .asset-item:last-child { border-bottom: none; padding-bottom: 0; }
    .asset-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    /* ── Document Card ── */
    .document-card {
        background: #f8fafc;
        border-radius: 18px;
        padding: 20px;
        border: 1px solid #e2e8f0;
    }

    /* ── Typography Helper ── */
    .extra-small { font-size: 0.72rem; }

    /* ── Section Divider Label ── */
    .section-sub {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #94a3b8;
        margin-bottom: 1.2rem;
        display: block;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
    }

    /* ── Responsive ── */
    @media (max-width: 991px) {
        .profile-header-meta { padding: 0 20px 25px 20px; text-align: center; }
        .profile-avatar-wrapper { margin-top: -65px; }
        .profile-avatar-img { width: 130px; height: 130px; border-radius: 24px; }
        .info-value { justify-content: center; }
        .profile-header-flex { flex-direction: column !important; align-items: center !important; }
        .meta-dates { text-align: center !important; margin-top: 1.5rem; }
    }
    @media (max-width: 576px) {
        .card-luxury .card-body { padding: 16px 18px 20px 18px; }
        .profile-header-meta { padding: 0 15px 20px 15px; }
        .btn-brand { padding: 10px 16px; font-size: 0.8rem; }
    }
</style>

<div class="container-fluid py-4 px-md-5">

    {{-- ── Page Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="font-weight-bold text-dark m-0">
                <i class="fas fa-id-badge mr-2" style="color: var(--orb-1);"></i> Employee Portfolio
            </h4>
            <p class="text-muted small m-0 mt-1">Complete profile, credentials & professional record.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('employees-data.edit', $employee->id) }}" class="btn-brand">
                <i class="fas fa-pen-nib"></i> Edit Record
            </a>
            <a href="{{ route('employees-data') }}" class="btn btn-light border shadow-sm" style="border-radius: 12px; font-weight: 600; padding: 10px 20px;">
                <i class="fas fa-arrow-left mr-2 text-muted"></i> Directory
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 mb-4" style="border-radius: 14px; background: #f0fff4; color: #22543d;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
        </div>
    @endif

    {{-- ── Profile Hero Card ── --}}
    <div class="profile-card">
        <div class="profile-cover"></div>
        <div class="profile-header-meta">
            <div class="d-flex profile-header-flex align-items-end mb-4" style="gap: 24px;">

                {{-- Avatar --}}
                <div class="profile-avatar-wrapper" style="flex-shrink: 0;">
                    @php
                        $photoPath = $employee->employeeDetail->photo ?? 'images/profile.png';
                        $photoPath = str_replace('public/', '', $photoPath);
                        if (Str::startsWith($photoPath, 'http')) {
                            $finalUrl = $photoPath;
                        } elseif (Str::startsWith($photoPath, 'uploads/')) {
                            $finalUrl = asset($photoPath);
                        } elseif ($photoPath && !in_array($photoPath, ['default_avatar.png', 'images/profile.png'])) {
                            $finalUrl = asset('storage/' . $photoPath);
                        } else {
                            $finalUrl = asset('images/profile.png');
                        }
                    @endphp
                    <img src="{{ $finalUrl }}"
                         class="profile-avatar-img"
                         onerror="this.src='{{ asset('images/profile.png') }}'; this.onerror=null;"
                         alt="Avatar">
                </div>

                {{-- Name & Badges --}}
                <div class="flex-grow-1">
                    <h1 class="font-weight-bold text-dark mb-1" style="font-size: 2rem; letter-spacing: -0.5px;">{{ $employee->name }}</h1>
                    <div class="d-flex align-items-center mb-3 flex-wrap" style="gap: 10px;">
                        <span class="badge border py-2 px-3 font-weight-bold" style="border-radius: 10px; color: var(--orb-1); background: rgba(75,0,232,0.07); font-size: 0.75rem;">
                            <i class="fas fa-hashtag mr-1"></i> {{ $employee->employee_id }}
                        </span>
                        <span class="text-muted font-weight-bold small">
                            <i class="fas fa-briefcase mr-1 text-primary"></i> {{ $employee->position->name ?? 'Unassigned' }}
                        </span>
                        <span class="text-muted font-weight-bold small">
                            <i class="fas fa-network-wired mr-1 text-info"></i> {{ $employee->department->name ?? 'Pending' }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 8px;">
                        @php
                            $statusBg = 'rgba(22,163,74,0.1)'; $statusColor = '#16a34a';
                            if($employee->status == 'Probation') { $statusBg='rgba(217,119,6,0.1)'; $statusColor='#d97706'; }
                            if($employee->status == 'Inactive')  { $statusBg='rgba(220,38,38,0.1)';  $statusColor='#dc2626'; }
                            if($employee->status == 'Completed') { $statusBg='rgba(14,165,233,0.1)';  $statusColor='#0ea5e9'; }
                        @endphp
                        <span class="status-pill" style="background: {{ $statusBg }}; color: {{ $statusColor }}; border: 1.5px solid {{ $statusColor }}40;">
                            <i class="fas fa-circle" style="font-size: 8px;"></i> {{ $employee->status }}
                        </span>
                        <span class="status-pill" style="background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0;">
                            <i class="fas fa-user-tie" style="font-size: 8px;"></i> {{ $employee->employment_type }}
                        </span>
                    </div>
                </div>

                {{-- Contract Dates --}}
                <div class="meta-dates" style="min-width: 230px;">
                    <div class="row no-gutters">
                        <div class="col-6 px-2">
                            <span class="info-label">Start Date</span>
                            <div class="info-value" style="font-size: 0.88rem;">
                                <i class="fas fa-calendar-check" style="font-size: 0.85rem;"></i>
                                {{ \Carbon\Carbon::parse($employee->start_of_contract)->format('d M, Y') }}
                            </div>
                        </div>
                        <div class="col-6 px-2" style="border-left: 1px solid #f1f5f9;">
                            <span class="info-label">{{ $employee->employment_type == 'Intern' ? 'Internship End' : 'Contract End' }}</span>
                            <div class="info-value" style="font-size: 0.88rem;">
                                <i class="fas fa-hourglass-end" style="font-size: 0.85rem;"></i>
                                {{ $employee->employment_type == 'Intern' && $employee->internship_end_date
                                    ? \Carbon\Carbon::parse($employee->internship_end_date)->format('d M, Y')
                                    : \Carbon\Carbon::parse($employee->end_of_contract)->format('d M, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ── Left: Main Content ── --}}
        <div class="col-lg-8">

            {{-- Biographical Info --}}
            <div class="card card-luxury">
                <div class="card-header">
                    <h5><i class="fas fa-id-card-alt"></i> Biographical Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 info-group">
                            <span class="info-label">Email Address</span>
                            <div class="info-value"><i class="fas fa-envelope-open"></i> {{ $employee->user->email }}</div>
                        </div>
                        <div class="col-md-4 info-group">
                            <span class="info-label">Mobile Number</span>
                            <div class="info-value"><i class="fas fa-phone-alt"></i> {{ $employee->employeeDetail->phone ?? 'Unlisted' }}</div>
                        </div>
                        <div class="col-md-4 info-group">
                            <span class="info-label">Emergency Contact</span>
                            <div class="info-value"><i class="fas fa-phone-square-alt" style="color: #dc2626;"></i> {{ $employee->employeeDetail->emergency_contact_number ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-4 info-group">
                            <span class="info-label">Gender</span>
                            <div class="info-value">
                                <i class="fas fa-user-circle"></i>
                                @if($employee->employeeDetail->gender == 'M') Male
                                @elseif($employee->employeeDetail->gender == 'F') Female
                                @else Other @endif
                            </div>
                        </div>
                        <div class="col-md-4 info-group">
                            <span class="info-label">Date of Birth</span>
                            <div class="info-value">
                                <i class="fas fa-birthday-cake"></i>
                                {{ \Carbon\Carbon::parse($employee->employeeDetail->date_of_birth)->format('d M, Y') }}
                            </div>
                        </div>
                        <div class="col-md-4 info-group">
                            <span class="info-label">Work Mode</span>
                            <div class="info-value">
                                @if($employee->employee_status == 'WFH')
                                    <i class="fas fa-house-user" style="color: #0284c7;"></i>
                                    <span style="color: #0284c7;">Work From Home</span>
                                @else
                                    <i class="fas fa-building" style="color: #16a34a;"></i>
                                    <span style="color: #16a34a;">Work From Office</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 info-group mb-0">
                            <span class="info-label">Residential Address</span>
                            <div class="info-value"><i class="fas fa-map-marked-alt"></i> {{ $employee->employeeDetail->address ?? 'Not provided' }}</div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4" style="border-top: 1px solid #f1f5f9;">
                        <span class="section-sub"><i class="fas fa-graduation-cap mr-2"></i> Academic & Experience</span>
                        <div class="row">
                            <div class="col-md-4 info-group">
                                <span class="info-label">Education Level</span>
                                <div class="info-value"><i class="fas fa-university"></i> {{ $employee->employeeDetail->last_education ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4 info-group">
                                <span class="info-label">Academic GPA</span>
                                <div class="info-value"><i class="fas fa-award"></i> {{ $employee->employeeDetail->gpa ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4 info-group">
                                <span class="info-label">Work Experience</span>
                                <div class="info-value"><i class="fas fa-history"></i> {{ $employee->employeeDetail->work_experience_in_years ?? '0' }} Years</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subordinates --}}
            @if($employee->subordinates->count() > 0)
            <div class="card card-luxury">
                <div class="card-header">
                    <h5><i class="fas fa-sitemap"></i> Direct Reportees ({{ $employee->subordinates->count() }})</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">Team members directly reporting to this employee.</p>
                    <div class="row">
                        @foreach($employee->subordinates as $sub)
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('employees-data.show', $sub->id) }}" class="manager-link">
                                @php
                                    $subPhoto = $sub->employeeDetail->photo ?? 'images/profile.png';
                                    $subPhoto = str_replace('public/', '', $subPhoto);
                                    $subFinalUrl = Str::startsWith($subPhoto, 'http') ? $subPhoto : (Str::startsWith($subPhoto, 'uploads/') ? asset($subPhoto) : asset('images/profile.png'));
                                @endphp
                                <img src="{{ $subFinalUrl }}" onerror="this.src='{{ asset('images/profile.png') }}'; this.onerror=null;" alt="{{ $sub->name }}">
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $sub->name }}</div>
                                    <div class="extra-small text-muted">{{ $sub->employee_id }}</div>
                                    <div class="extra-small text-primary mt-1 font-weight-bold">{{ $sub->position->name ?? 'Specialist' }}</div>
                                </div>
                                <i class="fas fa-chevron-right text-muted small ml-2"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Employment Parameters --}}
            @if(in_array($employee->employment_type, ['Intern', 'Full-Time']))
            <div class="card card-luxury">
                <div class="card-header">
                    <h5><i class="fas fa-sliders-h"></i> Employment Parameters</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($employee->employment_type == 'Intern')
                            <div class="col-md-4 info-group">
                                <span class="info-label">Internship Type</span>
                                <div class="info-value"><i class="fas fa-tags"></i> {{ $employee->internship_type ?? 'Standard' }}</div>
                            </div>
                            <div class="col-md-4 info-group">
                                <span class="info-label">Duration</span>
                                <div class="info-value"><i class="fas fa-clock"></i> {{ $employee->internship_duration ?? 0 }} Months</div>
                            </div>
                            <div class="col-md-4 info-group">
                                <span class="info-label">End Date</span>
                                <div class="info-value">
                                    <i class="fas fa-calendar-times" style="color: #dc2626;"></i>
                                    <span style="color: #dc2626;">{{ $employee->internship_end_date ? \Carbon\Carbon::parse($employee->internship_end_date)->format('d M, Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        @else
                            <div class="col-md-4 info-group">
                                <span class="info-label">Engagement Status</span>
                                <div class="info-value">
                                    <span class="badge {{ $employee->is_permanent ? 'badge-success-soft' : 'badge-warning-soft' }} px-3 py-2">
                                        <i class="fas {{ $employee->is_permanent ? 'fa-check-circle' : 'fa-hourglass-half' }} mr-1"></i>
                                        {{ $employee->is_permanent ? 'Permanent' : 'Probationary' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 info-group">
                                <span class="info-label">Probation Extension</span>
                                <div class="info-value"><i class="fas fa-plus-circle"></i> {{ $employee->probation_extension ? $employee->probation_extension . ' Months' : 'None' }}</div>
                            </div>
                            <div class="col-md-4 info-group">
                                <span class="info-label">Evaluation Date</span>
                                <div class="info-value">
                                    <i class="fas fa-calendar-check" style="color: var(--orb-1);"></i>
                                    <span style="color: var(--orb-1);">{{ $employee->probation_end_date ? \Carbon\Carbon::parse($employee->probation_end_date)->format('d M, Y') : 'Not Scheduled' }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- ── Right Sidebar ── --}}
        <div class="col-lg-4">

            {{-- Supervision Channel --}}
            <div class="card card-luxury p-4">
                <span class="section-sub"><i class="fas fa-sitemap mr-2"></i> Supervision Channel</span>
                <div class="info-group">
                    <span class="info-label">Reporting Manager</span>
                    @if($employee->manager)
                        <a href="{{ route('employees-data.show', $employee->manager->id) }}" class="manager-link">
                            @php
                                $mgrPhoto = $employee->manager->employeeDetail->photo ?? 'images/profile.png';
                                $mgrPhoto = str_replace('public/', '', $mgrPhoto);
                                $mgrUrl = Str::startsWith($mgrPhoto, 'http') ? $mgrPhoto : (Str::startsWith($mgrPhoto, 'uploads/') ? asset($mgrPhoto) : asset('images/profile.png'));
                            @endphp
                            <img src="{{ $mgrUrl }}" onerror="this.src='{{ asset('images/profile.png') }}'; this.onerror=null;" alt="{{ $employee->manager->name }}">
                            <div>
                                <div class="font-weight-bold text-dark">{{ $employee->manager->name }}</div>
                                <div class="extra-small text-muted">{{ $employee->manager->position->name ?? 'Manager' }}</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted small ml-auto"></i>
                        </a>
                    @else
                        <div class="p-3 bg-light rounded text-muted small text-center font-weight-bold" style="border-radius: 14px;">
                            <i class="fas fa-user-shield mr-2"></i> Independent Contributor
                        </div>
                    @endif
                </div>
                <div class="info-group mb-0">
                    <span class="info-label">Work Location</span>
                    <div class="p-3 d-flex align-items-center rounded font-weight-bold" style="background: #f1f5f9; border-radius: 14px; gap: 10px; font-size: 0.9rem;">
                        @if($employee->employee_status == 'WFH')
                            <i class="fas fa-house-user" style="color: #0284c7; font-size: 1.2rem;"></i>
                            <span style="color: #0284c7;">Remote (Work From Home)</span>
                        @else
                            <i class="fas fa-building" style="color: #16a34a; font-size: 1.2rem;"></i>
                            <span style="color: #16a34a;">Office (Work From Office)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Asset Allocations --}}
            <div class="card card-luxury p-4">
                <div class="d-flex align-items-center justify-content-between mb-3" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                    <span class="section-sub mb-0" style="border: none;"><i class="fas fa-boxes mr-2"></i> Inventory Custody</span>
                    <a href="{{ route('asset-allocations.create') }}" class="btn btn-sm btn-brand" style="padding: 6px 14px; font-size: 0.75rem; box-shadow: none;">
                        <i class="fas fa-plus"></i> Assign
                    </a>
                </div>
                @if(isset($employee->assetAllocations) && $employee->assetAllocations->count() > 0)
                    @foreach($employee->assetAllocations as $asset)
                    <div class="asset-item">
                        <div class="asset-icon">
                            @if($asset->asset_type == 'Laptop')     <i class="fas fa-laptop text-primary"></i>
                            @elseif($asset->asset_type == 'Mobile') <i class="fas fa-mobile-alt text-info"></i>
                            @elseif($asset->asset_type == 'ID Card')<i class="fas fa-id-badge text-success"></i>
                            @else                                    <i class="fas fa-box text-secondary"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="font-weight-bold text-dark" style="font-size: 0.88rem;">{{ $asset->asset_type }}</div>
                            <div class="extra-small text-muted mt-1">Assigned {{ \Carbon\Carbon::parse($asset->assigned_date)->format('d M, Y') }}</div>
                        </div>
                        <span class="badge {{ $asset->status == 'Active' ? 'badge-success-soft' : 'badge-danger-soft' }} px-3 py-2" style="font-size: 0.7rem;">
                            {{ $asset->status }}
                        </span>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4 rounded-lg" style="background: #f8fafc; border-radius: 16px;">
                        <i class="fas fa-box-open fa-2x text-muted mb-2 d-block" style="opacity: 0.35;"></i>
                        <p class="text-muted small font-weight-bold mb-0">No active assets assigned</p>
                    </div>
                @endif
            </div>

            {{-- Documents / CV --}}
            <div class="card card-luxury p-4">
                <span class="section-sub"><i class="fas fa-file-alt mr-2"></i> Credential Documents</span>
                @if($employee->employeeDetail->cv)
                    @php
                        $cvPath = $employee->employeeDetail->cv;
                        $cvPath = str_replace('public/', '', $cvPath);
                        $cvUrl = Str::startsWith($cvPath, 'http') ? $cvPath : (Str::startsWith($cvPath, 'uploads/') ? asset($cvPath) : asset('storage/' . $cvPath));
                    @endphp
                    <div class="document-card">
                        <div class="d-flex align-items-center mb-4">
                            <div class="asset-icon" style="background: rgba(220,38,38,0.08); width: 52px; height: 52px; border-radius: 14px; margin-right: 14px;">
                                <i class="fas fa-file-pdf" style="color: #dc2626; font-size: 1.4rem;"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">Personnel Resume</div>
                                <div class="extra-small text-muted mt-1"><i class="fas fa-shield-check mr-1 text-success"></i> Verified Document</div>
                            </div>
                        </div>
                        <div class="d-flex" style="gap: 10px;">
                            <a href="{{ $cvUrl }}" target="_blank" class="btn-brand flex-grow-1 justify-content-center" style="box-shadow:none; border-radius: 12px; padding: 9px 0; text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ $cvUrl }}" download class="btn btn-light border flex-grow-1 font-weight-bold" style="border-radius: 12px; font-size: 0.85rem;">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4" style="background: #f8fafc; border-radius: 16px;">
                        <i class="fas fa-folder-open fa-2x text-muted mb-2 d-block" style="opacity: 0.35;"></i>
                        <p class="text-muted small font-weight-bold mb-0">No documents uploaded</p>
                    </div>
                @endif
            </div>

            {{-- Danger Zone --}}
            <div class="card card-luxury p-4" style="background: #1e293b; border-radius: 22px;">
                <h6 class="font-weight-bold mb-2 small" style="letter-spacing: 2px; color: rgba(255,255,255,0.6);">
                    <i class="fas fa-exclamation-triangle mr-2 text-danger"></i> ADMINISTRATIVE SAFEGUARDS
                </h6>
                <p class="mb-4" style="font-size: 0.78rem; color: rgba(255,255,255,0.45); line-height: 1.6;">
                    Deleting this record is irreversible. All associated logs, system access, and data will be permanently removed.
                </p>
                <form action="{{ route('employees-data.destroy', $employee->id) }}" method="POST"
                      onsubmit="return confirm('⚠️ DANGER: This will permanently delete all records and login access. Are you sure?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-block font-weight-bold py-3"
                            style="border-radius: 14px; border-width: 2px; letter-spacing: 1px; font-size: 0.75rem;">
                        <i class="fas fa-user-slash mr-2"></i> TERMINATE RECORD
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection