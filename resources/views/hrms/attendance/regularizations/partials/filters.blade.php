@if(!empty($filters))
@php
    $currentMonthKey = now()->format('Y-m');
    $reqMonth = request('month');
    $hasCustomDates = request()->filled('from') || request()->filled('from_date') || request()->filled('to') || request()->filled('to_date');

    if ($hasCustomDates || $reqMonth === 'custom') {
        $activeMonth = 'custom';
    } elseif ($reqMonth !== null) {
        $activeMonth = $reqMonth;
    } else {
        $activeMonth = $currentMonthKey;
    }
@endphp

<div class="att-filter-panel">
    <form method="GET" action="{{ url()->current() }}" id="filterForm">
        <div class="att-filter-grid">
            @foreach($filters as $filter)
                @php
                    $filterName = $filter['name'];
                    $filterType = $filter['type'] ?? 'text';
                    $filterLabel = $filter['label'] ?? '';
                    $filterVal = request($filterName);
                    $placeholder = $filter['placeholder'] ?? ($filterType === 'select' ? 'All ' . $filterLabel : ($filterType === 'date' ? 'dd-mm-yyyy' : ''));
                    $isDateField = ($filterType === 'date' || in_array($filterName, ['from', 'to', 'from_date', 'to_date']));
                @endphp

                <div class="att-filter-item {{ $isDateField ? 'js-custom-date-field' : '' }}" style="{{ ($isDateField && $activeMonth !== 'custom') ? 'display: none;' : '' }}">
                    <label>{{ $filterLabel }}</label>
                    @if($filterType === 'select')
                        @php
                            $optionsList = [];
                            foreach ($filter['options'] ?? [] as $value => $label) {
                                $displayLabel = $label;
                                if ($filterName === 'request_type') {
                                    $displayLabel = $typeLabels[$value] ?? ucfirst(str_replace('_', ' ', $value));
                                }
                                $optionsList[$value] = $displayLabel;
                            }
                        @endphp
                        <select name="{{ $filterName }}" id="{{ $filterName === 'month' ? 'filter_month' : 'filter_' . $filterName }}" class="form-control select2-searchable">
                            @if($filterName !== 'month')
                                <option value="">All {{ $filterLabel }}</option>
                            @endif
                            @foreach($optionsList as $val => $lbl)
                                @php
                                    $isSelected = ($filterName === 'month')
                                        ? ((string) $activeMonth === (string) $val)
                                        : ((string) $filterVal === (string) $val);
                                @endphp
                                <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    @elseif($filterType === 'date')
                        <x-form.date-picker
                            :name="$filterName"
                            :value="$filterVal"
                            :placeholder="$placeholder"
                            class="form-control"
                        />
                    @else
                        <input type="{{ $filterType }}" name="{{ $filterName }}" value="{{ $filterVal }}" class="form-control" placeholder="{{ $placeholder }}">
                    @endif
                </div>
            @endforeach

            <div class="att-filter-actions">
                <button type="submit" class="btn text-white font-weight-bold shadow-sm" style="height: 42px; min-width: 105px; border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px;">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="{{ url()->current() }}?month={{ now()->format('Y-m') }}" class="btn btn-light border font-weight-bold" style="height: 42px; width: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Reset Filters">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </div>
    </form>
</div>
@endif
