<!-- Global Initiate Exit Modal -->
<div class="modal fade" id="initiateExitModal" tabindex="-1" role="dialog" aria-labelledby="initiateExitModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)); padding: 20px 24px;">
                <div>
                    <h5 class="modal-title font-weight-bold" id="initiateExitModalLabel" style="font-size: 18px; margin: 0; color: #fff;">
                        <i class="fas fa-sign-out-alt mr-2"></i> Initiate Employee Exit
                    </h5>
                    <p class="mb-0 small text-white-50 mt-1" id="initiateExitModalSub">
                        Initiate offboarding process for employee
                    </p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="initiateExitGlobalForm" method="POST" action="" class="mb-0 eo-exit-init-form">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                                Exit Type <span class="text-danger">*</span>
                            </label>
                            <x-form.select 
                                name="exit_type" 
                                id="global_modal_exit_type" 
                                class="eo-control eo-exit-type" 
                                placeholder="Select Exit Type"
                                :options="[
                                    'resignation' => 'Resignation',
                                    'termination' => 'Termination',
                                    'discontinued' => 'Discontinuation',
                                    'absconding' => 'Absconding',
                                    'retirement' => 'Retirement',
                                    'contract_end' => 'End of Contract',
                                    'internship_completed' => 'Completion of Internship',
                                    'deceased' => 'Death'
                                ]"
                                :required="true"
                                :searchable="true" 
                                wrapper-class="m-0"
                            />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                                Resignation / Effective Date
                            </label>
                            <x-form.date-picker 
                                name="resignation_date" 
                                id="global_modal_resignation_date" 
                                class="eo-control eo-resignation-date" 
                                :value="date('Y-m-d')" 
                                placeholder="dd-mm-yyyy"
                            />
                        </div>

                        <div class="col-md-6 mb-3 eo-notice-days-wrapper">
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                                Notice Period (Days)
                            </label>
                            <input type="number" name="notice_period_days" class="eo-control eo-notice-days form-control" value="15" min="0" max="365" style="border-radius: 12px; height: 42px; font-weight: 700;">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                                Last Working Day <span class="text-danger">*</span>
                            </label>
                            <x-form.date-picker 
                                name="last_working_day" 
                                id="global_modal_last_working_day" 
                                class="eo-control eo-last-working-day" 
                                :value="date('Y-m-d')" 
                                :required="true"
                                placeholder="dd-mm-yyyy"
                            />
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="d-flex align-items-center gap-4 flex-wrap" style="background: #F8FAFC; padding: 12px 16px; border-radius: 12px; border: 1px solid #E2E8F0;">
                                <div class="custom-control custom-checkbox mr-3">
                                    <input type="checkbox" name="notice_waived" value="1" class="custom-control-input eo-notice-waived" id="globalNoticeWaived">
                                    <label class="custom-control-label font-weight-bold text-dark" for="globalNoticeWaived" style="font-size: 12px; cursor: pointer;">Waive Notice Period</label>
                                </div>
                                <div class="custom-control custom-checkbox mr-3">
                                    <input type="checkbox" name="immediate_exit" value="1" class="custom-control-input eo-immediate-exit" id="globalImmediateExit">
                                    <label class="custom-control-label font-weight-bold text-dark" for="globalImmediateExit" style="font-size: 12px; cursor: pointer;">Immediate Exit</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="immediate_disable_login" value="1" class="custom-control-input" id="globalDisableLogin">
                                    <label class="custom-control-label font-weight-bold text-danger" for="globalDisableLogin" style="font-size: 12px; cursor: pointer;">Disable User Login Immediately</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                                Reason / Remarks
                            </label>
                            <textarea name="reason" class="eo-control form-control" rows="2" placeholder="Provide exit reason or remarks..." style="border-radius: 12px;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: #F9FAFB; border-top: 1px solid #E5E7EB; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 12px; font-weight: 700;">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white font-weight-bold px-4" style="border-radius: 12px; background: linear-gradient(135deg, #F59E0B, #D97706); border: none; min-height: 42px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-sign-out-alt mr-1"></i> Submit Exit Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #initiateExitModal .select2-container--bootstrap4 .select2-selection--single,
    #initiateExitModal .select2-container .select2-selection--single {
        height: 42px !important;
        border-radius: 12px !important;
        border: 1px solid #E2E8F0 !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 12px !important;
    }
    #initiateExitModal .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered,
    #initiateExitModal .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        color: #1F2937 !important;
        padding-left: 0 !important;
    }
    #initiateExitModal .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow,
    #initiateExitModal .select2-container .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    #initiateExitModal .orbo-date-picker-display,
    #initiateExitModal input[data-date-picker],
    #initiateExitModal .flatpickr-input {
        border-radius: 12px !important;
        height: 42px !important;
        min-height: 42px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        border: 1px solid #E2E8F0 !important;
        background-color: #ffffff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234B00E8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 14px center !important;
        background-size: 16px 16px !important;
        padding-right: 40px !important;
        cursor: pointer !important;
    }
    #initiateExitModal .orbo-date-picker-display:focus,
    #initiateExitModal input[data-date-picker]:focus,
    #initiateExitModal .flatpickr-input:focus {
        border-color: rgba(75, 0, 232, .45) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, .08) !important;
    }
    .flatpickr-calendar {
        z-index: 999999 !important;
    }
</style>

<script>
(function() {
    function initGlobalExitForm() {
        document.querySelectorAll('.eo-exit-init-form').forEach(function(form) {
            if (form.dataset.exitFormBound === 'true') return;
            form.dataset.exitFormBound = 'true';

            const exitType = form.querySelector('.eo-exit-type');
            const resignationDate = form.querySelector('.eo-resignation-date');
            const lastWorkingDay = form.querySelector('.eo-last-working-day');
            const noticeDays = form.querySelector('.eo-notice-days');
            const noticeWaived = form.querySelector('.eo-notice-waived');
            const immediateExit = form.querySelector('.eo-immediate-exit');
            const noticeWrapper = form.querySelector('.eo-notice-days-wrapper') || (noticeDays ? noticeDays.closest('.col-md-6') : null);

            const toYmd = function(dateObj) {
                const y = dateObj.getFullYear();
                const m = String(dateObj.getMonth() + 1).padStart(2, '0');
                const d = String(dateObj.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + d;
            };

            const setLastWorkingDay = function(valStr) {
                if (!lastWorkingDay) return;
                if (lastWorkingDay._flatpickr) {
                    lastWorkingDay._flatpickr.setDate(valStr, false);
                } else {
                    lastWorkingDay.value = valStr;
                }
            };

            const getResignationDateVal = function() {
                if (!resignationDate) return '';
                return resignationDate.value || '';
            };

            const recalc = function() {
                if (!exitType || !lastWorkingDay) return;

                const type = String(exitType.value || '').toLowerCase();
                const waived = !!(noticeWaived && noticeWaived.checked);
                const immediate = !!(immediateExit && immediateExit.checked);

                const isInternship = (type === 'internship_completed' || type === 'internship_exit' || type.includes('intern'));

                if (noticeWrapper) {
                    if (isInternship || type === 'termination' || type === 'absconding' || type === 'deceased') {
                        noticeWrapper.style.display = 'none';
                    } else {
                        noticeWrapper.style.display = '';
                    }
                }

                if (isInternship) {
                    if (noticeDays) noticeDays.value = 0;
                    const resVal = getResignationDateVal();
                    if (resVal) {
                        setLastWorkingDay(resVal);
                    }
                    return;
                }

                if (type === 'termination' || type === 'absconding' || immediate) {
                    const resVal = getResignationDateVal();
                    if (resVal) {
                        setLastWorkingDay(resVal);
                    }
                    return;
                }

                if (waived) {
                    const resVal = getResignationDateVal();
                    if (resVal) {
                        setLastWorkingDay(resVal);
                    }
                    return;
                }

                const notice = Math.max(0, parseInt((noticeDays && noticeDays.value) ? noticeDays.value : '15', 10) || 0);
                const resVal = getResignationDateVal();

                if (resVal) {
                    const base = new Date(resVal + 'T00:00:00');
                    if (!isNaN(base.getTime())) {
                        base.setDate(base.getDate() + (Math.max(1, notice) - 1));
                        setLastWorkingDay(toYmd(base));
                    }
                }
            };

            const modal = $(form).closest('.modal');
            if (modal.length) {
                modal.on('shown.bs.modal', function() {
                    if (typeof $.fn.select2 !== 'undefined') {
                        $(form).find('select.select2-searchable').each(function() {
                            if (!$(this).data('select2')) {
                                $(this).select2({
                                    theme: 'bootstrap4',
                                    width: '100%',
                                    dropdownParent: modal
                                });
                            }
                        });
                    }
                    if (typeof window.initOrboDatePickers === 'function') {
                        window.initOrboDatePickers(form);
                    }
                    recalc();
                });
            }

            $(form).find('.eo-exit-type').on('change change.select2', recalc);
            $(form).find('.eo-resignation-date, .eo-last-working-day, .eo-notice-days, .eo-notice-waived, .eo-immediate-exit').on('input change', recalc);

            recalc();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlobalExitForm);
    } else {
        initGlobalExitForm();
    }
})();
</script>
