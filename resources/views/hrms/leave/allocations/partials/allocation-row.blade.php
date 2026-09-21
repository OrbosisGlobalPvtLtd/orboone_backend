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
    <td class="text-center"><strong>{{ $loop->iteration }}</strong></td>

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

    <td class="text-center">
        <div class="leave-metric">{{ number_format((float) $allocation->total_allocated, 2) }}</div>
        <div class="leave-breakdown-box">
            <span class="leave-breakdown-item badge-paid">Paid: <span>{{ number_format((float)$allocation->paid_allocated, 2) }}</span></span>
            <span class="leave-breakdown-item badge-sick">Sick: <span>{{ number_format((float)$allocation->sick_allocated, 2) }}</span></span>
        </div>
    </td>

    <td class="text-center">
        <div class="leave-metric">{{ number_format((float) $allocation->total_used, 2) }}</div>
        <div class="leave-breakdown-box">
            <span class="leave-breakdown-item badge-paid">Paid: <span>{{ number_format((float)$allocation->paid_used, 2) }}</span></span>
            <span class="leave-breakdown-item badge-sick">Sick: <span>{{ number_format((float)$allocation->sick_used, 2) }}</span></span>
        </div>
        @if((float) $allocation->lwp_used > 0)
            <div class="mt-1">
                <span class="leave-pill pill-lwp">
                    <i class="fas fa-exclamation-circle"></i> LWP: {{ number_format((float) $allocation->lwp_used, 2) }}
                </span>
            </div>
        @endif
    </td>

    <td class="text-center">
        <div class="leave-metric text-success">{{ number_format((float) $allocation->total_remaining, 2) }}</div>
        <div class="leave-breakdown-box">
            <span class="leave-breakdown-item">Paid Rem: <span>{{ number_format((float)$allocation->paid_remaining, 2) }}</span></span>
            <span class="leave-breakdown-item">Sick Rem: <span>{{ number_format((float)$allocation->sick_remaining, 2) }}</span></span>
            <span class="leave-breakdown-item" style="background:#F0F9FF; border-color:#B2DDFF; color:#026AA7;">Comp Rem: <span style="color:#026AA7;">{{ number_format((float)$allocation->comp_off_remaining, 2) }}</span></span>
        </div>
    </td>

    <td class="text-center">
        @php
            $emp = $allocation->employee;
            $stg = strtolower($allocation->employment_stage ?? optional($emp)->employee_stage ?? optional($emp)->employment_type ?? '');
            $isIntern = str_contains($stg, 'intern');
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
        @else
            @php
                $mQuota = (float) $allocation->monthly_quota;
                $mCarry = (float) $allocation->monthly_carry_forward;
                $mUsed = (float) $allocation->monthly_used_this_month;
                $mUsedFromCarry = min($mUsed, $mCarry);
                $mRemCarry = max(0.0, $mCarry - $mUsedFromCarry);
                $mUsedFromQuota = max(0.0, $mUsed - $mUsedFromCarry);
                $mRemQuota = max(0.0, $mQuota - $mUsedFromQuota);
            @endphp
            <div class="leave-breakdown-box" style="font-size:11px; line-height: 1.6;">
                <div>
                    <span class="badge badge-success px-2 py-1" style="border-radius:6px; font-weight:900; font-size:11px; background:#12B76A; color:#fff;">
                        <i class="fas fa-coins mr-1"></i> Rem Paid: {{ number_format((float)$allocation->total_monthly_remaining_paid, 2) }}
                    </span>
                </div>
                <div class="mt-1" style="font-size:10px; font-weight:700; color:var(--leave-muted);">
                    Quota Rem: <strong class="text-success">{{ number_format($mRemQuota, 2) }}</strong> | Carry Rem: <strong class="text-danger">{{ number_format($mRemCarry, 2) }}</strong> | Used: <strong>{{ number_format($mUsed, 2) }}</strong>
                </div>
            </div>
        @endif
    </td>
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

    <td class="text-center">
        <div class="leave-action-wrap justify-content-center">
            <button type="button"
                class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center btn-edit-allocation"
                style="border-radius:10px; width:34px; height:34px; padding:0; box-shadow: 0 4px 10px rgba(75,0,232,0.15);"
                data-id="{{ $allocation->id }}"
                data-url="{{ route('leave-allocations.show', $allocation->id) }}"
                title="Edit Record">
                <i class="fas fa-edit" style="font-size:13px;"></i>
            </button>
            <button type="button"
                class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center ml-1 btn-delete-allocation"
                style="border-radius:10px; width:34px; height:34px; padding:0; box-shadow: 0 4px 10px rgba(220,53,69,0.15);"
                data-id="{{ $allocation->id }}"
                data-name="{{ $employeeName }}"
                data-year="{{ $allocation->year }}"
                data-stage="{{ ucfirst($allocation->employment_stage ?? '') }}"
                data-action="{{ route('leave-allocations.destroy', $allocation->id) }}"
                title="Delete Record">
                <i class="fas fa-trash-alt" style="font-size:13px;"></i>
            </button>
        </div>
    </td>
</tr>
