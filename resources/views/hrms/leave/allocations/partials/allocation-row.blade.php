@php
    $employeeName = optional($allocation->employee)->display_name
        ?? optional(optional($allocation->employee)->user)->name
        ?? 'Unknown Employee';

    $employeeCode = optional($allocation->employee)->employee_code
        ?? optional($allocation->employee)->code
        ?? 'EMP';

    $initial = strtoupper(substr(trim($employeeName), 0, 1));

    $rowStage = strtolower($allocation->employment_stage ?? '');
    $rowPolicy = strtolower(optional($allocation->policy)->policy_name ?? 'default leave policy');
    $currentSystemYear = (int) date('Y');
    $ledgerSummary = $ledgerSummary ?? ($ledgerDisplaySummaries[$allocation->id] ?? null);
    if ($allocation->year < $currentSystemYear || $allocation->is_locked) {
        $rowStatus = 'inactive';
    } elseif ($allocation->year > $currentSystemYear) {
        $rowStatus = 'upcoming';
    } else {
        $rowStatus = 'active';
    }
    $rowSearch = strtolower($employeeName . ' ' . $employeeCode . ' ' . $allocation->year);
@endphp

<tr data-stage="{{ $rowStage }}"
    data-policy="{{ $rowPolicy }}"
    data-status="{{ $rowStatus }}"
    data-search="{{ $rowSearch }}">
    <td class="text-center font-weight-bold" style="color: var(--leave-text);">
        {{ isset($allocations) && method_exists($allocations, 'currentPage') ? $loop->iteration + ($allocations->currentPage() - 1) * $allocations->perPage() : $loop->iteration }}
    </td>

    {{-- 1. Employee --}}
    <td class="text-left">
        <div class="leave-employee">
            @php
                $passportPhotoUrl = resolveEmployeeAdminAvatar($allocation->employee);
            @endphp
            @if($passportPhotoUrl)
                <div class="leave-avatar">
                    <img src="{{ $passportPhotoUrl }}"
                         alt="{{ $employeeName }}"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <span style="display: none;">{{ $initial }}</span>
                </div>
            @else
                <div class="leave-avatar">{{ $initial }}</div>
            @endif
            <div>
                <div class="leave-employee-name">{{ $employeeName }}</div>
                <div class="leave-employee-meta">{{ $employeeCode }}</div>
                <div class="text-muted mt-1" style="font-size:10px; font-weight: 700;">Year: <strong>{{ $allocation->year }}</strong></div>
            </div>
        </div>
    </td>

    {{-- 2. Stage & Policy --}}
    <td class="text-center">
        <div class="mb-1">
            <span class="leave-pill pill-stage">
                <i class="fas fa-user-clock"></i>
                {{ ucfirst(str_replace('_', ' ', $allocation->employment_stage ?? '-')) }}
            </span>
        </div>
        <div>
            <span class="leave-pill pill-policy">
                <i class="fas fa-shield-alt"></i>
                {{ optional($allocation->policy)->policy_name ?? 'Default Policy' }}
            </span>
        </div>
    </td>

    {{-- 3. Annual Entitlement --}}
    <td class="text-center">
        @php
            $col3Total = (float) ($ledgerSummary['year_end_total'] ?? $allocation->total_allocated);
            $col3Pl = (float) ($ledgerSummary['year_end_pl'] ?? $allocation->paid_allocated);
            $col3Sl = (float) ($ledgerSummary['year_end_sl'] ?? $allocation->sick_allocated);
            $annualTotal = $ledgerSummary ? ((float) ($ledgerSummary['annual_entitlement_pl'] ?? 0) + (float) ($ledgerSummary['annual_entitlement_sl'] ?? 0)) : 0;
            $isProrated = $ledgerSummary && abs($annualTotal - $col3Total) > 0.05;

            $tooltipContent = '';
            if ($ledgerSummary) {
                $lines = [];
                $lines[] = '<strong>' . ($isProrated ? 'Prorated Entitlement' : 'Annual Entitlement') . '</strong>';
                $lines[] = 'Annual policy: PL ' . number_format((float) ($ledgerSummary['annual_entitlement_pl'] ?? 0), 2) . ' | SL ' . number_format((float) ($ledgerSummary['annual_entitlement_sl'] ?? 0), 2);
                $lines[] = 'Accrued to date: PL ' . number_format((float) ($ledgerSummary['accrued_to_date_pl'] ?? 0), 2) . ' | SL ' . number_format((float) ($ledgerSummary['accrued_to_date_sl'] ?? 0), 2);
                if ((float) ($ledgerSummary['opening_balance_pl'] ?? 0) !== 0.0 || (float) ($ledgerSummary['opening_balance_sl'] ?? 0) !== 0.0) {
                    $lines[] = 'Opening: PL ' . number_format((float) $ledgerSummary['opening_balance_pl'], 2) . ' | SL ' . number_format((float) $ledgerSummary['opening_balance_sl'], 2);
                }
                $tooltipContent = implode('<br>', $lines);
            }
        @endphp
        <div class="d-flex align-items-center justify-content-center">
            <span class="leave-metric">{{ number_format($col3Total, 2) }}</span>
            @if($ledgerSummary)
                <span role="button" class="text-muted ml-2" style="cursor: pointer; font-size: 12px; margin-left: 6px; display: inline-flex; align-items: center;"
                      data-toggle="tooltip" data-bs-toggle="tooltip" data-html="true" data-bs-html="true"
                      title="{!! $tooltipContent !!}">
                    <i class="fas fa-info-circle text-primary" style="opacity: 0.8;"></i>
                </span>
            @endif
        </div>
        <div class="leave-breakdown-box">
            <span class="leave-breakdown-item badge-paid">{{ $ledgerSummary ? 'PL' : 'Paid' }}: <span>{{ number_format($col3Pl, 2) }}</span></span>
            <span class="leave-breakdown-item badge-sick">{{ $ledgerSummary ? 'SL' : 'Sick' }}: <span>{{ number_format($col3Sl, 2) }}</span></span>
        </div>
    </td>

    {{-- 4. Annual Remaining Balance --}}
    <td class="text-center">
        @php
            $col4Total = $ledgerSummary ? (float) ($ledgerSummary['year_end_total_remaining'] ?? 0) : (float) $allocation->total_remaining;
            $col4Pl = $ledgerSummary ? (float) ($ledgerSummary['year_end_pl_remaining'] ?? 0) : (float) $allocation->paid_remaining;
            $col4Sl = $ledgerSummary ? (float) ($ledgerSummary['year_end_sl_remaining'] ?? 0) : (float) $allocation->sick_remaining;
        @endphp
        <div class="leave-metric text-success">{{ number_format($col4Total, 2) }}</div>
        <div class="leave-breakdown-box">
            <span class="leave-breakdown-item">{{ $ledgerSummary ? 'PL left' : 'Paid Rem' }}: <span>{{ number_format($col4Pl, 2) }}</span></span>
            <span class="leave-breakdown-item">{{ $ledgerSummary ? 'SL left' : 'Sick Rem' }}: <span>{{ number_format($col4Sl, 2) }}</span></span>
        </div>
    </td>

    {{-- 5. Accrued Till Date --}}
    <td class="text-center">
        @php
            $col5Total = $ledgerSummary ? (float) ($ledgerSummary['total_accrued'] ?? $ledgerSummary['total_earned_to_date'] ?? 0) : (float) ($allocation->paid_allocated + $allocation->sick_allocated);
            $col5Pl = $ledgerSummary ? (float) ($ledgerSummary['pl_accrued'] ?? $ledgerSummary['pl_earned_to_date'] ?? 0) : (float) $allocation->paid_allocated;
            $col5Sl = $ledgerSummary ? (float) ($ledgerSummary['sl_accrued'] ?? $ledgerSummary['sl_earned_to_date'] ?? 0) : (float) $allocation->sick_allocated;
        @endphp
        <div class="leave-metric">{{ number_format($col5Total, 2) }}</div>
        <div class="leave-breakdown-box">
            <span class="leave-breakdown-item badge-paid">PL: <span>{{ number_format($col5Pl, 2) }}</span></span>
            <span class="leave-breakdown-item badge-sick">SL: <span>{{ number_format($col5Sl, 2) }}</span></span>
        </div>
    </td>

    {{-- 6. Used (YTD) --}}
    <td class="text-center">
        @php
            $col6Pl = (float) ($ledgerSummary['pl_used'] ?? $allocation->paid_used);
            $col6Sl = (float) ($ledgerSummary['sl_used'] ?? $allocation->sick_used);
            $col6Lwp = (float) $allocation->lwp_used;
            $col6TotalPaidUsed = $col6Pl + $col6Sl;
        @endphp
        <div class="leave-metric">{{ number_format($col6TotalPaidUsed, 2) }}</div>
        <div class="leave-breakdown-box">
            <span class="leave-breakdown-item badge-paid">{{ $ledgerSummary ? 'PL' : 'Paid' }}: <span>{{ number_format($col6Pl, 2) }}</span></span>
            <span class="leave-breakdown-item badge-sick">{{ $ledgerSummary ? 'SL' : 'Sick' }}: <span>{{ number_format($col6Sl, 2) }}</span></span>
            <span class="leave-breakdown-item" style="background:#FFF4ED; border-color:#FECDCA; color:#B93815;">LWP: <span style="color:#B93815;">{{ number_format($col6Lwp, 2) }}</span></span>
        </div>
    </td>

    {{-- 7. Available This Month --}}
    <td class="text-center">
        @php
            $emp = $allocation->employee;
            $stg = strtolower($allocation->employment_stage ?? optional($emp)->employee_stage ?? optional($emp)->employment_type ?? '');
            $isIntern = str_contains($stg, 'intern');
            $isStageEmployee = $isIntern || str_contains($stg, 'probation');
            $isUnpaidIntern = false;
            if ($isIntern) {
                if ($emp) {
                    $isUnpaidIntern = ((int)($emp->is_paid_intern ?? 1) === 0) || ((float)($emp->actual_salary ?? 0) <= 0 && (int)($emp->is_paid_intern ?? 0) === 0);
                }
                if ((float)$allocation->paid_allocated <= 0 && (float)$allocation->sick_allocated <= 0) {
                    $isUnpaidIntern = true;
                }
            }
        @endphp
        @if($isUnpaidIntern)
            <div class="d-inline-flex align-items-center justify-content-center">
                <span class="badge px-3 py-1.5" style="border-radius:8px; font-weight:800; font-size:13px; background:#F1F5F9; border:1px solid #CBD5E1; color:#1E293B; letter-spacing:0.3px;">
                    Total {{ (float)$allocation->total_allocated == (int)$allocation->total_allocated ? (int)$allocation->total_allocated : number_format((float)$allocation->total_allocated, 2) }}
                </span>
            </div>
        @elseif($ledgerSummary)
            @php
                $col7Total = (float) ($ledgerSummary['total_paid_available'] ?? $ledgerSummary['total_available'] ?? 0);
                $col7Pl = (float) ($ledgerSummary['pl_available'] ?? 0);
                $col7Sl = (float) ($ledgerSummary['sl_available'] ?? 0);
            @endphp
            <div>
                <span class="badge badge-success px-2 py-1" style="border-radius:6px; font-weight:900; font-size:11px; background:#12B76A; color:#fff;" title="Earned up to current month available balance">
                    <i class="fas fa-coins mr-1"></i> Rem Paid: {{ number_format($col7Total, 2) }}
                </span>
            </div>
            <div class="leave-breakdown-box">
                <span class="leave-breakdown-item badge-paid">PL: <span>{{ number_format($col7Pl, 2) }}</span></span>
                <span class="leave-breakdown-item badge-sick">SL: <span>{{ number_format($col7Sl, 2) }}</span></span>
            </div>
        @elseif($isStageEmployee)
            @php
                $stageRemPaid = (float) $allocation->paid_remaining;
                $stageRemSick = (float) $allocation->sick_remaining;
            @endphp
            <div>
                <span class="badge badge-success px-2 py-1" style="border-radius:6px; font-weight:900; font-size:11px; background:{{ $stageRemPaid > 0 ? '#12B76A' : '#64748B' }}; color:#fff;" title="Available paid leave for current stage">
                    <i class="fas fa-coins mr-1"></i> Rem Paid: {{ number_format($stageRemPaid, 2) }}
                </span>
            </div>
            <div class="leave-breakdown-box">
                <span class="leave-breakdown-item badge-paid">PL: <span>{{ number_format($stageRemPaid, 2) }}</span></span>
                <span class="leave-breakdown-item badge-sick">SL: <span>{{ number_format($stageRemSick, 2) }}</span></span>
            </div>
        @else
            @php
                $fallbackRemPaid = (float) $allocation->paid_remaining;
                $fallbackRemSick = (float) $allocation->sick_remaining;
            @endphp
            <div>
                <span class="badge badge-success px-2 py-1" style="border-radius:6px; font-weight:900; font-size:11px; background:{{ $fallbackRemPaid > 0 ? '#12B76A' : '#64748B' }}; color:#fff;">
                    <i class="fas fa-coins mr-1"></i> Rem Paid: {{ number_format($fallbackRemPaid, 2) }}
                </span>
            </div>
            <div class="leave-breakdown-box">
                <span class="leave-breakdown-item badge-paid">PL: <span>{{ number_format($fallbackRemPaid, 2) }}</span></span>
                <span class="leave-breakdown-item badge-sick">SL: <span>{{ number_format($fallbackRemSick, 2) }}</span></span>
            </div>
        @endif
    </td>

    {{-- 8. Comp-Off --}}
    <td class="text-center">
        @php
            $col9Earned = (float) ($allocation->comp_off_allocated ?? 0);
            $col9Used = (float) ($allocation->comp_off_used ?? 0);
            $col9Rem = (float) ($allocation->comp_off_remaining ?? 0);
        @endphp
        <div class="leave-metric {{ $col9Rem > 0 ? 'text-success' : '' }}" title="Comp-Off Remaining: {{ number_format($col9Rem, 2) }}">
            {{ number_format($col9Rem, 2) }}
        </div>
        <div class="leave-breakdown-box">
            <span class="leave-breakdown-item" style="background:#ECFDF3; border-color:#ABEFC6; color:#067647;">Earned: <span>{{ number_format($col9Earned, 2) }}</span></span>
            <span class="leave-breakdown-item" style="background:#FEF3F2; border-color:#FECDCA; color:#B42318;">Used: <span>{{ number_format($col9Used, 2) }}</span></span>
            <span class="leave-breakdown-item" style="background:#F0F9FF; border-color:#B2DDFF; color:#026AA7;">Rem: <span>{{ number_format($col9Rem, 2) }}</span></span>
        </div>
    </td>

    {{-- 10. Status --}}
    <td class="text-center">
        @if($rowStatus === 'inactive')
            <span class="leave-pill" style="background:#FEE4E2; color:#D92D20; border:1px solid #FECDCA;">
                <i class="fas fa-lock mr-1"></i> Inactive
            </span>
        @elseif($rowStatus === 'upcoming')
            <span class="leave-pill" style="background:#EFF8FF; color:#175CD3; border:1px solid #B2DDFF;">
                <i class="fas fa-calendar-plus mr-1"></i> Upcoming
            </span>
        @else
            <span class="leave-pill" style="background:#E8F5E9; color:#1B5E20; border:1px solid #C8E6C9;">
                <i class="fas fa-check-circle mr-1"></i> Active
            </span>
        @endif
    </td>

    {{-- 11. Actions --}}
    <td class="text-center">
        <div class="leave-action-wrap justify-content-center">
            {{-- <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center"
               href="{{ route('leave-allocations.index', ['year' => $allocation->year, 'tab' => 'monthly', 'employee_id' => $allocation->employee_id]) }}"
               style="border-radius:8px; width:30px; height:30px; padding:0;"
               title="View Monthly Passbook">
                <i class="fas fa-eye" style="font-size:12px;"></i>
            </a> --}}
            <button type="button"
                class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center btn-edit-allocation ml-1"
                style="border-radius:8px; width:30px; height:30px; padding:0; box-shadow:0 4px 10px rgba(75,0,232,.15);"
                data-id="{{ $allocation->id }}"
                data-url="{{ route('leave-allocations.show', $allocation->id) }}"
                data-update-url="{{ route('leave-allocations.update', $allocation->id) }}"
                title="Edit Record">
                <i class="fas fa-edit" style="font-size:12px;"></i>
            </button>
            <a class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center ml-1"
               href="{{ route('leave-allocations.index', ['year' => $allocation->year, 'tab' => 'transactions', 'employee_id' => $allocation->employee_id]) }}"
               style="border-radius:8px; width:30px; height:30px; padding:0;"
               title="View Transactions"
               aria-label="View transactions">
                <i class="fas fa-list-alt" style="font-size:12px;"></i>
            </a>
            <button type="button"
                class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center ml-1 btn-delete-allocation"
                style="border-radius:8px; width:30px; height:30px; padding:0; box-shadow:0 4px 10px rgba(220,53,69,.15);"
                data-name="{{ $employeeName }}"
                data-year="{{ $allocation->year }}"
                data-action="{{ route('leave-allocations.destroy', $allocation->id) }}"
                data-ledger-backed="{{ $allocation->isLedgerBacked() ? '1' : '0' }}"
                title="Delete Record">
                <i class="fas fa-trash-alt" style="font-size:12px;"></i>
            </button>
        </div>
    </td>
</tr>
