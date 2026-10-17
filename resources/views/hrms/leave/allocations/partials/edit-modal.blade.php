<!-- Single Reusable Edit Allocation Modal -->
<div class="modal fade orb-type-modal" id="editAllocationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <form method="POST" action="" id="editAllocationForm" class="modal-content leave-modal-content">
            @csrf
            @method('PUT')
            <input type="hidden" name="allocation_id" id="edit_allocation_id">
            <input type="hidden" name="employee_id" id="edit_employee_id">
            <input type="hidden" name="adjustment_uuid" id="edit_adjustment_uuid">

            <div class="modal-header leave-modal-header" style="background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%); color: #fff; padding: 18px 24px;">
                <div>
                    <h5 class="leave-modal-title mb-1" style="font-size: 17px; font-weight: 700; color: #fff;">
                        <i class="fas fa-edit mr-2"></i>Edit Leave Allocation
                    </h5>
                    <div class="leave-modal-subtitle" style="color: rgba(255,255,255,0.85); font-size: 13px;">
                        Employee: <strong id="edit_modal_employee_name" style="color: #fff;">Loading...</strong>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body leave-modal-body" style="padding: 22px 26px;">
                <!-- Loading State Overlay -->
                <div id="editModalLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted font-weight-bold" style="font-size: 13px;">Loading allocation details...</p>
                </div>

                <div id="editModalFormFields" class="row">
                    <!-- General Settings -->
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">Year</label>
                        <input type="number" name="year" id="edit_year" class="leave-control w-100" required min="2020" max="2099">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">Employment Stage</label>
                        <select name="employment_stage" id="edit_employment_stage" class="leave-control w-100 alloc-calc-trigger select2-searchable" required>
                            <option value="permanent">Permanent</option>
                            <option value="probation">Probation</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">Leave Policy</label>
                        <select name="policy_id" id="edit_policy_id" class="leave-control w-100 alloc-calc-trigger select2-searchable">
                            
                            @foreach($policies as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->policy_name }} (Total: {{ $p->annual_total_leaves }}, Paid: {{ $p->annual_paid_leaves }}, Sick: {{ $p->annual_sick_leaves }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dates -->
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">Allocation From Date</label>
                        <x-form.date-picker 
                            name="allocation_from_date" 
                            id="edit_allocation_from_date" 
                            placeholder="dd-mm-yyyy" 
                            class="leave-control w-100 alloc-calc-trigger" 
                        />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">Allocation To Date</label>
                        <x-form.date-picker 
                            name="allocation_to_date" 
                            id="edit_allocation_to_date" 
                            placeholder="dd-mm-yyyy" 
                            class="leave-control w-100 alloc-calc-trigger" 
                        />
                    </div>

                    <!-- SECTION 1: ANNUAL POLICY ENTITLEMENT (Sanctioned Quota) -->
                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-12 d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <h6 class="font-weight-bold text-primary mb-0" style="font-size: 13.5px;">
                                <i class="fas fa-calendar-check mr-1"></i> 1. Annual Policy Entitlement (Sanctioned Quota)
                            </h6>
                            <small class="text-muted">Total leave entitlement granted by policy for this full calendar year</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-recalc-allocation" id="btnRecalculateAllocation" style="border-radius:10px; font-weight:800; font-size:11px;">
                            <i class="fas fa-sync-alt mr-1"></i> Recalculate From Policy
                        </button>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">
                            Paid Allocated (PL)
                            <span class="badge badge-light border text-muted ledger-allocation-only ml-1" style="display:none; font-size:10px;">Policy Driven</span>
                        </label>
                        <input type="number" step="any" min="0" name="paid_allocated" id="edit_paid_allocated" class="leave-control w-100" required>
                        <small class="text-muted" style="font-size: 10.5px;">Annual Sanctioned Paid Leaves</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">
                            Sick Allocated (SL)
                            <span class="badge badge-light border text-muted ledger-allocation-only ml-1" style="display:none; font-size:10px;">Policy Driven</span>
                        </label>
                        <input type="number" step="any" min="0" name="sick_allocated" id="edit_sick_allocated" class="leave-control w-100" required>
                        <small class="text-muted" style="font-size: 10.5px;">Annual Sanctioned Sick Leaves</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-dark d-flex align-items-center justify-content-between" style="font-size: 12px;">
                            <span>Comp-Off Allocated</span>
                            <span class="badge badge-light border text-primary" style="font-size:10px;"><i class="fas fa-link mr-1"></i>Auto-Synced</span>
                        </label>
                        <input type="number" step="any" min="0" name="comp_off_allocated" id="edit_comp_off_allocated" class="leave-control w-100 bg-light" readonly style="color: #64748b; font-weight: 600;">
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <small class="text-muted" style="font-size: 10.5px;">Auto-calculated from records</small>
                            <a href="{{ route('hrms.comp_offs.index') }}" target="_blank" class="small text-primary font-weight-bold" style="font-size: 11px;">
                                <i class="fas fa-external-link-alt mr-1"></i>Manage &rarr;
                            </a>
                        </div>
                        <input type="hidden" name="comp_off_reason" id="edit_comp_off_reason" value="">
                    </div>

                    <!-- SECTION 2: USED LEAVES (Current Year Consumed) -->
                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-12 mb-2">
                        <h6 class="font-weight-bold mb-0" style="color: #c2410c; font-size: 13.5px;">
                            <i class="fas fa-calendar-minus mr-1"></i> 2. Leaves Used (Current Year Consumed)
                        </h6>
                        <small class="text-muted">Total leaves consumed by employee so far. Modifying used leaves automatically balances remaining leaves.</small>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">Paid Leaves Used</label>
                        <input type="number" step="any" min="0" name="paid_used" id="edit_paid_used" class="leave-control w-100">
                        {{-- <small class="text-muted" style="font-size: 10.5px;">Used Paid leaves</small> --}}
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">Sick Leaves Used</label>
                        <input type="number" step="any" min="0" name="sick_used" id="edit_sick_used" class="leave-control w-100">
                        {{-- <small class="text-muted" style="font-size: 10.5px;">Used Sick leaves</small> --}}
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark d-flex align-items-center justify-content-between" style="font-size: 12px;">
                            <span>Comp-Off Used</span>
                            <span class="badge badge-light border text-muted" style="font-size:10px;">Auto</span>
                        </label>
                        <input type="number" step="any" min="0" name="comp_off_used" id="edit_comp_off_used" class="leave-control w-100 bg-light" readonly style="color: #64748b; font-weight: 600;">
                        {{-- <small class="text-muted" style="font-size: 10.5px;">Updated via approved leaves</small> --}}
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">LWP Used (Unpaid)</label>
                        <input type="number" step="any" min="0" name="lwp_used" id="edit_lwp_used" class="leave-control w-100">
                        {{-- <small class="text-muted" style="font-size: 10.5px;">Loss of Pay days</small> --}}
                    </div>

                    <!-- Auto-Adjustment Notice Banner -->
                    <div class="col-12 mb-2" id="editSpilloverNotice" style="display: none;"></div>

                    <!-- SECTION 3: CURRENT AVAILABLE / REMAINING BALANCES -->
                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-12 mb-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="font-weight-bold mb-0" style="color: #047857; font-size: 13.5px;">
                                    <i class="fas fa-check-circle mr-1"></i> 3. Available / Remaining Balance
                                </h6>
                                <small class="text-muted">Current in-hand balance available for the employee to apply right now</small>
                            </div>
                             <span class="badge px-2 py-1" style="font-size: 11px; border-radius: 6px; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 600;">
        <i class="fas fa-calculator mr-1"></i> Auto-Calculated
    </span>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3 ledger-allocation-only" id="editLedgerPlTarget" style="display:none;">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">
                            Available Paid Leaves
                        </label>
                        <div class="input-group">
                           
                            <input type="number" step="any" min="0" name="paid_remaining" id="edit_paid_remaining" class="leave-control form-control" style="font-weight: 700;">
                        </div>
                        <input type="hidden" name="pl_target_edited" id="edit_pl_target_edited" value="0">
                        {{-- <small class="text-muted" style="font-size: 10.5px;">Current available Paid Leave balance</small> --}}
                    </div>

                    <div class="col-md-4 mb-3 ledger-allocation-only" id="editLedgerSlTarget" style="display:none;">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">
                            Available Sick Leaves
                        </label>
                        <div class="input-group">
                            
                            <input type="number" step="any" min="0" name="sick_remaining" id="edit_sick_remaining" class="leave-control form-control" style="font-weight: 700;">
                        </div>
                        <input type="hidden" name="sl_target_edited" id="edit_sl_target_edited" value="0">
                        {{-- <small class="text-muted" style="font-size: 10.5px;">Current available Sick Leave balance</small> --}}
                    </div>

                    <div class="col-md-4 mb-3" id="editCompOffRemainingBox">
                        <label class="font-weight-bold text-dark" style="font-size: 12px;">
                            Available Comp-Off
                        </label>
                        <div class="input-group">
                           
                            <input type="number" step="any" min="0" id="edit_comp_off_remaining" class="leave-control form-control bg-light" style="font-weight: 700;" readonly>
                        </div>
                        {{-- <small class="text-muted" style="font-size: 10.5px;">Auto calculated: Allocated − Used</small> --}}
                    </div>

                    <!-- Informational Notices -->
                    <div class="col-12 mb-2" id="editNonPermNotice" style="display: none;">
                        <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 11.5px; border-radius: 10px;">
                            <i class="fas fa-info-circle mr-1"></i> <strong>Note:</strong> Unpaid interns have a fixed 1-leave allocation for their entire duration without monthly quotas.
                        </div>
                    </div>
                    {{-- <div class="col-12 mb-3">
                        <div class="alert alert-light border py-2 px-3 mb-0" style="font-size:11.5px; border-radius:10px; background-color: #f8fafc;">
                            <i class="fas fa-book-open text-primary mr-1"></i> Monthly credits, usage, and closing balances are maintained in the Monthly Accrual passbook.
                        </div>
                    </div> --}}

                    <!-- SECTION 4: AUDIT REASON & SAFEGUARDS -->
                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-md-12 mb-3">
                        <label class="font-weight-bold text-dark" style="font-size: 12.5px;">
                            <i class="fas fa-comment-alt text-secondary mr-1"></i> Allocation / Adjustment Reason <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="allocation_reason" id="edit_allocation_reason" class="leave-control w-100" placeholder="e.g. Annual allocation, balance adjustment, or correction reason">
                        <small class="text-muted ledger-allocation-only d-block mt-1" style="display:none; font-size:11.5px;">
                            A reason is required when changing ledger-backed available balances for audit trail.
                        </small>
                    </div>

                    <div class="col-md-12 mb-2">
                        <div class="p-3 border rounded" style="background-color: #fef2f2; border-color: #fecaca !important;">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_is_locked" name="is_locked" value="1">
                                <label class="custom-control-label font-weight-bold text-dark" for="edit_is_locked" style="font-size: 12.5px; cursor: pointer;">
                                    <i class="fas fa-lock text-danger mr-1"></i> Lock Allocation (Freeze from automatic cron recalculations & freeze approvals)
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1 pl-4" style="font-size: 11.5px; line-height: 1.45; color: #7f1d1d !important;">
                                <i class="fas fa-info-circle mr-1"></i> Checking this locks this record so nightly cron jobs will <strong>not</strong> recalculate or overwrite it.
                                <em>Note: New leave approvals are also paused while locked.</em>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer leave-modal-footer" style="padding: 14px 26px; background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="button" class="leave-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="leave-btn"><i class="fas fa-save mr-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
