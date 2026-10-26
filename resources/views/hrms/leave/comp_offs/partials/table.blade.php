    <!-- Compensatory Off Records & Transactions Card with Leave Allocation Standard UI -->
    <div class="orb-table-card">
        <div class="orb-table-head">
            <div class="orb-table-title-wrap">
                <div class="orb-table-icon">
                    <i class="fas fa-calendar-check" id="compOffCardIcon"></i>
                </div>
                <div>
                    <h3 class="m-0" id="compOffCardTitle">
                        @if($isEmployeeRole)
                            My Compensatory Off
                        @else
                            Compensatory Off Records
                        @endif
                    </h3>
                    <p class="m-0 text-muted" id="compOffCardSubtitle" style="font-size: 12.5px;">
                        @if($isEmployeeRole)
                            Track earned credits, date of work, reasons, transactions against leaves, and expiry schedule.
                        @else
                            Track employee earned, used, expired compensatory-off balances and expiry schedule.
                        @endif
                    </p>
                </div>
            </div>

            @if(!empty($canManageCompOff))
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="leave-btn" style="height: 38px; border-radius: 10px; padding: 0 16px;" data-toggle="modal" data-target="#grantCompOffModal">
                        <i class="fas fa-plus-circle"></i> Grant Comp-Off
                    </button>
                </div>
            @endif
        </div>

        <!-- Filter Toolbar Section Directly Attached Inside Card (Leave Allocation EO Grid) -->
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
                        <table class="leave-table" id="compOffTable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    @if(!$isEmployeeRole)
                                        <th class="text-left" style="min-width: 220px;">Employee</th>
                                    @endif
                                    <th class="text-center" style="min-width: 140px;">Worked Date</th>
                                    <th class="text-center" style="min-width: 125px;">Credit Earned</th>
                                    <th class="text-center" style="min-width: 150px;">Usage & Balance</th>
                                    <th class="text-center" style="min-width: 140px;">Expiry Date</th>
                                    <th class="text-center" style="min-width: 105px;">Status</th>
                                    <th class="text-left" style="min-width: 200px;">Reason</th>
                                    @if(!empty($canManageCompOff))
                                        <th class="text-center no-export" style="min-width: 90px; width: 90px;">Actions</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($compOffs as $compOff)
                                @php
                                    $emp = $compOff->employee;
                                    $employeeName = optional($emp)->display_name ?? optional(optional($emp)->user)->name ?? 'Unknown Employee';
                                    $employeeCode = optional($emp)->employee_code ?? optional($emp)->code ?? 'EMP';
                                    $initial = strtoupper(substr(trim($employeeName), 0, 1));

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

                                    if (preg_match('/^Manual grant by HR:\s*(.*)$/i', $cleanReasonText, $m)) {
                                        $cleanReasonText = trim($m[1]);
                                    } elseif (preg_match('/^(?:Generated|Reconciled) from approved holiday\/weekoff work request #\d+[\.:\s]*(.*)$/i', $cleanReasonText, $m)) {
                                        $cleanReasonText = trim($m[1]);
                                    }

                                    if (empty($cleanReasonText) || strtolower($cleanReasonText) === 'approved comp-off credit' || strtolower($cleanReasonText) === 'comp-off credit') {
                                        $cleanReasonText = '-';
                                    }

                                    $earnedVal = (float) $compOff->earned_days;
                                    $usedVal = (float) ($compOff->used_days ?? ($rawStatus === 'used' ? $compOff->earned_days : 0));
                                    $balanceVal = max(0.0, $earnedVal - $usedVal);

                                    $expDate = optional($compOff->expiry_date);
                                    $isExp = $rawStatus === 'expired' || ($expDate && $expDate->isPast());
                                    $daysLeft = $expDate && !$isExp ? now('Asia/Kolkata')->startOfDay()->diffInDays($expDate->startOfDay(), false) : null;
                                    $rowIndex = isset($compOffs) && method_exists($compOffs, 'currentPage') ? $loop->iteration + ($compOffs->currentPage() - 1) * $compOffs->perPage() : $loop->iteration;
                                @endphp

                                <tr>
                                    <td class="text-center font-weight-bold" style="color: var(--leave-text);">
                                        {{ $rowIndex }}
                                    </td>

                                    @if(!$isEmployeeRole)
                                    <td class="text-left">
                                        <div>
                                            <div class="leave-employee-name" style="font-weight: 800; color: #1E293B; font-size: 13.5px;">{{ $employeeName }}</div>
                                            <div class="leave-employee-meta" style="font-size: 11.5px; color: var(--leave-muted); font-weight: 600;">{{ $employeeCode }}</div>
                                            @if(optional($emp)->designation || optional($emp)->department)
                                                <div class="text-muted" style="font-size: 11px; font-weight: 600;">
                                                    {{ optional(optional($emp)->designation)->name ?? optional(optional($emp)->department)->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    @endif

                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center font-weight-bold" style="color: var(--leave-text); font-size: 13px; gap: 6px;">
                                            <i class="far fa-calendar-check text-primary"></i>
                                            <span>{{ optional($compOff->worked_date)->format('d M Y') ?? '-' }}</span>
                                        </div>
                                        @if($compOff->worked_date)
                                            <div class="text-muted" style="font-size: 11px; font-weight: 600;">
                                                {{ $compOff->worked_date->format('l') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="leave-metric text-success font-weight-bold">
                                            +{{ number_format($earnedVal, 1) }}
                                        </div>
                                        <div class="leave-breakdown-box">
                                            <span class="leave-breakdown-item" style="background:#ECFDF3; border-color:#ABEFC6; color:#067647;">
                                                {{ $earnedVal >= 1.0 ? 'Full Day' : 'Half Day' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="leave-metric {{ $balanceVal > 0 ? 'text-primary' : 'text-muted' }}">
                                            {{ number_format($balanceVal, 1) }} <span style="font-size: 11px; font-weight: 700; color: var(--leave-muted);">Bal</span>
                                        </div>
                                        <div class="leave-breakdown-box">
                                            <span class="leave-breakdown-item" style="background:#FEF3F2; border-color:#FECDCA; color:#B42318;">
                                                Used: <span>-{{ number_format($usedVal, 1) }}</span>
                                            </span>
                                            <span class="leave-breakdown-item" style="background:#F0F9FF; border-color:#B2DDFF; color:#026AA7;">
                                                Rem: <span>{{ number_format($balanceVal, 1) }}</span>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center font-weight-bold" style="color: var(--leave-text); font-size: 13px; gap: 6px;">
                                            <i class="fas fa-clock {{ $isExp ? 'text-danger' : 'text-warning' }}"></i>
                                            <span>{{ $expDate ? $expDate->format('d M Y') : '-' }}</span>
                                        </div>
                                        <div class="leave-breakdown-box">
                                            @if($isExp)
                                                <span class="leave-breakdown-item" style="background:#FEF3F2; border-color:#FECDCA; color:#B42318;">
                                                    Expired
                                                </span>
                                            @elseif($daysLeft !== null)
                                                <span class="leave-breakdown-item" style="background:#FFF9EB; border-color:#FEDF89; color:#B54708;">
                                                    {{ $daysLeft == 0 ? 'Expires today' : ($daysLeft == 1 ? '1 day left' : $daysLeft . ' days left') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="leave-pill {{ $statusClass }}">
                                            <i class="fas fa-circle" style="font-size:6px;"></i>
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <td class="col-reason text-left">
                                        <div class="reason-text-main font-weight-bold" style="color: #1E293B; font-size: 13px;">
                                            {{ $cleanReasonText ?: '-' }}
                                        </div>
                                    </td>

                                    @if(!empty($canManageCompOff))
                                    <td class="text-center no-export">
                                        <div class="leave-action-wrap justify-content-center d-flex align-items-center">
                                            <button type="button"
                                                class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center btn-edit-comp-off"
                                                style="border-radius:8px; width:30px; height:30px; padding:0; box-shadow:0 4px 10px rgba(75,0,232,.15);"
                                                data-id="{{ $compOff->id }}"
                                                data-employee="{{ $employeeName }}"
                                                data-code="{{ $employeeCode }}"
                                                data-worked-date="{{ optional($compOff->worked_date)->format('Y-m-d') }}"
                                                data-earned-days="{{ $compOff->earned_days }}"
                                                data-expiry-date="{{ optional($compOff->expiry_date)->format('Y-m-d') }}"
                                                data-status="{{ strtolower($compOff->status ?? 'earned') }}"
                                                data-remarks="{{ $cleanReasonText !== '-' ? $cleanReasonText : '' }}"
                                                data-update-url="{{ route('hrms.comp_offs.update', $compOff->id) }}"
                                                title="Edit Comp-Off">
                                                <i class="fas fa-edit" style="font-size:12px;"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    @php
                                        $emptyColspan = 7;
                                        if (!$isEmployeeRole) $emptyColspan++;
                                        if (!empty($canManageCompOff)) $emptyColspan++;
                                    @endphp
                                    <td colspan="{{ $emptyColspan }}" class="text-center" style="text-align: center; vertical-align: middle; padding: 48px 18px;">
                                        <div class="empty-state" style="margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                            <i class="fas fa-calendar-plus"></i>
                                            <div style="font-weight:900; color:var(--leave-text); font-size: 15px;">
                                                No Compensatory Off Records Found
                                            </div>
                                            <div style="font-size:12.5px; margin-top:4px; color:var(--leave-muted);">
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

                <!-- Orbo Theme Server-Side Pagination Footer (Matching Leave Allocation) -->
                <div class="orb-table-footer p-0 border-0">
                    {{ $compOffs->links('vendor.pagination.orbo') }}
                </div>
            </div>

            <!-- TAB 2: Comp-Off Transactions Table -->
            <div class="tab-pane fade" id="tab-transactions" role="tabpanel" aria-labelledby="transactions-tab">
                <div class="leave-table-wrap p-0">
                    <div class="leave-table-responsive" style="border: 0; border-radius: 0;">
                        <table class="leave-table" id="compOffTransactionsTable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    @if(!$isEmployeeRole)
                                        <th class="text-left" style="min-width: 220px;">Employee</th>
                                    @endif
                                    <th class="text-center" style="min-width: 140px;">Transaction Date</th>
                                    <th class="text-center" style="min-width: 120px;">Type</th>
                                    <th class="text-center" style="min-width: 120px;">Amount</th>
                                    <th class="text-left" style="min-width: 220px;">Reason</th>
                                    <th class="text-center" style="min-width: 140px;">Action By</th>
                                    <th class="text-center" style="min-width: 130px;">Validity / Expiry</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($compOffTransactions as $tx)
                                @php
                                    $txEmp = $tx->employee;
                                    $employeeName = optional($txEmp)->display_name ?? optional(optional($txEmp)->user)->name ?? 'Unknown Employee';
                                    $employeeCode = optional($txEmp)->employee_code ?? optional($txEmp)->code ?? 'EMP';
                                    $initial = strtoupper(substr(trim($employeeName), 0, 1));

                                    $cleanTxRaw = trim(preg_replace('/\[[a-zA-Z0-9\-_:]+\]/', '', (string) $tx->description));
                                    if (preg_match('/^Manual grant by HR:\s*(.*)$/i', $cleanTxRaw, $m)) {
                                        $cleanTxReason = trim($m[1]);
                                    } elseif (preg_match('/^(?:Generated|Reconciled) from approved holiday\/weekoff work request #\d+[\.:\s]*(.*)$/i', $cleanTxRaw, $m)) {
                                        $cleanTxReason = trim($m[1]);
                                    } else {
                                        $lines = explode("\n", $cleanTxRaw);
                                        $cleanTxReason = trim($lines[0]);
                                    }
                                    if (empty($cleanTxReason) || strtolower($cleanTxReason) === 'comp-off movement') {
                                        $cleanTxReason = '-';
                                    }
                                @endphp

                                <tr>
                                    <td class="text-center font-weight-bold" style="color: var(--leave-text);">
                                        {{ $loop->iteration }}
                                    </td>

                                    @if(!$isEmployeeRole)
                                    <td class="text-left">
                                        <div>
                                            <div class="leave-employee-name" style="font-weight: 800; color: #1E293B; font-size: 13.5px;">{{ $employeeName }}</div>
                                            <div class="leave-employee-meta" style="font-size: 11.5px; color: var(--leave-muted); font-weight: 600;">{{ $employeeCode }}</div>
                                            @if(optional($txEmp)->designation || optional($txEmp)->department)
                                                <div class="text-muted" style="font-size: 11px; font-weight: 600;">
                                                    {{ optional(optional($txEmp)->designation)->name ?? optional(optional($txEmp)->department)->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    @endif

                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center font-weight-bold" style="color: var(--leave-text); font-size: 13px; gap: 6px;">
                                            <i class="far fa-calendar-alt text-primary"></i>
                                            <span>{{ $tx->effective_date }}</span>
                                        </div>
                                    </td>

                                    <td class="text-center">
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

                                    <td class="text-center">
                                        @if($tx->amount_numeric > 0)
                                            <span class="tx-amount-credit font-weight-bold">+{{ ltrim($tx->amount, '+') }} Days</span>
                                        @else
                                            <span class="tx-amount-debit font-weight-bold">{{ str_starts_with((string)$tx->amount, '-') ? $tx->amount : ('-' . ltrim($tx->amount, '-')) }} Days</span>
                                        @endif
                                    </td>

                                    <td class="col-reason text-left">
                                        <div class="reason-text-main font-weight-bold" style="color: #1E293B; font-size: 13px;">
                                            {{ $cleanTxReason ?: '-' }}
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        @php
                                            $abLower = strtolower($tx->action_by ?? '');
                                        @endphp
                                        @if(str_starts_with($abLower, 'manual'))
                                            <span class="d-inline-flex align-items-center font-weight-bold" style="font-size: 12px; color: #3538CD; background: #EEF4FF; border: 1px solid #C7D7FE; padding: 4px 10px; border-radius: 8px; gap: 5px;">
                                                <i class="fas fa-user-edit"></i>
                                                <span>{{ $tx->action_by }}</span>
                                            </span>
                                        @elseif(str_starts_with($abLower, 'leave'))
                                            <span class="d-inline-flex align-items-center font-weight-bold" style="font-size: 12px; color: #026AA7; background: #F0F9FF; border: 1px solid #B2DDFF; padding: 4px 10px; border-radius: 8px; gap: 5px;">
                                                <i class="fas fa-calendar-minus"></i>
                                                <span>{{ $tx->action_by }}</span>
                                            </span>
                                        @elseif(str_starts_with($abLower, 'system'))
                                            <span class="d-inline-flex align-items-center font-weight-bold" style="font-size: 12px; color: #475467; background: #F8F9FC; border: 1px solid #EAECF0; padding: 4px 10px; border-radius: 8px; gap: 5px;">
                                                <i class="fas fa-robot"></i>
                                                <span>{{ $tx->action_by }}</span>
                                            </span>
                                        @elseif(str_contains($abLower, 'holiday'))
                                            <span class="d-inline-flex align-items-center font-weight-bold" style="font-size: 12px; color: #067647; background: #ECFDF3; border: 1px solid #ABEFC6; padding: 4px 10px; border-radius: 8px; gap: 5px;">
                                                <i class="fas fa-business-time"></i>
                                                <span>{{ $tx->action_by }}</span>
                                            </span>
                                        @else
                                            <span class="d-inline-flex align-items-center font-weight-bold" style="font-size: 12px; color: #344054; background: #F2F4F7; border: 1px solid #D0D5DD; padding: 4px 10px; border-radius: 8px; gap: 5px;">
                                                <i class="fas fa-user-shield text-primary"></i>
                                                <span>{{ $tx->action_by }}</span>
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center font-weight-bold" style="color: var(--leave-text); font-size: 12.5px; gap: 6px;">
                                            <i class="fas fa-hourglass-half text-warning"></i>
                                            <span>{{ $tx->expiry_date ?: '-' }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $isEmployeeRole ? 7 : 8 }}" class="text-center" style="text-align: center; vertical-align: middle; padding: 48px 18px;">
                                        <div class="empty-state" style="margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                            <i class="fas fa-history"></i>
                                            <div style="font-weight:900; color:var(--leave-text); font-size: 15px;">
                                                No Comp-Off Transactions Found
                                            </div>
                                            <div style="font-size:12.5px; margin-top:4px; color:var(--leave-muted);">
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
