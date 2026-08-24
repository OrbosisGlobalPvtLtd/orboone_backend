@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Team Leave Calendar')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: rgba(75, 0, 232, 0.06);
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.prj-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.prj-container {
    max-width: 1550px;
    margin: 0 auto;
}

.prj-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.prj-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    overflow: hidden;
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-calendar-alt mr-2"></i>Team Leave Calendar</h1>
            <p class="mb-0 opacity-90">Resource planning view of team member leave applications and schedules.</p>
        </div>

        <!-- Filter Card -->
        <div class="prj-card p-4 mb-4">
            <form method="GET" action="{{ route('projects.team.leave') }}" class="form-row align-items-center">
                <div class="col-md-6 my-1">
                    <label class="small font-weight-bold text-muted">Leave Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All Statuses --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-6 my-1 text-right align-self-end">
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('projects.team.leave') }}" class="btn btn-light border px-3 ml-1" style="border-radius: 10px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Leave Requests Table -->
        <div class="prj-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Employee</th>
                            <th>HR Designation</th>
                            <th>Leave Type</th>
                            <th>Leave Duration</th>
                            <th>Requested Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr>
                            <td>
                                <strong class="text-dark">{{ optional($req->employee)->display_name }}</strong>
                                <div class="small text-muted">{{ optional($req->employee)->employee_code }}</div>
                            </td>
                            <td><span class="small text-muted">{{ optional(optional($req->employee)->designation)->name ?? 'N/A' }}</span></td>
                            <td><span class="badge badge-light border font-weight-bold">{{ optional($req->leaveType)->name ?? 'Leave' }}</span></td>
                            <td>
                                <span class="small font-weight-bold text-dark">
                                    {{ $req->start_date ? $req->start_date->format('d M Y') : 'N/A' }}
                                    &rarr;
                                    {{ $req->end_date ? $req->end_date->format('d M Y') : 'N/A' }}
                                </span>
                            </td>
                            <td><span class="small font-weight-bold text-primary">{{ $req->requested_days }} days</span></td>
                            <td>
                                <span class="badge badge-{{ $req->status == 'approved' ? 'success' : ($req->status == 'pending' ? 'warning' : 'danger') }} text-uppercase px-2 py-1">
                                    {{ $req->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">No team leave records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
