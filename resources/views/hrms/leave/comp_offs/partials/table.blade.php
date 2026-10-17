    <!-- Compensatory Off Records & Transactions Card with Tabs -->
    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">
                        @if($isEmployeeRole)
                            My Compensatory Off
                        @else
                            Compensatory Off Records & Transactions
                        @endif
                    </h5>
                    <div class="leave-card-subtitle">
                        @if($isEmployeeRole)
                            Track earned credits, date of work, reasons, transactions against leaves, and expiry schedule.
                        @else
                            Track employee earned, used, expired compensatory-off balances and transaction audit ledger.
                        @endif
                    </div>
                </div>
            </div>

            @if(!empty($canManageCompOff))
                <button type="button" class="btn btn-sm btn-orb-search" data-toggle="modal" data-target="#grantCompOffModal">
                    <i class="fas fa-plus-circle" style="font-size: 12px;"></i> Grant Comp-Off
                </button>
            @endif
        </div>

        <!-- Modern HRMS Tab Switcher -->
        <div class="leave-tabs-nav-wrap">
            <ul class="nav nav-pills leave-modern-tabs" id="compOffTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="records-tab" data-toggle="pill" href="#tab-records" role="tab" aria-controls="tab-records" aria-selected="true">
                        <i class="fas fa-calendar-check mr-2"></i>
                        <span>Comp-Off Records</span>
                        <span class="leave-tab-badge ml-2">{{ $compOffs->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="transactions-tab" data-toggle="pill" href="#tab-transactions" role="tab" aria-controls="tab-transactions" aria-selected="false">
                        <i class="fas fa-history mr-2"></i>
                        <span>Comp-Off Transactions</span>
                        <span class="leave-tab-badge ml-2">{{ $compOffTransactions->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Filter Toolbar Section Directly Below Tabs (Shared Filter) -->
        @include('hrms.leave.comp_offs.partials.filter-bar')

        <!-- Table Action Toolbar with Show Entries & Export Tools (HRMS Theme) -->
        <div class="orb-table-tools-bar eo-toolbar">
            <div class="orb-table-length-box eo-toolbar-left">
                <div class="dataTables_length d-flex align-items-center">
                    <label class="eo-entries-label mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size: 13px; gap: 8px;">
                        <span>Show</span>
                        <select id="compOffPerPageSelect" name="per_page" class="table-per-page-select orb-per-page-select" style="width: 75px;">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span>entries</span>
                    </label>
                </div>
            </div>
            <div class="orb-table-export-buttons eo-toolbar-right">
                <div class="orbo-export-group">
                    <button type="button" class="orbo-export-btn btn-export-csv" onclick="exportTable('csv')" title="Export CSV">
                        <i class="fas fa-file-csv icon-csv"></i>
                        <span>CSV</span>
                    </button>
                    <button type="button" class="orbo-export-btn btn-export-excel" onclick="exportTable('excel')" title="Export Excel">
                        <i class="fas fa-file-excel icon-excel"></i>
                        <span>Excel</span>
                    </button>
                    <button type="button" class="orbo-export-btn btn-export-pdf" onclick="exportTable('pdf', event)" title="Export PDF">
                        <i class="fas fa-file-pdf icon-pdf"></i>
                        <span>PDF</span>
                    </button>
                    <button type="button" class="orbo-export-btn btn-export-print" onclick="window.print()" title="Print">
                        <i class="fas fa-print icon-print"></i>
                        <span>Print</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Content Panes -->
        <div class="tab-content" id="compOffTabContent">

            <!-- TAB 1: Comp-Off Records Table -->
            <div class="tab-pane fade show active" id="tab-records" role="tabpanel" aria-labelledby="records-tab">
                <div class="leave-table-wrap p-0">
                    <div class="leave-table-responsive" style="border: 0; border-radius: 0;">
                        <table class="leave-table" id="compOffTable">
                            <thead>
                                <tr>
                                    <th style="width: 55px; text-align: center;">S.No.</th>
                                    @if(!$isEmployeeRole)
                                        <th style="min-width: 150px;">Employee</th>
                                    @endif
                                    <th style="min-width: 130px;">Worked Date</th>
                                    <th style="min-width: 250px;">Reason</th>
                                    <th style="min-width: 120px; text-align: center;">Earned / Used</th>
                                    <th style="min-width: 130px;">Expiry Date</th>
                                    <th style="min-width: 100px; text-align: center;">Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($compOffs as $compOff)
                                @php
                                $employeeName = optional($compOff->employee)->display_name ?? optional(optional($compOff->employee)->user)->name ?? 'Unknown Employee';
                                $employeeCode = optional($compOff->employee)->employee_code ?? optional($compOff->employee)->code ?? 'EMP';

                                $rawStatus = strtolower($compOff->status ?? 'earned');
                                $statusClass = 'pill-muted';
                                $statusLabel = ucfirst($rawStatus);
                                if ($rawStatus === 'earned' || $rawStatus === 'available') {
                                    $statusClass = 'pill-earned';
                                    $statusLabel = 'Available';
                                } elseif ($rawStatus === 'used') {
                                    $statusClass = 'pill-used';
                                    $statusLabel = 'Used';
                                } elseif ($rawStatus === 'expired') {
                                    $statusClass = 'pill-expired';
                                    $statusLabel = 'Expired';
                                }

                                $rawRemarks = (string) $compOff->remarks;
                                $cleanReasonText = trim(preg_replace('/\[[a-zA-Z0-9\-_:]+\]/', '', $rawRemarks));
                                $reasonType = 'other';
                                $reasonBadgeLabel = 'Comp-Off Credit';

                                if (preg_match('/Manual grant by HR:\s*(.*)/i', $cleanReasonText, $m)) {
                                    $reasonType = 'manual';
                                    $reasonBadgeLabel = 'Manual Grant';
                                    $cleanReasonText = trim($m[1]) !== '' ? trim($m[1]) : 'Manual HR Grant';
                                } elseif (preg_match('/holiday\/weekoff work request #(\d+)/i', $cleanReasonText, $m)) {
                                    $reasonType = 'holiday';
                                    $reasonBadgeLabel = 'Holiday Work';
                                    $cleanReasonText = 'Holiday / Weekoff Work (Request #' . $m[1] . ')';
                                } elseif (stripos($cleanReasonText, 'holiday') !== false || stripos($cleanReasonText, 'weekoff') !== false) {
                                    $reasonType = 'holiday';
                                    $reasonBadgeLabel = 'Holiday Work';
                                    $lines = explode("\n", $cleanReasonText);
                                    $cleanReasonText = trim($lines[0]);
                                }

                                if (empty($cleanReasonText)) {
                                    $cleanReasonText = 'Approved Comp-Off Credit';
                                }

                                $approverUser = $compOff->approvedBy;
                                if ($approverUser && $compOff->employee && (int) $approverUser->id === (int) $compOff->employee->user_id) {
                                    $approverName = 'HR Admin';
                                } else {
                                    $approverName = $approverUser ? $approverUser->name : null;
                                }
                                @endphp

                                <tr>
                                    <td style="text-align: center;"><strong>{{ $loop->iteration }}</strong></td>

                                    @if(!$isEmployeeRole)
                                    <td>
                                        <div>
                                            <div class="employee-name font-weight-bold" style="color: #1E293B; font-size: 13.5px;">{{ $employeeName }}</div>
                                            <div class="employee-meta text-muted" style="font-size: 11.5px; font-weight: 600;">{{ $employeeCode }}</div>
                                        </div>
                                    </td>
                                    @endif

                                    <td>
                                        <span class="date-pill">
                                            <i class="fas fa-calendar-day text-primary"></i>
                                            {{ optional($compOff->worked_date)->format('d M Y') ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="col-reason">
                                        <div class="reason-cell-wrap">
                                            <div class="d-flex align-items-center" style="gap: 6px;">
                                                @if($reasonType === 'manual')
                                                    <span class="co-reason-badge badge-manual">
                                                        <i class="fas fa-user-shield"></i> {{ $reasonBadgeLabel }}
                                                    </span>
                                                @elseif($reasonType === 'holiday')
                                                    <span class="co-reason-badge badge-holiday">
                                                        <i class="fas fa-calendar-check"></i> {{ $reasonBadgeLabel }}
                                                    </span>
                                                @else
                                                    <span class="co-reason-badge badge-system">
                                                        <i class="fas fa-tag"></i> {{ $reasonBadgeLabel }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="reason-text-main">
                                                {{ $cleanReasonText }}
                                            </div>
                                            @if($approverName)
                                                <div class="reason-approver-meta">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>Approved by {{ $approverName }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td style="text-align: center;">
                                        <div class="earned-used justify-content-center">
                                            <span class="earned-days">+{{ number_format((float) $compOff->earned_days, 1) }}</span>
                                            <span class="text-muted">/</span>
                                            <span class="used-days">-{{ number_format((float) ($compOff->used_days ?? ($rawStatus === 'used' ? $compOff->earned_days : 0)), 1) }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="date-pill">
                                            <i class="fas fa-clock text-warning"></i>
                                            {{ optional($compOff->expiry_date)->format('d M Y') ?? '-' }}
                                        </span>
                                    </td>

                                    <td style="text-align: center;">
                                        <span class="leave-pill {{ $statusClass }}">
                                            <i class="fas fa-circle" style="font-size:6px;"></i>
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $isEmployeeRole ? 6 : 7 }}">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-plus"></i>
                                            <div style="font-weight:900;color:var(--leave-text);">
                                                No Compensatory Off Records Found
                                            </div>
                                            <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                                @if($isEmployeeRole)
                                                    When you work on approved holidays or weekoffs, your comp-off credits will appear here.
                                                @else
                                                    No compensatory off records matching the selected month or criteria.
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Orbo Theme Server-Side Pagination -->
                <div class="border-top bg-white">
                    {{ $compOffs->links('vendor.pagination.orbo') }}
                </div>
            </div>

            <!-- TAB 2: Comp-Off Transactions Table -->
            <div class="tab-pane fade" id="tab-transactions" role="tabpanel" aria-labelledby="transactions-tab">
                <div class="leave-table-wrap p-0">
                    <div class="leave-table-responsive" style="border: 0; border-radius: 0;">
                        <table class="leave-table" id="compOffTransactionsTable">
                            <thead>
                                <tr>
                                    <th style="width: 55px; text-align: center;">S.No.</th>
                                    @if(!$isEmployeeRole)
                                        <th style="min-width: 150px;">Employee</th>
                                    @endif
                                    <th style="min-width: 130px;">Transaction Date</th>
                                    <th style="min-width: 120px;">Type</th>
                                    <th style="min-width: 100px;">Amount</th>
                                    <th style="min-width: 250px;">Reference & Reason</th>
                                    <th style="min-width: 140px;">Logged By</th>
                                    <th style="min-width: 110px;">Validity</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($compOffTransactions as $tx)
                                @php
                                $txEmp = $tx->employee;
                                $employeeName = optional($txEmp)->display_name ?? optional(optional($txEmp)->user)->name ?? 'Unknown Employee';
                                $employeeCode = optional($txEmp)->employee_code ?? optional($txEmp)->code ?? 'EMP';
                                
                                $cleanTxRaw = trim(preg_replace('/\[[a-zA-Z0-9\-_:]+\]/', '', (string) $tx->description));
                                if (preg_match('/Manual grant by HR:\s*(.*)/i', $cleanTxRaw, $m)) {
                                    $cleanTxReason = trim($m[1]) !== '' ? trim($m[1]) : 'Manual HR Grant';
                                } elseif (preg_match('/holiday\/weekoff work request #(\d+)/i', $cleanTxRaw, $m)) {
                                    $cleanTxReason = 'Holiday / Weekoff Work (Request #' . $m[1] . ')';
                                } else {
                                    $lines = explode("\n", $cleanTxRaw);
                                    $cleanTxReason = trim($lines[0]);
                                }
                                if (empty($cleanTxReason)) {
                                    $cleanTxReason = 'Comp-Off Movement';
                                }
                                @endphp

                                <tr>
                                    <td style="text-align: center;"><strong>{{ $loop->iteration }}</strong></td>

                                    @if(!$isEmployeeRole)
                                    <td>
                                        <div>
                                            <div class="employee-name font-weight-bold" style="color: #1E293B; font-size: 13.5px;">{{ $employeeName }}</div>
                                            <div class="employee-meta text-muted" style="font-size: 11.5px; font-weight: 600;">{{ $employeeCode }}</div>
                                        </div>
                                    </td>
                                    @endif

                                    <td>
                                        <span class="date-pill">
                                            <i class="fas fa-calendar-day text-primary"></i>
                                            {{ $tx->effective_date }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="leave-pill {{ $tx->badge_class }}">
                                            @if($tx->type === 'credit')
                                                <i class="fas fa-arrow-down" style="font-size:7px;"></i>
                                            @elseif($tx->type === 'debit')
                                                <i class="fas fa-arrow-up" style="font-size:7px;"></i>
                                            @elseif($tx->type === 'reversal')
                                                <i class="fas fa-undo" style="font-size:7px;"></i>
                                            @else
                                                <i class="fas fa-clock" style="font-size:7px;"></i>
                                            @endif
                                            {{ $tx->type_label }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($tx->amount_numeric > 0)
                                            <span class="tx-amount-credit">{{ $tx->amount }} Days</span>
                                        @else
                                            <span class="tx-amount-debit">{{ $tx->amount }} Days</span>
                                        @endif
                                    </td>

                                    <td class="col-reason">
                                        <div class="reason-cell-wrap">
                                            <div>
                                                <span class="tx-reference-tag">{{ $tx->reference }}</span>
                                            </div>
                                            <div class="reason-text-main">
                                                {{ $cleanTxReason }}
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="text-muted" style="font-size: 12px; font-weight: 600;">
                                            <i class="fas fa-user-shield text-primary mr-1"></i>
                                            {{ $tx->action_by }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="date-pill" style="font-size: 11px;">
                                            <i class="fas fa-hourglass-half text-warning"></i>
                                            {{ $tx->expiry_date ?: '-' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $isEmployeeRole ? 7 : 8 }}">
                                        <div class="empty-state">
                                            <i class="fas fa-history"></i>
                                            <div style="font-weight:900;color:var(--leave-text);">
                                                No Comp-Off Transactions Found
                                            </div>
                                            <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                                No comp-off credits, debits or movement transactions recorded for the selected month or criteria.
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
