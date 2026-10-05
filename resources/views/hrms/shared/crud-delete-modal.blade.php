@php
    $employeeName = data_get($row, 'employee_name') ?: (data_get($row, 'employee_display_name') ?: (data_get($row, 'name') ?: 'Employee'));
    $workedDate = data_get($row, 'worked_date');
    $targetTitle = !empty($pageTitle) ? \Illuminate\Support\Str::singular($pageTitle) : 'Work Request';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered orb-action-modal-dialog" style="max-width: 480px !important; width: 95% !important; margin: 1.75rem auto !important;">
        <form method="POST" action="{{ $action }}" class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden; background: #fff;">
            @csrf
            @method('DELETE')

            <div class="modal-header d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important; padding: 16px 22px; color: #fff; border: 0; margin: 0;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <i class="fas fa-trash-alt text-white" style="font-size: 16px;"></i>
                    <h5 class="modal-title font-weight-bold mb-0 text-white" style="font-size: 16px; letter-spacing: -0.2px;">Delete {{ $targetTitle }}</h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.22); display: inline-flex; align-items: center; justify-content: center; border: 0; color: #ffffff !important; font-size: 15px; opacity: 1; outline: none; cursor: pointer; padding: 0; margin: 0; line-height: 1; transition: all 0.2s ease;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body px-4 py-3" style="background: #fff;">
                <!-- Employee Summary Header -->
                <div class="p-3 mb-3 d-flex align-items-center justify-content-between" style="background: #FEF2F2; border-radius: 12px; border: 1px solid #FECACA;">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle font-weight-bold" style="width: 40px; height: 40px; background: #fff; color: #DC2626; font-size: 14px; border: 1.5px solid #FECACA;">
                            {{ strtoupper(substr($employeeName, 0, 1)) }}
                        </div>
                        <div class="text-left">
                            <div class="font-weight-bold text-dark" style="font-size: 13.5px; line-height: 1.2;">{{ $employeeName }}</div>
                            <div class="text-muted small" style="font-size: 11px;">{{ data_get($row, 'employee_code') ?: 'Employee' }}{{ $workedDate ? ' • ' . \Carbon\Carbon::parse($workedDate)->format('d M Y') : '' }}</div>
                        </div>
                    </div>
                    <span class="badge badge-danger font-weight-bold px-2 py-1" style="font-size: 10.5px; border-radius: 14px; letter-spacing: 0.5px;">DELETE</span>
                </div>

                <div class="p-3 text-center" style="background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
                    <p class="text-dark font-weight-semibold mb-1" style="font-size: 13.5px;">Are you sure you want to delete this {{ strtolower($targetTitle) }}?</p>
                    <p class="text-danger small mb-0 font-weight-semibold" style="font-size: 11.5px;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> This action is permanent and cannot be undone.
                    </p>
                </div>
            </div>

            <div class="modal-footer d-flex align-items-center justify-content-end" style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 12px 20px; gap: 10px;">
                <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px; font-size: 13px; padding: 8px 18px; font-weight: 700; border: 1px solid #CBD5E1; background: #fff; min-height: 38px;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger font-weight-bold" style="border-radius: 10px; font-weight: 700; padding: 8px 22px; border: 0; background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important; color: #fff; font-size: 13px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); min-height: 38px;">
                    <i class="fas fa-trash mr-1"></i> Confirm Delete
                </button>
            </div>
        </form>
    </div>
</div>
