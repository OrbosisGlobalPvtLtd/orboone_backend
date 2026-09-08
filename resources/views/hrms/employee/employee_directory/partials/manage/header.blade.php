            <div class="ev-header">
                <div class="ev-user">
                    @php
                    $passportPhotoUrl = resolveEmployeePassportPhoto($employeeData);
                    $employeeInitial = $initial;
                    $employeeName = $employeeData->name ?? 'Employee';
                    @endphp
                    <span class="hrms-emp-avatar mr-3">
                        @if($passportPhotoUrl)
                        <img
                            src="{{ $passportPhotoUrl }}"
                            alt="{{ $employeeName }}"
                            class="hrms-emp-avatar-img"
                            onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');">
                        <span class="hrms-emp-avatar-fallback is-hidden">
                            {{ $employeeInitial }}
                        </span>
                        @else
                        <span class="hrms-emp-avatar-fallback is-visible">
                            {{ $employeeInitial }}
                        </span>
                        @endif
                    </span>

                    <div>
                        <h1 class="ev-title">{{ $employeeData->name ?? 'Employee' }}</h1>
                        <p class="ev-sub">
                            {{ $employeeData->employee_code ?? '-' }}
                            · {{ $employeeData->department_name ?? 'No Department' }}
                            · {{ $employeeData->designation_name ?? 'No Designation' }}
                        </p>

                        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                            <span class="ev-pill {{ $employmentStatus === 'active' ? 'ev-pill-active' : 'ev-pill-inactive' }}">
                                <i class="fas fa-circle mr-1" style="font-size: 8px;"></i>{{ ucfirst($employmentStatus) }}
                            </span>
                            <span class="ev-pill ev-pill-default">
                                <i class="fas fa-layer-group mr-1"></i>{{ ucfirst(str_replace('_', ' ', $stage)) }}
                            </span>
                            <span class="ev-pill {{ $profileStatus === 'approved' ? 'ev-pill-completed' : ($profileStatus === 'submitted' ? 'ev-pill-submitted' : ($profileStatus === 'rejected' ? 'ev-pill-rejected' : 'ev-pill-pending')) }}">
                                <i class="fas fa-id-card mr-1"></i>{{ $isCompleted ? 'Profile Approved' : ucfirst($profileStatus) }}
                            </span>
                            @if ($isPermanent && $stage !== 'permanent')
                            <span class="ev-pill ev-pill-completed">
                                <i class="fas fa-user-check mr-1"></i>Permanent
                            </span>
                            @endif
                            <span class="ev-pill ev-pill-default">
                                <i class="fas fa-briefcase mr-1"></i>{{ strtoupper($employeeData->work_mode ?? '-') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ev-actions">
                    <a href="{{ route('hrms.employees.index') }}" class="ev-btn-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
