    @if(!empty($canManageCompOff))
        <div class="modal fade" id="grantCompOffModal" tabindex="-1" role="dialog" aria-labelledby="grantCompOffTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border:0;border-radius:18px;overflow:hidden;">
                    <div class="modal-header" style="background:linear-gradient(135deg,var(--leave-primary),var(--leave-secondary));color:#fff;">
                        <h5 class="modal-title font-weight-bold" id="grantCompOffTitle"><i class="fas fa-plus-circle mr-2"></i>Grant Comp-Off</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form method="POST" action="{{ route('hrms.comp_offs.grant') }}">
                        @csrf
                        <input type="hidden" name="grant_uuid" value="{{ $grantUuid }}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="grant_comp_off_employee" class="font-weight-bold">Employee</label>
                                <select id="grant_comp_off_employee" name="employee_id" class="form-control select2-searchable" required>
                                    <option value="">Select employee</option>
                                    @foreach($grantEmployees as $grantEmployee)
                                        <option value="{{ $grantEmployee->id }}" {{ (string) old('employee_id') === (string) $grantEmployee->id ? 'selected' : '' }}>
                                            {{ $grantEmployee->display_name ?? $grantEmployee->user->name ?? $grantEmployee->employee_code }} ({{ $grantEmployee->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-sm-6">
                                    <label for="grant_comp_off_date" class="font-weight-bold">Effective / Worked Date</label>
                                    <x-form.date-picker
                                        name="effective_date"
                                        id="grant_comp_off_date"
                                        :value="old('effective_date', now('Asia/Kolkata')->toDateString())"
                                        :max="now('Asia/Kolkata')->toDateString()"
                                        placeholder="dd-mm-yyyy"
                                        required
                                    />
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="grant_comp_off_days" class="font-weight-bold">Days</label>
                                    <input id="grant_comp_off_days" type="number" name="days" class="form-control" step="any" min="0.01" value="{{ old('days', '0.50') }}" required>
                                    
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label for="grant_comp_off_reason" class="font-weight-bold">Reason</label>
                                <textarea id="grant_comp_off_reason" name="reason" class="form-control" rows="3" maxlength="255" required>{{ old('reason') }}</textarea>
                            </div>
                            <small class="d-block text-muted mt-2">The credit is recorded as a dated lot and expires at the end of its effective month.</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background:var(--leave-primary);border-color:var(--leave-primary);font-weight:800;">Grant Comp-Off</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
