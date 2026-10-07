
@foreach($employees as $employee)
@php
$defaultNoticeDays = app(\App\Services\HRMS\Employee\EmployeeExitPolicyS::class)
    ->getNoticePeriodDays(null, 'resignation');
$status = strtolower($employee->employment_status ?? 'inactive');
$exitType = $employee->exit_type ?? '-';
$exitStatus = $employee->exit_status ?? 'exit_initiated';
$assetStatus = $employee->asset_handover_status ?? 'pending';
$fnfStatus = $employee->fnf_status ?? 'pending';
$documentStatus = $employee->document_status ?? 'pending';
$handoverStatus = $employee->handover_status ?? 'pending';
$experienceStatus = $employee->experience_letter_status ?? 'pending';
$relievingStatus = $employee->relieving_letter_status ?? 'pending';
$finalStatus = $employee->final_status ?? 'pending';

$statusPill = function ($value) {
    return match (strtolower($value ?? 'pending')) {
        'completed', 'exit_completed', 'issued', 'not_required', 'cleared', 'approved', 'paid' => 'eo-pill-success',
        'processing', 'clearance_pending', 'generated', 'sent', 'ready_for_final_approval', 'reviewed' => 'eo-pill-info',
        'lost', 'damaged', 'rejected', 'cancelled', 'absconded', 'terminated', 'discontinued' => 'eo-pill-danger',
        default => 'eo-pill-warning',
    };
};
@endphp

<div class="modal fade" id="exitModal-{{ $employee->id }}" tabindex="-1" role="dialog" aria-labelledby="exitModalLabel-{{ $employee->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-start">
                <div class="eo-modal-title-wrap pr-2">
                    <h5 class="modal-title" id="exitModalLabel-{{ $employee->id }}">
                        <i class="fas fa-user-check mr-2"></i> Process Employee Exit
                    </h5>
                    <p class="modal-subtitle">
                        Clearance flow management for <strong>{{ $employee->name ?? 'Employee' }}</strong> ({{ $employee->employee_code ?? 'N/A' }})
                    </p>
                </div>
                <button type="button" class="close ml-auto flex-shrink-0" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(empty($employee->exit_process_id))
                    @include('hrms.employee.exit.partials.modal.initiate')
                @else
                    @include('hrms.employee.exit.partials.modal.overview')
                    @include('hrms.employee.exit.partials.modal.module-summary')
                    @include('hrms.employee.exit.partials.modal.clearances')
                    @include('hrms.employee.exit.partials.modal.actions')
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
