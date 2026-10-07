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

                    <!-- Duration -->
                    <div class="leave-info-tile">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="far fa-clock"></i>
                        </div>
                        <div class="overflow-hidden" style="min-width: 0;">
                            <div class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px; color: #64748B;">DURATION</div>
                            <div class="font-weight-bold text-success mt-0.5" style="font-size: 12.5px; line-height: 1.2;">
                                {{ $daysText }}
                            </div>
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

                <!-- Leave Quota & Paid vs LWP Balance Breakdown -->
                <div class="mb-3 p-3 rounded-lg border bg-white" style="border-radius: 10px; border-color: #E2E8F0 !important; box-shadow: 0 1px 3px rgba(15,23,42,0.03);">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap" style="gap: 6px;">
                        <div class="d-flex align-items-center" style="gap: 6px;">
                            <i class="fas fa-calculator text-primary" style="font-size: 11px;"></i>
                            <span class="text-muted font-weight-bold uppercase" style="font-size: 9.5px; letter-spacing: 0.5px; color: #475569;">LEAVE BALANCE & QUOTA BREAKDOWN</span>
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

                        <!-- Current Month Remaining Paid Balance -->
                        <div class="leave-balance-tile">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="overflow-hidden" style="min-width: 0;">
                                <div class="text-muted font-weight-bold uppercase text-truncate" style="font-size: 8.5px; letter-spacing: 0.4px;">MONTH BALANCE</div>
                                <div class="font-weight-bold text-primary text-truncate" style="font-size: 13px; line-height: 1.1;">
                                    {{ (float)($lr->total_monthly_remaining_paid ?? $lr->paid_remaining ?? 0) }} {{ \Illuminate\Support\Str::plural('Day', (float)($lr->total_monthly_remaining_paid ?? $lr->paid_remaining ?? 0)) }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 9.5px;" title="Quota: {{ (float)($lr->monthly_quota ?? 2) }} / mo + {{ (float)($lr->monthly_carry_forward ?? 0) }} C/F">
                                    {{ (float)($lr->monthly_quota ?? 2) }}/mo + {{ (float)($lr->monthly_carry_forward ?? 0) }} C/F
                                </div>
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
                                        <strong class="text-dark font-weight-bold" style="font-size: 12.5px;">⏳ Manager Approval Pending</strong>
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
                    @elseif($stLower === 'approved' && ($isSuperAdminUser || $isHrAdminUser) && Route::has('leave-approvals.void'))
                        <button type="button" class="btn btn-sm font-weight-bold px-3.5" style="border-radius: 8px; height: 38px; background: #FFF1F2; color: #E11D48; border: 1px solid #FECDD3; font-size: 12.5px;" onclick="$('#viewModal{{ $lr->id }}').modal('hide'); setTimeout(function(){ $('#voidModal{{ $lr->id }}').modal('show'); }, 350);">
                            <i class="fas fa-ban mr-1"></i> Make Null & Void
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

