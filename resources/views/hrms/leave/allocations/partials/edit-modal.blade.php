<!-- Single Reusable Edit Allocation Modal -->
<div class="modal fade orb-type-modal" id="editAllocationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <form method="POST" action="" id="editAllocationForm" class="modal-content leave-modal-content">
            @csrf
            @method('PUT')
            <input type="hidden" name="allocation_id" id="edit_allocation_id">
            <input type="hidden" name="employee_id" id="edit_employee_id">

            <div class="modal-header leave-modal-header">
                <div>
                    <h5 class="leave-modal-title">
                        <i class="fas fa-edit text-primary mr-2"></i>Edit Leave Allocation
                    </h5>
                    <div class="leave-modal-subtitle">Employee: <strong id="edit_modal_employee_name">Loading...</strong></div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>

            <div class="modal-body leave-modal-body">
                <!-- Loading State Overlay -->
                <div id="editModalLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted font-weight-bold" style="font-size: 13px;">Loading allocation details...</p>
                </div>

                <div id="editModalFormFields" class="row">
                    <!-- General Settings -->
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Year</label>
                        <input type="number" name="year" id="edit_year" class="leave-control w-100" required min="2020" max="2099">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Employment Stage</label>
                        <select name="employment_stage" id="edit_employment_stage" class="leave-control w-100 alloc-calc-trigger" required>
                            <option value="permanent">Permanent</option>
                            <option value="probation">Probation</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="font-weight-bold text-dark">Leave Policy</label>
                        <select name="policy_id" id="edit_policy_id" class="leave-control w-100 alloc-calc-trigger">
                            <option value="">-- Select Leave Policy --</option>
                            @foreach($policies as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->policy_name }} (Total: {{ $p->annual_total_leaves }}, Paid: {{ $p->annual_paid_leaves }}, Sick: {{ $p->annual_sick_leaves }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dates -->
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Allocation From Date</label>
                        <input type="date" name="allocation_from_date" id="edit_allocation_from_date" class="leave-control w-100 alloc-calc-trigger">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Allocation To Date</label>
                        <input type="date" name="allocation_to_date" id="edit_allocation_to_date" class="leave-control w-100 alloc-calc-trigger">
                    </div>

                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-12 d-flex align-items-center justify-content-between mb-2">
                        <h6 class="font-weight-bold text-primary mb-0"><i class="fas fa-calculator mr-1"></i> Allocated Leave Quotas</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-recalc-allocation" id="btnRecalculateAllocation" style="border-radius:10px; font-weight:800; font-size:11px;">
                            <i class="fas fa-sync-alt mr-1"></i> Recalculate From Policy
                        </button>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-dark">Paid Allocated</label>
                        <input type="number" step="0.5" min="0" name="paid_allocated" id="edit_paid_allocated" class="leave-control w-100" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-dark">Sick Allocated</label>
                        <input type="number" step="0.5" min="0" name="sick_allocated" id="edit_sick_allocated" class="leave-control w-100" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-dark">Comp-Off Allocated</label>
                        <input type="number" step="0.5" min="0" name="comp_off_allocated" id="edit_comp_off_allocated" class="leave-control w-100">
                    </div>

                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-12"><h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-history mr-1"></i> Used Leaves (Manual Adjustment)</h6></div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">Paid Used</label>
                        <input type="number" step="0.5" min="0" name="paid_used" id="edit_paid_used" class="leave-control w-100">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">Sick Used</label>
                        <input type="number" step="0.5" min="0" name="sick_used" id="edit_sick_used" class="leave-control w-100">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">Comp-Off Used</label>
                        <input type="number" step="0.5" min="0" name="comp_off_used" id="edit_comp_off_used" class="leave-control w-100">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">LWP Used</label>
                        <input type="number" step="0.5" min="0" name="lwp_used" id="edit_lwp_used" class="leave-control w-100">
                    </div>

                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-12"><h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-cog mr-1"></i> Monthly & Carry Forward Settings</h6></div>
                    
                    <div class="col-12 mb-3" id="editNonPermNotice" style="display: none;">
                        <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 11.5px; border-radius: 10px;">
                            <i class="fas fa-info-circle mr-1"></i> <strong>Note:</strong> Unpaid interns have a fixed 1-leave allocation for their entire duration without monthly quotas.
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">Monthly Quota</label>
                        <input type="number" step="0.5" min="0" name="monthly_quota" id="edit_monthly_quota" class="leave-control w-100 monthly-calc-input">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">Monthly Carry Forward</label>
                        <input type="number" step="0.5" min="0" name="monthly_carry_forward" id="edit_monthly_carry_forward" class="leave-control w-100 monthly-calc-input">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">Used This Month</label>
                        <input type="number" step="0.5" min="0" name="monthly_used_this_month" id="edit_monthly_used_this_month" class="leave-control w-100 monthly-calc-input">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-success" style="font-size:12px;"><i class="fas fa-calculator mr-1"></i> Total Monthly Rem Paid</label>
                        <input type="number" step="0.5" min="0" name="total_monthly_remaining_paid" id="edit_total_monthly_remaining_paid" class="leave-control w-100 font-weight-bold text-success" style="background:#F0FDF4; border:1px solid #86EFAC;">
                    </div>

                    <div class="col-12 mb-3">
                        <div class="p-3 rounded" style="background:#F8FAFC; border:1px solid #E2E8F0;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span style="font-size:12px; font-weight:800; color:#334155;"><i class="fas fa-info-circle text-primary mr-1"></i> Monthly Deduction & Next Month Carryover Breakdown:</span>
                                <span class="badge badge-primary px-2 py-1" style="font-size:10px; font-weight:800;">Real-time Live Calc</span>
                            </div>
                            <div class="row text-center" style="font-size:12px;">
                                <div class="col-md-4 mb-1">
                                    <span class="text-muted d-block" style="font-size:11px;">Carry Forward Remaining</span>
                                    <strong class="text-danger" id="edit_rem_carry" style="font-size:14px;">0.00</strong>
                                </div>
                                <div class="col-md-4 mb-1">
                                    <span class="text-muted d-block" style="font-size:11px;">Monthly Quota Remaining</span>
                                    <strong class="text-success" id="edit_rem_quota" style="font-size:14px;">0.00</strong>
                                </div>
                                <div class="col-md-4 mb-1">
                                    <span class="text-muted d-block" style="font-size:11px;">Next Month Carryover</span>
                                    <strong class="text-primary" id="edit_next_carryover" style="font-size:14px;">0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="font-weight-bold text-dark">Allocation Reason</label>
                        <input type="text" name="allocation_reason" id="edit_allocation_reason" class="leave-control w-100" placeholder="Reason for allocation or adjustment">
                    </div>

                    <div class="col-md-12 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_is_locked" name="is_locked" value="1">
                            <label class="custom-control-label font-weight-bold text-dark" for="edit_is_locked">
                                Lock Allocation (Prevents automatic recalculation on system cron)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer leave-modal-footer">
                <button type="button" class="leave-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="leave-btn"><i class="fas fa-save mr-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
