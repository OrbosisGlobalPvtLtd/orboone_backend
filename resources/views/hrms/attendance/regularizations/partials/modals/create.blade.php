@if(!empty($canCreate))
<div class="modal fade glass-modal" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Apply Regularization</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route($storeRoute) }}" class="js-regularization-form">
                @csrf
                <div class="modal-body">
                    @php
                        $modalOwnEmpId = auth()->user()->employee->id ?? '';
                        $isSelfOnly = !empty($isEmployeeRole) || (empty($canViewAll) && empty($canViewTeam)) || (auth()->user()->role_id ?? null) == 7;
                    @endphp
                    @if(!$isSelfOnly)
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-control custom-select" required>
                                <option value="">Select Employee</option>
                                @foreach($formFields[0]['options'] ?? [] as $empId => $empName)
                                    <option value="{{ $empId }}" {{ (string)$empId === (string)$modalOwnEmpId ? 'selected' : '' }}>{{ $empName }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="employee_id" value="{{ $modalOwnEmpId }}">
                    @endif

                    <!-- Dynamic Status Alert Box -->
                    <div id="js-regularization-alert" class="alert d-none"></div>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Attendance Date <span class="text-danger">*</span></label>
                        <input type="date" name="attendance_date" class="form-control" max="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Request Type <span class="text-danger">*</span></label>
                        <select name="request_type" id="create_request_type" class="form-control custom-select" required>
                            <option value="">Select Date First</option>
                        </select>
                    </div>

                    <!-- Conditionally displayed time pickers -->
                    <div class="form-group js-in-group" style="display: none;">
                        <label class="font-weight-bold text-dark">Requested Punch In Time <span class="text-danger">*</span></label>
                        <input type="time" name="requested_punch_in" class="form-control">
                    </div>

                    <div class="form-group js-out-group" style="display: none;">
                        <label class="font-weight-bold text-dark">Requested Punch Out Time <span class="text-danger">*</span></label>
                        <input type="time" name="requested_punch_out" class="form-control">
                    </div>

                    <!-- Requested status correction dropdown (maps to other) -->
                    <div class="form-group js-status-group" style="display: none;">
                        <label class="font-weight-bold text-dark">Requested Status <span class="text-danger">*</span></label>
                        <select name="requested_status" class="form-control custom-select">
                            <option value="Present">Present</option>
                            <option value="Half Day">Half Day</option>
                            <option value="Absent">Absent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" minlength="5" required placeholder="Describe the reason for correction..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: rgba(255,255,255,0.5);">
                    <button type="button" class="btn btn-light" style="border-radius: 10px;" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4" style="background: var(--orb-primary); border: 0; border-radius: 10px;">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
