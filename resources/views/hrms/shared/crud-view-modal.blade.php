@php
    $rowId = data_get($row, 'id');
    $employeeName = data_get($row, 'employee_name') ?: (data_get($row, 'employee_display_name') ?: (data_get($row, 'name') ?: 'Employee'));
    $employeeCode = data_get($row, 'employee_code') ?: '-';
    $status = strtolower((string) data_get($row, 'status', 'pending'));
    $workType = data_get($row, 'work_type');
    $workMode = strtoupper((string) data_get($row, 'work_mode', 'WFO'));
    $workedDate = data_get($row, 'worked_date');
    $reason = data_get($row, 'reason');
    $compOffGen = (bool) data_get($row, 'comp_off_generated', false);
    $rejectionReason = data_get($row, 'rejection_reason');
    $approvedByName = data_get($row, 'approved_by_name');
    $approvedAt = data_get($row, 'approved_at');
    $createdAt = data_get($row, 'created_at');

    $isApproved = ($status === 'approved');
    $isRejected = ($status === 'rejected');
    $isPending = ($status === 'pending');
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" style="max-width: 680px !important; width: 95% !important; margin: 1.75rem auto !important;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden; background: #fff;">
            
            <!-- Orbo Gradient Modal Header -->
            <div class="modal-header d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #7000FF 0%, #E6007A 100%) !important; padding: 16px 22px; color: #fff; border: 0; margin: 0;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0 text-white d-flex align-items-center" style="font-size: 16px; letter-spacing: -0.2px;">
                        <i class="fas fa-file-invoice mr-2 text-white-50"></i> View Work Request #{{ $rowId }}
                    </h5>
                    <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; opacity: 0.9;">
                        Request details and review audit status.
                    </small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.22); display: inline-flex; align-items: center; justify-content: center; border: 0; color: #ffffff !important; font-size: 15px; opacity: 1; outline: none; cursor: pointer; padding: 0; margin: 0; line-height: 1; transition: all 0.2s ease;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body (Compact and Clean) -->
            <div class="modal-body px-4 py-3" style="background: #fff;">
                
                <!-- Section 1: Request Details -->
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2 pb-1 border-bottom" style="border-color: #E2E8F0 !important;">
                        <i class="fas fa-edit text-primary mr-2" style="font-size: 11px;"></i>
                        <span class="font-weight-bold text-uppercase" style="font-size: 11px; color: var(--orb-primary, #4B00E8); letter-spacing: 0.5px;">Request Details</span>
                    </div>

                    <div class="row no-gutters" style="margin: 0 -4px;">
                        <!-- Employee -->
                        <div class="col-6 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Employee</span>
                                <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px;" title="{{ $employeeName }} ({{ $employeeCode }})">
                                    {{ $employeeName }} <span class="text-muted small">({{ $employeeCode }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Worked Date -->
                        <div class="col-6 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Worked Date</span>
                                <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                    <i class="far fa-calendar-alt text-muted mr-1"></i> {{ $workedDate ? \Carbon\Carbon::parse($workedDate)->format('d-m-Y') : '-' }}
                                </div>
                            </div>
                        </div>

                        <!-- Work Type -->
                        <div class="col-6 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Work Type</span>
                                <div class="font-weight-bold text-primary text-truncate" style="font-size: 12.5px;">
                                    {{ $workType ? ucwords(str_replace('_', ' ', $workType)) : '-' }}
                                </div>
                            </div>
                        </div>

                        <!-- Work Mode -->
                        <div class="col-6 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Work Mode</span>
                                <div>
                                    <span class="badge" style="background: {{ $workMode === 'WFH' ? '#EEF2FF' : '#F0FDF4' }}; color: {{ $workMode === 'WFH' ? '#4338CA' : '#15803D' }}; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 4px;">
                                        {{ $workMode === 'WFH' ? 'WFH (Home)' : 'WFO (Office)' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Comp. Off Status -->
                        <div class="col-6 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Comp. Off</span>
                                <div>
                                    <span class="badge" style="background: {{ $compOffGen ? '#ECFDF5' : '#FEF2F2' }}; color: {{ $compOffGen ? '#065F46' : '#991B1B' }}; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 4px; border: 1px solid {{ $compOffGen ? '#A7F3D0' : '#FECACA' }};">
                                        <i class="{{ $compOffGen ? 'fas fa-check' : 'fas fa-times' }} mr-1"></i>
                                        {{ $compOffGen ? 'GENERATED' : 'NOT GENERATED' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-6 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Status</span>
                                <div>
                                    <span class="badge" style="background: {{ $isApproved ? '#ECFDF5' : ($isRejected ? '#FEF2F2' : ($isPending ? '#FEF3C7' : '#F1F5F9')) }}; color: {{ $isApproved ? '#065F46' : ($isRejected ? '#991B1B' : ($isPending ? '#92400E' : '#475569')) }}; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 4px; border: 1px solid {{ $isApproved ? '#A7F3D0' : ($isRejected ? '#FECACA' : ($isPending ? '#FDE68A' : '#E2E8F0')) }};">
                                        <i class="{{ $isApproved ? 'fas fa-check-circle' : ($isRejected ? 'fas fa-times-circle' : ($isPending ? 'fas fa-clock' : 'fas fa-info-circle')) }} mr-1"></i>
                                        {{ strtoupper($status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="col-12 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Reason</span>
                                <div class="text-dark font-italic" style="font-size: 12px; line-height: 1.4;">
                                    "{{ $reason ?: 'No reason provided.' }}"
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Review / Decision Details -->
                @if($isApproved || $isRejected || $approvedByName || $approvedAt)
                <div class="mb-1">
                    <div class="d-flex align-items-center mb-2 pb-1 border-bottom" style="border-color: #E2E8F0 !important;">
                        <i class="fas fa-clipboard-check text-primary mr-2" style="font-size: 11px;"></i>
                        <span class="font-weight-bold text-uppercase" style="font-size: 11px; color: var(--orb-primary, #4B00E8); letter-spacing: 0.5px;">{{ $isRejected ? 'Rejection Details' : 'Approval Details' }}</span>
                    </div>

                    <div class="row no-gutters" style="margin: 0 -4px;">
                        <div class="col-6 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 50px;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">
                                    {{ $isRejected ? 'Rejected By' : 'Approved By' }}
                                </span>
                                <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px;">
                                    <i class="fas fa-user-shield text-muted mr-1"></i> {{ $approvedByName ?: 'HR Admin' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-6 p-1">
                            <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 50px;">
                                <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">
                                    {{ $isRejected ? 'Rejected At' : 'Approved At' }}
                                </span>
                                <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                    <i class="far fa-clock text-muted mr-1"></i> {{ $approvedAt ? \Carbon\Carbon::parse($approvedAt)->format('d M Y, h:i A') : '-' }}
                                </div>
                            </div>
                        </div>

                        @if($isRejected && $rejectionReason)
                        <div class="col-12 p-1">
                            <div class="p-2 px-3 rounded font-weight-semibold" style="background: #FEF2F2; border: 1px solid #FECACA; font-size: 12px; line-height: 1.4; color: #991B1B;">
                                <span class="text-danger d-block text-uppercase font-weight-bold mb-1" style="font-size: 9.5px; letter-spacing: 0.5px;">
                                    Rejection Reason:
                                </span>
                                <i class="fas fa-exclamation-circle mr-1"></i> {{ $rejectionReason }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            <!-- Modal Footer (Compact) -->
            <div class="modal-footer d-flex justify-content-between align-items-center" style="border-top: 1px solid #E2E8F0; padding: 12px 22px; background: #F8FAFC;">
                <div class="text-muted" style="font-size: 11.5px;">
                    @if($createdAt)
                    <i class="far fa-calendar-check mr-1 text-muted"></i> Submitted on {{ \Carbon\Carbon::parse($createdAt)->format('d M Y, h:i A') }}
                    @endif
                </div>
                <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px; min-height: 36px; padding: 6px 20px; font-size: 13px; font-weight: 700; border: 1px solid #CBD5E1; background: #fff; color: #334155; cursor: pointer;">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
