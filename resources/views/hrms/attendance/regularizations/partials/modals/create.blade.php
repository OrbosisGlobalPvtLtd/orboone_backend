@if(!empty($canCreate))
<div class="modal fade glass-modal" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Apply Regularization</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route($storeRoute) }}" class="js-regularization-form">
                @csrf
                <div class="modal-body">
                    @php
                        $modalEmpId = $modalOwnEmpId ?? auth()->user()->employee->id ?? '';
                        $isSelfOnlyMode = isset($isSelfOnly) ? (bool)$isSelfOnly : (!empty($isEmployeeRole) || empty($canApplyForOthers));
                    @endphp

                    <!-- Dynamic Status Alert Box -->
                    <div id="js-regularization-alert" class="alert d-none mb-3" style="border-radius: 12px; font-size: 13px;"></div>

                    <div class="reg-form-grid">
                        @if(!$isSelfOnlyMode)
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark small text-uppercase">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-control custom-select select2-modal-searchable" style="border-radius: 10px;" required>
                                    <option value="">Select Employee</option>
                                    @foreach($formFields[0]['options'] ?? [] as $empId => $empName)
                                        <option value="{{ $empId }}" {{ (string)$empId === (string)$modalEmpId ? 'selected' : '' }}>{{ $empName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="employee_id" value="{{ $modalEmpId }}">
                        @endif

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small text-uppercase">Attendance Date <span class="text-danger">*</span></label>
                            <input type="text" data-date-picker name="attendance_date" class="form-control orbo-date-picker" placeholder="dd-mm-yyyy" max="{{ date('Y-m-d') }}" style="border-radius: 10px; height: 42px;" required>
                        </div>

                        <div class="form-group mb-0 js-req-type-group" id="create_request_type_group">
                            <label class="font-weight-bold text-dark small text-uppercase">Request Type <span class="text-danger">*</span></label>
                            <select name="request_type" id="create_request_type" class="form-control custom-select select2-modal-searchable" style="border-radius: 10px;" required>
                                <option value="">Select Date First</option>
                            </select>
                        </div>

                        <!-- Conditionally displayed time pickers -->
                        <div class="form-group mb-0 js-in-group" style="display: none;">
                            <label class="font-weight-bold text-dark small text-uppercase">Requested Punch In Time <span class="text-danger">*</span></label>
                            <input type="time" name="requested_punch_in" class="form-control" style="border-radius: 10px; height: 42px;">
                        </div>

                        <div class="form-group mb-0 js-out-group" style="display: none;">
                            <label class="font-weight-bold text-dark small text-uppercase">Requested Punch Out Time <span class="text-danger">*</span></label>
                            <input type="time" name="requested_punch_out" class="form-control" style="border-radius: 10px; height: 42px;">
                        </div>

                        <!-- Requested status correction dropdown (maps to other) -->
                        <div class="form-group mb-0 js-status-group" style="display: none;">
                            <label class="font-weight-bold text-dark small text-uppercase">Requested Status <span class="text-danger">*</span></label>
                            <select name="requested_status" class="form-control custom-select select2-modal-searchable" style="border-radius: 10px;">
                                <option value="Present">Present</option>
                                <option value="Half Day">Half Day</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </div>

                        <div class="form-group mb-0 grid-col-full">
                            <label class="font-weight-bold text-dark small text-uppercase">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" minlength="5" required placeholder="Describe the reason for correction..." style="border-radius: 10px; resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border font-weight-bold" style="border-radius: 10px; font-size: 13px;" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn text-white font-weight-bold px-4 shadow-sm" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: 0; border-radius: 10px; font-size: 13px;">
                        <i class="fas fa-paper-plane mr-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
