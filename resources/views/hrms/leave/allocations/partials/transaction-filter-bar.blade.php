<form method="GET" action="{{ route('leave-allocations.index') }}" class="eo-filter-grid eo-filter-grid-transactions">
    <input type="hidden" name="tab" value="transactions">

    <div class="eo-field">
        <label for="txFilterSearch"><i class="fas fa-search mr-1"></i> Quick Search</label>
        <input type="text" id="txFilterSearch" name="search" class="eo-control" placeholder="Search employee name or code..." value="{{ request('search') }}" autocomplete="off">
    </div>

    <div class="eo-field">
        <label for="txFilterYear"><i class="fas fa-calendar-alt mr-1"></i> Year</label>
        <select id="txFilterYear" name="year" class="eo-control select2-searchable">
            @php $txYear = (int) request('year', $year); @endphp
            @for($y = 2024; $y <= 2030; $y++)
                <option value="{{ $y }}" {{ $txYear === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    <div class="eo-field">
        <label for="txFilterMonth"><i class="far fa-calendar-check mr-1"></i> Month</label>
        <select id="txFilterMonth" name="month" class="eo-control select2-searchable">
            <option value="all" {{ ($selectedMonth ?? request('month')) === 'all' ? 'selected' : '' }}>All Months</option>
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ (string) ($selectedMonth ?? request('month')) === (string) $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(2026, $m, 1)->format('F') }} {{ (int) $m === (int) now('Asia/Kolkata')->month && (int) ($year ?? date('Y')) === (int) now('Asia/Kolkata')->year ? '(Current)' : '' }}
                </option>
            @endfor
        </select>
    </div>

    <div class="eo-field">
        <label for="txFilterBucket"><i class="fas fa-layer-group mr-1"></i> Bucket</label>
        <select id="txFilterBucket" name="bucket" class="eo-control select2-searchable">
            <option value="">All Buckets</option>
            <option value="PL" {{ request('bucket') === 'PL' ? 'selected' : '' }}>PL (Paid Leave)</option>
            <option value="SL" {{ request('bucket') === 'SL' ? 'selected' : '' }}>SL (Sick Leave)</option>
        </select>
    </div>

    <div class="eo-field">
        <label for="txFilterType"><i class="fas fa-tag mr-1"></i> Type</label>
        <select id="txFilterType" name="type" class="eo-control select2-searchable">
            <option value="">All Types</option>
            @foreach(['monthly_accrual'=>'Accrual','lapse'=>'Lapse','leave_debit'=>'Leave debit','reversal'=>'Reversal','adjustment'=>'Adjustment','opening_balance'=>'Opening'] as $key=>$label)
                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="eo-field">
        <label for="txFilterSource"><i class="fas fa-cogs mr-1"></i> Source</label>
        <select id="txFilterSource" name="source" class="eo-control select2-searchable">
            <option value="">All Sources</option>
            @foreach(['system'=>'System','manual'=>'Manual','import'=>'Import'] as $key=>$label)
                <option value="{{ $key }}" {{ request('source') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="eo-filter-actions-col">
        <div class="eo-filter-actions-wrap">
            <button type="submit" id="btnTxFilterSubmit">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            <a href="{{ route('leave-allocations.index', ['tab' => 'transactions', 'year' => $year]) }}" id="resetTxFilter" style="text-decoration:none;">
                <i class="fas fa-undo mr-1"></i> Reset
            </a>
        </div>
    </div>
</form>
