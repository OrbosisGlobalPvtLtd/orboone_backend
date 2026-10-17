    {{-- Pending Holiday Work Approvals (Admin/HR Only) --}}
    @if(!empty($canManageCompOff) && $holidayWorkRequests->count() > 0)
    <div class="leave-card">
        <div class="leave-card-head">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h5 class="leave-card-title">Pending Holiday Work Approvals</h5>
                    <div class="leave-card-subtitle">
                        Approve eligible holiday/weekoff work requests to generate comp-off credit.
                    </div>
                </div>
            </div>
        </div>

        <div class="pending-approval-grid">
            @foreach($holidayWorkRequests as $request)
            @php
            $employeeName = optional($request->employee)->display_name ?? optional(optional($request->employee)->user)->name ?? 'Unknown Employee';
            $initial = resolveEmployeeInitials($request->employee);
            @endphp

            <div class="approval-card">
                <div class="approval-employee">
                    @php
                        $passportPhotoUrl = resolveEmployeePassportPhoto($request->employee);
                        $employeeInitial = $initial;
                    @endphp
                    <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                        @if($passportPhotoUrl)
                            <img
                                src="{{ $passportPhotoUrl }}"
                                alt="{{ $employeeName }}"
                                class="hrms-emp-avatar-img"
                                onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');"
                            >
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
                        <div class="approval-name">{{ $employeeName }}</div>
                        <div class="approval-meta">
                            {{ optional($request->worked_date)->format('d M Y') }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('hrms.comp_offs.holiday_work.approve', $request->id) }}">
                    @csrf
                    <button class="approve-btn" type="submit">
                        <i class="fas fa-check"></i>
                        Approve
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif
