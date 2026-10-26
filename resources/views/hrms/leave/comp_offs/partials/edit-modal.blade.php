@if(!empty($canManageCompOff))
    <div class="modal fade" id="editCompOffModal" tabindex="-1" role="dialog" aria-labelledby="editCompOffTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border:0;border-radius:18px;overflow:hidden;box-shadow:0 15px 35px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background:linear-gradient(135deg,var(--leave-primary),var(--leave-secondary));color:#fff;">
                    <h5 class="modal-title font-weight-bold" id="editCompOffTitle">
                        <i class="fas fa-edit mr-2"></i>Edit Comp-Off Record
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editCompOffForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_comp_off_id" name="id" value="">

                    <div class="modal-body" style="padding: 22px 24px;">
                        <!-- Employee Info Banner (Rich Orbo Card) -->
                        <div class="p-3 mb-3 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #F8FAFC 0%, #EEF2F6 100%); border: 1px solid #E2E8F0; border-radius: 12px;">
                            <div class="d-flex align-items-center" style="gap: 12px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 44px; height: 44px; font-size: 15px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)); box-shadow: 0 3px 10px rgba(75, 0, 232, 0.25); flex-shrink: 0;" id="edit_employee_avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="text-muted font-weight-bold" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.6px;">Employee</div>
                                    <div id="edit_employee_name" class="font-weight-bold text-dark" style="font-size: 15px; line-height: 1.2;"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span id="edit_employee_code" class="badge" style="background: #EEF4FF; color: #3538CD; border: 1px solid #C7D7FE; font-weight: 800; font-size: 12px; border-radius: 8px; padding: 5px 12px;"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-sm-6">
                                <label for="edit_worked_date" class="font-weight-bold" style="font-size: 13px;">Worked Date <span class="text-danger">*</span></label>
                                <x-form.date-picker
                                    name="worked_date"
                                    id="edit_worked_date"
                                    placeholder="dd-mm-yyyy"
                                    required
                                />
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="edit_earned_days" class="font-weight-bold" style="font-size: 13px;">Credit Earned (Days) <span class="text-danger">*</span></label>
                                <input id="edit_earned_days" type="number" name="earned_days" class="form-control" step="0.5" min="0.5" max="5.0" required style="border-radius: 10px; height: 38px; font-weight: 600;">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-sm-6">
                                <label for="edit_expiry_date" class="font-weight-bold" style="font-size: 13px;">Expiry Date <span class="text-danger">*</span></label>
                                <x-form.date-picker
                                    name="expiry_date"
                                    id="edit_expiry_date"
                                    placeholder="dd-mm-yyyy"
                                    required
                                />
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="edit_status" class="font-weight-bold" style="font-size: 13px;">Status <span class="text-danger">*</span></label>
                                <select id="edit_status" name="status" class="form-control select2-searchable" required style="border-radius: 10px; height: 38px; font-weight: 600;">
                                    <option value="earned">Available (Earned)</option>
                                    <option value="used">Used</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label for="edit_remarks" class="font-weight-bold" style="font-size: 13px;">Reason / Remarks</label>
                            <textarea id="edit_remarks" name="remarks" class="form-control" rows="3" maxlength="255" placeholder="Enter reason or remarks" style="border-radius: 10px;"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer" style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 14px 24px;">
                        <button type="button" class="btn btn-light border font-weight-bold" data-dismiss="modal" style="border-radius: 10px; padding: 9px 20px; font-size: 13px; color: #475467; background: #fff;">Cancel</button>
                        <button type="submit" class="btn font-weight-bold text-white d-inline-flex align-items-center" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; border-radius: 10px; padding: 9px 24px; font-size: 13px; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25); transition: all 0.2s ease; gap: 7px;">
                            <i class="fas fa-save"></i>
                            <span>Update Comp-Off</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
