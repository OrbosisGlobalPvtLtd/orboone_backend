@extends('hrms.shared.crud-index')

@section('_head')
@parent
<style>
    .weekoff-grid-wrapper {
        margin-bottom: 24px;
    }

    .weekoff-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        align-items: start;
    }

    .weekoff-day-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        box-shadow: var(--orb-shadow);
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        height: auto !important;
        min-height: auto !important;
        align-self: start;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }

    .weekoff-day-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 24, 40, .08);
    }

    .weekoff-day-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--orb-primary);
        border-top-left-radius: 18px;
        border-top-right-radius: 18px;
    }

    /* Red indicator if it contains off days */
    .weekoff-day-card.has-off::before {
        background: #ef4444;
    }

    /* Green indicator if exclusively working */
    .weekoff-day-card.exclusively-working::before {
        background: #10b981;
    }

    .day-name-title {
        font-size: 14px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 4px 0 10px;
        text-transform: capitalize;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--orb-border);
        padding-bottom: 8px;
    }

    .rules-inner-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
    }

    /* Saturday specific layout */
    .weekoff-day-card.saturday-card {
        grid-column: span 2;
    }

    .weekoff-day-card.saturday-card .rules-inner-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        width: 100%;
    }

    @media (max-width: 1200px) {
        .weekoff-day-card.saturday-card {
            grid-column: span 1;
        }
        .weekoff-day-card.saturday-card .rules-inner-list {
            grid-template-columns: 1fr;
        }
    }

    .rule-item-box {
        border-radius: 10px;
        border: 1px solid var(--orb-border);
        padding: 8px 10px;
        background: #fafafa;
        font-size: 11px;
        position: relative;
        transition: background 0.15s ease;
    }

    .rule-item-box:hover {
        background: #f5f5f5;
    }

    .rule-status-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
        gap: 6px;
    }

    .week-lbl {
        font-size: 9px;
        font-weight: 850;
        color: var(--orb-primary);
        background: var(--orb-soft);
        padding: 2px 6px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .rule-date-range {
        font-size: 9px;
        color: var(--orb-muted);
        font-weight: 750;
        margin-top: 4px;
        display: block;
    }

    .empty-day-lbl {
        font-size: 11px;
        color: var(--orb-muted);
        font-style: italic;
        text-align: center;
        margin: auto;
        padding: 16px 0;
        font-weight: 600;
    }
</style>
@endsection

@section('_content')
<div class="orb-page">

    <!-- Premium Purple Gradient Hero Header -->
    <div class="orb-hero">
        <div class="orb-hero-content">
            <div class="orb-hero-kicker">
                <i class="fas fa-layer-group"></i> HRMS &bull; LEAVE MANAGEMENT
            </div>
            <h1>Weekoff Rules</h1>
            <p>
                Configure weekly offs, working Saturdays, and recurring off patterns.
            </p>
        </div>

        @if(!empty($canCreate))
        <div class="orb-hero-actions">
            <button type="button" class="orb-btn orb-btn-primary" data-toggle="modal" data-target="#createModal">
                <i class="fas fa-plus"></i> Add Weekoff Rule
            </button>
        </div>
        @endif
    </div>

    @if(session('success') || session('status'))
    <div class="alert alert-success border-0 shadow-sm">
        {{ session('success') ?: session('status') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        {{ $errors->first() }}
    </div>
    @endif

    <!-- Dynamic Metric Summary Cards -->
    @php
        $totalRules = method_exists($rows, 'total') ? $rows->total() : count($rows);
        $activeRules = collect($rows->items() ?? $rows)->where('is_active', 1)->count();
        $workingDays = collect($rows->items() ?? $rows)->where('is_working', 1)->count();
        $weekoffDays = collect($rows->items() ?? $rows)->where('is_off', 1)->count();
    @endphp
    <div class="orb-summary-grid">
        <div class="orb-summary-card">
            <div class="orb-summary-label">Total Rules</div>
            <div class="orb-summary-value">{{ $totalRules }}</div>
        </div>
        <div class="orb-summary-card" style="border-bottom: 4px solid #10b981;">
            <div class="orb-summary-label">Working Days</div>
            <div class="orb-summary-value text-success">{{ $workingDays }}</div>
        </div>
        <div class="orb-summary-card" style="border-bottom: 4px solid #ef4444;">
            <div class="orb-summary-label">Weekoff Days</div>
            <div class="orb-summary-value text-danger">{{ $weekoffDays }}</div>
        </div>
        <div class="orb-summary-card" style="border-bottom: 4px solid var(--orb-primary);">
            <div class="orb-summary-label">Active Rules</div>
            <div class="orb-summary-value text-primary">{{ $activeRules }}</div>
        </div>
    </div>

    <!-- Calendar-Style Weekoff Layout -->
    <div class="weekoff-grid-wrapper">
        @php
            $groupedRules = collect($rows->items() ?? $rows)->groupBy('weekday');
            $dayNames = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday'
            ];
        @endphp
        
        <div class="weekoff-cards-grid">
            @for($dayNum = 1; $dayNum <= 7; $dayNum++)
                @php
                    $dayRules = $groupedRules->get($dayNum, collect());
                    $hasOff = $dayRules->where('is_off', 1)->count() > 0;
                    $exclusivelyWorking = $dayRules->count() > 0 && $dayRules->where('is_off', 1)->count() === 0;
                    $cardClass = '';
                    if ($hasOff) {
                        $cardClass = 'has-off';
                    } elseif ($exclusivelyWorking) {
                        $cardClass = 'exclusively-working';
                    }
                    if ($dayNum === 6) {
                        $cardClass .= ' saturday-card';
                    }
                @endphp
                <div class="weekoff-day-card {{ $cardClass }}">
                    <div class="day-name-title">
                        <span>{{ $dayNames[$dayNum] }}</span>
                        @if($dayRules->count() > 0)
                            <span class="badge badge-light border text-primary" style="font-size: 10px; font-weight: 850;">
                                {{ $dayRules->count() }} {{ Str::plural('Rule', $dayRules->count()) }}
                            </span>
                        @endif
                    </div>

                    <div class="rules-inner-list">
                        @forelse($dayRules as $rule)
                            <div class="rule-item-box">
                                <div class="rule-status-header">
                                    <span class="week-lbl">
                                        {{ $rule->week_number ? $rule->week_number . ( [1=>'st', 2=>'nd', 3=>'rd'][$rule->week_number] ?? 'th' ) . ' Wk' : 'Every Wk' }}
                                    </span>
                                    
                                    <div class="d-inline-flex gap-1" style="gap: 4px;">
                                        @if($rule->is_working)
                                            <span class="badge badge-success px-2 py-1" style="font-size: 9px; font-weight: 900; border-radius: 6px;">WORKING</span>
                                        @endif
                                        @if($rule->is_off)
                                            <span class="badge badge-danger px-2 py-1" style="font-size: 9px; font-weight: 900; border-radius: 6px;">OFF</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <span class="badge {{ $rule->is_active ? 'badge-soft-primary text-primary' : 'badge-light text-muted' }}" style="font-size: 9px; font-weight: 850;">
                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                    </span>

                                    <!-- Quick Actions dropdown same as table -->
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-link text-muted p-0" type="button" data-toggle="dropdown" style="line-height:1; font-size:12px; outline:none; box-shadow:none;">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(!empty($canEdit))
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $rule->id }}">
                                                <i class="fas fa-edit mr-2 text-primary"></i> Edit
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($rule->effective_from || $rule->effective_to)
                                    <span class="rule-date-range">
                                        <i class="far fa-calendar-alt me-1 text-primary"></i>
                                        {{ $rule->effective_from ? \Carbon\Carbon::parse($rule->effective_from)->format('d M') : 'Start' }} - 
                                        {{ $rule->effective_to ? \Carbon\Carbon::parse($rule->effective_to)->format('d M Y') : 'End' }}
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="empty-day-lbl">
                                <i class="fas fa-calendar-check text-light d-block mb-1 fs-4" style="font-size: 16px;"></i>
                                Default Working
                            </div>
                        @endforelse
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <!-- Detailed Rules Table as Secondary View -->
    <div class="orb-card orb-table-card">
        <div class="orb-card-body">
            <div class="orb-table-header">
                <div class="orb-table-head-left d-flex align-items-center" style="gap: 14px;">
                    <div class="orb-icon-box">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                    <div>
                        <h3 class="orb-table-title">Detailed Rules Table</h3>
                        <p class="orb-table-subtitle">Manage records, filters, actions and exports from one clean table.</p>
                    </div>
                </div>

                <div class="orb-table-head-right">
                    <span class="orb-table-count">
                        <i class="fas fa-database"></i>
                        Total: {{ $totalRules }}
                    </span>

                    @if(!empty($filters))
                    <a href="{{ url()->current() }}" class="orb-btn orb-btn-light orb-btn-reset">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    @endif
                </div>
            </div>

            @if(!empty($filters))
            <div class="orb-filter">
                <form method="GET" id="filterForm" class="orb-filter-form">
                    @foreach($filters as $filter)
                    <div class="orb-filter-item">
                        <label>{{ $filter['label'] }}</label>
                        @if(($filter['type'] ?? 'text') === 'select')
                        <select name="{{ $filter['name'] }}" class="form-control js-auto-filter">
                            <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                            @foreach($filter['options'] as $value => $label)
                            <option value="{{ $value }}" {{ (string) request($filter['name']) === (string) $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @else
                        <input type="{{ $filter['type'] ?? 'text' }}" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}" class="form-control js-auto-filter" placeholder="{{ $filter['placeholder'] ?? '' }}">
                        @endif
                    </div>
                    @endforeach
                </form>
            </div>
            @endif

            <div class="orb-table-tools"></div>

            <div class="orb-table-wrap crud-table-responsive">
                <table class="table table-hover orb-table js-orb-datatable w-100">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Weekday</th>
                            <th>Week No.</th>
                            <th>Working</th>
                            <th>Off</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $isLwp = false;
                            @endphp
                            <tr>
                                <td><strong>{{ $loop->iteration }}</strong></td>
                                <td>
                                    <span class="font-weight-black text-dark">
                                        {{ $dayNames[$row->weekday] ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td>
                                    @if($row->week_number)
                                        <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 11px;">
                                            {{ $row->week_number }}@if($row->week_number == 1)st @elseif($row->week_number == 2)nd @elseif($row->week_number == 3)rd @else th @endif Week
                                        </span>
                                    @else
                                        <span class="text-muted font-weight-bold small">Every Week</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->is_working)
                                        <span class="orb-badge orb-badge-success">Working</span>
                                    @else
                                        <span class="text-muted font-weight-bold small">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->is_off)
                                        <span class="orb-badge orb-badge-danger">Off</span>
                                    @else
                                        <span class="text-muted font-weight-bold small">No</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $row->effective_from ? \Carbon\Carbon::parse($row->effective_from)->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    {{ $row->effective_to ? \Carbon\Carbon::parse($row->effective_to)->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    <span class="orb-badge {{ $row->is_active ? 'orb-badge-success' : 'orb-badge-danger' }}">
                                        {{ $row->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="orb-action-btn" type="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(!empty($canEdit))
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $row->id }}">
                                                <i class="fas fa-edit mr-2 text-primary"></i> Edit
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                                        <h5>No custom weekoff rules found</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($rows, 'links'))
            <div class="mt-3 px-3 pb-3">
                {{ $rows->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Dynamic CRUD Modals perfectly integrated -->
    @if(!empty($canCreate))
    @include('hrms.shared.crud-modal', [
        'modalId' => 'createModal',
        'modalTitle' => 'Add Weekoff Rule',
        'action' => route($storeRoute),
        'method' => 'POST',
        'fields' => $formFields,
        'row' => null
    ])
    @endif

    @if(!empty($canEdit))
    @foreach($rows as $row)
    @include('hrms.shared.crud-modal', [
        'modalId' => 'editModal'.$row->id,
        'modalTitle' => 'Edit Weekoff Rule',
        'action' => route($updateRoute, $row->id),
        'method' => 'PUT',
        'fields' => $formFields,
        'row' => $row
    ])
    @endforeach
    @endif

</div>
@endsection
