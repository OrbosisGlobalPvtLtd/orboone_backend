@php
    $actionKey = strtolower(\Illuminate\Support\Str::slug($action['label'] ?? 'action'));
    $isReject = in_array($actionKey, ['reject', 'decline']);
    $isApprove = in_array($actionKey, ['approve', 'accept']);
    $modalId = 'actionModal_' . $actionKey . '_' . data_get($row, 'id');
    $employeeName = data_get($row, 'employee_name') ?: (data_get($row, 'employee_display_name') ?: (data_get($row, 'name') ?: 'Employee'));
    $targetTitle = !empty($pageTitle) ? \Illuminate\Support\Str::singular($pageTitle) : 'Work Request';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md orb-action-modal-dialog" style="max-width: 680px !important; width: 95% !important; margin: 1.75rem auto !important;">
        <form method="POST" action="{{ route($action['route'], data_get($row, 'id')) }}" class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden; background: #fff;">
            @csrf

            <!-- Orbo Gradient Modal Header -->
            <div class="modal-header d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #7000FF 0%, #E6007A 100%) !important; padding: 16px 22px; color: #fff; border: 0; margin: 0;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0 text-white d-flex align-items-center" style="font-size: 16px; letter-spacing: -0.2px;">
                        <i class="{{ $isReject ? 'fas fa-times-circle' : ($isApprove ? 'fas fa-check-circle' : 'fas fa-shield-alt') }} mr-2"></i>
                        {{ $action['label'] }} {{ $targetTitle }}
                    </h5>
                    <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; opacity: 0.9;">
                        @if($isReject)
                            Rejection reason is mandatory.
                        @elseif($isApprove)
                            Confirm approval for this {{ strtolower($targetTitle) }}.
                        @else
                            Confirm {{ strtolower($action['label']) }} action for this {{ strtolower($targetTitle) }}.
                        @endif
                    </small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.22); display: inline-flex; align-items: center; justify-content: center; border: 0; color: #ffffff !important; font-size: 15px; opacity: 1; outline: none; cursor: pointer; padding: 0; margin: 0; line-height: 1; transition: all 0.2s ease;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body px-4 py-3" style="background: #fff;">
                <!-- Request Information Details -->
                @if(data_get($row, 'worked_date') || $employeeName)
                <div class="orb-form-group mb-3 text-left">
                    <label class="orb-form-label font-weight-bold text-uppercase d-flex align-items-center mb-2" style="font-size: 11px; letter-spacing: 0.5px; color: #64748B;">
                        <i class="fas fa-calendar-check text-primary mr-1" style="color: #7000FF !important;"></i> REQUEST DETAILS
                    </label>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted font-weight-bold d-block mb-1" style="font-size: 11px;">Employee</small>
                            <input type="text" class="form-control" value="{{ $employeeName }} ({{ data_get($row, 'employee_code') ?: 'EMP' }})" readonly style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; font-size: 13px; font-weight: 600; color: #1E293B;">
                        </div>
                        <div class="col-6">
                            <small class="text-muted font-weight-bold d-block mb-1" style="font-size: 11px;">Worked Date & Type</small>
                            <input type="text" class="form-control" value="{{ data_get($row, 'worked_date') ? \Carbon\Carbon::parse(data_get($row, 'worked_date'))->format('d M Y') : '-' }} • {{ ucwords(str_replace('_', ' ', data_get($row, 'work_type', 'Holiday Work'))) }}" readonly style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; font-size: 13px; font-weight: 600; color: #1E293B;">
                        </div>
                    </div>
                    @if(data_get($row, 'reason'))
                    <div class="mt-2 p-2 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; font-size: 12px; color: #475569;">
                        <strong>Employee Reason:</strong> "{{ data_get($row, 'reason') }}"
                    </div>
                    @endif
                </div>
                @endif

                @if($isReject)
                <div class="orb-form-group mb-0 text-left">
                    <label class="orb-form-label font-weight-bold text-uppercase d-flex align-items-center mb-1" style="font-size: 11px; letter-spacing: 0.5px; color: #1E293B;">
                        <span>Rejection Reason <span class="text-danger">*</span></span>
                    </label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="State reason for rejecting request..." style="border-radius: 10px; resize: vertical; font-size: 13px; min-height: 85px; border: 1.5px solid #CBD5E1; padding: 10px 14px; background: #fff;"></textarea>
                    <small class="form-text text-muted mt-1" style="font-size: 11.5px;">This reason will be shared with the employee via notification.</small>
                </div>
                @elseif($isApprove)
                <div class="orb-form-group mb-0 text-left">
                    <label class="orb-form-label font-weight-bold text-uppercase d-flex align-items-center mb-1" style="font-size: 11px; letter-spacing: 0.5px; color: #1E293B;">
                        <span>Remarks / Audit Note (Optional)</span>
                    </label>
                    <input type="text" class="form-control" name="notes" placeholder="Optional remarks for audit logs" style="border-radius: 10px; font-size: 13px; border: 1.5px solid #CBD5E1; padding: 10px 14px; background: #fff; height: 42px;">
                    <small class="form-text text-muted mt-1" style="font-size: 11.5px;">Comp off will be credited upon eligibility validation.</small>
                </div>
                @endif
            </div>

            <div class="modal-footer d-flex align-items-center justify-content-end" style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 14px 22px; gap: 10px;">
                <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px; font-size: 13px; padding: 8px 18px; font-weight: 700; border: 1px solid #CBD5E1; background: #fff; min-height: 38px;">
                    Cancel
                </button>
                @if($isReject)
                <button type="submit" class="btn btn-danger font-weight-bold" style="border-radius: 10px; font-weight: 700; padding: 8px 22px; border: 0; background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important; color: #fff; font-size: 13px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); min-height: 38px;">
                    <i class="fas fa-times mr-1"></i> Confirm Reject
                </button>
                @elseif($isApprove)
                <button type="submit" class="btn font-weight-bold text-white" style="border-radius: 10px; font-weight: 700; padding: 8px 22px; border: 0; background: linear-gradient(135deg, #7000FF 0%, #E6007A 100%) !important; color: #fff; font-size: 13px; box-shadow: 0 4px 14px rgba(112, 0, 255, 0.35); min-height: 38px;">
                    <i class="fas fa-check mr-1"></i> Confirm Approve
                </button>
                @else
                <button type="submit" class="btn font-weight-bold text-white" style="border-radius: 10px; font-weight: 700; padding: 8px 22px; border: 0; background: linear-gradient(135deg, #7000FF 0%, #E6007A 100%) !important; color: #fff; font-size: 13px; box-shadow: 0 4px 14px rgba(112, 0, 255, 0.35); min-height: 38px;">
                    <i class="fas fa-check mr-1"></i> Confirm {{ $action['label'] }}
                </button>
                @endif
            </div>
        </form>
    </div>
</div>
