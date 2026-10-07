@extends('layouts.panel', ['active' => 'reporting_structure'])

@section('page_title', 'Reporting Structure')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F6F7FB;
    --orb-card: #FFFFFF;
    --orb-border: #E7EAF3;
    --orb-text: #101828;
    --orb-muted: #667085;
    --orb-soft: #F4F2FF;
    --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
}

.rep-page {
    padding: 14px 16px 36px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
    font-family: 'Outfit', sans-serif;
    color: var(--orb-text);
}

.rep-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

/* Signature Hero Header Banner */
.rep-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px;
    padding: 22px 28px;
    margin-bottom: 20px;
    box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    flex-wrap: wrap;
}

.rep-hero::before {
    content: "";
    position: absolute;
    right: -60px;
    top: -80px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.rep-hero-kicker {
    font-size: 11.5px;
    font-weight: 850;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .92;
    margin-bottom: 6px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.rep-hero-title {
    font-size: 26px;
    font-weight: 900;
    margin: 0;
    line-height: 1.15;
    color: #ffffff;
}

.rep-hero-subtitle {
    font-size: 13.5px;
    font-weight: 500;
    margin-top: 6px;
    opacity: .92;
    max-width: 800px;
}

.rep-btn-glass {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.38) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border-radius: 999px !important;
    padding: 8px 18px !important;
    font-size: 12.5px !important;
    font-weight: 750 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    transition: all 0.2s ease !important;
    cursor: pointer;
}

.rep-btn-glass:hover {
    background: rgba(255, 255, 255, 0.32) !important;
    border-color: rgba(255, 255, 255, 0.65) !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

/* Metric KPI Cards */
.rep-metric-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    padding: 18px 22px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    display: flex;
    align-items: center;
    gap: 16px;
    height: 100%;
    transition: all 0.2s ease;
}

.rep-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.rep-metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

/* Toolbar Search Card */
.rep-toolbar-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    padding: 14px 18px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
}

/* Manager Tree Card */
.tree-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 20px;
    padding: 22px;
    box-shadow: var(--orb-shadow);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.tree-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 36px rgba(75, 0, 232, 0.12);
    border-color: rgba(75, 0, 232, 0.3);
}

.avatar-initial {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    color: #ffffff;
    font-weight: 800;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 16px rgba(75, 0, 232, 0.22);
    flex-shrink: 0;
}

.emp-list-item {
    padding: 10px 14px;
    border-radius: 12px;
    transition: all 0.15s ease;
    margin-bottom: 7px;
    background: #F8FAFC;
    border: 1px solid #EEF2F6;
}

.emp-list-item:hover {
    background: rgba(75, 0, 232, 0.04);
    border-color: rgba(75, 0, 232, 0.2);
    transform: translateX(2px);
}

.badge-code {
    background: #EEF2F6;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    font-family: inherit;
}

.badge-count {
    background: rgba(75, 0, 232, 0.08);
    color: var(--orb-primary);
    font-size: 11.5px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid rgba(75, 0, 232, 0.18);
}

/* Custom Scrollbar */
.custom-scroll::-webkit-scrollbar {
    width: 5px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: #F1F5F9;
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: var(--orb-muted);
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header -->
        <div class="rep-hero">
            <div>
                <div class="rep-hero-kicker">
                    <i class="fas fa-sitemap"></i> Organization Hierarchy
                </div>
                <h1 class="rep-hero-title">Organization Reporting Structure</h1>
                <p class="rep-hero-subtitle">
                    Hierarchical breakdown of Reporting Managers and their assigned reporting employees across departments.
                </p>
            </div>
            @if(method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
            <div>
                <a href="{{ route('reporting.assignments') }}" class="rep-btn-glass">
                    <i class="fas fa-users-cog"></i> Manage Assignments
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
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:0.5px; font-size: 11px;">Active Reporting Managers</div>
                        <div class="h4 font-weight-bold mb-0 text-dark" style="font-size: 24px;">{{ $totalSupervisors }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(255, 82, 82, 0.08); color: var(--orb-secondary);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:0.5px; font-size: 11px;">Assigned Reporting Staff</div>
                        <div class="h4 font-weight-bold mb-0 text-dark" style="font-size: 24px;">{{ $totalSubordinates }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(75, 0, 232, 0.08); color: var(--orb-primary);">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="letter-spacing:0.5px; font-size: 11px;">Average Team Size</div>
                        <div class="h4 font-weight-bold mb-0 text-dark" style="font-size: 24px;">{{ $avgTeamSize }} <span class="small text-muted font-weight-normal" style="font-size: 13px;">Members / Manager</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter Toolbar Card -->
        <div class="rep-toolbar-card">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-7 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px; border-color: var(--orb-border); color: var(--orb-muted);">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text" id="structureSearch" class="form-control border-left-0 border-right-0" style="border-color: var(--orb-border); height: 42px; font-size: 13.5px; font-weight: 500;" placeholder="Search manager, employee, designation, department, ID...">
                        <div class="input-group-append">
                            <button type="button" id="searchBtn" class="btn text-white font-weight-bold px-3 d-flex align-items-center" style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); border: none;">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                            <button type="button" id="resetBtn" class="btn btn-light px-3 d-flex align-items-center" title="Reset Search" style="border: 1px solid var(--orb-border); border-radius: 0 10px 10px 0; color: var(--orb-muted);">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-5 d-flex justify-content-md-end align-items-center">
                    <span class="badge px-3 py-2" style="background: var(--orb-soft); color: var(--orb-primary); font-size: 13px; font-weight: 700; border-radius: 50px; border: 1px solid rgba(75, 0, 232, 0.15);">
                        <i class="fas fa-id-badge mr-1"></i> Showing <strong id="visibleCardCount" class="mx-1">{{ $totalSupervisors }}</strong> of {{ $totalSupervisors }} Manager Cards
                    </span>
                </div>
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
            <div class="col-md-6 col-lg-6 col-xl-4 mb-4 supervisor-card-col" data-search="{{ strtolower($sup->supervisor_name . ' ' . $sup->supervisor_code . ' ' . ($sup->designation_name ?? '') . ' ' . ($sup->department_name ?? '') . ' ' . implode(' ', array_map(fn($e) => $e->display_name . ' ' . ($e->designation_name ?? '') . ' ' . ($e->employee_code ?? ''), $sup->employees->toArray()))) }}">
                <div class="tree-card">
                    <!-- Manager Header -->
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom" style="border-color: var(--orb-border) !important;">
                        <div class="avatar-initial mr-3">
                            {{ $initials ?: 'RM' }}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="text-dark font-weight-bold mb-1 text-truncate" style="font-size: 16px;">
                                {{ $sup->supervisor_name }}
                            </h6>
                            <div class="d-flex align-items-center flex-wrap gap-1">
                                <span class="badge-code mr-1">{{ $sup->supervisor_code }}</span>
                                <span class="text-muted small text-truncate font-weight-500" style="max-width: 180px;">
                                    {{ $sup->designation_name ?? 'Reporting Manager' }}
                                </span>
                            </div>
                            @if(!empty($sup->department_name))
                            <div class="mt-1">
                                <span class="badge badge-light text-muted border px-2 py-0.5" style="font-size: 10.5px; font-weight: 600; border-radius: 4px;">{{ $sup->department_name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Direct Reports List Header -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fas fa-sitemap mr-1" style="color: var(--orb-primary);"></i> Reporting Staff
                        </span>
                        <span class="badge-count">
                            {{ $empCount }} {{ Str::plural('Employee', $empCount) }}
                        </span>
                    </div>

                    <div class="flex-grow-1 custom-scroll mb-3" style="max-height: 240px; overflow-y: auto; padding-right: 2px;">
                        @forelse($sup->employees as $loopIdx => $emp)
                        <div class="emp-list-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center text-truncate mr-2">
                                <span class="badge rounded-circle mr-2 d-flex align-items-center justify-content-center text-white" style="width: 22px; height: 22px; font-size: 10px; font-weight: 700; flex-shrink: 0; background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));">
                                    {{ $loop->iteration }}
                                </span>
                                <div class="text-truncate">
                                    <strong class="text-dark font-weight-bold d-block text-truncate" style="font-size: 13px;">
                                        {{ $emp->display_name }}
                                    </strong>
                                    <small class="text-muted d-block text-truncate" style="font-size: 11px; font-weight: 500;">
                                        {{ $emp->designation_name ?? 'Employee' }}
                                    </small>
                                </div>
                            </div>
                            @if(!empty($emp->employee_code))
                            <span class="badge-code flex-shrink-0">{{ $emp->employee_code }}</span>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted bg-light rounded-lg">
                            <i class="fas fa-user-slash d-block mb-1 text-muted"></i>
                            <span class="small font-weight-500">No active reporting employees assigned.</span>
                        </div>
                        @endforelse
                    </div>

                    @if(method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
                    <div class="pt-2 border-top" style="border-color: var(--orb-border) !important;">
                        <a href="{{ route('reporting.assignments') }}" class="btn btn-sm btn-light text-primary w-100 font-weight-bold d-flex align-items-center justify-content-center" style="border-radius: 8px; font-size: 12px; border: 1px solid var(--orb-border); height: 32px;">
                            <i class="fas fa-user-edit mr-1"></i> Edit Assignments
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 20px; background: #FFFFFF;">
                    <div class="mb-3">
                        <span class="p-3 rounded-circle d-inline-flex" style="background: var(--orb-soft); color: var(--orb-primary); font-size: 24px;">
                            <i class="fas fa-sitemap"></i>
                        </span>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1">No Reporting Structures Defined Yet</h5>
                    <p class="text-muted small mx-auto mb-3" style="max-width: 450px;">
                        Use "Employee Assignments" to map Reporting Managers to employees across departments.
                    </p>
                    @if(method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin())
                    <div>
                        <a href="{{ route('reporting.assignments') }}" class="btn text-white px-4 py-2 font-weight-bold" style="border-radius: 50px; background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); border: none; box-shadow: 0 4px 15px rgba(75, 0, 232, 0.2);">
                            <i class="fas fa-user-plus mr-1"></i> Assign Reporting Managers
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
        var searchBtn = document.getElementById('searchBtn');
        var resetBtn = document.getElementById('resetBtn');
        var cardCols = document.querySelectorAll('.supervisor-card-col');
        var countBadge = document.getElementById('visibleCardCount');

        function performSearch() {
            if (!searchInput) return;
            var query = searchInput.value.toLowerCase().trim();
            var visibleCount = 0;

            cardCols.forEach(function(col) {
                var searchData = col.getAttribute('data-search') || '';
                if (!query || searchData.indexOf(query) !== -1) {
                    col.style.display = '';
                    visibleCount++;
                } else {
                    col.style.display = 'none';
                }
            });

            if (countBadge) {
                countBadge.textContent = visibleCount;
            }
        }

        if (searchInput) {
            // Live filter on type
            searchInput.addEventListener('input', performSearch);
            // On Enter key press
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });
        }

        if (searchBtn) {
            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                performSearch();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (searchInput) {
                    searchInput.value = '';
                }
                performSearch();
            });
        }
    })();
</script>
@endsection
