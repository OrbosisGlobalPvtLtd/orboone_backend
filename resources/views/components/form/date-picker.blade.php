@props([
    'name',
    'id' => null,
    'value' => null,
    'placeholder' => 'dd-mm-yyyy',
    'required' => false,
    'disabled' => false,
    'min' => null,
    'max' => null,
    'class' => '',
])

@php
    $inputId = $id ?? $name;
    $inputValue = $value ?? old($name, request($name));
@endphp

<input
    type="text"
    name="{{ $name }}"
    id="{{ $inputId }}"
    value="{{ $inputValue }}"
    placeholder="{{ $placeholder }}"
    class="{{ trim('orbo-date-picker form-control ' . $class) }}"
    data-date-picker
    autocomplete="off"
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($min) min="{{ $min }}" @endif
    @if($max) max="{{ $max }}" @endif
    {{ $attributes }}
>

@once
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<style>
.orbo-date-picker,
.orbo-date-picker-display,
.flatpickr-input.orbo-date-picker-display,
input[data-date-picker] {
    height: 38px;
    border-radius: 10px;
    border: 1px solid var(--orb-border, #E7EAF3);
    font-size: 13px;
    font-weight: 600;
    padding: 0 36px 0 12px !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366F1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 14px center !important;
    background-size: 16px 16px !important;
    color: var(--orb-text, #101828) !important;
    cursor: pointer !important;
}

.orbo-date-picker:focus,
.orbo-date-picker-display:focus,
.flatpickr-input.orbo-date-picker-display:focus,
input[data-date-picker]:focus {
    border-color: var(--orb-primary, #4B00E8) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, .1) !important;
    outline: none !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
(function () {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const shortMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    function syncCalendarWidth(input, instance) {
        if (!instance || !instance.calendarContainer) return;

        const targetField = instance.altInput || input;
        const fieldRect = targetField.getBoundingClientRect();
        const fieldWidth = Math.round(fieldRect.width);
        const viewportWidth = document.documentElement.clientWidth || window.innerWidth;

        if (fieldWidth > 0) {
            const calendarWidth = Math.min(fieldWidth, viewportWidth - 24);
            instance.calendarContainer.style.setProperty('width', `${calendarWidth}px`, 'important');
            instance.calendarContainer.style.setProperty('--orbo-date-picker-width', `${calendarWidth}px`);
        }
        updateOrboHeader(instance);
    }

    function updateOrboHeader(instance) {
        if (!instance || !instance.calendarContainer) return;
        const label = instance.calendarContainer.querySelector('.orbo-cal-header-label');
        if (label && typeof instance.currentMonth === 'number' && instance.currentYear) {
            const containerWidth = instance.calendarContainer.offsetWidth || 0;
            const useShortMonth = containerWidth > 0 && containerWidth < 250;
            const monthText = useShortMonth ? shortMonthNames[instance.currentMonth] : monthNames[instance.currentMonth];
            label.textContent = `${monthText} ${instance.currentYear}`;
        }
    }

    function setupOrboCalendarCustomHeader(instance) {
        if (!instance || !instance.calendarContainer) return;
        const container = instance.calendarContainer;

        if (container._orboCustomHeaderInitialized) {
            updateOrboHeader(instance);
            return;
        }
        container._orboCustomHeaderInitialized = true;

        const currentMonthContainer = container.querySelector('.flatpickr-current-month');
        if (!currentMonthContainer) return;

        // Build header clickable pill
        const containerWidth = container.offsetWidth || 0;
        const useShortMonth = containerWidth > 0 && containerWidth < 250;
        const initialMonthText = useShortMonth ? shortMonthNames[instance.currentMonth] : monthNames[instance.currentMonth];

        const pill = document.createElement('div');
        pill.className = 'orbo-cal-header-pill';
        pill.title = 'Click to quickly choose Month & Year';
        pill.innerHTML = `
            <span class="orbo-cal-header-label">${initialMonthText} ${instance.currentYear}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        `;
        currentMonthContainer.appendChild(pill);

        // Build overlay
        let overlayYear = instance.currentYear;
        const overlay = document.createElement('div');
        overlay.className = 'orbo-cal-overlay';
        overlay.style.display = 'none';

        overlay.innerHTML = `
            <div class="orbo-cal-overlay-year-row">
                <button type="button" class="orbo-yr-btn orbo-yr-prev5" title="Previous 5 Years">«</button>
                <button type="button" class="orbo-yr-btn orbo-yr-prev" title="Previous Year">‹</button>
                <span class="orbo-yr-display">${overlayYear}</span>
                <button type="button" class="orbo-yr-btn orbo-yr-next" title="Next Year">›</button>
                <button type="button" class="orbo-yr-btn orbo-yr-next5" title="Next 5 Years">»</button>
            </div>
            <div class="orbo-cal-overlay-grid">
                ${shortMonthNames.map((m, idx) => `<div class="orbo-cal-month-item" data-month="${idx}">${m}</div>`).join('')}
            </div>
            <div class="orbo-cal-overlay-footer">
                <button type="button" class="orbo-cal-footer-btn orbo-cal-footer-today">Today</button>
                <button type="button" class="orbo-cal-footer-btn orbo-cal-footer-close">Back to Calendar</button>
            </div>
        `;

        container.appendChild(overlay);

        const yrDisplay = overlay.querySelector('.orbo-yr-display');
        const monthItems = overlay.querySelectorAll('.orbo-cal-month-item');

        function syncOverlayState() {
            yrDisplay.textContent = overlayYear;
            monthItems.forEach((item, idx) => {
                if (idx === instance.currentMonth && overlayYear === instance.currentYear) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }

        function toggleOverlay(openState) {
            const shouldOpen = openState !== undefined ? openState : !container.classList.contains('orbo-overlay-open');
            if (shouldOpen) {
                overlayYear = instance.currentYear;
                syncOverlayState();
                container.classList.add('orbo-overlay-open');
            } else {
                container.classList.remove('orbo-overlay-open');
            }
        }

        pill.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleOverlay();
        });

        overlay.querySelector('.orbo-yr-prev5').addEventListener('click', function (e) {
            e.stopPropagation();
            overlayYear -= 5;
            syncOverlayState();
        });

        overlay.querySelector('.orbo-yr-prev').addEventListener('click', function (e) {
            e.stopPropagation();
            overlayYear -= 1;
            syncOverlayState();
        });

        overlay.querySelector('.orbo-yr-next').addEventListener('click', function (e) {
            e.stopPropagation();
            overlayYear += 1;
            syncOverlayState();
        });

        overlay.querySelector('.orbo-yr-next5').addEventListener('click', function (e) {
            e.stopPropagation();
            overlayYear += 5;
            syncOverlayState();
        });

        monthItems.forEach(item => {
            item.addEventListener('click', function (e) {
                e.stopPropagation();
                const targetMonth = parseInt(this.dataset.month, 10);
                instance.changeYear(overlayYear);
                instance.changeMonth(targetMonth, false);
                instance.redraw();
                updateOrboHeader(instance);
                toggleOverlay(false);
            });
        });

        overlay.querySelector('.orbo-cal-footer-today').addEventListener('click', function (e) {
            e.stopPropagation();
            const now = new Date();
            instance.setDate(now, true);
            instance.changeYear(now.getFullYear());
            instance.changeMonth(now.getMonth(), false);
            instance.redraw();
            updateOrboHeader(instance);
            toggleOverlay(false);
        });

        overlay.querySelector('.orbo-cal-footer-close').addEventListener('click', function (e) {
            e.stopPropagation();
            toggleOverlay(false);
        });
    }

    let activePickerInstance = null;

    function initOrboDatePickers(root = document) {
        if (typeof flatpickr === 'undefined') return;

        const scope = root || document;
        const inputs = scope.querySelectorAll ? scope.querySelectorAll('[data-date-picker]') : [];

        inputs.forEach(function (input) {
            if (input._flatpickr) return;

            flatpickr(input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                allowInput: true,
                disableMobile: true,
                monthSelectorType: 'static',
                locale: {
                    weekdays: {
                        shorthand: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                        longhand: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    }
                },
                onReady: function (selectedDates, dateStr, instance) {
                    if (instance.altInput) {
                        instance.altInput.classList.add('orbo-date-picker-display');
                    }
                    if (instance.calendarContainer) {
                        instance.calendarContainer._flatpickr = instance;
                        instance.calendarContainer.classList.add('orbo-date-picker-calendar');
                    }
                    setupOrboCalendarCustomHeader(instance);
                    syncCalendarWidth(input, instance);
                },
                onOpen: function (selectedDates, dateStr, instance) {
                    activePickerInstance = instance;
                    if (instance.calendarContainer) {
                        instance.calendarContainer._flatpickr = instance;
                        instance.calendarContainer.classList.remove('orbo-overlay-open');
                    }
                    setupOrboCalendarCustomHeader(instance);
                    updateOrboHeader(instance);
                    syncCalendarWidth(input, instance);
                },
                onMonthChange: function (selectedDates, dateStr, instance) {
                    updateOrboHeader(instance);
                },
                onYearChange: function (selectedDates, dateStr, instance) {
                    updateOrboHeader(instance);
                },
                onClose: function (selectedDates, dateStr, instance) {
                    if (instance.calendarContainer) {
                        const ov = instance.calendarContainer.querySelector('.orbo-cal-overlay');
                        if (ov) ov.style.display = 'none';
                        instance.calendarContainer.classList.remove('orbo-overlay-open');
                    }
                    if (activePickerInstance === instance) {
                        activePickerInstance = null;
                    }
                }
            });
        });
    }

    window.initOrboDatePickers = initOrboDatePickers;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initOrboDatePickers();
        });
    } else {
        initOrboDatePickers();
    }

    window.addEventListener('resize', function () {
        document.querySelectorAll('[data-date-picker]').forEach(function (input) {
            if (input._flatpickr) {
                syncCalendarWidth(input, input._flatpickr);
            }
        });
    });

    // Close calendar on page / modal scroll so it doesn't float disconnected
    window.addEventListener('scroll', function (e) {
        if (!activePickerInstance || !activePickerInstance.isOpen) return;
        if (activePickerInstance.calendarContainer && activePickerInstance.calendarContainer.contains(e.target)) {
            return;
        }
        activePickerInstance.close();
    }, { capture: true, passive: true });

    if (window.jQuery) {
        $(document).on('shown.bs.modal', function (e) {
            initOrboDatePickers(e.target);
        });
    }
})();
</script>
@endpush
@endonce
