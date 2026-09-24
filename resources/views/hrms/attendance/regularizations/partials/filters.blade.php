@if(!empty($filters))
<div class="att-filter-panel">
    <form method="GET" id="filterForm">
        <div class="att-filter-grid">
            @foreach($filters as $filter)
                <div>
                    <label>{{ $filter['label'] }}</label>
                    @if(($filter['type'] ?? 'text') === 'select')
                        <select name="{{ $filter['name'] }}" class="form-control select2-searchable">
                            <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                            @foreach($filter['options'] as $value => $label)
                                @php
                                    $displayLabel = $label;
                                    if ($filter['name'] === 'request_type') {
                                        $displayLabel = $typeLabels[$value] ?? ucfirst(str_replace('_', ' ', $value));
                                    }
                                @endphp
                                <option value="{{ $value }}" {{ (string) request($filter['name']) === (string) $value ? 'selected' : '' }}>
                                    {{ $displayLabel }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="{{ $filter['type'] ?? 'text' }}" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}" class="form-control" placeholder="{{ $filter['placeholder'] ?? '' }}">
                    @endif
                </div>
            @endforeach
            <div>
                <label>&nbsp;</label>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <button type="submit" class="btn text-white font-weight-bold shadow-sm" style="height: 38px; border-radius: 8px; background: var(--orb-primary); border: none; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px; flex: 1;">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ url()->current() }}" class="att-btn att-btn-light justify-content-center" style="height: 38px !important; width: 38px !important; border-radius: 8px !important; font-size: 12px !important; font-weight: 800 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; border: 1px solid var(--orb-border) !important;" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endif
