<!-- VIEW TIMELINE & DETAILS MODAL -->
<div class="modal fade" id="viewModal{{ $lr->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered leave-modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg leave-modal-content">
            
            <!-- Dynamic DB Branded Header (Primary to Secondary Color Gradient) -->
            <div class="modal-header text-white px-3 px-sm-4 py-3 align-items-center justify-content-between leave-modal-header">
                <div class="d-flex align-items-center" style="gap: 10px; min-width: 0;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); border: 1px solid rgba(255, 255, 255, 0.3); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.12); flex-shrink: 0;">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div style="min-width: 0;">
                        <h5 class="modal-title font-weight-bold text-white mb-0 text-truncate" style="font-size: 15px; letter-spacing: 0.2px;">
                            Leave Request Details
                        </h5>
                        <div class="text-white-50 text-truncate" style="font-size: 10.5px; font-weight: 500; opacity: 0.92;">
                            Request ID: #LR-{{ str_pad($lr->id, 4, '0', STR_PAD_LEFT) }} &bull; Submitted {{ !empty($lr->created_at) ? \Carbon\Carbon::parse($lr->created_at)->format('d M Y') : '—' }}
                        </div>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-10 border-0 ml-2" style="width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 18px; outline: none; line-height: 1; flex-shrink: 0;" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Scrollable Modal Body -->
            <div class="modal-body px-3 px-sm-4 py-3" style="overflow-y: auto; overflow-x: hidden; flex: 1; background: #F8FAFC;">
                
                <!-- Symmetrical Responsive Information Grid -->
                <div class="leave-modal-info-grid mb-3">
                    
                    <!-- Employee Card with Dynamic DB Gradient Avatar -->
                    <div class="leave-info-tile">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13.5px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(75,0,232,0.25);">
                            {{ strtoupper(substr($lr->display_name ?? 'E', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden" style="min-width: 0;">
                            <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">EMPLOYEE</div>
                            <div class="font-weight-bold text-dark text-truncate" style="font-size: 13px; line-height: 1.2;" title="{{ $lr->display_name }}">{{ $lr->display_name }}</div>
                            <div class="text-muted font-weight-bold text-truncate" style="font-size: 10px;">{{ $lr->employee_code }}</div>
                        </div>
                    </div>

                    <!-- Department & Designation -->
                    <div class="leave-info-tile">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(75, 0, 232, 0.08); color: var(--orb-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="overflow-hidden" style="min-width: 0;">
                            <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">DEPARTMENT & DESIGNATION</div>
                            <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px; line-height: 1.2;" title="{{ $lr->department_name ?? 'General' }}">{{ $lr->department_name ?? 'General' }}</div>
                            <div class="text-muted font-weight-bold text-truncate" style="font-size: 10.5px;" title="{{ $lr->designation_name ?? 'Employee' }}">{{ $lr->designation_name ?? 'Employee' }}</div>
                        </div>
                    </div>

                    <!-- Leave Type -->
                    <div class="leave-info-tile">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #F3E8FF; color: #9333EA; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="overflow-hidden" style="min-width: 0;">
                            <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">LEAVE TYPE</div>
                            <div class="mt-0.5">
                                <span class="badge font-weight-bold px-2 py-0.5 text-truncate" style="border-radius: 6px; font-size: 10.5px; background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE; max-width: 100%;">
                                    {{ $ltName }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Period -->
                    <div class="leave-info-tile">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEF2F2; color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="far fa-calendar-alt"></i>
                        </div>
                        <div class="overflow-hidden" style="min-width: 0;">
                            <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">LEAVE PERIOD</div>
                            <div class="font-weight-bold text-dark mt-0.5" style="font-size: 12px; line-height: 1.2;">
                                @if($isSingleDay)
                                    {{ $startDateFormatted }}
                                @else
                                    {{ $startDateFormatted }} — {{ $endDateFormatted }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Duration & Session -->
                    <div class="leave-info-tile">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="far fa-clock"></i>
                        </div>
                        <div class="overflow-hidden" style="min-width: 0;">
                            <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">DURATION &amp; SESSION</div>
                            <div class="font-weight-bold text-success mt-0.5" style="font-size: 12.5px; line-height: 1.2;">
                                {{ $daysText }}
                            </div>
                            @if(!empty($lr->is_half_day) || !empty($lr->half_day_type) || ($daysVal > 0 && $daysVal < 1))
                                <div class="mt-1">
                                    <span class="badge badge-pill font-weight-bold" style="background: rgba(14, 165, 233, 0.12); color: #0284C7; border: 1px solid rgba(14, 165, 233, 0.28); font-size: 9.5px; padding: 2px 6px;">
                                        <i class="far fa-clock mr-0.5"></i> {{ ($lr->half_day_type ?? '') === 'second_half' ? 'Second Half (Afternoon)' : 'First Half (Morning)' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Reporting Manager -->
                    <div class="leave-info-tile">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="overflow-hidden" style="min-width: 0;">
                            <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">REPORTING MANAGER</div>
                            <div class="font-weight-bold text-dark mt-0.5 text-truncate" style="font-size: 12px; line-height: 1.2;" title="{{ $lr->reporting_manager_name ?? '— Not Assigned' }}">
                                {{ $lr->reporting_manager_name ?? '— Not Assigned' }}
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sandwich & Half Day Detailed Explanation Banner -->
                @if(!empty($lr->sandwich_applied) || !empty($lr->is_half_day))
                    @php
                        $calcDates = collect($lr->dates ?? []);
                        $sandwichDateRows = $calcDates->where('is_sandwich_day', 1);
                        $workingDateRows = $calcDates->where('is_sandwich_day', 0)->where('deduct_as_leave', 1);
                        $workingDaysTotal = $workingDateRows->sum(fn($d) => (float)($d->paid_day + $d->sick_day + $d->comp_off_day + $d->lwp_day));
                        if ($workingDaysTotal <= 0) {
                            $workingDaysTotal = !empty($lr->is_half_day) ? 0.5 : (float)($lr->requested_days ?? 1);
                        }
                        $sandwichDaysTotal = $sandwichDateRows->sum(fn($d) => (float)($d->paid_day + $d->sick_day + $d->comp_off_day + $d->lwp_day));
                        if ($sandwichDaysTotal <= 0 && !empty($lr->sandwich_applied)) {
                            $sandwichDaysTotal = max(0, (float)($lr->deducted_days ?? 0) - $workingDaysTotal);
                        }
                    @endphp
                    <div class="mb-3 p-3 rounded-lg border" style="background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%); border-color: #FDE68A !important; border-radius: 10px; box-shadow: 0 1px 3px rgba(217, 119, 6, 0.08);">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap" style="gap: 6px;">
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <i class="fas fa-layer-group text-warning" style="font-size: 13px; color: #D97706 !important;"></i>
                                <span class="font-weight-bold text-dark" style="font-size: 12px; letter-spacing: 0.3px;">
                                    {{ !empty($lr->sandwich_applied) ? 'Sandwich Rule & Deduction Split' : 'Half Day Application Summary' }}
                                </span>
                            </div>
                            @if(!empty($lr->is_half_day))
                                <span class="badge badge-info font-weight-bold" style="background: #0284C7; color: #FFF; font-size: 10px; border-radius: 6px; padding: 3px 8px;">
                                    <i class="far fa-clock mr-1"></i> {{ ($lr->half_day_type ?? '') === 'second_half' ? 'Second Half (Afternoon Session)' : 'First Half (Morning Session)' }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center flex-wrap" style="gap: 12px; font-size: 12px; color: #92400E;">
                            <div>
                                <strong>Leave Applied:</strong>
                                <span class="text-dark font-weight-bold">
                                    {{ $workingDaysTotal }} {{ \Illuminate\Support\Str::plural('Day', $workingDaysTotal) }}
                                    @if(!empty($lr->is_half_day))
                                        ({{ ($lr->half_day_type ?? '') === 'second_half' ? '2nd Half' : '1st Half' }})
                                    @endif
                                </span>
                            </div>
                            @if(!empty($lr->sandwich_applied))
                                <div>+</div>
                                <div>
                                    <strong>Sandwich Days:</strong>
                                    <span class="text-danger font-weight-bold">{{ $sandwichDaysTotal }} {{ \Illuminate\Support\Str::plural('Day', $sandwichDaysTotal) }}</span>
                                </div>
                                <div>=</div>
                            @else
                                <div>&bull;</div>
                            @endif
                            <div>
                                <strong>Total Deducted:</strong>
                                <span class="text-success font-weight-bold" style="font-size: 13px;">{{ (float)($lr->deducted_days ?? $daysVal) }} {{ \Illuminate\Support\Str::plural('Day', (float)($lr->deducted_days ?? $daysVal)) }}</span>
                            </div>
                        </div>

                        @if($sandwichDateRows->isNotEmpty())
                            <div class="mt-2 pt-2 border-top small" style="border-color: rgba(217, 119, 6, 0.2) !important; font-size: 11px; color: #B45309;">
                                <i class="fas fa-info-circle mr-1"></i> Non-working day(s) included under sandwich policy:
                                <strong>{{ $sandwichDateRows->map(fn($d) => \Carbon\Carbon::parse($d->leave_date)->format('d M Y') . ($d->is_holiday ? ' (Holiday)' : ($d->is_weekoff ? ' (Week-off)' : '')))->implode(', ') }}</strong>.
                            </div>
                        @elseif(!empty($lr->sandwich_applied))
                            <div class="mt-2 pt-2 border-top small" style="border-color: rgba(217, 119, 6, 0.2) !important; font-size: 11px; color: #B45309;">
                                <i class="fas fa-info-circle mr-1"></i> Sandwich Policy applied between leave/holiday intervals (0.5 day deducted as per policy).
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Leave Quota & Paid vs LWP Balance Breakdown -->
                <div class="mb-3 p-3 rounded-lg border bg-white" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03);">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap" style="gap: 6px;">
                        <div class="d-flex align-items-center" style="gap: 6px;">
                            <i class="fas fa-calculator text-primary" style="font-size: 11px;"></i>
                            <span class="text-muted font-weight-bold uppercase" style="font-size: 9.5px; letter-spacing: 0.5px; color: #475569;">LEAVE BALANCE &amp; QUOTA BREAKDOWN</span>
                        </div>
                        @if((float)($lr->lwp_days ?? 0) > 0)
                            <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5;">
                                <i class="fas fa-exclamation-triangle mr-1"></i> {{ (float)$lr->lwp_days }} {{ \Illuminate\Support\Str::plural('Day', (float)$lr->lwp_days) }} LWP Impact
                            </span>
                        @else
                            <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #ECFDF5; color: #15803D; border: 1px solid #86EFAC;">
                                <i class="fas fa-check-circle mr-1"></i> Fully Paid Leave
                            </span>
                        @endif
                    </div>

                    <div class="leave-balance-breakdown-grid">
                        <!-- Paid Days in this Request -->
                        <div class="leave-balance-tile">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #ECFDF5; color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="overflow-hidden" style="min-width: 0;">
                                <div class="text-muted font-weight-bold uppercase text-truncate" style="font-size: 8.5px; letter-spacing: 0.4px;">PAID DAYS</div>
                                <div class="font-weight-bold text-success text-truncate" style="font-size: 13px; line-height: 1.1;">
                                    {{ (float)($lr->paid_days ?? 0) }} {{ \Illuminate\Support\Str::plural('Day', (float)($lr->paid_days ?? 0)) }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 9.5px;">Salary Protected</div>
                            </div>
                        </div>

                        <!-- LWP / Unpaid Days in this Request -->
                        <div class="leave-balance-tile" style="{{ (float)($lr->lwp_days ?? 0) > 0 ? 'border-color: #FCA5A5; background: #FFFBFB;' : '' }}">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: {{ (float)($lr->lwp_days ?? 0) > 0 ? '#FEE2E2' : '#F1F5F9' }}; color: {{ (float)($lr->lwp_days ?? 0) > 0 ? '#DC2626' : '#64748B' }}; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div class="overflow-hidden" style="min-width: 0;">
                                <div class="text-muted font-weight-bold uppercase text-truncate" style="font-size: 8.5px; letter-spacing: 0.4px;">LWP (UNPAID)</div>
                                <div class="font-weight-bold {{ (float)($lr->lwp_days ?? 0) > 0 ? 'text-danger' : 'text-dark' }} text-truncate" style="font-size: 13px; line-height: 1.1;">
                                    {{ (float)($lr->lwp_days ?? 0) }} {{ \Illuminate\Support\Str::plural('Day', (float)($lr->lwp_days ?? 0)) }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 9.5px;">{{ (float)($lr->lwp_days ?? 0) > 0 ? 'Loss of Pay' : '0 Unpaid Days' }}</div>
                            </div>
                        </div>

                        <!-- Current Available Paid Balance -->
                        <div class="leave-balance-tile">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="overflow-hidden" style="min-width: 0;">
                                <div class="text-muted font-weight-bold uppercase text-truncate" style="font-size: 8.5px; letter-spacing: 0.4px;">AVAILABLE PAID</div>
                                <div class="font-weight-bold text-primary text-truncate" style="font-size: 13px; line-height: 1.1;">
                                    {{ (float)($lr->paid_remaining ?? 0) }} {{ \Illuminate\Support\Str::plural('Day', (float)($lr->paid_remaining ?? 0)) }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 9.5px;">Available balance when request was loaded</div>
                            </div>
                        </div>

                        <!-- Annual Remaining (Paid & Sick) -->
                        <div class="leave-balance-tile">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #F3E8FF; color: #9333EA; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="overflow-hidden" style="min-width: 0; width: 100%;">
                                <div class="text-muted font-weight-bold uppercase text-truncate" style="font-size: 8.5px; letter-spacing: 0.4px;">ANNUAL REMAINING</div>
                                <div class="d-flex align-items-center flex-wrap" style="gap: 4px; margin-top: 2px;">
                                    <span class="badge font-weight-bold px-1.5 py-0.5" style="border-radius: 5px; font-size: 9.5px; background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;" title="Annual Paid Leave (Remaining / Allocated)">
                                        Paid: {{ (float)($lr->paid_remaining ?? 0) }}/{{ (float)($lr->paid_allocated ?? 0) }}
                                    </span>
                                    <span class="badge font-weight-bold px-1.5 py-0.5" style="border-radius: 5px; font-size: 9.5px; background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5;" title="Annual Sick Leave (Remaining / Allocated)">
                                        Sick: {{ (float)($lr->sick_remaining ?? 0) }}/{{ (float)($lr->sick_allocated ?? 0) }}
                                    </span>
                                    @if((float)($lr->comp_off_allocated ?? $lr->comp_off_remaining ?? 0) > 0)
                                        <span class="badge font-weight-bold px-1.5 py-0.5" style="border-radius: 5px; font-size: 9px; background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;" title="Comp-Off Remaining">
                                            Comp: {{ (float)($lr->comp_off_remaining ?? 0) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Schedule & Breakdown Table -->
                @if(!empty($lr->dates) && count($lr->dates) > 0)
                    <div class="mb-3 p-3 rounded-lg border bg-white" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <i class="fas fa-calendar-alt text-primary" style="font-size: 11px;"></i>
                                <span class="text-muted font-weight-bold uppercase" style="font-size: 9.5px; letter-spacing: 0.5px; color: #475569;">DAY-WISE CALCULATION AUDIT</span>
                            </div>
                            <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 10px;">{{ count($lr->dates) }} Day(s) Breakdown</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 11.5px;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th style="font-weight: 700;">Date</th>
                                        <th style="font-weight: 700;">Day</th>
                                        <th style="font-weight: 700;">Classification</th>
                                        <th style="font-weight: 700;">Session</th>
                                        <th class="text-center" style="font-weight: 700;">Deduction</th>
                                        <th style="font-weight: 700;">Quota Impact</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lr->dates as $dt)
                                        @php
                                            $isSand = (bool)($dt->is_sandwich_day ?? false);
                                            $isHol = (bool)($dt->is_holiday ?? false);
                                            $isWoff = (bool)($dt->is_weekoff ?? false);
                                            $dDed = (float)($dt->paid_day + $dt->sick_day + $dt->comp_off_day + $dt->lwp_day);
                                            $qParts = [];
                                            if ((float)$dt->paid_day > 0) $qParts[] = 'PL ' . (float)$dt->paid_day;
                                            if ((float)$dt->sick_day > 0) $qParts[] = 'SL ' . (float)$dt->sick_day;
                                            if ((float)$dt->comp_off_day > 0) $qParts[] = 'Comp ' . (float)$dt->comp_off_day;
                                            if ((float)$dt->lwp_day > 0) $qParts[] = 'LWP ' . (float)$dt->lwp_day;
                                        @endphp
                                        <tr style="{{ $isSand ? 'background: #FFFBEB;' : '' }}">
                                            <td class="font-weight-bold text-dark">
                                                {{ \Carbon\Carbon::parse($dt->leave_date)->format('d M Y') }}
                                            </td>
                                            <td class="text-muted">{{ $dt->day_name }}</td>
                                            <td>
                                                @if($isSand)
                                                    <span class="badge badge-warning font-weight-bold" style="background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                                        🥪 Sandwich {{ $isHol ? '(Holiday)' : ($isWoff ? '(Week-off)' : '') }}
                                                    </span>
                                                @elseif(!$dt->is_working_day)
                                                    <span class="badge badge-light border text-muted">{{ $isHol ? 'Holiday' : ($isWoff ? 'Week-off' : 'Off-Day') }}</span>
                                                @else
                                                    <span class="badge badge-success font-weight-bold" style="background: #DCFCE7; color: #15803D;">Working Day</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($lr->is_half_day) && !$isSand)
                                                    <span class="badge badge-info font-weight-bold" style="background: #E0F2FE; color: #0369A1;">
                                                        {{ ($lr->half_day_type ?? '') === 'second_half' ? '2nd Half (PM)' : '1st Half (AM)' }}
                                                    </span>
                                                @elseif($isSand && !empty($lr->is_half_day))
                                                    <span class="text-muted small">0.5 Day (Sandwich)</span>
                                                @else
                                                    <span class="text-muted small">Full Day</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-weight-bold {{ $dDed > 0 ? 'text-primary' : 'text-muted' }}">
                                                {{ $dDed > 0 ? ($dDed . ' Day') : '0' }}
                                            </td>
                                            <td class="font-weight-bold text-dark small">
                                                {{ !empty($qParts) ? implode(', ', $qParts) : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Attached Supporting / Medical Document -->
                @if(!empty($lr->attachment_path))
                    <div class="mb-3 p-3 rounded-lg border bg-white" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(75, 0, 232, 0.08); color: var(--orb-primary); display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                    <i class="fas fa-file-medical"></i>
                                </div>
                                <div>
                                    <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.4px;">ATTACHED SUPPORTING / MEDICAL DOCUMENT</div>
                                    <div class="font-weight-bold text-dark" style="font-size: 12.5px;">Supporting File Attached</div>
                                </div>
                            </div>
                            <a href="{{ route('leave-requests.attachment', $lr->id) }}" target="_blank" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 8px; padding: 6px 14px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fas fa-paperclip"></i>
                                <span>View Document</span>
                                <i class="fas fa-external-link-alt ml-0.5" style="font-size: 10px;"></i>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Sleek Approval Pipeline Stage Tracker -->
                <div class="leave-stage-pipeline-box mb-3">
                    
                    <!-- Pipeline Stages -->
                    <div class="d-flex align-items-center flex-wrap leave-stage-items-wrap" style="gap: 8px;">
                        <div class="d-flex align-items-center" style="gap: 5px;">
                            <i class="fas fa-layer-group text-muted" style="font-size: 11px;"></i>
                            <span class="text-muted font-weight-bold uppercase" style="font-size: 9.5px; letter-spacing: 0.5px; color: #64748B;">STAGES:</span>
                        </div>

                        <!-- Manager Stage -->
                        <div class="d-flex align-items-center px-2.5 py-1 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; gap: 6px;">
                            <span class="text-dark font-weight-bold" style="font-size: 11px;">1. Manager:</span>
                            @if($hrApproved)
                                @if($hasManager && $mgrApproved)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">🟢 Approved</span>
                                @else
                                    <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #F8FAFC; color: #64748B;">⚪ Not Required</span>
                                @endif
                            @elseif($mgrApproved)
                                <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">🟢 Approved</span>
                            @elseif($mgrRejected)
                                <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">🔴 Rejected</span>
                            @else
                                @if($hasManager)
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">🟠 Pending</span>
                                @else
                                    <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #F8FAFC; color: #64748B;">⚪ Not Required</span>
                                @endif
                            @endif
                        </div>

                        <i class="fas fa-arrow-right text-muted opacity-50 leave-stage-arrow"></i>

                        <!-- HR Stage -->
                        <div class="d-flex align-items-center px-2.5 py-1 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; gap: 6px;">
                            <span class="text-dark font-weight-bold" style="font-size: 11px;">2. HR Final:</span>
                            @if($hrApproved)
                                <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">🟢 Approved</span>
                            @elseif($hrRejected)
                                <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">🔴 Rejected</span>
                            @elseif($stLower === 'pending')
                                @if($hasManager && !$mgrApproved)
                                    <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #F8FAFC; color: #64748B;">⚪ Waiting</span>
                                @else
                                    <span class="badge font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #EEF2FF; color: var(--orb-primary); border: 1px solid #C7D2FE;">🔵 Action Required</span>
                                @endif
                            @else
                                <span class="badge border font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 9.5px; background: #F8FAFC; color: #64748B;">⚪ Waiting</span>
                            @endif
                        </div>
                    </div>

                    <!-- Overall Status Badge -->
                    <div class="d-flex align-items-center" style="gap: 6px;">
                        <span class="text-muted font-weight-bold uppercase" style="font-size: 9.5px; letter-spacing: 0.5px;">OVERALL:</span>
                        @if($stLower === 'approved')
                            <span class="badge badge-pill font-weight-bold px-3 py-1" style="font-size: 10px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; letter-spacing: 0.3px;">🟢 APPROVED</span>
                        @elseif($stLower === 'void')
                            <span class="badge badge-pill font-weight-bold px-3 py-1" style="font-size: 10px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; letter-spacing: 0.3px;">⚪ NULL & VOID</span>
                        @elseif($stLower === 'expired')
                            <span class="badge badge-pill font-weight-bold px-3 py-1" style="font-size: 10px; background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1; letter-spacing: 0.3px;">⚪ EXPIRED</span>
                        @elseif($stLower === 'rejected' || $stLower === 'cancelled')
                            <span class="badge badge-pill font-weight-bold px-3 py-1" style="font-size: 10px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; letter-spacing: 0.3px;">🔴 REJECTED</span>
                        @elseif($stLower === 'pending')
                            @if($hasManager && !$mgrApproved)
                                <span class="badge badge-pill font-weight-bold px-3 py-1" style="font-size: 10px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; letter-spacing: 0.3px;">🟠 PENDING MANAGER</span>
                            @else
                                <span class="badge badge-pill font-weight-bold px-3 py-1" style="font-size: 10px; background: #EEF2FF; color: var(--orb-primary); border: 1px solid #C7D2FE; letter-spacing: 0.3px;">🔵 PENDING HR</span>
                            @endif
                        @else
                            <span class="badge badge-pill font-weight-bold px-3 py-1" style="font-size: 10px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; letter-spacing: 0.3px;">🟠 {{ strtoupper($stLower ?: 'PENDING') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Reason for Leave Callout with DB Primary Color Accent -->
                @if(!empty($lr->reason))
                    <div class="mb-3 p-3 rounded-lg border bg-white" style="border-left: 4px solid var(--orb-primary) !important; border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03);">
                        <div class="d-flex align-items-center mb-1" style="gap: 6px;">
                            <i class="fas fa-quote-left opacity-60" style="font-size: 11px; color: var(--orb-primary);"></i>
                            <span class="text-muted font-weight-bold uppercase" style="font-size: 9.5px; letter-spacing: 0.4px;">REASON FOR LEAVE</span>
                        </div>
                        <div class="font-italic text-dark px-1" style="font-size: 12.5px; line-height: 1.45; color: #1E293B; word-break: break-word;">
                            "{{ $lr->reason }}"
                        </div>
                    </div>
                @endif

                <!-- Approval Workflow Timeline with Clean Card Highlights -->
                <div class="mb-1 p-3 rounded-lg border bg-white" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03);">
                    <div class="d-flex align-items-center mb-2.5" style="gap: 6px;">
                        <i class="fas fa-stream" style="font-size: 11px; color: var(--orb-primary);"></i>
                        <span class="text-muted font-weight-bold uppercase" style="font-size: 10px; letter-spacing: 0.5px;">APPROVAL WORKFLOW TIMELINE</span>
                    </div>
                    
                    <div class="approval-timeline" style="position: relative; padding-left: 24px;">
                        <!-- Connecting vertical line -->
                        <div style="position: absolute; top: 12px; bottom: 12px; left: 10px; width: 2px; background: #E2E8F0;"></div>

                        <!-- Step 1: Submission -->
                        <div class="timeline-step" style="position: relative; margin-bottom: 12px;">
                            <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid #10B981; border-radius: 8px;">
                                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                    <strong class="text-dark font-weight-bold" style="font-size: 12.5px;">Leave Request Submitted</strong>
                                    <span class="badge badge-light border text-success font-weight-bold" style="font-size: 9.5px;">✓ Completed</span>
                                </div>
                                <div class="text-muted mt-0.5" style="font-size: 10.5px; word-break: break-word;">Submitted by <strong>{{ $lr->display_name }}</strong> &bull; {{ !empty($lr->created_at) ? \Carbon\Carbon::parse($lr->created_at)->format('d M Y, h:i A') : '—' }}</div>
                            </div>
                        </div>

                        <!-- Step 2: Manager Approval -->
                        <div class="timeline-step" style="position: relative; margin-bottom: 12px;">
                            @if(!$hasManager)
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #94A3B8; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px;">
                                    <i class="fas fa-minus"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid #94A3B8; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="text-muted font-weight-bold" style="font-size: 12.5px;">— Manager Approval Not Required</strong>
                                        <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 9.5px;">Bypassed</span>
                                    </div>
                                    <div class="text-muted mt-0.5" style="font-size: 10.5px;">No Reporting Manager assigned to employee</div>
                                </div>
                            @elseif($mgrApproved)
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid #10B981; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="text-dark font-weight-bold" style="font-size: 12.5px;">✓ Manager Approved</strong>
                                        <span class="badge badge-light border text-success font-weight-bold" style="font-size: 9.5px;">✓ Approved</span>
                                    </div>
                                    <div class="text-muted mt-0.5" style="font-size: 10.5px; word-break: break-word;">Approved by <strong>{{ $lr->manager_approver_name ?? 'Reporting Manager' }}</strong> @if(!empty($lr->manager_approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->manager_approved_at)->format('d M Y, h:i A') }} @endif</div>
                                    @if(!empty($lr->manager_note))
                                        <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #F8FAFC; padding: 4px 8px; border-radius: 4px; border: 1px solid #E2E8F0; word-break: break-word;">Note: "{{ $lr->manager_note }}"</div>
                                    @endif
                                </div>
                            @elseif($mgrRejected)
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #EF4444; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #FCA5A5; border-left: 3px solid #EF4444; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="text-danger font-weight-bold" style="font-size: 12.5px;">✕ Manager Rejected</strong>
                                        <span class="badge font-weight-bold" style="background: #FEE2E2; color: #991B1B; font-size: 9.5px;">Rejected</span>
                                    </div>
                                    <div class="text-danger mt-0.5" style="font-size: 10.5px; word-break: break-word;">Rejected by <strong>{{ $lr->rejected_by_name ?? 'Manager' }}</strong> @if(!empty($lr->approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->approved_at)->format('d M Y, h:i A') }} @endif</div>
                                    @if(!empty($lr->rejection_reason))
                                        <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #FEF2F2; padding: 4px 8px; border-radius: 4px; border: 1px solid #FCA5A5; word-break: break-word;">Reason: "{{ $lr->rejection_reason }}"</div>
                                    @endif
                                </div>
                            @else
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #F59E0B; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid #F59E0B; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="text-dark font-weight-bold" style="font-size: 12.5px;"> Manager Approval Pending</strong>
                                        <span class="badge font-weight-bold" style="background: #FEF3C7; color: #92400E; font-size: 9.5px;">Pending Manager</span>
                                    </div>
                                    <div class="text-muted mt-0.5" style="font-size: 10.5px;">Pending Reporting Manager review</div>
                                </div>
                            @endif
                        </div>

                        <!-- Step 3: HR Approval -->
                        <div class="timeline-step" style="position: relative;">
                            @if($hrApproved)
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid #10B981; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="text-dark font-weight-bold" style="font-size: 12.5px;">✓ HR Final Approved</strong>
                                        <span class="badge badge-light border text-success font-weight-bold" style="font-size: 9.5px;">✓ Finalized</span>
                                    </div>
                                    <div class="text-muted mt-0.5" style="font-size: 10.5px; word-break: break-word;">Approved by <strong>{{ $lr->hr_approver_name ?? 'HR Admin' }}</strong> @if(!empty($lr->hr_approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->hr_approved_at)->format('d M Y, h:i A') }} @endif</div>
                                    @if(!empty($lr->hr_note))
                                        <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #F8FAFC; padding: 4px 8px; border-radius: 4px; border: 1px solid #E2E8F0; word-break: break-word;">Note: "{{ $lr->hr_note }}"</div>
                                    @endif
                                </div>
                            @elseif($stLower === 'void')
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #64748B; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.15);">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #CBD5E1; border-left: 3px solid #64748B; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="text-secondary font-weight-bold" style="font-size: 12.5px;">⚪ Null & Void (Reversed)</strong>
                                        <span class="badge font-weight-bold" style="background: #F1F5F9; color: #475569; font-size: 9.5px;">Null & Void</span>
                                    </div>
                                    <div class="text-muted mt-0.5" style="font-size: 10.5px; word-break: break-word;">Voided by <strong>{{ $lr->hr_approver_name ?? $lr->rejected_by_name ?? 'HR Admin' }}</strong> @if(!empty($lr->approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->approved_at)->format('d M Y, h:i A') }} @endif</div>
                                    @if(!empty($lr->hr_note))
                                        <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #F8FAFC; padding: 4px 8px; border-radius: 4px; border: 1px solid #E2E8F0; word-break: break-word;">Void Note: "{{ $lr->hr_note }}"</div>
                                    @elseif(!empty($lr->rejection_reason))
                                        <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #F8FAFC; padding: 4px 8px; border-radius: 4px; border: 1px solid #E2E8F0; word-break: break-word;">Void Note: "{{ $lr->rejection_reason }}"</div>
                                    @endif
                                </div>
                            @elseif($hrRejected)
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #EF4444; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #FCA5A5; border-left: 3px solid #EF4444; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="text-danger font-weight-bold" style="font-size: 12.5px;">✕ HR Rejected</strong>
                                        <span class="badge font-weight-bold" style="background: #FEE2E2; color: #991B1B; font-size: 9.5px;">Rejected</span>
                                    </div>
                                    <div class="text-danger mt-0.5" style="font-size: 10.5px; word-break: break-word;">Rejected by <strong>{{ $lr->rejected_by_name ?? 'HR Admin' }}</strong> @if(!empty($lr->approved_at)) &bull; {{ \Carbon\Carbon::parse($lr->approved_at)->format('d M Y, h:i A') }} @endif</div>
                                    @if(!empty($lr->rejection_reason))
                                        <div class="text-muted small mt-1 italic" style="font-size: 10px; background: #FEF2F2; padding: 4px 8px; border-radius: 4px; border: 1px solid #FCA5A5; word-break: break-word;">Reason: "{{ $lr->rejection_reason }}"</div>
                                    @endif
                                </div>
                            @elseif($mgrApproved || !$hasManager)
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: var(--orb-primary); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 9.5px; box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.25);">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid var(--orb-primary); border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="font-weight-bold" style="font-size: 12.5px; color: var(--orb-primary);">🔵 HR Approval Pending</strong>
                                        <span class="badge font-weight-bold" style="background: #EEF2FF; color: var(--orb-primary); border: 1px solid #C7D2FE; font-size: 9.5px;">Action Required</span>
                                    </div>
                                    <div class="text-muted mt-0.5" style="font-size: 10.5px;">Pending HR approval</div>
                                </div>
                            @else
                                <div style="position: absolute; left: -24px; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; border: 1px solid #CBD5E1; display: flex; align-items: center; justify-content: center; font-size: 9.5px;">
                                    <i class="far fa-circle"></i>
                                </div>
                                <div class="p-2.5 rounded-lg bg-white" style="border: 1px solid #E2E8F0; border-left: 3px solid #CBD5E1; border-radius: 8px;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                        <strong class="text-muted font-weight-bold" style="font-size: 12.5px;">○ HR Approval</strong>
                                        <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 9.5px;">Awaiting Manager</span>
                                    </div>
                                    <div class="text-muted mt-0.5" style="font-size: 10.5px;">Waiting for Manager approval</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <!-- Fixed Responsive Footer with Action Buttons -->
            <div class="modal-footer border-top bg-white px-3 px-sm-4 py-2.5 align-items-center justify-content-between leave-modal-footer">
                <button type="button" class="btn btn-sm btn-light font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; border: 1px solid #CBD5E1; color: #475569; font-size: 12.5px;" data-dismiss="modal">
                    Close
                </button>

                <div class="d-flex align-items-center leave-modal-footer-actions" style="gap: 8px;">
                    @if($stLower === 'pending' && Route::has('leave-approvals.approve') && ($canApprove || $canReject))
                        @if($isSuperAdminUser)
                            <!-- Super Admin Actions -->
                            @if($canReject)
                            <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; font-size: 12.5px;" onclick="$('#viewModal{{ $lr->id }}').modal('hide'); setTimeout(function(){ $('#rejectModal{{ $lr->id }}').modal('show'); }, 350);">
                                <i class="fas fa-times mr-1"></i> Reject Request
                            </button>
                            @endif
                            @if($canApprove)
                            <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm text-white font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.3); font-size: 12.5px;" onclick="return confirm('Super Admin Override: Approve leave request for {{ addslashes($lr->display_name) }}?')">
                                    <i class="fas fa-crown mr-1 text-warning"></i> Super Admin Approve
                                </button>
                            </form>
                            @endif
                        @elseif($hasManager && !$mgrApproved)
                            @if($isAssignedManager)
                                @if($canReject)
                                <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; font-size: 12.5px;" onclick="$('#viewModal{{ $lr->id }}').modal('hide'); setTimeout(function(){ $('#rejectModal{{ $lr->id }}').modal('show'); }, 350);">
                                    <i class="fas fa-times mr-1"></i> Reject Request
                                </button>
                                @endif
                                @if($canApprove)
                                <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm text-white font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.3); font-size: 12.5px;" onclick="return confirm('Approve leave request at Manager stage for {{ addslashes($lr->display_name) }}?')">
                                        <i class="fas fa-check mr-1"></i> Approve Request
                                    </button>
                                </form>
                                @endif
                            @elseif($isHrAdminUser)
                                @if($canReject)
                                <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; font-size: 12.5px;" onclick="$('#viewModal{{ $lr->id }}').modal('hide'); setTimeout(function(){ $('#rejectModal{{ $lr->id }}').modal('show'); }, 350);">
                                    <i class="fas fa-times mr-1"></i> Reject Request
                                </button>
                                @endif
                                @if($canApprove)
                                <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm text-white font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.3); font-size: 12.5px;" onclick="return confirm('HR Admin Direct Approval: Approve leave request for {{ addslashes($lr->display_name) }}?')">
                                        <i class="fas fa-check-double mr-1"></i> HR Admin Approve
                                    </button>
                                </form>
                                @endif
                            @else
                                <span class="badge border font-weight-bold px-3 py-1.5" style="border-radius: 8px; background: #FFFBEB; color: #D97706; border-color: #FDE68A !important; font-size: 11px;">
                                    <i class="fas fa-clock mr-1"></i> Waiting for Reporting Manager
                                </span>
                            @endif
                        @else
                            <!-- HR Stage (No Manager OR Manager HAS Approved) -->
                            @if($canReject)
                            <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FEF2F2; color: #DC2626; border: 1px solid #FCA5A5; font-size: 12.5px;" onclick="$('#viewModal{{ $lr->id }}').modal('hide'); setTimeout(function(){ $('#rejectModal{{ $lr->id }}').modal('show'); }, 350);">
                                <i class="fas fa-times mr-1"></i> Reject Request
                            </button>
                            @endif
                            @if($canApprove)
                            <form method="POST" action="{{ route('leave-approvals.approve', $lr->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm text-white font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.3); font-size: 12.5px;" onclick="return confirm('Perform final HR approval & deduct leave balance for {{ addslashes($lr->display_name) }}?')">
                                    <i class="fas fa-check-double mr-1"></i> HR Approve & Finalize
                                </button>
                            </form>
                            @endif
                        @endif
                    @elseif($stLower === 'approved' && (($isSuperAdminUser ?? false) || ($isHrAdminUser ?? false) || (auth()->check() && (in_array((int)(auth()->user()->system_role_id ?? auth()->user()->role_id ?? 0), [1, 2, 3], true) || (method_exists(auth()->user(), 'hasPermission') && (auth()->user()->hasPermission('leave.approvals.view_all') || auth()->user()->hasPermission('leave.approvals.approve')))))) && Route::has('leave-approvals.void'))
                        <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FFF1F2; color: #E11D48; border: 1px solid #FECDD3; font-size: 12.5px;" onclick="var vm = $('#viewModal{{ $lr->id }}'); vm.one('hidden.bs.modal', function(){ $('#voidModal{{ $lr->id }}').modal('show'); }); vm.modal('hide');">
                            <i class="fas fa-ban mr-1"></i> Make Null & Void
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

