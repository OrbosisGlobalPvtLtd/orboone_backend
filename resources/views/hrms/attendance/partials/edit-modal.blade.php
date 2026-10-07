@php
    $empUser = optional($attendance->user) ?: optional(optional($attendance->employee)->user);
    $empName = $empUser->name ?? 'Employee';
    $empCode = optional($attendance->employee)->employee_code ?? 'EMP';
    $empDept = optional(optional($attendance->employee)->department)->name ?? 'General';
    $formattedDate = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y (D)') : '-';
    $passportPhoto = resolveEmployeePassportPhoto($attendance->employee ?? $attendance);
    $initials = resolveEmployeeInitials($attendance->employee ?? $attendance);
@endphp

<div class="modal fade" id="editModal{{ $attendance->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable" style="max-width: 620px;">
        <div class="modal-content orb-modal" style="overflow: hidden; border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.18);">
            
            {{-- Header --}}
            <div class="orb-modal-header" style="padding: 16px 20px;">
                <div>
                    <h5 class="modal-title" style="font-size: 1.15rem; font-weight: 800;"><i class="fas fa-edit mr-1"></i> Update Attendance</h5>
                    <p class="orb-modal-subtitle" style="font-size: 12px; margin-top: 2px;">Modify status, shift timings, and update reason.</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:22px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('attendances.update') }}" style="width: 100%; margin: 0;">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" value="{{ $attendance->id }}">

                <div class="modal-body orb-modal-body" style="padding: 16px 18px !important; background: #F8FAFC !important; max-height: calc(100vh - 180px) !important;">
                    
                    {{-- Employee Context Chip --}}
                    <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-white border" style="border-radius: 12px; border-color: #E2E8F0 !important; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                        <div class="d-flex align-items-center" style="gap: 10px; min-width: 0;">
                            <span class="hrms-emp-avatar hrms-emp-avatar-sm" style="width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;">
                                @if($passportPhoto)
                                    <img src="{{ $passportPhoto }}" alt="{{ $empName }}" class="hrms-emp-avatar-img" style="width: 34px; height: 34px; border-radius: 10px;" onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');">
                                    <span class="hrms-emp-avatar-fallback is-hidden">{{ $initials }}</span>
                                @else
                                    <span class="hrms-emp-avatar-fallback is-visible" style="font-size: 12px;">{{ $initials }}</span>
                                @endif
                            </span>
                            <div style="min-width: 0;">
                                <div style="font-weight: 800; font-size: 13px; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $empName }}</div>
                                <div style="font-size: 11px; color: #64748B; font-weight: 600;">{{ $empCode }} &bull; {{ $empDept }}</div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="badge badge-light border font-weight-bold text-dark" style="font-size: 11px; border-radius: 8px; padding: 5px 9px; background: #F1F5F9; border-color: #CBD5E1 !important;">
                                <i class="far fa-calendar-alt text-primary mr-1"></i> {{ $formattedDate }}
                            </span>
                        </div>
                    </div>

                    {{-- Section 1: Status & Punch Timings (Unified Compact Card) --}}
                    <div class="orb-form-section" style="padding: 14px 16px !important; margin-bottom: 12px !important; border-radius: 14px !important; background: #fff !important; border: 1px solid #E2E8F0 !important;">
                        <div class="orb-form-section-title" style="font-size: 12px !important; margin-bottom: 10px !important; padding-bottom: 6px !important; font-weight: 800 !important; color: #0F172A !important;">
                            <i class="fas fa-sliders-h mr-1" style="color: var(--orb-primary, #4B00E8);"></i> Attendance Status & Timings
                        </div>

                        <div class="row" style="margin-left: -6px; margin-right: -6px;">
                            {{-- Status Dropdown --}}
                            <div class="col-md-6 px-2 mb-2">
                                <label class="orb-form-label mb-1" style="font-size: 11.5px !important; font-weight: 700 !important; color: #334155 !important;">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select name="attendance_type_id" class="form-control select2-modal-searchable" required style="width: 100%;">
                                    @foreach($attendanceTypes as $type)
                                        <option value="{{ $type->id }}" {{ $attendance->attendance_type_id == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Work Mode --}}
                            <div class="col-md-3 col-6 px-2 mb-2">
                                <label class="orb-form-label mb-1" style="font-size: 11.5px !important; font-weight: 700 !important; color: #334155 !important;">
                                    Work Mode
                                </label>
                                <select name="work_mode" class="form-control select2-modal-searchable" style="width: 100%;">
                                    <option value="">Auto/None</option>
                                    <option value="wfo" {{ strtolower($attendance->work_mode ?? '') === 'wfo' ? 'selected' : '' }}>WFO</option>
                                    <option value="wfh" {{ strtolower($attendance->work_mode ?? '') === 'wfh' ? 'selected' : '' }}>WFH</option>
                                </select>
                            </div>

                            {{-- Date --}}
                            <div class="col-md-3 col-6 px-2 mb-2">
                                <label class="orb-form-label mb-1" style="font-size: 11.5px !important; font-weight: 700 !important; color: #334155 !important;">
                                    Date
                                </label>
                                <x-form.date-picker name="attendance_date" id="edit_attendance_date_{{ $attendance->id }}" :value="optional($attendance->attendance_date)->format('Y-m-d')" placeholder="dd-mm-yyyy" class="form-control" />
                            </div>

                            {{-- Punch In --}}
                            <div class="col-6 px-2 mb-1">
                                <label class="orb-form-label mb-1" style="font-size: 11.5px !important; font-weight: 700 !important; color: #334155 !important;">
                                    <i class="fas fa-sign-in-alt text-success mr-1"></i> Punch In
                                </label>
                                <input type="time" name="punch_in_time" class="form-control" style="height: 38px !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 600;" value="{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('H:i') : '' }}">
                            </div>

                            {{-- Punch Out --}}
                            <div class="col-6 px-2 mb-1">
                                <label class="orb-form-label mb-1" style="font-size: 11.5px !important; font-weight: 700 !important; color: #334155 !important;">
                                    <i class="fas fa-sign-out-alt text-primary mr-1"></i> Punch Out
                                </label>
                                <input type="time" name="punch_out_time" class="form-control" style="height: 38px !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 600;" value="{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('H:i') : '' }}">
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Update Reason & Notes (Compact Card) --}}
                    <div class="orb-form-section" style="padding: 14px 16px !important; margin-bottom: 0 !important; border-radius: 14px !important; background: #fff !important; border: 1px solid #E2E8F0 !important;">
                        <div class="orb-form-section-title" style="font-size: 12px !important; margin-bottom: 10px !important; padding-bottom: 6px !important; font-weight: 800 !important; color: #0F172A !important;">
                            <i class="fas fa-comment-dots mr-1" style="color: var(--orb-primary, #4B00E8);"></i> Update Reason & Remarks
                        </div>

                        <div class="mb-2">
                            <label class="orb-form-label mb-1" style="font-size: 11.5px !important; font-weight: 700 !important; color: #334155 !important;">
                                Update Reason / Remarks <span class="text-danger">*</span>
                            </label>
                            <textarea name="hr_approval_note" class="form-control" rows="2" style="border-radius: 10px !important; font-size: 12.5px !important; padding: 8px 12px !important; resize: vertical; line-height: 1.4;" placeholder="Explain why status or timing is being updated (e.g. Work verified, Attendance corrected by HR)..." required>{{ $attendance->hr_approval_note ?: ($attendance->remarks ?: '') }}</textarea>
                        </div>

                        <div>
                            <label class="orb-form-label mb-1" style="font-size: 11.5px !important; font-weight: 700 !important; color: #64748B !important;">
                                Work Note / Punch Note <span class="text-muted font-weight-normal">(Optional)</span>
                            </label>
                            <textarea name="note" class="form-control" rows="2" style="border-radius: 10px !important; font-size: 12.5px !important; padding: 8px 12px !important; resize: vertical; line-height: 1.4;" placeholder="Optional punch out note or employee note...">{{ $attendance->punch_out_note }}</textarea>
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer orb-modal-footer" style="padding: 12px 20px; background: #fff; border-top: 1px solid #E2E8F0;">
                    <button type="button" class="orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal" style="height: 38px; padding: 0 16px; border-radius: 10px; font-weight: 700; font-size: 13px;">
                        Cancel
                    </button>
                    <button type="submit" class="orb-btn-primary" style="height: 38px; padding: 0 18px; border-radius: 10px; font-weight: 750; font-size: 13px;">
                        <i class="fas fa-save mr-1"></i> Update Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
