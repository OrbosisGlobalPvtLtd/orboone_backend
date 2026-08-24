@extends('layouts.panel', ['active' => 'reporting_structure'])

@section('page_title', 'Reporting Structure')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

.rep-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.rep-container {
    max-width: 1550px;
    margin: 0 auto;
}

.rep-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 24px 30px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    position: relative;
    overflow: hidden;
}

.rep-hero::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.rep-metric-card {
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 16px 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    gap: 16px;
    height: 100%;
}

.rep-metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.rep-search-box {
    position: relative;
    max-width: 420px;
}
.rep-search-box input {
    padding-left: 42px;
    border-radius: 12px;
    border: 1px solid var(--orb-border);
    height: 44px;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.rep-search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.tree-card {
    background: #ffffff;
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    padding: 22px;
    box-shadow: var(--orb-shadow);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.tree-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 36px rgba(75, 0, 232, 0.14);
    border-color: rgba(75, 0, 232, 0.35);
}

.avatar-initial {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }}, {{ $branding['secondary_color'] ?? '#FF5252' }});
    color: #ffffff;
    font-weight: 700;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 16px rgba(75, 0, 232, 0.22);
    flex-shrink: 0;
}

.emp-list-item {
    padding: 10px 12px;
    border-radius: 12px;
    transition: all 0.15s ease;
    margin-bottom: 6px;
    background: #F8FAFC;
    border: 1px solid #F1F5F9;
}
.emp-list-item:hover {
    background: rgba(75, 0, 232, 0.04);
    border-color: rgba(75, 0, 232, 0.2);
}

.badge-code {
    background: #F1F5F9;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
}
.badge-count {
    background: rgba(75, 0, 232, 0.08);
    color: var(--orb-primary);
    font-size: 12px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid rgba(75, 0, 232, 0.2);
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Banner -->
        <div class="rep-hero d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-3 mb-md-0">
                <h3 class="text-white font-weight-bold mb-1" style="font-size: 24px;">
                    <i class="fas fa-sitemap mr-2"></i>Organization Reporting Structure
                </h3>
                <p class="mb-0 text-white-50" style="font-size: 14px;">
                    Hierarchical breakdown of Reporting Managers and their assigned reporting employees across departments.
                </p>
            </div>
            @if(method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
            <div>
                <a href="{{ route('reporting.assignments') }}" class="btn btn-light font-weight-bold shadow-sm" style="border-radius: 12px; color: var(--orb-primary); padding: 10px 20px;">
                    <i class="fas fa-users-cog mr-2"></i>Manage Assignments
                </a>
            </div>
            @endif
        </div>

        @php
            $totalSupervisors = count($supervisors);
            $totalSubordinates = 0;
            foreach($supervisors as $s) {
                $totalSubordinates += count($s->employees ?? []);
            }
            $avgTeamSize = $totalSupervisors > 0 ? round($totalSubordinates / $totalSupervisors, 1) : 0;
        @endphp

        <!-- Metrics Overview Row -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(75, 0, 232, 0.08); color: var(--orb-primary);">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:0.5px;">Active Reporting Managers</div>
                        <div class="h4 font-weight-bold mb-0 text-dark" style="font-size: 22px;">{{ $totalSupervisors }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(255, 82, 82, 0.08); color: var(--orb-secondary);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:0.5px;">Assigned Reporting Staff</div>
                        <div class="h4 font-weight-bold mb-0 text-dark" style="font-size: 22px;">{{ $totalSubordinates }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(75, 0, 232, 0.08); color: var(--orb-primary);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:0.5px;">Avg Span of Control</div>
                        <div class="h4 font-weight-bold mb-0 text-dark" style="font-size: 22px;">{{ $avgTeamSize }} <span class="small text-muted font-weight-normal">emp / mgr</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="rep-search-box flex-grow-1">
                <i class="fas fa-search"></i>
                <input type="text" id="structureSearch" class="form-control" placeholder="Search manager, employee, or designation...">
            </div>
            <div class="text-muted small">
                Showing <strong id="visibleCardCount">{{ $totalSupervisors }}</strong> Manager Cards
            </div>
        </div>

        <!-- Supervisor Grid Cards -->
        <div class="row" id="supervisorCardsGrid">
            @forelse($supervisors as $sup)
            @php
                $empCount = count($sup->employees);
                $nameParts = explode(' ', trim($sup->supervisor_name ?? ''));
                $initials = strtoupper(substr($nameParts[0] ?? 'M', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
            @endphp
            <div class="col-md-6 col-lg-6 col-xl-4 mb-4 supervisor-card-col" data-search="{{ strtolower($sup->supervisor_name . ' ' . $sup->supervisor_code . ' ' . ($sup->designation_name ?? '') . ' ' . ($sup->department_name ?? '') . ' ' . implode(' ', array_map(fn($e) => $e->display_name . ' ' . ($e->designation_name ?? ''), $sup->employees->toArray()))) }}">
                <div class="tree-card">
                    <!-- Manager Header -->
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="avatar-initial mr-3">
                            {{ $initials ?: 'RM' }}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="text-dark font-weight-bold mb-0 text-truncate" style="font-size: 16px;">
                                    {{ $sup->supervisor_name }}
                                </h6>
                            </div>
                            <div class="mt-1 d-flex align-items-center flex-wrap gap-1">
                                <span class="badge-code mr-1">{{ $sup->supervisor_code }}</span>
                                <small class="text-muted text-truncate" style="max-width: 200px;">
                                    {{ $sup->designation_name ?? 'Reporting Manager' }}
                                </small>
                            </div>
                            @if(!empty($sup->department_name))
                            <div class="mt-1">
                                <span class="badge badge-light text-muted border" style="font-size: 10px;">{{ $sup->department_name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Direct Reports List -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fas fa-sitemap text-primary mr-1" style="color: var(--orb-primary) !important;"></i> Reporting Staff
                        </span>
                        <span class="badge-count">
                            {{ $empCount }} {{ Str::plural('Employee', $empCount) }}
                        </span>
                    </div>

                    <div class="flex-grow-1" style="max-height: 280px; overflow-y: auto;">
                        @forelse($sup->employees as $loopIdx => $emp)
                        @php
                            $subParts = explode(' ', trim($emp->display_name ?? ''));
                            $subInitials = strtoupper(substr($subParts[0] ?? 'E', 0, 1) . substr($subParts[1] ?? '', 0, 1));
                        @endphp
                        <div class="emp-list-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center text-truncate">
                                <span class="badge rounded-circle mr-2 d-flex align-items-center justify-content-center text-white" style="width: 22px; height: 22px; font-size: 10px; font-weight: 700; flex-shrink: 0; background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }}, {{ $branding['secondary_color'] ?? '#FF5252' }});">
                                    {{ $loop->iteration }}
                                </span>
                                <div class="text-truncate">
                                    <strong class="text-dark font-weight-bold d-block text-truncate" style="font-size: 13px;">
                                        {{ $emp->display_name }}
                                    </strong>
                                    <small class="text-muted d-block text-truncate" style="font-size: 11px;">
                                        {{ $emp->designation_name ?? 'Employee' }}
                                    </small>
                                </div>
                            </div>
                            @if(!empty($emp->employee_code))
                            <span class="badge-code ml-2 flex-shrink-0">{{ $emp->employee_code }}</span>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted bg-light rounded-lg">
                            <i class="fas fa-user-slash d-block mb-1 text-muted"></i>
                            <span class="small">No active reporting employees assigned.</span>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 20px;">
                    <div class="mb-3">
                        <span class="p-3 rounded-circle d-inline-flex" style="background: rgba(75, 0, 232, 0.08); color: var(--orb-primary);">
                            <i class="fas fa-sitemap fa-2x"></i>
                        </span>
                    </div>
                    <h5 class="font-weight-bold text-dark">No Reporting Structures Defined Yet</h5>
                    <p class="text-muted small mx-auto" style="max-width: 450px;">
                        Use "Employee Assignments" to map Reporting Managers to employees across departments.
                    </p>
                    @if(method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
                    <div>
                        <a href="{{ route('reporting.assignments') }}" class="btn btn-primary px-4 py-2 font-weight-bold" style="border-radius: 12px; background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }}, {{ $branding['secondary_color'] ?? '#FF5252' }}); border: none;">
                            <i class="fas fa-user-plus mr-2"></i> Assign Reporting Managers
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
    (function() {
        var searchInput = document.getElementById('structureSearch');
        var cardCols = document.querySelectorAll('.supervisor-card-col');
        var countBadge = document.getElementById('visibleCardCount');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var query = this.value.toLowerCase().trim();
                var visibleCount = 0;

                cardCols.forEach(function(col) {
                    var searchData = col.getAttribute('data-search') || '';
                    if (searchData.indexOf(query) !== -1) {
                        col.style.display = '';
                        visibleCount++;
                    } else {
                        col.style.display = 'none';
                    }
                });

                if (countBadge) {
                    countBadge.textContent = visibleCount;
                }
            });
        }
    })();
</script>
@endsection
