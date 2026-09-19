<!-- Department Exit Clearances Collapsible Accordion Section -->
<div class="card border-0 shadow-sm mb-3" style="border-radius:12px; background: #fff; border: 1px solid #E7EAF3;">
    <div class="card-header bg-white border-0 py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="font-weight-bold text-dark mb-0" style="font-size:13.5px;"><i class="fas fa-tasks text-primary mr-2"></i> Department Exit Clearances</h6>
            <span class="text-muted small">Click any department below to view checklist & update approval status</span>
        </div>
        <span class="badge badge-light border text-muted d-none d-sm-inline-block" style="font-size:11px;">8 Departments</span>
    </div>
    <div class="card-body p-2 px-3">
        @php
        $actor = auth()->user();
        $isSuperAdmin = $actor && method_exists($actor, 'isSuperAdmin') && $actor->isSuperAdmin();
        $isHrAdmin = $actor && method_exists($actor, 'hasRole') && $actor->hasRole('hr_admin');
        $isHRorAdmin = $isSuperAdmin || $isHrAdmin;

        $deptKeys = ['hr', 'manager', 'it', 'admin', 'finance', 'asset', 'security', 'accounts'];
        $deptLabels = [
            'hr' => 'Human Resources (HR)',
            'manager' => 'Reporting Manager',
            'it' => 'IT Department',
            'admin' => 'Admin Department',
            'finance' => 'Finance Department',
            'asset' => 'Asset Management Team',
            'security' => 'Security Office',
            'accounts' => 'Accounts Department',
        ];
        @endphp

        <div class="accordion" id="deptAccordion-{{ $employee->id }}">
            @foreach($deptKeys as $dKey)
            @php
            $clearance = isset($employee->clearances[$dKey]) ? $employee->clearances[$dKey] : null;
            $clrStatus = $clearance ? $clearance->status : 'pending';
            $clrRemarks = $clearance ? $clearance->remarks : '';
            $checklist = $clearance ? json_decode($clearance->checklist, true) : [];
            $approvedBy = '';
            if ($clearance && $clearance->approved_by_user_id) {
                $approvedBy = \DB::table('users')->where('id', $clearance->approved_by_user_id)->value('name');
            }
            $approvedAt = $clearance && $clearance->approved_at ? \Carbon\Carbon::parse($clearance->approved_at)->format('d M Y') : '';

            // Evaluate security/approve permission
            $canApproveDept = false;
            if ($isHRorAdmin) {
                $canApproveDept = true;
            } else {
                if ($dKey === 'manager' && $actor->employee && !empty($employee->reporting_manager_employee_id) && $employee->reporting_manager_employee_id == $actor->employee->id) {
                    $canApproveDept = true;
                }

                if (!$canApproveDept) {
                    $pMap = [
                        'hr' => 'employee_exit.clearance.hr',
                        'manager' => 'employee_exit.clearance.manager',
                        'it' => 'employee_exit.clearance.it',
                        'admin' => 'employee_exit.clearance.admin',
                        'finance' => 'employee_exit.clearance.finance',
                        'asset' => 'employee_exit.clearance.asset',
                        'security' => 'employee_exit.clearance.security',
                        'accounts' => 'employee_exit.clearance.accounts',
                    ];
                    if (isset($pMap[$dKey]) && $actor->hasPermission($pMap[$dKey])) {
                        $canApproveDept = true;
                    }
                }

                // Fallback department name check
                if (!$canApproveDept && $actor->employee && !empty($actor->employee->department_id)) {
                    $uDeptName = \DB::table('departments')->where('id', $actor->employee->department_id)->value('name');
                    if ($uDeptName) {
                        $uDeptNameLower = strtolower($uDeptName);
                        if ($dKey === 'it' && (str_contains($uDeptNameLower, 'it') || str_contains($uDeptNameLower, 'infrastructure') || str_contains($uDeptNameLower, 'devops'))) {
                            $canApproveDept = true;
                        } elseif ($dKey === 'finance' && (str_contains($uDeptNameLower, 'finance') || str_contains($uDeptNameLower, 'account'))) {
                            $canApproveDept = true;
                        } elseif ($dKey === 'accounts' && (str_contains($uDeptNameLower, 'finance') || str_contains($uDeptNameLower, 'account'))) {
                            $canApproveDept = true;
                        }
                    }
                }
            }
            @endphp

            <div class="card border mb-2 js-dept-card js-dept-card-{{ $dKey }} js-dept-card-{{ $dKey }}-{{ $employee->id }}" data-dept="{{ $dKey }}" data-employee-id="{{ $employee->id }}" style="border-radius:10px; overflow:hidden; border-left: 4px solid {{ $clrStatus === 'approved' ? '#10B981' : ($clrStatus === 'rejected' ? '#EF4444' : '#F59E0B') }} !important;">
                <div class="card-header py-2.5 px-3 d-flex justify-content-between align-items-center eo-dept-head" 
                     style="cursor:pointer;" 
                     data-toggle="collapse" 
                     data-target="#collapse-dept-{{ $dKey }}-{{ $employee->id }}" 
                     aria-expanded="false">
                    <!-- Left: Department Icon & Label -->
                    <div class="d-flex align-items-center eo-dept-head-left pr-2">
                        <i class="fas {{ $clrStatus === 'approved' ? 'fa-check-circle text-success' : ($clrStatus === 'rejected' ? 'fa-times-circle text-danger' : 'fa-clock text-warning') }} mr-2 js-dept-icon js-dept-icon-{{ $dKey }} js-dept-icon-{{ $dKey }}-{{ $employee->id }}" style="font-size:13.5px;"></i>
                        <span class="font-weight-bold text-dark" style="font-size:12.8px; letter-spacing: 0.1px;">{{ $deptLabels[$dKey] }}</span>
                    </div>

                    <!-- Right: Approver Info + Status Badge + Toggle Arrow -->
                    <div class="d-flex align-items-center eo-dept-head-right ml-auto">
                        <span class="js-dept-approved-by js-dept-approved-by-{{ $dKey }} js-dept-approved-by-{{ $dKey }}-{{ $employee->id }}">
                            @if($approvedBy)
                            <span class="text-muted small mr-2 d-none d-md-inline" style="font-size:11.5px;">by <strong class="text-dark">{{ $approvedBy }}</strong> on {{ $approvedAt }}</span>
                            @endif
                        </span>
                        <span class="badge js-dept-badge js-dept-badge-{{ $dKey }} js-dept-badge-{{ $dKey }}-{{ $employee->id }} badge-{{ $clrStatus === 'approved' ? 'success' : ($clrStatus === 'rejected' ? 'danger' : 'warning') }} px-2.5 py-1" style="font-size:11px; font-weight:700; border-radius:6px; letter-spacing:0.3px;">
                            {{ ucfirst($clrStatus) }}
                        </span>
                        <i class="fas fa-chevron-down fa-xs text-muted ml-2"></i>
                    </div>
                </div>

                <div id="collapse-dept-{{ $dKey }}-{{ $employee->id }}" class="collapse" data-parent="#deptAccordion-{{ $employee->id }}">
                    <div class="card-body p-3 bg-white">
                        @if($canApproveDept || $isHRorAdmin)
                        <form action="{{ route('hrms.employees.exit.clearance.dept.update', $employee->id) }}" method="POST" class="mb-0 js-dept-clearance-form" data-dept="{{ $dKey }}" data-employee-id="{{ $employee->id }}">
                            @csrf
                            <input type="hidden" name="exit_process_id" value="{{ $employee->exit_process_id }}">
                            <input type="hidden" name="department_key" value="{{ $dKey }}">

                            @if(!empty($checklist))
                            <label class="eo-label mb-1">Clearance Checklist Items:</label>
                            <div class="mb-3 pl-2">
                                @foreach($checklist as $index => $item)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="hidden" name="checklist[{{ $item['item'] }}]" value="0">
                                    <input type="checkbox" name="checklist[{{ $item['item'] }}]" value="1" class="custom-control-input" id="chk-{{ $dKey }}-{{ $index }}-{{ $employee->id }}" {{ $item['completed'] ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-normal text-dark" for="chk-{{ $dKey }}-{{ $index }}-{{ $employee->id }}" style="font-size:12px;">{{ $item['item'] }}</label>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-md-8 mb-2">
                                    <label class="eo-label">Remarks</label>
                                    <input type="text" name="remarks" class="eo-control" value="{{ $clrRemarks }}" placeholder="Enter department clearance remarks...">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="eo-label">Action</label>
                                    <select name="status" class="eo-control" required>
                                        <option value="pending" {{ $clrStatus === 'pending' ? 'selected' : '' }}>Set Pending</option>
                                        <option value="approved" {{ $clrStatus === 'approved' ? 'selected' : '' }}>Approve Clearance</option>
                                        <option value="rejected" {{ $clrStatus === 'rejected' ? 'selected' : '' }}>Reject Clearance</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                        @else
                        <!-- Read only view for employees or other departments -->
                        <div class="eo-action-body p-0">
                            @if(!empty($checklist))
                            <label class="eo-label mb-1">Clearance Checklist Items:</label>
                            <div class="mb-2 pl-2">
                                @foreach($checklist as $item)
                                <div class="mb-1 text-dark" style="font-size:12px;">
                                    <i class="fas {{ $item['completed'] ? 'fa-check-square text-success' : 'fa-square text-muted' }} mr-2"></i>
                                    <span class="{{ $item['completed'] ? 'text-success font-weight-bold' : '' }}">{{ $item['item'] }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            @if($clrRemarks)
                            <div class="mt-2 text-muted small"><strong>Remarks:</strong> {{ $clrRemarks }}</div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
