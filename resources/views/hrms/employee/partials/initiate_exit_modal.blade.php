<!-- Global Initiate Exit Modal -->
<div class="modal fade" id="initiateExitModal" tabindex="-1" role="dialog" aria-labelledby="initiateExitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #3730A3, #4F46E5); padding: 20px 24px;">
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
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151;">Exit Type <span class="text-danger">*</span></label>
                            <select name="exit_type" class="eo-control eo-exit-type form-control" required style="border-radius: 10px; height: 42px;">
                                <option value="" disabled selected>Select Exit Type</option>
                                <option value="resignation">Resignation</option>
                                <option value="termination">Termination</option>
                                <option value="discontinued">Discontinuation</option>
                                <option value="absconding">Absconding</option>
                                <option value="retirement">Retirement</option>
                                <option value="contract_end">End of Contract</option>
                                <option value="internship_completed">Completion of Internship</option>
                                <option value="deceased">Death</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151;">Resignation / Effective Date</label>
                            <input type="date" name="resignation_date" class="eo-control eo-resignation-date form-control" value="{{ date('Y-m-d') }}" style="border-radius: 10px; height: 42px;">
                        </div>
                        <div class="col-md-6 mb-3 eo-notice-days-wrapper">
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151;">Notice Period (Days)</label>
                            <input type="number" name="notice_period_days" class="eo-control eo-notice-days form-control" value="15" min="0" max="365" style="border-radius: 10px; height: 42px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151;">Last Working Day <span class="text-danger">*</span></label>
                            <input type="date" name="last_working_day" class="eo-control eo-last-working-day form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 10px; height: 42px;">
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="d-flex align-items-center gap-4 flex-wrap" style="background: #F8FAFC; padding: 12px 16px; border-radius: 10px; border: 1px solid #E2E8F0;">
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
                            <label class="eo-label font-weight-bold" style="font-size: 13px; color: #374151;">Reason / Remarks</label>
                            <textarea name="reason" class="eo-control form-control" rows="2" placeholder="Provide exit reason or remarks..." style="border-radius: 10px;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #F9FAFB; border-top: 1px solid #E5E7EB; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px; font-weight: 700;">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark font-weight-bold px-4" style="border-radius: 10px; background: #F59E0B; border: none;">
                        <i class="fas fa-sign-out-alt mr-1"></i> Submit Exit Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    function initGlobalExitForm() {
        document.querySelectorAll('.eo-exit-init-form').forEach(function(form) {
            if (form.dataset.exitFormBound === 'true') return;
            form.dataset.exitFormBound = 'true';

            const exitType = form.querySelector('.eo-exit-type');
            const resignationDate = form.querySelector('.eo-resignation-date');
            const terminationDate = form.querySelector('.eo-termination-date');
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
                    if (resignationDate && resignationDate.value) {
                        lastWorkingDay.value = resignationDate.value;
                    }
                    return;
                }

                if (type === 'termination' || type === 'absconding' || immediate) {
                    if (terminationDate && terminationDate.value) {
                        lastWorkingDay.value = terminationDate.value;
                    } else if (resignationDate && resignationDate.value) {
                        lastWorkingDay.value = resignationDate.value;
                    }
                    return;
                }

                if (waived) {
                    if (resignationDate && resignationDate.value) {
                        lastWorkingDay.value = resignationDate.value;
                    }
                    return;
                }

                const notice = Math.max(0, parseInt((noticeDays && noticeDays.value) ? noticeDays.value : '15', 10) || 0);

                if (resignationDate && resignationDate.value) {
                    const base = new Date(resignationDate.value + 'T00:00:00');
                    if (!isNaN(base.getTime())) {
                        base.setDate(base.getDate() + (Math.max(1, notice) - 1));
                        lastWorkingDay.value = toYmd(base);
                    }
                }
            };

            [exitType, resignationDate, terminationDate, noticeDays, noticeWaived, immediateExit].forEach(function(el) {
                if (!el) return;
                el.addEventListener('change', recalc);
                el.addEventListener('input', recalc);
            });

            if (typeof jQuery !== 'undefined') {
                $(form).closest('.modal').on('shown.bs.modal', function() {
                    recalc();
                });
            }

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
