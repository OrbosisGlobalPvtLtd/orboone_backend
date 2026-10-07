<!-- Table Container (Horizontal Scroll Only) -->
<div class="exit-table-scroll">
    <table id="exitEmployeesTable" class="table table-hover eo-table">
        <thead>
            <tr>
                <th style="width: 50px; min-width: 50px;">S. No.</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Stage</th>
                <th>Exit Type</th>
                <th>Joining</th>
                <th>Last Working</th>
                <th>Asset</th>
                <th>FNF</th>
                <th>Docs</th>
                <th>Handover</th>
                <th>Exit Status</th>
                <th style="min-width: 200px; width: 200px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
            @php
            $empStage = strtolower($employee->employee_stage ?? $employee->employment_type ?? '');

            $stageClass = match ($empStage) {
                'intern', 'internship' => 'eo-pill-info',
                'probation' => 'eo-pill-warning',
                'permanent', 'confirmed', 'full_time' => 'eo-pill-success',
                default => 'eo-pill-info',
            };

            $stageLabel = match ($empStage) {
                'intern', 'internship' => 'Internship',
                'probation' => 'Probation',
                'permanent', 'confirmed', 'full_time' => 'Permanent',
                default => ucfirst(str_replace('_', ' ', $empStage ?: 'Permanent')),
            };

            $exitType = $employee->exit_type ?? 'resignation';
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

            $joiningDateDisplay = '-';
            $isIntern = in_array(strtolower($employee->employee_stage ?? $employee->employment_type ?? ''), ['intern', 'internship'], true)
            || str_contains(strtolower($employee->designation_name ?? ''), 'intern');

            if ($isIntern && !empty($employee->internship_start_date)) {
                $joiningDateDisplay = \Carbon\Carbon::parse($employee->internship_start_date)->format('d M Y');
            } elseif (!empty($employee->joining_date)) {
                $joiningDateDisplay = \Carbon\Carbon::parse($employee->joining_date)->format('d M Y');
            } elseif (!empty($employee->internship_start_date)) {
                $joiningDateDisplay = \Carbon\Carbon::parse($employee->internship_start_date)->format('d M Y');
            }
            @endphp

            <tr id="employee-row-{{ $employee->id }}"
                data-search="{{ strtolower(($employee->employee_code ?? '') . ' ' . ($employee->name ?? '') . ' ' . ($employee->email ?? '')) }}"
                data-department="{{ strtolower($employee->department_name ?? '') }}"
                data-status="{{ strtolower($finalStatus === 'completed' ? 'exit_completed' : ($exitStatus ?? $employee->employment_status ?? 'notice_period')) }}"
                data-exit-type="{{ strtolower($employee->exit_type ?? '') }}"
                data-asset="{{ strtolower($employee->asset_handover_status ?? 'pending') }}"
                data-fnf="{{ strtolower($employee->fnf_status ?? 'pending') }}">
                <td>{{ $loop->iteration }}</td>
                <td>
                    <div class="eo-emp-cell">
                        <div class="eo-code-under">{{ $employee->employee_code ?? 'EMP-' . $employee->id }}</div>
                        <div class="eo-name">{{ $employee->name ?? '-' }}</div>
                        <div class="eo-muted-text">{{ $employee->email ?? '-' }}</div>
                    </div>
                </td>

                <td>{{ $employee->department_name ?? '-' }}</td>
                <td>{{ $employee->designation_name ?? '-' }}</td>

                <td class="js-tbl-cell-stage-{{ $employee->id }}">
                    <span class="eo-pill {{ $stageClass }}">
                        {{ $stageLabel }}
                    </span>
                </td>

                <td class="js-tbl-cell-exit-type-{{ $employee->id }}">
                    <span class="eo-pill eo-pill-info">
                        {{ match(strtolower($exitType)) {
                            'resignation' => 'Resignation',
                            'termination' => 'Termination',
                            'discontinued' => 'Discontinuation',
                            'absconding' => 'Absconding',
                            'retirement' => 'Retirement',
                            'contract_end' => 'End of Contract',
                            'internship_completed' => 'Completion of Internship',
                            'deceased' => 'Death',
                            default => ucfirst(str_replace('_', ' ', $exitType)),
                        } }}
                    </span>
                </td>

                <td>
                    {{ $joiningDateDisplay }}
                </td>

                <td>
                    {{ !empty($employee->relieving_date) ? \Carbon\Carbon::parse($employee->relieving_date)->format('d M Y') : '-' }}
                </td>

                <td class="js-tbl-cell-asset-{{ $employee->id }}">
                    <span class="eo-pill {{ $statusPill($assetStatus) }}">
                        {{ ucfirst(str_replace('_', ' ', $assetStatus)) }}
                    </span>
                </td>

                <td class="js-tbl-cell-fnf-{{ $employee->id }}">
                    <span class="eo-pill {{ $statusPill($fnfStatus) }}">
                        {{ ucfirst(str_replace('_', ' ', $fnfStatus)) }}
                    </span>
                </td>
                <td class="js-tbl-cell-document-{{ $employee->id }}">
                    <span class="eo-pill {{ $statusPill($documentStatus) }}">
                        {{ ucfirst(str_replace('_', ' ', $documentStatus)) }}
                    </span>
                </td>
                <td class="js-tbl-cell-handover-{{ $employee->id }}">
                    <span class="eo-pill {{ $statusPill($handoverStatus) }}">
                        {{ ucfirst(str_replace('_', ' ', $handoverStatus)) }}
                    </span>
                </td>

                <td class="js-tbl-cell-exit-flow-{{ $employee->id }}">
                    @php
                    $overallExitStatus = $finalStatus === 'completed' ? 'exit_completed' : $exitStatus;
                    @endphp
                    <span class="eo-pill {{ $statusPill($overallExitStatus) }}">
                        {{ ucfirst(str_replace('_', ' ', $overallExitStatus)) }}
                    </span>
                </td>

                <td>
                    <div class="eo-actions">
                        @if (Route::has('hrms.employees.show'))
                        <a href="{{ route('hrms.employees.show', $employee->id) }}"
                            class="eo-icon-btn" title="View Employee">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endif

                        @if (Route::has('hrms.employees.manage'))
                        <a href="{{ route('hrms.employees.manage', $employee->id) }}"
                            class="eo-icon-btn" title="Manage Employee">
                            <i class="fas fa-user-cog"></i>
                        </a>
                        @elseif(Route::has('hrms.employees.edit'))
                        <a href="{{ route('hrms.employees.edit', $employee->id) }}"
                            class="eo-icon-btn" title="Edit Employee">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif

                        <!-- Compact Premium Trigger Button to launch Employee-specific Exit Modal -->
                        <button type="button" class="eo-action-btn-premium" data-toggle="modal" data-target="#exitModal-{{ $employee->id }}" title="Process / Update Exit">
                            <i class="fas fa-clipboard-check"></i> Process / Update Exit
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="14" class="eo-empty text-center py-4">No exit employees found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
